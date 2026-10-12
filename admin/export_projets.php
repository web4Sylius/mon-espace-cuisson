<?php
////SI#7418 : Extraction base email///
require_once("admin_global.php");
include_once("../class/projet.class.php");
require_once($rootpath."admin/export/xlexport.php");

$csv =  new xlexport("csv");
$csv->name            = "projets_perdus_export";
$csv->csvsep          = ";";
$csv->striptags       = false;
$filename_source      = $rootpath."admin/csv/".$csv->name.'.csv';

//Listing des entêtes du fichier CSV
$head = array("N° projet","Date projet","Userid","Nom projet","Budget","Noms contact","Email","Téléphone","Portable","Commercial","Date perdu");
$csv->addrow($head, false, true);


$projets=$DB_site2->query("SELECT projet.*,s.date FROM projet INNER JOIN suivi_client s ON s.projetid = projet.projetid WHERE etatid = 6 AND s.date > 1646091407 AND s.suivi LIKE '%=&gt; Perdu%' GROUP BY projet.projetid ORDER BY projet.projetid");
while($projet_req = $DB_site2->fetch_array($projets)){
	$projet=new projet($projet_req["projetid"]);
	$users=$projet->users();
	foreach($users as $user){
		if(!is_null($user->userid)){
			$line[0] = "#PJT".$projet->projetid."#";
			$line[1] = (!is_null($projet->date)?$projet->date->format("d/m/Y"):"");
			$line[2] = $user->userid;
			$line[3] = $projet->libelle;
			$line[4] = $projet->budget;
			$line[5] = $user->nom . " " . $user->prenom;
			$line[6] = $user->mail;
			$line[7] = "'" . $user->telephone . "'";
			$line[8] = "'" . $user->portable."'";
			$line[9] = $projet->commercial;
			$line[10] = date("d/m/Y",$projet_req['date']);
			/*$projet_relations=$DB_site2->query("SELECT * FROM utilisateur_relation WHERE (user1id='".intval($projet->userid)."' OR user2id='".$projet->userid."') AND relation_type = 'utilisateur'");
			while($projet_relation=$DB_site2->fetch_array($projet_relations)){
				if($projet_relation['user1id']==$projet->userid){
					$userid_related=$projet_relation['user2id'];
				} else {
					$userid_related=$projet_relation['user1id'];
				}
				if(in_array($userid_related,$projet->relationids)){
					$csv->addrow($line, false, true);
					$utilisateur=new utilisateur($DB_site2,$userid_related);
					$line[0] = "#PJT".$projet->projetid."#";
					$line[1] = (!is_null($projet->date)?$projet->date->format("d/m/Y"):"");
					$line[2] = "";
					$line[3] = $projet->libelle;
					$line[4] = $projet->budget;
					$line[5] = $utilisateur->nom . " " . $utilisateur->prenom;
					$line[6] = $utilisateur->mail;
					$line[7] = "'" . $utilisateur->telephone . "'";
					$line[8] = "'" . $utilisateur->portable."'";
					$line[9] = $projet->commercial;
					$line[10] = date("d/m/Y",$projet_req['date']);
				}
			}*/

			$contacts=$user->contacts();
			foreach($contacts as $contact){
				if(in_array($contact->userid,$projet->contactids)){
					$csv->addrow($line, false, true);
					$line[0] = "#PJT".$projet->projetid."#";
					$line[1] = (!is_null($projet->date)?$projet->date->format("d/m/Y"):"");
					$line[2] = "";
					$line[3] = $projet->libelle;
					$line[4] = $projet->budget;
					$line[5] = $contact->nom . " " . $contact->prenom;
					$line[6] = $contact->mail;
					$line[7] = "'" . $contact->telephone . "'";
					$line[8] = "'" . $contact->portable."'";
					$line[9] = $projet->commercial;
					$line[10] = date("d/m/Y",$projet_req['date']);
				}
			}
			$csv->addrow($line, false, true);
		}
	}
}


@unlink($filename_source);

$f=fopen($filename_source,"w");
if($f){
	fputs($f,$csv->buffer);
	fclose($f);

}

$csv =  new xlexport("csv");
$csv->name            = "projets_export";
$csv->csvsep          = ";";
$csv->striptags       = false;
$filename_source      = $rootpath."admin/csv/".$csv->name.'.csv';

//Listing des entêtes du fichier CSV
$head = array("N° projet","Date projet","Userid","Nom projet","Budget","Noms contact","Email","Téléphone","Portable","Commercial");
$csv->addrow($head, false, true);


$projets=$DB_site2->query("SELECT * FROM projet WHERE etatid IN (2,3,4,7,8)");
while($projet = $DB_site2->fetch_array($projets)){
	$projet=new projet($projet["projetid"]);
	$users=$projet->users();
	foreach($users as $user){
		if(!is_null($user->userid)){
			$line[0] = "#PJT".$projet->projetid."#";
			$line[1] = (!is_null($projet->date)?$projet->date->format("d/m/Y"):"");
			$line[2] = $user->userid;
			$line[3] = $projet->libelle;
			$line[4] = $projet->budget;
			$line[5] = $user->nom . " " . $user->prenom;
			$line[6] = $user->mail;
			$line[7] = "'" . $user->telephone . "'";
			$line[8] = "'" . $user->portable."'";
			$line[9] = $projet->commercial;
			/*$projet_relations=$DB_site2->query("SELECT * FROM utilisateur_relation WHERE (user1id='".intval($projet->userid)."' OR user2id='".$projet->userid."') AND relation_type = 'utilisateur'");
			while($projet_relation=$DB_site2->fetch_array($projet_relations)){
				if($projet_relation['user1id']==$projet->userid){
					$userid_related=$projet_relation['user2id'];
				} else {
					$userid_related=$projet_relation['user1id'];
				}
				if(!in_array($userid_related,$projet->relationids)){
					$csv->addrow($line, false, true);
					$utilisateur=new utilisateur($DB_site2,$userid_related);
					$line[0] = "#PJT".$projet->projetid."#";
					$line[1] = (!is_null($projet->date)?$projet->date->format("d/m/Y"):"");
					$line[2] = "";
					$line[3] = $projet->libelle;
					$line[4] = $projet->budget;
					$line[5] = $utilisateur->nom . " " . $utilisateur->prenom;
					$line[6] = $utilisateur->mail;
					$line[7] = "'" . $utilisateur->telephone . "'";
					$line[8] = "'" . $utilisateur->portable."'";
					$line[9] = $projet->commercial;
				}
			}*/

			$contacts=$user->contacts();
			foreach($contacts as $contact){
				if(in_array($contact->userid,$projet->contactids)){
					$csv->addrow($line, false, true);
					$line[0] = "#PJT".$projet->projetid."#";
					$line[1] = (!is_null($projet->date)?$projet->date->format("d/m/Y"):"");
					$line[2] = "";
					$line[3] = $projet->libelle;
					$line[4] = $projet->budget;
					$line[5] = $contact->nom . " " . $contact->prenom;
					$line[6] = $contact->mail;
					$line[7] = "'" . $contact->telephone . "'";
					$line[8] = "'" . $contact->portable."'";
					$line[9] = $projet->commercial;
				}
			}
			$csv->addrow($line, false, true);
		}
	}
}

@unlink($filename_source);

$f=fopen($filename_source,"w");
if($f){
	fputs($f,$csv->buffer);
	fclose($f);

}
