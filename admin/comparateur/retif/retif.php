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
require_once($rootpath."class/fonctions.php");
require_once($rootpath."class/email.php");
require 'vendor/autoload.php';

$template=new template("FR",$DB_site2);
$DB_site = clone $DB_site2;

use Mirakl\MMP\Shop\Client\ShopApiClient as Client;
use Mirakl\MCI\Shop\Request\Hierarchy\GetHierarchiesRequest;

$apiUrl = 'https://marketplace.retif.eu/api/offers/imports'; 
$apiKey = RETIF_API_KEY;

$xml = ""
    . "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n"
    . "<import>\n";


//couleurs autorisées par retif
$couleurs_retif = array("ACAJOU","ACIER","ALUMINIUM","ANIS","ANTHRACITE","ARGENT","ARGENTE","BAMBOU","BEIGE","BETON","BLANC","BLANC","DECAPE","BLANCMAT","BLEU","BOIS","BOIS BLANCHI","BORDEAUX","BRONZE","CHENE","CHENE BLANCHI","CHENE CERUSE BLANC","CHOCOLAT","CHROME","CREME","CROCO BLANC","CROCO NOIR","ERABLE","FUCHSIA","GRIS","GRIS ALU","HETRE","IVOIRE","JAUNE","MAGNOLIA","MANGUIER","MARINE","MARRON","MIEL","MIROIR","MULTICOLORE","NATUREL","NOIR","NOYER","OR","ORANGE","ORME","OSB","PAON","PIN","POIRIER","ROSE","ROUGE","ROUILLE","SABLE","TABAC","TAUPE","TOILE BRONZE","TOILE ECRU","TOILE GRISE","TRANSPARENT","TURQUOISE","VERT","VERT POMME","VERTEAU","VIOLET","WENGE","CHENE AUTHENTIQUE","BLANC CENDRE");

//matieres autorisées par retif
$matieres_retif = array("ABS","ACIER","ACRYLIQUE","AGGLOMERE","ALUMINIUM","BOIS","BOIS MELAMINE","CAOUTCHOUC","CERAMIQUE","CONTREPLAQUE","COTON","CUIR","FER FORGE","FIBRE DE VERRE","FIL ACIER","FILMPLASTIQUE","GRANIT","INOX","LAITON","MDF","METACRILATE","METAL","MOUSSE","NYLON","OSIER","PANNEAU ALVEOLAIRE","PAPIER","PET","PEVA","PIN MASSIF","PLASTIQUE","PLASTIQUE CHROME","PLEXIGLASS","POLYCARBONATE","POLYESTER","POLYETHYLENE","POLYPROPYLENE","POLYSTRETCH","POLYSTYRENE","POLYSTYRENE INJECTE","POLYURETHANE","POLYWEAVE","PVC","RESINE","SIMILI CUIR","STRATIFIE","TISSU","VERRE","VERRE TREMPE","VERRESECU","ZAMAK","ZINC");

//gaz autorisés par retif
$gaz_retif = array("R134A","R404A","R407F","R2390","R452A","R448A","R600A","R449A","R1234YF");

$sql = "SELECT a.*, a.libelleFR libart, c.libelleFR libcat, mpc.enabled";
$sql .= " FROM market_place_champs mpc";
$sql .= " INNER JOIN article a ON (a.artcode = mpc.artcode)";
$sql .= " INNER JOIN categorie c ON (c.catid = a.catid)";
$sql .= " WHERE 1";
$sql .= " AND mpc.market_place = 'retif'";
$sql .= " AND mpc.enabled = 1";
$sql .= " GROUP BY a.artid";
$sql .= " ORDER BY a.catid, a.artcode";
$articles = $DB_site2->query($sql);

$xml .= ""
        . "<products>\n";

while ($article=$DB_site2->fetch_array($articles)) {
$sql = "SELECT champ, valeur";
     $sql .= " FROM market_place_catid";
     $sql .= " WHERE market_place = 'retif'";
     $sql .= " AND catid = '".$article['catid']."'";
     $requete = $DB_site2->query($sql);
     while($champ = $DB_site2->fetch_array($requete)){
          $mp_field[$champ['champ']] = mpfields($article, $champ['valeur'],'<br/><br><br />');
     }

$xml .= ""
          . "<product>\n";         


// PRODUCT_CATEGORY
          $arr_caves = array('1','4','9','10','11','12');
          if(in_array($article['typearticleid'],$arr_caves)){
               $cat_produit = "AGENCER/AGENCEMENT/ARMOIRE ALIMENTAIRE/CAVE A VIN";
          }
          elseif($article['typearticleid'] == '3'){
               $cat_produit = "AGENCER/EQUIPEMENT DE CUISINE/MATÉRIEL DE CONSERVATION/CONTAINER ISOTHERME";
          }
          elseif($article['typearticleid'] == '5'){
               $cat_produit = "AGENCER/EQUIPEMENT HÔTELLERIE/EQUIPEMENT CHAMBRE/MINI-BAR, MINI-RÉFRÉGÉRATEUR";
          }
          elseif($article['typearticleid'] == '2'){
               $cat_produit = "ANIMER/MATÉRIEL DE CUISINE/USTENSILE DE CUISINE/PLANCHE À DÉCOUPER";
          }
          elseif($article['typearticleid'] == '8'){
               $cat_produit = "ANIMER/MERCHANDISING VISUEL/PETIT MOBILIER DE DECORATION/PORTE-BOUTEILLE";
          }
          else{
               $cat_produit = "AGENCER/AGENCEMENT/ARMOIRE ALIMENTAIRE/CAVE A VIN";
          }
    $xml .= ""
                    . "<attribute>\n"
                         . "<code>CATEGORIE</code>\n"
                    . "<value>".$cat_produit."</value>\n"
               . "</attribute>\n"

// ARTSHOP
               . "<attribute>\n"
                         . "<code>REF PRODUIT</code>\n"
                    . "<value>".$article['artcode']."</value>\n"
               . "</attribute>\n";

// DESICAT
// PRODUCT_TITLE (Marque - Type de produit - RÃ©fÃ©rence - CaractÃ©ristique principale - Couleur - Genre)
          $sql = "SELECT libelle";
          $sql .= " FROM marque";
          $sql .= " INNER JOIN article_marque USING(marqueid)";
          $sql .= " WHERE artid = '".$article['artid']."'";
          $marque = $DB_site2->query_first($sql);
          $libellecat = trim($article['libcat']);
          $gamme = $article['gammeFR'];
          $couleur = $template->translate($article['coloris']);

          $title = $marque['libelle'] . " - " . $article['libelleFR']; 
          $title = (strlen($title.($couleur!=''?' - '.$couleur:''))>80?$title:$title.($couleur!=''?' - '.$couleur:''));
          if(strlen($title) > 80){
               $title = substr($title,0,75).'...';
          }
          $xml .= ""
          . "<attribute>\n"
          . "<code>TITRE</code>\n"
          . "<value>".utf8_encode($title)."</value>\n"
          . "</attribute>\n"


// UNITEVTE
               . "<attribute>\n"
                    . "<code>UNITEVTE</code>\n"
                    . "<value/>\n"
               . "</attribute>\n";


// DESCRIPTION COURTE
               $sql = "SELECT libelleFR";
               $sql .= " FROM pays";
               $sql .= " WHERE paysid = ".$article['paysid'];
               $pays = $DB_site2->query_first($sql);

               $Description = str_replace('<br>', '&#13;', $article['descriptionFR']);
               $Description = str_replace('<p>', '&#13;', $Description);
               $Description = str_replace('</p>', '', $Description);
               $Description = str_replace("&","&amp;",$Description);
               $Description = html_entity_decode(strip_tags($Description));

               $xml .= ""
               . "<attribute>\n"
                    . "<code>DESCRIPTION COURTE</code>\n"
                    . "<value><![CDATA[".(strlen($Description)>500?substr($Description,0,455).'...':substr($Description,0,500))."]]></value>\n"
               . "</attribute>\n";

// DESCRIPTION LONGUE
               $Description = str_replace('<br>', '&#13;', $article['descriptionFR']);
               $Description = str_replace('<p>', '&#13;', $Description);
               $Description = str_replace('</p>', '', $Description);
               $Description = str_replace("&","&amp;",$Description);
               $Description = html_entity_decode(strip_tags($Description));


               $xml .= ""
               . "<attribute>\n"
                    . "<code>DESCRIPTION LONGUE</code>\n"
                    . "<value><![CDATA[".(strlen($Description)>2000?substr($Description,0,1500).'...':substr($Description,0,2000))."]]></value>\n"
               . "</attribute>\n";


// CODE TVA
               $req_article_site=$DB_site2->query_first("SELECT * FROM article_site WHERE artid='".$article['artid']."'");
               $tauxchoisi=$req_article_site['tauxchoisi'];
               $taux=$DB_site2->query_first("SELECT 0,TVAtauxnormal,TVAtauxreduit FROM pays WHERE paysid='57'");
               $tvapc=$taux[$tauxchoisi];
               if($tvapc == 0){
                    $tva = 0;
               }
               elseif($tvapc == 20) {
                    $tva = 1;
               }
               elseif($tvapc == 5.5) {
                    $tva = 3;
               }
     $xml .= ""
               . "<attribute>\n"
                    . "<code>CODE TVA</code>\n"
                    . "<value>".$tva."</value>\n"
               . "</attribute>\n"



// REFOU
               . "<attribute>\n"
                    . "<code>REF FOURNISSEUR</code>\n"
                    . "<value/>\n"
               . "</attribute>\n"


// TYPECOND
               . "<attribute>\n"
                    . "<code>TYPE DE CONDITIONNEMENT</code>\n"
                    . "<value>UNITE</value>\n"
               . "</attribute>\n"


    // LARG
               . "<attribute>\n"
                    . "<code>LARGEUR DEPLOYEE (CM)</code>\n"
                    . "<value>".($article['largeur'] / 10)."</value>\n"
               . "</attribute>\n"

    // PROF
               . "<attribute>\n"
                    . "<code>PROFONDEUR DEPLOYEE (CM)</code>\n"
                    . "<value>".($article['profondeur'] / 10)."</value>\n"
               . "</attribute>\n"

    // HAUT
               . "<attribute>\n"
                    . "<code>HAUTEUR DEPLOYEE (CM)</code>\n"
                    . "<value>".($article['hauteur'] / 10)."</value>\n"
               . "</attribute>\n"

    // POIDSN
               . "<attribute>\n"
                    . "<code>POIDS NET (KG)</code>\n"
                    . "<value>".($article['poid'] / 1000)."</value>\n"
               . "</attribute>\n";

     // EAN13
     $sql = "SELECT codebarre";
     $sql .= " FROM code_barre";
     $sql .= " WHERE artid = '".$article['artid']."'";
     $ean = $DB_site2->query_first($sql);
     $xml .= ""
               . "<attribute>\n"
                    . "<code>EAN13</code>\n"
                    . "<value>".$ean['codebarre']."</value>\n"
               . "</attribute>\n"

    // TEINTE
               . "<attribute>\n"
                    . "<code>TEINTE</code>\n"
                    . "<value/>\n"
               . "</attribute>\n"

    // FINITION
               . "<attribute>\n"
                    . "<code>FINITION</code>\n"
                    . "<value/>\n"
               . "</attribute>\n"

    // MARQUE
               . "<attribute>\n"
                    . "<code>MARQUE</code>\n"
                    . "<value/>\n"
               . "</attribute>\n"


    // CODSERIE
               . "<attribute>\n"
                    . "<code>GAMME</code>\n"
                    . "<value>".$article['gammeFR']."</value>\n"
               . "</attribute>\n"

    // GAMMEPRIX
               . "<attribute>\n"
                    . "<code>GAMMEPRIX</code>\n"
                    . "<value/>\n"
               . "</attribute>\n";

    // ACTIVITE
     for($i=1;$i<11;$i++) {
          $xml .= ""
               . "<attribute>\n"
                    . "<code>ACTIVITE".$i."</code>\n"
                    . "<value/>\n"
               . "</attribute>\n";
     }
               

     // COULEURP
     if(in_array($template->translate($article['coloris']), $couleurs_retif)) {
          $xml .= ""
               . "<attribute>\n"
                    . "<code>COULEURP</code>\n"
                    . "<value>".$template->translate($article['coloris'])."</value>\n"
               . "</attribute>\n";
     }
     else {
          $xml .= ""
               . "<attribute>\n"
                    . "<code>COULEUR PRIMAIRE</code>\n"
                    . "<value/>\n"
               . "</attribute>\n";
     }


     // COULEURS
     $xml .= ""
               . "<attribute>\n"
                    . "<code>COULEUR SECONDAIRE</code>\n"
                    . "<value/>\n"
               . "</attribute>\n"

     // EPPRINCIP
               . "<attribute>\n"
                    . "<code>EPPRINCIP</code>\n"
                    . "<value/>\n"
               . "</attribute>\n";


     // MATIEREP
     if(in_array($template->translate($article['matiere']), $matieres_retif)) {
          $xml .= ""
               . "<attribute>\n"
                    . "<code>MATIERE PRIMAIRE</code>\n"
                    . "<value>".$template->translate($article['matiere'])."</value>\n"
               . "</attribute>\n";
     }
     else {
          $xml .= ""
               . "<attribute>\n"
                    . "<code>MATIERE PRIMAIRE</code>\n"
                    . "<value/>\n"
               . "</attribute>\n";
     }

     // MATIERES
     $xml .= ""
               . "<attribute>\n"
                    . "<code>MATIERE SECONDAIRE</code>\n"
                    . "<value/>\n"
               . "</attribute>\n";


     // MADEFRANCE
     $sql = "SELECT libelleFR";
     $sql .= " FROM pays";
     $sql .= " WHERE paysid = ".$article['paysid'];
     $pays = $DB_site2->query_first($sql);
     if($pays['libelleFR']=='France') {
          $madeinfrance = "OUI";
     }
     else{
          $madeinfrance = "NON";
     }
     $xml .= ""
               . "<attribute>\n"
                    . "<code>MADE IN FRANCE</code>\n"
                    . "<value>".$madeinfrance."</value>\n"
               . "</attribute>\n";

     // GARANT
     $xml .= ""
               . "<attribute>\n"
                    . "<code>GARANTIE</code>\n"
                    . "<value/>\n"
               . "</attribute>\n";


     /*// NORMEFSC
               . "<attribute>\n"
                    . "<code>NORMEFSC</code>\n"
                    . "<value/>\n"
               . "</attribute>\n"


     // NORMEPEFC
               . "<attribute>\n"
                    . "<code>NORMEPEFC</code>\n"
                    . "<value/>\n"
               . "</attribute>\n"

     // COMPBACGN
               . "<attribute>\n"
                    . "<code>COMPBACGN</code>\n"
                    . "<value/>\n"
               . "</attribute>\n"

     // CONTENL
               . "<attribute>\n"
                    . "<code>CONTENL</code>\n"
                    . "<value/>\n"
               . "</attribute>\n"

     // DEGIVAUTO
               . "<attribute>\n"
                    . "<code>DEGIVAUTO</code>\n"
                    . "<value/>\n"
               . "</attribute>\n";*/

     // GAZREFRIG
          if (in_array($template->translate($article['gaz']), $gaz_retif)) {
               $gaz = $template->translate($article['gaz']);
          }
          else {
               $gaz = "";
          }
          $xml .= ""
               . "<attribute>\n"
                    . "<code>GAZ REFRIGERANT</code>\n"
                    . "<value>".$gaz."</value>\n"
               . "</attribute>\n"

/*
     // NBCOMP
               . "<attribute>\n"
                    . "<code>NBCOMP</code>\n"
                    . "<value/>\n"
               . "</attribute>\n"

     // PIEDREGL
               . "<attribute>\n"
                    . "<code>PIEDREGL</code>\n"
                    . "<value/>\n"
               . "</attribute>\n"

     // PUISSELEC
               . "<attribute>\n"
                    . "<code>PUISSELEC</code>\n"
                    . "<value/>\n"
               . "</attribute>\n"
*/

     // TEMPMAX
               . "<attribute>\n"
                    . "<code>TEMPERATURE MIN-MAX</code>\n"
                    . "<value>".$template->translate($article['plage_temperature'])."</value>\n"
               . "</attribute>\n"

     // THERMOSTAT
               . "<attribute>\n"
                    . "<code>THERMOSTAT</code>\n"
                    . "<value/>\n"
               . "</attribute>\n"

    /* // WATT
               . "<attribute>\n"
                    . "<code>WATT</code>\n"
                    . "<value/>\n"
               . "</attribute>\n"

     // TYPARMOIRE
               . "<attribute>\n"
                    . "<code>TYPARMOIRE</code>\n"
                    . "<value/>\n"
               . "</attribute>\n"


     // TYPTEMPERA
               . "<attribute>\n"
                    . "<code>TYPTEMPERA</code>\n"
                    . "<value/>\n"
               . "</attribute>\n"*/
          . "</product>\n";
     }
     $xml .= ""
        . "</products>\n"
        . "<offers>\n";

        $sql = "SELECT a.*, a.libelleFR libart, c.libelleFR libcat, mpc.enabled";
        $sql .= " FROM market_place_champs mpc";
        $sql .= " INNER JOIN article a ON (a.artcode = mpc.artcode)";
        $sql .= " INNER JOIN categorie c ON (c.catid = a.catid)";
        $sql .= " WHERE 1";
        $sql .= " AND mpc.market_place = 'retif'";
        $sql .= " AND mpc.enabled = 1";
        $sql .= " GROUP BY a.artid";
        $sql .= " ORDER BY a.catid, a.artcode";
        $articles = $DB_site2->query($sql);

     while ($article=$DB_site2->fetch_array($articles)) {
$xml .= ""
          . "<offer>\n";

     // sku
          $xml .= ""
               . "<sku>".$article['artcode']."</sku>\n";

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
          $Description = str_replace('<br>', '&#13;', $article['descriptionFR']);
          $Description = str_replace('<p>', '&#13;', $Description);
          $Description = str_replace('</p>', '', $Description);
          $Description = str_replace("&","&amp;",$Description);
          $Description = html_entity_decode(strip_tags($Description));

          $xml .= ""
                    . "<description><![CDATA[".(strlen($Description)>2000?substr($Description,0,1700).'...':substr($Description,0,2000))."]]></description>\n";

    // internal-description
    $xml .= ""
                    . "<internal-description></internal-description>\n";

     // price
                    $xml .= ""
                    . "<price>".$article['prix']."</price>\n";

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

     // logistic-class
     $xml .= ""
                    . "<logistic-class></logistic-class>\n";

     // discount-price
     $xml .= ""
                    . "<discount-price></discount-price>\n";

     // discount-start-date
     $xml .= ""
                    . "<discount-start-date></discount-start-date>\n";

     // discount-end-date
     $xml .= ""
                    . "<discount-end-date></discount-end-date>\n";


     // update-delete
     $xml .= ""
                    . "<update-delete></update-delete>\n";

     $xml .= ""
    // ecotax
                    . "<offer-additional-fields>\n"
                         . "<offer-additional-field>\n"
                              . "<code>weee</code>\n"
                              . "<value>".$article['ecopart']."</value>\n"
                         . "</offer-additional-field>\n"
     // shipping-country
                         . "<offer-additional-field>\n"
                              . "<code>deliverytime</code>\n"
                              . "<value/>\n"
                         . "</offer-additional-field>\n";
     // deliverytimeday
          /*$sql = "SELECT a.commandable, a.date_appro, mpc.enabled";
          $sql .= " FROM article a, market_place_champs mpc";
          $sql .= " WHERE a.artcode = mpc.artcode";
          $sql .= " AND mpc.market_place = 'retif'";
          $sql .= " AND a.artid = ".intval($article['artid']);
          $tabInfos = $DB_site2->query_first($sql);

          $tabLivraison = delailivraison($article['artid']);

          if(empty($tabLivraison['date_appro']) && $tabInfos['date_appro'] > 0) $tabLivraison['date_appro'] = $tabInfos['date_appro'];
            
            $tabLivraison = delailivraison($article['artid']);
            if(empty($tabLivraison['date_appro']) && $article['date_appro'] > 0) $tabLivraison['date_appro'] = $article['date_appro'];
                        
            $delailivraison = $tabLivraison['delailivraison'];
            if($delailivraison >= 15){
                    $delailivraison = $delailivraison - 4; //Délais supérieurs à 15j, on indique le nombre réel - 4 jours
            } else {
                    $delailivraison = $delailivraison - 2;
            }*/
          if($article['delailivraison'] < 3) {
               $delai='3';
          }
          else {
               $delai=$article['delailivraison'];
          }
     $xml .= ""
                         . "<offer-additional-field>\n"
                              . "<code>deliverytimeday</code>\n"
                              . "<value>".$delai."</value>\n"
                         . "</offer-additional-field>\n"
                    . "</offer-additional-fields>\n";


     $xml .= ""
               . "</offer>\n";
}
$xml .= ""
          . "</offers>\n"
     . "</import>";


/*$ch = curl_init();
     try {
          $headers = array(
     "Authorization: ".$apiKey

);
          curl_setopt($ch, CURLOPT_URL, $apiUrl);
          curl_setopt($ch, CURLOPT_HEADER, $headers);
          curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
          curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);   
          curl_setopt($ch, CURLOPT_TIMEOUT, 5);         
          curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
          curl_setopt($ch, CURLOPT_MAXREDIRS, 1);
          curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
          
          $response = curl_exec($ch);
          
         if (curl_errno($ch)) {
               echo curl_error($ch);
               die();
          }
          
          $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
          if($http_code == intval(200)){
               echo $response;
          }
          else{
               echo "Ressource introuvable : " . $http_code;
          }
     } catch (\Throwable $th) {
          throw $th;
     } finally {
          curl_close($ch);
     }
*/

     $filename = $rootpath."configurations/".$host."/exports/retif.xml";
     $handle = fopen($filename, 'w+') ;

     fwrite($handle, $xml);

     fclose($handle);
     header('Content-Description: File Transfer'); 
     header('Content-Type: text/xml'); 
     header('Content-Disposition: attachment; filename=retif.xml'); 
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
     $cfile = new CURLFile($filename,'text/xml','retif');

// Assign POST data
     $data = array('file' => $cfile, 'import_mode' => 'REPLACE','with_products' => 'true');
     curl_setopt($curl, CURLOPT_POST,1);
     curl_setopt($curl, CURLOPT_POSTFIELDS, $data);


     $resp = curl_exec($curl);

     curl_close($curl);

//var_dump($resp);
?>