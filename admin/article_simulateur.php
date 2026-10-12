<?php
$checkedsimu0="";
$checkedsimu1="";
$contenu_onglet2="";


if(isset($_GET['actionsimu']) && $_GET['actionsimu'] == 'editsimu'){ // edition de la caracteristique (ajout option, modif option, suppression de l'option)
	require "admin_global.php";
	include_once "./header.php";
	include_once "./menu.php";	
	include_once("fonctions.php");
	$path = "../configurations/".$host."/images/simulateur/";
	$caractid = $_GET['caractid'];
	$artid= $_GET['artid'];
	$ascid= $_GET['ascid'];
	echo "<h3>Options de la caracteristique</h3>";
	
	if(isset($_GET['ajoutoption'])){ //ajout d'une option pour la caracteristique

	}else{
		if(isset($_GET['suppropt'])) { //suppression de l'option de la caractéristique
			//$DB_site2->query("DELETE FROM article_simu_caractval WHERE ascvalid ='".$_GET['ascvalid']."' ");
		}
		$i = 1;
		echo "<p><a href=\"article_simulateur.php?actionsimu=editsimu&ajoutoption=1&caractid=$caractid&artid=$artid&ascid=$ascid\">+ Ajouter une option</a></p>";
		$req = $DB_site2->query("SELECT * FROM article_simu_caractval WHERE ascid = '$ascid'");
		while($row = $DB_site2->fetch_array($req)){ // on affiche toutes les options existantes pour la caractéristique
			echo "<h4>Option $i</h4>";
			echo "<img src=\"".$path.$ascid."_".$row['ascvalid'].".png\" ><br>";
			echo "<a href=\"#\">Changer la photo</a><br>";
			echo "<a href=\"article_simulateur.php?actionsimu=editsimu&suppropt=1&ascvalid=".$row['ascvalid']."\">Supprimer l'option</a>";
			$i++;
		}
	}

	

}else{	// affichages des caractéristiques pour l'article
	$path = "../configurations/".$host."/images/simulateur/";

	$contenu_onglet .='<h3>Simulateur</h3>';
	$contenu_onglet .="".
	"<script type=\"text/javascript\">"
		."function validFormSim(){"
			."document.formu.action2.value='simulateur';"
			."document.formu.old.value='simulateur';"
				."document.formu.submit() ;"
		."}"
		."function miseAjourCaract(artid){"
			."\$jQuery.ajax({
						url: 'art_simu_ajax.php',
						type: 'GET',
						data: 'maj=1'+'&artid=' + artid,
						dataType: 'html',
						success: function(code_html, statut){console.log(code_html,statut);}
					});"
			."validFormSim();"
		."}"
		."function changeTypeCaract(typeid,artid,caractid){"
			."\$jQuery.ajax({
						url: 'art_simu_ajax.php',
						type: 'GET',
						data: 'changeType='+typeid+'&artid=' + artid + '&caractid=' + caractid,
						dataType: 'html',
						success: function(code_html, statut){console.log(code_html,statut);}
					});"
			."validFormSim();"
		."}"
		."function changeOrdre(artid,caractid,sens){"
			."\$jQuery.ajax({
						url: 'art_simu_ajax.php',
						type: 'GET',
						data: 'ordre='+sens+'&artid=' + artid + '&caractid=' + caractid,
						dataType: 'html',
						success: function(code_html, statut){console.log(code_html,statut);}
					});"
			."validFormSim();"
		."}"
		."function deleteImg(artid,caractid,caractvalid){"
			."\$jQuery.ajax({
						url: 'art_simu_ajax.php',
						type: 'GET',
						data: 'del=1' + '&artid=' + artid + '&caractid=' + caractid + '&caractvalid=' + caractvalid,
						dataType: 'html',
						success: function(code_html, statut){console.log(code_html,statut);}
					});"
			."validFormSim();"
		."}"
		."function deleteImgBase(artid,caractid){"
			."\$jQuery.ajax({
						url: 'art_simu_ajax.php',
						type: 'GET',
						data: 'del=1' + '&artid=' + artid + '&caractid=' + caractid ,
						dataType: 'html',
						success: function(code_html, statut){console.log(code_html,statut);}
					});"
			."validFormSim();"
		."}"
		."function imgSimu(ascid){"
			."\$jQuery.ajax({
						url: 'art_simu_ajax.php',
						type: 'GET',
						data: 'imgsimu=1' + '&ascid=' + ascid ,
						dataType: 'html',
						success: function(code_html, statut){console.log(code_html,statut);}
					});"
			."validFormSim();"
		."}"
		."function validSimu(valopt,artid){"
			."\$jQuery.ajax({
					url: 'art_simu_ajax.php',
					type: 'GET',
					data: 'simu='+valopt+'&artid=' + artid,
					dataType: 'html',
					success: function(code_html, statut){console.log(code_html,statut);}
				});"
				."validFormSim();"
			."}"
	."</script>";	

	$contenu_onglet .="<form name=\"formu\" action=\"art_simu_ajax.php\" method=\"post\" enctype=\"multipart/form-data\">
						<input type=\"hidden\" name=\"old\" value=\"\">
						<input type=\"hidden\" name=\"quitter\" value=\"\">
						<input type=\"hidden\" name=\"mq2\" value=\"\">
						<input type=\"hidden\" name=\"modif\" value=\"0\">
						<input type=\"hidden\" name=\"action2\" value=\"\">
						<input type=\"hidden\" name=\"artid\" value=\"$artid\">
						<input type=\"hidden\" name=\"catid\" value=\"$catid\">";

	$req = $DB_site2->query_first("SELECT simu FROM article WHERE artid='".$artid."'");

	if($req[0] == 'oui'){// si l'article est un article qui fait parti du simulateur

		//mise a jour des caractéristisques et options
		//Si on sélectionne oui, on rempli la table article_simu_caract avec les caratéristiques du produits existant.
		$i=1;
		$id_r= null;
		$req = $DB_site2->query("SELECT caractvalid FROM article_caractval WHERE artid='".$artid."'");
		while ($row = $DB_site2->fetch_array($req)){
			$id_r= null;
			$caract = $DB_site2->query_first("SELECT caractid FROM caracteristiquevaleur WHERE caractvalid='".$row['caractvalid']."'");
			$r= $DB_site2->query("SELECT artid FROM article_simu_caract WHERE artid='".$artid."' AND caractid='".$caract['caractid']."'");
			$rr= $DB_site2->query("SELECT artid FROM article_simu_caractval WHERE artid='".$artid."' AND caractvalid='".$row['caractvalid']."'");
			if($DB_site2->num_rows($r) == 0){
				$DB_site2->query("INSERT INTO article_simu_caract SET artid='".$artid."', caractid='".$caract['caractid']."', image= NULL, ordre='".$i."'");
				$id_r = $DB_site2->insert_id();
				$i++;
			}
			if(is_null($id_r)){
				if($DB_site2->num_rows($rr) == 0){
					//echo " -- $rr == 0";
					$r2 = $DB_site2->query_first("SELECT ascid FROM article_simu_caract WHERE artid='".$artid."' AND caractid='".$caract['caractid']."'");
					$DB_site2->query("INSERT INTO article_simu_caractval SET artid='".$artid."', caractvalid='".$row['caractvalid']."', ascid='".$r2['ascid']."'");
				}
			}else{
				if($DB_site2->num_rows($rr) == 0){
					$DB_site2->query("INSERT INTO article_simu_caractval SET artid='".$artid."', caractvalid='".$row['caractvalid']."', ascid='".$id_r."'");
				}
			}
		}

		//////////////////////////////////////////////////////////////////////////////////////////////

		$checkedsimu0 ="checked";
		
		$contenu_onglet2 .="<h3>Caractéristiques</h3>";
		//on récupère les caractéristiques pour cet article
		$req = $DB_site2->query("SELECT caractid, ordre,type_caracteristique,ascid,affiche FROM article_simu_caract WHERE artid='".$artid."' ORDER BY ordre ASC");
		$first_position = $DB_site2->query_first("SELECT ordre FROM article_simu_caract WHERE artid='".$artid."' ORDER BY ordre ASC LIMIT 1");
		$last_position = $DB_site2->query_first("SELECT ordre FROM article_simu_caract WHERE artid='".$artid."' ORDER BY ordre DESC LIMIT 1");
		$pathsim = "../configurations/".$host."/images/simulateur/";
		while ($row = $DB_site2->fetch_array($req)){
			$changeOrdre="";
			$caractid = $row['caractid'];
			//$caract = $DB_site2->query_first("SELECT c.libelleFR as libelle, c.caractid FROM caracteristique as c INNER JOIN caracteristiquevaleur as cv USING(caractid) WHERE cv.caractvalid='".$row['caractvalid']."'");
			$caract = $DB_site2->query_first("SELECT libelleFR as libelle FROM caracteristique WHERE caractid='".$row['caractid']."'");

			//on vérifie que toutes les caractéristiques du produits sont bien dans la table article_simu_caract si ce produit est un produit simulateur
			$r= $DB_site2->query("SELECT artid FROM article_simu_caract WHERE artid='".$artid."' AND caractid='".$row['caractid']."'");
			if($DB_site2->num_rows($r) == 0){
				$rr=$DB_site2->query_first("SELECT ordre FROM article_simu_caract WHERE artid='".$artid."' ORDER BY ordre DESC LIMIT 1");
				$ordre = $rr[0]+1;
				$DB_site2->query("INSERT INTO article_simu_caract SET artid='".$artid."', caractid='".$row['caractid']."', image= NULL, ordre='".$ordre."'");
			}
			if($row['ordre'] != $first_position[0]){
				$changeOrdre .="<a onclick=\"changeOrdre($artid,$caractid,'up');\" style=\"cursor:pointer;\"><i class=\"fa fa-caret-up\"></i></a>";
			}
			if($row['ordre'] != $last_position[0]){
				$changeOrdre .="<a onclick=\"changeOrdre($artid,$caractid,'down');\" style=\"cursor:pointer;\"><i class=\"fa fa-caret-down\"></i></a>";
			}

			$contenu_onglet2 .="<div class=\"row caract\"><div class=\"col-xs-1\" style=\"clear:both;\">".$changeOrdre."</div><div class=\"col-xs-3\" ><p id=\"caract_sim_".$row['caractid']."\"><strong>".$row['ordre']."</strong> - <a href=\"article_simulateur.php?actionsimu=editsimu&artid=$artid&caractid=".$row['caractid']."&ascid=".$row['ascid']."\" target=\"_blank\">".$caract['libelle']."</a></p></div>";

			//image de base de l'option
			if(!file_exists($pathsim.$artid."_".$row['caractid'].".png")){//si aucune photo n'est enregistrée pour ce produit en image de base
				$contenu_onglet2 .= "<div class=\"col-xs-4\" style=\"text-align:left;\"><input type=\"file\" name=\"img_caract_sim_".$row['caractid']."\" onchange=\"validFormSim();\"></div>";
			}else{// si il en existe une
				$contenu_onglet2 .= "<div class=\"col-xs-4 edit-img\" style=\"text-align:left;\"><span class=\"edit\"><input type=\"file\" name=\"img_caract_sim_".$row['caractid']."\" title=\"modifier\" onchange=\"validFormSim();\"></span><img src=\"../images/templates/delete.svg\" height=\"17px\" width=\"15px\" onclick=\"deleteImgBase('".$artid."','".$row['caractid']."');\"><a href=\"".$pathsim.$artid."_".$row['caractid'].".png?".time()."\" target=\"_blank\">APERCU</a></div>";
			}
			$checkedsimu ="";
			if($row['affiche'] == 1) $checkedsimu = "checked";	
			$contenu_onglet2 .= "<div class=\"col-xs-2\"><input type=\"checkbox\" onclick=\"imgSimu(".$row['ascid'].")\" $checkedsimu >&nbsp;&nbsp;&nbsp;simu</div>";
			$contenu_onglet2 .= "<div class=\"col-xs-2\"><select name=\"typecaract_".$row['caractid']."\" onchange=\"changeTypeCaract(this.value,$artid,$caractid);\">";
			$type_caract = $DB_site2->query("SELECT * FROM simu_type_caract") ;		
			$contenu_onglet2 .="<option value=\"0\"></option>";
			
			while ($row2 = $DB_site2->fetch_array($type_caract)){
				$defaut = "";
				if($row['type_caracteristique'] == $row2['type_caracteristique_id'])
					$defaut = "selected";

				$contenu_onglet2 .= "<option value=\"".$row2['type_caracteristique_id']."\" $defaut >".$row2['libelle']."</option>";
			}		
			$contenu_onglet2 .="</select></div></div>";
			// on affiche les options par caractéristique..
			$req2 = $DB_site2->query("SELECT cv.libelleFR as libelle, ascv.caractvalid as caractvalid, ascv.ascvalid, cv.caractid as caractid FROM article_simu_caractval as ascv INNER JOIN caracteristiquevaleur as cv USING(caractvalid) WHERE ascid = '".$row['ascid']."' ");

			$i = 0;
			while($row3 = $DB_site2->fetch_array($req2)){
				if($i%2 == 0){
					$classoption = 'option2';
				}else{
					$classoption = 'option';
				}

				$contenu_onglet2 .= "<div class=\"row $classoption\"><div class=\"col-xs-offset-1 col-xs-3\" style=\"clear:both;\"><p>".$row3['libelle']."</p></div>";
				//images de chaque option des caractéristiques
				if(!file_exists($pathsim.$artid."_".$row3['caractid']."_".$row3['caractvalid'].'.png')){//si aucune photo n'est enregistrée pour ce produit pour cette option
					$contenu_onglet2 .= "<div class=\"col-xs-4\" style=\"text-align:left;\"><input type=\"file\" name=\"opt_".$row3['caractid']."_".$row3['caractvalid']."\" onchange=\"validFormSim();\"></div></div>";
				}else{//si oui on a une photo existante
					$contenu_onglet2 .= "<div class=\"col-xs-4 edit-img\"><span class=\"edit\"><input type=\"file\" name=\"opt_".$row3['caractid']."_".$row3['caractvalid']."\" title=\"modifier\" onchange=\"validFormSim();\"></span><img src=\"../images/templates/delete.svg\" height=\"17px\" width=\"15px\" onclick=\"deleteImg('".$artid."','".$row3['caractid']."','".$row3['caractvalid']."');\"><a href=\"".$pathsim.$artid."_".$row3['caractid']."_".$row3['caractvalid'].".png?".time()."\" target=\"_blank\">APERCU</a></div></div>";
				}	
				$i++;
			}
		}

		$contenu_onglet2 .="</div></div>";

	}else{
		$checkedsimu1 ="checked";
	}

	$contenu_onglet .= "<div class=\"row\" style=\"margin-bottom:50px;\">"
						."<div class=\"col-xs-4\">Simulateur :</div>"
						."<div class=\"col-xs-4\" style=\"text-align:left;\"><input type=\"radio\" name=\"opt_simulateur\" onchange=\"validSimu(this.value,$artid);\" value=\"0\" style=\"margin:0;\" $checkedsimu0> Oui &nbsp;<input type=\"radio\" name=\"opt_simulateur\" value=\"1\" style=\"margin:0;\" onchange=\"validSimu(this.value,$artid);\" $checkedsimu1 > Non</div>"
						."<div class=\"col-xs-4\"><a onclick=\"miseAjourCaract($artid)\" style=\"cursor:pointer;\">Mise à jour des Caractéristiques</a></div>"
						."</div>";
	$contenu_onglet .= $contenu_onglet2;
	$contenu_onglet.="</form>";	
}
				
?>