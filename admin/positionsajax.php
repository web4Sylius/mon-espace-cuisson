<?php
require "admin_global.php";
include "./header.php";
include "./menu.php";

echo "
<script language=\"JavaScript\" type=\"text/javascript\" src=\"/javascripts/tool-man/core.js\"></script>
<script language=\"JavaScript\" type=\"text/javascript\" src=\"/javascripts/tool-man/events.js\"></script>
<script language=\"JavaScript\" type=\"text/javascript\" src=\"/javascripts/tool-man/css.js\"></script>
<script language=\"JavaScript\" type=\"text/javascript\" src=\"/javascripts/tool-man/coordinates.js\"></script>
<script language=\"JavaScript\" type=\"text/javascript\" src=\"/javascripts/tool-man/drag.js\"></script>
<script language=\"JavaScript\" type=\"text/javascript\" src=\"/javascripts/tool-man/dragsort.js\"></script>
<script language=\"JavaScript\" type=\"text/javascript\" src=\"/javascripts/tool-man/cookies.js\"></script>

<script language=\"JavaScript\" type=\"text/javascript\"><!--
	var dragsort = ToolMan.dragsort()
	var junkdrawer = ToolMan.junkdrawer()

	window.onload = function() {
		//junkdrawer.restoreListOrder(\"boxes\")


		dragsort.makeListSortable(document.getElementById(\"boxes\"),
				saveOrder)

	}

	function verticalOnly(item) {
		item.toolManDragGroup.verticalOnly()
	}

	function speak(id, what) {
		var element = document.getElementById(id);
		element.innerHTML = 'Clicked ' + what;
	}

	function saveOrder(item) {
		var group = item.toolManDragGroup
		var list = group.element.parentNode
		var id = list.getAttribute(\"id\")
		if (id == null) return
		group.register('dragend', function() {
			ToolMan.cookies().set(\"list-\" + id, 
					junkdrawer.serializeList(list), 365)
		})
	}

	//-->
</script>
" ;

echo "
<td valign=\"top\">
<table width=\"100%\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\"><tr><td>
<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\">
  <tr>
   <td>
   	<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\">
		<tr>
			<td height=\"25\" nowrap> &nbsp; <img src=\"images/arrow_2.jpg\"> <font class=\"cost\">Aide à l'administration</font>
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



function navigation($catidfunc , $isarticle=0)
	{
	$separateur_navigation=" > ";
	global $DB_site2,$libelle,$navigationbit,$parentid;
	$requete_categorie="select catid , $libelle , parentid from categorie where catid='$catidfunc' ";
	$navigation_categorie=$DB_site2->query_first($requete_categorie);
	if ($parentid==$catidfunc and $isarticle==0) 
		{
		$navigationbit=$navigation_categorie[$libelle];
		} 
	else 
		{
		$navigationbit="<a href=\"catalogue.php?catid=$navigation_categorie[catid]\">$navigation_categorie[$libelle]</a>" . $separateur_navigation . $navigationbit;
		}
	if ($navigation_categorie[parentid]!=0) 
		{
		navigation($navigation_categorie[parentid]);
		}
	}

if ($catid == "" || !isset($catid))
	{
	$catid="0";
	$parentid="0";
	}
else
	$parentid=$catid;

$i=0;

$tabcategorie=array();


$categ=$DB_site2->query_first("select $libelle, catid, parentid from categorie where catid = '$parentid'");

// AFFICHAGE BARRE NAVIGATION
if ($parentid=="0")
	{
	$navigationdebut="<table class=\"table_modules_boutiques\" align=\"center\">
						<tr>
							<td class=\"td_modules_boutiques\">".${"title$langue"};
	}
else
	{
	$navigationdebut="<table class=\"table_modules_boutiques\" align=\"center\">
						<tr>
							<td class=\"td_modules_boutiques\"><a href=\"catalogue.php\">".${"title$langue"}."</a> >";
	}
navigation($categ[parentid]);
echo ($navigationdebut.$navigationbit." <a href=\"catalogue.php?catid=$categ[catid]\">$categ[$libelle]</a> > Ordre d'affichage des articles</td>");

echo " </tr>
	</table>";

if (isset($action) and $action == "enregistrer")
	{
	$page = 1 ;
	$positionpage = 0 ;
	$liste = explode("|", $ordre) ;
	foreach ($liste as $key => $value)
		{
		$positionpage++ ;
		$DB_site2->query("update article set page = '$page', pageposition = '$positionpage' where artid = '$value'");
		if ($positionpage == $perpage)
			{
			$positionpage = 0 ;
			$page++ ;
			}
		/*if (substr($key, 0, 4) == "page")
			{
			$page = substr($key, 4) ;
			foreach ($value as $key2 => $value2)
				{
				$positionpage = $key2 + 1 ;
				$DB_site2->query("update article set page = '$page', pageposition = '$positionpage' where artid = '$value2'");
				}
			}*/
		}
	echo "<script language=\"javascript\">location.href=\"positionsajax.php?catid=$catid\";</script>";
	}

echo "<br><br>
	<p><input class=\"inspector\" type=\"button\" value=\"Enregistrer\" onclick=\"junkdrawer.inspectListOrder('boxes')\"/></p>
	<form method=\"post\" action=\"positionsajax.php\" name=\"formu\">
	<input type=\"hidden\" name=\"action\" value=\"enregistrer\">
	<input type=\"hidden\" name=\"catid\" value=\"$parentid\">
	<input type=\"hidden\" name=\"ordre\" value=\"\" id=\"ordre\">
	<div class=\"boites\">
		<ul id=\"boxes\">" ;
$n = 0 ;
$o = 0 ;
$articles=$DB_site2->query("select * from article where catid = '$parentid' order by page, pageposition");
while ($article=$DB_site2->fetch_array($articles))
	{
	if ($article[image] != "")
		{
		$largeur = 128;
		$hauteur = 100;
		$img = "http://$host/configurations/$host/images/produits/nouveautes_categ/$article[artid].$article[image]";
		$imagesize = getimagesize($img) ;
		if ($imagesize[0] > $largeur or $imagesize[1] > $hauteur)
			{
			$largeur_en_cours = $imagesize[0] ;
			$hauteur_en_cours = $imagesize[1] ;
			if ($largeur_en_cours > $largeur) // si largeur > 112
				{
				$width = $largeur ; // largeur = 112
				$height = floor ($largeur * $hauteur_en_cours / $largeur_en_cours) ; // hauteur = 112 * (768/1024)
				$largeur_en_cours = $width ;
				$hauteur_en_cours = $height ;
				} 
			if ($hauteur_en_cours > $hauteur) 
				{
				$width = floor($largeur_en_cours * $hauteur / $hauteur_en_cours) ;
				$height = $hauteur ;
				$largeur_en_cours = $width ;
				$hauteur_en_cours = $height ;
				}
			$newlargeur = $largeur_en_cours;
			$newhauteur = $hauteur_en_cours;			
			} 
		else 
			{
			$newlargeur = $largeur;
			$newhauteur = $hauteur;
			}
		}
		else
		{
		$img = "images/forme.jpg";
		$newlargeur = 128;
		$newhauteur = 100;		
		}
	echo "\n<li class=\"box\" id=\"$article[artid]\">
			".substr($article[$libelle], 0, 30)."<br>
			Réf $article[artcode]<br>
			<img src=\"$img\" width=\"$newlargeur\" height=\"$newhauteur\">
		</li>" ;
	$n++ ;
	/*if ($n == $perpage)
		{
		echo "<div class=\"bloc\"></div><br><br>" ;
		$n = 0 ;
		}*/
	}
echo "</ul>
	<div style=\"clear:both;\"></div>
</div>
<p><input class=\"inspector\" type=\"button\" value=\"Enregistrer\" onclick=\"junkdrawer.inspectListOrder('boxes')\"/></p>" ;

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
