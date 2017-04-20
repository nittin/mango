'use strict';

angular.module('myApp.back.user-service', [])

    .service('user', function (environment, $http, $q) {
        this.getAll = function () {
            return $http.get(environment);
        };
        return this;
    });
