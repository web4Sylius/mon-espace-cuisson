<?php
  function article_evier_fields() {
    return array(
      array(
	'criteres_generaux'	=> array(
	  'type_accessoire'		=> array(CHAMP_TRAD, 'Type','',true,true)
	),
  'dimensions' => array(
      'hauteur_evier' => array(CHAMP_NUMERIC,'Hauteur évier', 'mm'),
      'largeur_evier' => array(CHAMP_NUMERIC,'Largeur évier', 'mm'),
      'profondeur_evier' => array(CHAMP_NUMERIC,'Profondeur évier', 'mm'),
      'poids_evier' => array(CHAMP_NUMERIC,'Poids évier', 'kg'),
      'hauteur_robinet' => array(CHAMP_NUMERIC,'Hauteur robinet', 'mm'),
      'largeur_robinet' => array(CHAMP_NUMERIC,'Largeur robinet', 'mm'),
      'profondeur_robinet' => array(CHAMP_NUMERIC,'Profondeur robinet', 'mm'),
      'poids_robinet' => array(CHAMP_NUMERIC,'Poids robinet', 'g'),
      'hauteur_cuve' => array(CHAMP_NUMERIC,'Hauteur cuve', 'mm'),
      'largeur_cuve' => array(CHAMP_NUMERIC,'Largeur cuve', 'mm'),
      'profondeur_cuve' => array(CHAMP_NUMERIC,'Profondeur cuve', 'kg'),
    ),
  'capacite' => array(
      'volume_cavite' => array(CHAMP_NUMERIC,'Volume cavité', 'L'),
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
    
    ));
  }

  function article_evier_show($tab, $title) {
    article_global_show($tab, $title, true); // CHAMP_TRAD = champs groupés
  }
  function article_evier_update($tab, $title) {
    article_global_update($tab, $title, true); // CHAMP_TRAD = champs groupés
  }

?>