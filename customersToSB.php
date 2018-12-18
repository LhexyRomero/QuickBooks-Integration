<?php
    require_once "db_connect.php";

    if(!empty($_POST)) {
        $customer_name = $_POST['customer_name'];
        $customer_address = $_POST['customer_address'];
        $customer_email =   $_POST['customer_email'];
        $customer_phone =   $_POST['customer_phone'];
        $customer_mobile =  $_POST['customer_mobile'];
        $customer_fax =     $_POST['customer_fax'];
        $quickbooks_uid =   $_POST['quickbooks_uid'];
        $representative_name = $_POST['representative_name'];
        $representative_lname = $_POST['representative_lname'];

        $sql = "INSERT INTO `_relationship_db_customers` (`id`, `client_id`, `entity_type`, `customer_name`, `customer_lname`, `customer_address`, `license`, `customer_abn`, `representative_name`, `representative_lname`, `representative_position`, `representative_email`, `representative_mobile`, `customer_phone`, `customer_mobile`, `customer_fax`, `customer_email`, `date_modified`, `modified_by`, `modified_in`, `xero_uid`, `quickbooks_uid`, `myob_uid`, `status`, `xero_status`, `source`) VALUES (NULL, '0', NULL, '$customer_name', NULL, '$customer_address', NULL, NULL, '$representative_name', '$representative_lname', NULL, '', NULL, '$customer_phone', '$customer_mobile', '$customer_fax', '$customer_email', CURRENT_TIMESTAMP, '0', NULL, NULL, '$quickbooks_uid', NULL, NULL, NULL, NULL);";

        if($connect->query($sql)) {
            //
        }
        else {
            //
        }
    }
?>