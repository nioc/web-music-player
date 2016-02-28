/**
 * Artist Factory
 * @version 1.0.0
 */
'use strict';
angular
.module('wmpApp')
.factory('Artist', Artist);

function Artist($resource) {
    return $resource('../demo-files/data/:resource-:action-:id.json/', null,
        {
            'get': {method: 'GET', params: {resource: 'artist', action: 'single', id: '@id'}, isArray: false},
            'update': {method: 'GET', params: {resource: 'artist', action: 'single', id: '@id'}, isArray: false},
            'delete': {method: 'GET', params: {resource: 'playlist', action: 'empty'}, isArray: false},
        });
}
