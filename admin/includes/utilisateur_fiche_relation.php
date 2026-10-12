<div class="besoins">
	<center><span class="title_infos">Showroom</span></center>
	<div style="float:left;">Dates de passage au showroom :</div>
	<div style="float:right;">
		<form method="post" name="add_showroom_form" action="utilisateur.php?action=editer&userid=<?= $utilisateur['userid'] ?>">
			<input type="hidden" name="action" value="editer">
			<input type="hidden" name="add_showroom" value="1">
			<input type="hidden" name="userid" value="<?= $utilisateur['userid'] ?>\">
			<input type="text" class="date" id="taskedate" size="10" maxlength="10" name="date_showroom" value="">
			<a href="javascript:void(0);" onclick="document.add_showroom_form.submit();"><img src="/admin/images/icon/edit_add.png" alt="ajouter" align="absmiddle"></a>
		</form>
	</div>
	<div class="clear"></div>
	<?php
	$sql="SELECT * FROM utilisateur_showroom WHERE userid='".$utilisateur['userid']."' ORDER BY dateline ASC";
	$utilisateur_showrooms = $DB_site2->query($sql);
	$count_utilisateur_showroom=$DB_site2->num_rows($utilisateur_showrooms);
	if($count_utilisateur_showroom>0){
		echo "<div style=\"background-color:#fff; padding:5px;\">";
	}
	$virgule="";
	while($utilisateur_showroom=$DB_site2->fetch_array($utilisateur_showrooms)){
		echo $virgule."Le ".date("d/m/Y", $utilisateur_showroom['dateline']);
		echo " &nbsp; <a href=\"javascript:void(0);\" onclick=\"document.del_showroom_".$utilisateur_showroom['utilisateur_showroomid'].".submit();\"><img src=\"images/icon/cancel.png\" alt=\"\" style=\"width:15px; height:15px;\"></a>";
		echo "<form method=\"post\" name=\"del_showroom_".$utilisateur_showroom['utilisateur_showroomid']."\" action=\"utilisateur.php?action=editer&userid=".$utilisateur['userid']."\" style=\"display: none;\">
				<input type=\"hidden\" name=\"action\" value=\"editer\">
				<input type=\"hidden\" name=\"del_showroom\" value=\"1\">
				<input type=\"hidden\" name=\"utilisateur_showroomid\" value=\"".$utilisateur_showroom['utilisateur_showroomid']."\">
			</form>";
		$virgule=" , ";
	}
	if($count_utilisateur_showroom>0){
		echo "<div class=\"clear\"></div></div><div class=\"clear\"></div>";
	}
	?>
</div>

<div class="besoins">
	<center><span class="title_infos">Relation(s)</span></center>
	<br>
	<div style="float:left;"><b>Ce contact a été envoyé par :</b></div>
	<?php
	echo tableau_relation(1, 1);
	?>
	<div class="clear"></div><br>
	<div style="float:left;"><b>Ce contact nous a envoyé :</b></div>
	<?php
	echo tableau_relation(1, 2);
	?>
	<div class="clear"></div>
	<br>
	<div style="float:left;"><b>Ce contact est l'architecte de :</b></div>
	<?php
	echo tableau_relation(2, 1);
	?>
	<div class="clear"></div>
	<br>
	<div style="float:left;"><b>Ce contact a pour architecte :</b></div>
	<?php
	echo tableau_relation(2, 2);
	?>
	<div class="clear"></div>
	<br>
	<div style="float:left;"><b>Ce contact a un deuxième compte :</b></div>
	<?php
	echo tableau_relation(3, 1);
	echo tableau_relation(3, 2,'non');
	?>
</div>
<!--<div class="besoins" id="show_echeance_projet" style="max-width: 450px;">
	<a href="javascript:void(0)" onclick="affiche_div('edit_echeance_projet', 'show_echeance_projet');"><img src="<?php echo BACK_URL; ?>/images/icon/edit.png" style="float:right;"></a>
	<center><span class="title_infos">Échéance Projet</span></center>
	<br>
	<?php
		if(is_null($utilisateur['echeance_projet']) || $utilisateur['echeance_projet'] == '0000-00-00'){
			$utilisateur_echeance_projet="-";
		} else {
			$dateEch = new DateTime($utilisateur["echeance_projet"]);
			$utilisateur_echeance_projet=$dateEch->format('d/m/Y');
		}
		if(is_null($utilisateur['livraison_projet']) || $utilisateur['livraison_projet'] == '0000-00-00'){
			$utilisateur_livraison_projet="-";
		} else {
			$dateEch = new DateTime($utilisateur["livraison_projet"]);
			$utilisateur_livraison_projet=$dateEch->format('d/m/Y');
		}
		//if(($utilisateur['echeance_projet'] == '0000-00-00' || $utilisateur['echeance_projet'] == null) && ($utilisateur['budget_projet'] == "0" || $utilisateur['budget_projet'] == null)){
			
		//}else{
		  echo(
		  '<table style="border-style: none; border-radius: 10px;text-align:center; background-color: #cccccc; width:100%;">
			  <tr>
				  <td>
					  <strong>Date commande </strong>: 
				  </td>
				  <td>
					  <strong>Date livraison </strong>: 
				  </td>
				  <td>
					  <strong>Budget </strong>: 
				  </td>
			  </tr>
			  <tr>
				  <td>');
					 // if($utilisateur["echeance_projet"] !== "0000-00-00"){
						  echo $utilisateur_echeance_projet;
					 // }
					  echo(
				  '</td>
				  <td>');
					 // if($utilisateur["echeance_projet"] !== "0000-00-00"){
						  echo $utilisateur_livraison_projet;
					 // }
					  echo(
				  '</td>
				  <td>'
					  .number_format((float)$utilisateur["budget_projet"], 2, ",", " ").'€
				  </td>
			  </tr>
		  </table>');
		//}
	?>
		<br><strong>Infos diverses</strong> :<br>
        <?php echo $utilisateur['infos']; ?>
</div>-->
<div id="edit_echeance_projet" class="infos_client" style="display: none;">
	 <form method="post" name="form_echeance_projet" style="padding: 0; margin: 0;" enctype="multipart/form-data">
        <input type="hidden" name="userid" value="<?php echo $utilisateur['userid']; ?>">
        <input type="hidden" name="action" value="editer">
        <input type="hidden" name="edituser" value="1">
        <table class="edit">
        	<tr>
				<td>
					<strong>Date commande </strong>: 
				</td>
				<td>
					<input type="date" name="echeance_projet" id="datepicker1" value ="<?php if($utilisateur['echeance_projet']!=='0000-00-00') {echo $utilisateur['echeance_projet']; }?>">                                                                        
				</td>
			</tr>
			<tr>
				<td>
					<strong>Date livraison </strong>: 
				</td>
				<td>
					<input type="date" name="livraison_projet" id="datepicker2" value ="<?php if($utilisateur['livraison_projet']!=='0000-00-00') {echo $utilisateur['livraison_projet']; }?>">                                                                        
				</td>
			</tr>
			<tr>
				<td>
					<strong>Budget </strong>: 
				</td>
				<td>
					<span class="input-euro right">
						<input style="after-content:'€';" type="text" name="budget_projet" value=<?php if($utilisateur['budget_projet'] !==0 && $utilisateur['budget_projet'] !== NULL){echo $utilisateur['budget_projet'];} ?>>
					</span>
				</td>
			</tr>
			 <tr>
                <td>
                     Infos Projet :
                </td>
                <td>
                     <textarea name="infos"><?php echo $utilisateur['infos'] ?></textarea>
                </td>
           </tr>
			<tr>
                <td colspan="2" style="text-align: center; padding: 5px 0 0 0;">
                     <a href="javascript:void(0);" title="Valider" onclick="document.forms.form_echeance_projet.submit();"><img src="<?php echo BACK_URL; ?>/images/icon/succes.png" alt="Valider"></a>
                     <a href="javascript:void(0);" title="Annuler" onclick="affiche_div('show_echeance_projet', 'edit_echeance_projet'); document.forms.photoAEnvoyer.submit();"><img src="<?php echo BACK_URL; ?>/images/icon/cancel.png" alt="Annuler"></a>
                </td>
           </tr>
        </table>
    </form>

</div>



<?php
function tableau_relation($genre=1, $sens=1, $etendu="oui"){
	global $utilisateur;
	global $DB_site2;
    $return= '';
	if($etendu=="oui"){
		$return="<div style=\"float:right;\"><a href=\"javascript:void(0);\" onclick=\"document.getElementById('add_relation_".$genre."_".$sens."').style.display='block';\">Nouvelle relation</a></div><div class=\"clear\"></div>";
	}
	if($sens==1){
		$autresens=2;
	} else {
		$autresens=1;
	}
	$sql="SELECT * FROM utilisateur_relation
			WHERE relation_genre='".$genre."'
			AND user".$sens."id='".$utilisateur['userid']."' ";
	$contacts = $DB_site2->query($sql);
	$count_contact=$DB_site2->num_rows($contacts);
	if($count_contact>0){
		$return.="<div style=\"background-color:#fff; padding:5px;\">";
	}
	while($contact=$DB_site2->fetch_array($contacts)){
		$userid=$contact['user'.$autresens.'id'];
		$fournisseur=$contact['fournisseur_societe'];
		$relationid=$contact['relationid'];
		if($userid!=0){ // c'est un utilisateur
			$sql_contact="SELECT userid, raisonsociale, prenom, nom FROM utilisateur WHERE userid='".$userid."' ";
			$coordonnees=$DB_site2->query_first($sql_contact);
			if($coordonnees['userid']==$userid){
				$libelle="";
				if($coordonnees['raisonsociale']!=""){
					$libelle=$coordonnees['raisonsociale']." - ";
				}
				$libelle.=$coordonnees['prenom']." ".$coordonnees['nom'];
				$lien="utilisateur.php?action=editer&userid=".$userid;
				$type="client - ".$userid;
			} else {
				$libelle="<strong>erreur : client introuvable</strong>";
			}
		} else { // c'est un fournisseur
			$sql_contact="SELECT societe FROM fournisseur WHERE societe='".mysqli_real_escape_string($DB_site2->link_id,$fournisseur)."' ";
			$coordonnees=$DB_site2->query_first($sql_contact);
			if($coordonnees['societe']==$fournisseur){
				$libelle=$fournisseur;
				$lien="fournisseurs_liste.php?action=editer&fournisseur=".urlencode($fournisseur);
				$type="fournisseur";
			} else {
				$libelle="<strong>erreur : fournisseur introuvable</strong>";
			}
		}
		$return.="<a href=\"".$lien."\" target=\"_blank\">".$libelle."</a> (".$type.") ".$contact['relation_description'];
		$return.=" &nbsp; <a href=\"javascript:void(0);\" onclick=\"document.getElementById('edit_relation_".$relationid."').style.display='block';\"><img src=\"images/icon/edit.png\" alt=\"\" style=\"width:15px; height:15px;\"></a>";
		$return.="<form method=\"post\" id=\"edit_relation_".$relationid."\" action=\"utilisateur.php?action=editer&userid=".$utilisateur['userid']."\" style=\"display: none;\">
				<input type=\"hidden\" name=\"action\" value=\"editer\">
				<input type=\"hidden\" name=\"edit_relation\" value=\"1\">
				<input type=\"hidden\" name=\"relationid\" value=\"".$relationid."\">
				<input type=\"text\" name=\"relation_description\" value=\"".$contact['relation_description']."\">
				<input type=\"submit\" value=\"enregistrer\" style=\"display: block;\">
			</form>";
		$return.=" &nbsp; <a href=\"javascript:void(0);\" onclick=\"document.del_relation_".$relationid.".submit();\"><img src=\"images/icon/cancel.png\" alt=\"\" style=\"width:15px; height:15px;\"></a><br>";
		$return.="<form method=\"post\" name=\"del_relation_".$relationid."\" action=\"utilisateur.php?action=editer&userid=".$utilisateur['userid']."\">
				<input type=\"hidden\" name=\"action\" value=\"editer\">
				<input type=\"hidden\" name=\"del_relation\" value=\"1\">
				<input type=\"hidden\" name=\"relationid\" value=\"".$relationid."\">
			</form>";
	}
	if($etendu=="oui"){
		$return.="	<div id=\"add_relation_".$genre."_".$sens."\" class=\"infos_client\" style=\"display: none; width: 370px; margin-top:10px;\">
						<form method=\"post\" action=\"utilisateur.php?action=editer&userid=".$utilisateur['userid']."\" id=\"form_add_relation_".$genre."_".$sens."\">
							<input type=\"hidden\" name=\"action\" value=\"editer\">
							<input type=\"hidden\" name=\"add_relation\" value=\"1\">
							<input type=\"hidden\" name=\"relation_genre\" value=\"".$genre."\">
							<input type=\"hidden\" name=\"relation_sens\" value=\"".$sens."\">
							<input type=\"hidden\" name=\"user".$sens."id\" value=\"".$utilisateur['userid']."\">
							client ou fournisseur :<br><input type=\"text\" id=\"utilisateur_relation_label_".$genre."_".$sens."\" value=\"\">
							<span  id=\"utilisateur_relation_showtype_".$genre."_".$sens."\"></span>
							<input type=\"hidden\" id=\"utilisateur_relation_id_".$genre."_".$sens."\" name=\"id\" value=\"\">
							<input type=\"hidden\" id=\"utilisateur_relation_type_".$genre."_".$sens."\" name=\"relation_type\" value=\"\">
							<br>description :<br> <input type=\"text\" name=\"relation_description\" value=\"\">
							<input type=\"button\" value=\"ajouter\" style=\"display: block;\" id=\"btn_add_relation_".$genre."_".$sens."\">
						</form>
					</div>";
	}
	if($count_contact>0){
		$return.="</div>";
	}
	return $return;
}
	
?>
<script>

var $jq = jQuery.noConflict();
$jq(document).ready(function() {
  $jq('.date').datepicker($jq.datepicker.regional['fr']);
  $jq('#utilisateur_relation_label_1_1').autocomplete({
    source: 'ajax/relation_search.php?ignore=<?= $utilisateur['userid'] ?>',
    minLength: 2,
    select: function( event, ui ) {
      $jq('#utilisateur_relation_id_1_1').val(ui.item.id);
      $jq('#utilisateur_relation_type_1_1').val(ui.item.type2);
      $jq('#utilisateur_relation_showtype_1_1').text(ui.item.type);
    }
  })
  .data('ui-autocomplete')._renderItem = function(ul, item) {
    return $jq('<li>')
      .append('<a>'+item.value+'<br><span style="font-size:8px">'+item.type+'</span></a>')
      .appendTo(ul);
  };
  $jq('#utilisateur_relation_label_1_2').autocomplete({
    source: 'ajax/relation_search.php?ignore=<?= $utilisateur['userid'] ?>',
    minLength: 2,
    select: function( event, ui ) {
      $jq('#utilisateur_relation_id_1_2').val(ui.item.id);
      $jq('#utilisateur_relation_type_1_2').val(ui.item.type2);
      $jq('#utilisateur_relation_showtype_1_2').text(ui.item.type);
    }
  })
  .data('ui-autocomplete')._renderItem = function(ul, item) {
    return $jq('<li>')
      .append('<a>'+item.value+'<br><span style="font-size:8px">'+item.type+'</span></a>')
      .appendTo(ul);
  };
  $jq('#utilisateur_relation_label_2_1').autocomplete({
    source: 'ajax/relation_search.php?ignore=<?= $utilisateur['userid'] ?>',
    minLength: 2,
    select: function( event, ui ) {
      $jq('#utilisateur_relation_id_2_1').val(ui.item.id);
      $jq('#utilisateur_relation_type_2_1').val(ui.item.type2);
      $jq('#utilisateur_relation_showtype_2_1').text(ui.item.type);
    }
  })
  .data('ui-autocomplete')._renderItem = function(ul, item) {
    return $jq('<li>')
      .append('<a>'+item.value+'<br><span style="font-size:8px">'+item.type+'</span></a>')
      .appendTo(ul);
  };
  $jq('#utilisateur_relation_label_2_2').autocomplete({
    source: 'ajax/relation_search.php?ignore=<?= $utilisateur['userid'] ?>',
    minLength: 2,
    select: function( event, ui ) {
      $jq('#utilisateur_relation_id_2_2').val(ui.item.id);
      $jq('#utilisateur_relation_type_2_2').val(ui.item.type2);
      $jq('#utilisateur_relation_showtype_2_2').text(ui.item.type);
    }
  })
  .data('ui-autocomplete')._renderItem = function(ul, item) {
    return $jq('<li>')
      .append('<a>'+item.value+'<br><span style="font-size:8px">'+item.type+'</span></a>')
      .appendTo(ul);
  };


  $jq('#utilisateur_relation_label_3_1').autocomplete({
    source: 'ajax/relation_search.php?ignore=<?= $utilisateur['userid'] ?>',
    minLength: 2,
    select: function( event, ui ) {
      $jq('#utilisateur_relation_id_3_1').val(ui.item.id);
      $jq('#utilisateur_relation_type_3_1').val(ui.item.type2);
      $jq('#utilisateur_relation_showtype_3_1').text(ui.item.type);
    }
  })
  .data('ui-autocomplete')._renderItem = function(ul, item) {
    return $jq('<li>')
      .append('<a>'+item.value+'<br><span style="font-size:8px">'+item.type+'</span></a>')
      .appendTo(ul);
  };
  
  $jq('#btn_add_relation_1_1').click(function() {
	  if($jq('#utilisateur_relation_type_1_1').val()!=""){
		$jq('#form_add_relation_1_1').submit();
		console.log('1_1');
	  }else{
		alert('veuillez sélectionner un client ou un fournisseur');
	  }
  });
  $jq('#btn_add_relation_1_2').click(function() {
	  if($jq('#utilisateur_relation_type_1_2').val()!=""){
		$jq('#form_add_relation_1_2').submit();
		console.log('1_2');
	  }else{
		alert('veuillez sélectionner un client ou un fournisseur');
	  }
  });
  $jq('#btn_add_relation_2_1').click(function() {
	  if($jq('#utilisateur_relation_type_2_1').val()!=""){
		$jq('#form_add_relation_2_1').submit();
		console.log('2_1');
	  }else{
		alert('veuillez sélectionner un client ou un fournisseur');
	  }
  });
  $jq('#btn_add_relation_2_2').click(function() {
	  if($jq('#utilisateur_relation_type_2_2').val()!=""){
		$jq('#form_add_relation_2_2').submit();
		console.log('2_2');
	  }else{
		alert('veuillez sélectionner un client ou un fournisseur');
	  }
  });
  $jq('#btn_add_relation_3_1').click(function() {
	  if($jq('#utilisateur_relation_type_3_1').val()!=""){
		$jq('#form_add_relation_3_1').submit();
		console.log('3_1');
	  }else{
		alert('veuillez sélectionner un client ou un fournisseur');
	  }
  });


});
</script>
