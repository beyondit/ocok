<?php


class ControllerTestTest extends \Controller {

    public function index() {

        if ($this->is_cli === true) {
            $this->registry->set("test_output","Called by CLI");
        } else {
            $this->registry->set("test_output","Cli Variable not available");
        }

    }

}