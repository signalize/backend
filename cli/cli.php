<?php
chdir(__DIR__);
if (php_sapi_name() !== 'cli') {
    die("This file can only be loaded into a CLI Enviroment!");
}

function findAutoloader($DIR)
{
    if ($DIR === '/') {
        die("Cannot find Autoload File!");
    }
    $FILE = $DIR . "/vendor/autoload.php";
    if (file_exists($FILE)) {
        chdir($DIR);
        return require($FILE);
    }
    return findAutoloader(dirname($DIR));
}

findAutoloader(dirname(__DIR__));