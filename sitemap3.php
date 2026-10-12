<?php
/// sitemap pages articles
ini_set("display_errors","1");
error_reporting(E_ALL && ~E_WARNING);
ini_set("max_execution_time",100);


require ("global.php");

// header("content-type: application/xml");
header('Content-Type: application/xml; charset=utf-8');

$date=date("Y")."-".date("m")."-".date("d");
$host=$template->translate("/site/[site_code]/url");
$host_pro = $template->translate("/site/[site_code]/url_pro");

echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>
  <urlset xmlns=\"http://www.google.com/schemas/sitemap/0.9\" xmlns:image=\"http://www.google.com/schemas/sitemap-image/1.1\">\n";

if(!$sitepro){

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
	AND commandable='1'
	AND redirect=''
	ORDER BY catid");
	$catid="";
	$host_url = "www.mon-espace-cuisson.com";
	while ($article=$DB_site->fetch_array($articles)) {
		$url=$host.$template->mkurl("article",$article['artid'],$article);
		echo "<url><loc>".$url."</loc>\n";
		if(file_exists($rootpath."configurations/$host_url/images/produits/".$article['artid'].".".$article['image'])){
			echo "<image:image>
      			<image:loc>".$host."/configurations/$host_url/images/produits/".$article['artid'].".".$article['image']."</image:loc>
    		</image:image>\n";
    	}
    	$article_photos=$DB_site->query("SELECT * FROM `articlephoto` WHERE artid = '".$article['artid']."'");
    	if($DB_site->num_rows($article_photos) > 0){
    		while($article_photo=$DB_site->fetch_array($article_photos)){
    			if(file_exists($rootpath."configurations/$host_url/images/produits/".$article_photo['artid']."_".$article_photo['articlephotoid'].".".$article_photo['image'])){
					echo "<image:image>
      					<image:loc>".$host."/configurations/$host_url/images/produits/".$article_photo['artid']."_".$article_photo['articlephotoid'].".".$article_photo['image']."</image:loc>
    					</image:image>\n";
    			}
    		}
    	}
    	echo "</url>";
	}
}

echo "</urlset>";