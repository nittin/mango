'use strict';

// Declare app level module which depends on views, and components
angular.module('myApp', [
    'ngRoute',
    'uiGmapgoogle-maps',
    'ngFacebook',
    'myApp.view1',
    'myApp.view2',
    'myApp.version',
    'myApp.back'
]).config(function ($locationProvider, $routeProvider, $facebookProvider) {
    $locationProvider.hashPrefix('!');

    $routeProvider.otherwise({redirectTo: '/view1'});
    //main 192365741272526
    //test 192972897878477
    $facebookProvider.setAppId('192972897878477');
    $facebookProvider.setPermissions('public_profile,email,user_friends');
    $facebookProvider.setVersion('v2.9');
});
