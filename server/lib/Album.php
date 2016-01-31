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
     * Inserts an album in database.
     *
     * @return bool True on success or false on failure
     */
    private function insert()
    {
        global $connection;
        include_once $_SERVER['DOCUMENT_ROOT'].'/server/lib/Connection.php';
        $query = $connection->prepare('INSERT INTO `wmp`.`album` (`name`, `mbid`, `artist`, `year`, `disk`, `country`, `mbidGroup`) VALUES ( :name, :mbid, :artist, :year, :disk, :country, :mbidGroup);');
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
        $query = $connection->prepare('SELECT `album`.*, `artist`.`name` AS `artistName` FROM `album`, `artist` WHERE `album`.`artist`=`artist`.`id`'.$sqlCondition.' LIMIT 1;');
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
}
