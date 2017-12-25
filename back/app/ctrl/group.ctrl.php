<?php

namespace App\Controllers;

use App\Models\Group;
use DateTime;
use function React\Promise\map;

class GroupController extends Controller
{
    private function pushNotification($friendsStr, $message)
    {
        /* push notification to all friends*/
        $pusher = $this->container->pusher;
        $friend_array = explode(',', $friendsStr);
        foreach ($friend_array as $f) {
            if ($f) {
                $pusher->trigger($f, 'groups-work', $message);
            }
        }
    }

    private function singleNotification($friend, $message)
    {
        $pusher = $this->container->pusher;
        if ($friend) {
            $pusher->trigger($friend, 'groups-work', $message);
        }
    }

    public function listed($request, $response)
    {
        $groups = Group::with(['members' => function ($query) {
            $query->orderBy('role', 'desc');
        }])
            ->whereHas('members', function ($q) {
                $q->where('user', $this->container->me);
            })
            ->get()->each(function ($group) {
                $group->members->each(function ($user) use ($group) {
                    $user->admin = $group->admin == $user->id;
                });
                $group->owner = $group->admin == $this->container->me;
            });
        return $groups->toJson();
    }

    public function create($request, $response)
    {
        $input = $request->getParsedBody();
        $now = (new DateTime())->getTimestamp() * 1000;

        $group = Group::create([
            'name' => $input['name'],
            'description' => $input['description'],
            'admin' => $this->container->me,
            'theme' => $input['theme']
        ]);
        $message = [
            'content' => 'GROUP',
            'id' => $this->container->me,
            'name' => $group['name'],
            'date' => $now,
            'type' => 1
        ];
        $group->members()->attach($this->container->me, [
            'role' => 1,
            'status' => 1,
            'date' => $now
        ]);
        array_map(function ($id) use ($group, $now, $message) {
            $group->members()->attach($id, [
                'role' => 0,
                'status' => 1,
                'date' => $now
            ]);
            $this->singleNotification($id, $message);
        }, explode(',', $input['members']));

        $response->write(json_encode(array('success' => true, 'id' => $group['id'])));
        return $response;
    }

    public function update($request, $response)
    {

    }

    public function listPost($request, $response)
    {
        $groups = Group::find($request->getQueryParams()['group']);
        if (!$groups) {
            $response->write(json_encode($this->message['404']));
            return $response->withStatus(404);
        }
        $access = $groups->whereHas('members', function ($q) {
            $q->where('user', $this->container->me);
        })->get();
        if ($access->isNotEmpty()) {
            $response->write($groups->posts->toJson());
            return $response;
        } else {
            $response->write(json_encode($this->message['403']));
            return $response->withStatus(403);
        }
    }

    public function setPost($request, $response)
    {
        $pusher = $this->container->get('pusher');
        $domain = $this->container->get('settings')['db']['host'];
        $username = $this->container->get('settings')['db']['username'];
        $dbname = $this->container->get('settings')['db']['database'];
        $pass = $this->container->get('settings')['db']['password'];
        $link = mysql_connect($domain, $username, $pass) or die('Cannot connect to the DB');
        mysql_select_db($dbname, $link) or die('Cannot select the DB');

        $token = $request->getHeaderLine('Authorization');
        $query_token = "SELECT id FROM user_token WHERE token='$token'";
        $auth_result = mysql_query($query_token, $link) or die('Errant query:  ' . $query_token);
        if (mysql_num_rows($auth_result)) {
            $d_user = mysql_fetch_assoc($auth_result)['id'];
        } else {
            $answer = array('success' => false, 'message' => '401 User does not authorize');
            $response->write(json_encode($answer));
            return $response->withStatus(404);
        }

        $data = $request->getParsedBody();
        $now = (new DateTime())->getTimestamp() * 1000;
        $d_id = $data['id'];
        $d_group = $data['group'];
        $d_description = $data['description'];
        $d_lat = $data['lat'];
        $d_lng = $data['lng'];
        if ($d_id) {
            $query = "UPDATE `group_post` SET description=N'$d_description', lat='$d_lat', lng='$d_lng' "
                . "WHERE id='$d_id'";
        } else {
            $query = "INSERT INTO `group_post`(`group`, user, date, description, lat, lng) "
                . "VALUES('$d_group', '$d_user', '$now', N'$d_description', '$d_lat', '$d_lng')";
        }
        $result = mysql_query($query, $link) or die('Errant query:  ' . $query);
        $d_post = mysql_insert_id();


        $answer = array('success' => true, 'id' => $d_post, 'at' => $now);
        $response->write(json_encode($answer));
        return $response;
    }
}