<?php
include("../../configurations/www.ma-cave-a-vin.fr/config_mysql.php");
include("../../configurations/www.ma-cave-a-vin.fr/config.php");
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
$host = "www.ma-cave-a-vin.fr" ;
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

/*$message="message=$DATA";
$pathfile="pathfile=/home/macavavin/www/VAD/macavavin/param/pathfile";
$path_bin = "/home/macavavin/www/VAD/macavavin/bin/response";
$result=exec("$path_bin $message $pathfile");

$tableau = explode ("!", $result);
$code = $tableau[1];
$error = $tableau[2];
$merchant_id = $tableau[3];
$merchant_country = $tableau[4];
$amount = $tableau[5];*/
/************************************/
/*$transaction_id = $tableau[6];
$payment_means = $tableau[7];
$transmission_date= $tableau[8];
$payment_time = $tableau[9];
$payment_date = $tableau[10];*/
/************************************/
/*$response_code = $tableau[11];
$payment_certificate = $tableau[12];
$authorisation_id = $tableau[13];
$currency_code = $tableau[14];
$card_number = $tableau[15];
$cvv_flag = $tableau[16];
$cvv_response_code = $tableau[17];*/
/***********************************/
/*$bank_response_code = $tableau[18];
$complementary_code = $tableau[19];
$return_context = $tableau[20];
$caddie = $tableau[21];
$receipt_complement = $tableau[22];
$merchant_language = $tableau[23];
$language = $tableau[24];
$customer_id = $tableau[25];
$order_id = $tableau[26];
$customer_email = $tableau[27];
$customer_ip_address = $tableau[28];
$capture_day = $tableau[29];
$capture_mode = $tableau[30];
$data = $tableau[31];*/

/*
if ((($code == "") && ($error == "")) || ($code != 0) || $response_code != "00" || $bank_response_code != "00")
	{
	//ERREUR
	$DB_site->query("UPDATE facture SET valide = '3' WHERE facturid = '".$transaction_id."'");
	$message="
		erreur de reponse du serveur rmercanet sur la transaction  $transaction_id
		<br><br>
		code : $code
		<br><br>
		error : $error
		<br><br>
		response_code : $response_code
		<br><br>
		bank_response_code : $bank_response_code
	";
	$htmlmess = "Bonjour,<br>";
	$htmlmess .= "Une <b>commande par CB</b> a été initialisée mais n a pas pu aboutir (refus carte bleue)<br>Numero de facture : $transaction_id<br>";
	$htmlmess .= "Vous pouvez vous connecter à votre espace d'administration arobases pour plus d'infos sur votre client : vous y trouverez ses coordonnées.<br>";
	$subject="Tentative de commande par CB échouée";
	email($email_marchand, $subject, stripslashes($htmlmess), $email_marchand);
	} 
else 
	{
	//OK
	$DB_site->query(" UPDATE facture SET valide='1' WHERE facturid='".$transaction_id."' ");
	$htmlmess = "Bonjour,<br>";
	$htmlmess .= "Vous avez reçu une nouvelle <b>commande par CB</b><br>Numero de facture : $transaction_id<br>";
	$htmlmess .= "Connectez vous a votre espace d'administration arobases pour plus d informations.<br>";
	$htmlmess .= "Cette transaction est déjà notée <font color=blue>Validée</font> Libre à vous de l'expedier ou de la refuser.<br>";
	$htmlmess .= "Si vous la refusez, n'oubliez pas de vous connecter à l'espace d'administration de votre banque et de renbourser votre client.";
	$subject="Reception d'une commande par CB";
	email($email_marchand, $subject, stripslashes($htmlmess), $email_marchand);	
	
	
	
	$client=$DB_site->query_first("select mail from facture WHERE facturid = '".$transaction_id."'");
	$htmlmess = "Bonjour,<br>";
	$htmlmess .= "Votre <b>commande par CB</b> sur $host a été validée par votre établissement bancaire.<br>
	Vous recevrez un e-mail dès qu'elle sera expédiée.
	<br>Cordialement,<br><br>
	L'équipe ma-cave-a-vin.fr.";
	$subject="Commande par CB validée sur ma-cave-a-vin.fr";
	email($client[0], $subject, stripslashes($htmlmess), $email_marchand);
	}*/
 
$logfile="/home/macavavin/www/VAD/macavavin/autoresponse2.log";
$fp=fopen($logfile, "a");
fwrite($fp, "$result\n\n");
fclose ($fp);

?>