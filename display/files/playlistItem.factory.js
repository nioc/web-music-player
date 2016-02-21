/**
 * PlaylistItem Factory
 * @version 1.0.0
 */
'use strict';
angular
.module('wmpApp')
.factory('PlaylistItem', PlaylistItem);

function PlaylistItem($resource) {
    return $resource('/server/api/users/:userId/playlist/tracks/:sequence', {userId: '@userId', sequence: '@sequence'},
    {
        'update': {method: 'PUT', isArray: true}
    });
}
