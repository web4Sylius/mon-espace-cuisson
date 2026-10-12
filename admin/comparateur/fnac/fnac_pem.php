<?php
require_once __DIR__.'/../../../secrets.php';
include_once($_SERVER['DOCUMENT_ROOT'].'/admin/host_config_admin.php');

$nostat   = 1;

require_once($rootpath."class/devises.class.php");
require_once($rootpath."class/template.class.php");
require_once($rootpath."admin/mpfields.php");
require_once($rootpath."admin/export/xlexport.php");
require_once($rootpath."admin/ftp.php");
require_once($rootpath."class/DAOgarantie.class.php");
require_once($rootpath."class/DAOarticle.class.php");
require_once($rootpath."class/garantie.class.php");

//Production
$ftp_serveur      = 'ftp-01.fnac.com';
$ftp_user         = FNAC_FTP_USER;
$ftp_password     = FNAC_FTP_PASSWORD;
$fnac_dossier     = '/catalog_imports/submit/full/PEM/';
$mcav_dossier     = 'admin/export/fnac/';
$test_integration = false;

if($test_integration) header('Content-Type: text/html; charset=utf-8');

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

     //Remplacement des contenus de champs paramètrés
     $sql = "SELECT champ, valeur";
     $sql .= " FROM market_place_catid";
     $sql .= " WHERE market_place = 'fnac'";
     $sql .= " AND catid = '".$article['catid']."'";
     $requete = $DB_site2->query($sql);
     while($champ = $DB_site2->fetch_array($requete)){
          $mp_field[$champ['champ']] = mpfields($article, $champ['valeur'],"<br/><br><br />");
     }

     //On continue le traitement uniquement si le produit fais partie de la catégorie "Gros Electroménager"
     if(strpos($mp_field['meta-type'],'Petit électroménager') !== false){
			
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

          //Vérification de l'intitulé de coloris, pour qu'il soit conforme à la norme imposée par la Fnac
          if(!$coloris){
               $couleur_produit = $template->translate($article['coloris']);
          } else {
               $couleur_produit = $coloris['libelleFR'];
          }
          if(!in_array($couleur_produit, $tabCouleurs)){
               $couleur_produit = 'Non Communiqué';
          }
          
          //Attribution des valeurs de colonne
          $line[0]  = $ean[0];
          $line[1]  = $article['artid'];
          $line[2]  = $mp_field['meta-type'];
          $line[3]  = $mp_field['type'];
          $line[4]  = $mp_field['support'] == 'Oenologie' ? 'œnologie' : $mp_field['support'];
          $line[5]  = $mp_field['format'];
          $line[6]  = html_entity_decode($mp_field['titre'],ENT_COMPAT,'UTF-8');
          $line[7]  = str_replace(';','-',html_entity_decode($mp_field['description'],ENT_COMPAT,'UTF-8'));
          $line[8]  = $marque['libelle'];
          $line[9]  = "https://www.ma-cave-a-vin.fr/configurations/www.ma-cave-a-vin.fr/images/produits/".$article['artid'].".jpg";
          $line[10] = $couleur_produit;
          $line[11] = "";
          $line[12] = $garantieDeBaseAffiche;
          $line[13] = round($article['hauteur']/10,1)." cm";
          $line[14] = round($article['largeur']/10,1)." cm";
          $line[15] = round($article['profondeur']/10,1)." cm";
          $line[16] = round($article['poid']/1000)." kg";
          $line[17] = isset($photo[0]) ? "https://www.ma-cave-a-vin.fr/configurations/www.ma-cave-a-vin.fr/images/produits/".$article['artid']."_".$photo[0].".jpg" : "";
          $line[18] = isset($photo[1]) ? "https://www.ma-cave-a-vin.fr/configurations/www.ma-cave-a-vin.fr/images/produits/".$article['artid']."_".$photo[1].".jpg" : "";
          $line[19] = isset($photo[2]) ? "https://www.ma-cave-a-vin.fr/configurations/www.ma-cave-a-vin.fr/images/produits/".$article['artid']."_".$photo[2].".jpg" : "";
          $line[20] = "";
          $line[21] = "";
          $line[22] = "";
          $line[23] = "";
          $line[24] = "";
          $line[25] = "";
          $line[26] = "";

          //Insertion du produit dans le CSV, avec séparateur ; et sans les " entre les termes
          $csv->addrow($line, false, true);
     }
}

ini_set("display_errors", "1");

$libelle     = "libelleFR";
$description = "descriptionFR";

$today = mktime(0,0,0);

//Vérification de la présence d'une mise à jour avant de publier le fichier
/*$sql = "SELECT last_modified";
$sql .= " FROM market_place_champs";
$sql .= " WHERE market_place = 'fnac'";
$sql .= " AND enabled = 1";
$sql .= " AND DATE_FORMAT(FROM_UNIXTIME(last_modified), '%Y-%m-%d') = DATE_FORMAT(NOW(), '%Y-%m-%d')"; echo $sql;
$req = $DB_site2->query($sql);
if($DB_site2->num_rows($req) > 0){*/

     $template=new template("FR",$DB_site2);

     //Préparation du fichier csv pour le GEM
     $csv =  new xlexport("csv");
     $csv->name            = "fr_produits_pem_".strtotime(date("d-m-Y H:i"));
     $csv->csvsep          = ";";
     $csv->striptags       = false;
     $filename_source      = $rootpath.$mcav_dossier.$csv->name.'.csv';
     $filename_destination = $fnac_dossier.$csv->name.'.csv';

     //Tableau des couleurs acceptées par La Fnac
     //Si une couleur d'article ne correspond à aucune de celles ci-dessous, elle sera remplacée par "Non communiquée"
     //La couleur apparaissant dans l'intitulé, ce n'est pas un problème
     $tabCouleurs = array(
          'Argent',
          'Beige',
          'Blanc',
          'Bleu',
          'Bronze',
          'Gris',
          'Incolore',
          'Jaune',
          'Marron',
          'Métallique',
          'Multicolore',
          'Noir',
          'Or',
          'Orange',
          'Rose',
          'Rouge',
          'Vert',
          'Violet',
          'Non Communiquée'
     );

     //Listing des entêtes du fichier CSV
     $head = array(
         "EAN", //Obligatoire
         "SKU", //Obligatoire
         "Category", //Obligatoire
         "ItemType", //Obligatoire
         "Support", //Obligatoire
         "Format", //Obligatoire
         "Title", //Obligatoire
         "Description", //Obligatoire
         "Brand", //Obligatoire
         "MainImage", //Obligatoire
         "Colors", //Conseillé
         "AccessoriesIncluded", //Conseillé
         "Caution", //Conseillé
         "Height", //Conseillé
         "Length", //Conseillé
         "Width", //Conseillé
         "Weight", //Conseillé
         "SecondaryImage1", //Conseillé
         "SecondaryImage2", //Conseillé
         "SecondaryImage3", //Conseillé
         "TechnicalDetail", //Conseillé
         "DistinctionLegal", //Non obligatoire
         "WarningSecurity", //Non obligatoire
         "Batteryrequired", //Non obligatoire
         "BatteryrIncluded", //Non obligatoire
         "AccessoryObligatory", //Non obligatoire
         "AccessoryOptional" //Non obligatoire
     );
     $headkeys = array_flip($head);

     //Insertion des entêtes dans le fichier, avec séparateur ; et sans les " entre les termes
     $csv->addrow($head, false, true);

     //Récupération de tous les produits actifs et commandables du site
     $sql = "SELECT a.*";
     $sql .= " FROM article a";
     $sql .= " INNER JOIN categorie c USING(catid)";
     $sql .= " INNER JOIN article_site a_s USING(artid)";
     $sql .= " WHERE a_s.site_code LIKE 'base'";
     $sql .= " AND a_s.active = 1";
     $sql .= " AND commandable = '1'";
     $sql .= " GROUP BY artcode";
     $sql .= " ORDER BY a.artid";
     $articles = $DB_site2->query($sql);
     while ($article = $DB_site2->fetch_array($articles)){
          $mp_field = array();

          //Récupération de tous les produits que l'on souhaite vendre sur la market place FNAC
          $sql = "SELECT enabled";
          $sql .= " FROM market_place_champs";
          $sql .= " WHERE market_place = 'fnac'";
          $sql .= " AND artcode LIKE '".$article['artcode']."'";
          $allenabled = $DB_site2->query_first($sql);

          //Si le produit est activé et qu'il qu'il n'est pas en cours de réappro
          if($allenabled[0] == "1"){

               //Récupération des visuels du produit dans ses différents coloris
               /*$sql = "SELECT ac.image as imagecaractvalid, cv.image, ac.caractvalid, ac.artid, cv.".$libelle;
               $sql .= " FROM article_caractval ac";
               $sql .= " INNER JOIN caracteristiquevaleur cv USING(caractvalid)";
               $sql .= " WHERE ac.artid = '".$article['artid']."'";
               $sql .= " AND cv.caractid = '1'";
               $coloriss = $DB_site2->query($sql);

               //Ajout des produits dans le fichier CSV
               if($DB_site2->num_rows($coloriss) == 0){*/
                    $xml .= product($article);
               /*} else {
                    while($coloris = $DB_site2->fetch_array($coloriss)){
                         $xml .= product($article, $coloris);
                    }
               }*/
          }
     }

     //Suppression de la sauvegarde du 15ème jour précédent
     @unlink($rootpath.$mcav_dossier.substr($csv->name,0,strrpos($csv->name,'_')+1).date('dmY',mktime(0,0,0,date('m'),date('j')-15,date('Y'))).'.csv');
     @unlink($filename_source);

     //Création du fichier CSV sur le serveur de MCAV
     $f=fopen($filename_source,"w");
     if($f){
          fputs($f,chr(239).chr(187).chr(191).$csv->buffer);
          //fputs($f,$csv->buffer);
          fclose($f);
     } else {
          exit("<span style=\"color: red;\">Le fichier CSV n'a pas pu être généré, vérifiez qu'il n'existe pas déjà</span>");
     }

     //Copie du fichier sur le serveur de la FNAC
     $ftp = new ftp();
     $ftp->server = $ftp_serveur;
     $ftp->user   = $ftp_user;
     $ftp->pass   = $ftp_password;
      $result = $ftp->put($filename_source,$filename_destination,true);
      if($result){
          $_POST['URL'] = $_POST['URL']."&update=1";
          // header('Location: '.$_POST['URL']);
          // exit;
           exit("<span style=\"color: green;\">Fichier généré avec succès</span>");
      } else {
           exit("<span style=\"color: red;\">Erreur lors de l'enregistrement du fichier sur l'espace FTP de la Fnac</span>");
      }

/*} else {
     echo "Aucune référence à publier sur la FNAC";
}*/

if(isset($_POST['URL']) && $_POST['URL'] != ''){
	$date = date("d-m-Y H:i");
	$action = "Mise a jour des PEM";
	$market_place = "fnac";
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