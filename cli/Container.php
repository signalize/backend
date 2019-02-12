<?php
chdir(__DIR__);
require("../vendor/autoload.php");

if (php_sapi_name() !== 'cli') {
    die("This file can only be loaded into a CLI Enviroment!");
}

$arguments = getopt('s:', ['service:']);
if (!isset($arguments['s'])) {
    die("Service is missing!");
}

$service = $arguments['s'];
if (!class_exists($service)) {
    die("Service does not exist! (" . $service . ")");
}

try {
    return new $service();
} catch (Exception $e) {
    die("Fail! (" . $e->getMessage() . ")" . PHP_EOL);
}