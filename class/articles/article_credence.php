<?php
  function article_credence_fields() {
    return array(
      array(
	'criteres_generaux'	=> array(
	  // champ			// traduire, texte du label
	  'porte_ustensiles'		=> array(CHAMP_MULTI, 'Porte ustensiles','',true,true),
	),
  'dimensions' => array(
      'hauteur' => array(CHAMP_NUMERIC,'Hauteur', 'mm'),
      'largeur' => array(CHAMP_NUMERIC,'Largeur', 'mm'),
      'profondeur' => array(CHAMP_NUMERIC,'Profondeur', 'mm'),
      'poids' => array(CHAMP_NUMERIC,'Poids', 'g'),
    ),
  'coloris' => array(
      'couleur' => array(CHAMP_MULTI,'Couleur', '',false),
      'couleur_fab' => array(CHAMP_TRAD,'Couleur fabricant', ''),
    'matiere' => array(CHAMP_TRAD,'Matière', ''),
    )
      ));
  }

  function article_credence_show($tab, $title) {
    article_global_show($tab, $title, true); // CHAMP_TRAD = champs groupés
  }
  function article_credence_update($tab, $title) {
    article_global_update($tab, $title, true); // CHAMP_TRAD = champs groupés
  }

?>