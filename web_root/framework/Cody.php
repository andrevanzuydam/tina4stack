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
        if ($tablename !== "") {
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
     * Method to create a generic bootstrap table
     */
    function bootStrapTable($sql = "select * from user_detail", $buttons = "", $hideColumns = "", $toolbar = "My Grid", $customfields = null, $name = "grid", $class = "table table-striped", $rowLimit = 50, $paginate = true, $searchable = true ) {
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
                $header .= th(["data-sortable" => "true"], ucwords(str_replace("_", " ", strtolower($field["alias"]))));
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
                        if (!empty($customfield["url"])) {
                            $urlPath = $customfield["url"];
                            foreach ($fieldInfo as $fid2 => $field2) {
                                $urlPath = str_ireplace("{" . $field2["name"] . "}", $record[$fid2], $urlPath);
                            }
                        }

                        switch ($customfield["type"]) {
                            case "link":
                                $fieldData .= td(["style" => "text-align:" . $field["align"]], a(["href" => $urlPath], "" . $record[$fid] . ""));
                                break;
                            default :
                                $fieldData .= td(["style" => "text-align:" . $field["align"]], "" . $record[$fid] . "");
                                break;
                        }
                    } else {
                        $fieldData .= td(["style" => "text-align:" . $field["align"]], "" . $record[$fid] . "");
                    }
                }

                if ($rowButtons != "") {
                    $rowButtons = str_ireplace("{" . $field["name"] . "}", $record[$fid], $rowButtons);
                }
            }



            $data .= tr($fieldData . td($rowButtons));
        }



        $footer = tfoot("");

        $options = ["id" => $name, "class" => $class, "data-toolbar" => "#toolbar" . $name];
        if ($paginate) {
            $options["data-pagination"] = "true";
        }

        if ($searchable) {
            $options["data-search"] = "true";
        }

        $html .= div(["id" => "toolbar" . $name], div(["class" => "form-inline", "role" => "form"], $toolbar));

        $html .= div(["class" => "table-responsive"], table($options, $header, tbody($data), $footer));

        $html .= script('$(function () { var $table' . $name . ' = $("#' . $name . '"); $table' . $name . '.bootstrapTable(); });');

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
     * @param type $name
     * @param string $alttext
     * @param type $selecttype
     * @param type $lookup
     * @param type $value
     * @param type $event
     * @param type $cssid
     * @param type $readonly
     * @param type $nochoose
     * @return string
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
            if ($value == $option[0]) {
                $html .= "<option selected=\"selected\" value=\"{$option[0]}\">{$option[1]}</option>";
            } else {
                $html .= "<option value=\"{$option[0]}\">{$option[1]}</option>";
            }
        }
        $html .= "</select>";
        return $html;
    }

    function multitag($name, $alias, $url, $value, $dafaultValue = "") {
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

        //  elt.tagsinput('add', { 'ID': 0 , 'CITY_NAME': 'None' });

        return $html;
    }

    /**
     * The Validation of forms
     * 
     * The form validator is an easy way to validate forms in the system using jQuery
     * 
     * @see http://jqueryvalidation.org/files/demo/
     * @param String $rules A JSON object which matches the form inputs we need to use
     * @return type
     */
    function validateForm($rules, $messages = "") {
        $html = script(array("text" => "application/javascript"), "
        \$(document).ready (function () { \$('form').validate({
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
    function bootStrapPanel($title, $content, $class = "panel panel-default panel-small") {

        return div(["class" => $class], div(["class" => "panel-heading"], h3($title)), div(["class" => "panel-body"], $content));
    }

    /**
     * A function to create a valid Bootstrap Form
     * @param String $sql A valid SQL statement for the selected database
     * @return type
     */
    function bootStrapForm($sql = "", $hideColumns = "", $custombuttons = null, $customfields = null, $submitAction = "") {
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
                        case "password":
                            $input = input(["class" => "form-control", "type" => "password", "placeholder" => ucwords(str_replace("_", " ", strtolower($field["alias"]))), "name" => "txt" . $field["name"], "id" => $field["name"]], "");
                            break;
                        case "hidden":
                            $input = input(["class" => "form-control hidden", "type" => "hidden", "placeholder" => ucwords(str_replace("_", " ", strtolower($field["alias"]))), "name" => "txt" . $field["name"], "id" => $field["name"]], $record[$fid]);
                            break;
                        case "date":
                            $input = input(["class" => "form-control", "type" => "text", "placeholder" => ucwords(str_replace("_", " ", strtolower($field["alias"]))), "name" => "txt" . $field["name"], "id" => $field["name"]], $record[$fid]);
                            $input .= script("$('#" . $field["name"] . "').datepicker({format: '" . strtolower($this->DEB->outputdateformat) . "'})
                                .on('changeDate', function(ev){
                                });");
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
                        case "textarea":

                            $input = textarea(["class" => "form-control", "style" => $customfield["style"], "rows" => "3", "placeholder" => ucwords(str_replace("_", " ", strtolower($field["alias"]))), "name" => "txt" . $field["name"], "id" => $field["name"]], $record[$fid]);
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
                }
                else { //generic form                   
                    $validation[] = "txt" . $field["name"] . ":{required: true}";
                    $input = input(["class" => "form-control", "type" => "text", "placeholder" => ucwords(str_replace("_", " ", strtolower($field["alias"]))), "name" => "txt" . $field["name"], "id" => $field["name"]], $record[$fid]);
                }


                if (isset($customfields[$field["name"]])) {
                    $customfield = $customfields[$field["name"]];
                    switch ($customfield["type"]) {
                        case "hidden":
                            $html .= $input;
                            break;
                        default:
                            $html .= div(["class" => "form-group"], label([ "id" => "label" . $field["name"], "for" => $field["name"]], ucwords(str_replace("_", " ", strtolower($field["alias"])))), $input);
                            break;
                    }
                } else {
                    $html .= div(["class" => "form-group"], label([ "id" => "label" . $field["name"], "for" => $field["name"]], ucwords(str_replace("_", " ", strtolower($field["alias"])))), $input);
                }
            }
        }

        $html = form(["method" => "post", "action" => $submitAction, "enctype" => "multipart/form-data"], $html, $custombuttons, $this->validateForm(join($validation, ","), join($messages, ",")));

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
        $template = div(["class" => "row"], $key = div(["class" => "col-md-3"], ""), $value = div(["class" => "col-md-9"], "")
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
                            $key->set_content(span(["class" => "label label-default"], ucwords(str_replace("_", " ", strtolower($field["alias"])))));

                            $lookupList = "";
                            $lookuprow = $this->DEB->getRows($customfield["lookup"], DEB_ARRAY);

                            foreach ($lookuprow as $irow => $row) {
                                $lookupList .= $row[0] . "<br>\n";
                            }

                            $value->set_content($lookupList);

                            $htmlcontent .= $template;
                            break;
                        default:
                            $key->set_content(span(["class" => "label label-default"], ucwords(str_replace("_", " ", strtolower($field["alias"])))));
                            $value->set_content($record[$fid]);
                            $htmlcontent .= $template;
                            break;
                    }
                } else {
                    $key->set_content(span(["class" => "label label-default"], ucwords(str_replace("_", " ", strtolower($field["alias"])))));
                    $value->set_content($record[$fid]);
                    $htmlcontent .= $template;
                }
            }
        }

        $html .= div(["class" => "container"], $htmlcontent
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

}
