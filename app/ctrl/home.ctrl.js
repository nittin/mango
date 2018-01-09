'use strict';

angular.module('myApp.home')

    .config(function ($routeProvider) {
        $routeProvider.when('/', {
            templateUrl: 'view/home.view.html',
            controller: 'HomeCtrl',
            controllerAs: '$ctrl',
            resolve: {
                factory: function ($q, $rootScope, $location, $localStorage, $sessionStorage, user, $mobile) {
                    var d = $q.defer();

                    var reg = new RegExp("[?&]code(=([^&#]*)|&|#|$)").exec(window.location.href);
                    var code = reg ? reg[2] : null;
                    if (code) {
                        window.opener.postMessage(code, '*');
                        $sessionStorage.set(SESSION_LOGIN_CODE, code);
                        window.location.href = FB_RE_URL;//refresh url
                        // reg = new RegExp("[?&]state(=([^&#]*)|&|#|$)").exec(window.location.href);
                        // var state = reg ? reg[2] : null;
                        // if (!state) {
                            // auth(code);
                        // }

                    } else if ($localStorage.get(STORAGE_LOGIN)) {
                        d.reject();
                        $location.path('/map');
                    } else {
                        d.resolve(true);
                    }
                    return d.promise;
                }
            }
        });
    })

    .controller('HomeCtrl', function ($rootScope, $scope, $window, $location, $localStorage, $http, $mobile, $sessionStorage, user) {
        this.me = null;
        this.friends = null;
        this.ok = false;
        this.login = function () {
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
                authWindow.addEventListener('loadstart', function (event) {
                    if (event.url.indexOf('?code=') >= 0) {
                        authWindow.close();
                    }
                });
            }
        };
        var code = $sessionStorage.get(SESSION_LOGIN_CODE);
        if (code) {
            this.auth = true;
            var self = this;
            $sessionStorage.clear();
            user.auth(code).then(function (res) {
                $localStorage.set(STORAGE_ID, res.data.id);
                $localStorage.set(STORAGE_TOKEN, res.data.token);
                $localStorage.set(STORAGE_LOGIN, true);
                $rootScope.isAuth = true;
                self.me = res.data;
                user.friends().then(function (res) {
                    self.ok = true;
                    self.friends = res.data;
                });
            }, function () {
                $location.path('/').replace();
            });
        }
        else {
            this.auth = false;
        }
    });