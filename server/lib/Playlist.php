<?php

/**
 * Playlist (set of tracks) definition.
 *
 * The playlist is the user's list of tracks
 *
 * @version 1.0.0
 *
 * @internal
 */
class Playlist
{
    /**
     * @var array List of tracks included in a playlist
     */
    public $tracks = array();

    /**
     * Populate a specific user's playlist (tracks arrays).
     *
     * @param int $userId the user's identifier for who the playlist is requested
     */
    public function populate($userId)
    {
        global $connection;
        include_once $_SERVER['DOCUMENT_ROOT'].'/server/lib/Connection.php';
        include_once $_SERVER['DOCUMENT_ROOT'].'/server/lib/Track.php';
        $query = $connection->prepare('SELECT `track`.`id`, `track`.`title`, `track`.`artist`, `artist`.`name` AS `artistName`, `track`.`album`, `album`.`name` AS `albumName`, CONCAT(\'/stream/\',`track`.`id`) AS `file`, `playlist`.`userId` , `playlist`.`sequence` FROM `track`, `album`, `artist`, `playlist` WHERE `track`.`artist`=`artist`.`id` AND `track`.`album`=`album`.`id` AND `track`.`id`=`playlist`.`id` AND `playlist`.`userId`=:userId ORDER BY `sequence` ASC;');
        $query->bindValue(':userId', $userId, PDO::PARAM_INT);
        $query->execute();
        $this->tracks = $query->fetchAll(PDO::FETCH_CLASS);
        foreach ($this->tracks as $track) {
            $trackStructured = new Track();
            $track = $trackStructured->structureData($track);
        }
    }
}

/**
 * PlaylistItem (track) definition.
 *
 * This class describe the tracks included in a user's playlist
 *
 * @version 1.0.0
 *
 * @internal
 */
class PlaylistItem
{
    /**
     * @var int User identifier, indicates the playlist owner
     */
    public $userId;
    /**
     * @var int Track sequence in the user playlist
     */
    public $sequence;
    /**
     * @var int Track identifier
     */
    public $id;

    public function __construct($userId, $sequence, $id)
    {
        $this->userId = $userId;
        $this->sequence = $sequence;
        $this->id = $id;
    }
    /**
     * Returns a specific user's track by it sequence number.
     *
     * @return mixed|bool track on success, or false on error.
     */
    public function get()
    {
        global $connection;
        include_once $_SERVER['DOCUMENT_ROOT'].'/server/lib/Connection.php';
        include_once $_SERVER['DOCUMENT_ROOT'].'/server/lib/Track.php';
        $query = $connection->prepare('SELECT `track`.`id`, `track`.`title`, `track`.`artist`, `artist`.`name` AS `artistName`, `track`.`album`, `album`.`name` AS `albumName`, CONCAT(\'/stream/\',`track`.`id`) AS `file`, `playlist`.`userId` , `playlist`.`sequence` FROM `track`, `album`, `artist`, `playlist` WHERE `track`.`artist`=`artist`.`id` AND `track`.`album`=`album`.`id` AND `track`.`id`=`playlist`.`id` AND `playlist`.`userId`=:userId AND `playlist`.`sequence`=:sequence LIMIT 1;');
        $query->bindValue(':userId',   $this->userId,   PDO::PARAM_INT);
        $query->bindValue(':sequence', $this->sequence, PDO::PARAM_INT);
        $query->execute();
        $query->setFetchMode(PDO::FETCH_INTO, $this);
        if ($query->fetch()) {
            $trackStructured = new Track();
            //returns structured track
            return $trackStructured->structureData($this);
        }
        //returns false to indicate there is no such a track for this user
        return false;
    }
    /**
     * Inserts a specific user's track and returns it sequence number.
     *
     * @return mixed|bool the playlistitem inserted or false on error.
     */
    public function insert()
    {
        global $connection;
        include_once $_SERVER['DOCUMENT_ROOT'].'/server/lib/Connection.php';
        //get next sequence for this user
        $query = $connection->prepare('SELECT max(`sequence`) FROM `playlist` WHERE `userId`=:userId;');
        $query->bindValue(':userId',   $this->userId,   PDO::PARAM_INT);
        if ($query->execute()) {
            $this->sequence = (string) ($query->fetchColumn() + 1);
            //then insert new playlist items
            $query = $connection->prepare('INSERT INTO `wmp`.`playlist` (`userId`, `sequence`, `id`) VALUES (:userId, :sequence, :id);');
            $query->bindValue(':userId',   $this->userId,   PDO::PARAM_INT);
            $query->bindValue(':sequence', $this->sequence, PDO::PARAM_INT);
            $query->bindValue(':id',  $this->id,  PDO::PARAM_INT);
            if ($query->execute()) {
                //populate data for returning
                return $this->get();
            }
            //return false to indicate an error occurred while inserting track in user's playlist
            return false;
        }
        //return false to indicate an error occurred while reading current sequence user's playlist
        return false;
    }
    /**
     * Deletes a specific user's track.
     *
     * @return bool true if the track is deleted from the user's playlist, false on error
     */
    public function delete()
    {
        if (isset($this->userId, $this->sequence)) {
            global $connection;
            include_once $_SERVER['DOCUMENT_ROOT'].'/server/lib/Connection.php';
            $query = $connection->prepare('DELETE FROM `playlist` WHERE `userId`=:userId AND `sequence`=:sequence LIMIT 1;');
            $query->bindValue(':userId',   $this->userId,   PDO::PARAM_INT);
            $query->bindValue(':sequence', $this->sequence, PDO::PARAM_INT);
            //return true to indicate a successful track deletion
            return ($query->execute() && $query->rowCount() > 0);
        }
        //return false to indicate an error occurred while deleting track from user's playlist
        return false;
    }
}
