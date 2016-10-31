/**
 * Setting Factory
 * @version 1.0.0
 */
'use strict';
angular
.module('wmpApp')
.factory('Setting', Setting);

function Setting($resource) {
    return $resource('/server/api/settings/:key', {key: '@key'},
    {
        'update': {method: 'PUT'}
    });
}
