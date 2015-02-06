<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Phoebe
 *
 * @author Andre van Zuydam <andre@xineoh.com>
 */
class Phoebe {
    //put your code here
    
    function __construct($pharName) {
        echo "<pre>";
        $buildRoot = realpath(Ruth::getDOCUMENT_ROOT()."/../build");        
        $srcRoot = realpath(Ruth::getDOCUMENT_ROOT());        
        
        echo "Building {$pharName}.phar to ".$buildRoot."\n";
        echo "Source: {$srcRoot} \n";
        echo "Have you cleaned up the path yet ?";
        
        //clean up things
        
        $phar = new Phar($buildRoot."/".$pharName.".phar", FilesystemIterator::CURRENT_AS_FILEINFO | FilesystemIterator::KEY_AS_FILENAME, $pharName.".phar");
        $phar["index.php"] = file_get_contents($srcRoot . "/index.php");
        $phar->buildFromDirectory($srcRoot);
        $phar->setStub('<?php Phar::webPhar(); __HALT_COMPILER();');
        
        
        
        echo "<pre>";
    }
    
    
}
