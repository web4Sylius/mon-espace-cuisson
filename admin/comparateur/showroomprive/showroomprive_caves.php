<?php
require_once __DIR__.'/../../../secrets.php';
include_once($_SERVER['DOCUMENT_ROOT'].'/admin/host_config_admin.php');
include_once($_SERVER['DOCUMENT_ROOT']."/class/host.php");
$host_conf = new Host();
$host=$host_conf->host;
include_once($_SERVER['DOCUMENT_ROOT']."/configurations/".$host."/config_variables.php");
require_once($rootpath."class/devises.class.php");
require_once($rootpath."class/template.class.php");
require_once($rootpath."admin/mpfields.php");
require_once($rootpath."admin/export/xlexport.php");
require_once($rootpath."admin/ftp.php");
require_once($rootpath."class/devises.class.php");
require_once($rootpath."class/stock.class.php");
require_once($rootpath."class/frais_port.php");
require_once($rootpath."class/DAOgarantie.class.php");
require_once($rootpath."class/DAOarticle.class.php");
require_once($rootpath."class/garantie.class.php");
require_once($rootpath."class/email.php");
require 'vendor/autoload.php';

$template=new template("FR",$DB_site2);

use Mirakl\MMP\Shop\Client\ShopApiClient as Client;
use Mirakl\MCI\Shop\Request\Hierarchy\GetHierarchiesRequest;

$apiUrl = 'https://showroomprive.mirakl.net/api/offers/imports';
$apiKey = SHOWROOMPRIVE_API_KEY;

$arr_brand = array();
$arr_color = array();
$row = 1;
if (($handle = fopen("brands.csv", "r")) !== FALSE) {
	while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
		$num = count($data);

		$row++;
		for ($c=0; $c < $num; $c++) {
			$array_brand = explode(";",$data[$c]);
			$arr_brand[$array_brand[3]] = $array_brand[4];
		}
	}
	fclose($handle);
}
$row = 1;
if (($handle = fopen("color.csv", "r")) !== FALSE) {
	while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
		$num = count($data);

		$row++;
		for ($c=0; $c < $num; $c++) {
			$array_color = explode(";",$data[$c]);
			$arr_color[$array_color[3]] = $array_color[4];
		}
	}
	fclose($handle);
}

$marque_code="";
$color_en="";

$xml = ""
. "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n"
. "<import>\n";

$sql = "SELECT a.*, a.libelleFR as libart, c.libelleFR as libcat, mpc.enabled";
$sql .= " FROM market_place_champs mpc";
$sql .= " INNER JOIN article a ON (a.artcode = mpc.artcode)";
$sql .= " INNER JOIN categorie c ON (c.catid = a.catid)";
$sql .= " WHERE 1";
$sql .= " AND mpc.market_place = 'showroomprive'";
$sql .= " AND mpc.enabled = 1";
$sql .= " GROUP BY a.artid";
$sql .= " ORDER BY a.catid, a.artcode";
$articles = $DB_site2->query($sql);

$xml .= ""
. "<products>\n";

while ($article=$DB_site2->fetch_array($articles)) {
     //Remplacement des contenus de champs paramétré
	$sql = "SELECT champ, valeur";
	$sql .= " FROM market_place_catid";
	$sql .= " WHERE market_place = 'showroomprive'";
	$sql .= " AND catid = '".$article['catid']."'";
	$requete = $DB_site2->query($sql);
	while($champ = $DB_site2->fetch_array($requete)){
		$mp_field[$champ['champ']] = mpfields($article, $champ['valeur'],'<br/>&#13;<br />');
	}
	if($mp_field['categorie'] != '') {
		$xml .= ""
		. "<product>\n";


	// PRODUCT_CATEGORY
		$xml .= ""
		. "<attribute>\n"
		. "<code>PRODUCT_CATEGORY</code>\n"
		. "<value>".$mp_field['categorie']."</value>\n"
		. "</attribute>\n"
	// PRODUCT_SKU
		. "<attribute>\n"
		. "<code>PRODUCT_SKU</code>\n"
		. "<value>a_".$article['artcode']."</value>\n"
		. "</attribute>\n";


	// PRODUCT_TITLE (Marque - Type de produit - RÃ©fÃ©rence - CaractÃ©ristique principale - Couleur - Genre)
		$sql = "SELECT libelle";
		$sql .= " FROM marque";
		$sql .= " INNER JOIN article_marque USING(marqueid)";
		$sql .= " WHERE artid = '".$article['artid']."'";
		$marque = $DB_site2->query_first($sql);
		$libellecat = trim($article['libcat']);
		$gamme = $article['gammeFR'];
		$couleur = $template->translate($article['coloris']);
		$xml .= ""
		. "<attribute>\n"
		. "<code>PRODUCT_TITLE</code>\n"
		. "<value>".$marque['libelle'] . " - " . str_replace("&","&amp;",$article['libelleFR']) .($couleur!=''?' - '.$couleur:'')."</value>\n"
		. "</attribute>\n";


    // PRODUCT_EAN
		$sql = "SELECT codebarre";
		$sql .= " FROM code_barre";
		$sql .= " WHERE artid = '".$article['artid']."'";
		$ean = $DB_site2->query_first($sql);
		$xml .= ""
		. "<attribute>\n"
		. "<code>PRODUCT_EAN</code>\n"
		. "<value>".$ean['codebarre']."</value>\n"
		. "</attribute>\n";

	// PRODUCT_DESCRIPTION
		$sql = "SELECT p.libelle";
		$sql .= " FROM pays p";
		$sql .= " INNER JOIN article a ON a.paysid = p.paysid";
		$sql .= " WHERE artid = ".$article['artid'];
		$pays = $DB_site2->query_first($sql);
		if($pays['libelle']=='France Continentale (hors Corse)') { $pays['libelle'] = 'France'; }

			if(mpfields($article,$mp_field['description']) != "") {
				$Description = str_replace('<br>', '&#13;', $mp_field['description']);
				$Description = str_replace('<p>', '&#13;', $Description);
				$Description = str_replace('</p>', '', $Description);
				$Description = str_replace("&","&amp;",$Description);
				$xml .= ""
				. "<attribute>\n"
				. "<code>PRODUCT_DESCRIPTION</code>\n"
				. "<value>".html_entity_decode(strip_tags(mpfields($article,$Description)))."</value>\n"
				. "</attribute>\n";
			}
			else {
				$Description = str_replace('<br>', '&#13;', $article['descriptionFR']);
				$Description = str_replace('<p>', '&#13;', $Description);
				$Description = str_replace('</p>', '', $Description);
				$Description = str_replace("&","&amp;",$Description);
				$Description = html_entity_decode(strip_tags($Description));

				$xml .= ""
				. "<attribute>\n"
				. "<code>PRODUCT_DESCRIPTION</code>\n"
				. "<value><![CDATA[".(strlen($Description)>4000?substr($Description,0,3990).'...':substr($Description,0,4000))."]]></value>\n"
				. "</attribute>\n";
			}

    // PRODUCT_BRAND
		$marque_code = array_search(strtolower(str_replace('à','a',$marque['libelle'])), array_map('strtolower',$arr_brand));
		$xml .= ""
		. "<attribute>\n"
		. "<code>PRODUCT_BRAND</code>\n"
		. "<value>".$marque_code."</value>\n"
		. "</attribute>\n";

    // PRODUCT_COLOR
		$color_en = array_search($template->translate($article['coloris']), $arr_color);
		$xml .= ""
		. "<attribute>\n"
		. "<code>PRODUCT_COLOR</code>\n"
		. "<value>".$color_en."</value>\n"
		. "</attribute>\n";

    // PRODUCT_RECOMMENDED_RETAIL_PRICE
		$req_article_site=$DB_site2->query_first("SELECT * FROM article_site WHERE artid='".$article['artid']."'");
		$tauxchoisi=$req_article_site['tauxchoisi'];
		$taux=$DB_site2->query_first("SELECT 0,TVAtauxnormal,TVAtauxreduit FROM pays WHERE paysid='57'");
  		$tvapc=$taux[$tauxchoisi];
  		$prixconseille=round($article['prixconseilleht']*(1+$tvapc/100),2);
  		
		$xml .= ""
		. "<attribute>\n"
		. "<code>PRODUCT_RECOMMENDED_RETAIL_PRICE</code>\n"
		. "<value>".$prixconseille."</value>\n"
		. "</attribute>\n";

    // PRODUCT_MAIN_PICTURE
		///image width min 500px

		$image = imagecreatefromjpeg("https://$host/configurations/".$host."/images/produits/".$article['artid'].".".$article['image']);
		if(imagesx($image) < 500 && !file_exists($rootpath."configurations/".$host."/images/produits/".$article['artid']."_min500".".".$article['image'])){
			$fond = imagecreatetruecolor(500, imagesy($image));
   			imagecopy($fond, $image, (imagesx($fond)-imagesx($image))/2, 0, 0, 0, imagesx($fond), imagesy($image));
			imagejpeg($fond,"https://$host/configurations/".$host."/images/produits/".$article['artid']."_min500".".".$article['image']);
			$img="https://$host/configurations/".$host."/images/produits/".$article['artid']."_min500".".".$article['image'];
		}
		elseif(imagesx($image) < 500 && file_exists($rootpath."configurations/".$host."/images/produits/".$article['artid']."_min500".".".$article['image'])){
			$img="https://$host/configurations/".$host."/images/produits/".$article['artid']."_min500".".".$article['image'];
		}
		else{
			$img = "https://$host/configurations/".$host."/images/produits/".$article['artid'].".".$article['image'];
		}
		
		$xml .= ""
		. "<attribute>\n"
		. "<code>PRODUCT_MAIN_PICTURE</code>\n"
		. "<value>".$img."</value>\n"
		. "</attribute>\n";
    	//Récupération des visuels de l'article
		$sql = "SELECT articlephotoid";
		$sql .= " FROM articlephoto";
		$sql .= " WHERE artid = ".$article['artid'];
		$sql .= " ORDER BY position LIMIT 3";
		$r = $DB_site2->query($sql);
		if($DB_site2->num_rows($r)>0) {
			$iphoto=0;
			while($im = $DB_site2->fetch_array($r)){
				$iphoto++;
				///image width min 500px
				$image = imagecreatefromjpeg("https://$host/configurations/".$host."/images/produits/".$article['artid']."_".$im[0].".".$article['image']);
				if(imagesx($image) < 500 && !file_exists($rootpath."configurations/".$host."/images/produits/".$article['artid']."_".$im[0]."_min500".".".$article['image'])){
					$fond = imagecreatetruecolor(500, imagesy($image));
					imagecopy($fond, $image, (imagesx($fond)-imagesx($image))/2, 0, 0, 0, imagesx($fond), imagesy($image));
					imagejpeg($fond,"https://$host/configurations/".$host."/images/produits/".$article['artid']."_".$im[0]."_min500".".".$article['image']);
					$img="https://$host/configurations/".$host."/images/produits/".$article['artid']."_".$im[0]."_min500".".".$article['image'];
				}
				elseif(imagesx($image) < 500 && file_exists($rootpath."configurations/".$host."/images/produits/".$article['artid']."_".$im[0]."_min500".".".$article['image'])){
					$img="https://$host/configurations/".$host."/images/produits/".$article['artid']."_".$im[0]."_min500".".".$article['image'];
				}
				else{
					$img = "https://$host/configurations/".$host."/images/produits/".$article['artid']."_".$im[0].".".$article['image'];
				}

				$xml .= ""
				. "<attribute>\n"
				. "<code>PRODUCT_OTHER_PICTURE_".$iphoto."</code>\n"
				. "<value>".$img."</value>\n"
				. "</attribute>\n";
			}
		}
		else {
			$xml .= ""
			. "<attribute>\n"
			. "<code>PRODUCT_OTHER_PICTURE_1</code>\n"
			. "<value/>\n"
			. "</attribute>\n"
			. "<attribute>\n"
			. "<code>PRODUCT_OTHER_PICTURE_2</code>\n"
			. "<value/>\n"
			. "</attribute>\n"
			. "<attribute>\n"
			. "<code>PRODUCT_OTHER_PICTURE_3</code>\n"
			. "<value/>\n"
			. "</attribute>\n";
		}

    // PRODUCT_VARIANT_GROUP_CODE
		$xml .= ""
		. "<attribute>\n"
		. "<code>PRODUCT_VARIANT_GROUP_CODE</code>\n"
		. "<value/>\n"
		. "</attribute>\n";

    // PRODUCT_MANUFACTURER_PART_NUMBER
		$xml .= ""
		. "<attribute>\n"
		. "<code>PRODUCT_MANUFACTURER_PART_NUMBER</code>\n"
		. "<value/>\n"
		. "</attribute>\n";

    // PRODUCT_MANUFACTURE_COUNTRY
		$xml .= ""
		. "<attribute>\n"
		. "<code>PRODUCT_MANUFACTURE_COUNTRY</code>\n"
		. "<value/>\n"
		. "</attribute>\n";

    // SUSTAINABLE_DEVELOPMENT
		$xml .= ""
		. "<attribute>\n"
		. "<code>SUSTAINABLE_DEVELOPMENT</code>\n"
		. "<value/>\n"
		. "</attribute>\n";

    // ECOLABEL
		$xml .= ""
		. "<attribute>\n"
		. "<code>ECOLABEL</code>\n"
		. "<value/>\n"
		. "</attribute>\n";

    // PRODUCT_CE_MARKING
		$xml .= ""
		. "<attribute>\n"
		. "<code>PRODUCT_CE_MARKING</code>\n"
		. "<value/>\n"
		. "</attribute>\n";

    // PRODUCT_DIMENSION_IN_CM
		$xml .= ""
		. "<attribute>\n"
		. "<code>PRODUCT_DIMENSION_IN_CM</code>\n"
		. "<value>L".($article['largeur'] / 10) . "x P".($article['profondeur'] / 10) . "x H".($article['hauteur'] / 10) ."cm</value>\n"
		. "</attribute>\n";

    // PRODUCT_INGREDIENTS
		$xml .= ""
		. "<attribute>\n"
		. "<code>PRODUCT_INGREDIENTS</code>\n"
		. "<value/>\n"
		. "</attribute>\n";

    // PRODUCT_MODEL_NAME
		$xml .= ""
		. "<attribute>\n"
		. "<code>PRODUCT_MODEL_NAME</code>\n"
		. "<value/>\n"
		. "</attribute>\n";

    // PRODUCT_HEIGHT_IN_CENTIMETER
		$xml .= ""
		. "<attribute>\n"
		. "<code>PRODUCT_HEIGHT_IN_CENTIMETER</code>\n"
		. "<value>".($article['hauteur'] / 10)."</value>\n"
		. "</attribute>\n";

    // PRODUCT_LENGTH_IN_CENTIMETER
		$xml .= ""
		. "<attribute>\n"
		. "<code>PRODUCT_LENGTH_IN_CENTIMETER</code>\n"
		. "<value>".($article['largeur'] / 10)."</value>\n"
		. "</attribute>\n";

    // PRODUCT_DEPTH_IN_CENTIMETER
		$xml .= ""
		. "<attribute>\n"
		. "<code>PRODUCT_DEPTH_IN_CENTIMETER</code>\n"
		. "<value>".($article['profondeur'] / 10)."</value>\n"
		. "</attribute>\n";

    // PRODUCT_REPAIRABILITY_INDEX
		$xml .= ""
		. "<attribute>\n"
		. "<code>PRODUCT_REPAIRABILITY_INDEX</code>\n"
		. "<value/>\n"
		. "</attribute>\n";

    // PRODUCT_ENERGY_EFFICIENCY_CLASS
		$xml .= ""
		. "<attribute>\n"
		. "<code>PRODUCT_ENERGY_EFFICIENCY_CLASS</code>\n"
		. "<value>".$article['energy_label']."</value>\n"
		. "</attribute>\n";

    // PRODUCT_ENERGY_EFFICIENCY_CLASS_URL
		$energy_label_file = $DB_site2->query_first("SELECT ext FROM article_fiche_energie WHERE artid= ".$article['artid']);
		if($energy_label_file[0] != ""){
			$xml .= ""
			. "<attribute>\n"
			. "<code>PRODUCT_ENERGY_EFFICIENCY_CLASS_URL</code>\n"
			. "<value>http://$host/configurations/".$host."/images/energy_label/".$article['artid'].".".$energy_label_file['ext']."</value>\n"
			. "</attribute>\n";
		}
		else {
			$xml .= ""
			. "<attribute>\n"
			. "<code>PRODUCT_ENERGY_EFFICIENCY_CLASS_URL</code>\n"
			. "<value/>\n"
			. "</attribute>\n";
		}


	// PRODUCT_WEIGHT_IN_KG
		$xml .= ""
		. "<attribute>\n"
		. "<code>PRODUCT_WEIGHT_IN_KG</code>\n"
		. "<value>".($article['poid'] / 1000)."</value>\n"
		. "</attribute>\n";

	// PRODUCT_TOTAL_VOLUME_IN_LITERS
		$xml .= ""
		. "<attribute>\n"
		. "<code>PRODUCT_TOTAL_VOLUME_IN_LITERS</code>\n"
		. "<value/>\n"
		. "</attribute>\n";

	// PRODUCT_VOLTAGE_IN_VOLT
		$xml .= ""
		. "<attribute>\n"
		. "<code>PRODUCT_VOLTAGE_IN_VOLT</code>\n"
		. "<value/>\n"
		. "</attribute>\n";

	// PRODUCT_POWER_IN_WATT
		$xml .= ""
		. "<attribute>\n"
		. "<code>PRODUCT_POWER_IN_WATT</code>\n"
		. "<value/>\n"
		. "</attribute>\n";

	// PRODUCT_CONNECTED
		$xml .= ""
		. "<attribute>\n"
		. "<code>PRODUCT_CONNECTED</code>\n"
		. "<value/>\n"
		. "</attribute>\n";

	// PRODUCT_NUMBER_OF_GEARS
		$xml .= ""
		. "<attribute>\n"
		. "<code>PRODUCT_NUMBER_OF_GEARS</code>\n"
		. "<value/>\n"
		. "</attribute>\n";

	// PRODUCT_NUMBER_OF_PROGRAMS
		$xml .= ""
		. "<attribute>\n"
		. "<code>PRODUCT_NUMBER_OF_PROGRAMS</code>\n"
		. "<value/>\n"
		. "</attribute>\n";

	// PRODUCT_GENDER
		$xml .= ""
		. "<attribute>\n"
		. "<code>PRODUCT_GENDER</code>\n"
		. "<value/>\n"
		. "</attribute>\n";

	// PRODUCT_NUMBER_OF_DOORS
		$xml .= ""
		. "<attribute>\n"
		. "<code>PRODUCT_NUMBER_OF_DOORS</code>\n"
		. "<value/>\n"
		. "</attribute>\n";

    // PRODUCT_NUMBER_OF_DRAWERS
		$xml .= ""
		. "<attribute>\n"
		. "<code>PRODUCT_NUMBER_OF_DRAWERS</code>\n"
		. "<value/>\n"
		. "</attribute>\n";


	// PRODUCT_CAPACITY_IN_NUMBER_OF_BOTTLES
		if($mp_field['categorie'] == 'WINE_FRIDGE_ACCESSORIES') {
			$xml .= ""
			. "<attribute>\n"
			. "<code>PRODUCT_CAPACITY_IN_NUMBER_OF_BOTTLES</code>\n"
			. "<value/>\n"
			. "</attribute>\n"
			. "</product>\n";
		}
		else {
			$xml .= ""
			. "<attribute>\n"
			. "<code>PRODUCT_CAPACITY_IN_NUMBER_OF_BOTTLES</code>\n"
			. "<value>".$article['capacite_bouteilles']."</value>\n"
			. "</attribute>\n"
			. "</product>\n";
		}
	}
}
$xml .= ""
. "</products>\n"
. "<offers>\n";

$sql = "SELECT a.*, a.libelleFR libart, c.libelleFR libcat, mpc.enabled";
$sql .= " FROM market_place_champs mpc";
$sql .= " INNER JOIN article a ON (a.artcode = mpc.artcode)";
$sql .= " INNER JOIN categorie c ON (c.catid = a.catid)";
$sql .= " WHERE 1";
$sql .= " AND mpc.market_place = 'showroomprive'";
$sql .= " AND mpc.enabled = 1";
$sql .= " GROUP BY a.artid";
$sql .= " ORDER BY a.catid, a.artcode";
$articles = $DB_site2->query($sql);

while ($article=$DB_site2->fetch_array($articles)) {
	$sql = "SELECT champ, valeur";
	$sql .= " FROM market_place_catid";
	$sql .= " WHERE market_place = 'showroomprive'";
	$sql .= " AND catid = '".$article['catid']."'";
	$requete = $DB_site2->query($sql);
	while($champ = $DB_site2->fetch_array($requete)){
		$mp_field[$champ['champ']] = mpfields($article, $champ['valeur'],'<br/>&#13;<br />');
	}
	if($mp_field['categorie'] != '') {

		$xml .= ""
		. "<offer>\n";

    // sku
		$xml .= ""
		. "<sku>a_".$article['artcode']."</sku>\n";

	// product-id
		$sql = "SELECT codebarre";
		$sql .= " FROM code_barre";
		$sql .= " WHERE artid = '".$article['artid']."'";
		$ean = $DB_site2->query_first($sql);

		$xml .= ""
		. "<product-id>".$ean['codebarre']."</product-id>\n";

	// product-id-type
		$xml .= ""
		. "<product-id-type>EAN</product-id-type>\n";

	// description
				$xml .= ""
				. "<description>Garantie: ".$article['garantie_incluse'].". Livraison: ".$article['delailivraison_txt']."</description>\n";

    // internal-description
		$xml .= ""
		. "<internal-description></internal-description>\n";

	// price
		$req_article_site=$DB_site2->query_first("SELECT * FROM article_site WHERE artid='".$article['artid']."'");
		$tauxchoisi=$req_article_site['tauxchoisi'];
		$taux=$DB_site2->query_first("SELECT 0,TVAtauxnormal,TVAtauxreduit FROM pays WHERE paysid='57'");
  		$tvapc=$taux[$tauxchoisi];
  		$prixconseille=round($article['prixconseilleht']*(1+$tvapc/100),2);
		$xml .= ""
		. "<price>".$prixconseille."</price>\n";

	// price-additional-info
		$xml .= ""
		. "<price-additional-info></price-additional-info>\n";

	// quantity
		$xml .= ""
		. "<quantity>20</quantity>\n";

	// min-quantity-alert
		$xml .= ""
		. "<min-quantity-alert>3</min-quantity-alert>\n";

	// state
		$xml .= ""
		. "<state>11</state>\n";

	// available-start-date
		$xml .= ""
		. "<available-start-date></available-start-date>\n";

	// available-end-date
		$xml .= ""
		. "<available-end-date></available-end-date>\n";

		if($prixconseille == $article['prix']) {
			$prix_discount='';
			if($article['prix'] > 49){
				if(($article['poid'] / 1000) < 60){
					$logistic='CatLog1';
				}
				elseif(($article['poid'] / 1000) < 110){
					$logistic='CatLog2';
				}
				elseif(($article['poid'] / 1000) < 130){
					$logistic='CatLog3';
				}
				elseif(($article['poid'] / 1000) < 150){
					$logistic='CatLog4';
				}
				elseif(($article['poid'] / 1000) < 200){
					$logistic='CatLog5';
				}
			}
			else {
				if(($article['poid'] / 1000) < 60){
					$logistic='CatLog6';
				}
			}
		}
		else {
			$prix_discount=$article['prix'];
			if($article['prix'] > 49){
				if(($article['poid'] / 1000) < 60){
					$logistic='CatLog1';
				}
				elseif(($article['poid'] / 1000) < 110){
					$logistic='CatLog2';
				}
				elseif(($article['poid'] / 1000) < 130){
					$logistic='CatLog3';
				}
				elseif(($article['poid'] / 1000) < 150){
					$logistic='CatLog4';
				}
				elseif(($article['poid'] / 1000) < 200){
					$logistic='CatLog5';
				}
			}
			else {
				if(($article['poid'] / 1000) < 60){
					$logistic='CatLog6';
				}
			}
		}

	// logistic-class
		
		$xml .= ""
		. "<logistic-class>".$logistic."</logistic-class>\n";

	// discount-price
		
		$xml .= ""
		. "<discount-price>".$prix_discount."</discount-price>\n";

	// discount-start-date
		$xml .= ""
		. "<discount-start-date></discount-start-date>\n";

	// discount-end-date
		$xml .= ""
		. "<discount-end-date></discount-end-date>\n";

	// leadtime-to-ship
		if($article['delailivraison'] < 3) {
			$delai='3';
		}
		else {
			$delai=$article['delailivraison'];
		}
		$xml .= ""
		. "<leadtime-to-ship>".$delai."</leadtime-to-ship>\n";

	// update-delete
		$xml .= ""
		. "<update-delete></update-delete>\n";

		$xml .= ""
    // ecotax
		. "<offer-additional-fields>\n"
		. "<offer-additional-field>\n"
		. "<code>ecotax</code>\n"
		. "<value>".$article['ecopart']."</value>\n"
		. "</offer-additional-field>\n"
	// shipping-country
		. "<offer-additional-field>\n"
		. "<code>shipping-country</code>\n"
		. "<value></value>\n"
		. "</offer-additional-field>\n"
	// warranty
		. "<offer-additional-field>\n"
		. "<code>warranty</code>\n"
		. "<value></value>\n"
		. "</offer-additional-field>\n"
		. "</offer-additional-fields>\n";


		$xml .= ""
		. "</offer>\n";
	}
}
$xml .= ""
. "</offers>\n"
. "</import>";

$filename = $rootpath."configurations/".$host."/exports/showroomprive.xml";
$handle = fopen($filename, 'w+') ;

fwrite($handle, $xml);

fclose($handle);
header('Content-Description: File Transfer'); 
header('Content-Type: text/xml'); 
header('Content-Disposition: attachment; filename=showroomprive.xml'); 
readfile($filename);

$curl = curl_init($apiUrl);
curl_setopt($curl, CURLOPT_URL, $apiUrl);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

$headers = array(
	"Accept: application/json",
	"Authorization: ".$apiKey,
	"Content-Type: multipart/form-data",

);
curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
$cfile = new CURLFile($filename,'text/xml','showroomprive');

// Assign POST data
$data = array('file' => $cfile, 'import_mode' => 'REPLACE','with_products' => 'true');
curl_setopt($curl, CURLOPT_POST,1);
curl_setopt($curl, CURLOPT_POSTFIELDS, $data);


$resp = curl_exec($curl);

curl_close($curl);


?>