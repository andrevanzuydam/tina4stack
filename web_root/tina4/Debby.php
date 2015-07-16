<?php

/**
 * The database system on which the tina4stack connects to databases
 * 
 * @author Andre van Zuydam <andre@xineoh.com>
 * @license GPL
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */
define("DEB_OBJECT", 0);
define("DEB_ARRAY", 1);
define("DEB_ASSOC", 2);
//removing magic quotes
if (get_magic_quotes_gpc()) {
    $process = [
        &$_GET,
        &$_POST,
        &$_COOKIE,
        &$_REQUEST
    ];
    while (list( $key, $val ) = each($process)) {
        foreach ($val as $k => $v) {
            unset($process[$key][$k]);
            if (is_array($v)) {
                $process[$key][stripslashes($k)] = $v;
                $process[] = & $process[$key][stripslashes($k)];
            } else {
                $process[$key][stripslashes($k)] = stripslashes($v);
            }
        }
    }
    unset($process);
}

/**
 *  Debby is the database class 
 * 
 *  Debby is for developers just beginning with PHP database development, it takes the differences between the PHP database implementations and
 *  makes it easy to work and switch between the databases.  It also acts as the testing platform for the SQL translation tool.
 * 
 *  @author Andre van Zuydam <andre@xineoh.com>
 */
class Debby {

    var $dbh; //the database handle
    var $error; //any errors that may occur during an operation
    var $dbtype = "sqlite3"; //the default database type
    var $dbpath = ""; //path to the database format is ipaddress:databasepath / for sqlite we just use the path
    var $tmppath = "/tmp/"; //default path to temp space (only for sqlite)
    var $lastsql = Array(); //the last sql that ran in an array
    var $lasterror = Array(); //the last error before the current one
    var $debug = false; //set debug on or off
    var $affectedrows = 0; //affected rows or rows returned by a query
    var $nooffields = 0; //the no of columns or fields returned
    var $fieldinfo; //layout in an array of each field with its type and information
    var $version = "1.0"; //current version of Debby
    var $dbdateformat = "YYYY-mm-dd h:i:s"; //future functionality for date time conversion to the database for a database
    var $outputdateformat = "dd/mm/YYYY";
    var $updatefieldinfo = true; //this is turned off when doing computed field calculations, internal and expert use only
    var $RAWRESULT;
    var $lastrowid; //for sqlite autoincrement fields
    var $tag; //tag for the database to be used in calls
    
    /*
      Function to encode raw image data
      caches the image into a folder for quick cleanup
      $imagedata = The blob or file contents
      $imagestore = the path to the file where it must be created.
     */
    function encodeImage($imagedata, $imagestore = "imagestore", $size = "", $noimage = "/imagestore/noimage.jpg") {
       
        if (!file_exists($_SERVER["DOCUMENT_ROOT"].$noimage)) {
            if (!file_exists($_SERVER["DOCUMENT_ROOT"]."/".$imagestore)) {
              mkdir ($_SERVER["DOCUMENT_ROOT"]."/".$imagestore);
            }
            $imagePath = "http://lorempixel.com/200/200/people/".rand(0,10)."/";
            $image = file_get_contents($imagePath);
           
            file_put_contents ($_SERVER["DOCUMENT_ROOT"].$noimage, $image);
            
        }
                
        $createthumbnail = false;
        if ($size != "")
            $createthumbnail = true;
        if ($imagedata == "" && $size != "") {
            if (strpos($noimage, "http") === false) {
               $noimage = $_SERVER["DOCUMENT_ROOT"]. $noimage; 
            }
            $imagedata = file_get_contents($noimage);
        }
        $imagehash = md5($imagedata);
        if ($createthumbnail == true) {
            $imagefile = $_SERVER["DOCUMENT_ROOT"] . "{$imagestore}/thmb{$size}{$imagehash}";
            if (!file_exists($imagefile)) {
                file_put_contents($imagefile, $imagedata);
                $makethumbnail = true;
            } else {
                $makethumbnail = false;
            }
            $imagetype = exif_imagetype($imagefile);

            if ($makethumbnail) {
                //JPEG
                if ($imagetype == 2) {
                    $imagesrc = imagecreatefromjpeg($imagefile);
                } else if ($imagetype == 3) {
                    $imagesrc = imagecreatefrompng($imagefile);
                } else if ($imagetype == 6) {
                    $imagesrc = imagecreatefromwbmp($imagefile);
                } else {
                    //we don't know what file it is
                    $makethumbnail = false;
                }
                if ($makethumbnail) {                    
                    $thumbsize = explode("x", $size);
                    $thumbw = $thumbsize[0];
                    $thumbh = $thumbsize[1];
                    $imagesrcw = imagesx($imagesrc);
                    $imagesrch = imagesy($imagesrc);
                    if (( $thumbw == 0 ) && ( $thumbh == 0 )) {
                        //image must be same size
                        $thumbw = $imagesrcw;
                        $thumbh = $imagesrch;
                    } elseif ($thumbh == 0) {
                        $scalew = $thumbw / ( $imagesrcw - 1 );
                        $thumbh = $imagesrch * $scalew;
                    } elseif ($thumbw == 0) {
                        $scaleh = $thumbh / ( $imagesrch - 1 );
                        $thumbw = $imagesrcw * $scaleh;
                    }
                    $thumbw = (int) ( $thumbw );
                    $thumbh = (int) ( $thumbh );
                    
                    $imagethumb = imagecreatetruecolor($thumbw, $thumbh);
                    $white = imagecolorallocate($imagethumb, 255, 255, 255);
                    imagefill($imagethumb, 0, 0, $white);
                    
                    //http://php.net/manual/en/function.imagecopyresampled.php - rayg at daylongraphics dot com 
                    $lowend = 0.8;
                    $highend = 1.25;
                    
                    $scaleX = (float)$thumbw / $imagesrcw;
                    $scaleY = (float)$thumbh / $imagesrch;
                    $scale = min($scaleX, $scaleY);

                    $dstW = $thumbw;
                    $dstH = $thumbh;
                    $dstX = $dstY = 0;

                    $scaleR = $scaleX / $scaleY;
                    if($scaleR < $lowend || $scaleR > $highend)
                    {
                        $dstW = (int)($scale * $imagesrcw + 0.5);
                        $dstH = (int)($scale * $imagesrch + 0.5);

                        // Keep pic centered in frame.
                        $dstX = (int)(0.5 * ($thumbw - $dstW));
                        $dstY = (int)(0.5 * ($thumbh - $dstH));
                    }
                    
                    //echo "copying {$newthumbw}X{$newthumbh} image";		
                    if (!imagecopyresampled($imagethumb, $imagesrc, $dstX, $dstY, 0, 0,$dstW, $dstH, $imagesrcw, $imagesrch)) {
                        imagedestroy($imagethumb);
                        imagedestroy($imagesrc);
                    } else {
                        imagedestroy($imagesrc);
                        //create output thumbnail
                        if (imagejpeg($imagethumb, $imagefile, 100)) {
                            imagedestroy($imagethumb);
                        }
                    }
                }
            }
        } else {
            $imagefile = $_SERVER["DOCUMENT_ROOT"] . "/{$imagestore}/{$imagehash}";
            if (!file_exists($imagefile)) {
                file_put_contents($imagefile, $imagedata);
            }
        }
        if ($imagedata == "") {
            return "{$noimage}";
        } else {
            if ($createthumbnail) {
                return "{$imagestore}/thmb{$size}{$imagehash}";
            } else {
                return "{$imagestore}/{$imagehash}";
            }
        }
    }
    
    /*
      Function to embed raw image as base64
     */
    function embedImage($rawImage){
        if(!empty($rawImage)){
            if(!function_exists("finfo_open")){
                die("You need to enable php_fileinfo.dll in your php.ini");
            }
            $f = finfo_open();
            
            $mime_type = finfo_buffer($f, $rawImage, FILEINFO_MIME_TYPE);

            return '<img src="data:'.$mime_type.';base64,'.base64_encode($rawImage).'"/>';
        }else{
            return false;
        }
    }

    /*
      Function to make an array out of the Debby object
     */

    function toArray() {
        return (array) $this;
    }

    /* function to take row variables to request variables */
    function toRequest($row, $prefix = "") {
        foreach ($row as $name => $value) {
            if (class_exists("Ruth")) {
                Ruth::setREQUEST($prefix . $name, $value);
            }
            $_REQUEST[$prefix . $name] = $value;
        }
    }

    /* Output the last error */
    function lastError() {
        return $this->lasterror[count($this->lasterror) - 1];
    }

    /* Output the last error */
    function getLastError() {
        return $this->lasterror[count($this->lasterror) - 1];
    }
    
    /**
     * The default page template for maggy
     * @param type $title String A title to name the page by
     * @return type Shape A page template with default bootstrap
     */
    function getPageTemplate($title="Default") {
       $html = html (
                    head (
                            title ($title),
                            alink (["rel" => "stylesheet", "href"=>"https://maxcdn.bootstrapcdn.com/bootstrap/3.3.2/css/bootstrap.min.css"]),
                            alink (["rel" => "stylesheet", "href"=> "https://maxcdn.bootstrapcdn.com/bootstrap/3.3.2/css/bootstrap-theme.min.css"]),
                            alink (["rel" => "stylesheet", "href"=> "https://cdnjs.cloudflare.com/ajax/libs/bootstrap-table/1.8.1/bootstrap-table.min.css"])
                            
                            
                    ),
                    body (  ["style" => "padding: 0px 20px 0px", "id" => "content"])
               
               );  
       return $html; 
    }
    
    
    function createConnection() {
        $dbElements = ["sqlite3" => "SQLite3",
                       "mysql" => "MySQL",
                       "firebird" => "Firebird",
                       "mssql" => "MSSQL",
                       "postgres" => "PostgreSQL",
                      ];
        
        $dateFormat = [ "mm/dd/YYYY" => "mm/dd/YYYY",
                        "dd/mm/YYYY" => "dd/mm/YYYY",
                        "YYYY-mm-dd" => "YYYY-mm-dd"
                      ];
        
        $html = $this->getPageTemplate("Create Database Connection");
        $form = form (["class" => "form-group", "method" => "post",  "enctype" => "multipart/form-data"], 
                    (new Cody())->bootStrapInput("txtNAME", $caption = "Connection Name", $placeHolder = "Connection Name", $defaultValue = "connection"),
                    (new Cody())->bootStrapInput("txtALIAS", $caption = "Connection Alias (DEB)", $placeHolder = "Connection Alias", $defaultValue = "DEB"),
                    (new Cody())->bootStrapLookup ("txtDBTYPE", $caption = "Database Type", $dbElements , $defaultValue = "sqlite3"),
                    (new Cody())->bootStrapInput("txtDBPATH", $caption = "Database Path", $placeHolder = "hostname:dbname_dbpath", $defaultValue = ""),
                    (new Cody())->bootStrapInput("txtUSERNAME", $caption = "Username", $placeHolder = "Username", $defaultValue = "", "text", ""),
                    (new Cody())->bootStrapInput("txtPASSWORD", $caption = "Password", $placeHolder = "Password", $defaultValue = "", "text", ""),
                    (new Cody())->bootStrapLookup ("txtDATEFORMAT", $caption = "Date Format", $dateFormat , $defaultValue = "YYYY-mm-dd"),
                    (new Cody())->bootStrapButton("btnCreate", $caption = "Create")
                );
        
        if (!empty(Ruth::getSESSION("debbyCreateMessage"))) {
           $html->addContent ((new Cody())->bootStrapAlert("success", $caption="Success", Ruth::getSESSION("debbyCreateMessage")));  
           Ruth::setSESSION("debbyCreateMessage", null);
        }
        
        $form = (new Cody())->bootStrapPanel("Create Database Connection", $form);
        $html->addContent ($form);
        
       
        
        return $html;
    }
    
    function updateConnection(){
        $html = "";
        $fileName = Ruth::getDOCUMENT_ROOT()."/connections/".Ruth::getREQUEST("txtNAME").".php";
        
        $codeString = '<?php
global $'.Ruth::getREQUEST("txtALIAS").'; 
$'.Ruth::getREQUEST("txtALIAS").' = new Debby( "'.Ruth::getREQUEST("txtDBPATH").'", "'.Ruth::getREQUEST("txtUSERNAME").'", "'.Ruth::getREQUEST("txtPASSWORD").'", "'.Ruth::getREQUEST("txtDBTYPE").'", "'.Ruth::getREQUEST("txtDATEFORMAT").'" );
Ruth::setOBJECT("'.Ruth::getREQUEST("txtALIAS").'", $'.Ruth::getREQUEST("txtALIAS").');';
        
        file_put_contents($fileName, $codeString );          
                
        Ruth::setSESSION("debbyCreateMessage", "{$fileName} created successfully!");
        Ruth::redirect("/debby/create");      
        return $html;
    }

    /* Constructor for Debby */

    function __construct($dbpath = "", $username = "", $password = "", $dbtype = "sqlite", $outputdateformat = "YYYY-mm-dd", $tag="DEB", $debug = false) { //possible options are dd/mm/YYYY dd-mm-YYYY dd.mm.YYYY mm/dd/YYYY ... YYYY-mm-dd etc ...
        if (!empty($dbpath)) {
            $this->debug = $debug;
            $this->tag = $tag;
            $this->connect($dbpath, $username, $password, $dbtype, $outputdateformat);
            //how do we handle dates for different databases
            $this->outputdateformat = $outputdateformat; //how do we want the dates given back to us ???   
        }
    }

    /*     * *************************************************************************** 
      BEGIN Connect
      Connect to database and create handle in $dbh
     */

    function connect($dbpath = "", $username = "", $password = "", $dbtype = "sqlite", $outputdateformat = "YYYY-mm-dd h:i:s") {
        if ($dbpath == "") {
            trigger_error("No dbpath specified in " . __METHOD__ . " for " . $this->dbtype, E_USER_ERROR);
        } else {
            $this->dbpath = $dbpath;
            $this->dbtype = $dbtype;
        }
        /* ODBC Connection */
        if ($this->dbtype == "odbc") {
            //the dbpath variable will hold the full odbc connection
            //Example : "Driver={SQL Server Native Client 11.0};Server=.\SQLExpress;Database=DBNAME;"
            $this->dbdateformat = "YYYY-mm-dd h:i:s"; //Assuming this is the default, override if wrong
            $this->dbh = @odbc_pconnect($this->dbpath, $username, $password);
        } else /* MSSQL srv native components */ if ($this->dbtype == "mssqlnative") {
            $this->dbdateformat = "YYYY-mm-dd h:i:s";
            $dbpath = explode(":", $dbpath);
            $serverName = $dbpath[0];
            if ($username != "") {
                $connectionInfo["UID"] = $username;
                $connectionInfo["PWD"] = $password;
            }
            $connectionInfo["Database"] = $dbpath[1];
            if (function_exists("sqlsrv_connect")) {
                $this->dbh = @sqlsrv_connect($serverName, $connectionInfo);
            } else {
                trigger_error("Please download and install PHP module for " . $this->dbtype . " from Microsoft Download Center.", E_USER_ERROR);
            }
        } else /* CUBRID */ if ($this->dbtype == "CUBRID") {
            //date format setting
            $this->dbdateformat = "YYYY-mm-dd h:i:s";
            $dbpath = explode(":", $dbpath);
            if (function_exists("cubrid_connect")) { //Changed as per recommendation of Esen @ CUBRID
                if ($dbpath[2] == "")
                    $dbpath[2] = 33000;
                $this->dbh = @cubrid_connect($dbpath[0], $dbpath[2], $dbpath[1], $username, $password); //this should NOT be a persistent connection, unecessary for CUBRID
            } else {
                trigger_error("Please download and install PHP module for " . $this->dbtype, E_USER_ERROR);
            }
        } else /* SQLite */ if ($this->dbtype == "sqlite") {
            //date format setting
            $this->dbdateformat = "YYYY-mm-dd h:i:s";
            if (function_exists("sqlite_popen")) {
                putenv("TMP=" . $this->tmppath);
                $this->dbh = @sqlite_popen($this->dbpath);
            } else {
                trigger_error("Please enable PHP module for " . $this->dbtype, E_USER_ERROR);
            }
        } else /* SQLite3 */ if ($this->dbtype == "sqlite3") {
            $this->dbdateformat = "YYYY-mm-dd h:i:s";
            if (class_exists("SQLite3")) {
                putenv("TMP=" . $this->tmppath);
                $this->dbh = new SQLite3($this->dbpath);
            } else {
                trigger_error("Please enable PHP module for " . $this->dbtype, E_USER_ERROR);
            }
        } else /* Firebird */ if ($this->dbtype == "firebird") {
            $this->dbdateformat = "dd.mm.YYYY h:i:s";
            //$outputdateformat = dd/mm/YYYY
            $outputdateformat = str_replace("dd", "%d", $outputdateformat);
            $outputdateformat = str_replace("mm", "%m", $outputdateformat);
            $outputdateformat = str_replace("YYYY", "%Y", $outputdateformat);
            //maybe a limitation on timestamp but who would want the hours minutes and seconds to be otherwise
            ini_set("ibase.dateformat", $outputdateformat);
            ini_set("ibase.timestampformat", $outputdateformat . " %H:%M:%S");
            if (function_exists("ibase_pconnect")) {
                $this->dbh = @ibase_pconnect($dbpath, $username, $password);
            } else {
                trigger_error("Please enable PHP module for " . $this->dbtype, E_USER_ERROR);
            }
        } else /* MySQL */ if ($this->dbtype == "mysql") {
            $this->dbdateformat = "YYYY-mm-dd h:i:s";
            if (function_exists("mysqli_connect")) {
                $dbpath = explode(":", $dbpath);
                $this->dbh = new mysqli($dbpath[0], $username, $password, $dbpath[1]);
            } else if (function_exists("mysql_connect")) {
                $dbpath = explode(":", $dbpath);
                $this->dbh = @mysql_connect($dbpath[0], $username, $password);
                @mysql_select_db($dbpath[1]);
            } else {
                trigger_error("Please enable PHP module for " . $this->dbtype, E_USER_ERROR);
            }
        } else /* Oracle */ if ($this->dbtype == "oracle") {
            $this->dbdateformat = "YYYY-mm-dd h:i:s";
            if (function_exists("oci_connect")) {
                $this->dbh = @oci_connect($username, $password, $dbpath);
            } else {
                trigger_error("Please enable PHP module for " . $this->dbtype, E_USER_ERROR);
            }
        } else /* Postgres */ if ($this->dbtype == "postgres") {
            $dbpath = explode(":", $dbpath);
            $sconnect = "host={$dbpath[0]} dbname={$dbpath[1]} user=$username password=$password ";
            $this->dbdateformat = "YYYY-mm-dd h:i:s";
            if (function_exists("pg_connect")) {
                $this->dbh = @pg_connect($sconnect);
            } else {
                trigger_error("Please enable PHP module for " . $this->dbtype, E_USER_ERROR);
            }
        } else /* Microsoft SQL Server */ if ($this->dbtype == "mssql") {
            $dbpath = explode(":", $dbpath);
            $this->dbdateformat = "YYYY-mm-dd h:i:s";
            if (function_exists("mssql_connect")) {
                //MSSQL needs changes in the php.ini file - we need to make the user aware of this.
                ini_set("mssql.textlimit", "2147483647"); //We need to do this to make blobs work and it doesn't work!!!!!!
                ini_set("mssql.textsize", "2147483647"); //We need to do this to make blobs work  
                ini_set("odbc.defaultlrl", "12024K"); // this is the max size for blobs        
                ini_set("mssql.datetimeconvert", "0");
                $this->dbh = @mssql_connect($dbpath[0], $username, $password);
                @mssql_select_db($dbpath[1]);
            } else {
                trigger_error("Please enable PHP module for " . $this->dbtype, E_USER_ERROR);
            }
        } else {
            trigger_error("Please implement " . __METHOD__ . " for " . $this->dbtype, E_USER_ERROR);
        }

        //get the last error
        $this->getError();

        /* Debugging for Connect */
        if (!$this->dbh) {
            if (is_array($dbpath)) {
                $tmpdbpath = "<b>Host:</b>" . $dbpath[0] . " ";
                $tmpdbpath .= "<b>Database:</b>" . $dbpath[1] . " ";
                $tmpdbpath .= "<b>Port:</b>" . $dbpath[2] . " ";
                $dbpath = $tmpdbpath;
            }
            trigger_error("Could not establish connection for " . $dbpath . " in " . __METHOD__ . " for " . $this->dbtype, E_USER_ERROR);
        }
    }

    /**
      BEGIN Close
     */
    function close() {
        if (defined("TINA4_HAS_CACHE") && TINA4_HAS_CACHE !== false) {
            xcache_clear_cache(1);
        }
        
        $result = false;
        if (!$this->dbh) {
            trigger_error("No database handle, use connect first in " . __METHOD__ . " for " . $this->dbtype, E_USER_WARNING);
        } else /* ODBC Connection */ if ($this->dbtype == "odbc") {
            $result = @odbc_close($this->dbh);
        } else /* MSSQL srv native components */ if ($this->dbtype == "mssqlnative") {
            $result = @sqlsrv_close($this->dbh);
        } else /* CUBRID */ if ($this->dbtype == "CUBRID") {
            $result = @cubrid_disconnect($this->dbh);
        } else /* SQLite */ if ($this->dbtype == "sqlite") {
            $result = @sqlite_close($this->dbh);
            $result = true;
        } else /* SQLite3 */ if ($this->dbtype == "sqlite3") {
            $this->dbh->close();
            $result = true;
        } else /* Firebird */ if ($this->dbtype == "firebird") {
            $result = @ibase_close($this->dbh);
            $result = true;
        } else /* Oracle */ if ($this->dbtype == "oracle") {
            $result = @oci_close($this->dbh);
            $result = true;
        } else /* MySQL */ if ($this->dbtype == "mysql") {
            if (function_exists("mysqli_connect")) {
                $result = $this->dbh->close();
                $result = true;
            } else {
                $result = @mysql_close($this->dbh);
                $result = true;
            }
        } else /* Postgres */ if ($this->dbtype == "postgres") {
            $result = @pg_close($this->dbh);
        } else /* Microsoft SQL Server */ if ($this->dbtype == "mssql") {
            $result = @mssql_close($this->dbh);
        } else {
            trigger_error("Please implement " . __METHOD__ . " for " . $this->dbtype, E_USER_ERROR);
        }
        /* Debugging for Close */
        if ($result) {
            $this->dbh = "";
        } else {
            trigger_error("Cant close $this->dbpath in " . __METHOD__ . " for " . $this->dbtype, E_USER_NOTICE);
        }
        return $result;
    }

    /**
     * Start Transaction
     * 
     * This starts a transaction if you database engine supports it and returns the transaction id
     * The returned transaction id can be used for rolling back a transaction
     *  
     * @return Pointer A transaction id pointing to the transaction 
     */
    function startTransaction() {
        $result = false;
        if (!$this->dbh) {
            trigger_error("No database handle, use connect first in " . __METHOD__ . " for " . $this->dbtype, E_USER_WARNING);
        } else /* ODBC Connection */ if ($this->dbtype == "odbc") {
            trigger_error("Needs to be implemented");
        } else /* MSSQL srv native components */ if ($this->dbtype == "mssqlnative") {
            trigger_error("Needs to be implemented");
        } else /* CUBRID */ if ($this->dbtype == "CUBRID") {
            trigger_error("Needs to be implemented");
        } else /* SQLite */ if ($this->dbtype == "sqlite") {
            trigger_error("Needs to be implemented");
        } else /* SQLite3 */ if ($this->dbtype == "sqlite3") {
            //No transaction handling in this driver as of yet
            $result = "Resource id #0"; 
        } else /* Firebird */ if ($this->dbtype == "firebird") {
            $result = @ibase_trans(IBASE_DEFAULT, $this->dbh);
        } else /* Oracle */ if ($this->dbtype == "oracle") {
            trigger_error("Needs to be implemented");
        } else /* MySQL */ if ($this->dbtype == "mysql") {
            if (function_exists("mysqli_connect")) {
                $this->dbh->autocommit(false);
                $this->dbh->begin_transaction();
                $result = "Resource id #0";
                //trigger_error("Needs to be implemented");
            } else {
                @mysql_query("SET AUTOCOMMIT=0");
                @mysql_query("START TRANSACTION");
                $result = "Resource id #0";
            }
        } else /* Postgres */ if ($this->dbtype == "postgres") {
            trigger_error("Needs to be implemented");
        } else /* Microsoft SQL Server */ if ($this->dbtype == "mssql") {
            trigger_error("Needs to be implemented");
        } else {
            trigger_error("Please implement " . __METHOD__ . " for " . $this->dbtype, E_USER_ERROR);
        }

        return $result;
    }

    /**
     * Rollback Transaction
     * 
     * This starts a transaction if you database engine supports it and returns the transaction id
     * The returned transaction id can be used for rolling back a transaction
     *  
     * @return Pointer A transaction id pointing to the transaction 
     */
    function rollbackTransaction($handle) {
        $result = false;
        if (!$this->dbh) {
            trigger_error("No database handle, use connect first in " . __METHOD__ . " for " . $this->dbtype, E_USER_WARNING);
        } else /* ODBC Connection */ if ($this->dbtype == "odbc") {
            trigger_error("Needs to be implemented");
        } else /* MSSQL srv native components */ if ($this->dbtype == "mssqlnative") {
            trigger_error("Needs to be implemented");
        } else /* CUBRID */ if ($this->dbtype == "CUBRID") {
            trigger_error("Needs to be implemented");
        } else /* SQLite */ if ($this->dbtype == "sqlite") {
            trigger_error("Needs to be implemented");
        } else /* SQLite3 */ if ($this->dbtype == "sqlite3") {
            trigger_error("Needs to be implemented");
        } else /* Firebird */ if ($this->dbtype == "firebird") {
            $result = @ibase_rollback($handle);
        } else /* Oracle */ if ($this->dbtype == "oracle") {
            trigger_error("Needs to be implemented");
        } else /* MySQL */ if ($this->dbtype == "mysql") {
            if (function_exists("mysqli_connect")) {
                $this->dbh->rollback();
            } else {
                @mysql_query("ROLLBACK");
            }
        } else /* Postgres */ if ($this->dbtype == "postgres") {
            trigger_error("Needs to be implemented");
        } else /* Microsoft SQL Server */ if ($this->dbtype == "mssql") {
            trigger_error("Needs to be implemented");
        } else {
            trigger_error("Please implement " . __METHOD__ . " for " . $this->dbtype, E_USER_ERROR);
        }

        return $result;
    }

    /**
      BEGIN set_database
     * 
     * This is more for a MYSQL type database where you can choose a different database once a connection has been made
     */
    function setDatabase($dbname) {
        if ($this->dbh) {
            /* ODBC Connection */
            if ($this->dbtype == "odbc") {
                trigger_error("Please implement " . __METHOD__ . " for " . $this->dbtype);
            } else if ($this->dbtype == "mysql") {
                if (function_exists("mysqli_connect")) {
                    $this->dbh->select_db($dbname);
                } else {
                    @mysql_select_db($dbname, $this->dbh);
                }
                return true;
            } else {
                trigger_error("Please implement " . __METHOD__ . " for " . $this->dbtype);
            }
        } else {
            return false;
        }
    }

    /*     * *************************************************************************** 
      BEGIN get_instance A System Function

      Finds all types of a word and returns all the positions and word type

     */

    function getInstance($word, $sql) {
        $icount = 0;
        foreach ($sql as $id => $value) {
            if (trim(str_replace(",", "", $value)) == trim($word)) {
                $instance[$icount] = $id;
                $icount++;
            }
        }
        return $instance;
    }

    /*
      END  get_instance
     * *************************************************************************** */
    /*     * *************************************************************************** 
      BEGIN parseSQL - System Function
     */

    function parseSQL($sql = "", $fromdbtype = "generic", $todbtype = "generic") {
        //ignore initially the fromdbtype & todbtype
        //get rid of weird sql
        $sql = str_replace("'null'", "null", $sql);
        //first section - change limits in mysql to firebird - needs to be enhanced for many sub selects
        //flatten sql
        //echo $sql;

        if ($this->dbtype == "firebird") {
            $sql = str_replace("`", "\"", $sql);
        } else
        if ($this->dbtype == "mssql" || $this->dbtype == "mssqlnative") {
            $sql = str_replace("first", "top", $sql);
            $sql = str_replace("||", "+", $sql);
            $sql = str_replace("timestamp", "datetime", $sql);
        }
        if ($this->dbtype == "sqlite3") {
            $sql = str_replace("'now'", "current_timestamp", $sql);
        }
        
        if ($this->dbtype == "mysql") {
            
            $sql = str_replace("'now'", "CURRENT_TIMESTAMP", $sql);
            $sql = str_replace("'datetime'", "DATETIME", $sql);
        }

        if (( stripos($sql, "update") !== false || stripos($sql, "insert") !== false ) && ( $this->dbtype == "mssql" || $this->dbtype == "mssqlnative" )) {
            $sql = str_replace("'now'", "GETDATE()", $sql);
        } else if (( stripos($sql, "update") !== false || stripos($sql, "insert") !== false ) && $this->dbtype == "CUBRID") {
            $sql = str_replace("'now'", "CURRENT_TIMESTAMP()", $sql);
        }

        if (stripos($sql, "update ") === false && stripos($sql, "insert ") === false && stripos($sql, "delete ") === false) {

            $sql = str_replace("\r", "", $sql);
            $sql = str_replace("\n", " ", $sql);
            $sql = str_replace(" ,", ",", $sql);
            $sql = str_replace(", ", ",", $sql);
            $sql = str_replace(",", ", ", $sql);
            $parsedsql = explode(" ", $sql);
            //get rid of spaces ?

            foreach ($parsedsql as $pid => $pvalue) {
                $parsedsql[$pid] = trim($pvalue);
                if ($parsedsql[$pid] == "")
                    unset($parsedsql[$pid]);
            }
            //fix the index of the array
            $newarray = Array();
            $icount = 0;
            foreach ($parsedsql as $pid => $pvalue) {
                $newarray[$icount] = $pvalue;
                $icount++;
            }
            $parsedsql = $newarray;
            if ($this->dbtype == "postgres" && stripos($sql, "blob") !== false) { //postgres doesn't know about blobs it uses oids
                foreach ($parsedsql as $id => $value) {
                    $value = str_ireplace("longblob", "oid", $value);
                    $value = str_ireplace("shortblob", "oid", $value);
                    $parsedsql[$id] = str_ireplace("blob", "oid", $value);
                }
            } else if (( $this->dbtype == "mysql" || $this->dbtype == "sqlite3" ) && stripos($sql, "first ") !== false) {
                //select first 1 skip 10 must become 
                 
                $firsts = $this->getInstance("first", $parsedsql);

                if (count($firsts) > 0) {
                    $icount = 0;
                    //kill all the firsts 
                    foreach ($firsts as $id => $index) {
                        $limits[$icount] = $parsedsql[$index + 1];
                        unset($parsedsql[$index + 1]);
                        unset($parsedsql[$index]);
                        if (trim(strtolower($parsedsql[$index + 2])) == "skip") {

                            $limits[$icount] = $parsedsql[$index + 3] . "," . $limits[$icount];
                            unset($parsedsql[$index + 2]);
                            unset($parsedsql[$index + 3]);
                        }
                        $limits[$icount] = "limit " . $limits[$icount];

                        $icount++;
                    }
                    //Add the first limit to the end of the parsedsql;


                    array_push($parsedsql, $limits[0]);
                }
            } else if ($this->dbtype == "firebird" && stripos($sql, "limit ") !== false) { //check for MySQL or ORacle limit
                //find all the selects
                $selects = $this->getInstance("select", $parsedsql);
                $limits = $this->getInstance("limit ", $parsedsql);
                if (count($limits) > 0) {
                    //remove all the limits & parse for skip & first
                    $icount = 0;
                    foreach ($limits as $id => $index) {
                        $firstskip[$icount] = $parsedsql[$index + 1];
                        if (stripos($firstskip[$icount], ")") !== false) {
                            $parsedsql[$index + 1] = ")";
                            $firstskip[$icount] = str_replace(")", "", $firstskip[$icount]);
                        } else {
                            unset($parsedsql[$index + 1]);
                        }
                        $firstskip[$icount] = explode(",", $firstskip[$icount]);
                        unset($parsedsql[$index]);
                        $icount++;
                    }
                    //do the first & last select 
                    if (count($firstskip[$icount - 1]) == 1) {
                        $parsedsql[$selects[0]] = "select first " . $firstskip[$icount - 1][0];
                    } else {
                        $parsedsql[$selects[0]] = "select first " . $firstskip[$icount - 1][1] . " skip " . $firstskip[$icount - 1][0];
                    }
                    //and then the rest
                    if ($icount > 1) {
                        for ($i = 1; $i < $icount; $i++) {
                            if (count($firstskip[$i]) == 1) {
                                $parsedsql[$selects[$i]] = "(select first " . $firstskip[$i][0];
                            } else {
                                $parsedsql[$selects[$i]] = "(select first " . $firstskip[$i][1] . " skip " . $firstskip[$i][0];
                            }
                        }
                    }
                }
                //print_r ($parsedsql);
            } else if (( $this->dbtype == "mssql" || $this->dbtype == "mssqlnative" ) && ( stripos($sql, "blob") !== false || stripos($sql, "date") !== false || stripos($sql, "now") !== false )) {
                //check for blobs, dates and now
                $parsedsql = $sql;
                $sqlwords = array(
                    '/ blob/',
                    '/ date /',
                    '/\'now\'/'
                );
                $repwords = array(
                    ' image null',
                    ' datetime',
                    ' GETDATE() '
                );
                $parsedsql = preg_replace($sqlwords, $repwords, $parsedsql);
            }

            $newsql = "";
            if (is_array($parsedsql)) {
                foreach ($parsedsql as $id => $value) {
                    if (trim($value) != "") {
                        $newsql .= $value . " ";
                    }
                }
                $parsedsql = $newsql;
            }
        } else {
            $parsedsql = $sql;
        }

        $this->lastsql[count($this->lastsql)] = $parsedsql; // save the last sql  
        
        return $parsedsql;
    }

    /*
      END  parseSQL
     * *************************************************************************** */
    /*     * *************************************************************************** 
      BEGIN escape_string - a generic escape string function
     */

    function escapeString($data) {
        if ($data != 0 and (!isset($data) or empty($data)))
            return '';
        if (is_numeric($data))
            return $data;
        $non_displayables = array(
            '/%0[0-8bcef]/', // url encoded 00-08, 11, 12, 14, 15
            '/%1[0-9a-f]/', // url encoded 16-31
            '/[\x00-\x08]/', // 00-08
            '/\x0b/', // 11
            '/\x0c/', // 12
            '/[\x0e-\x1f]/' // 14-31
        );
        foreach ($non_displayables as $regex)
            $data = preg_replace($regex, '', $data);
        $data = str_replace("'", "''", $data);
        return $data;
    }

    /*
      END  escape_string
     * *************************************************************************** */
    /*     * *************************************************************************** 
      BEGIN Set Params - make all ? replaced with passed params
     */

    function setParams($sql = "", $inputvalues = Array()) {
        $lastplace = 1; //Mustn't go back in the replace
        $count = 0;
        for ($i = 1; $i < sizeof($inputvalues); $i++) {
            $tryme = $inputvalues[$i];
            if (strpos($tryme."", "Resource id #") !== false) {
              continue;  
            }           
            if ($this->dbtype != "CUBRID")
                $inputvalues[$i] = str_replace("'", "''", $inputvalues[$i]); //some strings have single ' which make it break on replacing!
            if ($this->dbtype == "mysql") {
                if (function_exists("mysqli_connect")) {
                    $inputvalues[$i] = $this->dbh->real_escape_string($inputvalues[$i]);
                } else {
                    $inputvalues[$i] = mysql_real_escape_string($inputvalues[$i]);
                }
            }
            if ($this->dbtype == "sqlite") {
                $inputvalues[$i] = sqlite_escape_string($inputvalues[$i]);
            } else
            if ($this->dbtype == "sqlite3") {
                $inputvalues[$i] = $this->escapeString($inputvalues[$i]);
            } else
            if ($this->dbtype == "CUBRID") {
                $inputvalues[$i] = $this->escapeString($inputvalues[$i]);
            }
            $inputvalues[$i] = "'" . $inputvalues[$i] . "'";
            $lastpos = 1;
            while ($lastpos <> 0) {
                $lastpos = strpos($sql, "?", $lastplace);
                if ($lastpos == "")
                    break; //This checks that lastpos
                if ($sql[$lastpos - 1] != "<" || $sql[$lastpos + 1] != ">") {
                    $sql = substr_replace($sql, $inputvalues[$i], $lastpos, 1);
                    $lastplace = $lastpos + strlen($inputvalues[$i]);
                }
                $lastpos = 0;
            }
            $count++;
        }
        return $sql;
    }

    /*
      END  Set Params
     * *************************************************************************** */
    /*     * *************************************************************************** 
      BEGIN Exec
     */

    function exec($sql = "") {
        $inputvalues = func_get_args();
        if (defined("TINA4_HAS_CACHE") && TINA4_HAS_CACHE !== false) {
            xcache_clear_cache(1);
        }
        
        
        $this->error = ""; // reset the last error;   
        $result = false;

        $sqlParse = $this->setParams($sql, $inputvalues);

        $this->lastsql[count($this->lastsql)] = "preparse: " . $sqlParse;
        $sql = $this->parseSQL($sql);

        if (!$this->dbh) {
            trigger_error("No database handle, use connect first in " . __METHOD__ . " for " . $this->dbtype, E_USER_WARNING);
        } else /* ODBC Connection */ if ($this->dbtype == "odbc") {

            $query = @odbc_prepare($this->dbh, $sql);
            $params = array();
            $params[0] = $query;
            //what if we have passed some parameters - firebird can do this
            for ($i = 1; $i < func_num_args(); $i++) {
                $params[$i] = func_get_arg($i);
            }
            if (sizeof($params) != 0) {
                $result = @call_user_func_array("odbc_execute", $params);
            } else {
                $result = @odbc_execute($query);
            }
        } else /* Microsoft SQL Server */ if ($this->dbtype == "mssqlnative") {

            $sql = $this->setParams($sql, $inputvalues);
            $result = @sqlsrv_query($this->dbh, $sql);
            if ($result != false) {
                $result = "No Errors";
            } else {
                $result = $this->getError();
            }
            //help with the debugging on mssql
            if ($result != "No Errors") {
                $result = false;
            } else {
                $result = true;
            }
        } else /* CUBRID */ if ($this->dbtype == "CUBRID") {

            $sql = $this->setParams($sql, $inputvalues);
            $result = @cubrid_execute($this->dbh, $sql);
            if ($result) {
                $result = true;
                $this->lastrowid = @cubrid_insert_id();
            }
        } else /* SQLite */ if ($this->dbtype == "sqlite") {

            $sql = $this->setParams($sql, $inputvalues);


            @sqlite_exec($this->dbh, $sql, $result);
            if ($result == "") {
                $result .= "No Errors\n";
            }
        } else /* SQLite3 */ if ($this->dbtype == "sqlite3") {
            
            if (strpos($sql, "?") !== false) {
                //echo "here";
                
                $statement = $this->dbh->prepare($sql);

                if ($statement) {

                    $params = array();
                    for ($i = 1; $i < func_num_args(); $i++) {
                        if (strpos(func_get_arg($i)."", "Resource id #") !== false) {
                            $tranID = func_get_arg($i);
                        } else {
                            $params[$i] = func_get_arg($i);
                        }
                    }
                        
                    
                    foreach ($params as $pid => $param) {
                        
                        if (is_float($param)) {
                                //echo "binding {$pid} float"; 
                                $statement->bindValue($pid, $param, \SQLITE3_FLOAT);
                            } else
                            if (is_int($param)) {
                                //echo "binding {$pid} int"; 
                                $statement->bindValue($pid,  $param, \SQLITE3_INTEGER);
                            } else
                            if (is_string($param)) {
                                //echo "binding  {$pid} text"; 
                                $statement->bindValue($pid, $param, \SQLITE3_TEXT);
                            }
                         else {
                            //echo "binding blob  ";
                            $statement->bindValue($pid, $param, \SQLITE3_BLOB);
                        }
                    }
                    
                    
                   
                    $result = $statement->execute();
                    
                    if (!empty($result)) {
                        $result = true;
                    }
                } else {
                    echo $result = "Failed to run script {$sql}";
                }
            } else {
                $result = $this->dbh->exec($sql);
            }


            if ($result == 1) {
                $this->lastrowid = $this->dbh->lastInsertRowID();
            }
        } else /* Firebird */ if ($this->dbtype == "firebird") {

            $params = array();
            $params[] = null;
            //what if we have passed some parameters - firebird can do this
            $tranID = "";
            for ($i = 1; $i < func_num_args(); $i++) {

                if (strpos(func_get_arg($i)."", "Resource id #") !== false) {
                    $tranID = func_get_arg($i);
                } else {
                    $params[] = func_get_arg($i);
                }
            }

            if (!empty($tranID)) {
                $query = @ibase_prepare($this->dbh, $tranID, $sql);
            } else {
                $query = @ibase_prepare($this->dbh, $sql);
            }

           
            $params[0] = $query;
            if (sizeof($params) != 0) {
                $result = @call_user_func_array("ibase_execute", $params);
            } else {
                $result = @ibase_execute($query);
            }


            if (strpos ($result." ", "Resource") !== false ) {
                $result = $this->getRow($result);
            }
            
        } else /* Oracle */ if ($this->dbtype == "oracle") {

            $sql = $this->setParams($sql, $inputvalues);
            $query = @oci_parse($this->dbh, $sql);
            $result = @oci_execute($query);
        } else /* MySQL */ if ($this->dbtype == "mysql") {

            if (function_exists("mysqli_connect")) {
                $sql = $this->setParams($sql, $inputvalues);
                $this->lastsql[count($this->lastsql)] = "preparse: " . $sql;
                if ($result = $this->dbh->query($sql)) {
                    //$this->dbh->free_result($result);
                    $result = "No Errors";
                } else {
                    //add the last error message
                    $this->lasterror[count($this->lasterror)] = $this->dbh->error;
                }
                
                if ($result === "No Errors") {
                  $this->lastrowid = $this->dbh->insert_id;
                }        
            } else {
                $sql = $this->setParams($sql, $inputvalues);
                $result = @mysql_query($sql, $this->dbh);
                $this->lastrowid = $this->dbh->insert_id;
                
                if ($result != false) {
                    $result = "No Errors";
                } else {
                    $result = $this->getError();
                }
            }
        } else /* Postgres */ if ($this->dbtype == "postgres") {

            $sql = $this->setParams($sql, $inputvalues);
            $result = @pg_query($sql);
            if ($result != false) {
                $result = "No Errors";
            } else {
                $result = $this->getError();
            }
        } else /* Microsoft SQL Server */ if ($this->dbtype == "mssql") {

            $sql = $this->setParams($sql, $inputvalues);
            $result = @mssql_query($sql);
            if ($result != false) {
                $result = "No Errors";
            } else {
                $result = $this->getError();
            }
            //help with the debugging on mssql
            if ($result != "No Errors") {
                print_r($result);
            }
        } else {
            trigger_error("Please implement " . __METHOD__ . " for " . $this->dbtype, E_USER_ERROR);
        }

        /* Debugging for Exec */
        return $result;
    }

    /*
      END  Exec
     * *************************************************************************** */
    /*     * *************************************************************************** 
      BEGIN Commit
     */
    function commit($tranId = null) {
        if (defined("TINA4_HAS_CACHE") && TINA4_HAS_CACHE !== false) {
            xcache_clear_cache(1);
        }
        
        if (!$this->dbh) {
            trigger_error("No database handle, use connect first in " . __METHOD__ . " for " . $this->dbtype, E_USER_WARNING);
        } else /* ODBC Connection */ if ($this->dbtype == "odbc") {
            $result = @odbc_commit($this->dbh);
        } else /* MS SQL Native */ if ($this->dbtype == "mssqlnative") {
            $result = @sqlsrv_commit($this->dbh);
        } else /* CUBRID */ if ($this->dbtype == "CUBRID") {
            $result = @cubrid_commit($this->dbh);
        } else /* SQLite */ if ($this->dbtype == "sqlite" || $this->dbtype == "sqlite3") {
            
            $result = true;
        } else /* Firebird */
        if ($this->dbtype == "firebird") {
            if ($tranId != null) {
                $result = @ibase_commit($tranId);
            } else {
                $result = @ibase_commit();
            }
        } else /* Oracle */ if ($this->dbtype == "oracle") {
            $result = @oci_commit($this->dbh);
        } else /* MySQL */ if ($this->dbtype == "mysql") {
            if (function_exists("mysqli_connect")) {
                $result = $this->dbh->commit();
            } else {
                @mysql_query("COMMIT");
                $result = true;
            }
        } else /* Postgres */ if ($this->dbtype == "postgres") {
            //Please test this !!!
            @pg_query($this->dbh, "commit");
            //0trigger_error ("Please implement ".__METHOD__." for ".$this->dbtype, E_USER_ERROR);
            $result = true;
        } else /* Microsoft SQL Server */ if ($this->dbtype == "mssql") {
            
            $result = true;
        } else {
            trigger_error("Please implement " . __METHOD__ . " for " . $this->dbtype, E_USER_ERROR);
        }

        //see if there was an error
        $this->getError();

        return $result;
    }

    
    /**
     * Create insert statement and run it
     * @param type $tablename Table name
     * @param type $fieldValues Array value pairs ["field" => "value"]
     */    
    function insert ($tablename, $fieldValues) {
        $sqlInsert = "insert into {$tablename} ";
        foreach ($fieldValues as $field => $value) {
          $columnNames[] = strtolower($field);      
          //ignore the defaults for timestamps
          if ($value !== "'now'" && $value !== "current_timestamp") {
            $columnValues[] = "?";
          }
           else {
            $columnValues[] = $value;    
          }
        }
        $sqlInsert .= "( ".join(",", $columnNames).") ";
        $sqlInsert .= "values ( ".join(",", $columnValues).");";
        
        $params[] = $sqlInsert;
        foreach ($fieldValues as $field => $value) {
            if ($value !== "'now'" && $value !== "current_timestamp") {
                 $params[] = $value;
            }
        }
                       
       
        return call_user_func_array([$this, "exec"], $params);
    }
    
    /**
     * Create update statement and run it
     * @param type $tablename Table name
     * @param type $fieldValues Array value pairs ["field" => "value"]
     * @param type $indexFieldValue Array ["index" => "value"]
     * @todo prevent updating if indexFieldValue is empty
     */
    function update ($tablename, $fieldValues, $indexFieldValue) {

        $sqlUpdate = "update {$tablename} set ";
        foreach ($fieldValues as $field => $value) {
            $sqlUpdate .= " {$field} = '".$this->escapeString($value)."',";
            
        }
        $sqlUpdate = substr ($sqlUpdate,0, -1);
        $count = 0;
        foreach ($indexFieldValue as $field => $value) {
         if ($count == 0) { $sqlUpdate .= " where "; } else { $sqlUpdate .= " and "; }  
         $sqlUpdate .=  " {$field} = '".$this->escapeString($value)."'";
         $count++;
        }        
       
        return $this->exec ($sqlUpdate);
        
    }
    
    /**
     * An easy way to write a delete statement and execute it immediatelly
     * @param String $tablename Name of the table to run the delete on
     * @param Array $fieldValues The fields to filter by, used in  the where statement
     * @return bool Success or Failure
     */
    function delete ($tablename, $fieldValues) {
        $sqlDelete = "delete from {$tablename} ";
                
        $count = 0;
        foreach ($fieldValues as $field => $value) {
         if ($count == 0) { $sqlDelete .= " where "; } else { $sqlDelete .= " and "; }  
         $sqlDelete .=  " {$field} = '".$this->escapeString($value)."'";
         $count++;
        }        
        
        $result = $this->exec ($sqlDelete);
        
        $this->commit();
        
        return $result;
        
    }
    
    /*
      END  Commit
     * *************************************************************************** */
    /*     * *************************************************************************** 
      BEGIN Get Error - last database error
     */

    function getError() {
        $result = false;
        /* ODBC Connection */
        if ($this->dbtype == "odbc") {
            $result = @odbc_error($this->dbh) . " " . @odbc_errormsg($this->dbh);
        } else /* MSSQL srv native components */ if ($this->dbtype == "mssqlnative") {
            $result = @sqlsrv_errors();
        } else /* CUBRID */ if ($this->dbtype == "CUBRID") {
            $result = @cubrid_error_code() . " " . @cubrid_error_msg();
        } else /* SQLite */ if ($this->dbtype == "sqlite") {
            $result = @sqlite_error_string(sqlite_last_error($this->dbh));
        } else /* SQLite3 */ if ($this->dbtype == "sqlite3") {
            $result = $this->dbh->lastErrorMsg();
            if ($result == "not an error")
                $result = "";
        } else /* Firebird */ if ($this->dbtype == "firebird") {
            $result = @ibase_errmsg();
            
        } else /* Oracle */ if ($this->dbtype == "oracle") {
            $result = @oci_error();
        } else /* MySQL */ if ($this->dbtype == "mysql") {
            if (function_exists("mysqli_connect")) {
                $result = $this->dbh->error;
            } else {
                $result = @mysql_error($this->dbh);
            }
        } else /* Postgres */ if ($this->dbtype == "postgres") {
            $result = @pg_last_error($this->dbh);
        } else /* Microsoft SQL Server */ if ($this->dbtype == "mssql") {
            $result = @mssql_get_last_message();
        } else {
            trigger_error("Please implement " . __METHOD__ . " for " . $this->dbtype, E_USER_ERROR);
        }

        if ($result == "")
            $result = "No Error";
        $this->lasterror[count($this->lasterror)] = $result;

        return $result;
    }

    /*
      END  Get Error
     * *************************************************************************** */

    function getDataType($data) {
        if (!function_exists("is_datetime")) {
            function is_datetime($data) {
                if (date('Y-m-d H:i:s', strtotime($data)) == $data) {
                    return true;
                } else {
                    return false;
                }
            }

        }

        if (strpos($data, "-") !== false || strpos($data, "/") !== false) {
            if (is_datetime($data)) {
                $type = "DATETIME";
            } else
            if (is_numeric($data)) {
                $type = "NUMERIC";
            } else {
                $type = "VARCHAR";
            }
        } else
        if (is_numeric($data)) {
            $type = "NUMERIC";
        } else {
            $type = "VARCHAR";
        }

        
        return $type;
    }

    /*     * *************************************************************************** 
      BEGIN Get Row - Fetch a row in a number of formats
      $rowtype = 0 - Object
      1 - Array
      2 - Array Indexed by Field Name

      if $fetchblob is set to false then the blob id's are returned

      17/04/2012 - Added calculated fields


      $calculatedfields["field alias"] = 'php function name';

      updatefieldinfo should be turned off when doing sub selects to get information

     */

    function getRows($sql = "", $rowtype = 0, $fetchblob = true, $calculatedfields = array()) {
        $dataCheckSum = md5($sql.$rowtype);
        if (defined("TINA4_HAS_CACHE") && TINA4_HAS_CACHE !== false) {
            if (defined("TINA4_DISABLE_CACHE") && TINA4_DISABLE_CACHE === false) {
                if (!empty(xcache_get($dataCheckSum))) {
                   $data = unserialize(xcache_get ($dataCheckSum));
                   $this->fieldinfo = $data["fieldInfo"];
                   return $data["resultSet"]; 
                }
            }
        }
        //parse the calculated fields to normalize array
        $newarr = array();
        foreach ($calculatedfields as $cid => $calcfield) {
            $newarr[strtoupper($cid)] = $calcfield;
        }
        $calculatedfields = $newarr;
        $result = false;
        //Clear the field data for new query
        if ($this->updatefieldinfo) {
            unset($this->fieldinfo);
        } else { //store the field info
            $tempfieldinfo = $this->fieldinfo;
        }
        //Dont matter if there is no sql - use the last one.
        
        if ($sql == "") {
            $sql = $this->lastsql[count($this->lastsql) - 1];
        }    
        
        if (strpos($sql." ", "Resource") === false) {
          $sql = $this->parseSQL($sql);
        }
            
        /* ODBC Connection */
        if ($this->dbtype == "odbc") {
            //get odbc results
            $query = @odbc_exec($this->dbh, $sql);
            $icount = 0;
            switch ($rowtype) {
                case 0: //Object
                    while ($row = @odbc_fetch_object($query)) {
                        $result[$icount] = $row;
                        $icount++;
                    }
                    break;
                case 1: //Index
                    $icount = @odbc_num_rows($query);
                    for ($irow = 0; $irow < $icount; $irow++) {
                        @odbc_fetch_into($query, $row, $irow);
                        $result[$irow] = $row;
                    }
                    break;
                case 2: //Associative Index
                    while ($row = @odbc_fetch_array($query)) {
                        $result[$icount] = $row;
                        $icount++;
                    }
                    break;
            }
            $this->nooffields = @odbc_num_fields($query);
            $this->fieldinfo = [];
            //odbc field numbering starts at 1 we need it at 0 base
            for ($i = 1; $i <= $this->nooffields; $i++) {
                $column_name = @odbc_field_name($query, $i);
                $column_type = @odbc_field_type($query, $i);
                $column_size = @odbc_field_len($query, $i);
                $this->fieldinfo[$i - 1]["name"] = strtoupper($column_name);
                $this->fieldinfo[$i - 1]["alias"] = strtoupper($column_name);
                $this->fieldinfo[$i - 1]["length"] = $column_size;
                $this->fieldinfo[$i - 1]["type"] = strtoupper($column_type);
                $this->fieldinfo[$i - 1][1] = strtoupper($column_name);
                $this->fieldinfo[$i - 1][0] = strtoupper($column_name);
                $this->fieldinfo[$i - 1][2] = $column_size;
                $this->fieldinfo[$i - 1][4] = strtoupper($column_type);
            }
            $this->affectedrows = $icount;
        } else /* Microsoft SQL Server Native Drivers */ if ($this->dbtype == "mssqlnative") {
            //build an array of results
            $query = @sqlsrv_query($this->dbh, $sql);
            //echo $sql;     
            $icount = 0;
            switch ($rowtype) {
                case 0: //Object
                    while ($row = @sqlsrv_fetch_object($query)) {
                        $result[$icount] = $row;
                        $icount++;
                    }
                    break;
                case 1: //Index
                    while ($row = @sqlsrv_fetch_array($query, SQLSRV_FETCH_NUMERIC)) {
                        $result[$icount] = $row;
                        $icount++;
                    }
                    break;
                case 2: //Associative Index
                    while ($row = @sqlsrv_fetch_array($query, SQLSRV_FETCH_ASSOC)) {
                        $result[$icount] = $row;
                        $icount++;
                    }
                    break;
            }
            $this->nooffields = @sqlsrv_num_fields($query);
            $field_data = sqlsrv_field_metadata($query);
            $this->fieldinfo = [];

            foreach ($field_data as $i => $field) {
                //code to determine aliases - testing in mssql - needs to be applied to all databases
                //by Rudy Smith   changed by Andre, thanks for the idea Rudy
                $origcol = "";
                $origalias = explode(",", $sql);
                foreach ($origalias as $oid => $ovalue) {
                    if (strpos($ovalue, $field["Name"]) !== false) {
                        $fieldname = explode("as", $ovalue);
                        $origcol = trim($fieldname[0]);
                    }
                }
                if ($origcol == "") {
                    $origcol = strtoupper($field["Name"]);
                }
                $column_type = "";
                switch ($field["Type"]) {
                    case -4:
                        $column_type = "BLOB";
                        break;
                    case 5:
                    case 4:
                        $column_type = "INTEGER";
                        break;
                    case 12:
                    case -9:
                        $column_type = "VARCHAR";
                        break;
                    case 2:
                    case 3:
                        $column_type = "NUMERIC";
                        break;
                    case 6:
                        $column_type = "NUMERIC";
                        break;
                    case 91:
                        $column_type = "DATE";
                        break;
                    case -2:
                    case 93:
                        $column_type = "DATETIME";
                        break;
                    case 91:
                        $column_type = "DATETIME";
                        break;
                    default:
                        $column_type = "Fix please: " . $field["Type"];
                        break;
                }
                $this->fieldinfo[$i]["name"] = strtoupper($origcol);
                //echo "<br>";                                    
                $this->fieldinfo[$i]["alias"] = $field["Name"];
                $this->fieldinfo[$i]["length"] = $field["Precision"];
                $this->fieldinfo[$i]["type"] = strtoupper($column_type);
                $this->fieldinfo[$i][1] = strtoupper($column_name);
                $this->fieldinfo[$i][0] = strtoupper($column_name);
                $this->fieldinfo[$i][2] = $column_size;
                $this->fieldinfo[$i][4] = strtoupper($column_type);
            }
            $this->affectedrows = $icount;
        } else /* CUBRID */ if ($this->dbtype == "CUBRID") {
            //still need to check the different modes
            $query = @cubrid_execute($this->dbh, $sql);
            $icount = 0;
            switch ($rowtype) {
                case 0: //Object
                    while ($row = @cubrid_fetch($query, CUBRID_OBJECT)) {
                        $result[$icount] = $row;
                        $icount++;
                    }
                    break;
                case 1: //Index
                    while ($row = @cubrid_fetch($query, CUBRID_NUM)) {
                        $result[$icount] = $row;
                        $icount++;
                    }
                    break;
                case 2: //Associative Index
                    while ($row = @cubrid_fetch($query, CUBRID_ASSOC)) {
                        $result[$icount] = $row;
                        $icount++;
                    }
                    break;
            }
            //get the columns
            $this->nooffields = @cubrid_num_cols($query);
            $column_names = cubrid_column_names($query);
            $column_types = cubrid_column_types($query);
            //iterate the columns
            $this->fieldinfo = [];
            for ($i = 0; $i < $this->nooffields; $i++) {
                $this->fieldinfo[$i]["name"] = $column_names[$i];
                if ($column_types[$i] == "timestamp")
                    $column_types[$i] = "DATETIME";
                $this->fieldinfo[$i]["type"] = strtoupper($column_types[$i]);
                $this->fieldinfo[$i]["alias"] = ucwords(strtolower($this->fieldinfo[$i]["name"]));
                $this->fieldinfo[$i][1] = $this->fieldinfo[$i]["name"];
                $this->fieldinfo[$i][0] = $this->fieldinfo[$i]["alias"];
                //$this->fieldinfo[$i][2] = $column_size; //need to calculate this
                $this->fieldinfo[$i][4] = $this->fieldinfo[$i]["type"];
            }
            $this->affectedrows = $icount;
            //free up the results, Debby now has them in memory anyway
            @cubrid_free_result($this->dbh);
        } else /* SQLite */ if ($this->dbtype == "sqlite") {
            //build an array of results
            $query = @sqlite_query($this->dbh, $sql);
            $icount = 0;
            switch ($rowtype) {
                case 0: //Object
                    while ($row = @sqlite_fetch_object($query)) {
                        $result[$icount] = $row;
                        $icount++;
                    }
                    break;
                case 1: //Index
                    while ($row = @sqlite_fetch_array($query, SQLITE_NUM)) {
                        $result[$icount] = $row;
                        $icount++;
                    }
                    break;
                case 2: //Associative Index
                    while ($row = @sqlite_fetch_array($query, SQLITE_ASSOC)) {
                        $result[$icount] = $row;
                        $icount++;
                    }
                    break;
            }
            $this->nooffields = @sqlite_num_fields($query);
            $this->fieldinfo = [];
            for ($i = 0; $i < $this->nooffields; $i++) {
                $this->fieldinfo[$i]["name"] = @sqlite_field_name($query, $i);
                $this->fieldinfo[$i]["alias"] = ucwords(strtolower($this->fieldinfo[$i]["name"]));
                $this->fieldinfo[$i][1] = $this->fieldinfo[$i]["name"];
                $this->fieldinfo[$i][0] = $this->fieldinfo[$i]["alias"];
                //$this->fieldinfo[$i][2] = $column_size; //need to calculate this
                //$this->fieldinfo[$i][4] = $this->fieldinfo[$i]["type"];
            }
            $this->affectedrows = $icount;
        } else /* SQLite */ if ($this->dbtype == "sqlite3") {
            //build an array of results
            $query = $this->dbh->query($sql);
            if (!method_exists($query, "fetchArray")) {
                trigger_error("Cant get row for $this->dbpath in " . __METHOD__ . " for " . $this->dbtype, E_USER_NOTICE);
            }
            $icount = 0;
            if ($query) {
                switch ($rowtype) {
                    case 0: //Object
                        while ($temprow = $query->fetchArray(SQLITE3_ASSOC)) {
                            //make the row an object
                            unset($row);
                            $row = (object) [];
                            foreach ($temprow as $fieldname => $fieldvalue) {
                                if (empty($fieldvalue)) {
                                  $fieldvalue = ""; 
                                }
                                
                                $row->$fieldname = $fieldvalue;
                            }
                            $result[$icount] = $row;
                            $icount++;
                        }
                        break;
                    case 1: //Index
                        while ($row = $query->fetchArray(SQLITE3_NUM)) {
                            $result[$icount] = $row;
                            $icount++;
                        }
                        break;
                    case 2: //Associative Index
                        while ($row = $query->fetchArray(SQLITE3_ASSOC)) {
                            $result[$icount] = $row;
                            $icount++;
                        }
                        break;
                }
                $nooffields = $query->numColumns();
                $this->fieldinfo = [];
                
                if (is_object ($query)) {   
                    for ($i = 0; $i < $nooffields; $i++) {
                        $this->fieldinfo[$i]["name"] = (string) $query->columnName($i);
                        $this->fieldinfo[$i]["alias"] = ucwords(strtolower($this->fieldinfo[$i]["name"]));
                    
                        if (!empty ($result) > 0)  {
                            
                          eval('$this->fieldinfo[$i]["type"] = @$this->getDataType ($result[0]->' . $this->fieldinfo[$i]["name"] . ');');
                        }
                            else {
                              $this->fieldinfo[$i]["type"] = ""; 
                            }
                        $this->fieldinfo[$i][1] = $this->fieldinfo[$i]["name"];
                        $this->fieldinfo[$i][0] = $this->fieldinfo[$i]["alias"];
                        $this->fieldinfo[$i][2] = 20; //need to calculate this
                        $this->fieldinfo[$i][3] = null;
                        $this->fieldinfo[$i][4] = $this->fieldinfo[$i]["type"];
                    }
                }

                $this->affectedrows = $icount;
            }
        } else /* Firebird */ if ($this->dbtype == "firebird") {
            //build an array of results

            if (strpos($sql." ", "Resource") === false) {
               $query = @ibase_query($this->dbh, $sql);     
            }
               else {
              $query = $sql;      
            }     
            
            $icount = 0;
            $result = [];
            if ($query) {
                switch ($rowtype) {
                    case 0: //Object
                        while ($row = @ibase_fetch_object($query)) {
                            $result[$icount] = $row;
                            $icount++;
                        }
                        break;
                    case 1: //Index
                        while ($row = @ibase_fetch_row($query)) {
                            $result[$icount] = $row;
                            $icount++;
                        }
                        break;
                    case 2: //Associative Index
                        while ($row = @ibase_fetch_assoc($query)) {
                            $result[$icount] = $row;
                            $icount++;
                        }
                        break;
                }
            }



            $this->nooffields = @ibase_num_fields($query);
            $this->fieldinfo = [];
            for ($i = 0; $i < $this->nooffields; $i++) {
                $this->fieldinfo[$i] = @ibase_field_info($query, $i);
                //print_r ($this->fieldinfo[$i]);
                if ($this->fieldinfo[$i]["name"] == "") {
                    $this->fieldinfo[$i][0] = $this->fieldinfo[$i]["alias"];
                    $this->fieldinfo[$i]["name"] = $this->fieldinfo[$i]["alias"];
                }
            }

            $this->affectedrows = $icount;
        } else /* Oracle */ if ($this->dbtype == "oracle") {
            //build an array of results
            $query = @oci_parse($this->dbh, $sql);
            @oci_execute($query);
            $icount = 0;
            switch ($rowtype) {
                case 0: //Object
                    while ($row = @oci_fetch_object($query)) {
                        $result[$icount] = $row;
                        $icount++;
                    }
                    break;
                case 1: //Index
                    while ($row = @oci_fetch_row($query)) {
                        $result[$icount] = $row;
                        $icount++;
                    }
                    break;
                case 2: //Associative Index
                    while ($row = @oci_fetch_array($query, OCI_ASSOC)) {
                        $result[$icount] = $row;
                        $icount++;
                    }
                    break;
            }
        } else /* My SQL */ if ($this->dbtype == "mysql") {
            //build an array of results
            if (function_exists("mysqli_connect")) {
                $query = $this->dbh->query($sql);
                
               
                
                if (is_object($query)) {
                    $fields = $query->fetch_fields();
                    $this->nooffields = count($fields);
                    $mysql_data_type_hash = array(
                        1 => 'INTEGER',
                        2 => 'INTEGER',
                        3 => 'INTEGER',
                        4 => 'NUMERIC',
                        5 => 'NUMERIC',
                        7 => 'DATE',
                        8 => 'INTEGER',
                        9 => 'INTEGER',
                        10 => 'DATE',
                        11 => 'DATE',
                        12 => 'DATE',
                        13 => 'year',
                        16 => 'bit',
                        252 => 'BLOB',
                        253 => 'VARCHAR',
                        254 => 'VARCHAR',
                        246 => 'NUMERIC'
                    );
                    $i = 0;
                    $this->fieldinfo = [];
                    foreach ($fields as $field) {
                        $this->fieldinfo[$i]["name"] = strtoupper($field->orgname);
                        $this->fieldinfo[$i]["alias"] = strtoupper($field->name);
                        $this->fieldinfo[$i]["length"] = $field->length;
                        $this->fieldinfo[$i]["type"] = $mysql_data_type_hash[$field->type];
                        $this->fieldinfo[$i][0] = strtoupper($field->orgname);
                        $this->fieldinfo[$i][1] = strtoupper($field->name);
                        $this->fieldinfo[$i][2] = $field->length;
                        $this->fieldinfo[$i][3] = $field->length;
                        $this->fieldinfo[$i][4] = $mysql_data_type_hash[$field->type];
                        $i++;
                    }
                    
                    
                  
                    $icount = 0;
                    switch ($rowtype) {
                        case 0: //Object
                            while ($row = $query->fetch_object()) {
                                $result[$icount] = $row;
                                $icount++;
                            }
                            break;
                        case 1: //Index
                            while ($row = $query->fetch_row()) {
                                $result[$icount] = $row;
                                $icount++;
                            }
                            break;
                        case 2: //Associative Index
                            while ($row = $query->fetch_assoc()) {
                                $result[$icount] = $row;
                                $icount++;
                            }
                            break;
                    }
                    $this->affectedrows = $icount;
                    //free up the query
                    $query->close();
                } else {
                    $this->lasterror[count($this->lasterror)] = $this->dbh->error;
                }
            } else {
                $query = @mysql_query($sql);
                $icount = 0;
                switch ($rowtype) {
                    case 0: //Object
                        while ($row = @mysql_fetch_object($query)) {
                            $result[$icount] = $row;
                            $icount++;
                        }
                        break;
                    case 1: //Index
                        while ($row = @mysql_fetch_row($query)) {
                            $result[$icount] = $row;
                            $icount++;
                        }
                        break;
                    case 2: //Associative Index
                        while ($row = @mysql_fetch_array($query, mysql_ASSOC)) {
                            $result[$icount] = $row;
                            $icount++;
                        }
                        break;
                }
                $this->nooffields = @mysql_num_fields($query);
                $this->fieldinfo = [];
                for ($i = 0; $i < $this->nooffields; $i++) {
                    $column_name = @mysql_field_name($query, $i);
                    $column_type = @mysql_field_type($query, $i);
                    $column_size = @mysql_field_len($query, $i);
                    $fieldinfo = @mysql_fetch_field($query, $i);
                    $this->fieldinfo[$i]["name"] = strtoupper($column_name);
                    $this->fieldinfo[$i]["alias"] = strtoupper($column_name);
                    $this->fieldinfo[$i]["length"] = $column_size;
                    $this->fieldinfo[$i]["type"] = strtoupper($column_type);
                    $this->fieldinfo[$i][0] = strtoupper($column_name);
                    $this->fieldinfo[$i][1] = strtoupper($column_name);
                    $this->fieldinfo[$i][2] = $column_size;
                    $this->fieldinfo[$i][3] = $column_size;
                    $this->fieldinfo[$i][4] = strtoupper($column_type);
                }
                $this->affectedrows = $icount;
            }
        } else /* Postgres */ if ($this->dbtype == "postgres") {
            //build an array of results
            $query = @pg_query($this->dbh, $sql);
            $icount = 0;
            switch ($rowtype) {
                case 0: //Object
                    while ($row = @pg_fetch_object($query)) {
                        $result[$icount] = $row;
                        $icount++;
                    }
                    break;
                case 1: //Index
                    while ($row = @pg_fetch_row($query)) {
                        $result[$icount] = $row;
                        $icount++;
                    }
                    break;
                case 2: //Associative Index
                    while ($row = @pg_fetch_assoc($query)) {
                        $result[$icount] = $row;
                        $icount++;
                    }
                    break;
            }
            $this->nooffields = @pg_num_fields($query);
            $this->fieldinfo = [];
            for ($i = 0; $i < $this->nooffields; $i++) {
                $column_name = @pg_field_name($query, $i);
                $column_type = @pg_field_type($query, $i);
                $column_size = @pg_field_size($query, $i);
                $this->fieldinfo[$i]["name"] = strtoupper($column_name);
                $this->fieldinfo[$i]["alias"] = strtoupper($column_name);
                $this->fieldinfo[$i]["length"] = $column_size;
                $this->fieldinfo[$i]["type"] = strtoupper($column_type);
                $this->fieldinfo[$i][1] = strtoupper($column_name);
                $this->fieldinfo[$i][0] = strtoupper($column_name);
                $this->fieldinfo[$i][2] = $column_size;
                $this->fieldinfo[$i][3] = $column_size;
                $this->fieldinfo[$i][4] = strtoupper($column_type);
            }
            $this->affectedrows = $icount;
        } else /* Microsoft SQL Server */ if ($this->dbtype == "mssql") {
            //build an array of results
            $query = @mssql_query();
            //echo $sql;     
            $icount = 0;
            switch ($rowtype) {
                case 0: //Object
                    while ($row = @mssql_fetch_object($query)) {
                        $result[$icount] = $row;
                        $icount++;
                    }
                    break;
                case 1: //Index
                    while ($row = @mssql_fetch_row($query)) {
                        $result[$icount] = $row;
                        $icount++;
                    }
                    break;
                case 2: //Associative Index
                    while ($row = @mssql_fetch_assoc($query)) {
                        $result[$icount] = $row;
                        $icount++;
                    }
                    break;
            }
            $this->nooffields = @mssql_num_fields($query);
            $this->fieldinfo = [];
            for ($i = 0; $i < $this->nooffields; $i++) {
                $column_name = @mssql_field_name($query, $i);
                $column_type = @mssql_field_type($query, $i);
                $column_size = @mssql_field_length($query, $i);
                //code to determine aliases - testing in mssql - needs to be applied to all databases
                //by Rudy Smith
                $origalias = explode($column_name, $sql);
                $origcol = explode(" as ", $origalias[0]);
                $lastone = explode(" ", $origcol[count($originalcol)]);
                $origcol = $lastone[count($lastone) - 1];
                if ($origcol == '') {
                    $origcol = strtoupper($column_name);
                }
                $this->fieldinfo[$i]["name"] = strtoupper($origcol);
                //echo "<br>";
                $this->fieldinfo[$i]["alias"] = strtoupper($column_name);
                $this->fieldinfo[$i]["length"] = $column_size;
                $this->fieldinfo[$i]["type"] = strtoupper($column_type);
                $this->fieldinfo[$i][1] = strtoupper($column_name);
                $this->fieldinfo[$i][0] = strtoupper($column_name);
                $this->fieldinfo[$i][2] = $column_size;
                $this->fieldinfo[$i][3] = $column_size;
                $this->fieldinfo[$i][4] = strtoupper($column_type);
            }
            $this->affectedrows = $icount;
        } else {
            trigger_error("Please implement " . __METHOD__ . " for " . $this->dbtype, E_USER_ERROR);
        }

        
        if (!empty($this->fieldinfo)) {
            //create the field information based on the select statement    
           
            
            foreach ($this->fieldinfo as $id => $field) {
                if (strpos(strtoupper($field[4]), "NUMERIC") !== false || strpos(strtoupper($field[4]), "DECIMAL") !== false || strpos(strtoupper($field[4]), "INTEGER") !== false || strpos(strtoupper($field[4]), "INT") !== false) {
                    if (strpos(strtoupper($field[4]), "NUMERIC") !== false || strpos(strtoupper($field[4]), "DECIMAL") !== false) {
                        $field[4] = "CURRENCY";
                        $field["type"] = "CURRENCY";
                    }
                    $field[5] = "right";
                    $field["align"] = "right";
                } else {
                    $field[5] = "left";
                    $field["align"] = "left";
                }
                if ($field[3] >= 200) {
                    $field[6] = 180;
                    $field["htmllength"] = 180;
                } else if ($field[3] <= 100) {
                    $field[6] = 120;
                    $field["htmllength"] = 120;
                } else {
                    $field[6] = $field[3];
                    $field["htmllength"] = $field[3];
                }
                $this->fieldinfo[$id] = $field;
            }
        }
        
        $this->RAWRESULT = $result;

        /* Debugging for getRows */
        if (!empty($result)) {
            $newresult = [];
            //check the data
            //Make the object uppercase for all the field names which is our standard convention 
            //We also need to give back the appropriate
            if ($rowtype == 0) {

                foreach ($result as $id => $value) {
                    $newresult[$id] = new stdClass();
                    foreach ($value as $field => $fieldvalue) {
                        $fieldinfo = $this->getFieldByName($field);
                        if ($fetchblob) {
                            if ($this->dbtype == "CUBRID" && $fieldinfo["type"] == "BLOB") {
                                //file:/root/CUBRID/databases/FILEOMINT/lob/ces_058/sw_script.00001335346225392229_6061
                                $table = explode(".", $fieldvalue);
                                $table = explode("/", $table[0]);
                                $table = $table[count($table) - 1];
                                $fieldname = $this->fieldinfo[0]["name"];
                                eval('$fieldvalue = "select {$fieldinfo["name"]} from $table where {$this->fieldinfo[0]["name"]} = \'".$value->' . $fieldname . '."\'";');
                            }
                            if ($fieldinfo["type"] == "BLOB" || $fieldinfo["type"] == "OID")
                                $fieldvalue = $this->getBlob($fieldvalue);
                        }
                        if (( $fieldinfo["type"] == "DATETIME" || $fieldinfo["type"] == "DATE" || $fieldinfo["type"] == "TIMESTAMP" ) && $this->dbtype != "firebird") { //firebird doesn't need to have any adjustments here
                            if ($this->dbtype == "mssqlnative") {
                                foreach ($fieldvalue as $name => $value) {
                                    if ($name == "date") {
                                        $adate = $value;
                                    }
                                }
                                $fieldvalue = $this->translateDate($adate, $this->dbdateformat, $this->outputdateformat);
                                $adate = "";
                            } else {
                                $fieldvalue = $this->translateDate($fieldvalue, $this->dbdateformat, $this->outputdateformat);
                            }
                        }
                        $field = strtoupper($field);
                        if (isset($calculatedfields[strtoupper($fieldinfo["alias"])])) {
                            $this->updatefieldinfo = false;
                            eval('$newresult[$id]->$field = ' . $calculatedfields[strtoupper($fieldinfo["alias"])] . ';');
                            $this->updatefieldinfo = true;
                        } else {

                            $newresult[$id]->$field = $fieldvalue;
                        }
                    }
                }
                $result = $newresult;
            } else if ($rowtype == 1) { //We can't leave this out because we need to read blobs - so the fieldnames are not made uppercase
                foreach ($result as $id => $value) {
                    $newresult[$id] = [];
                    foreach ($value as $field => $fieldvalue) {
                        $fieldinfo = $this->fieldinfo[$field];
                        if ($this->dbtype == "CUBRID" && $fieldinfo["type"] == "BLOB") {
                            $table = explode(".", $fieldvalue);
                            $table = explode("/", $table[0]);
                            $table = $table[count($table) - 1];
                            $fieldname = $this->fieldinfo[0]["name"];
                            eval('$fieldvalue = "select {$fieldinfo["name"]} from $table where {$this->fieldinfo[0]["name"]} = \'".$value[0]."\'";');
                        }
                        if ($fetchblob) {
                            if ($fieldinfo["type"] == "BLOB" || $fieldinfo["type"] == "OID")
                                $fieldvalue = $this->getBlob($fieldvalue);
                        }
                        if (( $fieldinfo["type"] == "DATETIME" || $fieldinfo["type"] == "DATE" ) && $this->dbtype != "firebird") { //firebird doesn't need to have any adjustments here
                            if ($this->dbtype == "mssqlnative") {
                                foreach ($fieldvalue as $name => $value) {
                                    if ($name == "date") {
                                        $adate = $value;
                                    }
                                }
                                $fieldvalue = $this->translateDate($adate, $this->dbdateformat, $this->outputdateformat);
                                $adate = "";
                            } else {
                                $fieldvalue = $this->translateDate($fieldvalue, $this->dbdateformat, $this->outputdateformat);
                            }
                        }
                        if (isset($calculatedfields[strtoupper($fieldinfo["alias"])])) {
                            $this->updatefieldinfo = false;
                            eval('$newresult[$id][$field] = ' . $calculatedfields[strtoupper($fieldinfo["alias"])] . ';');
                            $this->updatefieldinfo = true;
                        } else {
                            $newresult[$id][$field] = $fieldvalue;
                        }
                    }
                }
                $result = $newresult;
            } else if ($rowtype == 2) {

                foreach ($result as $id => $value) {
                    $newresult[$id] = [];
                    foreach ($value as $field => $fieldvalue) {
                        $fieldinfo = $this->getFieldByName($field);
                        if ($this->dbtype == "CUBRID" && $fieldinfo["type"] == "BLOB") {
                            //file:/root/CUBRID/databases/FILEOMINT/lob/ces_058/sw_script.00001335346225392229_6061
                            $table = explode(".", $fieldvalue);
                            $table = explode("/", $table[0]);
                            $table = $table[count($table) - 1];
                            $fieldname = $this->fieldinfo[0]["name"];
                            eval('$fieldvalue = "select {$fieldinfo["name"]} from $table where {$this->fieldinfo[0]["name"]} = \'".$value[$fieldname]."\'";');
                        }
                        if ($fetchblob) {
                            if ($fieldinfo["type"] == "BLOB" || $fieldinfo["type"] == "OID")
                                $fieldvalue = $this->getBlob($fieldvalue);
                        }
                        if (( $fieldinfo["type"] == "DATETIME" || $fieldinfo["type"] == "DATE" ) && $this->dbtype != "firebird") { //firebird doesn't need to have any adjustments here
                            if ($this->dbtype == "mssqlnative") {
                                foreach ($fieldvalue as $name => $value) {
                                    if ($name == "date") {
                                        $adate = $value;
                                    }
                                }
                                $fieldvalue = $this->translateDate($adate, $this->dbdateformat, $this->outputdateformat);
                                $adate = "";
                            } else {
                                $fieldvalue = $this->translateDate($fieldvalue, $this->dbdateformat, $this->outputdateformat);
                            }
                        }
                        $field = strtoupper($field);
                        if (isset($calculatedfields[strtoupper($fieldinfo["alias"])])) {
                            $this->updatefieldinfo = false;
                            eval('$newresult[$id][$field] = ' . $calculatedfields[strtoupper($fieldinfo["alias"])] . ';');
                            $this->updatefieldinfo = true;
                        } else {
                            $newresult[$id][$field] = $fieldvalue;
                        }
                    }
                }




                $result = $newresult;
            }
        }

        if (!$this->updatefieldinfo) {
            $this->fieldinfo = $tempfieldinfo;
        }
        
        if (defined("TINA4_HAS_CACHE") && TINA4_HAS_CACHE !== false && !empty($this->fieldinfo)) {
            if (defined("TINA4_DISABLE_CACHE") && TINA4_DISABLE_CACHE === false) {
                xcache_set($dataCheckSum, serialize( ["fieldInfo" => $this->fieldinfo, "resultSet" => $result ] ) );
            }
        }
        return $result;
    }

    /*
      END  Get Row
     * *************************************************************************** */
    /*     * *************************************************************************** 
      BEGIN Translate date - Change a date from the data to output format specified in the connection file
     */

    function translateDate($input, $dbdateformat, $outputdateformat) {
        if (empty($dbdateformat)) {
          $dbdateformat = $this->dbdateformat;  
        }
        if (empty ($outputdateformat)) {
          $outputdateformat = $this->outputdateformat;  
        }
        //mssql returns dates as a object
        if ($input != "" && !is_object($input)) {
            $split = explode(" ", $input); //get date and minutes
            $result = explode(" ", $outputdateformat);
            $result = $result[0]; //just the date part
            $YYto = strpos($outputdateformat, "YYYY");
            $YYin = strpos($dbdateformat, "YYYY");
            $mmto = strpos($outputdateformat, "mm");
            $mmin = strpos($dbdateformat, "mm");
            $ddto = strpos($outputdateformat, "dd");
            $ddin = strpos($dbdateformat, "dd");
            $result[$mmto] = $input[$mmin];
            $result[$mmto + 1] = $input[$mmin + 1];
            $result[$ddto] = $input[$ddin];
            $result[$ddto + 1] = $input[$ddin + 1];
            $result[$YYto] = $input[$YYin];
            $result[$YYto + 1] = $input[$YYin + 1];
            $result[$YYto + 2] = $input[$YYin + 2];
            $result[$YYto + 3] = $input[$YYin + 3];
            if (!empty($split[1])) {
                $result .= " " . $split[1]; //add the time piece to the end
            }
            $result = trim($result);
        } else {
            $result = "";
        }
        return $result;
    }

    /*
      END  Translate date
     * *************************************************************************** */
    /*     * *************************************************************************** 
      BEGIN Get Blob - Get the data from a blob like in Firebird - System function
     */
    function getBlob($column) {
        $content = "";
        if ($column && $this->dbtype == "CUBRID") {
            $req = @cubrid_execute($this->dbh, $column);
            $row = @cubrid_fetch_row($req, CUBRID_LOB);
            $content = "";
            while (true) {
                if ($data = cubrid_lob2_read($row[0], 1024)) {
                    $content .= $data;
                } elseif ($data === false) {
                    break;
                } else {
                    break;
                }
            }
        } else if ($column && $this->dbtype == "firebird") {
            //Get the blob information
            $blob_data = ibase_blob_info($this->dbh, $column);
            //Get a handle to the blob
            $blob_hndl = ibase_blob_open($this->dbh, $column);
            //Get the blob contents
            $content = ibase_blob_get($blob_hndl, $blob_data[0]);
        } else if ($column && $this->dbtype == "postgres") {
            //This may kill performance finding the size of the blob - but how else ???
            pg_query($this->dbh, "begin");
            $handle = pg_lo_open($this->dbh, $column, "r");
            //Find the end of the blob
            pg_lo_seek($handle, 0, PGSQL_SEEK_END);
            $size = pg_lo_tell($handle);
            //Find the beginning of the blob
            pg_lo_seek($handle, 0, PGSQL_SEEK_SET);
            //Read the whole blob
            $content = pg_lo_read($handle, $size);
            pg_query($this->dbh, "commit");
        } else { //All other databases ???
            $content = $column;
        }
        return $content;
    }

    /*
      END  Get Blob
     * *************************************************************************** */
    /*     * *************************************************************************** 
      BEGIN Set Blob - Set the data to a blob like in Firebird, MySQL, Postgres
     */

    function setBlob($tablename, $column, $blobvalue, $filter = "fieldname = 0") {
        $result = "";
        if ($column && $this->dbtype == "odbc") {
            $sqlupdate = "update $tablename set $column = ? where $filter";
            $result = $this->exec($sqlupdate, $blobvalue);
        } else if ($column && $this->dbtype == "CUBRID") {
            $sqlupdate = "update $tablename set $column = ? where $filter";
            $result = $this->exec($sqlupdate, $blobvalue);
        } else if ($column && $this->dbtype == "sqlite") {
            $sqlupdate = "update $tablename set $column = '" . sqlite_escape_string($blobvalue) . "' where $filter";
            $result = $this->exec($sqlupdate);
        } else if ($column && $this->dbtype == "sqlite3") {
            $sqlupdate = "update $tablename set $column = :blob where $filter";
            $query = $this->dbh->prepare($sqlupdate);
            $query->bindValue(":blob", $blobvalue, SQLITE3_BLOB);
            $result = $query->execute();
        } else if ($column && $this->dbtype == "firebird") {
            $sqlupdate = "update $tablename set $column = ? where $filter";
            $result = $this->exec($sqlupdate, $blobvalue);
        } else if ($column && $this->dbtype == "mysql") {
            if (function_exists("mysqli_connect")) {
                $sqlupdate = "update $tablename set $column = ? where $filter";
                $query = $this->dbh->prepare($sqlupdate);
                $null = NULL;
                $query->bind_param("b", $null);
                $query->send_long_data(0, $blobvalue);
                $query->execute();
            } else {
                $sqlupdate = "update $tablename set $column = 0x" . bin2hex($blobvalue) . " where $filter";
                $result = $this->exec($sqlupdate);
            }
        } else if ($column && $this->dbtype == "postgres") {
            pg_query($this->dbh, "begin");
            $oid = pg_lo_create($this->dbh);
            $handle = pg_lo_open($this->dbh, $oid, "w");
            pg_lo_write($handle, $blobvalue);
            pg_lo_close($handle);
            pg_query($this->dbh, "commit");
            $sqlupdate = "update $tablename set $column = '$oid' where $filter";
            $result = $this->exec($sqlupdate);
        } else if ($column && $this->dbtype == "mssql" || $this->dbtype == "mssqlnative") {
            $sqlupdate = "update $tablename set $column = 0x" . bin2hex($blobvalue) . " where $filter";
            $result = $this->exec($sqlupdate);
        }
        return $result;
    }

    /*
      END  Set Blob
     * *************************************************************************** */
    /*     * *************************************************************************** 
      BEGIN Get Value - Fetch a row in a number of formats
     */

    function getRow($sql = "", $id = 0, $rowtype = 0, $fetchblob = true) {
        $result = false;
        //Dont matter if there is no sql - use the last one.
        if ($sql == "") {
            $sql = $this->lastsql[count($this->lastsql) - 1];
        }
               
        $result = $this->getRows($sql, $rowtype, $fetchblob);
        
        if (!empty($result)) {
            $result = $result[$id]; //return the first value
        }

        return $result;
    }

    /*
      END  Get Value
     * *************************************************************************** */

    /**
     * This function returns a value key pair of an sql statement based on 2 records. The first record retrieved will be the key and the second will be the value
     * @param type $sql
     * @return type Array of Objects
     */
    function getKeyValue ($sql="") {
        $results = $this->getRows($sql, DEB_ARRAY);
        $keyValues = array();
        
        if(!empty($results)){
            foreach ($results as $rid => $result) {
              $keyValues[$result[0]] = $result[1];  
            }
        }
                      
        return $keyValues;
    }
    
    /*     * *************************************************************************** 
      BEGIN get_database
      Returns the layout of the whole database in an easy to use array

      Need to add support for views & stored procedures later on
    */

    function getDatabase() {
        $result = false;
        $database = null;
        if (!$this->dbh) {
            trigger_error("No database handle, use connect first in " . __METHOD__ . " for " . $this->dbtype, E_USER_WARNING);
        } else if ($this->dbtype == "odbc") {
            
        } else if ($this->dbtype == "CUBRID") {
            $sqltables = "SELECT class_name as name 
                    FROM db_class 
                    WHERE is_system_class = 'NO'
                    AND class_name <> '_cub_schema_comments'
                    ";
            $tables = $this->getRows($sqltables);
            foreach ($tables as $id => $record) {
                $sqlinfo = "select * from $record->NAME limit 1";
                $tableinfo = $this->getRows($sqlinfo);
                $fieldinfo = $this->fieldinfo;
                foreach ($fieldinfo as $tid => $trecord) {
                    $database[trim($record->NAME)][$tid]["column"] = $tid;
                    $database[trim($record->NAME)][$tid]["field"] = trim($trecord["name"]);
                    $database[trim($record->NAME)][$tid]["type"] = trim($trecord["type"]);
                    $database[trim($record->NAME)][$tid]["default"] = "";
                    $database[trim($record->NAME)][$tid]["notnull"] = "";
                    $database[trim($record->NAME)][$tid]["pk"] = "";
                }
            }
            $result = $database;
        } else /* SQLite & SQLite3 */ if ($this->dbtype == "sqlite" || $this->dbtype == "sqlite3") {
            $sqltables = "select name 
                      from sqlite_master
                     where type='table'
                  order by name";
            $tables = $this->getRows($sqltables);
            
            if (!empty($tables)) {
                foreach ($tables as $id => $record) {
                    $sqlinfo = "pragma table_info($record->NAME);";
                    $tableinfo = $this->getRows($sqlinfo);
                    //Go through the tables and extract their column information
                    foreach ($tableinfo as $tid => $trecord) {
                        $database[trim($record->NAME)][$tid]["column"] = trim($trecord->CID);
                        $database[trim($record->NAME)][$tid]["field"] = trim($trecord->NAME);
                        $database[trim($record->NAME)][$tid]["type"] = trim($trecord->TYPE);
                        $database[trim($record->NAME)][$tid]["default"] = trim($trecord->DFLT_VALUE);
                        $database[trim($record->NAME)][$tid]["notnull"] = trim($trecord->NOTNULL);
                        $database[trim($record->NAME)][$tid]["pk"] = trim($trecord->PK);
                    }
                }
            }
            $result = $database;
        } else /* Firebird */ if ($this->dbtype == "firebird") {
            $sqltables = 'select distinct rdb$relation_name as tablename
                      from rdb$relation_fields
                     where rdb$system_flag=0
                       and rdb$view_context is null';
            $tables = $this->getRows($sqltables);
            foreach ($tables as $id => $record) {
                $sqlinfo = 'SELECT r.RDB$FIELD_NAME AS field_name,
                           r.RDB$DESCRIPTION AS field_description,
                           r.RDB$DEFAULT_VALUE AS field_default_value,
                           r.RDB$NULL_FLAG AS field_not_null_constraint,
                           f.RDB$FIELD_LENGTH AS field_length,
                           f.RDB$FIELD_PRECISION AS field_precision,
                           f.RDB$FIELD_SCALE AS field_scale,
                           CASE f.RDB$FIELD_TYPE
                              WHEN 261 THEN \'BLOB\'
                              WHEN 14 THEN \'CHAR\'
                              WHEN 40 THEN \'CSTRING\'
                              WHEN 11 THEN \'D_FLOAT\'
                              WHEN 27 THEN \'DOUBLE\'
                              WHEN 10 THEN \'FLOAT\'
                              WHEN 16 THEN \'INT64\'
                              WHEN 8 THEN \'INTEGER\'
                              WHEN 9 THEN \'QUAD\'
                              WHEN 7 THEN \'SMALLINT\'
                              WHEN 12 THEN \'DATE\'
                              WHEN 13 THEN \'TIME\'
                              WHEN 35 THEN \'TIMESTAMP\'
                              WHEN 37 THEN \'VARCHAR\'
                              ELSE \'UNKNOWN\'
                            END AS field_type,
                            f.RDB$FIELD_SUB_TYPE AS field_subtype,
                            coll.RDB$COLLATION_NAME AS field_collation,
                            cset.RDB$CHARACTER_SET_NAME AS field_charset
                       FROM RDB$RELATION_FIELDS r
                       LEFT JOIN RDB$FIELDS f ON r.RDB$FIELD_SOURCE = f.RDB$FIELD_NAME
                       LEFT JOIN RDB$COLLATIONS coll ON r.RDB$COLLATION_ID = coll.RDB$COLLATION_ID
                        AND f.RDB$CHARACTER_SET_ID = coll.RDB$CHARACTER_SET_ID
                       LEFT JOIN RDB$CHARACTER_SETS cset ON f.RDB$CHARACTER_SET_ID = cset.RDB$CHARACTER_SET_ID
                      WHERE r.RDB$RELATION_NAME = \'' . $record->TABLENAME . '\'
                    ORDER BY r.RDB$FIELD_POSITION';
                $tableinfo = $this->getRows($sqlinfo);
                //Go through the tables and extract their column information
                foreach ($tableinfo as $tid => $trecord) {
                    $database[trim($record->TABLENAME)][$tid]["column"] = $tid;
                    $database[trim($record->TABLENAME)][$tid]["field"] = trim($trecord->FIELD_NAME);
                    $database[trim($record->TABLENAME)][$tid]["description"] = trim($trecord->FIELD_DESCRIPTION);
                    $database[trim($record->TABLENAME)][$tid]["type"] = trim($trecord->FIELD_TYPE);
                    $database[trim($record->TABLENAME)][$tid]["length"] = trim($trecord->FIELD_LENGTH);
                    $database[trim($record->TABLENAME)][$tid]["precision"] = trim($trecord->FIELD_PRECISION);
                    $database[trim($record->TABLENAME)][$tid]["default"] = trim($trecord->FIELD_DEFAULT_VALUE);
                    if (!empty($trecord->NOTNULL)) {
                        $database[trim($record->TABLENAME)][$tid]["notnull"] = trim($trecord->NOTNULL);
                    }
                    if (!empty($trecord->PK)) {
                        $database[trim($record->TABLENAME)][$tid]["pk"] = trim($trecord->PK);
                    }
                }
            }
            $result = $database;
        } else /* Oracle */ if ($this->dbtype == "oracle") {
            $result = true;
        } else /* MySQL */ if ($this->dbtype == "mysql") {
            $dbpath = explode(":", $this->dbpath);
            $sqltables = "SELECT table_name, table_type, engine
                      FROM INFORMATION_SCHEMA.tables
                     WHERE upper(table_schema) = upper('{$dbpath[1]}')
                     ORDER BY table_type ASC, table_name DESC";
            $tables = $this->getRows($sqltables);
            if (!empty($tables)) {
                foreach ($tables as $id => $record) {
                    $sqlinfo = 'show columns from ' . $record->TABLE_NAME;
                    $tableinfo = $this->getRows($sqlinfo);
                    //Go through the tables and extract their column information
                    foreach ($tableinfo as $tid => $trecord) {
                        //split the length & type for field
                        if (strpos($trecord->TYPE, "(") !== false) {
                            $type = substr($trecord->TYPE, 0, strpos($trecord->TYPE, "("));
                            $length = substr($trecord->TYPE, strpos($trecord->TYPE, "(") + 1, strpos($trecord->TYPE, ")") - strpos($trecord->TYPE, "(") - 1);
                        } else {
                            $type = $trecord->TYPE;
                        }
                        $database[trim($record->TABLE_NAME)][$tid]["column"] = $tid;
                        $database[trim($record->TABLE_NAME)][$tid]["field"] = trim($trecord->FIELD);
                        $database[trim($record->TABLE_NAME)][$tid]["description"] = trim($trecord->EXTRA);
                        $database[trim($record->TABLE_NAME)][$tid]["type"] = trim($type);
                        $database[trim($record->TABLE_NAME)][$tid]["length"] = trim($length);
                        $database[trim($record->TABLE_NAME)][$tid]["precision"] = "";
                        $database[trim($record->TABLE_NAME)][$tid]["default"] = trim($trecord->DEFAULT);
                        $database[trim($record->TABLE_NAME)][$tid]["notnull"] = trim($trecord->NULL);
                        $database[trim($record->TABLE_NAME)][$tid]["pk"] = trim($trecord->KEY);
                    }
                }
                $result = $database;
            }
                else {
                    $result = null;
                }
            
            
        } else /* Postgres */ if ($this->dbtype == "postgres") {
            $dbpath = explode(":", $this->dbpath);
            $sqltables = "SELECT table_name
                      FROM INFORMATION_SCHEMA.tables
                     WHERE upper(table_catalog) = upper('{$dbpath[1]}')
                      AND upper(table_schema) = upper('public') 
                     ORDER BY table_type ASC, table_name DESC";
            $tables = $this->getRows($sqltables);
            foreach ($tables as $id => $record) {
                $sqlinfo = "select * from INFORMATION_SCHEMA.columns where upper(table_name) = upper('$record->TABLE_NAME')";
                $tableinfo = $this->getRows($sqlinfo);
                //Go through the tables and extract their column information
                foreach ($tableinfo as $tid => $trecord) {
                    $database[trim($record->TABLE_NAME)][$tid]["column"] = $tid;
                    $database[trim($record->TABLE_NAME)][$tid]["field"] = trim(strtoupper($trecord->COLUMN_NAME));
                    $database[trim($record->TABLE_NAME)][$tid]["description"] = "";
                    $database[trim($record->TABLE_NAME)][$tid]["type"] = trim(strtoupper($trecord->UDT_NAME));
                    $database[trim($record->TABLE_NAME)][$tid]["length"] = trim(strtoupper($trecord->CHARACTER_MAXIMUM_LENGTH));
                    $database[trim($record->TABLE_NAME)][$tid]["precision"] = trim(strtoupper($trecord->NUMERIC_PRECISION));
                    $default = explode("::", $trecord->COLUMN_DEFAULT);
                    $database[trim($record->TABLE_NAME)][$tid]["default"] = $default[0];
                    $database[trim($record->TABLE_NAME)][$tid]["notnull"] = trim(strtoupper($trecord->IS_NULLABLE));
                    $database[trim($record->TABLE_NAME)][$tid]["pk"] = "";
                }
            }
            $result = $database;
        } else /* Microsoft SQL Server */ if ($this->dbtype == "mssql" || $this->dbtype == "mssqlnative") {
            $tables = $this->getRows("sp_tables @table_type = \"'table'\"");
            foreach ($tables as $id => $record) {
                $columns = $this->getRows("sp_columns $record->TABLE_NAME");
                foreach ($columns as $tid => $trecord) {
                    $database[trim($record->TABLE_NAME)][$tid]["column"] = $tid;
                    $database[trim($record->TABLE_NAME)][$tid]["field"] = trim(strtoupper($trecord->COLUMN_NAME));
                    $database[trim($record->TABLE_NAME)][$tid]["description"] = trim(strtoupper($trecord->REMARKS));
                    $database[trim($record->TABLE_NAME)][$tid]["type"] = trim(strtoupper($trecord->TYPE_NAME));
                    $database[trim($record->TABLE_NAME)][$tid]["length"] = trim(strtoupper($trecord->LENGTH));
                    $database[trim($record->TABLE_NAME)][$tid]["precision"] = trim(strtoupper($trecord->PRECISION));
                    $database[trim($record->TABLE_NAME)][$tid]["default"] = "";
                    $database[trim($record->TABLE_NAME)][$tid]["notnull"] = trim(strtoupper($trecord->IS_NULLABLE));
                    $database[trim($record->TABLE_NAME)][$tid]["pk"] = "";
                }
            }
            $result = $database;
        } else {
            trigger_error("Please implement " . __METHOD__ . " for " . $this->dbtype, E_USER_ERROR);
        }
        
        return $result;
    }

    /*
      END  Get Database
     * *************************************************************************** */
    /*     * *************************************************************************** 
      BEGIN Get Field Info - Fetch basic field info

      result = Array (
      ["alias"] = Alias
      ["name"] = Name
      ["type"] = Generic field type
      ["width"] = Column width
      )

     */

    function getFieldInfo($sql = "") {
        $result = 0;
        if ($sql == "") {
            $result = $this->fieldinfo;
        } else {
            $this->getRows($sql);
            $result = $this->fieldinfo;
        }
        return $result;
    }

    /**   BEGIN Get Affected Rows
      Get the number of rows changed or retrieved by last SQL
     */
    function getAffectedRows($sql = "") {
        $result = 0;
        if ($sql == "") {
            $result = $this->affectedrows;
        } else {
            $this->getRows($sql);
            $result = $this->affectedrows;
        }
        return $result;
    }

    /**
      BEGIN Get Field Info By Name
      Get a fields info by name
     */
    function getFieldByName($fieldname = "") {
        foreach ($this->fieldinfo as $id => $value) {
            if (strtoupper($fieldname) == strtoupper($value["alias"])) {
                return $value;
                break;
            }
        }
        return null;
    }

    /**
      BEGIN Get Random ID
      Get a random id function and adding 1
     */
    function getRandomId($noofchars) {
        $result = "";
        $result = hash('ripemd160', rand(100000, 9999999));
        $result = substr($result, 0, $noofchars - 1);
        return $result;
    }

    /**
      BEGIN Get Next ID
      Get the next id on a table by using the MAX function and adding 1
     */
    function getNextId($tablename = "", $fieldname = "", $filter = "") {
        $result = "";
        if ($filter != "")
            $filter = " where $filter"; //we may need to filter our tables
        if (is_string($fieldname)) {
            $sql = "select max($fieldname)+1 as \"NEXTID\" from $tablename $filter";
            $sql = $this->parseSQL($sql);
            $row = $this->getRow($sql);
            $row = $this->RAWRESULT[0];
            if ($row->NEXTID == "") {
                $row->NEXTID = 0;
            }

            $result = $row->NEXTID;
        }
        return $result;
    }

    /**
      BEGIN get_table_exists()
      See if a certain table exists
     */
    function getTableExists($tablename = "") {
        $result = "";
        $result = $this->get_database();
        if ($result[$tablename]) {
            return true;
        } else {
            return false;
        }
    }

    /**
      BEGIN Date to DB
      This function will format the date as needed by the database
     */
    function dateToDb($invalue) {
        //echo $invalue."<br>";
        if ($invalue != "") { //works only for firebird currently
            $avalue = $this->translateDate($invalue, $this->outputdateformat, $this->dbdateformat);
            return $avalue;
        } else {
            return "null";
        }
    }

    /**
      BEGIN Get Insert SQL
      This function attempts to eliminate errors by creating the insert statements using prefixed input fields
      If you have a form with inputs prefixed with "txt" for example it will chop off the txt and make an insert statement
      Field names need to be in uppercase for better processing

      <form>
      <input type="text" name="txtNAME" value="Andre">
      <input type="text" name="txtDATE" value="01/10/2010">
      </form>

     */
    function getInsertSQL($fieldprefix = "edt", //Field prefix as discussed above 
            $tablename = "", //Name of the tablename
            $primarykey = "", //Field name of the primary key
            $genkey = true, //Generate a new number using inbuilt get_next_id 
            $requestvar = "", //Request variable to populate with new id for post processing
            $passwordfields = "", //Fields that may be crypted automatically
            $datefields = "", //Fields that may be seen as date fields and converted accordingly
            $exec = false, 
            $arrayindex = 0) {
        //error_reporting(0);
        //Get the length of field prefix
        $prefixlen = strlen($fieldprefix);
        //Start the insert statement      
        
        if ( is_string($primarykey) && $genkey) {
            $newid = $this->getNextId($tablename, $primarykey);
            $_REQUEST[$requestvar] = $newid;
            $sqlinsert = "insert into $tablename ($primarykey";
        } else {
            $newid = "";
            if ( is_string($primarykey) ) {
                $newid = $_REQUEST[$fieldprefix . strtoupper($primarykey)];
                
            }
            $_REQUEST[$requestvar] = $newid;
            
            $sqlinsert = "insert into $tablename (";
        }
        
      
        //Search all the fields on the form
        foreach ($_REQUEST as $name => $value) {
            if (substr($name, 0, $prefixlen) == $fieldprefix) {
                $sqlinsert .= ", " . strtoupper(substr($name, $prefixlen, strlen($name) - $prefixlen));
            }
        }
        //Check if must add the generated primary key value  
        if ($genkey) {
            $sqlinsert .= ") values ($newid";
        } else {
            $sqlinsert .= ") values (";
        }
        foreach ($_REQUEST as $name => $value) {
            if (substr($name, 0, $prefixlen) == $fieldprefix) {
                //if ($value == "on") $value = 1;
                //$value = stripcslashes ($value);
                if (is_array($value)) {
                    $value = $value[$arrayindex];
                }
                $value = str_replace("'", "''", $value);
                $tempfields = explode(",", $passwordfields);


                foreach ($tempfields as $id => $fieldname) { //Look for password fields
                    if (trim(strtoupper($name)) === trim(strtoupper($fieldprefix . $fieldname))) {
                        $value = crypt($value);
                    }
                }
                $tempfields = explode(",", $datefields);
                foreach ($tempfields as $id => $fieldname) { //Look for date fields and convert them
                    if ($name == $fieldprefix . strtoupper($fieldname)) {
                        $value = $this->dateToDb($value);
                    }
                }
                $sqlinsert .= ", '" . $value . "'";
            }
        }
        $sqlinsert .= ")";
        //Clean up the sql
        $sqlinsert = str_replace("(,", "(", $sqlinsert);
        $sqlinsert = str_replace("'null'", "null", $sqlinsert);
        
        if (!$exec) { //Do we run the procedure execution 
            return $sqlinsert;
        } else {
            //Run the insert statement and upload files while we are at it.
            
            $error = $this->exec($sqlinsert);
            if (!$error) {
                $error = $this->getError();
                if ($error === "No Error") {
                    $error = "Failed: " . $sqlinsert;
                }
            } else {
                $error = "No Error";
            }



            $this->runFileUploads($fieldprefix, $tablename, $_REQUEST[$requestvar], $primarykey);
            return $error;
        }
    }
    
    /**
     * Function to upload files based on prefixes and column names
     * 
     * @param String $fieldprefix The prefix of the form input - example txtFILE
     * @param String $tablename The name of the table in the database
     * @param String $primarykeyValue The value of the index in the table
     * @param String $primarykey The name of the index field of the table
     * @return boolean Returns true every time 
     */

    function runFileUploads($fieldprefix="edt", $tablename="", $primarykeyValue=0, $primarykey="ID") {
        if ($_FILES) {
            $prefixlen = strlen ($fieldprefix);    
            foreach ($_FILES as $name => $value) {
                    if ($value["tmp_name"] != "") {
                        
                        if (substr($name, 0, $prefixlen) == $fieldprefix) {
                            //upload the file correctly into a blob field
                            $this->setBlob($tablename, strtoupper(substr($name, $prefixlen, strlen($name) - $prefixlen)), file_get_contents($value["tmp_name"]), $filter = "$primarykey = '" .$primarykeyValue. "'");                                                    
                        }
                    }
                }
            }
        
        return true;
    }
    
    
    /**
      BEGIN Get Update SQL
      This function attempts to eliminate errors by creating the update statements using prefixed input fields
      If you have a form with inputs prefixed with "txt" for example it will chop off the txt and make an update statement
      Field names need to be in uppercase for better processing

      <form>
      <input type="text" name="txtNAME" value="Andre">
      <input type="text" name="txtDATE" value="01/10/2010">
      </form>

     */
    function getUpdateSQL($fieldprefix = "edt", //Field prefix as discussed above 
            $tablename = "", //Name of the tablename
            $primarykey = "", //Field name of the primary key
            $index = "", //Index 
            $requestvar = "", //Request variable to populate with new id for post processing
            $passwordfields = "", //Fields that may be crypted automatically
            $datefields = "", //Fields that may be seen as date fields and converted accordingly 
            $exec = false, //Execute the command immediately
            $arrayindex = 0 //If a request field is an array - which index to use 
    ) {
        //Get the length of field prefix
        error_reporting(0);
        $prefixlen = strlen($fieldprefix);
        $sqlupdate = "update $tablename set 0=0 ";
        foreach ($_REQUEST as $name => $value) {
            //we need to see if we are dealing with a multiple update
            if (substr($name, 0, $prefixlen) == $fieldprefix) {
                //print_r ($value);
                if (is_array($value)) {
                    $value = $value[$arrayindex];
                }

                if ($value == "on")
                    $value = 1;
                $tempfields = explode(",", $passwordfields);
                $dontupdate = false;
                foreach ($tempfields as $id => $fieldname) { //Look for password fields
                    if ($name == $fieldprefix . strtoupper($fieldname)) {
                        if ($value != "") { //only if there is a password do we encrypt it
                            $value = crypt($value);
                        } else {
                            $dontupdate = true; //we must not update an empty password
                        }
                    }
                }
                $tempfields = explode(",", $datefields);
                foreach ($tempfields as $id => $fieldname) { //Look for date fields and convert them
                    if ($name == $fieldprefix . strtoupper($fieldname)) {
                        $value = $this->dateToDb($value);
                    }
                }
                //$value = stripcslashes ($value);
                $value = str_replace("'", "''", $value);
                if (!$dontupdate) {
                    $sqlupdate .= ", " . strtoupper(substr($name, $prefixlen, strlen($name) - $prefixlen)) . " = '" . $value . "'";
                }
            }
        }
        
        if (!empty($index)) {
          $sqlupdate .= " where $primarykey = '" . $index . "'";
        }
          else {
            $sqlupdate .= " where $primarykey";  
          }      
        
        $sqlupdate = str_replace("0=0 ,", "", $sqlupdate);
        $sqlupdate = str_replace("'null'", "null", $sqlupdate);
        $this->lastsql[count($this->lastsql)] = $sqlupdate;

        if (!$exec) { //Do we run the procedure execution
            return $sqlupdate;
        } else {
            //Run the insert statement and upload files while we are at it.
            //file_put_contents ("sql.txt", $sqlupdate);
            $error = $this->exec($sqlupdate);
            
            if (!$error) {
                $error = $this->getError();
                if ($error === "No Error") {
                    $error = "Failed: " . $sqlupdate;
                }
            } else {
                $error = "No Error";
            }
            
            
            $this->runFileUploads($fieldprefix, $tablename, $index, $primarykey);
            
            return $error;
        }
    }
    
    /**
     * A function to generate a CSV from an SQL statement
     * @param String $sql An SQL string
     * @param String $filename The name of the file you want
     * @param String $delim An optional delimeter - default is ','
     * @return String CSV file
     */
    function generateCsv($sql, $filename = "temp.csv", $delim=","){
        $results = $this->getRows($sql, DEB_ARRAY);
        if(!empty($results)){
            // make csv file
            header("Content-Type: text/csv");
            header("Content-Disposition: attachment; filename = {$filename}");
            
            $fh = fopen("php://output", "w");
            fputcsv($fh, array_map(function($element) { return $element['alias']; }, $this->fieldinfo), $delim); 
            
            foreach($results as $result){
                 
                fputcsv($fh, $result, $delim); 

            }
            
            fclose($fh);
            
        }
        return;
    }

}