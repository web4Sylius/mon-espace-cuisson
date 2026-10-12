<?php
set_time_limit(500) ;

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
				while ($data = fgetcsv ($rh, 10000, "\t")) 
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
					//echo "$key => <br>" ;
					if ($key != 1)
						{
						//echo "$key2 => $value2 <br>" ;
						$ref = $montableau[$key][0] ;
						$libFR = addslashes($montableau[$key][1]) ;
						$libEN = addslashes($montableau[$key][2]) ;
						$catid = $montableau[$key][6] ;
						//$montableau[$key][5] = addslashes($montableau[$key][5]) ;
						$libFR_cat = addslashes("($catid)".$montableau[$key][5]) ;
						$prix = $montableau[$key][16] ;
						switch ($montableau[$key][21])
							{
							case "TVA_1":
								$taux_tva = '1' ; 
								break ;
							case "TVA_2":
								$taux_tva = '2' ; 
								break ;
							case "TVA_3":
								$taux_tva = '0' ; 
								break ;
							}
						$poids = $montableau[$key][22] * 100 ;
						switch ($montableau[$key][43])
							{
							case "on":
								$visible = 1 ;
								break ;
							case "off":
								$visible = 0 ;
								break ;
							}
						switch ($montableau[$key][44])
							{
							case "on":
								$commandable = 1 ;
								break ;
							case "off":
								$commandable = 0 ;
								break ;
							}
						if ($montableau[$key][44] != "")
							{
							$stock = $montableau[$key][44] ;
							}
						else
							{
							$stock = 0 ;
							}
						if ($montableau[$key][48] != "")
							{
							$seuil_alerte = $montableau[$key][48] ;
							}
						else
							{
							$seuil_alerte = 0 ;
							}
						if ($montableau[$key][49] != "")
							{
							$delai = $montableau[$key][49] ;
							}
						else
							{
							$delai = 0 ;
							}
						if ($catid != 0)
							{
							$categorie=$DB_site2->query_first("SELECT * from categorie where libelleFR LIKE '($catid)%'") ;
							if ($categorie[0] == "")
								{
								$DB_site2->query("insert into categorie (parentid, libelleFR, onglet, visible_treeview) 
								values ('0', '$libFR_cat', '0', '1')") ;
								$catid=$DB_site2->insert_id() ;
								}
							else
								{
								$catid = $categorie[0] ;
								}
							}
						$DB_site2->query("insert into article (artcode, catid, active, prix, tauxchoisi, poid, libelleFR, 
						libelleEN, commandable) values ('$ref', '$catid', '$visible', '$prix', '$taux_tva', '$poids', '$libFR', 
						'$libEN', '$commandable')") ;
						}
					}
				echo count($montableau) - 1 . " article(s) créé(s)<br>" ;
				}
			}
		else
			{
			$msg_erreur_import = "Vous devez saisir un chemin !" ;
			}
		}
	} 


echo "<br><br>
<h3>Importer un fichier CSV de fiches Powerboutique</h3>
<form method=\"post\" enctype=\"multipart/form-data\" action=\"import_powerboutique.php\">
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
</form>" ;

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