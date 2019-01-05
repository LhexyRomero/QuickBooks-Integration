<?php
    require_once "../db_connect.php";

    if(!empty($_POST)) {
        $supplier_name = $_POST['supplier_name'];
        $supplier_address = $_POST['supplier_address'];
        $representative_email =   $_POST['representative_email'];
        $representative_phone =   $_POST['representative_phone'];
        $representative_mobile =  $_POST['representative_mobile'];
        $representative_fax =     $_POST['representative_fax'];
        $quickbooks_uid =   $_POST['quickbooks_uid'];
        $representative_name = $_POST['representative_name'];
        $representative_lname = $_POST['representative_lname'];
        $bank_account_number = $_POST["bank_account_number"];

        session_start();
        $client_id = $_SESSION["client_id"];

        $sql = "INSERT INTO `_relationship_db_suppliers` (`id`, `client_id`, `supplier_name`, `supplier_abn`, `supplier_address`, `representative_name`, `representative_lname`, `representative_jobtitle`, `representative_phone`, `representative_mobile`, `representative_fax`, `representative_email`, `bank_account_number`, `bank_account_name`, `bsb_number`, `date_modified`, `modified_by`, `modified_in`, `xero_uid`, `quickbooks_uid`, `myob_uid`, `status`, `xero_status`, `source`) VALUES (NULL, '$client_id', '$supplier_name', NULL, '$supplier_address', '$representative_name', '$representative_lname', NULL, '$representative_phone', '$representative_mobile', '$representative_fax', '$representative_email', '$bank_account_number', NULL, NULL, CURRENT_TIMESTAMP, '0', NULL, NULL, $quickbooks_uid, NULL, NULL, NULL, NULL);";

        if($connect->query($sql)) {
            //
        }
        else {
            //
        }
    }
?>