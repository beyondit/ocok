<?php

namespace BeyondIT\OCOK\Helpers;


class Installer {

    protected $registry;
    protected $loader;
    protected $oc_dir;

    public function init($oc_dir) {
        $this->oc_dir = $oc_dir;
        $cli_file = $oc_dir . "install" . DIRECTORY_SEPARATOR . "cli_install.php";

        if (file($cli_file)) {
            try {
                ob_start();
                require_once($cli_file);
                ob_end_clean();
            } catch (\Exception $error) {
                // Silence all Exceptions from cli_install.php
            }

            $this->registry = new \Registry();
            $this->loader = new \Loader($this->registry);
            $this->registry->set('load', $this->loader);
        }
    }

    public function checkRequirements() {
        $check = \check_requirements();

        return array(
            'check'   => $check[0] ,
            'message' => $check[0] ? "Requirements passed" : ("Check failed: " . $check[1])
        );
    }

    public function setDirectoryPermissions() {
        $check = true;
        $message = "Successfully set directory permissions";

        try {
            \dir_permissions();
        } catch(\Exception $error) {
            $check = false;
            $message = $error->getMessage();
        }

        return array(
            'check'   => $check ,
            'message' => $message
        );
    }

    public function removeDatabase($options) {
        $mysqli = new \mysqli($options['db_hostname'],$options['db_username'],$options['db_password']);
        if ($mysqli->select_db($options['db_database'])) {
            $mysqli->query("drop database " . $options['db_database']);
        }
        $mysqli->close();
    }

    public function setupDatabase($options) {
        $check = true;
        $message = "Successfully setup database";

        if (!defined("HTTP_OPENCART")) {
            define('HTTP_OPENCART', $options['http_server']);
        }

        try {
            $this->removeDatabase($options);

            $mysqli = new \mysqli($options['db_hostname'],$options['db_username'],$options['db_password']);
            $mysqli->query("create database if not exists " . $options['db_database']);
            $mysqli->close();

            $this->loader->model('install');
            $model = $this->registry->get('model_install');
            $model->database($options);
        } catch(\Exception $error) {
            $check = false;
            $message = $error->getMessage();
        }

        return array(
            'check'   => $check ,
            'message' => $message
        );
    }

    public function removeConfigFiles() {
        if ($this->oc_dir) {
            $directory = $this->oc_dir;
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

    public function writeConfigFiles($options) {
        $check = true;
        $message = "Successfully wrote config files";

        try {
            \write_config_files($options);
        } catch(\Exception $error) {
            $check = false;
            $message = $error->getMessage();
        }

        return array(
            'check'   => $check ,
            'message' => $message
        );
    }

}