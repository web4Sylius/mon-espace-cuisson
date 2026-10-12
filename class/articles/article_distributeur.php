<?php
function article_distributeur_fields() {
	return array(
    	array(
			'capacite'	=> array(
			  'capacite'							=> array(CHAMP_NUMERIC, 'Capacité totale (bouteilles inertées + bouteilles en réserve à température) ',''),
			  'nb_emplacements'				=> array(CHAMP_NUMERIC, 'Nombre d’emplacement pour la distribution','bouteilles'),
			  'nb_max_bouteilles'			=> array(CHAMP_NUMERIC, 'Nombre maxi de bouteilles inertées simultanément','bouteilles'),
			  'nb_max_bouteilles2'		=> array(CHAMP_NUMERIC, 'Nombre maxi de bouteilles en réserve à température de dégustation','bouteilles'),
			  'longeur_max_bouteille'	=> array(CHAMP_NUMERIC, 'Hauteur maximale des bouteilles','mm'),
			),

			'Design'		=> array(
			  'coloris'					=> array(CHAMP_TRAD, 'Coloris principal',''),
			  'type_porte'			=> array(CHAMP_TRAD, 'Type de porte','',false,true),
			  'coloris_cadre'		=> array(CHAMP_TRAD, 'Coloris du cadre de la porte','',false,true),
			  'habillage_ext'		=> array(CHAMP_TRAD, 'Habillage extérieur','',false,true),
			  'niveau_gamme'		=> array(CHAMP_MULTI, 'Niveau de gamme','',false,true),	  
			),
			'technique_distributeur'		=> array(
			  'utilisation'							=> array(CHAMP_MULTI, 'Utilisation(s) possible(s)','',false,true),
			  'nb_dosage'								=> array(CHAMP_TRAD, 'Nombre de dosage pré-programmés','',false,true),
			  'techno_conservation'			=> array(CHAMP_TRAD, 'Technologie de conservation des bouteilles','',false,true),
			  'position_bonbonne'				=> array(CHAMP_TRAD, 'Position bonbonne de gaz','',false,true),
			  'autonomie_bonbonne'			=> array(CHAMP_NUMERIC, 'Autonomie bonbonne de gaz','bouteilles',false,true),
			  'technologie_specifique'	=> array(CHAMP_TRAD, 'Technologie(s) spécifique(s)',''),
				'technologie_specifique1'	=> array(CHAMP_TRAD, 'Technologie(s) spécifique(s)',''),
				'technologie_specifique2'	=> array(CHAMP_TRAD, 'Technologie(s) spécifique(s)',''),
				'technologie_specifique3'	=> array(CHAMP_TRAD, 'Technologie(s) spécifique(s)',''),
			),
			'equipements_distributeur'		=> array(
			  'verouillage_tableau'		=> array(CHAMP_TRAD, 'Verrouillage du tableau de contrôle', '',false,true),
			  'distrib_libre_service'				=> array(CHAMP_TRAD, 'Equipement pour distribution libre-service', '',false,true),
			  'logiciel_caisse'	=> array(CHAMP_TRAD, 'Connexion au logiciel de caisse', '',false,true),
			  'interface_con'		=> array(CHAMP_TRAD, 'Interface de connexion', '',false,true),
			  'logiciel_gestion'				=> array(CHAMP_TRAD, 'Logiciel de gestion intégré', '',false,true),
			  'equipement_specifique'	=> array(CHAMP_TRAD, 'Équipement(s) spécifique(s)','',false,true),
			  'equipement_specifique1'	=> array(CHAMP_TRAD, 'Équipement(s) spécifique(s)','',false,true),
			  'equipement_specifique2'	=> array(CHAMP_TRAD, 'Équipement(s) spécifique(s)','',false,true),
			  'equipement_specifique3'	=> array(CHAMP_TRAD, 'Équipement(s) spécifique(s)','',false,true),
			),
			'equipements'		=> array(
			  'eclairage_int'				=> array(CHAMP_TRAD, 'Eclairage intérieur', '',false,true),
			  'verouillage_porte'		=> array(CHAMP_TRAD, 'Système de verrouillage des portes', '',false,true),
			  'alarme'				=> array(CHAMP_TRAD, 'Alarme(s)', '',false,true), 
			  'poignee'				=> array(CHAMP_TRAD, 'Type de poignée','',false,true),
			),
			'technique'		=> array(
			  'nb_zone_temp'						=> array(CHAMP_TRAD, 'Nombre de zones de température','',false,true),
			  'reglage_temp'						=> array(CHAMP_TRAD, 'Plage de réglage de la température','',false,true),
			  'regulation_thermostat'		=> array(CHAMP_TRAD, 'Régulation/Thermostat','',false,true),
			  'affichage'								=> array(CHAMP_TRAD, 'Type et position de l’affichage','',false,true),
			  'btn_reglage'							=> array(CHAMP_TRAD, 'Boutons de réglage','',false,true),
			  'position_thermostat'			=> array(CHAMP_TRAD, 'Position thermostat','',false,true),
			  'degivrage_auto'					=> array(CHAMP_TRAD, 'Dégivrage automatique','',false,true),
			  'fonction_hiver'					=> array(CHAMP_TRAD, 'Fonction Hiver', '',false,true), 
			  'type_froid_distributeur'	=> array(CHAMP_TRAD, 'Type de production de froid','',false,true),
			  'antivib'									=> array(CHAMP_TRAD, 'Système anti-vibrations','',false,true),
			  'gaz_froid' 							=> array(CHAMP_TRAD,'Fluide réfrigérant', '', false),
			  'puissance_totale' 				=> array(CHAMP_NUMERIC,'Puissance absorbée', 'W'),
		    'intensite_electrique' 		=> array(CHAMP_NUMERIC,'Intensité absorbée', 'A',false),
		    'classe_climatique' 			=> array(CHAMP_TXT,'Classe climatique', '', false),
		    'class_energetique' 			=> array(CHAMP_LISTE_EN,'Classe énergetique', '', false),
		    'conso_an'								=> array(CHAMP_NUMERIC, 'Consommation annuelle', 'Kwh/an'),
			 	//'conso_kw'								=> array(CHAMP_NUMERIC, 'Consommation journalière', 'Kwh/24h',false,true),
			  'conso_euro'							=> array(CHAMP_NUMERIC, 'Coût de fonctionnement annuel ', '€/an (0.17 € kW/h)',false,true),
			  'niveau_bruit'						=> array(CHAMP_NUMERIC, 'Niveau sonore', 'dB',false,true),
			  'niveau_bruit_abcd'				=> array(CHAMP_TRAD, 'Niveau sonore (ABCD)', '',false,true),
			  'alim_elec' 							=> array(CHAMP_TRAD,'Alimentation électrique', '',false),
			  'dispo_pieces' 						=> array(CHAMP_NUMERIC,'Disponibilité des pièces détachées', 'ans'),
		    'fiche_energie' 					=> array(CHAMP_PDF,'Étiquette énergie', '',false), 
			),
	
			'dimensions' => array(
					//'dimensions_hlp'	=> array(CHAMP_TXT,'Dimensions', 'HxLxP mm',false),
			    'hauteur' 				=> array(CHAMP_NUMERIC,'Hauteur nette', 'mm'),
			    'largeur'				 	=> array(CHAMP_NUMERIC,'Largeur nette', 'mm'),
			    'profondeur' 			=> array(CHAMP_NUMERIC,'Profondeur nette', 'mm'),
			    'poids' 					=> array(CHAMP_NUMERIC,'Poids net', 'kg'),
			),
			'installation'		=> array(
			  'type_pose'			=> array(CHAMP_MULTI, 'Installation(s) possible(s)','',false,true),
			  'niche_hauteur'		=> array(CHAMP_NUMERIC, 'Hauteur minimum de la niche d\'encastrement', 'mm'),
			  'niche_largeur'		=> array(CHAMP_NUMERIC, 'Largeur minimum de la niche d\'encastrement', 'mm'),
			  'niche_profondeur'	=> array(CHAMP_NUMERIC, 'Profondeur minimum de la niche d\'encastrement', 'mm'),
			  'infos_encastrement'	=> array(CHAMP_TRAD, 'Infos pour encastrement','',false,true),
			  'pieds_reglables'			=> array(CHAMP_TRAD, 'Pieds réglables', '',false,true),
			  'roulettes'			=> array(CHAMP_TRAD, 'Roulettes', '',false,true),
			  'ouverture_porte'		=> array(CHAMP_TRAD, 'Ouverture de la porte','',false,true),
			  //'temp_piece'			=> array(CHAMP_NUMERIC, 'Température de la pièce d\'installation mini/maxi', '°C'),
			  'temp_instal_max'		=> array(CHAMP_LISTE_TEMP, 'Température de la pièce d\'installation mini', '°C'),
			  'temp_instal_min'		=> array(CHAMP_LISTE_TEMP, 'Température de la pièce d\'installation mini','°C'),
			),    
    	));
  }

  function article_distributeur_show($tab, $title) {
    article_global_show($tab, $title, true); // true = champs groupés
  }
  function article_distributeur_update($tab, $title) {
    article_global_update($tab, $title, true); // true = champs groupés
  }
?>