<?php

ini_set("display_errors","on");

$rootpath="/var/www/vhosts/ma-cave-a-vin.fr/httpdocs/";
// $rootpath="/home/ma-cave-a-vin-2015/";

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
require_once($rootpath."class/email_client.php");
include_once($rootpath."mailconfirmation.php");
include($rootpath."admin/dissociation.php");

if(!isset($_GET['test'])){

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

} else {
     
     $code               = 1;
     $response_code      = "00";
     $bank_response_code = "00";
     $error              = 0;
     $transaction_id     = '073';
}

$reglement_id = substr($transaction_id, 1);
$sql = "SELECT *";
$sql .= " FROM reglement";
$sql .= " WHERE reglement_id = ".$reglement_id;
$tabReglement = $DB_site->query_first($sql);
$facturid = $tabReglement['facturid'];

$fcl = new facture($DB_site, $facturid);

$template = new template($fcl->utilisateur->langue, $DB_site);

if(($code != 0 && $error == '') || $code != 0 || $response_code != "00" || $bank_response_code != "00"){ //Paiement refusé
	
	$amount = $amount / 100;
	$amount = str_replace('.',',',$amount);
	
     //Code paiement refusé
     	$message="Numero de commande : $facturid
		<br>Montant : ".$amount."&euro;
		<br>Client : ".$fcl->utilisateur->userid." - ".$fcl->utilisateur->prenom." ".$fcl->utilisateur->nom.($fcl->raison_sociale!=""?" société ".$fcl->utilisateur->raison_sociale:"")."
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

	$DB_site->query("INSERT INTO suivi_commande SET facturid=".intval($facturid).", date=".time().", suivi='".mysql_real_escape_string($htmlmess)."'");
	
	$template=new template("FR",$DB_site);
	
	$mail=new email();
	$mail->expediteur=$template->translate("/site/mail_expediteur");
	$mail->expname=$template->translate("/site/nom");
	$mail->destinataire=$template->translate("/site/mail_commercial");
	$mail->destname=$template->translate("/site/nom");
	$mail->subject=$subject;
	$mail->body=$htmlmess;
	$mail->sendmail();
     
} else { //Paiement valide
     
     $sql = "UPDATE reglement SET";
     $sql .= " reglement_statut_id = 1";
     $sql .= ", reglement_date     = '".$tabReglement['reglement_date']."'";
     //$sql .= ", reglement_moyen  = '".intval($moyen_id)."'";
     $sql .= ", reglement_montant  = '".$tabReglement['reglement_montant']."'";
     $sql .= " WHERE reglement_id = ".$tabReglement['reglement_id'];
     $DB_site->query($sql);
     
     $sql = "INSERT INTO reglement_historique (";
     $sql .= " reglement_reception_id";
     $sql .= ", reglement_id";
     $sql .= ", reglement_reception_date";
     $sql .= ", reglement_historique_date";
     $sql .= ", reglement_historique_username";
     $sql .= ") VALUES (";
     $sql .= 3;
     $sql .= ", '".$tabReglement['reglement_id']."'";
     $sql .= ", '".$tabReglement['reglement_date']."'";
     $sql .= ", NOW()";
     $sql .= ", '".$_COOKIE['admin_username']."'";
     $sql .= ")";
     $DB_site->query($sql);
     
     $sql = "SELECT libelle".$template->langue." AS libelle";
     $sql .= " FROM moyenpaiement";
     $sql .= " WHERE moyenid = ".$tabReglement['reglement_moyen'];
     $moyenpaiement = $DB_site->query_first($sql);

     
     $sql = "SELECT u.*";
     $sql .= " FROM facture f, utilisateur u";
     $sql .= " WHERE f.userid = u.userid";
     $sql .= " AND f.facturid = ".$facturid;
     $tabUser = $DB_site->query_first($sql);
     
     $sql = "SELECT COUNT(*) as nb";
     $sql .= " FROM reglement";
     $sql .= " WHERE facturid = ".$facturid;
     $tabNbReg = $DB_site->query_first($sql);
     
     $vars=array(
     "prenom"=>$tabUser['prenom'],
     "nom"=>$tabUser['nom'],
     "montant"=>$template->price_format($tabReglement['reglement_montant']),
     "facturid"=>$facturid,
     "reglement_id"=>$tabReglement['reglement_id'],
     "date"=>date("d/m/Y"),
     "moyen"=>$moyenpaiement['libelle'],
     "partel"=>($tabNbReg['nb'] > 1 ? 'partiel' : ''),
     "nom_utilisateur"=>$fcl->utilisateur->prenom." ".$fcl->utilisateur->nom);
     
     $template->pro=$sitepro = ($fcl->utilisateur->raisonsociale!="" && $template->site_code()=="base");
     
     $ec = new email_client($DB_site,$template,138,null,$vars);
     
     $mail = new email();

     $mail->expediteur   = $template->translate("/site/mail_expediteur");
     $mail->expname      = $template->translate("/site/nom");
     $mail->destinataire = $tabUser['mail'];
     $mail->copymode     = "bcc";
     $mail->to           = 'contact@ma-cave-a-vin.fr,cecile@ma-cave-a-vin.fr';
     $mail->subject      = $ec->subject;
     $mail->body         = $ec->body;
     $mail->sendmail();

     //Insertion de l'email dans le suivi
     $sql = "INSERT INTO mail_outbox SET";
     $sql .= " suivitype    = 'suivi_commande'";
     $sql .= ", suiviid     = ".$facturid."";
     $sql .= ", date        = ".time();
     $sql .= ", `to`        = '".$tabUser['mail']."'";
     $sql .= ", utilisateur = '".$_COOKIE['admin_username']."'";
     $sql .= ", subject     = '".mysql_real_escape_string($mail->subject)."'";
     $sql .= ", body        = '".mysql_real_escape_string($mail->body)."'";
     $DB_site->query($sql);
}

$logfile=$rootpath."/vad/macavavin/autoresponse_bis.log";
$fp=fopen($logfile, "a");
fwrite($fp, "$result\n\n");
fclose ($fp);