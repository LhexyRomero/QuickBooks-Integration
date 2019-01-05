<?php
    require_once "../db_connect.php";

    if(!empty($_POST)) {
        $employee_name = $_POST["employee_name"];
        $employee_lname = $_POST["employee_lname"];
        $employee_address = $_POST["employee_address"];
        $employee_address_line1 = $_POST["employee_address_line1"];
        $employee_address_postcode = $_POST["employee_address_postcode"];
        $employee_address_country = $_POST["employee_address_country"];
        $employee_address_suburb = $_POST["employee_address_suburb"];
        $employee_birthday = $_POST["employee_birthday"];
        $employee_startdate = $_POST["employee_startdate"];
        $employee_email = $_POST["employee_email"];
        $employee_phone = $_POST["employee_phone"];
        $employee_mobile = $_POST["employee_mobile"];
        $employee_fax  = $_POST["employee_fax"];
        $employee_number = $_POST["employee_number"];
        $quickbooks_uid = $_POST["quickbooks_uid"];
        
        session_start();
        $client_id = $_SESSION["client_id"];
        

        $sql = "INSERT INTO `_relationship_db_employee` (`id`, `client_id`, `employee_name`, `employee_lastname`, `employee_number`, `employee_email`, `employee_phone`, `employee_fax`, `employee_mobile`, `employee_position`, `employee_rate`, `employee_cost_rate`, `employee_id`, `employee_whitecard`, `employee_address`, `employee_address_line1`, `employee_address_suburb`, `employee_address_state`, `employee_address_postcode`, `employee_address_country`, `employee_birthday`, `employee_startdate`, `date_modified`, `modified_by`, `modified_in`, `xero_uid`, `quickbooks_uid`, `myob_uid`, `status`, `xero_status`, `source`) VALUES (NULL, '$client_id', '$employee_name', '$employee_lname', '$employee_number', '$employee_email', '$employee_phone', '$employee_fax', '$employee_mobile', NULL, NULL, NULL, NULL, NULL, '$employee_address', '$employee_address_line1', '$employee_address_suburb', NULL, '$employee_address_postcode', '$employee_address_country', '$employee_birthday', '$employee_startdate', CURRENT_TIMESTAMP, '0', NULL, NULL, '$quickbooks_uid', NULL, NULL, NULL, NULL);";

        if($connect->query($sql)) {
            echo "Success";
        }
        else {
            echo "Failed";
        }
    }
?>