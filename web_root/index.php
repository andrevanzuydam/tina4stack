<?php

/**
 * tina4php contains all the utility functions
 */
require_once "tina4php.php";
/**
 * connection contains the database connection settings
 */
require_once "connection.php";

/**
 * initialize the routing and session
 */
Ruth::initRuth("tina4php");

/**
 * Set our roles for the system, if its not listed then deny
 */
$PUBLIC_RIGHTS = array("/", "/maggy", "/cody", "/phpinfo");
$USER_RIGHTS = array("/user/*");
$ADMIN_RIGHTS = array("/admin/*");

Ruth::setROLE("Public", $PUBLIC_RIGHTS, true);
Ruth::setROLE("User", array_merge($PUBLIC_RIGHTS, $USER_RIGHTS));
Ruth::setROLE("Admin", array_merge($PUBLIC_RIGHTS, $ADMIN_RIGHTS));

/**
 * Enable this line below for debugging
 */
//Ruth::DEBUG();

/**
 * Add the database object to the session 
 */
Ruth::setOBJECT("DEB", $DEB);

/**
 * Use Maggy to help with database migrations, watch out for databases that do not support transactions
 */
Ruth::addRoute("GET", "/maggy", function () {
    new Maggy("migration", ";");
});

/**
 * See which version of PHP we are running with and which modules have been loaded.
 */
Ruth::addRoute("GET", "/phpinfo", function () {
    phpinfo();
});

/**
 * Create CRUDL code from the database connection you have setup
 * 
 */
Ruth::addRoute ("GET", "/cody", function () {
    $DEB = Ruth::getOBJECT("DEB");
    echo (new Cody($DEB))->display();
});
     
/**
 * Parse all the routes, never delete, you can pass a single variable through to fake the route for testing.
 */
Ruth::parseRoutes();













