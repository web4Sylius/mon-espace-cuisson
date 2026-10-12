<?php
ini_set("display_errors","1");
error_reporting(E_ALL && ~E_WARNING);
ini_set("max_execution_time",100);


require ("global.php");

// header("content-type: application/xml");
header('Content-Type: application/xml; charset=utf-8');

$rechercherapide["FR"]=array("bouchon verseur","azote","argon","logiciel","thermomètre","hygromètre","tableau de cave","filtres à charbon","waeco","mobicool","preservino","modulocube","modulorack","frigelux","rvf2010","atelier du vin","wae");

//$cachefile=$template->cacheroot."/".$template->site_code()."/".$template->langue."/sitemap".($template->mobile?"_mobile":"").".xml";

/*
if(file_exists($cachefile) && filemtime($cachefile)>time()-86400){
    print file_get_contents($cachefile);
    exit;
}
*/

//ob_start();
echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>
  <urlset xmlns=\"http://www.google.com/schemas/sitemap/0.9\">\n";

$date=date("Y")."-".date("m")."-".date("d");
$host=$template->translate("/site/[site_code]/url");
$host_pro = $template->translate("/site/[site_code]/url_pro");
//if($template->mobile) $host="https://m.mon-espace-cuisson.com";


$host="www.mon-espace-cuisson.com";


if(!$sitepro){

	//home page
	echo "<url><loc>https://$host</loc></url>\n";

	//pages instit
	if(!$template->mobile){
	    $rq_menu = $DB_site->query( "SELECT * FROM institutionnel
									WHERE active='1'
									AND site_code='".$template->site_code()."'
									AND pro IN ('1','3')
									AND menu IN ('0','1','6')
									ORDER BY position" );
	    while( $rs_menu = $DB_site->fetch_array( $rq_menu ) ){
	    	if($rs_menu['libelle'.$template->langue] != ''){
		    	$url_menu = $template->mkurl("institutionnel",$rs_menu['institutionnelid'],$rs_menu);
		    	$url=$host.$url_menu;
		    	echo "<url><loc>https://".$url."</loc></url>\n";
			}
	    }
	}

	//pages conseils d'experts part
	if(!$template->mobile){
	    $rq_menu = $DB_site->query( "SELECT * FROM conseil_part
									WHERE site_code='".$template->site_code()."'
									ORDER BY position" );
	    while( $rs_menu = $DB_site->fetch_array( $rq_menu ) ){
	    	if($rs_menu['libelle'.$template->langue] != ''){
		    	$url_menu = $template->mkurl("conseil",$rs_menu['conseilid'],$rs_menu);
		    	$url=$host.$url_menu;
		    	echo "<url><loc>https://".$url."</loc></url>\n";
			}
	    }
	}


	$categories=$DB_site->query("	SELECT c.catid FROM categorie AS c
									INNER JOIN article AS a ON(a.catid=c.catid)
									WHERE a.active='1'
									AND NOT (parentid=0 AND menu_appartenance=0)
									GROUP BY catid ");
	while($cat=$DB_site->fetch_array($categories)){
		$categorie=new categorie($DB_site,$cat['catid'],$template->langue);
		if(strpos($categorie->url()['url'], "&") === false){
			// echo "<url><loc>".$host.str_replace("&","&amp;",$categorie->url()['url'])."</loc></url>\n";
			echo "<url><loc>https://".$host.$categorie->url()['url']."</loc></url>\n";
			
			$categorie->subcategories();
			$catmarques=$categorie->getmarques();
			if($catmarques!=false){
				foreach($catmarques as $param){
					$count=$DB_site->query_first("	SELECT COUNT(*) FROM article AS a
													INNER JOIN article_marque USING (artid)
													WHERE a.active='1'
													AND catid ='".$categorie->catid."'
													AND marqueid='".$param->id."' ");
					if($count[0]>0){
						$url=$categorie->mkurl(array("catid"=>$categorie->catid,"marqueids"=>$param->id));
						echo "<url><loc>https://".$host.str_replace("&","&amp;",$url['url'])."</loc></url>\n";
					}
				}
			}
		}
	}

	//pages articles

	//Categorie non souhaitées
	$cat = array(432, 142);
	$valeur = "";
	foreach ($cat as $lacat) {
		$categorie = new categorie($DB_site, $lacat, $template->langue);
		$categorie->subcategories();
		foreach ($categorie->subcatids as $souscat) {
			$valeur .= ",".$souscat;
		}
	}
	$valeur = substr($valeur, 1);


	$articles=$DB_site->query(" SELECT a.*,m.libelle AS marque FROM article a
								INNER JOIN article_marque USING (artid)
								INNER JOIN marque m USING(marqueid)
								WHERE catid!=0
								AND catid NOT IN(".$valeur.")
								AND active='1'
								AND redirect=''
								ORDER BY catid");
	$catid="";
	while ($article=$DB_site->fetch_array($articles)) {
		$url=$host.$template->mkurl("article",$article['artid'],$article);
		echo "<url><loc>https://".$url."</loc></url>\n";
	}


	//pages marque
	$marques=new marque($DB_site,0,$template->langue);
	$liste=$marques->listall("marqueid",null,$template->site_code());
	foreach($liste as $marque){
		$nbarticleactif=$DB_site->query_first("SELECT COUNT(*) FROM article AS a
												INNER JOIN article_marque AS am ON (a.artid=am.artid)
												WHERE am.marqueid='".$marque->marqueid."' AND a.active='1' ");
		if($nbarticleactif[0]>0){
			$url=$host.$template->mkurl("marque",$marque->marqueid,$marque);
			echo "<url><loc>https://".$url."</loc></url>\n";
		}
	}

	$url = $host.$template->translate("/site/url_marques",null,$template->langue);
	echo "<url><loc>https://".$url."</loc></url>\n";

	// pages promos
	$promocat=new categorie($DB_site,0,$template->langue);
	$promocat->promo_load();
	$url = $host.$promocat->promo_url;
	echo "<url><loc>https://".$url."</loc></url>\n";

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
			    	$url = $host.$pcat->promo_url;
		         	echo "<url><loc>https://".$url."</loc></url>\n";
			    			
			    }
			}
		
	    }
	}

	//pages photos
	$photopath=$template->translate("/site/gallery_path");
	$gallery=new gallery($DB_site,$template->langue);
	$gallery->path=$photopath;
	$gallery->loadIndex();
	$types = $gallery->getTypes();
	foreach($types as $type){
		if($type!="index"){
		    $list=$gallery->getListByType($type);
		    foreach($list as $g){
				$url = $host.$g->getFullUrl();
				echo "<url><loc>https://".$url."</loc></url>\n";
			}
		}

	}





}else{

	//home page
	echo "<url><loc>$host_pro</loc></url>\n";

	$rq_menu = $DB_site->query( "SELECT * FROM conseil_pro
									WHERE site_code='".$template->site_code()."'
									ORDER BY position" );
	    while( $rs_menu = $DB_site->fetch_array( $rq_menu ) ){
	    	if($rs_menu['libelle'.$template->langue] != ''){
		    	$url_menu = $template->mkurl("conseil",$rs_menu['conseilid'],$rs_menu);
		    	$url=$host_pro.$url_menu;
		    	echo "<url><loc>https://".$url."</loc></url>\n";
			}
	    }

	$categories=$DB_site->query("	SELECT c.catid FROM categorie AS c
									INNER JOIN article AS a ON(a.catid=c.catid)
									WHERE a.active='1'
									AND NOT (parentid=0 AND menu_appartenance=0)
									GROUP BY catid ");
	while($cat=$DB_site->fetch_array($categories)){
		$categorie=new categorie($DB_site,$cat['catid'],$template->langue);
		if(strpos($categorie->url()['url'], "&") === false){
			// echo "<url><loc>".$host.str_replace("&","&amp;",$categorie->url()['url'])."</loc></url>\n";
			echo "<url><loc>https://".$host_pro.$categorie->url()['url']."</loc></url>\n";
			
			$categorie->subcategories();
			$catmarques=$categorie->getmarques();
			if($catmarques!=false){
				foreach($catmarques as $param){
					$count=$DB_site->query_first("	SELECT COUNT(*) FROM article AS a
													INNER JOIN article_marque USING (artid)
													WHERE a.active='1'
													AND catid ='".$categorie->catid."'
													AND marqueid='".$param->id."' ");
					if($count[0]>0){
						$url=$categorie->mkurl(array("catid"=>$categorie->catid,"marqueids"=>$param->id));
						echo "<url><loc>https://".$host_pro.str_replace("&","&amp;",$url['url'])."</loc></url>\n";
					}
				}
			}
		}
	}    

}

echo "</urlset>";

//file_put_contents($cachefile,ob_get_contents());

//ob_end_flush();


?>