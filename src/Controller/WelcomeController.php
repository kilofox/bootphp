<?php

namespace App\Controller;

use Bootphp\Controller\Controller;

class WelcomeController extends Controller
{
    public function indexAction()
    {
        $this->response->body('hello, world!');
    }

}
