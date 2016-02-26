<?php

/**
 * Album definition.
 *
 * The album is a songs collection and is linked to an artist
 *
 * @version 1.0.0
 *
 * @internal
 */
class Album
{
    /**
     * @var int Album identifier
     */
    public $id;
    /**
     * @var string Album name
     */
    public $name;
    /**
     * @var string MusicBrainz release identifier
     */
    public $mbid;
    /**
     * @var int Album artist
     */
    public $artist;
    /**
     * @var int Year when the album was released
     */
    public $year;
    /**
     * @var int Disk number
     */
    public $disk;
    /**
     * @var string Country where the album is released
     */
    public $country;
    /**
     * @var string MusicBrainz release group identifier
     */
    public $mbidGroup;
    /**
     * @var string Album artist name
     */
    public $artistName;
    /**
     * @var array Tracks of the album
     */
    public $tracks;

    /**
     * Initializes an Album object with his identifier.
     *
     * @param int $id Album identifier
     */
    public function __construct($id = null)
    {
        if ($id !== null) {
            $this->id = intval($id);
        }
    }

    /**
     * Inserts an album in database.
     *
     * @return bool True on success or false on failure
     */
    private function insert()
    {
        global $connection;
        include_once $_SERVER['DOCUMENT_ROOT'].'/server/lib/Connection.php';
        $query = $connection->prepare('INSERT INTO `album` (`name`, `mbid`, `artist`, `year`, `disk`, `country`, `mbidGroup`) VALUES ( :name, :mbid, :artist, :year, :disk, :country, :mbidGroup);');
        $query->bindValue(':name',      $this->name,      PDO::PARAM_STR);
        $query->bindValue(':mbid',      $this->mbid,      PDO::PARAM_STR);
        $query->bindValue(':artist',    $this->artist,    PDO::PARAM_INT);
        $query->bindValue(':year',      $this->year,      PDO::PARAM_INT);
        $query->bindValue(':disk',      $this->disk,      PDO::PARAM_INT);
        $query->bindValue(':country',   $this->country,   PDO::PARAM_STR);
        $query->bindValue(':mbidGroup', $this->mbidGroup, PDO::PARAM_STR);
        if ($query->execute()) {
            $this->id = $connection->lastInsertId();
            //returns insertion was successfully processed
            return true;
        }
        //returns insertion has encountered an error
        return false;
    }

    /**
     * Inserts an album in database if not already existing.
     *
     * @param string $name   Album name
     * @param string $mbid   MusicBrainz release identifier
     * @param string $artist Main artist
     *
     * @return bool True on success or false on failure
     */
    public function insertIfRequired($name, $mbid, $artist)
    {
        $parameters['name'] = $name;
        $parameters['mbid'] = $mbid;
        if (!$this->populate($parameters)) {
            $this->name = $name;
            $this->mbid = $mbid;
            $this->artist = $artist;
            if ($this->insert()) {
                //returns album identifier
                return $this->id;
            }
            //return album insertion has encountered an error
            return false;
        }
        //returns album identifier
        return $this->id;
    }

    /**
     * Populates an Album.
     *
     * @param array $parameters Requested parameters.
     *
     * @return mixed Album identifier or false on failure
     */
    public function populate($parameters)
    {
        global $connection;
        include_once $_SERVER['DOCUMENT_ROOT'].'/server/lib/Connection.php';
        //handle requested parameters
        $sqlCondition = '';
        foreach ($parameters as $parameter => $value) {
            if (isset($value)) {
                switch ($parameter) {
                    case 'id' :
                        $sqlCondition .= ' AND `album`.`id` = :id';
                        break;
                    case 'name' :
                        $sqlCondition .= ' AND `album`.`name` = :name';
                        break;
                    case 'mbid' :
                        $sqlCondition .= ' AND `album`.`mbid` = :mbid';
                        break;
                }
            }
        }
        //prepare query
        $query = $connection->prepare('SELECT `album`.*, `artist`.`name` AS `artistName`, `cover`.`id` AS `coverId` FROM `artist`, `album`LEFT JOIN `cover` ON `album`.`id`=`cover`.`albumId` AND `cover`.`status` = 1 WHERE `album`.`artist`=`artist`.`id`'.$sqlCondition.' LIMIT 1;');
        //add query criteria value
        foreach ($parameters as $parameter => $value) {
            if (isset($value)) {
                switch ($parameter) {
                    case 'id' :
                        $query->bindValue(':id', $value, PDO::PARAM_INT);
                        break;
                    case 'name' :
                        $query->bindValue(':name', $value, PDO::PARAM_STR);
                        break;
                    case 'mbid' :
                        $query->bindValue(':mbid', $value, PDO::PARAM_STR);
                        break;
                }
            }
        }
        //execute query
        $query->setFetchMode(PDO::FETCH_INTO, $this);
        if ($query->execute() && $query->fetch()) {
            //returns the album object was successfully fetched
            return true;
        }
        //returns the album is not known or database was not reachable
        return false;
    }

    /**
     * Get album tracks.
     *
     * @return mixed Array of album tracks or false on failure
     */
    public function getTracks()
    {
        global $connection;
        include_once $_SERVER['DOCUMENT_ROOT'].'/server/lib/Connection.php';
        include_once $_SERVER['DOCUMENT_ROOT'].'/server/lib/Track.php';
        $query = $connection->prepare('SELECT `track`.`id`, `track`.`track`, `track`.`title`, `track`.`time`, `track`.`artist`, `artist`.`name` AS `artistName` FROM `track` INNER JOIN `artist` ON `artist`.`id` = `track`.`artist` WHERE `track`.`album` = :albumId  ORDER BY `track`.`track` ASC;');
        $query->bindValue(':albumId', $this->id, PDO::PARAM_INT);
        if ($query->execute()) {
            $this->tracks = $query->fetchAll(PDO::FETCH_CLASS);
            foreach ($this->tracks as $track) {
                $trackStructured = new Track();
                $track = $trackStructured->structureData($track);
            }
            //return album tracks
            return $this->tracks;
        }
        //returns database was not reachable
        return false;
    }

    /**
     * Delete an album from database.
     *
     * @return bool True on success or false on failure
     */
    public function delete()
    {
        if (is_int($this->id)) {
            global $connection;
            include_once $_SERVER['DOCUMENT_ROOT'].'/server/lib/Connection.php';
            $query = $connection->prepare('DELETE FROM `album` WHERE `id` = :id LIMIT 1;');
            $query->bindValue(':id', $this->id, PDO::PARAM_INT);
            //returns deletion result
            return $query->execute();
        }
        //returns an error if no identifier was provided
        return false;
    }

    /**
     * Return structured album.
     *
     * @return object A public version of album
     */
    public function structureData()
    {
        //create album structure
        $album = $this;
        $album->id = (int) $album->id;
        if (isset($album->year)) {
            $album->year = (int) $album->year;
        }
        if (isset($album->disk)) {
            $album->disk = (int) $album->disk;
        }
        //add path to cover
        if (isset($album->coverId)) {
            $album->coverPath = '/server/covers/'.$album->coverId.'.jpeg';
            unset($album->coverId);
        }
        //create artist structure
        $artist = new stdClass();
        $artist->id = (int) $album->artist;
        $artist->label = $album->artistName;
        unset($album->artist, $album->artistName);
        $album->artist = $artist;
        //return structured album
        return $album;
    }
}
