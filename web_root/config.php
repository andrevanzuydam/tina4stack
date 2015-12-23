<?php
/**
 * This is a helper file to help you configure some default search paths for the Tina4 auto loader
 * We recommend having a project folder for your class files
 */

/**
 * Define the release version of the stack
 */
define("TINA4_RELEASE", "v1.0.5");
/**
 * Set default time zone
 */
date_default_timezone_set('America/Los_Angeles');
/**
 * Auto load paths for Tina4 to put your project together
 * Each path you wish to be included can be separated by the default PHP path separator
 */
define("TINA4_INCLUDES", "project".PATH_SEPARATOR."objects".PATH_SEPARATOR."git");
/**
 * A standard way of operating a PHP website is to specify a session name for the site, Ruth needs a session to be set before she can operate otherwise she will call her sessions TINA4
 */
define("TINA4_SESSION", "TINA4");
/**
 * Set ruth debugging on for better debugging
 */
define("TINA4_RUTH_DEBUG", false);
/**
 * Disable the cache for development or if you don't like it 
 */
define("TINA4_DISABLE_CACHE", false); //this means we ignore the cache, currently not supported in PHP 7.0
/**
 * Define the paths relative to the web_root where we can load files from
 **/
define("TINA4_EDITOR_PATHS", ["assets", "objects", "project", "roles", "routes", "tina4", "config.php"]);
/**
 * Define the path for the rest path for access to the database
 */
define("TINA4_REST_PATH", "api");

/**
 * Define the git path
 */
define ("TINA4_GIT_BIN", "C:/Program Files/Git/bin/git.exe");
if (defined("TINA4_GIT_BIN") && !file_exists(TINA4_GIT_BIN)) {
    echo "Please define TINA4_GIT_BIN in ".__FILE__;
    exit;
}