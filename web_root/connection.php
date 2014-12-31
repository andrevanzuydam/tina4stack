<?php
global $DEB; 
$DEB = new Debby( __DIR__."\database\sample.db", $username = "", $password = "", $dbtype = "sqlite3", $outputdateformat = "dd/mm/YYYY" ); //possible options are dd/mm/YYYY dd-mm-YYYY dd.mm.YYYY mm/dd/YYYY ... YYYY-mm-dd etc ... 


