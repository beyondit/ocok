<?php

namespace BeyondIT\OCOK\Helpers;


class FileSystem {

    public function rmdir($dir) {
        $files = $this->getFilesRecursively($dir);

        foreach ($files as $fileinfo) {
            $todo = ($fileinfo->isDir() ? 'rmdir' : 'unlink');
            $todo($fileinfo->getRealPath());
        }

        rmdir($dir);
    }

    public function getFilesRecursively($dir) {
        return new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS), \RecursiveIteratorIterator::CHILD_FIRST
        );
    }

    public function isImage($file) {
        $allowedTypes = array(IMAGETYPE_PNG, IMAGETYPE_JPEG, IMAGETYPE_GIF);
        $detectedType = @exif_imagetype($file);
        return in_array($detectedType, $allowedTypes);
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

}