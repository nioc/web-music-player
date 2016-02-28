/**
 * Folder Factory
 * @version 1.0.0
 */
'use strict';
angular
.module('wmpApp')
.factory('Folder', Folder);

function Folder($resource) {
    return $resource('/demo-files/data/:resource-:action.json/', null,
        {
            'query': {method: 'GET', params: {resource: 'folder', action: 'single'}, isArray: true},
        });
}
