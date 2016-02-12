/**
 * User Factory
 * @version 1.0.0
 */
'use strict';
angular
.module('wmpApp')
.factory('User', function ($window) {
    var User = {
        //attributes
        token: null,
        login: null,
        name: null,
        scope: [],
        //populate attributes with a payload
        populate(payload){
            this.token = payload.token;
            this.login = payload.login;
            this.name = payload.name;
            this.scope = payload.scope;
            this.exp = payload.exp;
        },
        //return stored token
        getToken(){
            try {
                var payload = JSON.parse($window.localStorage.user);
                this.populate(payload);
                return this.token;
            } catch(e) {
                return false;
            }
        },
        //handle received token and store it
        handleToken(data) {
            if (!data.token) {
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
    return User;
});
