<?php

/**
 * Administration API.
 *
 * Provides system configuration informations and a way to update it
 *
 * @version 1.0.0
 *
 * @api
 */
require_once $_SERVER['DOCUMENT_ROOT'].'/server/lib/Api.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/server/lib/Artist.php';
$api = new Api('json', ['GET', 'PUT']);
if (!$api->checkAuth()) {
    //User not authentified/authorized
    return;
}
if (!$api->checkScope('admin')) {
    $api->output(403, 'Admin scope is required for the system administration API');
    //current user has no admin scope, return forbidden
    return;
}
require_once $_SERVER['DOCUMENT_ROOT'].'/server/lib/Configuration.php';
$configuration = new Configuration();
switch ($api->method) {
    case 'GET':
        //returns the settings configuration
        $api->output(200, $configuration->query());
        break;
    case 'PUT':
        //update setting
        if (!$api->checkParameterExists('key', $key)) {
            $api->output(400, '`Key` must be provided in path');
            //Key was not provided, return an error
            return;
        }
        if (!$api->checkParameterExists('value', $value)) {
            $api->output(400, '`Value` must be provided in body');
            //Value was not provided, return an error
            return;
        }
        if (!$configuration->set($key, $value)) {
            $api->output(500, 'An error occurred while processing your request');
            //There was an error during update, return an error
            return;
        }
        $setting = new stdClass();
        $setting->key = $key;
        $setting->value = $value;
        $api->output(200, $setting);
        break;
}
