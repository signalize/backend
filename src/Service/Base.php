<?php

namespace Signalize\Service;

use Composer\Script\Event;
use Signalize\Socket\Socket;
use WebSocket\Client;

use Signalize\Config;

abstract class Base
{
    /** @var Client $socket */
    private $socket;


    abstract public function worker();

    abstract static function converter(string $data): string;

    /**
     * Base constructor.
     * @throws \WebSocket\BadOpcodeException
     */
    public function __construct()
    {
        $this->socket = new Client('ws://127.0.0.1:' . Config::get('socket')->port);
        $this->socket->send("/authenticate\n\n" . Socket::token());
        if (!$this->socket->isConnected()) {
            throw new \Exception('Not possible to connect to the websocket!');
        }

        $this->worker();
    }

    /**
     * @param string $service
     * @param string $package
     * @return bool
     */
    protected function send(string $service, string $package)
    {
        if (!$this->socket->isConnected()) {
            $this->__construct();
        }
        $this->socket->send("/" . $this->getChannel() . "\n\n" . $package);
    }


    /**
     * @param \Composer\Script\Event $event
     */
    static public function Convert(\Composer\Script\Event $event)
    {
        try {
            $data = base64_decode(join('', $event->getArguments()));
            echo static::converter($data);
        } catch (\Exception $e) {
            var_dump($e->getMessage());
            var_dump($e->getTraceAsString());
        }
    }


    private function getChannel()
    {
        return 'services/module-p1';
    }


}