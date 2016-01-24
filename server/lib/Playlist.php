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
    public $tracks = array();

    /**
     * Populate a specific user's playlist (tracks arrays).
     *
     * @param int $userId the user's identifier for who the playlist is requested
     */
    public function populate($userId)
    {
        global $connection;
        //include_once $_SERVER['DOCUMENT_ROOT'].'/server/lib/Track.php';
        include_once $_SERVER['DOCUMENT_ROOT'].'/server/lib/Connection.php';
        $query = $connection->prepare('SELECT `track`.`id`, `track`.`title`, `track`.`artist`, `track`.`album`, CONCAT(\'/stream/\',`track`.`id`) AS `file`, `playlist`.`userId` , `playlist`.`sequence` FROM `track`, `playlist` WHERE `track`.`id`=`playlist`.`id` AND `playlist`.`userId`=:userId ORDER BY `sequence` ASC;');
        $query->bindValue(':userId', $userId, PDO::PARAM_INT);
        $query->execute();
        $this->tracks = $query->fetchAll(PDO::FETCH_CLASS);
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
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $userId;                         // int(6) not_null primary_key multiple_key unsigned
    public $sequence;                       // int(11) not_null primary_key unsigned
    public $id;                             // int(11) not_null unsigned

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
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
        $query = $connection->prepare('SELECT `track`.`id`, `track`.`title`, `track`.`artist`, `track`.`album`, CONCAT(\'/stream/\',`track`.`id`) AS `file`, `playlist`.`userId` , `playlist`.`sequence` FROM `track`, `playlist` WHERE `track`.`id`=`playlist`.`id` AND `playlist`.`userId`=:userId AND `playlist`.`sequence`=:sequence LIMIT 1;');
        $query->bindValue(':userId',   $this->userId,   PDO::PARAM_INT);
        $query->bindValue(':sequence', $this->sequence, PDO::PARAM_INT);
        $query->execute();
        $query->setFetchMode(PDO::FETCH_INTO, $this);

        return $query->fetch();
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
            } else {
                return false;
            }
        } else {
            return false;
        }
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

            return ($query->execute() && $query->rowCount() > 0);
        } else {
            return false;
        }
    }
}
