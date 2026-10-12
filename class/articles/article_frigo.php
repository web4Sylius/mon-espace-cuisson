<?php

function article_frigo_fields($typearticle=21){
    $fields = array(
	'Commun' => array(
	      'criteres_generaux'	=> array(
		'position_frigo'		=> array(CHAMP_TRAD, 'Position','',false,true),
		'type_pose'				=> array(CHAMP_TRAD, 'Type de pose','',false,true),
		'version_porte'			=> array(CHAMP_TRAD, 'Type d\'ouverture','',false,true),
		'type_froid_frigo'		=> array(CHAMP_TRAD, 'Type de froid','',false,true),
		'degivrage_auto'		=> array(CHAMP_MULTI, 'Dégivrage automatique','',false,true),
		'nofrost'				=> array(CHAMP_MULTI, 'No Frost','',false,true),
		'refrigeration_rapide'	=> array(CHAMP_MULTI, 'Réfrigération rapide','',false,true),
		'clayettes'				=> array(CHAMP_TRAD, 'Clayettes','',false,true),
		'compartiment'			=> array(CHAMP_TRAD, 'Compartiment','',false,true),
		'balconnet'				=> array(CHAMP_TRAD, 'Nombre de balconnets','',false,true),
		'bac_glacons'			=> array(CHAMP_MULTI, 'Bac à glaçons','',false,true),
	      ),
	      'capacite' => array(
	      	'volume_cavite'			=> array(CHAMP_NUMERIC, 'Volume','L',false,true),
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
	    ),
	    'performances' => array(
        'niveau_sonore'         => array(CHAMP_TRAD, 'Niveau sonore','dB',false,true),
        'puissance_totale' => array(CHAMP_NUMERIC,'Puissance totale', 'W'),
        'intensite_electrique' => array(CHAMP_TRAD,'Intensité Electrique', 'A',false),
        'classe_climatique'     => array(CHAMP_TRAD, 'Classe climatique','',false,true),
        'tension_electrique' => array(CHAMP_TRAD,'Tension éléctrique', 'V', false),
        'frequence' => array(CHAMP_TRAD,'Fréquence', 'Hz',false),
        'cordon_alim'=>array(CHAMP_TRAD,'Cordon d\'alimentation','',false),
        'class_energetique' => array(CHAMP_LISTE_EN,'Classe énergétique', false,false),
    )
	)
    );

    if ($typearticle == 26) {
    	unset($fields['Commun']['criteres_generaux']['type_pose']);
    	unset($fields['Commun']['dimensions']);
    	unset($fields['Commun']['dimensions_encastrement']);
    	unset($fields['Commun']['coloris']);

    }else{
    	unset($fields['Commun']['criteres_generaux']['position_frigo']);
    }

    return $fields;
}

function article_frigo_show($tab, $title) {
    article_global_show($tab, $title, true); // true = champs groupés
  }
  function article_frigo_update($tab, $title) {
    article_global_update($tab, $title, true); // true = champs groupés
  }

?>