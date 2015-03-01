<?php
/**
 * This is project's console commands configuration for Robo task runner.
 *
 * @see http://robo.li/
 */
use Symfony\Component\Finder\Finder;

class RoboFile extends \Robo\Tasks {

    function pharPackage() {
        $packer = $this->taskPackPhar('ocok.phar');
        $files = Finder::create()->ignoreVCS(true)
            ->files()
            ->name('*.php')
            ->path('vendor/autoload.php')
            ->path('src')
            ->path('vendor/ifsnop')
            ->path('vendor/symfony')
            ->path('vendor/composer')
            ->in(__DIR__);

        foreach ($files as $file) {
            $packer->addFile($file->getRelativePathname(), $file->getRealPath());
        }

        $packer
            ->addFile('ocok','ocok')
            ->executable('ocok')
            ->run();
    }

    function pharPublish() {
        $this->pharPackage();

        rename('ocok.phar', 'ocok-release.phar');
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