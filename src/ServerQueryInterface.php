<?php

namespace Mib\Component\GameServerQuery;

/**
 * Interface ServerQueryInterface
 * @package Mib\Component\GameServerQuery
 */
interface ServerQueryInterface {

    /**
     * Returns an array with the game server status information
     * Array keys represents the config game parameter
     *
     * @param $host
     * @return array
     * @throws \InvalidArgumentException|\RuntimeException
     */
    public function getStatus($host);

}