/**
 * MusicBrainz Factory
 * @version 1.0.0
 */
'use strict';
angular
.module('wmpApp')
.factory('MusicBrainz', MusicBrainz);

function MusicBrainz($resource) {
    return $resource('/server/api/MusicBrainz/:type', {type: '@type'});
}
