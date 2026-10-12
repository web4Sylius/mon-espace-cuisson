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
			<td height=\"25\" nowrap> &nbsp; <img src=\"images/arrow_2.jpg\"> <font class=\"cost\">Liste de mes alertes</font>
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


if (isset($action) and $action == "publier")
	{
	$DB_site2->query("update articlevote set actif = '1' where articlevoteid = '$id'") ;
	$msg = "<font class=\"cost\">Commentaire publié.</font><br><br>" ;
	$action = "" ;
	}


if (isset($action) and $action == "refuser")
	{
	$DB_site2->query("delete from articlevote where articlevoteid = '$id'") ;
	$msg = "<font class=\"cost\">Commentaire supprimé.</font><br><br>" ;
	$action = "" ;
	}




// Vérification module votes et commentaires
if (in_array ("18", $modules))
	{
	if (($comm == "visu") or ($comm == "auto"))
		{
		$modif_commentaire=$DB_site2->query("update parametresgeneraux set gestion_commentaires = '$comm'") ;
		}
	$commentaire=$DB_site2->query_first("select gestion_commentaires from parametresgeneraux") ;
	echo "<h3>Gestion des commentaires sur les atricles</h3><ul>" ;
	if ($commentaire[gestion_commentaires] == "")
		{
		echo "<li><a href=\"commentaires.php?comm=auto\">Afficher automatiquement un commentaire dès sa rédaction par un visiteur</a></li>
			 <li><a href=\"commentaires.php?comm=visu\">Visualiser les commentaires des visiteurs et choisir de les publier (ou non)</a></li>" ;
		}
	elseif ($commentaire[gestion_commentaires] == "auto")
		{
		echo "<li>Afficher automatiquement un commentaire dès sa rédaction par un visiteur</li>
			 <li><a href=\"commentaires.php?comm=visu\">Visualiser les commentaires des visiteurs et choisir de les publier (ou non)</a></li>" ;
		}
	elseif ($commentaire[gestion_commentaires] == "visu")
		{
		echo "<li><a href=\"commentaires.php?comm=auto\">Afficher automatiquement un commentaire dès sa rédaction par un visiteur</a></li>
			 <li>Visualiser les commentaires des visiteurs et choisir de les publier (ou non)</li>" ;
		}
	echo "</ul>";
	if ($commentaire[gestion_commentaires] == "visu")
		{
		echo "<br><h1>NOUVEAUX COMMENTAIRES POSTES SUR LES ARTICLES</h1><br>$msg" ;
		$new_commentaires=$DB_site2->query("select * from articlevote av, article a where av.artid = a.artid and (actif = '0' or actif = '')
											order by libelleFR asc, note desc") ;
		if ($DB_site2->num_rows ($new_commentaires) > 0)
			{
			echo "<table class=\"table_promos\"><tr>
			<td class=\"td_head_promos\" width=\"25\"></td>
			<td class=\"td_head_promos\" width=\"70\">Date<br>Note</td>
			<td class=\"td_head_promos\">Titre</td>
			<td class=\"td_head_promos\">Contenu</td>
			<td class=\"td_head_promos\">Action</td></tr>" ;
			while ($new_commentaire=$DB_site2->fetch_array($new_commentaires)) 
				{
				$rowalt="td_factures_".getrowbg();
				$madate = explode("-", $new_commentaire[datevote]) ;
				$note = "" ;
				$i = 0 ;
				while ($i < $new_commentaire[note]) 
					{
					$i++ ;
					$note .= "<img src=\"images/etoile.gif\">" ;
					}
				echo "<tr> 
				<td class=\"$rowalt\" align=\"center\" valign=\"top\"><a href=\"http://$domaine_info[domaine]/art,$new_commentaire[artid].htm\" target=\"_blank\" onMouseOver=\"poplink('Voir $new_commentaire[libelleFR] dans la boutique');\" onmouseout=\"killlink()\"><img src=\"images/voir.gif\"></a></td>
				<td class=\"$rowalt\" align=\"center\" valign=\"top\">$madate[2]/$madate[1]/$madate[0]<br>$note</td>
				<td class=\"$rowalt\" align=\"center\"><textarea readonly cols=\"15\" rows=\"3\">$new_commentaire[titre]</textarea></td>
				<td class=\"$rowalt\" align=\"center\"><textarea readonly cols=\"20\" rows=\"3\">$new_commentaire[contenu]</textarea></td>
				<td class=\"$rowalt\" align=\"center\" valign=\"top\">
					<a href=\"commentaires.php?action=publier&id=$new_commentaire[articlevoteid]\">Publier</a><br>
					<a href=\"commentaires.php?action=refuser&id=$new_commentaire[articlevoteid]\">Refuser</a>
				</td></tr>" ;
				}
			echo "</table>" ;
			}
		else
			{
			echo "Il n'y a pas de nouveaux commentaires.<br>" ;
			}
		}
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