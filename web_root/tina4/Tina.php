<?php
/**
 * @mainpage Tina4 Stack
 *
 * @tableofcontents
 *
 * @section introduction Introduction to the Tina4Stack
 *
 * The Tina4Stack started with a need to bring some uniformity to the development I was doing in PHP and especially where
 * I was working with other developers on the same project.  I have often wondered if I should start using one of the popular
 * frameworks out there but have decided against it for various reasons.  Instead I have decided to share with you my work bench
 * so to speak where you can also benefit from the work that has gone into this tool.
 *
 * @note Tina4 simply means "This is NOT another Framework!"
 *
 *
 *
 *
 *<IMG SRC="https://img.shields.io/sourceforge/dw/tina4stack.svg" />
 * <A HREF="https://sourceforge.net/projects/tina4stack/files/latest/download">
 * Download Latest Release
 * </A>
 *
 * @section what_is_in_the_stack What is in the Stack?
 *
 * The Stack is built up from the following technologies which hopefully will get your development up and running in minutes.
 * Incidentally we do not deploy any database engines for you and this should be done separately which is something we prefer.
 *
 * <UL>
 *  <LI>Nginx</LI>
 *  <LI>PHP 5.6 <OL>
 *                  <LI>Curl is enabled</LI>
 *                  <LI>GD2 is enabled</LI>
 *                  <LI>MySQLi is enabled</LI>
 *                  <LI>SQLite3 is enabled</LI>
 *                  <LI>XDebug is enabled on port 9000</LI>
 *                  <LI>XCache is enabled</LI>
 *                  <LI>Exif is enabled</LI>
 *                  <LI>FileInfo is enabled</LI>
 *                  <LI>Mbstring is enabled</LI>
 *                  <LI>OpenSSL is enabled</LI>
 *                  <LI>Soap is enabled</LI>
 *                  <LI>XSL is enabled</LI>
 *              </OL>
 *  </LI>
 *  <LI>Doxygen - both Windows & Linux scripts - you are reading generated documentation now!</LI>
 *  <LI>Swagger UI</LI>
 *  <LI>Selenium Client</LI>
 *  <LI>XCache UI</LI>
 * </UL>
 *
 * @note On my todo list are the Tina4 clients for Linux & MacOS
 *
 * @section what_is_tina4 What is Tina4
 *
 * Tina4 is a collection of tools which will help you with your PHP development.  In order to make the tools easy to remember and use we
 * have given them names so that you can identify with each of the personalities when you are working.  For example Ruth is responsible for
 * Routing, Maggy is responsible for migrations.
 *
 * Now it may seem daunting to make use of tools you did not assist in building but you can use as little or as much of the tools as you want.
 * After going over the core functionlity of each of the girls in the stack I will explain the basics of how everything is supposed to work.
 *
 *
 * @section tina Tina
 *
 * Tina is the CEO of the Tina4 Corporation, as anyone knows a successful company needs a strong leader. Her skills lie in the organization of each
 * of the girls and making sure each person collaborates properly on the task at hand.  Tina is passionate about providing solutions to her customers
 * and it is very important to her that each peson is doing the job they were assigned.  In a nutshell we have the following:
 *
 * <UL>
 *   <LI> Routing </LI>
 *   <LI> Migrations </LI>
 *   <LI> Database Abstraction </LI>
 *   <LI> Code Simplification </LI>
 *   <LI> Templating </LI>
 *   <LI> UI Testing with Selenium </LI>
 *   <LI> WYIWYG Report Generation </LI>
 *   <LI> Email Handling </LI>
 *   <LI> Object Abstraction </LI>
 * </UL>
 *
 * @section ruth Ruth
 *
 * @image html ruth_intro.png
 *
 * Ruth as we have said before is responsible for routing. Routing as you may be familiar with is the ability to handle URL requests to your website.
 * You may be quite comfortable with writing routing tables in Apache or NGINX. If you decide to use Ruth you immediately have an application which will
 * run on NGINX and Apache equally well.  Ruth also handles security and sessions which are explained in her section.
 *
 * A quick example of Ruth in action, you would get this link at http://localhost:12345/testing when developing.
 * The output of this route would be @a Hello @a Tina4!
 *
 * @subsection ruth_example Routing Example
 * @code{.php}
 *
 * //Adding a GET route
 * Ruth::addRoute (RUTH_GET, "/testing", function () {
 *      echo "Hello Tina4!";
 * });
 *
 * @endcode
 *
 * @note Simply adding a PHP file under the routes folder and adding the above code is sufficient for Ruth to start working.
 *
 *
 * @note The setup of the web server routes all URL requests to the tina4.php file which in turn relays them to Ruth. If your webserver has not been setup
 * the stack will attempt to give you a configuration for Nginx or Apache.
 *
 * @section cody Cody
 *
 * @image html cody_intro.png
 *
 * Cody was written to automate things that may take up a lot of repetition.  At this point in time Cody helps automate Bootstrap functionlity.
 * Cody has a very neat Ajax handler which automatically reads form variables and passes them to the URL you specify.  The Ajax handler of Cody can
 * also handle file uploading via Ajax and when used with Ruth and Debby you have very little coding to do.
 *
 * @subsection cody_example Example of Bootstrap Input
 * The following code will create a properly formatted Bootstrap input with all the correct classes. You can see this in action in Maggy http://localhost:12345/maggy/create
 * @code{.php}
 *
 *      echo (new Cody())->bootStrapInput("firstName", "First Name", "Full Names", $defaultValue = "", "text");
 *
 * @endcode
 *
 * @subsection code_example_ajax Example of Ajax Handler
 * The following code will create a Javascript function called @a callAjax which can be used to run Ajax commands to your routing
 * @code{.php}
 *
 * echo (new Cody())->ajaxHandler();
 *
 * @endcode
 *
 * @note To include the Ajax handler with Kim the code is {{Cody:ajaxHandler}}!
 *
 * The ajax Handler in Cody takes on the following params and should be used in Javascript on your page.
 *
 * @code
 *
 *  callAjax('/testing', //route to do a request to
 *           'myDiv', //The id of the tag or input you wish to target, leave blank if your ajax result is Javascript
 *           {id:10001}, //some JSON to pass with the request, Ruth will make it into request variables
 *           'post', //get,post,delete etc...
 *           true); //true or false, set to false to rewrite the URL in the browser after an Ajax call
 *
 * @endcode
 *
 * @section kim Kim
 *
 * @image html kim_intro.png
 *
 * Kim likes to work with content, if you need something working quickly in HTML whilst still having access to the power
 * of PHP then you need to see what Kim can do.
 * In order for Kim to work correctly you need to have an assets folder under your document root.  In this folder she will look for
 * the following folders:
 *
 * <UL>
 *  <LI>pages</LI>
 *  <LI>snippets</LI>
 *  <LI>forms</LI>
 * </UL>
 *
 * Here are some examples of Kim in action, hopefully from the examples you will be creating dynamic pages in a few minutes.
 *
 * @subsection kim_include Including HTML snippets
 * The {{include:snippet/[snippet_name]}} will include a section of a file into the existing file.  This is much like the normal
 * require or include of PHP except that it has the ability to render variables in {braces}.  The path used is relative to the
 * assets folder, so all your includes should be under assets.
 *
 * @code
 *
 *   {{include:snippet/header}}
 *
 *   <h1>Some normal html </h1>
 *
 *   {{include:snippet/footer}}
 *
 * @endcode
 *
 * @note Kim adds .html automatically so snippet/footer would parse to /web_root/assets/snippet/footer.html
 *
 * @subsection kim_parse_variable Parsing Variables
 *
 * Kim understands that it is cumbersome to reference variables in your HTML files.  More so if you need to make sure their scope
 * is correct.
 *
 * The normal way
 * @code
 *
 * <h1>
 * <?php
 *   echo $someVariable;
 * ?>
 * </h1>
 *
 * @endcode
 *
 * Kim's way
 * @code
 *
 * <h1>{someVariable}</h1>
 *
 * @endcode
 *
 * @subsection kim_calling_php Calling built in PHP functions
 *
 * Kim understands that we need to access some built in functions in our HTML templates, so she made it easy.
 *
 * The normal way
 * @code
 * <h1>
 * <?php
 *   echo substr("Some really long sentence!",0,4)."...";
 * ?>
 * </h1>
 * @endcode
 *
 * Kim's way
 * @code
 *
 * <h1>{{call:substr?"Some really long sentence!",0,4}}...</h1>
 *
 * @endcode
 *
 * @note Kim only needs you to put your params in quotes when you have spaces or commas in between
 *
 * @subsection kim_classes Calling methods on classes
 *
 * Kim is able instantiate classes as call their methods which should return either text or arrays
 *
 * The normal way
 * @code
 *  <h1>
 *  <?php
 *    $myObject = new MyObject();
 *    echo $myObject->getName();
 *  ?>
 *  </h1>
 * @endcode
 *
 * Kim's way
 * @code
 *   <h1>{{MyObject:getName}}</h1>
 * @endcode
 *
 *
 *
 *
 * @note Kim uses {} to designate variables and {{}} to designate objects or control structures.
 *
 * @section debby Debby
 *
 * @image html debby_intro.png
 *
 *
 * @section maggy Maggy
 *
 * @image html maggy_intro.png
 *
 *
 *
 * @section emma Emma
 *
 * @image html emma_intro.png
 *
 *
 *
 * @author Andre van Zuydam
 * @version 1.0.0
 * @copyright Tina4
 */