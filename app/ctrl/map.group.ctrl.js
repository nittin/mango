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
        $scope.group2 = {
            first: true,
            init: function () {
                $group.getAll().then(function (res) {
                    res.data.forEach(function (i) {
                        i.members.forEach(function (j) {
                            j.photo = {
                                marker: environment.markerPhoto + j.id + '.png',
                                pin: environment.pinPhoto + j.id + '.png',
                                origin: environment.originPhoto + j.id + '.jpg'
                            };
                        });
                    });
                    $scope.group.list = res.data;
                });
            },
            list: [],
            adding: false,
            form: {
                query: '',
                member: [],
                name: '',
                description: '',
                theme: ''
            },
            themes: Object.keys($mdColorPalette),
            create: function () {
                var members = this.form.member.map(function (i) {
                    return i.id;
                }).join(',');
                $group.create(this.form.name, this.form.description, this.form.theme, members).then(function (res) {
                    $scope.group.adding = false;
                    $scope.group.init();
                });
            },
            carousel: {
                active: 0,
                detail: null,
                statusMap: {},
                actions: {},
                more: function (group) {
                    $scope.group.carousel.detail = group.id;
                    $scope.carousel.active = -1;
                },
                less: function () {
                    $scope.group.carousel.detail = null;
                }
            },
            open: function (group) {
                var self = this;
                this.list.forEach(function (i, index) {
                    if (i.id !== group.id) {

                    } else {
                        self.carousel.active = index;
                    }
                });
                $scope.marker.list.forEach(function (i) {
                    i.options.visible = group.members.filter(function (j) {
                        return j.id === i.id
                    }).length > 0;
                });
                return false;
            },
            close: function () {
                this.carousel.active = -1;
            },
            select: function (group) {
                this.open(group);
            }
        };
    });