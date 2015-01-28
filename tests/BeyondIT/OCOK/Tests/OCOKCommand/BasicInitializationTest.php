<?php

namespace BeyondIT\OCOK\Tests\OCOKCommand;

use BeyondIT\OCOK\PHPDocCommand;
use BeyondIT\OCOK\CliTaskCommand;
use BeyondIT\OCOK\InfoCommand;
use BeyondIT\OCOK\BackupCommand;
use BeyondIT\OCOK\OCOKCommand;
use BeyondIT\OCOK\Helpers\DummyConfig;
use BeyondIT\OCOK\Tests\Helpers\TestOCOKCommand;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class BasicInitializationTest extends \PHPUnit_Framework_TestCase {

    /**
     * @var \Symfony\Component\Console\Application
     */
    protected $application;

    protected $execDir;

    public function setUp() {
        $config = new DummyConfig();
        $this->application = new Application();
        $this->application->add(new CliTaskCommand);

        $this->execDir = getcwd() . "/vendor/opencart";
    }

    public function testCommandInsideWrongDirectory() {
        $command = $this->application->find('run');
        $commandTester = new CommandTester($command);
        $commandTester->execute(
            array('command' => $command->getName() ,
                  'route' => 'catalog/attribute')
        );

        $this->assertRegExp("/^.*ERROR: No Opencart installation found!.*$/",$commandTester->getDisplay());
    }

    public function testCheckWithUninstalledOpenCart() {
        chdir($this->execDir);

        $command = new TestOCOKCommand("testCMD");
        $this->assertFalse($command->checkOC());
    }

    public function testCheckWithFakedInstalledOpenCart() {
        chdir($this->execDir);

        touch("config.php");
        touch("admin/config.php");

        $command = new TestOCOKCommand("testCMD");
        $this->assertTrue($command->checkOC());

        unlink("config.php");
        unlink("admin/config.php");
    }

    public function testCheckVersionOfOpenCart() {
        chdir($this->execDir);

        $command = new TestOCOKCommand("testCMD");
        $version = $command->loadVersion($this->execDir);

        $this->assertEquals("2.0.1.1",$version);
    }

    public function testCheckVersionSupportCases() {
        $command = new TestOCOKCommand("testCMD");


        $command->supported_versions = array("2.0.1.0");

        $command->setVersion("2.0.1.1");
        $this->assertFalse($command->isVersionSupported());

        $command->setVersion("2.0.1.0");
        $this->assertTrue($command->isVersionSupported());


        $command->supported_versions = array("1.5","2.0");

        $command->setVersion("2.0.1.1");
        $this->assertTrue($command->isVersionSupported());

        $command->setVersion("1.5.6.4");
        $this->assertTrue($command->isVersionSupported());

        $command->setVersion("2.1.0.0");
        $this->assertFalse($command->isVersionSupported());

        $command->setVersion("1.4");
        $this->assertFalse($command->isVersionSupported());
    }

}
