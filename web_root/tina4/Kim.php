<?php
/**
 * Kim is a class to handle the menu driven roles and routes for Tina4
 * @link http://localhost:12345/dokuwiki/kim
 */
class Kim {
    /**
     * This is a link to an sqlite database which holds the permissions for the system
     * @var object Debby 
     */
    var $createDB;
    var $KIM;
    var $defaultPages = ["index.html", "index.php", "home.html"];
    var $defaultExtensions = [".html", ".php"];
    

    
    /**
     * Create a database connection for Kim to use.
     */
    function __construct() {
        $this->createDB = false;
        if (!file_exists(realpath(__DIR__."/../")."/kim.db")) {
            $this->createDB = true;
        }
        
        $this->KIM = new Debby( realpath(__DIR__."/../")."/kim.db", $username = "", $password = "", $dbtype = "sqlite3", $outputdateformat = "YYYY-mm-dd", "KIM" ); 
        Ruth::setOBJECT("KIM", $this->KIM);
        /**
         * Check if we need to be creating the database for the menus
         */
        if ($this->createDB) {
            $this->createDatabase();
        }
        
        if (Ruth::getOBJECT("DEB")) {
            if (!file_exists ( Ruth::getDOCUMENT_ROOT()."/migrations/19000101100000 initial_kim_global_settings.sql" )) {
                
                $sqlGlobalSettings = "create table global_setting (
                                            global_setting_id integer default 0 not null,
                                            global_name varchar (100) default 'ENVIRONMENT',
                                            global_value varchar (200) default 'Development',
                                            description text default '',
                                            created timestamp default 'now',
                                            updated timestamp default 'now',
                                            primary key (global_setting_id)
                                        );";
                file_put_contents (Ruth::getDOCUMENT_ROOT()."/migrations/19000101100000 initial_kim_global_settings.sql", $sqlGlobalSettings );
            }
        }
                
        //Add the default routes for Kim
        Ruth::addRoute( 
            RUTH_GET, 
            "/kim/logout",
            function () {
                Ruth::setSESSION("KIM", ["loggedin" => 0]);
                Ruth::redirect("/kim/login");
            }                        
        );
        
       
        
       
               
        
        Ruth::addRoute (RUTH_GET, 
            "/kim/menu/get/{menuId}",
            function($menuId) {
                echo (new Kim())->getMenuItemForm($menuId);
            }
        );
        
        Ruth::addRoute (RUTH_GET, 
            "/kim/menu/{controller}",
            function($controller) {
                switch ($controller) {
                    case "menu_tree":
                        echo (new Kim())->getMenuTree();
                    break;
                    case "insert":
                        echo (new Kim())->getInsertMenuItemForm();
                    break;
                    default:
                        echo "{$controller} not found";  
                    break;    
                }
            }
        );
        
        Ruth::addRoute (RUTH_POST, 
            "/kim/menu/{controller}",
            function($controller) {
                switch ($controller) {
                    case "menu_list":
                        echo (new Kim())->getMenuList();  
                    break;
                    case "insert":
                        echo (new Kim())->insertMenuItem();
                        echo (new Kim())->getMenuTree(); 
                    break;
                    case "delete":
                        echo (new Kim())->deleteMenuItem();
                    break;
                    case "update":
                        echo (new Kim())->updateMenuItem();
                        echo (new Kim())->getMenuTree(); 
                    break;
                    default:
                        echo "{$controller} not found";  
                    break;    
                }
            }
        );
        
         Ruth::addRoute (RUTH_GET, 
            "/kim/*",
            function() {
                 (new Kim())->display();
            }
        );
        
        Ruth::addRoute (RUTH_POST, 
            "/kim/*",
            function() {
                 (new Kim())->updatePOST();
            }
        );
    }
    
    /**
     * Function to link Array by Element IDs for the preg match all function
     * @param type $m
     * @return type
     */
    function linkArray($m){ 
        $rt = [];
        for ($z = 0;$z < count($m);$z++){ 
            for ($x = 0;$x < count($m[$z]);$x++) { 
                $rt[$x][$z] = $m[$z][$x]; 
            } 
        }
        return $rt; 
    } 
    
 

    
    /**
     * Gets the default page to display where possible which will be found under assets
     * @param String $pageName The page to try and load or the path to load
     * @return type
     */    
    function getDefaultPage($pageName) {
        $assetFolder = Ruth::getDOCUMENT_ROOT()."/assets";
        $html = "";
        $fileName = "";
        
        if ($pageName === "/") {
            foreach ($this->defaultPages as $id => $page) {
                if (file_exists($assetFolder."/pages/".$page)) {
                    $fileName = $assetFolder."/pages/".$page;
                    break;
                }
            }
        }
            else {
                foreach ($this->defaultExtensions as $eid => $extension) {
                    if (file_exists($assetFolder.$pageName.$extension)) {
                        $fileName = $assetFolder.$pageName.$extension;
                        
                        break;
                    }
                }
            }
        
        if (file_exists($fileName)) {    
            $template = file_get_contents($fileName);
            if (!empty($template)) {
              $html = $this->parseTemplate($template);
            } else {
              $html = "<span style=\"color: red; font-weight: bold\">[File {$pageName}{$extension} included in your template was found to be empty!]</span>";  
            }
                    
        }
        
        return $html;        
    }
    
    /**
     * Function to create the tables for Kim to be used with roles, routes, menus
     */
    function createDatabase () {
        $sqlRole = "create table if not exists role (
                        role_id integer primary key autoincrement,
                        name varchar (100) default '',
                        description varchar (1000) default '',
                        status varchar (20) default 'Active',
                        created timestamp
                    );
                    CREATE INDEX if not exists idx_role_id on role (role_id);
                    ";
        
        $this->KIM->exec($sqlRole);
        
        $this->KIM->insert ("role", ["role_id" => 0, 
                                    "name" => "Public", 
                                    "description" => "The role which is set for public use", 
                                    "created" => "current_timestamp"
                                    ]);
        $this->KIM->insert ("role", ["role_id" => 1, 
                                    "name" => "System", 
                                    "description" => "The system role which handles the menu creation and everything", 
                                    "created" => "current_timestamp"
                                    ]);
        
        $sqlUser = "create table if not exists user (
                        user_id integer primary key autoincrement,
                        first_name varchar (100) default '',
                        last_name varchar (100) default '',
                        email varchar (200) default '',
                        passwd varchar (100) default '',
                        created timestamp,
                        status varchar (20) default 'Active',
                        photo blob,
                        role_id integer default 0 not null references role (role_id) on update cascade                       
                    );    
                    CREATE INDEX if not exists idx_user_id on user (user_id);
                    CREATE INDEX if not exists idx_user_role_id on user (role_id);
                ";
        
        $this->KIM->exec($sqlUser);
        
        
        $this->KIM->insert ("user", ["user_id" => 0,
                                     "first_name" => "None",
                                     "last_name" => "",
                                     "email" => "",
                                     "passwd" => "",
                                     "created" => "current_timestamp",
                                     "status" => 'Disabled',
                                     "role_id" => "0"   
                                    ]);
        $this->KIM->insert ("user", ["user_id" => 1,
                                     "first_name" => "Admin",
                                     "last_name" => "",
                                     "email" => "admin",
                                     "passwd" => password_hash("admin", PASSWORD_DEFAULT),
                                     "created" => "current_timestamp",
                                     "status" => 'Active',
                                     "role_id" => "1"   
                                    ]);
        
        $sqlMenu = "create table if not exists menu (
                        menu_id integer primary key autoincrement,
                        name varchar (100) default '',
                        icon varchar (100) default '',
                        target varchar (20) default '_self',
                        internal integer default 1,
                        path blob,
                        created timestamp,    
                        parent_id integer default 0,
                        order_index integer default 0,
                        status varchar (20) default 'Active',
                        system_menu integer default 1
                        
                );
                CREATE INDEX if not exists idx_menu_id on menu (menu_id);
                CREATE INDEX if not exists idx_menu_parent_id on menu (parent_id);
                CREATE INDEX if not exists idx_menu_order_index on menu (order_index);
                ";
        
        $this->KIM->exec($sqlMenu);
        $this->KIM->insert("menu", ["menu_id" => 0,
                                    "name" => "root",
                                    "created" => "current_timestamp",
                                    "status" => 'Disabled'                
                                    ]);
        $this->KIM->insert("menu", ["menu_id" => 1,
                                    "name" => "Profile",
                                    "created" => "current_timestamp",
                                    "status" => 'Active',
                                    "order_index" => 100
                                    ]);
        $this->KIM->insert("menu", ["menu_id" => 2,
                                    "name" => "Routes",
                                    "created" => "current_timestamp",
                                    "status" => 'Active',
                                    "path" => "/kim/routes",
                                    "order_index" => 200
                                    ]);
        $this->KIM->insert("menu", ["menu_id" => 3,
                                    "name" => "System",
                                    "created" => "current_timestamp",
                                    "status" => 'Active',
                                    "order_index" => 300    
                                    ]);
        $this->KIM->insert("menu", ["menu_id" => 4,
                                    "name" => "Update",
                                    "created" => "current_timestamp",
                                    "status" => 'Active',
                                    "parent_id" => 1,
                                    "path" => "/kim/profile",
                                    "order_index" => 100    
                                    ]);
        $this->KIM->insert("menu", ["menu_id" => 5,
                                    "name" => "Logout",
                                    "created" => "current_timestamp",
                                    "status" => 'Active',
                                    "parent_id" => 1,
                                    "path" => "/kim/logout",
                                    "order_index" => 200    
                                    ]);
        $this->KIM->insert("menu", ["menu_id" => 6,
                                    "name" => "Menus",
                                    "created" => "current_timestamp",
                                    "status" => 'Active',
                                    "parent_id" => 3,
                                    "path" => "/kim/menus",
                                    "order_index" => 100    
                                    ]);
        $this->KIM->insert("menu", ["menu_id" => 7,
                                    "name" => "Users",
                                    "created" => "current_timestamp",
                                    "status" => 'Active',
                                    "parent_id" => 3,
                                    "path" => "/kim/users",
                                    "order_index" => 200    
                                    ]);
        
        $this->KIM->insert("menu", ["menu_id" => 8,
                                    "name" => "User Types",
                                    "created" => "current_timestamp",
                                    "status" => 'Active',
                                    "parent_id" => 3,
                                    "path" => "/kim/user_types",
                                    "order_index" => 300    
                                    ]);
        
        $this->KIM->insert("menu", ["menu_id" => 9,
                                    "name" => "Global Settings",
                                    "created" => "current_timestamp",
                                    "status" => 'Active',
                                    "parent_id" => 3,
                                    "path" => "/kim/global_settings",
                                    "order_index" => 400    
                                    ]);
        
        $this->KIM->insert("menu", ["menu_id" => 10,
                                    "name" => "Tools",
                                    "created" => "current_timestamp",
                                    "status" => 'Active',
                                    "parent_id" => 0,                                   
                                    "order_index" => 500    
                                    ]);
        
        $this->KIM->insert("menu", ["menu_id" => 11,
                                    "name" => "Flush XCache",
                                    "created" => "current_timestamp",
                                    "status" => 'Active',
                                    "parent_id" => 10,
                                    "path" => "/kim/flush_xcache",
                                    "order_index" => 100    
                                    ]);
        
        $sqlMenuRole = "create table if not exists link_menu_role (
                            menu_id integer default 0 not null references menu (menu_id) on update cascade on delete cascade,
                            role_id integer default 0 not null references role (role_id) on update cascade on delete cascade,    
                            created timestamp,
                            primary key (menu_id, role_id)
                        );          
                ";
        $this->KIM->exec ($sqlMenuRole);
        $sqlAudit =     "create table if not exists audit (
                            audit_id integer primary key autoincrement,
                            audit_type varchar (20) default 'NONE',
                            description blob,
                            created timestamp,
                            user_id integer default 0 not null references user (user_id) on update cascade
                        );
                        CREATE INDEX if not exists idx_audit_id on audit (audit_id);
                        CREATE INDEX if not exists idx_audit_user_id on audit (user_id);
                        ";
        $this->KIM->exec ($sqlAudit);
        
        
        $sqlRoute =     "create table if not exists route (
                            route_id integer primary key autoincrement,
                            route_type varchar (20) default 'GET',
                            description blob,
                            path varchar (200) default '/',
                            target blob,                       
                            status varchar (20) default 'Active',
                            role_id integer default 0 not null references route (route_id) on update cascade
                        );
                        CREATE INDEX if not exists route_id on route (route_id);
                    ";
        $this->KIM->exec ($sqlRoute);
        
        
    }
    /**
     * This is an string of variables in the form var1,var2 or enclosed in quotes "var1","var2"
     * @param type $input
     * @return string
     */
    function getCallParams($input) {
        $result = [];
        $otherResults = [];
        
        if (strpos($input, '",')) {
            $result = explode ('",', $input);
            
            foreach ($result as $rid => $resultValue){
                if (trim($resultValue)[0] === '"') {
                    $result[$rid] = substr ( trim($resultValue), 1 );
                }
                    else { //see if we may have other results
                        $checkParams = explode (",", $resultValue);
                                                
                        if (count($checkParams) > 1) {
                          $otherResults = array_merge($otherResults, $checkParams);
                          unset($result[$rid]);
                        }
                    }
            }
            $result = array_merge ($result, $otherResults);
        }
            else {
                $result = explode (",", $input);
        }          
              
        return $result;
    }
    
    /**
     * The parseSnippets function looks for nested functionality in the template
     *  Example :   <ul>
     *              {{className:callSomething}}
     *                  <li> {SOME_NAME} </li>
     *              {{/className::callSomething}}
     *              </ul>
     * @param Array $elements   An array of elements
     * @param String $template  The html template from file or memory
     * @param Array $data
     * @return type
     */
    function parseSnippets ($elements, $template, $data) {
        $checkSum = "element".md5(print_r($template, 1));
        
        if (TINA4_HAS_CACHE) {
           $tempElements = xcache_get ($checkSum);
        }
       
        
        if (empty($tempElements)) {
           
            //copy the template to modify it so we can replace code after extracting the code snippets
            $modifiedTemplate = $template;
            
            //Match all if conditions to see what todo with them
            $ifElements = $this->matchConditions($modifiedTemplate);
            
            $controls = [];
            
            
            if (count($ifElements) > 0) {
                foreach ($ifElements as $ifeid => $ifElementResult) {

                   $ifTag = "[[if".$ifeid."]]"; 
                   $snippetLength = $ifElementResult["coords"]["end"] - $ifElementResult["coords"]["start"] - strlen($ifTag);
                   $modifiedTemplate = substr_replace ($modifiedTemplate, 
                                                                    $ifTag.str_repeat (" ", $snippetLength ), 

                                                                    $ifElementResult["coords"]["start"], //}}<element> - we account for the }}
                                                                    $snippetLength+strlen($ifTag) ); //accounting for the {{className}}      

                   $controls[] = ["ifTag" => $ifTag, "if" => $ifElementResult["if"], "else" => $ifElementResult["else"]];
                }
            }
           
            $lookup = $elements;
            foreach ($elements as $eid => $element) {
                $className = $element[0];
                if ($className[0] !== "/") { //look for all classes that dont have a / which is the end tag
                    $lookupId = $eid+1;
                    $ignoreCounter = 0;
                    //look for the ending tag of this snippet
                    while ($lookupId < count($lookup)) {
                        $lelement = $lookup[$lookupId][0];
                        if ($lelement === $className) { //found a nested class
                            $ignoreCounter++; 
                        }

                        if ($lelement === "/".$className) {
                            $ignoreCounter--;
                        }

                        if ($ignoreCounter < 0) {
                            $elements[$eid]["snippet_start"] = $element[1]+strlen($className)+2;
                            $elements[$eid]["snippet_stop"] = $lookup[$lookupId][1]-2;
                            $elements[$eid]["snippet"] =  substr ($template,$elements[$eid]["snippet_start"],$elements[$eid]["snippet_stop"]-$elements[$eid]["snippet_start"]);



                            $snippetLength = strlen ($elements[$eid]["snippet"]) +  strlen('{{/'.$className.'}}');
                            $modifiedTemplate = substr_replace ($modifiedTemplate, 
                                                                str_repeat (" ", $snippetLength ), 

                                                                $elements[$eid]["snippet_start"], //}}<element> - we account for the }}
                                                                $snippetLength ); //accounting for the {{className}}

                            //delete all nested elements between the ids
                            for ($i = $eid+1; $i < $lookupId; $i++) {
                                unset ($elements[$i]);
                            }

                            break;
                        }

                        $lookupId++;
                    }
                }
                  else {
                      unset ($elements[$eid]);
                  }      

            }
            //Store the elements in a cache
            if (TINA4_HAS_CACHE) {
                xcache_set ($checkSum, ["elements" => $elements,  "controls" => $controls, "modifiedTemplate" => $modifiedTemplate]);
            }
        }
            else {
                $elements = $tempElements["elements"];
                $controls = $tempElements["controls"];
                $modifiedTemplate = $tempElements["modifiedTemplate"];
            }
        
        //we need to see if we have variable inside the called classes
        foreach ($elements as $eid => $element) {
            if (empty($element[0])) {
                unset ($elements[$eid]);
            }
                else {
                    if (!empty($data)) {
                        foreach ($data as $index => $record) {
                            $elements[$eid][0] = str_replace ("{".$index."}", $record, $elements[$eid][0]);
                            //replace in the template the occurance
                            $modifiedTemplate = preg_replace ("{".str_replace ("?", '\?', $element[0])."}", $elements[$eid][0] , $modifiedTemplate, 1);
                        }
                    }    
                }
        }
        
        return ["elements" => $elements, "controls" => $controls, "template" => $modifiedTemplate];
    }
    
    
    /**
     * The template parser of kim is quite important for rendering content, she takes a string of template and optionally record / object / array of data to use for parsing.
     * A template may contain PHP code for whatever reason.
     * 
     * //default language is as follows
     * 
     * {variable}
     * 
     * Use of this is for when you have a variable in PHP and you want to display it.  This will check defines etc
     *  
     * {OBJECT}
     * 
     * This is normally provided from the DATA parameter and will be parsed first.
     *
     * {{Kim:phpinfo?1}} //call the Kim object phpinfo method, with first variable = 1
     * 
     * {{call:substr?test,1,2}} //call substr with variable params, this should return "es"
     * 
     * {{call:substr?"I am first, user's pet",1,2}} //call substr with params which have commas in 
     * 
     * {{include:/path/to/file}} //This will include a kim file and parse it
     *   
     * @param type $template a string template of content
     * @param type $data - an array or object to itterate through with data for parsing
     */
    function getTemplate ($template, $data="") {
       return $this->parseTemplate($template, $data); 
    }
    
    function parseTemplate ($template, $data="") {
        
        try {
            //get a checksum for the template
            $checkSum = "template".md5 ($template);
            if (TINA4_HAS_CACHE && !empty(xcache_get($checkSum))) {
                $template = xcache_get ($checkSum);
                //TODO: work out how to refresh cache dynamically
            }
                else {
            //first eval the template . this will load code parts into memory which are PHP code.
             $assetFolder = Ruth::getDOCUMENT_ROOT()."/assets/";
             foreach ($this->defaultExtensions as $eid => $extension) { 
                 if (file_exists($assetFolder.$template.$extension)) {
                     
                     $template = file_get_contents($assetFolder.$template.$extension);
                     if (TINA4_HAS_CACHE) {
                        xcache_set ($checkSum, $template);
                     }
                     break;
                 } 
             }
            }
            $originalTemplate = $template;
            //get PHP code snippets
            
            if (strpos($template, "<?php") !== false) { 
                 $code = explode ("<?php", $template);

                 $snippets = "";
                 $template = "";
                 foreach ($code as $cid => $codeValue) {
                    
                    $codeValue = explode ('?>', $codeValue);
                    
                    if (count($codeValue) > 1) {
                        $snippets .= $codeValue[0]."\n";
                    }
                        else {
                            $template .= $codeValue[0];
                        }
                    
                    if (!empty($codeValue[1])) {
                      $template .= $codeValue[1];
                    }
                 }
                
                 
                 eval ($snippets);
            }

            //then variables, global & local, local will be first
            foreach (get_defined_vars() as $varName => $varValue) {
                 if (!is_object($varValue) && $varName !== "template" && $varName !== "data" && $varName !== "originalTemplate" ) {
                     if (is_array($varValue)) {
                         foreach ($varValue as $vName => $vValue) {
                             $template = str_replace ("{".$vName."}", $vValue, $template);
                         }
                     }
                         else {
                             $template = str_replace ("{".$varName."}", $varValue, $template);
                         }
                 }    
            }

            //then defines
            foreach (get_defined_constants() as $varName => $varValue) {
                 if (!is_object($varValue)) {
                     if (is_array($varValue)) {
                         foreach ($varValue as $vName => $vValue) {
                             $template = str_replace ("{".$vName."}", $vValue, $template);
                         }
                     }
                         else {
                             $template = str_replace ("{".$varName."}", $varValue, $template);
                         }
                 }    
            }
            
            $elements = null;
            preg_match_all ('/{{(.*)}}/i', $template, $elements, PREG_OFFSET_CAPTURE);
            //then see about parsing methods & functions
                       
            
            $parsedSnippets = $this->parseSnippets ($elements[1], $template, $data);
             
             //Reset the elements & template to the parsed elements
             $elements = $parsedSnippets["elements"];
             $controls = $parsedSnippets["controls"];                          
             $template = $parsedSnippets["template"];
             
             if (!empty($controls)) {
                 $ifResult = "";
                 foreach ($controls as $cid => $control) {
                     //go through all the ifs
                     if (empty($control["if"])) continue;
                    
                     $found = false;
                     foreach ($control["if"] as $ifId => $ifStatement) {
                        
                        $myIf = '$expression = ('.$ifStatement["expression"].');';
                        if (!empty($data)) {
                            foreach ($data as $dName => $dValue ) {
                                $myIf = str_replace ("{".$dName."}", $dValue, $myIf);
                            }
                        }
                                                
                        @eval ($myIf);
                        if (!empty($expression)) {
                            if ($expression) {
                              $found = true;
                              $ifResult = $this->parseTemplate($ifStatement["code"], $data);
                            }
                        }
                     }
                     
                     if (!$found) {
                       
                       $ifResult = $this->parseTemplate($control["else"]["code"], $data);
                     }
                     //if we didn't get a true above then result as else
                 }
                 
                 $template = str_replace ($control["ifTag"], $ifResult, $template );
             }
             
             if (!empty($elements)) {
                 
                 $response = [];
                 foreach ($elements as $eid => $element) {
                     $response[$eid] = "";

                     $elementParts = explode (":", $element[0]);

                     switch ($elementParts[0]) {
                        case "call":
                             $callParts = explode("?", $elementParts[1]);
                           
                             if (!empty($callParts[1])) {
                                   
                                     $params = $this->getCallParams($callParts[1]);
                                 }
                                     else {
                                       $params = [];  
                                     }
                            
                             if (function_exists($callParts[0])) {
                                 $response[$eid] = call_user_func_array($callParts[0],$params);
                             }  
                                 else {
                                     $response[$eid] = "Function: {$callParts[0]} not found";
                                 }
                         break;
                         case "include":
                             //include a template file.
                             $response[$eid] = $this->parseTemplate($elementParts[1], $data);
                          
                         break;   
                         default:
                             if (class_exists($elementParts[0])) {
                                 $callParts = explode("?", $elementParts[1]);

                                 eval ('$classObject = new '.$elementParts[0].'();');

                                 if (!empty($callParts[1])) {
                                     $params = $this->getCallParams($callParts[1]);
                                 }
                                     else {
                                       $params = [];  
                                     }

                                 $result = @call_user_func_array(array($classObject, $callParts[0]), $params);
                               
                                 if (is_array($result)) {
                                     $result = (object) $result;
                                 }
                                                                  
                                 if (is_object ($result) && get_class($result) !== "htmlElement") {
                                    
                                     $html = "";
                                     foreach ($result as $rid => $resultData) {
                                        $html .= $this->parseTemplate($element["snippet"], $resultData);
                                     }        
                                                                        
                                     $response[$eid] = $html;
                                     
                                 }
                                    else {
                                        $response[$eid] = $result;
                                    }
                                 
                             }  
                                 else {
                                     $response[$eid] = "Class: {$elementParts[0]} not found";
                                 }

                         break;    
                     }

                                           
                 }  
                                  
                 foreach ($elements as $eid => $element) {
                    $element[0] = str_replace ("?", '\?', $element[0]); 
                    $template = preg_replace ('{{{'.$element[0].'}}}', $response[$eid], $template, 1);
                 }
                 
             }

            //see if we can parse the data variable
            if (!empty($data)) {
                foreach ($data as $name => $value) {
                    $template = str_replace ("{".$name."}", $value, $template);
                }
            } 
            //any variables that could not be found in the form {variable}
            preg_match_all ('/{([a-zA-Z0-9\_\-\>\[\]\"]+)}/i', $template, $elements);
            if (!empty($elements[1])) {
              foreach ($elements[1] as $eid => $element) {
                  $testVar = explode ("->", $element);
                  if (count($testVar) > 1) {
                      eval('if (empty($'.$testVar[0].')) { global $'.$testVar[0].'; }');
                  }
                  eval ('if (!empty($'.$element.')) { $var = $'.$element.'; }');
                  if (empty($var)) {
                    $template = str_replace ("{".$element."}", "<span style=\"color:red; font-weight:bold\">[{$element} is not defined, please check your template]</span>", $template);
                  }
                    else {
                        $template = str_replace ("{".$element."}", $var, $template);    
                    }
              }  
            }
       
        } catch (Exception $error) {
          $template = "Error parsing {$error}";  
        }
       //return the parsed information as a text string
       
       return $template;
    }
    
    /**
     * Return default bootstrap page
     * 
     */
    function getPageTemplate($title="Default") {
       $html = html (
                    head (
                            title ($title),
                            alink (["rel" => "stylesheet", "href"=>"https://maxcdn.bootstrapcdn.com/bootswatch/latest/cosmo/bootstrap.min.css"]),
                            alink (["rel" => "stylesheet", "href"=> "https://cdnjs.cloudflare.com/ajax/libs/bootstrap-table/1.8.1/bootstrap-table.min.css"]),
                            alink (["rel" => "stylesheet", "href"=> "http://jonmiles.github.io/bootstrap-treeview/css/bootstrap-treeview.css"]),
                            script(["src" => "http://code.jquery.com/jquery-2.1.4.min.js"]),
                            script(["src"=> "https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"]),
                            script(["src" => "https://cdnjs.cloudflare.com/ajax/libs/bootstrap-table/1.8.1/bootstrap-table.min.js"]),
                            script(["src" => "http://ajax.aspnetcdn.com/ajax/jquery.validate/1.13.1/jquery.validate.min.js"]),
                            script(["src" => "http://ajax.aspnetcdn.com/ajax/jquery.validate/1.13.1/additional-methods.min.js"]),
                            script(["src" => "http://cdnjs.buttflare.com/ajax/libs/interact.js/1.2.4/interact.min.js" ])
                            
                    ),
                    body (["style" => "padding: 0px 20px 0px"],
                            
                            div (["id" => "content"])
                            
                        
                    )
               
               );  
       return $html; 
    }
    
    function getSubMenus ($menuId=0, $systemMenu = 1) {
        $subMenus = $this->KIM->getRows("select * from menu where status = 'Active' and system_menu = {$systemMenu} and parent_id = {$menuId} order by order_index");
        $html = "";
        if (!empty($subMenus)) {
            $html = ul(["class" => "dropdown-menu"]);
            foreach ($subMenus as $mid => $menu) {
                $html->addContent( li ( a(["href" => $menu->PATH], $menu->NAME ) ) ); 
            }
        }
               
        return $html;
    }
    
    function getMenuList ($parentId=0, $systemMenu=1) {
        return $this->KIM->getRows("select * from menu where status = 'Active' and system_menu = {$systemMenu} and parent_id = {$parentId}");
    }
    
    function getMenu ($parentId = 0, $systemMenu = 1) {
        $menus = $this->KIM->getRows("select * from menu where status = 'Active' and system_menu = {$systemMenu} and parent_id = {$parentId} and menu_id <> {$parentId} order by order_index");
        
        $html = ul (["class" => "nav navbar-nav"]);
        
        foreach ($menus as $mid => $menu) {
            
            if (empty($menu->PATH)) $menu->PATH = "#"; 
            $html->addContent( $subMenu = li ( $subLink = a(["href" => $menu->PATH], $menu->NAME ) ) ); 
           
            //get sub menus for this menu if possible
            $subMenus = $this->getSubMenus($menu->MENU_ID, $systemMenu);
            
            //only add the elements if needed
            if (!empty($subMenus)) {
                $subLink->addAttribute("class", "dropdown-toggle");
                $subLink->addAttribute("data-toggle", "dropdown");
                $subLink->addAttribute("role", "button");
                $subLink->addAttribute("aria-haspopup", "true"); 
                $subLink->addAttribute("aria-expanded", "false"); 
                $subLink->addContent (span(["class" => "caret"]));

                $subMenu->addAttribute ("class", "dropdown");
                $subMenu->addContent ($subMenus);
            }
        }
        
        return  nav (["class" => "navbar navbar-default"], 
                    div (["class" => "container-fluid"], 
                        div (["class" => "collapse navbar-collapse"], 
                            $html 
                        )
                    )
                );
    }

    /**
     * 
     * @return string
     */
    function getInsertMenuItemForm() {
        
        $messages = [];
        $validation = [];
        
        $html = (new Cody())->bootStrapPanel("Add Menu Item", form (["id" => "formMenu", "onsubmit" => "return false"],
                    (new Cody())->bootStrapInput("txtNAME", "Menu Name", "The name for the menu"),
                    (new Cody())->bootStrapLookup("txtTARGET", "Menu Target", ["_self" => "Self", "_blank" => "Blank ( New Tab )"]),
                    (new Cody())->bootStrapInput("txtPATH", "Menu Path", "The path for the menu"),
                    (new Cody())->bootStrapLookup("txtPARENT_ID", "Menu Parent", $this->KIM->getKeyValue("select menu_id, name from menu where (system_menu = 0 or menu_id = 0)")),
                    (new Cody())->bootStrapCheckbox("cbROLE", $this->KIM->getKeyValue("select role_id, name from role")),
                    (new Cody())->bootStrapButton("btnAdd", "Add", "$('#formMenu').submit(); if ( $('#formMenu').validate().errorList.length == 0 ) { callAjax('/kim/menu/insert', 'left_nav', null, 'post');}")
                ));
        
        // add validation
        $validation[] = "txtNAME: { required : true }";
        $validation[] = "txtTARGET: { required : true }";
        $validation[] = "txtPATH: { required : true }";
        // add validation messages
        $messages[] = "txtNAME: { required: 'You need a name for the menu' } ";
        $messages[] = "txtTARGET: { required: 'You need a target for the menu' } ";
        $messages[] = "txtPATH: { required: 'You need a path for the menu' } ";
        
        $html .= (new Cody())->validateForm(join($validation, ","), join($messages, ","), "formMenu"); 
        
        return $html;
        
    }
    
    function insertMenuItem(){

        $this->KIM->insert ("menu", [
                "name" => Ruth::getREQUEST("txtNAME"), 
                "target" => Ruth::getREQUEST("txtTARGET"), 
                "path" => Ruth::getREQUEST("txtPATH"), 
                "parent_id" => empty(Ruth::getREQUEST("txtPARENT_ID")) ? 0 : Ruth::getREQUEST("txtPARENT_ID"),
                "system_menu" => 0,
            ]   
        );
          
        // show success message in right_nav and reload menu tree
        
        $this->getMenuTree();
        
        //byId("left_nav")->setValue($this->getMenuTree());
        
    }
    
    /**
     * 
     */
    function deleteMenuItem(){
        
        if($this->KIM->delete("menu", ["menu_id" => Ruth::getREQUEST("intMENU_ID")])){
            return (new Cody())->bootStrapAlert("success", "Deleted : ", "Menu Item Deleted Successfully");  
        }
        
        return (new Cody())->bootStrapAlert("danger", "Error", "Oops! Something went wrong");
        
    }
    
    function updateMenuItem(){
        
        if($this->KIM->update ("menu", [
                "name" => Ruth::getREQUEST("txtNAME"), 
                "target" => Ruth::getREQUEST("txtTARGET"), 
                "path" => Ruth::getREQUEST("txtPATH"), 
                "parent_id" => empty(Ruth::getREQUEST("txtPARENT_ID")) ? '0' : Ruth::getREQUEST("txtPARENT_ID"),
                "system_menu" => 0,
            ], ["system_menu" => 0, "menu_id" => Ruth::getREQUEST("intMENU_ID")]   
        )){
            
            return (new Cody())->bootStrapAlert("success", "Updated", "Menu Item Updated Successfully");
        }
        
        return (new Cody())->bootStrapAlert("danger", "Error", "Oops! Something went wrong");
    }
    
    // show edit form for menu item selected
    /**
     * 
     * @param type $menuId
     */
    function getMenuItemForm($menuId){
        
        $menu = $this->KIM->getRow("select * from menu where menu_id = {$menuId}");
        
        if(empty($menu)){ 
            return "Invalid Menu Item";
        }
        
        $html = (new Cody())->bootStrapPanel("Edit Menu Item : {$menu->NAME}", 
                    form (["id" => "formMenu", "onsubmit" => "return false"],
                        input(["name" => "intMENU_ID", "value" => $menuId, "type" => "hidden"]),
                        (new Cody())->bootStrapInput("txtNAME", "Menu Name", "The name for the menu", $menu->NAME),
                        (new Cody())->bootStrapLookup("txtTARGET", "Menu Target", ["_self" => "Self", "_blank" => "Blank ( New Tab )"], $menu->TARGET),
                        (new Cody())->bootStrapInput("txtPATH", "Menu Path", "The path for the menu", $menu->PATH),
                        (new Cody())->bootStrapLookup("txtPARENT_ID", "Menu Parent", $this->KIM->getKeyValue("select menu_id, name from menu where (system_menu = 0 or menu_id = 0) "), $menu->PARENT_ID),
                        (new Cody())->bootStrapCheckbox("cbROLE", $this->KIM->getKeyValue("select role_id, name from role")),
                        (new Cody())->bootStrapButton("btnDelete", "Delete", "$('#formMenu').submit(); if ( confirm('Delete this menu item?') ) { callAjax('/kim/menu/delete', 'right_nav', null, 'post');}", "btn btn-danger pull-right", "col-md-12", true),
                        (new Cody())->bootStrapButton("btnUpdate", "Save", "$('#formMenu').submit(); if ( $('#formMenu').validate().errorList.length == 0 ) { callAjax('/kim/menu/update', 'left_nav', null, 'post');}", "btn btn-primary pull-right", "col-md-12", true)
                    )
                );
        
        return $html;
        
    }
    
    function getMenuTree($filter = "where system_menu <> 1 and parent_id = 0", $menuId=0) {
        $menus = $this->KIM->getRows("select m.*, (select count(menu_id) from menu where parent_id = m.menu_id ) as count_children from menu m {$filter}");
        $style = style ('@import "http://maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css";

                              ul.tree {
  margin: 0;
  padding: 0;
}
ul.tree li {
  list-style: none;
  margin-left: -15px;
  position: relative;
}
ul.tree li input {
  cursor: pointer;
  height: 1em;
  left: 0;
  margin-left: 0;
  opacity: 0;
  position: absolute;
  top: 0;
  width: 2em;
  z-index: 2;
}
ul.tree li input:checked + ul {
  height: auto;
  margin: 0px 0px 0px -19px;
}
ul.tree li input:checked + ul:before {
  content: "\f068";
  font-family: Fontawesome;
  font-size: 15px;
  left: 0;
  margin-right: 5px;
  position: absolute;
  top: 0;
}
ul.tree li input:checked + ul > li {
  display: block;
  left: -3px;
  margin: 0 0 3px;
}
ul.tree li input:checked + ul > li last-child {
  margin: 0 0 .063em;
}
ul.tree li input + ul {
  padding: 0 0 0 39px;
}
ul.tree li input + ul:before {
  content: "\f067";
  font-family: Fontawesome;
  font-size: 15px;
  left: 0;
  margin-right: 5px;
  position: absolute;
  top: 0;
}
ul.tree li input + ul > li {
  display: none;
  margin-left: -10px;
  padding-left: 1px;
}
ul.tree li label {
  cursor: pointer;
  margin-bottom: 0;
  margin-left: 17px;
}
ul.tree > li {
  margin-left: -1px !important;
}
ul.tree > li a {
  color: #606061;
  display: block;
  text-decoration: none;
}
ul.tree > li a:before {
  content: "";
  display: block;
  float: left;
  font-family: Fontawesome;
  font-size: 15px;
  margin-right: 5px;
}
ul.tree > li > a:before {
  content: "\f0c9";
  display: block;
  float: left;
  font-family: Fontawesome;
  font-size: 15px;
  margin-right: 5px;
}
ul.tree > li > a > label {
  margin-left: 0;
}
ul.tree > li > ul > li > a > label:before,
ul.tree > li > ul > li > ul > li > a > label:before {
  content: "\f0c9";
  display: block;
  position: absolute;
  font-family: Fontawesome;
  font-size: 15px;
  margin-right: 5px;
  left: 0px;
}


                                ');
       
       if ($menuId == 0) {
         $html = ul (["class" => "tree", "id" => "menu_tree{$menuId}"]   );
         // add "Add New Menu Item" at the end of the list if we are the root
         $html->byId("menu_tree{$menuId}")->addContent (button(["class"=>"btn btn-primary btn-block","onclick" => "callAjax('/kim/menu/insert', 'right_nav');", "style"=>"margin-bottom: 11px;"],i(["class"=>"fa fa-plus"]), " Add New Menu Item"));
       }
            else {
                $html = ul (["id" => "menu_tree{$menuId}"]);
            }
        
       if(!empty($menus)){
            foreach ($menus as $mid => $menu) {
                
                if ($menu->COUNT_CHILDREN > 0) {
                  $html->byId("menu_tree{$menuId}")->addContent (li  (["id" => "menu{$menu->MENU_ID}"],  
                                                                    label (["for"=> "menu{$menu->MENU_ID}"], a(["onclick" => "callAjax('/kim/menu/get/{$menu->MENU_ID}', 'right_nav');"], $menu->NAME)), 
                                                                    input(["type" => "checkbox", "checked", "id"=> "menu{$menu->MENU_ID}"])
                                                                    
                                                                 ), a(["style" => "float: right", "onclick" => "callAjax('/kim/menu/get/{$menu->MENU_ID}', 'right_nav');"], "edit")          );  
                } else {
                  $html->byId("menu_tree{$menuId}")->addContent (li  (["id" => "menu{$menu->MENU_ID}"],  a(["onclick" => "callAjax('/kim/menu/get/{$menu->MENU_ID}', 'right_nav');"], label (["for"=> "menu{$menu->MENU_ID}"], $menu->NAME), input(["type" => "checkbox", "checked", "id"=> "menu{$menu->MENU_ID}"]) )) );
                }
                
                if ($menu->COUNT_CHILDREN > 0) {
                    $html->byId("menu{$menu->MENU_ID}")->addContent ( $this->getMenuTree("where system_menu <> 1 and parent_id = {$menu->MENU_ID}", $menu->MENU_ID) );
                }
                
            }
        }
        
        if ($menuId == 0) {
             $html .= $style;
        }
        
        return $html;
    }
        
    function getUserList($filter="") {
       if (!empty($filter)) {
          $filter = " and {$filter}"; 
       } 
       
      
       return $this->KIM->getRows("select * from user where user_id <> 0 {$filter}"); 
    }
    
    /**
     * 
     * @return type
     */
    function getMenuCreator() {
        
        $html = (new Cody())->ajaxHandler("/kim/test", "response_ajax", "callAjax", "", "get");
        

        $layout = div (["id" => "container"],
                    div(["class"=>"row"],
                        div (["id" => "left_nav", "class"=>"col-md-2"]),
                        div(["id" => "right_nav", "class"=>"col-md-10"])
                    )
        );
        
        
        $layout->byId("left_nav")->addContent($this->getMenuTree());
        
        $layout->byId("right_nav")->addContent((new Cody())->bootStrapPanel("Menu Dashboard", ""));
        
        $html .= $layout;
        
        return $html;
    }
    
   
    
    /**
     * 
     * @return type
     */
    function getUsers() {
        
        $buttons = "update,delete";  
                                       
        $toolBar["caption"] = "Users";  
        $customFields["STATUS"] = ["type" => "lookup", "list" => ["Active" => "Active", "Disabled" => "Disabled", "Suspended" => "Suspended"]];
        $customFields["ROLE_ID"] = ["type" => "lookup", "list" => $this->KIM->getKeyValue("select role_id, name from role")];
        $customFields["FIRST_NAME"] = ["type" => "text", "validation" => "required:true"];
        $customFields["LAST_NAME"] = ["type" => "text", "validation" => "required:true"];
        $customFields["EMAIL"] = ["type" => "text", "validation" => "required:true"];
        $customFields["PASSWD"] = ["type" => "password", "validation" => "required:false"];
        $customFields["PHOTO"] = ["type" => "image", "validation" => "required:false", "size" => "64x64"];
        $customFields["CREATED"] = ["type" => "readonly", "defaultValue" => date("Y-m-d h:i:s")];

        //More detailed information for the system to use the correct primary key and table, useful when there is complex table joins
        $tableInfo = ["table" => "user", "primarykey" => "user_id"];
        $content = (new Cody($this->KIM))->bootStrapTable(
                       $sql="select user_id, first_name, last_name, photo, email, passwd, status, role_id, created from user where user_id <> 0 order by last_name", 
                       $buttons, 
                       $hideColumns="user_id,passwd", 
                       $toolBar, 
                       $customFields, 
                       "Users", 
                       $tableInfo, 
                       $formHideColumns="user_id");  
        
        
        return $content;
    }
    
    function getUserTypes () {
        $buttons = "update,delete";
        $toolBar["caption"] = "User Types";  
        
        $customFields["NAME"] = ["validation" => "required:true"];
        $customFields["DESCRIPTION"] = ["type" => "textarea"];
        $customFields["STATUS"] = ["type" => "lookup", "list" => ["Active" => "Active", "Disabled" => "Disabled"]];
        $customFields["CREATED"] = ["type" => "readonly", "defaultValue" => date("Y-m-d h:i:s")];
        
        $tableInfo = ["table" => "role", "primarykey" => "role_id"];
        $content = (new Cody($this->KIM))->bootStrapTable(
                       $sql="select * from role order by name", 
                       $buttons, 
                       $hideColumns="role_id", 
                       $toolBar, 
                       $customFields, 
                       "UserTypes", 
                       $tableInfo, 
                       $formHideColumns="role_id"); 
        
        return $content;
    }
    
    function getRouteTarget($recordValue=null) {
   
        $html = textarea(["name" => "txtTARGET", "style" => "width: 100%; color: #fff; background: black"], $recordValue);
        return $html;  
    }
    
    function getRoutes () {
        $buttons = "update,delete";
        
        $toolBar["caption"] = "Routes";  
        
        
        $tableInfo = ["table" => "route", "primarykey" => "route_id"];
        
        $customFields["ROUTE_ID"] = ["type" => "readonly", "validation" => "required: true"];
        $customFields["ROUTE_TYPE"] = ["type" => "lookup", "list" => ["GET" => "GET", "POST" => "POST", "BOTH" => "BOTH"]];        
        $customFields["DESCRIPTION"] = ["type" => "textarea"];
        $customFields["PATH"] = ["type" => "text", "validation" => "required: true"];
        $customFields["TARGET"] = ["type" => "custom", "call" => "(new Kim())->getRouteTarget"];
        $customFields["STATUS"] = ["type" => "lookup", "list" => ["Active" => "Active", "Disabled" => "Disabled"]];
        $customFields["ROLE_ID"] = ["type" => "lookup", "list" => $this->KIM->getKeyValue("select role_id, name from role")];
        
        
        $content = (new Cody($this->KIM))->bootStrapTable(
                       $sql="select * from route order by description", 
                       $buttons, 
                       $hideColumns="route_id", 
                       $toolBar, 
                       $customFields, 
                       "Route", 
                       $tableInfo, 
                       $formHideColumns=""); 
        
        //update cache
       $this->cacheRoutes();
        
        
        return $content;   
        
        
    }
    
    function cacheRoutes () {
        $routes = $this->KIM->getRows("select * from route where status = 'Active' order by path");

        if (!empty($routes)) {
            foreach ($routes as $rid => $route) {
                $params = null;
                preg_match_all('/{[a-zA-Z0-9]+}/i', $route->PATH, $params, PREG_OFFSET_CAPTURE);


                $tempParams = [];
                foreach ($params[0] as $pid => $param) {
                    $tempParams[] = $param[0];
                }
                $routes[$rid]->PARAMS = join(",", $tempParams);
                $routes[$rid]->PARAMS = str_replace('{', '$', $routes[$rid]->PARAMS);
                $routes[$rid]->PARAMS = str_replace('}', '', $routes[$rid]->PARAMS);


            }
        }
        
        if (TINA4_HAS_CACHE) {
            xcache_set (md5("routes"), serialize($routes));
        }
            
        return $routes;
    }
    
    function loadRoutes () {
        if (TINA4_HAS_CACHE) {
            $routes = unserialize(xcache_get (md5("routes")));
        }

        if (empty($routes)) {
            $routes = $this->cacheRoutes();
        }


        if (!empty($routes)) {
            foreach ($routes as $rid => $route) {
                $code = "";
                //get params

                if ($route->ROUTE_TYPE === "BOTH") {
                    $code = 'Ruth::addRoute(RUTH_GET,
                        "' . $route->PATH . '",
                        function (' . $route->PARAMS . ') {
                            ' . $route->TARGET . ';
                        });
                        
                        Ruth::addRoute(RUTH_POST, 
                        "' . $route->PATH . '",
                        function (' . $route->PARAMS . ') {
                            ' . $route->TARGET . ';
                        });';

                } else {
                    $code = 'Ruth::addRoute("' . $route->ROUTE_TYPE . '",
                                            "' . $route->PATH . '",
                                            function (' . $route->PARAMS . ') {
                                                ' . $route->TARGET . ';
                                            });';

                }
                error_reporting(E_ALL);
                $success = eval ('try { ' . $code . ' }
                              catch (Exception $ex) {
                                die ("Failed: {$route->DESCRIPTION} has invalid syntax: ".$ex->getMessage());
                              }');
            }
        }
    }
    
    
    
    function getGlobalSettings () {
        $buttons = "update,delete";
        $toolBar["caption"] = "Global Settings";  
        
        $customFields["GLOBAL_NAME"] = ["validation" => "required:true"];
        $customFields["GLOBAL_VALUE"] = ["validation" => "required:true"];
        $customFields["DESCRIPTION"] = ["type" => "textarea"];
        $customFields["CREATED"] = ["type" => "readonly", "defaultValue" => date("m/d/Y h:i:s")];
        $customFields["UPDATED"] = ["type" => "readonly", "defaultValue" => date("m/d/Y h:i:s")];
        
        $tableInfo = ["table" => "global_setting", "primarykey" => "global_setting_id"];
        $content = (new Cody())->bootStrapTable(
                       $sql="select * from global_setting order by global_name", 
                       $buttons, 
                       $hideColumns="global_setting_id", 
                       $toolBar, 
                       $customFields, 
                       "GlobalSettings", 
                       $tableInfo, 
                       $formHideColumns="global_setting_id"); 
        
        return $content;    
        
    }
    
    /**
     * The function which flushes the XCache
     * @return String Message to indicate whether the cache has been cleared or not.
     */
    function getFlushXCache() {
        $html = "";
        if (function_exists("xcache_get")) {
            xcache_clear_cache(1);
            $html = (new Cody())->bootStrapAlert("success", "The XCache data has been cleared");
        } else {
            $html = (new Cody())->bootStrapAlert("warning", "Please enable XCache, it seems you do not have a working version");
        }
        
        return $html;
    }
    
    /**
     * Function for the login screen
     */
    function getLogin() {
        $html = "";
        $CODY = (new Cody());
        
        $content =  form (["method" => "POST"],
                    $CODY->bootStrapInput("txtEMAIL", "Email", "Email Address", "", "text"),
                    $CODY->bootStrapInput("txtPASSWORD", "Password", "Password", "", "password"),
                    $CODY->bootStrapButton($name="btnLogin", "Login")
                   );
        
        $html .= div (["style" => "width:400px; margin:0 auto;"], $CODY->bootStrapPanel("Login", $content) );
        return $html;
    }
    
    function authenticate() {
        $user = $this->KIM->getRow("select * from user where email = '".Ruth::getREQUEST("txtEMAIL")."'");
        if (password_verify(Ruth::getREQUEST("txtPASSWORD"), $user->PASSWD)) {
            Ruth::setSESSION("KIM", ["loggedin" => 1, "user" => $user]);
            Ruth::redirect("/kim/routes");
        }
            else {
            Ruth::setSESSION("KIM", ["loggedin" => 0]);    
            Ruth::redirect("/kim/login");
        }
        
    }
        
    function getUserRecord($userId=0) {
        return $this->KIM->getRows("select * from user where user_id = {$userId}");
    }
    
    function getProfileUpdate() {
        global $user;    
        $user = Ruth::getSESSION("KIM")["user"];
            
        
        $customFields["USER_ID"] = ["type" => "hidden", "required:true"];
        $customFields["STATUS"] = ["type" => "lookup", "list" => ["Active" => "Active", "Disabled" => "Disabled", "Suspended" => "Suspended"]];
        $customFields["ROLE_ID"] = ["type" => "lookup", "list" => $this->KIM->getKeyValue("select role_id, name from role")];
        $customFields["FIRST_NAME"] = ["type" => "text", "validation" => "required:true"];
        $customFields["LAST_NAME"] = ["type" => "text", "validation" => "required:true"];
        $customFields["EMAIL"] = ["type" => "text", "validation" => "required:true"];
        $customFields["PASSWORD"] = ["type" => "password", "validation" => "required:false"];
        $customFields["PHOTO"] = ["type" => "image", "validation" => "required:false", "size" => "64x64"];
        $customFields["CREATED"] = ["type" => "readonly", "defaultValue" => date("Y-m-d h:i:s")];
        
        $customButtons = button (["class" => "btn btn-primary", "onclick" => "$('#formUserUpdate').submit(); if ($('#formUserUpdate').validate().errorList.length == 0) { document.forms[0].submit() } "], "Save");
        
        $html = (new Cody($this->KIM))->bootStrapForm("select user_id, photo, first_name, last_name, email, passwd as password, status, created from user where user_id = {$user->USER_ID}", "", $customButtons, $customFields, $submitAction="/kim/profile", "formUserUpdate");      
        
        return $html;
    }
    
    function updatePOST () {
      switch (Ruth::getPATH()) {
                case "/kim/profile":
                    //fix up the password field
                    $_REQUEST["txtPASSWD"] = $_REQUEST["txtPASSWORD"];
                    unset($_REQUEST["txtPASSWORD"]);
                    $this->KIM->getUpdateSQL("txt", "user", "user_id", Ruth::getREQUEST("txtUSER_ID"), $requestvar="user_id", "passwd", "created", true);
                    if (!empty(ONUPDATE)) {
                        $params = ["action" => "update", "table" => "user", "user_id" => Ruth::getREQUEST("txtUSER_ID"), "session" => Ruth::getSESSION(), "request" => Ruth::getREQUEST()];
                        @call_user_func_array(ONUPDATE, $params);
                    }
                break;
                case "/kim/login":
                    (new Kim())->authenticate(); 
                break;    
                default:
                    die("No POST method defined for ".Ruth::getPATH());
                break;    
      }     
      Ruth::redirect(Ruth::getPATH());
    }
    
    /**
     * Cleans up the input String of a Condition
     * @param type $inputString
     * @return type
     */
    function cleanUpConditions($a){
        //TODO: clean up stuff in future
        return true;
    }
    
    function matchConditions($string){
        // set array
        $ifExpressions = array();//["match" => ["if" => array(), "else" => array(), "coords" => array("start" => null, "end" => null)]];

        // set pattern for regex
        $pattern = "/(?:{{(if)\\s*\\((.+?)\\)}}(.*?)){1}(?:{{(elseif)\\s*\\((.*?)\\)}}(.+?))?(?:{{(else)}}(.+?))?{{endif}}/si";

        preg_match_all($pattern, $string, $matches, PREG_OFFSET_CAPTURE);

        $matches = $this->linkArray($matches);

        foreach($matches as  $match_id => $match){
            $matches[$match_id] = array_values(array_filter($matches[$match_id], function($tempEntry){
                if(!is_array($tempEntry)){
                    return false;
                }
                array_map("trim", $tempEntry);
                return $this->cleanUpConditions(trim($tempEntry[0]));
            }));
        }

        foreach($matches as $match_id => $match){

            foreach($match as $key => $value){

                if($value[0] == 'if' || $value[0] == 'elseif'){

                    if($value[0] == 'if'){ $ifExpressions[$match_id]["coords"]["start"] = $value[1] - 2;}

                    //parse the code for more ifelse ?
                    
                    // set if statements
                    $ifExpressions[$match_id]["if"][] = array(
                        "statement" => $value[0],
                        "expression" => $match[$key + 1][0],
                        "code"  => trim($match[$key + 2][0])
                    );

                } else if($value[0] == 'else'){

                    // set else code
                    $ifExpressions[$match_id]["else"] = array(
                        "code"  => trim($match[$key + 1][0])
                    );

                } else {
                    // do nothing
                    continue;
                }

            }

            // set last coords
            $start = $ifExpressions[$match_id]["coords"]["start"];
            $ifExpressions[$match_id]["coords"]["end"] = strpos(substr($string, $start), "{{endif}}") + $start + 10;

        }

        
        
        return $ifExpressions;
    
    }
    
        
    
    /**
    * Determines whether to show login screen etc.
    */
    function display () {
        $html = $this->getPageTemplate("Tina4Stack - Kim");         

        //establish the session
        if (empty(Ruth::getSESSION("KIM"))) {
            Ruth::setSESSION("KIM", ["loggedin" => 0]);
        }
        
        $KIM = Ruth::getSESSION("KIM");
        if ($KIM["loggedin"] == 0 && Ruth::getPATH() !== "/kim/login") {
            Ruth::redirect("/kim/login");
        }
        
        if ($KIM["loggedin"] == 1) {
            $content = $this->getMenu();
            switch (Ruth::getPATH()) {
                case "/kim/users":
                    $content .= $this->getUsers();
                break;   
                case "/kim/user_types":
                    $content .= $this->getUserTypes();
                break;    
                case "/kim/global_settings":
                    $content .= $this->getGlobalSettings();
                break;    
                case "/kim/menus":
                    $content .=  $this->getMenuCreator();
                break;    
                case "/kim/routes":
                    $content .= $this->getRoutes();
                break;    
                case "/kim/flush_xcache":
                    $content .=  $this->getFlushXCache();
                break;    
                case "/kim/profile":
                    $content .= $this->getProfileUpdate();
                break;    
                default:
                   $content .= "Please implement the menu option ".Ruth::getPATH();  
                break;    
                
            }
        } else {
            $content = $this->getLogin();
        }
        
        
        $html->byId("content")->setContent ($content);
        
        echo $html;
    }   
}