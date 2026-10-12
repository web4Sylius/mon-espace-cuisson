<?php

require "admin_global.php";
include "./header.php";
include "./menu.php";

echo "
<td valign=\"top\">
<table width=\"100%\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\"><tr><td>
<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\">
  <tr>
   <td>
   	<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\">
		<tr>
			<td height=\"25\" nowrap> &nbsp; <img src=\"images/arrow_2.jpg\"> <font class=\"cost\">Texte accueil</font>
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
   <td align=\"justify\" valign=\"middle\"><br>
   	<font class=\"txt_2\">
		<table align=\"center\" width=\"100%\">
			<tr>
				<td align=\"center\" valign=\"middle\" width=\"100%\">" ;

echo "<h3>Personnaliser mon texte</h3>" ;

if (isset($action) and $action == "delimg1") {
	$DB_site2->query("update parametresgeneraux set img1 = '0'");
	$action = "" ;
	}
	
if (isset($action) and $action == "delimg2") {
	$DB_site2->query("update parametresgeneraux set img2 = '0'");
	$action = "" ;
	}

if (isset($action) and $action == "modif")
	{
	$erreur = "" ;
	if ($img1 != "") {
		if ($_FILES['img1']['name']!="") {
			if ($_FILES['img1']['error']==1)
				$erreur.="Erreur !!! Le fichier tꭩcharg顥xc饥 la taille autorisꥠpar le serveur (> 2Mb)";
			elseif ($_FILES['img1']['size']>1048576)
				$erreur.="erreur !!! Le fichier tꭩcharg顥xc饥 la taille autorisꥺ<br>Vous ne pouvez tꭩcharger qu'un fichier dont la taille est inf곩eur ࠱ Mo";
			elseif ($_FILES['img1']['error']==3)
				$erreur.="erreur !!! Le fichier n'a 굩 que partiellement tꭩcharg鮢;
			elseif ($_FILES['img1']['error']==2)
				$erreur.="erreur !!! ereur inconnue ...";
			elseif ($_FILES['img1']['error']==4)
				$erreur.="erreur !!! Aucun fichier n'a 굩 tꭩcharg鮢;
			elseif ($_FILES['img1']['type']!="image/pjpeg" && $_FILES['img1']['type']!="image/jpeg")
				$erreur.="erreur !!! Le fichier tꭩcharg顮'est pas du type autoris麼br>Vous ne pouvez tꭩcharger qu'un fichier du type JPEG (type envoy頺 ".$_FILES['img1']['type'].")";
			}
		else
			$erreur .= "Pas de fichier !" ;
		if ($erreur == "") {
			$nom_fic = $rootpath."configurations/$host/images/templates/header1.jpg" ;
			copier_image($nom_fic,'img1');
			$okimg1 = ", img1 = '1'" ;
			}
		}
	if ($img2 != "") {
		if ($_FILES['img2']['name']!="") {
			if ($_FILES['img2']['error']==1)
				$erreur.="Erreur !!! Le fichier tꭩcharg顥xc饥 la taille autorisꥠpar le serveur (> 2Mb)";
			elseif ($_FILES['img2']['size']>1048576)
				$erreur.="erreur !!! Le fichier tꭩcharg顥xc饥 la taille autorisꥺ<br>Vous ne pouvez tꭩcharger qu'un fichier dont la taille est inf곩eur ࠱ Mo";
			elseif ($_FILES['img2']['error']==3)
				$erreur.="erreur !!! Le fichier n'a 굩 que partiellement tꭩcharg鮢;
			elseif ($_FILES['img2']['error']==2)
				$erreur.="erreur !!! ereur inconnue ...";
			elseif ($_FILES['img2']['error']==4)
				$erreur.="erreur !!! Aucun fichier n'a 굩 tꭩcharg鮢;
			elseif ($_FILES['img2']['type']!="image/pjpeg" && $_FILES['img2']['type']!="image/jpeg")
				$erreur.="erreur !!! Le fichier tꭩcharg顮'est pas du type autoris麼br>Vous ne pouvez tꭩcharger qu'un fichier du type JPEG (type envoy頺 ".$_FILES['img2']['type'].")";
			}
		else
			$erreur .= "Pas de fichier !" ;
		if ($erreur == "") {
			$nom_fic = $rootpath."configurations/$host/images/templates/header2.jpg" ;
			copier_image($nom_fic,'img2');
			$okimg2 = ", img2 = '1'" ;
			}
		}
	$DB_site2->query("update parametresgeneraux set texte_occas = '$montexte' $okimg1 $okimg2");
	echo "<font class=\"cost\">Texte modifi頡 $erreur</font>" ;
	$action = "" ;
	}


if (!isset($action) or $action == "")
	{
	$texte=$DB_site2->query_first("select texte_occas, img1, img2 from parametresgeneraux");
	echo "<form method=\"post\" action=\"texte.php\" enctype=\"multipart/form-data\">
	<input type=\"hidden\" name=\"action\" value=\"modif\">
	<table align=\"center\">
		<tr>
			<td align=\"left\">Texte : </td>
			<td align=\"left\">" ;
			include($rootpath."fckeditor/fckeditor.php") ;	
			$oFCKeditor = new FCKeditor('montexte') ;
			$oFCKeditor->BasePath = '/fckeditor/' ;
			$oFCKeditor->Value		= $texte[0] ;
			$oFCKeditor->Height	= 400 ;
			$oFCKeditor->Width	= 445 ;
			echo $oFCKeditor->Create() ;
			echo "</td>
		</tr>
		<tr>
			<td align=\"left\">Image gauche en-t뵥 : </td>
			<td align=\"left\">" ;
			if ($texte[1] == 0)
				echo "<input type=\"file\" name=\"img1\">" ;
			else
				echo "<img src=\"/configurations/$host/images/templates/header1.jpg\"> <a href=\"texte.php?action=delimg1\">Supprimer</a>" ;
			echo "</td>
		</tr>
		<tr>
			<td align=\"left\">Image droite en-t뵥 : </td>
			<td align=\"left\">" ;
			if ($texte[2] == 0)
				echo "<input type=\"file\" name=\"img2\">" ;
			else
				echo "<img src=\"/configurations/$host/images/templates/header2.jpg\"> <a href=\"texte.php?action=delimg2\">Supprimer</a>" ;
			echo "</td>
		</tr>
	</table>
	<br><br>
	<input type=\"submit\" value=\"Modifier\">
	</form>
	" ;
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
        </tr>";

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
	@chmod ("$nom_fic", 0777) ;
	}


include "./footer.php";

?>