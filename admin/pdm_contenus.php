<?php
require_once("admin_global.php");

include_once("./header.php");
include_once("./menu.php");

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

function getCategorie($type, $params=array()){ //1: catégorie 2: sous-catégorie 3: sous-sous-catégorie
     global $DB_site2;
     
     $sql = " SELECT *";
     $sql .= " FROM categorie c";
     switch($type){
          case 1:
               $sql .= " WHERE c.parentid = 0";
               $sql .= " ORDER BY c.libelleFR";
               break;
          case 2:
               $sql .= " WHERE c.parentid = ".$params['catid'];
               $sql .= " ORDER BY c.libelleFR";
               break;
          case 3:
               $sql .= " INNER JOIN categorie c2 ON (c2.parentid = c.catid)";
               $sql .= " INNER JOIN categorie c3 ON (c3.parentid = c2.catid)";
               $sql .= " WHERE c3.parentid = ".$params['catid'];
               $sql .= " ORDER BY c3.libelleFR";
               break;
          case 4:
               $sql .= " INNER JOIN categorie c2 ON (c2.parentid = c.catid)";
               $sql .= " INNER JOIN categorie c3 ON (c3.parentid = c2.catid)";
               $sql .= " INNER JOIN categorie c4 ON (c4.parentid = c3.catid)";
               $sql .= " WHERE c4.parentid = ".$params['catid'];
               $sql .= " ORDER BY c4.libelleFR";
               break;
     }
     $req = $DB_site2->query($sql);
     while($row = $DB_site2->fetch_array($req)){
          $tabCategorie[] = $row;
     }
     
     return $tabCategorie;
}

function getNbProduits($params = array()){
     global $DB_site2;
     
     $sql = "SELECT COUNT(*) as nb";
     $sql .= " FROM article a, market_place_champs mpc, market_place mp";
     $sql .= " WHERE mp.market_place_nom2 = mpc.market_place";
     $sql .= " AND a.artcode LIKE mpc.artcode";
     $sql .= " AND a.active NOT IN ('2','3')";
     $sql .= " AND mp.market_place_id = '".$_GET['mpid']."'";
     $sql .= " AND mpc.enabled = '1'";
     if(isset($params['catid']) && is_numeric($params['catid'])){
          $sql .= " AND a.catid = ".$params['catid'];
     }
     $result = $DB_site2->query_first($sql);
     
     return $result['nb'];
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
     
     $sql = "SELECT valeur";
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
     $result = $DB_site2->query_first($sql);
     
     return $result['valeur'];
}

//Mise à jour configuration
if(!empty($_POST)){
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
    "AwardWon",
    "BulletPoint1",
    "BulletPoint2",
    "BulletPoint3",
    "BulletPoint4",
    "BulletPoint5",
    "ConditionNote",
    "Description",
    "leadtime-to-ship",
    "ProductName",
    "ProductType",
    "RecommendedBrowseNode1",
    "SearchTerms",
    "SearchTerms1",
    "SearchTerms2",
    "SearchTerms3",
    "SearchTerms4",
    "SearchTerms5"
);

$priceminister = array( //Priceminister
    "Titre",
    "Description",
    "Type",
    "typefichier",
    "categorie",
    "matiere",
    "style",
    "emplacement"
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

$pixmania = array( //Pixmania
    "segment",
    "title",
    "description"
);

$google = array(
    "genre",
    "title",
    "description",
    "product_type",
    "google_product_category"
);
$idealo = array(
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

$twil = array(
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

//$google=array("title","description","product_type","google_product_category","genre");
//$windil=array("Categorie","Titre","Description");

//Association des tableaux de configuration
$marketplaces = array(
     "rdc"           => $rdc,
     "amazon"        => $amazon,
     "priceminister" => $priceminister,
     "google"        => $google,
     /*"windil"        =>$windil,*/
     "pixmania"      => $pixmania,
     "fnac"          => $fnac,
     "cdiscount"     => $cdiscount,
     "redoute"       => $redoute,
     "twil"          => $twil,
     "delamaison"    => $delamaison,
     "idealo"        => $idealo,
     "conforama"     => $conforama
);

//Limites de taille de caractères des champs
$limites = array(
    "google" => array(
          "title" => 70
     ),
    "idealo" => array(
          "title" => 70
     ),
     "windil" => array(
          "Titre" => 60
     ),
     "priceminister" => array(
          "Description" => 3000
     ),
     "amazon" => array(
          "BulletPoint1" => 50,
          "BulletPoint2" => 50,
          "BulletPoint3" => 50,
          "BulletPoint4" => 50,
          "BulletPoint5" => 50,
          "SearchTerms1" => 50,
          "SearchTerms2" => 50,
          "SearchTerms3" => 50,
          "SearchTerms4" => 50,
          "SearchTerms5" => 50,
          "Description" => 2000,
          "ProductName" => 500
     ),
     "fnac" => array(
         "titre" => 255,
         "description" => 4000,
         "caracteristiques" => 4000
     ),
     "twil" => array(
         "titre" => 255,
         "description" => 4000,
         "caracteristiques" => 4000
     ),
     "cdiscount" => array(
          "libelle_court" => 30,
          "libelle_long" => 50,
          "description" => 5000
     ),
     "redoute" => array(
          "ProductTitle" => 100
     )
);

//Récupération de la liste des market place
$sql = "SELECT *";
$sql .= " FROM market_place";
$req = $DB_site2->query($sql);
while($row = $DB_site2->fetch_array($req)){
     $tabPDM[] = $row;
}

//Récupération de la liste des catégorie, à configurer pour une seule market place
if(isset($_GET['mpid']) && !empty($_GET['mpid']) && is_numeric($_GET['mpid'])){
     $tabCatMere = getCategorie(1);
     
     //Récupération des détails sur la market place
     $sql = "SELECT *";
     $sql .= " FROM market_place";
     $sql .= " WHERE market_place_id = ".$_GET['mpid'];
     $tabMP = $DB_site2->query_first($sql);
     
     //Récupération du nombre total de catégories existantes
     $sql = "SELECT COUNT(*) as nb";
     $sql .= " FROM categorie";
     $result = $DB_site2->query_first($sql);
     $nbCat = $result['nb'];
}
?>

<script type="text/javascript">
     function toggleCategorie(num){
          for(i=1;i<=<?php echo $nbCat; ?>;i++){
               if(i != num && document.getElementById('cat-'+i)){
                    document.getElementById('cat-'+i).style.display = 'none';
               }
          }
          
          if(document.getElementById('cat-'+num).style.display == 'block'){
               document.getElementById('cat-'+num).style.display = 'none';
          } else {
               document.getElementById('cat-'+num).style.display = 'block';
          }
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

     function testcb(httprequest){
          document.getElementById('preview').innerHTML=httprequest.responseText;
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
        var cattitle=document.querySelectorAll(".pdm_menu b,.pdm_cat b");
        for(i=0;i<cattitle.length;i++){
            cattitle[i].onclick=function(){
                var catdiv=this.parentNode;
                catdiv.classList.toggle("closed");
            }
        }
    }
</script>

<h1>Configuration des champs market place</h1>

<div id="previewdiv" style="display:none;position:absolute;background-color:#EEE;border:solid thin black;z-index:100;">
     <div id="preview" style="float:left;"></div>
     <div style="float:right;color:red;font-weight:bold;cursor:pointer;text-align:center;width:20px;" onclick="document.getElementById('previewdiv').style.display='none';">X</div>
</div>

<div class="filtres">
    <p>Place de marché :</p>
    <select onchange="if(this.value != ''){ location.href = '?mpid='+this.value; } else { location.href = ''; }">
            <option value="">- Choisir une place de marché -</option>
            <?php foreach($tabPDM as $la_pdm){ ?>
            <option value="<?php echo $la_pdm['market_place_id']; ?>" <?php echo isset($_GET['mpid']) && $_GET['mpid'] == $la_pdm['market_place_id'] ? 'selected' : ''; ?>><?php echo $la_pdm['market_place_nom_complet']; ?></option>
            <?php } ?>
    </select>
</div>

<?php

function affiche_categorie($cat){
    global $DB_site2;
    global $tabMP;
    global $marketplaces;
    
    $tblsscat="";

    $sql=" SELECT * FROM categorie WHERE parentid='".$cat['catid']."' ORDER BY position";
    $sscats=$DB_site2->query($sql);
    while ($sscat=$DB_site2->fetch_array($sscats)){
        $tblsscat.=affiche_categorie($sscat);
    }
    
    $nbPdtsCat = getNbProduits(array('catid' => $cat['catid']));
    $checkConfig = getConfigMP(array('catid' => $cat['catid'], 'mpnom2' => $tabMP['market_place_nom2']));
    $empty=$nbPdtsCat==0?"empty":"";
    
    $color="";
    if($nbPdtsCat > 0){
        if($checkConfig<count($marketplaces[$tabMP['market_place_nom2']])) $color="#FFAA00";
        if($checkConfig == 0) $color = 'red';
    }
    
    $champs="";
    foreach($marketplaces[$tabMP['market_place_nom2']] as $champ){
        $detail_champs = getChamps(array('catid' => $cat['catid'], 'mpnom2' => $tabMP['market_place_nom2'], 'champs' => $champs));
        
        $champs.=<<<HTML
        $champ
        <textarea name="field_$champ">$detail_champs</textarea>
HTML;
    }
    
    return <<<HTML
        <div class="pdm_cat">
                <b class="$empty" style="color:$color;">$cat[libelleFR] (<i>$nbPdtsCat</i>) <img src="images/editer.gif" onclick="editCat(event,$cat[catid])"></b>
                <div class="content">
                    <form method="post" name="form$cat[catid]" class="hidden">
                        <h3>$cat[libelleFR]</h3>
                        <div>
                            <input type="hidden" name="maj_config" value="1">
                            <input type="hidden" name="catid" value="$cat[catid]">
                            <input type="hidden" name="nom2" value="$tabMP[market_place_nom2]">
                            $champs
                            <input type="button" onclick="testfields(document.form$cat[catid])" value="Visualiser">
                            <input type="button" onclick="editCat(event,$cat[catid])" value="Annuler">
                            <input type="submit" value="Enregistrer">
                        </div>
                    </form>
                    $tblsscat
                </div>
        </div>
HTML;
}

if(isset($_GET['mpid'])){
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

include "./footer.php";

?>


