<?php
require "vendor/autoload.php";


use QuickBooksOnline\API\DataService\DataService;
use QuickBooksOnline\API\Core\Http\Serialization\XmlObjectSerializer;
use QuickBooksOnline\API\Facades\Customer;

// Prep Data Services
$config = include('config.php');
//Get Token
$accessTokenKey = $_POST["access_token"];
$refreshTokenKey = $_POST["refresh_token"];
$realmId = $_POST["realm_id"];

//POST
$customer_name = $_POST["customer_name"];
$customer_email = $_POST["customer_email"];
$customer_address = $_POST["customer_address"];
$customer_phone = $_POST["customer_phone"];
$customer_mobile = $_POST["customer_mobile"];
$customer_fax = $_POST["customer_fax"];
$representative_name = $_POST["representative_name"];
$representative_lname = $_POST["representative_lname"];

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
//Add a new Vendor
$theResourceObj = Customer::create([
    "BillAddr" => [
        "Line1" => "$customer_address",
    ],
    "GivenName" => "$representative_name",
    "FamilyName" => "$representative_lname",
    "Suffix" => "",
    "FullyQualifiedName" => "$customer_name",
    "CompanyName" => "$customer_name",
    "DisplayName" => "$customer_name",
    "PrimaryPhone" => [
        "FreeFormNumber" => "$customer_phone"
    ],
    "Mobile" => [
        "FreeFormNumber" => "$customer_mobile"
    ],
    "Fax" => [
        "FreeFormNumber" => "$customer_fax"
    ],
    "PrimaryEmailAddr" => [
        "Address" => "$customer_email"
    ]
]);

echo "HENLO";

$resultingObj = $dataService->Add($theResourceObj);
$error = $dataService->getLastError();
if ($error) {
    echo "The Status code is: " . $error->getHttpStatusCode() . "\n";
    echo "The Helper message is: " . $error->getOAuthHelperError() . "\n";
    echo "The Response message is: " . $error->getResponseBody() . "\n";
}
else {
    echo "Created Id={$resultingObj->Id}. Reconstructed response body:\n\n";
    $xmlBody = XmlObjectSerializer::getPostXmlFromArbitraryEntity($resultingObj, $urlResource);
    echo $xmlBody . "\n";
}