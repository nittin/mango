'use strict';

angular.module('myApp.home', ['ngRoute'])

    .config(function ($routeProvider) {
        $routeProvider.when('/', {
            templateUrl: 'view/home.view.html',
            controller: 'HomeCtrl',
            resolve: {
                factory: function ($q, $rootScope, $location, $facebook, $timeout) {
                    var d = $q.defer();
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
                    return d.promise;
                }
            }
        });
    })

    .controller('HomeCtrl', function ($rootScope, $scope, $location, $facebook) {
        $scope.start = function () {
            $facebook.login().then(function (e) {
                $location.path('/map');
            });
        };
    });