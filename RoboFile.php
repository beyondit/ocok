<?php

use Symfony\Component\Finder\Finder;

class RoboFile extends \Robo\Tasks
{
    
    function package() {        
        $packer = $this->taskPackPhar('ocok.phar');
        $files = Finder::create()->ignoreVCS(true)
            ->files()
            ->name('*.php')
            ->path('src')
            ->path('vendor')
            ->in(__DIR__);
        
        foreach ($files as $file) {
            $packer->addFile($file->getRelativePathname(), $file->getRealPath());
        }
        
        $packer->addFile('ocok','ocok')
            ->executable('ocok')
            ->run();      
    }
    
    function release($version = '') {
        $composer = json_decode(file_get_contents(__DIR__ . "/composer.json"),true);
        
        if (empty($version)) { // bump version patch x.y.+1
            $versionParts = explode('.',$composer['version']);
            $versionParts[count($versionParts)-1]++;
            $version = implode('.', $versionParts);            
        }
        
        $this->taskReplaceInFile(__DIR__.'/composer.json')
            ->from('"version": "'.$composer['version'].'"')
            ->to('"version": "'.$version.'"')
            ->run();
        
        $this->say($version);
    }
    
    public function publish() {
        $this->package();
        rename('ocok.phar','ocok-release.phar');
        $this->taskGitStack()->checkout('gh-pages')->run();
        rename('ocok-release.phar', 'ocok.phar');
        $this->taskGitStack()
        ->add('ocok.phar')
        ->commit('ocok.phar published')
        ->push('origin','gh-pages')
        ->checkout('master')
        ->run();
    }
    
}