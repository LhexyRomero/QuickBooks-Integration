<?php
    require_once "db_connect.php";

    $records = array();
    $sql = "SELECT * FROM _relationship_db_employee WHERE quickbooks_uid IS NULL";

    $query = $connect->query($sql);

    while($row = mysqli_fetch_array($query)) {
        array_push($records,$row);
    }

    echo json_encode($records, JSON_PRETTY_PRINT);
    
?>