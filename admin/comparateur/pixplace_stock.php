<?
require "../admin_global.php";
require "../fonctions.php";


$nom_fic = $rootpath."admin/comparateur/pixplacetempstock.csv" ;

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
	$filename = $rootpath."admin/comparateur/pixplacestock$newfile.csv" ;
	}
else
	$filename = $rootpath."admin/comparateur/pixplacestock.csv" ;

if (!file_exists($filename)) {
	$handle = fopen($filename, 'w') ;
	$contenu = "Pixplace_Serial		Country		Avaibility		Price_VAT_EXCL		Ecotax_VAT		Status_code		Shipping_price_alone	Shipping_price_multi	Id_website		Shipping_desc		Price_VAT_EXCL_Sales		Price_VAT_EXCL_Package		Stock\n";
	if (fwrite($handle, $contenu) === FALSE) {
		echo "Impossible d'écrire dans le fichier ($filename)";
		$erreur .= "Impossible d'écrire dans le fichier ($filename)" ;
		}
	else {
		fclose($handle);
		$handle = fopen($filename, 'w+') ;
		$erreur = "" ;
		$country = "FR" ;
		$stock = 100 ;
		foreach ($tab as $ligne) {
			if ($ligne[8] == "CRE")
				{
				$FUPID = $ligne[0] ;
				$pix_temp=$DB_site2->query_first("SELECT * FROM pix_temp where FUPID = '$FUPID'");
				if ($pix_temp[FUPID] == $FUPID) {
					$refid = $pix_temp[idproduitmarchand];
					$idproduit = explode("_",$refid);
					$artid = $idproduit[0];
					$caractvalid = $idproduit[1];
					$article = $DB_site2->query_first("SELECT * FROM article WHERE artid = '$artid'");
					$availability = $article[delailivraison] - 3 ;
					if ($article[sowine] == '1')
						{
						$availability = "2";
						$shipping_desc = "Livré par FEDEX Express." ;
						}
					else
						{
						$availability = $article[delailivraison] - 3;
						$shipping_desc = "Livré par MORY." ;	
						}
					$prixHT = number_format($article[prixht]-$article[ecopartht], 5, ".", "") ;	
					$shipping = $DB_site2->query_first("SELECT p.prix as prixport, a.poid FROM fraisport p, article a WHERE p.paysid = '57' AND a.poid between p.poiddebut and p.poidfin AND a.artid = '$artid'");
					$shipping_price = number_format($shipping[prixport]/1.196, 5, ".", "") ;
					$ecotaxeHT = number_format($article[ecopartht], 5, ".", "") ;
					$towrite = "$FUPID		" ;
					$towrite .= "$country		" ;
					$towrite .= "$availability		" ;
					$towrite .= "$prixHT		" ;
					$towrite .= "$ecotaxeHT		" ;
					$towrite .= "0		" ; // code état
					$towrite .= "$shipping_price		" ; // frais livraion HT
					$towrite .= "		" ; // frais livraison multi HT
					$towrite .= "1		" ; // site vente
					$towrite .= "$shipping_desc		" ; // desc livraison
					$towrite .= "		" ; // prix discounté HT
					$towrite .= "		" ; // prixHT bundle
					$towrite .= "$stock" ; //stock
					$towrite .= "\n" ;
					fwrite($handle, $towrite) ;
					}
				else {
					echo "La ligne concernant le FUPID $fupid (article $article[artid]) n'a pas été créée (pb chez pixplace)!<br>" ;
					$erreur .= "La ligne concernant le FUPID $fupid (article $article[artid]) n'a pas été créée (pb chez pixplace)!<br>" ;
					}
				}
			}
		}
	
	}

$logfile = $rootpath."comparateur/pixplacereponse.log" ;
$fp = fopen($logfile, "a") ;
fwrite( $fp, "\n-------------------------------------------\n");
fwrite( $fp, "étape => stock\n");
fwrite( $fp, "date => ".date('d/m/Y H:i:s')."\n");
fwrite( $fp, "fichier => $filename\n");
fwrite( $fp, "erreurs => $erreur\n");
fwrite( $fp, "-------------------------------------------\n");


echo "<script langugage=\"javascript\">window.location = '../lienspixplace.php?erreur=$erreur';</script>" ;
	
	
?>