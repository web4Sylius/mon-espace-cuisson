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

$template=new template("FR",$DB_site);

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
     
     $code               = 0;
     $response_code      = "00";
     $bank_response_code = "00";
     $error              = 0;
     $transaction_id     = '0100';
}

$reglement_id = substr($transaction_id, 1);
$sql = "SELECT *";
$sql .= " FROM reglement";
$sql .= " WHERE reglement_id = ".$reglement_id;
$tabReglement = $DB_site->query_first($sql);
$facturid = $tabReglement['facturid'];

if(($code != 0 && $error == '') || $code != 0 || $response_code != "00" || $bank_response_code != "00"){ //Paiement refusé

     //Code paiement refusé
     
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
     
     $sql = "SELECT libelleFR AS libelle";
     $sql .= " FROM moyenpaiement";
     $sql .= " WHERE moyenid = ".$tabReglement['reglement_moyen'];
     $moyenpaiement = $DB_site->query_first($sql);

     $sql = "SELECT *";
     $sql .= " FROM emails_clients";
     $sql .= " WHERE emailid = 138";
     $tabEmail = $DB_site->query_first($sql);
     
     $sql = "SELECT u.*";
     $sql .= " FROM facture f, utilisateur u";
     $sql .= " WHERE f.userid = u.userid";
     $sql .= " AND f.facturid = ".$facturid;
     $tabUser = $DB_site->query_first($sql);
     
     $sql = "SELECT COUNT(*) as nb";
     $sql .= " FROM reglement";
     $sql .= " WHERE facturid = ".$facturid;
     $tabNbReg = $DB_site->query_first($sql);
     
     $fcl = new facture($DB_site,$facturid);
     
     $sujet   = $tabEmail['sujetFR'];
     $sujet   = str_replace('%FACTURID%',$facturid,$sujet);
     $sujet = str_replace('%PARTIEL%',($tabNbReg['nb'] > 1 ? 'partiel' : ''),$sujet);
     $contenu = $tabEmail['contenuFR'];

     $contenu = str_replace('%PRENOM%',$tabUser['prenom'],$contenu);
     $contenu = str_replace('%NOM%',$tabUser['nom'],$contenu);
     $contenu = str_replace('%MONTANT%',number_format($tabReglement['reglement_montant'],2,',',' ').' ¤',$contenu);
     $contenu = str_replace('%REGLEMENT_ID%',$tabReglement['reglement_id'],$contenu);
     $contenu = str_replace('%FACTURID%',$facturid,$contenu);
     $contenu = str_replace('%DATE%',date("d/m/Y"),$contenu);
     $contenu = str_replace('%MOYEN%',$moyenpaiement['libelle'],$contenu);
     $contenu = str_replace('%PARTIEL%',($tabNbReg['nb'] > 1 ? 'partiel' : ''),$contenu);
     
     $sitepro = ($fcl->utilisateur->raisonsociale!="" && $template->site_code()=="base");
          
     $template->parts["pro"]     = ($sitepro?"_pro":"");
     $template->parts["urlsite"] = str_replace("http://","",$template->translate("/site/[site_code]/url"));

     if($sitepro) $template->parts["urlsite"] = str_replace("www","pro",$template->parts["urlsite"]);

     $template->parts["nom_utilisateur"] = $fcl->utilisateur->prenom." ".$fcl->utilisateur->nom;
     $template->parts["msg"]             = $contenu;
     $template->parts["subject"]         = $sujet;

     $template->evaltemplate("Mailconfirmation_reglement","htmlmess");

     $body=stripslashes($template->parts["htmlmess"]);
     $path=pathinfo($_SERVER['SCRIPT_NAME'],PATHINFO_DIRNAME);
     if($path!="/"){
         $nbpar=substr_count($path,"/");
         $parentpath=str_repeat("../",$nbpar);
         $body=preg_replace('/< *img([^>]*)src *= *["]?([^"]*)"/i','<img$1src="'.$parentpath.'$2"',$body);
     }
     
     $mail = new email();

     $mail->expediteur   = $template->translate("/site/mail_expediteur");
     $mail->expname      = $template->translate("/site/nom");
     $mail->destinataire = $tabUser['mail'];
     $mail->copymode     = "bcc";
     $mail->to           = 'contact@ma-cave-a-vin.fr,cecile@ma-cave-a-vin.fr';
     $mail->subject      = $sujet;
     $mail->body         = $body;
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

$logfile="/var/www/vhosts/ma-cave-a-vin.fr/httpdocs/vad/macavavin/autoresponse_bis.log";
$fp=fopen($logfile, "a");
fwrite($fp, "$result\n\n");
fclose ($fp);