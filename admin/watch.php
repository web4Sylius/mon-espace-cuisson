<?php
include_once("host_config_admin.php");
include_once($rootpath."configurations/".$host."/config_mail.php");
include($rootpath."class/email.php");

$acces=$DB_site2->query("SELECT * FROM acces_admin WHERE connecte=1 and disconnect=0 AND watch=0");

while($user = $DB_site2->fetch_array($acces)){
	if($user['timestamp']<time()-2400){
		//envoi mail
		$message="Ce message est envoyé suite à une déconnexion liée à une inactivité de plus de 40 minutes sur l'admin de ".SITE_URL." :<br>";
		$message.="Identifiant :".$user['user']."<br>ID de connexion : ".$user['id'];
		$mail=new email();
	    $mail->destinataire=MAIL_DIRECTION;
	    $mail->copymode="bcc";
	    $mail->to=array(MAIL_DEV2);
	    $mail->expediteur="contact@mon-espace-cuisson.com";
	    $mail->subject="Notification de déconnexion à l'admin de ".SITE_URL;
	    $mail->body=$message;
	    $result=$mail->sendmail();

	    $DB_site2->query('UPDATE acces_admin SET disconnect = 1, watch=1 WHERE id ='.$user['id']);
	}
}