/*
 * sign AngularJS code for wmp
 * version 1.0.0
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
            .post('/demo-files/data/token.json', $scope.user)
            .then(successCallback, errorCallback);
            function successCallback(response) {
                if (user.handleToken(response.data)) {
                    //redirect user to the main page
                    $window.location = '/display/player.html';
                } else {
                    errorCallback(response);
                }
            }
            function errorCallback(response) {
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
