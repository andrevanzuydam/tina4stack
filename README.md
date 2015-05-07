# README #

### Tina4 ###
Tina4 - This is not another framework. The download you will get consists of the following technologies:
* Nginx Webserver
* PHP with XDebug
* Report Manager compiled for web supporting MySQL, Firebird, SQLite
* PHPDoc
* Selenium libraries
* Netbeans project to get started quickly.

### Getting Started ###
Download the latest netbeans, extract the downloaded zip file and open the project in netbeans.  Run the tina4.exe file to get the web service running.  You can use the browser icon to open the default development enviroment which is available at http://localhost:12345

### Meet the Girls ###
The stack comprises of a couple of classes that have been included for you under the tina4 folder, they work together to provide you with the full functionality of the system.  You can use them individually in a project or together but we recommend using at least Tina, Ruth and Debby.  Find out more about them below. 

#### Tina ####
Tina manages automatic inclusion and direction of your project, by default she is looking for classes defined in the project folder.  You won't have to worry about including these classes in your project as Tina handles all this for you so you can focus on programming.

#### Ruth ####
Ruth is a routing system which allows you to structure your calls to the classes in your projects folder. She supports the use of variables in your routing paths and handles sessions and security.  She also handles request & session variables.  Use the documentation to get more familiar with her skills.  Ruth works from files you deploy in the routes & roles folder.  The roles folder defines permissions and is probably too long to go into in a short space of time.

#### Debby ####
Debby is a database expert and she can connect with pretty much any database using ODBC.  She has native support for MSSQL, MySQL, Firebird, Oracle, SQlite & Postgres.  She is able to handle things like blob managment, parameterised queries and facilitates the dynamic creation of Insert & Update statements.  She has some skill in translating between database dialects as well and keeps improving her language.

#### Maggy ####
Maggy handles all your database migrations you place in the migration folder.  We all know that changes happen to a database after design and this is what Maggy does.  She needs to have a connection setup for Debby to work as she will keep a log of her changes in your default database.  Ruth also keeps a default route available for Maggy at http://localhost:12345/maggy

#### Reta ####
Reta is a reporting tool which allows you to use the Report Manager WYSIWG tool to create reports for your PHP applications.  She will prompt you to create an ini file which will be specific for your environment and you can provide her with the database connection properties.  Drop the report templates she uses in the reports folder and you'll have the option to generate PDF & CSV files in no time.

#### Emma ####
Emma handles the sending of emails which are nicely intercepted for you by the Tina4 interface during development.  You will be able to see how a production enviroment will handle emails without having an SMTP service on your development machine.  Emma has never failed us when deploying to a production server.

#### Tessa ####
Tessa assists us in creating tests for the Selenium web service.  Download the stand-alone web service from the Selenium site and run it before spinning up tessa at http://localhost:12345/tessa.  All your tests can be placed as functions under the test folder and Tessa will automatically build them into callable methods in the runTessa.php file. She is currently improving her skills and does a lot of the ground work for you.

#### Phoebe ####
Phoebe is currently in training and will be responsible for building your latest work into a runnable Phar file.  Phoebe is still trying to understand how to deploy assets for a website in an effective manner.

#### Cody ####
Cody is a collection of ready made methods to create bootstrap inputs, tables and AJAX calls.  She improves CRUD development by making forms and tables from SQL statements.


### Deployment ###
Simply upload the files under the web_root folder in the stack and Ruth will help you setup your Apache or Nginx by giving you the appropriate settings.  Remember tina4stack runs on both windows and linux.

### So I am having issues or found a bug I would like to report? ###
* Please log the issue here on Github
* Send an email to andrevanzuydam AT gmail DOT com
