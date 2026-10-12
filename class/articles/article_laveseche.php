<?php

function article_laveseche_fields(){
    return array(
	'Commun' => array(
	      'criteres_generaux'	=> array(
	      	'type_lavelinge'		=> array(CHAMP_MULTI,'Type de lavelinge','',false,true),
	      	  'type_pose'			=> array(CHAMP_TRAD,'Type de pose','',false,true),
			  'commandes_lvs'		=> array(CHAMP_TRAD,'Commandes','',false,true),
			  'depart_differe'		=> array(CHAMP_MULTI, 'Départ différé','',false,true),
			  'affichage_lvs'		=> array(CHAMP_TRAD, 'Affichage','',false,true),
			  'affichage_programme'	=> array(CHAMP_MULTI, 'Affichage déroulement programme','',false,true),
	      ),
	       
            'lavage'			=> array(
		  	'programmes_nb'		=> array(CHAMP_NUMERIC, 'Nombres de programmes','',false,true),
	      ),
	      'securite'		=> array(
			'antidebordement'		=> array(CHAMP_MULTI, 'Sécurité anti débordement','',false,true),
			'antifuite'			=> array(CHAMP_MULTI, 'Sécurité anti fuite','',false,true),
			'antimousse'			=> array(CHAMP_MULTI, 'Sécurité anti mousse','',false,true)
	      ),
	      'fonctionnement'		=> array(
			  'raccordement'		=> array(CHAMP_TRAD, 'Raccordement','',false,true)
	      ),
	      'performances'			=> array(
			  
	            'niveau_sonore_lvs'		=> array(CHAMP_NUMERIC, 'Niveau sonore','dB',false,true),
	            'consommation_eau_lvs'		=> array(CHAMP_NUMERIC, 'Consommation eau','L',false,true),
	            'puissance_totale' 		=> array(CHAMP_NUMERIC,'Puissance totale', 'W'),
			  'intensite_electrique' 	=> array(CHAMP_TRAD,'Intensité Electrique', 'A',false),
			  'tension_electrique' 		=> array(CHAMP_TRAD,'Tension éléctrique', 'V', false),
			  'frequence' 				=> array(CHAMP_TRAD,'Fréquence', 'Hz',false),
			  'classe_energetique'	=> array(CHAMP_TRAD, 'Classe énergétique','',false,true),
	      ),
	      'dimensions' => array(
		      'hauteur' => array(CHAMP_NUMERIC,'Hauteur', 'mm'),
		      'largeur' => array(CHAMP_NUMERIC,'Largeur', 'mm'),
		      'profondeur' => array(CHAMP_NUMERIC,'Profondeur', 'mm'),
		      'poids' => array(CHAMP_NUMERIC,'Poids', 'g'),
		 ),
		 'capacite' => array(
		 	'capacite_lavage' => array(CHAMP_NUMERIC,'Capacité de lavage', 'Kg'),
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

function article_laveseche_show($tab, $title) {
     article_global_show($tab, $title, true, 'laveseche'); // true = champs groupés
}

function article_laveseche_update($tab, $title) {
     article_global_update($tab, $title, true, 'laveseche'); // true = champs groupés
}
  
/*
 * Permet l'ajout d'un lave vaisselle associé à l'article en cours (une cuisinière par exemple)
 */  
function article_laveseche_add() {
     global $DB_site2, $artid;

     $query = "INSERT INTO `article_laveseche` SET `artid` = '$artid'";
     return $DB_site2->query($query);
}

/*
 * Permet de supprimer un lave vaisselle associé à l'article en cours
 * Params:
 * (int) $laveseche_id : Identifiant du lave vaisselle a supprimer
 */
function article_laveseche_delete($laveseche_id) {
     global $DB_site2;

     if (is_null($laveseche_id) || !is_numeric($laveseche_id)){
          return (false);
     }

     $query = "DELETE FROM `article_laveseche` WHERE `id` = '$laveseche_id'";
     return $DB_site2->query($query);
}

/*
 * Permet de compter le nombre de lave vaisselle associés à un article
 */
function article_laveseche_count() {
     global $DB_site2, $artid;

     $count = $DB_site2->query_first(
       "SELECT COUNT(`artid`)".
       "FROM `article_laveseche`".
       "WHERE `artid` = $artid"
     );
     
     return($count[0]);
}