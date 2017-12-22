<?php

namespace App\Controllers;

use App\Models\User;
use DateTime;

class UserController extends Controller
{
    private function pushNotification($friendsStr, $message)
    {
        /* push notification to all friends*/
        $pusher = $this->container->get('pusher');
        $friend_array = explode(',', $friendsStr);
        foreach ($friend_array as $f) {
            if($f) {
                $pusher->trigger($f, 'user-online', $message);
            }
        }
    }

    public function listed($request, $response)
    {
        $users = User::all();
        $response->write(json_encode($users));
        return $response;
    }

    public function contact($request, $response)
    {
        $user = User::whereIn('id', explode(',', $request->getAttribute('id')))->get();
        $response->write(json_encode($user));
        return $response;
    }

    public function me($request, $response)
    {
        $me = User::find($this->container->me);
        $response->write(json_encode($me));
        return $response;
    }

    public function friends($request, $response)
    {
        $me = User::find($this->container->me);
        $friends = User::whereIn('id', explode(',', $me['friends']))->get();
        $response->write(json_encode($friends));
        return $response;
    }

    public function create($request, $response)
    {
        $input = $request->getParsedBody();
        $now = (new DateTime())->getTimestamp() * 1000;

        $user = User::created([
            'id' => $input['id'],
            'name' => $input['name'],
            'lat' => $input['lat'],
            'lng' => $input['lng'],
            'status' => $input['status'],
            'date' => $now,
            'device' => $input['device'],
            'friends' => $input['friends']
        ]);
        /* push notification to all friends*/
        $message = [
            'content' => 'NEW_MEM',
            'id' => $input['id'],
            'name' => $input['name'],
            'date' => $now,
            'type' => 1
        ];
        $this->pushNotification($input['friends'], $message);
        $response->write(json_encode(['success' => true, 'id' => $user['id']]));
        return $response;
    }

    public function update($request, $response)
    {
        $input = $request->getParsedBody();
        $now = (new DateTime())->getTimestamp() * 1000;

        User::find($this->container->me)->update([
            'name' => $input['name'],
            'lat' => $input['lat'],
            'lng' => $input['lng'],
            'status' => $input['status'],
            'date' => $now,
            'device' => $input['device'],
            'friends' => $input['friends']
        ]);

        /* push notification to friends*/
        $message = [
            'content' => 'ONLINE',
            'id' => $input['id'],
            'name' => $input['name'],
            'date' => $now,
            'type' => 2
        ];
        $this->pushNotification($input['friends'], $message);
        $response->write(json_encode(['success' => true, 'id' => $this->container->me]));
        return $response;
    }
}
