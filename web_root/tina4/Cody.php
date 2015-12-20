<?php

require_once "Shape.php";
require_once "Olga.php";

/**
 * Class AjaxResponseAction
 *
 * The response action is used as part of the response telling the system what should happen
 *
 * @code
 *  $responseAction = new AjaxResponseAction();
 *  $responseAction->setRedirectPath('/login');
 *  $responseAction->setResponseScript(script("window.alert('You need to goto /login')"));
 *
 * @endcode
 *
 */
class AjaxResponseAction extends Olga {
    var $redirectPath = "";
    var $responseScript = "";

    /**
     * Set the redirection path
     * @param $path string Path to the new location the application must navigate
     */
    function setRedirectPath($path) {
        $this->redirectPath = $path;
        return $this;
    }

    /**
     * Get the redirect path
     * @return string
     */
    function getRedirectPath() {
        return $this->redirectPath;
    }

    /**
     * Set the response script
     * @param $script
     */
    function setResponseScript ($script) {
        if (stripos($script,"script") === false) {
            $this->responseScript = script($script)."";
        }  else {
            $this->responseScript = $script;
        }
        return $this;
    }

    /**
     * Get the response script
     * @return string
     */
    function getResponseScript() {
        return $this->responseScript;
    }

}
/**
 * Class AjaxRouterResponse
 *
 * AjaxRouterResponse should be used to return responses to the Cody ajaxRouter command
 *
 * @code
 *  $responseAction = new AjaxResponseAction();
 *  $responseAction->setRedirectPath('/login');
 *  $responseAction->setResponseScript(script("window.alert('You need to goto /login')"));
 *
 *  $ajaxRouterResponse = new AjaxRouterResponse();
 *  $ajaxRouterResponse->setResponseAction ($responseAction);
 *  echo $ajaxRouterResponse->toJSON();
 * @endcode
 *
 */
class AjaxRouterResponse extends Olga {
    var $responseCode = "200";
    var $responseMessage = "";
    var $responseAction = null;

    /**
     * Set the response code
     * @param $code
     */
    function setResponseCode($code) {
        $this->responseCode = $code;
    }

    /**
     * Set the response message, if script tags are included the contents in them will be run
     * @param $message
     */
    function setResponseMessage($message) {
        $this->responseMessage = $message;
    }


    /**
     * Set the response action which is an object of AjaxResponseAction
     * @param $responseAction
     */
    function setResponseAction($responseAction) {
        if (is_object($responseAction) && get_class($responseAction) === "AjaxResponseAction") {
            $this->responseAction = $responseAction;
        } else {
            die("Response Action MUST be a AjaxResponseAction");
        }
    }

    /**
     * Get the response action
     * @return AjaxResponseAction
     */
    function getResponseAction () {
        return $this->responseAction();
    }

}
/**
 * Description of Cody
 *
 * Cody is a CRUDL generation tool to make coding in Tina4 stack easy and fun
 *
 */
class Cody {

    /**
     * The database connection
     * @var Debby A valid Debby database object
     */
    private $DEB;

    function bootStrapTextArea ($name, $caption="Test Input", $placeholder="Type in here", $defaultValue="", $colWidth="col-md-12", $noFormGroup=false) {
        $textArea =   shape (
            label (["for" => $name], $caption ),
            textarea (["class" => "form-control", "id" => $name, "name" => $name,  "placeholder" => $placeholder], $defaultValue)
        );

        if ($noFormGroup) {
            return $textArea;
        } else  {
            return div (["class" =>  "form-group {$colWidth}"],
                $textArea
            );
        }
    }

    function bootStrapButton ($name="button", $caption="Button", $onclick="", $style="btn btn-primary pull-right", $colWidth="col-md-12", $noFormGroup=false) {

        if (empty($onclick)) {

            $onclick = "document.forms[0].submit();";
        }

        if ($noFormGroup) {
            return button (["id" => str_replace(" ", "_", $name), "onclick" => $onclick, "class" => $style], $caption);
        } else {
            return div (["class" => "form-group {$colWidth}"], button (["id" => "button", "onclick" => $onclick, "class" => $style], $caption) );
        }
    }

    function bootStrapCheckbox($name, $elements, $colWidth="col-md-12"){

        $html = "";

        foreach($elements as $eid => $element){
            $html .= span( input( ["class" => "", "name" => "{$name}[]]", "type" => "checkbox", "value" => $eid] ), $element );
        }

        return div(["class" =>  "form-group {$colWidth}"], $html);

    }

    function bootStrapInput($name = "text", $caption = "Label", $placeHolder = "", $defaultValue = "", $type = "text", $required = "required", $colWidth="col-md-12" , $options = "", $step = 1) {
        $display = "";
        $onchange = "";
        $script = "";
        $attributes = ["class" => "form-control", "id" => $name, "name" => $name, "placeholder" => $placeHolder, "type" => $type, "value" => $defaultValue, $required, "onchange" => $onchange];


        $hidden = "";
        $rangeHeadings = "";
        if (!empty($options) && $type === "range") {

            foreach ($options as $minid => $val) {
                $min = $minid;
                $minval = $val;
                break;
            }
            $values = "";
            foreach ($options as $maxid => $val) {
                $max = $maxid;
                $maxval = $val;
                $values .= 'key' . $maxid . ':"' . $val . '",';
            }

            $defaultVal = !empty($options[$defaultValue]) ? $options[$defaultValue] : "{$minval}";
            $display = span(["id" => "display" . $name, "class" => "range-display"], $defaultVal);
            $onchange = "set{$name}Display(this.value, true)";
            $onchangemove = "set{$name}Display(this.value, false)";
            $script = script("function set{$name}Display(iValue, setvalue) {
                                var index{$name} = {" . $values . "};
                                if (setvalue) { $('#{$name}').set('value', iValue); }   
                                $('#display{$name}').set('innerHTML', eval('index{$name}.key'+iValue) );
                             }");



            if (!isset($defaultValue) && empty($defaultValue)) {
                $defaultValue = "";
            }
            $attributes = ["class" => "range-slider", "id" => "fake" . $name, "name" => "fake" . $name, "placeholder" => $placeHolder, "type" => $type, "value" => $defaultValue, "required", "onchange" => $onchange, "onmousemove" => $onchangemove, "min" => $min, "max" => $max, "step" => $step];

            $rangeHeadings = div(["class" => "range-heading"], span(["class" => "range-min"], $minval), span(["class" => "range-max"], $maxval));
            $hidden = input(["type" => "hidden", "id" => "{$name}", "name" => "{$name}", "value" => "{$defaultValue}"]);
        }

        $html = div(["class" =>  "form-group {$colWidth}"], label(["for" => $name], $caption), $display, div(["class" => "range-slider-content"], $rangeHeadings, input($attributes)), $hidden, span(["class" => "clearfix"]), $script);



        return $html;
    }

    /**
     * The default page template for maggy
     * @param type $title String A title to name the page by
     * @return type Shape A page template with default bootstrap
     */

//<link href="metro.css" rel="stylesheet">
//<script src="jquery.js"></script>
//<script src="metro.js"></script>

    function getPageTemplate($title="Default") {
        $html = html (
            head (
                title ($title),
                alink (["rel" => "stylesheet", "href"=>"/assets/metro-ui/css/metro.min.css"]),
                alink (["rel" => "stylesheet", "href"=>"/assets/metro-ui/css/metro-schemes.min.css"]),
                script (["src"=> "/assets/js/jquery-2.1.4.min.js"]),
                script (["src"=> "/assets/metro-ui/js/metro.js"])
            ),
            body (["id" => "content"])

        );
        return $html;
    }


    function getTemplate ($name="", $type="form") {
        $assetFolder = Ruth::getDOCUMENT_ROOT()."/assets/";
        $content = "";
        switch ($type) {
            case "form":
                $assetFile = $assetFolder."forms/{$name}.html";
                if (file_exists($assetFile)) {
                    $content = file_get_contents($assetFile);
                }
                break;
            default:
                die("This template type {$type} does not exist for {$name}");
                break;
        }

        return $content;
    }

    function createTableForm ($action, $object, $record, $db="") {
        if (!empty($db)) {
            $this->DEB = Ruth::getOBJECT($db);
        }

        $sql = $object[0];

        $hideColumns = $object[7];

        $customFields = "";

        if (!empty($object[4])) {
            $customFields = $object[4];
        }

        if (!empty($object[3])) {
            $toolBar = $object[3];
        }

        if (!empty($object[5])) {
            $name = $object[5];
        }
        else {
            $name = "grid";
        }


        if (!empty($object[6])) {

            $tableInfo = $object[6];
            $keyName = strtoupper($tableInfo->primarykey);
            $keyName = explode (",", $keyName);



            if (count($keyName) > 1) {
                $filterKey = "";
                $recordsVal = [];
                foreach ($keyName as $id => $key) {
                    if ($id != 0) $filterKey .= " and ";
                    if (!empty($record->$key)) {
                        $filterKey .= "{$key} = '".$record->$key."'";
                        $recordsVal[] = $record->$key;
                    }
                    else {
                        $filterKey .= "{$key} = '0'";
                        $recordsVal[] = "";
                    }

                }

                if ($action === "insert") {
                    $record = (object) $recordsVal;


                }
            }  else {
                $keyName = strtoupper($tableInfo->primarykey);
                if (empty($record)) {
                    $record = (object)[$keyName => "0"];
                }


                $filterKey = "{$tableInfo->primarykey} = '".$record->$keyName."'";


                if ($action === "insert") {
                    $record = (object) [$keyName => "0"];
                }
            }




            $selectFields = "*";
            if (!empty($tableInfo->fields)) {
                $selectFields = $tableInfo->fields;
            }

            $sql = "select {$selectFields} from {$tableInfo->table} where {$filterKey}";
        }
        else {
            $keyName = key($record);
            $sql = "select * from ({$sql}) t where {$keyName} = '".$record->$keyName."'";
        }

        $closeAction = "$('#{$name}Target').html('');";

        $ckeditorCheck = " if (CKEDITOR !== undefined) {  } ";
        switch ($action) {
            case "insert":
                $customButtons = $this->bootStrapButton("btnInsert", "Save", " {$ckeditorCheck} $('#form{$name}').submit(); if ( $('#form{$name}').validate().errorList.length == 0 ) {   this.disabled = true; this.innerHTML = 'Saving...';   call{$name}Ajax('/cody/form/insert/post','{$name}Target', {object : a{$name}object, record: null, db: '{$db}' }) } ", "btn btn-success", "", true);
                $customButtons .= $this->bootStrapButton("btnInsertCancel", "Cancel", $closeAction, "btn btn-warning", "", true);
                break;
            case "update":
                $customButtons = $this->bootStrapButton("btnUpdate", "Save", "  {$ckeditorCheck} $('#form{$name}').submit(); if ( $('#form{$name}').validate().errorList.length == 0 ) {  this.disabled = true; this.innerHTML = 'Saving...';   call{$name}Ajax('/cody/form/update/post','{$name}Target', {object : a{$name}object, record: '".  urlencode(json_encode($record) )."', db: '{$db}' }) } ", "btn btn-success", "", true);
                $customButtons .= $this->bootStrapButton("btnUpdateCancel", "Cancel", $closeAction, "btn btn-warning", "", true);
                break;
            default:
                $customButtons = $this->bootStrapButton("btnView", "Close", $closeAction, "btn btn-success", "", true);
                break;
        }


        if ($action == "insert") $action = "Add";
        if ($action == "update") $action = "Edit";

        //check if a template exists....
        if (!empty($tableInfo->form)) {
            $template = $this->getTemplate($tableInfo->form, "form");
        }
        else {
            $template = $this->getTemplate($name, "form");
        }

        if (!empty($template)) {
            $record = $this->DEB->getRow($sql);

            //Apply the constant values to the record
            if (!empty($customFields)) {
                foreach ($customFields as $field => $fieldValues) {
                    if (empty($record)) {
                        $record = (object)[];
                    }

                    if (!empty($fieldValues->constantValue)) {
                        $record->$field = $fieldValues->constantValue;
                    }
                }
            }


            //parse the template
            $template = (new Kim())->parseTemplate ($template, $record, false);


            //get the validation scripts

            //create the validation and messages section

            $fieldInfo = $this->getFieldInfo($sql);



            $hideColumns = explode(",", strtoupper($hideColumns));
            $validation = [];
            $messages = [];

            foreach ($fieldInfo as $fid => $field) {

                if (!in_array(strtoupper($field["name"]), $hideColumns)) {
                    $input = "";

                    if (is_object($customFields)) {
                        $customFields = (array) $customFields;
                    }



                    if (isset($customFields[strtoupper($field["name"])])) {
                        $customField = $customFields[strtoupper($field["name"])];

                        if (!empty($customField->list)) {
                            if (is_object($customField->list)) {
                                $customField->list = get_object_vars($customField->list);
                            }

                        }

                        if (!isset($customField->event))
                            $customField->event = "";
                        if (!isset($customField->style))
                            $customField->style = "";

                        if (!empty($customField->validation)) {
                            $message = explode(",", $customField->validation);
                            $mfound = false;
                            foreach ($message as $mid => $mvalue) {
                                $mvalue = explode(":", $mvalue);

                                if ($mvalue[0] == "message") {
                                    $messages[] = "txt" . strtoupper($field["name"]) . ": { required: '" . $mvalue[1] . "', remote: '" . $mvalue[1] . "'}";
                                    $mfound = true;
                                    unset($message[$mid]);
                                }
                            }

                            if ($mfound)
                                $customField->validation = join(",", $message);

                            $validation[] = "txt" . strtoupper($field["name"]) . ": {" . $customField->validation . "}";
                        } else {
                            $validation[] = "txt" . strtoupper($field["name"]) . ":{required: true}";
                        }
                    }
                }

            }



            $html = form(["method" => "post", "onsubmit" => "return false", "enctype" => "multipart/form-data", "id" => "form{$name}"], $template, $this->validateForm(join($validation, ","), join($messages, ","), "form{$name}"));

            return $this->bootStrapModal(ucwords($action)." ". $toolBar->caption,  $html, $customButtons, $closeAction);
        }
        else {
            return $this->bootStrapModal(ucwords($action)." ". $toolBar->caption, div (["class" => "row"], $this->bootStrapForm($sql, $hideColumns, "", $customFields, $submitAction="", "form{$name}" )), $customButtons, $closeAction);
        }
    }

    /**
     * The constructor for Cody requires a Debby database connection
     * @param Debby $DEB
     */
    function __construct($DEB = "") {
        //check to see if we can get a database handle
        if (empty($DEB) && !empty(Ruth::getOBJECT("DEB"))) {
            $DEB = Ruth::getOBJECT("DEB");
        }

        $this->DEB = $DEB;

        Ruth::addRoute(RUTH_GET, "/cody/swagger",
            function () {
                //generate a database schema for the default /rest interface
                Ruth::$wasAJAXCall = true;
                if (Ruth::getOBJECT("DEB")) {
                    unlink(Ruth::getDOCUMENT_ROOT().'/routes/restAPI.php');
                    $code = "/**\n";
                    $code .= '*    @SWG\Info(title="Generic REST API", version="0.1")'."\n";
                    $DEB = Ruth::getOBJECT("DEB");
                    $database = $DEB->getDatabase();
                    foreach ($database as $tableName => $tableColumns) {
                        $code .= '
                        @SWG\Definition(definition="'.$tableName.'", required={"'.$tableColumns[0]["field"].'"},
                        ';
                        foreach($tableColumns as $cid => $column) {
                            $type = "string";
                            $code .= '@SWG\Property(property="'.$column["field"].'",type="'.$type.'")';
                            if ($cid != count($tableColumns)-1) {
                                $code .= ','."\n";
                            }
                        }
                        $code .= ')
                        ';



                        //LIST
                        $code .= "*\n".'*    @SWG\Get('."\n".'*     path="/rest/'.$tableName.'",
                        @SWG\Response(response="200", description = "A list of records from the '.$tableName.' table" ),
                        @SWG\Response(response="400", description = "No content for '.$tableName.' table" ),
                        @SWG\Response(response="403", description = "Forbidden to access the REST service" ),
                        produces={"application/json"}
                        )'."\n";

                        //CREATE
                        $code .= "*\n".'*    @SWG\Post('."\n".'*     path="/rest/'.$tableName.'",
                        @SWG\Response(response="200", description = "A new record added to '.$tableName.' table" ),
                        @SWG\Response(response="400", description = "Failed to add a record for '.$tableName.' table" ),
                        @SWG\Response(response="403", description = "Forbidden to access the REST service" ),
                        @SWG\Parameter(
                        name="body",
                        description="JSON Record to be updated",
                        required=true,
                        schema="#/definitions/'.$tableName.'",
                        in="body"),
                        produces={"application/json"},
                        consumes={"application/json"}
                        )'."\n";

                        //READ
                        $code .= "*\n".'*    @SWG\Get('."\n".'*     path="/rest/'.$tableName.'/{id}",
                        @SWG\Response(response="200", description = "Get a record from '.$tableName.' table" ),
                        @SWG\Response(response="400", description = "Failed to get a record from '.$tableName.' table" ),
                        @SWG\Response(response="403", description = "Forbidden to access the REST service" ),
                        @SWG\Parameter(
                        name="id",
                        description="ID of the record to be fetched",
                        type="string",
                        in="path"),
                        produces={"application/json"},
                        consumes={"application/json"}
                        )'."\n";

                        //UPDATE
                        $code .= "*\n".'*    @SWG\Put('."\n".'*     path="/rest/'.$tableName.'/{id}",
                        @SWG\Response(response="200", description = "Update a record from '.$tableName.' table" ),
                        @SWG\Response(response="400", description = "Failed to update a record from '.$tableName.' table" ),
                        @SWG\Response(response="403", description = "Forbidden to access the REST service" ),
                        @SWG\Parameter(
                        name="id",
                        description="ID of the record to be updated",
                        type="string",
                        in="path"),
                        @SWG\Parameter(
                        name="body",
                        description="JSON Record to be updated",
                        required=true,
                        schema="#/definitions/'.$tableName.'",
                        in="body"),
                        produces={"application/json"},
                        consumes={"application/json"}
                        )'."\n";

                        //PATCH
                        $code .= "*\n".'*    @SWG\Patch('."\n".'*     path="/rest/'.$tableName.'/{id}",
                        @SWG\Response(response="200", description = "Patch a record from '.$tableName.' table" ),
                        @SWG\Response(response="400", description = "Failed to patch a record from '.$tableName.' table" ),
                        @SWG\Response(response="403", description = "Forbidden to access the REST service" ),
                        @SWG\Parameter(
                        name="id",
                        description="ID of the record to be patched",
                        type="string",
                        in="path"),
                        @SWG\Parameter(
                        name="body",
                        description="JSON Record to be updated",
                        required=true,
                        schema="#/definitions/'.$tableName.'",
                        in="body"),
                        produces={"application/json"},
                        consumes={"application/json"}
                        )'."\n";

                        //DELETE
                        $code .= "*\n".'*    @SWG\Delete('."\n".'*     path="/rest/'.$tableName.'/{id}",
                        @SWG\Response(response="200", description = "Delete a record from '.$tableName.' table" ),
                        @SWG\Response(response="400", description = "Failed to delete a record from '.$tableName.' table" ),
                        @SWG\Response(response="403", description = "Forbidden to access the REST service" ),
                        @SWG\Parameter(
                        name="id",
                        description="ID of the record to be deleted",
                        type="string",
                        in="path"),
                        produces={"application/json"},
                        consumes={"application/json"}
                        )'."\n";


                    }
                    $code .= "*/";
                    file_put_contents(Ruth::getDOCUMENT_ROOT().'/routes/restAPI.php', '<?php'."\n".$code);
                }


                require(Ruth::getDOCUMENT_ROOT()."/swagger-php/vendor/autoload.php");
                $swagger = \Swagger\scan(Ruth::getDOCUMENT_ROOT().'/routes');
                header('Content-Type: application/json');
                echo $swagger;
            }

        );

        Ruth::addRoute(RUTH_GET, "/cody/create/{tableName}" ,
            function ($tableName) {
                $html = $this->getPageTemplate("Generate Code for {$tableName}");
                $database = Ruth::getOBJECT("DEB")->getDatabase();
                $table = $database[$tableName];
                $code = '';
                $code .= '!===!===!BOOTSTRAP GRID!===!===!';
                $code .= '//Toolbar for the grid'."\n";
                $code .= '$toolBar = ["caption" => "'.ucwords(strtolower(str_replace("_", " ", $tableName))).'"];'."\n";
                $code .= ''."\n";
                $code .= '//Code for '.$tableName."\n";
                $code .= '$buttons = "insert,view,update,delete";'."\n";
                $code .= '// $buttons = ["buttons" => "update,mybutton1,mybutton2", "custom" => ["mybutton1" => "<button>{field_name}</button>", "mybutton2" => "<button>{field_name}</button>" ] ];'."\n"; //custom buttons
                $code .= ''."\n";



                foreach ($table as $field) {

                    $fieldType = "";
                    if (strtoupper($field["type"]) === "DATE" || strtoupper($field["type"]) === "DATETIME" || strtoupper($field["type"]) === "TIMESTAMP"  ) {
                        $fieldType = "date:true";
                    }
                    else
                        if (strtoupper($field["type"]) === "INTEGER") {
                            $fieldType = "number:true";
                        }

                    if (!empty($fieldType)) {
                        $fieldType = ",".$fieldType;
                    }

                    if (!empty($field["pk"])) {
                        $primaryKeys[] = $field["field"];
                    }

                    $fieldNames[] = $field["field"];
                    $code .= '$customFields["'.strtoupper($field["field"]).'"] = ["type" => "text", "validation" => "required:true,maxlength:'.$field["length"].$fieldType.'"];'."\n";
                }



                $code .= ''."\n";
                $code .= '//Table information for '.$tableName."\n";
                $code .= '$tableInfo = ["table" => "'.$tableName.'", "form"=> "'.$tableName.'", "primarykey" => "'.join(",", $primaryKeys).'", "fields" => "'.join(",", $fieldNames).'"];'."\n";

                $code .= ''."\n";
                $code .= '//Events which happen on the table'."\n";
                $code .= '$events = [ "onupdate" => "window.alert(\'onupdate '.$tableName.'\');",
            "oninsert" => "window.alert(\'oninsert '.$tableName.' \');", 
            "ondelete" => "window.alert(\'ondelete '.$tableName.' \');", 
            "beforeinsert" => "window.alert(\'beforeinsert '.$tableName.'\');", 
            "beforeupdate" => "window.alert(\'beforeupdate '.$tableName.'\');", 
            "beforedelete" => "window.alert(\'beforedelete '.$tableName.'\');"
            ];';
                $code .= ''."\n";
                $code .= '//Implementation for '.$tableName."\n";

                $code .= '$content = (new Cody($this->DEB))->bootStrapTable(
            $sql="select '.join(",", $fieldNames).' from '.$tableName.'", 
            $buttons, 
            $hideColumns="'.join(",", $primaryKeys).'", 
            $toolBar, 
            $customFields, 
            "grid'.str_replace (" ", "", ucwords(strtolower(str_replace ("_", " ", $tableName)))).'", 
            $tableInfo, 
            $formHideColumns="'.join(",", $primaryKeys).'",
            $events);'."\n";

                $code .= ''."\n";
                $code .= '//HTML code for a form'."\n";
                $code .= ''."\n";

                $code .= '!===!===!HTML FORM!===!===!';
                $code .= '<form role="form" method="post" enctype="multipart/form-data">'."\n";
                foreach ($fieldNames as $fid => $fieldName) {
                    $code .= '  <div id="form-group-'.$fieldName.'" class="form-group">'."\n";
                    $code .= '    <label for="txt'.strtoupper($fieldName).'" >'.ucwords(str_replace ("_", " ", $fieldName)).'</label>'."\n";
                    $code .= '    <input type="text" class="form-control" placeholder="" id="txt'.strtoupper($fieldName).'" name ="txt'.strtoupper($fieldName).'" value="{'.strtoupper($fieldName).'}" />'."\n";
                    $code .= '  </div>'."\n";
                }

                $code .= '</form>'."\n";

                $code .= '!===!===!DATA CLASS!===!===!';
                $code .= '//Class to get data from '.$tableName."\n";
                $code .= 'class '.ucwords(strtolower($tableName)).'Data{ '."  \n";
                $code .= '  var $DEB;'."\n";
                $code .= '  function __construct() {'."\n";
                $code .= '      $this->DEB = Ruth::getOBJECT("DEB");'."\n";
                $code .= '  }'."\n";
                $code .= ''."\n";
                foreach ($fieldNames as $fid => $fieldName) {
                    $code .= '  function getBy'.strtoupper($fieldName).'($filter="All") {'."\n";
                    $code .= '      $where = "";'."\n";
                    $code .= '      if ($filter !== "All") { '."\n";
                    $code .= '          $where = "where '.$fieldName.' = \'{$filter}\'"; '."\n";
                    $code .= '      }'."\n";
                    $code .= ''."\n";
                    $code .= '      $sql'.strtoupper($fieldName).' = "select * from '.$tableName.' {$where} ";'."\n";
                    $code .= '      $result = $this->DEB->getRows($sql'.strtoupper($fieldName).');'."\n";
                    $code .= ''."\n";
                    $code .= '      if (empty($result)) {'."\n";
                    $code .= '          $result[] = [];'."\n";
                    $code .= '      }'."\n";
                    $code .= ''."\n";
                    $code .= '      return $result;'."\n";
                    $code .= '  }'."\n";
                    $code .= ''."\n";
                }

                $code .= '}'."\n";
                $code .= ''."\n";
                //create the code so it can be displayed nicely on the screen
                $snippets = explode ("!===!===!", $code);
                $code = "";

                foreach ($snippets as $sid => $snippet) {
                    if (trim($snippet) !== "") {
                        $code .= '<pre  class="prettyprint">'."\n".htmlentities($snippet).'</pre>'."\n";
                    }
                }

                $code .= "<script>
                                !function ($) {
                                  $(function(){
                                    window.prettyPrint && prettyPrint()   
                                  })
                                }(window.jQuery)
                              </script>";

                $html->byId("content")->addContent($code);

                echo $html;
            }
        );

        Ruth::addRoute(RUTH_GET, "/cody/create" ,
            function () {
                //get the tables from the default database
                $html = $this->getPageTemplate("Code Creator");


                $database = Ruth::getOBJECT("DEB")->getDatabase();


                foreach ($database as $table => $fields) {
                    $li[] = li (["role"=>"presentation"], a(["href" => "/cody/create/{$table}"], $table ) );
                }
                $html->byId("content")->addContent( h1("Tables") );
                $html->byId("content")->addContent( h3("Click on a table to get code") );
                $html->byId("content")->addContent( ul (["id" => "tables", "class" => "nav nav-pills"],  $li  ) );

                echo $html;
            }

        );




        Ruth::addRoute(RUTH_POST, "/cody/form/{action}" ,
            function ($action) {
                $object = json_decode(rawurldecode(Ruth::getREQUEST("object")));
                $record = json_decode(rawurldecode(Ruth::getREQUEST("record")));

                if (!empty ($object[8])) {
                    $events = $object[8];
                }

                if ($action !== "delete") {
                    echo $this->createTableForm($action, $object, $record, Ruth::getREQUEST("db"));
                }
                else
                    if ($action === "delete") {
                        $sql = $object[0];

                        if (!empty($object[5])) {
                            $name = $object[5];
                        } else {
                            $name = "grid";
                        }

                        if (!empty($object[6])) {
                            $tableInfo = $object[6];
                            $keyName = explode (",", $tableInfo->primarykey);
                            if (count($keyName) > 1) {
                                $filterKey = "";
                                foreach ($keyName as $id => $key) {
                                    $key = strtoupper($key);
                                    if ($id != 0) $filterKey .= " and ";
                                    if (!empty($record)) {
                                        $filterKey .= "{$key} = '".$record->$key."'";
                                    } else {
                                        $filterKey .= "{$key} = ''";
                                    }
                                }
                            }  else {
                                $keyName = strtoupper($tableInfo->primarykey);
                                if (!empty($record)) {
                                    $filterKey = "{$tableInfo->primarykey} = '".$record->$keyName."'";
                                }  else {
                                    $filterKey = "{$tableInfo->primarykey} = ''";
                                }
                            }
                            $tableName = $tableInfo->table;
                        }
                        else {
                            $keyName = key($record);
                            //this is just a quess
                            $sql = explode("from", $sql);
                            $sql = explode($sql[1], " ");

                            $tableName = $sql[0];
                        }
                        $DEB = Ruth::getOBJECT(Ruth::getREQUEST("db"));

                        if (is_array($keyName)) {

                            $DEB->exec ("delete from {$tableName} where {$filterKey}");
                            $DEB->commit();
                        }
                        else {
                            $DEB->delete($tableName, [$keyName => $record->$keyName]);
                        }

                        if (defined ("ONDELETE") && !empty(ONDELETE)) {
                            if (is_array($keyName)) { $keyName = join("-", $keyName);
                                $record->$keyName = $filterKey;
                            }
                            $params = ["action" => $action, "table" => $tableName, $keyName => $record->$keyName, "session" => Ruth::getSESSION(), "request" => Ruth::getREQUEST()];
                            @call_user_func_array(ONDELETE, $params);
                        }

                        $tableEvent = "ONDELETE".$name;

                        $constantFound = false;

                        eval ('$constantFound = (defined ("'.$tableEvent.'") && !empty('.$tableEvent.'));');

                        if ($constantFound) {
                            if (is_array($keyName)) $keyName = join("-", $keyName);
                            $params = ["action" => $action, "table" => $tableName, $keyName => $record->$keyName, "session" => Ruth::getSESSION(), "request" => Ruth::getREQUEST()];
                            eval ('@call_user_func_array('.$tableEvent.', $params);');
                        }

                        if (!empty($events->ondelete)) {
                            echo script($events->ondelete);
                        }

                        echo $this->bootStrapAlert("success", $caption="Success", "Record deleted");
                        echo script ('$table'.$name.'.bootstrapTable("refresh");' );

                    }
                    else {
                        echo $this->bootStrapAlert("danger", $caption="Failed {$action}", "This action is unknown!");
                    }


            }

        );

        Ruth::addRoute(RUTH_POST, "/cody/form/{action}/post" ,
            function ($action) {
                $DEB = Ruth::getOBJECT(Ruth::getREQUEST("db"));
                $object = json_decode(rawurldecode(Ruth::getREQUEST("object")));
                $record = json_decode(rawurldecode(Ruth::getREQUEST("record")));
                $sql = $object[0];


                $fieldInfo = $DEB->getFieldInfo($sql);
                $dateFields = [];

                //TODO: determine the password and date fields
                foreach ($fieldInfo as $fid => $field) {

                    if ($field["type"] === "DATETIME" || $field["type"] === "DATE" || $field["type"] === "TIMESTAMP") {
                        $dateFields[] = $field["name"];
                    }
                }

                $passwordFields = "passwd,password";
                $dateFields = join (",", $dateFields);


                if (!empty($object[5])) {
                    $name = $object[5];
                } else {
                    $name = "grid";
                }

                if (!empty ($object[8])) {
                    $events = $object[8];
                }

                if (!empty($object[6])) {
                    $tableInfo = $object[6];
                    $keyName = explode (",", $tableInfo->primarykey);
                    if (count($keyName) > 1) {
                        $filterKey = "";
                        foreach ($keyName as $id => $key) {
                            $key = strtoupper($key);
                            if ($id != 0) $filterKey .= " and ";
                            if (!empty($record)) {
                                $filterKey .= "{$key} = '".$record->$key."'";
                            } else {
                                $filterKey .= "{$key} = ''";
                            }
                        }
                    }  else {
                        $keyName = strtoupper($tableInfo->primarykey);
                        if (!empty($record)) {
                            $filterKey = "{$tableInfo->primarykey} = '".$record->$keyName."'";
                        }  else {
                            $filterKey = "{$tableInfo->primarykey} = ''";
                        }
                    }
                    $tableName = $tableInfo->table;
                }
                else {
                    $keyName = key($record);
                    //this is just a quess
                    $sql = explode("from", $sql);
                    $sql = explode($sql[1], " ");

                    $tableName = $sql[0];
                }



                switch ($action) {
                    case "insert":

                        $sqlInsert = $DEB->getInsertSQL("txt", $tableName, $keyName, true, "{$action}{$name}", $passwordFields, $dateFields, true);

                        if ( $sqlInsert ) {
                            echo $this->bootStrapAlert("success", $caption="Success", "Record was updated successfully");
                            echo script ('$table'.$name.'.bootstrapTable("refresh");' );
                        }
                        else {
                            echo $this->bootStrapAlert("danger", $caption="Failure", "Record could not be updated");
                        }



                        if (defined ("ONINSERT") && !empty(ONINSERT)) {
                            if (is_array($keyName)) $keyName = join("-", $keyName);
                            $params = ["action" => $action, "table" => $tableName, $keyName => $_REQUEST["{$action}{$name}"], "session" => Ruth::getSESSION(), "request" => Ruth::getREQUEST()];
                            @call_user_func_array(ONINSERT, $params);
                        }

                        $tableEvent = "ONINSERT".$name;

                        $constantFound = false;

                        eval ('$constantFound = (defined ("'.$tableEvent.'") && !empty('.$tableEvent.'));');


                        if ($constantFound) {
                            if (is_array($keyName)) $keyName = join("-", $keyName);
                            $params = ["action" => $action, "table" => $tableName, $keyName => $record->$keyName, "session" => Ruth::getSESSION(), "request" => Ruth::getREQUEST()];
                            eval ('@call_user_func_array('.$tableEvent.', $params);');
                        }



                        if (!empty($events->oninsert)) {
                            echo script($events->oninsert);
                        }

                        break;
                    case "update":
                        $sqlUpdate = $DEB->getUpdateSQL("txt", $tableName, $filterKey, "", "{$action}{$name}", $passwordFields, $dateFields, true);

                        if ( $sqlUpdate ) {
                            echo $this->bootStrapAlert("success", $caption="Success", "Record was updated successfully");
                            echo script ('$table'.$name.'.bootstrapTable("refresh");' );
                        }
                        else {
                            echo $this->bootStrapAlert("danger", $caption="Failure", "Record could not be updated");
                        }

                        if (defined ("ONUPDATE") && !empty(ONUPDATE)) {
                            if (is_array($keyName)) $keyName = join("-", $keyName);
                            $params = ["action" => $action, "table" => $tableName, $keyName => $record->$keyName, "session" => Ruth::getSESSION(), "request" => Ruth::getREQUEST()];
                            @call_user_func_array(ONUPDATE, $params);
                        }

                        $tableEvent = "ONUPDATE".$name;

                        $constantFound = false;
                        eval ('$constantFound = (defined ("'.$tableEvent.'") && !empty('.$tableEvent.'));');

                        if ($constantFound) {
                            if (is_array($keyName)) $keyName = join("-", $keyName);
                            $params = ["action" => $action, "table" => $tableName, $keyName => $record->$keyName, "session" => Ruth::getSESSION(), "request" => Ruth::getREQUEST()];
                            eval ('@call_user_func_array('.$tableEvent.', $params);');
                        }


                        if (!empty($events->onupdate)) {
                            echo script($events->onupdate);
                        }
                        break;
                    default:
                        echo $this->bootStrapAlert("danger", $caption="Failed {$action}", "This action is unknown!");
                        break;
                }
            }

        );


        //Add the route for getting the ajax data
        Ruth::addRoute(RUTH_POST, "/cody/data/ajax/{db}", function($db) {
            if (empty($db)) {
                $db = Ruth::getOBJECT("KIM");
            }


            $DEB = Ruth::getOBJECT($db);
            $postData = json_decode(Ruth::getPOST_DATA());

            if (empty($postData))
                exit;

            $object = json_decode(rawurldecode($postData->object));


            if (!empty($object[8])) {
                $events = $object[8];
            }

            $beforeInsert = "";
            $beforeUpdate = "";
            $beforeDelete = "";
            $beforeView = "";

            if (!empty($events->beforeinsert)) $beforeInsert = $events->beforeinsert;
            if (!empty($events->beforeupdate)) $beforeUpdate = $events->beforeupdate;
            if (!empty($events->beforedelete)) $beforeDelete = $events->beforedelete;
            if (!empty($events->beforeview)) $beforeView = $events->beforeview;


            if (!empty($object[5])) {
                $name = $object[5];
            }
            else {
                $name = "grid";
            }

            if (!empty($postData->limit)) {
                $limit = $postData->limit;
            } else {
                $limit = $object[4];
            }
            if (!empty($postData->offset)) {
                $offSet = $postData->offset;
            } else {
                $offSet = 0;
            }

            if (!empty($postData->sort)) {
                $sort = $postData->sort;
            } else {
                $sort = "";
            }

            if (!empty($postData->order)) {
                $order = $postData->order;
            } else {
                $order = "";
            }

            if (!empty($postData->search)) {
                $search = $postData->search;
            } else {
                $search = "";
            }



            $orderBy = "";
            if (!empty($sort)) {
                $orderBy = "order by upper({$sort}) {$order}";
            }

            $sql = $object[0];

            $buttons = $object[1];

            $customButtons = [];
            if (is_object($buttons)) {
                $customButtons = $buttons->custom;
                $buttons = $buttons->buttons;
            }


            if (!is_object ($buttons)) {

                $tempButtons = explode (",", strtolower($buttons));

                if (is_array($tempButtons)) {
                    $buttons = script ("var a{recordid}{$name}record = '{record}';");
                    foreach ($tempButtons as $bid => $button) {
                        switch ($button) {
                            case "insert":
                                $buttons .= (new Cody())->bootStrapButton("btnInsertGrid".$name, "Add", "{$beforeInsert} call{$name}Ajax('/cody/form/insert','{$name}Target', {object : a{$name}object, record: a{recordid}{$name}record, db: '{$db}' })", "btn btn-success", "", true);
                                break;
                            case "update":
                                $buttons .= (new Cody())->bootStrapButton("btnEditGrid".$name, "Edit", " {$beforeUpdate}  call{$name}Ajax('/cody/form/update','{$name}Target', {object : a{$name}object, record: a{recordid}{$name}record, db: '{$db}' })", "btn btn-primary", "", true);
                                break;
                            case "delete":
                                $buttons .= (new Cody())->bootStrapButton("btnDeleteGrid".$name, "Del", "{$beforeDelete} if (confirm('Are you sure you want to delete this record ?')) { call{$name}Ajax('/cody/form/delete','{$name}Target', {object : a{$name}object, record: a{recordid}{$name}record, db: '{$db}' }) }", "btn btn-danger", "", true);
                                break;
                            case "view":
                                $buttons .= (new Cody())->bootStrapButton("btnViewGrid".$name, "View", "{$beforeView} call{$name}Ajax('/cody/form/view','{$name}Target', {object : a{$name}object, record: a{recordid}{$name}record, db: '{$db}' })", "btn btn-warning", "", true);
                                break;
                            default:
                                //add the custom button from the list
                                if (isset($customButtons->$button)) {
                                    $buttons .= $customButtons->$button;
                                }

                                break;
                        }
                    }
                }

                $buttons = div (["class"=>"btn-group btn-group"], $buttons );
            }

            $hideColumns = $object[2];


            $customFields = "";
            if (!empty($object[4])) {
                $customFields = $object[4];
            }

            if (!empty($object[15])) {
                $mtooltip = $object[15];
            } else {
                $mtooltip = "";
            }

            if (!is_array($hideColumns)) {
                $hideColumns = explode(",", strtoupper($hideColumns));
            }

            $DEB->getRow("select first 1 * from ({$sql}) t");
            $fieldInfo = $DEB->fieldinfo;

            $filter = "";
            if (!empty($search)) {
                $filter = [];
                foreach ($fieldInfo as $cid => $field) {
                    if ($field["type"] === "DATE") {
                        if($this->IsDate($search)){
                            $filter[] = "cast({$field["name"]} as char) like '%" . $DEB->translateDate($search, $DEB->outputdateformat, $DEB->dbdateformat) . "%'";
                        }
                    } else
                        if ($field["type"] === "VARCHAR") {
                            $filter[] = "upper({$field["name"]}) like '%" . str_replace("'", "''", strtoupper($search)) . "%'";
                        } else {
                            if (is_numeric($search)) {
                                $filter[] = "{$field["name"]} = '{$search}'";
                            }else{
                                $filter[] = "upper({$field["name"]}) like '%" . str_replace("'", "''", strtoupper($search)) . "%'";
                            }
                        }
                }
                $filter = join(" or ", $filter);
                $filter = "where ({$filter})";
            }


            $data = $DEB->getRow("select count(*) as COUNTRECORDS from ($sql) t {$filter}");
            $recordCount = $data->COUNTRECORDS;


            $sql = "select first {$limit} skip {$offSet} * from ($sql) t {$filter} {$orderBy}";
            $records = $DEB->getRows($sql, DEB_ASSOC, true);



            $rows = [];
            $value = "";
            $calcField = null;

            if (!empty($records)) {
                foreach ($records as $rid => $record) {
                    $row = null;
                    $rowButtons = $buttons."";


                    foreach ($fieldInfo as $fid => $field) {

                        if ($fid == 0) {
                            $field["align"] = "left";
                        }


                        if (!in_array(strtoupper($field["alias"]), $hideColumns)) {

                            $fieldName = strtoupper($field["name"]);
                            $fid = strtoupper($field["alias"]);

                            if (isset($customFields->$fieldName)) {
                                $customField = $customFields->$fieldName;

                                //Populate variables in URL path
                                if (!empty($customField->url)) {
                                    $urlPath = $customField->url;
                                    foreach ($fieldInfo as $fid2 => $field2) {
                                        $urlPath = str_ireplace("{" . $field2["name"] . "}", $record[$field2["name"]], $urlPath);
                                    }
                                } else {
                                    $urlPath = "";
                                }

                                //Populate variables in Onclick event
                                if (!empty($customField->onclick)) {
                                    $onClickEvent = $customField->onclick;
                                    foreach ($fieldInfo as $fid2 => $field2) {
                                        $onClickEvent = str_ireplace("{" . $field2["name"] . "}", $record[$field2["name"]], $onClickEvent);
                                    }
                                } else {
                                    $onClickEvent = "";
                                }


                                $uniq = "";
                                //Populate variables in Onclick event
                                if (!empty($customField->id)) {
                                    $parsedId = $customField->id;
                                    foreach ($fieldInfo as $fid2 => $field2) {
                                        $parsedId = str_ireplace("{" . $field2["name"] . "}", $record[$field2["name"]], $parsedId);
                                    }
                                    $uniq = $parsedId;
                                } else {
                                    $parsedId = "";
                                }

                                //Populate variables in comment field
                                if (!empty($customField->comment)) {
                                    $comment = $customField->comment;
                                    foreach ($fieldInfo as $fid2 => $field2) {
                                        $comment = str_ireplace("{" . $field2["name"] . "}", $record[$field2["name"]], $comment);
                                    }
                                } else {
                                    $comment = "";
                                }

                                //Populate variables in Dropdown SQL
                                if (!empty($customField->lookup)) {
                                    $dropDownSql = $customField->lookup;
                                    foreach ($fieldInfo as $fid2 => $field2) {
                                        $dropDownSql = str_ireplace("{" . $field2["name"] . "}", $record[$field2["name"]], $dropDownSql);
                                    }
                                } else {
                                    $dropDownSql = "";
                                }
                                //check to see if we have a custom class and add it to the container
                                if (empty($customField->class) ? $extraclass = "" : $extraclass = $customField->class);


                                if (empty($customField->disabled))
                                    $customField->disabled = false;

                                if (!empty($customField->list)) {
                                    if (is_object($customField->list)) {
                                        $customField->list = get_object_vars($customField->list);
                                    }

                                }


                                if (empty($customField->type)) {
                                    $customField->type = "text";
                                }

                                switch ($customField->type) {
                                    case "hidden":
                                        $row[$field["name"]] = $record[$fid];
                                        break;
                                    case "lookup":
                                        if (!empty($customField->list[$record[$fid]])) {
                                            $row[$field["name"]] = "" . div(["class" => "text-" . $field["align"] . " " . $extraclass], "" . $customField->list[$record[$fid]]. "");
                                        }
                                        else {
                                            $row[$field["name"]] = "" . div(["class" => "text-" . $field["align"] . " " . $extraclass], "-");
                                        }
                                        break;
                                    case "link":
                                        $row[$field["name"]] = "" . div(["class" => "text-" . $field["align"] . " " . $extraclass], area(["id" => $parsedId, "href" => $urlPath, "onclick" => $onClickEvent], "" . $record[$fid] . ""));
                                        break;
                                    case "checkbox":
                                        $row[$field["name"]] = "" . div(["class" => "text-" . $field["align"] . " " . $extraclass], "" . $record[$fid] . "");
                                        break;
                                    case "calculated":
                                        eval('$value = ' . $customField->formula . ';');
                                        $row[$field["name"]] = "" . div(["class" => "text-" . $field["align"] . " " . $extraclass], "" . $value . "");
                                        break;
                                    case "image":
                                        if (empty($customField->size)) {
                                            $customField->size = "100x100";
                                        }

                                        $size = explode ("x", $customField->size);
                                        $styleWidth = $size[0];
                                        $styleHeight = $size[1];

                                        $row[$field["name"]] = "" .img(["class" => "thumbnail", "style" => "height: {$styleHeight}px; width: {$styleWidth}px", "src" => $DEB->encodeImage($record[$fid], "/imagestore", $customField->size), "alt" => ucwords(str_replace("_", " ", strtolower($field["alias"])))]);
                                        //we need to make the record happy as well;
                                        $record[$fid] = "[image]";

                                        break;
                                    case "dropdown":
                                        if (empty($uniq)) {
                                            $uniq = $customField["id"] . uniqid();
                                        }
                                        $row[$field["name"]] = "" . div(["class" => "text-" . $field["align"] . " " . $extraclass], div(["class" => "dropdown"], button(["class" => "btn btn-default dropdown-toggle", "style" => "width:100%", "type" => "button", "id" => $uniq, "data-toggle" => "dropdown", "aria-expanded" => "true"], "" . $record[$fid] . " " . span(["class" => "caret"])), ul(["class" => "dropdown-menu", "role" => "menu", "aria-labelledby" => "dropdownMenu1"], (new Cody($DEB))->populateDropDownItems($dropDownSql, $onClickEvent, $uniq)
                                                    )
                                                )
                                            );
                                        break;
                                    default :

                                        $row[$field["name"]] = "" . div(["class" => "text-" . $field["align"] . " " . $extraclass], "" . $record[$fid] . "");
                                        break;
                                }
                            } else {

                                $row[$field["name"]] = "" . div(["class" => "text-" . $field["align"]], "" . $record[$fid] . "");
                            }
                        }


                        if ($rowButtons !== "") {

                            $rowButtons = str_ireplace("{" . $field["alias"] . "}", $record[strtoupper($field["alias"])], $rowButtons."");
                        }


                        $rows[$rid] = $row;
                    }

                    if ($rowButtons !== "") {

                        $rowButtons = str_replace ("{record}", rawurlencode(json_encode($record)), $rowButtons);
                        $rowButtons = str_replace ("{recordid}", $rid, $rowButtons);
                    }

                    if ($rowButtons !== "") {
                        $rows[$rid]["BUTTONS"] = "" . div($rowButtons);
                    }


                }
            }
            $result = ["total" => $recordCount, "rows" => $rows, "sql" => $sql];


            echo json_encode($result);
        }
        );
    }

    /**
     * @param string $alertType uses "success","info","warning","danger"
     * @param string $caption this is the text that will be displayed before the message is "WARNING!..."
     * @param string $content this is the content of the message to be displayed
     * @param bool $dismissible true/false. this will set the alert to be dismissable
     * @return shape
     */
    function bootStrapAlert($alertType = "success", $caption = "", $content = "", $dismissible = false) {
        if ($caption) {
            $caption = strong($caption);
        }
        if ($dismissible) {
            $alertType .= " alert-dismissible";
            $closeButton = button(["type" => "button", "class" => "close", "data-dismiss" => "alert", "aria-label" => "Close"], span(["aria-hidden" => "true", "class" => "fa fa-times"]));
        } else {
            $closeButton = "";
        }
        $html = div(["class" => "alert alert-" . $alertType, "role" => "alert"], $closeButton, $caption . " " . $content);

        return $html;
    }

    /**
     * An easy way to make a paginated bootStrapTable
     *
     * @param String $sql
     * @param Array $buttons
     * @param String $hideColumns
     * @param String $toolbar
     * @param Integer $rowLimit
     * @param Integer $selected_page
     * @param Array $customFields
     * @param String $name
     * @param String $tableInfo - Array of information about the table ["table" => "tablename", "primarykey" => "field1,field2[,...]", "fields" => "field1,field2[,...]"]
     * @param String $formHideFields field1,field2[,...]
     * @param String $events Array on events that could happen ["onupdate" => "javascript", "oninsert" => "javascript", "ondelete" => "javascript", "beforeinsert" => "javascript", "beforeupdate" => "javascript", "beforedelete" => "javascript"]
     * @param String $class
     * @param Boolean $paginate
     * @param Boolean $searchable
     * @param Boolean $checked
     * @param String $checkPostURL
     * @return type
     */
    function bootStrapTable($sql = "select * from user_detail", $buttons = "", $hideColumns = "", $toolbar = "My Grid", $customFields = null, $name = "grid", $tableInfo="", $formHideFields="", $events="", $class = "table table-striped",$rowLimit = 20, $paginate = true, $searchable = true, $checked = false, $selected_page = 1, $checkedPostURL = "", $checkSingleSelect = true, $checkEvent = "", $mobiletooltip = "") {
        $DEB = $this->DEB;
        $hideColumns = explode(",", strtoupper($hideColumns));
        $object = rawurlencode(json_encode(func_get_args()));
        $paginating = "false";
        if ($paginate) {
            $paginating = "true";
        }
        $options = ["id" => $name, "class" => $class, "data-toolbar" => "#toolbar" . $name,
            "data-pagination" => "{$paginating}",
            "data-side-pagination" => "server",
            "data-search" => "false",
            "data-page-list" => "[5, 10, 20, 50, 100, 200]",
            "data-page-size" => $rowLimit
        ];

        if ($searchable) {
            $options["data-search"] = "true";
        }

        if ($checked) {
            $options["data-click-to-select"] = "true";
            if ($checkSingleSelect) {
                $options["data-single-select"] = "true";
            }
        }

        $beforeInsert = "";
        if (!empty($events["beforeinsert"])) {
            $beforeInsert = $events["beforeinsert"];
        }


        $data = @$DEB->getRow("select first 1 * from ({$sql}) t ");



        $fieldInfo = @$DEB->fieldinfo;

        if (empty($fieldInfo)) {
            die("Perhaps the SQL for this query is broken {$sql} or the table does not exist, have you specified the correct database in your Cody initialization, Try running migrations with maggy");
        }

        $header = "";
        if ($checked) {
            $header .= th(["data-field" => "checked" . $name . "", "class" => "text-left", "data-checkbox" => "true"], "");
        }
        foreach ($fieldInfo as $fid => $field) {
            if (!in_array(strtoupper($field["name"]), $hideColumns)) {

                if (isset($customFields[$field["name"]])) {
                    $customField = $customFields[$field["name"]];

                    if (empty($customField["type"])) {
                        $customField["type"] = "text";
                    }

                    switch ($customField["type"]) {
                        default:
                            $header .= th(["data-field" => $field["name"], "class" => "text-" . $field["align"], "data-sortable" => "true"], ucwords(str_replace("_", " ", strtolower($field["alias"]))));
                            break;
                        case "checkbox":
                            $header .= th(["data-field" => $field["name"], "class" => "text-" . $field["align"], "data-checkbox" => "true"], "");
                            break;
                        case "hidden":
                            $header .= th(["data-field" => $field["name"], "class" => "hidden"], ucwords(str_replace("_", " ", strtolower($field["alias"]))));
                            break;
                    }
                } else {
                    $header .= th(["data-field" => $field["name"], "class" => "text-" . $field["align"], "data-sortable" => "true"], ucwords(str_replace("_", " ", strtolower($field["alias"]))));
                }
            }
        }

        $addColumn = "";
        if ($buttons) {
            $addColumn .= th(["data-field" => "BUTTONS"], "Options");
        }

        $header = thead(tr($header . $addColumn));

        if (empty($toolbar["caption"])) {
            $toolbar = array();
            $toolbar["caption"] = "";
        }



        $insertButton = $this->bootStrapButton("btnInsert".$name, "Add", " {$beforeInsert}  call{$name}Ajax('/cody/form/insert','{$name}Target', {object : a{$name}object, record: null, db: '{$DEB->tag}' })", "btn btn-success pull-left", "", true);

        if (empty($toolbar["buttons"])) {
            $toolbar["buttons"] = "";
            $toolbar["buttons"] = $insertButton.$toolbar["buttons"];
        }



        if (empty($toolbar["filter"])) {
            $toolbar["filter"] = "";
        }

        $tableHeading = "";
        if (!empty($toolbar["caption"])) {
            $tableHeading = h3($toolbar["caption"]) . hr();
        }
        $toolbarButtons = $toolbar["buttons"];
        $toolbarFilters = $toolbar["filter"];

        if ($searchable) {
            $toolbarFilters .= div(["class" => "search"], input(["id" => "search{$name}", "class" => "search form-control", "type" => "text", "placeholder" => "Search " . $toolbar["caption"], "onkeyup" => '$table' . $name . '.bootstrapTable(\'getData\')']));
        }


        $toolbarFilters = div(["class" => "form-inline", "role" => "form"], $toolbarFilters);



        $html = div(["class" => "table-responsive"], div(["class" => "table-toolbar clearfix"], div(["class" => "toolbar-buttons"], $tableHeading.$toolbarButtons) . div(["class" => "toolbar-filters"], $toolbarFilters)) . table($options, $header, tbody()));

        $html .= form(input (["type" => "hidden", "id" => $name."Checked"], ""));
        $html .= script('
                    function get'.$name.'Checked() {
                        var table'.$name.'Data = $table'.$name.'.bootstrapTable("getData");

                        var checked'.$name.'Object = [];
                        for (var i = 0, l = table'.$name.'Data.length; i < l; i++) {
                            if (table'.$name.'Data[i].checked'.$name.') {
                                checked'.$name.'Object.push(table'.$name.'Data[i].'.$tableInfo["primarykey"].'.replace(/<(?:.|\n)*?>/gm, ""));

                            }
                        }

                        $("#'.$name.'Checked").val(JSON.stringify(checked'.$name.'Object));
                    }

                    function refresh'.$name.'() {
                        $table' . $name . '.bootstrapTable("refresh", {pageNumber: 1});
                    }

                    var a'.$name.'object = "' . $object . '";

                    var $table' . $name . ' =  $("#' . $name . '").bootstrapTable({search : false, url : "/cody/data/ajax/' . $this->DEB->tag . '",
                                                                                method : "post",
                                                                                onCheck: function (row) {
                                                                                            eventType = \'check\';
                                                                                            get'.$name.'Checked();
                                                                                           ' . $checkEvent . '
                                                                                },
                                                                                onUnCheck: function (row) {
                                                                                           eventType = \'uncheck\';
                                                                                           get'.$name.'Checked();
                                                                                           ' . $checkEvent . '
                                                                                },
                                                                                onCheckAll: function () {
                                                                                            get'.$name.'Checked();
                                                                                },
                                                                                onUncheckAll: function () {
                                                                                            get'.$name.'Checked();
                                                                                },
                                                                                queryParams: function (p) {  return {object: a'.$name.'object, limit: p.limit, offset :p.offset, order: p.order, search : $("#search' . $name . '").val(), sort: p.sort }

                                                                                } });
                   $search = $("#search' . $name . '");
                    var timeoutId = null;
                    $search.off("keyup").on("keyup", function (event) {
                        clearTimeout(timeoutId);
                        timeoutId = setTimeout(function () {
                            refresh'.$name.'();
                        }, 1000);
                    });

                  ');

        $html .= div (["id" => "{$name}Target"]);
        $html .= $this->ajaxHandler("", "{$name}Target", "call{$name}Ajax");

        return $html;
    }

    /**
     * Helper function to populate the li of the dropdown items
     * @param type $dropDownSql
     * @return type
     */
    function populateDropDownItems($dropDownSql, $onClick, $parent) {

        $html = "";
        $lookuplist = [];
        $lookuprow = $this->DEB->getRows($dropDownSql, DEB_ARRAY);
        foreach ($lookuprow as $irow => $row) {
            $lookuplist[$irow] = $row[0] . "," . $row[1];
        }

        foreach ($lookuplist as $lid => $option) {
            $option = explode(",", $option);
            $option[0] = trim($option[0]);
            $html .= li(["role" => "presentation"], area(["role" => "menuitem",
                    "tabindex" => "-1",
                    "href" => "javascript:void(0);",
                    "onclick" => "  $('#{$parent}').text($(this).text()); $('#{$parent}').append('&nbsp;<span class=\'caret\'></span>'); {$onClick}",
                    "optionId" => "{$option[0]}"
                ], "{$option[1]}")
            );
        }

        return $html;
    }

    /**
     * @todo generate unique id identifier for tab ids
     * @param array $tabs
     * @return string
     *
     * USAGE
     * $tabs = array(
     * "Tab 1" => div("HTML/Shape tags", p("paragraph content")),
     * "Tab 2" => "Any Content",
     * "Tab 3" => "Tab 3 Content",
     * );
     * echo (new Cody($this->DEB))->bootStrapTabs($tabs);
     */
    function bootStrapTabs($tabs = array()) {

        $html = "";

        if (!empty($tabs)) {
            /**
             * Filter through tabs, creating additional values
             */
            $tempTabs = array();

            foreach ($tabs as $tabName => $tabContent) {

                /* transform tab name into id string */
                $id = "t" . str_replace(" ", "", strtolower($tabName));
                /* Generate unique id based on id */
                $uniqid = uniqid($id . "-", false);

                $tempTabs[] = array(
                    "tab" => $tabName,
                    "content" => $tabContent,
                    "tab_id" => "t" . $uniqid,
                    "tab_id_name" => "#t" . $uniqid,
                    "show_id" => "show" . $uniqid,
                    "show_id_name" => "#show" . $uniqid,
                    "anchor_id" => str_replace(" ", "_", $tabName)
                );
            }

            $tabs = $tempTabs;

            $i = 0;
            $tabPanel = "";
            $tabContent = "";

            foreach ($tabs as $tab) {

                $tab = (object) $tab;

                /* Set first tab to active */
                $active = $i == 0 ? "active" : "";

                /* Add Tabs to the panel */
                $tabPanel .= li(["role" => "presentation", "id" => "{$tab->tab_id}", "class" => "{$active}"], area(["id" => "{$tab->anchor_id}", "aria-controls" => "{$tab->show_id}", "role" => "tab", "data-toggle" => "tab", "href" => "{$tab->show_id_name}",], $tab->tab));

                /* Add tab content */
                $tabContent .= div(["role" => "tabpanel", "class" => "tab-pane {$active}", "id" => "{$tab->show_id}"], $tab->content);

                $i++;
            }

            /* Tab Content */
            $tabContent = div(["class" => "tab-content"], $tabContent);

            /* Tabs */
            $html = div(["role" => "tabpanel"], ul(["class" => "nav nav-tabs", "role" => "tablist", "id" => "mytab"],
                    /* add Tab panel items */ $tabPanel
                ) . $tabContent);
        }

        return $html;
    }


    /**
     * A function to create a valid Bootstrap Form
     * @param String $sql A valid SQL statement for the selected database
     * @return type
     */
    function bootStrapForm($sql = "", $hideColumns = "", $custombuttons = null, $customFields = null, $submitAction = "", $formId = "formId", $prefix="txt", $noForm = false) {
        $fieldinfo = $this->getFieldInfo($sql);
        $originalPrefix = $prefix;

        $hideColumns = explode(",", strtoupper($hideColumns));

        $record = @$this->DEB->getRow($sql, 0, DEB_ARRAY);
        $html = "";

        $validation = [];
        $messages = [];

        foreach ($fieldinfo as $fid => $field) {
            $prefix = $originalPrefix;

            if (!isset($record[$fid])) {
                $record[$fid] = null;
            } else {
                $record[$fid] .= "";
            }

            if (!in_array(strtoupper($field["name"]), $hideColumns)) {
                $input = "";


                if (is_object($customFields)) {
                    $customFields = (array) $customFields;
                }


                if (array_key_exists( strtoupper($field["name"]), $customFields) || array_key_exists(strtoupper($field["alias"]), $customFields)) {
                    //first try the alias
                    $customField = "";
                    if (array_key_exists(strtoupper($field["alias"]), $customFields)) {
                        $customField = $customFields[strtoupper($field["alias"])];
                    } else
                        //then try the field
                        if (empty($customField)) {
                            $customField = $customFields[strtoupper($field["name"])];
                        }


                    if (is_array($customField)) {
                        $customField = (object) $customField;
                    }

                    if (!empty($customField->defaultValue) && empty($record[$fid])) {
                        $record[$fid] = $customField->defaultValue;
                    }

                    if (!empty($customField->constantValue)) {
                        $record[$fid] = $customField->constantValue;
                    }

                    if (!empty($customField->list)) {
                        if (is_object($customField->list)) {
                            $customField->list = get_object_vars($customField->list);
                        }

                    }

                    if (!isset($customField->event))
                        $customField->event = "";
                    if (!isset($customField->style))
                        $customField->style = "";

                    if (isset($customField->prefix))
                        $prefix = $customField->prefix;

                    if (!empty($customField->validation)) {
                        $message = explode(",", $customField->validation);
                        $mfound = false;
                        foreach ($message as $mid => $mvalue) {
                            $mvalue = explode(":", $mvalue);

                            if ($mvalue[0] == "message") {
                                $messages[] = $prefix . strtoupper($field["name"]) . ": { required: '" . $mvalue[1] . "', remote: '" . $mvalue[1] . "'}";
                                $mfound = true;
                                unset($message[$mid]);
                            }
                        }

                        if ($mfound)
                            $customField->validation = join(",", $message);

                        $validation[] = $prefix . strtoupper($field["name"]) . ": {" . $customField->validation . "}";
                    } else {
                        $validation[] = $prefix . strtoupper($field["name"]) . ":{required: true}";
                    }

                    if (empty($customField->type)) {
                        $customField->type = "text";
                    }

                    switch ($customField->type) {
                        case "custom":
                            $call = explode("->", $customField->call);
                            if (count($call) > 1) {
                                $call[0] = str_replace ("(", "", $call[0]);
                                $call[0] = str_replace (")", "", $call[0]);
                                $call[0] = str_replace ("new", "", $call[0]);
                                $call[0] = trim($call[0]);
                                $callClass = "";
                                eval ('$callClass = new '.$call[0].'();');
                                $call[0] = $callClass;
                            } else {
                                $call = $customField->call;
                            }

                            $input = div(call_user_func($call, $record[$fid], strtoupper($field["name"])));
                            break;
                        case "password":
                            $input = input(["class" => "form-control", "type" => "password", "placeholder" => ucwords(str_replace("_", " ", strtolower($field["alias"]))), "name" => $prefix . strtoupper($field["name"]), "id" => $prefix . strtoupper($field["name"])], "");
                            break;
                        case "hidden":
                            $input = input(["class" => "form-control hidden", "type" => "hidden", "placeholder" => ucwords(str_replace("_", " ", strtolower($field["alias"]))), "name" => $prefix . strtoupper($field["name"]), "id" => $prefix . strtoupper($field["name"])], $record[$fid]);
                            break;
                        case "readonly":
                            $input = input(["class" => "form-control readonly", "type" => "text", "readonly", "placeholder" => ucwords(str_replace("_", " ", strtolower($field["alias"]))), "name" => $prefix . strtoupper($field["name"]), "id" => $prefix . strtoupper($field["name"])], $record[$fid]);
                            break;
                        case "date":
                            $input = input(["class" => "form-control", "type" => "text", "placeholder" => ucwords(str_replace("_", " ", strtolower($field["alias"]))), "name" => $prefix . strtoupper($field["name"]), "id" => $prefix . strtoupper($field["name"])], $record[$fid]);
                            $input .= script("$('#" . $field["name"] . "').datepicker({format: '" . strtolower($this->DEB->outputdateformat) . "'}).on('changeDate', function(ev) {  " . $customField["event"] . " } );");
                            break;
                        case "toggle":
                            $checked = null;
                            $input = input(["class" => "form-control", "type" => "checkbox", "data-toggle" => "toggle", "data-on" => "Yes", "data-off" => "No", "placeholder" => ucwords(str_replace("_", " ", strtolower($field["alias"]))), "name" => $prefix . strtoupper($field["name"]), "id" => $prefix . strtoupper($field["name"])], $record[$fid]);
                            if ($record[$fid] == 1) {
                                $input->addAttribute("checked", "checked");
                            }
                            break;

                        case "checkbox":
                            if ($record[$fid] == 1) {
                                $checked = "checked";
                            } else {
                                $checked = "";
                            }

                            $input = br() . label(["class" => "checkbox-inline"], input(["type" => "checkbox", "name" => $prefix . strtoupper($field["name"]), "id" => $prefix . strtoupper($field["name"]), "value" => "1", $checked]), $customField->checkCaption );
                            break;
                        case "image":
                            $input = img(["class" => "thumbnail", "style" => "height: 160px; width: 160px", "src" => $this->DEB->encodeImage($record[$fid], "/imagestore", "160x160"), "alt" => ucwords(str_replace("_", " ", strtolower($field["alias"])))]);
                            $input .= input(["type" => "hidden", "name" => "MAX_FILE_SIZE"], "4194304");
                            $input .= input(["class" => "btn btn-primary", "type" => "file", "accept" => "image/*", "name" => $prefix . strtoupper($field["name"]), "id" => $prefix . strtoupper($field["name"]), "onclick" => $customField->event], ucwords(str_replace("_", " ", strtolower($field["alias"]))));
                            break;
                        case "file":
                            $input = input(["type" => "hidden", "name" => "MAX_FILE_SIZE"], "4194304");
                            $input .= input(["class" => "btn btn-primary", "type" => "file", "name" => $prefix . strtoupper($field["name"]), "id" => $prefix . strtoupper($field["name"]), "onclick" => $customField->event], ucwords(str_replace("_", " ", strtolower($field["alias"]))));
                            break;
                        case "video":
                            $input = input(["type" => "hidden", "name" => "MAX_FILE_SIZE"], "4194304");
                            $filename = "video".md5($record[$fid]).".mp4";
                            //check first if the file exists
                            if(!file_exists($filename)) {
                                if (!file_exists($_SERVER["DOCUMENT_ROOT"]."/videostore")) {
                                    mkdir ($_SERVER["DOCUMENT_ROOT"]."/videostore");
                                }
                                file_put_contents(Ruth::getDOCUMENT_ROOT()."/videostore/".$filename, $record[$fid]);
                            }
                            //make the vidoe player view
                            $input .= video( [ "class" => "thumbnail", "width"=>"250", "height"=>"160", "controls"=>"true" ],
                                source( ["src" => "/videostore/{$filename}"]),
                                source( ["src" =>"/videostore/novideo.mp4"])
                            );
                            $input .= input(["class" => "btn btn-primary", "type" => "file", "name" => $prefix . strtoupper($field["name"]), "id" => $prefix . strtoupper($field["name"]), "onclick" => $customField->event], ucwords(str_replace("_", " ", strtolower($field["alias"]))));
                            break;

                        case "text":
                            $input = input(["class" => "form-control", "type" => "text", "placeholder" => ucwords(str_replace("_", " ", strtolower($field["alias"]))), "name" => $prefix . strtoupper($field["name"]), "id" => $prefix . strtoupper($field["name"])], $record[$fid]);
                            break;
                        case "readonly":
                            $input = input(["class" => "form-control", "type" => "text", "readonly" => "readonly", "placeholder" => ucwords(str_replace("_", " ", strtolower($field["alias"]))), "name" => $prefix . strtoupper($field["name"]), "id" => $prefix . strtoupper($field["name"])], $record[$fid]);
                            break;
                        case "textarea":

                            $input = textarea(["class" => "form-control", "style" => $customField->style, "rows" => "5", "placeholder" => ucwords(str_replace("_", " ", strtolower($field["alias"]))), "name" => $prefix . strtoupper($field["name"]), "id" => $prefix . strtoupper($field["name"])], $record[$fid]);
                            break;
                        case "lookup":
                            if (!isset($customField->event))
                                $customField->event = "";
                            if (!isset($customField->readonly))
                                $customField->readonly = false;
                            $input = $this->select($prefix . strtoupper($field["name"]), ucwords(str_replace("_", " ", strtolower($field["alias"]))), "array", $customField->list, $record[$fid], $customField->event, $prefix . strtoupper($field["name"]), $customField->readonly);
                            break;
                        default:
                            $input = div($record[$fid]);
                            break;
                    }

                    if (!empty($customField->description)) {
                        $input .= i(["class" => "field-optional alert-success"], ($customField->description));
                    }
                } else { //generic form
                    $validation[] = $prefix . $field["name"] . ":{required: false}";
                    $input = input(["class" => "form-control", "type" => "text", "placeholder" => ucwords(str_replace("_", " ", strtolower($field["alias"]))), "name" => $prefix . strtoupper($field["name"]), "id" => $prefix . strtoupper($field["name"])], $record[$fid]);
                    $input .= i(["class" => "field-optional alert-warning"], "*Optional");
                }

                if (isset($customFields[strtoupper($field["name"])])) {
                    $customField = $customFields[strtoupper($field["name"])];
                    if (is_array($customField)) {
                        $customField = (object) $customField;
                    }

                    switch ($customField->type) {
                        case "hidden":
                            $html .= $input;
                            break;
                        default:
                            $colWidth = "col-md-6";

                            if (!empty($customField->colWidth)) {
                                $colWidth = $customField->colWidth;
                            }

                            if ( (strtoupper($customField->type) === "IMAGE" && empty($customField->colWidth) ) || strtoupper($customField->type) === "TEXTAREA"  )  {
                                $colWidth = "col-md-12";

                            }

                            if (!empty($customField->help)) {
                                $html .= div(["class" => "form-group", "id" => "form-group" . $field["name"]], label(["id" => "label" . $field["name"], "for" => $prefix . strtoupper($field["name"])], ucwords(str_replace("_", " ", strtolower($field["alias"])))), span(["id" => "help" . $field["name"], "class" => "icon-x-info info-icon", "data-toggle" => "tooltip", "data-placement" => "right", "title" => $customField["help"]]), $input);
                                $html .= script("$('#help{$field["name"]}').tooltip({ trigger: 'hover' });");
                            } else {
                                $html .= div(["class" => "form-group {$colWidth}", "id" => "form-group" . $field["name"]], label(["id" => "label" . $field["name"], "for" => $prefix . strtoupper($field["name"])], ucwords(str_replace("_", " ", strtolower($field["alias"])))), $input);
                            }
                            break;
                    }
                } else {
                    $colWidth = "col-md-6";
                    $html .= div(["class" => "form-group {$colWidth}", "id" => "form-group" . $field["name"]], label(["id" => "label" . $field["name"], "for" => $field["name"]], ucwords(str_replace("_", " ", strtolower($field["alias"])))), $input);
                }
            } else {
                $html .= input(["class" => "form-control hidden", "type" => "hidden",  "name" => strtoupper($field["name"]), "id" => strtoupper($field["name"])], $record[$fid]);
            }
        }

        if ($noForm) {
            $html = shape( $html, $custombuttons, $this->validateForm(join($validation, ","), join($messages, ",")));
        } else {
            $html = form(["method" => "post", "action" => $submitAction, "onsubmit" => "return false", "enctype" => "multipart/form-data", "id" => $formId], $html, $custombuttons, $this->validateForm(join($validation, ","), join($messages, ","), $formId));
        }

        return $html;
    }

    /**
     * This function gets the fields from an SQL statement
     * @param String $sql A valid SQL statement for the selected database
     * @return FieldInfo An Array of Field Objects
     */
    function getFieldInfo($sql) {
        $record = $this->DEB->getRow($sql);
        return $this->DEB->fieldinfo;
    }

    /**
     * The select tag creator
     *
     * @param String $name The name of the select drop down
     * @param String $alttext The hover text to be displayed
     * @param String $selecttype Can be array, sql, multiarray, multisql
     * @param String $lookup An SQL statement or pipe delimited key value set - eg 0,None|1,Yes|2,No
     * @param String $value A value key to be set as the default in the drop down
     * @param String $event An event for the select box - eg onchange="window.alert('hello');"
     * @param String $cssid The name of the id for styling purposes
     * @param Boolean $readonly Is the component readonly
     * @param Boolean $nochoose Removes a dummy choose from option
     * @return String The resulting html for the select
     */
    function select($name = "txt", $alttext = "", $selecttype = "array", $lookup = "", $value = "", $event = "", $cssid = "", $readonly = false, $nochoose = true) {

        if (isset($_REQUEST[$name])) {
            if ($value == "") {
                $value = $_REQUEST[$name];
            }
        }
        $lookuplist = [];
        if ($selecttype == "array" || $selecttype == "multiarray") {

            if (is_array($lookup)) {
                $lookuplist = $lookup;
            } else {
                $lookuplist = explode("|", $lookup);
            }
        } else if ($selecttype == "sql" || $selecttype == "multisql") {
            $lookuprow = $this->DEB->getRows($lookup, DEB_ARRAY); //format [0] = NAME


            foreach ($lookuprow as $irow => $row) {
                $lookuplist[$irow] = $row[0] . "," . $row[1]; //make it in the form of array
            }
        }
        //make options for the type of select etc .....
        if ($selecttype == "multiarray" || $selecttype == "multisql") {
            $options = "multiple=\"multiple\"";
        } else {
            $options = "";
        }
        if ($readonly == true) {
            $disabled = "disabled=\"disabled\"";
        } else {
            $disabled = "";
        }
        //default text
        if ($alttext == "") {
            $alttext = "Choose";
        }
        if ($cssid != "") {
            $cssid = "id=\"{$cssid}\"";
        } else {
            $cssid = "";
        }
        $html = "<select class=\"form-control\" $cssid  name=\"{$name}\" $options {$event} $disabled >";

        if (!$nochoose) {
            $html .= "<option value=\"\">{$alttext}</option>";
        }

        foreach ($lookuplist as $lid => $option) {

            $toption = explode(",", $option);
            if (count($toption) != 2) {
                $toption[0] = $lid;
                $toption[1] = $option;
            }

            $option = $toption;

            if (trim($value) == trim($option[0])) {
                $html .= "<option selected=\"selected\" value=\"{$option[0]}\">{$option[1]}</option>";
            } else {
                $html .= "<option value=\"{$option[0]}\">{$option[1]}</option>";
            }
        }
        $html .= "</select>";
        return $html;
    }

    /**
     * The Validation of forms
     *
     * The form validator is an easy way to validate forms in the system using jQuery
     *
     * @see http://jqueryvalidation.org/files/demo/
     * @param String $rules A JSON object which matches the form inputs we need to use
     * @param String $messages A JSON object which matches the form inputs for the messaging
     * @return String An HTML javascript output
     */
    function validateForm($rules, $messages = "", $formId = "formInput") {
        $html = script(array("text" => "application/javascript"), "
        \$(document).ready (function () { \$('#$formId').validate({
                                            rules: {
                                            {$rules}
                                            },
                                             messages : {
                                            {$messages}
                                            },
                                            highlight: function(element) {
                                                $(element).closest('.form-group').addClass('has-error');
                                            },
                                            unhighlight: function(element) {
                                               $(element).closest('.form-group').removeClass('has-error');
                                            },
                                            errorElement: 'div',
                                            errorClass: 'col-sm-12 label label-danger',
                                            errorPlacement: function(error, element) {
                                                if(element.parent('.input-group').length) {
                                                    error.insertAfter(element.parent());
                                                } else {
                                                    error.insertAfter(element);
                                                }
                                            },
                                            invalidHandler: function(form, validator) {
                                                    var errors = validator.numberOfInvalids();
                                                    if (errors) {
                                                        validator.errorList[0].element.focus();
                                                    }
                                                }
                                          });


                                        });
    ");
        return $html;
    }

    /**
     * Boot strap panel formatter
     * @param String $title
     * @param String $content
     * @param String $class
     */
    function bootStrapPanel($title, $content, $class = "panel panel-default", $footerContent = "", $icon = "") {

        if ($footerContent == "") {
            $footer = "";
        } else {
            $footer = div(["class" => "panel-footer"], $footerContent);
        }
        $headericon = ($icon !== "" ? span(["class" => "{$icon}"]) : "");
        return div(["class" => $class], div(["class" => "panel-heading"], b($headericon . $title)), div(["class" => "panel-body"], $content), $footer);
    }

    /**
     * This is a function to create a dashboard type message box
     *
     * @param String $title Main title of the message box
     * @param String $linkTitle Title of the link
     * @param String $url The url/href of the link
     * @param String $number value for the amount of comments or etc..
     * @param type $panelClass Class for the panel to determine the color or style
     * @param type $glyphClass Class for the glyph to determine which glyph and size
     * @return $html return
     */
    function bootStrapMessageBox($title, $linkTitle = "Click Here", $url = "#", $number = "", $panelClass = "panel panel-primary", $glyphClass = "fa fa-comments fa-5x") {
        $html = "";
        $html .= div(["class" => "row"], div(["class" => $panelClass], div(["class" => "panel-heading"], div(["class" => "row"], div(["class" => "col-xs-3"], i(["class" => $glyphClass])
            ), div(["class" => "col-xs-9 text-right"], div(["class" => "huge"], $number), div($title)
                )
            )
        ), area(["href" => $url], div(["class" => "panel-footer"], span(["class" => "pull-left"], $linkTitle), span(["class" => "pull-right"], i(["class" => "fa fa-arrow-circle-right"])
                ), div(["class" => "clearfix"])
                )
            )
        ));
        return $html;
    }

    function bootStrapModal($header, $body, $footer, $closeCode = "$('.modal').removeClass('show');", $icon = "", $modalsize = "") {
        $headericon = "";
        $size = ( $modalsize !== "" ? $modalsize : "lg" );
        if ($icon !== "") {
            $headericon = span(["class" => "{$icon}"]);
        }
        $html = style (".modal {
                                overflow: scroll;
                            }");
        $html .= div(["class" => "modal show", "role" => "dialog", "aria-hidden" => "false"], div(["class" => "modal-dialog modal-{$size}"], div(["class" => "modal-content"],
                    div(["class" => "modal-header"], button(["type" => "button", "class" => "close", "onclick" => "{$closeCode}", "aria-label" => "Close"], span(["aria-hidden" => "true"], "×")), h4($headericon . " " . $header)
                    ),
                    div(["class" => "modal-body", "id" => "modal-body"], $body
                    ),
                    div(["class" => "modal-footer"], $footer
                    )
                )
            )
        );


        return $html;
    }

    /**
     *
     * @param type $sql
     * @param type $hideColumns
     * @param type $custombuttons
     * @param type $customFields
     * @param type $submitAction
     * @return type
     */
    function bootStrapView($sql = "", $hideColumns = "", $custombuttons = null, $customFields = null, $submitAction = "") {
        $fieldinfo = $this->getFieldInfo($sql);

        $hideColumns = explode(",", strtoupper($hideColumns));

        $record = $this->DEB->getRow($sql, 0, DEB_ARRAY);

        $html = "";
        $htmlcontent = "";

        /* template for each row */
        $template = div(["class" => "row review-lines"], $key = div(["class" => "col-md-6"], ""), $value = div(["class" => "col-md-6"], "")
        );

        foreach ($fieldinfo as $fid => $field) {
            if (!in_array($field["name"], $hideColumns)) {


                if (empty($record[$fid])) {
                    $record[$fid] = null;
                }

                if (isset($customFields[$field["name"]])) {
                    $customField = $customFields[$field["name"]];

                    switch ($customField["type"]) {
                        case "select":
                            $key->setContent(b(ucwords(str_replace("_", " ", strtolower($field["alias"])))));

                            $lookupList = "";
                            $lookuprow = $this->DEB->getRows($customField["lookup"], DEB_ARRAY);

                            foreach ($lookuprow as $irow => $row) {
                                $lookupList .= $row[0] . "<br>\n";
                            }

                            $value->setContent($lookupList);

                            $htmlcontent .= $template;
                            break;
                        default:
                            $key->setContent(b(ucwords(str_replace("_", " ", strtolower($field["alias"])))));
                            $value->setContent($record[$fid]);
                            $htmlcontent .= $template;
                            break;
                    }
                } else {
                    $key->setContent(b(ucwords(str_replace("_", " ", strtolower($field["alias"])))));
                    $value->setContent($record[$fid]);
                    $htmlcontent .= $template;
                }
            }
        }

        $html .= div(["class" => ""], $htmlcontent
        );

        $html = form(["method" => "post", "action" => $submitAction, "enctype" => "multipart/form-data"], $html, hr(), $custombuttons);


        return $html;


        //  $html = form(["method" => "post", "action" => $submitAction, "enctype" => "multipart/form-data"], $html, $custombuttons);
    }


    function getFilesForUpdate($dir) {
        $fileArray = [];

        foreach (glob($dir."/*") as $file) {
            $file = str_replace ('\\', '/', $file);
            if (is_dir($file)) {
                $fileArray = array_merge( $fileArray, $this->getFilesForUpdate($file));
            } else {
                $fileArray[] = $file;
            }
        }
        return $fileArray;
    }


    function runUpdate ($versionFolder, $destFolder, $applyUpdates=false) {
        $destFolder = str_replace ('\\', '/', $destFolder);
        $versionFolder = str_replace ('\\', '/', $versionFolder);

        $updateFiles = $this->getFilesForUpdate ($versionFolder);
        $list = []; //list of changes

        if (!file_exists($destFolder."/backup")) {
            mkdir($destFolder."/backup", "0755", true);
        }

        $backupFile = $destFolder."/backup/pre".str_replace("/", "_", str_replace($destFolder, "", $versionFolder)).".zip";

        $list[] = "Creating a backup file {$backupFile}";
        $zip = new ZipArchive();
        if($zip->open($backupFile, ZIPARCHIVE::CREATE) !== true) {
            $list[] = "Can't create zip file, already exists ...";
            return $list;
        }

        //add the files

        foreach ($updateFiles as $uid => $file) {
            //see if file exists in the destination folder
            $destFile = str_replace ($versionFolder, $destFolder, $file);
            $message = "";
            if (file_exists($destFile) && file_get_contents($destFile) !== "") {
                $message = "{$destFile} needs to be updated &";
            } else {
                $message = "{$destFile} needs to be created &";
            }

            $zipFileName = substr (str_replace ($versionFolder, "", $file), 1);
            $list[] = $message." ".$zipFileName." added to zip file";

            $zip->addFile( $destFile, $zipFileName);

        }

        $zip->close();
        $list[] = "Zip file created";

        if ($applyUpdates){
            foreach ($updateFiles as $uid => $file) {
                $destFile = str_replace ($versionFolder, $destFolder, $file);
                $list[] = "{$file} copied over to {$destFile}";
                file_put_contents ( $destFile,  file_get_contents ( $file ) );
            }
        }


        return $list;
    }


    function getNewFile ($filePath) {

        $html = div(["class" => "padding20 dialog", "data-show" => "true",  "data-role" => "dialog", "data-close-button" => "true", "data-overlay" => "true", "id" => "newFileDialog"],
                h3("New File"),
                p(
                    "File will be created in:", br(),
                    b("{$filePath}")
                ),
                form(["method" => "post", "data-role" => "validator", "onsubmit" => "return false;"],
                    label(["class" => "block"], "File name"),
                    input(["id" => "newFileName", "style" => "width:100%","data-validate-func" => "required", "data-validate-hint" => "Please type in a filename",  "name" => "fileName", "type" => "text"]),
                    label(["class" => "block"], "Reason"),
                    textarea(["" => "newFileReason","style" => "width:100%", "data-validate-func" => "required", "data-validate-hint" => "Reason for creating the file (used for git)", "name" => "fileReason"]),
                    div (input(["type" => "submit", "class" => "button success", "onclick" => "ajaxCode ('/cody/createFile', 'actionArea', {fileName: $('#newFileName').val(), fileReason: $('#newFileReason').val(), createPath: filePath, targetVersion: $('targetVersion').val() }); return false;"], "Create"),
                         input(["type" => "button", "class" => "button danger", "onclick" => "$('#newFileDialog').data('dialog').close();"], "Cancel"))
                ),
                span(["class" => "dialog-close-button"])

                );

        return $html;
    }

    function getRelease( $versionFolder, $destFolder ) {
        $updates = $this->runUpdate($versionFolder, $destFolder, true);
        $listUpdates = ul();
        foreach ($updates as $uid => $list) {
            $listUpdates->addContent(li($list));
        }

        $html = div(["class" => "padding20 dialog", "data-show" => "true",  "data-role" => "dialog", "data-close-button" => "true", "data-overlay" => "true", "id" => "newFileDialog"],
            h3("Updates List"),
            $listUpdates,
            span(["class" => "dialog-close-button"])
        );

        return $html;
    }


    function codeHandler ($action) {
        $html = "";
        switch ($action) {
            case "fileVersion":
                $fileNameOnly = explode ("/", Ruth::getREQUEST("fileName"));
                $fileNameOnly = $fileNameOnly[sizeof($fileNameOnly)-1];
                $release = str_replace (str_replace('\\', '/', Ruth::getDOCUMENT_ROOT()."/versions/"), "", Ruth::getREQUEST("targetVersion"));

                $sqlVersion = "select * from kim_version where file_name = '{$fileNameOnly}' and release = '{$release}' order by version_no desc";

                $versions = $this->DEB->getRows($sqlVersion);

                if (!empty($versions)) {
                    $select = select(["name" => "lastVersion", "id" => "lastVersion"]);

                    $select->addContent(option(["value" => ""], "Current Version"));
                    foreach ($versions as $vid => $version) {
                        $select->addContent(option(["value" => $version->VERSION_ID], $version->DATE_CREATED . " " . $version->USERNAME . " - " . $version->VERSION_NO));
                    }

                    $html .= label(["for" => "lastVersion"], "Previous Versions") . $select . button(["class" => "button info", "onclick" => "loadPreviousVersion()"], "Load");
                } else {
                    $html .= b("No previous versions found ...");
                }
            break;
            case "login":
                $html = $this->getPageTemplate("Login");

                $content = '<div style="width:30%;" class="login-form padding20 block-shadow">
                                        <form action="/cody/validate" method="post">
                                            <h1 class="text-light">Login to Cody</h1>
                                            <hr class="thin"/>
                                            <br />
                                            <div class="input-control text full-size" data-role="input">
                                                <label for="user_login">User:</label>
                                                <input type="text" name="user_login" id="user_login">
                                                <button class="button helper-button clear"><span class="mif-cross"></span></button>
                                            </div>
                                            <br />
                                            <br />
                                            <div class="input-control password full-size" data-role="input">
                                                <label for="user_password">Password:</label>
                                                <input type="password" name="user_password" id="user_password">
                                                <button class="button helper-button reveal"><span class="mif-looks"></span></button>
                                            </div>
                                            <br />
                                            <br />
                                            <div class="form-actions">
                                                <button type="submit" class="button primary">Login</button>
                                            </div>
                                        </form>
                                    </div>';

                $html->byId("content")->setContent($content);
            break;
            case "validate":
                $user = (new Kim())->KIM->getRow("select * from user where email = '".Ruth::getREQUEST("user_login")."'");

                if (password_verify(Ruth::getREQUEST("user_password"), $user->PASSWD)) {
                    Ruth::setSESSION("codyAuthenticated", ["loggedin" => 1, "user" => $user]);
                    Ruth::redirect("/cody");
                }
                else {
                    Ruth::setSESSION("codyAuthenticated", null);
                    Ruth::redirect("/cody/login");
                }
            break;
            case "logout":
                Ruth::setSESSION("codyAuthenticated",  null);
                Ruth::redirect("/cody/login");
            break;
            case "refreshFileExplorer":
               $html .= $this->getFileTree(Ruth::getDOCUMENT_ROOT(), "loadFileCode", true);
               $html .=  script("getFileVersion();");
            break;
            case "loadFile":
                if (!empty(Ruth::getREQUEST("versionId"))) {
                    $version = $this->DEB->getRow("select * from kim_version where version_id = ".Ruth::getREQUEST("versionId"));
                    $html .= $this->getCodeWindow("1", $version->CONTENT);
                } else {
                    $fileName = Ruth::getREQUEST("fileName");
                    if (strpos(Ruth::getREQUEST("fileName"), str_replace('\\', '/', Ruth::getDOCUMENT_ROOT() . "/versions/")) === false) {
                        $fileName = str_replace(str_replace('\\', '/', Ruth::getDOCUMENT_ROOT()), Ruth::getREQUEST("targetVersion"), $fileName);
                    }

                    if (file_exists($fileName)) {
                        $html .= $this->getCodeWindow("1", file_get_contents($fileName));
                    } else {
                        $html .= $this->getCodeWindow("1", file_get_contents(Ruth::getREQUEST("fileName")));
                    }

                    $html .= script("getFileVersion();");
                }
            break;
            case "newFile":
                $html .= $this->getNewFile(Ruth::getREQUEST("filePath"));

                break;
            case "createFile":
                //initial file
                $fileName =  Ruth::getREQUEST("createPath")."/".Ruth::getREQUEST("fileName");
                $fileName = str_replace (" ", "", $fileName);
                if (strpos($fileName,".php") == false) {
                    $fileName = $fileName.".php";
                }
                file_put_contents($fileName, ''); //dont put a place holder file on creation, this will get released anyway
                $versionFile = str_replace ( str_replace('\\', '/', Ruth::getDOCUMENT_ROOT()), Ruth::getREQUEST("targetVersion"), $fileName );
                file_put_contents($versionFile, '<'.'?'.'p'.'hp '."\n"."/**\nName : ".Ruth::getREQUEST("fileName")."\nReason:".Ruth::getREQUEST("fileReason")."\n**/");
                $html .= script("refreshFileExplorer();");
                //add it to Git?
            break;
            case "deleteFile":
                if (strpos(Ruth::getREQUEST("fileName"), str_replace('\\', '/', Ruth::getDOCUMENT_ROOT()."/versions/")) !== false) {
                    //what should we do if we find the file in the original space
                    $currentFile = str_replace(Ruth::getREQUEST("targetVersion"), str_replace('\\', '/', Ruth::getDOCUMENT_ROOT()), Ruth::getREQUEST("fileName"));
                    if (file_get_contents($currentFile) === "") {
                        unlink($currentFile);
                    }

                    $fileName = Ruth::getREQUEST("fileName");
                }  else {
                    //file should get added to a delete list for the version to delete it

                    $fileName = Ruth::getREQUEST("fileName");
                }
                unlink($fileName);
                $html .= script("refreshFileExplorer();");
            break;
            case "saveFile":
               //see if the file exists in the versioned area if there is no versioning in the name
               //save the file in the correct place

               if (strpos(Ruth::getREQUEST("fileName"), str_replace('\\', '/', Ruth::getDOCUMENT_ROOT()."/versions/")) === false) {
                   //need to make the directory
                   $fileName = Ruth::getREQUEST("fileName");

                   $fileName = str_replace ( str_replace ('\\', '/', Ruth::getDOCUMENT_ROOT()), Ruth::getREQUEST("targetVersion"), $fileName  );
                   $dirName = dirname($fileName);
                   if (!file_exists($dirName)) {
                       mkdir($dirName, "0755", true);
                   }

               }  else {
                   $fileName = Ruth::getREQUEST("fileName");
               }

               $release = str_replace (str_replace('\\', '/', Ruth::getDOCUMENT_ROOT()."/versions/"), "", Ruth::getREQUEST("targetVersion"));

               $sqlInsert = "insert into kim_version (file_name, username, date_created, content, release, version_no )
                             values(?, ?, 'now', ?, ?, ?)";

               $fileNameOnly = explode ("/", $fileName);
               $fileNameOnly = $fileNameOnly[sizeof($fileNameOnly)-1];

               $versionNo = $this->DEB->getNextId("kim_version", "version_no", "file_name = '{$fileNameOnly}'");

               $username = Ruth::getSESSION("codyAuthenticated");

               $username = $username["user"]->FIRST_NAME." ".$username["user"]->LAST_NAME;

               if (file_exists($fileName)) {
                   $this->DEB->exec($sqlInsert, $fileNameOnly, $username, file_get_contents($fileName), $release, $versionNo);
               }
               file_put_contents($fileName, Ruth::getREQUEST("fileCode"));

               $html .= script("$('#fileVersioning').html('Saved ".date("Y-m-d H:i:s")." - {$fileName}');    refreshFileExplorer();");
            break;
            case "releaseFiles":
               $html .= $this->getRelease(Ruth::getREQUEST("targetVersion"), Ruth::getDOCUMENT_ROOT());
            break;
            default:
                $html = "Unknown {$action} ".print_r (Ruth::getREQUEST(), 1);
            break;
        }

        return $html;
    }

    function codeBuilder() {
        if (!Ruth::getSESSION("codyAuthenticated")) Ruth::redirect("/cody/login");

        $html = $this->getPageTemplate("Code Builder");

        if (empty(Ruth::getREQUEST("targetVersion"))) {
            $version = "v1.0.1";
            Ruth::setREQUEST("targetVersion", str_replace ('\\', '/', Ruth::getDOCUMENT_ROOT()."/versions/v1.0.1"));
        } else {
            $version = str_replace (str_replace ('\\', '/', Ruth::getDOCUMENT_ROOT()."/versions/"), "", Ruth::getREQUEST("targetVersion"));
        }

        $content = $this->ajaxHandler("", "", "ajaxCode", "", "post", false);
        $content .= div(["class"=>"app-bar darcula"],
            a (["class"=>"app-bar-element", "href"=> "/?version={$version}", "target" => "_blank"], "Home"),
            span (["class" => "app-bar-divider"]),
            ul(["class" => "app-bar-menu"],
                li(a(["onclick" => "newFileCode()"], "New File")),
                li(a(["onclick" => "saveFileCode()"], "Save File")),
                li(a(["class" => "button danger", "onclick" => "console.log(fileName); if (fileName !== '' && confirm('Are you sure you want to delete '+fileName+'?') ) { deleteFileCode(); }"],"Delete")),
                li($this->getAppVersions()),
                li(a (["class"=> "button success", "href"=> "#", "onclick" => "releaseFiles()"], "Release")),
                li(a (["href"=> "/cody/logout"], "Logout"))

            ),

            span (["class" => "app-bar-pullbutton"])
        );
        $content .= script ("
        var fileName;
        var filePath;


        function loadPreviousVersion() {
            aFileName = fileName;
            ajaxCode ('/cody/loadFile', 'codeArea', { fileName : aFileName, versionId : $('#lastVersion').val()});
        }

        function getFileVersion() {
           aFileName = fileName;
           ajaxCode ('/cody/fileVersion', 'fileVersioning', { fileName: aFileName,  filePath: filePath, targetVersion: $('targetVersion').val()});
        }

        function newFileCode() {
            ajaxCode ('/cody/newFile', 'fileArea', {filePath: filePath});

        }

        function refreshFileExplorer() {
            ajaxCode ('/cody/refreshFileExplorer', 'fileExplorer', {filePath: filePath});
        }


        function saveFileCode() {
           aFileName = fileName;
           ajaxCode ('/cody/saveFile', 'actionArea', {fileName: aFileName, fileCode: editor.getValue(), targetVersion: $('targetVersion').val() });

        }

        function releaseFiles(){
           ajaxCode ('/cody/releaseFiles', 'actionArea', {targetVersion: $('targetVersion').val()});
        }

        function deleteFileCode() {
           aFileName = fileName;
           ajaxCode ('/cody/deleteFile', 'actionArea', {fileName: aFileName, targetVersion: $('targetVersion').val() });
        }

        function loadFileCode (aFileName, aFilePath) {
            filePath = aFilePath;
            if (aFileName !== '') {
              ajaxCode ('/cody/loadFile', 'codeArea', {fileName: aFileName});
            }
        }
        ");
        $content .= $this->getFileExplorer();
        $content .= div (["id" => "fileArea"]);
        $content .= div (["id" => "fileVersioning"], b("Click on a file on the left to see version information"));
        $content .= div (["id" => "codeArea"], $this->getCodeWindow("1", file_get_contents("index.php")));
        $content .= div (["id" => "actionArea"]); //place where the actions happen
        $html->byId("content")->setContent ($content);

        return $html;
    }

    function GetCallingMethodName(){
        $e = new Exception();
        $trace = $e->getTrace();
        //position 0 would be the line that called this function so we ignore it
        $last_call = $trace[1];
        return $last_call;
    }


    function getCodeWindow($id, $code) {
        $content = script(["src" => "/ace/ace.js"]);
        $content .= script(["src" => "/ace/mode-php.js"]);
        $content .= script(["src" => "/ace/ext-beautify.js"]);
        $content .= script(["src" => "/ace/ext-statusbar.js"]);
        $content .= script(["src" => "/ace/ext-error_marker.js"]);


        $content .=  div(["id" => "codeWindow{$id}", "style" => "min-width: 0px; max-height: 600px; min-height: 600px", "name" => "codeWindow{$id}"], htmlentities($code));
        $content .= script("

                                var editor = ace.edit('codeWindow{$id}');
                                    editor.setTheme('ace/theme/monokai');
                                    editor.getSession().setMode({path:'ace/mode/php', inline:true});

                                //supress save dialog
                                window.onkeypress = function(event) {
                                    if (event.charCode === 115 && event.ctrlKey) {
                                        event.preventDefault();

                                    }
                                };

                                editor.commands.addCommand({
                                            name: 'saveFile',
                                            bindKey: {
                                            win: 'Ctrl-S',
                                            mac: 'Command-S',
                                            sender: 'editor|cli'
                                            },
                                            exec: function(env, args, request) {
                                                saveFileCode();
                                            }
                                            });
                            ");
        return $content;
    }


    function getFileExplorer() {
        $content = div(["id" => "fileExplorer","style" => "float: left; width: 300px; overflow: auto; max-height: 600px; min-height: 600px", "title" => "File Explorer"], $this->getFileTree(Ruth::getDOCUMENT_ROOT(), "loadFileCode", true));
        return $content;
    }

    function getAppVersions() {
        $files = scandir(Ruth::getDOCUMENT_ROOT()."/versions");

        $html = select (["style" => " width: 9em;", "targetVersion" => "targetVersion", "name" => "targetVersion", "onchange" => "document.forms[0].submit();"]);

        $count = 0;
        foreach ($files as $fid => $file) {
            if ($file != "." && $file != "..") {
                $value = str_replace ('\\', '/', Ruth::getDOCUMENT_ROOT()."/versions/".$file);

                if (is_dir(Ruth::getDOCUMENT_ROOT()."/versions/".$file)) {
                    if ($value === Ruth::getREQUEST("targetVersion")) {
                        $html->AddContent(option(["value" => $value, "selected"], $file));

                    } else {
                        $html->AddContent(option(["value" => $value], $file));

                    }
                    $count++;
                }
            }

        }

        if ($count == 0) {
            $html->addContent(option(["value" => str_replace ('\\', '/', Ruth::getDOCUMENT_ROOT()."/versions/v1.0.1")], "v1.0.1"));
        }

        return form(["method" => "post", "action" => "/cody"], "Version:". $html );
    }

    function getFileTree($path="", $callBack="", $root=false) {
        $targetVersion = Ruth::getREQUEST("targetVersion");
        $content = ul(["class" => "treeview", "data-role" => "treeview"], "");
        $files = scandir($path);
        foreach ($files as $fid => $file) {
            if ($file != "." && $file != "..") {
                $fileName = str_replace ('\\', '/', $path."/".$file);
                $found = false;
                if (!empty(TINA4_EDITOR_PATHS) && $file !== "tina4.php" && $file != "restAPI.php") {
                    foreach (TINA4_EDITOR_PATHS as $id => $allowedPath) {
                        if (stripos($fileName, str_replace ('\\', '/', Ruth::getDOCUMENT_ROOT()."/".$allowedPath)) !== false) {
                            $found = true;
                            break;
                        }
                    }
                }

                if ($found) {
                    if (is_dir($path."/".$file)) {
                        $tpath = str_replace('\\', '/', $path."/".$file);

                        if ($tpath === Ruth::getREQUEST("filePath")) {
                            $content->addContent($list = li(["class" => "node", "onclick" => "{$callBack}('', '{$tpath}')"], span(["class" => "leaf"], $file), span(["class" => "node-toggle"])));
                        }  else {
                            $content->addContent($list = li(["class" => "node collapsed", "onclick" => "{$callBack}('', '{$tpath}')"], span(["class" => "leaf"], $file), span(["class" => "node-toggle"])));
                        }

                    } else {
                        $path = str_replace('\\', '/', $path);
                        $versionFileName =  str_replace ( str_replace('\\', '/', Ruth::getDOCUMENT_ROOT()), $targetVersion, $fileName  );

                        if (file_exists($versionFileName)) {
                            $content->addContent ( $list = li (a (["style" => "color:pink", "class" => "leaf", "href" => "#", "onclick" => "fileName = '{$versionFileName}';  {$callBack}('{$versionFileName}', '{$path}')"], $file )) );

                        } else {
                            $content->addContent ( $list = li (a (["class" => "leaf", "href" => "#", "onclick" => "fileName = '{$fileName}'; {$callBack}('{$fileName}', '{$path}')"], $file )) );

                        }

                    }
                    if (is_dir($path."/".$file)) {

                        $list->addContent ($this->getFileTree($path."/".$file, $callBack));
                    }
                }

            }
        }

        return $content;
    }

    function getCodeNavigator() {

        $content = div(["id" => "codeNavigator", "title" => "Navigator"], $this->getFileTree (Ruth::getDOCUMENT_ROOT()));
        $content .= script("$(function() {  $('#codeNavigator').dialog({position:{my: '0 0', at: 'left bottom', of: $('#fileExplorer') }, height:400 }); } );");
        return $content;
    }

    function getParseScript($name) {
        return "
        //function to run our own scripts
        function parse{$name}Script(_source) {
                    var source = _source;
                    var scripts = new Array();
                    // Strip out tags
                    while(source.toLowerCase().indexOf('<'+'s'+'c'+'r'+'i'+'p'+'t') > -1 || source.toLowerCase().indexOf('</'+'s'+'c'+'r'+'i'+'p'+'t') > -1) {
                        var s = source.toLowerCase().indexOf('<'+'s'+'c'+'r'+'i'+'p'+'t');
                        var s_e = source.indexOf('>', s);
                        var e = source.toLowerCase().indexOf('</'+'s'+'c'+'r'+'i'+'p'+'t', s);
                        var e_e = source.indexOf('>', e);
                        scripts.push(source.substring(s_e+1, e));
                        source = source.substring(0, s) + source.substring(e_e+1);
                    }

                    // Loop through every script collected and eval it
                    for(var i=0; i < scripts.length; i++) {
                    try {
                        if (scripts[i] != '') {
                            try  {          //IE
                                execScript(scripts[i]);
                                    }
                            catch(ex) {
                                window.eval(scripts[i]);
                                    }

                        }
                                }
                    catch(e) {
                        if (e instanceof SyntaxError) console.log (e.message+' - '+scripts[i]);
                                }
                }
                            return source;
            }";
    }


    /**
     * Ajax Router for directing form inputs and general things from an html segment.
     * @param string $responseTarget This is the targetted HTML Tag or element
     * @return string The script needed to run the router
     */
    function ajaxRouter ($responseTarget="", $name="callRouter", $defaultMethod="post") {
        $rand = rand(1000,9999);
        $randName = "formSpan".$rand;

        $html = span(["id" => $randName]);
        $html .= script ("
            serialize = function(obj) {
                var str = [];
                for(var p in obj)
                  if (obj.hasOwnProperty(p)) {
                    str.push(encodeURIComponent(p) + '=' + encodeURIComponent(obj[p]));
                  }
                return str.join('-!-');
            }

            ".$this->getParseScript($rand)."

            var span{$rand} = document.getElementById('{$randName}');
            if (span{$rand} !== null) {

                var form{$rand} = span{$rand}.parentElement;
                var response{$rand} = document.getElementById('{$responseTarget}');

                if (form{$rand} !== undefined && form{$rand} != null && form{$rand}.nodeName == 'FORM') {
                    if (response{$rand} !== undefined && response{$rand} != null) {
                       function {$name}(targetRoute,responseTarget,defaultMethod) {

                            if (responseTarget === undefined) responseTarget = '{$responseTarget}';
                            if (defaultMethod === undefined) defaultMethod = '{$defaultMethod}';

                            //populate the form into a json element
                            var e = form{$rand}.elements;
                            var js{$rand} = {};

                            for ( var elem, i = 0; ( elem = e[i] ); i++ ) {

                                eName = (elem.id !== undefined && elem.id !== '') ? elem.id : ((elem.name !== undefined) ? elem.name : null) ;
                                if (eName != null ) {
                                    if (elem.type === 'radio' || elem.type === 'checkbox') {
                                      if (elem.checked) {
                                        js{$rand}[eName] = (elem.value !== '') ? elem.value : 1 ;
                                      }
                                        else if (elem.type === 'checkbox') {
                                                js{$rand}[eName] = '0';
                                            }
                                    }
                                      else {

                                      if (elem.type === 'file') {

                                        var fileData = elem.files[0];
                                        if (fileData !== undefined) {
                                          formData.append (eName, fileData, fileData.name);
                                        }
                                      } else {

                                        js{$rand}[eName] = elem.value;

                                      }

                                    }
                                } else {
                                    console.log('Couldn\'t send form element via AJAX', elem);
                                }
                            }

                            form{$rand}Data = new FormData();
                            form{$rand}Data.append ('formData', serialize(js{$rand}));

                            try {
                                xhr = new XMLHttpRequest();
                                xhr.open (defaultMethod, targetRoute);

                                xhr.onload = function () {
                                    if (xhr.status == 200) {
                                        result = JSON.parse(xhr.responseText);

                                        targetElement = document.getElementById (responseTarget);

                                        if (targetElement != null && targetElement.tagName !== undefined && responseTarget != null) {
                                            tagTarget = targetElement.tagName.toUpperCase();

                                            if (tagTarget === 'INPUT' || tagTarget === 'TEXTAREA') {
                                                    console.log ('{$name} - Target is input:'+targetElement.id);
                                                    targetElement.value = result.responseMessage;
                                            }  else {
                                                    console.log ('{$name} - Target is HTML element'+targetElement.id);
                                                    targetElement.innerHTML = result.responseMessage;
                                            }

                                            if (result.responseAction == null) {
                                                parse{$rand}Script(result.responseMessage);
                                            } else {

                                                parse{$rand}Script(result.responseAction.responseScript);
                                                if (result.responseAction.redirectPath !== undefined && result.responseAction.redirectPath != null && result.responseAction.redirectPath !== '') {
                                                    location.href = result.responseAction.redirectPath;
                                                }
                                            }

                                        } else if(typeof window[responseTarget] === 'function'){//if target is a function pass it the results
                                            window[newTarget](result);
                                        }
                                          else {
                                            console.log ('Tina4 - parse script');

                                            if (result.responseAction == null) {
                                                parse{$rand}Script(result.responseMessage);
                                            } else {
                                                parse{$rand}Script(result.responseAction.responseScript);
                                                if (result.responseAction.redirectPath !== undefined && result.responseAction.redirectPath != null) {
                                                    location.href = result.responseAction.redirectPath;
                                                }
                                            }
                                        }
                                    } else {
                                        console.log('AjaxRouterResponse could not be gotten at '+targetRoute);
                                    }
                                }

                                xhr.send(form{$rand}Data);
                                delete form{$rand}Data;

                            } catch (e) {
                                console.log ('Tina4 ajaxRouter Error: ', e);
                            }
                       }

                    } else {
                        window.alert('HTML element with {$responseTarget} is not present on this page!');
                    }
                }  else {
                    window.alert('{{Cody:ajaxRouter}} needs to be just within a form tag!');
                }
            } else {
              window.alert('{{Cody:ajaxRouter}} could not be initialized!');
            }
        ");


        return $html;
    }



    /**
     * A function which encapulates the passing of form infomation with or without ids to a route and targeting the response to an html element.
     *
     * @param string $route - Path to the route which must be called - default is post request
     * @param string $target - target of the HTML element which receives the ajax response, if null then nothing will be passed back
     * @param string $name - Name you want the AJAX method to be called
     * @param string $fixedValues - A json string of values you want to be passed each time to the
     * @param string $method - POST or GET
     * @param bool $runNow - false or true - immediately execute the AJAX call as well
     * @return string - Code for the AJAX method to run
     */
    function ajaxHandler($route = "/test_ajax", $target = "", $name = "callAjax", $fixedValues = "", $method = "post", $runNow = false)
    {
        if ($fixedValues === "") $fixedValues = "{}";
        $html = script("
            serialize = function(obj) {
                var str = [];
                for(var p in obj)
                  if (obj.hasOwnProperty(p)) {
                    str.push(encodeURIComponent(p) + '=' + encodeURIComponent(obj[p]));
                  }
                return str.join('-!-');
              }    

            // function to check if object or variable/array/string empty : like php empty function
            function empty(mixed_var) {
                var undef, key, i, len;
                var emptyValues = [undef, null, false, 0, '', '0'];
                for (i = 0, len = emptyValues.length; i < len; i++) {
                    if (mixed_var === emptyValues[i]) {
                      return true;
                    }
                }
                if (typeof mixed_var === 'object') {
                    for (key in mixed_var) {
                      return false;
                    }
                    return true;
                }
                return false;
            }
            
            ".$this->getParseScript($name)."

            // newRoute = 'path', newTarget = 'div,input', extraParam = 'JSON', ignoreRoute = false, newMethod = 'GET/POST'
            function {$name}(newRoute, newTarget, extraParam, newMethod, ignoreRoute) {

                if (newRoute === undefined) newRoute = '{$route}';
                if (newTarget === undefined) newTarget = '{$target}';
                if (typeof newMethod === \"undefined\") newMethod = '{$method}';
                if (ignoreRoute === undefined) ignoreRoute = true;

                if ((extraParam !== undefined || extraParam === null) && extraParam.length != 0) {
                  json{$name}Data = extraParam === null ? [] : extraParam;
                }
                  else {
                  json{$name}Data = JSON.parse('{$fixedValues}');
                }
                
                form{$name}Data = new FormData();
                
                targetElement = document.getElementById (newTarget);
                
                //add the json information as an object key value
                if (document.forms) {
                    for (iform = 0; iform < document.forms.length; iform++) {
                        var form = document.forms[iform];
                        var e = form.elements;
                        
                        for ( var elem, i = 0; ( elem = e[i] ); i++ ) {
                            eName = (elem.id !== undefined && elem.id !== '') ? elem.id : ((elem.name !== undefined) ? elem.name : null) ;

                            if (eName != null ) {
                                if (elem.type === 'radio' || elem.type === 'checkbox') {
                                  if (elem.checked) {
                                    json{$name}Data[eName] = (elem.value !== '') ? elem.value : 1 ;
                                  }
                                    else if (elem.type === 'checkbox') {
                                            json{$name}Data[eName] = '0';
                                        }
                                }
                                  else {
                                  
                                  if (elem.type === 'file') {
                                    
                                    var file{$name}Data = elem.files[0];
                                    if (file{$name}Data !== undefined) {
                                      form{$name}Data.append (eName, file{$name}Data, file{$name}Data.name);
                                    }
                                  } else {

                                    json{$name}Data[eName] = elem.value;
                         
                                  }
                                  
                                }
                            } else {
                                console.log('Couldn\'t send form element via AJAX', elem);
                            }
                        }
                    }
                }

                //try some normal AJAX stuff
                try {

                    form{$name}Data.append ('formData', serialize(json{$name}Data));

                    xhr = new XMLHttpRequest();
                    xhr.open (newMethod, newRoute);

                    xhr.onload = function () {
                        if (xhr.status == 200) {
                            result = xhr.responseText;
                            
                            if (targetElement != null && targetElement.tagName !== undefined && newTarget != null) {
                               tagTarget = targetElement.tagName.toUpperCase();                              
                               
                               if (tagTarget === 'INPUT' || tagTarget === 'TEXTAREA') {
                                    console.log ('Tina4 - Target is input:'+targetElement.id);
                                    targetElement.value = result;
                               }  else {
                                    console.log ('Tina4 - Target is HTML element'+targetElement.id);
                                    if (newRoute.indexOf('/cody/') == -1 && ignoreRoute === false) {
                                      window.history.pushState({'html': result,'pageTitle': 'Call '+newRoute},'', newRoute);
                                    }
                                    targetElement.innerHTML = result;
                               }

                                parse{$name}Script(result);
                            } else if(typeof window[newTarget] === 'function'){//if target is a function pass it the results
                                window[newTarget](result);
                            }
                              else {
                              console.log ('Tina4 - parse script');
                              parse{$name}Script(result);
                            }
                        }
                          else {
                          console.log('Failed to get route'+newRoute);   
                        }

                    }

                    xhr.send(form{$name}Data);
                    delete form{$name}Data;
                } 
                catch (e) {
                    console.log ('Tina4 ajaxHandler Error: ', e);
                }
            

            }

        ");

        if ($runNow) {
            $html .= script($name . "();");
        }

        return $html;
    }

    function bootStrapMenuTree(){
        $html = "";



        return $html;

    }

    function bootStrapLookup($name, $caption, $elements, $value="", $onclick= "", $readonly = "",  $colWidth="col-md-12", $nochoose = false) {
        $html = div(["class" => "form-group ".$colWidth], label(["for" => $name], $caption), $this->select($name, $caption, "array", $elements, $value, $onclick, $name, $readonly, $nochoose));
        return $html;
    }

    function IsDate( $Str ){
        $Stamp = strtotime( $Str );
        if(!$Stamp){
            return $Stamp;
        }
        $Month = date( 'm', $Stamp );
        $Day   = date( 'd', $Stamp );
        $Year  = date( 'Y', $Stamp );
        return checkdate( $Month, $Day, $Year );
    }

}