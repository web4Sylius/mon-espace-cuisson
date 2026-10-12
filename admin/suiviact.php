<?php

require "admin_global.php";
include "./header.php";
include "./menu.php";
include "./fonctions.php";	

echo "<td valign=\"top\">
<table width=\"100%\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\"><tr><td>
<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\">
  <tr>
   <td>
   	<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\">
		<tr>
			<td height=\"25\" nowrap> &nbsp; <img src=\"images/arrow_2.jpg\"> <font class=\"cost\">Suivi d'activités</font>
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
				<td align=\"left\" valign=\"middle\" width=\"100%\">" ;
				
$treeview.="<script><!--
	function toggle(id,p)
		{
		var myChild = document.getElementById(id);
		if(myChild.style.display!='block')
			{
			myChild.style.display='block';
			document.getElementById(p).className='folderOpen2';
			}
		else
			{
			myChild.style.display='none';
			document.getElementById(p).className='folder2';
			}
		}
--></script>";


function categories_parentes($catid)
	{
	global $DB_site2, $categories, $i ;
	$parente=$DB_site2->query_first("select parentid from categorie where catid = '$catid'");
	if ($parente[0] != 0)
		{
		$categories[$i] = $parente[0] ;
		$i++ ;
		categories_parentes($parente[0]);
		}
	return ($categories) ;
	}


function construire_cat($parentid)
	{
	global $DB_site2,$treeview, $artid, $catid, $categories ;
	$cats=$DB_site2->query("select * from categorie where parentid = '$parentid' order by libelleFR asc");
	$treeview.="";
	while($cat=$DB_site2->fetch_array($cats)) // chaque categ de niveau 1
		{
		$categories = array () ;
		(categories_parentes($cat[catid])) ;
		$sscats=$DB_site2->query("select * from categorie where parentid = '$cat[catid]' limit 1");
		if($sscat=$DB_site2->fetch_array($sscats)) // chaque categ de niveau 2
			{
			if ($cat[catid] == $catid)
				{
				$treeview.="<li class=folder2 id=\"T$cat[catid]\"><a title=\"$cat[libelleFR]\" alt=\"$cat[libelleFR]\" href=\"suiviact.php?catid=$cat[catid]&categs=oui\" class=treeview><strong><font color=\"red\">$cat[libelleFR]</font></strong></a>$case</li>\n<ul class=tree2 id=\"F$cat[catid]\">$place\n";
				construire_cat($cat[catid]);
				$treeview.="</ul>\n";
				}
			elseif (in_array ("$catid", $categories))
				{
				$treeview.="<li class=folder2 id=\"T$cat[catid]\"><a title=\"$cat[libelleFR]\" alt=\"$cat[libelleFR]\" href=\"suiviact.php?catid=$cat[catid]&categs=oui\" class=treeview><font color=\"red\">$cat[libelleFR]</font></a>$case</li>\n<ul class=tree2 id=\"F$cat[catid]\">$place\n";
				construire_cat($cat[catid]);
				$treeview.="</ul>\n";
				}
			else
				{
				$treeview.="<li class=folder2 id=\"T$cat[catid]\"><a title=\"$cat[libelleFR]\" alt=\"$cat[libelleFR]\" href=\"suiviact.php?catid=$cat[catid]&categs=oui\" class=treeview>$cat[libelleFR]</a>$case</li>\n<ul class=tree2 id=\"F$cat[catid]\">$place\n";
				construire_cat($cat[catid]);
				$treeview.="</ul>\n";
				}
			}
		else
			{
			if ($cat[catid] == $catid)
				{
				$treeview.="<li class=folder2 id=\"T$cat[catid]\"><a title=\"$cat[libelleFR]\" alt=\"$cat[libelleFR]\" href=\"suiviact.php?catid=$cat[catid]&categs=oui\" class=treeview><strong><font color=\"red\">$cat[libelleFR]</font></strong></a>$case</li>\n<ul class=tree2 id=\"F$cat[catid]\">$place</ul>\n";
				}
			elseif (in_array ("$catid", $categories))
				{
				$treeview.="<li class=folder2 id=\"T$cat[catid]\"><a title=\"$cat[libelleFR]\" alt=\"$cat[libelleFR]\" href=\"suiviact.php?catid=$cat[catid]&categs=oui\" class=treeview><font color=\"red\">$cat[libelleFR]</font></a>$case</li>\n<ul class=tree2 id=\"F$cat[catid]\">$place</ul>\n";
				}
			else
				{
				$treeview.="<li class=folder2 id=\"T$cat[catid]\"><a title=\"$cat[libelleFR]\" alt=\"$cat[libelleFR]\" href=\"suiviact.php?catid=$cat[catid]&categs=oui\" class=treeview>$cat[libelleFR]</a>$case</li>\n<ul class=tree2 id=\"F$cat[catid]\">$place</ul>\n";
				}
			}
		}
	}


echo "<center><h3>Voir le suivi d'activités</h3></center>
<ul>
<li><a href=\"suiviact.php\" onMouseOver=\"poplink('Voir tout');\" onmouseout=\"killlink()\">Général</a></li>
<li><a href=\"suiviact.php?categs=oui\" onMouseOver=\"poplink('Choisir les catégories');\" onmouseout=\"killlink()\">Par rayon</a></li>
</ul>" ;

if (isset($catid))
	{
	$categs = "oui" ;
	}


if (isset($categs) and $categs == "oui")
	{
	echo "<br><bR><center><strong>Choisissez le rayon que vous souhaitez visualiser</strong></center><bR>" ;
	$i = 0 ;
	construire_cat(0);
	echo $treeview ;
	}

//////////////////////////////////////////////////////////////////////////////////////////
// SUIVI D'ACTIVITE
//////////////////////////////////////////////////////////////////////////////////////////
if (in_array ("10", $modules))
	{
	function recur_categorie ($tab, $pere, $nb_elements) 
		{
		global $tab_catid;
		for ($i=0 ; $i<$nb_elements ; $i++)
			{
			if ($tab[$i][1] == $pere)
				{
				$tab_catid .= ",".$tab[$i][0];
				$tab_catid .= recur_categorie( $tab, $tab[$i][0], $nb_elements );
				}
			}
		} 
	$rq_categorie = $DB_site2->query("SELECT * FROM categorie GROUP BY catid ORDER BY $libelle");
	while ($rs_categorie = $DB_site2->fetch_array($rq_categorie))
		{
		$tab_categorie[] = array ($rs_categorie[catid], $rs_categorie[parentid]);
		}
	$nb_categories = $DB_site2->query_first("select count(*) from categorie");
	$rq_categorie = $DB_site2->query("SELECT * FROM categorie WHERE parentid='$catid' ORDER BY $libelle");
	if (!isset($j) || $j=="") 
		{ $j=date( "d" ); }
	else 
		{
		if( $j < 10 && strlen($j)==1 )
			$j = "0".$j;
		}
	if (!isset($m) || $m=="" ) 
		{ $m=date( "m" ); }
	else 
		{
		if( $m < 10 && strlen($m)==1 )
			$m = "0".$m;
		}
	if( !isset($y) || $y=="" ) 
		{ $y=date( "Y" ); }
	if( !isset($periode) || $periode == "" )
		$periode = "j";
	// Date de lancement de la boutique
	$date_inscription = $DB_site2->query_first("SELECT date_inscription FROM parametresgeneraux");
	list ($annee_inscription, $mois_inscription, $jour_inscription) = explode('-', $date_inscription[0]);
	// Affichage par jour
	if ($periode == "j")
		{
		$date_facture_deb = "$y-$m-$j";
		// Si on n'est pas au dernier jour du mois on incrémente le jour
		if ($j < date ("t", mktime(0,0,0,$m,1,$y)))
			{
			$date_facture_fin = "$y-$m-".($j+1);
			}
		else
			{	// Sinon si on est pas au dernier mois de l'année on incrémente le mois
			if ($m < 12) 
				{
				$m2 = $m+1;
				if ($m2 < 10 && strlen($m2)==1)
					{
					$m2 = "0".$m2;
					}
				$date_facture_fin = "$y-$m2-01";
				} 
			else 
				{
				// Sinon on est au dernier jour de l'année donc on passe à l'année suivante
				$y2 = $y+1;
				$date_facture_fin = "$y2-01-01";
				}
			}
		// Barre de menu
		$barre_menu = "Jour 
		 <a href=\"suiviact.php?catid=$catid&periode=m#suivi\" onMouseOver=\"poplink('Par mois');\" onmouseout=\"killlink()\">Mois</a>
		 <a href=\"suiviact.php?catid=$catid&periode=y#suivi\"  onMouseOver=\"poplink('Par année');\" onmouseout=\"killlink()\">Année</a>
		 </td></tr>";
		// Titre du camembert
		$tab_mois = array (1 => 'Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Aout', 'Septembre', 'Octobre', 'Novembre', 'Decembre');
		$donnee_titre = "Chiffre d affaires pour le $j " . $tab_mois[$m+0] . " $y " ;  
		}
	// Affichage par mois
	if ($periode == "m")
		{
		$date_facture_deb = "$y-$m-01";
		if ($m < 12)
			{
			// Si on est pas au dernier mois de l'année on incrémente le mois
			$m2 = $m + 1 ;
			if ($m2 < 10 && strlen($m2) == 1)
				{
				$m2 = "0" . $m2 ;
				}
			$date_facture_fin = "$y-$m2-01";
			} 
		else 
			{
			// Sinon on est au dernier mois de l'année donc on passe à l'année suivante
			$y2 = $y+1;
			$date_facture_fin = "$y2-01-01";
			}
		if ($m != $mois_inscription || $y > $annee_inscription)
			{
			// Si on n'est pas sur le mois du lancement de la boutique, on commence au 1er du mois
			$jour_min = 1;
			} 
		else 
			{
			// Sinon on commence au jour du lancement de la boutique
			$jour_min = $jour_inscription+0;
			}
		if ($m != date ("m") || $y != date ("Y"))
			{
			// Si on n'est pas sur le mois actuel, on affiche tous les jours du mois
			$jour_max = date( "t", mktime(0,0,0,$m,1,$y) );
			echo "jour max : $jour_max<br>";
			} 
		else 
			{
			// Si on est sur le mois actuel, on s'arrette au jour actuel
			$jour_max = date( "d" );
			}
		// Menu "jour mois année"
		$barre_menu = " <a href=\"suiviact.php?catid=$catid&periode=j#suivi\" onMouseOver=\"poplink('Par jour');\" onmouseout=\"killlink()\">Jour</a>  Mois  <a href=\"suiviact.php?catid=$catid&periode=y#suivi\" onMouseOver=\"poplink('Par an');\" onmouseout=\"killlink()\">Année</a></td></tr>";
		// Index des jours du mois "1 2 3 4 [...]"
		$barre_menu .= "<tr><td align=center> jour : ";
		for ($i = $jour_min ; $i <= $jour_max ; $i++)
			{
			$barre_menu .= "<a href=\"suiviact.php?catid=$catid&periode=j&y=$y&m=$m&j=$i#suivi\">$i</a> ";
			}
		$barre_menu .= "</td></tr>";
		// Titre du camembert
		$tab_mois = array( 1=>'Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin'
							, 'Juillet', 'Aout', 'Septembre', 'Octobre', 'Novembre', 'Decembre' );
		$donnee_titre = "Chiffre d affaires pour ".$tab_mois[$m+0]." $y "; 
		}
	// Affichage par année
	if ($periode == "y")
		{
		$date_facture_deb = "$y-01-01";
		$y2 = $y+1;
		$date_facture_fin = "$y2-01-01";
		// Borne min
		if ($y > $annee_inscription)
			{
			// Si on est sur une année différente de la première année de la boutique, on commence en janvier
			$mois_min = 1;
			} 
		else 
			{
			// Sinon on commence au mois de la date d'inscription
			$mois_min = $mois_inscription+0;
			}
		// Borne max
		if ($y != date ("Y"))
			{
			// Si on n'est pas sur l'année actuelle, on va jusqu'en décembre
			$mois_max = 12;
			} 
		else 
			{
			// Si on est sur l'année actuelle, on s'arrette au mois actuel
			$mois_max = date( "m" );
			}
		// Menu "jour mois année"
		$barre_menu = " <a href=\"suiviact.php?catid=$catid&periode=j#suivi\" onMouseOver=\"poplink('Par jour');\" onmouseout=\"killlink()\">Jour</a>  <a href=\"suiviact.php?catid=$catid&periode=m#suivi\" onMouseOver=\"poplink('Par mois');\" onmouseout=\"killlink()\">Mois</a> Année";
		// On rajoute les années après le lien "année" si + d'un an a passé depuis le lancement de la boutique
		if ($annee_inscription < date ("Y"))
			{	
			for ($i = $annee_inscription ; $i <= date ("Y") ; $i++)
				{
				// Pour l'année sur laquelle on est on ne remet pas le lien hypertexte
				if ($i == $y)
					{
					$barre_menu .= " $y" ;
					}
				else
					{
					$barre_menu .= " <a href=\"suiviact.php?catid=$catid&periode=y&y=$i#suivi\"> $i</a>";	
					}
				}	
			}
		$barre_menu .= "</td></tr>";
		// Index des des mois "1 2 3 4 [...]"
		$barre_menu .= "<tr><td align=center> mois : ";
		$tab_mois = array( 1=>'Jan', 'Fev', 'Mar', 'Avr', 'Mai', 'Juin', 'Juil', 'Aou', 'Sep', 'Oct', 'Nov', 'Dec' );
		for ($i = $mois_min ; $i <= $mois_max ; $i++)
			{
			$barre_menu .= "<a href=\"suiviact.php?catid=$catid&periode=m&y=$y&m=$i#suivi\">$tab_mois[$i]</a> ";
			}
		$barre_menu .= "</td></tr>";
		// Titre du camembert
		$donnee_titre = "CA pour $y "; 
		}

	$is_null = 0 ;
	$virgule = "" ; // rajoute des "," pour séparer les catid; utilisé pour former la chaine $tab_catid
	$data_barre = "" ;
	$donnee_lib_barre = "" ;
	$pipe = "" ;
	while ($rs_categorie=$DB_site2->fetch_array($rq_categorie))
		{
		// Toutes les sous catégories de la catégorie en cours
		recur_categorie( $tab_categorie, $rs_categorie["catid"], $nb_categories[0] );
		// Requête pour le CA en camembert
		$statistique=$DB_site2->query_first("SELECT SUM(lf.qte*lf.prix) from lignefacture AS lf INNER JOIN facture 
		ON (facture.facturid = lf.factureid) WHERE facture.datefacture >= '$date_facture_deb' AND facture.datefacture < '$date_facture_fin'
		AND facture.valide = 5 AND lf.catid IN ($rs_categorie[catid]$tab_catid)");
		if( $periode == "m" )
			{
			$statistique_courbes=$DB_site2->query("SELECT SUM(lf.qte*lf.prix), facture.datefacture from lignefacture AS lf 
			INNER JOIN facture ON (facture.facturid = lf.factureid) WHERE facture.datefacture>='$date_facture_deb'
			AND facture.datefacture < '$date_facture_fin' AND facture.valide = 5 AND lf.catid IN ($rs_categorie[catid]$tab_catid)
			GROUP BY facture.datefacture ORDER BY facture.datefacture");
			$k = 1;
			$virgule3 = "";
			$data_barre .= $pipe;
			while ($statistique_courbe = $DB_site2->fetch_array($statistique_courbes))
				{
				list ($annee_fac, $mois_fac, $jour_fac) = explode ('-', $statistique_courbe[datefacture]) ;
				while ($k < $jour_fac)
					{
					$data_barre .= $virgule3."0" ;
					$k++;
					$virgule3 = ",";
					}
				$data_barre .= $virgule3.$statistique_courbe[0];
				$virgule3 = ",";
				$k++;
				}
			while ($k <= $jour_max)
				{
				$data_barre .= $virgule3."0";
				$k++;
				$virgule3 = ",";
				}
			$pipe = "|";
			$donnee_lib_barre .= $virgule.url_rewrite($rs_categorie[$libelle]);
			}
		elseif ($periode == "y")
			{
			$virgule3 = "";
			$data_barre .= $pipe;
			$donnee_lib_barre .= $virgule.url_rewrite($rs_categorie[$libelle]);
			
			for ($l=$mois_min; $l<=$mois_max; $l++)
				{
				$l2 = $l;
				if ($l2 < 10)
					{
					$l2 = "0$l2";
					}
				$date_facture_deb_courbes = "$y-$l2-01";
				$date_facture_fin_courbes = "$y-$l2-".date( "t", mktime(0,0,0,$l2,1,$y) );
				$statistique_courbes = $DB_site2->query_first("SELECT SUM(lf.qte*lf.prix) from lignefacture AS lf 
				INNER JOIN facture ON (facture.facturid = lf.factureid) WHERE facture.datefacture >= '$date_facture_deb_courbes'
				AND facture.datefacture <= '$date_facture_fin_courbes' AND facture.valide = 5 AND lf.catid IN ($rs_categorie[catid]$tab_catid)");
				if ($statistique_courbes[0] == "")
					{
					$statistique_courbes[0] = 0 ;
					}
				$data_barre .= $virgule3.$statistique_courbes[0];
				$virgule3 = ",";
				}
			$pipe = "|";
			}
		if ($statistique[0] == "")
			{
			$statistique[0] = 0;
			}
		else
			{
			$is_null++;
			}
		if ($statistique[0] != 0) 
			{
			$donnee .= $virgule.$statistique[0];
			$donnee_lib .= $virgule.url_rewrite($rs_categorie[$libelle])." : ".number_format($statistique[0], 2, '.', ' ')." Euros";
			$virgule = ",";
			$tab_catid = "";
			}
		}
	$statistique_articles=$DB_site2->query_first("SELECT SUM(lf.qte*lf.prix) from lignefacture AS lf INNER JOIN facture 
	ON (facture.facturid = lf.factureid) WHERE facture.datefacture >= '$date_facture_deb' AND facture.datefacture < '$date_facture_fin'
	AND facture.valide = 5 AND lf.catid = '$catid'");
	if ($statistique_articles[0] != "")
		{
		$donnee .= $virgule.$statistique_articles[0];
		$donnee_lib .= $virgule."Dans ce rayon : ".number_format($statistique_articles[0], 2, '.', ' ')." Euros";
		$is_null++;
		$virgule2 = "";
		$is_null2 = 0;
		$statistique_detail_articles = $DB_site2->query("SELECT SUM(lf.qte*lf.prix), lf.libelle from lignefacture AS lf 
	    INNER JOIN facture ON (facture.facturid = lf.factureid) WHERE facture.datefacture >= '$date_facture_deb'
		AND facture.datefacture < '$date_facture_fin' AND facture.valide = 5 AND lf.catid = '$catid'
		GROUP BY lf.artid ORDER BY lf.libelle" );
		while( $statistique_detail_article = $DB_site2->fetch_array( $statistique_detail_articles ) )
			{
			if($statistique_detail_article[0]!=0) 
				{
				$donnee_article .= $virgule2.$statistique_detail_article[0];
				$donnee_lib_article .= $virgule2.url_rewrite($statistique_detail_article[libelle])." : ".number_format($statistique_detail_article[0], 2, '.', ' ')." Euros";
				$is_null2++;
				$virgule2 = ",";
				}
			}
		$statistique_detail_courbes = $DB_site2->query( "SELECT SUM(lf.qte*lf.prix), facture.datefacture from lignefacture AS lf 
		INNER JOIN facture ON (facture.facturid=lf.factureid) WHERE facture.datefacture>='$date_facture_deb'
		AND facture.datefacture<'$date_facture_fin' AND facture.valide=5 AND lf.catid = '$catid'
		GROUP BY facture.datefacture ORDER BY facture.datefacture" );
		$k = 1;
		$virgule3 = "";
		if( $DB_site2->num_rows( $statistique_detail_courbes ) > 0 )
			{
			$data_barre .= $pipe;
			$donnee_lib_barre .= $virgule."Dans ce rayon";
			while ($statistique_detail_courbe = $DB_site2->fetch_array( $statistique_detail_courbes))
				{
				list ($annee_fac, $mois_fac, $jour_fac) = explode ('-', $statistique_detail_courbe[datefacture]) ;
				while ($k < $jour_fac)
					{
					$data_barre .= $virgule3."0";
					$k++;
					$virgule3 = ",";
					}
				$data_barre .= $virgule3.$statistique_detail_courbe[0];
				$virgule3 = ",";
				$k++;
				}
			while ($k <= $jour_max )
				{
				$data_barre .= $virgule3."0";
				$k++;
				$virgule3 = ",";
				}
			}
		}
	echo "<br><table class=\"table_modules_boutiques\">
			<tr><td align=\"center\">$barre_menu<tr><td align=\"center\">";
	if ($is_null == 0)
		{
		echo "<b><br>Aucune valeur pour votre choix</b>";
		}
	else
		{
		if ($catid == 0)
			{
			$donnee_titre_article = $donnee_titre."sur les articles de ce rayon";
			$donnee_titre .= "sur la boutique";
			} 
		else 
			{
			$libelle_categorie = $DB_site2->query_first( "SELECT $libelle FROM categorie WHERE catid=$catid" );
			$donnee_titre_article = $donnee_titre."sur les articles du rayon $libelle_categorie[0]";
			$donnee_titre .= "sur le rayon $libelle_categorie[0]";
			}
		echo "<img src=\"camembert.php?donnee=$donnee&donnee_lib=$donnee_lib&donnee_titre=$donnee_titre\">";
		if ($is_null2 != 0)
			{
			echo "<br><img src=\"camembert.php?donnee=$donnee_article&donnee_lib=$donnee_lib_article&donnee_titre=$donnee_titre_article\">";
			}
		}
	if ($periode == "m" || $periode == "y")
		{
		if ($periode == "m") 
			{
			$donnee_titre .= " par jour";
			$donnee_axex = "jour";
			} 
		else 
			{ // $periode == "y"
			$donnee_titre .= " par mois";
			$donnee_axex = "mois";
			}
		if( $is_null != 0 )
			{
			echo "<br><img src=\"courbe.php?donnee=$data_barre&donnee_lib_barre=$donnee_lib_barre&donnee_titre=$donnee_titre&donnee_axex=$donnee_axex\">";
			}	
		}
	echo "</td></tr></table>";
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