<?php

namespace Signalize\Modules\P1;

class Service extends \Signalize\Daemon\Service
{
    /**
     * Service constructor.
     * @throws \Exception
     */
    public function __construct()
    {
        parent::__construct();

        $device = new Device("/dev/ttyUSB0", 115200);
        $device->subscribe(function (Package $package) {
            $this->update($package);
        });
    }
}