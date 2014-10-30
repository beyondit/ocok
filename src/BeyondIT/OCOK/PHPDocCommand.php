<?php

namespace BeyondIT\OCOK;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class PHPDocCommand extends OCOKCommand {
    
    protected function configure() {
        $this->setName("phpdoc")
             ->setDescription("Add PhpDoc for better supporting autocomplete features in IDEs");
    }
        
    public function getModels( $basedir ) {
        $permission = array();
        $files = glob( $basedir . 'model/*/*.php' );
        foreach ( $files as $file ) {
            $data = explode( '/', dirname( $file ) );
            $names = explode( '_', basename( $file, '.php' ) );
            
            if ( !$names ) {
                $names = array( basename( $file, '.php' ) );
            }
            
            $permission[] = 'Model' . ucfirst( end( $data ) ) . implode( '', array_map( function ( $x ) {
                return ucfirst( $x );
            }, $names ) ) . ' $model_' . end( $data ) . '_' . basename( $file, '.php' );
        }
        return $permission;
    }
    
    public function getLineOfFile( $fp, $needle ) {
        rewind( $fp );
        $lineNumber = 0;
        
        while ( !feof( $fp ) ) {
            $line = fgets( $fp );
            if ( !( strpos( $line, $needle ) === false ) ) {
                break;
            }
            $lineNumber++;
        }
        
        return feof( $fp ) ? null : $lineNumber;
    }
    
    public function exec_v15(InputInterface $input, OutputInterface $output) {
        
    }
    
    public function exec_v2(InputInterface $input, OutputInterface $output) {
        
    }
    
    protected function execute(InputInterface $input, OutputInterface $output) {
        if (parent::execute($input, $output)) {          
            
            $this->loadOCConfig();
            
            $properties = array(
                'string $id',
                'string $template',
                'array $children',
                'array $data',
                'string $output'                
            );
            
            // TODO: get all library classes as well, maybe extract from registry somehow...
            
            $searchLine = "abstract class Controller {";
            $pathToController = "engine/controller.php";
            $catalogModels = $this->getModels(\DIR_APPLICATION);
            $adminModels = $this->getModels( str_ireplace( "catalog/", "admin/", \DIR_APPLICATION ) );
            $textToInsert = array_unique( array_merge( $properties, $catalogModels, $adminModels ) );
            
            //get line number where start Controller description
            $fp = fopen( \DIR_SYSTEM . $pathToController, 'r' );
            $lineNumber = $this->getLineOfFile( $fp, $searchLine );
            fclose( $fp );
            
            //regenerate Controller text with properties
            $file = new \SplFileObject( \DIR_SYSTEM . $pathToController );
            $file->seek( $lineNumber );
            $tempFile = sprintf( "<?php %s \t/**%s", PHP_EOL, PHP_EOL );
            foreach ( $textToInsert as $val ) {
                $tempFile .= sprintf( "\t* @property %s%s", $val, PHP_EOL );
            }
            $tempFile .= sprintf( "\t**/%s%s%s", PHP_EOL, $searchLine, PHP_EOL );
            while ( !$file->eof() ) {
                $tempFile .= $file->fgets();
            }
            
            //write Controller
            $fp = fopen( \DIR_SYSTEM . $pathToController, 'w' );
            fwrite( $fp, $tempFile );
            fclose( $fp );
            
        }
    }

    public function supportedVersions() {
        return array('1.5');
    }

}
