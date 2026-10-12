<?php

require "admin_global.php";
include "./header.php";
include "./menu.php";
include "./fonctions.php";	

echo "<td valign=\"top\">
<table width=\"100%\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\"><tr><td>
<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\">
  <tr>
   <td>
   	<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\">
		<tr>
			<td height=\"25\" nowrap> &nbsp; <img src=\"images/arrow_2.jpg\"> <font class=\"cost\">Déplacer un produit</font>
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
		<table align=\"center\">
			<tr>
				<td align=\"center\" valign=\"middle\">" ;


function arborescence($parentid){
	global $DB_site2,$select,$niveau,$libelle,$catid,$libellecat;
	$niveau.="--->";
	$requete_arbo=" select $libelle,catid from categorie where parentid='$parentid' ORDER BY $libelle ";
	$arbos=$DB_site2->query($requete_arbo);
	while ($arbo=$DB_site2->fetch_array($arbos)) {
		if($catid==$arbo[catid]) {
			$selected="selected";
			$libellecat=$arbo[$libelle];
		} else {
			$selected="";
		}
		$select.="<option value=\"$arbo[catid]\" $selected>$niveau $arbo[$libelle]</option>";
		arborescence($arbo[catid]);
	}
	$niveau=substr($niveau , 0 , strlen($niveau)-4 );
	return ($select);
}

function navigation($catidfunc , $isarticle=0){
	$separateur_navigation=" > ";
	global $DB_site2,$libelle,$navigationbit,$parentid;
	$requete_categorie="select catid , $libelle , parentid from categorie where catid='$catidfunc' ";
	$navigation_categorie=$DB_site2->query_first($requete_categorie);
	if ($parentid==$catidfunc and $isarticle==0) {
		$navigationbit=$navigation_categorie[$libelle];
	} else {
		$navigationbit="<a href=\"catalogue.php?catid=$navigation_categorie[catid]\">$navigation_categorie[$libelle]</a>" . $separateur_navigation . $navigationbit;
	}
	if ($navigation_categorie[parentid]!=0) {
		navigation($navigation_categorie[parentid]);
	}
}

if ($artid=="" || !isset($artid))
{
	echo "ERREUR !!!";
	include "./footer.php";
	exit;
}

$article=$DB_site2->query_first("SELECT * FROM article WHERE artid='$artid' ");
$catid=$article[catid];

//*******************************************************************
//****************AFFICHAGE BARRE NAVIGATION*************************
//*******************************************************************

$navigationdebut="<a href=\"catalogue.php\">".${"title$langue"}."</a> > ";
navigation($catid,1);
echo ($navigationdebut.$navigationbit."Déplacer le produit '".$article[$libelle]."'<br><br>");

if($action=="") {

arborescence(0);
echo "Déplacer l'article '".$article[$libelle]."' dans : <br><br>	
	<form action=\"deplacer_produit.php\" method=\"post\">
	<select name=depl_catid size=25>
	<option>...................................................................................................................................................................</option>
	<option value=\"0\">".${"title$langue"}."</option>
	$select
	</select><br><bR>
	<input type=\"hidden\" name=\"artid\" value=\"$artid\">
	<input type=\"hidden\" name=\"action\" value=\"deplacer\">
	<input type=\"submit\" value=\"déplacer\"><br>
	<br><br><a href=catalogue.php>Retour</a>";

}

if($action=="deplacer") {
	if($depl_catid!="") {
		$catid_req = $DB_site2->query_first("SELECT catid FROM article WHERE artid='$artid';");
		$DB_site2->query("UPDATE article SET catid='$depl_catid' WHERE artid='$artid';");
		
		//verifier que la categorie cible integre les meme caracteristique
		$DB_site2->query("INSERT IGNORE INTO categorie_caracteristique (catid, caractid, visible, filtrable) SELECT '$depl_catid' as catid, caractid, visible, filtrable FROM categorie_caracteristique WHERE catid = '".$catid_req['catid']."';");
		
		echo "Cet article a bien été déplacé...<br><br>
		<a href=catalogue.php>Retour</a>";
// mise à jour du treeview
	$DB_site2->query("update treeview_temporaire set dateline = dateline - 10000");


	} else {
		echo "ERREUR !!!";
		include "./footer.php";
		exit;
	}
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
?>