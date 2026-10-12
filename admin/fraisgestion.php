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
			<td height=\"25\" nowrap> &nbsp; <img src=\"images/arrow_2.jpg\"> <font class=\"cost\">Frais de gestion</font>
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
				<td align=\"center\" valign=\"middle\" width=\"100%\">
" ;


if (isset($action) and $action=="supprimer") 
	{
	$articles_concernes=$DB_site2->query("select * from article where active = '1' and fraisgestionid = '$_POST[supprimer_fraisgestionid]'");
	if ($DB_site2->num_rows($articles_concernes) > 0)
		{
		echo "<b>Vous ne pouvez pas supprimer ce frais de gestion</b> pour l'instant : <br>un ou plusieurs articles actifs sont concernés.
		<br>Vous devez modifier ces articles au préalable si vous souhaitez supprimer ce frais.<br><br>" ;
		$action = "" ;
		}
	else
		{
		echo "<form name=\"formu\" method=\"post\" action=\"fraisgestion.php\">";
			$requete="delete from fraisgestion where fraisgestionid=$_POST[supprimer_fraisgestionid]";
			$paysid=$DB_site2->query($requete);
			echo"<script language=\"javascript\">document.formu.submit();</script>";
		echo "</form>";
		}
	}

if (isset($action) and $action=="ajouter") 
	{
	echo "<h3>Nouveau frais de gestion</h3>
		<form method=\"post\" action=\"fraisgestion.php\">
			<input type=\"hidden\" name=\"action\" value=\"doajouter\">
			<table class=\"table_remises\">" ;
				for ($i=0;$i<count($langues);$i++)
					{
					echo "<tr>
							<td class=\"td_remises1\" width=\"50%\">Libellé $langues[$i] : </td>
							<td class=\"td_remises2\"><input type=\"text\" size=\"15\" name=\"libelle$langues[$i]\"></td>
						</tr>";
					}
				echo "<tr>
						<td class=\"td_remises1\">Prix : </td>
						<td class=\"td_remises2\"><input type=\"text\" name=\"prix\" size=\"4\"></td>
					</tr>
			</table>
			<br><input type=\"submit\" value=\"Ajouter\">&nbsp;&nbsp;<input type=\"button\" value=\"Annuler\" onclick=\"javascript:window.location='fraisgestion.php';\">
		</form>";
	}
	
if (isset($action) and $action=="doajouter") 
	{
	echo "<form name=\"formu\" method=\"post\" action=\"fraisgestion.php\">";
			$requete="insert into fraisgestion (libelleFR,libelleDE,libelleSP,libelleIT,libelleEN,prix) values ('$_POST[libelleFR]','$_POST[libelleDE]','$_POST[libelleSP]','$_POST[libelleIT]','$_POST[libelleEN]',".floatval($_POST[prix]).")";
			$DB_site2->query($requete);
	echo"<script language=\"javascript\">document.formu.submit();</script>";
	echo "</form>";
	}

if (!isset($action) or $action=="") 
	{
echo "<h3>Configuration des frais de gestion</h3>";
	echo "<form name=\"formu\" method=\"post\" action=\"fraisgestion.php\">";
		echo "<input type=\"hidden\" name=\"action\">";
		echo "<table class=\"table_caracts\">
				<tr>
					<td class=\"td_frais_gestion\"><strong>Libellé $langue</strong></td>
					<td class=\"td_frais_gestion\"><strong>Frais</strong></td>
					<td class=\"td_frais_gestion\"><strong>ACTION</strong></td>
				</tr>";
					$frais=$DB_site2->query("select * from fraisgestion");
					$i=0;
					while ($frai=$DB_site2->fetch_array($frais)) 
						{
						$i++;
						echo "<tr>
								<td class=\"td_frais_gestion\">".$frai["libelle".$langue]."</td>
								<td class=\"td_frais_gestion\">$frai[prix]€</td>
								<td class=\"td_frais_gestion\"><input type=\"button\" class=\"bouton1\" onclick=\"supprimer($frai[fraisgestionid])\" value=\"Supprimer\"></td>
							</tr>";
						}
					if ($i==0)
						{
						echo "<tr>
								<td colspan=\"3\"><font face=\"verdana\" size=\"2\"><b>Aucun frais de gestion n'a été défini sur votre boutique</b></font></td>
							</tr>";
						}
		echo "</table><br>
				<input type=\"hidden\" name=\"supprimer_fraisgestionid\">
				<div align=\"center\"><a href=\"fraisgestion.php?action=ajouter\">Ajouter un frais de gestion</a></div>
			 ";
	echo "</form>
	<br>
	<div align=\"left\">Certains de vos articles vous demandent plus de temps que d'autres ? Facturez ce coût !<br>
	Créez les différents frais existants (frais de dossier...) et vous pourrez les ajouter aux articles que vous souhaitez.</div>";

?>
<script language="javascript">
function supprimer(fraisgestionid){
	document.formu.action.value="supprimer";
	document.formu.supprimer_fraisgestionid.value=fraisgestionid;
	document.formu.submit();
}
</script>
<?php
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
