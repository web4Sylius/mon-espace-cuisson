<?php 
require ("global.php"); 
require BACK_PATH."/export/xlexport.php";

//mot de passe : 5G?L6t%c7!
// URL : https://www.mon-espace-cuisson.com/export_gclid.php?log=expgg&pass=5G?L6t%c7!
if(!isset($_GET['log']) || !isset($_GET['pass'])){
	header('location:/');
	exit;
}
if(isset($_GET['log']) && isset($_GET['pass'])){
	if($_GET['log'] != 'expgg' && $_GET['pass'] != '5G?L6t%c7!'){
		header('location:/');
		exit;
	}
}

$csv = new xlexport("csv","export_ggclid_mcav");
$csv->csvsep = ",";

$param_time = array("Parameters:TimeZone=Europe/Paris","","","","");
$csv->addrow($param_time,false);

//Listing des entêtes du fichier CSV
$head = array("Google Click ID","Conversion Name","Conversion Time","Conversion Value","Conversion Currency");
$csv->addrow($head,false);

//recupération des commandes avec un gclid
$datefin = mktime(0, 0, 0, date('m'), date('d')+1, date('Y')); // demain matin 00:00:01.
// $datedebut = $datefin - (60*60*24*2) ;
$datedebut = mktime(0, 0, 0, date('m'), date('d')-2, date('Y')); // il y a 2 jours à minuit

$sql = "SELECT facturid, gclid, heurefacture FROM facture WHERE gclid != '' AND heurefacture BETWEEN '".$datedebut."' AND '".$datefin."' AND valide IN (1,5,7,8)";

$req = $DB_site->query($sql);
while($row = $DB_site->fetch_array($req)){
	$facture = new facture($DB_site, $row['facturid']);
	$date = date("m/d/Y h:i:s A", $row['heurefacture']);
	$price = round($facture->totalht,2);
	$price = str_replace(",", ".", $price);
	$ligne = array($row['gclid'], "import_achat", $date, $price,"EUR");
	$csv->addrow($ligne,false, false, true);
}

$csv->export();


?>