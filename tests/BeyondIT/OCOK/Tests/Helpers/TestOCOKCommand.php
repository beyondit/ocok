<?php

namespace BeyondIT\OCOK\Tests\Helpers;

use BeyondIT\OCOK\OCOKCommand;

class TestOCOKCommand extends OCOKCommand {

    public $supported_versions = array();

    public function supportedVersions() {
        return $this->supported_versions;
    }
}