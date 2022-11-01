<?php
    namespace dblink_file\classes;

    require_once(__DIR__ . "/AbstractDBEngine.php");
    require_once(__DIR__ . "/AbstractDBTable.php");

    class AbstractDBDatabase {
        protected AbstractDBEngine $dbEngine;
        protected array $tablesInfo = array();
        protected array $tables = array();

        protected bool $needsSaved = false;
        

        public function __construct(AbstractDBEngine $dbEngine, string $filePath = "") {
            $this->dbEngine = $dbEngine;

            if ($filePath != "") {
                if (file_exists($filePath))
                    $this->Load($filePath);
            }
        }

        public function __destruct() {
        }

        public function GetTableNames() : array {
            return array_keys($this->tables);
        }

        public function GetTableStructure(string $tableName) {
            if (!isset($this->tablesInfo[$tableName]))
                return $this->dbEngine->SetCurrentError("1", "$tableName does not exist.");

            return array_merge($this->tablesInfo[$tableName], $this->tables[$tableName]->GetFieldsInfo());

        }

        public function GetNeedsSaved() : bool {
            return $this->needsSaved;
        }

        public function Save(string $filePath) : bool {
            $returnStatus = file_put_contents($filePath, serialize(array($this->tablesInfo, $this->tables)));

            if (!$returnStatus)
                return false;

            $this->needsSaved = false;

            return true;
        }

        public function Load(string $filePath) : bool {            
            if (($fileData = file_get_contents($filePath)) === false)
                return false;

            print_r($fileData);
            print_r(unserialize($fileData));
            die();

            list ($this->tablesInfo, $this->tables) = unserialize($fileData);

            $this->needsSaved = false;

            return true;
        }

        public function CreateTable($tableName, array $fieldsInfo) : bool {
            if (isset($this->tablesInfo[$tableName]))
                return $this->dbEngine->SetCurrentError(1, "Table '$tableName' already exists");

            $newTable = new AbstractDBTable($this, $tableName, "");

            foreach ($fieldsInfo as $fieldName => $fieldInfo) {
                if (!$newTable->RegisterField($fieldName, $fieldInfo["type"], $fieldInfo["length"], $fieldInfo["default"], $fieldInfo["defaultValue"], $fieldInfo["attributes"], $fieldInfo["autoIncrement"], $fieldInfo["persistent"]))
                    return $this->dbEngine->SetCurrentError("1", "table->RegisterField Failed.");
            }

            
            $this->tables[$tableName] = $newTable;
            $this->needsSaved = true;

            return true;
        }

/*


CREATE TABLE `ynhawebforms`.`test` ( 
    `id` BIGINT NOT NULL AUTO_INCREMENT , 
    `firstName` VARCHAR(50) NOT NULL , 
    `middleInitial` CHAR(1) NOT NULL , 
    `lastName` VARCHAR(50) NOT NULL , 
    `birthYear` INT UNSIGNED NOT NULL , 
    PRIMARY KEY (`id`)
) ENGINE = InnoDB;




*/

    }

?>