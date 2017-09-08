/**
 * Created by TinND on 4/20/2017.
 */
'use strict';

angular.module('myApp.back', [
    'myApp.back.user-service',
    'myApp.back.user-filter',
    'myApp.back.group-service',
    'myApp.back.notify-service'
])

    .value('environment', {
        oauth: BACK_HOST_NAME + 'back/oauth',
        user: BACK_HOST_NAME + 'back/users',
        userDetail: BACK_HOST_NAME + 'back/users/',
        cropPhoto: BACK_HOST_NAME + 'back/photo?url=',
        markerPhoto: BACK_HOST_NAME + 'back/assets/users/marker/',
        pinPhoto: BACK_HOST_NAME + 'back/assets/users/pin/',
        originPhoto: BACK_HOST_NAME + 'back/assets/users/origin/',
        assets: BACK_HOST_NAME + 'back/assets/img/',
        group: BACK_HOST_NAME + 'back/groups',
        wave: BACK_HOST_NAME + 'back/notify/wave',
        fb: BACK_HOST_NAME + 'back/fb'
    });