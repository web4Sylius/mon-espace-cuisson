<?php
//Machine à glaçons
function article_mag_fields() {
	return array(
    	array(
			'criteres_generaux'	=> array(
			  'type_glace'					=> array(CHAMP_TRAD, 'Type de glace', '',false,true),
			),
			'capacite'	=> array(
			  'capacite_glace1'		=> array(CHAMP_NUMERIC, 'Capacité de production /24h (température air 21°C / eau 15°C)', '',false,true),
			  'capacite_glace2'		=> array(CHAMP_NUMERIC, 'Capacité de production /24h (température air 10°C / eau 10°C)', '',false,true),
			  'capacite_prod' 		=> array(CHAMP_NUMERIC, 'Capacité de production maxi /24h (température air 10°C / eau 7°C)', ''),
			  'capacite_reserve'	=> array(CHAMP_NUMERIC, 'Capacité de la réserve', 'kg',false,true),
			  'poids_glace'				=> array(CHAMP_NUMERIC, 'Poids des glaçons', 'g',false),
			  'nb_glace_cycle' 		=> array(CHAMP_NUMERIC, 'Nombre de glaçons par cycle', ''),
			),

			'Design'		=> array(
				'coloris'							=> array(CHAMP_TRAD, 'Coloris principal',''),
				'type_porte'					=> array(CHAMP_TRAD, 'Type d\'ouverture','',false,true),
				'niveau_gamme'				=> array(CHAMP_MULTI, 'Niveau de gamme','',false,true),				
				'habillage_ext'				=> array(CHAMP_TRAD, 'Habillage extérieur','',false,true),
				'coloris_int'					=> array(CHAMP_TRAD, 'Revêtement cuve/coloris intérieur','',false,true),
			),
			'equipements'		=> array(
				//'accessoires_inclus'						=> array(CHAMP_TRAD, 'Accessoires inclus', '',false,true),
				'alarme'						=> array(CHAMP_TRAD, 'Alarme(s)', '',false,true),
				'verrouillage_cmd'			=> array(CHAMP_MULTI, 'Verrouillage des commandes', '',false,true),
				'pieds_reglables'						=> array(CHAMP_TRAD, 'Pieds réglables', '',false,true),
				'equipement_specifique'	=> array(CHAMP_TRAD, 'Équipement(s) spécifique(s)','',false,true),
			  'equipement_specifique1'	=> array(CHAMP_TRAD, 'Équipement(s) spécifique(s)','',false,true),
			  'equipement_specifique2'	=> array(CHAMP_TRAD, 'Équipement(s) spécifique(s)','',false,true),
			  'equipement_specifique3'	=> array(CHAMP_TRAD, 'Équipement(s) spécifique(s)','',false,true),
			),
	
			'dimensions' => array(
			    //'dimensions_hlp'						=> array(CHAMP_TXT,'Dimensions', 'HxLxP mm',false),
			    'hauteur' 									=> array(CHAMP_NUMERIC,'Hauteur nette', 'mm'),
			    'largeur'				 						=> array(CHAMP_NUMERIC,'Largeur nette', 'mm'),
			    'profondeur' 								=> array(CHAMP_NUMERIC,'Profondeur nette', 'mm'),
			    'poids' 										=> array(CHAMP_NUMERIC,'Poids net', 'kg'),
			    //'niche_dimensions'		=> array(CHAMP_TXT, 'Dimensions de la niche d\'encastrement', 'HxLxP mm'),
			    'niche_hauteur'				=> array(CHAMP_NUMERIC, 'Hauteur minimum de la niche d\'encastrement', 'mm'),
			  	'niche_largeur'				=> array(CHAMP_NUMERIC, 'Largeur minimum de la niche d\'encastrement', 'mm'),
			  	'niche_profondeur'		=> array(CHAMP_NUMERIC, 'Profondeur minimum de la niche d\'encastrement', 'mm'),			  	
			  	'infos_encastrement'	=> array(CHAMP_TRAD, 'Infos pour encastrement','',false,true),	    
			),
			'technique'		=> array(
				'type_froid_cave'					=> array(CHAMP_TRAD, 'Type de froid','',false,true),
				'degivrage_auto'					=> array(CHAMP_TRAD, 'Dégivrage automatique','',false,true), 
				'affichage'								=> array(CHAMP_TRAD, 'Affichage','',false,true),
				'condenseur'							=> array(CHAMP_TRAD, 'Refroidissement condenseur',''),
			  'isolation'								=> array(CHAMP_TRAD, 'Isolation','',false,true),
			  'technologie_specifique'	=> array(CHAMP_TRAD, 'Technologie(s) spécifique(s)',''),
			  'technologie_specifique1'	=> array(CHAMP_TRAD, 'Technologie(s) spécifique(s)',''),
			  'technologie_specifique2'	=> array(CHAMP_TRAD, 'Technologie(s) spécifique(s)',''),
			  'technologie_specifique3'	=> array(CHAMP_TRAD, 'Technologie(s) spécifique(s)',''),
			),
			'technique_performances'	=> array(
				'conso_eau' 						=> array(CHAMP_TRAD,'« Consommation d’eau', 'l/kg de glace', false),
				'classe_climatique'			=> array(CHAMP_TRAD,'Classe climatique', '', false),
				'puissance_totale' 			=> array(CHAMP_NUMERIC,'Puissance absorbée', 'W'),	  
				'intensite_electrique' 	=> array(CHAMP_NUMERIC,'Intensité absorbée', 'A',false),  
		    'intensite_max' 				=> array(CHAMP_NUMERIC,'Intensité Max', 'A',false),
		    'pression_mini' 				=> array(CHAMP_NUMERIC,'Pression de l\'arrivée d’eau mini', 'bars',false),
		    'pression_maxi' 				=> array(CHAMP_NUMERIC,'Pression de l\'arrivée d’eau maxi', 'bars',false),
		    'class_energetique' 		=> array(CHAMP_LISTE_EN,'Classe énergetique', '', false),
		    'conso_an'							=> array(CHAMP_NUMERIC, 'Consommation annuelle', 'Kwh/an'),
			 	//'conso_kw'							=> array(CHAMP_NUMERIC, 'Consommation journalière', 'Kwh/24h',false,true),
			  'conso_euro'						=> array(CHAMP_NUMERIC, 'Coût de fonctionnement annuel (0.17 € kW/h)', '',false,true),
			  'niveau_bruit'					=> array(CHAMP_NUMERIC, 'Niveau sonore', 'dB',false,true),
			  //'temp_piece'						=> array(CHAMP_NUMERIC, 'Température de la pièce d\'installation mini/maxi', '°C'),
			  'temp_instal_max'				=> array(CHAMP_LISTE_TEMP, 'Température d\'installation mini (affinage recherche, non visible)'),
			  'temp_instal_min'				=> array(CHAMP_LISTE_TEMP, 'Température d\'installation maxi (affinage recherche, non visible)'),
		    'gaz_froid' 						=> array(CHAMP_TRAD,'Fluide réfrigérant', '', false),
		    'alim_elec' 						=> array(CHAMP_TRAD,'Alimentation électrique', '',false),
		    'fiche_energie' 				=> array(CHAMP_PDF,'Étiquette énergie', '',false),
			),	 
      
    	));
  }

  function article_mag_show($tab, $title) {
    article_global_show($tab, $title, true); // true = champs groupés
  }
  function article_mag_update($tab, $title) {
    article_global_update($tab, $title, true); // true = champs groupés
  }
?>