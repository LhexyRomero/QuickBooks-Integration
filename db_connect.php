<?php
    $server = 'localhost';
    $username = 'root';
    $password = '';
    $dbname = 'quickbooks';

    //CONNECT
    $connect = new mysqli($server, $username, $password, $dbname);


    //TEST
    if(!$connect->connect_error) {
        //
    }
    else {
        //
    }

?>