<?php
require_once __DIR__.'/../../secrets.php';
$rootpath = "/var/www/vhosts/ma-cave-a-vin.fr/httpdocs/";
$host     = "www.ma-cave-a-vin.fr" ;
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

//Production
$mcav_dossier  = 'admin/export/priceminister/';

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
     global $tabMatieres;
     global $tvafrance;

     $line = array();
     
     $commentaire_annonce = " Achetez chez un Expert en caves à vin (couleur et inox) de 6 à 1000 bouteilles pour tous les usages : le vieillissement, la conservation, le service, la dégustation, et la préservation du vin et du champagne - Pour particuliers et professionnels - Livraison assurée par des professionnels en France et en Europe";
     
     //Remplacement des contenus de champs paramétré
     $sql = "SELECT champ, valeur";
     $sql .= " FROM market_place_catid";
     $sql .= " WHERE market_place = 'Priceminister'";
     $sql .= " AND catid = '".$article['catid']."'";
     $requete = $DB_site2->query($sql);
     while($champ = $DB_site2->fetch_array($requete)){
          $mp_field[$champ['champ']] = mpfields($article, $champ['valeur'],"<br/>");
     }

     //On continue le traitement uniquement si le produit fais partie de la catégorie "Gros Electroménager"
     if(strpos($mp_field['typefichier'],'Art de la table') !== false){

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

                         $garantieDeBaseAffiche = $anneeGarantie." ".$template->translate("/article/garantie_type/".$lesGaranties[$i]->type_garantie);
                    }
               }
          }

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
          
          //Récupération du prix de l'article
          $article_price = article_price($article['artid'], false, "base");
          $prix_public = round($article['prixconseilleht'] * (1 + ($tvafrance['taux1'] / 100)),2);
          
          //On regarde si cet article est lié à un autre dans le stock
          $sql = "SELECT artid";
          $sql .= " FROM stock_mini";
          $sql .= " WHERE artids LIKE '%".intval($article['artid'])."%'";
          $artPrincipal = $DB_site2->query_first($sql);

          $stock_id = $article['artid'];
          if(isset($artPrincipal['artid']) && !empty($artPrincipal['artid']) && !is_null($artPrincipal['artid'])) $stock_id = $artPrincipal['artid'];

          //Récupération de la date de réapprovisionnement pour cette référence
          $sql = "SELECT si2.date_livraison as date_reappro";
          $sql .= " FROM stock_item si2";
          $sql .= " WHERE si2.artid = ".intval($stock_id);
          $sql .= " AND si2.state = 0";
          $sql .= " ORDER BY si2.date_livraison";
          $sql .= " DESC LIMIT 0,1";
          $tabAppro = $DB_site2->query_first($sql);
          
          //Mise à jour de la quantité en fonction du stock du produit
          if(isset($tabAppro['date_reappro']) && !empty($tabAppro['date_reappro']) && mktime(0,0,0,date('m'),date('d')+7,date('Y')) < $tabAppro['date_reappro']){
               $quantity = 0;
          } else {
               $quantity = 10;
          }
          
          $commentaire_annonce .= (isset($garantieDeBaseAffiche) && !empty($garantieDeBaseAffiche) ? $garantieDeBaseAffiche.' - ' : '')."Délai de livraison : ".$article['delailivraison_txt'];
          
          //Vérification de l'intitulé de coloris, pour qu'il soit conforme à la norme imposée par Priceminister
          $couleur_produit = $template->translate($article['coloris']);
          if(!in_array($couleur_produit, $tabCouleurs)){
               $couleur_produit = 'Multicolore';
          }
          
          //Vérification de l'intitulé de matière, pour qu'il soit conforme à la norme imposée par Priceminister
          $matiere_produit = $template->translate($article['matiere']);
          if(!in_array($matiere_produit, $tabMatieres)){
               $matiere_produit = '';
          }
          
          //Attribution des valeurs de colonne
          $line[0]  = $article['artid'];                                         //Référence Parent       - OBLIGATOIRE
          $line[1]  = $article['artcode'];                                       //Référence Enfant (SKU) - OBLIGATOIRE
          $line[2]  = $ean[0];                                                   //Code-barres (EAN)      - OBLIGATOIRE
          $line[3]  = '';                                                        //Référence Fab. (MPN)   - INUTILE
          $line[4]  = $article_price;                                            //Prix de Vente          - OBLIGATOIRE
          $line[5]  = $prix_public;                                              //Prix Public
          $line[6]  = 'N';                                                       //Qualité                - OBLIGATOIRE
          $line[7]  = $quantity;                                                 //Quantité               - OBLIGATOIRE
          $line[8]  = $commentaire_annonce;                                      //Commentaire Annonce    - OBLIGATOIRE
          $line[9]  = '';                                                        //Commentaire Privé
          $line[10] = '';                                                        //Desc. Annonce Pers.
          $line[11]  = $mp_field['Type'];                                        //Type de Produit        - OBLIGATOIRE
          $line[12]  = $mp_field['Titre'];                                       //Titre                  - OBLIGATOIRE
          $line[13] = addslashes(str_replace(';',':',$mp_field['Description'])); //Description            - OBLIGATOIRE
          $line[14] = $marque['libelle'];                                        //Fabricant
          $line[15] = $couleur_produit;                                          //Coloris                - OBLIGATOIRE
          $line[16] = $matiere_produit;                                          //Matière dominante
          $line[17] = '';                                                        //Diamètre
          $line[18] = '';                                                        //Type de feux
          $line[19] = "http://www.ma-cave-a-vin.fr/configurations/www.ma-cave-a-vin.fr/images/produits/".$article['artid'].".jpg"; //Image Principale - CONSEILLE
          $line[20] = (isset($photo[0]) ? "http://www.ma-cave-a-vin.fr/configurations/www.ma-cave-a-vin.fr/images/produits/".$article['artid']."_".$photo[0].".jpg" : "").(isset($photo[1]) ? "#"."http://www.ma-cave-a-vin.fr/configurations/www.ma-cave-a-vin.fr/images/produits/".$article['artid']."_".$photo[1].".jpg" : "").(isset($photo[2]) ? "#"."http://www.ma-cave-a-vin.fr/configurations/www.ma-cave-a-vin.fr/images/produits/".$article['artid']."_".$photo[2].".jpg" : ""); //Images Secondaires - CONSEILLE
          $line[21] = '';                               //Code opération promo - INUTILE
          $line[22] = 'EXP';                            //Expédition, Retrait
          $line[23] = '+33(0)426030543';                //Téléphone
          $line[24] = '69003';                          //Code Postal
          $line[25] = 'France';                         //Pays
          $line[26] = '';                               //RSL - Expédié par PriceMinister - Rakuten
          $line[27] = $article['emballage_poids']*1000; //Poids du colis - OBLIGATOIRE
          $line[28] = '';                               //FRANCE
          $line[29] = '';                               //DROM_COM
          $line[30] = '';                               //EUROPE
          $line[31] = '';                               //MONDE

          //Insertion du produit dans le CSV, avec séparateur ; et sans les " entre les termes
          $csv->addrow($line, false, true);
     }
}

ini_set("display_errors", "1");

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
$csv->name            = "export_arttable";
$csv->csvsep          = ";";
$csv->striptags       = false;
$filename_source      = $rootpath.$mcav_dossier.$csv->name.'.csv';

//Tableau des couleurs acceptées par Priceminister
$tabCouleurs = array(
     'Argenté',
     'Beige',
     'Blanc',
     'Bleu',
     'Doré',
     'Ecru',
     'Gris',
     'Incolore',
     'Jaune',
     'Marron',
     'Multicolore',
     'Noir',
     'Orange',
     'Rose',
     'Rouge',
     'Vert',
     'Violet'
);

//Tableau des matières acceptées par Priceminister
$tabMatieres = array(
     'Acier',	
     'Acrylique',	
     'Aluminium',
     'Argent',
     'Bambou',	
     'Bois',	
     'Caoutchouc',
     'Cristal',	
     'Cuivre',	
     'Céramique',
     'Faïence',
     'Fonte',
     'Grès',
     'Inox',
     'Mélamine',	
     'Métal',
     'Papier',	
     'Pierre',	
     'Plastique',
     'Polypropylène',
     'Porcelaine',
     'Résine',
     'Silicone',
     'Terre cuite',
     'Verre'
);

//Listing des entêtes du fichier CSV
$head = array(
    "Référence Parent",
    "Référence Enfant (SKU)",
    "Code-barres (EAN)",
    "Référence Fabricant (MPN)",
    "Prix de Vente",
    "Prix Public",
    "Qualité",
    "Quantité",
    "Commentaire Annonce",
    "Commentaire Privé",
    "Description Annonce Personnalisée",
    "Type de Produit",
    "Titre",
    "Description",
    "Fabricant",
    "Coloris",
    "Matière",
    "Diamètre",
    "Type de feux",
    "Image Principale",
    "Images Secondaires",
    "Code opération promo",
    "Expédition, Retrait",
    "Téléphone",
    "Code Postal",
    "Pays",
    "RSL - Expédié par PriceMinister - Rakuten",
    "Poids du colis",
    "FRANCE",
    "DROM_COM",
    "EUROPE",
    "MONDE"    
);
$headkeys = array_flip($head);

//Insertion des entêtes dans le fichier, avec séparateur ; et sans les " entre les termes
$csv->addrow($head, false, true);

$sql = "SELECT 0 AS taux0, TVAtauxnormal AS taux1, TVAtauxreduit AS taux2";
$sql .= " FROM pays";
$sql .= "  WHERE paysid=57";
$tvafrance = $DB_site2->query_first($sql);

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

     //Récupération de tous les produits que l'on souhaite vendre sur la market place Priceminister
     $sql = "SELECT enabled";
     $sql .= " FROM market_place_champs";
     $sql .= " WHERE market_place = 'Priceminister'";
     $sql .= " AND artcode LIKE '".$article['artcode']."'";
     $allenabled = $DB_site2->query_first($sql);

     //Si le produit est activé et qu'il qu'il n'est pas en cours de réappro
     if($allenabled[0] == "1" && $article['date_appro'] < $today){
          
          //Priceminister ne gère pas les coloris différents !
          
          //Récupération des visuels du produit dans ses différents coloris
          /*$sql = "SELECT ac.image as imagecaractvalid, cv.image, ac.caractvalid, ac.artid, cv.".$libelle;
          $sql .= " FROM article_caractval ac";
          $sql .= " INNER JOIN caracteristiquevaleur cv USING(caractvalid)";
          $sql .= " WHERE ac.artid = '".$article['artid']."'";
          $sql .= " AND cv.caractid = '1'";
          $coloriss = $DB_site2->query($sql);*/

          //Ajout des produits dans le fichier CSV
          //if($DB_site2->num_rows($coloriss) == 0){
               $xml .= product($article);
          /*} else {
               while($coloris = $DB_site2->fetch_array($coloriss)){
                    $xml .= product($article, $coloris);
               }
          }*/
     }
}

//Suppression du fichier précédent
@unlink($filename_source);

//Création du fichier CSV sur le serveur de MCAV
$f=fopen($filename_source,"w");
if($f){
     fputs($f,$csv->buffer);
     fclose($f);
     
     exit("<span style=\"color: green;\">Le fichier a été généré, vous pouvez le consulter <a href=\"http://".$host."/".$mcav_dossier.$csv->name.".csv\">ici</a>.</span>");
} else {
     exit("<span style=\"color: red;\">Le fichier CSV n'a pas pu être généré, vérifiez qu'il n'existe pas déjà</span>");
}