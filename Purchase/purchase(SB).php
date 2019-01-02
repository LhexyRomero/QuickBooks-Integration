
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
            <a href="#" class="btn btn-secondary active" onclick="register(this)" id='btnRegister'>Register</a>
            <a href="../Employee/employeeContacts.php" class="btn btn-secondary">History</a>
        </div>
        <br>
        <br>
        <div id="table">
            <nav class='nav nav-tabs nav-justified'>
                <a class='nav-item nav-link active' href='#'>Small Builders to Quickbooks</a>
                <a class='nav-item nav-link' href='purchase.php'>Quickbooks to Smallbuilders</a>
            </nav><br>
            <!-- Dropdown --> 
            <form id="grpSelect">
                <select id='selectExpense' name="selected_expense" style='width: 200px;'>
                    <option value='0'>All Expenses</option>    
                </select>

                <select id='selectProject' name="selected_project" style='width: 200px;'>
                    <option value='0'>All Project</option> 
                    <?php
                        require_once "../db_connect.php";
                        $sql = "SELECT * FROM _project_db";

                        $query = $connect->query($sql);
                        $option = "";
                        while($row = mysqli_fetch_array($query)) {
                            $project_id = $row['id'];
                            $project_name = $row['project_name'];
                            $option .= "<option value=".$project_id.">".$project_name."</option>";
                        }

                        echo $option;
                    ?>
                </select>

                <select id='selectSupplier' name="selected_supplier" style='width: 200px;'>
                    <option value='0'>All Supplier</option> 
                    <?php
                        require_once "../db_connect.php";
                        $sql = "SELECT * FROM _supplier_db";

                        $query = $connect->query($sql);
                        $option = "";
                        while($row = mysqli_fetch_array($query)) {
                            $supplier_id = $row['id'];
                            $supplier_name = $row['supplier_name'];
                            $option .= "<option value=".$supplier_id.">".$supplier_name."</option>";
                        }

                        echo $option;
                    ?>
                </select>
            </form>
            <button onclick="viewPurchase()" class="btn btn-sm btn-success"> View Records </button>

            <div id='result'></div>
            <br>
            <table id='QBtoSB' class='table table-striped'>
                <thead>
                    <tr>
                        <th><input type='checkbox' onclick='checkAll(this);countIntegrate();'></th>
                        <th>Project Name</th>
                        <th>Supplier/Subcontractor</th>
                        <th>Invoice No.</th>
                        <th>Invoice Date</th>
                        <th>Due Date</th>
                        <th>Invoice Attachment</th>
                        <th>Account type</th>
                        <th>Amount</th>
                    </tr>
                </thead>
                <tbody id="expense">
                    <tr>
                        <td colspan = '9'><center>No data available in table</center></td>
                    </tr>
                </tbody>
            </table><br>
            <div class='alert alert-primary'>
                <input  type='radio' name="selectAction" value="1" class="integrateRadio" checked> Move the selected entries into my Quickbooks account. <br>
                <input  type='radio' name="selectAction" value="0" class="integrateRadio" > I do not want to move the selected items to Quickbooks. Move the selected items into Small Builders history.
            </div>
            <center><button id='btnIntegrate' class='mt-2 mb-5 btn btn-success btn-lg' onclick='integratePurchase()' disabled>Integrate</button></center>
            <script>
                $("#selectExpense").select2();
                $("#selectProject").select2();
                $("#selectSupplier").select2();
            </script>
        </div>
        <hr style='clear: both'>
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

        function viewPurchase(){
            
            console.log($("#grpSelect").serialize());
            var data = $("#grpSelect").serialize();
            $.ajax({
                method: "POST",
                url: "getPurchase(SB).php",
                data: data, 
                success: function(data){
                    console.log(data);
                    $("#expense").html(data);
                    $("#QBtoSB").DataTable();
                }
            });
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
                return;
            }
            document.getElementById("btnIntegrate").disabled = false;
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
                title: "Smallbuilders to Quickbooks",
                columnClass: "medium",
                theme: "modern",
                content: "",
                onOpenBefore: function () {
                    //Add Loading 
                    this.showLoading();
                    //Get This
                    var confirmJS = this;
                    //Collect all QuickBooks ids
                    var integrateCheck = document.querySelectorAll('.integrateCheck:checked');
                    
                    //Quickbooks Array
                    var purchases = [];

                    //Retrieve Customer Info
                    for (let i = 0; i < integrateCheck.length; i++) {
                        var id = integrateCheck[i].value;
                            //Pupunta sa Database Kunin ung Info
                        $.ajax({
                            method: "post",
                            url: "readPurchase(SBid).php",
                            dataType: "json",
                            data: "id=" + id + "&access_token="+ access_token + "&refresh_token=" + refresh_token + "&realm_id=" + realm_id,
                            success: function (data) {
                                //Add Customer to Array
                                console.log("AJAX FOR getting data from DB",data);
                                purchases.push(data);

                                //Check if All Request is Done
                                if(i == integrateCheck.length - 1) {
                                    purchaseToQB (purchases,confirmJS);
                                }
                            }
                        });
                    }
                },
                buttons: {
                    ok: {
                        action: function () {/* 
                            register(document.getElementById("btnRegister")); */
                            
                            window.location.href = "purchase(SB).php";
                        }
                    }
                }
            });
        }
        
        function purchaseToQB (purchases,confirmJS) {
            for (let i = 0; i < purchases.length; i++) {
                //CREATE FORM
                var frmPurchase = document.createElement("form");
                //Create Fields

                try {
                    var id = convertNulltoEmpty(purchases[i][0].id);
                } catch (error) {
                    var id = "";
                }
                try {
                    var invoice_date = convertNulltoEmpty(purchases[i][0].invoice_date);
                } catch (error) {
                    var invoice_date = "";
                }
                try {
                    var due_date = convertNulltoEmpty(purchases[i][0].due_date);
                } catch (error) {
                    var due_date = "";
                }
                try {
                    var invoice_no = convertNulltoEmpty(purchases[i][0].invoice_no);
                } catch (error) {
                    var invoice_no = "";
                }
                try {
                    var amount = convertNulltoEmpty(purchases[i][0].amount); 
                } catch (error) {
                    var amount = ""; 
                }
                var quickbooks_uid = purchases.quickbooks_uid;


                frmPurchase.innerHTML = "<input name='id' value='"+id+"'><input name='invoice_date' value='"+invoice_date+"'><input name='due_date' value='"+due_date+"'><input name='invoice_no' values='"+invoice_no+"'><input name='amount' value='"+amount+"'>";
                
                $.ajax({
                    method: "post",
                    url: "purchaseToQB.php",
                    data: $(frmPurchase).serialize() +"&access_token="+ access_token + "&refresh_token=" + refresh_token + "&realm_id=" + realm_id,
                    success: function (data) {
                        console.log("SB to QB",data);
                    },
                });
                
                //IF TAPOS LAHAT NG REQUEST
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