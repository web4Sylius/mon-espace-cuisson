<?php
//EXPORT FLUX VERS RUE DU COMMERCE//
//Les offres sont toutes écrasées puis recréées à partir de ce fichier
if(isset($_SERVER['SERVER_NAME']) && strpos($_SERVER['SERVER_NAME'], 'localhost') !== false){
    $rootpath = "d:/wamp/www/ma-cave-a-vin/";
} else if(isset($_SERVER['SERVER_NAME']) && strpos($_SERVER['SERVER_NAME'], 'test2015') !== false) {
    $rootpath = "/home/ma-cave-a-vin-2015/";
} else if(isset($_SERVER['SERVER_NAME']) && strpos($_SERVER['SERVER_NAME'], 'test') !== false) {
    $rootpath = $_SERVER['DOCUMENT_ROOT']."/";
} else {
    $rootpath   = "/var/www/vhosts/ma-cave-a-vin.fr/httpdocs/";
}
if(!isset($_SERVER['HTTP_HOST'])) $_SERVER['HTTP_HOST']="www.ma-cave-a-vin.fr";
include_once($rootpath."class/host.php");
$host_conf = new Host();
$host=$host_conf->host;

ini_set("max_execution_time",180);
ini_set("display_errors","on");
$prov = "comp" ;

//$rootpath="/media/disk/www/macave/site2012/";
//$rootpath="/var/www/vhosts/ma-cave-a-vin.fr/httpdocs/";

$nostat = 1;

require_once($rootpath."configurations/$host/config_mysql.php");
require_once($rootpath."class/mysql.php");
require_once($rootpath."class/template.class.php");
require_once($rootpath."admin/mpfields.php");
require_once($rootpath."class/fonctions.php");
require_once($rootpath."class/devises.class.php");
require_once($rootpath."class/stock.class.php");

// ini_set("display_errors","on");

$libelle     = "libelleFR";
$description = "descriptionFR";

$today = mktime(0,0,0)+86400;

$DB_site2 = new DB_Sql;
$DB_site2->appname='boutique';
$DB_site2->appshortname='boutique';
$DB_site2->database=$DBname;
$DB_site2->server=$db_server;
$DB_site2->user=$DBusername;
$DB_site2->password=$DBpassword;
$DB_site2->connect();

$DB_site = clone $DB_site2;

$template = new template("FR",$DB_site2);

$tabProduitsSoldes = array(
    /*
    2275, //ADV102O
    1643, //ADV209
    3128, //ADV703
    998, //ADV702
    983, //ADV414
    977, //ADV409
    978, //ADV802
    4089, //ADV860
    4090, //ADV861
    901, //ADV104CS
    3301, //AVI460
    2883, //AVI436
    2788, //AVI430
    2852, //AVI435
    2882, //AVI434
    3298, //CLI710
    4042, //CLI550
    870, //CLI421
    3800, //CLI120
    3124, //CLI101
    3264, //CLI704
    3151, //CLI574E
    3122, //CLI476
    2227, //ACI-CLI603-2
    */
);

function nice($var, $color = 'black'){
          echo "<div style=\"color:".$color.";\">";
          echo "<p style=\"color:red; font-weight:bold;\">Contenu de la variable :</p>";
          if(is_array($var) || is_object($var)){
                    echo "<pre>";
                    print_r($var);
                    echo "</pre>";   
          } else {
                    var_dump($var);
          }
          echo "</div>";
}

function nicex($var){
          nice($var);
          exit();
}

if(isset($_GET['list'])){
     $f = fopen($_GET['list'],"r");
     $i = 0;
     while(!feof($f)){
          $line = fgets($f,512);
          
          $sql = "UPDATE market_place_champs SET";
          $sql .= " enabled = 1";
          $sql .= " WHERE artcode = '".strtoupper(chop($line))."'";
          $result = $DB_site2->query($sql);
          
          print "<br>".$line." ".$result;
          $i++;
     }
     fclose($f);
     print "<br>".$i." activés";
     exit;
}

$liste_catid = "19,22,21,24,23,20,39,12,58,59,60,61,62,63,64,65,52,53,54,55,56,57,66,51,50,46,47,45,48,4,15,26,17,18,30,31,16,40,5,14,37,36,35,38";

function url_rewrite($string){
     $string = html_entity_decode($string , ENT_NOQUOTES , "ISO-8859-1" );
     $caracteres = array(
          "¥" => "Y", "µ" => "u", "À" => "A", "Á" => "A", "Â" => "A", "Ã" => "A", "Ä" => "A", "Å" => "A",
          "Æ" => "A", "Ç" => "C", "È" => "E", "É" => "E", "Ê"=> "E", "Ë" => "E", "Ì" => "I", "Í" => "I",
          "Î" => "I", "Ï" => "I", "Ð" => "D", "Ñ" => "N", "Ò" => "O", "Ó" => "O", "Ô" => "O", "Õ" => "O",
          "Ö" => "O", "Ø" => "O", "Ù" => "U", "Ú" => "U", "Û" => "U", "Ü" => "U", "Ý" => "Y", "ß" => "s",
          "à" => "a", "á" => "a", "â" => "a", "ã" => "a", "ä" => "a", "å"=> "a", "æ" => "a", "ç"=> "c",
          "è" => "e", "é" => "e", "ê" => "e", "ë" => "e", "ì" => "i", "í" => "i", "î" => "i", "ï"=> "i",
          "ð"=> "o", "ñ"=> "n", "ò" => "o", "ó" => "o", "ô" => "o", "õ" => "o", "ö" => "o", "ø" => "o",
          "ù" => "u", "ú" => "u", "û" => "u", "ü" => "u", "ý" => "y", "ÿ" => "y");
     
     $string = strtr($string , $caracteres);
     $string = strtolower($string);
     $len    = strlen($string);
    
     for($a = 0; $a < $len; $a++){
          $p = ord($string[$a]);
          if(($p > 96 && $p < 123) or ($p > 47 && $p < 58)) {
              $return .= $string[$a];
          } else {
              $return .= "-";
          }
     }
     
     while(strpos($return,"--") !== FALSE) {
          $return=str_replace("--","-",$return);
     }
     
     $return = trim($return,"-");
    
     return $return;
}

if(!is_dir($rootpath."configurations/".$host."/exports")){
     mkdir($rootpath."configurations/".$host."/exports",0777);
}

$filename = $rootpath."configurations/$host/exports/rueducommerce.xml";

if(!$handle = fopen($filename, 'w')){
     echo "Impossible d'ouvrir le fichier ($filename)";
     exit;
}

if(fwrite($handle, stripslashes(html_entity_decode($contenu))) === FALSE){
     echo "Impossible d'écrire dans le fichier ($filename)";
     exit;
}

$xml = ""
. "<?xml version=\"1.0\" encoding=\"UTF-8\"?>"
     . "<catalog xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" language=\"FR\" country=\"FR\" version=\"1.0.7\" date=\"".date('Y-m-d')." 08:00:00\" GMT=\"+1\">"
          . "<merchant mmid=\"MM-8A794M2223\">"
               . "<name>Ma cave a vin</name>"
               . "<url>http://".$host."</url>"
               . "<deliverytype type=\"Livraison Standard\">"
                    . "<currency>EUR</currency>";

$fraisport  = array();
$fdpgratuit = 1000000;

$sql = "SELECT fraisportid, prix, poiddebut, poidfin, gratuit";
$sql .= " FROM fraisport";
$sql .= " WHERE paysid = 57";
$sql .= " ORDER BY poiddebut";
$r = $DB_site2->query($sql);
while($fdp = $DB_site2->fetch_array($r)){
     
     $xml .= ""
     . "<deliverymethod rule=\"".$fdp['fraisportid']."\">"
          . "<zone type=\"country\" id=\"FR\">"
               . "<cost>".$fdp['prix']."</cost>"
               . "<shippingdelay delay=\"1\" unit=\"D\"/>"
          . "</zone>"
     . "</deliverymethod>\n";
     
    $fraisport[$fdp['fraisportid']] = array($fdp['poiddebut'],$fdp['poidfin']);
    $fdpgratuit = $fdp['gratuit'];
    
}

$xml .= ""
          . "<deliverymethod rule=\"1\">"
               . "<zone type=\"country\" id=\"FR\">"
                    . "<cost>0</cost>"
                    . "<shippingdelay delay=\"1\" unit=\"D\"/>"
               . "</zone>"
          . "</deliverymethod>"
     . "</deliverytype>"
. "</merchant>"
. "<products>";
        
fwrite($handle, $xml);      

$sql = "SELECT 0 AS taux0,TVAtauxnormal AS taux1, TVAtauxreduit AS taux2";
$sql .= " FROM pays";
$sql .= " WHERE paysid = 57";
$tvafrance = $DB_site2->query_first($sql);
    
$enddate=date("Y-m-d H:i:s",time()+86400*31);

$sql = "SELECT a.*";
$sql .= " FROM article a";
//$sql .= " INNER JOIN categorie c USING(catid)";
$sql .= " INNER JOIN article_site a_s USING(artid)";
$sql .= " WHERE a_s.active = '1'";
//$sql .= " AND commandable = '1'";
$sql .= " GROUP BY artcode";
$sql .= " ORDER BY a.artid";
$articles = $DB_site2->query($sql); // and artid = '105'

$compteur = 0;
while($article = $DB_site2->fetch_array($articles)){
     
     /*$tabLivraison = delailivraison($article['artid']);
     if(empty($tabLivraison['date_appro']) && $article['date_appro'] > 0) $tabLivraison['date_appro'] = $article['date_appro'];*/

     $xml      = "";
     $mp_field = array();
     
     $sql = "SELECT enabled";
     $sql .= " FROM market_place_champs";
     $sql .= " WHERE market_place = 'rdc'";
     $sql .= " AND artcode = '".$article['artcode']."'";
     $allenabled = $DB_site2->query_first($sql);

     /*$sql = "SELECT ac.image as imagecaractvalid, cv.image, ac.caractvalid, ac.artid, cv.".$libelle;
     $sql .= " FROM article_caractval ac";
     $sql .= " INNER JOIN caracteristiquevaleur cv USING(caractvalid)";
     $sql .= " WHERE ac.artid = ".$article['artid'];
     $sql .= " AND cv.caractid = '1'";
     $coloriss = $DB_site2->query($sql);*/
     
     if($allenabled['enabled'] == 1) $xml .= product($article, false);

     //On inclus le produit dans l'export uniquement s'il rempli toutes les conditions
     $compteur++;
     $xml = $xml;
     fwrite($handle, $xml);
    
}
    
$xml = ""
     . "</products>"
. "</catalog>";

fwrite($handle, $xml);
fclose($handle);
chmod($filename,0777);

echo "Votre export de catalogue pour rue ducommerce a bien été mis à jour (produit exportés : ".$compteur.")<br>
Il est disponible à l'adresse suivante :<a href=\"http://$host/configurations/$host/exports/rueducommerce.xml\"><strong>http://$host/configurations/$host/exports/rueducommerce.xml</strong></a>" ;

if(isset($_POST['URL']) && $_POST['URL'] != ''){
	$date = strtotime(date("d-m-Y H:i"));
	$newfilename = "rueducommerce".$date.".xml";
	$dest = $rootpath."/admin/comparateur/log/RDC/$newfilename";
  if (!is_dir("/admin/comparateur/log/RDC")) {
      mkdir("/admin/comparateur/log/RDC",0777);
  }
	copy($filename, $dest);

	$date = date("d-m-Y H:i");
	$action = "Mise a jour des fichiers";
	$market_place = "rueducommerce";
	//mise à jour du suivit
	$sql = "INSERT INTO suivi_export SET ";
	$sql .= " user = '$_COOKIE[admin_username]'";
	$sql .= ", time = '$date'";
	$sql .= ", action = '$action'";
	$sql .= ", market_place = '$market_place'";
	$DB_site2->query($sql);

	$_POST['URL'] = $_POST['URL']."&update=1";
	header('Location: '.$_POST['URL']);
	exit;
}

function product($article,$coloris){
     global $DB_site2;
     global $host;
     global $template;
     global $enddate;
     global $tvafrance;
     global $fdpgratuit;
     global $tabLivraison;
     global $tabProduitsSoldes;

     $sql = "SELECT champ, valeur";
     $sql .= " FROM market_place_catid";
     $sql .= " WHERE market_place = 'rdc'";
     $sql .= " AND catid = '".$article['catid']."'";
     $requete=$DB_site2->query($sql);
     
     while($champ = $DB_site2->fetch_array($requete)){
          $mp_field[$champ['champ']] = $champ['valeur'];
     }

     $sql = "SELECT marqueid, libelle";
     $sql .= " FROM article_marque";
     $sql .= " INNER JOIN marque USING(marqueid)";
     $sql .= " WHERE artid = '".$article['artid']."'";
     $marque = $DB_site2->query_first($sql);
     
     if($marque['marqueid'] != 17){
          $sql = "SELECT *";
          $sql .= " FROM fraisport";
          $sql .= " WHERE poiddebut <= '".$article['poid']."'";
          $sql .= " AND poidfin >= '".$article['poid']."'";
          $sql .= " AND paysid = '57'";
          $fraisdeport = $DB_site2->query_first($sql);
     }
  
     $article_prices = article_prices($article['artid'],$tvafrance,"base",false,false,false);

     /*switch($article['artid']){
          case 1788: //ACI-AVI578E
               $article_prices['prix'] = 799;
               break;
          case 2244: //ACI-AVI580E
               $article_prices['prix'] = 1099;
               break;
          case 489: //ACI-CLI475
               $article_prices['prix'] = 45;
               break;
          case 2227: //ACI-CLI603-2
               $article_prices['prix'] = 699;
               break;
          case 3124: //ACI-CLI101
               $article_prices['prix'] = 47;
               break;
          case 870: //ACI-CLI421
               $article_prices['prix'] = 49;
               break;
          case 1643: //ACI-ADV209
               $article_prices['prix'] = 39;
               break;
          case 998: //ACI-ADV702
               $article_prices['prix'] = 35;
               break;
          case 983: //ACI-ADV414
               $article_prices['prix'] = 29;
               break;
          case 1069: //ACI-ADV400x3
               $article_prices['prix'] = 89;
               break;
          case 901: //ACI-ADV104CS
               $article_prices['prix'] = 25;
               break;
          case 3123: //ACI-CLI100
               $article_prices['prix'] = 22.5;
               break;
          case 1820: //ACI-ART220
               $article_prices['prix'] = 1360;
               break;
     }*/

     $prixtotal  = $article_prices['prix'];
     $deliveryid = 1;
     
     if($fdpgratuit>$article_prices['prix']){
          $deliveryid = fraisportid($article['poid']);
     }

     $sqlcode = "SELECT codebarre";
     $sqlcode .= " FROM code_barre";
     $sqlcode .= " WHERE artid = '".$article['artid']."'";
     if($coloris != false){
          $sqlcode .= " AND caractvalid = '".intval($coloris['caractvalid'])."'";
     }
     $codebarre = $DB_site2->query_first($sqlcode);                                          
            
     $sql = "SELECT libelle";
     $sql .= " FROM marque";
     $sql .= " INNER JOIN article_marque USING(marqueid)";
     $sql .= " WHERE artid = '".$article['artid']."'";
     $marque = $DB_site2->query_first($sql) ;

     // pour le wine_cellar_door
     $sql = "SELECT *";
     $sql .= " FROM articlerecherche_typeporte";
     $sql .= " WHERE artid = '".$article['artid']."' ";
     $articlerecherche_typeporte = $DB_site2->query_first($sql);
            
     $sql = "SELECT *";
     $sql .= " FROM recherche_typeporte";
     $sql .= " WHERE recherchetypeporteid = '".$articlerecherche_typeporte['recherchetypeporteid']."'";
     $recherche_typeporte = $DB_site2->query_first($sql);          
            
     $sql = "SELECT libelleFR";
     $sql .= " FROM article_caractval";
     $sql .= " INNER JOIN caracteristiquevaleur USING(caractvalid)";
     $sql .= " WHERE artid = ".$article['artid'];
     $sql .= " AND differenceprix = 0";
     $sql .= " AND caractvalid <> 9";
     $sql .= " AND caractid IN (3,22)";
     $sql .= " ORDER BY position";
     $garantie = $DB_site2->query_first($sql);

     $xml = ""
     . "<product id=\"".$codebarre[0]."\" isnew=\"N\" isexclu=\"N\">"
          . "<denomination><![CDATA[".html_entity_decode(mpfields($article,$mp_field['denomination']))."]]></denomination>"
          . "<subjectivedenomination><![CDATA[".  html_entity_decode(mpfields($article,$mp_field['subjectivedenomination']))."]]></subjectivedenomination>"
          . "<description><![CDATA[".html_entity_decode(mpfields($article,$mp_field['description'],'','255'))."]]></description>"
          . "<fulldescription><![CDATA[".html_entity_decode(mpfields($article,$mp_field['fulldescription'],'<br><br /><br/>'))."]]></fulldescription>"
          . "<url>http://".$host."/art,".$article['artid'].",".url_rewrite($article[libelleFR]).".htm</url>"
          . "<warranty>".$garantie['libelleFR']."</warranty>"
          . "<brand>".html_entity_decode($marque['libelle'],ENT_QUOTES)."</brand>"
          . "<partnumber>".$article['artcode']."</partnumber>";
  
     if($codebarre[0] != ""){
          $xml .= '<ean13>'.$codebarre[0].'</ean13>';
     }
            
     $classif1 = "Electrom&eacute;nager";
     $classif2 = "Froid";
     $classif3 = "Caves à vin";
        
     $xml .= ""
          . "<keywords>"
               . "<keyword>cave a vin</keyword>"
               . "<keyword>cave vin</keyword>"
               . "<keyword>cave à vins</keyword>"
               . "<keyword>cave vieillissement</keyword>"
               . "<keyword>".$article['artcode']."</keyword>"
               . "<keyword>".str_replace("&","",$article['artcode_fabricant'])."</keyword>"
          . "</keywords>"
          . "<categories>"
                    . "<category type=\"gender\">U</category>"
               . "</categories>"
               . "<deliveries>"
                    . "<delivery>".$deliveryid."</delivery>"
               . "</deliveries>"
     . "<references>";

     if($coloris['imagecaractvalid'] == "jpg"){
          $imageprincipale = "http://$host/configurations/$host/images/produits/article_caractval/$article[artid]_".$coloris[caractvalid].".$coloris[imagecaractvalid]";
     } elseif($article['image'] != ""){
          $imageprincipale = "http://$host/configurations/$host/images/produits/article/$article[artid].$article[image]";
     }
     
     if ($coloris['image'] == "1"){
          $image = "http://$host/configurations/$host/images/caracteristiquevaleur/zoom/$coloris[caractvalid].jpg";
     }elseif($article['image'] != ""){
          $image = $imageprincipale;
     }
                                
     if($article['catid'] == "7"){
          $installation = "Encastrable";
     } else {
          $installation = "Pose libre";   
     }
     
     if($article['largeur'] != "" && $article['hauteur'] != ""){
          $dimensions = $article['hauteur']." x ".$article['largeur'];
     } else {
          $dimensions = "NC";     
     }
     
     $poids = $article['poid'] / 1000; 
     
     //Récupération des informations "commandable" et "activé sur rdc" sur le produit dans notre base
     $sql = "SELECT a.commandable, a.date_appro, mpc.enabled";
     $sql .= " FROM article a, market_place_champs mpc";
     $sql .= " WHERE a.artcode = mpc.artcode";
     $sql .= " AND mpc.market_place = 'rdc'";
     $sql .= " AND a.artid = ".intval($article['artid']);
     $tabInfos = $DB_site2->query_first($sql);

     //Récupération des informations "activé sur MCAV" sur le produit dans notre base
     $sql = "SELECT active";
     $sql .= " FROM article_site";
     $sql .= " WHERE site_code = 'base'";
     $sql .= " AND artid = ".intval($article['artid']);
     $tabActive = $DB_site2->query_first($sql);

     $tabLivraison = delailivraison($article['artid']);

     if($tabLivraison['delailivraison'] >= 7){
          $expedition_delay = $tabLivraison['delailivraison'] - 2;
     } else {
          $expedition_delay = $tabLivraison['delailivraison'] - 1;
     }

     if(empty($tabLivraison['date_appro']) && $tabInfos['date_appro'] > 0) $tabLivraison['date_appro'] = $tabInfos['date_appro'];
     
     if($tabActive['active'] != 1 || $tabInfos['commandable'] != 1 || $tabInfos['enabled'] != 1 || (!empty($tabLivraison['date_appro']) && mktime(0,0,0,date('m'),date('d')+7,date('Y')) < $tabLivraison['date_appro'])){
          $quantity = 0;
     } else {
          $quantity = 50;
     }
     
     $xml .= ""
     . "<reference currency=\"EUR\" id=\"".$codebarre[0]."\" available=\"Y\" ecotax=\"".$article['ecopart']."\" quantity=\"".$quantity."\">"
          . "<ean13>".$codebarre[0]."</ean13>"
          . "<availability code=\"S\" />"
          . "<expeditiondelay delay=\"".$expedition_delay."\" unit=\"D\" />";

     $colortxt = $template->translate($article['coloris']);
     
     if($coloris!=false){
          $colortxt = $coloris['libelleFR'];
     }
                                   
     switch(strtolower($mp_field['mcid'])){
          case "mc-2000":
               $xml .= ""
               . "<characteristics>"
                    . "<characteristic name=\"wine_cellar_capacity\">".html_entity_decode($article[capacite_bouteilles],ENT_QUOTES)."</characteristic>"
                    . "<characteristic name=\"color\">".html_entity_decode($colortxt,ENT_QUOTES)."</characteristic>"
                    . "<characteristic name=\"wine_cellar_door\">".html_entity_decode($recherche_typeporte[nom], ENT_QUOTES)."</characteristic>"
                    . "<characteristic name=\"installation\">".mpfields($article,$mp_field['installation'])."</characteristic>"
                    . "<characteristic name=\"wine_cellar_type\">".mpfields($article,$mp_field['wine_cellar_type'])."</characteristic>"
               . "</characteristics>";
               
               break;
          case "mc-0142":
               $xml .= ""
               . "<characteristics>"
                    . "<characteristic name=\"color\">".html_entity_decode($colortxt,ENT_QUOTES)."</characteristic>"
                    . "<characteristic name=\"refrigerator_type\">".mpfields($article,$mp_field['refrigerator_type'])."</characteristic>"
                    . "<characteristic name=\"installation\">".mpfields($article,$mp_field['installation'])."</characteristic>"
                    . "<characteristic name=\"total_net_capacity\">".$article['capacite_l']."</characteristic>"
                    . "<characteristic name=\"energy_label\">".$article['energy_label']."</characteristic>"
                    . "<characteristic name=\"freezer_pos\">".mpfields($article,$mp_field['freezer_pos'])."</characteristic>"
                    . "<characteristic name=\"has_water_fountain\">".mpfields($article,$mp_field['has_water_fountain'])."</characteristic>"
                    . "<characteristic name=\"has_auto_defrost\">".mpfields($article,$mp_field['has_auto_defrost'])."</characteristic>"
               . "</characteristics>";
               
               break;
          case "mc-3499":
               $xml .= ""
               . "<characteristics>"
                    . "<characteristic name=\"cooling_power\">".$article['conso_kw']."</characteristic>"
                    . "<characteristic name=\"noise\">".$article['niveau_bruit']."</characteristic>"
                    . "<characteristic name=\"installation_type\">".mpfields($article,$mp_field['installation_type'])."</characteristic>"
                    . "<characteristic name=\"is_reverse_cycle_air_conditioner\">".mpfields($article,$mp_field['is_reverse_cycle_air_conditioner'])."</characteristic>"
                    . "<characteristic name=\"energy_label\">".$article['energy_label']."</characteristic>"
               . "</characteristics>";
               
               break;
          case "mc-5423":
               $xml .= ""
               . "<characteristics>"
                    . "<characteristic name=\"basket_type\"></characteristic>"
                    . "<characteristic name=\"color\">".$colortxt."</characteristic>"
                    . "<characteristic name=\"width\">".$article['largeur']."</characteristic>"
                    . "<characteristic name=\"flow\"></characteristic>"
                    . "<characteristic name=\"warranty_electro\"></characteristic>"
               . "</characteristics>";
               
	break;
     }

     $files = "";
     /*$images=$DB_site2->query("SELECT * FROM articlephoto WHERE artid=".$article['artid']." ORDER BY position LIMIT 3");
     while($im=$DB_site2->fetch_array($images)){
         $files.="<file type=\"image/jpeg\" use=\"zoom\">http://$host/configurations/$host/images/produits/".$article[artid]."_".$im[articlephotoid].".".$im[image]."</file>\n";
     }*/

     $xml .= ""
     . " <files>"
          . "<file type=\"image/jpeg\" use=\"main view\">".$imageprincipale."</file>".$files
          . "<file type=\"image/jpeg\" use=\"front view\">".$image."</file>"
     . "</files>";
     
     if(time() > mktime(0,0,0,1,6,2016) && time() < mktime(0,0,0,2,17,2016) && in_array($article['artid'], $tabProduitsSoldes)){

          //Si période de solde, on récupère le prix soldé
          $article_prices_soldes = article_prices($article['artid'],$tvafrance,"base",false,false);
          $remise                = 100 - (($article_prices_soldes['prix'] * 100) / $article_prices['prix']);
          $differencepromo       = $article_prices['prix'] - $article_prices_soldes['prix'];
          
          $pctpromo        = number_format($remise,2,'.','');
          $prix_base       = number_format($article_prices['prix'],2,'.','');
          $differencepromo = number_format($differencepromo,2,'.','');
          $prixtotal       = number_format($article_prices_soldes['prix'],2,'.','');

          $xml .= ""
          . "<price>".$prix_base."</price>"
          . "<discount startdate=\"2016-01-01 08:00:00\" enddate=\"".$enddate."\" previousprice=\"".$prix_base."\" amount=\"".$differencepromo."\" percents=\"".$pctpromo."\">".$prixtotal."</discount>";

     } else if(round($article_prices['prixconseille']) > round($article_prices['prix'])){
          
          $differencepromo = $article_prices['prixconseille']-$article_prices['prix'];
          $pctpromo        = number_format($article_prices['remise'],2,'.','');
          $prixconseille   = number_format($article_prices['prixconseille'],2,'.','');
          $differencepromo = number_format($differencepromo,2,'.','');
          $prixtotal       = number_format($prixtotal,2,'.','');
	
          $xml .= ""
          . "<price>".$prixconseille."</price>"
          . "<discount startdate=\"2016-01-11 08:00:00\" enddate=\"".$enddate."\" previousprice=\"".$prixconseille."\" amount=\"".$differencepromo."\" percents=\"".$pctpromo."\">".$prixtotal."</discount>";
          
          
     } else {
          
          $prixtotal = number_format($prixtotal,2,'.','');
          
          $xml .= "<price>".$prixtotal."</price>";
          
     
     }
                                    
  $xml .= '
	<deliveries>
	  <delivery>'.$deliveryid.'</delivery>
	</deliveries>
	</reference>';
                                
    $xml .= '</references>
		<preferences>
		    <mcategories>
		      <mcid priority="1" internal="Y">'.strtoupper($mp_field['mcid']).'</mcid>
		    </mcategories>
		</preferences>
    </product>';

  return $xml;

}

function insert_mp($artcode,$champ,$valeur,$limit=50){
  global $DB_site2;
  $requete=$DB_site2->query("SELECT id FROM market_place_champs WHERE market_place='rdc' AND artcode='$artcode' AND champ='$champ'");
  if($DB_site2->num_rows($requete)==0)
    $DB_site2->query("INSERT INTO market_place_champs SET market_place='rdc', artcode='$artcode',champ='$champ',valeur='".addslashes($valeur)."',limite=".$limit);
}

function fraisportid($poids){
  global $fraisport;
  foreach($fraisport as $fraisportid=>$limite){
      if($poids>=$limite[0] && $poids<$limite[1]) return $fraisportid;
  }
}


?>