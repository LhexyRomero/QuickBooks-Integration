<?php
    require_once "../db_connect.php";

    parse_str($_POST['formdata'],$params);

    $selected_expense =  $params["selected_expense"];
    $selected_project = $params["selected_project"];
    $selected_supplier = $params["selected_supplier"];
    $quickbooks_uids = array();

    $sql = "SELECT * FROM _relationship_db_purchase WHERE quickbooks_uid IS NULL";
    
    if($selected_expense>0){
        $sql .= " AND expense_type = $selected_expense";   
    }

    if($selected_project>0){
        $sql .= " AND project_id = $selected_project";   
    }

    if($selected_supplier>0){
        $sql .= " AND supplier_subcontractor_id = $selected_supplier";   
    }

    $query = $connect->query($sql);

    $output = "";
    while($row = mysqli_fetch_assoc($query)) {
        $output .= 
        "<tr>
            <td><input type='checkbox' class='form-control integrateCheck' onclick='countIntegrate()' value='".$row["id"]."'></td>
            <td>'".$row["project_id"]."'</td>
            <td>'". $row["project_id"] ."'</td>
        </tr>";
    }

    echo $output;
?>