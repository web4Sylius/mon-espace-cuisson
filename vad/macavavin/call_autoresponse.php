<?php

ini_set("display_errors","on");

$rootpath="/var/www/vhosts/ma-cave-a-vin.fr/httpdocs/";
$vadpath="/var/www/vhosts/ma-cave-a-vin.fr/vad/";
$test="";
if(strpos($_SERVER['SERVER_NAME'], 'test')!==false){
    $vadpath="/home/ma-cave-a-vin-2015/vad/macavavin/";
    $rootpath="/home/ma-cave-a-vin-2015/";
    $test="test";
}

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
require_once($rootpath."class/fonctions.php");
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

$secure_infos=array(
"3D_FAILURE"=>"Le commerçant et le porteur de la carte sont inscrits au programme 3-D Secure mais l'acheteur n'a pas réussi à s'authentifier (mauvais mot de passe).",
"3D_ERROR"=>"Le commerçant participe au programme 3-D Secure mais le serveur e-transactions a rencontré un problème technique durant le processus d'authentification (lors de la vérification de l'inscription de la carte au programme 3-D Secure ou de l'authentification du porteur).",
"3D_NOTENROLLED"=>"Le commerçant participe au programme 3-D Secure mais la carte du porteur n'est pas enrôlée.",
"3D_ATTEMPT"=>"Le commerçant et le porteur de la carte sont inscrits au programme 3-D Secure mais l'acheteur n'a pas eu à s'authentifier (le serveur de contrôle d'accès de la banque qui a émis la carte n'implémente que la génération d'une preuve de tentative d'authentification).",
"SSL"=>"L'acheteur ne s'est pas authentifié pour une des raisons suivantes : le type de carte n'est pas supporté par le programme 3-D Secure ou le commerçant ou le porteur de la carte n'est pas inscrit au programme 3-D Secure");

$message="message=".$_POST['DATA'];
$pathfile="pathfile=".$vadpath."param/pathfile".$test;
$path_bin = $vadpath."bin/response";
$result=exec("$path_bin $message $pathfile");

$tableau = explode ("!", $result);
$code = $tableau[1];
$error = $tableau[2];
$merchant_id = $tableau[3];
$merchant_country = $tableau[4];
$amount = $tableau[5];
$transaction_id = $tableau[6];
$payment_means = $tableau[7];
$transmission_date= $tableau[8];
$payment_time = $tableau[9];
$payment_date = $tableau[10];
$response_code = $tableau[11];
$payment_certificate = $tableau[12];
$authorisation_id = $tableau[13];
$currency_code = $tableau[14];
$card_number = $tableau[15];
$cvv_flag = $tableau[16];
$cvv_response_code = $tableau[17];
$bank_response_code = $tableau[18];
$complementary_code = $tableau[19];
$complementary_info= $tableau[20];
$return_context = $tableau[21];
$caddie = $tableau[22];
$receipt_complement = $tableau[23];
$merchant_language = $tableau[24];
$language = $tableau[25];
$customer_id = $tableau[26];
$order_id = $tableau[27];
$customer_email = $tableau[28];
$customer_ip_address = $tableau[29];
$capture_day = $tableau[30];
$capture_mode = $tableau[31];
$data = $tableau[32];
$order_validity = $tableau[33];
$transaction_condition = $tableau[34];
$statement_reference = $tableau[35];
$card_validity = $tableau[36];
$score_value = $tableau[37];
$score_color = $tableau[38];
$score_info = $tableau[39];
$score_threshold = $tableau[40];
$score_profile = $tableau[41];

$facture = new facture($DB_site, $transaction_id);

if ((($code == "") && ($error == "")) || ($code != 0) || $response_code != "00" || $bank_response_code != "00")
	{
	//ERREUR
	$DB_site->query("UPDATE facture SET valide = '3',moyenid=1 WHERE facturid = '".$transaction_id."'");
	
	
	$message="Numero de commande : $transaction_id
		<br>Montant : ".$facture->totalttc."&euro;
		<br>Client : ".$facture->utilisateur->userid." - ".$facture->utilisateur->prenom." ".$facture->utilisateur->nom.($facture->raison_sociale!=""?" société ".$facture->utilisateur->raison_sociale:"")."
		<br><br>erreur de reponse du serveur rmercanet
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
	$htmlmess .= "Une <b>commande par CB</b> a été initialisée mais n a pas pu aboutir (refus carte bleue)<br><br>".$message;
	$subject="Tentative de commande par CB échouée";
// 	mail($email_marchand, $subject, stripslashes($htmlmess));

	$DB_site->query("INSERT INTO suivi_commande SET facturid=".intval($transaction_id).", date=".time().", suivi='".mysqli_real_escape_string($DB_site->link_id,$htmlmess)."'");
	
	$template=new template("FR",$DB_site);
	
	$mail=new email();
	$mail->expediteur   = $template->translate("/site/mail_expediteur");
	$mail->expname      = $template->translate("/site/nom");
	$mail->destinataire = $template->translate("/site/base/mail_com");
	$mail->to           = "pr@mon-espace-cuisson.com";
	$mail->destname     = $template->translate("/site/nom");
	$mail->subject      = $subject;
	$mail->body         = $htmlmess;
	$mail->sendmail();

     } else {
	
          //OK
          $moyenid = 1;
	
          if($payment_means=="AMEX") $moyenid = 13;
          
          
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
          $template->parts["contact_telephone"]=$template->translate("/site/[site_code]/telephone");
          
          mailconfirmation(1,$facture,$user_info);
          
          $diss = new dissociation($DB_site,$transaction_id);

          //Si commande à l'étranger et qu'un article n'a pas de code douanier ni d'origine enregistrée
          $facture = new facture($DB_site,$facture->facturid); //on recréé la variable pour prendre les lignes dissociées
          verif_export($facture,$template);
          
          if($transaction_condition!="3D_SUCCESS"){
	      	$htmlmess = "Bonjour,<br>";
		$htmlmess .= "la commande <a href=\"https://www.ma-cave-a-vin.fr/admin/facture.php?action=editer&facturid=".$facture->facturid."\">#CDE".$facture->facturid."#</a> a été validé par CB mais SANS garantie de paiement :
		<br><br>Client : ".$facture->utilisateur->userid." - ".$facture->utilisateur->prenom." ".$facture->utilisateur->nom.($facture->raison_sociale!=""?" société ".$facture->utilisateur->raison_sociale:"")." ".$facture->utilisateur->ville." ".$facture->utilisateur->pays."
		<br>Infos carte : ".$payment_means." ".$complementary_info."
		<br>3D Secure : ".$transaction_condition." 
		<br>".$secure_infos[$transaction_condition];
		$subject="Paiement par CB sans 3D secure #CDE".$facture->facturid."#";

		$DB_site->query("INSERT INTO suivi_commande SET facturid=".intval($transaction_id).", date=".time().", suivi='".mysqli_real_escape_string($DB_site->link_id,$htmlmess)."'");
		
		$mail=new email();
		$mail->expediteur   = $template->translate("/site/mail_expediteur");
		$mail->expname      = $template->translate("/site/nom");
		$mail->destinataire = MAIL_DIRECTION;
		$mail->to           = array(MAIL_COMMERCE2);
		$mail->destname     = $template->translate("/site/nom");
		$mail->subject      = $subject;
		$mail->body         = $htmlmess;
		$mail->sendmail();
          }
     }
 
$logfile=$vadpath."autoresponse.log";
$fp=fopen($logfile, "a");
fwrite($fp, "$result\n\n");
fclose ($fp);


?>
