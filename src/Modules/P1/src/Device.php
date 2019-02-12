<?php

namespace Signalize\Modules\P1;

use Signalize\Hardware\Serial as Serial;

class Device extends Serial
{
    public function process($chuck, $buffer)
    {
        if (substr($buffer, 0, 1) === '/') {
            if (substr($chuck, 0, 1) === '!') {
                return new Package($buffer);
            }
            return true;
        }
        return false;
    }
}