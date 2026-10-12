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
			<td height=\"25\" nowrap> &nbsp; <img src=\"images/arrow_2.jpg\"> <font class=\"cost\">Gestion des bons de réduction</font>
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


if (isset($caractid) and $caractid!="" and $action=="create" ) {
	if (${$libelle}!="") {
		$ajout_caract_val=" INSERT INTO caracteristiquevaleur ( caractid, libelleFR, libelleDE, libelleEN, libelleIT, libelleSP ) VALUES ( '$caractid', '".htmlentities($libelleFR)."', '".htmlentities($libelleDE)."', '".htmlentities($libelleEN)."', '".htmlentities($libelleIT)."', '".htmlentities($libelleSP)."' )  ";
		$DB_site2->query($ajout_caract_val);
?>
<script language="javascript">
function valider() {
	window.opener.formu.saverefresh.value='1';
	window.opener.formu.submit();
	window.close();
}
valider();
</script>
<?php
	}  else {
		$action="";
		echo "<font color=red>Vous devez remplir au moins la nouvelle valeur $languedefault ...</font>";
	}
}



if (isset($caractid) and $caractid!="" and ( !isset($action) or $action=="") ) {
	$requete_caracteristique=" SELECT * FROM caracteristique WHERE caractid='$caractid' ";
	$caracteristique=$DB_site2->query_first($requete_caracteristique);
	echo"	<table width=\"400\"><form action=\"modifier_caracteristique.php\" method=\"post\">
		<tr><td><b><u>Caracteristique : $caracteristique[$libelle]</u></b></td></tr>
		<tr><td>Description : $caracteristique[description]</td></tr>
		<tr><td>Valeurs existantes : <select name=\"valeurs\" size=\"5\">";
	$requete_caracteristique=" SELECT * FROM caracteristiquevaleur WHERE caractid='$caractid' ";
	$caract_vals=$DB_site2->query($requete_caracteristique);
	while($caract_val=$DB_site2->fetch_array($caract_vals)) {
		echo "<option>$caract_val[$libelle]</option>";
	}
	echo"	</select></td></tr>";


	if(in_array("FR" , $langues)){
		echo	"<tr><td>Nouvelle valeur FR : <input type=\"text\" name=\"libelleFR\"></td></tr>";
	} else {
		echo	"<input type=\"hidden\" name=\"libelleFR\">";
	}
	if(in_array("DE" , $langues)){
		echo	"<tr><td>Nouvelle valeur DE : <input type=\"text\" name=\"libelleDE\"></td></tr>";
	} else {
		echo	"<input type=\"hidden\" name=\"libelleDE\">";
	}
	if(in_array("EN" , $langues)){
		echo	"<tr><td>Nouvelle valeur EN : <input type=\"text\" name=\"libelleEN\"></td></tr>";
	} else {
		echo	"<input type=\"hidden\" name=\"libelleEN\">";
	}
	if(in_array("IT" , $langues)){
		echo	"<tr><td>Nouvelle valeur IT : <input type=\"text\" name=\"libelleIT\"></td></tr>";
	} else {
		echo	"<input type=\"hidden\" name=\"libelleIT\">";
	}
	if(in_array("SP" , $langues)){
		echo	"<tr><td>Nouvelle valeur SP : <input type=\"text\" name=\"libelleSP\"></td></tr>";
	} else {
		echo	"<input type=\"hidden\" name=\"libelleSP\">";
	}

	echo	"<input type=\"hidden\" name=\"caractid\" value=\"$caractid\">
		<input type=\"hidden\" name=\"action\" value=\"create\">
		<tr><td align=\"center\"><input type=\"submit\" value=\"envoyer\"></td></tr>
		</form>
		</table>";
}

	  
echo "				</td>
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
