<?php

require "../admin_global.php";

include("../mpfields.php");

$today=mktime(0,0,0);

$filename = $rootpath."configurations/$host/exports/windil.csv";
if (!$handle = fopen($filename, 'w')) 
	{
	echo "Impossible d'ouvrir le fichier ($filename)";
	exit;
	}

$tvafrance=$DB_site2->query_first("SELECT TVAtauxnormal AS taux1,TVAtauxreduit AS taux2 FROM pays WHERE paysid=57");
$tvafrance['taux1']=(1+$tvafrance['taux1']/100);
$tvafrance['taux2']=(1+$tvafrance['taux2']/100);

$articles=$DB_site2->query("select a.artid, a.artcode, a.coloris from article a INNER JOIN market_place_champs mpc USING(artcode) WHERE a.active='1' AND a.commandable='1' AND mpc.market_place='windil' AND mpc.enabled=1 group by a.artcode");
while ($article=$DB_site2->fetch_array($articles)) 
	{
	// nombre de coloris ==> nombre de ligne dans l'export
	$nbcouleurs = $DB_site2->query_first("SELECT COUNT(*) FROM article_caractval INNER JOIN caracteristiquevaleur USING(caractvalid) WHERE artid = '$article[artid]' AND caractid = '1'");
	if ($nbcouleurs[0] > 0)
		{ // on a plusieurs lignes
		$couleurs = $DB_site2->query("SELECT * FROM article_caractval ac INNER JOIN caracteristiquevaleur c USING(caractvalid) WHERE ac.artid = '$article[artid]' AND c.caractid = '1'");
		while ($couleur=$DB_site2->fetch_array($couleurs))
			{
			$codebarre = $DB_site2->query_first("SELECT codebarre FROM code_barre WHERE artid = '$article[artid]' AND caractvalid = '$couleur[caractvalid]'");
			$couleur[libelleFR] = stripslashes($couleur[libelleFR]);
			$contenu = generationligne($article[artid],$couleur[libelleFR],$codebarre[0]) ;
			if($contenu!=false) fwrite($handle, stripslashes(html_entity_decode($contenu,ENT_COMPAT,"ISO-8859-15")));
			}
		}
	else
		{// on a une ligne
		$codebarre=$DB_site2->query_first("SELECT codebarre FROM code_barre WHERE artid = '$article[artid]' AND caractvalid = '0'");
		$contenu = generationligne($article[artid],"",$codebarre[0]) ;
		if($contenu!=false) fwrite($handle, stripslashes(html_entity_decode($contenu,ENT_COMPAT,"ISO-8859-15")));
		}
	}

fclose($handle);

echo "Votre export de catalogue pour windil a bien été mis à jour.<br>
Il est disponible à l'adresse suivante :<a href=\"http://$host/configurations/$host/exports/windil.csv\"><strong>http://$host/configurations/$host/exports/windil.csv</strong></a>" ;


function generationligne ($artid,$couleur,$codebarre)
	{
	global $host,$DB_site2,$tvafrance,$today;
	$article = $DB_site2->query_first("select * from article WHERE artid = '$artid'");
	if($article['date_appro']>$today) return false;
	//marque
	$marque=$DB_site2->query_first("SELECT * FROM article_marque WHERE artid='$article[artid]'");
	$libmarque=$DB_site2->query_first("SELECT * FROM marque WHERE marqueid='$marque[marqueid]'");
	$categorie=$DB_site2->query_first("SELECT libelleFR FROM categorie WHERE catid=".$article['catid']);
	$requete=$DB_site2->query("SELECT * FROM market_place_catid WHERE catid=".$article['catid']." AND market_place='windil'");
	while($row=$DB_site2->fetch_array($requete))
	    $mp[$row['champ']]=$row['valeur'];
	

	$artcode = $article[artcode];

	$ligne = "";
	
	//image
	$article[urlimage] = "" ;
	if ($couleur != ""){
		
		$coloris = explode("'",$couleur);
		$color=$coloris[0];
		$infocaractvalid = $DB_site2->query_first("SELECT caractvalid FROM caracteristiquevaleur WHERE caractid = '1' AND libelleFR like '%$coloris[0]%'");
		$artcode.="-$infocaractvalid[caractvalid]";
		$existeimg = $DB_site2->query_first("SELECT image FROM article_caractval WHERE caractvalid = '$infocaractvalid[caractvalid]' AND artid = '$artid'");
		if ($existeimg[image] == "jpg")
			$article[urlimage] = "http://$host/configurations/$host/images/produits/article_caractval/$article[artid]_".$infocaractvalid[caractvalid].".$article[image]" ;
		elseif ($article[image] != "")
			$article[urlimage] = "http://$host/configurations/$host/images/produits/$article[artid].$article[image]" ;
	}elseif ($article[image] != ""){
		$article[urlimage] = "http://$host/configurations/$host/images/produits/$article[artid].$article[image]" ;
		$color=$article['coloris'];
	}
	
	//image2
	$article[urlimage2] = "" ;
	$article[urlimage3] = "" ;
	$article[urlimage4] = "" ;
	$article[urlimage5] = "" ;
	$article[urlimage6] = "" ;
	$i=2;
	$photos=$DB_site2->query("SELECT * FROM articlephoto WHERE artid=".$article['artid']." ORDER BY position LIMIT 6");
	while($photo=$DB_site2->fetch_array($photos)){
	    $article['urlimage'.$i]="http://$host/configurations/$host/images/produits/$article[artid]_$photo[articlephotoid].$photo[image]" ;
	    $i++;
	}
	

	//description
	$descarticle = mpfields($article,$mp['Description']);
	$descarticle=str_replace("&rsquo;","'",$descarticle);
	$descarticle=html_entity_decode($descarticle,ENT_COMPAT,"ISO-8859-15");
	$descarticle=str_replace(";",",",$descarticle);
	
	// references
			
	$titrearticle = mpfields($article,$mp['Titre']);
	
	$poids=intval($article['emballage_poids']*1000);
	if($poids==0) $poids=1000;

	//prix de vente TTC
	$temp_price=$DB_site2->query_first("SELECT * FROM temp_price WHERE artid=".$article['artid']." AND ".time()." BETWEEN du AND au");
	if($temp_price['id']!=""){
	    $article[prixconseilleht]=$temp_price[prixconseilleht];
	    $article[prix]=$temp_price[prix];
	    $article[prixht]=$temp_price[prix]/$tvafrance['taux'.$article['tauxchoisi']];
	}
	$port = fdp($article['emballage_poids'],$article[prix]);
	$prixvente = $article[prix] + $port ;	
	$prixpublic = ($article[prixconseilleht]*1.196) + $port;	
	if(floor($article[prixht])==floor($article[prixconseilleht]))
	  $prixpublic="";
	else
	  $prixpublic = number_format($prixpublic, 2, ',', '');

	$prixvente = number_format($prixvente, 2, ',', '');
	

	$libcateg=$mp['Categorie'];
	
	//debut de la ligne
	$ligne.="\n";	
	
	// reference annonce
	$ligne.="$artcode";
	//$ligne.="$article[modele]";
	$ligne.=";";
	
	// categorie
	$ligne.="$libcateg";
	$ligne.=";";
	
	// titre
	$ligne.="$titrearticle";
	$ligne.=";";
	
	// poids / frais de port
	$ligne.=$poids;
	$ligne.=";";
	
	// prix de vente ttc en euro
	$ligne.="$prixvente";
	$ligne.=";";
	
	// prix d'origine (prix public)
	$ligne.="$prixpublic";
	$ligne.=";";
	
	// eco part
	$ecopart = number_format($article[ecopart], 2, ',', '');	
	$ligne.="$ecopart";
	$ligne.=";";
	
	// etat
	$ligne.="NF";
	$ligne.=";";
	
	// quantite
	$ligne.="99";
	$ligne.=";";
	
	// description
	$ligne.="$descarticle";
	$ligne.=";";
	
	// code postal
	$ligne.="";
	$ligne.=";";
	
	// ville
	$ligne.="";
	$ligne.=";";
	
	// telephone
	$ligne.="";
	$ligne.=";";
	
	// photo1
	$ligne.="$article[urlimage]";
	$ligne.=";";
	
	// photo2
	$ligne.="$article[urlimage2]";
	$ligne.=";";
	
	// photo3
	$ligne.="$article[urlimage3]";
	$ligne.=";";
	
	// photo4
	$ligne.="$article[urlimage4]";
	$ligne.=";";
	
	// photo5
	$ligne.="$article[urlimage5]";
	$ligne.=";";
	
	// photo6
	$ligne.="$article[urlimage6]";
	$ligne.=";";
	
	// codeclient
	$ligne.="w30122";
	$ligne.=";";
	
	// taille
	$ligne.="";
	$ligne.=";";
	
	// couleur
	$ligne.=$color;
	$ligne.=";";
	
	// matiere
	$ligne.="";
	$ligne.=";";
	
	// ref modele
	$ligne.="";
	$ligne.=";";
	
	// ean
	$ligne.=$codebarre;
	$ligne.=";";
	
	// ISBN
	$ligne.="";
	$ligne.=";";
	
	
	// Accesoires console jeu
	$ligne.="";
	$ligne.=";";
	
	
	// autres plateformes
	$ligne.="";
	$ligne.=";";
	// carte a jouer
	$ligne.="";
	$ligne.=";";
	// carte de collection
	$ligne.="";
	$ligne.=";";
	// type chaussettes
	$ligne.="";
	$ligne.=";";
	// type bien
	$ligne.="";
	$ligne.=";";
	// type survêtement
	$ligne.="";
	$ligne.=";";
	// type costume
	$ligne.="";
	$ligne.=";";
	// type short
	$ligne.="";
	$ligne.=";";
	// type public
	$ligne.="";
	
	return $ligne;
	}

function fdp($poids,$prix){
	global $DB_site2;

	$fraisport=$DB_site2->query_first("SELECT * FROM fraisport WHERE poiddebut <= '".($poids*1000)."' AND poidfin >= '".($poids*1000)."' and paysid = '57'");
	if($fraisport['gratuit']>$prix)
	    return $fraisport['prix'];
	else
	    return 0;
}

?>