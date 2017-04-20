'use strict';

angular.module('myApp.view1', ['ngRoute'])

    .config(function ($routeProvider) {
        $routeProvider.when('/view1', {
            templateUrl: 'view/view1.html',
            controller: 'View1Ctrl'
        });
    })

    .controller('View1Ctrl', function ($rootScope, $scope, $q, $facebook, uiGmapIsReady, user) {
        $scope.user = {id: undefined, name: undefined, center: {latitude: 45, longitude: 45}};
        $scope.map = {
            center: {latitude: $scope.user.center.latitude, longitude: $scope.user.center.longitude},
            zoom: 15
        };
        $scope.marker = {
            list: [],
            options: {scrollwheel: false},
            open: function (popup) {
                $scope.marker.list.forEach(function (i) {
                    i.popup.options.visible = false;
                });
                popup.options.visible = true;
            },
            close: function (popup) {
                popup.options.visible = false;
            }
        };

        uiGmapIsReady.promise(1).then(function (instances) {
            instances.forEach(function (inst) {
                var map = inst.map;
            });

        });
        var center = function () {
            var d = $q.defer();
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(function (position) {
                    d.resolve(position.coords)
                }, function () {
                });
            } else {
                d.reject()
            }
            return d.promise;
        };
        var me = function () {
            var d = $q.defer();

            $facebook.api('/me').then(
                function (response) {
                    $scope.user = response;
                    $rootScope.welcomeMsg = 'Welcome ' + $scope.user.name;

                    $facebook.api($scope.user.id + '/friends', {fields: 'name,id,picture{url}'}).then(
                        function (response) {
                            $rootScope.friends = response.data;
                            center().then(function (position) {
                                $scope.map.center.latitude = position.latitude;
                                $scope.map.center.longitude = position.longitude;

                                $rootScope.friends.forEach(function (i) {
                                    $scope.marker.list.push({
                                        coords: {latitude: position.latitude, longitude: position.longitude},
                                        show: true,
                                        name: i.name,
                                        id: i.id,
                                        options: {
                                            icon: i.picture.data.url
                                        },
                                        popup: {options: {visible: false}}
                                    })
                                })
                            });
                        });
                },
                function (err) {
                });
            return d.promise;
        };
        user.getAll().then(function (r) {
            var a = r.data;
        });
        $facebook.getLoginStatus().then(function (e) {
            if (e.status === 'connected') {
                me();
            } else {
                $rootScope.welcomeMsg = 'Please log in';
                $facebook.login().then(function (e) {
                    console.log(e);
                    me();
                })
            }
        })
    });