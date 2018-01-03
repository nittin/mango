'use strict';

angular.module('myApp.home')
    .component('homeAuth',{
        templateUrl: 'view/home.auth.view.html',
        controller: 'HomeAuthCtrl',
        bindings: {
        }
    })
    .controller('HomeAuthCtrl', function ($rootScope, $scope, $localStorage, $sce, $element, $location, user) {
        $scope.url = '';
        $scope.trustSrc = function(src) {
            return $sce.trustAsResourceUrl(src);
        };
        var auth = function (code) {
            user.auth(code).then(function (res) {
                $localStorage.set(STORAGE_ID, res.data.id);
                $localStorage.set(STORAGE_TOKEN, res.data.token);
                $localStorage.set(STORAGE_LOGIN, true);
                $location.path('/map');
            });
        };
        $element.children('#auth-frame').on('load', function (e) {
            console.log(this.contentWindow.location.href);
            if (this.contentWindow.location.href.indexOf('?code=') >= 0) {
                console.log('load pass: ' + event.url);
                var code = this.contentWindow.location.href.split('?code=')[1].split('&')[0];
                auth(code);
            }
        });
        this.$onInit = function () {
            var client_id = FB_APP_ID; //your App ID or API Key
            var redirect_uri = FB_RE_URL;  //// YOUR CALLBACK URL
            var display = 'touch';
            var authorize_url = 'https://graph.facebook.com/v2.0/oauth/authorize?';
            authorize_url += 'client_id=' + client_id;
            authorize_url += '&redirect_uri=' + encodeURIComponent(redirect_uri).replace(/'/g,"%27").replace(/"/g,"%22");
            authorize_url += '&display=' + display;
            authorize_url += '&scope=public_profile,email';
            authorize_url += '&state=remote';
            $scope.url = authorize_url;
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