<?php
//armoire refrigerateur congelateur
function article_froid_mobile_fields() {
	return array(
    	array(
			'capacite'	=> array(
			  'volume'					=> array(CHAMP_NUMERIC, 'Volume utile', 'L',false,true),
			  'volume_brut'			=> array(CHAMP_NUMERIC, 'Volume brut', 'L',false,true),
			  'capacite'				=> array(CHAMP_NUMERIC, 'Capacité (Bordeaux 0,75L)', '',false,true),
			  'capacite_canette'=> array(CHAMP_NUMERIC, 'Capacité (canettes 0,33L)', '',false,true),
			),

			'Design'		=> array(
				'coloris'							=> array(CHAMP_TRAD, 'Coloris principal',''),
				'type_porte'					=> array(CHAMP_TRAD, 'Type d\'ouverture','',false,true),
				'coloris_cadre'				=> array(CHAMP_TRAD, 'Coloris du cadre de la porte','',false,true),
				'niveau_gamme'				=> array(CHAMP_MULTI, 'Niveau de gamme','',false,true),
				'habillage_ext'				=> array(CHAMP_TRAD, 'Habillage extérieur','',false,true),
				'coloris_int'					=> array(CHAMP_TRAD, 'Revêtement cuve/coloris intérieur','',false,true),
				'eclairage'						=> array(CHAMP_TRAD, 'Éclairage intérieur','',false,true),
			),
			'equipements'		=> array(
				'amenagement_int'			=> array(CHAMP_TRAD, 'Aménagement intérieur personnalisable', '',false,true),
				'serrure'						=> array(CHAMP_TRAD, 'Serrure', '',false,true),
			  'serrure_recherche'	=> array(CHAMP_MULTI, 'Serrure (affinage recherche, non visible)', '',false,true),
			  'alarme'						=> array(CHAMP_TRAD, 'Alarme(s)', '',false,true),
			  'type_poignee'						=> array(CHAMP_TRAD, 'Type de poignée', '',false,true),
			  'roulettes'						=> array(CHAMP_TRAD, 'Roulettes', '',false,true),
			  'equipement_specifique'	=> array(CHAMP_TRAD, 'Equipement(s) spécifique(s)',''),		  
			  'equipement_specifique1'	=> array(CHAMP_TRAD, 'Équipement(s) spécifique(s)','',false,true),
			  'equipement_specifique2'	=> array(CHAMP_TRAD, 'Équipement(s) spécifique(s)','',false,true),
			  'equipement_specifique3'	=> array(CHAMP_TRAD, 'Équipement(s) spécifique(s)','',false,true),
			),
	
			'dimensions' => array(
			    //'dimensions_hlp'						=> array(CHAMP_TXT,'dimensions', 'HxLxP',false),
			    'hauteur' 									=> array(CHAMP_NUMERIC,'Hauteur nette', 'mm'),
			    'largeur'				 						=> array(CHAMP_NUMERIC,'Largeur nette', 'mm'),
			    'profondeur' 								=> array(CHAMP_NUMERIC,'Profondeur nette', 'mm'),
			    'poids' 										=> array(CHAMP_NUMERIC,'Poids net', 'kg'),
			  	'sensporte'						=> array(CHAMP_TRAD, 'Sens d\'ouverture de la porte','',false,true),
			    'porte_reversible'						=> array(CHAMP_MULTI, 'Porte réversible', '',false,true),
			    'largeur_90' 								=> array(CHAMP_NUMERIC,'Largeur totale porte ouverte à 90°', 'mm'),
			    'profondeur_porte_ouverte' 	=> array(CHAMP_NUMERIC,'Profondeur totale porte ouverte', 'mm'),
			    //'dimensions_cuve'				=> array(CHAMP_TXT,'Dimensions de la cuve', 'HxLxP mm',false),
			    'hauteur_int' 							=> array(CHAMP_NUMERIC,'Hauteur intérieure cuve', 'mm'),
			    'largeur_int'				 				=> array(CHAMP_NUMERIC,'Largeur intérieure cuve', 'mm'),
			    'profondeur_int' 						=> array(CHAMP_NUMERIC,'Profondeur intérieure cuve', 'mm'),			    
			),
			'technique'		=> array(
				'type_froid'							=> array(CHAMP_TRAD, 'Production de froid','',false,true),
				'reglage_temp'						=> array(CHAMP_TRAD, 'Plage de réglage de la température','',false,true),
				'regulation_thermostat'		=> array(CHAMP_TRAD, 'Régulation','',false,true),
				'degivrage_auto'					=> array(CHAMP_TRAD, 'Dégivrage automatique','',false,true), 
				'affichage'								=> array(CHAMP_TRAD, 'Affichage','',false,true),
			  'isolation'								=> array(CHAMP_TRAD, 'Isolation','',false,true),
			  'technologie_specifique'	=> array(CHAMP_TRAD, 'Technologie(s) spécifique(s)',''),
			  'technologie_specifique1'	=> array(CHAMP_TRAD, 'Technologie(s) spécifique(s)',''),
			  'technologie_specifique2'	=> array(CHAMP_TRAD, 'Technologie(s) spécifique(s)',''),
			  'technologie_specifique3'	=> array(CHAMP_TRAD, 'Technologie(s) spécifique(s)',''),
			),
			'technique_performances'	=> array(
				'alim_elec' 						=> array(CHAMP_TRAD,'Alimentation électrique', '',false),
				'niveau_bruit'					=> array(CHAMP_NUMERIC, 'Niveau sonore', 'dB',false,true),
				//'temp_piece'					=> array(CHAMP_NUMERIC, 'Température de la pièce d\'installation mini/maxi', 'm³/h'),
			  'temp_instal_max'			=> array(CHAMP_LISTE_TEMP, 'Température d\'installation mini (affinage recherche, non visible)'),
			  'temp_instal_min'			=> array(CHAMP_LISTE_TEMP, 'Température d\'installation maxi (affinage recherche, non visible)'),
			  'class_energetique' 		=> array(CHAMP_LISTE_EN,'Classe énergetique', '', false),
		    'conso_an'							=> array(CHAMP_NUMERIC, 'Consommation en Kwh/an', 'Kwh'),
			 	//'conso_kw'							=> array(CHAMP_NUMERIC, 'Consommation en Kwh/24h', 'Kwh',false,true),
			  'conso_euro'						=> array(CHAMP_NUMERIC, 'Consommation en €/an (0.17 € kW/h)', 'EUR',false,true),
			  'iee' 									=> array(CHAMP_NUMERIC,'IEE', '', false),
			  'puissance_totale' 			=> array(CHAMP_NUMERIC,'Puissance absorbée', 'W'),
		    'intensite_electrique' 	=> array(CHAMP_NUMERIC,'Intensité absorbée', 'A',false),
				'classe_climatique' 		=> array(CHAMP_TRAD,'Classe climatique', '', false),
				'gaz_froid' 						=> array(CHAMP_TRAD,'Fluide réfrigérant', '', false),
		    'fiche_energie' 				=> array(CHAMP_PDF,'Étiquette énergie', '',false),		    
			),	 
      
    	));
  }

  function article_froid_mobile_show($tab, $title) {
    article_global_show($tab, $title, true); // true = champs groupés
  }
  function article_froid_mobile_update($tab, $title) {
    article_global_update($tab, $title, true); // true = champs groupés
  }
?>