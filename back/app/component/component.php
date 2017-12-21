<?php

namespace App\Components;

class Component
{
    protected $container;

    // constructor receives container instance
    public function __construct($container)
    {
        $this->container = $container;
    }
}
