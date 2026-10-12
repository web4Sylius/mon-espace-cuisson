<?php
  include_once($rootpath.'class/articles/article_foyers.php');

  function article_table_cuisson_fields($typearticle) {
    $fields = array(
      array(
	'table_cuisson_caracteristiques_generales'	=> array(
	  'coloris_plaque'		=> array(CHAMP_TRAD, 'Coloris','',false,true),
	  'type_table'			=> array(CHAMP_MULTI, 'Type de table','',true,true),
	  'type_pose_table'		=> array(CHAMP_TRAD, 'Type de pose','',true,true),
	  'nature_surface'		=> array(CHAMP_TRAD, 'Nature de la surface','',false,true),
	  'nb_foyers'			=> array(CHAMP_TRAD, 'Nombre total de foyers','',true,true),
	  'type_foyers_1'		=> array(CHAMP_TRAD, 'Type de foyers 1 (table mixte uniquement)','',false,true),
	  'nb_foyers_1'			=> array(CHAMP_TRAD, 'Nombre de foyers 1 (table mixte uniquement)','',false,true),
	  'type_foyers_2'		=> array(CHAMP_TRAD, 'Type de foyers 2 (table mixte uniquement)','',false,true),
	  'nb_foyers_2'			=> array(CHAMP_TRAD, 'Nombre de foyers 2 (table mixte uniquement)','',false,true),
// 	  'type_foyer_categorie'	=> array(CHAMP_TRAD, 'Type de foyers','Critère recherche',true),
	  'type_allumage'		=> array(CHAMP_TRAD, 'Type d\'allumage','',false,true),
	  'type_grille'			=> array(CHAMP_TRAD, 'Type de grille et chapeaux de brûleurs','',false,true),
	  'type_couronnes'		=> array(CHAMP_TRAD, 'Types de couronnes','',false,true),
	  'bords'			=> array(CHAMP_TRAD, 'Bords','',false,true),
	  'classe_energie_table'	=> array(CHAMP_TRAD, 'Classe énergétique de la table','',false,true),
	  'techno_specifique_table'	=> array(CHAMP_TRAD, 'Technologie spécifique','',false,true),
	  'thermostat_table'			=> array(CHAMP_TRAD, 'Thermostat','',false,true),
	  'puissance_table'		=> array(CHAMP_NUMERIC, 'Puissance table','W',false,true)
	),
	'table_cuisson_commandes'		=> array(
	  // champ			// traduire, texte du label
	  'emplacement'			=> array(CHAMP_TRAD, 'Emplacement','',false,true),
	  'ecran_table'			=> array(CHAMP_TRAD, 'Écran','',false,true),
	  'type_cmd'			=> array(CHAMP_TRAD, 'Type de commande','',true,true),
	  'cat_cmd'			=> array(CHAMP_TRAD, 'Catégorie de commande', '(pour les critères d\'affinage)',true,true),
	  'nombre_pos_cuisson'		=> array(CHAMP_TRAD, 'Nombre de positions de cuisson','',false,true),
	  'minuteur'			=> array(CHAMP_TRAD, 'Minuteur','',false,true),
	  'interrupteur'		=> array(CHAMP_TRAD, 'Interrupteur Marche/Arrêt','',false,true),
	  'verouillage_cmd'		=> array(CHAMP_TRAD, 'Verouillage de commande','',false,true),
	),
	'table_cuisson_fonctions_specifiques'	=> array(
	  'bruleur_wok'			=> array(CHAMP_TRAD, 'Brûleur wok','',false,true),
	  'bruleur_rapide'		=> array(CHAMP_TRAD, 'Brûleur rapide','',false,true),
	  'bruleur_semi_rapide'		=> array(CHAMP_TRAD, 'Brûleur semi-rapide','',false,true),
	  'bruleur_auxiliere'		=> array(CHAMP_TRAD, 'Brûleur auxiliere','',false,true),
	  'injecteur'			=> array(CHAMP_TRAD, 'Injecteur','',false,true),
	  'plancha'			=> array(CHAMP_TRAD, 'Plancha','',false,true),
	  'tapanaki'			=> array(CHAMP_TRAD, 'Teppanyaki','',false,true),
	  'chauffe_plat'		=> array(CHAMP_TRAD, 'Chauffe-plat','',false,true),
	  'memo_programme'		=> array(CHAMP_TRAD, 'Fonction de mémorisation des programmes','',false,true),
	  'support_wok'			=> array(CHAMP_TRAD, 'Support wok','',false,true),
	  'maintien_chaud'		=> array(CHAMP_TRAD, 'Fonction maintien chaud','',false,true),
	  'fonction_pause'		=> array(CHAMP_TRAD, 'Fonction pause Stop & Go','',false,true),
	  'fonction_autre'		=> array(CHAMP_TRAD, 'Autre fonction','',false,true),
	  'fonction_autre2'		=> array(CHAMP_TRAD, 'Autre fonction','',false,true)
	),
	'table_cuisson_equipement' => array(
	  'equipement_table'		=> array(CHAMP_TRAD, 'Équipement','',false,true),
	),
	'securite' => array(
	  'securite'			=> array(CHAMP_TRAD, 'Sécurité','',false,true),
	  'sonde_temp'			=> array(CHAMP_TRAD, 'Sonde de température','',false,true),
	  'turbine'			=> array(CHAMP_TRAD, 'Turbine de ventillation','',false,true),
	  'securite_enfants'		=> array(CHAMP_TRAD, 'Sécurité enfants','',false,true),
	  'temoin_chaleur_res'		=> array(CHAMP_TRAD, 'Témoin de chaleur résiduelle','',false,true),
	  'securite_anti_surchauffe'	=> array(CHAMP_TRAD, 'Sécurité anti-surchauffe','',false,true),
	  'securite_anti_deborde'	=> array(CHAMP_TRAD, 'Sécurité anti-débordement','',false,true),
	  'detect_objets'		=> array(CHAMP_TRAD, 'Détection petits objets','',false,true),
	  'detect_casserole'		=> array(CHAMP_TRAD, 'Détection casserole','',false,true),
	  'securite_arret_auto'		=> array(CHAMP_TRAD, 'Arrêt automatique','',false,true),
	  'securite_faible_intensite'	=> array(CHAMP_TRAD, 'Détecteur faible intensité','',false,true),
	  'securite_mauvaise_connexion' => array(CHAMP_TRAD, 'Détection mauvaise connexion','',false,true),
	),
      ),
    );

    //Champs cachés pour les piano et cuisinières
    if ($typearticle == 1 || $typearticle == 2) {
	unset($fields[0]['table_cuisson_caracteristiques_generales']['techno_specifique_table']);
	unset($fields[0]['table_cuisson_commandes']['cat_cmd']);
	unset($fields[0]['table_cuisson_caracteristiques_generales']['type_pose_table']);
    }
    if ($typearticle == 1 || $typearticle == 2 || $typearticle == 3) {
	unset($fields[0]['table_cuisson_caracteristiques_generales']['type_foyers_1']);
	unset($fields[0]['table_cuisson_caracteristiques_generales']['type_foyers_2']);
	unset($fields[0]['table_cuisson_caracteristiques_generales']['nb_foyers_1']);
	unset($fields[0]['table_cuisson_caracteristiques_generales']['nb_foyers_2']);
	unset($fields[0]['table_cuisson_caracteristiques_generales']['type_allumage']);
	unset($fields[0]['table_cuisson_caracteristiques_generales']['type_grille']);
	unset($fields[0]['table_cuisson_caracteristiques_generales']['type_couronnes']);
	unset($fields[0]['table_cuisson_commandes']['nombre_pos_cuisson']);
	unset($fields[0]['table_cuisson_fonctions_specifiques']['bruleur_rapide']);
	unset($fields[0]['table_cuisson_fonctions_specifiques']['bruleur_semi_rapide']);
	unset($fields[0]['table_cuisson_fonctions_specifiques']['bruleur_auxiliere']);
	unset($fields[0]['table_cuisson_fonctions_specifiques']['injecteur']);
    }
    if ($typearticle == 3) { // Plaque simple
      unset($fields[0]['table_cuisson_caracteristiques_generales']['coloris_plaque']);
      unset($fields[0]['table_cuisson_caracteristiques_generales']['classe_energie_table']);
      unset($fields[0]['table_cuisson_caracteristiques_generales']['type_foyer_categorie']);
      unset($fields[0]['table_cuisson_caracteristiques_generales']['puissance_table']);
    }

    return ($fields);
  }

  function article_table_cuisson_show($tab, $title, $id) {
        global $artid, $catid, $DB_site2, $erreur, $old, $typearticle, $template;
    echo <<<EOL
    <h3>$title</h3>
    <div class="cost">$erreur</div>
    <form name="formu" action="ajout_produit.php" method="post">
    <input type="hidden" name="old" value="">
    <input type="hidden" name="quitter" value="">
    <input type="hidden" name="mq2" value="">
    <input type="hidden" name="action2" value="$old">
    <input type="hidden" name="modif" value="0">
    <input type="hidden" name="artid" value="$artid">
    <input type="hidden" name="catid" value="$catid">
EOL;
    $query = "SELECT * FROM `article_table_cuisson` WHERE `id` = '$id'";
    $values = $DB_site2->fetch_array($DB_site2->query($query));

    $article_fields = article_table_cuisson_fields($typearticle);
    foreach($article_fields as $type => $groups) {
      if (!empty($type))
	echo "<fieldset><legend>$type :</legend>";

      foreach($groups as $group => $fields) {
	if (!empty($group))
	  echo '<h2>'.$template->translate('/article/champs/'.$group,'F').'</h2>';
	// Affichage des champs
	echo '<table width="100%">';
	foreach($fields as $field => $prop) {
	  list($tr, $label, $suffix, $req,$info) = $prop;
	  $value = htmlentities($values[$field],ENT_COMPAT,"ISO-8859-15");
	  $label = $label.(($req)?'<span class="obligatoire">*</span>':'')." : ";
	  $label.=($suffix!=""?"<br><small>(".$suffix.")</small>":"");
	  echo '<tr><td width="200">';
	  echo '<label for="'.$field.'">'.$label.'</label></td><td>';
	  echo selecttranslate($field,"/article/$field", html_entity_decode($value,ENT_COMPAT,"ISO-8859-15"),$tr,null,$info,$id);
	  echo '</td></tr>';
	}
	echo '</table>';
      }

      if (!empty($type))
	echo "</fieldset>";
    }
    echo '<fieldset id="iaction"><legend>Foyers :</legend>';
    article_foyers_show($tab, $id);
    echo '<input type="hidden" name="item_action" />';
    echo '<input type="hidden" name="item_id" />';
    echo '<input type="button" name="foyer_add" '.
	 'value="Ajouter un foyer" '.
	 "onclick=\"javascript:itemaction('$tab','foyer_add')\" />";
    echo '</fieldset>';
    echo '<input type="hidden" name="table_id" value="'.$id.'" />';
    if ($typearticle != 3) // Si ce n'est pas une table simple
      echo '<input type="button" value="Supprimer cette table de cuisson"'.
	 " onclick=\"javascript:itemaction('$tab','table_del', $id,'Supprimer la table \'$title\' ?')\" />";
  }
  
  function article_table_cuisson_update($tab, $title, $id) {
    global $DB_site2, $typearticle, $artid;
     
     //On vide les enregistrements pour qu'ils soient correctement recréés
     $sql = "UPDATE article_i SET";
     $sql .= " value = ''";
     $sql .= " WHERE artid=$artid";
     $sql .= " AND tabid='$id'";
     $DB_site2->query($sql);
           
    if ($_POST['item_action'] == 'table_del')
      return "Suppression ".(
	(article_table_cuisson_delete($_POST['item_id']))? "réussie" : "échouée");
    if (!is_numeric($id))
      return false;
    $article_fields = article_table_cuisson_fields($typearticle);
    
    $query = "UPDATE `article_table_cuisson` SET";

    foreach($article_fields as $type => $groups)
      foreach($groups as $group => $fields)
	foreach($fields as $field => $prop) {
	  list($tr, $label, $suffix, $req, $info) = $prop;
	  $value = mysql_real_escape_string(trim($_POST[$field]));
	  if ($req && empty($value))
	    $erreur .= "<p>Le champ \"$label\" est obligatoire.</p>";
	  $query .= " `$field` = '$value',";

	  
	  if($info){
	      switch($tr){
	      case CHAMP_NUMERIC:
	      case CHAMP_TRAD:
                article_update_chk($field,isset($_POST[$field."_chk"]),$id);
	      article_update_i($field,$_POST[$field."_i"],$id);
                article_update_chk($field."_i",isset($_POST[$field."_i_chk"]),$id);
	      break;
	      
	      case CHAMP_MULTI:
	      $exvalues=explode(",",$value);
	      foreach($exvalues as $exvalue){
		  $chkname=$field."_".substr(strrchr($exvalue,"/"),1);
                      article_update_chk($chkname,isset($_POST[$chkname."_chk"]),$id);
		  article_update_i($chkname,$_POST[$chkname."_i"],$id);
                      article_update_chk($chkname."_i",isset($_POST[$chkname."_i_chk"]),$id);
	      }
	      break;
	     }
	     
	  }
	}

//     if (!empty($erreur))
//       return ($erreur);
    if ($new_entry)
      $query .= " `id` = $id";
    else {
      $query = substr($query, 0, -1); // retrait de la dernière virgule
      $query .= " WHERE id=$id";
    }
    $DB_site2->query($query);
    modifhistory($_POST);
    article_foyers_update($_POST['item_action'], $id,  $_POST['item_id']);
  }

  function article_table_cuisson_count() {
    global $DB_site2, $artid;

    $count = $DB_site2->query_first(
      "SELECT COUNT(`artid`)".
      "FROM `article_table_cuisson`".
      "WHERE `artid` = $artid"
    );
    return($count[0]);
  }

  function article_table_cuisson_add() {
    global $DB_site2, $artid;

    $query = "INSERT INTO `article_table_cuisson` SET `artid` = '$artid'";
    return $DB_site2->query($query);
  }

  function article_table_cuisson_delete($id) {
    global $DB_site2, $artid;

    if (is_null($id) || !is_numeric($id))
      return (false);

    $query = "DELETE FROM `article_foyers` WHERE `table_id` = '$id'";
    $DB_site2->query($query);
    $query = "DELETE FROM `article_table_cuisson` WHERE `id` = '$id'";
    return $DB_site2->query($query);
  }
  
     /*
      * Retourne des informations sur la table de cuisson
      * @params int $artid Identifiant du produit
      * @params array $champs Liste des champs à retourner
      * 
      * @return array $tab_infos Tableau contenant les informations souhaitées
      */
     function get_infos($artid, $champs = array()){
          
          if(!empty($champs)){
               if(!is_array($champs)){
                    $champs = array($champs);
               }
               $liste_champs = implode(',', $champs);
               $liste_champs = str_replace(' ','',$liste_champs);
          } else {
               $champs = '*';
          }
          
          $sql = "SELECT ".$champs;
          $sql .= " FROM article_table_cuisson";
          $sql .= " WHERE artid = ".$artid;
          $tab_infos = $DB_site->query_first($sql);
          
          return $tab_infos;
     }

?>