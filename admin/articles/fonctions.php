<?php

function article_action($tabs, $tab, $action) {
  global $DB_site2, $erreur, $old, $action2, $rootpath;

  foreach($tabs as $key => $title) {
    if ($key == $tab) {
    	if (preg_match('/dyn_([\w_]+)_(\d+)/', $tab, $matches)) {
       $key = $matches[1];
       $id = $matches[2];
      }
    	$file = $rootpath."class/articles/article_$key.php";
    	$function = "article_${key}_$action";
    	$function_fallback = "article_global_${action}";
    	if (file_exists($file))
    	  include_once($file);
    	  else echo $file;
    	if (!function_exists($function))
    	  $erreur .= $function_fallback($tab, $title);
    	else
    	  $erreur .= $function($tab, $title, $id);
      
      return true;
    }
  }
  return false;
}

  function article_global_show($tab, $title, $grouped = false, $type_pdt = '') {
    global $artid, $catid, $DB_site2, $erreur, $template, $typearticle;
    if(!empty($type_pdt)){
         $article_fields_funct = "article_".$type_pdt."_fields";
         $tab = $type_pdt;
    } else {
         $article_fields_funct = "article_${tab}_fields";
    }         
    $article_fields = $article_fields_funct($typearticle);

    echo <<<EOL
    <h3>$title</h3>
    <div class="cost">$erreur</div>
    <form name="formu" action="ajout_produit.php" method="post" enctype="multipart/form-data" >
    <input type="hidden" name="old" value="">
    <input type="hidden" name="quitter" value="">
    <input type="hidden" name="mq2" value="">
    <input type="hidden" name="action2" value="">
    <input type="hidden" name="modif" value="0">
    <input type="hidden" name="artid" value="$artid">
    <input type="hidden" name="catid" value="$catid">
EOL;
    $query = "SELECT * FROM `article_$tab` WHERE `artid` = '$artid'";
    $values = $DB_site2->fetch_array($DB_site2->query($query));

    // Affichage des champs par groupes
    if (!$grouped)
      $article_fields = array(array($article_fields));
    foreach($article_fields as $type => $groups) {
      if (!empty($type))
	echo "<fieldset><legend>$type :</legend>";

      foreach($groups as $group => $fields) {
	if (!empty($group))
	  echo '<h2>'.$template->translate('/article/champs/'.$group).'</h2>';
	// Affichage des champs
	echo '<table width="100%">';
	foreach($fields as $field => $prop) {
	  list($tr, $label, $suffix, $req, $info)= $prop;
	  $value = htmlentities($values[$field],ENT_COMPAT,"ISO-8859-15");
	  $label .= (($req)?'<span class="obligatoire">*</span>':'');
	  $label.=($suffix!=""?" (".$suffix.") : ":" : ");
	  echo '<tr><td width="200">';
	  echo '<label for="'.$field.'">'.$label.'</label></td><td valign="top">';

	  print selecttranslate2($field,"/article/$field", html_entity_decode($value,ENT_COMPAT,"ISO-8859-15"),$tr,null,($info?0:false),0,$tab);

	  echo "</td>";

	  echo '</tr>';
	}
	echo '</table>';
      }

      if (!empty($type))
	echo "</fieldset>";
    }
  }

// construit des requête INSERT ou UPDATE automatiquement
function article_global_update($tab, $title, $grouped=false, $type_pdt = '') {
  global $artid, $catid, $DB_site2, $erreur, $typearticle,$host,$rootpath;
  $dimquery="";
  if(!empty($type_pdt)){
       $article_fields_funct = "article_".$type_pdt."_fields";
       $tab = $type_pdt;
  } else {
       $article_fields_funct = "article_${tab}_fields";
  }
  $article_fields = $article_fields_funct($typearticle);

  $new_entry = $DB_site2->query("SELECT artid FROM `article_$tab` WHERE artid=$artid");
  $new_entry = !$DB_site2->num_rows($new_entry);
  if ($new_entry) {
    $query = "INSERT INTO `article_$tab` SET";
  }
  else{
    $query = "UPDATE `article_$tab` SET";
  }
  $dim = array("hauteur","largeur","profondeur");
  if (!$grouped)
  $article_fields = array(array($article_fields));
  foreach($article_fields as $type => $groups)
    foreach($groups as $group => $fields)
      foreach($fields as $field => $prop) {
        list($tr, $label, $suffix, $req, $info) = $prop;
        if(!is_null($_POST[$field])){
          $value = mysqli_real_escape_string($DB_site2->link_id,trim($_POST[$field]));
          $query .= " `$field` = '$value',";
          if(in_array($field, $dim)){
            $dimquery .="`$field` = '$value',";
          }
        }
        if($req && empty($value)) $erreur .= "<p>Le champ \"$label\" est obligatoire.</p>";
    
        if($info){
            switch($tr){
            case CHAMP_NUMERIC:
            case CHAMP_TRAD:
                article_update_chk($field,isset($_POST[$field."_chk"]));
            break;
            
            case CHAMP_MULTI:
              $exvalues=explode(",",$value);
              foreach($exvalues as $exvalue){
        	      $chkname=$field."_".substr(strrchr($exvalue,"/"),1);
                article_update_chk($chkname,isset($_POST[$chkname."_chk"]));
              }
            break;

            case CHAMP_IMG:
              if($_FILES[$field]['error'] == 0){
                $extension_upload = strtolower(pathinfo($_FILES[$field]['name'], PATHINFO_EXTENSION));
                $extensions_valides = array( 'png','jpg');
                $dest = $rootpath."configurations/$host/article_$field/".$artid.".".$extension_upload;
                if ( in_array($extension_upload,$extensions_valides) ){
                  move_uploaded_file($_FILES[$field]['tmp_name'], $dest);
                  $query2 = "UPDATE article_$tab SET `$field` ='".$extension_upload."' WHERE artid=".$artid;
                }
              }
            break;
           }             
        }
        if($tr == CHAMP_PDF){
              if($_FILES[$field]['error'] == 0){
                $extension_upload = strtolower(pathinfo($_FILES[$field]['name'], PATHINFO_EXTENSION));
                $extensions_valides = array( 'pdf');
                $dest = $rootpath."configurations/$host/article_$field/".$artid.".".$extension_upload;
                if ( in_array($extension_upload,$extensions_valides) ){
                  move_uploaded_file($_FILES[$field]['tmp_name'], $dest);
                  $query2 = "UPDATE article_$tab SET `$field` ='".$extension_upload."' WHERE artid=".$artid;
                }
              }
           }
      }

  if ($new_entry){
    $query  .= " `artid` = $artid";
  }else {
    $query  = substr($query, 0, -1); // retrait de la dernière virgule
    $query  .= " WHERE artid=$artid";
  }

  //cas particulier des dimensions
  if($dimquery != ""){
    $dimquery = substr($dimquery, 0, -1);
    $DB_site2->query("UPDATE article SET $dimquery WHERE artid=$artid");
  }

  $DB_site2->query($query);
  if(!is_null($query2)) $DB_site2->query($query2);
  modifhistory($_POST);
}
  
  function article_update_i($field,$value,$tabid=0){
    global $artid,$DB_site2;
    
    $sql="";
    $testi=$DB_site2->query_first("SELECT artid FROM `article_i` WHERE artid=$artid AND field='$field' AND tabid='$tabid'");
    if($testi['artid']==$artid)
      $sql="UPDATE article_i SET value='$value' WHERE artid=$artid AND field='$field' AND tabid='$tabid'";
    else
      $sql="INSERT INTO article_i SET artid=$artid, field='$field', value='$value', tabid='$tabid'";
    $DB_site2->query($sql);
  }
  
  function article_update_chk($field,$checked,$tabid=0){
      global $artid, $DB_site2;
     
      if($checked){
	      $test=$DB_site2->query_first("SELECT * FROM article_field_chk WHERE artid='$artid' AND field='$field' AND tabid='$tabid'");
	      if($test['artid']!=$artid)
	        $DB_site2->query("INSERT INTO article_field_chk (artid,field,tabid) VALUES ('$artid','$field','$tabid')");
        }else
	        $DB_site2->query("DELETE FROM article_field_chk WHERE artid=$artid AND field='$field' AND tabid='$tabid'");
  }
?>