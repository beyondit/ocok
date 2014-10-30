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
    
    protected function configure()
    {
        $this->setName('info')
             ->setDescription('OpenCart Commandline Utilities');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        if (parent::execute($input, $output)) {
            
            $version = $this->getVersion();
            
            $output->writeln("<info>    ___       ___       ___       ___    </info>");     
            $output->writeln("<info>   /\  \     /\  \     /\  \     /\__\   </info>");  
            $output->writeln("<info>  /::\  \   /::\  \   /::\  \   /:/ _/_  </info>");  
            $output->writeln('<info> /:/\:\__\ /:/\:\__\ /:/\:\__\ /::-"\__\ </info>');  
            $output->writeln('<info> \:\/:/  / \:\ \/__/ \:\/:/  / \;:;-",-" </info>');  
            $output->writeln("<info>  \::/  /   \:\__\    \::/  /   |:|  |   </info>");  
            $output->writeln("<info>   \/__/     \/__/     \/__/     \|__|   </info>");  
                 
            $output->writeln("_______________________________________");
            $output->writeln("<info>OCOK - OpenCart Commandline Utilities</info>");
            $output->writeln("OpenCart Version <comment>$version</comment> found");
            $output->writeln("");
            
            $application = $this->getApplication();
            $commands = $application->all();
            
            $output->writeln("<comment>Available commands:</comment>");
            $spaces = "          "; 
            foreach($commands as $command) {
                $name = $command->getName();
                $desc = $command->getDescription();
                
                $output->writeln("  <info>$name</info>" . substr($spaces, strlen($name)) . $desc);
            }
            $output->writeln("");
        }
    }
}
