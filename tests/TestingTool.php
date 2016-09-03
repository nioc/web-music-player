<?php

/**
 * Mock database by creating dummy schema.
 *
 * @version 1.0.0
 *
 * @internal
 */
class TestingTool
{
    public function __construct()
    {
        //set document root for use in CLI
        if (!isset($_SERVER) || !array_key_exists('DOCUMENT_ROOT', $_SERVER) || $_SERVER['DOCUMENT_ROOT'] == '' ) {
            $_SERVER['DOCUMENT_ROOT'] = dirname(__FILE__).'/../';
        }
    }
    /**
     * Create SQL schema and tables.
     * @param  string  $dbEngine     Database engine (sqlite or mysql, default is sqlite)
     * @param  string  $wmpDbName    Database name (only for MySQL)
     * @param  string  $adminUserPwd Admin user account password
     * @return boolean True if schema is set
     */
    public function createSchema($dbEngine = 'sqlite', $wmpDbName = 'wmp', $adminUserPwd = 'nimda')
    {
        try {
            //connect to database
            require_once $_SERVER['DOCUMENT_ROOT'].'/server/lib/DatabaseConnection.php';
            $connection = new DatabaseConnection();
            //load language dependant script
            $sqlFilename = $_SERVER['DOCUMENT_ROOT'].'/server/configuration/create-'.$dbEngine.'.sql';
            //split each query (separated by the ";EOL")
            $array = explode(";\n", file_get_contents($sqlFilename));
            $nbLines = count($array);
            for ($i = 0; $i < $nbLines; ++$i) {
                //remove comments (-- and text behind) and handle line
                $queryString = preg_replace('/--.*$/m', '', filter_var($array[$i]));
                //remove EOL
                $queryString = str_replace("\n", ' ', $queryString);
                if ($queryString !== '' && $queryString !== ' ') {
                    //add end a ";" at the end of the query
                    $queryString .= ';';
                    //replace the default schema 'wmp' with the user's one (MySQL only)
                    $queryString = str_replace('`wmp`', "`$wmpDbName`", $queryString);
                    $query = $connection->prepare($queryString);
                    if ($query === false) {
                        //error during statement preparation, display cause
                        error_log(json_encode($connection->errorInfo()).' on query: '.$queryString);
                        $results['tables']['Tables creation'] = $connection->errorInfo()[2];
                        //return to the main thread for displaying error
                        return false;
                    }
                    if (!$query->execute()) {
                        //error during statement execution, display cause
                        error_log(json_encode($query->errorInfo()).' on query: '.$queryString);
                        $results['tables']['Tables creation'] = $query->errorInfo()[2];
                        $continue = false;
                        break;
                    }
                    //table creation is ok
                    $results['tables']['Tables creation'] = $i.' tables set';
                    $continue = true;
                }
            }
            $results['tables']['Admin user account'] = 'Admin user account password has not been changed';
            if ($continue && $adminUserPwd !== '') {
                $results['tables']['Admin user account'] = 'Admin user account password has been changed';
                //update admin user password
                $query = $connection->prepare('UPDATE `user` SET `password`=:password WHERE `id`=1 LIMIT 1;');
                $query->bindValue(':password', md5($adminUserPwd), PDO::PARAM_STR);
                if (!$query->execute()) {
                    $results['tables']['Admin user account'] = 'Admin user account password has not been changed';

                    return false;
                }

                return true;
            }

            return $continue;
        } catch (Exception $exception) {
            $results['user']['Database access'] = $exception->getMessage();

            return false;
        }
    }
    public function backupConfig()
    {
        //back up local configuration
        if (file_exists($_SERVER['DOCUMENT_ROOT'].'/server/configuration/local.ini')) {
            rename($_SERVER['DOCUMENT_ROOT'].'/server/configuration/local.ini', $_SERVER['DOCUMENT_ROOT'].'/server/configuration/localBeforePHPUnit.ini');
        }
    }
    public function restoreConfig()
    {
        //restore previous local configuration
        unlink($_SERVER['DOCUMENT_ROOT'].'/server/configuration/local.ini');
        if (file_exists($_SERVER['DOCUMENT_ROOT'].'/server/configuration/localBeforePHPUnit.ini')) {
            rename($_SERVER['DOCUMENT_ROOT'].'/server/configuration/localBeforePHPUnit.ini', $_SERVER['DOCUMENT_ROOT'].'/server/configuration/local.ini');
        }
    }
    /**
     * Setup a SQLlite database and configuration
     */
    public function setupDummySqliteConnection()
    {
        $this->backupConfig();
        //create local configuration
        $path = '/tests/server/data/wmpDummyPHPUnit.sqlite';
        $localConfigFile = fopen($_SERVER['DOCUMENT_ROOT'].'/server/configuration/local.ini', 'w');
        fwrite($localConfigFile, "; This a dummy configuration file for PHPUnit tests\n");
        fwrite($localConfigFile, "dbEngine = \"sqlite\"\n");
        fwrite($localConfigFile, "dbPath = \"$path\"\n");
        fclose($localConfigFile);
        //create directory and drop previous schema
        if (file_exists($_SERVER['DOCUMENT_ROOT'].$path)) {
            unlink($_SERVER['DOCUMENT_ROOT'].$path);
        } else {
            mkdir(dirname($_SERVER['DOCUMENT_ROOT'].$path), 0700);
        }
        //create schema
        $this->createSchema('sqlite');
    }
}
