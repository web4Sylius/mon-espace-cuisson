<?php

require "admin_global.php";
echo "<script language=\"JavaScript\">
<!--
function afficher(div)
	{
	document.getElementById(div).style.display='block';
	}
	
function cacher(div)
	{
	document.getElementById(div).style.display='none';
	}
//-->
</script>" ;
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
			<td height=\"25\" nowrap> &nbsp; <img src=\"images/arrow_2.jpg\"> <font class=\"cost\">Optimisation du rꧩrencement</font>
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

/*
Pour bien rꧩrencer une page avec des mots donn고:

    * Le nom de la page .html contient ces mots.
          o On peut s걡rer les mots par des espaces mais moi je prꧨre mettre des tirets (pour des raisons d'informaticien des 80ies)
    * Dans le titre de la page (balise HTML <title>)
          o limite autour de 128 caract鳥s
          o Il faut que 衠ait un sens (pas juste une succesion de mots).
          o C'est ce qui est affich顤ans le titre du browser + utilis顤ans les favoris
    * Dans les mots clefs de la page (balise HTML <meta name="Keywords"...)
          o pr귯ir les fautes de frappe
          o pr귯ir 귥ntuellement d'autres langues que le fran袩s
          o supprimer les articles et mots de liaison (le, la, du)
          o limite autour de 1024 caract鳥s.
          o pas de distinction de casse
          o Utiliser &xx; pour les caract鳥s accentu고?
    * Dans la description de la page (balise HTML <meta name="description"...)
          o +/- comme le titre (avec un sens)
    * Dans le texte de la page
          o Le texte doit 뵲e 'visible' (pas en noir sur fond noir).
    * Avec des bitmaps dont le nom contient ces mots.
    * Prꤩser les directives pour les robots "index et follow" (m뮥 si ce sont des options par dꧡut).

// 128 caracts : azertyuiopazertyuiopazertyuiopmloikujyhuiopmloooooooooooooooooooooooooooooooooooooooppppppppppppppppppppppjjjjjjjjjjjjjjjjjjjjjjj
*/

$script = array(0 => array("script" => "/index.php", "nom" => "Page d'accueil"),
				1 => array("script" => "/categorie.php", "nom" => "Page de pr괥ntation de rayon"),
				2 => array("script" => "/article.php", "nom" => "Page de pr괥ntation des articles"),
				3 => array("script" => "/institutionnel.php", "nom" => "Pages institutionnelles"),
				4 => array("script" => "/rechercher.php", "nom" => "Page de recherche"),
				5 => array("script" => "/client.php", "nom" => "Page d'info client"),
				6 => array("script" => "/passwordrecover.php", "nom" => "Page de rꤵperation de mot de passe"),
				7 => array("script" => "/cde1.php", "nom" => "Page de commande 1"),
				8 => array("script" => "/cde2.php", "nom" => "Page de commande 2"),
				9 => array("script" => "/cde3.php", "nom" => "Page de commande 3"),
				10 => array("script" => "/contacter.php", "nom" => "Page nous contacter"));

if ($action == "update") 
	{
	$pagetitle=$DB_site2->query_first("select * from pagetitle WHERE pagetitleid = '$pagetitleid'");
	if (in_array("FR" , $langues))
		{
		$pagetitletmp1=$modiftitleFR;
		$pagetitletmp1=str_replace( "[boutiquetitre]" , "\${\$title}" , $pagetitletmp1 );
		$pagetitletmp1=str_replace( "[categorietitre]" , "\$pagetitle_catname" , $pagetitletmp1 );
		$pagetitletmp1=str_replace( "[articletitre]" , "\$article[\$libelle]" , $pagetitletmp1 );
		$pagetitletmp1=str_replace( "[institutionneltitre]" , "\$institutionnel[\$libelle]" , $pagetitletmp1 );
		$pagetitletmp1=str_replace( "[sscatkeywords]" , "\$metasscat" , $pagetitletmp1 );
		$pagetitletmp1=str_replace( "[keydescription]" , "\$keydescription" , $pagetitletmp1 );
		$requetteupdate="$virgule titleFR='".addslashes($pagetitletmp1)."' ";
		$metadescription = $DB_site2->query_first(" select *  from description WHERE scriptname='$pagetitle[scriptname]' ");
		$pagetitletmp2=$modifdescriptionFR;
		$pagetitletmp2=str_replace( "[boutiquetitre]" , "\${\$title}" , $pagetitletmp2 );
		$pagetitletmp2=str_replace( "[categorietitre]" , "\$pagetitle_catname" , $pagetitletmp2 );
		$pagetitletmp2=str_replace( "[articletitre]" , "\$article[\$libelle]" , $pagetitletmp2 );
		$pagetitletmp2=str_replace( "[institutionneltitre]" , "\$institutionnel[\$libelle]" , $pagetitletmp2 );
		$pagetitletmp2=str_replace( "[sscatkeywords]" , "\$metasscat" , $pagetitletmp2 );
		$pagetitletmp2=str_replace( "[keydescription]" , "\$keydescription" , $pagetitletmp2 );
		$requetteupdate2="$virgule titleFR='".addslashes($pagetitletmp2)."' ";
		$metakeywords = $DB_site2->query_first(" select *  from keywords WHERE scriptname='$pagetitle[scriptname]' ");
		$pagetitletmp3=$modifkeywordsFR;
		$pagetitletmp3=str_replace( "[boutiquetitre]" , "\${\$title}" , $pagetitletmp3 );
		$pagetitletmp3=str_replace( "[categorietitre]" , "\$pagetitle_catname" , $pagetitletmp3 );
		$pagetitletmp3=str_replace( "[articletitre]" , "\$article[\$libelle]" , $pagetitletmp3 );
		$pagetitletmp3=str_replace( "[institutionneltitre]" , "\$institutionnel[\$libelle]" , $pagetitletmp3 );
		$pagetitletmp3=str_replace( "[sscatkeywords]" , "\$metasscat" , $pagetitletmp3 );
		$pagetitletmp3=str_replace( "[keydescription]" , "\$keydescription" , $pagetitletmp3 );
		$requetteupdate3="$virgule titleFR='".addslashes($pagetitletmp3)."' ";
		$virgule=" , ";
		}
	if (in_array("EN" , $langues))
		{
		$pagetitletmp4=$modiftitleEN;
		$pagetitletmp4=str_replace( "[boutiquetitre]" , "\${\$title}" , $pagetitletmp4 );
		$pagetitletmp4=str_replace( "[categorietitre]" , "\$pagetitle_catname" , $pagetitletmp4 );
		$pagetitletmp4=str_replace( "[articletitre]" , "\$article[\$libelle]" , $pagetitletmp4 );
		$pagetitletmp4=str_replace( "[institutionneltitre]" , "\$institutionnel[\$libelle]" , $pagetitletmp4 );
		$pagetitletmp4=str_replace( "[sscatkeywords]" , "\$metasscat" , $pagetitletmp4 );
		$pagetitletmp4=str_replace( "[keydescription]" , "\$keydescription" , $pagetitletmp4 );
		$requetteupdate.="$virgule titleEN='".addslashes($pagetitletmp4)."' ";
		$metadescription = $DB_site2->query_first(" select *  from description WHERE scriptname='$pagetitle[scriptname]' ");
		$pagetitletmp5=$modifdescriptionEN;
		$pagetitletmp5=str_replace( "[boutiquetitre]" , "\${\$title}" , $pagetitletmp5 );
		$pagetitletmp5=str_replace( "[categorietitre]" , "\$pagetitle_catname" , $pagetitletmp5 );
		$pagetitletmp5=str_replace( "[articletitre]" , "\$article[\$libelle]" , $pagetitletmp5 );
		$pagetitletmp5=str_replace( "[institutionneltitre]" , "\$institutionnel[\$libelle]" , $pagetitletmp5 );
		$pagetitletmp5=str_replace( "[sscatkeywords]" , "\$metasscat" , $pagetitletmp5 );
		$pagetitletmp5=str_replace( "[keydescription]" , "\$keydescription" , $pagetitletmp5 );
		$requetteupdate2.="$virgule titleEN='".addslashes($pagetitletmp5)."' ";
		$metakeywords = $DB_site2->query_first(" select *  from keywords WHERE scriptname='$pagetitle[scriptname]' ");
		$pagetitletmp6=$modifkeywordsEN;
		$pagetitletmp6=str_replace( "[boutiquetitre]" , "\${\$title}" , $pagetitletmp6 );
		$pagetitletmp6=str_replace( "[categorietitre]" , "\$pagetitle_catname" , $pagetitletmp6 );
		$pagetitletmp6=str_replace( "[articletitre]" , "\$article[\$libelle]" , $pagetitletmp6 );
		$pagetitletmp6=str_replace( "[institutionneltitre]" , "\$institutionnel[\$libelle]" , $pagetitletmp6 );
		$pagetitletmp6=str_replace( "[sscatkeywords]" , "\$metasscat" , $pagetitletmp6 );
		$pagetitletmp6=str_replace( "[keydescription]" , "\$keydescription" , $pagetitletmp6 );
		$requetteupdate3.="$virgule titleEN='".addslashes($pagetitletmp6)."' ";
		$virgule=" , ";
		}
	if (in_array("DE" , $langues))
		{
		$pagetitletmp7=$modiftitleDE;
		$pagetitletmp7=str_replace( "[boutiquetitre]" , "\${\$title}" , $pagetitletmp7 );
		$pagetitletmp7=str_replace( "[categorietitre]" , "\$pagetitle_catname" , $pagetitletmp7 );
		$pagetitletmp7=str_replace( "[articletitre]" , "\$article[\$libelle]" , $pagetitletmp7 );
		$pagetitletmp7=str_replace( "[institutionneltitre]" , "\$institutionnel[\$libelle]" , $pagetitletmp7 );
		$pagetitletmp7=str_replace( "[sscatkeywords]" , "\$metasscat" , $pagetitletmp7 );
		$pagetitletmp7=str_replace( "[keydescription]" , "\$keydescription" , $pagetitletmp7 );
		$requetteupdate.="$virgule titleDE='".addslashes($pagetitletmp7)."' ";
		$metadescription = $DB_site2->query_first(" select *  from description WHERE scriptname='$pagetitle[scriptname]' ");
		$pagetitletmp8=$modifdescriptionDE;
		$pagetitletmp8=str_replace( "[boutiquetitre]" , "\${\$title}" , $pagetitletmp8 );
		$pagetitletmp8=str_replace( "[categorietitre]" , "\$pagetitle_catname" , $pagetitletmp8 );
		$pagetitletmp8=str_replace( "[articletitre]" , "\$article[\$libelle]" , $pagetitletmp8 );
		$pagetitletmp8=str_replace( "[institutionneltitre]" , "\$institutionnel[\$libelle]" , $pagetitletmp8 );
		$pagetitletmp8=str_replace( "[sscatkeywords]" , "\$metasscat" , $pagetitletmp8 );
		$pagetitletmp8=str_replace( "[keydescription]" , "\$keydescription" , $pagetitletmp8 );
		$requetteupdate2.="$virgule titleDE='".addslashes($pagetitletmp8)."' ";
		$metakeywords = $DB_site2->query_first(" select *  from keywords WHERE scriptname='$pagetitle[scriptname]' ");
		$pagetitletmp9=$modifkeywordsDE;
		$pagetitletmp9=str_replace( "[boutiquetitre]" , "\${\$title}" , $pagetitletmp9 );
		$pagetitletmp9=str_replace( "[categorietitre]" , "\$pagetitle_catname" , $pagetitletmp9 );
		$pagetitletmp=str_replace( "[articletitre]" , "\$article[\$libelle]" , $pagetitletmp9 );
		$pagetitletmp9=str_replace( "[institutionneltitre]" , "\$institutionnel[\$libelle]" , $pagetitletmp9 );
		$pagetitletmp9=str_replace( "[sscatkeywords]" , "\$metasscat" , $pagetitletmp9 );
		$pagetitletmp9=str_replace( "[keydescription]" , "\$keydescription" , $pagetitletmp9 );
		$requetteupdate3.="$virgule titleDE='".addslashes($pagetitletmp9)."' ";
		$virgule=" , ";
		}
	if (in_array("SP" , $langues))
		{
		$pagetitletmp10=$modiftitleSP;
		$pagetitletmp10=str_replace( "[boutiquetitre]" , "\${\$title}" , $pagetitletmp10 );
		$pagetitletmp10=str_replace( "[categorietitre]" , "\$pagetitle_catname" , $pagetitletmp10 );
		$pagetitletmp10=str_replace( "[articletitre]" , "\$article[\$libelle]" , $pagetitletmp10 );
		$pagetitletmp10=str_replace( "[institutionneltitre]" , "\$institutionnel[\$libelle]" , $pagetitletmp10 );
		$pagetitletmp10=str_replace( "[sscatkeywords]" , "\$metasscat" , $pagetitletmp10 );
		$pagetitletmp10=str_replace( "[keydescription]" , "\$keydescription" , $pagetitletmp10 );
		$requetteupdate.="$virgule titleSP='".addslashes($pagetitletmp10)."' ";
		$metadescription = $DB_site2->query_first(" select *  from description WHERE scriptname='$pagetitle[scriptname]' ");
		$pagetitletmp11=$modifdescriptionSP;
		$pagetitletmp11=str_replace( "[boutiquetitre]" , "\${\$title}" , $pagetitletmp11 );
		$pagetitletmp11=str_replace( "[categorietitre]" , "\$pagetitle_catname" , $pagetitletmp11 );
		$pagetitletmp11=str_replace( "[articletitre]" , "\$article[\$libelle]" , $pagetitletmp11 );
		$pagetitletmp11=str_replace( "[institutionneltitre]" , "\$institutionnel[\$libelle]" , $pagetitletmp11 );
		$pagetitletmp11=str_replace( "[sscatkeywords]" , "\$metasscat" , $pagetitletmp11 );
		$pagetitletmp11=str_replace( "[keydescription]" , "\$keydescription" , $pagetitletmp11 );
		$requetteupdate2.="$virgule titleSP='".addslashes($pagetitletmp11)."' ";
		$metakeywords = $DB_site2->query_first(" select *  from keywords WHERE scriptname='$pagetitle[scriptname]' ");
		$pagetitletmp12=$modifkeywordsSP;
		$pagetitletmp12=str_replace( "[boutiquetitre]" , "\${\$title}" , $pagetitletmp12 );
		$pagetitletmp12=str_replace( "[categorietitre]" , "\$pagetitle_catname" , $pagetitletmp12 );
		$pagetitletmp12=str_replace( "[articletitre]" , "\$article[\$libelle]" , $pagetitletmp12 );
		$pagetitletmp12=str_replace( "[institutionneltitre]" , "\$institutionnel[\$libelle]" , $pagetitletmp12 );
		$pagetitletmp12=str_replace( "[sscatkeywords]" , "\$metasscat" , $pagetitletmp12 );
		$pagetitletmp12=str_replace( "[keydescription]" , "\$keydescription" , $pagetitletmp12 );
		$requetteupdate3.="$virgule titleSP='".addslashes($pagetitletmp12)."' ";
		$virgule=" , ";
		}
	if (in_array("IT" , $langues))
		{
		$pagetitletmp13=$modiftitleIT;
		$pagetitletmp13=str_replace( "[boutiquetitre]" , "\${\$title}" , $pagetitletmp13 );
		$pagetitletmp13=str_replace( "[categorietitre]" , "\$pagetitle_catname" , $pagetitletmp13 );
		$pagetitletmp13=str_replace( "[articletitre]" , "\$article[\$libelle]" , $pagetitletmp13 );
		$pagetitletmp13=str_replace( "[institutionneltitre]" , "\$institutionnel[\$libelle]" , $pagetitletmp13 );
		$pagetitletmp13=str_replace( "[sscatkeywords]" , "\$metasscat" , $pagetitletmp13 );
		$pagetitletmp13=str_replace( "[keydescription]" , "\$keydescription" , $pagetitletmp13 );
		$requetteupdate.="$virgule titleIT='".addslashes($pagetitletmp13)."' ";
		$metadescription = $DB_site2->query_first(" select *  from description WHERE scriptname='$pagetitle[scriptname]' ");
		$pagetitletmp14=$modifdescriptionIT;
		$pagetitletmp14=str_replace( "[boutiquetitre]" , "\${\$title}" , $pagetitletmp14 );
		$pagetitletmp14=str_replace( "[categorietitre]" , "\$pagetitle_catname" , $pagetitletmp14 );
		$pagetitletmp14=str_replace( "[articletitre]" , "\$article[\$libelle]" , $pagetitletmp14 );
		$pagetitletmp14=str_replace( "[institutionneltitre]" , "\$institutionnel[\$libelle]" , $pagetitletmp14 );
		$pagetitletmp14=str_replace( "[sscatkeywords]" , "\$metasscat" , $pagetitletmp14 );
		$pagetitletmp14=str_replace( "[keydescription]" , "\$keydescription" , $pagetitletmp14 );
		$requetteupdate2.="$virgule titleIT='".addslashes($pagetitletmp14)."' ";
		$metakeywords = $DB_site2->query_first(" select *  from keywords WHERE scriptname='$pagetitle[scriptname]' ");
		$pagetitletmp15=$modifkeywordsIT;
		$pagetitletmp15=str_replace( "[boutiquetitre]" , "\${\$title}" , $pagetitletmp15 );
		$pagetitletmp15=str_replace( "[categorietitre]" , "\$pagetitle_catname" , $pagetitletmp15 );
		$pagetitletmp15=str_replace( "[articletitre]" , "\$article[\$libelle]" , $pagetitletmp15 );
		$pagetitletmp15=str_replace( "[institutionneltitre]" , "\$institutionnel[\$libelle]" , $pagetitletmp15 );
		$pagetitletmp15=str_replace( "[sscatkeywords]" , "\$metasscat" , $pagetitletmp15 );
		$pagetitletmp15=str_replace( "[keydescription]" , "\$keydescription" , $pagetitletmp15 );
		$requetteupdate3.="$virgule titleIT='".addslashes($pagetitletmp15)."' ";
		$virgule=" , ";
		}
	$DB_site2->query("UPDATE pagetitle SET $requetteupdate WHERE pagetitleid = '$pagetitleid'");
	$metadescription=$DB_site2->query_first("select * from description WHERE scriptname = '$pagetitle[scriptname]'");
	$DB_site2->query("UPDATE description SET $requetteupdate2 WHERE scriptname = '$metadescription[scriptname]'");
	$metakeywords=$DB_site2->query_first("select *  from keywords WHERE scriptname = '$pagetitle[scriptname]'");
	$DB_site2->query("UPDATE keywords SET $requetteupdate3 WHERE scriptname = '$metakeywords[scriptname]'");
	$txt = "<font class=\"cost\">Les modifications ont bien 굩 prises en compte.</font>";
	$action = "" ;
	}
	
if (!isset($action) or $action == "")
	{
	echo" <h3>Mots cl과/h3>
	<div align=\"left\" style=\"margin:10px;font-size:11px;\">
	Ci-dessous vous retrouvez la liste des pages visibles sur votre boutique.<br>
	Pour chacune d'entre elles, il faut dꧩnir 3 informations* : 
	<ul>
	<li><strong>Le titre de la fen뵲e</strong><br>
	Indique le titre du document. Il est gꯩralement affich顤ans la barre de titre de la fen뵲e du navigateur pour permettre l'identification du document. Il doit 뵲e suffisamment explicite pour dꤲire le document. Les tags de formatages sont interdits et ignor곮</li>
	<br>
	<li><strong>La META description</strong><br>
	Indique aux moteurs de recherche la description de votre page.</li>
	<br>
	<li><strong>Les META keywords</strong><br>
	Indique aux moteurs de recherche les mots cl고de recherche du document.</li>
	</ul>
	*<font size=\"-2\">Il faut remplir les informations dans toutes les langues disponibles sur votre boutique.</font></div>" ;
	
	echo "$txt<br><br><table class=\"table_caracts\">" ;
	while (list($key, $val) = each($script)) 
		{
		$rowalt = "td_promos_".getrowbg();
		$pagetitle=$DB_site2->query_first("select pagetitleid from pagetitle WHERE scriptname = '{$script[$key][script]}'");
		echo "<tr>
				<td align=\"left\" width=\"70%\" class=\"$rowalt\">{$script[$key][nom]}</td>
				<td align=\"center\" class=\"$rowalt\"><a href=pagetitle.php?action=modify&pagetitleid=$pagetitle[0]>Modifier</a></td>
			</tr>" ;
		}
	echo "</table>" ;
	}

if ($action == "modify") 
	{
	$pagetitle=$DB_site2->query_first("select * from pagetitle WHERE pagetitleid = '$pagetitleid'");
	while (list($key, $val) = each($script)) 
		{
		if ($pagetitle[scriptname] == $script[$key][script])
			{
			echo "<h3>Modification des mots cl고de {$script[$key][nom]}</h3>" ;
			}
		}
	echo "<br>
		<table width=\"100%\" align=\"center\">
			<form action=pagetitle.php method=\"post\">
				<input type=\"hidden\" name=\"action\" value=\"update\">
				<input type=\"hidden\" name=\"pagetitleid\" value=\"$pagetitleid\">";
	if (in_array("FR", $langues))
		{
		$pagetitletmp1=$pagetitle[titleFR];
		$pagetitletmp1=str_replace( "\${\$title}" , "[boutiquetitre]" , $pagetitletmp1 );
		$pagetitletmp1=str_replace( "\$pagetitle_catname" , "[categorietitre]" , $pagetitletmp1 );
		$pagetitletmp1=str_replace( "\$article[\$libelle]" , "[articletitre]" , $pagetitletmp1 );
		$pagetitletmp1=str_replace( "\$institutionnel[\$libelle]" , "[institutionneltitre]" , $pagetitletmp1 );
		$pagetitletmp1=str_replace( "\$metasscat" , "[sscatkeywords]" , $pagetitletmp1 );
		$pagetitletmp1=str_replace( "\$keydescription" , "[keydescription]" , $pagetitletmp1 );
		$pagetitletmp1=stripslashes($pagetitletmp1);
		$metadescription=$DB_site2->query_first("select *  from description WHERE scriptname = '$pagetitle[scriptname]'");
		$pagetitletmp2=$metadescription[titleFR];
		$pagetitletmp2=str_replace( "\${\$title}" , "[boutiquetitre]" , $pagetitletmp2 );
		$pagetitletmp2=str_replace( "\$pagetitle_catname" , "[categorietitre]" , $pagetitletmp2 );
		$pagetitletmp2=str_replace( "\$article[\$libelle]" , "[articletitre]" , $pagetitletmp2 );
		$pagetitletmp2=str_replace( "\$institutionnel[\$libelle]" , "[institutionneltitre]" , $pagetitletmp2 );
		$pagetitletmp2=str_replace( "\$metasscat" , "[sscatkeywords]" , $pagetitletmp2 );
		$pagetitletmp2=str_replace( "\$keydescription" , "[keydescription]" , $pagetitletmp2 );
		$pagetitletmp2=stripslashes($pagetitletmp2);
		$metakeywords = $DB_site2->query_first(" select *  from keywords WHERE scriptname='$pagetitle[scriptname]' ");
		$pagetitletmp3=$metakeywords[titleFR];
		$pagetitletmp3=str_replace( "\${\$title}" , "[boutiquetitre]" , $pagetitletmp3 );
		$pagetitletmp3=str_replace( "\$pagetitle_catname" , "[categorietitre]" , $pagetitletmp3 );
		$pagetitletmp3=str_replace( "\$article[\$libelle]" , "[articletitre]" , $pagetitletmp3 );
		$pagetitletmp3=str_replace( "\$institutionnel[\$libelle]" , "[institutionneltitre]" , $pagetitletmp3 );
		$pagetitletmp3=str_replace( "\$metasscat" , "[sscatkeywords]" , $pagetitletmp3 );
		$pagetitletmp3=str_replace( "\$keydescription" , "[keydescription]" , $pagetitletmp3 );
		$pagetitletmp3=stripslashes($pagetitletmp3);
		echo "<tr><td align=\"left\" width=\"75\" valign=\"top\"><strong>Fran袩s : </strong><br><br></td>
				<td align=\"left\"><a href=\"Javascript:afficher('fr');\">Afficher</a>&nbsp;&nbsp;&nbsp;<a href=\"Javascript:cacher('fr');\">Cacher</a><bR><bR>
				<div id=\"fr\">
					Titre de la fen뵲e (environ 128 caract鳥s max.)<bR>
					<textarea name=\"modiftitleFR\"  cols=\"60\" rows=\"3\" style=\"font-family:Verdana;font-size:11px;border-color:#EEEEEE;border-width:1px;border-style:solid;color:#5F718A;\">$pagetitletmp1</textarea><bR><bR>
					META Description (environ 1024 caract鳥s max.)<bR>
					<textarea name=\"modifdescriptionFR\" cols=\"60\" rows=\"5\" style=\"font-family:Verdana;font-size:11px;border-color:#EEEEEE;border-width:1px;border-style:solid;color:#5F718A;\">$pagetitletmp2</textarea><bR><bR>
					META Keywords (environ 1024 caract鳥s max.)<bR>
					<textarea name=\"modifkeywordsFR\" cols=\"60\" rows=\"5\" style=\"font-family:Verdana;font-size:11px;border-color:#EEEEEE;border-width:1px;border-style:solid;color:#5F718A;\">$pagetitletmp3</textarea></div>
				</td>" ;
		}
	if (in_array("EN", $langues))
		{
		$pagetitletmp1=$pagetitle[titleEN];
		$pagetitletmp1=str_replace( "\${\$title}" , "[boutiquetitre]" , $pagetitletmp1 );
		$pagetitletmp1=str_replace( "\$pagetitle_catname" , "[categorietitre]" , $pagetitletmp1 );
		$pagetitletmp1=str_replace( "\$article[\$libelle]" , "[articletitre]" , $pagetitletmp1 );
		$pagetitletmp1=str_replace( "\$institutionnel[\$libelle]" , "[institutionneltitre]" , $pagetitletmp1 );
		$pagetitletmp1=str_replace( "\$metasscat" , "[sscatkeywords]" , $pagetitletmp1 );
		$pagetitletmp1=str_replace( "\$keydescription" , "[keydescription]" , $pagetitletmp1 );
		$pagetitletmp1=stripslashes($pagetitletmp1);
		$metadescription = $DB_site2->query_first(" select *  from description WHERE scriptname='$pagetitle[scriptname]' ");
		$pagetitletmp2=$metadescription[titleEN];
		$pagetitletmp2=str_replace( "\${\$title}" , "[boutiquetitre]" , $pagetitletmp2 );
		$pagetitletmp2=str_replace( "\$pagetitle_catname" , "[categorietitre]" , $pagetitletmp2 );
		$pagetitletmp2=str_replace( "\$article[\$libelle]" , "[articletitre]" , $pagetitletmp2 );
		$pagetitletmp2=str_replace( "\$institutionnel[\$libelle]" , "[institutionneltitre]" , $pagetitletmp2 );
		$pagetitletmp2=str_replace( "\$metasscat" , "[sscatkeywords]" , $pagetitletmp2 );
		$pagetitletmp2=str_replace( "\$keydescription" , "[keydescription]" , $pagetitletmp2 );
		$pagetitletmp2=stripslashes($pagetitletmp2);
		$metakeywords = $DB_site2->query_first(" select *  from keywords WHERE scriptname='$pagetitle[scriptname]' ");
		$pagetitletmp3=$metakeywords[titleEN];
		$pagetitletmp3=str_replace( "\${\$title}" , "[boutiquetitre]" , $pagetitletmp3 );
		$pagetitletmp3=str_replace( "\$pagetitle_catname" , "[categorietitre]" , $pagetitletmp3 );
		$pagetitletmp3=str_replace( "\$article[\$libelle]" , "[articletitre]" , $pagetitletmp3 );
		$pagetitletmp3=str_replace( "\$institutionnel[\$libelle]" , "[institutionneltitre]" , $pagetitletmp3 );
		$pagetitletmp3=str_replace( "\$metasscat" , "[sscatkeywords]" , $pagetitletmp3 );
		$pagetitletmp3=str_replace( "\$keydescription" , "[keydescription]" , $pagetitletmp3 );
		$pagetitletmp3=stripslashes($pagetitletmp3);
		echo "<tr><td align=\"left\" width=\"75\" valign=\"top\"><strong>Anglais : </strong><br><br></td>
				<td align=\"left\"><a href=\"Javascript:afficher('en');\">Afficher</a>&nbsp;&nbsp;&nbsp;<a href=\"Javascript:cacher('en');\">Cacher</a><bR><bR>
				<div id=\"en\">
					Titre de la fen뵲e (environ 128 caract鳥s max.)<bR>
					<textarea name=\"modiftitleEN\"  cols=\"60\" rows=\"3\" style=\"font-family:Verdana;font-size:11px;border-color:#EEEEEE;border-width:1px;border-style:solid;color:#5F718A;\">$pagetitletmp1</textarea><bR><bR>
					META Description (environ 1024 caract鳥s max.)<bR>
					<textarea name=\"modifdescriptionEN\" cols=\"60\" rows=\"5\" style=\"font-family:Verdana;font-size:11px;border-color:#EEEEEE;border-width:1px;border-style:solid;color:#5F718A;\">$pagetitletmp2</textarea><bR><bR>
					META Keywords (environ 1024 caract鳥s max.)<bR>
					<textarea name=\"modifkeywordsEN\" cols=\"60\" rows=\"5\" style=\"font-family:Verdana;font-size:11px;border-color:#EEEEEE;border-width:1px;border-style:solid;color:#5F718A;\">$pagetitletmp3</textarea></div>
				</td>
				<script>cacher('en');</script>" ;
		}
	if (in_array("DE", $langues))
		{
		$pagetitletmp1=$pagetitle[titleDE];
		$pagetitletmp1=str_replace( "\${\$title}" , "[boutiquetitre]" , $pagetitletmp1 );
		$pagetitletmp1=str_replace( "\$pagetitle_catname" , "[categorietitre]" , $pagetitletmp1 );
		$pagetitletmp1=str_replace( "\$article[\$libelle]" , "[articletitre]" , $pagetitletmp1 );
		$pagetitletmp1=str_replace( "\$institutionnel[\$libelle]" , "[institutionneltitre]" , $pagetitletmp1 );
		$pagetitletmp1=str_replace( "\$metasscat" , "[sscatkeywords]" , $pagetitletmp1 );
		$pagetitletmp1=str_replace( "\$keydescription" , "[keydescription]" , $pagetitletmp1 );
		$pagetitletmp1=stripslashes($pagetitletmp1);
		$metadescription = $DB_site2->query_first(" select *  from description WHERE scriptname='$pagetitle[scriptname]' ");
		$pagetitletmp2=$metadescription[titleDE];
		$pagetitletmp2=str_replace( "\${\$title}" , "[boutiquetitre]" , $pagetitletmp2 );
		$pagetitletmp2=str_replace( "\$pagetitle_catname" , "[categorietitre]" , $pagetitletmp2 );
		$pagetitletmp2=str_replace( "\$article[\$libelle]" , "[articletitre]" , $pagetitletmp2 );
		$pagetitletmp2=str_replace( "\$institutionnel[\$libelle]" , "[institutionneltitre]" , $pagetitletmp2 );
		$pagetitletmp2=str_replace( "\$metasscat" , "[sscatkeywords]" , $pagetitletmp2 );
		$pagetitletmp2=str_replace( "\$keydescription" , "[keydescription]" , $pagetitletmp2 );
		$pagetitletmp2=stripslashes($pagetitletmp2);
		$metakeywords = $DB_site2->query_first(" select *  from keywords WHERE scriptname='$pagetitle[scriptname]' ");
		$pagetitletmp3=$metakeywords[titleDE];
		$pagetitletmp3=str_replace( "\${\$title}" , "[boutiquetitre]" , $pagetitletmp3 );
		$pagetitletmp3=str_replace( "\$pagetitle_catname" , "[categorietitre]" , $pagetitletmp3 );
		$pagetitletmp3=str_replace( "\$article[\$libelle]" , "[articletitre]" , $pagetitletmp3 );
		$pagetitletmp3=str_replace( "\$institutionnel[\$libelle]" , "[institutionneltitre]" , $pagetitletmp3 );
		$pagetitletmp3=str_replace( "\$metasscat" , "[sscatkeywords]" , $pagetitletmp3 );
		$pagetitletmp3=str_replace( "\$keydescription" , "[keydescription]" , $pagetitletmp3 );
		$pagetitletmp3=stripslashes($pagetitletmp3);
		echo "<tr><td align=\"left\" width=\"75\" valign=\"top\"><strong>Allemand : </strong><br><br></td>
				<td align=\"left\"><a href=\"Javascript:afficher('de');\">Afficher</a>&nbsp;&nbsp;&nbsp;<a href=\"Javascript:cacher('de');\">Cacher</a><bR><bR>
				<div id=\"de\">
					Titre de la fen뵲e (environ 128 caract鳥s max.)<bR>
					<textarea name=\"modiftitleDE\"  cols=\"60\" rows=\"3\" style=\"font-family:Verdana;font-size:11px;border-color:#EEEEEE;border-width:1px;border-style:solid;color:#5F718A;\">$pagetitletmp1</textarea><bR><bR>
					META Description (environ 1024 caract鳥s max.)<bR>
					<textarea name=\"modifdescriptionDE\" cols=\"60\" rows=\"5\" style=\"font-family:Verdana;font-size:11px;border-color:#EEEEEE;border-width:1px;border-style:solid;color:#5F718A;\">$pagetitletmp2</textarea><bR><bR>
					META Keywords (environ 1024 caract鳥s max.)<bR>
					<textarea name=\"modifkeywordsDE\" cols=\"60\" rows=\"5\" style=\"font-family:Verdana;font-size:11px;border-color:#EEEEEE;border-width:1px;border-style:solid;color:#5F718A;\">$pagetitletmp3</textarea></div>
				</td>
				<script>cacher('de');</script>" ;
		}
	if (in_array("SP", $langues))
		{
		$pagetitletmp1=$pagetitle[titleSP];
		$pagetitletmp1=str_replace( "\${\$title}" , "[boutiquetitre]" , $pagetitletmp1 );
		$pagetitletmp1=str_replace( "\$pagetitle_catname" , "[categorietitre]" , $pagetitletmp1 );
		$pagetitletmp1=str_replace( "\$article[\$libelle]" , "[articletitre]" , $pagetitletmp1 );
		$pagetitletmp1=str_replace( "\$institutionnel[\$libelle]" , "[institutionneltitre]" , $pagetitletmp1 );
		$pagetitletmp1=str_replace( "\$metasscat" , "[sscatkeywords]" , $pagetitletmp1 );
		$pagetitletmp1=str_replace( "\$keydescription" , "[keydescription]" , $pagetitletmp1 );
		$pagetitletmp1=stripslashes($pagetitletmp1);
		$metadescription = $DB_site2->query_first(" select *  from description WHERE scriptname='$pagetitle[scriptname]' ");
		$pagetitletmp2=$metadescription[titleSP];
		$pagetitletmp2=str_replace( "\${\$title}" , "[boutiquetitre]" , $pagetitletmp2 );
		$pagetitletmp2=str_replace( "\$pagetitle_catname" , "[categorietitre]" , $pagetitletmp2 );
		$pagetitletmp2=str_replace( "\$article[\$libelle]" , "[articletitre]" , $pagetitletmp2 );
		$pagetitletmp2=str_replace( "\$institutionnel[\$libelle]" , "[institutionneltitre]" , $pagetitletmp2 );
		$pagetitletmp2=str_replace( "\$metasscat" , "[sscatkeywords]" , $pagetitletmp2 );
		$pagetitletmp2=str_replace( "\$keydescription" , "[keydescription]" , $pagetitletmp2 );
		$pagetitletmp2=stripslashes($pagetitletmp2);
		$metakeywords = $DB_site2->query_first(" select *  from keywords WHERE scriptname='$pagetitle[scriptname]' ");
		$pagetitletmp3=$metakeywords[titleSP];
		$pagetitletmp3=str_replace( "\${\$title}" , "[boutiquetitre]" , $pagetitletmp3 );
		$pagetitletmp3=str_replace( "\$pagetitle_catname" , "[categorietitre]" , $pagetitletmp3 );
		$pagetitletmp3=str_replace( "\$article[\$libelle]" , "[articletitre]" , $pagetitletmp3 );
		$pagetitletmp3=str_replace( "\$institutionnel[\$libelle]" , "[institutionneltitre]" , $pagetitletmp3 );
		$pagetitletmp3=str_replace( "\$metasscat" , "[sscatkeywords]" , $pagetitletmp3 );
		$pagetitletmp3=str_replace( "\$keydescription" , "[keydescription]" , $pagetitletmp3 );
		$pagetitletmp3=stripslashes($pagetitletmp3);
		echo "<tr><td align=\"left\" width=\"75\" valign=\"top\"><strong>Espagnol : </strong><br><br></td>
				<td align=\"left\"><a href=\"Javascript:afficher('sp');\">Afficher</a>&nbsp;&nbsp;&nbsp;<a href=\"Javascript:cacher('sp');\">Cacher</a><bR><bR>
				<div id=\"sp\">
					Titre de la fen뵲e (environ 128 caract鳥s max.)<bR>
					<textarea name=\"modiftitleSP\"  cols=\"60\" rows=\"3\" style=\"font-family:Verdana;font-size:11px;border-color:#EEEEEE;border-width:1px;border-style:solid;color:#5F718A;\">$pagetitletmp1</textarea><bR><bR>
					META Description (environ 1024 caract鳥s max.)<bR>
					<textarea name=\"modifdescriptionSP\" cols=\"60\" rows=\"5\" style=\"font-family:Verdana;font-size:11px;border-color:#EEEEEE;border-width:1px;border-style:solid;color:#5F718A;\">$pagetitletmp2</textarea><bR><bR>
					META Keywords (environ 1024 caract鳥s max.)<bR>
					<textarea name=\"modifkeywordsSP\" cols=\"60\" rows=\"5\" style=\"font-family:Verdana;font-size:11px;border-color:#EEEEEE;border-width:1px;border-style:solid;color:#5F718A;\">$pagetitletmp3</textarea></div>
				</td>
				<script>cacher('sp');</script>" ;
		}
	if (in_array("IT", $langues))
		{
		$pagetitletmp1=$pagetitle[titleIT];
		$pagetitletmp1=str_replace( "\${\$title}" , "[boutiquetitre]" , $pagetitletmp1 );
		$pagetitletmp1=str_replace( "\$pagetitle_catname" , "[categorietitre]" , $pagetitletmp1 );
		$pagetitletmp1=str_replace( "\$article[\$libelle]" , "[articletitre]" , $pagetitletmp1 );
		$pagetitletmp1=str_replace( "\$institutionnel[\$libelle]" , "[institutionneltitre]" , $pagetitletmp1 );
		$pagetitletmp1=str_replace( "\$metasscat" , "[sscatkeywords]" , $pagetitletmp1 );
		$pagetitletmp1=str_replace( "\$keydescription" , "[keydescription]" , $pagetitletmp1 );
		$pagetitletmp1=stripslashes($pagetitletmp1);
		$metadescription = $DB_site2->query_first(" select *  from description WHERE scriptname='$pagetitle[scriptname]' ");
		$pagetitletmp2=$metadescription[titleIT];
		$pagetitletmp2=str_replace( "\${\$title}" , "[boutiquetitre]" , $pagetitletmp2 );
		$pagetitletmp2=str_replace( "\$pagetitle_catname" , "[categorietitre]" , $pagetitletmp2 );
		$pagetitletmp2=str_replace( "\$article[\$libelle]" , "[articletitre]" , $pagetitletmp2 );
		$pagetitletmp2=str_replace( "\$institutionnel[\$libelle]" , "[institutionneltitre]" , $pagetitletmp2 );
		$pagetitletmp2=str_replace( "\$metasscat" , "[sscatkeywords]" , $pagetitletmp2 );
		$pagetitletmp2=str_replace( "\$keydescription" , "[keydescription]" , $pagetitletmp2 );
		$pagetitletmp2=stripslashes($pagetitletmp2);
		$metakeywords = $DB_site2->query_first(" select *  from keywords WHERE scriptname='$pagetitle[scriptname]' ");
		$pagetitletmp3=$metakeywords[titleIT];
		$pagetitletmp3=str_replace( "\${\$title}" , "[boutiquetitre]" , $pagetitletmp3 );
		$pagetitletmp3=str_replace( "\$pagetitle_catname" , "[categorietitre]" , $pagetitletmp3 );
		$pagetitletmp3=str_replace( "\$article[\$libelle]" , "[articletitre]" , $pagetitletmp3 );
		$pagetitletmp3=str_replace( "\$institutionnel[\$libelle]" , "[institutionneltitre]" , $pagetitletmp3 );
		$pagetitletmp3=str_replace( "\$metasscat" , "[sscatkeywords]" , $pagetitletmp3 );
		$pagetitletmp3=str_replace( "\$keydescription" , "[keydescription]" , $pagetitletmp3 );
		$pagetitletmp3=stripslashes($pagetitletmp3);
		echo "<tr><td align=\"left\" width=\"75\" valign=\"top\"><strong>Italien : </strong><br><br></td>
				<td align=\"left\"><a href=\"Javascript:afficher('it');\">Afficher</a>&nbsp;&nbsp;&nbsp;<a href=\"Javascript:cacher('it');\">Cacher</a><bR><bR>
				<div id=\"it\">
					Titre de la fen뵲e (environ 128 caract鳥s max.)<bR>
					<textarea name=\"modiftitleIT\"  cols=\"60\" rows=\"3\" style=\"font-family:Verdana;font-size:11px;border-color:#EEEEEE;border-width:1px;border-style:solid;color:#5F718A;\">$pagetitletmp1</textarea><bR><bR>
					META Description (environ 1024 caract鳥s max.)<bR>
					<textarea name=\"modifdescriptionIT\" cols=\"60\" rows=\"5\" style=\"font-family:Verdana;font-size:11px;border-color:#EEEEEE;border-width:1px;border-style:solid;color:#5F718A;\">$pagetitletmp2</textarea><bR><bR>
					META Keywords (environ 1024 caract鳥s max.)<bR>
					<textarea name=\"modifkeywordsIT\" cols=\"60\" rows=\"5\" style=\"font-family:Verdana;font-size:11px;border-color:#EEEEEE;border-width:1px;border-style:solid;color:#5F718A;\">$pagetitletmp3</textarea></div>
				</td>
				<script>cacher('it');</script>" ;
		}
	echo "<tr>
			<td colspan=\"2\" align=\"center\"><input type=\"submit\" value=\"modifier\"></td>
		</tr>
	</form>
	</table><br><br>
	<table class=\"table_caracts\"><tr><td align=\"left\">
	<center><font class=\"cost\">Vous pouvez utiliser les outils suivants</font></center><br>
	<strong>[boutiquetitre]</strong> = nom de la boutique<br>
	<strong>[categorietitre]</strong> = nom du rayon en cours (page article ou rayon)<br>
	<strong>[articletitre]</strong> = nom de l'article en cours (page article)<br>
	<strong>[institutionneltitre]</strong> = nom d'une page institutionnelle (page institutionnelle)<br>
	<strong>[sscatkeywords]</strong> = nom des sous-rayons (page rayon)<br>
	<strong>[keydescription]</strong> = mots clef d'un article (page article)
	</td></tr></table>
	<br><br><a href=pagetitle.php>Retour</a>";
	}

/*
if (isset($action) and $action != "") 
	{
	$pagetitle=$DB_site2->query_first("select * from pagetitle WHERE pagetitleid = '$pagetitleid'");
	reset($script);
	while (list($key, $val) = each($script)) 
		{
		if ($pagetitle[scriptname] == $script[$key][script]) 
			{
			$scriptname = str_replace($pagetitle[scriptname], $script[$key][nom], $pagetitle[scriptname]);
			}
		}
		
	echo "<b>$scriptname</b><br><br>";
	
	if ($action == "update") 
		{
		if (in_array("FR" , $langues))
			{
			$pagetitletmp=$modiftitleFR;
			$pagetitletmp=str_replace( "[boutiquetitre]" , "\${\$title}" , $pagetitletmp );
			$pagetitletmp=str_replace( "[categorietitre]" , "\$pagetitle_catname" , $pagetitletmp );
			$pagetitletmp=str_replace( "[articletitre]" , "\$article[\$libelle]" , $pagetitletmp );
			$pagetitletmp=str_replace( "[institutionneltitre]" , "\$institutionnel[\$libelle]" , $pagetitletmp );
			$pagetitletmp=str_replace( "[sscatkeywords]" , "\$metasscat" , $pagetitletmp );
			$pagetitletmp=str_replace( "[keydescription]" , "\$keydescription" , $pagetitletmp );
			$requetteupdate="$virgule titleFR='".addslashes($pagetitletmp)."' ";
			$metadescription = $DB_site2->query_first(" select *  from description WHERE scriptname='$pagetitle[scriptname]' ");
			$pagetitletmp=$modifdescriptionFR;
			$pagetitletmp=str_replace( "[boutiquetitre]" , "\${\$title}" , $pagetitletmp );
			$pagetitletmp=str_replace( "[categorietitre]" , "\$pagetitle_catname" , $pagetitletmp );
			$pagetitletmp=str_replace( "[articletitre]" , "\$article[\$libelle]" , $pagetitletmp );
			$pagetitletmp=str_replace( "[institutionneltitre]" , "\$institutionnel[\$libelle]" , $pagetitletmp );
			$pagetitletmp=str_replace( "[sscatkeywords]" , "\$metasscat" , $pagetitletmp );
			$pagetitletmp=str_replace( "[keydescription]" , "\$keydescription" , $pagetitletmp );
			$requetteupdate2="$virgule titleFR='".addslashes($pagetitletmp)."' ";
			$metakeywords = $DB_site2->query_first(" select *  from keywords WHERE scriptname='$pagetitle[scriptname]' ");
			$pagetitletmp=$modifkeywordsFR;
			$pagetitletmp=str_replace( "[boutiquetitre]" , "\${\$title}" , $pagetitletmp );
			$pagetitletmp=str_replace( "[categorietitre]" , "\$pagetitle_catname" , $pagetitletmp );
			$pagetitletmp=str_replace( "[articletitre]" , "\$article[\$libelle]" , $pagetitletmp );
			$pagetitletmp=str_replace( "[institutionneltitre]" , "\$institutionnel[\$libelle]" , $pagetitletmp );
			$pagetitletmp=str_replace( "[sscatkeywords]" , "\$metasscat" , $pagetitletmp );
			$pagetitletmp=str_replace( "[keydescription]" , "\$keydescription" , $pagetitletmp );
			$requetteupdate3="$virgule titleFR='".addslashes($pagetitletmp)."' ";
			$virgule=" , ";
			}
		if (in_array("EN" , $langues))
			{
			$pagetitletmp=$modiftitleEN;
			$pagetitletmp=str_replace( "[boutiquetitre]" , "\${\$title}" , $pagetitletmp );
			$pagetitletmp=str_replace( "[categorietitre]" , "\$pagetitle_catname" , $pagetitletmp );
			$pagetitletmp=str_replace( "[articletitre]" , "\$article[\$libelle]" , $pagetitletmp );
			$pagetitletmp=str_replace( "[institutionneltitre]" , "\$institutionnel[\$libelle]" , $pagetitletmp );
			$pagetitletmp=str_replace( "[sscatkeywords]" , "\$metasscat" , $pagetitletmp );
			$pagetitletmp=str_replace( "[keydescription]" , "\$keydescription" , $pagetitletmp );
			$requetteupdate.="$virgule titleEN='".addslashes($pagetitletmp)."' ";
			$metadescription = $DB_site2->query_first(" select *  from description WHERE scriptname='$pagetitle[scriptname]' ");
			$pagetitletmp=$modifdescriptionEN;
			$pagetitletmp=str_replace( "[boutiquetitre]" , "\${\$title}" , $pagetitletmp );
			$pagetitletmp=str_replace( "[categorietitre]" , "\$pagetitle_catname" , $pagetitletmp );
			$pagetitletmp=str_replace( "[articletitre]" , "\$article[\$libelle]" , $pagetitletmp );
			$pagetitletmp=str_replace( "[institutionneltitre]" , "\$institutionnel[\$libelle]" , $pagetitletmp );
			$pagetitletmp=str_replace( "[sscatkeywords]" , "\$metasscat" , $pagetitletmp );
			$pagetitletmp=str_replace( "[keydescription]" , "\$keydescription" , $pagetitletmp );
			$requetteupdate2.="$virgule titleEN='".addslashes($pagetitletmp)."' ";
			$metakeywords = $DB_site2->query_first(" select *  from keywords WHERE scriptname='$pagetitle[scriptname]' ");
			$pagetitletmp=$modifkeywordsEN;
			$pagetitletmp=str_replace( "[boutiquetitre]" , "\${\$title}" , $pagetitletmp );
			$pagetitletmp=str_replace( "[categorietitre]" , "\$pagetitle_catname" , $pagetitletmp );
			$pagetitletmp=str_replace( "[articletitre]" , "\$article[\$libelle]" , $pagetitletmp );
			$pagetitletmp=str_replace( "[institutionneltitre]" , "\$institutionnel[\$libelle]" , $pagetitletmp );
			$pagetitletmp=str_replace( "[sscatkeywords]" , "\$metasscat" , $pagetitletmp );
			$pagetitletmp=str_replace( "[keydescription]" , "\$keydescription" , $pagetitletmp );
			$requetteupdate3.="$virgule titleEN='".addslashes($pagetitletmp)."' ";
			$virgule=" , ";
			}
		if (in_array("DE" , $langues))
			{
			$pagetitletmp=$modiftitleDE;
			$pagetitletmp=str_replace( "[boutiquetitre]" , "\${\$title}" , $pagetitletmp );
			$pagetitletmp=str_replace( "[categorietitre]" , "\$pagetitle_catname" , $pagetitletmp );
			$pagetitletmp=str_replace( "[articletitre]" , "\$article[\$libelle]" , $pagetitletmp );
			$pagetitletmp=str_replace( "[institutionneltitre]" , "\$institutionnel[\$libelle]" , $pagetitletmp );
			$pagetitletmp=str_replace( "[sscatkeywords]" , "\$metasscat" , $pagetitletmp );
			$pagetitletmp=str_replace( "[keydescription]" , "\$keydescription" , $pagetitletmp );
			$requetteupdate.="$virgule titleDE='".addslashes($pagetitletmp)."' ";
			$metadescription = $DB_site2->query_first(" select *  from description WHERE scriptname='$pagetitle[scriptname]' ");
			$pagetitletmp=$modifdescriptionDE;
			$pagetitletmp=str_replace( "[boutiquetitre]" , "\${\$title}" , $pagetitletmp );
			$pagetitletmp=str_replace( "[categorietitre]" , "\$pagetitle_catname" , $pagetitletmp );
			$pagetitletmp=str_replace( "[articletitre]" , "\$article[\$libelle]" , $pagetitletmp );
			$pagetitletmp=str_replace( "[institutionneltitre]" , "\$institutionnel[\$libelle]" , $pagetitletmp );
			$pagetitletmp=str_replace( "[sscatkeywords]" , "\$metasscat" , $pagetitletmp );
			$pagetitletmp=str_replace( "[keydescription]" , "\$keydescription" , $pagetitletmp );
			$requetteupdate2.="$virgule titleDE='".addslashes($pagetitletmp)."' ";
			$metakeywords = $DB_site2->query_first(" select *  from keywords WHERE scriptname='$pagetitle[scriptname]' ");
			$pagetitletmp=$modifkeywordsDE;
			$pagetitletmp=str_replace( "[boutiquetitre]" , "\${\$title}" , $pagetitletmp );
			$pagetitletmp=str_replace( "[categorietitre]" , "\$pagetitle_catname" , $pagetitletmp );
			$pagetitletmp=str_replace( "[articletitre]" , "\$article[\$libelle]" , $pagetitletmp );
			$pagetitletmp=str_replace( "[institutionneltitre]" , "\$institutionnel[\$libelle]" , $pagetitletmp );
			$pagetitletmp=str_replace( "[sscatkeywords]" , "\$metasscat" , $pagetitletmp );
			$pagetitletmp=str_replace( "[keydescription]" , "\$keydescription" , $pagetitletmp );
			$requetteupdate3.="$virgule titleDE='".addslashes($pagetitletmp)."' ";
			$virgule=" , ";
			}
		if (in_array("SP" , $langues))
			{
			$pagetitletmp=$modiftitleSP;
			$pagetitletmp=str_replace( "[boutiquetitre]" , "\${\$title}" , $pagetitletmp );
			$pagetitletmp=str_replace( "[categorietitre]" , "\$pagetitle_catname" , $pagetitletmp );
			$pagetitletmp=str_replace( "[articletitre]" , "\$article[\$libelle]" , $pagetitletmp );
			$pagetitletmp=str_replace( "[institutionneltitre]" , "\$institutionnel[\$libelle]" , $pagetitletmp );
			$pagetitletmp=str_replace( "[sscatkeywords]" , "\$metasscat" , $pagetitletmp );
			$pagetitletmp=str_replace( "[keydescription]" , "\$keydescription" , $pagetitletmp );
			$requetteupdate.="$virgule titleSP='".addslashes($pagetitletmp)."' ";
			$metadescription = $DB_site2->query_first(" select *  from description WHERE scriptname='$pagetitle[scriptname]' ");
			$pagetitletmp=$modifdescriptionSP;
			$pagetitletmp=str_replace( "[boutiquetitre]" , "\${\$title}" , $pagetitletmp );
			$pagetitletmp=str_replace( "[categorietitre]" , "\$pagetitle_catname" , $pagetitletmp );
			$pagetitletmp=str_replace( "[articletitre]" , "\$article[\$libelle]" , $pagetitletmp );
			$pagetitletmp=str_replace( "[institutionneltitre]" , "\$institutionnel[\$libelle]" , $pagetitletmp );
			$pagetitletmp=str_replace( "[sscatkeywords]" , "\$metasscat" , $pagetitletmp );
			$pagetitletmp=str_replace( "[keydescription]" , "\$keydescription" , $pagetitletmp );
			$requetteupdate2.="$virgule titleSP='".addslashes($pagetitletmp)."' ";
			$metakeywords = $DB_site2->query_first(" select *  from keywords WHERE scriptname='$pagetitle[scriptname]' ");
			$pagetitletmp=$modifkeywordsSP;
			$pagetitletmp=str_replace( "[boutiquetitre]" , "\${\$title}" , $pagetitletmp );
			$pagetitletmp=str_replace( "[categorietitre]" , "\$pagetitle_catname" , $pagetitletmp );
			$pagetitletmp=str_replace( "[articletitre]" , "\$article[\$libelle]" , $pagetitletmp );
			$pagetitletmp=str_replace( "[institutionneltitre]" , "\$institutionnel[\$libelle]" , $pagetitletmp );
			$pagetitletmp=str_replace( "[sscatkeywords]" , "\$metasscat" , $pagetitletmp );
			$pagetitletmp=str_replace( "[keydescription]" , "\$keydescription" , $pagetitletmp );
			$requetteupdate3.="$virgule titleSP='".addslashes($pagetitletmp)."' ";
			$virgule=" , ";
			}
		if (in_array("IT" , $langues))
			{
			$pagetitletmp=$modiftitleIT;
			$pagetitletmp=str_replace( "[boutiquetitre]" , "\${\$title}" , $pagetitletmp );
			$pagetitletmp=str_replace( "[categorietitre]" , "\$pagetitle_catname" , $pagetitletmp );
			$pagetitletmp=str_replace( "[articletitre]" , "\$article[\$libelle]" , $pagetitletmp );
			$pagetitletmp=str_replace( "[institutionneltitre]" , "\$institutionnel[\$libelle]" , $pagetitletmp );
			$pagetitletmp=str_replace( "[sscatkeywords]" , "\$metasscat" , $pagetitletmp );
			$pagetitletmp=str_replace( "[keydescription]" , "\$keydescription" , $pagetitletmp );
			$requetteupdate.="$virgule titleIT='".addslashes($pagetitletmp)."' ";
			$metadescription = $DB_site2->query_first(" select *  from description WHERE scriptname='$pagetitle[scriptname]' ");
			$pagetitletmp=$modifdescriptionIT;
			$pagetitletmp=str_replace( "[boutiquetitre]" , "\${\$title}" , $pagetitletmp );
			$pagetitletmp=str_replace( "[categorietitre]" , "\$pagetitle_catname" , $pagetitletmp );
			$pagetitletmp=str_replace( "[articletitre]" , "\$article[\$libelle]" , $pagetitletmp );
			$pagetitletmp=str_replace( "[institutionneltitre]" , "\$institutionnel[\$libelle]" , $pagetitletmp );
			$pagetitletmp=str_replace( "[sscatkeywords]" , "\$metasscat" , $pagetitletmp );
			$pagetitletmp=str_replace( "[keydescription]" , "\$keydescription" , $pagetitletmp );
			$requetteupdate2.="$virgule titleIT='".addslashes($pagetitletmp)."' ";
			$metakeywords = $DB_site2->query_first(" select *  from keywords WHERE scriptname='$pagetitle[scriptname]' ");
			$pagetitletmp=$modifkeywordsIT;
			$pagetitletmp=str_replace( "[boutiquetitre]" , "\${\$title}" , $pagetitletmp );
			$pagetitletmp=str_replace( "[categorietitre]" , "\$pagetitle_catname" , $pagetitletmp );
			$pagetitletmp=str_replace( "[articletitre]" , "\$article[\$libelle]" , $pagetitletmp );
			$pagetitletmp=str_replace( "[institutionneltitre]" , "\$institutionnel[\$libelle]" , $pagetitletmp );
			$pagetitletmp=str_replace( "[sscatkeywords]" , "\$metasscat" , $pagetitletmp );
			$pagetitletmp=str_replace( "[keydescription]" , "\$keydescription" , $pagetitletmp );
			$requetteupdate3.="$virgule titleIT='".addslashes($pagetitletmp)."' ";
			$virgule=" , ";
			}
		$DB_site2->query("UPDATE pagetitle SET $requetteupdate WHERE pagetitleid = '$pagetitleid'");
		$metadescription=$DB_site2->query_first("select * from description WHERE scriptname = '$pagetitle[scriptname]'");
		$DB_site2->query("UPDATE description SET $requetteupdate2 WHERE scriptname = '$metadescription[scriptname]'");
		$metakeywords=$DB_site2->query_first("select *  from keywords WHERE scriptname = '$pagetitle[scriptname]'");
		$DB_site2->query("UPDATE keywords SET $requetteupdate3 WHERE scriptname = '$metakeywords[scriptname]'");
		echo "<br>Les modifications ont bien 굩 prises en compte.<br><br><a href=pagetitle.php>Retour</a>";
		}
	
	if ($action == "modify") 
		{
		echo "<table>
				<form action=pagetitle.php method=\"post\">
					<input type=\"hidden\" name=\"action\" value=\"update\">
					<input type=\"hidden\" name=\"pagetitleid\" value=\"$pagetitleid\">";
		if (in_array("FR", $langues))
			{
			$pagetitletmp=$pagetitle[titleFR];
			$pagetitletmp=str_replace( "\${\$title}" , "[boutiquetitre]" , $pagetitletmp );
			$pagetitletmp=str_replace( "\$pagetitle_catname" , "[categorietitre]" , $pagetitletmp );
			$pagetitletmp=str_replace( "\$article[\$libelle]" , "[articletitre]" , $pagetitletmp );
			$pagetitletmp=str_replace( "\$institutionnel[\$libelle]" , "[institutionneltitre]" , $pagetitletmp );
			$pagetitletmp=str_replace( "\$metasscat" , "[sscatkeywords]" , $pagetitletmp );
			$pagetitletmp=str_replace( "\$keydescription" , "[keydescription]" , $pagetitletmp );
			$pagetitletmp=stripslashes($pagetitletmp);
			echo "<tr>
					<td class=\"td_modules_boutiques\">titre de la fenetre FR</td>
					<td class=\"td_modules_boutiques\"><input type=\"text\" name=\"modiftitleFR\" size=\"60\" value=\"$pagetitletmp\" maxlength=\"255\"></td>
				</tr>";
			$metadescription=$DB_site2->query_first("select *  from description WHERE scriptname = '$pagetitle[scriptname]'");
			$pagetitletmp=$metadescription[titleFR];
			$pagetitletmp=str_replace( "\${\$title}" , "[boutiquetitre]" , $pagetitletmp );
			$pagetitletmp=str_replace( "\$pagetitle_catname" , "[categorietitre]" , $pagetitletmp );
			$pagetitletmp=str_replace( "\$article[\$libelle]" , "[articletitre]" , $pagetitletmp );
			$pagetitletmp=str_replace( "\$institutionnel[\$libelle]" , "[institutionneltitre]" , $pagetitletmp );
			$pagetitletmp=str_replace( "\$metasscat" , "[sscatkeywords]" , $pagetitletmp );
			$pagetitletmp=str_replace( "\$keydescription" , "[keydescription]" , $pagetitletmp );
			$pagetitletmp=stripslashes($pagetitletmp);
			echo "<tr>
					<td class=\"td_modules_boutiques\">META description FR</td>
					<td class=\"td_modules_boutiques\"><textarea name=\"modifdescriptionFR\" cols=\"60\" rows=\"5\">$pagetitletmp</textarea></td>
				</tr>";
			$metakeywords = $DB_site2->query_first(" select *  from keywords WHERE scriptname='$pagetitle[scriptname]' ");
			$pagetitletmp=$metakeywords[titleFR];
			$pagetitletmp=str_replace( "\${\$title}" , "[boutiquetitre]" , $pagetitletmp );
			$pagetitletmp=str_replace( "\$pagetitle_catname" , "[categorietitre]" , $pagetitletmp );
			$pagetitletmp=str_replace( "\$article[\$libelle]" , "[articletitre]" , $pagetitletmp );
			$pagetitletmp=str_replace( "\$institutionnel[\$libelle]" , "[institutionneltitre]" , $pagetitletmp );
			$pagetitletmp=str_replace( "\$metasscat" , "[sscatkeywords]" , $pagetitletmp );
			$pagetitletmp=str_replace( "\$keydescription" , "[keydescription]" , $pagetitletmp );
			$pagetitletmp=stripslashes($pagetitletmp);
			echo "<tr>
					<td class=\"td_modules_boutiques\">META keywords FR</td>
					<td class=\"td_modules_boutiques\"><textarea name=\"modifkeywordsFR\" cols=\"60\" rows=\"5\">$pagetitletmp</textarea></td>
				</tr>";
			}
		if (in_array("EN", $langues))
			{
			$pagetitletmp=$pagetitle[titleEN];
			$pagetitletmp=str_replace( "\${\$title}" , "[boutiquetitre]" , $pagetitletmp );
			$pagetitletmp=str_replace( "\$pagetitle_catname" , "[categorietitre]" , $pagetitletmp );
			$pagetitletmp=str_replace( "\$article[\$libelle]" , "[articletitre]" , $pagetitletmp );
			$pagetitletmp=str_replace( "\$institutionnel[\$libelle]" , "[institutionneltitre]" , $pagetitletmp );
			$pagetitletmp=str_replace( "\$metasscat" , "[sscatkeywords]" , $pagetitletmp );
			$pagetitletmp=str_replace( "\$keydescription" , "[keydescription]" , $pagetitletmp );
			$pagetitletmp=stripslashes($pagetitletmp);
			echo "<tr>
					<td class=\"td_modules_boutiques\">titre de la fenetre EN</td>
					<td class=\"td_modules_boutiques\"><input type=\"text\" name=\"modiftitleEN\" size=\"60\" value=\"$pagetitletmp\" maxlength=\"255\"></td>
				</tr>";
			$metadescription = $DB_site2->query_first(" select *  from description WHERE scriptname='$pagetitle[scriptname]' ");
			$pagetitletmp=$metadescription[titleEN];
			$pagetitletmp=str_replace( "\${\$title}" , "[boutiquetitre]" , $pagetitletmp );
			$pagetitletmp=str_replace( "\$pagetitle_catname" , "[categorietitre]" , $pagetitletmp );
			$pagetitletmp=str_replace( "\$article[\$libelle]" , "[articletitre]" , $pagetitletmp );
			$pagetitletmp=str_replace( "\$institutionnel[\$libelle]" , "[institutionneltitre]" , $pagetitletmp );
			$pagetitletmp=str_replace( "\$metasscat" , "[sscatkeywords]" , $pagetitletmp );
			$pagetitletmp=str_replace( "\$keydescription" , "[keydescription]" , $pagetitletmp );
			$pagetitletmp=stripslashes($pagetitletmp);
			echo "<tr>
					<td class=\"td_modules_boutiques\">META description EN</td>
					<td class=\"td_modules_boutiques\"><textarea name=\"modifdescriptionEN\" cols=\"60\" rows=\"5\">$pagetitletmp</textarea></td>
				</tr>";
			$metakeywords = $DB_site2->query_first(" select *  from keywords WHERE scriptname='$pagetitle[scriptname]' ");
			$pagetitletmp=$metakeywords[titleEN];
			$pagetitletmp=str_replace( "\${\$title}" , "[boutiquetitre]" , $pagetitletmp );
			$pagetitletmp=str_replace( "\$pagetitle_catname" , "[categorietitre]" , $pagetitletmp );
			$pagetitletmp=str_replace( "\$article[\$libelle]" , "[articletitre]" , $pagetitletmp );
			$pagetitletmp=str_replace( "\$institutionnel[\$libelle]" , "[institutionneltitre]" , $pagetitletmp );
			$pagetitletmp=str_replace( "\$metasscat" , "[sscatkeywords]" , $pagetitletmp );
			$pagetitletmp=str_replace( "\$keydescription" , "[keydescription]" , $pagetitletmp );
			$pagetitletmp=stripslashes($pagetitletmp);
			echo "<tr>
					<td class=\"td_modules_boutiques\">META keywords EN</td>
					<td class=\"td_modules_boutiques\"><textarea name=\"modifkeywordsEN\" cols=\"60\" rows=\"5\">$pagetitletmp</textarea></td>
				</tr>";
			}
		if (in_array("DE", $langues))
			{
			$pagetitletmp=$pagetitle[titleDE];
			$pagetitletmp=str_replace( "\${\$title}" , "[boutiquetitre]" , $pagetitletmp );
			$pagetitletmp=str_replace( "\$pagetitle_catname" , "[categorietitre]" , $pagetitletmp );
			$pagetitletmp=str_replace( "\$article[\$libelle]" , "[articletitre]" , $pagetitletmp );
			$pagetitletmp=str_replace( "\$institutionnel[\$libelle]" , "[institutionneltitre]" , $pagetitletmp );
			$pagetitletmp=str_replace( "\$metasscat" , "[sscatkeywords]" , $pagetitletmp );
			$pagetitletmp=str_replace( "\$keydescription" , "[keydescription]" , $pagetitletmp );
			$pagetitletmp=stripslashes($pagetitletmp);
			echo "<tr>
					<td class=\"td_modules_boutiques\">titre de la fenetre DE</td>
					<td class=\"td_modules_boutiques\"><input type=\"text\" name=\"modiftitleDE\" size=\"60\" value=\"$pagetitletmp\" maxlength=\"255\"></td>
				</tr>";
			$metadescription = $DB_site2->query_first(" select *  from description WHERE scriptname='$pagetitle[scriptname]' ");
			$pagetitletmp=$metadescription[titleDE];
			$pagetitletmp=str_replace( "\${\$title}" , "[boutiquetitre]" , $pagetitletmp );
			$pagetitletmp=str_replace( "\$pagetitle_catname" , "[categorietitre]" , $pagetitletmp );
			$pagetitletmp=str_replace( "\$article[\$libelle]" , "[articletitre]" , $pagetitletmp );
			$pagetitletmp=str_replace( "\$institutionnel[\$libelle]" , "[institutionneltitre]" , $pagetitletmp );
			$pagetitletmp=str_replace( "\$metasscat" , "[sscatkeywords]" , $pagetitletmp );
			$pagetitletmp=str_replace( "\$keydescription" , "[keydescription]" , $pagetitletmp );
			$pagetitletmp=stripslashes($pagetitletmp);
			echo "<tr>
					<td class=\"td_modules_boutiques\">META description DE</td>
					<td class=\"td_modules_boutiques\"><textarea name=\"modifdescriptionDE\" cols=\"60\" rows=\"5\">$pagetitletmp</textarea></td>
				</tr>";
			$metakeywords = $DB_site2->query_first(" select *  from keywords WHERE scriptname='$pagetitle[scriptname]' ");
			$pagetitletmp=$metakeywords[titleDE];
			$pagetitletmp=str_replace( "\${\$title}" , "[boutiquetitre]" , $pagetitletmp );
			$pagetitletmp=str_replace( "\$pagetitle_catname" , "[categorietitre]" , $pagetitletmp );
			$pagetitletmp=str_replace( "\$article[\$libelle]" , "[articletitre]" , $pagetitletmp );
			$pagetitletmp=str_replace( "\$institutionnel[\$libelle]" , "[institutionneltitre]" , $pagetitletmp );
			$pagetitletmp=str_replace( "\$metasscat" , "[sscatkeywords]" , $pagetitletmp );
			$pagetitletmp=str_replace( "\$keydescription" , "[keydescription]" , $pagetitletmp );
			$pagetitletmp=stripslashes($pagetitletmp);
			echo "<tr>
					<td class=\"td_modules_boutiques\">META keywords DE</td>
					<td class=\"td_modules_boutiques\"><textarea name=\"modifkeywordsDE\" cols=\"60\" rows=\"5\">$pagetitletmp</textarea></td>
				</tr>";
			}
		if (in_array("SP", $langues))
			{
			$pagetitletmp=$pagetitle[titleSP];
			$pagetitletmp=str_replace( "\${\$title}" , "[boutiquetitre]" , $pagetitletmp );
			$pagetitletmp=str_replace( "\$pagetitle_catname" , "[categorietitre]" , $pagetitletmp );
			$pagetitletmp=str_replace( "\$article[\$libelle]" , "[articletitre]" , $pagetitletmp );
			$pagetitletmp=str_replace( "\$institutionnel[\$libelle]" , "[institutionneltitre]" , $pagetitletmp );
			$pagetitletmp=str_replace( "\$metasscat" , "[sscatkeywords]" , $pagetitletmp );
			$pagetitletmp=str_replace( "\$keydescription" , "[keydescription]" , $pagetitletmp );
			$pagetitletmp=stripslashes($pagetitletmp);
			echo "<tr>
					<td class=\"td_modules_boutiques\">titre de la fenetre SP</td>
					<td class=\"td_modules_boutiques\"><input type=\"text\" name=\"modiftitleSP\" size=\"60\" value=\"$pagetitletmp\" maxlength=\"255\"></td>
				</tr>";
			$metadescription = $DB_site2->query_first(" select *  from description WHERE scriptname='$pagetitle[scriptname]' ");
			$pagetitletmp=$metadescription[titleSP];
			$pagetitletmp=str_replace( "\${\$title}" , "[boutiquetitre]" , $pagetitletmp );
			$pagetitletmp=str_replace( "\$pagetitle_catname" , "[categorietitre]" , $pagetitletmp );
			$pagetitletmp=str_replace( "\$article[\$libelle]" , "[articletitre]" , $pagetitletmp );
			$pagetitletmp=str_replace( "\$institutionnel[\$libelle]" , "[institutionneltitre]" , $pagetitletmp );
			$pagetitletmp=str_replace( "\$metasscat" , "[sscatkeywords]" , $pagetitletmp );
			$pagetitletmp=str_replace( "\$keydescription" , "[keydescription]" , $pagetitletmp );
			$pagetitletmp=stripslashes($pagetitletmp);
			echo "<tr>
					<td class=\"td_modules_boutiques\">META description SP</td>
					<td class=\"td_modules_boutiques\"><textarea name=\"modifdescriptionSP\" cols=\"60\" rows=\"5\">$pagetitletmp</textarea></td></tr>";
			$metakeywords = $DB_site2->query_first(" select *  from keywords WHERE scriptname='$pagetitle[scriptname]' ");
			$pagetitletmp=$metakeywords[titleSP];
			$pagetitletmp=str_replace( "\${\$title}" , "[boutiquetitre]" , $pagetitletmp );
			$pagetitletmp=str_replace( "\$pagetitle_catname" , "[categorietitre]" , $pagetitletmp );
			$pagetitletmp=str_replace( "\$article[\$libelle]" , "[articletitre]" , $pagetitletmp );
			$pagetitletmp=str_replace( "\$institutionnel[\$libelle]" , "[institutionneltitre]" , $pagetitletmp );
			$pagetitletmp=str_replace( "\$metasscat" , "[sscatkeywords]" , $pagetitletmp );
			$pagetitletmp=str_replace( "\$keydescription" , "[keydescription]" , $pagetitletmp );
			$pagetitletmp=stripslashes($pagetitletmp);
			echo "<tr>
					<td class=\"td_modules_boutiques\">META keywords SP</td>
					<td class=\"td_modules_boutiques\"><textarea name=\"modifkeywordsSP\" cols=\"60\" rows=\"5\">$pagetitletmp</textarea></td>
				</tr>";
			}
		if (in_array("IT", $langues))
			{
			$pagetitletmp=$pagetitle[titleIT];
			$pagetitletmp=str_replace( "\${\$title}" , "[boutiquetitre]" , $pagetitletmp );
			$pagetitletmp=str_replace( "\$pagetitle_catname" , "[categorietitre]" , $pagetitletmp );
			$pagetitletmp=str_replace( "\$article[\$libelle]" , "[articletitre]" , $pagetitletmp );
			$pagetitletmp=str_replace( "\$institutionnel[\$libelle]" , "[institutionneltitre]" , $pagetitletmp );
			$pagetitletmp=str_replace( "\$metasscat" , "[sscatkeywords]" , $pagetitletmp );
			$pagetitletmp=str_replace( "\$keydescription" , "[keydescription]" , $pagetitletmp );
			$pagetitletmp=stripslashes($pagetitletmp);
			echo "<tr>
					<td class=\"td_modules_boutiques\">titre de la fenetre IT</td>
					<td class=\"td_modules_boutiques\"><input type=\"text\" name=\"modiftitleIT\" size=\"60\" value=\"$pagetitletmp\" maxlength=\"255\"></td>
				</tr>";
			$metadescription = $DB_site2->query_first(" select *  from description WHERE scriptname='$pagetitle[scriptname]' ");
			$pagetitletmp=$metadescription[titleIT];
			$pagetitletmp=str_replace( "\${\$title}" , "[boutiquetitre]" , $pagetitletmp );
			$pagetitletmp=str_replace( "\$pagetitle_catname" , "[categorietitre]" , $pagetitletmp );
			$pagetitletmp=str_replace( "\$article[\$libelle]" , "[articletitre]" , $pagetitletmp );
			$pagetitletmp=str_replace( "\$institutionnel[\$libelle]" , "[institutionneltitre]" , $pagetitletmp );
			$pagetitletmp=str_replace( "\$metasscat" , "[sscatkeywords]" , $pagetitletmp );
			$pagetitletmp=str_replace( "\$keydescription" , "[keydescription]" , $pagetitletmp );
			$pagetitletmp=stripslashes($pagetitletmp);
			echo "<tr>
					<td class=\"td_modules_boutiques\">META description IT</td>
					<td class=\"td_modules_boutiques\"><textarea name=\"modifdescriptionIT\" cols=\"60\" rows=\"5\">$pagetitletmp</textarea></td>
				</tr>";
			$metakeywords = $DB_site2->query_first(" select *  from keywords WHERE scriptname='$pagetitle[scriptname]' ");
			$pagetitletmp=$metakeywords[titleIT];
			$pagetitletmp=str_replace( "\${\$title}" , "[boutiquetitre]" , $pagetitletmp );
			$pagetitletmp=str_replace( "\$pagetitle_catname" , "[categorietitre]" , $pagetitletmp );
			$pagetitletmp=str_replace( "\$article[\$libelle]" , "[articletitre]" , $pagetitletmp );
			$pagetitletmp=str_replace( "\$institutionnel[\$libelle]" , "[institutionneltitre]" , $pagetitletmp );
			$pagetitletmp=str_replace( "\$metasscat" , "[sscatkeywords]" , $pagetitletmp );
			$pagetitletmp=str_replace( "\$keydescription" , "[keydescription]" , $pagetitletmp );
			$pagetitletmp=stripslashes($pagetitletmp);
			echo "<tr>
					<td class=\"td_modules_boutiques\">META keywords IT</td>
					<td class=\"td_modules_boutiques\"><textarea name=\"modifkeywordsIT\" cols=\"60\" rows=\"5\">$pagetitletmp</textarea></td>
				</tr>";
			}
		echo "<tr>
				<td colspan=\"2\" align=\"center\"><input type=\"submit\" value=\"modifier\"></td>
			</tr>
		</form>
		</table>
		<br><br><a href=pagetitle.php>Retour</a>";
		}
	} 
else 
	{
	echo "<table class=\"table_modules_boutiques\">";
	$pagetitles = $DB_site2->query(" select *  from pagetitle order by pagetitleid ASC ");
	$virgule="";
	while ($pagetitle=$DB_site->fetch_array($pagetitles)) 
		{
		reset($script);
		while (list($key, $val) = each($script)) 
			{
			if ($pagetitle[scriptname] == $script[$key][script]) 
				{
				$scriptname=str_replace( $pagetitle[scriptname] , $script[$key][nom] , $pagetitle[scriptname] );
				}
			}
		
		echo " $virgule <tr><td class=\"td_modules_boutiques\">$scriptname</td><td class=\"td_modules_boutiques\"><a href=pagetitle.php?action=modify&pagetitleid=$pagetitle[pagetitleid]>Modifier</a></td></tr>";
		
		if (in_array("FR", $langues))
			{
			$pagetitletmp=$pagetitle[titleFR];
			$pagetitletmp=str_replace( "\${\$title}" , "[boutiquetitre]" , $pagetitletmp );
			$pagetitletmp=str_replace( "\$pagetitle_catname" , "[categorietitre]" , $pagetitletmp );
			$pagetitletmp=str_replace( "\$article[\$libelle]" , "[articletitre]" , $pagetitletmp );
			$pagetitletmp=str_replace( "\$institutionnel[\$libelle]" , "[institutionneltitre]" , $pagetitletmp );
			$pagetitletmp=str_replace( "\$metasscat" , "[sscatkeywords]" , $pagetitletmp );
			$pagetitletmp=str_replace( "\$keydescription" , "[keydescription]" , $pagetitletmp );
			$pagetitletmp=stripslashes($pagetitletmp);
			echo "<tr><td class=\"td_modules_boutiques\">titre de la fenetre FR</td><td class=\"td_modules_boutiques\">$pagetitletmp</td></tr>";
			$metadescription = $DB_site2->query_first(" select *  from description WHERE scriptname='$pagetitle[scriptname]' ");
			$pagetitletmp=$metadescription[titleFR];
			$pagetitletmp=str_replace( "\${\$title}" , "[boutiquetitre]" , $pagetitletmp );
			$pagetitletmp=str_replace( "\$pagetitle_catname" , "[categorietitre]" , $pagetitletmp );
			$pagetitletmp=str_replace( "\$article[\$libelle]" , "[articletitre]" , $pagetitletmp );
			$pagetitletmp=str_replace( "\$institutionnel[\$libelle]" , "[institutionneltitre]" , $pagetitletmp );
			$pagetitletmp=str_replace( "\$metasscat" , "[sscatkeywords]" , $pagetitletmp );
			$pagetitletmp=str_replace( "\$keydescription" , "[keydescription]" , $pagetitletmp );
			$pagetitletmp=stripslashes($pagetitletmp);
			echo "<tr><td class=\"td_modules_boutiques\">META description FR</td><td class=\"td_modules_boutiques\">$pagetitletmp</td></tr>";
			$metakeywords = $DB_site2->query_first(" select *  from keywords WHERE scriptname='$pagetitle[scriptname]' ");
			$pagetitletmp=$metakeywords[titleFR];
			$pagetitletmp=str_replace( "\${\$title}" , "[boutiquetitre]" , $pagetitletmp );
			$pagetitletmp=str_replace( "\$pagetitle_catname" , "[categorietitre]" , $pagetitletmp );
			$pagetitletmp=str_replace( "\$article[\$libelle]" , "[articletitre]" , $pagetitletmp );
			$pagetitletmp=str_replace( "\$institutionnel[\$libelle]" , "[institutionneltitre]" , $pagetitletmp );
			$pagetitletmp=str_replace( "\$metasscat" , "[sscatkeywords]" , $pagetitletmp );
			$pagetitletmp=str_replace( "\$keydescription" , "[keydescription]" , $pagetitletmp );
			$pagetitletmp=stripslashes($pagetitletmp);
			echo "<tr><td class=\"td_modules_boutiques\">META keywords FR</td><td class=\"td_modules_boutiques\">$pagetitletmp</td></tr>";
			}
		if (in_array("EN", $langues))
			{
			$pagetitletmp=$pagetitle[titleEN];
			$pagetitletmp=str_replace( "\${\$title}" , "[boutiquetitre]" , $pagetitletmp );
			$pagetitletmp=str_replace( "\$pagetitle_catname" , "[categorietitre]" , $pagetitletmp );
			$pagetitletmp=str_replace( "\$article[\$libelle]" , "[articletitre]" , $pagetitletmp );
			$pagetitletmp=str_replace( "\$institutionnel[\$libelle]" , "[institutionneltitre]" , $pagetitletmp );
			$pagetitletmp=str_replace( "\$metasscat" , "[sscatkeywords]" , $pagetitletmp );
			$pagetitletmp=str_replace( "\$keydescription" , "[keydescription]" , $pagetitletmp );
			$pagetitletmp=stripslashes($pagetitletmp);
			echo "<tr><td class=\"td_modules_boutiques\">titre de la fenetre EN</td><td class=\"td_modules_boutiques\">$pagetitletmp</td></tr>";
			$metadescription = $DB_site2->query_first(" select *  from description WHERE scriptname='$pagetitle[scriptname]' ");
			$pagetitletmp=$metadescription[titleEN];
			$pagetitletmp=str_replace( "\${\$title}" , "[boutiquetitre]" , $pagetitletmp );
			$pagetitletmp=str_replace( "\$pagetitle_catname" , "[categorietitre]" , $pagetitletmp );
			$pagetitletmp=str_replace( "\$article[\$libelle]" , "[articletitre]" , $pagetitletmp );
			$pagetitletmp=str_replace( "\$institutionnel[\$libelle]" , "[institutionneltitre]" , $pagetitletmp );
			$pagetitletmp=str_replace( "\$metasscat" , "[sscatkeywords]" , $pagetitletmp );
			$pagetitletmp=str_replace( "\$keydescription" , "[keydescription]" , $pagetitletmp );
			$pagetitletmp=stripslashes($pagetitletmp);
			echo "<tr><td class=\"td_modules_boutiques\">META description EN</td><td class=\"td_modules_boutiques\">$pagetitletmp</td></tr>";
			$metakeywords = $DB_site2->query_first(" select *  from keywords WHERE scriptname='$pagetitle[scriptname]' ");
			$pagetitletmp=$metakeywords[titleEN];
			$pagetitletmp=str_replace( "\${\$title}" , "[boutiquetitre]" , $pagetitletmp );
			$pagetitletmp=str_replace( "\$pagetitle_catname" , "[categorietitre]" , $pagetitletmp );
			$pagetitletmp=str_replace( "\$article[\$libelle]" , "[articletitre]" , $pagetitletmp );
			$pagetitletmp=str_replace( "\$institutionnel[\$libelle]" , "[institutionneltitre]" , $pagetitletmp );
			$pagetitletmp=str_replace( "\$metasscat" , "[sscatkeywords]" , $pagetitletmp );
			$pagetitletmp=str_replace( "\$keydescription" , "[keydescription]" , $pagetitletmp );
			$pagetitletmp=stripslashes($pagetitletmp);
			echo "<tr><td class=\"td_modules_boutiques\">META keywords EN</td><td class=\"td_modules_boutiques\">$pagetitletmp</td></tr>";
			}
		if (in_array("DE", $langues))
			{
			$pagetitletmp=$pagetitle[titleDE];
			$pagetitletmp=str_replace( "\${\$title}" , "[boutiquetitre]" , $pagetitletmp );
			$pagetitletmp=str_replace( "\$pagetitle_catname" , "[categorietitre]" , $pagetitletmp );
			$pagetitletmp=str_replace( "\$article[\$libelle]" , "[articletitre]" , $pagetitletmp );
			$pagetitletmp=str_replace( "\$institutionnel[\$libelle]" , "[institutionneltitre]" , $pagetitletmp );
			$pagetitletmp=str_replace( "\$metasscat" , "[sscatkeywords]" , $pagetitletmp );
			$pagetitletmp=str_replace( "\$keydescription" , "[keydescription]" , $pagetitletmp );
			$pagetitletmp=stripslashes($pagetitletmp);
			echo "<tr><td class=\"td_modules_boutiques\">titre de la fenetre DE</td><td class=\"td_modules_boutiques\">$pagetitletmp</td></tr>";
			$metadescription = $DB_site2->query_first(" select *  from description WHERE scriptname='$pagetitle[scriptname]' ");
			$pagetitletmp=$metadescription[titleDE];
			$pagetitletmp=str_replace( "\${\$title}" , "[boutiquetitre]" , $pagetitletmp );
			$pagetitletmp=str_replace( "\$pagetitle_catname" , "[categorietitre]" , $pagetitletmp );
			$pagetitletmp=str_replace( "\$article[\$libelle]" , "[articletitre]" , $pagetitletmp );
			$pagetitletmp=str_replace( "\$institutionnel[\$libelle]" , "[institutionneltitre]" , $pagetitletmp );
			$pagetitletmp=str_replace( "\$metasscat" , "[sscatkeywords]" , $pagetitletmp );
			$pagetitletmp=str_replace( "\$keydescription" , "[keydescription]" , $pagetitletmp );
			$pagetitletmp=stripslashes($pagetitletmp);
			echo "<tr><td class=\"td_modules_boutiques\">META description DE</td><td class=\"td_modules_boutiques\">$pagetitletmp</td></tr>";
			$metakeywords = $DB_site2->query_first(" select *  from keywords WHERE scriptname='$pagetitle[scriptname]' ");
			$pagetitletmp=$metakeywords[titleDE];
			$pagetitletmp=str_replace( "\${\$title}" , "[boutiquetitre]" , $pagetitletmp );
			$pagetitletmp=str_replace( "\$pagetitle_catname" , "[categorietitre]" , $pagetitletmp );
			$pagetitletmp=str_replace( "\$article[\$libelle]" , "[articletitre]" , $pagetitletmp );
			$pagetitletmp=str_replace( "\$institutionnel[\$libelle]" , "[institutionneltitre]" , $pagetitletmp );
			$pagetitletmp=str_replace( "\$metasscat" , "[sscatkeywords]" , $pagetitletmp );
			$pagetitletmp=str_replace( "\$keydescription" , "[keydescription]" , $pagetitletmp );
			$pagetitletmp=stripslashes($pagetitletmp);
			echo "<tr><td class=\"td_modules_boutiques\">META keywords DE</td><td class=\"td_modules_boutiques\">$pagetitletmp</td></tr>";
			}
		if (in_array("SP" , $langues))
			{
			$pagetitletmp=$pagetitle[titleSP];
			$pagetitletmp=str_replace( "\${\$title}" , "[boutiquetitre]" , $pagetitletmp );
			$pagetitletmp=str_replace( "\$pagetitle_catname" , "[categorietitre]" , $pagetitletmp );
			$pagetitletmp=str_replace( "\$article[\$libelle]" , "[articletitre]" , $pagetitletmp );
			$pagetitletmp=str_replace( "\$institutionnel[\$libelle]" , "[institutionneltitre]" , $pagetitletmp );
			$pagetitletmp=str_replace( "\$metasscat" , "[sscatkeywords]" , $pagetitletmp );
			$pagetitletmp=str_replace( "\$keydescription" , "[keydescription]" , $pagetitletmp );
			$pagetitletmp=stripslashes($pagetitletmp);
			echo "<tr><td class=\"td_modules_boutiques\">titre de la fenetre SP</td><td class=\"td_modules_boutiques\">$pagetitletmp</td></tr>";
			$metadescription = $DB_site2->query_first(" select *  from description WHERE scriptname='$pagetitle[scriptname]' ");
			$pagetitletmp=$metadescription[titleSP];
			$pagetitletmp=str_replace( "\${\$title}" , "[boutiquetitre]" , $pagetitletmp );
			$pagetitletmp=str_replace( "\$pagetitle_catname" , "[categorietitre]" , $pagetitletmp );
			$pagetitletmp=str_replace( "\$article[\$libelle]" , "[articletitre]" , $pagetitletmp );
			$pagetitletmp=str_replace( "\$institutionnel[\$libelle]" , "[institutionneltitre]" , $pagetitletmp );
			$pagetitletmp=str_replace( "\$metasscat" , "[sscatkeywords]" , $pagetitletmp );
			$pagetitletmp=str_replace( "\$keydescription" , "[keydescription]" , $pagetitletmp );
			$pagetitletmp=stripslashes($pagetitletmp);
			echo "<tr><td class=\"td_modules_boutiques\">META description SP</td><td class=\"td_modules_boutiques\">$pagetitletmp</td></tr>";
			$metakeywords = $DB_site2->query_first(" select *  from keywords WHERE scriptname='$pagetitle[scriptname]' ");
			$pagetitletmp=$metakeywords[titleSP];
			$pagetitletmp=str_replace( "\${\$title}" , "[boutiquetitre]" , $pagetitletmp );
			$pagetitletmp=str_replace( "\$pagetitle_catname" , "[categorietitre]" , $pagetitletmp );
			$pagetitletmp=str_replace( "\$article[\$libelle]" , "[articletitre]" , $pagetitletmp );
			$pagetitletmp=str_replace( "\$institutionnel[\$libelle]" , "[institutionneltitre]" , $pagetitletmp );
			$pagetitletmp=str_replace( "\$metasscat" , "[sscatkeywords]" , $pagetitletmp );
			$pagetitletmp=str_replace( "\$keydescription" , "[keydescription]" , $pagetitletmp );
			$pagetitletmp=stripslashes($pagetitletmp);
			echo "<tr><td class=\"td_modules_boutiques\">META keywords SP</td><td class=\"td_modules_boutiques\">$pagetitletmp</td></tr>";
			}
		if (in_array("IT" , $langues))
			{
			$pagetitletmp=$pagetitle[titleIT];
			$pagetitletmp=str_replace( "\${\$title}" , "[boutiquetitre]" , $pagetitletmp );
			$pagetitletmp=str_replace( "\$pagetitle_catname" , "[categorietitre]" , $pagetitletmp );
			$pagetitletmp=str_replace( "\$article[\$libelle]" , "[articletitre]" , $pagetitletmp );
			$pagetitletmp=str_replace( "\$institutionnel[\$libelle]" , "[institutionneltitre]" , $pagetitletmp );
			$pagetitletmp=str_replace( "\$metasscat" , "[sscatkeywords]" , $pagetitletmp );
			$pagetitletmp=str_replace( "\$keydescription" , "[keydescription]" , $pagetitletmp );
			$pagetitletmp=stripslashes($pagetitletmp);
			echo "<tr><td class=\"td_modules_boutiques\">titre de la fenetre IT</td><td class=\"td_modules_boutiques\">$pagetitletmp</td></tr>";
			$metadescription = $DB_site2->query_first(" select *  from description WHERE scriptname='$pagetitle[scriptname]' ");
			$pagetitletmp=$metadescription[titleIT];
			$pagetitletmp=str_replace( "\${\$title}" , "[boutiquetitre]" , $pagetitletmp );
			$pagetitletmp=str_replace( "\$pagetitle_catname" , "[categorietitre]" , $pagetitletmp );
			$pagetitletmp=str_replace( "\$article[\$libelle]" , "[articletitre]" , $pagetitletmp );
			$pagetitletmp=str_replace( "\$institutionnel[\$libelle]" , "[institutionneltitre]" , $pagetitletmp );
			$pagetitletmp=str_replace( "\$metasscat" , "[sscatkeywords]" , $pagetitletmp );
			$pagetitletmp=str_replace( "\$keydescription" , "[keydescription]" , $pagetitletmp );
			$pagetitletmp=stripslashes($pagetitletmp);
			echo "<tr><td class=\"td_modules_boutiques\">META description IT</td><td class=\"td_modules_boutiques\">$pagetitletmp</td></tr>";
			$metakeywords = $DB_site2->query_first(" select *  from keywords WHERE scriptname='$pagetitle[scriptname]' ");
			$pagetitletmp=$metakeywords[titleIT];
			$pagetitletmp=str_replace( "\${\$title}" , "[boutiquetitre]" , $pagetitletmp );
			$pagetitletmp=str_replace( "\$pagetitle_catname" , "[categorietitre]" , $pagetitletmp );
			$pagetitletmp=str_replace( "\$article[\$libelle]" , "[articletitre]" , $pagetitletmp );
			$pagetitletmp=str_replace( "\$institutionnel[\$libelle]" , "[institutionneltitre]" , $pagetitletmp );
			$pagetitletmp=str_replace( "\$metasscat" , "[sscatkeywords]" , $pagetitletmp );
			$pagetitletmp=str_replace( "\$keydescription" , "[keydescription]" , $pagetitletmp );
			$pagetitletmp=stripslashes($pagetitletmp);
			echo "<tr><td class=\"td_modules_boutiques\">META keywords IT</td><td class=\"td_modules_boutiques\">$pagetitletmp</td></tr>";
			}
		$virgule="<tr><td colspan=\"2\"><hr></td></tr>";
		}
	echo "</table>";
	}

echo "<br><br><div align=left><b>Nota :</b><br>
[boutiquetitre] repr괥nte le nom de votre boutique.<br>
[categorietitre] repr괥nte le nom de la catꨯrie en cours de visualisation <font style=\"font-size:9px;\">( en dehors d'une page de visualisation d'une catꨯrie ou d'un article, [categorietitre] ne vaux rien )</font><br>
[articletitre] repr괥nte le nom d'un article <font style=\"font-size:9px;\">( en dehors d'une page de visualisation d'un article , [articletitre] ne vaux rien )</font><br>
[institutionneltitre] repr괥nte le lib꭬顤'une page institutionnelle <font style=\"font-size:9px;\">( en dehors d'une page institutionnelle , [institutionneltitre] ne vaux rien )</font><br>
[sscatkeywords] repr괥nte les sous categories d'une page de visualisation de categorie <font style=\"font-size:9px;\">( en dehors d'une page de visualisation de categorie , [sscatkeywords] ne vaux rien )</font><br>
[keydescription] repr괥nte les mots clef de la description d'un article <font style=\"font-size:9px;\">( en dehors d'une page de visualisation d'article , [keydescription] ne vaux rien )</font></div>
*/

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

include "./footer.php";
?>