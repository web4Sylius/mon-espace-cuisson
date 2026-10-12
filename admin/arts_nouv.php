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
			<td height=\"25\" nowrap> &nbsp; <img src=\"images/arrow_2.jpg\"> <font class=\"cost\">Choix des nouveautés</font>
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


$nbmax_nouveautes_total = 10 ;

function construire_cat($parentid)
	{
	global $DB_site2, $treeview, $host ;
	$cats=$DB_site2->query("select * from categorie where parentid = '$parentid' order by libelleFR asc");
	$treeview .= "" ;
	while($cat=$DB_site2->fetch_array($cats))
		{
		$tab_conseilles = array() ;
		$arts_conseilles=$DB_site2->query("SELECT * FROM art_nouveautes");
		while ($art_conseille=$DB_site2->fetch_array($arts_conseilles))
			{
			array_push($tab_conseilles, $art_conseille[artid]) ;
			}
		$sscats=$DB_site2->query("select * from categorie where parentid = '$cat[catid]' limit 1");
		if ($sscat=$DB_site2->fetch_array($sscats))
			{
			$treeview.="<li class=folder id=\"T$cat[catid]\">
							<a title=\"$cat[libelleFR]\" alt=\"$cat[libelleFR]\" href=\"Javascript: toggle('F$cat[catid]','T$cat[catid]');\" class=treeview>
								<font style=\"color:#5F718A;\" id=\"C$cat[catid]\">$cat[libelleFR]</font>
							</a>
						</li>
						<ul class=tree id=\"F$cat[catid]\">";
			$articles=$DB_site2->query("SELECT * FROM article WHERE catid = '$cat[catid]'");
			if ($DB_site2->num_rows($articles))
				{
				$treeview .= "<ul class=\"tree2\">" ;
				while ($article=$DB_site2->fetch_array($articles))
					{
					$article[libelleFR] = substr($article[libelleFR], 0, 50) ;
					if (in_array($article[artid], $tab_conseilles))
						{
						$treeview .= "<li class=\"art\"><input type=\"checkbox\" name=\"chk_articles[$article[artid]]\" value=\"$article[artid]\" onclick=\"javascript:changecolor('A$article[artid]');\" checked> <img height=\"100\" src=\"/configurations/$host/images/produits/categorie/$article[artid].$article[image]\" style=\"vertical-align:middle;\"> <font style=\"color:#FF0000;\" id=\"A$article[artid]\">$article[libelleFR]</font> (prix : ".number_format($article[prix], 2, ",", " ")." €)</li>" ;
						}
					else
						{
						$treeview .= "<li class=\"art\"><input type=\"checkbox\" name=\"chk_articles[$article[artid]]\" value=\"$article[artid]\" onclick=\"javascript:changecolor('A$article[artid]');\"> <img height=\"100\" src=\"/configurations/$host/images/produits/categorie/$article[artid].$article[image]\" style=\"vertical-align:middle;\"> <font id=\"A$article[artid]\">$article[libelleFR]</font> (prix : ".number_format($article[prix], 2, ",", " ")." €)</li>" ;
						}
					}
				$treeview.="</ul>" ;
				}
			construire_cat($cat[catid]);
			$treeview.="</ul>\n";
			}
		else
			{
			$treeview.="<li id=\"T$cat[catid]\"><font style=\"color:#5F718A;\" id=\"C$cat[catid]\">$cat[libelleFR]</font></li>\n";
			$articles=$DB_site2->query("SELECT * FROM article WHERE catid = '$cat[catid]'");
			if ($DB_site2->num_rows($articles))
				{
				$treeview .= "<ul class=\"tree2\">" ;
				while ($article=$DB_site2->fetch_array($articles))
					{
					$article[libelleFR] = substr($article[libelleFR], 0, 50) ;
					if (in_array($article[artid], $tab_conseilles))
						{
						$treeview .= "<li class=\"art\"><input type=\"checkbox\" name=\"chk_articles[$article[artid]]\" value=\"$article[artid]\" onclick=\"javascript:changecolor('A$article[artid]');\" checked> <img height=\"100\" src=\"/configurations/$host/images/produits/categorie/$article[artid].$article[image]\" style=\"vertical-align:middle;\"> <font style=\"color:#FF0000;\" id=\"A$article[artid]\">$article[libelleFR]</font> (prix : ".number_format($article[prix], 2, ",", " ")." €)</li>" ;
						}
					else
						{
						$treeview .= "<li class=\"art\"><input type=\"checkbox\" name=\"chk_articles[$article[artid]]\" value=\"$article[artid]\" onclick=\"javascript:changecolor('A$article[artid]');\"> <img height=\"100\" src=\"/configurations/$host/images/produits/categorie/$article[artid].$article[image]\" style=\"vertical-align:middle;\"> <font id=\"A$article[artid]\">$article[libelleFR]</font> (prix : ".number_format($article[prix], 2, ",", " ")." €)</li>" ;
						}
					}
				$treeview .= "</ul>" ;
				}
			}
		}
	}
	
function derouler($moncatid)
	{
	global $DB_site2,$treeview ;
	$parentid=$DB_site2->query_first("select parentid from categorie where catid = '$moncatid'");
	$filleid=$DB_site2->query("select catid from categorie where parentid = '$moncatid'");
	$treeview .= "toggledemarrage('F$moncatid','T$moncatid') ;\n" ;
	if ($parentid[0] != "0")
		{
		derouler($parentid[0]);
		}
	}

echo "<div class=\"erreur\">ATTENTION : <strong>vous êtes limité à 10</strong> au total.<br><bR>" ;
echo "Seulement <strong>les $nbmax_index_nouveautes premiers</strong> articles seront affichés en page d'accueil.</div><br><bR>" ;

if (isset($action) and $action == "del")
	{
	$position=$DB_site2->query_first("SELECT position FROM art_nouveautes WHERE artid = '$artid'");
	$arts=$DB_site2->query("SELECT artid, position FROM art_nouveautes WHERE position > $position[position]");
	while ($art=$DB_site2->fetch_array($arts))
		{
		$DB_site2->query("update art_nouveautes set position = position - 1 where artid = '$art[artid]'");
		}
	$DB_site2->query("delete from art_nouveautes WHERE artid = '$artid'");
	echo "<script language=\"javascript\">location.href=\"arts_nouv.php\";</script>";
	}

if (isset($action) and $action == "first")
	{
	$position=$DB_site2->query_first("SELECT position FROM art_nouveautes WHERE artid = '$artid'");
	$poss=$DB_site2->query("select * from art_nouveautes WHERE position < '$position[position]'");
	while ($pos=$DB_site2->fetch_array($poss)) {
		$newpos = $pos[position]+1 ;
		$DB_site2->query("update art_nouveautes set position = '$newpos' WHERE artid = '$pos[artid]'");
		}
	$DB_site2->query("update art_nouveautes set position = '1' WHERE artid = '$artid'");
	echo "<script language=\"javascript\">location.href=\"arts_nouv.php\";</script>";
	}

if (isset($action) and $action == "last")
	{
	$position=$DB_site2->query_first("SELECT position FROM art_nouveautes WHERE artid = '$artid'");						
	$poss=$DB_site2->query("select * from art_nouveautes WHERE position > '$position[position]'");
	while ($pos=$DB_site2->fetch_array($poss)) {
		$newpos = $pos[position]-1 ;
		$DB_site2->query("update art_nouveautes set position = '$newpos' WHERE artid = '$pos[artid]'");
		}
	$max=$DB_site2->query_first("select max(position) from art_nouveautes");
	$new = $max[0] + 1 ;
	$DB_site2->query("update art_nouveautes set position = '$new' WHERE artid = '$artid'");
	echo "<script language=\"javascript\">location.href=\"arts_nouv.php\";</script>";
	}

if (isset($action) and $action == "up")
	{
	$position=$DB_site2->query_first("SELECT position FROM art_nouveautes WHERE artid = '$artid'");						
	$nouvelle_position_marque = $position[0] - 1 ;
	$position2=$DB_site2->query_first("SELECT artid FROM art_nouveautes WHERE position = '$nouvelle_position_marque'");
	$DB_site2->query("UPDATE art_nouveautes SET position = '$nouvelle_position_marque' WHERE artid = '$artid'");
	$DB_site2->query("UPDATE art_nouveautes SET position = '$position[0]' WHERE artid = '$position2[artid]'");
	echo "<script language=\"javascript\">location.href=\"arts_nouv.php\";</script>";
	}

if (isset($action) and $action == "down")
	{
	$position=$DB_site2->query_first("SELECT position FROM art_nouveautes WHERE artid = '$artid'");						
	$nouvelle_position_marque = $position[0] + 1 ;
	$position2=$DB_site2->query_first("SELECT artid FROM art_nouveautes WHERE position = '$nouvelle_position_marque'");
	$DB_site2->query("UPDATE art_nouveautes SET position = '$nouvelle_position_marque' WHERE artid = '$artid'");
	$DB_site2->query("UPDATE art_nouveautes SET position = '$position[0]' WHERE artid = '$position2[artid]'");
	echo "<script language=\"javascript\">location.href=\"arts_nouv.php\";</script>";
	}

if (isset($action) and $action == "modifier")
	{
	if (is_array($chk_articles))
		{
		if (count($chk_articles) <= $nbmax_nouveautes_total)
			{
			$DB_site2->query("delete from art_nouveautes");
			$pos = 0 ;
			foreach ($chk_articles as $cle=>$valeur)
				{
				$pos++ ;
				$DB_site2->query("insert into art_nouveautes (artid, position) values ('$cle', '$pos')");
				}
			if ($pos > 0)
				{
				echo "<div class=\"message\">Articles enregistrés</div>" ;
				}
			}
		else
			{
			echo "<div class=\"message\">Vous ne pouvez sélectionner que $nbmax_nouveautes_total articles au maximum.</div>" ;
			}
		}
	$action = "" ;
	}
	
if (!isset($action) or $action == "")
	{
	$arts=$DB_site2->query("SELECT * FROM art_nouveautes an inner join article a on (a.artid = an.artid) order by position");
	$nb_arts = $DB_site2->num_rows($arts) ;
	if ($nb_arts > 0)
		{
		echo "Articles actuellement selectionnés :<br><br>
		Légende : <img src=\"images/first.gif\" alt=\"1ère\" height=\"7\" border=\"0\" style=\"margin-left:15px;\"> 1ère position
		<img src=\"images/monter.gif\" alt=\"Monter\" height=\"7\" border=\"0\" style=\"margin-left:15px;\"> Monter
		<img src=\"images/descendre.gif\" alt=\"Descendre\" height=\"7\" border=\"0\" style=\"margin-left:15px;\"> Descendre
		<img src=\"images/last.gif\" alt=\"Dernière\" height=\"7\" border=\"0\" style=\"margin-left:15px;\"> Dernière position
		<br><br>
		<table class=\"table_remises_ajout\" align=\"center\">" ;
		$i = 0 ;
		while ($art=$DB_site2->fetch_array($arts))
			{
			$i++;
			if ($nb_arts > 1)
				{
				if ($i == 1)
					{
					$position = "
					<img src=\"images/vide.gif\" height=\"7\">
					<img src=\"images/vide.gif\" height=\"7\">
					<a href=\"arts_nouv.php?action=down&artid=$art[artid]\"><img src=\"images/descendre.gif\" alt=\"Descendre\" height=\"7\" border=\"0\"></a>
					<a href=\"arts_nouv.php?action=last&artid=$art[artid]\"><img src=\"images/last.gif\" alt=\"Dernière\" height=\"7\" border=\"0\"></a>
					";
					}
				elseif  ($i == $nb_arts)
					{
					$position = "
					<a href=\"arts_nouv.php?action=first&artid=$art[artid]\"><img src=\"images/first.gif\" alt=\"1ère\" height=\"7\" border=\"0\"></a>
					<a href=\"arts_nouv.php?action=up&artid=$art[artid]\"><img src=\"images/monter.gif\" alt=\"Monter\" height=\"7\" border=\"0\"></a>
					<img src=\"images/vide.gif\" height=\"7\">
					<img src=\"images/vide.gif\" height=\"7\">";
					}
				else
					{
					$position = "
					<a href=\"arts_nouv.php?action=first&artid=$art[artid]\"><img src=\"images/first.gif\" alt=\"1ère\" height=\"7\" border=\"0\"></a>
					<a href=\"arts_nouv.php?action=up&artid=$art[artid]\"><img src=\"images/monter.gif\" alt=\"Monter\" height=\"7\" border=\"0\"></a>
					<a href=\"arts_nouv.php?action=down&artid=$art[artid]\"><img src=\"images/descendre.gif\" alt=\"Descendre\" height=\"7\" border=\"0\"></a>
					<a href=\"arts_nouv.php?action=last&artid=$art[artid]\"><img src=\"images/last.gif\" alt=\"Dernière\" height=\"7\" border=\"0\"></a>
					";
					}
				}
			echo "<tr>
					<td align=\"center\">$position</td>
					<td align=\"center\"><img src=\"/configurations/$host/images/produits/categorie/$art[artid].$art[image]\" height=\"100\"></td>
					<td align=\"left\">$art[$libelle] $art[artcode]</td>
					<td align=\"left\">".number_format($art[prix], 2, ",", " ")." €</td>
					<td align=\"center\"><a href=\"arts_nouv.php?action=del&artid=$art[artid]\"><img src=\"images/supprimer.gif\"></a></td>
				</tr>
					" ;
			}
		echo "</table>" ;
		}
	else
		{
		echo "Aucun article selectionné pour le moment." ;
		}
	
	$treeview = "<script><!--
		function toggle(id,p)
			{
			var myChild = document.getElementById(id);
			if(myChild.style.display!='block')
				{
				myChild.style.display='block';
				document.getElementById(p).className='folderOpen';
				}
			else
				{
				myChild.style.display='none';
				document.getElementById(p).className='folder';
				}
			}
	
		function changecolor(id)
			{
			var myChild = document.getElementById(id);
			if (myChild.style.color=='rgb(255, 0, 0)' || myChild.style.color=='#ff0000')
				{
				myChild.style.color='#5F718A';
				}
			else
				{
				myChild.style.color='#FF0000';
				}
			}
	
		function toggledemarrage(id,p)
			{
			var myChild = document.getElementById(id);
			myChild.style.display='block';
			document.getElementById(p).className='folderOpen';
			}
	--></script>";
	
	construire_cat(0);
	echo "<form method=\"post\">
	<input type=\"hidden\" name=\"action\" value=\"modifier\">
	<div align=\"left\" style=\"margin:25px;\"><ul>$treeview</ul></div>
	<input type=\"submit\" value=\"Enregistrer\">" ;
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