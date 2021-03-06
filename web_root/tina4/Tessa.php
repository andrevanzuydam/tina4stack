<?php
/**
 * Created by PhpStorm.
 * User: andrevanzuydam
 * Date: 4/13/2015
 * Time: 1:14 PM
 */
global $TESSA;

function tessaErrorHandler($errno, $errstr, $errfile, $errline)
{
    switch ($errno) {
        case E_USER_ERROR:
            die ("<pre>Oops! - Please make sure the Selenium server is running and that it is not blocked by the Firewall\nError: {$errno} {$errstr}, {$errfile}, {$errline} </pre>");
            break;
    }
}
/**
 * Displays a message on the screen
 * @param string $msg The message you want displayed.
 */
function message ($msg) {
    echo $msg."...\n";
}

/**
 * Outputs a message prefixed with Trying to
 * @param string $text The message you want displayed
 */
function tryingTo ($msg) {
    echo "Trying to ".$msg."...\n";
}

/**
 * Outputs a message prefixed with I want to
 * @param string $msg The message you want to be displayed
 */
function IWantTo ($msg) {
    echo "I want to ".$msg."...\n";
}

/**
 * A function which tests if a certain text string is on the screen, if its not there the function will kill the test process.
 * @param string $text The text you wish to see on the screen
 */
function IExpectToSee ($text) {
    $pageText = byPath("//*")->getText();
    echo "I expected to see \"".$text."\"...\n";
    if (stripos($pageText, "{$text}") == 0) {
        die("And I did <b>NOT</b> see <b>\"".$text."\"</b>, here is what I saw:<br>". screenShot());
    }
    else {
        echo "And I saw \"".substr($pageText, stripos($pageText, "{$text}"), strlen("{$text}"))."\"...\n";
    }

}

/**
 * A function which checks if a certain string is on the screen and returns true or false, this is to be used in an if statement
 * Example:
 *
 * if (ISee("Home")) {
 *      ....
 * }
 *   else {
 *    ....
 * }
 *
 * @param string $text The text you want to see on the screen
 * @return bool True of False, whether the string existed on the screen.
 */
function ISee ($text) {
    $pageText = byPath("/html")->getText();

    if (stripos($pageText, $text) == 0) {
        return false;
    }
    else {
        return true;
    }
}

/**
 * A function that waits for 2 seconds
 */
function WaitFor2Seconds() {
    sleep(2);
}

/**
 * A function that waits for 1 seconds
 */
function WaitFor1Second() {
    sleep(1);
}


/**
 * Find an element by its ID on the page, returns a TestElement object
 *
 * @param string $id The ID of the html element, see <tag id="{id}"></tag>
 * @return TestElement The element to be used for further testing
 */
function byId($id) {
    global $TESSA;
    return $TESSA->byId($id);
}

/**
 * Find an element by ID, Path or Class, if all these fail the function causes the test process to fail
 * @param string $id The id, path or class of the element we are looking for.
 * @return TestElement
 */
function LookFor($id) {
    global $TESSA;
    $return = $TESSA->getAlias($id);

    if (empty($return)) {
        try {
            $return = $TESSA->byId($id);
        } catch (Exception $e) {
            try {
                $return = $TESSA->byPath($id);
            } catch (Exception $e) {
                try {
                    $return = $TESSA->byClass($id);
                } catch (Exception $e) {
                    try {
                        $return = $TESSA->byCSS($id);
                    } catch (Exception $e) {
                        die("<b> I could not find an element on the page with the name {$id} </b>");
                    }
                }
            }
        }
    }
    return $return;
}

/**
 * Using the lookFor function this function calls the click event on the found element.
 * @param string $id The id, path or css of the element we are looking to click on
 */
function ClickOn($id) {
    lookFor($id)->click();
}


/**
 * Find an element by its CSS tag and return a TestElement object
 * @param string $css The CSS path of the HTML element we are looking for
 * @return TestElement The element that was found
 */
function byCSS($css) {
    global $TESSA;
    return $TESSA->byCSS($css);
}

/**
 * @param string $class The class name of the HTML element we are looking for
 * @return TestElement
 */
function byClass ($class) {
    global $TESSA;
    return $TESSA->byClass($class);
}

/**
 * @param $path
 * @return mixed
 */
function byPath ($path) {
    global $TESSA;
    return $TESSA->byPath($path);
}

/**
 * @param string $id The ID of the HTML element we are expecting
 * @return TestElement
 */
function waitFor($id) {
    global $TESSA;
    return $TESSA->waitFor($id);
}

/**
 * @param string $tablename The name of the table to check in the database
 * @param string $fieldname The name of the field to reference
 * @param string $value The value expected for that field
 * @return RecordObject
 */
function checkDB($tablename, $fieldname, $value) {
    global $TESSA;
    return $TESSA->checkDB($tablename, $fieldname, $value);
}

/**
 * Function to retrieve records from the database
 * @param String $sql
 * @return Object
 */
function fetchRecords ($sql) {
    global $TESSA;
    return $TESSA->fetchRecords($sql);
}

/**
 * Function to retrieve a record from the database
 * @param String $sql
 * @return Object
 */
function fetchRecord ($sql) {
    global $TESSA;
    return $TESSA->fetchRecord($sql);
}


/**
 *  Creates a screen shot of the page where the error occured
 * @return HTML An Html string and link to the generated screen shot
 */
function screenShot() {
    global $TESSA;
    return $TESSA->screenShot();
}

/**
 * @param $test1
 * @param $test2
 * @return mixed
 */
function equals($test1, $test2) {
    global $TESSA;
    return $TESSA->equals($test1, $test2);
}
/**
 * @param $test1
 * @param $test2
 * @return mixed
 */
function IsDifferent($test1, $test2) {
    global $TESSA;
    return $TESSA->IsDifferent($test1, $test2);
}

/**
 *
 * @param $URL
 * @return mixed
 */
function openSite($URL) {
    global $TESSA;
    return $TESSA->openSite($URL);
}

/**
 *
 * @global type $TESSA
 * @param type $URL
 * @return type
 */
function navigateTo ($URL) {
    global $TESSA;
    return $TESSA->navigateTo ($URL);
}



/**
 * @return mixed
 */
function getActions() {
    global $TESSA;
    return $TESSA->getActions();
}

/**
 * @return mixed
 */
function deleteAllCookies() {
    global $TESSA;
    return  $TESSA->deleteAllCookies();
}


/**
 * Accept Popup
 * @return mixed
 */
function AcceptAlert() {
    global $TESSA;
    return $TESSA->acceptAlert();
}

/**
 * Dismiss Popup
 * @return mixed
 */
function DismissAlert() {
    global $TESSA;
    return $TESSA->dismissAlert();
}

/**
 * Execute JS
 * @param $script
 * @array $args
 * @return mixed
 */
function ExecJS($script, $args = array()) {
    global $TESSA;
    return $TESSA->executeJavaScript($script, $args);
}

/**
 * Class Tessa
 */
class Tessa {
    /**
     * @var Object
     */
    private $DEB; //database connection
    /**
     * @var string
     */
    private $testDir; //test directory
    /**
     * @var string
     */
    private $testServer;
    /**
     * @var string
     */
    private $browser;
    /**
     * @var
     */
    private $session;
    /**
     * @var
     */
    private $actions;
    /**
     * @var array
     */
    private $methods = []; //array of methods to use with __call
    /**
     * @var array
     */
    private $aliases = [];

    /**
     * @param string $testDir
     * @param string $browser
     * @param string $testServer
     */
    function __construct ($testDir="test", $browser="firefox", $testServer="") {
        global $TESSA;
        $TESSA = $this;


        if (!file_exists (dirname(__FILE__)."/../selenium/PHPWebDriver/__init__.php")) {
            die ("Please download the selenium driver for PHP and deploy in the web_root folder");
        }


        require_once dirname(__FILE__)."/../selenium/PHPWebDriver/__init__.php";

        if (empty(Ruth::getOBJECT("DEB"))) {
            die("You need to declare a database connection using Debby and assign Ruth a DEB object");
        } else {
            $this->DEB = Ruth::getOBJECT("DEB");
        }

        $this->testDir = $testDir;
        $this->testServer = $testServer;

        if (file_exists ($this->testDir)) {
            $this->testDir = $testDir;
            $this->browser = $browser;
            //Run the migration
            $this->doTesting();
        }


    }

    /**
     *
     */
    function doTesting() {
        $dirHandle = opendir(Ruth::getREAL_PATH() . "/" . $this->testDir);

        set_time_limit(0);

        $wd_host = 'http://localhost:4444/wd/hub';
        echo "<pre>";
        $this->message("Initialize testing on ".$wd_host." for ".$this->testServer);
        $this->message("Test Path: ".Ruth::getREAL_PATH() . "/" . $this->testDir);
        $this->message("Loading test methods into class");

        //include all the files , append all the functions / methods

        $fileArray = [];
        $i = 0;
        while (false !== ($entry = readdir($dirHandle))) {
            if ($entry != "." && $entry != ".." && stripos($entry, ".php")) {
                $fileArray[$i] = Ruth::getREAL_PATH() . "/" . $this->testDir."/".$entry;
                $i++;
            }
        }


        asort ($fileArray);
        $functions = get_defined_functions();
        $arrayFunctions = array_keys($functions['user']);
        $last_index = array_pop($arrayFunctions);
        // Include your file here.

        foreach ($fileArray as $fid => $fileName) {
            require_once $fileName;
        }

        $functions = get_defined_functions();
        $new_functions = array_slice($functions['user'], $last_index);
        unset ($new_functions[0]);
        foreach ($new_functions as $fid => $functionName) {
            $arguments = $this->get_func_argNames($functionName);
            $temp = "";
            foreach ($arguments as $aid => $argument) {
                $temp[] = '$'.$argument;
            }
            if (!empty($temp)) {
                $arguments = implode($temp, ',');
            }
            else {
                $arguments = "";
            }
            eval ('$this->methods["'.$functionName.'"] = \Closure::bind(function('.$arguments.') { '.$functionName.'('.$arguments.'); }, $this, get_class());');
        }

        $this->message("Test Functions added: ".print_r (implode ($new_functions, ","), 1));

        set_error_handler("tessaErrorHandler");

        try {
            $web_driver = new PHPWebDriver_WebDriver($wd_host);
        }
        catch(Exception $e) {

            die("Please check that your Selenium server is running and not blocked by a firewall");
        }



        if (!empty(Ruth::getSESSION("tessaSession"))) {
            $this->session = unserialize(Ruth::getSESSION("tessaSession"));
        }
        else {
            $browser_profile = new PHPWebDriver_WebDriverFirefoxProfile(dirname(dirname(__FILE__))."/selenium/firefox-profile");
            $this->session = $web_driver->session($this->browser,array(),array(),$browser_profile);
            Ruth::setSESSION("tessaSession", serialize($this->session));
        }


        echo "<h1>Running Tests!</h1>";

        //this is the default port for testing
        try {
            $this->runTests();
        }
        catch(Exception $e) {
            $browser_profile = new PHPWebDriver_WebDriverFirefoxProfile(dirname(dirname(__FILE__))."/selenium/firefox-profile");
            $this->session = $web_driver->session($this->browser,array(),array(),$browser_profile);
            Ruth::setSESSION("tessaSession", serialize($this->session));
            $this->runTests();
        }
        echo "</pre>";

    }

    /**
     * @param $funcName
     * @return array
     */
    function get_func_argNames($funcName) {
        $f = new ReflectionFunction($funcName);
        $result = array();
        foreach ($f->getParameters() as $param) {
            $result[] = $param->name;
        }
        return $result;
    }

    /**
     *
     */
    function runTests() {
        $this->message("No tests to run!");
    }

    /**
     * @param $method
     * @param $args
     * @return mixed
     */
    function __call($method, $args) {
        if (!empty($this->methods[strtolower($method)])) {
            $this->message("Calling ".$method);
            if (is_callable($this->methods[strtolower($method)])) {
                return call_user_func_array($this->methods[strtolower($method)], $args);
            }
        }
        else {
            $this->message("Test method ".$method." not available! Does the test exist?");
        }
    }

    /**
     * Display a message on the screen
     * @param $msg
     */
    function message ($msg) {
        echo $msg."...\n";
    }

    /**
     * Retrieves a record from the database based on the table, fieldname and value
     * @param String $tablename The name of the table
     * @param String $fieldname The name of the field
     * @param String $value The expected value
     * @return Object
     */
    function checkDB ($tablename, $fieldname, $value) {
        $this->DEB->commit();
        $sql = "select * from {$tablename} where {$fieldname} = '{$value}'";
        $record = $this->DEB->getRow($sql);
        return $record;
    }

    /**
     * Function to retrieve records from the database
     * @param String $sql
     * @return Object
     */
    function fetchRecords ($sql) {
        $this->DEB->commit();
        return $this->DEB->getRows($sql);
    }

    /**
     * Function to retrieve a record from the database
     * @param String $sql
     * @return Object
     */
    function fetchRecord ($sql) {
        $this->DEB->commit();
        return $this->DEB->getRow($sql);
    }

    /**
     * Display a message on the screen prefixed with Trying to
     * @param $msg
     */
    function tryingTo ($msg) {
        echo "Trying to ".$msg."...\n";
    }

    /**
     * Display a message on the screen prefixed with I want to
     * @param $msg
     */
    function IWantTo ($msg) {
        echo "I want to ".$msg."...\n";
    }

    /**
     * @param $name
     * @param $element
     */
    function addAlias ($name, $element) {
        $this->aliases[$name] = $element;
    }

    /**
     * @param $id
     */
    function clickOn($id) {
        $this->lookFor($id)->click();
    }

    /**
     * @param $id
     * @return null|TestELement
     */
    function lookFor($id) {
        global $TESSA;

        $return = $this->getAlias($id);

        if (empty($return)) {
            try {
                $return = $this->byId($id);
            } catch (Exception $e) {
                try {
                    $return = $this->byPath($id);
                } catch (Exception $e) {
                    try {
                        $return = $this->byClass($id);
                    } catch (Exception $e) {
                        die($e);
                    }
                }
            }
        }
        return $return;
    }

    /**
     * @param $name
     * @return null
     */
    function getAlias ($name) {
        if (!empty($this->aliases[$name])) {
            return $this->aliases[$name];
        }
        else {
            return null;
        }
    }

    /**
     * @param $id
     * @return TestELement
     */
    function byId($id) {
        return new TestElement($this->session->element(PHPWebDriver_WebDriverBy::ID, $id));
    }

    /**
     * @param $path
     * @return TestELement
     */
    function byPath ($path) {
        return new TestElement($this->session->element(PHPWebDriver_WebDriverBy::XPATH, $path));
    }

    /**
     * @param $class
     * @return TestELement
     */
    function byClass ($class) {
        return new TestElement($this->session->element(PHPWebDriver_WebDriverBy::CLASS_NAME, $class));
    }

    /**
     * @param $css
     * @return TestELement
     */
    function byCSS($css) {
        return new TestElement($this->session->element(PHPWebDriver_WebDriverBy::CSS_SELECTOR, $css));
    }

    /**
     * @param $id
     * @return TestELement
     */
    function waitFor($id) {
        try {

            eval ('
            $w = new PHPWebDriver_WebDriverWait($this->session);
            $w->until(
            function($session) {
                return ( count($session->elements(PHPWebDriver_WebDriverBy::ID, "'.$id.'")) + count($session->elements(PHPWebDriver_WebDriverBy::XPATH, "'.$id.'")) + count($session->elements(PHPWebDriver_WebDriverBy::CLASS_NAME, "'.$id.'")));
            }
        );');

        } catch (Exception $e) {
            echo "Could not wait for {$id} any longer !...\n";
            die ($e);
        }

        return $this->lookFor($id);
    }

    /**
     * @param $test1
     * @param $test2
     */
    function equals($test1, $test2) {
        $this->message ("Checking: '{$test1}' === '{$test2}'");
        if ($test1 !== $test2) {
            $this->failed("Found ".$test1." expected ".$test2);
        }
    }

    /**
     * @param $message
     */
    function failed($message) {
        echo "<h3>Failed: {$message}</h3>";
        echo $this->screenShot()."\n";
        die();
    }

    /**
     * A function to create screenshots when something fails
     * @return String The HTML path to the screen shot
     */
    function screenShot () {
        $imgData = base64_decode($this->session->screenshot());
        $filename = '/imagestore/screenshot'.rand(1000,9999).'.png';
        file_put_contents(Ruth::getREAL_PATH().'/'.$filename, $imgData);
        return "<a href=\"{$filename}\"><img width=\"640px\" src=\"{$filename}\"></a>";
    }

    /**
     * Reuben --- Check if two test cases are different
     * @param $test1
     * @param $test2
     */
    function IsDifferent($test1, $test2) {
        $this->message ("Checking: '{$test1}' !== '{$test2}'");
        if ($test1 == $test2) {
            $this->failed("Found ".$test1." is the same as ".$test2);
        }
    }

    /**
     * @param $URL
     * @return bool
     */
    function openSite($URL) {
        //this is the default port for testing
        $this->session->open($URL);
        $this->session->window()->maximize();
        return true;
    }

    /**
     * @param $URL
     * @return bool
     */
    function navigateTo($URL, $maximize=false) {
        //this is the default port for testing
        if ($maximize) {
            $this->session->window()->maximize();
        }
        $this->session->open($URL);

        return true;
    }

    /**
     *
     */
    function newSession() {
        Ruth::setSESSION("tessaSession", null);
    }

    /**
     * @return PHPWebDriver_WebDriverActionChains
     */
    function getActions() {
        $this->actions = new PHPWebDriver_WebDriverActionChains ($this->session);
        return $this->actions;
    }

    /**
     *
     */
    function deleteAllCookies() {
        $this->session->deleteAllCookies();
    }

    /**
     * Accept popup
     */
    function acceptAlert() {
        $this->session->accept_alert();
    }

    /**
     * Dismiss popup
     */
    function dismissAlert() {
        $this->session->dismiss_alert();
    }

    /**
     * Execute JS
     */
    function executeJavaScript($script, $args) {
        return $this->session->execute(array('script' => $script, 'args' => $args));
    }
}

/**
 * Test Element used by Tessa
 *
 **/
class TestElement
{
    /**
     * @var
     */
    private $element;

    /**
     * @param $element
     */
    function __construct($element)
    {
        $this->element = $element;

    }

    /**
     * @param $path
     * @return TestElement
     */
    function byPath($path)
    {
        return new TestElement($this->element->element(PHPWebDriver_WebDriverBy::XPATH, $path));
    }

    /**
     * @param $id
     * @return TestElement
     */
    function byId($id)
    {
        return new TestElement($this->element->element(PHPWebDriver_WebDriverBy::ID, $id));
    }

    /**
     * @param $tag
     * @return TestELement
     */
    function byTag($tag)
    {
        return new TestElement($this->element->element(PHPWebDriver_WebDriverBy::TAG_NAME, $tag));
    }

    /**
     * @param $value
     * @return TestElement
     */
    function chooseOption($value) {
        try {
            $result = $this->byCSS("option[value='{$value}']");
        } catch(Exception $e) {
            try {
                $result = $this->byCSS("input[value='{$value}']");
            }  catch (Exception $e) {
                die($e);
            }
        }
        return $result;
    }

    /**
     * @param $css
     * @return TestELement
     */
    function byCSS($css)
    {
        return new TestElement($this->element->element(PHPWebDriver_WebDriverBy::CSS_SELECTOR, $css));
    }

    /**
     * @param $class
     * @return TestElement
     */
    function byClass($class)
    {
        return new TestElement($this->element->element(PHPWebDriver_WebDriverBy::CLASS_NAME, $class));
    }

    /**
     * @param $text
     */
    function andSetText($text)
    {
        $this->setText($text, true);
    }

    /**
     * @param $text
     * @param bool $clear
     */
    function setText($text, $clear=false)
    {
        if ($clear){
            $this->element->sendKeys("\uE009"."a");
        }
        $this->element->sendKeys($text);
    }

    /**
     * @param $text
     */
    function andAddText($text)
    {
        $this->setText($text, false);
    }

    /**
     * @return mixed
     */
    function getText()
    {
        return $this->element->attribute("innerHTML");
    }

    /**
     * @return mixed
     */
    function getValue()
    {
        return $this->element->attribute("value");
    }

    /**
     * @param $attr
     * @return mixed
     */
    function getAttr($attr)
    {
        return $this->element->attribute($attr);
    }

    /**
     * @param $value
     */
    function setValue($value)
    {
        $this->element->value($value);
    }

    /**
     *
     */
    function andClick()
    {
        $this->click();
    }

    /**
     *
     */
    function click()
    {
        $this->element->click("");
    }

    /**
     * @param $name
     * @return mixed
     */
    function andAlias($name) {
        global $TESSA;

        $TESSA->addAlias($name, $this->element);

        return $this->element;
    }

    /**
     * @param $target
     * @param $options
     */
    function dragAndDrop ($target, $options) {
        $this->element->dragAndDrop( $target, $options )->perform();
    }

    /**
     *
     */
    function clickAndHold() {
        $this->element->clickAndHold();
    }

    /**
     * @return mixed
     */
    function getID() {
        return $this->element->getID();
    }

}