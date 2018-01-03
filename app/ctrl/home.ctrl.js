'use strict';

angular.module('myApp.home')

    .config(function ($routeProvider) {
        $routeProvider.when('/', {
            templateUrl: 'view/home.view.html',
            controller: 'HomeCtrl',
            controllerAs: '$ctrl',
            resolve: {
                factory: function ($q, $rootScope, $location, $localStorage, user, $mobile) {
                    var auth = function (code) {
                        user.auth(code).then(function (res) {
                            if (res.data && res.data.id) {
                                $localStorage.set(STORAGE_ID, res.data.id);
                                $localStorage.set(STORAGE_TOKEN, res.data.token);
                                $localStorage.set(STORAGE_LOGIN, true);
                            }
                        }).finally(function () {
                            if (!$mobile.exist) {
                                window.location.href = FB_RE_URL + 'map';
                            }
                        });
                    };

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
                        reg = new RegExp("[?&]state(=([^&#]*)|&|#|$)").exec(window.location.href);
                        var state = reg ? reg[2] : null;
                        if (!state) {
                            auth(code);
                        }
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
        this.loginMode = false;
    });