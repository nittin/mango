'use strict';

angular.module('myApp.info', ['ngRoute'])

    .config(['$routeProvider', function ($routeProvider) {
        $routeProvider.when('/info', {
            templateUrl: 'view/info.view.html',
            controller: 'InfoCtrl'
        });
    }])

    .controller('InfoCtrl', [function () {

    }]);