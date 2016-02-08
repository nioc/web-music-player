<?php

/**
 * User definition.
 *
 * @version 1.0.0
 *
 * @internal
 */
class User
{
    /**
     * @var int User internal identifier
     */
    public $id;
    /**
     * @var string User login
     */
    public $login;
    /**
     * @var string User full name
     */
    public $name;
    /**
     * @var string User email address
     */
    public $email;
    /**
     * @var string Encrypted user password
     */
    public $password;
    /**
     * @var bool USer status
     */
    public $status;

    /**
     * Check credentials and populate user if they are valid.
     *
     * @return bool True if the user credentials are valid, false otherwise
     */
    public function checkCredentials($login, $password)
    {
        $this->login = $login;
        $this->password = $password;
        global $connection;
        include_once $_SERVER['DOCUMENT_ROOT'].'/server/lib/Connection.php';
        $query = $connection->prepare('SELECT * FROM `user` WHERE `login`=:login AND `password`=:password LIMIT 1;');
        $query->bindValue(':login', $this->login, PDO::PARAM_STR);
        $query->bindValue(':password', md5($this->password), PDO::PARAM_STR);
        if ($query->execute() && $query->rowCount() > 0) {
            $query->setFetchMode(PDO::FETCH_INTO, $this);
            //return true if there is user fetched, false otherwise
            return (bool) $query->fetch();
        }
        //return false to indicate an error occurred while reading the user
        return false;
    }

    /**
     * Return public profile.
     *
     * @return object A public version of user profile
     */
    public function getProfile()
    {
        $user = new stdClass();
        $user->sub = (int) $this->id;
        $user->login = $this->login;
        $user->name = $this->name;
        $user->email = $this->email;
        //returns the user public profile
        return $user;
    }
}
