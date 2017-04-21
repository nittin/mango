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
            bounds: {},
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
        var map = function () {
            var d = $q.defer();
            uiGmapIsReady.promise(1).then(function (instances) {
                d.resolve(instances);
            }, function (e) {
                d.reject(e)
            });
            return d.promise;
        };
        var center = function () {
            var d = $q.defer();
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(function (position) {
                    d.resolve(position.coords);
                }, function () {
                });
            } else {
                d.reject();
            }
            return d.promise;
        };
        var me = function () {
            var d = $q.defer();

            $facebook.api('/me', {fields: 'name,id,picture{url}'}).then(
                function (userRes) {

                    $facebook.api(userRes.id + '/friends', {fields: 'name,id,picture{url}'}).then(
                        function (response) {
                            d.resolve({me: userRes, friends: response.data});
                        });
                },
                function (e) {
                    d.reject(e);
                });
            return d.promise;
        };
        var fb = function () {
            var d = $q.defer();

            $facebook.getLoginStatus().then(function (e) {
                if (e.status === 'connected') {
                    me().then(function (r) {
                        d.resolve(r)
                    });
                } else {
                    $rootScope.welcomeMsg = 'Please log in';
                    $facebook.login().then(function (e) {
                        // console.log(e);
                        me().then(function (r) {
                            d.resolve(r)
                        });
                    })
                }
            });

            return d.promise;
        };

        $q.all([map(), center(), fb()]).then(function (r) {
            var position = r[1];
            var fbInfo = r[2];

            $scope.map.center.latitude = position.latitude;
            $scope.map.center.longitude = position.longitude;
            $rootScope.friends = fbInfo.friends;
            var bounds = new google.maps.LatLngBounds();
            var checkAll = $rootScope.friends.map(function (i) {
                var d = $q.defer();
                user.check(i.id).then(function (userChecked) {
                    d.resolve({fbInfo: i, data: userChecked.data})
                });
                return d.promise;
            });
            $q.all(checkAll).then(function (all) {
                all.forEach(function (i) {
                    if (i.data) {
                        var lat = parseFloat(i.data.lat);
                        var lng = parseFloat(i.data.lng);
                        $scope.marker.list.push({
                            coords: {latitude: lat, longitude: lng},
                            show: true,
                            name: i.fbInfo.name,
                            id: i.fbInfo.id,
                            options: {
                                icon: i.fbInfo.picture.data.url
                            },
                            popup: {options: {visible: false}}
                        });
                        bounds.extend(new google.maps.LatLng(lat, lng));
                    }
                });
                bounds.extend(new google.maps.LatLng(position.latitude, position.longitude));
                $scope.map.bounds = {
                    northeast: {latitude: bounds.getNorthEast().lat(), longitude: bounds.getNorthEast().lng()},
                    southwest: {latitude: bounds.getSouthWest().lat(), longitude: bounds.getSouthWest().lng()}
                };
            });

            user.check(fbInfo.me.id).then(function (r) {
                var nowUTC = new Date(new Date().toISOString()).getTime();
                if (r.data) {//Update
                    user.update(fbInfo.me.id, fbInfo.me.name, position.latitude, position.longitude, 1, nowUTC);
                } else {//Insert
                    user.create(fbInfo.me.id, fbInfo.me.name, position.latitude, position.longitude, 1, nowUTC);
                }
                $scope.marker.list.push({
                    coords: {latitude: position.latitude, longitude: position.longitude},
                    show: true,
                    name: fbInfo.me.name,
                    id: fbInfo.me.id,
                    options: {
                        icon: fbInfo.me.picture.data.url
                    },
                    popup: {options: {visible: false}}
                })
            });
        });

    });