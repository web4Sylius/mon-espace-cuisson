<?php

ini_set("display_errors","1");
error_reporting(E_ALL);
ini_set("memory_limit","1024M");
set_time_limit(600);




extract($_REQUEST);
extract($_COOKIE);

if(isset($_SERVER['SERVER_NAME']) && strpos($_SERVER['SERVER_NAME'], 'localhost') !== false){ // LOCAL
    $rootpath   = "d:/wamp/www/ma-cave-a-vin/"; 
} else if(isset($_SERVER['SERVER_NAME']) && strpos($_SERVER['SERVER_NAME'], 'test2015') !== false) {
    $rootpath   = "/home/ma-cave-a-vin-2015/";
} else if(isset($_SERVER['SERVER_NAME']) && strpos($_SERVER['SERVER_NAME'], 'test') !== false) {
    $rootpath   = $_SERVER['DOCUMENT_ROOT']."/";
} else {
    $rootpath   = "/var/www/vhosts/ma-cave-a-vin.fr/httpdocs/";
}
if(!isset($_SERVER['HTTP_HOST'])) $_SERVER['HTTP_HOST']="www.ma-cave-a-vin.fr";
include_once($rootpath."class/host.php");
$host_conf = new Host();
$host=$host_conf->host;

$host="www.ma-cave-a-vin.fr" ;
$nostat=1;
include_once($rootpath."configurations/$host/config_mysql.php");
include($rootpath."class/mysql.php");
include($rootpath."configurations/$host/config.php");
include($rootpath."class/template.class.php");
include($rootpath."class/devises.class.php");
include_once($rootpath."class/fonctions.php");
include_once($rootpath."class/email.php");
include($rootpath."admin/export/ooo.php");
include($rootpath."class/ccstat.class.php");
include($rootpath."admin/marge.php");
include($rootpath."class/categorie.class.php");
include($rootpath."class/facture.class.php");
include($rootpath."class/frais_port.php");
include($rootpath."class/DAOgarantie.class.php");

$destinataire="adv@ma-cave-a-vin.fr";
$destinataire="webmaster@ma-cave-a-vin.fr";

$DB_site=new DB_Sql;
$DB_site->appname='boutique';
$DB_site->appshortname='boutique';
$DB_site->database=$DBname;
$DB_site->server=$db_server;
$DB_site->user=$DBusername;
$DB_site->password=$DBpassword;
$DB_site->connect();

$template=new template("FR",$DB_site);


if (isset($admin_username) && $admin_username != "") {
    if($admin_username!="9d3bb895f22bf0afa958d68c2a58ded7"){
      $ip=$_SERVER['REMOTE_ADDR'];
      $acces=$DB_site->query_first("SELECT * FROM acces_admin WHERE ip='$ip' AND user='$admin_username' AND connecte=1 ORDER BY timestamp DESC LIMIT 0,1");
      if($acces['timestamp']==false || $acces['timestamp']<time()-3600){
         exit;
      }else{
        $user_info=$DB_site->query_first("SELECT * FROM parametresgeneraux WHERE username = '$admin_username'");
        if ($user_info[username] != $admin_username) exit;
      }
    }
}else
  exit;



$articles=array();
$parentcavecatid=array(1,2,13,3,7);
$cavecatid=$parentcavecatid;
$requete=$DB_site->query("SELECT catid FROM categorie WHERE parentid IN (".implode(',',$parentcavecatid).")");
while($c=$DB_site->fetch_array($requete)) $cavecatid[]=$c['catid'];

$doc=new ooo();

if(!isset($_GET['mode'])){
  $m1s=time()-86400*7;
  $m1s=mktime(0,0,0,date("m",$m1s),date("d",$m1s),date("Y",$m1s));
  while(date("w",$m1s)!=1) $m1s-=86400;
  $fins=$m1s+86400*7-1;
  $doc->name="Ma Cave a vin semaine ".date("W",$m1s);
  $txttotal="Semaine ".date("W",$m1s);
}elseif($_GET['mode']=="month"){
  $doc->name="Ma Cave a Vin ".date("Y-m",$_GET['ts']);
  $txttotal=date("m/Y",$_GET['ts']);
  $m1s=mktime(0,0,0,date("m",$_GET['ts']),1,date("Y",$_GET['ts']));
  $fins=$m1s+86400*date("t",$_GET['ts']);
}elseif($_GET['mode']=="week"){
  $m1s=$_GET['ts'];
  $m1s=mktime(0,0,0,date("m",$m1s),date("d",$m1s),date("Y",$m1s));
  while(date("w",$m1s)!=1) $m1s-=86400;
  $fins=$m1s+86400*7-1;
  $doc->name="Ma Cave a vin semaine ".date("W",$m1s);
  $txttotal="Semaine ".date("W",$m1s);
}elseif($_GET['mode']=="year"){
  $m1s=$_GET['ts'];
  $m1s=mktime(0,0,0,1,1,date("Y",$m1s));
  $fins=$m1s+86400*(365+date("L",$m1s))-1;
  $doc->name="Ma Cave a vin année ".date("Y",$m1s);
  $txttotal="Année ".date("Y",$m1s);
}elseif($_GET['mode']=="free"){
  $m1s=mktime(0,0,0,date("m",$_GET['ts']),date("d",$_GET['ts']),date("Y",$_GET['ts']));
  $fins=mktime(0,0,0,date("m",$_GET['ts2']),date("d",$_GET['ts2']),date("Y",$_GET['ts2']));
  $doc->name="Ma Cave à vin ".date("Y-m-d",$_GET['ts'])."-".date("Y-m-d",$_GET['ts2']);
  $txttotal="Total";
}

$categorie=new categorie($DB_site);


$t1=new ooo_table("Cde enregistrées");
$bold=new ooo_style("bold");
$bold->font_weight="bold";
$bold->text_align="center";
$bold->family="table-cell";
$bold->setborders("0.5pt solid #000000");

$left=new ooo_style("left");
$left->family="table-cell";
$left->setborders("0.5pt solid #000000");

$right=new ooo_style("right");
$right->text_align="end";
$right->family="table-cell";
$right->setborders("0.5pt solid #000000");

$bright=new ooo_style("bright");
$bright->text_align="end";
$bright->family="table-cell";
$bright->setborders("0.5pt solid #000000");
$bright->font_weight="bold";

$center=new ooo_style("center");
$center->text_align="center";
$center->family="table-cell";
$center->setborders("0.5pt solid #000000");

$red=new ooo_style("red");
$red->family="table-cell";
$red->font_color="#FF0000";
$red->text_align="end";
$red->setborders("0.5pt solid #000000");

$nm1=clone($right);
$nm1->name="nm1";
$nm1->background_color="#DCDCDC";

$doc->addstyle($bold);
$doc->addstyle($left);
$doc->addstyle($right);
$doc->addstyle($bright);
$doc->addstyle($center);
$doc->addstyle($red);
$doc->addstyle($nm1);

$t1->set_col(3,"1.5cm");
$t1->set_col(10,"4cm");
$t1->set_col(12,"5cm");
$t1->set_col(14,"1cm");
$t1->set_row(1,"1cm");

$row=array();
$row[]=new ooo_cell("Date","bold");
$row[]=new ooo_cell("Heure","bold");
$row[]=new ooo_cell("N cde","bold");
$row[]=new ooo_cell("N cde cdiscount","bold");
$row[]=new ooo_cell("Comm. cdiscount","bold");
$row[]=new ooo_cell("Montant €TTC","bold");
$row[]=new ooo_cell("Montant €HT","bold");
$row[]=new ooo_cell("Dont Montant\nCaves €HT","bold");
$row[]=new ooo_cell("Qté cave","bold");
$row[]=new ooo_cell("Qté autres","bold");
$row[]=new ooo_cell("Offre","bold");
$row[]=new ooo_cell("Code\npromo","bold");
$row[]=new ooo_cell("Extension\ngarantie","bold");
$row[]=new ooo_cell("Installation\nspécialisée","bold");
$row[]=new ooo_cell("Passage commande\njour inscription","bold");
$row[]=new ooo_cell("Pays facturation","bold");
$row[]=new ooo_cell("Département","bold");
$row[]=new ooo_cell("Région","bold");
$row[]=new ooo_cell("Mode de paiement","bold");
$row[]=new ooo_cell("Page entrée","bold");
$row[]=new ooo_cell("Referer","bold");
$row[]=new ooo_cell("Provenance","bold");
$row[]=new ooo_cell("Type commande","bold");
$row[]=new ooo_cell("Commercial","bold");
$row[]=new ooo_cell("Code partenaire","bold");
$t1->addrow(1,$row);

$moyens=array();
$requete=$DB_site->query("SELECT * FROM moyenpaiement");
while($m=$DB_site->fetch_array($requete)) $moyens[$m['moyenid']]=$m['libelleFR'];

$marques=$DB_site->query("SELECT * FROM marque ORDER BY libelle");
while($m=$DB_site->fetch_array($marques)){
  $marque[$m['marqueid']]=$m['libelle'];
  $tmmemory[$m['marqueid']]=0;
}

$typearticles=$DB_site->query("SELECT * FROM typearticle ORDER BY typearticleid ASC");
while($t=$DB_site->fetch_array($typearticles)){
  $typearticle[$t['typearticleid']]=$t['libelle'];
}

$promos=array();
$req=$DB_site->query("SELECT artid,site_code FROM promo WHERE 1");
while($p=$DB_site->fetch_array($req)){
  $promos[$p['site_code']][]=$p['artid'];
}




class bmstat extends ccstat { 
	function get_visit2($user,$time,$tbl="stat",$ip_acilyon=true){
		global $DB_site;
	  $returns=array();
	  $sql = "SELECT *";
	  $sql .= " FROM ".$tbl;
	  $sql .= " WHERE user = ".$user;
	  $sql .= " AND timestamp < ".$time;
	  if(!$ip_acilyon) $sql .= " AND ip != '80.12.82.212'";
	  $sql .= " ORDER BY timestamp DESC";
	  $requete=mysql_query($sql);
	  if($requete==false || mysql_num_rows($requete)==0) return false;
	  while($row=mysql_fetch_object($requete)){
		$return=new visit();
		$return->statid=$row->id;
		$return->timestamp=$row->timestamp;
		$return->provenance=$row->referant;
		// $return->mrprovenance=$this->mrech($row->referant);
		$sql = " SELECT page, timestamp FROM stat_page WHERE statid='".$row->id."' UNION SELECT page, timestamp FROM stat_page_arch2 WHERE statid='".$row->id."' order by timestamp ASC limit 1 ";
		// echo $sql;
		$statpages=$DB_site->query($sql); 
		while($statpage=$DB_site->fetch_array($statpages)){
			$return->mrprovenance="naturel";
			if($row->referant==""){
				$return->mrprovenance="direct";
			}
			if(strpos($statpage[page],"gclid")>-1){
				// adwords
				$return->mrprovenance="adwords";
			}
			$return->entrance=$statpage[page];
		}
		//$return->mrprovenance=$this->mrech($row->referant);
		$return->os=$this->os_name($row->agent);
		$return->browser=$this->browser_name($row->agent);
		$return->fai=$this->getfai($row->host);
		$return->ip=$row->ip;
		$return->country=$this->ip2country($this->ip);
		$return->pages=$this->getpages($row->id);
		$returns[]=$return;
	  }
	 // var_dump($returns);
	  return $returns;
	  
	}
}


$i=2;
$ccstat=new bmstat();
// $ccstat->temporary_table("(SELECT * FROM stat_arch WHERE user>0) UNION (SELECT * FROM stat WHERE user>0)");
$totaljour=array();
$totalhtjour=array();
$totalnbjour=array();
$totalmarque=$tmmemory;
$f=new facture($DB_site);













// $list=$f->search("valide IN (1,5,7,8) AND moyenid>0 AND provenance IN ('mp') AND heurefacture BETWEEN $m1s AND $fins ORDER BY heurefacture");
$list=$f->search("valide IN (1,5,7,8) AND moyenid>0 AND provenance IN ('mp') AND provenanceid='13' ");
























// $factures=$DB_site->query("SELECT * FROM facture WHERE valide IN (1,5,7,8) AND moyenid>0 AND provenance='' AND heurefacture BETWEEN $m1s AND $fins ORDER BY heurefacture");
// while($facture=$DB_site->fetch_array($factures)){
foreach($list as $facture){
    $catcave=$catautre=0;
    $totalttc=0;
    $totalht=0;
    $totalcave=0;
    $gar="";
    $artbyd=array();
    $suivi=$DB_site->query_first("SELECT date,commercial FROM suivi_client WHERE userid=".intval($facture->utilisateur->userid)." AND date BETWEEN ".($facture->datecommande-7*86400)." AND ".$facture->datecommande." ORDER BY date DESC");
    $t0h=mktime(0,0,0,date("n",$facture->datefacture),date("j",$facture->datefacture),date("Y",$facture->datefacture));

    $vflist=array();
    $reqvf=$DB_site->query("SELECT artcode,site_code FROM vente_flash vf INNER JOIN vente_flash_article vfa ON(vfa.vfid=vf.id) WHERE ".$facture->datecommande." BETWEEN du and au");
    while($vf=$DB_site->fetch_array($reqvf)){
		if($facture->site_code==$vf['site_code'])
			$vflist[]=$vf['artid'];
    }
    
    foreach($facture->lignes as $ligne){
		$garantie=$DB_site->query_first("SELECT caractvalid FROM lignefacturecaracteristique WHERE lignefactureid=".$ligne->lignefactureid." AND caractvalid BETWEEN 9 AND 11");
		if($garantie[0]!="" && $garantie[0]!=9){
			switch($garantie[0]){
				case 10:
					$gar="2 ans";
				break;
				case 11:
					$gar="5 ans";
				break;
			}
		}
		$offres=array();
		if($ligne->artid!=0){
			$article=$DB_site->query_first("SELECT a.artid,a.catid,am.marqueid FROM article a INNER JOIN article_marque am ON (am.artid=a.artid) WHERE a.artid=".$ligne->artid);
			$artbyd[$article['marqueid']]+=$ligne->totalht;
			$articles[$ligne->artcode]['nb']+=$ligne->qte;
			$articles[$ligne->artcode]['ca']+=$ligne->totalht;
			$articles[$ligne->artcode]['artid']=$ligne->artid;
		
			if(in_array($article['catid'],$cavecatid)){
				$catcave+=$ligne->qte;
				$totalcave+=$ligne->totalht;
			}elseif(intval($article['catid'])!=0)
				$catautre+=$ligne->qte;
			
			if(in_array($ligne->artid,$promos[$facture->site_code])) $offres[]="Promo";
			if(in_array($ligne->artid,$vflist)) $offres[]="VF";
		}
    }
    $offes=array_unique($offres);

    $totaljour[$t0h]+=$facture->totalttc;
    $totalhtjour[$t0h]+=$facture->totalht;
    $totalnbjour[$t0h]++;
    
    $dep="";
    if(in_array($facture->utilisateur->paysid,array(57,111,181)))
		$dep=substr($facture->utilisateur->codepostal,0,2);
	// echo "date de commande : ".$facture->datecommande;
    $visit=$ccstat->get_visit2($facture->utilisateur->userid,$facture->datecommande,"stat_view");
	
	
	/*
    $prov=$visit[0]->provenance;
    if($visit[0]->mrprovenance['name']!="")
		$prov=$visit[0]->mrprovenance['name'];
    if($visit[0]->provenance=="")
		$prov="Accès direct";
    $kw=$visit[0]->mrprovenance['q'];
    */
	
    
    // $provcriteo=array();
    if($visit==false){
		$prov="";
		$entrance="";
		$referant="";
    }else{
		$prov="";
		$entrance="";
		$referant="";
		$virgule="";
		$revvisit=array_reverse($visit);
		foreach($revvisit as $v){
			if($v->mrprovenance!=""){
				$prov.=$virgule.$v->mrprovenance;
				$entrance.=$virgule.$v->entrance;
				$referant.=$virgule.$v->provenance;
				$virgule=",";
			}
		}
		/*
		$revvisit=array_reverse($visit);
		foreach($revvisit as $v){
			if($v->timestamp>$facture->datecommande-2592000 && $v->timestamp<$facture->datecommande){
				if($v->mrprovenance['type']=="")
					$v->mrprovenance['type']="direct";
				$provcriteo[]=$v->mrprovenance['type'];
			}
		}
		*/
	}
    
    $type="Cde directe";
    if($facture->commercial!="")
      $type="Cde par téléphone";
    elseif($suivi['date']!=""){
      $facture->commercial=$suivi['commercial'];
      $type="Cde directe après suivi commercial";
    }
    
    $codepartenaire="";
    if($facture->cadeautype=="caviste1" || $facture->cadeautype=="caviste2")
		$codepartenaire=$facture->codepromo;
    $newclient=($facture->datefacture==$facture->utilisateur->dateinscription?"1":"0");


    $marge=new marge($DB_site,$facture->facturid);
    $margeht=$marge->marge;
    $margejour[$t0h]+=$margeht;

    $facture->utilisateur->region();

    $row=array();
    $row[]=new ooo_cell(date("d/m/Y",$facture->datecommande),"left");
    $row[]=new ooo_cell(date("H:i",$facture->datecommande),"left");
    $row[]=new ooo_cell($facture->facturid,"left");
	
	$row[]=new ooo_cell($facture->mp_facturid,"left");
	$row[]=new ooo_cell($facture->commission,"left");
	
    $row[]=new ooo_cell($facture->totalttc,"right","currency");
    $row[]=new ooo_cell($facture->totalht,"right","currency");
    $row[]=new ooo_cell($totalcave,"right","currency");
    $row[]=new ooo_cell(intval($catcave),"center","float");
    $row[]=new ooo_cell(intval($catautre),"center","float");
    $row[]=new ooo_cell(implode(" ",$offres),"left");
    $row[]=new ooo_cell(round($facture->cadeauht,2),"right","float");
    $row[]=new ooo_cell($gar,"left");
    $row[]=new ooo_cell($facture->modelivraison=='1'?"oui":"non","left");
    $row[]=new ooo_cell($newclient,"center");
    $row[]=new ooo_cell($facture->utilisateur->pays,"left");
    $row[]=new ooo_cell($dep,"right");
    $row[]=new ooo_cell($facture->utilisateur->region,"left");
    $row[]=new ooo_cell($moyens[$facture->moyenid],"left");
    $row[]=new ooo_cell($entrance,"left");
    $row[]=new ooo_cell($referant,"left");
	$row[]=new ooo_cell($prov,"left");
    $row[]=new ooo_cell($type,"left");
    $row[]=new ooo_cell($facture->commercial,"left");
    $row[]=new ooo_cell($codepartenaire,"left");
    
    $t1->addrow($i,$row);

    $decal=count($row)+1;
    $xo=0;
    ksort($marque);
    foreach($marque as $marqueid=>$libelle){
		$prix=$artbyd[$marqueid];
		if($prix!=""){
		  $totalmarque[$marqueid]+=$prix;
		}
		$c=new ooo_cell($prix,"right","currency");
		$t1->addcell($decal+$xo,$i,$c);
		$xo++;
    }
	/* début modification de benoit : rajout des catégories de produit */
	ksort($typearticle);
	$x1=0;
	foreach($typearticle as $typearticleid=>$libelle){
		$totaltypearticle=0;
		reset($facture);
		foreach($facture->lignes as $ligne){
			if($ligne->artid!=0){
				$typearticlesql=$DB_site->query_first("SELECT typearticleid FROM article WHERE artid=".$ligne->artid." ");
				if($typearticlesql[0]==$typearticleid){
					$totaltypearticle+=$ligne->totalht;
				}
			}
		}
		$c=new ooo_cell($totaltypearticle,"right","currency");
		$t1->addcell($decal+$xo+$x1,$i,$c);
		// echo "on ajoute dans ".$decal+$xo+$x1." , ".$i." : la valeur ".$totaltypearticle."<br>";
		$x1++;
    }
	/* fin modification de benoit : rajout des catégories de produit */
    $i++;
}
$nblignes=$i;


ksort($marque);
$i=0;
foreach($marque as $libelle){
    $c=new ooo_cell($libelle,"bold");
    $t1->addcell($decal+$i,1,$c);
    $i++;
}


// ksort($typearticle);
// print_r($typearticle);

ksort($typearticle);
$j=0;
foreach($typearticle as $libelle){
	$c=new ooo_cell($libelle,"bold");
	$t1->addcell($decal+$i+$j,1,$c);
	// echo "on ajoute dans ".$decal+$i+$j." , 1 : la valeur ".$libelle."<br>";
	$j++;
}

// exit;

$xo=0;
ksort($totalmarque);
$totalmarques=array_sum($totalmarque);
if($totalmarques==0) $totalmarques++;
foreach($totalmarque as $marqueid=>$total){
    $c=new ooo_cell($total,"bright","currency");
    $t1->addcell($decal+$xo,$nblignes+1,$c);
    $c2=new ooo_cell(round(100*$total/$totalmarques,2)."%","right");
    $t1->addcell($decal+$xo,$nblignes+2,$c2);
    $xo++;
}

$doc->addtable($t1);

$doc->generate();

function achatport($poids){
	$requete=mysql_query("SELECT * FROM fraisport WHERE ".intval($poids*1000)." BETWEEN poiddebut AND poidfin AND paysid=57");
	if($requete==false) return 0;
	$frais=mysql_fetch_array($requete);
	return floatval($frais['achat']);
}

/*
function prixport($poids){
	$requete=mysql_query("SELECT * FROM fraisport WHERE ".intval($poids*1000)." BETWEEN poiddebut AND poidfin AND paysid=57");
	if($requete==false) return 0;
	$frais=mysql_fetch_array($requete);
	return floatval($frais['prix']/1.196);
}*/

function achatgarantie($artid){
	$requete=mysql_query("SELECT achat FROM article_caractval ac INNER JOIN caracteristiquevaleur cv USING (caractvalid) WHERE caractvalid IN (9,10,11,134,154,155) AND artid=".intval($artid)." ORDER BY cv.position LIMIT 1");
	$return=mysql_fetch_array($requete);
	return round($return['achat'],2);
}

function achatreel($facturid){
	global $DB_site;
	$totalachat=0;
	$requete=$DB_site->query("SELECT efl.ligne_facture_id,efl.paht,efl.ecopart,efl.qte,efl.facture FROM lignefacture lf inner join envoi_fournisseur_ligne efl on(efl.ligne_facture_id=lf.lignefactureid) WHERE lf.factureid=".$facturid);

	while($ligne=$DB_site->fetch_array($requete)){
	    $totalachat+=$ligne['qte']*($ligne['paht']+$ligne['ecopart']);
	}
	$requete=$DB_site->query("SELECT * FROM facture_fournisseur_autre WHERE facturid=$facturid");
	while($ligne=$DB_site->fetch_array($requete)){
	    $totalachat+=$ligne['prix'];
	}
	return $totalachat;
}

function tranchecapacite($capacite){
	global $categorie;
	foreach($categorie->capacites as $capacite0){
	    if($capacite0>$capacite) return "de $capacitem1 à $capacite0 bouteilles";
	    $capacitem1=$capacite0;
	}
}

?>