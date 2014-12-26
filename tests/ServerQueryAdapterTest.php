<?php
/**
 * Created by PhpStorm.
 * User: marcus
 * Date: 12/24/14
 * Time: 4:20 AM
 */

class ServerQueryAdapterTest extends PHPUnit_Framework_TestCase {

    /** @var \Mib\Component\GameServerQuery\ServerQueryAdapter */
    private $adapter;

    public function setUp()
    {
        /** @var Mib\Component\Network\SocketInterface $socket */
        $socket = $this
            ->getMockBuilder('Mib\Component\Network\SocketInterface')
            ->getMock();

        $this->adapter = new \Mib\Component\GameServerQuery\ServerQueryAdapter($socket);
    }

    public function testGetStatusThrowsIfPassedUnsupportedType()
    {
        $this->setExpectedException('\RunTimeException', 'unsupported game type');

        $this->adapter->getStatus('UNSUPPORTED_GAMETYPE', '127.0.0.1:1337');
    }

    public function testGetStatusThrowsIfPassedUnsupportedServerAddressFormat()
    {
        $this->setExpectedException('\InvalidArgumentException');

        $this->adapter->getStatus('source', '127.0.0.1');
    }
}