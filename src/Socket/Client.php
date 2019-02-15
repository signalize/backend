<?php

namespace Signalize\Socket;

use Signalize\Config;

class Client extends \WebSocket\Client
{

    /**
     * Client constructor.
     * @param string $SID
     * @param string $type
     * @param array $options
     */
    public function __construct(string $SID, string $type = null, array $options = array())
    {
        $path = 'ws://127.0.0.1:' . Config::get('socket')->port . "/?SID=" . $SID;
        if ($type) {
            $path .= "&type=" . $type;
        }
        parent::__construct($path, $options);
    }

    /**
     * @param Package $package
     * @throws \WebSocket\BadOpcodeException
     */
    public function push(Package $package)
    {
        $payload = new Message(basename($_SERVER['SCRIPT_NAME']), $package);
        parent::send($payload);
    }

}