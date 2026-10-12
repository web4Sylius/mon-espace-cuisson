<?php
function article_cellule_refroidissement_fields($typearticle) {
    $fields =  array(
      'Commun' => array(
	'criteres_generaux'	=> array(
	  // champ			// traduire, texte du label
	  'type_pose'			=> array(CHAMP_TRAD, 'Type de pose','',true,true),
	  'fermeture_douce'			=> array(CHAMP_TRAD, 'Fermeture douce','',true,true),
	  'type_ouverture'		=> array(CHAMP_TRAD, 'Type d\'ouverture','',true,true),
	  
	),
	'performances'		=> array(
		'thermostat'		=> array(CHAMP_TRAD, 'Plage de température', 'W',false,false),
	  'puissance_totale' => array(CHAMP_NUMERIC,'Puissance totale', 'W'),
    'intensite_electrique' => array(CHAMP_NUMERIC,'Intensité Electrique', 'A',false),
    'tension_electrique' => array(CHAMP_TRAD,'Tension éléctrique', 'V', false),
    'frequence' => array(CHAMP_TRAD,'Fréquence', 'Hz',false),
    'cordon_alim'=>array(CHAMP_TRAD,'Cordon d\'alimentation','',false),
    'class_energetique' => array(CHAMP_LISTE_EN,'Classe énergétique', false,false),
	  
	),
	'dimensions' => array(
      'hauteur' => array(CHAMP_NUMERIC,'Hauteur', 'mm'),
      'largeur' => array(CHAMP_NUMERIC,'Largeur', 'mm'),
      'profondeur' => array(CHAMP_NUMERIC,'Profondeur', 'mm'),
      'poids' => array(CHAMP_NUMERIC,'Poids', 'g'),
      'hauteur_cavite' => array(CHAMP_NUMERIC,'Hauteur cavité', 'mm'),
      'largeur_cavite' => array(CHAMP_NUMERIC,'Largeur cavité', 'mm'),
      'profondeur_cavite' => array(CHAMP_NUMERIC,'Profondeur cavité', 'mm'),
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

      return ($fields);
  }

  function article_cellule_refroidissement_show($tab, $title) {
    article_global_show($tab, $title, true); // true = champs groupés
  }
  function article_cellule_refroidissement_update($tab, $title) {
    article_global_update($tab, $title, true); // true = champs groupés
  }





?>