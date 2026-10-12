<?php

require "admin_global.php";
include "./header.php";
include "./menu.php";
require_once("function_stock.php");
 
$near_value = 1;
$notin_array = array(0);

echo "
<td valign=\"top\">
<table width=\"100%\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\"><tr><td>
<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\">
  <tr>
   <td>
   	<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\">
		<tr>
			<td height=\"25\" nowrap> &nbsp; <img src=\"images/arrow_2.jpg\"> <font class=\"cost\">Gestion des stocks</font>
			</td>
		</tr>
		<tr>
			<td background=\"images2/cadre_r3_c2.gif\" width=\"100%\" height=\"2\">
			</td>
		</tr>
	</table>
   </td>
   <td><img name=\"cadre_r2_c5\" src=\"images2/cadre_r2_c5.gif\" width=\"11\" height=\"27\" border=\"0\" alt=\"\"></td>
   <td background=\"images2/cadre_r2_c6.gif\" width=\"100%\" height=\"27\" align=\"center\" valign=\"middle\">
   </td>
   <td><img name=\"cadre_r2_c8\" src=\"images2/cadre_r2_c8.gif\" width=\"13\" height=\"27\" border=\"0\" alt=\"\"></td>
  </tr>
</table>

</tr></td>
<tr><td>

<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\">
  <tr>
   <td background=\"images2/cadre_r4_c2.gif\" width=\"3\" height=\"100%\"></td>
   <td align=\"center\" valign=\"middle\"><h3>Récapitulatif des stocks</h3>
   <span class=\"recap_alert\" onclick=\"uncollapse(this);\"><strong>Seuil d'alerte dépassé</strong></span> |  <span class=\"recap_near\" onclick=\"uncollapse(this);\"><strong>Seuil d'alerte bientôt atteint</strong></span> | <span onclick=\"uncollapse(this);\"><strong>Article en stock</strong></span><br><br>" ;

if($action=="" or !isset($action)) {

	echo "<form method=\"post\" action=\"recapstocks.php\">
	<input type=\"hidden\" name=\"action\" value=\"modif\">
	<br><br>
	<table id=\"recap_root\" align=\"center\"><tr><td align=\"left\">Catégorie ( articles )<br>Article [ caractéristiques ]</td>
	<td align=\"center\" width=\"10%\"><font class=\"cost\">Stock</font></td>
	<td align=\"center\" width=\"10%\"><font class=\"cost\">Seuil d'alerte</font></td>
	<td align=\"center\" width=\"10%\"><font class=\"cost\">Délai d'appro.</font></td></tr>" ;
	
	//on veu tout les articles qui ne sont pas des articles liés !
	$art_query = $DB_site2->query("SELECT artid, catid, $libelle FROM article WHERE artid NOT IN (SELECT article_lie FROM liens_articles) ORDER BY catid;");
	
	$art_array = array();
	while($art = $DB_site2->fetch_array($art_query)) {
		$art_array[$art['catid']][$art['artid']]['libelle'] = $art[$libelle];
	}
	
	//construire l'arbre de catÃ©gories
	$cat_array = array(0 => array('childs' => array()));
	$parent_array = array(0);
	
	do {
		
		//requete des catégories
		$cat_query = $DB_site2->query("SELECT catid, $libelle, parentid FROM categorie WHERE parentid IN(".implode(',', $parent_array).") AND catid NOT IN(".implode(',', $notin_array).") ORDER BY parentid, $libelle;");
	
		//on reconstruit la parent array pour la prochaine boucle
		$parent_array = array();
			
		while($cat = $DB_site2->fetch_array($cat_query)) {
			
			//on insere un parent
			$parent_array[] = $cat['catid'];
			
			//on insere la categorie
			$cat_array[$cat['catid']]['libelle'] = $cat[$libelle];
			
			if(isset($art_array[$cat['catid']]) && sizeof($art_array[$cat['catid']])) {
				$cat_array[$cat['catid']]['articles'] = &$art_array[$cat['catid']];
			}
			else {
				$cat_array[$cat['catid']]['articles'] = array();
			}
			
			$cat_array[$cat['catid']]['childs'] = array();
			
			$cat_array[$cat['parentid']]['childs'][] = $cat['catid'];
		}
	}
	while (sizeof($parent_array));
	
	//recuperer les stocks et articles
	$art_stquery = $DB_site2->query("SELECT stocks.parentid, stocks.stockid, stocks.total, stocks.artid, stock.seuil, stock.delaiappro, article.catid 
										FROM stocks 
										INNER JOIN stock USING(artid) 
										INNER JOIN article USING(artid)
										LEFT JOIN stocks as cs ON(stocks.stockid = cs.parentid) 
										WHERE cs.stockid <=> NULL AND article.artid NOT IN (SELECT article_lie FROM liens_articles) GROUP BY artid;");
	
	while($art_st = $DB_site2->fetch_array($art_stquery)) {
		$cat_array[$art_st['catid']]['articles'][$art_st['artid']]['total'] = $art_st['total'];
		$cat_array[$art_st['catid']]['articles'][$art_st['artid']]['stockid'] = $art_st['stockid'];
		$cat_array[$art_st['catid']]['articles'][$art_st['artid']]['seuil'] = $art_st['seuil'];
		$cat_array[$art_st['catid']]['articles'][$art_st['artid']]['delaiappro'] = $art_st['delaiappro'];
		$cat_array[$art_st['catid']]['articles'][$art_st['artid']]['caract'] = $art_st['parentid'] > 0;
		
		//on a un article avec des caractéristiques !!
		if($cat_array[$art_st['catid']]['articles'][$art_st['artid']]['caract']) {
			//on recupere tout les stocks
			$cat_array[$art_st['catid']]['articles'][$art_st['artid']]['caractvals'] = stock_retrieve($DB_site2, $art_st['artid'], $libelle);
		}
	}
	
	//j'ai toutes les info je les affiche recursivement
	function disp_recap(&$cat_array, &$point_array, $deep = 0) {
		
		global $near_value;
		
		$toreturn = "";
		
		$childs_exists = sizeof($point_array['childs']);
		$articles_exists = sizeof($point_array['articles']);
		$near_enabled = false;
		$alert_enabled = false;
		
		$art_disp = "";
		
		//si on a des article
		if($articles_exists) {
			foreach($point_array['articles'] as $ka => $va) {
				if(!$va['caract']) {
				
					$st_alert = ($va['seuil'] > $va['total']) || !$va['total'];
					$st_near = !$st_alert && $va['total'] && (($va['seuil']*(1+$near_value)) >= $va['total']);
					
					if($st_near) {
						$near_enabled = true;
					}
					
					if($st_alert) {
						$alert_enabled = true;
					}
					
					$art_disp .=  '<tr name="trart" '.($st_alert ? ' class="recap_alert"' : '').($st_near ? ' class="recap_near"' : '').'><td> - '.$va['libelle'].' <a href="ajout_produit.php?artid='.$ka.'&action2=stocks" target="_blank"><img src="images/voir.gif" align="absmiddle"></a></td><td><input type="text" name="sts_'.$ka.'" value="'.($va['total'] ? $va['total'] : '0').'" size="5"></td><td>'.($va['seuil'] ? $va['seuil'] : '0').'</td><td>'.($va['delaiappro'] ? $va['delaiappro'] : '0').'</td></tr>';
				} else {
					$near_article = false;
					$alert_article = false;
					
					$caract_disp = "";
					foreach($va['caractvals'] as $ck => $cv) {
						$libelle_array = array();
						$caractvalid_array = array();
						foreach($cv as $lk => $lv) {
							if($lk{0}.$lk{1} == 'lb') {
								$libelle_array[] = $lv;
					//			$caractid_array[] = substr($lk,2);
	
							}
	//						echo "$lk : $lv<br>";
						}
						
					//	echo "<pre>".print_r($cv,1)."<pre>";
						
						$st_alert = ($va['seuil'] > $cv['value']) || !$cv['value'];
						$st_near = !$st_alert && $cv['value'] && (($va['seuil']*(1+$near_value)) >= $cv['value']);
						
						if($st_near) {
							$near_article = true;
						}
						
						if($st_alert) {
							$alert_article = true;
						}
							
						$caract_disp .= '<tr name="trcaract" '.($st_alert ? ' class="recap_alert"' : '').($st_near ? ' class="recap_near"' : '').'><td> # <img src="images/fleche.gif" align="absmiddle"> '.implode(', ', $libelle_array).'</td><td><input type="text" name="sta_' . $cv['stockid'] . '" value="'.($cv['value'] ? $cv['value'] : '0').'" size="5"></td><td>'.($va['seuil'] ? $va['seuil'] : '0').'</td><td>'.($va['delaiappro'] ? $va['delaiappro'] : '0').'</td>';					
					}
					
					if($near_article && !$alert_article) {
						$near_enabled = true;
					}
					
					if($alert_article) {
						$alert_enabled = true;
					}
						
					$art_disp .=  '<tr name="trart" onclick="collapseCaract(this);" '.($alert_article ? ' class="recap_alert"' : '').($near_article ? ' class="recap_near"' : '').'><td><img name="colpse" src="images/moins.gif" align="absmiddle"> '.$va['libelle'].' ['.sizeof($va['caractvals']).'] <a href="ajout_produit.php?artid='.$ka.'&action2=stocks" target="_blank"><img src="images/voir.gif" align="absmiddle"></a></td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>';
					$art_disp .= $caract_disp;
				}	
			}
		}
		
		//on affiche le libelle
		$toreturn .= '<tr onclick="collapse(this);" name="trcat" class="recap_cat'.(!$childs_exists && !$articles_exists ? '_e' : '').'"><td><img name="colpse" src="images/moins.gif" align="absmiddle"> ';
		for($i = 0 ; $i < $deep ; $i++) {
			$toreturn .= ' <img src="images/fleche.gif" align="absmiddle"> ';	
		}
		$toreturn .= $point_array['libelle'].' ('.$articles_exists.') '.($near_enabled ? ' <span class="recap_near">!</span> ' : '').($alert_enabled ? ' <span class="recap_alert">!</span> ' : '').' </td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>';
	
		$toreturn .= $art_disp;
	
		//si on a des fils
		if($childs_exists) {
			foreach($point_array['childs'] as $val) {
				$toreturn .= disp_recap($cat_array, $cat_array[$val], $deep+1);
			}
		}
			
		return $toreturn;
	}
	
	
	foreach($cat_array[0]['childs'] as $v) {
		echo disp_recap($cat_array, $cat_array[$v]);
	}
	
	echo "</table>
	<br><input type=\"submit\" value=\"Enregistrer les modifications\">
	</form>" ;


	} 
else 
	{ // action = modif => enregistrement des données ...
	$erreur = "" ;
	function caractval_retrieve($stockid) 
		{
		global $DB_site2,$caractval_array;
		$aajouter=$DB_site2->query_first("SELECT caractvalid,parentid FROM stocks WHERE stockid='$stockid' ");
		$caractval_array[]=$aajouter[caractvalid];
		if($aajouter[parentid]!=0) 
			{
			caractval_retrieve($aajouter[parentid]);
			}
		}
	foreach ($_POST as $key => $val) 
		{
		if ($val < 0)
			{
			$val = 0 ;	
			$articlestock=$DB_site2->query_first("SELECT $libelle FROM stocks s inner join article a using(artid) WHERE stockid='".substr($key,4)."' ");
			if ($articlestock[$libelle])
				$erreur .= "\n<li>$articlestock[$libelle]</li>" ;
			}
		if (substr($key,0,3)=="sts") 
			{ // article sans caracteristique
			if (substr($key,4)!="") 
				{
				stock_update($DB_site2,substr($key,4),array(0),$val);
				}
			}
		if (substr($key,0,3)=="sta") 
			{ // article avec caracteristique
			if (substr($key,4)!="") 
				{
				$caractval_array=array();
				$articlestock=$DB_site2->query_first("SELECT artid FROM stocks WHERE stockid='".substr($key,4)."' ");
				caractval_retrieve(substr($key,4));
				stock_update($DB_site2,$articlestock[artid],$caractval_array,$val);
				}
			}
		}
	if ($erreur != "")
		$erreur = "Le stock saisi pour les articles suivants était négatif, il a été remplacé par 0 : <ul style=\"text-align:left;margin-left:150px;\">$erreur</ul>" ;
	echo "<br><strong>Les stocks ont été mis à jour.</strong><br>
	<br>$erreur";
	}


echo "</td>
   <td background=\"images2/cadre_r4_c10.gif\" width=\"3\" height=\"100%\" border=\"0\" alt=\"\"></td>
  </tr>
</table>
</td>
</tr>
<tr><td>

<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\">
  <tr>
   <td rowspan=\"2\"><img name=\"cadre_r6_c2\" src=\"images2/cadre_r6_c2.gif\" width=\"10\" height=\"26\" border=\"0\" alt=\"\"></td>
   <td align=\"center\" height=\"23\" width=\"100%\"></td>
   <td rowspan=\"2\"><img name=\"cadre_r6_c9\" src=\"images2/cadre_r6_c9.gif\" width=\"8\" height=\"26\" border=\"0\" alt=\"\"></td>
  </tr>
  <tr>
   <td background=\"images2/cadre_r7_c4.gif\" width=\"100%\" height=\"3\"></td>
  </tr>
</table>
</td></tr></table>
</td>
</tr><tr>
</tr></table>
				  </td>
              </tr>
            </table>
		  </td>
        </tr>";
?>
<script type="text/javascript">
	var root = document.getElementById('recap_root');
	collapseAll();
	
	function collapseAll() {
		//reduire toutes les branches
		var els = root.getElementsByTagName('tr');
		
		for(i = 0 ; i < els.length ; i++) {
			if(els[i].getAttribute('name') == 'trcat' || els[i].getAttribute('name') == 'trart') {
				imgs = els[i].getElementsByTagName('img');
				for(j = 0 ; j < imgs.length ; j++) {
					if(imgs[j].getAttribute('name') == 'colpse') {
						imgs[j].setAttribute('src', 'images/plus.gif');
					}	
				}
				
				els[i].setAttribute('disp', 'none');
			}
			
			if(els[i].getAttribute('name') == 'trart' || els[i].getAttribute('name') == 'trcaract') {
				els[i].style.display = 'none';
			}
		}
	}
	
	function collapseCaract(el) {
		dispel = el.getAttribute('disp');
		el.setAttribute('disp', dispel == 'none' ? 'ok' : 'none');
		
		imgs = el.getElementsByTagName('img');
		
		for(j = 0 ; j < imgs.length ; j++) {
			if(imgs[j].getAttribute('name') == 'colpse') {
					imgs[j].setAttribute('src', dispel != 'none' ? 'images/plus.gif' : 'images/moins.gif');
			}	
		}
		
		
		sibling = el.nextSibling;
		//alert(sibling.name +' '+ sibling.nodeName +' '+ sibling.getAttribute('name'));
		while(sibling.getAttribute('name') == 'trcaract') {
			sibling.style.display = (dispel == 'none' ? '' : 'none');
			sibling = sibling.nextSibling;
		}
	}
	
	function collapse(el) {
		dispel = el.getAttribute('disp');
		el.setAttribute('disp', dispel == 'none' ? 'ok' : 'none');
		
		imgs = el.getElementsByTagName('img');
		
		for(j = 0 ; j < imgs.length ; j++) {
			if(imgs[j].getAttribute('name') == 'colpse') {
					imgs[j].setAttribute('src', dispel != 'none' ? 'images/plus.gif' : 'images/moins.gif');
			}	
		}
		
		
		sibling = el.nextSibling;
		//alert(sibling.name +' '+ sibling.nodeName +' '+ sibling.getAttribute('name'));
		while(sibling.getAttribute('name') == 'trart' || sibling.getAttribute('name') == 'trcaract') {
			if(sibling.getAttribute('name') == 'trcaract' && dispel == 'none') {
				sibling = sibling.nextSibling;
				continue;
			}
			
			if(sibling.getAttribute('name') == 'trart' && dispel != 'none') {
				imgs = sibling.getElementsByTagName('img');
				sibling.setAttribute('disp', 'none');
				for(j = 0 ; j < imgs.length ; j++) {
					if(imgs[j].getAttribute('name') == 'colpse') {
						imgs[j].setAttribute('src', 'images/plus.gif');
						
					}	
				}
			}
			
			sibling.style.display = (dispel == 'none' ? '' : 'none');
			sibling = sibling.nextSibling;
			replie = false;
		}
	}
	
	function uncollapse(el) {
		el.setAttribute('disp', el.getAttribute('disp') == 'ok' ? 'none' : 'ok');
		dispel = el.getAttribute('disp');
		tclass = el.className;
		arts = root.getElementsByTagName('tr');
		for(i = 0 ; i < arts.length ; i++) {
			if(arts[i].getAttribute('name') == 'trart' && arts[i].className == tclass) {
				arts[i].style.display = dispel == 'none' ? 'none' : '';
			}
		}		
	}
</script>
<?php
include "./footer.php";
?>
