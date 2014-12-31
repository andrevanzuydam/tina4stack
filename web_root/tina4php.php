<?php
/**
 * Set default time zone
 */
date_default_timezone_set('America/Los_Angeles');

/**
 * Auto load the classes from the framework folder
 * @param type $class
 */
function __autoload ($class) {
  
  if (file_exists("{$class}.php")){
     require_once "{$class}.php";        
  }
    else 
  if (file_exists("project/{$class}.php")){
    require_once "/project/{$class}.php";      
  }
    else {
     require_once "/framework/{$class}.php"; 
  }
  
}