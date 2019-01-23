<?php

require_once('../vendor/autoload.php');
use QuickBooksOnline\API\DataService\DataService;

$config = include('../config.php');

session_start();
if(isset($_SESSION["client_id"])) {

}
else {
    header('Location:../login.php');
}

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
            <a href="../Sales/sales.php" class="btn btn-secondary">Sales</a>
            <a href="#" class="btn btn-secondary active">Purchases</a>
            <a href="#" class="btn btn-secondary">Time Activity</a>
        </div>
        <br><br>
        
        <div class="btn-group" id="customer">
            <a href="#" class="btn btn-secondary active" id='btnCustomers'>Register</a>
            <a href="purchaseIntegrated(SB).php" class="btn btn-secondary" >History</a>
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
                        <th><input type='checkbox' onclick='checkAll(this);countIntegrate();'></th>
                        <th>Project Name</th>
                        <th>Supplier/Subcontractor</th>
                        <th>Invoice No. </th>
                        <th>Invoice Date </th>
                        <th>Due Date </th>
                        <th>Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                        //GET FIELDS THAT HAVE QUICKBOOKS UID
                        require_once "../db_connect.php";

                        $quickbooks_uids = array();
                        $sql = "SELECT quickbooks_uid FROM tbl_expensesheet";
                        $sql_account = "SELECT * FROM _account_type_db";

                        $query = $connect->query($sql);
                        $allAccount = $connect->query($sql_account);

                        $account_options = array();

                        while($row = mysqli_fetch_array($query)) {
                            array_push($quickbooks_uids,$row["quickbooks_uid"]);
                        }

                        while($account = mysqli_fetch_assoc($allAccount)) {
                            $account_option = "<option value='".$account["account_id"]."'>".$account["type"]."</option>";
                            array_push($account_options,$account_option);
                        }

                        //GET Quickbooks Records
                        $allPurchase = $dataService->Query('SELECT * FROM Purchase');
                        foreach($allPurchase as $purchase) {
                            if (in_array($purchase->Id, $quickbooks_uids, TRUE)) 
                            { 
                            } 
                            else
                            {
                                /* $account_id = @$purchase->Line->AccountBasedExpenseLineDetail->AccountRef;
                                 */
                                echo "<tr>
                                <td><center><input type='checkbox' class='form-control integrateCheck' onclick='countIntegrate()' value='".$purchase->Id."'></center></td>
                                <td>".@$purchase->Line->AccountBasedExpenseLineDetail->Description."</td>";
                                echo "<td> --- </td>";
                                echo "<td>". @$purchase->DocNumber. "</td>";
                                echo "<td>". @$purchase->TxnDate. "</td>";
                                echo "<td>". @$purchase->TxnDate. "</td>";
                                /* echo "<td>
                                        <select name='type' id='selected_type".$purchase->Id."'>
                                            ".selectAccount($account_id,$account_options)."
                                        </select>
                                    </td>"; */
                                echo "<td>". @$purchase->TotalAmt. "</td>";
                                echo "</tr>";
                            } 
                        }

                        /* function selectAccount($id, $account_options) {
                            $options = "<option value='0' hidden> --- Select Account Type --- </option>";
                            for ($i=0; $i < sizeof($account_options); $i++) { 
                                if(strpos($account_options[$i], $id) !== false) {
                                    
                                    $value = "value='".$id."'";
                                    $replacedValue = $value . " selected";

                                    $options .= str_replace($value,$replacedValue,$account_options[$i]);
                                }
                                else {
                                    $options .= $account_options[$i];
                                }
                            }
                            return $options;
                        } */
                    ?>
                </tbody>
            </table>
            <center><button id='btnIntegrate' class='mt-2 mb-5 btn btn-success btn-lg' onclick='integratePurchase()' disabled>Integrate</button></center>
            <script>
                $("#QBtoSB").DataTable();         
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
            var client_id = "<?php echo $_SESSION["client_id"] ?>";

            $.confirm({
                title: "Quickbooks to Smallbuilders",
                columnClass: "col-md-12",
                theme: "modern",
                content: "<table class='table'><tr><th>Project Name</th><th>Supplier/Subcontractor</th><th>Invoice No.</th><th>Invoice Date</th><th>Amount</th><th>Status</th></tr></table>",
                onOpenBefore: function () {
                    var confirmJS = this;
                    var integrateCheck = document.querySelectorAll('.integrateCheck:checked');
                    
                    for (let i = 0; i < integrateCheck.length; i++) {

                        console.log($("select[name=type]").val());
                        console.log($("select[name=type] option:selected").text());
                        var id = integrateCheck[i].value;
                        var project_name = integrateCheck[i].parentNode.parentNode.parentNode.childNodes[3].innerHTML;
                        var supplier = integrateCheck[i].parentNode.parentNode.parentNode.childNodes[4].innerHTML;
                        var invoice_no = integrateCheck[i].parentNode.parentNode.parentNode.childNodes[5].innerHTML;
                        var invoice_date = integrateCheck[i].parentNode.parentNode.parentNode.childNodes[6].innerHTML;/* 
                        var account_type = integrateCheck[i].parentNode.parentNode.parentNode.childNodes[8].innerHTML; */
                        var amount = integrateCheck[i].parentNode.parentNode.parentNode.childNodes[8].innerHTML;
                        
                        confirmJS.$content.find('table').append("<tr><td>"+project_name+"</td><td>"+supplier+"</td><td>"+invoice_no+"</td><td>"+invoice_date+"</td><td>"+amount+"</td><td id='inte"+id+"'><p style='color: blue'>Integrating</p></td></tr>");
                            //GET QUICKBOOKS RECORD USING ID
                            $.ajax({
                                method: "post",
                                url: "purchaseToSB.php",
                                data: "access_token="+ access_token + "&refresh_token=" + refresh_token + "&realm_id=" + realm_id + "&id=" + id +"&client_id=" + client_id,
                                success: function (data) {
                                    if(data == "Success") {
                                        confirmJS.$content.find('#inte'+ getUrlParameter(this.data,"id") ).html("<p style='color: green'>Integrated</p>");   
                                    }
                                    else {
                                        confirmJS.$content.find('#inte'+ getUrlParameter(this.data,"id") ).html("<p style='color: red'>Failed</p>");   
                                    }

                                    if(i == integrateCheck.length - 1) {
                                        $( document ).ajaxStop(function(){
                                            confirmJS.buttons.ok.enable();
                                        });
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

        var getUrlParameter = function getUrlParameter(getURL,sParam) {
            var sPageURL = getURL,
                sURLVariables = sPageURL.split('&'),
                sParameterName,
                i;

            for (i = 0; i < sURLVariables.length; i++) {
                sParameterName = sURLVariables[i].split('=');

                if (sParameterName[0] === sParam) {
                    return sParameterName[1] === undefined ? true : decodeURIComponent(sParameterName[1]);
                }
            }
        }
    </script>
</html>