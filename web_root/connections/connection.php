<?php
global $DEB; 
$DEB = new Debby( "test.db", "", "", "sqlite3", "YYYY-mm-dd" );
Ruth::setOBJECT("DEB", $DEB);