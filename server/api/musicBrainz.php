<?php

/**
 * MusicBrainz API.
 *
 * Provides access to MusicBrainz API
 *
 * @version 1.1.0
 *
 * @api
 */
require_once $_SERVER['DOCUMENT_ROOT'].'/server/lib/Api.php';
$api = new Api('json', ['GET']);
switch ($api->method) {
    case 'GET':
        if (!$api->checkAuth()) {
            //User not authentified/authorized
            return;
        }
        if (!$api->checkParameterExists('type', $type)) {
            $api->output(400, '`Type` value (albums or artists) must be provided in path');
            //Type was not provided, return an error
            return;
        }
        require_once $_SERVER['DOCUMENT_ROOT'].'/server/lib/MusicBrainz.php';
        $musicBrainz = new MusicBrainz();
        switch ($type) {
            case 'artist':
                if (!$api->checkParameterExists('name', $artistName)) {
                    $api->output(400, 'A `name` parameter must be provided in query string for requesting artists');
                    //Type was not provided, return an error
                    return;
                }
                $result = $musicBrainz->searchArtistByName($artistName);
                if (!$result) {
                    $api->output(400, 'Error: '.$musicBrainz->errorMessage);
                    //return an error with message
                    return;
                }
                $api->output(200, $result);
                break;
            case 'album':
                if (!$api->checkParameterExists('title', $albumTitle)) {
                    $api->output(400, 'A `title` parameter must be provided in query string for requesting albums');
                    //Type was not provided, return an error
                    return;
                }
                $api->checkParameterExists('artist', $albumArtist);
                $result = $musicBrainz->searchAlbumByTitle($albumTitle, $albumArtist);
                if (!$result) {
                    $api->output(400, 'Error: '.$musicBrainz->errorMessage);
                    //return an error with message
                    return;
                }
                $api->output(200, $result);
                break;
            default:
                $api->output(405, 'Type must be valued with albums or artists');
        }
}
