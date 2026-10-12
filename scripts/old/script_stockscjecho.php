<?php

/*
 	SCRIPT : Vérifie la bonne structure des stocks
*/


include "/home/boutique/www/configurations/demimesure.vendre-en-ligne.fr/config.php";
require_once "mysql.php";
$DB_site=new DB_Sql;
$DB_site->appname='boutique';
$DB_site->appshortname='boutique';
$DB_site->database=$DBname;
$DB_site->server=$db_server;
$DB_site->user=$DBusername;
$DB_site->password=$DBpassword;
$DB_site->connect();
$DBpassword="";


$message = "" ;

$DB_site->query("delete FROM stocks where artid not in (select artid from article)") ;
$DB_site->query("delete FROM stock where artid not in (select artid from article)") ;

 if (in_array("4", $modules)) 
	{// 1438, 1456, 1444, 1445, 1448, 1481, 1474, 1437, 1454, 1450, 1442, 1449, 1485, 1455, 1322, 1323, 
	$articles=$DB_site->query("SELECT * FROM article order by artid") ;
	while ($article=$DB_site->fetch_array($articles))
		{
		$caracts=$DB_site->query("SELECT * FROM article_caractval where artid = '$article[artid]'") ;
		if ($DB_site->num_rows($caracts) == 0) // pas de caractristique
			{
			$stocks=$DB_site->query("SELECT * FROM stocks where artid = '$article[artid]'") ;
			if ($DB_site->num_rows($stocks) > 1)
				{
				$message .= "Article n° $article[artid] n'a pas de caracteristiques et il existe plus d'une ligne dans STOCKS.<br>" ;
				/*$mytot=$DB_site->query_first("select sum(total) from stocks where artid = '$article[artid]' and parentid != '0'") ;
				$DB_site->query("update stocks set total = '$mytot[0]' where artid = '$article[artid]' and parentid = '0'") ;
				$DB_site->query("delete FROM stocks where artid = '$article[artid]' and caractvalid != '0' and parentid != '0'") ;
				$DB_site->query("update stock set nombre = (select total from stocks where artid = '$article[artid]' and parentid != '0') where artid = '$article[artid]'") ;
				*/}
			elseif ($DB_site->num_rows($stocks) == 0)
				{
				$message .= "Article n° $article[artid] n'a pas de ligne dans STOCKS.<br>" ;
				/*$DB_site->query("insert into stocks (artid) values('$article[artid]')") ;
				$DB_site->query("update stock set nombre = 0 where artid = '$article[artid]'") ;*/
				}
			else
				{
				$stock1=$DB_site->query_first("SELECT * FROM stocks where artid = '$article[artid]'") ;
				$stock2=$DB_site->query_first("SELECT * FROM stock where artid = '$article[artid]'") ;
				if ($stock1[total] != $stock2[nombre])
					{
					$message .= "Article n° $article[artid] le stock des deux tables est différent.<br>" ;
					//$DB_site->query("update stock set nombre = (select total from stocks where artid = '$article[artid]' and parentid = '0') where artid = '$article[artid]'") ;
					}
				}
			}
		else
			{
			$plusqunevaleur = 0 ;
			$valeursparcaract=$DB_site->query("SELECT caractid, count(ac.caractvalid) nb FROM article_caractval ac INNER JOIN caracteristiquevaleur cv 
			USING (caractvalid) WHERE artid = '$article[artid]' GROUP BY artid, caractid");
			while ($valeurparcaract=$DB_site->fetch_array($valeursparcaract))
				{
				if ($valeurparcaract[nb] > 1)
					$plusqunevaleur = 1 ;
				}
			$lignesstocks=$DB_site->query_first("SELECT count(*) nb FROM stocks WHERE artid = '$article[artid]'");
			if ($plusqunevaleur == 0 and $lignesstocks[nb] > 1) // caracteristiques à une seule valeur
				{
				$message .= "Article n° $article[artid] n'a que des caracteristiques avec une seule valeur et il y a plusieurs lignes dans STOCKS.<br>" ;
				/*$mytot=$DB_site->query_first("select sum(total) from stocks where artid = '$article[artid]' and parentid != '0'") ;
				$DB_site->query("update stocks set total = '$mytot[0]' where artid = '$article[artid]' and parentid = '0'") ;
				$DB_site->query("delete FROM stocks where artid = '$article[artid]' and caractvalid != '0' and parentid != '0'") ;
				$DB_site->query("update stock set nombre = (select total from stocks where artid = '$article[artid]' and parentid = '0') where artid = '$article[artid]'") ;
				*/}
			if ($plusqunevaleur == 1 and $lignesstocks[nb] <= 1)
				{
				$message .= "Article n° $article[artid] a des caracteristiques avec plusieurs valeurs et il n'y a qu'une (ou 0) ligne dans STOCKS.<br>" ;
				/*$artid = $article[artid] ;
				$catid = $article[catid] ;
				require_once("../admin/function_stock.php");
				$stock_array = stock_retrieve($DB_site, $artid, libelleFR);
				$caract_array = current($stock_array);
				$ligne=$DB_site->query_first("SELECT stockid FROM stocks WHERE artid = '$article[artid]'");
				if ($ligne[stockid] != "")
					$last_parent = $ligne[stockid];
				else
					$last_parent = '0';
				foreach($stock_array as $skey => $svalue) 
					{
					$val = substr($skey, 1) ;
					if (!isset($svalue['stockid']))
						{
						//echo "INSERT INTO stocks(caractvalid, parentid, artid) (SELECT caracteristiquevaleur.caractvalid, '$last_parent' as parentid, article.artid FROM article_caractval INNER JOIN caracteristiquevaleur USING(caractvalid) INNER JOIN caracteristique USING(caractid) INNER JOIN article USING(artid) WHERE article_caractval.artid = '$artid' AND article_caractval.caractvalid = '$val') ON DUPLICATE KEY UPDATE total = '0';<br>" ;
						$insert_update=$DB_site->query("INSERT INTO stocks(caractvalid, parentid, artid) (SELECT caracteristiquevaleur.caractvalid, '$last_parent' as parentid, article.artid FROM article_caractval INNER JOIN caracteristiquevaleur USING(caractvalid) INNER JOIN caracteristique USING(caractid) INNER JOIN article USING(artid) WHERE article_caractval.artid = '$artid' AND article_caractval.caractvalid = '$val') ON DUPLICATE KEY UPDATE total = '0';");
						if ($DB_site->affected_rows() == 2) 
							{
							$query_id=$DB_site->query_first("SELECT stockid FROM stocks WHERE caractvalid = '$val' AND artid = '$artid' AND parentid = '$last_parent';");
							$last_parent = $query_id['stockid'];
							}
						else 
							$last_parent=$DB_site->insert_id();
						echo $last_parent ;
						}
					}
				//echo "INSERT INTO stocks (caractvalid, parentid, artid) (SELECT '0' as caractvalid, '0' as parentid, article.artid FROM article WHERE article.artid = '$artid') ON DUPLICATE KEY UPDATE total = '0';<br>" ;
				$insert_update=$DB_site->query("INSERT INTO stocks (caractvalid, parentid, artid) (SELECT '0' as caractvalid, '0' as parentid, article.artid FROM article WHERE article.artid = '$artid') ON DUPLICATE KEY UPDATE total = '0';");
				if ($DB_site->affected_rows() == 2) 
					{
					$query_id=$DB_site->query_first("SELECT stockid FROM stocks WHERE caractvalid = '$val' AND artid = '$artid' AND parentid = '$last_parent';");
					$last_parent = $query_id['stockid'];
					}
				else 
					$last_parent=$DB_site->insert_id();
				$insert_update=$DB_site->query("INSERT INTO stock (artid) VALUES('$artid') ON DUPLICATE KEY UPDATE nombre = '0';");
				//echo "INSERT IGNORE INTO stock (artid, seuil, delaiappro) VALUES('$artid', '0', '0');<br><br>" ;
				*/}
			if ($plusqunevaleur == 1) // à améliorer
				{
				$totalsomme=$DB_site->query_first("SELECT sum(total) FROM stocks WHERE artid = '$article[artid]' and parentid != '0'");
				$totalsaisi=$DB_site->query_first("SELECT total FROM stocks WHERE artid = '$article[artid]' and parentid = '0'");
				if ($totalsaisi[0] != $totalsomme[0]) {
					$message .= "Article n° $article[artid] la somme des stocks par caracteristiques est de $totalsomme[0] alors que le total enregistré est de $totalsaisi[0].<br>" ;
					//$totalsomme=$DB_site->query("update stocks set total = '$totalsomme[0]' WHERE artid = '$article[artid]' and parentid = '0'");
					}
				
				echo "<br>$article[artid]<br>" ;
				$caracts=$DB_site->query("SELECT distinct(c.caractid), caractid, libelleFR FROM caracteristique c inner join caracteristiquevaleur cv using(caractid) 
				inner join article_caractval ac using(caractvalid) WHERE ac.artid = '$article[artid]'");
				while($caract=$DB_site->fetch_array($caracts)) {
					echo "$caract[libelleFR] : " ;
					$caractvals=$DB_site->query("SELECT cv.caractvalid, libelleFR FROM caracteristiquevaleur cv inner join article_caractval ac using(caractvalid) 
					WHERE ac.artid = '$article[artid]' and cv.caractid = '$caract[caractid]'");
					while($caractval=$DB_site->fetch_array($caractvals)) {
						echo "$caractval[libelleFR], " ;
						}
					echo "<br>" ;
					}
				if ($DB_site->num_rows($caracts) == 1) {
					$nb=$DB_site->num_rows($caractvals) ;
					$nb1=$nb + 1 ;
					$lignes=$DB_site->query_first("SELECT count(*) FROM stocks WHERE artid = '$article[artid]'");
					if ($lignes[0] != $nb1)
						echo "Il devrait y avoir $nb lignes + 1 ligne à 0, soit $nb1.<br>
						Il y en a $lignes[0] !!!<br><br>" ;
					}
				else {
					$pbarticles .= "$article[artid] - " ;
					}
					
				/*
				$lignes=$DB_site->query("SELECT * FROM stocks WHERE artid = '$article[artid]'");
				while($ligne=$DB_site->fetch_array($lignes)) {
					print_r($ligne);
					echo "<br><br>" ;
					}*/
				}
			}
		/*$DB_site->query("update stock set nombre = (select total from stocks where artid = '$article[artid]' and parentid = '0') where artid = '$article[artid]'") ;
		//mettre a jour les stocks
		require_once("../admin/function_stock.php");
		$artid = $article[artid] ;
		$libelle = "libelleFR" ;
		// après : on vérifie la bonne structure dans stocks
		$lignesstocks=$DB_site->query_first("SELECT count(*) FROM stocks WHERE artid = '$artid'");
		if ($lignesstocks[0] == 0) // Aucune ligne n'existe
			{
			$caracts=$DB_site->query("SELECT * FROM article_caractval where artid = '$artid'") ;
			if ($DB_site->num_rows($caracts) == 0) // Il n'y a pas de caracteristiques : on crée une seule ligne à 0 et la ligne dans stock si elle n'existe pas" ;
				{
				$DB_site->query("insert ignore into stocks (artid) values ('$artid')") ;
				$DB_site->query("insert into stock (artid) values ('$artid') ON DUPLICATE KEY UPDATE nombre = '0'") ;
				}
			else // Il y a des caracteristiques : on vérifie s'il existe des combinaisons ou non" ;
				{
				$plusqunevaleur = 0 ;
				$valeursparcaract=$DB_site->query("SELECT caractid, count(ac.caractvalid) nb FROM article_caractval ac INNER JOIN 
				caracteristiquevaleur cv USING (caractvalid) WHERE artid = '$artid' GROUP BY artid, caractid");
				while ($valeurparcaract=$DB_site->fetch_array($valeursparcaract))
					{
					if ($valeurparcaract[nb] > 1)
						$plusqunevaleur = 1 ;
					}
				if ($plusqunevaleur == 0) // Il n'y a que des caracteristiques à 1 seule valeur donc pas de combinaisons
					{
					$DB_site->query("insert ignore into stocks (artid) values ('$artid')") ;
					$DB_site->query("insert into stock (artid) values ('$artid') ON DUPLICATE KEY UPDATE nombre = '0'") ;
					}
				else // créer les combinaisons dans stocks
					{
					$article=$DB_site->query_first("select * from article where artid = '$artid'") ;
					$catid = $article[catid] ;
					// on crée le tableau stocknew comme pour l'onglet stock et ensuite on lit ce tableau pour créer les lignes dans stocks
					$stock_array = stock_retrieve($DB_site, $artid, $libelle) ;
					foreach ($stock_array as $skey => $svalue) 
						{
						$stocknew[$skey] = 0 ;
						}
					if (is_array($stocknew) and count($stocknew))
						{
						foreach ($stocknew as $key => $value) 
							{
							//exploser la clé
							$key_array = explode("_", $key);
							
							$last_parent = '0';
							foreach ($key_array as $val) 
								{
								if ($val > 0)
									$insert_update=$DB_site->query("INSERT INTO stocks(caractvalid, parentid, artid) (SELECT caracteristiquevaleur.caractvalid, '$last_parent' as parentid, article.artid FROM article_caractval INNER JOIN caracteristiquevaleur USING(caractvalid) INNER JOIN caracteristique USING(caractid) INNER JOIN article USING(artid) WHERE article_caractval.artid = '$artid' AND article_caractval.caractvalid = '$val') ON DUPLICATE KEY UPDATE total = '0';","logstock",$artid);
								else
									$insert_update=$DB_site->query("INSERT INTO stocks(caractvalid, parentid, artid) (SELECT '0' as caractvalid, '0' as parentid, article.artid FROM article WHERE article.artid = '$artid') ON DUPLICATE KEY UPDATE total = '0';","logstock",$artid);
								//on cherche notre id parent
								if ($DB_site->affected_rows() == 2) 
									{
									$query_id=$DB_site->query_first("SELECT stockid FROM stocks WHERE caractvalid = '$val' AND artid = '$artid' AND parentid = '$last_parent';","logstock",$artid);
									$last_parent = $query_id['stockid'];
									}
								else 
									{
									$last_parent=$DB_site->insert_id();
									}
								}
						
							//on a fini de construire l'arbre inserons la valeur
							$DB_site->query("UPDATE stocks SET stocks.total = stocks.total + '$value' WHERE stocks.stockid = '$last_parent';","logstock",$artid);
							if ($value != "" && $value != 0)
								$DB_site->query("INSERT INTO stock_historique(stockid, dateline, delta) VALUES('$last_parent', UNIX_TIMESTAMP(), '$value');","logstock",$artid);
							}
						}
					//$DB_site->query("insert into stock (artid) values ('$artid') ON DUPLICATE KEY UPDATE nombre = '0'") ;
					$lignes=$DB_site->query_first("select count(*) from stocks WHERE artid = '$artid' and parentid != '0';");
					if ($lignes[0] > 0)
						{	
						$totalstock=$DB_site->query_first("select sum(total) from stocks where artid = '$artid' and parentid != '0'","logstock",$artid) ;
						$DB_site->query("update stocks set total = '$totalstock[0]' where artid = '$artid' and parentid = '0'","logstock",$artid) ;
						$DB_site->query("insert into stock (artid, nombre) values ('$artid', '$totalstock[0]') ON DUPLICATE KEY UPDATE nombre = '$totalstock[0]'","logstock",$artid) ;
						}
					else
						{
						$totalstock=$DB_site->query_first("select total from stocks where artid = '$artid' and parentid = '0'","logstock",$artid) ;
						$DB_site->query("insert into stock (artid, nombre) values ('$artid', '$totalstock[0]') ON DUPLICATE KEY UPDATE nombre = '$totalstock[0]'","logstock",$artid) ;
						}
					}
				}
			}
		else // Il existe des lignes : il faut vérifier qu'elles soient correctes
			{
			$caracts=$DB_site->query("SELECT * FROM article_caractval where artid = '$artid'") ;
			if ($DB_site->num_rows($caracts) == 0) // pas de caracteristiques : on vérifie qu'il n'y ait qu'une seule ligne et que la ligne dans stock est égale ;
				{
				$DB_site->query("delete from stocks where artid = '$artid' and parentid != '0'") ;
				$lignesstocks0=$DB_site->query_first("select count(*) from stocks where artid = '$artid' and parentid = '0'") ;
				if ($lignesstocks0[0] == 0) // pas de ligne avec parentid à 0 : on la crée
					{
					$DB_site->query("insert into stocks (artid) values ('$artid')") ;
					$DB_site->query("insert into stock (artid, nombre) values ('$artid', '0') ON DUPLICATE KEY UPDATE nombre = '0'") ;
					$modification = 1 ;
					}
				elseif ($lignesstocks0[0] > 1) // plus d'une ligne avec parentid à 0 : on les supprime et on en recrée une
					{
					$DB_site->query("delete from stocks where artid = '$artid' and parentid = '0'") ;
					$DB_site->query("insert into stocks (artid) values ('$artid')") ;
					$DB_site->query("insert into stock (artid, nombre) values ('$artid', '0') ON DUPLICATE KEY UPDATE nombre = '0'") ;
					$modification = 1 ;
					}
				else // une ligne avec parentid à 0
					{
					$contenuligne=$DB_site->query_first("select stockid, total from stocks where artid = '$artid' and parentid = '0'") ;
					if ($contenuligne[total] <= 0)
						{
						$DB_site->query("update stocks set total = '0' where stockid = '$contenuligne[stockid]'") ;
						$modification = 1 ;
						}
					}
				if ($lignesstocks[0] > 1) // plus d'une ligne : il faut supprimer les autres lignes
					{
					$DB_site->query("delete from stocks where artid = '$artid' and parentid != '0'") ;
					}
				$totalamettre=$DB_site->query_first("select total from stocks where artid = '$artid' and parentid = '0'") ;
				$DB_site->query("insert into stock (artid, nombre) values ('$artid', '$totalamettre[total]') ON DUPLICATE KEY UPDATE nombre = '$totalamettre[total]'") ;
				}
			else // Il y a des caracteristiques : on vérifie s'il existe des combinaisons ou non
				{
				$i = 0 ;
				$plusqunevaleur = 0 ;
				$valeursparcaract=$DB_site->query("SELECT caractid, count(ac.caractvalid) nb FROM article_caractval ac INNER JOIN 
				caracteristiquevaleur cv USING (caractvalid) WHERE artid = '$artid' GROUP BY artid, caractid");
				while ($valeurparcaract=$DB_site->fetch_array($valeursparcaract))
					{
					if ($valeurparcaract[nb] > 1)
						{
						$plusqunevaleur = 1 ;
						$i++ ;
						}
					}
				if ($plusqunevaleur == 0) // Il n'y a que des caracteristiques à 1 seule valeur donc c'est comme s'il n'y en avait pas
					{
					$DB_site->query("delete from stocks where artid = '$artid' and parentid != '0'") ;
					$lignesstocks0=$DB_site->query_first("select count(*) from stocks where artid = '$artid' and parentid = '0'") ;
					if ($lignesstocks0[0] == 0) // pas de ligne avec parentid à 0 : on la crée
						{
						$DB_site->query("insert into stocks (artid) values ('$artid')") ;
						$DB_site->query("insert into stock (artid, nombre) values ('$artid', '0') ON DUPLICATE KEY UPDATE nombre = '0'") ;
						$modification = 1 ;
						}
					elseif ($lignesstocks0[0] > 1) // plus d'une ligne avec parentid à 0 : on les supprime et on en recrée une
						{
						$DB_site->query("delete from stocks where artid = '$artid' and parentid = '0'") ;
						$DB_site->query("insert into stocks (artid) values ('$artid')") ;
						$DB_site->query("insert into stock (artid, nombre) values ('$artid', '0') ON DUPLICATE KEY UPDATE nombre = '0'") ;
						$modification = 1 ;
						}
					else // une ligne avec parentid à 0
						{
						$contenuligne=$DB_site->query_first("select stockid, total from stocks where artid = '$artid' and parentid = '0'") ;
						if ($contenuligne[total] <= 0)
							{
							$DB_site->query("update stocks set total = '0' where stockid = '$contenuligne[stockid]'") ;
							$modification = 1 ;
							}
						}
					if ($lignesstocks[0] > 1) // plus d'une ligne : il faut supprimer les autres lignes
						{
						$DB_site->query("delete from stocks where artid = '$artid' and parentid != '0'") ;
						}
					$totalamettre=$DB_site->query_first("select total from stocks where artid = '$artid' and parentid = '0'") ;
					$DB_site->query("insert into stock (artid, nombre) values ('$artid', '$totalamettre[total]') ON DUPLICATE KEY UPDATE nombre = '$totalamettre[total]'") ;
					}
				else // vérifier les combinaisons dans stocks, créer celles qui manquent, supprimer celles en trop
					{
					$article=$DB_site->query_first("select * from article where artid = '$artid'") ;
					$catid = $article[catid] ;
					// on crée le tableau stocknew comme pour l'onglet stock et ensuite on lit ce tableau pour créer les lignes dans stocks
					$stocknew = array();
					$stock_array = stock_retrieve($DB_site, $artid, $libelle) ;
					foreach ($stock_array as $skey => $svalue) 
						{
						$stocknew[$skey] = $stock_array[$skey]['value'] ;
						}
					$listeparents = "0" ;
					if (is_array($stocknew) and count($stocknew))
						{
						$stockidparent=$DB_site->query_first("select stockid from stocks where parentid = '0' and artid = '$artid'","logstock",$artid);
						foreach ($stocknew as $key => $value) 
							{
							//exploser la clé
							$key_array = explode("_", $key);
							$last_parent = '0';
							foreach ($key_array as $val) 
								{
								if ($val > 0)
									{
									$valsparcaract=$DB_site->query_first("SELECT count(ac.caractvalid) nb FROM article_caractval ac INNER JOIN 
									caracteristiquevaleur cv USING (caractvalid) WHERE ac.artid = '$artid' and cv.caractid = (select caractid from caracteristiquevaleur where caractvalid = '$val')");
									if ($valsparcaract[nb] > 1) // si on a bien plusieurs valeurs, c'est correct
										{
										if (count($key_array) > 1) // la clé est multiple
											{
											if ($last_parent == $stockidparent[stockid] and $i > 1)
												{ 
												$insert_update=$DB_site->query("INSERT INTO stocks(caractvalid, parentid, artid) (SELECT caracteristiquevaleur.caractvalid, '$last_parent' as parentid, article.artid FROM article_caractval INNER JOIN caracteristiquevaleur USING(caractvalid) INNER JOIN caracteristique USING(caractid) INNER JOIN article USING(artid) WHERE article_caractval.artid = '$artid' AND article_caractval.caractvalid = '$val') ON DUPLICATE KEY UPDATE total = '0';","logstock",$artid);
												}
											else
												$insert_update=$DB_site->query("INSERT INTO stocks(caractvalid, parentid, artid) (SELECT caracteristiquevaleur.caractvalid, '$last_parent' as parentid, article.artid FROM article_caractval INNER JOIN caracteristiquevaleur USING(caractvalid) INNER JOIN caracteristique USING(caractid) INNER JOIN article USING(artid) WHERE article_caractval.artid = '$artid' AND article_caractval.caractvalid = '$val') ON DUPLICATE KEY UPDATE total = '$value';","logstock",$artid);
											}
										else // la clé n'est pas multiple : on laisse la valeur
											$insert_update=$DB_site->query("INSERT INTO stocks(caractvalid, parentid, artid) (SELECT caracteristiquevaleur.caractvalid, '$last_parent' as parentid, article.artid FROM article_caractval INNER JOIN caracteristiquevaleur USING(caractvalid) INNER JOIN caracteristique USING(caractid) INNER JOIN article USING(artid) WHERE article_caractval.artid = '$artid' AND article_caractval.caractvalid = '$val') ON DUPLICATE KEY UPDATE total = '$value';","logstock",$artid);
										}
									}
								else
									$insert_update=$DB_site->query("INSERT INTO stocks(caractvalid, parentid, artid) (SELECT '0' as caractvalid, '0' as parentid, article.artid FROM article WHERE article.artid = '$artid') ON DUPLICATE KEY UPDATE total = '0';","logstock",$artid);
								//on cherche notre id parent
								if ($DB_site->affected_rows() == 2) 
									{
									$query_id=$DB_site->query_first("SELECT stockid FROM stocks WHERE caractvalid = '$val' AND artid = '$artid' AND parentid = '$last_parent';","logstock",$artid);
									$last_parent = $query_id['stockid'];
									}
								else 
									{
									$modification = 1 ;
									$last_parent=$DB_site->insert_id();
									}
								$listeparents .= ", $last_parent" ;
								}
							if ($value != "" && $value != 0)
								$DB_site->query("INSERT INTO stock_historique(stockid, dateline, delta) VALUES('$last_parent', UNIX_TIMESTAMP(), '$value');","logstock",$artid);
							}
						$DB_site->query("delete from stocks where artid = '$artid' and stockid NOT IN ($listeparents)") ;
						}
					// on remet à jour les totaux
					$lignes=$DB_site->query_first("select count(*) from stocks WHERE artid = '$artid' and parentid != '0';");
					if ($lignes[0] > 1)
						{
						$totalstock=$DB_site->query_first("select sum(total) from stocks where artid = '$artid' and parentid != '0'","logstock",$artid) ;
						}
					else
						{
						$totalstock=$DB_site->query_first("select total from stocks where artid = '$artid' and parentid = '0'","logstock",$artid) ;
						}
					$DB_site->query("update stocks set total = '$totalstock[0]' where artid = '$artid' and parentid = '0'","logstock",$artid) ;
					$DB_site->query("insert into stock (artid, nombre) values ('$artid', '$totalstock[0]') ON DUPLICATE KEY UPDATE nombre = '$totalstock[0]'","logstock",$artid) ;
					}
				}
			}
		if ($modification == 1) // si on a effectué des modifications on redirige vers l'onglet stocks
			{
			$DB_site->query("DELETE FROM stock_historique WHERE stockid IN (SELECT stockid FROM stocks WHERE artid = '$artid');","logstock",$artid);
			$quitter = 0 ;
			$action2 = "stocks" ;
			}*/
		}
	}

echo "$pbarticles<br><br>" ;
echo $message ;

//require_once "/home/hairprod/www/fonctions.php";
//envoi("c.jullien@arobases.fr", "c.jullien@arobases.fr", "Hair Prod : stocks", $message) ;
?>