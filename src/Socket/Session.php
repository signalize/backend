<?php

namespace Signalize\Socket;

use Signalize\Config;

class Session
{
    /** @var string */
    private $id;
    /** @var string */
    private $token;
    /** @var string */
    private $time;
    /** @var \stdClass */
    private $user;

    /**
     * Session constructor.
     * @param Connection $connection
     * @throws \Exception
     */
    public function __construct(Connection $connection)
    {
        $this->id = $connection->parameters->offsetGet('SID');
        if (strpos($this->id, '.')) {
            $seperation = explode('.', $this->id);
            $this->token = $seperation[0];
            $this->time = $seperation[1];
            return $this->validate();
        }
        throw new \Exception('Invalid Session-ID!', 401);
    }

    /**
     * @return bool
     * @throws \Exception
     */
    public function validate()
    {
        $deviceToken = Socket::token($this->time);
        if ($this->id === $deviceToken) {
            return true;
        }

        Config::clear();
        if ($credentials = Config::get('credentials')) {
            foreach ($credentials as $credential) {
                $hash = strtoupper(sha1($credential->username . "::" . $credential->password . "::" . $this->time));
                if ($this->token === $hash) {
                    $this->user = $credential;
                    return true;
                }
            }
        }

        throw new \Exception('Invalid Session-ID or Session is expired!', 401);
    }


    /**
     * @return string
     */
    public function id(): string
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function token(): string
    {
        return $this->token;
    }

    /**
     * @return string
     */
    public function time(): string
    {
        return $this->time;
    }

    /**
     * @return \stdClass
     */
    public function user(): \stdClass
    {
        return $this->user;
    }
}