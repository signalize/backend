<?php

namespace Signalize\Service;

use Signalize\Config;
use WebSocket\Client;

abstract class Service
{
    private $socket;

    /**
     * Service constructor.
     * @throws \Exception
     */
    public function __construct()
    {
        $this->socket = new Client('ws://127.0.0.1:'.Config::get('socket')->port.'/');
        $this->socket->send('MACHINE:' . Socket::Machine());
        if (!$this->socket->isConnected()) {
            throw new \Exception('Not possible to connect to the websocket!');
        }
    }

    /**
     * @param Package $package
     * @throws \Exception
     */
    protected function update(Package $package)
    {
        if (!$this->socket->isConnected()) {
            $this->__construct();
        }
        $this->socket->send($package);
    }
}