'use strict';

angular.module('myApp.back.local-storage-service', [])

    .service('$sessionStorage', function ($q) {
        this.set = function (key, value) {
            sessionStorage.setItem(key, angular.toJson(value));
        };
        this.get = function (key) {
            var value = sessionStorage.getItem(key);
            return angular.fromJson(value);
        };

        return this;
    });
