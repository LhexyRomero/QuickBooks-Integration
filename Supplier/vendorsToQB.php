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

// //Get Token
// $accessTokenKey = "eyJlbmMiOiJBMTI4Q0JDLUhTMjU2IiwiYWxnIjoiZGlyIn0..D-dRn7ITHUxmGAtmz5llhA._d3XLBw6jM4xMv3JHHhVAvBg_gQ4HiouCRCZaR5w5lZa7VGAdTQmyUZz5B6WXoZoBDcqpPt3i1rU-DTGxIklMbOoKTIKRwOj1lCbkeCS7ajy6xh2QPM93hZIPCKjK_SlruF5b9FmYCnXekdNZVaOsWzWyRJVw8FgJJEe8WapEsznIGK4i4tVniCfc5poU1JBymYQ6IVS2uyFFnRl7phaoTxx81G0Z26RPyPdPFzNkc1nS9cRPUmuulcX97ZUHrI4TFZUW3AYco5DY9arMp60dob9b5tJi0TRYbytZv6H-3-xLnA4h2UQGzhzMeo-if3y4EOvMWy0tmPvI_Cr_Fucn8N_92UJklP_3FuOqQUTbrmKhv4riUaLdKrkwBKWsxjO-C9leoSFfIpAyUKsUqdQ_QyQkbRRwIC3hbw1G4u7Tr8ulyuJ95I3J-B33niyMVVFhCQ38mBTwXtsTAY5lhqW10pPEEfiWnMoKwi8yDZ9_p879kvXacDUnC8dbTqIzdSiAlzD04GsDYkexp0md8QxeHHzKH_mMr7LJGXATzlJE8z4ZDNe9OYgsCD9kTJz7UwwBA016uWBurGMWXeqbIq5lY7dJqyWibdKTYngH6vk7VyDAEQktnbJ4erH0M5VPm_xS7STW95aBLl1G1uKPmJRcSzpSchkklzl89TkIkkLoIoPnvaD59QrV65lDAW2e3dC_pvruDrqW60zT0DCFL1Ojw8W2Nf0dxIqh1TM12drau93gvtNdF1Xhb0U3rm58hDQyMdU02d_cxpF1axWul5-bBOH48-bMiVC_Tuzr3K9wBIM1v14DDU8JYWStV9KPc1jtalzuiy0KfID4MHtnTIOmw.x9Lfi5OyznNuvi2QbzOglg";
// $refreshTokenKey = "L011553415956HHjd3wx8W3ShDxjSKD8zkd66MluyOHiQZM1M3";
// $realmId = "123146201844524";


//POST
$id = $_POST["id"];
$supplier_name = $_POST["supplier_name"];
$representative_email = $_POST["representative_email"];
$supplier_address = $_POST["supplier_address"];
$representative_phone = $_POST["representative_phone"];
$representative_mobile = $_POST["representative_mobile"];
$representative_fax = $_POST["representative_fax"];
$representative_name = $_POST["representative_name"];
$representative_lname = $_POST["representative_lname"];
$bank_account_number = $_POST["bank_account_number"];

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