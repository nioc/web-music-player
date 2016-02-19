/**
 * User Factory
 * @version 1.0.0
 */
'use strict';
//User factory
function User($resource) {
    return $resource('/server/api/users/:id', {id: '@sub'},
    {
        'update': {method: 'PUT'}
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
        populate(payload) {
            this.token = payload.token;
            this.id = payload.id;
            this.login = payload.login;
            this.name = payload.name;
            this.scope = payload.scope;
            this.exp = payload.exp;
        },
        //return user profile
        getProfile() {
            if (this.getToken()) {
                var profile = JSON.parse(JSON.stringify(this));
                return profile;
            }
            return false;
        },
        //return stored token
        getToken() {
            try {
                var payload = JSON.parse($window.localStorage.user);
                this.populate(payload);
                return this.token;
            } catch (e) {
                return false;
            }
        },
        //handle received token and store it
        handleToken(data) {
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
        deleteToken() {
            delete $window.localStorage.user;
        }
    };
    return LocalUser;
}

angular
.module('wmpApp')
.factory('User', User)
.factory('LocalUser', LocalUser);
