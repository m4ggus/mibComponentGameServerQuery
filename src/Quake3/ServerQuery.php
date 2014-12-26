<?php

namespace Mib\Component\GameServerQuery\Quake3;

use Mib\Component\GameServerQuery\ServerQueryInterface;
use Mib\Component\Network\SocketInterface;

class ServerQuery implements ServerQueryInterface {

    const COMMAND_STATUS = "\xFF\xFF\xFF\xFFgetstatus\x00";

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
     * Returns the quake3-like game server status information
     * @param $host
     * @return array
     */
    public function getStatus($host)
    {
        if (!is_string($host)) throw new \InvalidArgumentException(sprintf('string expected, got "%s" for $host', gettype($host)));

        @list($host, $port) = explode(':', $host);

        if (!($host = filter_var($host, FILTER_VALIDATE_IP))
            || !($port = filter_var($port, FILTER_VALIDATE_INT)))
            throw new \InvalidArgumentException(sprintf('invalid host format, got "%s". supported format: <host:port>', $host));


        $this->socket->sendTo(self::COMMAND_STATUS, strlen(self::COMMAND_STATUS), $host, $port);
        $data = $this->socket->read();
        $this->socket->close();

        if (empty($data)) {
            throw new \RuntimeException('invalid response data');
        }

        $expectedHeader = "\xFF\xFF\xFF\xFFstatusResponse\n";

        if (0 !== ($skip = strpos($data, $expectedHeader)))
            throw new \RuntimeException(sprintf('unsupported source response header'));

        $data = substr($data, $skip + strlen($expectedHeader) + 1);

        $settings = $this->shiftSettings($data);

        $players = $this->shiftPlayers($data);

        $settings['players'] = $players;

        return $settings;
    }

    /**
     * Disassembles the settings data into key value pairs
     * @param $in
     * @return array
     */
    protected function shiftSettings(&$in) {

        $settings = array();

        while (false !== ($posFirst = strpos($in, '\\'))) {
            $posSecond = strpos($in, '\\', $posFirst + 1);

            // bypass last time for last setting
            if (false === $posSecond)
                if (false === ($posSecond = strpos($in, "\n", $posFirst + 1)))
                    break;

            $name = substr($in, 0, $posFirst);
            $value = substr($in, $posFirst + 1, $posSecond - $posFirst - 1);

            $settings[$name] = $this->removeColorCodes($value);

            $in = substr($in, $posSecond + 1);
        }

        return $settings;
    }

    /**
     * Disassembles the player data into player game information
     * @param $in
     * @return array
     */
    protected function shiftPlayers(&$in)
    {
        $playerDataSet = array();

        while (false !== ($pos = strpos($in, "\n"))) {

            $line = substr($in, 0, $pos);

            $playerData = @sscanf($line, '%d %d "%s"');

            if (count($playerData) === 3) {
                $playerDataSet[] = array(
                    'name'   => $this->removeColorCodes($playerData[2]),
                    'score'  => $playerData[0],
                    'ping'   => $playerData[1]
                );
            } else {
                $playerDataSet[] = array(
                    'name'   => $this->removeColorCodes($playerData[3]),
                    'score'  => $playerData[0],
                    'ping'   => $playerData[1],
                    'deaths' => $playerData[2]
                );
            }

            $in = substr($in, $pos + 1);
        }

        return $playerDataSet;
    }

    /**
     * Removes the color codes from given input
     * @param $in
     * @return string
     */
    protected function removeColorCodes($in)
    {
        return preg_replace('/\^./', '', $in);
    }


}