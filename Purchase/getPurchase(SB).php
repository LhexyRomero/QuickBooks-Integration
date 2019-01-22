<?php
    require_once "../db_connect.php";
    
        $selected_expense =  $_POST["selected_expense"];
        $selected_project = $_POST["selected_project"];
        $selected_supplier = $_POST["selected_supplier"];
        $client_id = $_POST["client_id"];

        $sql = "SELECT * FROM `tbl_expensesheet` 
            JOIN _supplier_db ON tbl_expensesheet.supplier_id = _supplier_db.supplier_id 
            JOIN _account_type_db ON account_type_id = account_id 
            WHERE transferred_to_quickbooks='no'
            AND date_moved IS NULL
            AND client_id = $client_id AND quickbooks_uid is NULL";
        
        $sql_option = "SELECT * FROM `_account_type_db`";

        if($selected_expense>0){
            $sql .= " AND `tbl_expensesheet`.expense_type = $selected_expense";   
        }

        if($selected_project>0){
            $sql .= " AND `tbl_expensesheet`.project_id = $selected_project";   
        }

        if($selected_supplier>0){
            $sql .= " AND `tbl_expensesheet`.supplier_id = $selected_supplier"; 
        }

        $query = $connect->query($sql);
        $allTypes = $connect->query($sql_option);
        @$rowcount = mysqli_num_rows($query); 

        $type_options = array();

        $output = "";
        if($rowcount <=0){ 
            $output .= "<tr><td colspan = '9'><center>No data available in table</center></td></tr>";
            echo $output;
            return;
        }

        while($account = mysqli_fetch_assoc($allTypes)){
            $type_option = "<option value='".$account["account_id"]."'>".$account["type"]."</option>";
            array_push($type_options,$type_option);
        }
        
        while($row = mysqli_fetch_assoc($query)) {
            $output .= "<tr>
            <td><center><input type='checkbox' class='form-control integrateCheck' onclick='countIntegrate()' value='".$row["id"]."'></td></center>
            <td>". $row["project_name"]. "</td>
            <td>". $row["supplier_name"]. "</td>
            <td>". $row["invoice_number"]. "</td>
            <td>". $row["purchase_date"]. "</td>
            <td>". $row["due_date"]. "</td>
            <td>
                <select id='select_type' style='width: 200px;'>
                    ".selectType($row["account_type_id"],$type_options)."
                </select>
            </td>
            <td>". number_format($row["total_amount"],2) . "</td>
            </tr>";
        }

        echo $output;
      
        function selectType($id,$type_options){
            $options = "";
            for ($i=0; $i < sizeof($type_options); $i++) {
                if (strpos($type_options[$i], $id) !== false) {

                    $value = "value='".$id."'";
                    $replacedValue = $value . " selected";
                    $options .= str_replace($value,$replacedValue,$type_options[$i]);
                }
                else {
                    $options .= $type_options[$i];
                }
            }
            return $options;
        }
?>