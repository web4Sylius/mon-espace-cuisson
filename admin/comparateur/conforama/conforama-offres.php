<?php
ini_set("max_execution_time",300);
$prov = "comp" ;

header('Content-Type: text/html; charset=utf-8');

$nostat    = 1;
include_once("host_config_admin.php");
require_once($rootpath."class/template.class.php");
require_once($rootpath."admin/mpfields.php");
require_once($rootpath."class/fonctions.php");
require_once($rootpath."class/devises.class.php");
require_once($rootpath."class/stock.class.php");
require_once($rootpath."class/garantie.class.php");
require_once($rootpath."class/DAOgarantie.class.php");
require_once($rootpath."class/DAOarticle.class.php");
require_once($rootpath."class/article.class.php");
require_once($rootpath."class/email.php");

ini_set("display_errors",1);

$libelle     = "libelleFR";
$description = "descriptionFR";

$today = mktime(0,0,0)+86400;

$template = new template("FR",$DB_site2);

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

function nicexml($var){
     $var = str_replace('<![CDATA[','',$var);
     $var = str_replace(']]','',$var); nicex($var);
     $parser = simplexml_load_string($var);

     nicex($parser);
}

//Fonction pour CREATION OFFRES
function offer($article_id, $replace = false){
    global $DB_site2;
    global $tvafrance;
    global $template;
    //echo "hello world ( ". $article_id ." )!!!";
    $objArticle = new produit($DB_site2, 'FR', 'base', $template->tva_ref(), $article_id);
    //echo "yop ?!!!";
    //On recherche si le produit est toujours activé sur la marketplace
    $sql = "SELECT enabled";
    $sql .= " FROM market_place_champs";
    $sql .= " WHERE artcode = '".$objArticle->ref_aci."'";
    $sql .= " AND market_place = 'conforama'";
    $enabled = $DB_site2->query_first($sql)[0];

    //Délais de livraison
    $tabLivraison = delailivraison($objArticle->id);
    $delai_livraison = $tabLivraison['delailivraison'];

    if($objArticle->active != 1 || $objArticle->commandable != 1 || $enabled != 1 || $delai_livraison > 8 || (!empty($tabLivraison['date_appro']) && mktime(0,0,0,date('m'),date('d')+7,date('Y')) < $tabLivraison['date_appro'])){
        $quantite_article = 0;
    } else {
        $quantite_article = 50;
    }

    $quantite_alerte = 2;
    $etat_offre = 11;
	 
    /* classe logistique */
    $classe_logistique = "";
    if($objArticle->poid<20000){
            $classe_logistique = "G";
    }
    if($objArticle->poid>=20000 && $objArticle->poid<50000){
            $classe_logistique = "H";
    }
    if($objArticle->poid>=50000 && $objArticle->poid<100000){
            $classe_logistique = "I";
    }
    if($objArticle->poid>=100000){
            $classe_logistique = "J";
    }
    /* fin classe logistique */
    
    /* delai expedition */
    $leadtimetoship=$objArticle->delailivraison;
    
    /* fin delai expedition */
    
    $discount_price=$discount_start=$discount_end="";
    if($objArticle->id==4818){
        $discount_price=224.99;
        $discount_start="2017-11-20";
        $discount_end="2017-11-27";
    }
	if($objArticle->id==3079){
        $discount_price=295;
        $discount_start="2017-11-24";
        $discount_end="2017-11-27";
    }
    if($objArticle->id==4949){
        $discount_price=329;
        $discount_start="2018-09-24";
        $discount_end="2018-10-22";
    }

    //Informations de base du fichier offre
    $xml .= ""
    . "<offer>"
        . "<sku>".$objArticle->id."</sku>"
        . "<product-id>".$objArticle->code_barre."</product-id>"
        . "<product-id-type>EAN</product-id-type>"
                . "<price>".$objArticle->prix_ttc."</price>"
                . "<price-additional-info></price-additional-info>"
                . "<quantity>".$quantite_article."</quantity>"
        . "<description></description>"
        . "<internal-description></internal-description>"
        . "<state>".$etat_offre."</state>"
        . "<min-quantity-alert>".$quantite_alerte."</min-quantity-alert>"
        . "<available-start-date></available-start-date>"
        . "<available-end-date></available-end-date>"
                . (!$replace ? "<update-delete>UPDATE</update-delete>" : "") 
                // . "<update-delete>UPDATE</update-delete>"
                . "<logistic-class>".$classe_logistique."</logistic-class>"
        . "<favorite-rank>1</favorite-rank>"
                . "<price-ranges></price-ranges>"
        . "<discount-price>".$discount_price."</discount-price>"
        . "<discount-start-date>".$discount_start."</discount-start-date>"
        . "<discount-end-date>".$discount_end."</discount-end-date>"
                . "<discount-ranges></discount-ranges>"
                . "<leadtime-to-ship>".$leadtimetoship."</leadtime-to-ship>"
                . "<allow-quote-requests>false</allow-quote-requests>"
                . "<product-tax-code></product-tax-code>"
                . "<offer-additional-fields><offer-additional-field><code>ecopart</code><value>".$objArticle->ecopart."</value></offer-additional-field>"
                . "<offer-additional-field><code>garantie</code><value>2 ans pièces, main d'oeuvre et déplacement en France</value></offer-additional-field></offer-additional-fields>"
                . "<all-prices></all-prices>"

                
            /*  . "<garantie>2 ans pièces, main d'oeuvre et déplacement en France.</garantie>" */
    . "</offer>";
    
    return $xml;
}

function getCloserCapacity($capacite){
     global $aCapacite;

     foreach($aCapacite as $capacite_infos){

          if($capacite >= $capacite_infos['min'] && $capacite <= $capacite_infos['max']){
              return $capacite_infos['libelle'];
          }
     }
}

function sendInfos($methode, $postfields = array()){
    
    $oCurl = curl_init();      

    $typeMaj = 
    curl_setopt( $oCurl, CURLOPT_URL, 'https://conforama-prod.mirakl.net/api/'.$methode );

    //Authentification
    curl_setopt( $oCurl, CURLOPT_HTTPHEADER, array("Authorization: 2ed2c8dd-29ef-4cbb-9ca1-a1a4b65addd3") );

    // Set so curl_exec returns the result instead of outputting it.
    curl_setopt( $oCurl, CURLOPT_RETURNTRANSFER, true );

    if(count($postfields) > 0){
        curl_setopt( $oCurl, CURLOPT_POST, 1);
        curl_setopt( $oCurl, CURLOPT_POSTFIELDS, $postfields );
    }

    $sCurlResponse = curl_exec( $oCurl );
    $sCurlResponse = json_decode($sCurlResponse);

    return $sCurlResponse;
}

if(!is_dir($rootpath."configurations/".$host."/exports")){
     mkdir($rootpath."configurations/".$host."/exports",0777);
}

/**********************************/
/* TRAITEMENT DES OFFRES EN COURS */
/**********************************/
$aOffresActuelles = array();
/*
$oOffers = sendInfos('offers');
foreach($oOffers->offers as $l_offre){
	echo "il existe deja chez eux : ".$l_offre->shop_sku." (on update)<br>";
    $aOffresActuelles[] = $l_offre->shop_sku;
}
*/


$name_courantes     = "conforama-offres-courantes.xml";
$filename_courantes = $rootpath."configurations/".$host."/exports/".$name_courantes;

//Début d'écriture du XML des offres déjà présentes
$xml = ""
. "<?xml version=\"1.0\" encoding=\"UTF-8\"?>"
     . "<import>";

$compteur = 0;
//print_r($aOffresActuelles);
foreach($aOffresActuelles as $sku){
    if($compteur == 0) $xml .= "<offers>";
    
	//echo "artid : ".$sku." !2!2! ";
    $xml .= offer($sku, true);
    $compteur++;

    if($compteur == count($aOffresActuelles)){
        $xml .= "</offers>";
        break;
    }
}

$xml .= "</import>";

if(!$handle = fopen($filename_courantes, 'w')){
     echo "Impossible d'ouvrir le fichier ($filename_courantes)";
     exit;
}
fwrite($handle, $xml);
fclose($handle);

$postfields = array();
$postfields['file'] = '@'.$filename_courantes;
$postfields['import_mode'] = 'REPLACE';

$response = sendInfos('offers/imports', $postfields);

if(isset($response->import_id) && is_numeric($response->import_id)){
    $msgLog .= "<p style=\"color: green;\"><span style=\"font-weight: bold; font-size: 20px;\">".$compteur."</span> offres courantes mises à jour</p>";
} else {
    $msgLog .= "<p style=\"color: red;\">Erreur lors de la mise à jour des offres courantes</p>";
}


/***********************************/
/* TRAITEMENT DES NOUVELLES OFFRES */
/***********************************/

//Informations sur TVA en France
$sql = "SELECT 0 AS taux0,TVAtauxnormal AS taux1, TVAtauxreduit AS taux2";
$sql .= " FROM pays";
$sql .= " WHERE paysid = 57";
$tvafrance = $DB_site2->query_first($sql);

//Articles activés dans le module marketplace
$sql = "SELECT a.artid, mpc.enabled";
$sql .= " FROM market_place_champs mpc";
$sql .= " INNER JOIN article a ON (a.artcode = mpc.artcode)";
$sql .= " INNER JOIN article_marque am ON (am.artid = a.artid)";
$sql .= " WHERE 1";
$sql .= " AND mpc.market_place = 'conforama'";
$sql .= " AND mpc.enabled      = 1";
$articles = $DB_site2->query($sql);


$aProduit = array();
while($article = $DB_site2->fetch_array($articles)){
    if(!in_array($article['artid'], $aOffresActuelles)){ //On traite l'offre uniquement si elle n'existe pas déjà (traité dans la boucle ci-dessus)
         $aProduit[] = $article;
    }
}

$name_new     = "conforama-offres-nouvelles.xml";
$filename_new = $rootpath."configurations/".$host."/exports/".$name_new;

if(count($aProduit) > 0){
    $xml = ""
    . "<?xml version=\"1.0\" encoding=\"UTF-8\"?>"
    . "<import>"
        . "<offers>";

    $compteur = 0;
    foreach($aProduit as $article){
		// echo "artid : ".$article['artid']." !!! ";
		echo "il existe pas chez eux : ".$article['artid']." (on envoi)<br>";
        $xml .= offer($article['artid']);
        $compteur++;
    }

    $xml .= ""
        . "</offers>"
    . "</import>";

    if(!$handle = fopen($filename_new, 'w')){
     echo "Impossible d'ouvrir le fichier ($filename_new)";
     exit;
    }
    fwrite($handle, $xml);
    fclose($handle);

    $postfields = array();
    $postfields['file'] = '@'.$filename_new;
    $postfields['import_mode'] = 'NORMAL';

    $response = sendInfos('offers/imports', $postfields);

    if(isset($response->import_id) && is_numeric($response->import_id)){
        $msgLog .= "<p style=\"color: green;\"><span style=\"font-weight: bold; font-size: 20px;\">".$compteur."</span> demandes d'offres transmises</p>";
    } else {
        $msgLog .= "<p style=\"color: red;\">Erreur lors de la création des nouvelles offres</p>";
    }
} else {
    $msgLog .= "<p style=\"color: orange;\"><span style=\"font-weight: bold; font-size: 20px;\">Aucune nouvelle offre à créer</p>";
}

$mail = new email();
// $mail->destinataire = "webmaster@ma-cave-a-vin.fr";
$mail->destinataire = "webmaster2@ma-cave-a-vin.fr";
// $mail->to           = array("webmaster1@ma-cave-a-vin.fr");
$mail->to           = array("webmaster2@ma-cave-a-vin.fr");
$mail->expname      = "Ma Cave à Vin";
$mail->expediteur   = "contact@ma-cave-a-vin.fr";
$mail->subject      = "Conforama - Rapport d'intégration/mise à jour des offres - ".date('d/m/Y H:i:s');
$mail->body         = $msgLog; 
$mail->attachfile($filename_courantes,$name_courantes,"text/html");
$mail->attachfile($filename_new,$name_new,"text/html");
$mail->sendmail();

$logpath=$rootpath."/admin/comparateur/log/conforama/";
copy($filename_courantes,$logpath."offres-courantes".time().".xml");
copy($filename_new,$logpath."offres-nouvelles".time().".xml");
