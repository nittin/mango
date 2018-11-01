<?php

namespace App\Controllers;

use App\Models\Token;
use App\Models\User;
use \Facebook\Facebook;
use Facebook\Facebook\Exceptions\FacebookResponseException;
use Facebook\Facebook\Exceptions\FacebookSDKException;

class FBController extends Controller
{

    private function auth($request, $response, $environment)
    {
        $fb_app = $this->container->fb[$environment];
        $fb_auth = $fb_app->getOAuth2Client();

        $data = $request->getParsedBody();
        $d_code = $data['code'];
        $d_token = $data['token'];
        if (!$d_token) {
            $redirectUri = $this->container->get('settings')['fb'][$environment];
            // Obtain User Token
            $d_token = $fb_auth->getAccessTokenFromCode($d_code, $redirectUri)->getValue();
            // Stop if check 'user code' fail
            if (!$d_token) {
                $response->write(json_encode($this->message['401']));
                return $response->withStatus(401);
            }
        }
        // Get user info from this Token
        $fb_meta = $fb_auth->debugToken($d_token);

        // Stop if check 'user token' fail
        if (!$fb_meta->getIsValid()) {
            $response->write(json_encode($this->message['401']));
            return $response->withStatus(401);
        }
        $fb_app->setDefaultAccessToken($d_token);

        $d_user_id = $fb_meta->getUserId();
        $d_expire = $fb_meta->getExpiresAt();
        $d_scope = implode(',', $fb_meta->getScopes());

        $fb_user_photo_url = "https://graph.facebook.com/$d_user_id/picture?width=200&height=200&access_token=$d_token";
        makemarker($fb_user_photo_url, $d_user_id);

        Token::updateOrCreate(['id' => $d_user_id], [
            'token' => $d_token,
            'scope' => $d_scope,
            'environment' => $environment,
            'expire' => $d_expire]);
        $first_time = User::where('id', $d_user_id)->get()->isEmpty();

        $_SESSION['token'] = $d_token;
        $_SESSION['user'] = $d_user_id."";
        $_SESSION['environment'] = $environment;
        $me = $fb_app->get('/me?fields=name,first_name')->getDecodedBody();
        $friends = $fb_app->get('/me/friends?fields=id')->getDecodedBody();
        if ($d_code) {
            $me['token'] = $d_token;
        }
        $me['photo'] = $this->container->get('settings')['host']['assets'] . "users/origin/$d_user_id.jpg";
        $me['first_time'] = $first_time;
        $me['friends'] = implode(',', array_map(function ($i) {
            return $i['id'];
        }, $friends['data']));

        User::updateOrCreate(['id' => $d_user_id], [
            'name' => $me['name'],
            'first_name' => $me['first_name'],
            'friends' => $me['friends'],
            'status' => 1]);
        foreach ($friends['data'] as $friend) {
            $this->makeFriend($d_user_id, $friend['id']);
        }
        $response->write(json_encode($me));
        return $response;
    }

    public function authCI($request, $response)
    {
        return $this->auth($request, $response, 'ci');

    }

    public function authProduct($request, $response)
    {
        return $this->auth($request, $response, 'product');

    }

    public function me($request, $response)
    {
        $d_api = '/me?fields=name,id,picture{url},cover,first_name';
        try {
            // Returns a `Facebook\FacebookResponse` object
            $fb_response = $this->container->fb_app->get($d_api);
            $response->write(json_encode($fb_response->getDecodedBody()));
            return $response;
        } catch (FacebookResponseException $e) {
            $response->write(json_encode($this->message['401']));
            return $response;
        } catch (FacebookSDKException $e) {
            $response->write(json_encode($this->message['401']));
            return $response;
        }

    }

    public function friends($request, $response)
    {
        $d_api = '/me/friends?fields=name,first_name,id,picture{url},cover';
        try {
            // Returns a `Facebook\FacebookResponse` object
            $fb_response = $this->container->fb_app->get($d_api);
            $response->write(json_encode($fb_response->getDecodedBody()));
            return $response;
        } catch (FacebookResponseException $e) {
            $response->write(json_encode($this->message['401']));
            return $response;
        } catch (FacebookSDKException $e) {
            $response->write(json_encode($this->message['401']));
            return $response;
        }

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

    public function adminGenUserProduct($request, $response)
    {
        $d_scope = 'user_friends,email,public_profile';
        $environment = 'product';
        $d_expire = '2018';

        $fb_app = $this->container->fb[$environment];
        $fb_auth = $fb_app->getOAuth2Client();

        $data = $request->getParsedBody();
        $d_token = $data['token'];

        // Get user info from this Token
        $fb_meta = $fb_auth->debugToken($d_token);

        // Stop if check 'user token' fail
        if (!$fb_meta->getIsValid()) {
            $response->write(json_encode($this->message['401']));
            return $response->withStatus(401);
        }
        $d_user_id = $fb_meta->getUserId();

        $fb_app->setDefaultAccessToken($d_token);
        $friends = $fb_app->get('/me/friends?fields=id,name,first_name')->getDecodedBody();
        foreach ($friends['data'] as $friend) {
            Token::updateOrCreate(['id' => $friend['id']], [
                'scope' => $d_scope,
                'environment' => $environment,
                'expire' => $d_expire]);

            User::updateOrCreate(['id' => $friend['id']], [
                'name' => $friend['name'],
                'first_name' => $friend['first_name'],
                'friends' => $d_user_id,
                'status' => 1]);

            $fb_user_photo_url = "https://graph.facebook.com/".$friend['id']."/picture?width=200&height=200&access_token=$d_token";
            makemarker($fb_user_photo_url, $friend['id']);

            $this->makeFriend($friend['id'], $d_user_id);
        }
        $response->write(json_encode($friends));
    }

    private function makeFriend($meId, $friendId)
    {
        $me = User::find($meId);
        $friend = User::find($friendId);
        if ($meId == $friendId) {
            return;
        }
        if (!$friend) {
            // Temp friend, wait for friend join
            $me->friends()->detach($friendId);
            $me->friends()->attach($friendId, ['status' => '2']);
            return;
        } else if ($friend->isWaitForFriend($meId)) {
            // Re-update true friend
            $friend->friends()->updateExistingPivot($meId, ['status' => '1']);
        }
        $me->friends()->detach($friendId);
        $me->friends()->attach($friendId, ['status' => '1']);
    }
}
