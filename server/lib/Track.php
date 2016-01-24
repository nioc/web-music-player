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
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $id;                             // int(11) not_null primary_key unsigned auto_increment
    public $file;                           // string(1024) multiple_key
    public $album;                          // int(11) not_null multiple_key unsigned
    public $year;                           // int(4) not_null unsigned
    public $artist;                         // int(11) not_null multiple_key unsigned
    public $title;                          // string(255) multiple_key
    public $bitrate;                        // int(8) not_null unsigned
    public $rate;                           // int(8) not_null unsigned
    public $mode;                           // string(3) enum
    public $size;                           // int(11) unsigned
    public $time;                           // int(5) unsigned
    public $track;                          // int(5) unsigned
    public $mbid;                           // string(36)
    public $updateTime;                     // int(11) unsigned
    public $additionTime;                   // int(11) unsigned
    public $composer;                       // string(256)

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
    public function __construct()
    {
    }

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
        $query = $connection->prepare('SELECT `track`.`id`, `track`.`title`, `track`.`artist`, `track`.`file`, `track`.`album` FROM `track` WHERE 1=1 ORDER BY `additionTime` DESC;');
        if ($query->execute()) {
            $this->tracks = $query->fetchAll(PDO::FETCH_CLASS);

            return true;
        } else {
            return false;
        }
    }
}
