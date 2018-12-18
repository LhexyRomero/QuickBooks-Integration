<?php
    require_once "db_connect.php";

    $quickbooks_uid = array();
    $sql = "SELECT quickbooks_uid FROM _relationship_db_customers";

    $query = $connect->query($sql);

    while($row = mysqli_fetch_array($query)) {
        array_push($quickbooks_uid,$row);
    }

    echo json_encode($quickbooks_uid);
    
?>