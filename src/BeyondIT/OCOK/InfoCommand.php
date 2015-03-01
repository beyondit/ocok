<?php

namespace BeyondIT\OCOK;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class InfoCommand extends OCOKCommand {
    
    public function supportedVersions() {
        return array('1','2');
    }
    
    protected function configure() {
        $this->setName('info')
             ->setDescription('OpenCart Commandline Utilities');
    }

    public function printOCOKInfo(OutputInterface $output,$commands,$version = null) {

        $output->writeln("<info>    ___       ___       ___       ___    </info>");
        $output->writeln("<info>   /\  \     /\  \     /\  \     /\__\   </info>");
        $output->writeln("<info>  /::\  \   /::\  \   /::\  \   /:/ _/_  </info>");
        $output->writeln('<info> /:/\:\__\ /:/\:\__\ /:/\:\__\ /::-"\__\ </info>');
        $output->writeln('<info> \:\/:/  / \:\ \/__/ \:\/:/  / \;:;-",-" </info>');
        $output->writeln("<info>  \::/  /   \:\__\    \::/  /   |:|  |   </info>");
        $output->writeln("<info>   \/__/     \/__/     \/__/     \|__|   </info>");

        $output->writeln("_______________________________________");
        $output->writeln("<info>OCOK - OpenCart Commandline Utilities</info>");

        if ($version) {
            $output->writeln("OpenCart Version <comment>$version</comment> found");
        } else {
            $output->writeln("<info>No OpenCart installation found</info>");
        }
        $output->writeln("");

        $output->writeln("<comment>Available commands:</comment>");
        $spaces = "          ";
        foreach($commands as $command) {
            $output->writeln("  <info>".$command['name']."</info>" . substr($spaces, strlen($command['name'])) . $command['desc']);
        }
        $output->writeln("");
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $application = $this->getApplication();

        if (!$this->checkOC()) {
            $version = null;
            $cmds[] = $application->get("install");
            $cmds[] = $application->get("info");
            $cmds[] = $application->get("help");
        } else {
            $version = $this->getVersion();
            $cmds = $application->all();
        }

        $commands = array();
        foreach($cmds as $cmd) {
            $commands[] = array(
                'name' => $cmd->getName(),
                'desc' => $cmd->getDescription()
            );
        }

        $this->printOCOKInfo($output,$commands,$version);
    }
}
