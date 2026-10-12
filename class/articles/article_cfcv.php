<?php
function article_cfcv_fields() {
	return array(
    	array(
			'capacite'	=> array(
			  'volume'					=> array(CHAMP_NUMERIC, 'Volume utile', 'L',false,true),
			  'chargement_max'	=> array(CHAMP_NUMERIC, 'Chargement maximum', 'kg',false,true),
			),

			'Design'		=> array(
				'coloris'							=> array(CHAMP_TRAD, 'Coloris principal',''),
				'type_porte'					=> array(CHAMP_TRAD, 'Type de porte','',false,true),
				'coloris_cadre'				=> array(CHAMP_TRAD, 'Coloris du cadre de la porte','',false,true),
				'caract_vitrage'			=> array(CHAMP_TRAD, 'Caractéristique vitrage','',false,true),
				'antiuv'							=> array(CHAMP_MULTI, 'Traitement anti-UV','',false,true),
				'coloris_vitrage'			=> array(CHAMP_TRAD, 'Coloris vitrage','',false,true),
				'eclairage'						=> array(CHAMP_TRAD, 'Éclairage intérieur','',false,true),
				'coloris_eclairage'		=> array(CHAMP_TRAD, 'Coloris de l\'éclairage','',false,true),
			  'position_eclairage'	=> array(CHAMP_TRAD, 'Position de l\'éclairage','',false,true),
			  'habillage_ext'				=> array(CHAMP_TRAD, 'Habillage extérieur','',false,true),
			  'revetement_int'			=> array(CHAMP_TRAD, 'Revêtement intérieur','',false,true),
			  'coloris_int'					=> array(CHAMP_TRAD, 'Coloris intérieur','',false,true),	  
			  'niveau_gamme'				=> array(CHAMP_MULTI, 'Niveau de gamme','',false,true),
				'poignee'							=> array(CHAMP_TRAD, 'Poignée/Ouverture','',false,true),
			),
			'equipements'		=> array(
				'serrure'						=> array(CHAMP_TRAD, 'Serrure', '',false,true),
			  'serrure_recherche'	=> array(CHAMP_MULTI, 'Serrure (affinage recherche, non visible)', '',false,true),
			  'fonction_hiver'		=> array(CHAMP_TRAD, 'Fonction Hiver', '',false,true),		  
			  'filtre_charbon'		=> array(CHAMP_TRAD, 'Filtre à charbon', '',false,true),
			  'alarme'						=> array(CHAMP_TRAD, 'Alarme', '',false,true),  
			),

			'amenagement_int' => array(
			  //'dimensions_clayettes'			=> array(CHAMP_TXT, 'Dimensions des clayettes/grilles', 'LxP mm',false,true),
			  'largeur_clayettes'					=> array(CHAMP_NUMERIC, 'Largeur clayettes/grilles', 'mm',false,true),
			  'profondeur_clayettes'			=> array(CHAMP_NUMERIC, 'Profondeur clayettes/grilles', 'mm',false,true),
			  'amenagement_int'						=> array(CHAMP_TRAD, 'Aménagement intérieur personnalisable', '',false,true),
			  'reglage_clayettes'					=> array(CHAMP_NUMERIC, 'Hauteur de réglage des clayettes', 'mm',false,true),
			  'chargement_max_clayettes'	=> array(CHAMP_NUMERIC, 'Charge maximum par clayette', 'kg',false,true),
			  'nb_clayettes'							=> array(CHAMP_NUMERIC, 'Nombre de clayettes maximum', '',false,true),
			),
	
			'dimensions' => array(
			    //'dimensions_hlp'						=> array(CHAMP_TXT,'Dimensions', 'HxLxP',false),
			    'hauteur' 									=> array(CHAMP_NUMERIC,'Hauteur nette', 'mm'),
			    'largeur'				 						=> array(CHAMP_NUMERIC,'Largeur nette', 'mm'),
			    'profondeur' 								=> array(CHAMP_NUMERIC,'Profondeur nette', 'mm'),
			    'poids' 										=> array(CHAMP_NUMERIC,'Poids net', 'kg'),
			    'largeur_90' 								=> array(CHAMP_NUMERIC,'Largeur totale porte ouverte à 90°', 'mm'),
			    'profondeur_porte_ouverte' 	=> array(CHAMP_NUMERIC,'Profondeur totale porte ouverte', 'mm'),
			    //'dimensions_int_hlp'				=> array(CHAMP_TXT,'Dimensions intérieure', 'HxLxP',false),
			    'hauteur_int' 							=> array(CHAMP_NUMERIC,'Hauteur intérieure', 'mm'),
			    'largeur_int'				 				=> array(CHAMP_NUMERIC,'Largeur intérieure', 'mm'),
			    'profondeur_int' 						=> array(CHAMP_NUMERIC,'Profondeur intérieure', 'mm'),
			    'pieds_reglables'						=> array(CHAMP_TRAD, 'Pieds réglables', '',false,true),
			    'ouverture_porte'						=> array(CHAMP_TRAD, 'Ouverture de la porte', '',false,true),
			    'porte_reversible'						=> array(CHAMP_MULTI, 'Porte réversible', '',false,true),
			    
			),
			'installation'		=> array(
			  'type_pose'						=> array(CHAMP_MULTI, 'Installation(s) possible(s)','',false,true),
			  'infos_encastrement'	=> array(CHAMP_TRAD, 'Infos pour encastrement','',false,true),
			  //'dimensions_niche'		=> array(CHAMP_TRAD, 'Dimensions de la niche d’encastrement','HxLxP mm',false,true),
			  'niche_hauteur'				=> array(CHAMP_NUMERIC, 'Hauteur minimum de la niche d\'encastrement', 'mm'),
			  'niche_largeur'				=> array(CHAMP_NUMERIC, 'Largeur minimum de la niche d\'encastrement', 'mm'),
			  'niche_profondeur'		=> array(CHAMP_NUMERIC, 'Profondeur minimum de la niche d\'encastrement', 'mm'),
			  //'temp_piece'					=> array(CHAMP_NUMERIC, 'Température de la pièce d\'installation mini/maxi', '°C'),
			  'temp_instal_max'			=> array(CHAMP_LISTE_TEMP, 'Température d\'installation mini (affinage recherche, non visible)'),
			  'temp_instal_min'			=> array(CHAMP_LISTE_TEMP, 'Température d\'installation maxi (affinage recherche, non visible)'),
			),
			'technique_regulation'		=> array(
				'type_froid_cave'					=> array(CHAMP_TRAD, 'Type de production de froid','',false,true),
				'regulation_thermostat'		=> array(CHAMP_TRAD, 'Régulation/Thermostat','',false,true),
			  'nb_zone_temp'						=> array(CHAMP_TRAD, 'Nombre de zones de température','',false,true),
			  'reglage_temp'						=> array(CHAMP_TRAD, 'Plage de réglage de la température','',false,true),
			  'plage_reglage_zone_inf'	=> array(CHAMP_TRAD, 'Plage de réglage de la température zone inférieure','',false,true),
			  'plage_reglage_zone_sup'	=> array(CHAMP_TRAD, 'Plage de réglage de la température zone supérieure','',false,true),
			  'hygrometrie'							=> array(CHAMP_TRAD, 'Hygrométrie',''),
			  'affichage'								=> array(CHAMP_TRAD, 'Affichage','',false,true),
			  'hygrometre'							=> array(CHAMP_TRAD, 'Hygromètre',''),
			  'position_thermostat'			=> array(CHAMP_TRAD, 'Position thermostat','',false,true),		  
			  'btn_reglage'							=> array(CHAMP_TRAD, 'Boutons de réglage','',false,true),
			  'degivrage_auto'					=> array(CHAMP_TRAD, 'Dégivrage automatique','',false,true), 
			  'antivib'									=> array(CHAMP_TRAD, 'Système anti-vibrations','',false,true),
			),
			'technique_performances'	=> array(
				'class_energetique' 		=> array(CHAMP_LISTE_EN,'Classe énergetique', '', false),
				'conso_an'							=> array(CHAMP_NUMERIC, 'Consommation annuelle', 'Kwh/an'),
			 	//'conso_kw'							=> array(CHAMP_NUMERIC, 'Consommation journalière', 'Kwh/24h',false,true),
			  'conso_euro'						=> array(CHAMP_NUMERIC, 'Coût de fonctionnement annuel', '0.17 € kW/h',false,true),
			  'iee' 									=> array(CHAMP_NUMERIC,'IEE', '', false),
			  'niveau_bruit'					=> array(CHAMP_NUMERIC, 'Niveau sonore', 'dB',false,true),
			  'niveau_bruit_abcd'			=> array(CHAMP_TRAD, 'Niveau sonore (ABCD)', '',false,true),
			  'puissance_totale' 			=> array(CHAMP_NUMERIC,'Puissance absorbée', 'W'),
		    'intensite_electrique' 	=> array(CHAMP_NUMERIC,'Intensité absorbée', 'A',false),
		    'gaz_froid' 						=> array(CHAMP_TRAD,'Fluide réfrigérant', '', false),
		    'isolation' 						=> array(CHAMP_TRAD,'Isolation', '', false),
		    'alim_elec' 						=> array(CHAMP_TRAD,'Alimentation électrique', '',false),
		    'fiche_energie' 				=> array(CHAMP_PDF,'Étiquette énergie', '',false),
			),	 

		 'service' => array(
		      'dispo_pieces' => array(CHAMP_NUMERIC,'Disponibilité des pièces détachées', 'ans'),      
		    ),
      
    	));
  }

  function article_cfcv_show($tab, $title) {
    article_global_show($tab, $title, true); // true = champs groupés
  }
  function article_cfcv_update($tab, $title) {
    article_global_update($tab, $title, true); // true = champs groupés
  }
?>