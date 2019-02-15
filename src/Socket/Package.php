<?php

namespace Signalize\Socket;

/**
 * Class Package
 * @package Signalize\Socket
 * @author Maikel ten Voorde <info@signalize.nl>
 */
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