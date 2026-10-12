<?php
//Script Garantie
//Utilisé pour affecter une seule garantie par marque, et non plusieurs
//Affecte aussi article_garantie_liste, garantie_tranche, lignefacturegarantie

require_once($_SERVER['DOCUMENT_ROOT']."/global.php");

//Récupération des garanties composées de plusieurs marques
$sql = "SELECT *";
$sql.= " FROM garantie";
$sql.= " WHERE marques LIKE '%,%'";
$query = $DB_site->query($sql);

//Affecte les marques par garantie dans un tableau
while($row = $DB_site->fetch_array($query)){
	$tabGarantie[] = $row;
}

//Boucle sur chacune des valeurs du tableau
foreach($tabGarantie as $ligne => $val){
	//INSERT des nouvelles garanties
	garantie_UP($val[0], $val[1], $val[2], $val[3], $val[4], $val[5], $val[6], $val[7], $val[8], $val[9], $val[10], $val[11], $val[12], $val[13], $val[14]);
	
	//UPDATE des anciennes garanties ->comm .= 'OBSELETE (à conserver)'
	$sql = "UPDATE garantie";
	$sql.= " SET comm = '$val[13] - OBSELETE (à conserver)'";
	$sql.= " WHERE garantieid = $val[0]";
	$DB_site->query($sql);
}

echo "Script_garantie_marque.php : SUCCESS !"; // Bravo !


//Fonction INSERT garantie + suppléments de fonction
function garantie_UP($id, $fourn, $marques, $tProd, $uGarantie, $pays, $duree, $tGarantie, $paht, $devise, $prix, $ppi, $cumul, $comm, $gClient){
	global $DB_site;
	
	$tabMarques = explode(',', $marques);

	foreach($tabMarques as $cle => $marque){
		//INSERT des nouvelles garanties
		$sql = "INSERT INTO garantie (fournisseur, marques, types_produits, utilisation_garantie, pays, duree, type_garantie, paht, devise, prix, ppi, cumul, comm, garantie_client_id)";
		$sql.= " VALUES('$fourn', '$marque', '$tProd', $uGarantie, '$pays', $duree, $tGarantie, $paht, '$devise', '$prix', '$ppi', $cumul, '$comm', $gClient)";
		$DB_site->query($sql);
		$insertID = $DB_site->insert_id();
		
		//UPDATE des garanties de lignefacturegarantie
		lignefacturegarantie_UP($id, $marque, $insertID);
		
		//INSERT des tranches de prix liées à la garantie
		garantie_tranche_UP($id, $marque, $insertID);
		
		//INSERT des associations article->garantie
		article_gl_UP($id, $marque, $insertID);
	}
	
	//SUPPRESSION de toutes les associations 'OBSELETE'
	$del = "DELETE FROM article_garantie_liste";
	$del.= " WHERE garantieid = $id";
	$DB_site->query($del);
}

//Fonction INSERT article_garantie_liste
function article_gl_UP($garantie, $marque, $insertID){
	global $DB_site;
	
	$sqlUP = "SELECT *";
	$sqlUP.= " FROM article_garantie_liste a";
	$sqlUP.= " INNER JOIN article_marque am";
	$sqlUP.= " ON (a.artid = am.artid)";
	$sqlUP.= " WHERE a.garantieid = ".$garantie;
	$sqlUP.= " AND am.marqueid = ".$marque;
	$queryUP = $DB_site->query($sqlUP);
	
	while($row = $DB_site->fetch_array($queryUP)){
		$tabArticle[] = $row; 
	}

	if(is_array($tabArticle)){
		foreach($tabArticle as $cle => $article){
			$sql = "INSERT INTO article_garantie_liste (artid, garantieid, paht, devise, prix, ppi)";
			$sql.= " VALUES($article[artid], $insertID, $article[paht], '$article[devise]', '$article[prix]', '$article[ppi]')";
			$DB_site->query($sql);
		}
	}
}

//Fontion INSERT garantie_tranche
function garantie_tranche_UP($garantie, $marque, $insertID){
	global $DB_site;
	
	$sqlUP = "SELECT *";
	$sqlUP.= " FROM garantie_tranche";
	$sqlUP.= " WHERE garantieid = ".$garantie;
	$queryUP = $DB_site->query($sqlUP);
	
	while($row = $DB_site->fetch_array($queryUP)){
		$tabTranche[] = $row;
	}

	if(is_array($tabTranche)){
		foreach($tabTranche as $cle => $tranche){
			$sql = "INSERT INTO garantie_tranche (garantie_tranche_prix_mini, garantie_tranche_prix_maxi, garantie_tranche_paht, garantie_tranche_prix, garantie_tranche_ppi, garantieid)";
			$sql.= " VALUES($tranche[garantie_tranche_prix_mini], $tranche[garantie_tranche_prix_maxi], $tranche[garantie_tranche_paht], '$tranche[garantie_tranche_prix]', '$tranche[garantie_tranche_ppi]', $insertID)";
			$DB_site->query($sql);
		}
	}
}

//Fonction UPDATE lignefacturegarantie
function lignefacturegarantie_UP($garantie, $marque, $insertID){
	global $DB_site;
	
	$sql = "UPDATE lignefacturegarantie";
	$sql.= " SET garantieid = ".$insertID;
	$sql.= " WHERE garantieid = ".$garantie;
	$sql.= " AND lignefactureid IN(";
	$sql.= "  SElECT lf.lignefactureid";
	$sql.= "  FROM lignefacture lf";
	$sql.= "  INNER JOIN article_marque am";
	$sql.= "  ON (am.artid = lf.artid)";
	$sql.= "  WHERE am.marqueid = ".$marque.")";
	$DB_site->query($sql);
}