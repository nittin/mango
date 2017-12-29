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

    protected function pushNotification($friends, $method, $template, $channel, $mention, $meaning)
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
                User::whereIn('id', $friend_array)->get()->notifications()->create([
                    'template' => $template,
                    'channel' => $channel,
                    'mention' => is_array($mention) ? join(',', $mention) : $mention,
                    'meaning' => is_array($meaning) ? join(',', $meaning) : $meaning,
                    'status' => 0,
                ]);
                foreach ($friend_array as $f) {
                    $message = ['command' => CMD_PULL_NOW, 'date' => $now];
                    $pusher->trigger($f, $channel, $message);
                }
                break;

            case NOTIFY_INSTANT:

                foreach ($friend_array as $f) {
                    $message = [
                        'command' => CMD_SHOW_NOW,
                        'template' => $template,
                        'mention' => is_array($mention) ? join(',', $mention) : $mention,
                        'meaning' => is_array($meaning) ? join(',', $meaning) : $meaning,
                        'date' => $now
                    ];
                    $pusher->trigger($f, $channel, $message);
                }
                break;
        }
    }
}
