<?php



function article_froidmcav_fields() {
    return array(
	array(
	    'criteres_generaux'		=> array(
		'type_refrigerateur'		=> array(CHAMP_TRAD, 'Type de réfrigérateur','',false,true),
		'type_pose'		=> array(CHAMP_TRAD, 'Type de pose','',false,true),
		'alarme'			=> array(CHAMP_MULTI, 'Alarme porte ouverte','',false,true),
		'ecran_affichage'		=> array(CHAMP_MULTI, 'Écran d\'affichage','',false,true),
		'fonction_vacances'		=> array(CHAMP_MULTI, 'Fonction vacances','',false,true),
		'autonomie'			=> array(CHAMP_MULTI, 'Autnomie en cas de coupure de courant','',false,true),
		'porte_reversible'		=> array(CHAMP_MULTI, 'Porte réversible','',false,true),
		'bar'				=> array(CHAMP_MULTI, 'Bar en façade','',false,true),
		'compartiments_independants'	=> array(CHAMP_MULTI, 'Compartiments indépendants','',false,true),
		'technologie_specifique'	=> array(CHAMP_TRAD, 'Technologie spécifique','',false,true),
	),
    'performances' => array(
    	'niveau_sonore'			=> array(CHAMP_TRAD, 'Niveau sonore','dB',false,true),
    	'puissance_totale' => array(CHAMP_NUMERIC,'Puissance totale', 'W'),
	    'intensite_electrique' => array(CHAMP_NUMERIC,'Intensité Electrique', 'A',false),
	    'classe_climatique'		=> array(CHAMP_TRAD, 'Classe climatique','',false,true),
	    'tension_electrique' => array(CHAMP_TRAD,'Tension éléctrique', 'V', false),
	    'frequence' => array(CHAMP_TRAD,'Fréquence', 'Hz',false),
	    'class_energetique' => array(CHAMP_LISTE_EN,'Classe énergétique', false,false),
    ),
    'dimensions' => array(
    	'dimensions_hlp' => array(CHAMP_TXT,'dimensions', 'HxLxP',false),
		'hauteur' => array(CHAMP_NUMERIC,'Hauteur', 'mm'),
		'largeur' => array(CHAMP_NUMERIC,'Largeur', 'mm'),
		'profondeur' => array(CHAMP_NUMERIC,'Profondeur', 'mm'),
		'poids' => array(CHAMP_NUMERIC,'Poids', 'kg'),
    ),
    'capacite' => array(
    	'volume_total' => array(CHAMP_TRAD,'Volume total','L',false) ,
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
   ) );
}

function article_froidmcav_show($tab,$title) {
    article_global_show('froidmcav',$title,true);

    echo '<h2>Réfrigérateur / congelateur / cave à vin</h2>';
    echo '<input type="button" value="Ajouter un réfrigérateur" onclick="itemaction(\''.$tab.'\',\'frigomcav_add\')">';
    echo '<input type="button" value="Ajouter un congélateur" onclick="itemaction(\''.$tab.'\',\'congelmcav_add\')">';
    echo '<input type="hidden" name="item_action" />';
    echo '<input type="hidden" name="item_id" />';
}


function article_froidmcav_update($tab,$title) {
    if($_POST['item_action']=='frigomcav_add')
	article_frigomcav_add();
    if($_POST['item_action']=='congelmcav_add')
	article_congelmcav_add();
    article_global_update('froid',$title,true);
}

?>