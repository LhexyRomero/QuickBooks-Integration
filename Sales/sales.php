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

$_SESSION['authUrl'] = $authUrl;

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
            this.loginPopup = function (parameter) {
                this.loginPopupUri(parameter);
            }
            
            this.loginPopupUri = function (parameter) {

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

            this.getCompanyName = function() {
                $.ajax({
                    type: "GET",
                    url: "../getCompanyName.php",
                }).done(function( msg ) {
                    $( '#orgName' ).html( msg );
                });
            }

            this.getCompanyInfo = function() {
                $.ajax({
                    type: "GET",
                    url: "getCompanyInfo.php",


                }).done(function( msg ) {
                    $( '#apiCall' ).html( msg );
                });
            }
            
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
            <a href="../Purchase/purchase.php" class="btn btn-secondary">Purchases</a>
            <a href="#" class="btn btn-secondary">Time Activity</a>
        </div>
        <br><br>
        
        <div class="btn-group" id="customer">
            <a href="#" class="btn btn-secondary active">Register</a>
            <a href="salesIntegrated.php" class="btn btn-secondary">History</a>
        </div>
        <br>
        <br>
        <div id="table">
            <form method="get">
                <div class="row">
                    <div class ="col-md-3" style='padding-right:0px;'>
                            <select id="invoice_type" name='selected_invoice' style='width: 250px;'>
                                <option value ='0' hidden>--- Select Invoice Type ---</option>
                                <option value='1'>Registered Invoices</option>
                                <option value='2'>Unregistered Invoices</option>    
                            </select>
                    </div>
                    <div class ="col-md-9" style='padding-left:0px;'>
                        <button name="submitButton" type="submit" class="btn btn-sm btn-success"> View Records </button>
                    </div>
                </div>
                <br>
                <table id='SBtoQB' class='table table-striped'>
                    <thead>
                        <tr>
                            <th><input type='checkbox' onclick='checkAll(this);countIntegrate();'></th>
                            <th>Project Name</th>
                            <th>Customer Name <star class="required">*</star></th>
                            <th>Invoice No. </th>
                            <th>Invoice Date </th>
                            <th>Due Date </th>
                            <th><center>Amount <br><label class="text">(Inc. of GST, if applicable)</label></center></th>
                        </tr>
                    </thead>
                    <tbody id="sales">
                    <?php
                        require_once "../db_connect.php";
                        
                        if(isset($_GET["submitButton"])){
                            
                            $selected_invoice =  $_GET["selected_invoice"];
                            $quickbooks_uids = array();
                            $sql = "SELECT quickbooks_uid FROM _relationship_db_purchase";
                            $sql_sales = "SELECT * FROM `_relationship_db_sales` JOIN _project_db 
                                        ON _relationship_db_sales.project_id = _project_db.project_id 
                                        WHERE invoice_type = $selected_invoice AND quickbooks_uid IS NULL";
                            $sql_customers = "SELECT id, customer_name, quickbooks_uid FROM `_relationship_db_customers` WHERE quickbooks_uid IS NOT NULL AND customer_name IS NOT NULL";
                            
                            $query = $connect->query($sql);
                            $sales_query = $connect->query($sql_sales);
                            $allCustomers = $connect->query($sql_customers);

                            while($row = mysqli_fetch_array($query)) {
                                array_push($quickbooks_uids,$row["quickbooks_uid"]);
                            }

                            $customer_options = array();
                            
                            while($customer = mysqli_fetch_assoc($allCustomers)){                                 
                                $customer_option = "<option value='".$customer["quickbooks_uid"]."'>".$customer["customer_name"]."</option>";
                                array_push($customer_options,$customer_option);
                            }

                            $output = "";
                            while($row = mysqli_fetch_assoc($sales_query)) {
                            
                                if($row["customer_id"] == ""){
                                    $output .= "<tr class='sales' id = '".$row["id"]."'>
                                        <td><center><input type='checkbox' id='check_no".$row["id"]."' name='unable' class='form-control integrateCheck' onclick='countIntegrate()' value='".$row["id"]."'></td></center>
                                        <td>". $row["project_name"]. "</td>
                                        <td><select id='selected_customer_a".$row["id"]."' onchange='updateCustomer(".$row["id"].",1)'>    
                                                <option value ='0' hidden> --- Select Customer --- </option>
                                                    ". selectCustomer($row["customer_id"],$customer_options). "
                                                </select>
                                            </td>
                                            <td>". $row["invoice_no"]. "</td>
                                            <td>". $row["invoice_date"]. "</td>
                                            <td>". $row["due_date"]. "</td>
                                            <td>". number_format($row["total_amount"],2). "</td>
                                        </tr>";
                                }
                                else {
                                    
                                $output .= "<tr class='sales' id = '".$row["id"]."'>
                                        <td><center><input type='checkbox' class='form-control integrateCheck' onclick='countIntegrate()' value='".$row["id"]."'></td></center>
                                        <td>". $row["project_name"]. "</td>
                                        <td><select id='selected_customer_b".$row["id"]."' onchange='updateCustomer(".$row["id"].",2)'>   
                                                ". selectCustomer($row["customer_id"],$customer_options)  ." 
                                            </select>
                                            </td>
                                            <td>". $row["invoice_no"]. "</td>
                                            <td>". $row["invoice_date"]. "</td>
                                            <td>". $row["due_date"]. "</td>
                                            <td>". number_format($row["total_amount"],2). "</td>
                                        </tr>";
                                }
                            }
                            echo $output;
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
            </form>
            <center><button id='btnIntegrate' class='mt-2 mb-5 btn btn-success btn-lg' onclick='integrateSales()' disabled>Integrate</button></center>
            <script>
                $("#SBtoQB").DataTable();         
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
            var selected_ = "<?php echo $selected_invoice ?>";
            $("#invoice_type option[value='"+selected_+"']").attr("selected",true);
            $("input[name=unable]").attr('disabled', true); 
        }

        function checkBox(id) {    
        }

        function updateCustomer(id,type){
            if (type == 1 ){
                customer_id = $("#selected_customer_a"+id).val();
            }

            else {
                customer_id = $("#selected_customer_b"+id).val();
            }
            $.ajax({
                method: "POST",
                url: "updateCustomer.php",
                data: '&id='+id +'&customer_id='+customer_id,
                success: function(data){
                    $("#check_no"+id).attr('disabled', false); 
                    $("#check_no"+id).attr( 'checked', true );
                    countIntegrate();
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

        function integrateSales() {
            var invoice_global = "<?php echo $selected_invoice ?>";

            $.confirm({
                title: "Smallbuilders to Quickbooks",
                columnClass: "large",
                theme: "modern",
                content: "<table class='table'><tr><th>Project Name</th><th>Customer Name</th><th>Invoice No.</th><th>Invoice Date</th><th>Amount</th><th>Status</th></tr></table>",
                onOpenBefore: function () {

                    var confirmJS = this;
                    var integrateCheck = document.querySelectorAll('.integrateCheck:checked');
                    
                    for (let i = 0; i < integrateCheck.length; i++) {
                        var id = integrateCheck[i].value;
                        var project_name = integrateCheck[i].parentNode.parentNode.parentNode.childNodes[3].innerHTML;
                        var customer_name = integrateCheck[i].parentNode.parentNode.parentNode.childNodes[5].innerHTML;
                        var invoice_no = integrateCheck[i].parentNode.parentNode.parentNode.childNodes[7].innerHTML;
                        var invoice_date = integrateCheck[i].parentNode.parentNode.parentNode.childNodes[9].innerHTML;
                        var amount = integrateCheck[i].parentNode.parentNode.parentNode.childNodes[13].innerHTML;
                        
                        confirmJS.$content.find('table').append("<tr><td>"+project_name+"</td><td>"+customer_name+"</td><td>"+invoice_no+"</td><td>"+invoice_date+"</td><td>"+amount+"</td><td id='inte"+id+"'><p style='color: blue'>Integrating</p></td></tr>")
                        
                        if(invoice_global == 1){
                            $.ajax({
                                method: "post",
                                url: "registeredSalesToQB.php",
                                data:  "id=" + id + "&access_token="+ access_token + "&refresh_token=" + refresh_token + "&realm_id=" + realm_id,
                                success: function (data) {
                                    if(data == "Success") {
                                        console.log("LEKI");
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
                        else {
                            $.ajax({
                                method: "post",
                                url: "unregisteredSalesToQB.php",
                                data: "id=" + id + "&access_token="+ access_token + "&refresh_token=" + refresh_token + "&realm_id=" + realm_id,
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
                    }
                },
                buttons: {
                    ok: {
                        action: function () {
                            window.location.href = "/quickbooks-integration/Sales/sales.php?selected_invoice="+invoice_global+"&submitButton";
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