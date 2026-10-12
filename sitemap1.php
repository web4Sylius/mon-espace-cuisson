<?php
/// sitemap pages CMS (instit / conseils)
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

	//home page
	echo "<url><loc>$host</loc></url>\n";

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
				echo "<url><loc>".$url."</loc></url>\n";
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
				echo "<url><loc>".$url."</loc></url>\n";
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
			echo "<url><loc>".$url."</loc></url>\n";
		}
	}
}

echo "</urlset>";