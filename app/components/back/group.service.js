'use strict';

angular.module('myApp.back.group-service', [])

    .service('$group', function (environment, $http, $q, $localStorage, user) {
        this.current = {};

        this.getAll = function () {
            var id = user.current.id;
            return $http.get(environment.group, {params: {user: id}});
        };
        this.create = function (name, description, theme, members) {
            var admin = user.current.id;
            return $http.post(environment.group, {
                admin: admin,
                name: name,
                description: description,
                theme: theme,
                members: members
            });
        };

        return this;
    });
