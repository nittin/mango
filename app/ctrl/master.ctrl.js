'use strict';

angular.module('myApp.master', [])
    .controller('MasterCtrl', function ($rootScope, $scope, $q, $mobile) {
        $scope.device = $mobile.type;
    });