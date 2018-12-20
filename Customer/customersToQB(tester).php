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

require_once "db_connect.php";

// //Get Token
// $accessTokenKey = "eyJlbmMiOiJBMTI4Q0JDLUhTMjU2IiwiYWxnIjoiZGlyIn0..D-dRn7ITHUxmGAtmz5llhA._d3XLBw6jM4xMv3JHHhVAvBg_gQ4HiouCRCZaR5w5lZa7VGAdTQmyUZz5B6WXoZoBDcqpPt3i1rU-DTGxIklMbOoKTIKRwOj1lCbkeCS7ajy6xh2QPM93hZIPCKjK_SlruF5b9FmYCnXekdNZVaOsWzWyRJVw8FgJJEe8WapEsznIGK4i4tVniCfc5poU1JBymYQ6IVS2uyFFnRl7phaoTxx81G0Z26RPyPdPFzNkc1nS9cRPUmuulcX97ZUHrI4TFZUW3AYco5DY9arMp60dob9b5tJi0TRYbytZv6H-3-xLnA4h2UQGzhzMeo-if3y4EOvMWy0tmPvI_Cr_Fucn8N_92UJklP_3FuOqQUTbrmKhv4riUaLdKrkwBKWsxjO-C9leoSFfIpAyUKsUqdQ_QyQkbRRwIC3hbw1G4u7Tr8ulyuJ95I3J-B33niyMVVFhCQ38mBTwXtsTAY5lhqW10pPEEfiWnMoKwi8yDZ9_p879kvXacDUnC8dbTqIzdSiAlzD04GsDYkexp0md8QxeHHzKH_mMr7LJGXATzlJE8z4ZDNe9OYgsCD9kTJz7UwwBA016uWBurGMWXeqbIq5lY7dJqyWibdKTYngH6vk7VyDAEQktnbJ4erH0M5VPm_xS7STW95aBLl1G1uKPmJRcSzpSchkklzl89TkIkkLoIoPnvaD59QrV65lDAW2e3dC_pvruDrqW60zT0DCFL1Ojw8W2Nf0dxIqh1TM12drau93gvtNdF1Xhb0U3rm58hDQyMdU02d_cxpF1axWul5-bBOH48-bMiVC_Tuzr3K9wBIM1v14DDU8JYWStV9KPc1jtalzuiy0KfID4MHtnTIOmw.x9Lfi5OyznNuvi2QbzOglg";
// $refreshTokenKey = "L011553415956HHjd3wx8W3ShDxjSKD8zkd66MluyOHiQZM1M3";
// $realmId = "123146201844524";


//POST
$customer_name = $_POST["customer_name"];
$customer_email = $_POST["customer_email"];
$customer_address = $_POST["customer_address"];
$customer_phone = $_POST["customer_phone"];
$customer_mobile = $_POST["customer_mobile"];
$customer_fax = $_POST["customer_fax"];
$representative_name = $_POST["representative_name"];
$representative_lname = $_POST["representative_lname"];

$dataService = DataService::Configure(array(
    'auth_mode' => 'oauth2',
    'ClientID' => $config['client_id'],
    'ClientSecret' =>  $config['client_secret'],
    'RedirectURI' => $config['oauth_redirect_uri'],
    'accessTokenKey' => "eyJlbmMiOiJBMTI4Q0JDLUhTMjU2IiwiYWxnIjoiZGlyIn0..UERxP_LDnFJ4k4ii2a1asg.F15iOwfy6eG9lo9vdhdMwod4wkV87U2ylEwZqFGmo0M-nEB-rhQaHIgZ8v8drkdzmCXzXoM6fshX6oBommmOkJkLpZyxNQtWYqLl-mCBZUTIYxS9xsc_yzkKhF63wmAD-zBbUdMZ8dYGk6vXfgrF4sr0motl6GL4_uOTgjD_3wsJPQwHi_1zfQCLHVkTtvEr0gOB3l2TH28HhnwF5TrMccuOxJiX3vH3e_rlu8S9iLIqjQDk6Nxc229AqpAiwqVRW2YeLGbHp_ErNKsCr7XgxAP2VH5lNI9MBdp8Y3Y4kucgb5P-NN8lTxyEdF7HJ70mgI9L0FTFhSi1-CLnHLDLOGpHhDMcu48_ptk_rfiNiTQH_i4fvX9CvNu16tSUzbdOFQl3NmSttd5tZ4hNHu0nUi-SMHE69wpp3XG95ysZMsLtWcOL7Try5fDeOQh7o4ZSlbm6LHScYKhZrz5imL1vXy8c7suzC5Gx0wqrm0K74WYGq6Yjo4QOzDpRnsBHXy_1oSMJz-VvOh6eGvgXtUutCoLuoIfn8P3w1oF0TdT4AouwNnetXTpHWigHsDzYl4sGEhL6RlzRqnQoGbiA7LPoN1b8p4ae-Ja_1-8_1NZHdoSaufUPsgHT3DPpzcUKIEefMbc91W1gp__ZX6cVsfOZkRy8YY0TDGNedvM0-WQ99mn1xOSlZEV6KhJErLWtHuSigABCJAFoUTjr7pyPrWvFot5ssKdStyzQo0PuGEtSWV857GqCMjgGFE8vKWyvhIHwOiRAvN8AGJBZ4HUyiNBLXD3O4qpW9NxHXInMdhf3AAT73Xdg_XFr9MtrQ1qPVOfIZkmfkEuQIXfx0kH-5akHCQ.9jRU1ohcKvEheMZiFP8ulw",
    'refreshTokenKey' => "L011553474583P0RetVKRDYEhzTbnARBBOe2RBubLEzqYDgBGs",
    'QBORealmID' => "123146201844524",
    'baseUrl' => "Production"
));


$dataService->setLogLocation("/Users/hlu2/Desktop/newFolderForLog");
$dataService->throwExceptionOnError(true);
//Add a new Vendor
$theResourceObj = Customer::create([
    "BillAddr" => [
        "Line1" => "123 Main Street",
        "City" => "Mountain View",
        "Country" => "USA",
        "CountrySubDivisionCode" => "CA",
        "PostalCode" => "94042"
    ],
    "Notes" => "Here are other details.",
    "Title" => "Mr",
    "GivenName" => "James",
    "MiddleName" => "B",
    "FamilyName" => "King",
    "Suffix" => "Jr",
    "FullyQualifiedName" => "King Groceries",
    "CompanyName" => "King Groceries",
    "DisplayName" => "King's Groceries Displayname",
    "PrimaryPhone" => [
        "FreeFormNumber" => "(555) 555-5555"
    ],
    "PrimaryEmailAddr" => [
        "Address" => "jdrew@myemail.com"
    ]
]);

$resultingObj = $dataService->Add($theResourceObj);
$error = $dataService->getLastError();
if ($error) {
    echo "The Status code is: " . $error->getHttpStatusCode() . "\n";
    echo "The Helper message is: " . $error->getOAuthHelperError() . "\n";
    echo "The Response message is: " . $error->getResponseBody() . "\n";
}

else {
    // echo "Created Id={$resultingObj->Id}. Reconstructed response body:\n\n";
    $resultJSON = json_encode($resultingObj, JSON_PRETTY_PRINT);
    echo $resultJSON;
    // $xmlBody = XmlObjectSerializer::getPostXmlFromArbitraryEntity($resultingObj, $urlResource);
    // echo $xmlBody . "\n";
}