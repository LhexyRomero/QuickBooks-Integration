<?php
    require_once "db_connect.php";

    //session_start();

    if(!empty($_POST)) {
        $username = $_POST['username'];
        $password = $_POST['password'];
    
        $sql = "SELECT * FROM users WHERE username = '$username' and password = '$password'";

        $query = $connect->query($sql);

        if(mysqli_num_rows($query) > 0) {
            while($row = mysqli_fetch_array($query)) {
                session_start();
                $_SESSION["client_id"] = $row["id"];
                header('Location:index.php');
            }
        }
        else {
            echo "<script>alert('Incorrect username or password');</script>";
        }
    }
?>
<html>
    <head>
        <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
    </head>
    <body class="container">
        <div class="mt-5 card offset-3 col-md-6">
            <div class='card-header text-center'>
                <h3>Login</h3>
            </div>
            <div class="card-body">
                <form action='login.php' method='post'>
                    <input class='form-control' placeholder="Username" name='username' required>
                    <input class='form-control mt-3' placeholder="Password" type='password' name='password' required>
                    <button class='btn btn-success btn-block mt-3'>Login</button>
                </form>
            </div>
        </div>
    </body>
</html>
