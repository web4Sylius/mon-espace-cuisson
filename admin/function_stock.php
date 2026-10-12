<?php

/********** STOCK MANAGING FUNCTIONS **********/

/** Renvoie le nombre de stock restant pour un article et ses caractéristique
* $dbLink : Objet Mysql
* $artid : Identifiant d'article
* $caractval_array : Tableau de valeurs de caractéristiques
*/
function stock_get($dbLink, $artid, $caractval_array = array()) {
	
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
	
	if(sizeof($caractval_array))
		$last_result = "st".end($caractval_array);
	else
		$last_result = "stocks";
	
	//on choppe les stocks	
	$get_query = "SELECT $last_result.total FROM stocks";
	$last_value = "stocks";
	foreach($caractval_array as $value) {
		$get_query .= " INNER JOIN stocks as st$value ON(st$value.parentid = $last_value.stockid AND st$value.caractvalid = $value)";
		$last_value = "st$value";
	}			
	$get_query .= " WHERE stocks.artid = '$artid' AND stocks.caractvalid = '0' LIMIT 1;";
	
	//echo "$get_query";
	
	$get_req = $dbLink->query_first($get_query,"logstock",$artid);
	
	return $get_req['total'];
	
}

/** Renvoie un tableau contenant le detail des stocks pour cette article
* $dbLink : Objet Mysql
* $artid : Identifiant d'article
* $libelle : Choix du champ langue
*/
function stock_retrieve($dbLink, $artid, $libelle = "libelleFR", $orderby = "", $sens = "") 
	{
	$result_array = array();

	//recuperer les caracteristique de l'article
	$caract_req=$dbLink->query("SELECT caractid FROM article_caractval INNER JOIN caracteristiquevaleur USING(caractvalid) WHERE artid = '$artid' 
	GROUP BY artid, caractid HAVING(COUNT(caractvalid) > 1) ORDER BY caractid ASC;","logstock",$artid);

	$caract_array = array();
	while ($caract=$dbLink->fetch_array($caract_req))
		{
		$caract_array[] = $caract['caractid'];
		}

	//creer le tableau de combinaisons
	$combinaison_query = "SELECT article.artid".format_implode("", ", caract%%.caractvalid as cv%%, caract%%.lblval as lb%%, caract%%.lblcar as cl%%", $caract_array)." FROM article
							".format_implode("\n", "INNER JOIN (
											SELECT caracteristiquevaleur.$libelle as lblval, caracteristique.$libelle as lblcar, article_caractval.caractvalid, 
											article_caractval.artid FROM article_caractval 
											INNER JOIN caracteristiquevaleur USING(caractvalid) 
											INNER JOIN caracteristique USING(caractid) 
											WHERE caractid = '%%'
											) as caract%% ON(caract%%.artid = article.artid)", $caract_array)."
							WHERE article.artid = '$artid'".($orderby ? " ORDER BY $orderby $sens" : "").";";
	

	$combinaison_req=$dbLink->query($combinaison_query,"logstock",$artid);
	
	while ($combinaison=$dbLink->fetch_array($combinaison_req)) 
		{
		$key_chain = "";
		foreach ($caract_array as $caractid)
			$key_chain .= '_'.$combinaison['cv'.$caractid];
		foreach ($caract_array as $caractid) 
			{
			$result_array[$key_chain]['cl'.$caractid] = $combinaison['cl'.$caractid];
			$result_array[$key_chain]['lb'.$caractid] = $combinaison['lb'.$caractid];
			}
		}
	
	//recupere le stock sous forme de tableau
	$stock_query = "SELECT sts.artid, sts.total, sts.stockid".format_implode("", ", s%%.caractvalid as cv%%, s%%.stockid as st%%, s%%.total as ttl%%", $caract_array)."
			FROM (SELECT * FROM stocks WHERE stocks.artid = '$artid' AND stocks.caractvalid = '0') as sts";
	reset($caract_array);
	if (list($key, $value) = each($caract_array)) 
		{
		$stock_query .= "\nINNER JOIN stocks AS s$value ON ( s$value.parentid = sts.stockid )";
		$parent = $value;
		while (list($key2, $value2) = each($caract_array)) 
			{
			$stock_query .= "\nINNER JOIN stocks AS s$value2 ON ( s$value2.parentid = s$parent.stockid )";
			$parent = $value2;
			}
		}
	$stock_query .= ";";

	//peupler le tableau de combinaison
	$stock_req = $dbLink->query($stock_query,"logstock",$artid);
	
	while ($stock = $dbLink->fetch_array($stock_req)) 
		{
		$key_chain = "";
		$last_stockid = $stock['stockid'];
		$last_total = $stock['total'];
		foreach ($caract_array as $caractid) 
			{
			$key_chain .= '_'.$stock['cv'.$caractid];
			$last_stockid = $stock['st'.$caractid];
			$last_total = $stock['ttl'.$caractid];
			}
		$result_array[$key_chain]['value'] = $last_total;
		$result_array[$key_chain]['stockid'] = $last_stockid;
		}
	
	//echo "<pre>$combinaison_query\n\n\n$stock_query</pre>";
	if (!sizeof($result_array))
		$result_array = array("" => array());
	return $result_array;
	}

/** Efface des caractéristiques de tout les stocks ou d'articles
* $dbLink : Objet Mysql
* $caract_array : Tableau d'identifiant de caractéristique
* $art_array : Tableau d'identifiant d'article
*/
function stock_delete_caract($dbLink, $caract_array, $art_array = array()) {
	//requete de tout les caractvalid
	$caractval_query = $dbLink->query("SELECT caractvalid FROM caracteristiquevaleur WHERE caractid IN(".implode(",",$caract_array).");","logstock",implode(",",$art_array));
	$caractval_array = array();
	
	while($caractval = $dbLink->fetch_array($caractval_query))
		$caractval_array[] = $caractval['caractvalid'];
	
	//effacer les historique de tout les article concerné par les modification
	$dbLink->query("DELETE FROM stock_historique WHERE stockid IN(SELECT stockid FROM stocks WHERE artid IN(SELECT artid FROM stocks WHERE caractvalid IN(".implode(",", $caractval_array).")));","logstock",implode(",",$art_array));
	
	//create Temporary stock table
	$dbLink->query("CREATE TEMPORARY TABLE `tmp_stocks` (
		`stockid` int(6) unsigned NOT NULL auto_increment,
		`caractvalid` int(6) unsigned NOT NULL default '0',
		`parentid` int(6) unsigned NOT NULL default '0',
		`artid` int(6) unsigned NOT NULL,
		`total` int(6) NOT NULL default '0',
		PRIMARY KEY  (`stockid`)
			) ENGINE=Memory DEFAULT CHARSET=latin1;","logstock",implode(",",$art_array));
	
	//lock stocks table for updates
	$dbLink->query("LOCK TABLE stocks WRITE, stocks as cs WRITE, tmp_stocks WRITE;",implode(",",$art_array));
	
	//first node query by caractvalid...
	$nodes_query = "SELECT cs.stockid as cs_stockid, stocks.parentid, stocks.total FROM stocks 
		LEFT JOIN stocks as cs ON(cs.parentid = stocks.stockid)
		WHERE stocks.caractvalid IN(".implode(',',$caractval_array).")".(sizeof($art_array) ? " AND stocks.artid IN(".implode(",",$art_array).")" : '').";";

	//echo "<pre>".$nodes_query."</pre>";

	$nodes_req = $dbLink->query($nodes_query,"logstock",implode(",",$art_array));
	
	$nodes_array = array();
	$sum_array = array();
	$child_array = array();
	while($nodes = $dbLink->fetch_array($nodes_req)) {
		//c'est un noeud on remonte ses fils
		if($nodes['cs_stockid'] != NULL) {
			$nodes_array[$nodes['parentid']][] = $nodes['cs_stockid'];
		}
		
		//on fait la somme des noeuds pour avoir la valeur de stock à mettre dans parent
		//if(!isset($sum_array[$nodes['parentid']]))
			$sum_array[$nodes['parentid']] = $nodes['total'];
		/*else
			$sum_array[$nodes['parentid']] += $nodes['total'];*/
	
	}
	
	//on commence par remonter tout les noeuds qu'on a selectionner...
	foreach($sum_array as $k => $v) {
			//on met a jour le parent
			$dbLink->query("UPDATE stocks SET total = '".$v."' WHERE stockid = '".$k."' LIMIT 1;","logstock",implode(",",$art_array));
			
			//on efface tout les noeuds (c le but de la manoeuvre...)
			$dbLink->query("DELETE FROM stocks WHERE parentid = '".$k."';","logstock",implode(",",$art_array));
			
			//on remonte les sous-noeuds
			if(isset($nodes_array[$k]) && sizeof($nodes_array[$k])) {
				$implode = implode(",",$nodes_array[$k]);
				$child_array[] = $implode;
				//on copie les enregistrements et on les efface
				$dbLink->query("INSERT INTO tmp_stocks SELECT * FROM stocks WHERE stockid IN(".$implode.");","logstock",implode(",",$art_array));
				$dbLink->query("DELETE FROM stocks WHERE stockid IN(".$implode.");","logstock",implode(",",$art_array));
				$dbLink->query("UPDATE tmp_stocks SET parentid = '".$k."' WHERE stockid IN(".$implode.");","logstock",implode(",",$art_array));
			}
	}
	
	//tant qu'on a des noeuds a remonter
	while(isset($child_array) && sizeof($child_array)) {
		//on refait une selection sur les sous neouds cette fois
		$nodes_query = "SELECT cs.stockid as cs_stockid, tmp_stocks.parentid, tmp_stocks.total, tmp_stocks.caractvalid, tmp_stocks.artid FROM tmp_stocks 
			LEFT JOIN stocks as cs ON(cs.parentid = tmp_stocks.stockid)
			WHERE tmp_stocks.stockid IN(".implode(',',$child_array).");";
		
		$nodes_req = $dbLink->query($nodes_query,"logstock",implode(",",$art_array));
		
		//on peu vider la table temporaire
		$dbLink->query("DELETE FROM tmp_stocks WHERE 1;","logstock",implode(",",$art_array));

		
		$nodes_array = array();
		$sum_array = array();
		$article_array = array();
		$child_array = array();
		//on rentre les données dans un tableau associatif
		while($nodes = $dbLink->fetch_array($nodes_req)) {
			//c'est un noeud on remonte ses fils et on groupe par caractvalid
			if($nodes['cs_stockid'] != NULL)
				$nodes_array[$nodes['parentid']][$nodes['caractvalid']][] = $nodes['cs_stockid'];
						
			//on fait la somme des noeuds pour avoir la valeur de stock à leur mettre
			if(!isset($sum_array[$nodes['parentid']][$nodes['caractvalid']]))
				$sum_array[$nodes['parentid']][$nodes['caractvalid']] = $nodes['total'];
			else
				$sum_array[$nodes['parentid']][$nodes['caractvalid']] += $nodes['total'];
			
			$article_array[$nodes['parentid']] = $nodes['artid'];
		}
		
		//on termine en groupant tout les noeuds et en mettant a jour leur fils
		foreach($sum_array as $k => $v) {
			
			$insert_array = array();
			
			//on insere les noeuds groupés
			foreach($v as $ck => $cv) {
				$dbLink->query("INSERT INTO stocks (stockid, caractvalid, parentid, artid, total) VALUES(NULL, '".$ck."', '".$k."', '".$article_array[$k]."', '".$cv."');","logstock",$article_array[$k]);
				//il nous faut les last insertid
				$insert_array[$ck] = $dbLink->insert_id();
			}

			//on redirige les enfant
			if(isset($nodes_array[$k]) && sizeof($nodes_array[$k]))
				foreach($nodes_array[$k] as $cark => $carv) {
					if(isset($carv) && sizeof($carv)) {
						$implode = implode(',', $carv);
						$child_array[] = $implode;
						//on copie les enregistrements et on les efface
						$dbLink->query("INSERT INTO tmp_stocks SELECT * FROM stocks WHERE stockid IN(".$implode.");","logstock",implode(",",$art_array));
						$dbLink->query("DELETE FROM stocks WHERE stockid IN(".$implode.");","logstock",implode(",",$art_array));
						$dbLink->query("UPDATE tmp_stocks SET parentid = '".$insert_array[$cark]."' WHERE stockid IN(".$implode.");","logstock",implode(",",$art_array));
					}
			}
		}

	}

	//Release locks
	$dbLink->query("UNLOCK TABLES;","logstock",implode(",",$art_array));
	
	return;
}

/** Efface des valeurs de caractéristiques de tout les stocks ou d'articles
* $dbLink : Objet Mysql
* $caractval_array : Tableau d'identifiant de valeur de caractéristique
* $art_array : Tableau d'identifiant d'articles
*/
function stock_delete_vals($dbLink, $caractval_array, $art_array = array()) {
			
	do {
		//nodes query by caractvalid...
		if(!isset($child_array))
			$nodes_query = "SELECT cs.stockid as cs_stockid, stocks.parentid, stocks.stockid FROM stocks 
				LEFT JOIN stocks as cs ON(cs.parentid = stocks.stockid)
				WHERE stocks.caractvalid IN(".implode(',',$caractval_array).")".(sizeof($art_array) ? " AND stocks.artid IN (".implode(",",$art_array).")" : '').";";
		else
			$nodes_query = "SELECT cs.stockid as cs_stockid, stocks.parentid, stocks.stockid FROM stocks 
				LEFT JOIN stocks as cs ON(cs.parentid = stocks.stockid)
				WHERE stocks.stockid IN(".implode(',',$child_array).")".(sizeof($art_array) ? " AND stocks.artid IN (".implode(",",$art_array).")" : '').";";	
		
		$nodes_req = $dbLink->query($nodes_query,"logstock",implode(",",$art_array));
		
		//get child 
		$child_array = array();
		$nodes_array = array();
		while($nodes = $dbLink->fetch_array($nodes_req)) {
			if($nodes['cs_stockid'] != NULL)
				$child_array[] = $nodes['cs_stockid'];
			
			$nodes_array[] = $nodes['stockid'];
		}
		
		//Effacer Les noeuds
		if(sizeof($nodes_array)) {
			$dbLink->query("DELETE FROM stocks WHERE stockid IN (".implode(",", $nodes_array).");","logstock",implode(",",$art_array));
			$dbLink->query("DELETE FROM stock_historique WHERE stockid IN(".implode(",", $nodes_array).");","logstock",implode(",",$art_array));
		}
	}
	while(isset($child_array) && sizeof($child_array));
	
	return;
}

/** Decrementer le stock pour un article et ses caractéristique
* $dbLink : Objet Mysql
* $artid : Identifiant de l'article
* $caractval_array : Tableau de valeurs de caractéristiques
* $delta : Valeur à modifier dans le stock
*/
/*
function stock_decrement ($dbLink, $artid, $caractval_array = array(), $delta = '-1') 
	{
	//requete pour remettre les caracteristique dans l'ordre
	if (sizeof($caractval_array)) 
		{
		$caractval_req=$dbLink->query("SELECT caractvalid, caractid FROM caracteristiquevaleur INNER JOIN article_caractval USING(caractvalid) 
		WHERE caractvalid IN (".implode(",",$caractval_array).") AND article_caractval.artid = '$artid' ORDER BY caractid ASC;");
		$caractval_array = array();
		
		while ($caractval=$dbLink->fetch_array($caractval_req)) 
			{
			$nb_caract_req=$dbLink->query_first("SELECT count(*) FROM article_caractval INNER JOIN caracteristiquevaleur USING(caractvalid) WHERE caractid = '$caractval[caractid]';");
			if ($nb_caract_req[0] > 1)			
				$caractval_array[] = $caractval['caractvalid'];
			}
		}
	
	//verifier que la branche de l'arbre existe
	$verify_req=$dbLink->query_first("SELECT stockid FROM stocks WHERE artid = '$artid' AND caractvalid = '0' AND parentid = '0' LIMIT 1;");
		$lastparentid = $verify_req['stockid'];
	//verifier que les sous branches existent
	foreach ($caractval_array as $key => $value) 
		{
		$verify_req=$dbLink->query_first("SELECT stockid FROM stocks WHERE artid = '$artid' AND caractvalid = '$value' AND parentid = '$lastparentid' LIMIT 1;");
			$lastparentid=$verify_req['stockid'];
	}
	
	$to_update = sizeof($caractval_array) ? end($caractval_array) : 0;
	//Mettre à jour la ligne qui nous interesse dans l'arbre
	$dbLink->query("UPDATE stocks SET total = total + ($delta) WHERE stockid = '$lastparentid';");
	$dbLink->query("UPDATE stocks SET total = total + ($delta) WHERE artid = '$artid' and parentid = '0';");

	$totalarticle=$dbLink->query_first("select SUM(total) from stocks where artid = '$artid' and parentid != '0'");
	$dbLink->query("INSERT INTO stock (artid, nombre) VALUES('$artid', '$totalarticle[0]') ON DUPLICATE KEY UPDATE nombre = '$totalarticle[0]';");
}
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

function stock_update($dbLink, $artid, $caractval_array = array(), $delta ) 
	{
	//requete pour remettre les caracteristique dans l'ordre
	if (sizeof($caractval_array)) 
		{
		$caractval_query = "SELECT caractvalid, caractid FROM caracteristiquevaleur INNER JOIN article_caractval USING(caractvalid) WHERE caractvalid IN(".implode(",",$caractval_array).") AND article_caractval.artid = '$artid' ORDER BY caractid ASC;";
		$caractval_req = $dbLink->query($caractval_query,"logstock",$artid);
		$caractval_array = array();
		while ($caractval = $dbLink->fetch_array($caractval_req)) 
			{
			$nb_caract_req = $dbLink->query_first("SELECT count(*) FROM article_caractval INNER JOIN caracteristiquevaleur USING(caractvalid) WHERE caractid = '$caractval[caractid]' AND artid = '$artid';","logstock",$artid);
			if ($nb_caract_req[0] > 1)			
				$caractval_array[] = $caractval['caractvalid'];
			}
		}
	
	//verifier que la branche de l'arbre existe
	$verify_query = "SELECT stockid FROM stocks WHERE artid = '$artid' AND caractvalid = '0' AND parentid = '0' LIMIT 1;";
	$verify_req = $dbLink->query_first($verify_query,"logstock",$artid);
	if ($verify_req) 
		{
		$lastparentid = $verify_req['stockid'];
		//verifier les sous branches existent
		foreach($caractval_array as $key => $value) 
			{
			$verify_query = "SELECT stockid FROM stocks WHERE artid = '$artid' AND caractvalid = '$value' AND parentid = '$lastparentid' LIMIT 1;";	
			$verify_req = $dbLink->query_first($verify_query,"logstock",$artid);
			if (!$verify_req) 
				{
				$dbLink->query("INSERT INTO stocks (caractvalid, artid, parentid) VALUES('$value', '$artid', '$lastparentid');","logstock",$artid);
				$lastparentid = $dbLink->insert_id();
				}
			else 
				{
				$lastparentid = $verify_req['stockid'];
				}
			//$dbLink->query("INSERT INTO stock_historique (stockid, dateline, delta) VALUES('$lastparentid',  UNIX_TIMESTAMP(), '$value');","logstock",$artid);
			}
		}
	else 
		{
		$dbLink->query("INSERT INTO stocks (artid) VALUES('$artid');","logstock",$artid);
		$lastparentid = $dbLink->insert_id();
		}
	
	$to_update = sizeof($caractval_array) ? end($caractval_array) : 0;
	//Mettre à jour la ligne qui nous interesse dans l'arbre
	$avantmodif=$dbLink->query_first("select total from stocks where stockid = '$lastparentid';","logstock",$artid);
	if ($avantmodif[0] == 0)
		$modiftotal = $delta ;
	else
		$modiftotal = $delta - $avantmodif[0] ;
	if (abs($modiftotal) != 0)
		$dbLink->query("INSERT INTO stock_historique (stockid, dateline, delta) VALUES('$lastparentid', UNIX_TIMESTAMP(), '$modiftotal');","logstock",$artid);
	$dbLink->query("UPDATE stocks SET total = '$delta' WHERE stockid = '$lastparentid';","logstock",$artid);
	$lignes=$dbLink->query_first("select count(*) from stocks WHERE artid = '$artid' and parentid != '0';");
	if ($lignes[0] > 1)
		{	
		$mytot=$dbLink->query_first("select sum(total) from stocks WHERE artid = '$artid' and parentid != '0';");
		}
	else
		{
		$mytot=$dbLink->query_first("select total from stocks WHERE artid = '$artid' and parentid = '0';");
		}
	$dbLink->query("UPDATE stock SET nombre = '$mytot[0]' WHERE artid = '$artid';");
	}


//Advanced implode function with formatting options (for SQL compute)
function format_implode($sep, $replace, $array, $callback = "", $param = false) {
	$toreturn = "";
	foreach($array as $value) {
		$value = is_callable($callback) && $param ? call_user_func($callback, $value, $param) : (is_callable($callback) ? call_user_func($callback, $value) : $value);
		$needle = strpos($replace, "%%") === false ? $replace.$value : str_replace("%%", $value, $replace);
		$toreturn .= $toreturn ? (strpos($sep, "%%") === false ? $sep.$needle : str_replace("%%", $needle, $sep)) : $needle;
	}

	return $toreturn;
}

?>
