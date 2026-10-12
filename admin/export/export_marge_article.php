<?php

require "../admin_global.php";
include $rootpath."class/DAOarticle.class.php";
include $rootpath."class/garantie.class.php";
include $rootpath."class/DAOgarantie.class.php";
include($rootpath."class/frais_port.php");
include $rootpath."admin/spy.class.php";
include($rootpath."class/fonctions.php");
require_once($rootpath."class/devises.class.php");
require_once($rootpath."class/stock.class.php");
require_once($rootpath."class/email.php");

$DB_site=$DB_site2;

ini_set("display_errors","1");
error_reporting(7);
ini_set("memory_limit","1024M");

header('Content-Type: text/html; charset=utf-8');

extract($_REQUEST);
extract($_COOKIE);

$rootpath="/var/www/vhosts/ma-cave-a-vin.fr/httpdocs/";
$rootpath="../../";

$tauxtva=1.2; // soit 20%

ini_set("display_errors","1");

// on prend le compte de benoit ....
$spy=new spy("benoit","9243aec139440748b013103739128210");
$spy->request("src");
$data=$spy->getcsv();
foreach($data as $fields){
	$site_tmp=strtolower($fields[1]);
	$spydata[$fields[0]][$site_tmp]=$fields;
}

//Liste des sites affichés dans le tableau
$aSiteSpy = array(
	'amazon',
	'auchan',
	'dometic',
	'liebherr-electromenager',
	'abribat',
	'boulanger',
	'carrefour',
	'cdiscount',
	'conforama',
	'darty',
	'imenager',
	'mister good deal',
	'villatech',
	'votre cave',
	'vpcboost',
	'webdistrib'
);
//////////////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////////
if(isset($_GET['marqueid'])){
	$marqueid=$_GET['marqueid'];
} else {
	$marqueid=""; // toutes les marques
}
if(isset($_GET['typearticleid'])){
	$typearticleid=$_GET['typearticleid'];
} else {
	$typearticleid=""; // tous les produits
}
if(isset($_GET['active'])){
	$active=$_GET['active'];
} else {
	$active=array(1); // uniquement les articles actifs.
}
if(isset($_GET['lotage'])){
	$lotage=$_GET['lotage'];
} else {
	$lotage=array("seul"); // les articles seuls uniquement.
}
if(isset($_GET['from'])){
    $dfrom=datetime::createfromformat("d/m/Y",$_GET['from']);
}else{
    $dfrom=date_create("12 month ago");
}  
if(isset($_GET['to'])){
    $dto=datetime::createfromformat("d/m/Y",$_GET['to']);
}else{
    $dto=date_create();
}
if(isset($_GET['visu_fournisseur'])){
	$visu_fournisseur=$_GET['visu_fournisseur'];
} else {
	$visu_fournisseur=0; // si 1 => on n'affiche pas certaines colonnes.
}
if(isset($_GET['typesrc'])){
	$typesrc=$_GET['typesrc']; // les plus vendus, les plus vus ou tous les produits.
} else {
	$typesrc=""; // tous les produits
}
if(isset($_GET['marketplace'])){
	$marketplace=$_GET['marketplace']; // uniquement les produits vendus sur telle marketplace
} else {
	$marketplace=""; // tous les produits
}
if(isset($_GET['artids'])){
	$artids=$_GET['artids']; // uniquement les produits dont le artid esst indiqué
} else {
	$artids=""; // tous les produits
}
if(isset($_GET['typeutilisateur'])){
	$typeutilisateur=$_GET['typeutilisateur']; // uniquement les produits vendus sur telle type d'utilisateur
} else {
	$typeutilisateur=""; // tous les produits
}

//////////////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////////

$echototal="";
$echototal.="<html><head>
<script src=\"//code.jquery.com/jquery-1.12.4.js\"></script>
<script src=\"//cdn.datatables.net/1.10.16/js/jquery.dataTables.min.js\"></script>
<script src=\"//cdn.datatables.net/fixedcolumns/3.2.4/js/dataTables.fixedColumns.min.js\"></script>
</head>
<body>
<style>
.odd, .odd td, .odd th{
	background-color:#DDD;
}
.even, .even td, .even th{
	background-color:#FFF;
}
td, th {
   border: solid 1px black;
   padding: 0px;
   font-weight: normal;
   font-size: 11px;
   box-sizing: initial;
}
.sorting, .sorting_asc, .sorting_desc{
	background-color:#FFF;
}
.container {
   position: relative;
   overflow: hidden;
}
table {
	border-collapse: collapse;
}
body{
	font-family: verdana;
    font-size: 10px;
}
div.dataTables_wrapper {
	margin: 0 auto;
}
.input_marge_pct{
	width: 40px;
}
</style>
<style src=\"//cdn.datatables.net/1.10.16/css/jquery.dataTables.min.css\"></style>
<style src=\"//cdn.datatables.net/fixedcolumns/3.2.4/css/fixedColumns.dataTables.min.css\"></style>
<script>
function precisionRound(number, precision) {
	var factor = Math.pow(10, precision);
	return Math.round(number * factor) / factor;
}
//$=$jQuery;
$(document).ready(function() {
    var table = $('#tableau').DataTable( {
        scrollY:        \"500px\",
        scrollX:        true,
        scrollCollapse: false,
        paging:         false,
		aaSorting: [],
		initComplete: function(settings, json) {
			// $('.sorting').width($('.sorting').width()+10);
			// console.log('complete');
		},
		drawCallback: function( settings ) {
			// console.log('DataTables has redrawn the table');
			// $(\".dataTables_scrollHeadInner\").css({\"width\":\"100%\"});
			// $(\".table\").css({\"width\":\"100%\"});
		},
        fixedColumns:   {
            leftColumns: 4
        }
    } );
	$(\"#taux_commission\").change(function() {
		$(\".input_marge_pct\").each(function( index ) {
			var le_id=$(this).attr('id');
			var paht=parseFloat($(\"#hidden_patotal_\"+le_id).val());
			var pvttc=parseFloat($(\"#hidden_pvtotal_\"+le_id).val());
			var taux_commission=parseFloat($(\"#taux_commission\").val());
			var paht_comm=parseFloat(pvttc*taux_commission/100)+parseFloat(paht);
			var marge_pct=100*((pvttc/".$tauxtva.")-paht_comm)/(pvttc/".$tauxtva.");
			$(\"#\"+le_id).val(precisionRound(marge_pct, 2));
		});
	});
} );
</script>";

$status="(status : ";
$virgule="";
if(in_array(0,$active)){
	$status.=$virgule."inactif";
	$virgule=",";
}
if(in_array(1,$active)){
	$status.=$virgule."actif";
	$virgule=",";
}
if(in_array(2,$active)){
	$status.=$virgule."supprimé";
	$virgule=",";
}
if(in_array(3,$active)){
	$status.=$virgule."archivé";
	$virgule=",";
}
$status.=")";

$typetxt="(type : ";
if($typearticleid!=""){
	$type_article=$DB_site2->query_first("SELECT * FROM typearticle WHERE typearticleid='".$typearticleid."' ");
	$typetxt.=$type_article[libelle];
	$typesql=" AND a.typearticleid='".$typearticleid."' ";
} else {
	$typetxt.="tous";
	$typesql=" ";
}
$typetxt.=")";


$marquetxt="";
if($marqueid==""){
	$lib_marque_txt="Toutes marques";
	$marquesql="";
} else {
	$lib_marque=$DB_site2->query_first("SELECT * FROM marque WHERE marqueid='".$marqueid."' ");
	$lib_marque_txt=$lib_marque[libelle];
	$marquesql=" AND am.marqueid = ".$marqueid;
}

if($typesrc==""){
	$lib_typesrc_txt="";
	$typesrcsql="";
	$order_sql=" ORDER BY a.artcode ASC";
} else {
	if($typesrc=="vus"){
		$lib_typesrc_txt="(limité aux 100 articles les plus vus)";
		$select_sup=", sum(av.nb) as somme ";
		$typesrc_jointure=" INNER JOIN article_vu AS av ON(av.artid=a.artid) ";
		$typesrc_where=" AND av.date BETWEEN ".$dfrom->gettimestamp()." AND ".$dto->gettimestamp()." ";
		$order_sql=" ORDER BY somme DESC LIMIT 100 ";
	}
	if($typesrc=="vendus"){
		$lib_typesrc_txt="(limité aux 100 articles les plus vendus)";
		$select_sup=", sum(lf.qte) as qte ";
		$typesrc_jointure=" INNER JOIN lignefacture AS lf ON(lf.artid=a.artid) INNER JOIN facture AS f ON(f.facturid=lf.factureid)";
		$typesrc_where=" AND lf.prix>0 AND lf.lignefacturedissid=0 AND f.valide IN (1,5,7,8) AND f.provenance IN('', 'mobile') AND f.datefacture BETWEEN '".$dfrom->format("Y-m-d")."' AND '".$dto->format("Y-m-d")."' ";
		$order_sql=" ORDER BY qte DESC LIMIT 100 ";
		
		if($typeutilisateur=="pro"){
			$typesrc_where.=" AND f.raisonsociale<>'' ";
			$sql_nb_ventes_type_utilisateur=" AND f.raisonsociale<>'' ";
		}
		if($typeutilisateur=="part"){
			$typesrc_where.=" AND f.raisonsociale='' ";
			$sql_nb_ventes_type_utilisateur=" AND f.raisonsociale='' ";
		}
	}
}
$typesrc="";

if($marketplace!=""){
	$marketplace_sql=" INNER JOIN market_place_champs AS mpc ON (mpc.artcode = a.artcode) ";
	$marketplace_sql2=" AND mpc.enabled='1' and market_place='".$marketplace."' ";
} else {
	$marketplace_sql="";
	$marketplace_sql2="";
}

if($artids!=""){
	$les_artids=explode(",", $artids);
	$artids_sql=" AND a.artid IN (".implode(",", $les_artids).") ";
} else {
	$artids_sql="";
}

//$rowalt=0;
// $echototal.="<h1>Les ".$typetxt." ".$status." ".$lib_marque[libelle]." ".$lib_typesrc_txt." en article seul (sur la période du ".$dfrom." au ".$dto.")</h1>";
$echototal.="<h1>Les ".$typetxt." ".$status." ".$lib_marque[libelle]." ".$lib_typesrc_txt." en article seul (sur la période du ".$dfrom->format("Y-m-d")." au ".$dto->format("Y-m-d").")</h1>";
$nbcols=0;
if($visu_fournisseur==0){
	$echototal.="Commission : <input type=\"text\" name=\"taux_commission\" id=\"taux_commission\" value=\"0\">%<br><br>";
}

$echototal.="<div class=\"container\">";
// echo "<div class=\"fixed-th-table-wrapper\">";
$headtable="
	<table id=\"tableau\" class=\"stripe row-border order-column\" cellspacing=\"0\" width=\"100%\" border=\"0\">
		<thead>
		<tr>
		<th>Artcode</th>
		<th>Marque</th>";
if($visu_fournisseur!=1){
	// $headtable.="<th colspan=\"2\">Lot</th>";
	$headtable.="<th>Lot</th>";
}		
$headtable.="	<th>Ref Fab</th>
		
		<th>&nbsp;</th>";
if($visu_fournisseur!=1){
	$headtable.="<th>Date création</th>";
	$headtable.="<th>Type article</th>";
}		
$headtable.="<th>Status</th>";
if($visu_fournisseur!=1){
	$headtable.="<th>Comm.</th>";
}		
$headtable.="<th>Date réappro</th>";
if($visu_fournisseur!=1){
	$headtable.="<th>Visible</th>";
}		
$headtable.="<th>Garantie</th>
		<th>nb ventes</th>
		<th>nb devis</th>
		<th>nb vues</th>
		
		<th>&nbsp;</th>
		
		<th>PA HT</th>
		<th>éco. HT</th>
		<th>Emb. Transp.</th>
		<th>Garantie</th>
		<th>Transport</th>
		<th>PA total HT</th>
		
		<th>&nbsp;</th>
		
		<th>PPI TTC</th>
		<th>PV MCAV TTC</th>";
if($visu_fournisseur!=1){
	$headtable.="<th>PV Unit. TTC</th>
		<th>PV Unit. HT</th>
		<th>PV total HT</th>
		<th>Garantie</th>
		<th>Transport</th>";
}		
$headtable.="<th>PV total total HT</th>
		
		<th>&nbsp;</th>
		
		<th>Marge &euro; HT</th>
		<th>Marge %</th>";
if($visu_fournisseur!=1){		
	$headtable.="<th>Marge % -comm</th>";
}
$headtable.="<th>Marge % PPI</th>
		
		<th>&nbsp;</th>
		
		<th>Nb concurrents</th>
		<th>PV MCAV TTC</th>
		<th>Site le plus bas</th>
		<th>Prix le plus bas</th>
		<th>Marge align&eacute;e &euro;</th>
		<th>Marge align&eacute;e %</th>";
$nbcols+=8;
while (list($key, $val) = each($aSiteSpy)) {
	$headtable.="<th>".$val."</th>";
	$nbcols++;
}
reset($aSiteSpy);
$headtable.="</tr></thead><tbody>";
$echototal.=$headtable;
//$rowalt++;

if(in_array("seul",$lotage) && in_array("lot",$lotage)){
	$sqllotage="";
}else{
	if(in_array("seul",$lotage)){
		$sqllotage=" AND (SELECT COUNT(*) FROM article_lot WHERE lotid=a.artid)=0";
	}
	if(in_array("lot",$lotage)){
		$sqllotage=" AND (SELECT COUNT(*) FROM article_lot WHERE lotid=a.artid)>0";
	}
}

$sql="SELECT a.artid ".$select_sup." FROM article AS a
		INNER JOIN article_marque AS am ON(a.artid=am.artid)
		".$typesrc_jointure."
		".$marketplace_sql."
		WHERE 1
		".$typesrc_where."
		".$typesql."
		".$marquesql."
		".$marketplace_sql2."
		".$artids_sql."
		AND a.active IN ('".implode("','",$active)."')
		".$sqllotage."
		GROUP BY artid
		".$order_sql." ";
// echo $sql;
$req=$DB_site2->query($sql);
while($res=$DB_site2->fetch_array($req)){
	// echo "artid : ".$res[artid]."<br>";
	$echototal.=affiche_ligne($res[artid]);
	//echo $return;
}
$echototal.="</tbody></table>";
$echototal.= "</div><br>Les ventes ne comprennent pas les ventes sur les marketplaces.";
$echototal.= "</body></html>";

// $echototal = htmlentities($echototal, ENT_NOQUOTES);

echo $echototal;

if($artids==""){
	$DB_site2->query("INSERT INTO marge_archive (dateline, criteres, html) VALUES ('".time()."', '".serialize($_GET)."' , '".mysqli_real_escape_string($DB_site2->link_id,$echototal)."')");
}


function couleur_marge($pct) {
	$pct_txt=number_format($pct, 1, ',', ' ');
	if($pct<=0){
		$return="<font color=\"#ff00ff\"><b>".$pct_txt." %</b></font>";
	}
	if($pct>0 && $pct<=20){
		$return="<font color=\"#ff0000\"><b>".$pct_txt." %</b></font>";
	}
	if($pct>20 && $pct<=25){
		$return="<font color=\"#ffaa00\"><b>".$pct_txt." %</b></font>";
	}
	if($pct>25 && $pct<=30){
		$return="<font color=\"#22CC22\"><b>".$pct_txt." %</b></font>";
	}
	if($pct>30 && $pct<=40){
		$return="<font color=\"#339933\"><b>".$pct_txt." %</b></font>";
	}
	if($pct>40){
		$return="<font color=\"#3333ff\"><b>".$pct_txt." %</b></font>";
	}
	return $return;
}

function affiche_ligne($artid) {
	$spytime=time()-2*86400;
	global $dfrom;
	global $dto;
	global $DB_site2;
	global $aSiteSpy;
	global $tauxtva;
	//global $rowalt;
	global $spydata;
	global $visu_fournisseur;
	global $sql_nb_ventes_type_utilisateur;
	$sql="SELECT a.artid as artid, a.paht AS paht, SUM(av.nb) as somme, a.artcode AS artcode,
			a.artcode_eurocave AS artcode_eurocave, a.prix AS prix, a.prixtrspt AS prixtrspt,
			a.active, a.commandable, a.date_appro, a.prixconseilleht, a.typearticleid
			FROM article_vu AS av
			INNER JOIN article AS a ON(av.artid=a.artid)
			WHERE av.date BETWEEN ".$dfrom->gettimestamp()." AND ".$dto->gettimestamp(). "
			AND a.artid=".$artid." ";
	$res=$DB_site2->query_first($sql);
	$status="";
	if($res[active]==0){
		$status="inactif";
	}
	if($res[active]==1){
		$status="actif";
	}
	if($res[active]==2){
		$status="supprimé";
	}
	if($res[active]==3){
		$status="archivé";
	}
	if($res[commandable]==1){
		$commandable="oui";
	}else{
		$commandable="non";
	}
	
	$date_reappro = delailivraison($artid);
    if(empty($date_reappro['date_appro']) && $res['date_appro'] > 0){
		$date_reappro['date_appro'] = $res['date_appro'];
	}
	if($date_reappro['date_appro']!=""){
		$date_reappro_txt=date("d/m/Y",$date_reappro['date_appro']);
	}else {
		$date_reappro_txt="-";
	}
	$echoligne="";
	$echolignespy="";
	
	$sql="SELECT prix, paht FROM temp_price WHERE artid=".$artid." AND du < '".time()."' AND au > '".time()."' LIMIT 1";
	$temp_price=$DB_site2->query_first($sql);
	if($temp_price[prix]!=""){
		$article_pvttc=$temp_price[prix];
	} else {
		$article_pvttc=$res[prix];
	}
	
	$sql="	SELECT foa.offre, foa.value FROM fournisseur_offre AS fo
			INNER JOIN fournisseur_offre_article AS foa ON(fo.id=foa.offreid)
			WHERE foa.artid=".$artid." AND fo.du < '".time()."' AND fo.au > '".time()."' LIMIT 1";
	$offre_fournisseur=$DB_site2->query_first($sql);
	if($offre_fournisseur[offre]!=""){
		if($offre_fournisseur[offre]=="temp"){
			$article_paht=$offre_fournisseur[value];
		}
		if($offre_fournisseur[offre]=="reduc"){
			$article_paht=$res[paht]-$offre_fournisseur[value];
		}
		if($offre_fournisseur[offre]=="pc"){
			$article_paht=$res[paht]-($res[paht]*$offre_fournisseur[value]/100);
		}
	} else {
		$article_paht=$res[paht];
	}
	
	$article_fournisseur=$DB_site2->query_first("SELECT ecopartht, trspt FROM article_fournisseur WHERE artid=".$artid." AND principal LIKE '%base%' AND devise='EUR' ");
	if($article_fournisseur[ecopartht]==""){
		$article_fournisseur[ecopartht]=0;
	}
	if($article_fournisseur[trspt]==""){
		$article_fournisseur[trspt]=0;
	}
	$sql="SELECT m.libelle FROM marque AS m
			INNER JOIN article_marque AS am ON(m.marqueid=am.marqueid)
			WHERE am.artid=".$artid." ";
	$marque=$DB_site2->query_first($sql);
	
	
	$lot=$DB_site2->query_first("SELECT COUNT(*) as somme FROM article_lot WHERE lotid='".$artid."' ");
	if($lot[somme]!=0){ // ceci est un lot ....
		$lot_txt="OUI";
	} else {
		$lot_txt="--";
	}
	
	
	$sql="SELECT MIN(date) as date_creation FROM article_history WHERE artid=".$artid." ";
	$datecreation=$DB_site2->query_first($sql);
	
	$sitemin="";
	$pricemin="";
	$spylist=array();
	$nb_conccurents=0;
	
	while (list($key, $val) = each($aSiteSpy)) {
		if($lot[somme]!=0){
			$echolignespy.= "<td align=\"center\">***</td>";
		} else {
			$sql="SELECT price FROM history WHERE timestamp>".$spytime." AND artcode='".$res[artcode]."' AND site='".$val."' ORDER BY timestamp DESC LIMIT 1 ";
			// echo $sql;
			$spy=$DB_site2->query_first($sql);
			
			if($spy['price']!=""){
				$spylist[$artcode][$val]=$spy['price'];
				if($article_pvttc>$spy[price]){
					// ce site est mieux placé
					$bolddeb="<b><font color=\"red\">";
					$boldfin="</font></b>";
					if($pricemin==""){
						// première fois
						$pricemin=$spy[price];
						$sitemin=$val;
					} elseif($pricemin>$spy['price']){
						// nouveau meilleur prix
						$pricemin=$spy[price];
						$sitemin=$val;
					}
				} else {
					$bolddeb="";
					$boldfin="";
				}
				$nb_conccurents++;
				$echolignespy.= "<td align=\"right\" nowrap>".$bolddeb.number_format($spy[price], 0, ',', ' ').$boldfin."  &euro; <a href=\"".$spydata[$res[artcode]][$val][2]."\" target=\"_blank\">X</a></td>";
			} else {
				$echolignespy.= "<td align=\"center\">-</td>";
			}
		}
	}
	reset($aSiteSpy);
	
	if($lot[somme]!=0){
		$pricemin="***";
		$sitemin="***";
		$priceminformat="***";
	} else {
		if($pricemin==""){
			$pricemin="-";
			$sitemin="-";
			$priceminformat="-";
		} else {
			$pricemin=round($pricemin);
			$priceminformat=number_format($pricemin, 0, ',', ' ')." &euro;";
		}
	}
	$sql="SELECT sum(lf.qte) AS qte, sum(lf.qte*(lf.prix/(1+lf.tva/100))) AS caht
			FROM lignefacture lf
			INNER JOIN facture AS f ON(factureid=facturid)
			WHERE lf.prix>=0
			AND lf.lignefacturedissid=0
			AND f.valide IN (1,5,7,8)
			AND lf.artid='".$artid."'
			".$sql_nb_ventes_type_utilisateur."
			AND f.provenance IN('', 'mobile')
			AND f.datefacture>='".$dfrom->format("Y-m-d")."'
			AND f.datefacture<='".$dto->format("Y-m-d")."'";
    $ventes=$DB_site2->query_first($sql);
	if($ventes[qte]==""){
		$ventes[qte]=0;
		$ventes[caht]=0;
		
	}
	$sql="SELECT sum(lf.qte) AS qte, sum(lf.qte*(lf.prix/(1+lf.tva/100))) AS caht FROM lignefacturediss lf
			INNER JOIN facture f ON (f.facturid=lf.factureid)
			WHERE lf.prix>=0
			AND f.valide IN (1,5,7,8)
			AND lf.artid='".$artid."'
			".$sql_nb_ventes_type_utilisateur."
			AND f.datefacture>='".$dfrom->format("Y-m-d")."'
			AND f.datefacture<='".$dto->format("Y-m-d")."'
			AND f.provenance IN ('','mobile')";
    $ventes2=$DB_site2->query_first($sql);
	if($ventes2[qte]!=""){
		$ventes[qte]+=$ventes2[qte];
		$ventes[caht]+=$ventes2[caht];
	}
	
	
	/*
	$sql="SELECT sum(lf.qte) AS qte, sum(lf.qte*(lf.prix/(1+lf.tva/100))) AS caht FROM lignefacture lf
			INNER JOIN article a ON (a.artid=lf.artid)
			INNER JOIN facture f ON (f.facturid=lf.factureid)
			INNER JOIN article ap ON(ap.artid=lf.packid)
			WHERE lf.prix>=0
			AND lf.packid='".$artid."'
			AND f.valide IN (1,5,7,8)
			AND f.datefacture>='".$dfrom->format("Y-m-d")."'
			AND f.datefacture<='".$dto->format("Y-m-d")."'
			AND f.provenance IN ('','mobile')";
	if($artid==1820){
		echo "<br><br>".$sql;
	}
    $ventes3=$DB_site2->query_first($sql);
	if($ventes3[qte]!=""){
		$ventes[qte]+=$ventes3[qte];
		$ventes[caht]+=$ventes3[caht];
	}
	if($artid==1820){
		echo "<br>ventes3 : ".$ventes[qte];
	}
	*/
	
	
	if($res[somme]==""){
		$res[somme]=0;
	}
	$sql="SELECT sum(lf.qte) AS qte, sum(lf.qte*(lf.prix/(1+tva/100))) AS caht
		FROM lignefacture lf
		INNER JOIN facture AS f ON(factureid=facturid)
		INNER JOIN envoi_devis AS ed ON(f.facturid=ed.facturid)
		WHERE prix>=0
		AND lf.artid='".$artid."'
		AND datefacture>='".$dfrom->format("Y-m-d")."'
		AND datefacture<='".$dto->format("Y-m-d")."'";
    $devis=$DB_site2->query_first($sql);
	if($devis[qte]==""){
		$devis[qte]=0;
		$devis[caht]=0;
	}
	
	$visible_txt="";
	$article_visible=$DB_site2->query_first("SELECT * FROM article_visible WHERE artid='".$artid."' AND date>'".time()."' ORDER BY date ASC LIMIT 1");
	if($article_visible[id]!=""){
		if($article_visible[active]==1){
			$visible_txt.="activation le ".date("d/m/Y", $article_visible[date]);
		} else {
			$visible_txt.="désactivation le ".date("d/m/Y", $article_visible[date]);
		}
	} else {
		$visible_txt="-";
	}
	
	
	$lesGaranties=DAOgarantie::loadMaxGarantieArtByPays($DB_site2,'base','FR','57',$artid,'',$article_pvttc);
	$i=0;
	$garantie=0;
	$garantie_txt="";
	if(!empty($lesGaranties)){
		foreach ($lesGaranties as $laGarantie) {
			$i++;
			if ($i==1){
				$garantie=round($laGarantie->paht);
				$garantie_txt.=htmlentities($laGarantie->comm, ENT_NOQUOTES);
			}
			if ($laGarantie->cumul==1){
				$garantie+=round($laGarantie->paht);
				$garantie_txt.=htmlentities($laGarantie->comm, ENT_NOQUOTES);
			}
		}
	}
	
	$type_article=$DB_site2->query_first("SELECT * FROM typearticle WHERE typearticleid='".$res[typearticleid]."' ");
	
	
	
	$frais_port=new frais_port($DB_site2,array($artid=>1),57,$article_pvttc,$article_paht,'base');
	// $jsachatport.="\nachatport['$site_code']=".round($frais_port->paht,2).";";
	// $jsprixport.="\nprixport['$site_code']=".round($frais_port->prixht,2).";";
  
  
	// $article_paht_total=$article_paht+$res[prixtrspt]+$article_fournisseur[ecopartht]+$article_fournisseur[trspt]+$garantie+$frais_port->paht-$frais_port->prixht;
	$article_paht_total=$article_paht+$article_fournisseur[ecopartht]+$article_fournisseur[trspt]+$garantie+$frais_port->paht;
	$article_pv_ht_total=($article_pvttc/$tauxtva)+$frais_port->prixht;
	$marge_ht=$article_pv_ht_total-$article_paht_total;
	$marge_pct=100*$marge_ht/$article_pv_ht_total;
	if($res[prixconseilleht]+$frais_port->prixht>0){
		$marge_ppi_pct=100*($res[prixconseilleht]+$frais_port->prixht-$article_paht_total)/($res[prixconseilleht]+$frais_port->prixht);
	} else {
		$marge_ppi_pct=0;
	}
	// explication de la marge ....
	// $marge_expli="(".$article_pvttc."/".$tauxtva.")-(".$article_paht."+".$res[prixtrspt]."+".$article_fournisseur[ecopartht]."+".$article_fournisseur[trspt]."+".$garantie."+".$frais_port->paht."-".$frais_port->prixht.")";
	
	if($pricemin!="-" && $pricemin!="***"){
		$marge_aligne_ht=($pricemin/$tauxtva)-$article_paht_total;
		$marge_aligne_pct=couleur_marge($marge_aligne_ht*100/($pricemin/$tauxtva));
		$marge_aligne_ht=number_format($marge_aligne_ht, 0, ',', ' ')." &euro;";
	} else {
		$marge_aligne_ht=$pricemin;
		$marge_aligne_pct=$pricemin;
	}
	//$rowalt=$rowalt%2;
	
	$echoligne= "<tr>
					<td nowrap><a href=\"https://www.ma-cave-a-vin.fr/a-art".$artid.".htm\" target=\"_blank\">".$res[artcode]."</a></td>
					<td>".$marque[libelle]."</td>";
	if($visu_fournisseur!=1){
		// $echoligne.="<td colspan=\"2\">--</td>";
		$echoligne.="<td>".$lot_txt."</td>";
	}		
	$echoligne.="<td><a href=\"/admin/ajout_produit_history.php?artid=".$artid."\" target=\"_blank\">".$res[artcode_eurocave]."</a></td>
					
					<td>&nbsp;</td>";
	if($visu_fournisseur!=1){
		$echoligne.="<td>".date("d/m/Y",$datecreation[date_creation])."</td>";
		$echoligne.="<td>".$type_article[libelle]."</td>";
	}		
	$echoligne.="<td align=\"center\">".$status."</td>";
	if($visu_fournisseur!=1){
		$echoligne.="<td align=\"center\">".$commandable."</td>";
	}		
	$echoligne.="<td align=\"center\">".$date_reappro_txt."</td>";
	if($visu_fournisseur!=1){
		$echoligne.="<td align=\"center\">".$visible_txt."</td>";
	}		
	$echoligne.="<td align=\"center\"><span title=\"".$garantie_txt."\">".substr($garantie_txt,0,10)."</span></td>
					<td align=\"center\">".$ventes[qte]."</td>
					<td align=\"center\">".$devis[qte]."</td>
					<td align=\"center\">".$res[somme]."</td>
					
					<td>&nbsp;</td>
					
					<td align=\"right\" nowrap>".@number_format($article_paht, 0, ',', ' ')." &euro;</td>
					<td align=\"right\" nowrap>".number_format($article_fournisseur[ecopartht], 0, ',', ' ')." &euro;</td>
					<td align=\"right\" nowrap>".number_format($article_fournisseur[trspt], 0, ',', ' ')." &euro;</td>
					<td align=\"right\" nowrap>".number_format($garantie, 0, ',', ' ')." &euro;</td>
					<td align=\"right\" nowrap>".number_format($frais_port->paht, 0, ',', ' ')." &euro;</td>
					<td align=\"right\" nowrap>".number_format($article_paht_total, 0, ',', ' ')." &euro;</td>
					
					<td>&nbsp;</td>
					
					
					<td align=\"right\" nowrap>".number_format($res[prixconseilleht]*$tauxtva, 0, ',', ' ')." &euro;</td>
					<td align=\"right\" nowrap><b>".number_format($article_pvttc, 0, ',', ' ')." &euro;</b></td>";
	if($visu_fournisseur!=1){
		$echoligne.="<td align=\"center\">--</td>
					<td align=\"center\">--</td>
					<td align=\"right\" nowrap><b>".number_format($article_pvttc/$tauxtva, 0, ',', ' ')." &euro;</b></td>
					<td align=\"right\">0 &euro;</td>
					<td align=\"right\" nowrap>".number_format($frais_port->prixht, 0, ',', ' ')." &euro;</td>";
	}		
	$echoligne.="<td align=\"right\" nowrap><b>".number_format($article_pv_ht_total, 0, ',', ' ')." &euro;</b></td>
					
					<td>&nbsp;</td>
					
					<td align=\"right\" nowrap>".number_format($marge_ht, 0, ',', ' ')." &euro;</td>
					<td align=\"right\" nowrap>".couleur_marge($marge_pct)."</td>";
	if($visu_fournisseur!=1){
		$echoligne.="<td align=\"right\" nowrap>
						<input type=\"text\" name=\"input_marge_pct_".$artid."\" class=\"input_marge_pct\" id=\"".$artid."\" value=\"".round($marge_pct,2)."\" disabled> %
						<input type=\"hidden\" name=\"hidden_patotal_".$artid."\" class=\"hidden_patotal\" id=\"hidden_patotal_".$artid."\" value=\"".$article_paht_total."\">
						<input type=\"hidden\" name=\"hidden_pvtotal_".$artid."\" class=\"hidden_pvtotal\" id=\"hidden_pvtotal_".$artid."\" value=\"".$article_pvttc."\">
					</td>";
	}
	$echoligne.="	<td align=\"right\" nowrap>".couleur_marge($marge_ppi_pct)."</td>
					
					<td>&nbsp;</td>
					
					<td align=\"center\">".$nb_conccurents."</td>
					<td align=\"right\" nowrap><b>".number_format($article_pvttc, 0, ',', ' ')." &euro;</b></td>
					<td align=\"center\">".$sitemin."</td>
					<td align=\"right\" nowrap>".$priceminformat."</td>
					<td align=\"right\" nowrap>".$marge_aligne_ht."</td>
					<td align=\"right\" nowrap>".$marge_aligne_pct."</td>
					".$echolignespy;
	$echoligne.= "</tr>";
	
	//$rowalt++;
	return $echoligne;
}

?>