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
                            <th>Account type</th>
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
                                            <td></td>
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
                                            <td></td>
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
        }

        function checkBox(id) {    
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

        function integrateSales() {
            var invoice_global = "<?php echo $selected_invoice ?>";
            console.log(invoice_global,"THE SELECTED");
            $.confirm({
                title: "Smallbuilders to Quickbooks",
                columnClass: "medium",
                theme: "modern",
                content: "",
                onOpenBefore: function () {
                    this.showLoading();
                    
                    var confirmJS = this;
                    var integrateCheck = document.querySelectorAll('.integrateCheck:checked');
                    
                    var sales = [];

                    for (let i = 0; i < integrateCheck.length; i++) {
                        var id = integrateCheck[i].value;
                    
                        $.ajax({
                            method: "post",
                            url: "readSales(SBid).php",
                            dataType: "json",
                            data: "id=" + id + "&access_token="+ access_token + "&refresh_token=" + refresh_token + "&realm_id=" + realm_id,
                            success: function (data) {
                                sales.push(data);
                                console.log("AJAX FOR getting data from DB",sales);

                                if(i == integrateCheck.length - 1) {
                                    SalestoQB (sales,confirmJS,invoice_global); 
                                }
                            }
                        });
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
        
        function SalestoQB (sales,confirmJS,type) {
            console.log(sales.length);
            
            for (let i = 0; i < sales.length; i++) {
                
                var frmSales = document.createElement("form");

                try {
                    var deposit = convertNulltoEmpty(sales[i][0].deposit);
                } catch (error) {
                    var deposit = "";
                }

                try {
                    var project_type = convertNulltoEmpty(sales[i][0].project_type);
                } catch (error) {
                    var project_type = "";
                }

                try {
                    var project_name = convertNulltoEmpty(sales[i][0].project_name);
                } catch (error) {
                    var project_name = "";
                }

                try {
                    var id = convertNulltoEmpty(sales[i][0].id);
                } catch (error) {
                    var id = "";
                }

                try {
                    var due_date = convertNulltoEmpty(sales[i][0].due_date);
                } catch (error) {
                    var due_date = "";
                }

                try {
                    var invoice_date = convertNulltoEmpty(sales[i][0].invoice_date);
                } catch (error) {
                    var invoice_date = "";
                }

                try {
                    var invoice_no = convertNulltoEmpty(sales[i][0].invoice_no);
                } catch (error) {
                    var invoice_no = "";
                }

                try {
                    var customer_id = convertNulltoEmpty(sales[i][0].customer_id);
                } catch (error) {
                    var customer_id = "";
                }

                try {
                    var total_amount = convertNulltoEmpty(sales[i][0].total_amount);
                } catch (error) {
                    var total_amount = "";
                }

                frmSales.innerHTML = "<input name='total_amount' value='"+total_amount+"'><input name='deposit' value='"+deposit+"'><input name='project_name' value='"+project_name+"'><input name='project_type' value='"+project_type+"'><input name='id' value='"+id+"'><input name='invoice_date' value='"+invoice_date+"'><input name='due_date' value='"+due_date+"'><input name='invoice_no' value='"+invoice_no+"'><input name='customer_id' value='"+customer_id+"'>";
                
                if (type == 2){
                    $.ajax({
                        method: "post",
                        url: "unregisteredSalesToQB.php",
                        data: $(frmSales).serialize() +"&access_token="+ access_token + "&refresh_token=" + refresh_token + "&realm_id=" + realm_id,
                        success: function (data) {
                            console.log("SB to QB",data);

                            if(i == sales.length - 1) {
                                confirmJS.hideLoading();
                                confirmJS.setContent("Done");
                                confirmJS.buttons.ok.enable();
                            }
                        },
                    });
                }
                
                else {
                    $.ajax({
                        method: "post",
                        url: "registeredSalesToQB.php",
                        data: $(frmSales).serialize() +"&access_token="+ access_token + "&refresh_token=" + refresh_token + "&realm_id=" + realm_id,
                        success: function (data) {
                            console.log("SB to QB",data);
                            console.log("REGISTRE");

                            if(i == sales.length - 1) {
                                confirmJS.hideLoading();
                                confirmJS.setContent("Done");
                                confirmJS.buttons.ok.enable();
                            }
                        }
                    });
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