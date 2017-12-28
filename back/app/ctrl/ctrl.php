<?php

namespace App\Controllers;
use App\Models\User;
use DateTime;

class Controller
{
    protected $message;
    protected $container;

    // constructor receives container instance
    public function __construct($container)
    {
        $this->container = $container;
        $this->message = [
            '200' => ['success' => true],
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

    protected function pushNotification($friends, $method, $type, $channel, $content, $from)
    {
        if (!$friends) {
            return;
        }
        $now = (new DateTime())->getTimestamp() * 1000;
        /* push notification to all target user*/
        $pusher = $this->container->pusher;
        $friend_array = is_array($friends) ? $friends : explode(',', (string)$friends);

        switch ($method) {
            case NOTIFY_WITH_PULL_REQUEST:
            case NOTIFY_WITHOUT_PULL_REQUEST:
                User::whereIn('id', $friend_array)->notifications()->create([
                    'type' => $type,
                    'content' => is_array($content) ? join(',', $content) : $content,
                    'from' => is_array($from) ? join(',', $from) : $from,
                    'status' => 0,
                ]);
                break;
        }

        switch ($method) {
            case NOTIFY_ONLY_MESSAGE:
            case NOTIFY_WITH_PULL_REQUEST:
                foreach ($friend_array as $f) {
                    $message = $this->readNotification($type, $content, $from, $now);
                    $pusher->trigger($f, $channel, $message);
                }
                break;
            default:
                break;
        }

    }

    protected function readNotification($type, $content, $from, $date)
    {
        return  [
            'type' => $type,
            'content' => is_array($content) ? join(',', $content) : $content,
            'from' => is_array($from) ? join(',', $from) : $from,
            'date' => $date
        ];
    }
}
