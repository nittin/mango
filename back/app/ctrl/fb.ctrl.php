<?php

namespace App\Controllers;

use App\Models\Token;
use App\Models\User;
use \Facebook\Facebook;
use Facebook\Facebook\Exceptions\FacebookResponseException;
use Facebook\Facebook\Exceptions\FacebookSDKException;

class FBController extends Controller
{

    public function auth($request, $response)
    {
        $data = $request->getParsedBody();
        $code = $data['code'];
        $environment = $data['env'] == 'ci' ? 'ci' : 'product';
        $redirectUri = $this->container->get('settings')['fb'][$environment];
        $fb_app = $this->container->fb[$environment];
        $fb_auth = $fb_app->getOAuth2Client();

        // Obtain User Token
        $d_token = $fb_auth->getAccessTokenFromCode($code, $redirectUri)->getValue();

        if (!$d_token) { //Stop if check 'user code' fail
            $response->write(json_encode($this->message['401']));
            return $response->withStatus(401);
        }
        $fb_app->setDefaultAccessToken($d_token);
        // Get user info from this Token ($d_token)
        $fb_meta = $fb_auth->debugToken($d_token);
        /* Redirect browser */
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
        $_SESSION['user'] = $d_user_id;
        $_SESSION['environment'] = $environment;
        $me = $fb_app->get('/me?fields=name,first_name')->getDecodedBody();
        $friends = $fb_app->get('/me/friends?fields=id')->getDecodedBody();
        $me['token'] = $d_token;
        $me['photo'] = $fb_user_photo_url;
        $me['first_time'] = $first_time;
        $me['friends'] = implode(',',  array_map(function ($i) { return $i['id']; }, $friends['data']));
        User::updateOrCreate(['id' => $d_user_id], ['name' => $me['name'], 'friends' => $me['friends'] , 'status' => 1]);
        $response->write(json_encode($me));
        return $response;
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
}
