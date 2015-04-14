<?php
global $DEB; 
$DEB = new Debby( realpath(__DIR__."/../")."/test.db", $username = "", $password = "", $dbtype = "sqlite3", $outputdateformat = "mm/dd/YYYY" ); //possible options are dd/mm/YYYY dd-mm-YYYY dd.mm.YYYY mm/dd/YYYY ... YYYY-mm-dd etc ... 
Ruth::setOBJECT("DEB", $DEB);