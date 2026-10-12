<?php
function article_trancheuse_fields() {
	return array(
    	array(
			'criteres_generaux'	=> array(
			  'type_produit'					=> array(CHAMP_TRAD, 'Type',''),
			  'coloris'								=> array(CHAMP_TRAD, 'Coloris principal',''),
			  'niveau_gamme'					=> array(CHAMP_MULTI, 'Niveau de gamme','',false,true),
			  'diametre_lame'					=> array(CHAMP_NUMERIC, 'Diamètre de la lame','mm',false,true),
			  'epaisseur_coupe'				=> array(CHAMP_TXT, 'Epaisseur de coupe mini/maxi','mm'),
			  'diametre_coupe'				=> array(CHAMP_NUMERIC, 'Diamètre de coupe maxi','mm'),
			  'dimensions_coupe_rect'	=> array(CHAMP_TXT, 'Dimensions de coupe maxi pour pièces rectangulaires','HxL mm'),
			),
			'dimensions' => array(
			    //'dimensions'	=> array(CHAMP_TXT,'Dimensions', 'HxLxP mm',false),
			    'hauteur' 		=> array(CHAMP_NUMERIC,'Hauteur nette', 'mm'),
			    'largeur'			=> array(CHAMP_NUMERIC,'Largeur nette', 'mm'),
			    'profondeur' 	=> array(CHAMP_NUMERIC,'Profondeur nette', 'mm'),
			    'poids' 			=> array(CHAMP_NUMERIC,'Poids net', 'kg'),
			),
			'equipements'			=> array(
			  'aiguiseur'				=> array(CHAMP_TRAD, 'Aiguiseur intégré', '',false,true),
			  'affichage'				=> array(CHAMP_TRAD, 'Type et position de l’affichage','',false,true),
			  'btn_reglage'			=> array(CHAMP_TRAD, 'Boutons de réglage','',false,true),
			  'pieds_reglables'	=> array(CHAMP_TRAD, 'Pieds réglables (oui/non/nombre + hauteur)', '',false,true),
			  'interrupteur'		=> array(CHAMP_TRAD, 'Interrupteur : Marche / Arret ', '',false,true),
			  'equipement_specifique'	=> array(CHAMP_TRAD, 'Équipement(s) spécifique(s)','',false,true),
			  'equipement_specifique1'	=> array(CHAMP_TRAD, 'Équipement(s) spécifique(s)','',false,true),
			  'equipement_specifique2'	=> array(CHAMP_TRAD, 'Équipement(s) spécifique(s)','',false,true),
			  'equipement_specifique3'	=> array(CHAMP_TRAD, 'Équipement(s) spécifique(s)','',false,true),
			),
			'technique'	=> array(
				'type_transmission' 		=> array(CHAMP_TRAD,'Type de transmission', '', false),
				'matiere_lame' 					=> array(CHAMP_TRAD,'Matériau de la lame', '', false),
				'inclinaison_lame'			=> array(CHAMP_NUMERIC, 'Inclinaison de la lame', '°'),
				'technologie_specifique'	=> array(CHAMP_TRAD, 'Technologie(s) spécifique(s)',''),
				'technologie_specifique1'	=> array(CHAMP_TRAD, 'Technologie(s) spécifique(s)',''),
				'technologie_specifique2'	=> array(CHAMP_TRAD, 'Technologie(s) spécifique(s)',''),
				'technologie_specifique3'	=> array(CHAMP_TRAD, 'Technologie(s) spécifique(s)',''),
		  	'puissance_totale' 			=> array(CHAMP_NUMERIC,'Puissance absorbée', 'W'),
	    	'intensite_electrique' 	=> array(CHAMP_NUMERIC,'Intensité absorbée', 'A',false),
	    	//'temp_piece'						=> array(CHAMP_TXT, 'Température d\'utilisation mini/maxi ', 'C°'),
			  'temp_utilisation_max'	=> array(CHAMP_LISTE_TEMP, 'Température d\'utilisation maxi','C°'),
			  'temp_utilisation_min'	=> array(CHAMP_LISTE_TEMP, 'Température d\'utilisation mini','C°'),
	    	'alim_elec' 						=> array(CHAMP_TRAD,'Alimentation électrique', '',false),
			),	 
			'sous_vide'		=> array(
			  'efficacite_pompage'			=> array(CHAMP_NUMERIC, 'Efficacité de pompage','L/mn',false,true),
			  'type_pompe'							=> array(CHAMP_TRAD, 'Type de pompe','',false,true),
			  'vitesse_pompe'						=> array(CHAMP_TRAD, 'Vitesse pompe','',false,true),
			  'scellage'								=> array(CHAMP_TRAD, 'Scellage','',false,true),
			  'largeur_barre_scellage'	=> array(CHAMP_NUMERIC, 'Largeur barre de scellage','mm',false,true),
			),
			'service' => array(
      'dispo_pieces' => array(CHAMP_NUMERIC,'Disponibilité des pièces détachées', 'ans'),
    ),

      
    	));
  }

  function article_trancheuse_show($tab, $title) {
    article_global_show($tab, $title, true); // true = champs groupés
  }
  function article_trancheuse_update($tab, $title) {
    article_global_update($tab, $title, true); // true = champs groupés
  }
?>