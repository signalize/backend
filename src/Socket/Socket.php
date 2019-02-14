<?php

namespace Signalize\Socket;

use Ratchet\ConnectionInterface;
use Ratchet\Http\HttpServer;
use Ratchet\MessageComponentInterface;
use Ratchet\Server\IoServer;
use Ratchet\WebSocket\WsServer;

class Socket implements MessageComponentInterface
{
    /** @var \SplObjectStorage<Connection> */
    private $connections;

    /**
     * Service constructor.
     */
    public function __construct($port)
    {
        $this->connections = new \SplObjectStorage;
        $server = IoServer::factory(
            new HttpServer(
                new WsServer(
                    $this
                )
            ),
            $port
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
     * @return bool
     */
    public function onMessage(ConnectionInterface $conn, $msg)
    {
        # Get current connection
        if (!$connection = $this->getConnection($conn)) {
            return false;
        }

        # Validate the received package structure
        if (!$service = trim(substr($msg, 1, strpos($msg, "\n\n")))) {
            return false;
        }
        $package = trim(substr($msg, strpos($msg, "\n\n")));

        # Process the login Command
        if (substr($msg, 0, 13) === '/authenticate') {
            return $this->authenticate($connection, $package);
        }

        # Check or user is authorized to execute a command
        if (!$connection->authorized()) {
            return false;
        }

        # Check or service is valid
        if (substr($service, 0, 9) !== 'services/') {
            return false;
        }

        # Execute script
        $response = $package;
//        $pid = exec("vendor/bin/" . $service . " " . base64_encode($package) . "  > /dev/null 2>&1 & echo $!;");

        # Send data to all the open connections
        foreach ($this->connections as $c) {
            if ($c->authorized()) {
                $c->send($response);
            }
        }
        return true;
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

    protected function authenticate(Connection $connection, $package): bool
    {
        if (!self::tokenValid($package)) {
            $connection->close();
            return false;
        }
        $this->dump('- Authenticated :)', '1;32');
        $connection->authorize(true);
        return true;
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
        return '0000';//Config::get('socket')->security;
    }

    static public function tokenValid(string $token): bool
    {
        return $token === self::token();
    }
}
