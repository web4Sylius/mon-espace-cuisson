<?php

require "admin_global.php";
include "./header.php";
include "./menu.php";

$lien="";
$couleur="#e4e1df";
$nom="";
$datedbt="";
$datefin="";

if(isset($_GET['site_code'])) 
	$site_code=$_GET['site_code'];
else
	$site_code = 'base';

if(isset($_GET['edit']) && ($msgButtValid!= "Ajouter"))
	{ 	$fondNum=$_GET['edit'];
		$noClick="style='pointer-events:none;'";
		
		// $site_code, $langue
		// nom='".$_POST['nom']."', lien='".$url."', fond_color='".$color."'
		
		//[id] => 310 [site_code] => base [langue] => FR [actif] => 0 [part] => 0 [pro] => 0 [nom] => [lien] => /noel_red.htm 
		//[fond_color] => #FFFFFF [date_dbt] => 1489622400 [date_fin] => 1489708800
		
		$req = $DB_site2->query_first("SELECT * FROM fond WHERE id=".$fondNum);
		
		$lien = $req["lien"];
		$couleur = $req["fond_color"];
		$nom = $req["nom"];
		if (isset($_POST[dbt]))
			$datedbt = $_POST[dbt];
		else // the date = date ( req["date_dbt"]) ??
			$datedbt = date("d/m/Y",$req["date_dbt"]);
		if (isset($_POST[fin]))
			$datefin = $_POST[fin];
		else $datefin = date("d/m/Y",$req["date_fin"]);
		
	} 
else
	{	$fondNum="";
		$noClick="";
	}

// gestion des radio boutons sur choix site d'activation de la page de fond
if(isset($_GET['actif'])){
	if(in_array($_GET['site'],array('part','pro','aucun','2'))){
		switch($_GET['site']){
			case 'aucun':
				$DB_site2->query("UPDATE fond SET `part`=0 WHERE id=".$_GET['actif']);
				$DB_site2->query("UPDATE fond SET `pro`=0 WHERE id=".$_GET['actif']);
			break;

			case '2':
				$DB_site2->query("UPDATE fond SET `part`=0 WHERE site_code='".$site_code."' AND langue='".$_GET['langue']."'");
				$DB_site2->query("UPDATE fond SET `pro`=0 WHERE site_code='".$site_code."' AND langue='".$_GET['langue']."'");
				$DB_site2->query("UPDATE fond SET `part`=1 WHERE id=".$_GET['actif']);
				$DB_site2->query("UPDATE fond SET `pro`=1 WHERE id=".$_GET['actif']);
			break;

			case 'part':
				$DB_site2->query("UPDATE fond SET `part`=0 WHERE site_code='".$site_code."' AND langue='".$_GET['langue']."'");
				$DB_site2->query("UPDATE fond SET `pro`=0 WHERE id=".$_GET['actif']);
				$DB_site2->query("UPDATE fond SET `part`=1 WHERE id=".$_GET['actif']);
			break;

			case 'pro':
				$DB_site2->query("UPDATE fond SET `pro`=0 WHERE site_code='".$site_code."' AND langue='".$_GET['langue']."'");
				$DB_site2->query("UPDATE fond SET `part`=0 WHERE id=".$_GET['actif']);
				$DB_site2->query("UPDATE fond SET `pro`=1 WHERE id=".$_GET['actif']);
			break;
		}
	}
}

// TSI effacer un fond d'ecran !! pas de verif avant effacement !! Ajout d'une message box avant appel => ok
if(isset($_GET['del']))
	$DB_site2->query("DELETE FROM fond WHERE id=".intval($_GET['del']));

// Modification / Ajout d'un nouveau fond LEFT
if(isset($_FILES['new_left']) && isset($_FILES['new_right'])){
	$extension_left = pathinfo($_FILES['new_left']['name'], PATHINFO_EXTENSION);
	$extension_right = pathinfo($_FILES['new_right']['name'], PATHINFO_EXTENSION);
	$file_left = file_exists("../configurations/".$host."/images/fond/".$_POST['id']."_left.jpg");
	$file_right = file_exists("../configurations/".$host."/images/fond/".$_POST['id']."_right.jpg");
	if(($extension_left=="jpg" && $extension_right == "jpg") || ($file_left && $file_right)){
		//URL
		$url="";
		if($_POST['link']!=""){
                    $ex_url = preg_split("/.fr|.com|.uk/", $_POST['link']);
                    $nb_url = count($ex_url);
                    $url = $ex_url[$nb_url-1];
                    if($url[0] != '/') 
                            $url = "/".$url;
                }
		//Code couleur
		$ex_color = preg_split("/#/", $_POST['couleur']);
		$nb_color = count($ex_color);
		$color = $ex_color[$nb_color-1];
		if($color[0] != '#')
			$color = "#".$color;
		//Date
		if($_POST['dbt']!='' && $_POST['fin']!=''){
			$dfrom=preg_split('/[- :\/h]/',$_POST['dbt']);
    		$dbt=mktime($dfrom[3],$dfrom[4],0,$dfrom[1],$dfrom[0],$dfrom[2]);
    		$dto=preg_split('/[- :\/h]/',$_POST['fin']);
    		$fin=mktime($dto[3],$dto[4],0,$dto[1],$dto[0],$dto[2]);
		}else{
			$dbt = 0;
			$fin = 0;
		}
		
		$bgfile="";
		
		// Ajout d'un fond
		if (isset($_POST["action"]) and $_POST["action"]=="Ajouter")
		{
			$DB_site2->query("INSERT INTO fond SET site_code='".$_POST['site']."', langue='".$_POST['langue']."', nom='".$_POST['nom']."', lien='".$url."', fond_color='".$color."', date_dbt=".$dbt.", date_fin=".$fin);
			$newid=$DB_site2->insert_id();
			$bgfile_left="../configurations/".$host."/images/fond/".$newid."_left.jpg";
			$bgfile_right="../configurations/".$host."/images/fond/".$newid."_right.jpg";
			
		}
		
		// Modifier un fond
		if (isset($_POST["action"]) and $_POST["action"]=="Modifier")
		{
			$updtReq = "UPDATE fond SET site_code='".$_POST['site']."', langue='".$_POST['langue']."', nom='".$_POST['nom']."', lien='".$url."', fond_color='".$color."', date_dbt=".$dbt.", date_fin=".$fin." WHERE id=".$_POST["id"];
			$DB_site2->query($updtReq);
			$bgfile_left="../configurations/".$host."/images/fond/".$_POST["id"]."_left.jpg";
			$bgfile_right="../configurations/".$host."/images/fond/".$_POST["id"]."_right.jpg";
		
		}
		
		if ($bgfile_left !="" && $bgfile_right !="") {
			$upload_left = move_uploaded_file($_FILES['new_left']['tmp_name'], $bgfile_left);
			$upload_right = move_uploaded_file($_FILES['new_right']['tmp_name'], $bgfile_right);
			if(!$upload_right && !$upload_left ){
				$upret="Vos fichiers n'ont pas pu être envoyés";
			}else{
				exec("jpegoptim ".$bgfile_left);
				exec("jpegoptim ".$bgfile_right);
				$upret="Fond d'écran ajouté !<br>";
				// remise a zero des variables une fois la sauvegarde effectuée, permet d'avoir l'onglet ajouter vide apres une modification
				$msgButtValid="";
				$fondNum="";
				$noClick="";
				$lien="";
				$couleur="";
				$nom="";
				$datedbt="";
				$datefin="";
				$_GET['edit']="";
				$_POST['edit']="";
				$msgButtValid== "";
			}
		}
		
	}else{
		if($_FILES['new_left']['name'] !='' || $_FILES['new_right']['name'] != '')
			$upret="<b><font color='red'> ! Seul le format jpg est autorisé, ou pas de fichier image selectionnné !</font><b>";
	}
}

echo "<h1>Fond d'écran</h1>";

if(!isset($site_code)) $optsite="<option></option>";
foreach($sites as $code=>$site){
	$optsite.="<option value=\"".$code."\"".($code==$site_code?" selected":"").">$code</option>";
}

echo "<p>Site : <select name=\"site\" onchange=\"document.location='style.php?site_code='+this.value\">".$optsite."</select></p>";

print $upret."<br>";

if(isset($site_code)){
	$site_langues=$sites[$site_code]['hosts'];
	print "<main class=\"main_tab\">";
	
	// choix de l'onglet affiché au chargment (TSI Ajout selection de l'onglet modification en mode edition et blocage retour autres onglets par $noClick)
	foreach($site_langues as $hostname => $langue){
		if($langue == 'FR' and $fondNum == "") $check = 'checked';
		else $check = '';
		print"
		<input id=\"chk_$langue\" type=\"radio\" name=\"photo_tabs\" class=\"inp_photo\" $check>
		<label for=\"chk_$langue\" $noClick>Fond $langue <img src=\"images/drapeaux/$langue.gif\" width=\"20\"></label>";
	}

	if( $fondNum != "") 
	{	$check = 'checked';
		$msgButtValid= "Modifier";
	}
	else
	{	$msgButtValid= "Ajouter";
	}
	print "<input id=\"chk_add\" type=\"radio\" name=\"photo_tabs\" class=\"inp_photo\" $check>
		<label for=\"chk_add\">Modifier/Ajouter <img src=\"images/icon/edit_add_22.png\" width=\"20\"></label>";
	
	
	// Affichage des fond d'écrans en fonction de la langue (FR, EN, ES)
	foreach($site_langues as $hostname => $langue){
		$optlang.="<option value=\"".$langue."\"".($langue=='FR'?" selected":"").">Fond $langue</option>";
		
		$req = $DB_site2->query("SELECT * FROM fond WHERE site_code='".$site_code."' AND langue='".$langue."' ORDER BY id DESC");

		print "
		<section id=\"back_$langue\" class=\"section_photo\">
		<form method=\"post\" enctype=\"multipart/form-data\">
			<table class=\"table_fond\">";

		$i = false;
		

		while($fond = $DB_site2->fetch_array($req)){
			$i = true;

			// TSI: gestion de l'affichage de l'icone "loupe" pour visualisation uniquement si le fond est actif
			$affVisu = 0;
			if(time() >= $fond['date_dbt'] && time() <= $fond['date_fin']){
				$affVisu=1;
			}
			
			print "
			<tr>
				<td colspan=\"2\" align=\"center\"><br><b>".$fond['nom']."</b><br>
					<img src=\"../configurations/$host/images/fond/".$fond['id']."_left.jpg\" class=\"left\" style=\"width:150px;margin-right:115px;\" >
					<img src=\"../configurations/$host/images/fond/".$fond['id']."_right.jpg\" class=\"right\" style=\"width:150px;\" >
				</td>
				<td>
					<img src=\"images/supprimer.gif\" title=\"Supprimer\" onclick=\"if (confirm('supprimer ?')) document.location='style.php?site_code=$site_code&del=".$fond['id']."';\" style=\"vertical-align:top; cursor:pointer;\"><br><br><br>
					<img src=\"images/icon/edit_pt.png\" title=\"Editer\" onclick=\"if (confirm('editer ?')) document.location='style.php?site_code=$site_code&edit=".$fond['id']."&langue=".$langue."';\" style=\"vertical-align:top; cursor:pointer;\"><br><br><br>";
			if ($affVisu==1) print "<a href='/' target='_blank'><img src=\"images/icon/viewmag_pt.png\" title=\"Voir\" style=\"vertical-align:top; cursor:pointer;\"></a>";
			print "
				</td>
			</tr>
			<tr>
				<td><b>Affichage :</b><br>
					<input type=\"radio\" name=\"fond0[$langue]\" ".($fond['part']+$fond['pro']==0?'checked':'')." value=\"".$fond['id']."\" onclick=\"document.location='style.php?actif=".$fond['id']."&site_code=$site_code&langue=$langue&site=aucun'\">Aucun
					<input type=\"radio\" name=\"fondpart[$langue]\" ".($fond['part']==1 && $fond['pro']==0?'checked':'')." value=\"".$fond['id']."\" onclick=\"document.location='style.php?actif=".$fond['id']."&site_code=$site_code&langue=$langue&site=part'\">Part
					<input type=\"radio\" name=\"fondpro[$langue]\" ".($fond['pro']==1 && $fond['part']==0?'checked':'')." value=\"".$fond['id']."\" onclick=\"document.location='style.php?actif=".$fond['id']."&site_code=$site_code&langue=$langue&site=pro'\">Pro
					<input type=\"radio\" name=\"fond2[$langue]\" ".($fond['part']+$fond['pro']==2?'checked':'')." value=\"".$fond['id']."\" onclick=\"document.location='style.php?actif=".$fond['id']."&site_code=$site_code&langue=$langue&site=2'\">Les 2
				</td>
				<td>du <b>".($fond['date_dbt']!=0 ? date("d/m/Y",$fond['date_dbt']) : '/')."</b> à <b>".($fond['date_dbt']!=0 ? date("H:i",$fond['date_dbt']) : '/')."</b> <br> au <b>".($fond['date_fin']!=0 ? date("d/m/Y",$fond['date_fin']) : '/')."</b> à <b>".($fond['date_fin']!=0 ? date("H:i",$fond['date_fin']) : '/')."</b><td>
				<td></td>	
			</tr>";
		}

		if($i == false) print "<tr><td><img src=\"images/drapeaux/$langue.gif\" width=\"20\"> <b>$langue :</b> Aucun fond d'ajouter pour cette langue</td></tr>";

		print "</table>
		</section>";
	} // fin de l'affichage detail fond d'ecrans

	// Affichage de la Gestion de l'ajout/modification d'un fond d'ecran 
	print "<section id=\"back_add\" class=\"section_photo\">
		<input type=\"hidden\" name=\"site\" value=\"$site_code\">
		<input type=\"hidden\" name=\"action\" value=\"$msgButtValid\">
		<input type=\"hidden\" name=\"id\" value=\"$fondNum\">
		<table class=\"table_fondAdd\">";
	
	// Affichage de l'ID du fond en modification
	if ($fondNum !="") 
		print "		<tr>
		<td>ID Modif :</td>
		<td> $fondNum</td>
		</tr>";
	
	print "		<tr>
				<td>
					<p style=\"margin-left:0;\">Nom :</p>
					<p style=\"margin-left:0;\">Image gauche: </p>
					<p style=\"margin-left:0;\">Image droite: </p>
				</td>
				<td>
					<input type=\"text\" name=\"nom\" placeholder=\"Pas obligatoire\" value=\"".$nom."\"> 
					<input type=\"file\" name=\"new_left\">
					<input type=\"file\" name=\"new_right\">
				</td>
			</tr>
			<tr>
				<td>Langue :</td>
				<td><select name=\"langue\">".$optlang."</select></td>
			</tr>
			<tr>
				<td>Lien :</td>
				<td><input type=\"text\" name=\"link\" placeholder=\"/url.html\" value=\"".$lien."\"></td>
			</tr>
			<tr>
				<td>Code couleur :<br><em>(couleur de fond)</em></td>
				<td><input type=\"text\" name=\"couleur\" value=\"".$couleur."\"></td>
			</tr>
			<tr>
				<td>Date de début :</td>
				<td><input id=\"dbt\" name=\"dbt\" onclick=\"calendrier_show(this.id,this.value,true);\" size=\"14\" type=\"text\" value=\"".$datedbt."\">
					<a href=\"javascript:cal_delete('dbt');\" class=\"cal_delete\" id=\"\">&nbsp;X&nbsp;</a></td>
			</tr>
			<tr>
				<td>Date de fin :</td>
				<td><input id=\"fin\" name=\"fin\" onclick=\"calendrier_show(this.id,this.value,true);\" size=\"14\" type=\"text\" value=\"".$datefin."\">
					<a href=\"javascript:cal_delete('fin');\" class=\"cal_delete\" id=\"\">&nbsp;X&nbsp;</a></td>
			</tr>
			<tr>
				<td colspan=\"1\"><input type=\"submit\" value=\"$msgButtValid\"></td>
				<td colspan=\"1\"><input type=\"button\" value=\"Annuler\" onclick=\"window.location='/admin/style.php?site_code=$site_code';\"></td>
			</tr>
		</table>
		
		</section>
		</form>
		</main>";
}

print "<script language=\"javascript\">
function cal_delete(value){
	var myValue = document.getElementsByName(value);
	myValue[0].value = '';
}
</script>";

include "./footer.php";

?>