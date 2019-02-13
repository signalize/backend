<?php

namespace Signalize\Socket;

use Ratchet\ConnectionInterface;
use Ratchet\Http\HttpServer;
use Ratchet\MessageComponentInterface;
use Ratchet\Server\IoServer;
use Ratchet\WebSocket\WsServer;
use Signalize\Config;

class Service implements MessageComponentInterface
{
    /** @var \SplObjectStorage<Connection> */
    private $connections;

    /**
     * Service constructor.
     */
    public function __construct()
    {
        $this->connections = new \SplObjectStorage;
        $server = IoServer::factory(
            new HttpServer(
                new WsServer(
                    $this
                )
            ),
            Config::get('socket')->port
        );
        $server->run();
    }

    /**
     * @param ConnectionInterface $conn
     */
    public function onOpen(ConnectionInterface $conn)
    {
        $connection = new Connection($conn);
        $this->connections->attach($connection);
        $this->dump('Connection Established.', '1;32');
    }

    /**
     * @param ConnectionInterface $conn
     * @param string $msg
     */
    public function onMessage(ConnectionInterface $conn, $msg)
    {
        if ($connection = $this->getConnection($conn)) {
            if (substr($msg, 0, 8) === 'LOGIN:') {
                $token = substr($msg, 8);
                if ($token !== self::token()) {
                    $connection->close();
                }
                $connection->authorize(true);
                return;
            }

            if ($connection->authorized()) {
                echo "Message: " . $msg . PHP_EOL;

                /** @var Connection $connection */
                foreach ($this->connections as $c) {
                    if ($c->authorized() && !$c->isConnection($conn)) {
                        $c->send($msg);
                    }
                }
            }
        }
    }

    /**
     * @param ConnectionInterface $conn
     * @param \Exception $e
     */
    public function onError(ConnectionInterface $conn, \Exception $e)
    {
        //
    }

    /**
     * @param ConnectionInterface $conn
     */
    public function onClose(ConnectionInterface $conn)
    {
        foreach ($this->connections as $connection) {
            if ($connection->isConnection($conn)) {
                $this->connections->detach($connection);
            }
        }
        $this->dump('Connection Closed.', '1;31');
    }

    /**
     * @param ConnectionInterface $conn
     * @return Connection
     */
    private function getConnection(ConnectionInterface $conn): Connection
    {
        /** @var Connection $connection */
        foreach ($this->connections as $connection) {
            if ($connection->isConnection($conn)) {
                return $connection;
            }
        }
        return null;
    }

    /**
     * @param string $str
     * @param string $color
     */
    private function dump(string $str, string $color = '0')
    {
        echo "\033[" . $color . "m" . $str . "\033[0m" . PHP_EOL;
    }

    /**
     * @return string
     */
    static public function token(): string
    {
        return Config::get('socket')->security;
    }
}
