<?php

namespace Signalize\Socket;

use Ratchet\ConnectionInterface;

class Connection
{
    /** @var ConnectionInterface $connection */
    public $connection;

    /** @var Session $session */
    public $session = null;

    /** @var Parameters $parameters */
    public $parameters = null;

    /**
     * Connection constructor.
     * @param ConnectionInterface $connection
     * @throws \Exception
     */
    public function __construct(ConnectionInterface $connection)
    {
        $this->connection = $connection;
        $this->parameters = new Parameters($connection);
        $this->session = new Session($this);
    }

    /**
     * @param ConnectionInterface $conn
     * @return bool
     */
    public function isConnection(ConnectionInterface $conn)
    {
        return ($this->connection === $conn);
    }

    public function sameAs(Connection $connection)
    {
        return $this->session->id() === $connection->session->id();
    }

    /**
     * @param Message $message
     */
    public function send(Message $message): void
    {
        $this->connection->send($message);
    }

    /**
     *
     */
    public function close(): void
    {
        $this->connection->close();
    }
}