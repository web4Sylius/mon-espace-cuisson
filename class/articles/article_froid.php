<?php

include_once($rootpath.'class/articles/article_frigo.php');
include_once($rootpath.'class/articles/article_congel_dependant.php');
include_once($rootpath.'class/articles/article_cave.php');

function article_froid_fields() {
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
	    'intensite_electrique' => array(CHAMP_TRAD,'Intensité Electrique', 'A',false),
	    'classe_climatique'		=> array(CHAMP_TRAD, 'Classe climatique','',false,true),
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
    ),
    'capacite' => array(
    	'volume_cavite' => array(CHAMP_NUMERIC,'Volume total','L',false) ,
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

function article_froid_show($tab,$title) {
    article_global_show('froid',$title,true);

    echo '<h2>Réfrigérateur / congelateur / cave à vin</h2>';
    echo '<input type="button" value="Ajouter un réfrigérateur" onclick="itemaction(\''.$tab.'\',\'frigo_dependant_add\')">';
    echo '<input type="button" value="Ajouter un congélateur" onclick="itemaction(\''.$tab.'\',\'congel_dependant_add\')">';
    echo '<input type="button" value="Ajouter une cave à vin" onclick="itemaction(\''.$tab.'\',\'cave_add\')">';
    echo '<input type="hidden" name="item_action" />';
    echo '<input type="hidden" name="item_id" />';
}


function article_froid_update($tab,$title) {
    if($_POST['item_action']=='frigo_dependant_add')
	article_frigo_dependant_add();
    if($_POST['item_action']=='congel_dependant_add')
	article_congel_dependant_add();
    if($_POST['item_action']=='cave_add')
	article_cave_add();
    article_global_update('froid',$title,true);
}

?>