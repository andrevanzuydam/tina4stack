<?php
/**
 * @ignore
 */
if (!function_exists("function_alias")) {   
  function function_alias ($original, $alias) {
   
    $args = func_get_args();
    assert('count($args) == 2');
    assert('is_string($original) && is_string($alias)');
   
    // valid function name - http://php.net/manual/en/functions.user-defined.php
    assert('preg_match(\'/^[a-zA-Z_\x7f-\xff][\\\\\\\\a-zA-Z0-9_\x7f-\xff]*$/\', $original) > 0');
    assert('preg_match(\'/^[a-zA-Z_\x7f-\xff][\\\\\\\\a-zA-Z0-9_\x7f-\xff]*$/\', $alias) > 0');
   
    $aliasNamespace = substr($alias, 0, strrpos($alias, '\\') !== false ? strrpos($alias, '\\') : 0);
    $aliasName = substr($alias, strrpos($alias, '\\') !== false ? strrpos($alias, '\\') + 1 : 0);
    $serializedOriginal = var_export($original, true);
   
    eval("
      namespace $aliasNamespace {
        function $aliasName () {
          return call_user_func_array($serializedOriginal, func_get_args());
        }
      }
    ");
  }
}
/**
 * @ignore
 */
class THTMLElement {
  private $childrenElements = array();
  private $attributes = array();
  private $openingtag = "";
  private $closingtag = "";
  private $content = "";
  private $requestvalue;
  private $atype = "Element";
  private $description = "Base HTML Element Class";
  private $acceptelements = array ("all");
  private $baseelements = array("tshape", "th1tag", "th2tag", "th3tag", "th4tag", "th5tag", "th6tag", "th7tag", "ttitle", "tptag", "tdivtag", "tformtag", "tbodytag", "thtmltag", "tprogress", "tq", "ts", "tstrong", "ttrack", "tem", "tcomment", "tabbr", "tdfn",
                                              "theadtag", "ttextinput", "tpasswordinput", "tbutton", "tsubmit", "tline", "tarticle", "tfieldset", "ttable", "ttr", "tth", "ttd", "tcheckbox", "tdd", "tdt", "tdl", "ttime", "tmark", "tmeta", "tmeter", "tobject", "toptgroup", "toutput",
											  "tinput", "tscripttag", "ttextarea", "tselect", "toption", "ta", "tstyle", "tbr", "tspan", "tlabel", "tb", "ti", "tu", "tlegend", "tul", "tol", "tli", "tcite", "tvar", "twbr", "tstrike","tdel", "tcolgroup", "tblockquote", "tbig", "tsmall", "tfigcaption", "tmap",
											  "tpre", "tthead", "ttbody", "ttfoot", "tsass", "tcoffeescript", "tiframe", "tnav", "timg", "tfooter", "tsection", "talink", "thr", "tsub", "tsup", "taside", "tbase", "tbdo", "tdatalist", "tcode", "tarea", "taudio", "tsource", "ttcanvas", "tcaption", "tcenter", "tfigure");
  
  function set_openingtag ($tag) {
    $this->openingtag = $tag;
  }
  
  function get_openingtag () {
    return $this->openingtag;
  }
  
  function set_closingtag ($tag) {
    $this->closingtag = $tag;
  }  
  
  function get_closingtag () {
    return $this->closingtag;
  }  
  
  function set_attribute ($aattribute, $value="") {
    
    if (is_array($aattribute)) {
      $this->attributes [] = $aattribute;
    }
      else {
      $found = false;
      //search for a particular attribute
      foreach ($this->attributes as $aid => $attribute) {
    	  if (is_array ($attribute)) {
      		foreach ($attribute as $aaid => $attr) {
      		   if (strtolower($aaid) == strtolower($aattribute)) { 
      			   $this->attributes[$aid][$aaid] = $value;
               $found = true; 
      		   }
      		} 
    	  }
    	   else {
    	     if (strtolower($aid) == strtolower($aattribute)) { 
    	        $this->attributes[$aid] = $value;
              $found = true;
    	     }
    	  } 
    	 }
       
       //we need to add the attribute
       if (!$found) {
         $this->attributes[] = array ($aattribute => $value);
       }
       
    }  
	
    return true;
  }
  
  
  function get_attributes () {
    return $this->attributes;
  }
  
  function get_attribute ($sname) {
    $avalue = "";
    foreach ($this->attributes as $aid => $attribute) {
	  if (is_array ($attribute)) {
		foreach ($attribute as $aaid => $attr) {
		   if (strtolower($aaid) == strtolower($sname)) { 
			 $avalue = $attr; 
			 break; 
		   }
		} 
	  }
	   else {
	     if (strtolower($aid) == strtolower($sname)) { 
			 $avalue = $attribute; 
			 break; 
		  }
	  } 
	}
    return $avalue;
  }
  
  
  function get_name () {
    $aname = $this->get_attribute ("name");
	if($aname == "") $aname = "unknown";
    return $aname;
  }
  
  
  
  
  function get_required () {
    $required = $this->get_attribute ("required");
	if($required == "") $required = false;
	return $required;
  }
  
  function get_childrenElements() {
    return $this->childrenElements;
  }
  
  function get_children ($element=null) {
    if ($element == null) {
      $children = $this->get_childrenElements();
      $element = $this;
    }
      else {
      $children = $element->get_childrenElements();
    }  
     
   // print_r ($children);
    if (count ($children) > 0) {   
      foreach ($children as $cid => $child) {
        if (is_object ($child)) {
		if (count($child->get_childrenElements()) > 0) { 
		  $subkids = $this->get_children ($child);
                  $children = array_merge ($children, $subkids);
		}
        }
      }
    }

    
    return $children;
  }
  
  function set_children ( $element ) {
    //clear the elements
   $this->childrenElements = array();
   //add the new children
   $this->append ( $element ); 
  }
  
  //function appends another element to the mix
  function append ( $element ) {
     $this->childrenElements[] = $element;
  }
  
  //this function is what makes everything happen as far as attributes etc go
  function initialize () {
    //blank for implementing
	
	//go through the attributes see if any of them have a name propery
  }
  
  function set_value ( $avalue ) {
    $this->requestvalue = $avalue;
  }
  
  function get_value () {
    return $this->requestvalue;
  }
    
  function set_type ( $atype ) {
    $this->atype = $atype;
  }
	
  function get_type () {
    return $this->atype;
  }	
	
  function set_caption ( $caption ) {
    $this->content = $caption;
  }
 
  function get_caption ( ) {
    return $this->content;
  }
  
  function set_content ( $content ) {
    if (is_object ( $content ) ) {
      $this->set_children ( $content ); 
    }
      else {
      $this->content = $content;  
    }
  }

  function get_content ( ) {
    return $this->content;
  }
  
  function __construct ($args="") {
    $arguments = func_get_args ();
    //check the arguments - expecting tag, value for the tag, properties, other elements
	//can take a variable number of arguments inclusing elements , elements are created on the fly
    
		
    foreach ( $arguments as $argid => $argument ) {
		 //add the arguments to a list
		
		 if ( is_object ($argument) ) {
                   if (in_array (get_class($argument) , $this->baseelements, true)  ) {
		                  $this->childrenElements [] = $argument;
                   }  
		 }
		   else  
		 if ( is_array($argument) ) {
		    $argument = array_flatten ($argument);  
		     //checking to see if our array might hold an array of elements like for a table.
			 $elementsfound = false;
			 foreach ($argument as $aid => $possibleelement) {
			   if (is_object ($possibleelement)) { 
                                 if ( in_array (get_class($possibleelement) , $this->baseelements, true)  ) {
					 $this->childrenElements [] = $possibleelement;
					 $elementsfound = true;
				 }			 
			   }
                            else {
                             //print_r ($possibleelement); 
                           }          	
			 }
			 
			 //don't go trying to add to attributes if this is not the case
			 if (!$elementsfound) {
		           $this->set_attribute ($argument);
		     }
		 }
		   else {
                     if ($argument != "") {  
                         
                         $this->childrenElements [] = $argument; 
                         
                     }  
		     //echo "We have a value ;;;{$argument};;;";
                      
                     //$this->content .= $argument;
		 } 
		  
	 }
	 
	 //here we get the post values from the system	 	 
	 foreach ($this->attributes as $aid => $attribute) {	 
		 foreach ($attribute as $aname => $avalue) {
			 if (strtoupper ($aname) == "NAME") {
				 if (isset ($_REQUEST[$avalue])) {
			 	   $this->requestvalue = $_REQUEST[$avalue];
				 }
			  }
		 }
	 }
	  
	 $this->initialize ();
  }
  
  function compile_html () {
	 //see how we can do this
	 //here go the subtags & attributes
         $html = ""; 
	 $attributes = "";
	 foreach ($this->attributes as $aid => $avalue) {
	    foreach ($avalue as $vkey => $value) {
	      $attributes .= " {$vkey}=\"{$value}\"";
		}
	 }
	  
	 if ( strpos($this->openingtag, "[content]") !== false ) {
	   $openingtag = str_replace ("[attributes]", $attributes, $this->openingtag);
	   $openingtag = str_replace ("[content]", $this->content, $openingtag );
	   $html .= $openingtag;
	 } 
	   else {
	   $html .= str_replace ("[attributes]", $attributes, $this->openingtag);
	   $html .= $this->content;
	 }
	 
         //print_r ($this->childrenElements);
	 foreach ($this->childrenElements as $childid => $element) {
            if (is_object($element)){ 
	      $html .= $element->compile_html();
            }
              else {
              $html .= $element;                
            } 
	 }
	 
	 
	 if ($this->closingtag != "") { 
	   $html .= str_replace ("[attributes]", $attributes, $this->closingtag);
	 }
	 
	 return $html;
  }
  
  //we're going to use this to put together the elements
  function __toString () {
     return $this->compile_html ();
  }
}


/*SHAPE TAG =======================================*/
class tshape extends THTMLElement {
   //this function is what makes everything happen as far as attributes etc go
  function initialize () {
    $this->set_openingtag("");
	  $this->set_closingtag("");
  }
}

//function that outputs things
function shape ($arguments) {
  $arguments = func_get_args();
  $rc = new ReflectionClass('tshape');
  return $rc->newInstanceArgs( $arguments ); 
}
/*END SHAPE TAG ===================================*/

/*COFFEESCRIPT TAG =======================================*/
class tcoffeescript extends THTMLElement {
   //this function is what makes everything happen as far as attributes etc go
  function initialize () {
    $this->set_openingtag("<SCRIPT[]>");
	  $this->set_closingtag("</SCRIPT>");
  }
  
  function compile_html () {
	 //see how we can do this
	 //here go the subtags & attributes
   
   //see if we can make a coffee script compiler
   
   if (file_exists (dirname(__FILE__)."/coffeescript/Init.php")) {
      require_once dirname(__FILE__)."/coffeescript/Init.php";
      Coffeescript\Init::load();
      $this->content = CoffeeScript\Compiler::compile($this->get_content(), array("filename"=>"test.coffee", "bare" => true));
   }
     else {
     $this->content = $this->get_content();      
   }
    
   
   $html = ""; 
	 $attributes = "";
   $this->openingtag = $this->get_openingtag();
   $this->closingtag = $this->get_closingtag();
   $this->attributes = $this->get_attributes();
   $this->childrenElements = $this->get_childrenElements();
      
	 foreach ($this->attributes as $aid => $avalue) {
	    foreach ($avalue as $vkey => $value) {
	      $attributes .= " {$vkey}=\"{$value}\"";
		}
	 }
	  
	 if ( strpos($this->openingtag, "[content]") !== false ) {
	   $openingtag = str_replace ("[attributes]", $attributes, $this->openingtag);
	   $openingtag = str_replace ("[content]", $this->content, $openingtag );
	   $html .= $openingtag;
	 } 
	   else {
	   $html .= str_replace ("[attributes]", $attributes, $this->openingtag);
	   $html .= $this->content;
	 }
	 
	 foreach ($this->childrenElements as $childid => $element) {
	    
             $html .= $element->compile_html();
	 }
	 
	 
	 if ($this->closingtag != "") { 
	   $html .= str_replace ("[attributes]", $attributes, $this->closingtag);
	 }
	 
	 return $html;
  }
  
}

//function that outputs things
function coffeescript ( $arguments = "" ) {
  $arguments = func_get_args();
  $rc = new ReflectionClass('tcoffeescript');
  return $rc->newInstanceArgs( $arguments ); 
}

function_alias ("coffeescript", "cs");

/*END COFFEESCRIPT TAG ===================================*/


/*SASS TAG =======================================*/
class tsass extends THTMLElement {
   //this function is what makes everything happen as far as attributes etc go
  function initialize () {
    $this->set_openingtag("<STYLE>");
	  $this->set_closingtag("</STYLE>");
  }
  
  function compile_html () {
	 //see how we can do this
	 //here go the subtags & attributes
   
   //see if we can make a sass script compiler
   
   if (file_exists (dirname(__FILE__)."/scssphp/scss.inc.php")) {
      require_once dirname(__FILE__)."/scssphp/scss.inc.php";
      $scss = new scssc();
      $this->content = $scss->compile ($this->get_content());
   }
     else {
     $this->content = $this->get_content ();
   }
     
   
   $html = ""; 
	 $attributes = "";
   $this->openingtag = $this->get_openingtag();
   $this->closingtag = $this->get_closingtag();
   $this->attributes = $this->get_attributes();
   $this->childrenElements = $this->get_childrenElements();
      
	 foreach ($this->attributes as $aid => $avalue) {
	   foreach ($avalue as $vkey => $value) {
	      $attributes .= " {$vkey}=\"{$value}\"";
		 }
	 }
	  
	 if ( strpos($this->openingtag, "[content]") !== false ) {
	   $openingtag = str_replace ("[attributes]", $attributes, $this->openingtag);
	   $openingtag = str_replace ("[content]", slib_compress_script ( $this->content ), $openingtag );
	   $html .= $openingtag;
	 } 
	   else {
	   $html .= str_replace ("[attributes]", $attributes, $this->openingtag);
	   $html .= slib_compress_script ( $this->content );
	 }
	 
	 foreach ($this->childrenElements as $childid => $element) {
	    $html .= $element->compile_html();
	 }
	 
	 
	 if ($this->closingtag != "") { 
	   $html .= str_replace ("[attributes]", $attributes, $this->closingtag);
	 }
	 
	 return $html;
  }
  
  
}

//function that outputs things
function sass ( $arguments = "" ) {
  $arguments = func_get_args();
  $rc = new ReflectionClass('tsass');
  return $rc->newInstanceArgs( $arguments ); 
}

function_alias ("sass", "scss");

/*END SASS TAG ===================================*/




/*HTML TAG =======================================*/
class thtmltag extends THTMLElement {
   //this function is what makes everything happen as far as attributes etc go
  function initialize () {
    $this->set_openingtag("<!DOCTYPE html><HTML[attributes]>");
  	$this->set_closingtag("</HTML>");
  }
}

//function that outputs things
function htmltag ( $arguments = "" ) {
  $arguments = func_get_args();
  $rc = new ReflectionClass('thtmltag');
  return $rc->newInstanceArgs( $arguments ); 
}

function_alias ("htmltag", "html");
/*END HTML TAG ===================================*/


/*SECTION TAG =======================================*/
class tsection extends THTMLElement {
   //this function is what makes everything happen as far as attributes etc go
  function initialize () {
    $this->set_openingtag("<SECTION[attributes]>");
	  $this->set_closingtag("</SECTION>");
  }
}

//function that outputs things
function section ( $arguments = "" ) {
  $arguments = func_get_args();
  $rc = new ReflectionClass('tsection');
  return $rc->newInstanceArgs( $arguments ); 
}

/*END SECTION TAG ===================================*/



/*HEAD TAG =======================================*/
class theadtag extends THTMLElement {
   //this function is what makes everything happen as far as attributes etc go
  function initialize () {
    $this->set_openingtag ( "<HEAD[attributes]>" );
	  $this->set_closingtag ( "</HEAD>" );
  }
}

//function that outputs things
function headtag ( $arguments = "" ) {
  $arguments = func_get_args();
  $rc = new ReflectionClass('theadtag');
  return $rc->newInstanceArgs( $arguments ); 
}

function_alias ("headtag", "head");
/*END HEAD TAG ====================================*/

/*TITLE TAG =======================================*/
class ttitle extends THTMLElement {
   //this function is what makes everything happen as far as attributes etc go
  function initialize () {
    $this->set_openingtag ( "<TITLE[attributes]>" );
	  $this->set_closingtag ( "</TITLE>" );
  }
}

//function that outputs things
function title ( $arguments = "" ) {
  $arguments = func_get_args();
  $rc = new ReflectionClass('ttitle');
  return $rc->newInstanceArgs( $arguments ); 
}
/*END TITLE TAG ====================================*/



/*BODY TAG =======================================*/
class tbodytag extends THTMLElement {
   //this function is what makes everything happen as far as attributes etc go
  function initialize () {
    $this->set_openingtag("<BODY[attributes]>");
	  $this->set_closingtag("</BODY>");
  }
}

//function that outputs things
function bodytag ( $arguments = "" ) {
  $arguments = func_get_args();
  $rc = new ReflectionClass('tbodytag');
  return $rc->newInstanceArgs( $arguments ); 
}

function_alias ("bodytag", "body");
/*END BODY TAG ===================================*/

/*IFRAME TAG =====================================*/
class tiframe extends THTMLElement {
   //this function is what makes everything happen as far as attributes etc go
  function initialize () {
    $this->set_openingtag("<IFRAME[attributes]>");
	  $this->set_closingtag("<P>Your browser does not support iframes!</P></IFRAME>");
  }
}

//function that outputs things
function iframe ( $arguments = "" ) {
  $arguments = func_get_args();
  $rc = new ReflectionClass('tiframe');
  return $rc->newInstanceArgs( $arguments ); 
}
/*END IFRAME TAG ===============================*/

/*IMG TAG =======================================*/
class timg extends THTMLElement {
   //this function is what makes everything happen as far as attributes etc go
  function initialize () {
    $this->set_openingtag ( "<IMG alt=\"" );
	  $this->set_closingtag ( "\"[attributes]/>" );
  }
}

//function that outputs things
function img ( $arguments = "" ) {
  $arguments = func_get_args();
  $rc = new ReflectionClass('timg');
  return $rc->newInstanceArgs( $arguments ); 
}
/*END IMG TAG ====================================*/

/*HEADER TAG = DO NOT CONFUSE WITH HEAD!===============*/
class theadertag extends THTMLElement {
   //this function is what makes everything happen as far as attributes etc go
  function initialize () {
    $this->set_openingtag("<HEADER[attributes]>");
	  $this->set_closingtag("</HEADER>");
  }
}

//function that outputs things
function headertag ( $arguments = "" ) {
  $arguments = func_get_args();
  $rc = new ReflectionClass('theadertag');
  return $rc->newInstanceArgs( $arguments ); 
}
function_alias ("headertag", "aheader");
/*END HEADER TAG ===============================*/

/*FOOTER TAG = DO NOT CONFUSE WITH TFOOT!===============*/
class tfooter extends THTMLElement {
   //this function is what makes everything happen as far as attributes etc go
  function initialize () {
    $this->set_openingtag("<FOOTER[attributes]>");
	  $this->set_closingtag("</FOOTER>");
  }
}

//function that outputs things
function footer ( $arguments = "" ) {
  $arguments = func_get_args();
  $rc = new ReflectionClass('tfooter');
  return $rc->newInstanceArgs( $arguments ); 
}
/*END FOOTER TAG ===============================*/

/*PRE TAG =======================================*/
class tpre extends THTMLElement {
   //this function is what makes everything happen as far as attributes etc go
  function initialize () {
    $this->set_openingtag("<PRE[attributes]>");
	  $this->set_closingtag("</PRE>");
  }
}

//function that outputs things
function pre ( $arguments = "" ) {
  $arguments = func_get_args();
  $rc = new ReflectionClass('tpre');
  return $rc->newInstanceArgs( $arguments ); 
}
/*END PRE TAG ===================================*/

/*HR TAG =====================================*/
class thr extends THTMLElement {
   //this function is what makes everything happen as far as attributes etc go
   function initialize() {
     $this->set_openingtag("<HR[attributes]>");
     $this->set_closingtag("");
   }
}

//function that outputs things
function hr ( $arguments = "" ) {
  $arguments = func_get_args();
  $rc = new ReflectionClass('thr');
  return $rc->newInstanceArgs( $arguments );
}
/*END HR TAG =======================================*/

/*SUB TAG ========================================*/
class tsub extends THTMLElement {
  //this function is what makes everything happen as far as attributes etc go
  function initialize() {
    $this->set_openingtag("<SUB[attributes]>");
    $this->set_closingtag("</SUB>");
  }
}

//function that outputs things
function sub ( $arguments = "" ) {
  $arguments = func_get_args();
  $rc = new ReflectionClass('tsub');
  return $rc->newInstanceArgs( $arguments );
}
/*END SUB TAG ========================================*/

/*SUP TAG ========================================*/
class tsup extends THTMLElement {
  //this function is what makes everything happen as far as attributes etc go
  function initialize() {
    $this->set_openingtag("<SUP[attributes]>");
    $this->set_closingtag("</SUP>");
  }
}

//function that outputs things
function sup ( $arguments = "" ) {
  $arguments = func_get_args();
  $rc = new ReflectionClass('tsup');
  return $rc->newInstanceArgs( $arguments );
}
/*END SUP TAG ========================================*/

/*ASIDE TAG ========================================*/
class taside extends THTMLElement {
  //this function is what makes everything happen as far as attributes etc go
  function initialize() {
    $this->set_openingtag("<ASIDE[attributes]>");
    $this->set_closingtag("</ASIDE>");
  }
}

//function that outputs things
function aside ( $arguments = "" ) {
  $arguments = func_get_args();
  $rc = new ReflectionClass('taside');
  return $rc->newInstanceArgs( $arguments );
}
/*END ASIDE TAG ========================================*/

/*BASE TAG =====================================*/
class tbase extends THTMLElement {
   //this function is what makes everything happen as far as attributes etc go
   function initialize() {
     $this->set_openingtag("<BASE[attributes]>");
     $this->set_closingtag("");
   }
}

//function that outputs things
function base ( $arguments = "" ) {
  $arguments = func_get_args();
  $rc = new ReflectionClass('tbase');
  return $rc->newInstanceArgs( $arguments );
}
/*END BASE TAG =======================================*/

/*BDO TAG =====================================*/
class tbdo extends THTMLElement {
   //this function is what makes everything happen as far as attributes etc go
   function initialize() {
     $this->set_openingtag("<BDO[attributes]>");
     $this->set_closingtag("</BDO>");
   }
}

//function that outputs things
function bdo ( $arguments = "" ) {
  $arguments = func_get_args();
  $rc = new ReflectionClass('tbdo');
  return $rc->newInstanceArgs( $arguments );
}
/*END BDO TAG =======================================*/

/*DATALIST TAG =====================================*/
class tdatalist extends THTMLElement {
   //this function is what makes everything happen as far as attributes etc go
   function initialize() {
     $this->set_openingtag("<DATALIST[attributes]>");
     $this->set_closingtag("</DATALIST>");
   }
}

//function that outputs things
function datalist ( $arguments = "" ) {
  $arguments = func_get_args();
  $rc = new ReflectionClass('tdatalist');
  return $rc->newInstanceArgs( $arguments );
}
/*END DATALIST TAG =======================================*/

/*CITE TAG =====================================*/
class tcite extends THTMLElement {
   //this function is what makes everything happen as far as attributes etc go
   function initialize() {
     $this->set_openingtag("<CITE[attributes]>");
     $this->set_closingtag("</CITE>");
   }
}

//function that outputs things
function cite ( $arguments = "" ) {
  $arguments = func_get_args();
  $rc = new ReflectionClass('tcite');
  return $rc->newInstanceArgs( $arguments );
}
/*END CITE TAG =======================================*/

/*VAR TAG =====================================*/
class tvar extends THTMLElement {
   //this function is what makes everything happen as far as attributes etc go
   function initialize() {
     $this->set_openingtag("<VAR[attributes]>");
     $this->set_closingtag("</VAR>");
   }
}

//function that outputs things
function avar ( $arguments = "" ) {
  $arguments = func_get_args();
  $rc = new ReflectionClass('tvar');
  return $rc->newInstanceArgs( $arguments );
}
/*END VAR TAG =======================================*/

/*WBR TAG =======================================*/
class twbr extends THTMLElement {
  //this function is what makes everything happen as far as attributes etc go
  function initialize() {
    $this->set_openingtag("<WBR[attributes]>");
    $this->set_closingtag("</WBR>");
  }
}

//function that outputs things
function wbr ( $arguments = "" ) {
  $arguments = func_get_args();
  $rc = new ReflectionClass('twbr');
  return $rc->newInstanceArgs ( $arguments ) ;
}
/*END WBR TAG =======================================*/

/*DL TAG =======================================*/
class tdl extends THTMLElement {
  //this function is what makes everything happen as far as attributes etc go
  function initialize() {
    $this->set_openingtag("<DL[attributes]>");
    $this->set_closingtag("</DL>");
  }
}

//function that outputs things
function dl ( $arguments = "" ) {
  $arguments = func_get_args ();
  $rc = new ReflectionClass('tdl');
  return $rc->newInstanceArgs ( $arguments );
}
/*END DL TAG ========================================*/

/*DD TAG =======================================*/
class tdd extends THTMLElement {
  //this function is what makes everything happen as far as attributes etc go
  function initialize() {
    $this->set_openingtag("<DD[attributes]>");
    $this->set_closingtag("</DD>");
  }
}

//function that outputs things
function dd ( $arguments = "" ) {
  $arguments = func_get_args ();
  $rc = new ReflectionClass('tdd');
  return $rc->newInstanceArgs ( $arguments );
}
/*END DD TAG ========================================*/

/*DT TAG =======================================*/
class tdt extends THTMLElement {
  //this function is what makes everything happen as far as attributes etc go
  function initialize() {
    $this->set_openingtag("<DT[attributes]>");
    $this->set_closingtag("</DT>");
  }
}

//function that outputs things
function dt ( $arguments = "" ) {
  $arguments = func_get_args ();
  $rc = new ReflectionClass('tdt');
  return $rc->newInstanceArgs ( $arguments );
}
/*END DT TAG ========================================*/

/*DEL TAG =======================================*/
class tdel extends THTMLElement {
  //this function is what makes everything happen as far as attributes etc go
  function initialize() {
    $this->set_openingtag("<DEL[attributes]>");
    $this->set_closingtag("</DEL>");
  }
}

//function that outputs things
function del ( $arguments = "" ) {
  $arguments = func_get_args ();
  $rc = new ReflectionClass('tdel');
  return $rc->newInstanceArgs ( $arguments );
}
/*END DEL TAG ========================================*/

/*STRIKE TAG =======================================*/
class tstrike extends THTMLElement {
  //this function is what makes everything happen as far as attributes etc go
  function initialize() {
    $this->set_openingtag("<STRIKE[attributes]>");
    $this->set_closingtag("</STRIKE>");
  }
}

//function that outputs things
function strike ( $arguments = "" ) {
  $arguments = func_get_args ();
  $rc = new ReflectionClass('tstrike');
  return $rc->newInstanceArgs ( $arguments );
}
/*END STRIKE TAG ========================================*/

/*CODE TAG =======================================*/
class tcode extends THTMLElement {
  //this function is what makes everything happen as far as attributes etc go
  function initialize() {
    $this->set_openingtag("<CODE[attributes]>");
    $this->set_closingtag("</CODE>");
  }
}

//function that outputs things
function code ( $arguments = "" ) {
  $arguments = func_get_args ();
  $rc = new ReflectionClass('tcode');
  return $rc->newInstanceArgs ( $arguments );
}
/*END CODE TAG ========================================*/

/*COLGROUP TAG =======================================*/
class tcolgroup extends THTMLElement {
  //this function is what makes everything happen as far as attributes etc go
  function initialize() {
    $this->set_openingtag("<COLGROUP[attributes]>");
    $this->set_closingtag("</COLGROUP>");
  }
}

//function that outputs things
function colgroup ( $arguments = "" ) {
  $arguments = func_get_args ();
  $rc = new ReflectionClass('tcolgroup');
  return $rc->newInstanceArgs ( $arguments );
}
/*END COLGROUP TAG ========================================*/

/*VIDEO TAG =======================================*/
class tvideo extends THTMLElement {
  //this function is what makes everything happen as far as attributes etc go
  function initialize() {
    $this->set_openingtag("<VIDEO[attributes]>");
    $this->set_closingtag("</VIDEO>");
  }
}

//function that outputs things
function video ( $arguments = "" ) {
  $arguments = func_get_args ();
  $rc = new ReflectionClass('tvideo');
  return $rc->newInstanceArgs ( $arguments );
}
/*END VIDEO TAG ========================================*/

/*TIME TAG =======================================*/
class ttime extends THTMLElement {
  //this function is what makes everything happen as far as attributes etc go
  function initialize() {
    $this->set_openingtag("<TIME[attributes]>");
    $this->set_closingtag("</TIME>");
  }
}

//function that outputs things
function atime ( $arguments = "" ) {
  $arguments = func_get_args ();
  $rc = new ReflectionClass('ttime');
  return $rc->newInstanceArgs ( $arguments );
}
/*END TIME TAG ========================================*/

/*AREA TAG =======================================*/
class tarea extends THTMLElement {
  //this function is what makes everything happen as far as attributes etc go
  function initialize() {
    $this->set_openingtag("<AREA[attributes]>");
    $this->set_closingtag("");
  }
}

//function that outputs things
function area ( $arguments = "" ) {
  $arguments = func_get_args ();
  $rc = new ReflectionClass('tarea');
  return $rc->newInstanceArgs ( $arguments );
}
/*END AREA TAG ========================================*/

/*AUDIO TAG =======================================*/
class taudio extends THTMLElement {
  //this function is what makes everything happen as far as attributes etc go
  function initialize() {
    $this->set_openingtag("<AUDIO[attributes]>");
    $this->set_closingtag("</AUDIO>");
  }
}

//function that outputs things
function audio ( $arguments = "" ) {
  $arguments = func_get_args ();
  $rc = new ReflectionClass('taudio');
  return $rc->newInstanceArgs ( $arguments );
}
/*END AUDIO TAG ========================================*/

/*SOURCE TAG =======================================*/
class tsource extends THTMLElement {
  //this function is what makes everything happen as far as attributes etc go
  function initialize() {
    $this->set_openingtag("<SOURCE[attributes]>");
    $this->set_closingtag("");
  }
}

//function that outputs things
function source ( $arguments = "" ) {
  $arguments = func_get_args ();
  $rc = new ReflectionClass('tsource');
  return $rc->newInstanceArgs ( $arguments );
}
/*END SOURCE TAG ========================================*/

/*BLOCKQUOTE TAG =======================================*/
class tblockquote extends THTMLElement {
  //this function is what makes everything happen as far as attributes etc go
  function initialize() {
    $this->set_openingtag("<BLOCKQUOTE[attributes]>");
    $this->set_closingtag("</BLOCKQUOTE");
  }
}

//function that outputs things
function blockquote ( $arguments = "" ) {
  $arguments = func_get_args ();
  $rc = new ReflectionClass('tblockquote');
  return $rc->newInstanceArgs ( $arguments );
}
/*END BLOCKQUOTE TAG ========================================*/

/*BIG TAG =======================================*/
class tbig extends THTMLElement {
  //this function is what makes everything happen as far as attributes etc go
  function initialize() {
    $this->set_openingtag("<BIG[attributes]>");
    $this->set_closingtag("</BIG>");
  }
}

//function that outputs things
function big ( $arguments = "" ) {
  $arguments = func_get_args ();
  $rc = new ReflectionClass('tbig');
  return $rc->newInstanceArgs ( $arguments );
}
/*END BIG TAG ========================================*/

/*SMALL TAG =======================================*/
class tsmall extends THTMLElement {
  //this function is what makes everything happen as far as attributes etc go
  function initialize() {
    $this->set_openingtag("<SMALL[attributes]>");
    $this->set_closingtag("</SMALL>");
  }
}

//function that outputs things
function small ( $arguments = "" ) {
  $arguments = func_get_args ();
  $rc = new ReflectionClass('tsmall');
  return $rc->newInstanceArgs ( $arguments );
}
/*END SMALL TAG ========================================*/

/*CANVAS TAG =======================================*/
class ttcanvas extends THTMLElement {
  //this function is what makes everything happen as far as attributes etc go
  function initialize() {
    $this->set_openingtag("<CANVAS[attributes]>");
    $this->set_closingtag("</CANVAS>");
  }
}

//function that outputs things
function canvas ( $arguments = "" ) {
  $arguments = func_get_args ();
  $rc = new ReflectionClass('ttcanvas');
  return $rc->newInstanceArgs ( $arguments );
}
/*END CANVAS TAG ========================================*/

/*CAPTION TAG =======================================*/
class tcaption extends THTMLElement {
  //this function is what makes everything happen as far as attributes etc go
  function initialize() {
    $this->set_openingtag("<CAPTION[attributes]>");
    $this->set_closingtag("</CAPTION>");
  }
}

//function that outputs things
function caption ( $arguments = "" ) {
  $arguments = func_get_args ();
  $rc = new ReflectionClass('tcaption');
  return $rc->newInstanceArgs ( $arguments );
}
/*END CAPTION TAG ========================================*/

/*CENTER TAG =======================================*/
class tcenter extends THTMLElement {
  //this function is what makes everything happen as far as attributes etc go
  function initialize() {
    $this->set_openingtag("<CENTER[attributes]>");
    $this->set_closingtag("</CENTER>");
  }
}

//function that outputs things
function center ( $arguments = "" ) {
  $arguments = func_get_args ();
  $rc = new ReflectionClass('tcenter');
  return $rc->newInstanceArgs ( $arguments );
}
/*END CENTER TAG ========================================*/

/*FIGURE TAG =======================================*/
class tfigure extends THTMLElement {
  //this function is what makes everything happen as far as attributes etc go
  function initialize() {
    $this->set_openingtag("<FIGURE[attributes]>");
    $this->set_closingtag("</FIGURE>");
  }
}

//function that outputs things
function figure ( $arguments = "" ) {
  $arguments = func_get_args ();
  $rc = new ReflectionClass('tfigure');
  return $rc->newInstanceArgs ( $arguments );
}
/*END FIGURE TAG ========================================*/

/*FIGCAPTION TAG =======================================*/
class tfigcaption extends THTMLElement {
  //this function is what makes everything happen as far as attributes etc go
  function initialize() {
    $this->set_openingtag("<FIGCAPTION[attributes]>");
    $this->set_closingtag("</FIGCAPTION>");
  }
}

//function that outputs things
function figcaption ( $arguments = "" ) {
  $arguments = func_get_args ();
  $rc = new ReflectionClass('tfigcaption');
  return $rc->newInstanceArgs ( $arguments );
}
/*END FIGCAPTION TAG ========================================*/

/*MAP TAG =======================================*/
class tmap extends THTMLElement {
  //this function is what makes everything happen as far as attributes etc go
  function initialize() {
    $this->set_openingtag("<MAP[attributes]>");
    $this->set_closingtag("</MAP>");
  }
}

//function that outputs things
function map ( $arguments = "" ) {
  $arguments = func_get_args ();
  $rc = new ReflectionClass('tmap');
  return $rc->newInstanceArgs ( $arguments );
}
/*END MAP TAG ========================================*/


/*MARK TAG =======================================*/
class tmark extends THTMLElement {
  //this function is what makes everything happen as far as attributes etc go
  function initialize() {
    $this->set_openingtag("<MARK[attributes]>");
    $this->set_closingtag("</MARK>");
  }
}

//function that outputs things
function mark ( $arguments = "" ) {
  $arguments = func_get_args ();
  $rc = new ReflectionClass('tmark');
  return $rc->newInstanceArgs ( $arguments );
}
/*END MARK TAG ========================================*/

/*META TAG =======================================*/
class tmeta extends THTMLElement {
  //this function is what makes everything happen as far as attributes etc go
  function initialize() {
    $this->set_openingtag("<META[attributes]>");
    $this->set_closingtag("</META>");
  }
}

//function that outputs things
function meta ( $arguments = "" ) {
  $arguments = func_get_args ();
  $rc = new ReflectionClass('tmeta');
  return $rc->newInstanceArgs ( $arguments );
}
/*END META TAG ========================================*/

/*METER TAG =======================================*/
class tmeter extends THTMLElement {
  //this function is what makes everything happen as far as attributes etc go
  function initialize() {
    $this->set_openingtag("<METER[attributes]>");
    $this->set_closingtag("</METER>");
  }
}

//function that outputs things
function meter ( $arguments = "" ) {
  $arguments = func_get_args ();
  $rc = new ReflectionClass('tmeter');
  return $rc->newInstanceArgs ( $arguments );
}
/*END METER TAG ========================================*/

/*OBJECT TAG =======================================*/
class tobject extends THTMLElement {
  //this function is what makes everything happen as far as attributes etc go
  function initialize() {
    $this->set_openingtag("<OBJECT[attributes]>");
    $this->set_closingtag("</OBJECT>");
  }
}

//function that outputs things
function object ( $arguments = "" ) {
  $arguments = func_get_args ();
  $rc = new ReflectionClass('tobject');
  return $rc->newInstanceArgs ( $arguments );
}
/*END OBJECT TAG ========================================*/

/*OPTGROUP TAG =======================================*/
class toptgroup extends THTMLElement {
  //this function is what makes everything happen as far as attributes etc go
  function initialize() {
    $this->set_openingtag("<OPTGROUP[attributes]>");
    $this->set_closingtag("</OPTGROUP>");
  }
}

//function that outputs things
function optgroup ( $arguments = "" ) {
  $arguments = func_get_args ();
  $rc = new ReflectionClass('toptgroup');
  return $rc->newInstanceArgs ( $arguments );
}
/*END OPTGROUP TAG ========================================*/

/*OUTPUT TAG =======================================*/
class toutput extends THTMLElement {
  //this function is what makes everything happen as far as attributes etc go
  function initialize() {
    $this->set_openingtag("<OUTPUT[attributes]>");
    $this->set_closingtag("</OUTPUT>");
  }
}

//function that outputs things
function output ( $arguments = "" ) {
  $arguments = func_get_args ();
  $rc = new ReflectionClass('toutput');
  return $rc->newInstanceArgs ( $arguments );
}
/*END OUTPUT TAG ========================================*/

/*PROGRESS TAG =======================================*/
class tprogress extends THTMLElement {
  //this function is what makes everything happen as far as attributes etc go
  function initialize() {
    $this->set_openingtag("<PROGRESS[attributes]>");
    $this->set_closingtag("</PROGRESS>");
  }
}

//function that outputs things
function progress ( $arguments = "" ) {
  $arguments = func_get_args ();
  $rc = new ReflectionClass('tprogress');
  return $rc->newInstanceArgs ( $arguments );
}
/*END PROGRESS TAG ========================================*/

/*Q TAG =======================================*/
class tq extends THTMLElement {
  //this function is what makes everything happen as far as attributes etc go
  function initialize() {
    $this->set_openingtag("<Q[attributes]>");
    $this->set_closingtag("</Q>");
  }
}

//function that outputs things
function q ( $arguments = "" ) {
  $arguments = func_get_args ();
  $rc = new ReflectionClass('tq');
  return $rc->newInstanceArgs ( $arguments );
}
/*END Q TAG ========================================*/

/*S TAG =======================================*/
class ts extends THTMLElement {
  //this function is what makes everything happen as far as attributes etc go
  function initialize() {
    $this->set_openingtag("<S[attributes]>");
    $this->set_closingtag("</S>");
  }
}

//function that outputs things
function s ( $arguments = "" ) {
  $arguments = func_get_args ();
  $rc = new ReflectionClass('ts');
  return $rc->newInstanceArgs ( $arguments );
}
/*END S TAG ========================================*/

/*STRONG TAG =======================================*/
class tstrong extends THTMLElement {
  //this function is what makes everything happen as far as attributes etc go
  function initialize() {
    $this->set_openingtag("<STRONG[attributes]>");
    $this->set_closingtag("</STRONG>");
  }
}

//function that outputs things
function strong ( $arguments = "" ) {
  $arguments = func_get_args ();
  $rc = new ReflectionClass( 'tstrong' );
  return $rc->newInstanceArgs ( $arguments );
}
/*END STRONG TAG ========================================*/

/*TRACK TAG =======================================*/
class ttrack extends THTMLElement {
  //this function is what makes everything happen as far as attributes etc go
  function initialize() {
    $this->set_openingtag("<TRACK[attributes]>");
    $this->set_closingtag("</TRACK>");
  }
}

//function that outputs things
function track ( $arguments = "" ) {
  $arguments = func_get_args ();
  $rc = new ReflectionClass( 'ttrack' );
  return $rc->newInstanceArgs ( $arguments );
}
/*END TRACK TAG ========================================*/

/*EM TAG =======================================*/
class tem extends THTMLElement {
  //this function is what makes everything happen as far as attributes etc go
  function initialize() {
    $this->set_openingtag("<EM[attributes]>");
    $this->set_closingtag("</EM>");
  }
}

//function that outputs things
function em ( $arguments = "" ) {
  $arguments = func_get_args ();
  $rc = new ReflectionClass( 'tem' );
  return $rc->newInstanceArgs ( $arguments );
}
/*END EM TAG ========================================*/

/*!-- TAG =======================================*/
class tcomment extends THTMLElement {
  //this function is what makes everything happen as far as attributes etc go
  function initialize() {
    $this->set_openingtag("<!--");
    $this->set_closingtag("-->");
  }
}

//function that outputs things
function comment ( $arguments = "" ) {
  $arguments = func_get_args ();
  $rc = new ReflectionClass('tcomment');
  return $rc->newInstanceArgs ( $arguments );
}
/*END !-- TAG ========================================*/

/*ABBR TAG =======================================*/
class tabbr extends THTMLElement {
  //this function is what makes everything happen as far as attributes etc go
  function initialize() {
    $this->set_openingtag("<ABBR[attributes]>");
    $this->set_closingtag("</ABBR>");
  }
}

//function that outputs things
function abbr ( $arguments = "" ) {
  $arguments = func_get_args ();
  $rc = new ReflectionClass('tabbr');
  return $rc->newInstanceArgs ( $arguments );
}
/*END ABBR TAG ========================================*/

/*DFN TAG =======================================*/
class tdfn extends THTMLElement {
  //this function is what makes everything happen as far as attributes etc go
  function initialize() {
    $this->set_openingtag("<DFN[attributes]>");
    $this->set_closingtag("</DFN>");
  }
}

//function that outputs things
function dfn ( $arguments = "" ) {
  $arguments = func_get_args ();
  $rc = new ReflectionClass( 'tdfn' );
  return $rc->newInstanceArgs ( $arguments );
}
/*END DFN TAG ========================================*/

/*SCRIPT TAG =======================================*/
class tscripttag extends THTMLElement {
   //this function is what makes everything happen as far as attributes etc go
  function initialize () {
    $this->set_openingtag("<SCRIPT[attributes]>");
	  $this->set_closingtag("</SCRIPT>");
  }
}

//function that outputs things
function scripttag ( $arguments = "" ) {
  $arguments = func_get_args();
  $rc = new ReflectionClass( 'tscripttag' );
  return $rc->newInstanceArgs( $arguments ); 
}

function_alias ("scripttag", "script");
/*END SCRIPT TAG ===================================*/

/*SPAN TAG =======================================*/
class tspan extends THTMLElement {
   //this function is what makes everything happen as far as attributes etc go
  function initialize () {
    $this->set_openingtag("<SPAN[attributes]>");
	  $this->set_closingtag("</SPAN>");
  }
}

//function that outputs things
function span ( $arguments = "" ) {
  $arguments = func_get_args();
  $rc = new ReflectionClass( 'tspan' );
  return $rc->newInstanceArgs( $arguments ); 
}
/*END SPAN TAG ===================================*/

/*LINK TAG =======================================*/
class talink extends THTMLElement {
   //this function is what makes everything happen as far as attributes etc go
  function initialize () {
    $this->set_openingtag("");
	  $this->set_closingtag("<LINK[attributes]/>");
  }
}

//function that outputs things
function alink ( $arguments = "" ) {
  $arguments = func_get_args();
  $rc = new ReflectionClass('talink');
  return $rc->newInstanceArgs( $arguments ); 
}

/*END LINK TAG ===================================*/

/*LABEL TAG =======================================*/
class tlabel extends THTMLElement {
   //this function is what makes everything happen as far as attributes etc go
  function initialize () {
    $this->set_openingtag("<LABEL[attributes]>");
	$this->set_closingtag("</LABEL>");
  }
}

//function that outputs things
function label ( $arguments = "" ) {
  $arguments = func_get_args();
  $rc = new ReflectionClass('tlabel');
  return $rc->newInstanceArgs( $arguments ); 
}
/*END LABEL TAG ===================================*/

/*UL TAG =======================================*/
class tul extends THTMLElement {
   //this function is what makes everything happen as far as attributes etc go
  function initialize () {
    $this->set_openingtag("<UL[attributes]>");
	$this->set_closingtag("</UL>");
  }
}

//function that outputs things
function ul ( $arguments = "" ) {
  $arguments = func_get_args();
  $rc = new ReflectionClass('tul');
  return $rc->newInstanceArgs( $arguments ); 
}
/*END UL TAG ===================================*/

/*OL TAG =======================================*/
class tol extends THTMLElement {
   //this function is what makes everything happen as far as attributes etc go
  function initialize () {
    $this->set_openingtag("<OL[attributes]>");
	$this->set_closingtag("</OL>");
  }
}

//function that outputs things
function ol ( $arguments = "" ) {
  $arguments = func_get_args();
  $rc = new ReflectionClass('tol');
  return $rc->newInstanceArgs( $arguments ); 
}
/*END OL TAG ===================================*/


/*LI TAG =======================================*/
class tli extends THTMLElement {
   //this function is what makes everything happen as far as attributes etc go
  function initialize () {
    $this->set_openingtag("<LI[attributes]>");
	$this->set_closingtag("</LI>");
  }
}

//function that outputs things
function li ( $arguments = "" ) {
  $arguments = func_get_args();
  $rc = new ReflectionClass('tli');
  return $rc->newInstanceArgs( $arguments ); 
}
/*END LI TAG ===================================*/


/*START B TAG =======================================*/
class tb extends THTMLElement {
   //this function is what makes everything happen as far as attributes etc go
  function initialize () {
    $this->set_openingtag("<B[attributes]>");
	$this->set_closingtag("</B>");
  }
}

//function that outputs things
function b ( $arguments = "" ) {
  $arguments = func_get_args();
  $rc = new ReflectionClass('tb');
  return $rc->newInstanceArgs( $arguments ); 
}
/*END B TAG ===================================*/

/*START I TAG =======================================*/
class ti extends THTMLElement {
   //this function is what makes everything happen as far as attributes etc go
  function initialize () {
    $this->set_openingtag("<I[attributes]>");
	$this->set_closingtag("</I>");
  }
}

//function that outputs things
function i ( $arguments = "" ) {
  $arguments = func_get_args();
  $rc = new ReflectionClass('ti');
  return $rc->newInstanceArgs( $arguments ); 
}
/*END I TAG ===================================*/

/*START U TAG =======================================*/
class tu extends THTMLElement {
   //this function is what makes everything happen as far as attributes etc go
  function initialize () {
    $this->set_openingtag("<U[attributes]>");
	$this->set_closingtag("</U>");
  }
}

//function that outputs things
function u ( $arguments = "" ) {
  $arguments = func_get_args();
  $rc = new ReflectionClass('tu');
  return $rc->newInstanceArgs( $arguments ); 
}
/*END U TAG ===================================*/

/*P TAG =======================================*/
class tptag extends THTMLElement {
   //this function is what makes everything happen as far as attributes etc go
  function initialize () {
    $this->set_openingtag("<P[attributes]>");
	$this->set_closingtag("</P>");
  }
}

//function that outputs things
function ptag ( $arguments = "" ) {
  $arguments = func_get_args();
  $rc = new ReflectionClass('tptag');
  return $rc->newInstanceArgs( $arguments ); 
}

function_alias ("ptag", "p");
/*END P TAG ===================================*/

/*ARTICLE TAG =======================================*/
class tarticle extends THTMLElement {
   //this function is what makes everything happen as far as attributes etc go
  function initialize () {
    $this->set_openingtag("<ARTICLE[attributes]>");
	$this->set_closingtag("</ARTICLE>");
  }
}

//function that outputs things
function article ( $arguments = "" ) {
  $arguments = func_get_args();
  $rc = new ReflectionClass('tarticle');
  return $rc->newInstanceArgs( $arguments ); 
}
/*END ARTICLE TAG ===================================*/

/*A TAG =======================================*/
class ta extends THTMLElement {
   //this function is what makes everything happen as far as attributes etc go
  function initialize () {
    $this->set_openingtag("<A[attributes]>");
	$this->set_closingtag("</A>");
  }
}

//function that outputs things
function a ( $arguments = "" ) {
  $arguments = func_get_args();
  $rc = new ReflectionClass('ta');
  return $rc->newInstanceArgs( $arguments ); 
}
/*END A TAG ===================================*/

/*STYLE TAG =======================================*/
class tstyle extends THTMLElement {
   //this function is what makes everything happen as far as attributes etc go
  function initialize () {
    $this->set_openingtag("<STYLE[attributes]>");
	  $this->set_closingtag("</STYLE>");
  }
}

//function that outputs things
function style ( $arguments = "" ) {
  $arguments = func_get_args();
  $rc = new ReflectionClass('tstyle');
  return $rc->newInstanceArgs( $arguments ); 
}
/*END STYLE TAG ===================================*/

/*BR TAG =======================================*/
class tbr extends THTMLElement {
   //this function is what makes everything happen as far as attributes etc go
  function initialize () {
    $this->set_openingtag("");
	$this->set_closingtag("<BR[attributes]/>");
  }
}

//function that outputs things
function br ( $arguments = "" ) {
  $arguments = func_get_args();
  $rc = new ReflectionClass('tbr');
  return $rc->newInstanceArgs( $arguments ); 
}
/*END STYLE TAG ===================================*/


/*DIV TAG =======================================*/
class tdivtag extends THTMLElement {
   //this function is what makes everything happen as far as attributes etc go
  function initialize () {
    $this->set_openingtag("<DIV[attributes]>[content]");
	$this->set_closingtag("</DIV>");
  }
}

//function that outputs things
function divtag ( $arguments = "" ) {
  $arguments = func_get_args();
  $rc = new ReflectionClass('tdivtag');
  return $rc->newInstanceArgs( $arguments ); 
}

function_alias ("divtag", "div");
/*END DIV TAG ===================================*/


/*SELECT TAG =======================================*/
class tselect extends THTMLElement {
   //this function is what makes everything happen as far as attributes etc go
  function initialize () {
    $this->set_openingtag("<SELECT[attributes]>");
	$this->set_closingtag("</SELECT>");
	$this->set_type ("list");
  }
}

//function that outputs things
function select  ( $arguments = "" ) {
  $arguments = func_get_args();
  $rc = new ReflectionClass('tselect');
  return $rc->newInstanceArgs( $arguments ); 
}
/*END SELECT TAG ===================================*/


/*OPTION TAG =======================================*/
class toption extends THTMLElement {
   //this function is what makes everything happen as far as attributes etc go
  function initialize () {
    $this->set_openingtag("<OPTION[attributes]>");
	$this->set_closingtag("</OPTION>");
  }
}

//function that outputs things
function option  ( $arguments = "" ) {
  $arguments = func_get_args();
  $rc = new ReflectionClass('toption');
  return $rc->newInstanceArgs( $arguments ); 
}
/*END OPTION TAG ===================================*/

/*LEGEND TAG =======================================*/
class tlegend extends THTMLElement {
   //this function is what makes everything happen as far as attributes etc go
  function initialize () {
    $this->set_openingtag("<LEGEND[attributes]>");
	$this->set_closingtag("</LEGEND>");
  }
}

//function that outputs things
function legend ( $arguments = "" ) {
  $arguments = func_get_args();
  $rc = new ReflectionClass('tlegend');
  return $rc->newInstanceArgs( $arguments ); 
}
/*END LEGEND TAG ===================================*/

/*FIELDSET TAG =======================================*/
class tfieldset extends THTMLElement {
   //this function is what makes everything happen as far as attributes etc go
  function initialize () {
    $this->set_openingtag("<FIELDSET[attributes]>");
	$this->set_closingtag("</FIELDSET>");
  }
}

//function that outputs things
function fieldset ( $arguments = "" ) {
  $arguments = func_get_args();
  $rc = new ReflectionClass('tfieldset');
  return $rc->newInstanceArgs( $arguments ); 
}
/*END FIELDSET TAG ===================================*/

/*H1 TAG =======================================*/
class th1tag extends THTMLElement {
   //this function is what makes everything happen as far as attributes etc go
  function initialize () {
    $this->set_openingtag ( "<H1[attributes]>" );
	$this->set_closingtag ( "</H1>" );
  }
}

//function that outputs things
function h1tag ( $arguments = "" ) {
  $arguments = func_get_args();
  $rc = new ReflectionClass('th1tag');
  return $rc->newInstanceArgs( $arguments ); 
}

function_alias ("h1tag", "h1");
/*END H1 TAG ====================================*/

/*H2 TAG =======================================*/
class th2tag extends THTMLElement {
   //this function is what makes everything happen as far as attributes etc go
  function initialize () {
    $this->set_openingtag ( "<H2[attributes]>" );
	$this->set_closingtag ( "</H2>" );
  }
}

//function that outputs things
function h2tag ( $arguments = "" ) {
  $arguments = func_get_args();
  $rc = new ReflectionClass('th2tag');
  return $rc->newInstanceArgs( $arguments ); 
}

function_alias ("h2tag", "h2");
/*END H2 TAG ====================================*/

/*H3 TAG =======================================*/
class th3tag extends THTMLElement {
   //this function is what makes everything happen as far as attributes etc go
  function initialize () {
    $this->set_openingtag ( "<H3[attributes]>" );
	$this->set_closingtag ( "</H3>" );
  }
}

//function that outputs things
function h3tag ( $arguments = "" ) {
  $arguments = func_get_args();
  $rc = new ReflectionClass('th3tag');
  return $rc->newInstanceArgs( $arguments ); 
}

function_alias ("h3tag", "h3");
/*END H3 TAG ====================================*/

/*H4 TAG =======================================*/
class th4tag extends THTMLElement {
   //this function is what makes everything happen as far as attributes etc go
  function initialize () {
    $this->set_openingtag ( "<H4[attributes]>" );
	$this->set_closingtag ( "</H4>" );
  }
}

//function that outputs things
function h4tag ( $arguments = "" ) {
  $arguments = func_get_args();
  $rc = new ReflectionClass('th4tag');
  return $rc->newInstanceArgs( $arguments ); 
}

function_alias ("h4tag", "h4");
/*END H4 TAG ====================================*/

/*H5 TAG =======================================*/
class th5tag extends THTMLElement {
   //this function is what makes everything happen as far as attributes etc go
  function initialize () {
    $this->set_openingtag ( "<H5[attributes]>" );
	  $this->set_closingtag ( "</H5>" );
  }
}

//function that outputs things
function h5tag ( $arguments = "" ) {
  $arguments = func_get_args();
  $rc = new ReflectionClass('th5tag');
  return $rc->newInstanceArgs( $arguments ); 
}

function_alias ("h5tag", "h5");
/*END H5 TAG ====================================*/

/*H6 TAG =======================================*/
class th6tag extends THTMLElement {
   //this function is what makes everything happen as far as attributes etc go
  function initialize () {
    $this->set_openingtag ( "<H6[attributes]>" );
	$this->set_closingtag ( "</H6>" );
  }
}

//function that outputs things
function h6tag ( $arguments = "" ) {
  $arguments = func_get_args();
  $rc = new ReflectionClass('th6tag');
  return $rc->newInstanceArgs( $arguments ); 
}

function_alias ("h6tag", "h6");
/*END H6 TAG ====================================*/

/*H7 TAG =======================================*/
class th7tag extends THTMLElement {
   //this function is what makes everything happen as far as attributes etc go
  function initialize () {
    $this->set_openingtag ( "<H7[attributes]>" );
	$this->set_closingtag ( "</H7>" );
  }
}

//function that outputs things
function h7tag ( $arguments = "" ) {
  $arguments = func_get_args();
  $rc = new ReflectionClass('th7tag');
  return $rc->newInstanceArgs( $arguments ); 
}

function_alias ("h7tag", "h7");
/*END H7 TAG ====================================*/


/*HR TAG =======================================*/
class tline extends THTMLElement {
   //this function is what makes everything happen as far as attributes etc go
  function initialize () {
    $this->set_openingtag ( "<HR[attributes]>" );
	$this->set_closingtag ( "" );
  }
}

//function that outputs things
function line ( $arguments = "" ) {
  $arguments = func_get_args();
  $rc = new ReflectionClass('tline');
  return $rc->newInstanceArgs( $arguments ); 
}
/*END HR TAG ====================================*/


/*TABLE TAG =======================================*/
class ttable extends THTMLElement {
   //this function is what makes everything happen as far as attributes etc go
  function initialize () {
    $this->set_openingtag ( "<TABLE[attributes]>" );
	$this->set_closingtag ( "</TABLE>" );
  }
}

//function that outputs things
function table ( $arguments = "" ) {
  $arguments = func_get_args();
  $rc = new ReflectionClass('ttable');
  return $rc->newInstanceArgs( $arguments ); 
}
/*END TABLE TAG ====================================*/



/*THEAD TAG =======================================*/
class tthead extends THTMLElement {
   //this function is what makes everything happen as far as attributes etc go
  function initialize () {
    $this->set_openingtag ( "<THEAD[attributes]>" );
	$this->set_closingtag ( "</THEAD>" );
  }
}

//function that outputs things
function thead ( $arguments = "" ) {
  $arguments = func_get_args();
  $rc = new ReflectionClass('tthead');
  return $rc->newInstanceArgs( $arguments ); 
}
/*END THEAD TAG ====================================*/

/*TTBODY TAG =======================================*/
class ttbody extends THTMLElement {
   //this function is what makes everything happen as far as attributes etc go
  function initialize () {
    $this->set_openingtag ( "<TBODY[attributes]>" );
	$this->set_closingtag ( "</TBODY>" );
  }
}

//function that outputs things
function tbody ($arguments = "") {
  $arguments = func_get_args();
  $rc = new ReflectionClass('ttbody');
  return $rc->newInstanceArgs( $arguments ); 
}
/*END TTBODY TAG ====================================*/

/*TTFOOT TAG =======================================*/
class ttfoot extends THTMLElement {
   //this function is what makes everything happen as far as attributes etc go
  function initialize () {
    $this->set_openingtag ( "<TFOOT[attributes]>" );
	$this->set_closingtag ( "</TFOOT>" );
  }
}

//function that outputs things
function tfoot ($arguments = "") {
  $arguments = func_get_args();
  $rc = new ReflectionClass('ttfoot');
  return $rc->newInstanceArgs( $arguments ); 
}
/*END TTBODY TAG ====================================*/




/*TR TAG =======================================*/
class ttr extends THTMLElement {
   //this function is what makes everything happen as far as attributes etc go
  function initialize () {
    $this->set_openingtag ( "<TR[attributes]>" );
	$this->set_closingtag ( "</TR>" );
  }
}

//function that outputs things
function tr ($arguments = "") {
  $arguments = func_get_args();
  $rc = new ReflectionClass('ttr');
  return $rc->newInstanceArgs( $arguments ); 
}
/*END TR TAG ====================================*/


/*TD TAG =======================================*/
class ttd extends THTMLElement {
   //this function is what makes everything happen as far as attributes etc go
  function initialize () {
    $this->set_openingtag ( "<TD[attributes]>" );
	$this->set_closingtag ( "</TD>" );
  }
}

//function that outputs things
function td ($arguments = "") {
  $arguments = func_get_args();
  $rc = new ReflectionClass('ttd');
  return $rc->newInstanceArgs( $arguments ); 
}
/*END TD TAG ====================================*/

/*TH TAG =======================================*/
class tth extends THTMLElement {
   //this function is what makes everything happen as far as attributes etc go
  function initialize () {
    $this->set_openingtag ( "<TH[attributes]>" );
	$this->set_closingtag ( "</TH>" );
  }
}

//function that outputs things
function th ($arguments = "") {
  $arguments = func_get_args();
  $rc = new ReflectionClass('tth');
  return $rc->newInstanceArgs( $arguments ); 
}
/*END TH TAG ====================================*/


/*FORM TAG =======================================*/
class tformtag extends THTMLElement {
   //this function is what makes everything happen as far as attributes etc go
  function initialize () {
    $this->set_openingtag ( "<FORM[attributes]>" );
	$this->set_closingtag ( "</FORM>" );
  }
}

//function that outputs things
function formtag ($arguments = "") {
  $arguments = func_get_args();
  $rc = new ReflectionClass('tformtag');
  return $rc->newInstanceArgs( $arguments ); 
}

function_alias ("formtag", "form");

/*END FORM TAG ====================================*/


/*INPUT TAG =======================================*/
class tinput extends THTMLElement {
   //this function is what makes everything happen as far as attributes etc go
  function initialize () {
    $this->set_openingtag ( "<INPUT value=\"" );
	$this->set_closingtag ( "\"[attributes]>" );
	$this->set_type ("input");
  }
}

//function that outputs things
function input ($arguments = "") {
  $arguments = func_get_args();
  $rc = new ReflectionClass('tinput');
  return $rc->newInstanceArgs( $arguments ); 
}
/*END INPUT TAG ====================================*/


/*TEXTAREA TAG =======================================*/
class ttextarea extends THTMLElement {
   //this function is what makes everything happen as far as attributes etc go
  function initialize () {
    $this->set_openingtag ( "<TEXTAREA[attributes]>" );
	$this->set_closingtag ( "</TEXTAREA>" );
	$this->set_type ("textarea");
  }
}

//function that outputs things
function textarea ($arguments = "") {
  $arguments = func_get_args();
  $rc = new ReflectionClass('ttextarea');
  return $rc->newInstanceArgs( $arguments ); 
}

/*END TEXTAREA TAG ====================================*/


/*TEXTINPUT TAG =======================================*/
class ttextinput extends THTMLElement {
   //this function is what makes everything happen as far as attributes etc go
  function initialize () {
    $this->set_openingtag ( "<LABEL>" );
	$this->set_closingtag ( "</LABEL><INPUT TYPE=\"text\"[attributes]>" );
	$this->set_type ("text");
  }
}

//function that outputs things
function textinput ($arguments = "") {
  $arguments = func_get_args();
  $rc = new ReflectionClass('ttextinput');
  return $rc->newInstanceArgs( $arguments );
}
/*END TEXTINPUT TAG ====================================*/

/*TEXTINPUT TAG =======================================*/
class tpasswordinput extends THTMLElement {
   //this function is what makes everything happen as far as attributes etc go
  function initialize () {
    $this->set_openingtag ( "<LABEL>" );
	$this->set_closingtag ( "</LABEL><INPUT TYPE=\"password\"[attributes]>" );
	$this->set_type ("password");
  }
}

//function that outputs things
function passwordinput ($arguments = "") {
  $arguments = func_get_args();
  $rc = new ReflectionClass('tpasswordinput');
  return $rc->newInstanceArgs( $arguments ); 
}
/*END TEXTINPUT TAG ====================================*/

/*BUTTON TAG =======================================*/
class tbutton extends THTMLElement {
   //this function is what makes everything happen as far as attributes etc go
  function initialize () {
    $this->set_openingtag ( "<INPUT TYPE=\"button\" value=\"" );
	$this->set_closingtag ( "\"[attributes]>" );
	$this->set_type ("button");
  }
}

//function that outputs things
function button ($arguments = "") {
  $arguments = func_get_args();
  $rc = new ReflectionClass('tbutton');
  return $rc->newInstanceArgs( $arguments ); 
}
/*END BUTTON TAG ====================================*/

/*CHECKBOX TAG =======================================*/
class tcheckbox extends THTMLElement {
   //this function is what makes everything happen as far as attributes etc go
  function initialize () {
    $this->set_openingtag ( "<INPUT TYPE=\"checkbox\" value=\"" );
	$this->set_closingtag ( "\"[attributes]>" );
	$this->set_type ("checkbox");
  }
}

//function that outputs things
function checkbox ($arguments = "") {
  $arguments = func_get_args();
  $rc = new ReflectionClass('tcheckbox');
  return $rc->newInstanceArgs( $arguments ); 
}
/*END CHECKBOX TAG ====================================*/

/*SUBMIT TAG =======================================*/
class tsubmit extends THTMLElement {
   //this function is what makes everything happen as far as attributes etc go
  function initialize () {
    $this->set_openingtag ( "<INPUT TYPE=\"submit\" value=\"" );
	$this->set_closingtag ( "\"[attributes]>" );
	$this->set_type ("button");
  }
}

//function that outputs things
function submit ($arguments = "") {
  $arguments = func_get_args();
  $rc = new ReflectionClass('tsubmit');
  return $rc->newInstanceArgs( $arguments ); 
}
/*END SUBMIT TAG ====================================*/

/*NAV TAG ===========================================*/
class tnav extends THTMLElement {
   //this function is what makes everything happen as far as attributes etc go
  function initialize () {
    $this->set_openingtag ( "<NAV[attributes]>" );
	$this->set_closingtag ( "</NAV>" );
  }
}

//function that outputs things
function nav ($arguments = "") {
  $arguments = func_get_args();
  $rc = new ReflectionClass('tnav');
  return $rc->newInstanceArgs( $arguments ); 
}
/*END NAV TAG ======================================*/

//Thanks to Blackbrick for slib_compress_script
function slib_compress_script( $buffer ) {
  // JavaScript compressor by John Elliot <jj5@jj5.net>
  $replace = array(
    '#\'([^\n\']*?)/\*([^\n\']*)\'#' => "'\1/'+\'\'+'*\2'", // remove comments from ' strings
    '#\"([^\n\"]*?)/\*([^\n\"]*)\"#' => '"\1/"+\'\'+"*\2"', // remove comments from " strings
    '#/\*.*?\*/#s'            => "",      // strip C style comments
    '#[\r\n]+#'               => "\n",    // remove blank lines and \r's
    '#\n([ \t]*//.*?\n)*#s'   => "\n",    // strip line comments (whole line only)
    '#([^\\])//([^\'"\n]*)\n#s' => "\\1\n",
                                          // strip line comments
                                          // (that aren't possibly in strings or regex's)
    '#\n\s+#'                 => "\n",    // strip excess whitespace
    '#\s+\n#'                 => "\n",    // strip excess whitespace
    '#(//[^\n]*\n)#s'         => "\\1\n", // extra line feed after any comments left
                                          // (important given later replacements)
    '#/([\'"])\+\'\'\+([\'"])\*#' => "/*" // restore comments in strings
  );

  $search = array_keys( $replace );
  $script = preg_replace( $search, $replace, $buffer );

  $replace = array(
    "&&\n" => "&&",
    "||\n" => "||",
    "(\n"  => "(",
    ")\n"  => ")",
    "[\n"  => "[",
    "]\n"  => "]",
    "+\n"  => "+",
    ",\n"  => ",",
    "?\n"  => "?",
    ":\n"  => ":",
    ";\n"  => ";",
    "{\n"  => "{",
//  "}\n"  => "}", (because I forget to put semicolons after function assignments)
    "\n]"  => "]",
    "\n)"  => ")",
    "\n}"  => "}",
    "\n\n" => "\n"
  );

  $search = array_keys( $replace );
  $script = str_replace( $search, $replace, $script );

  return trim( $script );

}
  
//We should be putting the THMTLElement for the shape language in here 

 //flatten arrays that are sent in as params
if (!function_exists("array_flatten")) {
   function array_flatten ($array)
  {
      $newarray = array();
      $it = new RecursiveIteratorIterator(new RecursiveArrayIterator($array));
      foreach($it as $ind => $v) {
        $newarray[$ind] = $v;
      }
      
      return $newarray;
  }
}


/*Function for getting information about class objects*/
if ( !function_exists( "get_function_arguments" ) ){
	function get_function_arguments ($functionname) {
        $f = new ReflectionFunction($functionname);
        $args = array();
        foreach ($f->getParameters() as $param) {
                $tmparg = '';
                if ($param->isPassedByReference()) $tmparg = '&';
                if ($param->isOptional()) {
                        $tmparg = '[' . $tmparg . '$' . $param->getName() . ' = ' . $param->getDefaultValue() . ']';
                } else {
                        $tmparg.= '$' . $param->getName();
                }
                $args[] = $tmparg;
                unset ($tmparg);
        }
		
		return $args;
    }

}

if ( !function_exists( "get_classfunction_arguments" ) ){
	function get_classfunction_arguments ($classname, $functionname) {
        $f = new ReflectionMethod($classname, $functionname);
        $args = array();
        foreach ($f->getParameters() as $param) {
                $tmparg = '';
                if ($param->isPassedByReference()) $tmparg = '&';
				
			
                if ($param->isOptional()) {
                        $tmparg = '[' . $tmparg . '$' . $param->getName() . ' = ' . $param->getDefaultValue() . ']';
                } else {
                        $tmparg.= '$' . $param->getName();
                }
                $args[] = $tmparg;
                unset ($tmparg);
        }
		
		return $args;
	}
}
