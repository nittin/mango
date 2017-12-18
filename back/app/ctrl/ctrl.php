<?php

namespace App\Controllers;

class Controller
{
    protected $container;

    // constructor receives container instance
    public function __construct($container)
    {
        $this->container = $container;
    }

    //replace all property of the 'container' to 'this'
    public function __get($property)
    {
        if ($this->container->{$property}) {
            return $this->container->{$property};
        }
    }
}
