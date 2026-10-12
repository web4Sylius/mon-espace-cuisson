<?php
function article_four_fields($typearticle=4) {
    $fields =  array(
      'Commun' => array(
	'criteres_generaux'	=> array(
	  // champ			// traduire, texte du label
	  'type_pose'			=> array(CHAMP_TRAD, 'Type de pose','',true,true),
	  'emplacement_four'		=> array(CHAMP_TRAD, 'Emplacement du four','',false,false),
	  'type_four'			=> array(CHAMP_TRAD, 'Type de four','',true,true),
	  'techno'			=> array(CHAMP_TRAD, 'Technologie du four','',false,false),
	  'type_ouverture'		=> array(CHAMP_TRAD, 'Type d\'ouverture','',true,true),
	  'nb_vitres'			=> array(CHAMP_TRAD, 'Nombre de vitres','',false,false),
	  'fermeture_douce'		=> array(CHAMP_TRAD, 'Fermeture douce','',false,false),
	  'nettoyage'			=> array(CHAMP_TRAD, 'Type de nettoyage','',true,true),
	  
	),
	'bandeau_commandes'	=> array(
	  'type_cmd'			=> array(CHAMP_TRAD, 'Type de commande','',true,true),
	  //'type_cmd_categorie'		=> array(CHAMP_TRAD, 'Type de commande','Critère recherche',true),
	  'horloge'			=> array(CHAMP_MULTI, 'Horloge','',false,false),
	  'minuterie'			=> array(CHAMP_MULTI, 'Minuterie','',false,false),
	),
	'securite'	=> array(
	  'securite_four'		=> array(CHAMP_MULTI, 'Securité porte','',false,false),
	  'securite_enfant'		=> array(CHAMP_MULTI, 'Securité enfant','',false,false),
	  'verouillage_porte'		=> array(CHAMP_MULTI, 'Verrouillage','',false,false),
	),
	'fonction_cuisson'	=> array(
	  'nb_niv_cuisson'		=> array(CHAMP_TRAD, 'Nombre de niveau cuisson/puissance','',false,false),
	  'nb_mode_cuisson'		=> array(CHAMP_TRAD, 'Nombre de modes de cuisson','',false,false),
	  'chaleur_tournante'		=> array(CHAMP_TRAD, 'Chaleur tournante','',false,false),
	  'grill_presence'			=> array(CHAMP_MULTI, 'Présence grill','',false,false),
	  'nb_four_intelligent'		=> array(CHAMP_MULTI, 'Four intelligent','',false,false),
	),
	'performances'		=> array(
		'puissance_four'		=> array(CHAMP_NUMERIC, 'Puissance du four', 'W',false,false),
		'plage_temp'				=> array(CHAMP_TRAD, 'Plage de température', '°',false,false),
	  'grill'		=> array(CHAMP_TRAD, 'Puissance du grill', 'W',false,false),
	  'puissance_totale' => array(CHAMP_NUMERIC,'Puissance totale', 'W'),
    'intensite_electrique' => array(CHAMP_TRAD,'Intensité Electrique', 'A',false),
    'tension_electrique' => array(CHAMP_TRAD,'Tension éléctrique', 'V', false),
    'frequence' => array(CHAMP_TRAD,'Fréquence', 'Hz',false),
	 	'class_energetique' => array(CHAMP_LISTE_EN,'Classe énergétique', false,false), 
	),
	
	 'dimensions' => array(
		'hauteur' => array(CHAMP_NUMERIC,'Hauteur four', 'mm'),
		'largeur' => array(CHAMP_NUMERIC,'Largeur four', 'mm'),
		'profondeur' => array(CHAMP_NUMERIC,'Profondeur four', 'mm'),
		'poids' => array(CHAMP_NUMERIC,'Poids', 'g'),
		'hauteur_cavite' => array(CHAMP_NUMERIC,'Hauteur cavité', 'mm'),
		'largeur_cavite' => array(CHAMP_NUMERIC,'Largeur cavité', 'mm'),
		'profondeur_cavite' => array(CHAMP_NUMERIC,'Profondeur cavité', 'mm'),
    ),
	 'capacite' => array(
    	'volume_cavite'			=> array(CHAMP_NUMERIC, 'Volume cavité','L',false,true),
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

   ),
   'Micro-ondes' => array(
			'micro_ondes'		=> array(
	  		'type_plateau'		=> array(CHAMP_TRAD, 'Type de plateau','',false,false),
	  		'diametre_plateau'		=> array(CHAMP_TRAD, 'Diamètre du plateau', 'cm',false,false),
			),
			
    ),
   'Four-vapeur' => array(
   		'micro_ondes_vapeur'		=> array(
	 			'type_vapeur'		=> array(CHAMP_TRAD, 'Type de vapeur','',false,false),
			),
   )
 );

      //Champs cachés pour les piano et cuisinières
      if ($typearticle == 1 || $typearticle == 2) {
	unset($fields['Commun']['criteres_generaux']['type_pose']);
	unset($fields['Commun']['fonction_cuisson']['chaleur_tournante']);
	unset($fields['Commun']['fonction_cuisson']['plage_temp']);
	unset($fields['Commun']['fonction_cuisson']['nb_niv_cuisson']);
	unset($fields['Commun']['porte']['porte_demontable']);
	unset($fields['Commun']['fonction_cuisson']['multiniveau_categorie']);
	unset($fields['Commun']['bandeau_commandes']['type_cmd_categorie']);
	unset($fields['Commun']['dimensions']['dimensions_hlp']);
	unset($fields['Commun']['dimensions']['hauteur']);
	unset($fields['Commun']['dimensions']['largeur']);
	unset($fields['Commun']['dimensions']['profondeur']);
	unset($fields['Commun']['dimensions']['poids']);
	unset($fields['Commun']['dimensions_encastrement']);
	unset($fields['Commun']['coloris']);
      }
      elseif ($typearticle == 4) { // Four simple
	unset($fields['Commun']['fonction_cuisson']['chaleur_tournante']);
	unset($fields['Commun']['fonction_cuisson']['air_brasse']);
	unset($fields['Commun']['fonction_cuisson']['convection_naturelle']);
	unset($fields['Commun']['fonction_cuisson']['nb_niv_cuisson']);
	unset($fields['Commun']['criteres_generaux']['emplacement_four']);
	unset($fields['Commun']['criteres_generaux']['classe_energie_four']);
	unset($fields['Commun']['criteres_generaux']['puissance_four']);
	unset($fields['Commun']['porte']['porte_demontable']);

	$fields['Micro-ondes']['micro_ondes']['puissance_micro_ondes'] = array(
	  CHAMP_NUMERIC, 'Puissance du Micro-ondes', 'W');
      }
      elseif ($typearticle == 17) { // Micro-ondes
	unset($fields['Commun']['criteres_generaux']['emplacement_four']);
	unset($fields['Commun']['criteres_generaux']['techno_refroidissement']);
	unset($fields['Commun']['criteres_generaux']['classe_energie_four']);
	unset($fields['Commun']['criteres_generaux']['techno']);
	unset($fields['Commun']['criteres_generaux']['type_chaleur']);
	unset($fields['Commun']['fonction_cuisson']['nb_niv_cuisson']);
	unset($fields['Commun']['fonction_cuisson']['four_intelligent']);
	unset($fields['Commun']['fonction_cuisson']['circulation_air']);
	unset($fields['Commun']['fonction_cuisson']['chaleur_tournante']);
	unset($fields['Commun']['fonction_cuisson']['multiniveau']);
	unset($fields['Commun']['performances']['plage_temp']);
	unset($fields['Commun']['fonction_cuisson']['regulation_temp']);
	unset($fields['Commun']['fonction_cuisson']['air_brasse']);
	unset($fields['Commun']['fonction_cuisson']['convection_naturelle']);
	unset($fields['Commun']['fonction_cuisson']['booster']);
	unset($fields['Commun']['fonction_cuisson']['etuve']);
	unset($fields['Commun']['fonction_cuisson']['prechauffache_booster']);
	unset($fields['Commun']['fonction_cuisson']['turbogril']);
	unset($fields['Commun']['fonction_cuisson']['grill_presence']);
	unset($fields['Commun']['consommation']);
	unset($fields['Commun']['equipements']['catalyseur']);
	unset($fields['Commun']['equipements']['sole']);
	unset($fields['Commun']['equipements']['pizza']);
	unset($fields['Commun']['equipements']['tournebroche']);
	unset($fields['Commun']['equipements']['turbobroche']);
	unset($fields['Commun']['equipements']['micro_onde']);
	unset($fields['Commun']['porte']['interieur_porte_verre']);
	unset($fields['Commun']['porte']['porte_demontable']);
	unset($fields['Commun']['porte']['nb_vitres']);
	unset($fields['Commun']['porte']['fermeture_douce']);
	unset($fields['Commun']['criteres_generaux']['puissance_four']);
	unset($fields['Micro-ondes']);

	$fields['Commun']['criteres_generaux']['puissance_micro_ondes'] = array(
	  CHAMP_NUMERIC, 'Puissance du Micro-ondes', 'W');
	$fields['Commun']['criteres_generaux']['diffusion_onde'] = array(
	  CHAMP_TRAD, 'Système de diffusion des ondes','',false,false);
	$fields['Commun']['plateau']['type_plateau'] = array(CHAMP_TRAD, 'Type de plateau','',false,false);
	$fields['Commun']['plateau']['diametre_plateau'] = array(CHAMP_TRAD, 'Diamètre du plateau', 'cm',false,false);
     }
      return ($fields);
  }
  
  function article_four_fields_order($field){
    switch($field){
    case 'mode_cuisson':
    return array(
      "/article/mode_cuisson/eclairage",
      "/article/mode_cuisson/convection_naturelle",
      "/article/mode_cuisson/chaleur_tournante",
      "/article/mode_cuisson/chaleur_pulsee",
      "/article/mode_cuisson/sole_seule",
      "/article/mode_cuisson/sole_brassee",
      "/article/mode_cuisson/gril",
      "/article/mode_cuisson/turbogril",
      "/article/mode_cuisson/decongelation",
      "/article/mode_cuisson/tournebroche",
      "/article/mode_cuisson/turbobroche",
      "/article/mode_cuisson/pizza",
      "/article/mode_cuisson/etuve",
      "/article/mode_cuisson/catalyse",
      "/article/mode_cuisson/pyrolyse");
     break;
     
     case "four_intelligent":
     return array(
     "/article/four_intelligent/roti_boeuf",
     "/article/four_intelligent/roti_porc",
     "/article/four_intelligent/roti_agneau",
     "/article/four_intelligent/gateau",
     "/article/four_intelligent/pain");
     break;
     
     default:
     return false;
    }
  }
  
  function article_four_show($tab, $title, $id) {
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
    $query = "SELECT * FROM `article_four` WHERE `id` = '$id'";
    $values = $DB_site2->fetch_array($DB_site2->query($query));


    $article_fields = article_four_fields($typearticle);
    foreach($article_fields as $type => $groups) {
      if (!empty($type))
	echo "<fieldset><legend>$type :</legend>";

      foreach($groups as $group => $fields) {
	if (!empty($group))
	  echo '<h2>'.$template->translate('/article/champs/'.$group).'</h2>';
	// Affichage des champs
	echo '<table width="100%">';
	foreach($fields as $field => $prop) {
	  list($tr, $label, $suffix, $req, $info) = $prop;
	  $value = $values[$field];
	  $label .= (($req)?'<span class="obligatoire">*</span>':'')." : ";
	  $label.=($suffix!=""?"<br><small>(".$suffix.")</small>":"");
	  echo '<tr><td width="200">';
	  echo '<label for="'.$field.'">'.$label.'</label></td><td>';

	  print selecttranslate2($field,"/article/$field", $value,$tr,null,$info,$id);
	      
	  echo "</td>";

	  echo '</tr>';
	}
	echo '</table>';
      }

      if (!empty($type))
	echo "</fieldset>";
    }
    echo '<input type="hidden" name="four_id" value="'.$id.'" />';
    echo '<input type="hidden" name="item_action" />';
    echo '<input type="hidden" name="item_id" />';
    if ($typearticle != 4) // Si ce n'est pas un four simple
      echo '<input type="button" value="Supprimer ce four"'.
	 " onclick=\"javascript:itemaction('$tab','four_del', $id,'Supprimer le four \'$title\' ?')\" />";
  }

function article_four_update($tab, $title, $id) {
  global $DB_site2, $typearticle;

  if ($_POST['item_action'] == 'four_del') return "Suppression ".((article_four_delete($_POST['item_id']))? "réussie" : "échouée");
  if (!is_numeric($id)) return false;
  
  $article_fields = article_four_fields($typearticle);

  $query = "UPDATE `article_four` SET";
    
  foreach($article_fields as $type => $groups){
  	foreach($groups as $group => $fields){
			foreach($fields as $field => $prop) {
	  		list($tr, $label, $suffix, $req, $info) = $prop;
	  		$value = mysqli_real_escape_string($DB_site2->link_id,trim($_POST[$field]));
	  		if ($req && empty($value)) $erreur .= "<p>Le champ \"$label\" est obligatoire.</p>";
	  	  $query .= " `$field` = '$value',";
	  
	  		article_update_chk($field,isset($_POST[$field."_chk"]),$id);
			  
			  if($info){
			      switch($tr){
			      case CHAMP_NUMERIC:
			      case CHAMP_TRAD:
			      	//article_update_i($field,$_POST[$field."_i"],$id);
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
    }
	}
  if($new_entry)
    $query .= " `id` = $id";
  else {
    $query = substr($query, 0, -1); // retrait de la dernière virgule
    $query .= " WHERE id=$id";
  }
  $DB_site2->query($query);
  modifhistory($_POST);
}

  function article_four_count() {
    global $DB_site2, $artid;

    $count = $DB_site2->query_first(
      "SELECT COUNT(`artid`)".
      "FROM `article_four`".
      "WHERE `artid` = $artid"
    );
    return($count[0]);
  }

  function article_four_add() {
    global $DB_site2, $artid;


    $query = "INSERT INTO `article_four` SET `artid` = '$artid'";
    return $DB_site2->query($query);
  }

  function article_four_delete($four_id) {
    global $DB_site2;

    if (is_null($four_id) || !is_numeric($four_id))
      return (false);


    $query = "DELETE FROM `article_four` WHERE `id` = '$four_id'";
    return $DB_site2->query($query);
  }
  
  
  function article_four_order_values($field,$values){
      $fieldorder=article_four_fields_order($field);
      if($fieldorder==false) return $values;
      
      $keys=array_flip($fieldorder);
      $ret=array();
      $i=0;
      foreach($values as $value){
	  $key=$keys[$value];
	  if(!isset($keys[$value])){
	      $key=count($fieldorder)+$i;
	      $i++;
	  }
	  $ret[$key]=$value;
      }
      ksort($ret);

      return $ret;
  }
?>