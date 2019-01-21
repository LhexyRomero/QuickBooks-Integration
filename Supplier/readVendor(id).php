<?php

require "../vendor/autoload.php";
require_once "../db_connect.php";

use QuickBooksOnline\API\DataService\DataService;
use QuickBooksOnline\API\Core\Http\Serialization\XmlObjectSerializer;
use QuickBooksOnline\API\Facades\Vendor;

session_start();
$client_id = $_SESSION["client_id"];

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
$vendor = $dataService->FindbyId('vendor', $id);
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

    $supplier_name = @addslashes($vendor->DisplayName);
    $supplier_address = @$vendor->BillAddr->Line1 . " " . @$vendor->BillAddr->Middlefield . " " . @$vendor->BillAddr->Country;
    $representative_email =   @$vendor->PrimaryEmailAddr->Address;
    $representative_phone =   @$vendor->PrimaryPhone->FreeFormNumber;
    $representative_mobile =  @$vendor->Mobile->FreeFormNumber;
    $representative_fax =     @$vendor->Fax->FreeFormNumber;
    $quickbooks_uid =   @$vendor->Id;
    $representative_name = @$vendor->GivenName;
    $representative_lname = @$vendor->FamilyName;
    $bank_account_number = @$vendor->AcctNum;

    
    $sql = "INSERT INTO `_relationship_db_suppliers` (`id`, `client_id`, `supplier_name`, `supplier_abn`, `supplier_address`, `representative_name`, `representative_lname`, `representative_jobtitle`, `representative_phone`, `representative_mobile`, `representative_fax`, `representative_email`, `bank_account_number`, `bank_account_name`, `bsb_number`, `date_modified`, `modified_by`, `modified_in`, `xero_uid`, `quickbooks_uid`, `myob_uid`, `status`, `xero_status`, `source`) VALUES (NULL, '$client_id', '$supplier_name', NULL, '$supplier_address', '$representative_name', '$representative_lname', NULL, '$representative_phone', '$representative_mobile', '$representative_fax', '$representative_email', '$bank_account_number', NULL, NULL, CURRENT_TIMESTAMP, '0', NULL, NULL, $quickbooks_uid, NULL, NULL, NULL, NULL);";

    if($connect->query($sql)) {
        echo "Success";
    }
    else {
        echo("Error description: " . mysqli_error($connect));
        echo $sql;
    }
}