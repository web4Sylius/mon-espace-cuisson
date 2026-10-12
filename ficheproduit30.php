<?php
//rotissoire
if($article['capacite_bouteilles']!=""){
    $pdf->setXY($ptablex,$ly);
    $pdf->cell($col1,4,$template->translate("/devis/capacite","F"),0,0,'L',0);
    $pdf->cell($col2,4,$article['capacite_bouteilles']." ".$template->translate($article['capacite_unite'])." (".$template->translate("/devis/mini","F")." $article[capacite_mini] - ".$template->translate("/devis/maxi","F")." $article[capacite_maxi])",0,0,'L',0);
    $ly+=4;
}
if($article['coloris']!=""){
    $pdf->setXY($ptablex,$ly);
    $pdf->cell($col1,4,$template->translate("/devis/coloris_produit","F"),0,0,'L',0);
    $pdf->cell($col2,4,$template->translate($article['coloris']),0,0,'L',0);
    $ly+=4;
}
	
$finition=$DB_site->query_first("SELECT nom".$template->langue." FROM recherche_finition INNER JOIN articlerecherche_finition USING(finitionid) WHERE artid=".$article['artid']);
if($finition[0]!=""){
    $pdf->setXY($ptablex,$ly);
    $pdf->cell($col1,4,$template->translate("/devis/niveau_gamme","F"),0,0,'L',0);
    $pdf->cell($col2,4,$finition[0],0,0,'L',0);
    $ly+=4;
}
$ly+=2;

$pdf->setXY($ptablex,$ly);
$pdf->textColor($autocolor);
$pdf->setfont('arial','B',10);
$pdf->cell($col1+$col2,5,$template->translate("/devis/caracteristiques_principales","F"),0,0,'L',0);
$pdf->setfont('arial','',8);
$pdf->textColor("black");
$lheight=5;
$ly+=5;

if($article['type_climatiseur']!=""){
    $pdf->setXY($ptablex,$ly);
    $txt=$template->translate($article['type_climatiseur']);
    $hcol=$pdf->nblines($col2,$txt)*$lheight;
    $pdf->cell($col1,$hcol,$template->translate("/devis/type_climatiseur","F"),0);
    $pdf->multicell($col2,$lheight,$txt,0,'',0);
    $ly+=$hcol;
}
if($article['nbr_temperature']!=""){
    $pdf->setXY($ptablex,$ly);
    $txt=$template->translate($article['nbr_temperature']);
    $hcol=$pdf->nblines($col2,$txt)*$lheight;
    $pdf->cell($col1,$hcol,$template->translate("/devis/nombre_temperatures","F"),0);
    $pdf->multicell($col2,$lheight,$txt,0,'',0);
    $ly+=$hcol;
}
if($article['plage_temperature']!=""){
    $pdf->setXY($ptablex,$ly);
    $txt=$template->translate($article['plage_temperature']);
    $hcol=$pdf->nblines($col2,$txt)*$lheight;
    $pdf->cell($col1,$hcol,$template->translate("/devis/plage_temperature","F"),0);
    $pdf->multicell($col2,$lheight,$txt,0,'',0);
    $ly+=$hcol;
}
if($article['filtre_poussiere']!=""){
    $pdf->setXY($ptablex,$ly);
    $txt=$template->translate($article['filtre_poussiere']);
    $hcol=$pdf->nblines($col2,$txt)*$lheight;
    $pdf->cell($col1,$hcol,$template->translate("/devis/filtre_poussiere","F"),0);
    $pdf->multicell($col2,$lheight,$txt,0,'',0);
    $ly+=$hcol;
}
if($article['distance_unites']>0){
    $pdf->setXY($ptablex,$ly);
    $txt=$article['distance_unites']."m";
    $hcol=$pdf->nblines($col2,$txt)*$lheight;
    $pdf->cell($col1,$hcol,$template->translate("/devis/distance_unites","F"),0);
    $pdf->multicell($col2,$lheight,$txt,0,'',0);
    $ly+=$hcol;
}
if($article['longueur_gaine']>0){
    $pdf->setXY($ptablex,$ly);
    $txt=$article['longueur_gaine']."m";
    $hcol=$pdf->nblines($col2,$txt)*$lheight;
    $pdf->cell($col1,$hcol,$template->translate("/devis/longueur_gaine","F"),0);
    $pdf->multicell($col2,$lheight,$txt,0,'',0);
    $ly+=$hcol;
}

 
$req=$DB_site->query("SELECT qte,libelle FROM article_accessoire WHERE artid=".$article['artid']);
if($DB_site->num_rows($req)>0){
    $pdf->setXY($ptablex,$ly);
    $txt="";
    
    while($acc=$DB_site->fetch_array($req)){
	$txt.=$acc['qte']." ".$template->translate($acc['libelle'],"L")."\n";
    }
    $hcol=$DB_site->num_rows($req)*$lheight;
    $pdf->cell($col1,$hcol,$template->translate("/devis/accessoires_inclus","F"),0);
    $pdf->multicell($col2,$lheight,$txt,0,'',0);
    $ly+=$hcol;
}
if($article['temperature_cave']!=""){
    $pdf->setXY($ptablex,$ly);
    $txt=$template->translate($article['temperature_cave']);
    $pdf->multicell($col1,$lheight,$template->translate("/devis/temperature_installation","F"),0);
    $pdf->setXY($ptablex+$col1,$ly);
    $pdf->multicell($col2,$lheight,$txt,0,'',0);
    $ly+=$lheight;
}
if($article['energy_label']!=""){
    $pdf->setXY($ptablex,$ly);
    $hcol=$lheight;
    $pdf->cell($col1,$hcol,$template->translate("/devis/classe_energetique","F"),0);
    $pdf->multicell($col2,$lheight,$article['energy_label'],0,'',0);
    $ly+=$hcol;
}
$pdf->setXY($ptablex,$ly);
$hcol=$pdf->nblines($col2,$article['dimensions'])*$lheight;
$pdf->cell($col1,$hcol,$template->translate("/devis/dimensions","F"),0);
$pdf->multicell($col2,$lheight,sprintf($template->translate("/article/champs/dimensions_format"),$article['hauteur'],$article['largeur'],$article['profondeur']),0,'',0);
$ly+=$hcol;
if($article['conso_kw']!=0){
    $pdf->setXY($ptablex,$ly);
    $txt=sprintf($template->translate("/devis/kw_24h_soit_ttc"),round($article['conso_kw'],2),round($article['conso_kw']*$prixkw*365)." €");
    $hcol=$pdf->nblines($col2,$txt)*$lheight;
    $pdf->cell($col1,$hcol,$template->translate("/devis/consommation_energie","F"),0);
    $pdf->multicell($col2,$lheight,$txt,0,'',0);
    $ly+=$hcol;
}
if($article['niveau_bruit']!=""){
    $pdf->setXY($ptablex,$ly);
    $hcol=$pdf->nblines($col2,trim($article['niveau_bruit'])." dB")*$lheight;
    $pdf->cell($col1,$hcol,$template->translate("/devis/niveau_bruit","F"),0);
    $pdf->multicell($col2,$lheight,trim($article['niveau_bruit'])." dB",0,'',0);
    $ly+=$hcol;
}
if($article['paysfabrication']!=""){
    $pdf->setXY($ptablex,$ly);
    $pdf->cell($col1,$lheight,$template->translate("/devis/origine_fabrication","F"),0);
    $pdf->multicell($col2,$lheight,$template->translate("/article/origines/".$article['paysfabrication']),0,'',0);
    $ly+=$lheight;
}

$ly+=2;

$pdf->setXY($ptablex,$ly);
$pdf->textColor($autocolor);
$pdf->setfont('arial','B',10);
$pdf->cell($col1+$col2,5,$template->translate("/devis/groupe_condensation","F"),0,0,'L',0);
$pdf->setfont('arial','',8);
$pdf->textColor("black");
$lheight=5;
$ly+=5;

if($article['puissance_frigo']>0){
    $pdf->setXY($ptablex,$ly);
    $txt=$article['puissance_frigo']."W";
    $hcol=$pdf->nblines($col2,$txt)*$lheight;
    $pdf->cell($col1,$hcol,$template->translate("/devis/puissance_frigo","F"),0);
    $pdf->multicell($col2,$lheight,$txt,0,'',0);
    $ly+=$hcol;
}

if($article['niveau_sonore_1m']+$article['niveau_sonore_3m']+$article['niveau_sonore_5m']>0){
    $pdf->setXY($ptablex,$ly);
    $txt="";
    if($article['niveau_sonore_1m']>0) $txt.=" @1m = ".$article['niveau_sonore_1m']."dB";
    if($article['niveau_sonore_3m']>0) $txt.=" @3m = ".$article['niveau_sonore_3m']."dB";
    if($article['niveau_sonore_5m']>0) $txt.=" @5m = ".$article['niveau_sonore_5m']."dB";
    $txt=trim($txt);
    $hcol=$pdf->nblines($col2,$txt)*$lheight;
    $pdf->cell($col1,$hcol,$template->translate("/devis/niveau_sonore","F"),0);
    $pdf->multicell($col2,$lheight,$txt,0,'',0);
    $ly+=$hcol;
}

if($article['kit_sonore']!=""){
    $pdf->setXY($ptablex,$ly);
    $txt=$template->translate($article['kit_sonore']);
    $pdf->multicell($col1,$lheight,$template->translate("/devis/kit_bas_niveau_sonore","F"),0);
    $pdf->setXY($ptablex+$col1,$ly);
    $pdf->multicell($col2,$lheight,$txt,0,'',0);
    $ly+=$lheight;
}

if($article['gaz']!=""){
    $pdf->setXY($ptablex,$ly);
    $txt=$template->translate($article['gaz']);
    $pdf->multicell($col1,$lheight,$template->translate("/devis/type_gaz","F"),0);
    $pdf->setXY($ptablex+$col1,$ly);
    $pdf->multicell($col2,$lheight,$txt,0,'',0);
    $ly+=$lheight;
}

if($article['debit_air_ext']>0){
    $pdf->setXY($ptablex,$ly);
    $txt=$article['debit_air_ext']."m".chr(179)."/h";
    $hcol=$pdf->nblines($col2,$txt)*$lheight;
    $pdf->cell($col1,$hcol,$template->translate("/devis/debit_air_ext","F"),0);
    $pdf->multicell($col2,$lheight,$txt,0,'',0);
    $ly+=$hcol;
}

if($article['coloris_groupe']!=""){
    $pdf->setXY($ptablex,$ly);
    $txt=$template->translate($article['coloris_groupe']);
    $pdf->multicell($col1,$lheight,$template->translate("/devis/coloris_groupe","F"),0);
    $pdf->setXY($ptablex+$col1,$ly);
    $pdf->multicell($col2,$lheight,$txt,0,'',0);
    $ly+=$lheight;
}

if($article['hauteur_groupe']+$article['largeur_groupe']+$article['profondeur_groupe']+$article['poids_net_groupe']>0){
    $pdf->setXY($ptablex,$ly);
    $txt=sprintf($template->translate("/article/champs/dimensions_format"),$article['hauteur_groupe'],$article['largeur_groupe'],$article['profondeur_groupe'])." ".round($article['poids_net_groupe']/1000,1)."kg";
    $pdf->multicell($col1,$lheight,$template->translate("/devis/dimensions","F"),0);
    $pdf->setXY($ptablex+$col1,$ly);
    $pdf->multicell($col2,$lheight,$txt,0,'',0);
    $ly+=$lheight;
}

$ly+=2;


$pdf->setXY($ptablex,$ly);
$pdf->textColor($autocolor);
$pdf->setfont('arial','B',10);
$pdf->cell($col1+$col2,5,$template->translate("/devis/evaporateur","F"),0,0,'L',0);
$pdf->setfont('arial','',8);
$pdf->textColor("black");
$lheight=5;
$ly+=5;

if($article['nb_ventilateur']>0){
    $pdf->setXY($ptablex,$ly);
    $txt=$article['nb_ventilateur'];
    $hcol=$pdf->nblines($col2,$txt)*$lheight;
    $pdf->cell($col1,$hcol,$template->translate("/devis/nb_ventilateur","F"),0);
    $pdf->multicell($col2,$lheight,$txt,0,'',0);
    $ly+=$hcol;
}


if($article['debit_air_int']>0){
    $pdf->setXY($ptablex,$ly);
    $txt=$article['debit_air_int']."m".chr(179)."/h";
    $hcol=$pdf->nblines($col2,$txt)*$lheight;
    $pdf->cell($col1,$hcol,$template->translate("/devis/debit_air_int","F"),0);
    $pdf->multicell($col2,$lheight,$txt,0,'',0);
    $ly+=$hcol;
}

if($article['puissance_chauffage']>0){
    $pdf->setXY($ptablex,$ly);
    $txt=$article['puissance_chauffage']."W";
    $hcol=$pdf->nblines($col2,$txt)*$lheight;
    $pdf->cell($col1,$hcol,$template->translate("/devis/puissance_chauffage","F"),0);
    $pdf->multicell($col2,$lheight,$txt,0,'',0);
    $ly+=$hcol;
}

if($article['kit_humidificateur']!=""){
    $pdf->setXY($ptablex,$ly);
    $txt=$template->translate($article['kit_humidificateur']);
    $pdf->multicell($col1,$lheight,$template->translate("/devis/kit_humidificateur","F"),0);
    $pdf->setXY($ptablex+$col1,$ly);
    $pdf->multicell($col2,$lheight,$txt,0,'',0);
    $ly+=$lheight;
}

if($article['evacuation_eau']!=""){
    $pdf->setXY($ptablex,$ly);
    $txt=$template->translate($article['evacuation_eau']);
    $pdf->multicell($col1,$lheight,$template->translate("/devis/evacuation_eau","F"),0);
    $pdf->setXY($ptablex+$col1,$ly);
    $pdf->multicell($col2,$lheight,$txt,0,'',0);
    $ly+=$lheight;
}

if($article['coloris_evaporateur']!=""){
    $pdf->setXY($ptablex,$ly);
    $txt=$template->translate($article['coloris_evaporateur']);
    $pdf->multicell($col1,$lheight,$template->translate("/devis/coloris_evaporateur","F"),0);
    $pdf->setXY($ptablex+$col1,$ly);
    $pdf->multicell($col2,$lheight,$txt,0,'',0);
    $ly+=$lheight;
}

if($article['hauteur_evaporateur']+$article['largeur_evaporateur']+$article['profondeur_evaporateur']>0){
    $pdf->setXY($ptablex,$ly);
    $txt=sprintf($template->translate("/article/champs/dimensions_format"),$article['hauteur_evaporateur'],$article['largeur_evaporateur'],$article['profondeur_evaporateur'])." ".round($article['poids_net_evaporateur']/1000,1) ."kg";
    $pdf->multicell($col1,$lheight,$template->translate("/devis/dimensions","F"),0);
    $pdf->setXY($ptablex+$col1,$ly);
    $pdf->multicell($col2,$lheight,$txt,0,'',0);
    $ly+=$lheight;
}

$ly+=2;

if($article['regulation'].$article['temperature'].$article['thermometre'].$article['thermometre'].$article['reglage'].$article['affichage']!=""){
	$pdf->setXY($ptablex,$ly);
	$pdf->textColor($autocolor);
	$pdf->setfont('arial','B',10);
	$pdf->cell($col1+$col2,5,$template->translate("/devis/regulation","F"),0,0,'L',0);
	$pdf->setfont('arial','',8);
	$pdf->textColor("black");
	$ly+=5;

	if($article['regulation']!=""){
	    $pdf->setXY($ptablex,$ly);
	    $txt=$template->translate(stripslashes($article['regulation']));
	    $hcol=$pdf->nblines($col2,$txt)*$lheight;
	    $pdf->cell($col1,$hcol,$template->translate("/devis/regulation","F"),0);
	    $pdf->multicell($col2,$lheight,$txt,0,'',0);
	    $ly+=$hcol;
	}
	if($article['temperature']!=""){
	    $pdf->setXY($ptablex,$ly);
	    $txt=$template->translate(stripslashes($article['temperature']));
	    $hcol=$pdf->nblines($col2,$txt)*$lheight;
	    $pdf->cell($col1,$hcol,$template->translate("/devis/temperature","F"),0);
	    $pdf->multicell($col2,$lheight,$txt,0,'',0);
	    $ly+=$hcol;
	}
	if($article['thermometre']!=""){
	    $pdf->setXY($ptablex,$ly);
	    $txt=$template->translate($article['thermometre']);
	    $hcol=$pdf->nblines($col2,$txt)*$lheight;
	    $pdf->cell($col1,$hcol,$template->translate("/devis/thermometre","F"),0);
	    $pdf->multicell($col2,$lheight,$txt,0,'',0);
	    $ly+=$hcol;
	}
	if($article['reglage']!=""){
	    $pdf->setXY($ptablex,$ly);
	    $txt=$template->translate($article['reglage']);
	    $hcol=$pdf->nblines($col2,$txt)*$lheight;
	    $pdf->cell($col1,$hcol,$template->translate("/devis/reglage","F"),0);
	    $pdf->multicell($col2,$lheight,$txt,0,'',0);
	    $ly+=$hcol;
	}
	if($article['affichage']!=""){
	    $pdf->setXY($ptablex,$ly);
	    $txt=$template->translate($article['affichage']);
	    $hcol=$pdf->nblines($col2,$txt)*$lheight;
	    $pdf->cell($col1,$hcol,html_entity_decode($template->translate("/devis/affichage","F"),ENT_COMPAT,"ISO-8859-15"),0);
	    $pdf->multicell($col2,$lheight,$txt,0,'',0);
	    $ly+=$hcol;
	}
	if($article['position_regulateur']!=""){
	    $pdf->setXY($ptablex,$ly);
	    $txt=$template->translate($article['position_regulateur']);
	    $hcol=$pdf->nblines($col2,$txt)*$lheight;
	    $pdf->cell($col1,$hcol,html_entity_decode($template->translate("/devis/position_regulateur","F"),ENT_COMPAT,"ISO-8859-15"),0);
	    $pdf->multicell($col2,$lheight,$txt,0,'',0);
	    $ly+=$hcol;
	}
	$ly+=3;
}

if($article['hygrometrie'].$article['hygrometre']!=""){
	$pdf->setXY($ptablex,$ly);
	$pdf->textColor($autocolor);
	$pdf->setfont('arial','B',10);
	$pdf->cell($col1+$col2,5,$template->translate("/devis/equipement","F"),0,0,'L',0);
	$pdf->setfont('arial','',8);
	$pdf->textColor("black");
	$ly+=5;

	if($article['hygrometrie']!=""){
	    $pdf->setXY($ptablex,$ly);
	    $txt=$template->translate($article['hygrometrie']);
	    $hcol=$pdf->nblines($col2,$txt)*$lheight;
	    $pdf->cell($col1,$hcol,$template->translate("/devis/hygrometrie","F"),0);
	    $pdf->multicell($col2,$lheight,$txt,0,'',0);
	    $ly+=$hcol;
	}
	if($article['hygrometre']!=""){
	    $pdf->setXY($ptablex,$ly);
	    $txt=$template->translate($article['hygrometre']);
	    $hcol=$pdf->nblines($col2,$txt)*$lheight;
	    $pdf->cell($col1,$hcol,$template->translate("/devis/hygrometre","F"),0);
	    $pdf->multicell($col2,$lheight,$txt,0,'',0);
	    $ly+=$hcol;
	}
	$ly+=3;
}

if($article['production_froid'].$article['production_chaud'].$article['fonction_hiver_techno']!=""){
	$pdf->setXY($ptablex,$ly);
	$pdf->textColor($autocolor);
	$pdf->setfont('arial','B',10);
	$pdf->cell($col1+$col2,5,$template->translate("/devis/technologie","F"),0,0,'L',0);
	$pdf->setfont('arial','',8);
	$pdf->textColor("black");
	$ly+=5;

	if($article['production_froid']!=""){
	    $pdf->setXY($ptablex,$ly);
	    $txt=$template->translate($article['production_froid']);
	    $hcol=$pdf->nblines($col2,$txt)*$lheight;
	    $pdf->cell($col1,$hcol,$template->translate("/devis/production_froid","F"),0);
	    $pdf->multicell($col2,$lheight,$txt,0,'',0);
	    $ly+=$hcol;
	}
	if($article['production_chaud']!=""){
	    $pdf->setXY($ptablex,$ly);
	    $txt=$template->translate($article['production_chaud']);
	    $hcol=$pdf->nblines($col2,$txt)*$lheight;
	    $pdf->cell($col1,$hcol,$template->translate("/devis/production_chaud","F"),0);
	    $pdf->multicell($col2,$lheight,$txt,0,'',0);
	    $ly+=$hcol;
	}
	if($article['fonction_hiver_techno']!=""){
	    $pdf->setXY($ptablex,$ly);
	    $txt=$template->translate($article['fonction_hiver_techno']);
	    $hcol=$pdf->nblines($col2,$txt)*$lheight;
	    $pdf->cell($col1,$hcol,$template->translate("/devis/fonction_hiver","F"),0);
	    $pdf->multicell($col2,$lheight,$txt,0,'',0);
	    $ly+=$hcol;
	}
	if($article['gaz']!=""){
		$pdf->setXY($ptablex,$ly);
		$txt=$template->translate($article['gaz']);
		$hcol=$pdf->nblines($col2,$txt)*$lheight;
		$pdf->cell($col1,$hcol,$template->translate("/devis/type_gaz","F"),0);
		$pdf->multicell($col2,$lheight,$txt,0,'',0);
		$ly+=$hcol;
	    }
	$ly+=5;
}

if($template->pro)
    $differenceprix = "differenceprixht";
else 
    $differenceprix = "differenceprix";

//include 'devisoptions.php';

?>
