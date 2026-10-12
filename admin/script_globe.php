<?php
/////SI#7317 : fichier globe commandes
require_once($_SERVER['DOCUMENT_ROOT']."/admin/admin_global.php");
include_once($rootpath."admin/export/xlexport.php");
ini_set("display_errors",1);

$nb_colis=$DB_site2->query("SELECT a.facturid, a.id, a.date, af.name, af.value FROM `adv` a INNER JOIN adv_fields af ON af.advid = a.id WHERE a.destinataire = 'GLOBE EXPRESS 2 DIS' AND a.date > 1577833205 AND af.name LIKE '%colis\_%' ORDER BY a.id DESC");
$colis_nb=[];
while($colis = $DB_site2->fetch_array($nb_colis)){
	$colis_nb[$colis['facturid']]+=1;
}

$results=$DB_site2->query("SELECT a.facturid, a.date, af.value, a.lignes, af.name FROM `adv` a INNER JOIN adv_fields af ON af.advid = a.id WHERE a.destinataire = 'GLOBE EXPRESS 2 DIS' AND a.date > 1577833205 AND (af.name ='valeurtotalsls' OR af.name LIKE '%colis\_%') ORDER BY a.date DESC");
$ligne=[];

while($result = $DB_site2->fetch_array($results)){
	$qte_art = preg_match_all('/\d-/',$result['lignes'],$rqte);
	$lignes = preg_match_all('/-\d+/',$result['lignes'],$rlignes);
	$poids_tt_ligne=0;
	foreach ($rlignes as &$rligne) {
    	$lignes = preg_replace('/-/','',$rligne);
	}
	foreach ($rqte as &$qte) {
    	$qte_art = preg_replace('/-/','',$qte);
	}

	$count_qte=0;
	foreach($lignes as $lignefac){
		$poids=$DB_site2->query_first("SELECT emballage_poids FROM article a INNER JOIN lignefacture lf ON lf.artid = a.artid WHERE lf.lignefactureid = ".$lignefac); 
		$poids_tt_ligne += intval($poids['emballage_poids'])*intval($qte_art[$count_qte]);
		$count_qte++;
	}

	$ligne[$result['facturid']]['poids'] = $poids_tt_ligne;


	$ligne[$result['facturid']]['nb_colis'] = $colis_nb[$result['facturid']];
	
}

$csv=new xlexport("csv");
$csv->name = "globe_express_cmd_01012020";
$csv->csvsep = ";";
$csv->striptags = false;
$filename_source = $rootpath."admin/csv/".$csv->name.".csv";
$csv->addrow(array("Num cmde","Date","Nb colis","Poids brut","Valeur déclarée"));

$results_tabl=$DB_site2->query("SELECT a.facturid, a.date, af.value, af.name FROM `adv` a INNER JOIN adv_fields af ON af.advid = a.id WHERE a.destinataire = 'GLOBE EXPRESS 2 DIS' AND a.date > 1577833205 AND (af.name ='valeurtotalsls') ORDER BY a.date DESC");

while($result_tabl = $DB_site2->fetch_array($results_tabl)){
	$csv->addrow(array($result_tabl['facturid'],date('d/m/Y',$result_tabl['date']),($ligne[$result_tabl['facturid']]['nb_colis']==''?1:$ligne[$result_tabl['facturid']]['nb_colis']),$ligne[$result_tabl['facturid']]['poids'],$result_tabl['value']));
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



