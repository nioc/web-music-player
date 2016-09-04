<?php

/**
 * Artist definition.
 *
 * Artist is a single person or band who wrote or performed a song or album (almost obvious, I know ...)
 *
 * @version 1.1.0
 *
 * @internal
 */
class Artist
{
    /**
     * @var int Artist identifier
     */
    public $id;
    /**
     * @var string Artist name
     */
    public $name;
    /**
     * @var string MusicBrainz artist identifier
     */
    public $mbid;
    /**
     * @var string Biography of the artist
     */
    public $summary;
    /**
     * @var string Country where the artist came from
     */
    public $country;
    /**
     * @var array Tracks of the artist
     */
    public $tracks;

    /**
     * Initializes an Artist object with his identifier.
     *
     * @param int $id Artist identifier
     */
    public function __construct($id = null)
    {
        if ($id !== null) {
            $this->id = intval($id);
        }
    }

    /**
     * Inserts an artist in database.
     *
     * @return bool True on success or false on failure
     */
    public function insert()
    {
        require_once $_SERVER['DOCUMENT_ROOT'].'/server/lib/DatabaseConnection.php';
        $connection = new DatabaseConnection();
        $query = $connection->prepare('INSERT INTO `artist` (`name`, `mbid`, `summary`, `country`) VALUES (:name, :mbid, :summary, :country);');
        $query->bindValue(':name',    $this->name,    PDO::PARAM_STR);
        $query->bindValue(':mbid',    $this->mbid,    PDO::PARAM_STR);
        $query->bindValue(':summary', $this->summary, PDO::PARAM_STR);
        $query->bindValue(':country', $this->country, PDO::PARAM_STR);
        if ($query->execute()) {
            $this->id = $connection->lastInsertId();
            //returns insertion was successfully processed
            return true;
        }
        //returns insertion has encountered an error
        return false;
    }

    /**
     * Inserts an artist in database if not already existing.
     *
     * @param string $name Artist name
     * @param string $mbid MusicBrainz artist identifier
     *
     * @return mixed Artist identifier or false on failure
     */
    public function insertIfRequired($name, $mbid)
    {
        $parameters['name'] = $name;
        $parameters['mbid'] = $mbid;
        if (!$this->populate($parameters)) {
            $this->name = $name;
            $this->mbid = $mbid;
            if ($this->insert()) {
                //returns artist identifier
                return $this->id;
            }
            //return artist insertion has encountered an error
            return false;
        }
        //returns artist identifier
        return $this->id;
    }

    /**
     * Populates an Artist.
     *
     * @param array $parameters Requested parameters.
     *
     * @return bool true if the database read is ok and an artist is returned, false otherwise
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
                        $sqlCondition .= ' AND `artist`.`id` = :id';
                        break;
                    case 'name' :
                        $sqlCondition .= ' AND `artist`.`name` = :name';
                        break;
                    case 'mbid' :
                        $sqlCondition .= ' AND `artist`.`mbid` = :mbid';
                        break;
                }
            }
        }
        //prepare query
        $query = $connection->prepare('SELECT `artist`.* FROM `artist` WHERE 1'.$sqlCondition.' LIMIT 1;');
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
            //returns the artist object was successfully fetched
            return true;
        }
        //returns the artist is not known or database was not reachable
        return false;
    }

    /**
     * Validate a artist object with provided informations.
     *
     * @param object $artist Artist object to validate
     * @param string $error  The returned error message
     *
     * @return bool True if the artist object provided is correct
     */
    public function validateModel($artist, &$error)
    {
        $error = '';
        if ($artist === null) {
            $error = 'invalid resource';
            //return false and detailed error message
            return false;
        }
        //iterate on each object attributes to set object
        foreach ($this as $key => $value) {
            if (property_exists($artist, $key)) {
                //get provided attribute
                $this->$key = $artist->$key;
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
        if (isset($this->summary)) {
            if (!is_string($this->summary) || $this->summary === '') {
                $this->summary = null;
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
        //Artist is valid
        return true;
    }

    /**
     * Update artist with provided informations.
     *
     * @param string $error The returned error message
     *
     * @return bool True if the artist is updated
     */
    public function update(&$error)
    {
        $error = '';
        if (is_int($this->id)) {
            require_once $_SERVER['DOCUMENT_ROOT'].'/server/lib/DatabaseConnection.php';
            $connection = new DatabaseConnection();
            $query = $connection->prepare('UPDATE `artist` SET `name`=:name, `summary`=:summary, `country`=:country, `mbid`=:mbid WHERE `id`=:id LIMIT 1;');
            $query->bindValue(':id', $this->id, PDO::PARAM_INT);
            $query->bindValue(':name', $this->name, PDO::PARAM_STR);
            $query->bindValue(':summary', $this->summary, PDO::PARAM_STR);
            $query->bindValue(':country', $this->country, PDO::PARAM_STR);
            $query->bindValue(':mbid', $this->mbid, PDO::PARAM_STR);
            if ($query->execute()) {
                //return true to indicate a successful artist update
                return true;
            }
            $error = $query->errorInfo()[2];
        }
        //return false to indicate an error occurred while reading the user
        return false;
    }

    /**
     * Get artist tracks.
     *
     * @return mixed Array of artist tracks or false on failure
     */
    public function getTracks()
    {
        require_once $_SERVER['DOCUMENT_ROOT'].'/server/lib/Track.php';
        require_once $_SERVER['DOCUMENT_ROOT'].'/server/lib/DatabaseConnection.php';
        $connection = new DatabaseConnection();
        $query = $connection->prepare('SELECT `track`.`id`, `track`.`track`, `track`.`title`, `track`.`time`, `track`.`year`, `track`.`album`, `album`.`name` AS `albumName` FROM `track` INNER JOIN `album` ON `album`.`id` = `track`.`album` WHERE `track`.`artist` = :artistId  ORDER BY `track`.`year` ASC;');
        $query->bindValue(':artistId', $this->id, PDO::PARAM_INT);
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
     * Delete an artist from database.
     *
     * @return bool True on success or false on failure
     */
    public function delete()
    {
        if (is_int($this->id)) {
            require_once $_SERVER['DOCUMENT_ROOT'].'/server/lib/DatabaseConnection.php';
            $connection = new DatabaseConnection();
            $query = $connection->prepare('DELETE FROM `artist` WHERE `id` = :id LIMIT 1;');
            $query->bindValue(':id', $this->id, PDO::PARAM_INT);
            //returns deletion result
            return $query->execute();
        }
        //returns an error if no identifier was provided
        return false;
    }

    /**
     * Return structured artist.
     *
     * @return object A public version of artist
     */
    public function structureData()
    {
        //create artist structure
        $artist = $this;
        $artist->id = (int) $artist->id;
        //return structured artist
        return $artist;
    }
}
