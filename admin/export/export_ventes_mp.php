<?php

include("../admin_global.php");
include("ooo.php");

if(!isset($_GET['ts'])) $_GET['ts']=time();
if(!isset($_GET['mode']) || !in_array($_GET['mode'],array("month","year"))) $_GET['mode']="month";

$d=DateTime::createfromformat("U",$_GET['ts']);
$d->settimezone(new datetimezone("Europe/Stockholm"));
switch($_GET['mode']){
    case "month":
    $start=$d->modify("first day of this month")->settime(0,0,0)->gettimestamp();
    $end=$d->modify("last day of this month")->settime(23,59,59)->gettimestamp();
    $name="mp_".$d->format("Y-m");
    break;
    
    case "year":
    $start=$d->modify("first day of january")->settime(0,0,0)->gettimestamp();
    $end=$d->modify("last day of december")->settime(23,59,59)->gettimestamp();
    $name="mp_".$d->format("Y");
    break;
}

$req=$DB_site2->query("SELECT market_place_site_id,market_place_nom_complet,market_place_com FROM market_place");
while($mp=$DB_site2->fetch_array($req))
    $mplist[$mp['market_place_site_id']]=$mp;

$sql="SELECT lf.*,f.provenanceid 
FROM lignefacture lf INNER JOIN facture f ON(f.facturid=lf.factureid)
WHERE f.provenance='mp' AND lignefacturedissid=0 AND UNIX_TIMESTAMP(datefacture) BETWEEN ".$start." AND ".$end;

$total=$totalcom=$qte=$libelle=array();
$req=$DB_site2->query($sql);
while($lf=$DB_site2->fetch_array($req)){
    $total[$lf['provenanceid']][$lf['artid']]+=$lf['prix']*$lf['qte'];
    $totalcom[$lf['provenanceid']][$lf['artid']]+=$lf['prix']*$lf['qte']*(1-$mplist[$lf['provenanceid']]['market_place_com']/100);
    $qte[$lf['provenanceid']][$lf['artid']]+=$lf['qte'];
    $libelle[$lf['artid']]=$lf['libelle'];
    $artcodes[$lf['artid']]=$lf['artcode'];
}

$sql="SELECT lf.*,f.provenanceid 
FROM lignefacturediss lf
INNER JOIN facture f ON ( lf.factureid = f.facturid )
WHERE f.provenance = 'mp' AND UNIX_TIMESTAMP(datefacture) BETWEEN ".$start." AND ".$end;
$req=$DB_site2->query($sql);
while($lf=$DB_site2->fetch_array($req)){
    $total[$lf['provenanceid']][$lf['artid']]+=$lf['prix']*$lf['qte'];
    $totalcom[$lf['provenanceid']][$lf['artid']]+=$lf['prix']*$lf['qte']*(1-$mplist[$lf['provenanceid']]['market_place_com']/100);
    $qte[$lf['provenanceid']][$lf['artid']]+=$lf['qte'];
    $libelle[$lf['artid']]=$lf['libelle'];
    $artcodes[$lf['artid']]=$lf['artcode'];
}


$ooo=new ooo();
$ooo->name=$name;


foreach($total as $provenanceid=>$artids){
    
    $name=$mplist[$provenanceid]['market_place_nom_complet'];
    $tables[$name]=new ooo_table($name);
    $tables[$name]->set_col(1,"100mm");
    $cells=array();
    $cells[]=new ooo_cell("Article");
    $cells[]=new ooo_cell("Référence");
    $cells[]=new ooo_cell("Qté");
    $cells[]=new ooo_cell("Total");
    $cells[]=new ooo_cell("Total après com");
    $tables[$name]->addrow(1,$cells);
    
    $i=2;
    arsort($artids);
    foreach($artids as $artid=>$totalart){
	$cells=array();
	$cells[]=new ooo_cell(html_entity_decode($libelle[$artid]));
	$cells[]=new ooo_cell($artcodes[$artid]);
	$cells[]=new ooo_cell($qte[$provenanceid][$artid]);
	$cells[]=new ooo_cell($totalart);
	$cells[]=new ooo_cell($totalcom[$provenanceid][$artid]);
	$tables[$name]->addrow($i,$cells);
	$i++;
    }
}

if(!is_null($tables)){
    ksort($tables);
    foreach($tables as $table) $ooo->addtable($table);
}

$ooo->generate();

?>
