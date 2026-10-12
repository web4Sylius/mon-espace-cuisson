<?php
require_once($_SERVER['DOCUMENT_ROOT']."/global.php");



$libelle = "libelle".$template->parts["langue"];

$catids          = array();
$marqueids       = array();
$nb              = 0;
$perpage_default = 20;
$perpage         = isset($_GET['perpage']) ? intval($_GET['perpage']) : $perpage_default;

$affiche_liste_filtres = false;

if(isset($_GET['action']) && $_GET['action'] != ""){


	$rechercherapide = strip_tags(trim($_GET['action']));


	if($rechercherapide === "wineart") {
		$rechercherapide = "wine art";
	}

	/*
	if(!preg_match("!!u",$rechercherapide))
		$rechercherapide = utf8_encode($rechercherapide);
	*/
		$rechercherapide = str_replace("  "," ",$rechercherapide);
		

		if(strlen($rechercherapide) > 3){
		//**********************************ANTI INJECTION SQL ********************************* 06/07/2018///
		//PS: ne pas mettre ce code dans la fonction url-rewrite car elle est utilisé pourd'autre chose sur le site
			if(strlen($rechercherapide)>=30)
				$rechercherapide = substr($rechercherapide, 0,29);
			$faille = array(" and "," AND "," or "," OR "," in "," IN ","=","!=",">","<","<=",">=","<>","union","UNION","select","SELECT","like","LIKE");
			foreach ($faille as $lemot) {
				$test = stripos($rechercherapide, $lemot);
				if($test !== false)
					$rechercherapide=substr($rechercherapide, 0,$test);
			}
		//**************************FIN ANTI INJECTION SQL **************************************// 

		//$r=$DB_site->query_first("SELECT * FROM marque WHERE libelle LIKE '$rechercherapide%'");
		//if($r['marqueid']!="") header("location:".url_rewrite($r['type'.$template->langue])."-".url_rewrite($r[libelle])."-".$r[marqueid].".htm");

			$filtermarque     = array();
			$recherchelibelle = $rechercherapide;
		//on sauvegarde le mot recherché dans la table stat_recherches
			if(!isset($_COOKIE['admin_username']) && !in_array($_SERVER['REMOTE_ADDR'], $aAdminIPS) ){
				saveMotCle($recherchelibelle);
			}

		/*
		$aSearch  = array("é","ê","è","ë","â","à","č");
		$aReplace = array("e","e","e","e","a","a","e");
		$recherchemarque  = str_replace($aSearch,$aReplace,$recherchelibelle);
		*/
		$recherchelibelle=url_rewrite($recherchelibelle);
		$recherchelibelle = str_replace("-","%",$recherchelibelle);
		
		
		$sql = "SELECT marqueid, libelle";
		$sql .= " FROM marque";
		$sql .= " WHERE 1";
		$marques = $DB_site->query($sql);

		while($m = $DB_site->fetch_array($marques)){
			$mpos = mb_stripos($recherchelibelle,$m['libelle']);
			if($mpos !== false){
				$end = $mpos + strlen($m['libelle']);
				if($end < strlen($recherchelibelle) && substr($recherchelibelle,$end,1) != " ")
					$end++;
				$recherchelibelle = substr($recherchelibelle,0,$mpos)." ".substr($recherchelibelle,$end);
				$filtermarque[]   = $m['marqueid'];

			}
		}

		$recherchelibelle = trim($recherchelibelle);
		if(preg_match("/\b(\d{2,4})(\s+)?(bouteille|bottle)(s)?/i",$rechercherapide,$m)){
			$capacite          = intval($m[1]);
			$where['capacite'] = " AND capacite_unite='/article/unite/bouteilles' AND capacite_bouteilles BETWEEN ".round($capacite/1.1)." AND ".round($capacite*1.1);
			$recherchelibelle  = str_ireplace($m[0],"",$recherchelibelle);
		}

		$libsrc = array();
		$recherchelibelle = trim($recherchelibelle);
		if(strlen($recherchelibelle) > 2){
			$explode = explode(" ",strtolower($recherchelibelle));
			foreach($explode as $word){
				if(strlen($word) > 0){
					$libsrc[]="HTML_UnEncode(libelle".$template->langue.") LIKE '%".mysqli_real_escape_string($DB_site->link_id,$word)."%'";
				}
			} 

			$where['rapide'] =  ""
			. " AND ("
			. "a.refs_fabricant LIKE '%".mysqli_real_escape_string($DB_site->link_id,$recherchelibelle)."%'  "
			. " OR a.artcode LIKE '%".mysqli_real_escape_string($DB_site->link_id,$recherchelibelle)."%' "
			. " OR a.artcode_eurocave LIKE '%".mysqli_real_escape_string($DB_site->link_id,$recherchelibelle)."%' "
			. " OR a.gamme".$template->langue." LIKE '%".mysqli_real_escape_string($DB_site->link_id,$recherchelibelle)."%' "
			. " OR (".implode(" AND ",$libsrc).")"
			. ")" ; // COLLATE utf8_general_ci
		}
		/*
	    $where['active'] = ""
	    . " AND ("
	    	. "SELECT COUNT(*)"
	    	. " FROM article_site"
	    	. " WHERE 1"
	    	. " AND artid     = a.artid"
	    	. " AND active    = 1"
	    	. " AND site_code = '".$template->site_code()."'"
    	. ") > 0";
		*/
    	$where['pro'] = " AND a.pro IN ('".($sitepro?2:1)."','3')";

    	if(count($filtermarque)>0){

			// $filtermarque = array_map("mysqli_real_escape_string",$DB_site->link_id,$filtermarque);

    		$where['marque'] = ""
    		. " AND a.artid IN ("
    		. "SELECT artid"
    		. " FROM article_marque"
    		. " WHERE marqueid IN (".implode(",",$filtermarque).")"
    		. ")";
    	}

    	$pagetitle       = $rechercherapide." - ".$template->translate("/recherche/meta_title","F");
    	$pagedescription = $rechercherapide." - ".$template->translate("/recherche/meta_description","F");

    	$template->parts["catlibelle"] = $template->translate("/recherche/recherche_pour","F").(strlen($rechercherapide) <= 26 ? "<br/><span class='libelle-recherche'>".$rechercherapide."</span>" : "<br/><span class='libelle-recherche'>".substr($rechercherapide,0,26)."...</span>");
		//$template->translate("/recherche/recherche","F")." \"".(strlen($rechercherapide) <= 18 ? $rechercherapide : substr($rechercherapide,0,18).'...')."\"";
    	if($ordrerecherche=="" || $ordrerecherche==null) $ordrerecherche="ventes";

    	switch($ordrerecherche){
    		case "ASC":
    		$ordre = "ORDER BY prixht ASC";
    		$template->parts["checkedasc"] = "checked";
    		break;

    		case "DESC":
    		$ordre = "ORDER BY prixht DESC";
    		$template->parts["checkeddesc"] = "checked";
    		break;

    		case "btl":
    		$ordre = "ORDER BY CAST(capacite_bouteilles AS SIGNED)";
    		break;

    		case "btlDESC":
    		$ordre = "ORDER BY CAST(capacite_bouteilles AS SIGNED) DESC";
    		break;

    		case "appro":
    		$ordre = "ORDER BY date_appro,prixht";
    		break;

    		case "ventes":
    		$ordre = "ORDER BY ventes DESC";
    		$template->parts["checkedv"] = "checked";
    		break;


    	}


    	$template->parts["sensorderasc"]  = "recherche-rapide.php?action=".$rechercherapide."&amp;ordrerecherche=ASC";
    	$template->parts["sensorderdesc"] = "recherche-rapide.php?action=".$rechercherapide."&amp;ordrerecherche=DESC";
    	$template->parts["sensorderv"]    = "recherche-rapide.php?action=".$rechercherapide."&amp;ordrerecherche=ventes";
		//Récupération du nombre de résultats de la recherche
    	$where="";
    	$nbword=0;
    	// $nbword permet de ne pas effectuer la recherche s'il n'y a que des mots avec 3 lettres, ou moins, en mot clé
    	$words=explode(" ",html_entity_decode($rechercherapide));

    	foreach($words as $word){
    		if(strlen($word)>3 || $word === 'vin'){
    			$where.=" AND (a.libelle".$template->langue." LIKE '%".mysqli_real_escape_string($DB_site->link_id,$word)."%' OR m.libelle LIKE '%".mysqli_real_escape_string($DB_site->link_id,$word)."%' OR a.artcode LIKE '%".mysqli_real_escape_string($DB_site->link_id,$word)."%' OR a.refs_fabricant LIKE '%".mysqli_real_escape_string($DB_site->link_id,$word)."%' OR a.artcode_eurocave LIKE '%".mysqli_real_escape_string($DB_site->link_id,$word)."%' OR a.gamme".$template->langue." LIKE '%".mysqli_real_escape_string($DB_site->link_id,$word)."%' AND a.active = '1')";
    			$nbword++;
    		}
    	}
    	
    	$req=$DB_site->query("SELECT * FROM article a INNER JOIN article_marque am ON am.artid = a.artid INNER JOIN marque m ON m.marqueid = am.marqueid WHERE 1 $where AND a.active = '1' GROUP BY a.artid ORDER BY SUM(a.ventes) DESC");

    	$nb = $DB_site->num_rows($req);

	    //Si aucun résultat, on lance une nouvelle recherche en ajoutant le libellé produit dans les critères
    	if($nb == 0 && $libsrc != array()){
    		$template->parts["nbcategorie"] = 0;
    		$template->parts["nbmarque"] = 0;
    		//$where['rapide'] = " AND (".implode(" OR ",$libsrc).")";

    		//$nb = nbresults(implode("",$where)." ".$dcat);
    	}

	    //Si aucun résultat, on lance une nouvelle recherche sur les produits archivés
	    /*if($nb == 0 && $libsrc != array()){

			$where['rapide'] = ""
			. " AND ("
				. "a.refs_fabricant LIKE '%".mysqli_real_escape_string($DB_site->link_id,$recherchelibelle)."%'"
				. " OR a.artcode LIKE '%".mysqli_real_escape_string($DB_site->link_id,$recherchelibelle)."%'"
				. " OR a.artcode_eurocave LIKE '%".mysqli_real_escape_string($DB_site->link_id,$recherchelibelle)."%'"
				. " OR a.gamme".$template->langue." LIKE '%".mysqli_real_escape_string($DB_site->link_id,$recherchelibelle)."%'"
				. " OR (".implode(" AND ",$libsrc).")"
			. ")" ;

			$where['active'] = ""
			. " AND ("
				. "SELECT COUNT(*)"
				. " FROM article_site"
				. " WHERE 1"
				. " AND artid 	  = a.artid"
				. " AND active 	  = 3"
				. " AND site_code = '".$template->site_code()."'"
			. ") > 0";

			$nb      = nbresults(implode("",$where)." ".$dcat);

			$archive = true;
		}*/

	    //Si il y a des résultats, on les affiche, sinon message "aucun résultat"
		if ($nb > 0 && $nbword != 0) {

	    	//Paramétrage de la pagination
			$start = 0;
			if(isset($_GET['start'])) $start = intval($_GET['start']);
			$template->parts["pagesnav"] = "";
			$nbpages = ceil($nb / $perpage);
			if($nbpages > 1){

				//Reconstruction de l'URL
				$url = '';
				foreach($_GET as $cle => $valeur){
					if($cle != 'page' && $cle != 'start'){
						$url .= ($url == '' ? '?' : '&') . $cle . '=' . $valeur;
					}
				}
				$url_complete = 'recherche-rapide.php' . $url;
				$categorie = new categorie($DB_site,$categorie->catid,$template->langue);
				$url_complete = $categorie->mkurl($_GET,array("actif" => "produits"), false, '/recherche-rapide.php?');
				$url_complete = $url_complete['url'];

				$page_courante = $start / $perpage;
				$page_courante++;
				
			    //Bouton précédent
				if($page_courante > 1){

					$template->parts["urlprecedent"] = $url_complete . '&page=' . ($_GET['page'] - 1) . '&start=' . ($start - $perpage);
					$template->parts["relprev"]      = '';

					$template->evaltemplate("NavigationPrecedent","precedent");
				}else
				$template->evaltemplate("NavigationPrecedentOff","precedent");

			    //Boutons de page
				$nb_prec = 2;
				$nb_suiv = 2;
				$debut_boucle = $page_courante > $nb_prec ? $page_courante - $nb_prec : 1;
				$fin_boucle = $page_courante + $nb_suiv < $nbpages ? $page_courante + $nb_suiv : $nbpages;

				for($i = $debut_boucle; $i <= $fin_boucle; $i++){

					$url_page = $url_complete . '&page=' . $i . '&start=' . ($perpage * ($i - 1));

					$template->parts["pagesnav"] .= "<li><a href= \"".$url_page."\"  class=\"".($start == (($i - 1) * $perpage) ? "selected" : "")."\">".($i)."</a></li>";
				}
				$template->parts["pagesnavmobile"]=$page_courante."/".$nbpages;

			    //Bouton suivant
				if($page_courante < $nbpages){

					$template->parts["urlsuivant"] = $url_complete . '&page=' . ($_GET['page'] + 1) . '&start=' . ($start + $perpage);
					$template->parts["relnext"]    = '';

					$template->evaltemplate("NavigationSuivant","suivant");
				}else
				$template->evaltemplate("NavigationSuivantOff","suivant");
			}else{
				$template->evaltemplate("NavigationSuivantOff","suivant");
				$template->evaltemplate("NavigationPrecedentOff","precedent");
				$template->parts["pagesnavmobile"]="1/1";
			}

			$limite_requete = ' LIMIT ' . $start . ',' . $perpage;

			displayresults("",$ordre,$rechercherapide,$limite_requete);

		} else {


			$template->parts["articles"] = ""
			. "<div class=\"centermsg\">"
			. "<p>".$template->translate("/categorie/pas_resultat")."</p>"
			. "</div><br><span class=\"liseret\" style=\"background-color: #716a64;\"><h2 class=\"color_bg\">".$template->translate("/recherche/resultats_archive","F")."</h2></span>";

			$m=new marque($DB_site,0,$template->langue);
			$marques=$m->listall("libelle",($sitepro?2:1),$template->site_code(),3);
			
			$txt = strip_tags(trim($_GET['action']));
			$txt = str_replace("  "," ",$txt);
			// $txt = url_rewrite($txt);
			// $txt = str_replace("-"," ",$txt);

			$sql = $DB_site->query("SELECT m.libelle, m.marqueid, COUNT(m.libelle) AS qte FROM article a
				INNER JOIN article_marque am USING(artid)
				INNER JOIN marque m USING(marqueid)
				WHERE 1 AND (a.refs_fabricant LIKE '%".mysqli_real_escape_string($DB_site->link_id,$txt)."%' OR a.artcode LIKE '%".mysqli_real_escape_string($DB_site->link_id,$txt)."%' OR a.artcode_eurocave LIKE '%".mysqli_real_escape_string($DB_site->link_id,$txt)."%' OR a.gammeFR LIKE '%".mysqli_real_escape_string($DB_site->link_id,$txt)."%' OR (HTML_UnEncode(libelleFR) LIKE '%".mysqli_real_escape_string($DB_site->link_id,$txt)."%'))
				AND (SELECT COUNT(*) FROM article_site WHERE 1 AND artid=a.artid AND (a.active = '3' OR a.active = '5') AND site_code='base') > 0 AND a.pro IN ('1','3')  GROUP BY libelle");
			while($archive = $DB_site->fetch_array($sql)){
				$ach[$archive['marqueid']] = $archive['libelle'];
				$nach[$archive['marqueid']] = $archive['qte'];
			}
			if($DB_site->num_rows($sql) == 0) 
				$template->parts["archives"] .= "<p>".$template->translate("/categorie/pas_resultat")."</p>";
			else {

				displayresultsarchive("",$rechercherapide);
			}

			$template->evaltemplate("NavigationSuivantOff","suivant");
			$template->evaltemplate("NavigationPrecedentOff","precedent");
			$template->parts["pagesnavmobile"]="1/1";
		}
	}

	if($nbword == 0) {
		$nb = 0;
		$template->parts["nbmarque"] = 0;
		$template->parts["nbcategorie"] = 0;
	}

	$template->parts["articlecount"] = $nb;

	if($nb > 0){

		$template->parts["description"] = sprintf($template->translate("/recherche/resultats_pour","F"),$nb,$rechercherapide);
		$li        = array();
		$catids    = array_unique($catids);
		$marqueids = array_unique($marqueids);

		$categorie_check=false;
		$where="";
		$nbword=0;
		$words=explode(" ",html_entity_decode($rechercherapide));
		foreach($words as $word){
			if(strlen($word)>3 || $word === 'vin'){
				$where .= " AND (c.libelle".$template->langue." LIKE '%".mysqli_real_escape_string($DB_site->link_id,$word)."%' OR a.libelle".$template->langue." LIKE '%".mysqli_real_escape_string($DB_site->link_id,$word)."%' OR m.libelle LIKE '%".mysqli_real_escape_string($DB_site->link_id,$word)."%' OR a.artcode LIKE '%".mysqli_real_escape_string($DB_site->link_id,$word)."%' OR a.refs_fabricant LIKE '%".mysqli_real_escape_string($DB_site->link_id,$word)."%' OR a.artcode_eurocave LIKE '%".mysqli_real_escape_string($DB_site->link_id,$word)."%' OR a.gamme".$template->langue." LIKE '%".mysqli_real_escape_string($DB_site->link_id,$word)."%' AND a.active = '1')";
				$categorie_check=true;
				$nbword++;
			}
		}

		if(!$archive){

			if($categorie_check){
				$r=$DB_site->query("SELECT c.libelle".$template->langue." AS 'libelle',c.catid,c.url".$template->langue." AS 'url' FROM categorie c INNER JOIN article a ON c.catid = a.catid INNER JOIN article_marque am ON am.artid = a.artid INNER JOIN marque m ON m.marqueid = am.marqueid WHERE 1 $where AND a.active = '1' GROUP BY catid ORDER BY SUM(a.ventes) DESC");
				
				$nbcat = 0;
				while($list=$DB_site->fetch_array($r)){
					$categorie = new categorie($DB_site,$list['catid'],$template->langue);
					$categorie->setsubcatids($list['catid']);
					$categorie->setparam("site_code", $template->site_code());
					$categorie->setparam("pro",($sitepro ? 2 : 1));
					$categoriecount = $categorie->articlecount($categorie->subcatids,true);
					$template->parts["catselection"] .= vignetteCat($categorie,"",null,null,'classic');
					$nbcat++;
				}
				$template->parts["nbcategorie"] = $nbcat;
			}


		/*<div class='blocmarque'><h3>".$list['libelle'] . "</h3>
		<div class='logo'>
		<img src='".$marque->image_small." alt='".$list['libelle']."/>
}
}
	</div>
		<div class='col-xs-offset-1 col-xs-10 col-sm-offset-2 col-sm-8 col-md-offset-2 col-md-8 col-lg-offset-2 col-lg-8'>
<a href='".$marque->link."' rel='nofollow' class='savoir'>En savoir plus<i class='fa fa-chevron-right'></i></a>
</div></div></div>";
	}
}
			/*foreach($catids as $catid){
			    
			    $cat = new categorie($DB_site,$catid,$template->langue);
			    $params["catid"] = $catid;
			    if($filtermarque!=array()) $params["marqueids"] = $filtermarque[0];
			    if(!is_null($capacite)){
					
					$trancheid = $cat->tranche_capacite($capacite/1.1);
					if($trancheid != false) $params["capaciteids"] = $trancheid;
			    }
			    $url = $cat->mkurl($params);

			    $li[$cat->libelle] = "<li><a href=\"".$url['url']."\" rel=\"".$url['rel']."\">".$cat->libelle."</a></li>";
			}*/


		} else {

			foreach($marqueids as $marqueid){

				$marque = new marque($DB_site,$marqueid,$template->langue);
				$marque->archive_load();
				$li[$marque->libelle] = "<li><a href=\"".$marque->archive_url."\">".$marque->archive_title."</a></li>";
			}

		}

		ksort($li);

		$template->parts["description"] .= "<ul>".implode("",$li)."</ul>";

		$where="";
		$nbword=0;
		$words=explode(" ",html_entity_decode($rechercherapide));
		foreach($words as $word){
			if(strlen($word)>3 || $word === 'vin'){
				$where .= " AND (m.libelle LIKE '%".mysqli_real_escape_string($DB_site->link_id,$word)."%' OR a.libelle".$template->langue." LIKE '%".mysqli_real_escape_string($DB_site->link_id,$word)."%' OR a.artcode LIKE '%".mysqli_real_escape_string($DB_site->link_id,$word)."%' OR a.refs_fabricant LIKE '%".mysqli_real_escape_string($DB_site->link_id,$word)."%' OR a.artcode_eurocave LIKE '%".mysqli_real_escape_string($DB_site->link_id,$word)."%' OR a.gamme".$template->langue." LIKE '%".mysqli_real_escape_string($DB_site->link_id,$word)."%')";
				$nbword++;
				$marque=true;
			}
		}


		if($marque){
			$r=$DB_site->query("SELECT m.libelle,m.marqueid,m.image,m.type".$template->langue.",m.url".$template->langue." FROM marque m INNER JOIN article_marque am ON m.marqueid = am.marqueid INNER JOIN article a ON a.artid = am.artid WHERE 1 $where AND a.active = '1' AND m.marqueid<>18 GROUP BY m.libelle");
			$nbmarque = 0;
			while($list=$DB_site->fetch_array($r)){
				$marque = new marque($DB_site,$list['marqueid'],$template->langue);
				$template->parts["class2"]="";
				$template->parts["marquelink"]=strtolower($marque->link);
				$template->parts["marquelibelle"]=$marque->libelle;
				$template->parts["imglogo"]="<img src=\"".$marque->logo(150,50)."\" alt=\"".$marque->type."\" title=\"".$marque->libelle."\">";
				$template->evaltemplate("MarqueLink","marqueslist");
				$nbmarque++;
			}
			$template->parts["nbmarque"] = $nbmarque;
		}


		foreach($marqueids as $marqueid){
			$marque = new marque($DB_site,$marqueid,$template->langue);
		    //$template->parts["bpselection"] .= "<a href=\"".$marque->link."\" class=\"bp selection\">".$marque->libelle."</a>";
		}

	} else {

		$template->parts["description"]    = sprintf($template->translate("/recherche/aucun_resultat","F"),$rechercherapide);
		$template->parts["hidefiltration"] = "hidden";
	}
	
	$template->parts["categorie_visuel"] = "<div id=\"categorie_visuel\" class=\"hidden-xs\"><img src=\"/images/templates/recherche_loupe.jpg\" class=\"img-responsive\" alt=\"loupe\"></div>";

} else { //Redirection 301 si aucune recherche lancée
	header("HTTP/1.1 301 Moved Permanently");
	header("location:/");
	header("Connection: close");
}

$template->parts["fil_ariane"] ="> <span property=\"itemListElement\" typeof=\"ListItem\"><span property=\"name\">".$template->translate("/categorie/recherche","F")."</span><meta property=\"position\" content=\"2\"></span> > <span property=\"itemListElement\" typeof=\"ListItem\"><span property=\"name\">".$rechercherapide."</span><meta property=\"position\" content=\"3\"></span>";

//Paramétrage de la liste de choix de l'ordre de tri
$aResTri = array(
	'ventes',
	'ASC',
	'DESC',
);

if($template->langue == 'FR') {
	$template->parts["select_tri"] .= "<option value='' selected>Trier par</option>";
}
else {
	$template->parts["select_tri"] .= "<option value='' selected>Sort by</option>";
}
foreach($aResTri as $type_tri){

	$actif = "&amp;actif=produits";

	$urlTri = "recherche-rapide.php?action=".$rechercherapide."&ordrerecherche=".$type_tri.$actif;
	$template->parts["select_tri"] .= "<option value=\"".$urlTri."\" ".($type_tri == $_GET['ordrerecherche'] ? 'selected' : '').">".$template->translate("/categorie/tri/".$type_tri,"F")."</option>";
	$template->parts["link_tri"].="<a href=\"".$urlTri."\"><i class=\"fa fa".($type_tri == $ordrerecherche ? '-check' : '')."-square-o\"></i>".$template->translate("/categorie/tri/".$type_tri,"F")."</a>";
}
//Paramétrage de la liste de choix du nombre de resultats
$aResParPage = array(10,20,50,100,999);

foreach($aResParPage as $nb_result){
	$categorie = new categorie($DB_site,$categorie->catid,$template->langue);

	$urlParPage = $categorie->mkurl($_GET, array("perpage" => $nb_result == $perpage_default ? null : $nb_result, "start" => 0, "actif" => "produits"), false, '/recherche-rapide.php?');
	$template->parts["par_page"] .= "<option value=\"".$urlParPage['url']."\" ".($nb_result == $perpage ? 'selected' : '').">".($nb_result == 999 ? $template->translate("/categorie/filtres/tous","F") : $nb_result)."</option>";
}
$template->evaltemplate("TriNbResultats","tri_nb_resultats"); 


include("barrefixe.php");

$template->parts["onload"]="scroll_search();";

$template->evaltemplate("Footer1","footer1");

$template->pageparams($host,$pagetitle,$pagedescription);
$template->evaltemplate("RechercheRapide","main");

$template->out();



function nbresults($where){

	global $DB_site;
	global $template;
	$sql  = "SELECT COUNT(*)";
	$sql .= " FROM article a";
	$sql .= " INNER JOIN article_site AS a_s ON(a.artid=a_s.artid)";
	$sql .= " WHERE a_s.active = 1";
	$sql .= " AND a_s.site_code = '".$template->site_code()."'";
	$sql .= $where;
	$nb = $DB_site->query_first($sql);

	return $nb[0];
}

function displayresults($where,$ordre,$rechercherapide,$limite){

	global $DB_site;
	global $templatecache;
	global $libelle;
	global $catids;
	global $marqueids;
	global $template;
	$return  = "";
	$today   = mktime(0,0,0);
	$result  = array();
	$keylist = array();

	$where="";
	$nbword=0;
	$words=explode(" ",html_entity_decode($rechercherapide));

	foreach($words as $word){
		if(strlen($word)>3 || $word === 'vin'){
			$where.=" AND (a.libelle".$template->langue." LIKE '%".mysqli_real_escape_string($DB_site->link_id,$word)."%' OR m.libelle LIKE '%".mysqli_real_escape_string($DB_site->link_id,$word)."%' OR a.artcode LIKE '%".mysqli_real_escape_string($DB_site->link_id,$word)."%' OR a.refs_fabricant LIKE '%".mysqli_real_escape_string($DB_site->link_id,$word)."%' OR a.artcode_eurocave LIKE '%".mysqli_real_escape_string($DB_site->link_id,$word)."%' OR a.gamme".$template->langue." LIKE '%".mysqli_real_escape_string($DB_site->link_id,$word)."%' AND a.active = '1')";
			$nbword++;
		}
	}
	$sql  = "SELECT a.libelle".$template->langue." AS 'libellea',artcode,a.artid,a.prix,commandable,artcode_eurocave,refs_fabricant,m.libelle AS marque FROM article a INNER JOIN article_marque am ON am.artid = a.artid INNER JOIN marque m ON m.marqueid = am.marqueid WHERE 1 $where AND a.active = '1' GROUP BY a.artid ";
	$sql .= $ordre." ";
	$sql .= $limite; 
	$articles = $DB_site->query($sql);

	$cpt    = 0;
	$catids = $marqueids = array();

	while ($article = $DB_site->fetch_array($articles))   {

		if($cpt == 0) $class_sup = 'first';
		if($cpt == 3) $class_sup = 'last';
		
		vignette($article['artid'], 'categorie', $class_sup);
		
		$class_sup = '';
		
		$cpt++;
		
		if($cpt == 4) $cpt = 0;
		
		$catids[]    = $article['catid'];
		$marqueids[] = $article['marqueid'];
	}

}

function displayresultsarchive($where,$rechercherapide){

	global $DB_site;
	global $templatecache;
	global $libelle;
	global $catids;
	global $marqueids;
	global $template;

	$return  = "";
	$today   = mktime(0,0,0);
	$result  = array();
	$keylist = array();

	$where="";
	$nbword=0;
	$words=explode(" ",html_entity_decode($rechercherapide));

	foreach($words as $word){
		if(strlen($word)>3 || $word === 'vin'){
			$where.=" AND (a.libelle".$template->langue." LIKE '%".mysqli_real_escape_string($DB_site->link_id,$word)."%' OR m.libelle LIKE '%".mysqli_real_escape_string($DB_site->link_id,$word)."%' OR a.artcode LIKE '%".mysqli_real_escape_string($DB_site->link_id,$word)."%' OR a.refs_fabricant LIKE '%".mysqli_real_escape_string($DB_site->link_id,$word)."%' OR a.artcode_eurocave LIKE '%".mysqli_real_escape_string($DB_site->link_id,$word)."%' OR a.gamme".$template->langue." LIKE '%".mysqli_real_escape_string($DB_site->link_id,$word)."%' AND (a.active = '3' OR a.active = '5'))";
			$nbword++;
		}
	}
	$sql  = "SELECT a.libelle".$template->langue." AS 'libellea',artcode,a.artid,a.prix,commandable,artcode_eurocave,refs_fabricant,m.libelle AS marque FROM article a INNER JOIN article_marque am ON am.artid = a.artid INNER JOIN marque m ON m.marqueid = am.marqueid WHERE 1 $where AND  (a.active = '3' OR a.active = '5') GROUP BY a.artid ";
	$sql .= $ordre." ";
	$sql .= $limite; 
	$articles = $DB_site->query($sql);

	$cpt    = 0;
	$catids = $marqueids = array();

	while ($article = $DB_site->fetch_array($articles))   {

		if($cpt == 0) $class_sup = 'first';
		if($cpt == 3) $class_sup = 'last';
		
		vignette($article['artid'], 'categorie', $class_sup);
		
		$class_sup = '';
		
		$cpt++;
		
		if($cpt == 4) $cpt = 0;
		
		$catids[]    = $article['catid'];
		$marqueids[] = $article['marqueid'];
	}

}
?>