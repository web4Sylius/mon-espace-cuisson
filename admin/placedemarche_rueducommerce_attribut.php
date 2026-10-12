<?php

require "admin_global.php";
include "./header.php";
include "./menu.php";
include "./fonctions.php";	

echo "
<td valign=\"top\">
<table width=\"100%\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\"><tr><td>
<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\">
  <tr>
   <td>
   	<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\">
		<tr>
			<td height=\"25\" nowrap> &nbsp; <img src=\"images/arrow_2.jpg\"> <font class=\"cost\">Page de téléchargement de documents</font>
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
   <td align=\"center\" valign=\"middle\"><br>" ;



// MODIFIER UN ATTRIBUT
if (isset($action) and $action == "modifier") {
	$critere=$DB_site2->query_first("SELECT * FROM rueducommerce_attribut WHERE attributid = '$attributid'");
	$f_libelle = $critere[libelle] ;
	$code = $critere[code] ;
	$MCID = $critere[MCID] ;
	echo "<div class=\"titrefleche\">Modifier un attribut</div>
	<div class=\"right\"><a href=\"placedemarche_rueducommerce_attribut.php\"><img src=\"images/templates/btn_retour.jpg\" alt=\"\"></a></div>
	<form method=\"post\">
	<input type=\"hidden\" name=\"action\" value=\"domodifier\">
	<input type=\"hidden\" name=\"attributid\" value=\"$attributid\">
	<blockquote>
	<table>
		<tr>
			<td>MCID : </td>
			<td><input type=\"text\" name=\"MCID\" value=\"$MCID\"></td>
		</tr>	
		<tr>
			<td>Libellé attribut : </td>
			<td><input type=\"text\" name=\"f_libelle\" value=\"$f_libelle\"></td>
		</tr>
		<tr>
			<td>Code attribut : </td>
			<td><input type=\"text\" name=\"code\" value=\"$code\"></td>
		</tr>		
		<tr>
			<td colspan=\"2\" align=\"center\"><br><input type=\"submit\"></td>
		</tr>
	</table>
	<br>
	</blockquote>
	</form>
	" ;

}

// MODIFIER UN ATTRIBUT
if (isset($action) and $action == "domodifier") {
	$DB_site2->query("update rueducommerce_attribut set libelle='".addslashes($f_libelle)."', MCID = '$MCID', code = '$code' where attributid = '$attributid'");
	$action = "" ;
	}

// SUPPRIMER UN ATTRIBUT
if (isset($action) and $action == "supprimer"){
	echo "<div class=\"titrefleche\">Supprimer un attribut</div><br>
	<center>Etes-vous sûr(e) de vouloir supprimer ce attribut ?<br><br>
	<a href=\"placedemarche_rueducommerce_attribut.php?action=supprimer2&attributid=$attributid\"><img src=\"images/templates/btn_oui.jpg\"></a>&nbsp;&nbsp;
	<a href=\"placedemarche_rueducommerce_attribut.php\"><img src=\"images/templates/btn_non.jpg\"></a></center>";
	}

// SUPPRIMER UN ATTRIBUT (validé)
if (isset($action) and $action == "supprimer2") {
	$DB_site2->query("delete from rueducommerce_attribut where attributid = '$attributid'");
	$DB_site2->query("delete from article_attribut where attributid = '$attributid'");
	$action = "" ;
	}

// AJOUTER UN ATTRIBUT
if (isset($action) and $action == "ajouter") {
	echo "<div class=\"titrefleche\">Ajouter un attribut</div>
	<div class=\"right\"><a href=\"placedemarche_rueducommerce_attribut.php\"><img src=\"images/templates/btn_retour.jpg\" alt=\"\"></a></div>
	<form method=\"post\">
	<input type=\"hidden\" name=\"action\" value=\"doajouter\">
	<blockquote>
	<table>
		<tr>
			<td>MCID : </td>
			<td><input type=\"text\" name=\"MCID\" value=\"$MCID\" size=\"7\"></td>
		</tr>	
		<tr>
			<td>Libellé attribut : </td>
			<td><input type=\"text\" name=\"f_libelle\" value=\"$f_libelle\"></td>
		</tr>
		<tr>
			<td>Code attribut : </td>
			<td><input type=\"text\" name=\"code\" value=\"$code\"></td>
		</tr>		
		<tr>
			<td colspan=\"2\" align=\"center\"><bR><input type=\"submit\"></td>
		</tr>
	</table>
	<br>
	</blockquote>
	</form>
	" ;

}

// AJOUTER UN ATTRIBUT (validé)
if (isset($action) and $action == "doajouter") {
	$DB_site2->query("insert into rueducommerce_attribut (MCID, libelle, code) VALUES ( '$MCID', '".addslashes($f_libelle)."', '$code')");
	$action = "" ;	
	}




if (!isset($action) or $action == ""){
	// Pour getpagenav
	if (!isset($pagenumber) or $pagenumber == "") {
		$pagenumber = 1;
		unset($_SESSION['perpageSession']);
	}	
	if (isset($perpageAdmin) && $perpageAdmin != "") {
		if($_SESSION['perpageSession']!=$perpageAdmin) {
			$_SESSION['perpageSession'] = $perpageAdmin;
			$pagenumber = 1;
		}
	}else{
		if(!isset($_SESSION['perpageSession'])){
			$_SESSION['perpageSession'] = $perpage;
		}
	}
		
	$perpageSelect = $_SESSION['perpageSession'];
	
	$limitlower=($pagenumber-1)*$perpageSelect;
	$limitsql = " LIMIT $limitlower, $perpageSelect";
	
	$selectPerPageAdminBit = "";
	
	$countAttributs = $DB_site2->query_first("SELECT COUNT(*) FROM rueducommerce_attribut");
	
	// sélection par page
	if ($countAttributs[0] >= $perpage) {
		
		$perpageAdmin_selected = "";
		$perpageAdmin_value = $perpage;
		if ($perpageSelect == $perpageAdmin_value) {
			$perpageAdmin_selected = "selected";
		}
		$selectPerPageAdminBit .= "<option value=\"$perpageAdmin_value\" $perpageAdmin_selected>$perpageAdmin_value</option>";
		
		if ($countAttributs[0] > $perpage * 2) {
			$perpageAdmin_selected = "";
			$perpageAdmin_value = $perpage * 2;
			if ($perpageSelect == $perpageAdmin_value) {
				$perpageAdmin_selected = "selected";
			}
			$selectPerPageAdminBit .= "<option value=\"$perpageAdmin_value\" $perpageAdmin_selected>$perpageAdmin_value</option>";
		}
		
		if ($countAttributs[0] > $perpage * 4) {
			$perpageAdmin_selected = "";
			$perpageAdmin_value = $perpage * 4;
			if ($perpageSelect == $perpageAdmin_value) {
				$perpageAdmin_selected = "selected";
			}
			$selectPerPageAdminBit .= "<option value=\"$perpageAdmin_value\" $perpageAdmin_selected>$perpageAdmin_value</option>";
		}
		
		if ($countAttributs[0] > $perpage * 6) {
			$perpageAdmin_selected = "";
			$perpageAdmin_value = $perpage * 6;
			if ($perpageSelect == $perpageAdmin_value) {
				$perpageAdmin_selected = "selected";
			}
			$selectPerPageAdminBit .= "<option value=\"$perpageAdmin_value\" $perpageAdmin_selected>$perpageAdmin_value</option>";
		}
		$selectPerPageAdmin = "";
	}
	
	
	$pagenav=getpagenav2($countAttributs[0],"placedemarche_rueducommerce_attribut.php?perpageperso=$perpageperso");
	
	echo "<div align=\"center\"><font class=\"erreur\">$erreur</font></div>
	<div>Pour obtenir la liste des catégories, <a href='liste_categories_rdc.xls' target='_blank'>cliquez ici.</a></div>
	<div class=\"titrefleche\">Liste des attributs</div>" ;
	echo "<div align=\"center\"><br><a href=\"placedemarche_rueducommerce_attribut.php?action=ajouter\" target=\"_self\">Ajouter un attribut</a></div><br>";		
	echo "
		<div class=\"right\"><form action=\"placedemarche_rueducommerce_attribut.php\" method=\"post\" name=\"formuperpage\">$selectPerPageAdmin</form></div>	
		<div class=\"clear\"></div>
		<div class=\"pagenav\">$pagenav</div>
		
		<table class=\"tab_users\" cellpadding=\"4\" cellspacing=\"0\" align=\"center\">
		<tr class=\"th\">
			<th>MCID</th>
			<th>Attribut</th>
			<th>Code</th>
			<th width=\"150\" align=\"center\">Actions</th>
		</tr>
		";
			$criteres=$DB_site2->query("SELECT * FROM rueducommerce_attribut ORDER BY MCID, libelle LIMIT $limitlower , $perpage");
			$nb_criteres=$DB_site2->num_rows($criteres) ;
			$i_critere = 0 ;
			while ($critere=$DB_site2->fetch_array($criteres)){
				$rowalt = "td_users".getrowbg();
				$i_critere++ ;
				echo "
					<tr>
						<td class=\"$rowalt\">$critere[MCID]</td>
						<td class=\"$rowalt\">$critere[libelle]</td>
						<td class=\"$rowalt\">$critere[code]</td>
						<td class=\"$rowalt\" align=\"center\" width=\"150\">
							<a href=\"placedemarche_rueducommerce_attribut.php?action=modifier&attributid=$critere[attributid]\"><img src=\"images/editer.gif\" alt=\"\"></a> 
							<a href=\"placedemarche_rueducommerce_attribut.php?action=supprimer&attributid=$critere[attributid]\"><img src=\"images/supprimer.gif\" alt=\"\"></a> 
						</td>
					</tr>" ;
				}
		
	echo "
	</table><br>" ;
	echo "<div align=\"center\"><br><a href=\"placedemarche_rueducommerce_attribut.php?action=ajouter\" target=\"_self\">Ajouter un attribut</a></div>";	
	}



echo "</td>
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
        </tr>";

include "./footer.php";

function copier_fichier($nom_fic,$imagename)
	{
	if($OpenLocal= fopen($_FILES[$imagename]['tmp_name'], "rb")) 
		{
		$LocalFileContent = fread($OpenLocal, $_FILES[$imagename]['size']);
		} 
	else 
		{
		fclose($OpenLocal);
		die("<b>Erreur : </b>impossible d'ouvrir le fichier local '".$_POST[$imagename].".");
		}
	fclose($OpenLocal);
	if($OpenRemote = fopen($nom_fic, "wb")) 
		{
		if(fwrite($OpenRemote, $LocalFileContent)) 
			{
			fclose($OpenRemote);
			}
		}
	else 
		{
		fclose($OpenRemote);
		die("<b>Erreur : </b>impossible d'ouvrir le fichier distant '".$nom_fic.".");
		}
	chmod ("$nom_fic",0777);
	}

?>