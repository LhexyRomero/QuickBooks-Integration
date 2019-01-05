<?php

require_once('../vendor/autoload.php');
use QuickBooksOnline\API\DataService\DataService;

$config = include('../config.php');

session_start();
if(isset($_SESSION["client_id"])) {
    //Has Session
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
        alert('Please Connect to Quickbooks');
        window.location.href = '../index.php';
    </script>";
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
            <a href="#" class="btn btn-secondary active">Contacts</a>
            <a href="#" class="btn btn-secondary">Sales</a>
            <a href="../Purchase/purchase(SB).php" class="btn btn-secondary">Purchases</a>
            <a href="#" class="btn btn-secondary">Time Activity</a>
        </div>
        <br><br>
        
        <div id="contacts">
            <div class="btn-group">
                <a href="../Customer/customerContacts(SB).php" class="btn btn-secondary" id='btnCustomers'>Customers</a>
                <a href="../Employee/employeeContacts(SB).php" class="btn btn-secondary">Employees</a>
                <a href="../Supplier/vendorContacts(SB).php" class="btn btn-secondary active">Vendor</a>
            </div>
        </div>
        <br>
        <br>
        <div id="table">
            <div class='alert alert-warning'>
            Below Contacts are those Vendors that exist in your QuickBooks account but didn't exist in your Small Builders account.
            </div>
            <nav class='nav nav-tabs nav-justified'>
                <a class='nav-item nav-link' href='vendorContacts(SB).php'>Small Builders to Quickbooks</a>
                <a class='nav-item nav-link active' href='#'>Quickbooks to Smallbuilders</a>
            </nav>
            <table id='QBtoSB' class='table table-striped'>
                <thead>
                    <tr>
                        <td><input type='checkbox' onclick='checkAll(this);countIntegrate();'></td>
                        <td>Supplier Name</td>
                        <td>Supplier Address</td>
                        <td>Representative Name</td>
                        <td>Email Address</td>
                        <td>Phone Number</td>
                    </tr>
                </thead>
                <tbody>
                    <?php
                        //GET FIELDS THAT HAVE QUICKBOOKS UID
                        require_once "../db_connect.php";

                        $quickbooks_uids = array();
                        $sql = "SELECT quickbooks_uid FROM _relationship_db_suppliers WHERE client_id = ".$_SESSION["client_id"];
                    
                        $query = $connect->query($sql);
                    
                        while($row = mysqli_fetch_array($query)) {
                            array_push($quickbooks_uids,$row["quickbooks_uid"]);
                        }

                        //GET Quickbooks Records
                        $vendorAll = $dataService->Query('SELECT * FROM Vendor');
                        foreach($vendorAll as $vendor) {
                            if (in_array($vendor->Id, $quickbooks_uids, TRUE)) 
                            { 
                                //If Found Show
                            } 
                            else
                            { 
                                echo "<tr>
                                <td><input type='checkbox' class='form-control integrateCheck' onclick='countIntegrate()' value='".$vendor->Id."'></td>
                                <td>".$vendor->DisplayName."</td>";
                                echo "<td>".@$vendor->BillAddr->Line1.", ".@$vendor->BillAddr->City.", ".@$vendor->BillAddr->Country."</td>";
                                echo "<td>". @$vendor->GivenName." ".@$vendor->MiddleName." ".@$vendor->FamilyName."</td>";
                                echo "<td>". @$vendor->PrimaryEmailAddr->Address. "</td>";
                                echo "<td>Phone: ".@$vendor->PrimaryPhone->FreeFormNumber."<br>Mobile: ".@$vendor->Mobile->FreeFormNumber."<br>Fax: ".@$vendor->Fax->FreeFormNumber."</td>";
                                echo "</tr>"; 
                            } 
                        }
                    ?>
                </tbody>
            </table>
            <button id='btnIntegrate' class='mt-2 mb-5 float-right btn btn-success btn-lg' onclick='integrateVendor()' disabled>Integrate</button>
            <script>
                $("#QBtoSB").DataTable();         
            </script>
        </div>
        <hr style='clear: both'>
        <div id="table2">
            <br>
            <h3 class='text-center'>Reconciled vendor</h3>
            <br>
            <table id='ReconciledCust'class='table table-striped'>
                <thead>
                    <tr>
                        <td>Supplier Name</td>
                        <td>Supplier Address</td>
                        <td>Representative Name</td>
                        <td>Email Address</td>
                        <td>Phone Number</td>
                    </tr>
                </thead>
                <tbody>
                    <?php
                        //GET RECONCILED vendor
                        require_once "../db_connect.php";

                        $records = array();
                        $sql = "SELECT * FROM _relationship_db_suppliers WHERE quickbooks_uid IS NOT NULL AND client_id = ".$_SESSION["client_id"];

                        $query = $connect->query($sql);

                        while($row = mysqli_fetch_array($query)) {
                            echo "<tr>
                                <td>".$row["supplier_name"]."</td>
                                <td>".$row["supplier_address"]."</td>                               
                                <td>".$row["representative_name"] ." ". $row["representative_lname"] . "</td>
                                <td>".$row["representative_email"]."</td>
                                <td>Phone: ".$row["representative_phone"]."<br>Mobile: ".$row["representative_mobile"]. "<br>Fax: ".$row["representative_fax"]."</td>
                            </tr>";
                        }
                        
                    ?>
                </tbody>         
            </table>
            <script>
            $("#ReconciledCust").DataTable(); 
            </script>
        </div>

        
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
            //GET COMPANY NAME
            apiCall.getCompanyName();
            //RETRIEVE
            //vendor();
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

        function integrateVendor() {
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
                    var vendors = [];

                    //Retrieve vendor Info
                    for (let i = 0; i < integrateCheck.length; i++) {
                        var id = integrateCheck[i].value;

                            //GET QUICKBOOKS RECORD USING ID
                            $.ajax({
                                method: "post",
                                url: "readVendor(id).php",
                                data: "access_token="+ access_token + "&refresh_token=" + refresh_token + "&realm_id=" + realm_id + "&id=" + id,
                                success: function (data) {
                                    //Add vendor to Array
                                    vendors.push(data);

                                    //Check if All Request is Done
                                    if(i == integrateCheck.length - 1) {
                                        vendorToDB(vendors,confirmJS);
                                    }
                                }
                            });
                    }
                },
                buttons: {
                    ok: {
                        action: function () {
                            window.location.href = "vendorContacts.php";
                        }
                    }
                }
            });
        }

        function vendorToDB (vendors,confirmJS) {
            for (let i = 0; i < vendors.length; i++) {
                //PARSE JSON
                var vendor = JSON.parse(vendors[i]);
                //CHECK JSON
                console.log(vendor);
                //CREATE FORM
                var frmVendor = document.createElement("form");
                //Create Fields

                //REPRESENTATIVE NAME
                var representative_name = convertNulltoEmpty(vendor.GivenName);
                //REPRESENTATIVE LAST NAME
                var representative_lname = convertNulltoEmpty(vendor.FamilyName);
                //vendor NAME
                var supplier_name = convertNulltoEmpty(vendor.DisplayName);   
                //ADDRESS LINE1
                try {
                    var supplier_address = convertNulltoEmpty(vendor.BillAddr.Line1);
                } catch (error) {
                    var supplier_address = "";
                }
                //CITY
                try {
                    var supplier_city = convertNulltoEmpty(vendor.BillAddr.City);
                } catch (error) {
                    var supplier_city = "";
                }
                //COUNTRY
                try {
                    var supplier_country = convertNulltoEmpty(vendor.BillAddr.Country);
                } catch (error) {
                    var supplier_country = "";
                }  
                //vendor EMAIL
                try {
                    var representative_email = convertNulltoEmpty(vendor.PrimaryEmailAddr.Address);
                } catch (error) {
                    var representative_email = "";
                }

                //PHONE
                try {
                    var representative_phone = convertNulltoEmpty(vendor.PrimaryPhone.FreeFormNumber);
                } catch (error) {
                    var representative_phone = "";
                }
                //MOBILE
                try {
                    var representative_mobile = convertNulltoEmpty(vendor.Mobile.FreeFormNumber);
                } catch (error) {
                    var representative_mobile = "";
                }
                //FAX
                try {
                    var representative_fax = convertNulltoEmpty(vendor.Fax.FreeFormNumber); 
                } catch (error) {
                    var representative_fax = ""; 
                }
                //ACCOUNT NUMBER
                try {
                    var bank_account_number = convertNulltoEmpty(vendor.AcctNum);
                } catch (error) {
                    var bank_account_number = "";
                }
                
                var quickbooks_uid = convertNulltoEmpty(vendor.Id);
                
                frmVendor.innerHTML = "<input name='supplier_name' value='"+supplier_name+"'><input name='supplier_address' value='"+supplier_address+", "+supplier_city+", "+supplier_country+"'><input name='representative_email' value='"+representative_email+"'><input name='representative_phone' value='"+representative_phone+"'><input name='representative_mobile' value='"+representative_mobile+"'><input name='representative_fax' value='"+representative_fax+"'><input name='quickbooks_uid' value='"+quickbooks_uid+"'><input name='representative_name' value='"+representative_name+"'><input name='representative_lname' value='"+representative_lname+"'><input name='bank_account_number' value='"+bank_account_number+"'>";
                
                //alert($(frmVendor).serialize());

                //PASOK SA DB   
                $.ajax({
                    method: "post",
                    url: "vendorsToSB.php",
                    data : $(frmVendor).serialize(),
                    success: function () {
                        //NAPASOK
                    },
                });
                
                //IF TAPOS NA MAGPASOK GAGAWING DONE UNG CONFIRM JS
                if(i == vendors.length - 1) {
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