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
$api = new Api('json', ['GET', 'POST']);
include_once $_SERVER['DOCUMENT_ROOT'].'/server/lib/Track.php';
switch ($api->method) {
    case 'GET':
        //returns the library
        if (!$api->checkAuth()) {
            //User not authentified/authorized
            return false;
        }
        $library = new Tracks();
        $parameter = array();
        //checks parameters
        $api->checkParameterExists('title', $parameter['trackTitle']);
        $api->checkParameterExists('artist', $parameter['artistName']);
        $api->checkParameterExists('album', $parameter['albumName']);
        //querying the library
        if (!$library->populateTracks($parameter)) {
            $api->output(500, 'Querying error');
            //SQL error
            return;
        }
        if (count($library->tracks) == 0) {
            $api->output(204, null);
            //no data to provides
            return;
        }
        $api->output(200, $library->tracks);
        break;
    case 'POST':
        //scan folder and add tracks
        if (!$api->checkAuth()) {
            //User not authentified/authorized
            return false;
        }
        if (!$api->checkParameterExists('folder', $folder)) {
            $api->output(400, 'Folder not provided');
            //folder was not provided, return an error
            return;
        }
        $library = new Tracks();
        if (count($tracks = $library->addFiles($folder)) == 0) {
            $api->output(500, 'No track was added');
            //no track was added...
            return;
        }
        $api->output(201, $tracks);
        break;
}
