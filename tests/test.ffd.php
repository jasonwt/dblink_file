<?php
    require_once(__DIR__ . "/classes/FFD.php");

    use ffd\FFE;
    
    $obj = new FFE();

    $query = "CREATE TABLE `testdatabase`.`test` ( 
        `id` BIGINT NOT NULL AUTO_INCREMENT , 
        `firstName` VARCHAR(50) NOT NULL , 
        `middleInitial` CHARASDFASDFASDFASDFASDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDFASDF(1) NOT NULL , 
        `lastName` VARCHAR(50) NOT NULL , 
        `birthYear` INT UNSIGNED NOT NULL , 
        PRIMARY KEY (`id`)
    ) ENGINE = InnoDB";

    $query = "CREATE DATABASE testdatabase     ;CREATE DATABASE testdatabase     ;";

    /*
    $query = "
        SELECT
            user,
            (
                SELECT
                    latest_timestamp
                FROM (
                    SELECT
                        user_id,
                        MAX(timestamp) AS latest_timestamp
                    FROM comments
                    GROUP BY user_id
                ) aggregate
                WHERE aggregate.user_id = users.id
            ) AS latest_timestamp
        FROM users
        ORDER BY username
        LIMIT @i, @r;
    ";
    */
    if (($results = $obj->Query($query)) === false) {
        print_r($obj);
        throw new \Exception($obj->Errno() . ": " . $obj->Error());
    }

    print_r($obj);

?>