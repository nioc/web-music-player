/**
 * Album Factory
 * @version 1.0.0
 */
'use strict';
angular
.module('wmpApp')
.factory('Album', Album);

function Album($resource) {
    return $resource('../demo-files/data/:resource-:action-:id.json/', null,
        {
            'get': {method: 'GET', params: {resource: 'album', action: 'single', id: '@id'}, isArray: false},
            'update': {method: 'GET', params: {resource: 'album', action: 'single', id: '@id'}, isArray: false},
            'delete': {method: 'GET', params: {resource: 'playlist', action: 'empty'}, isArray: false},
        });
}