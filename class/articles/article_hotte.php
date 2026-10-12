<?php

// var_dump(debug_backtrace());

  function article_hotte_fields() {
    return array(
      array(
	'criteres_generaux'	=> array(
	  'type_hotte'			=> array(CHAMP_TRAD, 'Type de hotte','',true,true),
	  'type_pose'			=> array(CHAMP_TRAD,'Type de pose','',false,true),
	  'forme'			=> array(CHAMP_TRAD, 'Forme de la hotte','',true,true),
	  'type_aspiration'		=> array(CHAMP_TRAD, 'Type d\'évacuation','',true,true),
	  'nb_vitesses'			=> array(CHAMP_TRAD, 'Nombre de vitesses d\'aspiration','',false,true),
	),
	'commandes'		=> array(
	  'type_cmd_hotte'		=> array(CHAMP_TRAD, 'Type de commandes','',true,true),
	  'telecommande'		=> array(CHAMP_MULTI, 'Télécommande','',false,true),
	),
	'fonctionnalites'	=> array(
	  'fonction_booster_hottes'	=> array(CHAMP_MULTI, 'Fonction booster','',false,true),
	  'mode_aspiration'		=> array(CHAMP_TRAD, 'Mode d\'aspiration','',true,true),
	  'fonction_arret_differe'	=> array(CHAMP_MULTI, 'Fonction arrêt différé','',false,true),
	),
	'installation'		=> array(
	  'distance_mini'		=> array(CHAMP_NUMERIC, 'Distance min entre la hotte et la plaque', 'cm',false,true),
	  'diametre_sortie_air'		=> array(CHAMP_NUMERIC, 'Diamètre de la sortie d\'air', 'cm',false,true),
	),
	'eclairage'		=> array(
	  'eclairage'	=> array(CHAMP_MULTI, 'Éclairage','',false,true),
	),
	'performances'		=> array(
	  'debit_air_max'		=> array(CHAMP_NUMERIC, 'Debit d\'air maximum', 'm³/h',true,true),
	  'debit_air_booster'		=> array(CHAMP_NUMERIC, 'Debit d\'air booster', 'm³/h',false,true),
	  'pression_mini'		=> array(CHAMP_NUMERIC, 'Pression mini', 'Pa',false,true),
	  'pression_maxi'		=> array(CHAMP_NUMERIC, 'Pression maxi', 'Pa',false,true),
	  'puissance_mini'		=> array(CHAMP_NUMERIC, 'Puissance mini', 'W',false,true),
	  'puissance_maxi'		=> array(CHAMP_NUMERIC, 'Puissance maxi', 'W',false,true),
	  'niveau_sonore_mini'		=> array(CHAMP_TRAD, 'Niveau sonore vitesse minimale', 'dB',false,true),
	  'niveau_sonore'		=> array(CHAMP_NUMERIC, 'Niveau sonore vitesse maximale', 'dB',true,true),
	  'niv_sonore_booster'		=> array(CHAMP_NUMERIC, 'Niveau sonore booster', 'dB',false,true),
	  'puissance_totale' => array(CHAMP_NUMERIC,'Puissance totale', 'W'),
    'intensite_electrique' => array(CHAMP_TRAD,'Intensité Electrique', 'A',false),
    'tension_electrique' => array(CHAMP_TRAD,'Tension éléctrique', 'V', false),
    'frequence' => array(CHAMP_TRAD,'Fréquence', 'Hz',false),
    'cordon_alim' => array(CHAMP_TRAD,'Cordon d\'alimentation', '',false),
    'class_energetique' => array(CHAMP_LISTE_EN,'Classe énergétique', false,false),
	),
	'filtration'		=> array(
	  'filtre_graisse'		=> array(CHAMP_MULTI, 'Filtre(s) graisse','',false,true),
	  'filtre_charbon'		=> array(CHAMP_MULTI, 'Filtre(s) à charbon','',false,true),
	  'voyant_saturation_filtres'	=> array(CHAMP_MULTI, 'Voyant de saturation filtres','',false,true),
	),
	 'dimensions' => array(
		      'hauteur' => array(CHAMP_NUMERIC,'Hauteur', 'mm'),
		      'largeur' => array(CHAMP_NUMERIC,'Largeur', 'mm'),
		      'profondeur' => array(CHAMP_NUMERIC,'Profondeur', 'mm'),
		      'poids' => array(CHAMP_NUMERIC,'Poids', 'g'),
		 ),
		 'dimensions_encastrement' => array(
		      'hauteur_encastrement' => array(CHAMP_NUMERIC,'Hauteur', 'mm'),
		      'largeur_encastrement' => array(CHAMP_NUMERIC,'Largeur', 'mm'),
		      'profondeur_encastrement' => array(CHAMP_NUMERIC, 'Profondeur', 'mm')
		    ),
		 'coloris' => array(
	      'couleur' => array(CHAMP_MULTI,'Couleur', '',false),
	      'couleur_fab' => array(CHAMP_TRAD,'Couleur fabricant', ''),
	    'matiere' => array(CHAMP_TRAD,'Matière', ''),
	    )
      )
    );
  }

  function article_hotte_show($tab, $title) {
    article_global_show($tab, $title, true); // true = champs groupés
  }
  function article_hotte_update($tab, $title) {
    article_global_update($tab, $title, true); // true = champs groupés
  }
?>