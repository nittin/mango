<?php

error_reporting(E_ERROR);
ini_set('display_errors', 1);
session_start();
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use \Facebook\Facebook as FB;

require 'vendor/autoload.php';
require 'key.php';
require 'img.php';

$pusher = new Pusher(
    $_KEY_PUSHER_AUTH,
    $_KEY_PUSHER_SECRET,
    $_KEY_PUSHER_APP,
    array('cluster' => $_KEY_PUSHER_CLUSTER, 'encrypted' => true)
);
$fb_ci = new FB([
    'app_id' => $_KEY_FB_CI_APP,
    'app_secret' => $_KEY_FB_CI_SECRET,
    'default_graph_version' => 'v2.9',
]);
$fb = new FB([
    'app_id' => $_KEY_FB_APP,
    'app_secret' => $_KEY_FB_SECRET,
    'default_graph_version' => 'v2.9',
]);
if (!ini_get('date.timezone')) {
    date_default_timezone_set('GMT');
}
$app = new \Slim\App([
    'settings' => [
        'displayErrorDetails' => true,
        'db' => [
            'driver' => 'mysql',
            'host' => $_KEY_DB_SERVER,
            'database' => $_KEY_DB,
            'username' => $_KEY_USERNAME,
            'password' => $_KEY_PASSWORD,
            'charset' => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix' => ''
        ],
        'channel' => [
            'world' => 'world-channel'
        ],
        'fb' => [
            'ci' => $_KEY_FB_CI_REDIRECT,
            'product' => $_KEY_FB_REDIRECT,
        ]
    ]
]);
$container = $app->getContainer();
$capsule = new \Illuminate\Database\Capsule\Manager;
$capsule->addConnection($container['settings']['db']);
$capsule->setAsGlobal();
$capsule->bootEloquent();
$container['pusher'] = function ($container) use ($pusher) {
    return $pusher;
};
$container['db'] = function ($container) use ($capsule) {
    return $capsule;
};
$container['pusher'] = function ($container) use ($pusher) {
    return $pusher;
};
$container['fb'] = function ($container) use ($fb, $fb_ci) {
    return ['product' => $fb, 'ci' => $fb_ci];
};
$container['UserController'] = function ($container) {
    return new \App\Controllers\UserController($container);
};
$container['GroupController'] = function ($container) {
    return new \App\Controllers\GroupController($container);
};
$container['FBController'] = function ($container) {
    return new \App\Controllers\FBController($container);
};

$app->options('/{routes:.+}', function ($request, $response, $args) {
    return $response;
});

$app->add(function ($req, $res, $next) {
    $response = $next($req, $res);
    return $response
        ->withHeader('Content-type', 'application/json')
        ->withHeader('Access-Control-Allow-Credentials', 'true')
        ->withHeader('Access-Control-Allow-Origin', '*')
        ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
        ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
});
$app->post('/notify/wave', function (Request $request, Response $response) use ($pusher)  {
    header('Content-type: application/json');

    $data = $request->getParsedBody();
    $d_id = $data["id"];
    $d_name = $data["name"];
    $d_target = $data["target_id"];
    $message['content'] = $d_name.' say hi!';
    $message['id'] = $d_id;
    $message['name'] = $d_name;
        $pusher->trigger($d_target, 'user-wave', $message);
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
$app->get('/testphoto', function (Request $request, Response $response) {

    $image_origin = imagecreatefromjpeg("assets/users/origin/1749952611685681.jpg");
    $image_50x50 = imagecreatetruecolor(50, 50);

    $o_width = imagesx($image_origin);
    $o_height = imagesy($image_origin);
    imagecopyresampled($image_50x50, $image_origin, 0, 0, 0, 0, 50, 50, $o_width, $o_height);
    $image_circle = imageCreateCorners($image_50x50,50,50, 25);
    $image_marker = imagecreatefrompng('assets/img/marker.png');
    $image_pin = imagecreatefrompng('assets/img/pin.png');

    imagealphablending($image_marker, true);
    imagesavealpha($image_marker, true);
    imagecopy($image_marker, $image_circle, 10, 10, 0, 0, 50, 50);
    imagepng($image_marker, "assets/users/marker/1749952611685681.png");

    imagealphablending($image_pin, true);
    imagesavealpha($image_pin, true);
    imagecopy($image_pin, $image_circle, 10, 10, 0, 0, 50, 50);
    imagepng($image_pin, "assets/users/pin/1749952611685681.png");



    header('Content-Type: image/png');
    imagepng($image_marker);
    imagedestroy($image_marker);
});
$app->post('/photo/me', function (Request $request, Response $response) {
    header('Content-type: application/json');
    $url = $request->getQueryParams()['url'];

    $data = $request->getParsedBody();
    $d_id = $data["id"];
    copy($url, "assets/users/origin/$d_id.jpg");

    $answer = array('success' => true, 'id' => $d_id);
    return json_encode($answer);
});
$app->post('/fb', function (Request $request, Response $response) use ($fb, $fb_ci){
    header('Content-type: application/json');
    $data = $request->getParsedBody();
    $d_api = $data["api"];
    $d_token = $data["token"];
    $environment = $data["env"];

    try {
        // Returns a `Facebook\FacebookResponse` object
        if ($environment == "ci") {
            $fb_response = $fb_ci->get($d_api, $d_token);
        } else {
            $fb_response = $fb->get($d_api, $d_token);
        }
    } catch (Facebook\Exceptions\FacebookResponseException $e) {
        echo 'Graph returned an error: ' . $e->getMessage();
        exit;
    } catch (Facebook\Exceptions\FacebookSDKException $e) {
        echo 'Facebook SDK returned an error: ' . $e->getMessage().' - '.$d_api.' - '.$d_token;
        exit;
    }
    $decoded = $fb_response->getDecodedBody();
    return json_encode($decoded,true);

});

require 'app/routes.php';

$app->run();
