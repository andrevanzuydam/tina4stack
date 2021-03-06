<?php
/**
 * Olga is a class which adds getters and setters to your existing object, it has methods to export the class to JSON and to import the class from JSON
 * User: Andre van Zuydam
 * Date: 2015-09-04
 * Time: 04:05 PM
 */
define("OLGA_MATCH_ANY", 1);
define("OLGA_MATCH_BEGINNING", 2);
define("OLGA_MATCH_ENDING", 3);
class Olga implements Iterator  {
    var $DEBUG = false;
    var $arrayObjects = []; //an array or collection of objects of the same type as this (we probably need to check if it is the same)
    var $errors;
    var $javascript = "<script> window.onclick = function() { var elements = document.getElementsByTagName('span'); for(var i = 0; i < elements.length; i++) {if ( elements[i].className === 'formError' ) {elements[i].style.display = 'none';} } }  </script>";

    function debug ($message) {
        if ($this->DEBUG) {
            echo $message."\n";
        }
    }



    /**
     * Method to get the results from XCache into the object
     */
    function populateFromXCache() {
        if (function_exists("xcache_get")) {
            if (!empty($this->id) || isset($this->id)) { //this must be a single record, we will need to fetch it by its unique id in memory
                $json = unserialize(xcache_get("olgaSingleObject".get_class($this)."-".$this->id));
                $this->fromJSON($json);

                return $this;
            } else {
                $objects = json_decode(unserialize(xcache_get("olgaArrayObjects".get_class($this))));

                $this->clear();
                if (!empty($this->mapping["object"]) && !empty($objects)) {
                    foreach ($objects as $oid => $object) {
                        $newObject = "";

                        eval ('$newObject = new '.$this->mapping["object"].'(\''.json_encode($object).'\');');
                        $this->append($newObject);
                    }

                } else { //No object found
                    return false;
                }
                return $this;
            }
        } else {
            return false;
        }
    }

    /**
     * Method to put the results into the xcache
     */
    function populateToXCache() {
        if (function_exists("xcache_set")) {
            if (!empty($this->id) || isset($this->id)) { //save the single record
                $json = $this->toJSON();
                xcache_set("olgaSingleObject".get_class($this)."-".$this->id, serialize($json));
            } else {
                xcache_set("olgaArrayObjects".get_class($this), serialize($this->toJSON()));
            }
        } else {
            return false;
        }
        return $this;
    }

    /**
     * Method to remove an entry from xcache
     * @return $this|bool
     */
    function removeFromXCache () {
        if (function_exists("xcache_set")) {
            if (!empty($this->id) || isset($this->id)) { //save the single record
                if (xcache_isset("olgaSingleObject".get_class($this)."-".$this->id)) {
                    xcache_unset("olgaSingleObject".get_class($this)."-".$this->id);
                }
            } else {

                if (xcache_isset("olgaArrayObjects".get_class($this))) {
                    xcache_unset("olgaArrayObjects".get_class($this));
                }
            }
        } else {
            return false;
        }
        return $this;
    }

    /**
     * Maps a record to the object
     * @param $record
     */
    function mapRecord($record) {
        foreach ($this->mapping["fields"] as $objectField => $field) {
            eval ('$this->' . $objectField . ' = $record->' . strtoupper($field["field"]) . ';');
        }
    }

    /**
     * Method to get the results from the database
     */
    function populateFromDebby($DEB) {
        if (!empty($this->id) || isset($this->id)) { //single object - very easy
            if (!empty($this->mapping["table"])) {
                if (!empty($this->mapping["fields"])) {
                    $sql = "select ";
                    $fields = [];
                    foreach ($this->mapping["fields"] as $objectField => $field) {
                        if ($objectField === "id") {
                            $primaryKey = $field["field"];
                        }
                        $fields[] = $field["field"];
                    }
                    $sql .= join(",", $fields);
                    $sql .= " from {$this->mapping["table"]} ";
                    $sql .= " where {$primaryKey} = '{$this->id}'";

                    $record = $DEB->getRow ($sql);
                    if (!empty($record)) {
                        //map to the memory record and the object
                        $this->mapRecord ($record);
                    }

                    $this->createGetSet();
                    //save us to the memory
                    $this->save(true);

                    return $this;
                } else { //No fields found
                    return false;
                }
            } else { //No table found
                return false;
            }
        } else { //complex object - very difficult - see paging //TODO:we must make paging happen here, for small objects no pressure
            if (!empty($this->mapping["table"])) {
                $sql = "select *";
                $sql .= " from {$this->mapping["table"]} ";

                if (!empty($this->mapping["object"])) {
                    $records = $DEB->getRows($sql);
                    if (!empty($records)) {
                        foreach ($records as $rid => $record) {
                            //create some objects in memory and add the object to this
                            $newObject = "";
                            eval ('$newObject = new ' . $this->mapping["object"] . '();');
                            $newObject->mapRecord($record);
                            //add the object to memory
                            $newObject->createGetSet();
                            $newObject->save(true);

                            $this->append($newObject);
                        }

                        //save the object to memory
                        $this->save(true);
                        return $this;
                    }
                } else {
                    return false;
                }
            } else { //No table found
                return false;
            }
        }
    }

    /**
     * Method to update the database from the objects in memory
     * @return bool
     */
    function populateToDebby() {
        $DEB = Ruth::getOBJECT("DEB");

        //read from the database
        if (!empty($DEB)) {
            if (!empty($this->id) || isset($this->id)) { //a single record
                if (!empty($this->mapping["table"])) {
                    if (!empty($this->mapping["fields"])) {
                        $fieldValues = [];
                        foreach ($this->mapping["fields"] as $objectField => $field) {
                            if ($objectField === "id") {
                                $primaryKey = [$field["field"] => $this->id];
                            }
                            eval (' if (!empty($this->get'.ucwords($objectField).'()) || $this->get'.ucwords($objectField).'() === "0" ) {  $fieldValues[$field["field"]] = $this->get'.ucwords($objectField).'(); } ');

                        }
                        $DEB->updateOrInsert($this->mapping["table"], $fieldValues, $primaryKey);
                        $DEB->commit();

                    } else {
                        return false;
                    }
                }
                else {
                    return false;
                }
            } else { //multiple objects
                if (!empty($this->mapping["table"])) {

                    if (!empty($this->mapping["object"])) {
                        $newObject = "";

                        eval ('$newObject = new '.$this->mapping["object"].'();');
                        $table = $newObject->mapping["table"];
                        foreach ($this as $rid => $object) {
                            if (!empty($newObject->mapping["fields"])) {
                                if (!empty($newObject->mapping["table"])) {
                                    $fieldValues = [];
                                    foreach ($newObject->mapping["fields"] as $objectField => $field) {
                                        if ($objectField === "id") {
                                            $primaryKey = [$field["field"] => $object->id];
                                        }
                                        eval (' if (!empty($object->get'.ucwords($objectField).'()) || $this->get'.ucwords($objectField).'() === "0" ) {  $fieldValues[$field["field"]] = $object->get'.ucwords($objectField).'(); } ');
                                    }
                                    $DEB->updateOrInsert($table, $fieldValues, $primaryKey);
                                    $DEB->commit();
                                }
                            } else {
                                return false;
                            }
                        }
                    } else {
                        return false;
                    }
                } else {
                    return false;
                }
            }
        }
        return true;
    }

    /**
     *  Remove items from the database
     * @return bool
     */
    function removeFromDebby() {
        $DEB = Ruth::getOBJECT("DEB");

        //read from the database
        if (!empty($DEB)) {
            if (!empty($this->id) || isset($this->id)) { //a single record
                if (!empty($this->mapping["table"])) {
                    if (!empty($this->mapping["fields"])) {
                        foreach ($this->mapping["fields"] as $objectField => $field) {
                            if ($objectField === "id") {
                                $primaryKey = [$field["field"] => $this->id];
                            }
                        }
                        $DEB->delete ($this->mapping["table"], $primaryKey);
                        $DEB->commit();
                    } else {
                        return false;
                    }
                }
                else {
                    return false;
                }
            } else { //multiple objects
                if (!empty($this->mapping["table"])) {
                    if (!empty($this->mapping["object"])) {
                        $newObject = "";
                        eval ('$newObject = new '.$this->mapping["object"].'();');
                        $table = $newObject->mapping["table"];
                        foreach ($this as $rid => $object) {
                            if (!empty($newObject->mapping["fields"])) {
                                if (!empty($newObject->mapping["table"])) {
                                    $newObject->delete();
                                }
                            } else {
                                return false;
                            }
                        }
                    } else {
                        return false;
                    }
                } else {
                    return false;
                }
            }
        }
        return true;
    }


    /**
     * A method to load the data from the database into the object in question
     */
    function load() {
        //see what type of object we have, do we have a database object or not,
        $DEB = Ruth::getOBJECT("DEB");
        //read from the database
        if (!empty($DEB)) {
            //see if we can grab from xcache first

            if (empty($this->populateFromXCache())) {
                return $this->populateFromDebby($DEB);
            } else {
                return $this->populateFromXCache();
            }
        } else {
            return $this->populateFromXCache();
        }


    }

    /**
     * A method to save the data into the database from the object in question
     * @param bool|false $onlyToMemory Saves only to memory
     * @return bool
     */
    function save($onlyToMemory=false) {
        $DEB = Ruth::getOBJECT("DEB");
        //save the data to the database
        if ($onlyToMemory) {
            return $this->populateToXCache();
        } else
            if (!empty($DEB)) {
                if ($this->populateToDebby()) {
                    return $this->populateToXCache();
                }
            } else {
                return $this->populateToXCache();
            }

        return $this;
    }


    /**
     * A method to delete a whole object from the database & memory
     * @param bool|false $onlyFromMemory
     */
    function delete ($onlyFromMemory=false) {
        $DEB = Ruth::getOBJECT("DEB");
        //delete the data from the database
        if ($onlyFromMemory) {
            return $this->removeFromXCache();
        } else
            if (!empty($DEB)) {

                if ($this->removeFromDebby()) {
                    return $this->removeFromXCache();
                }
            } else {
                return $this->removeFromXCache();
            }

        return $this;

    }


    /**
     * The clone function must recreate the object closures
     */
    function __clone() {
        //we will have to clone the closures
        $this->createGetSet();
    }

    /**
     * Append an object that has similar properties as the known object
     */
    function append($object) {
        if (is_object($object)) {
            $this->arrayObjects[] = clone $object;
        }
        else {
            throw new Exception("Olga says this is not an object");
        }
    }

    /**
     * Create a JSON instance of the object
     * @return string
     */
    function toJSON() {
        //print_r ((array) $this);
        if (count($this->arrayObjects) > 0) {
            return $this->__toJson(  $this->arrayObjects);
        } else {
            return $this->__toJson((array)$this);
        }
    }

    /**
     * Create an PHP array from the JSON representation of the object
     * @return Array
     */
    function toArray() {
        return (array) json_decode($this->toJSON());
    }

    /**
     * Create an PHP object from the JSON representation of the object
     * @return Array
     */
    function toObject() {
        return (object) json_decode($this->toJSON());
    }

    /**
     * Default to return a JSON string
     * @return string
     */
    function __toString() {
        return $this->toJSON();
    }

    /**
     * Converts a JSON strings values to the current object for instantiating the object
     * @param $jsonString
     */
    function fromJSON($jsonString) {
        $jsonObject = json_decode($jsonString);

        if (!empty($jsonObject)) {
            foreach ($jsonObject as $varName => $varValue) {
                $this->$varName = $varValue;
            }
        }

    }

    function populateObject($sql) {
        //single object
        $DEB = Ruth::getOBJECT("DEB");

        if (!empty($this->id) || isset($this->id)) {
            $record = $DEB->getRow($sql);
            if (!empty($record)) {
                //map to the memory record and the object
                $this->mapRecord($record);
            }
        } else { //multiple object
            $records = $DEB->getRows($sql);

            if (!empty($records)) {
                //map to the memory record and the object
                $this->clear();

                foreach ($records as $rid => $record) {
                    $newObject = "";
                    eval ('$newObject = new '.$this->mapping["object"].'();');
                    $newObject->mapRecord($record);
                    $this->append($newObject);

                }
            }
        }
    }


    /**
     * Exact matching for retrieving a field from the database
     * @param $fieldArray
     * @return $this
     */
    function getBy($fieldArray) {
        $DEB = Ruth::getOBJECT("DEB");

        //read from the database
        if (!empty($DEB)) {
            $sql = "select ";
            $fields = [];
            if (!empty ($this->mapping["fields"])) {
                foreach ($this->mapping["fields"] as $objectField => $field) {
                    $fields[] = $field["field"];
                }
            }

            if (empty($fields)) {
                $sql .= "*";
            } else {
                $sql .= join(",", $fields);
            }


            $sql .= " from {$this->mapping["table"]} ";
            $sql .= " where ";

            foreach ($fieldArray as $fieldName => $fieldValue) {
                $searchFieldName = $fieldName;
                if (!empty($this->mapping["fields"][$fieldName])) {
                    $searchFieldName = $this->mapping["fields"][$fieldName]["field"];
                }
                $where[] = "{$searchFieldName} = '$fieldValue'";
            }

            $sql .= join (" and ", $where);

            $this->populateObject($sql);
        } else {
            //TODO: something for me todo
            die("Need to write this to fetch objects from memory!");
        }

        return $this;
    }

    /**
     * Loose field matching
     * @param $fieldArray
     * @return $this
     */
    function getLike($fieldArray, $matchType=OLGA_MATCH_ENDING) {
        $DEB = Ruth::getOBJECT("DEB");
        //read from the database
        if (!empty($DEB)) {
            $sql = "select ";
            $fields = [];
            if (!empty ($this->mapping["fields"])) {
                foreach ($this->mapping["fields"] as $objectField => $field) {
                    $fields[] = $field["field"];
                }
            }

            if (empty($fields)) {
                $sql .= "*";
            } else {
                $sql .= join(",", $fields);
            }

            $sql .= " from {$this->mapping["table"]} ";
            $sql .= " where ";

            foreach ($fieldArray as $fieldName => $fieldValue) {
                $searchFieldName = $fieldName;
                if (!empty($this->mapping["fields"][$fieldName])) {
                    $searchFieldName = $this->mapping["fields"][$fieldName]["field"];
                }
                switch ($matchType) {
                    case OLGA_MATCH_ANY:
                        $where[] = "upper({$searchFieldName}) like upper('%{$fieldValue}%')";
                        break;
                    case OLGA_MATCH_BEGINNING:
                        $where[] = "upper({$searchFieldName}) like upper('%{$fieldValue}')";
                        break;
                    case OLGA_MATCH_ENDING:
                        $where[] = "upper({$searchFieldName}) like upper('{$fieldValue}%')";
                        break;
                    default:
                        die("Invalid match type : USE OLGA_MATCH_ANY,OLGA_MATCH_BEGINNING,OLGA_MATCH_ENDING");
                        break;
                }
            }

            $sql .= join (" and ", $where);

            $this->populateObject($sql);
        } else {
            //TODO: something for me todo
            die("Need to write this to fetch objects from memory!");
        }
        return $this;
    }



    /**
     * Custom Object JSON encoder, thanks to boukeversteegh at gmail dot com, modified by Andre van Zuydam to ignore dynamic getters and setters
     * @param $data
     * @return string
     */
    function __toJson ( $data ) {
        if( is_array($data) || is_object($data) ) {
            $islist = is_array($data) && ( empty($data) || array_keys($data) === range(0,count($data)-1) );
            if( $islist ) {
                $json = '[' . join(',', array_map([$this, '__toJson'],  $data) ) . ']';
            } else {
                $items = Array();

                if (is_object($data)) {
                    $data = (array) $data;
                }

                foreach(  $data as $key => $value ) {
                    $isClosure = false;

                    if (is_object($value) && get_class($value) === "Closure") {
                        $isClosure = true;
                    }



                    if (!$isClosure && $key !== "arrayObjects"  && $key !== "mapping" && $key !== "validation" && $key != "DEBUG" && $key != "javascript" && $key != "errors") {
                        $items[] = $this->__toJson("$key") . ':' . $this->__toJson( $value);
                    }
                }

                $json = '{' . implode(',', $items) . '}';
            }
        } elseif( is_string($data) ) {

            # Escape non-printable or Non-ASCII characters.
            # I also put the \\ character first, as suggested in comments on the 'addclashes' page.
            $string = '"' . addcslashes($data, "\\\"\n\r\t/" . chr(8) . chr(12)) . '"';
            $json    = '';
            $len    = strlen($string);
            # Convert UTF-8 to Hexadecimal Codepoints.
            for( $i = 0; $i < $len; $i++ ) {

                $char = $string[$i];
                $c1 = ord($char);

                # Single byte;
                if( $c1 <128 ) {
                    $json .= ($c1 > 31) ? $char : sprintf("\\u%04x", $c1);
                    continue;
                }

                # Double byte
                $c2 = ord($string[++$i]);
                if ( ($c1 & 32) === 0 ) {
                    $json .= sprintf("\\u%04x", ($c1 - 192) * 64 + $c2 - 128);
                    continue;
                }

                # Triple
                $c3 = ord($string[++$i]);
                if( ($c1 & 16) === 0 ) {
                    $json .= sprintf("\\u%04x", (($c1 - 224) <<12) + (($c2 - 128) << 6) + ($c3 - 128));
                    continue;
                }

                # Quadruple
                $c4 = ord($string[++$i]);
                if( ($c1 & 8 ) === 0 ) {
                    $u = (($c1 & 15) << 2) + (($c2>>4) & 3) - 1;

                    $w1 = (54<<10) + ($u<<6) + (($c2 & 15) << 2) + (($c3>>4) & 3);
                    $w2 = (55<<10) + (($c3 & 15)<<6) + ($c4-128);
                    $json .= sprintf("\\u%04x\\u%04x", $w1, $w2);
                }
            }
        } else {
            # int, floats, bools, null

            $json = strtolower(var_export( $data, true ));
        }
        return $json;
    }


    /**
     * Call the dynamically instantiated getters & setters
     * @param $method
     * @param $args
     * @return mixed
     */
    function __call ($method, $args) {
        if (isset($this->$method)) {
            $func = $this->$method;
            return call_user_func_array($func, $args);
        }
        return false;
    }

    /**
     * Create the getters & setters
     */
    function createGetSet() {
        $variables = get_object_vars($this);
        foreach ($variables as $varName => $varValue) {

            if ($varName != "arrayObjects" && $varName !== "mapping" && $varName !== "validation" && $varName != "DEBUG" && $varName != "javascript" && $varName != "errors" && !method_exists($this, "get" . UcWords($varName)) && !method_exists($this, "set" . UcWords($varName)) ) {

                $getter = "get" . UcWords($varName);
                $setter = "set" . UcWords($varName);
                eval ('
                    $this->' . $getter . ' = function () {
                        return "{$this->' . $varName . '}";
                    };

                    $this->' . $setter . ' = function ($value) {
                        $this->' . $varName . ' = $value;
                        return $this;
                    };
                    ');
            }

        }
    }

    /**
     * Constructor which will make getters & setters
     * @param string $jsonString
     */
    function __construct($jsonString="") {
        if (!empty($jsonString)) {
            $this->fromJSON($jsonString);
        }
        $this->createGetSet();
    }

    /**
     * Return the current element
     * @link http://php.net/manual/en/iterator.current.php
     * @return mixed Can return any type.
     * @since 5.0.0
     */
    public function current()
    {
        return current($this->arrayObjects);
    }

    /**
     * Move forward to next element
     * @link http://php.net/manual/en/iterator.next.php
     * @return void Any returned value is ignored.
     * @since 5.0.0
     */
    public function next()
    {
        return next ($this->arrayObjects);
    }

    /**
     * Return the key of the current element
     * @link http://php.net/manual/en/iterator.key.php
     * @return mixed scalar on success, or null on failure.
     * @since 5.0.0
     */
    public function key()
    {
        return key($this->arrayObjects);
    }

    /**
     * Checks if current position is valid
     * @link http://php.net/manual/en/iterator.valid.php
     * @return boolean The return value will be casted to boolean and then evaluated.
     * Returns true on success or false on failure.
     * @since 5.0.0
     */
    public function valid()
    {
        $key = key($this->arrayObjects);
        $var = ($key !== NULL && $key !== FALSE);
        return $var;
    }

    /**
     * Rewind the Iterator to the first element
     * @link http://php.net/manual/en/iterator.rewind.php
     * @return void Any returned value is ignored.
     * @since 5.0.0
     */
    public function rewind()
    {
        reset($this->arrayObjects);
    }

    /**
     * Give a count of the records in the object
     * @return int
     */
    public function count() {
        return count($this->arrayObjects);
    }

    /**
     * Method to clear all the objects from the system
     * @return bool
     */
    public function clear() {
        $this->arrayObjects = [];
        return true;
    }

    //Validation Section
    function validName ($input, $checkValid = false ) {
        $result = $this->validString($input);
        return $result;
    }

    function validString ($input) {
        return is_string($input) && $input !== "";
    }

    function validNumber ($input) {
        return is_numeric($input) && $input !== "";
    }

    function validEmail ($input, $briteVerify = false) {
        $result = true;
        if (!filter_var($input, FILTER_VALIDATE_EMAIL)) {
            $result = false;
        }
        return $result;
    }

    function validPhone ($input, $briteVerify = false ) {
        $result = true;
        if (!$this->validNumber(str_replace (" ", "", str_replace ("-", "", str_replace ( ")", "", str_replace ("(", "", str_replace ("+", "", str_replace ("-", "", $input ) ))))))) {
            $result = false;
        }
        return $result;
    }

    /**
     * This function will return either true or object with all the invalid fields
     * @param $_REQUEST $requestArray
     * @param JSON $requestTypes example '"street_address":{"string": "true"},"state":{"string": "true", "maxlength": "2"}'
     */
    function validateForm ($requestArray, $requestTypes) {
        $requestTypes = json_decode("{".$requestTypes."}");
        $valid = true;
        $this->errors = [];
        if ($this->DEBUG) echo "<pre>";
        foreach ($requestArray as $requestName => $requestValue) {
            if (!empty($requestTypes->$requestName)) {
                $this->debug("Validate {$requestName}");

                //Validate String
                if (!empty($requestTypes->$requestName->string)) {
                    $this->debug("Validating String");
                    if (!$this->validString($requestValue)) {
                        $this->errors[$requestName] = (!empty($requestTypes->$requestName->message)) ? $requestTypes->$requestName->message : $requestName." is not a valid string";
                        $this->javascript .= $this->hookError ($requestName, $this->errors[$requestName]);
                        $valid = false;
                    }

                }

                //Validate Number
                if (!empty($requestTypes->$requestName->number)) {
                    $this->debug("Validating Number");
                    if (!$this->validNumber($requestValue)) {
                        $this->errors[$requestName] = (!empty($requestTypes->$requestName->message)) ? $requestTypes->$requestName->message : $requestName." is not a valid number";
                        $this->javascript .= $this->hookError ($requestName, $this->errors[$requestName]);
                        $valid = false;
                    }

                }

                //Validate Name
                if (!empty($requestTypes->$requestName->name)) {
                    $this->debug("Validating Name");
                    $validate = !empty($requestTypes->$requestName->validate) && $requestTypes->$requestName->validate;
                    if (!$this->validName($requestValue, $validate)) {
                        $this->errors[$requestName] = (!empty($requestTypes->$requestName->message)) ? $requestTypes->$requestName->message : $requestName." is not a valid name";
                        $this->javascript .= $this->hookError ($requestName, $this->errors[$requestName]);
                        $valid = false;
                    }

                }

                //Validate Phone
                if (!empty($requestTypes->$requestName->phone)) {
                    $this->debug("Validating Phone Number");
                    $validate = !empty($requestTypes->$requestName->validate) && $requestTypes->$requestName->validate;
                    if (!$this->validPhone($requestValue, $validate)) {
                        $this->errors[$requestName] = (!empty($requestTypes->$requestName->message)) ? $requestTypes->$requestName->message : $requestName." is not a valid phone number";
                        $this->javascript .= $this->hookError ($requestName, $this->errors[$requestName]);
                        $valid = false;
                    }
                }

                //Validate Email
                if (!empty($requestTypes->$requestName->email)) {
                    $validate = !empty($requestTypes->$requestName->validate) && $requestTypes->$requestName->validate;
                    $this->debug("Validating Email Address");
                    if (!$this->validEmail($requestValue, $validate)) {
                        $this->errors[$requestName] = (!empty($requestTypes->$requestName->message)) ? $requestTypes->$requestName->message : $requestName." is not a valid email address";
                        $this->javascript .= $this->hookError ($requestName, $this->errors[$requestName]);
                        $valid = false;
                    }

                }

                //Validate the max length
                if ($valid && isset($requestTypes->$requestName->maxlength)) {
                    if (strlen($requestValue) > $requestTypes->$requestName->maxlength) {
                        $this->errors[$requestName] = (!empty($requestTypes->$requestName->message)) ? $requestTypes->$requestName->message : $requestName." length should be less than equal to ".$requestTypes->$requestName->maxlength;
                        $this->javascript .= $this->hookError ($requestName, $this->errors[$requestName]);
                        $valid = false;
                    }
                }

                //Validate the min length
                if ($valid && isset($requestTypes->$requestName->minlength)) {
                    if (strlen($requestValue) < $requestTypes->$requestName->minlength) {
                        $this->errors[$requestName] = (!empty($requestTypes->$requestName->message)) ? $requestTypes->$requestName->message : $requestName." length should be greater than equal to ".$requestTypes->$requestName->minlength;
                        $this->javascript .= $this->hookError ($requestName, $this->errors[$requestName]);
                        $valid = false;
                    }
                }

                //Validate the max value
                if ($valid && isset($requestTypes->$requestName->maxvalue)) {
                    if ($requestValue > $requestTypes->$requestName->maxvalue) {
                        $this->errors[$requestName] = (!empty($requestTypes->$requestName->message)) ? $requestTypes->$requestName->message : $requestName." value should not be greater than  ".$requestTypes->$requestName->maxvalue;
                        $this->javascript .= $this->hookError ($requestName, $this->errors[$requestName]);
                        $valid = false;
                    }
                }

                //Validate the min value
                if ($valid && isset($requestTypes->$requestName->minvalue)) {

                    if ($requestValue < $requestTypes->$requestName->minvalue) {
                        $this->errors[$requestName] = (!empty($requestTypes->$requestName->message)) ? $requestTypes->$requestName->message : $requestName." value should not be less than ".$requestTypes->$requestName->minvalue;
                        $this->javascript .= $this->hookError ($requestName, $this->errors[$requestName]);
                        $valid = false;
                    }
                }


            }
        }

        $this->debug(print_r ($this->errors, 1));
        if ($this->DEBUG) echo "</pre>";
        return $valid;
    }

    /**
     * Uses minifyjs so be aware of this
     * @param type $fieldName
     * @param type $message
     */
    function hookError ($fieldName, $message) {

        return script("
            if (document.getElementById('error{$fieldName}') == null) {
                newNode = document.createElement('span');
                newNode.setAttribute('style', 'color:red');
                newNode.setAttribute('class', 'formError');
                newNode.setAttribute('id', 'error{$fieldName}');
                newNode.innerHTML = '{$message}';
                referenceNode = document.getElementById('{$fieldName}');
                referenceNode.parentNode.insertBefore(newNode, referenceNode.nextSibling);
            } else {
              document.getElementById('error{$fieldName}').innerHTML = '{$message}';
              document.getElementById('error{$fieldName}').style.display = 'block';
            }
        ")."";

    }

    function getErrors(){
        return $this->errors;
    }

    function getErrorScript() {
        return $this->javascript;
    }

    function failed() {
        return (count($this->errors) > 0) ? true : false;
    }

}