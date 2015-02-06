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

    /**
     * The constructor for Cody requires a Debby database connection
     * @param Debby $DEB
     */
    function Cody($DEB) {
        $this->DEB = $DEB;
    }

    /**
     * 
     */

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
     * Get the code from a tablename
     */
    function getCode($tablename = "") {
        $html = h1($tablename);
        $sql = "select\n";
        if (!empty($tablename)) {
            $tables = $this->DEB->getDatabase();
            $fields = $tables[$tablename];

            foreach ($fields as $fid => $field) {
                $sql .= "  " . $field["field"] . ",\n";
                $html .= label(input(["type" => "checkbox", "checked" => "checked"]), $field["field"] . " " . $field["type"]) . br();
            }

            $sql = substr($sql, 0, -2);

            $sql .= "\n from " . $tablename;

            $html .= textarea(["class" => "sql"], $sql);
        } else {
            $html .= h2("Choose a table from the left") . hr();
        }

        $html .= div(["style" => "overflow: auto"], pre($this->bootstrapForm($sql) . "\n" . $this->kendoGrid($sql)));

        return $html;
    }

    /**
     * An easy way to make a paginated bootStrapTable
     * 
     * @param String $sql 
     * @param Array $buttons
     * @param String $hideColumns
     * @param String $toolbar
     * @param Array $customfields
     * @param String $name
     * @param String $class
     * @param Integer $rowLimit
     * @param Boolean $paginate
     * @param Boolean $searchable
     * @param Boolean $checked
     * @param String  $checkPostURL
     * @return type
     */
    function bootStrapTable($sql = "select * from user_detail", $buttons = "", $hideColumns = "", $toolbar = "My Grid", $customfields = null, $name = "grid", $class = "table table-striped", $rowLimit = 1000, $paginate = true, $searchable = true, $checked=false, $checkedPostURL="", $checkSingleSelect=true, $event="" ) {
        $html = "";
        //make the header

        $hideColumns = explode(",", strtoupper($hideColumns));


        $skiprow = 0;

        $sql = "select first {$rowLimit} skip  {$skiprow} * from ( $sql ) t ";

        $records = $this->DEB->getRows($sql, DEB_ARRAY);

        //print_r ($records);

        $fieldInfo = $this->DEB->fieldinfo;

        $header = "";
        foreach ($fieldInfo as $fid => $field) {
            if (!in_array($field["name"], $hideColumns)) {
                
               if (isset($customfields[$field["name"]])) { 
                 $customfield = $customfields[$field["name"]];
                 switch ($customfield["type"]) {           
                    case "checkbox":
                      $header .= th(["class" => "text-" . $field["align"], "data-checkbox" => "true"],"");
                    break;    
                    case "hidden":
                      $header .= th(["class" => "hidden"], ucwords(str_replace("_", " ", strtolower($field["alias"]))));
                    break;    
                    
                    default: 
                      $header .= th(["class" => "text-" . $field["align"], "data-sortable" => "true"], ucwords(str_replace("_", " ", strtolower($field["alias"]))));
                    break;          
                 }
               
               }  else {
                 $header .= th(["class" => "text-" . $field["align"],"data-sortable" => "true"], ucwords(str_replace("_", " ", strtolower($field["alias"]))));  
               }
                   
            }
        }

        $addColumn = "";
        if ($buttons) {
            $addColumn .= th("Options");
        }



        $header = thead(tr($header . $addColumn));

        $data = "";
        foreach ($records as $rid => $record) {
            $fieldData = "";
            $rowButtons = $buttons;
            foreach ($fieldInfo as $fid => $field) {

                if ($fid == 0) {
                    $field["align"] = "left";
                }
                
               
                if (!in_array($field["name"], $hideColumns)) {

                    if (isset($customfields[$field["name"]])) {
                        $customfield = $customfields[$field["name"]];
                        
                        //Populate variables in URL path
                        if (!empty($customfield["url"])) {
                            $urlPath = $customfield["url"];
                            foreach ($fieldInfo as $fid2 => $field2) {
                                $urlPath = str_ireplace("{" . $field2["name"] . "}", $record[$fid2], $urlPath);
                            }
                        } else {
                          $urlPath = "";  
                        }
                        
                        //Populate variables in Onclick event
                        if (!empty($customfield["onclick"])) {
                            $onClickEvent = $customfield["onclick"];
                            foreach ($fieldInfo as $fid2 => $field2) {
                                $onClickEvent = str_ireplace("{" . $field2["name"] . "}", $record[$fid2], $onClickEvent);
                            }
                        } else {
                          $onClickEvent ="";  
                        }                       
                        
                     
                        switch ($customfield["type"]) {
                            case "hidden":
                                $fieldData .= td(["class" => "hidden"], "" . $record[$fid] . "");
                                break;
                            case "link":
                                $fieldData .= td(["class" => "text-" . $field["align"]], a(["href" => $urlPath,"onclick"=>$onClickEvent], "" . $record[$fid] . ""));
                                break;
                            case "checkbox":
                                $fieldData .= td(["class" => "text-" . $field["align"]], "" . $record[$fid] . "");
                                break;
                            case "calculated":
                                  eval ( '$value = '.$customfield["formula"].';');
                                  $fieldData .= td(["class" => "text-" . $field["align"]], "" .$value. "");  
                                break;
                            default :
                                $fieldData .= td(["class" => "text-" . $field["align"]], "" . $record[$fid] . "");
                                break;
                        }
                    } else {
                        $fieldData .= td(["class" => "text-". $field["align"]], "" . $record[$fid] . "");
                    }
                }

                if ($rowButtons != "") {
                    $rowButtons = str_ireplace("{" . $field["name"] . "}", $record[$fid], $rowButtons);
                }
            }


            if ($rowButtons != "") {
              $data .= tr($fieldData . td($rowButtons));
            } 
             else {
              $data .= tr($fieldData);   
            }
        }



        $footer = tfoot("");

        $options = ["id" => $name, "class" => $class, "data-toolbar" => "#toolbar" . $name];
        if ($paginate) {
            $options["data-pagination"] = "true";
        }

        if ($searchable) {
            $options["data-search"] = "true";
        }
        
        if ($checked){
            $options["data-click-to-select"]="true";                        
            if($checkSingleSelect){
               $options["data-single-select"]="true";
            }
        }
        
        
        if(empty($toolbar["caption"])){
            $toolbar["caption"] = "";
        }        
        if(empty($toolbar["buttons"])){
            $toolbar["buttons"] = "";
        }        
        if(empty($toolbar["filter"])){
            $toolbar["filter"] = "";
        }                
        
        $tableHeading = "";
        if (!empty( $toolbar["caption"])) {
          $tableHeading = h3($toolbar["caption"]).hr();
        }
        $toolbarButtons = $toolbar["buttons"];
        $toolbarFilters = $toolbar["filter"];
        
        if ($searchable) {
         $toolbarFilters .= div (["class" => "search"],input(["class" => "search form-control", "type" => "text", "placeholder" => "Search ".$toolbar["caption"]] ) );
        }    
        

        $toolbarFilters = div(["class"=>"form-inline", "role"=>"form"], $toolbarFilters);

        //$html .= div(["id" => "toolbar" . $name], div(["class" => "form-inline", "role" => "form"], $toolbar));

        $html .= $tableHeading.div(["class" => "table-responsive"],  div (["class" => "table-toolbar clearfix"], div(["class" => "toolbar-buttons"],  $toolbarButtons). div(["class" => "toolbar-filters"], $toolbarFilters)  ). table($options, $header, tbody($data), $footer) );
        

        $html .= script('$(function () { var $table' . $name . ' = $("#' . $name . '"); $table' . $name . '.bootstrapTable().on(\'check.bs.table\', function (e, row) { console.log(e); '.$event.'}); });');

        if ($checked) {
          $html = form(["method" => "post", "action" => $checkedPostURL, "enctype" => "multipart/form-data"], $html);  
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
            "Tab 1" => div("HTML/Shape tags", p("paragraph content")),
            "Tab 2" => "Any Content",
            "Tab 3" => "Tab 3 Content",
        );
        echo (new Cody($this->DEB))->bootStrapTabs($tabs);
     */
    
    function bootStrapTabs($tabs = array()){

        $html = "";
        
        if(!empty($tabs)){
            /**
             * Filter through tabs, creating additional values
             */
            $tempTabs = array();

            foreach($tabs as $tabName => $tabContent){
                
                /* transform tab name into id string */
                $id = "t".str_replace(" ", "", strtolower($tabName));
                /* Generate unique id based on id */
                $uniqid = uniqid($id."-", false);

                $tempTabs[] = array(
                    "tab" => $tabName,
                    "content" => $tabContent,
                    "tab_id" => "t".$uniqid,
                    "tab_id_name" => "#t".$uniqid,
                    "show_id" => "show".$uniqid,
                    "show_id_name" => "#show".$uniqid,
                );
            }

            $tabs = $tempTabs;
            
            $i = 0;
            $tabPanel = "";
            $tabContent = "";
            
            foreach($tabs as $tab){
                
                $tab = (object)$tab;
                
                /* Set first tab to active */
                $active = $i == 0 ? "active" : "";
                
                /* Add Tabs to the panel*/
                $tabPanel .= li (["role" => "presentation", "id" => "{$tab->tab_id}", "class" => "{$active}"], 
                                 a (["aria-controls" => "{$tab->show_id}", "role" => "tab", "data-toggle"=>"tab", "href" => "{$tab->show_id_name}", ], $tab->tab));
                                 
                /* Add tab content*/                                 
                $tabContent .= div (["role" => "tabpanel", "class" => "tab-pane {$active}", "id" => "{$tab->show_id}"],  $tab->content);
                
                $i++;
                
            }
            
            /* Tab Content */
            $tabContent = div (["class"=>"tab-content"], $tabContent );

            /* Tabs */
            $html =  div (["role" => "tabpanel"], 
                        ul ([ "class" => "nav nav-tabs", "role" => "tablist", "id" => "mytab"],
                        /* add Tab panel items */
                        $tabPanel   
            ).$tabContent);   
            
        }

        return $html;
    }

    /**
     * The CRUD code generation tool of cody
     */
    function display() {


        $tables = $this->DEB->getDatabase();
        $tableData = h1(" Tables ");
        foreach ($tables as $tableName => $tableFields) {
            $tableData .= li(a(["href" => "?tablename={$tableName}"], $tableName));
        }

        $html = html(
                head(
                        title("Cody - Generation of Code")
                ), body(
                        style(
                                "   .tables {
                               border: 1px solid pink;
                               width: 20%;
                               float: left;
                               
                            }
                            
                            .code {
                               border : 1px solid orange;
                               width: 75%;
                               float: left;
                            }
                            
                            body {
                              border: 0px;
                              margin: 0px;
                            }
                            
                            .sql {
                               width: 100%;
                               height: 200px;
                            }
                            
                        "
                        ), div(["class" => "tables"], ul($tableData)
                        ), div(["class" => "code"], $this->getCode(Ruth::getREQUEST("tablename"))
                        )
                )
        );



        return $html->compile_html();
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
        if ($selecttype == "array" || $selecttype == "multiarray") {
            $lookuplist = explode("|", $lookup);
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
            $option = explode(",", $option);
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
     * The multitag function which returns back a bloodhound, type ahead component compatible with bootstrap.
     * 
     * @param String $name The name of the component to be used with an ID
     * @param String $alias An Alias for the place holder message
     * @param String $url The url where the multitag can fetch its data from in the form ID, DATA = itemValue, itemText
     * @param String $value A value for the the multitag if it is not fetched
     * @param String $dafaultValue The default values to be loaded into the input
     * @return type
     */
    function multitag($name, $alias, $url, $value, $dafaultValue = "") {
        $html = input(["type" => "text", "tabindex" => "-1", "placeholder" => ucwords(str_replace("_", " ", strtolower($alias))), "name" => "txt" . $name, "id" => $name], "");
        $html .= script("
                var multi" . $name . "lookup = new Bloodhound({
                  datumTokenizer: function (d) { return Bloodhound.tokenizers.whitespace(d.tokens.join(' ')); },
                  queryTokenizer: Bloodhound.tokenizers.whitespace,
                  remote: { url : '" . $url . "?filter=%QUERY' }
                });
               multi" . $name . "lookup.initialize();
               var a" . $name . "lookup = $('#" . $name . "');
                a" . $name . "lookup.tagsinput({
                  itemValue: 'ID',
                  itemText: 'DATA',
                  freeInput: false, 
                  typeaheadjs: {
                    name: 'cities',
                    displayKey: 'DATA',
                    source: multi" . $name . "lookup.ttAdapter()
                  }
                });
                
                a" . $name . "lookup.addClass('form-control');
                    
                a" . $name . "lookup.on('itemAdded', function(event) {
                    console.log(event.item);
                  });
                

                ");

        if ($dafaultValue != "") {
            $lookuprow = $this->DEB->getRows($dafaultValue, DEB_ARRAY);
            $scriptVar = "";
            foreach ($lookuprow as $irow => $row) {
                $scriptVar .= "a" . $name . "lookup.tagsinput('add', { 'ID': '" . $row[0] . "' , 'DATA': '" . $row[1] . "' });\n";
            }
            $html .= script($scriptVar);
        }
        return $html;
    }
    
    
     /**
     * The multitag function which returns back a bloodhound, type ahead component compatible with bootstrap.
     * 
     * @param String $name The name of the component to be used with an ID
     * @param String $alias An Alias for the place holder message
     * @param String $url The url where the multitag can fetch its data from in the form ID, DATA = itemValue, itemText
     * @param String $value A value for the the multitag if it is not fetched
     * @param String $dafaultValue The default values to be loaded into the input
     * @return type
     */
    function multitagGooglePlaces($name, $alias, $url, $value, $dafaultValue = "") {
        //https://maps.googleapis.com/maps/api/js/AutocompletionService.GetPredictions?1stest&4sen-US&7scountry%3Aus&9sgeocode&callback=_xdc_._cvw7ch&token=63182
        
        $html = input(["type" => "text", "placeholder" => ucwords(str_replace("_", " ", strtolower($alias))), "name" => "txt" . $name, "id" => $name], "");
        $html .= script("
                var multi" . $name . "lookup = new Bloodhound({
                  datumTokenizer: function (d) { return Bloodhound.tokenizers.whitespace(d.tokens.join(' ')); },
                  queryTokenizer: Bloodhound.tokenizers.whitespace,
                  remote: { url : '" . $url . "?filter=%QUERY' }
                });
               multi" . $name . "lookup.initialize();
               var a" . $name . "lookup = $('#" . $name . "');
                a" . $name . "lookup.tagsinput({
                  itemValue: 'ID',
                  itemText: 'DATA',
                  typeaheadjs: {
                    name: 'cities',
                    displayKey: 'DATA',
                    source: multi" . $name . "lookup.ttAdapter()
                  }
                });
                a" . $name . "lookup.addClass('form-control');

                ");

        if ($dafaultValue != "") {
            $lookuprow = $this->DEB->getRows($dafaultValue, DEB_ARRAY);
            $scriptVar = "";
            foreach ($lookuprow as $irow => $row) {
                $scriptVar .= "a" . $name . "lookup.tagsinput('add', { 'ID': '" . $row[0] . "' , 'DATA': '" . $row[1] . "' });\n";
            }
            $html .= script($scriptVar);
        }
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
    function validateForm($rules, $messages = "", $formId="formInput") {
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
                                            errorElement: 'span',
                                            errorClass: 'help-block',
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
    function bootStrapPanel($title, $content, $class = "panel panel-default panel-small",$footerContent="", $icon="") {
        
        if ($footerContent == ""){
            $footer = "";
        } else {
            $footer = div(["class"=>"panel-footer"],$footerContent);
        }

        return div(["class" => $class], div(["class" => "panel-heading"], $icon.h3($title)), div(["class" => "panel-body"], $content), $footer);
    }

    /**
     * A function to create a valid Bootstrap Form
     * @param String $sql A valid SQL statement for the selected database
     * @return type
     */
    function bootStrapForm($sql = "", $hideColumns = "", $custombuttons = null, $customfields = null, $submitAction = "", $formId = "formId") {
        $fieldinfo = $this->getFieldInfo($sql);

        $hideColumns = explode(",", strtoupper($hideColumns));

        $record = $this->DEB->getRow($sql, 0, DEB_ARRAY);
        $html = ""; 

        $validation = [];
        $messages = [];

        foreach ($fieldinfo as $fid => $field) {

            if (!isset($record[$fid])) {
                $record[$fid] = null;
            } else {
                $record[$fid] .= "";
            }

            if (!in_array($field["name"], $hideColumns)) {
                $input = "";

                if (isset($customfields[$field["name"]])) {
                    $customfield = $customfields[$field["name"]];

                    if (!isset($customfield["event"]))
                        $customfield["event"] = "";
                    if (!isset($customfield["style"]))
                        $customfield["style"] = "";
                    if (!empty($customfield["validation"])) {
                        $message = explode(",", $customfield["validation"]);
                        $mfound = false;
                        foreach ($message as $mid => $mvalue) {
                            $mvalue = explode(":", $mvalue);

                            if ($mvalue[0] == "message") {
                                $messages[] = "txt" . $field["name"] . ": { required: '" . $mvalue[1] . "', remote: '" . $mvalue[1] . "'}";
                                $mfound = true;
                                unset($message[$mid]);
                            }
                        }

                        if ($mfound)
                            $customfield["validation"] = join(",", $message);

                        $validation[] = "txt" . $field["name"] . ": {" . $customfield["validation"] . "}";
                    }
                    else {
                        $validation[] = "txt" . $field["name"] . ":{required: true}";
                    }

                    switch ($customfield["type"]) {
                        case "multitag":
                            $input = $this->multitag($field["name"], $field["alias"], $customfield["url"], $record[$fid], $customfield["dafaultValue"]);

                        break;
                        case "googleplaces":
                            $input = $this->multitagGooglePlaces($field["name"], $field["alias"], $customfield["url"], $record[$fid], $customfield["dafaultValue"]);
                        break;    
                        case "password":
                            $input = input(["class" => "form-control", "type" => "password", "placeholder" => ucwords(str_replace("_", " ", strtolower($field["alias"]))), "name" => "txt" . $field["name"], "id" => $field["name"]], "");
                            break;
                        case "hidden":
                            $input = input(["class" => "form-control hidden", "type" => "hidden", "placeholder" => ucwords(str_replace("_", " ", strtolower($field["alias"]))), "name" => "txt" . $field["name"], "id" => $field["name"]], $record[$fid]);
                            break;
                        case "date":
                            $input = input(["class" => "form-control", "type" => "text", "placeholder" => ucwords(str_replace("_", " ", strtolower($field["alias"]))), "name" => "txt" . $field["name"], "id" => $field["name"]], $record[$fid]);
                            $input .= script("$('#" . $field["name"] . "').datepicker({format: '" . strtolower($this->DEB->outputdateformat) . "'}).on('changeDate', function(ev) { console.log(ev); ".$customfield["event"]." } );");
                            break;
                        case "toggle":
                            $checked = null;
                            $input = input(["class" => "form-control", "type" => "checkbox", "data-toggle" => "toggle", "data-on" => "Yes", "data-off" => "No", "placeholder" => ucwords(str_replace("_", " ", strtolower($field["alias"]))), "name" => "txt" . $field["name"], "id" => $field["name"]], $record[$fid]);           
                            if ($record[$fid] == 1) {
                                $input->addAttribute ("checked", "checked");
                            }  
                         break;   
                        case "image":

                            $input = img(["class" => "thumbnail", "style" => "height: 160px; width: 160px", "src" => $this->DEB->encodeImage($record[$fid], "/imagestore", "160x160", "/imagestore/default_profile.jpg"), "alt" => ucwords(str_replace("_", " ", strtolower($field["alias"])))]);
                            $input .= input(["type" => "hidden", "name" => "MAX_FILE_SIZE"], "4194304");
                            $input .= input(["class" => "btn btn-primary", "type" => "file", "accept" => "image/*", "name" => "txt" . $field["name"], "onclick" => $customfield["event"]], ucwords(str_replace("_", " ", strtolower($field["alias"]))));
                            break;
                        case "file":
                            $input = input(["type" => "hidden", "name" => "MAX_FILE_SIZE"], "4194304");
                            $input .= input(["class" => "btn btn-primary", "type" => "file", "accept" => "image/*", "name" => "txt" . $field["name"], "onclick" => $customfield["event"]], ucwords(str_replace("_", " ", strtolower($field["alias"]))));
                            break;
                        case "text":
                            $input = input(["class" => "form-control", "type" => "text", "placeholder" => ucwords(str_replace("_", " ", strtolower($field["alias"]))), "name" => "txt" . $field["name"], "id" => $field["name"]], $record[$fid]);
                            break;
                        case "readonly":
                            $input = input(["class" => "form-control", "type" => "text", "readonly" => "readonly", "placeholder" => ucwords(str_replace("_", " ", strtolower($field["alias"]))), "name" => "txt" . $field["name"], "id" => $field["name"]], $record[$fid]);
                            break;
                        case "textarea":
                            $input = textarea(["class" => "form-control", "style" => $customfield["style"], "rows" => "3","placeholder" => ucwords(str_replace("_", " ", strtolower($field["alias"]))), "name" => "txt" . $field["name"], "id" => $field["name"]], $record[$fid]);
                            break;
                        case "select":
                            if (!isset($customfield["event"]))
                                $customfield["event"] = "";
                            if (!isset($customfield["readonly"]))
                                $customfield["readonly"] = false;
                            $input = $this->select("txt" . $field["name"], ucwords(str_replace("_", " ", strtolower($field["alias"]))), $customfield["selecttype"], $customfield["lookup"], $record[$fid], $customfield["event"], $field["name"], $customfield["readonly"]);
                            break;
                        default:
                            $input = div($record[$fid]);
                         break;
                    }
                   
                    if (!empty($customfield["description"])) {
                       $input .=  i ( ["class" => "field-optional alert-success"], ($customfield["description"]) );
                    }
                    
                    
                }
                else { //generic form                   
                    $validation[] = "txt" . $field["name"] . ":{required: false}";
                    $input = input(["class" => "form-control", "type" => "text", "placeholder" => ucwords(str_replace("_", " ", strtolower($field["alias"]))), "name" => "txt" . $field["name"], "id" => $field["name"]], $record[$fid]);
                    $input .=  i ( ["class" => "field-optional alert-warning"], "*Optional" );
                }


                if (isset($customfields[$field["name"]])) {
                    $customfield = $customfields[$field["name"]];
                    switch ($customfield["type"]) {
                        case "hidden":
                            $html .= $input;
                            break;
                        default:
                            if (!empty($customfield["help"])) {
                                $html .= div(["class" => "form-group", "id" => "form-group" . $field["name"]],
                                            label([ "id" => "label" . $field["name"], "for" => $field["name"]], ucwords(str_replace("_", " ", strtolower($field["alias"])))),
                                                span(["id" => "help".$field["name"], "class" => "icon-x-info info-icon","data-toggle"=>"tooltip", "data-placement"=>"right", "title"=> $customfield["help"]]), 
                                            $input); 
                                $html .= script("$('#help{$field["name"]}').tooltip({ trigger: 'hover' });");                       
                            }
                        else {
                          $html .= div(["class" => "form-group","id" => "form-group" . $field["name"]], label([ "id" => "label" . $field["name"], "for" => $field["name"]], ucwords(str_replace("_", " ", strtolower($field["alias"])))), $input);  
                        }
                            
                            
                        break;
                    }
                    
                    
                    
                } else {
                    $html .= div(["class" => "form-group"], label([ "id" => "label" . $field["name"], "for" => $field["name"]], ucwords(str_replace("_", " ", strtolower($field["alias"])))), $input);
                }
                
                
            }
        }

        $html = form(["method" => "post", "action" => $submitAction, "enctype" => "multipart/form-data", "id" => $formId], $html, $custombuttons, $this->validateForm(join($validation, ","), join($messages, ","), $formId));

        return $html;
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
    
   function bootStrapMessageBox($title, $linkTitle="Click Here", $url="#", $number="", $panelClass="panel panel-primary", $glyphClass="fa fa-comments fa-5x"){
        $html = "";
        $html .= div(["class"=>"row"],
                                div(["class" => $panelClass],
                                    div(["class"=>"panel-heading"],
                                        div(["class"=>"row"],
                                            div(["class"=>"col-xs-3"],
                                                    i(["class"=>$glyphClass])
                                                ),
                                            div(["class"=>"col-xs-9 text-right"],
                                                div(["class"=>"huge"],$number),
                                                div($title)
                                                )
                                            )
                                        ),
                                        a(["href"=>$url],
                                            div(["class"=>"panel-footer"],
                                                span(["class"=>"pull-left"],$linkTitle),
                                                span(["class"=>"pull-right"],
                                                    i(["class"=>"fa fa-arrow-circle-right"])    
                                                    ),
                                                div(["class"=>"clearfix"])
                                                )
                                         )
                                        ));
        return $html;
        
    }
    
    /**
     * TODO - FIX THIS
     * @param type $title
     * @param type $number
     * @param type $sql
     * @return type
     */
    function bootstrapDashboardPanel($title, $number=0, $sql=""){        
        $html ="";                        

        $records = $this->DEB->getRows($sql);
        $fieldinfo = $this->getFieldInfo($sql);
        
        $header = div(["class"=>"panel-heading"],
                            div(["class"=>""],
                                div(["class"=>""],
                                        span(["class"=>"x-icon-headings-invert icon-x-order x-icon-headings"]).h3(["class"=>"header-white header-smaller"],$title)
                                    ),
                                div(["class"=>""],
                                    div(["class"=>"huge"],strval($number))                                    
                                    )
                                )
                            );
        
        $lines = "";
        
        foreach ($records as $rid => $record) {
            $fieldData = "";       
            $width = "width: " . $record->PERCENTAGE . "%";
            $url = "/broker/order/detail/{$record->ORDER_DETAIL_ID}";
            $leadUrl = "/broker/order/{$record->ORDER_DETAIL_ID}/lead";                
            
            $lines .=   div(["class"=>"panel-footer"],                                            
                            div(["class"=>"row"],
                                div(["class"=>"col-lg-5 "],                                                    
                                        a(["href"=>$url],
                                            span(["class"=>"pull-left"],$record->DESCRIPTION)
                                        )
                                    ),
                                div(["class"=>"col-lg-2 clearfix"],                                                    
                                        a(["href"=>$leadUrl],
                                            span(["class"=>"pull-right"],$record->DETAIL)
                                        )
                                    ),                                                           
                                div(["class"=>"col-lg-5"],
                                    div(["class"=>"progress dashboard-progress-bar", "style"=>"background-color: rgba(221, 221, 221, 1)"],
                                            div(["class"=>"progress-bar", "progress-bar-success"=>"yes", "role"=>"progressbar", "aria-valuenow"=>$record->LEAD_FILLED, "aria-valuemin"=>"0", "aria-valuemax"=>$record->LEAD_GOAL, "style"=>$width]
                                                )
                                        )
                                    )    
                                ), 
                            div(["class"=>"clearfix"])
                        );
                         
            
        }        
        
        $html .= div(["class" => "panel panel-primary"],
                            $header,
                            $lines
                    );                                
        
        return $html;
        
    }
    
    /**
     * 
     * @param type $sql
     * @param type $hideColumns
     * @param type $custombuttons
     * @param type $customfields
     * @param type $submitAction
     * @return type
     */
    function bootStrapView($sql = "", $hideColumns = "", $custombuttons = null, $customfields = null, $submitAction="") {        
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

                if (isset($customfields[$field["name"]])) {
                    $customfield = $customfields[$field["name"]];

                    switch ($customfield["type"]) {
                        case "select":
                            $key->setContent(b(ucwords(str_replace("_", " ", strtolower($field["alias"])))));

                            $lookupList = "";
                            $lookuprow = $this->DEB->getRows($customfield["lookup"], DEB_ARRAY);

                            foreach ($lookuprow as $irow => $row) {
                                $lookupList .= $row[0] . "<br>\n";
                            }

                            $value->setContent($lookupList);

                            $htmlcontent .= $template;
                            break;
                        default:
                            $key->setContent(b( ucwords(str_replace("_", " ", strtolower($field["alias"])))));
                            $value->setContent($record[$fid]);
                            $htmlcontent .= $template;
                            break;
                    }
                } else {
                    $key->setContent(b( ucwords(str_replace("_", " ", strtolower($field["alias"])))));
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

    /**
     * A function that make a Kendo Grid for you to cut and paste.
     * 
     * @param String $sql A valid SQL statement
     * @param String $gridName The name of the grid, essential when using more than one grid on a page
     * @param String $height The height of the grid
     * @param Boolean $groupable Can the grid use the grouping functionality
     * @param Boolean $sortable Is the grid able to be sorted
     * @param Boolean $pageable Can the grid handle paging
     * @param Boolean $selectable The items in the grid can be selected
     * @param Boolean $pageSize The default page size is 20 items
     * @return String A valid Javascript / HTML implementation of the grid
     */
    function kendoGrid($sql, $gridName = "myGrid", $height = "550", $groupable = true, $sortable = true, $pageable = true, $selectable = true, $pageSize = 20) {
        $fieldinfo = $this->getFieldInfo($sql);

        $html = 'div(["id" => "' . $gridName . '"]),' . "\n";

        $gridScript = '$(document).ready(function(){
           $(\'#' . $gridName . '\').kendoGrid({
      dataSource: {
        
        transport: {
          read: { url: \'http://' . Ruth::getSERVER("HTTP_HOST") . '/data\',
                  dataType: \'json\' }   
        },
        schema:{
          data: \'data\'
        },
        pageSize: ' . $pageSize . '
      },
      height:' . $height . ",\n";
        $gridScript .= '      groupable: ';
        $gridScript .= ($groupable) ? 'true' : 'false';
        $gridScript .= ',' . "\n";
        $gridScript .= '      sortable: ';
        $gridScript .= ($sortable) ? 'true' : 'false';
        $gridScript .= ',' . "\n";
        $gridScript .= '      selectable: ';
        $gridScript .= ($selectable) ? 'true' : 'false';
        $gridScript .= ',' . "\n";
        if ($pageable) {
            $gridScript .= '      pageable:{
                refresh: true,
                pageSizes: true,
                buttonCount: 5
            },'
                    . "\n";
        }
        $gridScript .= '      columns: [' . "\n";
        foreach ($fieldinfo as $fid => $field) {
            $gridScript .= '        {
            field: \'' . $field["name"] . '\',
            title: \'' . $field["alias"] . '\',
            width: \'' . $field["htmllength"] . 'px\'
        }';
            if ($fid != count($fieldinfo) - 1) {
                $gridScript .= ",\n";
            }
        }
        $gridScript .= "\n" . '      ]' . "\n";
        $gridScript .= "\n" . '});';
        $gridScript .= "\n" . '});';

        $html .= 'script (" ' . $gridScript . ' ") ';

        $html = 'shape (' . $html . ');';


        $html .= "\n" . '
                //CODE FOR THE ROUTER
              Ruth::addRoute("GET", "/data", function () {
                    $DEB = Ruth::getOBJECT("DEB");
                    $sql = "' . $sql . '";
                    $records = $DEB->getRows ($sql);

                    $data = new stdClass();
                    $data->data = $records;


                    echo json_encode ($data);

                });';

        return pre(htmlentities($html));
    }
/**
 * 
 * @return string returns lead info :street address,city,state,nb of baths and beds in a nice format. EXAMPLE-11845 Intermountain Rd, Redding, CA , 3 Beds, 2 Baths
 */
}
