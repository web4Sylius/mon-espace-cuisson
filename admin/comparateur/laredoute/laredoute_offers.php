<?php
ini_set("max_execution_time",180);
ini_set("display_errors",1);

include_once($_SERVER['DOCUMENT_ROOT'].'/admin/host_config_admin.php');

$vendeur_redoute_id = '11604'; //Num?ro vendeur ? ajouter quand on nous le transmet
        
$nostat = 1;
$passage_ligne = "\r\n";
$DB_site = clone $DB_site2;

require_once($rootpath."class/template.class.php");
require_once($rootpath."admin/mpfields.php");
require_once($rootpath."class/fonctions.php");
require_once($rootpath."class/devises.class.php");
require_once($rootpath."class/stock.class.php");
require_once($rootpath."class/frais_port.php");
require_once($rootpath."class/email.php");

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

function generateBoundary(){
     $caracteres = "abcdefghijklmnopqrstuvwyxz0123456789";
     $nb_caract  = 20;
     
     for($i = 1; $i <= $nb_caract; $i++) {
 
          // On compte le nombre de caract?res
          $nbr = strlen($caracteres);

          // On choisit un caract?re au hasard dans la chaine s?lectionn?e :
          $nbr = mt_rand(0,($nbr-1));

          // Pour finir, on ?crit le r?sultat :
          $pwd .= $caracteres[$nbr];
     }
     
     return $pwd;
}

function product($article,$coloris){
     
     global $DB_site2;
     global $host;
     global $template;
     global $passage_ligne;
     global $vendeur_redoute_id;
     
     //Donn?es code EAN
     $sqlcode = "SELECT codebarre";
     $sqlcode .= " FROM code_barre";
     $sqlcode .= " WHERE artid = '".$article['artid']."'";
     if($coloris != false){
          $sqlcode .= " AND caractvalid = '".intval($coloris['caractvalid'])."'";
     }
     $codebarre = $DB_site2->query_first($sqlcode);
     
     //Récupération du prix de l'article
     $ecopart = intval($article['ecopart']);
     $article_price = article_price($article['artid'], false, "base", false);
     $prix_ttc = $article_price - $ecopart;
     if($prix_ttc <= 0) $prix_ttc = 6000;
     $total_ttc = $article_price;
     $prix_ttc_solde = '';

     $offre_debut     = date('Y-m-d').'T00:00:00';
     $offre_fin       = date('Y-m-d',mktime(0,0,0,date('n'),date('d')+7,date('Y'))).'T23:59:59';
     $offre_solde_fin = '';

     /*if(time() > mktime(0,0,0,6,22,2016) && time() < mktime(0,0,0,8,2,2016)){
          switch($article['artid']){
               case 1643:
                    $prix_ttc_solde  = 41.5 - $ecopart;
                    $offre_solde_debut = '2016-06-22T00:01:00';
                    $offre_solde_fin = '2016-08-03T00:01:00';
                    break;
               case 1073:
                    $prix_ttc_solde  = 76 - $ecopart;
                    $offre_solde_debut = '2016-06-22T00:01:00';
                    $offre_solde_fin = '2016-08-03T00:01:00';
                    break;
               case 972:
                    $prix_ttc_solde  = 56 - $ecopart;
                    $offre_solde_debut = '2016-06-22T00:01:00';
                    $offre_solde_fin = '2016-08-03T00:01:00';
                    break;
               case 1070:
                    $prix_ttc_solde  = 50.5 - $ecopart;
                    $offre_solde_debut = '2016-06-22T00:01:00';
                    $offre_solde_fin = '2016-08-03T00:01:00';
                    break;
               case 901:
                    $prix_ttc_solde  = 28 - $ecopart;
                    $offre_solde_debut = '2016-06-22T00:01:00';
                    $offre_solde_fin = '2016-08-03T00:01:00';
                    break;
               case 964:
                    $prix_ttc_solde  = 56 - $ecopart;
                    $offre_solde_debut = '2016-06-22T00:01:00';
                    $offre_solde_fin = '2016-08-03T00:01:00';
                    break;
               case 1818:
                    $prix_ttc_solde  = 1229 - $ecopart;
                    $offre_solde_debut = '2016-06-22T00:01:00';
                    $offre_solde_fin = '2016-08-03T00:01:00';
                    break;
               case 3422:
                    $prix_ttc_solde  = 1449 - $ecopart;
                    $offre_solde_debut = '2016-06-22T00:01:00';
                    $offre_solde_fin = '2016-08-03T00:01:00';
                    break;
               case 1789:
                    $prix_ttc_solde  = 1129 - $ecopart;
                    $offre_solde_debut = '2016-06-22T00:01:00';
                    $offre_solde_fin = '2016-08-03T00:01:00';
                    break;
               case 1790:
                    $prix_ttc_solde  = 1129 - $ecopart;
                    $offre_solde_debut = '2016-06-22T00:01:00';
                    $offre_solde_fin = '2016-08-03T00:01:00';
                    break;
               case 2881:
                    $prix_ttc_solde  = 1799 - $ecopart;
                    $offre_solde_debut = '2016-06-22T00:01:00';
                    $offre_solde_fin = '2016-08-03T00:01:00';
                    break;
               case 2883:
                    $prix_ttc_solde  = 2149 - $ecopart;
                    $offre_solde_debut = '2016-06-22T00:01:00';
                    $offre_solde_fin = '2016-08-03T00:01:00';
                    break;
               case 3913:
                    $prix_ttc_solde  = 1529 - $ecopart;
                    $offre_solde_debut = '2016-06-22T00:01:00';
                    $offre_solde_fin = '2016-08-03T00:01:00';
                    break;
               case 3887:
                    $prix_ttc_solde  = 380 - $ecopart;
                    $offre_solde_debut = '2016-06-22T00:01:00';
                    $offre_solde_fin = '2016-08-03T00:01:00';
                    break;
               case 3817:
                    $prix_ttc_solde  = 595 - $ecopart;
                    $offre_solde_debut = '2016-06-22T00:01:00';
                    $offre_solde_fin = '2016-08-03T00:01:00';
                    break;
               case 3800:
                    $prix_ttc_solde  = 63 - $ecopart;
                    $offre_solde_debut = '2016-06-22T00:01:00';
                    $offre_solde_fin = '2016-08-03T00:01:00';
                    break;
               case 3124:
                    $prix_ttc_solde  = 48 - $ecopart;
                    $offre_solde_debut = '2016-06-22T00:01:00';
                    $offre_solde_fin = '2016-08-03T00:01:00';
                    break;
               case 870:
                    $prix_ttc_solde  = 45 - $ecopart;
                    $offre_solde_debut = '2016-06-22T00:01:00';
                    $offre_solde_fin = '2016-08-03T00:01:00';
                    break;
               case 4042:
                    $prix_ttc_solde  = 44 - $ecopart;
                    $offre_solde_debut = '2016-06-22T00:01:00';
                    $offre_solde_fin = '2016-08-03T00:01:00';
                    break;
               case 2224:
                    $prix_ttc_solde  = 1389 - $ecopart;
                    $offre_solde_debut = '2016-06-22T00:01:00';
                    $offre_solde_fin = '2016-08-03T00:01:00';
                    break;
               case 2227:
                    $prix_ttc_solde  = 699 - $ecopart;
                    $offre_solde_debut = '2016-06-22T00:01:00';
                    $offre_solde_fin = '2016-08-03T00:01:00';
                    break;
               case 1212:
                    $prix_ttc_solde  = 569 - $ecopart;
                    $offre_solde_debut = '2016-06-22T00:01:00';
                    $offre_solde_fin = '2016-08-03T00:01:00';
                    break;
               case 3017:
                    $prix_ttc_solde  = 729 - $ecopart;
                    $offre_solde_debut = '2016-06-22T00:01:00';
                    $offre_solde_fin = '2016-08-03T00:01:00';
                    break;
               case 3018:
                    $prix_ttc_solde  = 729 - $ecopart;
                    $offre_solde_debut = '2016-06-22T00:01:00';
                    $offre_solde_fin = '2016-08-03T00:01:00';
                    break;
               case 4096:
                    $prix_ttc_solde  = 46 - $ecopart;
                    $offre_solde_debut = '2016-06-22T00:01:00';
                    $offre_solde_fin = '2016-08-03T00:01:00';
                    break;
               case 319:
                    $prix_ttc_solde  = 179 - $ecopart;
                    $offre_solde_debut = '2016-06-22T00:01:00';
                    $offre_solde_fin = '2016-08-03T00:01:00';
                    break;
          }
     }*/

     switch($article['tauxchoisi']){
          case 0:
               $taux_tva = 0;
               break;
          case 1:
               $taux_tva = 20;
               break;
          case 2:
               $taux_tva = 5.5;
               break;
     }
     $blocked_price = round(($article['prixconseilleht'] * (1 + $taux_tva / 100)) - $ecopart, 2);

     //Récupération des informations "commandable" et "activé sur fnac" sur le produit dans notre base
     $sql = "SELECT a.commandable, a.date_appro, mpc.enabled, a.artcode";
     $sql .= " FROM article a, market_place_champs mpc";
     $sql .= " WHERE a.artcode = mpc.artcode";
     $sql .= " AND mpc.market_place = 'redoute'";
     $sql .= " AND a.artid = ".intval($article['artid']);
     $tabInfos = $DB_site2->query_first($sql);

     //Récupération des informations "activé sur MCAV" sur le produit dans notre base
     $sql = "SELECT active";
     $sql .= " FROM article_site";
     $sql .= " WHERE site_code = 'base'";
     $sql .= " AND artid = ".intval($article['artid']);
     $tabActive = $DB_site2->query_first($sql);

     $tabLivraison = delailivraison($article['artid']);
     if(empty($tabLivraison['date_appro']) && $tabInfos['date_appro'] > 0) $tabLivraison['date_appro'] = $tabInfos['date_appro'];

     /*
      * Conditions pour que le produit soit désactivé (quantité 0) :
      * - Produit désactivé de la vente sur MCAV
      * - Produit non commandable sur MCAV
      * - Produit désactivé sur LaRedoute
      * - Date de réappro supérieure à 7 jours
      */
      
    $quantity = 10;
    if($tabActive['active'] != 1){
        $DB_site2->query("INSERT INTO market_place_error SET date=".time().", market_place='redoute', artcode='".$article['artcode']."', error='Désactivé : produit inactif'");
        $quantity=0;
    }
    
    if($tabInfos['commandable'] != 1){
        $DB_site2->query("INSERT INTO market_place_error SET date=".time().", market_place='redoute', artcode='".$article['artcode']."', error='Désactivé : produit non commandable'");
        $quantity=0;
    }

    if($tabInfos['enabled'] != 1)
        $quantity=0;
        
    if(!empty($tabLivraison['date_appro']) && mktime(0,0,0,date('m'),date('d')+7,date('Y')) < $tabLivraison['date_appro']){
        $DB_site2->query("INSERT INTO market_place_error SET date=".time().", market_place='redoute', artcode='".$article['artcode']."', error='Désactivé : délai livraison >7j'");
        $quantity = 0;
    }

     
     $frais_port = new frais_port($DB_site2,array($article['artid'] => 1),57,$total_ttc,0,'base');

     $delai_preparation = 2;
     $delai_livraison_min = $tabLivraison['delailivraison'] - 2;
     $delai_livraison_max = $tabLivraison['delailivraison'];

     //Construction du XML
     $xml = ""
     . "<ns0:Item>".$passage_ligne
          . "<ns0:ItemIdentification>".$passage_ligne
               . "<ns0:ItemStandardID Qualifier=\"EN\">".$codebarre[0]."</ns0:ItemStandardID>".$passage_ligne
          . "</ns0:ItemIdentification>".$passage_ligne
          . "<ns0:ItemConditions>".$passage_ligne
               . "<ns0:ItemCondition Condition=\"New\">".$passage_ligne
                    . "<ns0:Availability>".$passage_ligne
                         . "<ns0:StockQuantity>".$quantity."</ns0:StockQuantity>".$passage_ligne
                    . "</ns0:Availability>".$passage_ligne
                    . "<ns0:SellerOffers>".$passage_ligne
                         . "<ns0:SellerOffer CountryCode=\"FR\" Site=\"www.laredoute.fr\">".$passage_ligne
                              . "<ns0:SellerOfferPrices>".$passage_ligne;

                              /*$xml .= "<ns0:SellerOfferPrice CustomerTarget=\"All\" OfferNature=\"Standard\" SellerOfferID=\"".$tabInfos['artcode']."\">".$passage_ligne
                                        . "<ns0:OfferPeriod>".$passage_ligne
                                             . "<ns0:StartDate>".$offre_debut."</ns0:StartDate>".$passage_ligne
                                             . "<ns0:EndDate>".($offre_solde_fin != '' ? $offre_solde_fin : $offre_fin)."</ns0:EndDate>".$passage_ligne 
                                        . "</ns0:OfferPeriod>".$passage_ligne
                                        . "<ns0:SellingPrice PriceType=\"SellingPrice\" CurrencyCode=\"EUR\">".$passage_ligne
                                             . "<ns0:Amount>".(!empty($prix_ttc_solde) && $prix_ttc_solde != 0 ? round($prix_ttc_solde) : round($prix_ttc))."</ns0:Amount>".$passage_ligne
                                             . "<ns0:VATRate>".$taux_tva."</ns0:VATRate>".$passage_ligne
                                             . ($ecopart > 0 ? "<ns0:WEEEFee>".round($ecopart)."</ns0:WEEEFee>".$passage_ligne : "")
                                        . "</ns0:SellingPrice>".$passage_ligne
                                        . "<ns0:BlockedPrice>".$blocked_price."</ns0:BlockedPrice>".$passage_ligne
                                   . "</ns0:SellerOfferPrice>".$passage_ligne;*/

                              if($offre_solde_debut != '' && $offre_solde_fin != ''){
								  
                                   $xml .= "<ns0:SellerOfferPrice CustomerTarget=\"All\" OfferNature=\"Standard\" SellerOfferID=\"".$tabInfos['artcode']."\">".$passage_ligne
                                        . "<ns0:OfferPeriod>".$passage_ligne
                                             . "<ns0:StartDate>".$offre_solde_debut."</ns0:StartDate>".$passage_ligne
                                             . "<ns0:EndDate>".$offre_solde_fin."</ns0:EndDate>".$passage_ligne 
                                        . "</ns0:OfferPeriod>".$passage_ligne
                                        . "<ns0:SellingPrice PriceType=\"SellingPrice\" CurrencyCode=\"EUR\">".$passage_ligne
                                             . "<ns0:Amount>".round($prix_ttc_solde)."</ns0:Amount>".$passage_ligne
                                             . "<ns0:VATRate>".$taux_tva."</ns0:VATRate>".$passage_ligne
                                             . ($ecopart > 0 ? "<ns0:WEEEFee>".round($ecopart)."</ns0:WEEEFee>".$passage_ligne : "")
                                        . "</ns0:SellingPrice>".$passage_ligne
                                        . "<ns0:BlockedPrice>".$blocked_price."</ns0:BlockedPrice>".$passage_ligne
                                   . "</ns0:SellerOfferPrice>".$passage_ligne;
								   
                              } else {
								  
                                   $xml .= "<ns0:SellerOfferPrice CustomerTarget=\"All\" OfferNature=\"Standard\" SellerOfferID=\"".$tabInfos['artcode']."\">".$passage_ligne
                                        . "<ns0:OfferPeriod>".$passage_ligne
                                             . "<ns0:StartDate>".date('Y-m-d')."T00:00:00</ns0:StartDate>".$passage_ligne
                                             . "<ns0:EndDate>".date('Y-m-d',mktime(0,0,0,date('n'),date('d')+7,date('Y')))."T23:59:59</ns0:EndDate>".$passage_ligne 
                                        . "</ns0:OfferPeriod>".$passage_ligne
                                        . "<ns0:SellingPrice PriceType=\"SellingPrice\" CurrencyCode=\"EUR\">".$passage_ligne
                                             . "<ns0:Amount>".round($prix_ttc)."</ns0:Amount>".$passage_ligne
                                             . "<ns0:VATRate>".$taux_tva."</ns0:VATRate>".$passage_ligne
                                             . ($ecopart > 0 ? "<ns0:WEEEFee>".round($ecopart)."</ns0:WEEEFee>".$passage_ligne : "")
                                        . "</ns0:SellingPrice>".$passage_ligne
                                        . "<ns0:BlockedPrice>".$blocked_price."</ns0:BlockedPrice>".$passage_ligne
                                   . "</ns0:SellerOfferPrice>".$passage_ligne;
                              }

                              $xml .= "</ns0:SellerOfferPrices>".$passage_ligne
                              . "<ns0:Deliveries>".$passage_ligne
                                   . "<ns0:Delivery DeliveryMode=\"Home\" DeliveryType=\"Regular\">".$passage_ligne
                                        . "<ns0:DeliveryFee CurrencyCode=\"EUR\">".round($frais_port->prixht * 1.2)."</ns0:DeliveryFee>".$passage_ligne
                                        . "<ns0:ShipmentDelay>".$delai_preparation."</ns0:ShipmentDelay>".$passage_ligne
                                        . "<ns0:DeliveryDaysRange>".$passage_ligne
                                             . "<ns0:Min>".$delai_livraison_min."</ns0:Min>".$passage_ligne
                                             . "<ns0:Max>".$delai_livraison_max."</ns0:Max>".$passage_ligne
                                        . "</ns0:DeliveryDaysRange>".$passage_ligne
                                   . "</ns0:Delivery>".$passage_ligne
                              . "</ns0:Deliveries>".$passage_ligne
                              . "<ns0:Descriptions>".$passage_ligne
                                   . "<ns0:Description LanguageCode=\"fr\"><![CDATA[Livraison dans les délais affichés, assurée par des professionnels, en France et en Europe.]]></ns0:Description>".$passage_ligne
                              . "</ns0:Descriptions>".$passage_ligne
                         . "</ns0:SellerOffer>".$passage_ligne
                    . "</ns0:SellerOffers>".$passage_ligne
               . "</ns0:ItemCondition>".$passage_ligne
          . "</ns0:ItemConditions>".$passage_ligne
     . "</ns0:Item>".$passage_ligne;

     return $xml;
}

if(!is_dir($rootpath."admin/comparateur/log/laredoute/Offres")){
     mkdir($rootpath."admin/comparateur/log/laredoute/Offres",0777);
}

$filename = $vendeur_redoute_id.".MKP_SellerOffer.001.".date('y-m-d-H-i-s').".xml";
$filehost = $rootpath."admin/comparateur/log/laredoute/Offres/".$filename;
$filelink = "http://".$host."/admin/comparateur/log/laredoute/Offres/".$filename;

if(!$handle = fopen($filehost, 'w')){
     echo "Impossible d'ouvrir le fichier (".$filehost.")";
     exit;
}

$sql = "SELECT a.artid, a.tauxchoisi, a.ecopart, a.prixconseilleht";
$sql .= " FROM article a USE INDEX(artcode)";
$sql .= " INNER JOIN market_place_champs mpc ON (mpc.artcode = a.artcode)";
$sql .= " INNER JOIN market_place_catid mp ON (mp.catid = a.catid)";
$sql .= " WHERE 1";
//$sql .= " AND a.active = '1'";
$sql .= " AND mp.market_place = 'redoute'";
$sql .= " AND mpc.market_place = 'redoute'";
$sql .= " GROUP BY a.artcode";
$sql .= " ORDER BY a.artid";
$articles = $DB_site2->query($sql);


$xml = ""
. "<?xml version=\"1.0\" encoding=\"UTF-8\"?>".$passage_ligne
. "<ns0:SellerOfferRequest xmlns:ns0=\"http://RedCats/MarketPlace/Partnership/2.0\" HubID=\"400\" SellerID=\"".$vendeur_redoute_id."\" MessageDate=\"".date('c')."\" MessageRelease=\"1.0\">".$passage_ligne;

$compteur = 0;
while($article = $DB_site2->fetch_array($articles)){

     //if($article['artid'] == 319 || $article['artid'] == 383 || $article['artid'] == 1538 || $article['artid'] == 1821 || $article['artid'] == 1510 || $article['artid'] == 1511 || $article['artid'] == 518 || $article['artid'] == 1791 || $article['artid'] == 1802 || $article['artid'] == 1518 || $article['artid'] == 1787 || $article['artid'] == 1820 || $article['artid'] == 2068 || $article['artid'] == 2035 || $article['artid'] == 1789 || $article['artid'] == 1509 || $article['artid'] == 1549 || $article['artid'] == 1209){  
     $xml .= product($article, false);
     $compteur++; 
//}
     //if($compteur == 5) break;
}

$xml .= "</ns0:SellerOfferRequest>";

fwrite($handle, $xml);
fclose($handle);
// chmod($filehost,0777);

/*echo "Votre export de catalogue pour La Redoute a bien ?t? mis ? jour (produit export?s : ".$compteur.")<br>
Il est disponible ? l'adresse suivante : <a href=\"".$filelink."\"><strong>".$filelink."</strong></a>" ;*/

//Data, connection, auth
$soapUrl = "https://marketplace.laredoute.com/Redcats/File/ESB-TIBCO/File/1.0"; // asmx URL of WSDL

$boundary = "=_".generateBoundary();

$xml_post_string = ""
     ."--".$boundary.$passage_ligne
     . "Content-Type: application/soap+xml; charset=UTF-8;".$passage_ligne
     . "Content-Transfer-Encoding: 8bit".$passage_ligne
     . "Content-ID: <webmaster1@ma-cave-a-vin.fr>".$passage_ligne
     . $passage_ligne
     . "<?xml version=\"1.0\" encoding=\"utf-8\"?>".$passage_ligne
     . "<SOAP-ENV:Envelope xmlns:SOAP-ENV=\"http://www.w3.org/2003/05/soap-envelope\" xmlns:xsd=\"http://www.w3.org/2001/XMLSchema\" xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xmlns:SOAP-ENC=\"http://schemas.xmlsoap.org/soap/encoding/\" xmlns:ns3797=\"http://tempuri.org\">".$passage_ligne
          . "<SOAP-ENV:Body>".$passage_ligne
               . "<InjectFileRequest_1.0 xmlns=\"http://Redcats/File/ESB-TIBCO/File/1.0/InjectFile/1.0\">".$passage_ligne
                    . "<EUserId>".$vendeur_redoute_id."</EUserId>".$passage_ligne
                    . "<ESystem>400</ESystem>".$passage_ligne
                    . "<FlowName>MKP_SellerOffer</FlowName>".$passage_ligne
                    . "<FlowVersion>001</FlowVersion>".$passage_ligne
                    . "<FileName>".$filename."</FileName>".$passage_ligne
                    . "<fileAttachedWithSoapMsg>true</fileAttachedWithSoapMsg>".$passage_ligne
               . "</InjectFileRequest_1.0>".$passage_ligne
          . "</SOAP-ENV:Body>".$passage_ligne
     . "</SOAP-ENV:Envelope>".$passage_ligne
     . $passage_ligne
     . "--".$boundary.$passage_ligne
     . "Content-Type: text/xml; charset=UTF-8;".$passage_ligne
     . "Content-Transfer-Encoding: 8bit".$passage_ligne
     . "Content-ID: <".$filename.">".$passage_ligne
     . "Content-Disposition: attachment; name=\"".$filename."\", filename=\"".$filename."\"".$passage_ligne
     . $passage_ligne
     . $xml; // data from the form, e.g. some ID number

$headers = array(
     "Content-Type: multipart/related; type=\"application/soap+xml\"; boundary=\"".$boundary."\"",
     "SOAPAction: \"InjectFile_1.0\"",
     "Content-Length: ".strlen($xml_post_string),
     "User-Agent: STREAM-CONTEXT"
 ); //SOAPAction: your op URL

$url = $soapUrl;
//echo $xml_post_string;
$fp = fopen(dirname(__FILE__).'/error-curl-redoute.txt', 'w');

// PHP cURL  for https connection
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 60);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $xml_post_string); // the SOAP request
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_VERBOSE, true);
curl_setopt($ch, CURLOPT_STDERR, $fp);

$response = curl_exec($ch);

// converting
/*if($response === false)
{                
    $err = 'Transfert ?chou? : <span style="color:red;">' . curl_error($ch) . '</span>';
    echo $err;
} 
else 
{
     echo "Transfert r?ussi";
}*/

//nicex($xml_post_string);

curl_close($ch);

$clean_xml = str_ireplace(['SOAP-ENV:', 'SOAP:', 'ns0:'], '', $response);
$response = simplexml_load_string($clean_xml);

/*nice($xml_post_string);*/

foreach($response as $key => $value){
     if(is_object($value) && !empty($value)){
          foreach($value as $key2 => $value2){
               if(is_object($value2) && !empty($value2)){
                    foreach($value2 as $key3 => $value3){
                         if(is_object($value3) && !empty($value3)){
                              foreach($value3 as $key4 => $value4){ 
                                   if(is_object($value4) && !empty($value4)){ 
                                        $message .= 'Message => '.$value4[0].'<br>';
                                   } else {
                                        $message .= $key4.' => '.$value4.'<br>';
                                   }
                              }
                         } else {
                              $message .= $key3.' => '.$value3.'<br>';
                         }
                    }
               } else {
                    $message .= $key2.' => '.$value2.'<br>';
               }
          }
     } else {
          $message .= $key.' => '.$value.'<br>';
     }
}

if(isset($_POST['URL']) && $_POST['URL'] != ''){
	$date = date("d-m-Y H:i");
	$action = "Mise a jour des fichiers \"Offres\"";
	$market_place = "laredoute";
	//mise Ã  jour du suivi
	$sql = "INSERT INTO suivi_export SET ";
	$sql .= " user = '$_COOKIE[admin_username]'";
	$sql .= ", time = '$date'";
	$sql .= ", action = '$action'";
	$sql .= ", market_place = '$market_place'";
	$DB_site2->query($sql);
	$_POST['URL'] = $_POST['URL']."&update=1";
	header('Location: '.$_POST['URL']);
} else {
	$mail = new email();
	$mail->destinataire = "webmaster1@ma-cave-a-vin.fr";
	//$mail->to           = array("webmaster@ma-cave-a-vin.fr");
	$mail->expname      = "Ma Cave à Vin";
	$mail->expediteur   = "contact@ma-cave-a-vin.fr";
	$mail->subject      = "LaRedoute - Rapport de creation des offres - ".date('d/m/Y H:i:s');
	$mail->body         = $message; 
	$mail->sendmail();
}
