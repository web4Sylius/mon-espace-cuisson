<?php
//armoire refrigerateur congelateur
function article_arc_fields() {
	return array(
    	array(
    	'criteres_generaux'	=> array(
			  'type_produit'					=> array(CHAMP_TRAD, 'Type de produit', '',false,true),
			),	
			'capacite'	=> array(
			  'volume'					=> array(CHAMP_NUMERIC, 'Volume utile', 'L',false,true),
			  'volume_brut'			=> array(CHAMP_NUMERIC, 'Volume brut', 'L',false,true),
			  'capacite'				=> array(CHAMP_NUMERIC, 'Capacité', 'Bac GN',false,true),
			  //'dimensions_expo'	=> array(CHAMP_TXT,'Dimensions d’exposition', 'LxP mm',false),
			  'largeur_expo' 		=> array(CHAMP_NUMERIC,'Largeur d\'exposition', 'mm'),
			  'profondeur_expo' => array(CHAMP_NUMERIC,'Profondeur d\'exposition', 'mm'),
			  'capacite_frigo'	=> array(CHAMP_NUMERIC, 'Capacité de réfrigération', 'kg',false,true),
			  'capacite_congel'	=> array(CHAMP_NUMERIC, 'Capacité de surgélation', 'kg',false,true),
			),

			'Design'		=> array(
				'coloris'							=> array(CHAMP_TRAD, 'Coloris principal',''),
				'type_porte'					=> array(CHAMP_TRAD, 'Type d\'ouverture','',false,true),
				'coloris_cadre'				=> array(CHAMP_TRAD, 'Coloris du cadre de la porte','',false,true),
				'caract_vitrage'		=> array(CHAMP_TRAD, 'Caractéristique vitrage','',false,true),
				'niveau_gamme'				=> array(CHAMP_MULTI, 'Niveau de gamme','',false,true),
				'habillage_ext'				=> array(CHAMP_TRAD, 'Habillage extérieur','',false,true),
				'coloris_int'					=> array(CHAMP_TRAD, 'Revêtement cuve/coloris intérieur','',false,true),
				'eclairage'						=> array(CHAMP_TRAD, 'Éclairage intérieur','',false,true),
				'coloris_eclairage'		=> array(CHAMP_TRAD, 'Coloris de l\'éclairage','',false,true),
			  'position_eclairage'	=> array(CHAMP_TRAD, 'Position de l\'éclairage','',false,true),
			),
			'equipements'		=> array(
				//'accessoires_inclus'			=> array(CHAMP_TRAD, 'Accessoires inclus', '',false,true),
				'dimensions_etagere'			=> array(CHAMP_TXT, 'Dimensions étagère intermédiaire', 'mm',false,true),
			  'largeur_etagere'					=> array(CHAMP_NUMERIC, 'Largeur étagère intermédiaire', 'mm',false,true),
			  'profondeur_etagere'			=> array(CHAMP_NUMERIC, 'Profondeur étagère intermédiaire', 'mm',false,true),
				//'dimensions_clayettes'		=> array(CHAMP_TXT, 'Dimensions des clayettes/grilles', 'LxP mm',false,true),
			  'largeur_clayettes'				=> array(CHAMP_NUMERIC, 'Largeur clayettes/grilles', 'mm',false,true),
			  'profondeur_clayettes'		=> array(CHAMP_NUMERIC, 'Profondeur clayettes/grilles', 'mm',false,true),
			  'nb_clayettes'						=> array(CHAMP_NUMERIC, 'Nombre de clayettes maximum', '',false,true),
			  'reglage_clayettes'				=> array(CHAMP_NUMERIC, 'Hauteur de réglage des clayettes', 'mm',false,true),
			  'chargement_max_clayettes'=> array(CHAMP_NUMERIC, 'Charge maximum par clayette', 'kg',false,true),
			  'serrure'									=> array(CHAMP_TRAD, 'Serrure', '',false,true),
			  'serrure_recherche'				=> array(CHAMP_MULTI, 'Serrure (affinage recherche, non visible)', '',false,true),
			  'alarme'									=> array(CHAMP_TRAD, 'Alarme(s)', '',false,true),
			  'verrouillage_cmd'				=> array(CHAMP_MULTI, 'Verrouillage des commandes', '',false,true),
			  'type_poignee'						=> array(CHAMP_TRAD, 'Type de poignée', '',false,true),
			  'roulettes'								=> array(CHAMP_TRAD, 'Roulettes', '',false,true),
			  'pieds_reglables'					=> array(CHAMP_TRAD, 'Pieds réglables', '',false,true),
			  'equipement_specifique'		=> array(CHAMP_TRAD, 'Equipement(s) spécifique(s)',''),
			  'equipement_specifique1'		=> array(CHAMP_TRAD, 'Equipement(s) spécifique(s)',''),
			  'equipement_specifique2'		=> array(CHAMP_TRAD, 'Equipement(s) spécifique(s)',''),
			  'equipement_specifique3'		=> array(CHAMP_TRAD, 'Equipement(s) spécifique(s)',''),

			),
	
			'dimensions' => array(
			    //'dimensions_hlp'						=> array(CHAMP_TXT,'Dimensions', 'HxLxP mm',false),
			    'hauteur' 									=> array(CHAMP_NUMERIC,'Hauteur nette', 'mm'),
			    'largeur'				 						=> array(CHAMP_NUMERIC,'Largeur nette', 'mm'),
			    'profondeur' 								=> array(CHAMP_NUMERIC,'Profondeur nette', 'mm'),
			    'poids' 										=> array(CHAMP_NUMERIC,'Poids net', 'kg'),
			    'niche_hauteur'				=> array(CHAMP_NUMERIC, 'Hauteur minimum de la niche d\'encastrement', 'mm'),
			  	'niche_largeur'				=> array(CHAMP_NUMERIC, 'Largeur minimum de la niche d\'encastrement', 'mm'),
			  	'niche_profondeur'		=> array(CHAMP_NUMERIC, 'Profondeur minimum de la niche d\'encastrement', 'mm'),
			  	//'niche_dimensions'		=> array(CHAMP_TXT, 'Dimensions de la niche d\'encastrement', 'HxLxP mm'),
			  	'infos_encastrement'	=> array(CHAMP_TRAD, 'Infos pour encastrement','',false,true),
			  	'sensporte'						=> array(CHAMP_TRAD, 'Sens d\'ouverture de la porte','',false,true),
			    'largeur_90' 								=> array(CHAMP_NUMERIC,'Largeur totale porte ouverte à 90°', 'mm'),
			    'porte_reversible'						=> array(CHAMP_MULTI, 'Porte réversible', '',false,true),
			    'profondeur_porte_ouverte' 	=> array(CHAMP_NUMERIC,'Profondeur totale porte ouverte', 'mm'),
			    //'dimensions_cuve'				=> array(CHAMP_TXT,'Dimensions de la cuve', 'HxLxP mm',false),
			    'hauteur_int' 							=> array(CHAMP_NUMERIC,'Hauteur intérieure cuve', 'mm'),
			    'largeur_int'				 				=> array(CHAMP_NUMERIC,'Largeur intérieure cuve', 'mm'),
			    'profondeur_int' 						=> array(CHAMP_NUMERIC,'Profondeur intérieure cuve', 'mm'),			    
			),
			'technique'		=> array(
				'type_froid_cave'					=> array(CHAMP_TRAD, 'Type de froid','',false,true),
				'reglage_temp'						=> array(CHAMP_TRAD, 'Plage de réglage de la température','',false,true),
				'regulation_thermostat'		=> array(CHAMP_TRAD, 'Régulation','',false,true),
				'fonction_hiver'					=> array(CHAMP_TRAD, 'Fonction Hiver','',false,true),
				'degivrage_auto'					=> array(CHAMP_TRAD, 'Dégivrage automatique','',false,true), 
				'affichage'								=> array(CHAMP_TRAD, 'Affichage','',false,true),
			  'hygrometre'							=> array(CHAMP_TRAD, 'Hygrométrie',''),
			  'evaporateur'							=> array(CHAMP_TRAD, 'Evaporateur','',false,true),
			  'condenseur'							=> array(CHAMP_TRAD, 'Condenseur',''),
			  'isolation'								=> array(CHAMP_TRAD, 'Isolation','',false,true),
			  'technologie_specifique'	=> array(CHAMP_TRAD, 'Technologie(s) spécifique(s)',''),
			  'technologie_specifique1'	=> array(CHAMP_TRAD, 'Technologie(s) spécifique(s)',''),
			  'technologie_specifique2'	=> array(CHAMP_TRAD, 'Technologie(s) spécifique(s)',''),
			  'technologie_specifique3'	=> array(CHAMP_TRAD, 'Technologie(s) spécifique(s)',''),
			),
			'technique_performances'	=> array(
				'classe_climatique' 		=> array(CHAMP_TRAD,'Classe climatique', '', false),
				'puissance_totale' 			=> array(CHAMP_NUMERIC,'Puissance absorbée', 'W'),
		    'intensite_electrique' 	=> array(CHAMP_NUMERIC,'Intensité absorbée', 'A',false),
		    'class_energetique' 		=> array(CHAMP_LISTE_EN,'Classe énergetique', '', false),
		    'conso_an'							=> array(CHAMP_NUMERIC, 'Consommation annuelle', 'Kwh'),
			 	//'conso_kw'							=> array(CHAMP_NUMERIC, 'Consommation journalière', 'Kwh',false,true),
			  'conso_euro'						=> array(CHAMP_NUMERIC, 'Coût de fonctionnement annuel (0.17 € kW/h)', '',false,true),
			  'niveau_bruit'			=> array(CHAMP_NUMERIC, 'Niveau sonore', 'dB',false,true),
			  'degagement_calorifique'	=> array(CHAMP_NUMERIC,'Dégagement calorifique', 'W/h', false),
				'iee' 									=> array(CHAMP_NUMERIC,'IEE', '', false),
			  //'temp_piece'					=> array(CHAMP_NUMERIC, 'Température de la pièce d\'installation mini/maxi', '°C'),
			  'temp_instal_max'			=> array(CHAMP_LISTE_TEMP, 'Température d\'installation mini (affinage recherche, non visible)'),
			  'temp_instal_min'			=> array(CHAMP_LISTE_TEMP, 'Température d\'installation maxi (affinage recherche, non visible)'),
		    'gaz_froid' 						=> array(CHAMP_TRAD,'Fluide réfrigérant', '', false),
		    'alim_elec' 						=> array(CHAMP_TRAD,'Alimentation électrique', '',false),
		    'fiche_energie' 				=> array(CHAMP_PDF,'Étiquette énergie', '',false),
			),	 
      
    	));
  }

  function article_arc_show($tab, $title) {
    article_global_show($tab, $title, true); // true = champs groupés
  }
  function article_arc_update($tab, $title) {
    article_global_update($tab, $title, true); // true = champs groupés
  }
?>