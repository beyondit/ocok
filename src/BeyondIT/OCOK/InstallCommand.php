<?php

namespace BeyondIT\OCOK;

use BeyondIT\OCOK\Helpers\Installer;
use BeyondIT\OCOK\OCOKCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class InstallCommand extends OCOKCommand {

    public function supportedVersions() {
        return array("2");
    }

    protected function configure() {
        $this->setName("install")
            ->setDescription("Install OpenCart")
            ->addOption("db_driver","d",InputOption::VALUE_OPTIONAL,"Database Drive, defaults to mysqli","mysqli")
            ->addOption("db_hostname","o",InputOption::VALUE_OPTIONAL,"Database Hostname, defaults to localhost","localhost")
            ->addOption("db_username","u",InputOption::VALUE_REQUIRED,"Database Username")
            ->addOption("db_password","p",InputOption::VALUE_REQUIRED,"Database Password")
            ->addOption("db_database","b",InputOption::VALUE_OPTIONAL,"Database Name, defaults to opencart","opencart")
            ->addOption("db_prefix","x",InputOption::VALUE_OPTIONAL,"Database Prefix, empty as default","")
            ->addOption("username","U",InputOption::VALUE_OPTIONAL,"Username","admin")
            ->addOption("password","P",InputOption::VALUE_REQUIRED,"Password")
            ->addOption("email","e",InputOption::VALUE_REQUIRED,"Email")
            ->addOption("http_server","s",InputOption::VALUE_OPTIONAL,"HTTP Server, defaults to http://localhost/opencart/","http://localhost/opencart/")
            ->addArgument("directory", InputArgument::OPTIONAL, "Set the directory to install OpenCart","./");
    }

    /*
     * http://stackoverflow.com/questions/7497733/how-can-use-php-to-check-if-a-directory-is-empty
     */
    public function isEmptyDirectory($dir) {
        if (!is_readable($dir)) return null;
        $handle = opendir($dir);
        while (false !== ($entry = readdir($handle))) {
            if ($entry != "." && $entry != "..") {
                return false;
            }
        }
        return true;
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $installer = new Installer();

        if ($input->hasArgument("directory") && $input->getArgument("directory")) {
            chdir($input->getArgument("directory"));
        }

        $options = array();
        $options['db_driver']   = $input->getOption("db_driver");
        $options['db_hostname'] = $input->getOption("db_hostname");
        $options['db_username'] = $input->getOption("db_username");
        $options['db_password'] = $input->getOption("db_password");
        $options['db_database'] = $input->getOption("db_database");
        $options['db_prefix']   = $input->getOption("db_prefix");
        $options['username']    = $input->getOption("username");
        $options['password']    = $input->getOption("password");
        $options['email']       = $input->getOption("email");
        $options['http_server'] = $input->getOption("http_server");

        if (!$options['db_username']) {
            $output->writeln("<error>Database Username option is missing!</error>");
            return;
        }

        if (!$options['db_password']) {
            $output->writeln("<error>Database Password option is missing!</error>");
            return;
        }

        if (!$options['username']) {
            $output->writeln("<error>Username option is missing!</error>");
            return;
        }

        if (!$options['password']) {
            $output->writeln("<error>Password option is missing!</error>");
            return;
        }

        if (!$options['email']) {
            $output->writeln("<error>Email option is missing!</error>");
            return;
        }

        $dir = getcwd();
        if ($this->isEmptyDirectory($dir)) {
            // TODO: download first
            $output->writeln("<error>Downloading OpenCart not yet supported!</error>");
            return;
        } else if ($this->checkUninstalledOC()) { // OC present
            $installer->init(getcwd() . DIRECTORY_SEPARATOR);
        } else {
            $output->writeln("<error>No Empty and no OC Directory found</error>");
            return;
        }

        for ($i = 1 ; $i <= 4 ; $i++) {
            switch ($i) {
                case $i === 1:
                    $step = $installer->checkRequirements();
                    break;
                case $i === 2:
                    $step = $installer->setupDatabase($options);
                    break;
                case $i === 3:
                    $step = $installer->writeConfigFiles($options);
                    break;
                case $i === 4:
                    $step = $installer->setDirectoryPermissions();
                    break;
                default:
                    break;
            }

            if ($step['check']) {
                $output->writeln("<info>".$step['message']."</info>");
            } else {
                $output->writeln("<error>".$step['message']."</error>");
                return;
            }
        }

        $output->writeln("<info>OpenCart was successfully installed!</info>");
    }

}