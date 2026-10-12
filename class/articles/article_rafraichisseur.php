<?php
function article_rafraichisseur_fields() {
	return array(
    	array(
			'criteres_generaux'	=> array(
			  'capacite'									=> array(CHAMP_NUMERIC, 'Capacité','bouteilles'),
			  'nbr_temperature'						=> array(CHAMP_TRAD, 'Nombre de températures',''),
			  'reglage_temp'							=> array(CHAMP_TRAD, 'Plage de réglage de la température','',false,true),
			  'type_froid_rafraichisseur'	=> array(CHAMP_TRAD, 'Type de production de froid','',false,true),
			  'conservation_bouteille'		=> array(CHAMP_NUMERIC, 'Durée de conservation des bouteilles ','jours',false,true),
			  //'temp_piece'								=> array(CHAMP_TXT, 'Température de la pièce d\'installation mini/maxi', '°C'),
			  'temp_instal_max'						=> array(CHAMP_LISTE_TEMP, 'Température d\'installation mini (affinage recherche, non visible)'),
			  'temp_instal_min'						=> array(CHAMP_LISTE_TEMP, 'Température d\'installation maxi (affinage recherche, non visible)'),
			),

			'Design'		=> array(
			  'coloris'				=> array(CHAMP_TRAD, 'Coloris principal',''),
			  'niveau_gamme'	=> array(CHAMP_MULTI, 'Niveau de gamme','',false,true),
			  'eclairage'			=> array(CHAMP_TRAD, 'Éclairage','',false,true),
			  'matiere'				=> array(CHAMP_TRAD, 'Matériau','',false,true),
			  'habillage_ext'	=> array(CHAMP_TRAD, 'Habillage extérieur','',false,true),
			),
	
			'dimensions' => array(
					'diametre_max_bouteille'	=> array(CHAMP_NUMERIC, 'Diamètre maximum de la bouteille','mm',false,true),
				  'longeur_mini_bouteille'	=> array(CHAMP_NUMERIC, 'Hauteur mini de la bouteille','mm'),
				  'longeur_max_bouteille'		=> array(CHAMP_NUMERIC, 'Hauteur maxi de la bouteille','mm'),
			    //'dimensions'							=> array(CHAMP_TXT,'Dimensions', 'mm',false),
			    'hauteur' 								=> array(CHAMP_NUMERIC,'Hauteur Nette', 'mm'),
			    'largeur'				 					=> array(CHAMP_NUMERIC,'Largeur Nette', 'mm'),
			    'profondeur' 							=> array(CHAMP_NUMERIC,'Profondeur Nette', 'mm'),
			    'poids' 									=> array(CHAMP_NUMERIC,'Poids Net', 'kg'),
			    'volume'									=> array(CHAMP_NUMERIC, 'Volume intérieur', 'L',false,true),
			),
			'regulation'		=> array(
				'regulation_thermostat'	=> array(CHAMP_TRAD, 'Régulation/Thermostat','',false,true),
				'affichage'							=> array(CHAMP_TRAD, 'Type et position de l’affichage','',false,true),
				'mode_chauffage'				=> array(CHAMP_TRAD, 'Fonction chauffage',''),
				'alarme'								=> array(CHAMP_TRAD, 'Alarme(s)', '',false,true),
			),
			'technique_performances'		=> array(
				'puissance_totale' 			=> array(CHAMP_NUMERIC,'Puissance absorbée', 'W'),
				'intensite_electrique' 	=> array(CHAMP_NUMERIC,'Intensité absorbée', 'A',false),
				'class_energetique' 		=> array(CHAMP_LISTE_EN,'Classe énergetique', '', false),
				'conso_an'							=> array(CHAMP_NUMERIC, 'Consommation annuelle', 'Kwh/an'),
			 	//'conso_kw'							=> array(CHAMP_NUMERIC, 'Consommation journalière', 'Kwh/24h',false,true),
			  'conso_euro'						=> array(CHAMP_NUMERIC, 'Coût de fonctionnement annuel', '€/an (0.17 € kW/h)',false,true),
			  'niveau_bruit'					=> array(CHAMP_NUMERIC, 'Niveau sonore', 'dB',false,true),
			  'niveau_bruit_abcd'			=> array(CHAMP_TRAD, 'Niveau sonore (ABCD)', '',false,true),
			  'classe_climatique' 		=> array(CHAMP_TXT,'Classe climatique', '', false),
				'alim_elec' 						=> array(CHAMP_TRAD,'Alimentation électrique', '',false),
				'fiche_energie' 				=> array(CHAMP_PDF,'Étiquette énergie', '',false),
				'dispo_pieces' 					=> array(CHAMP_NUMERIC,'Disponibilité des pièces détachées', 'ans'),
	    	
			),
      
    	));
  }

  function article_rafraichisseur_show($tab, $title) {
    article_global_show($tab, $title, true); // true = champs groupés
  }
  function article_rafraichisseur_update($tab, $title) {
    article_global_update($tab, $title, true); // true = champs groupés
  }
?>