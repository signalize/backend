<?php

namespace Signalize\Service;

use WebSocket\Client;
use Signalize\Config;
use Signalize\Socket\Package;

abstract class Base
{
    /** @var Client $socket */
    private $socket;

    /**
     * Service constructor.
     * @throws \Exception
     */
    public function __construct()
    {
        $this->socket = new Client('ws://127.0.0.1:' . Config::get('socket')->port);
        if (!$this->socket->isConnected()) {
            throw new \Exception('Not possible to connect to the websocket!');
        }
    }

    /**
     * @param Package $package
     * @throws \Exception
     */
    protected function update(Package $package): void
    {
        if (!$this->socket->isConnected()) {
            $this->__construct();
        }
        $this->socket->send($package);
    }
}