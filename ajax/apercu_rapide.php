<?php
require_once($_SERVER['DOCUMENT_ROOT']."/global.php");

$artid = intval($_GET['artid']);
if($artid==0) exit;

$accesPanier = intval($_GET['accesPanier']);

$packs=new Pack($DB_site);
$kdopack=$packs->kdos_pack($artid);

$onglets = $DB_site->query_first('
  SELECT `bdd_table`
  FROM `typearticle`
  WHERE `typearticleid` = (SELECT typearticleid FROM article WHERE artid='.$artid.')');
$tables = explode(',', $onglets[0]);

$tabArticle=$DB_site->query_first("SELECT a.*,a.libelle".$template->langue." AS libelle,gamme".$template->langue." AS gamme,a_s.active AS active, spec.couleur_fab AS couleur FROM article a INNER JOIN article_site a_s USING(artid) INNER JOIN $tables[0] as spec USING(artid) WHERE artid=".$artid." AND a_s.site_code='".$template->site_code()."'");
if($tabArticle=="") exit;
$article_prices=article_prices($tabArticle[artid],$template->tva_ref(),$template->site_code(),$sitepro);
$marque=$DB_site->query_first("SELECT m.* FROM marque m INNER JOIN article_marque USING(marqueid) WHERE artid=".intval($tabArticle['artid']));
$options=$DB_site->query_first("SELECT COUNT(*) FROM article_caractval WHERE artid=".intval($tabArticle['artid']));
$tabArticle['marque']=$marque['libelle'];

$etiq_energie = '';
$tabArticle['class_energetique'] = strtoupper(preg_replace('~[^a-gA-G\+]+~', '', $tabArticle['class_energetique']));
$labels_energetiques = preg_split('@(?=A|B|C|D|E|F|G)@', $tabArticle['class_energetique']);
$label_energetique = str_replace('+','plus',strtolower($labels_energetiques[1]));
$etiq_energie="<img alt=\"".$template->translate("/article/champs/classe_energetique","F")." ".$label_energetique."\" src=\"/images/templates/energie_".$label_energetique.".jpg\">";
$l1 = substr($label_energetique,0,1);
$lbl_energie = $l1;
if(count($labels_energetiques) > 2) {
  foreach ($labels_energetiques as $key => $value) {
    if($key > 1) {
      $label_energetique2 = str_replace('+','plus',strtolower($labels_energetiques[$key]));
      $etiq_energie2="<img alt=\"".$template->translate("/article/champs/classe_energetique","F")." ".$label_energetique2."\" src=\"/images/templates/energie_".$label_energetique2.".jpg\">";
      $l2 = substr($label_energetique2,0,1);
      if($l1 < $l2) {
        $etiq_energie = $etiq_energie2;
        $lbl_energie = $l2;
        $l1 = $l2;
      } else if($l1 === $l2) {
        if(strlen($label_energetique) > strlen($label_energetique2)) {
          $etiq_energie = $etiq_energie2;
          $lbl_energie = $l2;
          $l1 = $l2;
        }
      }
    }
  }
}

$tabArticle['marque_nom']    = strtoupper($tabArticle['marque_nom']);

$template->parts["ajoutSelect"]="";
for($i=1;$i<=30;$i++) $template->parts["ajoutSelect"].="<option value=\"$i\">$i</option>";

//Visuel article
$template->parts["imgart"] = "<img src=\"".article_image($artid,280,280,$kdopack)."\" alt=\"".$tabArticle[$libelle]."\" class=\"visuel\">";

//Visuel marque
if ($tabArticle['image'] != ""){
    $template->parts["logomarque"] = "<img src=\"/marques/".$marque['marqueid']."_100_28.jpg\" alt=\"".$marque['type'.$template->langue]."\">" ;
}

//Variables
$template->parts["marque_libelle"] = $marque['libelle'];
$template->parts["article_libelle"] = $tabArticle['libelle'];
$template->parts["artcode"] = $tabArticle["artcode"];
$template->parts["gamme"] = $tabArticle['gamme'];


$finition=$DB_site->query_first("SELECT finitionid,nom".$template->langue." FROM articlerecherche_finition INNER JOIN recherche_finition USING(finitionid) WHERE artid=".$tabArticle['artid']);
if($finition[1]!="") $template->parts['caractprincipales'] .= "<li><b>".$template->translate("/article/champs/niveau_gamme","F")." : </b>$finition[1]</li>";

if($tabArticle[couleur]!="" && $template->translate($tabArticle[couleur])!="") $template->parts['caractprincipales'] .= "<li><b>".$template->translate("/article/champs/coloris_modele","F")." : </b>".$template->translate($tabArticle[couleur])."</li>";
$template->parts['caractprincipales'] .= "<li><b>".$template->translate("/article/champs/dimensions","F")." : </b>".sprintf($template->translate("/article/champs/dimensions_format"),$tabArticle[hauteur],$tabArticle[largeur],$tabArticle[profondeur])."</li>";
//if($tabArticle['class_energetique']!="" && $template->translate($tabArticle['class_energetique']) !='') $template->parts['caractprincipales'] .= "<li><b>".$template->translate("/article/champs/classe_energetique","F")." : </b>".$template->translate($tabArticle['class_energetique'])."</li>";




$template->parts["strikeout"]="<div class=\"estrikeout\"></div>";
$template->parts["pc"]="";

$template->parts["pcprice"]=round($article_prices['remise'])."%";
$template->parts["pricediff"]=$template->price_format($article_prices['prixconseille']-$article_prices['prix']);
$template->parts["soprice"]=$template->price_format($article_prices['prixconseille']);
$template->parts["pricetax"]=$template->translate($sitepro?"/site/ht":"/site/ttc","U");
$template->parts["price"]=$template->price_format($article_prices['prix']);
$template->parts["lien_fiche"]=$template->mkurl("article",$tabArticle['artid'],$tabArticle);

if($tabArticle['energy_label'] != ""){
    $label_energetique = str_replace('+','plus',strtolower($tabArticle['energy_label']));
    if(file_exists("../images/templates/energie_".$label_energetique.".jpg")){
        $iconenergie = "<img class=\"picto\" alt=\"Classe energétique ".$label_energetique."\" src=\"/images/templates/energie_".$label_energetique.".jpg\">";
    }
}

if (isset($user_info['paysid'])) {
  $idPays=$user_info['paysid'];
} else {
  $idPays=$template->pays_ref();
}
$tabGaranties=DAOgarantie::loadMaxGarantieArtByPays($DB_site,$template->site_code(),'FR',$idPays,$tabArticle['artid']);
$garantie=$tabGaranties[0];
if ($garantie->cumul==1) {
  //on cherche dans le tableau qui contient toutes les garanties offertes celle du fournisseur des années de garanties avant l'extension et du même type
  $pointer=DAOgarantie::searchGarantieToAddExtension($DB_site,$garantie->type_garantie,$tabGaranties,$garantie->garantieid);
  //on prend la durée de cette garantie pour l'ajouter à l'extension
  $dureeBase=$tabGaranties[$pointer]->duree;
  $duree=$garantie->duree+$dureeBase;
} else {
  $duree=$garantie->duree;
}
$dureeGarantie=$duree;
if(!empty($garantie)){
    if(file_exists("../images/templates/".$template->langue."/garantie_".$dureeGarantie.".jpg")){
        $icongarantie = "<img class=\"picto\" src=\"/images/templates/".$template->langue."/garantie_".$dureeGarantie.".jpg\">";
    } 
}

if(file_exists("../images/templates/origine-gd-".$tabArticle['paysfabrication'].".jpg")){
    $template->parts["icons"] .= "<img class=\"picto\" src=\"/images/templates/origine-gd-".$tabArticle['paysfabrication'].".jpg\" alt=\"".$template->translate("/article/origines/".$tabArticle['paysfabrication'],"F")."\" align=\"absmiddle\">";
}


$promo=$DB_site->query_first("SELECT COUNT(*) FROM promo WHERE artid=".$tabArticle[artid]);
$secondchoix=$DB_site->query_first("SELECT COUNT(*) FROM secondchoix WHERE artid=".$tabArticle[artid]);
if (round($article_prices['prix']) != round($article_prices['prixconseille'])){
    if($promo[0]>0 || $secondchoix[0]>0){
	$template->parts["pc"]="<p class=\"reduc\">-".round($article_prices['remise'])."%</p>";
	$template->parts["strikeout"]="<p class=\"prix_barre\">".$template->price_format($article_prices['prixconseille'])."</p>";
    }
    $template->evaltemplate("ArticlePrixBarre","prixbarre");
}


if($article_prices['vf']) {

    $template->parts["pc"]="<div class=\"pc\">".round(-$article_prices['remise'])."%</div>";
    $template->parts["strikeout"]="<div class=\"strikeout\"><strike>".$template->price_format($article_prices['prixconseille'])."</strike></div>";
    $template->parts["venteflash"]="<div class=\"title_vf\">".$template->translate("/article/champs/fin_vente_flash")." <span id=\"vfdecompte\" class=\"compt_vf\"></span></div>";
}

if($article['ecopart']>0){
  $template->parts["ecopart"]=($sitepro?$article['ecopartht']:$article["ecopart"]);
  $template->evaltemplate("ArticleEcopart","ecoparttxt");
}

$template->parts["btnComparer"]="";
if($comparateur->compare_type($tabArticle['typearticleid'])){
    if($comparateur->in_articles($artid)){
	$classComp="btn_comparer_on";
	$txtComp=$template->translate("/comparateur/ajoute_au_comparateur","F");
    }else{
	$classComp="btn_comparer_off";
	$txtComp=$template->translate("/comparateur/comparer","F");
    }
    $template->parts["btnComparer"]=<<<HTML
    <a id="btn_comparaison" class="$classComp btn_comparer" onclick="toggleCompare($artid);" href="javascript:void(0)">
      <div class="case"></div>
      <span>$txtComp</span>
    </a>
HTML;
    }


$template->parts["artid"] = $artid;



if(!isset($_COOKIE['admin_username'])){
    $artvus=$DB_site->query_first("SELECT COUNT(*) FROM article_vu WHERE artid=$artid AND s='$s'");
    if($artvus[0]==0){
      $DB_site->query("INSERT INTO article_vu SET artid=$artid, nb=1, s='$s', userid=".intval($user_info['userid']).", date=".time());
    }else{
      $DB_site->query("UPDATE article_vu SET nb=nb+1 WHERE artid=$artid AND s='$s'");
    }
}

/*
 * GENERATION DES TEMPLATES
 */

//Si le produit n'est pas archivé, on affiche les templates
if($tabArticle['active'] != 3){

      //***********************************//
      // DEBUT GESTION DELAIS DE LIVRAISON //
      //***********************************//
      $d = delailivraison($tabArticle);
      $jourlivraison = mktime(0,0,0)+(86400*$d['delailivraison']);
      $template->parts["delaitxt"]      = $d['delailivraison']." ".$template->translate("/article/champs/jours_transport_inclus");
      $template->parts["datelivraison"] = date("d/m/Y",$jourlivraison);

      //En stock
      if($d['stock'] != false) $classid = "2";
      $template->parts["stock_class"]   = "stockvert".$classid;
      $template->parts["stock_libelle"] = $template->translate("/article/vignette/en_stock","F");

      //Avec délai
      if($d['delailivraison'] > 15){
	  $template->parts["stock_class"]   = "stockorange";
	  $template->parts["stock_libelle"] = str_replace('[nb_jours]',$d['delailivraison'],$template->translate("/article/stock/livraison_sous","F"));
      } 

      //En réappro
      if($d['date_appro'] != false){
	  $template->parts["stock_libelle"]   = $template->translate("/article/stock/reappro_le")." ".date("d/m/Y",$d['date_appro']);
	  //$template->parts["datelivraison"] = date("d/m/Y",$jourlivraison);
      }

     //Template "Prix barré"
     if($affiche_prix_barre){
          $template->evaltemplate("ArticlePrixBarre","prixbarre");
     }
     
     //Template "Prix"
     $template->evaltemplate("ArticlePrix","prices");
     
     
     //Template "EcoPart" (si montant > à 0)
     if($tabArticle['ecopart'] > 0){
          $template->evaltemplate("ArticleEcopart","ecoparttxt");
     }
     
     //Livraison offerte
     $fraisport=$DB_site->query_first("SELECT gratuit FROM fraisport WHERE paysid=57 LIMIT 1");
      $totallivr=$fraisport[0];
      if($tabArticle['prix'] >= $totallivr){
	  $template->evaltemplate("ArticleLivraison","article_livraison");
	  //$iconlivr = "<div class=\"iconlivraison\"><img src=\"images/templates/".$template->langue."/livraison.png\"></div>";
      }
     
      if($icongarantie) $template->parts["icons"] .= $icongarantie;
      if($iconenergie) $template->parts["icons"] .= $iconenergie;
    
      $requete=$DB_site->query("SELECT iconid FROM article_icon ai INNER JOIN icons ON(icons.id=ai.iconid) WHERE active=1 AND ai.artid=".$tabArticle['artid']);
      while($icon=$DB_site->fetch_array($requete)){
	  $path="../images/icons/".$template->langue."/".$icon['iconid'].".png";
	  if(file_exists($path)) $template->parts["icons"].="<img src=\"/images/icons/".$template->langue."/".$icon['iconid'].".png\" class=\"picto\">";
      }

      if(is_null($kdopack) || $kdopack==0){
	      //Template "Mise au panier" selon statut du produit
	      if ($tabArticle['commandable'] == '0' && $options[0]==0){
		      $template->evaltemplate("ArticleAjout0","ajout");
	      } elseif($tabArticle['commandable']=='1' && $options[0]==0){
          if($accesPanier == 1)
		        $template->evaltemplate("ArticleAjoutAccPanier","ajout");
          else
            $template->evaltemplate("ArticleAjout","ajout");
	      } elseif($tabArticle['commandable']=='2'){
		      $template->evaltemplate("ArticleAjout2","ajout");
	      }
      } else {
	      $template->parts["kodartid"]=$kdopack;
	      $template->evaltemplate("ArticleAjout3","ajout");
      }
     
} else {
     //Template "Mise au panier" => Produit indisponible
     $template->evaltemplate("ArticleAjout2","ajout");
}

$template->evaltemplate("AjoutPanier","ajoutpanier");

/*
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <link href="/css/css-refonte.css" rel="stylesheet" type="text/css">
    <link href="/css/css-refonte-cc.css" rel="stylesheet" type="text/css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css" type="text/css">
</head>
<body>
*/




?>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <link href="/css/css.css" rel="stylesheet" type="text/css">
</head>
<body>
<?php 

if($accesPanier == 1)
  echo $template->evaltemplate("apercu_rapide_panier","html");
else
  echo $template->evaltemplate("apercu_rapide","html");

echo stripslashes($template->parts["html"]);
?>
</body>