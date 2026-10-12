<?php

ini_set("display_errors","on");

$rootpath="/var/www/vhosts/ma-cave-a-vin.fr/httpdocs/";

include($rootpath."configurations/www.ma-cave-a-vin.fr/config_mysql.php");
include($rootpath."class/mysql.php");
include($rootpath."class/facture.class.php");
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
require_once($rootpath."class/email.php");
require_once($rootpath."class/devises.class.php");
require_once($rootpath."class/template.class.php");
require_once($rootpath."class/garantie.class.php");
require_once($rootpath."class/DAOgarantie.class.php");
require_once($rootpath."class/DAOarticle.class.php");
require_once($rootpath."class/lignefacturegarantie.class.php");
require_once($rootpath."class/DAOlignefacturegarantie.class.php");
require_once($rootpath."class/DAOpays.class.php");
require_once($rootpath."class/DAOfournisseur.class.php");
include_once($rootpath."mailconfirmation.php");
include($rootpath."admin/dissociation.php");

$message="message=".$_POST['DATA'];
$pathfile="pathfile=/var/www/vhosts/ma-cave-a-vin.fr/httpdocs/vad/macavavin/param/pathfile";
$path_bin = "/var/www/vhosts/ma-cave-a-vin.fr/httpdocs/vad/macavavin/bin/response";
$result=exec("$path_bin $message $pathfile");

$tableau = explode ("!", $result);
$code = $tableau[1];
$error = $tableau[2];
$merchant_id = $tableau[3];
$merchant_country = $tableau[4];
$amount = $tableau[5];
/************************************/$transaction_id = $tableau[6];
$payment_means = $tableau[7];
$transmission_date= $tableau[8];
$payment_time = $tableau[9];
$payment_date = $tableau[10];
/************************************/$response_code = $tableau[11];
$payment_certificate = $tableau[12];
$authorisation_id = $tableau[13];
$currency_code = $tableau[14];
$card_number = $tableau[15];
$cvv_flag = $tableau[16];
$cvv_response_code = $tableau[17];
/***********************************/$bank_response_code = $tableau[18];
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
$data = $tableau[31];


if ((($code == "") && ($error == "")) || ($code != 0) || $response_code != "00" || $bank_response_code != "00")
	{
	//ERREUR
	$DB_site->query("UPDATE facture SET valide = '3',moyenid=1 WHERE facturid = '".$transaction_id."'");
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
	$htmlmess .= "Une <b>commande par CB</b> a été initialisée mais n a pas pu aboutir (refus carte bleue)<br>Numero de commande : $transaction_id<br>".$message;
	$subject="Tentative de commande par CB échouée";
// 	mail($email_marchand, $subject, stripslashes($htmlmess));

	$DB_site->query("INSERT INTO suivi_commande SET facturid=".intval($transaction_id).", date=".time().", suivi='".mysql_real_escape_string($htmlmess)."'");
	
	$template=new template("FR",$DB_site);
	
	$mail=new email();
	$mail->expediteur=$template->translate("/site/mail_expediteur");
	$mail->expname=$template->translate("/site/nom");
	$mail->destinataire=$template->translate("/site/mail_commercial");
	$mail->destname=$template->translate("/site/nom");
	$mail->subject=$subject;
	$mail->body=$htmlmess;
	$mail->sendmail();

     } else {
	
          //OK
          $moyenid = 1;
	
          if($payment_means=="AMEX") $moyenid = 13;
          
          $facture = new facture($DB_site, $transaction_id);
          
          $params = array(
              'valide'  => 1,
              'moyenid' => $moyenid,
              'ventes'  => true
          );
          $facture->update($params);
	
          /*$htmlmess = "Bonjour,<br>";
          $htmlmess .= "Vous avez reçu une nouvelle <b>commande par CB</b><br>Numero de facture : $transaction_id<br>";
          $htmlmess .= "Connectez vous a votre espace d'administration arobases pour plus d informations.<br>";
          $htmlmess .= "Cette transaction est déjà notée <font color=blue>Validée</font> Libre à vous de l'expedier ou de la refuser.<br>";
          $htmlmess .= "Si vous la refusez, n'oubliez pas de vous connecter à l'espace d'administration de votre banque et de renbourser votre client.";
          $subject="Reception d'une commande par CB";*/
          //email($email_marchand, $subject, stripslashes($htmlmess), $email_marchand);	
	
          $sql = "SELECT *";
          $sql .= " FROM utilisateur";
          $sql .= " WHERE userid = ".$facture->utilisateur->userid;
          $user_info = $DB_site->query_first($sql);
          
          $template = new template($facture->utilisateur->langue,$DB_site);
          
          $template->path = $rootpath."templates/".$facture->utilisateur->langue;
          
          mailconfirmation(1,$facture,$user_info);
          
          $diss = new dissociation($DB_site,$transaction_id);

          //Si commande à l'étranger et qu'un article n'a pas de code douanier ni d'origine enregistrée
           $facture = new facture($DB_site,$facture->facturid); //on recréé la variable pour prendre les lignes dissociées
           verif_export($facture,$template);
     }
 
$logfile="/var/www/vhosts/ma-cave-a-vin.fr/httpdocs/vad/macavavin/autoresponse.log";
$fp=fopen($logfile, "a");
fwrite($fp, "$result\n\n");
fclose ($fp);


?>