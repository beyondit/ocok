<?php

namespace BeyondIT\OCOK;

use BeyondIT\OCOK\Helpers\FileSystem;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Ifsnop\Mysqldump\Mysqldump;

class BackupCommand extends OCOKCommand {

    protected $backup_folder = '';
    protected $backup_db = 'db_backup.sql';

    public function supportedVersions() {
        return array('1.5', '2');
    }

    protected function configure() {
        $this->setName('backup')
                ->setDescription('Backup OpenCart Installation')
                ->addOption("images", "i", InputOption::VALUE_NONE, "Add images to backup")
                ->addOption("database", "d", InputOption::VALUE_NONE, "Add database to backup");
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        if (parent::execute($input, $output)) {
            $file_system_helper = new FileSystem();

            $this->loadOCConfig();
            $this->backup_folder = $this->getOCDirectory() . DIRECTORY_SEPARATOR . ".backup_tmp/";

            $za = new \ZipArchive();
            if ($za->open("ocok_backup_" . date("Y_m_d_H_i") . ".zip", \ZipArchive::OVERWRITE)) {

                if (is_dir($this->backup_folder)) {
                    $file_system_helper->rmdir($this->backup_folder);
                }

                mkdir($this->backup_folder);

                $image_path = DIR_IMAGE;
                if ($this->isVersion('2')) {
                    $image_path .= 'catalog/';
                } elseif ($this->isVersion('1')) {
                    $image_path .= 'data/';
                }

                if ($input->getOption("images")) {
                    $files = $file_system_helper->getFilesRecursively($image_path);
                    foreach ($files as $file) {
                        if ($file->isFile() && $file->isReadable() && $file_system_helper->isImage($file->getPathname())) {

                            // remove basefolder prefix from path
                            if (substr($file->getPathname(), 0, strlen($this->getOCDirectory())) == $this->getOCDirectory()) {
                                $path = substr($file->getPathname(), strlen($this->getOCDirectory()));
                            }

                            $za->addFile($file->getPathname(), substr($path, 1));
                        }
                    }
                }

                if ($input->getOption("database")) {
                    $dumper = new Mysqldump(
                            DB_DATABASE, DB_USERNAME, DB_PASSWORD, DB_HOSTNAME, 'mysql', array(
                        'add-drop-table' => true,
                        'add-drop-database' => true,
                        'databases' => true
                            )
                    );

                    $dumper->start($this->backup_folder . $this->backup_db);
                    $za->addFile($this->backup_folder . $this->backup_db, $this->backup_db);
                }
                
                $za->close();
                $file_system_helper->rmdir($this->backup_folder);
            }
        }
    }

}
