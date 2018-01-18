'use strict';

angular.module('myApp.map')
    .component('mapTerm',{
        templateUrl: 'view/map.term.view.html',
        controller: 'MapTermCtrl',
        bindings: {
            onAgree: '&',
            onDisagree: '&'
        }
    })
    .controller('MapTermCtrl', function ($rootScope, $scope, $localStorage, $mdSidenav, $mdMedia, $mdToast, $q, $timeout, $http, $interval, uiGmapIsReady, user, notify, environment, $group, $mdColorPalette) {
        var ctrl = this;
        ctrl.accept = false;
        this.$onInit = function () { };
        
        this.broadcast = function () {
            ctrl.accept = true;
            if (navigator.geolocation) {
                var a=navigator.geolocation.getCurrentPosition(function (position) {
                    ctrl.onAgree({value: position.coords});
                    $scope.$apply();
                }, function (error) {
                    if (error.code === error.PERMISSION_DENIED) {
                        ctrl.accept = false;
                        $scope.$apply();
                    }
                });
                console.log(a);
            }
            //
        };
    });