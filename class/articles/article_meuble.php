<?php
  function article_meuble_fields() {
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
      )
    );
  }

  function article_meuble_show($tab, $title) {
    article_global_show($tab, $title, true); // CHAMP_TRAD = champs groupés
  }
  function article_meuble_update($tab, $title) {
    article_global_update($tab, $title, true); // CHAMP_TRAD = champs groupés
  }

?>