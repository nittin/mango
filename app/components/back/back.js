/**
 * Created by TinND on 4/20/2017.
 */
'use strict';

angular.module('myApp.back', [
    'myApp.back.user-service',
    'myApp.back.user-filter'
])

    .value('environment', {
        user: BACK_HOST_NAME + 'back/users',
        userDetail: BACK_HOST_NAME + 'back/users/',
        cropPhoto: BACK_HOST_NAME + 'back/photo?url='
    });