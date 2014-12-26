<?php

class ServerQueryTest extends PHPUnit_Framework_TestCase
{
    private $serverHost = '127.0.0.1';
    private $serverPort = 27960;

    public function testCanGetStatus()
    {
        $host = $this->serverHost;
        $port = $this->serverPort;

        $socket = $this
            ->getMockBuilder('\Mib\Component\Network\SocketInterface')
            ->getMock();

        $socket->expects($this->once())
            ->method('sendTo')
            ->with(
                "\xFF\xFF\xFF\xFFgetstatus\x00",
                strlen("\xFF\xFF\xFF\xFFgetstatus\x00"),
                $host,
                $port
            );

        $socket->expects($this->once())
            ->method('read')
            ->will($this->returnCallback(function()use($host, $port){

                $handle = fopen('data/sample_quake3.raw', 'rb');
                $buffer = fread($handle, 4096);
                fclose($handle);
                return $buffer;
            }));

        $serverQuery = new \Mib\Component\GameServerQuery\Quake3\ServerQuery($socket);
        $response = $serverQuery->getStatus($host.':'.$port);

        $this->assertInternalType('array', $response);
    }
}