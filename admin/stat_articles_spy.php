<?php

require "admin_global.php";

include "../class/DAOarticle.class.php";
include "../class/garantie.class.php";
include "../class/DAOgarantie.class.php";
include "../class/stock.class.php";
include "spy.class.php";

//Liste des sites affichés dans le tableau
$aSiteSpy = array(
	'amazon',
	'auchan',
	'dometic',
	'liebherr-electromenager',
	'magarantie5ans',
	'abribat',
	'boulanger',
	'carrefour',
	'cdiscount',
	'climadiff',
	'conforama',
	'imenager',
	'mister good deal',
	'villatech',
	'vinokado',
	'votre cave',
	'vpcboost',
	'super10count',
	'bedigital'
);

if(isset($_POST['climadiff_only']) && $_POST['climadiff_only']==1){
	unset($aSiteSpy);
	$aSiteSpy = array(
		'climadiff',
		'vinokado'
	);
}


$showtable=false;

$visibletxt=array(1=>"<img src=\"images/icon/green.png\">",0=>"<img src=\"images/icon/orange.png\">");

$exclu=array("googlebot","as29017","msn","voilabot","ahref","bingbot","acoon","google","yandex","exabot","torservers","as13285");
$exclip=array("80.12.82.212","88.184.216.175");

if(isset($_POST['from']))
    $dfrom=datetime::createfromformat("d/m/Y",$_POST['from']);
else
    $dfrom=date_create("1 month ago");
    
if(isset($_POST['to']))
    $dto=datetime::createfromformat("d/m/Y",$_POST['to']);
else
    $dto=date_create();
    
$typesrc=(isset($_POST['typesrc']) ? intval($_POST['typesrc']) : 0);    

$displaydate="";
switch($typesrc){
    case 0:
		if($order=="") $order="artcode";
		$chktypesrc0="checked";
    break;
    
    case 1:
		if($order=="") $order="vus";
		$chktypesrc1="checked";
    break;
    
    case 2:
		if($order=="") $order="ca";
		$chktypesrc2="checked";
    break;
    
    case 3:
		if($order=="") $order="artcode";
		$chktypesrc3="checked";
		$displaydate="display:none;";
		$dfrom=date_create("12 month ago 0:0");
		$dto=date_create();
    break;
}

if(isset($_POST['typearticle']) && $_POST['typearticle']!=""){
    $typearticle=intval($_POST['typearticle']);
    $showtable=true;
    $sqltypearticle=" AND a.typearticleid=".$typearticle;
}

$siteconcurrent=(isset($_POST['site']) ? $_POST['site'] : "");

if($_POST['order']!="") $order=$_POST['order'];

$spytime=time()-2*86400;
//$spytime=1451631601;
    
if(isset($_POST['valide']))
    $valide=$_POST['valide'];
else
    $valide=array("1,7","5,8");
    
$chkvalid=in_array("1,7",$valide)? "checked":"";
$chkfacture=in_array("5,8",$valide)? "checked":"";

if(isset($_POST['climadiff_only']) && $_POST['climadiff_only']==1){
	$climadiff_only_checked="checked";
} else {
	$climadiff_only_checked="";
}

$from=$dfrom->format("d/m/Y");
$to=$dto->format("d/m/Y");

$ts1week=date_create("last week 0:0")->gettimestamp();
$ts3months=date_create("3 month ago 0:0")->gettimestamp();
$ts12month=date_create("12 month ago 0:0")->gettimestamp();
$tsperiode=array("1s"=>$ts1week,"3m"=>$ts3months,"12m"=>$ts12month);
$periodes=array("1s"=>"1 sem","3m"=>"3 mois","12m"=>"12 mois");


if(isset($_POST['climadiff_only']) && $_POST['climadiff_only']==1){
	$sqlmarque=" AND am.marqueid IN(6, 12, 13, 14, 16, 20, 21, 22, 67)";
	$showtable=true;
} else {
	if(isset($marque) && $marque!=""){
		$sqlmarque=" AND am.marqueid=".$marque;
		$showtable=true;
	}
}

$req=$DB_site2->query("SELECT * FROM marque ORDER BY libelle");
while($m=$DB_site2->fetch_array($req)){
    $marques[$m['marqueid']]=$m['libelle'];
    $optmarques.="<option value=\"".$m['marqueid']."\"".($_POST['marque']==$m['marqueid']?" selected":"").">".$m['libelle']."</option>";
}

$req=$DB_site2->query("SELECT * FROM typearticle ORDER BY libelle");
while($ta=$DB_site2->fetch_array($req))
	$opttypearticle.="<option value=\"".$ta['typearticleid']."\"".($typearticle==$ta['typearticleid']?" selected":"").">".$ta['libelle']."</option>";

//Récupération des sites à afficher

$sql = "SELECT LOWER(site) AS site";
$sql .= " FROM history";
$sql .= " WHERE site IN (";
foreach($aSiteSpy as $le_site){
	$sql .= "'".strtolower($le_site)."',";
}
$sql .= "'')";
$sql .= " GROUP BY site";
$req = $DB_site2->query($sql);
// echo $sql;
while($spy = $DB_site2->fetch_array($req)){
    $spysitelist[] = $spy['site'];
    $optsites .= "<option value=\"".$spy['site']."\"".($siteconcurrent==$spy['site']?" selected":"").">".$spy['site']."</option>";
}
natcasesort($spysitelist);

if($showtable){
    $spylist=$spymini=$spyminisite=array();
    $req=$DB_site2->query("SELECT artcode,CAST( substring_index( group_concat( price ORDER BY timestamp DESC ) , ',', 1 ) AS char ) AS price, LOWER(site) AS site FROM history WHERE timestamp>".$spytime." GROUP BY artcode,site ORDER BY timestamp DESC");
	// echo "SELECT artcode,CAST( substring_index( group_concat( price ORDER BY timestamp DESC ) , ',', 1 ) AS char ) AS price,site FROM history WHERE timestamp>".$spytime." GROUP BY artcode,site ORDER BY timestamp DESC";
    while($spy=$DB_site2->fetch_array($req)){
		if($spy['price']!=""){
			$artcode=$spy['artcode'];
			if($spy['price']<$spymini[$artcode] || !isset($spymini[$artcode])){
				$spymini[$artcode]=$spy['price'];
				$spyminisite[$artcode]=$spy['site'];
			}
			$spylist[$spy['site']]=$spy['price'];
			$spylist[$artcode][$spy['site']]=$spy['price'];
		}
    }
	//print_r($spylist);

    $spy=new spy($user_info['username'],$user_info['password']);
    $spy->request("src");
    $data=$spy->getcsv();
    foreach($data as $fields){
		$site_tmp=strtolower($fields[1]);
		$spydata[$fields[0]][$site_tmp]=$fields;
    }

	$vus=array();
    $vus_periode=array();
    $sql=" SELECT artid, SUM(nb) AS somme FROM article_vu
			WHERE artid<>0 AND date BETWEEN ".$dfrom->gettimestamp()." AND ".$dto->gettimestamp()."
			GROUP BY artid ";
    $req=$DB_site2->query($sql);
    while($av=$DB_site2->fetch_array($req)){
		$vus[$av['artid']]=$av['somme'];
    }
	

    $listeprix=array();
    $sql="	SELECT a.artid,(SELECT CONCAT(CAST(prix AS CHAR),'|',CAST(prixconseilleht AS CHAR),'|',CAST(paht AS CHAR)) FROM temp_price WHERE artid=a.artid AND ".$now." BETWEEN du AND au LIMIT 1) AS temp,a_s.prix AS prix,a_s.prixconseilleht,a_s.tauxchoisi,a_s.active,a.emballage_poids,af.prix AS paht,af.ecopartht,af.trspt FROM article a
			INNER JOIN article_site a_s USING(artid)
			INNER JOIN article_fournisseur af USING(artid)
			INNER JOIN article_marque am USING(artid) 
			WHERE site_code='base'
			AND af.principal LIKE '%base%'
			AND a_s.active IN('0','1')".$sqlmarque.$sqltypearticle;
    $req=$DB_site2->query($sql);
    while($article=$DB_site2->fetch_array($req)){
	  
		if(!is_null($article['temp'])){
			list($tprix,$tprixconseilleht,$tpaht)=explode("|",$article['temp']);
			$article['prix']=$tprix;
			$article['prixconseilleht']=$tprixconseilleht;
			$article['paht']=$tpaht;
		}

		$tva=$template->tva_ref("taux".$article['tauxchoisi']);
		$prixconseille=round($article['prixconseilleht']*(1+$tva/100),2);

		$fraisport=$DB_site2->query_first("SELECT * FROM fraisport WHERE paysid=57 AND ".(1000*$article['emballage_poids'])." BETWEEN poiddebut AND poidfin");
		if($article['prix']>$fraisport['gratuit'])
			$fraisport['prix']=0;
		$fraisport['prix']=$fraisport['prix']/(1+$tva/100);

		$pahtgarantie=0;
		$garanties=DAOgarantie::loadMaxGarantieArtByPays($DB_site2,"base","FR",57,$article['artid']);
		foreach($garanties as $g)
			$pahtgarantie+=$g->paht;

		$totalachat=$article['paht']+$article['ecopartht']+$article['trspt']+$fraisport['achat']+$pahtgarantie;
		$totalvente=$article['prix']/(1+$tva/100)+$fraisport['prix'];
		$marge=$totalvente-$totalachat;
		$pcmarge=100*$marge/$totalvente;

		$listeprix[$article['artid']]=$article;
		$listeprix[$article['artid']]['prixconseille']=$prixconseille;
		$listeprix[$article['artid']]['pcremise']=($prixconseille!=0 ? round(-100*($prixconseille-$article['prix'])/$prixconseille) : 0);
		$listeprix[$article['artid']]['marge']=$marge;
		$listeprix[$article['artid']]['pcmarge']=$pcmarge;
		$listeprix[$article['artid']]['totalachat']=$totalachat;
		$listeprix[$article['artid']]['prixport']=$fraisport['prix'];
		$listeprix[$article['artid']]['tva']=$tva;
    }

    $ventes=array();
    $ventes_periode=array();
    $sql="	SELECT artid,provenance,lf.qte AS qte,lf.qte*(lf.prix/(1+tva/100)) AS caht, lf.qte*(lf.prix) AS ca,UNIX_TIMESTAMP(datefacture) AS datefacture FROM lignefacture lf
			INNER JOIN facture ON(factureid=facturid)
			WHERE prix>0
			AND lf.lignefacturedissid=0
			AND valide IN (1,5,7,8)
			AND datefacture>='".$dfrom->format("Y-m-d")."'
			AND datefacture<='".$dto->format("Y-m-d")."'";
    $req2=$DB_site2->query($sql);
    while($v=$DB_site2->fetch_array($req2)){
		$ventes[$v['artid']][$v['provenance']]['qte']+=$v['qte'];
		$ventes[$v['artid']][$v['provenance']]['caht']+=$v['caht'];
		$ventes[$v['artid']][$v['provenance']]['ca']+=$v['ca'];
		foreach($tsperiode as $periode=>$ts){
			if($v['datefacture']>=$ts){
				$ventes_periode[$v['artid']][$periode][$v['provenance']]['qte']+=$v['qte'];
				$ventes_periode[$v['artid']][$periode][$v['provenance']]['caht']+=$v['caht'];
				$ventes_periode[$v['artid']][$periode][$v['provenance']]['ca']+=$v['ca'];
			}
		}
    }
    
    $sql="	SELECT artid,provenance,lf.qte AS qte,lf.qte*(lf.prix/(1+tva/100)) AS caht, lf.qte*(lf.prix) AS ca, UNIX_TIMESTAMP(datefacture) AS datefacture FROM lignefacturediss lf
			INNER JOIN facture ON(factureid=facturid)
			WHERE prix>0
			AND valide IN (1,5,7,8)
			AND datefacture>='".$dfrom->format("Y-m-d")."'
			AND datefacture<='".$dto->format("Y-m-d")."'";
    $req2=$DB_site2->query($sql);
    while($v=$DB_site2->fetch_array($req2)){
		$ventes[$v['artid']][$v['provenance']]['qte']+=$v['qte'];
		$ventes[$v['artid']][$v['provenance']]['caht']+=$v['caht'];
		$ventes[$v['artid']][$v['provenance']]['ca']+=$v['ca'];
		foreach($tsperiode as $periode=>$ts){
			if($v['datefacture']>=$ts){
				$ventes_periode[$v['artid']][$periode][$v['provenance']]['qte']+=$v['qte'];
				$ventes_periode[$v['artid']][$periode][$v['provenance']]['caht']+=$v['caht'];
				$ventes_periode[$v['artid']][$periode][$v['provenance']]['ca']+=$v['ca'];
			}
		}
    }

    
    $price_change=$price_history=array();
    if($typesrc==3){
		$sql="SELECT artid,
			(SELECT value FROM article_history WHERE artid=ah.artid AND `key`='prix' ORDER BY date DESC limit 1,1) AS lastchange,
			(SELECT MAX(date) FROM article_history WHERE artid=ah.artid AND `key`='prix') AS datechange 
			FROM `article_history` ah 
			WHERE `key` LIKE 'prix'
			AND date>".$dfrom->gettimestamp()."
			GROUP BY artid";
		$req=$DB_site2->query($sql);
		while($ah=$DB_site2->fetch_array($req)){
			$price_change[$ah['artid']]=$ah['datechange'];
			if(preg_match("/'base' => '([0-9\.]+)'/",$ah['lastchange'],$m))
			$price_history[$ah['artid']]=floatval($m[1]);
			else
			$price_history[$ah['artid']]=floatval($ah['lastchange']);
		}
    }
    
    $stock=new stock($DB_site2);

    /*
    $sql="SELECT l.artcode,f.facturid,l.qte,l.prix,l.tva,am.marqueid,m.libelle,a.artcode_eurocave,a.active,a.catid,a.artid 
    FROM article a INNER JOIN `lignefacture` l ON (a.artid=l.artid) INNER JOIN facture f ON (f.facturid=l.factureid) INNER JOIN article_marque am ON (l.artid=am.artid) INNER JOIN marque m ON (am.marqueid=m.marqueid) 
    WHERE l.prix>=0 AND provenance IN ('') AND l.lignefacturedissid=0".$sqlvalide.$sqlfrom.$sqlto.$sqlmarque;

    $sql2="SELECT l.artcode,f.facturid,l.qte,l.prix,l.tva,am.marqueid,m.libelle,a.artcode_eurocave,a.active,a.catid,a.artid 
    FROM article a INNER JOIN `lignefacturediss` l ON (a.artid=l.artid) INNER JOIN facture f ON (f.facturid=l.factureid) INNER JOIN article_marque am ON (l.artid=am.artid) INNER JOIN marque m ON (am.marqueid=m.marqueid) 
    WHERE l.prix>=0 AND provenance IN ('')".$sqlvalide.$sqlfrom.$sqlto.$sqlmarque;
    */

    $sql="	SELECT a.artid,a.artcode,a.artcode_eurocave,am.marqueid,a_s.prix,a_s.active FROM article a
			INNER JOIN article_marque am USING(artid)
			INNER JOIN article_site a_s USING(artid)
			WHERE a_s.active IN(0,1)
			AND a_s.site_code='base'".$sqlmarque.$sqltypearticle;

    $mainlist=array();

    $cat=array();
    $plist=$pca=$pmarq=$pfab=$pactive=$pvente=array();
    $pvus_periode=$plist_periode=array();
    $req=$DB_site2->query($sql);
    while($prod=$DB_site2->fetch_array($req)){
		$addprod=false;
		
		/*$ventes=array();
		$sql="SELECT provenance,SUM(lf.qte) AS qte,SUM(lf.qte*(lf.prix/(1+tva/100))) AS caht, SUM(lf.qte*(lf.prix)) AS ca
		FROM lignefacture lf INNER JOIN facture ON(factureid=facturid)
		WHERE artid=".$prod['artid']." AND prix>0 AND lf.lignefacturedissid=0 AND valide IN (1,5,7,8) AND datefacture>='".$dfrom->format("Y-m-d")."' AND datefacture<='".$dto->format("Y-m-d")."' 
		GROUP BY provenance";
		$req2=$DB_site2->query($sql);
		while($v=$DB_site2->fetch_array($req2)){
			$ventes[$v['provenance']]=$v;
		}
		
		$sql="SELECT provenance,SUM(lf.qte) AS qte,SUM(lf.qte*(lf.prix/(1+tva/100))) AS caht, SUM(lf.qte*(lf.prix)) AS ca
		FROM lignefacturediss lf INNER JOIN facture ON(factureid=facturid)
		WHERE artid=".$prod['artid']." AND prix>0 AND valide IN (1,5,7,8) AND datefacture>='".$dfrom->format("Y-m-d")."' AND datefacture<='".$dto->format("Y-m-d")."' 
		GROUP BY provenance";
		$req2=$DB_site2->query($sql);
		while($v=$DB_site2->fetch_array($req2)){
			$ventes[$v['provenance']]['qte']+=$v['qte'];
			$ventes[$v['provenance']]['caht']+=$v['caht'];
			$ventes[$v['provenance']]['ca']+=$v['ca'];
		}*/
		
		
		switch($typesrc){
			case 0:
			case 3:
				if($siteconcurrent=="" || isset($spylist[$prod['artcode']][$siteconcurrent])) $addprod=true;
			break;
			
			case 1:
				if(isset($vus[$prod['artid']]) && $vus[$prod['artid']]>0  && ($siteconcurrent=="" || isset($spylist[$prod['artcode']][$siteconcurrent]))) $addprod=true;
			break;
			
			case 2:
				if($ventes[$prod['artid']]['']['ca']+$ventes[$prod['artid']]['mp']['ca']+$ventes[$prod['artid']]['sav']['ca']>0 && ($siteconcurrent=="" || isset($spylist[$prod['artcode']][$siteconcurrent]))) $addprod=true;
			break;
		}
		
		if($addprod){
			$artids[$prod['artcode']]=$prod['artid'];
			$plist[$prod['artcode']]+=$ventes[$prod['artid']]['']['qte'];
			$pcaht[$prod['artcode']]+=$ventes[$prod['artid']]['']['caht'];
			$pmarq[$prod['artcode']]=$marques[$prod['marqueid']];
			$pfab[$prod['artcode']]=$prod['artcode_eurocave'];
			$pactive[$prod['artcode']]=$prod['active'];
			$pvus[$prod['artcode']]=intval($vus[$prod['artid']]);
			foreach($tsperiode as $periode=>$ts){
				$pvus_periode[$periode][$prod['artcode']]=$vus_periode[$prod['artid']][$periode];
				$plist_periode[$periode][$prod['artcode']]=$ventes_periode[$prod['artid']][$periode]['']['qte'];
			}
			$pvente[$prod['artcode']]=$ventes[$prod['artid']];
			$img[$prod['artcode']]="<img src=\"../configurations/".$host."/images/produits/br/".$prod['artid'].".jpg\" width=\"30\">";
			$pspymini[$prod['artcode']]=(isset($spylist[$prod['artcode']]) ? 100*($listeprix[$prod['artid']]['prix']-min($spylist[$prod['artcode']]))/$listeprix[$prod['artid']]['prix'] : 0);
			
		}
    }
    
    switch($order){
		case "qte":
			$mainlist=$plist;
			arsort($mainlist);
			$qtesort="&nbsp;&#9660;";
		break;
		
		case (preg_match('/qte(.+)/',$order,$m)?true:false):
			$mainlist=$plist_periode[$m[1]];
			arsort($mainlist);
			${"qte".$m[1]."sort"}="&nbsp;&#9660;";
		break;

		case "ca":
			$mainlist=$pcaht;
			arsort($mainlist);
			$casort="&nbsp;&#9660;";
		break;
		
		case "marque":
			$mainlist=$pmarq;
			asort($mainlist);
			$marquesort="&nbsp;&#9650;";
		break;

		case "artcode":
			$mainlist=$plist;
			ksort($mainlist);
			$codesort="&nbsp;&#9650;";
		break;

		case "ref":
			$mainlist=$pfab;
			asort($mainlist);
		break;
		
		case "vus":
			$mainlist=$pvus;
			arsort($mainlist);
			$vussort="&nbsp;&#9660;";
		break;
		
		case (preg_match('/vus(.+)/',$order,$m)?true:false):
			$mainlist=$pvus_periode[$m[1]];
			arsort($mainlist);
			${"vus".$m[1]."sort"}="&nbsp;&#9660;";
		break;
		
		case "diffspy":
			$mainlist=$pspymini;
			natsort($mainlist);
			$mainlist=array_reverse($mainlist);
			$spyminsort="&nbsp;&#9660;";
		break;
		
		default:
			$mainlist=$pca;
			arsort($mainlist);
			$casort="&nbsp;&#9660;";
		break;
    }
}

if($_POST['export']==1)
    include("stat_articles_spy_ooo.php");
else
    include("stat_articles_spy_html.php");

?>