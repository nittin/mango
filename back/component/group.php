<?php
use \Interop\Container\ContainerInterface as ContainerInterface;

class GroupController
{
    protected $container;

    // constructor receives container instance
    public function __construct(ContainerInterface $container) {
        $this->container = $container;
    }

    public function listed($request, $response, $args) {
        header('Content-type: application/json');

        $domain = $this->container->get('settings')['db']['domain'];
        $username = $this->container->get('settings')['db']['user'];
        $dbname = $this->container->get('settings')['db']['dbname'];
        $pass = $this->container->get('settings')['db']['pass'];
        $link = mysql_connect($domain, $username, $pass) or die('Cannot connect to the DB');
        mysql_select_db($dbname, $link) or die('Cannot select the DB');

        $d_user = $url = $request->getQueryParams()['user'];

        /* grab the posts from the db */
        $query = "SELECT g.* 
        FROM `group` AS g,`user_group` 
        WHERE g.`id`=`user_group`.`group` AND `user_group`.`user` = '$d_user'";

        $group_result = mysql_query($query, $link) or die('Errant query:  '.$query);

        /* create one master array of the records */
        $groups = array();
        if(mysql_num_rows($group_result)) {
            while($group = mysql_fetch_assoc($group_result)) {
                $group_id = $group['id'];
                $query = "SELECT u.`id`, u.`name`, `user_group`.role
                FROM `user` AS u,`user_group` 
                WHERE u.`id`=`user_group`.`user` AND `user_group`.`group` = '$group_id'";

                $user_result = mysql_query($query, $link) or die('Errant query:  '.$query);
                $users = array();
                if(mysql_num_rows($user_result)) {
                    while($user = mysql_fetch_assoc($user_result)) {
                        $users[] = $user;
                    }
                }
                $group['members'] = $users;
                $groups[] = $group;
            }
        }
        return json_encode($groups);
    }

    public function create($request, $response, $args) {
        header('Content-type: application/json');

        $pusher = $this->container->get('pusher');
        $domain = $this->container->get('settings')['db']['domain'];
        $username = $this->container->get('settings')['db']['user'];
        $dbname = $this->container->get('settings')['db']['dbname'];
        $pass = $this->container->get('settings')['db']['pass'];
        $link = mysql_connect($domain, $username, $pass) or die('Cannot connect to the DB');
        mysql_select_db($dbname, $link) or die('Cannot select the DB');

        $data = $request->getParsedBody();
//    $now = new DateTime();
        $d_date = '';
        $d_admin = $data["admin"];
        $d_name = $data["name"];
        $d_description = $data["description"];
        $d_theme = $data["theme"];
        $d_members = $data["members"];
        /* grab the posts from the db */
        $query = "INSERT INTO `group`(name, description, theme, admin, date) "
            ."VALUES(N'$d_name', N'$d_description', '$d_theme', '$d_admin', '$d_date')";
        $result = mysql_query($query, $link) or die('Errant query:  ' . $query);
        $d_group = mysql_insert_id();
        /*Insert rest member*/
        $member_array = explode(",", $d_members);
        foreach ($member_array as $item) {
            $query = "INSERT INTO `user_group`(user, `group`, role, status, date) "
                ."VALUES('$item', '$d_group', '0', '0', '$d_date')";
            $result = mysql_query($query, $link) or die('Errant query:  ' . $query);
        }
        /*Insert admin*/
        $query = "INSERT INTO `user_group`(user, `group`, role, status, date) "
            ."VALUES('$d_admin', '$d_group', '1', '1', '$d_date')";
        $result = mysql_query($query, $link) or die('Errant query:  ' . $query);

        $answer = array('success' => true, 'id' => $d_group);
        return json_encode($answer);
    }

    public function update($request, $response, $args)  {
        
    }

    public function listPost($request, $response, $args) {
        header('Content-type: application/json');

        $domain = $this->container->get('settings')['db']['domain'];
        $username = $this->container->get('settings')['db']['user'];
        $dbname = $this->container->get('settings')['db']['dbname'];
        $pass = $this->container->get('settings')['db']['pass'];
        $link = mysql_connect($domain, $username, $pass) or die('Cannot connect to the DB');
        mysql_select_db($dbname, $link) or die('Cannot select the DB');

        $d_user = $url = $request->getQueryParams()['user'];
        $d_group = $url = $request->getQueryParams()['group'];

        /* grab the posts from the db */
        $query = "SELECT gp.* 
        FROM `group_post` AS gp
        WHERE gp.`group` = '$d_group'";

        $group_result = mysql_query($query, $link) or die('Errant query:  '.$query);

        /* create one master array of the records */
        $posted = array();
        if(mysql_num_rows($group_result)) {
            while($group = mysql_fetch_assoc($group_result)) {
                $group['me'] = $group['user'] == $d_user;
                $posted[] = $group;
            }
        }
        return json_encode($posted);
    }

    public function createPost($request, $response, $args) {
        header('Content-type: application/json');

        $pusher = $this->container->get('pusher');
        $domain = $this->container->get('settings')['db']['domain'];
        $username = $this->container->get('settings')['db']['user'];
        $dbname = $this->container->get('settings')['db']['dbname'];
        $pass = $this->container->get('settings')['db']['pass'];
        $link = mysql_connect($domain, $username, $pass) or die('Cannot connect to the DB');
        mysql_select_db($dbname, $link) or die('Cannot select the DB');

        $data = $request->getParsedBody();
//    $now = new DateTime();
        $d_date = '';
        $d_group = $data["group"];
        $d_user = $data["user"];
        $d_content = $data["content"];
        $d_lat = $data["lat"];
        $d_lng = $data["lng"];
        /* grab the posts from the db */
        $query = "INSERT INTO `group_post`(`group`, user, date, content, lat, lng) "
            ."VALUES('$d_group', '$d_user', '$d_date', N'$d_content', '$d_lat', '$d_lng', '$d_date')";
        $result = mysql_query($query, $link) or die('Errant query:  ' . $query);
        $d_group = mysql_insert_id();
        /*Insert rest member*/
        $member_array = explode(",", $d_members);
        foreach ($member_array as $item) {
            $query = "INSERT INTO `user_group`(user, `group`, role, status, date) "
                ."VALUES('$item', '$d_group', '0', '0', '$d_date')";
            $result = mysql_query($query, $link) or die('Errant query:  ' . $query);
        }
        /*Insert admin*/
        $query = "INSERT INTO `user_group`(user, `group`, role, status, date) "
            ."VALUES('$d_group', '$d_group', '1', '1', '$d_date')";
        $result = mysql_query($query, $link) or die('Errant query:  ' . $query);

        $answer = array('success' => true, 'id' => $d_group);
        return json_encode($answer);
    }
}