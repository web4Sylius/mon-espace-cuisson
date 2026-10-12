<?
set_time_limit(1200) ; 
ini_set (memory_limit, -1) ;
require "../admin_global.php";
require "../fonctions.php";

$nom_fic = $rootpath."admin/comparateur/tempmedia.csv" ;

fichier($nom_fic, "fileproduit") ;

$rh = fopen($nom_fic, 'rb'); 
if ($rh)  {
	$row = 1 ;
	while ($data = fgetcsv ($rh, 10000, "		"))  {
		$num = count ($data);
		for ($c = 0 ; $c < $num ; $c++) 
			$tab[$row][$c] = $data[$c] ;
		$row++;
		}
	}
fclose($rh);				
unlink($nom_fic) ;

if (isset($newfile) && $newfile != "") {
	$newfile = $newfile + 1 ;
	$filename = $rootpath."admin/comparateur/pixplacemedia$newfile.csv" ;
	}
else
	$filename = $rootpath."admin/comparateur/pixplacemedia.csv" ;

if (!file_exists($filename)) {
	$handle = fopen($filename, 'w') ;
	$contenu = "FUPID		Url du média		Principal		Type\n";
	if (fwrite($handle, $contenu) === FALSE) {
		echo "Impossible d'écrire dans le fichier ($filename)";
		$erreur .= "Impossible d'écrire dans le fichier ($filename)";
		}
	else {
		fclose($handle);
		$handle = fopen($filename, 'w+') ;
		foreach ($tab as $ligne) {
			if ($ligne[22] == "CRE")
				{
				$refartid = $ligne[0] ;
				$FUPID = $ligne[24] ;
				$idproduit = explode("_",$refartid);
				$artid = $idproduit[0];
				$caractvalid = $idproduit[1];
				$DB_site2->query("INSERT INTO pix_temp (idproduitmarchand, FUPID) VALUES ('$refartid','$FUPID')");
				$article = $DB_site2->query_first("SELECT image FROM article WHERE artid = '$artid'");				
				if ($caractvalid != "")
					{
					$article_caract = $DB_site2->query_first("SELECT image FROM article_caractval WHERE artid = '$artid' and caractvalid = '$caractvalid'");
					if ($article_caract[image] == "jpg")
						$pix_temp[url] = "http://$host/configurations/$host/images/produits/article_caractval/".$artid."_".$caractvalid.".$article[image]";
					else
						$pix_temp[url] = "http://$host/configurations/$host/images/produits/article/$artid.$article[image]";
					$towrite = "$FUPID		$pix_temp[url]		1		0\n" ;
					fwrite($handle, $towrite) ;					
					$caracteristiquevaleur = $DB_site2->query_first("SELECT image FROM caracteristiquevaleur WHERE caractvalid = '$caractvalid'");
					if ($caracteristiquevaleur[image] == '1')
						{
						$pix_temp[url] = "http://$host/configurations/$host/images/caracteristiquevaleur/zoom/$caractvalid.jpg";
						$towrite = "$FUPID		$pix_temp[url]		0		0\n" ;
						fwrite($handle, $towrite) ;
						}
					}
				else
					{
					$pix_temp[url] = "http://$host/configurations/$host/images/produits/article/$artid.$article[image]";
					$towrite = "$FUPID		$pix_temp[url]		1		0\n" ;
					fwrite($handle, $towrite) ;
					}	
				}
			}
		}
	
	}

$logfile = $rootpath."admin/comparateur/pixplacereponse.log" ;
$fp = fopen($logfile, "a") ;
fwrite( $fp, "\n-------------------------------------------\n");
fwrite( $fp, "étape => média\n");
fwrite( $fp, "date => ".date('d/m/Y H:i:s')."\n");
fwrite( $fp, "fichier => $filename\n");
fwrite( $fp, "erreurs => $erreur\n");
fwrite( $fp, "-------------------------------------------\n");

echo "<script langugage=\"javascript\">window.location = '../lienspixplace.php';</script>" ;

	
?>