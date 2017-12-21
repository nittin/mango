<?php
/** Please run:
 ** > php composer.phar dump-autoload -o
 ** to gen autoload*/

use App\Components\AuthComponent;

$app->group('', function () {
    $this->get('/users', 'UserController:listed');
    $this->get('/users/{id}', 'UserController:contact');
    $this->get('/users/info/me', 'UserController:me');
    $this->get('/users/info/friends', 'UserController:friends');
    $this->post('/users', 'UserController:create');
    $this->put('/users', 'UserController:update');

    $this->get('/groups', 'GroupController:listed');
    $this->post('/groups', 'GroupController:create');
    $this->put('/groups', 'GroupController:update');
    $this->get('/groups/post', 'GroupController:listPost');
    $this->post('/groups/post', 'GroupController:setPost');
    $this->put('/groups/post', 'GroupController:setPost');

})->add(new AuthComponent($container));