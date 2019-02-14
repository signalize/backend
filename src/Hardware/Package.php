<?php

namespace Signalize\Hardware;

abstract class Package
{
    abstract protected function toArray(): array;

    public function __toString()
    {
        return json_encode($this->toArray());
    }
}