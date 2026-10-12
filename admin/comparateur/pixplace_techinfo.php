<?
require "../admin_global.php";
require "../fonctions.php";

$nom_fic = $rootpath."admin/comparateur/pixplacetemptechinfo.csv" ;

fichier($nom_fic, "filetechinfo") ;
$rh = fopen($nom_fic, 'rb');
if ($rh)  {
	$row = 1 ;
	while ($data = fgets($rh))  {
		$tabdata = explode("		", $data);
		foreach ($tabdata as $key2 => $value2) {
			$value2 = str_replace("\"", "", $value2);
			$tab[$row][$key2] = $value2 ;
			}
		$row++;
		}
	}
fclose($rh);				
unlink($nom_fic) ;

if (isset($newfile) && $newfile != "") {
	$newfile = $newfile + 1 ;
	$filename = $rootpath."admin/comparateur/pixplacetechinfo$newfile.csv" ;
	}
else
	$filename = $rootpath."admin/comparateur/pixplacetechinfo.csv" ;

if (!file_exists($filename)) {
	$handle = fopen($filename, 'w') ;
	$contenu = "FUPID		Id élément fiche technique		Langue		Libellé\n";
	if (fwrite($handle, $contenu) === FALSE) {
		echo "Impossible d'écrire dans le fichier ($filename)";
		$erreur .= "Impossible d'écrire dans le fichier ($filename)" ;
		}
	else {
		fclose($handle);
		$handle = fopen($filename, 'w+') ;
		$erreur = "" ;
		$langue = "FR" ;
		$tabSegment1 = array(9633, 9634, 9635, 9646, 9651, 9652, 4334, 4335, 9632, 9640, 9647, 4336, 4338) ;
		$tabSegment2 = array(9633, 9634, 9635, 9640, 9643, 9645, 9646, 9647, 9651, 9652, 4334, 4336, 4335, 4338, 4340, 9632) ;
		$tabSegment3 = array(4312, 4313) ;
		$tabSegment4 = array(9669) ;
		$tabSegment5 = array(9667) ;
		foreach ($tab as $ligne) {
			$FUPID = $ligne[0] ;
			$pix_temp=$DB_site2->query_first("SELECT * FROM pix_temp where FUPID = '$FUPID'");
			if ($pix_temp[FUPID] == $FUPID) {
				$and = "" ;
				if (!ereg("_", $pix_temp[idproduitmarchand]))
					$artid = $pix_temp[idproduitmarchand] ;
				else {
					$tabfupid = explode("_", $pix_temp[idproduitmarchand]) ;
					$artid = $tabfupid[0] ;
					array_shift($tabfupid) ;
					$and = "and caractvalid in (".implode(",", $tabfupid).")" ;
					}
				$categ=$DB_site2->query_first("SELECT pixsegmentid FROM categorie c inner join article a using(catid) where artid = '$artid'");
				if (in_array($categ[pixsegmentid], $tabSegment1)) {
					$pixid = "HD00061580" ;
					$caractval=$DB_site2->query_first("SELECT cv.$libelle FROM article_caractval ac inner join caracteristiquevaleur cv using(caractvalid) 
					where artid = '$artid' and caractid = '15' $and");
					if ($caractval[$libelle]) {
						$towrite = "$FUPID		" ;
						$towrite .= "$pixid		" ;
						$towrite .= "$langue		" ;
						$towrite .= "$caractval[$libelle]" ;
						$towrite .= "\n" ;
						fwrite($handle, $towrite) ;
						}
					else {
						$caractval=$DB_site2->query_first("SELECT cv.$libelle FROM article_caractval ac inner join caracteristiquevaleur cv using(caractvalid) 
						where artid = '$artid' and caractid = '22' $and");
						if ($caractval[$libelle]) {
							$towrite = "$FUPID		" ;
							$towrite .= "$pixid		" ;
							$towrite .= "$langue		" ;
							$towrite .= "$caractval[$libelle]" ;
							$towrite .= "\n" ;
							fwrite($handle, $towrite) ;
							}
						else {
							$caractval=$DB_site2->query_first("SELECT cv.$libelle FROM article_caractval ac inner join caracteristiquevaleur cv using(caractvalid) 
							where artid = '$artid' and caractid = '16' $and");
							if ($caractval[$libelle]) {
								$towrite = "$FUPID		" ;
								$towrite .= "$pixid		" ;
								$towrite .= "$langue		" ;
								$towrite .= "$caractval[$libelle]" ;
								$towrite .= "\n" ;
								fwrite($handle, $towrite) ;
								}
							}
						}
					}
				if (in_array($categ[pixsegmentid], $tabSegment2)) {
					$pixid = "HD00061581" ;
					$caractval=$DB_site2->query_first("SELECT cv.$libelle FROM article_caractval ac inner join caracteristiquevaleur cv using(caractvalid) 
					where artid = '$artid' and caractid = '13' $and");
					if ($caractval[$libelle]) {
						$towrite = "$FUPID		" ;
						$towrite .= "$pixid		" ;
						$towrite .= "$langue		" ;
						$towrite .= "$caractval[$libelle]" ;
						$towrite .= "\n" ;
						fwrite($handle, $towrite) ;
						}
					}
				if (in_array($categ[pixsegmentid], $tabSegment3)) {
					$pixid = "HD00061686" ;
					$caractval=$DB_site2->query_first("SELECT cv.$libelle FROM article_caractval ac inner join caracteristiquevaleur cv using(caractvalid) 
					where artid = '$artid' and caractid = '30' $and");
					if ($caractval[$libelle]) {
						$towrite = "$FUPID		" ;
						$towrite .= "$pixid		" ;
						$towrite .= "$langue		" ;
						$towrite .= "$caractval[$libelle]" ;
						$towrite .= "\n" ;
						fwrite($handle, $towrite) ;
						}
					$pixid = "HD00061682" ;
					$caractval=$DB_site2->query_first("SELECT cv.$libelle FROM article_caractval ac inner join caracteristiquevaleur cv using(caractvalid) 
					where artid = '$artid' and caractid = '28' $and");
					if ($caractval[$libelle]) {
						$towrite = "$FUPID		" ;
						$towrite .= "$pixid		" ;
						$towrite .= "$langue		" ;
						$towrite .= "$caractval[$libelle]" ;
						$towrite .= "\n" ;
						fwrite($handle, $towrite) ;
						}
					$pixid = "HD00061687" ;
					$caractval=$DB_site2->query_first("SELECT cv.$libelle FROM article_caractval ac inner join caracteristiquevaleur cv using(caractvalid) 
					where artid = '$artid' and caractid = '26' $and");
					if ($caractval[$libelle]) {
						$towrite = "$FUPID		" ;
						$towrite .= "$pixid		" ;
						$towrite .= "$langue		" ;
						$towrite .= "$caractval[$libelle]" ;
						$towrite .= "\n" ;
						fwrite($handle, $towrite) ;
						}
					}
				if (in_array($categ[pixsegmentid], $tabSegment4)) {
					$pixid = "HD00061584" ;
					$caractval=$DB_site2->query_first("SELECT cv.$libelle FROM article_caractval ac inner join caracteristiquevaleur cv using(caractvalid) 
					where artid = '$artid' and caractid = '22' $and");
					if ($caractval[$libelle]) {
						$towrite = "$FUPID		" ;
						$towrite .= "$pixid		" ;
						$towrite .= "$langue		" ;
						$towrite .= "$caractval[$libelle]" ;
						$towrite .= "\n" ;
						fwrite($handle, $towrite) ;
						}
					$pixid = "HD00061585" ;
					$caractval=$DB_site2->query_first("SELECT cv.$libelle FROM article_caractval ac inner join caracteristiquevaleur cv using(caractvalid) 
					where artid = '$artid' and caractid = '13' $and");
					if ($caractval[$libelle]) {
						$towrite = "$FUPID		" ;
						$towrite .= "$pixid		" ;
						$towrite .= "$langue		" ;
						$towrite .= "$caractval[$libelle]" ;
						$towrite .= "\n" ;
						fwrite($handle, $towrite) ;
						}
					}
				if (in_array($categ[pixsegmentid], $tabSegment5)) {
					$pixid = "HD00061582" ;
					$caractval=$DB_site2->query_first("SELECT cv.$libelle FROM article_caractval ac inner join caracteristiquevaleur cv using(caractvalid) 
					where artid = '$artid' and caractid = '22' $and");
					if ($caractval[$libelle]) {
						$towrite = "$FUPID		" ;
						$towrite .= "$pixid		" ;
						$towrite .= "$langue		" ;
						$towrite .= "$caractval[$libelle]" ;
						$towrite .= "\n" ;
						fwrite($handle, $towrite) ;
						}
					$pixid = "HD00061583" ;
					$caractval=$DB_site2->query_first("SELECT cv.$libelle FROM article_caractval ac inner join caracteristiquevaleur cv using(caractvalid) 
					where artid = '$artid' and caractid = '13' $and");
					if ($caractval[$libelle]) {
						$towrite = "$FUPID		" ;
						$towrite .= "$pixid		" ;
						$towrite .= "$langue		" ;
						$towrite .= "$caractval[$libelle]" ;
						$towrite .= "\n" ;
						fwrite($handle, $towrite) ;
						}
					}
				}
			else {
				echo "La ligne concernant le FUPID $fupid n'a pas été créée (pb chez pixplace)!<br>" ;
				$erreur .= "La ligne concernant le FUPID $fupid n'a pas été créée (pb chez pixplace)!<br>" ;
				}
			}
		}
	}

$logfile = $rootpath."admin/comparateur/pixplacereponse.log" ;
$fp = fopen($logfile, "a") ;
fwrite( $fp, "\n-------------------------------------------\n");
fwrite( $fp, "étape => techinfo\n");
fwrite( $fp, "date => ".date('d/m/Y H:i:s')."\n");
fwrite( $fp, "fichier => $filename\n");
fwrite( $fp, "erreurs => $erreur\n");
fwrite( $fp, "-------------------------------------------\n");


echo "<script langugage=\"javascript\">window.location = '../lienspixplace.php?erreur=$erreur';</script>" ;
	
?>