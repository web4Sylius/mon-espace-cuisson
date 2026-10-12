<?php
/// sitemap pages marques et promos
ini_set("display_errors","1");
error_reporting(E_ALL && ~E_WARNING);
ini_set("max_execution_time",100);


require ("global.php");

// header("content-type: application/xml");
header('Content-Type: application/xml; charset=utf-8');

$date=date("Y")."-".date("m")."-".date("d");
$host=$template->translate("/site/[site_code]/url");
$host_pro = $template->translate("/site/[site_code]/url_pro");
//if($template->mobile) $host="https://m.mon-espace-cuisson.com";

echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>
  <urlset xmlns=\"http://www.google.com/schemas/sitemap/0.9\">\n";

if(!$sitepro){
	//pages marque
	$marques=new marque($DB_site,0,$template->langue);
	$liste=$marques->listall("marqueid",null,$template->site_code());
	foreach($liste as $marque){
		$nbarticleactif=$DB_site->query_first("SELECT COUNT(*) FROM article AS a
			INNER JOIN article_marque AS am ON (a.artid=am.artid)
			WHERE am.marqueid='".$marque->marqueid."' AND a.active='1' ");
		if($nbarticleactif[0]>0){
			$url=$host.$template->mkurl("marque",$marque->marqueid,$marque);
			echo "<url><loc>".$url."</loc></url>\n";
		}
	}

	$url = $host.$template->translate("/site/url_marques",null,$template->langue);
	echo "<url><loc>".$url."</loc></url>\n";

	// pages promos
	$promocat=new categorie($DB_site,0,$template->langue);
	$promocat->promo_load();
	$url = $host.$promocat->promo_url;
	echo "<url><loc>".$url."</loc></url>\n";

	$main=new categorie($DB_site,0,$template->langue);
	foreach($main->getMenus() as $idmenu=>$menuinfos){
		$subcatids=$main->getMenu($idmenu);
		$subcatids=array_map(function($a){return $a['catid'];},$subcatids);
		$main->setsubcatids($subcatids);
		$main->setparam("bonsplans",array("promo","selection"));
		$main->setparam("pro",($sitepro?2:1));
		$main->setparam("site_code",$template->site_code());
		$artcount=$main->articlecount($main->subcatids);
	    if($artcount>0) { //on affiches juste les pages où il y a des produits
	    	$i=1;

	    	foreach($subcatids as $menusubcatid){
	    		$pcat=new categorie($DB_site,$menusubcatid,$template->langue);
	    		$pcat->setparam("bonsplans",array("promo","selection"));
	    		$pcat->setparam("pro",($sitepro?2:1));
	    		$pcat->setparam("site_code",$template->site_code());
	    		$pcat->promo_load();
	    		$pcat->setsubcatids($menusubcatid);
	    		$catartcount=$pcat->articlecount($pcat->subcatids,false);
	    		if($catartcount>0){
	    			if($pcat->promo_url!==null){
	    				$url = $host.$pcat->promo_url;
	    				echo "<url><loc>".$url."</loc></url>\n";
	    			}	
	    		}
	    	}
	    }
	}
}

echo "</urlset>";