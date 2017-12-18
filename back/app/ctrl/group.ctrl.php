<?php

namespace App\Controllers;

class GroupController extends Controller
{
    public function listed($request, $response, $args)
    {
        $domain = $this->container->get('settings')['db']['host'];
        $username = $this->container->get('settings')['db']['username'];
        $dbname = $this->container->get('settings')['db']['database'];
        $pass = $this->container->get('settings')['db']['password'];
        $link = mysql_connect($domain, $username, $pass) or die('Cannot connect to the DB');
        mysql_select_db($dbname, $link) or die('Cannot select the DB');

        /** Check user authenticate **/
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
        /** Continue after approve **/

        /* grab the posts from the db */
        $query = "SELECT g.* 
        FROM `group` AS g,`user_group` 
        WHERE g.`id`=`user_group`.`group` AND `user_group`.`user` = '$d_user'";

        $group_result = mysql_query($query, $link) or die('Errant query:  ' . $query);

        /* create one master array of the records */
        $groups = array();
        if (mysql_num_rows($group_result)) {
            while ($group = mysql_fetch_assoc($group_result)) {
                $group_id = $group['id'];
                $query = "SELECT u.`id`, u.`name`, `user_group`.role
                FROM `user` AS u,`user_group` 
                WHERE u.`id`=`user_group`.`user` AND `user_group`.`group` = '$group_id'";

                $user_result = mysql_query($query, $link) or die('Errant query:  ' . $query);
                $users = array();
                $owned = false;
                if (mysql_num_rows($user_result)) {
                    while ($user = mysql_fetch_assoc($user_result)) {
                        if ($user['id'] === $d_user && $user['role'] === '1') {
                            $owned = true;
                        }
                        $users[] = $user;
                    }
                }
                $group['members'] = $users;
                $group['owned'] = $owned;
                $groups[] = $group;
            }
        }
        return json_encode($groups);
    }

    public function create($request, $response, $args)
    {
        $pusher = $this->container->get('pusher');
        $domain = $this->container->get('settings')['db']['host'];
        $username = $this->container->get('settings')['db']['username'];
        $dbname = $this->container->get('settings')['db']['database'];
        $pass = $this->container->get('settings')['db']['password'];
        $link = mysql_connect($domain, $username, $pass) or die('Cannot connect to the DB');
        mysql_select_db($dbname, $link) or die('Cannot select the DB');

        $data = $request->getParsedBody();
        $now = (new DateTime())->getTimestamp() * 1000;
        $d_admin = $data['admin'];
        $d_name = $data['name'];
        $d_description = $data['description'];
        $d_theme = $data['theme'];
        $d_members = $data['members'];
        /* Create group first*/
        $query = "INSERT INTO `group`(name, description, theme, admin, date) "
            . "VALUES(N'$d_name', N'$d_description', '$d_theme', '$d_admin', '$now')";
        $result = mysql_query($query, $link) or die('Errant query:  ' . $query);
        $d_group = mysql_insert_id();
        /*Then, insert member without the admin*/
        $member_array = explode(",", $d_members);
        foreach ($member_array as $item) {
            $query = "INSERT INTO `user_group`(user, `group`, role, status, date) "
                . "VALUES('$item', '$d_group', '0', '0', '$now')";
            $result = mysql_query($query, $link) or die('Errant query:  ' . $query);
        }
        /*Last, insert admin*/
        $query = "INSERT INTO `user_group`(user, `group`, role, status, date) "
            . "VALUES('$d_admin', '$d_group', '1', '1', '$now')";
        $result = mysql_query($query, $link) or die('Errant query:  ' . $query);
        /*Notify to all members*/

        $response->write(json_encode(array('success' => true, 'id' => $d_group)));
        return $response;
    }

    public function update($request, $response, $args)
    {

    }

    public function listPost($request, $response, $args)
    {
        $domain = $this->container->get('settings')['db']['host'];
        $username = $this->container->get('settings')['db']['username'];
        $dbname = $this->container->get('settings')['db']['database'];
        $pass = $this->container->get('settings')['db']['password'];
        $link = mysql_connect($domain, $username, $pass) or die('Cannot connect to the DB');
        mysql_select_db($dbname, $link) or die('Cannot select the DB');

        /** Check user authenticate **/
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

        $d_group = $url = $request->getQueryParams()['group'];
        /* Get group posts */
        $query = "SELECT gp.* 
        FROM `group_post` AS gp
        WHERE gp.`group` = '$d_group'";

        $group_result = mysql_query($query, $link) or die('Errant query:  ' . $query);

        /* create one master array of the records */
        $posted = array();
        if (mysql_num_rows($group_result)) {
            while ($group = mysql_fetch_assoc($group_result)) {
                $group['me'] = $group['user'] == $d_user;
                $posted[] = $group;
            }
        }
        $response->write(json_encode($posted));
        return $response;
    }

    public function setPost($request, $response, $args)
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