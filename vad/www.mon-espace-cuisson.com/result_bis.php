<?php
$document_root = '/home/mon-espace-cuisson.com';

require_once($document_root.'/global.php');
ini_set('display_errors',1);

$errors = array(
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
     "99"=>"incident domaine initiateur."
);

$etransaction_ip = array("194.2.122.158","194.2.160.66","195.25.7.166","195.101.99.76","194.2.122.190","195.25.7.146","195.101.99.72","80.12.82.212");

//Si l'utilisateur qui charge cette page n'est pas dans la plage d'adresse IP ci-dessus => page blanche
if(!in_array($_SERVER['REMOTE_ADDR'],$etransaction_ip)) exit;

//Enregistrement du détail de la transaction dans les logs
$f = fopen("paybox_bis.log","a");
fwrite($f,date("d/m/Y H:i:s").",".$_GET['reglement'].",".$_GET['amount'].",".$_GET['auto'].",".$_GET['trans'].",".$_GET['cbpays'].",".$_GET['bin6'].",".$_GET['error']."\n");
fclose($f);

if(isset($_GET['type']) && $_GET['type'] == 'AMEX'){
     $moyen_id = 13;
} else {
     $moyen_id = 1;
}
/*if(isset($_GET['test']) && $_GET['test'] == 1){
	$_GET['reglement'] = '293677/175';
	$_GET['error'] = "00000";
}*/
list($facturid,$reglement_id)=explode("/",$_GET['reglement']);

$fcl=new facture($DB_site,$facturid);
if(!isset($objUser)) $objUser = new user($DB_site,$fcl->userid);

//Récupération du détail du règlement  
$tabReglement = $objUser->getReglement($reglement_id); 

if($_GET['error'] == "00000"){ //Paiement valide
     
     $objUser->upd_payment(array(
         'reglement_id'   => $tabReglement['reglement_id'],
         'statut'         => 1,
         'date'           => $tabReglement['reglement_date'],
         'moyen'	  => $moyen_id,
         'montant'	  => $tabReglement['reglement_montant']
     ));
     
     $objUser->add_recpt_payment(array(
         'reglement_id'   => $tabReglement['reglement_id'],
         'reception_id'   => 3,
         'reception_date' => $tabReglement['reglement_date']
     ));
     
     $sql = "SELECT libelleFR AS libelle";
     $sql .= " FROM moyenpaiement";
     $sql .= " WHERE moyenid = ".$tabReglement['reglement_moyen'];
     $moyenpaiement = $DB_site->query_first($sql);

	 //SAVOIR SI C'EST LE 1er, 2ème... PAIEMENT PARTIEL
	 $sqlReg = "SELECT *";
	 $sqlReg .= " FROM reglement ";
	 $sqlReg .= " WHERE facturid = ".$fcl->facturid;
	 $sqlReg .= " ORDER BY reglement_id";
	 $reqReg = $DB_site->query($sqlReg);
		
	 $i=1;	
	 while($regid = $DB_site->fetch_array($reqReg)){
		 if($regid['reglement_id'] == $tabReglement['reglement_id']){ //$tabReglement pour resultat_bis.php et $reglement pour facture_reglement.php
			 $rang = $i;
			 break;
		 }
		 $i++;
	 }
	 //$reg = $rang."".($rang==1 ? "er" : "ème");
	 //FIN
	 
	  //SI 1er REGLEMENT :
	 if($rang == 1){
		 $reg = $rang."er";
		 $fin = "<p>Votre commande sera trait&eacute;e et vous sera exp&eacute;di&eacute;e dans les plus brefs d&eacute;lais.</p><p>Vous recevrez un prochain e-mail d&egrave;s que votre commande sera exp&eacute;di&eacute;e.</p><p>Nous vous remercions de votre confiance et de l&#39;int&eacute;r&ecirc;t que vous portez &agrave; nos produits. Notre &eacute;quipe est &agrave; votre disposition :<br />- Commercial : +33 (0) 4.81.91.90.40<br />- Suivi de commande : +33 (0) 4.81.91.90.41</p><p>A tr&egrave;s bient&ocirc;t sur <strong>www.mon-espace-cuisson.com</strong><br /><br /><font face=\"Arial\" size=\"2\">Cordialement,<br /><br /><strong>L&#39;&eacute;quipe Mon Espace Cuisson</strong><br /><br /><img src=\"http://www.mon-espace-cuisson.com/images/logo_mail_mec.jpg\" alt=\"Mon Espace Cuisson\"><br /><strong>Les experts en électroménager de cuisson</strong><br /><strong><a href=\"http://www.mon-espace-cuisson.com/\"><font color=\"#ff5a08\">www.mon-espace-cuisson.com</font></a></strong><br /><br /><b>Hotline :</b> <font color=\"#ff5a08\">+33 4 81 91 90 40</font></font></p>";
	 }else{ //LES SUIVANTS :
		 $reg = $rang."ème";
		 $fin = "<p>Nous vous remercions de votre confiance.</p><p>A tr&egrave;s bient&ocirc;t,<br /><br /><b>L&#39;&eacute;quipe Mon Espace Cuisson</b><br /><img alt=\"Mon Espace Cuisson\" src=\"http://www.mon-espace-cuisson.com/images/logo_mail_mec.jpg\" /><br /><b><font>Les experts en &eacute;lectrom&eacute;nager de cuisson</font></b><br /><b><a href=\"www.mon-espace-cuisson.com\"><font color=\"#ff5a08\">www.mon-espace-cuisson.com</font></a></b><br /><br /><b>Pour tout renseignement compl&eacute;mentaire, contactez-nous :</b><br /><a href=\"mailto:contact@mon-espace-cuisson.com\">Service commercial</a> : <font color=\"#ff5a08\">+33 4 81 91 90 40</font><br /><a href=\"mailto:adv@mon-espace-cuisson.com\">Suivi de commande</a> : <font color=\"#ff5a08\">+33 4 81 91 90 41</font><br /><a href=\"mailto:qualite@mon-espace-cuisson.com\">Service apr&egrave;s-vente</a> : <font color=\"#ff5a08\">+33 4 81 91 90 42</font></p>";
	 }
	 
     $tabEmail = $objUser->getMail(110);

     $sujet   = $tabEmail['sujetFR'];
     $sujet   = str_replace('%FACTURID%',$fcl->facturid,$sujet);
     $contenu = $tabEmail['contenuFR'];

     $contenu = str_replace('%PRENOM%',$objUser->facturation_prenom,$contenu);
     $contenu = str_replace('%NOM%',$objUser->facturation_nom,$contenu);
	 $contenu = str_replace('%REG%',$reg,$contenu); //COMBIEN IEME PAIEMENT PARTIEL
	 $contenu = str_replace('%FIN%',$fin,$contenu); // FIN DU MAIL
     $contenu = str_replace('%MONTANT%',number_format($tabReglement['reglement_montant'],2,',',' ').' ¤',$contenu);
     $contenu = str_replace('%REGLEMENT_ID%',$tabReglement['reglement_id'],$contenu);
     $contenu = str_replace('%FACTURID%',$fcl->facturid,$contenu);
     $contenu = str_replace('%DATE%',date("d/m/Y"),$contenu);
     $contenu = str_replace('%MOYEN%',$moyenpaiement['libelle'],$contenu);

     $mail = new email();

     $mail->expediteur   = $template->translate("/site/mail_expediteur");
     $mail->expname      = $template->translate("/site/nom");
     $mail->destinataire = $objUser->mail;
     $mail->destname	 = $objUser->facturation_prenom." ".$objUser->facturation_nom;
     $mail->to           = array('contact@mon-espace-cuisson.com','cecile@mon-espace-cuisson.com');
     $mail->copymode     = "bcc";
     $mail->subject      = $sujet;
     $mail->body         = $contenu;
     $mail->sendmail();

     //Insertion de l'email dans le suivi
     $sql = "INSERT INTO mail_outbox SET";
     $sql .= " suivitype    = 'suivi_commande'";
     $sql .= ", suiviid     = ".$fcl->facturid."";
     $sql .= ", date        = ".time();
     $sql .= ", `to`        = '".$objUser->mail."'";
     $sql .= ", utilisateur = '".$_COOKIE['admin_username']."'";
     $sql .= ", subject     = '".mysql_real_escape_string($mail->subject)."'";
     $sql .= ", body        = '".mysql_real_escape_string($mail->body)."'";
     $DB_site->query($sql);
     
} else { //Paiement refusé/abandonné/etc...
     // on annule pas un règlement, le client peut le refaire
     /*$objUser->upd_payment(array(
         'reglement_id'    => $tabReglement['reglement_id'],
         'transaction_num' => $_GET['trans'],
         'reglement_transaction_com' => $errors[substr($_GET['error'],-2)],
         'statut'          => 3
     ));*/
     
}