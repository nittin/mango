'use strict';

angular.module('myApp.map', ['ngRoute'])

    .config(function ($routeProvider) {
        $routeProvider.when('/map', {
            templateUrl: 'view/map.view.html',
            controller: 'MapCtrl',
            resolve: {
                factory: function ($q, $rootScope, $location, $localStorage, user) {
                    var d = $q.defer();
                    var goHome = function () {
                        d.reject();
                        $location.path('/');
                    };
                    console.log('hello '+$localStorage.get(STORAGE_LOGIN));
                    if ($localStorage.get(STORAGE_LOGIN)) {
                        user.fb('/me?fields=id,name').then(function (res) {
                            console.log('its me ');
                            d.resolve(true);
                        }, function (e) {
                            // localStorage.clear();
                            console.log('its should back ' + angular.toJson(e));
                            // goHome(e);
                        });
                    }
                    else { goHome(); }

                    return d.promise;
                }
            }
        });
    })

    .controller('MapCtrl', function ($rootScope, $scope, $localStorage, $mdSidenav, $mdMedia, $mdToast, $q, $timeout, $interval, uiGmapIsReady, user, environment) {
        $scope.user = {id: undefined, name: undefined, center: {latitude: 45, longitude: 45}};
        $scope.map = {
            center: {latitude: $scope.user.center.latitude, longitude: $scope.user.center.longitude},
            bounds: {},
            zoom: 15
        };
        $scope.marker = {
            list: [],
            size: {w: 40, h: 58},
            options: {scrollwheel: false},
            open: function (marker) {
                $scope.marker.list.forEach(function (i, index) {
                    if (i.id !== marker.id) {
                        i.popup.show = false;
                    } else {
                        $scope.carousel.active = index;
                    }
                });
                $scope.map.center.longitude = marker.coords.longitude;
                if ($scope.inSmallScreen) {
                    $scope.map.center.latitude = marker.coords.latitude;
                }
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
                $scope.carousel.active = -1;
            },
            event: {
                click:function (m,e,agr) {
                    var a=e;
                }
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
        $scope.carousel = {
            active: -1
        };
        $scope.$watch('carousel.active', function (i) {
            if (i > -1) {
                $scope.marker.open($scope.marker.list[i]);
            }
        });
        $scope.$watch(function() { return $mdMedia('xs') }, function(isSmall) {
            $scope.inSmallScreen = isSmall;
        });
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
            user.fb('/me?fields=name,id,picture{url},cover,first_name').then(
                function (res) {
                    var meData = res.data;
                    $rootScope.progress.message = 'Your info is ready';
                    $rootScope.progress.current += 20;
                    user.fb(meData.id + '/friends?fields=name,id,picture{url},cover,first_name').then(
                        function (response) {
                            $rootScope.progress.fb = true;
                            $rootScope.progress.message = 'Your friends is ready';
                            $rootScope.progress.current += 10;
                            d.resolve({me: meData, friends: response.data.data});
                        });
                },
                function (e) {
                    d.reject(e);
                });
            return d.promise;
        };
        var fb = function () {
            var d = $q.defer();
            if ($localStorage.get(STORAGE_LOGIN)) {
                $rootScope.progress.current += 10;
                me().then(function (r) {
                    d.resolve(r)
                });
            } else {
                $rootScope.progress.message = 'Please log in...';
                d.reject();
                $location.path('/');
            }

            return d.promise;
        };
        $rootScope.progress.message = 'Init map...';
        $q.all([map(), center(), fb()]).then(function (thread) {
            var position = thread[1];
            var fbInfo = thread[2];

            $scope.map.center.latitude = position.latitude;
            $scope.map.center.longitude = position.longitude;
            $rootScope.friends = fbInfo.friends;
            var bounds = new google.maps.LatLngBounds();

            $rootScope.progress.message = 'Start set your friends list';
            var friendChain = fbInfo.friends.map(function (i) { return i.id }).join(',');
            user.check(friendChain).then(function (r) {
                var all = r.data.users;
                $rootScope.friends.forEach(function (i, index) {
                    var target = all.filter(function (j) { return i.id === j.id; })[0];
                    if (target) {
                        i.date = parseInt(target.date, 10);
                        i.device = parseInt(target.device, 10);
                        i.lat = parseFloat(target.lat);
                        i.lng = parseFloat(target.lng);
                        $scope.marker.list.push({
                            coords: {latitude: i.lat, longitude: i.lng},
                            show: false,
                            date: i.date,
                            name: i.name,
                            id: i.id,
                            fb: i,
                            options: {
                                icon: {
                                    url: environment.cropPhoto + encodeURIComponent(i.picture.data.url),
                                    scaledSize: {
                                        width: $scope.marker.size.w, height: $scope.marker.size.h
                                    }
                                }
                            },
                            popup: {options: {visible: true}}
                        });
                        bounds.extend(new google.maps.LatLng(i.lat, i.lng));
                    }
                });
                bounds.extend(new google.maps.LatLng(position.latitude, position.longitude));
                $scope.map.bounds = {
                    northeast: {latitude: bounds.getNorthEast().lat(), longitude: bounds.getNorthEast().lng()},
                    southwest: {latitude: bounds.getSouthWest().lat(), longitude: bounds.getSouthWest().lng()}
                };
                $rootScope.progress.current += 10;
                $rootScope.progress.message = 'Get your friends info done';
            });
            user.check(fbInfo.me.id).then(function (r) {
                var nowUTC = new Date(new Date().toISOString()).getTime();
                if (r.data.users[0]) {//Update
                    user.update(fbInfo.me.id, fbInfo.me.name, position.latitude.toString(), position.longitude.toString(), 1, nowUTC, friendChain);
                } else {//Insert
                    user.create(fbInfo.me.id, fbInfo.me.name, position.latitude.toString(), position.longitude.toString(), 1, nowUTC, friendChain);
                }
                $scope.marker.list.push({
                    coords: {latitude: position.latitude, longitude: position.longitude},
                    show: false,
                    name: fbInfo.me.name,
                    id: fbInfo.me.id,
                    fb: fbInfo.me,
                    options: {
                        icon: {
                            url: environment.cropPhoto + encodeURIComponent(fbInfo.me.picture.data.url),
                            scaledSize: {
                                width: $scope.marker.size.w, height: $scope.marker.size.h
                            }
                        }
                    },
                    me: true,
                    popup: {options: {visible: true}}
                });
                $rootScope.me = fbInfo.me;
                $rootScope.me.coords = {latitude: position.latitude, longitude: position.longitude};
                $rootScope.progress.current += 19;
                $rootScope.progress.message = 'Go to your map now...';
                $timeout(function () {
                    $rootScope.progress.current += 1;
                    startSubscribe();
                }, 2000);
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

        Pusher.logToConsole = DEBUG;
        var startSubscribe = function () {
            var pusher = new Pusher(PUSHER.key, {
                cluster: PUSHER.cluster,
                encrypted: true
            });
            var channelId = $rootScope.me.id;
            var userChannel = pusher.subscribe($rootScope.me.id);
            var worldChannel = pusher.subscribe('world-channel');
            userChannel.bind('user-online', function(data) {
                var friend = $rootScope.friends.filter(function (i) {
                    return i.id === data.id && $rootScope.me.id !== data.id;
                })[0];
                if (friend) {
                    friend.date = data.date;
                    $mdToast.show({
                        hideDelay: 5000,
                        position: 'top right',
                        controller: 'ToastCtrl',
                        templateUrl: 'view/toast.template.html',
                        toastClass: 'notifier',
                        locals: {data: friend}
                    });
                }
            });
            worldChannel.bind('system', function(data) {
                switch (data.type){
                    case 0:
                        $mdToast.show(
                            $mdToast.simple()
                                .textContent(data.message)
                                .position('bottom right')
                                .hideDelay(3000)
                        ).then(function () {
                            window.location.reload();
                        });
                        break;
                    default:break;
                }
            });
        };
    })
    .controller('ToastCtrl', function($scope, $mdToast, data) {
           $scope.data = data;
           $scope.event = {
               direct: function () {

               }
           };
    });