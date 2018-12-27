
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
        <div id="table"><!-- 
            <div class='alert alert-warning'>
            Below Contacts are those Customers that exist in your Smallbuilders account but didn't exist in your QuickBooks account.
            </div> -->
            <nav class='nav nav-tabs nav-justified'>
                <a class='nav-item nav-link active' href='#'>Small Builders to Quickbooks</a>
                <a class='nav-item nav-link' href='purchase.php'>Quickbooks to Smallbuilders</a>
            </nav><br>
            <!-- Dropdown --> 
            <form id="grpSelect" name="grpSelect">
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
                <!-- <button onclick="viewPurchase()" class="btn btn-sm btn-success" id='but_read'> View Records </button> -->
                <!-- harnessing jquery, sayang naka-import na rin naman na -->
                <button type="submit" class="btn btn-sm btn-success" id='but_read'> View Records </button>
            </form>

            <div id='result'></div>
            <br>
            <table id='QBtoSB' class='table table-striped'>
                <thead>
                    <tr>
                        <td><input type='checkbox' onclick='checkAll(this);countIntegrate();'></td>
                        <td>Project Name</td>
                        <td>Supplier/Subcontractor</td>
                        <td>Invoice No. </td>
                        <td>Due Date <td>
                        <td>Invoice Attachment</td>
                        <td>Account type</td>
                        <td>Amount</td>
                    </tr>
                </thead>
                <tbody id="expense"></tbody>
            </table><br>
            <div class='alert alert-primary'>
                <input type='radio' name="selectAction" value="1" class="integrateRadio" onclick="countIntegrate(true)"> Move the selected entries into my XERO account. <br>
                <input type='radio' name="selectAction" value="0" class="integrateRadio" onclick="countIntegrate(true)"> I do not want to move the selected items to XERO. Move the selected items into Small Builders history. You must select this box first if the expense form attachment is more than 3mb file size.
            </div>
            <center><button id='btnIntegrate' class='mt-2 mb-5 btn btn-success btn-lg' onclick='integrateCustomer()' disabled>Integrate</button></center>
            <script>
                $("#selectExpense").select2();
                $("#selectProject").select2();
                $("#selectSupplier").select2();
                $("#QBtoSB").DataTable();         
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

        // uncomment this and comment the function below if you want to use your method
        // i can't guarantee the functionality of my function, no database to test to
        
        // function viewPurchase(){

        //     console.log("im here");
        //     var data = $("#grpSelect").serialize();
        //     $.ajax({
        //         method: "POST",
        //         url: "getPurchase(SB).php",
        //         data: {data:data},
        //         success: function(data){
        //             console.log(data);
        //             $("#expense").html(data);
        //         }
        //     });
        // }
        $(document).on("submit", "form[name='grpSelect']", function(e) {
            e.preventDefault();

            var formdata = $(this).serialize();

            $.ajax({
                type: "POST",
                url: "getPurchase(SB).php",
                data: {formdata: formdata},
                success: function(data) {
                    console.log(data);
                    $("#expense").html(data);
                }
            });

        });

        function countIntegrate(toIntegrate) {
            var integrateAction = $("input[name=selectAction]:checked").val();
            var integrateCheck = document.getElementsByClassName("integrateCheck");

            for (let i = 0; i < integrateCheck.length; i++) {
                integrateCheck[i].checked == true
            }

            if(toIntegrate) {
                document.getElementById("btnIntegrate").disabled = false;
                return;
            }
            document.getElementById("btnIntegrate").disabled = true;
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
                                url: "readPurchase(SBid).php",
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
                            register(document.getElementById("btnRegister"));
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
                    url: "purchaseToQB.php",
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