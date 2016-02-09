<?php

/**
 * Configuration wrapper.
 *
 * @version 1.0.0
 *
 * @internal
 */
class Configuration
{
    /**
     * @var array Configuration settings
     */
    private $configuration;

    public function __construct()
    {
        $confDist = array();
        $confLocal = array();
        //get original configuration
        if (is_file($_SERVER['DOCUMENT_ROOT'].'/server/configuration/configuration.ini')) {
            $confDist = parse_ini_file($_SERVER['DOCUMENT_ROOT'].'/server/configuration/configuration.ini');
        }
        //get local configuration
        if (is_file($_SERVER['DOCUMENT_ROOT'].'/server/configuration/local.ini')) {
            $confLocal = parse_ini_file($_SERVER['DOCUMENT_ROOT'].'/server/configuration/local.ini');
        }
        //merge configurations (local override original)
        $this->configuration = array_merge($confDist, $confLocal);
    }

    /**
     * Returns a configuration setting.
     *
     * @param string $key Searched setting
     *
     * @return mixed|bool Setting value
     */
    public function get($key)
    {
        if (array_key_exists($key, $this->configuration)) {
            return $this->configuration[$key];
        }
        //unknown key
        return false;
    }
}
