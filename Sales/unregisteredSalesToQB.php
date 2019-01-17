<?php
require "../vendor/autoload.php";

use QuickBooksOnline\API\DataService\DataService;
use QuickBooksOnline\API\Core\Http\Serialization\XmlObjectSerializer;
use QuickBooksOnline\API\Facades\Invoice;

// Prep Data Services
$config = include('../config.php');
//Get Token
$accessTokenKey = $_POST["access_token"];
$refreshTokenKey = $_POST["refresh_token"];
$realmId = $_POST["realm_id"];

require_once "../db_connect.php";

//POST
$id = $_POST["id"];

$sql = "SELECT * FROM `_relationship_db_sales` JOIN _project_db 
        ON _relationship_db_sales.project_id = _project_db.project_id 
        WHERE id = $id";

$query = $connect->query($sql);

while($row = mysqli_fetch_array($query)) {

  $invoice_no = $row["invoice_no"];
  $invoice_date = $row["invoice_date"];
  $due_date = $row["due_date"];
  $customer_id = $row["customer_id"];
  $total_amount = $row["total_amount"];
  $project_name = $row["project_name"];
  $project_type = $row["project_type"];

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
  
  $theResourceObj = Invoice::create([  
        "Line"=> [
          [
            "Amount"=> "$total_amount",
            "Description"=> "$description",
            "DetailType"=> "SalesItemLineDetail",
            "SalesItemLineDetail"=> [
              "ItemRef"=> [
                "value"=> "1",
                "name"=> "Services"
              ]
            ]
          ]
        ],
        "CustomerRef"=> [
          "value"=> "$customer_id"
        ],
      "DocNumber"=> "$invoice_no",
      "TxnDate"=> "$invoice_date",
      "DueDate"=> "$due_date"
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
      $sql = "UPDATE `_relationship_db_sales` SET `quickbooks_uid` = '$quickbooks_uid', date_moved = CURRENT_TIMESTAMP WHERE `id` = $id";

      if($connect->query($sql)) {
          echo "Success";
      }
      else {
          //
      }
  } 
}