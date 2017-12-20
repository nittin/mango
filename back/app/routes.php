<?php
/** Please run:
 ** > php composer.phar dump-autoload -o
 ** to gen autoload*/

$app->get('/users', 'UserController:listed');
$app->get('/users/{id}', 'UserController:contact');
$app->get('/users/info/me', 'UserController:me');
$app->get('/users/info/friends', 'UserController:friends');
$app->post('/users', 'UserController:create');
$app->put('/users', 'UserController:update');

$app->get('/groups', 'GroupController:listed');
$app->post('/groups', 'GroupController:create');
$app->put('/groups', 'GroupController:update');
$app->get('/groups/post', 'GroupController:listPost');
$app->post('/groups/post', 'GroupController:setPost');
$app->put('/groups/post', 'GroupController:setPost');
