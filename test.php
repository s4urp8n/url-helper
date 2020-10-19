<?php

require "vendor/autoload.php";

ob_implicit_flush(true);
ini_set('implicit_flush', 1);
error_reporting(E_ALL);

$processes = \Zver\Common::getProcessesList();
$running = false;
$root = __DIR__ . DIRECTORY_SEPARATOR . 'root';

foreach ($processes as $process) {
    $process = \Zver\StringHelper::load($process);
    if ($process->isContainAll([
                                   'php -S',
                                   'localhost:55',
                                   '-t',
                                   $root,
                               ])) {
        $running = true;
    }
}

if (!$running) {
    echo "Running web-server\n";
    echo 'php -S localhost:55 -t "' . $root . '"', "\n";
    \Zver\Common::executeInSystemAsync('php -S localhost:55 -t "' . $root . '"');
    sleep(2);
}

echo passthru('.\vendor\bin\phpunit --bootstrap="tests/bootstrap.php"');
