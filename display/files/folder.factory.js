/**
 * Folder Factory
 * @version 1.0.0
 */
'use strict';
angular
.module('wmpApp')
.factory('Folder', Folder);

function Folder($resource) {
    return $resource('/server/api/library/folders:id');
}
