<?php
set_time_limit(600) ;

require "admin_global.php";
include "./header.php";
include "./menu.php";

echo "<h1>mise à jour de prix</h1>" ;
echo "format du fichier : ref;ppi_ttc<br>";


// $marqueid=12; // NOVY
$remise=round( (1*0.66)*0.9 , 6);



if ($action == "maj_prix_glemairlux") {
	$nom_fic="./csv/prix.csv";
	$row = 1;
	if (($handle = fopen($nom_fic, "r")) !== FALSE) {
		while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {
			$num = count($data);
			// echo "<p> $num fields in line $row: <br /></p>\n";
			if($row>1){
				//for ($c=0; $c < $num; $c++) {
				//	echo $data[$c] . "<br />\n";
				//}
				$marqueid=$data[0];
				$ref=$data[1];
				$ppittc=$data[2];
				$paht=$data[3];
				
				echo "marque ".$marqueid." - reférence ".$ref." passe au prix ".$ppittc." € achat à ".$paht."<br>";
				
				$article=$DB_site2->query_first("	SELECT article.artid FROM article 
													INNER JOIN article_marque ON (article.artid=article_marque.artid)
													WHERE article_marque.marqueid=".$marqueid."
													AND artcode_eurocave='".$ref."' ");
				if($article['artid']!=""){
					$prixht=round($ppittc/1.2,2);
					//$paht=round($prixht * $remise, 2);
					$sql = "UPDATE article SET prix = '".$ppittc."', prixconseilleht = '".$prixht."', prixht = '".$prixht."', paht='".$paht."' WHERE artid = '".$article['artid']."' ";
					echo $sql."<br>";
					// $DB_site2->query($sql);
					// à décommenter :)
				} else {
					echo "article non trouvé ...<br>";
				}
			}
			$row++;
		}
		fclose($handle);
	}
}


if ($action == "maj_prix_novy") {
	$articles=$DB_site2->query("	SELECT article.artid, article.prix FROM article 
									INNER JOIN article_marque ON (article.artid=article_marque.artid)
									WHERE article_marque.marqueid=".$marqueid." ");
	while($article=$DB_site2->fetch_array($articles)){
		$prix=$article['prix'];
		$ppittc=$prix;
		$prixht=round($ppittc/1.2,2);
		$paht=round($prixht * $remise, 2);
		$sql = "UPDATE article SET prixconseilleht = '".$prixht."', prixht = '".$prixht."', paht='".$paht."' WHERE artid = '".$article['artid']."' ";
		echo $sql."<br>";
		// $DB_site2->query($sql);
	}
}

echo "<br><br>
		<a href=\"benoit_majprix.php?action=maj_prix_glemairlux\">mettre à jour les prix airlux et glem</a><br>
		<!-- <a href=\"benoit_majprix.php?action=maj_prix_novy\">mettre à jour les prix d'achat de tous les produits novy</a> -->
	<br>"; 
include "./footer.php";

?>