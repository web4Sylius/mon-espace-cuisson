<?php

$prov="comp" ;
require "admin_global.php";

/*////////////////////////////////
// FONCTIONS AJAX FICHIER FACTURE.PHP
////////////////////////////////*/

if(isset($_GET['contact'])){
  if($_GET['contact']=="" || strlen($_GET['contact'])<3) exit;
  $txt=$_GET['contact'];

  /*$requete=$DB_site2->query("SELECT * FROM fournisseur WHERE (societe LIKE '%$txt%' OR nom LIKE '%$txt%' OR fonction LIKE '%$txt%') AND mail<>'' ORDER BY nom");
  while($contact=$DB_site2->fetch_array($requete)){
    $nom=utf8_encode($contact['nom'])." ".utf8_encode($contact['prenom']);
    print "<a href=\"javascript:selectcontact('fourn','$contact[id]');\" title=\"$contact[mail]\"><img src=\"/admin/images/pro.png\" height=\"20\" border=\"0\" align=\"absmiddle\">".surligne($nom,$txt)." (".utf8_encode(surligne($contact['fonction']." ".$contact['societe'],$txt)).")</a><br>";
  }*/

  $requete=$DB_site2->query("SELECT * FROM utilisateur WHERE (raisonsociale LIKE '%$txt%' OR nom LIKE '%$txt%') AND mail<>'' ORDER BY nom");
  while($contact=$DB_site2->fetch_array($requete)){
    $nom=utf8_encode($contact['raisonsociale']." ".$contact['nom'])." ".utf8_encode($contact['prenom']);
    print "<a href=\"javascript:selectcontact('user','$contact[userid]');\" title=\"$contact[mail]\"><img src=\"/admin/images/client.png\" height=\"20\" border=\"0\" align=\"absmiddle\">".$contact['userid']." ".surligne($nom,$txt)."</a><br>";
  }
}

if(isset($_GET['contactinfo'])){
   switch($_GET['type']){
      case "fourn":
      $fourn=$DB_site2->query_first("SELECT * FROM fournisseur WHERE id=".$_GET['contactinfo']);
      print utf8_encode($fourn[societe])."|".utf8_encode($fourn[nom])."|".utf8_encode($fourn[prenom])."|".$fourn[mail]."|".$fourn[telephone]."|".utf8_encode($fourn[adresse1])."|".$fourn[cp]."|".utf8_encode($fourn[ville])."|".utf8_encode($fourn[paysid]);
      break;
      
      case "user":
      $utilisateur=$DB_site2->query_first("SELECT * FROM utilisateur WHERE userid=".$_GET['contactinfo']);
      print utf8_encode($utilisateur[raisonsociale])."|".utf8_encode($utilisateur[nom])."|".utf8_encode($utilisateur[prenom])."|".utf8_encode($utilisateur[mail])."|".$utilisateur[telephone]."|".utf8_encode($utilisateur[adresse])."|".$utilisateur[codepostal]."|".utf8_encode($utilisateur[ville])."|".$utilisateur[paysid];
      break;
   }
}

function surligne($txt,$src){
    return preg_replace( '/('.$src.')/i', '<b>$1</b>',$txt);
}

/*//////////
// Fonction liste_article_categorie
// Permet de retrouver tous les articles d'une catégorie
// Paramètres : $catid
// Resultat : retourne un select avec la liste des articles
//////////*/
if(isset($action) && $action == 'liste_article_categorie')
{
	$reponse = '<select id="select_articles_nouveau_article" name="select_articles_nouveau_article" ';
	$reponse .= 'onchange="recherche_caracteristique()">';
	$reponse .= '<option value="-1">Selectionnez l\'article</option>';
	
	$rq_listearticle = $DB_site2->query("SELECT artid, artcode, $libelle as libelle FROM article WHERE catid = '$categorie'");
	while($rs_listearticle = $DB_site2->fetch_array($rq_listearticle))
	{
		$reponse .= "<option value=\"$rs_listearticle[artid]\">$rs_listearticle[libelle] ($rs_listearticle[artcode])</option>";
	}
	$reponse .= '</select>';
	
	echo $reponse;
	exit();
}

/*//////////
// Fonction liste_caracteristique_article
// Permet de retrouver toutes les caracteristiques d'un article
// Paramètres : $artid
// Resultat : retourne un tableau avec la liste des caracteristiques
//////////*/
if(isset($action) && $action == 'liste_caracteristique_article')
{
	$reponse = "<table border=\"0\" width=\"100%\" style=\"display:inline\"><tr>";
	$tab_caract = '';
	$zone_liste_caract = '';
	//On récupère la liste des caractéristiques de la categorie
	$sql = "SELECT marqueid";
	$sql .= " FROM article_marque";
	$sql .= " WHERE artid = '".$article."'";
	$article_marque = $DB_site2->query_first($sql)[0];

	$rq_caracteristiques=$DB_site2->query("SELECT DISTINCT(c.caractid), c.$libelle as libelle
			FROM article_caractval ac INNER JOIN caracteristiquevaleur cv ON (cv.caractvalid = ac.caractvalid)
			INNER JOIN caracteristique c ON (cv.caractid = c.caractid)
			WHERE ac.artid = '$article' AND c.marqueid = '".$article_marque."'
			ORDER BY c.caractid ASC");
	
	$nb_caract = 0;				
	while($rs_caracteristique=$DB_site2->fetch_array($rq_caracteristiques))
	{
		//Pour chaque caracteristique, on liste les valeurs possibles
		$reponse .= '<td align="left"><div id="caract_nouveau_article_'.$nb_caract.'" style="display:inline">'.$rs_caracteristique[libelle];
		$reponse .= '</div><br><select id="caractval_nouveau_article_'.$nb_caract.'" name="caractval_nouveau_article_'.$nb_caract.'" onchange="document.getElementById(\'qte_nouveau_article\').focus();">';
		
		//On récupère la liste des caracteristiquevaleur associées
		$rq_caracteristiquevaleur = $DB_site2->query("SELECT cv.caractvalid, cv.$libelle as libelle , ac.differenceprix
				FROM caracteristiquevaleur cv INNER JOIN caracteristique c ON(cv.caractid = c.caractid)
				INNER JOIN article_caractval ac ON (ac.caractvalid = cv.caractvalid)
				WHERE cv.caractid = $rs_caracteristique[caractid]
				AND ac.artid = '$article' AND c.marqueid = '".$article_marque."'
				ORDER BY cv.caractvalid ASC");

		while($rs_caracteristiquevaleur = $DB_site2->fetch_array($rq_caracteristiquevaleur))
		{
			$reponse .= "<option value=\"$rs_caracteristiquevaleur[caractvalid]\" ";
			$reponse .= ">$rs_caracteristiquevaleur[libelle] ";

			/*if($rs_caracteristiquevaleur[differenceprix] > 0)
				$reponse .= 	"(+$rs_caracteristiquevaleur[differenceprix] &euro;)";
			elseif($rs_caracteristiquevaleur[differenceprix] < 0)
				$reponse .= 	"($rs_caracteristiquevaleur[differenceprix] &euro;)";*/
			$reponse .= "</option>";
		}
		$nb_caract++;
		$reponse .= '</select></td>';
	}
	if ($nb_caract != 0)
	{
		$reponse .= '</table>';
	}else
	{
		$reponse = '';
	}
	/*if ($reponse == "<table border=\"0\" width=\"100%\"><tr>")
		$reponse = '';
	else
		$reponse .= '</table>';
	*/
	$reponse .='<div id="nb_caract_nouveau_article" style="display:none">'.$nb_caract.'</div>';

	echo $reponse;
	exit();
}

/*//////////
// Fonction ajouter_article
// Permet d'ajouter un article à la facture
// Paramètres : facturid, catid, artid, caract_libelle[], caractval_libelle[], qte
// Resultat : 
//////////*/
if(isset($action) && $action == 'ajouter_article')
	{
	$liste_caract_libelle = explode("|",$caract_libelle);
	$liste_caractval_libelle = explode("|",$caractval_libelle);
	$liste_caractvalid = explode("|",$caractvalid);
	$article_present = false;
	
	$rq_caracteristiques_select = 'SELECT l.lignefactureid, l.qte ';
	$rq_caracteristiques_from = 'FROM lignefacture l ';
	$rq_caracteristiques_inner = '';
	$rq_caracteristiques_where = 'WHERE factureid = \''.$facturid.'\' AND catid = \''.$catid.'\' AND artid = \''.$artid.'\'';
	$rq_caracteristiques_and = '';

	if ($nbcaract > 0)
		{//On ajoute les informations des caractéristiques à la requête
		$rq_caracteristiques_on = 'l';

		for($i=0 ; $i < $nbcaract ; $i++)
			{
			//Création de la ligne INNER JOIN
			$rq_caracteristiques_inner .= 'INNER JOIN lignefacturecaracteristique lc'.$i.' ON ('.$rq_caracteristiques_on.'.lignefactureid = lc'.$i.'.lignefactureid) ';
			$rq_caracteristiques_on = 'lc'.$i;
			//Creation de la ligne AND
			$rq_caracteristiques_and .= 'AND lc'.$i.'.libelle_caract = \''.$liste_caract_libelle[$i].'\' ';
			$rq_caracteristiques_and .= 'AND lc'.$i.'.libelle_caractval = \''.$liste_caractval_libelle[$i].'\' ';
			}			
		}

	//Construction de notre requête
	$rq_present = $rq_caracteristiques_select.$rq_caracteristiques_from.$rq_caracteristiques_inner.$rq_caracteristiques_where.$rq_caracteristiques_and;	
	$rs_present = $DB_site2->query_first($rq_present);
	
	if ($rs_present[lignefactureid] != '')
		$article_present = true;
	
	if($article_present)
		{
		$DB_site2->query('UPDATE lignefacture SET qte = (qte+\''.$qte.'\') WHERE lignefactureid = \''.$rs_present[lignefactureid].'\'');
		}
	else
		{
		//On récupère les informations sur le produit
		$rs_article = $DB_site2->query_first("SELECT a.artcode, a.$libelle as libelle, a.tva, a.tauxchoisi, a.prix, a.poid FROM article a WHERE artid = '$artid'");
		
		//On récupère le taux de tva
		$taux=$DB_site2->query_first("SELECT TVAtauxnormal, TVAtauxreduit FROM pays WHERE paysid = '$paysid'");
		
		//On vérifie si une promotion est en cours
		if($rs_promo = $DB_site2->query_first("SELECT prix FROM promotion WHERE artid = '$artid'
							AND datedebut <= '" .date( "Y-m-d" ). "' AND datefin > '" .date( "Y-m-d" ). "'"))
			{
			//Une promotion est en cours
			if ($rs_promo[prix] != "")
				{	// Article en cours de Promotion
				$prix_article = $rs_promo[prix];
				}
			}
		else
			$prix_article = $rs_article[prix];
		
		//On définit le taux de tva à appliquer
		if ($rs_article[tauxchoisi] == '1')
			$tauxarticle = $taux[TVAtauxnormal] ;
		elseif ($rs_article[tauxchoisi] == '2')
			$tauxarticle = $taux[TVAtauxreduit] ;
		else
			$tauxarticle = 0 ;
			
			
		//On peut maintenant ajouter la ligne à notre facture
		$DB_site2->query("INSERT IGNORE INTO lignefacture (factureid, artid, catid, artcode, qte, libelle, prix, tva) 
							VALUES ('$facturid', '$artid', '$catid', '$rs_article[artcode]', '$qte',
							'".addslashes($rs_article[libelle])."', '$prix_article', '$tauxarticle')");
		
		$lignefactureid=$DB_site2->insert_id();

		// On ajoute ensuite les caractéristiques de l'article
		for($i=0;$i<$nbcaract;$i++)
			{
			$diff=$DB_site2->query_first("SELECT differenceprix FROM article_caractval 
					WHERE caractvalid = '$liste_caractvalid[$i]' AND artid = '$artid'") ;
		
			if($diff[differenceprix] != 0)
				{
				$DB_site2->query("UPDATE lignefacture SET prix = prix + '$diff[differenceprix]' WHERE lignefactureid = '$lignefactureid'");
				}
	
			$DB_site2->query("INSERT IGNORE INTO lignefacturecaracteristique (lignefactureid, libelle_caract, libelle_caractval) 
							VALUES ('$lignefactureid', '$liste_caract_libelle[$i]', '$liste_caractval_libelle[$i]')");				
			}
		
		}//Fin if(l'article est il déjà present dans lignefacture)
	
	//MAJ des frais de port	
	$prixfraisport = calcul_frais_de_port($facturid, $DB_site2);
	$DB_site2->query("UPDATE facture SET prixport = '$prixfraisport' WHERE facturid = '$facturid'");

	exit();
	}

/*//////////
// Fonction supprimer_article
// Parametres : $lignefactureid, $facturid
// Supprime un article de la commande
//////////*/
if(isset($action) && $action == "supprimer_article")
	{	
	$rs_dellignefacturechamp = $DB_site2->query("DELETE FROM lignefacturechamp WHERE lignefactureid=$lignefactureid");
	$rs_dellignefacturecaract = $DB_site2->query("DELETE FROM lignefacturecaracteristique WHERE lignefactureid=$lignefactureid");
	$rs_dellignefacture = $DB_site2->query("DELETE FROM lignefacture WHERE lignefactureid=$lignefactureid");
	
	//MAJ des frais de port	
	$prixfraisport = calcul_frais_de_port($facturid, $DB_site2);
	$DB_site2->query("UPDATE facture SET prixport = '$prixfraisport' WHERE facturid = '$facturid'");
		
	//TODO : si plus d'article, supprime-t-on la facture ?
	}


/*//////////
// Fonction modifier_article
// Parametres : 
// Modifie un article de la commande
//////////*/
if(isset($action) && $action == "modifier_article")
	{
	sleep(1);
	if($caractvalid == "")
		$nb_caract = 0;
	else
	{
		$caracteristiques = explode("|",$caractvalid);
		$nb_caract = count($caracteristiques);
	}
	//MAJ de lignefacturecaracteristique
	if ($nb_caract > 0)
	{
		//On vérifie qu'un article avec les nouvelles caractéristiques n'existe pas déjà
		$rq_caracteristiques_select = 'SELECT l.lignefactureid, l.qte ';
		$rq_caracteristiques_from = 'FROM lignefacture l ';
		$rq_caracteristiques_inner = '';
		$rq_caracteristiques_where = 'WHERE factureid = \''.$facturid.'\' AND catid = \''.$catid.'\' AND artid = \''.$artid.'\'';
		$rq_caracteristiques_and = 'AND l.lignefactureid !=\''.$lignefactureid.'\' ';
	
		$rq_caracteristiques_on = 'l';

		for($i=0;$i<$nb_caract;$i++)
		{
			//Récupération des libelles des caracteristiques
			$rs_caract_lib = $DB_site2->query_first("SELECT cv.$libelle as libellecaractval, c.$libelle as libellecaract
									FROM caracteristiquevaleur cv INNER JOIN caracteristique c ON (cv.caractid = c.caractid)  
									WHERE cv.caractvalid = $caracteristiques[$i]");
			//Création de la ligne INNER JOIN
			$rq_caracteristiques_inner .= 'INNER JOIN lignefacturecaracteristique lc'.$i.' ON ('.$rq_caracteristiques_on.'.lignefactureid = lc'.$i.'.lignefactureid) ';
			$rq_caracteristiques_on = 'lc'.$i;
			//Creation de la ligne AND
			$rq_caracteristiques_and .= 'AND lc'.$i.'.libelle_caract = \''.$rs_caract_lib[libellecaract].'\' ';
			$rq_caracteristiques_and .= 'AND lc'.$i.'.libelle_caractval = \''.$rs_caract_lib[libellecaractval].'\' ';
		}			
		//Construction de notre requête
		$rq_present = $rq_caracteristiques_select.$rq_caracteristiques_from.$rq_caracteristiques_inner.$rq_caracteristiques_where.$rq_caracteristiques_and;
		$rs_present = $DB_site2->query_first($rq_present);

		if($rs_present[lignefactureid] != '')
		{
			//Un article similaire est déjà présent, on le met à jour avec les nouvelles valeurs
			//On supprime dans un premier temps l'article mis à jour
			$rs_dellignefacturechamp = $DB_site2->query("DELETE FROM lignefacture WHERE lignefactureid=$lignefactureid");
			$rs_dellignefacturecaract = $DB_site2->query("DELETE FROM lignefacturecaracteristique WHERE lignefactureid=$lignefactureid");
			//on met à jour lignefactureid qui prends la valeur de l'autre article identique
			$lignefactureid = $rs_present[lignefactureid];
			//Seul la quantité à besoin d'être modifiée
			$rq_maj_qte_prix = 'qte = (qte+\''.$qte.'\') ';

		}else
		{
			
			//Aucun article ne correspond, on peut modifier notre article tranquillement
			$lignefacturecaracteristiqueid = explode("|",$lignefacturecaractid);
			//Pour chaque caracteristiques, on cherche le libellé puis la difference de prix
			for($i=0;$i < $nb_caract; $i++)
			{
				$rs_caract = $DB_site2->query_first("SELECT cv.$libelle as libellecaractval,
										c.$libelle as libellecaract 
										FROM caracteristiquevaleur cv INNER JOIN caracteristique c ON (cv.caractid = c.caractid) 
										WHERE cv.caractvalid = $caracteristiques[$i]");
				
				$rs_diffprix = $DB_site2->query_first("SELECT ac.differenceprix 
										FROM caracteristiquevaleur cv INNER JOIN article_caractval ac ON (cv.caractvalid = ac.caractvalid) 
										WHERE cv.caractvalid = $caracteristiques[$i]");
										
				if($rs_diffprix[differenceprix]!='')
					$diffprix += $rs_diffprix[differenceprix];
									
				$rs_updatelignefacturecaract = $DB_site2->query("UPDATE lignefacturecaracteristique
										SET libelle_caract = '$rs_caract[libellecaract]',
										libelle_caractval = '$rs_caract[libellecaractval]'
										WHERE lignefacturecaracteristiqueid = $lignefacturecaracteristiqueid[$i]");
			}
			//On ajoute la différence au prix de l'article
			$rs_prixarticle = $DB_site2->query_first("SELECT prix FROM article WHERE artid = $artid");
			$prixfinal = $rs_prixarticle[prix] + $diffprix;
			$rq_maj_qte_prix = 'qte = \''.$qte.'\', prix = \''.$prixfinal.'\' ';
		}//end if($rs_present[lignefactureid] != '')
	}else
	{	
		//Article sans caracteristique, il ne peut donc y avoir de doublons
		//On modifie donc uniquement la quantité, le ayant déjà été calculé
		$rq_maj_qte_prix = 'qte = \''.$qte.'\' ';
	}//end if (($nb_caract = count($caracteristiques)) > 0)
		
	//On modifie la lignefacture
	$rs_updatelignefacture = $DB_site2->query("UPDATE lignefacture SET $rq_maj_qte_prix
									WHERE lignefactureid = $lignefactureid");
	
	//MAJ des frais de port	
	$prixfraisport = calcul_frais_de_port($facturid, $DB_site2);
	$DB_site2->query("UPDATE facture SET prixport = '$prixfraisport' WHERE facturid = '$facturid'");
}




/*//////////
// Fonction calcul_frais_de_port
// Parametres : $facturid
// Calcule les frais de port pour une facture
// Version AJAX et version FONCTION
//////////*/
if(isset($action) && $action == 'calcul_frais_de_port')
{
	//Prix total de la commande
	$prixtotal = $DB_site2->query_first("SELECT SUM(lf.qte*lf.prix) as prixtotal
					FROM facture f INNER JOIN lignefacture lf ON (f.facturid = lf.factureid) 
					WHERE f.facturid = '$facturid'");
					
	//Poids total de la facture
	$poidstotal = $DB_site2->query_first("SELECT SUM(a.poid*lf.qte) as poidstotal 
					FROM article a INNER JOIN lignefacture lf ON (a.artid = lf.artid) 
					WHERE lf.factureid = '$facturid'");
							
	// frais de port		
	$gratuitfrais = $DB_site2->query_first("SELECT distinct gratuit 
									FROM fraisport fp INNER JOIN facture f ON (fp.paysid = f.paysid) 
									WHERE f.facturid = '$facturid'");
									
	if (($gratuitfrais[gratuit] != 0) and ($prixtotal[prixtotal] >= $gratuitfrais[gratuit])) 
		// si on paie pas de frais de port !!
		echo '0';
	else 
	{
		// on fait tout le traitement des frais de port !!
		$paysid = $DB_site2->query_first("SELECT paysid FROM facture WHERE facturid = $facturid");

		$fraisport=$DB_site2->query_first("SELECT * FROM fraisport WHERE poiddebut <= '$poidstotal[poidstotal]' AND poidfin >= '$poidstotal[poidstotal]' 
											and paysid = '$paysid[paysid]'");
											
		if ($fraisport[fraisportid] == "")
		{
			// On prend le frais de port par défaut
			$rs_fraisportpays = $DB_site2->query_first("SELECT prixfraisport FROM pays WHERE paysid = '$paysid[paysid]'");
			$prixfraisport = $rs_fraisportpays[prixfraisport];
		}else
			//On prend le frais de port correspondant
			$prixfraisport = $fraisport[prix];
			
		echo $prixfraisport;
	}								
}

function calcul_frais_de_port($facturid, $DB_site2)
{
	//Prix total de la commande
	$prixtotal = $DB_site2->query_first("SELECT SUM(lf.qte*lf.prix) as prixtotal
					FROM facture f INNER JOIN lignefacture lf ON (f.facturid = lf.factureid) 
					WHERE f.facturid = '$facturid'");
					
	//Poids total de la facture
	$poidstotal = $DB_site2->query_first("SELECT SUM(a.poid*lf.qte) as poidstotal 
					FROM article a INNER JOIN lignefacture lf ON (a.artid = lf.artid) 
					WHERE lf.factureid = '$facturid'");
							
	// frais de port		
	$gratuitfrais = $DB_site2->query_first("SELECT distinct gratuit 
									FROM fraisport fp INNER JOIN facture f ON (fp.paysid = f.paysid) 
									WHERE f.facturid = '$facturid'");
									
	if (($gratuitfrais[gratuit] != 0) and ($prixtotal[prixtotal] >= $gratuitfrais[gratuit])) 
		// si on paie pas de frais de port !!
		return 0;

	else 
	{
		// on fait tout le traitement des frais de port !!
		$paysid = $DB_site2->query_first("SELECT paysid FROM facture WHERE facturid = $facturid");

		$fraisport=$DB_site2->query_first("SELECT * FROM fraisport WHERE poiddebut <= '$poidstotal[poidstotal]' AND poidfin >= '$poidstotal[poidstotal]' 
											and paysid = '$paysid[paysid]'");
											
		if ($fraisport[fraisportid] == "")
		{
			// On prend le frais de port par défaut
			$rs_fraisportpays = $DB_site2->query_first("SELECT prixfraisport FROM pays WHERE paysid = '$paysid[paysid]'");
			$prixfraisport = $rs_fraisportpays[prixfraisport];
		}else
			//On prend le frais de port correspondant
			$prixfraisport = $fraisport[prix];
			
		return $prixfraisport;
	}								
}

/*////////////////////////////////
// FIN FONCTIONS AJAX
////////////////////////////////*/
?>
