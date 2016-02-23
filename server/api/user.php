<?php

/**
 * User API.
 *
 * Provides user management
 *
 * @version 1.0.0
 *
 * @api
 */
include_once $_SERVER['DOCUMENT_ROOT'].'/server/lib/Configuration.php';
include_once $_SERVER['DOCUMENT_ROOT'].'/server/lib/Api.php';
include_once $_SERVER['DOCUMENT_ROOT'].'/server/lib/User.php';
$api = new Api('json', ['GET', 'PUT']);
switch ($api->method) {
    case 'GET':
        if (!$api->checkAuth()) {
            //User not authentified/authorized
            return;
        }
        if (!$api->checkParameterExists('id', $id)) {
            //without 'id' parameter, users list is requested, check if current user is granted
            if (!$api->checkScope('admin')) {
                $api->output(403, 'Admin scope is required for listing users');
                //current user has no admin scope, return forbidden
                return;
            }
            //returns all users
            $user = new User();
            $rawUsers = $user->getAllUsers();
            if ($rawUsers === false) {
                $api->output(500, 'Error while querying');
                //return an internal error
                return;
            }
            $users = array();
            foreach ($rawUsers as $user) {
                array_push($users, $user->getProfile());
            }
            $api->output(200, $users);
            //return users list
            return;
        }
        //returns the requested user profile
        $user = new User($id);
        if (!$user->populate()) {
            $api->output(404, 'User not found');
            //indicate the user was not found
            return;
        }
        $api->output(200, $user->getProfile());
        break;
    case 'PUT':
        //update user profile
        if (!$api->checkAuth()) {
            //User not authentified/authorized
            return;
        }
        if (!$api->checkParameterExists('id', $id)) {
            $api->output(400, 'User identifier must be provided');
            //user was not provided, return an error
            return;
        }
        //get user
        $user = new User($id);
        if (!$user->populate()) {
            $api->output(404, 'User not found');
            //indicate the user was not found
            return;
        }
        //adapt and validate object received
        $updatedUser = $api->query['body'];
        if ($updatedUser !== null) {
            $updatedUser->status = 1;
        }
        if (!$user->validateModel($updatedUser, $errorMessage)) {
            $api->output(400, 'User is not valid: '.$errorMessage);
            //provided user is not valid
            return;
        }
        //update user
        if (!$user->update($updatedUser)) {
            $api->output(500, 'Error during profile update');
            //something gone wrong :(
            return;
        }
        $api->output(200, $user->getProfile());
        break;
}
