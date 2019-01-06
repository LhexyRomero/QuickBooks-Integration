<?php
    require_once "../db_connect.php";
    
    $id =  $_POST["id"];
    $sql = "UPDATE `_relationship_db_purchase` SET `expense_type` = 2 WHERE `_relationship_db_purchase`.`id` = $id";
    $query = $connect->query($sql);
    
    if($query) {
        echo "Success History!";
        return;
    }
?>