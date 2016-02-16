/**
 * Library Factory
 * @version 1.0.0
 */
'use strict';
angular
.module('wmpApp')
.factory('Library', Library);

function Library($resource) {
    return $resource('/server/api/library/tracks:id', null,
    {
        'save': {method: 'POST', isArray: true}
    });
};
