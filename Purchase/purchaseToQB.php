<?php
require "../vendor/autoload.php";

use QuickBooksOnline\API\DataService\DataService;
use QuickBooksOnline\API\Core\Http\Serialization\XmlObjectSerializer;
use QuickBooksOnline\API\Facades\Purchase;

// Prep Data Services
$config = include('../config.php');
//Get Token
$accessTokenKey = $_POST["access_token"];
$refreshTokenKey = $_POST["refresh_token"];
$realmId = $_POST["realm_id"];

require_once "../db_connect.php";

//POST
$id = $_POST["id"];
$invoice_no = $_POST["invoice_no"];
$invoice_date = $_POST["invoice_date"];
$due_date = $_POST["due_date"];
$amount = $_POST["amount"];

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
$theResourceObj = Purchase::create([    
    "PaymentType"=> "CreditCard", 
    "AccountRef"=> [
      "name"=> "Visa", 
      "value"=> "42"
    ], 
    "Line"=> [
      [
        "DetailType"=> "AccountBasedExpenseLineDetail", 
        "Amount"=> "$amount", 
        "AccountBasedExpenseLineDetail"=> [
          "AccountRef"=> [
            "name"=> "Meals and Entertainment", 
            "value"=> "13"
          ]
        ]
      ]
    ],
    "DocNumber"=> "$invoice_no",
    "TxnDate"=> "$invoice_date" 
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
    
    $sql = "UPDATE `_relationship_db_purchase` SET `quickbooks_uid` = '$quickbooks_uid' WHERE `_relationship_db_purchase`.`id` = $id";

    if($connect->query($sql)) {
        echo "Success btich";
    }
    else {
        //
    }
}