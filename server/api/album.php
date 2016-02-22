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
$api = new Api('json', ['GET']);
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
        $api->output(200, $album->structureData());
        break;
}
