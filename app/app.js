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
    'myApp.master',
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
    })
    .value('$mobile', {exist: false, ios: false, android: false, type: ''})
    .run(function ($mobile) {
        if (window.monaca) {
            $mobile.exist = true;
            $mobile.android = window.monaca.isAndroid;
            $mobile.ios = window.monaca.isIOS;
            $mobile.type = $mobile.android ? 'android' : $mobile.ios ? 'ios' : 'other';
        }
    });
