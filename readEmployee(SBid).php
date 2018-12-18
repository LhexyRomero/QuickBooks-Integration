<?php
    require_once "db_connect.php";

    if(!empty($_POST)) {
        $id = $_POST["id"];
        $records = array();
        $sql = "SELECT * FROM _relationship_db_employee WHERE id=$id";
    
        $query = $connect->query($sql);
    
        while($row = mysqli_fetch_array($query)) {
            array_push($records,$row);
        }
    
        echo json_encode($records);
    }
    
?>