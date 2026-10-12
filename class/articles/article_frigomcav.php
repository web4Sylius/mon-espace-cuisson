<?php
function article_frigomcav_fields() {
	return array(
    	array(
    	'criteres_generaux'	=> array(
			  'position'	=> array(CHAMP_IMG, 'Position', 'L',false,true),
			),
			'capacite'	=> array(
			  'volume'			=> array(CHAMP_NUMERIC, 'Volume utile', 'L',false,true),
			  'volume_brut'	=> array(CHAMP_NUMERIC, 'Volume brut','L'),
			),

			'Design'		=> array(
				'type_ouverture'		=> array(CHAMP_TRAD, 'Ouverture de la porte','',false,true),
				'coloris_cadre'			=> array(CHAMP_TRAD, 'Coloris du cadre de la porte','',false,true),
				'type_poignee'		=> array(CHAMP_TRAD, 'Type de poignée', '',false,true),
				'coloris_int'				=> array(CHAMP_TRAD, 'Revêtement cuve/coloris intérieur','',false,true),
				'eclairage'					=> array(CHAMP_TRAD, 'Éclairage intérieur','',false,true),
			  'coloris_eclairage'	=> array(CHAMP_TRAD, 'Coloris de l\'éclairage','',false,true),
			  'position_eclairage'=> array(CHAMP_TRAD, 'Position de l\'éclairage','',false,true),			  			  	  
			),
			'amenagement_int' => array(
			  'accessoires_inclus'				=> array(CHAMP_TRAD, 'Accessoires inclus', '',false,true),
			  'accessoires_inclus1'				=> array(CHAMP_TRAD, 'Accessoires inclus', '',false,true),
			  'accessoires_inclus2'				=> array(CHAMP_TRAD, 'Accessoires inclus', '',false,true),
			  'accessoires_inclus3'				=> array(CHAMP_TRAD, 'Accessoires inclus', '',false,true),
			  'reglage_clayettes'					=> array(CHAMP_NUMERIC, 'Hauteur de réglage des clayettes', 'mm',false,true),
				//'dimensions_clayettes'			=> array(CHAMP_TXT, 'Dimensions des clayettes/grilles', 'LxP mm',false,true),
			  'largeur_clayettes'					=> array(CHAMP_NUMERIC, 'Largeur des clayettes/grilles', 'mm',false,true),
			  'profondeur_clayettes'			=> array(CHAMP_NUMERIC, 'Profondeur des clayettes/grilles', 'mm',false,true),
			  'nb_clayettes'							=> array(CHAMP_NUMERIC, 'Nombre maximum de clayettes', '',false,true),
			  'chargement_max_clayettes'	=> array(CHAMP_NUMERIC, 'Charge maxi par clayette/grille (kg)', '',false,true),
			),
			'dimensions' => array(
			    //'dimensions_cuve'						=> array(CHAMP_TXT,'dimensions de la cuve', 'HxLxP',false),
			    'hauteur_int' 							=> array(CHAMP_NUMERIC,'Hauteur intérieure cuve', 'mm'),
			    'largeur_int'				 				=> array(CHAMP_NUMERIC,'Largeur intérieure cuve', 'mm'),
			    'profondeur_int' 						=> array(CHAMP_NUMERIC,'Profondeur intérieure cuve', 'mm'),
			),
			'technique'	=> array(
				'type_froid'						=> array(CHAMP_TRAD, 'Type de froid','',false,true),
				'reglage_temp'					=> array(CHAMP_TRAD, 'Plage de réglage de la température','',false,true),
				'regulation'						=> array(CHAMP_TRAD, 'Régulation','',false,true),
				'degivrage_auto'				=> array(CHAMP_TRAD, 'Dégivrage automatique','',false,true),
				'affichage'							=> array(CHAMP_TRAD, 'Affichage','',false,true),
				'hygrometrie'						=> array(CHAMP_TRAD, 'Hygrométrie',''),
			  'evaporateur'						=> array(CHAMP_TRAD, 'Evaporateur','',false,true),
			  'condenseur'						=> array(CHAMP_TRAD, 'Condenseur',''),
			  'puissance_totale' 			=> array(CHAMP_NUMERIC,'Puissance absorbée', 'W'),
		    'intensite_electrique' 	=> array(CHAMP_NUMERIC,'Intensité absorbée', 'A',false),
			  'isolation'							=> array(CHAMP_TRAD, 'Isolation','',false,true),
			  'technologie_specifique'=> array(CHAMP_TRAD, 'Technologie(s) spécifique(s)',''),  
			  'technologie_specifique1'	=> array(CHAMP_TRAD, 'Technologie(s) spécifique(s)',''),
			  'technologie_specifique2'	=> array(CHAMP_TRAD, 'Technologie(s) spécifique(s)',''),
			  'technologie_specifique3'	=> array(CHAMP_TRAD, 'Technologie(s) spécifique(s)',''),
			),
			'equipements'		=> array(
			  'serrure'										=> array(CHAMP_TRAD, 'Serrure', '',false,true),
			  'serrure_recherche'					=> array(CHAMP_MULTI, 'Serrure (affinage recherche, non visible)', '',false,true),
			  'alarme'										=> array(CHAMP_TRAD, 'Alarme(s)', '',false,true),  
			  'verrouillage_cmd'					=> array(CHAMP_MULTI, 'Verrouillage des commandes', '',false,true),
			  'arret_ventilateur'					=> array(CHAMP_MULTI, 'Arrêt ventilateur à l’ouverture', '',false,true),
			  'equipement_specifique'	=> array(CHAMP_TRAD, 'Équipement(s) spécifique(s)','',false,true),
			  'equipement_specifique1'	=> array(CHAMP_TRAD, 'Équipement(s) spécifique(s)','',false,true),
			  'equipement_specifique2'	=> array(CHAMP_TRAD, 'Équipement(s) spécifique(s)','',false,true),
			  'equipement_specifique3'	=> array(CHAMP_TRAD, 'Équipement(s) spécifique(s)','',false,true),
			),        
    	));
  }

function article_frigomcav_show($tab, $title, $id) {
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
    $query = "SELECT * FROM `article_frigomcav` WHERE `id` = '$id'";
    $values = $DB_site2->fetch_array($DB_site2->query($query));

    $article_fields = article_frigomcav_fields($typearticle);
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
	  $label .= (($req)?'<span class="obligatoire">*</span>':'')."";
	  $label.=($suffix!=""?" (".$suffix.") : ":" : ");
	  echo '<tr><td width="200">';
	  echo '<label for="'.$field.'">'.$label.'</label></td><td>';

	  print selecttranslate2($field,"/article/$field", html_entity_decode($value,ENT_COMPAT,"ISO-8859-15"),$tr,null,$info,$id,'frigomcav');
	      
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


    echo '<input type="button" value="Supprimer ce réfrigérateur"'.
	 " onclick=\"javascript:itemaction('$tab','frigomcav_del', $id,'Supprimer le congel \'$title\' ?')\" /><br><br>";
}

function article_frigomcav_update($tab, $title, $id) {
    global $DB_site2, $typearticle;

    if ($_POST['item_action'] == 'frigomcav_del')
      return "Suppression ".(
	(article_frigomcav_delete($_POST['item_id']))? "réussie" : "échouée");
    if (!is_numeric($id))
      return false;

    $article_fields = article_frigomcav_fields($typearticle);

    $query = "UPDATE `article_frigomcav` SET";
    
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

  function article_frigomcav_count() {
    global $DB_site2, $artid;

    $count = $DB_site2->query_first(
      "SELECT COUNT(`artid`)".
      "FROM `article_frigomcav`".
      "WHERE `artid` = $artid"
    );
    return($count[0]);
  }

function article_frigomcav_add(){
    global $DB_site2, $artid;

    $query="INSERT INTO `article_frigomcav` SET artid='$artid'";
    return $DB_site2->query($query);
}

function article_frigomcav_delete($id){
    global $DB_site2;
  
    if (is_null($id) || !is_numeric($id))
      return (false);


    $query = "DELETE FROM `article_frigomcav` WHERE `id` = '$id'";
    return $DB_site2->query($query);
}
?>