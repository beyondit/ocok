<?php

namespace BeyondIT\OCOK;

use BeyondIT\OCOK\Helpers\Downloader;
use BeyondIT\OCOK\Helpers\FileSystem;
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
            ->addOption("version","V",InputOption::VALUE_OPTIONAL,"Download OpenCart Version (e.g.: 2.0.1.1)","2.0.1.1")
            ->addOption("db_driver","d",InputOption::VALUE_OPTIONAL,"Database Drive, defaults to mysqli","mysqli")
            ->addOption("db_hostname","o",InputOption::VALUE_OPTIONAL,"Database Hostname, defaults to localhost","localhost")
            ->addOption("db_username","u",InputOption::VALUE_REQUIRED,"Database Username")
            ->addOption("db_password","p",InputOption::VALUE_REQUIRED,"Database Password")
            ->addOption("db_database","b",InputOption::VALUE_OPTIONAL,"Database Name, defaults to opencart","opencart")
            ->addOption("db_prefix","x",InputOption::VALUE_OPTIONAL,"Database Prefix, oc_ as default","oc_")
            ->addOption("username","U",InputOption::VALUE_OPTIONAL,"OpenCart Admin Username","admin")
            ->addOption("password","P",InputOption::VALUE_REQUIRED,"OpenCart Admin Password")
            ->addOption("email","e",InputOption::VALUE_REQUIRED,"OpenCart Admin Email")
            ->addOption("http_server","s",InputOption::VALUE_OPTIONAL,"HTTP Server, defaults to http://localhost/opencart/","http://localhost/opencart/");
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $fsh = new FileSystem();

        if ($input->hasArgument("directory") && $input->getArgument("directory")) {
            chdir($input->getArgument("directory"));
        }

        $installer = new Installer(getcwd() . DIRECTORY_SEPARATOR);

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
        if ($fsh->isEmptyDirectory($dir)) {
            $version = "2.0.1.1";
            if ($input->getOption("version")) {
                $version = $input->getOption("version");
            }

            $downloader = new Downloader($dir);
            $downloader->process($version);
            $output->writeln("<info>OpenCart Version $version downloaded.</info>");
        } else if ($this->checkUninstalledOC()) { // OC present
            $version = $this->getVersion();
            $output->writeln("<info>OpenCart Version $version found.</info>");
        } else {
            $output->writeln("<error>No Empty and no OC Directory found here</error>");
            return;
        }

        try {
            $installer->install($options);
        } catch(\Exception $e) {
            $output->writeln("<error>Error: ".$e->getMessage()."</error>");
            return;
        }

        $output->writeln("<info>OpenCart was successfully installed!</info>");
    }

}