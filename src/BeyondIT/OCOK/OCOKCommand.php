<?php

namespace BeyondIT\OCOK;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

abstract class OCOKCommand extends Command {
    protected $version = '';
    /*
     * Method should return a String array of supported Version numbers
     * e.g. ['1.5','2'] 
     * 
     * return array
     */
    abstract public function supportedVersions();

    /**
     *  Installed OpenCart File Structure check
     */
    protected $files = array(
        'catalog/index' => 'index.php' ,
        'admin/index'   => 'admin/index.php',
        'catalog/config'=> 'config.php' ,
        'admin/config'  => 'admin/config.php'
    );

    /**
     *  Uninstalled OpenCart File Structure check
     */
    protected $uninstalled_files = array(
        'catalog/index' => 'index.php' ,
        'admin/index'   => 'admin/index.php',
        'install/cli'   => 'install/cli_install.php',
        'install/index' => 'install/index.php',
        'install/sql'   => 'install/opencart.sql'
    );
    
    // GETTERS
    
    public function getOCDirectory() {
        return getcwd();
    }
    
    public function getVersion() {
        return $this->version;
    }

    public function setVersion($version) {
        $this->version = $version;
    }
    
    // VERSION SECTION
    
    public function isVersion($version) {
        return strpos($this->getVersion(), $version) === 0 ? true : false;
    }
    
    public function isVersionSupported() {
        $supported = false;
        foreach ($this->supportedVersions() as $supportedVersion) {
            if ($this->isVersion($supportedVersion)) {
                $supported = true;
            }
        }
        return $supported;
    }
            
    public function loadOCConfig() {
        require_once 'config.php';
    }

    public function loadVersion($execDir) {
        $output = false;
        
        $handle = fopen($execDir ."/". $this->files['catalog/index'], "r");
        if ($handle) {
            while (($line = fgets($handle)) !== false) {
                $pos = strpos($line, "VERSION");
                if ($pos > 0) {
                    // eval(substr_replace($line,"_HACK",$pos+7,0));
                    preg_match("/[0-9]?\.[0-9]{1,2}\.[0-9]{1,2}\.\w{1,3}/i", $line,$match);
                    $output = $match[0];
                    break;
                }
            }
        }

        return $output;
    }

    public function checkUninstalledOC() {
        $execDir = $this->getOCDirectory();
        $output = true;

        // check if installed OC is present
        foreach ($this->uninstalled_files as $file) {
            if (!is_file($execDir ."/". $file)) {
                $output = false;
            }
        }

        // check the OC Version
        if ($output && $version = $this->loadVersion($execDir)) {
            $this->setVersion($version);
        }

        return $output;
    }

    public function checkOC() {
        $execDir = $this->getOCDirectory();
        $output = true;
                
        // check if installed OC is present
        foreach ($this->files as $file) {
            if (!is_file($execDir ."/". $file)) {
                $output = false;
            }
        }
                
        // check the OC Version
        if ($output && $version = $this->loadVersion($execDir)) {
            $this->setVersion($version);
        }
                
        return $output;
    }
    
    protected function execute(InputInterface $input, OutputInterface $output) {
        $result = true;
                
        if (!$this->checkOC()) {
            $result = false;
            $output->writeln("<error>ERROR: No Opencart installation found!</error>");
        } else if (!$this->isVersionSupported()) {
            $result = false;
            $output->writeln("<error>ERROR: OpenCart Version ".$this->getVersion()." is not supported!</error>");
        }
        
        return $result;
    }
    
}
