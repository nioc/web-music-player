<?php
//manage cache browser : no response needed
if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE'])) {
    header('Last-Modified: '.$_SERVER['HTTP_IF_MODIFIED_SINCE'], true, 304);
    exit;
}
//get album id parameter
$id = isset($_GET['id']) ? filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT) : null;
if (is_null($id)) {
    //if id is not provided, return a 404 HTTP status
    header('HTTP/1.0 404 Not Found');
    exit;
}
include_once $_SERVER['DOCUMENT_ROOT'].'/server/lib/Album.php';
$album = new Album($id);
$image = $album->getCoverImage();
if ($image === false) {
    //if thumbnail is not ok, redirect on default image with a 302 HTTP status
    header('Location: /server/covers/default.png');
    exit;
}
//manage cache browser
header('Cache-Control: private, max-age=31536000, pre-check=31536000');
header('Pragma: private');
header('Expires: '.date('D, d M Y H:i:s', strtotime('365 day')).' GMT');
header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
header('Content-type: image/jpeg');
//output the image
echo $image;
