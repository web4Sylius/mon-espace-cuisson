<?php
require_once($_SERVER['DOCUMENT_ROOT']."/admin/admin_global.php");

if(!isset($_GET['type']) || empty($_GET['type'])) exit;
if(!isset($_GET['userid']) && !isset($_GET['facturid'])) exit;
if(!isset($_GET['suiviid']) || empty($_GET['suiviid'])) exit;
if(!isset($_GET['type_suivi']) || empty($_GET['type_suivi'])) exit;


switch($_GET['type_suivi']){

	case 'classique':
		//on recupere le contenu du suivi a dupliquer
		$suivi = $DB_site2->query_first("SELECT * FROM suivi_client WHERE id='".$_GET['suiviid']."'");

		//on copie
		$sql  = "INSERT INTO suivi_client";
		$sql .= " SET type ='".$suivi['type']."',";
		$sql .= " moyen = '".$suivi['moyen']."',";
		$sql .= " destinataire ='".$suivi['destinataire']."',";
		$sql .= " userid ='".$suivi['userid']."',";
		$sql .= " commercial = '".$suivi['commercial']."',";
		$sql .= " date = '".$suivi['date']."',";
		$sql .= " suivi = '".mysqli_escape_string($DB_site2->link_id,$suivi['suivi'])."',";
		$sql .= " rappel = '".$suivi['rappel']."',";
		$sql .= " rappeltype = '".$suivi['rappeltype']."',";
		$sql .= " jour = '".$suivi['jour']."',";
		$sql .= " etat = '".$suivi['etat']."',";
		$sql .= " pour = '".$suivi['pour']."',";
		$sql .= " hide = '".$suivi['hide']."',";
		$sql .= " projetid = '0'";
		$DB_site2->query($sql);

	break;

	case 'mail_outbox':
		$suivi = $DB_site2->query_first("SELECT * FROM mail_outbox WHERE id='".$_GET['suiviid']."'");

		//on copie
		$sql  = "INSERT INTO mail_outbox";
		$sql .= " SET date ='".$suivi['date']."',";
		$sql .= " utilisateur = '".$suivi['utilisateur']."',";
		$sql .= " `from` = '".$suivi['from']."',";
		$sql .= " `to` ='".$suivi['to']."',";
		$sql .= " subject ='".mysqli_real_escape_string($DB_site2->link_id,$suivi['subject'])."',";
		$sql .= " body = '".mysqli_real_escape_string($DB_site2->link_id,$suivi['body'])."',";
		$sql .= " pj = '".$suivi['pj']."',";
		$sql .= " projetid = ".$suivi['projetid'].", ";
		$sql .= " savid =".$suivi['savid'].", ";
		$sql .= " userid =".$suivi['userid'].", ";
		$sql .= " facturid=".$suivi['facturid'].", ";
		$sql .= " fournisseur ='".$suivi['fournisseur']."' ,";
		$sql .= " compta ='".$suivi['compta']."'";

		$DB_site2->query($sql);

	break;

	case 'mail_inbox':
		$suivi = $DB_site2->query_first("SELECT * FROM mail_inbox WHERE id='".$_GET['suiviid']."'");
		//on copie
		$sql  = "INSERT INTO mail_inbox";
		$sql .= " SET date ='".$suivi['date']."',";
		$sql .= " `from` = '".$suivi['from']."',";
		$sql .= " `to` ='".$suivi['to']."',";
		$sql .= " subject ='".mysqli_real_escape_string($DB_site2->link_id,$suivi['subject'])."',";
		$sql .= " body = '".mysqli_real_escape_string($DB_site2->link_id,$suivi['body'])."',";
		$sql .= " pj = '".$suivi['pj']."',";
		$sql .= " projetid = ".$suivi['projetid'].", ";
		$sql .= " savid =".$suivi['savid'].", ";
		$sql .= " userid =".$suivi['userid'].", ";
		$sql .= " facturid=".$suivi['facturid'].", ";
		$sql .= " fournisseur ='".$suivi['fournisseur']."' ,";
		$sql .= " compta ='".$suivi['compta']."'";

		$DB_site2->query($sql);
		
	break;



}