<?php
    namespace dblink_file\classes;

    require_once(__DIR__ . "/AbstractParseQueryString.php");

    require_once(__DIR__ . "/DBEngine.php");

    class DebugginDBEngine extends DBEngine {        
        public function __destruct() {

        }

        public function SetCurrentError(int $errno, string $error) : bool {
            echo "SetCurrentError(): $errno, \"$error\"\n";

            return parent::SetCurrentError($errno, $error);
        } 

        public function CreateDatabase(string $name) : bool {
            echo "CreateDatabase(): " . print_r($name, true) . "\n";

            return parent::CreateDatabase($name);
        }

        protected function ProcessCreateDatabaseQuery(array &$parsedQueryArray) {
            echo "ProcessCreateDatabase(): " . print_r($parsedQueryArray, true) . "\n";
            
            return parent::ProcessCreateDatabaseQuery($parsedQueryArray);
        }

        protected function ProcessCreateQuery(array &$parsedQueryArray) {
            echo "ProcessCreateQuery(): " . print_r($parsedQueryArray, true) . "\n";
            
            return parent::ProcessCreateQuery($parsedQueryArray);
        }

        protected function ProcessShowQuery(array &$parsedQueryArray) {
            echo "ProcessShowQuery(): " . print_r($parsedQueryArray, true) . "\n";
            
            return parent::ProcessShowQuery($parsedQueryArray);
        }

        protected function ProcessQuery(array &$parsedQueryArray) {
            echo "ProcessQuery(): " . print_r($parsedQueryArray, true) . "\n";
            
            return parent::ProcessQuery($parsedQueryArray);
        }

        public function Query(string $query) {
            echo "Query(): $query\n";

            return parent::Query($query);            
        }
    }



?>