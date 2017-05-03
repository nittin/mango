'use strict';

angular.module('myApp.info', ['ngRoute'])

    .config(['$routeProvider', function ($routeProvider) {
        $routeProvider.when('/info', {
            templateUrl: 'view/info.view.html',
            controller: 'InfoCtrl'
        });
    }])

    .controller('InfoCtrl', function ($rootScope, $scope) {
        $scope.messages = [];
        $scope.chat = {
            joined: false,
            user: '',
            content: '',
            send: function (user, text) {
                var msg = {
                    user: user,
                    text: text,
                    time: new Date().getTime()
                };
                updateMessages(msg);
                conn.send(angular.toJson(msg));
                $scope.chat.content = '';
            },
            join: function (user) {
                var msg = {
                    user: user,
                    text: user + ' entered the room',
                    time: new Date().getTime()
                };
                updateMessages(msg);
                conn.send(angular.toJson(msg));
                $scope.chat.joined = true;
            },
            leave: function (user) {
                var msg = {
                    user: user,
                    text: user + ' has left the room',
                    time: new Date().getTime()
                };
                updateMessages(msg);
                conn.send(angular.toJson(msg));
                conn.close();
                $scope.messages = [];
                $scope.chat.content = '';
                $scope.chat.joined = false;
            }
        };

        function updateMessages(msg) {
            $scope.messages.push(msg);
        }

        var conn = new WebSocket('ws://localhost:8080');
        conn.onopen = function (e) {
            console.log("Connection established!");
        };

        conn.onmessage = function (e) {
            var msg = angular.fromJson(e.data);
            updateMessages(msg);
            $scope.$apply();
        };
    });