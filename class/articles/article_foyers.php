<?php

  function article_foyers_fields($typearticle) {
    $fields = array(
      'emplacement_foyer'	=> array(CHAMP_TRAD, 'Emplacement','',true,true),
      'techno_foyer'		=> array(CHAMP_TRAD, 'Technologie du foyer','',false,true),
      'puissance'		=> array(CHAMP_TRAD, 'Puissance', 'W',false,true),
      'diametre'		=> array(CHAMP_TRAD, 'Diamètre du foyer', '',false,true),
    );

    return ($fields);
  }

  function article_foyers_show($tab, $tableid) {
    global $DB_site2, $artid, $typearticle;

    $fields = article_foyers_fields($typearticle);
    $query = $DB_site2->query("SELECT * FROM `article_foyers` WHERE `table_id` = '$tableid' ORDER BY `id`");
    $i = 1;
    while($values = $DB_site2->fetch_array($query)) {
      echo '<fieldset><legend>Foyer '.$i.'</legend>';
      echo '<table>';
      foreach($fields as $field => $prop) {
	list($tr, $label, $suffix, $req, $info) = $prop;
	$value = htmlentities($values[$field],ENT_COMPAT,"ISO-8859-15");
	$label .= (($req)?'<span class="obligatoire">*</span>':'')." : ";
	$label.=($suffix!=""?" (".$suffix.")":"");
	echo '<tr><td>';
	echo '<label for="'.$field.'_'.$values['id'].'">'.$label.'</label></td><td>';
	echo selecttranslate("${field}_$values[id]","/article/$field", html_entity_decode($value,ENT_COMPAT,"ISO-8859-15"),$tr,null,$info,$values['id']);
	echo '</td></tr>';
      }
      echo '</table>';
      echo '<input type="button" value="Supprimer ce foyer" onclick='.
	   "\"javascript:itemaction('$tab', 'foyer_del', $values[id], 'Supprimer le foyer n°$i ?')\" />";
      echo '</fieldset>';
      $i++;
    }
  }

  function article_foyers_update($action, $tableid, $foyer) {
    global $artid, $DB_site2, $typearticle;

    if ($action == 'foyer_add')
      article_foyers_add($tableid);
    if ($action == 'foyer_del')
      article_foyers_delete($foyer);

    $fields = article_foyers_fields($typearticle);

    $foyers_query = $DB_site2->query("SELECT id FROM `article_foyers` WHERE table_id=$tableid");

    while ($foyers = $DB_site2->fetch_array($foyers_query)) {
      $query = "UPDATE `article_foyers` SET";

      foreach($fields as $field => $prop) {
	list($tr, $label, $suffix, $req, $info) = $prop;
	$value = mysqli_real_escape_string($DB_site2->link_id,trim($_POST["${field}_$foyers[id]"]));
	if ($req && empty($value))
	  $erreur .= "<p>Le champ \"$label\" est obligatoire.</p>";
	$query .= " `$field` = '$value',";

	//article_update_chk($field,isset($_POST[$field."_".$foyers[id]."_chk"]),$foyers[id]);
	/*if($info){
	      switch($tr){
	      case CHAMP_NUMERIC:
	      case CHAMP_TRAD:
	      article_update_i($field,$_POST[$field."_".$foyers[id]."_i"],$foyers[id]);
	      break;
	      
	      case CHAMP_MULTI:
	      $exvalues=explode(",",$value);
	      foreach($exvalues as $exvalue){
		  $chkname=$field."_".substr(strrchr($exvalue,"/"),1);
		  article_update_i($chkname,$_POST[$chkname."_i"],$foyers[id]);
	      }
	      break;
	     }
	     article_update_chk($field."_i",isset($_POST[$field."_".$foyers[id]."_i_chk"]),$foyers[id]);
	  }*/
      }

//       if (!empty($erreur))
// 	return ($erreur);
      $query = substr($query, 0, -1); // retrait de la dernière virgule
      $query .= " WHERE id=$foyers[id]";
      $DB_site2->query($query);
    }
    modifhistory($_POST);
  }

  function article_foyers_add($table) {
    global $DB_site2;

    $query = "INSERT INTO `article_foyers` SET `table_id` = '$table'";
    return $DB_site2->query($query);
  }

  function article_foyers_delete($foyer_id) {
    global $DB_site2, $artid;

    if (is_null($foyer_id) || !is_numeric($foyer_id))
      return (false);

    $query = "DELETE FROM `article_foyers` WHERE `id` = '$foyer_id'";
    return $DB_site2->query($query);
  }
?>