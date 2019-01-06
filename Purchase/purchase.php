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


?>

<!DOCTYPE html>
<html>
<head>
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
            <a href="#" class="btn btn-secondary">Contacts</a>
            <a href="#" class="btn btn-secondary">Sales</a>
            <a href="#" class="btn btn-secondary active">Purchases</a>
            <a href="#" class="btn btn-secondary">Time Activity</a>
        </div>
        <br><br>
        
        <div class="btn-group" id="customer">
            <a href="#" class="btn btn-secondary active" id='btnCustomers'>Register</a>
            <a href="purchaseIntegrated.php" class="btn btn-secondary" >History</a>
        </div>
        <br>
        <br>
        <div id="table">
            <nav class='nav nav-tabs nav-justified'>
                <a class='nav-item nav-link' href='purchase(SB).php'>Small Builders to Quickbooks</a>
                <a class='nav-item nav-link active' href='#'>Quickbooks to Smallbuilders</a>
            </nav>
            <br>
            <table id='QBtoSB' class='table table-striped'>
                <thead>
                    <tr>
                        <td><input type='checkbox' onclick='checkAll(this);countIntegrate();'></td>
                        <td>Project Name</td>
                        <td>Supplier/Subcontractor</td>
                        <td>Invoice No. </td>
                        <td>Invoice Date </td>
                        <td>Due Date </td>
                        <td>Invoice Attachment</td>
                        <td>Account type</td>
                        <td>Amount</td>
                    </tr>
                </thead>
                <tbody>
                    <?php
                        //GET FIELDS THAT HAVE QUICKBOOKS UID
                        require_once "../db_connect.php";

                        $quickbooks_uids = array();
                        $sql = "SELECT quickbooks_uid FROM _relationship_db_purchase";
                    
                        $query = $connect->query($sql);
                    
                        while($row = mysqli_fetch_array($query)) {
                            array_push($quickbooks_uids,$row["quickbooks_uid"]);
                        }

                        //GET Quickbooks Records
                        $allPurchase = $dataService->Query('SELECT * FROM Purchase');
                        foreach($allPurchase as $purchase) {
                            if (in_array($purchase->Id, $quickbooks_uids, TRUE)) 
                            { 
                                //If Found Show
                            } 
                            else
                            { 
                                $account_id = json_encode(@$purchase->Line->AccountBasedExpenseLineDetail->AccountRef);
                                $selected_sql = "SELECT * FROM _account_type_db WHERE account_id = $account_id";
                                
                                $option = $connect->query($selected_sql);
                                $option_row = mysqli_fetch_array($option);

                                echo "<tr>
                                <td><input type='checkbox' class='form-control integrateCheck' onclick='countIntegrate()' value='".$purchase->Id."'></td>
                                <td> --- </td>";
                                echo "<td> --- </td>";
                                echo "<td>". @$purchase->DocNumber. "</td>";
                                echo "<td>". @$purchase->TxnDate. "</td>";
                                echo "<td>". @$purchase->TxnDate. "</td>";
                                echo "<td> --- </td>";
                                echo "<td> --- </td>";
                                echo "<td>". @$purchase->TotalAmt. "</td>";
                                echo "</tr>";
                            } 
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

        function countIntegrate() {
            var integrateCheck = document.getElementsByClassName("integrateCheck");
            var checks = 0;
            for (let i = 0; i < integrateCheck.length; i++) {
                if(integrateCheck[i].checked == true) {
                    checks++;
                }
            }
            if(checks == 0) {
                document.getElementById("btnIntegrate").disabled = true;
            }
            else {
                document.getElementById("btnIntegrate").disabled = false;
            }
        }

        function checkAll(elem) {
            var integrateCheck = document.getElementsByClassName("integrateCheck");
            if(elem.checked == true) {
                for (let i = 0; i < integrateCheck.length; i++) {
                    integrateCheck[i].checked = true;
                }
            }
            else {
                for (let i = 0; i < integrateCheck.length; i++) {
                    integrateCheck[i].checked = false;
                }
            }
        }

        function integratePurchase() {
            $.confirm({
                title: "Quickbooks to Smallbuilders",
                columnClass: "medium",
                theme: "modern",
                content: "",
                onOpenBefore: function () {
                    //Add Loading 
                    this.showLoading();
                    //PUT THIS TO VARIABLE
                    var confirmJS = this;

                    //Collect all QuickBooks ids na nacheckan
                    var integrateCheck = document.querySelectorAll('.integrateCheck:checked');
                    
                    //Quickbooks Array
                    var purchases = [];

                    //Retrieve Customer Info
                    for (let i = 0; i < integrateCheck.length; i++) {
                        var id = integrateCheck[i].value;

                            //GET QUICKBOOKS RECORD USING ID
                            $.ajax({
                                method: "post",
                                url: "readPurchase(id).php",
                                data: "access_token="+ access_token + "&refresh_token=" + refresh_token + "&realm_id=" + realm_id + "&id=" + id,
                                success: function (data) {
                                    //Add Customer to Array
                                    purchases.push(data);
                                    console.log(purchases);

                                    //Check if All Request is Done
                                    if(i == integrateCheck.length - 1) {
                                        customerToDB (purchases,confirmJS);
                                    }
                                }
                            });
                    }
                },
                buttons: {
                    ok: {
                        action: function () {
                            window.location.href = "purchase.php";
                        }
                    }
                }
            });
        }

        function customerToDB (purchases,confirmJS) {
            for (let i = 0; i < purchases.length; i++) {
                //PARSE JSON
                var purchase = JSON.parse(purchases[i]);
                //CHECK JSON
                console.log(purchases);
                //CREATE FORM
                var frmCustomer = document.createElement("form");
                //Create Fields

                try {
                    var invoice_number = convertNulltoEmpty(purchase.DocNumber);
                } catch (error) {
                    var invoice_number = "";
                }
                try {
                    var invoice_date = convertNulltoEmpty(purchase.TxnDate);
                } catch (error) {
                    var invoice_date = "";
                }
                try {
                    var due_date = convertNulltoEmpty(purchase.TxnDate);
                } catch (error) {
                    var due_date = "";
                }  
                try {
                    var amount = convertNulltoEmpty(purchase.TotalAmt);
                } catch (error) {
                    var amount = "";
                }

                //comment
                var quickbooks_uid = purchase.Id;
                console.log("ID",quickbooks_uid);

                frmCustomer.innerHTML = "<input name='quickbooks_uid' value='"+quickbooks_uid+"'><input name='invoice_number' value='"+invoice_number+"'><input name='invoice_date' value='"+invoice_date+"'><input name='due_date' value='"+due_date+"'><input name='amount' value='"+amount+"'><input name='quickbooks_uid' value='"+quickbooks_uid+"";
                
                console.log(frmCustomer);
                //PASOK SA DB   
                $.ajax({
                    method: "post",
                    url: "purchaseToSB.php",
                    data : $(frmCustomer).serialize(),
                    success: function () {
                        console.log("SUCCESS");
                    },
                });
                
                //IF TAPOS NA MAGPASOK GAGAWING DONE UNG CONFIRM JS
                if(i == purchases.length - 1) {
                    confirmJS.hideLoading();
                    confirmJS.setContent("Done");
                }
            }
        }

        function convertNulltoEmpty(str) {
            try {
                if(str == null ){
                    return "";
                }
                else {
                    return str;
                }
            } catch (error) {
                return "";
            }
        }
    </script>
</html>