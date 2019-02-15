<?php

namespace Signalize\Socket;

use Ratchet\ConnectionInterface;
use Ratchet\Http\HttpServer;
use Ratchet\MessageComponentInterface;
use Ratchet\Server\IoServer;
use Ratchet\WebSocket\WsServer;
use Signalize\Config;

class Socket implements MessageComponentInterface
{
    /** @var \SplObjectStorage<Connection> */
    private $connections;

    /**
     * Socket constructor.
     * @param $port
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
        try {
            $connection = new Connection($conn);
            $this->connections->attach($connection);
            $this->dump('Connection Established.', '1;32');
        } catch (\Exception $e) {
            $this->dump($e->getMessage(), '1;31');
            $conn->send(
                new Message('error', new Package([
                    'code' => $e->getCode(),
                    'message' => $e->getMessage()
                ]))
            );
            $conn->close();
        }
    }

    /**
     * @param ConnectionInterface $conn
     * @param string $msg
     * @return void
     */
    public function onMessage(ConnectionInterface $conn, $msg)
    {
        try {
            if ($msg === 'HELO') {
                $conn->send("HELO");
                return;
            }
            # Get current connection
            $connection = $this->getConnection($conn);

            # Validate current session
            $connection->session->validate();

            # Validate and decode the received package
            $msg = $this->decode($msg);


            $type = $connection->parameters->offsetGet('type');
            switch ($type) {
                case "response":
                    /** @var Connection $conn */
                    foreach ($this->connections as $conn) {
                        if ($conn->sameAs($connection)) {
                            $conn->send($msg);
                        }
                    }
                    break;
                case "push":
                    /** @var Connection $conn */
                    foreach ($this->connections as $conn) {
                        if (!$conn->sameAs($connection)) {
                            $conn->send($msg);
                        }
                    }
                    break;
                default:
                    # Check or service is valid
                    if (!in_array($msg->service(), ['service-socket', 'service-manager'])) {
                        # Execute command, if exists
                        if (file_exists("vendor/bin/" . $msg->service())) {
                            $this->dump('Execute command: ' . $msg->service(), '1;32');
                            exec("vendor/bin/" . $msg->service() . " --SID=" . $connection->session->id() . " --package=" . base64_encode($msg->package()) . " > /dev/null 2>&1 & echo $!;");
                            return;
                        }
                    }
                    throw new \Exception('Command not found!');
                    break;
            }
        } catch (\Exception $e) {
            $this->dump($e->getMessage(), '1;31');
            $conn->send(
                new Message('error', new Package([
                    'code' => $e->getCode(),
                    'message' => $e->getMessage()
                ]))
            );
            if (in_array($e->getCode(), [401, 500])) {
                $conn->close();
            }
        }
    }

    /**
     * @param ConnectionInterface $conn
     * @param \Exception $e
     * @return bool
     */
    public function onError(ConnectionInterface $conn, \Exception $e): bool
    {
        //
        return false;
    }

    /**
     * @param ConnectionInterface $conn
     * @return bool
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
     * @throws \Exception
     */
    private function getConnection(ConnectionInterface $conn): Connection
    {
        /** @var Connection $connection */
        foreach ($this->connections as $connection) {
            if ($connection->isConnection($conn)) {
                return $connection;
            }
        }
        throw new \Exception('Cannot find Connection', 500);
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
     * @param string $msg
     * @return Message
     * @throws \Exception
     */
    private function decode(string $msg): Message
    {
        $seperator = (strpos($msg, "\r\n\r\n") ? "\r\n\r\n" : "\n\n");
        if (strpos($msg, $seperator) && strpos($msg, $seperator) < 32) {
            $service = trim(substr($msg, 1, strpos($msg, $seperator)));
            $rawPackage = trim(substr($msg, strpos($msg, $seperator)));

            if (($package = json_decode($rawPackage)) !== null) {
                return new Message($service, new Package($package));
            }
        }
        throw new \Exception('Cannot read message!', 415);
    }

    /**
     * @param string $time
     * @return string
     */
    static public function token(string $time): string
    {
        return strtoupper(sha1(Config::get('socket')->security . "::" . $time)) . "." . $time;
    }

}
