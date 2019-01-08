<?php
    require_once "../db_connect.php";
    
    $selected_invoice =  $_POST["selected_invoice"];
    $quickbooks_uids = array();
    $sql = "SELECT quickbooks_uid FROM _relationship_db_purchase";
    $sql_sales = "SELECT * FROM `_relationship_db_sales` 
                JOIN _project_db ON _relationship_db_sales.project_id = _project_db.project_id
                WHERE account_type = $selected_invoice";

    $query = $connect->query($sql);
    $sales_query = $connect->query($sql_sales);

    while($row = mysqli_fetch_array($query)) {
        array_push($quickbooks_uids,$row["quickbooks_uid"]);
    }

    $customer_options = "";
    $allCustomers = $dataService->Query('SELECT * FROM Customer');
    foreach($allCustomers as $customer) {
        if (in_array($customer->Id, $quickbooks_uids, TRUE)) 
        { 
        } 
        else
        { 
            $customer_options .= "<option value ='".$customer->Id."'>".$customer->DisplayName."</option>";
        } 
    }

    $output = "";
    while($row = mysqli_fetch_assoc($query)) {
        $output .= "<tr>
            <td><center><input type='checkbox' class='form-control integrateCheck' onclick='countIntegrate()' value='".$row["id"]."'></td></center>
            <td>". $row["project_name"]. "</td>
            <td>". $customer_option. "</td>
            <td>". $row["invoice_no"]. "</td>
            <td>". $row["invoice_date"]. "</td>
            <td>". $row["due_date"]. "</td>
            <td></td>
            <td>". number_format($row["total_amount"],2). "</td>
        </tr>";
    }

    echo $output;
?>