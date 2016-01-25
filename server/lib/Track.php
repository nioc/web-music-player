<?php

/**
 * Track definition.
 *
 * The track is a single song and is linked to the artist and the album
 *
 * @version 1.0.0
 *
 * @internal
 */
class Track
{
    /**
     * @var int Track identifier
     */
    public $id;
    /**
     * @var string Track path and filename where the file is stored
     */
    public $file;
    /**
     * @var int Album identifier
     */
    public $album;
    /**
     * @var int Year
     */
    public $year;
    /**
     * @var int Artist identifier
     */
    public $artist;
    /**
     * @var string Track title
     */
    public $title;
    /**
     * @var int Bitrate
     */
    public $bitrate;
    /**
     * @var int Main rating
     */
    public $rate;
    /**
     * @var string Encoding bitrate mode, possible values are 'abr','vbr','cbr'
     */
    public $mode;
    /**
     * @var int File size
     */
    public $size;
    /**
     * @var int Time in seconds
     */
    public $time;
    /**
     * @var int Track number
     */
    public $track;
    /**
     * @var string MusicBrainz identifier
     */
    public $mbid;
    /**
     * @var int Last modification timestamp
     */
    public $updateTime;
    /**
     * @var int Adding timestamp
     */
    public $additionTime;
    /**
     * @var string Composer
     */
    public $composer;

    /**
     * Returns the filename for a specific track.
     *
     * @param string $id track identifier
     *
     * @return string the filename on success, or false on error.
     */
    public function getFile($id = null)
    {
        if (!is_null($id)) {
            $this->id = $id;
        }
        global $connection;
        include_once $_SERVER['DOCUMENT_ROOT'].'/server/lib/Connection.php';
        $query = $connection->prepare('SELECT `file` FROM `track` WHERE `id`=:id LIMIT 1;');
        $query->bindValue(':id', $this->id, PDO::PARAM_INT);
        $query->execute();
        $query->setFetchMode(PDO::FETCH_INTO, $this);
        if ($query->fetch()) {
            return $this->file;
        } else {
            return false;
        }
    }
    /**
     * Returns a track object with artist and album structures.
     *
     * @param object $track A track object from database reading
     *
     * @return object Track structured
     */
    public function structureData($track)
    {
        //create album structure
        $album = new stdClass();
        $album->id = $track->album;
        $album->label = $track->albumLabel;
        unset($track->album, $track->albumLabel);
        $track->album = $album;

        //create artist structure
        $artist = new stdClass();
        $artist->id = $track->artist;
        $artist->label = $track->artistLabel;
        unset($track->artist, $track->artistLabel);
        $track->artist = $artist;

        return $track;
    }
}

/**
 * Set of tracks.
 *
 * This is a set of tracks (library, search, ...)
 *
 * @version 1.0.0
 *
 * @internal
 */
class Tracks
{
    /**
     * @var array Tracks included in the library
     */
    public $tracks;

    /**
     * Returns all the library tracks.
     *
     * @return bool true if the database read is ok, false otherwise
     */
    public function get()
    {
        global $connection;
        include_once $_SERVER['DOCUMENT_ROOT'].'/server/lib/Connection.php';
        $query = $connection->prepare('SELECT `track`.`id`, `track`.`title`, `track`.`artist`, `artist`.`name` AS `artistLabel`, `track`.`album`, `album`.`name` AS `albumLabel`, CONCAT(\'/stream/\',`track`.`id`) AS `file` FROM `track`, `album`, `artist` WHERE `track`.`artist`=`artist`.`id` AND `track`.`album`=`album`.`id` ORDER BY `additionTime` DESC;');
        if ($query->execute()) {
            $this->tracks = $query->fetchAll(PDO::FETCH_CLASS);
            foreach ($this->tracks as $track) {
                $trackStructured = new Track();
                $track = $trackStructured->structureData($track);
            }

            return true;
        } else {
            return false;
        }
    }
}
