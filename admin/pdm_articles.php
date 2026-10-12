<?php
require_once("admin_global.php");
require_once("../class/DAOarticle.class.php");
require_once("../class/garantie.class.php");
require_once("../class/DAOgarantie.class.php");
require_once("../class/frais_port.php");
require_once("../class/fonctions.php");
require_once("../class/stock.class.php");

include_once("./header.php");
include_once("./menu.php");

// ini_set('display_errors','on');
// ini_set('memory_limit','256M');

$DB_site = clone $DB_site2;

$site_code="base";
if(isset($_POST['site_code'])) $site_code=$_POST['site_code'];

$blacklisting_rdc = array(
    'ACI-CLI810',
    'ACI-MCV503',
    'ACI-AVI450E',
    'ACI-CLI459',
    'ACI-ART135TC',
    'ACI-ART121TC',
    'ACI-CDY100',
    'ACI-CLI473',
    'ACI-FRX201',
    'ACI-CDY100E',
    'ACI-ROS500E',
    'ACI-CAL200',
    'ACI-CAL204',
    'ACI-CAL100',
    'ACI-CAL202TC',
    'ACI-CAL205',
    'ACI-CAL207',
    'ACI-CAL110TC',
    'ACI-CAL204TC',
    'ACI-CAL207TC',
    'ACI-CAL210TC',
    'ACI-CAL310TC',
    'ACI-ART110T',
    'ACI-CAL111',
    'ACI-SHO101E',
    'ACI-CLI403',
    'ACI-VSV430',
    'ACI-AVI573',
    'ACI-CLI411',
    'ACI-ART211',
    'ACI-ART220TC',
    'ACI-ART210TC',
    'ACI-ART211TC',
    'ACI-ART200TC',
    'ACI-ART201TC'
);

$blacklist_marque=array(9,104,106,12,115,101,117,58,109,60,34,110,25,120,93,114,105,2,71,111,113,76);

//Paramètrage des champs demandés par chaque market place
$rdc = array( //Rue du commerce
    "denomination",
    "description",
    "fulldescription",
    "installation",
    "mcid",
    "subjectivedenomination",
    "wine_cellar_type",
    "refrigerator_type",
    "installation",
    "total_net_capacity",
    "freezer_pos",
    "has_water_fountain",
    "has_auto_defrost",
    "color",
    "installation_type",
    "cooling_power",
    "noise",
    "is_reverse_cycle_air_conditioner"
);

$fnac = array( //La Fnac
    "meta-type",
    "type",
    "support",
    "format",
    "titre",
    "description",
    "caracteristiques",
    "type_pose",
    "type_cave"
);

$amazon = array( //Amazon
    "bullet_point1",
    "bullet_point2",
    "bullet_point3",
    "bullet_point4",
    "bullet_point5",
    "product_description",
    "item_name",
    "feed_product_type",
    "recommended_browse_nodes1",
    "generic_keywords",
    "SearchTerms"
);

$cdiscount = array( //Cdiscount
    "libelle_court",
    "libelle_long",
    "description",
    "code_categorie",
    "niveau1",
    "niveau2",
    "niveau3",
     "niveau4"
);

$google = array(
    "genre",
    "title",
    "description",
    "product_type",
    "google_product_category"
);

$redoute = array(
    "ClassificationValue",
    "ProductTitle",
    "ProductDescription",
    "ProductType"
);

$delamaison = array( 
    "categorie",
    "titre",
    "description"
);

$conforama=array(
    "categorie",
    "description",
    "titre"
);

$showroomprive=array(
    "categorie",
    "description",
    "titre"
);

$retif=array(
    "categorie",
    "description",
    "titre"
);

//$google=array("title","description","product_type","google_product_category","genre");
//$windil=array("Categorie","Titre","Description");

//Association des tableaux de configuration
$marketplaces = array(
     "rdc"           => $rdc,
     "amazon"        => $amazon,
     "google"        => $google,
     "fnac"          => $fnac,
     "cdiscount"     => $cdiscount,
     "redoute"       => $redoute,
     "delamaison"    => $delamaison,
     "conforama"     => $conforama,
     "showroomprive" => $showroomprive,
     "retif"         => $retif   
);


//Paramétrage des champs d'aide à la configuration
$keys = array(
    "artcode"             => "Code article",
     "reffab"             => "Ref. constructeur",
     "libelle"            => "Libellé",
     "description"        => "Description",
     "marque"             => "Marque",
     "gamme"              => "Gamme",
     "modele"             => "Modèle",
     "usage"              => "Usage",
     "dimensions"         => "Dimensions",
     "porte"              => "Version porte",
     "couleur"            => "Couleur",
     "couleurcadre"       => "Couleur cadre",
     "capacite"           => "Capacité",
     "capacitemini"       => "Capacité mini",
     "capacitemaxi"       => "Capacité maxi",
     "tempinstall"        => "Température d'installation",
     "nbretemperatures"   => "Nombre températures",
     "vibrations"         => "Absence de vibrations",
     "filtre"             => "Filtre à charbon",
     "rangements"         => "Rangements inclus",
     "consoenergie"       => "Conso. énergie",
     "consoeuro"          => "Conso. euros",
     "poids"              => "Poids",
     "prixht"             => "Prix HT",
     "prixttc"            => "Prix TTC",
     "prixconseille"      => "Prix conseillé HT",
     "ecopartht"          => "Eco part. HT",
     "ecopartttc"         => "Eco part. TTC",
     "tauxtva"            => "Taux TVA",
     "delailivraison"     => "Texte délai livraison",
     "paysfabrication"    => "Pays fabrication",
     "refsfab"            => "Références constructeur",
     "garantie"           => "Garantie incluse",
     "regulation"         => "Régulation",
     "bouton"             => "Boutons réglages",
     "reglage"            => "Réglage",
     "affichage"          => "Affichage",
     "temperature"        => "Température",
     "thermometre"        => "Thermomètre",
     "hygrometrie"        => "Hygrométrie",
     "hygrometre"         => "Hygrométre",
     "pieds"              => "Pieds vérins réglables",
     "serrure"            => "Serrure",
     "poignee"            => "Poignée",
     "modularite"         => "Modularité intérieure",
     "interrupteur"       => "Interrpteur",
     "eclairage"          => "Eclairage",
     "boutontest"         => "Bouton test",
     "videpoche"          => "Vide poche",
     "clayettefil"        => "Clayette fil",
     "paniertransport"    => "Panier de transport",
     "vinokit"            => "Vinokit",
     "productionfroid"    => "Production de froid",
     "productionchaud"    => "Production de chaud",
     "declenchementchaud" => "Déclenchement de chaud",
     "fonctionhiver"      => "Fonction hiver",
     "puissance"          => "Puissance électrique",
     "intensite"          => "Intensité",
     "conso24h"           => "Conso en kW/h 24h",
     "conso1an"           => "Conso en kW/h par an",
     "bruit"              => "Niveau de bruit (dBA)",
     "hauteur"            => "Hauteur",
     "largeur"            => "Largeur",
     "profondeur"         => "Profondeur",
     "volume"             => "Volume m<sup>3</sup>",
     "classe_energie"     => "Classe énergétique",
     "classe_clima"       => "Classe climatique",
     "plage_temperature"  => "Plage de températures",
     "gaz"                => "Gaz"
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

function getAncre($string){
     $string = preg_replace('/\s\s+/', '', $string);
     $string = str_replace(' ', '_', $string);
     $string = mb_strtolower($string);
     $string = strtr($string,'àáâãäçèéêëìíîïñòóôõöùúûüýÿÀÁÂÃÄÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜÝ',
'aaaaaceeeeiiiinooooouuuuyyAAAAACEEEEIIIINOOOOOUUUUY');
     $string = str_replace(array('&eacute;','&egrave;'),array('e','e'),$string);
     return $string;
}

function getVentesPDM($params){
     global $DB_site2;
     
     $sql = "SELECT COUNT(*) as nb";
     $sql .= " FROM lignefacture lf";
     $sql .= " INNER JOIN facture f ON (f.facturid = lf.factureid)";
     $sql .= " INNER JOIN article a ON (a.artid = lf.artid)";
     if(isset($params['marque_id']) && !empty($params['marque_id'])){
          $sql .= " INNER JOIN article_marque am ON (am.artid = a.artid)";
     }
     if(isset($params['categorie_id']) && $params['categorie_id'] != ''){
          $sql .= " INNER JOIN categorie c ON (c.catid = a.catid)";
          $sql .= " LEFT JOIN categorie c2 ON (c2.catid = c.parentid)";
          $sql .= " LEFT JOIN categorie c3 ON (c3.catid = c2.parentid)";
     }
     $sql .= " WHERE 1";
     $sql .= " AND (active <> '2' && active <> '3')";
     $sql .= " AND f.provenance = 'mp'";
     $sql .= " AND f.provenanceid = '".$params['mp_id']."'";
     $sql .= " AND f.valide IN (5,7,8)"; 
     if(isset($params['year']) && $params['year'] != ''){
          $sql .= " AND DATE_FORMAT(datefacture, '%Y') = '".$params['year']."'";
     }
     if(isset($params['article_id']) && $params['article_id'] != ''){
          $sql .= " AND lf.artid = ".$params['article_id'];
     }
     if(isset($params['marque_id']) && !empty($params['marque_id'])){
          $sql .= " AND am.marqueid = ".$params['marque_id'];
     }
     if(isset($params['categorie_id']) && $params['categorie_id'] != ''){
          $sql .= " AND (c.catid = ".$params['categorie_id']." OR c2.catid = ".$params['categorie_id']." OR c3.catid = ".$params['categorie_id'].")";
     }
     $result = $DB_site2->query_first($sql);
     
     return $result['nb'];
}

function getNbPdtsPDM($params){
     global $DB_site2;
     
     $sql = "SELECT a.artid, a.date_appro";
     $sql .= " FROM market_place_champs mpc, article a";
     $sql .= " WHERE a.artcode = mpc.artcode";
     $sql .= " AND a.active NOT IN ('2','3')"; //Produits désactivés filtrés
     if(isset($params['nom2']) && !empty($params['nom2'])){
          $sql .= " AND mpc.market_place = '".$params['nom2']."'";
     } 
     if(isset($params['enabled']) && $params['enabled'] != ''){
          $sql .= " AND mpc.enabled = ".$params['enabled'];
     }
     if(isset($params['filtre']) && $params['filtre']){ //Produit en reappro filtrés
          $sql .= " AND a.active = '1'";
          $sql .= " AND a.commandable = '1'";
     }
     $result = $DB_site2->query($sql);
     
     $nb = 0;
     while($row = $DB_site2->fetch_array($result)){
          $tabLivraison = delailivraison($row['artid']);
          if(empty($tabLivraison['date_appro']) && $row['date_appro'] > 0) $tabLivraison['date_appro'] = $row['date_appro'];
          
          if(empty($tabLivraison['date_appro']) || mktime(0,0,0,date('m'),date('d')+7,date('Y')) >= $tabLivraison['date_appro']){
               $nb++;
          }
     }
     
     return $nb;
}

function getConfigMP($params = array()){
     global $DB_site2;
     
     $sql = "SELECT COUNT(*) as nb";
     $sql .= " FROM market_place_catid";
     $sql .= " WHERE 1";
     if(isset($params['catid']) && is_numeric($params['catid'])){
          $sql .= " AND catid = ".$params['catid'];
     }
     if(isset($params['mpnom2']) && !empty($params['mpnom2'])){
          $sql .= " AND market_place LIKE '".$params['mpnom2']."'";
     }
     $sql.=" AND valeur!=''";
     $result = $DB_site2->query_first($sql);
     
     return $result['nb'];
}

function getChamps($params = array()){
     global $DB_site2;
     global $marketplaces;
     
     $result=array();
     $return=array();
     $sql = "SELECT *";
     $sql .= " FROM market_place_catid";
     $sql .= " WHERE 1";
     if(isset($params['catid']) && is_numeric($params['catid'])){
          $sql .= " AND catid = ".$params['catid'];
     }
     if(isset($params['mpnom2']) && !empty($params['mpnom2'])){
          $sql .= " AND market_place LIKE '".$params['mpnom2']."'";
     }
     if(isset($params['champs']) && !empty($params['champs'])){
          $sql .= " AND champ LIKE '".$params['champs']."'";
     }
     $req = $DB_site2->query($sql);
     while($champ=$DB_site2->fetch_array($req))
        $result[$champ['champ']]=$champ['valeur'];

        
    foreach($marketplaces[$params['mpnom2']] as $key)
        $return[$key]=isset($result[$key]) ? $result[$key] : "";
     
     return $return;
}

function affiche_categorie($cat){
    global $DB_site2;
    global $tabArticles;
    global $select_pdm;
    global $sites;
    global $site_code;
    global $change;
    global $code_couleur;
    global $marketplaces;
    global $errors;

    $tblsscat=$tblproduits="";
    $nbchk=0;

    $sql=" SELECT * FROM categorie WHERE parentid='".$cat['catid']."' ORDER BY position";
    $sscats=$DB_site2->query($sql);
    while ($sscat=$DB_site2->fetch_array($sscats)){
        $tblsscat.=affiche_categorie($sscat);
    }
    
    
    $classconf="";
    if(isset($tabArticles[$cat['catid']]) && count($tabArticles[$cat['catid']])>0){
    
        //Récupération de la liste de produits vendus sur chaque market place pour cette catégorie
        $params = array(
            'categorie_id' => $cat['catid'],
            'mp_id'        => $select_pdm['id']
        );
//         $nb_pdts_cat = getVentesPDM($params);
    
        $tblproduits=<<<HTML
        <table class="tableau_pdm">
            <tr class="entete_categorie">
                <td></td>
                <td class="produit">Produit</td>
                <td class="produit">Prix TTC / marge HT</td>
                <td class="produit">Stock / dispo</td>
                <td class="produit_pdm" colspan="2">Ventes</td>
                <td colspan="2" class="produit_pdm">Marge HT après com</td>
            </tr>
HTML;

        foreach($tabArticles[$cat['catid']] as $l_article){
            
            //Récupération de la quantité totale en stock pour cette référence
            $sql = "SELECT COUNT(*) as nb_stock";
            $sql .= " FROM stock_item";
            $sql .= " WHERE artid = ".$l_article['artid'];
            $sql .= " AND state = 1";
            $result = $DB_site2->query_first($sql);
            $nb_stock = $result['nb_stock'];
            
//             $tabLivraison = delailivraison($l_article['artid']);
            $dateappro=!empty($tabLivraison['date_appro']) ? date('d/m/Y', $tabLivraison['date_appro']) : '';
            
            //Récupération des informations de vente sur la place de marché pour cette référence
            $sql = "SELECT *";
            $sql .= " FROM market_place_champs";
            $sql .= " WHERE artcode = '".$l_article['artcode']."' AND market_place LIKE '".$select_pdm['nom2']."'";
            $mpart = $DB_site2->query_first($sql);
            
            //Récupération du status visible/invisible
            $article_commandable="";
            if($l_article['active'] == 1){
                $article_visible = '<span style="color: #65b71a; font-weight: bold;">Visible</span>';
                
                //Récupération du status commandable/non commandable
                if($l_article['commandable'] == 1){
                    $article_commandable = '<span style="color: #65b71a; font-weight: bold;">Commandable</span>';
                } else {
                    $article_commandable = '<span style="color: red;">Non commandable</span>';
                }
            } else {
                    $article_visible = '<span style="color: red;">Invisible</span>';
            }
            

            //Récupération de la liste des garanties associés au produit, pour la France
            $garantie_euro = 0;
            $lesGaranties  = DAOgarantie::loadMaxGarantieArtByPays($DB_site2,$site_code,'FR',$sites[$site_code]['paysid_ref'],$l_article['artid']);

            //Calcul du prix d'achat total (garantie + extension)
            $i=0;
            foreach ($lesGaranties as $laGarantie) {
                $i++;
                if ($i==1) {
                    $garantie_euro = ($change[$laGarantie->devise]>0 ? $laGarantie->paht/$change[$laGarantie->devise] : '');
                }
                if ($laGarantie->cumul == 1) {
                    $garantie_euro += $laGarantie->paht/$change[$laGarantie->devise];
                }
            }

            //Récupération des frais de port
            $paht_euro      = $l_article['paht'] / $change[$l_article['devise']];
            $frais_port     = new frais_port($DB_site2, array($l_article['artid'] => 1), $sites[$site_code]['paysid_ref'], $l_article['prix'], $paht_euro);
            $prix_ht_euro   = $l_article['prixht'] / $change[$sites[$site_code]['devise']];
            $prixtrspt_euro = $l_article['trspt'] / $change[$l_article['devise']];
            $ecopartht_euro = $l_article['ecopartht'] / $change[$l_article['devise']];
            $benefice_pdt = $prix_ht_euro + $frais_port->prixht;
            $margeHT = number_format(($benefice_pdt) - ($paht_euro + $prixtrspt_euro + $ecopartht_euro + $garantie_euro + $frais_port->paht),2,'.','');
            $margePC = number_format(($margeHT * 100) / $benefice_pdt,1,',',' ');
             
            $price = number_format($l_article['prix'],2,',','');
            
            $params = array(
                'article_id'   => $l_article['artid'],
                'mp_id'        => $select_pdm['id'],
                'year'         => date('Y')
            );
            $nb_pdts_vendus_N = getVentesPDM($params);
            
            //Récupération du nombre de vente du produit, pour la marketplace, courant l'année N-1
            $params = array(
                'article_id'   => $l_article['artid'],
                'mp_id'        => $select_pdm['id'],
                'year'         => date('Y') - 1
            );
            $nb_pdts_vendus_avant = getVentesPDM($params);
            
            //Récupération du nombre de vente du produit, pour la marketplace, courant l'année N-1
            $params = array(
                'article_id'   => $l_article['artid'],
                'mp_id'        => $select_pdm['id'],
                'year'         => date('Y') - 2
            );
            $nb_pdts_vendus_avant_avant = getVentesPDM($params);
            
            //Récupération du nombre de vente du produit, pour la marketplace, au total
            $params = array(
                'article_id'   => $l_article['artid'],
                'mp_id'        => $select_pdm['id']
            );
            $nb_pdts_vendus = getVentesPDM($params);
            $margePDM = $l_article['prix'] * ($select_pdm['com'] / 100);
            $margeHT_PDM = number_format($margeHT - ($margePDM / 1.2),2,',','');
            $margePC_PDM = ($margeHT_PDM * 100) / $benefice_pdt;
            
            $mp_MAJ = ucfirst($select_pdm['nom2']);

            //Marge inférieure à 20% et pdt vendu sur la market place : code rouge
            if($mpart['enabled']==1){
                $nbchk++;
                $chkmp="checked";
                if($margePC_PDM <= $select_pdm['marge']){
                    $statut_marge = 'rouge';
                    $tranche = 'inf';
                }else{
                    $statut_marge = 'vert';
                    $tranche = 'sup';
                }
            }else{
                $chkmp="";
                if($margePC_PDM <= $select_pdm['marge']){
                    $statut_marge = 'orange';
                    $tranche = 'inf';
                }else{
                    $statut_marge = 'bleu';
                    $tranche = 'sup';
                }
            }
            
            $margePC_PDM = number_format($margePC_PDM,1,',','');
            
            $couleur_fond = $code_couleur[$statut_marge];
            $tdbackground='background-color:'.$couleur_fond.'; color: white; font-weight: bold;';
            
            $error=isset($errors[$l_article['artcode']]) ? "<span class=\"error\"><i class=\"fa fa-exclamation-triangle\" aria-hidden=\"true\"></i>".$errors[$l_article['artcode']]."</span>" : "";
            
            $tblproduits.=<<<HTML
            <tr data-marqueid="$l_article[marqueid]">
                <td>
                    <img src="/configurations/www.ma-cave-a-vin.fr/images/produits/br/$l_article[artid].jpg" class="visuel_pdt">
                </td>
                <td class="infos_prod" style="max-width: 230px;">
                    <a href="/admin/ajout_produit.php?artid=$l_article[artid]&prec=modif" target="_blank">$l_article[artcode]<br>($l_article[artcode_eurocave])</a>
                    <br>$l_article[gammeFR]
                    <br><strong>$l_article[marque_libelle]</strong>
                </td>
                <td class="infos_prod" style="width: 85px;">
                    {$price}€
                    <br><strong>{$margePC}%</strong>
                    <br><strong>{$margeHT}€</strong>
                </td>
                <td class="infos_prod" style="width: 120px;">
                    <strong>$nb_stock</strong>
                    <br>$dateappro
                    <br>$article_visible
                    <br>$article_commandable
                </td>
                <td class="infos_prod" style="text-align: right;">
                    N
                    <br>N-1
                    <br>N-2
                    <br><strong>Total</strong>
                </td>
                <td class="infos_prod_pdm">
                    $nb_pdts_vendus_N
                    <br>$nb_pdts_vendus_avant
                    <br>$nb_pdts_vendus_avant_avant
                    <br><strong>$nb_pdts_vendus</strong>
                </td>
                <td id="art-$l_article[artid]-$select_pdm[id_reel]" style="width:234px; text-align: center; $tdbackground">
                    <input id="check-$l_article[artid]-$select_pdm[id_reel]" type="checkbox" $chkmp onchange="update_article_statut($l_article[artid], '$select_pdm[nom2]', '$tranche');">
                    <br>{$margePC_PDM}%
                    <br>{$margeHT_PDM}€
                    <br><a href="javascript:testarticle($l_article[artid])">Tester</a>
                    $error
                </td>
            </tr>
HTML;

        }
        $tblproduits.="</table>";
        
        if($nbchk>0){
            $checkConfig = getConfigMP(array('catid' => $cat['catid'], 'mpnom2' => $select_pdm['nom2']));
            if($checkConfig<count($marketplaces[$select_pdm['nom2']])) $classconf="conferror";
            if($checkConfig == 0) $classconf = 'noconf';
        }
    }
    
    $champs="";
    foreach(getChamps(array('catid' => $cat['catid'], 'mpnom2' => $select_pdm['nom2'], 'champs' => $champs)) as $champ=>$valeur){
        
        $champs.=<<<HTML
        $champ
        <textarea name="field_$champ">$valeur</textarea>
HTML;
    }
    
    $return.=<<<HTML
    <div class="pdm_cat $classconf">
            <b>$cat[libelleFR] (<i></i>) <img src="images/editer.gif" onclick="editCat(event,$cat[catid])"></b>
            <form method="post" name="form$cat[catid]" class="hidden">
                <h3>$cat[libelleFR]</h3>
                <div>
                    <input type="hidden" name="maj_config" value="1">
                    <input type="hidden" name="catid" value="$cat[catid]">
                    <input type="hidden" name="nom2" value="$select_pdm[nom2]">
                    $champs
                    <input type="button" onclick="testfields(document.form$cat[catid])" value="Visualiser">
                    <input type="button" onclick="editCat(event,$cat[catid])" value="Annuler">
                    <input type="submit" value="Enregistrer">
                </div>
            </form>
            <div class="content">
                $tblproduits
                $tblsscat
            </div>
    </div>
HTML;
    
    return $return;
}


if(!empty($_POST)){
     if(isset($_POST['marge_tolere']) && !empty($_POST['marge_tolere'])){
          $sql = "UPDATE market_place SET";
          $sql .= " market_place_marge_toleree = '".$_POST['marge_tolere']."'";
          $sql .= " WHERE market_place_id = ".$_POST['pdm_id'];
          $DB_site2->query($sql);
     }
     
     if(isset($_POST['commission']) && !empty($_POST['commission'])){
          $sql = "UPDATE market_place SET";
          $sql .= " market_place_com = '".$_POST['commission']."'";
          $sql .= " WHERE market_place_id = ".$_POST['pdm_id'];
          $DB_site2->query($sql);
     }
     
     if(isset($_POST['maj_config']) && $_POST['maj_config'] == 1){
          
          //On efface tous les champs pour les recréer
          $sql = "DELETE FROM market_place_catid";
          $sql .= " WHERE market_place = '".$_POST['nom2']."'";
          $sql .= " AND catid = ".$_POST['catid'];
          $DB_site2->query($sql);
          
          //Remplissage des champs
          foreach($_POST as $name => $value){
               if(substr($name,0,6)=="field_"){
                    $name=substr($name,6);
                    
                    $sql = "INSERT INTO market_place_catid (";
                    $sql .= "market_place";
                    $sql .= ", champ";
                    $sql .= ", valeur";
                    $sql .= ", catid";
                    $sql .= ") VALUES (";
                    $sql .= "'".$_POST['nom2']."'";
                    $sql .= ", '".$name."'";
                    $sql .= ", '".mysqli_real_escape_string($DB_site2->link_id,$value)."'";
                    $sql .= ", '".$_POST['catid']."'";
                    $sql .= ")";
                    $DB_site2->query($sql);
               }
          }
     }
}

//Nettoyage automatique de la table : Suppression des produits de la table qui sont supprimés ou archivés
/*$sql = "DELETE FROM market_place_champs";
$sql .= " WHERE artcode IN (";
     $sql .= "SELECT artcode";
     $sql .= " FROM article";
     $sql .= " WHERE (active = '2' OR active = '3')";
$sql .= ")";
$DB_site2->query($sql);*/


//Récupération de la liste des market place
$sql = "SELECT *";
$sql .= " FROM market_place";
$sql .= " WHERE market_place_site_id != '00'";
$requete = $DB_site2->query($sql);
$PDM = array();
while($row = $DB_site2->fetch_array($requete)){
     $PDM[strtolower($row['market_place_nom'])] = array(
          'logo'        => $row['market_place_logo'],
          'com'         => $row['market_place_com'],
          'marge'       => $row['market_place_marge_toleree'],
          'active'      => (isset($_GET['pdm']) && $_GET['pdm'] == $row['market_place_nom2']) || (isset($_GET['pdm']) && empty($_GET['pdm'])) || !isset($_GET['pdm']) ? true : false,
          'nom2'        => $row['market_place_nom2'],
          'nom_complet' => $row['market_place_nom_complet'],
          'id'          => $row['market_place_site_id'],
          'id_reel'     => $row['market_place_id'],
          'infos'       => $row['market_place_infos']
     );
}


$code_couleur = array(
    'rouge' => '#ff0000',
    'orange' => '#ff6e00',
    'vert' => '#65b71a',
    'bleu' => '#0090cb'
);

//Récupération du nombre de produits en vente pour chaque marketplace
foreach($PDM as $nom => $infos){
     $params = array(
          'nom2'    => $infos['nom2'],
          'enabled' => '1',
          'filtre'  => true
     );
     $PDM[$nom]['nb_pdts'] = getNbPdtsPDM($params);
     
     $params = array(
          'nom2'    => $infos['nom2'],
          'enabled' => '1'
     );
     $PDM[$nom]['nb_pdts_tot'] = getNbPdtsPDM($params);
}

//Reconstruction de l'url pour les filtres
$url_filtre_cat .= '?';
$url_filtre_marque .= '?';
$url_filtre_pdm .= '?';
foreach($_GET as $key => $value){
     
     if($key != 'catid') $url_filtre_cat .= $key.'='.$value.'&';
     if($key != 'marqueid') $url_filtre_marque .= $key.'='.$value.'&';
     if($key != 'pdm') $url_filtre_pdm .= $key.'='.$value.'&';
     
}

$change = $devises->getchange();
$change['EUR']=1;

$artcodes = array();
$lignes   = "";

//Récupération liste des catégories, pour affichage dans filtres
$sql = "SELECT IF(c.parentid != 0, IF(c2.parentid != 0, c3.catid, c2.catid), c.catid) as categorie_id, IF(c.parentid != 0, IF(c2.parentid != 0, c3.libelleFR, c2.libelleFR), c.libelleFR) as categorie_libelle";
$sql .= " FROM categorie c";
$sql .= " LEFT JOIN categorie c2 ON (c2.catid = c.parentid)";
$sql .= " LEFT JOIN categorie c3 ON (c3.catid = c2.parentid)";
$sql .= " WHERE 1";
$sql .= " AND (c.catid IN (SELECT catid FROM article WHERE (active <> '2' && active <> '3')) OR c2.catid IN (SELECT catid FROM article WHERE (active <> '2' && active <> '3')) OR c3.catid IN (SELECT catid FROM article WHERE (active <> '2' && active <> '3')))";
$sql .= " GROUP BY categorie_id";
$sql .= " ORDER BY categorie_libelle";
$requete = $DB_site2->query($sql);
$tabCategorie = array();
while($row = $DB_site2->fetch_array($requete)){
     $tabCategorie[] = $row;
}


//Récupération liste des marques, pour afficher dans filtres
$sql = "SELECT m.marqueid as marque_id, m.libelle as marque_libelle";
$sql .= " FROM marque m";
$sql .= " INNER JOIN article_marque am USING(marqueid)";
$sql .= " WHERE 1";
$sql .= " AND am.artid IN (SELECT artid FROM article WHERE (active <> '2' && active <> '3'))";
$sql .= " AND m.marqueid NOT IN (".implode(",",$blacklist_marque).")";
$sql .= " GROUP BY marque_id";
$sql .= " ORDER BY marque_libelle";
$requete = $DB_site2->query($sql);
$tabMarque = array();
while($row = $DB_site2->fetch_array($requete)){
     $tabMarque[] = $row; 
}

if(isset($_GET['pdm'])){
    $select_pdm=$PDM[strtolower($_GET['pdm'])];

    //Récupération de la liste des produits
    $sql = "SELECT a.artid";
        $sql .= ", a.catid";
        $sql .= ", a.artcode";
        $sql .= ", a.artcode_eurocave";
        $sql .= ", a.active";
        $sql .= ", a.commandable";
        $sql .= ", a.paht";
        $sql .= ", a.prix";
        $sql .= ", a.prixht";
        $sql .= ", a.ecopartht";
        $sql .= ", a.date_appro";
        $sql .= ", af.trspt";
        $sql .= ", af.ecopartht";
        $sql .= ", af.devise";
        $sql .= ", m.libelle as marque_libelle";
        $sql .= ", m.marqueid";
        $sql .= ", c.parentid";
        $sql .= ", IF(c.parentid != 0, IF(c2.parentid != 0, c3.libelleFR, c2.libelleFR), c.libelleFR) as categorie_libelle";
        $sql .= ", IF(c.parentid != 0, IF(c2.parentid != 0, c3.catid, c2.catid), c.catid) as categorie_id";
    $sql .= " FROM article a";
    $sql .= " INNER JOIN article_fournisseur af ON (af.artid = a.artid AND af.principal LIKE '%base%')";
    $sql .= " INNER JOIN article_marque am ON (am.artid = a.artid)";
    $sql .= " INNER JOIN marque m ON (m.marqueid = am.marqueid)";
    $sql .= " INNER JOIN categorie c ON (c.catid = a.catid)";
    $sql .= " LEFT JOIN market_place_champs mpc ON (mpc.artcode = a.artcode)";
    $sql .= " LEFT JOIN categorie c2 ON (c2.catid = c.parentid)";
    $sql .= " LEFT JOIN categorie c3 ON (c3.catid = c2.parentid)";
    $sql .= " WHERE (active <> '2' && active <> '3')";
    if(isset($_GET['catid']) && !empty($_GET['catid']) && is_numeric($_GET['catid'])){
        $sql .= " AND (c.catid = ".$_GET['catid']." OR c2.catid = ".$_GET['catid']." OR c3.catid = ".$_GET['catid'].")";
    }
    if(isset($_GET['marqueid']) && !empty($_GET['marqueid']) && is_numeric($_GET['marqueid'])){
        $sql .= " AND m.marqueid = ".$_GET['marqueid'];
    }
    ///pour retif, on accepte Friax, Liebherr, Le Chai
    if($_GET['pdm']=='retif'){
        $blacklist_marque=array(106,12,115,101,117,58,109,60,110,25,120,93,114,105,2,71,111,113,76);
    }
    $sql.=" AND m.marqueid NOT IN (".implode(",",$blacklist_marque).")";
    $sql .= " GROUP BY a.artid";
    $sql .= " ORDER BY categorie_libelle, marque_libelle, a.artcode";
    $requete = $DB_site2->query($sql);

    $ancreCategorie = array();
    while($row = $DB_site2->fetch_array($requete)){
        if(!in_array($row['categorie_libelle'], $ancreCategorie)){
            $ancreCategorie[] = $row['categorie_libelle'];
        }
        if(!isset($tabArticles[$row['categorie_id']])) $tabArticles[$row['categorie_id']]=array();
        $tabArticles[$row['catid']][] = $row;
    }
    
    $errors=array();
    $sql="SELECT * FROM market_place_error WHERE market_place='".$select_pdm['nom2']."' AND date>".(time()-86400);
    $requete = $DB_site2->query($sql);
    while($e=$DB_site2->fetch_array($requete)){
        if(!isset($errors[$e['artcode']])) $errors[$e['artcode']]="";
        $errors[$e['artcode']].=$e['error'];
    }
}

?>
<script type="text/javascript">
    var mp="<?=$_GET['pdm']; ?>";
     tabCouleur = new Array();
     <?php 
     foreach($code_couleur as $nom => $code){
          echo "tabCouleur['".$nom."'] = '".$code."';";
     }
     ?>
     
     function update_article_statut(artid, pdm, statut){
          ajaxrequest("pdm_ajax_maj_article.php?artid="+artid+"&pdm="+pdm+"&statut="+statut, update_callback);
     }
     
     function update_callback(httprequest){
          var response = httprequest.responseText;
          var tabResponse = response.split('||');
          var couleur = tabResponse[0];
          var artid = tabResponse[1];
          var pdm = tabResponse[2];

          document.getElementById('art-'+artid+'-'+pdm).style.backgroundColor = tabCouleur[couleur];
          nbexport();
    }
    
    function filter(){
        var marqueid=document.getElementById("marqueid").value;
        var src=document.getElementById("src").value;
        var reg=new RegExp(src,"im");
        var trlist=document.querySelectorAll(".tableau_pdm tr:not(.entete_categorie)");
        var inactive=document.getElementById('inactive').checked;
        var checked=document.getElementById('checked').checked;
       
        for(i=0;i<trlist.length;i++){
            var haserror=trlist[i].querySelectorAll(".error").length>0;
            var productchecked=trlist[i].querySelector("input[type=checkbox]").checked;
            if((marqueid=="" || marqueid==trlist[i].dataset.marqueid) && (src.length<3 || reg.test(trlist[i].innerText)) && (!inactive || haserror) && (!checked || productchecked)) {
                trlist[i].classList.remove("hidden");
            }
            else
                trlist[i].classList.add("hidden");
        }
        nbexport();
    }
    
    function nbexport(){
        var src=document.getElementById("src").value;
        var tables=document.querySelectorAll(".pdm_cat");
        for(i=0;i<tables.length;i++){
            var checked=tables[i].querySelectorAll("input[type=checkbox]:checked");
            var visible=tables[i].querySelectorAll("tr:not(.hidden):not(.entete_categorie)").length;
            tables[i].querySelector("b i").innerHTML=checked.length;
            if(checked.length==0 || visible==0){
                
                tables[i].classList.add("closed");
                tables[i].querySelector("b").classList.add("empty");
            }else{
                tables[i].classList.remove("closed");
                tables[i].querySelector("b").classList.remove("empty");
            }
        }
        var totalexport=document.querySelectorAll(".content input[type=checkbox]:checked").length;
        var totalerrors=document.querySelectorAll(".content .error").length;
        document.getElementById("totalexport").innerHTML=totalexport+" produits exportés dont "+totalerrors+" en erreur";
    }
    
    function testfields(form){
        var post = new Array();
        post['catid'] = form.catid.value;
        for(i=0;i<form.getElementsByTagName("TEXTAREA").length;i++){
            var oi=form.getElementsByTagName("TEXTAREA")[i];
            post[oi.name]=oi.value;     
        }
        for(i=0;i<form.getElementsByTagName("INPUT").length;i++){
            var oi=form.getElementsByTagName("INPUT")[i];
            post[oi.name]=oi.value;     
        }

        ajaxrequest('mp_cats_ajax.php',testcb,post);
     }
     
     function testarticle(artid){
        var post=new Array();
        post['artid']=artid;
        post['mp']=mp;
        ajaxrequest('mp_cats_ajax.php',testcb,post);
     }

     function testcb(httprequest){
        document.querySelector('#previewdiv .preview').innerHTML=httprequest.responseText;
        document.getElementById('previewdiv').style.display='block';
        document.getElementById('previewdiv').style.top=document.body.scrollTop+'px';
     }
    
    function editCat(event,catid){
        event.stopPropagation();
        document.forms['form'+catid].classList.toggle("hidden");
        if(document.forms['form'+catid].querySelector(".shortcodes")==null){
            var clone=document.querySelector(".shortcodes.hidden").cloneNode(true);
            clone.classList.remove("hidden");
            document.forms['form'+catid].appendChild(clone,);
        }
    }
    
    window.onload=function(){
        var cattitle=document.querySelectorAll(".pdm_cat b");
        for(i=0;i<cattitle.length;i++){
            cattitle[i].onclick=function(){
                var catdiv=this.parentNode;
                catdiv.classList.toggle("closed");
            }
        }
        nbexport();
    }
</script>

<h1>Configuration des produits market place</h1>

<div class="filtres">
    <form method="get">
        <select name="pdm" onchange="this.parentNode.submit()">
                <option value="">- Choisir une place de marché -</option>
                <?php foreach($PDM as $la_pdm){ ?>
                <option value="<?php echo $la_pdm['nom2']; ?>" <?php echo isset($_GET['pdm']) && $_GET['pdm'] == $la_pdm['nom2'] ? 'selected' : '' ?>><?php echo $la_pdm['nom_complet']; ?></option>
                <?php } ?>
        </select>
        <select id="marqueid" onchange="filter()">
                <option value="">- Toutes les marques -</option>
                <?php foreach($tabMarque as $la_marque){ ?>
                <option value="<?php echo $la_marque['marque_id']; ?>" <?php echo isset($_GET['marqueid']) && $_GET['marqueid'] == $la_marque['marque_id'] ? 'selected' : ''; ?>><?php echo $la_marque['marque_libelle']; ?></option>
                <?php } ?>
        </select>
        <input type="checkbox" id="inactive" onclick="filter()"> Produits désactivés
        <input type="checkbox" id="checked" onclick="filter()"> Produits cochés
        <input type="text" id="src" placeholder="Rechercher" onkeyup="filter()">
    </form>
</div>
<br>
<a href="#legende" style="font-size: 12px;">Voir la légende</a>

<?php                   

if(isset($_GET['pdm'])){
    print <<<HTML
    <form method="post">
         <input type="hidden" name="pdm_id" value="$select_pdm[id_reel]">
        Commission $select_pdm[nom_complet] : <input type="text" name="commission" value="$select_pdm[com]" size="4">%
        <input type="submit" value="Mettre à jour">
    </form>
    <p id="totalexport"></p>
HTML;


    $sqlmenu="SELECT * FROM menus ORDER BY position";
    $rmenu=$DB_site2->query($sqlmenu);
    while($menu=$DB_site2->fetch_array($rmenu)){
        print "<div class=\"pdm_menu\">
            <b>Menu ".$menu['libelleFR']."</b>
            <div class=\"content\">";
            
        $sql=" SELECT * FROM categorie WHERE menu_appartenance='".$menu['idmenu']."' ORDER BY position";
        $cats=$DB_site2->query($sql);
        while ($cat=$DB_site2->fetch_array($cats)){
            print affiche_categorie($cat);
        }
        
        print "</div>
        </div>";
    }
                            
} else {
    print <<<HTML
    <p class="information">Veuillez sélectionner une market place</p>
HTML;
}

$tdshort="";
foreach($keys as $key=>$value)
    $tdshort.="<tr>
        <td>[".$key."]</td>
        <td>".$value."</td>
    </tr>";

print <<<HTML
<table border="0" cellpadding="0" cellspacing="0" width="100%" class="hidden shortcodes">
    <tr>
        <td class="titre" colspan="2">Shortcodes de remplacement</td>
    </tr>
    $tdshort
</table>
HTML;

?>

<div id="previewdiv">
     <div class="preview"></div>
     <div class="x" onclick="document.getElementById('previewdiv').style.display='none';">X</div>
</div>

<a name="legende"></a>
<p style="color:red;">Les produits Afinox, Avintage, Berkel, Brema, Brera, Calice, Calice Design, Ellemme, Friax, Hoonved, Atelier du vin, Le Chai, Liebherr, Liebherr pro, Paul Bocuse, Tecfrigo, Tecnox, Transtherm, Winetaste, Winebar, Wineroom, Winefit ne sont pas affichés car leur vente sur market place est interdite</p>
<strong><u>Fond <span style="color:#ff0000;">noir</span></u></strong><br>Produits sur liste noire de la market place (ventes inexistantes les 12 derniers mois)<br><br>
<strong><u>Fond <span style="color:#ff0000;">rouge</span></u></strong><br>Export produit <u>activé</u><br>Marge inférieure à la limite fixée (à décocher, sauf exception)<br><br>
<strong><u>Fond <span style="color:#65b71a;">vert</span></u></strong><br>Export produit <u>activé</u><br>Marge supérieure à la limite fixée (cas idéal, ne pas changer)<br><br>
<strong><u>Fond <span style="color:#ff6e00;">orange</span></u></strong><br>Export produit <u>désactivé</u><br>Marge inférieure à la limite fixée (ne pas cocher, sauf cas exception)<br><br>
<strong><u>Fond <span style="color:#0090cb;">bleu</span></u></strong><br>Export produit <u>désactivé</u><br>Marge supérieure à la limite fixée (à cocher, sauf cas exception)<br><br>
<strong><u>Fond classique</u></strong><br>Export produit <u>désactivé</u><br>La catégorie du produit n'a pas été configurée dans la rubrique "Places de marché > Gérer les contenus"<br>
<p style="font-weight: bold;">Même cochés, les articles inactifs n'apparaitront pas dans l'export</p>

<?php

include "./footer.php";

?>
