<?php
require 'vendor/autoload.php';
require 'Chat.php';

use ChatApp\Chat;

// Run the server application through the WebSocket protocol on port 8080
$app = new Ratchet\App("www.mangoround.com", 8081, '0.0.0.0');
$app->route('/chat', new Chat, array('*'));

$app->run();