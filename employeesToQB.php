<?php
require "vendor/autoload.php";


use QuickBooksOnline\API\DataService\DataService;
use QuickBooksOnline\API\Core\Http\Serialization\XmlObjectSerializer;
use QuickBooksOnline\API\Facades\Employee;

// Prep Data Services
$config = include('config.php');
//Get Token
$accessTokenKey = $_POST["access_token"];
$refreshTokenKey = $_POST["refresh_token"];
$realmId = $_POST["realm_id"];

require_once "db_connect.php";

//POST
$id = $_POST["id"];
$employee_name = $_POST["employee_name"];
$employee_lname = $_POST["employee_lname"];
$employee_address_line1 = $_POST["employee_address_line1"];
$employee_address_suburb = $_POST["employee_address_suburb"];
$employee_address_country = $_POST["employee_address_country"];
$employee_address_postcode = $_POST["employee_address_postcode"];
$employee_birthday = $_POST["employee_birthday"];
$employee_startdate = $_POST["employee_startdate"];
$employee_email = $_POST["employee_email"];
$employee_phone = $_POST["employee_phone"];
$employee_mobile = $_POST["employee_mobile"];
$employee_fax = $_POST["employee_fax"];
$employee_number = $_POST["employee_number"];



$dataService = DataService::Configure(array(
    'auth_mode' => 'oauth2',
    'ClientID' => $config['client_id'],
    'ClientSecret' =>  $config['client_secret'],
    'RedirectURI' => $config['oauth_redirect_uri'],
    'accessTokenKey' => $accessTokenKey,
    'refreshTokenKey' => $refreshTokenKey,
    'QBORealmID' => $realmId,
    'baseUrl' => "Production"
));


$dataService->setLogLocation("/Users/hlu2/Desktop/newFolderForLog");
$dataService->throwExceptionOnError(true);
//Add a new Vendor
$theResourceObj = Employee::create([
    "PrimaryAddr" => [
      "Line1" => "$employee_address_line1",
      "City" => "$employee_address_suburb",
      "Country" => "$employee_address_country",
      "PostalCode" => "$employee_address_postcode",
    ],
    "BirthDate" => "$employee_birthday",
    "HiredDate" => "$employee_startdate",
    "GivenName" => "$employee_name",
    "FamilyName" => "$employee_name",
    "DisplayName" => "$employee_name $employee_lname",
    "PrimaryPhone" => [
      "FreeFormNumber" => "$employee_phone",
    ],
    "Mobile" => [
      "FreeFormNumber" => "$employee_mobile",
    ]
]);

$resultingObj = $dataService->Add($theResourceObj);
$error = $dataService->getLastError();
if ($error) {
    echo "The Status code is: " . $error->getHttpStatusCode() . "\n";
    echo "The Helper message is: " . $error->getOAuthHelperError() . "\n";
    echo "The Response message is: " . $error->getResponseBody() . "\n";
}

else {
    // UPDATE QUICKBOOKS_UID IN DATABASE   
    $quickbooks_uid = $resultingObj->Id;
    
    $sql = "UPDATE `_relationship_db_employee` SET `quickbooks_uid` = '$quickbooks_uid' WHERE `_relationship_db_employee`.`id` = $id;";

    
    if($connect->query($sql)) {
        echo "Success";
    }
    else {
        //
    }
}