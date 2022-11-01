<?php
    namespace dblink_file\classes;

    
    require_once(__DIR__ . "/AbstractDBDatabase.php");

    class AbstractDBTable {
        protected AbstractDBDatabase $dbDatabase;

        protected string $name;
        protected string $comments;

        protected array $fieldsInfo = array();
        protected array $rows = array();

        public function __construct(AbstractDBDatabase $dbDatabase, string $name, string $comments) {
            $this->dbDatabase = $dbDatabase;
            $this->name = $name;
            $this->comments = $comments;
        }

        public function __destruct() {

        }

        public function GetFieldsInfo(string $fieldName = "") {
            if ($fieldName == "")
                return $this->fieldsInfo;

            if (!isset($this->fieldsInfo[$fieldName]))
                return ($this->dbDatabase->dbEngine->SetError(1, "Field $fieldName does not exist."));                

            return $this->fieldsInfo[$fieldName];
        }
       

        public function RegisterField(string $name, $type, string $length, $default, string $defaultValue, $attributes, bool $autoIncrement, bool $persistent) {
            if (isset($this->fieldsInfo[$name])) 
                return ($this->dbDatabase->dbEngine->SetError(1, "Table Row already exists."));                

            $default = strtoupper($default);

            $this->fieldsInfo[$name] = array(
                "name" => $name,
                "type" => $type,
                "length" => $length,
                "default" => $default,
                "defaultValue" => $defaultValue,
                "attributes" => $attributes,
                "autoIncrement" => $autoIncrement,
                "persistence" => $persistent
            );

            for ($rcnt = 0; $rcnt < count($this->rows); $rcnt ++)
                $this->rows[$rcnt][] = (($default == "CURRENT_TIMESTAMP" ? time() : ($default == "NULL" ? null : $defaultValue)));

            return true;
            
        }

        public function GetName() {
            return $this->name;
        }

        public function GetComments() {
            return $this->comments;
        }



    }

?>