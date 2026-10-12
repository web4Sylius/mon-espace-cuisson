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
//$rootpath = "/var/www/vhosts/ma-cave-a-vin.fr/httpdocs/";
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

     //Récupération du pays
     $sql = "SELECT paysid, libelle";
     $sql .= " FROM pays";
     $sql .= " WHERE paysid = ".$article['paysid'];
     $pays = $DB_site2->query_first($sql);

     //Récupération des visuels de l'article
     $sql = "SELECT articlephotoid";
     $sql .= " FROM articlephoto";
     $sql .= " WHERE artid = ".$article['artid'];
     $sql .= " ORDER BY position";
     $r = $DB_site2->query($sql);
     $photo = array();
     while($im = $DB_site2->fetch_array($r)){
          $photo[] = $im[0];
     }

     //Vérification de l'intitulé de coloris, pour qu'il soit conforme à la norme imposée par Cdiscount
     if(!$coloris){
          $couleur_produit = $template->translate($article['coloris']);
     } else {
          $couleur_produit = $coloris['libelleFR'];
     }
     if(!in_array($couleur_produit, $tabCouleurs)){
          $couleur_produit = '';
     }

     $profondeur = !empty($article['profondeur']) ? $article['profondeur']/10 : '';
     $largeur    = !empty($article['largeur']) ? $article['largeur']/10 : '';
     $hauteur    = !empty($article['hauteur']) ? $article['hauteur']/10 : '';
     $poid       = !empty($article['poid']) ? $article['poid']/1000 : '';

     //Attribution des valeurs de colonne
     $line[0]  = $article['artid'];           //Obligatoire - SKU (alphanum.)
     $line[1]  = $ean[0];                     //Obligatoire - EAN (num.)
     $line[2]  = $marque['libelle'];          //Obligatoire - Marque (alphanum.)
     $line[3]  = 'Standard';                  //Obligatoire - Nature du produit (alphanum.)
     $line[4]  = $mp_field['code_categorie']; //Obligatoire - Code catégorie (alphanum.)
     $line[5]  = $mp_field['libelle_court'];  //Obligatoire - Libellé court panier (alphanum.)
     $line[6]  = $mp_field['libelle_long'];   //Obligatoire - Libellé long produit (alphanum.)
     $line[7]  = str_replace(';','-',$mp_field['description']); //Obligatoire - Description produit (alphanum.)
     $line[8]  = "https://www.ma-cave-a-vin.fr/configurations/www.ma-cave-a-vin.fr/images/produits/".$article['artid'].".jpg"; //Obligatoire - Image 1 (jpeg) (url)
     $line[9]  = ""; //Inutile - Sku famille
     $line[10] = ""; //Inutile - Taille (borné)
     $line[11] = ""; //Inutile - Couleur marketing
     $line[12] = ""; //Optionnel - Description marketing
     $line[13] = isset($photo[0]) ? "https://www.ma-cave-a-vin.fr/configurations/www.ma-cave-a-vin.fr/images/produits/".$article['artid']."_".$photo[0].".jpg" : "";
     $line[14] = isset($photo[1]) ? "https://www.ma-cave-a-vin.fr/configurations/www.ma-cave-a-vin.fr/images/produits/".$article['artid']."_".$photo[1].".jpg" : "";
     $line[15] = isset($photo[2]) ? "https://www.ma-cave-a-vin.fr/configurations/www.ma-cave-a-vin.fr/images/produits/".$article['artid']."_".$photo[2].".jpg" : "";
     $line[16] = ""; //Optionnel - Navigation / classification / rayon
     $line[17] = ""; //Inutile - ISBN
     $line[18] = ""; //Inutile - MFPN
     $line[19] = $profondeur; //Optionnel - Longueur (cm)
     $line[20] = $largeur;    //Optionnel - Largeur (cm)
     $line[21] = $hauteur;    //Optionnel - Hauteur (cm)
     $line[22] = $poid;       //Optionnel - Poids (kg)
     $line[23] = ""; //Inutile - Avertissement(s)
     $line[24] = ""; //Inutile - Commentaire
     $line[25] = ""; //Inutile - Couleurs
    // $line[26] = ""; //Inutile - Avertissement
    // $line[27] = ""; //Optionnel - Capacité
    // $line[28] = $article['energy_label']; //Optionnel - Classe d'efficacité énergétique
    // $line[29] = ""; //Inutile - Compatibilité feux
    // $line[30] = ""; //Inutile - Confort
    // $line[31] = ""; //Inutile - Classe d'efficacité énergétique (doublon)
     $line[26] = $couleur_produit; //Optionnel - Couleur principale
    // $line[33] = ""; //Inutile - Epaisseur du matelas
    // $line[34] = ""; //Inutile - Finition
    // $line[35] = ""; //Inutile - Fonction / Usage
    // $line[36] = ""; //Inutile - Forme
     $line[27] = ""; //Inutile - Genre
    // $line[38] = ""; //Inutile - Largeur
    // $line[39] = ""; //Inutile - Largeur du couchage
     $line[28] = ""; //Inutile - Licence
   //  $line[41] = ""; //Inutile - Longueur
   //  $line[42] = ""; //Inutile - Longueur du couchage
   //  $line[43] = ""; //Inutile - Matière de la structure
    // $line[44] = ""; //Inutile - Matière du revêtement
    // $line[45] = ""; //Inutile - Nombre de places
    // $line[46] = ""; //Inutile - Réfrigérateur|Volume utile total
    // $line[47] = ""; //Inutile - Relaxation
    // $line[48] = ""; //Inutile - Type d'angle
    // $line[49] = ""; //Inutile - Type de canapé
    // $line[50] = ""; //Inutile - Type de chauffe
    // $line[51] = ""; //Inutile - Type de lit
    // $line[52] = ""; //Inutile - Type de matelas
    // $line[53] = ""; //Inutile - Type de piscine
     $line[29] = ""; //Sport
     $line[30] = ""; //Inutile - Type de public
  

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

$template=new template("FR",$DB_site2);

//Préparation du fichier csv pour le GEM
$csv =  new xlexport("csv");
$csv->name            = "export_cdiscount_produits_".strtotime(date("d-m-Y H:i"));
$csv->csvsep          = ";";
$csv->striptags       = false;
$filename_source      = $rootpath.$mcav_dossier.$csv->name.'.csv';

//Tableau des couleurs acceptées par Cdiscount
//Si une couleur d'article ne correspond à aucune de celles ci-dessous, elle sera remplacée par "Non communiqué"
//La couleur apparaissant dans l'intitulé, ce n'est pas un problème
$tabCouleurs = array(
     'Beige',
     'Blanc',
     'Bleu',
     'Gris',
     'Jaune',
     'Marron',
     'Noir',
     'Orange',
     'Rose',
     'Rouge',
     'Vert',
     'Violet'
);

//Listing des entêtes du fichier CSV
$head = array(
    "Référence vendeur",                    //Obligatoire
    "EAN (Facultatif pour Mode et Maison)", //Obligatoire
    "Marque",                               //Obligatoire
    "Nature du produit",                    //Obligatoire
    "Code catégorie",                       //Obligatoire
    "Libellé court panier",                 //Obligatoire
    "Libellé long produit",                 //Obligatoire
    "Description produit",                  //Obligatoire
    "Image 1 (jpeg)",                       //Obligatoire
    "Sku famille",                          //Inutile
    "Taille (borné)",                       //Inutile
    "Couleur marketing",                    //Inutile
    "Description marketing",                //Optionnel
    "Image 2 (jpeg)",                       //Optionnel
    "Image 3 (jpeg)",                       //Optionnel
    "Image 4 (jpeg)",                       //Optionnel
    "Navigation / classification / rayon",  //Optionnel
    "ISBN",                                 //Inutile
    "MFPN",                                 //Inutile
    "Longueur (cm)",                        //Optionnel
    "Largeur (cm)",                         //Optionnel
    "Hauteur (cm)",                         //Optionnel
    "Poids (kg)",                           //Optionnel
    "Avertissement(s)",                     //Inutile
    "Commentaire",                          //Inutile
    "Couleurs",                             //Inutile
    //"Avertissement",                        //Inutile
    //"Capacité",                             //Optionnel
    //"Classe d'efficacité énergétique",      //Optionnel
    //"Compatibilité feux",                   //Inutile
    //"Confort",                              //Inutile
    //"Consommation|Classe d'efficacité énergétique", //Optionnel
    "Couleur principale",                   //Optionnel
    //"Epaisseur du matelas",                 //Inutile
    //"Finition",                             //Optionnel
    //"Fonction / Usage",                     //Optionnel
    //"Forme",                                //Inutile
    "Genre",                                //Inutile
    //"Largeur",                              //Inutile
    //"Largeur du couchage",                  //Inutile
    "Licence",                              //Inutile
    //"Longueur",                             //Inutile
    //"Longueur du couchage",                 //Inutile
    //"Matière de la structure",              //Inutile
    //"Matière du revêtement",                //Inutile
    //"Nombre de places",                     //Inutile
    //"Réfrigérateur|Volume utile total",     //Inutile
    //"Relaxation",                           //Inutile
    //"Type d'angle",                         //Inutile
    //"Type de canapé",                       //Inutile
    //"Type de chauffe",                      //Inutile
    //"Type de lit",                          //Inutile
    //"Type de matelas",                      //Inutile
    //"Type de piscine",                      //Inutile
    "Sports",
    "Type de public",                       //Inutile
    //"Type de sommier"                       //Inutile
    
);

//Insertion des entêtes dans le fichier, avec séparateur ; et sans les " entre les termes
$csv->addrow($head, false, true);

//Récupération de tous les produits actifs et commandables du site
$sql = "SELECT a.*";
$sql .= " FROM article a";
$sql .= " INNER JOIN categorie c USING(catid)";
$sql .= " WHERE active = '1'";
$sql .= " AND commandable = '1'";
$sql .= " GROUP BY artcode";
$sql .= " ORDER BY a.artid";
$articles = $DB_site2->query($sql);
while ($article = $DB_site2->fetch_array($articles)){
     $mp_field = array();

     //Récupération de tous les produits que l'on souhaite vendre sur la market place Cdiscount
     $sql = "SELECT enabled";
     $sql .= " FROM market_place_champs";
     $sql .= " WHERE market_place = 'cdiscount'";
     $sql .= " AND artcode LIKE '".$article['artcode']."'";
     $allenabled = $DB_site2->query_first($sql);

     //Si le produit est activé et qu'il qu'il n'est pas en cours de réappro
     if($allenabled[0] == "1" && $article['date_appro'] < $today){

          //Récupération des visuels du produit dans ses différents coloris
          $sql = "SELECT ac.image as imagecaractvalid, cv.image, ac.caractvalid, ac.artid, cv.".$libelle;
          $sql .= " FROM article_caractval ac";
          $sql .= " INNER JOIN caracteristiquevaleur cv USING(caractvalid)";
          $sql .= " WHERE ac.artid = '".$article['artid']."'";
          $sql .= " AND cv.caractid = '1'";
          $coloriss = $DB_site2->query($sql);

          $xml .= product($article);
     }
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

$date = date("d-m-Y H:i");
$action = "Mise a jour des produits";
$market_place = "cdiscount";
//mise à jour du suivi
$sql = "INSERT INTO suivi_export SET ";
$sql .= " user = '$_COOKIE[admin_username]'";
$sql .= ", time = '$date'";
$sql .= ", action = '$action'";
$sql .= ", market_place = '$market_place'";
$DB_site2->query($sql);
$_POST['URL'] = $_POST['URL']."&update=1";
header('Location: '.$_POST['URL']);
exit;