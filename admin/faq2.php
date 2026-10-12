<?php

require "admin_global.php";
include "./header.php";
include "./menu.php";

echo "<td valign=\"top\">
<table width=\"100%\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\"><tr><td>
<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\">
  <tr>
   <td>
   	<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\">
		<tr>
			<td height=\"25\" nowrap> &nbsp; <img src=\"images/arrow_2.jpg\"> <font class=\"cost\">Support</font>
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



// MONTER D'UNE POSITION 
if (isset ($action) and $action == "up")
	{
	$position=$DB_site2->query_first("SELECT position FROM faq WHERE faqid = '$faqid'");						
	$nouvelle_position_faq = $position[0] - 1 ;
	$position2=$DB_site2->query_first("SELECT faqid FROM faq WHERE position = '$nouvelle_position_faq'");
	$DB_site2->query("UPDATE faq SET position = '$nouvelle_position_faq' WHERE faqid = '$faqid'");
	$DB_site2->query("UPDATE faq SET position = '$position[0]' WHERE faqid = '$position2[faqid]'");
	echo "<script language=\"javascript\">location.href=\"faq2.php\";</script>";
	}

// DESCENDRE D'UNE POSITION 
if (isset ($action) and $action == "down")
	{
	$position=$DB_site2->query_first("SELECT position FROM faq WHERE faqid = '$faqid'");						
	$nouvelle_position_faq = $position[0] + 1 ;
	$position2=$DB_site2->query_first("SELECT faqid FROM faq WHERE position = '$nouvelle_position_faq'" );
	$DB_site2->query("UPDATE faq SET position = '$nouvelle_position_faq' WHERE faqid = '$faqid'");
	$DB_site2->query("UPDATE faq SET position = '$position[0]' WHERE faqid = '$position2[faqid]'");
	echo "<script language=\"javascript\">location.href=\"faq2.php\";</script>";
	}


if (isset($action) and $action == "supprimer")
	{
	echo "Etes-vous sûr(e) de vouloir supprimer cette question ?<br><br>
	<a href=\"faq2.php?action=supprok&quest=$quest\">OUI</a> | <a href=\"faq2.php\">NON</a>" ;
	}

if (isset($action) and $action == "supprok")
	{
	$position_page=$DB_site2->query_first("SELECT position FROM faq WHERE faqid = '$quest'");
	$position_page = $position_page[0];
	$rq_positions_suivantes = $DB_site2->query("SELECT faqid, position FROM faq WHERE position > '$position_page'" );
	while ($rs_positions_suivantes=$DB_site2->fetch_array($rq_positions_suivantes)) 
		{
		$position_temp = $rs_positions_suivantes[position] - 1;
		$DB_site2->query("UPDATE faq SET position = '$position_temp' WHERE faqid = '$rs_positions_suivantes[faqid]'");
		}
	$del=$DB_site2->query("delete from faq where faqid = '$quest'");
	$action = "" ;
	}

if (isset($action) and $action == "new")
	{
	if (isset($action2) and $action2 == "ok")
		{
		$pos=$DB_site2->query_first("select max(position) from faq");
		if ($pos[0] == 0)
			{
			$pos = 1 ;
			}
		else
			{
			$pos = $pos[0] + 1 ;
			}
		$DB_site2->query("insert into faq (question, reponse, position) values ('$question', '$reponse', '$pos')");
		$action = "" ;
		}
	else
		{
		echo "<h3>Question / réponse</h3>
		<form method=\"post\" action=\"faq2.php?action=new\">
		<input type=\"hidden\" name=\"action2\" value=\"ok\">
		<table width=\"100%\" align=\"center\">";
		echo "
		<tr>
		<td align=\"right\" valign=\"top\"><b>Question : </b></td><td><textarea cols=\"50\" rows=\"4\" name=\"question\">$question</textarea></td>
		</tr>
		<tr>
		<td align=\"right\" valign=\"top\"><b>Réponse : </b></td><td><textarea cols=\"50\" rows=\"13\" name=\"reponse\">$reponse</textarea></td>
		</tr>
		</table>
		<input type=\"submit\" value=\"OK\">
		</form>
		<br><br><a href=\"faq2.php\">Retour</a>";
		}
	}


if (isset($action) and $action == "visu")
	{
	if (isset($action2) and $action2 == "repondu")
		{
		$reponse=$DB_site2->query("update faq set reponse = '$reponse', question = '$question' where faqid = '$quest'");
		}
	$question=$DB_site2->query_first("SELECT * FROM faq where faqid = '$quest'");
	echo "<h3>Question / réponse</h3>
	<form method=\"post\" action=\"faq2.php?action=visu&quest=$question[faqid]\">
	<input type=\"hidden\" name=\"action2\" value=\"repondu\">
	<table width=\"100%\" align=\"center\">";
	echo "
	<tr>
	<td align=\"right\" valign=\"top\"><b>Question : </b></td><td><textarea cols=\"50\" rows=\"4\" name=\"question\">$question[question]</textarea></td>
	</tr>
	<tr>
	<td align=\"right\" valign=\"top\"><b>Réponse : </b></td><td><textarea cols=\"50\" rows=\"13\" name=\"reponse\">$question[reponse]</textarea></td>
	</tr>
	</table>
	<input type=\"submit\" value=\"OK\">
	</form>
	<br><br><a href=\"faq2.php\">Retour</a>";
	}



if (!isset($action) or $action == "")
	{
	echo "<h3>Questions / Réponses</h3>
	<a href=\"faq2.php?action=new\">Ajouter</a>
	<br><br>
	<table class=\"table_modules_boutiques\">";
	$questions=$DB_site2->query("SELECT * FROM faq order by position asc");
	if ($DB_site2->num_rows($questions) > 0)
		{
		echo "<tr>
			   <td align=\"center\">Position</td>
			   <td align=\"center\"><b>Question</b></td>
			   <td align=\"center\"><b>Réponse</b></td>
			   <td align=\"center\"><b>ACTIONS</b></td>
			  </tr>";
		$nb_faq=$DB_site->num_rows($questions) ;
		$i_faq = 0 ;
		while ($question=$DB_site2->fetch_array($questions))
			{
			$rowalt = "td_promos_".getrowbg();
			$i_faq++ ;
			if ($nb_faq > 1)
				{
				if ($i_faq == 1) // Première marque
					{
					$images_position = "<img src=\"images/vide.gif\" height=\"7\">
										<a href=\"faq2.php?action=down&faqid=$question[faqid]\">
										<img src=\"images/descendre.gif\" alt=\"Descendre\" title=\"Descendre\" height=\"7\" border=\"0\"></a>";
					}
				elseif ($i_faq == $nb_faq) // Dernière marque
					{
					$images_position = "<a href=\"faq2.php?action=up&faqid=$question[faqid]\">
										<img src=\"images/monter.gif\" alt=\"Monter\" title=\"Monter\" height=\"7\" border=\"0\"></a>
										<img src=\"images/vide.gif\" height=\"7\">";
					}
				else
					{
					$images_position = "<a href=\"faq2.php?action=up&faqid=$question[faqid]\">
										<img src=\"images/monter.gif\" alt=\"Monter\" title=\"Monter\" height=\"7\" border=\"0\"></a>
										<a href=\"faq2.php?action=down&faqid=$question[faqid]\">
										<img src=\"images/descendre.gif\" alt=\"Descendre\" title=\"Descendre\" height=\"7\" border=\"0\"></a>";
					}
				}
			echo "<tr class=\"td_promos\">
					<td class=\"$rowalt\">$images_position</td>
					<td class=\"$rowalt\">$question[question]</td>
					<td class=\"$rowalt\" align=\"center\">$question[reponse]</td>
					<td class=\"$rowalt\" align=\"center\">
						<a href=\"faq2.php?action=visu&quest=$question[faqid]\">Modifier</a>&nbsp;
						<a href=\"faq2.php?action=supprimer&quest=$question[faqid]\">Supprimer</a>
						</td>
				</tr>";
			}
		echo "</table>";
		echo $pagenav ;
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
        </tr>";

include "./footer.php";
?>