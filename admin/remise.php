<?php

require "admin_global.php";
include "./header.php";
include "./menu.php";

function arborescence($parentid)
	{
	global $DB_site2,$select,$niveau,$libelle,$catid,$libellecat;
	$niveau.="--->";
	$requete_arbo=" select $libelle,catid from categorie where parentid='$parentid' ORDER BY $libelle ";
	$arbos=$DB_site2->query($requete_arbo);
	while ($arbo=$DB_site2->fetch_array($arbos)) 
		{
		if($arbo[catid]==$catid) 
			{
			$selected="selected";
			$libellecat=$arbo[$libelle];
			$select.="<option value=\"$catid\" $selected>$niveau $libellecat</option>";
			} 
		else 
			{
			$selected="";
			$select.="<option value=\"$arbo[catid]\">$niveau $arbo[$libelle]</option>";
			arborescence($arbo[catid]);
			}
		}
	$niveau=substr($niveau , 0 , strlen($niveau)-4 );
	return ($select);
	}


echo "
<td valign=\"top\">
<table width=\"100%\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\"><tr><td>
<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\">
  <tr>
   <td>
   	<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\">
		<tr>
			<td height=\"25\" nowrap> &nbsp; <img src=\"images/arrow_2.jpg\"> <font class=\"cost\">Gestion des remises de gros</font>
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
   <td align=\"justify\" valign=\"middle\">
   	<font class=\"txt_2\">
		<table align=\"center\" width=\"100%\">
			<tr>
				<td align=\"center\" valign=\"middle\" width=\"100%\">" ;


if( !in_array( "3", $modules ) )
	echo "<script language=\"javascript\">location.href=\"index.php\";</script>";


if( !in_array( "3", $modules ) )
	echo "<script language=\"javascript\">location.href=\"index.php\";</script>";

////////////////////////////////////////////////////////////////////////////////////////////////////////
// AFFICHER LES REMISES
////////////////////////////////////////////////////////////////////////////////////////////////////////
if ( !isset($action) or $action == "" )
{
	echo "<form name=\"form_remise\" method=\"post\" action=\"remise.php\">";
	echo "<h3>Remises existantes</h3>
	<table align=\"center\" width=\"100%\" ><tr><td>
			<table align=\"center\" width=\"100%\"  class=\"table_modules_boutiques\">
			  <tr><td class=\"td_modules_boutiques\" align=\"center\"><b>Type</b></td>
				  <td class=\"td_modules_boutiques\" align=\"center\"><b>Valeur <br>(Pds, px ou qté)</b></td>
				  <td class=\"td_modules_boutiques\" align=\"center\"><b>%</b></td>
				  <td class=\"td_modules_boutiques\" align=\"center\"><b>Rayon / <bR>Ss rayon</b></td>
				  <td class=\"td_modules_boutiques\" align=\"center\"><b>Article</b></td>
				  <td class=\"td_modules_boutiques\" align=\"center\"><b>ACTION</b></td>
			  </tr>";
			$remises = $DB_site2->query( "select * from remise where actif = '1' order by typeremise, valeur" );
			$i=0;
			while ( $remise = $DB_site2->fetch_array( $remises ) )
			{
				$rowalt = "td_".getrowbg();

				echo "<tr>";	
				if( $remise["typeremise"] == '0' ) {
					echo "    <td class=\"$rowalt\" align=\"center\">Sur le poids</td>";
					$unite_valeur = "grammes";
				} elseif( $remise["typeremise"] == '1' ) {
					echo "    <td class=\"$rowalt\" align=\"center\">Sur le prix</td>";
					$unite_valeur = "€";
				} else { // $remise["typeremise"] == '2'
					echo "    <td class=\"$rowalt\" align=\"center\">Sur la quantité</td>";
					$unite_valeur = "articles";
				}
				echo "    <td class=\"$rowalt\" align=\"center\"> > $remise[valeur] $unite_valeur</td>";
				echo "    <td class=\"$rowalt\" align=\"center\">$remise[pctremise] %</td>";
				// remise sur une categorie ou un article
				$rayons = $DB_site2->query( "SELECT * from categorie c, remisecategorie rc where c.catid = rc.catid and rc.remiseid = '$remise[remiseid]'" );
				if ($DB_site2->num_rows($rayons) > 0)
					{
					echo "  <td class=\"$rowalt\" align=\"center\">" ;
					while ($rayon=$DB_site2->fetch_array($rayons))
						{
							echo "$rayon[libelleFR]<br>" ;
						}
					echo "</td>" ;
					}
				else
					{
					echo "  <td class=\"$rowalt\" align=\"center\">--</td>" ;
					}
				$articles = $DB_site2->query( "SELECT * from article a, remisearticle ra where a.artid = ra.artid and ra.remiseid = '$remise[remiseid]'" );
				if ($DB_site2->num_rows($articles) > 0)
					{
					echo "  <td class=\"$rowalt\" align=\"center\">" ;
					while ($article=$DB_site2->fetch_array($articles))
						{
						$artic = $article[libelleFR] ;
						if (strlen($artic) > 20)
							{
							$artic = substr($artic, 0, 20) ;
							}
							echo "$artic<br>" ;
						}
					echo "</td>" ;
					}
				else
					{
					echo "  <td class=\"$rowalt\" align=\"center\">--</td>" ;
					}

				echo "  <td class=\"$rowalt\" align=\"center\">
						  <a href=\"remise.php?action=editer&remiseid=$remise[remiseid]\" target=\"_self\">Editer</a>
				  		| <a href=\"remise.php?action=supprimer&remiseid=$remise[remiseid]\" target=\"_self\">Supprimer</a>
						</td>";

				$i++;
			}
			if ($i==0)
				{
				echo "<tr><td colspan=\"5\" align=\"center\"><font face=\"verdana\" size=\"2\"><br><b>Actuellement, il n'existe aucune remise</b><br><br></font></td></tr>";
				}
	echo "	</tr></table></td></tr></table><center><br>";
	echo "<div align=\"center\"><br><a href=\"remise.php?action=ajouter\" target=\"_self\">Ajouter une remise</a></div>";

}

////////////////////////////////////////////////////////////////////////////////////////////////////////
// AJOUTER UNE REMISE (action=ajouter)
////////////////////////////////////////////////////////////////////////////////////////////////////////
elseif ( $action == "ajouter" )
{
	echo "<script language=\"javascript\">
			function visualiser( type )
			{
				if( type == 0 ) {
					document.getElementById('gauche').innerHTML = \"Valeur (poids) \";
					document.getElementById('droite').innerHTML = \"grammes\";
				} else {
					if( type == 1 )
					{
						document.getElementById('gauche').innerHTML = \"Valeur (prix) \";
						document.getElementById('droite').innerHTML = \"€\";
					} else {
						if( type == 2 )
						{
							document.getElementById('gauche').innerHTML = \"Valeur (quantité) \";
							document.getElementById('droite').innerHTML = \"articles\";
						}
					}
				}			
			}
		  </script>"; 

	echo "<div align=\"center\"><h3>Ajouter une remise</h3></div>";

	if( !isset($_POST['btn_ajouter']) || $_POST['btn_ajouter'] != 'Ajouter la remise' )
	{
		echo "
			<form method=\"post\" action=\"remise.php\" name=\"form_remise\">
			<table class=\"table_remises_ajout\"><tr><td>
				<table>
				  <tr ><td  class=\"td_remises1\">Type de remise </td> 
					  <td  class=\"td_remises2\">
					  &nbsp;&nbsp;<input type=\"radio\" name=\"opt_type\" value=\"0\" checked onClick=\"javascript:visualiser(this.value);\"> sur le poids<br>
					  &nbsp;&nbsp;<input type=\"radio\" name=\"opt_type\" value=\"1\" onClick=\"javascript:visualiser(this.value);\"> sur le prix<br>
					  &nbsp;&nbsp;<input type=\"radio\" name=\"opt_type\" value=\"2\" onClick=\"javascript:visualiser(this.value);\"> sur la quantité<br>
					  </td></tr>
			  <tr>
			  <td  class=\"td_remises1\">Rayon / ss-rayon concerné</td>
			  <td  class=\"td_remises2\">" ;
			arborescence(0);
	echo "&nbsp;&nbsp;&nbsp;&nbsp;<select name=\"catid\" onchange=\"JavaScript:document.form_remise.submit();\">" ;
	echo "<option value=\"0\"</option>";
	echo "$select</select><br>" ;
	echo "</td><td></td></tr>" ;


	if (isset($catid) and $catid != 0)
		{	
		$articles = $DB_site2->query("SELECT artid, a.libelleFR
									  FROM article a, categorie c
 									  WHERE a.catid = c.catid
									   and  c.catid = '$catid'
 									  ORDER BY libelleFR ASC  ") ;
		if ($DB_site2->num_rows($articles) > 0)
			{
		echo " <tr><td  class=\"td_remises1\">Article concerné&nbsp;&nbsp;&nbsp;</td>		  
				  <td  class=\"td_remises2\">" ;
			echo "&nbsp;&nbsp;&nbsp;&nbsp;<select name=\"art\">
				<option value=\"0\"></option>" ;
				while ($article=$DB_site2->fetch_array($articles))
					{
						if ($article[artid] == $art)
							{
							$selected_art = "selected" ;
							}
						$lib = $article[libelleFR] ;
						$lib = substr($lib, 0, 50) ;
					echo "<option value=\"$article[artid]\" $selected_art>$lib</option>" ;
					}
				echo "</select>" ;
			}
		}


			echo "</td><td></td></tr>
				  <tr><td  class=\"td_remises1\"><div name=\"gauche\" ID=\"gauche\">Valeur (poids) </div></td>
					  <td class=\"td_remises2\">&nbsp;&nbsp;&nbsp;&nbsp;<input type=\"text\" size=\"5\" name=\"txt_valeur\">
					  	  <div name=\"droite\" ID=\"droite\" style=\"display:inline\">grammes</div>
					  </td></tr>
				  <tr><td  class=\"td_remises1\">Pourcentage </td>";
						$pourcentage="<option value=\"1\" selected>1%</option>";
						for ( $i=2; $i<=100; $i++ )
							$pourcentage .= "<option value=\"$i\">$i%</option>";
				echo "<td  class=\"td_remises2\">&nbsp;&nbsp;&nbsp;&nbsp;<select name=\"lst_pourcentage\">$pourcentage</select></td></tr>";
			
		echo "	</table>
		
				</td></tr></table><br>
				<center><input type=\"hidden\" name=\"action\" value=\"ajouter\">
				<input type=\"submit\" name=\"btn_ajouter\" value=\"Ajouter la remise\"></center>
			  </form><br>
			  <a href=\"remise.php\" target=\"_self\">Retour</a>";

	}
	
	elseif ( isset($_POST['btn_ajouter']) && $_POST['btn_ajouter'] == 'Ajouter la remise' )
	{
		$erreur_saisie = 0;

		echo "<table class=\"table_erreur_saisie\">";


		if( !isset( $txt_valeur ) || $txt_valeur == "" )
			{
			echo "<tr><td class=\"td_erreur_saisie\"><b>Vous devez saisir la valeur</b></td></tr>";
			$erreur_saisie = 1;
			} 
		elseif( !is_numeric( $txt_valeur ) )
			{
			echo "<tr><td class=\"td_erreur_saisie\"><b>La valeur doit être de type numérique</b></td></tr>";
			$erreur_saisie = 1;
			} 
		elseif( $txt_valeur < 1 )
			{
			echo "<tr><td class=\"td_erreur_saisie\"><b>La valeur doit être supérieure à 1</b></td></tr>";
			$erreur_saisie = 1;
			} 
		else
			{
			// On teste que cette remise n'existe pas déjà
			$remise_doublon = $DB_site2->query_first( "select * from remise
														where typeremise = '". $opt_type ."' 
														and   valeur = '". $txt_valeur ."'
														and pctremise = '" .$lst_pourcentage ."' and actif = 1" );
			$remise_article_doublon = $DB_site2 ->query_first ("SELECT * FROM  remisearticle ra, remise r 
																WHERE  ra.remiseid = r.remiseid
																AND    typeremise = '". $opt_type ."' AND   valeur = '". $txt_valeur ."'
																AND    pctremise = '" .$lst_pourcentage ."' and actif = 1" );
			$remise_article_doublon = $DB_site2 ->query_first ("SELECT * FROM  remisecategorie rc, remise r 
																WHERE rc.remiseid = r.remiseid
																AND   typeremise = '". $opt_type ."' AND   valeur = '". $txt_valeur ."'
																AND   pctremise = '" .$lst_pourcentage ."' and actif = 1" );
			if (($remise_doublon[remiseid] != "") or ($remise_article_doublon[remiseid] != "") or ($remise_article_doublon[remiseid] != ""))
			{
				echo "<tr><td class=\"td_erreur_saisie\"><b>Cette remise existe déja !</b></td></tr>";		
				$erreur_saisie = 1;
			}
		}
		echo "</table>";
		
		if( $erreur_saisie == 0 )
		{
			// Si pas d'erreur on ajoute la remise
			$rq_ajouter_remise = "INSERT INTO remise (typeremise, valeur, pctremise, actif)
								 VALUES ( '" .htmlentities($opt_type). "', '".htmlentities($txt_valeur). "', '".htmlentities($lst_pourcentage). "', '1' )";
			$DB_site2->query($rq_ajouter_remise);
			// si remise sur catégorie ou sur article
			if ($catid != 0)
				{
				$req_idremise = $DB_site2 ->query_first("select remiseid from remise where typeremise = '" .htmlentities($opt_type). "' and valeur = '".htmlentities($txt_valeur). "' and pctremise = '".htmlentities($lst_pourcentage). "' and actif = '1'");
				if ($art != 0) // remise sur un article
					{
					$rq_ajouter_remise_article = $DB_site2->query("INSERT INTO remisearticle (remiseid, artid)
																   VALUES ('$req_idremise[remiseid]', '$art')") ;
					}
				else // remise sur une catégorie
					{
					$rq_ajouter_remise_categorie = $DB_site2->query("INSERT INTO remisecategorie (remiseid, catid)
																				   VALUES ('$req_idremise[remiseid]', '$catid')") ;
					}
				}

			echo "<script language=\"javascript\">location.href=\"remise.php\";</script>";

		}
		else
		{
			// Sinon on réaffiche le formulaire
			echo "<form method=\"post\" action=\"remise.php\" name=\"form_remise\">
					<table border=\"0\">
					  <tr><td>Type de remise :</td> 
						  <td><input type=\"radio\" name=\"opt_type\" value=\"0\"";
						  		if($opt_type=="0") {
									echo " checked";
									$tmp_valeur = "Valeur (poids) :";
									$tmp_unite_valeur = "grammes";
								}
								echo " onClick=\"javascript:visualiser(this.value);\"> sur le poids<br>";
							  echo "<input type=\"radio\" name=\"opt_type\" value=\"1\"";
						  		if($opt_type=="1") {
									echo " checked";
									$tmp_valeur = "Valeur (prix) :";
									$tmp_unite_valeur = "€";
								}
								echo " onClick=\"javascript:visualiser(this.value);\"> sur le prix<br>";
							  echo "<input type=\"radio\" name=\"opt_type\" value=\"2\"";
						  		if($opt_type=="2") {
									echo " checked";
									$tmp_valeur = "Valeur (quantité) :";
									$tmp_unite_valeur = "articles";
								}
								echo " onClick=\"javascript:visualiser(this.value);\"> sur la quantité<br>";

				echo "<tr>
			  <td align=\"left\">Rayon / ss-rayon concerné</td>
			  <td colspan=\"2\">" ;
			arborescence(0);
	echo "&nbsp;&nbsp;<select name=\"catid\" onchange=\"JavaScript:document.form_remise.submit();\">" ;
	echo "<option value=\"0\"</option>";
	echo "$select
	</select><br>" ;
	echo "</td><td></td></tr>" ;


	if (isset($catid) and $catid != 0)
		{	
		$articles = $DB_site2->query("SELECT artid, a.libelleFR
									  FROM article a, categorie c
 									  WHERE a.catid = c.catid
									   and  c.catid = '$catid'
 									  ORDER BY libelleFR ASC  ") ;
		if ($DB_site2->num_rows($articles) > 0)
			{
		echo " <tr><td align=\"right\">Article concerné&nbsp;&nbsp;&nbsp;</td>		  
				  <td colspan=\"2\">" ;
			echo "&nbsp;&nbsp;<select name=\"art\">
				<option value=\"0\"></option>" ;
				while ($article=$DB_site2->fetch_array($articles))
					{
						if ($article[artid] == $art)
							{
							$selected_art = "selected" ;
							}
						$lib = $article[libelleFR] ;
						$lib = substr($lib, 0, 50) ;
					echo "<option value=\"$article[artid]\" $selected_art>$lib</option>" ;
					}
				echo "</select>" ;
			}
		}


			echo "</td><td></td></tr>" ;
				echo "<tr><td><div name=\"gauche\" ID=\"gauche\">$tmp_valeur</div></td>
						  <td>&nbsp;&nbsp;<input type=\"text\" size=\"5\" name=\"txt_valeur\" value=\"$txt_valeur\">
						  	  <div name=\"droite\" ID=\"droite\" style=\"display:inline\">$tmp_unite_valeur</div>
						  </td></tr>
					  <tr><td>Pourcentage :</td>";
						for ( $i=1; $i<=100; $i++ )
						{
							if( $i == $lst_pourcentage )
								$selected = "selected";
							else
								$selected = "";	
							$pourcentage .= "<option value=\"$i\" $selected>$i%</option>";
						}
				echo "<td>&nbsp;&nbsp;<select name=\"lst_pourcentage\">$pourcentage</select></td></tr>";
				
		echo "	</table>
				<input type=\"hidden\" name=\"action\" value=\"ajouter\">
				<input type=\"submit\" name=\"btn_ajouter\" value=\"Ajouter la remise\">
			  </form>";
		}
	}
}

////////////////////////////////////////////////////////////////////////////////////////////////////////
// EDITER UNE REMISE (action=editer)
////////////////////////////////////////////////////////////////////////////////////////////////////////
elseif ( $action == "editer" )
{
	echo "<script language=\"javascript\">
			function visualiser( type )
			{
				if( type == 0 ) {
					document.getElementById('gauche').innerHTML = \"Valeur (poids) :\";
					document.getElementById('droite').innerHTML = \"grammes\";
				} else {
					if( type == 1 )
					{
						document.getElementById('gauche').innerHTML = \"Valeur (prix) :\";
						document.getElementById('droite').innerHTML = \"€\";
					} else {
						if( type == 2 )
						{
							document.getElementById('gauche').innerHTML = \"Valeur (quantité) :\";
							document.getElementById('droite').innerHTML = \"articles\";
						}
					}
				}			
			}
		  </script>"; 
	echo "<h3>Editer une remise</h3>";
	// Premier affichage de la page de modification
	if( !isset($_POST['btn_editer']) || $_POST['btn_editer'] != 'Editer la remise' )
	{
		$rs_remise = $DB_site2->query_first("select * from remise where remiseid = $remiseid");
		echo "<form method=\"post\" action=\"remise.php\" name=\"form_remise\">
				<table class=\"table_remises\" border=\"0\">
				  <tr><td class=\"td_remises1\">Type de remise : </td> 
					  <td class=\"td_remises2\"><input type=\"radio\" name=\"opt_type\" value=\"0\"";
							if($rs_remise[typeremise]=="0") {
								echo " checked";
								$tmp_valeur = "Valeur (poids) : ";
								$tmp_unite_valeur = "grammes";
							}
							echo " onClick=\"javascript:visualiser(this.value);\"> Sur le poids<br>";
						  echo "<input type=\"radio\" name=\"opt_type\" value=\"1\"";
							if($rs_remise[typeremise]=="1") {
								echo " checked";
								$tmp_valeur = "Valeur (prix) : ";
								$tmp_unite_valeur = "€";
							}
							echo " onClick=\"javascript:visualiser(this.value);\"> Sur le prix<br>";
						  echo "<input type=\"radio\" name=\"opt_type\" value=\"2\"";
							if($rs_remise[typeremise]=="2") {
								echo " checked";
								$tmp_valeur = "Valeur (quantité) : ";
								$tmp_unite_valeur = "articles";
							}
							echo " onClick=\"javascript:visualiser(this.value);\"> Sur la quantité<br>";
			$rq_remise_cat = $DB_site2->query_first("select c.catid, c.libelleFR from remisecategorie rc, categorie c where c.catid = rc.catid and remiseid = '$remiseid'");
			if ($rq_remise_cat[catid] != "")
				{
				echo "<tr><td  class=\"td_remises1\">Rayon / ss-rayon concerné</td>" ;
				echo "<td class=\"td_remises2\">&nbsp;&nbsp;$rq_remise_cat[libelleFR]</td></tr>" ;
				}
			$rq_remise_art = $DB_site2->query_first("select a.artid, a.libelleFR from remisearticle ra, article a where a.artid = ra.artid and remiseid = '$remiseid'");
			if ($rq_remise_art[artid] != "")
				{
				echo " <tr><td  class=\"td_remises1\">Article concerné</td>" ;
				echo "<td class=\"td_remises2\">&nbsp;&nbsp;$rq_remise_art[libelleFR]</td></tr>" ;
				}
			echo "<tr><td  class=\"td_remises1\"><div name=\"gauche\" ID=\"gauche\">$tmp_valeur</div></td>
					  <td  class=\"td_remises2\">&nbsp;&nbsp;<input type=\"text\" size=\"5\" name=\"txt_valeur\" value=\"$rs_remise[valeur]\">
					  	  <div name=\"droite\" ID=\"droite\" style=\"display:inline\">$tmp_unite_valeur</div>
					  </td></tr>
				  <tr><td  class=\"td_remises1\">Pourcentage :</td>";
					for ( $i=1; $i<=100; $i++ )
					{
						if( $i == $rs_remise[pctremise] )
							$selected = "selected";
						else
							$selected = "";	
						$pourcentage .= "<option value=\"$i\" $selected>$i%</option>";
					}
			echo "<td class=\"td_remises2\">&nbsp;&nbsp;<select name=\"lst_pourcentage\">$pourcentage</select></td></tr>
				</table>
				<input type=\"hidden\" name=\"action\" value=\"editer\">
				<input type=\"hidden\" name=\"remiseid\" value=\"$remiseid\">
				<br>
				<input type=\"submit\" name=\"btn_editer\" value=\"Editer la remise\">
			  </form>";
	}
	
	// Si on a validé les modifications
	elseif ( isset($_POST['btn_editer']) && $_POST['btn_editer'] == 'Editer la remise' )
	{
		$erreur_saisie = 0;

		echo "<table class=\"table_erreur_saisie\" align=\"center\">";
		
		if( !isset( $txt_valeur ) || $txt_valeur == "" )
		{
			echo "<tr><td class=\"td_erreur_saisie\"><b>Vous devez saisir la valeur</b></td></tr>";
			$erreur_saisie = 1;
		} elseif( !is_numeric( $txt_valeur ) )
		{
			echo "<tr><td class=\"td_erreur_saisie\"><b>La valeur doit être de type numérique</b></td></tr>";
			$erreur_saisie = 1;
		} elseif( $txt_valeur < 1 )
		{
			echo "<tr><td class=\"td_erreur_saisie\"><b>La valeur doit être supérieure à 1</b></td></tr>";
			$erreur_saisie = 1;
		} else
		{
			// On teste que cette remise n'existe pas déja
			$remise_doublon = $DB_site2->query_first( "select * from remise
														where typeremise = '". $opt_type ."'
														and   valeur = '". $txt_valeur ."'
														and   pctremise = '" .$lst_pourcentage ."'" );

			if( $remise_doublon[remiseid] != "" )
			{
				echo "<tr><td class=\"td_erreur_saisie\"><b>Cette remise existe déja !</b></td></tr>";		
				$erreur_saisie = 1;
			}
		}
		echo "</table>";
		
		if( $erreur_saisie == 0 )
		{
			// Si pas d'erreur on édite la remise
			$rq_editer_remise = "UPDATE remise
								 SET typeremise = '" .htmlentities($opt_type). "',
								 	 valeur = '" .htmlentities($txt_valeur). "',
								 	 pctremise = '" .htmlentities($lst_pourcentage). "' 
								 WHERE remiseid = $remiseid";
			$DB_site2->query($rq_editer_remise);

			echo "<script language=\"javascript\">location.href=\"remise.php\";</script>";

		}
		else
		{
			// Sinon on réaffiche le formulaire
			echo "<form method=\"post\" action=\"remise.php\" name=\"form_remise\">
					<table border=\"0\">
					  <tr><td>Type de remise :</td> 
						  <td><input type=\"radio\" name=\"opt_type\" value=\"0\"";
								if($opt_type=="0") {
									echo " checked";
									$tmp_valeur = "Valeur (poids) :";
									$tmp_unite_valeur = "grammes";
								}
								echo " onClick=\"javascript:visualiser(this.value);\"> sur le poids<br>";
							  echo "<input type=\"radio\" name=\"opt_type\" value=\"1\"";
								if($opt_type=="1") {
									echo " checked";
									$tmp_valeur = "Valeur (prix) :";
									$tmp_unite_valeur = "€";
								}
								echo " onClick=\"javascript:visualiser(this.value);\"> sur le prix<br>";
							  echo "<input type=\"radio\" name=\"opt_type\" value=\"2\"";
								if($opt_type=="2") {
									echo " checked";
									$tmp_valeur = "Valeur (quantité) :";
									$tmp_unite_valeur = "articles";
								}
								echo " onClick=\"javascript:visualiser(this.value);\"> sur la quantité<br>";
				echo "<tr><td><div name=\"gauche\" ID=\"gauche\">$tmp_valeur</div></td>
						  <td><input type=\"text\" size=\"5\" name=\"txt_valeur\" value=\"$txt_valeur\">
						      <div name=\"droite\" ID=\"droite\" style=\"display:inline\">$tmp_unite_valeur</div>
						  </td></tr>
					  <tr><td>Pourcentage :</td>";
						for ( $i=1; $i<=100; $i++ )
						{
							if( $i == $lst_pourcentage )
								$selected = "selected";
							else
								$selected = "";	
							$pourcentage .= "<option value=\"$i\" $selected>$i%</option>";
						}
				echo "<td><select name=\"lst_pourcentage\">$pourcentage</select></td></tr>
					</table>
					<input type=\"hidden\" name=\"action\" value=\"editer\">
					<input type=\"hidden\" name=\"remiseid\" value=\"$remiseid\">
					<input type=\"submit\" name=\"btn_editer\" value=\"Editer la remise\">
				  </form>
			  <a href=\"remise.php\" target=\"_self\">Retour</a>";
		}
	}
}

////////////////////////////////////////////////////////////////////////////////////////////////////////
// SUPPRIMER UNE REMISE (action=supprimer)
////////////////////////////////////////////////////////////////////////////////////////////////////////
elseif ( $action == "supprimer" )
{
	// Confirmation de suppression
	echo "<bR>Etes vous sûr de vouloir supprimer cette remise ?<br><br>
		  <a href=\"remise.php?action=supprimer2&remiseid=$remiseid\">Oui</a>
		  | <a href=\"remise.php\">Non</a><bR>";
}
////////////////////////////////////////////////////////////////////////////////////////////////////////
// SUPPRIMER UNE REMISE (action=supprimer2)
////////////////////////////////////////////////////////////////////////////////////////////////////////	
elseif( $action == "supprimer2" )
{
	$rq_supprimer = "UPDATE remise SET actif = 0 WHERE remiseid = '$remiseid'";
	$DB_site2->query($rq_supprimer) ;

	echo "<script language=\"javascript\">location.href=\"remise.php\";</script>";
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