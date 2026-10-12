<?php
set_time_limit(200) ;

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
			<td height=\"25\" nowrap> &nbsp; <img src=\"images/arrow_2.jpg\"> <font class=\"cost\">Importer des fiches produit</font>
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


function copier_image($nom_fic,$imagename)
	{
	if($OpenLocal= fopen($_FILES[$imagename]['tmp_name'], "rb")) 
		{
		$LocalFileContent = fread($OpenLocal, $_FILES[$imagename]['size']);
		} 
	else 
		{
		fclose($OpenLocal);
		die("<b>Erreur : </b>impossible d'ouvrir le fichier local '".$_POST[$imagename].".");
		}
	fclose($OpenLocal);
	if($OpenRemote = fopen($nom_fic, "wb")) 
		{
		if(fwrite($OpenRemote, $LocalFileContent)) 
			{
			fclose($OpenRemote);
			}
		}
	else 
		{
		fclose($OpenRemote);
		die("<b>Erreur : </b>impossible d'ouvrir le fichier distant '".$nom_fic.".");
		}
	chmod ("$nom_fic",0777);
	}


// import
if ($action == "import")
	{
	//"text/comma-separated-values
	$msg_erreur_import = "" ;
	$msg_import = "" ;
	if (isset($fichier_mails) and $fichier_mails != "")
		{
		if ($_FILES['fichier_mails']['name']!="")
			{
			$nom_fic="/home/boutique/www/configurations/$host/images/importcsv/import.csv" ;
			copier_image($nom_fic,"fichier_mails") ;
			$rh = fopen($nom_fic, 'rb');
			if ($rh) 
				{
				$row = 1 ;
				while ($data = fgetcsv ($rh, 10000, ";")) 
					{
					$num = count ($data);
					for ($c = 0 ; $c < $num ; $c++) 
						{
						$montableau[$row][$c] = $data[$c] ;
						}
					$row++;
					}
				}
			fclose($rh);				
			$folder = "/home/boutique/www/configurations/$host/images/importcsv";
			$dossier = opendir($folder);
			while ($fichier = readdir($dossier))
				{
				if ($fichier != "." && $fichier != "..")	
					{
					if (file_exists($folder."/import.csv"))
						{
						unlink($folder."/import.csv");
						}
					}
				}
			if ((isset($montableau)) and (count($montableau) > 0))
				{
				foreach ($montableau as $key => $value) 
					{
					if ($key == 1) // 1ère ligne : on isole les caracteristiques et la page institutionnelle PaymentInstructions
						{
						if ($montableau[$key][50] == "PaymentInstructions")
							{
							$pos=$DB_site2->query_first("SELECT max(position) from institutionnel") ;
							if ($pos[0] == "")
								{
								$pos[0] = 1 ;
								}
							$libinsti = "Insctructions de règlement" ;
							$DB_site2->query("INSERT INTO institutionnel (active, position, libelleFR, libelleDE, libelleSP, libelleEN, 
							libelleIT) VALUES ('0', '$pos[0]', '$libinsti', '$libinsti', '$libinsti', '$libinsti', '$libinsti')") ;
							$institutionnelid=$DB_site2->insert_id() ;
							}
						foreach ($value as $key1 => $value1) 
							{
							if ((substr($value1, 0, 2) == "A:") and (substr($value1, 0, 9) != "A:SIFFTAS"))
								{
								$libcaract = addslashes(substr($value1, 2)) ;
								$DB_site2->query("INSERT INTO caracteristique (libelleFR, libelleDE, libelleEN, libelleIT, libelleSP, 
								description) VALUES ('$libcaract', '$libcaract', '$libcaract', '$libcaract', '$libcaract', '$libcaract')") ;
								$caractid=$DB_site2->insert_id() ;
								$caracts[$key1] = $caractid ;
								}
							}
						$existe=$DB_site2->query_first("SELECT * from fraisport where paysid = '57'") ;
						if ($existe[0] == "")
							{
							$DB_site2->query("insert into fraisport (poiddebut, poidfin, prix, paysid) values ('0', '10000', '1', '57')") ;
							}
						}
					if ($key != 1 and $montableau[$key][3] != "")
						{ 
						// frais de port (pays de livraison)
						if ($montableau[$key][92] != "")
							{
							$regs = explode("|", "{$montableau[$key][92]}") ;
							$k = 0 ;
							while ($k < count($regs))
								{
								$paysexiste=$DB_site2->query_first("SELECT paysid from pays where libelle = '$regs[$k]'") ;
								if ($paysexiste[0] != "")
									{
									$fraisportexiste=$DB_site2->query_first("SELECT * from fraisport where paysid = '$paysexiste[0]'") ;
									if ($fraisportexiste[0] == "")
										{
										$DB_site2->query("insert into fraisport (poiddebut, poidfin, prix, paysid) values ('0',
										 '10000', '1', '$paysexiste[0]')") ;
										}
									}
								$k++ ;
								}
							}
						if ($montableau[$key][97] != "")
							{
							$regs = explode("|", $montableau[$key][97]) ;
							$k = 0 ;
							while ($k < count($regs))
								{
								$paysexiste=$DB_site2->query_first("SELECT paysid from pays where libelle = '$regs[$k]'") ;
								if ($paysexiste[0] != "")
									{
									$fraisportexiste=$DB_site2->query_first("SELECT * from fraisport where paysid = '$paysexiste[0]'") ;
									if ($fraisportexiste[0] == "")
										{
										$DB_site2->query("insert into fraisport (poiddebut, poidfin, prix, paysid) values ('0',
										 '10000', '1', '$paysexiste[0]')") ;
										}
									}
								$k++ ;
								}
							}
						if ($montableau[$key][102] != "")
							{
							$regs = explode("|", $montableau[$key][102]) ;
							$k = 0 ;
							while ($k < count($regs))
								{
								$paysexiste=$DB_site2->query_first("SELECT paysid from pays where libelle = '$regs[$k]'") ;
								if ($paysexiste[0] != "")
									{
									$fraisportexiste=$DB_site2->query_first("SELECT * from fraisport where paysid = '$paysexiste[0]'") ;
									if ($fraisportexiste[0] == "")
										{
										$DB_site2->query("insert into fraisport (poiddebut, poidfin, prix, paysid) values ('0',
										 '10000', '1', '$paysexiste[0]')") ;
										}
									}
								$k++ ;
								}
							}
						// institutionnel
						if ($montableau[$key][50] != "")
							{
							$contenuactuel=$DB_site2->query_first("SELECT * from institutionnel where libelleFR = 'Insctructions de règlement'") ;
							if (!(ereg("{$montableau[$key][50]}", $contenuactuel[contenuFR])))
								{
								$nouveaucontenu = addslashes($contenuactuel[contenuFR]) . addslashes($montableau[$key][50]) ;
								$DB_site2->query("UPDATE institutionnel set contenuFR = '$nouveaucontenu', contenuDE = '$nouveaucontenu', 
								contenuEN = '$nouveaucontenu', contenuSP = '$nouveaucontenu', contenuIT = '$nouveaucontenu' where 
								institutionnelid = '$institutionnelid'") ;
								}
							}
						// categorie
						$existe=$DB_site2->query_first("SELECT * from categorie where libelleFR = 'Rayon {$montableau[$key][6]}'") ; 
						if ($existe[0] != "")
							{
							$catid = $existe[0] ;
							}
						else
							{
							$DB_site2->query("INSERT INTO categorie (parentid, libelleFR, libelleDE, libelleSP, libelleIT, libelleEN, 
							descriptionFR, descriptionEN, descriptionDE, descriptionSP, descriptionIT) VALUES ('0', 
							'Rayon {$montableau[$key][6]}', 'Rayon {$montableau[$key][6]}', 'Rayon {$montableau[$key][6]}',
							'Rayon {$montableau[$key][6]}', 'Rayon {$montableau[$key][6]}', '', '', '', '', '')") ;
							$catid=$DB_site2->insert_id() ;
							}
						// article
						if ($montableau[$key][69] != "")
							{
							$poids = "{$montableau[$key][69]}" ;
							}
						elseif ($montableau[$key][42] == 1)
							{
							$poids = "1" ;
							}
						else
							{
							$poids = "0" ;
							}
						$montableau[$key][25] = addslashes(urldecode($montableau[$key][25])) ;
						if (substr($montableau[$key][25], 0, 5) == "@@@@%") 
							{
							$montableau[$key][25] = substr($montableau[$key][25], 5) ;
							}
						$montableau[$key][3] = addslashes(urldecode($montableau[$key][3])) ;
						$montableau[$key][4] = addslashes(urldecode($montableau[$key][4])) ;
						$DB_site2->query("insert into article (artcode, catid, prix, active, tva, tauxchoisi, poid, fraisgestionid, 
						image, legende, libelleFR, descriptionFR, libelleDE, descriptionDE, libelleEN, descriptionEN, libelleIT, 
						descriptionIT, libelleSP, descriptionSP, Titre2FR, Titre2DE, Titre2EN, Titre2IT, Titre2SP, upload_images,
						upload_images_oblig, upload_comm, upload_comm_oblig, commandable) values ('$ref', '$catid',
						'{$montableau[$key][13]}', '1', '0', '1', '$poids', '0','', '', '{$montableau[$key][3]}',
						'{$montableau[$key][25]}', '{$montableau[$key][3]}', '{$montableau[$key][25]}', '{$montableau[$key][3]}',
						'{$montableau[$key][25]}', '{$montableau[$key][3]}', '{$montableau[$key][25]}','{$montableau[$key][3]}', 
						'{$montableau[$key][25]}', '{$montableau[$key][4]}', '{$montableau[$key][4]}', '{$montableau[$key][4]}',
						'{$montableau[$key][4]}', '{$montableau[$key][4]}', '0', '0', '0', '0', '1')") ;
						$artid=$DB_site2->insert_id() ;
						//$DB_site2->query("insert into article_marque (artid, marqueid) values ('$artid', '7')") ;
						// stock
						if ($montableau[$key][10] != "") 
							{
							$seuil = $montableau[$key][10] + 10 ;
							$DB_site2->query("insert into stock (artid, nombre, seuil, delaiappro) values ('$artid', 
							'{$montableau[$key][10]}', '$seuil', '10')") ;
							}
						// caracteristiques
						foreach ($caracts as $key2 => $value2) 
							{
							if (($montableau[$key][$key2] != "") and ($montableau[$key][$key2] != "-"))
								{
								$libelle=$DB_site2->query_first("select libelleFR from caracteristique where caractid = '$caracts[$key2]'") ;
								$vals = explode("|", $montableau[$key][$key2]) ;
								$vals[0] = addslashes($vals[0]) ;
								if ($vals[0] != "") // valeur (caracteristiquevaleur, categorie_caracteristique, article_caractval)
									{
									$existe=$DB_site2->query_first("SELECT * from caracteristiquevaleur where libelleFR = '$vals[0]'") ;
									if ($existe[0] != "")
										{
										$caractvalid = $existe[0] ;
										}
									else
										{
										$pos=$DB_site2->query_first("SELECT max(position) from caracteristiquevaleur where caractid = '$caracts[$key2]'") ;
										if ($pos[0] == "")
											{
											$pos[0] = 1 ;
											}
										$requete3="INSERT INTO caracteristiquevaleur (caractid, libelleFR, libelleDE, libelleEN, 
										libelleIT, libelleSP, position) VALUES ('$caracts[$key2]', '$vals[0]', '$vals[0]', '$vals[0]', 
										'$vals[0]', '$vals[0]', '$pos[0]')";
										$DB_site2->query($requete3) ;
										$caractvalid=$DB_site2->insert_id() ;
										}
									$existe=$DB_site2->query_first("SELECT * from categorie_caracteristique where catid = '$catid' and 
									caractid = '$caracts[$key2]'") ;
									if ($existe[0] == "")
										{
										$DB_site2->query("INSERT INTO categorie_caracteristique (catid, caractid, visible, filtrable) VALUES 
										('$catid', '$caracts[$key2]', '1', '1')") ;
										}
									$DB_site2->query("INSERT INTO article_caractval (artid, caractvalid, differenceprix) VALUES 
									('$artid', '$caractvalid', '0')") ;
									}
								}
							}
						}
					}
				echo count($montableau) . " article(s) créé(s)<br>" ;
				}
			}
		else
			{
			$msg_erreur_import = "Vous devez saisir un chemin !" ;
			}
		}
	} 


echo "<br><br>
<h3>Importer un fichier CSV de fiches Turbo Lister</h3>
<form method=\"post\" enctype=\"multipart/form-data\" action=\"import_turbo_lister.php\">
	<input type=\"hidden\" name=\"action\" value=\"import\">
	<font class=\"cost\">$msg_import $msg_erreur_import</font>
	<table class=\"table_caracts\">
		<tr>
			<td align=\"center\" colspan=\"3\" valign=\"middle\">Le fichier doit être de type csv (.csv).<br><bR></td>
		</tr>
		<tr>
			<td align=\"right\" width=\"25%\">Fichier : </td>
			<td align=\"left\" width=\"25%\"><input type=\"file\" name=\"fichier_mails\"></td>
			<td align=\"center\" width=\"50%\"><input type=\"submit\" value=\"Importer\"></td>
		</tr>
	</table>
</form>
<br><bR><br><bR>
<h3>Aide</h3>
<div align=\"left\">
<blockquote>
<strong>Comment exporter mes fiches produit ?</strong><br><br>
1) Dans Turbo Lister sélectionner tous les articles à exporter.<br>
2) Menu 'Fichier-Exporter des objets'<br>
3) La fenêtre suivante saffiche : <br><bR>
<img src=\"images/export1.gif\"><br><br>
Sélectionnez le 1er choix 'Format de Transfert de données (fichier csv)'<br>
Cliquez sur 'Afficher les options' en bas à gauche<br>
4) La fenêtre suivante s'affiche : <br><br>
<img src=\"images/export2.gif\"><br><br>
Décochez puis recochez la case 'Tous' (ceci est primordial)<br>
Cliquez sur 'Exporter'<br>
5) Enregistrez le fichier où vous le souhaitez
<bR><br>
<strong>Comment importer mes fiches produit ?</strong><br><br>
1) Dans votre panneau d'administration rubrique 'Importer des fiches produit'.<br>
2) Cliquez sur 'Parcourir'<br>
3) Sélectionnez le fichier que vous venez d'exporter (ci-dessus) puis cliquez sur 'Ouvrir'<br>
Cliquez sur 'Importer'<br>
4) Dans la rubrique 'Articles et rayons' renommez vos rayons et déplacez-les où vous le souhaitez<br>
5) Ajoutez les images de vos articles
</blockquote>
</div>" ;
/*
<P align=right><STRONG><FONT face=verdana size=3><FONT size=1><FONT color=#008000>Visitez nos autres
 articles en cliquant ici :</FONT>&nbsp;</FONT><A href="http://stores.ebay.fr/Planete-Modelisme"><ST
RONG><IMG style="WIDTH: 144px; HEIGHT: 42px" height=90 alt="Planete Modelisme" src="http://i2.ebayim
g.com/02/i/05/e6/f1/70_11.JPG" width=310 border=0></STRONG></A></FONT></STRONG></P>
= 383

if (isset($action) and $action == "majtexte")
	{
	$articles=$DB_site2->query("select artid, descriptionFR from article2 where artcode = 'debut'") ;
	echo $DB_site2->num_rows($articles) ;
	while ($article=$DB_site2->fetch_array($articles))
		{
		$nouvelledesc = addslashes(substr($article[descriptionFR], 383)) ;
		echo $nouvelledesc ;
		//$DB_site2->query("update article2 set descriptionFR = '$nouvelledesc' where artid = '$article[artid]'") ;
		}
	}
*/

/*
<P align=center><FONT color=green>Visitez notre boutique en cliquant ici :</FONT>&nbsp;<A href="http
://stores.ebay.fr/Planete-Modelisme"><IMG style="WIDTH: 144px; HEIGHT: 42px" height=90 alt="Planete 
Modelisme" src="http://i2.ebayimg.com/02/i/05/e6/f1/70_11.JPG" width=310 border=0></A></P><P align=c
enter><STRONG><FONT face=verdana size=3>N’oubliez pas de m’ajouter à <A href="http://cgi1.ebay.fr/aw
-cgi/eBayISAPI.dll?AcceptSavedSeller&amp;sellerid=*planetemodelisme.com*&amp;sspageName=DB:FavList" 
target=_blank>votre liste de favoris </A>!</FONT></STRONG></P>
= 562

if (isset($action) and $action == "majtexte")
	{
	$articles=$DB_site2->query("select artid, descriptionFR from article2 where artcode = 'fin'") ;
	echo $DB_site2->num_rows($articles) ;
	while ($article=$DB_site2->fetch_array($articles))
		{
		$nouvelledesc = addslashes(substr($article[descriptionFR], 0, -562)) ;
		echo $nouvelledesc ;
		//$DB_site2->query("update article2 set descriptionFR = '$nouvelledesc' where artid = '$article[artid]'") ;
		}
	}
*/
		
echo "<a href=\"import_turbo_lister.php?action=majtexte\">MAJ</a></td>
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