<?php

namespace BeyondIT\OCOK\Tests\CliTaskCommand;


use BeyondIT\OCOK\Helpers\DummyConfig;
use BeyondIT\OCOK\Helpers\FileSystem;
use BeyondIT\OCOK\CliTaskCommand;
use BeyondIT\OCOK\Helpers\Installer;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

// needs global $config variable
// for preventing strange problem with php global call
$config = new DummyConfig();

class CliTaskCommandTest extends \PHPUnit_Framework_TestCase {

    /**
     * @var \BeyondIT\OCOK\Helpers\Installer
     */
    protected $installer;

    /**
     * @var \BeyondIT\OCOK\Helpers\FileSystem
     */
    protected $fsh;

    protected $test_controller = "/tests/BeyondIT/OCOK/Tests/Helpers/TestController.php";
    protected $oc_controller_dir = "/vendor/opencart/admin/controller/test/";
    protected $controller_file_name = "test.php";
    protected $execDir;

    public function setUp() {
        $this->application = new Application();
        $this->application->add(new CliTaskCommand());

        $dir = getcwd();
        $this->execDir = getcwd() . "/vendor/opencart";

        $this->installer = new Installer($this->execDir);
        $this->options = array(
            'db_hostname' => 'localhost' ,
            'db_username' => 'root' ,
            'db_password' => 'root' ,
            'db_database' => 'ocok_opencart_test' ,
            'db_prefix'   => 'oc_',
            'db_driver'   => 'mysqli',
            'email'       => 'test@ocok.com' ,
            'username'    => 'admin' ,
            'password'    => 'password' ,
            'http_server' => 'http://localhost/opencart/'
        );
        $this->installer->install($this->options);

        $this->fsh = new FileSystem();
        $this->oc_controller_dir = $dir . $this->oc_controller_dir;
        $this->test_controller = $dir . $this->test_controller;

        if (!is_dir($this->oc_controller_dir)) {
            mkdir($this->oc_controller_dir);
        }

        copy($this->test_controller,$this->oc_controller_dir . $this->controller_file_name);
    }

    public function tearDown() {
        if (is_dir($this->oc_controller_dir)) {
            $this->fsh->rmdir($this->oc_controller_dir);
        }

        $this->installer->removeDatabase($this->options);
        $this->installer->removeConfigFiles();
    }

    public function testTestCommandExecution() {
        chdir($this->execDir);
        $command = $this->application->find('run');

        $commandTester = new CommandTester($command);

        if (defined("HTTP_SERVER")) {
            echo "is defined already";
        }
        $commandTester->execute(array('route' => 'test/test'));

        $this->assertRegExp("/^.*Called by CLI*$/",$command->registry->get('test_output'));
    }

}
