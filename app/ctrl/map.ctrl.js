'use strict';

angular.module('myApp.map', ['ngRoute'])

    .config(function ($routeProvider) {
        $routeProvider.when('/map', {
            templateUrl: 'view/map.view.html',
            controller: 'MapCtrl',
            resolve: {
                factory: function ($q, $rootScope, $location, $facebook) {
                    var d = $q.defer();
                    var goHome = function () {
                        d.reject();
                        $location.path('/');
                    };
                    $facebook.getLoginStatus().then(function (e) {
                        if (e.status === 'connected') { d.resolve(true); }
                        else { goHome(); }
                    }, goHome);
                    return d.promise;
                }
            }
        });
    })

    .controller('MapCtrl', function ($rootScope, $scope,$mdSidenav,$mdMedia, $q, $facebook, uiGmapIsReady, user, environment) {
        $scope.user = {id: undefined, name: undefined, center: {latitude: 45, longitude: 45}};
        $scope.map = {
            center: {latitude: $scope.user.center.latitude, longitude: $scope.user.center.longitude},
            bounds: {},
            zoom: 15
        };
        $scope.marker = {
            list: [],
            options: {scrollwheel: false},
            open: function (marker) {
                $scope.marker.list.forEach(function (i) {
                    i.popup.options.visible = false;
                });
                marker.popup.options.visible = true;
                // $scope.map.center.latitude = marker.coords.latitude;
                $scope.map.center.longitude = marker.coords.longitude;
            },
            close: function (popup) {
                popup.options.visible = false;
            }
        };

        $scope.submenu = {
            active: 0
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
                    d.reject();
                });
            } else {
                d.reject();
            }
            return d.promise;
        };
        var me = function () {
            var d = $q.defer();

            $facebook.api('/me', {fields: 'name,id,picture{url},cover'}).then(
                function (userRes) {

                    $facebook.api(userRes.id + '/friends', {fields: 'name,id,picture{url},cover'}).then(
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
                    });
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
            var checkAll = fbInfo.friends.map(function (i) {
                var d = $q.defer();
                user.check(i.id).then(function (userChecked) {
                    d.resolve({fbInfo: i, data: userChecked.data})
                });
                return d.promise;
            });
            $q.all(checkAll).then(function (all) {
                all.forEach(function (i, index) {
                    if (i.data) {
                        i.data.lat = parseFloat(i.data.lat);
                        i.data.lng = parseFloat(i.data.lng);
                        $scope.marker.list.push({
                            coords: {latitude: i.data.lat, longitude: i.data.lng},
                            show: true,
                            name: i.fbInfo.name,
                            id: i.fbInfo.id,
                            fb: i.fbInfo,
                            options: {
                                icon: {
                                    url: environment.cropPhoto + encodeURIComponent(i.fbInfo.picture.data.url),
                                    scaledSize: {
                                        width:30,height:40
                                    }
                                }
                            },
                            popup: {options: {visible: false}}
                        });
                        bounds.extend(new google.maps.LatLng(i.data.lat, i.data.lng));
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
                    fb: fbInfo.me,
                    options: {
                        icon: {
                            url: environment.cropPhoto + encodeURIComponent(fbInfo.me.picture.data.url),
                            scaledSize: {
                                width: 30, height: 40
                            }
                        }
                    },
                    popup: {options: {visible: false}}
                });
                $rootScope.me = fbInfo.me;
            });
        });

        var debounce = function (func, wait, context) {
            var timer;
            return function debounced() {
                var context = $scope,
                    args = Array.prototype.slice.call(arguments);
                $timeout.cancel(timer);
                timer = $timeout(function () {
                    timer = undefined;
                    func.apply(context, args);
                }, wait || 10);
            };
        };
        var buildDelayedToggler = function (navID) {
            return debounce(function() {
                // Component lookup should always be available since we are not using `ng-if`
                $mdSidenav(navID)
                    .toggle()
                    .then(function () {
                        $log.debug("toggle " + navID + " is done");
                    });
            }, 200);
        };
        $scope.toggleLeft = buildDelayedToggler('left');
        $scope.openId = function (id) {
            var marker = $scope.marker.list.filter(function (i) {
                return i.id === id;
            })[0];
            if (marker) {
                $scope.map.center.latitude = marker.coords.latitude;
                $scope.map.center.longitude = marker.coords.longitude;
                $scope.marker.open(marker);
            }
        };
    });