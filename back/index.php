<?php

//error_reporting(E_ALL);
//ini_set('display_errors', 1);
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require 'vendor/autoload.php';
require 'key.php';
require 'img.php';

$pusher = new Pusher(
    $_KEY_PUSHER_AUTH,
    $_KEY_PUSHER_SECRET,
    $_KEY_PUSHER_APP,
    array('cluster' => $_KEY_PUSHER_CLUSTER, 'encrypted' => true)
);
$app = new \Slim\App([
    'settings' => [
        'displayErrorDetails' => true,
        'db' => [
            'domain' => 'mangoround.com',
            'user' => $_KEY_USERNAME,
            'pass' => $_KEY_PASSWORD,
            'dbname' => $_KEY_DB
        ],
        'channel' => [
            'world' => 'world-channel'
        ]
    ],
]);
$app->options('/{routes:.+}', function ($request, $response, $args) {
    return $response;
});

$app->add(function ($req, $res, $next) {
    $response = $next($req, $res);
    return $response
        ->withHeader('Access-Control-Allow-Origin', '*')
        ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
        ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
});

$app->get('/users', function (Request $request, Response $response) {
    header('Content-type: application/json');

    $domain = $this->get('settings')['db']['domain'];
    $username = $this->get('settings')['db']['user'];
    $dbname = $this->get('settings')['db']['dbname'];
    $pass = $this->get('settings')['db']['pass'];
    $link = mysql_connect($domain, $username, $pass) or die('Cannot connect to the DB');
    mysql_select_db($dbname, $link) or die('Cannot select the DB');

    /* grab the posts from the db */
    $query = "SELECT * FROM user";
    $result = mysql_query($query,$link) or die('Errant query:  '.$query);

    /* create one master array of the records */
    $posts = array();
    if(mysql_num_rows($result)) {
        while($post = mysql_fetch_assoc($result)) {
            $posts[] = $post;
        }
    }

    return json_encode(array('users'=>$posts));
});

$app->get('/users/{id}', function (Request $request, Response $response) {
    header('Content-type: application/json');

    $domain = $this->get('settings')['db']['domain'];
    $username = $this->get('settings')['db']['user'];
    $dbname = $this->get('settings')['db']['dbname'];
    $pass = $this->get('settings')['db']['pass'];

    $d_id = $request->getAttribute('id');
    $link = mysql_connect($domain, $username, $pass) or die('Cannot connect to the DB');
    mysql_select_db($dbname, $link) or die('Cannot select the DB');

    /* grab the posts from the db */
    $query = "SELECT * FROM user WHERE user.id IN ($d_id)";
    $result = mysql_query($query,$link) or die('Errant query:  '.$query);
    /* create one master array of the records */
    $posts = array();
    if(mysql_num_rows($result)) {
        while($post = mysql_fetch_assoc($result)) {
            $posts[] = $post;
        }
    }

    return json_encode(array('users'=>$posts));
});
$app->post('/users', function (Request $request, Response $response) use ($pusher) {
    header('Content-type: application/json');

    $domain = $this->get('settings')['db']['domain'];
    $username = $this->get('settings')['db']['user'];
    $dbname = $this->get('settings')['db']['dbname'];
    $pass = $this->get('settings')['db']['pass'];
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
        ."VALUES('$d_id', '$d_name', '$d_lat', '$d_lng', '$d_friends', '$d_status', '$d_device', '$d_date')";
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
});
$app->put('/users', function (Request $request, Response $response) use ($pusher)  {
    header('Content-type: application/json');

    $domain = $this->get('settings')['db']['domain'];
    $username = $this->get('settings')['db']['user'];
    $dbname = $this->get('settings')['db']['dbname'];
    $dbtable = 'user';
    $pass = $this->get('settings')['db']['pass'];
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
    $query = "UPDATE $dbtable SET name =  '$d_name',lat = '$d_lat',lng = '$d_lng',friends = '$d_friends',status = '$d_status',device = '$d_device', date = '$d_date' WHERE CONCAT(`$dbtable`.`id`) = '$d_id'";
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
});

$app->get('/assets/{height}/{width}/{id}/{type}', function (Request $request, Response $response) {
    $dir = dirname(__DIR__) . "/back/assets/img/";
    $height = $request->getAttribute('height');
    $width = $request->getAttribute('width');
    $type = $request->getAttribute('type');
    $id = $request->getAttribute('id');
    $im = new Imagick();
    $im->setBackgroundColor(new ImagickPixel('transparent'));
    $svg = file_get_contents($dir.$id);
    $im->readImageBlob($svg);

    $im->setImageFormat("png32");
    $im->resizeImage($height,$width,Imagick::FILTER_LANCZOS,1);

    header('Content-type:image/'.$type);
    echo $im;
    $im->destroy();

});
$app->get('/photo', function (Request $request, Response $response) {
    header("Content-Type: image/png");
    $url = $request->getQueryParams()['url'];
    $im = imagecreatefromjpeg($url);
    $image_marker = imagecreatefrompng('assets/img/marker-w.png');
    $image = imageCreateCorners($im,50,50, 25);

    imagealphablending($image_marker, true);
    imagesavealpha($image_marker, true);
    imagecopy($image_marker, $image, 15, 18, 0, 0, 50, 50);

//    imagecopymerge( $image,$image_marker, 0, 0, 5, 5, 50, 50, 0);
    imagepng($image_marker);
    imagedestroy($im);
    imagedestroy($image_marker);
    imagedestroy($image);
});
$app->run();
