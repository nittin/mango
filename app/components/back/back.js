/**
 * Created by TinND on 4/20/2017.
 */
'use strict';

angular.module('myApp.back', [
    'myApp.back.user-service'
])

    .value('environment', {
        user: 'http://coloroi.com/back/users',
        userDetail: 'http://coloroi.com/back/users/'
    });