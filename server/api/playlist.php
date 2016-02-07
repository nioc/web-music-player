<?php

/**
 * Playlist API.
 *
 * Provides the current user's playlist and the included tracks
 *
 * @version 1.0.0
 *
 * @api
 */
include_once $_SERVER['DOCUMENT_ROOT'].'/server/lib/Api.php';
$api = new Api('json', ['POST', 'GET', 'DELETE', 'PUT']);
include_once $_SERVER['DOCUMENT_ROOT'].'/server/lib/Playlist.php';
switch ($api->method) {
    case 'GET':
        //querying a user playlist
        if (!$api->checkParameterExists('userId', $userId)) {
            $api->output(400, 'User identifier must be provided');
            //user was not provided, return an error
            return;
        }
        $playlist = new Playlist($userId);
        $playlist->populate();
        if (count($playlist->tracks) === 0) {
            $api->output(204, null);
            //user's playlist is empty
            return;
        }
        $api->output(200, $playlist->tracks);
        break;
    case 'POST':
        if (!$api->checkParameterExists('userId', $userId)) {
            $api->output(400, 'User identifier must be provided');
            //user was not provided, return an error
            return;
        }
        if (!$api->checkParameterExists('id', $trackId)) {
            $api->output(400, 'Track identifier must be provided');
            //track identifier was not provided, return an error
            return;
        }
        $playlistItem = new PlaylistItem($userId, null, $trackId);
        $response = $playlistItem->insert();
        if (!$response) {
            $api->output(500, 'Add error');
            //something happened during track insertion, return internal error
            return;
        }
        $api->output(201, $response);
        break;
    case 'DELETE':
        if (!$api->checkParameterExists('userId', $userId)) {
            $api->output(400, 'User identifier must be provided');
            //user was not provided, return an error
            return;
        }
        if (!$api->checkParameterExists('sequence', $sequence)) {
            $api->output(400, 'Track sequence must be provided');
            //$sequence was not provided, return an error
            return;
        }
        $playlistItem = new PlaylistItem($userId, $sequence, null);
        if (!$playlistItem->delete()) {
            $api->output(404, 'No such track in playlist');
            //something happened during track deletion (probably sequence was not existing), return not found error
            return;
        }
        $api->output(204, null);
        break;
    case 'PUT':
        if (!$api->checkParameterExists('newSequence', $newSequence)) {
            $api->output(400, 'New sequence not provided');
            //new sequence was not provided, return an error
            return;
        }
        if (!$api->checkParameterExists('sequence', $oldSequence)) {
            $api->output(400, 'Current sequence not provided');
            //old sequence was not provided, return an error
            return;
        }
        if (!$api->checkParameterExists('userId', $userId)) {
            $api->output(400, 'User identifier not provided');
            //user was not provided, return an error
            return;
        }
        $playlist = new Playlist($userId);
        if (!$playlist->reorder($oldSequence, $newSequence)) {
            $api->output(500, 'Internal error');
            //something gone wrong :(
            return;
        }
        $playlist->populate();
        if (count($playlist->tracks) === 0) {
            $api->output(204, null);
            //user's playlist is empty (should not happens but we handle it)
            return;
        }
        //return all the user's playlist tracks for synchronizing with GUI
        $api->output(200, $playlist->tracks);
        break;
    default:
        $api->output(501, $this->method.' method is not supported for this ressource');
}
