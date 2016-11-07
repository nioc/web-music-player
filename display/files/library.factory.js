/**
 * Library Factory
 * @version 1.1.1
 */
'use strict';
angular
.module('wmpApp')
.factory('Library', Library);

function Library($resource, $cacheFactory) {
    //declare cache variable
    var cache = $cacheFactory('LibraryCache');

    //this interceptor will clear cached resources (collection)
    var removeCache = {
        response(response) {
            cache.removeAll();
            return response;
        }
    };

    return $resource('/server/api/library/tracks/:id', {id: '@id'},
    {
        'get':    {method: 'GET', cache: cache},
        'query':  {method: 'GET', cache: cache, isArray: true},
        'save':   {method: 'POST', isArray: true, interceptor: removeCache},
        'update': {method: 'PUT', interceptor: removeCache}
    });
}
