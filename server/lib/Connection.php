<?php

/**
 * Connection to the SQL database.
 *
 * @version 1.0.0
 *
 * @internal
 */
if (!isset($connection)) {
    include_once $_SERVER['DOCUMENT_ROOT'].'/server/configuration/configuration.php';
    $connection = new PDO('mysql:host='.$gDbHost.';port='.'3306'.';dbname='.$gDbName, $gDbUser, $gDbPwd, array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'));
}
