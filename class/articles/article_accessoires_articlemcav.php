<?php
  function article_accessoires_articlemcav_fields() {
    return array(
      array(
  'criteres_generaux' => array(
    'type_accessoire'   => array(CHAMP_TRAD, 'Type','',true,true)
  ),
  'capacite'  => array(
    'capacite_unite'    => array(CHAMP_TRAD, 'Unité capacité','',true,true),
    'capacite'    => array(CHAMP_NUMERIC, 'Capacité','',true,true),
  ),
  'coloris' => array(
      'coloris' => array(CHAMP_TRAD,'Coloris principal', '',false),
      'matiere' => array(CHAMP_TRAD,'Matériau', ''),
    ),
  'dimensions' => array(
      'hauteur' => array(CHAMP_NUMERIC,'Hauteur nette', 'mm'),
      'largeur' => array(CHAMP_NUMERIC,'Largeur nette', 'mm'),
      'profondeur' => array(CHAMP_NUMERIC,'Profondeur nette', 'mm'),
      'poids' => array(CHAMP_NUMERIC,'Poids net', 'kg'),
    ),
  
    
    ));
  }

  function article_accessoires_articlemcav_show($tab, $title) {
    article_global_show($tab, $title, true); // CHAMP_TRAD = champs groupés
  }
  function article_accessoires_articlemcav_update($tab, $title) {
    article_global_update($tab, $title, true); // CHAMP_TRAD = champs groupés
  }

?>