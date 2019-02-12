<?php

namespace Signalize;
class Config
{
    static $config;

    static public function clear()
    {
        self::$config = false;
    }

    static public function get($property)
    {
        if (!self::$config) {
            $file = dirname(__DIR__) . "/config.json";
            if (file_exists($file) && $config = file_get_contents($file)) {
                self::$config = json_decode($config);
            }
        }
        return self::$config->{$property};
    }
}