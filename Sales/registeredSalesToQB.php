<?php
require "../vendor/autoload.php";

use QuickBooksOnline\API\DataService\DataService;
use QuickBooksOnline\API\Core\Http\Serialization\XmlObjectSerializer;
use QuickBooksOnline\API\Facades\SalesReceipt;

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
$customer_id = $_POST["customer_id"];
$total_amount = $_POST["total_amount"];
$project_name = $_POST["project_name"];
$project_type = $_POST["project_type"];

$description = $project_type.":".$project_name;

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
 
$theResourceObj = SalesReceipt::create([  
    "Line"=> [
        "Description"=> "$description",
        "Amount"=> $total_amount,
        "DetailType"=> "SalesItemLineDetail",
        "SalesItemLineDetail"=> [
            "ItemRef"=> [
                "value"=> "1",
                "name"=> "Services"
            ]
        ]
    ],
    "CustomerRef"=> [
        "value"=> "$customer_id"
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
    $quickbooks_uid = $resultingObj->Id;
    //invoice_type == 3 is moved
    $sql = "UPDATE `_relationship_db_sales` SET `quickbooks_uid` = '$quickbooks_uid', date_moved = CURRENT_TIMESTAMP WHERE `id` = $id";

    if($connect->query($sql)) {
        echo "Success btich";
    }
    else {
        //
    }
} 