<?php
    require_once "../db_connect.php";
    
    echo "leki";
    $id = $_POST["id"];
    $customer_id = $_POST["customer_id"];

    $sql = "UPDATE `_relationship_db_sales` SET `customer_id`= $customer_id WHERE `id`=$id";
    
    if($query = $connect->query($sql)){
        echo $sql,"SUCCESS";
        return;
    };
?>