<?php

namespace controller;

class Dashboard extends ApplicationController {
    public function index() {
        return array('contact_email' => 'test@test.com');
    }   
}

?>
