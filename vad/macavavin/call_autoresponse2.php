<?php
include("../../configurations/www.mon-espace-cuisson.com/config_mysql.php");
include("../../configurations/www.mon-espace-cuisson.com/config.php");
include("../../mysql.php");
$DB_site=new DB_Sql;
$DB_site->appname='boutique';
$DB_site->appshortname='boutique';
$DB_site->database=$DBname;
$DB_site->server=$db_server;
$DB_site->user=$DBusername;
$DB_site->password=$DBpassword;
$DB_site->connect();
$DBpassword="";
$host = "www.mon-espace-cuisson.com" ;
require_once("../email.php");

if (isset($_GET['token']) && $_GET['token']!="" && isset ($_GET['PayerID']) && $_GET['PayerID']!="" ){
	//debut boucherie
	$token=$_GET['token'];
	$payerid=$_GET['PayerID'];
	$paypal=$DB_site->query_first("SELECT * FROM paypal p, facture f WHERE p.token='$token' AND p.factureid=f.facturid ");
	$lien="https://api-3t.paypal.com/nvp?METHOD=DoExpressCheckoutPayment&TOKEN=".$token."&VERSION=".$versionpaypal."&PWD=".$passpaypal."&USER=".$userpaypal."&SIGNATURE=".$signaturepaypal."&PAYMENTACTION=Sale&PAYERID=".$payerid."&AMT=".$paypal[amt]."&CURRENCYCODE=EUR&INVNUM=".$paypal[factureid];
	$lien=file($lien);
	$tableau=explode("&",$lien[0]);
	$tableau[0]=urldecode($tableau[0]);
	$montoken=explode("=",$tableau[0]);
	//fin boucherie
	$transaction_id=$paypal[factureid];
	if ($montoken[1]==$token){ //paiement reussi et validé par paypal, on realise toutes les opérations nécessaires
		$DB_site->query(" UPDATE facture SET valide='1' WHERE facturid='".$transaction_id."' ");
		$htmlmess = "Bonjour,<br>";
		$htmlmess .= "Vous avez reçu une nouvelle <b>commande par Paypal</b><br>Numero de facture : $transaction_id<br>";
		$htmlmess .= "Connectez vous a votre espace d'administration arobases pour plus d informations.<br>";
		$htmlmess .= "Cette transaction est déjà notée <font color=blue>Validée</font> Libre à vous de l'expedier ou de la refuser.<br>";
		$htmlmess .= "Si vous la refusez, n'oubliez pas de vous connecter à l'espace d'administration de votre banque et de rembourser votre client.";
		$subject="Reception d'une commande par Paypal";
		email($email_marchand, $subject, stripslashes($htmlmess), $email_marchand);	
		$client=$DB_site->query_first("select mail from facture WHERE facturid = '".$transaction_id."'");
		$htmlmess = "Bonjour,<br>";
		$htmlmess .= "Votre <b>commande par Paypal</b> sur $host a été validée par votre établissement bancaire.<br>
		Vous recevrez un e-mail dès qu'elle sera expédiée.
		<br>Cordialement,<br><br>
		L'équipe ma-cave-a-vin.fr.";
		$subject="Commande par Paypal validée sur ma-cave-a-vin.fr";
		email($client[0], $subject, stripslashes($htmlmess), $email_marchand);
	}else{ //erreur
		$DB_site->query("UPDATE facture SET valide = '3' WHERE facturid = '".$transaction_id."'");
		$message="erreur de reponse du serveur sur la transaction  $transaction_id";
		$htmlmess = "Bonjour,<br>";
		$htmlmess .= "Une <b>commande par Paypal</b> a été initialisée mais n a pas pu aboutir <br>Numero de facture : $transaction_id<br>";
		$htmlmess .= "Vous pouvez vous connecter à votre espace d'administration arobases pour plus d'infos sur votre client : vous y trouverez ses coordonnées.<br>";
		$subject="Tentative de commande par Paypal échouée";
		email($email_marchand, $subject, stripslashes($htmlmess), $email_marchand);
	}
}
header('Location: /index.php?action=delpanier_complet');


/*$logfile="/home/macavavin/www/VAD/macavavin/autoresponse2.log";
$fp=fopen($logfile, "a");
fwrite($fp, "$result\n\n");
fclose ($fp);*/

?>