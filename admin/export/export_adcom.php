<?php

ini_set("display_errors","1");
error_reporting(E_ALL);
ini_set("memory_limit","1024M");
set_time_limit(300);

extract($_COOKIE);
extract($_REQUEST);
$nostat=1;
include_once($_SERVER['DOCUMENT_ROOT']."/class/host.php");
$host_conf = new Host();
$host=$host_conf->host;

include_once($_SERVER['DOCUMENT_ROOT']."/configurations/".$host."/config_variables.php");
include_once($rootpath."configurations/$host/config_mysql.php");
include($rootpath."class/mysql.php");
include($rootpath."configurations/$host/config.php");
include($rootpath."class/template.class.php");
include($rootpath."class/devises.class.php");
include_once($rootpath."class/fonctions.php");
include_once($rootpath."class/email.php");
include($rootpath."admin/export/ooo.php");
include($rootpath."class/ccstat.class.php");
include($rootpath."class/fournisseur.class.php");
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
		  $requete=$DB_site->query($sql);
		  if($DB_site->num_rows($requete)==0)
			  return false;
		  while($row=$DB_site->fetch_array($requete)){
			$return=new visit();
			$return->statid=$row['id'];
			$return->timestamp=$row['timestamp'];
			$return->provenance=$row['referant'];
			// $return->mrprovenance=$this->mrech($row->referant);
			$sql = " SELECT page, timestamp FROM stat_page WHERE statid='".$row['id']."' UNION SELECT page, timestamp FROM stat_page_arch2 WHERE statid='".$row['id']."' order by timestamp ASC limit 1 ";
			// echo $sql;
			$statpages=$DB_site->query($sql); 
			while($statpage=$DB_site->fetch_array($statpages)){
				$return->mrprovenance="naturel";
				if($row['referant']==""){
					$return->mrprovenance="direct";
				}
				$my_array_of_vars=array();
				if(strpos($statpage[page],"?")===false && strpos($statpage[page],"&")!==false){
					$statpage[page]=str_replace("&","?",$statpage[page]);
				}
				if(strpos($row['referant'],"/aclk?sa=")!==false){
					$return->mrprovenance="adwords";
				}
				parse_str( parse_url( $statpage[page], PHP_URL_QUERY ), $my_array_of_vars );
				if($my_array_of_vars['gclid']!=""){
					$return->mrprovenance="adwords";
				}
				if($my_array_of_vars[utm_source]!=""){
					$provenance_connue=0;
					if($my_array_of_vars[utm_source]=="Google"){
						$return->mrprovenance="adwords";
						$provenance_connue=1;
					}
					if($my_array_of_vars[utm_source]=="adwords"){
						$return->mrprovenance="adwords";
						$provenance_connue=1;
					}
					if($my_array_of_vars[utm_source]=="Bing"){
						$return->mrprovenance="bingads";
						$provenance_connue=1;
					}
					if($my_array_of_vars[utm_source]=="Facebook"){
						$return->mrprovenance="facebookads";
						$provenance_connue=1;
					}
					if($my_array_of_vars[utm_source]=="facebook"){
						$return->mrprovenance="facebookads";
						$provenance_connue=1;
					}
					if($my_array_of_vars[utm_source]=="Nextperf"){
						$return->mrprovenance="nextperf";
						$provenance_connue=1;
					}
					if($my_array_of_vars[utm_source]=="Idealo"){
						$return->mrprovenance="idealo";
						$provenance_connue=1;
					}
					if($provenance_connue==0){
						$return->mrprovenance=$my_array_of_vars[utm_source];
					}
				}
				if($my_array_of_vars[utm_campaign]!=""){
					$return->mrprovenance.=$my_array_of_vars[utm_campaign];
				}
				if($return->mrprovenance=="naturel"){
					if(strpos($return->provenance,"https://www.facebook.com/")>-1){
						$return->mrprovenance="referral";
					}
				}
				$return->entrance=$statpage[page];
			}
			//$return->mrprovenance=$this->mrech($row->referant);
			$return->os=$this->os_name($row['agent']);
			$return->browser=$this->browser_name($row['agent']);
			$return->fai=$this->getfai($row['host']);
			$return->ip=$row['ip'];
			$return->country=$this->ip2country($this->ip);
			$return->pages=$this->getpages($row['id']);
			$returns[]=$return;
		  }
		  return $returns;
		  
		}
	}

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

if($_GET['request']=='cde' || $_GET['request'] == 'all'){
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
	$row[]=new ooo_cell("Validée","bold");
	$row[]=new ooo_cell("Montant €TTC","bold");
	$row[]=new ooo_cell("Montant €HT","bold");
	// $row[]=new ooo_cell("Dont Montant\nCaves €HT","bold");
	$row[]=new ooo_cell("Qté cave","bold");
	$row[]=new ooo_cell("Qté autres","bold");
	$row[]=new ooo_cell("Offre","bold");
	$row[]=new ooo_cell("remise \nHT","bold");
	$row[]=new ooo_cell("Part\n ou Pro","bold");
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
	$row[]=new ooo_cell("Provenance Simple","bold");
	$row[]=new ooo_cell("Type commande","bold");
	$row[]=new ooo_cell("Commercial","bold");
	$row[]=new ooo_cell("Code partenaire","bold");
	$t1->addrow(1,$row);

	$moyens=array();
	$requete=$DB_site->query("SELECT * FROM moyenpaiement");
	while($m=$DB_site->fetch_array($requete)) $moyens[$m['moyenid']]=$m['libelleFR'];

	$marques=$DB_site->query("SELECT * FROM marque ORDER BY libelle ASC");
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


	$i=2;
	$ccstat=new bmstat();
	// $ccstat->temporary_table("(SELECT * FROM stat_arch WHERE user>0) UNION (SELECT * FROM stat WHERE user>0)");
	$totaljour=array();
	$totalhtjour=array();
	$totalnbjour=array();
	$sum_totalht=0;

	$totalmarque=$tmmemory;
	$f=new facture($DB_site);
	$list=$f->search("( (valide IN (1,5,7,8) AND moyenid>0) OR (valide = 0 AND moyenid in (2,4,5,14,17,18,99)) )  AND provenance IN ('','mobile') AND heurefacture BETWEEN $m1s AND $fins ORDER BY heurefacture");


	// $factures=$DB_site->query("SELECT * FROM facture WHERE valide IN (1,5,7,8) AND moyenid>0 AND provenance='' AND heurefacture BETWEEN $m1s AND $fins ORDER BY heurefacture");
	// while($facture=$DB_site->fetch_array($factures)){
	foreach($list as $facture){
	    $catcave=$catautre=0;
	    $totalttc=0;
	    $totalht=0;
	    $totalcave=0;
	    $ligne_totalqte=0;
	    $gar="non";
	    $artbyd=array();
	    $suivi=$DB_site->query_first("SELECT date,commercial,id FROM suivi_client WHERE userid=".intval($facture->utilisateur->userid)." AND date BETWEEN ".($facture->datecommande-7*86400)." AND ".$facture->datecommande." ORDER BY date DESC");
	    
	    $t0h=mktime(0,0,0,date("n",$facture->datefacture),date("j",$facture->datefacture),date("Y",$facture->datefacture));

	    $vflist=array();
	    $reqvf=$DB_site->query("SELECT artcode,site_code FROM vente_flash vf INNER JOIN vente_flash_article vfa ON(vfa.vfid=vf.id) WHERE ".$facture->datecommande." BETWEEN du and au");
	    while($vf=$DB_site->fetch_array($reqvf)){
			if($facture->site_code==$vf['site_code'])
				$vflist[]=$vf['artid'];
	    }
		if($facture->datecommande>1577836800){ // apres 1er janvier 2020
			$table_view="stat_view";
		} else {
			$table_view="stat_view2";
		}
	    foreach($facture->lignes as $ligne){
			$garantie=$DB_site->query_first("SELECT lignefacturegarantie FROM lignefacturegarantie as lfg INNER JOIN garantie as g USING(garantieid) WHERE lfg.lignefactureid=".$ligne->lignefactureid." AND  g.cumul = 1");

			if($garantie[0]!=""){
				$gar = "oui";
			}
			$ligne_totalqte +=$ligne->qte;
			$offres=array();
			if($ligne->artid!=0){
				$article=$DB_site->query_first("SELECT a.artid,a.catid,am.marqueid,a.typearticleid FROM article a INNER JOIN article_marque am ON (am.artid=a.artid) WHERE a.artid=".$ligne->artid);
				$artbyd[$article['marqueid']]+=$ligne->totalht;
				$articles[$ligne->artcode]['nb']+=$ligne->qte;
				$articles[$ligne->artcode]['ca']+=$ligne->totalht;
				$articles[$ligne->artcode]['artid']=$ligne->artid;
			
				if($article['typearticleid']==1){
					$catcave+=$ligne->qte;
					$totalcave+=$ligne->totalht;
				}else
					$catautre+=$ligne->qte;
				
				if(in_array($ligne->artid,$promos[$facture->site_code])) $offres[]="Promo";
				if(in_array($ligne->artid,$vflist)) $offres[]="VF";

				$sql = "SELECT * FROM temp_price WHERE artid =".$ligne->artid." AND site_code='base' AND '".$facture->datecommande."' BETWEEN du AND au";
				//A voir pour les prix temporaires des lots
				
				$temp_price = $DB_site->query_first($sql);
				if($temp_price[0]!=''){
					$offes[] = "prix temporaire";
				}
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
		
		
	    $visit=$ccstat->get_visit2($facture->utilisateur->userid,$facture->datecommande,$table_view);
		

		if($facture->utilisateur->raisonsociale==""){
			$pro_ou_part="particulier";
		} else {
			$pro_ou_part="professionnel";
		}
		
		/*
	    $prov=$visit[0]->provenance;
	    if($visit[0]->mrprovenance['name']!="")
			$prov=$visit[0]->mrprovenance['name'];
	    if($visit[0]->provenance=="")
			$prov="Accès direct";
	    $kw=$visit[0]->mrprovenance['q'];
	    */
		
	  
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
				//if($v->mrprovenance!=""){
					$prov.=$virgule.$v->mrprovenance;
					$virgule=",";
					if($entrance != '')
						$entrance.=','.$v->entrance;
					else
						$entrance = $v->entrance;

					if($referant != '')
						$referant.=','.$v->provenance;
					else
						$referant = $v->provenance;
					
				//}
			}
			//var_dump($visit);
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
	    $prov2="";
	    $type="Cde directe";
	    $suivi_origine = array('adv','qualite','cecile','');
	    if($facture->commercial!=""){
	      $type="Cde par téléphone";
		  if($prov==""){
			$connu=$DB_site->query_first("SELECT connu FROM utilisateur WHERE userid=".$facture->utilisateur->userid." ");
			if($connu[0]!=""){
				$prov=$connu[0];
			} else {
				$prov="direct";
			}
			$prov2="direct";
		  }
	    }elseif($facture->commercial_attribution !="" && $facture->commercial_attribution!="direct"){
	    	$type="Cde client après suivi commercial";
	    	$facture->commercial =  $facture->commercial_attribution;

	    }elseif($suivi['date']!='' && !in_array($suivi['commercial'], $suivi_origine)){
	      $facture->commercial=$suivi['commercial'];
	      $type="Cde client après suivi commercial";
	    }
	 
		$anciennefacture=$DB_site->query_first("SELECT COUNT(*) FROM facture
												WHERE userid=".$facture->utilisateur->userid."
												AND valide IN (1,5,7,8)
												AND moyenid>0 
												AND heurefacture < ".$facture->datecommande." ");
		if($anciennefacture[0]>0){
			if($prov==""){
				$prov="Déjà client";
				$prov2="direct";
			}
		}
		if($prov2==""){
			$virgule="";
			$explode=explode(",",$prov);
			if(is_array($explode)){
				// $prov2=$explode[0];
				$prov3="";
				foreach($explode as $value){
					if($value==""){
						$value="naturel";
					}
					$prov3.=$virgule.$value;
					if(strpos($value, "adwords")!==false){ // adwordsMarques
						$prov2.=$virgule."adwords";
					}elseif(stripos($value, "bingads")!==false){
						$prov2.=$virgule."bingads";
					}elseif(strpos($value,"facebookads")!==false){
						$prov2.=$virgule."facebookads";
					}elseif(strpos($value,"direct")!==false){
						$prov2.=$virgule."direct";
					}elseif($value == 'Déjà client'){
						$prov2.=$virgule."direct";
					}else{
						$prov2.=$virgule."naturel";
					}
					$virgule=",";
				}
				$prov=$prov3;
			} else {
				$prov2="naturel";
				$prov="naturel";
			}
		}
		
	    $codepartenaire="";
	    if(strpos($facture->cadeautype,"caviste") == 0) $codepartenaire=$facture->codepromo;
	    
	    $jour_inscription = date("Y-m-d",$facture->utilisateur->dateinscription);
		$newclient=($facture->datefacture==$jour_inscription?"oui":"non");

	    $marge=new marge($DB_site,$facture->facturid);
	    $margeht=$marge->marge;
	    $margejour[$t0h]+=$margeht;

	    $facture->utilisateur->region();
		
		if($facture->valide==0){
			$facture_etat="NON";
		} else {
			$facture_etat="oui";
		}

		$sum_totalht += $facture->totalht;
		
	    $row=array();
	    $row[]=new ooo_cell(date("d/m/Y",$facture->datecommande),"left");
	    $row[]=new ooo_cell(date("H:i",$facture->datecommande),"left");
	    $row[]=new ooo_cell($facture->facturid,"left");
		$row[]=new ooo_cell($facture_etat,"left");
	    $row[]=new ooo_cell($facture->totalttc,"right","currency");
	    $row[]=new ooo_cell($facture->totalht,"right","currency");
	    // $row[]=new ooo_cell($totalcave,"right","currency");
	    $row[]=new ooo_cell(intval($catcave),"center","float");
	    $row[]=new ooo_cell(intval($catautre),"center","float");
	    $row[]=new ooo_cell(implode(" ",$offres),"left");
	    $row[]=new ooo_cell(round($facture->cadeauht,2),"right","float");
		$row[]=new ooo_cell($pro_ou_part,"left");
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
		$row[]=new ooo_cell($prov2,"left");
	    $row[]=new ooo_cell($type,"left");
	    $row[]=new ooo_cell($facture->commercial_attribution,"left");
	    $row[]=new ooo_cell($codepartenaire,"left");
	    
	    $t1->addrow($i,$row);

	    $decal=count($row)+1;
	    $xo=0;
	    
	    asort($marque);

	    foreach($marque as $marqueid=>$libelle){
			$prix=$artbyd[$marqueid];

			if($prix!="" && !is_null($prix)){
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
			$qtetypearticle=0;
			reset($facture);
			foreach($facture->lignes as $ligne){
				if($ligne->artid!=0){
					$typearticlesql=$DB_site->query_first("SELECT typearticleid FROM article WHERE artid=".$ligne->artid." ");
					if($typearticlesql[0]==$typearticleid){
						$totaltypearticle+=$ligne->totalht;
						$qtetypearticle+=$ligne->qte;
					}
				}
			}
			$c=new ooo_cell($totaltypearticle,"right","currency");
			$t1->addcell($decal+$xo+$x1,$i,$c);
			$x1++;
			// echo "on ajoute dans ".$decal+$xo+$x1." , ".$i." : la valeur ".$totaltypearticle."<br>";
			$c=new ooo_cell($qtetypearticle,"right","float");
			$t1->addcell($decal+$xo+$x1,$i,$c);
			$x1++;
	    }
	    
		$c=new ooo_cell($ligne_totalqte,"right");
		$t1->addcell($decal+$xo+$x1,$i,$c);
		/* fin modification de benoit : rajout des catégories de produit */
	    $i++;
	}
	$nblignes=$i;


	//asort($marque);
	$i=0;
	foreach($marque as $libelle){
	    $c=new ooo_cell($libelle,"bold");
	    $t1->addcell($decal+$i,1,$c);
	    $i++;
	}


	// ksort($typearticle);
	// print_r($typearticle);
	$c=new ooo_cell("Total","bright");
	$t1->addcell(1,$nblignes,$c);


	ksort($typearticle);
	$j=0;
	foreach($typearticle as $libelle){
		$c=new ooo_cell($libelle,"bold");
		$t1->addcell($decal+$i+$j,1,$c);
		// echo "on ajoute dans ".$decal+$i+$j." , 1 : la valeur ".$libelle."<br>";
		$j++;
		$c=new ooo_cell("qte ".$libelle,"bold");
		$t1->addcell($decal+$i+$j,1,$c);
		// echo "on ajoute dans ".$decal+$i+$j." , 1 : la valeur ".$libelle."<br>";
		$j++;
	}

	$xo = $i+$j;
	$c=new ooo_cell("Total qte","bright");
	$t1->addcell($decal+$xo,1,$c);



	//total des montant total HT
	$xo=0;
	$c=new ooo_cell($sum_totalht,"bright","currency");
	$t1->addcell(6,$nblignes,$c);
	/** Fin total des montant total HT **/

	/** Total de chaque marque**/
	$xo=0;
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
	/** FIN Total de chaque marque**/
}


if($_GET['request']=='vente' || $_GET['request'] == 'all'){
	$t2=new ooo_table("Meilleures ventes période");
	$t2->set_col(1,"4cm");
	$t2->set_col(2,"6cm");
	$t2->set_col(3,"6cm");
	$t2->set_col(4,"6cm");
	$t2->set_col(5,"6cm");
	$t2->set_col(13,"16cm");

	$row=array();
	$row[]=new ooo_cell("Ref article","bold");
	$row[]=new ooo_cell("Ref fabricant","bold");
	$row[]=new ooo_cell("Catégorie principale","bold");
	$row[]=new ooo_cell("Catégorie","bold");
	$row[]=new ooo_cell("Capacité","bold");
	$row[]=new ooo_cell("Marque","bold");
	$row[]=new ooo_cell("CA HT n-1","bold");
	$row[]=new ooo_cell("CA HT","bold");
	$row[]=new ooo_cell("Nb article n-1","bold");
	$row[]=new ooo_cell("Nb article","bold");
	$row[]=new ooo_cell("Nb cmds n-1","bold");
	$row[]=new ooo_cell("Nb cmds","bold");
	$row[]=new ooo_cell("URL","bold");
	$t2->addrow(1,$row);

	$r=$DB_site->query("SELECT catid,parentid,libelleFR FROM categorie");
	while($c=$DB_site->fetch_array($r)){
	  $parentkeys[$c['catid']]=$c['parentid'];
	  $catname[$c['catid']]=$c['libelleFR'];
	}

	$sql="SELECT a.artid,a.artcode,a.artcode_eurocave,a.catid,a.capacite_bouteilles,am.marqueid,a.libelleFR,m.libelle AS marque,SUM(lf.qte*lf.prix*(1-lf.tva/100)) AS totalht,SUM(lf.qte) AS qte,COUNT(facturid) AS cmdnb
	FROM facture f 
	INNER JOIN lignefacture lf ON(lf.factureid=f.facturid) 
	INNER JOIN article a USING(artid) 
	INNER JOIN article_marque am USING(artid) 
	INNER JOIN marque m USING(marqueid) 
	WHERE f.heurefacture BETWEEN $m1s AND $fins AND valide IN (1,5,7,8) AND provenance IN ('','mobile') 
	GROUP BY artid ORDER BY totalht DESC";

	$req=$DB_site->query($sql);
	while($article=$DB_site->fetch_array($req)){
	    $artlist[$article['artcode']]=$article;
	}

	$duree=$fins-$m1s;
	$sql="SELECT a.artid,a.artcode,a.artcode_eurocave,a.catid,a.capacite_bouteilles,am.marqueid,a.libelleFR,m.libelle AS marque,SUM(lf.qte*lf.prix*(1-lf.tva/100)) AS totalht2,SUM(lf.qte) AS qte2,COUNT(facturid) AS cmdnb2
	FROM facture f 
	INNER JOIN lignefacture lf ON(lf.factureid=f.facturid) 
	INNER JOIN article a USING(artid) 
	INNER JOIN article_marque am USING(artid) 
	INNER JOIN marque m USING(marqueid) 
	WHERE f.heurefacture BETWEEN ".($m1s-$duree)." AND ".($m1s)." AND valide IN (1,5,7,8) AND provenance IN ('','mobile') 
	GROUP BY artid ORDER BY totalht2 DESC";

	$req=$DB_site->query($sql);
	while($article=$DB_site->fetch_array($req)){
	    if(!isset($artlist[$article['artcode']]))
		$artlist[$article['artcode']]=$article;
	    else{
		$artlist[$article['artcode']]['totalht2']=$article['totalht2'];
		$artlist[$article['artcode']]['qte2']=$article['qte2'];
		$artlist[$article['artcode']]['cmdnb2']=$article['cmdnb2'];
	    }
	}

	usort($artlist,function($a,$b){ return $b['totalht']-$a['totalht']; });

	$i=2;

	foreach($artlist as $article){
	    $capacite="";
	    if(in_array($article['catid'],$cavecatid)) $capacite=tranchecapacite($article['capacite_bouteilles']);
	    $parentid=$parentkeys[$article['catid']];
	    while($parentkeys[$parentid]>0) $parentid=$parentkeys[$parentid];

	    $url="https://www.ma-cave-a-vin.fr".$template->mkurl("article",$article['artid'],$article);

	    $row=array();
	    $row[]=new ooo_cell($article['artcode'],"left");
	    $row[]=new ooo_cell($article['artcode_eurocave'],"left");
	    $row[]=new ooo_cell(html_entity_decode($catname[$parentid]),"left");
	    $row[]=new ooo_cell(html_entity_decode($catname[$article['catid']]),"left");
	    $row[]=new ooo_cell($capacite,"left");
	    $row[]=new ooo_cell($marque[$article['marqueid']],"left");
	    $row[]=new ooo_cell(number_format($article['totalht2'],2,'.',''),"nm1","float");
	    $row[]=new ooo_cell(number_format($article['totalht'],2,'.',''),"right","float");
	    $row[]=new ooo_cell($article['qte2'],"nm1","float");
	    $row[]=new ooo_cell($article['qte'],"right","float");
	    $row[]=new ooo_cell($article['cmdnb2'],"nm1","float");
	    $row[]=new ooo_cell($article['cmdnb'],"right","float");
	    $row[]=new ooo_cell($url,"left");
	    $t2->addrow($i,$row);

	    $i++;
	}

	$doc->addtable($t2);
}


if($_GET['request']=='article' || $_GET['request']=='all'){

	$t4=new ooo_table("Articles vendus les 8 derniers mois");
	$t4->set_col(1,"4cm");
	$t4->set_col(2,"6cm");
	$t4->set_col(3,"6cm");
	$t4->set_col(4,"6cm");
	$t4->set_col(5,"6cm");
	$t4->set_col(10,"16cm");

	$row=array();
	$row[]=new ooo_cell("Ref article","bold");
	$row[]=new ooo_cell("Ref fabricant","bold");
	$row[]=new ooo_cell("Catégorie principale","bold");
	$row[]=new ooo_cell("Catégorie","bold");
	$row[]=new ooo_cell("Capacité","bold");
	$row[]=new ooo_cell("Marque","bold");
	$row[]=new ooo_cell("CA HT","bold");
	$row[]=new ooo_cell("Nombre\narticle","bold");
	$row[]=new ooo_cell("Nb\ncommandes","bold");
	$row[]=new ooo_cell("URL","bold");
	$t4->addrow(1,$row);

	$r=$DB_site->query("SELECT catid,parentid,libelleFR FROM categorie");
	while($c=$DB_site->fetch_array($r)){
	  $parentkeys[$c['catid']]=$c['parentid'];
	  $catname[$c['catid']]=$c['libelleFR'];
	}
	$i=2;

	$sql="SELECT a.artid,a.artcode,a.artcode_eurocave,a.catid,a.capacite_bouteilles,am.marqueid,a.libelleFR,m.libelle AS marque,SUM(lf.qte*lf.prix*(1-lf.tva/100)) AS totalht,SUM(lf.qte) AS qte,COUNT(facturid) AS cmdnb
	FROM facture f 
	INNER JOIN lignefacture lf ON(lf.factureid=f.facturid) 
	INNER JOIN article a USING(artid) 
	INNER JOIN article_marque am USING(artid) 
	INNER JOIN marque m USING(marqueid) 
	WHERE f.heurefacture>".(time()-21038400)." AND valide IN (1,5,7,8) AND provenance IN ('','mobile') 
	GROUP BY artid ORDER BY totalht DESC";

	$req=$DB_site->query($sql);
	while($article=$DB_site->fetch_array($req)){
	//     $article=$DB_site->query_first("SELECT a.artid,a.catid,a.paht,a.emballage_poids,c.libelleFR AS catname, am.marqueid,a.libelleFR AS libelle, a.capacite_bouteilles FROM article a INNER JOIN article_marque am ON(am.artid=a.artid) INNER JOIN categorie c ON(c.catid=a.catid) WHERE a.artid='".$articles[$artcode]['artid']."'");

	    $capacite="";
	    if(in_array($article['catid'],$cavecatid)) $capacite=tranchecapacite($article['capacite_bouteilles']);
	    $parentid=$parentkeys[$article['catid']];
	    while($parentkeys[$parentid]>0) $parentid=$parentkeys[$parentid];

	    $url="https://www.ma-cave-a-vin.fr".$template->mkurl("article",$article['artid'],$article);
	    
	    $row=array();
	    $row[]=new ooo_cell($article['artcode'],"left");
	    $row[]=new ooo_cell($article['artcode_eurocave'],"left");
	    $row[]=new ooo_cell(html_entity_decode($catname[$parentid]),"left");
	    $row[]=new ooo_cell(html_entity_decode($catname[$article['catid']]),"left");
	    $row[]=new ooo_cell($capacite,"left");
	    $row[]=new ooo_cell($marque[$article['marqueid']],"left");
	    $row[]=new ooo_cell(number_format($article['totalht'],2,'.',''),"right","float");
	    $row[]=new ooo_cell($article['qte'],"right","float");
	    $row[]=new ooo_cell($article['cmdnb'],"right");
	    $row[]=new ooo_cell($url,"left");
	    $t4->addrow($i,$row);

	    $i++;
	}

	$doc->addtable($t4);
}

$doc->generate();

function achatport($poids){
    global $DB_site;
	$requete=$DB_site->query("SELECT * FROM fraisport WHERE ".intval($poids*1000)." BETWEEN poiddebut AND poidfin AND paysid=57");
	if($requete==false) return 0;
	$frais=$DB_site->fetch_array($requete);
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
    global $DB_site;
	$requete=$DB_site->query("SELECT achat FROM article_caractval ac INNER JOIN caracteristiquevaleur cv USING (caractvalid) WHERE caractvalid IN (9,10,11,134,154,155) AND artid=".intval($artid)." ORDER BY cv.position LIMIT 1");
	$return=$DB_site->fetch_array($requete);
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
