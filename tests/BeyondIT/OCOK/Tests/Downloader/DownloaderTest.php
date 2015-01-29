<?php

namespace BeyondIT\OCOK\Tests\Downloader;


use BeyondIT\OCOK\Helpers\Downloader;
use BeyondIT\OCOK\Helpers\FileSystem;

class DownloaderTest extends \PHPUnit_Framework_TestCase {

    /**
     * @var \BeyondIT\OCOK\Helpers\Downloader
     */
    protected $downloader;

    protected $dir;

    public function setUp() {
        $this->dir = $dir = getcwd() . DIRECTORY_SEPARATOR . "oc_download";
        $this->downloader = new Downloader($dir);
    }

    public function tearDown() {
        $fsh = new FileSystem();
        $fsh->rmdir($this->dir);
    }

    public function testDownloadingAndUnzipingOpencart() {
        $this->downloader->process("2.0.1.1");

        $this->assertFileExists($this->dir . DIRECTORY_SEPARATOR . "index.php");
        $this->assertFileExists($this->dir . DIRECTORY_SEPARATOR . "admin" . DIRECTORY_SEPARATOR . "index.php");
        $this->assertFileNotExists(getcwd() . DIRECTORY_SEPARATOR . "oc.zip");
    }

}
