<?php
/**
 * Routing system for the framework to handle HTTP requests
 *
Installation on NGINX

location / {
root   web_root;
try_files $uri $uri/ /index.php;
index  index.php index.html index.htm;
}

Installation on Apache
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]

 * @author Andre van Zuydam <andrevanzuydam@gmail.com>
 * @license GPL
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

/**
 * Defined variable to ignoring routes, used with add route
 */
define ("RUTH_IGNORE_ROUTE", true);
define ("RUTH_GET", "GET");
define ("RUTH_POST", "POST");
define ("RUTH_PUT", "PUT");
define ("RUTH_PATCH", "PATCH");
define ("RUTH_DELETE", "DELETE");

class Ruth {
    /**
     * Indicates whether debugging is on or off
     * @var Boolean
     */
    private static $DEBUG = false;  //If this is true we have debugging
    /**
     * The list of all the routes in the system which are in the form  (object) array("requestMethod" => $requestMethod, "routePath" => $routePath, "routeFunction" => $routeFunction);
     * @var array
     */
    private static $routes = [];    //Routes that the system must route through
    /**
     * A list of error routes to goto when an error happens, this will be instead of the default
     */
    private static $errorRoutes = [];
    /**
     * A list of objects which must persist in Ruth
     * @var array
     */
    private static $objects = [];   //Any objects that need to be used in the system
    /**
     * An array snapshot of the $_SESSION variable
     * @var array
     */
    private static $SESSION;        //The session variables
    /**
     * An array snapshot of the $_REQUEST variable
     * @var array
     */
    private static $REQUEST;        //The request variables
    /**
     * An array snapshot of the $_COOKIE variable
     * @var array
     */
    private static $COOKIE;         //All the cookie variables
    /**
     * An array snapshot of the $_SERVER variable
     * @var array
     */
    private static $SERVER;         //All the server variables
    /**
     * An array snapshot of the $_FILES variable
     * @var array
     */
    private static $FILES;          //Any file variables
    /**
     * The roles are not compulsory but can be defined to provide security in the system
     * @var array
     */
    private static $ROLES = [];     //Roles are all the permissions for the routes
    /**
     * The default role to be used in the security, see addRole
     * @var String
     */
    private static $DEFAULT_ROLE;   //The default Role to be used as "Public"
    /**
     * The request method can be either GET, POST, PUT, DELETE
     * @var String
     */
    private static $REQUEST_METHOD; //The method of the request passed by the browser
    /**
     * The full URL passed by the browser
     * @var String
     */
    private static $REQUEST_URI;    //The full path passed by the browser
    /**
     * The path to the document root of the web server
     * @var String
     */
    private static $DOCUMENT_ROOT;  //The root of the webserver, see the REAL_PATH var for the actual path.
    /**
     * The real path to where the web files are being hosted, can be different from the document root
     * @var String
     */
    private static $REAL_PATH;       //The root of where the files are served from
    /**
     * The path after the domain name
     * @var String
     */
    private static $PATH;           //The path after the domain name, eg http://www.send.com/hello?test=9 will be /hello?test=9

    /**
     * This will be the raw post data as per php://input
     * @var String
     */
    private static $POST_DATA;

    /**
     * This is for the default life time of a session in minutes
     * @var Integer
     */
    private static $lifeTime;


    /**
     * Log of messages to inject after the page load
     * @var
     */
    public static $debugMessages;

    /**
     * Was an ajax call
     * @var
     */
    public static $wasAJAXCall = false;

    /**
     * The basic auth for a rest user
     * @var string
     */
    public static $restUser = "RESTUser";

    /**
     * The basic password for a rest user
     * @var string
     */
    public static $restPass = "RESTMaster";

    /**
     * A list of codes that will be used with HTTP responses
     * @var array
     */
    private static $CODES = array(  //An array of possible error messages we can send through to the end user
        100 => 'Continue',
        101 => 'Switching Protocols',
        102 => 'Processing',
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        207 => 'Multi-Status',
        208 => 'Already Reported',
        226 => 'IM Used',
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        306 => '(Unused)',
        307 => 'Temporary Redirect',
        308 => 'Permanent Redirect',
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Payload Too Large',
        414 => 'URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Range Not Satisfiable',
        417 => 'Expectation Failed',
        418 => 'I Am A Tea Pot',
        422 => 'Unprocessable Entity',
        423 => 'Locked',
        424 => 'Failed Dependency',
        426 => 'Upgrade Required',
        428 => 'Precondition Required',
        429 => 'Too Many Requests',
        431 => 'Request Header Fields Too Large',
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported',
        506 => 'Variant Also Negotiates',
        507 => 'Insufficient Storage',
        508 => 'Loop Detected',
        510 => 'Not Extended',
        511 => 'Network Authentication Required'
    );

    /**
     * Static class mustn't construct
     */
    private function __construct() {
    }

    /**
     * Static class mustn't destruct
     */
    private function __destruct() {
    }

    /**
     * static object should not be cloned
     */
    private function __clone() {

    }


    /**
     * Function to add rest authorization to the rest calls which should be protected
     * @param String $username
     * @param String $password
     */
    public static function setRESTAuth($username, $password) {
        if (self::$restUser === "RESTUser") {
            self::$restUser = [];
            self::$restPass = [];
        }

        self::$restUser[] = $username;
        self::$restPass[] = password_hash($password, PASSWORD_DEFAULT);
    }

    /**
     * Function to check that the authorization is correct for the user's requesting permission
     * @param String $username
     * @param String $password
     * @return bool
     */
    public static function getRESTAuth($username, $password) {
        if (strpos(self::getSERVER("HTTP_HOST"), "local") === false) {
            if (!is_array(self::$restUser) && !is_array(self::$restPass)) {
                if ($username === self::$restUser && $password === self::$restPass) {
                } else {
                    self::responseHeader(403);
                    die();
                }
            } else { //Search through the arrays to get the correct usernames and validate the salt
                if (!empty(self::$restUser)) {
                    foreach (self::$restUser as $rid => $restUser) {
                        if ($restUser === $username) {
                            //check if the passwords match
                            if (password_verify($password, self::$restPass[$rid])) {
                                //do nothing but exit because we are good!
                                return true;
                            } else {
                                self::responseHeader(403);
                                die();
                            }
                        }
                    }
                }
                //So we had no joy with any username
                self::responseHeader(403);
                die();
            }
        }
    }

    /**
     * The default page template for Ruth so she can make routes
     * @param type $title String A title to name the page by
     * @return type Shape A page template with default bootstrap
     */
    public static function getPageTemplate($title="Default") {
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

    /**
     * Auto Load tries to include new class requires automatically
     */
    public static function autoLoad($paths, $loadAutoLoad=true, $ignoreRequires=false) {
        /*
        * Optional : Maybe we can use DIR to prefix paths
        */
        $paths = explode (PATH_SEPARATOR, $paths);
        $code =  'function autoloadTina4($classname) {';
        foreach ($paths as $pid => $path) {
            $code .= '
                  if (!empty(TINA4_VERSION) && file_exists("'.TINA4_VERSION."/".$path.'/{$classname}.php")) {
                    require_once ("'.TINA4_VERSION."/".$path.'/{$classname}.php");
                  } else
                  if (file_exists("'.$path.'/{$classname}.php")) {
                     require_once ("'.$path.'/{$classname}.php");
                  }';

            foreach(glob("{$path}/*.php") as $filename){
                if (!$ignoreRequires) {

                    $versionFileName = str_replace(Ruth::getDOCUMENT_ROOT(), TINA4_VERSION, $filename );
                    if (!empty(TINA4_VERSION) && file_exists($versionFileName)) {
                        require_once $versionFileName;
                    } else {
                        require_once $filename;
                    }
                }
            }
        }
        $code .= '}';

        if ($loadAutoLoad) {
            eval($code);
            spl_autoload_register('autoloadTina4');
        }
    }

    /**
     * The debugging system for the class
     * @param String $message A message to be sent through for debugging purposes
     */
    public static function Message ($message) {
        Ruth::$debugMessages[] = $message."<br />";
    }

    /**
     * Switch debugging on for Ruth
     *
     * Turns on the debugging in Ruth showing a user how the routing is done
     */
    public static function DEBUG() {
        self::Message("DEBUGGING IS ON!");
        self::Message("================");
        self::$DEBUG = true;
    }

    /**
     * A function to set request variables using Ruth
     * @param String $keyName The name of the request variable to set.
     * @param string|type $keyValue The value stored in the request variable
     */
    public static function setREQUEST ($keyName="", $keyValue="") {
        if (!empty($keyName)) {
            $_REQUEST[$keyName] = $keyValue;
            self::$REQUEST = array_merge($_REQUEST, self::parseParams($_SERVER["REQUEST_URI"]));
        }
    }

    /**
     * Getter for the stored $_REQUEST params, blank keyName returns all
     * @param String $keyName The name of the request variable
     * @return Mixed An mixed variable of what was present for that key
     */
    public static function getREQUEST($keyName = "") {
        if (empty($keyName)) {
            return self::$REQUEST;
        } else
            if (isset(self::$REQUEST[$keyName]) || !empty(self::$REQUEST[$keyName])) {
                return self::$REQUEST[$keyName];
            } else {
                return null;
            }
    }

    public static function getREQUEST_URI () {
        return self::$REQUEST_URI;
    }

    /**
     * Function to make request variables into Session variables with a filter array of fields that shouldn't be considered
     * @param type $filter
     */
    public static function requestToSESSION ($filter="") {
        if ($filter === "") $filter = [];
        foreach (Ruth::getREQUEST() as $requestName => $requestValue ) {
            if (!in_array($requestName, $filter)) {
                Ruth::setSESSION($requestName, $requestValue);
            }
        }
    }

    /**
     *
     * @param String $type, hidden, text
     * @param Array $filter List of elements not to create
     * @return type
     */
    public static function requestToInput($type="hidden", $filter=null) {
        if (empty($filter)) {
            $filter = [];
        }
        $html = [];
        foreach (Ruth::getREQUEST() as $keyName => $keyValue) {
            if (!in_array($keyName, $filter)) {
                $html[] = input(["type" => $type, "name" => $keyName, "value" => $keyValue]);
            }
        }
        return shape($html);
    }

    /**
     * Getter for the raw post data as per php://input
     */
    public static function getPOST_DATA() {
        return self::$POST_DATA;
    }


    /**
     * Gets a particular object based on the key
     * @param String $keyName The name of the object
     * @return Object The object that was requested
     */
    public static function getOBJECT($keyName = "") {
        if (empty($keyName)) {
            return self::$objects;
        } else
            if (!empty(self::$objects[$keyName]) || isset(self::$objects[$keyName]) ) {
                return self::$objects[$keyName];
            } else {
                return null;
            }
    }

    /**
     * Sets the object in the Ruth system for use everywhere
     * @param String $name The name of the object
     * @param Object $object The actual object
     */
    public static function setOBJECT ($name, $object) {
        if (!empty($name)) {
            self::$objects[$name] = $object;
        }
    }

    /**
     * Sets the Cookie name and value for things we need to store client side
     * @param String $cookieName The name of the cookie
     * @param String $value The value to be stored in the cookie
     * @param String $minutes How many minutes until the cookie must expire
     */
    public static function setCOOKIE($cookieName, $value="", $minutes=60) {
        setcookie ($cookieName, $value, time()+($minutes*60));
    }

    /**
     * Getter for the cookie variables, blank keyName returns all
     * @param String $keyName The name of the cookie
     * @return String The value of the cookie
     */
    public static function getCOOKIE($keyName = "") {
        if (empty($keyName)) {
            return self::$COOKIE;
        } else
            if (!empty(self::$COOKIE[$keyName])) {
                return self::$COOKIE[$keyName];
            } else {
                return null;
            }
    }

    /**
     * Getter for the file variables, blank keyName will return all
     * @param String $keyName The name of the file variable
     * @return Object The file object that is present in the $_FILES variable
     */
    public static function getFILES($keyName = "") {
        if (empty($keyName)) {
            return self::$FILES;
        } else
            if (!empty(self::$FILES[$keyName])) {
                return self::$FILES[$keyName];
            } else {
                return null;
            }
    }

    /**
     * Getter for the Server variables
     * @param String $keyName The name of the variable requested
     * @return String The value of the variable requested
     */
    public static function getSERVER($keyName = "") {
        if (empty($keyName)) {
            return self::$SERVER;
        } else
            if (!empty(self::$SERVER[$keyName])) {
                return self::$SERVER[$keyName];
            } else {

                return null;
            }
    }


    /**
     * Get the path of the URL after the website address
     * @return String The path of the website after the domain name
     */
    public static function getPATH() {
        return self::$PATH;
    }


    /**
     * Get the request method that was used
     * @return String The request method used - either POST or GET
     */
    public static function getRequestMethod() {
        return self::$REQUEST_METHOD;
    }

    /**
     * Get the real path to where ruth is running form
     * @return String return the real path
     */
    public static function getREAL_PATH() {
        return self::$REAL_PATH;
    }

    /**
     * Return the document root where the webserver is running from is running from
     * @return String The path to where the website is being hosted relative to the server
     */
    public static function getDOCUMENT_ROOT() {
        return self::$DOCUMENT_ROOT;
    }

    /**
     * Gets the last route based on an option match to request method , either post or get
     * @param String $requestMethod GET or POST
     * @return String Last route that Ruth remembers
     */
    public static function getLASTROUTE ($requestMethod="") {
        $notFound = true;
        if (!empty($_SESSION["routeLASTPATH"]) && count ($_SESSION["routeLASTPATH"]) > 1) {
            $iCount = count ($_SESSION["routeLASTPATH"])-2;
            if (!empty($requestMethod)) {
                $iCount++;
            }
            while ($notFound && $iCount > 0 ) {

                if (empty($requestMethod)) {
                    $match = strtoupper($_SESSION["routeLASTPATH"][$iCount]->requestMethod);
                }
                else {

                    $match = strtoupper($requestMethod);
                }
                if (strtoupper($_SESSION["routeLASTPATH"][$iCount]->requestMethod) === $match) {
                    $notFound = false;
                    return $_SESSION["routeLASTPATH"][$iCount]->routePath;
                }

                $iCount--;
            }
        }

        return Ruth::$PATH;
    }

    /**
     * A function to set session variables using Ruth
     * @param String $keyName The name of the sesson variable to set.
     * @param type $keyValue The value stored in the session variable
     */
    public static function setSESSION ($keyName="", $keyValue="") {
        if (isset($keyValue) || !empty($keyName)) {

            if(is_null($keyValue)){
                unset($_SESSION[$keyName]);
            } else {
                $_SESSION[$keyName] = $keyValue;
            }

            self::$SESSION = $_SESSION;
        }
    }

    /**
     * Get a session variable using Ruth
     * @param type $keyName
     * @return type
     */
    public static function getSESSION($keyName = "") {
        if (!empty($_SESSION)) {
            self::$SESSION = $_SESSION;
            if (empty($keyName)) {
                return self::$SESSION;
            } else
                if (isset(self::$SESSION[$keyName]) || !empty(self::$SESSION[$keyName])) {
                    return self::$SESSION[$keyName];
                } else {
                    return null;
                }
        }
    }
    /**
     * Unset session variables using Ruth
     * @param String $keyName The name of the session variable
     * @return boolean Was the resseting successful
     */
    public static function unsetSESSION($keyName="") {
        if (!empty($_SESSION)) {
            self::$SESSION = $_SESSION;
            if (!empty(self::$SESSION[$keyName])) {
                unset(self::$SESSION[$keyName]);
                unset($_SESSION[$keyName]);
                return true;
            } else {
                unset($_SESSION);
                return false;
            }
        }
        return false;
    }


    /**
     * Set a role for Ruth to use in validating your routes that you want people to access
     * @param type $roleName
     * @param type $routesAllowed
     * @param type $defaultRole
     */

    public static function setROLE($roleName="", $routesAllowed="", $defaultRole=false) {
        if ($defaultRole) {
            self::$DEFAULT_ROLE = $roleName;
        }
        if (!empty(self::$ROLES[$roleName])) {
            self::$ROLES[$roleName] = array_merge(self::$ROLES[$roleName], $routesAllowed);
        } else {
            self::$ROLES[$roleName] = $routesAllowed;
        }

        if (self::$DEBUG) {
            self::Message(__LINE__.": Routes Allowed ". print_r ($routesAllowed , 1));
        }

    }

    /**
     * Dynamically allow routes to be added to a Role
     * @param string|array $roleName
     * @param $routeAllowed
     */
    public static function authorizeRoute ($roleNames="", $routeAllowed) {
        if (is_array($roleNames)) {
            foreach ($roleNames as $id => $roleName) {
                self::$ROLES[$roleName][] = $routeAllowed;
            }
        } else {
            self::$ROLES[$roleNames][] = $routeAllowed;
        }

    }



    /**
     * The method to set authorization
     *
     * Authorization works by setting an authToken in the session and an expiry time. Also the role is defined for security purposes.
     *
     * @param String $roleName The naame of the Role matching declared roles
     * @param Mixed $authDATA Any PHP array or object
     * @param Integer $lifeTime The amount minutes
     */
    public static function setAuthorization ($roleName="", $authDATA="", $lifeTime=30) {
        if ($roleName != "") {
            if (array_key_exists ($roleName, self::$ROLES)) {

                Ruth::$lifeTime = $lifeTime;

                $_SESSION["authToken"] = md5($roleName.print_r ($authDATA, 1).$lifeTime);
                $_SESSION["authData"] = $authDATA;
                $_SESSION["roleName"] = $roleName;

                $expiryTime = new DateTime (Date("Y-m-d h:i:s"));
                $expiryTime->add (new DateInterval ("PT".$lifeTime."M") );
                $_SESSION["expires"] = $expiryTime;

                self::$SESSION = $_SESSION;
            }
            else {
                die ("<pre>Role ".$roleName." specified does not exist in declared roles:\n".print_r (self::$ROLES, 1)."</pre>");
            }
        }
    }

    /**
     * Deletes the security token
     *
     * The session is invalidated and the internal session variable of Ruth updated
     *
     * @return Boolean Returns true if there was a non empty authToken in the session
     */
    public static function delAuthorization () {
        if (!empty($_SESSION["authToken"])) {
            $_SESSION["authToken"] = null;
            $_SESSION["authData"] = null;
            $_SESSION["roleName"] = null;
            $_SESSION["expires"] = null;
            self::$SESSION = $_SESSION;
            return true;
        }
        else {
            return false;
        }

    }

    /**
     * Create the session name and initialize all the system variables
     *
     * This method creates a session with the name specified and sets all the internal variables it can.
     * It also parses the URL for any get variables that may be present and puts them in the internal $REQUEST
     *
     * @param String $sessionName The name of the session for the web application
     * @return bool
     */
    public static function initRuth($sessionName) {
        if (empty($sessionName))
            die("This framework works with sessions, please specify a session name when calling Router");

        self::$POST_DATA = file_get_contents ("php://input");

        session_name($sessionName);
        session_start();

        $URL = explode ("?", $_SERVER["REQUEST_URI"], 2);
        self::$REQUEST_METHOD = $_SERVER["REQUEST_METHOD"];
        self::$REQUEST_URI = $URL[0];
        self::$DOCUMENT_ROOT = realpath(__DIR__."/../");
        self::$REAL_PATH = getcwd ();

        if (!empty($_FILE)) {
            self::$FILE = $_FILES;
        }

        if (!empty($_REQUEST)) {
            self::$REQUEST = $_REQUEST;
        }

        //parser for formData from raw AJAX
        if (!empty($_REQUEST["formData"])) {
            self::$wasAJAXCall = true;
            $formData = rawurldecode($_REQUEST["formData"]);
            //make sure we get the correct strings
            $formData =  html_entity_decode($formData, ENT_QUOTES);

            $jsonRequest = [];
            foreach (explode('-!-', $formData) as $data) {

                $param = explode ("=", $data, 2);
                $jsonRequest[$param[0]] = $param[1];

                if (!empty($param[0])) {
                    self::setREQUEST($param[0], $param[1]);
                    $_REQUEST[$param[0]] = $param[1];
                }
            }

            $jsonRequest = json_encode((object) $jsonRequest);
            self::setREQUEST("jsonRequest", $jsonRequest);
            $_REQUEST["jsonRequest"] = $jsonRequest;


            unset($_REQUEST["formData"]);
            unset(Ruth::$REQUEST["formData"]);
        }

        if (!empty($_REQUEST)) {
            self::$REQUEST = array_merge(self::$REQUEST, self::parseParams($_SERVER["REQUEST_URI"]));
        }
        else {
            self::$REQUEST = self::parseParams($_SERVER["REQUEST_URI"]);
        }

        if (!empty($_COOKIE)) {
            self::$COOKIE = $_COOKIE;
        }

        if (!empty($_SERVER)) {
            self::$SERVER = $_SERVER;
        }

        if (!empty($_FILES)) {
            self::$FILES = $_FILES;
        }

        self::setAuthorization("", "");

        /**
         * CREATE
         * Add a specific record to the table
         */
        self::addRoute(RUTH_POST, "/".TINA4_REST_PATH."/{tablename}",
            function($tableName){
                Ruth::$wasAJAXCall = true;
                Ruth::getRESTAuth(self::getSERVER("PHP_AUTH_USER"), self::getSERVER("PHP_AUTH_PW"));
                if (!empty(self::getOBJECT("DEB"))) {
                    $DEB = self::getOBJECT("DEB");
                    $database = $DEB->getDatabase();
                    $table = $database[$tableName];
                    if (!empty($table[0]["pk"]) || $table[0]["pk"] === "PRIMARY KEY") {
                        $primaryKey = strtoupper($table[0]["field"]);
                    } else {
                        $primaryKey = strtoupper($table[0]["field"]); // this is a generic fall back
                    }
                    //try and add the record
                    $json = json_decode(urldecode(file_get_contents("php://input")), false);

                    $tableData = "";
                    foreach ($json as $field => $value) {
                        $tableData[$field] = $value;
                    }
                    if (empty($tableData[$primaryKey])) {
                        $tableData[$primaryKey] = $DEB->getNextId($tableName, $primaryKey);
                    }
                    $DEB->insert ($tableName, $tableData);
                    $DEB->commit();
                    $result = $DEB->getRow("select * from {$tableName} where {$primaryKey} = '{$tableData[$primaryKey]}'");
                    if (!empty($result)) {
                        echo json_encode($result); //return the newly added record
                        header('Content-Type: application/json');
                        self::responseHeader("200", "Success: Record was added to {$tableName}");
                        $DEB->commit();
                    }  else {
                        self::responseHeader("400", "Failed: Could not add record to {$tableName}");
                    }
                    $DEB->close();
                } else {
                    die("DEB database object not registered");
                }
            }
        );


        /**
         * READ
         * Get a specific record from a table using the primary key
         */
        self::addRoute(RUTH_GET, "/".TINA4_REST_PATH."/{tablename}/{id}",
            function($tableName, $id){
                self::$wasAJAXCall = true;
                Ruth::getRESTAuth(self::getSERVER("PHP_AUTH_USER"), self::getSERVER("PHP_AUTH_PW"));
                if (!empty(self::getOBJECT("DEB"))) {
                    $DEB = self::getOBJECT("DEB");
                    $database = $DEB->getDatabase();
                    $table = $database[$tableName];
                    if (!empty($table[0]["pk"]) || $table[0]["pk"] === "PRIMARY KEY") {
                        $primaryKey = strtoupper($table[0]["field"]);
                    } else {
                        $primaryKey = strtoupper($table[0]["field"]); // this is a generic fall back
                    }

                    $result = $DEB->getRow("select * from {$tableName} where {$primaryKey} = '{$id}'");
                    if (!empty($result)) {
                        echo json_encode($result);
                        header('Content-Type: application/json');
                        Ruth::responseHeader("200", "Success: Record {$id} was found in {$tableName}");
                    }  else {
                        self::responseHeader("400", "Failed: Record {$id} was NOT found in {$tableName}");
                    }
                } else {
                    die("DEB database object not registered");
                }
            }
        );




        /**
         * UPDATE
         * Update a specific record in the system based on its ID
         */
        self::addRoute(RUTH_PUT, "/".TINA4_REST_PATH."/{tablename}/{id}",
            function($tableName, $id){
                self::$wasAJAXCall = true;
                self::getRESTAuth(self::getSERVER("PHP_AUTH_USER"), self::getSERVER("PHP_AUTH_PW"));
                if (!empty(Ruth::getOBJECT("DEB"))) {
                    $DEB = Ruth::getOBJECT("DEB");
                    $database = $DEB->getDatabase();
                    $table = $database[$tableName];
                    if (!empty($table[0]["pk"]) || $table[0]["pk"] === "PRIMARY KEY") {
                        $primaryKey = strtoupper($table[0]["field"]);
                    } else {
                        $primaryKey = strtoupper($table[0]["field"]); // this is a generic fall back
                    }

                    $json = json_decode(urldecode(file_get_contents("php://input")), false);

                    $tableData = "";
                    foreach ($table as $fid => $column) {
                        $column["field"] = strtoupper($column["field"]);
                        if (strtoupper($column["field"]) !== $primaryKey ) {
                            if (isset($json->$column["field"])) {
                                $tableData[$column["field"]] = $json->$column["field"];
                            } else {
                                $tableData[$column["field"]] = "null";
                            }
                        }
                    }

                    $DEB->update ($tableName, $tableData, [$primaryKey => $id]);
                    $DEB->commit();

                    $result = $DEB->getRow("select * from {$tableName} where {$primaryKey} = '{$id}'");
                    if (!empty($result)) {
                        echo json_encode($result);
                        header('Content-Type: application/json');
                        self::responseHeader("200", "Success: Record {$id} was found in {$tableName}");

                        $DEB->commit();
                    }  else {
                        self::responseHeader("400", "Failed: Record {$id} was NOT found in {$tableName}");
                    }
                } else {
                    die("DEB database object not registered");
                }
            }
        );


        /**
         * PATCH
         * Patch a specific record in the system based on its ID
         */
        self::addRoute(RUTH_PATCH, "/".TINA4_REST_PATH."/{tablename}/{id}",
            function($tableName, $id){

                Ruth::$wasAJAXCall = true;
                Ruth::getRESTAuth(self::getSERVER("PHP_AUTH_USER"), self::getSERVER("PHP_AUTH_PW"));
                if (!empty(self::getOBJECT("DEB"))) {
                    $DEB = self::getOBJECT("DEB");
                    $database = $DEB->getDatabase();

                    $table = $database[$tableName];
                    if (!empty($table[0]["pk"]) || $table[0]["pk"] === "PRIMARY KEY") {
                        $primaryKey = strtoupper($table[0]["field"]);
                    } else {
                        $primaryKey = strtoupper($table[0]["field"]); // this is a generic fall back
                    }

                    $json = json_decode(urldecode(file_get_contents("php://input")), false);

                    $tableData = "";
                    foreach ($table as $fid => $column) {
                        $column["field"] = strtoupper($column["field"]);
                        if ($column["field"] !== $primaryKey ) {
                            if (isset($json->$column["field"])) {
                                $tableData[$column["field"]] = $json->$column["field"];
                            }
                        }
                    }


                    $DEB->update ($tableName, $tableData, [$primaryKey => $id]);
                    $DEB->commit();


                    $result = $DEB->getRow("select * from {$tableName} where {$primaryKey} = '{$id}'");
                    if (!empty($result)) {
                        echo json_encode($result);
                        header('Content-Type: application/json');
                        self::responseHeader("200", "Success: Record {$id} was found in {$tableName}");

                        $DEB->commit();
                    }  else {
                        self::responseHeader("400", "Failed: Record {$id} was NOT found in {$tableName}");
                    }
                    $DEB->close();
                } else {
                    die("DEB database object not registered");
                }
            }
        );


        /**
         * DELETE
         * Delete a specific record from a table using the primary key
         */
        self::addRoute(RUTH_DELETE, "/".TINA4_REST_PATH."/{tablename}/{id}",
            function($tableName, $id){
                self::$wasAJAXCall = true;
                self::getRESTAuth(self::getSERVER("PHP_AUTH_USER"), self::getSERVER("PHP_AUTH_PW"));
                if (!empty(Ruth::getOBJECT("DEB"))) {
                    $DEB = Ruth::getOBJECT("DEB");
                    $database = $DEB->getDatabase();
                    $table = $database[$tableName];
                    if (!empty($table[0]["pk"]) || $table[0]["pk"] === "PRIMARY KEY") {
                        $primaryKey = strtoupper($table[0]["field"]);
                    } else {
                        $primaryKey = strtoupper($table[0]["field"]); // this is a generic fall back
                    }
                    if ( $DEB->delete($tableName, [$primaryKey => $id]) ) {
                        self::responseHeader("200", "Success: Record {$id} was deleted from {$tableName}");
                        $DEB->commit();
                    }  else {
                        self::responseHeader("400", "Failed: Record {$id} was NOT deleted from {$tableName}");
                    }
                    $DEB->close();
                } else {
                    die("DEB database object not registered");
                }
            }
        );

        /**
         * LIST
         * Get a list of records out of the database
         */
        self::addRoute(RUTH_GET, "/".TINA4_REST_PATH."/{tablename}",
            function($tableName){
                self::$wasAJAXCall = true;
                self::getRESTAuth(self::getSERVER("PHP_AUTH_USER"), self::getSERVER("PHP_AUTH_PW"));
                if (!empty(self::getOBJECT("DEB"))) {
                    $DEB = self::getOBJECT("DEB");


                    $result = $DEB->getRows("select * from {$tableName}");
                    if (!empty($result)) {
                        echo json_encode($result);
                        header('Content-Type: application/json');
                        self::responseHeader("200", "Success: Listing for {$tableName}");
                    }  else {
                        self::responseHeader("400", "Failed: Listing for {$tableName}");
                    }
                    $DEB->close();
                } else {
                    die("DEB database object not registered");
                }
            }
        );



        return true;
    }

    /**
     * Parse the params
     *
     * This parses the params from the ? sent on the URL
     *
     * @param string a URL that was posted to the router
     * @return array An array of params that were passed from the URL - GET variables
     */
    private static function parseParams($URI = "") {
        $params = array();
        $args = parse_url($URI);
        if (isset($args['query'])) {
            parse_str($args['query'], $params);
        }
        return $params;
    }

    /**
     * The Add Route Method
     *
     * This method adds routes into the system based on what the user wants to use, your webserver needs to be configured correctly for this
     * to work.
     *
     * @param String $requestMethod Either GET, PUT, POST, DELETE
     * @param String $routePath A path to be used as a route, eg. /user, variables can also be parsed /user/{id}/ and wildcards are *
     * @param Closure $routeFunction A function which will be called with the corresponding variables
     * @param Boolean $routeIgnoreTracking Ruth uses this to give you back the last route but you may not want her to remember all the paths
     * @return Boolean Always returns true
     */
    public static function addRoute($requestMethod, $routePath, $routeFunction, $customParams="", $routeIgnoreTracking=false) {
        self::$routes[] = (object) array("requestMethod" => $requestMethod, "routePath" => $routePath, "routeFunction" => $routeFunction, "routeParams" => $customParams, "routeIgnoreTracking" => $routeIgnoreTracking);
        return true;
    }

    /**
     * This is where you add custom routes for HTML error codes, they should be already defined via addRoute in order to work
     *
     * @param Integer $errorCode A valid HTML error code
     * @param String $routePath A valid route for Ruth to redirect to
     * @return boolean
     */
    public static function addErrorRoute ($errorCode, $routePath) {
        self::$errorRoutes[$errorCode] = (object) array ("errorCode" => $errorCode, "routePath" => $routePath);
        return true;
    }

    /**
     * The response header
     *
     * We use response header to return a valid HTTP response when something happens, included is a message or body to display with the message
     *
     * @param String $errorCode A valid error code which is declared above
     * @param String $message A custom message to display with the error code otherwise the default is taken.
     */
    public static function responseHeader($errorCode = 404, $message = "") {
        if (!empty (self::$errorRoutes[$errorCode])) {

            Ruth::redirect(self::$errorRoutes[$errorCode]->routePath);
        }
        else {
            header("HTTP/1.0 " . $errorCode . " " . self::$CODES[$errorCode]);
            if (empty($message)) {
                echo "HTTP/1.0 " . $errorCode . " " . self::$CODES[$errorCode];
            } else {
                header('X-Ruth-Message:'.$message);
            }
            die();
        }
    }

    /**
     * Creates a regular expression
     *
     * This method created a regular expression to be used for matching with the routing and security
     *
     * @param type $routePath The path specified by the user
     * @return String A regular expression which can be used for routing
     */
    public static function createRegEx($routePath) {
        //replace the variables with regex to get them
        $regEx = preg_replace('/\{(.+)\}/i', '([a-z0-9\_\-\%\@\.]+)', explode ("/", $routePath));
        $regEx = join ("/", $regEx);
        $regEx = str_replace('/', '\/', $regEx);
        $regEx = str_replace('*', '.*+', $regEx);
        return '/^' . $regEx . '\/?$/i';
    }

    /**
     * The method
     *
     * @param type $routePath
     * @param string $URI
     * @return boolean
     *
     */
    public static function matchRoute($routePath, $URI) {
        if(strpos($routePath, "|") !== false){
            $routePath = "(".$routePath.")";
        }

        //Add a trailing slash to $URI if there is none
        if (substr($URI, -1) !== "/") $URI .="/";
        //fix the routes
        $matching = false;
        $regEx = self::createRegEx($routePath);

        if (self::$DEBUG) {
            self::Message(__LINE__.": Matching ". $regEx);
            self::Message(__LINE__.": To ".$URI);
        }
        preg_match($regEx, $URI, $matches);


        if (!empty($matches)) {
            if (self::$DEBUG) {
                self::Message(__LINE__.": Matches ".print_r($matches, 1));
            }
            $matching = true;
        }
        return $matching;
    }

    public static function getParams($routePath, $URI) {
        //Remove the trailing slash
        if (substr($routePath,-1,1) === "/") {
            $routePath = substr($routePath, 0,  -1);
        }
        $regEx = self::createRegEx($routePath);
        preg_match($regEx, $URI, $matches);
        $params = array();
        foreach ($matches as $id => $value) {
            if ($id != 0) {
                $params[] = urldecode($value);
            }
            else if ($id == 0) {
                self::$PATH = $value;
            }
        }
        return $params;
    }


    public static function redirect ($newPath="") {
        if (!headers_sent()) {
            header('Location: '.$newPath);
            die();
        }
        else {
            echo "<script>location.href = '{$newPath}'; </script>";
            die();
        }

    }

    public static function getAuthorization ($routePath="") {

        //Authorized is off by default
        $authorized = false;
        //Determine the role
        $roleName = "";

        if (!empty($_SESSION["authToken"])) {
            $expiryTime = new DateTime(Date("Y-m-d h:i:s"));

            if (!empty($_SESSION["expires"])) {
                $interval = $_SESSION["expires"]->diff($expiryTime);
                $minutes = $interval->format("%i");
                if ($minutes > 0) {

                    if (!empty($_SESSION["roleName"])) {
                        $roleName = $_SESSION["roleName"];
                    }

                    if (empty(Ruth::$lifeTime)) {
                        Ruth::$lifeTime = 30;
                    }

                    //update the session again
                    $expiryTime = new DateTime (Date("Y-m-d h:i:s"));
                    $expiryTime->add (new DateInterval ("PT".Ruth::$lifeTime."M") );
                    $_SESSION["expires"] = $expiryTime;


                } else {
                    $roleName = self::$DEFAULT_ROLE;
                    Ruth::delAuthorization();
                }
            } else {
                $roleName = self::$DEFAULT_ROLE;
            }
        } else {
            $roleName = self::$DEFAULT_ROLE;
        }

        //Check if the role we have found is real

        if (array_key_exists ($roleName, self::$ROLES)) {
            //Check if routePath is matched in the list of the Role
            if (self::$DEBUG) {
                self::Message(__LINE__.": {$roleName} Allowed ".print_r(self::$ROLES[$roleName], 1));
            }

            foreach (self::$ROLES[$roleName] as $rid => $roleRoute) {

                if (self::matchRoute($roleRoute, $routePath)) {
                    $authorized = true;
                    break;
                }
            }
        }
        else {
            Ruth::delAuthorization();
            self::responseHeader(401);
            die;
        }

        return $authorized;
    }

    public static function parseRoutes($customPath="") {

        self::$PATH = self::$REQUEST_URI;

        if ($customPath) {
            self::$REQUEST_URI = $customPath;
        }

        //Choose the correct route
        $found = false;


        foreach (self::$routes as $rid => $route) {
            if ($route->requestMethod == self::$REQUEST_METHOD) {
                if (self::matchRoute($route->routePath, self::$REQUEST_URI)) {
                    //security segement
                    if (!empty(self::$ROLES)) {
                        $authorized = self::getAuthorization($route->routePath);
                    }
                    else {
                        $authorized = true;
                    }

                    if ($authorized) {


                        $params = self::getParams($route->routePath, self::$REQUEST_URI);

                        if (!empty($route->routeParams)) {
                            $params[] = $route->routeParams;
                        }

                        $args = func_get_args ($route->routeFunction);

                        $reflection = new ReflectionFunction ($route->routeFunction);

                        $method_args_count = $reflection->getParameters();

                        if (count($params) != count($method_args_count)) {
                            for ($i = 0; $i < count($method_args_count); $i++) {
                                $params[] = null;
                            }
                        }


                        if (!$route->routeIgnoreTracking) {
                            $lastRoute = "";
                            if (!empty($_SESSION["routeLASTPATH"])) {
                                if (count($_SESSION["routeLASTPATH"]) > 1) {
                                    $lastRoute = $_SESSION["routeLASTPATH"][count($_SESSION["routeLASTPATH"])-1]->routePath;
                                }
                            }

                            if ($lastRoute !== $route->routePath) {
                                if (!empty($_SESSION["routeLASTPATH"]) && count($_SESSION["routeLASTPATH"]) > 10) {
                                    $_SESSION["routeLASTPATH"] = array_splice($_SESSION["routeLASTPATH"], count($_SESSION["routeLASTPATH"])-10);
                                }
                                $_SESSION["routeLASTPATH"][] = (object) array("routePath" => self::$REQUEST_URI, "requestMethod" => $route->requestMethod);
                            }
                        }




                        call_user_func_array($route->routeFunction, $params);
                        $found = true;

                    }
                    else {
                        self::responseHeader(401);
                        die;
                    }
                    break;
                }
            }
        }

        if (defined("ONROUTE") && !empty(ONROUTE)) {

            $params = ["action" => "route", "server" => Ruth::getSERVER(), "cookies" => Ruth::getCOOKIE(), "session" => Ruth::getSESSION(), "request" => Ruth::getREQUEST()];
            @call_user_func_array(ONROUTE, $params);

        }

        if (!$found) {
            //before we quit, see if Kim can help us!
            if (class_exists("Kim")) {

                $pageName = Ruth::$REQUEST_URI;

                $html = (new Kim())->getDefaultPage($pageName);
                if (!empty($html)) {
                    echo $html;
                }
                else {
                    self::responseHeader(404);
                }
            }
            else {
                self::responseHeader(404);
            }
        }

        if (self::$DEBUG) {
            if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                //ajax - save to a file which we can read
                file_put_contents (self::getREAL_PATH()."/ajax_debug.log", urldecode(file_get_contents('php://input'))."\n", FILE_APPEND);
            }
            else {
                file_put_contents (self::getREAL_PATH()."/ajax_debug.log", print_r(self::getREQUEST(),1).print_r(self::getCOOKIE(),1).print_r(self::getSESSION(),1));
                //output the debug window
                if ( stripos(Ruth::getREQUEST_URI(),"/".TINA4_REST_PATH) === false && stripos(Ruth::getREQUEST_URI(),"/swagger") === false && Ruth::getREQUEST_URI()  != "/debug" && !self::$wasAJAXCall) {
                    echo (new Kim())->parseTemplate("/snippet/debug", ["CODE" => file_get_contents(self::getREAL_PATH() . "/ajax_debug.log")]);
                }
            }
        }
    }

    /**
     * Some useful scripts which can be used with KIM
     */
    public static function scripts () {
        $scripts = script("
        var Ruth = function(){};
        Ruth.redirect = function(newPath) {
            location.href = newPath;
        }
        ");

        return $scripts;
    }

}
