/**
 * Artist Factory
 * @version 1.1.1
 */
'use strict';
angular
.module('wmpApp')
.factory('Artist', Artist);

function Artist($resource, $cacheFactory) {
    //declare cache variable
    var cache = $cacheFactory('ArtistsCache');

    //this interceptor will clear cached resources (only item)
    var removeCache = {
        response(response) {
            cache.remove(response.config.url);
            //remove Library cache
            if($cacheFactory.get('LibraryCache')) {
                $cacheFactory.get('LibraryCache').removeAll();
            }
            return response;
        }
    };

    return $resource('/server/api/artists/:id', {id: '@id'},
    {
        'get':    {method: 'GET', cache: cache},
        'update': {method: 'PUT', interceptor: removeCache},
        'delete': {method: 'DELETE', interceptor: removeCache}
    });
}
