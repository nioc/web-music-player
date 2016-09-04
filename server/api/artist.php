<?php

/**
 * Artist API.
 *
 * Provides artist informations
 *
 * @version 1.0.0
 *
 * @api
 */
require_once $_SERVER['DOCUMENT_ROOT'].'/server/lib/Api.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/server/lib/Artist.php';
$api = new Api('json', ['GET', 'DELETE', 'PUT']);
switch ($api->method) {
    case 'GET':
        //returns the artist
        if (!$api->checkAuth()) {
            //User not authentified/authorized
            return;
        }
        if (!$api->checkParameterExists('id', $id)) {
            $api->output(400, 'Artist identifier must be provided');
            //artist was not provided, return an error
            return;
        }
        $artist = new Artist();
        if (!$artist->populate(['id' => $id])) {
            $api->output(404, 'Artist not found');
            //indicate the artist was not found
            return;
        }
        $artist->getTracks();
        $api->output(200, $artist->structureData());
        break;
    case 'DELETE':
        //delete artist and all his tracks
        if (!$api->checkAuth()) {
            //User not authentified/authorized
            return;
        }
        if (!$api->checkScope('admin')) {
            $api->output(403, 'Admin scope is required for deleting artist');
            //indicate the requester do not have the required scope for deleting artist
            return;
        }
        if (!$api->checkParameterExists('id', $id)) {
            $api->output(400, 'Artist identifier must be provided');
            //artist was not provided, return an error
            return;
        }
        $artist = new Artist($id);
        if (!$artist->delete()) {
            $api->output(500, 'Error during artist deletion');
            //something gone wrong :(
            return;
        }
        $api->output(204, null);
        break;
    case 'PUT':
        //update artist
        if (!$api->checkAuth()) {
            //User not authentified/authorized
            return;
        }
        if (!$api->checkScope('admin')) {
            $api->output(403, 'Admin scope is required for editing artist');
            //indicate the requester do not have the required scope for updating artist
            return;
        }
        if (!$api->checkParameterExists('id', $id)) {
            $api->output(400, 'Artist identifier must be provided');
            //artist was not provided, return an error
            return;
        }
        $artist = new Artist($id);
        if (!$artist->populate(['id' => $id])) {
            $api->output(404, 'Artist not found');
            //indicate the artist was not found
            return;
        }
        //adapt and validate object received
        $updatedArtist = $api->query['body'];
        if (!$artist->validateModel($updatedArtist, $errorMessage)) {
            $api->output(400, 'Artist is not valid: '.$errorMessage);
            //provided user is not valid
            return;
        }
        if (!$artist->update($errorMessage)) {
            $api->output(500, 'Error during artist update'.$errorMessage);
            //something gone wrong :(
            return;
        }
        $artist->getTracks();
        $api->output(200, $artist->structureData());
        break;
}
