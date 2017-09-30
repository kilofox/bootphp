<?php

namespace App\Controller;

class WelcomeController extends \Bootphp\Controller
{
    public function indexAction()
    {
        $this->response->body('hello, world!');
    }

}
