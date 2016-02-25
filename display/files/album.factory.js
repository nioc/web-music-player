/**
 * Album Factory
 * @version 1.0.0
 */
'use strict';
angular
.module('wmpApp')
.factory('Album', Album);

function Album($resource) {
    return $resource('/server/api/albums/:id', {id: '@id'});
}
