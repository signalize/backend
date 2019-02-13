<?php

namespace Signalize;
class Config
{
    static $config = null;

    static public function clear()
    {
        self::$config = null;
    }

    static public function load($function)
    {
        if (!static::$config) {
            $file = getcwd() . "/config.json";
            if (file_exists($file) && $composer = file_get_contents($file)) {
                static::$config = json_decode($composer);
            }
        }
        return $function(static::$config);
    }

    static public function get($property)
    {
        return static::load(function ($config) use ($property) {
            return $config->{$property};
        });
    }

    static public function modules()
    {
        return [];
    }
}