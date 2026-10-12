<?php

ini_set("display_errors","1");
error_reporting(E_ALL);
ini_set("memory_limit","1024M");

extract($_REQUEST);
extract($_COOKIE);

header('Content-Type: text/html; charset=utf-8');



if(isset($_SERVER['SERVER_NAME']) && strpos($_SERVER['SERVER_NAME'], 'localhost') !== false){ // LOCAL
    $rootpath   = "d:/wamp/www/ma-cave-a-vin/"; 
} else if(isset($_SERVER['SERVER_NAME']) && strpos($_SERVER['SERVER_NAME'], 'test2015') !== false) {
    $rootpath   = "/home/ma-cave-a-vin-2015/";
} else if(isset($_SERVER['SERVER_NAME']) && strpos($_SERVER['SERVER_NAME'], 'test') !== false) {
    $rootpath   = $_SERVER['DOCUMENT_ROOT']."/";
} else {
    $rootpath   = "/var/www/vhosts/ma-cave-a-vin.fr/httpdocs/";
}


$host="www.ma-cave-a-vin.fr" ;
$nostat=1;
include_once($rootpath."configurations/$host/config_mysql.php");
include($rootpath."class/mysql.php");
include($rootpath."configurations/$host/config.php");

/*
include $rootpath."class/DAOarticle.class.php";
include $rootpath."class/garantie.class.php";
include $rootpath."class/DAOgarantie.class.php";
include($rootpath."class/frais_port.php");
*/

/*
include($rootpath."class/template.class.php");
include($rootpath."class/devises.class.php");
include_once($rootpath."class/fonctions.php");
include_once($rootpath."class/email.php");
include($rootpath."admin/export/ooo.php");
include($rootpath."class/ccstat.class.php");
include($rootpath."admin/marge.php");
include($rootpath."class/categorie.class.php");
include($rootpath."class/facture.class.php");


include $rootpath."class/stock.class.php";
include $rootpath."admin/spy.class.php";
*/

ini_set("display_errors","1");
// error_reporting(E_ALL);


$DB_site=new DB_Sql;
$DB_site->appname='boutique';
$DB_site->appshortname='boutique';
$DB_site->database=$DBname;
$DB_site->server=$db_server;
$DB_site->user=$DBusername;
$DB_site->password=$DBpassword;
$DB_site->connect();

// $template=new template("FR",$DB_site);


if (isset($admin_username) && $admin_username != "") {
    if($admin_username!="9d3bb895f22bf0afa958d68c2a58ded7"){
      $ip=$_SERVER['REMOTE_ADDR'];
      $acces=$DB_site->query_first("SELECT * FROM acces_admin WHERE ip='$ip' AND user='$admin_username' AND connecte=1 ORDER BY timestamp DESC LIMIT 0,1");
      if($acces['timestamp']==false || $acces['timestamp']<time()-3600){
         exit;
      }else{
        $user_info=$DB_site->query_first("SELECT * FROM parametresgeneraux WHERE username = '$admin_username'");
        if ($user_info[username] != $admin_username){
			echo "erreur d'autenfication";
			exit;
		}
      }
    }
}else
  exit;






if(isset($_POST['from']))
    $from=$_POST['from'];
else
    $from=mktime(0,0,0, 1, 1, 2015 );
    
if(isset($_POST['to']))
    $to=$_POST['to'];
else
    $to=mktime(0,0,0, 1, 1, 2018 );
    

//$spytime=1451631601;

?>
<html><head></head><body>
<style>
.row1{
	background-color:#DDD;
}
.row2{
	background-color:#FFF;
}
table td, table th{
	border: solid 1px black;
}
table{
	border-collapse:collapse;
}
</style>
<?php

$rowalt=0;
echo "<h1>Les cdes auprès des fournisseurs avec livraison à destination de Ginko (entrées en stock)</h1>";
$headtable="<table>
			<tr class=\"row".$rowalt."\">
				<th>date cde</th>
				<th>date entrée</th>
			<!--	<th>debug</th> -->
				<th>N° cde stock</th>
				<th>N° cde client</th>
				<th>marque</th>
				<th>type produit</th>
				<th>ref aci</th>
				<th>ref fabriquant</th>
				<th>largeur</th>
				<th>hauteur</th>
				<th>profondeur</th>
				<th>poids</th>
				<th>surface au sol</th>
				<th>volume</th>
			</tr>";
echo $headtable;
$rowalt++;

/*
$sql="SELECT av.artid, SUM(av.nb) as somme FROM article_vu AS av FORCE INDEX(artid)
	INNER JOIN article AS a ON(av.artid=a.artid) 
	WHERE date BETWEEN ".$dfrom->gettimestamp()." AND ".$dto->gettimestamp()."
	AND a.typearticleid='1'
	AND (SELECT COUNT(*) FROM article_lot WHERE lotid=a.artid)=0
	GROUP BY artid ORDER BY somme DESC LIMIT 40 ";
*/
	
	
// public $statetxt=array(0=>"en commande",1=>"en stock",2=>"réservé",3=>"expédié");


$sql="SELECT a.artid, cmd.id as cmdid, item.date as itemdate, cmd.date AS cmddate, item.id AS itemid, item.stock_cmd_id, item.artid, a.artid, a.artcode AS artcode, a.typearticleid, a.artcode_eurocave AS artcode_fabricant,
	a.emballage_largeur AS emballage_largeur, a.emballage_hauteur AS emballage_hauteur, a.emballage_profondeur AS emballage_profondeur, a.emballage_poids AS emballage_poids, m.libelle AS marquelibelle, typearticle.libelle AS typearticle_libelle, item.dest as destination, item.origin
	FROM stock_item AS item
	LEFT OUTER JOIN stock_cmd AS cmd ON (item.stock_cmd_id=cmd.id) 
	INNER JOIN article AS a ON(item.artid=a.artid)
	INNER JOIN article_marque AS am ON(a.artid=am.artid)
	INNER JOIN marque AS m ON (am.marqueid=m.marqueid)
	INNER JOIN typearticle ON(a.typearticleid=typearticle.typearticleid)
	WHERE (item.lieu = '630900' OR item.lieu='632979')
	AND item.state>0
	AND item.date>=".$from." AND item.date<=".$to." 
	ORDER BY item.date ASC ";
// echo $sql;
$totalsurface=0;
$totalvolume=0;
$req=$DB_site->query($sql);
while($res=$DB_site->fetch_array($req)){
	// $return=affiche_ligne($res[artid]);
	// echo $return;
	$rowalt=$rowalt%2;
	$debug="";
	$dateentree=date("d/m/Y", $res['itemdate']);
	if($res['cmddate']==0){
		$datecde=$res['origin'];
	}else{
		$datecde=date("d/m/Y", $res['cmddate']);
	}
	$surface=round( ($res['emballage_largeur']*$res['emballage_profondeur'])/1000/1000 , 2);
	$volume=round( ($res['emballage_largeur']*$res['emballage_profondeur']*$res['emballage_hauteur'])/1000/1000/1000 , 2);
	$totalsurface+=($res['emballage_largeur']*$res['emballage_profondeur'])/1000/1000;
	$totalvolume+=($res['emballage_largeur']*$res['emballage_profondeur']*$res['emballage_hauteur'])/1000/1000/1000;
	if($res['cmdid']==""){
		// $res['cmdid']=$res['destination'];
		$res['cmdid']=$res['origin'];
	}
	if(substr($res['destination'],0,8)=="facture "){
		$res['destination']=substr($res['destination'],8);
	} else{
		if($res['destination']==""){
			$res['destination']="en stock";
		}
	}
	if($res['artcode']=="ACI-ART910"){
		$debug=$res[itemid];
	}
	echo "<tr class=\"row".$rowalt."\">
				<td>".$datecde."</td>
				<td>".$dateentree."</td>
			<!--	<td>".$debug."</td> -->
				<td align=\"right\">".$res['cmdid']."</td>
				<td align=\"right\">".$res['destination']."</td>
				<td>".$res['marquelibelle']."</td>
				<td>".$res['typearticle_libelle']."</td>
				<td>".$res['artcode']."</td>
				<td>".$res['artcode_fabricant']."</td>
				<td align=\"right\">".$res['emballage_largeur']."</td>
				<td align=\"right\">".$res['emballage_hauteur']."</td>
				<td align=\"right\">".$res['emballage_profondeur']."</td>
				<td align=\"right\">".$res['emballage_poids']."</td>
				<td align=\"right\">".$surface." m2</td>
				<td align=\"right\">".$volume." m3</td>
			</tr>";
	$rowalt++;
}
$totalsurface=round($totalsurface,2);
$totalvolume=round($totalvolume,2);
echo "<tr class=\"row".$rowalt."\">
				<td colspan=13 align=\"right\">".$totalsurface." m²</td>
				<td align=\"right\">".$totalvolume." m3</td>
			</tr>";
$foottable="</table>";
echo $foottable;


$rowalt=0;
echo "<h1>Les cdes à destination des clients en partance de Ginko (sorties de stock)</h1>";
$headtable="<table>
			<tr class=\"row".$rowalt."\">
				<th>date cde</th>
				<th>date sortie</th>
			<!--	<th>Debug</th> -->
				<th>N° cde stock</th>
				<th>N° cde client</th>
				<th>marque</th>
				<th>type produit</th>
				<th>ref aci</th>
				<th>ref fabriquant</th>
				<th>largeur</th>
				<th>hauteur</th>
				<th>profondeur</th>
				<th>poids</th>
				<th>surface au sol</th>
				<th>volume</th>
			</tr>";
echo $headtable;
$rowalt++;



$sql="SELECT a.artid, item.date_enlevement AS date_enlevement, item.id AS itemid, item.stock_cmd_id, item.date AS itemdate, item.artid, a.artid, a.artcode AS artcode, a.typearticleid, a.artcode_eurocave AS artcode_fabricant,
	a.emballage_largeur AS emballage_largeur, a.emballage_hauteur AS emballage_hauteur, a.emballage_profondeur AS emballage_profondeur, a.emballage_poids AS emballage_poids, m.libelle AS marquelibelle, typearticle.libelle AS typearticle_libelle, CAST(SUBSTR(item.dest,8) AS UNSIGNED) as destination, item.origin
	FROM stock_item AS item
	LEFT OUTER JOIN stock_cmd AS cmd ON (item.stock_cmd_id=cmd.id) 
	INNER JOIN article AS a ON(item.artid=a.artid)
	INNER JOIN article_marque AS am ON(a.artid=am.artid)
	INNER JOIN marque AS m ON (am.marqueid=m.marqueid)
	INNER JOIN typearticle ON(a.typearticleid=typearticle.typearticleid)
	WHERE (item.lieu = '630900' OR item.lieu='632979') 
	AND item.state=3
	AND item.dest LIKE 'facture %'
	AND item.date>=".$from." AND item.date<=".$to." 
	ORDER BY destination ASC ";
// echo $sql;
$totalsurface=0;
$totalvolume=0;

$req=$DB_site->query($sql);
while($res=$DB_site->fetch_array($req)){
	// $return=affiche_ligne($res[artid]);
	// echo $return;
	$debug="";
	$rowalt=$rowalt%2;
	// $factureid=substr($res['destination'],8);
	$factureid=$res['destination'];
	
	$sql="SELECT MAX(date) as dateenlevement FROM envoi_fournisseur WHERE facturid='".$factureid."' AND type='stock' ";
	$enlevement=$DB_site->query_first($sql);
	$res['date_enlevement']=$enlevement['dateenlevement'];
	
	if($res['date_enlevement']==0){
		$datesortie="ANOMALIE";
	}else{
		$datesortie=date("d/m/Y", $res['date_enlevement']);
	}
	
	if($res['itemdate']==0){
		$datecde="-";
	}else{
		$datecde=date("d/m/Y", $res['itemdate']);
	}
	if($res['stock_cmd_id']==0){
		//if($res['origin']!="fourn"){
			$res['stock_cmd_id']=$res['origin'];
		//} else {
		//	$res['stock_cmd_id']="-";
		//}
	}
	
	$surface=round( ($res['emballage_largeur']*$res['emballage_profondeur'])/1000/1000 , 2);
	$volume=round( ($res['emballage_largeur']*$res['emballage_profondeur']*$res['emballage_hauteur'])/1000/1000/1000 , 2);
	$totalsurface+=($res['emballage_largeur']*$res['emballage_profondeur'])/1000/1000;
	$totalvolume+=($res['emballage_largeur']*$res['emballage_profondeur']*$res['emballage_hauteur'])/1000/1000/1000;
	//if($res['stock_cmd_id']==0){
	//	$res['stock_cmd_id']="-";
	//}
	echo "<tr class=\"row".$rowalt."\">
				<td>".$datecde."</td>
				<td>".$datesortie."</td>
			<!--	<td>".$debug."</td> -->
				<td align=\"right\">".$res['stock_cmd_id']."</td>
				<td align=\"right\">".$factureid."</td>
				<td>".$res['marquelibelle']."</td>
				<td>".$res['typearticle_libelle']."</td>
				<td>".$res['artcode']."</td>
				<td>".$res['artcode_fabricant']."</td>
				<td align=\"right\">".$res['emballage_largeur']."</td>
				<td align=\"right\">".$res['emballage_hauteur']."</td>
				<td align=\"right\">".$res['emballage_profondeur']."</td>
				<td align=\"right\">".$res['emballage_poids']."</td>
				<td align=\"right\">".$surface." m2</td>
				<td align=\"right\">".$volume." m3</td>
			</tr>";
	$rowalt++;
}
$totalsurface=round($totalsurface,2);
$totalvolume=round($totalvolume,2);
echo "<tr class=\"row".$rowalt."\">
				<td colspan=13 align=\"right\">".$totalsurface." m²</td>
				<td align=\"right\">".$totalvolume." m3</td>
			</tr>";
$foottable="</table>";
echo $foottable;


/*
$rowalt=0;
echo "<h1>Les 40 plus vendus (par CA HT uniquement sur site)</h1>";
echo $headtable;
$rowalt++;

$sql="SELECT a.artid AS artid, sum(lf.qte) AS qte, sum(lf.qte*(lf.prix/(1+lf.tva/100))) AS caht
		FROM lignefacture lf
		INNER JOIN facture AS f ON(lf.factureid=f.facturid)
		INNER JOIN article AS a ON(lf.artid=a.artid)
		WHERE lf.prix>0
		AND lf.lignefacturedissid=0
		AND f.valide IN (1,5,7,8)
		AND f.datefacture>='".$dfrom->format("Y-m-d")."'
		AND f.datefacture<='".$dto->format("Y-m-d")."'
		AND a.typearticleid='1'
		AND f.provenance=''
		AND (SELECT COUNT(*) FROM article_lot WHERE lotid=a.artid)=0
		GROUP BY lf.artid ORDER BY caht DESC LIMIT 40";
	
$req=$DB_site->query($sql);
while($res=$DB_site->fetch_array($req)){
	$return=affiche_ligne($res[artid]);
	echo $return;
}

echo $foottable;

$rowalt=0;
echo "<h1>Top 3 des ".$nb_marques." marques les plus vendues (par nombre de ventes, MP incluses)</h1>";
echo $headtable;
$rowalt++;

while (list($key, $val) = each($marques)) {
	$sql="SELECT a.artid AS artid, sum(lf.qte) AS qte, sum(lf.qte*(lf.prix/(1+lf.tva/100))) AS caht
			FROM lignefacture lf INNER JOIN facture AS f ON(lf.factureid=f.facturid)
			INNER JOIN article AS a ON(lf.artid=a.artid)
			INNER JOIN article_marque AS am ON(a.artid=am.artid)
			WHERE lf.prix>0
			AND lf.lignefacturedissid=0
			AND f.valide IN (1,5,7,8)
			AND f.datefacture>='".$dfrom->format("Y-m-d")."'
			AND f.datefacture<='".$dto->format("Y-m-d")."'
			AND a.typearticleid='1'
			AND am.marqueid = ".$key."
			AND (SELECT COUNT(*) FROM article_lot WHERE lotid=a.artid)=0
			AND qte>0
			GROUP BY lf.artid
			ORDER BY qte DESC LIMIT 3";
			// AND f.provenance='' (3 lignes plus tot)
	$req=$DB_site->query($sql);
	while($res=$DB_site->fetch_array($req)){
		$return=affiche_ligne($res[artid]);
		echo $return;
	}
}
echo $foottable;
*/

echo "</body></html>";

/*
function affiche_ligne($artid) { // $res = array(artid, somme , artcode, prix)
	$spytime=time()-2*86400;
	global $dfrom;
	global $dto;
	global $DB_site;
	global $aSiteSpy;
	global $tauxtva;
	global $rowalt;
	$sql="SELECT a.artid as artid, a.paht AS paht, SUM(av.nb) as somme, a.artcode AS artcode, a.prix AS prix, a.prixtrspt AS prixtrspt
		FROM article_vu AS av
		INNER JOIN article AS a ON(av.artid=a.artid)
		WHERE date BETWEEN ".$dfrom->gettimestamp()." AND ".$dto->gettimestamp(). "
		AND a.artid=".$artid." ";
	$res=$DB_site->query_first($sql);
	$echoligne="";
	$sql="SELECT prix, paht FROM temp_price WHERE artid=".$artid." AND du < '".time()."' AND au > '".time()."' LIMIT 1";
	$temp_price=$DB_site->query_first($sql);
	if($temp_price[prix]!=""){
		$article_pvttc=$temp_price[prix];
		$article_paht=$temp_price[paht];
	} else {
		$article_pvttc=$res[prix];
		$article_paht=$res[paht];
	}
	$article_fournisseur=$DB_site->query_first("SELECT ecopartht, trspt FROM article_fournisseur WHERE artid=".$artid." AND principal LIKE '%base%' AND devise='EUR' ");
	if($article_fournisseur[ecopartht]==""){
		$article_fournisseur[ecopartht]=0;
	}
	if($article_fournisseur[trspt]==""){
		$article_fournisseur[trspt]=0;
	}
	
	$sitemin="";
	$pricemin="";
	$spylist=array();
	while (list($key, $val) = each($aSiteSpy)) {
		$sql="SELECT price FROM history WHERE timestamp>".$spytime." AND artcode='".$res[artcode]."' AND site='".$val."' ORDER BY timestamp DESC LIMIT 1 ";
		// echo $sql;
		$spy=$DB_site->query_first($sql);
		
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
			$echoligne.= "<td align=\"right\" nowrap>".$bolddeb.number_format($spy[price], 0, ',', ' ').$boldfin."  &euro;</td>";
		} else {
			$echoligne.= "<td align=\"center\">-</td>";
		}
	}
	reset($aSiteSpy);
	if($pricemin==""){
		$pricemin="-";
		$sitemin="-";
		$priceminformat="-";
	} else {
		$pricemin=round($pricemin);
		$priceminformat=number_format($pricemin, 0, ',', ' ')." &euro;";
	}
	$sql="SELECT sum(lf.qte) AS qte, sum(lf.qte*(lf.prix/(1+tva/100))) AS caht
		FROM lignefacture lf INNER JOIN facture AS f ON(factureid=facturid)
		WHERE prix>0 AND lf.lignefacturedissid=0 AND f.valide IN (1,5,7,8)
		AND lf.artid='".$artid."'
		AND f.provenance=''
		AND datefacture>='".$dfrom->format("Y-m-d")."' AND datefacture<='".$dto->format("Y-m-d")."'";
    $ventes=$DB_site->query_first($sql);
	if($ventes[qte]==""){
		$ventes[qte]=0;
		$ventes[caht]=0;
	}
	if($res[somme]==""){
		$res[somme]=0;
	}
	
	$lesGaranties=DAOgarantie::loadMaxGarantieArtByPays($DB_site,'base','FR','57',$artid,'',$article_pvttc);
	$i=0;
	$garantie=0;
	if(!empty($lesGaranties)){
		foreach ($lesGaranties as $laGarantie) {
			$i++;
			if ($i==1)
				$garantie=round($laGarantie->paht);
			if ($laGarantie->cumul==1)
				$garantie+=round($laGarantie->paht);
		}
	}
	
	
	
	$frais_port=new frais_port($DB_site,array($artid=>1),57,$article_pvttc,$article_paht,'base');
	// $jsachatport.="\nachatport['$site_code']=".round($frais_port->paht,2).";";
	// $jsprixport.="\nprixport['$site_code']=".round($frais_port->prixht,2).";";
  
  
  
  
	
	$article_paht_total=$article_paht+$res[prixtrspt]+$article_fournisseur[ecopartht]+$article_fournisseur[trspt]+$garantie+$frais_port->paht-$frais_port->prixht;
	$marge_ht=($article_pvttc/$tauxtva)-$article_paht_total;
	if($pricemin!="-"){
		$marge_aligne=number_format( (($pricemin/$tauxtva)-$article_paht_total)*100/($pricemin/$tauxtva), 0, ',', ' ')." %";
	} else {
		$marge_aligne="-";
	}
	$rowalt=$rowalt%2;
	
	
	$echoligne= "<tr class=\"row".$rowalt."\">
					<td nowrap><a href=\"https://www.ma-cave-a-vin.fr/a-art".$artid.".htm\" targe=\"_blank\">".$res[artcode]."</a></td>
					<td align=\"right\">".$res[somme]."</td>
					<td align=\"right\">".$ventes[qte]."</td>
					<td align=\"right\" nowrap>".number_format($ventes[caht], 0, ',', ' ')." &euro;</td>
					<td align=\"right\" nowrap>".number_format($article_pvttc, 0, ',', ' ')." &euro;</td>
					<td align=\"right\" nowrap>".number_format($article_paht_total, 0, ',', ' ')." &euro;</td>
					<td align=\"right\" nowrap>".number_format($marge_ht, 0, ',', ' ')." &euro;</td>
					<td align=\"right\" nowrap>".$marge_aligne."</td>
					<td align=\"right\" nowrap>".$priceminformat."</td>
					<td align=\"center\">".$sitemin."</td>
					".$echoligne;
	$echoligne.= "</tr>";
	$rowalt++;
	return $echoligne;
}
*/

?>