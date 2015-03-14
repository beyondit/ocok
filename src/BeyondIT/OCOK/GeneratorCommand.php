<?php

namespace BeyondIT\OCOK;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Question\ChoiceQuestion;

class GeneratorCommand extends OCOKCommand {
    public function getTemplatePath() {
        $paths = get_included_files();
        return dirname($paths[0]) . DIRECTORY_SEPARATOR . "templates" . DIRECTORY_SEPARATOR;
    }

    public function getCamelCaseName($name) {
        $output = '';

        $parts = explode("_",$name);
        foreach($parts as $part) {
            $output .= ucfirst(strtolower($part));
        }

        return $output;
    }

    public function getConfigVars($path) {
        $names = explode("/",$path);

        if (count($names) != 2) {
            throw new \Exception("The given path doesn't comply");
        }

        return array(
            'model' => 'Model' . $this->getCamelCaseName($names[0]) . $this->getCamelCaseName($names[1]) ,
            'controller' => 'Controller' . $this->getCamelCaseName($names[0]) . $this->getCamelCaseName($names[1]) ,
            'name'  => $this->getCamelCaseName($names[1]) ,
            'identifier' => $names[1] ,
            'full_identifier' => $names[0] . '_' . $names[1] ,
            'css_identifier' => str_replace('_','-',$names[1]),
            'default_sort' => $names[1] . '_id' ,
            'base_name' => $names[0] ,
            'path' => $path ,
            'title_uc' => ucwords(str_replace('_',' ',$names[1])) ,
            'title_lc' => str_replace('_',' ',$names[1])
        );
    }

    public function supportedVersions() {
        return array('1','2');
    }
    
    protected function configure() {
        $this->setName('generate')
             ->setDescription('Generate Opencart Admin CRUD')
             ->addArgument("path",InputArgument::REQUIRED,"Specify the Path to generate. e.g. catalog/special_product");
    }

    protected function executeSql($sql) {
        $config = $this->loadDatabaseConfig($this->getOCDirectory());
        $pdo = new \PDO('mysql:host='.$config['db_hostname'].';dbname='.$config['db_database'], $config['db_username'], $config['db_password']);
        $pdo->exec($sql);
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $loader = new \Twig_Loader_Filesystem($this->getTemplatePath());
        $twig   = new \Twig_Environment($loader,array());

        $path = strtolower($input->getArgument("path"));
        $data = $this->getConfigVars($path);

        $helper = $this->getHelper('question');

        while (true) {
            $min_size = $max_size = -1;
            $ask_required = true;

            $question1  = new Question('Add the name of the model attribute (end with empty answer): ','');
            $attribute_name = strtolower($helper->ask($input, $output, $question1));
            if (empty($attribute_name)) {break;}
            $attribute_title = ucwords(str_replace('_',' ',$attribute_name));

            $question2 = new ChoiceQuestion(
                'Set the type of the model attribute: ',
                array('integer', 'varchar', 'text', 'htmltext', 'boolean', 'date', 'decimal'),
                0);
            $attribute_type = $helper->ask($input, $output, $question2);

            if($attribute_type === 'boolean') {
                $ask_required = false;
                $attribute_required = false;
            }

            if ($attribute_type === 'varchar') {
                $q_min = new Question('Set min size of varchar input (0-255): ',0);
                $min_size = $helper->ask($input, $output, $q_min);
                if ($min_size < 0 || $min_size > 255) {$min_size = 0;}

                $q_max = new Question('Set max size of varchar input (0-255): ',255);
                $max_size = $helper->ask($input, $output, $q_max);
                if ($max_size < 0 || $max_size > 255) {$max_size = 255;}

                if ($min_size > 0) {
                    $ask_required = false;
                    $attribute_required = true;
                }
            }

            $selection = array("yes","no");
            $question3  = new Question('Should the attribute be displayed in the List (yes/no):','no');
            $question3->setAutocompleterValues($selection);
            $attribute_list = $helper->ask($input, $output, $question3) === 'yes' ? true : false;

            if ($attribute_list) {
                $question4  = new Question('Should the attribute be the default sorting for the List (yes/no): ','no');
                $question4->setAutocompleterValues($selection);
                if ($helper->ask($input, $output, $question4) === 'yes') {
                    $data['default_sort'] = $attribute_name;
                }
            }

            if ($ask_required) {
                $question5 = new Question('Should the attribute be required (yes/no):', 'no');
                $question5->setAutocompleterValues($selection);
                $attribute_required = $helper->ask($input, $output, $question5) === 'yes' ? true : false;
            }

            $attribute_definition = array(
                'attribute_name'  => $attribute_name ,
                'attribute_css_id' => 'input-' . str_replace("_","-",$attribute_name) ,
                'attribute_type'  => $attribute_type ,
                'attribute_list'  => $attribute_list ,
                'attribute_title' => $attribute_title,
                'attribute_required' => isset($attribute_required) ? $attribute_required : false
            );

            if ($min_size >= 0 && $max_size > 0) {
                $attribute_definition['attribute_min_size'] = $min_size;
                $attribute_definition['attribute_max_size'] = $max_size;
            }

            $data['attributes'][] = $attribute_definition;
        }

        $base_dir = $this->getOCDirectory() . DIRECTORY_SEPARATOR . "admin" . DIRECTORY_SEPARATOR;
        $controller_dir = $base_dir . "controller" . DIRECTORY_SEPARATOR . $data['base_name'];
        $model_dir      = $base_dir . "model" . DIRECTORY_SEPARATOR . $data['base_name'];
        $language_dir   = $base_dir . "language" . DIRECTORY_SEPARATOR . "english" . DIRECTORY_SEPARATOR . $data['base_name'];
        $template_dir   = $base_dir . "view" . DIRECTORY_SEPARATOR . "template" . DIRECTORY_SEPARATOR . $data['base_name'];

        //create all the dirs, if they don't exists yet
        @mkdir($controller_dir,0755,true);
        @mkdir($model_dir,0755,true);
        @mkdir($language_dir,0755,true);
        @mkdir($template_dir,0755,true);

        file_put_contents($base_dir . "controller" . DIRECTORY_SEPARATOR . $path . ".php", $twig->render('admin/controller.twig',$data));
        file_put_contents($base_dir . "model" . DIRECTORY_SEPARATOR . $path . ".php", $twig->render('admin/model.twig',$data));
        file_put_contents($base_dir . "view" . DIRECTORY_SEPARATOR . "template" . DIRECTORY_SEPARATOR . $path . "_list.tpl", $twig->render('admin/list_template.twig',$data));
        file_put_contents($base_dir . "view" . DIRECTORY_SEPARATOR . "template" . DIRECTORY_SEPARATOR . $path . "_form.tpl", $twig->render('admin/form_template.twig',$data));
        file_put_contents($base_dir . "language" . DIRECTORY_SEPARATOR . "english" . DIRECTORY_SEPARATOR . $path . ".php", $twig->render('admin/language.twig',$data));

        $this->executeSql($twig->render('admin/base_table.twig',array_merge($data,$this->loadDatabaseConfig($this->getOCDirectory()))));
    }
}
