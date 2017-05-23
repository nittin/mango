'use strict';

// Declare app level module which depends on views, and components
angular.module('myApp', [
    'ngRoute',
    'ngMaterial',
    'ngMessages',
    'ngSanitize',
    'ngTouch',
    'ngCordova',
    'uiGmapgoogle-maps',
    'angular-carousel',
    'myApp.home',
    'myApp.map',
    'myApp.info',
    'myApp.version',
    'myApp.device',
    'myApp.storage',
    'myApp.back'
])
    .config(function ($locationProvider, $routeProvider) {
        $locationProvider.hashPrefix('');

        $routeProvider.otherwise({redirectTo: '/'});
        //main 192365741272526
        //test 192972897878477
    })
    .value('$mobile', {exist: false})
    .run(function ($mobile) {
        if (window.monaca) {
            $mobile.exist = true;
        }
    })
    .controller('MasterCtrl', function ($rootScope, $scope, $q) {

    });
