/**
 * Album Factory
 * @version 1.1.0
 */
'use strict';
angular
.module('wmpApp')
.factory('Album', Album);

function Album($resource, $cacheFactory) {
    //declare cache variable
    var cache = $cacheFactory('AlbumsCache');

    //this interceptor will clear cached resources (only item)
    var removeCache = {
        response: function (response) {
            cache.remove(response.config.url);
            //remove Library cache
            $cacheFactory.get('LibraryCache').removeAll();
            return response;
        }
    };

    return $resource('/server/api/albums/:id', {id: '@id'},
    {
        'get':    {method: 'GET', cache: cache},
        'update': {method: 'PUT', interceptor: removeCache},
        'delete': {interceptor: removeCache}
    });
}
