'use strict';

angular.module('myApp.back.user-service', [])

    .service('user', function (environment, $http, $q, $localStorage, device) {
        this.current = {};
        this.auth = function (code) {
            return $http.post(environment.oauth, {code: code, env: environment.key});
        };
        this.fb = function (api) {
            var token = $localStorage.get(STORAGE_TOKEN);
            return $http.post(environment.fb, {api: api, token: token, env: environment.key});
        };
        this.valid = function () {
            return $http.get(environment.valid);
        };
        this.getAll = function () {
            return $http.get(environment.user);
        };
        this.getNotifications = function () {
            return $http.get(environment.notifications);
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
