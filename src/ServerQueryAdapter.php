<?php

namespace Mib\Component\GameServerQuery;

use Mib\Component\Network\SocketInterface;

/**
 * Class ServerQueryAdapter
 * @package Mib\Component\GameServerQuery
 */
class ServerQueryAdapter {

    /** @var SocketInterface */
    private $socket;

    private $gameTypes = array();

    /**
     * Constructor
     * @param SocketInterface $socket
     */
    public function __construct(SocketInterface $socket)
    {
        $this->socket = $socket;
        $this->gameTypes['source'] = 'Source\ServerQuery';
        $this->gameTypes['q3'] = 'Quake3\ServerQuery';
    }

    /**
     * Get game status by supported game type
     * The host value needs to be in a ip:port format
     * @param $type
     * @param $host
     * @return array
     */
    public function getStatus($type, $host)
    {
        if (!isset($this->gameTypes[$type])) {
            throw new \RuntimeException('unsupported game type');
        }

        $class = __NAMESPACE__.'\\'.$this->gameTypes[$type];

        /** @var ServerQueryInterface $query */
        $query = new $class($this->socket);

        return $query->getStatus($host);

    }
}