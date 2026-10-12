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
			<td height=\"25\" nowrap> &nbsp; <img src=\"images/arrow_2.jpg\"> <font class=\"cost\">Formulaire personnalisé</font>
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

echo "<h3>Personnaliser mon formulaire</h3>
Vous pouvez utiliser un formulaire personnel pour, par exemple, réaliser une enquête sur votre boutique.<br>
Sélectionnez les champs que vous souhaitez afficher et mettez le formulaire en ligne, les réponses vous seront envoyées par e-mail. 
Vous pouvez le désactiver à tout moment !<br><br>" ;


	// Activer un formulaire 
	if (isset ($action) and $action == "activer")
		{
		$DB_site2->query("update formulaire set actif = '1' where formulaireid = '$formu'");						
		echo "<script language=\"javascript\">location.href=\"formulaire.php\";</script>";
		}

	// Desactiver un formulaire 
	if (isset ($action) and $action == "desactiver")
		{
		$DB_site2->query("update formulaire set actif = '0' where formulaireid = '$formu'");						
		echo "<script language=\"javascript\">location.href=\"formulaire.php\";</script>";
		}

	// Ajouter un formulaire (2)
	if (isset ($action) and $action == "ajtform2")
		{
		if ($nom != "" and $descript != "")
			{
			$DB_site2->query("insert into formulaire (nom, description) values ('$nom', '$descript')");						
			echo "<script language=\"javascript\">location.href=\"formulaire.php\";</script>";
			}
		else
			{
			$action = "ajtform" ;
			}
		}

	// Ajouter un formulaire 
	if (isset ($action) and $action == "ajtform")
		{
		echo "<br><br><strong>Ajouter</strong><br><br>
		<form method=\"post\" action=\"formulaire.php\">
		<input type=\"hidden\" name=\"action\" value=\"ajtform2\">
		<table class=\"table_moyen_paiement\">
		<tr><td>Nom : </td><td><input type=\"text\" name=\"nom\" value=\"$nom\" size=\"20\"></td></tr>
		<tr><td>Description : </td><td><input type=\"text\" name=\"descript\" value=\"$descript\" size=\"40\"></td></tr>
		</table>
		<input type=\"submit\" value=\"Ajouter\">
		</form><br><br>
		<a href=\"formulaire.php\">Retour</a>" ;
		}

	// Modifier le formu (1)
	if (isset($action) and $action == "modifform1")
		{
		$DB_site2->query("update formulaire set nom = '$nom', description = '$descript' where formulaireid = '$formu'");						
		$action = "modif" ;
		}

	// ajouter un champ
	if (isset($action) and $action == "ajoutchamp")
		{
		if ($lib_champFR != "" and $nom_champ != "" and $type_champ != "" and $oblig_champ != "")
			{
			$pos=$DB_site2->query_first("select max(position) from formulaire_champ where formulaireid = '$formu'");
			$position = $pos[0] + 1 ;
			$DB_site2->query("insert into formulaire_champ (formulaireid, libelleFR, libelleDE, libelleEN, libelleIT, libelleSP, 
								nom, type, obligatoire, position) values ('$formu', '$lib_champFR', '$lib_champDE', '$lib_champEN', 
								'$lib_champIT', '$lib_champSP', '$nom_champ', '$type_champ', '$oblig_champ', '$position')");
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
							<input type=\"hidden\" name=\"formu\" value=\"$formu\">
							<table align=\"center\">
								<tr>
									<td align=\"right\">Largeur : </td>
									<td align=\"left\"><input type=\"text\" name=\"largeur\" value=\"40\" size=\"2\"></td>
								</tr>
								<tr>
									<td align=\"right\">Hauteur : </td>
									<td align=\"left\"><input type=\"text\" name=\"hauteur\" value=\"6\" size=\"2\"></td>
								</tr>
							</table>
							<input type=\"submit\" value=\"Enregistrer\">
						</form>" ;
					break ;
				case "2": // Boutons radio
					echo "Vous devez définir des valeurs pour les boutons radio.<br>
						Nombre de valeurs : <form method=\"post\">
						<input type=\"hidden\" name=\"action\" value=\"valeurs\">
						<input type=\"hidden\" name=\"formu\" value=\"$formu\">
						<input type=\"hidden\" name=\"champ\" value=\"$insertid\">
						<input type=\"text\" name=\"nb\" value=\"2\" size=\"1\"> 
						<input type=\"submit\" value=\"Créer\"></form>" ;
					break ;
				case "3": // Cases à cocher
					echo "Vous devez définir des valeurs pour les cases à cocher.<br>
						<form method=\"post\">
						<input type=\"hidden\" name=\"action\" value=\"valeurs\">
						<input type=\"hidden\" name=\"formu\" value=\"$formu\">
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
							<input type=\"hidden\" name=\"formu\" value=\"$formu\">
							<input type=\"hidden\" name=\"champ\" value=\"$insertid\">
							<table align=\"center\">
								<tr>
									<td align=\"right\">Longueur : </td>
									<td align=\"left\"><input type=\"text\" name=\"longueur\" value=\"30\" size=\"2\"></td>
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
						<input type=\"hidden\" name=\"formu\" value=\"$formu\">
						<input type=\"hidden\" name=\"champ\" value=\"$insertid\">
						Nombre de valeurs : <input type=\"text\" name=\"nb\" value=\"2\" size=\"1\"> 
						<input type=\"submit\" value=\"Créer\">
						</form>" ;
					break ;
				case "6": // Liste à sélections multiples
					echo "Vous devez définir des valeurs pour la liste à sélections multiples.<br>
						<form method=\"post\">
						<input type=\"hidden\" name=\"action\" value=\"valeurs\">
						<input type=\"hidden\" name=\"formu\" value=\"$formu\">
						<input type=\"hidden\" name=\"champ\" value=\"$insertid\">
						Nombre de valeurs : <input type=\"text\" name=\"nb\" value=\"2\" size=\"1\"> 
						<input type=\"submit\" value=\"Créer\">
						</form>" ;
					break ;
				}
			}
		}
	
	// ajouter des valeurs pour un champ
	if (isset($action) and $action == "valeurs")
		{
		$type=$DB_site2->query_first("select type from formulaire_champ where formulairechampid = '$champ'");
		switch ($type[type])
			{
			case "1": // boîte texte
				$DB_site2->query("insert into formulaire_champ_valeur (formulairechampid, libelle, description, valeur) values ('$champ', 'cols', 'Largeur', '$largeur')");
				$DB_site2->query("insert into formulaire_champ_valeur (formulairechampid, libelle, description, valeur) values ('$champ', 'rows', 'Hauteur', '$hauteur')");
				$action = "modif" ; 
				break ;
			case "2": // Boutons radio
				if (isset($action3) and $action3 == "ok")
					{
					$i = 0 ;
					while ($i < count($val))
						{
						$DB_site2->query("insert into formulaire_champ_valeur (formulairechampid, libelle, description, valeur) values ('$champ', 'value', '$val[$i]', '$i')");
						$i++ ;
						}
					$action = "modif" ; 
					}
				else
					{
					echo "<form method=\"post\">
						<input type=\"hidden\" name=\"action3\" value=\"ok\">
						<input type=\"hidden\" name=\"formu\" value=\"$formu\">
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
						$DB_site2->query("insert into formulaire_champ_valeur (formulairechampid, libelle, description, valeur) values ('$champ', 'value', '$val[$i]', '$i')");
						$i++ ;
						}
					$action = "modif" ; 
					}
				else
					{
					echo "<form method=\"post\">
						<input type=\"hidden\" name=\"action3\" value=\"ok\">
						<input type=\"hidden\" name=\"formu\" value=\"$formu\">
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
				$DB_site2->query("insert into formulaire_champ_valeur (formulairechampid, libelle, description, valeur) values ('$champ', 'size', 'Longueur', '$longueur')");
				$DB_site2->query("insert into formulaire_champ_valeur (formulairechampid, libelle, description, valeur) values ('$champ', 'maxlength', 'Taille', '$taille')");
				$action = "modif" ; 
				break ;
			case "5": // Liste à sélection unique
				if (isset($action3) and $action3 == "ok")
					{
					$i = 0 ;
					while ($i < count($val))
						{
						$DB_site2->query("insert into formulaire_champ_valeur (formulairechampid, libelle, description, valeur) values ('$champ', 'value', '$val[$i]', '$i')");
						$i++ ;
						}
					$action = "modif" ; 
					}
				else
					{
					echo "<form method=\"post\">
						<input type=\"hidden\" name=\"action3\" value=\"ok\">
						<input type=\"hidden\" name=\"formu\" value=\"$formu\">
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
						$DB_site2->query("insert into formulaire_champ_valeur (formulairechampid, libelle, description, valeur) values ('$champ', 'value', '$val[$i]', '$i')");
						$i++ ;
						}
					$action = "modif" ; 
					}
				else
					{
					echo "<form method=\"post\">
						<input type=\"hidden\" name=\"action3\" value=\"ok\">
						<input type=\"hidden\" name=\"action\" value=\"valeurs\">
						<input type=\"hidden\" name=\"formu\" value=\"$formu\">
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
			}
		}

	// supprimer un champ
	if (isset($action) and $action == "suppr")
		{
		echo "Etes-vous sûr(e) de vouloir supprimer ce champ ?<br><br>
		<a href=\"formulaire.php?action=suppr2&champid=$champid&formu=$formu\">OUI</a> | <a href=\"formulaire.php?action=modif&formu=$formu\">NON</a>" ;
		}
	
	// supprimer un champ (2)
	if (isset($action) and $action == "suppr2")
		{
		$position_page=$DB_site2->query_first("SELECT position FROM formulaire_champ WHERE formulairechampid = '$champid'");
		$position_page = $position_page[0];
		$rq_positions_suivantes = $DB_site2->query("SELECT formulairechampid, position FROM formulaire_champ WHERE position > $position_page" );
		while ($rs_positions_suivantes=$DB_site2->fetch_array($rq_positions_suivantes)) 
			{
			$position_temp = $rs_positions_suivantes[position] - 1;
			$DB_site2->query("UPDATE formulaire_champ SET position = '$position_temp' WHERE formulairechampid = '$rs_positions_suivantes[formulairechampid]'");
			}
		$DB_site2->query("delete from formulaire_champ_valeur where formulairechampid = '$champid'");
		$DB_site2->query("delete from formulaire_champ where formulairechampid = '$champid'");
		echo "<font class=\"cost\">Champ supprimé</font><br>" ;
		$action = "modif" ;
		}

	// MONTER D'UNE POSITION 
	if (isset ($action) and $action == "up")
		{
		$position=$DB_site2->query_first("SELECT position FROM formulaire_champ WHERE formulairechampid = '$champid'");						
		$nouvelle_position_faq = $position[0] - 1 ;
		$position2=$DB_site2->query_first("SELECT formulairechampid FROM formulaire_champ WHERE position = '$nouvelle_position_faq'");
		$DB_site2->query("UPDATE formulaire_champ SET position = '$nouvelle_position_faq' WHERE formulairechampid = '$champid'");
		$DB_site2->query("UPDATE formulaire_champ SET position = '$position[0]' WHERE formulairechampid = '$position2[formulairechampid]'");
		$action = "modif" ;
		}
	
	// DESCENDRE D'UNE POSITION 
	if (isset ($action) and $action == "down")
		{
		$position=$DB_site2->query_first("SELECT position FROM formulaire_champ WHERE formulairechampid = '$champid'");						
		$nouvelle_position_faq = $position[0] + 1 ;
		$position2=$DB_site2->query_first("SELECT formulairechampid FROM formulaire_champ WHERE position = '$nouvelle_position_faq'" );
		$DB_site2->query("UPDATE formulaire_champ SET position = '$nouvelle_position_faq' WHERE formulairechampid = '$champid'");
		$DB_site2->query("UPDATE formulaire_champ SET position = '$position[0]' WHERE formulairechampid = '$position2[formulairechampid]'");
		$action = "modif" ;
		}

	// supprimer une valeur
	if (isset($action) and $action == "suppr_val")
		{
		$DB_site2->query("delete from formulaire_champ_valeur where formulairechampvaleurid = '$valid'");
		echo "<font class=\"cost\">Valeur supprimée</font><br>" ;
		$action = "modif" ;
		}

	// modifier un champ (2)
	if (isset($action) and $action == "modif2")
		{
		if ($lib_champFR != "" and $nom_champ != "" and $oblig_champ != "")
			{
			$DB_site2->query("update formulaire_champ set libelleFR = '$lib_champFR', libelleDE = '$lib_champDE', libelleEN = '$lib_champEN', 
							libelleIT = '$lib_champIT', libelleSP = '$lib_champSP', nom = '$nom_champ',	obligatoire = '$oblig_champ' 
							where formulairechampid = '$champid'");
			if ($action3 == "vals" and $nb > 0)
				{
				echo "<form method=\"post\" action=\"formulaire.php?action=ajt_vals&nb=$nb\">
				<input type=\"hidden\" name=\"champid\" value=\"$champid\"><input type=\"hidden\" name=\"formu\" value=\"$formu\">
				<table >" ;
				$i = 0 ;
				while ($i < $nb)
					{
					echo "<tr><td align=\"right\">Valeur sup. $i : </td><td align=\"left\"><input type=\"text\" name=\"val_chp$i\" value=\"\"></td></tr>" ;
					$i++ ;
					}
				echo "</table>
				<input type=\"submit\" value=\"Ajouter\">
				</form><br>
				<a href=\"formulaire.php?action=modif&formu=$formu\">Retour</a>" ;
				}
			else
				{
				$valeurs=$DB_site2->query("SELECT * from formulaire_champ_valeur where formulairechampid = '$champid'");
				while ($valeur=$DB_site2->fetch_array($valeurs))
					{
					$num = $valeur[formulairechampvaleurid] ;
					$lib = "val_chp$num" ;
					$DB_site2->query("update formulaire_champ_valeur set valeur = '$_POST[$lib]' where formulairechampvaleurid = '$num'");
					echo "<font class=\"cost\">Champ modifié</font><br>" ;
					$action = "modif" ;
					}
				}
			}
		}

	// modifier un champ (2)
	if (isset($action) and $action == "ajt_vals")
		{
		$max_num=$DB_site2->query_first("select max(valeur) from formulaire_champ_valeur where formulairechampid = '$champid'");
		if ($max_num[0] == "")
			{
			$max_num[0] = 0 ;
			}
		$i = 0 ;
		while ($i < $nb)
			{
			$max_num[0]++ ;
			$lib = "val_chp$i" ;
			$DB_site2->query("insert into formulaire_champ_valeur (formulairechampid, libelle, description, valeur) values ('$champid', 'value', '$_POST[$lib]', '$max_num[0]')");
			$i++ ;
			}
		$action = "modif" ;
		}

	// modifier un champ
	if (isset($action) and $action == "modifchp")
		{
		$champ=$DB_site2->query_first("SELECT * from formulaire_champ where formulairechampid = '$champid'");
		$type=$DB_site2->query_first("SELECT nom from formulaire_type_champ where formulairetypechampid = '$champ[type]'");
		$valeurs=$DB_site2->query("SELECT * from formulaire_champ_valeur where formulairechampid = '$champid'");
		$affich_valeurs = "<strong>Valeurs</strong><bR>
		<bR><table class=\"table_remises\">" ;
		if ($champ[type] == '1' or $champ[type] == '4')
			{
			while ($valeur=$DB_site2->fetch_array($valeurs))
				{
				$num = $valeur[formulairechampvaleurid] ;
				$affich_valeurs .= "
				<tr><td align=\"right\">$valeur[description] : </td><td align=\"left\"><input type=\"text\" name=\"val_chp$num\" value=\"$valeur[valeur]\"></td></tr>" ;
				}
			}
		else
			{
			while ($valeur=$DB_site2->fetch_array($valeurs))
				{
				$num = $valeur[formulairechampvaleurid] ;
				$affich_valeurs .= "<tr><td align=\"right\">Valeur : </td><td align=\"left\"><input type=\"text\" name=\"val_chp$num\" value=\"$valeur[description]\"> 
				<a href=\"formulaire.php?action=formu&action2=suppr_val&valid=$valeur[formulairechampvaleurid]&champid=$champid\">Supprimer</a></td></tr>" ;
				}
			$plus = "
			<input type=\"hidden\" name=\"action3\" value=\"vals\">
			<bR>Ajouter <input type=\"text\" name=\"nb\" value=\"0\" SIZE=\"1\"> valeur(s).<br>
			(Cliquez sur modifier pour ajouter les valeurs.)" ;
			}
		$affich_valeurs .= "</table>" ;
		switch ($champ[obligatoire])
			{
			case "0":
				$checked0 = "checked" ;
				$checked1 = "" ;
				break ;
			case "1":
				$checked0 = "" ;
				$checked1 = "checked" ;
				break ;
			}
		echo "<strong>Modifier un champ</strong><br><br>
		<form method=\"post\" action=\"formulaire.php?action=formu\">
		<input type=\"hidden\" name=\"action\" size=\"10\" value=\"modif2\">
		<input type=\"hidden\" name=\"formu\" value=\"$formu\">
		<input type=\"hidden\" name=\"champid\" size=\"10\" value=\"$champid\">
		<table class=\"table_remises\">" ;
			if (in_array("FR" , $langues))
				{
				echo "<tr>
						<td class=\"td_head_location\">Texte affiché FR</td>
						<td align=\"center\"><input type=\"text\" name=\"lib_champFR\" size=\"10\" value=\"$champ[libelleFR]\"></td>
					</tr>" ;
				}
			if (in_array("DE" , $langues))
				{
				echo "<tr>
						<td class=\"td_head_location\">Texte affiché DE</td>
						<td align=\"center\"><input type=\"text\" name=\"lib_champDE\" size=\"10\" value=\"$champ[libelleDE]\"></td>
					</tr>" ;
				}
			if (in_array("EN" , $langues))
				{
				echo "<tr>
						<td class=\"td_head_location\">Texte affiché EN</td>
						<td align=\"center\"><input type=\"text\" name=\"lib_champEN\" size=\"10\" value=\"$champ[libelleEN]\"></td>
					</tr>" ;
				}
			if (in_array("IT" , $langues))
				{
				echo "<tr>
						<td class=\"td_head_location\">Texte affiché IT</td>
						<td align=\"center\"><input type=\"text\" name=\"lib_champIT\" size=\"10\" value=\"$champ[libelleIT]\"></td>
					</tr>" ;
				}
			if (in_array("SP" , $langues))
				{
				echo "<tr>
						<td class=\"td_head_location\">Texte affiché SP</td>
						<td align=\"center\"><input type=\"text\" name=\"lib_champ SP\" size=\"10\" value=\"$champ[libelleSP]\"></td>
					</tr>" ;
				}
			echo "<tr>
					<td class=\"td_head_location\">Nom du champ</td>
					<td align=\"center\"><input type=\"text\" name=\"nom_champ\" size=\"10\" maxlenght=\"20\" value=\"$champ[nom]\"></td>
				</tr>
				<tr>
					<td class=\"td_head_location\">Type de champ</td>
					<td align=\"center\">$type[nom]</td>
				</tr>
				<tr>
					<td class=\"td_head_location\">Obligatoire</td>
					<td align=\"center\"><input type=\"radio\" name=\"oblig_champ\" value=\"1\" $checked1> Oui 
										<input type=\"radio\" name=\"oblig_champ\" value=\"0\" $checked0> Non</td>
				</tr>
			</table>
		<br>
		$affich_valeurs $plus<br><br>
		<input type=\"submit\" value=\"Modifier\">
		</form>
		<br><a href=\"formulaire.php?action=modif&formu=$formu\">Retour</a>" ;
		}
	
	// Modifier le formu
	if (isset($action) and $action == "modif")
		{
		$types_champs=$DB_site2->query("SELECT * FROM formulaire_type_champ");
		$liste_types = "" ;
		while ($type_champ=$DB_site2->fetch_array($types_champs))
			{
			if ($type_champ[formulairetypechampid] == $champ[type])
				{
				$liste_types .= "<option value=\"$type_champ[formulairetypechampid]\" selected>$type_champ[nom]</option>" ;
				}
			else
				{
				$liste_types .= "<option value=\"$type_champ[formulairetypechampid]\">$type_champ[nom]</option>" ;
				}
			}
		$form=$DB_site2->query_first("select * from formulaire where formulaireid = '$formu'") ;
		echo "<br><br><strong>Modifier le formulaire</strong><br><br>
		<form method=\"post\" action=\"formulaire.php\">
		<input type=\"hidden\" name=\"action\" value=\"modifform1\">
		<input type=\"hidden\" name=\"formu\" value=\"$formu\">
		<table class=\"table_modif_categ\"><tr><td>
		<table>
		<tr><td>Nom : </td><td><input type=\"text\" name=\"nom\" value=\"$form[nom]\" size=\"20\"></td></tr>
		<tr><td>Description : </td><td><input type=\"text\" name=\"descript\" value=\"$form[description]\" size=\"35\"></td></tr>
		</table></td><td><input type=\"submit\" value=\"Modifier\"></td></tr></table>
		</form>
		<div align=\"left\"><bR><br><strong>Ajouter un champ :</strong><br>
		 <form method=\"post\" action=\"formulaire.php\">
		 <input type=\"hidden\" name=\"action\" value=\"ajoutchamp\">
		<input type=\"hidden\" name=\"formu\" value=\"$formu\">
		 <table class=\"table_promos\">
			<tr>
				<td class=\"td_head_location\">Texte affiché</td>
				<td class=\"td_head_location\">Nom du champ</td>
				<td class=\"td_head_location\">Type de champ</td>
				<td class=\"td_head_location\">Obligatoire</td>
				<td class=\"td_head_location\"></td>
			</tr>
			<tr>
				<td align=\"center\">" ;
				if (in_array("FR" , $langues))
					{
					echo "FR <input type=\"text\" name=\"lib_champFR\" size=\"10\"><br>" ;
					}
				if (in_array("DE" , $langues))
					{
					echo "DE <input type=\"text\" name=\"lib_champDE\" size=\"10\"><br>" ;
					}
				if (in_array("EN" , $langues))
					{
					echo "EN <input type=\"text\" name=\"lib_champEN\" size=\"10\"><br>" ;
					}
				if (in_array("IT" , $langues))
					{
					echo "IT <input type=\"text\" name=\"lib_champIT\" size=\"10\"><br>" ;
					}
				if (in_array("SP" , $langues))
					{
					echo "SP <input type=\"text\" name=\"lib_champSP\" size=\"10\"><br>" ;
					}
				echo "</td>
				<td align=\"center\"><input type=\"text\" name=\"nom_champ\" size=\"10\" maxlenght=\"20\"></td>
				<td align=\"center\"><select name=\"type_champ\">$liste_types</select></td>
				<td align=\"center\"><input type=\"radio\" name=\"oblig_champ\" value=\"1\" checked> Oui<bR><input type=\"radio\" name=\"oblig_champ\" value=\"0\"> Non</td>
				<td align=\"center\"><input type=\"submit\" value=\"Ajouter\"></td>
			</tr>
			</table>
			<br><br>
			<div align=\"left\"><strong>Champs existants :</strong><br>
			 <table class=\"table_promos\">
				<tr>
					<td class=\"td_head_location\">Pos.</td>
					<td class=\"td_head_location\">Texte affiché</td>
					<td class=\"td_head_location\">Nom du champ</td>
					<td class=\"td_head_location\">Type de champ</td>
					<td class=\"td_head_location\">Obligatoire</td>
					<td class=\"td_head_location\"></td>
				</tr>" ;
			$champs=$DB_site2->query("select formulairechampid, position, libelleFR, fc.nom as nom, ftc.nom as nomtype, obligatoire 
			from formulaire_champ fc, formulaire_type_champ ftc where fc.type=ftc.formulairetypechampid and formulaireid = '$formu' order by position asc") ;
			$nb_champs=$DB_site2->num_rows($champs) ;
			$i_champ = 0 ;
			while ($champ=$DB_site2->fetch_array($champs))
				{
				$i_champ++ ;
				if ($nb_champs > 1)
					{
					if ($i_champ == 1) // Premier champ
						{
						$images_position = "<img src=\"images/vide.gif\" height=\"7\">
											<a href=\"formulaire.php?action=down&champid=$champ[formulairechampid]&formu=$formu\">
											<img src=\"images/descendre.gif\" alt=\"Descendre\" title=\"Descendre\" height=\"7\" border=\"0\"></a>";
						}
					elseif ($i_champ == $nb_champs) // Dernier champ
						{
						$images_position = "<a href=\"formulaire.php?action=up&champid=$champ[formulairechampid]&formu=$formu\">
											<img src=\"images/monter.gif\" alt=\"Monter\" title=\"Monter\" height=\"7\" border=\"0\"></a>
											<img src=\"images/vide.gif\" height=\"7\">";
						}
					else
						{
						$images_position = "<a href=\"formulaire.php?action=up&champid=$champ[formulairechampid]&formu=$formu\">
											<img src=\"images/monter.gif\" alt=\"Monter\" title=\"Monter\" height=\"7\" border=\"0\"></a>
											<a href=\"formulaire.php?action=down&champid=$champ[formulairechampid]&formu=$formu\">
											<img src=\"images/descendre.gif\" alt=\"Descendre\" title=\"Descendre\" height=\"7\" border=\"0\"></a>";
						}
					}
				switch ($champ[obligatoire])
					{
					case "0":
						$champ[obligatoire] = "Non" ;
						break ;
					case "1":
						$champ[obligatoire] = "Oui" ;
						break ;
					}
				echo "<tr>
					<td align=\"center\">$images_position</td>
					<td align=\"center\">$champ[libelleFR]</td>
					<td align=\"center\">$champ[nom]</td>
					<td align=\"center\">$champ[nomtype]</td>
					<td align=\"center\">$champ[obligatoire]</td>
					<td align=\"center\"><a href=\"formulaire.php?action=modifchp&champid=$champ[formulairechampid]&formu=$formu\">Modifier</a>
					<bR><a href=\"formulaire.php?action=suppr&champid=$champ[formulairechampid]&formu=$formu\">Supprimer</a></td>
				</tr>" ;
				}
			echo "</table><br><br>
			<center><a href=\"http://$host\" target=\"_blank\">Voir les modifications sur ma boutique</a>
			<br><br><a href=\"formulaire.php\">Retour</a></center>" ;
		}

	// autrement
	if (!isset($action) or $action == "")
		{
		echo "<br><bR><strong>Formulaires</strong><br><br>
		<table class=\"table_caracts\">
		<tr><td align=\"center\"><strong>Nom</strong></td><td align=\"center\"><strong>Description</strong></td><td align=\"center\"><strong>ACTION</strong></td></tr>" ;
		$formus=$DB_site2->query("select * from formulaire where formulaireid != '1'");
		while ($formu=$DB_site2->fetch_array($formus))
			{
			if ($formu[actif] == '0')
				{
				$action = "<a href=\"formulaire.php?action=activer&formu=$formu[formulaireid]\">Activer</a>" ;
				}
			else
				{
				$action = "<a href=\"formulaire.php?action=desactiver&formu=$formu[formulaireid]\">Désactiver</a>" ;
				}
			echo "<tr><td>$formu[nom]</td><td>$formu[description]</td><td width=\"30%\" align=\"center\">$action | <a href=\"formulaire.php?action=modif&formu=$formu[formulaireid]\">Modifier</a></td></tr>" ;
			}
		echo "</table>" ;
		//echo "<br><br><a href=\"formulaire.php?action=ajtform\">Ajouter un formulaire</a>" ;
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