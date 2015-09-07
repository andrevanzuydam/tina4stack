<?php
/**
 * Reta makes the reporting possible by using the open source engine of Report Manager
 * Reta requires wine to run on Linux  and uses the reta.sh shell wrapper
 *
 * Currently supported database engines are: sqlite3, firebird-2.5, mysql
 *
 */
class Reta {

    private $retaPath;
    private $reportPath;
    private $outputPath;
    private $iniFile;

    /**
     * The constructor for Reta
     * @param String $reportPath Relative to the document root
     * @param String $outputPath Relative to the document root
     * @param String $iniFile Path to the iniFile, normally is in the same folder as reta, but you may want more.
     * @param String $retaPath Path to reta if the system can't determine it, usually one folder below the document root
     */
    function __construct($reportPath="reports", $outputPath="output", $iniFile="reta.ini", $retaPath="") {
        $this->reportPath = Ruth::getDOCUMENT_ROOT()."/".$reportPath;
        $this->outputPath = Ruth::getDOCUMENT_ROOT()."/".$outputPath;
        $this->iniFile = $iniFile;
        if (empty($retaPath)) {
            $this->retaPath = realpath( Ruth::getDOCUMENT_ROOT().'/..').'/reta.exe';
            $this->iniFile = realpath( Ruth::getDOCUMENT_ROOT().'/..').'/reta.ini';
        }
        else {
            $this->retaPath  = $retaPath;
        }


        if (!file_exists($this->iniFile)) {
            echo "<pre>";
            echo "Please setup a reta.ini in the same folder as {$this->retaPath}  for reporting to work:\n";
            echo "[Database]\nProtocol=firebird-2.5,sqlite-3,mysql\nHostName=127.0.0.1\nPath={DBPATH}\nUser=SYSDBA\nPassword=masterkey\n";
            die();
        }

        //Check if we may be on Linux - then we need to run the shell script
        if (PHP_OS === 'Linux') {
            $this->retaPath = str_replace(".exe", ".sh", $this->retaPath);
            $this->iniFile = "z:".$this->iniFile;
        }

    }


    /**
     * generate calls the rita engine to generate the report.
     * @param String $reportName The name of the report file without the rep extension
     * @param String $sql A valid SQL statement which will work with the database
     * @param String $outputType A comma separated string which can include : pdf,csv,xls,html
     * @param Boolean $debug Turn on the debugging messages for troubleshooting
     */
    function generate($reportName="test", $sql, $outputType="pdf,csv", $debug=false) {
        $result = "";

        if (PHP_OS === 'Linux') {
            $this->outputPath = "z:".$this->outputPath;
            $this->reportPath = "z:".$this->reportPath;
        }

        $sql = str_replace ("\n", " ", $sql);

        $command = '"'.$this->retaPath.'" -ini "'.$this->iniFile.'" -sql "'.$sql.'" -out "'.$this->outputPath.'" -tem "'.$this->reportPath.'/'.$reportName.'.rep" -typ "'.$outputType.'"';
        if ($debug) {
            $command .= " -debug";
        }

        exec ($command, $result);

        $result = join (" ", $result);

        $result = str_replace ("z\:", "", $result);

        return $result;
    }

}
