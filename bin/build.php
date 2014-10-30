<?php

$pharFile = "ocok.phar";
$baseDir  = dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR;

if (file_exists($pharFile)) {
    unlink($pharFile);
}

$phar = new Phar($pharFile,0,$pharFile);
$phar->setSignatureAlgorithm(Phar::SHA1);

$phar->startBuffering();

$addRecursive = function ($folder) use ($baseDir,$phar) {
    $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($baseDir.$folder), RecursiveIteratorIterator::SELF_FIRST);

    foreach($files as $name => $file){
        $filename = str_replace($baseDir,"",$name);    

        if (("." != substr($name,-1) && is_dir($name)) || (substr($name,-3) == "php")) {
            $phar->addFile($name,$filename);
        }
    }
};

$addRecursive("src");
$addRecursive("vendor");

$phar->addFile($baseDir."index.php", "index.php");
$phar->setStub("#!/usr/bin/env php\n".$phar->createDefaultStub('index.php'));

$phar->stopBuffering();
