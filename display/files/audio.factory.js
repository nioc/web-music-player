/**
 * HTML audio object Factory
 * @version 1.0.0
 */
angular
.module('wmpApp')
.factory('Audio', Audio);

function Audio($document) {
    return $document[0].createElement('audio');
};
