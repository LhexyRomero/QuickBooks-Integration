
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
            <a href="#" class="btn btn-secondary">Purchases</a>
            <a href="#" class="btn btn-secondary">Time Activity</a>
        </div>
        <br><br>
        
        <div class="btn-group" id="vendor">
            <a href="#" class="btn btn-secondary" onclick="vendor(this)" id='btnVendors'>Customers</a>
            <a href="../Employee/employeeContacts.php" class="btn btn-secondary">Employees</a>
            <a href="#" class="btn btn-secondary active">Vendor</a>
        </div>
        <br>
        <br>
        <div id="table">
            <div class='alert alert-warning'>
            Below Contacts are those Vendors that exist in your Smallbuilders account but didn't exist in your QuickBooks account.
            </div>
            <nav class='nav nav-tabs nav-justified'>
                <a class='nav-item nav-link active' href='#'>Small Builders to Quickbooks</a>
                <a class='nav-item nav-link' href='vendorContacts.php'>Quickbooks to Smallbuilders</a>
            </nav>
            <table id='QBtoSB' class='table table-striped'>
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
                        //GET FIELDS THAT HAVE QUICKBOOKS UID
                        require_once "../db_connect.php";

                        $quickbooks_uids = array();
                        $sql = "SELECT * FROM _relationship_db_suppliers WHERE quickbooks_uid IS NULL";
                    
                        $query = $connect->query($sql);
                    
                        while($row = mysqli_fetch_array($query)) {
                            echo "<tr>
                            <td><input type='checkbox' class='form-control integrateCheck' onclick='countIntegrate()' value='".$row["id"]."'></td>
                            <td>".$row["supplier_name"]."</td>";
                            echo "<td>". $row["representative_email"] ."</td>";
                            echo "<td>". $row["representative_name"]." " .$row["representative_lname"]."</td>";
                            echo "<td>". $row["supplier_address"]."</td>";
                            echo "<td>Phone: ".$row["representative_phone"]."<br>Mobile: ".$row["representative_mobile"]."<br>Fax: ".$row["representative_fax"]."</td>";
                            echo "</tr>"; 
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
            <h3 class='text-center'>Reconciled Vendor</h3>
            <br>
            <table id='ReconciledCust'class='table table-striped'>
                <thead>
                    <tr>
                        <td>Vendor Name</td>
                        <td>Vendor Email</td>
                        <td>Representative Name</td>
                        <td>Vendor Address</td>
                        <td>Vendor phone Number</td>
                    </tr>
                </thead>
                <tbody>
                    <?php
                        //GET RECONCILED CUSTOMER
                        require_once "../db_connect.php";

                        $records = array();
                        $sql = "SELECT * FROM _relationship_db_suppliers WHERE quickbooks_uid IS NOT NULL";

                        $query = $connect->query($sql);

                        while($row = mysqli_fetch_array($query)) {
                            echo "<tr>
                                <td>".$row["supplier_name"]."</td>
                                <td>".$row["representative_email"]."</td>
                                <td>".$row["representative_name"] ." ". $row["representative_lname"] . "</td>
                                <td>".$row["supplier_address"]."</td>
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
                    var vendors = [];

                    //Retrieve Vendor Info
                    for (let i = 0; i < integrateCheck.length; i++) {
                        var id = integrateCheck[i].value;
                            //Pupunta sa Database Kunin ung Info
                            $.ajax({
                                method: "post",
                                url: "readVendor(SBid).php",
                                dataType: "json",
                                data: "id=" + id + "&access_token="+ access_token + "&refresh_token=" + refresh_token + "&realm_id=" + realm_id,
                                success: function (data) {
                                    //Add Vendor to Array
                                    vendors.push(data);

                                    //Check if All Request is Done
                                    if(i == integrateCheck.length - 1) {
                                        vendorToQB (vendors,confirmJS);
                                    }
                                }
                            });
                    }
                },
                buttons: {
                    ok: {
                        action: function () {
                            window.location.href = "vendorContacts(SB).php";
                        }
                    }
                }
            });
        }
        function vendorToQB (vendors,confirmJS) {
            for (let i = 0; i < vendors.length; i++) {
                //CREATE FORM
                var frmVendor = document.createElement("form");
                //Create Fields

                //CUSTOMER ID
                var id = vendors[i][0].id;
                //REPRESENTATIVE NAME
                var representative_name = convertNulltoEmpty(vendors[i][0].representative_name);
                //REPRESENTATIVE LAST NAME
                var representative_lname = convertNulltoEmpty(vendors[i][0].representative_lname);
                //CUSTOMER NAME
                var supplier_name = convertNulltoEmpty(vendors[i][0].supplier_name);   
                //ADDRESS LINE1
                try {
                    var supplier_address = convertNulltoEmpty(vendors[i][0].supplier_address);
                } catch (error) {
                    var supplier_address = "";
                }
                //CUSTOMER EMAIL
                try {
                    var representative_email = convertNulltoEmpty(vendors[i][0].representative_email);
                } catch (error) {
                    var representative_email = "";
                }
                //PHONE
                try {
                    var representative_phone = convertNulltoEmpty(vendors[i][0].representative_phone);
                } catch (error) {
                    var representative_phone = "";
                }
                //MOBILE
                try {
                    var representative_mobile = convertNulltoEmpty(vendors[i][0].representative_mobile);
                } catch (error) {
                    var representative_mobile = "";
                }
                //FAX
                try {
                    var representative_fax = convertNulltoEmpty(vendors[i][0].representative_fax); 
                } catch (error) {
                    var representative_fax = ""; 
                }
                //ACCOUNT NUMBER
                try {
                    var bank_account_number = convertNulltoEmpty(vendor[i][0].bank_account_number);
                } catch (error) {
                    var bank_account_number = "";
                }
                


                frmVendor.innerHTML = "<input name='id' value='"+id+"'><input name='supplier_name' value='"+supplier_name+"'><input name='supplier_address' value='"+supplier_address+"'><input name='representative_address' values='"+representative_address+"'><input name='representative_phone' value='"+representative_phone+"'><input name='representative_mobile' value='"+representative_mobile+"'><input name='representative_fax' value='"+representative_fax+"'><input name='representative_name' value='"+representative_name+"'><input name='representative_lname' value='"+representative_lname+"'><input name='bank_account_number' value='"+bank_account_number+"'><input name='representative_email' value='"+representative_email+"'>";

                alert($(frmVendor).serialize());
                
                $.ajax({
                    method: "post",
                    url: "vendorsToQB.php",
                    data: $(frmVendor).serialize() +"&access_token="+ access_token + "&refresh_token=" + refresh_token + "&realm_id=" + realm_id,
                    success: function (data) {
                        console.log(data);
                    },
                });
                
                //IF TAPOS LAHAT NG REQUEST
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