/*
 * sign AngularJS code for wmp
 * version 1.1.0
 */
(function() {
    'use strict';
    angular
    //declare module
    .module('wmpApp', [])
    //declare sign controller
    .controller('SignController', SignController);
    //SignController function
    function SignController($scope, $http, $window, LocalUser) {
        $scope.user = {login: null, password: null};
        $scope.result = {text: '', class: ''};
        $scope.submit = submit;
        function submit() {
            var user = LocalUser;
            $http
            .post('/server/api/users/tokens', $scope.user)
            .then(successCallback, errorCallback);
            function successCallback(response) {
                if (user.handleToken(response.data)) {
                    //redirect user to the main page
                    $window.location = '/';
                } else {
                    errorCallback(response);
                }
            }
            function errorCallback(response) {
                // Offline
                if (response.status === -1) {
                    $scope.result = {text: 'Can not reach server, please check your internet connection or try again later', class: 'form-error'};
                    return;
                }
                // Erase the token if the user fails to log in
                user.deleteToken();
                $scope.result = {text: 'Invalid credentials', class: 'form-error'};
                if (response.data && response.data.message) {
                    $scope.result.text = response.data.message;
                }
            }
        }
    }
})();
