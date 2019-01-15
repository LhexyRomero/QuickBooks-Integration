<?php

require_once('../vendor/autoload.php');
use QuickBooksOnline\API\DataService\DataService;

$config = include('../config.php');

session_start();

$dataService = DataService::Configure(array(
    'auth_mode' => 'oauth2',
    'ClientID' => $config['client_id'],
    'ClientSecret' =>  $config['client_secret'],
    'RedirectURI' => $config['oauth_redirect_uri'],
    'scope' => $config['oauth_scope'],
    'baseUrl' => "Development"
));

$OAuth2LoginHelper = $dataService->getOAuth2LoginHelper();
$authUrl = $OAuth2LoginHelper->getAuthorizationCodeURL();

// Store the url in PHP Session Object;
$_SESSION['authUrl'] = $authUrl;

//set the access token using the auth object
if (isset($_SESSION['sessionAccessToken'])) {

    $accessToken = $_SESSION['sessionAccessToken'];
    $accessTokenJson = array('token_type' => 'bearer',
        'access_token' => $accessToken->getAccessToken(),
        'refresh_token' => $accessToken->getRefreshToken(),
        'x_refresh_token_expires_in' => $accessToken->getRefreshTokenExpiresAt(),
        'expires_in' => $accessToken->getAccessTokenExpiresAt()
    );
    $dataService->updateOAuth2Token($accessToken);
    $oauthLoginHelper = $dataService -> getOAuth2LoginHelper();
    $CompanyInfo = $dataService->getCompanyInfo();
}

else {
    echo "<script>
        alert('Please Connect again to Quickbooks');
        window.location.href = '../index.php';
    </script>";
}


?>

<!DOCTYPE html>
<html>
<head>
    <link href="../public/css/style.css" rel="stylesheet">
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" rel="stylesheet"
    integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.3.0/jquery-confirm.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.3.0/jquery-confirm.min.js"></script>
    <link href="https://cdn.datatables.net/1.10.18/css/dataTables.bootstrap4.min.css" rel="stylesheet">
    <script src="https://cdn.datatables.net/1.10.18/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.18/js/dataTables.bootstrap4.min.js"></script>

    <script src="../public/js/select2.min.js"></script>
    <link href='../public/css/select2.min.css' rel='stylesheet' type='text/css'>
    
    <script>

        var url = '<?php echo $authUrl; ?>';

        var OAuthCode = function(url) {


            //SHOW LOGIN WINDOW
            this.loginPopup = function (parameter) {
                this.loginPopupUri(parameter);
            }
            
            //CREATE LOGIN WINDOW
            this.loginPopupUri = function (parameter) {

                // Launch Popup
                var parameters = "location=1,width=800,height=650";
                parameters += ",left=" + (screen.width - 800) / 2 + ",top=" + (screen.height - 650) / 2;

                var win = window.open(url, 'connectPopup', parameters);
                var pollOAuth = window.setInterval(function () {
                    try {

                        if (win.document.URL.indexOf("code") != -1) {
                            window.clearInterval(pollOAuth);
                            win.close();
                            location.reload();
                        }
                    } catch (e) {
                        console.log(e)
                    }
                }, 100);
            }
        }

        var apiCall = function() {

            //GET COMPANY NAME
            this.getCompanyName = function() {
                $.ajax({
                    type: "GET",
                    url: "../getCompanyName.php",
                }).done(function( msg ) {
                    $( '#orgName' ).html( msg );
                });
            }
            
            //GET COMPANY INFO
            this.getCompanyInfo = function() {
                $.ajax({
                    type: "GET",
                    url: "getCompanyInfo.php",
                }).done(function( msg ) {
                    $( '#apiCall' ).html( msg );
                });
            }
            
            //REFRESH TOKEN
            this.refreshToken = function() {
                $.ajax({
                    type: "POST",
                    url: "refreshToken.php",
                }).done(function( msg ) {

                });
            }
        }



        var oauth = new OAuthCode(url);
        var apiCall = new apiCall();
    </script>

</head>
<body>

<div class="container">

    <div>
        QuickBooks
    </div>
    <br><br>

    <div id="conn_status">
        <?php
            if(isset($accessTokenJson)) {
                echo "Status: <p style='color: green; display: inline'>Connected</p><br>";
                echo "Organisation: <p id='orgName' style='display: inline'></p><br>";
                echo "<a href='../logout.php'><img src='../disconnect.png'></a>";
            }
            else {
                echo "Status: <p style='color: red; display: inline'>Not Connected</p><br><br>";
                echo "<a class='imgLink' href='#' onclick='oauth.loginPopup()'><img src='../views/C2QB_green_btn_lg_default.png' width='178' /></a>
                <hr />";
            }
        ?>
    </div>
    <br>

        <div class="btn-group">
            <a href="../Customer/customerContacts(SB).php" class="btn btn-secondary">Contacts</a>
            <a href="#" class="btn btn-secondary active">Sales</a>
            <a href="../Purchase/purchase(SB).php" class="btn btn-secondary">Purchases</a>
            <a href="#" class="btn btn-secondary">Time Activity</a>
        </div>
        <br><br>
        
        <div class="btn-group" id="customer">
            <a href="sales.php" class="btn btn-secondary">Register</a>
            <a href="salesIntegrated.php" class="btn btn-secondary active" >History</a>
        </div>
        <br>
        <br>
        <div id="table">
            <br>
            <table id='QBtoSB' class='table table-striped'>
                <thead>
                    <tr>
                        <th>Project Name</th>
                        <th>Customer Name <star class="required">*</star></th>
                        <th>Invoice No. </th>
                        <th>Invoice Date </th>
                        <th>Due Date </th>
                        <th>Account type</th>
                        <th><center>Amount <br><label class="text">(Inc. of GST, if applicable)</label></center></th>
                        <th>Date Moved</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                        require_once "../db_connect.php";

                        $sql = "SELECT * FROM `_relationship_db_sales` JOIN _project_db 
                                ON _relationship_db_sales.project_id = _project_db.project_id 
                                WHERE quickbooks_uid IS NOT NULL";
                        $sql_customers = "SELECT id, customer_name, quickbooks_uid FROM `_relationship_db_customers` WHERE quickbooks_uid IS NOT NULL AND customer_name IS NOT NULL";

                        $query = $connect->query($sql);
                        $allCustomers = $connect->query($sql_customers);

                        $customer_options = array();
                            
                        while($customer = mysqli_fetch_assoc($allCustomers)){                                 
                            $customer_option = "<option value='".$customer["quickbooks_uid"]."'>".$customer["customer_name"]."</option>";
                            array_push($customer_options,$customer_option);
                        }

                        while($row = mysqli_fetch_assoc($query)) {
                            echo "<tr>
                                  <td>". $row["project_name"] ."</td>";
                            echo "<td><select id='selected_customer'>   
                                        ". selectCustomer($row["customer_id"],$customer_options)  ." 
                                     </select>
                                  </td>";
                            echo "<td>". $row["invoice_no"] ."</td>";
                            echo "<td>". $row["invoice_date"] ."</td>";
                            echo "<td>". $row["due_date"] ."</td>";
                            echo "<td>
                                    <select id='select_type' name='selected_expense' style='width: 150px;'>
                                        <option value=".$row["account_type"]."selected> Sales </option>
                                        <option>Interest Income</option>
                                        <option>Other Revenue </option>
                                    </select>
                                </td>";
                            echo "<td>". number_format($row["total_amount"],2) ."</td>";
                            echo "<td>". $row["date_moved"] ."</td>";
                            echo "</tr>";
                        }

                        function selectCustomer($id,$customer_options) {
                            $options = "";
                            for ($i=0; $i < sizeof($customer_options); $i++) {
                                if (strpos($customer_options[$i], $id) !== false) {
                                    //REPLACE value='id' to value='id' selected
                                    $value = "value='".$id."'";
                                    $replacedValue = $value . " selected";
                                    //REPLACE IT
                                    $options .= str_replace($value,$replacedValue,$customer_options[$i]);
                                }
                                //IF D NAHANAP
                                else {
                                    $options .= $customer_options[$i];
                                }

                            }
                            return $options;
                        }
                    ?>
                </tbody>
            </table>
            <center><button id='btnIntegrate' class='mt-2 mb-5 btn btn-success btn-lg' onclick='integratePurchase()' disabled>Integrate</button></center>
            <script>
                $("#QBtoSB").DataTable();         
                $('#select_types').select2();
            </script>
        </div>
        <hr style='clear: both'>
    <!-- <pre id="accessToken">
        <style="background-color:#efefef;overflow-x:scroll"><?php
    $displayString = isset($accessTokenJson) ? $accessTokenJson : "No Access Token Generated Yet";
    echo json_encode($displayString, JSON_PRETTY_PRINT); ?>
    </pre> -->

</div>
</body>
    <script>
        //TOKENS and IDs
        var access_token = "<?php $json = json_encode($accessTokenJson, JSON_PRETTY_PRINT);; 
                                    $json = json_decode($json, true);
                                    echo $json["access_token"];?>";
        var refresh_token = "<?php $json = json_encode($accessTokenJson, JSON_PRETTY_PRINT);; 
                                    $json = json_decode($json, true);
                                    echo $json["refresh_token"];?>";
        var realm_id = "<?php echo $accessToken->getRealmID(); ?>";


        window.onload = function () {
            apiCall.getCompanyName();
        }
        
    </script>
</html>