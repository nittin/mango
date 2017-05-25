'use strict';

angular.module('myApp.home', ['ngRoute'])

    .config(function ($routeProvider) {
        $routeProvider.when('/', {
            templateUrl: 'view/home.view.html',
            controller: 'HomeCtrl',
            resolve: {
                factory: function ($q, $rootScope, $location, $localStorage, user) {
                    var d = $q.defer();
                    var goMap = function () {
                        d.reject();
                        $location.path('/map');
                        // window.location.href = window.location.href.split("?")[0] + 'map';//clear all request params
                    };
                    var reg = new RegExp("[?&]code(=([^&#]*)|&|#|$)").exec(window.location.href);
                    var code = reg ? reg[2] : null;
                    if (code) {
                        window.opener.postMessage(code, '*');
                        
                    } else if ($localStorage.get(STORAGE_LOGIN)) {
                        goMap();
                    } else {
                        $rootScope.connect = 'good';
                        d.resolve(true);
                    }
                    return d.promise;
                }
            }
        });
    })

    .controller('HomeCtrl', function ($rootScope, $scope, $window, $location, $localStorage, $http, $mobile, $cordovaInAppBrowser, user) {
        var auth = function (code) {
            user.auth(code).then(function (res) {
                $localStorage.set(STORAGE_ID, res.data.id);
                $localStorage.set(STORAGE_TOKEN, res.data.token);
                $localStorage.set(STORAGE_LOGIN, true);
                $location.path('/map');
            });
        };
        $scope.start = function () {
            var client_id = FB_APP_ID; //your App ID or API Key
            var redirect_uri = FB_RE_URL;  //// YOUR CALLBACK URL
            var display = 'touch';
            var authorize_url = 'https://graph.facebook.com/v2.0/oauth/authorize?';
            authorize_url += 'client_id=' + client_id;
            authorize_url += '&redirect_uri=' + encodeURIComponent(redirect_uri).replace(/'/g,"%27").replace(/"/g,"%22");
            authorize_url += '&display=' + display;
            authorize_url += '&scope=public_profile,email';
            var authWindow = window.open(authorize_url, '_blank', 'location=yes,clearcache=yes,toolbar=yes');
            if (authWindow.addEventListener) {
                console.log('listen loadstart');
                authWindow.addEventListener('loadstart', function (event) {
                    if (event.url.indexOf('?code=') >= 0) {
                        console.log('load pass: ' + event.url);
                        authWindow.close();
                        var code = event.url.split('?')[1].split('#')[0];
                        $rootScope.access = code;
                        // window.location.href=a;
                        auth(code);
                    }
                });
            } else {
                console.log('listen message');

                window.addEventListener('message', function (e) {
                    console.log(e.data);
                    auth(e.data);
                    authWindow.close();
                }, false);
            }
        };

    });