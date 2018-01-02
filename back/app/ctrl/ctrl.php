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

    protected function cURL($ch_url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $ch_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
        $ch_send = curl_exec($ch);
        curl_close($ch);
        return $ch_send;
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
            case NOTIFY_PULL:
                foreach ($friend_array as $f) {
                    User::find($f)->notifications()->create([
                        'template' => $template,
                        'channel' => $channel,
                        'mention' => is_array($mention) ? join(',', $mention) : $mention,
                        'meaning' => is_array($meaning) ? join(',', $meaning) : $meaning,
                        'status' => 0,
                    ]);
                    $message = ['date' => $now];
                    $pusher->trigger($f, CMD_PULL_NOW, $message);
                }
                break;

            case NOTIFY_INSTANT:

                foreach ($friend_array as $f) {
                    $message = [
                            'template' => $template,
                            'date' => $now
                        ] + $this->readNotification($mention, $meaning);
                    $pusher->trigger($f, CMD_SHOW_NOW, $message);
                }
                break;
        }
    }
    protected function readNotification($mention, $meaning)
    {
        $mention_arr =is_array($mention) ? $mention : explode(',', (string)$mention);
        $meaning_arr = is_array($meaning) ? $meaning : explode(',', (string)$meaning);
        $result = (object)[];
        array_map(function ($a, $b, $i) use ($result){
            $result->{"$b$i"} = $a;
            return ['id' => $a, 'type' => $b];
        }, $mention_arr, $meaning_arr, array_keys($meaning_arr));
        return $result;
    }
}
