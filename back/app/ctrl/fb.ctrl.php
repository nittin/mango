<?php

namespace App\Controllers;

use App\Models\Token;
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
        $fb_setting = $this->container->get('settings')['fb'];
        $fb_app = $fb_setting[$environment]['app'];
        $fb_secret = $fb_setting[$environment]['secret'];
        $fb_redirect = $fb_setting[$environment]['redirect'];

        // Obtain User Token
        $fb_user_token = json_decode($this->cURL("https://graph.facebook.com/v2.9/oauth/access_token"
            . "?client_id=$fb_app"
            . "&client_secret=$fb_secret"
            . "&redirect_uri=$fb_redirect"
            . "&code=$code"
        ), true);

        $d_token = $fb_user_token['access_token'];
        if (!$d_token) { //Stop if check 'user code' fail
            $response->write(json_encode($this->message['401']));
            return $response->withStatus(401);
        }
        // Get user info from this Token
        $fb_check_user = json_decode($this->cURL("https://graph.facebook.com/debug_token"
            . "?input_token=$d_token"
            . "&access_token=$fb_app|$fb_secret"
        ), true);
//        $user = $fb->get("/debug_token?input_token=$d_token", "$fb_app|$fb_secret");
        /* Redirect browser */
        $d_user_id = $fb_check_user['data']['user_id'];
        $d_expire = $fb_check_user['data']['expires_at'] * 1000;
        $d_scope = implode(',', $fb_check_user['data']['scopes']);

        // get photo
        $fb_user_photo_url = "https://graph.facebook.com/$d_user_id/picture?width=200&height=200&access_token=$d_token";
        makemarker($fb_user_photo_url, $d_user_id);

        Token::updateOrCreate(['id' => $d_user_id], [
            'token' => $d_token,
            'scope' => $d_scope,
            'environment' => $environment,
            'expire' => $d_expire]);
        $_SESSION['token'] = $d_token;
        $_SESSION['user'] = $d_user_id;
        $_SESSION['environment'] = $environment;

        $response->write(json_encode(['id' => $d_user_id, 'token' => $d_token, 'photo' => $fb_user_photo_url]));
        return $response;
    }

    public function me($request, $response)
    {
        $d_api = '/me?fields=name,id,picture{url},cover,first_name';
        try {
            // Returns a `Facebook\FacebookResponse` object
            $fb_response = $this->container->fb_app->get($d_api, $this->container->token);
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
}
