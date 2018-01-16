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
        this.$onInit = function () {
        };
        this.broadcast = function () {
            ctrl.onAgree({});
        };
    });