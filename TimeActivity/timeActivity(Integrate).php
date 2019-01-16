<?php
require "../vendor/autoload.php";
use QuickBooksOnline\API\DataService\DataService;
use QuickBooksOnline\API\Core\Http\Serialization\XmlObjectSerializer;
use QuickBooksOnline\API\Facades\TimeActivity;
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

//GET INFO ON DATABASE
$sql = "SELECT _relationship_db_employee.quickbooks_uid,_relationship_db_employee.employee_name,_relationship_db_employee.employee_lastname,_relationship_db_employee.employee_rate,project_name,cost_centre,total_paid_hours,date_worked FROM _relationship_db_timesheet inner JOIN _relationship_db_employee on _relationship_db_timesheet.employee_id = _relationship_db_employee.id WHERE _relationship_db_timesheet.id = $id";


$query = $connect->query($sql);

while($row = mysqli_fetch_array($query)) {
    $time =  convertTime($row["total_paid_hours"]);
    // echo $row["employee_name"];
    // echo $row["employee_lastname"];
    // echo $row["total_paid_hours"];
    // echo $row["project_name"];
    // echo $row["cost_centre"];
    // echo $row["employee_rate"];
    // echo $row["date_worked"];
    // echo $row["quickbooks_uid"];
    $dataService->setLogLocation("/Users/hlu2/Desktop/newFolderForLog");
    $dataService->throwExceptionOnError(true);
    $theResourceObj = TimeActivity::create([
        "NameOf" => "Employee",
        "EmployeeRef" => [
            "value" => $row["quickbooks_uid"],
            "name" => $row["employee_name"]. " ". $row["employee_lastname"],
        ],
        "ItemRef" => [
            "name" => "",
            "value" => ""
        ],
        "OtherNameRef" => [
            "value" => "",
            "name" => ""
        ],            
        "Hours" => $time[0],
        "Minutes" => $time[1],
        "TxnDate" => $row["date_worked"],
        "BillableStatus" => "NotBillable",
        "Taxable" => false,
        "HourlyRate" => 15,
        "Description"=> $row["cost_centre"]
    ]);

    $resultingObj = $dataService->Add($theResourceObj);
    $error = $dataService->getLastError();
    if ($error) {
        echo "Error";
    }
    else {
        // UPDATE QUICKBOOKS_UID IN DATABASE   
        $quickbooks_uid = $resultingObj->Id;
        $currentDate = date("Y-m-d");
        $sql = "UPDATE `_relationship_db_timesheet` SET `quickbooks_uid` = '$quickbooks_uid', `date_moved` = '$currentDate' WHERE `_relationship_db_timesheet`.`id` = $id;";

        
        if($connect->query($sql)) {
            echo "Success";
        }
        else {
            echo "Error";
        }
    }
}

function convertTime($dec)
{
    $time = array();
    // start by converting to seconds
    $seconds = ($dec * 3600);
    // we're given hours, so let's get those the easy way
    $hours = floor($dec);
    // since we've "calculated" hours, let's remove them from the seconds variable
    $seconds -= $hours * 3600;
    // calculate minutes left
    $minutes = floor($seconds / 60);
    // remove those from seconds as well
    $seconds -= $minutes * 60;
    // return the time formatted HH:MM:SS
    array_push($time,$hours);
    array_push($time,$minutes);
    return $time;
}

?>