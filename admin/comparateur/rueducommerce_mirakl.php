<?php
ini_set("max_execution_time",300);
ini_set("display_errors",1);
$prov = "comp" ;

header('Content-Type: text/html; charset=utf-8');
include_once($_SERVER['DOCUMENT_ROOT'].'/admin/host_config_admin.php');

$nostat    = 1;

require_once($rootpath."class/template.class.php");
require_once($rootpath."admin/mpfields.php");
require_once($rootpath."class/fonctions.php");
require_once($rootpath."class/devises.class.php");
require_once($rootpath."class/stock.class.php");
require_once($rootpath."class/garantie.class.php");
require_once($rootpath."class/DAOgarantie.class.php");
require_once($rootpath."class/DAOarticle.class.php");
require_once($rootpath."class/article.class.php");
require_once($rootpath."class/tva.class.php");
require_once($rootpath."class/frais_port.php");

// ini_set("display_errors","on");

$libelle     = "libelleFR";
$description = "descriptionFR";

$today = mktime(0,0,0)+86400;

$template = new template("FR",$DB_site2);
$tva = new tva($DB_site2);
$tva->setPays(57);

//Liste des capacités (en attendant que le nombre ne soit plus "fermé")
$aCapacite = array(1,2,3,4,5,6,7,8,9,10,11,12,14,15,16,18,20,25,30,35,40,48,64);

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

if(isset($_GET['list'])){
     $f = fopen($_GET['list'],"r");
     $i = 0;
     while(!feof($f)){
          $line = fgets($f,512);
          
          $sql = "UPDATE market_place_champs SET";
          $sql .= " enabled = 1";
          $sql .= " WHERE artcode = '".strtoupper(chop($line))."'";
          $result = $DB_site->query($sql);
          
          print "<br>".$line." ".$result;
          $i++;
     }
     fclose($f);
     print "<br>".$i." activés";
     exit;
}

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

if(!is_dir($rootpath."configurations/".$host."/exports/RDC")){
     mkdir($rootpath."configurations/".$host."/exports/RDC",0777);
}

$filename_produits = $rootpath."configurations/$host/exports/RDC/rueducommerce-mirakl-produits.xml";
$filename_offres   = $rootpath."configurations/$host/exports/RDC/rueducommerce-mirakl-offres.xml";

if(!$handle_produits = fopen($filename_produits, 'w')){
     echo "Impossible d'ouvrir le fichier ($filename_produits)";
     exit;
}

if(!$handle_offres = fopen($filename_offres, 'w')){
     echo "Impossible d'ouvrir le fichier ($filename_offres)";
     exit;
}

//Articles activés dans le module marketplace
$sql = "SELECT a.artid, mpc.enabled";
$sql .= " FROM market_place_champs mpc";
$sql .= " INNER JOIN article a ON (a.artcode = mpc.artcode)";
$sql .= " INNER JOIN article_marque am ON (am.artid = a.artid)";
$sql .= " WHERE 1";
$sql .= " AND mpc.market_place = 'rdc'";
//$sql .= " AND mpc.enabled      = 1";
$articles = $DB_site2->query($sql);

//Début d'écriture du XML
$xml_produits = $xml_offres = ""
. "<?xml version=\"1.0\" encoding=\"UTF-8\"?>"
     . "<import>";

$aProduit = array();
while($article = $DB_site2->fetch_array($articles)){
    $aProduit[] = $article;
}

//*******************\\
// CREATION PRODUITS \\
//*******************\\
$compteur        = 0;
$aRefIncompletes = array();
foreach($aProduit as $article){

    //Si au moins 1 article, insertion de la balise "<products>"
    if($compteur == 0) $xml_produits .= "<products>";

    if($article['enabled'] == 1){

        //Génération des tags produit
        $xml_produits .= product($article['artid']); 
    }

    $compteur++;

    //Si au moins 1 article, insertion de la balise "<products>"
    if($compteur == count($aProduit)){
        $xml_produits .= "</products>";
        break;
    }
}

$xml_produits .= "</import>";

fwrite($handle_produits, $xml_produits);
fclose($handle_produits);

//*****************\\
// CREATION OFFRES \\
//*****************\\
$compteur = 0;
foreach($aProduit as $article){
     
     if($compteur == 0) $xml_offres .= "<offers>";
     
     $xml_offres .= offer($article['artid'], $article['enabled']);
     $compteur++;

     if($compteur == count($aProduit)){
        $xml_offres .= "</offers>";
        break;
     }
}

$xml_offres .= "</import>";

//nicexml($xml);
fwrite($handle_offres, $xml_offres);
fclose($handle_offres);
//chmod($filename,0777);

echo ""
. "Vos exports de catalogue pour Rue du Commerce ont été générés (produit exportés : ".$compteur.")"
. "<br>"
. "Il sont disponibles aux adresses :"
. "<br>"
. "- <a href=\"http://".$host."/configurations/".$host."/exports/RDC/rueducommerce-mirakl-produits.xml\"><strong>http://".$host."/configurations/".$host."/exports/RDC/rueducommerce-mirakl-produits.xml</strong></a>"
. "<br>"
. "- <a href=\"http://".$host."/configurations/".$host."/exports/RDC/rueducommerce-mirakl-offres.xml\"><strong>http://".$host."/configurations/".$host."/exports/RDC/rueducommerce-mirakl-offres.xml</strong></a>";

//Fonction pour CREATION PRODUITS
function product($article_id){
     global $DB_site2;
     global $template;
     global $aCapacite;
     global $http_host;
     global $aRefIncompletes;
   global $rootpath;

     $objArticle = new produit($DB_site2, 'FR', 'base', $template->tva_ref(), $article_id);
     
     //Champs à transmettre à RDC
     $sql = "SELECT champ, valeur, valeur_temp";
     $sql .= " FROM market_place_catid";
     $sql .= " WHERE market_place = 'rdc'";
     $sql .= " AND catid = ".$objArticle->categorie[0]['categorie_id']; //echo $sql.'<br>';
     $requete = $DB_site2->query($sql);

     $mp_field = array();
     while($champ = $DB_site2->fetch_array($requete)){
          $mp_field[$champ['champ']] = !empty($champ['valeur_temp']) ? $champ['valeur_temp'] : $champ['valeur'];
     }

     //if($mp_field['mcid'] == 'CAVEAC') exit($objArticle->ref_aci);

     /**
      * article_sku OBLIGATOIRE : Identifiant unique article
      * brand OBLIGATOIRE : Libellé de la marque
      * brand_reference FACULTATIF : Référence fabricant
      * category OBLIGATOIRE : Classification
      * ean13 FACULTATIF : Code EAN
      * short_title OBLIGATOIRE : Titre courte
      * long_title FACULTATIF : Titre long
      * short_description OBLIGATOIRE : Description courte
      * full_description FACULTATIF : Description longue
      * group_code FACULTATIF : Variantes d'un article
      * media_notice_url FACULTATIF : URL notice
      * media_url_1 OBLIGATOIRE : image principale
      * media_url_X FACULTATIF : images secondaires
      * warranty FACULTATIF : garantie constructeur (en mois)
      */
     
     //Gestion cas particulier
     if(strpos($objArticle->marque_libelle, 'HOTPOINT') !== false) $objArticle->marque_libelle = 'HOTPOINT-ARISTON';
  
  if($objArticle->visuel[0]['lien'] != ''){

    foreach($objArticle->visuel as $le_visuel)
    {
        if(isset($le_visuel['lien']) && $le_visuel['lien'] != '')
        {
            //Les images doivent être en 600x600 pour Rue du Commerce
            //On crée donc le visuel adapté et on modifie le lien
            $visuel              = $rootpath . substr($le_visuel['lien'], 1);
            $nom_visuel          = substr($visuel,strrpos($visuel,'/')+1,strlen($visuel));
            $nom_visuel          = 'configurations/www.ma-cave-a-vin.fr/images/produits/600x600/' . $nom_visuel;
            $destinataire_abs    = $rootpath .  $nom_visuel;
            $destinataire_lien[] = $http_host . '/' . $nom_visuel;
            if(!file_exists($destinataire_abs)) redim_color_bg($visuel, $destinataire_abs, array('R' => 255, 'G' => 255, 'B' => 255), array('largeur' => 600, 'hauteur' => 600));
        }
    }

     $aMainBalise = array(
          'article_sku'       => $objArticle->id,
          'brand'             => mb_strtoupper(str_replace('à','a',$objArticle->marque_libelle)),
          'brand_reference'   => '',
          'category'          => $mp_field['mcid'],
          'ean13'             => $objArticle->code_barre,
          'short_title'       => "<![CDATA[".html_entity_decode(mpfields($objArticle->id,$mp_field['subjectivedenomination'],"<br/><br><br />"))."]]>",
          'long_title'        => "<![CDATA[".html_entity_decode(mpfields($objArticle->id,$mp_field['denomination'],"<br/><br><br />"))."]]>",
          'short_description' => "<![CDATA[".html_entity_decode(mpfields($objArticle->id,$mp_field['description'],"<br/><br><br />"))."]]>",
          'full_description'  => "<![CDATA[".html_entity_decode(mpfields($objArticle->id,$mp_field['fulldescription'],"<br/><br><br />"))."]]>",
          'group_code'        => '',
          'media_notice_url'  => '',
          'media_url_1'       => $destinataire_lien[0],
          'media_url_2'       => isset($destinataire_lien[1]) && $destinataire_lien[1] != '' ? $destinataire_lien[1] : '',
          'media_url_3'       => isset($destinataire_lien[2]) && $destinataire_lien[2] != '' ? $destinataire_lien[2] : '',
          'media_url_4'       => isset($destinataire_lien[3]) && $destinataire_lien[3] != '' ? $destinataire_lien[3] : '',
          'media_url_5'       => isset($destinataire_lien[4]) && $destinataire_lien[4] != '' ? $destinataire_lien[4] : '',
          'warranty'          => $objArticle->garantie->mois
     );   

     //Informations complémentaires selon le type d'article

     switch($mp_field['mcid']){
          
          //COMPLET
          case 'ACCESS-55': //Cuisine et Art de la table > Accessoires pour le vin

              /**
              * matter               OBLIGATOIRE : Matière
              * quantity             FACULTATIF  : Nombre d'unité
              * weight_g             OBLIGATOIRE : Poids
              * celar_accessory_type FACULTATIF  : Type accessoire cave à vin
              * wine_accessory_type  OBLIGATOIRE : Type d'accessoire vin (Etiquette bouteille;Bec verseur;Aérateur;Tire-bouchon;Refroidisseur;Coffret accessoires;Ice-bag;Bouchon;Pompe à vide;Carafe;Porte-bouteille:Crachoire;Doseur verre;N/A;NC)
              */
              
              //Contrôle sur les données obligatoires
              if($objArticle->poids != ''){

                  //Recherche du type
                  if(strpos($objArticle->libelle, 'tire-bouchon') !== false){
                      $type_accessoire = 'Tire-bouchon';
                  } else if(strpos($objArticle->libelle, 'bouchon') !== false){
                      $type_accessoire = 'Bouchon';
                  } else if(strpos($objArticle->libelle, 'carafe') !== false){
                      $type_accessoire = 'Carafe';
                  } else if(strpos($objArticle->libelle, 'crachoire') !== false){
                      $type_accessoire = 'Crachoire';
                  } else {
                      $type_accessoire = 'NC';
                  }

                  $aBalise = array(
                      'matter'               => $objArticle->matiere != '' ? $template->translate($objArticle->matiere) : 'NC',
                      'quantity'             => 1,
                      'weight_g'             => $objArticle->poids,
                      'celar_accessory_type' => '',
                      'wine_accessory_type'  => $type_accessoire
                 );

              } else {

                  $aBalise = array();
                  $aRefIncompletes[$objArticle->id][] = 'Poids manquant';
              }

               break;

          //COMPLET
          case 'ACCESS-87': //Electroménager > Accessoires PEM GEM > Accessoires Froid > Accessoires Cave a vin

               /**
                * brand_of_compatible_product OBLIGATOIRE : Marque compatible
                * model_of_compatible_product OBLIGATOIRE : Modèle compatible (IMPOSSIBLE A INDIQUER)
                * celar_accessory_type        OBLIGATOIRE : Type accessoire cave à vin
                */
               $aBalise = array(
                    'brand_of_compatible_product' => $objArticle->marque_libelle,
                    'model_of_compatible_product' => 'NC',
                    'celar_accessory_type'        => 'NC'
               );

               break;

          //COMPLET
          case 'ACCESS-88': //Electroménager > Accessoires PEM GEM > Accessoires Froid > Accessoires Réfrigérateur

               /**
                * brand_of_compatible_product OBLIGATOIRE : Marque compatible
                * model_of_compatible_product OBLIGATOIRE : Modèle compatible (IMPOSSIBLE A INDIQUER)
                * refrigerator_accessory_type OBLIGATOIRE : Type accessoire réfrigérateur
                */
               $aBalise = array(
                    'brand_of_compatible_product' => $objArticle->marque_libelle,
                    'model_of_compatible_product' => 'NC',
                    'refrigerator_accessory_type' => 'NC'
               );

               break;

          //COMPLET
          case 'BRIQUE': //Equipement du fumeur > Briquet

              /**
              * color         OBLIGATOIRE : Couleur
              * is_electrical FACULTATIF  : Electrique
              * matter        OBLIGATOIRE : Matière
              * rechargeable  FACULTATIF  : Rechargeable
              * lighter_type  FACULTATIF  : Type de briquet
              */
             
              $aBalise = array(
                  'color'         => $objArticle->couleur != '' ? $template->translate($objArticle->couleur) : 'NC', //OBLIGATOIRE
                  'is_electrical' => 'NC',
                  'matter'        => $objArticle->matiere != '' ? $template->translate($objArticle->matiere) : 'NC', //OBLIGATOIRE
                  'rechargeable'  => 'NC',
                  'lighter_type'  => 'NC'
              );

              break;

          //COMPLET
          case 'CARAFE-2': //Cuisine et Art de la table > Carafes > Carafe à Vin

               /**
                * capacity_l OBLIGATOIRE : Capacité (litres)
                * shape      OBLIGATOIRE : Forme
                * matter     OBLIGATOIRE : Matière
                */
               
              //Contrôle sur les données obligatoires
              if($objArticle->capacite_l != ''){

                  $aBalise = array(
                      'capacity_l' => $objArticle->capacite_l != '' ? $objArticle->capacite_l : 'NC', //OBLIGATOIRE, //OBLIGATOIRE
                      'shape'      => 'NC', //OBLIGATOIRE
                      'matter'     => $objArticle->matiere != '' ? $template->translate($objArticle->matiere) : 'NC', //OBLIGATOIRE
                  );

              } else {

                  $aBalise = array();
                  $aRefIncompletes[$objArticle->id][] = 'Capacité manquante';
              }

               break;

          //COMPLET
          case 'CAVEAC': //Equipement du fumeur > Cave à cigare

               /**
                * color                OBLIGATOIRE : Couleur
                * with_hygrometer      OBLIGATOIRE : Fonction hygromètre
                * dimensions_height_m  FACULTATIF  : Hauteur
                * dimensions_width_cm  OBLIGATOIRE : Largeur
                * dimensions_length_cm OBLIGATOIRE : Longueur
                * cigar_number         FACULTATIF  : Nombre de cigare
                */
               
                if($objArticle->largeur != '' && $objArticle->profondeur != ''){

                    //Recherche sur inclusion de l'hygromètre
                    $aHygrometre = array(
                        'Avec fonction hygromètre',
                        'Sans fonction hygromètre'
                    );
                    if($objArticle->hygrometre != '' && $objArticle->hygrometre != '/article/hygrometre/Non' && $objArticle->hygrometre != '/article/hygrometre/En_option'){
                        $has_hygro = $aHygrometre[0];
                    } else {
                        $has_hygro = $aHygrometre[1];
                    }

                    $aBalise = array(
                        'color'                => $objArticle->couleur != '' ? $template->translate($objArticle->couleur) : 'NC',
                        'with_hygrometer'      => $has_hygro,
                        'dimensions_height_m'  => $objArticle->hauteur / 100,
                        'dimensions_width_cm'  => $objArticle->largeur / 10,
                        'dimensions_length_cm' => $objArticle->profondeur / 10,
                        'cigar_number'         => ''
                    );

                } else {

                    $aBalise = array();
                    $aRefIncompletes[$objArticle->id][] = 'Largeur et/ou longueur non renseignée(s)';
                }

               break;

          //COMPLET
          case 'CAVEAV': //Electroménager > GEM > Froid > Cave à vin

               /**
                * energy_label                           FACULTATIF  : Classe énergétique (A/A+/A++/A+++/B/C/D/E/F/G)
                * energy_label_media_url                 OBLIGATOIRE : Etiquette énergétique (URL)
                * manufacturer_technical_sheet_media_url FACULTATIF  : Fiche constructeur (URL)
                * bottles_number                         OBLIGATOIRE : Nombre de bouteilles
                * with_glass_door                        OBLIGATOIRE : Porte vitrée (Porte vitrée/Sans Porte vitrée)
                * wine_cellar_type                       OBLIGATOIRE : Type de cave à vin (Vieillissement/Service/Multi-températures)
                * installation                           FACULTATIF  : Type de pose (Pose libre/Encastrable)
                * availability_when_available_spare_part FACULTATIF  : Pièces détachées
                */
               
                //Adaptation du type de porte
                if($objArticle->type_porte_id == 2){
                    $type_porte = 'Porte vitrée';
                } else {
                    $type_porte = 'Sans Porte vitrée';
                }

                if($mp_field['wine_cellar_type'] == 'Multi-usages') $mp_field['wine_cellar_type'] = 'Multi-températures';
                if($mp_field['installation'] == 'Intégrable') $mp_field['installation'] = 'Encastrable';

                $aBalise = array(
                    'energy_label'                           => $objArticle->class_energie != '' ? $objArticle->class_energie : 'NC',
                    'energy_label_media_url'                 => $objArticle->etiquette_energie, //OBLIGATOIRE
                    'manufacturer_technical_sheet_media_url' => '',
                    'bottles_number'                         => in_array($objArticle->capacite,$aCapacite) ? $objArticle->capacite : getCloserCapacity($objArticle->capacite), //OBLIGATOIRE
                    'with_glass_door'                        => $type_porte, //OBLIGATOIRE
                    'wine_cellar_type'                       => mpfields($objArticle->id,$mp_field['wine_cellar_type']), //OBLIGATOIRE
                    'installation'                           => mpfields($objArticle->id,$mp_field['installation']), 
                    'availability_when_available_spare_part' => ''
                );

               break;

          //COMPLET
          case 'CENDRI': //Equipement du fumeur > Cendriers

               /**
                * color                OBLIGATOIRE : Couleur
                * diameter_cm          OBLIGATOIRE : Diamètre
                * is_electrical        OBLIGATOIRE : Electrique
                * dimensions_height_cm OBLIGATOIRE : Hauteur
                * matter               OBLIGATOIRE : Matière
                * use_type             FACULTATIF : Usage
                */
               
              if($objArticle->largeur != ''){
              
                  $aBalise = array(
                      'color'                => $objArticle->couleur != '' ? $template->translate($objArticle->couleur) : 'NC',
                      'diameter_cm'          => $objArticle->largeur, //OBLIGATOIRE
                      'is_electrical'        => 'NC',
                      'dimensions_height_cm' => $objArticle->hauteur / 10,
                      'matter'               => $objArticle->matiere != '' ? $template->translate($objArticle->matiere) : 'NC', //OBLIGATOIRE,
                      'use_type'             => 'NC'
                 );

              } else {

                  $aBalise = array();
                  $aRefIncompletes[$objArticle->id][] = 'Aucune hauteur renseignée';
              }

               break;

          //COMPLET
          case 'CLIMAT-3': //Chauffage, ventilation, climatisation et aération > Climatisation > Climatiseur de cave

              if($objArticle->poids != '' && $objArticle->largeur != ''){

                  $aBalise = array(
                      'dimensions_height_cm'                   => $objArticle->hauteur != '' ? $objArticle->hauteur / 10 : '',
                      'dimensions_width_cm'                    => $objArticle->largeur / 10, //OBLIGATOIRE
                      'dimensions_length_cm'                   => $objArticle->profondeur != '' ? $objArticle->profondeur / 10 : '', 
                      'noise'                                  => $objArticle->bruit != '' ? $objArticle->bruit : '',
                      'weight_kg'                              => $objArticle->poids * 1000, //OBLIGATOIRE
                      'availability_when_available_spare_part' => '',
                      'cooling_power'                          => ''
                 );

              } else {

                  $aBalise = array();
                  $aRefIncompletes[$objArticle->id][] = 'Largeur et/ou poids non renseigné(s)';
              }

               break;

          //COMPLET
          case 'CUISIN-5': //Livres > Vie pratique > Cuisine et Vins

              $aBalise = array(
                    'book_format'       => 'NC', //OBLIGATOIRE
                    'book_language'     => 'NC', //OBLIGATOIRE
                    'book_author'       => 'NC', 
                    'book_release_date' => 'NC', //OBLIGATOIRE
                    'cooking_book_type' => 'NC'  //OBLIGATOIRE
               );

               break;

          //COMPLET
          case 'DESHUM': //Chauffage, ventilation, climatisation et aération > Ventilation et aération > Déshumidificateur et humidificateur

              if($objArticle->hauteur != '' && $objArticle->largeur != '' && $objArticle->profondeur != '' && $objArticle->poids != '' && $objArticle->puissance != ''){

                  $aBalise = array(
                      'with_clock'                             => 'NC', 
                      'availability_when_available_spare_part' => '',
                      'capacity_l'                             => $objArticle->capacite_l != '' ? $objArticle->capacite_l : '', 
                      'dimensions_height_cm'                   => $objArticle->hauteur / 10, //OBLIGATOIRE
                      'dimensions_length_cm'                   => $objArticle->profondeur / 10, //OBLIGATOIRE
                      'dimensions_width_cm'                    => $objArticle->largeur / 10, //OBLIGATOIRE
                      'noise'                                  => $objArticle->bruit != '' ? $objArticle->bruit : '',
                      'weight_kg'                              => $objArticle->poids * 1000,  //OBLIGATOIRE
                      'power_watts'                            => $objArticle->puissance  //OBLIGATOIRE
                 );

              } else {

                  $aBalise = array();
                  $aRefIncompletes[$objArticle->id][] = 'Largeur et/ou largeur et/ou profondeur et/ou puissance non renseignée(s)';
              }

               break;

          //COMPLET
          case 'ETUIET-2': //Equipement du fumeur > Etui et coupe cigare

               /**
                * color                OBLIGATOIRE : Couleur
                * diameter_cm          FACULTATIF : Diamètre
                * dimensions_height_cm FACULTATIF : Hauteur
                * matter               OBLIGATOIRE : Matière
                */
               
               if($objArticle->couleur != '' && $objArticle->matiere != ''){

                   $aBalise = array(
                        'color'                => $objArticle->couleur != '' ? $template->translate($objArticle->couleur) : '',
                        'diameter_cm'          => '',
                        'dimensions_height_cm' => $objArticle->hauteur != '' ? $objArticle->hauteur / 10 : '',
                        'matter'               => $objArticle->matiere != '' ? $template->translate($objArticle->matiere) : 'NC'
                   );
               } else {

                  $aBalise = array();
                  $aRefIncompletes[$objArticle->id][] = 'Couleur et/ou matière non renseignée(s)';
               }

               break;

          //COMPLET
          case 'FONTAI-1': //Cuisine et Art de la table > Fontaine et distributeur de vin

              $aBalise = array(
                    'bottles_number' => in_array($objArticle->capacite,$aCapacite) ? $objArticle->capacite : getCloserCapacity($objArticle->capacite),
                    'capacity_l'     => $objArticle->capacite_l != '' ? $objArticle->capacite_l : ''
               );

               break;

          //COMPLET
          case 'PLATCL': //Cuisine et Art de la table > Accessoires de table > Plat, cloche à fromage

               /**
                * tableware_and_ustensill_matter OBLIGATOIRE : Matière de vaisselle/ustensile
                */
               $aBalise = array(
                    'tableware_and_ustensill_matter' => 'NC'
               );

               break;

          //COMPLET
          case 'RAFRAI-1': //Cuisine et Art de la table > Sceau à champagne et rafraîssisseur de bouteille > Rafraîssisseur de bouteille

               /**
                * color       OBLIGATOIRE : Couleur
                * is_isotherm OBLIGATOIRE : Isotherme
                * matter      OBLIGATOIRE : Matière 
                */
               $aBalise = array(
                    'color'       => $objArticle->couleur != '' ? $template->translate($objArticle->couleur) : 'NC',
                    'is_isotherm' => 'NC',
                    'matter'      => $objArticle->matiere != '' ? $template->translate($objArticle->matiere) : 'NC'
               );

               break;

           //COMPLET
          case 'RANGEM-3': //Meubles et rangements > Rangements > Rangements à bouteilles

               /**
                * color                OBLIGATOIRE : Couleur
                * dimensions_height_cm FACULTATIF  : Hauteur
                * dimensions_width_cm  FACULTATIF  : Largeur
                * dimensions_length_cm FACULTATIF  : Longueur
                * matter               OBLIGATOIRE : Matière
                * bottles_number       OBLIGATOIRE : Nombre de bouteilles
                */
               
               if($objArticle->capacite > 0){

                  $aBalise = array(
                      'color'                => $objArticle->couleur != '' ? $template->translate($objArticle->couleur) : 'NC',
                      'dimensions_height_cm' => $objArticle->hauteur > 0 ? $objArticle->hauteur / 10 : '',
                      'dimensions_width_cm'  => $objArticle->largeur > 0 ? $objArticle->largeur / 10 : '',
                      'dimensions_length_cm' => $objArticle->profondeur > 0 ? $objArticle->profondeur / 10 : '',
                      'matter'               => $objArticle->matiere != '' ? $template->translate($objArticle->matiere) : 'NC',
                      'bottles_number'       => in_array($objArticle->capacite,$aCapacite) ? $objArticle->capacite : getCloserCapacity($objArticle->capacite)
                 );

               } else {

                  $aBalise = array();
                  $aRefIncompletes[$objArticle->id][] = 'Capacité non renseignée';
               }
               
               break;          

          //COMPLET
          case 'REFRIG-2': //Electroménager > GEM > Froid > Réfrigérateur Mini Bar

              if($objArticle->puissance != '' && $objArticle->largeur > 0){

                  $aBalise = array(
                      'power_watts'                            => $objArticle->puissance,
                      'energy_label'                           => $objArticle->class_energie != '' ? $objArticle->class_energie : 'NC',
                      'dimensions_height_m'                    => $objArticle->hauteur > 0 ? $objArticle->hauteur / 10 : '',
                      'dimensions_length_cm'                   => $objArticle->profondeur > 0 ? $objArticle->profondeur / 10 : '',
                      'dimensions_width_cm'                    => $objArticle->largeur / 10,
                      'energy_label_media_url'                 => $objArticle->etiquette_energie,
                      'voltage_v'                              => 0, //Valeur obligatoire mais irrécupérable dans notre BDD
                      'availability_when_available_spare_part' => ''
                 );

              } else {

                  $aBalise = array();
                  $aRefIncompletes[$objArticle->id][] = 'Puissance et/ou largeur non renseignée';
              }

               break;

          //COMPLET
          case 'SCEAUA-1': //Cuisine et Art de la table > Sceau à champagne et rafraîssisseur de bouteille > Sceau à bouteille

                $aBalise = array(
                      'color'       => $objArticle->couleur != '' ? $template->translate($objArticle->couleur) : 'NC',
                      'is_isotherm' => 'NC'
                 );

               break;

          //COMPLET
          case 'SERVIC-2': //Cuisine et Art de la table > Verres > Service de verres

               /**
                * capacity_cl           OBLIGATOIRE : Capacité (cl)
                * compatible_dishwasher OBLIGATOIRE : Compatible lave-vaisselle
                * color                 OBLIGATOIRE : Couleur
                * matter                OBLIGATOIRE : Matière
                * batch                 OBLIGATOIRE : Unité par lot 
                */
               
               if($objArticle->capacite_l > 0){

                  $aBalise = array(
                      'capacity_cl'           => $objArticle->capacite_l * 100,
                      'compatible_dishwasher' => 'NC',
                      'color'                 => $objArticle->couleur != '' ? $template->translate($objArticle->couleur) : 'NC',
                      'matter'                => $objArticle->matiere != '' ? $template->translate($objArticle->matiere) : 'NC',
                      'batch'                 => 'NC'
                 );

               } else {

                  $aBalise = array();
                  $aRefIncompletes[$objArticle->id][] = 'Capacité non renseignée';
               }

          case 'VERREA-4': //Cuisine et Art de la table > Verres > Verre à vin

               /**
                * capacity_cl           OBLIGATOIRE : Capacité
                * compatible_dishwasher OBLIGATOIRE : Compatible lave-vaisselle
                * color                 OBLIGATOIRE : Couleur
                * matter                OBLIGATOIRE : Matière
                * batch                 OBLIGATOIRE : Unité par lot 
                */
               
               if($objArticle->capacite_l > 0){

                  $aBalise = array(
                      'capacity_cl'           => $objArticle->capacite_l * 100,
                      'compatible_dishwasher' => 'NC',
                      'color'                 => $objArticle->couleur != '' ? $template->translate($objArticle->couleur) : 'NC',
                      'matter'                => $objArticle->matiere != '' ? $template->translate($objArticle->matiere) : 'NC',
                      'batch'                 => 'NC'
                 );

               } else {

                  $aBalise = array();
                  $aRefIncompletes[$objArticle->id][] = 'Capacité non renseignée';
               }

               break;

          case 'VETEME-17': //Vêtements de travail et uniformes > Vêtements de cuisine

              $aBalise = array(
                    'adult_gender'      => $objArticle->capacite_l * 100,
                    'universal_size'    => 'NC',
                    'matter'            => $objArticle->matiere != '' ? $template->translate($objArticle->matiere) : 'NC',
                    'color'             => $objArticle->couleur != '' ? $template->translate($objArticle->couleur) : 'NC',
                    'work_clothes_type' => 'NC'
               );

               break;

          case 'REFRIG-5': //Electroménager > GEM > Froid > Réfrigérateur US

                $aBalise=array(
                    'color'             => $objArticle->couleur != '' ? $template->translate($objArticle->couleur) : 'NC',
                    'distributor_function' => 'NC',
                    'freezer_pos'       => 'NC',
                    'has_water_fountain'=> 'NC',
                    'total_net_capacity'=> $objArticle->capacite_l,
                    'energy_label'      => $objArticle->class_energie != '' ? $objArticle->class_energie : 'NC',
                    'energy_label_media_url' => $objArticle->etiquette_energie
                );
              
               break;

          /*case 'THERMO-13': //Outils de mesure eau et air > Thermomètres et hygromètres pour l'air

               $xml .= ""
               //FACULTATIF : Avec effets Sonores
               . "<with_sound_effects></with_sound_effects>"
               //OBLIGATOIRE : Digital/Mercure
               . "<digital_or_mercur></digital_or_mercur>"
               //FACULTATIF : Fonction horloge
               . "<with_clock></with_clock>"
               //OBLIGATOIRE : Infrarouge
               . "<is_infrared></is_infrared>"
               //OBLIGATOIRE : Mémoire de mesure
               . "<it_memory_measurement></it_memory_measurement>"
               //OBLIGATOIRE : Type de thermomètre
               . "<thermometer_type></thermometer_type>"
               //OBLIGATOIRE : Type d'affichage
               . "<it_display_type></it_display_type>";

               break;*/
     }

      if(!empty($aBalise)){

          $xml .= "<product>";

          foreach($aMainBalise as $balise => $valeur){
              $xml .= '<'.$balise.'>'.$valeur.'</'.$balise.'>';
          }

          foreach($aBalise as $balise => $valeur){
              $xml .= '<'.$balise.'>'.$valeur.'</'.$balise.'>';
          }

          $xml .= "</product>";

      } else if(!isset($aBalise)) {
          $aRefIncompletes[$objArticle->id][] = 'Aucun attribut défini pour la catégorie de ce produit';
      }
  }

     return $xml;
}

//Fonction pour CREATION OFFRES
function offer($article_id, $enabled){
     global $DB_site2;
     global $tva;
     global $template;
	
	
	
	
	////////////////////////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////////////////////////
	// début de Gestion des soldes
	////////////////////////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////////////////////////
	 $tabProduitsSoldes = array(
		4212 => floatval('79.2'), //	ACI-OEN300L
		3079 => floatval('359.1'), //	ACI-WNA100CE
		870 => floatval('39.2'), //		ACI-CLI421
		3800 => floatval('55.2'), //	ACI-CLI120
		5219 => floatval('254.15'), //	ACI-CLI579E
		2550 => floatval('98.1'), //	ACI-MCC101
		2554 => floatval('219.12'), //	ACI-MCC104
		5400 => floatval('539'), //		ACI-CVS120E
		4817 => floatval('854'), //		ACI-AVI465
		5195 => floatval('119.2'), //	ACI-CLI489
		4741 => floatval('229') //		ACI-DOM700
	);
	
	$datedebutsoldes=mktime(0,0,0,2,5,2018); // le 5 fevrier 2018
	$datefinsoldes=mktime(0,0,0,2,13,2018); // le 16 fevrier 2018
	////////////////////////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////////////////////////
	// fin de gestion des soldes
	////////////////////////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////////////////////////
	
	$objArticle = new produit($DB_site2, 'FR', 'base', $template->tva_ref(), $article_id);
  
     $frais_port = new frais_port($DB_site2, array($objArticle->id), 57, $objArticle->prix_ttc);
     $port_ttc = ceil($frais_port->prixht * (1+$tva->taux_normal/100));
     $objArticle->prix_ttc = $objArticle->prix_ttc > 0 ? $objArticle->prix_ttc + $port_ttc : 0;
	
	if(time() > $datedebutsoldes && time() < $datefinsoldes && array_key_exists($article_id, $tabProduitsSoldes)){
		$objArticle->discount_ttc=$tabProduitsSoldes[$article_id];
		$objArticle->date_debut_remise=date("Y-m-d",$datedebutsoldes);
		$objArticle->date_fin_remise=date("Y-m-d",$datefinsoldes);
	} else {
		$objArticle->discount_ttc="";
		$objArticle->date_fin_remise="";
		$objArticle->date_debut_remise="";
	}

     //Délais de livraison
     $tabLivraison = delailivraison($objArticle->id);

     $expedition_delay = $tabLivraison['delailivraison'] > 2 ? $tabLivraison['delailivraison'] - 2 : 1;
     $expedition_delay = getDelaisOuvres(date('Y-m-d'), $expedition_delay); //Ajout des jours des jours fériés & WE dans le calcul

     if($objArticle->active != 1 || $objArticle->commandable != 1 || $enabled != 1 || (!empty($tabLivraison['date_appro']) && mktime(0,0,0,date('m'),date('d')+7,date('Y')) < $tabLivraison['date_appro'])){
          $quantite_article = 0;
          $expedition_delay=1;
     } else {
          $quantite_article = 50;
     }

     $quantite_alerte = 2;
     $etat_offre = 11;

     //Informations de base du fichier offre
     $xml .= ""
     . "<offer>"
          //OBLIGATOIRE : Identifiant unique de l'offre
          . "<sku>".$objArticle->id."</sku>"
          //OBLIGATOIRE : Identifiant unique de produit (pour un product-id-type donné)
          . "<product-id>".$objArticle->code_barre."</product-id>"
          //OBLIGATOIRE : Type d’identifiant product-id (EAN/SHOP_SKU/SKU)
          . "<product-id-type>EAN</product-id-type>"
          //OBLIGATOIRE : Description de l'offre
          . "<description></description>"
          //OBLIGATOIRE : Description interne, visible uniquement sur le Back-Office
          . "<internal-description></internal-description>"
          //OBLIGATOIRE : Prix de vente TTC, incluant toutes les taxes (Ecotaxe, DEEE…) (Un point sépare les centimes)
          . "<price>".$objArticle->prix_ttc."</price>"
          //FACULTATIF : Informations complémentaires concernant le prix ?
          . "<price-additional-info></price-additional-info>"
          //RECOMMANDE : Quantité disponible en stock
          . "<quantity>".$quantite_article."</quantity>"
          //FACULTATIF : Seuil de quantité minimale en stock déclenchant une alerte
          . "<min-quantity-alert>".$quantite_alerte."</min-quantity-alert>"
          //OBLIGATOIRE : Code d’état de l’offre. Celle-ci est obligatoirement neuve (code "11")
          . "<state>".$etat_offre."</state>"
          //FACULTATIF : Premier jour de disponibilité de l’offre. Si le champ est vide, l’offre n’a pas de date de départ
          . "<available-start-date></available-start-date>"
          //FACULTATIF : Dernier jour de disponibilité de l’offre. Si le champ est vide, l’offre n’a pas de date de fin
          . "<available-end-date></available-end-date>"
          //FACULTATIF : Code classe logistique. Laisser vide pour prendre celle définie par défaut pour la catégorie de produit
          . "<logistic-class></logistic-class>"
          //FACULTATIF : KEZAKO ?
          . "<favorite-rank></favorite-rank>"
          //FACULTATIF : Prix remisé TTC en Euros
          . "<discount-price>".$objArticle->discount_ttc."</discount-price>"
          //. "<discount-price>".$objArticle->prix_remise_ttc."</discount-price>" //On n'envoi aucun prix remisé à rue du commerce
          //FACULTATIF : Date de début de promotion. Format aaaa-mm-jj
          . "<discount-start-date>".$objArticle->date_debut_remise."</discount-start-date>"
          //. "<discount-start-date>".$objArticle->date_debut_remise."</discount-start-date>" //On n'envoi aucun prix remisé à rue du commerce
          //FACULTATIF : Date de fin de promotion. Format aaaa-mm-jj
          . "<discount-end-date>".$objArticle->date_fin_remise."</discount-end-date>"
          //. "<discount-end-date>".$objArticle->date_fin_remise."</discount-end-date>" //On n'envoi aucun prix remisé à rue du commerce
          //OBLIGATOIRE : Délai d'expédition. Temps de préparation de la commande en jours ouvrés jusqu'à la remise au transporteur
          . "<leadtime-to-ship>".$expedition_delay."</leadtime-to-ship>"
     . "</offer>";

     return $xml;
}

function getCloserCapacity($capacite){
     global $aCapacite;

     foreach($aCapacite as $la_capacite){
          if($capacite < $la_capacite) return $la_capacite;

          $max = count($aCapacite);
          return $aCapacite[$max-1];
     }
}

nice($aRefIncompletes);
