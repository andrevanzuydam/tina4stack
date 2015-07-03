<?php

/**
 * Description of Cody
 *
 * Cody is a CRUDL generation tool to make coding in Tina4 stack easy and fun
 *
 * @author Andre van Zuydam <andre@xineoh.com>
 */
require_once "Shape.php";

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
            return button (["id" => "button", "onclick" => $onclick, "class" => $style], $caption);
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
            
            if ($action === "insert") {
                $record = (object) [$keyName => ""];
            }            
            $sql = "select * from {$tableInfo->table} where {$tableInfo->primarykey} = '".$record->$keyName."'";
        }
          else {
           $keyName = key($record);             
           $sql = "select * from ({$sql}) t where {$keyName} = '".$record->$keyName."'";
        }        
                
        $closeAction = "$('#{$name}Target').html('');";

        $ckeditorCheck = " if (CKEDITOR !== undefined) {  } ";
        switch ($action) {
            case "insert":
                $customButtons = $this->bootStrapButton("btnInsert", "OK", " {$ckeditorCheck} $('#form{$name}').submit(); if ( $('#form{$name}').validate().errorList.length == 0 ) {  call{$name}Ajax('/cody/form/insert/post','{$name}Target', {object : a{$name}object, record: null, db: '{$db}' }) } ", "btn btn-success", "", true);
                $customButtons .= $this->bootStrapButton("btnInsert", "Cancel", $closeAction, "btn btn-warning", "", true);                
            break;    
            case "update":
                $customButtons = $this->bootStrapButton("btnUpdate", "OK", "  {$ckeditorCheck} $('#form{$name}').submit(); if ( $('#form{$name}').validate().errorList.length == 0 ) {  call{$name}Ajax('/cody/form/update/post','{$name}Target', {object : a{$name}object, record: '".  urlencode(json_encode($record) )."', db: '{$db}' }) } ", "btn btn-success", "", true);
                $customButtons .= $this->bootStrapButton("btnUpdate", "Cancel", $closeAction, "btn btn-warning", "", true);                
            break;    
            default:
                $customButtons = $this->bootStrapButton("btnView", "Close", $closeAction, "btn btn-success", "", true);                
            break;
        }
                
                
        if ($action == "insert") $action = "Add";                    
        if ($action == "update") $action = "Edit";   
        
        //check if a template exists....
        $template = $this->getTemplate($name, "form");
        if (!empty($template)) {
           $record = $this->DEB->getRow($sql); 
           //parse the template
           $template = (new Kim())->parseTemplate ($template, $record);
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
                return $this->bootStrapModal(ucwords($action)." ". $toolBar->caption,  $this->bootStrapForm($sql, $hideColumns, "", $customFields, $submitAction="", "form{$name}" ), $customButtons, $closeAction);
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
        
        Ruth::addRoute(RUTH_POST, "/cody/form/{action}" ,
                    function ($action) {
                        $object = json_decode(rawurldecode(Ruth::getREQUEST("object")));
                        $record = json_decode(rawurldecode(Ruth::getREQUEST("record")));    
                     
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
                                $keyName = strtoupper($tableInfo->primarykey);             
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
                            
                            $DEB->delete($tableName, [$keyName => $record->$keyName]);
                                                   
                            if (!empty(ONDELETE)) {
                                                $params = ["action" => $action, "table" => $tableName, $keyName => $record->$keyName, "session" => Ruth::getSESSION(), "request" => Ruth::getREQUEST()];
                                                @call_user_func_array(ONINSERT, $params);
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
                            if ($field["type"] === "DATETIME" || $field["type"] === "DATE") {
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
                        
                        if (!empty($object[6])) {
                            $tableInfo = $object[6];
                            $keyName = strtoupper($tableInfo->primarykey);             
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

                                if (!empty(ONINSERT)) {
                                    $params = ["action" => $action, "table" => $tableName, $keyName => $_REQUEST["{$action}{$name}"], "session" => Ruth::getSESSION(), "request" => Ruth::getREQUEST()];
                                    @call_user_func_array(ONINSERT, $params);
                                }    
                            break;    
                            case "update":

                                $sqlUpdate = $DEB->getUpdateSQL("txt", $tableName, $keyName, $record->$keyName, "{$action}{$name}", $passwordFields, $dateFields, true);

                                if ( $sqlUpdate ) {
                                     echo $this->bootStrapAlert("success", $caption="Success", "Record was updated successfully"); 
                                     echo script ('$table'.$name.'.bootstrapTable("refresh");' );
                                }     
                                    else {
                                        echo $this->bootStrapAlert("danger", $caption="Failure", "Record could not be updated");              
                                    }

                                if (!empty(ONUPDATE)) {
                                    $params = ["action" => $action, "table" => $tableName, $keyName => $record->$keyName, "session" => Ruth::getSESSION(), "request" => Ruth::getREQUEST()];
                                    @call_user_func_array(ONUPDATE, $params);
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
            if (!is_object ($buttons)) {
                $tempButtons = explode (",", strtolower($buttons));
                
                if (is_array($tempButtons)) {
                    $buttons = script ("var a{recordid}record = '{record}';");
                    foreach ($tempButtons as $bid => $button) {
                        switch ($button) {
                            case "insert":
                                $buttons .= (new Cody())->bootStrapButton("btnInsert", "Add", "call{$name}Ajax('/cody/form/insert','{$name}Target', {object : a{$name}object, record: a{recordid}record, db: '{$db}' })", "btn btn-success", "", true);
                            break;
                            case "update":
                                $buttons .= (new Cody())->bootStrapButton("btnEdit", "Edit", "call{$name}Ajax('/cody/form/update','{$name}Target', {object : a{$name}object, record: a{recordid}record, db: '{$db}' })", "btn btn-primary", "", true);
                            break;
                            case "delete":
                                $buttons .= (new Cody())->bootStrapButton("btnDelete", "Del", "if (confirm('Are you sure you want to delete this record ?')) { call{$name}Ajax('/cody/form/delete','{$name}Target', {object : a{$name}object, record: a{recordid}record, db: '{$db}' }) }", "btn btn-danger", "", true);
                            break;
                            case "view":
                                $buttons .= (new Cody())->bootStrapButton("btnInsert", "View", "call{$name}Ajax('/cody/form/view','{$name}Target', {object : a{$name}object, record: a{recordid}record, db: '{$db}' })", "btn btn-warning", "", true);
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
            
            
            $hideColumns = explode(",", strtoupper($hideColumns));

            $DEB->getRow("select first 1 * from ({$sql}) t");
            $fieldInfo = $DEB->fieldinfo;

            $filter = "";
            if (!empty($search)) {
                $filter = [];
                foreach ($fieldInfo as $cid => $field) {
                    if ($field["type"] === "DATE") {
                        $filter[] = "cast(\"{$field["alias"]}\" as varchar(20)) like '%" . strtoupper($search) . "%'";
                    } else
                    if ($field["type"] === "VARCHAR") {
                        $filter[] = "upper({$field["alias"]}) like '" . strtoupper($search) . "%'";
                    } else {
                        if (is_numeric($search)) {
                            $filter[] = "\"{$field["alias"]}\" = '{$search}'";
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

            foreach ($records as $rid => $record) {
                $row = null;
                $rowButtons = $buttons."";
                
                
                foreach ($fieldInfo as $fid => $field) {

                    if ($fid == 0) {
                        $field["align"] = "left";
                    }


                    if (!in_array(strtoupper($field["name"]), $hideColumns)) {

                        $fieldName = strtoupper($field["name"]);
                        $fid = strtoupper($field["name"]);
                        
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
                                        $row[$field["name"]] = "" . div(["class" => "text-" . $field["align"] . " " . $extraclass], "Unknown Lookup");  
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
                        
                        $rowButtons = str_ireplace("{" . $field["name"] . "}", $record[strtoupper($field["name"])], $rowButtons."");
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
     * @param String $class
     * @param Boolean $paginate
     * @param Boolean $searchable
     * @param Boolean $checked
     * @param String $checkPostURL
     * @return type
     */
    function bootStrapTable($sql = "select * from user_detail", $buttons = "", $hideColumns = "", $toolbar = "My Grid", $customFields = null, $name = "grid", $tableInfo="", $formHideFields="", $class = "table table-striped",$rowLimit = 10, $paginate = true, $searchable = true, $checked = false, $selected_page = 1, $checkedPostURL = "", $checkSingleSelect = true, $event = "", $mobiletooltip = "") {
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
            "data-height" => "400",        
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


        $data = @$DEB->getRow("select first 1 * from ({$sql}) t ");
            
        
        $fieldInfo = @$DEB->fieldinfo;

        if (empty($fieldInfo)) {
            die("Perhaps the SQL for this query is broken {$sql} or the table does not exist, have you specified the correct database in your Cody initialization, Try running migrations with maggy");
        }
        
        $header = "";
        if ($checked) {
            $header .= th(["data-field" => "checked" . $name . "[]", "class" => "text-left", "data-checkbox" => "true"], "");
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
        
        $insertButton = $this->bootStrapButton("btnInsert", "Add", "call{$name}Ajax('/cody/form/insert','{$name}Target', {object : a{$name}object, record: null, db: '{$DEB->tag}' })", "btn btn-success pull-left", "", true);
        
        if (empty($toolbar["buttons"])) {
            $toolbar["buttons"] = "";
        }
        
        $toolbar["buttons"] = $insertButton.$toolbar["buttons"];
        
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

        

        $html = $tableHeading . div(["class" => "table-responsive"], div(["class" => "table-toolbar clearfix"], div(["class" => "toolbar-buttons"], $toolbarButtons) . div(["class" => "toolbar-filters"], $toolbarFilters)) . table($options, $header, tbody()));
        
        $html .= script('
                    var a'.$name.'object = "' . $object . '";
                    var $table' . $name . ' =  $("#' . $name . '").bootstrapTable({search : false, url : "/cody/data/ajax/' . $this->DEB->tag . '",
                                                                                method : "post",
                                                                                onCheck: function (row) {
                                                                                            eventType = \'check\';
                                                                                           ' . $event . '
                                                                                },
                                                                                onUnCheck: function (row) {
                                                                                           eventType = \'uncheck\';
                                                                                           ' . $event . '
                                                                                },
                                                                                queryParams: function (p) {  return {object: a'.$name.'object, limit: p.limit, offset :p.offset, order: p.order, search : $("#search' . $name . '").val(), sort: p.sort }

                                                                                } });
                   $search = $("#search' . $name . '");
                    var timeoutId = null;
                    $search.off("keyup").on("keyup", function (event) {
                        clearTimeout(timeoutId);
                        timeoutId = setTimeout(function () {

                          $table' . $name . '.bootstrapTable("refresh", {pageNumber: 1});

                        }, 1000);
                    });

                  ');

        if ($checked) {
            $html = form(["method" => "post", "action" => $checkedPostURL, "enctype" => "multipart/form-data"], $html);
        }
        
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
    function bootStrapForm($sql = "", $hideColumns = "", $custombuttons = null, $customFields = null, $submitAction = "", $formId = "formId") {
        $fieldinfo = $this->getFieldInfo($sql);

        $hideColumns = explode(",", strtoupper($hideColumns));

        $record = @$this->DEB->getRow($sql, 0, DEB_ARRAY);
        $html = "";

        $validation = [];
        $messages = [];

        foreach ($fieldinfo as $fid => $field) {

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
                
                
                if (isset($customFields[strtoupper($field["name"])])) {
                    $customField = $customFields[strtoupper($field["name"])];
                    if (is_array($customField)) {
                        $customField = (object) $customField;
                    }
                    
                     if (!empty($customField->defaultValue) && empty($record[$fid])) {
                                $record[$fid] = $customField->defaultValue;
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
                                eval ('$callClass = new '.$call[0].'();');
                                $call[0] = $callClass;
                            } else {
                                $call = $customField->call;
                            }
                            
                            $input = div(call_user_func($call, $record[$fid]));  
                        break;    
                        case "password":
                            $input = input(["class" => "form-control", "type" => "password", "placeholder" => ucwords(str_replace("_", " ", strtolower($field["alias"]))), "name" => "txt" . strtoupper($field["name"]), "id" => "txt" . strtoupper($field["name"])], "");
                            break;
                        case "hidden":
                            $input = input(["class" => "form-control hidden", "type" => "hidden", "placeholder" => ucwords(str_replace("_", " ", strtolower($field["alias"]))), "name" => "txt" . strtoupper($field["name"]), "id" => "txt" . strtoupper($field["name"])], $record[$fid]);
                        break;
                        case "readonly":
                            $input = input(["class" => "form-control readonly", "type" => "text", "readonly", "placeholder" => ucwords(str_replace("_", " ", strtolower($field["alias"]))), "name" => "txt" . strtoupper($field["name"]), "id" => "txt" . strtoupper($field["name"])], $record[$fid]);
                        break;
                        case "date":
                            $input = input(["class" => "form-control", "type" => "text", "placeholder" => ucwords(str_replace("_", " ", strtolower($field["alias"]))), "name" => "txt" . strtoupper($field["name"]), "id" => "txt" . strtoupper($field["name"])], $record[$fid]);
                            $input .= script("$('#" . $field["name"] . "').datepicker({format: '" . strtolower($this->DEB->outputdateformat) . "'}).on('changeDate', function(ev) { console.log(ev); " . $customField["event"] . " } );");
                            break;
                        case "toggle":
                            $checked = null;
                            $input = input(["class" => "form-control", "type" => "checkbox", "data-toggle" => "toggle", "data-on" => "Yes", "data-off" => "No", "placeholder" => ucwords(str_replace("_", " ", strtolower($field["alias"]))), "name" => "txt" . strtoupper($field["name"]), "id" => "txt" . strtoupper($field["name"])], $record[$fid]);
                            if ($record[$fid] == 1) {
                                $input->addAttribute("checked", "checked");
                            }
                            break;

                        case "radioYesNo":

                            if ($record[$fid] == 1) {
                                $checkedYes = "checked";
                                $checkedNo = "";
                            } else {
                                $checkedYes = "";
                                $checkedNo = "checked";
                            }

                            $inputNo = label(["class" => "radio-inline"], input(["type" => "radio", "name" => "txt" . strtoupper($field["name"]), "id" => "txt" . strtoupper($field["name"]), "value" => "0", $checkedNo]), "No"
                            );
                            $inputYes = label(["class" => "radio-inline"], input(["type" => "radio", "name" => "txt" . strtoupper($field["name"]), "id" => "txt" . strtoupper($field["name"]), "value" => "1", $checkedYes]), "Yes"
                            );

                            $input = br() . $inputNo . $inputYes;

                            break;

                        case "checkbox":
                            if ($record[$fid] == 1) {
                                $checked = "checked";
                            } else {
                                $checked = "";
                            }

                            $input = br() . label(["class" => "checkbox-inline"], input(["type" => "checkbox", "name" => "txt" . strtoupper($field["name"]), "id" => "txt" . strtoupper($field["name"]), "value" => "1", $checked]), $customField->checkCaption );
                        break;
                        case "image":
                            $input = img(["class" => "thumbnail", "style" => "height: 160px; width: 160px", "src" => $this->DEB->encodeImage($record[$fid], "/imagestore", "160x160"), "alt" => ucwords(str_replace("_", " ", strtolower($field["alias"])))]);
                            $input .= input(["type" => "hidden", "name" => "MAX_FILE_SIZE"], "4194304");
                            $input .= input(["class" => "btn btn-primary", "type" => "file", "accept" => "image/*", "name" => "txt" . strtoupper($field["name"]), "onclick" => $customField->event], ucwords(str_replace("_", " ", strtolower($field["alias"]))));
                            break;
                        case "file":
                            $input = input(["type" => "hidden", "name" => "MAX_FILE_SIZE"], "4194304");
                            $input .= input(["class" => "btn btn-primary", "type" => "file", "accept" => "image/*", "name" => "txt" . strtoupper($field["name"]), "onclick" => $customField->event], ucwords(str_replace("_", " ", strtolower($field["alias"]))));
                        break;
                        case "text":
                            $input = input(["class" => "form-control", "type" => "text", "placeholder" => ucwords(str_replace("_", " ", strtolower($field["alias"]))), "name" => "txt" . strtoupper($field["name"]), "id" => "txt" . strtoupper($field["name"])], $record[$fid]);
                        break;
                        case "readonly":
                            $input = input(["class" => "form-control", "type" => "text", "readonly" => "readonly", "placeholder" => ucwords(str_replace("_", " ", strtolower($field["alias"]))), "name" => "txt" . strtoupper($field["name"]), "id" => "txt" . strtoupper($field["name"])], $record[$fid]);
                        break;
                        case "textarea":
                           
                            $input = textarea(["class" => "form-control", "style" => $customField->style, "rows" => "5", "placeholder" => ucwords(str_replace("_", " ", strtolower($field["alias"]))), "name" => "txt" . strtoupper($field["name"]), "id" => "txt" . strtoupper($field["name"])], $record[$fid]);
                            break;
                        case "lookup":
                            if (!isset($customField->event))
                                $customField->event = "";
                            if (!isset($customField->readonly))
                                $customField->readonly = false;
                            $input = $this->select("txt" . strtoupper($field["name"]), ucwords(str_replace("_", " ", strtolower($field["alias"]))), "array", $customField->list, $record[$fid], $customField->event, "txt" . strtoupper($field["name"]), $customField->readonly);
                            break;
                       
                        default:
                            $input = div($record[$fid]);
                            break;
                    }

                    if (!empty($customField->description)) {
                        $input .= i(["class" => "field-optional alert-success"], ($customField->description));
                    }
                } else { //generic form
                    $validation[] = "txt" . $field["name"] . ":{required: false}";
                    $input = input(["class" => "form-control", "type" => "text", "placeholder" => ucwords(str_replace("_", " ", strtolower($field["alias"]))), "name" => "txt" . strtoupper($field["name"]), "id" => "txt" . strtoupper($field["name"])], $record[$fid]);
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
                            if (!empty($customField->help)) {
                                $html .= div(["class" => "form-group", "id" => "form-group" . $field["name"]], label(["id" => "label" . $field["name"], "for" => "txt" . strtoupper($field["name"])], ucwords(str_replace("_", " ", strtolower($field["alias"])))), span(["id" => "help" . $field["name"], "class" => "icon-x-info info-icon", "data-toggle" => "tooltip", "data-placement" => "right", "title" => $customField["help"]]), $input);
                                $html .= script("$('#help{$field["name"]}').tooltip({ trigger: 'hover' });");
                            } else {
                                $html .= div(["class" => "form-group", "id" => "form-group" . $field["name"]], label(["id" => "label" . $field["name"], "for" => "txt" . strtoupper($field["name"])], ucwords(str_replace("_", " ", strtolower($field["alias"])))), $input);
                            }
                        break;
                    }
                } else {
                    $html .= div(["class" => "form-group", "id" => "form-group" . $field["name"]], label(["id" => "label" . $field["name"], "for" => $field["name"]], ucwords(str_replace("_", " ", strtolower($field["alias"])))), $input);
                }
            }
        }

        $html = form(["method" => "post", "action" => $submitAction, "onsubmit" => "return false", "enctype" => "multipart/form-data", "id" => $formId], $html, $custombuttons, $this->validateForm(join($validation, ","), join($messages, ","), $formId));

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

    function bootStrapModal($header, $body, $footer, $closeCode = "", $icon = "", $modalsize = "") {
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

    
    function codeHandler ($action) {
        $html = "";
        switch ($action) {
           case "loadFile":
             $html .= $this->getCodeWindow ("1", file_get_contents(Ruth::getREQUEST("fileName")));
        
               
           break;    
           default:
               $html = "Unknown {$action} ".print_r (Ruth::getREQUEST(), 1);
           break;    
        }
               
        return $html;
    }
    
    function codeBuilder() {
        $links [] = script(["src" => "https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"]);
        $links [] = script(["src" => "https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.2/jquery-ui.min.js"]);
        $links [] = alink(["rel" => "stylesheet", "href" => "https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.2/themes/smoothness/jquery-ui.css"]);
        
        $content = $this->ajaxHandler("", "", "ajaxCode", "", "post", false);
        $content .= script ("
        function loadFileCode (aFileName) {
            ajaxCode ('/cody/loadFile', 'codeArea', {fileName: aFileName});
        }
        ");
        $content .= $this->getFileExplorer();
        $content .= div (["id" => "codeArea"], $this->getCodeWindow("1", file_get_contents("index.php")));
        
        $html = shape(doctype(), html(
                        head(meta(["charset" => "UTF-8"]), title("Code- Online Developer Editor"), $links), 
                        
                        body(
                                $content
                        )
                )
        );

        return $html;
    }

    function getCodeWindow($id, $code) {
        $content = script(["src" => "https://cdnjs.cloudflare.com/ajax/libs/ace/1.1.9/ace.js"]);
        $content .= script(["src" => "https://cdnjs.cloudflare.com/ajax/libs/ace/1.1.9/mode-php.js"]);
        
        $content .=  div(["id" => "codeWindow{$id}", "style" => "min-width: 0px; max-height: 600px; min-height: 600px", "name" => "codeWindow{$id}"], htmlentities($code));
        $content .= script("$(function() {
                                var editor = ace.edit('codeWindow{$id}');
                                    editor.getSession().setMode('ace/mode/php');
                             } );
                            ");
        return $content;
    }

    function getFileExplorer() {
        $content = div(["id" => "fileExplorer","style" => "float: left; width: 300px; overflow: auto; max-height: 600px; min-height: 600px", "title" => "File Explorer"], $this->getFileTree(Ruth::getDOCUMENT_ROOT(), "loadFileCode", true));
        return $content;
    }

    
    function getFileTree($path="", $callBack="", $root=false) {
        $content = ul(["class" => "tree"], "");
        $files = scandir($path);
        foreach ($files as $fid => $file) {
            if ($file != "." && $file != "..") {
                $fileName = str_replace ('\\', '/', $path."/".$file);
                if (is_dir($path."/".$file)) {
                  $content->addContent ( $list = li (a (["href" => "#"], $file )) );
                } else {
                  $content->addContent ( $list = li (a (["href" => "#", "onclick" => "{$callBack}('{$fileName}')"], $file )) );
                }
                if (is_dir($path."/".$file)) {
                    $list->addContent ($this->getFileTree($path."/".$file, $callBack));
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
                return str.join('&');
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
            
            //function for minifiedjs to run scripts
            function parse{$name}Script(_source) {
                    var source = _source;
                    var scripts = new Array();

                    // Strip out tags
                    while(source.toLowerCase().indexOf('<script') > -1 || source.toLowerCase().indexOf('</script') > -1) {
                        var s = source.toLowerCase().indexOf('<script');
                        var s_e = source.indexOf('>', s);
                        var e = source.toLowerCase().indexOf('</script', s);
                        var e_e = source.indexOf('>', e);
                        scripts.push(source.substring(s_e+1, e));
                        source = source.substring(0, s) + source.substring(e_e+1);
                    }

                    // Loop through every script collected and eval it
                    for(var i=0; i<scripts.length; i++) {
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
            }

            function {$name}(newRoute, newTarget, extraParam, newMethod) {
                
                if (newRoute === undefined) newRoute = '{$route}';
                if (newTarget === undefined) newTarget = '{$target}';
                if (typeof newMethod === \"undefined\") newMethod = '{$method}';

                if (extraParam !== undefined || extraParam === null) {
                  jsonData = extraParam === null ? [] : extraParam;
                }
                  else {
                  jsonData = JSON.parse('{$fixedValues}');
                }
                
                var formData = new FormData();
                
                targetElement = $('#'+newTarget);
                //add the json information as an object key value
                if (document.forms) {
                    for (iform = 0; iform < document.forms.length; iform++) {
                        var form = document.forms[iform];
                        var e = form.elements;
                        
                        for ( var elem, i = 0; ( elem = e[i] ); i++ ) {

                            if (elem.id !== undefined && elem.id !== '') {
                                if (elem.type === 'radio' || elem.type === 'checkbox') {
                                  if (elem.checked) {
                                    jsonData[elem.id] = (elem.value !== '') ? elem.value : 1 ;
                                  }
                                    else if (elem.type === 'checkbox') {
                                            jsonData[elem.id] = '0';
                                        }
                                }
                                  else {
                                  
                                  if (elem.type === 'file') {
                                    
                                    var fileData = elem.files[0];
                                    if (fileData !== undefined) {
                                      formData.append (elem.id, fileData, fileData.name);
                                    }
                                  } else {

                                    jsonData[elem.id] = elem.value;
                         
                                  }
                                  
                                }
                            }
                              else {
                              if (elem.name !== undefined) {
                                    if (elem.type === 'radio' || elem.type === 'checkbox') {
                                        if (elem.checked) {
                                            if (elem.name.indexOf('[') == -1 && elem.name.indexOf(']') == -1) {
                                              jsonData[elem.name] = (elem.value !== '') ? elem.value : 1 ;
                                            }
                                              else {
                                              if (jsonData[elem.name] === undefined) jsonData[elem.name] = [];
                                              jsonData[elem.name].push ((elem.value !== '') ? elem.value : 1);
                                            }
                                        }
                                          else if (elem.type === 'checkbox') {
                                              if (elem.name.indexOf('[') == -1 && elem.name.indexOf(']') == -1) {
                                                  jsonData[elem.name] = '0';
                                              }  else {
                                                  if (jsonData[elem.name] === undefined) jsonData[elem.name] = [];
                                                  jsonData[elem.name].push (0);
                                              }

                                        }
                                    }
                                    else {
                                        
                                        if (elem.type === 'file') {
                                            var fileData = elem.files[0];
                                            if (fileData !== undefined) {
                                                formData.append (elem.name, fileData, fileData.name);
                                            }
                                        }  
                                          else {
                                          jsonData[elem.name] = elem.value;
                                          
                                        }    
                                    }
                                }
                            }
                        }
                    }
                }

                //try jQuery
                if (window.jQuery) {

                    formData.append ('formData', serialize(jsonData));
                    
                    if(empty(formData['formData']) && newMethod == 'get'){ formData = null; }
                    
                    var ajxReq = $.ajax ({
                        method: newMethod,
                        url: newRoute,
                        data: formData,                        
                        processData: false,
                        contentType: false,
                    }).done (function (result) {
                    
                       //see if the target is an input, else parse the input for scripts
                       
                       if (targetElement.prop('tagName') !== undefined && newTarget != null) {
                           tagTarget = targetElement.prop('tagName').toUpperCase();

                           if (tagTarget === 'INPUT' || tagTarget === 'TEXTAREA') {
                                console.log ('Tina4 - Target is input:'+tagTarget.name);
                                targetElement.val(result);
                           }  else {
                                console.log ('Tina4 - Target is HTML element'+tagTarget.name);
                                targetElement.html(result);
                           }
                       }
                         else {
                          console.log ('Tina4 - parse script');
                          parse{$name}Script(result);
                       }
                   });
               } else {
                    $.request (newMethod, newRoute, jsonData
                    ).then (
                        function success (result) {
                            if (targetElement.get('tagName') !== undefined && newTarget != null) {
                               tagTarget = targetElement.get('tagName').toUpperCase();
                               if (tagTarget === 'INPUT' || tagTarget === 'TEXTAREA') {
                                    console.log ('Tina4 - Target is input:'+tagTarget.name);
                                    targetElement.set({value: result});
                               }  else {
                                    console.log ('Tina4 - Target is HTML element'+tagTarget.name);
                                    targetElement.set('innerHTML', result);
                               }
                            }
                              else {
                              console.log ('Tina4 - parse script');
                              parse{$name}Script(result);
                            }


                        }
                    );

                    return false;
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

}
