<?php

include_once($rootpath.'class/articles/article_cave.php');
include_once($rootpath.'class/articles/article_charcuterie.php');
include_once($rootpath.'class/articles/article_fromage.php');
include_once($rootpath.'class/articles/article_cigare.php');
include_once($rootpath.'class/articles/article_chocolat.php');
include_once($rootpath.'class/articles/article_maturation.php');


function article_combine_cave_fields() {
	return array(
    	array(
    	'criteres_generaux'		=> array(
			  'type_pose'		=> array(CHAMP_MULTI, 'Installation(s) possible(s)','',false,true),
			  'agencement'		=> array(CHAMP_IMG, 'Composition du combiné', '',false,true),
			  'nb_zone_temp'	=> array(CHAMP_TRAD, 'Nombre de zones de température','',false,true),
			),
			'capacite'	=> array(
			  'volume'				=> array(CHAMP_NUMERIC, 'Volume utile', 'L',false,true),
			  'capacite_vin' 		=> array(CHAMP_NUMERIC, 'Capacité totale Vins','bouteilles Bordelaises 0,75L'),
			  'capacite_clayettes' 	=> array(CHAMP_NUMERIC, 'Capacité avec clayettes coulissantes','bouteilles Bordelaises 0,75L'),
			  'capacite_max' 		=> array(CHAMP_NUMERIC, 'Capacité maximale','bouteilles Bordelaises 0,75L'),
			  'capacite_charcuterie'=> array(CHAMP_NUMERIC, 'Capacité totale Charcuterie','L/kg'),
			  'capacite_fromage' 	=> array(CHAMP_NUMERIC, 'Capacité totale Fromages','L/kg'),
			  'capacite_cigare' 	=> array(CHAMP_NUMERIC, 'Capacité totale Cigares','L/cigares'),
			  'capacite_chocolat' 	=> array(CHAMP_NUMERIC, 'Capacité totale Chocolats','L/Kg'),
			  'capacite_viande' 	=> array(CHAMP_NUMERIC, 'Capacité totale Viande','L/kg'),
			),

			'Design'		=> array(
				'type_porte'		=> array(CHAMP_TRAD, 'Nombre et type de portes','',false,true),
				'coloris'			=> array(CHAMP_TRAD, 'Coloris principal','',false,true),
				'habillage_ext'		=> array(CHAMP_TRAD, 'Habillage extérieur','',false,true),
				'niveau_gamme'		=> array(CHAMP_MULTI, 'Niveau de gamme','',false,true),
				'serrure'			=> array(CHAMP_TRAD, 'Serrure', '',false,true),
			  'serrure_recherche'	=> array(CHAMP_TRAD, 'Serrure (affinage recherche, non visible)', '',false,true),
			  'eclairage'			=> array(CHAMP_TRAD, 'Éclairage intérieur','',false,true),
				'coloris_eclairage'	=> array(CHAMP_TRAD, 'Coloris de l\'éclairage','',false,true),
			  'position_eclairage'	=> array(CHAMP_TRAD, 'Position de l\'éclairage','',false,true),			  	  
			),
			'dimensions' => array(
			    'dimensions_hlp'		=> array(CHAMP_TXT,'Dimensions nettes du combiné', 'HxLxP',false),
			    'hauteur' 				=> array(CHAMP_NUMERIC,'Dimensions Nettes combiné – Hauteur', 'mm'),
			    'largeur'				=> array(CHAMP_NUMERIC,'Dimensions Nettes combiné – Largeur', 'mm'),
			    'profondeur' 			=> array(CHAMP_NUMERIC,'Dimensions Nettes combiné – Profondeur', 'mm'),
			    'poids' 				=> array(CHAMP_NUMERIC,'Dimensions Nettes combiné – Poids', 'kg'),
			    'dimensions_niche'		=> array(CHAMP_TRAD, 'Dimensions de la niche d’encastrement','HxLxP mm',false,true),
			    'niche_hauteur'			=> array(CHAMP_NUMERIC, 'Hauteur minimum de la niche d\'encastrement', 'mm'),
				'niche_largeur'			=> array(CHAMP_NUMERIC, 'Largeur minimum de la niche d\'encastrement', 'mm'),
				'niche_profondeur'		=> array(CHAMP_NUMERIC, 'Profondeur minimum de la niche d\'encastrement', 'mm'),
				'infos_encastrement'	=> array(CHAMP_TRAD, 'Infos pour encastrement','',false,true),
			),						
			'technique_performances'	=> array(
				'class_energetique' => array(CHAMP_LISTE_EN,'Classe énergetique', '', false),
				'iee' 				=> array(CHAMP_TRAD,'IEE', '', false),
				'conso_an'			=> array(CHAMP_NUMERIC, 'Consommation en Kwh/an', 'Kwh'),
			 	//'conso_kw'			=> array(CHAMP_NUMERIC, 'Consommation en Kwh/24h', 'Kwh',false,true),
			  	'conso_euro'		=> array(CHAMP_NUMERIC, 'Consommation en €/an (0.17 € kW/h)', 'EUR',false,true),
			  	'niveau_bruit'		=> array(CHAMP_NUMERIC, 'Niveau de bruit', 'dBA',false,true),
			  	'niveau_bruit_abcd'	=> array(CHAMP_TRAD, 'Niveau sonore (ABCD)', 'W',false,true),
			  	'fonction_hiver'	=> array(CHAMP_TRAD, 'Fonction Hiver', '',false,true),
			  	//'temp_piece'		=> array(CHAMP_NUMERIC, 'Température de la pièce d\'installation mini/maxi', 'm³/h'),
			  	'temp_instal_max'	=> array(CHAMP_NUMERIC, 'Température d\'installation de la cave mini (affinage recherche, non visible)'),
			  	'temp_instal_min'	=> array(CHAMP_NUMERIC, 'Température d\'installation de la cave maxi (affinage recherche, non visible)'),
		    	'alim_elec' 		=> array(CHAMP_TRAD,'Alimentation électrique', '',false),
			),
			'service' => array(
		      'dispo_pieces' => array(CHAMP_NUMERIC,'Disponibilité des pièces détachées', 'ans'),      
		    ),
      
    	));
  }

function article_combine_cave_show($tab,$title) {
    article_global_show('combine_cave',$title,true);

    echo '<h2>Charcuterie / Chocolats / Fromage / Maturation / Cigares / cave à vin</h2>';
    echo '<input type="button" style="width:200px;margin:5px;" value="Ajouter une cave à Charcuterie" onclick="itemaction(\''.$tab.'\',\'charcuterie_add\')">';
    echo '<input type="button" style="width:200px;margin:5px;" value="Ajouter une cave à chocolat" onclick="itemaction(\''.$tab.'\',\'chocolat_add\')"><br>';
    echo '<input type="button" style="width:200px;margin:5px;" value="Ajouter une cave à fromage" onclick="itemaction(\''.$tab.'\',\'fromage_add\')">';
    echo '<input type="button" style="width:200px;margin:5px;" value="Ajouter une cave de Maturation" onclick="itemaction(\''.$tab.'\',\'maturation_add\')"><br>';
    echo '<input type="button" style="width:200px;margin:5px;" value="Ajouter une cave à cigares" onclick="itemaction(\''.$tab.'\',\'cigare_combine_add\')">';
    echo '<input type="button" style="width:200px;margin:5px;" value="Ajouter une cave à vin" onclick="itemaction(\''.$tab.'\',\'cave_combine_add\')">';
    echo '<input type="hidden"  name="item_action" />';
    echo '<input type="hidden" name="item_id" /><br>';
}


function article_combine_cave_update($tab,$title) {
    if($_POST['item_action']=='fromage_add')	article_fromage_add();
    if($_POST['item_action']=='chocolat_add')	article_chocolat_add();
    if($_POST['item_action']=='charcuterie_add')	article_charcuterie_add();
    if($_POST['item_action']=='maturation_add')	article_maturation_add();
    if($_POST['item_action']=='cigare_add')	article_cigare_add();
    if($_POST['item_action']=='cave_add')	article_cave_add();
    article_global_update('combine_cave',$title,true);
}
?>