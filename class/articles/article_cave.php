<?php
function article_cave_fields() {
	return array(
    	array(
			'capacite'	=> array(
			  'capacite'				=> array(CHAMP_NUMERIC, 'Capacité avec équipement standard','bouteilles Bordeaux 0,75L'),
			  'capacite_coulissant'		=> array(CHAMP_NUMERIC, 'Capacité avec équipement coulissant','bouteilles Bordeaux 0,75L'),
			  'capacite_max'			=> array(CHAMP_NUMERIC, 'Capacité maximale','bouteilles 0.75L'),
			  'diametre_max_bouteille'	=> array(CHAMP_NUMERIC, 'Diamètre maximum des bouteilles','mm',false,true),
			  'longeur_max_bouteille'	=> array(CHAMP_NUMERIC, 'Longueur maximum des bouteilles','mm'),
			  'nb_bouteilles_vis'		=> array(CHAMP_NUMERIC, 'Nombre de bouteilles avec etiq. visible','bouteilles bordeaux 0.75L'),
			),

			'Design'		=> array(
			  'niveau_gamme'		=> array(CHAMP_MULTI, 'Niveau de gamme','',false,true),
			  'coloris'				=> array(CHAMP_TRAD, 'Coloris principal',''),
			  'type_porte'			=> array(CHAMP_TRAD, 'Type de porte','',false,true),
			  'coloris_porte'		=> array(CHAMP_TRAD, 'Coloris de la porte','',false,true),
			  'coloris_cadre'		=> array(CHAMP_TRAD, 'Coloris du cadre de la porte','',false,true),
			  'caract_vitrage'		=> array(CHAMP_TRAD, 'Caractéristique vitrage','',false,true),
			  'antiuv'				=> array(CHAMP_MULTI, 'Traitement anti-UV','',false,true),
			  'coloris_vitrage'		=> array(CHAMP_TRAD, 'Coloris vitrage','',false,true),
			  'porte_reversible'	=> array(CHAMP_MULTI, 'Porte réversible','',false,true),
			  'ouverture_porte'		=> array(CHAMP_TRAD, 'Ouverture de la porte','',false,true),
			  'poignee'				=> array(CHAMP_TRAD, 'Poignée/Ouverture','',false,true),
			  'eclairage'			=> array(CHAMP_TRAD, 'Éclairage intérieur','',false,true),
			  'coloris_eclairage'	=> array(CHAMP_TRAD, 'Coloris de l\'éclairage','',false,true),
			  'position_eclairage'	=> array(CHAMP_TRAD, 'Position de l\'éclairage','',false,true),
			  'habillage_ext'		=> array(CHAMP_TRAD, 'Habillage extérieur','',false,true),
			  'revetement_int'		=> array(CHAMP_TRAD, 'Revêtement intérieur','',false,true),
			  'coloris_int'			=> array(CHAMP_TRAD, 'Coloris intérieur','',false,true),	  
			),
			'equipements'		=> array(
			  'fonction_hiver'		=> array(CHAMP_TRAD, 'Fonction Hiver', '',false,true),
			  'serrure'				=> array(CHAMP_TRAD, 'Serrure', '',false,true),
			  'serrure_recherche'	=> array(CHAMP_MULTI, 'Serrure (affinage recherche, non visible)', '',false,true),
			  'filtre_charbon'		=> array(CHAMP_TRAD, 'Filtre à charbon', '',false,true),
			  'alarme'				=> array(CHAMP_TRAD, 'Alarme(s)', '',false,true),  
			),

			'amenagement_int' => array(
			  'amenagement_int'			=> array(CHAMP_TRAD, 'Aménagement intérieur personnalisable', '',false,true),
			  'reglage_clayettes'		=> array(CHAMP_NUMERIC, 'Hauteur de réglage des clayettes', 'mm',false,true),
			  'nb_clayettes'							=> array(CHAMP_NUMERIC, 'Nombre de clayettes maximum coulissantes', '',false,true),
			),
	
			'dimensions' => array(
			    //'dimensions_hlp'			=> array(CHAMP_TXT,'Dimensions', 'HxLxP mm',false),
			    'hauteur' 					=> array(CHAMP_NUMERIC,'Hauteur nette', 'mm'),
			    'largeur'				 	=> array(CHAMP_NUMERIC,'Largeur nette', 'mm'),
			    'profondeur' 				=> array(CHAMP_NUMERIC,'Profondeur nette', 'mm'),
			    'poids' 					=> array(CHAMP_NUMERIC,'Poids net', 'kg'),
			    'largeur_90' 				=> array(CHAMP_NUMERIC,'Largeur totale porte ouverte à 90°', 'mm'),
			    'profondeur_butee' 			=> array(CHAMP_NUMERIC,'Profondeur totale avec butée arrière', 'mm'),
			    'profondeur_porte_ouverte' 	=> array(CHAMP_NUMERIC,'Profondeur totale porte ouverte', 'mm'),
			    'pieds_reglables'			=> array(CHAMP_TRAD, 'Pieds réglables', '',false,true),
			    'volume'					=> array(CHAMP_NUMERIC, 'Volume utile', 'L',false,true),
			),
			'installation'		=> array(
			  'type_pose'			=> array(CHAMP_MULTI, 'Installation(s) possible(s)','',false,true),
			  'infos_encastrement'	=> array(CHAMP_TRAD, 'Infos pour encastrement','',false,true),
			  'type_ventilation'	=> array(CHAMP_TRAD, 'Type de ventilation','',false,true),
			  'hauteur_porte'		=> array(CHAMP_TRAD, 'Hauteur de porte','',false,true),
			  'niche_hauteur'		=> array(CHAMP_NUMERIC, 'Hauteur minimum de la niche d\'encastrement', 'mm'),
			  'niche_largeur'		=> array(CHAMP_NUMERIC, 'Largeur minimum de la niche d\'encastrement', 'mm'),
			  'niche_profondeur'	=> array(CHAMP_NUMERIC, 'Profondeur minimum de la niche d\'encastrement', 'mm'),
			  //'temp_piece'			=> array(CHAMP_NUMERIC, 'Température de la pièce d\'installation mini/maxi', '°C'),
			  'temp_instal_max'		=> array(CHAMP_LISTE_TEMP, 'Température d\'installation de la cave mini (affinage recherche, non visible)'),
			  'temp_instal_min'		=> array(CHAMP_LISTE_TEMP, 'Température d\'installation de la cave maxi (affinage recherche, non visible)'),
			),
			'technique_regulation'		=> array(
			  'nb_zone_temp'			=> array(CHAMP_TRAD, 'Nombre de zones de température','',false,true),
			  'reglage_temp'			=> array(CHAMP_TRAD, 'Plage de réglage de la température','',false,true),
			  'plage_reglage_1'			=> array(CHAMP_TRAD, 'Plage de réglage de la température Cave 1 (gauche)','',false,true),
			  'plage_reglage_2'			=> array(CHAMP_TRAD, 'Plage de réglage de la température Cave 2 (centre gauche)','',false,true),
			  'plage_reglage_3'			=> array(CHAMP_TRAD, 'Plage de réglage de la température Cave 3 (centre droit)','',false,true),
			  'plage_reglage_4'			=> array(CHAMP_TRAD, 'Plage de réglage de la température Cave 4 (droite)','',false,true),
			  'plage_reglage_zone_cent'	=> array(CHAMP_TRAD, 'Plage de réglage de la température zone centrale','',false,true),
			  'plage_reglage_zone_inf'	=> array(CHAMP_TRAD, 'Plage de réglage de la température zone inférieure','',false,true),
			  'plage_reglage_zone_sup'	=> array(CHAMP_TRAD, 'Plage de réglage de la température zone supérieure','',false,true),
			  'type_froid_cave'			=> array(CHAMP_TRAD, 'Type de production de froid','',false,true),
			  'regulation_thermostat'	=> array(CHAMP_TRAD, 'Régulation/Thermostat','',false,true),
			  'position_thermostat'		=> array(CHAMP_TRAD, 'Position thermostat','',false,true),
			  'affichage'				=> array(CHAMP_TRAD, 'Affichage','',false,true),
			  'btn_reglage'				=> array(CHAMP_TRAD, 'Boutons de réglage','',false,true),
			  'hygrometrie'				=> array(CHAMP_TRAD, 'Hygrométrie',''),
			  'affichage_hygro'			=> array(CHAMP_TRAD, 'Affichage de l\'hygrométrie',''),
			  'antivib'					=> array(CHAMP_TRAD, 'Système anti-vibrations','',false,true),
			  'degivrage_auto'			=> array(CHAMP_TRAD, 'Dégivrage automatique','',false,true), 
			),
			'technique_performances'		=> array(
				'class_energetique' 	=> array(CHAMP_LISTE_EN,'Classe énergetique', '', false),
				'iee' 					=> array(CHAMP_NUMERIC,'IEE', '', false),
				'conso_an'				=> array(CHAMP_NUMERIC, 'Consommation annuelle', 'Kwh/an'),
			 	//'conso_kw'				=> array(CHAMP_NUMERIC, 'Consommation journalière', 'Kwh/24h',false,true),
			  	'conso_euro'			=> array(CHAMP_NUMERIC, 'Coût de fonctionnement annuel', '€/an - 0,17€/kwh',false,true),
			  	'niveau_bruit'			=> array(CHAMP_NUMERIC, 'Niveau sonore', 'dB',false,true),
			  	'niveau_bruit_abcd'		=> array(CHAMP_TRAD, 'Niveau de sonore', 'ABCD',false,true),
			  	'puissance_totale' 		=> array(CHAMP_NUMERIC,'Puissance absorbée', 'W'),
		    	'intensite_electrique' 	=> array(CHAMP_NUMERIC,'Intensité absorbée', 'A',false),
		    	'classe_climatique' 	=> array(CHAMP_TXT,'Classe climatique', '', false),
		    	'gaz_froid' 			=> array(CHAMP_TRAD,'Fluide réfrigérant', '',false),
		    	'isolation' 			=> array(CHAMP_TRAD,'Isolation', '',false),
		    	'alim_elec' 			=> array(CHAMP_TRAD,'Alimentation électrique', '',false),
		    	'fiche_energie' 		=> array(CHAMP_PDF,'Etiquette énergie', '',false),
			),	 

		 'service' => array(
		      'dispo_pieces' => array(CHAMP_NUMERIC,'Disponibilité des pièces détachées', 'ans'),      
		    ),
      
    	));
  }

  function article_cave_show($tab, $title) {
    article_global_show($tab, $title, true); // true = champs groupés
  }
  function article_cave_update($tab, $title) {
    article_global_update($tab, $title, true); // true = champs groupés
  }
?>