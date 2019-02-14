<?php

namespace Signalize\Hardware;

abstract class Serial
{
    private $fp;
    private $size;


    abstract function process($chuck, $buffer): Package;


    public function __construct($device, $size)
    {
        $this->fp = fopen($device, "r+");
        $this->size = $size;
    }

    public function subscribe($method)
    {
        $buffer = null;
        while ($chuck = fgets($this->fp, $this->size)) {
            if (trim($chuck)) {
                $buffer .= $chuck;
                if ($package = $this->process($chuck, trim($buffer))) {
                    if (is_a($package, Package::class)) {
                        $method($package);
                    }
                } else {
                    $buffer = null;
                }
            }
        }
    }

    public function __destruct()
    {
        fclose($this->fp);
    }
}