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

$sql = "SELECT * FROM `tbl_expensesheet` 
        JOIN _supplier_db ON tbl_expensesheet.supplier_id = _supplier_db.supplier_id 
        JOIN _account_type_db ON account_type_id = account_id 
        WHERE tbl_expensesheet.id = $id";

$query = $connect->query($sql);
    
while($row = mysqli_fetch_array($query)) {
    $invoice_no = $row["invoice_number"];
    $invoice_date = $row["purchase_date"];
    $due_date = $row["due_date"];
    $amount = $row["total_amount"];
    $account_id = $row["account_type_id"];
    $project_name = $row["project_name"];

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
                "Description"=> "$project_name",
                "Amount"=> "$total",
                "DetailType"=> "AccountBasedExpenseLineDetail",
                "AccountBasedExpenseLineDetail"=> [
                    "AccountRef"=> [
                        "value"=> "$account_id"
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
        $sql = "UPDATE `tbl_expensesheet` SET `quickbooks_uid` = '$quickbooks_uid', date_transferred_to_quickbooks = CURRENT_TIMESTAMP, transferred_to_quickbooks='yes' WHERE `tbl_expensesheet`.`id` = $id";

        if($connect->query($sql)) {
            echo "Success";
        }
        else {
            //
        }
    }
}




