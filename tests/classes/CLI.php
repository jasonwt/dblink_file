<?php
    require_once(__DIR__ . "/../../src/classes/DBEngine.php");

    use dblink_file\classes\AbstractParseQueryString;
    use dblink_file\classes\DBEngine;

    class CLI {
        protected $dbEngine;
        protected $selectedDatabase = array();
//
        public function __construct() {
            $loadDatabasesArray = array();

            for ($acnt = 1; $acnt < count($_SERVER['argv']); $acnt ++) {
                $parameter = $_SERVER['argv'][$acnt];
            
                if (file_exists($parameter))
                    $loadDatabasesArray[] = $parameter;
                else
                    echo "Unknown flag: $parameter\n";
            }

            $this->dbEngine = new DBEngine($loadDatabasesArray);
        }
//
        public function __destruct() {
            print_r($this);
        }
//
        protected function SetCurrentError(int $errno, string $error) : bool {
            return $errno == 0;
        }
//
        protected function Query(string $query) {
            $tQuery = $query;

            if (($parsedQueryArray = AbstractParseQueryString::ParseQueryString($tQuery)) == null)
                return false;

            switch (strtoupper(($line = array_shift($parsedQueryArray)))) {
                case "USE": {
                    if ($this->dbEngine->Query($query)) {
                        $this->selectedDatabase[] = $this->dbEngine->GetSelectedDatabase();
                        return true;
                    }

                    return false;
                }

                default: {
                    return $this->dbEngine->Query($query);
                }
            }            
        }
//
        public function Execute() {
            $sql = "";

            while (true) {
                if ($sql == "")
                    echo "DB [" . (count($this->selectedDatabase) == 0 ? "" : $this->selectedDatabase[count($this->selectedDatabase)-1]) . "]> ";
                else
                    echo "\t-> ";
        
                $sql .= ltrim(readline());
        
                if (substr($sql, -1) == ";") {
                    echo "\n" . $sql . "\n";

                    if (strtoupper($sql) == "EXIT;") {
                        if (count($this->selectedDatabase) > 0)
                            array_pop($this->selectedDatabase);
                        else
                            break;
                    } else if (strtoupper($sql) == "QUIT;") {
                        break;
                    } else {                                
                        if (($results = $this->Query($sql)) === false) {
                            echo "Error " . $this->dbEngine->Errno() . ": " . $this->dbEngine->Error() . "\n";
                            
                        } else if ($results === true) {
                            echo "OK\n";
                        } else {
                            print_r($results);
                        }

                    }
                        
                    echo "\n";
        
                    $sql = "";
                }
            }
        }
    }

?>