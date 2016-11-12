<?php

/**
 * Media streamer.
 *
 * Provides the track requested by its identifier
 *
 * @version 1.1.0
 */
//manage cache browser: no response needed
if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE'])) {
    header('Last-Modified: '.$_SERVER['HTTP_IF_MODIFIED_SINCE'], true, 304);

    return;
}

//get token parameter
require_once $_SERVER['DOCUMENT_ROOT'].'/server/lib/Api.php';
$api = new Api('base64', ['GET']);
if (!$api->checkAuth()) {
    //User not authentified/authorized
    return;
}

//get id parameter
if (!$api->checkParameterExists('track', $trackId)) {
    $api->output(400, 'Track identifier must be sent');
    //Track identifier not provided
    return;
}

//get file information
require_once $_SERVER['DOCUMENT_ROOT'].'/server/lib/Track.php';
$track = new Track($trackId);
$filename = $track->getFile();
if ($filename === false) {
    $api->output(404, 'Track not found');
    //indicate the track was not found
    return;
}

//manage cache browser
header('Cache-Control: private, max-age=604800, pre-check=604800');
header('Pragma: private');
header('Expires: '.date('D, d M Y H:i:s', strtotime('7 day')).' GMT');
if (isset($track->additionTime)) {
    header('Last-Modified: '.gmdate('D, d M Y H:i:s', $track->additionTime).' GMT');
} else {
    header('Last-Modified: '.gmdate('D, d M Y H:i:s', time()).' GMT');
}

//get content base64 encoded
$stream = $track->getBase64();

//output it
header('Content-Length: '.$stream);
$api->output(200, $stream);
