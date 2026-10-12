<?php

require "admin_global.php";
setcookie("admin_username","",time()+$cookietime,"/",$cookie_domain);
setcookie("admin_passcode","",time()+$cookietime,"/",$cookie_domain);

$acces=$DB_site2->query_first("SELECT * FROM acces_admin WHERE user='".$admin_username."' AND connecte=1 ORDER BY timestamp DESC LIMIT 0,1");
if($acces['timestamp']>0){
	// MAIL deco
	$message="Ce message est envoyé suite à une déconnexion de l'admin de ".SITE_URL." :<br>";
	$message.="Identifiant :".$acces['user']."<br>ID de connexion : ".$acces['id'];
	$mail=new email();
    $mail->destinataire=MAIL_DIRECTION;
    $mail->copymode="bcc";
    $mail->to=array(MAIL_DEV2);
    $mail->expediteur="contact@mon-espace-cuisson.com";
    $mail->subject="Notification de déconnexion à l'admin de ".SITE_URL;
    $mail->body=$message;
    $result=$mail->sendmail();
    $DB_site2->query('UPDATE acces_admin SET disconnect = 1, watch=1 WHERE id ='.$acces['id']);
}

sleep(1);

header("Location: http://".$_SERVER['HTTP_HOST']."/admin");

?>