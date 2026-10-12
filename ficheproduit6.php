<?php
	//plancha
	include_once "class/articles/article_plancha.php";
	$article_fields = article_plancha_fields();
	foreach($article_fields as $type => $groups) {
		foreach($groups as $group => $fields) {
			foreach($fields as $field => $prop) {
				list($tr, $label, $suffix, $req, $info)= $prop;
				$tabinfoschamps[$field] = array($tr, $label, $suffix, $req, $info);
			}
		}
	}
	$DB_site->query("CREATE TEMPORARY TABLE TempArt AS SELECT * FROM article_plancha WHERE artid=".$article[artid]);
	$DB_site->query("ALTER TABLE TempArt DROP COLUMN id");
	$DB_site->query("ALTER TABLE TempArt DROP COLUMN artid");
	$art = $DB_site->query_first("SELECT * FROM TempArt");


	foreach($art as $key => $value){
		if(is_string($key) && $art[$key]!=""){
			if($key == "poids"){
			$art[$key] = $art[$key]/1000;
			$tabinfoschamps[$key][2] = "Kg";
		}
		if($key == 'couleur') continue;
		    $pdf->setXY($ptablex,$ly);
		    $pdf->cell($col1,4,$template->translate("/article/champs/$key"),0,0,'L',0);
		    $pdf->cell($col2,4,$template->translate($art[$key])." ".$tabinfoschamps[$key][2],0,0,'L',0);
		    $ly+=4;
		 }
	}
	$DB_site->query("DROP TEMPORARY TABLE IF EXISTS TempArt");
?>
