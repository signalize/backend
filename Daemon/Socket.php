<?php

namespace Signalize\Daemon;

use Ratchet\ConnectionInterface;
use Ratchet\Http\HttpServer;
use Ratchet\MessageComponentInterface;
use Ratchet\Server\IoServer;
use Ratchet\WebSocket\WsServer;
use Signalize\Core\Config;

class Socket implements MessageComponentInterface
{
    private $clients;

    public function __construct()
    {
        $this->clients = new \SplObjectStorage;
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


    public function onOpen(ConnectionInterface $conn)
    {
        $client = new Client($conn);
        $this->clients->attach($client);
        $this->dump('Connection Established.', '1;32');
    }

    public function onMessage(ConnectionInterface $from, $msg)
    {
        if ($client = $this->getClient($from)) {
            if (substr($msg, 0, 8) === 'MACHINE:') {
                $machineID = substr($msg, 8);
                if ($machineID !== Socket::Machine()) {
                    $from->close();
                }
                $client->authorized(true);
                return;
            }

            if ($client->authorized()) {
                echo "Message: " . $msg . PHP_EOL;

                /** @var Client $client */
                foreach ($this->clients as $client) {
                    if ($client->authorized() && !$client->isConnection($from)) {
                        $client->send($msg);
                    }
                }
            }
        }
    }

    public function onError(ConnectionInterface $conn, \Exception $e)
    {
        // TODO: Implement onError() method.
    }

    public function onClose(ConnectionInterface $conn)
    {
        foreach ($this->clients as $client) {
            if ($client->isConnection($conn)) {
                $this->clients->detach($client);
            }
        }
        $this->dump('Connection Closed.', '1;31');
    }


    private function getClient($conn)
    {
        /** @var Client $client */
        foreach ($this->clients as $client) {
            if ($client->isConnection($conn)) {
                return $client;
            }
        }
        return false;
    }

    static function Machine()
    {
        return Config::get('socket')->security;
    }


    private function dump($str, $color = 0)
    {
        echo "\033[" . $color . "m" . $str . "\033[0m" . PHP_EOL;
    }
}


class Client
{
    private $connection;
    private $authorized = false;

    public function __construct(ConnectionInterface $connection)
    {
        $this->connection = $connection;
    }

    public function isConnection($conn)
    {
        return $conn === $this->connection;
    }

    public function authorized($set = null)
    {
        if (!is_null($set)) {
            $this->authorized = $set;
        }
        return $this->authorized;
    }

    public
    function send($data)
    {
        $this->connection->send($data);
    }
}