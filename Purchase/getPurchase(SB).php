<?php
    require_once "../db_connect.php";
    
    $selected_expense =  $_POST["selected_expense"];
    $selected_project = $_POST["selected_project"];
    $selected_supplier = $_POST["selected_supplier"];
    $quickbooks_uids = array();

    $sql = "SELECT * FROM `_relationship_db_purchase` JOIN _project_db ON project_id = _project_db.id JOIN _supplier_db ON supplier_subcontractor_id = _supplier_db.id JOIN _account_type_db ON account_type_id = _account_type_db.id WHERE quickbooks_uid is NULL";
    
    if($selected_expense>0){
        $sql .= " AND expense_type = $selected_expense";   
        echo $sql;
    }

    if($selected_project>0){
        $sql .= " AND project_id = $selected_project";   
    }

    if($selected_supplier>0){
        $sql .= " AND supplier_subcontractor_id = $selected_supplier";   
    }

    $query = $connect->query($sql);
    $rowcount=mysqli_num_rows($query); 

    $output = "";

    if($rowcount <=0){ 
        $output .= "<tr><td colspan = '9'><center>No data available in table</center></td></tr>";
        echo $output;
        return;
    }

    while($row = mysqli_fetch_assoc($query)) {
        $output .= "<tr>
        <td><input type='checkbox' class='form-control integrateCheck' onclick='countIntegrate()' value='".$row["id"]."'></td>
        <td>". $row["project_name"]. "</td>
        <td>". $row["supplier_name"]. "</td>
        <td>". $row["invoice_no"]. "</td>
        <td>". $row["invoice_date"]. "</td>
        <td>". $row["due_date"]. "</td>
        <td>". $row["invoice_attachment"]. "</td>
        <td>". $row["expense_type"]. "</td>
        <td>". $row["amount"]. "</td>
        </tr>";
    }
    echo $output;
?>