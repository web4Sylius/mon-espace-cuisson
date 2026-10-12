<?php

include ("global.php");
include("class/requetage.class.php");

$categorie=new categorie($DB_site);
$categorie->setparam("site_code",$template->site_code());
$conf=new configurateur($DB_site);
$req=new requetage($DB_site,$template->langue,$categorie);

foreach($req->typesproduits() as $typeproduit){
    $usages=$req->usages($typeproduit);
    if($usages==false) $usages=array(null);
    
    foreach($usages as $usage){
	/*$i=0;
	$liens=array();
	while($i<10){
	    $categorie=new categorie($DB_site);
	    $categorie->setparam("site_code",$template->site_code());
	    $req->random_link($typeproduit,$usage);
	    $cache=$req->get_cache();
	    if($cache===false){
		$conf->requetage($categorie,$req->requetes);
		$articles=$categorie->articles("prix",true);
		$cache=count($articles);
		$req->update_cache($cache);
	    }
	    if($cache>0){
		$liens[$req->url()]=ucfirst($req->titre());
		$i++;
	    }
// 	    $i++;
	}*/
	
	$requetes=$req->from_cache($typeproduit,$usage,10);

	$li=array();
	$sstitre="";
	foreach($requetes as $r){
	    if(!is_null($usage)) $sstitre="<h3>".ucfirst($r->titre_part("typeproduit"))." ".$r->titre_part("usage")."</h3>";
	    $li[]="<li><a href=\"/".$template->translate("/requetage/path")."/".$r->url()."\">".ucfirst($r->titre())."</a></li>";
	}
	$template->parts["liens_".$typeproduit].=$sstitre."<ul>".implode("",$li)."</ul>";
    }
    
   
}


$template->parts["js"]="<script language=\"javascript\" src=\"/javascripts/configurateur.js\"></script>";

include("barrefixe.php");

$template->parts["fil_ariane"]=" &gt; <span property=\"itemListElement\" typeof=\"ListItem\"><a property=\"item\" typeof=\"WebPage\" href=\"/".$template->translate("/requetage/path")."/".$template->translate("/requetage/index_url")."\"><span property=\"name\">".$template->translate("/requetage/link")."</span></a><meta property=\"position\" content=\"2\"></span>";
$template->parts["title"]="Rechercher une cave à vin";
$template->evaltemplate("ReqPageIndex","content");
$metatitle="Rechercher une cave à vin en 1 minute avec Ma Cave à Vin";
$metadesc="Rechercher une cave à vin en 1 minute avec Ma Cave à Vin. Vente en ligne de caves à vin de vieillissement, services ou multi-usages. Retrouvez sur notre site toutes les promotions du moment et nos conseils d'expert.";
$template->pageparams($host,$metatitle,$metadesc);
$template->evaltemplate("InstituPage","main");

$template->out();

?>