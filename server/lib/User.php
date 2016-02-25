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
     * @var bool User status
     */
    public $status;
    /**
     * @var array User scope
     */
    public $scope;

    /**
     * Initializes a User object with his identifier.
     *
     * @param int $id User identifier
     */
    public function __construct($id = null)
    {
        if ($id !== null) {
            $this->id = intval($id);
        }
    }

    /**
     * Populate user profile by querying on his identifier.
     *
     * @return bool True if the user is retrieved
     */
    public function populate()
    {
        if (is_int($this->id)) {
            global $connection;
            include_once $_SERVER['DOCUMENT_ROOT'].'/server/lib/Connection.php';
            $query = $connection->prepare('SELECT * FROM `user` WHERE `id`=:id LIMIT 1;');
            $query->bindValue(':id', $this->id, PDO::PARAM_INT);
            if ($query->execute() && $query->rowCount() > 0) {
                $query->setFetchMode(PDO::FETCH_INTO, $this);
                //return true if there is user fetched, false otherwise
                return (bool) $query->fetch();
            }
        }
        //return false to indicate an error occurred while reading the user
        return false;
    }

    /**
     * Validate a user object with provided informations.
     *
     * @param object $user  User object to validate
     * @param string $error The returned error message
     *
     * @return bool True if the user object provided is correct
     */
    public function validateModel($user, &$error)
    {
        $error = '';
        if ($user === null) {
            $error = 'invalid resource';
            //return false and detailed error message
            return false;
        }
        if (property_exists($user, 'sub')) {
            $user->id = $user->sub;
        }
        //iterate on each object attributes to set object
        foreach ($this as $key => $value) {
            if (property_exists($user, $key)) {
                //get provided attribute
                $this->$key = $user->$key;
            }
        }
        //check mandatory attributes
        if (isset($this->id) && !is_int($this->id)) {
            $error = 'integer must be provided in sub attribute';
            //return false and detailed error message
            return false;
        }
        if (!is_string($this->login)) {
            $error = 'string must be provided in login attribute';
            //return false and detailed error message
            return false;
        }
        if (!is_string($this->password)) {
            $error = 'string must be provided in password attribute';
            //return false and detailed error message
            return false;
        }
        if (!is_int($this->status)) {
            $error = 'integer must be provided in status attribute';
            //return false and detailed error message
            return false;
        }
        //User is valid
        return true;
    }

    /**
     * Create user with provided informations.
     *
     * @param object $user  User with his values attributes
     * @param string $error The returned error message
     *
     * @return bool True if the user is created
     */
    public function create($user, &$error)
    {
        $error = '';
        global $connection;
        include_once $_SERVER['DOCUMENT_ROOT'].'/server/lib/Connection.php';
        $query = $connection->prepare('INSERT INTO `user` (`login`, `name`, `email`, `password`, `status`) VALUES (:login, :name, :email, :password, :status);');
        $query->bindValue(':login', $this->login, PDO::PARAM_STR);
        $query->bindValue(':name', $this->name, PDO::PARAM_STR);
        $query->bindValue(':email', $this->email, PDO::PARAM_STR);
        $query->bindValue(':password', md5($this->password), PDO::PARAM_STR);
        $query->bindValue(':status', $this->status, PDO::PARAM_INT);
        if ($query->execute() && $query->rowCount() > 0) {
            $this->id = $connection->lastInsertId();
            //return true to indicate a successful user creation
            return true;
        }
        $error = $query->errorInfo()[2];
        //try to return intelligible error
        if ($query->errorInfo()[1] === 1062) {
            $error = 'login `'.$this->login.'` already exists';
        }
        //return false to indicate an error occurred while creating user
        return false;
    }

    /**
     * Update user scope with provided information.
     *
     * @param string $scope The requested scope (space-delimited)
     *
     * @return bool True if scope is updated
     */
    public function updateScope($scope)
    {
        global $connection;
        include_once $_SERVER['DOCUMENT_ROOT'].'/server/lib/Connection.php';
        $scopes = explode(' ', $scope);
        $result = true;
        $query = $connection->prepare('DELETE FROM `scope` WHERE `userId` = :userId;');
        $query->bindValue(':userId', $this->id, PDO::PARAM_INT);
        if (!$query->execute()) {
            //return false to indicate an error during scope update
            return false;
        }
        foreach ($scopes as $scope) {
            $query = $connection->prepare('INSERT INTO `scope` (`userId`, `scope`) VALUES (:userId, :scope);');
            $query->bindValue(':userId', $this->id, PDO::PARAM_INT);
            $query->bindValue(':scope', $scope, PDO::PARAM_STR);
            if (!$query->execute()) {
                //set result to false to indicate an error during scope update
                $result = false;
            }
        }
        //return the scope global update result
        return $result;
    }

    /**
     * Update user with provided informations.
     *
     * @param object $user  User with his new values attributes
     * @param string $error The returned error message
     *
     * @return bool True if the user is updated
     */
    public function update($user, &$error)
    {
        $error = '';
        if (is_int($user->id)) {
            global $connection;
            include_once $_SERVER['DOCUMENT_ROOT'].'/server/lib/Connection.php';
            $query = $connection->prepare('UPDATE `user` SET `login`=:login, `name`=:name, `email`=:email, `password`=:password, `status`=:status WHERE `id`=:id LIMIT 1;');
            $query->bindValue(':id', $this->id, PDO::PARAM_INT);
            $query->bindValue(':login', $this->login, PDO::PARAM_STR);
            $query->bindValue(':name', $this->name, PDO::PARAM_STR);
            $query->bindValue(':email', $this->email, PDO::PARAM_STR);
            $query->bindValue(':password', md5($this->password), PDO::PARAM_STR);
            $query->bindValue(':status', $this->status, PDO::PARAM_INT);
            if ($query->execute()) {
                //return true to indicate a successful user update
                return true;
            }
            $error = $query->errorInfo()[2];
            //try to return intelligible error
            if ($query->errorInfo()[1] === 1062) {
                $error = ': login `'.$this->login.'` already exists';
            }
        }
        //return false to indicate an error occurred while reading the user
        return false;
    }

    /**
     * Check credentials and populate user if they are valid.
     *
     * @param string $login    User login
     * @param string $password User password
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
     * Return user scope.
     *
     * @return array User scope (list of granted scopes)
     */
    public function getScope()
    {
        global $connection;
        include_once $_SERVER['DOCUMENT_ROOT'].'/server/lib/Connection.php';
        $query = $connection->prepare('SELECT `scope` FROM `scope` WHERE `userId`=:userId;');
        $query->bindValue(':userId', $this->id, PDO::PARAM_INT);
        if ($query->execute()) {
            $this->scope = $query->fetchAll(PDO::FETCH_COLUMN);
            //return scope
            return $this->scope;
        }
        //indicate there was an error during scope querying
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
        //get user scope as a list of space-delimited strings (see https://tools.ietf.org/html/rfc6749#section-3.3)
        $user->scope = implode(' ', $this->getScope());
        //returns the user public profile
        return $user;
    }

    /**
     * Return all users.
     *
     * @return array All users
     */
    public function getAllUsers()
    {
        global $connection;
        include_once $_SERVER['DOCUMENT_ROOT'].'/server/lib/Connection.php';
        $query = $connection->prepare('SELECT * FROM `user`;');
        if ($query->execute() && $query->rowCount() > 0) {
            //return array of users
            return $query->fetchAll(PDO::FETCH_CLASS, 'User');
        }
        //indicate there is a problem during querying
        return false;
    }
}
