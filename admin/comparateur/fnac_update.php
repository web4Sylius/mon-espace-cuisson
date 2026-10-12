<?php
require_once __DIR__.'/../../secrets.php';
include_once("../host_config_admin.php");

include($rootpath."class/fonctions.php");
require_once($rootpath."class/devises.class.php");
require_once($rootpath."class/stock.class.php");
require_once($rootpath."class/frais_port.php");
require_once($rootpath."admin/comparateur/fnacapi/autoload.php");
require_once($rootpath."class/email.php");
ini_set('display_errors','on');

$DB_site=clone $DB_site2;

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

use FnacApiClient\Client\SimpleClient;
use FnacApiClient\Service\Request\OfferQuery;
use FnacApiClient\Service\Request\OfferUpdate;
use FnacApiClient\Service\Request\BatchStatus;

use FnacApiClient\Entity\Offer;

use FnacApiClient\Type\ResponseStatusType;
use FnacApiClient\Type\ProductType;
use FnacApiClient\Type\OfferReferenceType;
use FnacApiClient\Type\ProductStateType;

$host="https://marketplace.ws.fd-recette.net/api.php";
$shop_id="087FC902-6945-38B4-59B4-BB4281E16EFE";
$partner_id="A012594B-C5B1-1062-E3AD-0C7FFAEB24FD";
$key=FNAC_API_KEY_OLD;

$host="https://vendeur.fnac.com/api.php";
$shop_id="1F53469C-1EB1-0B9F-6E25-82E40F685BA5";
$partner_id="8AC7076B-6D33-4E1F-903C-D558F35C3FD1";
$key=FNAC_API_KEY;

//In the constructor
$myClient = new SimpleClient($host, $shop_id, $partner_id, $key);

//With an array in the init method
$myClient->init(array(
  'host' => $host,
  'shop_id' => $shop_id,
  'partner_id' => $partner_id,
  'key' => $key
));

//********************************\\
//MISE A JOUR DES OFFRES COURANTES\\
//********************************\\
//
//Classe de consultation des offres
$offerQuery = new OfferQuery();
$offerQuery->setPaging(1);
$offerQuery->setResultsCount(1500);
$offerQueryResponse = $myClient->callService($offerQuery);

//Récupération de toutes les offres courantes
$offers = $offerQueryResponse->getOffers();

//Classe de mise à jour des offres
$offerUpdate = new OfferUpdate();


// DEBUT - EN CAS DE SOLDES :
$tabProduitsSoldes = array(
	4000 => floatval('929'), //	ACI-AVI577E
	4968 => floatval('1359'), //	ACI-AVI603E
	2883 => floatval('2149'), //	ACI-AVI436
	2852 => floatval('1799'), //	ACI-AVI435
	4233 => floatval('1759'), //	ACI-CLI722TC
	5031 => floatval('939'), //	ACI-CLI724
	5159 => floatval('1349'), //	ACI-CLI724TC
	1643 => floatval('39'), //	ACI-ADV209
	3117 => floatval('49'), //	ACI-ADV321
	3079 => floatval('379'), //	ACI-WNA100CE
	5195 => floatval('119'), //	ACI-CLI489
	3800 => floatval('55'), //	ACI-CLI120
	4212 => floatval('79') //	ACI-OEN300L
);

/*
	3999 => floatval('929'), //	ACI-AVI577
	4000 => floatval('929'), //	ACI-AVI577E
	4968 => floatval('1359'), // 	ACI-AVI603E
	4967 => floatval('1359'), // 	ACI-AVI603
	2883 => floatval('2149'), // 	ACI-AVI436
	2852 => floatval('1799'), // 	ACI-AVI435
	4233 => floatval('1759'), // 	ACI-CLI722TC
	5031 => floatval('939'), // 	ACI-CLI724
	5159 => floatval('1349'), // 	ACI-CLI724TC
	1643 => floatval('39'), // 	ACI-ADV209
	3117 => floatval('49') // 	ACI-ADV321
*/

$datedebutsoldes=mktime(0,0,0,1,10,2018); // le 10 janvier 2018
$datefinsoldes=mktime(0,0,0,2,20,2018); // le 20 fevrier 2018
// FIN - EN CAS DE SOLDES :



//Boucles sur les offres courantes
$tabMemoireArticle = array();
foreach($offers as $offer){
     
     //Récupération de l'identifiant du produit (SKU)
     $artid = $offer->getOfferSellerId();
     
     //Vérification que le produit est bien intégré dans notre table marketplace fnac
     $sql = "SELECT mpc.id";
     $sql .= " FROM market_place_champs mpc, article a";
     $sql .= " WHERE mpc.artcode = a.artcode";
     $sql .= " AND a.artid = ".intval($artid);
     $sql .= " AND mpc.market_place = 'fnac'";
     $tabTest = $DB_site2->query_first($sql);

     //Stockage dans un tableau pour traitement ultérieur
     $tabMemoireArticle[] = $artid;

     //Récupération des informations "commandable" et "activé sur fnac" sur le produit dans notre base
     $sql = "SELECT a.commandable, a.date_appro, mpc.enabled";
     $sql .= " FROM article a, market_place_champs mpc";
     $sql .= " WHERE a.artcode = mpc.artcode";
     $sql .= " AND mpc.market_place = 'fnac'";
     $sql .= " AND a.artid = ".intval($artid);
     $tabInfos = $DB_site2->query_first($sql);

     //Récupération des informations "activé sur MCAV" sur le produit dans notre base
     $sql = "SELECT active";
     $sql .= " FROM article_site";
     $sql .= " WHERE site_code = 'base'";
     $sql .= " AND artid = ".intval($artid);
     $tabActive = $DB_site2->query_first($sql);

     $tabLivraison = delailivraison($artid);
     if(empty($tabLivraison['date_appro']) && $tabInfos['date_appro'] > 0) $tabLivraison['date_appro'] = $tabInfos['date_appro'];

     /*
      * Conditions pour que le produit soit désactivé (quantité 0) :
      * - Produit désactivé de la vente sur MCAV
      * - Produit non commandable sur MCAV
      * - Produit désactivé sur la FNAC
      * - Date de réappro supérieure à 7 jours
      */
	 
	 
	 
	 
	 
     if($tabActive['active'] != 1 || $tabInfos['commandable'] != 1 || $tabInfos['enabled'] != 1 || (!empty($tabLivraison['date_appro']) && mktime(0,0,0,date('m'),date('d')+7,date('Y')) < $tabLivraison['date_appro'])){
          $quantity = 0;
     } else {
          $quantity = 10;
     }
	 /*
	 if($artid==3999){
		 echo "active : ".$tabActive['active']." , commandable : ".$tabInfos['commandable']." , enabled :".$tabInfos['enabled'] ." date appro : ".$tabLivraison['date_appro']." ";
		 echo " -> qte : ".$quantity."<br>";
     }
	 */

     //Récupération du prix de l'article
     $article_price = article_price($artid, false, "base", false);

     $frais_port = new frais_port($DB_site2, array($artid), 57, $article_price);
     $port_ttc = ceil($frais_port->prixht * 1.2);
     $article_price = $article_price > 0 ? $article_price + $port_ttc : 0;

     //Sécurité : si prix = 0, on met 1 par défaut
     if($article_price == 0) $article_price = 1;

      //Offres périodes de soldes
    $article_price_adh = $article_price;
     if(time() > $datedebutsoldes && time() < $datefinsoldes && array_key_exists($artid, $tabProduitsSoldes)){

        //$article_price_adh = article_price($artid, false, "base");
        $article_price = $tabProduitsSoldes[$artid];
        $frais_port_solde = new frais_port($DB_site2, array($artid), 57, $article_price);
        $port_ttc_solde = ceil($frais_port_solde->prixht * 1.2);
        $article_price += $article_price > 0 ? $port_ttc_solde : 0;
     }

     //Changement du stock et des prix
     $offer->setQuantity($quantity); 
     //$offer->setPrice($article_price_adh);
     $offer->setPrice($article_price);
     // $offer->setAdherentPrice($article_price_adh);
     // $offer->setAdherentPrice($article_price);
     $offer->setDescription("Livraison sous ".$tabLivraison['delailivraison']." jours à l'entrée de votre maison ou au pied de votre immeuble, assurée par des professionnels, en France et en Europe.");
  
     //Si le produit n'est pas indiqué comme intégré à la FNAC dans notre BDD (sécurité)
     if(!isset($tabTest['id'])){
          if($quantity > 0){ //enabled
               $enabled = 1;
          } else { //disabled
               $enabled = 0;
          }

          $sql = "SELECT artcode";
          $sql .= " FROM article";
          $sql .= " WHERE artid = ".intval($artid);
          $tabInsert = $DB_site2->query_first($sql);

          //Réintégration du produit dans la BDD
          $sql = "INSERT INTO market_place_champs (";
          $sql .= "market_place,";
          $sql .= "artcode,";
          $sql .= "enabled";
          $sql .= ") VALUES (";
          $sql .= "'fnac',";
          $sql .= "'".$tabInsert['artcode']."',";
          $sql .= $enabled;
          $sql .= ")";
          $DB_site2->query($sql);
     }

     //Mise à jour de l'offre
     $offerUpdate->addOffer($offer);
}
unset($offer);

//*****************************\\
//CREATION DES NOUVELLES OFFRES\\
//*****************************\\

$sql = "SELECT a.artid";
$sql .= " FROM article a";
$sql .= " INNER JOIN market_place_champs mpc ON (mpc.artcode = a.artcode)";
$sql .= " INNER JOIN article_site a_s ON (a_s.artid = a.artid)";
$sql .= " WHERE a_s.active = 1";
$sql .= " AND a_s.site_code LIKE 'base'";
$sql .= " AND mpc.enabled = 1";
$sql .= " AND mpc.market_place = 'fnac'";
$sql .= " AND a.artid NOT IN (";
foreach($tabMemoireArticle as $larticle_id){
     $sql .= $larticle_id.',';
}
$sql .= "-1)";
$req = $DB_site2->query($sql);
while($l_article = $DB_site2->fetch_array($req)){
     
     //Récupération du code barre de l'article
     $sql = "SELECT codebarre";
     $sql .= " FROM code_barre";
     $sql .= " WHERE artid = ".intval($l_article['artid']);
     $tabEan = $DB_site2->query_first($sql);
     
     //Récupération du prix de l'article
     $article_prix = article_price($l_article['artid'], false, "base", false);
     
     $frais_port = new frais_port($DB_site2, array($l_article['artid']), 57, $article_prix);
     $port_ttc = ceil($frais_port->prixht * 1.2);
     $article_prix = $article_prix > 0 ? $article_prix+$port_ttc : 0;

     //Offres périodes de soldes
     $article_price_adh = $article_prix;
     if(time() > $datedebutsoldes && time() < $datefinsoldes && array_key_exists($l_article['artid'], $tabProduitsSoldes)){

        //$article_price_adh = article_price($l_article['artid'], false, "base");
        $article_prix = $tabProduitsSoldes[$l_article['artid']];
        $article_prix += $article_prix > 0 ? $port_ttc : 0;
     }

     $tabLivraison = delailivraison($l_article['artid']);
     
     if(isset($tabEan['codebarre']) && !empty($tabEan['codebarre']) && is_numeric($article_prix) && $article_prix > 0){ 
          $offer = new Offer();
          $offer->setProductReferenceType(ProductType::EAN);
          $offer->setProductReference($tabEan['codebarre']);
          $offer->setOfferReferenceType(OfferReferenceType::SELLER_SKU);
          $offer->setOfferReference($l_article['artid']);
          $offer->setPrice($article_prix);
          // $offer->setPrice($article_price_adh);
          $offer->setProductState(ProductStateType::UNUSED);
          $offer->setQuantity(10);
          // $offer->setAdherentPrice($article_price_adh);
          $offer->setDescription("Livraison sous ".$tabLivraison['delailivraison']." jours à l'entrée de votre maison ou au pied de votre immeuble, assurée par des professionnels, en France et en Europe.");
		  // $offer->setAdherentPrice($article_prix);
          $offerUpdate->addOffer($offer);
          unset($offer);
     }
     
}

$offerUpdateResponse = $myClient->callService($offerUpdate);

$batch_id = $offerUpdateResponse->getBatchId();

//Create batch status service
$batchStatus = new BatchStatus();

//Set the id we want to retrieve
$batchStatus->setBatchId($batch_id);

do {
    echo "Requesting batch status in 5 sec";
    
    sleep(5);
    $batchStatusResponse = $myClient->callService($batchStatus); 

} while($batchStatusResponse->getStatus() != ResponseStatusType::OK && $batchStatusResponse->getStatus() != ResponseStatusType::ERROR && $batchStatusResponse->getStatus() != ResponseStatusType::WARNING);

$message = '';
$nb = 0;
foreach ($batchStatusResponse->getOffersUpdate() as $offerUpdate)
{ 
    if ($offerUpdate->getStatus() == ResponseStatusType::ERROR)
    {
        foreach($offerUpdate->getErrors() as $error)
        {
            print sprintf("<span style=\"color:red;\">".$nb." - An error occured updating Offer #%s with code %s and message : %s </span><br>", $offerUpdate->getOfferSellerId(), $error->getCode(), $error->getMessage());
            if($message != '') $message .= '<br>';
            $message .= "SKU : ".$offerUpdate->getOfferSellerId()." | ERR : ".$error->getCode()." | Message : ".$error->getMessage();
            $article=$DB_site->query_first("SELECT artcode FROM article WHERE artid=".intval($offerUpdate->getOfferSellerId()));
            $DB_site->query("INSERT INTO market_place_error SET date=".time().", market_place='fnac', artcode='".$article['artcode']."', error='".mysqli_real_escape_string($DB_site->link_id,utf8_encode($error->getMessage()))."'");
        }
    }
    else
    {
    //Offer has been updated
    echo "<span style=\"color:green;\">".$nb." - Offre mise à jour pour l'article id = ".$offerUpdate->getOfferSellerId()."</span><br>";
    }

    $nb++;
}

if(isset($_POST['URL']) && $_POST['URL'] != ''){
    $fichier_destinataire = fopen("./log/fnac/fichier_destinataire.txt", 'a');
    fputs($fichier_destinataire, $message);
    fclose($fichier_destinataire);
    header('Location: '.$_POST['URL']);
    exit;
} else {
    $mail = new email();
    $mail->destinataire = "webmaster1@ma-cave-a-vin.fr";
    //$mail->to           = array("webmaster@ma-cave-a-vin.fr");
    $mail->expname      = "Ma Cave à Vin";
    $mail->expediteur   = "contact@ma-cave-a-vin.fr";
    $mail->subject      = "Fnac - Rapport de creation des offres - ".date('d/m/Y H:i:s');
    $mail->body         = $message != '' ? $message : 'Aucune erreur'; 
    $mail->sendmail();
}
?>
