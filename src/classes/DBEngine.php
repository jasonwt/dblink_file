<?php
    namespace dblink_file\classes;

    require_once(__DIR__ . "/AbstractDBEngine.php");

    class DBEngine extends AbstractDBEngine {
    }


    /*
    $query = " SELECT name FR;OM \"table name\"(WHERE(id=id2 AND name=\"Jas(on T\\\\\\\"ho)mpson \" OR name=' Rog(er Mil)ler')LIMIT 1,2)ORDER BY id,id2;";
    $query = ' SELECT                                                        name FR;OM \'table name\'(WHERE(id=id2 AND name=\'Jas(on T\\\\\\\'ho)mpson \' OR name=" Rog(er Mil)ler")LIMIT 1,2)ORDER BY id,id2;';


    $query = '
    SELECT Name,
        SUM(Value) AS "SUM(VALUE)",
        SUM(Value) / totals.total AS "% of Total"
    FROM   table1,
        (
            SELECT Name,
                SUM(Value) AS total
            FROM   table1
            GROUP BY Name
        ) AS totals
    WHERE  table1.Name = totals.Name
    AND    Year BETWEEN 2000 AND 2001
    GROUP BY Name;
    ';

    $query = "
            SELECT
                id, username, email
            FROM
                accounts, history
            WHERE
                history.accountid= accounts.id AND
                accounts.id > 10;
    ";

    $query = "SELECT * FROM users";


    $query = "
        CREATE TABLE `ynhawebforms`.`test` ( 
            `id` BIGINT NOT NULL AUTO_INCREMENT , 
            `firstName` VARCHAR(50) NOT NULL , 
            `middleInitial` CHAR(1) NOT NULL , 
            `lastName` VARCHAR(50) NOT NULL , 
            `birthYear` INT UNSIGNED NOT NULL , 
            PRIMARY KEY (`id`)
        ) ENGINE = InnoDB;
    ";




    
    $de = new DBEngine("test.db");

    if (is_null(($results = $de->Query($query))))
        throw new \Exception("invalid query");

    print_r($results);

    die();
*/
?>