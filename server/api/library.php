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
require_once $_SERVER['DOCUMENT_ROOT'].'/server/lib/Api.php';
$api = new Api('json', ['GET', 'POST', 'PUT']);
require_once $_SERVER['DOCUMENT_ROOT'].'/server/lib/Track.php';
switch ($api->method) {
    case 'GET':
        //returns the library
        if (!$api->checkAuth()) {
            //User not authentified/authorized
            return false;
        }
        if ($api->checkParameterExists('id', $id)) {
            //with 'id' parameter, a single track is requested
            $id = intval($id);
            $track = new Track($id);
            if (!$track->populate()) {
                $api->output(404, 'Track not found');
                //indicate the track was not found
                return;
            }
            $api->output(200, $track->structureData($track));
            break;
        }
        //without 'id' parameter, tracks list is requested
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
        if (!$api->checkScope('admin')) {
            $api->output(403, 'Admin scope is required for creating new track on library');
            //current user has no admin scope, return forbidden
            return;
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
    case 'PUT':
        //update track
        if (!$api->checkAuth()) {
            //User not authentified/authorized
            return;
        }
        if (!$api->checkScope('admin')) {
            $api->output(403, 'Admin scope is required for editing track');
            //indicate the requester do not have the required scope for updating track
            return;
        }
        if (!$api->checkParameterExists('id', $id)) {
            $api->output(400, 'Track identifier must be provided');
            //track was not provided, return an error
            return;
        }
        $track = new Track($id);
        if (!$track->populate()) {
            $api->output(404, 'Track not found');
            //indicate the track was not found
            return;
        }
        //adapt and validate object received
        $updatedTrack = $api->query['body'];
        if (!$track->validateModel($updatedTrack, $errorMessage)) {
            $api->output(400, 'Track is not valid: '.$errorMessage);
            //provided user is not valid
            return;
        }
        if (!$track->update($errorMessage)) {
            $api->output(500, 'Error during track update'.$errorMessage);
            //something gone wrong :(
            return;
        }
        $api->output(200, $track->structureData($track));
        break;
}
