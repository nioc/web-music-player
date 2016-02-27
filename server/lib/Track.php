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
     * Initializes a Track object with his identifier.
     *
     * @param int $id Track identifier
     */
    public function __construct($id = null)
    {
        if ($id !== null) {
            $this->id = intval($id);
        }
    }

    /**
     * Populates a track.
     *
     * @return bool true if the database read is ok and track is returned, false otherwise
     */
    public function populate()
    {
        global $connection;
        include_once $_SERVER['DOCUMENT_ROOT'].'/server/lib/Connection.php';
        //prepare query
        $query = $connection->prepare('SELECT `track`.`id`, `track`.`track`, `track`.`title`, `track`.`file`, `track`.`time`, `track`.`year`, `track`.`artist`, `artist`.`name` AS `artistName`, `track`.`album`, `album`.`name` AS `albumName` FROM `track` INNER JOIN `artist` ON `artist`.`id` = `track`.`artist` INNER JOIN `album` ON `album`.`id` = `track`.`album` WHERE `track`.`id` = :id LIMIT 1;');
        $query->bindValue(':id', $this->id, PDO::PARAM_INT);
        //execute query
        $query->setFetchMode(PDO::FETCH_INTO, $this);
        if ($query->execute() && $query->fetch()) {
            //returns the track object was successfully fetched
            return true;
        }
        //returns the track is not known or database was not reachable
        return false;
    }

    /**
     * Validate a track object with provided informations.
     *
     * @param object $track Track object to validate
     * @param string $error The returned error message
     *
     * @return bool True if the track object provided is correct
     */
    public function validateModel($track, &$error)
    {
        $error = '';
        if ($track === null) {
            $error = 'invalid resource';
            //return false and detailed error message
            return false;
        }
        //unset sub-objects
        unset($track->artist, $track->album);
        //iterate on each object attributes to set object
        foreach ($this as $key => $value) {
            if (property_exists($track, $key)) {
                //get provided attribute
                $this->$key = $track->$key;
            }
        }
        //check mandatory attributes
        if (!is_int($this->id)) {
            $error = 'integer must be provided in id attribute';
            //return false and detailed error message
            return false;
        }
        if (!is_string($this->title) || $this->title === '') {
            $error = 'string must be provided in title attribute';
            //return false and detailed error message
            return false;
        }
        if (isset($this->track)) {
            $this->track = intval($this->track);
            if ($this->track === 0) {
                $this->track = null;
            }
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
        //Track is valid
        return true;
    }

    /**
     * Update track with provided informations.
     *
     * @param string $error The returned error message
     *
     * @return bool True if the track is updated
     */
    public function update(&$error)
    {
        $error = '';
        if (is_int($this->id)) {
            global $connection;
            include_once $_SERVER['DOCUMENT_ROOT'].'/server/lib/Connection.php';
            $query = $connection->prepare('UPDATE `track` SET `title`=:title, `track`=:track, `year`=:year WHERE `id`=:id LIMIT 1;');
            $query->bindValue(':id', $this->id, PDO::PARAM_INT);
            $query->bindValue(':title', $this->title, PDO::PARAM_STR);
            $query->bindValue(':track', $this->track, PDO::PARAM_INT);
            $query->bindValue(':year', $this->year, PDO::PARAM_INT);
            if ($query->execute()) {
                //return true to indicate a successful track update
                return true;
            }
            $error = $query->errorInfo()[2];
        }
        //return false to indicate an error occurred while reading the user
        return false;
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
            //returns file path
            return $this->file;
        }
        //return false to indicate file was not found
        return false;
    }
    /**
     * Inserts a track in database.
     *
     * @return bool True on success or false on failure
     */
    public function insert()
    {
        global $connection;
        include_once $_SERVER['DOCUMENT_ROOT'].'/server/lib/Connection.php';
        $query = $connection->prepare('INSERT INTO `track` (`file`, `album`, `year`, `artist`, `title`, `bitrate`, `rate`, `mode`, `size`, `time`, `track`, `mbid`, `updateTime`, `additionTime`, `composer`) VALUES (:file, :album, :year, :artist, :title, :bitrate, :rate, :mode, :size, :time, :track, :mbid, unix_timestamp(), unix_timestamp(), :composer);');
        $query->bindValue(':file',     $this->file,     PDO::PARAM_STR);
        $query->bindValue(':album',    $this->album,    PDO::PARAM_INT);
        $query->bindValue(':year',     $this->year,     PDO::PARAM_INT);
        $query->bindValue(':artist',   $this->artist,   PDO::PARAM_INT);
        $query->bindValue(':title',    $this->title,    PDO::PARAM_STR);
        $query->bindValue(':bitrate',  $this->bitrate,  PDO::PARAM_INT);
        $query->bindValue(':rate',     $this->rate,     PDO::PARAM_INT);
        $query->bindValue(':mode',     $this->mode,     PDO::PARAM_STR);
        $query->bindValue(':size',     $this->size,     PDO::PARAM_INT);
        $query->bindValue(':time',     $this->time,     PDO::PARAM_INT);
        $query->bindValue(':track',    $this->track,    PDO::PARAM_INT);
        $query->bindValue(':mbid',     $this->mbid,     PDO::PARAM_STR);
        $query->bindValue(':composer', $this->composer, PDO::PARAM_STR);
        if ($query->execute()) {
            $this->id = $connection->lastInsertId();
            //return true to indicate a successful track insertion
            return true;
        }
        //return false to indicate an error during track insertion
        return false;
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
        //change type
        if (property_exists($track, 'id')) {
            $track->id = intval($track->id);
        }
        if (property_exists($track, 'track') && $track->track !== null) {
            $track->track = intval($track->track);
        }
        if (property_exists($track, 'time') && $track->time !== null) {
            $track->time = intval($track->time);
        }
        if (property_exists($track, 'year') && $track->year !== null) {
            $track->year = intval($track->year);
        }
        //create album structure
        if (property_exists($track, 'album') && property_exists($track, 'albumName')) {
            $album = new stdClass();
            $album->id = intval($track->album);
            $album->label = $track->albumName;
            //add path to cover
            if (isset($track->coverId)) {
                $album->coverPath = '/server/covers/'.$track->coverId.'.jpeg';
            }
            unset($track->album, $track->albumName, $track->coverId);
            $track->album = $album;
        }
        //create artist structure
        if (property_exists($track, 'artist') && property_exists($track, 'artistName')) {
            $artist = new stdClass();
            $artist->id = intval($track->artist);
            $artist->label = $track->artistName;
            unset($track->artist, $track->artistName);
            $track->artist = $artist;
        }
        //return structured track
        return $track;
    }
    /**
     * Read ID3 tags from a file.
     *
     * @return array Result of reading
     */
    public function readId3()
    {
        if ($this->file != null) {
            include_once $_SERVER['DOCUMENT_ROOT'].'/server/lib/vendor/getid3/getid3/getid3.php';
            // Initialize getID3 engine
            $getID3 = new getID3();
            $getID3->setOption(array('encoding' => 'UTF-8'));
            $trackInfo = $getID3->analyze($this->file);
            getid3_lib::CopyTagsToComments($trackInfo);
            //return informations array
            return $trackInfo;
        }
        //return false to indicate ID3 was not read
        return false;
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
     * @var array Folders stored in server
     */
    public $folders = array();
    /**
     * @var array Files stored in server
     */
    public $files = array();
    /**
     * @var string File extensions handled by server
     */
    const EXTENSIONS = 'mp3|m4a';

    /**
     * Populates tracks collection with all library tracks matching criteria.
     *
     * @param array $parameters Requested parameters.
     *
     * @return bool true if the database read is ok, false otherwise
     */
    public function populateTracks($parameters)
    {
        global $connection;
        include_once $_SERVER['DOCUMENT_ROOT'].'/server/lib/Connection.php';
        //handle requested parameters
        $sqlCondition = '';
        foreach ($parameters as $parameter => $value) {
            if (isset($value)) {
                switch ($parameter) {
                    case 'trackTitle' :
                        $sqlCondition .= ' AND `track`.`title` LIKE :trackTitle';
                        break;
                    case 'artistName' :
                        $sqlCondition .= ' AND `artist`.`name` LIKE :artistName';
                        break;
                    case 'albumName' :
                        $sqlCondition .= ' AND `album`.`name` LIKE :albumName';
                        break;
                }
            }
        }
        //prepare query
        $query = $connection->prepare('SELECT `track`.`id`, `track`.`title`, `track`.`artist`, `artist`.`name` AS `artistName`, `track`.`album`, `album`.`name` AS `albumName`, CONCAT(\'/stream/\',`track`.`id`) AS `file`, `cover`.`id` AS `coverId` FROM `track`, `artist` ,`album` LEFT JOIN `cover` ON `album`.`id`=`cover`.`albumId` AND `cover`.`status` = 1 WHERE `track`.`artist`=`artist`.`id` AND `track`.`album`=`album`.`id`'.$sqlCondition.' ORDER BY `additionTime` DESC;');
        //add query criteria value
        foreach ($parameters as $parameter => $value) {
            if (isset($value)) {
                switch ($parameter) {
                    case 'trackTitle' :
                        $query->bindValue(':trackTitle', "%$value%", PDO::PARAM_STR);
                        break;
                    case 'artistName' :
                        $query->bindValue(':artistName', "%$value%", PDO::PARAM_STR);
                        break;
                    case 'albumName' :
                        $query->bindValue(':albumName', "%$value%", PDO::PARAM_STR);
                        break;
                }
            }
        }
        if ($query->execute()) {
            $this->tracks = $query->fetchAll(PDO::FETCH_CLASS);
            foreach ($this->tracks as $track) {
                $trackStructured = new Track();
                $track = $trackStructured->structureData($track);
            }
            //return true to indicate tracks was successfully read
            return true;
        }
        //return false to indicate an error occurred while reading tracks
        return false;
    }
    /**
     * Scans folder and subfolders and stores files found.
     *
     * @param string $folderPath       Folder to analyse
     * @param array  $parentSubfolders Returned list of subfolders
     */
    private function scanFolders($folderPath, &$parentSubfolders)
    {
        $slash = '/';
        if (is_dir($folderPath)) {
            if ($folderResource = opendir($folderPath)) {
                //initialize variables
                $folder = new stdClass();
                $folder->path = $folderPath;
                $folder->subfolders = array();
                $folder->files = array();
                $subfolders = array();
                while (false !== ($file = readdir($folderResource))) {
                    if ($file != '.' && $file != '..') {
                        $fullFilename = $folderPath.$file;
                        if (is_dir($fullFilename)) {
                            //found a directory, add to list for parsing later
                            $subfolders[] = $fullFilename;
                        } else {
                            //found a file, store it
                            if (preg_match('/^.+\.('.$this::EXTENSIONS.')$/i', $file)) {
                                array_push($this->files, $fullFilename);
                                array_push($folder->files, $file);
                            }
                        }
                    }
                }
                closedir($folderResource);
                sort($folder->files);
                //browse subfolders
                sort($subfolders);
                foreach ($subfolders as $subfolder) {
                    $this->scanFolders($subfolder.$slash, $folder->subfolders);
                }
                //add current folder and his childs to main array
                array_push($parentSubfolders, $folder);
            }
        }
    }

    /**
     * Scan the specified folder and subfolders.
     *
     * @param string $folder Root folder to scan
     */
    public function getFolders($folder)
    {
        $this->scanFolders($folder, $this->folders);
    }

    /**
     * Scan the specified folder and subfolders, insert in the library the files found.
     *
     * @param string $folder Root folder to scan
     *
     * @return array Tracks inserted
     */
    public function addFiles($folder)
    {
        include_once $_SERVER['DOCUMENT_ROOT'].'/server/lib/Artist.php';
        include_once $_SERVER['DOCUMENT_ROOT'].'/server/lib/Album.php';
        $result = array();
        $this->scanFolders($folder, $this->folders);
        foreach ($this->files as $file) {
            //reset timeout for 20 seconds before processing each file
            set_time_limit(20);
            $track = new Track();
            $track->file = $file;
            $trackInfo = $track->readId3();
            if (key_exists('comments_html', $trackInfo)) {
                //ID3 has been found, we can use it
                if (key_exists('title', $trackInfo['comments_html'])) {
                    $track->title = $trackInfo['comments_html']['title'][0];
                }
                if (key_exists('album', $trackInfo['comments_html'])) {
                    $track->albumName = $trackInfo['comments_html']['album'][0];
                }
                if (key_exists('artist', $trackInfo['comments_html'])) {
                    $track->artistName = $trackInfo['comments_html']['artist'][0];
                }
                if (key_exists('track_number', $trackInfo['comments_html'])) {
                    $track->track = intval($trackInfo['comments_html']['track_number'][0]);
                }
                if (key_exists('year', $trackInfo['comments_html'])) {
                    $track->year = intval($trackInfo['comments_html']['year'][0]);
                }
                if (key_exists('audio', $trackInfo) && key_exists('bitrate_mode', $trackInfo['audio'])) {
                    $track->mode = $trackInfo['audio']['bitrate_mode'];
                }
                if (key_exists('audio', $trackInfo) && key_exists('bitrate', $trackInfo['audio'])) {
                    $track->bitrate = intval($trackInfo['audio']['bitrate']);
                }
                if (key_exists('playtime_seconds', $trackInfo)) {
                    $track->time = intval($trackInfo['playtime_seconds']);
                }
                if (key_exists('filesize', $trackInfo)) {
                    $track->size = intval($trackInfo['filesize']);
                }
            }
            if (!isset($track->title, $track->albumName, $track->artistName)) {
                //We use the filesystem pattern /path/artistName/albumName/title.ext
                $elements = explode('/', $file);
                $title = str_replace('-', ' ', str_replace('_', ' ', end($elements)));
                $albumName = str_replace('-', ' ', str_replace('_', ' ', prev($elements)));
                $artistName = str_replace('-', ' ', str_replace('_', ' ', prev($elements)));
                if (!isset($track->title)) {
                    $track->title = $title;
                }
                if (!isset($track->albumName)) {
                    $track->albumName = $albumName;
                }
                if (!isset($track->artistName)) {
                    $track->artistName = $artistName;
                }
            }
            //insert/update artist
            $artist = new Artist();
            $track->artist = $artist->insertIfRequired($track->artistName, null);
            //insert/update album
            $album = new Album();
            $track->album = $album->insertIfRequired($track->albumName, null, $track->artist);
            //insert track
            if ($track->insert()) {
                //add to the returned array
                array_push($result, $track->structureData($track));
            }
        }
        //return inserted files
        return $result;
    }
}
