<?php

namespace BeyondIT\OCOK\Tests\Installer;


use BeyondIT\OCOK\Helpers\Installer;

class InstallerTest extends \PHPUnit_Framework_TestCase {

    /**
     * @var \BeyondIT\OCOK\Helpers\Installer
     */
    protected $installer;

    protected $options;

    protected $oc_dir;

    public function setUp() {
        $this->oc_dir = getcwd() . "/vendor/opencart/";

        $this->installer = new Installer();
        $this->installer->init($this->oc_dir);

        $this->options = array(
            'db_hostname' => 'localhost' ,
            'db_username' => 'root' ,
            'db_password' => 'root' ,
            'db_database' => 'ocok_opencart_test' ,
            'db_prefix'   => '',
            'db_driver'   => 'mysqli',
            'email'       => 'test@ocok.com' ,
            'username'    => 'admin' ,
            'password'    => 'password' ,
            'http_server' => 'http://localhost/opencart/'
        );

    }

    public function testSettingUpTheDatabase() {
        $options = $this->options;
        $output = $this->installer->setupDatabase($options);

        if (!$output['check']) {
            echo $output['message'];
        }

        $this->assertTrue($output['check']);

        $this->installer->removeDatabase($options);
    }

    public function testWritingConfigFiles() {
        $options = $this->options;
        $output = $this->installer->writeConfigFiles($options);

        if (!$output['check']) {
            echo "ERROR: " . $output['message'];
        }

        $this->assertTrue($output['check']);
        $this->assertTrue(file_exists($this->oc_dir . "config.php"));
        $this->assertTrue(file_exists($this->oc_dir . "admin" . DIRECTORY_SEPARATOR . "config.php"));

        $this->installer->removeConfigFiles($options);
        $this->assertFalse(file_exists($this->oc_dir . "config.php"));
        $this->assertFalse(file_exists($this->oc_dir . "admin" . DIRECTORY_SEPARATOR . "config.php"));
    }

}
