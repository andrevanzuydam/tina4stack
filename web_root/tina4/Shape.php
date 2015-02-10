<?php
/**
 * The basic shape element class to handle inheritance
 */
class shapeBaseElement {

    private $id;
    private $parent;
    private $keyValue;
    private $keyName;

    function __construct($keyName = "", $keyValue = "") {
        $this->id = uniqid();
        $this->parent = "";
        $this->keyName = $keyName;
        $this->keyValue = $keyValue;
        $this->registerGlobal();
    }

    function __clone() {
        if (empty($this->parent)) {
            $this->parent = $this->id;
            $this->id = uniqid();
        }
        $this->registerGlobal();
    }

    function registerGlobal() {
        $GLOBALS["shapeElements"][$this->id] = $this;
    }

    function getParent() {
        return $this->parent;
    }

    function getValue() {
        return $this->keyValue;
    }

    function getKey() {
        return $this->keyName;
    }

    function getId() {
        return $this->id;
    }

    function setParent($value = null) {
        $this->parent = $value;
    }

    function setValue($value) {
        $this->keyValue = $value;
    }

    function setKey($value) {
        $this->keyName = $value;
    }

}
/**
 * The htmlElement class which handles the HTML compilation etc
 */
class htmlElement extends shapeBaseElement {

    private $openingTag = "";
    private $closingTag = "";
    private $attributes;
    private $content;

    /**
     * Compile all the Attributes
     * @return string
     */
    function compileAttributes() {
        $html = "";
        if (!empty($this->attributes)) {
            foreach ($this->attributes as $aid => $attribute) {
                if (!is_array($attribute->getValue())) {
                    $html .= ' ' . $attribute->getKey() . '="' . $attribute->getValue() . '"';
                }
            }
        }
        return $html;
    }

    /**
     * Compile the content for the Element
     * @param type $acontent
     * @return type
     */
    function compileContent($acontent = null) {
        $html = "";
        if (!empty($acontent)) {
            foreach ($acontent as $cid => $content) {


                if (is_object($content) && get_class($content) === "shapeBaseElement") {
                    if (is_object($content->getValue()) && get_class($content->getValue()) === "htmlElement") {
                        $html .= $content->getValue()->compileHTML();
                    } else {
                        
                        if (is_array($content->getValue())) {
                          foreach ($content->getValue() as $ccid => $ccontent) {
                              if (is_object($ccontent) && get_class($ccontent) === "htmlElement") {
                                $html .= $ccontent->compileHTML();
                              }
                         }                           
                        }
                          else {
                           $html .= $content->getValue();
                          } 
                    }
                }
            }
        }
        return $html;
    }

    /**
     * Compiling HTML
     * @return type
     */
    function compileHTML() {
        $html = "";
        $attributes = $this->compileAttributes();
        $html .= str_ireplace("[attributes]", $attributes, $this->openingTag);
        $html .= $this->compileContent($this->content);
        $html .= str_ireplace("[attributes]", $attributes, $this->closingTag);

        return $html;
    }

    /**
     * Make HTML from the Object
     * @return type
     */
    function __toString() {
        return $this->compileHTML();
    }

    /**
     * Function to check is an array is an associative or not
     * @param type $array
     * @return type
     */
    function is_assoc($array) {
        return (bool) count(array_filter(array_keys($array), 'is_string'));
    }

    /**
     * Parse all the Arguments passed to the class, see if they are content or attributes
     * @param type $arg
     */
    function parseArgument($arg) {
        if (is_array($arg) && $this->is_assoc($arg) && !empty($arg)) {
            foreach ($arg as $keyName => $keyValue) {
                $this->attributes[] = new shapeBaseElement($keyName, $keyValue);
            }
        } else {
            $this->content[] = new shapeBaseElement("content", $arg);
        }
    }

    /**
     * Constructor for HTMLElement
     */
    function __construct() {
        parent::__construct();
        $args = func_get_args();
        foreach ($args as $arg) {
            $this->parseArgument($arg);
        }
    }

    /**
     * Cloning the Object
     */
    function __clone() {
        parent::__clone();
        $this->cloneChildren($this);
    }

    /**
     * Clone All the Children
     * @param type $element
     */
    function cloneChildren($element) {
        if (!empty($element->attributes)) {
            foreach ($element->attributes as $aid => $attribute) {
                if (empty($attribute->getParent())) {
                    $element->attributes[$aid] = clone $attribute;
                }
            }
        }
        if (!empty($element->content)) {
            foreach ($element->content as $cid => $content) {

                $element->content[$cid] = clone $content;

                if (is_object($content) && get_class($content) === "shapeBaseElement") {
                   
                    if (is_object($element->content[$cid]->getValue()) && get_class($element->content[$cid]->getValue()) == "htmlElement") {
                        
                        $element->content[$cid]->setValue(clone $element->content[$cid]->getValue());
                        $this->cloneChildren($element->content[$cid]->getValue());
                    }
                }
            }
        }
    }

    /**
     * Setting Content
     * @param type $value
     */
    function setContent($value) {
        if (count($this->content) == 1) {
          $this->content[0]->setValue($value);  
          if (!empty($this->content[0]->getParent())) {
            
            $this->content[0]->setParent();  
          }
        }
          else {
          
          $content = new shapeBaseElement("content", $value);    
          $this->content = [$content];
          //find all the children of this element and add the attribute
          foreach ($GLOBALS["shapeElements"] as $eid => $element) {
             if ($element->getParent() === $this->getId()) {
                 $element->cloneContent($content);
             }
          }
          
        }
        $this->setInherited();
    }

    /**
     * Getting the content for the Element
     * @return type
     */
    function getContent() {
        return $this->content;
    }

    /**
     * Adding Content
     * @param type $value
     */
    function addContent($value) {
        $this->content[] = new shapeBaseElement("content", $value);
    }

    /**
     * BySearch - internal function to find elements
     */
    function bySearch ($keyName, $keyIndex="id") {
        $result = null;
        if (!empty($this->attributes)) {
            foreach ($this->attributes as $aid => $attribute) {
                if (strtoupper($attribute->getKey()) === strtoupper("id") && $attribute->getValue() === $keyName) {
                    $result = $this;
                }
            }
        }
        
        if (empty($result)) {
            if (!empty($this->content)) {
                foreach ($this->content as $cid => $content) {
                if (is_object($content) && get_class($content) === "shapeBaseElement") {
                        if (is_object($this->content[$cid]->getValue()) && get_class($this->content[$cid]->getValue()) == "htmlElement") {
                            $result = $this->content[$cid]->getValue()->byId($keyName);
                            if (!empty($result)) {
                               break;  
                            }
                        }
                    }
                }
            }
        }
        return $result;
    }
    
    /**
     * Find and Element by Its HTML Id
     * Example: p(["id" => "Test"])
     * @param type $keyName
     * @return \htmlElement
     */
    function byId($keyName) {
       return $this->bySearch($keyName, "id"); 
    }
    
    /**
     * Find and Element by Its HTML Id
     * Example: p(["clas" => "Test"])
     * @param type $keyName
     * @return \htmlElement
     */
    function byClass($keyName) {
       return $this->bySearch($keyName, "class"); 
    }

    /**
     * Set inherited properties
     */
    function setInherited() {
            if (!empty($this->attributes)) {
                foreach ($this->attributes as $cid => $attribute) {
                    if (is_object($attribute) && get_class($attribute) === "shapeBaseElement") {
                        //update all the children to have my value
                        foreach ($GLOBALS["shapeElements"] as $sid => $element) {
                           if ($element->getParent() === $attribute->getId()) {
                               $element->setValue (  $attribute->getValue());
                           }  
                        }
                    }    
                }
            }
            
            if (!empty($this->content)) {
                foreach ($this->content as $cid => $content) {
                    if (is_object($content) && get_class($content) === "shapeBaseElement") {
                        //update all the children to have my value
                        foreach ($GLOBALS["shapeElements"] as $sid => $element) {
                           if ($element->getParent() === $content->getId()) {
                             if (is_object($element) && get_class($element) == "shapeBaseElement") {
                                 if (is_object($element->getValue()) && get_class($element->getValue()) === "htmlElement") {
                                   $this->content[$cid]->getValue()->setInherited();
                                 }
                                   else {
                                     $element->setValue (  $content->getValue());
                                   }
                               }
                           }  
                        }
                        
                        if (is_object($this->content[$cid]->getValue()) && get_class($this->content[$cid]->getValue()) === "htmlElement") {
                            if ($element->getParent() === $content->getId()) {
                              
                            }
                        }
                    }
                }
            }
      
    }
    
    /**
     * Set attributes to the Element
     * @param type $keyName
     * @param type $keyValue
     */
    function setAttribute($keyName, $keyValue) {
        $wasSet = false;
        if (!empty($this->attributes)) {
        foreach ($this->attributes as $aid => $attribute) {
            if ($attribute->getKey() === $keyName) {
                if (!empty($attribute->getParent())) {
                    $this->attributes[$aid] = clone $attribute;
                }
                $this->attributes[$aid]->setValue($keyValue);
                $this->attributes[$aid]->setParent();
                $this->setInherited();
                $wasSet = true;
            }
        }
        }

        if (!$wasSet) {
            $attribute = new shapeBaseElement($keyName, $keyValue);
            $this->attributes[] = $attribute;
            //find all the children of this element and add the attribute
            foreach ($GLOBALS["shapeElements"] as $eid => $element) {
               if ($element->getParent() === $this->getId()) {
                 $element->cloneAttribute($attribute);
               }
            }
            
            return $attribute;
        }
    }

    /**
     * Clones a new attribute onto the child
     * @param type $attribute
     */
    function cloneAttribute ($attribute) {
       $this->attributes[] = clone $attribute; 
    }

    /**
     * Clones content  
     * @param type $content
     */    
    function cloneContent ($content) {
       $this->content[] = clone $content; 
    }
    /**
     * Add Attributes to the Element
     * @param type $keyName
     * @param type $keyValue
     */
    function addAttribute($keyName, $keyValue) {
        return $this->setAttribute($keyName, $keyValue);
    }

    /**
     * Add Opening and Closing Tags
     * @param type $openingTag
     * @param type $closingTag
     */
    function setTags($openingTag, $closingTag) {
        $this->openingTag = $openingTag;
        $this->closingTag = $closingTag;
    }

}

/**
 * Create a dynamic instance of a class
 * @param String $class Name of the class
 * @param String $params Params of the class
 * @return Class The new class
 */
function createInstance($class, $params) {
    $reflection_class = new ReflectionClass($class);
    return $reflection_class->newInstanceArgs($params);
}

function getHTMLAttributes($element) {
    $result = [];
    if (!empty($element->attributes)) {
        foreach ($element->attributes as $keyName => $keyValue) {
            $result[] = [$keyName => $keyValue->value];
        }
    }
    return $result;
}

function getHTMLText($element) {
    $result = "";
    if (!empty($element->childNodes)) {
        foreach (range(0, $element->childNodes->length - 1) as $idx) {
            if (!empty($element->childNodes->item($idx))) {
                if ($element->childNodes->item($idx)->nodeType == 3) {
                    $result .= $element->childNodes->item($idx)->nodeValue;
                }
            }
        }
    }
    return $result;
}

function traverseDOM($element) {
    $result = [];
    foreach ($element as $subelement) {
        if (!empty($subelement->tagName)) {
            $result[] = (object) ["tag" => $subelement->tagName, "attributes" => getHTMLAttributes($subelement), "content" => getHTMLText($subelement), "children" => traverseDOM($subelement->childNodes)];
        }
    }
    return $result;
}

function parseHTMLText($content) {
    $result = "";
    if (!empty($content)) {
        $result = '"' . $content . '"';
    } else {
        $result = "\n";
    }
    return $result;
}

/**
 * Parse all the HTML Attributes
 * @param type $attributes
 * @return string
 */
function parseHTMLAttributes($attributes) {
    $result = "";
    if (!empty($attributes)) {
        $result = [];
        foreach ($attributes as $aid => $attribute) {
            foreach ($attribute as $key => $value) {
                $result[] = '"' . $key . '"=>"' . $value . '"';
            }
        }
        $result = '[' . join(",", $result) . '],';
    }

    return $result;
}

/**
 * Make Shape code from an Array
 * @param type $shapeArray
 * @param type $level
 * @return string
 */
function arrayToShapeCode($shapeArray, $level = 0) {
    $code = "";
    $level++;
    $padding = str_repeat(" ", $level * 2);
    foreach ($shapeArray as $aid => $shape) {
        $code .= $padding . $shape->tag . '(' . parseHTMLAttributes($shape->attributes) . parseHTMLText($shape->content) . arrayToShapeCode($shape->children, $level) . $padding . ")";
        if ($aid < count($shapeArray) - 1) {
            $code .= ",\n";
        }
    }

    $code .= "\n";
    return $code;
}

/**
 * Convert the HTML content to Shape Code
 * @param String $content HTML from a website 
 * @return String Shape code
 */
function HTMLtoShape($content) {
    $dom = new DOMDocument;
    @$dom->loadHTML($content);

    $document = $dom->childNodes;

    $shapeArray = traverseDOM($document);

    $shapeCode = arrayToShapeCode($shapeArray);



    return $shapeCode;
}

/**
 * Anchor function
 * The <a> tag defines a hyperlink, which is used to link from one page to another.
 * @return htmlElement
 */
function a() {
    $html = createInstance("htmlElement", func_get_args());
    $html->setTags("<A[attributes]>", "</A>");
    return $html;
}

/**
 * Abbr function
 * The <abbr> tag defines an abbreviation or an acronym, like "Mr.", "Dec.", "ASAP", "ATM".
 * @return htmlElement
 */
function abbr() {
    $html = createInstance("htmlElement", func_get_args());
    $html->setTags("<ABBR[attributes]>", "</ABBR>");
    return $html;
}

/**
 * Acronym function
 * The <acronym> tag is not supported in HTML5. Use the <abbr> tag instead.
 * @return htmlElement
 */
function acronym() {
    $html = createInstance("htmlElement", func_get_args());
    $html->setTags("<ACRONYM[attributes]>", "</ACRONYM>");
    $html->setUnsupported();
    return $html;
}

/**
 * Address function
 * The <address> tag defines the contact information for the author/owner of a document or an article.
 * @return htmlElement
 */
function address() {
    $html = createInstance("htmlElement", func_get_args());
    $html->setTags("<ADDRESS[attributes]>", "</ADDRESS>");
    return $html;
}

/**
 * Applet function - not supported in HTML5
 * The <applet> tag is not supported in HTML5. Use the <object> tag instead.
 * @return htmlElement
 */
function applet() {
    $html = createInstance("htmlElement", func_get_args());
    $html->setTags("<APPLET[attributes]>", "</APPLET>");
    $html->setUnsupported();
    return $html;
}

/**
 * Area function
 * The <area> tag defines an area inside an image-map (an image-map is an image with clickable areas).
 * The <area> element is always nested inside a <map> tag.
 * @return htmlElement
 */
function area() {
    $html = createInstance("htmlElement", func_get_args());
    $html->setTags("<AREA[attributes]>", "");
    return $html;
}

/**
 * Article function
 * The <article> tag specifies independent, self-contained content.
 * @return htmlElement
 */
function article() {
    $html = createInstance("htmlElement", func_get_args());
    $html->setTags("<ARTICLE[attributes]>", "</ARTICLE>");
    return $html;
}

/**
 * Aside function
 * The <aside> tag defines some content aside from the content it is placed in.
 * @return htmlElement
 */
function aside() {
    $html = createInstance("htmlElement", func_get_args());
    $html->setTags("<ASIDE[attributes]>", "</ASIDE>");
    return $html;
}

/**
 * Audio function
 * The <audio> tag defines sound, such as music or other audio streams.
 * @return htmlElement
 */
function audio() {
    $html = createInstance("htmlElement", func_get_args());
    $html->setTags("<AUDIO CONTROLS[attributes]>", "</AUDIO>");
    return $html;
}

/**
 * Bold function
 * The <b> tag specifies bold text.
 * @return htmlElement
 */
function b() {
    $html = createInstance("htmlElement", func_get_args());
    $html->setTags("<B[attributes]>", "</B>");
    return $html;
}

/**
 * Base function
 * Specify a default URL and a default target for all links on a page
 * @return type
 */
function base() {
    $html = createInstance("htmlElement", func_get_args());
    $html->setTags("<BASE[attributes]>", "");
    return $html;
}

/**
 * Basefont function
 * The <basefont> tag is not supported in HTML5. Use CSS instead.
 * @return type
 */
function basefont() {
    $html = createInstance("htmlElement", func_get_args());
    $html->setTags("<BASEFONT[attributes]>", "");
    $html->setUnsupported();
    return $html;
}

/**
 * BDI function
 * The <bdi> tag isolates a part of text that might be formatted in a different direction from other text outside it.
 * @return type
 */
function bdi() {
    $html = createInstance("htmlElement", func_get_args());
    $html->setTags("<BDI[attributes]>", "</BDI>");
    return $html;
}

/**
 * BDO function
 * The <bdo> tag is used to override the current text direction.
 * @return type
 */
function bdo() {
    $html = createInstance("htmlElement", func_get_args());
    $html->setTags("<BDO[attributes]>", "</BDO>");
    return $html;
}

/**
 * Big function
 * The <big> tag is not supported in HTML5. Use CSS instead.
 * @return type
 */
function big() {
    $html = createInstance("htmlElement", func_get_args());
    $html->setTags("<BIG[attributes]>", "</BIG>");
    $html->setUnsupported();
    return $html;
}

/**
 * BlockQuote function
 * The <blockquote> tag specifies a section that is quoted from another source.
 * @return type
 */
function blockquote() {
    $html = createInstance("htmlElement", func_get_args());
    $html->setTags("<BLOCKQUOTE[attributes]>", "</BLOCKQUOTE>");
    return $html;
}

/**
 * Body function
 * The <body> tag defines the document's body.
 * @return type
 */
function body() {
    $html = createInstance("htmlElement", func_get_args());
    $html->setTags("<BODY[attributes]>", "</BODY>");
    return $html;
}

/**
 * Break tag
 * Use the <br> tag to enter line breaks, not to separate paragraphs.
 * @return type
 */
function br() {
    $html = createInstance("htmlElement", func_get_args());
    $html->setTags("<BR[attributes]/>", "");
    return $html;
}

/**
 * Button function
 * The <button> tag defines a clickable button.
 * @return type
 */
function button() {
    $html = createInstance("htmlElement", func_get_args());
    $html->setTags("<BUTTON[attributes]>", "</BUTTON>");
    return $html;
}

/**
 * Canvas function
 * The <canvas> tag is used to draw graphics, on the fly, via scripting (usually JavaScript).
 * @return type
 */
function canvas() {
    $html = createInstance("htmlElement", func_get_args());
    $html->setTags("<CANVAS[attributes]>", "</CANVAS>");
    return $html;
}

/**
 * Caption function
 * The <caption> tag defines a table caption.
 * The <caption> tag must be inserted immediately after the <table> tag
 * @return type
 */
function caption() {
    $html = createInstance("htmlElement", func_get_args());
    $html->setTags("<CAPTION[attributes]>", "</CAPTION>");
    return $html;
}

/**
 * Center function
 * The <center> tag is not supported in HTML5. Use CSS instead.
 * @return type
 */
function center() {
    $html = createInstance("htmlElement", func_get_args());
    $html->setTags("<P[attributes]>", "</P>");
    $html->setUnsupported();
    return $html;
}

/**
 * Cite function
 * The <cite> tag defines the title of a work (e.g. a book, a song, a movie, a TV show, a painting, a sculpture, etc.).
 * @return type
 */
function cite() {
    $html = createInstance("htmlElement", func_get_args());
    $html->setTags("<CITE[attributes]>", "</CITE>");
    return $html;
}

/**
 * Code function
 * The <code> tag is a phrase tag. It defines a piece of computer code. 
 * @return type
 */
function code() {
    $html = createInstance("htmlElement", func_get_args());
    $html->setTags("<CODE[attributes]>", "</CODE>");
    return $html;
}

/**
 * Col function
 * The <col> tag specifies column properties for each column within a <colgroup> element.
 * @return type
 */
function col() {
    $html = createInstance("htmlElement", func_get_args());
    $html->setTags("<COL[attributes]>", "");
    return $html;
}

/**
 * Colgroup function
 * The <colgroup> tag specifies a group of one or more columns in a table for formatting.
 * @return type
 */
function colgroup() {
    $html = createInstance("htmlElement", func_get_args());
    $html->setTags("<COLGROUP[attributes]>", "</COLGROUP>");
    return $html;
}

/**
 * Datalist function
 * The <datalist> tag specifies a list of pre-defined options for an <input> element.
 * @return type
 */
function datalist() {
    $html = createInstance("htmlElement", func_get_args());
    $html->setTags("<DATALIST[attributes]>", "</DATALIST>");
    return $html;
}

function dd() {
    $html = createInstance("htmlElement", func_get_args());
    $html->setTags("<DD[attributes]>", "</DD>");
    return $html;
}

/**
 * The <dl> tag defines a description list.
 * @return type
 */
function dl() {
    $html = createInstance("htmlElement", func_get_args());
    $html->setTags("<DL[attributes]>", "</DL>");
    return $html;
}

function dt() {
    $html = createInstance("htmlElement", func_get_args());
    $html->setTags("<DT[attributes]>", "</DT>");
    return $html;
}

/**
 * The <del> tag defines text that has been deleted from a document.
 * @return type
 */
function del() {
    $html = createInstance("htmlElement", func_get_args());
    $html->setTags("<DEL[attributes]>", "</DEL>");
    return $html;
}

function details() {
    $html = createInstance("htmlElement", func_get_args());
    $html->setTags("<DETAILS[attributes]>", "</DETAILS>");
    return $html;
}

function dfn() {
    $html = createInstance("htmlElement", func_get_args());
    $html->setTags("<DFN[attributes]>", "</DFN>");
    return $html;
}

function dialog() {
    $html = createInstance("htmlElement", func_get_args());
    $html->setTags("<DIALOG[attributes]>", "</DIALOG>");
    return $html;
}

/**
 * Dir function
 * The <dir> tag is not supported in HTML5. Use CSS instead.
 * @return type
 */
function adir() {
    $html = createInstance("htmlElement", func_get_args());
    $html->setTags("<DIR[attributes]>", "</DIR>");
    $html->setUnsupported();
    return $html;
}

/**
 * Div function
 * The <div> tag defines a division or a section in an HTML document.
 * @return type
 */
function div() {
    $html = createInstance("htmlElement", func_get_args());
    $html->setTags("<DIV[attributes]>", "</DIV>");
    return $html;
}

/**
 * Doctype for beginning of html page
 * The <!DOCTYPE> declaration must be the very first thing in your HTML document, before the <html> tag.
 * @return htmlElement
 */
function doctype() {
    $html = createInstance("htmlElement", func_get_args());
    $html->setTags("<!DOCTYPE html[attributes]", ">");
    return $html;
}

/**
 * The <em> tag is a phrase tag. It renders as emphasized text.
 * @return type
 */
function em() {
    $html = createInstance("htmlElement", func_get_args());
    $html->setTags("<P[attributes]>", "</P>");
    return $html;
}

/**
 * The <embed> tag defines a container for an external application or interactive content (a plug-in).
 * @return type
 */
function embed() {
    $html = createInstance("htmlElement", func_get_args());
    $html->setTags("<EMBED[attributes]>", "");
    return $html;
}

/**
 * The <fieldset> tag is used to group related elements in a form.
 * @return type
 */
function fieldset() {
    $html = createInstance("htmlElement", func_get_args());
    $html->setTags("<FIELDSET[attributes]>", "</FIELDSET>");
    return $html;
}

/**
 * The <figcaption> tag defines a caption for a <figure> element.
 * @return type
 */
function figcaption() {
    $html = createInstance("htmlElement", func_get_args());
    $html->setTags("<FIGCAPTION[attributes]>", "</FIGCAPTION>");
    return $html;
}

/**
 * The <figure> tag specifies self-contained content, like illustrations, diagrams, photos, code listings, etc.
 * @return type
 */
function figure() {
    $html = createInstance("htmlElement", func_get_args());
    $html->setTags("<FIGURE[attributes]>", "</FIGURE>");
    return $html;
}

function font() {
    $html = createInstance("htmlElement", func_get_args());
    $html->setTags("<FONT[attributes]>", "</FONT>");
    $html->setUnsupported();
    return $html;
}

function footer() {
    $html = createInstance("htmlElement", func_get_args());
    $html->setTags("<FOOTER[attributes]>", "</FOOTER>");
    return $html;
}

function form() {
    $html = createInstance("htmlElement", func_get_args());
    $html->setTags("<FORM[attributes]>", "</FORM>");
    return $html;
}

function frame() {
    $html = createInstance("htmlElement", func_get_args());
    $html->setTags("<FRAMESET[attributes]>", "");
    return $html;
}

function frameset() {
    $html = createInstance("htmlElement", func_get_args());
    $html->setTags("<FRAMESET[attributes]>", "</FRAMESET>");
    $html->setUnsupported();
    return $html;
}

/**
 * The <head> element is a container for all the head elements.
 * @return type
 */
function head() {
    $html = createInstance("htmlElement", func_get_args());
    $html->setTags("<HEAD[attributes]>", "</HEAD>");
    return $html;
}

/**
 * The <header> element represents a container for introductory content or a set of navigational links.
 * @return type
 */
function aheader() {
    $html = createInstance("htmlElement", func_get_args());
    $html->setTags("<HEADER[attributes]>", "</HEADER>");
    return $html;
}

/**
 * The <hgroup> tag is used to group heading elements.
 * @return type
 */
function hgroup() {
    $html = createInstance("htmlElement", func_get_args());
    $html->setTags("<HGROUP[attributes]>", "</HGROUP>");
    return $html;
}

/**
 * The <h1> to <h6> tags are used to define HTML headings.
 * @return htmlElement
 */
function h1() {
    $html = createInstance("htmlElement", func_get_args());
    $html->setTags("<H1[attributes]>", "</H1>");
    return $html;
}

/**
 * The <h1> to <h6> tags are used to define HTML headings.
 * @return htmlElement
 */
function h2() {
    $html = createInstance("htmlElement", func_get_args());
    $html->setTags("<H2[attributes]>", "</H2>");
    return $html;
}

/**
 * The <h1> to <h6> tags are used to define HTML headings.
 * @return htmlElement
 */
function h3() {
    $html = createInstance("htmlElement", func_get_args());
    $html->setTags("<H3[attributes]>", "</H3>");
    return $html;
}

/**
 * The <h1> to <h6> tags are used to define HTML headings.
 * @return htmlElement
 */
function h4() {
    $html = createInstance("htmlElement", func_get_args());
    $html->setTags("<H4[attributes]>", "</H4>");
    return $html;
}

/**
 * The <h1> to <h6> tags are used to define HTML headings.
 * @return htmlElement
 */
function h5() {
    $html = createInstance("htmlElement", func_get_args());
    $html->setTags("<H5[attributes]>", "</H5>");
    return $html;
}

/**
 * The <h1> to <h6> tags are used to define HTML headings.
 * @return htmlElement
 */
function h6() {
    $html = createInstance("htmlElement", func_get_args());
    $html->setTags("<H6[attributes]>", "</H6>");
    return $html;
}

/**
 * In HTML5, the <hr> tag defines a thematic break.
 */
function hr() {
    $html = createInstance("htmlElement", func_get_args());
    $html->setTags("<HR[attributes] />", "");
    return $html;
}

/**
 * Html function
 * The <html> tag tells the browser that this is an HTML document.
 * @return type
 */
function html() {
    $html = createInstance("htmlElement", func_get_args());
    $html->setTags("<HTML[attributes]>", "</HTML>");
    return $html;
}

/**
 * The <i> tag defines a part of text in an alternate voice or mood.
 * @return type
 */
function i() {
    $html = createInstance("htmlElement", func_get_args());
    $html->setTags("<I[attributes]>", "</I>");
    return $html;
}

/**
 * The <iframe> tag specifies an inline frame.
 * @return type
 */
function iframe() {
    $html = createInstance("htmlElement", func_get_args());
    $html->setTags("<IFRAME[attributes]>", "</IFRAME>");
    return $html;
}

function img() {
    $html = createInstance("htmlElement", func_get_args());
    $html->setTags("<IMG[attributes]>", "");
    return $html;
}

function input() {
    $html = createInstance("htmlElement", func_get_args());
    $html->setTags("<INPUT[attributes] value=\"", "\">");
    return $html;
}

function ins() {
    $html = createInstance("htmlElement", func_get_args());
    $html->setTags("<INS[attributes]>", "</INS>");
    return $html;
}

function kbd() {
    $html = createInstance("htmlElement", func_get_args());
    $html->setTags("<KBD[attributes]>", "</KBD>");
    return $html;
}

function keygen() {
    $html = createInstance("htmlElement", func_get_args());
    $html->setTags("<KEYGEN[attributes]>", "");
    return $html;
}

function label() {
    $html = createInstance("htmlElement", func_get_args());
    $html->setTags("<LABEL[attributes]>", "</LABEL>");
    return $html;
}

function legend() {
    $html = createInstance("htmlElement", func_get_args());
    $html->setTags("<LEGEND[attributes]>", "</LEGEND>");
    return $html;
}

function li() {
    $html = createInstance("htmlElement", func_get_args());
    $html->setTags("<LI[attributes]>", "</LI>");
    return $html;
}

function alink() {
    $html = createInstance("htmlElement", func_get_args());
    $html->setTags("<LINK[attributes]>", "");
    return $html;
}

function main() {
    $html = createInstance("htmlElement", func_get_args());
    $html->setTags("<MAIN[attributes]>", "</MAIN>");
    return $html;
}

function map() {
    $html = createInstance("htmlElement", func_get_args());
    $html->setTags("<MAP[attributes]>", "</MAP>");
    return $html;
}

function mark() {
    $html = createInstance("htmlElement", func_get_args());
    $html->setTags("<MARK[attributes]>", "</MARK>");
    return $html;
}

function menu() {
    $html = createInstance("htmlElement", func_get_args());
    $html->setTags("<MENU[attributes]>", "</MENU>");
    return $html;
}

function menuitem() {
    $html = createInstance("htmlElement", func_get_args());
    $html->setTags("<MENUITEM[attributes]>", "</MENUITEM>");
    return $html;
}

function meta() {
    $html = createInstance("htmlElement", func_get_args());
    $html->setTags("<META[attributes]>", "");
    return $html;
}

function meter() {
    $html = createInstance("htmlElement", func_get_args());
    $html->setTags("<METER[attributes]>", "</METER>");
    return $html;
}

function nav() {
    $html = createInstance("htmlElement", func_get_args());
    $html->setTags("<NAV[attributes]>", "</NAV>");
    return $html;
}

function noframes() {
    $html = createInstance("htmlElement", func_get_args());
    $html->setTags("<NOFRAMES[attributes]>", "</NOFRAMES>");
    return $html;
}

function noscript() {
    $html = createInstance("htmlElement", func_get_args());
    $html->setTags("<NOSCRIPT[attributes]>", "</NOSCRIPT>");
    return $html;
}

function object() {
    $html = createInstance("htmlElement", func_get_args());
    $html->setTags("<OBJECT[attributes]>", "</OBJECT>");
    return $html;
}

function ol() {
    $html = createInstance("htmlElement", func_get_args());
    $html->setTags("<OL[attributes]>", "</OL>");
    return $html;
}

function optgroup() {
    $html = createInstance("htmlElement", func_get_args());
    $html->setTags("<OPTGROUP[attributes]>", "</OPTGROUP>");
    return $html;
}

function option() {
    $html = createInstance("htmlElement", func_get_args());
    $html->setTags("<OPTION[attributes]>", "</OPTION>");
    return $html;
}

function output() {
    $html = createInstance("htmlElement", func_get_args());
    $html->setTags("<OUTPUT[attributes]>", "</OUTPUT>");
    return $html;
}

function p() {
    $html = createInstance("htmlElement", func_get_args());
    $html->setTags("<P[attributes]>", "</P>");
    return $html;
}

function param() {
    $html = createInstance("htmlElement", func_get_args());
    $html->setTags("<PARAM[attributes]>", "");
    return $html;
}

function pre() {
    $html = createInstance("htmlElement", func_get_args());
    $html->setTags("<PRE[attributes]>", "</PRE>");
    return $html;
}

function progress() {
    $html = createInstance("htmlElement", func_get_args());
    $html->setTags("<PROGRESS[attributes]>", "</PROGRESS>");
    return $html;
}

function q() {
    $html = createInstance("htmlElement", func_get_args());
    $html->setTags("<Q[attributes]>", "</Q>");
    return $html;
}

function rp() {
    $html = createInstance("htmlElement", func_get_args());
    $html->setTags("<RP[attributes]>", "</RP>");
    return $html;
}

function rt() {
    $html = createInstance("htmlElement", func_get_args());
    $html->setTags("<RT[attributes]>", "</RT>");
    return $html;
}

function ruby() {
    $html = createInstance("htmlElement", func_get_args());
    $html->setTags("<RUBY[attributes]>", "</RUBY>");
    return $html;
}

function s() {
    $html = createInstance("htmlElement", func_get_args());
    $html->setTags("<S[attributes]>", "</S>");
    return $html;
}

function samp() {
    $html = createInstance("htmlElement", func_get_args());
    $html->setTags("<SAMP[attributes]>", "</SAMP>");
    return $html;
}

function script() {
    $html = createInstance("htmlElement", func_get_args());
    $html->setTags("<SCRIPT[attributes]>", "</SCRIPT>");
    return $html;
}

function section() {
    $html = createInstance("htmlElement", func_get_args());
    $html->setTags("<SECTION[attributes]>", "</SECTION>");
    return $html;
}

function select() {
    $html = createInstance("htmlElement", func_get_args());
    $html->setTags("<SELECT[attributes]>", "</SELECT>");
    return $html;
}

/**
 * Container for HTML
 * @return type
 */
function shape() {
    $html = createInstance("htmlElement", func_get_args());
    $html->setTags("", "");
    return $html;
}

function small() {
    $html = createInstance("htmlElement", func_get_args());
    $html->setTags("<SMALL[attributes]>", "</SMALL>");
    return $html;
}

function source() {
    $html = createInstance("htmlElement", func_get_args());
    $html->setTags("<SOURCE[attributes]>", "");
    return $html;
}

function span() {
    $html = createInstance("htmlElement", func_get_args());
    $html->setTags("<SPAN[attributes]>", "</SPAN>");
    return $html;
}

function strike() {
    $html = createInstance("htmlElement", func_get_args());
    $html->setTags("<STRIKE[attributes]>", "</STRIKE>");
    return $html;
}

function strong() {
    $html = createInstance("htmlElement", func_get_args());
    $html->setTags("<STRONG[attributes]>", "</STRONG>");
    return $html;
}

function style() {
    $html = createInstance("htmlElement", func_get_args());
    $html->setTags("<STYLE[attributes]>", "</STYLE>");
    return $html;
}

function sub() {
    $html = createInstance("htmlElement", func_get_args());
    $html->setTags("<SUB[attributes]>", "</SUB>");
    return $html;
}

function summary() {
    $html = createInstance("htmlElement", func_get_args());
    $html->setTags("<SUMMARY[attributes]>", "</SUMMARY>");
    return $html;
}

function sup() {
    $html = createInstance("htmlElement", func_get_args());
    $html->setTags("<SUP[attributes]>", "</SUP>");
    return $html;
}

function table() {
    $html = createInstance("htmlElement", func_get_args());
    $html->setTags("<TABLE[attributes]>", "</TABLE>");
    return $html;
}

function tbody() {
    $html = createInstance("htmlElement", func_get_args());
    $html->setTags("<TBODY[attributes]>", "</TBODY>");
    return $html;
}

function td() {
    $html = createInstance("htmlElement", func_get_args());
    $html->setTags("<TD[attributes]>", "</TD>");
    return $html;
}

function textarea() {
    $html = createInstance("htmlElement", func_get_args());
    $html->setTags("<TEXTAREA[attributes]>", "</TEXTAREA>");
    return $html;
}

function tfoot() {
    $html = createInstance("htmlElement", func_get_args());
    $html->setTags("<TFOOT[attributes]>", "</TFOOT>");
    return $html;
}

function th() {
    $html = createInstance("htmlElement", func_get_args());
    $html->setTags("<TH[attributes]>", "</TH>");
    return $html;
}

function thead() {
    $html = createInstance("htmlElement", func_get_args());
    $html->setTags("<THEAD[attributes]>", "</THEAD>");
    return $html;
}

function atime() {
    $html = createInstance("htmlElement", func_get_args());
    $html->setTags("<TIME[attributes]>", "</TIME>");
    return $html;
}

function title() {
    $html = createInstance("htmlElement", func_get_args());
    $html->setTags("<TITLE[attributes]>", "</TITLE>");
    return $html;
}

function tr() {
    $html = createInstance("htmlElement", func_get_args());
    $html->setTags("<TR[attributes]>", "</TR>");
    return $html;
}

function track() {
    $html = createInstance("htmlElement", func_get_args());
    $html->setTags("<TRACK[attributes]>", "");
    return $html;
}

function tt() {
    $html = createInstance("htmlElement", func_get_args());
    $html->setTags("<TT[attributes]>", "</TT>");
    return $html;
}

function u() {
    $html = createInstance("htmlElement", func_get_args());
    $html->setTags("<U[attributes]>", "</U>");
    return $html;
}

function ul() {
    $html = createInstance("htmlElement", func_get_args());
    $html->setTags("<UL[attributes]>", "</UL>");
    return $html;
}

function vari() {
    $html = createInstance("htmlElement", func_get_args());
    $html->setTags("<VAR[attributes]>", "</VAR>");
    return $html;
}

function video() {
    $html = createInstance("htmlElement", func_get_args());
    $html->setTags("<VIDEO[attributes] controls>", "</VIDEO>");
    return $html;
}

function wbr() {
    $html = createInstance("htmlElement", func_get_args());
    $html->setTags("<WBR[attributes]>", "</WBR>");
    return $html;
}
