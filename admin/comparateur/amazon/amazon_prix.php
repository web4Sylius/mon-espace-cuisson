<?php
include_once($_SERVER['DOCUMENT_ROOT'].'/admin/host_config_admin.php');

require_once($rootpath."class/devises.class.php");
require_once($rootpath."class/template.class.php");
require_once($rootpath."admin/mpfields.php");
require_once($rootpath."admin/export/xlexport.php");
require_once($rootpath."admin/ftp.php");
require_once($rootpath."class/DAOgarantie.class.php");
require_once($rootpath."class/DAOarticle.class.php");
require_once($rootpath."class/DAOtype.class.php");
require_once($rootpath."class/garantie.class.php");
require_once($rootpath."class/fonctions.php");
require_once($rootpath."class/stock.class.php");
require_once($rootpath."class/email.php");

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

ini_set('display_errors','on');
ini_set('max_execution_time', 300);

$libelle     = "libelleFR";
$description = "descriptionFR";

$template=new template("FR",$DB_site2);

$today=mktime(0,0,0);
$day7=date_create("+7 days")->settime(0,0,0)->gettimestamp();

$filename = 'amazon-prix.txt';
@unlink($filename);

//Ouverture du fichier en écriture
if (!$handle = fopen($filename, 'w')){
     echo "Impossible d'ouvrir le fichier (".$filename.")";
     exit;
}

//Définition des champs
$head = array("sku","price","minimum-seller-allowed-price","maximum-seller-allowed-price","quantity","leadtime-to-ship","fulfillment-channel") ;

//Séparation par tabulation
$tab = "	";
	
$handle = fopen($filename, 'w+') ;
fwrite($handle,implode($tab,$head));

$n = 0 ;

$stock=new stock($DB_site2);

$stockamazon=array();
$req=$DB_site2->query("SELECT id
FROM `fournisseur`
WHERE `type` LIKE 'fourn'
AND societe LIKE '%amazon%'");
while($f=$DB_site2->fetch_array($req)){
    $stockamazon[]=$f['id'];
}


//Récupération des articles, puis boucle sur chacun d'eux
$sql = "SELECT a.artid, a.commandable, a.date_appro, a_s.active, mpc.enabled";
$sql .= " FROM market_place_champs mpc";
$sql .= " INNER JOIN article a ON (a.artcode = mpc.artcode)";
$sql .= " INNER JOIN article_site a_s ON (a_s.artid = a.artid)";
$sql .= " WHERE 1";
$sql .= " AND mpc.market_place = 'amazon'";
$sql .= " AND a_s.site_code    = 'base'";
/*$sql .= " AND (";
$sql .= " (";
$sql .= "      a.artid NOT IN ("; //On ne met pas à jour les produits stockés chez Amazon, qui bénéficient d'un traitement à part
$sql .= "           SELECT si.artid";
$sql .= "           FROM stock_item si";
$sql .= "           WHERE si.artid = a.artid";
$sql .= "           AND si.lieu = '631641'";
$sql .= "     )";
$sql .= "     AND a.artid NOT IN (";
$sql .= "           SELECT si.artid";
$sql .= "           FROM stock_item si";
$sql .= "           WHERE si.artid = a.artid";
$sql .= "           AND si.lieu = '631658'";
$sql .= "     )";
$sql .= "     AND a.artid NOT IN (";
$sql .= "           SELECT si.artid";
$sql .= "           FROM stock_item si";
$sql .= "           WHERE si.artid = a.artid";
$sql .= "           AND si.lieu = '631932'";
$sql .= "     )";
$sql .= " )";
$sql .= " OR (a.artid IN (3123)))"; //CLI100 //Pour les produits gérés en stock chez Amazon mais que l'on souhaite malgré tout mettre à jour (arrêt temporaire du stockage chez Amazon par exemple)
*/
$sql .= " GROUP BY a.artid";
$sql .= " ORDER BY a.catid, a.artcode";
$articles = $DB_site2->query($sql);
$DB_site = clone $DB_site2;
while ($article=$DB_site2->fetch_array($articles)) {

    $article_price          = article_price($article['artid'], false, "base", false);
    $article_price_noformat = $article_price;
    if($article_price == 0) $article_price = 12000;
    $article_price = str_replace('.',',',$article_price);

    $article_price_min = $article_price;
    $article_price_max = $article_price + 20;

    $tabLivraison = delailivraison($article['artid']);
    if(empty($tabLivraison['date_appro']) && $article['date_appro'] > 0) $tabLivraison['date_appro'] = $article['date_appro'];

    $delailivraison = $tabLivraison['delailivraison'];
    if($delailivraison >= 15){
    $delailivraison = $delailivraison - 4; //Délais supérieurs à 15j, on indique le nombre réel - 4 jours
    } else {
    $delailivraison = $delailivraison - 2;
    }
    
    if($article['active'] != 1 || $article['commandable'] != 1 || $article['enabled'] != 1 || (!empty($tabLivraison['date_appro']) && $tabLivraison['date_appro']>$day7)){
        $quantity = 0;
    } else {
        $quantity = 100;
    }
    
    $delailivraison=max(1,min(30,$delailivraison));
    
    $amazitems=$stock->search($article['artid'],$stockamazon);
    if(count($amazitems)>0) $quantity=$delailivraison="";

    $contenu .= "\n";

    $contenu .= $article['artid'].$tab; //sku
    $contenu .= $article_price.$tab; //price
    $contenu .= $article_price_min.$tab; //minimum-seller-allowed-price
    $contenu .= $article_price_max.$tab; //maximum-seller-allowed-price
    $contenu .= $quantity.$tab; //quantity
    $contenu .= $delailivraison.$tab; //leadtime-to-ship //rajout delai livraison
    $contenu .= ''; //fulfillment-channel	
}

fwrite($handle, $contenu);
fclose($handle);

//Envoi du fichier par mail
$message =  "Bonjour,<br><br>";
$message .= "En PJ, le fichier de mise à jour des prix/quantités à publier dans l'<a href=\"https://sellercentral.amazon.fr/gp/homepage.html?\">interface vendeur Amazon</a>.<br><br>";
$message .= "Chemin d'accès : STOCK > Ajouter des produits par fichier > Charger votre fichier de stock (onglet) > CHARGER UN FICHIER > Fichier Prix et Quantité (Type de fichier)";

$mail = new email();
$mail->destinataire = "marketing1@ma-cave-a-vin.fr";
$mail->to           = array("webmaster1@ma-cave-a-vin.fr");
$mail->expname      = "Ma Cave à Vin";
$mail->expediteur   = "contact@ma-cave-a-vin.fr";
$mail->subject      = "Amazon - Fichier de mise à jour des prix/quantités - ".date('d/m/Y H:i:s');
$mail->body         = $message; 
$mail->attachfile($filename,"amazon-prix.txt","text/html");
$mail->sendmail();

//Exécution automatique du programme
if(isset($_GET['execution']) && $_GET['execution'] == 'manuelle'){
     header('Content-Description: File Transfer'); 
     header('Content-Type: application/force-download'); 
     header('Content-Disposition: attachment; filename='.$filename); 
     readfile($filename); 
}

//Exécution manuelle du programme
if(isset($_POST['URL']) && $_POST['URL'] != ''){
	$date = strtotime(date("d-m-Y H:i"));
	$newfilename = "amazon_rapide_prix".$date.".txt";
	$dest = $rootpath."/admin/comparateur/log/amazon/$newfilename";
     if (!is_dir("/admin/comparateur/log/amazon")) {
      mkdir("/admin/comparateur/log/amazon",0777);
     }
	copy($filename, $dest);

	$date = date("d-m-Y H:i");
	$action = "Mise à jour rapide des fichiers";
	$market_place = "amazon";
	//mise Ã  jour du suivit
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
?> 
