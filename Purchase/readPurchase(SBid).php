<?php
    require_once "../db_connect.php";

    if(!empty($_POST)) {
        $id = $_POST["id"];
        $records = array();
        $sql = "SELECT * FROM `_relationship_db_purchase` 
                JOIN _project_db ON _relationship_db_purchase.project_id = _project_db.project_id 
                JOIN _supplier_db ON supplier_subcontractor_id = supplier_id 
                JOIN _account_type_db ON account_type_id = account_id 
                WHERE _relationship_db_purchase.id = $id";

        $query = $connect->query($sql);
    
        while($row = mysqli_fetch_array($query)) {
            array_push($records,$row);
        }
        echo json_encode($records, JSON_PRETTY_PRINT);
    }
    
?>