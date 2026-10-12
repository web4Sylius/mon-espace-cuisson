<?php

require "admin_global.php";
include "./header.php";
include "./menu.php";

$langues = array("FR");

$error="";
if($_POST['action']=="ajouter2"){
    $DB_site2->query("INSERT INTO icons SET libelle='".$_POST['libelle']."'");
    $iconid=$DB_site2->insert_id();
    foreach($langues as $langue){
		if(!is_dir("../images/icons/".$langue))
			mkdir("../images/icons/".$langue);
		if(isset($_FILES['icon'.$langue])){
			if($_FILES['icon'.$langue]['type']!="image/png"){
				$error.="<br>icone $langue : il faut une image au format png";
			}else{
				$ret=move_uploaded_file($_FILES['icon'.$langue]['tmp_name'],"../images/icons/".$langue."/".$iconid.".png");
				if($ret==false)
					$error.="<br>icone $langue : erreur d'envoi du fichier";
			}
		}
    }
    unset($_POST['action']);
    unset($action);
}

if($_POST['action']=="editer2"){
    $DB_site2->query("UPDATE icons SET libelle='".$_POST['libelle']."' WHERE id=".$_POST['id']);
    foreach($langues as $langue){
		if(isset($_FILES['icon'.$langue])){
			if($_FILES['icon'.$langue]['type']!="image/png"){
				$error.="<br>icone $langue : il faut une image au format png";
			}else{
				$ret=move_uploaded_file($_FILES['icon'.$langue]['tmp_name'],"../images/icons/".$langue."/".$_POST['id'].".png");
				if($ret==false)
					$error.="<br>icone $langue : erreur d'envoi du fichier";
			}
		}
    }
    unset($_POST['action']);
    unset($action);
}

echo "
<td valign=\"top\">
<table width=\"100%\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\"><tr><td>
<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\">
  <tr>
   <td>
   	<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\">
		<tr>
			<td height=\"25\" nowrap> &nbsp; <img src=\"images/arrow_2.jpg\"> <font class=\"cost\">Icones</font>
			</td>
		</tr>
		<tr>
			<td background=\"images2/cadre_r3_c2.gif\" width=\"100%\" height=\"2\">
			</td>
		</tr>
	</table>
   </td>
   <td><img name=\"cadre_r2_c5\" src=\"images2/cadre_r2_c5.gif\" width=\"11\" height=\"27\" border=\"0\" alt=\"\"></td>
   <td background=\"images2/cadre_r2_c6.gif\" width=\"100%\" height=\"27\" align=\"center\" valign=\"middle\">
   </td>
   <td><img name=\"cadre_r2_c8\" src=\"images2/cadre_r2_c8.gif\" width=\"13\" height=\"27\" border=\"0\" alt=\"\"></td>
  </tr>
</table>

</tr></td>
<tr><td>

<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\">
  <tr>
   <td background=\"images2/cadre_r4_c2.gif\" width=\"3\" height=\"100%\"></td>
   <td align=\"justify\" valign=\"middle\">
   	<font class=\"txt_2\">
		<table align=\"center\" width=\"100%\">
			<tr>
				<td align=\"center\" valign=\"middle\" width=\"100%\">" ;

if(!isset($action)){
    if($error!="") print "<p style=\"color:red;\">".$error."</p>";
    print "<table>
    <tr><td colspan=\"".(count($langues)+2)."\" align=\"center\"><a href=\"icones.php?action=ajouter\">Ajouter une icone</a> <br>Format d'image : png transparent 71px x 71px</td></tr>
    <tr><td>Icone</td>";
    foreach($langues as $langue){
	$flag=$langue;
	if($langue=="EN") $flag="GB";
	if($langue=="SP") $flag="ES";
	print "<td align=\"center\" width=\"80\"><img src=\"images/drapeaux/$flag.gif\" width=\"20\"></td>";
    }
    print "<td></td></tr>";

    $requete=$DB_site2->query("SELECT * FROM icons WHERE 1");
    while($icon=$DB_site2->fetch_array($requete)){
	print "<tr><td>".$icon['libelle']."</td>";
	foreach($langues as $langue){
	    $path="../images/icons/".$langue."/".$icon['id'].".png";
	    if(file_exists($path))
		print "<td><img src=\"".$path."\"></td>";
	    else
		print "<td align=\"center\">X</td>";
	}
	print "<td><a href=\"icones.php?action=editer&id=".$icon['id']."\" title=\"Modifier\"><img src=\"images/editer.gif\"></a> <a href=\"\" title=\"Supprimer\"><img src=\"images/supprimer.gif\"></a></td>
	</tr>";
    }
    print "</table>";
}

if($action=="ajouter"){
    print "<h2>Ajouter une icone</h2>
    <form method=\"post\" action=\"icones.php\" enctype=\"multipart/form-data\">
    <input type=\"hidden\" name=\"action\" value=\"ajouter2\">
    <p>Format d'image : png transparent 71px x 71px</p>
    Nom : <input type=\"text\" name=\"libelle\">";
    foreach($langues as $langue){
	$flag=$langue;
	if($langue=="EN") $flag="GB";
	if($langue=="SP") $flag="ES";
	print "<br>Icone <img src=\"images/drapeaux/$flag.gif\" width=\"20\"> : <input type=\"file\" name=\"icon$langue\">";
    }
    print "<p align=\"center\"><input type=\"submit\" value=\"ajouter\"><br><a href=\"icones.php\">Annuler</a></p>
    </form>";
}

if($action=="editer" && isset($_GET['id'])){
    if(isset($_GET['del']) && file_exists("../images/icons/".$_GET['del']."/".$_GET['id'].".png")) unlink("../images/icons/".$_GET['del']."/".$_GET['id'].".png");

    $icon=$DB_site2->query_first("SELECT * FROM icons WHERE id=".$_GET['id']);
    print "<h2>Modifier une icone</h2>
    <form method=\"post\" action=\"icones.php\" enctype=\"multipart/form-data\">
    <input type=\"hidden\" name=\"action\" value=\"editer2\">
    <input type=\"hidden\" name=\"id\" value=\"".$_GET['id']."\">
    <p>Format d'image : png transparent 71px x 71px</p>
    Nom : <input type=\"text\" name=\"libelle\" value=\"".$icon['libelle']."\">
    <table>";
    foreach($langues as $langue){
	$flag=$langue;
	if($langue=="EN") $flag="GB";
	if($langue=="SP") $flag="ES";
	$img="";
	$path="../images/icons/".$langue."/".$icon['id'].".png";
	if(file_exists($path)) $img="<img src=\"$path\" align=\"absmiddle\"> <a href=\"icones.php?action=editer&id=".$_GET['id']."&del=$langue\"><img src=\"images/supprimer.gif\"></a>";
	print "<tr><td>Icone <img src=\"images/drapeaux/$flag.gif\" width=\"20\"></td><td>$img <img src=\"images/editer.gif\" onclick=\"document.getElementById('icon$langue').style.display='table-cell';\"></td><td id=\"icon$langue\" style=\"display:none\">Nouvelle image : <input type=\"file\" name=\"icon$langue\" size=\"0\"></td></tr>";
    }
    print "</table>
    <p align=\"center\"><input type=\"submit\" value=\"Modifier\"><br><a href=\"icones.php\">Annuler</a></p>
    </form>";
}

echo "</td>
			</tr>	
		</table>	
     </font>
	</td>
   <td background=\"images2/cadre_r4_c10.gif\" width=\"3\" height=\"100%\" border=\"0\" alt=\"\"></td>
  </tr>
</table>
</td>
</tr>
<tr><td>

<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\">
  <tr>
   <td rowspan=\"2\"><img name=\"cadre_r6_c2\" src=\"images2/cadre_r6_c2.gif\" width=\"10\" height=\"26\" border=\"0\" alt=\"\"></td>
   <td align=\"center\" height=\"23\" width=\"100%\"></td>
   <td rowspan=\"2\"><img name=\"cadre_r6_c9\" src=\"images2/cadre_r6_c9.gif\" width=\"8\" height=\"26\" border=\"0\" alt=\"\"></td>
  </tr>
  <tr>
   <td background=\"images2/cadre_r7_c4.gif\" width=\"100%\" height=\"3\"></td>
  </tr>
</table>
</td></tr></table>
</td>
</tr><tr>
</tr></table>
				  </td>
              </tr>
            </table>
		  </td>
        </tr>
";

include "./footer.php";