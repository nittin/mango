'use strict';

angular.module('myApp.home', ['ngRoute'])

    .config(function ($routeProvider) {
        $routeProvider.when('/', {
            templateUrl: 'view/home.view.html',
            controller: 'HomeCtrl',
            resolve: {
                factory: function ($q, $rootScope, $location, $facebook) {
                    var d = $q.defer();
                    var start = function () {
                        d.reject();
                        $location.path('/view1');
                    };
                    $facebook.getLoginStatus().then(function (e) {
                        if (e.status === 'connected') { start(); }
                        else { d.resolve(true); }
                    }, function () {
                        d.resolve(true);
                    });
                    return d.promise;
                }
            }
        });
    })

    .controller('HomeCtrl', function ($rootScope, $scope, $location, $facebook) {
        $scope.start = function () {
            $facebook.login().then(function (e) {
                $location.path('/view1');
            });
        };
    });