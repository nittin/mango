'use strict';

angular.module('myApp.back.user-service', [])

    .service('user', function (environment, $http, $q, device) {
        this.getAll = function () {
            return $http.get(environment.user);
        };
        this.check = function (id) {
            return $http.get(environment.userDetail + id);
        };
        this.create = function (id, name, lat, lng, status, date, friends) {
            return $http.post(environment.user, {id: id, name: name, lat: lat, lng: lng, status: status, date: date, device:device.check(), friends: friends});
        };
        this.update = function (id, name, lat, lng, status, date, friends) {
            return $http.put(environment.user, {id: id, name: name, lat: lat, lng: lng, status: status, date: date, device:device.check(), friends: friends});
        };
        return this;
    });
