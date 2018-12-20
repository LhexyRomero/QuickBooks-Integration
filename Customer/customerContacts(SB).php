
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
        
        <div class="btn-group" id="customer">
            <a href="#" class="btn btn-secondary active" onclick="customer(this)" id='btnCustomers'>Customers</a>
            <a href="../Employee/employeeContacts.php" class="btn btn-secondary">Employees</a>
        </div>
        <br>
        <br>
        <div id="table">
            <!-- table views -->
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
            customer();
        }

        function customer() {
            document.getElementById("table").innerHTML = "Loading...";
            $.ajax({
                type: "post",
                url: "readCustomers(SB).php",
                dataType: "json",
                success: function (data) {
                    console.log(data);
                    //CONSTRUCT TABLE
                    var tblCustomer = document.createElement("table");
                    tblCustomer.setAttribute("class","table table-stripe");
                    var thead = document.createElement("thead");
                    thead.innerHTML = "<tr><th><input type='checkbox' class='form-control' onclick='checkAll(this);countIntegrate();'></th></th><th>Customer Name</th><th>Customer Email</th><th>Representative Name</th><th>Customer Address</th><th>Customer Phone Numbers</th></tr>";
                    var tbody = document.createElement("tbody");

                    //LOOP TABLE
                    for (let i = 0; i < data.length; i++) {
                        var row = tbody.insertRow(-1);
                        var cell0 = row.insertCell(-1);
                        var cell1 = row.insertCell(-1);
                        var cell2 = row.insertCell(-1);
                        var cell3 = row.insertCell(-1);
                        var cell4 = row.insertCell(-1);
                        var cell5 = row.insertCell(-1);
                        

                        
                        cell0.innerHTML = "<input type='checkbox' class='form-control integrateCheck' onclick='countIntegrate()' value="+data[i].id +">";
                        cell1.innerHTML = data[i].customer_name;
                        cell2.innerHTML = data[i].customer_email;
                        cell3.innerHTML = data[i].representative_name + " " + data[i].representative_lname;
                        cell4.innerHTML = data[i].customer_address;
                        cell5.innerHTML = "Phone: "+ data[i].customer_phone + "<br>" + "Mobile: "+ data[i].customer_mobile + "<br>" +"Fax: "+ data[i].customer_fax + "<br>" ;
                    
                    }

                    tblCustomer.appendChild(thead);
                    tblCustomer.appendChild(tbody);

                    document.getElementById("table").innerHTML = "";

                    //CREATE ALERT
                    var alertCard = document.createElement("div");
                    alertCard.setAttribute("class","alert alert-warning");
                    alertCard.innerHTML = "Below Contacts are those Customers that exist in your SmallBuilders account but didn't exist in your Quickbooks account.";
                    document.getElementById("table").appendChild(alertCard);

                    //CREATE QUICKBOOKS TO SMALL BUILDERS
                    var selection = document.createElement("nav");
                    selection.setAttribute("class",'nav nav-tabs nav-justified');
                    selection.innerHTML = "<a class='nav-item nav-link active' href='#'>Small Builders to Quickbooks</a>"+
                        "<a class='nav-item nav-link' href='customerContacts.php'>Quickbooks to Smallbuilders</a>";
                        
                    document.getElementById("table").appendChild(selection);

                    document.getElementById("table").appendChild(tblCustomer);
                    
                    //Make table as Datable
                    $(tblCustomer).DataTable();

                    //CREATE INTEGRATE BUTTON
                    var btnIntegrate = document.createElement("button");
                    btnIntegrate.setAttribute("id", "btnIntegrate");
                    btnIntegrate.setAttribute("class", "mt-2 mb-5 float-right btn btn-success btn-lg");
                    btnIntegrate.disabled = true;
                    btnIntegrate.innerHTML = "Integrate";
                    btnIntegrate.onclick = function () {
                        integrateCustomer();
                    };
                    document.getElementById("table").appendChild(btnIntegrate);
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

        function integrateCustomer() {
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
                    var customers = [];

                    //Retrieve Customer Info
                    for (let i = 0; i < integrateCheck.length; i++) {
                        var id = integrateCheck[i].value;
                            //Pupunta sa Database Kunin ung Info
                            $.ajax({
                                method: "post",
                                url: "readCustomer(SBid).php",
                                dataType: "json",
                                data: "id=" + id + "&access_token="+ access_token + "&refresh_token=" + refresh_token + "&realm_id=" + realm_id,
                                success: function (data) {
                                    //Add Customer to Array
                                    customers.push(data);

                                    //Check if All Request is Done
                                    if(i == integrateCheck.length - 1) {
                                        customerToQB (customers,confirmJS);
                                    }
                                }
                            });
                    }
                },
                buttons: {
                    ok: {
                        action: function () {
                            customer(document.getElementById("btnCustomers"));
                        }
                    }
                }
            });
        }
        function customerToQB (customers,confirmJS) {
            for (let i = 0; i < customers.length; i++) {
                //CREATE FORM
                var frmCustomer = document.createElement("form");
                //Create Fields

                //CUSTOMER ID
                var id = customers[i][0].id;
                //REPRESENTATIVE NAME
                var representative_name = convertNulltoEmpty(customers[i][0].representative_name);
                //REPRESENTATIVE LAST NAME
                var representative_lname = convertNulltoEmpty(customers[i][0].representative_lname);
                //CUSTOMER NAME
                var customer_name = convertNulltoEmpty(customers[i][0].customer_name);   
                //ADDRESS LINE1
                try {
                    var customer_address = convertNulltoEmpty(customers[i][0].customer_address);
                } catch (error) {
                    var customer_address = "";
                }
                //CUSTOMER EMAIL
                try {
                    var customer_email = convertNulltoEmpty(customers[i][0].customer_email);
                } catch (error) {
                    var customer_email = "";
                }
                //PHONE
                try {
                    var customer_phone = convertNulltoEmpty(customers[i][0].customer_phone);
                } catch (error) {
                    var customer_phone = "";
                }
                //MOBILE
                try {
                    var customer_mobile = convertNulltoEmpty(customers[i][0].customer_mobile);
                } catch (error) {
                    var customer_mobile = "";
                }
                //FAX
                try {
                    var customer_fax = convertNulltoEmpty(customers[i][0].customer_fax); 
                } catch (error) {
                    var customer_fax = ""; 
                }


                frmCustomer.innerHTML = "<input name='id' value='"+id+"'><input name='customer_name' value='"+customer_name+"'><input name='customer_address' value='"+customer_address+"'><input name='customer_email' values='"+customer_email+"'><input name='customer_phone' value='"+customer_phone+"'><input name='customer_mobile' value='"+customer_mobile+"'><input name='customer_fax' value='"+customer_fax+"'><input name='representative_name' value='"+representative_name+"'><input name='representative_lname' value='"+representative_lname+"'>";
                
                $.ajax({
                    method: "post",
                    url: "customersToQB.php",
                    data: $(frmCustomer).serialize() +"&access_token="+ access_token + "&refresh_token=" + refresh_token + "&realm_id=" + realm_id,
                    success: function (data) {
                        console.log(data);
                    },
                });
                
                //IF TAPOS LAHAT NG REQUEST
                if(i == customers.length - 1) {
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