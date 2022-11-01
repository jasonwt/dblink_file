<?php
//
//
//
    namespace ffd;
//
//
//
    class FFT {
        protected FFD $flatFileDatabase;

        public function __construct(FFD $flatFileDatabase) {
            $this->flatFileDatabase = $flatFileDatabase;
        }
        public function __destruct() {}
    }
//    
//
//
    class FFD {
        protected FFE $flatFileEngine;

        protected array $tables = array();

        public function __construct(FFE $flatFileEngine) {
            $this->flatFileEngine = $flatFileEngine;
        }

        public function __destruct() {}

        public function Save($filePath) : bool {
            if ((file_put_contents($filePath, serialize($this->tables))) === false)
                return false;

            return true;
        }

        public function Load($filePath) : bool{
            if (($fileData = file_get_contents($filePath)) === false)
                return false;

            $this->tables = unserialize($fileData);

            return true;
        }
    }
//
//
//
    class FFE {
        protected int $errno = 0;
        protected string $error = "";

        protected $selectedDatabase = "";

        protected array $databases = array();

        protected array $supportedOperationsArray = [
            "SHOW" => [
                "DATABASES" => "ParseShowDatabases", 
                "TABLES" => "ParseShowTables"
            ], "CREATE" => [
                "DATABASE" => "ParseCreateDatabase",
                "TABLE" => "ParseCreateTable"
            ], 
            "DESCRIBE" => "ParseDescribeTable", 
            "USE" => "ParseUseDatabase"
        ];
//
        public function __construct() {}

        public function __destruct() {
            foreach ($this->databases as $k => $v)
                $this->SaveDatabase($k);
        }
//
        public static function GetQuery(array $parsedQueryArray) {
            $returnValue = "";

            foreach ($parsedQueryArray as $k => $v) {
                if (is_array($v))
                    $returnValue .= self::GetQuery($v) . " ";
                else
                    $returnValue .= $v . " ";
            }

            return $returnValue;
        }
//
        public static function TrimEdges($str, array $trimCharArray = array("'", '"', '`')) {
            if (!is_string($str))
                return $str;

            if (strlen($str) <= 2)
                return $str;

            $head = $tail = 0;

            if (in_array($str[0], $trimCharArray))            
                $head = 1;

            if (in_array($str[strlen($str)-1], $trimCharArray))
                $tail = 1;

            return substr($str, $head, strlen($str) - $head - $tail);
        }        
//
        public function Errno() {
            return $this->errno;
        }        
//
        public function Error() : string {
            return $this->error;
        }        
//
        protected function GetDebugString() {
            $debugString = "";

            for ($cnt = 0; $cnt < count(func_get_args()); $cnt ++)
                $debugString .= print_r(func_get_args()[$cnt], true);

            $debugString .= "\n\nStack trace:\n";

            for ($cnt = 2; $cnt < count(debug_backtrace()); $cnt ++)
                $debugString .= "#" . ($cnt-2) . " " . debug_backtrace()[$cnt]["file"] . "(" . debug_backtrace()[$cnt]["line"] . "): " . debug_backtrace()[$cnt]["function"] . "()\n";

            return $debugString . "\n";
        }

//
        protected function SetError(int $errno, string $error) : bool {
            if ($errno != 0) {
                
                $this->errno = $errno;
                $this->error = $error . $this->GetDebugString();

                return false;
            }

            return true;
        }
//
        public function CreateDatabase(string $databaseName, string $databaseFilePath = "") : bool {
            if (isset($this->databases[$databaseName]))
                return $this->SetError(1, "Can't create database '$databaseName'; database exist");

            if ($databaseFilePath == "")
                $databaseFilePath = $databaseName . ".ffd";

            if (is_dir($databaseFilePath))
                return $this->SetError(1, "Can't set database file path to '$databaseFilePath'; is a directory");

            $this->databases[$databaseName] = [
                "filePath" => $databaseFilePath,
                "object" => new FFD($this)
            ];

            return true;
        }
//
        public function SaveDatabase(string $databaseName) : bool {
            if (!isset($this->databases[$databaseName]))
                return $this->SetError(1, "Can't save database '$databaseName'; Unknown database");

            if (!$this->databases[$databaseName]["object"]->Save($this->databases[$databaseName]["filePath"]))
                return $this->SetError(1, "Can't save database '$databaseName'; Save failed");

            return true;
        }
//
        public function LoadDatabase(string $databaseFilePath) {
            $databaseName = substr($databaseFilePath, (($rpos = strrpos($databaseFilePath, '/')) == false ? 0 : $rpos + 1));
            $databaseName = substr($databaseName, 0, (($lpos = strpos($databaseName, ".")) === false ? strlen($databaseName) : $lpos));            

            if (isset($this->databases[$databaseName]))
                return $this->SetError(1, "Can't load database '$databaseName'; Already loaded");

            $newDatabase = new FFD($this);

            if (($newDatabase->Load($databaseFilePath)) == false)
                return $this->SetError(1, "Can't load database '$databaseName'; Load failed");

            $this->databases[$databaseName] = [
                "filePath" => $databaseFilePath,
                "object" => $newDatabase
            ];
            
            return true;
        }
//
//
//
        protected function ProcessParseQueryStringValue(array &$arrayReference, $value) {
            if (!is_array($value) && false)
                $arrayReference[] = "!" . $value . "!";
            else
                $arrayReference[] = $value;
        }
//        
        public function ParseQueryString(&$query, $opening = null) {
            $returnValue = array();
    
            $strPos = 0;
    
            while ($strPos < strlen($query)) {
                if ($opening == "'" || $opening == '"') {
                    $nop = 0;
    
                    do {
                        if (($nop = strpos($query, $opening, $nop+1)) === false)
                            return null;
    
                        $ncnt = $nop - 1;
    
                        while ($ncnt >= 0 && $query[$ncnt] == "\\")
                            $ncnt --;
                            
                        if ((($nop - ($ncnt+1)) % 2) == 0)
                            break;

                    } while ($nop < strlen($query));
    
                    $returnValue = substr($query, 0, $nop);
                    $query = substr($query, $nop+1);
    
                    return $opening . $returnValue . $opening;                    
                } else {
                    $queryKey = trim($query[$strPos]);
                    
                    if ($queryKey == "" || $queryKey == '"' || $queryKey == "'" || $queryKey == "(" || $queryKey == "," || $queryKey == "=" || $queryKey == ";" || $queryKey == "." || $queryKey == ")") {
                        $trimValue = trim(substr($query, 0, $strPos));
    
                        if ($trimValue != "")
                            $this->ProcessParseQueryStringValue($returnValue, $trimValue);
                        
                        $query = substr($query, $strPos+1);

                        if ($queryKey == '"' || $queryKey == "'" || $queryKey == "(") {
                            if (($parseQueryResults = $this->ParseQueryString($query, $queryKey)) === false)
                                return false;
    
                            $this->ProcessParseQueryStringValue($returnValue, $parseQueryResults);
                        } else if ($queryKey == "," || $queryKey == "=" || $queryKey == ";" || $queryKey == ".") {
                            $this->ProcessParseQueryStringValue($returnValue, $queryKey);
                        } else if ($queryKey == ")") {
                            return $returnValue;
                        }

                        $strPos = 0;    
                        continue;                                            
                    }
                }
    
                $strPos ++;
            }
    
            if ($query != "")
                $this->ProcessParseQueryStringValue($returnValue, $query);

            return ((is_null($opening)) ? $returnValue : false);            
        }
//
        protected function ShiftParsedQueryResults(array &$parsedQueryResults, int $num = 1) {
            if (count($parsedQueryResults) < $num)
                return $this->SetError(1, "You have an error in your SQL syntax near: " . self::GetQuery($parsedQueryResults));

            $shiftedValues = [];

            for ($cnt = 0; $cnt < $num; $cnt ++)
                $shiftedValues[] = array_shift($parsedQueryResults);

            return $shiftedValues;
        }
//
        protected function ParseUseDatabase(array &$parsedQueryResults) {
            if (($shiftedValues = $this->ShiftParsedQueryResults($parsedQueryResults, 1)) === false)
                return false;            

            if ($shiftedValues[0] == ";") {
                $this->selectedDatabase = "";

            } else {
                $databaseName = $shiftedValues[0];

                if (($shiftedValues = $this->ShiftParsedQueryResults($parsedQueryResults, 1)) === false)
                    return false;

                if ($shiftedValues[0] != ";")
                    return $this->SetError(1, "You have an error in your SQL syntax near: " . self::GetQuery(array_merge(array($databaseName, $shiftedValues[0]), $parsedQueryResults)));


                if (!isset($this->databases[$databaseName]))
                    return $this->SetError(1, "Can't use database '" . $databaseName . "'; Database unknown");

                $this->selectedDatabase = $databaseName;
            }

            return true;
        }
//
        protected function ParseShowDatabases(array &$parsedQueryResults) {
            if (($shiftedValues = $this->ShiftParsedQueryResults($parsedQueryResults, 1)) === false)
                return false;            

            if ($shiftedValues[0] != ";")
                return $this->SetError(1, "You have an error in your SQL syntax near: " . self::GetQuery(array_merge(array($shiftedValues[0]), $parsedQueryResults)));

            return array_keys($this->databases);
        }        
//
        protected function ParseCreateDatabase(array &$parsedQueryResults) {
            if (($shiftedValues = $this->ShiftParsedQueryResults($parsedQueryResults, 2)) === false)
                return false;            

            if ($shiftedValues[1] != ";")
                return $this->SetError(1, "You have an error in your SQL syntax near: " . self::GetQuery(array_merge(array($shiftedValues[1]), $parsedQueryResults)));

            return $this->CreateDatabase($shiftedValues[0]);            
        }
//
        public function ProcessParsedQueryResults(array &$parsedQueryResults, array $ptr) {
            if (($shiftedValues = $this->ShiftParsedQueryResults($parsedQueryResults, 1)) === false)
                return false;

            $operationKey = strtoupper($shiftedValues[0]);

            if (!isset($ptr[$operationKey]))
                return $this->SetError(1, "You have an error in your SQL syntax near: " . self::GetQuery(array_merge($shiftedValues, $parsedQueryResults)));

            if (is_array($ptr[$operationKey])) {
                $ptr = $ptr[$operationKey];

                return $this->ProcessParsedQueryResults($parsedQueryResults, $ptr);
            }

            $function = $ptr[$operationKey];

            return $this->$function($parsedQueryResults);
        }
//
        public function Query(string $query) {
            $this->errno = 0;
            $this->error = "";

            if (($parsedQueryResults = $this->ParseQueryString($query)) === false)
                return $this->SetError(1, "You have an error in your SQL syntax near: $query");

            $results = $this->ProcessParsedQueryResults($parsedQueryResults, $this->supportedOperationsArray);

            $query = $this->GetQuery($parsedQueryResults);
                        
            return $results;            
        }
    }
?>