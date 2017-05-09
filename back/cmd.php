<?php
require 'vendor/autoload.php';
require 'key.php';


$options = array(
    'cluster' => $_KEY_PUSHER_CLUSTER,
    'encrypted' => true
);
$pusher = new Pusher(
    $_KEY_PUSHER_AUTH,
    $_KEY_PUSHER_SECRET,
    $_KEY_PUSHER_APP,
    $options
);

$data['message'] = 'Restart now!';
$data['type'] = '0';
$pusher->trigger('world-channel', 'system', $data);