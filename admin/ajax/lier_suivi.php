<?php
require_once($_SERVER['DOCUMENT_ROOT']."/admin/admin_global.php");

if(!isset($_GET['suiviid']) || empty($_GET['suiviid'])) exit;
if(!isset($_GET['type']) || empty($_GET['type'])) exit;
if(!isset($_GET['typeid']) || empty($_GET['typeid'])) exit;

switch ($_GET['action']) {

	case 'lier':

		switch ($_GET['type']) {
			case 'sav':
				$sql = "UPDATE suivi_commande SET savid ='".$_GET['typeid']."' WHERE id='".$_GET['suiviid']."' ";	
				$DB_site2->query($sql);
				break;

			/*case 'commande':
				$sql = "UPDATE $table SET facturid ='".$_GET['destinataire_id']."' WHERE id='".$_GET['mailid']."' ";	
				$DB_site2->query($sql);

				break;*/
		}

		break;
	
	case 'delier':
		switch ($_GET['type']) {
			case 'sav':
				$DB_site2->query("UPDATE suivi_commande SET savid =0 WHERE id='".$_GET['suiviid']."' ");
				break;

			case 'commande':
				$DB_site2->query("UPDATE $table SET facturid =0 WHERE id='".$_GET['mailid']."' ");
				break;
		}
		break;
}


echo 'done';