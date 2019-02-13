<?php

namespace Signalize;
class Config
{
    /** @var null|object */
    static $config = null;

    static public function clear(): void
    {
        self::$config = null;
    }

    /**
     * @param callable $function
     * @return mixed
     */
    static public function load(callable $function)
    {
        if (!static::$config) {
            $file = getcwd() . "/config.json";
            if (file_exists($file) && $composer = file_get_contents($file)) {
                static::$config = json_decode($composer);
            }
        }
        return $function(static::$config);
    }

    /**
     * @param string $property
     * @return mixed
     */
    static public function get(string $property)
    {
        return static::load(function ($config) use ($property) {
            return $config->{$property};
        });
    }

    /**
     * @return array
     */
    static public function modules(): array
    {
        return [];
    }
}