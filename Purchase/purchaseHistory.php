<?php
    require_once "../db_connect.php";
    
    $id =  $_POST["id"];
    $sql = "UPDATE `tbl_expensesheet` SET date_transferred_to_quickbooks = CURRENT_TIMESTAMP, transferred_to_quickbooks='yes' WHERE `tbl_expensesheet`.`id` = $id";
    $query = $connect->query($sql);
    
    if($query) {
        echo "Success History!";
        return;
    }
?>