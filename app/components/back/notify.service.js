'use strict';

angular.module('myApp.back.notify-service', [])

    .service('notify', function (environment, $http, $q, $localStorage, device, user) {
        this.wave = function (target) {
            return $http.post(environment.wave, {
                id: user.current.id, name: user.current.name, target_id: target
            });
        };
        return this;
    });
