/*
 * sign AngularJS code for wmp
 * version 1.0.0
 */
(function () {
    'use strict';
    angular
    .module('wmpApp', [])
    .controller('SignController', function ($scope, $http, $window, User) {
        $scope.user = { login : null, password : null };
        $scope.result = { text : '', class : '' };
        $scope.submit = function() {
            var user = User;
            $http
            .post('/server/api/users/tokens', $scope.user)
            .then(successCallback, errorCallback);
            function successCallback(response) {
                if (user.handleToken(response.data)) {
                    //redirect user to the main page
                    $window.location = '/main';
                }
                else {
                    errorCallback(response)
                }
            }
            function errorCallback (response) {
                // Erase the token if the user fails to log in
                user.deleteToken();
                $scope.result = { text : 'Invalid credentials', class : 'form-error' };
                if (response.data && response.data.message) {
                    $scope.result.text = response.data.message
                }
            }
        }
    });
})();
