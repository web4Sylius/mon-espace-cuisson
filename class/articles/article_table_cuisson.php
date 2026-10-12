<?php
  include_once($rootpath.'class/articles/article_foyers.php');

  function article_table_cuisson_fields($typearticle) {
    $fields = array(
      array(
	'table_cuisson_caracteristiques_generales'	=> array(
		'type_pose'			=> array(CHAMP_TRAD, 'Type de pose','',fales,true),
	  'type_table'			=> array(CHAMP_MULTI, 'Type de plaque','',false,true),
	  'nb_foyers'			=> array(CHAMP_MULTI, 'Nombre total de foyers','',false,true),
	),
	'table_cuisson_commandes'		=> array(
	  // champ			// traduire, texte du label
	  'emplacement'			=> array(CHAMP_TRAD, 'Emplacement','',false,true),
	  'type_cmd'			=> array(CHAMP_TRAD, 'Type de commande','',false,true),
	),
	'securite' => array(
	  'securite_enfants'		=> array(CHAMP_MULTI, 'Sécurité enfants','',false,true),
	  'temoin_chaleur_res'		=> array(CHAMP_MULTI, 'Témoin de chaleur résiduelle','',false,true),
	  'securite_anti_surchauffe'	=> array(CHAMP_MULTI, 'Sécurité anti-surchauffe','',false,true),
	  'securite_anti_deborde'	=> array(CHAMP_MULTI, 'Sécurité anti-débordement','',false,true),
	  'detect_casserole'		=> array(CHAMP_MULTI, 'Détection casserole','',false,true),
	  'securite_arret_auto'		=> array(CHAMP_MULTI, 'Arrêt automatique','',false,true),
	),
	'performances' => array(
	  'puissance_totale' => array(CHAMP_NUMERIC,'Puissance totale', 'W'),
    'intensite_electrique' => array(CHAMP_TRAD,'Intensité Electrique', 'A',false),
    'tension_electrique' => array(CHAMP_TRAD,'Tension éléctrique', 'V', false),
    'frequence' => array(CHAMP_TRAD,'Fréquence', 'Hz',false),
    'class_energetique' => array(CHAMP_LISTE_EN,'Classe énergétique', false,false),
	),
	'dimensions' => array(
		  'hauteur' => array(CHAMP_NUMERIC,'Hauteur', 'mm'),
		  'largeur' => array(CHAMP_NUMERIC,'Largeur', 'mm'),
		  'profondeur' => array(CHAMP_NUMERIC,'Profondeur', 'mm'),
		  'poids' => array(CHAMP_NUMERIC,'Poids', 'g'),
		 ),
	'dimensions_encastrement' => array(
		 	 'type_encastrement'   => array(CHAMP_TRAD, 'Type d\'encastrement','',false,true),
		      'hauteur_encastrement' => array(CHAMP_NUMERIC,'Hauteur', 'mm'),
		      'largeur_encastrement' => array(CHAMP_NUMERIC,'Largeur', 'mm'),
		      'profondeur_encastrement' => array(CHAMP_NUMERIC, 'Profondeur', 'mm'),
		      'type_encastrement1'   => array(CHAMP_TRAD, 'Type d\'encastrement','',false,true),
		      'hauteur_encastrement1' => array(CHAMP_NUMERIC,'Hauteur', 'mm'),
		      'largeur_encastrement1' => array(CHAMP_NUMERIC,'Largeur', 'mm'),
		      'profondeur_encastrement1' => array(CHAMP_NUMERIC, 'Profondeur', 'mm')
		  ),
	'coloris' => array(
      'couleur' => array(CHAMP_MULTI,'Couleur', '',false),
      'couleur_fab' => array(CHAMP_TRAD,'Couleur fabricant', ''),
    'matiere' => array(CHAMP_TRAD,'Matière', ''),
    )


      ),
    );

    if ($typearticle == 2 || $typearticle == 1) {
    		unset($fields[0]['dimensions_encastrement']);
    		unset($fields[0]['dimensions']['poids']);
    		unset($fields[0]['table_cuisson_caracteristiques_generales']['type_pose']);
    		unset($fields[0]['coloris']);

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
	  echo '<h2>'.$template->translate('/article/champs/'.$group).'</h2>';
	// Affichage des champs
	echo '<table width="100%">';
	foreach($fields as $field => $prop) {
	  list($tr, $label, $suffix, $req,$info) = $prop;
	  $value = htmlentities($values[$field],ENT_COMPAT,"UTF-8");
	  $label = $label.(($req)?'<span class="obligatoire">*</span>':'')." : ";
	  $label.=($suffix!=""?"<br><small>(".$suffix.")</small>":"");
	  echo '<tr><td width="200">';
	  echo '<label for="'.$field.'">'.$label.'</label></td><td>';
	  echo selecttranslate2($field,"/article/$field", html_entity_decode($value,ENT_COMPAT,"UTF-8"),$tr,null,$info,$id);
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
	  $value = mysqli_real_escape_string($DB_site2->link_id,trim($_POST[$field]));
	  if ($req && empty($value))
	    $erreur .= "<p>Le champ \"$label\" est obligatoire.</p>";
	  $query .= " `$field` = '$value',";

	  
	  if($info){
	      switch($tr){
	      case CHAMP_NUMERIC:
	      case CHAMP_TRAD:
                article_update_chk($field,isset($_POST[$field."_chk"]),$id);
	      break;
	      
	      case CHAMP_MULTI:
	      $exvalues=explode(",",$value);
	      foreach($exvalues as $exvalue){
		  		$chkname=$field."_".substr(strrchr($exvalue,"/"),1);
          article_update_chk($chkname,isset($_POST[$chkname."_chk"]),$id);
	      }
	      break;
	     }
	     
	  }
	}

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

?>