<?php

namespace Signalize\Modules\P1;

class Package extends \Signalize\Daemon\Package
{
    public function __construct($package)
    {
        $this->package = explode("\r\n", $package);
    }

    public function getUsage()
    {
        return [
            "meter1" => $this->getValue(4),
            "meter2" => $this->getValue(5)
        ];
    }

    public function getReturn()
    {
        return [
            "meter1" => $this->getValue(6),
            "meter2" => $this->getValue(7)
        ];
    }

    public function getGas()
    {
        return $this->getValue(33, 1);
    }

    private function getValue($index, $cursor = 0)
    {
        $values = explode("(", $this->package[$index]);
        array_shift($values);

        $values = array_map(function ($value) {
            return substr($value, 0, strpos($value, ")"));
        }, $values);

        return floatval($values[$cursor]);
    }


    public function toArray()
    {
        return [
            'electricity' => [
                'usage' => $this->getUsage(),
                'result' => $this->getReturn()
            ],
            'gas' => $this->getGas(),
        ];
    }
}
