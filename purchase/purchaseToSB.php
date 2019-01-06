<?php
    require_once "../db_connect.php";

    if(!empty($_POST)) {
        $invoice_number = $_POST['invoice_number'];
        $invoice_date = $_POST['invoice_date'];
        $due_date = $_POST['due_date'];
        $amount = $_POST['amount'];
        $quickbooks_uid =   $_POST['quickbooks_uid'];

        $sql = "INSERT INTO `_relationship_db_purchase` (`invoice_no`, `invoice_date`, `due_date`, `amount`, `quickbooks_uid`, `date_moved`) VALUES ('$invoice_no', '$invoice_date', '$due_date', '$amount', '$quickbooks_uid', CURRENT_TIMESTAMP);";

        if($connect->query($sql)) {
            //
        }
        else {
            //
        }
    }
?>