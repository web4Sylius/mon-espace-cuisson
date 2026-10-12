<?php

include_once($rootpath.'class/articles/article_cave_combine.php');
include_once($rootpath.'class/articles/article_armoire_pos.php');
include_once($rootpath.'class/articles/article_armoire_neg.php');
include_once($rootpath.'class/articles/article_frigomcav.php');
include_once($rootpath.'class/articles/article_congelmcav.php');
include_once($rootpath.'class/articles/article_charcuterie.php');
include_once($rootpath.'class/articles/article_fromage.php');
include_once($rootpath.'class/articles/article_cigare_combine.php');
include_once($rootpath.'class/articles/article_chocolat.php');
include_once($rootpath.'class/articles/article_maturation.php');


function article_combine_fields() {
	return array(
    	array(
    	'criteres_generaux'		=> array(
    		'agencement'		=> array(CHAMP_IMG, 'Composition du combiné', '',false,true),
    		'nb_zone_temp'	=> array(CHAMP_TRAD, 'Nombre de compartiments','',false,true),	  
			),
			'capacite'	=> array(
			  'volume'					=> array(CHAMP_NUMERIC, 'Volume utile total', 'L',false,true),
			  'capacite_unite' 			=> array(CHAMP_TRAD, 'Unité capacité',''),
			  'capacite_max' 			=> array(CHAMP_NUMERIC, 'Capacité totale',''),
			),

			'Design'		=> array(
				'coloris'			=> array(CHAMP_TRAD, 'Coloris principal','',false,true),
				'habillage_ext'		=> array(CHAMP_TRAD, 'Habillage extérieur','',false,true),
				'niveau_gamme'		=> array(CHAMP_MULTI, 'Niveau de gamme','',false,true),  	  
			),
			'dimensions' => array(
			    //'dimensions_hlp'	=> array(CHAMP_TXT,'Dimensions nettes', 'HxLxP mm',false),
			    'hauteur' 			=> array(CHAMP_NUMERIC,'Hauteur nette', 'mm'),
			    'largeur'			=> array(CHAMP_NUMERIC,'Largeur nette', 'mm'),
			    'profondeur' 		=> array(CHAMP_NUMERIC,'Profondeur nette', 'mm'),
			    'poids' 			=> array(CHAMP_NUMERIC,'Poids net', 'g'),
			    'ouverture_porte'	=> array(CHAMP_TRAD, 'Ouverture de la porte','',false,true),
			    'porte_reversible'	=> array(CHAMP_MULTI, 'Porte réversible', ''),
				'largeur_90'		=> array(CHAMP_NUMERIC, 'Largeur totale porte ouverte à 90°', 'mm'),
				'profondeur_butee'	=> array(CHAMP_NUMERIC, 'Profondeur totale avec butée arrière', 'mm'),
				'profondeur_porte_ouverte'	=> array(CHAMP_NUMERIC, 'Profondeur totale porte ouverte (mm)','',false,true),
				'pieds_reglables'	=> array(CHAMP_TRAD, 'Pieds réglables','',false,true),
				'roulettes'			=> array(CHAMP_TRAD, 'Roulettes','',false,true),
			),
			'encastrement' => array(
				'type_pose'			=> array(CHAMP_MULTI, 'Installation(s) possible(s)','',false,true),
			    //'dimensions_niche'	=> array(CHAMP_TXT, 'Dimensions de la niche d’encastrement','HxLxP mm',false,true),
			    'niche_hauteur'		=> array(CHAMP_NUMERIC, 'Hauteur minimum de la niche d\'encastrement', 'mm'),
				'niche_largeur'		=> array(CHAMP_NUMERIC, 'Largeur minimum de la niche d\'encastrement', 'mm'),
				'niche_profondeur'	=> array(CHAMP_NUMERIC, 'Profondeur minimum de la niche d\'encastrement', 'mm'),
				'infos_encastrement'=> array(CHAMP_TRAD, 'Infos pour encastrement','',false,true),			    
			),	
			'emplacement' => array(
				'fonction_hiver'	=> array(CHAMP_TRAD, 'Fonction Hiver','',false,true),
			    'classe_climatique'	=> array(CHAMP_TXT, 'Classe climatique','',false,true),
			    //'temp_piece'		=> array(CHAMP_TXT, 'Température de la pièce d\'installation mini/maxi', '°C'),
			 	'temp_instal_max'	=> array(CHAMP_LISTE_TEMP, 'Température d\'installation mini','°C'),
			 	'temp_instal_min'	=> array(CHAMP_LISTE_TEMP, 'Température d\'installation maxi','°C'),		    
			),					
			'technique_performances'		=> array(
				'class_energetique' 	=> array(CHAMP_LISTE_EN,'Classe énergetique', '', false),
				'iee' 					=> array(CHAMP_NUMERIC,'IEE', '', false),
				'conso_an'				=> array(CHAMP_NUMERIC, 'Consommation annuelle', 'Kwh/an'),
			 	//'conso_kw'				=> array(CHAMP_NUMERIC, 'Consommation journalière', 'Kwh/24h',false,true),
				'conso_euro'			=> array(CHAMP_NUMERIC, 'Coût de fonctionnement annuel (0.17 € kW/h)', '',false,true),
				'niveau_bruit'			=> array(CHAMP_NUMERIC, 'Niveau sonore', 'dB',false,true),
				'niveau_bruit_abcd'		=> array(CHAMP_TRAD, 'Niveau sonore (ABCD)', '',false,true),
				'intensite_max'			=> array(CHAMP_NUMERIC, 'Intensité max', 'A',false,true),
				'degagement_calorifique'=> array(CHAMP_NUMERIC, 'Dégagement calorifique (W/h)', ''),
				'isolation'				=> array(CHAMP_TRAD, 'Isolation'),
				'gaz_froid'				=> array(CHAMP_TRAD, 'Fluide réfrigérant'),
		    	'alim_elec' 			=> array(CHAMP_TRAD,'Alimentation électrique', '',false),
		    	'fiche_energie' 		=> array(CHAMP_PDF,'Étiquette énergie', '',false),
			),
			'service' => array(
		      'dispo_pieces' => array(CHAMP_NUMERIC,'Disponibilité des pièces détachées', 'ans'),      
		    ),
      
    	));
  }

function article_combine_show($tab,$title) {
    article_global_show('combine',$title,true);

    echo '<h2>Armoire positive / Armoire négative / réfrigérateur / congélateur / cave à vin</h2>';
    echo '<input type="button" style="width:200px;margin:5px;" value="Ajouter une cave à vin" onclick="itemaction(\''.$tab.'\',\'cave_combine_add\')">';
    echo '<input type="button" style="width:200px;margin:5px;" value="Ajouter une cave à Charcuterie" onclick="itemaction(\''.$tab.'\',\'charcuterie_add\')">';
    echo '<input type="button" style="width:200px;margin:5px;" value="Ajouter une cave à fromage" onclick="itemaction(\''.$tab.'\',\'fromage_add\')">';
    echo '<input type="button" style="width:200px;margin:5px;" value="Ajouter une cave à cigares" onclick="itemaction(\''.$tab.'\',\'cigare_combine_add\')"><br>';
    echo '<input type="button" style="width:200px;margin:5px;" value="Ajouter une armoire positive" onclick="itemaction(\''.$tab.'\',\'armoire_pos_add\')">';
    echo '<input type="button" style="width:200px;margin:5px;" value="Ajouter une armoire négative" onclick="itemaction(\''.$tab.'\',\'armoire_neg_add\')">';
    echo '<input type="button" style="width:200px;margin:5px;" value="Ajouter un réfrigérateur" onclick="itemaction(\''.$tab.'\',\'frigomcav_add\')">';
    echo '<input type="button" style="width:200px;margin:5px;" value="Ajouter un congélateur" onclick="itemaction(\''.$tab.'\',\'congelmcav_add\')"><br>';
    echo '<input type="hidden"  name="item_action" />';
    echo '<input type="hidden" name="item_id" /><br>';
}


function article_combine_update($tab,$title) {
    if($_POST['item_action']=='armoire_pos_add')	article_armoire_pos_add();
    if($_POST['item_action']=='armoire_neg_add')	article_armoire_neg_add();
    if($_POST['item_action']=='frigomcav_add')		article_frigomcav_add();
    if($_POST['item_action']=='congelmcav_add')		article_congelmcav_add();
    if($_POST['item_action']=='fromage_add')		article_fromage_add();
    if($_POST['item_action']=='chocolat_add')		article_chocolat_add();
    if($_POST['item_action']=='charcuterie_add')	article_charcuterie_add();
    if($_POST['item_action']=='maturation_add')		article_maturation_add();
    if($_POST['item_action']=='cigare_combine_add')	article_cigare_combine_add();
    if($_POST['item_action']=='cave_combine_add')	article_cave_combine_add();

    article_global_update('combine',$title,true);
}
?>