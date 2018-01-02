<?php
/** Please run:
 ** > php composer.phar dump-autoload -o
 ** to gen autoload*/

use App\Components\AuthComponent;
use App\Components\FBComponent;

require 'constant/notification.constant.php';


$app->group('', function () {
    $this->get('/users', 'UserController:listed');
    $this->get('/users/{id}', 'UserController:contact');
    $this->get('/users/info/me', 'UserController:me');
    $this->get('/users/info/friends', 'UserController:friends');
    $this->post('/users', 'UserController:create');
    $this->put('/users', 'UserController:update');
    $this->get('/notifications', 'UserController:pullNotifications');

    $this->get('/groups', 'GroupController:listed');
    $this->post('/groups', 'GroupController:create');
    $this->put('/groups', 'GroupController:update');
    $this->get('/groups/post', 'GroupController:listPost');
    $this->post('/groups/post', 'GroupController:newPost');
    $this->put('/groups/post', 'GroupController:editPost');
    $this->get('/fb/me', 'FBController:me');

})->add(new AuthComponent($container));

$app->group('', function () {
    $this->post('/auth', 'FBController:auth');
})->add(new FBComponent($container));
