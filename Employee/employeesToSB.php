<?php
require "../vendor/autoload.php";
require_once "../db_connect.php";

use QuickBooksOnline\API\DataService\DataService;
use QuickBooksOnline\API\Core\Http\Serialization\XmlObjectSerializer;
use QuickBooksOnline\API\Facades\Customer;

// Prep Data Services
$config = include('../config.php');
//Get Token
$accessTokenKey = $_POST["access_token"];
$refreshTokenKey = $_POST["refresh_token"];
$realmId = $_POST["realm_id"];
$id = $_POST["id"];

$dataService = DataService::Configure(array(
    'auth_mode' => 'oauth2',
    'ClientID' => $config['client_id'],
    'ClientSecret' =>  $config['client_secret'],
    'RedirectURI' => $config['oauth_redirect_uri'],
    'accessTokenKey' => $accessTokenKey,
    'refreshTokenKey' => $refreshTokenKey,
    'QBORealmID' => $realmId,
    'baseUrl' => "Development"
));

$dataService->setLogLocation("/Users/hlu2/Desktop/newFolderForLog");
$dataService->throwExceptionOnError(true);
$employee = $dataService->FindbyId('employee', $id);
$error = $dataService->getLastError();
if ($error) {
    echo "The Status code is: " . $error->getHttpStatusCode() . "\n";
    echo "The Helper message is: " . $error->getOAuthHelperError() . "\n";
    echo "The Response message is: " . $error->getResponseBody() . "\n";
}
else {
    // echo "Created Id={$customer->Id}. Reconstructed response body:\n\n";
    // $xmlBody = XmlObjectSerializer::getPostXmlFromArbitraryEntity($customer , $urlResource);
    // echo $xmlBody . "\n";
    // echo json_encode($employee, JSON_PRETTY_PRINT);
    $employee_name = @$employee->GivenName; 
    $employee_lname = @$employee->FamilyName;
    $employee_address = @$employee->PrimaryAddr->Line1. " ". @$employee->PrimaryAddr->City. " ". @$employee->PrimaryAddr->Country;
    $employee_address_line1 = @$employee->PrimaryAddr->Line1;
    $employee_address_postcode = @$employee->PrimaryAddr->PostalCode;
    $employee_address_country = @$employee->PrimaryAddr->Country;
    $employee_address_suburb = @$employee->PrimaryAddr->City;
    $employee_birthday = @$employee->BirthDate;
    $employee_startdate = @$employee->HiredDate;
    $employee_email = @$employee->PrimaryEmailAddr->Address;
    $employee_phone = @$employee->PrimaryPhone->FreeFormNumber;
    $employee_mobile = @$employee->Mobile->FreeFormNumber;
    $employee_fax  = @$employee->Fax->FreeFormNumber;
    $employee_number = @$employee->EmployeeNumber;
    $employee_rate = @$employee->BillRate;
    $quickbooks_uid = @$employee->Id;

    // echo $employee_name . "  ";
    // echo $employee_lname . "  ";
    // echo $employee_address . "  ";
    // echo $employee_address_line1 . "  ";
    // echo $employee_address_postcode  . "  ";
    // echo $employee_address_country . "  ";
    // echo $employee_address_suburb . "  ";
    // echo $employee_birthday . "  ";
    // echo $employee_startdate . "  ";
    // echo $employee_email . "  ";
    // echo $employee_phone . "  ";
    // echo $employee_mobile . "  ";
    // echo $employee_fax . "  ";
    // echo $employee_number . "  "; 
    // echo $employee_rate . "   ";
    // echo $quickbooks_uid. "  "; 
        
    session_start();
    $client_id = $_SESSION["client_id"];
    

    $sql = "INSERT INTO `_relationship_db_employee` (`id`, `client_id`, `employee_name`, `employee_lastname`, `employee_number`, `employee_email`, `employee_phone`, `employee_fax`, `employee_mobile`, `employee_position`, `employee_rate`, `employee_cost_rate`, `employee_id`, `employee_whitecard`, `employee_address`, `employee_address_line1`, `employee_address_suburb`, `employee_address_state`, `employee_address_postcode`, `employee_address_country`, `employee_birthday`, `employee_startdate`, `date_modified`, `modified_by`, `modified_in`, `xero_uid`, `quickbooks_uid`, `myob_uid`, `status`, `xero_status`, `source`) VALUES (NULL, '1', '$employee_name', '$employee_lname', '$employee_number', '$employee_email', '$employee_phone', '$employee_fax', '$employee_mobile', NULL, '$employee_rate', NULL, NULL, NULL, '$employee_address', '$employee_address_line1', '$employee_address_suburb', NULL, '$employee_address_postcode', '$employee_address_country', '$employee_birthday', '$employee_startdate', CURRENT_TIMESTAMP, '0', NULL, NULL, $quickbooks_uid, NULL, NULL, NULL, NULL);";

    if($connect->query($sql)) {
        echo "Success";
    }
    else {
        echo("Error description: " . mysqli_error($connect));
        echo $sql;
    }
}