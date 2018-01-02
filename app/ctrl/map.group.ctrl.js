'use strict';

angular.module('myApp.map')
    .component('mapGroup',{
        templateUrl: 'view/map.group.view.html',
        controller: 'MapGroupCtrl',
        controllerAs: 'group',
        bindings: {
            list: '=',
            adding: '=',
            select: '='
        }
    })
    .controller('MapGroupCtrl', function ($rootScope, $scope, $localStorage, $mdSidenav, $mdMedia, $mdToast, $q, $timeout, $http, $interval, uiGmapIsReady, user, notify, environment, $group, $mdColorPalette) {
        this.state = 'not yet';

        this.$onInit = function () {
            this.state = 'hello 2';
        };
    });