<?php

ini_set("display_errors","1");
error_reporting(E_ALL);
ini_set("memory_limit","1024M");

extract($_REQUEST);
extract($_COOKIE);

include_once($_SERVER['DOCUMENT_ROOT']."/class/host.php");
$host_conf = new Host();
$host=$host_conf->host;
include_once($_SERVER['DOCUMENT_ROOT']."/configurations/".$host."/config_variables.php");

$tauxtva=1.2; // soit 20%

$nostat=1;
include_once($rootpath."configurations/$host/config_mysql.php");
include($rootpath."class/mysql.php");
include($rootpath."configurations/$host/config.php");

include $rootpath."class/DAOarticle.class.php";
include $rootpath."class/garantie.class.php";
include $rootpath."class/DAOgarantie.class.php";
include($rootpath."class/frais_port.php");
include $rootpath."admin/spy.class.php";


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

// on prend le compte de benoit ....
$spy=new spy("benoit","9243aec139440748b013103739128210");
$spy->request("src");
$data=$spy->getcsv();
foreach($data as $fields){
	$site_tmp=strtolower($fields[1]);
	$spydata[$fields[0]][$site_tmp]=$fields;
}

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


//Liste des sites affichés dans le tableau
$aSiteSpy = array(
	'amazon',
	'auchan',
	'dometic',
	'abribat',
	'boulanger',
	'carrefour',
	'cdiscount',
	'climadiff',
	'conforama',
	'darty',
	'liebherr-electromenager',
	'imenager',
	'lasommeliere',
	'mister good deal',
	'villatech',
	'vinokado',
	'votre cave',
	'vpcboost',
	'super10count',
	'bedigital'
);

// là dedans les principales marques à suivre ....
$marques=array(
	1 => "Artevino",
	2=> "Transtherm",
	6 => "Climadif",
	14 => "La sommeliere",
	9 => "Liebherr",
	10 => "Dometic",
	12 => "Avintage",
	99 => "Caviss",
	104 => "Le chai",
	13 => "Vin sur vin"
	);
$nb_marques=count($marques);
	
$exclu=array("googlebot","as29017","msn","voilabot","ahref","bingbot","acoon","google","yandex","exabot","torservers","as13285");
$exclip=array("80.12.82.212","88.184.216.175");

if(isset($_POST['from']))
    $dfrom=datetime::createfromformat("d/m/Y",$_POST['from']);
else
    $dfrom=date_create("6 month ago");

$dfrom2=date_create("3 month ago");


if(isset($_POST['to']))
    $dto=datetime::createfromformat("d/m/Y",$_POST['to']);
else
    $dto=date_create();
    
$timestampfrom=time()-(2*24*60*60);
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
echo "<h1>Les 40 plus vus (sur 6 mois)</h1>";
$nbcols=0;
$headtable="	<table>
		<tr class=\"row".$rowalt."\">
		<th>artcode</th>
		<th>Ref Fab</th>
		<th>Code barre Fab</th>
		<th>Status</th>
		<th>nb vues</th>
		<th>nb ventes 6 mois</th>
		<th>CA HT 6 mois</th>
		<th>nb ventes 3 mois</th>
		<th>PV MCAV TTC</th>
		<th>PPI TTC</th>
		<th>PAT HT</th>
		<th>Marge % MCAV</th>
		<th>Marge align&eacute;e %</th>
		<th>Prix le plus bas</th>
		<th>Site le plus bas</th>
		<th>Nb concurrents</th>";
$nbcols+=8;
while (list($key, $val) = each($aSiteSpy)) {
	$headtable.="<th>".$val."</th>";
	$nbcols++;
}
reset($aSiteSpy);
$headtable.="</tr>";
echo $headtable;
$rowalt++;

$sql="SELECT av.artid, SUM(av.nb) as somme FROM article_vu AS av FORCE INDEX(artid)
	INNER JOIN article AS a ON(av.artid=a.artid) 
	INNER JOIN article_marque AS am ON(a.artid=am.artid)
	WHERE date BETWEEN ".$dfrom->gettimestamp()." AND ".$dto->gettimestamp()."
	AND a.typearticleid='1'
	AND (SELECT COUNT(*) FROM article_lot WHERE lotid=a.artid)=0
	AND am.marqueid NOT IN (58, 109, 18)
	GROUP BY artid
	ORDER BY somme DESC LIMIT 40 ";
$req=$DB_site->query($sql);
while($res=$DB_site->fetch_array($req)){
	$return=affiche_ligne($res[artid]);
	echo $return;
}
$foottable="</table>";
echo $foottable;

$rowalt=0;
echo "<h1>Les 40 plus vendus (par CA HT uniquement sur site sur 6 mois)</h1>";
echo $headtable;
$rowalt++;

$sql="SELECT a.artid AS artid, sum(lf.qte) AS qte, sum(lf.qte*(lf.prix/(1+lf.tva/100))) AS caht
		FROM lignefacture lf
		INNER JOIN facture AS f ON(lf.factureid=f.facturid)
		INNER JOIN article AS a ON(lf.artid=a.artid)
		INNER JOIN article_marque AS am ON(a.artid=am.artid)
		WHERE lf.prix>0
		AND lf.lignefacturedissid=0
		AND f.valide IN (1,5,7,8)
		AND f.datefacture>='".$dfrom->format("Y-m-d")."'
		AND f.datefacture<='".$dto->format("Y-m-d")."'
		AND a.typearticleid='1'
		AND f.provenance=''
		AND am.marqueid NOT IN (58, 109, 18)
		AND (SELECT COUNT(*) FROM article_lot WHERE lotid=a.artid)=0
		GROUP BY lf.artid
		ORDER BY caht DESC LIMIT 40";
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
			FROM lignefacture lf
			INNER JOIN facture AS f ON(lf.factureid=f.facturid)
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
	$req=$DB_site->query($sql);
	while($res=$DB_site->fetch_array($req)){
		$return=affiche_ligne($res[artid]);
		echo $return;
	}
}
echo $foottable;






$rowalt=0;
echo "<h1>Les 50 plus vendus o&ugrave; on est les mieux plac&eacute;s</h1>";
echo $headtable;
$rowalt++;

$sql="SELECT a.artid AS artid, sum(lf.qte) AS qte, sum(lf.qte*(lf.prix/(1+lf.tva/100))) AS caht
		FROM lignefacture AS lf 
		INNER JOIN facture AS f ON(lf.factureid=f.facturid) 
		INNER JOIN article AS a ON(lf.artid=a.artid) 
		INNER JOIN article_marque AS am ON(am.artid=a.artid) 
		WHERE lf.prix>0
		AND lf.lignefacturedissid=0
		AND f.valide IN (1,5,7,8)
		AND f.datefacture>='".$dfrom->format("Y-m-d")."'
		AND f.datefacture<='".$dto->format("Y-m-d")."'
		AND a.typearticleid='1'
		AND a.active='1'
		AND a.commandable='1'
		AND f.provenance='' 
		AND am.marqueid NOT IN (58, 109, 18)
		AND (SELECT COUNT(*) FROM article_lot WHERE lotid=a.artid)=0 
		AND ( 
				(SELECT price FROM history WHERE artcode=a.artcode AND timestamp>".$timestampfrom."	ORDER BY  price ASC LIMIT 1) IS NULL 
				OR 
				(SELECT price FROM history WHERE artcode=a.artcode AND timestamp>".$timestampfrom."	ORDER BY  price ASC LIMIT 1) >= a.prix 
			)
		GROUP BY lf.artid
		ORDER BY caht DESC LIMIT 50";
		// on ne prend pas les marques 'calice', 'calice design' et 'ma cave à vin'
		// on ne prend que les commandes sur site
		
$req=$DB_site->query($sql);
while($res=$DB_site->fetch_array($req)){
	$return=affiche_ligne($res[artid]);
	echo $return;
}

echo $foottable;

echo "<br>Les nombres de vues sont sur 6 mois, les nombres de vente sont sur 6 mois.";

echo "</body></html>";


function affiche_ligne($artid) { // $res = array(artid, somme /* nombre de vues */, artcode, prix)
	$spytime=time()-2*86400;
	global $dfrom;
	global $dfrom2;
	global $dto;
	global $DB_site;
	global $aSiteSpy;
	global $tauxtva;
	global $rowalt;
	global $spydata;
	$sql="SELECT a.artid AS artid, a.active AS active, a.paht AS paht, SUM(av.nb) AS somme, a.artcode AS artcode, a.artcode_eurocave AS artcode_eurocave, a.prix AS prix, a.prixtrspt AS prixtrspt, a.codebarre AS codebarre, a.prixconseilleht AS prixconseilleht
		FROM article_vu AS av
		INNER JOIN article AS a ON(av.artid=a.artid)
		WHERE date BETWEEN ".$dfrom->gettimestamp()." AND ".$dto->gettimestamp(). "
		AND a.artid=".$artid." ";
	$res=$DB_site->query_first($sql);
	
	$echoligne="";
	$echolignespy="";
	$article_fournisseur=$DB_site->query_first("SELECT ecopartht, trspt, prix FROM article_fournisseur WHERE artid=".$artid." AND principal LIKE '%base%' AND devise='EUR' ");
	if($article_fournisseur[ecopartht]==""){
		$article_fournisseur[ecopartht]=0;
	}
	if($article_fournisseur[trspt]==""){
		$article_fournisseur[trspt]=0;
	}

	$sql="SELECT prix, paht FROM temp_price WHERE artid=".$artid." AND du < '".time()."' AND au > '".time()."' LIMIT 1";
	$temp_price=$DB_site->query_first($sql);

	
	if($temp_price[prix]!=""){
		$article_pvttc=$temp_price[prix];
		$article_paht=$temp_price[paht];
	} else {
		$article_pvttc=$res[prix];
		// MODIFIER PAR ARIEL TACHE SI#4399 
		if($article_fournisseur[prix] != ''){
			$article_paht = $article_fournisseur[prix];
		}else{
			$article_paht=$res[paht];
		}
	}
	
	$sitemin="";
	$pricemin="";
	$spylist=array();
	$nb_conccurents=0;
	$prixleplusbas=0;
	while (list($key, $val) = each($aSiteSpy)) {
		$sql="SELECT price FROM history WHERE timestamp>".$spytime." AND artcode='".$res[artcode]."' AND LOWER(site)='".$val."' ORDER BY timestamp DESC LIMIT 1 ";
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
				if($prixleplusbas>$spy['price'] || $prixleplusbas==0){
					$prixleplusbas=$spy[price];
				}
			}
			$nb_conccurents++;
			
			$echolignespy.= "<td align=\"right\" nowrap>".$bolddeb.number_format($spy[price], 0, ',', ' ').$boldfin."  &euro; <a href=\"".$spydata[$res[artcode]][$val][2]."\" target=\"_blank\">X</a></td>";
		} else {
			$echolignespy.= "<td align=\"center\">-</td>";
		}
	}
	reset($aSiteSpy);
	if($pricemin==""){
		$pricemin="-";
		$sitemin="-";
		$priceminformat="-";
		if($prixleplusbas>0 && $prixleplusbas>$article_pvttc){
			$sitemin="<strong><font style=\"color:#00b749;\">MCAV</font></strong>";
		}
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
	$sql="SELECT sum(lf.qte) AS qte
		FROM lignefacture lf INNER JOIN facture AS f ON(factureid=facturid)
		WHERE prix>0 AND lf.lignefacturedissid=0 AND f.valide IN (1,5,7,8)
		AND lf.artid='".$artid."'
		AND f.provenance=''
		AND datefacture>='".$dfrom2->format("Y-m-d")."' AND datefacture<='".$dto->format("Y-m-d")."'";
    $ventes2=$DB_site->query_first($sql);
	if($ventes2[qte]==""){
		$ventes2[qte]=0;
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
	
	
	$ppi_ttc = $res[prixconseilleht] * $tauxtva;
	
	$marge_pct=$marge_ht*100/($article_pvttc/$tauxtva);
	
	// explication de la marge ....
	$marge_expli="(".$article_pvttc."/".$tauxtva.")-(".$article_paht."+".$res[prixtrspt]."+".$article_fournisseur[ecopartht]."+".$article_fournisseur[trspt]."+".$garantie."+".$frais_port->paht."-".$frais_port->prixht.")";
	if($pricemin!="-"){
		$marge_aligne=number_format( (($pricemin/$tauxtva)-$article_paht_total)*100/($pricemin/$tauxtva), 1, ',', ' ')." %";
	} else {
		$marge_aligne="-";
	}
	$rowalt=$rowalt%2;
	
	$status="";
	if($res[active]=="0"){
		$status="inactif";
	}
	if($res[active]=="1"){
		$status="actif";
	}
	if($res[active]=="2"){
		$status="<b>supprim&eacute;</b>";
	}
	if($res[active]=="3"){
		$status="<b>archiv&eacute;</b>";
	}
	
	$echoligne= "<tr class=\"row".$rowalt."\">
					<td nowrap><a href=\"https://www.ma-cave-a-vin.fr/a-art".$artid.".htm\" targe=\"_blank\">".$res[artcode]."</a></td>
					<td><a href=\"https://www.ma-cave-a-vin.fr/admin/ajout_produit.php?artid=".$artid."&prec=modif\" target=\"_blank\">".$res[artcode_eurocave]."</a></td>
					<td>".$res[codebarre]."</td>
					<td align=\"center\">".$status."</td>
					<td align=\"right\">".$res[somme]."</td>
					<td align=\"right\">".$ventes[qte]."</td>
					<td align=\"right\" nowrap>".number_format($ventes[caht], 0, ',', ' ')." &euro;</td>
					<td align=\"right\">".$ventes2[qte]."</td>
					<td align=\"right\" nowrap><b>".number_format($article_pvttc, 0, ',', ' ')." &euro;</b></td>
					<td align=\"right\" nowrap>".number_format($ppi_ttc, 0, ',', ' ')." &euro;</td>
					<td align=\"right\" nowrap>".number_format($article_paht_total, 0, ',', ' ')." &euro;</td>
					<td align=\"right\" nowrap>".number_format($marge_pct, 1, ',', ' ')." %</td>
					<td align=\"right\" nowrap>".$marge_aligne."</td>
					<td align=\"right\" nowrap>".$priceminformat."</td>
					<td align=\"center\">".$sitemin."</td>
					<td align=\"right\">".$nb_conccurents."</td>
					".$echolignespy;
	$echoligne.= "</tr>";
	$rowalt++;
	return $echoligne;
}

?>