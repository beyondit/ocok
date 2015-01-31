<?php
/**
 * Created by PhpStorm.
 * User: stefan
 * Date: 28.01.15
 * Time: 20:40
 */

namespace BeyondIT\OCOK\Tests\BackupCommand;


use BeyondIT\OCOK\BackupCommand;
use BeyondIT\OCOK\Helpers\DummyConfig;
use BeyondIT\OCOK\Helpers\Installer;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class BackupCommandTest extends \PHPUnit_Framework_TestCase {

    /**
     * @var \Symfony\Component\Console\Application
     */
    protected $application;

    /**
     * @var \BeyondIT\OCOK\Helpers\Installer
     */
    protected $installer;

    protected $execDir;


    public function setUp() { // $this->markTestSkipped("Constants already defined problems");
        $this->application = new Application();
        $this->application->add(new BackupCommand());

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
    }

    public function tearDown() {
        $this->installer->removeDatabase($this->options);
        $this->installer->removeConfigFiles();
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testBackingUpCatalogImagesAndDatabase() {
        chdir($this->execDir);

        $command = $this->application->find('backup');

        $commandTester = new CommandTester($command);
        $commandTester->execute(array(
            '--images' => null,
            '--database' => null));

        foreach (glob("ocok_backup_".date("Y_m_d_")."*.zip") as $file) {
            $this->assertGreaterThan(0,filesize($file));
            unlink($file);
            $this->assertFalse(is_file($file));

        }
    }
}