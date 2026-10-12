<?php

require "admin_global.php";
setcookie("hostname",$host,time()+3600,"/");
include "./header.php";
include "./menu.php";

echo "<h1>Articles de presse</h1>";

////////////////////////////////////////////////////////////////////////////////////////////////////////
// Function Delete PDF
// TSI
////////////////////////////////////////////////////////////////////////////////////////////////////////
function DeletePressePDF($iddel)
{

	$dirdel = '../configurations/www.ma-cave-a-vin.fr/images/presse/';
    //$namefilter = "#^".$iddel."_[a-zA-Z0-9-. _]*(\.pdf|\.PDF|\.Pdf)$#"; // filtre les .pdf en id_(xxxx).(pdf|PDF|Pdf)
    $namefilterdel = "#^".$iddel."_[a-zA-Z0-9-. _]*(\.pdf|\.PDF|\.Pdf)$#";
    

	$filestodelete = preg_grep($namefilterdel, scandir($dirdel,SCANDIR_SORT_DESCENDING));

	foreach($filestodelete as $myfiledel)
		{
	 		if(file_exists($dirdel."".$myfiledel)) 
				{
					unlink($dirdel.$myfiledel);
					//echo '<BR>';
					//print_r ("can be deleted: ".$dirdel."".$myfiledel);
				};
				
		};
};

/////////////////////////////////////// TSI ////////////////////////////////////////////////////////////


////////////////////////////////////////////////////////////////////////////////////////////////////////
// ACTION
////////////////////////////////////////////////////////////////////////////////////////////////////////
if(isset( $_POST['action'])){

    switch($_POST['action']){
    	// MONTER D'UNE POSITION
		case "up":
			$i_position = 1;
			$position = $DB_site2->query_first( "SELECT position FROM presse WHERE id = '$id'" );						
			$nouvelle_position_page = $position[0]-1;
			$position2 = $DB_site2->query_first( "SELECT id FROM presse WHERE position = '$nouvelle_position_page' AND parent_id = '$parent_id'" );
			$rq_modifier_position = "UPDATE presse SET position='$nouvelle_position_page' 
									 WHERE id = '$id'";
			$DB_site2->query( $rq_modifier_position );
			$rq_modifier_position2 = "UPDATE presse SET position='$position[0]' 
									  WHERE id = '$position2[id]'";
			$DB_site2->query( $rq_modifier_position2 );

			$action = "";
      	break;

      	// DESCENDRE D'UNE POSITION
      	case "down":
			$i_position = 1;
			$position = $DB_site2->query_first( "SELECT position FROM presse WHERE id = '$id'" );						
			$nouvelle_position_page = $position[0]+1;
			$position2 = $DB_site2->query_first( "SELECT id FROM presse WHERE position = '$nouvelle_position_page'  AND parent_id = '$parent_id'" );
			$rq_modifier_position = "UPDATE presse SET position='$nouvelle_position_page' WHERE id = '$id'";
			$DB_site2->query( $rq_modifier_position );
			$rq_modifier_position2 = "UPDATE presse SET position='$position[0]' WHERE id = '$position2[id]'";
			$DB_site2->query( $rq_modifier_position2 );

			$action = "";
      	break;

      	// AJOUTER
      	case "ajouter":
      		if($_POST['numero'] == "") $_POST['numero'] = 0;
			$sql = "INSERT INTO presse SET";
			$sql .= " parent_id = " . intval($_POST['parent_id']);
			$sql .= " , revue   = '" . mysqli_real_escape_string($DB_site2->link_id,$_POST['revue']) . "'";
			$sql .= " , numero  = " . $_POST['numero'];
			foreach($langues as $l)
			{
				$sql .= ", libelle" . $l . " 		 = '" . mysqli_real_escape_string($DB_site2->link_id,$_POST['libelle' . $l]) . "'";
				$sql .= ", contenu" . $l . " 		 = '" . mysqli_real_escape_string($DB_site2->link_id,$_POST['contenu' . $l]) . "'";
				$sql .= ", resume" . $l . " 		 = '" . mysqli_real_escape_string($DB_site2->link_id,$_POST['resume' . $l]) . "'";
				$sql .= ", ref_title" . $l . " 		 = '" . mysqli_real_escape_string($DB_site2->link_id,$_POST['ref_title' . $l]) . "'";
				$sql .= ", ref_description" . $l . " = '" . mysqli_real_escape_string($DB_site2->link_id,$_POST['ref_description' . $l]) . "'";
				$sql .= ", url" . $l . " 			 = '" . $_POST['url' . $l] . "'";
			}
			$sql .= ", date_creation = " . time();
			$sql .= ", auteur 		 = '" . mysqli_real_escape_string($DB_site2->link_id,$_POST['auteur']) . "'";
			$sql .= ", date_revu 	 = '" . $_POST['date_revu'] . "'";
			$sql .= ", lien  		 = '" . $_POST['lien'] . "'";
			$DB_site2->query($sql);

			$revueId = $DB_site2->insert_id();

			if(isset($_FILES)){
				if($_FILES['logo']['name'] != "" && intval($_POST['parent_id']) == 3){
					$ext = pathinfo($_FILES['logo']['name'],PATHINFO_EXTENSION);
					if(strtolower($ext) == "jpg" || strtolower($ext) == "png"){
						$return = move_uploaded_file($_FILES['logo']['tmp_name'],"../configurations/www.ma-cave-a-vin.fr/images/presse/".$revueId."-logo.".strtolower($ext));
					}else
							$err = "Ce type de fichier n'est pas autorisé";
				}
				if($_FILES['img_v']['name'] != ""){
					$ext = pathinfo($_FILES['img_v']['name'],PATHINFO_EXTENSION);
					if(strtolower($ext) == "jpg"){
						$return = move_uploaded_file($_FILES['img_v']['tmp_name'],"../configurations/www.ma-cave-a-vin.fr/images/presse/".$revueId."-vign.jpg");
					}else
							$err = "Ce type de fichier n'est pas autorisé";
				}
				if($_FILES['img_c']['name'] != ""){
					$ext = pathinfo($_FILES['img_v']['name'],PATHINFO_EXTENSION);
					if(strtolower($ext) == "jpg"){
						$return = move_uploaded_file($_FILES['img_c']['tmp_name'],"../configurations/www.ma-cave-a-vin.fr/images/presse/".$revueId."-couv.jpg");
					}else
							$err = "Ce type de fichier n'est pas autorisé";
				}
				if($_FILES['file_pj']['name'] != ""){
					$ext = pathinfo($_FILES['img_v']['name'],PATHINFO_EXTENSION);
					if(strtolower($ext) == "pdf"){
						$return = move_uploaded_file($_FILES['file_pj']['tmp_name'],"../configurations/www.ma-cave-a-vin.fr/images/presse/".$revueId.".pdf");
					}else
							$err = "Ce type de fichier n'est pas autorisé";
				}
			}

			$action="";
      	break;
      
      	// ACTIVER - DESACTIVER
      	case "active":
	      	$sql="UPDATE presse SET active=".intval($_POST['active'])." WHERE id=".intval($_POST['id']);
	      	$DB_site2->query($sql);

	      	$action="";
      	break;
      
      	// EDITER
      	case "editer":

			if($_POST['numero'] == "") $_POST['numero'] = 0;
			$sql = "UPDATE presse SET";
			$sql .= " parent_id = " . intval($_POST['parent_id']);
			$sql .= " , revue   = '" . mysqli_real_escape_string($DB_site2->link_id,$_POST['revue']) . "'";
			$sql .= " , numero  = " . $_POST['numero'];
			foreach($langues as $l)
			{
				$sql .= ", libelle" . $l . " 		 = '" . mysqli_real_escape_string($DB_site2->link_id,$_POST['libelle' . $l]) . "'";
				$sql .= ", contenu" . $l . " 		 = '" . mysqli_real_escape_string($DB_site2->link_id,$_POST['contenu' . $l]) . "'";
				$sql .= ", resume" . $l . " 		 = '" . mysqli_real_escape_string($DB_site2->link_id,$_POST['resume' . $l]) . "'";
				$sql .= ", ref_title" . $l . " 		 = '" . mysqli_real_escape_string($DB_site2->link_id,$_POST['ref_title' . $l]) . "'";
				$sql .= ", ref_description" . $l . " = '" . mysqli_real_escape_string($DB_site2->link_id,$_POST['ref_description' . $l]) . "'";
				$sql .= ", url" . $l . " 			 = '" . $_POST['url' . $l] . "'";
			}
			$sql .= ", auteur 		 = '" . mysqli_real_escape_string($DB_site2->link_id,$_POST['auteur']) . "'";
			$sql .= ", date_revu 	 = '" . $_POST['date_revu'] . "'";
			$sql .= ", lien  		 = '" . $_POST['lien'] . "'";
			$sql .= " WHERE id = " . intval($_POST['id']);
			$DB_site2->query($sql);

			if(isset($_FILES)){
				if($_FILES['logo']['name'] != "" && intval($_POST['parent_id']) == 3){
					$ext = pathinfo($_FILES['logo']['name'],PATHINFO_EXTENSION);
					if(strtolower($ext) == "jpg" || strtolower($ext) == "png"){
						if(file_exists("../configurations/www.ma-cave-a-vin.fr/images/presse/".$_POST['id']."-logo.".strtolower($ext))) 
							unlink("../configurations/www.ma-cave-a-vin.fr/images/presse/".$_POST['id']."-logo.".strtolower($ext));
						$return = move_uploaded_file($_FILES['logo']['tmp_name'],"../configurations/www.ma-cave-a-vin.fr/images/presse/".$_POST['id']."-logo.".strtolower($ext));
					}else
							$err = "Ce type de fichier n'est pas autorisé";
				}
				if($_FILES['img_v']['name'] != ""){
					$ext = pathinfo($_FILES['img_v']['name'],PATHINFO_EXTENSION);
					if(strtolower($ext) == "jpg"){
						if(file_exists("../configurations/www.ma-cave-a-vin.fr/images/presse/".$_POST['id']."-vign.jpg")) 
							unlink("../configurations/www.ma-cave-a-vin.fr/images/presse/".$_POST['id']."-vign.jpg");
						$return = move_uploaded_file($_FILES['img_v']['tmp_name'],"../configurations/www.ma-cave-a-vin.fr/images/presse/".$_POST['id']."-vign.jpg");
					}else
							$err = "Ce type de fichier n'est pas autorisé";
				}
				if($_FILES['img_c']['name'] != ""){
					$ext = pathinfo($_FILES['img_c']['name'],PATHINFO_EXTENSION);
					if(strtolower($ext) == "jpg"){
						if(file_exists("../configurations/www.ma-cave-a-vin.fr/images/presse/".$_POST['id']."-couv.jpg")) 
							unlink("../configurations/www.ma-cave-a-vin.fr/images/presse/".$_POST['id']."-couv.jpg");
						$return = move_uploaded_file($_FILES['img_c']['tmp_name'],"../configurations/www.ma-cave-a-vin.fr/images/presse/".$_POST['id']."-couv.jpg");
					}else
							$err = "Ce type de fichier n'est pas autorisé";
				}
				if($_FILES['file_pj']['name'] != ""){
					$ext = pathinfo($_FILES['file_pj']['name'],PATHINFO_EXTENSION);
					if(strtolower($ext) == "pdf"){

						/////////////////////////////////////// TSI ////////////////////////////////////////////////////////////
						$today = date("YmdHis");

						//if(file_exists("../configurations/www.ma-cave-a-vin.fr/images/presse/".$_POST['id'].".pdf")) 
						//	unlink("../configurations/www.ma-cave-a-vin.fr/images/presse/".$_POST['id'].".pdf");
						DeletePressePDF($_POST['id']);

						//$return = move_uploaded_file($_FILES['file_pj']['tmp_name'],"../configurations/www.ma-cave-a-vin.fr/images/presse/".$_POST['id'].".pdf");
						$return = move_uploaded_file($_FILES['file_pj']['tmp_name'],"../configurations/www.ma-cave-a-vin.fr/images/presse/".$_POST['id']."_".$today.".pdf");

						/////////////////////////////////////// TSI ////////////////////////////////////////////////////////////

					}else
							$err = "Ce type de fichier n'est pas autorisé";
				}
			}
  
			$action="";
		break;
   	}
}

////////////////////////////////////////////////////////////////////////////////////////////////////////
// AFFICHER LES PAGES INSTITUTIONNELLES
////////////////////////////////////////////////////////////////////////////////////////////////////////
if( !isset( $action ) || $action == "" ){
	$i_conseil=0;

	echo "<h3>Articles existants</h3>";
	
	$rq = $DB_site2->query( "SELECT * FROM presse WHERE parent_id=0 ORDER BY date_creation DESC" );
	while($parent=$DB_site2->fetch_array($rq)){
		echo "<table class=\"table_modules_boutiques\">
     		<tr>
				<td align=\"center\"><b>Titre</b></td>
				<td align=\"center\" width=\"25%\"><b>Action</b></td>
	      	</tr>
	      	<tr>
				<td><b>".$parent[revue]." ".$parent[libelleFR]."</b></td>
				<td align=\"center\" width=\"20%\">
					<a href=\"presse.php?action=ajouter&parent_id=".$parent['id']."\" target=\"_self\">Ajouter un article</a>
				  	| <a href=\"presse.php?action=editer&id=".$parent[id]."\" target=\"_self\">Editer</a>
				  	| <a href=\"presse.php?action=supprimer&id=".$parent[id]."\" target=\"_self\">Supprimer</a>
				</td>
	      	</tr>";

      	$req2=$DB_site2->query("SELECT * FROM presse WHERE parent_id=".$parent['id']." ORDER BY date_creation DESC");
      	while ($rs_conseil=$DB_site2->fetch_array($req2)) {
			$rowalt = "td_".getrowbg();
			$i_conseil++;

			if($nb_pages > 1){
				if($i_conseil == 1){ // Première page
					$images_position = "<a href=\"presse.php?action=down&id=".$rs_conseil[id]."&parent_id=".$rs_conseil[parent_id]."\">
										<img src=\"images/descendre.gif\" alt=\"Descendre\" height=\"7\" border=\"0\"></a>";
				}elseif( $i_conseil == $nb_pages ) { // Dernière page
					$images_position = "<a href=\"presse.php?action=up&id=".$rs_conseil[id]."&parent_id=".$rs_conseil[parent_id]."\">
										<img src=\"images/monter.gif\" alt=\"Monter\" height=\"7\" border=\"0\"></a>";
				}else{
					$images_position = "<a href=\"presse.php?action=up&id=".$rs_conseil[id]."&parent_id=".$rs_conseil[parent_id]."\">
										<img src=\"images/monter.gif\" alt=\"Monter\" height=\"7\" border=\"0\"></a>
										<a href=\"presse.php?action=down&id=".$rs_conseil[id]."&parent_id=".$rs_conseil[parent_id]."\">
										<img src=\"images/descendre.gif\" alt=\"Descendre\" height=\"7\" border=\"0\"></a>";
				}
			}

			if($rs_conseil['active']==1)
		    	$active="<a href=\"javascript:active_article(".$rs_conseil[id].",0)\" style=\"color:red;\">Désactiver</a>";
			else
		    	$active="<a href=\"javascript:active_article(".$rs_conseil[id].",1)\">Activer</a>";

			echo "<tr>
				<td>&nbsp;- ".$rs_conseil[revue]." ".$rs_conseil[libelleFR]."</td>
				<td  align=\"center\" width=\"20%\">
					".$active."
				  	| <a href=\"presse.php?action=editer&id=".$rs_conseil[id]."&i_langue=0\" target=\"_self\">Editer</a>
				  	| <a href=\"presse.php?action=supprimer&id=".$rs_conseil[id]."\" target=\"_self\">Supprimer</a>
				</td>
	      	</tr>";
		}
	
	    echo "</table>&nbsp;";
	}

	if($i_conseil == 0)
		echo "<tr><td align=\"center\"><br>Il n'y a aucune page</td></tr>";
	
	echo "<br><a href=\"presse.php?action=ajouter\" target=\"_self\">Ajouter un article</a>";
	
	echo "<form action=\"presse.php\" name=\"activeform\" method=\"post\">
		<input type=\"hidden\" name=\"action\" value=\"active\">
		<input type=\"hidden\" name=\"active\">
		<input type=\"hidden\" name=\"id\">
	</form>
	<script language=\"javascript\">
		function active_article(id,active){
		    document.forms.activeform.id.value=id;
		    document.forms.activeform.active.value=active;
		    document.forms.activeform.submit();
		}
	</script>";
}

////////////////////////////////////////////////////////////////////////////////////////////////////////
// EDITER un conseil INSTITUTIONNELLE (action=editer)
////////////////////////////////////////////////////////////////////////////////////////////////////////
elseif( $action == "editer" && isset( $id ) ){

	$rs = $DB_site2->query_first("SELECT * FROM presse WHERE id = ".$id);

	if ($rs[parent_id] == "0") $liste_parents = "<option value=\"0\" selected>Aucun parent</>";
	else $liste_parents = "<option value=\"0\">Aucun parent</>";
	$parents = $DB_site2->query("SELECT * FROM presse WHERE parent_id = '0'");
	while( $parent = $DB_site2->fetch_array( $parents ) ){
		if ($parent[id] == $rs[parent_id])
			$liste_parents .= "<option value=\"$parent[id]\" selected>$parent[$libelle]</>";
		elseif($parent['id']!=$rs['id'])
			$liste_parents .= "<option value=\"$parent[id]\">$parent[$libelle]</>";
	}

	$img_v = $rs['id']."-vign.jpg";
	$img_c = $rs['id']."-couv.jpg";

	$contenu_onglet = "<div align=\"center\"><h3>Editer un article</h3></div>
	<form method=\"post\" action=\"presse.php\" name=\"form_institutionnel\" enctype=\"multipart/form-data\">
	<table style=\"float:left;\">
		<tr>
			<td><b>Parent :</b></td>
			<td><select name=\"parent_id\">".$liste_parents."</select></td>
		</tr>
		<tr>
			<td><b>Logo :</b></td>
			<td><input type=\"file\" name=\"logo\"> *pour article web</td>
		</tr>
		<tr>
			<td><b>Vignette :</b></td>
			<td><input type=\"file\" name=\"img_v\"></td>
		</tr>
		<tr>
			<td><b>Couverture :</b></td>
			<td><input type=\"file\" name=\"img_c\"></td>
		</tr>
		<tr>
			<td><b>Revue :</b></td>
			<td><input type=\"text\" name=\"revue\" value=\"".$rs['revue']."\"></td>
			<td><b>N° :</b></td>
			<td><input type=\"text\" name=\"numero\" value=\"".$rs['numero']."\"></td>
		</tr>
		<tr>
			<td><b>Fichier de l'article :</b></td>
			<td><input type=\"file\" name=\"file_pj\"></td>
		</tr>
	</table>
	<table align=\"center\">
		<tr>
			<td style=\"text-align:center;\"><b>Vignette</b><br><img src=\"../configurations/www.ma-cave-a-vin.fr/images/presse/".$img_v."\" style=\"max-width:100; max-height:125;\"></td>
			<td style=\"text-align:center;\"><b>Couverture</b><br><img src=\"../configurations/www.ma-cave-a-vin.fr/images/presse/".$img_c."\" style=\"max-width:100; max-height:125;\"></td>
		</tr>
	</table>
	<main class=\"main_tab\" style=\"display:inline-block;\">";

	foreach($langues as $langue){
		if($langue == 'FR') $check = 'checked';
		else $check = '';

		$contenu_onglet .= "
		<input id=\"".$langue."\" type=\"radio\" name=\"photo_tabs\" class=\"inp_photo\" ".$check.">
		<label for=\"".$langue."\">Article ".$langue." <img src=\"images/drapeaux/".$langue.".gif\"></label>";
	}

	foreach($langues as $langue){
		$contenu_onglet.= "<section id=\"content_$langue\" class=\"section_photo\">
		<table>
			<tr>
			    <td><b>Titre :</b></td>
			    <td><input type=\"text\" name=\"libelle".$langue."\" value=\"".$rs['libelle'.$langue]."\" size=\"60\"></td>
		  	</tr>
		  	<tr>
			    <td><b>Contenu :</b></td>
			    <td><textarea name=\"contenu".$langue."\" class=\"ckeditor\">".$rs['contenu'.$langue]."</textarea></td>
		  	</tr>
		  	<tr>
		  		<td><b>Résumé :</b></td>
		  		<td><textarea name=\"resume".$langue."\">".$rs['resume'.$langue]."</textarea></td>
		  	</tr>
		  	<tr>
		    	<td><b>Meta title :</b></td>
		    	<td><input type=\"text\" name=\"ref_title".$langue."\" value=\"".$rs['ref_title'.$langue]."\" size=\"60\"></td>
		  	</tr>
		  	<tr>
			    <td><b>Meta description :</b></td>
			    <td><textarea name=\"ref_description".$langue."\" cols=\"80\" rows=\"4\">".$rs['ref_description'.$langue]."</textarea></td>
		  	</tr>
	  	 	<tr>
			    <td><b>URL :</b></td>
			    <td>presse/<input type=\"text\" name=\"url".$langue."\" value=\"".$rs['url'.$langue]."\" size=\"40\">.htm</td>
		  	</tr>
		  	<tr>
		  		<td colspan=\"2\" style=\"border-bottom:solid 1px black;\">&nbsp;</td>
	  		</tr>
		</table>
		</section>";
	}

	$contenu_onglet .= "<br>
	<table>
		<tr>
			<td><b>Auteur :</b></td>
			<td><input type=\"text\" name=\"auteur\" value=\"".$rs['auteur']."\"></td>
		</tr>
		<tr>
			<td><b>Date de l'article :</b></td>
			<td><input type=\"text\" name=\"date_revu\" value=\"".$rs['date_revu']."\"></td>
		</tr>
		<tr>
			<td><b>Article original (lien) :</b></td>
			<td><input type=\"text\" name=\"lien\" value=\"".$rs['lien']."\"></td>
		</tr>
	</table>
	<input type=\"hidden\" name=\"action\" value=\"editer\">
	<input type=\"hidden\" name=\"id\" value=\"$id\">
	<div align=\"center\">
		<input type=\"submit\" value=\"Enregistrer\">
		<input type=\"button\" value=\"Annuler\" onclick=\"document.location='presse.php'\">
	</div>
	</main>
	</form>";

	print $contenu_onglet;

	echo "<script language=\"javascript\">
	setTimeout(\"autosave()\",900000);
	
	function autosave(){
	    if(confirm(\"Par sécurite la page doit être enregistrée, voulez-vous le faire maintenant ?\"))
		document.forms.form_institutionnel.submit();
	    else
		setTimeout(\"autosave()\",900000);
	}
	CKEDITOR.config.height='500px';
	CKEDITOR.config.filebrowserBrowseUrl='ckbrowser.php'
	CKEDITOR.config.filebrowserUploadUrl='ckupload.php';
	</script>";
}

////////////////////////////////////////////////////////////////////////////////////////////////////////
// AJOUTER un conseil INSTITUTIONNELLE (action=ajouter)
////////////////////////////////////////////////////////////////////////////////////////////////////////	
elseif( $action == "ajouter" ){
	if ($rs_conseil[parent_id] == "0") $liste_parents = "<option value=\"0\" selected>Aucun parent</>";
	else $liste_parents = "<option value=\"0\">Aucun parent</>";
	$parents = $DB_site2->query("SELECT * FROM presse WHERE parent_id = '0'");
	while( $parent = $DB_site2->fetch_array( $parents ) ){
		if ($parent[id] == $rs_conseil[parent_id]) $liste_parents .= "<option value=\"".$parent[id]."\" selected>".$parent[$libelle]."</>";
		elseif($parent['id']!=$rs_conseil['id']) $liste_parents .= "<option value=\"".$parent[id]."\">".$parent[$libelle]."</>";
	}

	$contenu_onglet = "<div align=\"center\"><h3>Ajouter un article</h3></div>
	<form method=\"post\" action=\"presse.php\" name=\"form_institutionnel\" enctype=\"multipart/form-data\">
		<table>
			<tr>
				<td><b>Parent :</b></td>
				<td><select name=\"parent_id\">".$liste_parents."</select></td>
			</tr>
			<tr>
			<td><b>Logo :</b></td>
			<td><input type=\"file\" name=\"logo\"> *pour article web</td>
		</tr>
			<tr>
				<td><b>Vignette :</b></td>
				<td><input type=\"file\" name=\"img_v\"></td>
			</tr>
			<tr>
				<td><b>Couverture :</b></td>
				<td><input type=\"file\" name=\"img_c\"></td>
			</tr>
			<tr>
				<td><b>Revue :</b></td>
				<td><input type=\"text\" name=\"revue\" value=\"\"></td>
				<td><b>N° :</b></td>
				<td><input type=\"text\" name=\"numero\" value=\"\"></td>
			</tr>
			<tr>
				<td><b>Fichier de l'article :</b></td>
				<td><input type=\"file\" name=\"file_pj\"></td>
			</tr>
		</table>
		<main class=\"main_tab\">";

	foreach($langues as $langue){
		if($langue == 'FR') $check = 'checked';
		else $check = '';
		$contenu_onglet .= "
		<input id=\"".$langue."\" type=\"radio\" name=\"photo_tabs\" class=\"inp_photo\" ".$check.">
		<label for=\"".$langue."\">Article ".$langue." <img src=\"images/drapeaux/".$langue.".gif\"></label>";
	}

	foreach($langues as $langue){
		$contenu_onglet.= "<section id=\"content_$langue\" class=\"section_photo\">
		<table>
			<tr>
			    <td><b>Titre :</b></td>
			    <td><input type=\"text\" name=\"libelle".$langue."\" value=\"\" size=\"60\"></td>
		  	</tr>
		  	<tr>
			    <td><b>Contenu :</b></td>
			    <td><textarea name=\"contenu".$langue."\" class=\"ckeditor\"></textarea></td>
		  	</tr>
		  	<tr>
		  		<td><b>Résumé :</b></td>
		  		<td><textarea name=\"resume".$langue."\"></textarea></td>
		  	</tr>
		  	<tr>
		    	<td><b>Meta title :</b></td>
		    	<td><input type=\"text\" name=\"ref_title".$langue."\" value=\"\" size=\"60\"></td>
		  	</tr>
		  	<tr>
			    <td><b>Meta description :</b></td>
			    <td><textarea name=\"ref_description".$langue."\" cols=\"80\" rows=\"4\"></textarea></td>
		  	</tr>
	  	 	<tr>
			    <td><b>URL :</b></td>
			    <td>presse/<input type=\"text\" name=\"url".$langue."\" value=\"\" size=\"40\">.htm</td>
		  	</tr>
		  	<tr>
		  		<td colspan=\"2\" style=\"border-bottom:solid 1px black;\">&nbsp;</td>
	  		</tr>
		</table>
		</section>";
	}

	$contenu_onglet .= "<br>
			<table>
				<tr>
					<td><b>Auteur :</b></td>
					<td><input type=\"text\" name=\"auteur\" value=\"\"></td>
				</tr>
				<tr>
					<td><b>Date de l'article :</b></td>
					<td><input type=\"text\" name=\"date_revu\" value=\"\"></td>
				</tr>
				<tr>
					<td><b>Article original (lien) :</b></td>
					<td><input type=\"text\" name=\"lien\" value=\"\"></td>
				</tr>
			</table>
			<input type=\"hidden\" name=\"action\" value=\"ajouter\">
			<input type=\"hidden\" name=\"i_langue\" value=\"$i_langue\">
			<div align=\"center\">
				<input type=\"submit\" value=\"Enregistrer\">
				<input type=\"button\" value=\"Annuler\" onclick=\"document.location='presse.php'\">
			</div>
		</main>
	</form>";

	print $contenu_onglet;

	echo "<script language=\"javascript\">
		//CKEDITOR.config.height='500px';
		CKEDITOR.config.filebrowserBrowseUrl='ckbrowser.php'
		CKEDITOR.config.filebrowserUploadUrl='ckupload.php';
	</script>";
}

////////////////////////////////////////////////////////////////////////////////////////////////////////
// SUPPRIMER un conseil INSTITUTIONNELLE (action=supprimer)
////////////////////////////////////////////////////////////////////////////////////////////////////////	
elseif( $action == "supprimer" ){
	// Confirmation de suppression
	echo "Etes-vous sûr(e) de vouloir supprimer cet article ?<br><br>
		  <a href=\"presse.php?action=supprimer2&id=$id\">Oui</a>
		  | <a href=\"presse.php\">Non</a>";
}

////////////////////////////////////////////////////////////////////////////////////////////////////////
// SUPPRIMER un conseil INSTITUTIONNELLE (action=supprimer2)
////////////////////////////////////////////////////////////////////////////////////////////////////////	
elseif( $action == "supprimer2" ){
	// On décalle toutes les positions des pages suivantes
	$position_page = $DB_site2->query_first( "SELECT position FROM presse WHERE id = $id" );
	$position_page = $position_page[0];
	
	$rq_positions_suivantes = $DB_site2->query( "SELECT id, position FROM presse WHERE position > $position_page" );
	
	while( $rs_positions_suivantes = $DB_site2->fetch_array( $rq_positions_suivantes ) ){
		$position_temp = $rs_positions_suivantes[position] - 1;
		$rq_modifier_position = "UPDATE presse SET position = '$position_temp' WHERE id = '$rs_positions_suivantes[id]'";
		$DB_site2->query( $rq_modifier_position );
	}
	
	// Suppression
	$rq_supprimer = "DELETE FROM presse WHERE id = $id";
	$DB_site2->query( $rq_supprimer );

	echo "<script language=\"javascript\">location.href=\"presse.php\";</script>";
}

include "./footer.php";

?>