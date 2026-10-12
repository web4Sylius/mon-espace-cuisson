<?php
require_once __DIR__.'/../../secrets.php';

ini_set("display_errors","1");
error_reporting(E_ALL);
ini_set("memory_limit","1024M");

// $rootpath="/var/www/vhosts/ma-cave-a-vin.fr/httpdocs/";
// $rootpath="/home/ma-cave-a-vin-2015/";
// $rootpath = "D:/wamp/www/ma-cave-a-vin/";
$rootpath="../../";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

//Load composer's autoloader
require $rootpath.'class/phpmailer/PHPMailer.php';
require $rootpath.'class/phpmailer/SMTP.php';
require $rootpath.'class/phpmailer/Exception.php';



$mail = new PHPMailer(true);                              // Passing `true` enables exceptions
try {
    //Server settings
    $mail->SMTPDebug = 2;                                 // Enable verbose debug output
    $mail->isSMTP();                                      // Set mailer to use SMTP
    $mail->Host = 'mail.ma-cave-a-vin.fr';  // Specify main and backup SMTP servers (';' separated)
    $mail->SMTPAuth = true;                               // Enable SMTP authentication
    $mail->Username = SMTP_BENOIT_USER;                 // SMTP username
    $mail->Password = SMTP_PASSWORD;                           // SMTP password
    $mail->SMTPSecure = 'tls';                            // Enable TLS encryption, `ssl` also accepted
    $mail->Port = 25;                                    // TCP port to connect to

    //Recipients
    $mail->setFrom('webmaster2@ma-cave-a-vin.fr', 'Ma cave a vin');
    $mail->addAddress('webmaster1@ma-cave-a-vin.fr', 'Joe User');     // Add a recipient
    // $mail->addAddress('ellen@example.com');               // Name is optional
    // $mail->addReplyTo('info@example.com', 'Information');
    // $mail->addCC('cc@example.com');
    // $mail->addBCC('bcc@example.com');

    //Attachments
    // $mail->addAttachment('/var/tmp/file.tar.gz');         // Add attachments
    // $mail->addAttachment('/tmp/image.jpg', 'new.jpg');    // Optional name

    //Content
    $mail->isHTML(true);                                  // Set email format to HTML
    $mail->Subject = 'Here is the subject';
    $mail->Body    = 'This is the HTML message body <b>in bold!</b>';
    $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

    $mail->send();
    echo 'Message has been sent';
} catch (Exception $e) {
    echo 'Message could not be sent.';
    echo 'Mailer Error: ' . $mail->ErrorInfo;
}


?>