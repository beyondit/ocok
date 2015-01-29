<?php

namespace BeyondIT\OCOK\Helpers;

use BeyondIT\OCOK\Helpers\FileSystem;

class Downloader {

    protected $dir;
    protected $tmp;

    protected $file_system_helper;

    protected $version_mapping = array(
        '2.0.1.1' => 'https://github.com/opencart/opencart/archive/2.0.1.1.zip'
    );

    function __construct($dir) {
        $this->file_system_helper = new FileSystem();
        $this->dir = $dir;
        $this->tmp = getcwd() . DIRECTORY_SEPARATOR . ".tmp";
        $this->oc_zip = getcwd() .DIRECTORY_SEPARATOR . "oc.zip";

        if (!is_dir($dir)) {
            mkdir($dir);
        }
        if (is_dir($this->tmp)) {
            $this->file_system_helper->rmdir($this->tmp);
        }
        mkdir($this->tmp);
    }

    public function process($version) {
        $check = $this->download($version);

        if ($check) {
            $check = $this->unzip();
        }

        if ($check) {
            if (is_dir($this->tmp)) {
                $this->file_system_helper->rmdir($this->tmp);
            }
            if (is_file($this->oc_zip)) {
                unlink($this->oc_zip);
            }
        }
    }

    public function download($version) {
        if (!$this->version_mapping[$version]) {
            return false;
        }

        return file_put_contents($this->oc_zip, fopen($this->version_mapping[$version], 'r'));
    }

    public function unzip() {
        if (is_file($this->oc_zip)) {
            $za = new \ZipArchive();
            $za->open($this->oc_zip);

            $base = "";
            for ($i = 0; $i < $za->numFiles; $i++) {
                $stat = $za->statIndex($i);
                if (preg_match("/.*\/upload\//i", $stat['name'])) {
                    $base = $stat["name"];
                    break;
                }
            }

            $za->extractTo($this->tmp);
            return rename($this->tmp."/opencart-2.0.1.1/upload",$this->dir);
        }

        return false;
    }

}