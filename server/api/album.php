<?php

/**
 * Album API.
 *
 * Provides album informations
 *
 * @version 1.0.0
 *
 * @api
 */
include_once $_SERVER['DOCUMENT_ROOT'].'/server/lib/Api.php';
include_once $_SERVER['DOCUMENT_ROOT'].'/server/lib/Album.php';
$api = new Api('json', ['GET', 'DELETE', 'PUT']);
switch ($api->method) {
    case 'GET':
        //returns the album
        if (!$api->checkAuth()) {
            //User not authentified/authorized
            return;
        }
        if (!$api->checkParameterExists('id', $id)) {
            $api->output(400, 'Album identifier must be provided');
            //Album was not provided, return an error
            return;
        }
        $album = new Album();
        if (!$album->populate(['id' => $id])) {
            $api->output(404, 'Album not found');
            //indicate the album was not found
            return;
        }
        $album->getTracks();
        $api->output(200, $album->structureData());
        break;
    case 'DELETE':
        //delete album
        if (!$api->checkAuth()) {
            //User not authentified/authorized
            return;
        }
        if (!$api->checkScope('admin')) {
            $api->output(403, 'Admin scope is required for deleting album');
            //indicate the requester do not have the required scope for deleting album
            return;
        }
        if (!$api->checkParameterExists('id', $id)) {
            $api->output(400, 'Album identifier must be provided');
            //Album was not provided, return an error
            return;
        }
        $album = new Album($id);
        if (!$album->delete()) {
            $api->output(500, 'Error during album deletion');
            //something gone wrong :(
            return;
        }
        $api->output(204, null);
        break;
    case 'PUT':
        //update album
        if (!$api->checkAuth()) {
            //User not authentified/authorized
            return;
        }
        if (!$api->checkScope('admin')) {
            $api->output(403, 'Admin scope is required for editing album');
            //indicate the requester do not have the required scope for updating album
            return;
        }
        if (!$api->checkParameterExists('id', $id)) {
            $api->output(400, 'Album identifier must be provided');
            //album was not provided, return an error
            return;
        }
        $album = new Album($id);
        if (!$album->populate(['id' => $id])) {
            $api->output(404, 'Album not found');
            //indicate the album was not found
            return;
        }
        //adapt and validate object received
        $updatedAlbum = $api->query['body'];
        if (!$album->validateModel($updatedAlbum, $errorMessage)) {
            $api->output(400, 'Album is not valid: '.$errorMessage);
            //provided album is not valid
            return;
        }
        if (!$album->update($errorMessage)) {
            $api->output(500, 'Error during album update'.$errorMessage);
            //something gone wrong :(
            return;
        }
        $album->getTracks();
        $api->output(200, $album->structureData());
        break;
}
