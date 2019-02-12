<?php

namespace Signalize\Service;

abstract class Package
{
    protected $package;

    abstract function toArray();

    public function __toString()
    {
        return json_encode($this->toArray());
    }
}