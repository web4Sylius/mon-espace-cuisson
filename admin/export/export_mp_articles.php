<?php

ini_set("display_errors","1");
error_reporting(E_ALL);
ini_set("memory_limit","1024M");

extract($_REQUEST);
extract($_COOKIE);

$rootpath="/var/www/vhosts/ma-cave-a-vin.fr/httpdocs/";
// $rootpath="/home/ma-cave-a-vin-2015/";
// $rootpath = "D:/wamp/www/ma-cave-a-vin/";
$rootpath="../../";

$tauxtva=1.2; // soit 20%

$host="www.ma-cave-a-vin.fr" ;
$nostat=1;
include_once($rootpath."configurations/$host/config_mysql.php");
include($rootpath."class/mysql.php");
include($rootpath."configurations/$host/config.php");

include $rootpath."class/DAOarticle.class.php";
include $rootpath."class/garantie.class.php";
include $rootpath."class/DAOgarantie.class.php";
include $rootpath."class/frais_port.php";
include $rootpath."admin/spy.class.php";
include("ooo.php");

$DB_site=new DB_Sql;
$DB_site->appname='boutique';
$DB_site->appshortname='boutique';
$DB_site->database=$DBname;
$DB_site->server=$db_server;
$DB_site->user=$DBusername;
$DB_site->password=$DBpassword;
$DB_site->connect();

$doc=new ooo();

$doc->name="export market place ".date("Y-m-d");

$sname=new ooo_style("name");
$sname->family="table-cell";

$s0=new ooo_style("title");
$s0->family="table-cell";
$s0->font_color="#000000";
$s0->font_bold="bold";
$s0->border_bottom="0.5pt solid #000000";
$s0->border_top=$s0->border_left=$s0->border_right=$s0->border_bottom;
$s0->background_color="#AAAAFF";
$s0->text_align="center";

$s1=new ooo_style("enabled1");
$s1->family="table-cell";
$s1->font_color="#000000";
$s1->border_right="0.5pt solid #000000";
$s1->border_left=$s1->border_right;

$s2=new ooo_style("enabled0");
$s2->family="table-cell";
$s2->font_color="#888888";
$s2->border_right="0.5pt solid #000000";
$s2->border_left=$s2->border_right;

$sb=new ooo_style("bottom");
$sb->family="table-cell";
$sb->font_color="#000000";
$sb->font_bold="bold";
$sb->border_bottom="0.5pt solid #000000";
$sb->border_top=$sb->border_left=$sb->border_right=$sb->border_bottom;


$dfrom1=datetime::createfromformat("d/m/Y","01/01/2017");
$dto1=datetime::createfromformat("d/m/Y","31/12/2017");

$dfrom2=datetime::createfromformat("d/m/Y","01/01/2018");
$dto2=datetime::createfromformat("d/m/Y","31/12/2018");

$dfrom3=datetime::createfromformat("d/m/Y","01/01/2019");
$dto3=datetime::createfromformat("d/m/Y","31/12/2019");


$mp=array(  "01"=>"amazon",
			"07"=>"rdc",
			"12"=>"fnac",
			"13"=>"cdiscount",
			"14"=>"redoute",
			"19"=>"conforama");
foreach ($mp as $mp_key => $mp_name) {
	$rdctable=new ooo_table($mp_name);
	$rdctable->set_col(1,"3cm");
	$rdctable->set_col(2,"5cm");
	$rdctable->set_col(3,"5cm");
	$rdctable->set_col(4,"5cm");
	$rdctable->set_col(5,"5cm");
	$rdctable->set_col(6,"5cm");
	$rdctable->set_col(7,"5cm");
	$rdctable->set_col(8,"5cm");
	$rdctable->set_col(9,"5cm");
	$rdctable->set_col(10,"5cm");
	$rdctable->set_col(11,"5cm");
	$rdctable->set_col(12,"5cm");
	$rdctable->set_col(13,"5cm");
	$rdctable->set_col(14,"5cm");

	$c0=new ooo_cell($mp_name);
	$c0->style="name";

	$rdctable->set_row(1,"1cm");
	$rdctable->addcell(1,1,$c0);

	$c1=new ooo_cell("Artcode");
	$c1->style="title";
	$c2=new ooo_cell("Lib Art");
	$c2->style="title";
	$c3=new ooo_cell("Lib Cat");
	$c3->style="title";
	$c4=new ooo_cell("Actif");
	$c4->style="title";
	$c5=new ooo_cell("Ref fabriquant");
	$c5->style="title";
	$c6=new ooo_cell("PV TTC");
	$c6->style="title";
	$c7=new ooo_cell("PA HT");
	$c7->style="title";
	$c8=new ooo_cell("Marge HT EUR (fdp et ecopart inclus - commission marketplace exclue)");
	$c8->style="title";
	$c9=new ooo_cell("CA N-2");
	$c9->style="title";
	$c10=new ooo_cell("CA N-1");
	$c10->style="title";
	$c11=new ooo_cell("CA N");
	$c11->style="title";
	$c12=new ooo_cell("Nb ventes N-2");
	$c12->style="title";
	$c13=new ooo_cell("Nb ventes N-1");
	$c13->style="title";
	$c14=new ooo_cell("Nb ventes N");
	$c14->style="title";


	$doc->addstyle($sname);
	$doc->addstyle($s0);
	$doc->addstyle($s1);
	$doc->addstyle($s2);
	$doc->addstyle($sb);

	$rdctable->set_row(2,"1cm");
	$rdctable->addcell(1,2,$c1);
	$rdctable->addcell(2,2,$c2);
	$rdctable->addcell(3,2,$c3);
	$rdctable->addcell(4,2,$c4);
	$rdctable->addcell(5,2,$c5);
	$rdctable->addcell(6,2,$c6);
	$rdctable->addcell(7,2,$c7);
	$rdctable->addcell(8,2,$c8);
	$rdctable->addcell(9,2,$c9);
	$rdctable->addcell(10,2,$c10);
	$rdctable->addcell(11,2,$c11);
	$rdctable->addcell(12,2,$c12);
	$rdctable->addcell(13,2,$c13);
	$rdctable->addcell(14,2,$c14);

	$line=2;
	// $prevartcode="";
	$curstyle=1;



	$sql  = " SELECT a.artid, a.artcode, a.catid, a.libelleFR AS libart, a.artcode_eurocave, a.prix, a.paht, a.prixtrspt AS prixtrspt, a.active,
			  c.libelleFR AS libcat, mpc.enabled";
	$sql .= " FROM market_place_champs mpc";
	$sql .= " INNER JOIN article a ON (a.artcode = mpc.artcode)";
	$sql .= " INNER JOIN categorie c ON (c.catid = a.catid)";
	$sql .= " WHERE 1";
	$sql .= " AND mpc.market_place = '".$mp_name."' ";
	$sql .= " AND mpc.enabled = 1";
	$sql .= " GROUP BY a.artid";
	$sql .= " ORDER BY a.catid, a.artcode";

	$requete = $DB_site->query($sql);
	while($row = $DB_site->fetch_array($requete)){
		//if($row['artcode']!=$prevartcode)
		$curstyle=abs($curstyle-1);
		
		$artid=$row[artid];
		$article_pvttc=$row['prix'];
		$article_paht=$row[paht];
		
		
		$article_fournisseur=$DB_site->query_first("SELECT ecopartht, trspt FROM article_fournisseur WHERE artid=".$artid." AND principal LIKE '%base%' AND devise='EUR' ");
		if($article_fournisseur[ecopartht]==""){
			$article_fournisseur[ecopartht]=0;
		}
		if($article_fournisseur[trspt]==""){
			$article_fournisseur[trspt]=0;
		}
		
		$frais_port=new frais_port($DB_site,array($artid=>1),57,$article_pvttc,$article_paht,'base');
	  
		$article_paht_total=$article_paht+$res[prixtrspt]+$article_fournisseur[ecopartht]+$article_fournisseur[trspt]+$garantie+$frais_port->paht-$frais_port->prixht;
		$marge_ht=($article_pvttc/$tauxtva)-$article_paht_total;
		
		
		$sql="SELECT sum(lf.qte) AS qte, sum(lf.qte*(lf.prix/(1+tva/100))) AS caht
			FROM lignefacture lf INNER JOIN facture AS f ON(factureid=facturid)
			WHERE prix>0 AND lf.lignefacturedissid=0 AND f.valide IN (1,5,7,8)
			AND lf.artid='".$artid."'
			AND f.provenanceid='".$mp_key."'
			AND datefacture>='".$dfrom1->format("Y-m-d")."' AND datefacture<='".$dto1->format("Y-m-d")."'";
		$ventes1=$DB_site->query_first($sql);
		if($ventes1[qte]==""){
			$ventes1[qte]=0;
			$ventes1[caht]=0;
		}
		$sql="SELECT sum(lf.qte) AS qte, sum(lf.qte*(lf.prix/(1+tva/100))) AS caht
			FROM lignefacture lf INNER JOIN facture AS f ON(factureid=facturid)
			WHERE prix>0 AND lf.lignefacturedissid=0 AND f.valide IN (1,5,7,8)
			AND lf.artid='".$artid."'
			AND f.provenanceid='".$mp_key."'
			AND datefacture>='".$dfrom2->format("Y-m-d")."' AND datefacture<='".$dto2->format("Y-m-d")."'";
		$ventes2=$DB_site->query_first($sql);
		if($ventes2[qte]==""){
			$ventes2[qte]=0;
			$ventes2[caht]=0;
		}
		$sql="SELECT sum(lf.qte) AS qte, sum(lf.qte*(lf.prix/(1+tva/100))) AS caht
			FROM lignefacture lf INNER JOIN facture AS f ON(factureid=facturid)
			WHERE prix>0 AND lf.lignefacturedissid=0 AND f.valide IN (1,5,7,8)
			AND lf.artid='".$artid."'
			AND f.provenanceid='".$mp_key."'
			AND datefacture>='".$dfrom3->format("Y-m-d")."' AND datefacture<='".$dto3->format("Y-m-d")."'";
		$ventes3=$DB_site->query_first($sql);
		if($ventes3[qte]==""){
			$ventes3[qte]=0;
			$ventes3[caht]=0;
		}
		
		if($row['active']==1){
			$actif="oui";
		} else {
			$actif="non";
		}
	

		$rdctable->set_row($line+1,"0.5cm");
		$c1=new ooo_cell($row['artcode']);
		$c1->style="enabled".$curstyle;
		$c2=new ooo_cell($row['libart']); // utf8_encode() ?
		$c2->style="enabled".$curstyle;
		$c3=new ooo_cell($row['libcat']);
		$c3->style="enabled".$curstyle;
		
		$c4=new ooo_cell($actif); ////////////////////////// la colonne rajoutée .................
		$c4->style="enabled".$curstyle;
		
		$c5=new ooo_cell($row['artcode_eurocave']);
		$c5->style="enabled".$curstyle;
		$c6=new ooo_cell(number_format($article_pvttc, 2, ',', ''),"","currency");
		$c6->style="enabled".$curstyle;
		$c7=new ooo_cell(number_format($article_paht, 2, ',', ''),"","currency");
		$c7->style="enabled".$curstyle;
		$c8=new ooo_cell(number_format($marge_ht, 2, ',', ''),"","currency"); // marge
		$c8->style="enabled".$curstyle;
		
		$c9=new ooo_cell(number_format($ventes1[caht], 2, ',', ''),"","currency"); // CA N-2
		$c9->style="enabled".$curstyle;
		$c10=new ooo_cell(number_format($ventes2[caht], 2, ',', ''),"","currency"); // CA N-1
		$c10->style="enabled".$curstyle;
		$c11=new ooo_cell(number_format($ventes3[caht], 2, ',', ''),"","currency"); // CA N
		$c11->style="enabled".$curstyle;
		
		$c12=new ooo_cell(number_format($ventes1[qte], 2, ',', ''),"","currency"); // CA N-2
		$c12->style="enabled".$curstyle;
		$c13=new ooo_cell(number_format($ventes2[qte], 2, ',', ''),"","currency"); // CA N-1
		$c13->style="enabled".$curstyle;
		$c14=new ooo_cell(number_format($ventes3[qte], 2, ',', ''),"","currency"); // CA N
		$c14->style="enabled".$curstyle;
		
		$rdctable->addcell(1,$line+1,$c1);
		$rdctable->addcell(2,$line+1,$c2);
		$rdctable->addcell(3,$line+1,$c3);
		$rdctable->addcell(4,$line+1,$c4);
		$rdctable->addcell(5,$line+1,$c5);
		$rdctable->addcell(6,$line+1,$c6);
		$rdctable->addcell(7,$line+1,$c7);
		$rdctable->addcell(8,$line+1,$c8);
		$rdctable->addcell(9,$line+1,$c9);
		$rdctable->addcell(10,$line+1,$c10);
		$rdctable->addcell(11,$line+1,$c11);
		$rdctable->addcell(12,$line+1,$c12);
		$rdctable->addcell(13,$line+1,$c13);
		$rdctable->addcell(14,$line+1,$c14);
		
		
		$line++;
		//$prevartcode=$row['artcode'];
	}

	$doc->addtable($rdctable);


}

$doc->generate();

?>