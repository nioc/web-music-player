<?php

/**
 * Connection to the SQL database.
 *
 * @version 1.0.0
 *
 * @internal
 */
if (!isset($connection)) {
    include_once $_SERVER['DOCUMENT_ROOT'].'/server/lib/Configuration.php';
    $configuration = new Configuration();
    $connection = new PDO('mysql:host='.$configuration->get('dbHost').';port='.'3306'.';dbname='.$configuration->get('dbName'), $configuration->get('dbUser'), $configuration->get('dbPwd'), array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'));
}
