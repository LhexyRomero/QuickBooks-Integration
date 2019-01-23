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
</head>
<body>

<div class="container">
<br><br>
    <table id='apiHistory' class='table table-striped'>
            <thead>
                <tr>
                    <th>Operation</th>
                    <th>User</th>
                    <th>Timestamp</th>
                    <th>Request URI</th>
                    <th>Request Code</th>
                    <th>Method</th>
                    <th>Request Body</th>
                    <th>Error Code</th>
                </tr>
            </thead>
            <tbody>
                <?php
                    //GET RECONCILED CUSTOMER
                    require_once "db_connect.php";

                    $records = array();
                    $sql = "SELECT * FROM _api_history";

                    $query = $connect->query($sql);

                    while($row = mysqli_fetch_array($query)) {
                        echo "<tr>
                            <td><span class='badge badge-primary'>".$row["operation"]."</span></td>
                            <td>".$row["client_id"]."</td>
                            <td>".$row["timestamp"]."</td>
                            <td>".$row["request_uri"]."</td>";
                        if($row["request_code"] == 200) {
                            echo "<td style='color: green'>".$row["request_code"]."</td>";
                        }
                        else if ($row["request_code"] == 400) {
                            echo "<td style='color: red'>".$row["request_code"]."</td>";
                        }
                        echo "<td>".$row["method"]."</td>";
                        echo "<td><button class='btn btn-primary'>View</button></td>";
                        echo "<td><button class='btn btn-primary'>View</button></td>";
                        echo "</tr>";
                    }    
                ?>
            </tbody>
        </table>
        <script>
            $("#apiHistory").dataTable();
        </script>

</div>
</body>
</html>