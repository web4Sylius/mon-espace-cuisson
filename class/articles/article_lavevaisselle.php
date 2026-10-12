<?php

function article_lavevaisselle_fields(){
    return array(
	'Commun' => array(
	      'criteres_generaux'	=> array(
	      	'type_pose'		=> array(CHAMP_TRAD,'Type de pose','',false,true),
			  'commandes_lv'		=> array(CHAMP_TRAD,'Commandes','',false,true),
			  'depart_differe'		=> array(CHAMP_MULTI, 'Départ différé','',false,true),
			  'affichage_lv'		=> array(CHAMP_TRAD, 'Affichage','',false,true),
			  'affichage_programme'	=> array(CHAMP_MULTI, 'Affichage déroulement programme','',false,true),
			  'version_porte_lv'	=> array(CHAMP_TRAD, 'Type d\'ouverture','',false,true)
	      ),
	       
            'lavage'			=> array(
		  	'programmes_nb'		=> array(CHAMP_NUMERIC, 'Nombre de programmes','',false,true),
	      ),
	      'consommation'		=> array(
		  	'conso_eau'			=> array(CHAMP_NUMERIC, 'Consommation d\'eau','L',false,true),
	      ),
	      'securite'		=> array(
			  'antidebordement'		=> array(CHAMP_MULTI, 'Sécurité anti débordement','',false,true),
			  'antifuite'			=> array(CHAMP_MULTI, 'Sécurité anti fuite','',false,true)
	      ),
	      'fonctionnement'		=> array(
			  'voyant_niveau_sel'		=> array(CHAMP_MULTI, 'Voyant niveau de sel','',false,true),
			  'voyant_niveau_rincage'	=> array(CHAMP_MULTI, 'Voyant niveau de liquide de rinçage','',false,true),
		  	  'raccordement_eau_chaude'	=> array(CHAMP_TRAD, 'Raccordement','',false,true),
	      ),
	      'performances'			=> array(
			  
	            'niveau_sonore'		=> array(CHAMP_NUMERIC, 'Niveau sonore','dB',false,true),
	            'puissance_totale' 		=> array(CHAMP_NUMERIC,'Puissance totale', 'W'),
			  'intensite_electrique' 	=> array(CHAMP_TRAD,'Intensité Electrique', 'A',false),
			  'class_energetique' 		=> array(CHAMP_LISTE_EN,'Classe énergétique', false,false),
			  'tension_electrique' 		=> array(CHAMP_TRAD,'Tension éléctrique', 'V', false),
			  'frequence' 				=> array(CHAMP_TRAD,'Fréquence', 'Hz',false),
	      ),
	      'dimensions' => array(
		'hauteur' => array(CHAMP_NUMERIC,'Hauteur', 'mm'),
		'largeur' => array(CHAMP_NUMERIC,'Largeur', 'mm'),
		'profondeur' => array(CHAMP_NUMERIC,'Profondeur', 'mm'),
		'poids' => array(CHAMP_NUMERIC,'Poids', 'g'),
    ),
    'capacite' => array(
    	'couverts' => array(CHAMP_NUMERIC,'Nombre de couverts','L',false) ,
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
	)
    );
}

function article_lavevaisselle_show($tab, $title) {
     article_global_show($tab, $title, true, 'lavevaisselle'); // true = champs groupés
}

function article_lavevaisselle_update($tab, $title) {
     article_global_update($tab, $title, true, 'lavevaisselle'); // true = champs groupés
}
  
/*
 * Permet l'ajout d'un lave vaisselle associé à l'article en cours (une cuisinière par exemple)
 */  
function article_lavevaisselle_add() {
     global $DB_site2, $artid;

     $query = "INSERT INTO `article_lavevaisselle` SET `artid` = '$artid'";
     return $DB_site2->query($query);
}

/*
 * Permet de supprimer un lave vaisselle associé à l'article en cours
 * Params:
 * (int) $lavevaisselle_id : Identifiant du lave vaisselle a supprimer
 */
function article_lavevaisselle_delete($lavevaisselle_id) {
     global $DB_site2;

     if (is_null($lavevaisselle_id) || !is_numeric($lavevaisselle_id)){
          return (false);
     }

     $query = "DELETE FROM `article_lavevaisselle` WHERE `id` = '$lavevaisselle_id'";
     return $DB_site2->query($query);
}

/*
 * Permet de compter le nombre de lave vaisselle associés à un article
 */
function article_lavevaisselle_count() {
     global $DB_site2, $artid;

     $count = $DB_site2->query_first(
       "SELECT COUNT(`artid`)".
       "FROM `article_lavevaisselle`".
       "WHERE `artid` = $artid"
     );
     
     return($count[0]);
}