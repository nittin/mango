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
    'pascalprecht.translate',
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
    .config(function ($locationProvider, $routeProvider, $mdThemingProvider, $httpProvider, $translateProvider) {
        $locationProvider.hashPrefix('');
        $routeProvider.otherwise({redirectTo: '/'});
        // $httpProvider.defaults.withCredentials = true;
        $httpProvider.interceptors.push('httpRequestInterceptor');
        $translateProvider
            .translations('en', LANG_EN)
            .translations('vi', LANG_VI)
            .preferredLanguage('en');
        $mdThemingProvider.theme('lime-dark')
            .primaryPalette('lime')
            .accentPalette('orange')
            .warnPalette('blue').dark();
        $mdThemingProvider.alwaysWatchTheme(true);
    })
    .value('$mobile', {exist: false, ios: false, android: false, type: ''})
    .run(function ($mobile, $rootScope, environment) {
        if (window.monaca) {
            $mobile.exist = true;
            $mobile.android = window.monaca.isAndroid;
            $mobile.ios = window.monaca.isIOS;
            $mobile.type = $mobile.android ? 'android' : $mobile.ios ? 'ios' : 'other';
        }
        $rootScope.dev = environment.key === 'ci';
    })
    .factory('httpRequestInterceptor', function ($localStorage) {
        return {
            request: function ($config) {
                if ($localStorage.get(STORAGE_TOKEN)) {
                    $config.headers['Authorization'] = $localStorage.get(STORAGE_TOKEN);
                }
                return $config;
            }
        };
    });
