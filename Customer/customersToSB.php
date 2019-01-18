<?php
require "../vendor/autoload.php";


use QuickBooksOnline\API\DataService\DataService;
use QuickBooksOnline\API\Core\Http\Serialization\XmlObjectSerializer;
use QuickBooksOnline\API\Facades\Customer;

session_start();
$client_id = $_SESSION["client_id"];
$request_body = json_encode($_REQUEST);
$request_uri = $_SERVER["REQUEST_URI"];

try {
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
        $customer = $dataService->FindbyId('customer', $id);
        $error = $dataService->getLastError();

        $resp = array();
        if ($error) {
            echo "Error";
            echo "The Status code is: " . $error->getHttpStatusCode() . "\n";
            echo "The Helper message is: " . $error->getOAuthHelperError() . "\n";
            echo "The Response message is: " . $error->getResponseBody() . "\n";
        }
        else {
            // echo "Created Id={$customer->Id}. Reconstructed response body:\n\n";
            // $xmlBody = XmlObjectSerializer::getPostXmlFromArbitraryEntity($customer , $urlResource);
            // echo $xmlBody . "\n";
            require_once "../db_connect.php";

            $customer_name = @addslashes($customer->FullyQualifiedName);
            $customer_address = @$customer->BillAddr->Line1 . " " . @$customer->BillAddr->City . " " . @$customer->BillAddr->Country;
            $customer_email =   @$customer->PrimaryEmailAddr->Address;
            $customer_phone =   @$customer->PrimaryPhone->FreeFormNumber;
            $customer_mobile =  @$customer->Mobile->FreeFormNumber;
            $customer_fax =     @$customer->Fax->FreeFormNumber;
            $quickbooks_uid =   @$customer->Id;
            $representative_name = @$customer->GivenName;
            $representative_lname = @$customer->FamilyName;

            $sql = "INSERT INTO `_relationship_db_customers` (`id`, `client_id`, `entity_type`, `customer_name`, `customer_lname`, `customer_address`, `license`, `customer_abn`, `representative_name`, `representative_lname`, `representative_position`, `representative_email`, `representative_mobile`, `customer_phone`, `customer_mobile`, `customer_fax`, `customer_email`, `date_modified`, `modified_by`, `modified_in`, `xero_uid`, `quickbooks_uid`, `myob_uid`, `status`, `xero_status`, `source`) VALUES (NULL, '$client_id', NULL, '$customer_name', NULL, '$customer_address', NULL, NULL, '$representative_name', '$representative_lname', NULL, '', '', '$customer_phone', '$customer_mobile', '$customer_fax', '$customer_email', CURRENT_TIMESTAMP, '0', NULL, NULL, '$quickbooks_uid', NULL, NULL, NULL, NULL);";

            if($connect->query($sql)) {
                echo "Success";

                $sql = "UPDATE `_relationship_db_customers` SET `quickbooks_uid` = '$quickbooks_uid' WHERE `_relationship_db_customers`.`id` = $id;";

                
                //API HISTORY
                if($connect->query($sql)) {

                    $sql = "INSERT INTO `_api_history` (`id`,`operation`, `client_id`, `timestamp`, `request_uri`, `request_code`, `method`, `request_body`, `error_message`) VALUES (NULL,'INSERT', '$client_id', CURRENT_TIMESTAMP, '$request_uri', '200', 'POST', '$request_body', NULL);";

                    if($connect->query($sql)) {
                        //SUCCESS
                    }
                    else {
                        //
                    }
                }

            }
            else {
                echo("Error description: " . mysqli_error($connect));
                echo $sql;
            }
        }
} 

catch (Exception $e) {
    //Log Error
    $error_msg = $e->getMessage();

    $sql = "INSERT INTO `_api_history` (`id`,`operation`, `client_id`, `timestamp`, `request_uri`, `request_code`, `method`, `request_body`, `error_message`) VALUES (NULL,'INSERT', '$client_id', CURRENT_TIMESTAMP, '$request_uri', '400', 'POST', '$request_body', '$error_msg');";

    
    if($connect->query($sql)) {
        echo $e->getMessage();
    }
    else {
        echo mysqli_error($connect);
    }
}