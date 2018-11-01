<?php

namespace App\Controllers;

use App\Models\Token;
use App\Models\User;
use Google\Google_Client;

class GGController extends Controller
{

    public function auth($request, $response)
    {
        $data = $request->getParsedBody();
        $d_token = $data['token'];

        if (!$d_token) { //Stop if check 'user code' fail
            $response->write(json_encode($this->message['401']));
            return $response->withStatus(401);
        }

        $client = $this->container->get('settings')['gg'];
        $payload = $client->verifyIdToken($d_token);
        if (!$payload) {
            $response->write(json_encode($this->message['403']));
            return $response->withStatus(403);
        }
        $d_user_id = $payload['sub'];
        $d_user_photo_url = $payload['picture'];
        makemarker($d_user_photo_url, $d_user_id);
        Token::updateOrCreate(['id' => $d_user_id], ['token' => $d_token, 'scope' => 'email', 'environment' => 'google', 'expire' => $payload['exp']]);
        $first_time = User::where('id', $d_user_id)->get()->isEmpty();

        $_SESSION['token'] = $d_token;
        $_SESSION['user'] = $d_user_id;
        $_SESSION['environment'] = 'google';

        $me = [];
        $me['id'] = $d_user_id;
//        $me['token'] = $d_token;
        $me['name'] = $payload['name'];
        $me['first_name'] = $payload['given_name'];
        $me['photo'] = $d_user_photo_url;
        $me['first_time'] = $first_time;
        User::updateOrCreate(['id' => $d_user_id],
            ['name' => $me['name'], 'first_name' => $me['first_name'], 'status' => 1]);
        $response->write(json_encode($me));
        return $response;
    }

    public function hello($request, $response)
    {
        $response->write('hello page!');
        return $response;
    }

    public function valid($request, $response)
    {
        try {
            $meta = $this->container->fb_app->getOAuth2Client()->debugToken($this->container->token);
            $response->write(json_encode(['valid' => $meta->getIsValid()]));
            return $response;
        } catch (FacebookSDKException $e) {
            $response->write(json_encode($this->message['401']));
            return $response;
        }

    }
}
