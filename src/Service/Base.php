<?php

namespace Signalize\Service;

use Signalize\Socket\Client;
use Signalize\Socket\Package;

abstract class Base
{
    /** @var array */
    protected $parameters;

    /**
     * @return void
     */
    abstract public function worker();

    /**
     * @param Package $package
     * @return Package|mixed
     */
    abstract function execute(Package $package);

    /**
     * Base constructor.
     */
    public function __construct()
    {
        try {
            $this->parameters = new Parameters($_SERVER['argv']);
            switch (true) {
                case $this->parameters->offsetExists('worker'):
                    $this->worker();
                    break;
                case $this->parameters->offsetExists('package'):
                    try {
                        $package = base64_decode($this->parameters->offsetGet('package'));
                        if (($package = json_decode($package)) !== null) {
                            $package = new Package($package);
                            if ($response = $this->execute($package)) {
                                $this->respond($response);
                            }
                        }
                    } catch (\Exception $e) {
                        $this->respond(new Package([
                            'code' => $e->getCode(),
                            'message' => $e->getMessage()
                        ]));
                    }
                    break;
            }

        } catch (\Exception $e) {
            die($e->getMessage());
        }
    }

    /**
     * @param Package $package
     * @throws \WebSocket\BadOpcodeException
     */
    protected function send(Package $package)
    {
        $socket = new Client($this->parameters->offsetGet('SID'), 'push');
        $socket->push($package);
        $socket->close();
    }


    /**
     * @param Package $response
     * @throws \WebSocket\BadOpcodeException
     */
    protected function respond(Package $response)
    {
        $socket = new Client($this->parameters->offsetGet('SID'), 'response');
        $socket->push($response);
        $socket->close();
    }
}