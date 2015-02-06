<?php

/**
 * Please do not modify this file, what you need to know is this:
 * 
 * Database connections go in the connection folder for Debby
 * Routes are set in the routes folder for Ruth
 * Migrations are set in the migrations folder for Maggy
 * 
 * */
//Default rights which should be allowed in TINA4 if roles are going to be used
$_TINA4_SYSTEM_ROUTES = ["/cody", "/maggy", "/phpinfo"];
$_TINA4_LOAD_PATHS = "";

//Funky output message
function tina4Message($message, $error = "Error") {
    echo "<div><h3>{$error}</h3>{$message}</div>";
}

//Set the include paths for tina4, on the windows, we should look for the tina4 stack one folder below the current one or the web root folder
if (file_exists(realpath(__DIR__ . "/../tina4") . "/Shape.php")) {
    ini_set('include_path', get_include_path() . PATH_SEPARATOR . realpath(__DIR__ . "/../tina4"));
} else
if (file_exists(realpath(__DIR__ . "/tina4") . "/Shape.php")) {
    ini_set('include_path', get_include_path() . PATH_SEPARATOR . realpath(__DIR__ . "/tina4"));
} else {
    tina4Message("Please check your tina4 installation, we cannot find includes local to your web root");
    die();
}

//include shape for the project
require_once "Shape.php";
//include Ruth for the routing
require_once "Ruth.php";
//include Debby for the datbases
require_once "Debby.php";
//include Cody for the code generation
require_once "Cody.php";
//include Maggy for the migrations
require_once "Maggy.php";
//include Emma for the emailing
require_once "Emma.php";
//include Phoebe for the phar file building
require_once "Phoebe.php";
//include Reta for the reporting
require_once "Reta.php";

//Check if we have a config file to work with
if (file_exists(realpath(__DIR__) . "/config.php")) {
    require_once "config.php";
}
//We assume stuff
else {
    date_default_timezone_set('America/Los_Angeles');
    define("TINA4_INCLUDES", "project");
    define("TINA4_SESSION", "TINA4");
    define("TINA4_RUTH_DEBUG", false);
}


Ruth::autoLoad($_TINA4_LOAD_PATHS . TINA4_INCLUDES, true, true);

/**
 * Initialize the TIN4 session
 */
if (!empty(TINA4_SESSION)) {
    Ruth::initRuth(TINA4_SESSION);
    if (!empty(TINA4_RUTH_DEBUG)) {
        if (TINA4_RUTH_DEBUG) {
            Ruth::DEBUG();
        }
    }
} else {
    tina4Message("TINA4_SESSION variable is not set", "Config Error");
}

//Check if we have a connections folder for database connections and init Debby
if (file_exists(realpath(__DIR__ . "/connections"))) {
    Ruth::autoLoad(realpath(__DIR__ . "/connections"), false);
}

//Check if the routes folder exists for routing
if (!file_exists(realpath(__DIR__ . "/routes"))) {
    tina4Message("Please setup a routes folder in your web root folder");
    die();
} else {
    $_TINA4_LOAD_PATHS .= realpath(__DIR__ . "/routes") . PATH_SEPARATOR;
}

if (file_exists(realpath(__DIR__ . "/roles"))) {
    $_TINA4_LOAD_PATHS .= realpath(__DIR__ . "/roles") . PATH_SEPARATOR;
}


//Check if we have a migrations folder and init Maggy

if (file_exists(realpath(__DIR__ . "/migration"))) {
    /**
     * Use Maggy to help with database migrations
     */
    Ruth::addRoute("GET", "/maggy", function () {
        new Maggy("migration", ";");
    });

}



/**
 * Add the default PHP info route
 */
Ruth::addRoute(RUTH_GET, "/phpinfo", function () {
    phpinfo();
}, RUTH_IGNORE_ROUTE);

/**
 * Build a phar file of the project
 */
Ruth::addRoute(RUTH_GET, "/build", function () {
    //call Phoebe with the name of the phar file, by default phoebe will build the web_root folder
    new Phoebe (TINA4_SESSION);
}); 

/**
 * Include all the relevant paths
 */
Ruth::autoLoad($_TINA4_LOAD_PATHS . TINA4_INCLUDES, false);

/**
 * Parse all the routes, never delete this code below, you can pass a single variable through to fake the route for testing.
 */
Ruth::parseRoutes();
