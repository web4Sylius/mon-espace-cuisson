<?php
require_once __DIR__.'/../../secrets.php';
if(isset($_SERVER['SERVER_NAME']) && strpos($_SERVER['SERVER_NAME'], 'localhost') !== false){
    $rootpath = "d:/wamp/www/ma-cave-a-vin/";
} else if(isset($_SERVER['SERVER_NAME']) && strpos($_SERVER['SERVER_NAME'], 'test2015') !== false) {
    $rootpath = "/home/ma-cave-a-vin-2015/";
} else if(isset($_SERVER['SERVER_NAME']) && strpos($_SERVER['SERVER_NAME'], 'test') !== false) {
    $rootpath = $_SERVER['DOCUMENT_ROOT']."/";
} else {
    $rootpath   = "/var/www/vhosts/ma-cave-a-vin.fr/httpdocs/";
}
$_SERVER['HTTP_HOST']="www.ma-cave-a-vin.fr";
include_once($rootpath."class/host.php");
$host_conf = new Host();
$host=$host_conf->host;
$nostat   = 1;

require_once($rootpath."configurations/$host/config_mysql.php");
require_once($rootpath."class/mysql.php");
require_once($rootpath."class/devises.class.php");
require_once($rootpath."class/template.class.php");
require_once($rootpath."admin/mpfields.php");
require_once($rootpath."admin/export/xlexport.php");
require_once($rootpath."admin/ftp.php");
require_once($rootpath."class/DAOgarantie.class.php");
require_once($rootpath."class/DAOarticle.class.php");
require_once($rootpath."class/garantie.class.php");
require_once($rootpath."class/fonctions.php");
require_once($rootpath."class/stock.class.php");

//Production
$mcav_dossier  = 'admin/export/cdiscount/';

//Test
/*$ftp_serveur  = 'ma-cave-a-vin.fr';
$ftp_user     = TEST_FTP_USER;
$ftp_password = TEST_FTP_PASSWORD;*/

ini_set("max_execution_time",180);
ini_set("display_errors","on");

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

function product($article, $coloris=false){
     global $DB_site2;
     global $template;
     global $csv;
     global $headkeys;
     global $tabCouleurs;

     $line = array();

     //Remplacement des contenus de champs paramétré
     $sql = "SELECT champ, valeur";
     $sql .= " FROM market_place_catid";
     $sql .= " WHERE market_place = 'cdiscount'";
     $sql .= " AND catid = '".$article['catid']."'";
     $requete = $DB_site2->query($sql);
     while($champ = $DB_site2->fetch_array($requete)){
          $mp_field[$champ['champ']] = mpfields($article, $champ['valeur']);
     }

     //Informations sur la marque
     $sql = "SELECT m.*";
     $sql .= " FROM article_marque am";
     $sql .= " INNER JOIN marque m";
     $sql .= " USING(marqueid)";
     $sql .= " WHERE am.artid = ".$article['artid'];
     $marque = $DB_site2->query_first($sql);

     //Récupération du code barre en fonction du coloris
     $sql = "SELECT codebarre";
     $sql .= " FROM code_barre";
     $sql .= " WHERE artid = '".$article['artid']."'";
     $sql .= ($coloris != false ? " AND caractvalid = ".$coloris['caractvalid'] : "");
     $ean = $DB_site2->query_first($sql);

     //Récupération de la meilleure garantie gratuite
     $lesGaranties = DAOgarantie::loadMaxGarantieArtByPays($DB_site2,$template->site_code(),'FR',57,$article['artid']);
     $garantieDeBaseAffiche="";
     if(count($lesGaranties) > 0){
          for ($i=0; $i < count($lesGaranties); $i++){
               $duree        = $lesGaranties[$i]->duree;
               $dureeAf      = $duree;
               $typeGarantie = $template->translate("/article/garantie_type/garantie",'F');

               if($lesGaranties[$i]->cumul == 1){
                   //On cherche dans le tableau qui contient toutes les garanties offertes celle du fournisseur de produit
                   $pointer=DAOgarantie::searchGarantieToAddExtension($DB_site,$lesGaranties[$i]->type_garantie,$lesGaranties,$lesGaranties[$i]->garantieid);
                   //On prend la durée de cette garantie pour l'ajouter à l'extension
                   $dureeAf      += $lesGaranties[$pointer]->duree;
                   $typeGarantie = $template->translate("/article/garantie_type/extension_garantie",'F')." +";
               }

               $an = $template->translate("/article/garantie_type/an");
               if($lesGaranties[$i]->duree>1){
                    $an = $template->translate("/article/garantie_type/ans");
               }

               //Pour la meilleure garantie incluse/gratuite
               if($i == 0){
                    $ppi       = $lesGaranties[$i]->ppi;
                    $prix      = $lesGaranties[$i]->prix;

                    $dureeDeBaseAf = $dureeAf;

                    if($dureeAf != 99){
                         $anneeGarantie = $dureeAf." ".$an;
                    } else {
                         $anneeGarantie = $template->translate("/article/garantie_type/a_vie",'F');
                    }

                    $garantieDeBaseAffiche = utf8_decode($anneeGarantie." ".$template->translate("/article/garantie_type/".$lesGaranties[$i]->type_garantie));

                    $auLieuDe = "";
                    if($ppi > $prix){
                         $auLieuDe = " ".$template->translate("/article/garantie_type/au_lieu_de")." ".$ppi."&euro;";
                    }
               }
          }
     }

     //Récupération du pays
     $sql = "SELECT paysid, libelle";
     $sql .= " FROM pays";
     $sql .= " WHERE paysid = ".$article['paysid'];
     $pays = $DB_site2->query_first($sql);
     
     $tabLivraison = delailivraison($article['artid']);
     if(empty($tabLivraison['date_appro']) && $article['date_appro'] > 0) $tabLivraison['date_appro'] = $article['date_appro'];

     /*
      * Conditions pour que le produit soit désactivé (quantité 0) :
      * - Produit désactivé de la vente sur MCAV
      * - Produit non commandable sur MCAV
      * - Produit désactivé sur CDiscount
      * - Date de réappro supérieure à 7 jours
      */
     if($article['active'] != 1 || $article['commandable'] != 1 || $article['enabled'] != 1 || (!empty($tabLivraison['date_appro']) && mktime(0,0,0,date('m'),date('d')+7,date('Y')) < $tabLivraison['date_appro']) || $tabLivraison['delailivraison']-2 > 10){
          $quantity = 0;
     } else {
          $quantity = 10;
     }

     //Récupération du prix de l'article
     $article_price = article_price($article['artid'], false, "base", false);
     if($article_price == 0) $article_price = 1;
     
     //Attribution des valeurs de colonne
     $line[0]  = $article['artid']; //Obligatoire - SKU
     $line[1]  = $ean[0];           //Obligatoire - EAN
     $line[2]  = "Neuf - Neuf";     //Obligatoire - Sous état du produit ?
     $line[3]  = $quantity;         //Obligatoire - Stock
     $line[4]  = $article_price;    //Obligatoire - Prix (¤)(TTC)
     $line[5]  = $article['tauxchoisi'] == 2 ? '5.5' : '20'; //Obligatoire - TVA (%)
     $line[6]  = $article['ecopart']; //Obligatoire - Eco Part(¤)
     $line[7]  = "0";  //Obligatoire - DEA(¤) ?
     $line[8]  = "";  //Facultatif - Unité de conditionnement
     $line[9]  = "";  //Facultatif - Conditionnement du produit
     $line[10]  = ""; //Facultatif - Prix de référence (¤)(TTC)
     $line[11]  = !empty($garantieDeBaseAffiche) ? "Garantie ".$garantieDeBaseAffiche : ""; //Facultatif - Commentaire offre
     $line[12] = ""; //Inutile
     $line[13] = ""; //Inutile
     $line[14] = ""; //Inutile
     $line[15] = ""; //Inutile
     $line[16] = ""; //Inutile
     $line[17] = ""; //Inutile
     $line[18] = ""; //Inutile
     $line[19] = ""; //Inutile
     $line[20] = ""; //Inutile
     $line[21] = $tabLivraison['delailivraison']-2; //Obligatoire
     $line[22] = $article_price >= 49 ? 0 : 5; //Obligatoire
     $line[23] =  ""; //Facultatif
     $line[24] = $article_price >= 49 ? 0 : 5; //Obligatoire
     $line[25] = ""; //Facultatif
     $line[26] = $article_price >= 49 ? 0 : 5; //obligatoire
     $line[27] = ""; //Facultatif

     //Insertion du produit dans le CSV, avec séparateur ; et sans les " entre les termes
     $csv->addrow($line, false, true);
}

$libelle     = "libelleFR";
$description = "descriptionFR";

$today = mktime(0,0,0);

$DB_site2=new DB_Sql;
$DB_site2->appname='boutique';
$DB_site2->appshortname='boutique';
$DB_site2->database=$DBname;
$DB_site2->server=$db_server;
$DB_site2->user=$DBusername;
$DB_site2->password=$DBpassword;
$DB_site2->connect();

$DB_site=clone $DB_site2;

$template=new template("FR",$DB_site2);

//Préparation du fichier csv pour le GEM
$csv =  new xlexport("csv");
$csv->name            = "export_cdiscount_offres_".strtotime(date("d-m-Y H:i"));
$csv->csvsep          = ";";
$csv->striptags       = false;
$filename_source      = $rootpath.$mcav_dossier.$csv->name.'.csv';

//Listing des entêtes du fichier CSV
$head = array(
    "Votre référence",      //Obligatoire
    "EAN",                  //Obligatoire
    "Sous état du produit", //Obligatoire
    "Stock",                //Obligatoire
    "Prix (¤)(TTC)",        //Obligatoire
    "TVA (%)",              //Obligatoire
    "Eco Part(¤)",          //Obligatoire
    "DEA(¤)",               //Obligatoire
    "Unité de conditionnement",   //Facultatif
    "Conditionnement du produit", //Facultatif
    "Prix de référence (¤)(TTC)", //Facultatif
    "Commentaire offre",          //Facultatif
    "Mécanique commerciale", //Inutile
    "Date (jj/mm/aaaa)",     //Inutile
    "Heure (hh:mm)",         //Inutile
    "Date (jj/mm/aaaa)",     //Inutile
    "Heure (hh:mm)",         //Inutile
    "Remise (%)",            //Inutile
    "Prix de référence Soldes (¤)(TTC)", //Inutile
    "S'aligner automatiquement sur la meilleure offre", //Inutile
    "Plus bas prix autorisé (¤)(TTC)", //Inutile
    "Délai de préparation (nb jours ouvrés max)",                 //Inutile
    "Principal (¤)",     //Inutile
    "Additionnel (¤) Facultatif",     //Inutile
    "Principal (¤)",      //Inutile
    "Additionnel (¤) Facultatif",      //Inutile
    "Principal (¤)", //Inutile  
    "Additionnel (¤) Facultatif"  //Inutile
    
);

//Insertion des entêtes dans le fichier, avec séparateur ; et sans les " entre les termes
$csv->addrow($head, false, true);

//Récupération de tous les produits actifs et commandables du site
$sql = "SELECT a.*, a_s.active, mpc.enabled";
$sql .= " FROM market_place_champs mpc";
$sql .= " INNER JOIN article a ON (a.artcode = mpc.artcode)";
$sql .= " INNER JOIN article_site a_s ON (a_s.artid = a.artid)";
$sql .= " WHERE 1";
$sql .= " AND mpc.market_place = 'cdiscount'";
$sql .= " AND a_s.site_code    = 'base'";
$sql .= " GROUP BY a.artid";
$sql .= " ORDER BY a.catid, a.artcode";
$articles = $DB_site2->query($sql);
while ($article = $DB_site2->fetch_array($articles)){
     $mp_field = array();

     //Si le produit est activé et qu'il qu'il n'est pas en cours de réappro
     $xml .= product($article);
}

//Suppression de la sauvegarde du 15ème jour précédent
@unlink($rootpath.$mcav_dossier.substr($csv->name,0,strrpos($csv->name,'_')+1).date('dmY',mktime(0,0,0,date('m'),date('j')-15,date('Y'))).'.csv');
@unlink($filename_source);

//Création du fichier CSV sur le serveur de MCAV
$f=fopen($filename_source,"w");
if($f){
     fputs($f,$csv->buffer);
     fclose($f);
} else {
     exit("<span style=\"color: red;\">Le fichier CSV n'a pas pu être généré, vérifiez qu'il n'existe pas déjà</span>");
}

if(isset($_POST['URL']) && $_POST['URL'] != ''){
	$date = date("d-m-Y H:i");
	$action = "Mise a jour des offres";
	$market_place = "cdiscount";
	//mise à jour du suivit
	//$bdd = new PDO('mysql:host=localhost;dbname=macave;charset=utf8', 'root', '');
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