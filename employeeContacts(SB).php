
<?php

require_once(__DIR__ . '/vendor/autoload.php');
use QuickBooksOnline\API\DataService\DataService;

$config = include('config.php');

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
                    url: "getCompanyName.php",
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
                echo "<a href='logout.php'><img src='disconnect.png'></a>";
            }
            else {
                echo "Status: <p style='color: red; display: inline'>Not Connected</p><br><br>";
                echo "<a class='imgLink' href='#' onclick='oauth.loginPopup()'><img src='views/C2QB_green_btn_lg_default.png' width='178' /></a>
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
            <a href="#" class="btn btn-secondary" onclick="window.location.href='customerContacts.php'" id='btnCustomers'>Customers</a>
            <a href="#" class="btn btn-secondary  active" onclick="window.location.href='employeeContacts.php';" >Employees</a>
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
            employee();
        }

        function employee() {
            document.getElementById("table").innerHTML = "Loading...";
            $.ajax({
                type: "post",
                dataType: "json",
                url: "readEmployees(SB).php",
                data: "access_token="+ access_token + "&refresh_token=" + refresh_token + "&realm_id=" + realm_id,
                success: function (data) {
                    console.log(data);
                    //CONSTRUCT TABLE
                    var tblCustomer = document.createElement("table");
                    tblCustomer.setAttribute("class","table table-stripe");
                    var thead = document.createElement("thead");
                    thead.innerHTML = "<tr><th><input type='checkbox' class='form-control' onclick='checkAll(this);countIntegrate();'></th></th><th>Employee Name</th><th>Email Address</th><th>Contact No.</th></tr>";
                    var tbody = document.createElement("tbody");

                    //LOOP TABLE
                    for (let i = 0; i < data.length; i++) {
                        var row = tbody.insertRow(-1);
                        var cell0 = row.insertCell(-1);
                        var cell1 = row.insertCell(-1);
                        var cell2 = row.insertCell(-1);
                        var cell5 = row.insertCell(-1);
                        
                        var fname = (data[i].employee_name) ?  data[i].employee_name : "";
                        var lname = (data[i].employee_lastname) ? data[i].employee_lastname : "";
                        
                        
                        //CHECK EMAIL
                        try {
                            var email = data[i].employee_email;
                        } catch (error) {
                            var email = "";
                        }
                        
                        //CHECK PHONE
                        try {
                            var phone = "Phone: " + data[i].employee_phone + "<br>";
                        } catch (error) {
                            var phone = "";
                        }

                        //CHECK PHONE NUMBER

                        try {
                            var mobile = "Mobile: " + data[i].employee_mobile + "<br>";
                        } catch (error) {
                            var mobile = "";
                        }

                        //CHECK FAX

                        var fax = (data[i].employee_fax) ? "Fax: "+ data[i].employee_fax + "<br>" : "";

                        
                        cell0.innerHTML = "<input type='checkbox' class='form-control integrateCheck' onclick='countIntegrate()' value="+data[i].id +">";
                        cell1.innerHTML = fname + " " + lname;
                        cell2.innerHTML = email;
                        cell5.innerHTML = phone + mobile + fax;
                        
                    }

                    tblCustomer.appendChild(thead);
                    tblCustomer.appendChild(tbody);

                    document.getElementById("table").innerHTML = "";

                    //CREATE ALERT
                    var alertCard = document.createElement("div");
                    alertCard.setAttribute("class","alert alert-warning");
                    alertCard.innerHTML = "Below Contacts are those Employees that exist in your QuickBooks account but didn't exist in your Small Builders account."
                    document.getElementById("table").appendChild(alertCard);

                    //CREATE QUICKBOOKS TO SMALL BUILDERS
                    var selection = document.createElement("nav");
                    selection.setAttribute("class",'nav nav-tabs nav-justified');
                    selection.innerHTML = "<a class='nav-item nav-link active' href='#'>Small Builders to Quickbooks</a>"+
                        "<a class='nav-item nav-link' href='employeeContacts.php'>Quickbooks to Smallbuilders</a>";
                        
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
                        integrateEmployee();
                    };
                    document.getElementById("table").appendChild(btnIntegrate);
                }
            });
        }

        function countIntegrate() {
            //VALIDATE TO ENABLE INTEGRATE BUTTON
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

        function integrateEmployee() {
            $.confirm({
                title: "Quickbooks to Smallbuilders",
                columnClass: "medium",
                theme: "modern",
                content: "",
                onOpenBefore: function () {
                    //Add Loading 
                    this.showLoading();
                    //Get This
                    var confirmJS = this;

                    //Collect all Check QuickBooks ids
                    var integrateCheck = document.querySelectorAll('.integrateCheck:checked');
                    
                    //Quickbooks Array
                    var employees = [];

                    //Retrieve Customer Info
                    for (let i = 0; i < integrateCheck.length; i++) {
                        var id = integrateCheck[i].value;
                            //Pupunta sa Quickbooks info
                            $.ajax({
                                method: "post",
                                url: "readEmployee(SBid).php",
                                data: "access_token="+ access_token + "&refresh_token=" + refresh_token + "&realm_id=" + realm_id + "&id=" + id,
                                success: function (data) {
                                    //Add Customer to Array
                                    employees.push(data);
                                    //Check if All Request is Done
                                    if(i == integrateCheck.length - 1) {
                                        employeeToQB (employees,confirmJS);
                                    }
                                }
                            });
                    }
                },
                buttons: {
                    ok: {
                        action: function () {
                            employee();
                        }
                    }
                }
            });
        }
        function employeeToQB (employees,confirmJS) {
            for (let i = 0; i < employees.length; i++) {
                //PARSE JSON
                var employee = JSON.parse(employees[i]);
                employee = employee[0];
                //CHECK JSON
                //CREATE FORM
                var frmEmployee = document.createElement("form");
                //Create Fields

                //EMPLOYEE NUMBER
                var employee_number = convertNulltoEmpty(employee.EmployeeNumber);
                
                //EMPLOYEE NAME
                var employee_name = convertNulltoEmpty(employee.employee_name);
                //EMPLOYEE LAST NAME
                var employee_lname = convertNulltoEmpty(employee.employee_lastname);
                //BIRTHDAY
                var employee_birthday = convertNulltoEmpty(employee.employee_birthday);
                //EMPLOYEE START DATE = 
                var employee_startdate = convertNulltoEmpty(employee.employee_startdate);

                //EMPLOYEE POSTAL CODE
                try {
                    var employee_address_postcode = convertNulltoEmpty(employee.employee_address_postcode);
                } catch (error) {
                    var employee_address_postcode = "";
                }
                //ADDRESS LINE 1
                try {
                    var employee_address_line1 = convertNulltoEmpty(employee.employee_address_line1);
                } catch (error) {
                    var employee_address_line1 = "";
                }
                //STATE
                try {
                    var employee_address_suburb = convertNulltoEmpty(employee.employee_address_suburb);
                } catch (error) {
                    var employee_address_suburb = "";
                }
                //COUNTRY
                try {
                    var employee_address_country = convertNulltoEmpty(employee.employee_address_country);
                } catch (error) {
                    var employee_address_country = "";
                }  
                //CUSTOMER EMAIL
                try {
                    var employee_email = convertNulltoEmpty(employee.employee_email);
                } catch (error) {
                    var employee_email = "";
                }
                //PHONE
                try {
                    var employee_phone = convertNulltoEmpty(employee.employee_phone);
                } catch (error) {
                    var employee_phone = "";
                }
                //MOBILE
                try {
                    var employee_mobile = convertNulltoEmpty(employee.employee_mobile);
                } catch (error) {
                    var employee_mobile = "";
                }
                //FAX
                try {
                    var employee_fax = convertNulltoEmpty(employee.employee_fax); 
                } catch (error) {
                    var employee_fax = ""; 
                }
                var id = convertNulltoEmpty(employee.id);
                

                frmEmployee.innerHTML = "<input name='employee_name' value='"+employee_name+"'><input name='employee_lname' value='"+employee_lname+"'><input name='employee_address' value='"+employee_address_line1+ ", " + employee_address_suburb + ", " + employee_address_country+"'><input name='employee_address_line1' value='"+employee_address_line1+"'><input name='employee_address_postcode' value='"+employee_address_postcode+"'><input name='employee_address_suburb' value='"+employee_address_suburb+"'><input name='employee_birthday' value='"+employee_birthday+"'><input name='employee_address_country' value='"+employee_address_country+"'><input name='employee_startdate' value='"+employee_startdate+"'><input name='employee_email' value='"+employee_email+"'><input name='employee_phone' value='"+employee_phone+"'><input name='employee_mobile' value='"+employee_mobile+"'><input name='employee_fax' value='"+employee_fax+"'><input name='employee_number' value='"+employee_number+"'><input name='id' value='"+id+"'>";

                alert($(frmEmployee).serialize());
                
                $.ajax({
                    method: "post",
                    url: "employeesToQB.php",
                    data : $(frmEmployee).serialize() +"&access_token="+ access_token + "&refresh_token=" + refresh_token + "&realm_id=" + realm_id,
                    success: function (data) {
                        //alert(data);
                    },
                });
                
                //IF TAPOS LAHAT NG REQUEST
                if(i == employees.length - 1) {
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