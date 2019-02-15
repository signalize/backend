<?php

namespace Signalize\Socket;

use Psr\Http\Message\RequestInterface;
use Ratchet\ConnectionInterface;

/**
 * Class Parameters
 * @package Signalize\Socket
 * @author Maikel ten Voorde <info@signalize.nl>
 */
class Parameters extends \ArrayIterator
{
    /**
     * Parameters constructor.
     * @param ConnectionInterface $connection
     * @param int $flags
     * @throws \Exception
     */
    public function __construct(ConnectionInterface $connection, $flags = 0)
    {
        if (!isset($connection->httpRequest)) {
            throw new \Exception("Not able to setup the connection for the websocket!", 500);
        }
        /** @var RequestInterface $request */
        $request = $connection->httpRequest;
        parse_str($request->getUri()->getQuery(), $data);
        parent::__construct($data, $flags);

        if (!$this->offsetExists('SID')) {
            throw new \Exception('Unable to connect to the socket! The Session-Id is missing!', 401);
        }
    }
}