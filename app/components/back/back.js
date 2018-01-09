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
        key: ENVIRONMENT,
        oauth: BACK_HOST_NAME + 'back/auth',
        user: BACK_HOST_NAME + 'back/users',
        signOut: BACK_HOST_NAME + 'back/users/me/out',
        myProfile: BACK_HOST_NAME + 'back/users/me/profile',
        myFriends: BACK_HOST_NAME + 'back/users/me/friends',
        notifications: BACK_HOST_NAME + 'back/notifications',
        userDetail: BACK_HOST_NAME + 'back/users/',
        cropPhoto: BACK_HOST_NAME + 'back/photo?url=',
        markerPhoto: BACK_HOST_NAME + 'back/assets/users/marker/',
        pinPhoto: BACK_HOST_NAME + 'back/assets/users/pin/',
        originPhoto: BACK_HOST_NAME + 'back/assets/users/origin/',
        assets: BACK_HOST_NAME + 'back/assets/img/',
        group: BACK_HOST_NAME + 'back/groups',
        wave: BACK_HOST_NAME + 'back/notify/wave',
        fb: BACK_HOST_NAME + 'back/fb',
        valid: BACK_HOST_NAME + 'back/fb/valid',
        me: BACK_HOST_NAME + 'back/fb/me'
    });