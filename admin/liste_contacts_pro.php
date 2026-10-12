<?php
/// tâche SI#6780 : Extraction base de donnée


ini_set("display_errors","on");
include_once("host_config_admin.php");
include_once($rootpath."class/email.php");
include_once($rootpath."class/stock.class.php");
include_once($rootpath."class/fonctions.php");
include_once($rootpath."admin/export/xlexport.php");

$DB_site=clone $DB_site2;


$csv=new xlexport("csv");
$csv->name            = "liste_contacts_pro";
$csv->csvsep          = ";";
$csv->striptags       = false;
$filename_source      = $rootpath."admin/csv/".$csv->name.".csv";

$csv->addrow(array("Date inscription", "N. client", "Societe","Nom", "Prenom","Email","Fonction","Telephone","Portable","Langue","Pays","Inscrit newsletters"));


$requete=$DB_site->query("SELECT u.raisonsociale, u.userid, u.nom, u.prenom, u.mail, u.profession, u.telephone, u.portable, u.allowemail, u.dateinscription, u.langue, p.libelle FROM utilisateur u INNER JOIN pays p ON p.paysid = u.paysid WHERE u.raisonsociale != ''");


while($pro=$DB_site->fetch_array($requete)) {
	$date = new DateTime($pro['dateinscription']);
	$csv->addrow(array($date->format('Y-m-d'), $pro['userid'],$pro['raisonsociale'],$pro['nom'],$pro['prenom'],$pro['mail'], $pro['profession'], $pro['telephone'], $pro['portable'], $pro['langue'], $pro['libelle'],($pro['allowemail'] == '1' ? 'oui' : 'non')));

	$requete2=$DB_site->query("SELECT u_a.utilisateur_assoc_id, u_a.utilisateur_assoc_nom, u_a.utilisateur_assoc_prenom, u_a.utilisateur_assoc_fonction, u_a.utilisateur_assoc_email, u_a.utilisateur_assoc_tel, u_a.utilisateur_assoc_portable FROM utilisateur_assoc u_a WHERE u_a.userid = ".$pro['userid']);
	while($pro2=$DB_site->fetch_array($requete2)) {
		$csv->addrow(array("", $pro['userid'], $pro['raisonsociale'], $pro2['utilisateur_assoc_nom'], $pro2['utilisateur_assoc_prenom'], $pro2['utilisateur_assoc_email'], $pro2['utilisateur_assoc_fonction'], $pro2['utilisateur_assoc_tel'], $pro2['utilisateur_assoc_portable'], "", "",""));
	}
}




@unlink($rootpath."admin/csv/".$csv->name.".csv");

$f=fopen($filename_source,"w+");
fputs($f,$csv->buffer);
fclose($f);
$file = $rootpath."admin/csv/".$csv->name.".csv"; 
header('Content-Description: File Transfer'); 
header('Content-Type: application/force-download'); 
header('Content-Disposition: attachment; filename=' . basename($file)); 
readfile($file); 
exit;