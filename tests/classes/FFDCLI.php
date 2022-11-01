<?php
    require_once(__DIR__ . "/FFD.php");

    use ffd\FFE;
    use ffd\FFD;
    use ffd\FFT;

    class CLI {
        protected $dbEngine;
        protected $selectedDatabase = array();
//
        public function __construct() {
            $this->dbEngine = new FFE();

            $loadDatabasesArray = array();

            for ($acnt = 1; $acnt < count($_SERVER['argv']); $acnt ++) {
                $parameter = $_SERVER['argv'][$acnt];
            
                if (file_exists($parameter)) {
                    if (($this->dbEngine->LoadDatabase($parameter)) == false)
                        echo "Error " . $this->dbEngine->Errno() . ": " . $this->dbEngine->Error();     
                        
                    
//                    print_r($this);
                } else {
                    echo "Unknown flag: $parameter\n";
                }
            }
        }
//
        public function __destruct() {
            //print_r($this);
        }
//
        protected function SetCurrentError(int $errno, string $error) : bool {
            return $errno == 0;
        }
//
        protected function Query(string $query) {
            $returnValue = $this->dbEngine->Query($query);

            if ($returnValue === false)
                return false;

            $splitArray = preg_split('/\s+/', $query);

            print_r($splitArray);

            if (strtoupper($splitArray[0]) == "USE;") {
                array_pop($this->selectedDatabase);
            } else if (strtoupper($splitArray[0]) == "USE") {
                if (strtoupper($splitArray[1] == ";")) {
                    array_pop($this->selectedDatabase);
                } else {
                    $this->selectedDatabase[] = substr($splitArray[1], 0, strlen($splitArray[1])-1);
                }
            }

            return $returnValue;

            die();
            return $this->dbEngine->Query($query);
/*            
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
*/            
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
                    //echo "\n" . $sql . "\n";
                    echo "\n";

                    if (strtoupper($sql) == "EXIT;") {
                        if (count($this->selectedDatabase) == 0)
                            break;
                            
                        $this->Query("USE;");                        
                    } else if (strtoupper($sql) == "QUIT;") {
                        break;
                    } else {                                
                        if (($results = $this->Query($sql)) === false) {
                            echo "Error " . $this->dbEngine->Errno() . ": " . $this->dbEngine->Error();
                            
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