<?php
// Import PHPMailer classes into the global namespace
// These must be at the top of your script, not inside a function
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

//Load Composer's autoloader
require 'vendor/autoload.php';
require_once "db_connect.php";

$mail = new PHPMailer(true);                              // Passing `true` enables exceptions
$tblcontent = $_POST["tblcontent"];
$subj = $_POST["subj"];
$desc = $_POST["desc"];
session_start();
$client_id = $_SESSION["client_id"];
$sql = "SELECT * FROM users WHERE id=$client_id";

$query = $connect->query($sql);

while($row = mysqli_fetch_array($query)) {        
    try {
        //Server settings
        $mail->SMTPDebug = 2;                                 // Enable verbose debug output
        $mail->isSMTP();                                      // Set mailer to use SMTP
        $mail->Host = 'smtp.gmail.com';                       // Specify main and backup SMTP servers
        $mail->SMTPAuth = true;                               // Enable SMTP authentication
        $mail->Username = 'pupbsit.porwan@gmail.com';         // SMTP username
        $mail->Password = 'BSIT4-1@2019';                     // SMTP password
        $mail->SMTPSecure = 'tls';                            // Enable TLS encryption, `ssl` also accepted
        $mail->Port = 587;                                    // TCP port to connect to

        //Recipients
        $mail->setFrom('no-reply@smallbuilders.com.au', 'Small Builders');
        $mail->addAddress($row["email"], $row["name"]);     // Add a recipient
        //$mail->addAddress('ellen@example.com');               // Name is optional


        //Attachments
        //$mail->addAttachment('/var/tmp/file.tar.gz');         // Add attachments
        //$mail->addAttachment('/tmp/image.jpg', 'new.jpg');    // Optional name

        //Content
        $mail->isHTML(true);                                  // Set email format to HTML
        $mail->Subject = $subj;
        $mail->Body    = "<style>table,tr,th,td {border: 1px solid gray;}</style>Please do not reply. This is an automated email.<br></br> Hi ".$row["name"].",<br><br>$desc<br><br><table style='border-collapse: collapse;'>$tblcontent</table><br>If you have any questions, contact John Dela Cruz on 0414 325 080.<br><br>Regards,<br>Small Builders";
        //$mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

        $mail->send();
        echo 'Message has been sent';
    } catch (Exception $e) {
        echo 'Message could not be sent. Mailer Error: ', $mail->ErrorInfo;
    }
}