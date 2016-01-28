<?php

/**
 * Library API.
 *
 * Provides the list of all the tracks included in the library
 *
 * @version 1.0.0
 *
 * @api
 */
include_once $_SERVER['DOCUMENT_ROOT'].'/server/lib/Api.php';
$api = new Api('json', ['GET']);
include_once $_SERVER['DOCUMENT_ROOT'].'/server/lib/Track.php';
switch ($api->method) {
    case 'GET':
        //returns the library
        $library = new Tracks();
        if ($library->get()) {
            $api->output(200, $library->tracks);
        } else {
            $api->output(500, 'SQL error');
        }
        break;
}
