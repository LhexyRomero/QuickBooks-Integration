<?php
    require_once "../db_connect.php";

    if(!empty($_POST)) {
        $invoice_number = $_POST['invoice_number'];
        $invoice_date = $_POST['invoice_date'];
        $due_date = $_POST['due_date'];
        $amount = $_POST['amount'];
        $quickbooks_uid = $_POST['quickbooks_uid'];

        $sql = "INSERT INTO `_relationship_db_purchase` (`project_id`, `supplier_subcontractor_id`, `account_type_id`,`invoice_no`, `invoice_date`, `due_date`, `amount`, `quickbooks_uid` ,`expense_type`,`date_moved`) VALUES (1,2,98,'$invoice_number', '$invoice_date', '$due_date', '$amount', '$quickbooks_uid', 1, CURRENT_TIMESTAMP)";

        echo $sql;
        if($connect->query($sql)) {
            echo "BOBO";
        }
        else {
            echo mysqli_error($sql);
        }
    }
?>