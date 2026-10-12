<?php

ini_set("display_errors","1");
error_reporting(E_ALL);
ini_set("memory_limit","1024M");

$rootpath="/var/www/vhosts/ma-cave-a-vin.fr/httpdocs/";
// $rootpath="/media/disk/www/macave/site2010/";

$host="www.ma-cave-a-vin.fr" ;
$nostat=1;
include_once($rootpath."configurations/$host/config_mysql.php");
include($rootpath."class/mysql.php");
include($rootpath."configurations/$host/config.php");
include_once($rootpath."class/fonctions.php");
include_once($rootpath."class/email.php");
include($rootpath."admin/export/ooo.php");
include($rootpath."class/ccstat.class.php");
include($rootpath."admin/marge.php");
include($rootpath."class/categorie.class.php");

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


$t1=new ooo_table("Weboost 1");
$bold=new ooo_style("bold");
$bold->font_weight="bold";
$bold->text_align="center";
$bold->family="table-cell";
$bold->setborders("0.5pt solid #000000");
$doc->addstyle($bold);
$left=new ooo_style("left");
$left->family="table-cell";
$left->setborders("0.5pt solid #000000");
$doc->addstyle($left);
$right=new ooo_style("right");
$right->text_align="end";
$right->family="table-cell";
$right->setborders("0.5pt solid #000000");
$doc->addstyle($right);
$bright=new ooo_style("bright");
$bright->text_align="end";
$bright->family="table-cell";
$bright->setborders("0.5pt solid #000000");
$bright->font_weight="bold";
$doc->addstyle($bright);
$center=new ooo_style("center");
$center->text_align="center";
$center->family="table-cell";
$center->setborders("0.5pt solid #000000");
$doc->addstyle($center);
$red=new ooo_style("red");
$red->family="table-cell";
$red->font_color="#FF0000";
$red->text_align="end";
$red->setborders("0.5pt solid #000000");
$doc->addstyle($red);

$t1->set_col(3,"1.5cm");
$t1->set_col(10,"4cm");
$t1->set_col(12,"5cm");
$t1->set_col(14,"1cm");
$t1->set_col(15,"6cm");
$t1->set_row(1,"1cm");

$row=array();
$row[]=new ooo_cell("Date","bold");
$row[]=new ooo_cell("Heure","bold");
$row[]=new ooo_cell("N cde","bold");
$row[]=new ooo_cell("Montant €TTC","bold");
$row[]=new ooo_cell("Montant €HT","bold");
$row[]=new ooo_cell("Dont Montant\nCaves €HT","bold");
$row[]=new ooo_cell("Marge €HT","bold");
$row[]=new ooo_cell("Qté cave","bold");
$row[]=new ooo_cell("Qté autres","bold");
$row[]=new ooo_cell("Code\npromo","bold");
$row[]=new ooo_cell("Extension\ngarantie","bold");
$row[]=new ooo_cell("Installation\nspécialisée","bold");
$row[]=new ooo_cell("Passage commande\njour inscription","bold");
$row[]=new ooo_cell("Client perdu","bold");
$row[]=new ooo_cell("Pays facturation","bold");
$row[]=new ooo_cell("Département","bold");
$row[]=new ooo_cell("Mode de paiement","bold");
$row[]=new ooo_cell("Provenance","bold");
$row[]=new ooo_cell("Mots clés","bold");
$row[]=new ooo_cell("Type commande","bold");
$row[]=new ooo_cell("Commercial","bold");
$row[]=new ooo_cell("Code partenaire","bold");
$t1->addrow(1,$row);

$moyens=array();
$requete=$DB_site->query("SELECT * FROM moyenpaiement");
while($m=$DB_site->fetch_array($requete)) $moyens[$m['moyenid']]=utf8_encode($m['libelleFR']);

$marques=$DB_site->query("SELECT * FROM marque ORDER BY libelle");
while($m=$DB_site->fetch_array($marques)){
  $marque[$m['marqueid']]=$m['libelle'];
  $tmmemory[$m['marqueid']]=0;
}


$i=2;
$ccstat=new ccstat();
$totaljour=array();
$totalhtjour=array();
$totalnbjour=array();
$totalmarque=$tmmemory;
$factures=$DB_site->query("SELECT * FROM facture WHERE valide IN (1,5,7,8) AND moyenid>0 AND provenance='' AND heurefacture BETWEEN $m1s AND $fins ORDER BY heurefacture");
while($facture=$DB_site->fetch_array($factures)){
    $catcave=$catautre=0;
    $totalttc=0;
    $totalht=0;
    $totalcave=0;
    $gar="";
    $artbyd=array();
    $utilisateur=$DB_site->query_first("SELECT u.codepostal,p.libelle,u.paysid,u.dateinscription FROM utilisateur u INNER JOIN pays p ON (p.paysid=u.paysid) WHERE u.userid=".$facture['userid']);
    $suivi=$DB_site->query_first("SELECT date,commercial FROM suivi_client WHERE userid=".intval($facture['userid'])." AND date<".$facture['heurefacture']." ORDER BY date DESC");
    $lignes=$DB_site->query("SELECT * FROM lignefacture WHERE factureid=".$facture['facturid']);
    $t0h=mktime(0,0,0,date("n",$facture['heurefacture']),date("j",$facture['heurefacture']),date("Y",$facture['heurefacture']));

    while($ligne=$DB_site->fetch_array($lignes)){
	$garantie=$DB_site->query_first("SELECT caractvalid FROM lignefacturecaracteristique WHERE lignefactureid=".$ligne['lignefactureid']." AND caractvalid BETWEEN 9 AND 11");
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

	if($ligne['artid']!=0){
	    $article=$DB_site->query_first("SELECT a.artid,a.catid,am.marqueid FROM article a INNER JOIN article_marque am ON (am.artid=a.artid) WHERE a.artid=".$ligne['artid']);
	    $artbyd[$article['marqueid']]+=round($ligne['prix']/(1+$ligne['tva']/100),2);
	    $articles[$ligne['artcode']]['nb']++;
	    $articles[$ligne['artcode']]['ca']+=round($ligne['prix']/(1+$ligne['tva']/100),2);
	    $articles[$ligne['artcode']]['artid']=$ligne['artid'];
	
	
	    $totalht+=round($ligne['qte']*$ligne['prix']/(1+$ligne['tva']/100),2);
	    $totalttc+=$ligne['prix']*$ligne['qte'];
	    if(in_array($article['catid'],$cavecatid)){
		$catcave+=$ligne['qte'];
		$totalcave+=round($ligne['qte']*$ligne['prix']/(1+$ligne['tva']/100),2);
	    }elseif(intval($article['catid'])!=0)
		$catautre+=$ligne['qte'];
	}
	
    }
    $totalttc+=$facture['prixport']-$facture['montantcadeau'];
    $totalht+=$facture['prixport']/(1+$facture['tvaport']/100)-($facture['montantcadeau']/1.196);
    
    $totaljour[$t0h]+=$totalttc;
    $totalhtjour[$t0h]+=$totalht;
    $totalnbjour[$t0h]++;
    
    $dep="";
    if(in_array($utilisateur['paysid'],array(57,111,181))) $dep=substr($utilisateur['codepostal'],0,2);
    $visit=$ccstat->get_visit($facture['userid'],$facture['heurefacture']);
    $prov=$visit[0]->provenance;
    if($visit[0]->mrprovenance['name']!="") $prov=utf8_encode($visit[0]->mrprovenance['name']);
    if($visit[0]->provenance=="") $prov="Accès direct";
    $kw=$visit[0]->mrprovenance['q'];
    if($visit==false) $prov="?";

    $type="Cde directe";
    if($facture['commercial']!="")
      $type="Cde par téléphone";
    elseif($suivi['date']!=""){
      $facture['commercial']=$suivi['commercial'];
      $type="Cde directe après suivi commercial";
    }
    
    $totalcadeau=0;
    $codepartenaire="";
    if ($facture[cadeauid] != ""){
            $infoscadeau=$DB_site->query_first("SELECT * FROM cadeau WHERE cadeauid = '$facture[cadeauid]'");
	    if($infoscadeau['code']!=""){
	      $totalcadeau=$facture['montantcadeau'];
	      $totalreduc=array();
	      $article=$DB_site->query_first("SELECT artcode from article a, article_cadeau ac where a.artid = ac.artid and cadeauid = '$facture[cadeauid]'");
	      if ($article[0] == "") $rayon=$DB_site->query_first("SELECT libelleFR from categorie c, categorie_cadeau cc where c.catid = cc.catid and cadeauid = '$facture[cadeauid]'");
	      
	      $lignefactures=$DB_site->query("SELECT * FROM lignefacture WHERE artcode<>'' AND factureid = '$facture[facturid]'");
	      while($l=$DB_site->fetch_array($lignefactures)){
		  $caract=$DB_site->query_first("SELECT SUM(differenceprix) FROM lignefacturecaracteristique WHERE lignefactureid=".$l['lignefactureid']);
		  if($l['artcode']==$article[0] || $l['catid']==$rayon[0] || ($article[0]=="" && $rayon[0]==""))
		      $totalreduc[$l['tva']]+=$l['qte']*($l['prix']-$caract[0]);
	      }
	    }

	    $code_caviste=$DB_site->query_first("SELECT * FROM code_caviste WHERE code='$facture[cadeauid]'");
	    if($code_caviste['code']!=""){
		$codepartenaire=$code_caviste['code'];
		if($code_caviste['type']==2) $totalcadeau=$facture['montantcadeau'];		
		
		if($code_caviste['type']==1 && $facture['montantcadeau']<$code_caviste['mini']) $totalcadeau=$facture['montantcadeau'];
		  
		$totalreduc=array();
		$lignefactures=$DB_site->query("SELECT * FROM lignefacture WHERE artcode<>'' AND factureid = '$facture[facturid]'");
		while($l=$DB_site->fetch_array($lignefactures)){
		    $caract=$DB_site->query_first("SELECT SUM(differenceprix) FROM lignefacturecaracteristique WHERE lignefactureid=".$l['lignefactureid']);
			$totalreduc[$l['tva']]+=$l['qte']*($l['prix']-$caract[0]);
		}
	    }

	    $cadeauht=0;
	    if($totalcadeau>0){
	      foreach($totalreduc as $tx=>$totaltx)
		   $cadeauht+=($facture[montantcadeau]*$totaltx/array_sum($totalreduc))/(1+$tx/100);

            }
    }

    $newclient=($facture['datefacture']==$utilisateur['dateinscription']?"1":"0");


    $marge=new marge($DB_site,$facture['facturid']);
    $margeht=$marge->marge;
    $margejour[$t0h]+=$margeht;

    $row=array();
    $row[]=new ooo_cell(date("d/m/Y",$facture['heurefacture']),"left");
    $row[]=new ooo_cell(date("H:i",$facture['heurefacture']),"left");
    $row[]=new ooo_cell($facture['facturid'],"left");
    $row[]=new ooo_cell($totalttc,"right","currency");
    $row[]=new ooo_cell($totalht,"right","currency");
    $row[]=new ooo_cell($totalcave,"right","currency");
    $row[]=new ooo_cell($margeht,"right","currency");
    $row[]=new ooo_cell(intval($catcave),"center","float");
    $row[]=new ooo_cell(intval($catautre),"center","float");
    $row[]=new ooo_cell(round($cadeauht,2),"right","float");
    $row[]=new ooo_cell($gar,"left");
    $row[]=new ooo_cell($facture['modelivraison']=='1'?"oui":"non","left");
    $row[]=new ooo_cell($newclient,"center");
    $row[]=new ooo_cell("","center");
    $row[]=new ooo_cell(utf8_encode($utilisateur['libelle']),"left");
    $row[]=new ooo_cell($dep,"right");
    $row[]=new ooo_cell($moyens[$facture['moyenid']],"left");
    $row[]=new ooo_cell($prov,"left");
    $row[]=new ooo_cell($kw,"left");
    $row[]=new ooo_cell($type,"left");
    $row[]=new ooo_cell($facture['commercial'],"left");
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

    $i++;
}
$nblignes=$i;


ksort($marque);
$i=0;
foreach($marque as $libelle){
    $c=new ooo_cell(utf8_encode($libelle),"bold");
    $t1->addcell($decal+$i,1,$c);
    $i++;
}

$xo=0;
ksort($totalmarque);
$totalmarques=array_sum($totalmarque);
foreach($totalmarque as $marqueid=>$total){
    $c=new ooo_cell($total,"bright","currency");
    $t1->addcell($decal+$xo,$nblignes+1,$c);
    $c2=new ooo_cell(round(100*$total/$totalmarques,2)."%","right");
    $t1->addcell($decal+$xo,$nblignes+2,$c2);
    $xo++;
}

$doc->addtable($t1);



$t3=new ooo_table("Weboost 3");
$row=array();
$row[]=new ooo_cell("Date","bold");
$row[]=new ooo_cell("Heure","bold");
$row[]=new ooo_cell("N cde","bold");
$row[]=new ooo_cell("Montant €TTC","bold");
$row[]=new ooo_cell("Montant €HT","bold");
$row[]=new ooo_cell("Dont Montant\nCaves €HT","bold");
$row[]=new ooo_cell("Marge €HT","bold");
$row[]=new ooo_cell("Qté cave","bold");
$row[]=new ooo_cell("Qté autres","bold");
$row[]=new ooo_cell("Code\npromo","bold");
$row[]=new ooo_cell("Extension\ngarantie","bold");
$row[]=new ooo_cell("Installation\nspécialisée","bold");
$row[]=new ooo_cell("Nouveau\ncontact","bold");
$row[]=new ooo_cell("Client perdu","bold");
$row[]=new ooo_cell("Pays facturation","bold");
$row[]=new ooo_cell("Département","bold");
$row[]=new ooo_cell("Mode de paiement","bold");
$row[]=new ooo_cell("Provenance","bold");
$row[]=new ooo_cell("Mots clés","bold");
$row[]=new ooo_cell("Type devis","bold");
$row[]=new ooo_cell("Commercial","bold");
$t3->addrow(1,$row);

$i=2;
$ccstat=new ccstat();
$totalmarque=$tmmemory;
$factures=$DB_site->query("SELECT * FROM facture f WHERE facturid=(SELECT facturid FROM facture WHERE userid=f.userid AND valide=0 AND provenance='' AND heurefacture BETWEEN $m1s AND $fins ORDER BY heurefacture DESC LIMIT 1) ORDER BY heurefacture");
while($facture=$DB_site->fetch_array($factures)){
    $catcave=$catautre=0;
    $totalttc=0;
    $totalht=0;
    $totalcave=0;
    $gar="";
    $artbyd=array();
    $utilisateur=$DB_site->query_first("SELECT u.codepostal,p.libelle,u.paysid,u.dateinscription FROM utilisateur u INNER JOIN pays p ON (p.paysid=u.paysid) WHERE u.userid=".$facture['userid']);
    $suivi=$DB_site->query_first("SELECt * FROM suivi_client WHERE userid=".intval($facture['userid'])." AND date<".$facture['heurefacture']." ORDER BY date DESC");
    $lignes=$DB_site->query("SELECT * FROM lignefacture WHERE factureid=".$facture['facturid']);
    $t0h=mktime(0,0,0,date("n",$facture['heurefacture']),date("j",$facture['heurefacture']),date("Y",$facture['heurefacture']));

    while($ligne=$DB_site->fetch_array($lignes)){
	$garantie=$DB_site->query_first("SELECT caractvalid FROM lignefacturecaracteristique WHERE lignefactureid=".$ligne['lignefactureid']." AND caractvalid BETWEEN 9 AND 11");
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

	if($ligne['artid']!=0){
	    $article=$DB_site->query_first("SELECT a.catid,am.marqueid FROM article a INNER JOIN article_marque am ON (am.artid=a.artid) WHERE a.artid=".$ligne['artid']);
	    $artbyd[$article['marqueid']]+=round($ligne['prix']/(1+$ligne['tva']/100),2);
	}
	
	$totalht+=round($ligne['qte']*$ligne['prix']/(1+$ligne['tva']/100),2);
	$totalttc+=$ligne['prix']*$ligne['qte'];
	if(in_array($article['catid'],$cavecatid)){
	    $catcave+=$ligne['qte'];
	    $totalcave+=round($ligne['qte']*$ligne['prix']/(1+$ligne['tva']/100),2);
	}elseif(intval($article['catid'])!=0)
	    $catautre+=$ligne['qte'];
	    
    }

    $totalcadeau=0;
    if ($facture[cadeauid] != ""){
            $infoscadeau=$DB_site->query_first("SELECT * FROM cadeau WHERE cadeauid = '$facture[cadeauid]'");
	    if($infoscadeau['code']!=""){
	      $totalcadeau=$facture['montantcadeau'];
	      $totalreduc=array();
	      $article=$DB_site->query_first("SELECT artcode from article a, article_cadeau ac where a.artid = ac.artid and cadeauid = '$facture[cadeauid]'");
	      if ($article[0] == "") $rayon=$DB_site->query_first("SELECT libelleFR from categorie c, categorie_cadeau cc where c.catid = cc.catid and cadeauid = '$facture[cadeauid]'");
	      
	      $lignefactures=$DB_site->query("SELECT * FROM lignefacture WHERE artcode<>'' AND factureid = '$facture[facturid]'");
	      while($l=$DB_site->fetch_array($lignefactures)){
		  $caract=$DB_site->query_first("SELECT SUM(differenceprix) FROM lignefacturecaracteristique WHERE lignefactureid=".$l['lignefactureid']);
		  if($l['artcode']==$article[0] || $l['catid']==$rayon[0] || ($article[0]=="" && $rayon[0]==""))
		      $totalreduc[$l['tva']]+=$l['qte']*($l['prix']-$caract[0]);
	      }
	    }

	    $code_caviste=$DB_site->query_first("SELECT * FROM code_caviste WHERE code='$facture[cadeauid]'");
	    if($code_caviste['code']!=""){
		if($code_caviste['type']==2) $totalcadeau=$facture['montantcadeau'];		
		
		if($code_caviste['type']==1 && $facture['montantcadeau']<$code_caviste['mini']) $totalcadeau=$facture['montantcadeau'];
		  
		$totalreduc=array();
		$lignefactures=$DB_site->query("SELECT * FROM lignefacture WHERE artcode<>'' AND factureid = '$facture[facturid]'");
		while($l=$DB_site->fetch_array($lignefactures)){
		    $caract=$DB_site->query_first("SELECT SUM(differenceprix) FROM lignefacturecaracteristique WHERE lignefactureid=".$l['lignefactureid']);
			$totalreduc[$l['tva']]+=$l['qte']*($l['prix']-$caract[0]);
		}
	    }

	    $cadeauht=0;
	    if($totalcadeau>0){
	      foreach($totalreduc as $tx=>$totaltx)
		   $cadeauht+=($facture[montantcadeau]*$totaltx/array_sum($totalreduc))/(1+$tx/100);

            }
    }

    $totalttc+=$facture['prixport']-$totalcadeau;
    $totalht+=$facture['prixport']/(1+$facture['tvaport']/100)-($facture['montantcadeau']/1.196);
    
    $totaldevis[$t0h]+=$totalht;
    
    $dep="";
    if(in_array($utilisateur['paysid'],array(57,111,181))) $dep=substr($utilisateur['codepostal'],0,2);
    $visit=$ccstat->get_visit($facture['userid'],$facture['heurefacture']);
    $prov=$visit[0]->provenance;
    if($visit[0]->mrprovenance['name']!="") $prov=utf8_encode($visit[0]->mrprovenance['name']);
    if($visit[0]->provenance=="") $prov="Accès direct";
    $kw=$visit[0]->mrprovenance['q'];
    if($visit==false) $prov="?";


    switch($suivi['etat']){
	case 0:
	$etat="Perdu";
	$cause=$DB_site->query_first("SELECT * FROM causeperte WHERE userid=".$facture['userid']." ORDER BY date DESC LIMIT 1");
	switch($cause['cause']){
	  case 0:
	  $etat.=" (cause prix)";
	  break;
	  case 1:
	  $etat.=" (cause délai)";
	  break;
	  case 2:
	  $etat.=" (case projet abandonné)";
	  break;
	  case 3:
	  $etat.=" (trop relancé)";
	  break;
	  case 4:
	  $etat.=" (mauvais rapports commerciaux)";
	  break;
	  case 5:
	  $etat.=" (ne veux pas dire)";
	  break;
	  case 6:
	  $etat.=" (autre)";
	  break;
	}
	break;
	case 1:
	$etat="En cours (ne sais pas)";
	break;
	case 2:
	$etat="En cours (presque sûr)";
	break;
	case 3:
	$etat="En cours (peu probable)";
	break;
    }

    $marge=new marge($DB_site,$facture['facturid']);
    $margeht=$marge->marge;

    $newclient=($facture['datefacture']==$utilisateur['dateinscription']?"1":"0");

    $row=array();
    $row[]=new ooo_cell(date("d/m/Y",$facture['heurefacture']),"left");
    $row[]=new ooo_cell(date("H:i",$facture['heurefacture']),"left");
    $row[]=new ooo_cell($facture['facturid'],"left");
    $row[]=new ooo_cell($totalttc,"right","currency");
    $row[]=new ooo_cell($totalht,"right","currency");
    $row[]=new ooo_cell($totalcave,"right","currency");
    $row[]=new ooo_cell($margeht,"right","currency");
    $row[]=new ooo_cell(intval($catcave),"center","float");
    $row[]=new ooo_cell(intval($catautre),"center","float");
    $row[]=new ooo_cell($cadeauht,"right","float");
    $row[]=new ooo_cell($gar,"left");
    $row[]=new ooo_cell($facture['modelivraison']=='1'?"oui":"non","left");
    $row[]=new ooo_cell($newclient,"center");
    $row[]=new ooo_cell($suivi['etat']==0?"1":"0","center");
    $row[]=new ooo_cell(utf8_encode($utilisateur['libelle']),"left");
    $row[]=new ooo_cell($dep,"right");
    $row[]=new ooo_cell("","left");
    $row[]=new ooo_cell($prov,"left");
    $row[]=new ooo_cell($kw,"left");
    $row[]=new ooo_cell($etat,"left");
    $row[]=new ooo_cell($suivi['commercial'],"left");
    
    $t3->addrow($i,$row);

    $decal=count($row)+1;

    $xo=0;
    ksort($marque);
    foreach($marque as $marqueid=>$libelle){
	$prix=$artbyd[$marqueid];
	if($prix!=""){
	  $totalmarque[$marqueid]+=$prix;
	}
	$c=new ooo_cell($prix,"right","currency");
	$t3->addcell($decal+$xo,$i,$c);
	$xo++;
    }

    $i++;

}

$nblignes=$i;

ksort($marque);
$i=0;
foreach($marque as $libelle){
    $c=new ooo_cell(utf8_encode($libelle),"bold");
    $t3->addcell($decal+$i,1,$c);
    $i++;
}

$xo=0;
ksort($totalmarque);
$totalmarques=array_sum($totalmarque);
foreach($totalmarque as $marqueid=>$total){
    $c=new ooo_cell($total,"bright","currency");
    $t3->addcell($decal+$xo,$nblignes+1,$c);
    $c2=new ooo_cell(round(100*$total/$totalmarques,2)."%","right");
    $t3->addcell($decal+$xo,$nblignes+2,$c2);
    $xo++;
}



$t2=new ooo_table("Weboost 2");
$head=array();
$head[]=new ooo_cell("Date","bold");
$head[]=new ooo_cell("Nbre\nCde","bold");
$head[]=new ooo_cell("Objectif €HT","bold");
$head[]=new ooo_cell("Montant €HT","bold");
$head[]=new ooo_cell("Marge €HT","bold");
$head[]=new ooo_cell("% de\nl'objectif","bold");
$head[]=new ooo_cell("Nouveaux\ncontacts","bold");
$head[]=new ooo_cell("Clients\nperdus","bold");
$head[]=new ooo_cell("% clients\nperdus","bold");
$head[]=new ooo_cell("Devis\nperdus","bold");
$head[]=new ooo_cell("Moyenne\ncde €HT","bold");
$t2->addrow(1,$head);


$y=2;
$i=$m1s;
$sumobj=0;

while($i<$fins){
    $row=array();
    if(date("H",$i)==1) $i-=3600;
    if(date("H",$i)==23) $i+=3600;
    $dobj=round($objectif_facture[date("Y",$i)][date("n",$i)]/date("t",$i));
    $sumobj+=$dobj;
    $pcobj=round(100*$totaljour[$i]/$dobj);
    $style="right";
    if($pcobj<100) $style="red";
    $nclients=$DB_site->query_first("SELECT COUNT(*) FROM utilisateur WHERE UNIX_TIMESTAMP(dateinscription) BETWEEN $i AND ".($i+86399));
    $lclients=$DB_site->query_first("SELECT COUNT(*) FROM suivi_client WHERE etat=0 AND date BETWEEN $i AND ".($i+86399));
    if($nclients[0]=="") $nclients[0]=1;
    if($nclients[0]>0)
      $pclost=round(100*$lclients[0]/$nclients[0]);
    else
      $pclost=0;
    $moycde=0;
    if($totalnbjour[$i]>0) $moycde=round($totaljour[$i]/$totalnbjour[$i]);

    $row[]=new ooo_cell(date("d/m/Y",$i),"left");
    $row[]=new ooo_cell($totalnbjour[$i],"right","float");
    $row[]=new ooo_cell($dobj,"right","currency");
    $row[]=new ooo_cell($totalhtjour[$i],"right","currency");
    $row[]=new ooo_cell($margejour[$i],"right","currency");
    $row[]=new ooo_cell($pcobj,$style,"float");
    $row[]=new ooo_cell(intval($nclients[0]),"right","float");
    $row[]=new ooo_cell(intval($lclients[0]),"right","float");
    $row[]=new ooo_cell($pclost,"right","float");
    $row[]=new ooo_cell($totaldevis[$i],"right","currency");
    $row[]=new ooo_cell($moycde,"right","currency");

    $t2->addrow($y,$row);

    
    $y++;
    $i+=86400;
}

$y++;

$pcobj=round(100*array_sum($totalhtjour)/$sumobj);
$style="bright";
if($pcobj<100) $style="red";
$nclients=$DB_site->query_first("SELECT COUNT(*) FROM utilisateur WHERE UNIX_TIMESTAMP(dateinscription) BETWEEN $m1s AND $fins");
$lclients=$DB_site->query_first("SELECT COUNT(*) FROM suivi_client WHERE etat=0 AND date BETWEEN $m1s AND $fins");
$pclost=round(100*$lclients[0]/$nclients[0]);
$moycde=round(array_sum($totalhtjour)/array_sum($totalnbjour));

$row=array();
$row[]=new ooo_cell($txttotal,"bold");
$row[]=new ooo_cell(array_sum($totalnbjour),"bright","currency");
$row[]=new ooo_cell($sumobj,"bright","currency");
$row[]=new ooo_cell(array_sum($totalhtjour),"bright","currency");
$row[]=new ooo_cell(array_sum($margejour),"bright","currency");
$row[]=new ooo_cell($pcobj,$style,"float");
$row[]=new ooo_cell(intval($nclients[0]),"bright","float");
$row[]=new ooo_cell(intval($lclients[0]),"bright","float");
$row[]=new ooo_cell($pclost,"bright","float");
$row[]=new ooo_cell(array_sum($totaldevis),"bright","currency");
$row[]=new ooo_cell($moycde,"bright","currency");

$t2->addrow($y,$row);

$doc->addtable($t2);

$doc->addtable($t3);


$t4=new ooo_table("Weboost 4");
$t4->set_col(1,"4cm");
$t4->set_col(2,"6cm");
$t4->set_col(3,"6cm");
$t4->set_col(4,"6cm");
$t4->set_col(9,"16cm");

$row=array();
$row[]=new ooo_cell("Ref article","bold");
$row[]=new ooo_cell("Catégorie principale","bold");
$row[]=new ooo_cell("Catégorie","bold");
$row[]=new ooo_cell("Capacité","bold");
$row[]=new ooo_cell("Marque","bold");
$row[]=new ooo_cell("CA HT","bold");
$row[]=new ooo_cell("Marge €HT","bold");
$row[]=new ooo_cell("Nb\ncommandes","bold");
$row[]=new ooo_cell("URL","bold");
$t4->addrow(1,$row);

$r=$DB_site->query("SELECT catid,parentid,libelleFR FROM categorie");
while($c=$DB_site->fetch_array($r)){
  $parentkeys[$c['catid']]=$c['parentid'];
  $catname[$c['catid']]=$c['libelleFR'];
}

$i=2;
foreach($articles as $artcode=>$data){
    $article=$DB_site->query_first("SELECT a.artid,a.catid,a.paht,a.emballage_poids,c.libelleFR AS catname, am.marqueid,a.libelleFR AS libelle, a.capacite_bouteilles FROM article a INNER JOIN article_marque am ON(am.artid=a.artid) INNER JOIN categorie c ON(c.catid=a.catid) WHERE a.artid='".$articles[$artcode]['artid']."'");
    $coutport=achatport($article['emballage_poids']);
    $gar=achatgarantie($article['artid']);

    $margeht=$data['ca']-(($article['paht']+$coutport+$gar)*$data['nb']);
    $capacite="";
    if(in_array($article['catid'],$cavecatid)) $capacite=tranchecapacite($article['capacite_bouteilles']);
    $parentid=$parentkeys[$article['catid']];
    while($parentkeys[$parentid]>0) $parentid=$parentkeys[$parentid];

    $row=array();
    $row[]=new ooo_cell($artcode,"left");
    $row[]=new ooo_cell(utf8_encode(html_entity_decode($catname[$parentid])),"left");
    $row[]=new ooo_cell(utf8_encode(html_entity_decode($article['catname'])),"left");
    $row[]=new ooo_cell($capacite,"left");
    $row[]=new ooo_cell($marque[$article['marqueid']],"left");
    $row[]=new ooo_cell($data['ca'],"right","float");
    $row[]=new ooo_cell($margeht,"right","float");
    $row[]=new ooo_cell($data['nb'],"right");
    $row[]=new ooo_cell("http://www.ma-cave-a-vin.fr/art,".$article['artid'].",".url_rewrite($article['libelle']).".html","left");
    $t4->addrow($i,$row);

    $i++;
}

$doc->addtable($t4);


$t5=new ooo_table("Weboost 5");

$t5->set_col(1,"6cm");
$t5->set_col(2,"4cm");
$t5->set_col(3,"6cm");
$t5->set_col(4,"6cm");
$t5->set_col(5,"8cm");
$t5->set_col(6,"4cm");
$t5->set_col(9,"16cm");

$row=array();
$row[]=new ooo_cell("Ref\nconstructeur","bold");
$row[]=new ooo_cell("Ref\nacilyon","bold");
$row[]=new ooo_cell("Catégorie","bold");
$row[]=new ooo_cell("Sous catégorie","bold");
$row[]=new ooo_cell("Libelle","bold");
$row[]=new ooo_cell("Marque","bold");
$row[]=new ooo_cell("Prix de\nvente HT","bold");
$row[]=new ooo_cell("Prix achat\ntotal €HT","bold");
$row[]=new ooo_cell("URL","bold");
$t5->addrow(1,$row);

$i=2;
$requete=$DB_site->query("SELECT a.artid,a.artcode,a.artcode_eurocave,a.emballage_poids,c.libelleFR AS catname,c.catid,a.libelleFR,am.marqueid,prixht,paht,emballage_poids FROM article a INNER JOIN article_marque am ON(am.artid=a.artid) INNER JOIN categorie c ON(c.catid=a.catid)  WHERE a.active='1' GROUP BY artcode");
while($article=$DB_site->fetch_array($requete)){
    $parentcat=$DB_site->query_first("SELECT cp.* FROM categorie c INNER JOIN categorie cp ON(cp.catid=c.parentid) WHERE c.catid=".$article['catid']);
    $coutport=achatport($article['emballage_poids']);
    $gar=achatgarantie($article['artid']);
    $achattotal=$article['paht']+$gar+$coutport;
    $prixtotal=$article['prixht']+prixport($article['emballage_poids']);
    
    $row=array();
    $row[]=new ooo_cell($article['artcode_eurocave'],"left");
    $row[]=new ooo_cell($article['artcode'],"left");
    $row[]=new ooo_cell(utf8_encode(html_entity_decode($parentcat['libelleFR'])),"left");
    $row[]=new ooo_cell(utf8_encode(html_entity_decode($article['catname'])),"left");
    $row[]=new ooo_cell(utf8_encode(html_entity_decode($article['libelleFR'])),"left");
    $row[]=new ooo_cell($marque[$article['marqueid']],"left");
    $row[]=new ooo_cell($prixtotal,"right","currency");
    $row[]=new ooo_cell($achattotal,"right","currency");
    $row[]=new ooo_cell("http://www.ma-cave-a-vin.fr/art,".$article['artid'].",".url_rewrite($article['libelleFR']).".html","left");
    $t5->addrow($i,$row);
    $i++;
}

$doc->addtable($t5);

$doc->generate();

function achatport($poids){
	$requete=mysql_query("SELECT * FROM fraisport WHERE ".intval($poids*1000)." BETWEEN poiddebut AND poidfin AND paysid=57");
	if($requete==false) return 0;
	$frais=mysql_fetch_array($requete);
	return floatval($frais['achat']);
}

function prixport($poids){
	$requete=mysql_query("SELECT * FROM fraisport WHERE ".intval($poids*1000)." BETWEEN poiddebut AND poidfin AND paysid=57");
	if($requete==false) return 0;
	$frais=mysql_fetch_array($requete);
	return floatval($frais['prix']/1.196);
}

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