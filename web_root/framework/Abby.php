<?php
require_once "Shape.php";


/**
 * Abby is the AB template routing helper to assist in managing A/B templates
 * 
 * @author Andre van Zuydam <andre@xineoh.com>
 * @license GPL
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */
class Abby {

    /**
     * The path on which Abby is running 
     * @property String 
     */
    private $PATH;

    /**
     * The constructor for Abby
     * 
     * The path where the A/B template is to be run is set here   
     * 
     * @param String $PATH The variable is for the path on where the template must run
     */        
    function __construct($PATH = "") {
        if (!empty($PATH)) {
            $this->PATH = $PATH;
        }
    }

    /**
     * The stylesheet includes the bootstrap CDN
     * @return String The URL of the style sheet
     */
    private function stylesheet() {
        return alink(array("rel" => "stylesheet", "type" => "text/css", "href" => "//maxcdn.bootstrapcdn.com/bootstrap/3.3.1/css/bootstrap.min.css"));
    }
    
    /**
     * The login form for Abby
     * @return String The HTML for the login screen for Abby
     */
    function login() {
        return form(array("role" => "form", "method" => "POST", "action" => "/abby/login"), 
                h1(array("class" => "lead"), "Login"), 
                div(array("class" => "form-group"), 
                        label(array("class" => "col-sm-2", "for" => "username"), "username"), 
                        div(array("class" => "col-sm-2"), input(array("name" => "username", "class" => "form-control"), ""))
                ), 
                div(array("class" => "form-group"), 
                        label(array("class" => "col-sm-2", "for" => "password"), "password"), 
                        div(array("class" => "col-sm-2"), input(array("name" => "password", "type" => "password", "class" => "form-control")))
                ), 
                input(array("type" => "submit", "class" => "btn btn-primary"), "Login")
        );
    }

    /**
     * The validate function for Abby
     * @return Boolean This will return true or false depending on whether the user was validated properly
     */
    function validate() {
        return print_r(Ruth::getREQUEST(), 1);
    }

    /**
     * The display method is for drawing the navigation of the Ab Testing system
     * @return String HTML text where the system is currently running
     */
    function display() {
        $html = "";
        $html .= $this->stylesheet();
        switch ($this->PATH) {
            case "/abby/display":
                $html .= $this->login();
                break;
            case "/abby/login":
                $html .= $this->validate();
                break;

            default:
                $html .= "This page is not valid";
                break;
        }

        return $html;
    }

    /**
     * The default constructor
     * @return String The result of the display method
     */
    function __toString() {
        return $this->display();
    }

}
