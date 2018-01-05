'use strict';

angular.module('myApp.home')
    .component('homeAuth', {
        templateUrl: 'view/home.auth.view.html',
        controller: 'HomeAuthCtrl',
        bindings: {}
    })
    .controller('HomeAuthCtrl', function ($rootScope, $scope, $localStorage, $sessionStorage, $mobile, $element, $location, user) {
        $scope.mode = 'NOT_AUTH';
        var code = $sessionStorage.get(SESSION_LOGIN_CODE);

        var auth = function (code) {
            user.auth(code).then(function (res) {
                $localStorage.set(STORAGE_ID, res.data.id);
                $localStorage.set(STORAGE_TOKEN, res.data.token);
                $localStorage.set(STORAGE_LOGIN, true);
                $location.path('/map');
            });
        };
        this.$onInit = function () {
            if (code) {
                $scope.mode = 'AUTHENTICATING';
                var clientId = FB_APP_ID; //your App ID or API Key
                var redirect = FB_RE_URL;  //// YOUR CALLBACK URL
                var display = 'touch';
                var authorizeUrl = 'https://graph.facebook.com/v2.0/oauth/authorize?';
                authorizeUrl += 'client_id=' + clientId;
                authorizeUrl += '&redirect_uri=' + encodeURIComponent(redirect).replace(/'/g, "%27").replace(/"/g, "%22");
                authorizeUrl += '&display=' + display;
                authorizeUrl += '&scope=public_profile,email';
                var target = $mobile.exist ? '_blank' : '_self';
                var authWindow = window.open(authorizeUrl, target, 'location=yes,clearcache=no,toolbar=yes');
                if (authWindow.addEventListener) {
                    console.log('listen loadstart');
                    authWindow.addEventListener('loadstart', function (event) {
                        if (event.url.indexOf('?code=') >= 0) {
                            console.log('load pass: ' + event.url);
                            authWindow.close();
                            var code = event.url.split('?code=')[1].split('&')[0];
                            $rootScope.access = event.url;
                            // window.location.href=a;
                            auth(code);
                        }
                    });
                }
            }
            else {
            }
        };

        /*var authWindow = window.open(authorize_url, 'auth-frame', 'location=yes,clearcache=no,toolbar=yes');
        if (authWindow.addEventListener) {
            console.log('listen loadstart');
            authWindow.addEventListener('loadstart', function (event) {
                if (event.url.indexOf('?code=') >= 0) {
                    console.log('load pass: ' + event.url);
                    authWindow.close();
                    var code = event.url.split('?code=')[1].split('&')[0];
                    auth(code);
                }
            });
        }
        */

    });