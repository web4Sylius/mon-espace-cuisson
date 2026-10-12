<?
require_once __DIR__.'/../../secrets.php';

include("google-api.php");

$cle = SCRIPTS_TEST_CLE ;
//$cle2 = SCRIPTS_TEST_CLE_2 ;
//$cle3 = SCRIPTS_TEST_CLE_3 ;
//$cle4 = SCRIPTS_TEST_CLE_4 ;

$maliste = array() ;
array_push ($maliste, "arobases.fr", "avignon.vendre-en-ligne.fr", "boutiques-en-ligne.fr", "chambery.boutiques-en-ligne.fr", 
"chambery.vendre-en-ligne.fr", "dijon.boutiques-en-ligne.fr", "dijon.vendre-en-ligne.fr", "geneve.boutiques-en-ligne.fr", 
"geneve.vendre-en-ligne.fr", "grenoble.boutiques-en-ligne.fr", "grenoble.vendre-en-ligne.fr", "lauzanne.vendre-en-ligne.fr", 
"le-puy.boutiques-en-ligne.fr", "le-puy.vendre-en-ligne.fr", "lille.boutiques-en-ligne.fr", "lille.vendre-en-ligne.fr", 
"lyon.boutiques-en-ligne.fr", "lyon.vendre-en-ligne.fr", "nantes.boutiques-en-ligne.fr", "nantes.vendre-en-ligne.fr", 
"nice.boutiques-en-ligne.fr", "nice.vendre-en-ligne.fr", "paris.boutiques-en-ligne.fr", "paris.vendre-en-ligne.fr", 
"rouen.boutiques-en-ligne.fr", "rouen.vendre-en-ligne.fr", "st-etienne.boutiques-en-ligne.fr", "st-etienne.vendre-en-ligne.fr", 
"strasbourg.boutiques-en-ligne.fr", "strasbourg.vendre-en-ligne.fr", "tours.boutiques-en-ligne.fr", "tours.vendre-en-ligne.fr", 
"vendre-en-ligne.fr", "vendre-en-ligne.eu", "www.arobases.fr", "www.boutiques-en-ligne.fr", "www.vendre-en-ligne.fr", "www.vendre-en-ligne.eu") ;



$nb_par_page = 10;

$google = new GoogleApiSearch;

$k=0;

$q=stripslashes($q);

echo "<html><head></head><body><form><input type=\"text\" name=\"q\" value=\"$q\"><input type=\"submit\"></form>";

if ($q!="" and isset($q) ) {
	while($k<3) {
		$start=$k*10;
		$result = $google->doGoogleSearch($cle, $q, $start);
		if ( $result == false ) {
			echo "La requette a échoué.";
		} else {
			$nb_res = $result['estimatedTotalResultsCount'];
			if ( $nb_res == 0 ) {
				echo "Aucun résultat.";
		 	}  else  {
				$p=$start+1;
				foreach ($result['resultElements'] as $resultat) {
					$pos = strpos($resultat["URL"], "/" , 8 );

					if ($pos === false) {
						$resultaturl=substr($resultat["URL"] , 7 );
					} else {
						$resultaturl=substr($resultat["URL"] , 7 , $pos-7 );
					}
					if(in_array( $resultaturl , $maliste ) ) {
						echo $resultaturl . "<br>";
						echo "position de vendre en ligne : $p<br>";
						$trouve=1;
						break(2);
					}
					echo $resultaturl . "<br>";
					$p++;
				}
			}
		}
		$k++;
	}
	if ( $trouve!=1 ) {
		$p=$p-1;
		echo "non trouvé => position : >$p";
	}
}

echo "</html>";

?>