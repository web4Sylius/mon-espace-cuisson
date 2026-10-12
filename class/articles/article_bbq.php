<?php
  function article_bbq_fields() {
    return array(
      array(
	'criteres_generaux'	=> array(
	  // champ			// traduire, texte du label
	  'type_bbq'			=> array(CHAMP_TRAD, 'Type de barbecue','',true,true),
	  'plancha'			=> array(CHAMP_MULTI, 'Plancha','',false,true),
	  'systeme_allumage'		=> array(CHAMP_TRAD, 'Système d\'allumage','',false,true),
	  'type_bouton_reglage'		=> array(CHAMP_TRAD, 'Boutons de réglage','',false,true),
	  'matiere_gille'		=> array(CHAMP_TRAD, 'Matière de la grille','',false,true),
	  'tiroir_rangement'		=> array(CHAMP_MULTI, 'Tiroir de rangement','',false,false),
	  'logement_bouteille'		=> array(CHAMP_TRAD, 'Logement pour bouteille gaz','',false,false),
	),
	'performances' => array(
		'thermostat_reglable'		=> array(CHAMP_TRAD, 'Plage de température','',false,true),
		'consommation_gaz'		=> array(CHAMP_TRAD, 'Consommation gaz', 'g/h',false,true),
	  'consommation_elec'		=> array(CHAMP_TRAD, 'Consommation électrique', 'kW/h','',false,true),
	  'puissance_totale' => array(CHAMP_NUMERIC,'Puissance', 'W'),
    'intensite_electrique' => array(CHAMP_TRAD,'Intensité Electrique', 'A',false),
    'tension_electrique' => array(CHAMP_TRAD,'Tension éléctrique', 'V', false),
    'frequence' => array(CHAMP_TRAD,'Fréquence', 'Hz',false),
    'class_energetique' => array(CHAMP_LISTE_EN,'Classe énergétique', false,false),
	  
	),
	'plan_de_travail'	=> array(
	  'nb_plan_travail'		=> array(CHAMP_TRAD, 'Plan(s) de travail','',false,true),
	),
	'dimensions' => array(
      'hauteur' => array(CHAMP_NUMERIC,'Hauteur', 'mm'),
      'largeur' => array(CHAMP_NUMERIC,'Largeur', 'mm'),
      'profondeur' => array(CHAMP_NUMERIC,'Profondeur', 'mm'),
      'poids' => array(CHAMP_NUMERIC,'Poids', 'g'),
      'largeur_surface' => array(CHAMP_NUMERIC,'Largeur surface de cuisson', 'mm'),
      'profondeur_surface' => array(CHAMP_NUMERIC,'Profondeur surface de cuisson', 'mm'),
      'surface_de_cuisson' => array(CHAMP_NUMERIC,'Surface de cuisson', 'Cm²'),
      'largeur_plan' => array(CHAMP_NUMERIC,'Largeur plan de travail 1', 'mm'),
      'profondeur_plan' => array(CHAMP_NUMERIC,'Profondeur plan de travail 1', 'mm'),
      'largeur_plan2' => array(CHAMP_NUMERIC,'Largeur plan de travail 2', 'mm'),
      'profondeur_plan2' => array(CHAMP_NUMERIC,'Profondeur plan de travail 2', 'mm'),
      'largeur_plan3' => array(CHAMP_NUMERIC,'Largeur plan de travail 3', 'mm'),
      'profondeur_plan3' => array(CHAMP_NUMERIC,'Profondeur plan de travail 3', 'mm'),
      'surface_plan' => array(CHAMP_NUMERIC,'Surface plan de travail', 'Cm²'),
    ),
	'coloris' => array(
	      'couleur' => array(CHAMP_MULTI,'Couleur', '',false),
	      'couleur_fab' => array(CHAMP_TRAD,'Couleur fabricant', ''),
	    'matiere' => array(CHAMP_TRAD,'Matière', ''),
	    )
      )
    );
  }

  function article_bbq_show($tab, $title) {
    article_global_show($tab, $title, true); // true = champs groupés
  }
  function article_bbq_update($tab, $title) {
    article_global_update($tab, $title, true); // true = champs groupés
  }
?>