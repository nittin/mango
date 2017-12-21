<?php

namespace App\Components;

use App\Models\Token;
use App\Models\User;

class AuthComponent extends Component
{
    private $message = [
        '401' => ['success' => false, 'message' => '401 User does not authorize'],
        '404' => ['success' => false, 'message' => 'Not found']
    ];

    public function __invoke($request, $response, $next)
    {
        /** Check user authenticate **/
        $token = $request->getHeaderLine('Authorization');
        $auth = Token::where('token', $token)->get()->first();
        if (!count($auth)) {
            $response->write(json_encode($this->message['401']));
            return $response->withStatus(401);
        } else {
            $this->container->me = $auth['id'];
            $response = $next($request, $response);
            return $response;
        }

    }

}