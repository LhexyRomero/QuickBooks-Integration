<?php
    require_once "../db_connect.php";
    
    $id =  $_POST["id"];
    $sql = "UPDATE `_relationship_db_sales` SET date_moved = CURRENT_TIMESTAMP WHERE `_relationship_db_sales`.`id` = $id";
    $query = $connect->query($sql);
    
    if($query) {
        echo "Success";
        return;
    }
?>