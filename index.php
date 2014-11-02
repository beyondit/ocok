<?php

// add namespace to Composer autoloader
require_once __DIR__.'/vendor/autoload.php';

use BeyondIT\OCOK\PHPDocCommand;
use BeyondIT\OCOK\CliTaskCommand;
use BeyondIT\OCOK\InfoCommand;
use BeyondIT\OCOK\BackupCommand;
use BeyondIT\OCOK\DummyConfig;
use Symfony\Component\Console\Application;

$config = new DummyConfig();

$application = new Application();

$application->setName("OpenCart OK Command Line Utilities");

$application->add(new PHPDocCommand);
$application->add(new CliTaskCommand);
$application->add(new BackupCommand);
$application->add(new InfoCommand);

$application->setDefaultCommand('info');
$application->run();