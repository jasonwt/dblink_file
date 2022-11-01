<?php
    
    $query = "CREATE TABLE `testdatabase`.`test` ( 
        `id` BIGINT NOT NULL AUTO_INCREMENT , 
        `firstName` VARCHAR(50) NOT NULL , 
        `middleInitial` CHARASDFASDFASDFASDFASDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDFASDF(1) NOT NULL , 
        `lastName` VARCHAR(50) NOT NULL , 
        `birthYear` INT UNSIGNED NOT NULL , 
        PRIMARY KEY (`id`)
    ) ENGINE = InnoDB;
    ";

    $debugging = false;

    function GetQuery(array $parsedQueryArray) {
        $returnValue = "";

        foreach ($parsedQueryArray as $k => $v) {
            if (is_array($v))
                $returnValue .= GetQuery($v) . " ";
            else
                $returnValue .= $v . " ";
        }

        return $returnValue;
    }

    function ParseQuery(&$query, $opening = null) {
        global $debugging;

        $returnValue = array();

        $strPos = 0;

        while ($strPos < strlen($query)) {
            if ($debugging) {
                echo "\n\n************************** debugging strPos: $strPos, query:$query\n\n";
                $debugging = false;
            }

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
                        
                    usleep(1000);
                } while ($nop < strlen($query));

                $returnValue = "!" . substr($query, 0, $nop) . "!";;
                $query = substr($query, $nop+1);

                return $opening . $returnValue . $opening;                    
            } else {
                $queryKey = $query[$strPos];

                if ($queryKey == '"' || $queryKey == "'" ) {
                    $trimValue = substr($query, 0, $strPos);

                    if ($trimValue != "")
                        $returnValue[]["A"] = "!" . $trimValue . "!";;

                    $query = substr($query, $strPos+1);

                    $returnValue[]["A"] = ParseQuery($query, $queryKey);

                    $strPos = 0;

                    continue;
                } else if (trim($query[$strPos]) == "") {

                    $trimValue = trim(substr($query, 0, $strPos));

                    if ($trimValue != "")
                        $returnValue[]["B"] = "!" . $trimValue . "!";;
                    
                    $query = substr($query, $strPos+1);

                    $strPos = 0;

                    continue;
                } else if ($query[$strPos] == "(") {

                    $trimValue = substr($query, 0, $strPos);

                    if ($trimValue != "")
                        $returnValue[]["C"] = "!" . $trimValue . "!";;

                    $query = substr($query, $strPos+1);

                    $returnValue[] = ParseQuery($query, '(');

                    $strPos = 0;

//                    echo print_r(GetQuery($returnValue[count($returnValue)-1]), true) . "\n";
  //                  echo $query . "\n";
                    
                    continue;
                } else if ($query[$strPos] == ")") {
                    echo "\n\nstrPos: $strPos, query:" . $query . "\n\n";
                    
                    if ($strPos > 0) {
                        $trimValue = substr($query, 0, $strPos);

                        echo "trimValue: $trimValue\n\n";

                        $returnValue[]["D"] = "!" . $trimValue . "!";;
                    }

                    if (substr($query, 0, 11) == "1) NOT NULL")
                        $debugging = true;

                    $query = substr($query, $strPos+1);

                    
                    
                    return $returnValue;
                } else if ($queryKey == "," || $queryKey == "=" || $queryKey == ";" || $queryKey == ".") {
                    $trimValue = trim(substr($query, 0, $strPos));


                    if ($trimValue != "")
                        $returnValue[]["E"] = "!" . $trimValue . "!";;

                    $returnValue[]["E2"] = "!" . $queryKey . "!";


                    $query = substr($query, $strPos+1);

                    $strPos = 0;

                    continue;
                } else {
                    echo "{" . $query[$strPos] . "}";                    
                }
            }

            $strPos ++;
        }

        if ($query != "")
            $returnValue[]["Z"] = $query;
        
        if (!is_null($opening))
            throw new \Exception("query structure error[$opening]: " . $query);

//          $query = substr($query, $strPos);
//    }



        //echo "******************************************************************************************* RETURN*\n\n";
        return $returnValue;

    }

    $queryStructureArray = ParseQuery($query);

    print_r($queryStructureArray);

    echo "\n" . GetQuery($queryStructureArray) . "\n\n";
//    if (is_null($queryStructureArray))
//        throw new \Exception("parse query failed.");

    
    die();

    print_r(preg_split('~(?:\'[^\']*\'|"[^"]*")(*SKIP)(*F)|\h+~', $query));

    print_r(preg_split('/(\[[^]]+\])/', $query, -1, PREG_SPLIT_DELIM_CAPTURE));

    print_r(preg_match_all("/\((?:[^()]|(?R))+\)|'[^']*'|[^(),\s]+/", $query, $matches));
    print_r($matches);


    $regex = '([^\)]+)\(([^\)]+)\)';
    preg_match('/(\d+)(?:\s*)([\+\-\*\/])(?:\s*)(\d+)/', $query, $matches);
    print_r($matches);

    $regEx = '/\s(?=([^"]*"[^"]*")*[^"]*$)/';
    print_r(preg_split($regEx, $query));
    print_r($matches);

    die();
    

    $returnValue = array();

    //parse($query, $returnValue);

    function aparse($str, array &$returnValue) {
        $inQuote = false;
        $value = array();

        $strPos = 0;

        while (true) {
            $strPos = 0;

            while ($strPos < strlen($str) && $str[$strPos] != "(") {                
                $strPos ++;
            }

            echo $strPos;

            break;

        }
        

        return $value;

    }

    print_r($returnValue);
?>


