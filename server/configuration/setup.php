<?php
/*
 * This script provides the following for a first installation
 * 1 - create applicative database account
 * 2 - create schema database
 * 3 - grant applicative user rights on schema
 * 4 - create tables
 */

//get posted parameters and set default value
include_once $_SERVER['DOCUMENT_ROOT'].'/server/lib/Configuration.php';
$configuration = new Configuration();
$rootDbLogin = isset($_POST['rootDbLogin']) ? filter_input(INPUT_POST, 'rootDbLogin', FILTER_SANITIZE_STRING) : 'root';
$rootDbPassword = filter_input(INPUT_POST, 'rootDbPassword',  FILTER_SANITIZE_STRING);
$wmpDbLogin = isset($_POST['wmpDbLogin']) ? filter_input(INPUT_POST, 'wmpDbLogin', FILTER_SANITIZE_STRING) : $configuration->get('dbUser');
$wmpDbPassword = isset($_POST['wmpDbPassword']) ? filter_input(INPUT_POST, 'wmpDbPassword', FILTER_SANITIZE_STRING) : $configuration->get('dbPwd');
$wmpDbName = isset($_POST['wmpDbName']) ? filter_input(INPUT_POST, 'wmpDbName', FILTER_SANITIZE_STRING) : $configuration->get('dbName');
$process = filter_input(INPUT_POST, 'process', FILTER_SANITIZE_STRING);
$wmpDbDrop = isset($_POST['wmpDbDrop']) ? filter_input(INPUT_POST, 'wmpDbDrop', FILTER_SANITIZE_STRING) : 0;
$hashKey = isset($_POST['hashKey']) ? filter_input(INPUT_POST, 'hashKey', FILTER_SANITIZE_STRING) : $configuration->get('hashKey');
$adminUserPwd = isset($_POST['adminUserPwd']) ? filter_input(INPUT_POST, 'adminUserPwd', FILTER_SANITIZE_STRING) : '';
$filesPath = isset($_POST['filesPath']) ? filter_input(INPUT_POST, 'filesPath', FILTER_SANITIZE_STRING) : $configuration->get('filesPath');

//initialize variables
$results = array();
$results['user'] = array();
$results['schema'] = array();
$results['tables'] = array();
$results['local'] = array();

if (isset($process) &&
        $rootDbLogin !== null && $rootDbLogin !== '' &&
        $rootDbPassword !== null && $rootDbPassword !== '' &&
        $wmpDbLogin !== null && $wmpDbLogin !== '' &&
        $wmpDbPassword !== null && $wmpDbPassword !== '' &&
        $wmpDbName !== null && $wmpDbName !== '') {
    $DbName = $wmpDbName;
    $DbUser = $rootDbLogin;
    $DbPwd = $rootDbPassword;
    $localConfigFile = fopen('local.ini', 'w');
    fwrite($localConfigFile, "; This your local configuration file\n");
    try {
        //connect to database with provided informations
        $connection = new PDO('mysql:host=localhost;port=3306;', $DbUser, $DbPwd, array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'));
        //create user
        $query = $connection->prepare("DROP USER :user@'localhost';");
        $query->bindValue(':user', $wmpDbLogin);
        $query->execute();
        $query = $connection->prepare("CREATE USER :user@'localhost' IDENTIFIED BY :password;");
        $query->bindValue(':user', $wmpDbLogin);
        $query->bindValue(':password', $wmpDbPassword);
        if ($query->execute()) {
            fwrite($localConfigFile, "dbUser = \"$wmpDbLogin\"\n");
            fwrite($localConfigFile, "dbPwd = \"$wmpDbPassword\"\n");
            $results['user']['Database user'] = '<span class="valid"> Ok </span>User `'.$wmpDbLogin.'` is set';
            //create schema
            if ($wmpDbDrop) {
                $queryString = "DROP SCHEMA $wmpDbName;";
                $query = $connection->prepare($queryString);
                $query->execute();
            }
            $queryString = "CREATE SCHEMA IF NOT EXISTS $wmpDbName DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;";
            $query = $connection->prepare($queryString);
            if ($query->execute()) {
                fwrite($localConfigFile, "dbName = \"$wmpDbName\"\n");
                $results['schema']['Database schema'] = '<span class="valid"> Ok </span>Schema `'.$wmpDbName.'` is set';
                //grant user on schema
                $queryString = "GRANT SELECT, INSERT, UPDATE, DELETE, CREATE ON $wmpDbName.* TO :user@'localhost';";
                $query = $connection->prepare($queryString);
                $query->bindValue(':user', $wmpDbLogin);
                if ($query->execute()) {
                    $results['schema']['Schema grant'] = '<span class="valid"> Ok</span> User `'.$wmpDbLogin.'` is granted on schema `'.$wmpDbName.'`';
                    //create tables
                    $sqlFilename = $_SERVER['DOCUMENT_ROOT'].'/server/configuration/create.sql';
                    $array = explode(";\n", file_get_contents($sqlFilename));
                    for ($i = 0; $i < count($array); ++$i) {
                        $queryString = str_replace("\n", ' ', filter_var($array[$i]));
                        if ($queryString !== '' && $queryString !== ' ') {
                            $queryString .= ';';
                            $queryString = str_replace('`wmp`', "`$wmpDbName`", $queryString);
                            $query = $connection->prepare($queryString);
                            if (!$query->execute()) {
                                error_log(json_encode($query->errorInfo()));
                                $results['tables']['Tables creation'] = '<span class="error"> Failed </span>'.$query->errorInfo()[2];
                                $continue = false;
                                break;
                            }
                            $results['tables']['Tables creation'] = '<span class="valid"> Ok </span>'.$i.' tables set';
                            $continue = true;
                        }
                    }
                    $results['tables']['Admin user account'] = '<span class="error"> Skipped </span> Admin user account password has not been changed';
                    if ($continue && $adminUserPwd !== '') {
                        $results['tables']['Admin user account'] = '<span class="valid"> Ok </span> Admin user account password has been changed';
                        //update admin user password
                        $query = $connection->prepare('UPDATE `user` SET `password`=:password WHERE `id`=1 LIMIT 1;');
                        $query->bindValue(':password', md5($adminUserPwd), PDO::PARAM_STR);
                        if (!$query->execute()) {
                            $results['tables']['Admin user account'] = '<span class="error"> Failed </span> Admin user account password has not been changed';
                        }
                    }
                } else {
                    $results['schema']['Schema grant'] = '<span class="error"> Failed</span>'.$query->errorInfo()[2];
                }
            } else {
                $results['schema']['Database schema'] = '<span class="error"> Creation failed </span>'.$query->errorInfo()[2];
            }
        } else {
            $results['user']['Database user'] = '<span class="error"> Creation failed</span>';
        }
    } catch (Exception $exception) {
        $results['user']['Database access'] = '<span class="error"> Failed </span>'.$exception->getMessage();
    }
    //update local hash key
    fwrite($localConfigFile, "hashKey = \"$hashKey\"\n");
    $results['local']['Hash key'] = '<span class="valid"> Ok </span> tokens will use your cipher';
    //achieve local configuration file
    fclose($localConfigFile);
}
if (isset($process) && $filesPath !== null && $filesPath !== '') {
    //check if folder exists
    $results['local']['Files path'] = '<span class="error"> Failed </span> Folder '.$filesPath.' do not exists';
    if (file_exists($filesPath)) {
        //check if folder is readable
        $results['local']['Files path'] = '<span class="error"> Failed </span> Folder '.$filesPath.' can not be read by the webserver user, check permissions';
        if (is_readable($filesPath)) {
            //add '/' on path if missing
            if (substr($filesPath,-1) != '/') {
                $filesPath .= '/';
            }
            //update local root files path
            $localConfigFile = fopen('local.ini', 'w');
            fwrite($localConfigFile, "filesPath = \"$filesPath\"\n");
            $results['local']['Files path'] = '<span class="valid"> Ok </span> Your library root path is set to '.$filesPath;
            //achieve local configuration file
            fclose($localConfigFile);
        }
    }
}
?>
<!doctype html>
<html>
    <head>
        <meta charset="utf-8">
        <title>Web Music Player Setup</title>
        <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1">
        <link type="text/css" href="/display/files/vendor/normalize.css/normalize.css" rel="stylesheet"/>
        <link type="text/css" href="/display/files/vendor/font-awesome-4.3.0/css/font-awesome.min.css" rel="stylesheet"/>
        <link type="text/css" href="/display/files/wmp.css" rel="stylesheet"/>
        <style type="text/css">
        form p {
            display: table-row;
        }
        label, input, button {
            display: table-cell;
            margin: 3px 10px;
        }
        .valid {
            color : #4CAF50;
        }
        .error {
            color : #DD2C00;
        }
        </style>
    </head>
    <body>
        <h1>WMP Setup</h1>
        <p>
            <i>This will create an applicative MySQL account, a schema and the tables required, it needs an access to the MySQL root user.</i>
        </p>
        <form method="post">
            <p>
                <label for="rootDbLogin">Root database login*</label>
                <input type="text" name="rootDbLogin" id="rootDbLogin" placeholder="root" required="required" value="<?=$rootDbLogin?>"/>
            </p>
            <p>
                <label for="rootDbPassword">Root database password*</label>
                <input type="password" name="rootDbPassword" id="rootDbPassword" placeholder="Your MySQL root password" required="required" value="<?=$rootDbPassword?>"/>
            </p>
            <p>
                <label for="wmpDbLogin">WMP database login*</label>
                <input type="text" name="wmpDbLogin" id="wmpDbLogin" placeholder="wmp" required="required" value="<?=$wmpDbLogin?>"/>
            </p>
            <p>
                <label for="wmpDbPassword">WMP batabase password*</label>
                <input type="password" name="wmpDbPassword" id="wmpDbPassword" placeholder="wmp" required="required" value="<?=$wmpDbPassword?>"/>
            </p>
            <p>
                <label for="wmpDbName">WMP schema name*</label>
                <input type="text" name="wmpDbName" id="wmpDbName" placeholder="wmp" required="required" value="<?=$wmpDbName?>"/>
            </p>
            <p>
                <label for="wmpDbDrop">Drop existing schema*</label>
                <input type="checkbox" name="wmpDbDrop" id="wmpDbDrop"/>
            </p>
            <p>
                <label for="hashKey">Hash key (used for token signing)*</label>
                <input type="text" name="hashKey" id="hashKey" placeholder="Your cipher" required="required" value="<?=$hashKey?>"/>
            </p>
            <p>
                <label for="adminUserPwd">Admin user password*</label>
                <input type="password" name="adminUserPwd" id="adminUserPwd" placeholder="Admin user password" required="required" value="<?=$adminUserPwd?>"/>
            </p>
            <p>
                <label for="filesPath">Root files path*</label>
                <input type="text" name="filesPath" id="filesPath" placeholder="/home/user/music" required="required" value="<?=$filesPath?>"/>
            </p>
            <p>
                <label></label>
                <button type="submit" class="plain-button" name="process" value="process">Process</button>
            </p>
            <?foreach ($results['user'] as $key => $value):?><p><label><?=$key?>:</label><span>&nbsp;<?=$value?></span></p>
            <?endforeach;?>
            <?foreach ($results['schema'] as $key => $value):?><p><label><?=$key?>:</label><span>&nbsp;<?=$value?></span></p>
            <?endforeach;?>
            <?foreach ($results['tables'] as $key => $value):?><p><label><?=$key?>:</label><span>&nbsp;<?=$value?></span></p>
            <?endforeach;?>
            <?foreach ($results['local'] as $key => $value):?><p><label><?=$key?>:</label><span>&nbsp;<?=$value?></span></p>
            <?endforeach;?>
        </form>
    </body>
</html>
