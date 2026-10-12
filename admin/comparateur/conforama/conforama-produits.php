<?php

ini_set("max_execution_time",300);
ini_set("display_errors",1);

$prov = "comp" ;

header('Content-Type: text/html; charset=utf-8');

$nostat    = 1;
$compteur  = 0;

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
require_once($rootpath."admin/export/xlexport.php");


$libelle     = "libelleFR";
$description = "descriptionFR";

$today = mktime(0,0,0)+86400;


$template = new template("FR",$DB_site2);

//Tableau permettant de récupérer le bon libellé de capacité à envoyer à Delamaison
$aCapacite = array(
    array(
        'min' => 0,
        'max' => 50,
        'libelle' => 'Jusqu\'à 50 bouteilles'
    ),
    array(
        'min'     => 51,
        'max'     => 150,
        'libelle' => 'De 50 à 150 bouteilles'
    ),
    array(
        'min' => 150,
        'max' => 300000, //pas de limite max, si un jour on vend un produit qui stocke plus de 300 000 bouteilles... réhausser la limite :-)
        'libelle' => 'Plus de 150 bouteilles'
    )
);

//Tableau permettant de récupérer le bon libellé de couleur à envoyer à Delamaison
$aCouleur = array(
    'Noir'                   => 'Noir',
    'Bordeaux'               => 'Rouge',
    'Lie de vin'             => 'Rouge',
    'Acier brossé'           => 'Gris',
    'Inox'                   => 'Gris',
    'Style Inox sans traces' => 'Gris',
    'Gris anthracite'        => 'Gris',
    'Argent'                 => 'Gris',
    'Métal'                  => 'Gris',
    'Silver'                 => 'Gris',
    'Noir et Rouge'          => 'Multicolore',
    'Noir et Inox'           => 'Multicolore',
    'Noir et silver'         => 'Multicolore',
    'Blanc et bois'          => 'Multicolore',
    'Terra'                  => 'Marron',
    'Marron'                 => 'Marron',
    'Platane tabac'          => 'Marron',
    'Blanc'                  => 'Blanc',
    'Prunier d\'automne'     => 'Marron'
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

function nicexml($var){
     $var = str_replace('<![CDATA[','',$var);
     $var = str_replace(']]','',$var); nicex($var);
     $parser = simplexml_load_string($var);

     nicex($parser);
}

//Fonction pour CREATION PRODUITS
function product($article_id){ 
     global $DB_site2;
     global $tvafrance;
     global $template;
     global $aCapacite;
     global $http_host;
     global $aRefIncompletes;
     global $aRem;
     global $aErreurs;
     global $aCouleur;
     global $compteur;
     global $rootpath;
     //global $csv;

     $objArticle = new produit($DB_site2, 'FR', 'base', $tvafrance, $article_id);

     //Champs à transmettre à Delamaison
     $sql = "SELECT champ, valeur";
     $sql .= " FROM market_place_catid";
     $sql .= " WHERE market_place = 'conforama'";
     $sql .= " AND catid = ".$objArticle->categorie[0]['categorie_id'];
     $requete = $DB_site2->query($sql);

     $mp_field = array();
     while($champ = $DB_site2->fetch_array($requete)){
          $mp_field[$champ['champ']] = $champ['valeur'];
     }
      
     $tabLivraison = delailivraison($objArticle->id);
     $delai_livraison = $tabLivraison['delailivraison'];

    //Pour l'instant, on n'exporte que les caves
    if($mp_field['categorie'] == 957){

        $aErreurs[$article_id]['ref'] = $objArticle->ref_aci;
        $aErreurs[$article_id]['erreurs'] = array();

        //Vérification des champs obligatoires
        if(!isset($aCouleur[$template->translate($objArticle->couleur)])) $aErreurs[$article_id]['erreurs']['couleur'] = 'Couleur '.$template->translate($objArticle->couleur).' manquante (non renseignée ou non traduite pour la marketplace)';
        if($mp_field['description'] == '')
			$aErreurs[$article_id]['erreurs']['desc'] = 'Description manquante';
        if(!is_numeric($objArticle->poids) || $objArticle->poids <= 0)
			$aErreurs[$article_id]['erreurs']['poids'] = 'Poids non renseigné';
        //if($delai_livraison > 8)
		//	$aErreurs[$article_id]['erreurs']['delai'] = 'Délai d\'expédition + livraison supérieur à 8 jours (en tenant compte d\'un éventuel délai de réapprovisionnement)';
        if($objArticle->visuel[0]['lien'] == ''){
            $aErreurs[$article_id]['erreurs']['visuel'] = 'Visuel manquant';
        } else {
            //Taille minimale des visuels 500x500
//             list($visuel_width,$visuel_height) = getimagesize($rootpath.$objArticle->visuel[0]['lien']);
//             if($visuel_width < 500 || $visuel_height < 500) $aErreurs[$article_id]['erreurs']['dimensions'] = 'Visuel trop petit ('.$visuel_width.'x'.$visuel_height.' au lieu de 500x500 minimum)';
        }
        
        //Ligne permettant d'afficher tous les coloris actuellement exportés
        //if(!in_array($template->translate($objArticle->couleur), $aRem)) $aRem[$article_id] = $template->translate($objArticle->couleur);
      
         $aMainBalise = array(
              'Nature'      => $mp_field['categorie'],
            /*  'couleur'     => $aCouleur[$template->translate($objArticle->couleur)], */
              'description' => "<![CDATA[".html_entity_decode(mpfields($objArticle->id,$mp_field['description'],"<br/><br><br />"))."]]>",
              'ean'         => $objArticle->code_barre,
              'urlImage1'   => $http_host."/image.php?artid=".$objArticle->id."&amp;dstWidth=500&amp;dstHeight=500&amp;nowm=1",
              'fabricant'   => mb_strtoupper($objArticle->marque_libelle),
              'shopSku'     => $objArticle->id,
              'productLabel'=> "<![CDATA[".html_entity_decode(mpfields($objArticle->id,$mp_field['titre'],"<br/><br><br />"))."]]>"
         );   
			// refFournisseur
			// 
		//Récupération des visuels de l'article
          $sql = "SELECT articlephotoid";
          $sql .= " FROM articlephoto";
          $sql .= " WHERE artid = ".$objArticle->id;
          $sql .= " ORDER BY position LIMIT 4";
          $r = $DB_site2->query($sql);
		  $iphoto=1;
          while($im = $DB_site2->fetch_array($r)){
			   $iphoto++;
			   $aMainBalise['urlImage'.$iphoto]= $http_host."/image.php?artid=".$objArticle->id."_".$im[0]."&amp;dstWidth=500&amp;dstHeight=500&amp;nowm=1";
          }
		  
		  

         if(count($aErreurs[$article_id]['erreurs']) == 0){

             //Informations complémentaires selon le type d'article
             switch($mp_field['categorie']){
                  
                  case '957':
                        
                        //Export exceptionnel pour demander des modifications de description
                        //$csv->addrow(array($objArticle->code_barre,html_entity_decode(mpfields($objArticle->id,$mp_field['description'],"<br/><br><br />"))), false, true);

                        $type_porte = '';
                        $usage      = '';

                        //Adaptation du type de porte
                        if($objArticle->type_porte_id == 2){
                            $type_porte = '989_1';
                        } else {
                            $type_porte = '989_2';
                        }

                        switch($objArticle->usage){
                            case 'Vieillissement':
                                $usage = '1012_2';
                                break;
                            case 'Conservation':
                                $usage = '1012_2';
                                break;
                            case 'Service':
							    $usage = '1012_1';
                                break;
                            case 'Multi-usages':
                            case 'Multiusages':
                                $usage = '1012_3';
                                break;
                            default:
                                $usage = '1012_3';
                              break;
                        }

                        $aBalise = array(
                              '928'       => $objArticle->capacite,/* nombre de bouteilles */
                              '989'       => $type_porte,
							  '1012'      => $usage,
							  '293958'    => '293958_2', /* connectée : 293958_1 = oui | 293958_2 = non */
							  '583'       => round($objArticle->hauteur / 10), /* hauteur en cm */
							  '584'       => round($objArticle->largeur / 10), /* largeur en cm */
							  '587'       => round($objArticle->profondeur / 10) /* profondeur en cm */
                        );

						
                       break;
             }

              if(!empty($aBalise)){
                  $xml .= "<product>";
                  foreach($aMainBalise as $balise => $valeur){
                      // $xml .= '<'.$balise.'>'.$valeur.'</'.$balise.'>';
					  $xml .= '<attribute><code>'.$balise.'</code><value>'.$valeur.'</value></attribute>';
                  }
                  foreach($aBalise as $balise => $valeur){
                      $xml .= '<attribute><code>'.$balise.'</code><value>'.$valeur.'</value></attribute>';
                  }
                  $xml .= "</product>";
              } else {
				  echo "Cet article n'a pas d'attribu ...<br>";
			  }

          $compteur++;
        } else {
			echo "EHHHH une erreur sur ".$article_id." <br>";
		//	print_r($aErreurs[$article_id]);
		}
    } else {
		echo "EHHHH mauvaise categorie sur ".$article_id." (".$mp_field['categorie'].")<br>";
	}

     return $xml;
}

/*
function getCloserCapacity($capacite){
     global $aCapacite;

     foreach($aCapacite as $capacite_infos){

          if($capacite >= $capacite_infos['min'] && $capacite <= $capacite_infos['max']){
              return $capacite_infos['libelle'];
          }
     }
}
*/

function sendInfos($methode, $postfields = array()){
    
    $oCurl = curl_init();      

    $typeMaj = 
    curl_setopt( $oCurl, CURLOPT_URL, 'https://conforama-prod.mirakl.net/api/'.$methode );

    //Authentification
    curl_setopt( $oCurl, CURLOPT_HTTPHEADER, array("Authorization: 2ed2c8dd-29ef-4cbb-9ca1-a1a4b65addd3") ); // 2ed2c8dd-29ef-4cbb-9ca1-a1a4b65addd3

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

/***************************************************/
/* TRAITEMENT DES DEMANDES DE CREATION DE PRODUITS */
/***************************************************/
$aOffresActuelles = array();
/*
$oOffers = sendInfos('offers');
foreach($oOffers->offers as $l_offre){
    $aOffresActuelles[] = $l_offre->shop_sku;
}
*/
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
   // if(!in_array($article['artid'], $aOffresActuelles)){ //On traite l'offre uniquement si elle n'existe pas déjà (traité dans la boucle ci-dessus)
         $aProduit[] = $article;
   // }
}

$name     = "conforama-produits.xml";
$filename = $rootpath."configurations/".$host."/exports/".$name;

if(count($aProduit) > 0){

    //Export exceptionnel pour demander des modifications de description
    /*$csv =  new xlexport("csv");
    $csv->name      = "export_delamaison_".strtotime(date("d-m-Y H:i"));
    $csv->csvsep    = ";";
    $csv->striptags = false;
    $csv->addrow(array('SKU/EAN','Description'), false, true);*/

    $xml = ""
    . "<?xml version=\"1.0\" encoding=\"UTF-8\"?>"
    . "<import>\n"
        . "<products>\n";

    foreach($aProduit as $article){
        $xml .= product($article['artid']);
		// echo "il envoi chez eux : ".$article['artid']." (on envoi)<br>";
    }

    $xml .= ""
        . "</products>\n"
    . "</import>\n";

    if(!$handle = fopen($filename, 'w')){
     echo "Impossible d'ouvrir le fichier ($filename)";
     exit;
    }
    fwrite($handle, $xml);
    fclose($handle);
	// echo $xml;
    //Export exceptionnel pour demander des modifications de description
    /*$filename_source = $rootpath."configurations/".$host."/exports/".$csv->name.".csv";
    $f = fopen($filename_source,"w");
    if($f){
        fputs($f,chr(239).chr(187).chr(191).$csv->buffer);
        fclose($f);
    }*/

    $postfields = array();
    $postfields['file'] = '@'.$filename;

    $response = sendInfos('products/imports', $postfields);

    if(isset($response->import_id) && is_numeric($response->import_id)){
        $msgLog = "<p style=\"color: green;\"><span style=\"font-weight: bold; font-size: 20px;\">".$compteur."</span> demandes de création de produits envoyées</p>";
    } else {
        $msgLog = "<p style=\"color: red;\">La demande de création n'a pas pu être finalisée. La marketplace a retourné une erreur.</p>";
    }
} else {
    $msgLog = "<p style=\"color: orange;\"><span style=\"font-weight: bold; font-size: 20px;\">Aucune produit à créer</p>";
}

if(count($aErreurs) > 0){
    
    $msgLog .= ""
    . "<table border=\"1\" cellpadding=\"0\" cellspacing=\"0\" style=\"border:1px solid; border-collapse: collapse; font-family: 'Open Sans',Arial,sans-serif;\">"
        . "<tr>"
            . "<td colspan=\"2\" style=\"padding: 2px 5px; text-align: center; font-weight: bold; font-size: 18px; background-color: rgb(21, 99, 163); color: white;\">Produits non importés</td>"
        . "</tr>";

    $has_errors = false;
    $cpt2       = 0;
    foreach($aErreurs as $artid => $les_erreurs){ 

        if(count($les_erreurs['erreurs']) > 0){

            $has_errors = true;
            $bg_color   = $cpt2%2 == 0 ? '#F9F9F9' : '#FFFFFF';
            $rowspan    = count($les_erreurs['erreurs']);

            $msgLog .= ""
            . "<tr>"
                . "<td rowspan=\"".$rowspan."\" style=\"padding: 3px 10px; font-size: 12px; background-color: ".$bg_color."\"><span style=\"font-weight: bold; text-decoration: underline;\">SKU</span> : ".$artid."<br><span style=\"font-weight: bold; text-decoration: underline;\">Réf</span>. : ".$les_erreurs['ref']."</td>";

            $cpt = 0;
            foreach($les_erreurs['erreurs'] as $l_erreur){

                if($cpt == 0){

                    $msgLog .= ""
                        . "<td style=\"padding: 3px 10px; font-size: 12px; background-color: ".$bg_color."\">".$l_erreur."</td>"
                    . "</tr>";

                } else {

                    $msgLog .= ""
                    . "<tr>"
                        . "<td style=\"padding: 3px 10px; font-size: 12px; background-color: ".$bg_color."\">".$l_erreur."</td>"
                    . "</tr>";
                }

                $cpt++;
            }

            $cpt2++;
        }
    }

    if(!$has_errors){

        $msgLog .= ""
        . "<tr>"
            . "<td colspan=\"2\" style=\"padding: 3px 10px; font-size: 12px;\">Aucune erreur</td>"
        . "</tr>";
    }

    $msgLog .= "</table>";
}

$mail = new email();
// $mail->destinataire = "webmaster@ma-cave-a-vin.fr";
$mail->destinataire = "webmaster2@ma-cave-a-vin.fr";
// $mail->to           = array("webmaster1@ma-cave-a-vin.fr");
$mail->to           = array("webmaster2@ma-cave-a-vin.fr");
$mail->expname      = "Ma Cave à Vin";
$mail->expediteur   = "contact@ma-cave-a-vin.fr";
$mail->subject      = "Conforama - Rapport d'intégration des produits - ".date('d/m/Y H:i:s');
$mail->body         = $msgLog; 
$mail->attachfile($filename,$name,"text/html");
$mail->sendmail();

$logpath=$rootpath."/admin/comparateur/log/conforama/";
copy($filename,$logpath."produits".time().".xml");
file_put_contents($logpath."retours".time().".html",$msgLog);
