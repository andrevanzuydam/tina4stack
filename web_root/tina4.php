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
global $_TINA4_SYSTEM_ROUTES;
$_TINA4_SYSTEM_ROUTES = ["/cody/*", "/maggy/*", "/phpinfo", "/kim/*", "/debby/*", "/tessa/*", "/debug"];
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
//Include Tessa for the testing
require_once "Tessa.php";
//Include Kim for the menus and content
require_once "Kim.php";

//assume things that could be overridden in the config.php file
date_default_timezone_set('America/Los_Angeles');

//Check if we have a config file to work with
if (file_exists(realpath(__DIR__) . "/config.php")) {
    require_once "config.php";
}

if (function_exists("xcache_get")) {
  if (defined("TINA4_DISABLE_CACHE") && TINA4_DISABLE_CACHE === true) {
     define("TINA4_HAS_CACHE", false);
  }
    else {
       define("TINA4_HAS_CACHE", true);  
    }
  
}
 else {
  define("TINA4_HAS_CACHE", false);   
}



if (!defined ("TINA4_INCLUDES")) define("TINA4_INCLUDES", "project");
if (!defined ("TINA4_SESSION")) define("TINA4_SESSION", "TINA4");
if (!defined ("TINA4_RUTH_DEBUG")) define("TINA4_RUTH_DEBUG", false);
    
Ruth::autoLoad($_TINA4_LOAD_PATHS . TINA4_INCLUDES, true, true);

/*
 * Initialize the TIN4 session
 */
if (!empty(TINA4_SESSION)) {
    Ruth::initRuth(TINA4_SESSION);
    if (!empty(TINA4_RUTH_DEBUG)) {
        if (TINA4_RUTH_DEBUG) {
            Ruth::DEBUG();

            Ruth::addRoute(RUTH_POST, "/debug", function() {
                echo  file_get_contents(Ruth::getREAL_PATH()."/ajax_debug.log");
            });
        }
    }
} else {
    tina4Message("TINA4_SESSION variable is not set", "Config Error");
}

/**
 * Check for mobile device
 */
if(class_exists("MobileDetect", true)){
    
    $mobile = new MobileDetect();
    $isMobile = $mobile->isMobile() !== false && $mobile->isTablet() !== true ? 1 : 0 ;
    
    Ruth::setSESSION("isMobile", $isMobile);
    define("ISMOBILE", $isMobile);
   
}

//Check if we have a connections folder for database connections and init Debby
if (file_exists(realpath(__DIR__ . "/connections"))) {
    Ruth::autoLoad(realpath(__DIR__ . "/connections"), false);
    
    if ( strpos (Ruth::getREQUEST_URI(), "/debby") !== false ) {
        Ruth::addRoute(RUTH_GET, "/debby/create", 
                function () {
                    echo (new Debby())->createConnection();
                }
        );

        Ruth::addRoute(RUTH_POST, "/debby/create", 
                function () {
                    (new Debby())->updateConnection();
                }
        );
    }
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

if (file_exists(realpath(__DIR__ . "/migrations"))) {
    /**
     * Use Maggy to help with database migrations
     */
    if ( strpos (Ruth::getREQUEST_URI(), "/maggy") !== false ) {
        Ruth::addRoute(RUTH_GET, "/maggy", function () {
            new Maggy("migrations", ";", true); //we want to run migrations
        });

        Ruth::addRoute(RUTH_GET, "/maggy/create", function () {
            echo (new Maggy("migrations", ";"))->createMigration();
        });

        Ruth::addRoute(RUTH_POST, "/maggy/create", function () {
            (new Maggy("migrations", ";"))->updateMigration();
        });
    }

}

//Check if we have tests existing and then add this path
if (file_exists(realpath(__DIR__ . "/test"))) {
    /**
     * Use Tessa to run selenium tests, selenium server must be downloaded and running on the default port
     */
    if ( strpos (Ruth::getREQUEST_URI(), "/tessa") !== false ) {
        Ruth::addRoute("GET", "/tessa", function () {
            require_once realpath(__DIR__ . "/test/runTessa.php");
            new runTessa("test");
        });

        Ruth::addRoute("GET", "/tessa/{browser}", function ($browser) {
            require_once realpath(__DIR__ . "/test/runTessa.php");
            new runTessa("test", $browser);
        });
        
        
        
    }
}

/**
 * Add the default PHP info route
 */
Ruth::addRoute(RUTH_GET, "/phpinfo", function () {
    phpinfo();
}, null, RUTH_IGNORE_ROUTE);

/** 
 * Initialize KIM only if needed
 */
if ( strpos (Ruth::getREQUEST_URI(), "/kim") !== false || strpos (Ruth::getREQUEST_URI(), "/cody") !== false) {
    (new Kim());
}

/**
 * Initialize the default routes for cody
 */
(new Cody());

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

if ( strpos (Ruth::getREQUEST_URI(), "/cody") !== false ) {
    
    Ruth::addRoute(RUTH_GET, "/cody", function() {
            echo (new Cody())->codeBuilder();
        }
    );
    
    Ruth::addRoute(RUTH_POST, "/cody/{action}", function($action) {
            echo (new Cody())->codeHandler($action);
        }
    );
    
}

//We should check to see if we have a kim.db file to load routes from before parsing
if (file_exists("kim.db") && strpos(Ruth::getREQUEST_URI(), "/maggy") === false) {
   (new Kim())->loadDefines(); //these come from global settings
   (new Kim())->loadRoutes();   
}

/**
 * Parse all the routes, never delete this code below, you can pass a single variable through to fake the route for testing.
 */
Ruth::parseRoutes();
