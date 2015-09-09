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
    var $arrayObjects = []; //an array or collection of objects of the same type as this (we probably need to check if it is the same)
    /**
     * Method to get the results from XCache into the object
     */
    function populateFromXCache() {
        if (function_exists("xcache_get")) {
            if (!empty($this->id)) { //this must be a single record, we will need to fetch it by its unique id in memory
                $json = unserialize(xcache_get("olgaSingleObject".get_class($this)."-".$this->id));
                $this->fromJSON($json);
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
            if (!empty($this->id)) { //save the single record
                $json = $this->toJSON();
                xcache_set("olgaSingleObject".get_class($this)."-".$this->id, serialize($json));
            } else {
                xcache_set("olgaArrayObjects".get_class($this), serialize($this->toJSON()));
            }
        } else {
            return false;
        }
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
        if (!empty($this->id)) { //single object - very easy
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
                    foreach ($records as $rid => $record) {
                        //create some objects in memory and add the object to this
                        $newObject = "";
                        eval ('$newObject = new '.$this->mapping["object"].'();');
                        $newObject->mapRecord ($record);
                        //add the object to memory
                        $newObject->createGetSet();
                        $newObject->save(true);

                        $this->append($newObject);
                    }

                    //save the object to memory
                    $this->save(true);

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
                            eval (' if (!empty($this->get'.ucwords($objectField).'())) {  $fieldValues[$field["field"]] = $this->get'.ucwords($objectField).'(); } ');

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
                                        eval (' if (!empty($object->get'.ucwords($objectField).'())) {  $fieldValues[$field["field"]] = $object->get'.ucwords($objectField).'(); } ');
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
     * A method to load the data from the database into the object in question
     */
    function load() {
        //see what type of object we have, do we have a database object or not,
        $DEB = Ruth::getOBJECT("DEB");
        //read from the database
        if (!empty($DEB)) {
            //see if we can grab from xcache first
            if (!$this->populateFromXCache()) {
                return $this->populateFromDebby($DEB);
            } else {
                return $this->populateFromDebby($DEB);
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



                    if (!$isClosure && $key !== "arrayObjects"  && $key !== "mapping") {

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

    //Save the object to disk or database or xcache in a structure
    function persist() {
        return false;
    }

    /**
     * Create the getters & setters
     */
    function createGetSet() {
        $variables = get_object_vars($this);
        foreach ($variables as $varName => $varValue) {
            if ($varName != "arrayObjects" && !method_exists($this, "get" . UcWords($varName)) && !method_exists($this, "set" . UcWords($varName)) ) {
                $getter = "get" . UcWords($varName);
                $setter = "set" . UcWords($varName);
                eval ('
                    $this->' . $getter . ' = function () {
                        return $this->' . $varName . ';
                    };

                    $this->' . $setter . ' = function ($value) {
                        $this->' . $varName . ' = $value;
                        $this->persist();
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
}