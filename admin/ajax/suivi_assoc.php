<?php
require_once($_SERVER['DOCUMENT_ROOT']."/admin/admin_global.php");

if(!isset($_GET['destinataire_id']) || empty($_GET['destinataire_id'])) exit;
// if(!isset($_GET['userid']) || empty($_GET['userid'])) exit;
if(!isset($_GET['suiviid']) || empty($_GET['suiviid'])) exit;
if(!isset($_GET['type_suivi']) || empty($_GET['type_suivi'])) exit;
if(!isset($_GET['origine']) || empty($_GET['origine'])) exit;

//Ajout dans le suivi des commandes
switch($_GET['type_suivi']){
	
	case 'fournisseur':
                $sql="SELECT * FROM suivi_fournisseur WHERE id=".$_GET['suiviid'];
	
	case 'classique':

                if(!isset($sql)){
                    $sql = "SELECT *";
                    $sql .= " FROM suivi_client"; 
                    $sql .= " WHERE id = ".$_GET['suiviid'];
                }
                
		$aSuiviClient = $DB_site2->query_first($sql);
		
		switch($_GET['origine']){

			case 'commande':

				$sql = "INSERT INTO suivi_commande (";
				$sql .= "type";
				$sql .= ", destinataire";
				$sql .= ", moyen";
				$sql .= ", facturid";
				$sql .= ", date";
				$sql .= ", commercial";
				$sql .= ", suivi";
				$sql .= ", rappel";
				$sql .= ", rappeltype";
				$sql .= ", jour";
				$sql .= ", pour";
				$sql .= ", hide";
				$sql .= ") VALUES (";
				$sql .= "'".$aSuiviClient['type']."'";
				$sql .= ", '".$aSuiviClient['destinataire']."'";
				$sql .= ", '".$aSuiviClient['moyen']."'";
				$sql .= ", '".$_GET['destinataire_id']."'";
				$sql .= ", '".$aSuiviClient['date']."'";
				$sql .= ", '".$aSuiviClient['commercial']."'";
				$sql .= ", '".mysqli_real_escape_string($DB_site2->link_id,$aSuiviClient['suivi'])."'";
				$sql .= ", '".$aSuiviClient['rappel']."'";
				$sql .= ", '".$aSuiviClient['rappeltype']."'";
				$sql .= ", '".$aSuiviClient['jour']."'";
				$sql .= ", '".$aSuiviClient['pour']."'";
				$sql .= ", '".$aSuiviClient['hide']."'";
				$sql .= ")";
				$DB_site2->query($sql);

				break;

			case 'client':

				$sql = "INSERT INTO suivi_client (";
				$sql .= "type";
				$sql .= ", moyen";
				$sql .= ", destinataire";
				$sql .= ", userid";
				$sql .= ", commercial";
				$sql .= ", date";
				$sql .= ", suivi";
				$sql .= ", rappel";
				$sql .= ", rappeltype";
				$sql .= ", jour";
				$sql .= ", etat";
				$sql .= ", pour";
				$sql .= ", hide";
				$sql .= ") VALUES (";
				$sql .= "'".$aSuiviClient['type']."'";
				$sql .= ", '".$aSuiviClient['moyen']."'";
				$sql .= ", '".$aSuiviClient['destinataire']."'";
				$sql .= ", '".$_GET['destinataire_id']."'";
				$sql .= ", '".$aSuiviClient['commercial']."'";
				$sql .= ", '".$aSuiviClient['date']."'";
				$sql .= ", '".mysqli_real_escape_string($DB_site2->link_id,$aSuiviClient['suivi'])."'";
				$sql .= ", '".$aSuiviClient['rappel']."'";
				$sql .= ", '".$aSuiviClient['rappeltype']."'";
				$sql .= ", '".$aSuiviClient['jour']."'";
				$sql .= ", '".$aSuiviClient['etat']."'";
				$sql .= ", '".$aSuiviClient['pour']."'";
				$sql .= ", '".$aSuiviClient['hide']."'";
				$sql .= ")";
				$DB_site2->query($sql);

				break;
		}

		break;

	//Cas de l'insertion dans les mail_inbox et outbox
	case 'mail_outbox':

		$sql = "SELECT *";
		$sql .= " FROM mail_outbox"; 
		$sql .= " WHERE id = ".$_GET['suiviid'];
		$aSuiviClient = $DB_site2->query_first($sql);

		$sql = "INSERT INTO mail_outbox SET date='".$aSuiviClient['date']."' , utilisateur='".$aSuiviClient['utilisateur']."', `to`='".$aSuiviClient['to']."', `from`='".$aSuiviClient['from']."', subject='".mysqli_real_escape_string($DB_site2->link_id,$aSuiviClient['subject'])."', body='".mysqli_real_escape_string($DB_site2->link_id,$aSuiviClient['body'])."', pj='".$aSuiviClient['pj']."' ";

		switch($_GET['origine']){
			case 'commande':
				$sql .= ", facturid='".$_GET['destinataire_id']."'";
				break;
			case 'client':
				$sql .= ", userid='".$_GET['destinataire_id']."'";
				break;
		}

		$sql .=", uniqueid='".$aSuiviClient['uniqueid']."'";

		$DB_site2->query($sql);

		break;

	case 'mail_inbox':
		
		$sql = "SELECT *";
		$sql .= " FROM mail_inbox"; 
		$sql .= " WHERE id = ".$_GET['suiviid'];
		$aSuiviClient = $DB_site2->query_first($sql);

		$sql = "INSERT INTO mail_inbox SET date='".$aSuiviClient['date']."' , utilisateur='".$aSuiviClient['utilisateur']."', `to`='".$aSuiviClient['to']."', `from`='".$aSuiviClient['from']."', subject='".mysqli_real_escape_string($DB_site2->link_id,$aSuiviClient['subject'])."', body='".mysqli_real_escape_string($DB_site2->link_id,$aSuiviClient['body'])."', pj='".$aSuiviClient['pj']."' ";

		switch($_GET['origine']){

			case 'commande':
				$sql .= ", facturid='".$_GET['destinataire_id']."'";
				break;
			case 'client':
				$sql .= ", userid='".$_GET['destinataire_id']."'";
				break;
		}
		$sql .=", uniqueid='".$aSuiviClient['uniqueid']."'";
		
		$DB_site2->query($sql);

		break;
}

$insertId = $DB_site2->insert_id();

//Ajout dans le suivi des duplications
$sql = "INSERT INTO suivi_duplication (";
$sql .= "suivi_origine_id";
$sql .= ", suivi_origine_type";
$sql .= ", suivi_copie_id";
$sql .= ", suivi_copie_type";
$sql .= ", copie_id";
$sql .= ") VALUES (";
$sql .= $_GET['suiviid'];
$sql .= ", 'client'";
$sql .= ", ".$insertId;
switch($_GET['origine']){
	case 'commande':
		$sql .= ", 'cmd'";
		break;
	case 'client':
		$sql .= ", 'cli'";
		break;
}
$sql .= ", ".$_GET['destinataire_id'];
$sql .= ")";
$DB_site2->query($sql);

echo "done";