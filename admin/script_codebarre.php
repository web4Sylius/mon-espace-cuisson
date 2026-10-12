<?php
set_time_limit(1200) ; 

require "admin_global.php";
include "./header.php";
include "./menu.php";

print "<h1>Génération de codes barres</h1>";

$offset=9999;

$max=$DB_site2->query_first("SELECT MAX(codebarreid)-".$offset." FROM code_barre");
if($max[0]>=99000)
    print "<p style=\"color:red;font-weight:bold;text-align:center;\">Attention : la valeur de ".$max[0]." sur 99 999 a été atteinte</p>";
else
    print "<p>Code de départ : ".$max[0]." / 99 999</p>";

// insertion des lignes dans la BDD
$nb=0;
//$DB_site2->query("TRUNCATE TABLE code_barre");
$articles=$DB_site2->query("select artcode, artid from article order by catid, artcode") ; // group by artcode 
while ($article=$DB_site2->fetch_array($articles)){
	$existe2=$DB_site2->query_first("SELECT count(*) as nb FROM code_barre WHERE artid='".$article[artid]."' ");
// 	if ($existe2[nb]==0){
		// nombre de coloris ==> nombre de ligne dans l'export
		$couleurs = $DB_site2->query("SELECT caractvalid FROM article_caractval ac INNER JOIN caracteristiquevaleur c USING(caractvalid) WHERE ac.artid = '$article[artid]' AND c.caractid = '1' order by caractvalid");
		if ($DB_site2->num_rows($couleurs) > 0){
			while ($couleur=$DB_site2->fetch_array($couleurs)){
				$existe = $DB_site2->query_first("SELECT * FROM code_barre WHERE artid = '$article[artid]' AND caractvalid = '$couleur[caractvalid]'");
				if ($existe[codebarreid] == ""){
					if($max[0]+$nb+1>99999){
						print "<p style=\"color:red;font-weight:bold;text-align:center;\">Plus aucun code barre disponible</p>";
						break;
					}
					$DB_site2->query("INSERT INTO code_barre (artid, caractvalid) VALUES ('$article[artid]', '$couleur[caractvalid]')");
					echo $article[artid]." ".$article[artcode]." : cet article a été mis a jour <br>";
					$nb++;
				}
			}
		} else {
			$existe = $DB_site2->query_first("SELECT * FROM code_barre WHERE artid = '$article[artid]' AND caractvalid = '0'");
			if ($existe[codebarreid] == "")	{
				if($max[0]+$nb+1>99999){
					print "<p style=\"color:red;font-weight:bold;text-align:center;\">Plus aucun code barre disponible</p>";
					break;
				}
				$DB_site2->query("INSERT INTO code_barre (artid, caractvalid) VALUES ('$article[artid]', '0')");
				echo $article[artid]." ".$article[artcode]." : cet article a été mis a jour <br>";
				$nb++;
			}
		}
// 	}
}
	
// création du code barre
$lignes = $DB_site2->query("SELECT * FROM code_barre WHERE codebarre = '' order by codebarreid");
while ($ligne=$DB_site2->fetch_array($lignes))	{
	$cnuf = "3665104"; // premier code : $cnuf = "37005402";
	$cip = $ligne[codebarreid]-$offset;
	$calcul = $cnuf.str_pad($cip, 5, "0", STR_PAD_LEFT);
	$key=calculcodebarre($calcul);
	$codebarre = $calcul.$key;
	$DB_site2->query("UPDATE code_barre SET codebarre = '$codebarre' WHERE codebarreid = '".$ligne[codebarreid]."' ");
// 	echo "UPDATE code_barre SET codebarre = '$codebarre' WHERE codebarreid = '".$ligne[codebarreid]."' ";
}

if($nb==0)
	print "<p>Aucun code barre généré</p>";

function calculcodebarre($calcul1){
	$ch=chunk_split($calcul1);
	$pair=$ch[1]+$ch[3]+$ch[5]+$ch[7]+$ch[9]+$ch[11];
	$pair*=3;
	$impair=$ch[0]+$ch[2]+$ch[4]+$ch[6]+$ch[8]+$ch[10];
	$pimp=$pair+$impair;
	$arrsup=ceil($pimp/10)*10;
	$key=$arrsup-$pimp;
	return $key;
}


/*
$articles=$DB_site2->query("SELECT * FROM article order by artid");

while ($article=$DB_site2->fetch_array($articles)){
	if ($article[sowine] == "1"){
	 
	}else{
		$coloriss = $DB_site2->query("SELECT ac.image as imagecaractvalid, cv.image, ac.caractvalid, ac.artid, cv.libelleFR FROM article_caractval ac INNER JOIN caracteristiquevaleur cv USING(caractvalid) WHERE ac.artid = '$article[artid]' AND cv.caractid = '1'"); 
		if ($DB_site2->num_rows($coloriss) == 0){
			$codebarre = $DB_site2->query_first("SELECT codebarre FROM code_barre WHERE artid = '$article[artid]' AND caractvalid = '$coloris[caractvalid]'");
			if ($codebarre[0] == ""){
				//$DB_site2->query("INSERT INTO code_barre (artid, caractvalid) VALUES ('$article[artid]', '0')");
				echo "creation code barre article $article[artid]<br>";
			}else{
				
			}
		}else{
			while ($coloris=$DB_site2->fetch_array($coloriss)) 	{
				$codebarre = $DB_site2->query_first("SELECT codebarre FROM code_barre WHERE artid = '$article[artid]' AND caractvalid = '$coloris[caractvalid]'");
				if ($codebarre[0] == ""){
					//$DB_site2->query("INSERT INTO code_barre (artid, caractvalid) VALUES ('$article[artid]', '$coloris[caractvalid]')");
					echo "creation code barre article $article[artid]<br>";
				}else{
					
				}
			}
		}
	}
}*/

include "./footer.php";

?>