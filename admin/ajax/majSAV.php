<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/admin/admin_global.php');

if(!isset($_POST['sav_id'])) exit;

require_once(BACK_PATH.'/sav.class.php');

$sav_id      = $_POST['sav_id'];

$sav_contenu = '';
if(isset($_POST['content'])) $sav_contenu = $_POST['content'];

$sav_priorite = '';
if(isset($_POST['valeur'])) $sav_priorite = $_POST['valeur'];

$oSAV = new sav($DB_site2, $sav_id);

if($sav_contenu != ''){
	//Récupération du précédent 
	$sql = "SELECT sav_etat";
	$sql .= " FROM sav";
	$sql .= " WHERE id = ".$oSAV->id;
	$tabSuivi = $DB_site2->query_first($sql);

	//Mise à jour du SAV
	$sql = "UPDATE sav";
	$sql .= " SET sav_etat = '".mysqli_real_escape_string($DB_site2->link_id,$sav_contenu)."'";
	$sql .= " WHERE id = ".$oSAV->id;
	$DB_site2->query($sql);

	//Mise en forme du texte du suivi
	$suivi_commande = ""
	. "[maj_sav] Dossier SAV ".$oSAV->types[$oSAV->typelitige]." du ".date("d/m/Y",$oSAV->date_creation)." : mise à jour suivi rapide :<br>"
	. "\"" . $tabSuivi['sav_etat'] . "\"" . " => " . "\"" . $sav_contenu . "\"";

	//Mise à jour dans le suivi
	$sql = "INSERT INTO suivi_commande SET";
	$sql .= " facturid    = ".$oSAV->facturid;
	$sql .= ", date       = ".time()."";
	$sql .= ", commercial = '".$_COOKIE['admin_username']."'";
	$sql .= ", suivi      = '".mysqli_real_escape_string($DB_site2->link_id,$suivi_commande)."'";
	$DB_site2->query($sql);
}

if($sav_priorite != ''){
	//Mise à jour du SAV
	$sql = "UPDATE sav";
	$sql .= " SET sav_priorite = ".$sav_priorite;
	$sql .= " WHERE id = ".$oSAV->id;
	$DB_site2->query($sql);

	//Mise en forme du texte du suivi
	$suivi_commande = ""
	. "[maj_sav] Dossier SAV ".$oSAV->types[$oSAV->typelitige]." du ".date("d/m/Y",$oSAV->date_creation)." : mise à jour de la priorité vers ".($sav_priorite == 1 ? 'Oui' : 'Non');

	//Mise à jour dans le suivi
	$sql = "INSERT INTO suivi_commande SET";
	$sql .= " facturid    = ".$oSAV->facturid;
	$sql .= ", date       = ".time()."";
	$sql .= ", commercial = '".$_COOKIE['admin_username']."'";
	$sql .= ", suivi      = '".mysqli_real_escape_string($DB_site2->link_id,$suivi_commande)."'";
	$DB_site2->query($sql);
}