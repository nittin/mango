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
    .value('$mobile', {exist: false, ios: false, android: false, type: ''})
    .run(function ($mobile) {
        if (window.monaca) {
            $mobile.exist = true;
            $mobile.android = window.monaca.isAndroid;
            $mobile.ios = window.monaca.isIOS;
            $mobile.type = $mobile.android ? 'android' : $mobile.ios ? 'ios' : 'other';
        }
    })
    .controller('MasterCtrl', function ($rootScope, $scope, $q, $mobile) {
        $scope.device = $mobile.type;
    });
