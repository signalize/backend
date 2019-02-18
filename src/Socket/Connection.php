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

    public $subscriptions = [];

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

    public function subscribe($service)
    {
        if (!$this->subscriptions[$service]) {
            $this->connection->send('Subscribed to Service [' . $service . ']');
        }
        $this->subscriptions[$service] = true;
    }

    public function unsubscribe($service)
    {
        if ($this->subscriptions[$service]) {
            $this->connection->send('Unsubscribed from Service [' . $service . ']');
        }
        $this->subscriptions[$service] = false;
    }

    /**
     * @param Message $message
     */
    public function send(Message $message)
    {
        if (array_key_exists($message->service(), $this->subscriptions)) {
            $this->connection->send($message);
        }
    }

    /**
     *
     */
    public function close()
    {
        $this->connection->close();
    }
}