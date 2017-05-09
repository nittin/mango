<?php
require 'vendor/autoload.php';
require 'key.php';


$options = array(
    'cluster' => 'ap1',
    'encrypted' => true
);
$pusher = new Pusher(
    $_KEY_PUSHER_AUTH,
    $_KEY_PUSHER_SECRET,
    $_KEY_PUSHER_APP,
    $options
);

$data['message'] = 'hello world';
$pusher->trigger('my-channel', 'my-event', $data);