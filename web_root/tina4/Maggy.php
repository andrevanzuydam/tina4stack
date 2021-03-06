<?php

/**
 * Description of Maggy
 * 
 * Maggy makes migrating of your database a breeze, all you need to do is make a migration folder in your web root and create files in the following format
 * 
 * There are some "rules" that Maggy prescribes for the migrations and it would be in your best interests to follow them.
 * 1.) File formats are in the following format:
 *     YYYYMMDDHHMMSS Description of migration followed by dot sql
 *     Example: 01012015101525 The first migration.sql 
 * 2.) Do not put commit statements in the SQL, this will make it impossible for a migration to fail or roll back. Instead make more files.
 *     A create table statement would be in its own file.
 * 3.) Stored procedures, triggers and views should be in their own files, they are run as individual statements and are commited so, delimter is not needed on the end of these.
 *     You do not need to change the delimiter either before running these statements. 
 *
 */
class Maggy {
    /**
     * The migration path
     * @var String 
     */
    private $migrationPath = "migration";
    /**
     * The delimiter
     * @var String
     */
    private $delim = ";";
    /**
     * The database connection
     * @var Debby
     */
    private $DEB = null;

    /**
     * Constructor for Maggy
     * 
     * There needs to be a Ruth Object DEB for the database declared using the Debby Class
     * The path is relative to your web folder.
     * 
     * @param String $migrationPath relative path to your web folder
     * @param String $delim A delimiter to say how your SQL is run
     */
    function __construct($migrationPath = "migration", $delim = ";", $runMigrations=false) {
        if (empty(Ruth::getOBJECT("DEB"))) {
            die("You need to declare a database connection using Debby and assign Ruth a DEB object");
        } else {
            $this->DEB = Ruth::getOBJECT("DEB");
        }
        $this->delim = $delim;
        $database = $this->DEB->getDatabase();
        
       
        $this->migrationPath = $migrationPath;

        if (empty($database["TINA4_MAGGY"]) && empty($database["tina4_maggy"])) {
            echo ((new Cody())->bootStrapAlert("success", $caption="Success", "Maggy: I need to create a migration table because it doesn't exist \n"));  
            
            $this->DEB->exec("create table tina4_maggy ("
                    . "migration_id varchar (14) not null,"
                    . "description varchar (1000) default '',"
                    . "content blob,"
                    . "passed integer default 0,"
                    . "primary key (migration_id))");
            $this->DEB->commit();
        }
        
        if ($runMigrations && file_exists ($this->migrationPath)) {
          //Run the migration     
          $this->doMigration();
        }
    }
    
    /**
     * Do Migration
     *
     * Do Migration finds the last possible migration based on what is read from the database on the constructor
     * It then opens the migration file, imports it into the database and tries to run each statement.
     * The migration files must run in sequence and Maggy will stop if she hits an error!
     *
     * DO NOT USE COMMIT STATEMENTS IN YOUR MIGRATIONS , RATHER BREAK THINGS UP INTO SMALLER LOGICAL PIECES
     */
    function doMigration() {
        $dirHandle = opendir(Ruth::getREAL_PATH() . "/" . $this->migrationPath);
        $error = false;
        set_time_limit ( 0 );

        echo "<pre>";
        echo "STARTING Maggy ....\n";

        $error = false;
        error_reporting(0);
        $fileArray = [];
        while (false !== ($entry = readdir($dirHandle)) && !$error) {
            if ($entry != "." && $entry != ".." && stripos($entry, ".sql")) {
                $fileParts = explode(".", $entry);
                $fileParts = explode(" ", $fileParts[0]);
                $fileArray[$fileParts[0]] = $entry;
            }
        }


        asort ($fileArray);



        foreach ( $fileArray as $fid => $entry ) {

                $fileParts = explode(".", $entry);
                $fileParts = explode(" ", $fileParts[0]);
                $sqlCheck = "select * from tina4_maggy where migration_id = '{$fileParts[0]}'";
                $record = $this->DEB->getRow($sqlCheck);




                $migrationId = $fileParts[0];
                unset($fileParts[0]);
                $description = join(" ", $fileParts);

                $content = file_get_contents(Ruth::getREAL_PATH() . "/" . $this->migrationPath . "/" . $entry);



                $runsql = false;
                if (empty($record)) {
                    echo "<span style=\"color:orange;\">RUNNING:\"{$migrationId} {$description}\" ...</span>\n";
                    $transId = $this->DEB->startTransaction();

                    $sqlInsert = "insert into tina4_maggy (migration_id, description, content, passed)
                                values ('{$migrationId}', '{$description}', ?, 0)";


                    $this->DEB->exec($sqlInsert, $transId, substr($content, 0, 10000));
                    $this->DEB->commit($transId);
                    $runsql = true;
                } else {

                    if ($record->PASSED === "0" || $record->PASSED === "" || $record->PASSED == 0) {
                        echo "<span style=\"color:orange;\">RETRY: \"{$migrationId} {$description}\" ... </span> \n";
                        $runsql = true;
                    } else
                    if ($record->PASSED === "1" || $record->PASSED == 1 ) {
                        echo "<span style=\"color:green;\">PASSED:\"{$migrationId} {$description}\"</span>\n";
                        $runsql = false;
                    }
                }

                if ($runsql) {
                    $transId = $this->DEB->startTransaction();
                    //before exploding the content, see if it is a stored procedure, trigger or view.
                    if (stripos ($content, "create trigger") === false && stripos ($content, "create procedure") === false && stripos ($content, "create view") === false ) {
                      $content = explode($this->delim, $content);
                    }
                     else {
                      $sql = $content;
                      $content = [];
                      $content[] = $sql;
                    }

                    $error = false;
                    foreach ($content as $cid => $sql) {
                        if (!empty(trim($sql))) {
                            $success = $this->DEB->exec($sql, $transId);

                            if (!$success) {
                                echo "<span style=\"color:red;\">FAILED: \"{$migrationId} {$description}\"</span>\nQUERY:{$sql}\nERROR:".$this->DEB->getError()."\n";
                                $error = true;
                                break;
                            } else {
                                echo "<span style=\"color:green;\">PASSED:</span> ";
                            }
                            echo $sql . " ...\n";
                        }
                    }

                    if ($error) {
                        echo "<span style=\"color:red;\">FAILED: \"{$migrationId} {$description}\"</span>\nAll Transactions Rolled Back ...\n";
                        $this->DEB->rollbackTransaction($transId);
                    } else {

                        $this->DEB->commit( $transId );

                        //we need to make sure the commit resulted in no errors
                        if ($this->DEB->getError() !== "No Error") {
                          echo "<span style=\"color:red;\">FAILED COMMIT: \"{$migrationId} {$description}\"</span>\nERROR:".$this->DEB->getError()."\n";
                          $this->DEB->rollbackTransaction($transId);
                          $error = true;
                          break;
                        }
                         else {
                           $transId = $this->DEB->startTransaction();
                           $this->DEB->exec ("update tina4_maggy set passed = 1 where migration_id = '{$migrationId}'", $transId);
                           $this->DEB->commit($transId);
                           echo "<span style=\"color:green;\">PASSED: \"{$migrationId} {$description}\"</span>\n";
                        }
                    }
                }



        }

        if (!$error) echo "FINISHED!";
        error_reporting(E_ALL);
        echo "</pre>";
    }
    
    function createMigration() {
        $html = $this->getPageTemplate("Create Migration");       
        $form = form (["class" => "form-group", "method" => "post",  "enctype" => "multipart/form-data"], 
                    (new Cody())->bootStrapInput("txtDESCRIPTION", $caption = "Description", $placeHolder = "Description", $defaultValue = ""),
                    (new Cody())->bootStrapTextArea("txtSQL", $caption = "SQL Metadata", $placeHolder = "SQL Text like a create statement", $defaultValue = ""),
                    (new Cody())->bootStrapButton("btnCreate", $caption = "Create")
                    
                );
        
        if (!empty(Ruth::getSESSION("maggyCreateMessage"))) {
           $html->addContent ((new Cody())->bootStrapAlert("success", $caption="Success", Ruth::getSESSION("maggyCreateMessage")));  
           Ruth::setSESSION("maggyCreateMessage", null);
        }
              
        $form = (new Cody())->bootStrapPanel("Create Migration", $form);
        
        $html->byId("content")->addContent ($form);


        
        return $html;
    }
    
    /**
     * The default page template for maggy
     * @param type $title String A title to name the page by
     * @return type Shape A page template with default bootstrap
     */
    function getPageTemplate($title="Default") {
       $html = html (
                    head (
                            title ($title),
                            alink (["rel" => "stylesheet", "href"=>"https://maxcdn.bootstrapcdn.com/bootstrap/3.3.2/css/bootstrap.min.css"]),
                            alink (["rel" => "stylesheet", "href"=> "https://maxcdn.bootstrapcdn.com/bootstrap/3.3.2/css/bootstrap-theme.min.css"]),
                            alink (["rel" => "stylesheet", "href"=> "https://cdnjs.cloudflare.com/ajax/libs/bootstrap-table/1.8.1/bootstrap-table.min.css"])


                    ),
                    body (  ["style" => "padding: 0px 20px 0px", "id" => "content"])

               );
       return $html;
    }
    
    function updateMigration() {
      $fileName = Ruth::getDOCUMENT_ROOT()."/".$this->migrationPath."/".date("Ymdhis")." ".Ruth::getREQUEST("txtDESCRIPTION").".sql";
      file_put_contents($fileName, Ruth::getREQUEST("txtSQL") );
      Ruth::setSESSION("maggyCreateMessage", "{$fileName} created successfully!");
      Ruth::redirect("/maggy/create");
    }

}
