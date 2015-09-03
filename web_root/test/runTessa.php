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
        IWantTo("Navigate to the extra running instance of my local site http://localhost:12346");
        NavigateTo("http://localhost:12346");
        IWantTo("Check the landing page for Tina4");
        IExpectToSee("Tina4 Release ".TINA4_RELEASE);
        IWantTo("See if there is some other things");
        IExpectToSee("Some other things");

    }


}