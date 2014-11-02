<?php

namespace BeyondIT\OCOK;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Monolog\Logger;

class CliTaskCommand extends OCOKCommand {
    
    public function supportedVersions() {
        return array('2','1.5');
    }
    
    protected function configure() {
        $this->setName("run")
             ->setDescription("Run OpenCart controllers as tasks from commandline")
             ->addOption("catalog","c", InputOption::VALUE_NONE	, "Set the catalog controllers as execution scope, default scope is admin")
             ->addOption("post","p", InputOption::VALUE_NONE	, "Changes the custom arguments as POST parameters, default is GET")
             ->addArgument("route", InputArgument::REQUIRED, "Set the route for the Task Controller")
             ->addArgument("args", InputArgument::IS_ARRAY, "Custom arguments, which are set as GET or POST (-p option) parameters for the controller script. Added as key/value pairs (e.g. key=value)");
    }
    
    protected function execute(InputInterface $input, OutputInterface $output) {
        if (parent::execute($input, $output)) {
                            
            if (!$input->getOption("catalog")) {
                chdir('admin');  
            }
                                    
            foreach ($input->getArgument("args") as $arg) {
                $pair = explode("=", $arg);
                if (count($pair) === 2) {
                    if ($input->getOption("post")) {
                        $_POST[$pair[0]] = $pair[1];
                    } else {
                        $_GET[$pair[0]] = $pair[1];
                    }                    
                }
            }
            
            ob_start();
            require_once $this->getOCDirectory() . DIRECTORY_SEPARATOR . "index.php";      
            ob_end_clean();
                    
            
            $logger = new Logger("ocok");
            
            $registry->set('cli',array(
                'logger' => $logger)
            );
            
            $controller = new \Front($registry);
            $controller->dispatch(new \Action($input->getArgument("route")), new \Action('error/not_found'));
            
        }
    }    
}
