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

$sql = "SELECT * FROM `_relationship_db_purchase` 
        JOIN _project_db ON _relationship_db_purchase.project_id = _project_db.project_id 
        JOIN _supplier_db ON supplier_subcontractor_id = supplier_id 
        JOIN _account_type_db ON account_type_id = account_id 
        WHERE _relationship_db_purchase.id = $id";


$query = $connect->query($sql);
    
while($row = mysqli_fetch_array($query)) {
    $invoice_no = $_POST["invoice_no"];
    $invoice_date = $_POST["invoice_date"];
    $due_date = $_POST["due_date"];
    $amount = $_POST["amount"];

    $total = str_replace(",", "", $amount);

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
            "AccountRef"=> [
              "value"=> "42",
              "name"=> "Visa"
            ],
            "PaymentType"=> "CreditCard",
            "Line"=> [
                "Amount"=> "$total",
                "DetailType"=> "AccountBasedExpenseLineDetail",
                "AccountBasedExpenseLineDetail"=> [
                    "AccountRef"=> [
                        "name"=> "Utilities",
                        "value"=> "24"
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
        $quickbooks_uid = $resultingObj->Id;
        
        //expense_type == 2 is moved
        $sql = "UPDATE `_relationship_db_purchase` SET `quickbooks_uid` = '$quickbooks_uid', expense_type = 2, date_moved = CURRENT_TIMESTAMP WHERE `_relationship_db_purchase`.`id` = $id";

        if($connect->query($sql)) {
            echo "Success";
        }
        else {
            //
        }
    }
}




