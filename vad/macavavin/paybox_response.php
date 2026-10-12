<?php

ini_set("display_errors","on");

if(ESPACE != 'prod'){
    $rootpath="/var/www/vhosts/ma-cave-a-vin.fr/fusion.ma-cave-a-vin.fr/";
    $vadpath="/var/www/vhosts/ma-cave-a-vin.fr/fusion.ma-cave-a-vin.fr/vad/";
}else{
    $rootpath="/var/www/vhosts/mon-espace-cuisson.com/httpdocs/";
    $vadpath="/var/www/vhosts/mon-espace-cuisson.com/vad/";
}


$test="";

if(strpos($_SERVER['SERVER_NAME'], 'test')!==false){
    $rootpath=$_SERVER['DOCUMENT_ROOT']."/";
    $vadpath=$rootpath."vad/macavavin/";
    $test="test";
}
include_once($_SERVER['DOCUMENT_ROOT']."/class/host.php");
$host_conf = new Host();
$host=$host_conf->host;

include_once($_SERVER['DOCUMENT_ROOT']."/configurations/".$host_conf->path."/config_variables.php");
include($rootpath."configurations/www.mon-espace-cuisson.com/config_mysql.php");
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

//$host = "www.ma-cave-a-vin.fr" ;
require_once($rootpath."class/email.php");
require_once($rootpath."class/devises.class.php");
require_once($rootpath."class/fonctions.php");
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

$errors=array(
"00"=>"transaction approuvée ou traitée avec succès.",
"01"=>"Numéro de commerçant incorrect ou inconnu",
"02"=>"contacter l'émetteur de carte.",
"03"=>"commerçant invalide.",
"04"=>"conserver la carte.",
"05"=>"ne pas honorer.",
"07"=>"conserver la carte, conditions spéciales.",
"08"=>"approuver après identification du porteur.",
"12"=>"transaction invalide.",
"13"=>"montant invalide.",
"14"=>"numéro de porteur invalide.",
"15"=>"émetteur de carte inconnu.",
"17"=>"annulation client.",
"19"=>"répéter la transaction ultérieurement.",
"20"=>"réponse erronée (erreur dans le domaine serveur).",
"24"=>"mise à jour de fichier non supportée.",
"25"=>"impossible de localiser l'enregistrement dans le fichier.",
"26"=>"enregistrement dupliqué, ancien enregistrement remplacé.",
"27"=>"erreur en « edit » sur champ de mise à jour fichier.",
"28"=>"accès interdit au fichier.",
"29"=>"mise à jour de fichier impossible.",
"30"=>"erreur de format.",
"31"=>"identifiant de l'organisme acquéreur inconnu.",
"33"=>"date de validité de la carte dépassée.",
"34"=>"suspicion de fraude.",
"38"=>"nombre d'essais code confidentiel dépassé.",
"41"=>"carte perdue.",
"43"=>"carte volée.",
"51"=>"provision insuffisante ou crédit dépassé.",
"54"=>"date de validité de la carte dépassée.",
"55"=>"code confidentiel erroné.",
"56"=>"carte absente du fichier.",
"57"=>"transaction non permise à ce porteur.",
"58"=>"transaction interdite au terminal.",
"59"=>"suspicion de fraude.",
"60"=>"l'accepteur de carte doit contacter l'acquéreur.",
"61"=>"dépasse la limite du montant de retrait.",
"63"=>"règles de sécurité non respectées.",
"68"=>"réponse non parvenue ou reçue trop tard.",
"75"=>"nombre d'essais code confidentiel dépassé.",
"76"=>"porteur déjà en opposition,ancien enregistrement conservé.",
"90"=>"arrêt momentané du système.",
"91"=>"émetteur de cartes inaccessible.",
"94"=>"demande dupliquée.",
"96"=>"mauvais fonctionnement du système.",
"97"=>"échéance de la temporisation de surveillance globale.",
"98"=>"serveur inaccessible (positionné par le serveur).",
"99"=>"incident domaine initiateur.");

$infos3d=array(""=>"Inconnu",
"Y"=>"Porteur authentifié",
"A"=>"Authentification du porteur forcée par la banque de l’acheteur",
"U"=>"L’authentification du porteur n’a pas pu s’effectuer",
"N"=>"Porteur non authentifié");


$f=fopen("paybox.log","a");
fwrite($f,date("d/m/Y H:i:s").",".$_GET['facturid'].",".$_GET['amount'].",".$_GET['auto'].",".$_GET['trans'].",".$_GET['cbpays'].",".$_GET['bin6'].",".$_GET['error'].",".$_GET['auth3d'].",".$_GET['gar3d']."\n");
fclose($f);

$facturid=$_GET['facturid'];
if(preg_match("/(\d+)\-(\d+)/",$facturid,$m)){
    $facturid=$m[1];
    $reglement_id=$m[2];
}
$facture=new facture($DB_site,$facturid);

$template=new template($facture->utilisateur->langue,$DB_site);
// $template->path = $rootpath."templates_refonte/desktop/base/".$facture->utilisateur->langue;

if(isset($_GET['type']) && $_GET['type'] == 'AMEX'){
     $moyen_id = 13;
} else {
     $moyen_id = 1;
}

if($_GET['error']=="00000"){
     //Paiement valide
     
    if(is_null($reglement_id)){
        $sql = "UPDATE facture SET";
        $sql .= " valide = 1";
        $sql .= ", moyenid = ".$moyen_id;
        $sql .= ", transaction_id = '".$_GET['trans']."'";
        $sql .= " WHERE facturid = '".$facture->facturid."'";
        $DB_site->query($sql);
        
        mailconfirmation(1,$facture,null);
        $diss = new dissociation($DB_site,$facture->facturid);

        //Si commande à l'étranger et qu'un article n'a pas de code douanier ni d'origine enregistrée
       $facture = new facture($DB_site,$facture->facturid); //on recréé la variable pour prendre les lignes dissociées
       verif_export($facture,$template);
       
    }else{
        $sql = "SELECT *";
        $sql .= " FROM reglement";
        $sql .= " WHERE reglement_id = ".$reglement_id;
        $tabReglement = $DB_site->query_first($sql);
    
        $sql = "UPDATE reglement SET";
        $sql .= " reglement_statut_id = 1";
        $sql .= " WHERE reglement_id = ".$reglement_id;
        $DB_site->query($sql);
        
        $sql = "INSERT INTO reglement_historique (";
        $sql .= " reglement_reception_id";
        $sql .= ", reglement_id";
        $sql .= ", reglement_reception_date";
        $sql .= ", reglement_historique_date";
        $sql .= ", reglement_historique_username";
        $sql .= ") VALUES (";
        $sql .= 2;
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
        "prenom"=>$facture->utilisateur->prenom,
        "nom"=>$facture->utilisateur->nom,
        "montant"=>$template->price_format($tabReglement['reglement_montant']),
        "facturid"=>$facturid,
        "reglement_id"=>$tabReglement['reglement_id'],
        "date"=>date("d/m/Y"),
        "moyen"=>$moyenpaiement['libelle'],
        "partel"=>($tabNbReg['nb'] > 1 ? 'partiel' : ''),
        "nom_utilisateur"=>$facture->utilisateur->prenom." ".$facture->utilisateur->nom);
        
        $template->pro=$sitepro = ($facture->utilisateur->raisonsociale!="" && $template->site_code()=="base");
        $ec = new email_client($DB_site,$template,283,null,$vars);
        
        $mail = new email();

        $mail->expediteur   = $template->translate("/site/mail_expediteur");
        $mail->expname      = $template->translate("/site/nom");
        $mail->destinataire = $facture->utilisateur->mail;
        $mail->copymode     = "bcc";
        $mail->to           = 'contact@mon-espace-cuisson.com,cecile@mon-espace-cuisson.com';
        $mail->subject      = $ec->subject;
        $mail->body         = $ec->body;
        $mail->sendmail();

        //Insertion de l'email dans le suivi
        $sql = "INSERT INTO mail_outbox SET";
        $sql .= " suivitype    = 'suivi_commande'";
        $sql .= ", facturid     = ".$facturid."";
        $sql .= ", date        = ".time();
        $sql .= ", `to`        = '".$tabUser['mail']."'";
        $sql .= ", utilisateur = '".$_COOKIE['admin_username']."'";
        $sql .= ", subject     = '".mysqli_real_escape_string($DB_site->link_id,$mail->subject)."'";
        $sql .= ", body        = '".mysqli_real_escape_string($DB_site->link_id,$mail->body)."'";
        $DB_site->query($sql);
    }

    //si AMEX, on regarde seulement si le porteur est authentifié, sinon on peut regarder les 2 conditions
    if(($_GET['type'] == 'AMEX' && !in_array($_GET['auth3d'],array("Y","A"))) || ($_GET['type'] != 'AMEX' && ($_GET['gar3d']!="O" || !in_array($_GET['auth3d'],array("Y","A"))))) {
        $htmlmess = "Bonjour,<br>";
        $htmlmess .= "la commande <a href=\"https://fusion.ma-cave-a-vin.fr/admin/facture.php?action=editer&facturid=".$facture->facturid."\">#CDE".$facture->facturid."#</a> a été validé par CB mais SANS garantie de paiement :
        <br><br>Client : ".$facture->utilisateur->userid." - ".$facture->utilisateur->prenom." ".$facture->utilisateur->nom.($facture->raison_sociale!=""?" société ".$facture->utilisateur->raison_sociale:"")." ".$facture->utilisateur->ville." ".$facture->utilisateur->pays."
        <br>Infos carte : ".$_GET['type']." ".$_GET['bin6']." ".$_GET['cbpays']."
        <br>3D Secure : ".$infos3d[$_GET['auth3d']];
        $subject="Paiement par CB sans 3D secure #CDE".$facture->facturid."#";

        $DB_site->query("INSERT INTO suivi_commande SET facturid=".intval($facture->facturid).", date=".time().", suivi='".mysqli_real_escape_string($DB_site->link_id,$htmlmess)."'");
        
        $mail=new email();
        $mail->expediteur   = $template->translate("/site/mail_expediteur");
        $mail->expname      = $template->translate("/site/nom");
        $mail->destinataire = MAIL_DIRECTION;
        $mail->to           = MAIL_COMMERCE2;
        $mail->destname     = $template->translate("/site/nom");
        $mail->subject      = $subject;
        $mail->body         = $htmlmess;
        $mail->sendmail();
    }
}else{
    //Paiement refusé
    
    if(is_null($reglement_id)){
        $sql = "UPDATE facture SET";
        $sql .= " valide = 3";
        $sql .= ", moyenid = ".$moyen_id."";
        $sql .= ", transaction_id = '".$_GET['trans']."'";
        $sql .= " WHERE facturid = '".$facture->facturid."'";
        $DB_site->query($sql);
    }
        
    $errcode = substr($_GET['error'],-2);
    $htmlmsg = "Paiement carte bancaire refusée : ".$errors[$errcode]." (".$_GET['error'].")
    <br>Montant : ".round($_GET['amount']/100);

    $DB_site->query("INSERT INTO suivi_commande SET facturid=".intval($facture->facturid).", date=".time().", suivi='".mysqli_real_escape_string($DB_site->link_id,$htmlmsg)."'");

    $htmlmsg="Commande <a href=\"https://fusion.ma-cave-a-vin.fr/admin/facture.php?action=editer&facturid=".$facture->facturid."\">#CDE".$facture->facturid."#</a>
    <br>".$facture->utilisateur->nom." ".$facture->utilisateur->prenom." (Client <a href=\"https://fusion.ma-cave-a-vin.fr/admin/utilisateur.php?action=editer&userid=".$facture->utilisateur->userid."\">".$facture->utilisateur->userid."</a>)
    <br>".$facture->utilisateur->adresse."
    ".(isset($facture->utilisateur->adresse2) && !empty($facture->utilisateur->adresse2) ? "<br>".$facture->utilisateur->adresse2 : "")."
    <br>".$facture->utilisateur->codepostal." ".$facture->utilisateur->ville."
    <br>".$facture->utilisateur->pays.
    "<br><br>".$htmlmsg;

    $mail=new email();
    $mail->expediteur = $template->translate("/site/mail_expediteur");
    $mail->expname = $template->translate("/site/nom");
    $mail->destinataire = $template->translate("/site/mail_expediteur");
    $mail->destname = $template->translate("/site/nom");
    $mail->to = array($template->translate("/site/base/mail_commercial2"),"webmaster4@mon-espace-cuisson.com");
    $mail->subject = "Tentative de commande par CB échouée";
    $mail->body = $htmlmsg;
    $mail->sendmail();
    
}
?>
