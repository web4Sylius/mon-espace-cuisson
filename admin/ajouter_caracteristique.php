<?php

require "admin_global.php";
include "./header.php";
include "./menu.php";

ini_set("display_errors",1);

$filter_langue=count(array_diff($langues,$langue_access))>0;
unset($langues[2]); //supprime l'espagnol 
$caractid=isset($_POST['caractid']) ? intval($_POST['caractid']) : intval($_GET['caractid']);


// monter une valeur
if (isset($_GET['action2'])){
    switch($_GET['action2']){

    case "up":
	$position=$DB_site2->query_first("SELECT position FROM caracteristiquevaleur WHERE caractvalid = '$_GET[caractvalid]'");						
	$nouvelle_position_marque = $position[0] - 1 ;
	$position2=$DB_site2->query_first("SELECT caractvalid FROM caracteristiquevaleur WHERE position = '$nouvelle_position_marque' and caractid = '$caractid'");
	$DB_site2->query("UPDATE caracteristiquevaleur SET position = '$nouvelle_position_marque' WHERE caractvalid = '$_GET[caractvalid]'");
	$DB_site2->query("UPDATE caracteristiquevaleur SET position = '$position[0]' WHERE caractvalid = '$position2[caractvalid]'");
    break;

// descendre une valeur
    case "down":
	$position=$DB_site2->query_first("SELECT position FROM caracteristiquevaleur WHERE caractvalid = '$_GET[caractvalid]'");						
	$nouvelle_position_marque = $position[0] + 1 ;
	$position2=$DB_site2->query_first("SELECT caractvalid FROM caracteristiquevaleur WHERE position = '$nouvelle_position_marque' and caractid = '$caractid'" );
	$DB_site2->query("UPDATE caracteristiquevaleur SET position = '$nouvelle_position_marque' WHERE caractvalid = '$_GET[caractvalid]'");
	$DB_site2->query("UPDATE caracteristiquevaleur SET position = '$position[0]' WHERE caractvalid = '$position2[caractvalid]'");
    break;
    
    case 'del_vignette':
	$sql = "UPDATE caracteristiquevaleur SET ";
	$sql .= "image = 0";
	$sql .= " WHERE caractvalid = '".$_GET['caractvalid']."'";
	$DB_site2->query($sql);
    
	@unlink($rootpath."/configurations/$host/images/caracteristiquevaleur/".$_GET['caractvalid'].".jpg");
	break;
    
    case 'del_zoom':
	@unlink($rootpath."/configurations/$host/images/caracteristiquevaleur/zoom/".$_GET['caractvalid'].".jpg");
	break;
    }
}


if(isset($_POST['action'])){

// modifier les valeurs (supprimer...)
    switch($_POST['action']){

	// modifier la caracteristique
	case "modify":

	$erreur=0;
	$action="edit";

	$sql="UPDATE caracteristique SET descriptioninterne='".mysqli_real_escape_string($DB_site2->link_id,$_POST['descriptioninterne'])."', marqueid = ".$_POST['marque_id'];
	foreach($langues as $l) {
	    $sql.=",libelle".$l."='".mysqli_real_escape_string($DB_site2->link_id,$_POST['libelle'.$l])."', description".$l."='".mysqli_real_escape_string($DB_site2->link_id,$_POST['description'.$l])."'";
	}
	$sql.=" WHERE caractid = '$caractid'";
	if ($erreur==0) $DB_site2->query($sql);
	break;
	
	case "add":
	
	$maxposition=$DB_site2->query_first("SELECT MAX(position) FROM caracteristique WHERE marqueid = '".$_POST['marque_id']."'");
	$maxposition=$maxposition[0]+1;
	
	$sql="INSERT INTO caracteristique SET descriptioninterne='".mysqli_real_escape_string($DB_site2->link_id,$_POST['descriptioninterne'])."', position=".$maxposition.", marqueid = ".$_POST['marque_id'];
	foreach($langues as $l) {
	    $sql.=",libelle".$l."='".mysqli_real_escape_string($DB_site2->link_id,$_POST['libelle'.$l])."', description".$l."='".mysqli_real_escape_string($DB_site2->link_id,$_POST['description'.$l])."'";
	}
	$DB_site2->query($sql);
		
	$caractid=$DB_site2->insert_id();
	$action="edit";
	unset($_POST);
		
	break;
    
	case "modifyvals":
	$caractvals=$DB_site2->query("SELECT * from caracteristiquevaleur where caractid = '".$_POST['caractid']."'");
	while($caractval=$DB_site2->fetch_array($caractvals)){

		//supprime l'option
    	if(isset($_POST['delete_option']) && $_POST['delete_option'] != ''){
    		$DB_site2->query("DELETE FROM caracteristiquevaleur WHERE caractvalid = ".$_POST['delete_option']);
    	}

		///prix fixe à zero
		if($_POST['prix_fixe_'.$caractval['caractvalid']] == '2'){
			$_POST['prix_fixe_'.$caractval['caractvalid']] = '1';
			$_POST['prix_achat_'.$caractval['caractvalid']] = '0';
			$_POST['prix_vente_'.$caractval['caractvalid']] = '0';
			$_POST['prix_vente_ttc_'.$caractval['caractvalid']] = '0';
		}

	    $sql = "UPDATE caracteristiquevaleur SET ";
	    $sql .= "ref = '".mysqli_real_escape_string($DB_site2->link_id,$_POST['ref'][$caractval['caractvalid']])."', prix_fixe = ".$_POST['prix_fixe_'.$caractval['caractvalid']];

	    ///si prix variable, on supprime les montants fixes
	    if($_POST['prix_fixe_'.$caractval['caractvalid']] == '0'){
			$sql .= ", paht =null,pvht = null,pvttc = null";
		}
	    elseif(isset($_POST['prix_achat_'.$caractval['caractvalid']]) && $_POST['prix_achat_'.$caractval['caractvalid']] != ''){
	    	///on calcule le prix vente ttc et on l'enregistre aussi
			$sql .= ", paht =".$_POST['prix_achat_'.$caractval['caractvalid']].",pvht = ".$_POST['prix_vente_'.$caractval['caractvalid']].", pvttc = ".$_POST['prix_vente_ttc_'.$caractval['caractvalid']];
		}

	    foreach($langues as $langue){
            $sql .= ", libelle".$langue." = '".mysqli_real_escape_string($DB_site2->link_id,$_POST['libelle'.$langue][$caractval['caractvalid']])."'";
            $sql .= ", description".$langue." = '".mysqli_real_escape_string($DB_site2->link_id,$_POST['description'.$langue][$caractval['caractvalid']])."'";
	    }
	    $sql .= " WHERE caractvalid = '".$caractval['caractvalid']."'";

	    $DB_site2->query($sql);
	    
	    $file_uploaded_tmpname = isset($_FILES['file']['tmp_name'][$caractval['caractvalid']]) ? $_FILES['file']['tmp_name'][$caractval['caractvalid']] : '';
	    $zoom_uploaded_tmpname = isset($_FILES['zoom']['tmp_name'][$caractval['caractvalid']]) ? $_FILES['zoom']['tmp_name'][$caractval['caractvalid']] : '';
	    
	    if($file_uploaded_tmpname != ''){
            $sql = "UPDATE caracteristiquevaleur SET ";
            $sql .= "image = 1";
            $sql .= " WHERE caractvalid = '".$caractval['caractvalid']."'";
            $DB_site2->query($sql);
            
            $file_dest_name = $_SERVER['DOCUMENT_ROOT']."/configurations/".$host."/images/caracteristiquevaleur/".$caractval['caractvalid'].".jpg";
            @unlink($file_dest_name);
    // 		  move_uploaded_file($file_uploaded_tmpname, $file_dest_name);
            redimentionner_image($file_uploaded_tmpname,$file_dest_name,300,300);
	    }

	    if($zoom_uploaded_tmpname != ''){
            $zoom_dest_name = $_SERVER['DOCUMENT_ROOT']."/configurations/".$host."/images/caracteristiquevaleur/zoom/".$caractval['caractvalid'].".jpg";
            @unlink($zoom_dest_name);
    // 		  move_uploaded_file($zoom_uploaded_tmpname, $zoom_dest_name);
            redimentionner_image($zoom_uploaded_tmpname, $zoom_dest_name,600,600);
	    }
	}
	$action="edit";
	unset($_POST);
	break;

// ajouter une valeur // ok
	case "doinsert":
	$erreur = 0 ;

	if ($_FILES['imagevaleur']['name']!=""){
		if(isset($_FILES['imagevaleur']['error'])){
		      switch($_FILES['imagevaleur']['error']){
			  case 1:
				$erreur = 1;
				$erreurmessagecarval .= "Le poids du visuel est trop important (> 2Mb)<br>";
				break;
			  case 2:
				$erreur = 1;
				$erreurmessagecarval .= "Erreur lors de l'envoi de l'image<br>";
				break;
			  case 3:
				$erreur = 1;
				$erreurmessagecarval .= "Le visuel n'a été que partiellement envoyé<br>";
				break;
			  case 4:
				$erreur = 1;
				$erreurmessagecarval .= "Aucun visuel envoyé<br>";
				break;
		      }
		}
		
		if($_FILES['imagevaleur']['type'] != "image/pjpeg" && $_FILES['imagevaleur']['type'] != "image/jpeg"){
		      $erreur = 1;
		      $erreurmessagecarval .= "Le visuel doit être au format jpg (ou .jpeg)<br>";
		}
	}
	
	 //Vérifications sur le visuel (zoom)
	if ($_FILES['zoom']['name'] != ""){
	    if(isset($_FILES['zoom']['error'])){
		  switch($_FILES['zoom']['error']){
		      case 1:
			    $erreur = 1;
			    $erreurmessagecarval .= "Le poids du zoom est trop important (> 2Mb)<br>";
			    break;
		      case 2:
			    $erreur = 1;
			    $erreurmessagecarval .= "Erreur lors de l'envoi de l'image de zoom<br>";
			    break;
		      case 3:
			    $erreur = 1;
			    $erreurmessagecarval .= "Le zoom n'a été que partiellement envoyé<br>";
			    break;
		      case 4:
			    $erreur = 1;
			    $erreurmessagecarval .= "Aucun zoom envoyé<br>";
			    break;
		  }
	    }
	    
	    if($_FILES['imagevaleur']['type'] != "image/pjpeg" && $_FILES['imagevaleur']['type'] != "image/jpeg"){
		  $erreur = 1;
		  $erreurmessagecarval .= "Le zoom doit être au format jpg (ou .jpeg)<br>";
	    }
	}
	
	if ($erreur == "0"){
		$pos=$DB_site2->query_first("select max(position) from caracteristiquevaleur where caractid = '$caractid'") ;
		$new = $pos[0] + 1;

		/// prix fixe à zero
		if($_POST['prix_fixe'] == '2'){
			$_POST['prix_fixe'] = '1';
			$_POST['prix_achat'] = 0;
			$_POST['prix_vente'] = 0;
			$_POST['prix_vente_ttc'] = 0;
		}
		
		$sql="INSERT INTO caracteristiquevaleur SET caractid=".$_POST['caractid'].", ref='".mysqli_real_escape_string($DB_site2->link_id,$_POST['ref'])."', position=".$new.", prix_fixe = ".$_POST['prix_fixe'];
		
		if($_POST['prix_vente'] !== '' && $_POST['prix_fixe'] == '1'){
			$sql .= ", paht =".$_POST['prix_achat'].",pvht = ".$_POST['prix_vente'].", pvttc = ".$_POST['prix_vente_ttc'];
		}

		foreach($langues as $langue)
		    $sql.=", libelle".$langue."='".mysqli_real_escape_string($DB_site2->link_id,$_POST['libelle'.$langue])."', description".$langue."='".mysqli_real_escape_string($DB_site2->link_id,$_POST['description'.$langue])."'";
		    
		$DB_site2->query($sql);
		$insertid = $DB_site2->insert_id() ;
		
		if ($_FILES['imagevaleur']['name']!=""){
			$DB_site2->query("update caracteristiquevaleur set image = '1' where caractvalid = '$insertid'");
			$nom_fic=$rootpath."/configurations/$host/images/caracteristiquevaleur/$insertid.jpg";
// 			copier_image($nom_fic,"imagevaleur");
			redimentionner_image($_FILES["imagevaleur"]['tmp_name'],$nom_fic,300,300);
		}

		if ($_FILES['zoom']['name'] != ""){
			$nom_fic = $rootpath."/configurations/$host/images/caracteristiquevaleur/zoom/".$insertid.".jpg";
// 			copier_image($nom_fic, "zoom");
			redimentionner_image($_FILES["zoom"]['tmp_name'],$nom_fic,600,600);
		}
		
		$erreurmessagecarval .= "L'option a été ajouté avec succès";
		
		$action = "edit" ;
		unset($_POST);
	}
	break;
    }
}
	



print "<h1>Gestion des caractéristiques</h1>";

if (isset($action) and $action == "edit"){
    $caract=$DB_site2->query_first("SELECT * FROM caracteristique WHERE caractid=".$caractid);

    $champs_traduits="";
    foreach($langues as $langue){
	$libelle="libelle".$langue;
	$description="description".$langue;
	$flag="<img src=\"images/drapeaux/br/$langue.gif\">";
	
	$champs_traduits.=<<<HTML
	    <tr>
		  <td><strong>$flag Libellé $langue :</strong></td>
		  <td>
		      <input type="text" name="libelle$langue" value="$caract[$libelle]">
		  </td>
	    </tr>
	    <tr>
		  <td><strong>$flag Description $langue :</strong></td>
		  <td>
		      <input type="text" name="description$langue" value="$caract[$description]">
		  </td>
	    </tr>
HTML;
    }

	$listemarques = $DB_site2->query("SELECT * FROM marque ORDER BY libelle ASC");
	$listemarque = "";
	foreach($listemarques as $marque){
		if($marque['marqueid'] == $caract['marqueid']){
			$selected = "selected";
		}
		else{
			$selected = "";
		}
		$listemarque .= "<option value=\"".$marque['marqueid']."\" ".$selected.">".$marque['libelle']."</option>";;
	}
	$marque_page = $DB_site2->query_first("SELECT marqueid FROM caracteristique WHERE caractid=".$caract[caractid]);

    echo <<<HTML

    <div class="commande_detail">
	<a style="color: #363E42; font-weight: bold; font-size: 13px; margin: 10px 0 0 10px; float: left;" href="caracteristique.php?marque_id=$marque_page[marqueid]">Retour à la liste</a>
	<table class="table_modules_boutiques no_border">
	      <tr>
		  <td><h2>Edition de la caractéristique</h2></td>
	      </tr>
	      <tr>
		  <td>
			<form action="ajouter_caracteristique.php" method="post" enctype="multipart/form-data">
			    <input type="hidden" name="action" value="modify">
			    <input type="hidden" name="caractid" value="$caract[caractid]">
			    <p style="color: #CC0000; font-weight: bold; text-align: center; margin: 5px 0; padding: 0;">$erreurmessagecar</p>
			    <table id="formulaire_creation_caracteristique">
				  <tr>
				      <td><strong>Libellé (interne) :</strong></td>
				      <td><input type="text" name="descriptioninterne" value="$caract[descriptioninterne]"></td>
				  </tr>
				  $champs_traduits
				  <tr>
				      <td><strong>Marque :</strong></td>
				      <td>
				      	<select name="marque_id" style="margin-left:10px;" readonly>
				      		<option value="0">Sélectionner</option>
				      		$listemarque
				      	</select>
				      </td>
				  </tr>
				  <tr>
				      <td colspan="2" align="center"><input type="submit" class="skined" value="Valider" style="padding: 0 10px;"></td>
				  </tr>
			      </table>
		      </form>
		  </td>
	      </tr>
	</table>
    </div>
    <div class="clear"></div>
HTML;


    echo <<<HTML
    <a href="#ajout_option" style="text-decoration: none; color: #363E42; float: right; margin: 0 90px 0 0; font-weight: bold;"><img src="images/icon/edit_add_22.png" style="vertical-align:middle;"> Ajouter une option</a>
    <div class="commande_detail">
	<h2>Edition des options</h2>
	<form action="ajouter_caracteristique.php" method="post" enctype="multipart/form-data">
	<input type="hidden" name="action" value="modifyvals">
	<input type="hidden" name="caractid" value="$caractid">
	<table class="table_modules_boutiques no_border">
	
HTML;

    $caractvalnb=0;

    $caractvals=$DB_site2->query("SELECT * FROM caracteristiquevaleur WHERE caractid = '$caractid' ORDER BY position ASC");
    if ($DB_site2->num_rows($caractvals) > 0){

	while($caractval=$DB_site2->fetch_array($caractvals)){

		if($caractval['prix_fixe'] == '1' && $caractval['pvht'] == '0' && $caractval['paht'] == '0'){
			$radio_fixe_zero = 'checked';
			$radio_var = '';
			$radio_fixe = '';
			$display_fixe = '';
		}
		elseif($caractval['prix_fixe'] == '1'){
			$radio_fixe = 'checked';
			$radio_var = '';
			$display_fixe = 'table-row';
		}
		else{
			$radio_fixe = '';
			$radio_var = 'checked';
			$display_fixe = '';
		}

		/// sécurité suppresion : seuls philippe et les dev peuvent supprimer une option, seulement si aucun article ne possède cette option
		$article_val=$DB_site2->query_first("SELECT artid FROM article_caractval WHERE caractvalid = '$caractval[caractvalid]'");
		if(is_null($article_val['artid']) && ($admin_username == 'jessica' || $admin_username == 'ariel' || $admin_username == 'benoit' || $admin_username == 'philippe')){
			$display_supp = 'inline-block';
		}
		else{
			$display_supp = 'none';
		}

		//Vignette
		if(file_exists($_SERVER['DOCUMENT_ROOT'].'/configurations/'.$host.'/images/caracteristiquevaleur/'.$caractval['caractvalid'].'.jpg')){
		      $visuel_vignette = '/configurations/'.$host.'/images/caracteristiquevaleur/'.$caractval['caractvalid'].'.jpg?t='.time();
		} else {
		      $visuel_vignette = 'images/no-image.jpg';
		}
		
		//Zoom
		if(file_exists($_SERVER['DOCUMENT_ROOT'].'/configurations/'.$host.'/images/caracteristiquevaleur/zoom/'.$caractval['caractvalid'].'.jpg')){
		      $visuel_zoom = '/configurations/'.$host.'/images/caracteristiquevaleur/zoom/'.$caractval['caractvalid'].'.jpg';
		      $visuel_zoom_icone = 'viewmag_pt.png';
		      $intitule_action = 'Visualiser le zoom';
		      $visuel_zoom_color = "00AA00";
		      $visuel_action_click = "";
		      $visuel_action_mouseover = "document.getElementById('zoom".$caractval['caractvalid']."').style.display = 'block';";
		      $visuel_action_mouseout = "document.getElementById('zoom".$caractval['caractvalid']."').style.display = 'none';";
		      $affiche_zoom_options = <<<HTML
		      <div id="zoom$caractval[caractvalid]" class="zoom_option">
			    <img src="/configurations/$host/images/caracteristiquevaleur/zoom/$caractval[caractvalid].jpg">
		      </div>
		      <a href="javascript:void(0);" title="Modifier" onclick="document.getElementById('vignette_zoom$caractval[caractvalid]').style.display = 'block'; document.getElementById('vignette_zoom$caractval[caractvalid]').style.display = 'block';"><img src="images/icon/edit_pt.png" alt="Modifier"></a>
		      <a href="javascript:void(0);" title="Supprimer" onclick="if(confirm('Etes-vous sûr de vouloir supprimer ce visuel de zoom ?')){ location.href = 'ajouter_caracteristique.php?action=edit&action2=del_zoom&caractid=$caractval[caractid]&caractvalid=$caractval[caractvalid]' }"><img src="images/icon/delete_pt.png" alt="Supprimer"></a>
HTML;
		} else {
		      $visuel_zoom = '';
		      $visuel_zoom_icone = 'edit_add.png';
		      $intitule_action = 'Visualiser le zoom';
		      $visuel_action_click = "jQuery('#vignette_zoom$caractval[caractvalid]').removeClass('hidden');";
		      $visuel_action_mouseover = "";
		      $visuel_action_mouseout = "";
		      $affiche_zoom_options = "";
		      $visuel_zoom_color = "CC0000";
		}
		
		$traductions="";
		foreach($langues as $langue){
		      $flag="<img src=\"images/drapeaux/br/$langue.gif\">";
		      $traductions.="<tr>
					<td><strong>$flag Libelle $langue :</strong></td>
					<td>
					      <input type=\"text\" name=\"libelle".$langue."[".$caractval[caractvalid]."]\" style=\"width:615px; margin: 0;\" value=\"".$caractval['libelle'.$langue]."\">
					</td>
				    </tr>
				    <tr>
					<td><strong>$flag Description $langue :</strong></td>
					<td><textarea rows=\"3\" name=\"description".$langue."[".$caractval[caractvalid]."]\" style=\"width:615px;height:40px;\">".$caractval['description'.$langue]."</textarea></td>
				    </tr>";
		}
		
		$up=$down="";
		if($caractvalnb>0) $up="<a href=\"ajouter_caracteristique.php?action=edit&action2=up&caractid=$caractval[caractid]&caractvalid=$caractval[caractvalid]\"><img src=\"images/monter.gif\" alt=\"Monter\" height=\"7\" border=\"0\"></a>";
		if($caractvalnb<$DB_site2->num_rows($caractvals)-1) $down="<a href=\"ajouter_caracteristique.php?action=edit&action2=down&caractid=$caractval[caractid]&caractvalid=$caractval[caractvalid]\"><img src=\"images/descendre.gif\" alt=\"Descendre\" height=\"7\" border=\"0\"></a>";
		$caractvalnb++;

		$tauxfrance = $DB_site2->query_first("SELECT TVAtauxnormal FROM pays WHERE paysid=57");
		
		echo <<<HTML
		<tr>
		    <td>
			  <div class="fond_blanc">
			  	n°$caractval[caractvalid]
				    <div class="carac_images">
				    <div style="margin-right:30px;text-align: center;">
				    <p>Vignette</p>
				    <img src="$visuel_vignette">
				    <div class="clear"></div>
				    <a href="javascript:void(0);" title="Modifier" onclick="jQuery('#vignette_titre$caractval[caractvalid]').removeClass('hidden'); /*document.getElementById('vignette_fichier$caractval[caractvalid]').style.display = 'block';*/"><img src="images/icon/edit_pt.png" alt="Modifier"></a>
				    <a href="javascript:void(0);" title="Supprimer" onclick="if(confirm('Etes-vous sûr de vouloir supprimer cette vignette ?')){ location.href = 'ajouter_caracteristique.php?action=edit&action2=del_vignette&caractid=$caractval[caractid]&caractvalid=$caractval[caractvalid]' }"><img src="images/icon/delete_pt.png" alt="Supprimer"></a>
				    </div>
				    <div class="clear"></div>
				    <div style="margin-right:30px;text-align: center;"><a href="javascript:void(0);" onclick="$visuel_action_click" style="color:#$visuel_zoom_color;">Zoom<img src="images/icon/$visuel_zoom_icone" alt="$intitule_action" style="vertical-align: middle; margin: 0 0 0 3px;"></a>
				    $affiche_zoom_options
				    </div>
				    </div>
				    <div class="rang">
				    Rang $caractval[position]<br>
				    $up
				    $down
			      </div>
			      <table class="option">
				    <tr>
					<td><strong>Réf. fabricant</strong> :</td>
					<td><input type="text" style="width:615px; margin: 0;" name="ref[$caractval[caractvalid]]" value="$caractval[ref]"></td>
				    </tr>
				    $traductions
				    <tr>
		  	<td></td>
		  	<td>
		  		<input type="radio" name="prix_fixe_$caractval[caractvalid]" value="2" onclick="show_prix_fixe($caractval[caractvalid],false);" $radio_fixe_zero id="fixe_zero_$caractval[caractvalid]"> <label for="fixe_zero_$caractval[caractvalid]" style="font-weight:bold;">Prix fixe à zero €</label> <input type="radio" name="prix_fixe_$caractval[caractvalid]" value="1" onclick="show_prix_fixe($caractval[caractvalid],true);" $radio_fixe id="fixe_$caractval[caractvalid]"> <label for="fixe_$caractval[caractvalid]" style="font-weight:bold;">Prix fixe</label> <input type="radio" name="prix_fixe_$caractval[caractvalid]" value="0" onclick="show_prix_fixe($caractval[caractvalid],false);" $radio_var id="var_$caractval[caractvalid]"> <label for="var_$caractval[caractvalid]" style="font-weight:bold;">Prix variable</label>
		  	</td>
		  </tr>
		  <tr id="prix_fixe_option_$caractval[caractvalid]" style="display:$display_fixe">
		  	<td>
		  	</td>
		  	<td style="padding-top:10px;">
		  		<strong>Prix de vente HT :</strong>
		  		<input type="text" name="prix_vente_$caractval[caractvalid]" value="$caractval[pvht]" id="prix_vente_$caractval[caractvalid]" style="width:61;margin:0;" onkeyup="setprixttc_edit($caractval[caractvalid])"> €
		  		<strong>Prix de vente TTC :</strong>
		  		<input type="text" name="prix_vente_ttc_$caractval[caractvalid]" value="$caractval[pvttc]" id="prix_vente_ttc_$caractval[caractvalid]" style="width:61;margin:0;" readonly> €
		  		<strong>Prix achat HT :</strong>
		  		<input type="text" name="prix_achat_$caractval[caractvalid]" value="$caractval[paht]" id="prix_achat_$caractval[caractvalid]" style="width:61;margin:0;"> €
		  	</td>
		  </tr>
				    <tr>
					<td colspan="2">
					      <div id="vignette_titre$caractval[caractvalid]" class="hidden">
						  Vignette : <input type="file" name="file[$caractval[caractvalid]]" style="margin: 0 0 0 34px;">
						  <br><span style="font-weight: normal; font-size: 10px;">Largeur max. : 100px | Hauteur max. : 50px</span> 
					      </div>
					</td>
				    </tr>
				    <tr>
					<td colspan="2">
					      <div id="vignette_zoom$caractval[caractvalid]" class="hidden">
						  Zoom : <input type="file" name="zoom[$caractval[caractvalid]]" style="margin: 0 0 0 52px;">
						  <br><span style="font-weight: normal; font-size: 10px;">Largeur max. : 400px | Hauteur max. : 300px</span> 
					      </div>
					</td>
				    </tr>
				    <tr>
					<td colspan="2" align="center" style="padding:0;"><input type="submit" class="skined" value="Valider" style="padding: 0 10px;">
					<button class="skined" style="padding: 0 10px;display:$display_supp" onclick="check_suppr($caractval[caractvalid]);">Supprimer</td>
				    </tr>
				    <tr><td></td><td><input type="hidden" name="delete_option" value="" id="delete_option_carac_$caractval[caractvalid]"></td></tr>
			      </table>
			  </div>
		    </td>
		</tr>
HTML;
	}
    }
    
    $traductions="";
    foreach($langues as $langue){
	$flag="<img src=\"images/drapeaux/br/$langue.gif\">";
	$description=isset($_POST['description'.$langue]) ? $_POST['description'.$langue] : "";
	$libelle=isset($_POST['libelle'.$langue]) ? $_POST['libelle'.$langue] : "";

	$traductions.=<<<HTML
	    <tr>
		  <td><strong>$flag Libellé $langue :</strong></td>
		  <td>
		      <input type="text" style="width:615px; margin: 0;" name="libelle$langue" value="$libelle">
		  </td>
	    </tr>
	    <tr>
		  <td><strong>$flag Description $langue :</strong></td>
		  <td><textarea rows="3" name="description$langue" style="width:615px;height:40px;">$description</textarea></td>
	    </tr>
HTML;
    }

$tauxfrance = $DB_site2->query_first("SELECT TVAtauxnormal FROM pays WHERE paysid=57");
    
print <<<HTML
    </table>
    </form>
    </div>
<script>
    function setprixttc_edit(ht) {
    	var tva = $tauxfrance[0];
    	var val_ht = \$jQuery('#prix_vente_'+ht).val();
    	\$jQuery('#prix_vente_ttc_'+ht).val(Math.round(val_ht * (1 + tva / 100) * 100) / 100);
    }

    function check_suppr(valid){
    	if(confirm('Etes-vous sûr de vouloir supprimer cette option ?')){
    		\$jQuery('#delete_option_carac_'+valid).val(valid);
    	}
    }

	</script>
    <div class="commande_detail">
	<a name="ajout_option"></a>
	<h2>Ajout d'une option</h2>
	<form action="ajouter_caracteristique.php#ajout_option" method="post" enctype="multipart/form-data">
	      <input type="hidden" name="action" value="doinsert">
	      <input type="hidden" name="caractid" value="$caractid">
	      <p style="text-align: center; padding: 0; margin: 0; color: #CC0000; font-weight: bold;">$erreurmessagecarval</p>
	      <table class="table_modules_boutiques no_border">
		  <tr>
			<td><strong>Réf. fabricant</strong> :</td>
			<td><input type="text" style="width:615px; margin: 0;" name="ref" value=""></td>
		  </tr>
		  $traductions
		  <tr>
			<td></td>
			<td style="display:flex;flex-wrap:nowrap;">
			<div><strong>Vignette :</strong>
			<input type="file" name="imagevaleur"><span style="font-weight: normal; font-size: 10px;">Largeur max. : 100px | Hauteur max. : 50px</span></div>
			<div><strong>Zoom :</strong>
			<br><input type="file" name="zoom"><span style="font-weight: normal; font-size: 10px;">Largeur max. : 400px | Hauteur max. : 300px</span></div>
			</td>
		  </tr>
		  <tr>
			<td style="padding-top:15px;"><strong>Tarif :</strong></td>
		  	<td style="padding-top:15px;">
		  		<input type="radio" name="prix_fixe" value="2" onclick="show_prix_fixe(false,'');" id="fixe_zero"> <label for="fixe_zero" style="font-weight:bold;">Prix fixe à zero €</label> <input type="radio" name="prix_fixe" value="1" onclick="show_prix_fixe(true,'add');" id="fixe_prix"> <label for="fixe_prix" style="font-weight:bold;">Prix fixe</label> <input type="radio" name="prix_fixe" value="0" onclick="show_prix_fixe(false,'');" id="var_prix" required> <label for="var_prix" style="font-weight:bold;">Prix variable</label>
		  	</td>
		  </tr>
		  <tr>
		  	
		  </tr>
		  <tr id="prix_fixe_option">
		  	<td>
		  	</td>
		  	<td style="padding-top:10px;">
		  		<strong>Prix de vente HT :</strong>
		  		<input type="text" name="prix_vente" style="width:100px;margin:0;" id="prix_vente" onkeyup="setprixttc()"> €
		  		<strong>Prix de vente TTC :</strong>
		  		<input type="text" name="prix_vente_ttc" style="width:100px;margin:0;margin:0;" id="prix_vente_ttc" readonly> €
		  		<strong>Prix achat HT :</strong>
		  		<input type="text" name="prix_achat" style="width:100px;margin:0;"> €
		  	</td>
		  </tr>
		  <tr>
			<td colspan="2" align="center" style="padding:0;"><input type="submit" class="skined" value="Valider" style="padding: 0 10px;"></td>
		  </tr>
	      </table>
	</form>
    </div>

    <script>
    function setprixttc() {
    	var tva = $tauxfrance[0];
    	var ht  = \$jQuery('#prix_vente').val();
    	\$jQuery('#prix_vente_ttc').val(Math.round(ht * (1 + tva / 100) * 100) / 100);
    }

	</script>
HTML;

}else{
    $champs_traduits="";
    foreach($langues as $langue){
	$libelle="libelle".$langue;
	$description="description".$langue;
	$flag="<img src=\"images/drapeaux/br/$langue.gif\">";
	
	$champs_traduits.=<<<HTML
	    <tr>
		  <td><strong>$flag Libellé $langue :</strong></td>
		  <td>
		      <input type="text" name="libelle$langue" value="$caract[$libelle]">
		  </td>
	    </tr>
	    <tr>
		  <td><strong>$flag Description $langue :</strong></td>
		  <td>
		      <input type="text" name="description$langue" value="$caract[$description]">
		  </td>
	    </tr>
HTML;
    }

    $listemarques = $DB_site2->query("SELECT * FROM marque ORDER BY libelle ASC");
	$listemarque = "";
	foreach($listemarques as $marque){
		$listemarque .= "<option value=\"".$marque['marqueid']."\">".$marque['libelle']."</option>";;
	}

    echo <<<HTML

    <div class="commande_detail">
	<a style="color: #363E42; font-weight: bold; font-size: 13px; margin: 10px 0 0 10px; float: left;" href="caracteristique.php">Retour à la liste</a>
	<table class="table_modules_boutiques no_border">
	      <tr>
		  <td><h2>Ajout d'une caractéristique</h2></td>
	      </tr>
	      <tr>
		  <td>
			<form action="ajouter_caracteristique.php" method="post" enctype="multipart/form-data">
			    <input type="hidden" name="action" value="add">
			    <p style="color: #CC0000; font-weight: bold; text-align: center; margin: 5px 0; padding: 0;">$erreurmessagecar</p>
			    <table id="formulaire_creation_caracteristique">
				  <tr>
				      <td><strong>Libellé (interne) :</strong></td>
				      <td><input type="text" name="descriptioninterne" value="$caract[descriptioninterne]"></td>
				  </tr>
				  $champs_traduits
				  <tr>
				      <td><strong>Marque :</strong></td>
				      <td>
				      	<select name="marque_id" style="margin-left:10px;">
				      		<option value="0">Sélectionner</option>
				      		$listemarque
				      	</select>
				      </td>
				  </tr>
				  <tr>
				      <td colspan="2" align="center"><input type="submit" class="skined" value="Valider" style="padding: 0 10px;"></td>
				  </tr>
			      </table>
		      </form>
		  </td>
	      </tr>
	</table>
    </div>
    <div class="clear"></div>
HTML;
}



include "./footer.php";

function redimentionner_image ($nom_fichier, $destination, $largeur, $hauteur)
	{
	if (substr ($destination, -3) == "jpg") 
		{
		$imagetmp = imagecreatefromjpeg ($nom_fichier) ;
		}
	if (substr ($destination, -3) == "gif") 
		{
		$imagetmp = imagecreatefromgif ($nom_fichier) ;
		}
	if (substr ($destination, -3) == "png") 
		{
		$imagetmp = imagecreatefrompng ($nom_fichier) ;
		}
	$imagesize = getimagesize ($nom_fichier) ;
	if ($imagesize[0] > $largeur or $imagesize[1] > $hauteur)
		{
		$largeur_en_cours = $imagesize[0] ;
		$hauteur_en_cours = $imagesize[1] ;
		if ($largeur_en_cours > $largeur) // si largeur > 112
			{
			$width = $largeur ; // largeur = 112
			$height = floor ($largeur * $hauteur_en_cours / $largeur_en_cours) ; // hauteur = 112 * (768/1024)
			$largeur_en_cours = $width ;
			$hauteur_en_cours = $height ;
			} 
		if ($hauteur_en_cours > $hauteur) 
			{
			$width = floor($largeur_en_cours * $hauteur / $hauteur_en_cours) ;
			$height = $hauteur ;
			$largeur_en_cours = $width ;
			$hauteur_en_cours = $height ;
			}
		$image = imagecreatetruecolor ($largeur_en_cours, $hauteur_en_cours) ;
		imagecopyresampled ($image, $imagetmp, 0, 0, 0, 0, $largeur_en_cours, $hauteur_en_cours, $imagesize[0], $imagesize[1]) ;
		if (substr ($destination, -3) == "jpg") 
			{
			imagejpeg ($image, $destination , 80) ;
			}
		if (substr ($destination, -3) == "gif") 
			{
			imagegif ($image, $destination , 80) ;
			}
		if (substr ($destination, -3) == "png") {
			
			imagepng ($image, $destination , 0) ;
			}
// 		chmod ($destination, 0777) ;
		imagedestroy ($image) ;
		} 
	else 
		{
		$image = imagecreatetruecolor ($imagesize[0], $imagesize[1]) ;
		imagecopyresampled ($image, $imagetmp, 0, 0, 0, 0, $imagesize[0], $imagesize[1], $imagesize[0], $imagesize[1]) ;
		if (substr ($destination, -3) == "jpg") 
			{
			imagejpeg($image, $destination, 80) ;
			}
		if (substr ($destination, -3) == "gif") 
			{
			imagegif ($image, $destination, 80) ;
			}
		if (substr ($destination, -3) == "png") 
			{
			imagepng ($image, $destination, 80) ;
			}
// 		chmod ($destination, 0777) ;
		imagedestroy ($image) ;
		}
	}

function copier_image ($nom_fic, $imagename)
    {
	if ($OpenLocal = fopen ($_FILES[$imagename]['tmp_name'], "rb")) 
		{
		$LocalFileContent = fread ($OpenLocal, $_FILES[$imagename]['size']) ;
		} 
	else 
		{
		fclose ($OpenLocal) ;
		die ("<b>Erreur :</b> Impossible d'ouvrir le fichier local '" . $_POST[$imagename] . ".") ;
		}
	fclose ($OpenLocal) ;
	if ($OpenRemote = fopen ($nom_fic, "wb")) 
		{
		if (fwrite ($OpenRemote, $LocalFileContent)) 
			{
			fclose ($OpenRemote) ;
			}
		} 
	else 
		{
		fclose ($OpenRemote) ;
		die ("<b>Erreur :</b> Impossible d'ouvrir le fichier distant $nom_fic") ;
		}
    
	chmod ("$nom_fic", 0777) ;
	}

?>
