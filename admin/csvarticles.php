<?php

require "admin_global.php";

function recur($catid)
	{
	global $DB_site2,$select, $i;
	$arbo=$DB_site2->query_first("select parentid, libelleFR from categorie where catid = '$catid'");
	//$select .= " > $arbo[libelleFR]";
	$select[$i] = trim($arbo[libelleFR]) ;
	$i++ ;
	if ($arbo[parentid] != 0)
		{
		recur($arbo[parentid]);
		}
	return ($select);
	}



$contenu="id;catégorie;référence;libelleFR;descriptionFR;prix (euros TTC);Prix promo;Article conseil 1 (ref);Article conseil 2;Article conseil 3;Article conseil 4;taux de tva (0% , 19,6% ou 5,5%);poids (grammes);image (URL complète);image 2;libelleDE;descriptionDE;libelleEN;descriptionEN;libelleIT;descriptionIT;libelleSP;descriptionSP;Stock initial;Seuil d alerte;Délai reappro;Caractéristiques;commandable;visible;marque";

$filename = 'csv/articles.csv';
if (is_writable($filename)) {
	if (!$handle = fopen($filename, 'w')) {
		echo "Impossible d'ouvrir le fichier ($filename)";
		exit;
	}
	if (fwrite($handle, stripslashes(html_entity_decode($contenu))) === FALSE) {
		echo "Impossible d'écrire dans le fichier ($filename)";
		exit;
	}
}else {
	echo "Le fichier $filename n'est pas accessible en écriture.";
	exit();
}

$handle = fopen($filename, 'a');
//$contenu="Référence;Désignation;Numéro;Rayon;Prix;TVA;Frais de gestion;Description;Caractéristiques";
$articles=$DB_site2->query("SELECT * FROM article order by artcode");
while($article=$DB_site2->fetch_array($articles)) 
	{
	$caracts = "" ;
	$caracteristiques=$DB_site2->query("SELECT * FROM categorie_caracteristique cc, caracteristique c 
	where cc.caractid = c.caractid and catid = '$article[catid]' and visible = '1'");
	while($caracteristique=$DB_site2->fetch_array($caracteristiques)) 
		{
		$caracts .= strtoupper($caracteristique[libelleFR]) . " : " ;
		$caracteristique_valeurs=$DB_site2->query("SELECT * FROM caracteristiquevaleur where caractid = '$caracteristique[caractid]'");
		while($caracteristique_valeur=$DB_site2->fetch_array($caracteristique_valeurs)) 
			{
			$caracteristique_article=$DB_site2->query_first("SELECT * FROM article_caractval 
			where caractvalid = '$caracteristique_valeur[caractvalid]' and artid = '$article[artid]'");
			$modif = "" ;
			if ($caracteristique_article[0] != "")
				{
				if ($caracteristique_article[differenceprix] != 0)
					{
					if (substr($caracteristique_article[differenceprix], 0, 1) == "-")
						{
						$modif = "($caracteristique_article[differenceprix] €)" ;
						}
					else
						{
						$modif = "(+$caracteristique_article[differenceprix] €)" ;
						}
					}
				$caracts .= "$caracteristique_valeur[libelleFR] $modif," ;
				}
			}
		$caracts = substr ($caracts, 0, -1) ;
		$caracts .= " " ;
		}
	$select = array() ;
	$i = 0 ;
	recur($article[catid]) ;
	$j = count($select) ;
	$select2 = "" ;
	while ($j > 0)
		{
		$j-- ;
		$select2 .= " > " . $select[$j] ;
		}
		
	
	$contenu="\n";	
	$contenu.="$article[artid];";	
	$contenu.="$select2;";	
	$contenu.="$article[artcode];";
	$contenu.="$article[libelleFR];";
	$article[descriptionFR]=str_replace(chr(10), "", $article[descriptionFR]);
	$article[descriptionFR]=str_replace(chr(13), "", $article[descriptionFR]);
	$article[descriptionFR] = str_replace(";", "", $article[descriptionFR]) ;
	$contenu.="$article[descriptionFR];";
	$contenu.="$article[prix];";
	$promo=$DB_site2->query_first("SELECT * FROM promotion where artid = '$article[artid]' and datedebut <= '".date('Y-m-d')."' and datefin > '".date('Y-m-d')."'");
	$contenu.="$promo[prix];";
	$conseils=$DB_site2->query("SELECT a.artcode FROM article_conseil ac inner join article a on (ac.artid_conseille = a.artid) where ac.artid = '$article[artid]' order by a.artcode limit 4");
	if ($DB_site2->num_rows($conseils) > 0)
		{
		$n = 0 ;
		while ($conseil=$DB_site2->fetch_array($conseils))
			{
			$n++ ;
			$contenu .= "$conseil[artcode];" ;
			}
		while ($n < 4)
			{
			$contenu.=";";
			$n++;
			}
		}
	else
		{
		$contenu.=";";
		$contenu.=";";
		$contenu.=";";
		$contenu.=";";
		}
	if ($article[tauxchoisi] == 0)
		{
		$contenu.="0;";
		}
	else
		{
		switch ($article[tauxchoisi])
			{
			case 1:
				$tvataux = "TVAtauxnormal" ;
				break;
			case 2:
				$tvataux = "TVAtauxreduit" ;
				break;
			}
		$tauxtva=$DB_site2->query_first("SELECT $tvataux FROM pays where paysid = '57'");
		$contenu.="$tauxtva[$tvataux];";
		}
	$contenu.="$article[poid];";
	if ($article[image] != NULL)
		{
		$contenu.="http://$host/configurations/$host/images/produits/$article[artid].$article[image];";
		}
	else
		{
		$contenu.=";";
		}
	$imgsup=$DB_site2->query_first("SELECT articlephotoid, image FROM articlephoto where artid = '$article[artid]' limit 1");
	if ($imgsup[articlephotoid] != 0)
		{
		$contenu .= "http://$host/configurations/$host/images/produits/$article[artid]_$imgsup[articlephotoid].$imgsup[image];" ;
		}
	else
		{
		$contenu.=";";
		}
	$contenu.="$article[libelleDE];";
	$article[descriptionDE]=str_replace(chr(10), "", $article[descriptionDE]);
	$article[descriptionDE]=str_replace(chr(13), "", $article[descriptionDE]);
	$article[descriptionDE] = str_replace(";", "", $article[descriptionDE]) ;
	$contenu.="$article[descriptionDE];";
	$contenu.="$article[libelleEN];";
	$article[descriptionEN]=str_replace(chr(10), "", $article[descriptionEN]);
	$article[descriptionEN]=str_replace(chr(13), "", $article[descriptionEN]);
	$article[descriptionEN] = str_replace(";", "", $article[descriptionEN]) ;
	$contenu.="$article[descriptionEN];";
	$contenu.="$article[libelleIT];";
	$article[descriptionIT]=str_replace(chr(10), "", $article[descriptionIT]);
	$article[descriptionIT]=str_replace(chr(13), "", $article[descriptionIT]);
	$article[descriptionIT] = str_replace(";", "", $article[descriptionIT]) ;
	$contenu.="$article[descriptionIT];";
	$contenu.="$article[libelleSP];";
	$article[descriptionSP]=str_replace(chr(10), "", $article[descriptionSP]);
	$article[descriptionSP]=str_replace(chr(13), "", $article[descriptionSP]);
	$article[descriptionSP] = str_replace(";", "", $article[descriptionSP]) ;
	$contenu.="$article[descriptionSP];";
	$stock=$DB_site2->query_first("SELECT * FROM stock where artid = '$article[artid]'");
	$contenu.="$stock[nombre];$stock[seuil];$stock[delaiappro];";
	$contenu.=$caracts;
	$contenu .= ";$article[commandable]" ;
	$contenu .= ";$article[active]" ;
	$mq=$DB_site2->query_first("SELECT libelle FROM marque m inner join article_marque am on (m.marqueid = am.marqueid) where artid = '$article[artid]'");
	$contenu .= ";$mq[libelle]" ;
	fwrite($handle, stripslashes(html_entity_decode($contenu)));
}

fclose($handle);
$file = $rootpath."admin/".$filename; 
header('Content-Description: File Transfer'); 
header('Content-Type: application/force-download'); 
header('Content-Length: ' . filesize($file)); 
header('Content-Disposition: attachment; filename=' . basename($file)); 
readfile($file); 
exit;
 
?>
