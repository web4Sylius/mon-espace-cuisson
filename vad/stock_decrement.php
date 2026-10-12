<?

/** Decrementer le stock pour un article et ses caractéristique
* $dbLink : Objet Mysql
* $artid : Identifiant de l'article
* $caractval_array : Tableau de valeurs de caractéristiques
* $delta : Valeur à modifier dans le stock
*/
function stock_decrement($dbLink, $artid, $caractval_array = array(), $delta = '-1') {

	//requete pour remettre les caracteristique dans l'ordre
	if(sizeof($caractval_array)) {
		$caractval_query = "SELECT caractvalid, caractid FROM caracteristiquevaleur INNER JOIN article_caractval USING(caractvalid) WHERE caractvalid IN(".implode(",",$caractval_array).") AND article_caractval.artid = '$artid' ORDER BY caractid ASC;";
	
		$caractval_req = $dbLink->query($caractval_query,"logstock",$artid);
		$caractval_array = array();
		
		while($caractval = $dbLink->fetch_array($caractval_req)) {
	
			$nb_caract_req = $dbLink->query_first("SELECT count(*) FROM article_caractval INNER JOIN caracteristiquevaleur USING(caractvalid) WHERE caractid = '$caractval[caractid]' AND artid = '$artid';","logstock",$artid);
	
			if($nb_caract_req[0] > 1)			
				$caractval_array[] = $caractval['caractvalid'];
		}
	}
	
	//verifier que la branche de l'arbre existe
	$verify_query = "SELECT stockid FROM stocks WHERE artid = '$artid' AND caractvalid = '0' AND parentid = '0' LIMIT 1;";
	$verify_req = $dbLink->query_first($verify_query,"logstock",$artid);
	if($verify_req) {
		$lastparentid = $verify_req['stockid'];
		//verifier les sous branches existent
		foreach($caractval_array as $key => $value) {
			$verify_query = "SELECT stockid FROM stocks WHERE artid = '$artid' AND caractvalid = '$value' AND parentid = '$lastparentid' LIMIT 1;";	
			$verify_req = $dbLink->query_first($verify_query,"logstock",$artid);
			if(!$verify_req) {
				$dbLink->query("INSERT INTO stocks (caractvalid, artid, parentid) VALUES('$value', '$artid', '$lastparentid');");
				$lastparentid = $dbLink->insert_id();
			}
			else {
				$lastparentid = $verify_req['stockid'];
			}
		}
	}
	else {
		$dbLink->query("INSERT INTO stocks (artid) VALUES('$artid');","logstock",$artid);
		$lastparentid = $dbLink->insert_id();
	}
	
	$to_update = sizeof($caractval_array) ? end($caractval_array) : 0;
	//Mettra a jour la ligne qui nous interesse dans l'arbre
	$update_query = "UPDATE stocks SET total = total + ($delta) WHERE stockid = '$lastparentid';";
	$dbLink->query($update_query,"logstock",$artid);
	
	// mises à jour
	$nblignes=$dbLink->query_first("select count(*) from stocks where artid = '$artid';","logstock",$artid);
	if ($nblignes[0] == 1)
		$dbLink->query("update stock set nombre = (select total from stocks where artid = '$artid') where artid = '$artid';","logstock",$artid);
	else
		{
		$totalligne=$dbLink->query_first("select sum(total) from stocks where artid = '$artid' and parentid != '0';","logstock",$artid);
		$dbLink->query("update stocks set total = '$totalligne[0]' where artid = '$artid' and parentid = '0';","logstock",$artid);
		$dbLink->query("update stock set nombre = '$totalligne[0]' where artid = '$artid';","logstock",$artid);
		}
}
/*function stock_decrement($dbLink, $artid, $caractval_array = array(), $delta = '-1') {

	//requete pour remettre les caracteristique dans l'ordre
	if(sizeof($caractval_array)) {
		$caractval_query = "SELECT caractvalid, caractid FROM caracteristiquevaleur INNER JOIN article_caractval USING(caractvalid) WHERE caractvalid IN(".implode(",",$caractval_array).") AND article_caractval.artid = '$artid' ORDER BY caractid ASC;";
	
		$caractval_req = $dbLink->query($caractval_query);
		$caractval_array = array();
		
		while($caractval = $dbLink->fetch_array($caractval_req)) {
	
			$nb_caract_req = $dbLink->query_first("SELECT count(*) FROM article_caractval INNER JOIN caracteristiquevaleur USING(caractvalid) WHERE caractid = '$caractval[caractid]';");
	
			if($nb_caract_req[0] > 1)			
				$caractval_array[] = $caractval['caractvalid'];
		}
	}
	
	//verifier que la branche de l'arbre existe
	$verify_query = "SELECT stockid FROM stocks WHERE artid = '$artid' AND caractvalid = '0' AND parentid = '0' LIMIT 1;";
	$verify_req = $dbLink->query_first($verify_query);
	if(!$verify_req)
		$dbLink->query("INSERT INTO stocks (artid) VALUES('$artid')");	
	
	$lastparentid = $verify_req['stockid'];
	//verifier les sous branches existent
	foreach($caractval_array as $key => $value) {
		$verify_query = "SELECT stockid FROM stocks WHERE artid = '$artid' AND caractvalid = '$value' AND parentid = '$lastparentid' LIMIT 1;";	
		$verify_req = $dbLink->query_first($verify_query);
		if(!$verify_req) {
			$dbLink->query("INSERT INTO stocks (caractvalid, artid, parentid) VALUES('$value', '$artid', '$lastparentid')");
			$lastparentid = $dbLink->insert_id();
		}
		else
			$lastparentid = $verify_req['stockid'];
	}
	
	$to_update = sizeof($caractval_array) ? end($caractval_array) : 0;
	//Mettra a jour la ligne qui nous interesse dans l'arbre
	$update_query = "UPDATE stocks SET total = total + ($delta) WHERE stockid = '$lastparentid';";
	$dbLink->query($update_query);

	$totalarticle=$dbLink->query_first("select SUM(total) from stocks where artid = '$artid'");
	$dbLink->query("INSERT INTO stock (artid, nombre) VALUES('$artid', '$totalarticle[0]') ON DUPLICATE KEY UPDATE nombre = '$totalarticle[0]';");
}*/

?>