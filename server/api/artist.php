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
include_once $_SERVER['DOCUMENT_ROOT'].'/server/lib/Api.php';
include_once $_SERVER['DOCUMENT_ROOT'].'/server/lib/Artist.php';
$api = new Api('json', ['GET']);
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
        $api->output(200, $artist->structureData());
        break;
}
