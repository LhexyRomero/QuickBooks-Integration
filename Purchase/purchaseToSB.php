<?php
require "../vendor/autoload.php";


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
$purchase = $dataService->FindbyId('purchase', $id);
$error = $dataService->getLastError();
if ($error) {
    echo "The Status code is: " . $error->getHttpStatusCode() . "\n";
    echo "The Helper message is: " . $error->getOAuthHelperError() . "\n";
    echo "The Response message is: " . $error->getResponseBody() . "\n";
}
else {
    require_once "../db_connect.php";
    
    if(!empty($_POST)) {
        $project_name = @$purchase->Line->AccountBasedExpenseLineDetail->Description;
        $invoice_number = @$purchase->DocNumber;
        $invoice_date = @$purchase->TxnDate;
        $due_date = @$purchase->TxnDate;
        $amount = @$purchase->TotalAmt;
        $account_id = @$purchase->Line->AccountBasedExpenseLineDetail->AccountRef;
        $quickbooks_uid = @$purchase->Id;
        
        $sql =   "INSERT INTO `tbl_expensesheet` (account_id,quickbooks_uid, name, clientname, clientemail, client_id, project_name, purchase_date, time_stamp, invoice_number, sub_gst, total_amount, total_amount_excl, inclusive_gst, gst_component, pcinvoicenumber, due_date, purchase_items, timestamp_insert, state, expense_submitted, manager, cost_centre, uploaded_invoice) 
                VALUES ('".$account_id."','".$quickbooks_uid."', NULL, NULL, NULL, 0, '".$project_name."', '".$invoice_date."', NULL, '".$invoice_number."',NULL, '".$amount."', NULL, NULL, NULL, NULL, '".$due_date."', NULL, NULL, NULL, NULL, NULL, NULL, NULL)";
        
        if($connect->query($sql)) {
            echo "Success";
        }
        else {
            echo("Error description: " . mysqli_error($connect));
            echo $sql;
        }
    }
}
?>