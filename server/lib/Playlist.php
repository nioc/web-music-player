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
     * @var int Owner of the playlist
     */
    public $userId;
    /**
     * @var array List of tracks included in a playlist
     */
    public $tracks = array();
    /**
     * @param int $userId Owner of the playlist
     */
    public function __construct($userId)
    {
        $this->userId = $userId;
    }

    /**
     * Populate a specific user's playlist (tracks arrays).
     */
    public function populate()
    {
        include_once $_SERVER['DOCUMENT_ROOT'].'/server/lib/Track.php';
        include_once $_SERVER['DOCUMENT_ROOT'].'/server/lib/DatabaseConnection.php';
        $connection = new DatabaseConnection();
        $query = $connection->prepare('SELECT `track`.`id`, `track`.`title`, `track`.`artist`, `artist`.`name` AS `artistName`, `track`.`album`, `album`.`name` AS `albumName`, CONCAT(\'/stream/\',`track`.`id`) AS `file`, `playlist`.`userId` , `playlist`.`sequence`, `cover`.`id` AS `coverId` FROM `track`, `artist`, `playlist`, `album` LEFT JOIN `cover` ON `album`.`id`=`cover`.`albumId` AND `cover`.`status` = 1 WHERE `track`.`artist`=`artist`.`id` AND `track`.`album`=`album`.`id` AND `track`.`id`=`playlist`.`id` AND `playlist`.`userId`=:userId ORDER BY `sequence` ASC;');
        $query->bindValue(':userId', $this->userId, PDO::PARAM_INT);
        $query->execute();
        $this->tracks = $query->fetchAll(PDO::FETCH_CLASS);
        foreach ($this->tracks as $track) {
            $trackStructured = new Track();
            $track = $trackStructured->structureData($track);
            $track->sequence = (int) $track->sequence;
        }
    }

    /**
     * Return the maximum sequence used for user.
     *
     * @return int|bool The max sequence for the user or false on error.
     */
    private function getSequenceMax()
    {
        include_once $_SERVER['DOCUMENT_ROOT'].'/server/lib/DatabaseConnection.php';
        $connection = new DatabaseConnection();
        $query = $connection->prepare('SELECT MAX(`sequence`) FROM `playlist` WHERE `userId`=:userId;');
        $query->bindValue(':userId', $this->userId, PDO::PARAM_INT);
        if ($query->execute()) {
            //return max sequence from user's playlist
            return (int) $query->fetchColumn();
        }
        //return false to indicate an error occurred while reading the max sequence from user's playlist
        return false;
    }

    /**
     * Change order of a track in the user's playlist.
     *
     * @param int $oldSequence Current sequence of the updated track
     * @param int $newSequence Requested sequence of the updated track
     */
    public function reorder($oldSequence, $newSequence)
    {
        if ($oldSequence !== $newSequence) {
            include_once $_SERVER['DOCUMENT_ROOT'].'/server/lib/Track.php';
            include_once $_SERVER['DOCUMENT_ROOT'].'/server/lib/DatabaseConnection.php';
            $connection = new DatabaseConnection();
            //get sequence max
            $max = $this->getSequenceMax();
            //move to the end the track
            $query = $connection->prepare('UPDATE `playlist` SET `sequence`=:maxSequence WHERE `userId`=:userId AND `sequence`=:oldSequence LIMIT 1;');
            $query->bindValue(':userId', $this->userId, PDO::PARAM_INT);
            $query->bindValue(':oldSequence', $oldSequence, PDO::PARAM_INT);
            $query->bindValue(':maxSequence', $max + 1, PDO::PARAM_INT);
            if ($query->execute()) {
                //move other tracks
                if ($oldSequence < $newSequence) {
                    $query = $connection->prepare('UPDATE `playlist` SET `sequence`=`sequence`-1 WHERE `userId`=:userId AND `sequence`>:oldSequence AND `sequence`<=:newSequence ORDER BY `sequence` ASC;');
                } else {
                    $query = $connection->prepare('UPDATE `playlist` SET `sequence`=`sequence`+1 WHERE `userId`=:userId AND `sequence`<:oldSequence AND `sequence`>=:newSequence ORDER BY `sequence` DESC;');
                }
                $query->bindValue(':userId', $this->userId, PDO::PARAM_INT);
                $query->bindValue(':oldSequence', $oldSequence, PDO::PARAM_INT);
                $query->bindValue(':newSequence', $newSequence, PDO::PARAM_INT);
                if ($query->execute()) {
                    //set requested sequence for the track
                    $query = $connection->prepare('UPDATE `playlist` SET `sequence`=:newSequence WHERE `userId`=:userId AND `sequence`=:maxSequence LIMIT 1;');
                    $query->bindValue(':userId', $this->userId, PDO::PARAM_INT);
                    $query->bindValue(':newSequence', $newSequence, PDO::PARAM_INT);
                    $query->bindValue(':maxSequence', $max + 1, PDO::PARAM_INT);
                    //return the result of the last operation
                    return $query->execute();
                }
            }
            //return false if there was error
            return false;
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
        include_once $_SERVER['DOCUMENT_ROOT'].'/server/lib/Track.php';
        include_once $_SERVER['DOCUMENT_ROOT'].'/server/lib/DatabaseConnection.php';
        $connection = new DatabaseConnection();
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
        include_once $_SERVER['DOCUMENT_ROOT'].'/server/lib/DatabaseConnection.php';
        $connection = new DatabaseConnection();
        //get next sequence for this user
        $query = $connection->prepare('SELECT max(`sequence`) FROM `playlist` WHERE `userId`=:userId;');
        $query->bindValue(':userId',   $this->userId,   PDO::PARAM_INT);
        if ($query->execute()) {
            $this->sequence = (string) ($query->fetchColumn() + 1);
            //then insert new playlist items
            $query = $connection->prepare('INSERT INTO `playlist` (`userId`, `sequence`, `id`) VALUES (:userId, :sequence, :id);');
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
            include_once $_SERVER['DOCUMENT_ROOT'].'/server/lib/DatabaseConnection.php';
            $connection = new DatabaseConnection();
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
