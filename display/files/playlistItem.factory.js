/**
 * PlaylistItem Factory
 * @version 1.0.0
 */
'use strict';
angular
.module('wmpApp')
.factory('PlaylistItem', PlaylistItem);

function PlaylistItem($resource) {
    return $resource('../demo-files/data/playlist-:action-:identifer.json/', null,
    {
        'query': {method: 'GET', params: {resource: 'playlist', action: 'query'}, isArray: true},
        'save': {method: 'GET', params: {resource: 'playlist', action: 'single'}, isArray: false},
        'delete': {method: 'GET', params: {resource: 'playlist', action: 'empty'}, isArray: false},
        'update': {method: 'GET', params: {resource: 'playlist', action: 'query'}, isArray: true}
    });
}
