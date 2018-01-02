<?php

namespace App\Components;

class Component
{
    protected $message = [
        '401' => ['success' => false, 'message' => '401 User does not authorize'],
        '404' => ['success' => false, 'message' => 'Not found']
    ];
    protected $container;

    // constructor receives container instance
    public function __construct($container)
    {
        $this->container = $container;
    }
}
