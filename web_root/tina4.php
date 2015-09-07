<?php
/**
 * @mainpage Tina4 Stack
 * @author Andre van Zuydam
 * @version 1.0.0
 * @copyright Tina4
 * @tableofcontents
 *
 * @section introduction Introduction to the Tina4Stack
 *
 * The Tina4Stack started with a need to bring some uniformity to the development I was doing in PHP and especially where
 * I was working with other developers on the same project.  I have often wondered if I should start using one of the popular
 * frameworks out there but have decided against it for various reasons.  Instead I have decided to share with you my work bench
 * so to speak where you can also benefit from the work that has gone into this tool.
 *
 * @note Tina4 simply means "This is NOT another Framework!"
 *
 * @section what_is_in_the_stack What is in the Stack?
 *
 * The Stack is built up from the following technologies which hopefully will get your development up and running in minutes.
 * Incidentally we do not deploy any database engines for you and this should be done separately which is something we prefer.
 *
 * <UL>
 *  <LI>Nginx</LI>
 *  <LI>PHP 5.6 <OL>
 *                  <LI>Curl is enabled</LI>
 *                  <LI>GD2 is enabled</LI>
 *                  <LI>MySQLi is enabled</LI>
 *                  <LI>SQLite3 is enabled</LI>
 *                  <LI>XDebug is enabled on port 9000</LI>
 *                  <LI>XCache is enabled</LI>
 *                  <LI>Exif is enabled</LI>
 *                  <LI>FileInfo is enabled</LI>
 *                  <LI>Mbstring is enabled</LI>
 *                  <LI>OpenSSL is enabled</LI>
 *                  <LI>Soap is enabled</LI>
 *                  <LI>XSL is enabled</LI>
 *              </OL>
 *  </LI>
 *  <LI>Doxygen - both Windows & Linux scripts - you are reading generated documentation now!</LI>
 *  <LI>Swagger UI</LI>
 *  <LI>Selenium Client</LI>
 *  <LI>XCache UI</LI>
 * </UL>
 *
 * @note On my todo list are the Tina4 clients for Linux & MacOS
 *
 * @section what_is_tina4 What is Tina4
 *
 * Tina4 is a collection of tools which will help you with your PHP development.  In order to make the tools easy to remember and use we
 * have given them names so that you can identify with each of the personalities when you are working.  For example Ruth is responsible for
 * Routing, Maggy is responsible for migrations.
 *
 * Now it may seem daunting to make use of tools you did not assist in building but you can use as little or as much of the tools as you want.
 * After going over the core functionlity of each of the girls in the stack I will explain the basics of how everything is supposed to work.
 *
 *
 * @section tina Tina
 *
 * Tina is the CEO of the Tina4 Corporation, as anyone knows a successful company needs a strong leader. Her skills lie in the organization of each
 * of the girls and making sure each person collaborates properly on the task at hand.  Tina is passionate about providing solutions to her customers
 * and it is very important to her that each peson is doing the job they were assigned.  In a nutshell we have the following:
 *
 * <UL>
 *   <LI> Routing </LI>
 *   <LI> Migrations </LI>
 *   <LI> Database Abstraction </LI>
 *   <LI> Code Simplification </LI>
 *   <LI> Templating </LI>
 *   <LI> UI Testing with Selenium </LI>
 *   <LI> WYIWYG Report Generation </LI>
 *   <LI> Email Handling </LI>
 *   <LI> Object Abstraction </LI>
 * </UL>
 *
 * @section ruth Ruth
 *
 * Ruth as we have said before is responsible for routing. Routing as you may be familiar with is the ability to handle URL requests to your website.
 * You may be quite comfortable with writing routing tables in Apache or NGINX. If you decide to use Ruth you immediately have an application which will
 * run on NGINX and Apache equally well.  Ruth also handles security and sessions which are explained in her section.
 *
 * A quick example of Ruth in action, you would get this link at http://localhost:12345/testing when developing.
 * The output of this route would be @a Hello @a Tina4!
 *
 * @subsection ruth_example Ruth Example
 * @code{.php}
 *
 * //Adding a GET route
 * Ruth::addRoute (RUTH_GET, "/testing", function () {
 *      echo "Hello Tina4!";
 * });
 *
 * @endcode
 *
 * @note Simply adding a PHP file under the routes folder and adding the above code is sufficient for Ruth to start working.
 *
 *
 * @remark The setup of the web server routes all URL requests to the tina4.php file which in turn relays them to Ruth. If your webserver has not been setup
 * the stack will attempt to give you a configuration for Nginx or Apache.
 *
 * @section cody Cody
 * Cody was written to automate things that may take up a lot of repetition.  At this point in time Cody helps automate Bootstrap functionlity.
 * Cody has a very neat Ajax handler which automatically reads form variables and passes them to the URL you specify.  The Ajax handler of Cody can
 * also handle file uploading via Ajax and when used with Ruth and Debby you have very little coding to do.
 *
 * @subsection cody_example Example of Bootstrap Input
 * The following code will create a properly formatted Bootstrap input with all the correct classes. You can see this in action in Maggy http://localhost:12345/maggy/create
 * @code
 *
 *      echo (new Cody())->bootStrapInput("firstName", "First Name", "Full Names", $defaultValue = "", "text");
 *
 * @endcode
 *
 * @subsection code_example_ajax Example of Ajax Handler
 * The following code will create a Javascript function called @a callAjax which can be used to run Ajax commands to your routing
 * @code
 *
 * echo (new Cody())->ajaxHandler();
 *
 * @endcode
 *
 *
 *
 *
 *
 */
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
//Include Olga for object
require_once "Olga.php";


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
