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
			<td height=\"25\" nowrap> &nbsp; <img src=\"images/arrow_2.jpg\"> <font class=\"cost\">Remises sur le catalogue</font>
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

if (in_array ("106", $modules))
	{
	if ($action == "ajoutgroupe")
		{
		if ($action2 == "ajt_gp")
			{
			$ok = 0 ;
			if ($nom == "")
				{
				echo "<font class=\"cost\">Vous devez saisir un nom pour ce groupe d'utilisateurs.</font>" ;
				}
			else
				{
				if ($pct != "")
					{
					 if (!is_numeric ($pct))
						{
						echo "<font class=\"cost\">Le pourcentage doit être numérique.</font>" ;
						}
					else
						{
						$DB_site2->query("insert into groupe_utilisateur (nom, pctremise) values ('$nom', '$pct')");
						$ok = 1 ;
						}
					}
				else
					{
					$DB_site2->query("insert into groupe_utilisateur (nom) values ('$nom')");
					$ok = 1 ;
					}
				}
			if ($ok == 1)
				{
				echo "<script language=\"javascript\">location.href=\"groupe_user.php\";</script>";
				}
			}
		echo "<h3>Ajout d'un groupe d'utilisateurs</h3>
		<form method=\"post\" action=\"groupe_user.php\">
		<input type=\"hidden\" name=\"action\" value=\"ajoutgroupe\">
		<input type=\"hidden\" name=\"action2\" value=\"ajt_gp\">
		<table width=\"100%\">
			<tr>
				<td align=\"right\">Nom : </td>
				<td align=\"left\"><input type=\"text\" name=\"nom\" value=\"$nom\"size=\"35\" ></td>
			</tr>
			<tr>
				<td align=\"right\">Pourcentage : </td>
				<td align=\"left\"><input type=\"text\" name=\"pct\" value=\"$pct\" size=\"1\" maxlength=\"2\"> %</td>
			</tr>
		</table>
		<br><input type=\"submit\" value=\"Ajouter\">
		</form>
		<br><a href=\"groupe_user.php\">Retour</a><br>";
		}
	elseif ($action == "ajoutuser")
		{
		if ($action2 == "ajoutuserok")
			{
			$ok = 0 ;
			 if (!is_numeric ($pct))
				{
				echo "<font class=\"cost\">Le pourcentage doit être numérique.</font>" ;
				}
			else
				{
				$DB_site2->query("update utilisateur set pctremise = '$pct', groupeid = '0' where userid = '$user'");
				echo "<script language=\"javascript\">location.href=\"groupe_user.php\";</script>";
				}
			}
		$utils=$DB_site2->query("SELECT * FROM utilisateur order by prenom, nom");
		while ($util=$DB_site2->fetch_array($utils))
			{
			$select_users .= "<option value=\"$util[userid]\">$util[prenom] $util[nom] ($util[mail])</option>" ;
			}
		echo "<h3>Ajout d'un groupe d'utilisateurs</h3>
		<form method=\"post\" action=\"groupe_user.php\">
		<input type=\"hidden\" name=\"action\" value=\"ajoutuser\">
		<input type=\"hidden\" name=\"action2\" value=\"ajoutuserok\">
		<table class=\"table_caracts\">
			<tr>
				<td align=\"right\">Nom : </td>
				<td align=\"left\"><select name=\"user\"><option value=\"0\"></option>$select_users</select></td>
			</tr>
			<tr>
				<td align=\"right\">Pourcentage : </td>
				<td align=\"left\"><input type=\"text\" name=\"pct\" value=\"$pct\" size=\"1\" maxlength=\"2\"> %</td>
			</tr>
		</table>
		<br><input type=\"submit\" value=\"Ajouter\">
		</form>
		<br><a href=\"groupe_user.php\">Retour</a><br>";
		}
	elseif ($action == "enleveruser")
		{
		echo "Etes-vous sûr(e) de vouloir enlever cet utilisateur du groupe ?<br><br>
		<a href=\"groupe_user.php?action=enleveruser2&userid=$userid&gp=$gp\">OUI</a> | <a href=\"groupe_user.php?action=modifiergp&gp=$gp\">NON</a>" ;
		}
	elseif ($action == "ajt_user_gp")
		{
		if ($action2 == "ajt_user_gp2")
			{
			if ($user != 0)
				{
				$DB_site2->query("update utilisateur set groupeid = '$gp', pctremise = '0' where userid = '$user'");
				echo "<script language=\"javascript\">location.href=\"groupe_user.php?action=modifiergp&gp=$gp\";</script>";
				}
			}
		$utils=$DB_site2->query("SELECT * FROM utilisateur order by prenom, nom");
		while ($util=$DB_site2->fetch_array($utils))
			{
			$select_users .= "<option value=\"$util[userid]\">$util[prenom] $util[nom] ($util[mail])</option>" ;
			}
		$gp=$DB_site2->query_first("SELECT * FROM groupe_utilisateur where groupeid = '$gp'");
		echo "<h3>Ajouter un utilisateur pour le groupe $gp[nom]</h3>
		<form method=\"post\" action=\"groupe_user.php\">
		<input type=\"hidden\" name=\"action2\" value=\"ajt_user_gp2\">
		<input type=\"hidden\" name=\"action\" value=\"ajt_user_gp\">
		<input type=\"hidden\" name=\"gp\" value=\"$gp[groupeid]\">
		<table class=\"table_moyen_paiement\">
			<tr>
				<td align=\"right\">Nom : </td>
				<td align=\"left\"><select name=\"user\"><option value=\"0\"></option>$select_users</select></td>
			</tr>
		</table>
		<br><input type=\"submit\" value=\"Ajouter\">
		</form>" ;
		echo "<br><a href=\"groupe_user.php\">Retour</a><br>";
		}
	elseif ($action == "enleveruser2")
		{
		$DB_site2->query("update utilisateur set groupeid = '0' where userid = '$userid'");
		echo "<script language=\"javascript\">location.href=\"groupe_user.php?action=modifiergp&gp=$gp\";</script>";
		}
	elseif ($action == "modifiergp")
		{
		if ($action2 == "modifiergpok")
			{
			$ok = 0 ;
			if ($nom == "")
				{
				echo "<font class=\"cost\">Vous devez saisir un nom pour ce groupe d'utilisateurs.</font>" ;
				}
			else
				{
				if ($pct != "")
					{
					 if (!is_numeric ($pct))
						{
						echo "<font class=\"cost\">Le pourcentage doit être numérique.</font>" ;
						}
					else
						{
						$DB_site2->query("update groupe_utilisateur set nom = '$nom', pctremise = '$pct' where groupeid = '$gp'");
						$ok = 1 ;
						}
					}
				else
					{
					$DB_site2->query("update groupe_utilisateur set nom = '$nom' where groupeid = '$gp'");
					$ok = 1 ;
					}
				}
			if ($ok == 1)
				{
				echo "<script language=\"javascript\">location.href=\"groupe_user.php\";</script>";
				}
			}
		$gp=$DB_site2->query_first("SELECT * FROM groupe_utilisateur where groupeid = '$gp'");
		echo "<h3>Modifier un groupe d'utilisateurs</h3>
		<form method=\"post\" action=\"groupe_user.php\">
		<input type=\"hidden\" name=\"action\" value=\"modifiergp\">
		<input type=\"hidden\" name=\"action2\" value=\"modifiergpok\">
		<input type=\"hidden\" name=\"gp\" value=\"$gp[groupeid]\">
		<table width=\"100%\">
			<tr>
				<td align=\"right\">Nom : </td>
				<td align=\"left\"><input type=\"text\" name=\"nom\" value=\"$gp[nom]\"size=\"35\" ></td>
			</tr>
			<tr>
				<td align=\"right\">Pourcentage : </td>
				<td align=\"left\"><input type=\"text\" name=\"pct\" value=\"$gp[pctremise]\" size=\"1\" maxlength=\"2\"> %</td>
			</tr>
		</table>
		<input type=\"submit\" value=\"Modifier\">
		</form><br><br>
		<b>Utilisateurs</b> <a href=\"groupe_user.php?action=ajt_user_gp&gp=$gp[groupeid]\">Ajouter</a><br><br>
		<table class=\"table_moyen_paiement\"><tr><td class=\"td_head_comparateurs\">Utilisateur</td><td class=\"td_head_comparateurs\">ACTION</td></tr>" ;
		$utilisateurs_groupe=$DB_site2->query("SELECT * FROM utilisateur where groupeid = '$gp[groupeid]'");
		while ($utilisateur_groupe=$DB_site2->fetch_array($utilisateurs_groupe))
			{
			//td_comparateurs1
			$rowalt = "td_promos_".getrowbg();
			echo "<tr><td class=\"$rowalt\">$utilisateur_groupe[prenom] $utilisateur_groupe[nom]</td><td class=\"$rowalt\" align=\"center\"><a href=\"groupe_user.php?action=enleveruser&userid=$utilisateur_groupe[userid]&gp=$gp[groupeid]\">Enlever du groupe</a></td></tr>" ;
			}
		echo "</table>" ;
		echo "<br><a href=\"groupe_user.php\">Retour</a><br>";
		}
	elseif ($action == "supprimergp")
		{
		echo "Etes-vous sûr(e) de vouloir supprimer ce groupe ?<bR><br>
		<a href=\"groupe_user.php?action=supprimergpok&gp=$gp\">OUI</a> | <a href=\"groupe_user.php\">NON</a>" ;
		}
	elseif ($action == "supprimergpok")
		{
		$DB_site2->query("delete from groupe_utilisateur where groupeid = '$gp'");
		echo "<script language=\"javascript\">location.href=\"groupe_user.php\";</script>";
		}
	elseif ($action == "modifier_user")
		{
		if ($action2 == "modifier_userok")
			{
			if ($pct_user == "")
				{
				echo "<font class=\"cost\">Vous devez entrer une remise pour l'utilisateur.<br>Si vous souhaitez lui enlever, saisissez 0.</font>" ;
				}
			else
				{
				$DB_site2->query("update utilisateur set pctremise = '$pct_user' where userid = '$user'" );
				echo "<script language=\"javascript\">location.href=\"groupe_user.php\";</script>";
				}
			}
		$user=$DB_site2->query_first("SELECT * FROM utilisateur where userid = '$user'" );
		echo "<h3>Modifier le pourcentage pour un utilisateur</h3>
		<form method=\"post\" action=\"groupe_user.php\">
		<input type=\"hidden\" name=\"action2\" value=\"modifier_userok\">
		<input type=\"hidden\" name=\"action\" value=\"modifier_user\">
		<input type=\"hidden\" name=\"user\" value=\"$user[userid]\">
		<table class=\"table_moyen_paiement\">
			<tr>
				<td align=\"right\">Nom : </td>
				<td align=\"left\">$user[prenom] $user[nom]</td>
			</tr>
			<tr>
				<td align=\"right\">Pourcentage : </td>
				<td align=\"left\"><input type=\"text\" name=\"pct_user\" value=\"$user[pctremise]\" size=\"1\" maxlength=\"2\"> %</td>
			</tr>
		</table>
		<br><input type=\"submit\" value=\"Modifier\">
		</form>
		<br><a href=\"groupe_user.php\">Retour</a>" ;
		}
		
		
	if (!isset($action) or $action == "")
		{	
		echo "<h3>Gestion des remises ciblées</h3>
		<br>
		<table width=\"100%\" align=\"center\">
			<tr>
				<td width=\"100\" align=\"left\" valign=\"top\"><br><strong>Groupes</strong><br><br></td>
				<td><div id=\"groupes\">" ;
					echo "<br>Groupes et réductions 
				<a href=\"groupe_user.php?action=ajoutgroupe\">Ajouter un groupe</a><bR><br>
					<table class=\"table_modules_boutiques\">" ;
					$gps=$DB_site2->query("SELECT * FROM groupe_utilisateur order by nom" );
					echo "<tr>
							<td align=\"center\"><strong>Nom</strong></td>
							<td align=\"center\"><strong>Remise</strong></td>
							<td align=\"center\">&nbsp;</td>
						</tr>" ;
					while ($gp=$DB_site2->fetch_array($gps))
						{
						$rowalt = "td_promos_".getrowbg();
						if ($gp[pctremise] == 0)
							{
							$gp[pctremise] = "Aucune" ;
							}
						else
							{
							$gp[pctremise] = "$gp[pctremise] %" ;
							}
						echo "<tr>
								<td align=\"left\" class=\"$rowalt\">$gp[nom]</td>
								<td align=\"center\" class=\"$rowalt\">$gp[pctremise]</td>
								<td align=\"center\" class=\"$rowalt\"><a href=\"groupe_user.php?action=modifiergp&gp=$gp[groupeid]\">Modifier</a></td>
							</tr>" ;
						$users=$DB_site2->query("SELECT * FROM utilisateur where groupeid = '$gp[groupeid]' order by nom" );
						while ($user=$DB_site2->fetch_array($users))
							{
							echo "<tr>
									<td align=\"left\"><li>$user[nom] $user[prenom]</li></td>
									<td align=\"center\" colspan=\"2\"><a href=\"groupe_user.php?action=enleveruser&userid=$user[userid]&gp=$gp[groupeid]\">Sortir</a></td>
									</tr>" ;
							}
						}
					echo "</table>" ;
				echo "</div></td>
			</tr>
			<tr>
				<td width=\"100\" align=\"left\" valign=\"top\"><br><strong>Utilisateurs</strong><br><br></td>
				<td><div id=\"utilisateurs\">" ;
					if (!isset($pagenumber) or $pagenumber=="") 
						{
						$pagenumber="1";
						}
					$perpage="30";
					$limitlower=($pagenumber-1)*$perpage;
					$limitupper=($pagenumber)*$perpage;
					$utilisateurcount = $DB_site2->query_first( "SELECT count(*) FROM utilisateur" );
					$pagenav=getpagenav($utilisateurcount[0],"utilisateur.php?");
					echo $pagenav ;
					echo "<br>Utilisateurs et réductions 
				<a href=\"groupe_user.php?action=ajoutuser\">Ajouter une reduction perso</a><br><br>
					<table class=\"table_modules_boutiques\">";
					$rq_client = $DB_site2->query("SELECT * FROM utilisateur where pctremise != 0 ORDER BY nom LIMIT $limitlower , $perpage " );
					if ($DB_site2->num_rows($rq_client) > 0)
						{
						echo "<tr>
							   <td align=\"center\"><b>Nom</b></td>
							   <td align=\"center\"><b>Remise</b></td>
							   <td align=\"center\"><b></b></td>			   
							  </tr>";
						while ($rs_client=$DB_site2->fetch_array($rq_client))
							{
							$rowalt = "td_promos_".getrowbg();
							list($annee, $mois, $jour) = explode( "-", $rs_client[dateinscription] );
							echo "<tr class=\"td_promos\">";
							echo "  <td class=\"$rowalt\" align=\"left\">$rs_client[nom] $rs_client[prenom]</td>";
							echo "  <td class=\"$rowalt\" align=\"center\">$rs_client[pctremise] %</td>";
							echo "  <td class=\"$rowalt\" align=\"center\"><a href=\"groupe_user.php?action=modifier_user&user=$rs_client[userid]\">Modifier</a></td>";
							echo "</tr>";
							}
						}
					echo "</table>";
					echo $pagenav ;
				echo "</div></td>
			</tr>
		</table><bR><br><br>" ;

		/*
				echo "<h3>Pourcentage pour un groupe d'utilisateurs</h3>
				<table class=\"table_modif_categ\">" ;
				$gps=$DB_site2->query("SELECT * FROM groupe_utilisateur order by nom" );
				if ($DB_site2->num_rows($gps) > 0)
					{
					echo "<tr><td align=\"center\"><strong>Nom</strong></td><td align=\"center\"><strong>Remise sur catalogue</strong></td><td align=\"center\"><strong>ACTIONS</strong></td></tr>" ;
					while ($gp=$DB_site2->fetch_array($gps))
						{
						if ($gp[pctremise] == 0)
							{
							$gp[pctremise] = "Aucune" ;
							}
						else
							{
							$gp[pctremise] = "$gp[pctremise] %" ;
							}
						echo "<tr>
								<td align=\"left\">$gp[nom]</td><td align=\"center\">$gp[pctremise]</td><td align=\"center\"><a href=\"groupe_user.php?action=modifiergp&gp=$gp[groupeid]\">Modifier</a> <a href=\"groupe_user.php?action=supprimergp&gp=$gp[groupeid]\">Supprimer</a></td></tr>" ;
						}
					}
				else
					{
					echo "<tr><td align=\"center\">Aucun groupe d'utilisateurs.</td></tr>" ;
					}
				echo "</table>
				<br><br>
				<bR><br><h3>Pourcentage pour un utilisateur</h3>
				<table class=\"table_modif_categ\">" ;
				$users=$DB_site2->query("SELECT * FROM utilisateur where pctremise !=  0 order by nom" );
				if ($DB_site2->num_rows($users) > 0)
					{
					echo "<tr><td align=\"center\"><strong>Nom</strong></td><td align=\"center\"><strong>Remise sur catalogue</strong></td><td align=\"center\"><strong>ACTIONS</strong></td></tr>" ;
					while ($user=$DB_site2->fetch_array($users))
						{
						echo "<tr>
								<td align=\"left\">$user[prenom] $user[nom]</td>
								<td align=\"center\">$user[pctremise] %</td><td align=\"center\">
								<a href=\"groupe_user.php?action=modifier_user&user=$user[userid]\">Modifier</a></td></tr>" ;
						}
					}
				else
					{
					echo "<tr><td align=\"center\">Aucun utilisateur.</td></tr>" ;
					}
				echo "</table>
				<br><a href=\"groupe_user.php?action=ajoutuser\">Ajouter un pourcentage pour un utilisateur.</a><br>";
		*/
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