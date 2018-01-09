'use strict';

angular.module('myApp.home')
    .component('homeAuth', {
        templateUrl: 'view/home.auth.view.html',
        controller: 'HomeAuthCtrl',
        bindings: {
            user: '=',
            friends: '=',
        }
    })
    .controller('HomeAuthCtrl', function ($rootScope, $scope, $localStorage, $sessionStorage, $mobile, $element, $location, user) {
        $scope.mode = 'NOT_AUTH';

        this.$onInit = function () {

        };

        this.start = function () {
            $location.path('/map');
        };

    });