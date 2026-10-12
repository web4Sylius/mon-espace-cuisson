<?php
include("../admin_global.php");
include("xlexport.php");
$version = array("pro", "part");
if(isset($_GET['langue'])){
	$langue = mysqli_real_escape_string($DB_site2->link_id,$_GET['langue']);
}else{
	$langue = 'FR';
}

$csv = new xlexport("csv","conseils_expert");
$csv->csvsep = ";";
$head = array("titre page", "version", "langue","contenu");
$csv->addrow($head,false);

foreach ($version as $v){
	$txt_version = array("Conseils ".$v);
	$csv->addrow($txt_version,false);
	$req = $DB_site2->query("SELECT *, libelle".$langue." as libelle FROM conseil_$v");
	while($r = $DB_site2->fetch_array($req)){
		$cb = $DB_site2->query("SELECT contenu FROM conseil_bloc_$v WHERE conseilid='".$r['conseilid']."' AND langue='".$langue."' ORDER BY ordre");
		while($cbl = $DB_site2->fetch_array($cb)){
			$content = str_replace("&rsquo;","'",strip_tags($cbl['contenu']));
			// $libellePage = utf8_encode($r['libelle']);
			$libellePage = $r['libelle'];
			// $libellePage = html_entity_decode(strip_tags($r['libelle']),ENT_QUOTES);
			$ligne = array($libellePage,$v,$langue,$content);
			$csv->addrow($ligne,false,false,false,true);
		}
	}
}

$csv->export();




?>