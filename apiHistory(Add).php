<?php
    require_once "db_connect.php";

    $operation = $_POST["operation"];
    $client_id = $_POST["client_id"];
    $request_uri = $_POST["request_uri"];
    $request_code = $_POST["request_code"];
    $method = $_POST["method"];
    $request_body = $POST["request_body"];
    $error_message = $POST["error_message"];

    $sql = "INSERT INTO `_relationship_db_employee` (`id`, `client_id`, `employee_name`, `employee_lastname`, `employee_number`, `employee_email`, `employee_phone`, `employee_fax`, `employee_mobile`, `employee_position`, `employee_rate`, `employee_cost_rate`, `employee_id`, `employee_whitecard`, `employee_address`, `employee_address_line1`, `employee_address_suburb`, `employee_address_state`, `employee_address_postcode`, `employee_address_country`, `employee_birthday`, `employee_startdate`, `date_modified`, `modified_by`, `modified_in`, `xero_uid`, `quickbooks_uid`, `myob_uid`, `status`, `xero_status`, `source`) VALUES (NULL, '$client_id', '$employee_name', '$employee_lname', '$employee_number', '$employee_email', '$employee_phone', '$employee_fax', '$employee_mobile', NULL, '$employee_rate', NULL, NULL, NULL, '$employee_address', '$employee_address_line1', '$employee_address_suburb', NULL, '$employee_address_postcode', '$employee_address_country', '$employee_birthday', '$employee_startdate', CURRENT_TIMESTAMP, '0', NULL, NULL, $quickbooks_uid, NULL, NULL, NULL, NULL);";

    if($connect->query($sql)) {
        echo "Success";
    }
    else {
        echo("Error description: " . mysqli_error($connect));
        echo $sql;
    }
?>