<?php
require("cli.php");

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