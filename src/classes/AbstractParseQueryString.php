<?php
    namespace dblink_file\classes;

    

    

    abstract class AbstractParseQueryString {
        static public function ParseQueryString(&$query, $opening = null) {
            $returnValue = array();
    
            $strPos = 0;
    
            while ($strPos < strlen($query)) {
                if ($opening == "'" || $opening == '"') {
                    $nop = 0;
    
                    do {
                        $nop = strpos($query, $opening, $nop+1);
    
                        $ncnt = $nop - 1;
    
                        for ($ncnt = $nop - 1; $ncnt >= 0; $ncnt --) {
                            if ($query[$ncnt] !=  "\\")
                                break;
                        }                        
    
                        if ((($nop - ($ncnt+1)) % 2) == 0)
                            break;

                    } while ($nop < strlen($query));
    
                    $returnValue = substr($query, 0, $nop);
                    $query = substr($query, $nop+1);
    
                    return $opening . $returnValue . $opening;                    
                } else {
                    $queryKey = $query[$strPos];
    
                    if ($queryKey == '"' || $queryKey == "'" ) {
                        $trimValue = substr($query, 0, $strPos);
    
                        if ($trimValue != "")
                            $returnValue[] = $trimValue;
    
                        $query = substr($query, $strPos+1);

                        if (is_null(($parseQueryResults = AbstractParseQueryString::ParseQueryString($query, $queryKey))))
                            return null;
    
                        $returnValue[] = $parseQueryResults;
    
                        $strPos = 0;
    
                        continue;
                    } else if (trim($queryKey) == "") {
    
                        $trimValue = trim(substr($query, 0, $strPos));
    
                        if ($trimValue != "")
                            $returnValue[] = $trimValue;
                        
                        $query = substr($query, $strPos+1);
    
                        $strPos = 0;
    
                        continue;
                    } else if ($queryKey == "(") {
    
                        $trimValue = substr($query, 0, $strPos);
    
                        if ($trimValue != "")
                            $returnValue[] = $trimValue;
    
                        $query = substr($query, $strPos+1);

                        if (is_null(($parseQueryResults = AbstractParseQueryString::ParseQueryString($query, '('))))
                            return null;
    
                        $returnValue[] = $parseQueryResults;

                        $strPos = 0;
    
                        continue;
                    } else if ($queryKey == ")") {
                        if ($strPos > 0) {
                            $trimValue = substr($query, 0, $strPos);
    
                            $returnValue[] = $trimValue;
                        }
    
                        $query = substr($query, $strPos+1);
                        
                        return $returnValue;
                    } else if ($queryKey == "," || $queryKey == "=" || $queryKey == ";" || $queryKey == ".") {
                        $trimValue = trim(substr($query, 0, $strPos));
    
                        if ($trimValue != "")
                            $returnValue[] = $trimValue;

                        $returnValue[] = $queryKey;
    
                        $query = substr($query, $strPos+1);
    
                        $strPos = 0;
    
                        continue;
                    } else {
                        //echo "{" . $query[$strPos] . "}";                    
                    }
                }
    
                $strPos ++;
            }
    
            if ($query != "")
                $returnValue[] = $query;
            
            if (!is_null($opening))
                return false;

            return $returnValue;    
        }
    }

?>