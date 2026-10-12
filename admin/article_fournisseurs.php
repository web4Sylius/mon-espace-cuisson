 <?php

 // MULTISITE
 
  function fournisseurs_css() {
    return <<<EOL
      #fournisseurs { clear:both; margin-top:20px}
      #fournisseurs table caption {font: bold 11px "Vernada",serif; color:#363E42; margin-bottom:1em}
      #fournisseurs table th {font: bold 12px sans-serif}
      #fournisseurs table tr:nth-child(even) {background:#DDF}
      #fournisseurs .prix {text-align:right}
      .fournisseur {cursor:pointer}
EOL;
  }

  function fournisseurs_js() {
    return <<<EOL
    updatefmarges();
      function setprix(fourn_id){
// 		updatefmarges();
		for(i=0;i<sites.length;i++){
		    var btnlist=document.getElementsByName('fourn_principal['+sites[i]+']');
		    for(r=0;r<btnlist.length;r++){
			if(btnlist[r].checked){
			    var fourn_id=btnlist[r].value;
			  
			    var prix = Number(document.getElementsByName('fourn_prix_' + fourn_id)[0].value);
			    var prixtrspt=Number(document.getElementsByName('fourn_trspt_' + fourn_id)[0].value);
			    var ecopartht = Number(document.getElementsByName('fourn_ecopartht_' + fourn_id)[0].value);
			    var tvapc = Number(document.getElementsByName('tvapc['+sites[i]+']')[0].value);
			    
			    document.getElementsByName('paht['+sites[i]+']')[0].value=prix;
			    document.getElementsByName('prixtrspt['+sites[i]+']')[0].value=prixtrspt;
			    
			    setmodif();
			    calctxtva(sites[i]);
			    calcmarge('',sites[i]);
			}
		    }
		}
      }

      function updatefmarges() {
		fournisseurs = document.getElementsByName('fourn_principal');
		for (fourn in fournisseurs) {
		  fourn_id = fournisseurs[fourn].value;
		  if (fourn_id == undefined)
		    break;
// 		  if(!isNaN(parseFloat(document.getElementsByName('fourn_prix_'+fourn_id)[0].value))) calcmarge('', 'fourn_prix_' + fourn_id, 'fourn_marge_' + fourn_id, document.getElementsByName('fourn_trspt_'+fourn_id)[0].value);
		}
      }
      
	  
	  function delfournisseur(id){
		  document.getElementsByName('delfournisseur')[0].value=id;
		  clique('prix','prix');
      }
EOL;

  }
  function show_fournisseurs($db, $mq, $artid) {
      global $devises;
      global $sites;
      
      $lot=$db->query_first("SELECT COUNT(*) FROM article_lot WHERE lotid=".$artid);
      if($lot[0]>0)
	return show_fournisseurs_lot($db,$artid);
      
      foreach($sites as $site_code=>$site) $tdprincipal.="<th>Prl $site_code</th>";
      
      $content = <<<EOL
	  <input type="hidden" name="delfournisseur" value="">
	  <table>
	  <caption>Fournisseurs</caption>
	    <tr>
	      $tdprincipal
	      <th>Fournisseur</th>
	      <th>Prix</th>
	      <th>Ecopart HT</th>
	      <th>Trspt</th>
	      <th>Marge</th>
	      <th>Délai livr.</th>
	      <th>Réappro</th>
	    </tr>
EOL;
      $marque = $db->query_first("
	SELECT `marqueid`
	FROM `article_marque`
	WHERE `artid` = '$artid'
      ");
      $marque = $marque['marqueid'];
      $fournisseurs_dispo = $db->query("
	SELECT f.societe AS nom, f.marques, f.id, af.prix, af.ecopartht, af.principal, af.trspt, af.devise, af.delai, af.reappro,af.id AS afid
	FROM `fournisseur` f
	LEFT OUTER JOIN `article_fournisseur` af ON af.fournisseur= f.societe
	WHERE `artid` LIKE '$artid' AND f.type='fournadv' GROUP BY societe ORDER BY societe
	");
      $check_fourn = array();
      while ($fournisseur = $db->fetch_array($fournisseurs_dispo)) {
	if ($marque && !in_array($marque, explode(',', $fournisseur['marques'])))
	  continue ;
	
	$fournisseur['principal']=explode(",",$fournisseur['principal']);
	$check_fourn[$fournisseur['id']] = true;
	$reapprodmy=($fournisseur['reappro']>0?date("d/m/Y",$fournisseur['reappro']):"");
	$devisetxt=$devises->symbole($fournisseur['devise']);
	
	$tdprincipal="";
	foreach($sites as $site_code=>$site){
		$principal = (in_array($site_code,$fournisseur['principal']))?'checked':NULL;
		$tdprincipal.='<td>
		    <input type="radio" name="fourn_principal['.$site_code.']" value="'.$fournisseur['id'].'" '.$principal.' onclick="setprix('.$fournisseur['id'].')">
		</td>';
	}
	
	$content .= <<<EOL
	  <tr class="fournisseur">
	    $tdprincipal
	    <td>
	      $fournisseur[nom]
	      <input type="hidden" name="fourn_name_$fournisseur[id]" value="$fournisseur[nom]">
	    </td>
	    <td class="prix">
	      <input type="hidden" name="devise_$fournisseur[id]" value="$fournisseur[devise]">
	      <input type="text" name="fourn_prix_$fournisseur[id]" value="$fournisseur[prix]" size="5" onkeyup="setprix($fournisseur[id])" autocomplete="off" />&nbsp;$devisetxt
	    </td>
	    <td>
	      <input type="text" name="fourn_ecopartht_$fournisseur[id]" value="$fournisseur[ecopartht]" size="5" onkeyup="setprix($fournisseur[id])" autocomplete="off" />&nbsp;$devisetxt
	    </td>
	    <td>
	      <input type="text" name="fourn_trspt_$fournisseur[id]" size="5" value="$fournisseur[trspt]" onkeyup="setprix($fournisseur[id])" autocomplete="off" >&nbsp;$devisetxt
	    </td>
	    <td id="fourn_marge_$fournisseur[id]"></td>
	    <td>
	      <input type="text" name="fourn_delai_$fournisseur[id]" size="5" value="$fournisseur[delai]" onkeyup="setprix($fournisseur[id])">&nbsp;j
	    </td>
	    <td>
	      <input type="text" id="fourn_reappro_$fournisseur[id]" name="fourn_reappro_$fournisseur[id]" size="8" value="$reapprodmy" onkeyup="setprix($fournisseur[id]);" onclick="calendrier_show(this.id,this.value,undefined,setprix,$fournisseur[id]);">
	    </td>
	    <td><img src="images/supprimer.gif" onclick="delfournisseur($fournisseur[afid])"></td>
	  </tr>
EOL;
      }

      //TODO: ne pas dupliquer le code juste au dessus
      $fournisseurs_dispo = $db->query("SELECT societe AS nom, marques, id FROM `fournisseur` WHERE type='fournadv' GROUP BY societe");
      while ($fournisseur = $db->fetch_array($fournisseurs_dispo)) {
	$conditions=$db->query_first("SELECT devise FROM fournisseur_condition WHERE societe='".mysqli_real_escape_string($db->link_id,$fournisseur['nom'])."'");
	if($conditions['devise']=="") $conditions['devise']=$devises->defaut;
	$devisetxt=$devises->symbole($conditions['devise']);
	
	if ($marque && !in_array($marque, explode(',', $fournisseur['marques'])))
	  continue ;
	if ($check_fourn[$fournisseur['id']])
	  continue ;
	
	$tdprincipal="";
	foreach($sites as $site_code=>$site)
		$tdprincipal.='<td>
		    <input type="radio" name="fourn_principal['.$site_code.']" value="'.$fournisseur['id'].'" onclick="setprix('.$fournisseur['id'].')">
		    <input type="hidden" name="fourn_name_'.$fournisseur['id'].'" value="'.$fournisseur['nom'].'">
		</td>';	
	
	$content .= <<<EOL
	  <tr class="fournisseur">
	    $tdprincipal
	    <td>$fournisseur[nom]</td>
	    <td class="prix">
	      <input type="hidden" name="devise_$fournisseur[id]" value="$conditions[devise]">
	      <input type="text" name="fourn_prix_$fournisseur[id]" size="5" onkeyup="setprix($fournisseur[id])" />&nbsp;$devisetxt
	    </td>
	    <td>
	      <input type="text" name="fourn_ecopartht_$fournisseur[id]" size="5" onkeyup="setprix($fournisseur[id])" autocomplete="off" />&nbsp;$devisetxt
	    </td>
	    <td>
	      <input type="text" name="fourn_trspt_$fournisseur[id]" size="5" onkeyup="setprix($fournisseur[id])">&nbsp;$devisetxt
	    </td>
	    <td id="fourn_marge_$fournisseur[id]"></td>
	    <td>
	      <input type="text" name="fourn_delai_$fournisseur[id]" size="5" onkeyup="setprix($fournisseur[id])">&nbsp;j
	    </td>
	    <td>
	      <input type="text" id="fourn_reappro_$fournisseur[id]" name="fourn_reappro_$fournisseur[id]" size="8" onkeyup="setprix($fournisseur[id]);" onclick="calendrier_show(this.id,this.value,undefined,setprix,$fournisseur[id]);">
	    </td>
	    <td><img src="images/ajouter.gif" onclick="clique('prix','prix')"></td>
	  </tr>
EOL;
      }
      $content .= '</table>';
      return $content;
  }
  
  function show_fournisseurs_lot($db,$artid){
      global $devises;
      global $sites;

      foreach($sites as $site_code=>$site){
	  $af=$db->query_first("SELECT * FROM article_fournisseur WHERE artid=".$artid." AND principal='".$site_code."'");
	  $paht=floatval($af['prix']);
	  $ecopartht=floatval($af['ecopartht']);
	  $trspt=floatval($af['trspt']);
	  $devise=$af['devise']!=""?$af['devise']:"EUR";
	  
	  $content.=<<<HTML
	    <input type="hidden" name="fourn_principal[$site_code]" value="$site_code">
	    <input type="hidden" name="fourn_name_$site_code" value="$site_code">
	    <input type="hidden" name="devise_$site_code" value="$devise">
	    <input type="hidden" name="fourn_prix_$site_code" value="$paht">
	    <input type="hidden" name="fourn_ecopartht_$site_code" value="$ecopartht">
	    <input type="hidden" name="trspt_$site_code" value="$trspt">
HTML;
	  
      }
      
      return $content;
  }
  
  function update_fournisseurs($db, $artid, $form) {
    if (!is_numeric($artid))
      return false;

    foreach ($form as $field => $value) {
      if (preg_match('/fourn_prix_(\w+)/', $field, $data)) {
	$fourn=$_POST['fourn_name_'.$data[1]];
	$ecopartht=floatval($_POST['fourn_ecopartht_'.$data[1]]);
	$trspt=$_POST['fourn_trspt_'.$data[1]];
	$delai=$_POST['fourn_delai_'.$data[1]];
	$devise=$_POST['devise_'.$data[1]];
	$reappro=strtots($_POST['fourn_reappro_'.$data[1]]);
	if($value>0){
	    create_fournisseur_entry($db, $artid, $fourn);
	    $db->query("
	      UPDATE `article_fournisseur`
	      SET `prix` = '$value', `trspt`='$trspt', ecopartht='$ecopartht', `delai`='$delai', `reappro`='$reappro', devise='$devise'
	      WHERE `artid` = '$artid' AND `fournisseur` = '".mysqli_real_escape_string($db->link_id,$fourn)."'
	    ");
	}
      }
    }
    
    if (isset($form[fourn_principal])) {

      $db->query("
	UPDATE `article_fournisseur`
	SET `principal` = ''
	WHERE `artid` = '$artid'
      ");
      
      if(is_array($form[fourn_principal])){
	$principaux=array();
	foreach($form[fourn_principal] as $site_code=>$fourn_id) $principaux[$_POST['fourn_name_'.$fourn_id]][]=$site_code;
	foreach($principaux as $fourn=>$site_codes)
	  $db->query("
	    UPDATE `article_fournisseur`
	    SET `principal` = '".implode(",",$site_codes)."'
	    WHERE `artid` = '$artid' AND `fournisseur` = '".mysqli_real_escape_string($db->link_id,$fourn)."'
	  ");	  
      }
    }

    if($_POST['delfournisseur']!=="") $db->query("DELETE FROM article_fournisseur WHERE id=".intval($_POST['delfournisseur']));
  }
  
  
  function create_fournisseur_entry($db, $artid, $fourn) {
    if (!is_numeric($artid) || !isset($fourn))
      return false;
      
    $data = $db->query_first("
      SELECT COUNT(`id`)
      FROM `article_fournisseur`
      WHERE `artid` = $artid AND `fournisseur` = '".mysqli_real_escape_string($db->link_id,$fourn)."'");

    if (!$data[0])
      $data = $db->query("
	INSERT INTO `article_fournisseur`
	SET `artid` = $artid, `fournisseur` = '".mysqli_real_escape_string($db->link_id,$fourn)."'
      ");
    return ($data);
  }
 ?>
