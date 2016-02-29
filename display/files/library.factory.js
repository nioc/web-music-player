/**
 * Library Factory
 * @version 1.0.0
 */
'use strict';
angular
.module('wmpApp')
.factory('Library', Library);

function Library($resource) {
    return $resource('../demo-files/data/:resource-:action-:identifer.json/', null,
    {
        'query': {method: 'GET', params: {resource: 'library', action: 'query'}, isArray: true},
        'save': {method: 'GET', params: {resource: 'library', action: 'query'}, isArray: true},
        'update': {method: 'GET', params: {resource: 'library', action: 'query'}}
    });
}
