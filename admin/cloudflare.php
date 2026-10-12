<?php
require "admin_global.php";

if(isset($_GET['mode']) && $_GET['mode']=="mini"){
	// on affiche la version minimaliste
	$minimaliste=1;
	header('Content-Type: text/html; charset=utf-8');
} else {
	$minimaliste=0;
}

$superusers = array("philippe", "cc", "benoit", "ariel", "pauline","jessica","denis","jb");
$is_superuser = in_array($user_info['username'], $superusers);



if($minimaliste==0){
	include "./header.php";
	include "./menu.php";
}
include "../class/fonctions.php";
include $rootpath."configurations/".$host."/class/cloudflare.class.php";


if($minimaliste==0){
	echo "<h1>Retirer des pages du cache de cloudflare</h1>";
	print "<fieldset>
		<legend>suppression d'une URL :</legend>
		<div>
			supprimer une URL simple :
			<form action=\"cloudflare.php?action=delete\" method=\"post\">
				URL : <br>
<textarea name=\"url\" rows=\"30\" cols=\"140\">
https://www.mon-espace-cuisson.com/
https://www.mon-espace-cuisson.com/css/css.css
</textarea><br>
				Indiquez les URL que vous souhaitez purger du cache (1 URL par ligne, pas de séparateur, 30 URL maxi)<br>
				<input type=\"submit\" value=\"envoyer\">
			</form>
		</div>
	</fieldset>
	<fieldset>
		<legend>suppression d'un article du cache :</legend>
		<div>
			<form action=\"cloudflare.php?action=deletearticle\" method=\"post\">
				artid : <br>
				<input type=\"text\" name=\"artid\" value=\"4286\"><br>
				Indiquez le artid<br>
				<input type=\"submit\" value=\"envoyer\">
			</form>
		</div>
	</fieldset>";
	if($is_superuser){
		echo "<fieldset>
				<legend>suppression de TOUTES LES URL :</legend>
				<div>
					supprimer toutes les URL sur MEC :
					<form action=\"cloudflare.php?action=deleteall&site=mec\" method=\"post\">
						<input type=\"submit\" value=\"envoyer\"> <b>!!!! ATTENTION : ceci fera ralentir le site !!!!!</b>
					</form>
			</fieldset>";
		echo "<fieldset>
				<legend>suppression du cache hdd du menu :</legend>
				<div>
					<form action=\"cloudflare.php?action=deletemenu\" method=\"post\">
						<input type=\"submit\" value=\"envoyer\">
					</form>
			</fieldset>";
	}
}


/*
$cloudflare_email = "webmaster1@mon-espace-cuisson.com";
$cloudflare_api = "2ed4a283e3f5d8747014ff3ec70980ea2c7a1";
$cloudflare_zone_id_mcav="f7cad87460fa491891e49b63b91cef01"; // MCAV
$cloudflare_zone_id_mwc="cd4f29762e99a7525a9d7b56863bdbdb"; // MWC
*/





if(isset($_GET['action'])){
	if($_GET['action']=="deletecateg"){
		echo "<b>execution de la purge des URL suivantes :</b><br>";
		include($rootpath."class/categorie.class.php");
		$categorie = new categorie($DB_site2, 0, $template->langue);
		$categorie->load($_GET['catid']);
		$urlCat = $template->mkurl("categorie", $categorie->catid,$categorie);
		$urls[] = "https://".$template->host.$urlCat;
		
		$cloud = new cloudflare_api($DB_site2,$rootpath);
		//$cloud->zone_id=$zone_id;
		while (list($key, $val) = each($urls)) {
			if($val!=""){
				$zoneid = $cloudflare_zone_id;
				$results = $cloud->purgeFiles($cloudflare_email, $cloudflare_api, $zoneid, array($val) );
				if($results->success === true) {
					echo "<br>URL : $val : <b>Execution r&eacute;alis&eacute;e avec succ&egrave;s.</b>";
				}
				if($results->success === false) {
					echo "<br>URL : $val : <b>ERREUR D'EXECUTION.</b>";
					var_dump($results);
				}
			}
		}
	}
    if($_GET['action']=="delete"){
		echo "<b>execution de la purge des URL suivantes :</b><br>";
		$urls = preg_split("/\\r\\n|\\r|\\n/", $_POST['url']);
		// echo nl2br($_POST['url']);
		$cloud = new cloudflare_api($DB_site2,$rootpath);
		//$cloud->zone_id=$zone_id;
		while (list($key, $val) = each($urls)) {
			if($val!=""){
				$zoneid = $cloudflare_zone_id;
				$results = $cloud->purgeFiles($cloudflare_email, $cloudflare_api, $zoneid, array($val) );
				if($results->success === true) {
					echo "<br>URL : $val : <b>Execution r&eacute;alis&eacute;e avec succ&egrave;s.</b>";
				}
				if($results->success === false) {
					echo "<br>URL : $val : <b>ERREUR D'EXECUTION.</b>";
					var_dump($results);
				}
			}
		}
    }

    if($_GET['action']=="deleteall"){
		echo "<b>execution de la purge totale ....</b><br>";
		$cloud = new cloudflare_api($DB_site2,$rootpath);
		$zoneid = $cloudflare_zone_id;
		$results = $cloud->purgeAll($cloudflare_email, $cloudflare_api, $zoneid);
		if($results->success === true) {
			echo "<br><b>..... Execution r&eacute;alis&eacute;e avec succ&egrave;s.</b>";
		}
		if($results->success === false) {
			echo "<br><b>ERREUR D'EXECUTION !!!!</b>";
		}
    }
	if($_GET['action']=="deletearticle"){
		echo "<b>Execution de la purge d'un article ....</b><br>";
		$cloud = new cloudflare_api($DB_site2,$rootpath);
		$results = $cloud->purgeArticle($artid);
		if($results[success] === true) {
			echo "<br><b>..... Execution r&eacute;alis&eacute;e avec succ&egrave;s.</b><br>";
			// echo "URLs : <br>".implode("<br>", $results[url1])."<br>".implode("<br>", $results[url2]);
			echo "URLs : <br>".implode("<br>", $results[url1]);
		}
		if($results[success] === false) {
			echo "<br><b>ERREUR D'EXECUTION !!!!</b>";
		}
		/* refaire pour la deuxième zone ....
		$results = $cloud->purgeArticle($email, $api, $zone_id, $artid);
		if($results->success === true) {
			echo "<br><b>..... Execution réalisée avec succès.</b>";
		}
		if($results->success === false) {
			echo "<br><b>ERREUR D'EXECUTION !!!!</b>";
		}
		*/
    }
	if($_GET['action']=="deletemenu"){
		unlink($rootpath."configurations/".$host."/cache/FR/menu0.html");
		unlink($rootpath."configurations/".$host."/cache/FR/menuresp0.html");
		unlink($rootpath."configurations/".$host."/cache/FR/sousmenus0.html");
		unlink($rootpath."configurations/".$host."/cache/FR/sousmenusResp0.html");
		unlink($rootpath."configurations/".$host."/cache/FR/soussousmenu0.html");
		echo "<br><b>fichiers supprimés</b>";
	}
}
if($minimaliste==0){
	include "./footer.php";
}

?>