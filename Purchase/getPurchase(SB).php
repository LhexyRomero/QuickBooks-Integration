<?php
    require_once "../db_connect.php";
    
    $selected_expense =  $_POST["selected_expense"];
    $selected_project = $_POST["selected_project"];
    $selected_supplier = $_POST["selected_supplier"];
    $quickbooks_uids = array();

    $sql = "SELECT * FROM `_relationship_db_purchase` 
            JOIN _project_db ON _relationship_db_purchase.project_id = _project_db.project_id 
            JOIN _supplier_db ON supplier_subcontractor_id = supplier_id 
            JOIN _account_type_db ON account_type_id = account_id 
            WHERE quickbooks_uid is NULL";
    
    $option = "SELECT * FROM `_account_type_db`";

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
    $result = $connect->query($option);
    $rowcount=mysqli_num_rows($query); 

    $output = "";
    $options = "";

    if($rowcount <=0){ 
        $output .= "<tr><td colspan = '9'><center>No data available in table</center></td></tr>";
        echo $output;
        return;
    }

    while($row_option = mysqli_fetch_array($result)){
        $options .= "<option value = ". $row_option["account_id"] ." > ".$row_option["type"]."</option>";
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
        <td>
            <select id='select_type' name='selected_expense' style='width: 200px;'>
                <option value=". $row_option["account_type_id"] .">". $row["type"]. "</option>
                ". $options ."
            </select>
        </td>
        <td>". $row["amount"]. "</td>
        </tr>";
    }
    
    echo $output;
?>