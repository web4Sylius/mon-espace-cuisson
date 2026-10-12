<?php
$document_root = '/home/mon-espace-cuisson.com';

require_once($document_root.'/global.php');
include_once($document_root.'/mailconfirmation.php');
require_once($document_root.'/Aci-AdM/dissociation.php');

$errors=array(
"00"=>"transaction approuvée ou traitée avec succès.",
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

$etransaction_ip=array("194.2.160.66","194.2.122.158","194.2.122.190","195.25.7.146","195.25.7.166","195.101.99.72","195.101.99.76","80.12.82.212");
if(isset($_SERVER['REMOTE_ADDR']) && !in_array($_SERVER['REMOTE_ADDR'],$etransaction_ip)) exit;

$f=fopen("paybox.log","a");
fwrite($f,date("d/m/Y H:i:s").",".$_GET['facturid'].",".$_GET['amount'].",".$_GET['auto'].",".$_GET['trans'].",".$_GET['cbpays'].",".$_GET['bin6'].",".$_GET['error']."\n");
fclose($f);


$facture=new facture($DB_site,$_GET['facturid']);
$template=new template($facture->utilisateur->langue,$DB_site);
if(isset($_GET['type']) && $_GET['type'] == 'AMEX'){
     $moyen_id = 13;
} else {
     $moyen_id = 1;
} 
if($_GET['error']=="00000"){
     //Paiement valide
     $sql = "UPDATE facture SET";
     $sql .= " valide = 1";
     $sql .= ", moyenid = ".$moyen_id;
     $sql .= ", transaction_id = '".$_GET['trans']."'";
     $sql .= " WHERE facturid = '".$facture->facturid."'";
     $DB_site->query($sql);
     
     $facture->add_historique($facture->facturid, 1);
     
     $template->path = SITE_PATH."/templates/".$facture->utilisateur->langue;
     mailconfirmation(1,$facture,null);
     $diss = new dissociation($DB_site,$facture->facturid);
}else{
     //Paiement refusé
     $sql = "UPDATE facture SET";
     $sql .= " valide = 3";
     $sql .= ", moyenid = ".$moyen_id."";
     $sql .= ", transaction_id = '".$_GET['trans']."'";
     $sql .= " WHERE facturid = '".$facture->facturid."'";
     $DB_site->query($sql);
     
     $facture->add_historique($facture->facturid, 3);
     
     $errcode = substr($_GET['error'],-2);
     $htmlmsg = "Paiement carte bancaire refusée : ".$errors[$errcode]." (".$_GET['error'].")";

     $DB_site->query("INSERT INTO suivi_commande SET facturid=".intval($facture->facturid).", date=".time().", suivi='".mysql_real_escape_string($htmlmsg)."'");

     $htmlmsg="Commande ".$facture->facturid."
     <br>".$facture->utilisateur->nom." ".$facture->utilisateur->prenom."
     <br>".$facture->utilisateur->adresse."
     ".(isset($facture->utilisateur->adresse2) && !empty($facture->utilisateur->adresse2) ? "<br>".$facture->utilisateur->adresse2 : "")."
     <br>".$facture->codepostal." ".$facture->ville."
     <br>".$facture->pays.
     "<br><br>".$htmlmsg;

     $mail=new email();
     $mail->expediteur = MAIL_CONTACT;
     $mail->expname = NOM_CONTACT;
     $mail->destinataire = MAIL_CONTACT;
     $mail->destname = NOM_CONTACT;
     $mail->to = MAIL_ADMIN;
     $mail->subject = "Tentative de commande par CB échouée";
     $mail->body = $htmlmsg;
     $mail->sendmail();
}
?>