'use strict';

angular.module('myApp.back.session-storage-service', [])

    .service('$localStorage', function ($q) {
        this.set = function (key, value) {
            localStorage.setItem(key, angular.toJson(value));
        };

        this.get = function (key) {
            var value = localStorage.getItem(key);
            return angular.fromJson(value);
        };

        this.clear = function () {
            localStorage.clear();
        };

        return this;
    });
