<?php
//armoire refrigerateur congelateur
function article_minibar_fields() {
	return array(
    	array(
			'capacite'	=> array(
			  'volume'					=> array(CHAMP_NUMERIC, 'Volume utile', 'L',false,true),
			  'volume_brut'			=> array(CHAMP_NUMERIC, 'Volume brut', 'L',false,true),
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
				'reglage_clayettes'		=> array(CHAMP_NUMERIC, 'Hauteur de réglage des clayettes', 'mm',false,true),
				'serrure'						=> array(CHAMP_TRAD, 'Serrure', '',false,true),
			  'serrure_recherche'	=> array(CHAMP_MULTI, 'Serrure (affinage recherche, non visible)', '',false,true),
			  'alarme'						=> array(CHAMP_TRAD, 'Alarme(s)', '',false,true),
			  'type_poignee'						=> array(CHAMP_TRAD, 'Type de poignée', '',false,true),
			  'pieds_reglables'						=> array(CHAMP_TRAD, 'Pieds réglables', '',false,true),
			  'equipement_specifique'	=> array(CHAMP_TRAD, 'Equipement(s) spécifique(s)',''),
			  'equipement_specifique1'	=> array(CHAMP_TRAD, 'Equipement(s) spécifique(s)',''),
			  'equipement_specifique2'	=> array(CHAMP_TRAD, 'Equipement(s) spécifique(s)',''),
			  'equipement_specifique3'	=> array(CHAMP_TRAD, 'Equipement(s) spécifique(s)',''),		 
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
			  	'porte_reversible'						=> array(CHAMP_MULTI, 'Porte réversible', '',false,true),
			    'largeur_90' 								=> array(CHAMP_NUMERIC,'Largeur totale porte ouverte à 90°', 'mm'),
			    'profondeur_porte_ouverte' 	=> array(CHAMP_NUMERIC,'Profondeur totale porte ouverte', 'mm'),
			    //'dimensions_cuve'				=> array(CHAMP_TXT,'Dimensions de la cuve', 'HxLxP mm',false),
			    'hauteur_int' 							=> array(CHAMP_NUMERIC,'Hauteur intérieure cuve', 'mm'),
			    'largeur_int'				 				=> array(CHAMP_NUMERIC,'Largeur intérieure cuve', 'mm'),
			    'profondeur_int' 						=> array(CHAMP_NUMERIC,'Profondeur intérieure cuve', 'mm'),			    
			),
			'technique'		=> array(
				'type_froid'					=> array(CHAMP_TRAD, 'Production de froid','',false,true),
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
				'niveau_bruit'					=> array(CHAMP_NUMERIC, 'Niveau sonore', 'dB',false,true),
				'class_energetique' 		=> array(CHAMP_LISTE_EN,'Classe énergetique', '', false),
				'conso_an'							=> array(CHAMP_NUMERIC, 'Consommation annuelle', 'Kwh/an'),
			 	//'conso_kw'							=> array(CHAMP_NUMERIC, 'Consommation journalière', 'Kwh/24h',false,true),
			  'conso_euro'						=> array(CHAMP_NUMERIC, 'Coût de fonctionnement annuel (0.17 € kW/h)', '',false,true),
			  'iee' 									=> array(CHAMP_NUMERIC,'IEE', '', false),
			  'puissance_totale' 			=> array(CHAMP_NUMERIC,'Puissance absorbée', 'W'),
		    'intensite_electrique' 	=> array(CHAMP_NUMERIC,'Intensité absorbée', 'A',false),
				'classe_climatique' 		=> array(CHAMP_TRAD,'Classe climatique', '', false),
				//'temp_piece'					=> array(CHAMP_NUMERIC, 'Température de la pièce d\'installation mini/maxi', '°C'),
			  'temp_instal_max'			=> array(CHAMP_LISTE_TEMP, 'Température d\'installation mini (affinage recherche, non visible)'),
			  'temp_instal_min'			=> array(CHAMP_LISTE_TEMP, 'Température d\'installation maxi (affinage recherche, non visible)'),			  
		    'gaz_froid' 						=> array(CHAMP_TRAD,'Fluide réfrigérant', '', false),
		    'alim_elec' 						=> array(CHAMP_TRAD,'Alimentation électrique', '',false),
		    'fiche_energie' 				=> array(CHAMP_PDF,'Étiquette énergie', '',false),
			),	 
      
    	));
  }

  function article_minibar_show($tab, $title) {
    article_global_show($tab, $title, true); // true = champs groupés
  }
  function article_minibar_update($tab, $title) {
    article_global_update($tab, $title, true); // true = champs groupés
  }
?>