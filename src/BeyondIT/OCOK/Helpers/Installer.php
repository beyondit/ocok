<?php

namespace BeyondIT\OCOK\Helpers;


class Installer {

    protected $registry;
    protected $loader;

    protected $dir_opencart;

    function __construct($dir_opencart) {
        $this->dir_opencart = $dir_opencart;
    }

    /**
     * @params options array options array for cli installer
     * @return array output of cli exec
     *
     */
    public function install($options) {
        chdir($this->dir_opencart . DIRECTORY_SEPARATOR . "install");

        $this->removeDatabase($options);
        $this->createDatabase($options);

        $exec =  "php cli_install.php install";
        $exec .= " --http_server " . $options['http_server'];
        $exec .= " --db_hostname " . $options['db_hostname'];
        $exec .= " --db_username " . $options['db_username'];
        $exec .= " --db_password " . $options['db_password'];
        $exec .= " --db_database " . $options['db_database'];
        $exec .= " --db_prefix "   . $options['db_prefix'];
        $exec .= " --db_driver "   . $options['db_driver'];
        $exec .= " --email "       . $options['email'];
        $exec .= " --username "    . $options['username'];
        $exec .= " --password "    . $options['password'];

        exec($exec,$output);
        return $output;
    }

    public function removeInstallationFiles() {
        chdir($this->dir_opencart);

        if (is_file($this->dir_opencart . DIRECTORY_SEPARATOR . "config-dist.php")) {
            unlink($this->dir_opencart . DIRECTORY_SEPARATOR . "config-dist.php");
        }

        if (is_file($this->dir_opencart . DIRECTORY_SEPARATOR . "admin" . DIRECTORY_SEPARATOR . "config-dist.php")) {
            unlink($this->dir_opencart . DIRECTORY_SEPARATOR . "admin" . DIRECTORY_SEPARATOR . "config-dist.php");
        }

        // remove install folder
        if (is_dir($this->dir_opencart . DIRECTORY_SEPARATOR . "install")) {
            $fsh = new FileSystem();
            $fsh->rmdir($this->dir_opencart . DIRECTORY_SEPARATOR . "install");
        }
    }

    public function createDatabase($options) {
        $mysqli = new \mysqli($options['db_hostname'],$options['db_username'],$options['db_password']);
        $mysqli->query("create database if not exists " . $options['db_database']);
        $mysqli->close();
    }

    public function removeDatabase($options) {
        $mysqli = new \mysqli($options['db_hostname'],$options['db_username'],$options['db_password']);
        if ($mysqli->select_db($options['db_database'])) {
            $mysqli->query("drop database " . $options['db_database']);
        }
        $mysqli->close();
    }

    public function removeConfigFiles() {
        if ($this->dir_opencart) {
            $directory = $this->dir_opencart;
        } else {
            $directory = getcwd();
        }

        $catalog_config = $directory . DIRECTORY_SEPARATOR . "config.php";
        $admin_config   = $directory . DIRECTORY_SEPARATOR . "admin" . DIRECTORY_SEPARATOR . "config.php";

        if (file_exists($catalog_config) && is_file($catalog_config)) {
            @unlink($catalog_config);
        }
        if (file_exists($admin_config) && is_file($admin_config)) {
            @unlink($admin_config);
        }
    }
}