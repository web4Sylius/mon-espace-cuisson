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
			<td height=\"25\" nowrap> &nbsp; <img src=\"images/arrow_2.jpg\"> <font class=\"cost\">Rangements</font>
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


// MODIFIER UN RANGEMENT
if (isset($action) and $action == "modifier"){
	$mq=$DB_site2->query_first("SELECT * from rangement where rangementid = '$rangementid'");
	$singulier = $mq[lib_singulier] ;
	$pluriel = $mq[lib_pluriel] ;
	echo "<h3>Modifier un rangement</h3>
	<form method=\"post\">
	<input type=\"hidden\" name=\"action\" value=\"modifier2\">
	<table>
		<tr>
			<td>Libellé singulier : </td>
			<td><input type=\"text\" name=\"singulier\" value=\"$singulier\"></td>
		</tr>
		<tr>
			<td>Libellé pluriel : </td>
			<td><input type=\"text\" name=\"pluriel\" value=\"$pluriel\"></td>
		</tr>
	</table>
	<bR><input type=\"submit\" value=\"Modifier\">
	<br></form><br><a href=\"rangements.php\">Retour</a>
	" ;
}

// MODIFIER UN RANGEMENT
if (isset($action) and $action == "modifier2"){
	$erreur = "" ;
	if ($singulier != "" and $pluriel != ""){
		$DB_site2->query("update rangement set lib_singulier = '$singulier', lib_pluriel = '$pluriel' where rangementid = '$rangementid'");
	}
	else{
		$erreur = "Les deux champs sont obligatoires" ;
	}
	$action = "" ;
}

// SUPPRIMER UN RANGEMENT
if (isset($action) and $action == "supprimer"){
	echo "Etes-vous sûr(e) de vouloir supprimer ce rangement ?<br><br>
	<a href=\"rangements.php?action=supprimer2&rangementid=$rangementid\">OUI</a> | <a href=\"rangements.php\">NON</a>" ;
}

// SUPPRIMER UN RANGEMENT (validé)
if (isset($action) and $action == "supprimer2"){
	$suppr=$DB_site2->query("delete from rangement where rangementid = '$rangementid'");
	$suppr2=$DB_site2->query("delete from article_rangement where rangementid = '$rangementid'");
	$action = "" ;
}


// AJOUTER UN RANGEMENT (validé)
if (isset($action) and $action == "ajouter2"){
	$erreur = "" ;
	if ($singulier != "" and $pluriel != ""){
		$new=$DB_site2->query("insert into rangement (lib_singulier, lib_pluriel) values ('$singulier', '$pluriel')");
		$action = "" ;
	}else{
		$erreur = "Les deux champs sont obligatoires" ;
		echo $erreur ;
		$action = "ajouter" ;
	}
}

// AJOUTER UN RANGEMENT
if (isset($action) and $action == "ajouter"){
	echo "<h3>Ajouter un rangement</h3>
	<form method=\"post\" enctype=\"multipart/form-data\">
	<input type=\"hidden\" name=\"action\" value=\"ajouter2\">
	<table>
		<tr>
			<td>Libellé au singulier : </td>
			<td><input type=\"text\" name=\"singulier\" value=\"$singulier\"></td>
		</tr>
		<tr>
			<td>Libellé au pluriel : </td>
			<td><input type=\"text\" name=\"pluriel\" value=\"$pluriel\"></td>
		</tr>
	</table>
	<bR><input type=\"submit\" value=\"Ajouter\">
	<br></form><br><a href=\"rangements.php\">Retour</a>" ;
}


if (!isset($action) or $action == ""){
	echo "
		<br><br><table class=\"table_moyen_paiement\">
		<tr>
			<td class=\"td_head_comparateurs\"><strong>Libellé</strong></td>
			<td class=\"td_head_comparateurs\" width=\"20%\"><strong>ACTION</strong></td>
		</tr>" ;
	$rangements=$DB_site2->query("SELECT * from rangement");
	$nb_marques=$DB_site2->num_rows($rangements) ;
	$i_marque = 0 ;
	while ($rangement=$DB_site2->fetch_array($rangements)){
		echo "<tr>
				<td class=\"td_location\" align=\"left\">$rangement[lib_singulier]</td>
				<td class=\"td_location\" align=\"center\"><a href=\"rangements.php?action=modifier&rangementid=$rangement[rangementid]\"><img src=\"images/editer.gif\"></a>&nbsp;&nbsp;&nbsp;&nbsp;<a href=\"rangements.php?action=supprimer&rangementid=$rangement[rangementid]\"><img src=\"images/supprimer.gif\"></a></td>
			</tr>" ;
	}
	echo "</table><br><br>
	<a href=\"rangements.php?action=ajouter\">Ajouter un rangement</a>" ;
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