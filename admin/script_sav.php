<?php
/******************************************************************************************************************************************************************/
// Ce script récupère la liste des SAV de types "panne" qui ne sont pas en statut "demande de cloture", ni "cloturé" et qui ont été créé il y a plus de 30 jours. //
// Il envoi ensuite une alerte par email pour vérifier si la réparation a bien été effectué ou non 																  //
/******************************************************************************************************************************************************************/
ini_set('display_errors',1);
include_once("host_config_admin.php");
require_once($rootpath."class/email.php"); 
require_once($rootpath."admin/sav.class.php");

$DB_site = clone $DB_site2;

$sql = "SELECT s.id as savid";
$sql .= " FROM sav s";
$sql .= " INNER JOIN sav_fields sf ON (sf.savid = s.id)";
$sql .= " WHERE 1";
$sql .= " AND FROM_UNIXTIME(s.date) < DATE_SUB(NOW(),INTERVAL 1 MONTH)";
$sql .= " AND sf.name  			= 'sav_typelitige'";
$sql .= " AND sf.value 			= 1";
$sql .= " AND s.fin 		    = 0";
$sql .= " AND s.demande_cloture = 0"; 
$sql .= " ORDER BY s.date ASC";
$req = $DB_site->query($sql); 

$cpt = 0;
while($le_sav = $DB_site->fetch_array($req)){

	$oSav = new sav($DB_site, $le_sav['savid']); 

	//Récupération nom/prénom client
	$sql = "SELECT u.prenom, u.nom";
	$sql .= " FROM facture f";
	$sql .= " INNER JOIN utilisateur u ON (u.userid = f.userid)";
	$sql .= " WHERE f.facturid = ".$oSav->facturid;
	$aClient = $DB_site->query_first($sql);

	$tableSav .= ""
	. "<tr>"
		. "<td style=\"text-align: center; padding: 10px; background-color: ".($cpt%2 == 0 ? '#e6e6e6' : '#d0d0d0')."\"><a href=\"http://".$host."/admin/facture.php?action=editer&facturid=".$oSav->facturid."&lst_trietat=\">".$oSav->facturid."</a></td>"
		. "<td style=\"text-align: center; padding: 10px; background-color: ".($cpt%2 == 0 ? '#e6e6e6' : '#d0d0d0')."\">".ucfirst(mb_strtolower($aClient['prenom'], 'UTF-8')).' '.mb_strtoupper($aClient['nom'], 'UTF-8')."</td>"
		. "<td style=\"text-align: center; padding: 10px; background-color: ".($cpt%2 == 0 ? '#e6e6e6' : '#d0d0d0')."\">".str_replace(':','h',date('d/m/Y H:i', $oSav->date_creation))."</td>"
		. "<td style=\"text-align: left; max-width: 500px; padding: 0 5px; background-color: ".($cpt%2 == 0 ? '#e6e6e6' : '#d0d0d0')."\">".$oSav->fields['sav_detail1-verif']."</td>"
	. "</tr>";

	$cpt++;
}

$sujet   = "Suivi automatique - SAV de panne de plus de 30 jours en cours";
$message = ""
. "Bonjour,<br><br>"
. "Ci-dessous, la liste des SAV de type \"panne\", créés il y a plus d'1 mois, et qui ne sont ni en \"demande de clôture\", ni \"clôturés\".<br><br>"
. "<table border=\"0\" cellpaddin=\"0\" cellspacing=\"0\" style=\"border-collapse: collapse;\">"
	. "<tr>"
		. "<td style=\"border: 1px solid; text-align: center; font-weight: bold; padding: 10px; background-color: #A88D5C; color: white;\">N°commande</td>"
		. "<td style=\"border: 1px solid; text-align: center; font-weight: bold; padding: 10px; background-color: #A88D5C; color: white;\">Client</td>"
		. "<td style=\"border: 1px solid; text-align: center; font-weight: bold; padding: 10px; background-color: #A88D5C; color: white;\">Date création SAV</td>"
		. "<td style=\"border: 1px solid; text-align: center; font-weight: bold; padding: 10px; background-color: #A88D5C; color: white;\">Description</td>"
	. "</tr>"
	. $tableSav
. "</table>";

if(!isset($_GET['test'])){

	$mail = new email();

	$mail->expediteur   = "contact@mon-espace-cuisson.com";
	$mail->expname      = "Ma Cave à Vin";
	$mail->destinataire = "qualite@mon-espace-cuisson.com";
	$mail->to           = array("pr@mon-espace-cuisson.com");
	$mail->subject      = $sujet;
	$mail->body         = $message;
	$mail->sendmail();

} else {
	header('Content-Type: text/html; charset=utf-8');
	echo "Sujet du mail : ".$sujet."<br><br>";
	echo "Message : <br><br>".$message;
}