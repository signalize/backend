<?php

namespace Signalize\Socket;

class Package extends \ArrayIterator
{
    /**
     * @return string
     */
    public function __toString()
    {
        return json_encode($this);
    }
}