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
            <a href="../Customer/customerContacts.php" class="btn btn-secondary">Contacts</a>
            <a href="../Sales/sales.php" class="btn btn-secondary">Sales</a>
            <a href="#" class="btn btn-secondary active">Purchases</a>
            <a href="#" class="btn btn-secondary">Time Activity</a>
        </div>
        <br><br>
        
        <div class="btn-group" id="customer">
            <a href="#" class="btn btn-secondary active" onclick="register(this)" id='btnRegister'>Register</a>
            <a href="purchaseIntegrated.php" class="btn btn-secondary">History</a>
        </div>
        <br>
        <br>
        <div id="table">
            <nav class='nav nav-tabs nav-justified'>
                <a class='nav-item nav-link active' href='#'>Small Builders to Quickbooks</a>
                <a class='nav-item nav-link' href='purchase.php'>Quickbooks to Smallbuilders</a>
            </nav><br>
            <div class="row">
                <div class ="col-md-6" style='padding-right:0px;'>
                    <form id="grpSelect">
                        <select id='selectExpense' name="selected_expense" style='width: 175px;'>
                            <option value='0'>All Expenses</option>
                            <option value='3'>Reconciled Expenses</option>    
                        </select>

                        <select id='selectProject' name="selected_project" style='width: 175px;'>
                            <option value='0'>All Project</option> 
                            <?php
                                require_once "../db_connect.php";
                                $sql = "SELECT * FROM _project_db";
                                $query = $connect->query($sql);
                                $option = "";
                                while($row = mysqli_fetch_array($query)) {
                                    $project_id = $row['project_id'];
                                    $project_name = $row['project_name'];
                                    $option .= "<option value=".$project_id.">".$project_name."</option>";
                                }
                                echo $option;
                            ?>
                        </select>

                        <select id='selectSupplier' name="selected_supplier" style='width: 175px;'>
                            <option value='0'>All Supplier</option> 
                            <?php
                                require_once "../db_connect.php";
                                $sql = "SELECT * FROM _supplier_db";
                                $query = $connect->query($sql);
                                $option = "";
                                while($row = mysqli_fetch_array($query)) {
                                    $supplier_id = $row['supplier_id'];
                                    $supplier_name = $row['supplier_name'];
                                    $option .= "<option value=".$supplier_id.">".$supplier_name."</option>";
                                }
                                echo $option;
                            ?>
                        </select>
                    </form>
                </div>
                <div class ="col-md-6" style='padding-left:0px;'>
                    <button onclick="viewPurchase()" class="btn btn-sm btn-success"> View Records </button>
                </div>
            </div>
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
                <input  type='radio' name="selectAction" value="0" class="integrateRadio" onclick="history()"> I do not want to move the selected items to Quickbooks. Move the selected items into Small Builders history.
            </div>
            <center><button id='btnIntegrate' class='mt-2 mb-5 btn btn-success btn-lg' onclick='integratePurchase()' disabled>Integrate</button>
            <button id='btnHistory' class='mt-2 mb-5 btn btn-success btn-lg' onclick='integratePurchase(true)'>Integrate</button></center>
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
            $("#btnHistory").hide();
        }
        function viewPurchase(){
            
            var data = $("#grpSelect").serialize();
            var client_id = "<?php echo $_SESSION["client_id"] ?>";
            
            $.ajax({
                method: "POST",
                url: "getPurchase(SB).php",
                data: data + "&client_id="+client_id, 
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
        function integratePurchase(history) {

            $.confirm({
                onOpenBefore: function (){
                    this.showLoading();
                }
            });

            var integrateCheck = document.querySelectorAll('.integrateCheck:checked');

            var tbl = document.createElement("table");
            var header = tbl.createTHead();
            header.innerHTML = "<th>Project Name</th><th>Supplier/Subcontractor</th><th>Invoice No.</th><th>Invoice Date</th><th>Due Date</th><th>Amount</th>";
            
            var body = tbl.createTBody();
            for (let i = 0; i < integrateCheck.length; i++) {

                var id = integrateCheck[i].value;
                var project_name = integrateCheck[i].parentNode.parentNode.parentNode.childNodes[3].innerHTML;
                var supplier = integrateCheck[i].parentNode.parentNode.parentNode.childNodes[5].innerHTML;
                var invoice_no = integrateCheck[i].parentNode.parentNode.parentNode.childNodes[7].innerHTML;
                var invoice_date = integrateCheck[i].parentNode.parentNode.parentNode.childNodes[9].innerHTML;
                var due_date = integrateCheck[i].parentNode.parentNode.parentNode.childNodes[11].innerHTML;
                //13 account type
                var amount = integrateCheck[i].parentNode.parentNode.parentNode.childNodes[15].innerHTML;

                body.innerHTML += "<tr id='tr"+id+"'><td>"+project_name+"</td><td>"+supplier+"</td><td>"+invoice_no+"</td><td>"+invoice_date+"</td><td>"+due_date+"</td><td>"+amount+"</td></tr>";
                
                if(history){
                    $.ajax({
                        method: "post",
                        url: "purchaseHistory.php",    
                        data:"id="+ id +"&access_token="+ access_token + "&refresh_token=" + refresh_token + "&realm_id=" + realm_id,
                        success: function (data) {
                            if(data == "Success") {
                            
                            }
                            else {
                                $(tbl).find("#tr" + getUrlParameter(this.data,"id")).remove();
                            }
                        }
                    });
                    $(document).one("ajaxStop", function() {
                        sendEmail(tbl.innerHTML,true);
                    });
                }
                else {
                    $.ajax({
                        method: "post",
                        url: "purchaseToQB.php",    
                        data:"id="+ id +"&access_token="+ access_token + "&refresh_token=" + refresh_token + "&realm_id=" + realm_id,
                        success: function (data) {
                            if(data == "Success") {
                            
                            }
                            else {
                                $(tbl).find("#tr" + getUrlParameter(this.data,"id")).remove();
                            }
                        }
                    });
                    $(document).one("ajaxStop", function() {
                        sendEmail(tbl.innerHTML,false);
                    });
                }
            }
/* 
            $.confirm({
                title: "Smallbuilders to Quickbooks",
                columnClass: "large",
                theme: "modern",
                content: "<table class='table'><tr><th>Project Name</th><th>Supplier/Subcontractor</th><th>Invoice No.</th><th>Invoice Date</th><th>Amount</th><th>Status</th></tr></table>",
                onOpenBefore: function () {
                    var confirmJS = this;
                    //Collect all QuickBooks ids
                    var integrateCheck = document.querySelectorAll('.integrateCheck:checked');
                    
                    //Retrieve Customer Info
                    for (let i = 0; i < integrateCheck.length; i++) {
                        var id = integrateCheck[i].value;
                        var project_name = integrateCheck[i].parentNode.parentNode.parentNode.childNodes[3].innerHTML;
                        var supplier = integrateCheck[i].parentNode.parentNode.parentNode.childNodes[5].innerHTML;
                        var invoice_no = integrateCheck[i].parentNode.parentNode.parentNode.childNodes[7].innerHTML;
                        var invoice_date = integrateCheck[i].parentNode.parentNode.parentNode.childNodes[9].innerHTML;
                        var due_date = integrateCheck[i].parentNode.parentNode.parentNode.childNodes[11].innerHTML;
                        //13 account type
                        var amount = integrateCheck[i].parentNode.parentNode.parentNode.childNodes[15].innerHTML;

                        confirmJS.$content.find('table').append("<tr><td>"+project_name+"</td><td>"+supplier+"</td><td>"+invoice_no+"</td><td>"+invoice_date+"</td><td>"+amount+"</td><td id='inte"+id+"'><p style='color: blue'>Integrating</p></td></tr>");
                        
                        $.ajax({
                            method: "post",
                            url: "purchaseToQB.php",    
                            data:"&id="+ id +"&access_token="+ access_token + "&refresh_token=" + refresh_token + "&realm_id=" + realm_id +"&client_id="+$_SESSION["client_id"],
                            success: function (data) {
                                if(data == "Success") {
                                    console.log(data,"DITO SKO");
                                    confirmJS.$content.find('#inte'+ getUrlParameter(this.data,"id") ).html("<p style='color: green'>Integrated</p>");   
                                }
                                else {
                                    console.log(data);
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
                            window.location.href = "purchase(SB).php";
                        }
                    }
                }
            }); */
        }
        
        function sendEmail(tblContent,history) {
            var tbl = document.createElement("table");
            tbl.innerHTML = tblContent;

            if (tbl.getElementsByTagName("tbody")[0].innerHTML == ""){
                alert("No Integration were successful!");
                window.location.href = "/quickbooks-integration/Sales/sales.php?selected_invoice="+selected_+"&submitButton";
                return;
            }
            //Add Style to every th 
            var th = tbl.getElementsByTagName("th");
            var td = tbl.getElementsByTagName("td");
            //Loop to th
            for (let i = 0; i < th.length; i++) {
                th[i].setAttribute("style","border:solid 1px #ccc; text-align:center; padding: 15px 40px;");  
            }
            //Loop to td
            for (let i = 0; i < td.length; i++) {
                td[i].setAttribute("style","border:solid 1px #ccc; text-align:center; padding: 15px 40px;");
            }

            if(history) {
                var subj = "Expense Claim Successfully moved to Small Builders History";
                var desc = "You have successfully moved your Expense Claim details in Small Builders History.";
                
                $.ajax({
                    method: "post",
                    url: "../sendMail.php",
                    data: "tblcontent=" + tbl.innerHTML + "&subj="+ subj + "&desc=" + desc,
                    success: function (data) {
                        var body = document.getElementsByTagName("body")[0];
                        body.innerHTML = `<div class="mt-5 card col-md-8 offset-2" style='background: #FCFCFC; padding: 20px 20px 20px 20px;'>
                            <p style='color: green'>Success! A copy of your submission has been emailed to you.</p>
                        </div>`;

                        setTimeout(function() {window.location.href = "/quickbooks-integration/Purchase/purchase(SB).php"},2000);
                    }
                });
            }
            else {
                var subj = "Expense Claim Successfully added to Quickbooks Purchase";
                var desc = "You have successfully automated your Expense Claim details in your Quickbooks Account.";
                
                $.ajax({
                    method: "post",
                    url: "../sendMail.php",
                    data: "tblcontent=" + tbl.innerHTML + "&subj="+ subj + "&desc=" + desc,
                    success: function (data) {
                        //Change Whole Body InnerHTML
                        var body = document.getElementsByTagName("body")[0];
                        body.innerHTML = `<div class="mt-5 card col-md-8 offset-2" style='background: #FCFCFC; padding: 20px 20px 20px 20px;'>
                            <p style='color: green'>Success! A copy of your submission has been emailed to you.</p>
                            
                            <table class='table table-striped'>`+tblContent+`</table>
                            
                            <br>
                            <div class='text-center'>
                                <a href='purchase(SB).php' class='btn btn-secondary' style='width: 200px;'>Back to Integration</a>
                            </div>
                        </div>`;
                    }
                });
            }
        }

        function history(){
            $("#btnIntegrate").hide();
            $("#btnHistory").show();
        }

        function toPurchaseHistory(){
            $.confirm({
                title: "Smallbuilders History",
                columnClass: "medium",
                theme: "modern",
                content: "",
                onOpenBefore: function () {
                    this.showLoading();
                    var confirmJS = this;
                    var integrateCheck = document.querySelectorAll('.integrateCheck:checked');
                    console.log("PURPOSE",integrateCheck);
                    
                    for (let i = 0; i < integrateCheck.length; i++) {
                        var id = integrateCheck[i].value;
                        console.log("lenggth",integrateCheck.length);
                        $.ajax({
                            method: "post",
                            url: "purchaseHistory.php",
                            data: "id="+id,
                            success: function (data) {
                                if(i == integrateCheck.length - 1) {
                                    confirmJS.hideLoading();
                                    confirmJS.setContent("Done");
                                }
                            }
                        });
                        console.log("lenggthafter",integrateCheck.length);
                    }
                    console.log("IM WORKING");
                },
                buttons: {
                    ok: {
                        action: function () {
                            viewPurchase();
                        }
                    }
                }
            });
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
        };
    </script>
</html>