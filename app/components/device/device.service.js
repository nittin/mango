'use strict';

angular.module('myApp.back.device-service', [])

    .service('device', function ($q) {
        this.check = function () {
            if (/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent)) {
                return 2;
            } else {
                return 1;
            }
        };

        return this;
    });
