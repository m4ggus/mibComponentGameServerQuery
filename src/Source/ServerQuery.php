<?php
/**
 * Created by PhpStorm.
 * User: marcus
 * Date: 12/21/14
 * Time: 7:18 PM
 */

namespace Mib\Component\GameServerQuery\Source;


use Mib\Component\GameServerQuery\ServerQueryInterface;
use Mib\Component\Network\SocketInterface;

class ServerQuery implements ServerQueryInterface {

    const COMMAND_STATUS = "\xFF\xFF\xFF\xFFTSource Engine Query\x00";

    /** @var SocketInterface */
    private $socket;

    /**
     * Constructor
     * @param SocketInterface $socket
     */
    public function __construct(SocketInterface $socket)
    {
        $this->socket = $socket;
    }

    /**
     * Returns the source game server status information
     * @param $gameServerAddress
     * @return array
     */
    public function getStatus($gameServerAddress)
    {
        if (!is_string($gameServerAddress)) throw new \InvalidArgumentException(sprintf('string expected, got "%s" for $host', gettype($gameServerAddress)));

        @list($host, $port) = explode(':', $gameServerAddress);

        if (!($host = filter_var($host, FILTER_VALIDATE_IP))
            || !($port = filter_var($port, FILTER_VALIDATE_INT)))
            throw new \InvalidArgumentException(sprintf('invalid host format, got "%s". supported format: <host:port>', $gameServerAddress));


        $this->socket->sendTo(self::COMMAND_STATUS, strlen(self::COMMAND_STATUS), $host, $port);
        $data = $this->socket->read();
        $this->socket->close();

        if (empty($data)) {
            throw new \RuntimeException('invalid response data');
        }

        $expectedHeader = "\xFF\xFF\xFF\xFF\x49";

        if (0 !== ($skip = strpos($data, $expectedHeader)))
            throw new \RuntimeException(sprintf('unsupported source response header'));

        $data = substr($data, $skip + strlen($expectedHeader) + 1);

        return array(
            'name' => $this->shiftString($data),
            'map'  => $this->shiftString($data),
            'folder' => $this->shiftString($data),
            'game' => $this->shiftString($data),
            'id' => $this->shiftShort($data),
            'players' => $this->shiftByte($data),
            'maxPlayers' => $this->shiftByte($data),
            'bots' => $this->shiftByte($data),
            'serverType' => $this->shiftByte($data, false),
            'environment' => $this->shiftByte($data, false),
            'visibility' => $this->shiftByte($data),
            'vac' => $this->shiftByte($data)
        );
    }

    /**
     * Extracts a string from binary input and removes it
     * @param $in
     * @return string
     */
    protected function shiftString(&$in) {

        $end = strpos($in, "\000");

        // get remaining chars
        if (false === $end)
            $end = null;

        $value = substr($in, 0, $end);

        // update input by removing the extracted string or set it to an empty one
        if (null !== $end)
            $in = substr($in, $end + 1);
        else
            $in = '';

        return $value;
    }

    /**
     * Extracts a byte from binary input and removes it
     * If is asNumeric is true value will be converted to integer instead of char
     * @param $in
     * @param bool $asNumeric
     * @return int
     */
    protected function shiftByte(&$in, $asNumeric = true)
    {
        $len = strlen($in);

        if (strlen($len) < 1)
            throw new \RuntimeException(sprintf('buffer overflow'));

        $value = $in[0];

        if ($len > 1)
            $in = substr($in, 1);
        else
            $in = '';

        return $asNumeric ? ord($value) : $value ;
    }

    /**
     * Extracts a short from binary and removes it
     * If is asNumeric is true value will be converted to integer instead of a string
     * @param $in
     * @param bool $asNumeric
     * @return int|string
     */
    protected function shiftShort(&$in, $asNumeric = true)
    {
        $len = strlen($in);

        if ($len < 2)
            throw new \RuntimeException(sprintf('buffer overflow'));

        $value = substr($in, 0, 2);

        if ($len > 2)
            $in = substr($in, 2);
        else
            $in = '';

        return ($asNumeric ? ord($value[0])*ord($value[1]) : $value);
    }

}