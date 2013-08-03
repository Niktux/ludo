<?php

namespace Ludo\Controllers\Home;

class Controller extends \Firenote\Controllers\AbstractController
{
    public function indexAction()
    {
        return $this->page
            ->setTitle('Home page')
            ->render('home.twig');
    }
}