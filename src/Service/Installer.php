<?php

namespace Signalize\Service;

use Signalize\Cache;
use Signalize\Socket\Package;

class Installer extends Base
{
    /** */
    public function worker()
    {
        $this->savePackages();
        sleep(60);
    }

    /**
     * @param Package $package
     * @return null|Package
     * @throws \Exception
     */
    public function execute(Package $package)
    {
        switch ($package->offsetGet('execute')) {
            # Retrieve list of available packages
            case "list":
                return $this->list(); // Send to sender

            # Install a Package
            case "install":
                $response = $this->install($package);
                $this->send($response); // Send to everybody else
                return $response; // Send to Sender

            # Uninstall a Package
            case "uninstall":
                $response = $this->uninstall($package);
                $this->send($response); // Send to everybody else
                return $response; // Send to Sender
        }
        throw new \Exception('Execute property is missing or command is not available.');
    }

    /**
     * @return Package
     */
    private function list(): Package
    {
        $packages = Cache::open('service-available-packages');
        return new Package($packages);
    }

    /**
     * @param Package $package
     * @return Package
     * @throws \Exception
     */
    private function install(Package $package): Package
    {
        if (!$package->offsetExists('package')) {
            throw new \Exception('Package property is missing!', 415);
        }

        # Check package is installable
        $packageName = $package->offsetGet('package');
        if (!$this->validPackage($packageName)) {
            throw new \Exception('Installation of this package is not accepted!', 415);
        }

        # Install the package
        exec("php composer.phar require " . $packageName . " 2>&1", $output);
        return new Package([
            "status" => "Installed",
            "package" => $packageName
        ]);
    }

    /**
     * @param Package $package
     * @return Package
     * @throws \Exception
     */
    private function uninstall(Package $package): Package
    {
        if (!$package->offsetExists('package')) {
            throw new \Exception('Package property is missing!', 415);
        }

        # Check package is uninstallable
        $packageName = $package->offsetGet('package');
        if (!$this->validPackage($packageName)) {
            throw new \Exception('Uninstallation of this package is not accepted!', 415);
        }

        # Install the package
        exec("php composer.phar remove " . $packageName . " 2>&1", $output);
        return new Package([
            "status" => "Not Installed",
            "package" => $packageName
        ]);
    }

    /**
     * @param string $packageName
     * @return bool
     */
    private function validPackage(string $packageName): bool
    {
        $availablePackages = $this->list()->getArrayCopy();
        return count(array_filter($availablePackages, function ($p) use ($packageName) {
                return $p->package === $packageName;
            })) > 0;
    }

    private function savePackages()
    {
        exec("php composer.phar search signalize -t signalize-module", $availablePackages);
        exec("php composer.phar show -N", $installedPackages);

        Cache::save('service-available-packages', array_map(function ($package) use ($installedPackages) {
            $packageName = trim(substr($package, strpos($package, " ")));
            $packageUrl = trim(substr($package, 0, strpos($package, " ")));
            return [
                "name" => $packageName,
                "package" => $packageUrl,
                "status" => (in_array($packageUrl, $installedPackages) ? "Installed" : "Not Installed")
            ];
        }, array_unique($availablePackages)));
    }
}
