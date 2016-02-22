/**
 * Artist Factory
 * @version 1.0.0
 */
'use strict';
angular
.module('wmpApp')
.factory('Artist', Artist);

function Artist($resource) {
    return $resource('/server/api/artists/:id');
}
