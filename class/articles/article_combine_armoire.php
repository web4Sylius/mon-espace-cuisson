<?php

include_once($rootpath.'class/articles/article_cave.php');
include_once($rootpath.'class/articles/article_armoire_pos.php');
include_once($rootpath.'class/articles/article_armoire_neg.php');
include_once($rootpath.'class/articles/article_frigomcav.php');
include_once($rootpath.'class/articles/article_congelmcav.php');

function article_combine_armoire_fields() {
	return array(
    	array(
    	'criteres_generaux'		=> array(
			  'type_pose'			=> array(CHAMP_MULTI, 'Installation(s) possible(s)','',false,true),
			  'agencement'		=> array(CHAMP_IMG, 'Composition du combiné', '',false,true),
			  'nb_zone_temp'	=> array(CHAMP_TRAD, 'Nombre de compartiments','',false,true),
			),
			'capacite'	=> array(
			  'volume'					=> array(CHAMP_NUMERIC, 'Volume total combiné', 'L',false,true),
			  'capacite_armoire_pos' 	=> array(CHAMP_NUMERIC, 'Volume utile armoire positive','L'),
			  'capacite_armoire_neg' 	=> array(CHAMP_NUMERIC, 'Capacité armoire négative','L'),
			  'capacite_frigo' 			=> array(CHAMP_NUMERIC, 'Capacité réfrigérateur','L'),
			  'capacite_congel' 		=> array(CHAMP_NUMERIC, 'Capacité congélateur','L'),
			  'capacite_vin' 			=> array(CHAMP_NUMERIC, 'Capacité totale Vins','bouteilles Bordelaises 0,75L'),
			  'capacite_clayettes' 		=> array(CHAMP_NUMERIC, 'Capacité avec clayettes coulissantes','bouteilles Bordelaises 0,75L'),
			  'capacite_max' 			=> array(CHAMP_NUMERIC, 'Capacité maximale','bouteilles Bordelaises 0,75L'),
			),

			'Design'		=> array(
				'type_porte'					=> array(CHAMP_TRAD, 'Nombre et type de portes','',false,true),
				'coloris'							=> array(CHAMP_TRAD, 'Coloris principal','',false,true),
				'habillage_ext'				=> array(CHAMP_TRAD, 'Habillage extérieur','',false,true),
				'niveau_gamme'				=> array(CHAMP_MULTI, 'Niveau de gamme','',false,true),
				'serrure'							=> array(CHAMP_TRAD, 'Serrure', '',false,true),
			  'serrure_recherche'		=> array(CHAMP_TRAD, 'Serrure (affinage recherche, non visible)', '',false,true),	  	  
			),
			'dimensions' => array(
			    'dimensions_hlp'			=> array(CHAMP_TXT,'Dimensions nettes du combiné', 'HxLxP',false),
			    'hauteur' 						=> array(CHAMP_NUMERIC,'Dimensions Nettes combiné – Hauteur', 'mm'),
			    'largeur'				 			=> array(CHAMP_NUMERIC,'Dimensions Nettes combiné – Largeur', 'mm'),
			    'profondeur' 					=> array(CHAMP_NUMERIC,'Dimensions Nettes combiné – Profondeur', 'mm'),
			    'poids' 							=> array(CHAMP_NUMERIC,'Dimensions Nettes combiné – Poids', 'kg'),
			    'dimensions_niche'		=> array(CHAMP_TRAD, 'Dimensions de la niche d’encastrement','HxLxP mm',false,true),
			    'niche_hauteur'				=> array(CHAMP_NUMERIC, 'Hauteur minimum de la niche d\'encastrement', 'mm'),
				  'niche_largeur'				=> array(CHAMP_NUMERIC, 'Largeur minimum de la niche d\'encastrement', 'mm'),
				  'niche_profondeur'		=> array(CHAMP_NUMERIC, 'Profondeur minimum de la niche d\'encastrement', 'mm'),
				  'infos_encastrement'	=> array(CHAMP_TRAD, 'Infos pour encastrement','',false,true),
			),						
			'technique_performances'		=> array(
				'class_energetique' 	=> array(CHAMP_LISTE_EN,'Classe énergetique', '', false),
				'iee' 					=> array(CHAMP_NUMERIC,'IEE', '', false),
				'conso_an'				=> array(CHAMP_NUMERIC, 'Consommation en Kwh/an', 'Kwh'),
			 	//'conso_kw'				=> array(CHAMP_NUMERIC, 'Consommation en Kwh/24h', 'Kwh',false,true),
			  'conso_euro'				=> array(CHAMP_NUMERIC, 'Consommation en €/an (0.17 € kW/h)', 'EUR',false,true),
			  'niveau_bruit'			=> array(CHAMP_NUMERIC, 'Niveau sonore', 'dB',false,true),
			  'niveau_bruit_abcd'		=> array(CHAMP_TRAD, 'Niveau sonore (ABCD)', 'W',false,true),
			  'fonction_hiver'			=> array(CHAMP_TRAD, 'Fonction Hiver', '',false,true),
			  //'temp_piece'				=> array(CHAMP_TXT, 'Température de la pièce d\'installation mini/maxi', '°C'),
			  'temp_instal_max'			=> array(CHAMP_LISTE_TEMP, 'Température d\'installation mini (affinage recherche)'),
			  'temp_instal_min'			=> array(CHAMP_LISTE_TEMP, 'Température d\'installation maxi (affinage recherche)'),
		    'alim_elec' 					=> array(CHAMP_TRAD,'Alimentation électrique', '',false),
			),
			'service' => array(
		      'dispo_pieces' => array(CHAMP_NUMERIC,'Disponibilité des pièces détachées', 'ans'),      
		    ),
      
    	));
  }

function article_combine_armoire_show($tab,$title) {
    article_global_show('combine_armoire',$title,true);

    echo '<h2>Armoire positive / Armoire négative / réfrigérateur / congélateur / cave à vin</h2>';
    echo '<input type="button" style="width:200px;margin:5px;" value="Ajouter une armoire positive" onclick="itemaction(\''.$tab.'\',\'armoire_pos_add\')">';
    echo '<input type="button" style="width:200px;margin:5px;" value="Ajouter une armoire négative" onclick="itemaction(\''.$tab.'\',\'armoire_neg_add\')"><br>';
    echo '<input type="button" style="width:200px;margin:5px;" value="Ajouter un réfrigérateur" onclick="itemaction(\''.$tab.'\',\'frigomcav_add\')">';
    echo '<input type="button" style="width:200px;margin:5px;" value="Ajouter un congélateur" onclick="itemaction(\''.$tab.'\',\'congelmcav_add\')"><br>';
    echo '<input type="button" style="width:200px;margin:5px;" value="Ajouter une cave à vin" onclick="itemaction(\''.$tab.'\',\'cave_add\')">';
    echo '<input type="hidden"  name="item_action" />';
    echo '<input type="hidden" name="item_id" /><br>';
}


function article_combine_armoire_update($tab,$title) {
    if($_POST['item_action']=='armoire_pos_add')	article_armoire_pos_add();
    if($_POST['item_action']=='armoire_neg_add')	article_armoire_neg_add();
    if($_POST['item_action']=='frigomcav_add')	article_frigomcav_add();
    if($_POST['item_action']=='congelmcav_add')	article_congelmcav_add();
    if($_POST['item_action']=='cave_add')	article_cave_add();

    article_global_update('combine_armoire',$title,true);
}
?>