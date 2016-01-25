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
$api = new Api('json', ['POST', 'GET', 'DELETE']);
include_once $_SERVER['DOCUMENT_ROOT'].'/server/lib/Playlist.php';
switch ($api->method) {
    case 'GET':
        //querying a user playlist
        if (!array_key_exists('userId', $api->query)) {
            $api->output(404, 'User identifier must be provided');
            exit();
        }
        $playlist = new Playlist();
        $playlist->populate($api->query['userId']);
        if (count($playlist->tracks) == 0) {
            $api->output(204, null);
        } else {
            $api->output(200, $playlist->tracks);
        }
        exit();
        break;
    case 'POST':
        /* @TODO
        if (!array_key_exists('userId', $api->query)){
            exit();
        }
        */
        $playlistItem = new PlaylistItem($api->query['userId'], null, $api->query['body']->id);
        $response = $playlistItem->insert();
        if ($response) {
            $api->output(201, $response);
        } else {
            $api->output(500, 'Update error');
        }
        exit();
        break;
    case 'DELETE':
        $playlistItem = new PlaylistItem($api->query['userId'], $api->query['sequence'], null);
        if ($playlistItem->delete()) {
            $api->output(204, null);
        } else {
            $api->output(404, 'No such track in playlist');
        }
        break;
    default:
        $api->output(501, $this->method.' method is not supported for this ressource');
        exit();
}
