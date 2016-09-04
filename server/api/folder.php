<?php

/**
 * Folders and files server API.
 *
 * Provides the folders stored in the server
 *
 * @version 1.0.0
 *
 * @api
 */
require_once $_SERVER['DOCUMENT_ROOT'].'/server/lib/Configuration.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/server/lib/Api.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/server/lib/Track.php';
$api = new Api('json', ['GET']);
switch ($api->method) {
    case 'GET':
        //returns the folders
        if (!$api->checkAuth()) {
            //User not authentified/authorized
            return false;
        }
        if (!$api->checkScope('admin')) {
            $api->output(403, 'Admin scope is required for listing folders');
            //current user has no admin scope, return forbidden
            return;
        }
        $library = new Tracks();
        $configuration = new Configuration();
        $library->getFolders($configuration->get('filesPath'));
        if (count($library->folders) == 0) {
            $api->output(204);
            //end the process
            return;
        }
        $api->output(200, $library->folders);
        break;
}
