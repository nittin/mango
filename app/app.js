'use strict';

// Declare app level module which depends on views, and components
angular.module('myApp', [
    'ngRoute',
    'ngMaterial',
    'ngMessages',
    'ngSanitize',
    'uiGmapgoogle-maps',
    'ngFacebook',
    'myApp.home',
    'myApp.map',
    'myApp.info',
    'myApp.version',
    'myApp.back'
]).config(function ($locationProvider, $routeProvider, $facebookProvider) {
    $locationProvider.hashPrefix('');

    $routeProvider.otherwise({redirectTo: '/'});
    //main 192365741272526
    //test 192972897878477
    $facebookProvider.setAppId('192365741272526');
    $facebookProvider.setPermissions('public_profile,email,user_friends');
    $facebookProvider.setVersion('v2.9');
})
    .controller('MasterCtrl', function ($rootScope, $scope, $q) {
        
    });
