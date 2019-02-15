<?php

namespace Signalize\Socket;

class Message
{
    /** @var string $service */
    private $service;
    /** @var Package $package */
    private $package;


    /**
     * Message constructor.
     * @param string $service
     * @param Package $package
     */
    public function __construct(string $service, Package $package)
    {
        $this->service = $service;
        $this->package = $package;
    }

    /**
     * @return string
     */
    public function service(): string
    {
        return $this->service;
    }

    /**
     * @return Package
     */
    public function package(): Package
    {
        return $this->package;
    }


    public function __toString()
    {
        return "/" . $this->service . "\r\n\r\n" . $this->package;
    }

}