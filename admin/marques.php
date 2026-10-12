<?php

require "admin_global.php";
include "./header.php";
include "./menu.php";

include_once("../class/article_stat.php");

$filter_langue=array_diff($langues,$langue_access)!=array();

echo "<h1>Marques</h1>" ;

//VISIBILITE MENU FOOTER
if(isset($action) && $action=='menu_footer'){
	$action="";
	foreach($langues as $l){
		if($l == 'FR' || $l == 'EN')
			$config[$l] = $DB_site2->query_first("SELECT libelleFooter".$l." FROM marque_description WHERE marqueid=".$_GET['marqueid']);	
	}
	
	if($config['FR'][0] == '' || $config['EN'][0] == '')
		$action = "config_err";

	if($action!="config_err"){
		$visible = 0;
		$req = $DB_site2->query_first("SELECT menu_footer FROM marque WHERE marqueid=".$_GET['marqueid']);
		if($req['menu_footer'] == 0)
			$visible = 1;
		else
			$visible = 0;

		$update = $DB_site2->query("UPDATE marque SET menu_footer=".$visible." WHERE marqueid=".$_GET['marqueid']);
	}
	$action = "" ;
}


//VISIBILITE MENU HOME
if(isset($action) && $action=='menu_home'){
	$visible = 0;
	$req = $DB_site2->query_first("SELECT menu_home FROM marque WHERE marqueid=".$_GET['marqueid']);
	if($req['menu_home'] == 0)
		$visible = 1;
	else
		$visible = 0;

	$update = $DB_site2->query("UPDATE marque SET menu_home=".$visible." WHERE marqueid=".$_GET['marqueid']);
	$action = "" ;
}


// MODIFIER LES PARAMETRES
if (isset($action) and $action == "parametres"){
	if (is_numeric($marque_nb_ligne) and is_numeric($marque_max)){
		$parametres=$DB_site2->query("update parametresgeneraux SET marque_affichage = '$marque_affichage', 
									marque_nb_ligne = '$marque_nb_ligne', marque_max = '$marque_max', marque_tri = '$marque_tri'");
	}
	$action = "" ;
}

// MODIFIER UNE MARQUE
if (isset($action) and $action == "modifier"){
	if ($action2 == "supprimage"){
		$mq=$DB_site2->query_first("SELECT * from marque where marqueid = '$marqueid'");
		$fichier = $mq[marqueid].".".$mq[image] ;
		$folder = $rootpath."configurations/$host/images/marques";
		$folder2 = $rootpath."configurations/$host/images/marques/br";
		if (file_exists($folder."/".$fichier)){
			unlink($folder."/".$fichier);
			unlink($folder2."/".$fichier);
			$DB_site2->query("update marque set image = '' where marqueid = '$marqueid'");
		}
		$mq=$DB_site2->query_first("SELECT * from marque where marqueid = '$marqueid'");
	}
	$mq=$DB_site2->query_first("SELECT * from marque where marqueid = '$marqueid'");
	$mq_libelle = $mq['libelle'] ;
	$mq_titreh1FR = $mq['titreh1FR'] ;
	$mq_titreh1EN = $mq['titreh1EN'] ;
	
	if ($mq[image] != ""){
		$fichier = $mq[marqueid].".".$mq[image] ;
		$folder = $rootpath."configurations/$host/images/marques";
		if (file_exists($folder."/".$fichier)){
			$image = "<a href=\"http://$host/configurations/$host/images/marques/$fichier\" target=\"_blank\">Voir</a> <a href=\"marques.php?action=modifier&action2=supprimage&marqueid=$marqueid\">Supprimer</a>" ;
		}else{
			$image = "<input type=\"file\" name=\"mq_image\">" ;
		}
	}else{
		$image = "<input type=\"file\" name=\"mq_image\">" ;
	}
	echo "<h3>Modifier une marque</h3>
	<form method=\"post\" enctype=\"multipart/form-data\">
	<input type=\"hidden\" name=\"action\" value=\"modifier2\">
	<table>
	<tr>
	<td>Libellé : </td>
	<td><input type=\"text\" name=\"mq_libelle\" value=\"$mq_libelle\"".($filter_langue?" readonly":"")."></td>
	</tr>
	<tr>
	<td>Image : </td>
	<td>$image</td>
	</tr>
	<tr>
	<td>URL : <br><small>uniquement partie référencement</small></td><td>";
	foreach($langues as $langue){
		$flag=$langue;
		if($flag=="EN") $flag="GB";
		if($flag=="SP") $flag="ES";
		echo "<img src=\"images/drapeaux/$flag.gif\" width=\"20\"> ".(in_array($langue,$langue_access)?"<input type=\"text\" name=\"mq_url".$langue."\" value=\"".$mq['url'.$langue]."\" size=\"40\">":$mq['url'.$langue])."<br>";
	}
	echo "</td></tr>
	<tr>
	<td>Type de produit :<br><small>pour le référencement</small></td>
	<td>";

	foreach($langues as $langue){
		$flag=$langue;
		if($flag=="EN") $flag="GB";
		if($flag=="SP") $flag="ES";
		print "<img src=\"images/drapeaux/$flag.gif\" width=\"20\"> ".(in_array($langue,$langue_access)?"<input type=\"text\" name=\"type$langue\" value=\"".$mq['type'.$langue]."\">":$mq['type'.$langue])."<br>";
	}

	echo "	</td>
	</tr>

	<tr>
	<td>Titre H1 :<br><small>pour la page marque</small></td>
	<td>";
	foreach($langues as $langue){
		$flag=$langue;
		if($flag=="EN") $flag="GB";
		if($flag!=="SP"){
			print "<img src=\"images/drapeaux/$flag.gif\" width=\"20\"> ".(in_array($langue,$langue_access)?"<input type=\"text\" name=\"mq_titreh1$langue\" value=\"".$mq['titreh1'.$langue]."\">":$mq['titreh1'.$langue])."<br>";
		}
	}
	echo "	
	</td>
	</tr>
	</table>
	<br><input type=\"submit\" value=\"Modifier\">
	<br></form><br><a href=\"marques.php".(isset($_GET['order'])?"?order=".$_GET['order']:"")."\">Retour</a>
	" ;
}

// MODIFIER UNE MARQUE
if (isset($action) and $action == "modifier2"){
	$erreur = "" ;

	if ($mq_libelle != ""){
		$sql="";
		foreach($langues as $langue) if(in_array($langue,$langue_access) && $langue!=="SP") $sql.=", type$langue='".mysqli_real_escape_string($DB_site2->link_id,$_POST['type'.$langue])."', url$langue='".$_POST['mq_url'.$langue]."', titreh1$langue='".$_POST['mq_titreh1'.$langue]."'";
		$DB_site2->query("update marque set libelle = '".mysqli_real_escape_string($DB_site2->link_id,$mq_libelle)."' $sql where marqueid = '$marqueid'");
	}

	if ($mq_image != ""){
		if ($_FILES['mq_image']['name']!=""){
			if ($_FILES['mq_image']['error']==1){
				$erreur.="Erreur !!! Le fichier téléchargé excède la taille autorisée par le serveur (> 2Mb)";
			}elseif ($_FILES['mq_image']['size']>1048576){
				$erreur.="erreur !!! Le fichier téléchargé excède la taille autorisée:<br>Vous ne pouvez télécharger qu'un fichier dont la taille est inférieur à 1 Mo";
			}elseif ($_FILES['mq_image']['error']==3){
				$erreur.="erreur !!! Le fichier n'a été que partiellement téléchargé.";
			}elseif ($_FILES['mq_image']['error']==2){
				$erreur.="erreur !!! ereur inconnue ...";
			}elseif ($_FILES['mq_image']['error']==4){
				$erreur.="erreur !!! Aucun fichier n'a été téléchargé.";
			}elseif ($_FILES['mq_image']['type']!="image/pjpeg" && $_FILES['mq_image']['type']!="image/gif" && $_FILES['mq_image']['type']!="image/png" && $_FILES['mq_image']['type']!="image/jpeg"){
				$erreur.="erreur !!! Le fichier téléchargé n'est pas du type autorisé:<br>Vous ne pouvez télécharger qu'un fichier du type JPEG , JPG , GIF ou PNG (type envoyé : ".$_FILES['articleimg']['type'].")";
			}
		}
		if ($erreur == ""){
			if ($_FILES['mq_image']['type'] == "image/pjpeg"){
				$type_fichier = "jpg" ;
			}elseif ($_FILES['mq_image']['type']=="image/jpeg"){
				$type_fichier = "jpg" ;
			}elseif ($_FILES['mq_image']['type'] == "image/gif"){
				$type_fichier = "gif" ;
			}elseif($_FILES['mq_image']['type']=="image/png"){
				$type_fichier="png";
			}
			$DB_site2->query("update marque set image = '$type_fichier' where marqueid = '$marqueid'");
			$nom_fic=$rootpath."configurations/$host/images/marques/".$marqueid.".".$type_fichier;
			copier_image($nom_fic,'mq_image');
			$destination=$rootpath."configurations/$host/images/marques/br/".$marqueid.".".$type_fichier;
			redimentionner_image($nom_fic,$destination,$marque_largeur,$marque_hauteur);
		}
		echo $erreur ;
	}else{
		$erreur = "Les deux champs sont obligatoires" ;
	}
	$action = "" ;
}

if($_POST['action']=="promos"){
    $sql="";
    foreach($langues as $l) 
	if(in_array($l,$langue_access)) $sql.=",title$l='".mysqli_real_escape_string($DB_site2->link_id,$_POST['title'.$l])."', description$l='".mysqli_real_escape_string($DB_site2->link_id,$_POST['description'.$l])."', ref_title$l='".mysqli_real_escape_string($DB_site2->link_id,$_POST['ref_title'.$l])."', url$l='".$_POST['url'.$l]."', link$l='".mysqli_real_escape_string($DB_site2->link_id,$_POST['link'.$l])."'";
    
    $promo=$DB_site2->query_first("SELECT marqueid FROM marque_promo WHERE marqueid=".intval($_POST['marqueid']));
    if($promo['marqueid']!=$_POST['marqueid'])
	$sql="INSERT INTO marque_promo SET marqueid=".intval($_POST['marqueid']).$sql;
    else
	$sql="UPDATE marque_promo SET ".substr($sql,1)." WHERE marqueid=".intval($_POST['marqueid']);

    $result=$DB_site2->query($sql);
    if($result) $msg="<p style=\"color:green;\">La modification a bien été prise en compte. <a href=\"marques.php".(isset($_GET['order'])?"?order=".$_GET['order']:"")."\">Retour</a></p>";
}

if (isset($action) and $action == "promos"){
	$marqueid=intval($marqueid);
	$marque=$DB_site2->query_first("SELECT libelle FROM marque WHERE marqueid=".$marqueid);
	$promo=$DB_site2->query_first("SELECT * FROM marque_promo WHERE marqueid=".$marqueid);
	
	print $msg;

	print "<h3>Modifier la page promo ".$marque['libelle']."</h3>
	<form method=\"post\" name=\"form\" enctype=\"multipart/form-data\">
	<input type=\"hidden\" name=\"action\" value=\"promos\">
	<input type=\"hidden\" name=\"marqueid\" value=\"".$marqueid."\">";
	
	$art_stat=new article_stat($DB_site2);
	print $art_stat->fieldlist();

	foreach($langues as $l) {
	    $flag=$l;
	    if($l=="EN") $flag="GB";
	    if($l=="SP") $flag="ES";
	    $readonly=!in_array($l,$langue_access);
	    
	    print "<p>Titre <img src=\"images/drapeaux/br/$flag.gif\"> : ".($readonly?$promo['title'.$l]:"<input type=\"text\" name=\"title$l\" value=\"".$promo['title'.$l]."\" size=\"50\">")."</p>
	    <p>Description <img src=\"images/drapeaux/br/$flag.gif\"> : ".($readonly?$promo['description'.$l]:"<textarea name=\"description$l\" class=\"ckeditor\">".$promo['description'.$l]."</textarea>")."</p>
	    <p>Méta title <img src=\"images/drapeaux/br/$flag.gif\"> : ".($readonly?$promo['ref_title'.$l]:"<input type=\"text\" name=\"ref_title$l\" value=\"".$promo['ref_title'.$l]."\" size=\"50\">")."</p>
	    <p>URL <img src=\"images/drapeaux/br/$flag.gif\"> : ".($readonly?$promo['url'.$l]:"<input type=\"text\" name=\"url$l\" value=\"".$promo['url'.$l]."\" size=\"50\">")."</p>
	    <p>Texte lien <img src=\"images/drapeaux/br/$flag.gif\"> : ".($readonly?$promo['link'.$l]:"<input type=\"text\" name=\"link$l\" value=\"".$promo['link'.$l]."\" size=\"50\">")."</p>
	    <hr>";
	}
	
	print "<input type=\"button\" onclick=\"verifform()\" value=\"Enregistrer\">
	</form>
	<a href=\"marques.php\">Retour</a></p>";
	
	print "<script language=\"javascript\">
	var langues=new Array('".implode("','",$langues)."');
	function verifform() {
	    var warnempty=false;
	    for(i=0;i<langues.length;i++){
		var url=document.getElementsByName('url'+langues[i])[0].value;
		if(url=='') warnempty=true;
		if(!verifurl(url)){
		    alert(\"L'url \"+langues[i]+\" doit commencer par un répertoire représentant le type de produit ex: cave-vin/\");
		    return;
		}
	    }
	    if(warnempty) alert(\"Attention : les pages dont l'url n'est pas remplie ne s'afficheront pas\");
	    document.forms.form.submit();
	}
	
	function verifurl(url) {
	    if(url=='') return true;
	    var rep=url.split('/');
	    return (rep.length>1);
	}
	</script>";
}

if($_POST['action']=="description"){
    $sql="";
    $sql_ref_desc ="";
    $sql_ref_title="";
    foreach($langues as $l){
		if(in_array($l,$langue_access)){
			$sql.=',description'.$l.'="'.mysqli_real_escape_string($DB_site2->link_id,$_POST['description'.$l]).'" ';
			$sql.=',ref_title'.$l.'="'.mysqli_real_escape_string($DB_site2->link_id,$_POST['ref_title'.$l]).'" ';
			$sql.=',ref_description'.$l.'="'.mysqli_real_escape_string($DB_site2->link_id,$_POST['ref_description'.$l]).'" ';
			//$sql.=',libelle'.$l.'="'.mysqli_real_escape_string($DB_site2->link_id,$_POST['libelle'.$l]).'" ';
			$sql.=',libelleFooter'.$l.'="'.mysqli_real_escape_string($DB_site2->link_id,$_POST['libelleFooter'.$l]).'" ';
		}
	}
    $description=$DB_site2->query_first("SELECT marqueid FROM marque_description WHERE marqueid=".intval($_POST['marqueid']));
    if($description['marqueid']!=$_POST['marqueid'])
		$sql="INSERT INTO marque_description SET marqueid=".intval($_POST['marqueid']).$sql;
    else
		$sql="UPDATE marque_description SET ".substr($sql,1)." WHERE marqueid=".intval($_POST['marqueid']);


    $result=$DB_site2->query($sql);
    if($result)
		$msg="<p style=\"color:green;\">La modification a bien été prise en compte. <a href=\"marques.php".(isset($_GET['order'])?"?order=".$_GET['order']:"")."\">Retour</a></p>";
}

if (isset($action) and $action == "description"){
	$marqueid=intval($marqueid);
	$marque=$DB_site2->query_first("SELECT libelle FROM marque WHERE marqueid=".$marqueid);
	$description=$DB_site2->query_first("SELECT * FROM marque_description WHERE marqueid=".$marqueid);
	
	print $msg;

	print "<h3>Modifier la description de ".$marque['libelle']."</h3>
	<form method=\"post\" name=\"form\" enctype=\"multipart/form-data\">
	<input type=\"hidden\" name=\"action\" value=\"description\">
	<input type=\"hidden\" name=\"marqueid\" value=\"".$marqueid."\">";
	
	//$art_stat=new article_stat($DB_site2);
	//print $art_stat->fieldlist();

	foreach($langues as $l) {
	    $flag=$l;
	    if($l=="EN")
			$flag="GB";
	    if($l=="SP")
			$flag="ES";
	    $readonly=!in_array($l,$langue_access);
	    
	    /*Ne sers plus pour l'instant
	    echo "<p><img src=\"images/drapeaux/" . $flag . ".gif\" width=\"20\"> Titre de la page " . $l . " : " . ($readonly ? $description['libelle' . $l] : "<input type=\"text\" name=\"libelle" . $l . "\" value=\"" . $description['libelle' . $l] . "\" size=\"60\">") . "</p>";*/
		echo "<p><img src=\"images/drapeaux/" . $flag . ".gif\" width=\"20\"> Titre court (footer) de la page " . $l . " : ".($readonly ? $description['libelleFooter' . $langue] : "<input type=\"text\" name=\"libelleFooter" . $l . "\" value=\"" . $description['libelleFooter' . $l] . "\" size=\"60\">") . "</p>"
	    ."<p>Description <img src=\"images/drapeaux/br/$flag.gif\"> : ".($readonly?$description['description'.$l]:"<textarea name=\"description$l\" class=\"ckeditor\">".$description['description'.$l]."</textarea>")."</p>
	    <hr>";
	    echo ""
		. "<p><img src=\"images/drapeaux/" . $flag . ".gif\" width=\"20\"> Meta title " . $l . " : ".($readonly ? $description['ref_title' . $l] : "<input type=\"text\" name=\"ref_title" . $l . "\" value=\"" . $description['ref_title' . $l] . "\" size=\"60\">") . "</p>"
		. "<p><img src=\"images/drapeaux/" . $flag . ".gif\" width=\"20\"> Meta description :<br>".($readonly ? $description['ref_description' . $l] : "<textarea name=\"ref_description" . $l . "\" cols=\"80\" rows=\"4\">" . $description['ref_description' .$l] . "</textarea>")."</p>"
		. "<hr><br>";
	}

	
	print "<input type=\"submit\" onclick=\"verifform()\" value=\"Enregistrer\">
	</form>
	<a href=\"marques.php\">Retour</a></p>";
}

if(isset($_POST['action']) && $_POST['action']=="archive"){
	$sql="";
	foreach($langues as $l) 
	    if(in_array($l,$langue_access)) $sql.=",title$l='".mysqli_real_escape_string($DB_site2->link_id,$_POST['title'.$l])."', description$l='".mysqli_real_escape_string($DB_site2->link_id,$_POST['description'.$l])."', url$l='".$_POST['url'.$l]."'";
	
	$arch=$DB_site2->query_first("SELECT marqueid FROM marque_archive WHERE marqueid=".intval($_POST['marqueid']));
	if($arch['marqueid']!=$_POST['marqueid'])
	    $sql="INSERT INTO marque_archive SET marqueid=".intval($_POST['marqueid']).$sql;
	else
	    $sql="UPDATE marque_archive SET ".substr($sql,1)." WHERE marqueid=".intval($_POST['marqueid']);

	$result=$DB_site2->query($sql);
	if($result) $msg="<p style=\"color:green;\">La modification a bien été prise en compte. <a href=\"marques.php".(isset($_GET['order'])?"?order=".$_GET['order']:"")."\">Retour</a></p>";
}

if(isset($_POST['action']) && $_POST['action']=="gamme"){
        $sql="";
	foreach($langues as $l) 
	    if(in_array($l,$langue_access)) $sql.=",title$l='".mysqli_real_escape_string($DB_site2->link_id,$_POST['title'.$l])."', description$l='".mysqli_real_escape_string($DB_site2->link_id,$_POST['description'.$l])."', ref_title$l='".mysqli_real_escape_string($DB_site2->link_id,$_POST['ref_title'.$l])."', url$l='".$_POST['url'.$l]."'";
	
	$arch=$DB_site2->query_first("SELECT marqueid FROM marque_gamme WHERE marqueid=".intval($_POST['marqueid'])." AND gamme='".mysqli_real_escape_string($DB_site2->link_id,$_POST['gamme'])."'");
	if($arch['marqueid']!=$_POST['marqueid'])
	    $sql="INSERT INTO marque_gamme SET marqueid=".intval($_POST['marqueid']).", gamme='".mysqli_real_escape_string($DB_site2->link_id,$_POST['gamme'])."'".$sql;
	else
	    $sql="UPDATE marque_gamme SET ".substr($sql,1)." WHERE marqueid=".intval($_POST['marqueid'])." AND gamme='".mysqli_real_escape_string($DB_site2->link_id,$_POST['gamme'])."'";

	$result=$DB_site2->query($sql);
	if($result) $msg="<p style=\"color:green;\">La modification a bien été prise en compte. <a href=\"marques.php".(isset($_GET['order'])?"?order=".$_GET['order']:"")."\">Retour</a></p>";
}

if (isset($action) and $action == "archive"){
	$marqueid=intval($marqueid);
	$marque=$DB_site2->query_first("SELECT libelle FROM marque WHERE marqueid=".$marqueid);
	$arch=$DB_site2->query_first("SELECT * FROM marque_archive WHERE marqueid=".$marqueid);
	
	print "<h3>Modifier la page archive ".$marque['libelle']."</h3>
	<form method=\"post\" name=\"form\" enctype=\"multipart/form-data\">
	<input type=\"hidden\" name=\"action\" value=\"archive\">
	<input type=\"hidden\" name=\"marqueid\" value=\"".$marqueid."\">";
	
	print $msg;
	
	foreach($langues as $l){
	    $flag=$l;
	    if($l=="EN") $flag="GB";
	    if($l=="SP") $flag="ES";
	    
	    print "<p>Titre <img src=\"images/drapeaux/br/$flag.gif\"> : <input type=\"text\" name=\"title$l\" value=\"".$arch['title'.$l]."\" size=\"100\"></p>
	    <p>Description <img src=\"images/drapeaux/br/$flag.gif\"> : <textarea name=\"description$l\" class=\"ckeditor\">".$arch['description'.$l]."</textarea></p>
	    <p>URL <img src=\"images/drapeaux/br/$flag.gif\"> : <input type=\"text\" name=\"url$l\" value=\"".$arch['url'.$l]."\" size=\"100\"></p>
	    <hr>";
	}

	print "<input type=\"submit\" value=\"Enregistrer\">
	</form>
	<a href=\"marques.php\">Retour</a></p>";
}

if (isset($action) and $action == "gamme"){
	$marqueid=intval($marqueid);
	$marque=$DB_site2->query_first("SELECT libelle FROM marque WHERE marqueid=".$marqueid);
	$gamme=$DB_site2->query_first("SELECT * FROM marque_gamme WHERE marqueid=".$marqueid." AND gamme='".$_GET['gamme']."'");
	
	print "<h3>Modifier la page gamme ".$_GET['gamme']." ".$marque['libelle']."</h3>
	<form method=\"post\" name=\"form\" enctype=\"multipart/form-data\">
	<input type=\"hidden\" name=\"action\" value=\"gamme\">
	<input type=\"hidden\" name=\"marqueid\" value=\"".$marqueid."\">
	<input type=\"hidden\" name=\"gamme\" value=\"".$_GET['gamme']."\">";
	
	print $msg;
	
	foreach($langues as $l){
	    $flag=$l;
	    if($l=="EN") $flag="GB";
	    if($l=="SP") $flag="ES";
	    
	    print "<p>Titre <img src=\"images/drapeaux/br/$flag.gif\"> : <input type=\"text\" name=\"title$l\" value=\"".$gamme['title'.$l]."\" size=\"100\"></p>
	    <p>Description <img src=\"images/drapeaux/br/$flag.gif\"> : <textarea name=\"description$l\" class=\"ckeditor\">".$gamme['description'.$l]."</textarea></p>
	    <p>Méta title <img src=\"images/drapeaux/br/$flag.gif\"> : <input type=\"text\" name=\"ref_title$l\" value=\"".$gamme['ref_title'.$l]."\" size=\"100\"></p>
	    <p>URL <img src=\"images/drapeaux/br/$flag.gif\"> : <input type=\"text\" name=\"url$l\" value=\"".$gamme['url'.$l]."\" size=\"100\"></p>
	    <hr>";
	}

	print "<input type=\"submit\" value=\"Enregistrer\">
	</form>
	<a href=\"marques.php\">Retour</a></p>";
}
	
// SUPPRIMER UNE MARQUE
/*if (isset($action) and $action == "supprimer"){
	echo "Etes-vous sûr(e) de vouloir supprimer cette marque ?<br><br>
	<a href=\"marques.php?action=supprimer2&marqueid=$marqueid\">OUI</a> | <a href=\"marques.php\">NON</a>" ;
}*/

// SUPPRIMER UNE MARQUE (validé)
/*if (isset($action) and $action == "supprimer2"){
	$position_page=$DB_site2->query_first("SELECT position FROM marque WHERE marqueid = '$marqueid'");
	$position_page = $position_page[0];
	$rq_positions_suivantes = $DB_site2->query("SELECT marqueid, position FROM marque WHERE position > $position_page" );
	while ($rs_positions_suivantes=$DB_site2->fetch_array($rq_positions_suivantes)){
		$position_temp = $rs_positions_suivantes[position] - 1;
		$DB_site2->query("UPDATE marque SET position = '$position_temp' WHERE marqueid = '$rs_positions_suivantes[marqueid]'");
	}
	$suppr=$DB_site2->query("delete from marque where marqueid = '$marqueid'");
	$suppr2=$DB_site2->query("delete from article_marque where marqueid = '$marqueid'");
	$action = "" ;
}*/


// AJOUTER UNE MARQUE (validé)
if (isset($action) and $action == "ajouter2"){
	$erreur = "" ;
	if ($mq_libelle != "" && $mq_image != ""){
		if ($_FILES['mq_image']['name']!=""){
			if ($_FILES['mq_image']['error']==1){
				$erreur.="Erreur !!! Le fichier téléchargé excède la taille autorisée par le serveur (> 2Mb)";
			}elseif ($_FILES['mq_image']['size']>1048576){
				$erreur.="erreur !!! Le fichier téléchargé excède la taille autorisée:<br>Vous ne pouvez télécharger qu'un fichier dont la taille est inférieur à 1 Mo";
			}elseif ($_FILES['mq_image']['error']==3){
				$erreur.="erreur !!! Le fichier n'a été que partiellement téléchargé.";
			}elseif ($_FILES['mq_image']['error']==2){
				$erreur.="erreur !!! ereur inconnue ...";
			}elseif ($_FILES['mq_image']['error']==4){
				$erreur.="erreur !!! Aucun fichier n'a été téléchargé.";
			}elseif ($_FILES['mq_image']['type']!="image/pjpeg" && $_FILES['mq_image']['type']!="image/gif" && $_FILES['mq_image']['type']!="image/png" && $_FILES['mq_image']['type']!="image/jpeg"){
				$erreur.="erreur !!! Le fichier téléchargé n'est pas du type autorisé:<br>Vous ne pouvez télécharger qu'un fichier du type JPEG , JPG , GIF ou PNG (type envoyé : ".$_FILES['articleimg']['type'].")";
			}
		}
		if ($erreur == ""){
			if ($_FILES['mq_image']['type'] == "image/pjpeg"){
				$type_fichier = "jpg" ;
			}elseif ($_FILES['mq_image']['type']=="image/jpeg"){
				$type_fichier = "jpg" ;
			}elseif ($_FILES['mq_image']['type'] == "image/gif"){
				$type_fichier = "gif" ;
			}elseif($_FILES['mq_image']['type']=="image/png"){
				$type_fichier="png";
			}
			$sql_colonne = $sql_value = "";
			foreach($langues as $langue){
				if(in_array($langue,$langue_access) && $langue!=="SP"){
					$sql_colonne.=",titreh1$langue";
					$sql_value .= ",'".$_POST['mq_titreh1'.$langue]."'";
				}
			}
			$last=$DB_site2->query_first("select max(position) from marque");
			$position = $last[0] + 1 ;
			$new=$DB_site2->query("insert into marque (libelle, image, url, position".$sql_colonne.") values ('".mysqli_real_escape_string($DB_site2->link_id,$mq_libelle)."', '$type_fichier', '$mq_url', '$position'".$sql_value.")");
			$insertid=$DB_site2->insert_id();
			$nom_fic=$rootpath."configurations/$host/images/marques/".$insertid.".".$type_fichier;
			copier_image($nom_fic,'mq_image');
			$destination=$rootpath."configurations/$host/images/marques/br/".$insertid.".".$type_fichier;
			redimentionner_image($nom_fic,$destination,$marque_largeur,$marque_hauteur);
			$action = "" ;
		} else {
			echo $erreur ;
			$action = "ajouter" ;
		}
	}else{
		$erreur = "Les champs sont obligatoires" ;
		echo $erreur ;
		$action = "ajouter" ;
	}
}

// AJOUTER UNE MARQUE
if (isset($action) and $action == "ajouter"){
	echo "<h3>Ajouter une marque</h3>
	<form method=\"post\" enctype=\"multipart/form-data\">
	<input type=\"hidden\" name=\"action\" value=\"ajouter2\">
	<table>
		<tr>
			<td>Libellé* : </td>
			<td><input type=\"text\" name=\"mq_libelle\" value=\"$mq_libelle\"></td>
		</tr>
		<tr>
			<td>Image* : </td>
			<td><input type=\"file\" name=\"mq_image\"></td>
		</tr>
		<tr>
			<td>URL : </td>
			<td><input type=\"text\" name=\"mq_url\" value=\"$mq_url\"></td>
		</tr>

	<tr>
	<td>Titre H1 :<br><small>pour la page marque</small></td>
	<td>";
	foreach($langues as $langue){
		$flag=$langue;
		if($flag=="EN") $flag="GB";
		if($flag!=="SP"){
			print "<img src=\"images/drapeaux/$flag.gif\" width=\"20\"> ".(in_array($langue,$langue_access)?"<input type=\"text\" name=\"mq_titreh1$langue\" value=\"".$mq['titreh1'.$langue]."\">":$mq['titreh1'.$langue])."<br>";
		}
	}
	echo "	
	</td>
	</tr>
	</table>			
	</table>
	<bR><input type=\"submit\" value=\"Ajouter\">
	<br></form><br><a href=\"marques.php\">Retour</a>" ;
}

// MONTER D'UNE POSITION
if (isset ($action) and $action == "up"){
	$position=$DB_site2->query_first("SELECT position FROM marque WHERE marqueid = '$marqueid'");
	$nouvelle_position_marque = $position[0] - 1 ;
	$position2=$DB_site2->query_first("SELECT marqueid FROM marque WHERE position = '$nouvelle_position_marque'");
	$DB_site2->query("UPDATE marque SET position = '$nouvelle_position_marque' WHERE marqueid = '$marqueid'");
	$DB_site2->query("UPDATE marque SET position = '$position[0]' WHERE marqueid = '$position2[marqueid]'");
	echo "<script language=\"javascript\">location.href=\"marques.php\";</script>";
}

// DESCENDRE D'UNE POSITION
if (isset ($action) and $action == "down"){
	$position=$DB_site2->query_first("SELECT position FROM marque WHERE marqueid = '$marqueid'");
	$nouvelle_position_marque = $position[0] + 1 ;
	$position2=$DB_site2->query_first("SELECT marqueid FROM marque WHERE position = '$nouvelle_position_marque'" );
	$DB_site2->query("UPDATE marque SET position = '$nouvelle_position_marque' WHERE marqueid = '$marqueid'");
	$DB_site2->query("UPDATE marque SET position = '$position[0]' WHERE marqueid = '$position2[marqueid]'");
	echo "<script language=\"javascript\">location.href=\"marques.php\";</script>";
}

if (!isset($action) or $action == "" or $action == "config_err"){
	$order="libelle";
	if($action=='config_err'){
		echo "<script type=\"text/javascript\"> alert(\"Le Titre Footer FR ou EN de la page n'est pas configuré !!\")</script>";
		$action="";
	}

	/*
	if(isset($_GET['order'])) $order=$_GET['order'];
	
	if($order=="position") print "<p><a href=\"marques.php?order=libelle\">Afficher par ordre alphabétique</a></p>";
	if($order=="libelle") print "<p><a href=\"marques.php\">Afficher par ordre trié</a></p>";
	*/
	echo "<br><br><table class=\"table_moyen_paiement\" style=\"width:100%;\" cellspacing=\"2\">
		<tr>
			<td class=\"td_head_comparateurs\" width=\"50%\"><strong>Marque</strong></td>
			<td class=\"td_head_comparateurs\"><strong>Nb articles<br>actifs</strong></td>
			<td class=\"td_head_comparateurs\"><strong>Nb articles<br>inactifs</strong></td>
			<td class=\"td_head_comparateurs\"><strong>Nb articles<br>Supprimés</strong></td>
			<td class=\"td_head_comparateurs\"><strong>Nb articles<br>Archivés</strong></td>
			<td class=\"td_head_comparateurs\" nowrap><strong>modifier</strong></td>
			<td class=\"td_head_comparateurs\" nowrap><strong>description FR</strong></td>
			<td class=\"td_head_comparateurs\" nowrap><strong>description EN</strong></td>
			<td class=\"td_head_comparateurs\" nowrap><strong>promos FR</strong></td>
			<td class=\"td_head_comparateurs\" nowrap><strong>promos EN</strong></td>
			<td class=\"td_head_comparateurs\" nowrap><strong>archive FR</strong></td>
			<td class=\"td_head_comparateurs\" nowrap><strong>archive EN</strong></td>
			<td class=\"td_head_comparateurs\" nowrap><strong>page gamme</strong></td>
			<td class=\"td_head_comparateurs\" nowrap><strong>Footer</strong></td>
			<td class=\"td_head_comparateurs\" nowrap><strong>Home</strong></td>
		</tr>" ;
	$marques=$DB_site2->query("SELECT * from marque order by $order");
	$nb_marques=$DB_site2->num_rows($marques) ;
	$i_marque = 0 ;
	$rowalt=1;
	while ($marque=$DB_site2->fetch_array($marques)){
		$i_marque++ ;
		if ($nb_marques > 1 && !$filter_langue && $order=="position"){
			if ($i_marque == 1) { // Première marque
				$images_position = "<img src=\"images/vide.gif\" height=\"7\">
									<a href=\"marques.php?action=down&marqueid=$marque[marqueid]\">
									<img src=\"images/descendre.gif\" alt=\"Descendre\" title=\"Descendre\" height=\"7\" border=\"0\"></a>";
			}elseif ($i_marque == $nb_marques) {// Dernière marque
				$images_position = "<a href=\"marques.php?action=up&marqueid=$marque[marqueid]\">
									<img src=\"images/monter.gif\" alt=\"Monter\" title=\"Monter\" height=\"7\" border=\"0\"></a>
									<img src=\"images/vide.gif\" height=\"7\">";
			}else{
				$images_position = "<a href=\"marques.php?action=up&marqueid=$marque[marqueid]\">
									<img src=\"images/monter.gif\" alt=\"Monter\" title=\"Monter\" height=\"7\" border=\"0\"></a>
									<a href=\"marques.php?action=down&marqueid=$marque[marqueid]\">
									<img src=\"images/descendre.gif\" alt=\"Descendre\" title=\"Descendre\" height=\"7\" border=\"0\"></a>";
			}
		}
		
		$descriptionFR=$DB_site2->query_first("SELECT descriptionFR FROM marque_description WHERE marqueid=".$marque['marqueid']);
		$descriptionEN=$DB_site2->query_first("SELECT descriptionEN FROM marque_description WHERE marqueid=".$marque['marqueid']);
		
		$promoFR=$DB_site2->query_first("SELECT descriptionFR FROM marque_promo WHERE marqueid=".$marque['marqueid']);
		$promoEN=$DB_site2->query_first("SELECT descriptionEN FROM marque_promo WHERE marqueid=".$marque['marqueid']);
		
		$archiveFR=$DB_site2->query_first("SELECT descriptionFR FROM marque_archive WHERE marqueid=".$marque['marqueid']);
		$archiveEN=$DB_site2->query_first("SELECT descriptionFR FROM marque_archive WHERE marqueid=".$marque['marqueid']);
		
		$nbarticleactif=$DB_site2->query_first("SELECT COUNT(*) FROM article AS a
												INNER JOIN article_marque AS am ON (a.artid=am.artid)
												WHERE am.marqueid='".$marque['marqueid']."' AND a.active='1' ");
		$nbarticleinactif=$DB_site2->query_first("SELECT COUNT(*) FROM article AS a
												INNER JOIN article_marque AS am ON (a.artid=am.artid)
												WHERE am.marqueid='".$marque['marqueid']."' AND a.active='0' ");
		$nbarticlesuppr=$DB_site2->query_first("SELECT COUNT(*) FROM article AS a
												INNER JOIN article_marque AS am ON (a.artid=am.artid)
												WHERE am.marqueid='".$marque['marqueid']."' AND a.active='2' ");
		$nbarticlearch=$DB_site2->query_first("SELECT COUNT(*) FROM article AS a
												INNER JOIN article_marque AS am ON (a.artid=am.artid)
												WHERE am.marqueid='".$marque['marqueid']."' AND a.active='3' ");
                
        $access=$DB_site2->query_first("SELECT COUNT(*) FROM marque_gamme WHERE marqueid=".$marque['marqueid']." AND gamme='access'");
        $premium=$DB_site2->query_first("SELECT COUNT(*) FROM marque_gamme WHERE marqueid=".$marque['marqueid']." AND gamme='premium'");
        
		
		$rowalt=($rowalt%2);
		echo "<tr class=\"td_alt".$rowalt."\">
				<td class=\"td_location\" align=\"left\">$marque[libelle]</td>
				<td class=\"td_location\" align=\"center\">".$nbarticleactif[0]."</td>
				<td class=\"td_location\" align=\"center\">".$nbarticleinactif[0]."</td>
				<td class=\"td_location\" align=\"center\">".$nbarticlesuppr[0]."</td>
				<td class=\"td_location\" align=\"center\">".$nbarticlearch[0]."</td>
				<td class=\"td_location\" align=\"center\" nowrap><a href=\"marques.php?action=modifier&marqueid=$marque[marqueid]&order=$order\"><img src=\"images/editer.gif\"></a></td>
				<td class=\"td_location\" align=\"center\" nowrap><a href=\"marques.php?action=description&marqueid=$marque[marqueid]&order=$order\" style=\"color:".($descriptionFR[0]!=""?"green":"red")."\">Description FR</a></td>
				<td class=\"td_location\" align=\"center\" nowrap><a href=\"marques.php?action=description&marqueid=$marque[marqueid]&order=$order\" style=\"color:".($descriptionEN[0]!=""?"green":"red")."\">Description EN</a></td>
				<td class=\"td_location\" align=\"center\" nowrap><a href=\"marques.php?action=promos&marqueid=$marque[marqueid]&order=$order\" style=\"color:".($promoFR[0]!=""?"green":"red")."\">Promos FR</a></td>
				<td class=\"td_location\" align=\"center\" nowrap><a href=\"marques.php?action=promos&marqueid=$marque[marqueid]&order=$order\" style=\"color:".($promoEN[0]!=""?"green":"red")."\">Promos EN</a></td>
				<td class=\"td_location\" align=\"center\" nowrap><a href=\"marques.php?action=archive&marqueid=$marque[marqueid]&order=$order\" style=\"color:".($archiveFR[0]!=""?"green":"red")."\">Archive FR</a></td>
				<td class=\"td_location\" align=\"center\" nowrap><a href=\"marques.php?action=archive&marqueid=$marque[marqueid]&order=$order\" style=\"color:".($archiveEN[0]!=""?"green":"red")."\">Archive EN</a></td>
				<td class=\"td_location\" align=\"center\" nowrap><a href=\"marques.php?action=gamme&marqueid=$marque[marqueid]&order=$order&gamme=access\" style=\"color:".($access[0]>0?"green":"red")."\">Access</a><br><a href=\"marques.php?action=gamme&marqueid=$marque[marqueid]&order=$order&gamme=premium\" style=\"color:".($premium[0]>0?"green":"red")."\">Premium</a></td>
				<td class=\"td_location\" align=\"center\" nowrap><a href=\"marques.php?action=menu_footer&marqueid=$marque[marqueid]&order=$order\"><img src=\"images/".($marque[menu_footer]==1?"visible.png":"invisible.png")."\" width=\"30\"></a></td> 
				<td class=\"td_location\" align=\"center\" nowrap><a href=\"marques.php?action=menu_home&marqueid=$marque[marqueid]&order=$order\"><img src=\"images/".($marque[menu_home]==1?"visible.png":"invisible.png")."\" width=\"30\"></a></td> 
			</tr>" ;
		$rowalt++;
	}
	echo "</table><br><br>";
	
	if(!$filter_langue) print "<a href=\"marques.php?action=ajouter\">Ajouter une marque</a>" ;
}


include "./footer.php";


// fonction php images
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
			
			imagepng ($image, $destination , 80) ;
			}
		chmod ($destination, 0777) ;
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
		chmod ($destination, 0777) ;
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

function copier_image_url ($nom_fic, $url, $extension)
	{
	$ok = "non" ;
	switch ($extension)
		{
		case "gif":
			if ($monimage = @imagecreatefromgif($url))
				{
				$ok = "oui" ;
				imagegif ($monimage, $nom_fic , 80) ;
				chmod ($nom_fic, 0777) ;
				imagedestroy ($monimage) ;
				}
			break ;
		case "jpg":
			if ($monimage = @imagecreatefromjpeg($url))
				{
				$ok = "oui" ;
				imagejpeg ($monimage, $nom_fic , 80) ;
				chmod ($nom_fic, 0777) ;
				imagedestroy ($monimage) ;
				}
			break ;
		case "png":
			if ($monimage = @imagecreatefrompng($url))
				{
				$ok = "oui" ;
				imagepng ($monimage, $nom_fic , 80) ;
				chmod ($nom_fic, 0777) ;
				imagedestroy ($monimage) ;
				}
			break ;
		}
	if ($ok == "oui")
		{
		return true ;
		}
	else
		{
		return false ;
		}
	}

function categories_parentes($catid)
	{
	global $DB_site2, $cats ;
	$parente=$DB_site2->query_first("select parentid from categorie where catid = '$catid'");
	if ($parente[0] != 0)
		{
		$cats .= "," . $parente[0] ;
		categories_parentes($parente[0]);
		}
	return ($cats) ;
	}

?>