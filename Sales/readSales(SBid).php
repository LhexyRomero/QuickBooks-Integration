<?php

    require_once('../db_connect.php');

    if(!empty($_POST)){
        $id = $_POST["id"];
        $records = array();
        $sql = "SELECT * FROM `_relationship_db_sales` JOIN _project_db 
                ON _relationship_db_sales.project_id = _project_db.project_id 
                WHERE id = $id";

        $query = $connect->query($sql);
            
        while($row = mysqli_fetch_array($query)) {
            array_push($records,$row);
        }
        echo json_encode($records, JSON_PRETTY_PRINT);
    }

?>