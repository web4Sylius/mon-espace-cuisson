<?php

require "admin_global.php";
echo "<script language=\"javascript\"> 
<!-- 
window.moveTo(420,375);

function fermer(id)
	{
	if (id != 0)
		{
		window.opener.document.formu.chp2.value = id  ;
		window.opener.document.formu.action2.value = 'saisie' ;
		window.opener.document.formu.old.value = 'saisie' ;
		window.opener.document.formu.submit () ;
		} 
	window.close() ;
	} 
//-->
</script>" ;

echo "
<html>
<LINK href=\"vendre-en-ligne.css\" type=\"text/css\" rel=\"stylesheet\">
<body>
<table width=\"100%\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\"><tr><td>
<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\">
  <tr>
   <td>
   	<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\">
		<tr>
			<td height=\"25\" nowrap> &nbsp; <img src=\"images/arrow_2.jpg\"> <font class=\"cost\">Ajouter un champ</font>
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
   <td align=\"center\" valign=\"middle\">" ;
if (isset($action) and $action == "ajouter2")
	{
	if ($lib_champFR == "" or $nom_champ == "" or $type_champ == "" or $oblig_champ == "")
		{
		echo "Tous les champs sont obligatoires" ;
		}
	else
		{
		$pos=$DB_site2->query_first("select max(position) from article_champ where artid = '$artid'");
		if ($pos[0] == "")
			{
			$position = 1 ;
			}
		else
			{
			$position = $pos[0] + 1 ;
			}
		$DB_site2->query("insert into article_champ (artid, libelleFR, libelleDE, libelleEN, libelleIT, libelleSP, nom, type, 
							obligatoire, position) values ('$artid', '$lib_champFR', '$lib_champDE', '$lib_champEN', '$lib_champIT', 
							'$lib_champSP', '$nom_champ', '$type_champ', '$oblig_champ', '$position')");
		$insertid=$DB_site2->insert_id() ;
		echo "<font class=\"cost\">Champ ajouté</font><br>" ;
		switch ($type_champ)
			{
			case "1": // boîte texte
				echo "Vous devez définir une hauteur et une largeur pour la boîte de texte.<br>
					Si vous ne savez pas, laissez les valeurs par défaut. Vous pourrez les modifier ultérieurement.<br><br>
					<form method=\"post\">
						<input type=\"hidden\" name=\"action\" value=\"valeurs\">
						<input type=\"hidden\" name=\"champ\" value=\"$insertid\">
						<input type=\"hidden\" name=\"artid\" value=\"$artid\">
						<table align=\"center\">
							<tr>
								<td align=\"right\">Largeur : </td>
								<td align=\"left\"><input type=\"text\" name=\"largeur\" value=\"25\" size=\"2\"></td>
							</tr>
							<tr>
								<td align=\"right\">Hauteur : </td>
								<td align=\"left\"><input type=\"text\" name=\"hauteur\" value=\"3\" size=\"2\"></td>
							</tr>
						</table>
						<input type=\"submit\" value=\"Enregistrer\">
					</form>" ;
				break ;
			case "2": // Boutons radio
				echo "Vous devez définir des valeurs pour les boutons radio.<br>
					Nombre de valeurs : <form method=\"post\">
					<input type=\"hidden\" name=\"action\" value=\"valeurs\">
					<input type=\"hidden\" name=\"artid\" value=\"$artid\">
					<input type=\"hidden\" name=\"champ\" value=\"$insertid\">
					<input type=\"text\" name=\"nb\" value=\"2\" size=\"1\"> 
					<input type=\"submit\" value=\"Créer\"></form>" ;
				break ;
			case "3": // Cases à cocher
				echo "Vous devez définir des valeurs pour les cases à cocher.<br>
					<form method=\"post\">
					<input type=\"hidden\" name=\"action\" value=\"valeurs\">
					<input type=\"hidden\" name=\"artid\" value=\"$artid\">
					<input type=\"hidden\" name=\"champ\" value=\"$insertid\">
					Nombre de valeurs : <input type=\"text\" name=\"nb\" value=\"2\" size=\"1\"> 
					<input type=\"submit\" value=\"Créer\">
					</form>
					" ;
				break ;
			case "4": // Champ texte
				echo "Vous devez définir une longueur et un nombre de caractères maximum pour le champ texte.<br>
					Si vous ne savez pas, laissez les valeurs par défaut. Vous pourrez les modifier ultérieurement.<br><br>
					<form method=\"post\">
						<input type=\"hidden\" name=\"action\" value=\"valeurs\">
						<input type=\"hidden\" name=\"artid\" value=\"$artid\">
						<input type=\"hidden\" name=\"champ\" value=\"$insertid\">
						<table align=\"center\">
							<tr>
								<td align=\"right\">Longueur : </td>
								<td align=\"left\"><input type=\"text\" name=\"longueur\" value=\"20\" size=\"2\"></td>
							</tr>
							<tr>
								<td align=\"right\">Taille : </td>
								<td align=\"left\"><input type=\"text\" name=\"taille\" value=\"100\" size=\"2\"></td>
							</tr>
						</table>
						<input type=\"submit\" value=\"Enregistrer\">
					</form>" ;
				break ;
			case "5": // Liste à sélection unique
				echo "Vous devez définir des valeurs pour la liste à sélection unique.<br>
					<form method=\"post\">
					<input type=\"hidden\" name=\"action\" value=\"valeurs\">
					<input type=\"hidden\" name=\"artid\" value=\"$artid\">
					<input type=\"hidden\" name=\"champ\" value=\"$insertid\">
					Nombre de valeurs : <input type=\"text\" name=\"nb\" value=\"2\" size=\"1\"> 
					<input type=\"submit\" value=\"Créer\">
					</form>" ;
				break ;
			case "6": // Liste à sélections multiples
				echo "Vous devez définir des valeurs pour la liste à sélections multiples.<br>
					<form method=\"post\">
					<input type=\"hidden\" name=\"action\" value=\"valeurs\">
					<input type=\"hidden\" name=\"artid\" value=\"$artid\">
					<input type=\"hidden\" name=\"champ\" value=\"$insertid\">
					Nombre de valeurs : <input type=\"text\" name=\"nb\" value=\"2\" size=\"1\"> 
					<input type=\"submit\" value=\"Créer\">
					</form>" ;
				break ;
			case "7": // Insertion de fichier
				echo "Vous devez définir une longueur pour le champ insertion de fichier.<br>
					Si vous ne savez pas, laissez la valeur par défaut.<br><br>
					<form method=\"post\">
						<input type=\"hidden\" name=\"action\" value=\"valeurs\">
						<input type=\"hidden\" name=\"artid\" value=\"$artid\">
						<input type=\"hidden\" name=\"champ\" value=\"$insertid\">
						<table align=\"center\">
							<tr>
								<td align=\"right\">Longueur : </td>
								<td align=\"left\"><input type=\"text\" name=\"longueur\" value=\"20\" size=\"2\"></td>
							</tr>
						<input type=\"submit\" value=\"Enregistrer\">
					</form>" ;
				break ;
			}
		}
	}


if (isset($action) and $action == "valeurs")
	{
	$type=$DB_site2->query_first("select type from article_champ where articlechampid = '$champ'");
	switch ($type[type])
		{
		case "1": // boîte texte
			$DB_site2->query("insert into article_champ_valeur (articlechampid, libelle, description, valeur) values ('$champ', 'cols', 'Largeur', '$largeur')");
			$DB_site2->query("insert into article_champ_valeur (articlechampid, libelle, description, valeur) values ('$champ', 'rows', 'Hauteur', '$hauteur')");
			$action = "quitter" ; 
			break ;
		case "2": // Boutons radio
			if (isset($action3) and $action3 == "ok")
				{
				$i = 0 ;
				while ($i < count($val))
					{
					$DB_site2->query("insert into article_champ_valeur (articlechampid, libelle, description, valeur) values ('$champ', 'value', '$val[$i]', '$i')");
					$i++ ;
					}
				$action = "quitter" ; 
				}
			else
				{
				echo "<form method=\"post\">
					<input type=\"hidden\" name=\"action3\" value=\"ok\">
					<input type=\"hidden\" name=\"artid\" value=\"$artid\">
					<input type=\"hidden\" name=\"action\" value=\"valeurs\">
					<input type=\"hidden\" name=\"champ\" value=\"$champ\">
					<table align=\"center\">" ;
					$i = 0 ;
					while ($i < $nb)
						{
						echo "	<tr>
								<td align=\"right\">Valeur $i : </td>
								<td align=\"left\"><input type=\"text\" name=\"val[$i]\" value=\"\" size=\"20\"></td>
							</tr>" ;
						$i++ ;
						}
					echo "</table>
					<input type=\"submit\" value=\"Enregistrer\">
				</form>" ;
				}
			break ;
		case "3": // Cases à cocher
			if (isset($action3) and $action3 == "ok")
				{
				$i = 0 ;
				while ($i < count($val))
					{
					$DB_site2->query("insert into article_champ_valeur (articlechampid, libelle, description, valeur) values ('$champ', 'value', '$val[$i]', '$i')");
					$i++ ;
					}
				$action = "quitter" ; 
				}
			else
				{
				echo "<form method=\"post\">
					<input type=\"hidden\" name=\"action3\" value=\"ok\">
					<input type=\"hidden\" name=\"artid\" value=\"$artid\">
					<input type=\"hidden\" name=\"action\" value=\"valeurs\">
					<input type=\"hidden\" name=\"champ\" value=\"$champ\">
					<table align=\"center\">" ;
					$i = 0 ;
					while ($i < $nb)
						{
						echo "	<tr>
								<td align=\"right\">Valeur $i : </td>
								<td align=\"left\"><input type=\"text\" name=\"val[$i]\" value=\"\" size=\"20\"></td>
							</tr>" ;
						$i++ ;
						}
					echo "</table>
					<input type=\"submit\" value=\"Enregistrer\">
				</form>" ;
				}
			break ;
		case "4": // Champ texte
			$DB_site2->query("insert into article_champ_valeur (articlechampid, libelle, description, valeur) values ('$champ', 'size', 'Longueur', '$longueur')");
			$DB_site2->query("insert into article_champ_valeur (articlechampid, libelle, description, valeur) values ('$champ', 'maxlength', 'Taille', '$taille')");
			$action = "quitter" ; 
			break ;
		case "5": // Liste à sélection unique
			if (isset($action3) and $action3 == "ok")
				{
				$i = 0 ;
				while ($i < count($val))
					{
					$DB_site2->query("insert into article_champ_valeur (articlechampid, libelle, description, valeur) values ('$champ', 'value', '$val[$i]', '$i')");
					$i++ ;
					}
				$action = "quitter" ; 
				}
			else
				{
				echo "<form method=\"post\">
					<input type=\"hidden\" name=\"action3\" value=\"ok\">
					<input type=\"hidden\" name=\"artid\" value=\"$artid\">
					<input type=\"hidden\" name=\"action\" value=\"valeurs\">
					<input type=\"hidden\" name=\"champ\" value=\"$champ\">
					<table align=\"center\">" ;
					$i = 0 ;
					while ($i < $nb)
						{
						echo "	<tr>
								<td align=\"right\">Valeur $i : </td>
								<td align=\"left\"><input type=\"text\" name=\"val[$i]\" value=\"\" size=\"20\"></td>
							</tr>" ;
						$i++ ;
						}
					echo "</table>
					<input type=\"submit\" value=\"Enregistrer\">
				</form>" ;
				}
			break ;
		case "6": // Liste à sélections multiples
			if (isset($action3) and $action3 == "ok")
				{
				$i = 0 ;
				while ($i < count($val))
					{
					$DB_site2->query("insert into article_champ_valeur (articlechampid, libelle, description, valeur) values ('$champ', 'value', '$val[$i]', '$i')");
					$i++ ;
					}
				$action = "quitter" ; 
				}
			else
				{
				echo "<form method=\"post\">
					<input type=\"hidden\" name=\"action3\" value=\"ok\">
					<input type=\"hidden\" name=\"action\" value=\"valeurs\">
					<input type=\"hidden\" name=\"artid\" value=\"$artid\">
					<input type=\"hidden\" name=\"champ\" value=\"$champ\">
					<table align=\"center\">" ;
					$i = 0 ;
					while ($i < $nb)
						{
						echo "	<tr>
								<td align=\"right\">Valeur $i : </td>
								<td align=\"left\"><input type=\"text\" name=\"val[$i]\" value=\"\" size=\"20\"></td>
							</tr>" ;
						$i++ ;
						}
					echo "</table>
					<input type=\"submit\" value=\"Enregistrer\">
				</form>" ;
				}
			break ;
		case "7": // Insertion de fichier
			$DB_site2->query("insert into article_champ_valeur (articlechampid, libelle, description, valeur) values ('$champ', 'size', 'Longueur', '$longueur')");
			$action = "quitter" ; 
			break ;
		}
	}

if (isset($action) and $action == "quitter")
	{
	echo "<script language=\"javascript\">fermer($champ) ;</script>" ;
	}

if (!isset($action) or $action == "")
	{
	$types_champs=$DB_site2->query("SELECT * FROM article_type_champ");
	$liste_types = "" ;
	while ($type_champ=$DB_site2->fetch_array($types_champs))
		{
		$liste_types .= "<option value=\"$type_champ[articletypechampid]\">$type_champ[nom]</option>" ;
		}
	
	echo "	<form method=\"post\" enctype=\"multipart/form-data\" style=\"display:inline\">
		<input type=\"hidden\" name=\"action\" value=\"ajouter2\">
			<br><table>" ;
			if(in_array("FR" , $langues))
				{
				echo "<tr>
					<td>Texte affiché FR : </td>
					<td><input type=\"text\" name=\"lib_champFR\" value=\"$lib_champFR\" size=\"25\"></td>
				</tr>" ;
				}
			if(in_array("DE" , $langues))
				{
				echo "<tr>
					<td>Texte affiché DE : </td>
					<td><input type=\"text\" name=\"lib_champDE\" value=\"$lib_champDE\" size=\"25\"></td>
				</tr>" ;
				}
			if(in_array("EN" , $langues))
				{
				echo "<tr>
					<td>Texte affiché EN : </td>
					<td><input type=\"text\" name=\"lib_champEN\" value=\"$lib_champEN\" size=\"25\"></td>
				</tr>" ;
				}
			if(in_array("IT" , $langues))
				{
				echo "<tr>
					<td>Texte affiché IT : </td>
					<td><input type=\"text\" name=\"lib_champIT\" value=\"$lib_champIT\" size=\"25\"></td>
				</tr>" ;
				}
			if(in_array("SP" , $langues))
				{
				echo "<tr>
					<td>Texte affiché SP : </td>
					<td><input type=\"text\" name=\"lib_champSP\" value=\"$lib_champSP\" size=\"25\"></td>
				</tr>" ;
				}
			echo "<tr>
				<td>Nom : </td>
				<td><input type=\"text\" name=\"nom_champ\" value=\"$nom_champ\" size=\"25\" maxlength=\"20\"></td>
			</tr>
			<tr>
				<td>Type : </td>
				<td><select name=\"type_champ\">$liste_types</select></td>
			</tr>
			<tr>
				<td>Obligatoire : </td>
				<td><input type=\"radio\" name=\"oblig_champ\" value=\"1\" checked> Oui<bR><input type=\"radio\" name=\"oblig_champ\" value=\"0\"> Non</td>
			</tr>
		</table>
		<br>
		<input type=\"submit\" value=\"Ajouter\">&nbsp;&nbsp;<input type=\"button\" value=\"Retour\" onclick=\"Javascript: fermer(0) ;\"></form>" ;
	}
	
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
</body>
</html>";
			
?>
