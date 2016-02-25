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
$api = new Api('json', ['GET', 'PUT', 'POST']);
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
        $id = intval($id);
        if ($api->requesterId !== $id && !$api->checkScope('admin')) {
            $api->output(403, 'Admin scope is required for getting another user');
            //indicate the requester do not have the required scope for querying another user
            return;
        }
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
        $id = intval($id);
        if ($api->requesterId !== $id && !$api->checkScope('admin')) {
            $api->output(403, 'Admin scope is required for updating another user');
            //indicate the requester do not have the required scope for updating another user
            return;
        }
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
        if (!$user->update($updatedUser, $errorMessage)) {
            $api->output(500, 'Error during profile update'.$errorMessage);
            //something gone wrong :(
            return;
        }
        //update scope if requester is admin
        if (property_exists($updatedUser, 'scope') && $api->checkScope('admin')) {
            if (!$user->updateScope($updatedUser->scope)) {
                $api->output(500, 'User is updated but not his scope');
                //something gone wrong :(
                return;
            }
        }
        $api->output(200, $user->getProfile());
        break;
    case 'POST':
        //create a user
        if (!$api->checkAuth()) {
            //User not authentified/authorized
            return;
        }
        if (!$api->checkScope('admin')) {
            $api->output(403, 'Admin scope is required for creating user');
            //indicate the requester do not have the required scope for creating another user
            return;
        }
        $user = new User();
        //adapt and validate object received
        $requestedUser = $api->query['body'];
        if ($requestedUser !== null) {
            $requestedUser->status = 1;
        }
        if (!$user->validateModel($requestedUser, $errorMessage)) {
            $api->output(400, 'User is not valid: '.$errorMessage);
            //provided user is not valid
            return;
        }
        //create user
        if (!$user->create($requestedUser, $errorMessage)) {
            $api->output(500, 'Error during creation: '.$errorMessage);
            //something gone wrong :(
            return;
        }
        //add scope
        if (property_exists($requestedUser, 'scope')) {
            if (!$user->updateScope($requestedUser->scope)) {
                $api->output(500, 'User is created but not his scope');
                //something gone wrong :(
                return;
            }
        }
        $api->output(201, $user->getProfile());
        break;
}
