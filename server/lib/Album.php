<?php

/**
 * Album definition.
 *
 * The album is a songs collection and is linked to an artist
 *
 * @version 1.1.0
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
     * @var string MusicBrainz release identifier (36 characters)
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
        require_once $_SERVER['DOCUMENT_ROOT'].'/server/lib/DatabaseConnection.php';
        $connection = new DatabaseConnection();
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
                //if there is a valid MBID, try to get cover
                if (strlen($this->mbid) == 36) {
                    $this->callCoverArtArchive();
                }
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
        require_once $_SERVER['DOCUMENT_ROOT'].'/server/lib/DatabaseConnection.php';
        $connection = new DatabaseConnection();
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
        $query = $connection->prepare('SELECT `album`.*, `artist`.`name` AS `artistName`, `cover`.`albumId` AS `coverId` FROM `artist`, `album`LEFT JOIN `cover` ON `album`.`id`=`cover`.`albumId` AND `cover`.`status` = 1 WHERE `album`.`artist`=`artist`.`id`'.$sqlCondition.' LIMIT 1;');
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
     * Validate a album object with provided informations.
     *
     * @param object $album Album object to validate
     * @param string $error The returned error message
     *
     * @return bool True if the album object provided is correct
     */
    public function validateModel($album, &$error)
    {
        $error = '';
        if ($album === null) {
            $error = 'invalid resource';
            //return false and detailed error message
            return false;
        }
        //unset sub-objects
        unset($album->artist, $album->tracks);
        //iterate on each object attributes to set object
        foreach ($this as $key => $value) {
            if (property_exists($album, $key)) {
                //get provided attribute
                $this->$key = $album->$key;
            }
        }
        //check mandatory attributes
        if (!is_int($this->id)) {
            $error = 'integer must be provided in id attribute';
            //return false and detailed error message
            return false;
        }
        if (!is_string($this->name) || $this->name === '') {
            $error = 'string must be provided in name attribute';
            //return false and detailed error message
            return false;
        }
        if (isset($this->year)) {
            $this->year = intval($this->year);
            if ($this->year === 0) {
                $this->year = null;
            }
            if ($this->year !== null && $this->year < 1800) {
                $error = 'Integer with a valid value must be provided in year attribute';
                //return false and detailed error message
                return false;
            }
        }
        if (isset($this->country)) {
            if (!is_string($this->country) || $this->country === '') {
                $this->country = null;
            }
            if ($this->country !== null && strlen($this->country) !== 2) {
                $error = 'String with a valid format must be provided in country attribute';
                //return false and detailed error message
                return false;
            }
        }
        if (isset($this->mbid)) {
            if (!is_string($this->mbid) || $this->mbid === '') {
                $this->mbid = null;
            }
            if ($this->mbid !== null && strlen($this->mbid) !== 36) {
                $error = 'String with a valid format must be provided in MBID attribute';
                //return false and detailed error message
                return false;
            }
        }
        //Album is valid
        return true;
    }

    /**
     * Update album with provided informations.
     *
     * @param string $error The returned error message
     *
     * @return bool True if the album is updated
     */
    public function update(&$error)
    {
        $error = '';
        if (is_int($this->id)) {
            require_once $_SERVER['DOCUMENT_ROOT'].'/server/lib/DatabaseConnection.php';
            $connection = new DatabaseConnection();
            $query = $connection->prepare('UPDATE `album` SET `name`=:name, `year`=:year, `country`=:country, `mbid`=:mbid WHERE `id`=:id LIMIT 1;');
            $query->bindValue(':id', $this->id, PDO::PARAM_INT);
            $query->bindValue(':name', $this->name, PDO::PARAM_STR);
            $query->bindValue(':year', $this->year, PDO::PARAM_INT);
            $query->bindValue(':country', $this->country, PDO::PARAM_STR);
            $query->bindValue(':mbid', $this->mbid, PDO::PARAM_STR);
            if ($query->execute()) {
                //return true to indicate a successful album update
                return true;
            }
            $error = $query->errorInfo()[2];
        }
        //return false to indicate an error occurred while reading the user
        return false;
    }

    /**
     * Get album tracks.
     *
     * @return mixed Array of album tracks or false on failure
     */
    public function getTracks()
    {
        require_once $_SERVER['DOCUMENT_ROOT'].'/server/lib/Track.php';
        require_once $_SERVER['DOCUMENT_ROOT'].'/server/lib/DatabaseConnection.php';
        $connection = new DatabaseConnection();
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
            require_once $_SERVER['DOCUMENT_ROOT'].'/server/lib/DatabaseConnection.php';
            $connection = new DatabaseConnection();
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
        }
        unset($album->coverId);
        //create artist structure
        $artist = new stdClass();
        $artist->id = intval($album->artist);
        $artist->label = $album->artistName;
        unset($album->artist, $album->artistName);
        $album->artist = $artist;
        //return structured album
        return $album;
    }

    /**
     * Populate album MBID.
     *
     * @return bool Result
     */
    private function getMBID()
    {
        require_once $_SERVER['DOCUMENT_ROOT'].'/server/lib/DatabaseConnection.php';
        $connection = new DatabaseConnection();
        $query = $connection->prepare('SELECT `mbid` FROM `album` WHERE `id` = :id;');
        $query->bindValue(':id', $this->id, PDO::PARAM_INT);
        $query->execute();
        $this->mbid = $query->fetchColumn();
        //return true if there is a MBID
        return $this->mbid !== false && !is_null($this->mbid);
    }

    /**
     * Call Cover Art Archive wrapper and store image.
     *
     * @return bool Result
     */
    private function callCoverArtArchive()
    {
        require_once $_SERVER['DOCUMENT_ROOT'].'/server/lib/CoverArtArchive.php';
        $CoverArtArchive = new CoverArtArchive();
        $stream = $CoverArtArchive->getCoverImage($this->mbid);
        if ($stream !== false) {
            //store image
            $this->storeCover($stream);
            //returns image
            return $stream;
        }
        //returns Cover Art Archive search has encountered an error
        return false;
    }

    /**
     * Store cover image.
     *
     * @param string $stream Image data
     *
     * @return bool Result
     */
    private function storeCover($stream)
    {
        //default values
        $height = null;
        $width = null;
        $mime = 'image/jpeg';
        //get image informations
        $size = getimagesizefromstring($stream);
        if ($size !== false) {
            $height = $size[0];
            $width = $size[1];
            $mime = $size['mime'];
        }
        //insert in database
        require_once $_SERVER['DOCUMENT_ROOT'].'/server/lib/DatabaseConnection.php';
        $connection = new DatabaseConnection();
        $query = $connection->prepare('INSERT INTO `cover` (`albumId`, `status`, `width`, `height`, `mime`, `image`) VALUES ( :albumId, :status, :width, :height, :mime, :image);');
        $query->bindValue(':albumId', $this->id,  PDO::PARAM_INT);
        $query->bindValue(':status',  1,          PDO::PARAM_INT);
        $query->bindValue(':width',   $width,     PDO::PARAM_INT);
        $query->bindValue(':height',  $height,    PDO::PARAM_INT);
        $query->bindValue(':mime',    $mime,      PDO::PARAM_STR);
        $query->bindValue(':image',   $stream,    PDO::PARAM_STR);
        //return result
        return $query->execute();
    }

    /**
     * Delete album cover.
     *
     * @return bool Result
     */
    public function deleteCoverImage()
    {
        require_once $_SERVER['DOCUMENT_ROOT'].'/server/lib/DatabaseConnection.php';
        $connection = new DatabaseConnection();
        $query = $connection->prepare('DELETE FROM `cover` WHERE `albumId` = :albumId AND `status` = 1;');
        $query->bindValue(':albumId', $this->id,  PDO::PARAM_INT);
        //return result
        return $query->execute();
    }

    /**
     * Get album cover.
     *
     * @return bool|string Stream of the cover image or false on failure
     */
    public function getCoverImage()
    {
        //check if there is already a cover
        require_once $_SERVER['DOCUMENT_ROOT'].'/server/lib/DatabaseConnection.php';
        $connection = new DatabaseConnection();
        $query = $connection->prepare('SELECT `image` FROM `cover` WHERE `albumId` = :albumId AND `status` = 1;');
        $query->bindValue(':albumId', $this->id, PDO::PARAM_INT);
        $query->execute();
        $image = $query->fetchColumn();
        if ($image !== false) {
            //image found in database
            return $image;
        }
        //not found in database, try to get it from Cover Art Archive if there is a MBID
        if ($this->getMBID()) {
            return $this->callCoverArtArchive();
        }
        //returns there is no MBID for this album
        return false;
    }
}
