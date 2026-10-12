<?php

ini_set("display_errors",1);

$rootpath=dirname(dirname(__DIR__))."/";

include_once($_SERVER['DOCUMENT_ROOT']."/class/host.php");
$host_conf = new Host();
$host=$host_conf->host;
include_once($_SERVER['DOCUMENT_ROOT']."/configurations/".$host."/config_variables.php");

require_once($rootpath."configurations/$host/config_mysql.php");
require_once($rootpath."class/mysql.php");
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

ini_set('display_errors','on');
ini_set('max_execution_time', 300);

$libelle     = "libelleFR";
$description = "descriptionFR";

$DB_site2=new DB_Sql;
$DB_site2->appname='boutique';
$DB_site2->appshortname='boutique';
$DB_site2->database=$DBname;
$DB_site2->server=$db_server;
$DB_site2->user=$DBusername;
$DB_site2->password=$DBpassword;
$DB_site2->connect();

$DB_site = clone $DB_site2;

$template=new template("FR",$DB_site2);

$stock=new stock($DB_site2);

$filename = $rootpath."/admin/comparateur/log/amazon/amazon_global".time().".txt";

$today=mktime(0,0,0);
$day7=date_create("+7 days")->settime(0,0,0)->gettimestamp();
$contenu = "TemplateType=fptcustom     Version=2021.0317   TemplateSignature=UkVGUklHRVJBVElPTl9BUFBMSUFOQ0U=     settings=contentLanguageTag=fr_FR&feedType=113&headerLanguageTag=fr_FR&metadataVersion=MatprodVkxBUHJvZC0xMDc1&primaryMarketplaceId=amzn1.mp.o.A13V1IB3VIYZZH&templateIdentifier=de5444dc-aba2-4816-a9f2-3d0767c05c2e&timestamp=2021-03-17T13%3A19%3A36.889Z    Les 3 premières lignes sont réservées à l'utilisation par Amazon.com uniquement. Évitez de modifier ou supprimer les 3 premières lignes.";

//Définition des champs
$head1 = array("Type de produit","SKU du vendeur","Marque","ID du produit","Type d'ID du produit","Nom du produit","Fabricant","Numéro de pièce du fabricant","Catégorie du produit (Nœud)","Catégorie du produit (Nœud)","Prix standard","quantité","URL de l'image principale"," URL d'une autre image1","URL d'une autre image2","URL d'une autre image3","URL d'une autre image4","URL d'une autre image5","URL d'une autre image6","URL d'une autre image7","URL d'une autre image8","URL d'une image swap","Parenté","SKU Parent","Type de relation","Thème variation","Mettre à jour Supprimer","Description du produit","Référence","Avec ou sans freezer","gtin_exemption_reason","Termes de recherche","Mots-clés platinum","Mots-clés platinum","Mots-clés platinum","Mots-clés platinum","Mots-clés platinum","Taille","Couleur","Nuancier standard de couleur","Produit validé \"Energy Star\"","Consommation électrique annuelle","Unité de mesure de consommation annuelle d’énergie","Portable","Profondeur encastrable","Unité de mesure de la profondeur du compteur compatible","Options incluses","Options incluses","Options incluses","Options incluses","Type d'installation","Niveau de bruit","Unité de mesure du niveau de bruit","Spécificités","Spécificités","Spécificités","Spécificités","Spécificités","Voltage (tension)","Wattage (puissance)","Type de connectique","Type de connectique","Nombre de tiroirs","Capacité de congélation","Unité de mesure de la capacité du congélateur","Capacité de conservation d'aliments frais","Unité de mesure de la capacité en nourriture fraiche","Nombre de clayettes","Echelle des indices d’efficacité énergétique","Efficacité énergétique","style_name","Unité de mesure du voltage","pattern_name","Unité de mesure du Wattage","Unité de mesure de la clarté","Couleur dru ruban","Public cible","Caractéristiques clés du produit","Caractéristiques clés du produit","Caractéristiques clés du produit","Caractéristiques clés du produit","Caractéristiques clés du produit","Unité de mesure de la puissance chevaux","Unité de mesure de la capacité en flux d'air","shaft_style_type","DefrostSystemType","Classe climatique","Capacité","Poids à l'expédition","Unité de mesure du poids de l’expédition du site internet","Qualité de fabrication","VolumeCapacityName","Format du produit","Volume total","Unité de mesure du volume de rangement","Diamètre","Unité de mesure du diamètre de l’affichage de l’article","ItemThickness","Unité de mesure de l’épaisseur de l’article","Hauteur","Longueur","Largeur","Unité de mesure du volume de l’affichage de l’article","unit_count","item_display_volume","Unité de mesure du nom du volume de la capacité","size_map","maximum_pressure_unit_of_measure","item_display_length","Unité de mesure de la longueur de l’affichage de l’article","Unité de mesure des dimensions de l’article","unit_count_type","ID du centre de distribution","Hauteur du produit emballé","Unité de mesure de la hauteur du paquet","Largeur du produit emballé","Unité de mesure de la largeur du paquet","Longueur du produit emballé","Unité de mesure de la longueur du paquet","Poids du produit emballé","Unité de mesure du poids du paquet","Livraison spécifique nécessaire","Directive EU relative à la Sécurité des Jouets - Avertissement à propos de l’âge","Directive EU relative à la Sécurité des Jouets - Avertissement non relatif à l’âge","Directive EU relative à la Sécurité des Jouets - Avertissement non relatif à l’âge","Directive EU relative à la Sécurité des Jouets - Avertissement non relatif à l’âge","Directive EU relative à la Sécurité des Jouets - Avertissement non relatif à l’âge","Directive EU relative à la Sécurité des Jouets - Avertissement non relatif à l’âge","Directive EU relative à la Sécurité des Jouets - Avertissement non relatif à l’âge","Directive EU relative à la Sécurité des Jouets - Avertissement non relatif à l’âge","Directive EU relative à la Sécurité des Jouets - Avertissement non relatif à l’âge","Directive EU relative à la Sécurité des Jouets - Avertissement relatif à la langue","Directive EU relative à la Sécurité des Jouets - Avertissement relatif à la langue","Directive EU relative à la Sécurité des Jouets - Avertissement relatif à la langue","Directive EU relative à la Sécurité des Jouets - Avertissement relatif à la langue","Directive EU relative à la Sécurité des Jouets - Avertissement relatif à la langue","Directive EU relative à la Sécurité des Jouets - Avertissement relatif à la langue","Directive EU relative à la Sécurité des Jouets - Avertissement relatif à la langue","Directive EU relative à la Sécurité des Jouets - Avertissement relatif à la langue","Garantie","Description de la garantie","Pays/Région d'origine","Poids de l'article","item_weight_unit_of_measure","Piles fournies","Ce produit est-il ou contient-il une pile ou une batterie ?","Type/dimensions des piles ou batteries","Type/dimensions des piles ou batteries","Type/dimensions des piles ou batteries","Nombre de piles ou batteries","Nombre de piles ou batteries","Nombre de piles ou batteries","Composition des piles ou batteries Poids de la pile ou batterie (en grammes)","battery_weight_unit_of_measure","Nombre de cellules Lithium-Métal","Nombre de cellules Lithium-Ion","Emballage de la batterie lithium","Wattheures par pile ou batterie","lithium_battery_energy_content_unit_of_measure","Teneur en lithium (en grammes)","lithium_battery_weight_unit_of_measure","Réglementations applicables aux matières dangereuses","Réglementations applicables aux matières dangereuses","Réglementations applicables aux matières dangereuses","Réglementations applicables aux matières dangereuses","Réglementations applicables aux matières dangereuses","Numéro ONU","URL de la fiche de données de sécurité (FDS) Volume d'eau","item_volume_unit_of_measure","Point d'éclair (°C) ?","Fiche de produit","Étiquette d'efficacité énergétique","external_testing_certification","Pictogrammes de catégorisation/SGH (sélectionnez tout ce qui s'applique)","Pictogrammes de catégorisation/SGH (sélectionnez tout ce qui s'applique)","Pictogrammes de catégorisation/SGH (sélectionnez tout ce qui s'applique)","Devise","type d'état","Indications sur l'État","Quantité d'articles dans le lot vendu","Date de sortie date de parution","Date de remise en stock","délai de traitement","Prix réduit","Date de début de la remise","Date de fin de la remise","MaxAggregateShipQuantity","Taxe DEEE pour les produits électriques ou électroniques","devise de la taxe deee","Message cadeau disponible","Emballage cadeau disponible","Produit en arrêt de fabrication","Prix de vente au détail suggéré par le fabricant","Date de fin de l’offre","Régions d’expéditions du vendeur","Vendre le Stock Restant","quantité maximale pouvant être commandée","Date de début de l’offre","delivery_schedule_group_id","Code d'imposition du produit","Quantité Minimum de Commande","Prix professionnel","Type de prix selon la quantité","Seuil inférieur de quantité 1","Prix selon la quantité 1","Seuil inférieur de quantité 2","Prix selon la quantité 2","Seuil inférieur de quantité 3","Prix selon la quantité 3","Seuil inférieur de quantité 4","Prix selon la quantité 4","Seuil inférieur de quantité 5","Prix selon la quantité 5","Type de remise progressive","Limite inférieure de la remise","progressive 1","Remise progressive de valeur 1","Limite inférieure de la remise progressive 2","Remise progressive de valeur 2","Limite inférieure de la remise progressive 3","Remise progressive de valeur 3","Numéro de nomenclature OTAN","Classification normalisée des produits et des services des Nations Unies ","Action de tarification", "\n");

$head = array("feed_product_type","item_sku","brand_name","external_product_id","external_product_id_type","item_name","manufacturer", "part_number", "recommended_browse_node","recommended_browse_node2","standard_price","quantity","main_image_url","other_image_url1","other_image_url2","other_image_url3","other_image_url4","other_image_url5","other_image_url6","other_image_url7","other_image_url8","swatch_image_url","parent_child","parent_sku","relationship_type","variation_theme","update_delete","product_description","model","temperature_rating","gtin_exemption_reason","generic_keywords","platinum_keywords1","platinum_keywords2","platinum_keywords3","platinum_keywords4","platinum_keywords5","size_name","color_name","color_map","specification_met","annual_energy_consumption ","annual_energy_consumption_unit_of_measure","is_portable","compatible_counter_depth","compatible_counter_depth_unit_of_measure","included_components1","included_components2","included_components3","included_components4","installation_type","noise_level","noise_level_unit_of_measure","special_features1","special_features2","special_features3","special_features4","special_features5","voltage","wattage","connector_type1","connector_type2","number_of_drawers","freezer_capacity","freezer_capacity_unit_of_measure","fresh_food_capacity","fresh_food_capacity_unit_of_measure","number_of_shelves","eu_energy_label_efficiency_class","efficiency","style_name","voltage_unit_of_measure","pattern_name","wattage_unit_of_measure","brightness_unit_of_measure","band_color","target_audience_base","bullet_point1","bullet_point2","bullet_point3","bullet_point4","bullet_point5","horsepower_unit_of_measure","air_flow_capacity_unit_of_measure","shaft_style_type","defrost_system_type","refrigeration_climate_classification","bottle_count","website_shipping_weight","website_shipping_weight_unit_of_measure","product_grade","volume_capacity_name","form_factor","storage_volume","storage_volume_unit_of_measure","item_display_diameter","item_display_diameter_unit_of_measure","item_thickness_derived","item_thickness_unit_of_measure","item_height","item_length","item_width","item_display_volume_unit_of_measure","unit_count","item_display_volume","volume_capacity_name_unit_of_measure","size_map","maximum_pressure_unit_of_measure","item_display_length","item_display_length_unit_of_measure","item_dimensions_unit_of_measure","unit_count_type","fulfillment_center_id","package_height","package_height_unit_of_measure","package_width","package_width_unit_of_measure","package_length","package_length_unit_of_measure","package_weight","package_weight_unit_of_measure","is_white_glove_required","eu_toys_safety_directive_age_warning","eu_toys_safety_directive_warning1","eu_toys_safety_directive_warning2","eu_toys_safety_directive_warning3","eu_toys_safety_directive_warning4","eu_toys_safety_directive_warning5","eu_toys_safety_directive_warning6","eu_toys_safety_directive_warning7","eu_toys_safety_directive_warning8","eu_toys_safety_directive_language1","eu_toys_safety_directive_language2","eu_toys_safety_directive_language3","eu_toys_safety_directive_language4","eu_toys_safety_directive_language5","eu_toys_safety_directive_language6","eu_toys_safety_directive_language7","eu_toys_safety_directive_language8","mfg_warranty_description_type","seller_warranty_description","country_of_origin","item_weight","item_weight_unit_of_measure","are_batteries_included","batteries_required","battery_type1","battery_type2","battery_type3","number_of_batteries1","number_of_batteries2","number_of_batteries3","battery_cell_composition","battery_weight","battery_weight_unit_of_measure","number_of_lithium_metal_cells","number_of_lithium_ion_cells","lithium_battery_packaging","lithium_battery_energy_content","lithium_battery_energy_content_unit_of_measure","lithium_battery_weight","lithium_battery_weight_unit_of_measure","supplier_declared_dg_hz_regulation1","supplier_declared_dg_hz_regulation2","supplier_declared_dg_hz_regulation3","supplier_declared_dg_hz_regulation4","supplier_declared_dg_hz_regulation5","hazmat_united_nations_regulatory_id","safety_data_sheet_url","item_volume","item_volume_unit_of_measure","flash_point","product_efficiency_image_url","energy_efficiency_image_url","external_testing_certification","ghs_classification_class1","ghs_classification_class2","ghs_classification_class3","currency","condition_type","condition_note","item_package_quantity","product_site_launch_date","merchant_release_date","restock_date","fulfillment_latency","sale_price","sale_from_date","sale_end_date","max_aggregate_ship_quantity","weee_tax_value","weee_tax_value_unit_of_measure","offering_can_be_gift_messaged","offering_can_be_giftwrapped","is_discontinued_by_manufacturer","list_price","offering_end_date","merchant_shipping_group_name","max_order_quantity","offering_start_date","delivery_schedule_group_id","product_tax_code","business_price","quantity_price_type","quantity_lower_bound1","quantity_price1","quantity_lower_bound2","quantity_price2","quantity_lower_bound3","quantity_price3","quantity_lower_bound4","quantity_price4","quantity_lower_bound5","quantity_price5","progressive_discount_type","progressive_discount_lower_bound1","progressive_discount_value1","progressive_discount_lower_bound2","progressive_discount_value2","progressive_discount_lower_bound3","progressive_discount_value3","national_stock_number","unspsc_code","pricing_action") ;

/*$lstcatids = "15,26,17,18,30,31,16,33,19,23,20,24,34";
categories_filles(6) ;
$lstcatids .= ",8" ;
categories_filles(8) ;*/

//Séparation par tabulation
$tab = "\t";

// 57855031

$debutPromo = date('Y-m-d',mktime(0,0,0,date('m'),date('d')-1,date('Y')));
$finPromo = date('Y-m-d',mktime(0,0,0,date('m'),date('d')+180,date('Y')));
    
$tvafrance=$DB_site2->query_first("SELECT 0 AS taux0, TVAtauxnormal AS taux1, TVAtauxreduit AS taux2 FROM pays WHERE paysid=57");
$types_garantie=DAOtype::getTypes($DB_site2);
    
$handle = fopen($filename, 'w+') ;
fwrite($handle,"TemplateType=fptcustom".$tab."Version=2021.0317".$tab."TemplateSignature=UkVGUklHRVJBVElPTl9BUFBMSUFOQ0U=".$tab."settings=contentLanguageTag=fr_FR&feedType=113&headerLanguageTag=fr_FR&metadataVersion=MatprodVkxBUHJvZC0xMDc1&primaryMarketplaceId=amzn1.mp.o.A13V1IB3VIYZZH&templateIdentifier=de5444dc-aba2-4816-a9f2-3d0767c05c2e&timestamp=2021-03-17T13%3A19%3A36.889Z".$tab."Les 3 premières lignes sont réservées à l'utilisation par Amazon.com uniquement. Évitez de modifier ou supprimer les 3 premières lignes.\n");
fwrite($handle,implode($tab,$head1));
fwrite($handle,implode($tab,$head));

$n = 0 ;

//Récupération des articles, puis boucle sur chacun d'eux
$sql = "SELECT a.*, a.libelleFR libart, c.libelleFR libcat, mpc.enabled";
$sql .= " FROM market_place_champs mpc";
$sql .= " INNER JOIN article a ON (a.artcode = mpc.artcode)";
$sql .= " INNER JOIN categorie c ON (c.catid = a.catid)";
$sql .= " WHERE 1";
$sql .= " AND mpc.market_place = 'amazon'";
/*$sql .= " AND ((";
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
$sql .= " ) OR (a.artid IN (3079,870)))"; //Pour les produits gérés en stock chez Amazon mais que l'on souhaite malgré tout mettre à jour (arrêt temporaire du stockage chez Amazon par exemple)*/
$sql .= " GROUP BY a.artid";
$sql .= " ORDER BY a.catid, a.artcode";

//Récupération des articles, puis boucle sur chacun d'eux
/*$sql = "SELECT artid, artcode, a.catid, poid, image, a.".$libelle." libart, a.".$description.", c.".$libelle." libcat, a.*";
$sql .= " FROM article a";
$sql .= " INNER JOIN categorie c USING(catid)";
$sql .= " INNER JOIN article_site a_s USING(artid)";
$sql .= " WHERE a_s.active = '1'";
$sql .= " AND commandable = '1'";
$sql .= " AND a.artid NOT IN (";
$sql .= "      SELECT si.artid";
$sql .= "      FROM stock_item si";
$sql .= "      WHERE si.artid = a.artid";
$sql .= "      AND si.lieu = '631641'";
$sql .= ")";
$sql .= " AND a.artid NOT IN (";
$sql .= "      SELECT si.artid";
$sql .= "      FROM stock_item si";
$sql .= "      WHERE si.artid = a.artid";
$sql .= "      AND si.lieu = '631658'";
$sql .= ")";
//$sql .= " AND a.catid NOT IN (".$lstcatids.")";
$sql .= " GROUP BY artcode";
$sql .= " ORDER BY a.catid, artcode";*/
$articles = $DB_site2->query($sql);
$nbarticles = $DB_site2->num_rows($articles) ;
while ($article=$DB_site2->fetch_array($articles)) {

     //recherche champs modifiables market place
     $mp_field=array();
     
     //Récupération des différents champs de paramétrage Amazon
     $sql = "SELECT champ, valeur";
     $sql .= " FROM market_place_catid";
     $sql .= " WHERE catid = '".$article['catid']."'";
     $sql .= " AND market_place = 'amazon'";
     $requete = $DB_site2->query($sql);
     while($champ = $DB_site2->fetch_array($requete)){
          $mp_field[$champ['champ']] = $champ['valeur'];
     }

     $n++;
     $towrite = "";
     $contenu = "";

     //Récupération de la marque
     $sql = "SELECT libelle";
     $sql .= " FROM marque";
     $sql .= " INNER JOIN article_marque USING(marqueid)";
     $sql .= " WHERE artid = '".$article['artid']."'";
     $marque = $DB_site2->query_first($sql) ;
        
     $parents = array();
     $tabparents = amazon_arbo($DB_site2, $article['artid'], $libelle, $article['typeproduit']) ;

     $txtva = (1+$tvafrance[$article['tauxchoisi']]/100);

     $article_price          = article_price($article['artid'], false, "base", false);
     if($article_price == 0) $article_price = 1;

     foreach ($tabparents as $numligne => $ligne) {
          $contenu .= "\n";
          $tabsku = "";

          //ProductType
          if(mpfields($article,$mp_field['feed_product_type']) != "") {
               $contenu .= mpfields($article,$mp_field['feed_product_type']).$tab;
          }
          else {
               $contenu .= "refrigerationappliance".$tab;
          }

          //SKU
          $contenu .= $ligne['SKU'].$tab;

          //Brand
          $contenu .= $marque['libelle'].$tab;
            
          //StandardProductID et ProductIDType
          if($ligne['parentage'] == "parent") {
               $contenu .= "    ";
               $contenu .= "    ";
          } else {
               $tabsku = explode("_",$ligne[SKU]);
                
               $sql = "SELECT codebarre";
               $sql .= " FROM code_barre";
               $sql .= " WHERE artid = '".$article['artid']."'";
               $sql .= " AND caractvalid = '".$tabsku[1]."' ";
               $lignecode = $DB_site2->query_first($sql);
                    
               $code = $lignecode['codebarre'];
               
               $contenu .= $code.$tab;
               $contenu .= "EAN ".$tab;
          }
       
          //Formatage du nom de produit
          $ProductName = "" ;
          $ProductName .= $marque['libelle']." - " ;
          $ProductName .= trim($article['libcat'])." - " ;
          $ProductName .= $article['gammeFR'];
    
          //Si la longueur du nom est inférieure à 200, on renseigne aussi le coloris (taille titre limitée à 200)
          if(strlen($ProductName) < 200 && $article['coloris'] != ""){
               if($article['coloris']){
                    $ProductName .= " - ".$template->translate($article['coloris']);
               }
          } 
          
          //Si il y a encore de la place dans le titre, on rajoute l'information "capacité en bouteilles"
          if(strlen($ProductName." - Capacité bouteilles : ".$article['capacite_bouteilles']." (min/maxi : ".$article['capacite_mini']."/".$article['capacite_maxi'].")") < 200 && $article['capacite_bouteilles'] != ""){
               $ProductName .= " - Capacité bouteilles : ".$article['capacite_bouteilles']." (min/maxi : ".$article['capacite_mini']."/".$article['capacite_maxi'].")" ;
          }
       
          
          //Génération du titre à partir du modèle Amazon
          $contenu .= html_entity_decode($ProductName).$tab;

          //Manufacturer
          $contenu .= $marque['libelle'].$tab;

          //part_number
          $contenu .= substr($article['artcode_eurocave'], 0, 40).$tab;

          //recommended_browse_nodes
          $contenu .= "57855031".$tab;
           $contenu .= $tab;
          //$contenu.=mpfields($article,$mp_field['recommended_browse_nodes1']).$tab;
          //if(isset($_GET['tr'])) insert_mp($article['artcode'],"RecommendedBrowseNode",'57855031',255);
     
          //Récupération des informations "commandable" et "activé sur rdc" sur le produit dans notre base
          $sql = "SELECT a.commandable, a.date_appro, mpc.enabled";
          $sql .= " FROM article a, market_place_champs mpc";
          $sql .= " WHERE a.artcode = mpc.artcode";
          $sql .= " AND mpc.market_place = 'amazon'";
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

          if($tabActive['active'] != 1 || $tabInfos['commandable'] != 1 || $tabInfos['enabled'] != 1 || (!empty($tabLivraison['date_appro']) && mktime(0,0,0,date('m'),date('d')+7,date('Y')) < $tabLivraison['date_appro'])){
               $quantity = 0;
          } else {
               $quantity = 100;
            }
            
            $tabLivraison = delailivraison($article['artid']);
            if(empty($tabLivraison['date_appro']) && $article['date_appro'] > 0) $tabLivraison['date_appro'] = $article['date_appro'];
                        
            $delailivraison = $tabLivraison['delailivraison'];
            if($delailivraison >= 15){
                    $delailivraison = $delailivraison - 4; //Délais supérieurs à 15j, on indique le nombre réel - 4 jours
            } else {
                    $delailivraison = $delailivraison - 2;
            }
            
            $amazitems=$stock->search($article['artid'],$stockamazon);
            if(count($amazitems)>0) $delailivraison="";
            if(count($amazitems)>0) $quantity=0;

          //ItemPrice et Currency et Quantity
          if($ligne['parentage'] == "parent") {
               $contenu .= $tab;
               //$contenu .= $tab;
               $contenu .= $tab;
          } else {
               $contenu .= number_format($article_price, 2, ".", "").$tab;
               //$contenu .= "EUR   " ;
               $contenu .= $quantity.$tab;
          }
                
        //MainImageURL
        $contenu .= "https://$host/configurations/$host/images/produits/$article[artid].$article[image]".$tab;

          //OtherImageURL1 à 8
          $nphoto = 0 ;
          $photos=$DB_site2->query("select * from articlephoto where artid = '$article[artid]' order by articlephotoid limit 8") ;
          while ($photo=$DB_site2->fetch_array($photos)) {
               $nphoto++ ;
               $contenu .= "http://$host/configurations/$host/images/produits/$article[artid]"."_"."$photo[articlephotoid].$photo[image]".$tab ;
               }
          while ($nphoto < 8) {
               $nphoto++ ;
               $contenu .= $tab;
               }

          //SwatchImageURL
          if ($ligne[FD_image])
               $contenu .= "$ligne[FD_image]".$tab;
          else 
               $contenu .= $tab;
          

          //Parentage
          $contenu .= "$ligne[parentage]".$tab;
          
          //ParentSKU
          $contenu .= "$ligne[ParentSKU]".$tab;
          
          //RelationshipType
          $contenu .= "$ligne[RelationshipType]".$tab;

          //VariationTheme
          $contenu .= $tab;

          //update
          $contenu .= $tab;
          

        $descarticle = explode("<>", wordwrap(strip_tags($article[$description]), 2000, "<>", 1));
        $Description = $descarticle[0] ;
        $Description = str_replace(chr(10), " ", $Description) ;
        $Description = str_replace(chr(13), " ", $Description) ;
        $Description = str_replace(chr(27), " ", $Description) ;
        $Description = stripslashes($Description) ;
        
        
        
        $Description = "<p>$Description</p>" ;
        if ($article[sowine] == 0) {
            if (strlen($Description."<br>T° d'installation cave à vin : $article[temperature_cave]") < 2000 && $article[temperature_cave] != "")
                $Description .= "<br>T° d'installation cave à vin : $article[temperature_cave]" ;
            if (strlen($Description."<br>Consommation d'énergie : $article[conso_kw] Kw/24h soit env. ".str_replace("€", "&euro;", $article[conso_euro])." TTC/an") < 2000 && $article[conso_kw] != "")
                $Description .= "<br>Consommation d'énergie : $article[conso_kw] Kw/24h soit env. ".str_replace("€", "&euro;", $article[conso_euro])." TTC/an" ;
            if (strlen($Description."<br>Régulation : $article[regulation]") < 2000 && $article[regulation] != "")
                $Description .= "<br>Régulation : $article[regulation]" ;
            if (strlen($Description."<br>Affichage : $article[affichage]") < 2000 && $article[affichage] != "")
                $Description .= "<br>Affichage : $article[affichage]" ;
            if (strlen($Description."<br>Hygrometrie : $article[hygrometrie]") < 2000 && $article[hygrometrie] != "")
                $Description .= "<br>Hygrometrie : $article[hygrometrie]" ;
            if (strlen($Description."<br>Serrure : $article[serrure]") < 2000 && $article[serrure] != "")
                $Description .= "<br>Serrure : $article[serrure]" ;
            if (strlen($Description."<br>Eclairage : $article[eclairage]") < 2000 && $article[eclairage] != "")
                $Description .= "<br>Eclairage : $article[eclairage]" ;
            if (strlen($Description."<br>Interrupteur : $article[interrupteur]") < 2000 && $article[interrupteur] != "")
                $Description .= "<br>Interrupteur : $article[interrupteur]" ;
            if (strlen($Description."<br>Garantie incluse : $article[garantie_incluse]") < 2000 && $article[garantie_incluse] != "")
                $Description .= "<br>Garantie incluse : $article[garantie_incluse]" ;
            }
        if (strlen($Description."<br>Eco-participation incluse : ".number_format($article[ecopart], 2, ",", " ")." &euro;") < 2000 && $article[ecopart] != "")
            $Description .= "<br>Eco-participation incluse : ".number_format($article[ecopart], 2, ",", " ")." &euro;";
        
        $Description = str_replace("&euro;", "euros", $Description);
        
        //Description
        //$contenu .= "$Description " ; // max 2000 caracts
        $contenu.=mpfields($article,$mp_field['product_description'],'<br/><br>',2000).$tab;
        if(isset($_GET['tr'])) insert_mp($article['artcode'],"Description",$Description,2000);

          //$contenu.=$Description;
        
        //Controle longueur description (<2000)
        //$contenu .= strlen($Description)."    " ;
        
          for($i=1;$i<50;$i++) {
            $contenu .= $tab;
          }
     
        //if ($article[sowine] == 0) {
            
            //BulletPoint1
            //$contenu .= "Nbre de températures différentes : $article[nbr_temperature] - Capacité du modèle : $article[capacite_bouteilles] bouteilles (Mini / Maxi : $article[capacite_mini] bouteilles / $article[capacite_maxi] bouteilles) " ; // max 500 caracts
            $contenu.=mpfields($article,$mp_field['bullet_point1']).$tab;
            if(isset($_GET['tr'])) insert_mp($article['artcode'],"BulletPoint1","Nbre de températures différentes : $article[nbr_temperature] - Capacité du modèle : $article[capacite_bouteilles] bouteilles (Mini / Maxi : $article[capacite_mini] bouteilles / $article[capacite_maxi] bouteilles)",500);

            $version_porte = "Version porte : $article[version_porte]" ;
            $colorisscadre=$DB_site2->query("select ac.caractvalid, cv.$libelle from article_caractval ac inner join caracteristiquevaleur cv using(caractvalid) where artid = '$article[artid]' and caractid = '2'") ;
            if ($DB_site2->num_rows($colorisscadre) == 0) {
                if (strlen($version_porte." - Coloris cadre de la vitre : N/A") < 500)
                    $version_porte .= " - Coloris cadre de la vitre : N/A" ;
            }
            elseif ($DB_site2->num_rows($colorisscadre) == 1) {
                $coloriscadre=$DB_site2->fetch_array($colorisscadre) ;
                if (strlen($version_porte." - Coloris cadre de la vitre : $coloriscadre[$libelle]") < 500)
                    $version_porte .= " - Coloris cadre de la vitre : $coloriscadre[$libelle]" ;
            }
            else {
                $tabcoloris = array();
                while ($coloriscadre=$DB_site2->fetch_array($colorisscadre))
                    array_push($tabcoloris, $coloriscadre[caractvalid]) ;
                if (in_array(7, $tabcoloris) && in_array(12, $tabcoloris)) {
                    if (strlen($version_porte." - Coloris cadre de la vitre : Aluminium noir") < 500)
                        $version_porte .= " - Coloris cadre de la vitre : Aluminium noir" ;
                }
                elseif (strlen($version_porte." - Coloris cadre de la vitre : N/A") < 500)
                    $version_porte .= " - Coloris cadre de la vitre : N/A" ;
            }
            if (strlen($version_porte." Dimensions / Poids : $article[dimensions]"))
                $version_porte .= " - Dimensions / Poids : $article[dimensions]" ;
            
            //BulletPoint2
            //$contenu .= "$version_porte   " ; // max 500 caracts
            $contenu.=mpfields($article,$mp_field['bullet_point2']).$tab;
            if(isset($_GET['tr'])) insert_mp($article['artcode'],"BulletPoint2",$version_porte,500);
            
            
            $article[rangements_txt] = str_replace("<br>", " - ", $article[rangements_txt]);
            $rangements_txt = explode("<>", wordwrap(strip_tags($article[rangements_txt]), 500, "<>", 1));
            $rangements_txt = $rangements_txt[0] ;
            if (strlen($rangements_txt." Pas de filtre à charbon - Pas de vibration : Moteur sur système anti-vibration - Cave sans entretien") < 500)
                $rangements_txt .= " Pas de filtre à charbon - Pas de vibration : Moteur sur système anti-vibration - Cave sans entretien" ;
            
            //BulletPoint3
            //$contenu .= "Rangements inclus : $rangements_txt  " ; // max 500 caracts
            $contenu.=mpfields($article,$mp_field['bullet_point3']).$tab;
            if(isset($_GET['tr'])) insert_mp($article['artcode'],"BulletPoint3","Rangements inclus : $rangements_txt",500);
            
            //BulletPoint4
            //$contenu .= "Des Experts en caves a vin de 6 à 2000 bouteilles, gamme couleur et inox, Distributeurs agrées des plus Grandes marques  " ; // max 500 caracts
            $contenu.=mpfields($article,$mp_field['bullet_point4']).$tab;
            if(isset($_GET['tr'])) insert_mp($article['artcode'],"BulletPoint4",'Des Experts en caves a vin de 6 à 6000 bouteilles, gamme couleur et inox, Distributeurs agrées des plus Grandes marques',500);

            //BulletPoint5
            //$contenu .= " " ; // max 500 caracts
            $contenu.=mpfields($article,$mp_field['bullet_point5']).$tab;
            if(isset($_GET['tr'])) insert_mp($article['artcode'],"BulletPoint5",'',500);
        //}
        /*else {
            $contenu .= "   " ; // max 500 caracts
            $contenu .= "   " ; // max 500 caracts
            $contenu .= "   " ; // max 500 caracts
            $contenu .= "   " ; // max 500 caracts
            $contenu .= "   " ; // max 500 caracts
        }*/

          for($i=1;$i<7;$i++) {
            $contenu .= $tab;
          }
        
          //ShippingWeight
          $contenu .= "$article[poid]".$tab;
          
          //ShippingWeightUnitOfMeasure
          $contenu .= "GR".$tab;
                    
        //leadtime-to-ship
        //$contenu .= "$delailivraison".$tab;
//              $contenu.=mpfields($article,$mp_field['leadtime-to-ship']).$tab;
        //if(isset($_GET['tr'])) insert_mp($article['artcode'],"leadtime-to-ship",$delailivraison,500);
        
    
        
        
        //SearchTerms1 et SearchTerms2 et SearchTerms3 et SearchTerms4 et SearchTerms5
        if ($article[sowine] == 0) {
            /*$contenu .= "$article[artcode_amazon] " ;
            $contenu .= "cave vin, cave a vin, cave à vin, caves a vins " ;
            $contenu .= "armoire vin, armoire a vin, armoire à vin  " ;
            $contenu .= "la sommeliere, sommeliere, climadiff   " ;
            $contenu .= "   " ;*/
            if(isset($_GET['tr'])) insert_mp($article['artcode'],"SearchTerms1",$article[artcode_amazon]);
            if(isset($_GET['tr'])) insert_mp($article['artcode'],"SearchTerms2",'cave vin, cave a vin, cave à vin, caves a vin');
            if(isset($_GET['tr'])) insert_mp($article['artcode'],"SearchTerms3",'armoire vin, armoire a vin, armoire à vin');
            if(isset($_GET['tr'])) insert_mp($article['artcode'],"SearchTerms4","la sommeliere, sommeliere, climadiff");
            if(isset($_GET['tr'])) insert_mp($article['artcode'],"SearchTerms5",'');
            }
        else {  
            /*$contenu .= "$article[artcode_amazon] " ;
            $contenu .= "bar vin, bar vins, home bar, home bar vin  " ;
            $contenu .= "bars a vins, bars vins, bar à vins, servir vin " ;
            $contenu .= "Sowine Brandt, Sowine, cave 2 bouteilles   " ;
            $contenu .= "   " ;*/
            if(isset($_GET['tr'])) insert_mp($article['artcode'],"SearchTerms1",$article[artcode_amazon]);
            if(isset($_GET['tr'])) insert_mp($article['artcode'],"SearchTerms2",'bar vin, bar vins, home bar, home bar vin');
            if(isset($_GET['tr'])) insert_mp($article['artcode'],"SearchTerms3",'bars a vins, bars vins, bar à vins, servir vin');
            if(isset($_GET['tr'])) insert_mp($article['artcode'],"SearchTerms4",'Sowine Brandt, Sowine, cave 2 bouteilles');
            if(isset($_GET['tr'])) insert_mp($article['artcode'],"SearchTerms",'');
            }
            
        $contenu.=mpfields($article,$mp_field['SearchTerms1']).$tab;
        $contenu.=mpfields($article,$mp_field['SearchTerms2']).$tab;
        $contenu.=mpfields($article,$mp_field['SearchTerms3']).$tab;
        $contenu.=mpfields($article,$mp_field['SearchTerms4']).$tab;
        $contenu.=mpfields($article,$mp_field['SearchTerms5']).$tab;

        //ConditionType 
        $contenu .= $tab;
        
        //ConditionNote
        //$contenu .= "Dans son emballage d'origine " ;
        $contenu.=mpfields($article,$mp_field['ConditionNote']).$tab;
        if(isset($_GET['tr'])) insert_mp($article['artcode'],"ConditionNote","Dans son emballage d'origine");
        
        
        //RecommendedBrowseNode2
        $contenu .= $tab;
        
        //LaunchDate
        //$contenu .= date('Y-m-d'); // date du jour.
        $contenu .= $tab;
        
        
        //MerchantCatalogNumber
        $contenu .= $tab;
            
            
        //DisplayLength 
        $contenu .= $tab;
        
        //DisplayLengthUnitOfMeasure
        $contenu .= $tab;
        
        //DisplayWidth
        $contenu .= $tab;
        
        //DisplayWidthUnitOfMeasure
        $contenu .= $tab;
        
        //DisplayHeight
        $contenu .= $tab;
        
        //DisplayHeightUnitOfMeasure
        $contenu .= $tab;
        
        //DisplayDepth
        $contenu .= $tab;
        
        //DisplayDepthUnitOfMeasure
        $contenu .= $tab;
        
        //DisplayDiameter
        $contenu .= $tab;
        
        //DisplayDiameterUnitOfMeasure
        $contenu .= $tab;
        
        //DisplayWeight
        $contenu .= $tab;
        
        //DisplayWeightUnitOfMeasure
        $contenu .= $tab;
        
        //Volume
        $contenu .= $tab;
        
        //VolumeUnitOfMeasure
        $contenu .= $tab;
            
        for($i=1;$i<27;$i++) {
               $contenu .= $tab;
          }
        
        //ManufacturerWarranty
        $garantie=$DB_site2->query_first("SELECT libelleFR FROM `article_caractval` inner join caracteristiquevaleur using(caractvalid) WHERE `artid` = ".$article['artid']." and differenceprix=0 AND caractvalid<>9 AND caractid IN (3,22) ORDER BY position");
        $garanties=DAOgarantie::loadMaxGarantieArtByPays($DB_site2,"base","FR",57,$article['artid']);
        $garantie_libelle="";
        if(count($garanties)>0){
            $garantie=array_pop($garanties);
            $garantie_libelle=$garantie->duree." an".($garantie->duree>1?"s":"")." ".$types_garantie[$garantie->type_garantie];
        }
        $contenu .= $garantie_libelle.$tab;
        
        /*if ($article[paysfabrication]==0)
            $paysfab="FRANCE";
        if ($article[paysfabrication]==1)
            $paysfab="EUROPE";
        if ($article[paysfabrication]==2)
            $paysfab="ASIE";
        if ($article[paysfabrication]==3)
            $paysfab="USA";*/

        $pays=$DB_site2->query_first("SELECT libelleFR FROM `pays` WHERE paysid =".$article['paysid']);
            
        //CountryProducedIn
        $contenu .= $pays['libelleFR'];
        $contenu .= $tab;

        //ReleaseDate
        $contenu .= $tab;
        
        //SalesPrice
          $contenu .= $tab;

          for($i=1;$i<35;$i++) {
               $contenu .= $tab;
          }

          //currency
          $contenu .= "EUR".$tab;


          //ConditionType     
          $contenu .= "New".$tab;

          $contenu .= $tab;

          //ItemPackageQuantity
          if ($ligne[parentage] == "parent") {
               $contenu .= $tab;
          }
          else  {
               $contenu .= "100".$tab;
          }
        /*if ($ligne[parentage] == "parent") {
            $contenu .= "   " ;
        }else{
        
            
            $fraisdeport = $DB_site2->query_first("SELECT prix,gratuit FROM fraisport WHERE poiddebut <= '$article[poid]' AND poidfin >= '$article[poid]' 
                                        and paysid = '57'");
            if($fraisdeport['gratuit']<$article['prix']) $fraisdeport['prix']=0;

            $prixvente=$article_prices['prix']+$fraisdeport[prix];
            
            $contenu .= number_format($prixvente, 2, ".", "")." " ;
        }*/
        //FulfillmentCenterID
        $contenu .= $tab;
        
        //SaleStartDate et SaleEndDate
          $contenu .= $tab;
          $contenu .= $tab;
        /*if ($ligne[parentage] == "parent") {
            $contenu .= "   " ;
            $contenu .= "   " ;
        }else{
            $contenu .= "$debutPromo    " ;
            $contenu .= "$finPromo  " ;
        }*/
        
                    //Date de réappro
        $contenu .= (isset($tabLivraison['date_appro']) && !empty($tabLivraison['date_appro']) && $tabLivraison['date_appro'] > mktime(0,0,0,date('m'),date('d')+7,date('Y')) ? date('Y-m-d',$tabLivraison['date_appro']) : '').$tab; //RestockDate (date de réappro)
        $contenu .= $tab;
                      $contenu .= $tab;
        $contenu .= $tab;
        $contenu .= $tab;
        $contenu .= $tab;
        $contenu .= $tab;
        $contenu .= $tab;
        $contenu .= $tab;
        //manufacturer-safety-warnin
        //$contenu .= " " ;
        $contenu.=mpfields($article,$mp_field['AwardWon']).$tab;
        if(isset($_GET['tr'])) insert_mp($article['artcode'],"AwardWon","");
        /*if ($article[sowine] == 1) 
            $contenu .= "Trophée de la maison 2008  " ;
        else
            $contenu .= "   " ;*/
    }

     if(empty($delailivraison) || $delailivraison < 30){
          fwrite($handle, utf8_decode($contenu));
     }
    flush ; 
}

function insert_mp($artcode,$champ,$valeur,$limit=50){

  $requete=$DB_site2->query("SELECT id FROM market_place_champs WHERE market_place='amazon' AND artcode='$artcode' AND champ='$champ'");
  if($DB_site2->num_rows($requete)==0)
    $DB_site2->query("INSERT INTO market_place_champs SET market_place='amazon', artcode='$artcode',champ='$champ',valeur='".addslashes($valeur)."',limite=".$limit);
}


function amazon_arbo($dbLink, $artid, $libelle = "libelleFR") {

    global $host ;
    
    $result_array = array();
    $caract_array = array();
    $amatabcaracts = array();
    $amatab = array();

    // retourne les caractids pour lesquels il y a + d'1 valeur pour cet article
    $caract_req=$dbLink->query("SELECT caractid, COUNT(caractvalid) nb FROM article_caractval INNER JOIN caracteristiquevaleur USING(caractvalid) WHERE artid = '$artid' and caractid = '1' GROUP BY artid, caractid ORDER BY nb ASC"); //HAVING(nb > 1) 
    while ($caract=$dbLink->fetch_array($caract_req)) {
        $caract_array[] = $caract['caractid'];
        $amatabcaracts[$caract[caractid]] = $caract[nb] ;
        }
        
    //creer le tableau de combinaisons
    $combinaison_query = "SELECT article.artid".format_implode("", ", caract%%.caractvalid as cv%%, caract%%.lblval as lb%%, caract%%.lblcar as cl%%", $caract_array)." FROM article
        ".format_implode("\n", "INNER JOIN (
                                                SELECT caracteristiquevaleur.$libelle as lblval, caracteristique.$libelle as lblcar, article_caractval.caractvalid, article_caractval.artid FROM article_caractval 
                                                INNER JOIN caracteristiquevaleur USING(caractvalid) 
                                                INNER JOIN caracteristique USING(caractid) 
                                                WHERE caractid = '%%'
                                            ) as caract%% ON(caract%%.artid = article.artid)", $caract_array)."
        WHERE article.artid = '$artid'".($orderby ? " ORDER BY $orderby $sens" : "").";";
    
    $combinaison_req=$dbLink->query($combinaison_query);
    
    while ($combinaison=$dbLink->fetch_array($combinaison_req)) {
        $key_chain = "";
        if (is_array($caract_array)) {
            foreach($caract_array as $caractid)
                $key_chain .= '_'.$combinaison['cv'.$caractid];
            }
        if (is_array($caract_array)) {
            foreach($caract_array as $caractid)
                $result_array[$key_chain][$caractid] = $combinaison['lb'.$caractid];
            }
        }


    $amacount = count($amatabcaracts) ;
    if ($amacount == 0) {// pas de caracteristiques
        $codebarre=$dbLink->query_first("SELECT codebarre FROM code_barre WHERE artid = '$artid'");
        $amatab[0][SKU] = $artid ;
        $amatab[0][parentage] = "child" ;
        $amatab[0][ParentSKU] = "" ;
        $amatab[0][RelationshipType] = "" ;
        $amatab[0][K_Color] = "" ;
        $amatab[0][K_Size] = "" ;
        $amatab[0][K_VariationTheme] = "" ;
        $amatab[0][codebarre] = $codebarre[codebarre] ;
        }
    elseif ($amacount == 1) {// une seule caracteristique à plusieurs valeurs
        $VariationTheme = "" ;
        $amacpt = 0 ;
        foreach ($result_array as $cle => $valeur) {
            $val=$dbLink->query_first("SELECT $libelle FROM caracteristiquevaleur WHERE caractvalid = '".substr($cle, 1)."'");
            $codebarre=$dbLink->query_first("SELECT codebarre FROM code_barre WHERE artid = '$artid' and caractvalid = '".substr($cle, 1)."'");
            $amacpt++ ;
            $amatab[$amacpt][SKU] = $artid.$cle ;
            $amatab[$amacpt][parentage] = "child" ;
            $amatab[$amacpt][ParentSKU] = $artid ;
            $amatab[$amacpt][RelationshipType] = "Variation" ;
            $amatab[$amacpt][FD_Color] = stripslashes($val[$libelle]) ;
            $amatab[$amacpt][FD_Size] = "" ;
            $amatab[$amacpt][FD_VariationTheme] = "Color" ;
            $amatab[$amacpt][codebarre] = $codebarre[codebarre] ;
            $amatab[$amacpt][FD_image] = "https://$host/configurations/$host/images/caracteristiquevaleur/zoom/".substr($cle, 1).".jpg" ;
            $VariationTheme = "Color" ;
            }
        $amacpt = 0 ;
        $codebarre=$dbLink->query_first("SELECT codebarre FROM code_barre WHERE artid = '$artid'");
        $amatab[$amacpt][SKU] = $artid ;
        $amatab[$amacpt][parentage] = "parent" ;
        $amatab[$amacpt][ParentSKU] = "" ;
        $amatab[$amacpt][RelationshipType] = "" ;
        $amatab[$amacpt][FD_Color] = "" ;
        $amatab[$amacpt][FD_Size] = "" ;
        $amatab[$amacpt][FD_VariationTheme] = "Color" ;
        $amatab[$amacpt][codebarre] = $codebarre[codebarre] ;
        sort($amatab);
        }
    return $amatab ;
    }
        

    function format_implode($sep, $replace, $array, $callback = "", $param = false) {
        $toreturn = "";
        foreach($array as $value) {
            $value = is_callable($callback) && $param ? call_user_func($callback, $value, $param) : (is_callable($callback) ? call_user_func($callback, $value) : $value);
            $needle = strpos($replace, "%%") === false ? $replace.$value : str_replace("%%", $value, $replace);
            $toreturn .= $toreturn ? (strpos($sep, "%%") === false ? $sep.$needle : str_replace("%%", $needle, $sep)) : $needle;
        }

        return $toreturn;
    }

require_once($rootpath.'/admin/comparateur/amazonapi/MarketplaceWebService/config.inc.php');
chdir($rootpath."/admin/comparateur/amazonapi/");
include_once($rootpath."/admin/comparateur/amazonapi/MarketplaceWebService/Client.php");

$serviceUrl = "https://mws.amazonservices.fr";

$config = array (
  'ServiceURL' => $serviceUrl,
  'ProxyHost' => null,
  'ProxyPort' => -1,
  'MaxErrorRetry' => 3,
);

$marketplace_ids=array("ES"=>"A1RKKUPIHCS9HS","UK"=>"A1F83G8C2ARO7P","FR"=>"A13V1IB3VIYZZH","DE"=>"A1PA6795UKMFR9","IT"=>"APJ6JRA9NG5V4");

$service = new MarketplaceWebService_Client(
     AWS_ACCESS_KEY_ID, 
     AWS_SECRET_ACCESS_KEY, 
     $config,
     APPLICATION_NAME,
     APPLICATION_VERSION);
     
rewind($handle);
$md5=base64_encode(md5(stream_get_contents($handle), true));
rewind($handle);

$request = new MarketplaceWebService_Model_SubmitFeedRequest();
$request->setMerchant(MERCHANT_ID);
$request->setMarketplaceIdList(array("Id"=>array($marketplace_ids['FR'])));
$request->setFeedType("_POST_FLAT_FILE_LISTINGS_DATA_");
$request->setFeedContent($handle);
$request->setContentMd5($md5);
$request->setPurgeAndReplace(false);

$response = $service->submitFeed($request);

fclose($handle);


?> 
