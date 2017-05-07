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

    .controller('MapCtrl', function ($rootScope, $scope, $mdSidenav, $mdMedia, $mdToast, $q, $facebook, $timeout, $interval, uiGmapIsReady, user, environment) {
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
                    if (i.id !== marker.id) {
                        i.popup.show = false;
                    }
                });
                // $scope.map.center.latitude = marker.coords.latitude;
                $scope.map.center.longitude = marker.coords.longitude;
                if (!marker.distance && !marker.me) {
                    direction.service.route({
                        origin: new google.maps.LatLng($rootScope.me.coords.latitude, $rootScope.me.coords.longitude),
                        destination: new google.maps.LatLng(marker.coords.latitude, marker.coords.longitude),
                        travelMode: 'DRIVING'
                    }, function (response, status) {
                        if (status === 'OK') {
                            marker.direction = response;
                            marker.distance = response.routes[0].legs[0].distance.value;
                        } else {
                            marker.distance = 0;
                        }
                    });
                }
                $rootScope.direct.current = marker;
                marker.popup.show = true;

                return false;
            },
            close: function (popup) {
                popup.show = false;
            }
        };
        $rootScope.direct = {
            current: null,
            toMe: function () {
                direction.render.setDirections($rootScope.direct.current.direction);
                $rootScope.direct.current.popup.show = false;
            }
        };
        $rootScope.progress = {
            message: '',
            current: 0,
            map: false,
            fb: false,
            data: false,
            all: false
        };

        $scope.submenu = {
            active: 0,
            toggle: function () {
                $mdSidenav('left').toggle().then(function () {
                });
            }
        };
        // var ii = 0;
        // $interval(function () {
        //     $mdToast.show({
        //         hideDelay   : 5000,
        //         position    : 'top right',
        //         controller  : 'ToastCtrl',
        //         templateUrl: 'view/toast.template.html',
        //         toastClass: 'notifier',
        //         locals: {data: $rootScope.friends[ii]}
        //     });
        //     ii = ii >= $rootScope.friends.length - 1 ? 0 : ii + 1;
        // }, 5000);
        var direction = {
            service: null,
            render: null
        };
        var map = function () {
            var d = $q.defer();
            uiGmapIsReady.promise(1).then(function (instances) {
                $rootScope.progress.message = 'Google Map ready';
                $rootScope.progress.current += 20;
                direction.service = new google.maps.DirectionsService;
                direction.render = new google.maps.DirectionsRenderer({suppressMarkers: true});
                direction.render.setMap(instances[0].map);
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
                    $rootScope.progress.message = 'Your location is detected';
                    $rootScope.progress.current += 10;
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
            $facebook.api('/me', {fields: 'name,id,picture{url},cover,first_name'}).then(
                function (userRes) {
                    $rootScope.progress.message = 'Your info is ready';
                    $rootScope.progress.current += 20;
                    $facebook.api(userRes.id + '/friends', {fields: 'name,id,picture{url},cover,first_name'}).then(
                        function (response) {
                            $rootScope.progress.fb = true;
                            $rootScope.progress.message = 'Your friends is ready';
                            $rootScope.progress.current += 10;
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
                $rootScope.progress.current += 10;
                if (e.status === 'connected') {
                    me().then(function (r) {
                        d.resolve(r)
                    });
                } else {
                    $rootScope.progress.message = 'Please log in...';
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
        $rootScope.progress.message = 'Init map...';
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
            $rootScope.progress.message = 'Get your friends list';

            $q.all(checkAll).then(function (all) {
                all.forEach(function (i, index) {
                    if (i.data) {
                        $rootScope.friends[index].date = parseInt(i.data.date);
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
                $rootScope.progress.current += 10;
                $rootScope.progress.message = 'Get your info';
                user.check(fbInfo.me.id).then(function (r) {
                    var nowUTC = new Date(new Date().toISOString()).getTime();
                    if (r.data) {//Update
                        user.update(fbInfo.me.id, fbInfo.me.name, position.latitude.toString(), position.longitude.toString(), 1, nowUTC);
                    } else {//Insert
                        user.create(fbInfo.me.id, fbInfo.me.name, position.latitude.toString(), position.longitude.toString(), 1, nowUTC);
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
                        me: true,
                        popup: {options: {visible: false}}
                    });
                    $rootScope.me = fbInfo.me;
                    $rootScope.me.coords = {latitude: position.latitude, longitude: position.longitude};
                    $rootScope.progress.current += 19;
                    $rootScope.progress.message = 'Go to your map now...';
                    $timeout(function () {
                        $rootScope.progress.current += 1;
                    }, 2000);
                });
            });
            $rootScope.progress.all = true;
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
    })
    .controller('ToastCtrl', function($scope, $mdToast, data) {
           $scope.data = data;
           $scope.event = {
               direct: function () {

               }
           };
    });