<?php

namespace Signalize;

class Cache
{
    /**
     * @param string $filename
     * @param mixed $data
     */
    static public function save($filename, $data)
    {
        if (is_object($data) || is_array($data)) {
            $data = json_encode($data);
        }
        file_put_contents(self::path($filename), $data);
    }

    /**
     * @param string $filename
     * @return mixed
     */
    static public function open($filename)
    {
        $data = file_get_contents(self::path($filename));
        if ($json = @json_decode($data)) {
            return $json;
        }
        return json_decode("{}");
    }

    static public function set($filename, $property, $value)
    {
        $file = self::open($filename);
        $file->{$property} = $value;
        self::save($filename, $file);
    }

    static public function get($filename, $property)
    {
        $file = self::open($filename);
        return $file->{$property};
    }


    /**
     * @param string $filename
     * @return string
     */
    static public function path($filename)
    {
        $path = 'cache/' . $filename;
        if (!file_exists('cache/' . $filename)) {
            if (!file_exists('cache')) {
                mkdir('cache');
            }
            file_put_contents($path, null);
        }
        return $path;
    }
}