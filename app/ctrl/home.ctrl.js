'use strict';

angular.module('myApp.home', ['ngRoute'])

    .config(function ($routeProvider) {
        $routeProvider.when('/', {
            templateUrl: 'view/home.view.html',
            controller: 'HomeCtrl',
            resolve: {
                factory: function ($q, $rootScope, $location, $facebook, $timeout, $mobile) {
                    var d = $q.defer();
                    if ($mobile.exist) {
                        window.document.addEventListener('deviceready', function () {
                            $rootScope.connect = 'good';
                            d.resolve(true);
                        }, false);
                    } else {
                        var start = function () {
                            d.reject();
                            $location.path('/map');
                        };
                        $facebook.getLoginStatus().then(function (e) {
                            $rootScope.connect = 'good';
                            if (e.status === 'connected') { start(); }
                            else { d.resolve(true); }
                        }, function () {
                            d.resolve(true);
                        });
                        $timeout(function () {
                            if (!window.FB) {
                                $rootScope.connect = 'fail';
                                d.resolve(true);
                            }
                        }, 5000);
                    }
                    return d.promise;
                }
            }
        });
    })

    .controller('HomeCtrl', function ($rootScope, $scope, $window, $location,$http, $facebook, $mobile, $cordovaInAppBrowser) {
        $rootScope.access = 'no access!';
        $scope.start = function () {
            if ($mobile.exist) {
                var login_accessToken;
                var accessToken;
                console.log('connect');
                var client_id = FB_APP_ID; //your App ID or API Key
                var client_secret = FB_APP_SECRET; //// your App Secret
                var redirect_uri = FB_RE_URL;  //// YOUR CALLBACK URL
                var display = 'touch';
                var authorize_url = 'https://graph.facebook.com/v2.0/oauth/authorize?';
                authorize_url += 'client_id=' + client_id;
                authorize_url += '&redirect_uri=' + redirect_uri;
                authorize_url += '&display=' + display;
                authorize_url += '&scope=public_profile,email';

                var options = {
                    location: 'yes',
                    clearcache: 'no',
                    toolbar: 'yes'
                };
                FB.getLoginStatus(function(response) {
                    if (response.status === 'connected') {
                        console.log( 'Log yes');


                    }
                    else {
                        console.log('Logged no.');
                        FB.login();
                    }
                }, true);
                $rootScope.$on('$cordovaInAppBrowser:loadstart', function(e, event){
                    console.log('loadstart ' + event.url);
                    if(event.url.indexOf(redirect_uri + '?') >= 0){
                        // $cordovaInAppBrowser.close();

                        // $rootScope.$broadcast("fb.auth.authResponseChange", {status:'connected'}, FB);

                        // var result = event.url.split('#')[0];
                        // login_accessToken = result.split('&')[0].split('=')[1];
                        // console.log('login_accessToken ' + login_accessToken);

                        // var url = 'https://graph.facebook.com/v2.9/oauth/access_token?';
                        // url += 'client_id=' + client_id;
                        // url += '&client_secret=' + client_secret;
                        // url += '&code=' + login_accessToken;
                        // url += '&redirect_uri=' + redirect_uri;

                        // $facebook.api('/oauth/access_token', {
                        //     client_id: client_id,
                        //     client_secret: client_secret,
                        //     code: login_accessToken,
                        //     redirect_uri: redirect_uri
                        // }).then(function (r) {
                        //     console.log(angular.toJson(r));
                        //     $rootScope.access = r.data.access_token;
                        //
                        // }, function (e) {
                        //     $rootScope.access = 'error '+ JSON.stringify(e);
                        //
                        // });
                        // $http.post(url, null).then(function (r) {
                        //     // accessToken = data.split('&')[0].split('=')[1];
                        //     console.log(angular.toJson(r.data));
                        //     $rootScope.access = r.data.access_token;
                        //     FB.api('/me', {
                        //         fields: 'name,id,picture{url},cover,first_name',
                        //         access_token: r.data.access_token
                        //     }, function (userRes) {
                        //         console.log('Your info is ready' + userRes);
                        //
                        //     });
                        // }, function (e) {
                        //     $rootScope.access = 'error';
                        //
                        // });

                    }
                });
                $cordovaInAppBrowser.open(authorize_url, '_blank', options)
                    .then(function(event) {
                        console.log('open ok');

                    });
            } else {
                $facebook.login().then(function (e) {
                    $location.path('/map');
                });
            }
        };
    });