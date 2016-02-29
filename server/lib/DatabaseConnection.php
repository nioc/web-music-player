<?php

/**
 * Database connection wrapper.
 *
 * @version 1.0.0
 *
 * @internal
 */
class DatabaseConnection extends PDO
{
    /**
     * Initializes connection with database.
     */
    public function __construct()
    {
        //get configuration informations
        include_once $_SERVER['DOCUMENT_ROOT'].'/server/lib/Configuration.php';
        $configuration = new Configuration();
        //create PDO constructor
        parent::__construct('mysql:host='.$configuration->get('dbHost').';port='.'3306'.';dbname='.$configuration->get('dbName'), $configuration->get('dbUser'), $configuration->get('dbPwd'), array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'));
    }
}
