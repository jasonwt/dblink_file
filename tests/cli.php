<?php
    //require_once(__DIR__ . "/classes/DebuggingCLI.php");
    //require_once(__DIR__ . "/classes/CLI.php");
    require_once(__DIR__ . "/classes/FFDCLI.php");

    //$cli = new DebuggingCLI();
    $cli = new CLI();
    $cli->Execute();

//    print_r($cli);
    

    echo "Shutting Down...\n";

?>