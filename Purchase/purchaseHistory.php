<?php
    require_once "../db_connect.php";
    
    $id =  $_POST["id"];
    $sql = "UPDATE `tbl_expensesheet` SET date_moved = CURRENT_TIMESTAMP, transferred_to_quickbooks='no' WHERE `tbl_expensesheet`.`id` = $id";
    $query = $connect->query($sql);
    
    if($query) {
        echo "Success";
        return;
    }
?>