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
    $this->get('/users/me/profile', 'UserController:me');
    $this->get('/users/me/friends', 'UserController:friends');
    $this->put('/users/me/ping', 'UserController:ping');
    $this->post('/users/me/out', 'UserController:signOut');
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
    $this->get('/fb/friends', 'FBController:friends');
    $this->get('/fb/valid', 'FBController:valid');

})->add(new AuthComponent($container));

$app->post('/auth', 'FBController:auth');
