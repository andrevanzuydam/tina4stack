<?php
/**
 * This is just an example of a default database for you to use, if you don't want it delete
 */
global $DEB; 
$DEB = new Debby( "application.db", "", "", "sqlite3", "YYYY-mm-dd" );
Ruth::setOBJECT("DEB", $DEB);