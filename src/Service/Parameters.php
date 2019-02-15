<?php

namespace Signalize\Service;

/**
 * Class Parameters
 * @package Signalize\Service
 * @author Maikel ten Voorde <info@signalize.nl>
 */
class Parameters extends \ArrayIterator
{
    /**
     * Parameters constructor.
     * @param array $arguments
     * @param int $flags
     * @throws \Exception
     */
    public function __construct(array $arguments, int $flags = 0)
    {
        $array = [];
        foreach ($arguments as $argument) {
            if (substr($argument, 0, 2) === '--') {
                $seperation = explode("=", substr($argument, 2));
                $array[$seperation[0]] = $seperation[1];
            }
        }

        if (!isset($array['SID'])) {
            throw new \Exception("Cannot execute the service because the Session ID is missing!?", 401);
        }

        parent::__construct($array, $flags);
    }
}