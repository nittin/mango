/**
 * Created by TinND on 4/20/2017.
 */
'use strict';

angular.module('myApp.back', [
    'myApp.back.user-service'
])

    .value('environment', {
        user: 'https://mangoround.com/back/users',
        userDetail: 'https://mangoround.com/back/users/',
        cropPhoto: 'https://mangoround.com/back/photo?url='
    });