<?php

/**
 * Cover Art Archive wrapper.
 *
 * The Cover Art Archive is a joint project between the Internet Archive and MusicBrainz, whose goal is to make cover art images available to everyone on the Internet in an organised and convenient way. See https://coverartarchive.org/
 *
 * @version 1.1.0
 *
 * @internal
 */
class CoverArtArchive
{
    /**
     * @var string URL base for requesting MusicBrainz services
     */
    private $endpoint = 'http://coverartarchive.org/';
    /**
     * @var string Last error message encountered
     */
    public $errorMessage = '';

    /**
     * Provide album cover URL based on MBID.
     *
     * @param string $mbid MusicBrainz release identifier
     *
     * @return string URL of the cover image
     */
    public function getCoverURL($mbid)
    {
        $this->endpoint = $this->endpoint.'release/'.$mbid.'/front';
        //returns endpoint
        return $this->endpoint;
    }

    /**
     * Get album cover image.
     *
     * @param string $mbid MusicBrainz release identifier
     *
     * @return bool|string Stream of the cover image or false on failure
     */
    public function getCoverImage($mbid)
    {
        $this->getCoverURL($mbid);
        $results = $this->executeCall();
        if ($results !== false) {
            return $results;
        }
        //returns an error during request
        return false;
    }
    /**
     * Execute a HTTP request to Cover Art Archive server, errorMessage attribute is set in case of error.
     *
     * @return bool|string MusicBrainz informations or false on failure
     */
    private function executeCall()
    {
        //create a HTTP request object and call
        include_once $_SERVER['DOCUMENT_ROOT'].'/server/lib/HttpRequest.php';
        $request = new HttpRequest();
        if (!$request->execute($this->endpoint, 'GET', null, null, null, $response, $responseHeaders)) {
            $this->errorMessage = 'Error during request';
            //error during HTTP request
            return false;
        }
        //return object
        return $response;
    }
}
