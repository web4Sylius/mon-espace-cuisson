<?
$prov = "comp" ;
require "../admin_global.php";


function navigation($catidfunc , $isarticle=0)	{

	global $DB_site2,$navigationbit,$catid,$pagetitle_catname;
	$requete_categorie="select catid, libelleFR, parentid from categorie where catid='$catidfunc'";
	$navigation_categorie=$DB_site2->query_first($requete_categorie);

	$navigationbit="$navigation_categorie[libelleFR]" . " > " . $navigationbit;

	if ($navigation_categorie[parentid]!=0) {
		navigation($navigation_categorie[parentid]);
	}
}


$contenu="brand|designation|price|regular_price|product_url|description|image_url|category|merchant_id|in_stock|shipping_cost\n";

include "../fonctions.php";

$requete=" SELECT a.*, p.prix as prixport FROM article as a, fraisport as p WHERE  a.poid between p.poiddebut and p.poidfin and p.paysid=57";
$articles=$DB_site2->query($requete);
while($article=$DB_site2->fetch_array($articles)) {
	unset($top);	
	$top=trouve_article($article);
	if ($top[commandable]=='1' && $top[prix]>0 && $top[estenstock] > 0){
		$top[descarticle]=str_replace("\n"," ",$top[descarticle]);
		$top[descarticle]=str_replace("\r"," ",$top[descarticle]);
		$top[descarticle]=str_replace("\t"," ",$top[descarticle]);
		$top[descarticle] = strip_tags($top[descarticle]);
		$contenu.= "$titleFR|";
		$contenu.= $top[libarticle]."|";
		if ($top[prixpromo]!=""){
			$top[prix]=$top[prixpromo];
			$top[prix2]=$top[prixnormal];
		}
		else {
			$top[prix]=$top[prixnormal];
			$top[prix2]=0;
		}
		/*$top[prix]=$top[prix]*(1+($article[tva]/100));*/
		$contenu.=$top[prix]."|";
		$contenu.=$top[prix2]."|";
		$contenu.= "http://".$host."/art".",$article[artid],$articlename.htm|";
		$madescription=$top[descarticle];
		if ($madescription=="") {
			$madescription="Pas de description pour cet article";
		}
		$contenu.=$madescription."|";
		$contenu.="http://".$host."/configurations/$host/images/produits/".$article[artid].".".$article[image]."|";
		$navigationbit="";
		navigation($article[catid]);
		if (substr($navigationbit,strlen($navigationbit)-3,3)==" > ")
			$navigationbit=substr($navigationbit,0,strlen($navigationbit)-3);
		$contenu.=$navigationbit."|";
		$contenu.=$article[artid]."|";
		$contenu.="O|";
		$contenu.=$article[prixport];
		$contenu.="\n";
	}
}
if (!is_dir($rootpath."configurations/$host/exports")) {
	mkdir($rootpath."configurations/$host/exports",0777);
}
$filename = $rootpath."configurations/$host/exports/export_twenga.txt";
if (!$handle = fopen($filename, 'w')) {
	echo "Impossible d'ouvrir le fichier ($filename)";
	exit;
} else {
	if (fwrite($handle, stripslashes(html_entity_decode($contenu))) === FALSE) {
		echo "Impossible d'écrire dans le fichier ($filename)";
		fclose($handle);
		exit;
	} else {
		fclose($handle);
		//header("Location: http://www.arobases.fr/boutique/admin/comparateur/$filename");
		//$file = "/home/arobases/www/vendre-en-ligne.fr/admin/comparateur/".$filename; 
		//header('Content-Description: File Transfer'); 
		//header('Content-Type: application/force-download'); 
		//header('Content-Length: ' . filesize($file)); 
		//header('Content-Disposition: attachment; filename=' . basename($file)); 
		//readfile($file); 
		//exit;
		echo "L'adresse de votre fichier d'export twenga est la suivante : <a href=\"http://$host/configurations/$host/exports/export_twenga.txt\">http://$host/configurations/$host/exports/export_twenga.txt</a><br>";
	}
}





function est_en_stock($dbLink, $artid) 
	{
	$total=$dbLink->query_first("SELECT SUM(total) FROM stocks WHERE artid = '$artid'");
	return $total[0];
	}

function trouve_article($top){
	global $DB_site2,$debut,$datedujour,$libelle,$description,$count;
	$datedujour = date("Ymd");
	$lies=$DB_site2->query_first("select article_base from liens_articles where article_lie = '$top[artid]'") ;
	$existepromo =$DB_site2->query_first("SELECT count(promotionid) FROM promotion WHERE artid = '$top[artid]' AND datedebut <= '$datedujour' AND datefin > '$datedujour'" ); 
	if ($lies[0] == ""){
		// définition du prix
		if ($top[commandable] == '0' and $top[prix] == 0)
			$TopNouveautesBitPrix = "" ;
		else{
			if (isset($remise_sur_tout_catalogue) and $remise_sur_tout_catalogue > 0){
				if ($existepromo[0] != ""){	
					$rs_promo=$DB_site2->query_first("SELECT prix FROM promotion WHERE artid = '$top[artid]' AND datedebut <= '$datedujour' AND datefin > '$datedujour'" );
					if ($rs_promo[prix] != ""){ 
						$temp_prixpromo = $rs_promo[prix] * (1 - $remise_sur_tout_catalogue / 100) ;
						$temp_prixnormal = $top[prix] * (1 - $remise_sur_tout_catalogue / 100) ;
						$temp_prixnormal = number_format ($temp_prixnormal, 2, ',', ' ') ;
						$temp_prixpromo = number_format ($temp_prixpromo, 2, ',', ' ') ;
					}else{
						$temp_prixpromo = $top[prix] * (1 - $remise_sur_tout_catalogue / 100) ;
						$temp_prixnormal = number_format ($top[prix], 2, ',', ' ') ;
						$temp_prixpromo = number_format ($temp_prixpromo, 2, ',', ' ') ;
					}
				}else{
					$temp_prixpromo = $top[prix] * (1 - $remise_sur_tout_catalogue / 100) ;
					$temp_prixnormal = number_format ($top[prix], 2, ',', ' ') ;
					$temp_prixpromo = number_format ($temp_prixpromo, 2, ',', ' ') ;
				}
			}else{
				if ($existepromo[0] != ""){	
					$rs_promo=$DB_site2->query_first("SELECT prix FROM promotion WHERE artid = '$top[artid]' AND datedebut <= '$datedujour' AND datefin > '$datedujour'" );
					if ($rs_promo[prix] != ""){
						// Article en cours de Promotion, on affiche le prix promo
						$temp_prixpromo = number_format ($rs_promo[prix], 2, ',', ' ') ;
						$temp_prixnormal = number_format ($top[prix], 2, ',', ' ') ;
					}else{
						// Prix normal
						$temp_prixnormal = number_format ($top[prix], 2, ',', ' ') ;
					}
				}else{
					$temp_prixnormal = number_format ($top[prix], 2, ',', ' ') ;
				}
			}
		}
		$top[prixnormal]=$temp_prixnormal;
		$top[prixpromo]=$temp_prixpromo;
		// libelle et description
		$top[libarticle] = html_entity_decode(strip_tags($top[$libelle])) ;
		$top[descarticle] = html_entity_decode(strip_tags($top[$description])) ;
		$top[$titre2] = html_entity_decode(strip_tags($top[$titre2])) ;
		// stock 
		$top[estenstock]=est_en_stock($DB_site2, $top[artid]);
		if ($top[estenstock] > 0)
			$top[textestock] = "En stock" ;
		else
			$top[textestock] = "Epuisé" ;		
		//lien
		$top[lien]=$regleurlrewrite[article]."-".$top[url_libelle]."-".$top[artid].".htm";
		// marque
		$article_marque=$DB_site2->query_first("select libelle from marque m, article_marque am where m.marqueid = am.marqueid and am.artid = '$top[artid]'") ;
		$top[marque] = $article_marque[libelle] ;
		// evals
	}
	return $top;
}
?>