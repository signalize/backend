<?php

namespace Signalize\Service;

use Signalize\Config;

class Manager
{
    private $loaded = false;
    private $services = [];

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

            $validServices = [];
            Config::clear();
            $this->loadSocket($validServices);
            $this->loadModules($validServices);
            $this->validateServices($validServices);

            $this->loaded = true;
            sleep(1);
        }
    }

    private function loadSocket(&$validServices)
    {
        $service = "\\Signalize\\Service\\Socket";
        if (class_exists($service)) {
            $this->loadService($service);
            $validServices[] = $service;
        }
    }

    private function loadModules(&$validServices)
    {
        foreach (Config::get('modules') as $module => $options) {
            $service = "\\Signalize\\Modules\\" . $module . "\\Service";
            if (class_exists($service)) {
                $this->loadService($service);
                $validServices[] = $service;
            }
        }
    }

    private function validateServices(&$validServices)
    {
        $offset = 0;
        foreach ($this->services as $pid => $service) {
            if (!in_array($service, $validServices)) {
                $this->dump('STOP', $pid, $service, '1;31');
                exec("kill -9 " . $pid);
            } else if (count(array_keys($this->services, $service)) > 1) {
                $this->dump('DUPLICATE', $pid, $service, '1;31');
                exec("kill -9 " . $pid . " > /dev/null 2>&1");
                unset($this->services[$pid]);
            }
            $offset++;
        }
    }


    private function loadService($service)
    {
        if (in_array($service, $this->services)) {
            return $service;
        }

        $cmd = str_replace("\\", "\\\\", $service);
        $pid = exec("php " . dirname(dirname(__DIR__)) . "/cli/service-container.php -s " . $cmd . "  > /dev/null 2>&1 & echo $!;");

        sleep(1);

        $this->services = $this->loadProcesses();
        if (!array_key_exists($pid, $this->services)) {
            $this->dump('FAIL', $pid, $service, '1;31');
        } else {
            $this->dump('RUNNING', $pid, $service, '1;32');
        }

        return $service;
    }

    private function loadProcesses()
    {
        $processes = null;
        exec("ps aux", $processes);
        $processes = array_filter($processes, function ($row) {
            return strpos($row, dirname(dirname(__DIR__)) . '/cli/service-container.php -s');
        });

        $response = [];
        foreach ($processes as $process) {
            $process = explode(dirname(dirname(__DIR__)) . "/cli/service-container.php -s", $process);
            $pid = explode(" ", preg_replace('/\s+/', ' ', $process[0]));
            $response[$pid[1]] = trim($process[1]);
        }
        ksort($response);
        return $response;
    }

    protected function dump($status, $pid, $str, $color = 0)
    {
        echo "[\033[1;34m" . $pid . "\033[0m]\t\033[1;37m" . $str . "\033[0m" . "\t[\033[" . $color . "m" . $status . "\033[0m]" . PHP_EOL;
    }
}
