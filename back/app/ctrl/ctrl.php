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

    protected function pushNotification($friends, $method, $type, $channel, $content, $relate)
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
                    'content' => $content,
                    'status' => 0,
                    'from' => 'center',
                ]);
                break;
            case NOTIFY_ONLY_PULL_REQUEST:
            case NOTIFY_ONLY_MESSAGE:
            default:
                break;
        }

        switch ($method) {
            case NOTIFY_ONLY_MESSAGE:
            case NOTIFY_WITH_PULL_REQUEST:
                foreach ($friend_array as $f) {
                    $pusher->trigger($f, $channel, [
                        'type' => $type,
                        'content' => $content,
                        'relate' => $relate,
                        'date' => $now
                    ]);
                }
                break;
            case NOTIFY_WITHOUT_PULL_REQUEST:
                break;
            case NOTIFY_ONLY_PULL_REQUEST:
                return;
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
