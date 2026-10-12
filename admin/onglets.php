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
			<td height=\"25\" nowrap> &nbsp; <img src=\"images/arrow_2.jpg\"> <font class=\"cost\">Gestion des onglets</font>
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

//*****************AFFICHAGE CHOIX ONGLETS***************************
if (isset($action) and ($action == "Activer" or $action == "Désactiver"))
	{
	switch ($action) 
		{
			case "Activer":
			  $DB_site2->query("UPDATE parametresgeneraux set onglets_actifs = '1'");
			   break;
			case "Désactiver":
			  $DB_site2->query("UPDATE parametresgeneraux set onglets_actifs = '0'");
			   break;
		}
	$action = "" ;
	}
$params=$DB_site2->query_first("SELECT * FROM parametresgeneraux");
if ($params[onglets_activables] == 1)
	{
	switch ($params[onglets_actifs]) 
		{
			case 0:
			   $nom = "Activer" ;
			   break;
			case 1:
			   $nom = "Désactiver" ;
			   break;
		}
	echo "
	<h3>Gestion des onglets</h3>
	<table><tr><td>Vous pouvez choisir de mettre en avant certains rayons sur votre boutique.<br>
	Pour cela, vous devez cocher les rayons souhaités dans la liste ci-dessous et ils apparaîtront instantanément sous forme d'onglets 
	sur toutes les pages de votre boutique.</td></tr></table>";
	}


				
$params=$DB_site2->query_first("SELECT * FROM parametresgeneraux");
if ($params[onglets_activables] == 1 and $params[onglets_actifs] == 1)
	{
	echo "<br>Pour des raisons d'affichage, le nombre de rayons que vous pouvez sélectionner est limité à <strong>$params[nb_onglets_max]</strong>.<br>" ;
	if (isset ($action) and $action == "ajouter")
		{
		$nb_choisies = count ($chk) ;
		if ($nb_choisies > $params[nb_onglets_max])
			{
			echo "<font color=\"red\">Vous avez choisi trop de rayons, vous êtes limité(e) à $params[nb_onglets_max].</font><br><br>" ;
			$action = "" ;
			$action_prec = "ajouter" ;
			}
		else
			{
			if ($nb_choisies > 0)
				{
				$liste_keys = "" ;
				while (list($key, $val) = each($chk))
					{
					$DB_site2->query("UPDATE categorie SET onglet = '1' where catid = '$key'");
					$liste_keys .= "$key," ;
					}
				$liste_keys = substr ($liste_keys, 0, -1) ;
				$DB_site2->query("UPDATE categorie SET onglet = '0' where catid NOT IN ($liste_keys)");
				}
			else
				{
				$DB_site2->query("UPDATE categorie SET onglet = '0'");
				$chk = array() ;
				}
			echo "<font color=\"red\">Modification effectuée.</font><br><br>" ;
			$action = "" ;
			$action_prec = "ajouter" ;
			}
		}
	if (isset ($action) and $action == "modif_max")
		{
		if ($nb_cat_poss > 0)
			{
			$DB_site2->query("UPDATE parametresgeneraux SET nb_onglets_max = '$nb_cat_poss'");
			$params=$DB_site2->query_first("SELECT * FROM parametresgeneraux");
			}
		else
			{
			echo "<font color=\"red\">VOUS DEVEZ SAISIR UN NOMBRE MAXIMUM.</font><br><br>" ;
			}
		$action = "" ;
		}
	if (!isset ($action) or $action == "")
		{
		echo "<br><br><table class=\"table_modules_boutiques\">
				<tr>
					<td align=\"center\"><br>
						<form method=\"post\" action=\"onglets.php\">
							<input type=\"hidden\" name=\"action\" value=\"ajouter\">
							<table width=\"100%\"><tr>" ;
					$categs_onglets=$DB_site2->query("select distinct(c.$libelle), c.catid, c.onglet from categorie c order by c.position asc") ;
								$nb_categs = $DB_site2->num_rows($categs_onglets) ;
								$lignes = ceil($nb_categs / 3) ;
								$nb = 0 ;
								while ($categ_onglet=$DB_site2->fetch_array($categs_onglets))
									{
									$nb++ ;
									$chkd = "" ;
									if ($action_prec == "ajouter")
										{
										if (array_key_exists($categ_onglet[catid], $chk))
											{
											$chkd = "checked" ;
											}
										}
									else
										{
										if ($categ_onglet[onglet] == '1')
											{
											$chkd = "checked" ;
											}
										}
									echo "<td align=\"center\"><input type=\"checkbox\" name=\"chk[$categ_onglet[catid]]\" $chkd></td>
										<td align=\"left\">$categ_onglet[$libelle]</td>" ;
									if ($nb == 3)
										{
										echo "</tr><tr>" ;
										$nb = 0 ;
										}
									}
							echo "</tr>
							</table>
							<br>
							<input type=\"submit\" value=\"Valider ce choix\">
						</form>
					</td>
				</tr>
			</table><br>
				";
		}
	}
else
	{
		echo "<br><table >
			<tr>
				<td align=\"center\">
				<br>
					<font class=\"cost\">Les onglets ne sont pas actifs sur votre boutique.</font><bR><br>
				</td>
			</tr>
		</table>" ;
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
