<?php

namespace Signalize\Signalize\cli;

use Signalize\Service\Manager;

chdir(__DIR__);
require("../vendor/autoload.php");
return new Manager();