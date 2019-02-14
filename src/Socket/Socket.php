<?php

namespace Signalize\Socket;

use Ratchet\ConnectionInterface;
use Ratchet\Http\HttpServer;
use Ratchet\MessageComponentInterface;
use Ratchet\Server\IoServer;
use Ratchet\WebSocket\WsServer;
use Signalize\Config;
use WebSocket\Client;

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
    public function onOpen(ConnectionInterface $conn): bool
    {
        $connection = new Connection($conn);
        $this->connections->attach($connection);
        $this->dump('Connection Established.', '1;32');
        return true;
    }

    /**
     * @param ConnectionInterface $conn
     * @param string $msg
     * @return bool
     */
    public function onMessage(ConnectionInterface $conn, $msg): bool
    {
        # Get current connection
        if (!$connection = $this->getConnection($conn)) {
            return false;
        }

        # Validate the received package structure
        if (!$msg = $this->decode($msg)) {
            return false;
        }

        # Process the login Command
        if ($msg['service'] === 'authenticate') {
            return $this->authenticate($connection, $msg['package']);
        }

        # Check or user is authorized to execute a command
        if (!$connection->authorized()) {
            return false;
        }

        # Check or service is valid
        if (in_array($msg['service'], ['service-socket', 'service-manager'])) {
            return false;
        }

        # Execute script
        if (strpos($msg['service'], 'execute:') !== false) {
            $service = substr($msg['service'], strpos($msg['service'], ':') + 1);
            if (file_exists("vendor/bin/" . $service)) {
                $this->dump('Execute command: ' . $service, '1;32');
                exec("vendor/bin/" . $service . " --data=" . base64_encode($msg['package']) . " > /dev/null 2>&1 & echo $!;");
                return true;
            }
        }

        /** @var Connection $c */
        foreach ($this->connections as $c) {
            if ($c->authorized()) {
                $c->send($msg['service'], $msg['package']);
            }
        }


        return true;
    }

    /**
     * @param ConnectionInterface $conn
     * @param \Exception $e
     */
    public function onError(ConnectionInterface $conn, \Exception $e): bool
    {
        //
    }

    /**
     * @param ConnectionInterface $conn
     */
    public function onClose(ConnectionInterface $conn): bool
    {
        foreach ($this->connections as $connection) {
            if ($connection->isConnection($conn)) {
                $this->connections->detach($connection);
                $this->dump('Connection Closed.', '1;31');
                return true;
            }
        }
        return false;
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
     * @param string $msg
     * @return array|bool
     */
    private function decode(string $msg)
    {
        $seperator = (strpos($msg, "\r\n\r\n") ? "\r\n\r\n" : "\n\n");
        if (strpos($msg, $seperator) && strpos($msg, $seperator) < 32) {
            return [
                'service' => trim(substr($msg, 1, strpos($msg, $seperator))),
                'package' => trim(substr($msg, strpos($msg, $seperator)))
            ];
        }
        return false;
    }

    /**
     * @param Connection $connection
     * @param string $token
     * @return bool
     */
    protected function authenticate(Connection $connection, string $token): bool
    {
        if (!self::tokenValid($token)) {
            $connection->send('authenticate', "401 Unauthorized");
            $connection->close();
            return false;
        }
        $this->dump('- Authenticated :)', '1;32');
        $connection->authorize(true);
        $connection->send('authenticate', "200 Authentication completed");

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
        return md5(date("Ymd") . Config::get('socket')->security);
    }

    /**
     * @param string $token
     * @return bool
     */
    static public function tokenValid(string $token): bool
    {
        # Check token is device
        if ($token === self::token()) {
            return true;
        }

        # Check token is a valid credential
        if ($credentials = Config::get('credentials')) {
            foreach ($credentials as $credential) {
                if ($token === md5(date("Ymd") . ":" . $credential->username . ":" . $credential->password)) {
                    return true;
                }
            }
        }

        return false;
    }
}
