<?php
/**
 * Created by PhpStorm.
 * User: andrevanzuydam
 * Date: 4/13/2015
 * Time: 1:14 PM
 */
global $TESSA;

function message ($msg) {
    echo date("d/m/Y h:i:s ").$msg."...\n";
}

function byId($id) {
    global $TESSA;
    return $TESSA->byId($id);
}

function byCSS($css) {
    global $TESSA;
    return $TESSA->byCSS($css);
}

function byClass ($class) {
    global $TESSA;
    return $TESSA->byClass($class);
}

function byPath ($path) {
    global $TESSA;
    return $TESSA->byPath($path);
}

function waitFor($id) {
    global $TESSA;
    return $TESSA->waitFor($id);
}

function checkDB($tablename, $fieldname, $value) {
    global $TESSA;
    return $TESSA->checkDB($tablename, $fieldname, $value);
}


function screenShot() {
    global $TESSA;
    return $TESSA->screenShot();
}

function equals($test1, $test2) {
    global $TESSA;
    return $TESSA->equals($test1, $test2);
}

class Tessa {
    private $DEB; //database connection
    private $testDir; //test directory
    private $testServer;
    private $browser;
    private $session;
    private $methods = []; //array of methods to use with __call

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

    function checkDB ($tablename, $fieldname, $value) {
        $this->DEB->commit();
        $sql = "select * from {$tablename} where {$fieldname} = '{$value}'";
        $record = $this->DEB->getRow($sql);
        //print_r ($record);
        return $record;
    }

    function screenShot () {
        $imgData = base64_decode($this->session->screenshot());
        $filename = 'images/screenshot'.rand(1000,9999).'.png';
        file_put_contents(Ruth::getREAL_PATH().'/'.$filename, $imgData);
        return "<a href=\"{$filename}\"><img width=\"640px\" src=\"/{$filename}\"></a>";
    }

    function message ($msg) {
        echo date("d/m/Y h:i:s ").$msg."...\n";
    }

    function byId($id) {
        return new TestElement($this->session->element(PHPWebDriver_WebDriverBy::ID, $id));
    }

    function byCSS($css) {
        return new TestElement($this->session->element(PHPWebDriver_WebDriverBy::CSS_SELECTOR, $css));
    }

    function byClass ($class) {
        return new TestElement($this->session->element(PHPWebDriver_WebDriverBy::CLASS_NAME, $class));
    }

    function byPath ($path) {
        return new TestElement($this->session->element(PHPWebDriver_WebDriverBy::XPATH, $path));
    }

    function waitFor($id) {
        $this->message("Waiting for ".$id);
        eval ('
            $w = new PHPWebDriver_WebDriverWait($this->session);
            $w->until(
            function($session) {
                return count($session->elements(PHPWebDriver_WebDriverBy::ID, "'.$id.'"));
            }
        );');

        return new TestElement( $this->session->element(PHPWebDriver_WebDriverBy::ID, $id) );
    }

    function failed($message) {
        echo "<h3>Failed: {$message}</h3>";
        echo $this->screenShot()."\n";
        die();
    }

    function equals($test1, $test2) {
        $this->message ("Checking: '{$test1}' === '{$test2}'");
       if ($test1 !== $test2) {
           $this->failed("Found ".$test1." expected ".$test2);
       }
    }

    function __construct ($testDir="test", $browser="firefox", $testServer="http://localhost:12346/") {
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

    function get_func_argNames($funcName) {
        $f = new ReflectionFunction($funcName);
        $result = array();
        foreach ($f->getParameters() as $param) {
            $result[] = $param->name;
        }
        return $result;
    }

    function doTesting() {
        $dirHandle = opendir(Ruth::getREAL_PATH() . "/" . $this->testDir);
        echo "<pre>";
        set_time_limit(600);

        $wd_host = 'http://localhost:4444/wd/hub';
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

        $web_driver = new PHPWebDriver_WebDriver($wd_host);

        $this->session = $web_driver->session($this->browser);

        //this is the default port for testing
        $this->session->open($this->testServer);

        $this->session->window()->maximize();
        //go through each test, load then up and run them
        $this->runTests();
        echo "<pre>";
    }

    function runTests() {
        $this->message("No tests to run!");
    }

}

/**
 * Test Element used by Tessa
 *
 **/
class TestELement
{
    private $element;

    function byPath($path)
    {
        return new TestElement($this->element->element(PHPWebDriver_WebDriverBy::XPATH, $path));
    }

    function byId($id)
    {
        return new TestElement($this->element->element(PHPWebDriver_WebDriverBy::ID, $id));
    }

    function byTag($tag)
    {
        return new TestElement($this->element->element(PHPWebDriver_WebDriverBy::TAG_NAME, $tag));
    }

    function byCSS($css)
    {

        return new TestElement($this->element->element(PHPWebDriver_WebDriverBy::CSS_SELECTOR, $css));
    }

    function byClass($class)
    {
        return new TestElement($this->element->element(PHPWebDriver_WebDriverBy::CLASS_NAME, $class));
    }

    function setText($text)
    {
        $this->element->sendKeys($text);
    }

    function getText()
    {
        return $this->element->attribute("innerHTML");
    }

    function getValue()
    {
        return $this->element->attribute("value");
    }

    function getAttr($attr)
    {
        return $this->element->attribute($attr);
    }

    function setValue($value)
    {
        $this->element->value($value);
    }

    function click()
    {
        $this->element->click("");
    }

    function __construct($element)
    {
        $this->element = $element;

    }

}