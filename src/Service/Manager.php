<?php

namespace Signalize\Service;

use Signalize\Socket\Socket;

class Manager
{
    /** @var bool $loaded */
    private $loaded = false;
    /** @var array<string> */
    private $services = [];

    /**
     * Manager constructor.
     */
    public function __construct()
    {
        // Kill All Open Services
        foreach ($this->loadProcesses() as $pid => $service) {
            exec("kill -9 " . $pid . " > /dev/null 2>&1");
        }

        // Initialize services
        while (true) {
            $this->services = $this->loadProcesses();
            if (!$this->loaded) {
                $this->dump("START", getmypid(), 'Service Manager started!', '1;32');
            }

            $this->loadServices();

            $this->loaded = true;
            sleep(10);
        }
    }

    /**
     * Load all the Composer Bin Services
     */
    private function loadServices()
    {
        # Receive list of available service binary's
        exec("php composer.phar exec --list", $services);
        $services = array_filter($services, function ($row) {
            return (substr($row, 0, 1) === '-' && strpos($row, 'service-')) &&
                (!strpos($row, 'service-manager')) &&
                (!strpos($row, 'service-socket'));
        });

        array_unshift($services, "service-socket");
        foreach ($services as $service) {
            $service = str_replace("- ", "", $service);
            $this->loadService($service);
        }
    }

    /**
     * @param string $service
     * @return string
     */
    private function loadService(string $service): string
    {
        if (in_array($service, $this->services)) {
            return $service;
        }

        $pid = exec("vendor/bin/" . $service . " --SID=" . Socket::token(mktime()) . " --worker > /dev/null 2>&1 & echo $!;");
        sleep(1);
        $this->services = $this->loadProcesses();


        $msg = $service . "\t";
        if (strlen($msg) < 16) {
            $msg .= "\t";
        }

        if (!array_key_exists($pid, $this->services)) {
            $this->dump('FAIL', $pid, $msg, '1;31');
        } else {
            $this->dump('RUNNING', $pid, $msg, '1;32');
        }

        return $service;
    }

    /**
     * @return array
     */
    private function loadProcesses(): array
    {
        $processes = null;
        exec("ps aux | grep php", $processes);
        $processes = array_filter($processes, function ($row) {
            return strpos($row, 'vendor/bin/') && !strpos($row, 'vendor/bin/service-manager');
        });
        $response = [];
        foreach ($processes as $process) {
            $process = explode('vendor/bin/', $process);
            $pid = explode(" ", preg_replace('/\s+/', ' ', $process[0]));
            $service = explode(" ", $process[1]);
            $response[$pid[1]] = trim($service[0]);
        }
        ksort($response);
        return $response;
    }

    /**
     * @param string $status
     * @param string $pid
     * @param string $str
     * @param string $color
     */
    protected function dump(string $status, string $pid, string $str, string $color = '0')
    {
        echo "[\033[1;34m" . $pid . "\033[0m]\t\033[1;37m" . $str . "\033[0m" . "\t[\033[" . $color . "m" . $status . "\033[0m]" . PHP_EOL;
    }
}
