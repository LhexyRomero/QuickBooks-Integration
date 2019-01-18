<?php
require "../vendor/autoload.php";


use QuickBooksOnline\API\DataService\DataService;
use QuickBooksOnline\API\Core\Http\Serialization\XmlObjectSerializer;
use QuickBooksOnline\API\Facades\Vendor;

// Prep Data Services
$config = include('../config.php');
//Get Token
$accessTokenKey = $_POST["access_token"];
$refreshTokenKey = $_POST["refresh_token"];
$realmId = $_POST["realm_id"];

require_once "../db_connect.php";

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

require_once "../db_connect.php";

//POST
$id = $_POST["id"];

$sql = "SELECT * FROM _relationship_db_suppliers WHERE id=$id";

$query = $connect->query($sql);

while($row = mysqli_fetch_array($query)) {

            $supplier_name = $row["supplier_name"];
            $representative_email = $row["representative_email"];
            $supplier_address = $row["supplier_address"];
            $representative_phone = $row["representative_phone"];
            $representative_mobile = $row["representative_mobile"];
            $representative_fax = $row["representative_fax"];
            $representative_name = $row["representative_name"];
            $representative_lname = $row["representative_lname"];
            $bank_account_number = $row["bank_account_number"];

            //Add a new Vendor
            $theResourceObj = Vendor::create([
                "PrimaryEmailAddr" => [
                "Address" => "$representative_email"
                ], 
                "PrimaryPhone" => [
                "FreeFormNumber" => "$representative_phone"
                ], 
                "DisplayName" => "$supplier_name", 
                "Mobile" => [
                "FreeFormNumber" => "$representative_mobile"
                ], 
                "FamilyName" => "$representative_lname", 
                "AcctNum" => "$bank_account_number", 
                "CompanyName" => "$supplier_name", 
                "BillAddr" => [
                "Line1" => "$supplier_address", 
                ], 
                "GivenName" => "$representative_name", 
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
                
                $sql = "UPDATE `_relationship_db_suppliers` SET `quickbooks_uid` = '$quickbooks_uid' WHERE `_relationship_db_suppliers`.`id` = $id;";

            
                if($connect->query($sql)) {
                    echo "Success";
                }
                else {
                    //
                }
            }
}