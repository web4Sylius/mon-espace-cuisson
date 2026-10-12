<?php

require "admin_global.php";
include "./header.php";
include "./menu.php";
include "./fonctions.php";	

echo "
<td valign=\"top\">
<table width=\"100%\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\"><tr><td>
<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\">
  <tr>
   <td>
   	<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\">
		<tr>
			<td height=\"25\" nowrap> &nbsp; <img src=\"images/arrow_2.jpg\"> <font class=\"cost\">Page de téléchargement de documents</font>
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
   <td align=\"center\" valign=\"middle\"><br>" ;

// MONTER D'UNE POSITION 
if (isset ($action) and $action == "up")
	{
	$position=$DB_site2->query_first("SELECT position FROM document WHERE documentid = '$docid'");						
	$nouvelle_position_marque = $position[0] - 1 ;
	$position2=$DB_site2->query_first("SELECT documentid FROM document WHERE position = '$nouvelle_position_marque'");
	$DB_site2->query("UPDATE document SET position = '$nouvelle_position_marque' WHERE documentid = '$docid'");
	$DB_site2->query("UPDATE document SET position = '$position[0]' WHERE documentid = '$position2[documentid]'");
	echo "<script language=\"javascript\">location.href=\"telecharg.php\";</script>";
	}

// DESCENDRE D'UNE POSITION 
if (isset ($action) and $action == "down")
	{
	$position=$DB_site2->query_first("SELECT position FROM document WHERE documentid = '$docid'");						
	$nouvelle_position_marque = $position[0] + 1 ;
	$position2=$DB_site2->query_first("SELECT documentid FROM document WHERE position = '$nouvelle_position_marque'" );
	$DB_site2->query("UPDATE document SET position = '$nouvelle_position_marque' WHERE documentid = '$docid'");
	$DB_site2->query("UPDATE document SET position = '$position[0]' WHERE documentid = '$position2[documentid]'");
	echo "<script language=\"javascript\">location.href=\"telecharg.php\";</script>";
	}


// Désactiver la page
if (isset($action) and $action == "Désactiver")
	{
	$DB_site2->query("update parametresgeneraux set pagedocuments_active = '0'") ;
	$action = "" ;
	}

// 	Activer la page
if (isset($action) and $action == "Activer")
	{
	$DB_site2->query("update parametresgeneraux set pagedocuments_active = '1'") ;
	$action = "" ;
	}

// Désactiver un doc
if (isset($action) and $action == "desactdoc")
	{
	$DB_site2->query("update document set actif = '0' where documentid = '$doc'") ;
	$action = "" ;
	}

// 	Activer un doc
if (isset($action) and $action == "actdoc")
	{
	$DB_site2->query("update document set actif = '1' where documentid = '$doc'") ;
	$action = "" ;
	}
	
// ajouter un doc
if (isset($action) and $action == "ajout")
	{
	if ($_FILES['doc']['name'] != "" and $nomdoc != "") 
		{
		if ($_FILES['doc']['error'] == 1) 
			{
			$msg .= "Le fichier téléchargé excède la taille autorisée par le serveur ( > 2Mb)";
			}
		elseif ($_FILES['doc']['error'] == 2) 
			{
			$msg .= "Ereur inconnue ...";
			}
		elseif ($_FILES['doc']['error'] == 3) 
			{
			$msg .= "Le fichier n'a été que partiellement téléchargé.";
			}
		elseif ($_FILES['doc']['error'] == 4) 
			{
			$msg .= "Aucun fichier n'a été téléchargé.";
			}
		elseif ($_FILES['doc']['type'] == "application/x-msdos-program" or $_FILES['doc']['type'] == "application/x-msdownload") 
			{
			$msg .= "Le type du fichier téléchargé n'est pas autorisé.";
			}
		else // ok
			{
			switch ($_FILES['doc']['type'])  // définition de l'extension
				{
				case "audio/scpls":
					$file = "pls" ;
					break;
				case "audio/x-mpegurl":
					$file = "m3u" ;
					break;
				case "application/php":	
					$file = "php" ;
					break ;
				case "application/x-php":
					$file = "php" ;
					break ;
				case "application/x-shockwave-flash":
					$file = "swf" ;
					break;
				case "application/postscript":
					$file = "eps" ;
					break;
				case "application/msword":
					$file = "doc" ;
					break;
				case "application/msword":
					$file = "rtf" ;
					break;
				case "application/excel":
					$file = "xls" ;
					break;
				case "application/vnd.ms-powerpoint":
					$file = "pps" ;
					break;
				case "application/pdf":
					$file = "pdf" ;
					break;
				case "application/x-gzip":
					$file = "gzip" ;
					break;
				case "application/acad":
					$file = "dwg" ;
					break;
				case "application/clariscad":
					$file = "ccad" ;
					break;
				case "application/drafting":
					$file = "drw" ;
				   break;
				case "application/dxf":
					$file = "dxf" ;
				   break;
				case "application/i-deas":
					$file = "unv" ;
				   break;
				case "application/iges":
					$file = "igs" ;
				   break;
				case "application/oda":
					$file = "oda" ;
				   break;
				case "application/pdf":
					$file = "dwg" ;
				   break;
				case "application/postscript":
					$file = "ai" ;
				   break;
				case "application/pro":
					$file = "prt" ;
				   break;
				case "application/rtf":
					$file = "rtf" ;
				   break;
				case "application/set":
					$file = "set" ;
				   break;
				case "application/sla":
					$file = "stl" ;
				   break;
				case "application/solids":
					$file = "dwg" ;
				   break;
				case "application/step":
					$file = "step" ;
				   break;
				case "application/vda":
					$file = "vda" ;
				   break;
				case "application/x-mif":
					$file = "mif" ;
				   break;
				case "application/x-csh":
					$file = "dwg" ;
				   break;
				case "application/x-dvi":
					$file = "dvi" ;
				   break;
				case "application/hdf":
					$file = "hdf" ;
				   break;
				case "application/x-latex":
					$file = "latex" ;
				   break;
				case "application/x-netcdf":
					$file = "nc" ;
				   break;
				case "application/x-sh":
					$file = "dwg" ;
				   break;
				case "application/x-tcl":
					$file = "tcl" ;
				   break;
				case "application/x-tex":
					$file = "tex" ;
				   break;
				case "application/x-texinfo":
					$file = "texi" ;
				   break;
				case "application/x-troff":
					$file = "troff" ;
				   break;
				case "application/x-troff-man":
					$file = "man" ;
				   break;
				case "application/x-troff-me":
					$file = "me" ;
				   break;
				case "application/x-troff-ms":
					$file = "ms" ;
				   break;
				case "application/x-wais-source":
					$file = "src" ;
				   break;
				case "application/x-bcpio":
					$file = "bcpio" ;
				   break;
				case "application/x-cpio":
					$file = "cpio" ;
				   break;
				case "application/x-gtar":
					$file = "gtar" ;
				   break;
				case "application/x-shar":
					$file = "shar" ;
				   break;
				case "application/x-sv4cpio":
					$file = "sv4cpio" ;
				   break;
				case "application/x-sv4crc":
					$file = "sc4crc" ;
				   break;
				case "application/x-tar":
					$file = "tar" ;
				   break;
				case "application/x-ustar":
					$file = "tar" ;
				   break;
				case "application/zip":
					$file = "ZIP" ;
				   break;
				case "application/x-bzip":
					$file = "bz2" ;
				   break;
				case "audio/vnd.rn-realaudio":
					$file = "ra" ;
				   break;
				case "audio/x-pn-realaudio":
					$file = "ram" ;
				   break;
				case "application/vnd.rn-realmedia":
					$file = "rm" ;
				   break;
				case "video/vnd.rn-realvideo":
					$file = "rv" ;
				   break;
				case "audio/wav":
					$file = "wav" ;
				   break;
				case "audio/ogg":
					$file = "ogg" ;
				   break;
				case "audio/mpeg":
					$file = "mp3" ;
				   break;
				case "audio/aiff":
					$file = "aif" ;
					break;
				case "audio/mid":
					$file = "midi" ;
					break;
				case "audio/basic":
					$file = "snd" ;
				   break;
				case "audio/x-aiff":
					$file = "aif" ;
				   break;
				case "audio/x-wav":
					$file = "wav" ;
				   break;
				case "image/psd":
					$file = "psd" ;
				   break;
				case "image/png":
					$file = "png" ;
				   break;
				case "image/bmp":
					$file = "bmp" ;
				   break;
				case "image/gif":
					$file = "gif" ;
				   break;
				case "image/ief":
					$file = "ief" ;
				   break;
				case "image/jpeg":
					$file = "jpg" ;
				   break;
				case "image/pjpeg":
					$file = "jpg" ;
				   break;
				case "image/tiff":
					$file = "tiff" ;
				   break;
				case "image/x-cmu-raster":
					$file = "cmu" ;
				   break;
				case "image/x-portable-anymap":
					$file = "pnm" ;
				   break;
				case "image/x-portable-bitmap":
					$file = "pbm" ;
				   break;
				case "image/x-portable-graymap":
					$file = "pgm" ;
				   break;
				case "image/x-portable-pixmap":
					$file = "ppm" ;
				   break;
				case "image/x-rgb":
					$file = "rgb" ;
				   break;
				case "image/x-xbitmap":
					$file = "xbm" ;
				   break;
				case "image/x-xpixmap":
					$file = "xpm" ;
				   break;
				case "image/x-xwindowdump":
					$file = "dump" ;
				   break;
				case "multipart/x-zip":
					$file = "zip" ;
				   break;
				case "multipart/x-gzip":
					$file = "gzip" ;
				   break;
				case "text/xml":
					$file = "xml" ;
				   break;
				case "text/xsl":
					$file = "xsl" ;
				   break;
				case "text/html":
					$file = "html" ;
				   break;
				case "text/plain":
					$file = "txt" ;
				   break;
				case "text/richtext":
					$file = "rtx" ;
				   break;
				case "text/tab-separated-value":
					$file = "tsv" ;
				   break;
				case "text/x-setext":
					$file = "etx" ;
				   break;
				case "text/css":
					$file = "css" ;
				   break;
				case "video/x-ms-wmv":
					$file = "wmv" ;
				   break;
				case "video/x-ms-wmx":
					$file = "wmx" ;
				   break;
				case "video/avi":
					$file = "avi" ;
				   break;
				case "video/x-ms-asf":
					$file = "asx" ;
				   break;
				case "video/mpeg":
					$file = "mpeg" ;
				   break;
				case "video/quicktime":
					$file = "mov" ;
				   break;
				case "video/msvideo":
					$file = "avi" ;
				   break;
				case "video/x-sgi-movie":
					$file = "movie" ;
				   break;
				}
			$position=$DB_site2->query_first("select max(position) from document") ;
			if ($position[0] == NULL)
				{
				$pos = 1 ;
				}
			else
				{
				$pos = $position[0] + 1 ;
				}
			$infos_upload=$DB_site2->query("INSERT INTO document (nom, extension, actif, position) VALUES ('$nomdoc', '$file', '1', '$pos')") ;
			$insertid=$DB_site2->insert_id() ;
			$nom_fic="/home/boutique/www/configurations/$host/images/docs/".$insertid.".".$file ;
			copier_fichier($nom_fic,"doc") ;
			echo "<font class=\"cost\">Document ajouté</font>" ;
			}
		}
	else
		{
		echo "<font class=\"cost\">ERREUR ! Vous devez choisir un nom et un emplacement.</font>" ;
		}
	echo "<font class=\"cost\"><br>$msg</font>" ;
	$action = "" ;
	}
	
// supprimer un doc
if (isset($action) and $action == "supprdoc")
	{
	echo "Etes-vous sûr(e) de vouloir supprimer ce document ?<bR><br>
	<a href=\"telecharg.php?action=supprdocok&doc=$doc\">OUI</a> | <a href=\"telecharg.php\">NON</a>" ;
	}

// supprimer un doc (2)
if (isset($action) and $action == "supprdocok")
	{
	$mondoc=$DB_site2->query_first("select extension from document where documentid = '$doc'") ;
	$docasupprimer = "$doc.$mondoc[0]" ;
	$position_page=$DB_site2->query_first("SELECT position FROM document WHERE documentid = '$doc'");
	$position_page = $position_page[0];
	$rq_positions_suivantes = $DB_site2->query("SELECT documentid, position FROM document WHERE position > $position_page" );
	while ($rs_positions_suivantes=$DB_site2->fetch_array($rq_positions_suivantes)) 
		{
		$position_temp = $rs_positions_suivantes[position] - 1;
		$DB_site2->query("UPDATE document SET position = '$position_temp' WHERE documentid = '$rs_positions_suivantes[documentid]'");
		}
	$DB_site2->query("delete from document where documentid = '$doc'") ;
	$folder = "/home/boutique/www/configurations/$host/images/docs";
	$dossier = opendir($folder);
	while ($fichier = readdir ($dossier))
		{
		if ($fichier == $docasupprimer)	
			{
			unlink ($folder."/".$fichier) ;
			}
		}
	echo "<font class=\"cost\">Document supprimé</font>" ;
	$action = "" ;
	}
	
// toujours	
if (!isset($action) or $action == "")
	{
	$param=$DB_site2->query_first("select pagedocuments_active from parametresgeneraux") ;
	if ($param[0] == '1')
		{
		$act = "active" ;
		$act2 = "Désactiver" ;
		}
	else
		{
		$act = "inactive" ;
		$act2 = "Activer" ;
		}
	$docs=$DB_site2->query_first("select count(*) from document where actif = '1'") ;
	echo "<h3>Téléchargez des documents et mettez-les en ligne</h3>
	<div align=\"left\">
	<ul>
	<li>Votre page de téléchargement est actuellement <strong>$act</strong> sur votre boutique. <a href=\"telecharg.php?action=$act2\" alt=\"$act2\" title=\"$act2\">$act2</a></li>
	<li>Vous avez $docs[0] documents disponibles sur cette page. <a href=\"http://$host/telecharg.php\" target=\"_blank\">Voir</a><br></li>
	</ul>
	</div>
	<bR><hr><br>
	<strong>Ajouter un document</strong><bR><bR>
	<form method=\"post\" action=\"telecharg.php\" enctype=\"multipart/form-data\" >
	<input type=\"hidden\" name=\"action\" value=\"ajout\">
	<table>
		<tr><td align=\"right\">Nom du document : </td><td><input type=\"text\" name=\"nomdoc\" value=\"$nomdoc\" size=\"34\"></td></tr>
		<tr><td align=\"right\">Emplacement : </td><td valign=\"middle\"><input type=\"file\" name=\"doc\" value=\"\"></td></tr>
	</table>
	<br>
	<input type=\"submit\" value=\"Ajouter\">
	</form>
	<br><br>
	<strong>Documents disponibles</strong>
	<br><br>
	<table class=\"table_caracts\">
	<tr><td align=\"center\"><strong>Position</strong></td><td align=\"center\"><strong>Nom</strong></td><td align=\"center\"><strong>Document</strong></td><td align=\"center\"><strong>ACTION</strong></td></tr>" ;
	$documents=$DB_site2->query("select * from document order by position asc") ;
	$nb_docs=$DB_site2->num_rows($documents) ;
	$i_doc = 0 ;
	while ($document=$DB_site2->fetch_array($documents))
		{
		$i_doc++ ;
		if ($nb_docs > 1)
			{
			if ($i_doc == 1) // Première marque
				{
				$images_position = "<img src=\"images/vide.gif\" height=\"7\">
									<a href=\"telecharg.php?action=down&docid=$document[documentid]\">
									<img src=\"images/descendre.gif\" alt=\"Descendre\" title=\"Descendre\" height=\"7\" border=\"0\"></a>";
				}
			elseif ($i_doc == $nb_docs) // Dernière marque
				{
				$images_position = "<a href=\"telecharg.php?action=up&docid=$document[documentid]\">
									<img src=\"images/monter.gif\" alt=\"Monter\" title=\"Monter\" height=\"7\" border=\"0\"></a>
									<img src=\"images/vide.gif\" height=\"7\">";
				}
			else
				{
				$images_position = "<a href=\"telecharg.php?action=up&docid=$document[documentid]\">
									<img src=\"images/monter.gif\" alt=\"Monter\" title=\"Monter\" height=\"7\" border=\"0\"></a>
									<a href=\"telecharg.php?action=down&docid=$document[documentid]\">
									<img src=\"images/descendre.gif\" alt=\"Descendre\" title=\"Descendre\" height=\"7\" border=\"0\"></a>";
				}
			}
		if ($document[actif] == '1')
			{
			$action2 = "<a href=\"telecharg.php?action=desactdoc&doc=$document[documentid]\">Désactiver</a>" ;
			}
		else
			{
			$action2 = "<a href=\"telecharg.php?action=actdoc&doc=$document[documentid]\">Activer</a>" ;
			}
		echo "<tr><td>$images_position</td><td>$document[nom]</td><td align=\"center\"><a href=\"fichier.php?action=telechargerdoc&doc=$document[documentid]\">$document[documentid].$document[extension]</a></td><td align=\"center\"><a href=\"telecharg.php?action=supprdoc&doc=$document[documentid]\">Supprimer</a> $action2</td></tr>" ;
		}
	echo "</table>" ;
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
		  </td>
        </tr>";

include "./footer.php";

function copier_fichier($nom_fic,$imagename)
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

?>