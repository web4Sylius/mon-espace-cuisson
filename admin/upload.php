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
			<td height=\"25\" nowrap> &nbsp; <img src=\"images/arrow_2.jpg\"> <font class=\"cost\">Uploads de fichiers et commentaires</font>
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

// Vérification module upload fichiers
if (in_array ("103", $modules))
	{
	echo "<h3>Fichiers et commentaires uploadés par les clients</h3>" ;
	if (isset($action) and $action == "archiv")
		{
		echo "Etes-vous sûr(e) de bien vouloir archiver ces uploads ?<br>
		<a href=\"upload.php?archiv=1&uploadpageid=$uploadpageid\">OUI</a>&nbsp;|&nbsp;<a href=\"upload.php\">NON</a>" ;
		}

	if (isset($archiv) and $archiv == "1")
		{
		$modif_upload=$DB_site2->query("update uploads set archive = '1' where uploadpageid = '$uploadpageid'") ;
		echo "<script language=\"javascript\">location.href=\"upload.php?archivok=1\";</script>";
		}

	if (isset($action) and $action == "visu")
		{
		// requêtes 
		$client=$DB_site2->query_first("select * from utilisateur u, pays p, uploads up
									 where p.paysid = u.paysid and u.userid = up.userid and up.uploadpageid = '$uploadpageid'") ;
		$art=$DB_site2->query_first("select * from article a, uploads up 
									where a.artid = up.artid and up.uploadpageid = '$uploadpageid'") ;
		$lignes_factures=$DB_site2->query("select * from lignefacture lf, uploads up, article a 
											where lf.factureid = up.factureid and a.artid = lf.artid
											and up.uploadpageid = '$uploadpageid' group by lf.lignefactureid") ;
		echo "<br>Uploads concernant l'article <b>$art[libelleFR]</b><bR>" ;
		// article + client
		echo "<br><br>
			<table width=\"100%\" align=\"center\" class=\"table_coupscoeur\">
				<tr>
					<td width=\"50%\" align=\"center\" valign=\"top\">
						<table width=\"100%\" align=\"center\">
							<tr>
								<td align=\"center\" valign=\"top\"><strong>Article</strong><br><br></td>
							</tr>
							<tr>
								<td align=\"left\" valign=\"top\">
									N° $art[artid] <br>
									Ref : $art[artcode]<br><br>
									$art[libelleFR]
									</td>
							</tr>
						</table>
					</td>
					<td width=\"50%\" align=\"center\" valign=\"top\">
						<table width=\"100%\" align=\"center\">
							<tr>
								<td align=\"center\" valign=\"top\"><strong>Client</strong><br><br></td>
							</tr>
							<tr>
								<td align=\"center\" valign=\"middle\">$client[prenom] $client[nom]<br><br>
									$client[adresse]<br>
									$client[codepostal] $client[ville]<br>
									$client[libelle]<br><br>
									$client[telephone]<br>
									</td>
							</tr>
						</table>
					</td>
				</tr>" ;
				if ($DB_site2->num_rows($lignes_factures) > 0)
					{
					$fact=$DB_site2->query_first("select facturid, datefacture from uploads up, facture f 
												where up.factureid = f.facturid and up.uploadpageid = '$uploadpageid'") ;
					$j = substr($fact[1], 8, 2) ;
					$m = substr($fact[1], 5, 2) ;
					$a = substr($fact[1], 0, 4) ;
					$fact[1] = "$j/$m/$a" ;
					echo "<tr>
						<td width=\"100%\" align=\"center\" valign=\"top\" colspan=\"2\"><hr></td>
					</tr>
					<tr>
						<td width=\"100%\" align=\"center\" valign=\"top\" colspan=\"2\">
							<table width=\"100%\" align=\"center\">
								<tr>
									<td align=\"center\" valign=\"top\" colspan=\"5\">
										<strong>Contenu de la facture n° $fact[0] du $fact[1]</strong><br><br>
									</td>
								</tr>
								<tr>
								   <td align=\"center\"><u>Référence</u></td>
								   <td align=\"center\"><u>Libelle</u></td>
								   <td align=\"center\"><u>Caractéristiques</u></td>
								   <td align=\"center\"><u>Quantité</u></td>
								   <td align=\"center\"><u>Prix total TTC</u></td>
							   </tr>" ;
					while ($ligne_facture=$DB_site2->fetch_array($lignes_factures))
						{
						$liste_caracteristiques = "<table border=\"0\" width=\"100%\"><tr>";
						$caracts=$DB_site2->query("SELECT * FROM lignefacturecaracteristique
														WHERE lignefactureid = '$ligne_facture[lignefactureid]'");
						while ($caract=$DB_site2->fetch_array($caracts))
							{
							$liste_caracteristiques .= "<td align=\"center\">$caract[libelle_caract]<br>$caract[libelle_caractval]</td>";
							}
						$liste_caracteristiques .= "</tr></table>";
						$ligne_factureprix = $ligne_facture[qte] * $ligne_facture[prix] ;
						echo "<tr>
								<td align=\"center\">$ligne_facture[artcode]</td>
								<td align=\"center\">$ligne_facture[libelleFR]</td>
								<td align=\"center\">$liste_caracteristiques</td>
								<td align=\"center\">$ligne_facture[qte]</td>
								<td align=\"center\">$ligne_factureprix</td>
							</tr>" ;
						}
					echo "</table>
						</td>
					</tr>" ;
					}
				echo "<tr>
					<td width=\"100%\" align=\"center\" valign=\"top\" colspan=\"2\"><hr></td>
				</tr>
				<tr>
					<td width=\"100%\" align=\"center\" valign=\"top\" colspan=\"2\">" ;
						$comms=$DB_site2->query("select uploadid, commentaire from uploads 
											where uploadpageid = '$uploadpageid' and commentaire != '' order by uploadid asc") ;
						echo "<strong>Commentaires</strong><br><br>
								<table align=\"center\" class=\"table_moyen_paiement\">" ;
						while ($comm=$DB_site2->fetch_array($comms)) 
							{
							echo "<tr>
									<td width=\"15%\" valign=\"top\">N° $comm[uploadid]</td>
									<td width=\"85%\">$comm[commentaire]</td>
								</tr>" ;
							}
						echo "</table>" ;
						$imgs=$DB_site2->query("select uploadid, image from uploads 
												where uploadpageid = '$uploadpageid' and image != '' order by uploadid asc") ;
						echo "<br><strong>Images</strong><br><bR>
								<table align=\"center\" class=\"table_moyen_paiement\">" ;
						while ($img=$DB_site2->fetch_array($imgs)) 
							{
							$img[image] = "$img[uploadid].$img[image]&nbsp;&nbsp;
								<a href=\"http://$host/configurations/$host/images/uploads/$img[uploadid].$img[image]\" target=\"blank\">Voir</a>" ;
							echo "<tr>
									<td width=\"15%\" valign=\"top\">N° $img[uploadid]</td>
									<td width=\"85%\">$img[image]</td>
								</tr>" ;
							}
							echo "</table><bR>" ;
			echo "</td>
				</tr>
			</table>
			<br><a href=\"upload.php\">Retour</a><br>" ;
						}

	if (isset($archivok) and $archivok == "1")
		{
		$msg = "L'upload a bien été archivée." ;
		$action = "" ;
		}
	
	if (isset($action) and $action== "visu_archiv")
		{
		echo "<b>Liste des uploads archivées</b><br><br>";
		$uploads=$DB_site2->query("select * from uploads up, utilisateur u, article a, facture f
								where up.userid = u.userid and up.artid = a.artid and f.facturid = up.factureid
								and archive = '1' group by up.uploadpageid order by date desc, u.nom asc") ;
		if ($DB_site2->num_rows($uploads) > 0)
			{
			echo "<table class=\"table_promos\">
					<tr class=\"td_head_promos\">
						<td><strong>Date</strong></td><td><strong>Client</strong></td><td><strong>Article</strong></td><td><strong>Uploads</strong></td>
					</tr>" ;
			while ($upload=$DB_site2->fetch_array($uploads)) 
				{
				$j = substr($upload[date], 8, 2) ;
				$m = substr($upload[date], 5, 2) ;
				$a = substr($upload[date], 0, 4) ;
				$upload[date] = "$j/$m/$a" ;
				$rowalt = "td_promos_".getrowbg() ;
				$upload[commentaire] = substr($upload[commentaire], 0, 20) ;
				$upload[commentaire] .= "..." ;
				$upload[prenom] = substr($upload[prenom], 0, 1) ;
				$nb_uploads=$DB_site2->query_first("select count(uploadid) from uploads where uploadpageid = '$upload[uploadpageid]'") ;
				echo  "<tr class=\"$rowalt\">
							<td align=\"center\" width=\"5%\">$upload[date]</td>
							<td align=\"center\" width=\"20%\">$upload[prenom]. $upload[nom]</td>
							<td align=\"left\" width=\"35%\">$upload[libelleFR]</td>
							<td width=\"10%\" align=\"center\">$nb_uploads[0]</td>
							<td align=\"center\" width=\"20%\"><a href=\"upload.php?action=visu&artid=$upload[artid]&userid=$upload[userid]\">Visualiser</a></td>
						</tr>" ;
				}
			echo "</table>" ;
			}
		else
			{
			echo "<br>Aucune upload n'a encore été archivée." ;
			}
		echo "<br><br><a href=\"upload.php\">Retour</a>" ;
		}
	
	if (!isset($action) or $action == "")
		{
		$uploads=$DB_site2->query("select * from uploads up, utilisateur u, article a, facture f
								where up.userid = u.userid and up.artid = a.artid and f.facturid = up.factureid
								and archive = '0' group by up.uploadpageid order by date desc, u.nom asc") ;
		echo "<b>Liste des uploads</b><br><br>";
		echo "<font class=\"cost\">$msg<br></font>" ;
		if ($DB_site2->num_rows($uploads) > 0)
			{
			echo "<table class=\"table_promos\">
					<tr class=\"td_head_promos\">
						<td><strong>Date</strong></td><td><strong>Client</strong></td><td><strong>Article</strong></td><td><strong>Uploads</strong></td><td><strong>ACTION</strong></td>
					</tr>" ;
			while ($upload=$DB_site2->fetch_array($uploads)) 
				{
				$j = substr($upload[date], 8, 2) ;
				$m = substr($upload[date], 5, 2) ;
				$a = substr($upload[date], 0, 4) ;
				$upload[date] = "$j/$m/$a" ;
				$rowalt = "td_promos_".getrowbg() ;
				$upload[commentaire] = substr($upload[commentaire], 0, 20) ;
				$upload[commentaire] .= "..." ;
				$upload[prenom] = substr($upload[prenom], 0, 1) ;
				$nb_uploads=$DB_site2->query_first("select count(uploadid) from uploads where uploadpageid = '$upload[uploadpageid]'") ;
				echo  "<tr class=\"$rowalt\">
							<td align=\"center\" width=\"5%\">$upload[date]</td>
							<td align=\"center\" width=\"20%\">$upload[prenom]. $upload[nom]</td>
							<td align=\"left\" width=\"35%\">$upload[libelleFR]</td>
							<td width=\"10%\" align=\"center\">$nb_uploads[0]</td>
							<td align=\"center\" width=\"20%\"><a href=\"upload.php?action=visu&uploadpageid=$upload[uploadpageid]\">Visualiser</a>&nbsp;|&nbsp;<a href=\"upload.php?action=archiv&uploadpageid=$upload[uploadpageid]\">Archiver</a></td>
						</tr>" ;
				}
			echo "</table>
				<br><a href=\"upload.php?action=visu_archiv\">Voir les uploads archivées</a>" ;
			}
		else
			{
			echo "<br>Aucune upload existante pour le moment." ;
			}
		}
	}
else
	{
	echo "<script language=\"javascript\">location.href=\"index.php\";</script>";
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