/**
 * User Factory
 * @version 1.0.0
 */
'use strict';
//User factory
function User($resource) {
    return $resource('../demo-files/data/:resource-:action-:id.json/', null,
        {
            'query': {method: 'GET', params: {resource: 'user', action: 'query'}, isArray: true},
            'get': {method: 'GET', params: {resource: 'user', action: 'single'}, isArray: false},
            'update': {method: 'GET', params: {resource: 'user', action: 'single', id: '@sub'}, isArray: false},
            'save': {method: 'GET', params: {resource: 'user', action: 'single', id: '1'}, isArray: false},
        });
}
//LocalUser factory
function LocalUser($window) {
    var LocalUser = {
        //attributes
        token: null,
        id: null,
        login: null,
        name: null,
        scope: [],
        exp: null,
        //populate attributes with a payload
        populate: function(payload) {
            this.token = payload.token;
            this.id = payload.id;
            this.login = payload.login;
            this.name = payload.name;
            this.scope = payload.scope;
            this.exp = payload.exp;
        },
        //return user profile
        getProfile: function() {
            if (this.getToken()) {
                var profile = JSON.parse(JSON.stringify(this));
                return profile;
            }
            return false;
        },
        //return stored token
        getToken: function() {
            try {
                var payload = JSON.parse($window.localStorage.user);
                this.populate(payload);
                return this.token;
            } catch (e) {
                return false;
            }
        },
        //handle received token and store it
        handleToken: function(data) {
            if (!data || !data.token) {
                //token is not provided in data
                return false;
            }
            var parts = data.token.split('.');
            if (parts.length !== 3) {
                //token do not contains 3 parts (standard structure)
                return false;
            }
            try {
                //parse the payload part
                var payload = JSON.parse(atob(parts[1]));
                //transform the payload and store it in local storage
                payload.token = data.token;
                payload.id = payload.sub;
                delete payload.sub;
                $window.localStorage.user = JSON.stringify(payload);
                //populate this User
                this.populate(payload);
                return true;
            } catch (err) {
                //error during parsing
                return false;
            }
        },
        //delete token
        deleteToken: function() {
            delete $window.localStorage.user;
        }
    };
    return LocalUser;
}

angular
.module('wmpApp')
.factory('User', User)
.factory('LocalUser', LocalUser);
