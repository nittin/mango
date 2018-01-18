
'use strict';

angular.module('myApp.master', [])
    .controller('MasterCtrl', function ($rootScope, $scope, $q, $mobile, user, $location, $localStorage) {
        $rootScope.isAuth = $localStorage.get(STORAGE_LOGIN);
        $scope.device = $mobile.type;
        $rootScope.signOut = function () {
            user.signOut();
            $rootScope.isAuth = false;
            $location.path('/').replace();
        };
    });