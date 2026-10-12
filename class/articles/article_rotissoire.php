<?php
function article_rotissoire_fields($typearticle) {
    $fields =  array(
      'Commun' => array(
   'criteres_generaux'  => array(
    'type_pose'=>array(CHAMP_TRAD,'Type de pose','',true,false),
    'type_foyer'=>array(CHAMP_TRAD, 'Type de foyer', '', true, false),
    'nb_foyer'=>array(CHAMP_NUMERIC, 'Nombre de foyers', '', true, false),
   ),
   'bandeau_commandes'  => array(
    'type_cmd'      => array(CHAMP_TRAD, 'Type de commande','',true,true),
    ),
    'securite'  => array(
    'securite'   => array(CHAMP_TRAD, 'Securité','',false,false),
  ), 
	'performances'		=> array(
	  'puissance_totale' => array(CHAMP_NUMERIC,'Puissance de la rôtissoire', 'W'),
    'intensite_electrique' => array(CHAMP_TRAD,'Intensité Electrique', 'A',false),
    'tension_electrique' => array(CHAMP_TRAD,'Tension éléctrique', 'V', false),
    'frequence' => array(CHAMP_TRAD,'Fréquence', 'Hz',false),
    'class_energetique' => array(CHAMP_LISTE_EN,'Classe énergétique', false,false),
	  
	),
  'dimensions' => array(
      'hauteur' => array(CHAMP_NUMERIC,'Hauteur de la rôtissoire', 'mm'),
      'largeur' => array(CHAMP_NUMERIC,'Largeur de la rôtissoire', 'mm'),
      'profondeur' => array(CHAMP_NUMERIC,'Profondeur de la rôtissoire', 'mm'),
      'poids' => array(CHAMP_NUMERIC,'Poids', 'g'),
      'largeur_utile' => array(CHAMP_NUMERIC,'Largeur utile du foyer', 'mm'),
    ),
  'coloris' => array(
        'couleur' => array(CHAMP_MULTI,'Couleur', '',false),
        'couleur_fab' => array(CHAMP_TRAD,'Couleur fabricant', ''),
      'matiere' => array(CHAMP_TRAD,'Matière', ''),
      )

   ));

      return ($fields);
  }
  
   function article_rotissoire_show($tab, $title) {
    article_global_show($tab, $title, true); // true = champs groupés
  }
  function article_rotissoire_update($tab, $title) {
    article_global_update($tab, $title, true); // true = champs groupés
  }
?>