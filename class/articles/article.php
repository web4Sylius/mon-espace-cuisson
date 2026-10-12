<?php

// type de champs

define('CHAMP_NUMERIC',0);	// chiffres seulement
define('CHAMP_TRAD',1);		// texte traduit
define('CHAMP_TEXT',2);		// texte non traduit
define('CHAMP_MULTI',3);	// multi critères traduit


  function article_fields() {
    return array(
      array(
	/*'performances'		=> array(
	  'puissance_totale'		=> array(CHAMP_NUMERIC, 'Puissance totale', 'W'),
	  'conso_gaz_totale'		=> array(CHAMP_NUMERIC, 'Puissance gaz totale', 'W'),
	  'puissance_electrique'	=> array(CHAMP_NUMERIC, 'Puissance électrique totale', 'W'),
	  'intensite_electrique'	=> array(CHAMP_TRAD, 'Intensité électrique', 'A'),
	  'energy_label'		=> array(CHAMP_TRAD, 'Classe énergétique'),
	  'cordon_alim'			=> array(CHAMP_TRAD, 'Cordon d\'alimentation'),
	  'tension_electrique'		=> array(CHAMP_TRAD, 'Tension éléctrique', 'V'),
	  'frequence'			=> array(CHAMP_TRAD, 'Fréquence', 'Hz'),
	  'branchements'		=> array(CHAMP_NUMERIC, 'Branchements'),
	),*/
	'dim_brutes_col1'	=> array(
	  'emballage_hauteur1'		=> array(CHAMP_NUMERIC, 'Hauteur', 'mm'),
	  'emballage_largeur1'		=> array(CHAMP_NUMERIC, 'Largeur', 'mm'),
	  'emballage_profondeur1'	=> array(CHAMP_NUMERIC, 'Profondeur', 'mm'),
	  'emballage_poids1'		=> array(CHAMP_NUMERIC, 'Poids', 'kg'),
	),
	'dim_brutes_col2'	=> array(
	  'emballage_hauteur2'		=> array(CHAMP_NUMERIC, 'Hauteur', 'mm'),
	  'emballage_largeur2'		=> array(CHAMP_NUMERIC, 'Largeur', 'mm'),
	  'emballage_profondeur2'	=> array(CHAMP_NUMERIC, 'Profondeur', 'mm'),
	'emballage_poids2'		=> array(CHAMP_NUMERIC, 'Poids', 'kg'),
	),
	'dim_brutes_col3'	=> array(
	  'emballage_hauteur3'		=> array(CHAMP_NUMERIC, 'Hauteur', 'mm'),
	  'emballage_largeur3'		=> array(CHAMP_NUMERIC, 'Largeur', 'mm'),
	  'emballage_profondeur3'	=> array(CHAMP_NUMERIC, 'Profondeur', 'mm'),
	  'emballage_poids3'		=> array(CHAMP_NUMERIC, 'Poids', 'kg'),
	),
      )
    );
  }
  
/*
      * Retourne des informations sur la table
      * @params string $table Nom de la table ou faire la recherche
      * @params int $artid Identifiant du produit
      * @params array $champs Liste des champs à retourner
      * 
      * @return array $tab_infos Tableau contenant les informations souhaitées
      */
     function get_infos($table, $artid, $champs = array()){
          global $DB_site;
          if(!empty($champs)){
               if(!is_array($champs)){
                    $champs = array($champs);
               }
               $liste_champs = implode(',', $champs);
               $liste_champs = str_replace(' ','',$liste_champs);
          } else {
               $liste_champs = '*';
          }
          
          $sql = "SELECT ".$liste_champs;
          $sql .= " FROM ".$table;
          $sql .= " WHERE artid = ".$artid;
          $tab_infos = $DB_site->query_first($sql);
          
          return clean_tab($tab_infos);
     }
     
     function get_nb_foyers($id){
          global $DB_site;
          
          $sql = "SELECT COUNT(id) as nb";
          $sql .= " FROM article_foyers";
          $sql .= " WHERE table_id = ".$id;
          $tab_infos = $DB_site->query_first($sql);
          
          return $tab_infos['nb'];
     }
     
     function get_infos_finitions($artid){
          global $DB_site;
          
          $sql = "SELECT rf.nom";
          $sql .= " FROM articlerecherche_finition ar";
          $sql .= " LEFT JOIN recherche_finition rf ON (rf.finitionid = ar.finitionid)";
          $sql .= " WHERE ar.artid = ".$artid;
          $sql .= " GROUP BY ar.finitionid";
          $sql .= " ORDER BY ar.finitionid";
          $article_finition = $DB_site->query($sql);
          if($DB_site->num_rows($article_finition) > 0){
               while($la_finition = $DB_site->fetch_array($article_finition)){
                    $tab_retour[] = clean_tab($la_finition);
               }
               
               return $tab_retour;
          } else {
               return array();
          }
     }
     
     function get_type_fours($artid){
          global $DB_site;
          
          $sql = "SELECT type_four";
          $sql .= " FROM article_four";
          $sql .= " WHERE artid = ".$artid;
          $sql .= " ORDER BY id ASC";
          $article_type_four = $DB_site->query($sql);
          if($DB_site->num_rows($article_type_four) > 0){
               while($le_type = $DB_site->fetch_array($article_type_four)){
                    $tab_retour[] = clean_tab($le_type);
               }
               
               return $tab_retour;
          } else {
               return array();
          }
     }
     

?>