<?php

require "admin_global.php";
include "./header.php";
include "./menu.php";


?>
<?php

echo "
<td valign=\"top\">
<table width=\"100%\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\"><tr><td>
<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\">
  <tr>
   <td>
       <table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\">
        <tr>
            <td height=\"25\" nowrap> &nbsp; <img src=\"images/arrow_2.jpg\"> <font class=\"cost\">Page Optimisée</font>
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
   <td align=\"center\" valign=\"middle\" id=\"optipage\">";

?>

<style type="text/css">
#optipage table {
	width:100%;
	border: none;
}

#optipage th {
	font-size: 0.8em;
}

#optipage td {
	text-align:center;
}

#optipage .imgborder {
	border: 2px solid purple;
	vertical-align: middle;
}

#optipage .imgchange {
	cursor: pointer;
	vertical-align: middle;
}
</style>

<?php


if(isset($_POST['deloptid']) && is_numeric($_POST['deloptid']) && $_POST['deloptid']) {
	
	$DB_site2->query("DELETE FROM opti_article WHERE optid = '".$_POST['deloptid']."';");
	$DB_site2->query("DELETE FROM opti_page WHERE optid = '".$_POST['deloptid']."' LIMIT 1;");
	
	echo '<center>Page #'.$_POST['deloptid'].' effacée avec succés</center>';
	
}

if(isset($_POST['editoptid']) && is_numeric($_POST['editoptid']) && $_POST['editoptid']) {
	
	$DB_site2->query("UPDATE opti_page SET titreFR = '".$_POST['titre_FR']."', titreEN = '".$_POST['titre_EN']."', titreDE = '".$_POST['titre_DE']."', titreIT = '".$_POST['titre_IT']."', titreSP = '".$_POST['titre_SP']."', textFR = '".$_POST['text_FR']."', textEN = '".$_POST['text_EN']."', textDE = '".$_POST['text_DE']."', textIT = '".$_POST['text_IT']."', textSP = '".$_POST['text_SP']."', nbarticle = '".$_POST['nbart']."' WHERE optid = '".$_POST['editoptid']."' LIMIT 1;");
	
	$DB_site2->query("DELETE FROM opti_article WHERE optid = '".$_POST['editoptid']."';");
	
	$art_query = array();
	
	if(count($_POST['chk_articles'])>0) {
		foreach($_POST['chk_articles'] as $artid) {
			$art_query[] = "('".$_POST['editoptid']."', '$artid')";
		}
	}
	if(count($art_query)>0) {
		$DB_site2->query("INSERT INTO opti_article (optid, artid) VALUES".implode(',',$art_query).";");
	}
	
	echo '<center>Page #'.$_POST['editoptid'].' mise à jour avec succés</center>';
}



switch ($action) {
	case 'delete' :
		if($opti_query = $DB_site2->query_first("SELECT * FROM opti_page WHERE optid = '".$_GET['optid']."' LIMIT 1;")) {
			
			echo '<center>Confirmer la suppression de la page d\'optimisation (#'.$opti_query['optid'].')<br /><b>'.$opti_query['titre'.$langue].'</b><br/><br/><form action="pageopti.php" method="post"><input type="hidden" name="deloptid" value="'.$opti_query['optid'].'"/><input type="submit" value="Confirmer"><br /><br /><a href="pageopti.php">Retour</a></form></center>';
			
		}
		else {
			echo '<center>Aucune page d\'optimisation avec cet ID...<br /><br /><a href="pageopti.php">RETOUR</a></center>';
		}
	break;
	
	case 'new' :
		$DB_site2->query("INSERT INTO opti_page (optid) VALUES(NULL);");
		$_GET['optid'] = $DB_site2->insert_id();
	case 'edit' :
		
		
		if($opti_query = $DB_site2->query_first("SELECT * FROM opti_page WHERE optid = '".$_GET['optid']."' LIMIT 1;")) {
		
			?>
			<script language="javascript" type="text/javascript">
				var regexp = new Array();
				var regrep = new Array();
			
				<?php
				
				$links_req = $DB_site2->query("SELECT * FROM opti_page WHERE optid != '".$_GET['optid']."';");
				
				while($links = $DB_site2->fetch_array($links_req)) {
					foreach ($langues as $lng) {
						if($links['titre'.$lng]) {
							echo 'regexp.push(\''.'('.implode('[ ,]*', preg_split("/[\s,]+/", addslashes($links['titre'.$lng]))).')'.'\');'."\n";
							echo 'regrep.push(\''.'<a href="/op-'.$links['optid'].'-'.'visualiser'.'.html"><strong>$1</strong></a>'.'\');'."\n";
						}
					}
				}
	
				foreach ($langues as $lng) {
					if($opti_query['titre'.$lng]) {
						echo 'regexp.push(\''.'('.implode('[ ,]*', preg_split("/[\s,]+/",addslashes($opti_query['titre'.$lng]))).')'.'\');'."\n";
						echo 'regrep.push(\''.'<strong>$1</strong>'.'\');'."\n";
					}
				}
				?>
			
				regexp.push("\r\n|\n|\r");
				regrep.push("<br />");
			
				function showLNG(lng) {
					var divs = document.getElementsByName('opti_edit');				
					
					for(i = 0 ; i < divs.length ; i++) {
						if(divs[i].id == 'opti_'+lng) {
							divs[i].style.display = '';
						}
						else {
							divs[i].style.display = 'none';
						}
							
					}
					
					var imgs = document.getElementsByName('opti_img');				
					
					for(i = 0 ; i < imgs.length ; i++) {
						if(imgs[i].id == 'img_'+lng) {
							imgs[i].className = 'imgborder';
						}
						else {
							imgs[i].className = 'imgchange';
						}
							
					}
					
				}
				
				function UnSelect(s) {
					for(i = 0 ; i < s.options.length ; i++) {
						s.options[i].selected = false;
					}
				}
				
				function CopyToArea(s, area) {
					
					var txt = document.getElementById(area);
					
					
					
					if(s.selectedIndex >= 0) {
						if(txt.selectionStart != null) {
							txt.value = txt.value.substring(0, txt.selectionStart)+' '+s.options[s.selectedIndex].value+' '+txt.value.substring(txt.selectionStart);
						}
						else {
							txt.value += ' '+s.options[s.selectedIndex].value+' ';
						}
					}
				}
				
				function UpdateApp(maj, ar, tlt) {
					var upcell = document.getElementById(maj);
					var txt = document.getElementById(ar).value;
					var reg = null;
					var title = document.getElementById(tlt).value;
					
					/**
					
					RegExp replace the Text
					
					**/
					
					for(r = 0 ; r < regexp.length ; r++) {
						reg = new RegExp(regexp[r], 'gi');
						reg.multiline = true;
						txt = txt.replace(reg, regrep[r]);
						//alert(txt);
					}
									
					
					if(upcell.innerHTML != null) {
						upcell.innerHTML = '<h1>'+title+'</h1>'+txt;
					}
					
					setTimeout("UpdateApp('"+maj+"', '"+ar+"', '"+tlt+"')", "3000");
				}
			</script>		
			<?php
			
			
			
			
			
			echo '<div align="right">';
			
			foreach ($langues as $lng) {
				if($lng == $langue) {
					echo ' <img name="opti_img" id="img_'.$lng.'" src="images/mini_'.strtolower($lng).'.jpg" class="imgborder" alt="'.$lng.'"onclick="javascript: showLNG(\''.$lng.'\')"> ';
				}
				else {
					echo ' <img name="opti_img" id="img_'.$lng.'" src="images/mini_'.strtolower($lng).'.jpg" class="imgchange" alt="'.$lng.'" onclick="javascript: showLNG(\''.$lng.'\')"> ';
				}
			}
			
			echo '</div>';
			
			echo '<form method="post" action="pageopti.php">
				<input type="hidden" name="editoptid" value="'.$opti_query['optid'].'"><br /><br /><div align="right">Nombres d\'articles affichés : <input type="text" name="nbart" value="'.$opti_query['nbarticle'].'" size="2"></div><br /><br />';
			
			foreach ($langues as $lng) {
				if($lng == $langue) {
					echo '<div name="opti_edit" id="opti_'.$lng.'">';
				}
				else {
					echo '<div name="opti_edit" id="opti_'.$lng.'" style="display: none;">';
				}
				
				echo 'Saisissez les données de la page d\'optimisation (#'.$opti_query['optid'].') : <b>'.$opti_query['titre'.$lng].'</b></center><br /><br /><br />';
				
				$keywords_query=$DB_site2->query("SELECT titre$lng FROM opti_page ORDER by 1;");
				
				$select_keywords = '';
				
				while($keywords = $DB_site2->fetch_array($keywords_query)) {
					$select_keywords .= '<option value="'.$keywords[0].'">'.$keywords[0].'</option>';
					
				}
								
				echo '<table>';
				echo '<tr>
				<th>Titre</th><td><input name="titre_'.$lng.'" type="text" id="tlt_'.$lng.'" value="'.$opti_query['titre'.$lng].'" size="50"/></td><td rowspan="2">Mots-Clés<br /><br /><select size="10" ondblclick="javascript: CopyToArea(this, \'area_'.$lng.'\'); UnSelect(this); UpdateApp(\'app_'.$lng.'\', \'area_'.$lng.'\', \'tlt_'.$lng.'\');">'.$select_keywords.'</select></td>
					</tr><tr>
				<th>Contenu</th><td><textarea name="text_'.$lng.'" id="area_'.$lng.'" cols="40" rows="10">'.$opti_query['text'.$lng].'</textarea></td>
				</tr>';
				
				echo '<tr><th>Aperçu</th><td colspan="2"><div id="app_'.$lng.'" style="margin-left:20px;" align="left"><h1>'.$opti_query['titre'.$lng].'</h1>'.$opti_query['text'.$lng].'</div></td></tr>';
				
				echo '</table>';
				
				echo '</div>';
				
				echo '<script language="javascript" type="text/javascript">UpdateApp(\'app_'.$lng.'\', \'area_'.$lng.'\', \'tlt_'.$lng.'\');</script>';
			}
			
			//afficher tout les articles et catégories...
			
			$treeview = '<script><!--
			function toggle(id,p)
				{
				var myChild = document.getElementById(id);
				if(myChild.style.display!=\'block\')
					{
					myChild.style.display=\'block\';
					document.getElementById(p).className=\'folderOpen\';
					}
				else
					{
					myChild.style.display=\'none\';
					document.getElementById(p).className=\'folder\';
					}
				}
				
			function changecolor(id)
				{
				var myChild = document.getElementById(id);
				if (myChild.style.color==\'rgb(255, 0, 0)\' || myChild.style.color==\'#ff0000\')
					{
					myChild.style.color=\'#5F718A\';
					}
				else
					{
					myChild.style.color=\'#FF0000\';
					}
				}
				
			function toggledemarrage(id,p)
				{
				var myChild = document.getElementById(id);
				myChild.style.display=\'block\';
				document.getElementById(p).className=\'folderOpen\';
				}
		--></script>';
			
			function construire_cat($parentid)
				{
				global $DB_site2,$treeview, $opti_query ;
				$cats=$DB_site2->query("select * from categorie where parentid = '$parentid' order by libelleFR asc");
				$treeview.="";
				while($cat=$DB_site2->fetch_array($cats)) // chaque categ de niveau 1
					{
					$txt = "" ;
					$tab_conseilles = array() ;
					$arts_conseilles=$DB_site2->query("SELECT artid FROM opti_article WHERE optid = '".$opti_query['optid']."';");
					//$arts_conseilles=$DB_site2->query("SELECT * FROM article_conseil ac, article a WHERE a.catid = '$cat[catid]'
					//AND a.artid = ac.artid_conseille AND ac.artid = '$artid'");
					while ($art_conseille=$DB_site2->fetch_array($arts_conseilles))
						{
						$txt = "ok" ;
						array_push($tab_conseilles, $art_conseille['artid']) ;
						}
					$sscats=$DB_site2->query("select * from categorie where parentid = '$cat[catid]' limit 1");
					if($sscat=$DB_site2->fetch_array($sscats)) // chaque categ de niveau 2
						{
						if ($txt != "")
							{
							$treeview.="<li class=folder id=\"T$cat[catid]\"><a title=\"$cat[libelleFR]\" alt=\"$cat[libelleFR]\" href=\"Javascript: toggle('F$cat[catid]','T$cat[catid]');\" class=treeview><font style=\"color:#FF0000;\" id=\"C$cat[catid]\">$cat[libelleFR]</font></a></li>\n<ul class=tree id=\"F$cat[catid]\">\n";
							$treeview .= "<ul class=\"tree2\">" ;
							$articles=$DB_site2->query("SELECT * FROM article WHERE catid = '$cat[catid]'");
							while ($article=$DB_site2->fetch_array($articles))
								{
								$article[libelleFR] = substr($article[libelleFR], 0, 50) ;
								if ($article[artid] == $artid)
									{
									$treeview .= "<li class=\"art\">&nbsp;<font color=\"#000044\"><strong>$article[libelleFR]</strong></font></li>" ;
									}
								else 
									{
									if (in_array($article[artid], $tab_conseilles))
										{
										$treeview .= "<li class=\"art\"><input type=\"checkbox\" name=\"chk_articles[$article[artid]]\" value=\"$article[artid]\" onclick=\"javascript:changecolor('A$article[artid]');\" checked> <font style=\"color:#FF0000;\" id=\"A$article[artid]\">$article[libelleFR]</font></li>" ;
										}
									else
										{
										$treeview .= "<li class=\"art\"><input type=\"checkbox\" name=\"chk_articles[$article[artid]]\" value=\"$article[artid]\" onclick=\"javascript:changecolor('A$article[artid]');\"> <font id=\"A$article[artid]\">$article[libelleFR]</font></li>" ;
										}
									}
								}
							$treeview.="</ul>" ;
							construire_cat($cat[catid]);
							$treeview.="</ul>\n";
							}
						else
							{
							$treeview.="<li class=folder id=\"T$cat[catid]\"><a title=\"$cat[libelleFR]\" alt=\"$cat[libelleFR]\" href=\"Javascript: toggle('F$cat[catid]','T$cat[catid]');\" class=treeview><font style=\"color:#5F718A;\" id=\"C$cat[catid]\">$cat[libelleFR]</font></a></li>\n<ul class=tree id=\"F$cat[catid]\">\n";
							$treeview .= "<ul class=\"tree2\">" ;
							$articles=$DB_site2->query("SELECT * FROM article WHERE catid = '$cat[catid]'");
							while ($article=$DB_site2->fetch_array($articles))
								{
								$article[libelleFR] = substr($article[libelleFR], 0, 50) ;
								if ($article[artid] == $artid)
									{
									$treeview .= "<li class=\"art\">&nbsp;<font color=\"#000044\"><strong>$article[libelleFR]</strong></font></li>" ;
									}
								else 
									{
									if (in_array($article[artid], $tab_conseilles))
										{
										$treeview .= "<li class=\"art\"><input type=\"checkbox\" name=\"chk_articles[$article[artid]]\" value=\"$article[artid]\" onclick=\"javascript:changecolor('A$article[artid]');\" checked> <font style=\"color:#FF0000;\" id=\"A$article[artid]\">$article[libelleFR]</font></li>" ;
										}
									else
										{
										$treeview .= "<li class=\"art\"><input type=\"checkbox\" name=\"chk_articles[$article[artid]]\" value=\"$article[artid]\" onclick=\"javascript:changecolor('A$article[artid]');\"> <font id=\"A$article[artid]\">$article[libelleFR]</font></li>" ;
										}
									}
								}
							$treeview.="</ul>" ;
							construire_cat($cat[catid]);
							$treeview.="</ul>\n";
							}
						}
					else
						{
						if ($txt != "")
							{
							$treeview.="<li id=\"T$cat[catid]\"><font style=\"color:#000044;\" id=\"C$cat[catid]\">$cat[libelleFR]</font></li>\n<ul class=tree id=\"F$cat[catid]\">\n</ul>\n";
							}
						else
							{
							$treeview.="<li id=\"T$cat[catid]\"><font style=\"color:#5F718A;\" id=\"C$cat[catid]\">$cat[libelleFR]</font></li>\n";
							}
						$treeview .= "<ul class=\"tree2\">" ;
						$articles=$DB_site2->query("SELECT * FROM article WHERE catid = '$cat[catid]'");
						while ($article=$DB_site2->fetch_array($articles))
							{
							$article[libelleFR] = substr($article[libelleFR], 0, 50) ;
							if ($article[artid] == $artid)
								{
								$treeview .= "<li class=\"art\">&nbsp;<font color=\"#000044\"><strong>$article[libelleFR]</strong></font></li>" ;
								}
							else 
								{
								if (in_array($article[artid], $tab_conseilles))
									{
									$treeview .= "<li class=\"art\"><input type=\"checkbox\" name=\"chk_articles[$article[artid]]\" value=\"$article[artid]\" onclick=\"javascript:changecolor('A$article[artid]');\" checked> <font style=\"color:#FF0000;\" id=\"A$article[artid]\">$article[libelleFR]</font></li>" ;
									}
								else
									{
									$treeview .= "<li class=\"art\"><input type=\"checkbox\" name=\"chk_articles[$article[artid]]\" value=\"$article[artid]\" onclick=\"javascript:changecolor('A$article[artid]');\"> <font id=\"A$article[artid]\">$article[libelleFR]</font></li>" ;
									}
								}
							}
						$treeview .= "</ul>" ;
						}
					}
				}

			function derouler($moncatid) 
				{
				global $DB_site2,$treeview ;
				$parentid=$DB_site2->query_first("select parentid from categorie where catid = '$moncatid'");
				$filleid=$DB_site2->query("select catid from categorie where parentid = '$moncatid'");
				$treeview .= "toggledemarrage('F$moncatid','T$moncatid') ;\n" ;
				if ($parentid[0] != "0" and $parentid[0] != "12")
					{
					derouler($parentid[0]);
					}
				}
				
			construire_cat(12);
			
			$treeview .= "<script language=\"javascript\">" ;
			
			$cat_a_derouler = $DB_site2->query("SELECT catid FROM opti_article INNER JOIN article USING(artid) WHERE optid = '".$opti_query['optid']."';");
			
			while($cat = $DB_site2->fetch_array($cat_a_derouler)) {
				derouler($cat[0]);
			}
			
			$treeview .= "</script>" ;
			
			echo '<br /><br /><br /><div align="left">'.$treeview.'</div>';
			
			echo '<br /><br /><br /><input type="submit" value="Enregistrer les modifications"></form><br /><br /><a href="pageopti.php">RETOUR</a>';
			
		}
		else {
			echo '<center>Aucune page d\'optimisation avec cet ID...<br /><br /><a href="pageopti.php">RETOUR</a></center>';
		}
		
	break;
	
	
	
	default :
		$opti_query = $DB_site2->query("SELECT * FROM opti_page ORDER BY titre$langue");
		
		echo '<table>
			<tr>
				<th>Id</th>
				<th>Titre</th>
				<th># Articles</th>
				<th>Actions</th>
			</tr>
			';
		
		while($optis = $DB_site2->fetch_array($opti_query)) {
			echo '<tr>
					<td>'.$optis['optid'].'</td>
					<td>'.$optis['titre'.$langue].'</td>
					<td>'.$optis['nbarticle'].'</td>
					<td><a href="?action=edit&optid='.$optis['optid'].'"><img src="images/editer.gif" /></a> <a href="?action=delete&optid='.$optis['optid'].'"><img src="images/supprimer.gif" /></a> <a href="/op-'.$optis['optid'].'-visualiser.html" target="_blank"><img src="images/voir.gif" /></a></td>
				</tr>';
		}
		
		echo '</table>';
		
		echo '<br /><br /><center><a href="?action=new">Ajouter une page</a></center>';
		
	break;	
}


?>

<?php

echo "</td>
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

?>

<?php
include "./footer.php";
?> 