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


    /**
     * @return void
     */
    abstract public function worker();

    /**
     * @param string $data
     * @return void
     */
    abstract function execute(string $data);

    /**
     * Base constructor.
     * @throws \WebSocket\BadOpcodeException
     */
    public function __construct()
    {
        # Check Arguments is Available
        if (!isset($_SERVER['argv'][1])) {
            die("Cannot execute the service. Maybe you forgot some arguments?");
        }
        $property = $_SERVER['argv'][1];

        # Setup the WebSocket Connection
        $this->socket = new Client('ws://127.0.0.1:' . Config::get('socket')->port);
        $this->socket->send("/authenticate\n\n" . Socket::token());
        if (!$this->socket->isConnected()) {
            die('Not possible to connect to the websocket!');
        }

        # Check wich command has to be executed
        switch (true) {
            # Run the worker
            case ($property === "--worker"):
                $this->worker();
                break;
            # Execute, to process input
            case (substr($property, 0, 7) === '--data='):
                $data = base64_decode(substr($property, 7));
                $this->execute($data);
                break;
        }
    }

    /**
     * @param string $command
     * @param mixed $package
     * @return bool
     */
    protected function send($package)
    {
        if (!$this->socket->isConnected()) {
            $this->__construct();
        }

        if (is_array($package) || is_object($package)) {
            $package = json_encode($package);
        }

        $this->socket->send("/" . basename($_SERVER['SCRIPT_NAME']) . "\n\n" . $package);
    }
}