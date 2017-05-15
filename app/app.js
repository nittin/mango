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
    'ngFacebook',
    'angular-carousel',
    'myApp.home',
    'myApp.map',
    'myApp.info',
    'myApp.version',
    'myApp.device',
    'myApp.back'
]).config(function ($locationProvider, $routeProvider, $facebookProvider) {
    $locationProvider.hashPrefix('');

    $routeProvider.otherwise({redirectTo: '/'});
    //main 192365741272526
    //test 192972897878477
    $facebookProvider.setAppId(FB_APP_ID);
    $facebookProvider.setPermissions('public_profile,email,user_friends');
    $facebookProvider.setVersion('v2.9');
})
    .value('$mobile', {exist: false})
    .run(function ($mobile) {
        if (window.monaca) {
            $mobile.exist = true;
        }
    })
    .controller('MasterCtrl', function ($rootScope, $scope, $q) {

    });
