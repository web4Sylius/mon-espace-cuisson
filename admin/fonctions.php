<?php
require_once __DIR__.'/../secrets.php';

function envoyer_fichier($filename) {

	global $host;
	
	$oCurl = curl_init();
	$sSerial = 'a9d46d210a00033df3dac09e5ae21868';
	$sHttpLogin = PIXMANIA_HTTP_LOGIN;
	$sHttpPwd = PIXMANIA_HTTP_PASSWORD;
	
	$sCalledurl = 'https://pixplace-ws.pixmania.com/index.php?d=webServices_Server&c=ServerRest';
	$aParameters = array ('rm' => 'importFileContr', 'rf' => 'importProduct', 'sl'	=> $sSerial , 'FILENAME' => "@http://$host/admin/".$filename);
	curl_setopt($oCurl, CURLOPT_URL, $sCalledurl);
	curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($oCurl, CURLOPT_USERPWD, $sHttpLogin.':'.$sHttpPwd);
	curl_setopt($oCurl, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
	curl_setopt($oCurl, CURLOPT_UNRESTRICTED_AUTH, true);
	curl_setopt($oCurl, CURLOPT_SSL_VERIFYHOST, 0);
	curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($oCurl, CURLOPT_POST, 1);
	curl_setopt($oCurl, CURLOPT_HTTPHEADER, array("Expect:"));
	curl_setopt($oCurl, CURLOPT_POSTFIELDS, $aParameters);
	$sCurlResponse = curl_exec($oCurl);
	if ($sCurlResponse)
		return "Erreur lors de la transmission du fichier $filename ($sCurlResponse)" ;
	//exit();
	}

function recuperer_fichier() {

	global $host;
	
	$oCurl = curl_init();
	$sSerial = 'a9d46d210a00033df3dac09e5ae21868';
	$sHttpLogin = PIXMANIA_HTTP_LOGIN;
	$sHttpPwd = PIXMANIA_HTTP_PASSWORD;

	//https://pixplace-ws.pixmania.com/index.php?d=webServices_Server&c=ServerRest&sl=a9d46d210a00033df3dac09e5ae21868&rm=exportFile&rf=exportLastReportFile&FILE_TYPE_CODE=PRD
	$sCalledurl = 'https://pixplace-ws.pixmania.com/index.php?d=webServices_Server&c=ServerRest';
	$aParameters = array ('rm' => 'exportFile', 'rf' => 'exportLastReportFile', 'sl'	=> $sSerial , 'FILE_TYPE_CODE' => "PRD");
	curl_setopt($oCurl, CURLOPT_URL, $sCalledurl);
	curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($oCurl, CURLOPT_USERPWD, $sHttpLogin.':'.$sHttpPwd);
	curl_setopt($oCurl, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
	curl_setopt($oCurl, CURLOPT_UNRESTRICTED_AUTH, true);
	curl_setopt($oCurl, CURLOPT_SSL_VERIFYHOST, 0);
	curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($oCurl, CURLOPT_POST, 1);
	curl_setopt($oCurl, CURLOPT_HTTPHEADER, array("Expect:"));
	curl_setopt($oCurl, CURLOPT_POSTFIELDS, $aParameters);
	$sCurlResponse = curl_exec($oCurl);
	echo "$sCurlResponse" ;
	/*if ($sCurlResponse)
		return "Erreur lors de la transmission du fichier $filename ($sCurlResponse)" ;*/
	exit();
	}


function fichier($nom_fic, $imagename) {
	if ($OpenLocal = fopen ($_FILES[$imagename]['tmp_name'], "rb")) 
		$LocalFileContent = fread ($OpenLocal, $_FILES[$imagename]['size']) ;
	else 
		{
		fclose ($OpenLocal) ;
		die ("<b>Erreur :</b> Impossible d'ouvrir le fichier local '" . $_POST[$imagename] . ".") ;
		}
	fclose ($OpenLocal) ;
	if ($OpenRemote = fopen ($nom_fic, "wb")) 
		{
		if (fwrite ($OpenRemote, $LocalFileContent)) 
			fclose ($OpenRemote) ;
		} 
	else 
		{
		fclose ($OpenRemote) ;
		die ("<b>Erreur :</b> Impossible d'ouvrir le fichier distant $nom_fic") ;
		}
	chmod ("$nom_fic", 0777) ;
	}


function url_rewrite2($string) {

	$string=html_entity_decode($string , ENT_NOQUOTES , "ISO-8859-1" );

	$caracteres = array("¥" => "Y", "µ" => "u", "À" => "A", "Á" => "A", "Â" => "A", "Ã" => "A", "Ä" => "A", "Å" => "A",
	"Æ" => "A", "Ç" => "C", "È" => "E", "É" => "E", "Ê"=> "E", "Ë" => "E", "Ì" => "I", "Í" => "I",
	"Î" => "I", "Ï" => "I", "Ð" => "D", "Ñ" => "N", "Ò" => "O", "Ó" => "O", "Ô" => "O", "Õ" => "O",
	"Ö" => "O", "Ø" => "O", "Ù" => "U", "Ú" => "U", "Û" => "U", "Ü" => "U", "Ý" => "Y", "ß" => "s",
	"à" => "a", "á" => "a", "â" => "a", "ã" => "a", "ä" => "a", "å"=> "a", "æ" => "a", "ç"=> "c",
	"è" => "e", "é" => "e", "ê" => "e", "ë" => "e", "ì" => "i", "í" => "i", "î" => "i", "ï"=> "i",
	"ð"=> "o", "ñ"=> "n", "ò" => "o", "ó" => "o", "ô" => "o", "õ" => "o", "ö" => "o", "ø" => "o",
	"ù" => "u", "ú" => "u", "û" => "u", "ü" => "u", "ý" => "y", "ÿ" => "y");

	$string=strtolower($string);

	$string = strtr($string , $caracteres);


	$len = strlen($string);
	for($a=0; $a<$len; $a++){
		$p = ord($string[$a]);
		if (($p > 64 && $p < 91) or ($p > 96 && $p < 123) or ($p > 47 && $p < 58)) { //  || $p == 32
			$return .= $string[$a];
		} else {
			$return .= "-";
		}
	}


	$i=0;
	while($i<5) {
		$return=str_replace("--","-",$return);
		$i++;
	}

	return $return;
}

if (!function_exists("chemin")) {
	function chemin($categorie, $DB) {
		global $libelle, $separateur_navigation, $chemin, $titleFR, $host ;
		$ss=$DB->query_first("SELECT count(catid) FROM categorie where parentid = '$categorie'");
		$categorie=$DB->query_first("SELECT parentid, $libelle FROM categorie where catid = '$categorie'");
		if ($ss[0] > 0)
			$chemin = " $separateur_navigation <a href=\"\" title=\"\">$categorie[$libelle]</a> $chemin" ;
		else
			$chemin = " $separateur_navigation $categorie[$libelle] $chemin" ;
		if ($categorie[parentid] != 0)
			chemin($categorie[parentid], $DB) ;
		else
			$chemin = "<a href=\"http://$host\" title=\"$titleFR\">$titleFR</a> $chemin" ;
		return $chemin ;
		}
	}


function url_rewrite($string) {
	$string=str_replace("","c",$string);
	$string=str_replace("_","s",$string);
	$string=str_replace(">","l",$string);
	$string=str_replace("Z","s",$string);
	$string=str_replace("B","i",$string);
	$string=str_replace("í","i",$string);
	$string=str_replace("}","z",$string);
	$string=str_replace("","e",$string);
	$string=str_replace("»","-",$string);
	$string=str_replace("$%","-",$string);
	$string=str_replace("^","s",$string);
	$string=str_replace("%","-",$string);
	$string=str_replace("[","s",$string);
	$string=str_replace("9","l",$string);
	$string=str_replace(":","l",$string);
	$string=str_replace("é","e",$string);
	$string=str_replace("ô","o",$string);
	$string=str_replace("ö","o",$string);
	$string=str_replace("ä","a",$string);
	$string=str_replace("Q","o",$string);
	$string=str_replace("d","t",$string);
	$string=str_replace("’%","-",$string);
	$string=str_replace("z","z",$string);
	$string=str_replace("W%","-",$string);
	$string=str_replace("Î","i",$string);
	$string=str_replace("¸","-",$string);
	$string=str_replace("ó","o",$string);
	$string=str_replace("ú","u",$string);
	$string=str_replace("","a",$string);
	$string=str_replace("","a",$string);
	$string=str_replace("~","z",$string);
	$string=str_replace("","e",$string);
	$string=str_replace("","c",$string);
	$string=str_replace("«","-",$string);
	$string=str_replace("‘%","-",$string);
	$string=str_replace("Á","a",$string);
	$string=str_replace("Â","a",$string);
	$string=str_replace("","e",$string);
	$string=str_replace("","c",$string);
	$string=str_replace("ç","c",$string);
	$string=str_replace("=","l",$string);
	$string=str_replace("o","u..",$string);
	$string=str_replace("ë","e",$string);
	$string=str_replace("]%","-",$string);
	$string=str_replace("{","z",$string);
	$string=str_replace("|","z",$string);
	$string=str_replace("c%","-",$string);
	$string=str_replace("“%","-",$string);
	$string=str_replace("%","-",$string);
	$string=str_replace("¬","-",$string);
	$string=str_replace("4%","-",$string);
	$string=str_replace("ß","b",$string);
	$string=str_replace("%","-",$string);
	$string=str_replace("Ó","o",$string);
	$string=str_replace(",%","-",$string);
	$string=str_replace("Ô","o",$string);
	$string=str_replace("%","-",$string);
	$string=str_replace("D","n",$string);
	$string=str_replace("%","-",$string);
	$string=str_replace("C","n",$string);
	$string=str_replace("<%","-",$string);
	$string=str_replace("H","n",$string);
	$string=str_replace("","a",$string);
	$string=str_replace("`","s",$string);
	$string=str_replace("","a",$string);
	$string=str_replace("a","s",$string);
	$string=str_replace("","d",$string);
	$string=str_replace("­","-",$string);
	$string=str_replace("T%","-",$string);
	$string=str_replace("Z%","-",$string);
	$string=str_replace("i%","-",$string);
	$string=str_replace("U","r",$string);
	$string=str_replace("f%","-",$string);
	$string=str_replace("p","u",$string);
	$string=str_replace("â","a",$string);
	$string=str_replace("P%","-",$string);
	$string=str_replace("Ý","y",$string);
	$string=str_replace("`%","-",$string);
	$string=str_replace("ý","y",$string);
	$string=str_replace("l%","-",$string);
	$string=str_replace("c","t",$string);
	$string=str_replace("¤","-",$string);
	$string=str_replace("´","-",$string);
	$string=str_replace("","d",$string);
	$string=str_replace("Ý","-",$string);
	$string=str_replace("Q%","-",$string);
	$string=str_replace("Ë","e",$string);
	$string=str_replace("Ç","-",$string);
	$string=str_replace("","d",$string);
	$string=str_replace("Û","-",$string);
	$string=str_replace("","d",$string);
	$string=str_replace("Ø","-",$string);
	$string=str_replace("Í","i",$string);
	$string=str_replace("÷","-",$string);
	$string=str_replace("G","n",$string);
	$string=str_replace("§","-",$string);
	$string=str_replace("","e",$string);
	$string=str_replace("°","-",$string);
	$string=str_replace("î","i",$string);
	$string=str_replace("e","t",$string);
	$string=str_replace("P","o",$string);
	$string=str_replace("Ü","u",$string);
	$string=str_replace("€%","-",$string);
	$string=str_replace("n","u",$string);
	$string=str_replace(" %","-",$string);
	$string=str_replace("Ö","o",$string);
	$string=str_replace("%","r",$string);
	$string=str_replace("Ù","-",$string);
	$string=str_replace("%","-",$string);
	$string=str_replace("¨","-",$string);
	$string=str_replace("ˆ%","-",$string);
	$string=str_replace("q","u",$string);
	$string=str_replace("„%","-",$string);
	$string=str_replace("X","r",$string);
	$string=str_replace("b","t",$string);
	$string=str_replace("Y","r",$string);
	$string=str_replace("," , "-" , $string);
	$string=str_replace(" " , "-" , $string);
	
	$string=str_replace("&eacute;" , "e" , $string);
	$string=str_replace("&egrave;" , "e" , $string);
	$string=str_replace("&agrave;" , "a" , $string);
	$string=str_replace("&ecirc;" , "e" , $string);
	$string=str_replace("&ocirc;" , "o" , $string);
	$string=str_replace("&deg;" , "-" , $string);
	$string=str_replace("&amp;" , "et" , $string);


	$string=str_replace("." , "-" , $string);
	$string=str_replace("&" , "et" , $string);
	$string=str_replace("+" , "-" , $string);
	$string=str_replace("'" , "-" , $string);
	$string=str_replace("\"" , "-" , $string);
	$string=str_replace("/" , "-" , $string);
	$string=str_replace("\\" , "-" , $string);
	$string=str_replace("--" , "-" , $string);
	$string=str_replace("--" , "-" , $string);
	$string=str_replace("--" , "-" , $string);
	$string=str_replace("--" , "-" , $string);
	$string=str_replace("--" , "-" , $string);
	$string=str_replace("--" , "-" , $string);

	return $string;
}

if (!function_exists("getpagenav2")) {
	function getpagenav2($results,$address) {
		global $perpage,$pagenumber,$pagenavpages;
		if ($results <= $perpage) {
			return "";
		}
		$totalpages = ceil($results/$perpage);
		if ($pagenumber>1) {
			$prevpage = $pagenumber-1;
			$prevlink = "<a href=".str_replace("&&" , $prevpage , $address ) .">&lt;</a> &nbsp;";
		}
		if ($pagenumber<$totalpages) {
			$nextpage = $pagenumber+1;
			$nextlink = "<a href=".str_replace("&&" , $nextpage , $address ) .">&gt;</a>";
		}
		while ($curpage++<$totalpages) {
			if ( ( $curpage <= $pagenumber-$pagenavpages || $curpage >= $pagenumber+$pagenavpages ) && $pagenavpages!=0 ) {
				if ($curpage==1) {
					$firstlink = "<a href=".str_replace("&&" , $curpage , $address ) .">&lt;&lt;</a> &nbsp;";
				}
				if ($curpage==$totalpages) {
					$lastlink = "&nbsp; <a href=".str_replace("&&" , $curpage , $address ) .">&gt;&gt;</a>";
				}
			} else {
				if ($curpage==$pagenumber) {
					$pagenav .= " $curpage &nbsp; ";
				} else {
					$pagenav .= "<a href=".str_replace("&&" , $curpage , $address ) .">$curpage</a> &nbsp; ";
				}
			}
		}
		$pagenav="$multilangue[page] $pagenumber / $totalpages : <b>[ $firstlink $prevlink $pagenav $nextlink $lastlink ]</b>";
		return $pagenav;
	}
}

function envoi($from, $to, $subject, $message)
	{
	$mailhead = "From: $from\n";
	$mailhead .= "Content-Type: text/html; charset=iso-8859-1\n";
	@mail($to, $subject, $message, $mailhead);	
	}


function amazon_arbo($dbLink, $artid, $libelle = "libelleFR") {

	global $host ;
	
	$result_array = array();
	$caract_array = array();
	$amatabcaracts = array();
	$amatab = array();

	// retourne les caractids pour lesquels il y a + d'1 valeur pour cet article
	$caract_req=$dbLink->query("SELECT caractid, COUNT(caractvalid) nb FROM article_caractval INNER JOIN caracteristiquevaleur USING(caractvalid) WHERE artid = '$artid' and caractid = '1' GROUP BY artid, caractid ORDER BY nb ASC"); //HAVING(nb > 1) 
	while ($caract=$dbLink->fetch_array($caract_req)) {
		$caract_array[] = $caract['caractid'];
		$amatabcaracts[$caract[caractid]] = $caract[nb] ;
		}
		
	//creer le tableau de combinaisons
	$combinaison_query = "SELECT article.artid".format_implode("", ", caract%%.caractvalid as cv%%, caract%%.lblval as lb%%, caract%%.lblcar as cl%%", $caract_array)." FROM article
		".format_implode("\n", "INNER JOIN (
												SELECT caracteristiquevaleur.$libelle as lblval, caracteristique.$libelle as lblcar, article_caractval.caractvalid, article_caractval.artid FROM article_caractval 
												INNER JOIN caracteristiquevaleur USING(caractvalid) 
												INNER JOIN caracteristique USING(caractid) 
												WHERE caractid = '%%'
											) as caract%% ON(caract%%.artid = article.artid)", $caract_array)."
		WHERE article.artid = '$artid'".($orderby ? " ORDER BY $orderby $sens" : "").";";
	
	$combinaison_req=$dbLink->query($combinaison_query);
	
	while ($combinaison=$dbLink->fetch_array($combinaison_req)) {
		$key_chain = "";
		if (is_array($caract_array)) {
			foreach($caract_array as $caractid)
				$key_chain .= '_'.$combinaison['cv'.$caractid];
			}
		if (is_array($caract_array)) {
			foreach($caract_array as $caractid)
				$result_array[$key_chain][$caractid] = $combinaison['lb'.$caractid];
			}
		}


	$amacount = count($amatabcaracts) ;
	if ($amacount == 0) {// pas de caracteristiques
		$codebarre=$dbLink->query_first("SELECT codebarre FROM code_barre WHERE artid = '$artid'");
		$amatab[0][SKU] = $artid ;
		$amatab[0][parentage] = "child" ;
		$amatab[0][ParentSKU] = "" ;
		$amatab[0][RelationshipType] = "" ;
		$amatab[0][K_Color] = "" ;
		$amatab[0][K_Size] = "" ;
		$amatab[0][K_VariationTheme] = "" ;
		$amatab[0][codebarre] = $codebarre[codebarre] ;
		}
	elseif ($amacount == 1) {// une seule caracteristique à plusieurs valeurs
		$VariationTheme = "" ;
		$amacpt = 0 ;
		foreach ($result_array as $cle => $valeur) {
			$val=$dbLink->query_first("SELECT $libelle FROM caracteristiquevaleur WHERE caractvalid = '".substr($cle, 1)."'");
			$codebarre=$dbLink->query_first("SELECT codebarre FROM code_barre WHERE artid = '$artid' and caractvalid = '".substr($cle, 1)."'");
			$amacpt++ ;
			$amatab[$amacpt][SKU] = $artid.$cle ;
			$amatab[$amacpt][parentage] = "child" ;
			$amatab[$amacpt][ParentSKU] = $artid ;
			$amatab[$amacpt][RelationshipType] = "Variation" ;
			$amatab[$amacpt][FD_Color] = stripslashes($val[$libelle]) ;
			$amatab[$amacpt][FD_Size] = "" ;
			$amatab[$amacpt][FD_VariationTheme] = "Color" ;
			$amatab[$amacpt][codebarre] = $codebarre[codebarre] ;
			$amatab[$amacpt][FD_image] = "http://$host/configurations/$host/images/caracteristiquevaleur/zoom/".substr($cle, 1).".jpg" ;
			$VariationTheme = "Color" ;
			}
		$amacpt = 0 ;
		$codebarre=$dbLink->query_first("SELECT codebarre FROM code_barre WHERE artid = '$artid'");
		$amatab[$amacpt][SKU] = $artid ;
		$amatab[$amacpt][parentage] = "parent" ;
		$amatab[$amacpt][ParentSKU] = "" ;
		$amatab[$amacpt][RelationshipType] = "" ;
		$amatab[$amacpt][FD_Color] = "" ;
		$amatab[$amacpt][FD_Size] = "" ;
		$amatab[$amacpt][FD_VariationTheme] = "Color" ;
		$amatab[$amacpt][codebarre] = $codebarre[codebarre] ;
		sort($amatab);
		}
	return $amatab ;
	}

function pixplace_arbo($dbLink, $artid, $libelle = "libelleFR") {

	global $host ;
	
	$result_array = array();
	$caract_array = array();
	$amatabcaracts = array();
	$amatab = array();

	// retourne les caractids pour lesquels il y a + d'1 valeur pour cet article
	$caract_req=$dbLink->query("SELECT caractid, COUNT(caractvalid) nb FROM article_caractval INNER JOIN caracteristiquevaleur USING(caractvalid) WHERE artid = '$artid' and caractid = '1' GROUP BY artid, caractid ORDER BY nb ASC"); //HAVING(nb > 1) 
	while ($caract=$dbLink->fetch_array($caract_req)) {
		$caract_array[] = $caract['caractid'];
		$amatabcaracts[$caract[caractid]] = $caract[nb] ;
		}
		
	//creer le tableau de combinaisons
	$combinaison_query = "SELECT article.artid".format_implode("", ", caract%%.caractvalid as cv%%, caract%%.lblval as lb%%, caract%%.lblcar as cl%%", $caract_array)." FROM article
		".format_implode("\n", "INNER JOIN (
												SELECT caracteristiquevaleur.$libelle as lblval, caracteristique.$libelle as lblcar, article_caractval.caractvalid, article_caractval.artid FROM article_caractval 
												INNER JOIN caracteristiquevaleur USING(caractvalid) 
												INNER JOIN caracteristique USING(caractid) 
												WHERE caractid = '%%'
											) as caract%% ON(caract%%.artid = article.artid)", $caract_array)."
		WHERE article.artid = '$artid'".($orderby ? " ORDER BY $orderby $sens" : "").";";
	
	$combinaison_req=$dbLink->query($combinaison_query);
	
	while ($combinaison=$dbLink->fetch_array($combinaison_req)) {
		$key_chain = "";
		if (is_array($caract_array)) {
			foreach($caract_array as $caractid)
				$key_chain .= '_'.$combinaison['cv'.$caractid];
			}
		if (is_array($caract_array)) {
			foreach($caract_array as $caractid)
				$result_array[$key_chain][$caractid] = $combinaison['lb'.$caractid];
			}
		}


	$amacount = count($amatabcaracts) ;
	if ($amacount == 0) {// pas de caracteristiques
		$codebarre=$dbLink->query_first("SELECT codebarre FROM code_barre WHERE artid = '$artid'");
		$amatab[0][id] = $artid ;
		$amatab[0][codebarre] = $codebarre[codebarre] ;
		}
	elseif ($amacount == 1) {// une seule caracteristique à plusieurs valeurs
		$VariationTheme = "" ;
		$amacpt = 0 ;
		foreach ($result_array as $cle => $valeur) {
			$val=$dbLink->query_first("SELECT $libelle FROM caracteristiquevaleur WHERE caractvalid = '".substr($cle, 1)."'");
			$codebarre=$dbLink->query_first("SELECT codebarre FROM code_barre WHERE artid = '$artid' and caractvalid = '".substr($cle, 1)."'");
			$amacpt++ ;
			$amatab[$amacpt][id] = $artid.$cle ;
			$amatab[$amacpt][couleur] = stripslashes($val[$libelle]) ;
			$amatab[$amacpt][codebarre] = $codebarre[codebarre] ;
			$amatab[$amacpt][image] = "http://$host/configurations/$host/images/caracteristiquevaleur/zoom/".substr($cle, 1).".jpg" ;
			}
		sort($amatab);
		}
	return $amatab ;
	}



if (!function_exists("categories_filles")) {
	function categories_filles($cat) {		
		global $DB_site2, $lstcatids ;
		$filles=$DB_site2->query("SELECT catid FROM categorie WHERE parentid = '$cat'");
		while ($fille=$DB_site2->fetch_array($filles)) {
			$lstcatids .= ",$fille[catid]" ;
			categories_filles($fille[catid]) ;
			}
		return $lstcatids ;
		}
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

function getJours($datedeb,$datefin,$utilisateur=""){ // format "Y-m-d"
	global $DB_site2;
	$jour_ferie_array=array();
	$absence_array=array();
	$jour_feries=$DB_site2->query("SELECT * FROM jour_ferie ORDER BY dateline ASC ");
	while($jour_ferie=$DB_site2->fetch_array($jour_feries)){
		$jour_ferie_array[]=$jour_ferie[dateline]; // ici les jours fériés
	}
	if($utilisateur!=""){
		$absences=$DB_site2->query("SELECT * FROM absence WHERE username='".$utilisateur."' AND absence_raisonid IN (1,3) "); // on ne décompte pas les jours d'absence injustifiées.
		while($absence=$DB_site2->fetch_array($absences)){
			$abs_dated=explode('-',$absence[datedebut]);
			$abs_datef=explode('-',$absence[datefin]);
			$abs_timestampcurr=mktime(0,0,0,$abs_dated[1],$abs_dated[2],$abs_dated[0]);
			$abs_timestampf=mktime(0,0,0,$abs_datef[1],$abs_datef[2],$abs_datef[0]);
			while($abs_timestampcurr<=$abs_timestampf){
				$absence_array[]=date("Y-m-d",$abs_timestampcurr); // ici les jours d'absence
				$abs_timestampcurr=mktime(0,0,0,date('m',$abs_timestampcurr),(date('d',$abs_timestampcurr)+1) ,date('Y',$abs_timestampcurr));
			}
		}
    }
    $nb_jours=0;
    $dated=explode('-',$datedeb);
    $datef=explode('-',$datefin);
    $timestampcurr=mktime(0,0,0,$dated[1],$dated[2],$dated[0]);
    $timestampf=mktime(0,0,0,$datef[1],$datef[2],$datef[0]);
    while($timestampcurr<=$timestampf){
		$datecurr=date('Y-m-d',$timestampcurr);
		if( !in_array($datecurr,$jour_ferie_array) && !in_array($datecurr,$absence_array) ){ // si c'est pas un jour férié
			if((date('w',$timestampcurr)!=0)&&(date('w',$timestampcurr)!=6)){ // si c'est pas un samedi ou un dimanche
				$nb_jours++;
			}
		}
		$timestampcurr=mktime(0,0,0,date('m',$timestampcurr),(date('d',$timestampcurr)+1) ,date('Y',$timestampcurr));
    }
	return $nb_jours;
}

?>
