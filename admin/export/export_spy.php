<?php

include("../admin_global.php");
include("xlexport.php");

if(!isset($_GET['marqueid'])) exit;

$rm=$DB_site2->query("SELECT * FROM marque WHERE marqueid=".$_GET['marqueid']);
$marque=$DB_site2->fetch_array($rm);

$tvafrance=$DB_site->query_first("SELECT 0,TVAtauxnormal,TVAtauxreduit FROM pays WHERE paysid=57");

$xl=new xlexport("csv");
$xl->name="export_".date("Ymd")."_".$marque['libelle'];

$i=0;
$requete=$DB_site2->query("SELECT LCASE(site) AS site FROM `history` group by `site` order by site");
while($sites=$DB_site2->fetch_array($requete)){
      $colm[$i]=$sites[0];
      $i++;
}

$head=array("Code article","Code fabricant","Prix Vente TTC","Prix achat TTC");
$head=array_merge($head,$colm);
$xl->addrow($head);

$tslimit=time()-(7*86400);

$rart=$DB_site2->query("SELECT a.artcode, a.artcode_eurocave, a.prix, a.paht, a.tauxchoisi FROM article a, article_marque am WHERE  a.artid = am.artid AND am.marqueid =".$_GET['marqueid']);
while($article=$DB_site2->fetch_array($rart)){
      $txtva=(1+$tvafrance[$article['tauxchoisi']]/100);
      $h=array();
      $rh=$DB_site2->query("SELECT timestamp,LCASE(site) AS site,price FROM history WHERE artcode='".$article['artcode']."' AND timestamp>".$tslimit." ORDER BY timestamp");
      while($history=$DB_site2->fetch_array($rh)){
	    $h[$history['site']][$history['timestamp']]=$history['price'];
      }

      $row=array($article['artcode'],$article['artcode_eurocave'],$article['prix'],floatval($article['paht'])*$txtva);
      foreach($colm as $cid=>$name){
	  if($h[$name]!=null){
	      krsort($h[$name]);
	      $keys=array_keys($h[$name]);
	      $first=array_pop($h[$name]);
	      //$row[]=date("d/m/Y",$keys[0])." ".$first;
	      $row[]=$first;
	  }else
	      $row[]="";
      }
      $xl->addrow($row);
}

$xl->export();