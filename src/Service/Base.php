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

    abstract protected function worker();

    abstract static function converter(string $data): string;

    /**
     * Base constructor.
     * @param bool $loadSocket
     * @throws \WebSocket\BadOpcodeException
     */
    public function __construct($loadSocket = true)
    {
        if ($loadSocket) {
            $this->connect();
        }
    }

    /**
     * @param \Composer\Script\Event $event
     */
    static public function Work(\Composer\Script\Event $event)
    {
        try {
            $instance = new static();
            echo $instance->worker();
        } catch (\Exception $e) {
            var_dump($e->getMessage());
            var_dump($e->getTraceAsString());
        }
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

    /**
     * @param string $service
     * @param string $package
     * @return bool
     */
    protected function send(string $service, string $package)
    {
        try {
            if (!$this->socket || !$this->socket->isConnected()) {
                $this->connect();
            }
            $this->socket->send("/" . $service . "\n\n" . $package);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    private function connect()
    {
        $this->socket = new Client('ws://127.0.0.1:' . Config::get('socket')->port);
        $this->socket->send("/authenticate\n\n" . Socket::token());
        if (!$this->socket->isConnected()) {
            throw new \Exception('Not possible to connect to the websocket!');
        }
    }
}