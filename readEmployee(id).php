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
$employee = $dataService->FindbyId('employee', $id);
$error = $dataService->getLastError();
if ($error) {
    echo "The Status code is: " . $error->getHttpStatusCode() . "\n";
    echo "The Helper message is: " . $error->getOAuthHelperError() . "\n";
    echo "The Response message is: " . $error->getResponseBody() . "\n";
}
else {
    // echo "Created Id={$customer->Id}. Reconstructed response body:\n\n";
    // $xmlBody = XmlObjectSerializer::getPostXmlFromArbitraryEntity($customer , $urlResource);
    // echo $xmlBody . "\n";
    echo json_encode($employee, JSON_PRETTY_PRINT);
}