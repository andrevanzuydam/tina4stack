<?php
/**
 * Created by PhpStorm.
 * User: andrevanzuydam
 * Date: 4/13/2015
 * Time: 2:03 PM
 *
 * Modify run tessa as needed except for the constructor
 */
class runTessa extends Tessa{

    //Make sure you have downloaded and run the stand alone selenium server
    function runTests() {
        //write your tests here

        equals(byPath("/html/head/title")->getText(), "Tina4 - Hello World!"); //Pass
        equals(byPath("/html/head/title")->getText(), "Hello World!"); //Fail

        //Functions to use
        // byId($id) -> gets an element by Id
        // byPath ($Xpath) -> gets an element by Path
        // byClass($class) -> gets an element by Class
        // waitFor($id) -> waits for an element by ID

        //Each element can use the following methods as well as the above
        //click() -> clicks on the object - use waitFor to determine if the object exists
        //setText() -> change the text (innerHTML) of an object
        //getText() -> get the text (innerHTML) of an object
        //setValue() -> change the value of an input
        //getValue() -> get the value of an input.


    }


}