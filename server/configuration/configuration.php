<?php

/**
 * Configuration file for set global variables.
 *
 * @version 1.0.0
 *
 * @internal
 */

    // MySQL database name
    $gDbName = 'wmp';
    // MySQL database user
    $gDbUser = 'wmp';
    // MySQL database user
    $gDbPwd = 'wmp';
    // Server hosting
    $gDbHost = 'localhost';
    // Server hosting website
    $gDns = 'music.domain.com';
    // Path for music files
    $gFilesPath = '/var/www/wmp/';
    //override with local values
    @include_once $_SERVER['DOCUMENT_ROOT'].'/configuration/local.php';
