<?php

namespace Signalize;
class Database
{
    /** @var string $database */
    static $database;

    /** @var \MongoLite\Database $database */
    static $_databaseInstance;


    static private function connect()
    {
        static::$_databaseInstance = (new \MongoLite\Client("databases"))->moduleP1;
    }

    /**
     * @param $collection
     * @param $data
     * @return mixed
     */
    static public function store($collection, $data)
    {
        if (!static::$_databaseInstance) {
            static::connect();
        }
        return static::$_databaseInstance->{$collection}->save($data);
    }

    /**
     * @param string $collection
     * @return \MongoLite\Collection|object
     */
    static public function collection($collection)
    {
        if (!static::$_databaseInstance) {
            static::connect();
        }
        return static::$_databaseInstance->{$collection};
    }
}