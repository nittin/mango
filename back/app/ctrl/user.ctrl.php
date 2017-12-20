<?php

namespace App\Controllers;

use App\Models\Token;
use App\Models\User;

class UserController extends Controller
{
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
        /** Check user authenticate **/
        $token = $request->getHeaderLine('Authorization');
        $auth = Token::where('token', $token)->get()->first();
        if (!count($auth)) {
            $response->write(json_encode($this->message['401']));
            return $response->withStatus(401);
        } else {
            $user = User::find($auth['id']);
            $response->write(json_encode($user));
            return $response;
        }
    }

    public function friends($request, $response)
    {
        $token = $request->getHeaderLine('Authorization');
        $auth = Token::where('token', $token)->get()->first();
        if (!count($auth)) {
            $response->write(json_encode($this->message['401']));
            return $response->withStatus(401);
        } else {
            $user = User::find($auth['id']);
            $friends = User::whereIn('id', explode(',', $user['friends']))->get();
            $response->write(json_encode($friends));
            return $response;
        }
    }

    public function create($request, $response, $args)
    {
        header('Content-type: application/json');

        $pusher = $this->container->get('pusher');
        $domain = $this->container->get('settings')['db']['host'];
        $username = $this->container->get('settings')['db']['username'];
        $dbname = $this->container->get('settings')['db']['database'];
        $pass = $this->container->get('settings')['db']['password'];
        $link = mysql_connect($domain, $username, $pass) or die('Cannot connect to the DB');
        mysql_select_db($dbname, $link) or die('Cannot select the DB');

        $data = $request->getParsedBody();
        $d_id = $data["id"];
        $d_name = $data["name"];
        $d_lat = $data["lat"];
        $d_lng = $data["lng"];
        $d_status = $data["status"];
        $d_date = $data["date"];
        $d_device = $data["device"];
        $d_friends = $data["friends"];
        /* grab the posts from the db */
        $query = "INSERT INTO user(id, name, lat, lng, friends, status, device, date) "
            . "VALUES('$d_id', N'$d_name', '$d_lat', '$d_lng', '$d_friends', '$d_status', '$d_device', '$d_date')";
        $result = mysql_query($query, $link) or die('Errant query:  ' . $query);
        /* push notification to friends*/
        $message['content'] = 'new user';
        $message['id'] = $d_id;
        $message['name'] = $d_name;
        $message['date'] = $d_date;
        $message['type'] = 1;
        $friend_array = explode(",", $d_friends);
        foreach ($friend_array as $f) {
            $pusher->trigger($f, 'user-online', $message);
        }
        $answer = array('success' => true, 'id' => mysql_insert_id());
        return json_encode($answer);
    }

    public function update($request, $response, $args)
    {
        header('Content-type: application/json');
        $pusher = $this->container->get('pusher');
        $domain = $this->container->get('settings')['db']['host'];
        $username = $this->container->get('settings')['db']['username'];
        $dbname = $this->container->get('settings')['db']['database'];
        $dbtable = 'user';
        $pass = $this->container->get('settings')['db']['password'];
        $link = mysql_connect($domain, $username, $pass) or die('Cannot connect to the DB');
        mysql_select_db($dbname, $link) or die('Cannot select the DB');

        $data = $request->getParsedBody();
        $d_id = $data["id"];
        $d_name = $data["name"];
        $d_lat = $data["lat"];
        $d_lng = $data["lng"];
        $d_status = $data["status"];
        $d_date = $data["date"];
        $d_device = $data["device"];
        $d_friends = $data["friends"];
        /* grab the posts from the db */
        $query = "UPDATE $dbtable SET name =  N'$d_name',lat = '$d_lat',lng = '$d_lng',"
            . "friends = '$d_friends',status = '$d_status',device = '$d_device', date = '$d_date'"
            . " WHERE CONCAT(`$dbtable`.`id`) = '$d_id'";
        $result = mysql_query($query, $link) or die('Errant query:  ' . $query);
        /* push notification to friends*/
        $message['content'] = 'online';
        $message['id'] = $d_id;
        $message['name'] = $d_name;
        $message['date'] = $d_date;
        $message['type'] = 2;
        $friend_array = explode(",", $d_friends);
        foreach ($friend_array as $f) {
            $pusher->trigger($f, 'user-online', $message);
        }
        /* answer user*/
        $answer = array('success' => true, 'id' => $d_id);
        return json_encode($answer);
    }
}
