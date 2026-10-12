<?php
function article_chocolat_fields() {
	return array(
    	array(
			'capacite'	=> array(
			  'volume'				=> array(CHAMP_NUMERIC, 'Volume utile', 'L',false,true),
			  'capacite_max' 	=> array(CHAMP_NUMERIC, 'Chargement maximum','kg'),
			),

			'Design'		=> array(
				'type_porte'			=> array(CHAMP_TRAD, 'Type de porte','',false,true),
				'coloris_cadre'		=> array(CHAMP_TRAD, 'Coloris du cadre de la porte','',false,true),
				'coloris_porte'		=> array(CHAMP_TRAD, 'Coloris de la porte','',false,true),
				'caract_vitrage'	=> array(CHAMP_TRAD, 'Caractéristique vitrage','',false,true),
				'antiuv'					=> array(CHAMP_MULTI, 'Traitement anti-UV','',false,true),
				'coloris_vitrage'	=> array(CHAMP_TRAD, 'Coloris vitrage','',false,true),
				'type_poignee'		=> array(CHAMP_TRAD, 'Type de poignée', '',false,true),
				'revetement_int'	=> array(CHAMP_TRAD, 'Revêtement intérieur','',false,true),
			  'coloris_int'			=> array(CHAMP_TRAD, 'Coloris intérieur','',false,true),			  	  
			),
			'amenagement_int' => array(
			  'accessoires_inclus'				=> array(CHAMP_TRAD, 'Accessoires inclus', '',false,true),
			  'amenagement_int'						=> array(CHAMP_TRAD, 'Aménagement intérieur personnalisable', '',false,true),
				'dimensions_clayettes'			=> array(CHAMP_TXT, 'Dimensions des clayettes/grilles', 'LxP mm',false,true),
			  'largeur_clayettes'					=> array(CHAMP_NUMERIC, 'Dimensions clayettes/grilles – Largeur ', 'mm',false,true),
			  'profondeur_clayettes'			=> array(CHAMP_NUMERIC, 'Dimensions clayettes/grilles – Profondeur', 'mm',false,true),
			  'nb_clayettes'							=> array(CHAMP_NUMERIC, 'Nombre de clayettes maximum', '',false,true),
			  'reglage_clayettes'					=> array(CHAMP_NUMERIC, 'Hauteur de réglage des clayettes', 'mm',false,true),
			  'chargement_max_clayettes'	=> array(CHAMP_NUMERIC, 'Charge maximum par clayette', 'kg',false,true),
			),
			'technique_regulation'		=> array(
			  'nb_zone_temp'						=> array(CHAMP_TRAD, 'Nombre de zones de température','',false,true),
			  'reglage_temp'						=> array(CHAMP_TRAD, 'Plage de réglage de la température','',false,true),
			  'type_froid_cave'					=> array(CHAMP_TRAD, 'Type de production de froid','',false,true),
			  'fonction_hiver'					=> array(CHAMP_TRAD, 'Fonction Hiver', '',false,true),
			  'regulation_thermostat'		=> array(CHAMP_TRAD, 'Régulation/Thermostat','',false,true),
			  'position_thermostat'			=> array(CHAMP_TRAD, 'Position thermostat','',false,true),
			  'affichage'								=> array(CHAMP_TRAD, 'Affichage','',false,true),
			  'btn_reglage'							=> array(CHAMP_TRAD, 'Boutons de réglage','',false,true),
			  'hygrometrie'							=> array(CHAMP_TRAD, 'Hygrométrie',''),
			  'affichage_hygro'					=> array(CHAMP_TRAD, 'Affichage de l\'hygrométrie',''),
			  'antivib'									=> array(CHAMP_TRAD, 'Système anti-vibrations','',false,true),
			  'degivrage_auto'					=> array(CHAMP_TRAD, 'Dégivrage automatique','',false,true),
			  'technologie_specifique'	=> array(CHAMP_TRAD, 'Technologie(s) spécifique(s)',''),		  
			),
			'equipements'		=> array(
			  'serrure'						=> array(CHAMP_TRAD, 'Serrure', '',false,true),
			  'serrure_recherche'	=> array(CHAMP_TRAD, 'Serrure (affinage recherche, non visible)', '',false,true),
			  'filtre_charbon'		=> array(CHAMP_TRAD, 'Filtre à charbon', '',false,true),
			  'alarme'						=> array(CHAMP_TRAD, 'Alarme', '',false,true),  
			),
			'dimensions' => array(
			    'dimensions_hlp'						=> array(CHAMP_TXT,'dimensions', 'HxLxP',false),
			    'hauteur' 									=> array(CHAMP_NUMERIC,'Hauteur nette', 'mm'),
			    'largeur'				 						=> array(CHAMP_NUMERIC,'Dimensions Nettes produit – Largeur', 'mm'),
			    'profondeur' 								=> array(CHAMP_NUMERIC,'Dimensions Nettes produit – Profondeur', 'mm'),
			    'poids' 										=> array(CHAMP_NUMERIC,'Dimensions Nettes produit – Poids', 'kg'),
			    'largeur_90' 								=> array(CHAMP_NUMERIC,'Largeur totale porte ouverte à 90°', 'mm'),
			    'profondeur_porte_ouverte' 	=> array(CHAMP_NUMERIC,'Profondeur totale porte ouverte', 'mm'),
			    'hauteur_int' 							=> array(CHAMP_NUMERIC,'Dimensions intérieure produit – Hauteur', 'mm'),
			    'largeur_int'				 				=> array(CHAMP_NUMERIC,'Dimensions intérieure produit – Largeur', 'mm'),
			    'profondeur_int' 						=> array(CHAMP_NUMERIC,'Dimensions intérieure produit – Profondeur', 'mm'),
			    'pieds_reglables'						=> array(CHAMP_TRAD, 'Pieds réglables', '',false,true),
			    'porte_reversible'					=> array(CHAMP_MULTI, 'Porte réversible','',false,true),
			  	'ouverture_porte'						=> array(CHAMP_TRAD, 'Ouverture de la porte','',false,true),
			),			
			'technique_performances'		=> array(
				'class_energetique' 		=> array(CHAMP_LISTE_EN,'Classe énergetique', '', false),
				'conso_an'							=> array(CHAMP_NUMERIC, 'Consommation en Kwh/an', 'Kwh'),
			 	//'conso_kw'							=> array(CHAMP_NUMERIC, 'Consommation en Kwh/24h', 'Kwh',false,true),
			  'conso_euro'						=> array(CHAMP_NUMERIC, 'Consommation en €/an (0.17 € kW/h)', 'EUR',false,true),
			  'classe_climatique' 		=> array(CHAMP_TRAD,'Classe climatique', '', false),
			  'iee' 									=> array(CHAMP_TRAD,'IEE', '', false),
			  'niveau_bruit'					=> array(CHAMP_NUMERIC, 'Niveau de bruit', 'dBA',false,true),
			  'niveau_bruit_abcd'			=> array(CHAMP_TRAD, 'Niveau sonore (ABCD)', 'W',false,true),
			  'puissance_totale' 			=> array(CHAMP_NUMERIC,'Puissance absorbée', 'W'),
		    'intensite_electrique' 	=> array(CHAMP_NUMERIC,'Intensité absorbée', 'A',false),
		    'gaz_froid' 						=> array(CHAMP_TRAD,'Fluide réfrigérant', '', false),
		    'isolation'							=> array(CHAMP_TRAD, 'Isolation','',false,true),
		    'alim_elec' 						=> array(CHAMP_TRAD,'Alimentation électrique', '',false),
		    'fiche_energie' 				=> array(CHAMP_TRAD,'Étiquette énergie', '',false),
			),
			'service' => array(
     	 'dispo_pieces' => array(CHAMP_NUMERIC,'Disponibilité des pièces détachées', 'ans'),
    	),
      
    	));
  }

  function article_chocolat_show($tab, $title, $id) {
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
    $query = "SELECT * FROM `article_chocolat` WHERE `id` = '$id'";
    $values = $DB_site2->fetch_array($DB_site2->query($query));

    $article_fields = article_chocolat_fields($typearticle);
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


    echo '<input type="button" value="Supprimer cette cave à chocolat"'.
	 " onclick=\"javascript:itemaction('$tab','chocolat_del', $id,'Supprimer la cave à chocolat \'$title\' ?')\" /><br><br>";
}

function article_chocolat_update($tab, $title, $id) {
    global $DB_site2, $typearticle;

    if ($_POST['item_action'] == 'chocolat_del')
      return "Suppression ".(
	(article_chocolat_delete($_POST['item_id']))? "réussie" : "échouée");
    if (!is_numeric($id))
      return false;

    $article_fields = article_chocolat_fields($typearticle);

    $query = "UPDATE `article_chocolat` SET";
    
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

  function article_chocolat_count() {
    global $DB_site2, $artid;

    $count = $DB_site2->query_first(
      "SELECT COUNT(`artid`)".
      "FROM `article_chocolat`".
      "WHERE `artid` = $artid"
    );
    return($count[0]);
  }

function article_chocolat_add(){
    global $DB_site2, $artid;

    $query="INSERT INTO `article_chocolat` SET artid='$artid'";
    return $DB_site2->query($query);
}

function article_chocolat_delete($id){
    global $DB_site2;
  
    if (is_null($id) || !is_numeric($id))
      return (false);


    $query = "DELETE FROM `article_chocolat` WHERE `id` = '$id'";
    return $DB_site2->query($query);
}
?>