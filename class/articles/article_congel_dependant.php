<?php

function article_congel_dependant_fields($typearticle) {
    $fields= array(
	'Commun' => array(
	    'criteres_generaux'	=> array(
        'position_congel'        => array(CHAMP_TRAD, 'Position','',false,true),
		'type_pose'			=> array(CHAMP_TRAD, 'Type de pose','',false,true),
        'version_porte_congel'      => array(CHAMP_TRAD, 'Type d\'ouverture','',false,true),
		'type_froid_congel'			=> array(CHAMP_TRAD, 'Type de froid','',false,true),
		'degivrage_congel'			=> array(CHAMP_MULTI, 'Dégivrage automatique','',false,true),
		'nofrost'					=> array(CHAMP_MULTI, 'No Frost','',false,true),
		'congelation_rapide'		=> array(CHAMP_MULTI, 'Fonction congélation rapide','',false,true),
		'bac_glacon_congel'			=> array(CHAMP_TRAD, 'Bac à glaçons','',false,true),
		'compartiment'		=> array(CHAMP_TRAD, 'Compartiment','',false,true)
	     ),
        'capacite' => array(
            'volume_cavite'          => array(CHAMP_NUMERIC, 'Volume','L',false,true),
          ),
          'dimensions' => array(
        'dimensions_hlp' => array(CHAMP_TXT,'dimensions', 'HxLxP',false),
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
    )

	)
    );

    if ($typearticle == '26') {
        unset($fields['Commun']['criteres_generaux']['type_pose']);
        unset($fields['Commun']['dimensions']);
        unset($fields['Commun']['dimensions_encastrement']);
        unset($fields['Commun']['coloris']);

    }else{
        unset($fields['Commun']['criteres_generaux']['position_congel']);

    }
    return $fields;
}


function article_congel_dependant_show($tab, $title, $id) {
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
    $query = "SELECT * FROM `article_congel_dependant` WHERE `id` = '$id'";
    $values = $DB_site2->fetch_array($DB_site2->query($query));

    $article_fields = article_congel_dependant_fields($typearticle);
    foreach($article_fields as $type => $groups) {
      if (!empty($type))
	echo "<fieldset><legend>$type :</legend>";

      foreach($groups as $group => $fields) {
	if (!empty($group))
	  echo '<h2>'.$template->translate('/article/champs/'.$group,'F').'</h2>';
	// Affichage des champs
	echo '<table>';
	foreach($fields as $field => $prop) {
	  list($tr, $label, $suffix, $req, $info) = $prop;
	  $value = htmlentities($values[$field],ENT_COMPAT,"ISO-8859-15");
	  $label .= (($req)?'<span class="obligatoire">*</span>':'')." : ";
	  $label.=($suffix!=""?"<br><small>(".$suffix.")</small>":"");
	  echo '<tr><td width="200">';
	  echo '<label for="'.$field.'">'.$label.'</label></td><td>';

	  print selecttranslate2($field,"/article/$field", html_entity_decode($value,ENT_COMPAT,"ISO-8859-15"),$tr,null,$info,$id);
	      
	  echo "</td>";

	  echo '</tr>';
	}
	echo '</table>';
      }

      if (!empty($type))
	echo "</fieldset>";
    }
    echo '<input type="hidden" name="id" value="'.$id.'" />';
    echo '<input type="hidden" name="item_action" />';
    echo '<input type="hidden" name="item_id" />';

    if ($typearticle == 26){
    echo '<input type="button" value="Supprimer ce congélateur"'.
	 " onclick=\"javascript:itemaction('$tab','congel_dependant_del', $id,'Supprimer le congélateur \'$title\' ?')\" />";
    }
}

function article_congel_dependant_update($tab, $title, $id) {
    global $DB_site2, $typearticle;

    if ($_POST['item_action'] == 'congel_dependant_del')
      return "Suppression ".(
	(article_congel_dependant_delete($_POST['item_id']))? "réussie" : "échouée");
    if (!is_numeric($id))
      return false;

    $article_fields = article_congel_dependant_fields($typearticle);

    $query = "UPDATE `article_congel_dependant` SET";
    
    foreach($article_fields as $type => $groups)
      foreach($groups as $group => $fields)
	foreach($fields as $field => $prop) {
	  list($tr, $label, $suffix, $req, $info) = $prop;
	  $value = mysqli_real_escape_string($DB_site2->link_id,trim($_POST[$field]));
	  if ($req && empty($value))
	    $erreur .= "<p>Le champ \"$label\" est obligatoire.</p>";
	  $query .= " `$field` = '$value',";
	  
	  article_update_chk($field,isset($_POST[$field."_chk"]),$id);
      }

    if ($new_entry)
      $query .= " `id` = $id";
    else {
      $query = substr($query, 0, -1); // retrait de la dernière virgule
      $query .= " WHERE id=$id";
    }
    $DB_site2->query($query);
    modifhistory($_POST);
}

function article_congel_dependant_count() {
    global $DB_site2, $artid;

    $count = $DB_site2->query_first(
      "SELECT COUNT(`artid`)".
      "FROM `article_congel_dependant`".
      "WHERE `artid` = $artid"
    );
    return($count[0]);
  }

function article_congel_dependant_add(){
    global $DB_site2, $artid;

    $query="INSERT INTO `article_congel_dependant` SET artid='$artid'";
    return $DB_site2->query($query);
}

function article_congel_dependant_delete($id){
    global $DB_site2;
  
    if (is_null($id) || !is_numeric($id))
      return (false);


    $query = "DELETE FROM `article_congel_dependant` WHERE `id` = '$id'";
    return $DB_site2->query($query);
}

?>