<?php
/**
 * This is a helper file to help you configure some default search paths for the Tina4 auto loader
 * We recommend having a project folder for your class files
 */

/**
 * Set default time zone
 */
date_default_timezone_set('America/Los_Angeles');
/**
 * Auto load paths for Tina4 to put your project together
 * Each path you wish to be included can be separated by the default PHP path separator
 */
define("TINA4_INCLUDES", "project".PATH_SEPARATOR."other");
/**
 * A standard way of operating a PHP website is to specify a session name for the site, Ruth needs a session to be set before she can operate otherwise she will call her sessions TINA4
 */
define("TINA4_SESSION", "NDIRA");
/**
 * Set ruth debugging on for better debugging
 */
define("TINA4_RUTH_DEBUG", false);