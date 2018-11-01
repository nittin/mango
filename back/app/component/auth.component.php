<?php

namespace App\Components;

use App\Models\Token;
use App\Models\User;

class AuthComponent extends Component
{

    public function __invoke($request, $response, $next)
    {
        /** Check user authenticate **/
        $token = $request->getHeaderLine('Authorization');

        if ($_SESSION['token'] && $_SESSION['token'] == $token) {
            $this->container->token = $_SESSION['token'];
            $this->container->me = $_SESSION['user'];
            $this->container->environment = $_SESSION['environment'];
            if ($_SESSION['environment'] != 'gg') {
                $this->container->fb_app = $this->container->fb[$_SESSION['environment']];
                $this->container->fb_app->setDefaultAccessToken($_SESSION['token']);
            }
            return $next($request, $response);
        } else {
            $auth = Token::where('token', $token)->get()->first();
            if (!count($auth)) {
                $response->write(json_encode($this->message['401']));
                return $response->withStatus(401);
            } else {
                $_SESSION['token'] = $token;
                $_SESSION['user'] = $auth['id']."";
                $_SESSION['environment'] = $auth['environment'];
                $this->container->token = $_SESSION['token'];
                $this->container->me = $_SESSION['user'];
                $this->container->environment = $_SESSION['environment'];
                if ($_SESSION['environment'] != 'gg') {
                    $this->container->fb_app = $this->container->fb[$_SESSION['environment']];
                    $this->container->fb_app->setDefaultAccessToken($_SESSION['token']);
                }
                return $next($request, $response);
            }
        }
    }
}