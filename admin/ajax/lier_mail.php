<?php
require_once($_SERVER['DOCUMENT_ROOT']."/admin/admin_global.php");

if(!isset($_GET['destinataire_id']) || empty($_GET['destinataire_id'])) exit;
if(!isset($_GET['mailid']) || empty($_GET['mailid'])) exit;
if(!isset($_GET['type']) || empty($_GET['type'])) exit;
if(!isset($_GET['sens']) || empty($_GET['sens'])) exit;

switch ($_GET['sens']) {
	case 'out':
		$table = 'mail_outbox';

		break;
	
	case 'in':
		$table = 'mail_inbox';

		break;
}

switch ($_GET['action']) {

	case 'lier':

		switch ($_GET['type']) {
			case 'sav':
				$facturid = $DB_site2->query_first("SELECT facturid FROM sav WHERE id='".$_GET['destinataire_id']."'");
				$sql = "UPDATE $table SET savid ='".$_GET['destinataire_id']."', facturid='".$facturid[0]."' WHERE id='".$_GET['mailid']."' ";	
				$DB_site2->query($sql);
				$type="sav";
				break;

			case 'commande':
				$sql = "UPDATE $table SET facturid ='".$_GET['destinataire_id']."' WHERE id='".$_GET['mailid']."' ";	
				$DB_site2->query($sql);
				$type="cmd";
				break;
		}

		/*$insertId = $DB_site2->insert_id();

		//Ajout dans le suivi des duplications
		$sql = "INSERT INTO suivi_duplication (";
		$sql .= "suivi_origine_id";
		$sql .= ", suivi_origine_type";
		$sql .= ", suivi_copie_id";
		$sql .= ", suivi_copie_type";
		$sql .= ", copie_id";
		$sql .= ") VALUES (";
		$sql .= $_GET['mailid'];
		$sql .= ", 'client'";
		$sql .= ", 0";
		$sql .= " ,'".$type."'";
		$sql .= ", ".$_GET['destinataire_id'];
		$sql .= ")";
		$DB_site2->query($sql);*/

		break;
	
	case 'delier':
		switch ($_GET['type']) {
			case 'sav':
				$DB_site2->query("UPDATE $table SET savid =0 WHERE id='".$_GET['mailid']."' ");
				break;

			case 'commande':
				$DB_site2->query("UPDATE $table SET facturid =0 WHERE id='".$_GET['mailid']."' ");
				break;
		}
		break;
}


echo 'done';