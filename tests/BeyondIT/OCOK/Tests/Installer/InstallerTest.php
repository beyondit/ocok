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

        $this->installer = new Installer($this->oc_dir);

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

        $this->installer->removeDatabase($this->options);
    }

    public function testInstallingOpenCart() {
        $options = $this->options;
        $output = $this->installer->install($options);

        $this->assertEquals("SUCCESS! Opencart successfully installed on your server",$output[0]);
        $this->assertEquals("Store link: " . $options['http_server'],$output[1]);
        $this->assertEquals("Admin link: " . $options['http_server'] . "admin/",$output[2]);

        $this->assertTrue(file_exists($this->oc_dir . "config.php"));
        $this->assertTrue(file_exists($this->oc_dir . "admin" . DIRECTORY_SEPARATOR . "config.php"));

        $this->installer->removeConfigFiles($options);
        $this->assertFalse(file_exists($this->oc_dir . "config.php"));
        $this->assertFalse(file_exists($this->oc_dir . "admin" . DIRECTORY_SEPARATOR . "config.php"));

        $this->installer->removeDatabase($options);
    }
}
