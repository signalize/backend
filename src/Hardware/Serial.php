<?php

namespace Signalize\Hardware;

use Signalize\Socket\Package;

abstract class Serial
{
    private $fp;


    /**
     * @param string $chuck
     * @param string $buffer
     * @return mixed
     */
    abstract function process(string $chuck, string $buffer);

    /**
     * Serial constructor.
     * @param string $device
     * @param int $size
     */
    public function __construct(string $device)
    {
        $this->fp = fopen($device, "w+b");
    }

    /**
     * @param callable $method
     */
    public function subscribe(callable $method)
    {
        $buffer = null;
        while ($chuck = fgets($this->fp, 256)) {
            if (trim($chuck)) {
                $buffer .= $chuck;
                if ($package = $this->process($chuck, trim($buffer))) {
                    if (is_a($package, Package::class)) {
                        /** @var Package $package */
                        $method($package);
                        $buffer = null;
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