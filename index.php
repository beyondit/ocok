<?php

// add namespace to Composer autoloader
$loader = require __DIR__.'/vendor/autoload.php';
$loader->add('BeyondIT\\OCOK',__DIR__.'/src/');

class DummyConfig {
    public function get($name) {
        return false;
    }
}
$config = new DummyConfig();

use BeyondIT\OCOK\PHPDocCommand;
use BeyondIT\OCOK\CliTaskCommand;
use BeyondIT\OCOK\InfoCommand;
use BeyondIT\OCOK\BackupCommand;

use Symfony\Component\Console\Application;

$application = new Application();

$application->setName("OpenCart OK Command Line Utilities");

$application->add(new PHPDocCommand);
$application->add(new CliTaskCommand);
$application->add(new BackupCommand);
$application->add(new InfoCommand);

$application->setDefaultCommand('info');
$application->run();