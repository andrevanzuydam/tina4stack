<?php
/**
 * Use this as a starting point for creating a setting up routes
 */
Ruth::addRoute(RUTH_GET, "/", 
        function () {
            echo html ( head(meta(["charset" => "UTF-8"], title("Tina4 - Hello World!")), body( a(["href" => "http://tina4.com"], img(["src" => "images/helloworld.png"])))));
        
            
            $names [] = ["name" => "Andre"];
            $names [] = ["name" => "John"];
            $names [] = ["name" => "James"];
            $names [] = ["name" => "Mark"];
            $names [] = ["name" => "Louis"];
            
            
            $template = shape ( div ("{name}") , hr() );
            
            echo loop ($names, $template);
        }
);

//-----------------------------------------------------------------------------
// Example Ajax call for bootstrapTable pagination
//-----------------------------------------------------------------------------
Ruth::addRoute("POST", "/data/ajax/{rowLimit}/{page}", function ($rowLimit, $page) {
    //decodes passed through function arguments
    $object = json_decode($_POST['object'],true);
    
    $DEB = Ruth::getOBJECT("DEB");
    $cody = new Cody($DEB);
    
    $object[4] = $rowLimit;//replace rowlimit with value from drop down
    
    $object[5] = $page;//replace page with page value
    //use call_user_func_array to load function with the rowlimit and page values
    echo call_user_func_array(array($cody, "bootStrapTable"), $object);
});