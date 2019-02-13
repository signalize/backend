<?php

namespace Signalize\Socket;

use Ratchet\ConnectionInterface;

class Connection
{
    /** @var ConnectionInterface $connection */
    private $connection;
    /** @var bool $authorized */
    private $authorized = false;

    /**
     * Connection constructor.
     * @param ConnectionInterface $connection
     */
    public function __construct(ConnectionInterface $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @param ConnectionInterface $conn
     * @return bool
     */
    public function isConnection(ConnectionInterface $conn): bool
    {
        return $conn === $this->connection;
    }

    /**
     * @return bool
     */
    public function authorized(): bool
    {
        return $this->authorized;
    }

    /**
     * @param bool $value
     */
    public function authorize(bool $value)
    {
        $this->authorized = $value;
    }

    /**
     * @param string $data
     */
    public function send(string $data)
    {
        try {
            $this->connection->send($data);
        } catch (\Exception $e) {
        }
    }

    public function close()
    {
        $this->connection->close();
    }
}