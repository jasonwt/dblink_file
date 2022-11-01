<?php
    require_once(__DIR__ . "/../../src/classes/DebuggingDBEngine.php");

    require_once(__DIR__ . "/CLI.php");

    use dblink_file\classes\AbstractParseQueryString;
    use dblink_file\classes\DebugginDBEngine;

    class DebuggingCLI extends CLI {
        public function __construct() {
            $databasesArray = array_slice($_SERVER['argv'], 1);

            $this->dbEngine = new DebugginDBEngine($databasesArray);
        }

        public function __destruct() {

        }

        protected function SetCurrentError(int $errno, string $error) : bool {
            echo "SetCurrentError(): $errno, \"$error\"\n";

            return parent::SetCurrentError($errno, $error);
        }

        protected function Query(string $query) {
            echo "Query(): " . print_r($query, true) . "\n";

            return parent::Query($query);            
        }

        public function Execute() {
            echo "Execute():\n";

            return parent::Execute();
        }
    }

?>