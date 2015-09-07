<?php

/**
 * Olga is a class which adds getters and setters to your existing object, it has methods to export the class to JSON and to import the class from JSON
 * User: Andre van Zuydam
 * Date: 2015-09-04
 * Time: 04:05 PM
 */
class Olga implements Iterator  {

    var $arrayObjects = []; //an array or collection of objects of the same type as this (we probably need to check if it is the same)

    /**
     * The clone function must recreate the object closures
     */
    function __clone() {
        //we will have to clone the closures
        foreach( (array) $this as $key => $value ) {
            if (is_callable($this->$key)) {
                if (substr($key,0,3) === "get") {
                    eval ('
                    $this->' . $key . ' = function () {
                        return $this->' . strtolower($key[3]).substr($key,4) . ';
                    };');
                }

                if (substr($key,0,3) === "set") {
                    eval ('
                    $this->' . $key . ' = function ($value) {
                         $this->' . strtolower($key[3]).substr($key,4) . ' = $value;
                    };');
                }

            }
        }
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
     * Converts a JSON strings values to the current object for instantiating the object
     * @param $jsonString
     */
    function fromJSON($jsonString) {
        $jsonObject = json_decode($jsonString);
        foreach ($jsonObject as $varName => $varValue) {
            $this->$varName = $varValue;
        }
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



                    if (!$isClosure && $key !== "arrayObjects") {

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
     * Constructor to make getters and setters
     */
    function __construct($jsonString="") {
        if (!empty($jsonString)) {
            $this->fromJSON($jsonString);
        }

        $variables = get_object_vars($this);
        foreach ($variables as $varName => $varValue) {
            if ($varName != "arrayObjects") {
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
}