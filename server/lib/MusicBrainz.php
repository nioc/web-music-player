<?php

/**
 * MusicBrainz wrapper.
 *
 * MusicBrainz is an open music encyclopedia that collects music metadata and makes it available to the public. See https://musicbrainz.org/
 *
 * @version 1.0.0
 *
 * @internal
 */
class MusicBrainz
{
    /**
     * @var string URL base for requesting MusicBrainz services
     */
    private $endpoint = 'http://musicbrainz.org/ws/';
    /**
     * @var string Requested format for output
     */
    private $format;
    /**
     * @var array Query parameters to supply in request
     */
    private $queryParameters = array();
    /**
     * @var string Last error message encountered
     */
    public $errorMessage = '';
    /**
     * Apply the choice for output format.
     *
     * @param string $format Requested format (json is default value)
     */
    private function setRequestedFormat($format = 'json')
    {
        $this->format = $format;
        $this->queryParameters['fmt'] = $format;
    }
    /**
     * Execute a HTTP request to MusicBrainz server, errorMessage attribute is set in case of error.
     *
     * @return bool|string MusicBrainz informations or false on failure
     */
    private function executeCall()
    {
        //request JSON output format
        $this->setRequestedFormat('json');
        //create a HTTP request object and call
        require_once $_SERVER['DOCUMENT_ROOT'].'/server/lib/HttpRequest.php';
        $request = new HttpRequest();
        if (!$request->execute($this->endpoint, 'GET', null, null, $this->queryParameters, $response, $responseHeaders)) {
            $this->errorMessage = 'Error during request';
            //error during HTTP request
            return false;
        }
        //decode response
        if ($this->format === 'json') {
            $response = json_decode($response);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $this->errorMessage = 'Invalid response received';
                //error on JSON parsing
                return false;
            }
            //return object
            return $response;
        }
        //return false by default
        return false;
    }
    /**
     * Compare the score of 2 objects (artists or releases).
     *
     * @param object $a First object
     * @param object $b Second object
     *
     * @return number The result of the compare
     */
    private static function orderByScore($a, $b)
    {
        if ($a->score === $b->score) {
            //score are equals
            return 0;
        }
        //return 1 if A is lower than B
        return (intval($a->score) < intval($b->score)) ? 1 : -1;
    }
    /**
     * Search an artist by his name.
     *
     * @param string $artistName The artist name requested
     *
     * @return bool|array Artists collection or false on failure
     */
    public function searchArtistByName($artistName)
    {
        //set valid request
        $this->endpoint .= '2/artist/';
        $this->queryParameters['query'] = "artist:$artistName;";
        //execute the request
        $response = $this->executeCall();
        if ($response === false) {
            //error on response
            return false;
        }
        if ($response->count === 0) {
            $this->errorMessage = 'No artist found';
            //no artist matched
            return array();
        }
        //order by score
        $musicBrainzArtists = $response->artists;
        usort($musicBrainzArtists, array('MusicBrainz', 'orderByScore'));
        //get high score and apply a 90% ratio
        $thresholdScore = $musicBrainzArtists[0]->score * 0.9;
        $artists = array();
        //transform MusicBrainz artists into WMP artists
        require_once $_SERVER['DOCUMENT_ROOT'].'/server/lib/Artist.php';
        foreach ($musicBrainzArtists as $musicBrainzArtist) {
            if ($musicBrainzArtist->score >= $thresholdScore) {
                $artist = new Artist();
                $artist->mbid = $musicBrainzArtist->id;
                $artist->name = $musicBrainzArtist->name;
                if (property_exists($musicBrainzArtist, 'disambiguation')) {
                    $artist->summary = $musicBrainzArtist->disambiguation;
                }
                if (property_exists($musicBrainzArtist, 'country')) {
                    $artist->country = $musicBrainzArtist->country;
                }
                unset($artist->id, $artist->tracks);
                array_push($artists, $artist);
            }
        }
        //return artists
        return $artists;
    }
    /**
     * Search an album by his title.
     *
     * @param string $albumTitle
     *
     * @return bool|array Albums collection or false on failure
     */
    public function searchAlbumByTitle($albumTitle)
    {
        //set valid request
        $this->endpoint .= '2/release/';
        $this->queryParameters['query'] = "release:$albumTitle;";
        //execute the request
        $response = $this->executeCall();
        if ($response === false) {
            //error on response
            return false;
        }
        if ($response->count === 0) {
            $this->errorMessage = 'No album found';
            //no release matched
            return array();
        }
        //order by score
        $musicBrainzReleases = $response->releases;
        usort($musicBrainzReleases, array('MusicBrainz', 'orderByScore'));
        //get high score and apply a 90% ratio
        $thresholdScore = $musicBrainzReleases[0]->score * 0.9;
        $albums = array();
        //transform MusicBrainz albums into WMP albums
        require_once $_SERVER['DOCUMENT_ROOT'].'/server/lib/Album.php';
        foreach ($musicBrainzReleases as $musicBrainzRelease) {
            if ($musicBrainzRelease->score >= $thresholdScore) {
                $album = new Album();
                $album->mbid = $musicBrainzRelease->id;
                $album->name = $musicBrainzRelease->title;
                if (property_exists($musicBrainzRelease, 'date')) {
                    $album->year = substr($musicBrainzRelease->date, 0, 4);
                }
                if (property_exists($musicBrainzRelease, 'country')) {
                    $album->country = $musicBrainzRelease->country;
                }
                if (property_exists($musicBrainzRelease, 'artist-credit') && count($musicBrainzRelease->{'artist-credit'}) > 0) {
                    $artists = $musicBrainzRelease->{'artist-credit'};
                    $album->artistName = $artists[0]->artist->name;
                }
                $album->mbidGroup = $musicBrainzRelease->{'release-group'}->id;
                $status = '?';
                $packaging = '?';
                $type = '?';
                $media = '?';
                if (property_exists($musicBrainzRelease, 'status')) {
                    $status = $musicBrainzRelease->status;
                }
                if (property_exists($musicBrainzRelease, 'packaging')) {
                    $packaging = $musicBrainzRelease->packaging;
                }
                if (property_exists($musicBrainzRelease->{'release-group'}, 'primary-type')) {
                    $type = $musicBrainzRelease->{'release-group'}->{'primary-type'};
                }
                if (count($musicBrainzRelease->media) > 0 && property_exists($musicBrainzRelease->media[0], 'format')) {
                    $media = $musicBrainzRelease->media[0]->format;
                }
                $album->description = $type.' - '.$status.' - '.$packaging.' - '.$media;
                //propose a cover URL with Cover Art Archive
                require_once $_SERVER['DOCUMENT_ROOT'].'/server/lib/CoverArtArchive.php';
                $coverArtArchive = new CoverArtArchive();
                $album->coverPath = $coverArtArchive->getCoverURL($musicBrainzRelease->id);
                $album->structureData();
                unset($album->id, $album->tracks, $album->disk);
                array_push($albums, $album);
            }
        }
        //return albums
        return $albums;
    }
}
