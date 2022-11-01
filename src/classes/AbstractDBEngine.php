<?php
    namespace dblink_file\classes;

    require_once(__DIR__ . "/AbstractParseQueryString.php");

    require_once(__DIR__ . "/AbstractDBDatabase.php");

    class AbstractDBEngine {
        protected array $databaseFilePaths = array();

        protected $selectedDatabase = "";

        protected array $databases = array();

        protected $error = "";
        protected $errno = 0;

        public function __construct(array $databaseFilePaths = array()) {
            foreach ($databaseFilePaths as $path)
                $this->LoadDatabase($path);                
        }

        public function __destruct() {
            foreach (array_keys($this->databases) as $databaseName) {
                $this->SaveDatabase($databaseName);                
            }
        }

        public static function GetQuery(array $parsedQueryArray) {
            $returnValue = "";

            foreach ($parsedQueryArray as $k => $v) {
                if (is_array($v))
                    $returnValue .= GetQuery($v) . " ";
                else
                    $returnValue .= $v . " ";
            }

            return $returnValue;
        }

        public static function TrimEdgeQuotes($str, array $trimCharArray = array("'", '"', '`')) {
            if (!is_string($str))
                return $str;

            if (strlen($str) <= 2)
                return $str;

            $head = 0;
            $tail = 0;

            if (in_array($str[0], $trimCharArray))            
                $head = 1;

            if (in_array($str[strlen($str)-1], $trimCharArray))
                $tail = 1;

            return substr($str, $head, strlen($str) - $head - $tail);
        }

        public function Errno() {
            return $this->errno;
        }

        public function Error() {
            return $this->error;
        }

        public function LoadDatabase(string $databaseFilePath) : bool {
            $databaseName = substr($databaseFilePath, (($rpos = strrpos($databaseFilePath, '/')) == false ? 0 : $rpos + 1));
            $databaseName = substr($databaseName, 0, (($lpos = strpos($databaseName, ".")) === false ? strlen($databaseName) : $lpos));            

            if (isset($this->databaseFilePaths[$databaseName]) || isset($this->databases[$databaseName]))
                return $this->SetCurrentError(1, "LoadDatabase($databaseFilePath) failed.");

            $database = new AbstractDBDatabase($this);

            if (!$database->Load($databaseFilePath))
                return $this->SetCurrentError(1, "LoadDatabase($databaseFilePath) failed.");
                
            $this->databaseFilePaths[$databaseName] = $databaseFilePath;
            $this->databases[$databaseName] = $database;

            return true;
        }

        public function SaveDatabase($databaseName) : bool {
            if (!isset($this->databaseFilePaths[$databaseName]) || !isset($this->databases[$databaseName]))
                return $this->SetCurrentError(1, "SaveDatabase($databaseName) failed.");

            if (!$this->databases[$databaseName]->Save($this->databaseFilePaths[$databaseName]))
                return $this->SetCurrentError(1, "SaveDatabase($databaseName) failed.");

            return true;
        }

        public function SetCurrentError(int $errno, string $error) : bool {
            $this->error = $error;
            $this->errno = $errno;
            
            return $errno == 0;
        }
        
        public function GetSelectedDatabase() {
            return $this->selectedDatabase;
        }
        
        public function CreateDatabase(string $databaseName) : bool {
            if (isset($this->databases[$databaseName]))
                return $this->SetCurrentError(1, "Database already exists: $databaseName");

            $this->databases[$databaseName] = new AbstractDBDatabase($this);
            $this->databaseFilePaths[$databaseName] = $databaseName . ".fdb";

            $this->SaveDatabase($databaseName);

            return true;
        }

        protected function ProcessUseQuery(array &$parsedQueryArray) {
            if (count($parsedQueryArray) < 2)
                return $this->SetCurrentError(1, "You have an error in your SQL syntax near: " . self::GetQuery($parsedQueryArray));
                            
            $databaseName = array_shift($parsedQueryArray);
            $semicolon = array_shift($parsedQueryArray);

            if ($semicolon != ";")
                return $this->SetCurrentError(1, "You have an error in your SQL syntax near: " . self::GetQuery(array_merge(array($semicolon), $parsedQueryArray)));

            if (!isset($this->databases[$databaseName]))
                return $this->SetCurrentError(1, "Unknown database '$databaseName'");

            $this->selectedDatabase = $databaseName;

            return true;
        }


        
/*

CREATE TABLE `test` ( 
    `id` BIGINT NOT NULL AUTO_INCREMENT , 
    `firstName` VARCHAR(50) NOT NULL , 
    `middleInitial` CHAR(1) NOT NULL , 
    `lastName` VARCHAR(50) NOT NULL , 
    `birthYear` INT UNSIGNED NOT NULL , 
    PRIMARY KEY (`id`)
) ENGINE = InnoDB;

CREATE TABLE `testdatabase`.`test` ( 
    `id` BIGINT NOT NULL AUTO_INCREMENT , 
    `firstName` VARCHAR(50) NOT NULL , 
    `middleInitial` CHAR(1) NOT NULL , 
    `lastName` VARCHAR(50) NOT NULL , 
    `birthYear` INT UNSIGNED NOT NULL , 
    PRIMARY KEY (`id`));


) ENGINE = InnoDB;

*/
        protected function ProcessCreateTableQuery(array &$parsedQueryArray) {
            if (count($parsedQueryArray) < 2)
                return $this->SetCurrentError(1, "CREATE TABLE");

            $tableName = self::TrimEdgeQuotes(array_shift($parsedQueryArray));

            if (($value = self::TrimEdgeQuotes(array_shift($parsedQueryArray))) == ".") {
                $databaseName = $tableName;

                if (is_array(($value = self::TrimEdgeQuotes(array_shift($parsedQueryArray))))) 
                    return $this->SetCurrentError(1, "You have an error in your SQL syntax near: " . self::GetQuery($parsedQueryArray));
                
                $tableName = $value;
                
                $value = self::TrimEdgeQuotes(array_shift($parsedQueryArray));
            } else {
                if (($databaseName = $this->selectedDatabase) == "")
                    return $this->SetCurrentError(1, "No Database Selected.");

                
            }

            if (!isset($this->databases[$databaseName]))
                return $this->SetCurrentError(3, "Database $databaseName does not exist.");

            if (in_array($tableName, $this->databases[$databaseName]->GetTableNames()))
                return $this->SetCurrentError(4, "Database Table $tableName already exist.");

            if (!is_array($value))
                return $this->SetCurrentError(2, "You have an error in your SQL syntax near: " . self::GetQuery(array_merge(array($value), $parsedQueryArray)));

            $currentFieldName = "";
            $fieldsInfoArray = array();

            print_r($value);

            $fieldInfo = self::TrimEdgeQuotes(array_shift($value));

            do {
                if ($fieldInfo == ",") {
                    $currentFieldName = "";

                } else if ($currentFieldName == "") {
                    $currentFieldName = $fieldInfo;

                    if ($currentFieldName != "PRIMARY") {
                        $fieldsInfoArray[$currentFieldName] = array(
                            "name" => $currentFieldName,
                            "type" => "",
                            "length" => "",
                            "default" => "",
                            "defaultValue" => "",
                            "attributes" => "",
                            "autoIncrement" => false,
                            "persistent" => true
                        );
                    }

                } else if ($currentFieldName == "PRIMARY") {

                } else if ($fieldsInfoArray[$currentFieldName]["type"] == "") {
                    $fieldsInfoArray[$currentFieldName]["type"] = $fieldInfo;

                    if (is_array($value[0])) {
                        $fieldsInfoArray[$currentFieldName]["length"] = self::TrimEdgeQuotes(self::GetQuery(array_shift($value)), array("(", ")"));
                    } else if ($value[0] == "UNSIGNED") {
                        $fieldsInfoArray[$currentFieldName]["type"] .= " " . array_shift($value);
                    }
                } else if ($fieldInfo == "AUTO_INCREMENT") {
                    $fieldsInfoArray[$currentFieldName]["autoIncrement"] = true;
                } else if ($fieldInfo == "NOT") {
                    if (count($value) > 0 && $value[0] == "NULL") {
                        $fieldsInfoArray[$currentFieldName]["default"] = "NOT " . array_shift($value);
                    } else {
                        return $this->SetCurrentError(2, "You have an error in your SQL syntax near: " . self::GetQuery(array_merge(array($fieldInfo), $value)));
                    }

                    $fieldsInfoArray[$currentFieldName]["autoIncrement"] = true;
                } else {
                    echo "Skipping: $currentFieldName:!" . print_r($fieldInfo, true) . "!\n";
                }
                
                if (count($value) == 0)
                    break;

                $fieldInfo = self::TrimEdgeQuotes(array_shift($value));

            } while (true);            
            
            print_r($parsedQueryArray);

            while (count($parsedQueryArray) > 0) {
                $value = self::TrimEdgeQuotes(array_shift($parsedQueryArray));

                if ($value == ";")
                    return $this->databases[$databaseName]->CreateTable($tableName, $fieldsInfoArray);                
            }

            return $this->SetCurrentError(2, "You have an error in your SQL syntax : Missing semicolon.");            
        }

        protected function ProcessCreateDatabaseQuery(array &$parsedQueryArray) {
            if (count($parsedQueryArray) < 2)
            return $this->SetCurrentError(1, "You have an error in your SQL syntax near: " . self::GetQuery($parsedQueryArray));

            $databaseName = array_shift($parsedQueryArray);
            $semicolon = array_shift($parsedQueryArray);

            if ($semicolon != ";")
                return $this->SetCurrentError(1, "You have an error in your SQL syntax near: " . self::GetQuery(array_merge(array($semicolon), $parsedQueryArray)));

            return $this->CreateDatabase($databaseName);            
        }

        protected function ProcessCreateQuery(array &$parsedQueryArray) {
            if (count($parsedQueryArray) == 0)
                return $this->SetCurrentError(1, "You have an error in your SQL syntax near: " . self::GetQuery($parsedQueryArray));

            switch (strtoupper(($line = array_shift($parsedQueryArray)))) {
                case "DATABASE":
                    return $this->ProcessCreateDatabaseQuery($parsedQueryArray);

                case "TABLE":
                    return $this->ProcessCreateTableQuery($parsedQueryArray);

                default: 
                    return $this->SetCurrentError(1, "You have an error in your SQL syntax near: " . self::GetQuery(array_merge(array($line), $parsedQueryArray)));
            }
        }

        protected function ProcessShowTables(array &$parsedQueryArray) {
            if (count($parsedQueryArray) < 1)
                return $this->SetCurrentError(1, "You have an error in your SQL syntax near: " . self::GetQuery($parsedQueryArray));

            $semicolon = array_shift($parsedQueryArray);

            if ($semicolon != ";")
                return $this->SetCurrentError(1, "You have an error in your SQL syntax near: " . self::GetQuery(array_merge(array($semicolon), $parsedQueryArray)));

            if ($this->selectedDatabase == "")
                return $this->SetCurrentError(1, "No database selected");

            return $this->databases[$this->selectedDatabase]->GetTableNames();
        }

        protected function ProcessShowQuery(array &$parsedQueryArray) {
            if (count($parsedQueryArray) == 0)
                return $this->SetCurrentError(1, "You have an error in your SQL syntax near: " . self::GetQuery($parsedQueryArray));

            switch (strtoupper(($line = array_shift($parsedQueryArray)))) {
                case "DATABASES": 
                    return array_keys($this->databases);

                case "TABLES":
                    return $this->ProcessShowTables($parsedQueryArray);
                
                default:
                    return $this->SetCurrentError(1, "You have an error in your SQL syntax near: " . self::GetQuery(array_merge(array($line), $parsedQueryArray)));
                    
            }    
        }

        protected function ProcessDescribeQuery(array &$parsedQueryArray) {
            if (count($parsedQueryArray) < 2)
                return $this->SetCurrentError(1, "You have an error in your SQL syntax near: " . self::GetQuery($parsedQueryArray));

            $tableName = array_shift($parsedQueryArray);
            $semicolon = array_shift($parsedQueryArray);

            if ($semicolon != ";")
                return $this->SetCurrentError(1, "You have an error in your SQL syntax near: " . self::GetQuery(array_merge(array($semicolon), $parsedQueryArray)));

            if ($this->selectedDatabase == "")
                return $this->SetCurrentError(1, "No database selected");

            return $this->databases[$this->selectedDatabase]->GetTableStructure($tableName);
        }

        

        protected function ProcessQuery(array &$parsedQueryArray) {            
            switch (strtoupper(($line = array_shift($parsedQueryArray)))) {
                case "SHOW":
                    return $this->ProcessShowQuery($parsedQueryArray, 1);

                case "CREATE":
                    return $this->ProcessCreateQuery($parsedQueryArray, 1);

                case "DESCRIBE":
                    return $this->ProcessDescribeQuery($parsedQueryArray, 1);

                case "USE":
                    return $this->ProcessUseQuery($parsedQueryArray, 1);

                default:
                    return $this->SetCurrentError(1, "You have an error in your SQL syntax near: " . self::GetQuery(array_merge(array($line), $parsedQueryArray)));
            }

            return $parsedQueryArray;
        }

        public function Query(string $query) {
            $this->error = "";
            $this->errno = "";

            $tQuery = $query;

            if (($parsedQueryArray = AbstractParseQueryString::ParseQueryString($tQuery)) == null)
                return false;

            return $this->ProcessQuery($parsedQueryArray);
        }
    }



?>