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
    protected function pushNotification($friends, $type, $message)
    {
        /* push notification to all target user*/
        $pusher = $this->container->pusher;
        $friend_array = explode(',', $friends);
        foreach ($friend_array as $f) {
            if ($f) {
                $pusher->trigger($f, 'user-online', $message);
            }
        }
        switch ($type) {
            case NOTIFY_ONLY_PULL_REQUEST:
                break;
            case NOTIFY_WITH_PULL_REQUEST:
                break;
            case NOTIFY_WITHOUT_PULL_REQUEST:
                break;
            default:
                break;
        }

    }
    protected function singleNotification($friend, $message)
    {
        $pusher = $this->container->pusher;
        if ($friend) {
            $pusher->trigger($friend, 'groups-work', $message);
        }
    }
}
