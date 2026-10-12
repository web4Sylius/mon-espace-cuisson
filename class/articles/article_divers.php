<?php
  function article_divers_fields() {
    return array(
      array(
	'criteres_generaux'	=> array(
	  // champ			// traduire, texte du label
	  'type_accessoire'		=> array(CHAMP_TRAD, 'Type','',true,true),
	),
  'coloris' => array(
      'couleur' => array(CHAMP_MULTI,'Couleur', '',false),
      'couleur_fab' => array(CHAMP_TRAD,'Couleur fabricant', ''),
    'matiere' => array(CHAMP_TRAD,'Matière', ''),
    ),
  
  'dimensions' => array(
    'hauteur' => array(CHAMP_NUMERIC,'Hauteur', 'mm'),
    'largeur' => array(CHAMP_NUMERIC,'Largeur', 'mm'),
    'profondeur' => array(CHAMP_NUMERIC,'Profondeur', 'mm'),
    'diametre' => array(CHAMP_NUMERIC,'Diamètre', 'mm'),
    'poids' => array(CHAMP_NUMERIC,'Poids', 'g'),
    ),
    'dimensions_encastrement' => array(
      'hauteur_encastrement' => array(CHAMP_NUMERIC,'Hauteur', 'mm'),
      'largeur_encastrement' => array(CHAMP_NUMERIC,'Largeur', 'mm'),
      'profondeur_encastrement' => array(CHAMP_NUMERIC, 'Profondeur', 'mm')
    ),
    'performances'    => array(
      'puissance_totale' => array(CHAMP_NUMERIC,'Puissance totale', 'W'),
      'intensite_electrique' => array(CHAMP_TRAD,'Intensité Electrique', 'A',false),
      'tension_electrique' => array(CHAMP_TRAD,'Tension éléctrique', 'V', false),
      'frequence' => array(CHAMP_TRAD,'Fréquence', 'Hz',false),
      'class_energetique' => array(CHAMP_LISTE_EN,'Classe énergétique', false,false), 
    )
      )
    );
  }

  function article_divers_show($tab, $title) {
    article_global_show($tab, $title, true); // CHAMP_TRAD = champs groupés
  }
  function article_divers_update($tab, $title) {
    article_global_update($tab, $title, true); // CHAMP_TRAD = champs groupés
  }

?>