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
            <a href="../TimeActivity/timeActivity.php" class="btn btn-secondary">Time Activity</a>
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
                            $sql = "SELECT quickbooks_uid FROM _relationship_db_sales";
                            $sql_sales = "SELECT * FROM `_relationship_db_sales` JOIN _project_db 
                                        ON _relationship_db_sales.project_id = _project_db.project_id 
                                        WHERE invoice_type = $selected_invoice AND client_id = ".$_SESSION["client_id"]." AND quickbooks_uid IS NULL AND date_moved IS NULL";
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
            </form><br>
            <div class='alert alert-primary'>
                <input  type='radio' name="selectAction" class="integrateRadio" checked> Move the selected entries into my Quickbooks account. <br>
                <input  type='radio' name="selectAction" class="integrateRadio" onclick ="history()"> I do not want to move the selected items to Quickbooks. Move the selected items into Small Builders history.
            </div>
            <center><button id='btnIntegrate' class='mt-2 mb-5 btn btn-success btn-lg' onclick='integrateSales()' disabled>Integrate</button>
            <button id='btnHistory' class='mt-2 mb-5 btn btn-success btn-lg' onclick='integrateSales(true)'>Integrate</button></center>
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
            $("#btnHistory").hide();
            var selected_ = "<?php echo $selected_invoice ?>";
            $("#invoice_type option[value='"+selected_+"']").attr("selected",true);
            $("input[name=unable]").attr('disabled', true); 
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

        function integrateSales(history) {
            console.log(history);
            var invoice_global = "<?php echo $selected_invoice ?>";
            var radio_val = $(".integrateRadio").val();

            $.confirm({
                onOpenBefore: function (){
                    this.showLoading();
                }
            });

            var integrateCheck = document.querySelectorAll('.integrateCheck:checked');

            var tbl = document.createElement("table");
            var header = tbl.createTHead();
            header.innerHTML = "<th>Project Name</th><th>Customer Name</th><th>Invoice No.</th><th>Invoice Date</th><th>Due Date</th><th>Amount</th>";

            var body = tbl.createTBody();
            for (let i = 0; i < integrateCheck.length; i++) {
                var record = tbl.insertRow(-1);

                var id = integrateCheck[i].value;
                var project_name = integrateCheck[i].parentNode.parentNode.parentNode.childNodes[3].innerHTML;
                var customer_name = integrateCheck[i].parentNode.parentNode.parentNode.childNodes[5].innerHTML;
                var invoice_no = integrateCheck[i].parentNode.parentNode.parentNode.childNodes[7].innerHTML;
                var invoice_date = integrateCheck[i].parentNode.parentNode.parentNode.childNodes[9].innerHTML;
                var due_date = integrateCheck[i].parentNode.parentNode.parentNode.childNodes[11].innerHTML;
                var amount = integrateCheck[i].parentNode.parentNode.parentNode.childNodes[13].innerHTML;
                body.innerHTML += "<tr id='tr"+id+"'><td>"+project_name+"</td><td>"+customer_name+"</td><td>"+invoice_no+"</td><td>"+invoice_date+"</td><td>"+due_date+"</td><td>"+amount+"</td></tr>"
               
                if(history){
                    $.ajax({
                        method: "post",
                        url: "saleHistory.php",
                        data:  "id=" + id,
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
                    if(invoice_global == 1){
                        $.ajax({
                            method: "post",
                            url: "registeredSalesToQB.php",
                            data:  "id=" + id + "&access_token="+ access_token + "&refresh_token=" + refresh_token + "&realm_id=" + realm_id,
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

                    else {  
                        $.ajax({
                            method: "post",
                            url: "unregisteredSalesToQB.php",
                            data: "id=" + id + "&access_token="+ access_token + "&refresh_token=" + refresh_token + "&realm_id=" + realm_id,
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
            }
        }

        function sendEmail(tblContent,history) {
            var tbl = document.createElement("table");
            tbl.innerHTML = tblContent;

            if (tbl.getElementsByTagName("tbody")[0].innerHTML == ""){
                alert("No Integration were successful!");
                return;
            }
            var th = tbl.getElementsByTagName("th");
            var td = tbl.getElementsByTagName("td");
            for (let i = 0; i < th.length; i++) {
                th[i].setAttribute("style","border:solid 1px #ccc; text-align:center; padding: 15px 40px;");  
            }
            for (let i = 0; i < td.length; i++) {
                td[i].setAttribute("style","border:solid 1px #ccc; text-align:center; padding: 15px 40px;");
            }

            if(history){
                var subj = "Small Builders Sales successfully moved to Small Builders History";
                var desc = "You have successfully automated your Payment Claim details into your Small Builders History.";
                
                $.ajax({
                    method: "post",
                    url: "../sendMail.php",
                    data: "tblcontent=" + tbl.innerHTML + "&subj="+ subj + "&desc=" + desc,
                    success: function (data) {

                        var body = document.getElementsByTagName("body")[0];
                        body.innerHTML = `<div class="mt-5 card col-md-8 offset-2" style='background: #FCFCFC; padding: 20px 20px 20px 20px;'>
                            <p style='color: green'>Success! A copy of your submission has been emailed to you.</p>
                        </div>`;

                        setTimeout(function() {window.location.href = "/quickbooks-integration/Sales/sales.php"},2000);
                    }
                });
            }

            else {
                var subj = "Small Builders Sales successfully added to Quickbooks Sales";
                var desc = "You have successfully automated your Payment Claim details into your Quickbooks Account. This claim is now available in your Quickbooks Sales with the following details.";
                
                $.ajax({
                    method: "post",
                    url: "../sendMail.php",
                    data: "tblcontent=" + tbl.innerHTML + "&subj="+ subj + "&desc=" + desc,
                    success: function (data) {
                        var body = document.getElementsByTagName("body")[0];
                        body.innerHTML = `<div class="mt-5 card col-md-8 offset-2" style='background: #FCFCFC; padding: 20px 20px 20px 20px;'>
                            <p style='color: green'>Success! A copy of your submission has been emailed to you.</p>
                            
                            <table class='table table-striped'>`+tblContent+`</table>
                            
                            <br>
                            <div class='text-center'>
                                <a href='sales.php' class='btn btn-secondary' style='width: 200px;'>Back to Integration</a>
                            </div>
                        </div>`;
                    }
                });
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

        function history(){
            $("#btnIntegrate").hide();
            $("#btnHistory").show();
        }
    </script>
</html>