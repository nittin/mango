<?php

namespace App\Controllers;

class Controller
{
    protected $message;
    protected $container;

    // constructor receives container instance
    public function __construct($container)
    {
        $this->container = $container;
        $this->message = [
            '401' => ['success' => false, 'message' => '401 User does not authorize'],
            '403' => ['success' => false, 'message' => '403 User does not access'],
            '404' => ['success' => false, 'message' => 'Not found']
        ];
    }

    //replace all property of the 'container' to 'this'
    public function __get($property)
    {
        if ($this->container->{$property}) {
            return $this->container->{$property};
        }
    }
}
