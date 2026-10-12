<?php

// $rootpath="/var/www/macave/site/";
// $rootpath="/var/www/vhosts/ma-cave-a-vin.fr/httpdocs/";

$host="www.ma-cave-a-vin.fr" ;
$nostat=1;
include_once("../../configurations/$host/config_mysql.php");
include("../../class/mysql.php");

include("ooo.php");

$DB_site=new DB_Sql;
$DB_site->appname='boutique';
$DB_site->appshortname='boutique';
$DB_site->database=$DBname;
$DB_site->server=$db_server;
$DB_site->user=$DBusername;
$DB_site->password=$DBpassword;
$DB_site->connect();

$prices=array();
$artcode=array();
$fabcode=array();
$sites=array();
$moy=array();
$achat=array();
$poids=array();

$tvafrance=$DB_site->query_first("SELECT 0,TVAtauxnormal,TVAtauxreduit FROM pays WHERE paysid=57");
$txtva=array(1=>1+$tvafrance[1]/100,2=>1+$tvafrance[2]/100);

$sql="SELECT marqueid,libelle FROM marque";
if(isset($_GET['marqueid'])) $sql.=" WHERE marqueid=".$_GET['marqueid'];
$requete=$DB_site->query($sql);
while($data=$DB_site->fetch_array($requete)){
    $libmarque[$data['marqueid']]=$data['libelle'];
}
asort($libmarque);

$sql="SELECT artid,artcode,artcode_eurocave,active,paht,ecopartht,prixtrspt,tauxchoisi,prix,emballage_poids,marqueid FROM article a INNER JOIN article_marque USING(artid) WHERE active IN ('0','1') AND (SELECT COUNT(*) FROM article_lot WHERE lotid=a.artid)=0";
if(isset($_GET['marqueid'])) $sql.=" AND marqueid=".$_GET['marqueid'];
$requete=$DB_site->query($sql);
while($data=$DB_site->fetch_array($requete)){
    $portdata=$DB_site->query_first("SELECT prix,achat,gratuit FROM fraisport WHERE (".round(1000*$data['emballage_poids'])." BETWEEN poiddebut AND poidfin) AND paysid=57");
    $garantie=0;
    $garanties=$DB_site->query("SELECT (select achat from article_caractval inner join caracteristiquevaleur using(caractvalid) where artid=".$data['artid']." and caractid=cv.caractid order by position limit 1) FROM article_caractval ac INNER JOIN caracteristiquevaleur cv USING (caractvalid) WHERE artid=".$data['artid']." group by caractid");
    while($g=$DB_site->fetch_array($garanties)) $garantie+=$g[0];
    $prix[$data['artcode']]=$data['prix'];
    if($portdata['gratuit']>0 && $portdata['gratuit']<=$data['prix']) $portdata['prix']=0;
    $port[$data['artcode']]=$portdata['prix'];
    $achat[$data['artcode']]=round(($data['paht']+$portdata['achat']+$data['prixtrspt']+$garantie+$data['ecopartht'])*$txtva[$data['tauxchoisi']],2);
    if($prix[$data['artcode']]>0) $marge[$data['artcode']]=round((($data['prix']+$portdata['prix'])-$achat[$data['artcode']])/($data['prix']+$portdata['prix']),4);
    $poids[$data['artcode']]=$data['emballage_poids'];
    $fabcode[$data['artcode']]=$data['artcode_eurocave'];
    $marques[$data['marqueid']][$data['artcode']]=$data['artcode'];
    $active[$data['artcode']]=($data['active']==1?"oui":"non");
    $ca[$data['artcode']]=0;
}

$year=time()-(365*86400);
$yearm1=$year-(365*86400);

$t1=$year;
$t2=time();
$req=$DB_site->query("SELECT artcode,SUM(qte*prix),SUM(qte) FROM lignefacture lf INNER JOIN facture f ON(f.facturid=lf.factureid) WHERE valide IN (1,5,7,8) AND prix>=0 AND heurefacture BETWEEN $t1 AND $t2 GROUP BY artcode");
while($cadata=$DB_site->fetch_array($req)){
    $ca[$cadata['artcode']]=$cadata[1];
    $caqte[$cadata['artcode']]=$cadata[2];
}
arsort($ca);
$sortkeys=array_flip(array_keys($ca));
// var_dump($sortkeys);

$t1=$yearm1;
$t2=$year;
$req=$DB_site->query("SELECT artcode,SUM(qte*prix),SUM(qte) FROM lignefacture lf INNER JOIN facture f ON(f.facturid=lf.factureid) WHERE valide IN (1,5,7,8) AND prix>=0 AND heurefacture BETWEEN $t1 AND $t2 GROUP BY artcode");
while($cadata=$DB_site->fetch_array($req)){
    $cam1[$cadata['artcode']]=$cadata[1];
    $caqtem1[$cadata['artcode']]=$cadata[2];
}

$tslimit=time()-864000;
$sql="SELECT h.price,h.site,a.artcode,marqueid FROM history h INNER JOIN article a ON (h.artcode=a.artcode) INNER JOIN article_marque USING(artid) WHERE a.active<>'2' AND h.timestamp>$tslimit";
if(isset($_GET['site'])) $sql.=" AND site='".$_GET['site']."'";
if(isset($_GET['marqueid'])) $sql.=" AND marqueid=".$_GET['marqueid'];
$requete=$DB_site->query($sql);
while($data=$DB_site->fetch_array($requete)){
    $sites[ucfirst($data['site'])]=1;
    $prices[$data['artcode']][ucfirst($data['site'])]=$data['price'];
    $marquesite[$data['marqueid']][]=ucfirst($data['site']);
}

ksort($artcode);

$doc=new ooo();

$doc->name="export spy ".date("Y-m-d");

$s0=new ooo_style("inf");
$s0->currencydigits=2;
$s0->family="table-cell";
$s0->font_color="#FF0000";
$s0->text_align="right";
$s0->border_bottom="0.5pt solid #909090";
$s0->border_top=$s0->border_left=$s0->border_right=$s0->border_bottom;
$s1=new ooo_style("code");
$s1->family="table-cell";
$s1->background_color="#d4AAFF";
$s1->border_bottom="0.5pt solid #000000";
$s1->border_top=$s1->border_left=$s1->border_right=$s1->border_bottom;
$s2=new ooo_style("site");
$s2->family="table-cell";
$s2->background_color="#00AAFF";
$s2->border_bottom="0.5pt solid #000000";
$s2->border_top=$s2->border_left=$s2->border_right=$s2->border_bottom;
$s3=new ooo_style("macave");
$s3->family="table-cell";
$s3->background_color="#ddb69e";
$s3->text_align="right";
$s3->border_bottom="0.5pt solid #000000";
$s3->border_top=$s3->border_left=$s3->border_right=$s3->border_bottom;
$s4=new ooo_style("title");
$s4->family="table-cell";
$s4->font_color="#000000";
$s4->font_bold="bold";
$s5=new ooo_style("moyenne");
$s5->family="table-cell";
$s5->background_color="#fa7e7e";
$s5->text_align="right";
$s5->border_bottom="0.5pt solid #909090";
$s5->border_top=$s3->border_left=$s3->border_right=$s3->border_bottom;
$s6=new ooo_style("prix");
$s6->family="table-cell";
$s6->font_color="#000000";
$s6->text_align="right";
$s6->currencydigits=2;
$s6->border_bottom="0.5pt solid #909090";
$s6->border_top=$s6->border_left=$s6->border_right=$s6->border_bottom;
$s7=new ooo_style("sep");
$s7->family="table-cell";
$s7->background_color="#FFFF00";
$s7->border_bottom="0.5pt solid #909090";
$s7->border_top=$s7->border_left=$s7->border_right=$s7->border_bottom;
$s8=new ooo_style("nospy");
$s8->family="table-cell";
$s8->background_color="#CCCCCC";
$s8->border_bottom="0.5pt solid #909090";
$s8->border_top=$s8->border_left=$s8->border_right=$s8->border_bottom;
$s9=clone($s6);
$s9->name="nospyprix";
$s9->background_color="#CCCCCC";
$s10=new ooo_style("pc");
$s10->family="table-cell";
$s10->font_color="#000000";
$s10->text_align="right";
$s10->percentagedigits=2;
$s10->border_bottom="0.5pt solid #909090";
$s10->border_top=$s10->border_left=$s10->border_right=$s10->border_bottom;
$s11=clone($s10);
$s11->name="nospypc";
$s11->background_color="#CCCCCC";

$doc->addstyle($s0);
$doc->addstyle($s1);
$doc->addstyle($s2);
$doc->addstyle($s3);
$doc->addstyle($s4);
$doc->addstyle($s5);
$doc->addstyle($s6);
$doc->addstyle($s7);
$doc->addstyle($s8);
$doc->addstyle($s9);
$doc->addstyle($s10);
$doc->addstyle($s11);


$titles=array();
$titles[]=new ooo_cell("Code article","code");
$titles[]=new ooo_cell("Code fabricant","code");
$titles[]=new ooo_cell("Actif","code");
$titles[]=new ooo_cell("Prix vente TTC\nhors trspt","macave");
$titles[]=new ooo_cell("Prix vente TTC\ntrspt inclus","macave");
$titles[]=new ooo_cell("Prix achat\ntotal TTC","macave");
$titles[]=new ooo_cell("% marge brute","macave");
$titles[]=new ooo_cell("","sep");
$titles[]=new ooo_cell("Qte\nconcurrent","macave");
$titles[]=new ooo_cell("Qté vendue\nsur 12 mois","macave");
$titles[]=new ooo_cell("CA sur 12\nderniers mois","macave");
$titles[]=new ooo_cell("Qté vendue\nn-1 (12 mois)","macave");
$titles[]=new ooo_cell("CA N-1\n(12 mois)","macave");
$titles[]=new ooo_cell("","sep");
$titles[]=new ooo_cell("Prix mini","macave");
$titles[]=new ooo_cell("Marge si\nprix mini","macave");
$titles[]=new ooo_cell("Prix maxi","macave");
$titles[]=new ooo_cell("Marge si\nprix maxi","macave");


$table=new ooo_table("TOUT");
$c1=new ooo_cell("Tous prix concurrents a partir du ".date("d/m/Y",$tslimit));
$c1->style="title";
$table->addcell(1,1,$c1);
$table->set_row(1,"1cm");
$table->set_col(3,"1cm");
$table->set_col(8,"0.3cm");
$table->set_col(14,"0.3cm");
$titles2=array();

ksort($sites);
foreach($sites as $site=>$un){
  $titles2[]=new ooo_cell($site,"site");
}
$table->addrow(2,array_merge($titles,$titles2));

$rows=array();
foreach($prices as $artcode=>$lsites){
    $x=19;
    $row=array();
    $min=min($lsites);
    $max=max($lsites);
    $prixtotal=($prix[$artcode]+$port[$artcode]);
    $row[15]=new ooo_cell($min,"prix","currency");
    if($min>0)
      $row[16]=new ooo_cell(round(($min-$achat[$artcode])/$min,4),"pc","currency");
    else
      $row[16]=new ooo_cell("");
    $row[17]=new ooo_cell($max,"prix","currency");
    if($max>0)
      $row[18]=new ooo_cell(round(($max-$achat[$artcode])/$max,4),"pc","currency");
    else
      $row[18]=new ooo_cell("");
    foreach($sites as $site=>$un){
	if(isset($lsites[$site]))
	  $row[$x]=new ooo_cell($lsites[$site],($lsites[$site]<$prixtotal?"inf":"prix"),"currency");
	else
	  $row[$x]=new ooo_cell("");
	$x++;
    }
    $row[1]=new ooo_cell($artcode);
    $row[2]=new ooo_cell($fabcode[$artcode]);
    $row[3]=new ooo_cell($active[$artcode],$class);
    $row[4]=new ooo_cell($prix[$artcode],"prix","currency");
    $row[5]=new ooo_cell($prixtotal,"prix","currency");
    $row[6]=new ooo_cell($achat[$artcode],"prix","currency");
    $row[7]=new ooo_cell($marge[$artcode],"pc","currency");
    $row[8]=new ooo_cell("","sep");
    $row[9]=new ooo_cell(intval(count($prices[$artcode])),"float");
    $row[10]=new ooo_cell($caqte[$artcode],"float");
    $row[11]=new ooo_cell($ca[$artcode],"prix","currency");
    $row[12]=new ooo_cell($caqtem1[$artcode],"float");
    $row[13]=new ooo_cell($cam1[$artcode],"prix","currency");
    $row[14]=new ooo_cell("","sep");
    ksort($row);

    $rows[intval($sortkeys[$artcode])]=$row;
}

$i=3;
ksort($rows);
foreach($rows as $row){ 
  $table->addrow($i,$row);
  $i++;
}

$doc->addtable($table);


foreach($libmarque as $marqueid=>$libelle){
    $artcodes=$marques[$marqueid];
    $table=new ooo_table(utf8_encode($libelle));
    $c1=new ooo_cell(utf8_encode("Prix concurrents $libelle a partir du ".date("d/m/Y",$tslimit)));
    $c1->style="title";
    $table->addcell(1,1,$c1);
    $table->set_row(1,"1cm");
    $table->set_col(3,"1cm");
    $table->set_col(8,"0.3cm");
    $table->set_col(14,"0.3cm");
    $titles2=array();
    if(isset($marquesite[$marqueid])){
	$marquesite[$marqueid]=array_unique($marquesite[$marqueid]);
	asort($marquesite[$marqueid]);
	foreach($marquesite[$marqueid] as $site){
	    $titles2[]=new ooo_cell($site,"site");
	}
    }
    $table->addrow(2,array_merge($titles,$titles2));
    $i=3;
    $rows=array();
    if(isset($artcodes)){
      foreach($artcodes as $artcode){
	  $row=array();
	  $class="nospy";
	  $x=19;
	  $prixtotal=($prix[$artcode]+$port[$artcode]);
	  if(isset($marquesite[$marqueid])){
	      if(isset($prices[$artcode])){
		$class=null;
		ksort($prices[$artcode]);
		$min=min($prices[$artcode]);
		$max=max($prices[$artcode]);
		$row[15]=new ooo_cell($min,"prix","currency");
		if($min>0)
		  $row[16]=new ooo_cell(round(($min-$achat[$artcode])/$min,4),"pc","currency");
		else
		  $row[16]=new ooo_cell("");
		$row[17]=new ooo_cell($max,"prix","currency");
		if($max>0)
		  $row[18]=new ooo_cell(round(($max-$achat[$artcode])/$max,4),"pc","currency");
		else
		  $row[18]=new ooo_cell("");
		foreach($marquesite[$marqueid] as $site){
		  if(isset($prices[$artcode][$site]))
		    $row[$x]=new ooo_cell($prices[$artcode][$site],($prices[$artcode][$site]<$prixtotal?"inf":"prix"),"currency");
		  else
		    $row[$x]=new ooo_cell("");
		  $x++;
		}
	      }else{
		$row[15]=$row[16]=$row[17]=$row[18]=new ooo_cell("","nospy");
		foreach($marquesite[$marqueid] as $site){
		  $row[$x]=new ooo_cell("","nospy");
		  $x++;
		}
	      }
	  }
	  $row[1]=new ooo_cell($artcode,$class);
	  $row[2]=new ooo_cell($fabcode[$artcode],$class);
	  $row[3]=new ooo_cell($active[$artcode],$class);
	  $row[4]=new ooo_cell($prix[$artcode],$class."prix","currency");
	  $row[5]=new ooo_cell($prixtotal,$class."prix","currency");
	  $row[6]=new ooo_cell($achat[$artcode],$class."prix","currency");
	  $row[7]=new ooo_cell($marge[$artcode],$class."pc","currency");
	  $row[8]=new ooo_cell("","sep");
	  $row[9]=new ooo_cell(intval(count($prices[$artcode])),$class,"float");
	  $row[10]=new ooo_cell($caqte[$artcode],$class,"float");
	  $row[11]=new ooo_cell($ca[$artcode],$class."prix","currency");
	  $row[12]=new ooo_cell($caqtem1[$artcode],$class,"float");
	  $row[13]=new ooo_cell($cam1[$artcode],$class."prix","currency");
	  $row[14]=new ooo_cell("","sep");
	  ksort($row);
	  $rows[intval($sortkeys[$artcode])]=$row;
      }

      ksort($rows);
      foreach($rows as $row){ 
	$table->addrow($i,$row);
	$i++;
      }
    }
    $doc->addtable($table);
}

$doc->generate();


?>