<?php
include_once($_SERVER['DOCUMENT_ROOT'].'/global.php');

$libelle = "libelle".$template->langue;

$artid = $_GET['artid'];

//Test article si actif et existant
if (!isset($artid) or $artid == "") {
    
    header("Status: 301 Moved Permanently");
    header("Location: /");
    exit();

} else {
    
    $sql = "SELECT a_s.active, redirect, libelle".$template->langue." AS libelle, libelle".$template->langue.", m.libelle AS marque";
    $sql .= " FROM article";
    $sql .= " INNER JOIN article_site a_s USING(artid)";
    $sql .= " INNER JOIN article_marque am USING(artid) INNER JOIN marque m USING(marqueid)";
    $sql .= " WHERE a_s.site_code = '".$template->site_code()."'";
    $sql .= " AND artid = ".$artid;
    $art_actif = $DB_site->query_first($sql);

    $redirecturl = "/";

    if(intval($art_actif['redirect']) > 0){
        
        $sql = "SELECT artid, libelle".$template->langue." AS libelle, active";
        $sql .= " FROM article";
        $sql .= " WHERE artid = ".intval($art_actif['redirect']);
        $artredirect = $DB_site->query_first($sql);

        if($artredirect != "" && $artredirect['active'] == '1') $redirecturl = "/art,".$artredirect['artid'].",".url_rewrite($artredirect['libelle']).".htm";
    }

    if($art_actif[0] == 0 and !isset ($prov)){
        
        header("Status: 302 Moved Temporarily", false, 302);
        header("Location: ".$redirecturl);
        exit();
    }
    
    if($art_actif[0]==2 and !isset ($prov)){
        
        header("Status: 301 Moved Permanently", false, 301);
        header("Location: ".$redirecturl);
        exit();
    }

    $goodurl="/art,".$artid.",".url_rewrite($art_actif['libelle']).".htm";
    $goodurl=$template->mkurl("article",$artid,$art_actif);
    
    if(parse_url($_SERVER['REQUEST_URI'],PHP_URL_PATH)!=$goodurl) $template->redirect($goodurl,301);
}

// statistiques
// 1 page vue en + pour cet article
$includepage = "<SCRIPT SRC=\"articleprix.js\" type=\"text/javascript\"></SCRIPT>";
$icongarantie=$iconlivr="";

$jma = date ('Y-m-d');

$sql = "SELECT *";
$sql .= " FROM statistique_produit";
$sql .= " WHERE datestatistique = '".$jma."'";
$sql .= " AND artid = ".$artid;
$stat_art = $DB_site->query_first($sql);

if ($stat_art[0] == ""){
    
    $sql = "INSERT INTO statistique_produit (";
    $sql .="artid";
    $sql .= ", datestatistique";
    $sql .= ", visites";
    $sql .= ") VALUES (";
    $sql .= $artid;
    $sql .= ", '".$jma."'";
    $sql .= ", '1'";
    $sql .= ")";
    $new_stat = $DB_site->query($sql);

} else {

    $sql = "UPDATE statistique_produit SET";
    $sql .= " visites = visites + 1";
    $sql .= " WHERE datestatistique = '".$jma."'";
    $sql .= " AND artid = ".$artid;
    $new_stat = $DB_site->query($sql);
}

// 1 page vue en + pour cet article et ce user
if($_COOKIE['userid'] != ""){
    
    $jma = date('Y-m-d');

    $sql = "SELECT *";
    $sql .= " FROM statistique_produit_utilisateur";
    $sql .= " WHERE userid = ".$_COOKIE['userid'];
    $sql .= " AND datestatistique = '".$jma."'";
    $sql .= " AND artid = ".$artid;
    $stat_user_art = $DB_site->query_first($sql);

    if ($stat_user_art[0] == ""){
        
        $sql = "INSERT INTO statistique_produit_utilisateur (";
        $sql .= "artid";
        $sql .= ", userid";
        $sql .= ", datestatistique";
        $sql .= ", visites";
        $sql .= ") VALUES (";
        $sql .= "'$artid'";
        $sql .= ", ".$_COOKIE['userid'];
        $sql .= ", '".$jma."'";
        $sql .= ", '1'";
        $sql .= ")";
        $new_stat = $DB_site->query($sql);

    } else {

        $sql = "UPDATE statistique_produit_utilisateur SET";
        $sql .= " visites = visites + 1";
        $sql .= " WHERE userid = ".$_COOKIE['userid'];
        $sql .= " AND datestatistique = '".$jma."'";
        $sql .= " AND artid = ".$artid;
        $new_stat = $DB_site->query($sql);
    }
}

//Sélection détail de l'article
$sql = "SELECT a.*, m.libelle AS marque";
$sql .= " FROM article a";
$sql .= " INNER JOIN article_marque am USING(artid)";
$sql .= " INNER JOIN marque m USING(marqueid)";
$sql .= " WHERE artid = '".$artid."'";
$article = $DB_site->query_first($sql);

//article commandable pour utilisateur admin
if(isset($_COOKIE['admin_username']) && preg_match("/dedie(pro)?\.|localhost\.|test\./",$_SERVER['HTTP_HOST'])){
    $article['commandable']='1';
}

$template->parts['js_page'] = "<script src=\"/javascripts/photoswipe.min.js\" defer></script>";
//$template->parts['js_page'] .= "<script src=\"/javascripts/photoswipe-ui-default.min.js\" defer></script>";
$template->parts['js_page'] .= "<script src=\"/javascripts/jquery.manific-popup.js\" defer></script>";
$template->parts['css_page'] = "<link rel=\"stylesheet\" type=\"text/css\" href=\"//cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.css\"/>\n";

$template->parts["itempropArtid"] = $artid;

//Consommation annuelle en KW
$article['conso_euro'] = round(intval($article['conso_kw']) * $prixkw * 365);

//Récupération des informations sur la catégorie mère
unset($menu);
$categorie = new categorie($DB_site,$article['catid'],$template->langue);
$menus=$categorie->getMenus();

if($categorie->menu_appartenance>0)
    $menu=$menus[$categorie->menu_appartenance];
$fil_cats[]=$categorie;

while($categorie->parentid != 0){
    $categorie = new categorie($DB_site,$categorie->parentid,$template->langue);
    if(is_null($menu)) $fil_cats[]=$categorie;
    if($categorie->menu_appartenance>0) $menu=$menus[$categorie->menu_appartenance];
}
//Infos sur la marque
$sql = "SELECT *";
$sql .= " FROM article_marque am, marque m";
$sql .= " WHERE am.marqueid = m.marqueid";
$sql .= " AND am.artid = '".$article['artid']."'";
$marq = $DB_site->query_first($sql);
$marque = new marque($DB_site,$marq['marqueid'],$template->langue);

$template->parts["logomarquedetail"] = "<img src=\"".$marque->logo(150,30)."\" alt=\"".$marq['type'.$template->langue]."\" width=\"150\" height=\"30\">" ;
$template->parts["fil_ariane"] = "";
$pos=2;
//Mise en place fil d'ariane
if($art_actif[0]!=3 || $art_actif[0]!=5){
  if($menu != 0){
    $template->parts["fil_ariane"].=<<<HTML
      &gt; <span property="itemListElement" typeof="ListItem">
      <a property="item" typeof="WebPage" href="/$menu[url].htm"><span property="name"> $menu[libelle]</span></a>
      <meta property="position" content="$pos">
      </span>
HTML;
     $pos++;
     } 

    $fil_cats=array_reverse($fil_cats);
    foreach($fil_cats as $fil_cat){
      $url=$fil_cat->url();
      $template->parts["fil_ariane"].=<<<HTML
      &gt; <span property="itemListElement" typeof="ListItem">
      <a property="item" typeof="WebPage" href="$url[url]"><span property="name"> $fil_cat->libelle</span></a>
      <meta property="position" content="$pos">
      </span>
HTML;
    $pos++;
    }
}else{
    $marque->archive_load();
    $template->parts["fil_ariane"].="&gt; <span property=\"itemListElement\" typeof=\"ListItem\"><a property=\"item\" typeof=\"WebPage\" href=\"/".$template->translate("/archives/url")."\"><span property=\"name\">".$template->translate("/archives/fil")."</span></a><meta property=\"position\" content=\"".$pos."\"></span>
    &gt; <span property=\"itemListElement\" typeof=\"ListItem\"><a property=\"item\" typeof=\"WebPage\" href=\"/".$marque->archive_url."\"><span property=\"name\">".$marque->libelle."</span></a><meta property=\"position\" content=\"".($pos+1)."\"></span>";
    $pos=$pos+2;
}

$fillibelle=$article[$libelle]." ".ucfirst(strtolower($marq['libelle']));
$urlArt = $template->mkurl("article",$article['artid'],$article);
 //pour le dernier lien du breadcrumb on ne met pas l'attribut typeof="v:Breadcrumb" sinon cela cause des erreurs d'explorations google
$template->parts["fil_ariane"].=<<<HTML
  &gt; <span property="itemListElement" typeof="ListItem"><a property="item" typeof="WebPage" href="$urlArt"><span property="name">$fillibelle</span></a><meta property="position" content="$pos"></span>
HTML;


$tauxtvaref = $template->tva_ref();
$txtva = (1 + $tauxtvaref[$article['tauxchoisi']] / 100);

$titre2 = "Titre2$langue" ;
$temp   = "title$langue";

//Infos finitions
$sql = "SELECT finitionid,nom".$template->langue;
$sql .= " FROM articlerecherche_finition";
$sql .= " INNER JOIN recherche_finition USING(finitionid)";
$sql .= " WHERE artid = ".$artid;
$finition = $DB_site->query_first($sql);

$datejour = date("Y") ."-". date("m") . "-" . date("d") ;

if($article["plus"] != ""){
    $template->parts["plustxt"]=$article["plus"];
    $template->evaltemplate("plusProduit","plus");
}

$template->parts["artid"]=$article['artid'];


$template->parts["descriptionh2"]=$article['libelle'.$template->langue]." ".ucwords(strtolower($marq['libelle']));

/**** DEBUT caract prinipales *******/
$template->parts['caractsprinc']="";
switch($article['typearticleid']){
  case '2':
    $plaques = $DB_site->query("SELECT type_table FROM article_table_cuisson WHERE artid=".$artid);
    $nb_plaques = $DB_site->num_rows($plaques);
    if($nb_plaques>1){
      $template->parts['caractsprinc'].="<div><p>".$template->translate("/article/caract_principales/plaque")." : ".$nb_plaques." </p></div>";
      $template->parts['caractsprinc'].="<div><p>".$template->translate("/article/caract_principales/foyers")." : ".$template->translate("/article/caract_principales/foyers2")." </p></div>";
    }else{
      if($nb_plaques > 0){
        while($plaque = $DB_site->fetch_array($plaques)){
          $template->parts['caractsprinc'].="<div><p>".$template->translate("/article/caract_principales/type_plaque")." : ".$template->translate($plaque['type_table'])." </p></div>";
          //nb foyers
          $nb_foyers = $DB_site->query_first("SELECT COUNT(table_id) FROM article_foyers af INNER JOIN article_table_cuisson atc ON af.table_id=atc.id WHERE atc.artid = '$artid'");
          $template->parts['caractsprinc'].="<div><p>".$template->translate("/article/caract_principales/foyers")." : ".$nb_foyers[0]." </p></div>";
        }
      }
    }

    $nb_fours = $DB_site->query_first("SELECT nettoyage FROM article_four WHERE artid = '$artid'");
    $dimensions = $DB_site->query_first("SELECT hauteur, largeur, profondeur, nb_fours, couleur_fab, class_energetique FROM article_cuisiniere WHERE artid = '$artid'");
    $template->parts['caractsprinc'].="<div><p>".$template->translate("/article/caract_principales/fours")." : ".$template->translate($dimensions['nb_fours'])." </p></div>";
    if(!is_null($nb_fours)) $template->parts['caractsprinc'].="<div><p>".$template->translate("/article/caract_principales/nettoyage_four")." : ".$template->translate($nb_fours[nettoyage])." </p></div>";  
    
    $template->parts['caractsprinc'].="<div><p>".$template->translate("/article/caract_principales/coloris")." : ".$template->translate($dimensions['couleur_fab'])."</p></div>";
    $template->parts['caractsprinc'].="<div><p>".$template->translate("/article/caract_principales/dimensions")." : H".$dimensions['hauteur']."xL".$dimensions['largeur']."xP".$dimensions['profondeur']." mm </p></div>";
    $class_energetique = $dimensions[class_energetique];
  break;

  case '1':
    $plaques = $DB_site->query("SELECT type_table FROM article_table_cuisson WHERE artid=".$artid);
    $nb_plaques = $DB_site->num_rows($plaques);
    if($nb_plaques>1){
      $template->parts['caractsprinc'].="<div><p>".$template->translate("/article/caract_principales/plaque")." : ".$nb_plaques." </p></div>";
      $template->parts['caractsprinc'].="<div><p>".$template->translate("/article/caract_principales/foyers")." : ".$template->translate("/article/caract_principales/foyers2")." </p></div>";
    }else{
      if($nb_plaques > 0){
        while($plaque = $DB_site->fetch_array($plaques)){
          $template->parts['caractsprinc'].="<div><p>".$template->translate("/article/caract_principales/type_plaque")." : ".$template->translate($plaque['type_table'])." </p></div>";
          //nb foyers
          $nb_foyers = $DB_site->query_first("SELECT COUNT(table_id) FROM article_foyers af INNER JOIN article_table_cuisson atc ON af.table_id=atc.id WHERE atc.artid = '$artid'");
          $template->parts['caractsprinc'].="<div><p>".$template->translate("/article/caract_principales/foyers")." : ".$nb_foyers[0]." </p></div>";
        }
      }
    }
    $nb_fours = $DB_site->query_first("SELECT nettoyage FROM article_four WHERE artid = '$artid'");
    $dimensions = $DB_site->query_first("SELECT hauteur, largeur, profondeur,couleur_fab, nb_fours,class_energetique FROM article_piano WHERE artid = '$artid'");
    
    $template->parts['caractsprinc'].="<div><p>".$template->translate("/article/caract_principales/fours")." : ".$template->translate($dimensions[nb_fours])." </p></div>";
    $template->parts['caractsprinc'].="<div><p>".$template->translate("/article/caract_principales/nettoyage_four")." : ".$template->translate($nb_fours[nettoyage])." </p></div>";

    $template->parts['caractsprinc'].="<div><p>".$template->translate("/article/caract_principales/coloris")." : ".$template->translate($dimensions['couleur_fab'])."</p></div>";
    $template->parts['caractsprinc'].="<div><p>".$template->translate("/article/caract_principales/dimensions")." : H".$dimensions['hauteur']."xL".$dimensions['largeur']."xP".$dimensions['profondeur']." mm </p></div>";

    $class_energetique = $dimensions[class_energetique];
  break;

  case '3'://plaque de cuisson seule
    $plaque = $DB_site->query_first("SELECT type_table, hauteur, largeur, profondeur, couleur_fab as couleur, class_energetique FROM article_table_cuisson WHERE artid=".$artid);
    $type_plaque = explode(",",$plaque['type_table']);
    $text="";
    foreach($type_plaque as $type){
        $text .= $template->translate($type);
        $text .=",";
    }
    $text = substr($text, 0,-1);
    $template->parts['caractsprinc'].="<div><p>".$template->translate("/article/caract_principales/type_plaque")." : ".$text." </p></div>";
    //nb foyers
    $nb_foyers = $DB_site->query_first("SELECT COUNT(table_id) FROM article_foyers af INNER JOIN article_table_cuisson atc ON af.table_id=atc.id WHERE atc.artid = '$artid'");
    $template->parts['caractsprinc'].="<div><p>".$template->translate("/article/caract_principales/foyers")." : ".$nb_foyers[0]." </p></div>";
    $template->parts['caractsprinc'].="<div><p>".$template->translate("/article/caract_principales/coloris")." : ".$template->translate($plaque['couleur'])."</p></div>";
    $template->parts['caractsprinc'].="<div><p>".$template->translate("/article/caract_principales/dimensions")." : H".$plaque['hauteur']."xL".$plaque['largeur']."xP".$plaque['profondeur']." mm </p></div>";
    $class_energetique = $plaque[class_energetique];

  break;

  case '21'://frigo seul
    $result = $DB_site->query_first("SELECT volume_cavite, couleur_fab as couleur, hauteur, largeur, profondeur,class_energetique FROM article_frigo WHERE artid=".$artid);
    $template->parts['caractsprinc'].="<div><p>".$template->translate("/article/caract_principales/capacite")." : ".$template->translate($result['volume_cavite'])." L</p></div>";
    $template->parts['caractsprinc'].="<div><p>".$template->translate("/article/caract_principales/coloris")." : ".$template->translate($result['couleur'])."</p></div>";
    $template->parts['caractsprinc'].="<div><p>".$template->translate("/article/caract_principales/dimensions")." : H".$result['hauteur']."xL".$result['largeur']."xP".$result['profondeur']." mm </p></div>"; 
    $class_energetique = $result[class_energetique];
  break;

  case '22': //congel seul
    $result = $DB_site->query_first("SELECT volume_cavite, couleur_fab as couleur, hauteur, largeur, profondeur, class_energetique FROM article_congel WHERE artid=".$artid);
    $template->parts['caractsprinc'].="<div><p>".$template->translate("/article/caract_principales/capacite")." : ".$template->translate($result['volume_cavite'])." L</p></div>";
    $template->parts['caractsprinc'].="<div><p>".$template->translate("/article/caract_principales/coloris")." : ".$template->translate($result['couleur'])."</p></div>";
    $template->parts['caractsprinc'].="<div><p>".$template->translate("/article/caract_principales/dimensions")." : H".$result['hauteur']."xL".$result['largeur']."xP".$result['profondeur']." mm </p></div>";
    $class_energetique = $result[class_energetique];
  break;

  case '7'://BBQ
    $result = $DB_site->query_first("SELECT type_bbq, couleur_fab as couleur, hauteur, largeur, profondeur,systeme_allumage, class_energetique FROM article_bbq WHERE artid=".$artid);
    $template->parts['caractsprinc'].="<div><p>".$template->translate("/article/caract_principales/type_bbq")." : ".$template->translate($result['type_bbq'])."</p></div>";
    $template->parts['caractsprinc'].="<div><p>".$template->translate("/article/caract_principales/systeme_allumage")." : ".$template->translate($result['systeme_allumage'])."</p></div>";
    $template->parts['caractsprinc'].="<div><p>".$template->translate("/article/caract_principales/coloris")." : ".$template->translate($result['couleur'])."</p></div>";
    $template->parts['caractsprinc'].="<div><p>".$template->translate("/article/caract_principales/dimensions")." : H".$result['hauteur']."xL".$result['largeur']."xP".$result['profondeur']." mm </p></div>";
    $class_energetique = $result[class_energetique];
  break;

  case '6'://plancha
    $result = $DB_site->query_first("SELECT type_plancha, couleur_fab as couleur, hauteur, largeur, profondeur,systeme_allumage, class_energetique FROM article_plancha WHERE artid=".$artid);
    $template->parts['caractsprinc'].="<div><p>".$template->translate("/article/caract_principales/type_plancha")." : ".$template->translate($result['type_plancha'])."</p></div>";
    $template->parts['caractsprinc'].="<div><p>".$template->translate("/article/caract_principales/systeme_allumage")." : ".$template->translate($result['systeme_allumage'])."</p></div>";
    $template->parts['caractsprinc'].="<div><p>".$template->translate("/article/caract_principales/coloris")." : ".$template->translate($result['couleur'])."</p></div>";
    $template->parts['caractsprinc'].="<div><p>".$template->translate("/article/caract_principales/dimensions")." : H".$result['hauteur']."xL".$result['largeur']."xP".$result['profondeur']." mm </p></div>";
    $class_energetique = $result[class_energetique];
  break;
  case '26'://combine froid
    $result = $DB_site->query_first("SELECT type_refrigerateur, couleur_fab as couleur, hauteur, largeur, profondeur,volume_cavite, class_energetique FROM article_froid WHERE artid=".$artid);
    $template->parts['caractsprinc'].="<div><p>".$template->translate("/article/caract_principales/type_combine")." : ".$template->translate($result['type_refrigerateur'])."</p></div>";
    $template->parts['caractsprinc'].="<div><p>".$template->translate("/article/caract_principales/capacite_totale")." : ".$template->translate($result['volume_cavite'])." L</p></div>";
    $frigo = $DB_site->query_first("SELECT volume_cavite FROM article_frigo_dependant WHERE artid =".$artid);
    $template->parts['caractsprinc'].="<div><p>".$template->translate("/article/caract_principales/capacite_frigo")." : ".$template->translate($frigo['volume_cavite'])." L</p></div>";
    $congel = $DB_site->query_first("SELECT volume_cavite FROM article_congel_dependant WHERE artid =".$artid);
    $template->parts['caractsprinc'].="<div><p>".$template->translate("/article/caract_principales/capacite_congel")." : ".$template->translate($congel['volume_cavite'])." L</p></div>";

    $template->parts['caractsprinc'].="<div><p>".$template->translate("/article/caract_principales/coloris")." : ".$template->translate($result['couleur'])."</p></div>";
    $template->parts['caractsprinc'].="<div><p>".$template->translate("/article/caract_principales/dimensions")." : H".$result['hauteur']."xL".$result['largeur']."xP".$result['profondeur']." mm </p></div>";
    $class_energetique = $result[class_energetique];
  break;

  case '38'://cellule refroidissement
    $result = $DB_site->query_first("SELECT couleur_fab as couleur, hauteur, largeur, profondeur,volume_cavite, class_energetique FROM article_cellule_refroidissement WHERE artid=".$artid);
    $template->parts['caractsprinc'].="<div><p>".$template->translate("/article/caract_principales/volume_cavite")." : ".$template->translate($result['volume_cavite'])." L</p></div>";
    $template->parts['caractsprinc'].="<div><p>".$template->translate("/article/caract_principales/coloris")." : ".$template->translate($result['couleur'])."</p></div>";
    $template->parts['caractsprinc'].="<div><p>".$template->translate("/article/caract_principales/dimensions")." : H".$result['hauteur']."xL".$result['largeur']."xP".$result['profondeur']." mm </p></div>";
    $class_energetique = $result[class_energetique];
  break;
  case '28'://credence
    $result = $DB_site->query_first("SELECT couleur_fab as couleur, hauteur, largeur, profondeur FROM article_credence WHERE artid=".$artid);
    $template->parts['caractsprinc'].="<div><p>".$template->translate("/article/caract_principales/coloris")." : ".$template->translate($result['couleur'])."</p></div>";
    $template->parts['caractsprinc'].="<div><p>".$template->translate("/article/caract_principales/dimensions")." : H".$result['hauteur']."xL".$result['largeur']."xP".$result['profondeur']." mm </p></div>";
  break;
  case '20'://Accessoires
    $result = $DB_site->query_first("SELECT couleur_fab as couleur, hauteur, largeur, profondeur,type_accessoire FROM article_accessoires_article WHERE artid=".$artid);
    $template->parts['caractsprinc'].="<div><p>".$template->translate("/article/caract_principales/type")." : ".$template->translate($result['type_accessoire'])."</p></div>";
    $template->parts['caractsprinc'].="<div><p>".$template->translate("/article/caract_principales/coloris")." : ".$template->translate($result['couleur'])."</p></div>";
    $template->parts['caractsprinc'].="<div><p>".$template->translate("/article/caract_principales/dimensions")." : H".$result['hauteur']."xL".$result['largeur']."xP".$result['profondeur']." mm </p></div>";
  break;
  case '25'://meuble
    $result = $DB_site->query_first("SELECT couleur_fab as couleur, hauteur, largeur, profondeur,type_accessoire FROM article_meuble WHERE artid=".$artid);
    $template->parts['caractsprinc'].="<div><p>".$template->translate("/article/caract_principales/type")." : ".$template->translate($result['type_accessoire'])."</p></div>";
    $template->parts['caractsprinc'].="<div><p>".$template->translate("/article/caract_principales/coloris")." : ".$template->translate($result['couleur'])."</p></div>";
    $template->parts['caractsprinc'].="<div><p>".$template->translate("/article/caract_principales/dimensions")." : H".$result['hauteur']."xL".$result['largeur']."xP".$result['profondeur']." mm </p></div>";
  break;
  case '34'://pieces detachés
    $result = $DB_site->query_first("SELECT couleur, hauteur, largeur, profondeur,type_accessoire FROM article_piecedetache WHERE artid=".$artid);
    $template->parts['caractsprinc'].="<div><p>".$template->translate("/article/caract_principales/type")." : ".$template->translate($result['type_accessoire'])."</p></div>";
    $template->parts['caractsprinc'].="<div><p>".$template->translate("/article/caract_principales/coloris")." : ".$template->translate($result['couleur'])."</p></div>";
    $template->parts['caractsprinc'].="<div><p>".$template->translate("/article/caract_principales/dimensions")." : H".$result['hauteur']."xL".$result['largeur']."xP".$result['profondeur']." mm </p></div>";
  break;
  case '27'://divers
    $result = $DB_site->query_first("SELECT couleur_fab as couleur, hauteur, largeur, profondeur,type_accessoire FROM article_divers WHERE artid=".$artid);
    $template->parts['caractsprinc'].="<div><p>".$template->translate("/article/caract_principales/type")." : ".$template->translate($result['type_accessoire'])."</p></div>";
    $template->parts['caractsprinc'].="<div><p>".$template->translate("/article/caract_principales/coloris")." : ".$template->translate($result['couleur'])."</p></div>";
    $template->parts['caractsprinc'].="<div><p>".$template->translate("/article/caract_principales/dimensions")." : H".$result['hauteur']."xL".$result['largeur']."xP".$result['profondeur']." mm </p></div>";
  break;
  case '4'://four seul
    $result = $DB_site->query_first("SELECT couleur_fab as couleur, hauteur, largeur, profondeur,type_four,nettoyage,techno,class_energetique FROM article_four WHERE artid=".$artid);
    $template->parts['caractsprinc'].="<div><p>".$template->translate("/article/caract_principales/type_four")." : ".$template->translate($result['type_four'])."</p></div>";
    $template->parts['caractsprinc'].="<div><p>".$template->translate("/article/caract_principales/techno_four")." : ".$template->translate($result['techno'])."</p></div>";
    $template->parts['caractsprinc'].="<div><p>".$template->translate("/article/caract_principales/nettoyage_four")." : ".$template->translate($result['nettoyage'])."</p></div>";
    $template->parts['caractsprinc'].="<div><p>".$template->translate("/article/caract_principales/coloris")." : ".$template->translate($result['couleur'])."</p></div>";
    $template->parts['caractsprinc'].="<div><p>".$template->translate("/article/caract_principales/dimensions")." : H".$result['hauteur']."xL".$result['largeur']."xP".$result['profondeur']." mm </p></div>";
    $class_energetique = $result[class_energetique];
  break;
  case '17'://miro onde
    $result = $DB_site->query_first("SELECT couleur, hauteur, largeur, profondeur,type_four,nettoyage, class_energetique FROM article_four WHERE artid=".$artid);
    $template->parts['caractsprinc'].="<div><p>".$template->translate("/article/caract_principales/type_four")." : ".$template->translate($result['type_four'])."</p></div>";
    $template->parts['caractsprinc'].="<div><p>".$template->translate("/article/caract_principales/nettoyage_four")." : ".$template->translate($result['nettoyage'])."</p></div>";
    $template->parts['caractsprinc'].="<div><p>".$template->translate("/article/caract_principales/coloris")." : ".$template->translate($result['couleur'])."</p></div>";
    $template->parts['caractsprinc'].="<div><p>".$template->translate("/article/caract_principales/dimensions")." : H".$result['hauteur']."xL".$result['largeur']."xP".$result['profondeur']." mm </p></div>";
    $class_energetique = $result[class_energetique];
  break;
  case '29'://tiroir chauffant
    $result = $DB_site->query_first("SELECT couleur_fab as couleur, hauteur, largeur, profondeur, class_energetique FROM article_tiroir WHERE artid=".$artid);
    $template->parts['caractsprinc'].="<div><p>".$template->translate("/article/caract_principales/coloris")." : ".$template->translate($result['couleur'])."</p></div>";
    $template->parts['caractsprinc'].="<div><p>".$template->translate("/article/caract_principales/dimensions")." : H".$result['hauteur']."xL".$result['largeur']."xP".$result['profondeur']." mm </p></div>";
    $class_energetique = $result[class_energetique];
  break;
  case '30'://rotissoire
    $result = $DB_site->query_first("SELECT couleur_fab as couleur, hauteur, largeur, profondeur,type_foyer,nb_foyer,class_energetique FROM article_rotissoire WHERE artid=".$artid);
    $template->parts['caractsprinc'].="<div><p>".$template->translate("/article/caract_principales/type_foyer")." : ".$template->translate($result['type_foyer'])." </p></div>";
    $template->parts['caractsprinc'].="<div><p>".$template->translate("/article/caract_principales/foyers")." : ".$result['nb_foyer']." </p></div>";
    $template->parts['caractsprinc'].="<div><p>".$template->translate("/article/caract_principales/coloris")." : ".$template->translate($result['couleur'])."</p></div>";
    $template->parts['caractsprinc'].="<div><p>".$template->translate("/article/caract_principales/dimensions")." : H".$result['hauteur']."xL".$result['largeur']."xP".$result['profondeur']." mm </p></div>";
    $class_energetique = $result[class_energetique];
  break;
  case '5'://hotte
    $result = $DB_site->query_first("SELECT couleur_fab as couleur, hauteur, largeur, profondeur,type_hotte,type_aspiration,debit_air_max,nb_vitesses,class_energetique FROM article_hotte WHERE artid=".$artid);
    $template->parts['caractsprinc'].="<div><p>".$template->translate("/article/caract_principales/type_hotte")." : ".$template->translate($result['type_hotte'])." </p></div>";
    $template->parts['caractsprinc'].="<div><p>".$template->translate("/article/caract_principales/type_aspiration")." : ".$template->translate($result['type_aspiration'])." </p></div>";
    $template->parts['caractsprinc'].="<div><p>".$template->translate("/article/caract_principales/debit_air_max")." : ".$result['debit_air_max']." </p></div>";
    $template->parts['caractsprinc'].="<div><p>".$template->translate("/article/caract_principales/nb_vitesses")." : ".$template->translate($result['nb_vitesses'])." </p></div>";
    $template->parts['caractsprinc'].="<div><p>".$template->translate("/article/caract_principales/coloris")." : ".$template->translate($result['couleur'])."</p></div>";
    $template->parts['caractsprinc'].="<div><p>".$template->translate("/article/caract_principales/dimensions")." : H".$result['hauteur']."xL".$result['largeur']."xP".$result['profondeur']." mm </p></div>";
    $class_energetique = $result[class_energetique];
  break;

  case '23'://lave vaiselle
    $result = $DB_site->query_first("SELECT couleur_fab as couleur, hauteur, largeur, profondeur,couverts, class_energetique FROM article_lavevaisselle WHERE artid=".$artid);
    $template->parts['caractsprinc'].="<div><p>".$template->translate("/article/caract_principales/couverts")." : ".$result['couverts']." </p></div>";
    $template->parts['caractsprinc'].="<div><p>".$template->translate("/article/caract_principales/coloris")." : ".$template->translate($result['couleur'])."</p></div>";
    $template->parts['caractsprinc'].="<div><p>".$template->translate("/article/caract_principales/dimensions")." : H".$result['hauteur']."xL".$result['largeur']."xP".$result['profondeur']." mm </p></div>";
    $class_energetique = $result[class_energetique];
  break;
  case '36'://lave seche
    $result = $DB_site->query_first("SELECT couleur_fab as couleur, hauteur, largeur, profondeur,type_lavelinge,capacite_lavage, class_energetique FROM article_laveseche WHERE artid=".$artid);
    $template->parts['caractsprinc'].="<div><p>".$template->translate("/article/caract_principales/type_lavelinge")." : ".$template->translate($result['type_lavelinge'])."</p></div>";
    $template->parts['caractsprinc'].="<div><p>".$template->translate("/article/caract_principales/capacite_lavage")." : ".$result['capacite_lavage']." </p></div>";
    $template->parts['caractsprinc'].="<div><p>".$template->translate("/article/caract_principales/coloris")." : ".$template->translate($result['couleur'])."</p></div>";
    $template->parts['caractsprinc'].="<div><p>".$template->translate("/article/caract_principales/dimensions")." : H".$result['hauteur']."xL".$result['largeur']."xP".$result['profondeur']." mm </p></div>";
    $class_energetique = $result[class_energetique];
  break;
  case '33'://lave evier
    $result = $DB_site->query_first("SELECT couleur_fab as couleur, hauteur_evier as hauteur, largeur_evier as largeur, profondeur_evier as profondeur,type_accessoire as type FROM article_evier WHERE artid=".$artid);
    $template->parts['caractsprinc'].="<div><p>".$template->translate("/article/caract_principales/type")." : ".$template->translate($result['type'])."</p></div>";
    $template->parts['caractsprinc'].="<div><p>".$template->translate("/article/caract_principales/coloris")." : ".$template->translate($result['couleur'])."</p></div>";
    $template->parts['caractsprinc'].="<div><p>".$template->translate("/article/caract_principales/dimensions")." : H".$result['hauteur']."xL".$result['largeur']."xP".$result['profondeur']." mm </p></div>";
  break;
  case '37'://prestation service
    $result = $DB_site->query_first("SELECT type_accessoire FROM article_prestation WHERE artid=".$artid);
    $template->parts['caractsprinc'].="<div><p>".$template->translate("/article/caract_principales/type")." : ".$template->translate($result['type_accessoire'])."</p></div>";
  break;

}

if (isset($user_info['paysid'])) {
  $idPays=$user_info['paysid'];
} else {
  $idPays=$template->pays_ref();
}

$tabGaranties=DAOgarantie::loadMaxGarantieArtByPays($DB_site,$template->site_code(),'FR',$idPays,$article['artid'],!$sitepro ? 1 : 2);
$garantie=$tabGaranties[0];
$dureeGarantie=0;
if (!empty($garantie)) {
  //si la garantie est un extension
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
  if ($duree!=99) {
    $an=$template->translate("/article/garantie_type/an");
    if ($duree>1) {
      $an=$template->translate("/article/garantie_type/ans");
    }
    $libGarantie=$duree." ".$an;
  } else {
    $libGarantie=$template->translate("/article/garantie_type/a_vie",'F');
  }
  $nomGarantie=$template->translate('/article/garantie_type/garantie','F');
  $typeGarantie=$template->translate("/article/garantie_type/$garantie->type_garantie");
  $garantie_incluse = <<<HTML
      <div class="row">
        <div class="col-md-6 col-sm-6 col-xs-12" ><strong>$nomGarantie&nbsp;:</strong></div>
        <div class="col-md-6 col-sm-6 col-xs-12" >$libGarantie $typeGarantie</div>
      </div>
HTML;

  $template->evaltemplate("InfoGarantieBtn","infogarantie");
}
  
//************************//
// DEBUT CARACTERISTIQUES //
//************************//
$template->parts["input_options"] ="";

$oArticle = new produit($DB_site, $template->langue, $template->site_code(), $template->tva_ref(), $artid);
$simulateur = $oArticle->simulateur;

//Pour photos devis simulateur, on créé les cookies avec le caractid de base et on creer les div calques.
if($simulateur) {
  $i = 1;
  $tabCalque = array();
  $template->parts['imgcalques']="";
  $pathsim = "configurations/".$host."/images/simulateur/";
  $req = $DB_site->query("SELECT caractid FROM article_simu_caract WHERE artid='".$oArticle->id."' AND affiche = 1 ORDER BY ordre");
  while ($row = $DB_site->fetch_array($req)) {
      setcookie('calque'.$i, $row['caractid'], time() + 24*60*60);
      $template->parts['imgcalques'] .="<img class=\"calque$i\" src=\"".$pathsim.$oArticle->id."_".$row['caractid'].".png\">";
      $tabCalque[$row['caractid']] = $i;
      $i++;
  }
}else{
  //on supprime les cookie des calques si ce n'est pas ou plus un article simulateur et qu'il sont toujours presents
  foreach ($_COOKIE as $key => $value) {
        $calque = strpos($key, "calque");
        if (is_numeric($calque)){
          setcookie($key,"off",time());
        }
   }

}
$tabCaracteristiques = $oArticle->getCaracteristiquesPrincipales($artid);
$caract_num = 1;

$ralcolor=new ral($DB_site, $artid);
$ralcolor->langue=$template->langue;
$ralcolor->select_function="article_ral_select";


//Boucle sur les caractéristiques mères
$hasoption=false;

if(!empty($tabCaracteristiques)){
  $hasoption = true;
  $voir=$masquer="";

  if(!$template->mobile){
    $voir=$template->translate("/article/options/voir","F");
    $masquer=$template->translate("/article/options/masquer","F");
  }
    
  $template->parts['liste_options_preview'] = ""
  . "<div class=\"encart_preview_opts\">"
  . "<p class=\"titre_preview\">".$template->translate("/article/options/les_options","F")." :</p>";

  foreach($tabCaracteristiques as $la_caracteristique){
    //Si c'est une teinte RAL, on n'affiche pas la liste des options "classique"
    if(!$ralcolor->is_ral_carac($la_caracteristique['caractid'])){
      $template->parts['article_options_new'] .= ""
        . "<div class=\"opt_ligne".($caract_num%2 == 0 ? '2' : '1')."\">"
          . "<div class=\"libelle_option\">"
            . "<p class=\"intitule\"><span>".$caract_num."</span> ".$la_caracteristique['libelle'.$template->langue]."</p>"
            . "<p id=\"txt_options\" data-open=\"".$voir."\" data-close=\"".$masquer."\">".($caract_num <= 1 && !$reconstruction ? $masquer : $voir)." <i class=\"fa fa-chevron-".($caract_num <= 1 && !$reconstruction ? 'up' : 'down')." fa-lg\"></i></p>"
          . "</div>"
        . "<div class=\"centre_options\" ".($caract_num <= 1 && !$reconstruction ? 'style=\"display:block;\"' : '').">";

      $template->parts['liste_options_preview'] .= ""
        . "<div class=\"optpreview\">"
          . "<div class=\"opttitle\">".$la_caracteristique['libelle'.$template->langue]." :</div>"
          . "<div class=\"optimg\" id=\"previewopt".$la_caracteristique['caractid']."\"><span style=\"color:#df4126;\">".$template->translate("/article/options/definir","F")."</span></div>"
          . "<div class=\"clear\"></div>"
        . "</div>";
      $template->parts["input_options"] .="<input type=\"hidden\" id=\"checked_opt_".$la_caracteristique['caractid']."\" value=\"\" > ";
    }

    //Récupération des différentes options de la caractéristique mère
    $tabOptions = array();
    if(!$ralcolor->is_ral_carac($la_caracteristique['caractid'])){
      $tabOptions = $oArticle->getOptions($artid, $la_caracteristique['caractid']); 
    }
    $taille_reduite = false;
    /*if(count($tabOptions) > 10){
      $taille_reduite = true;
    }*/

    if(!empty($tabOptions)){
      foreach($tabOptions as $l_option){ 

        $affiche_visuel = false;
        $affiche_zoom = false;
        $preview_action = ''; 

        ///si un prix fixe est sur une option, on va donc chercher son prix fixe dans caracteristiquevaleur, sinon on prend son prix individuel dans article_caractval.
        $lot_prix=array();
        $caractvaltotal=array();
        $req_lot=$DB_site->query("SELECT artid,qte FROM article_lot WHERE lotid=".$artid);
        while($art_lot=$DB_site->fetch_array($req_lot)){
          $lot_prix[]=array($art_lot['artid'],$art_lot['qte']);
        }
        //Si lot, on additionne les prix
        if($lot_prix!=array()){
          foreach($lot_prix as $articleid => $art_val){
            $art=$DB_site->query_first("SELECT differenceprixht, differenceprix, cv.prix_fixe, cv.pvht, cv.pvttc FROM article_caractval INNER JOIN caracteristiquevaleur as cv USING(caractvalid)WHERE caractvalid=".$l_option['caractvalid']." AND artid=".intval($art_val[0]));
            if($art!=""){
              if($art['prix_fixe'] == '1'){
                $caractvaltotal["prixlot"]=$caractvaltotal["prixlot"] + $art['pv'.($sitepro ? 'ht' : 'ttc')]*$art_val[1];
              }
              else{
                $diffprix = $art["differenceprix".($sitepro ? 'ht' : '')];
                $caractvaltotal["prixlot"]=$caractvaltotal["prixlot"] + $diffprix['base']*$art_val[1];
              }
            }
          }
        }
        if(isset($caractvaltotal["prixlot"])){
          $l_option['differenceprix']=round($caractvaltotal["prixlot"],4);
        }
        else{
          if($l_option['prix_fixe'] == '1'){
            $l_option['differenceprix'] = $l_option['pv'.($sitepro ? 'ht' : 'ttc')];
          }
          else{
            $aDiffPrix = $l_option['differenceprix'.($sitepro ? 'ht' : '')];
            $l_option['differenceprix'] = $aDiffPrix;
          }
        }

        if(file_exists($_SERVER['DOCUMENT_ROOT']."/configurations/".$host."/images/caracteristiquevaleur/".$l_option['caractvalid'].".jpg")){ 
          $affiche_visuel = true;

          //Récupération de l'image de zoom
          if($l_option['articlephotoid'] > 0){
            $affiche_zoom = true;
            $image_zoom = "/images/".$article['artid']."_".$l_option['articlephotoid']."_300_300.jpg";
            $preview_action = "document.getElementById('big').src = '/images/".$artid."_".$l_option['articlephotoid']."_280_280.jpg';";
          } elseif(file_exists($_SERVER['DOCUMENT_ROOT']."/configurations/".$host."/images/caracteristiquevaleur/zoom/".$l_option['caractvalid'].".jpg")){ 
            $affiche_zoom = true;
            $image_zoom = "/images/caracteristiquevaleur/zoom/".$l_option['caractvalid'].".jpg";
          }
        } 

        //ici on gère le chagement d'affichage pour les options simulateur, et on vérifie si des photos existent par option
        if($simulateur){
          $calque = $tabCalque[$l_option['caractid']];
          if(file_exists($_SERVER['DOCUMENT_ROOT']."/".$pathsim.$article['artid']."_".$l_option['caractid']."_".$l_option['caractvalid'].".png"))
            $checkOption = "checkOptionSimu(".$article['artid'].",".$l_option['caractid'].",".$l_option['caractvalid'].",".$calque.",".time().");";
          else
            $checkOption = "";
        }else{
          $checkOption ="";
        }

        //DEBUT ENCART OPTION
        $template->parts['article_options_new'] .= ""
          . "<a name=\"choix_options".$caract_num."\"></a>"
          . "<input type=\"hidden\" name=\"incomp_opt_".$l_option['caractvalid']."\" class=\"input_hidden_option\" id=\"incomp_opt".$l_option['caractvalid']."\" value=\"\">"
          . "<div class=\"encart_option\" ".($taille_reduite ? 'style=\"width: 50px\"' : '')." id=\"option".$l_option['caractvalid']."\" onclick=\"".($ralcolor->is_ral_option($l_option['caractvalid']) ? "ral_show('ral_panel_".$l_option['caractvalid']."',true,this);" : "checkOption(this, ".$article['artid'].", ".$l_option['caractid'].", ".$l_option['caractvalid'].", ".$caract_num.");".$checkOption." ".$preview_action)."\">";

        if(!$taille_reduite){
          $template->parts['article_options_new'] .= "<div class=\"title\">".$l_option['libelle'.$template->langue]."</div>";
        }

        if($affiche_visuel){ 
          $template->parts['article_options_new'] .= ""
            . "<img src=\"/images/caracteristiquevaleur/".$l_option['caractvalid'].".jpg\" class=\"visuel_option\" alt=\"".$l_option['description'.$template->langue]."\">";
        }
        
        $template->parts['article_options_new'] .= "<div class=\"check\">
        <input type=\"radio\" name=\"lst_".$la_caracteristique['caractid']."\" id=\"lst".($ralcolor->is_ral_option($l_option['caractvalid']) ? '_ral' : '')."_".$la_caracteristique['caractid']."_".$l_option['caractvalid']."\" value=\"".$l_option['caractvalid']."\" style=\"vertical-align: -2px; margin: 0 5px 0 0;\">";
        if($article['commandable']>0){
            $template->parts['article_options_new'] .="<span class=\"prix\">".($l_option['differenceprix'] != 0 ? $template->price_format($l_option['differenceprix']) : $template->translate("/commande/offert","F"))."</span>";
        }

                   
        $template->parts['article_options_new'] .= "</div></div>";
        
        //DEBUT POPUP RAL
        if($ralcolor->is_ral_option($l_option['caractvalid'])){
          $ralcolor->caractid=$la_caracteristique['caractid'];

          $template->parts['article_options_new'] .= "<div class=\"ral_panel\" id=\"ral_panel_".$l_option['caractvalid']."\">".$ralcolor->generate($la_caracteristique['caractid'],$l_option['caractvalid'])."</div>";
        }
        //FIN POPUP RAL

        //FIN ENCART OPTION

        //DEBUT POPUP ZOOM (Titre + Descriptif + Photo)
        if($affiche_zoom & !empty($l_option['desc_caract_val'])){
          $template->parts['article_options_new'] .= ""
            . "<div class=\"visuel_zoom\">"
              . "<p id=\"info_disabled_".$l_option['caractvalid']."\" class=\"info_disabled\">Incompatible avec l'option<br><span id=\"lst_opt_dis_".$l_option['caractvalid']."\"></span></p>"
                . "<span class=\"titre\">".$l_option['libelle'.$template->langue]."</span>";
                
            if($l_option['description'.$template->langue]!="")
                    $template->parts['article_options_new'] .="<span class=\"description\">".$l_option['description'.$template->langue]."</span>";

          if(!empty($l_option['desc_caract_val'])){
            $template->parts['article_options_new'] .= "<p ".(!$affiche_zoom ? 'style=\"margin-bottom: 0;\"' : '').">".$l_option['desc_caract_val']."</p>";
          }

          if($affiche_zoom){
            $template->parts['article_options_new'] .= "<img src=\"".$image_zoom."\" alt=\"".$l_option['description'.$template->langue]."\">";
          }

          $template->parts['article_options_new'] .= "</div>";

        }
        //FIN POPUP ZOOM
      }

      $template->parts['article_options_new'] .= "</div></div>";

      $caract_num++;
    }
  }
  $template->parts['liste_options_preview'] .= "</div>";
}
//**********************//
// FIN CARACTERISTIQUES //
//**********************//

$sql = "SELECT *";
$sql .= " FROM article_site";
$sql .= " WHERE artid = '".$artid."'";
$archive = $DB_site->query_first($sql);

if($hasoption && ($archive['active'] != 3 || $archive['active'] != 5)){
    if($article['commandable']!=1) $template->parts['hide_options']="hidden";
    $template->evaltemplate("ArticleOptionPreviewNew","optionspreviewnew");
    $template->evaltemplate("ArticleOptionNew","optionnew");

    //$template->evaltemplate("ArticleOption","options");
} 

//BLOC INSTALLATEUR
if($article['montage']==1) $template->evaltemplate("ArticleInstallateur","article_installateur");

if($article['commandable']==0 || $article['commandable']==2 || $art_actif[0]==3 || $art_actif[0]==5 || $article['date_appro']>time()){
    $art_remp=$DB_site->query_first("SELECT remp_artid FROM article_remplacement ar INNER JOIN article_site a_s ON(a_s.artid=ar.remp_artid) WHERE ar.artid=".$article['artid']." AND a_s.site_code='".$template->site_code()."' AND a_s.active=1 ORDER BY position LIMIT 1");
    if($art_remp!=""){
  vignette($art_remp['remp_artid'],"categorie");
  $template->evaltemplate("ArticleRemplacement","article_remp");
    }
}

//***********************************//
// DEBUT GESTION DELAIS DE LIVRAISON //
//***********************************//
$d = delailivraison($article);
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
    $template->parts["stock_libelle"] = $template->translate("/article/stock/reappro_le")." ".date("d/m/Y",$d['date_appro']);
    $template->parts["datelivraison"] = date("d/m/Y",($jourlivraison));
}

//*********************************//
// FIN GESTION DELAIS DE LIVRAISON //
//*********************************//

$requete=$DB_site->query("SELECT aa.libelle,aa.qte FROM article_accessoire aa WHERE aa.artid=".$article['artid']);
if($DB_site->num_rows($requete)>0){

  $acclist="";
  while($acc=$DB_site->fetch_array($requete)){
    if($acc['qte']==0) $acc['qte']="";
    $acclist.="<br>".trim($acc['qte'])." ".$template->translate($acc['libelle']);
  }
  $template->parts["acclist"]="<li><b>".$template->translate("/article/champs/accessoires_inclus","F")." : </b>
  $acclist
  </li>";
    
    $article['acclist']="<div class=\"%class%\">
                           <div class=\"col-md-6 col-sm-6 col-xs-12\" ><b>".$template->translate("/article/champs/accessoires_inclus","F")."&nbsp;: </b></div>
                           <div class=\"col-md-6 col-sm-6 col-xs-12\">".substr($acclist,4)."</div>
                       </div>";
}

//liste rangements T_article_rangement
$liste_rangements = "";
$rangements = $DB_site->query("SELECT * FROM article_rangement WHERE artid = '$article[artid]'");
if ($DB_site->num_rows($rangements) > 0){
    while ($rangement=$DB_site->fetch_array($rangements)){
        $librangement = $DB_site->query_first("SELECT libelle".$template->langue." AS libelle FROM article WHERE artid = '$rangement[artid_rangement]'");
        $liste_rangements .= "$rangement[nombre] $librangement[libelle]<br>";
    }
}else
    $liste_rangements = "Aucun rangement spécifié";



$criteocat=$DB_site->query_first("SELECT * FROM display_categorie WHERE display='criteo' AND catid=".$article['catid']);
if($criteocat['catid']!="" && $article['commandable']=="1"){
$template->parts["criteo"]=<<<JS
      <script type="text/javascript" src="//static.criteo.net/js/ld/ld.js" async="true"></script>
      <script type="text/javascript">
      window.criteo_q = window.criteo_q || [];
      window.criteo_q.push(
      { event: "setAccount", account: 2995 },
      { event: "setEmail", email: ["$user_info[mail]"] },
      { event: "setSiteType", type: "d" },
      { event: "viewItem", item: "$artid"}
      );
      </script>
JS;
}

//$nextpcat=$DB_site->query_first("SELECT * FROM display_categorie WHERE display='nextperformance' AND catid=".$article['catid']);
if($nextpcat['catid']!="" && $article['commandable']=="1"){
  $template->parts["criteo"].="<script src=\"http://nxtck.com/act.php?tag=36947;pid=$artid\"></script>";
}

if(ESPACE == 'prod'){
    //Mise en place des tags Nextperformance
    $template->parts["nextperformance"] = "<script type=\"text/javascript\" src=\"https://nxtck.com/act.php?tag=43956&pid=".$artid."\" ></script>";
}

// Utile pour le template "ArticlePage"
$article[$description] = stripslashes($article[$description]);
$article[$description2] = stripslashes($article[$description2]);
if ($article[poid] > 0){
    if ($article[poid] > 1000){
        $article[poid] = $article[poid] / 1000 ;
        $poidsunit="kg";
    }else{
        $poidsunit="g";
    }
}

$nbcrit=0;

//debut donnees tech
// Construction des données techniques
//$chkdescription = array();
$onglets = $DB_site->query_first('
  SELECT `bdd_table`
  FROM `typearticle`
  WHERE `typearticleid` = '. $article['typearticleid']);
$tables = explode(',', $onglets[0]);

if($tables[0] != ''){
  show_specs($tables[0],$artid,'artid');
  if($tables[0] == "article_cuisiniere"){
    $match = array('article_table_cuisson','article_four');
    foreach($match as $type){
      $test = checkit($artid,$type);
      if(is_array($test) && $test[0] == true){
        $i=0;
        foreach($test[2] as $id){
          show_specs($type,$id,'id',$i);
          if($type == "article_table_cuisson"){
            $foyers = checkit($id,'article_foyers','table_id');
            if(is_array($foyers) && $foyers[0] == true){
              $f=0;
              $template->parts['techlist'].="<div class=\"col-xs-12\">";
              foreach($foyers[2] as $id){
                show_specs_foyers('article_foyers',$id,'id',$f);
                $f++;
              }
              $template->parts['techlist'].="</div>";
            }
          }
          $i++;
        }      
      }
    }
  }elseif($tables[0]=='article_piano'){
    $match = array('article_table_cuisson','article_four');
    foreach($match as $type){
      $test = checkit($artid,$type);
      if(is_array($test) && $test[0] == true){
        $i=0;
        foreach($test[2] as $id){
          show_specs($type,$id,'id',$i);
          if($type == "article_table_cuisson"){
            $foyers = checkit($id,'article_foyers','table_id');
            if(is_array($foyers) && $foyers[0] == true){
              $f=0;
              $template->parts['techlist'].="<div class=\"col-xs-12\">";
              foreach($foyers[2] as $id){
                show_specs_foyers('article_foyers',$id,'id',$f);
                $f++;
              }
              $template->parts['techlist'].="</div>";
            }
          }
          $i++;
        }      
      }
    }    
  }elseif($tables[0]=='article_froid'){
    $match = array('article_congel_dependant','article_frigo_dependant');
    foreach($match as $type){
      $test = checkit($artid,$type);
      if(is_array($test) && $test[0] == true){
        $i=0;
        foreach($test[2] as $id){
          show_specs($type,$id,'id',$i);
          $i++;
        }      
      }
    }    
  }elseif($tables[0]=='article_table_cuisson'){
    $test = checkit($artid,'article_table_cuisson');
    if(is_array($test) && $test[0] == true){
        $i=0;
        foreach($test[2] as $id){
          $foyers = checkit($id,'article_foyers','table_id');
          if(is_array($foyers) && $foyers[0] == true){
            $f=0;
            $template->parts['techlist'].="<div class=\"col-xs-12\">";
            foreach($foyers[2] as $id){
              show_specs_foyers('article_foyers',$id,'id',$f);
              $f++;
            }
            $template->parts['techlist'].="</div>";
          }
          $i++;
        }      
      }
  }
}



//******************************//
// DEBUT ACCESSOIRES CONSEILLES //
//******************************//
$i=0;
$art_rangements=$DB_site->query("SELECT * FROM article a INNER JOIN article_rangement ac ON(a.artid = ac.artid_rangement) INNER JOIN article_site a_s ON(a.artid=a_s.artid) WHERE ac.artid = '$artid' AND a_s.site_code='".$template->site_code()."' AND a_s.active = '1' GROUP BY a.artid ORDER BY a.artcode");
if($DB_site->num_rows($art_rangements) > 0){
    while ($art_rangement=$DB_site->fetch_array($art_rangements)){
        $template->parts['class_sup'] = $i == 0 ? 'premier_acc vignette_acc' : 'vignette_acc';
        vignette($art_rangement['artid'],'accessoires');
        $i++;
    }
    $template->parts["nb_accessoires"]=$i;
    $template->evaltemplate("OngletArtCons","ongletartcons");
}

vignscroll("accessoires",$i);

//****************************//
// FIN ACCESSOIRES CONSEILLES //        
//****************************//


//accessoires inclus//

$i=0;
$requete=$DB_site->query("SELECT aa.id,aa.qte,aa.libelle FROM article_accessoire aa WHERE aa.artid=".$artid);
if($DB_site->num_rows($requete)>0){
    while($acc=$DB_site->fetch_array($requete)){
        if($i>0) $template->parts["artincl"].=", ";
        $template->parts["artincl"].= $template->translate($acc["libelle"])." x ".$acc[qte];
        $i++;
    }

    $template->parts["nb_accessincl"]=$i;
    $template->evaltemplate("OngletArtIncl","ongletartincl");
}    




//fin accessoires inclus//

// articles liés (inverse rangements)
$sep="";
$i=0;
$artcodelist=$artcodelistmobile=array();
$refs=array();
$art_rangements=$DB_site->query("SELECT a.artcode,a.artcode_eurocave,a.active,a.artid,a.catid,a.libelleFR,a.libelleEN,m.marqueid,(SELECT COUNT(id) FROM article_lot WHERE lotid=a.artid) AS lot,m.libelle AS marque FROM cross_selling cs, article a,article_marque am,marque m WHERE a.artid = cs.artid_cross AND a.artid=am.artid AND m.marqueid=am.marqueid AND cs.artid= '$artid'");
if ($DB_site->num_rows($art_rangements) > 0){
    while ($art_rangement=$DB_site->fetch_array($art_rangements)){
  if($art_rangement['active']==1){
      $artcodelist[]="<div class=\"artcompat\">
        <a href=\"".$template->mkurl("article",$art_rangement['artid'],$art_rangement)."\">
    <img src=\"/images/".$art_rangement['artid']."_50_50.jpg\" alt=\"".$art_rangement['libelle'.$template->langue]."\" width=\"50\" height=\"50\">
    <br>".$art_rangement['artcode'].
        "</a>
      </div>";
      $artcodelistmobile[]=$art_rangement['artcode'];
  }
  $active=intval($art_rangement['active']);
  if($active==0) $active=1;
  if(intval($art_rangement['lot'])==0 && $art_rangement['catid']!=212) $refs[$art_rangement['marqueid']][$active][]=$art_rangement['artcode_eurocave'];
    }
    
    if(count($artcodelist)>0) $template->parts["compatlist"]="<p class=\"compattitle\">".$template->translate("/article/champs/compatible_modele_suivants","F")."</p>"
  .(!$template->mobile?implode("",$artcodelist):implode(" - ",$artcodelistmobile));
        
    $template->evaltemplate("OngletCompat","ongletcompatible");
}

//cross selling complementaire

$sep="";
$i=0;
$artcodelist=$artcodelistmobile=array();
$refs=array();
$art_rangements=$DB_site->query("SELECT a.artcode,a.artcode_eurocave,a.active,a.artid,a.catid,a.libelleFR,a.libelleEN,m.marqueid,(SELECT COUNT(id) FROM article_lot WHERE lotid=a.artid) AS lot,m.libelle AS marque FROM cross_selling2 cs, article a,article_marque am,marque m WHERE a.artid = cs.artid_cross AND a.artid=am.artid AND m.marqueid=am.marqueid AND cs.artid= '$artid'");
if ($DB_site->num_rows($art_rangements) > 0){
    while ($art_rangement=$DB_site->fetch_array($art_rangements)){
  if($art_rangement['active']==1){
      $artcodelist[]="<div class=\"artcompat\">
        <a href=\"".$template->mkurl("article",$art_rangement['artid'],$art_rangement)."\">
    <img src=\"/images/".$art_rangement['artid']."_50_50.jpg\" alt=\"".$art_rangement['libelle'.$template->langue]."\" width=\"50\" height=\"50\">
    <br>".$art_rangement['artcode'].
        "</a>
      </div>";
      $artcodelistmobile[]=$art_rangement['artcode'];
  }
  $active=intval($art_rangement['active']);
  if($active==0) $active=1;
  if(intval($art_rangement['lot'])==0 && $art_rangement['catid']!=212) $refs[$art_rangement['marqueid']][$active][]=$art_rangement['artcode_eurocave'];
    }
    
    if(count($artcodelist)>0) $template->parts["compatlist2"]="<p class=\"compattitle\">".$template->translate("/article/champs/compatible_modele_suivants","F")."</p>"
  .(!$template->mobile?implode("",$artcodelist):implode(" - ",$artcodelistmobile));
        
    $template->evaltemplate("OngletCompat2","ongletcompatible2");
}

//******************//
// DEBUT MEME GAMME //
//******************//
$sep="";
$i=0;
$arts_sim=$DB_site->query("SELECT arts.artid FROM article_similaire arts INNER JOIN article a USING(artid) INNER JOIN article_site a_s ON(a.artid=a_s.artid) WHERE gid=(select gid from article_similaire where artid = '$artid' LIMIT 1) AND arts.artid<>'$artid' AND a_s.site_code='".$template->site_code()."' AND a_s.active='1' GROUP BY a.artid ORDER BY a.artcode");
if($DB_site->num_rows($arts_sim) > 0){
    while ($art_sim=$DB_site->fetch_array($arts_sim)){
        //$template->parts['class_sup'] = $i == 0 ? 'premier_acc vignette_acc' : 'vignette_acc';
        vignette($art_sim['artid'],'similaire');
        $i++;
    }
    $template->evaltemplate("OngletSimilaire","ongletsimilaire");
    $template->parts["nb_similaire"]=$i;
    vignscroll("similaire",$i);
}
//****************//
// FIN MEME GAMME //
//****************//


//************//
// DEBUT PACK //
//************//
$sep="";
$j=0;
$olddispoid=0;

$packs=new Pack($DB_site);

if($sitepro)
  $kdopid = $packs->kdos_pack($artid,$sitepro);
else
  $kdopid = $packs->kdos_pack($artid);


if($sitepro)
  $listpack=$packs->contains($artid, $sitepro);
else
  $listpack=$packs->contains($artid);

if (count($listpack)>0 && $article['typearticleid']==1 && $template->site_code()=="base"){
  $nbPack=0;
  //$nbPack=$DB_site->query_first("SELECT COUNT(*) AS total FROM article_pack WHERE artid=".$artid.";")['total'];
    
  foreach($listpack as $packid){
    $template->parts["cadeaupack"]="";
    $pack=$packs->getDetails($packid);
    if($pack['active']==1 && $packs->active($packid) && $packid!=$kdopid) {
      $artinpack=$packs->getProduits($packid);
      //$pack_prices=article_prices($packid,$template->tva_ref(),$template->site_code(),$sitepro);
      $nbArtPack=count($artinpack);
      
      foreach($artinpack as $artinpackartid=>$art_pack){
        if($artinpackartid==$artid){
          $firstart=$art_pack;
          unset($artinpack[$artinpackartid]);
          break;
        }
      }
      
      if(!is_null($firstart)) array_unshift($artinpack,$firstart);
    
      $j++;
      $template->parts["addbasket"]="";
      $template->parts["prices"]="";
      $template->parts["pc"]="";
      $template->parts["nomPack"]="";
      $template->parts["libPack"]="";
      $strikeout="<div class=\"estrikeout\"></div>";
      $template->parts["packid"]=$art_pack['packid'];

      $lekdo="";
      $i=0;
      
      foreach($artinpack as $art_pack){
        $i++;
        $typeoffre=explode('-', $art_pack['offre'])[0];

        if ($typeoffre=='pdtoffert' && $art_pack['prixpack']==0) {
          $template->parts["pdtoffertpack"]="<img src=\"../images/templates/".$template->langue."/offert.png\"/>";
          $prices=article_prices($art_pack['artid'],$template->tva_ref(),$template->site_code(),$sitepro);
          $template->parts["prixpdtoffert"]=$template->translate("/article/autres_infos/soit",'F')." ".$template->price_format($prices['prix'])." ".$template->translate("/article/autres_infos/offert");
          $template->parts["kdopack"] = "";
          vignette($art_pack['artid'],'packoffert',"last");
          //$template->parts["kdopack"]="<div class=\"packsepkdo\">=</div>".artpack($art_pack['artid'],"ArtPack",$pack['artid'],$art_pack['qte'],$dispo,$dispoid);
          $template->parts["prixpdtoffert"]="";
          
          if ($dispoid>$olddispoid) {
            $olddispoid=$dispoid;
            $template->parts["dispopack"]=$dispo;
          }
          $template->parts["pdtoffertpack"]="";
        } else {
          //$template->parts["artinpack"].=$sep.artpack($art_pack['artid'],"ArtPack",$pack['artid'],$art_pack['qte'],$dispo,$dispoid);
          $template->parts["artinpack"] .= $sep;
          //vignette($art_pack['artid'],'pack',$i==1?"first":"");
          vignette($art_pack['artid'],'pack',"");
          $sep = "<div class=\"sep_conteneur\"><div class=\"packsep\"></div></div>";
          if ($dispoid>$olddispoid) {
            $olddispoid=$dispoid;
            $template->parts["dispopack"]=$dispo;
          }
        }
      }
    
      $nbPack++;
      if($nbPack>1) $template->parts["listartinpack"].="<div class=\"clear\"></div><div class=\"hsep\"></div>";
      $template->parts["artid"]=$art_pack['packid'];
      
      /*
      * On vérifie si un produit du pack possède des options
      * Si c'est le cas, on dirige vers la sélection des options du/des produit(s) du pack
      * Sinon, on envoie vers le panier
      */

      //Requête de sélection des produits d'un pack
      $sqlProduitsPack = "SELECT COUNT(ap.artid) as nb"; 
      $sqlProduitsPack .= " FROM article_pack ap";
      $sqlProduitsPack .= " INNER JOIN article_caractval ac ON (ac.artid = ap.artid)";
      $sqlProduitsPack .= " AND ap.packid = '".$art_pack['packid']."'";
      $sqlProduitsPack .= " GROUP  BY ap.artid";
      $reqArticlesOptions = $DB_site->query($sqlProduitsPack);

      //Si la requête retourne un résultat => il y a des options
      $template->parts["nbOptions"] = $DB_site->num_rows($reqArticlesOptions);      
      
      $pack_prices=$packs->prices($packid,$template->tva_ref(),$sitepro);
      
      $template->parts["nomPack"]=$pack['artcode'];
      $taxtxt=($sitepro?"/site/ht":"/site/ttc");
      $strikeout="<div class=\"strikeout\"><del>".$template->price_format($pack_prices['prixconseille'])." <small>".$template->translate($taxtxt,"U")."</small></del></div>";
      $price=$template->price_format($pack_prices['prix'],2,',','',' ')." <sup>".$template->translate($taxtxt,"U")."</sup>";
      $eurosoffert=$template->price_format($pack_prices['remisepack']);
      
      $template->parts["prices"] = ""
        . "<div class=\"prices\">"
          . $strikeout
          . "<div class=\"price\">".$price."</div>"
        . "</div>";
      
      $j=0;
      
      if ($typeoffre=='euro') {
        $reducTxt="<span class=\"reducTxt\">-".$template->price_format($pack_prices['remisepack'],0)."</span>";
        $lekdo = ""
          . "<div class=\"artpack\">"
          //. $cercleReduc
          . $reducTxt
          . "<div class=\"clear\"></div>"
          . "</div>";

        $template->parts["kdopack"]=$lekdo;
      }
      
      if ($typeoffre=='pourcent') {
        $reducTxt="<span class=\"reducTxt\">-".explode('-', $art_pack['offre'])[1]."%</span>";
        $lekdo = ""
          . "<div class=\"artpack\">"
          //. $cercleReduc
          . $reducTxt
          . "<div class=\"clear\"></div>"
          . "</div>";

        $template->parts["kdopack"]=$lekdo;
      }
      
      if(!empty($template->parts["cadeaupack"])){
        $template->evaltemplate("PackKdo","remisepack");
      } else {
        $template->evaltemplate("PackRemise","remisepack");
      }
      
      $template->evaltemplate("ListArtPack","listartinpack");
      
      $template->parts["kdopack"]="";
      $template->parts["artinpack"]=" ";
      $sep="";
      $olddispoid=0;
      $template->parts["pdtoffertpack"]="";
      $template->parts["dispopack"]="";
      $template->parts["remisepack"]="";
    }
  }
    
  if($nbPack>0) $template->evaltemplate("OngletPack","ongletpack");
  $template->parts["nb_pack"]=$nbPack;
}
//**********//
// FIN PACK //
//**********//

$article['conseil_securite']="";
$article['conseil_installation']="";
$article['conseil_utilisation']="";
$article['conseil_entretien']="";


$case=0;
$nosconseils=array();
$req=$DB_site->query("SELECT type,path FROM article_nosconseils WHERE artid=$artid");
while($art_nc=$DB_site->fetch_array($req)){
    $nosconseils[$art_nc['type']][]=$template->translate($art_nc['path'],"F");
}

if($nosconseils!=array()){
    if(isset($nosconseils['conseil_installation'])){
  $template->parts["conseilinstallation"]="<p class=\"techtitle\">".$template->translate("/article/champs/conseil_installation","F")."</p>
  <ul><li>"
  .implode("</li><li>",$nosconseils['conseil_installation']).
  "</li></ul>";
  $case++;
    }
    if(isset($nosconseils['conseil_utilisation'])){
  $template->parts["conseilutilisation"].="<p class=\"techtitle\">".$template->translate("/article/champs/conseil_utilisation","F")."</p>
  <ul><li>"
  .implode("</li><li>",$nosconseils['conseil_utilisation']).
  "</li></ul>";
  $case++;
    }
    if(isset($nosconseils['conseil_entretien'])){
  if($case==2) $template->parts["conseilentretien"]="</div><div class=\"conseil\">";
  $template->parts["conseilentretien"].="<p class=\"techtitle\">".$template->translate("/article/champs/conseil_entretien","F")."</p>
  <ul><li>"
  .implode("</li><li>",$nosconseils['conseil_entretien']).
  "</li></ul>";
  $case++;
    }
    if(isset($nosconseils['conseil_securite'])){
  if($case==2) $template->parts["conseilentretien"]="</div><div class=\"conseil\">";
  $template->parts["conseilsecurite"].="<p class=\"techtitle\">".$template->translate("/article/champs/conseil_securite","F")."</p>
  <ul><li>"
  .implode("</li><li>",$nosconseils['conseil_securite']).
  "</li></ul>";
    }

    $template->evaltemplate("ArticleConseil","artconseil");
    $template->evaltemplate("OngletConseil","ongletconseil");
}
$template->evaltemplate("DescGenerale","descgenerale");
if($template->parts["artcons"]!="") $template->evaltemplate("ArticleConseille","artconseilles");
if($template->parts["artincl"]!="") $template->evaltemplate("ArticleConseilleIncl","artaccessincl");
if($template->parts["artsim"]!="") $template->evaltemplate("ArticleSimilaire","artsimilaires");
if($template->parts["artselect"]!="") $template->evaltemplate("ArticleSelection","artselection");
if($template->parts["compatlist"]!="" || $template->parts["compatrefs"]!="") $template->evaltemplate("ArticleCompatible","compatible");
if($template->parts["compatlist2"]!="") $template->evaltemplate("ArticleCompatible2","compatible2");
if($template->parts["listartinpack"]!="") $template->evaltemplate("ArticlePack","artpack");
$template->evaltemplate("OngletTechnique","onglettechnique");
$template->evaltemplate("ArticleTechnique","fichetechnique");
$template->evaltemplate("ArticlePagePswp","articlepswp");


$imgart=article_image($artid,280,420,$kdopid);
$template->parts["imgartid"] = "artid=".$artid;

  //Visuel de l'article
if($simulateur){
  $resource = imagecreatefrompng($rootpath."configurations/".$host."/images/simulateur/".$article['artid']."_51.png");
  //$resource = imagecreatefrompng();
  $dest = imagecreatetruecolor(1200, 1000);
  imagecopyresampled($dest, $resource, 0, 0, 0, 0, 649, 813, 849, 1013);
  imagepng($dest,$rootpath."simulateur/test.png");
  
  $template->evaltemplate("ArticleSimuImg", "imgart");
  $template->evaltemplate("ArticleSimuZoom","articlesimulateur");
}else{
  $template->parts["imgart"]="<img id=\"artpic\" class=\"visuel\" src=\"".$imgart."\" width=\"280\" height=\"420\" itemprop=\"image\" alt=\"".str_replace("\"", "", $article[$libelle])."\" onclick=\"openPhotoSwipe();\">";
  $template->parts["imgart_mobile"]="<img src=\"".$imgart."\" itemprop=\"image\" alt=\"".str_replace("\"", "", $article[$libelle])."\" onclick=\"\">";
}




$template->parts["strikeout"]="<div class=\"estrikeout\"></div>";
$template->parts["pc"]="";


$fraisport=$DB_site->query_first("SELECT gratuit FROM fraisport WHERE paysid=57 LIMIT 1");
$totallivr=$fraisport[0];



//Gestion prix articles
$template->parts["devise_code"] = $template->devise();
$template->parts["itempropDevise"] = $template->devise();
$article_prices = article_prices($article[artid],$template->tva_ref(),$template->site_code(),$sitepro);

$template->parts["pcprice"]   = round($article_prices['remise'])."%";
$template->parts["pricediff"] = $template->price_format($article_prices['prixconseille']-$article_prices['prix']);
$template->parts["soprice"]   = $template->price_format($article_prices['prixconseille']);

//Formatage des prix
$template->parts["pricetax"]      = $template->translate($sitepro?"/site/ht":"/site/ttc","U");
$template->parts["price"]         = $template->price_format($article_prices['prix'],2,true,false);
$template->parts["itempropPrix"]  = $template->price_itemprop($article_prices['prix'],2);
$template->parts["prixd3"]        = $template->price_format($article_prices['prix']/3);

$promo=$DB_site->query_first("SELECT COUNT(*) FROM promo WHERE artid='".$article[artid]."' AND fin_promo > '".time()."'");
$secondchoix=$DB_site->query_first("SELECT COUNT(*) FROM secondchoix WHERE artid=".$article[artid]);
if (round($article_prices['prix']) != round($article_prices['prixconseille']) && round($article_prices['remise'])>4){
    if($promo[0]>0 || $secondchoix[0]>0){
        $template->parts["pc"]        = "<p class=\"reduc\">-".round($article_prices['remise'])."%</p>";
        $template->parts["strikeout"] = "<p class=\"prix_barre\">".$template->price_format($article_prices['prixconseille'])."</p>";
        $promotitle=round($article_prices['remise'])."% ".$template->translate("/article/reduction")." : ";
    }
    if($promo[0] == 0){
      //if($article_prices['remise'] >= 5) $template->evaltemplate("ArticleEconomie","economie");
      $template->evaltemplate("ArticlePrixBarre","prixbarre");
    }
}

if($article_prices['vf']) {
    $vfarticle=$DB_site->query_first("SELECT * FROM vente_flash_article vfa INNER JOIN vente_flash vf ON (vf.id=vfa.vfid) WHERE (".time()." BETWEEN vf.du AND vf.au) AND vfa.artcode='$article[artcode]'");
	// var dn=new Date(".date("Y").",".(date("m")-1).",".date("d").",".date("H").",".date("i").",0);
    $template->parts["js"]="<script language=\"javascript\">
    var d=new Date(".date("Y",$vfarticle['au']).",".(date("m",$vfarticle['au'])-1).",".date("d",$vfarticle['au']).",".date("H",$vfarticle['au']).",".date("i",$vfarticle['au']).",0);
    var dn=new Date();
    var dstr={days:\"".$template->translate("/article/champs/jours")."\",hours:\"".$template->translate("/article/champs/heures")."\",minutes:\"".$template->translate("/article/champs/minutes")."\",seconds:\"".$template->translate("/article/champs/secondes")."\"};
	if(typeof conversion_seconde_heure != 'function'){
		window.conversion_seconde_heure = function(time){
			var reste=time;
			var result='';
					
			var nbJours=Math.floor(reste/(3600*24));
			reste -= nbJours*24*3600;
						
			var nbHours=Math.floor(reste/3600);
			reste -= nbHours*3600;
						
			var nbMinutes=Math.floor(reste/60);
			reste -= nbMinutes*60;
		 
			var nbSeconds=reste;
		 
			if (nbJours>0) result=result+nbJours+dstr.days+' ';
			if (nbHours>0)result=result+nbHours+dstr.hours+' ';
			if (nbMinutes>0) result=result+nbMinutes+dstr.minutes+' ';
			if (nbSeconds>0) result=result+Math.round(nbSeconds)+dstr.seconds+' ';
						
			return result;
		};
	}
	if(typeof upddecompte != 'function'){
	   window.upddecompte = function(){
			if(!document.getElementById('vfdecompte'))
				return;
			dn.setTime(dn.getTime()+1000);
			var diff=d.getTime()-dn.getTime();
			if(diff<=1000)
				location.reload();
			document.getElementById('vfdecompte').innerHTML=conversion_seconde_heure(diff/1000);
			setTimeout('upddecompte()',1000);
	   };
	}
    upddecompte();
    </script>" ;
    
    if($article_prices['remise'] < 1 || $vfarticle['remise'] == '0%') {
    }
    else {
    $template->parts["pc"]="<p class=\"reduc\">".round(-$article_prices['remise'])."%</p>";
    $template->parts["strikeout"]="<p class=\"prix_barre\">".$template->price_format($article_prices['prixconseille'])." </p>";
    $promotitle=round($article_prices['remise'])."% ".$template->translate("/article/reduction")." : ";
}
    $template->evaltemplate("VFCompteur","venteflash");
    $template->parts["imgart"]="<img src=\"/images/templates/".$template->langue."/french_days.jpg\" class=\"vfimg\">".$template->parts["imgart"];
}
if($article_prices['vp']) {
    $vfarticle=$DB_site->query_first("SELECT * FROM vente_privee_article vfa INNER JOIN vente_privee vf ON (vf.id=vfa.vfid) WHERE (".time()." BETWEEN vf.du AND vf.au) AND vfa.artcode='$article[artcode]'");
  // var dn=new Date(".date("Y").",".(date("m")-1).",".date("d").",".date("H").",".date("i").",0);
    $template->parts["js"]="<script language=\"javascript\">
    var d=new Date(".date("Y",$vfarticle['au']).",".(date("m",$vfarticle['au'])-1).",".date("d",$vfarticle['au']).",".date("H",$vfarticle['au']).",".date("i",$vfarticle['au']).",0);
    var dn=new Date();
    var dstr={days:\"".$template->translate("/article/champs/jours")."\",hours:\"".$template->translate("/article/champs/heures")."\",minutes:\"".$template->translate("/article/champs/minutes")."\",seconds:\"".$template->translate("/article/champs/secondes")."\"};
  if(typeof conversion_seconde_heure != 'function'){
    window.conversion_seconde_heure = function(time){
      var reste=time;
      var result='';
          
      var nbJours=Math.floor(reste/(3600*24));
      reste -= nbJours*24*3600;
            
      var nbHours=Math.floor(reste/3600);
      reste -= nbHours*3600;
            
      var nbMinutes=Math.floor(reste/60);
      reste -= nbMinutes*60;
     
      var nbSeconds=reste;
     
      if (nbJours>0) result=result+nbJours+dstr.days+' ';
      if (nbHours>0)result=result+nbHours+dstr.hours+' ';
      if (nbMinutes>0) result=result+nbMinutes+dstr.minutes+' ';
      if (nbSeconds>0) result=result+Math.round(nbSeconds)+dstr.seconds+' ';
            
      return result;
    };
  }
  if(typeof upddecompte != 'function'){
     window.upddecompte = function(){
      if(!document.getElementById('vfdecompte'))
        return;
      dn.setTime(dn.getTime()+1000);
      var diff=d.getTime()-dn.getTime();
      if(diff<=1000)
        location.reload();
      document.getElementById('vfdecompte').innerHTML=conversion_seconde_heure(diff/1000);
      setTimeout('upddecompte()',1000);
     };
  }
    upddecompte();
    </script>" ;

    if($article_prices['remise'] < 1 || $vfarticle['remise'] == '0%') {
    }
    else {
      $template->parts["pc"]="<p class=\"reduc\">".round(-$article_prices['remise'])."%</p>";
      $template->parts["strikeout"]="<p class=\"prix_barre\">".$template->price_format($article_prices['prixconseille'])." </p>";
      $promotitle=round($article_prices['remise'])."% ".$template->translate("/article/reduction")." : ";
    }

    $template->evaltemplate("VPCompteur","venteprivee");
    $template->parts["imgart"]="<img src=\"/images/templates/".$template->langue."/vente_mobar.jpg\" class=\"vfimg\">".$template->parts["imgart"];
}

if($article['ecopart'] > 0){
    $template->parts["ecopart"] = ($sitepro ? number_format($article['ecopartht'],2,',',' ').' €' : number_format($article['ecopart'],2,',',' ').' €');
    $template->evaltemplate("ArticleEcopart","ecoparttxt");
}

//Livraison offerte
if($article_prices['prix'] >= $totallivr ){
    $template->evaltemplate("ArticleLivraisonOfferte","article_livraison_offerte");
}

//Génération du picto garantie
if(!empty($garantie)){
    if(file_exists($_SERVER['DOCUMENT_ROOT']."/configurations/".$host."/images/templates/".$template->langue."/garantie_".$dureeGarantie.".jpg")){
        $icongarantie = "<img class=\"picto\" src=\"/images/templates/".$template->langue."/garantie_".$dureeGarantie.".jpg\" alt=\"garantie\" width=\"41\" height=\"41\">";
    } 
}

//Génération du picto niveau sonore
if(!empty($niveaubruit)){
    if(file_exists($_SERVER['DOCUMENT_ROOT']."/configurations/".$host."/images/templates/niveausonore_".$niveaubruit.".png")){
        $iconsonore = "<img class=\"picto\" src=\"/images/templates/niveausonore_".$niveaubruit.".png\" alt=\"niveau sonore\" width=\"41\" height=\"41\">";
    } 
}
if(!is_null($class_energetique)){
    if(file_exists($_SERVER['DOCUMENT_ROOT']."/configurations/".$host."/images/templates/energie_".$template->translate("/article/class_energetique/$class_energetique").".jpg")){
        $iconenergie="<img class=\"picto\" src=\"/images/templates/energie_".$template->translate("/article/class_energetique/$class_energetique").".jpg\" alt=\"niveau sonore\" width=\"41\" height=\"41\">";
    }
}

//Affichage des icones
//if($iconlivr) $template->parts["icons"] .= $iconlivr;
if($icongarantie) $template->parts["icons"] .= $icongarantie;
if($iconenergie) $template->parts["icons"] .= $iconenergie;
if($iconsonore) $template->parts["icons"] .= $iconsonore;

// pays de fabrication
if(file_exists($_SERVER['DOCUMENT_ROOT']."/configurations/".$host."/images/templates/origine-gd-".$article['paysfabrication'].".jpg")){
    $template->parts["icons"] .= "<img class=\"picto\" src=\"/images/templates/origine-gd-".$article['paysfabrication'].".jpg\" alt=\"".$template->translate("/article/origines/".$article['paysfabrication'],"F")."\" width=\"41\" height=\"41\">";
}

if($template->site_code()=="base"){
    $sr=new showroom($DB_site,$template->langue);
    $sr_icon="/images/templates/showroom.jpg";
    if($sr->in_showroom($article['artid']) && file_exists($_SERVER['DOCUMENT_ROOT']."/configurations/".$host.$sr_icon)) $template->parts["icons"] .="<a href=\"/showroom-cave-a-vin-lyon.html\" target=\"_blank\"><img class=\"picto\" src=\"$sr_icon\" alt=\"showroom\" width=\"41\" height=\"41\"></a>";
}

$requete=$DB_site->query("SELECT iconid FROM article_icon ai INNER JOIN icons ON(icons.id=ai.iconid) WHERE active=1 AND ai.artid=".$article['artid']);
while($icon=$DB_site->fetch_array($requete)){
    $path="/images/icons/".$template->langue."/".$icon['iconid'].".png";
    if(file_exists($_SERVER['DOCUMENT_ROOT']."/configurations/".$host.$path)) $template->parts["icons"].="<img src=\"".$path."\" alt=\"picto\" class=\"picto\">";
}

$configurateur=new configurateur($DB_site);
$configurateur->loadcookie();
/*if($configurateur->in_artids($artid)){ // POUR Avoir un bouton retour au configurateur 
    $template->parts["config_url"]="/configurateur.php?id=".$configurateur->configurateurid;
    $template->evaltemplate("ConfigRetour","retour_config");
}*/

if($article['video_code'.$template->langue]!=""){
  $template->parts["video_code"]=$article['video_code'.$template->langue];
  $template->evaltemplate("ArticleVideo","artvideo");
  $template->evaltemplate("OngletVideo","ongletvideo");
}

//Gestion Sofinco - affichage taux credit, url sofinco etc...
//Si le produit est commandable
if($article['commandable'] != '0'){

    //Affichage du prix
    $template->evaltemplate("ArticlePrix","prices");

    //if($article['prix']>150) $template->evaltemplate("Paiement3x","paiement3x");

    $oArticle = new produit($DB_site, $template->langue, $template->site_code(), $template->tva_ref(), $article['artid']);

    if(!$sitepro && $article_prices['prix'] > 150 && $article_prices['prix'] <= 25000 && $oArticle->categorie[count($oArticle->categorie)-1]['categorie_id'] != 182){ 
              $providerId                             = "99102122272";
              $template->parts["url_simulation"]      = "https://www.creditpartner.fr/creditpartner/?q6=web_em&x1=simu_vac&a9=".$providerId."&n2=290&s3=".$article_prices['prix'];
              $template->parts["mention_pedagogique"] = "Un crédit vous engage et doit être remboursé. Vérifiez vos capacités de remboursement avant de vous engager";
              $template->parts["total_financement"] = $article_prices['prix'];
           }
}


$template->parts["prices"] ="";
$template->parts["artid"]=$article['artid'];
$template->parts["ajoutSelect"]="";
for($i=1;$i<=500;$i++) $template->parts["ajoutSelect"].="<option value=\"$i\">$i</option>";

//Récupération des images de l'article
$limit=3;
$photonb=0;
$images_ambiance=array();
// if($icongarantie!="") $limit--;
// if($iconlivr!="") $limit--;
$sql = "SELECT ap.articlephotoid, ap.image, ap.legende, ap.ambiance";
$sql .= " FROM articlephoto ap, article a";
$sql .= " WHERE a.artid = ap.artid";
$sql .= " AND a.artid = '".$article['artid']."'";
$sql .= " AND ap.langue IN ('','".$template->langue."')";
$sql .= " AND ap.visible = 0";
$sql .= " AND ap.pro IN ('".($template->pro?2:1)."','0')";
$sql .= " ORDER BY position";
$article_images = $DB_site->query($sql);
$nb_articles = $DB_site->num_rows($article_images);
$sep = "";
$urlPhotos = array();
if($nb_articles > 0){
        while ($article_image = $DB_site->fetch_array($article_images)){
            $photonb++;
            $src = "/images/".$article['artid']."_$article_image[articlephotoid]_60_60.jpg";
            $urlPhotos[] = "/images/".$article['artid']."_$article_image[articlephotoid]_800_600.jpg";
            $template->parts["itempropImage"] .= "<link itemprop=\"image\" href=\"".$src."\" />";
            
            if ($photonb < 6) $TemplatePhotosArticleBit .= ""
            . "<div class=\"smallimg\" onclick=\"openPhotoSwipe('/images/".$article['artid']."_".$article_image[articlephotoid]."_800_600.jpg');\" onmouseover=\"change_artpic('/images/".$article['artid']."_".$article_image[articlephotoid]."_280_420.jpg');\" onmouseout=\"change_artpic('".$imgart."')\">"
                . "<img src=\"".$src."\" alt=\"".$article[$libelle]."\" width=\"60\" height=\"60\">"
            . "</div>";
            
            if($article_image['ambiance'] == 1) $images_ambiance[] = "<img src=\"/images/".$article['artid']."_".$article_image['articlephotoid']."_930_543.jpg\" width=\"630\" height=\"350\" alt=\"".$article[$libelle]."\">";
            
            $template->parts["imgcomp_mobile"].="<img src=\"/images/".$article['artid']."_".$article_image['articlephotoid']."_280_420.jpg\" width=\"420\" height=\"280\">";
        }

    $template->parts["simg"] = $TemplatePhotosArticleBit;
}
$imgbase = "/images/".$article['artid']."_800_600.jpg";
//$img_base = resizedimg($imgbase,600,800);
$template->parts["jspswp"] = ""
."<script type=\"text/javascript\" >\n"
  ."function openPhotoSwipe(urlimg){"
  ."var pswpElement = document.querySelectorAll('.pswp')[0];\n"
  ."if(typeof(urlimg) == 'undefined' ) {"
  ."var items = ["
    ."{src:'".$imgbase."',w:800,h:600},";

      foreach($urlPhotos as $urlPhoto){
      //$size = getimagesize($urlPhoto);
        $template->parts["jspswp"].="{src:'".$urlPhoto."',w:800,h:600},";
      }

$template->parts["jspswp"] = substr($template->parts["jspswp"], 0, strlen($template->parts["jspswp"])-1);
$template->parts["jspswp"] .= "];\n"
      ."}else{"
        ."var items = ["
        ."{src:urlimg,w:800,h:600},"
        ."{src:'".$imgbase."',w:800,h:600},";
        foreach($urlPhotos as $urlPhoto){
          //$size = getimagesize($urlPhoto);
          $template->parts["jspswp"].="{src:'".$urlPhoto."',w:800,h:600},";
        }
$template->parts["jspswp"] = substr($template->parts["jspswp"], 0, strlen($template->parts["jspswp"])-1);
$template->parts["jspswp"] .= "];}\n";

$template->parts["jspswp"] .= "var options = { 
		index : 0,
		mainClass : 'pswp--minimal--dark',
		barsSize : {top:0,bottom:0},
		captionEl : false,
		fullscreenEl : false,
		shareEl : false,
		bgOpacity : 0.85,
		tapToClose : true,
		tapToToggleControls : false
	};\n"
  ."var gallery = new PhotoSwipe( pswpElement, PhotoSwipeUI_Default, items, options);\n"
  ."gallery.init();}\n"
  ."</script> \n";



//Si article actif, on affiche certains blocs
if($art_actif[0] == 1){
    //Si ce n'est pas un pack, on laisse choisir la quantité souhaitée
    if(is_null($kdopid) || $kdopid==0){

        if($article['commandable'] == '0'){
            $template->evaltemplate("ArticlePrix0","prices");
        } elseif($article['commandable'] == '1'){
            $template->evaltemplate("ArticleAjout","ajout");
        } elseif($article['commandable'] == '2'){
            $template->evaltemplate("ArticleAjout2","ajout");
        }
  
    } else {

      $artinpack=$packs->getProduits($kdopid);
      $template->parts["kodartid"]=$kdopid;
      foreach($artinpack as $art_pack){ 
        if($art_pack['artid']!=$artid){
          $marque=$DB_site->query_first("SELECT marque.marqueid,type".$template->langue.", libelle FROM marque INNER JOIN article_marque USING (marqueid) WHERE artid=".$art_pack['artid']);
          $libelle_pack=$art_pack['libelle'.$template->langue];
        
          $kdo_pack_img = article_image($art_pack['artid'], 100, 100);

          $template->parts["kdopacklibelle"]=$libelle_pack." ".$marque['libelle'];
          $template->parts["kdopackref"]=$art_pack['artcode'];
          $template->parts["kdopacklogo"]="<img src=\"/marques/".$marque['marqueid']."_100_20.jpg\" alt=\"".$marque['type']."\" width=\"100\" height=\"20\">";
          //$template->parts["kdopackimg"]="images/".$art_pack['artid']."_100_100.jpg";
          $template->parts["kdopackimg"]= $kdo_pack_img;
          $template->parts["kdopacklink"]="art,".$art_pack['artid'].",".url_rewrite($libelle_pack).".htm";
          $template->parts["kdopacklien"]=$template->mkurl('article',$art_pack['artid'],$art_pack);
          $kdoprice=article_prices($art_pack['artid'],$template->tva_ref(),$template->site_code(),$sitepro);
          $template->parts["kdoprice"]=$template->price_format($kdoprice['prix']);
          $template->evaltemplate("ArticleAjout3","ajout");
          $template->evaltemplate("ArticleAjoutPack","kdopack");
          break;
        }
      }
    }

    
    //Si le produit est commandable
    if($article['commandable'] != '0'){
        
        //Affichage du prix
        $template->evaltemplate("ArticlePrix","prices");

        //if($article['prix']>150) $template->evaltemplate("Paiement3x","paiement3x");

        $oArticle = new produit($DB_site, $template->langue, $template->site_code(), $template->tva_ref(), $article['artid']);

        if(!$sitepro && $article_prices['prix'] > 150 && $article_prices['prix'] <= 25000 && $oArticle->categorie[count($oArticle->categorie)-1]['categorie_id'] != 182){ 

              $template->parts["js_sofinco"]  = "<input type=\"hidden\" id=\"artid_sofinco\" value=\"".$article['artid']."\">";
              $template->evaltemplate("PaiementSofinco","paiement_sofinco");
              $template->evaltemplate("PaiementSofincoResp","paiement_sofinco_xs");
          }
    }
    
    if($sitepro){
    $loc=location($article_prices['prix']);
    
    if($loc!=false){
        $template->evaltemplate("ArticleLocation","paiement_sofinco");
        $template->parts["paiement_sofinco"]=$template->short_links($template->parts["paiement_sofinco"]);
        
        $template->parts["loctable"]="";
        foreach($loc as $mens=>$prix){
      $template->parts["loctable"].=$hr."
      <div class=\"mens\">
        <div class=\"one\">".$mens."</div>
        <div class=\"two\">".$template->price_format($prix)."</div>
        <div class=\"three\">".$template->price_format($mens*$prix)."</div>
        <div class=\"clear\"></div>
      </div>";
      $hr="<div class=\"hr\"></div>";
        }

        $template->parts["totalloc"]=$template->price_format($article_prices['prix']);
        $template->evaltemplate("PaiementLocation","paiement_sofinco");
    }
    }
    
    if($article['commandable']=='1'){
    $template->evaltemplate("DelaiLivraison","delailivraison");
    $template->evaltemplate("ArticleDevis","devis");
    $template->evaltemplate("ArticleLivraison","article_livraison");
    $template->evaltemplate("ArticleStock","article_stock");
    }
    
    $template->evaltemplate("AjoutPanier","ajoutpanier");

    //Affichage des vignettes de l'article
    $template->evaltemplate("ArticleVignettes","smallimg");

    if($cookieconsent){

    $pname=utf8_encode(html_entity_decode($article['libelle'.$template->langue],ENT_COMPAT,"ISO-8859-15"));    
    $template->parts["remarketing"]=google_remarketing($article['artid'],"product",$article['prix'],utf8_encode($categorie->libelle),$pname,$article['prix']);
  }
}


if($art_actif[0]==3 || $art_actif[0]==5){
    $template->parts["icons"]="";
    $template->parts["simg"]="";
    $template->evaltemplate("ArticleContact","contact_article");
    $template->evaltemplate("ArticleArchive","ajoutpanier");
}

if($cookieconsent){
//pixel facebook
$template->parts['fbpixel'] = "fbq('track', 'ViewContent', { 
  content_name: '".$article[artcode]."',
  content_category: '".utf8_encode($categorie->libelle)."',
  content_ids: ['".$article[artid]."'], // top 5-10 search results
  content_type: 'product',
  value: ".$article[prix].",
  currency: 'EUR'
});";
}
$secondchoix=$DB_site->query_first("SELECT COUNT(*) FROM secondchoix WHERE artid=".$article[artid]);
if($secondchoix[0]>0){
    //$template->parts["imgart"]="<img src=\"/images/templates/".$template->langue."/secondchoix.png\" class=\"bande\">".$template->parts["imgart"];
}

$article[$titre2] = html_entity_decode($article[$titre2],ENT_COMPAT,"ISO-8859-15") ;
$article[ecopart]= number_format($article[ecopart], 2, ',', '');
$article[ecopartht]= number_format(floatval($article[ecopartht]), 2, ',', '');
$article[poid] = $article[poid] / 1000;
$template->parts["libelle"]=$article[$libelle];
$template->parts["marque"]=$marq['libelle'];
$template->parts["itempropMarque"]=$marq['libelle'];
$template->parts["gamme"]=$article['gamme'.$template->langue];
$template->parts["artcode"]=$article["artcode"];
if($article["description".$template->langue] != ''){
  $template->parts["description_txt"] = $article["description".$template->langue];
  $template->evaltemplate("ArticleDescription","description");
}
if($art_actif[0]==3 || $art_actif[0]==5){
    $lot=$DB_site->query_first("SELECT COUNT(*) FROM article_lot WHERE lotid=".$artid);
    if($lot[0]==0) $template->parts["artcode"].=" (".$article['artcode_eurocave'].")";
}
//Affichage de l'image d'ambiance
if(count($images_ambiance)>0){
    $template->parts["ambiance_slider"] = ""
    . "<div id=\"ambianceimgs\">"
        . implode("",$images_ambiance)
    . "</div>"
    . "<div id=\"ambiancebtnswrap\">"
        . "<div id=\"ambiancebtns\"></div>"
    . "</div>"
    . "<div class=\"clear\"></div>";

    $navigationslider=count($images_ambiance)>1 ? "navigation: {container: 'ambiancebtns', label: '', prev: {className: 'SliderPrev', label: ''}, next: {className: 'SliderNext', label: ''} }" : "";
    
    $template->parts["js"].="

<script type=\"text/javascript\" src=\"//cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.min.js\"></script>
 <script type=\"text/javascript\">
    $(document).ready(function(){
      $('#ambianceimgs').slick({
        autoplay: true,
        autoplaySpeed: 2000,
        fade: true,
        dots: true,
        centerMode: true,
        prevArrow: '<a href=\"javascript:void(0);\" class=\"slick-prev\"></button>',
        nextArrow: '<a href=\"javascript:void(0);\" class=\"slick-next\"></button>',
        });
    });
  </script>


    </script>";

    $template->evaltemplate("ArticleAmbiance","ambiance");
}

include("barrefixe.php");

foreach($template->sites() as $site_code=>$site){
    $article_site=$DB_site->query_first("SELECT active FROM article_site WHERE site_code='".$site_code."' AND artid=".$article['artid']);
    if($article_site['active']==1 || $article_site['active']==3 || $article_site['active']==5){
  foreach($site['hosts'] as $url=>$deflangue){
      if($site['actif'][$url]){
        $alternate=new template($deflangue,$DB_site);
        $alternate->langue=$deflangue;
        $template->parts["alternate"].="<link rel=\"alternate\" hreflang=\"".$site['locales'][$deflangue]."\" href=\"https://www.".$url.$alternate->mkurl("article",$article['artid'],$article)."\">\n";
      }
  }
    }
}

//$metatitle=$promotitle.$template->parts["libelle"]." ".ucwords($marq['libelle']).", ".$article['artcode']." - ".$template->translate("/site/nom");
$metatitle=$template->parts["libelle"]." ".ucwords($marq['libelle']) . " " .$template->translate("/site/nom");
$metadescription=$template->parts["libelle"]." ".ucwords($marq['libelle']) . ". " .$template->translate("/article/meta_description");
if($art_actif[0]==3 || $art_actif[0]==5){
    $metatitle=$template->translate("/archives/meta_article","F")." ".$template->parts["libelle"]." ".ucwords($marq['libelle'])." - ".$template->translate("/site/nom");
    $metadescription=$metatitle." ".$template->translate("/site/meta_description");
}else{
  $template->parts['is_itemprop'] = "itemscope itemtype=\"http://schema.org/Product\"";
  $template->evaltemplate("ArticleItemProp", "itemprop");  
}
$template->parts["opengraph"]=opengraph($metatitle,"website",null,$imgart);
$template->pageparams($host,$metatitle,$metadescription);
$template->evaltemplate("Footer1","footer1");
$template->evaltemplate("ArticlePage","main");
$template->out();


function article_base($artid){
    global $DB_site, $artid_base;
    $artid_base = $artid;
    $article = $DB_site->query_first("SELECT * FROM liens_articles WHERE article_lie = '$artid'");
    if ($article[liensarticlesid] != ""){
        $actif = $DB_site->query_first("SELECT * FROM article WHERE artid = $article[article_base]");
        if ($actif[active] == '0' or $actif[artid] == ""){
            $artid_base = $article[article_lie];
        }else{
            article_base($article[article_base]);
        }
    }
    return $artid_base;
}


function vignscroll($divid,$nbelem){
global $template;

$template->parts["scrollbtns_".$divid]="";
if($nbelem<=3) return false;
  $template->parts["scrollbtns_".$divid]="<div class=\"scrollbtnsparent\"><div class=\"scrollbtns\" id=\"scroll_".$divid."_btns\"><span onclick=\"vignscroll('".$divid."','-1')\"><i class=\"fas fa-chevron-left\"></i></span>";
  for($b=0;$b<$nbelem;$b++){
    $template->parts["scrollbtns_".$divid].="<span class=\"btn".($b<=2?" selected":"")."\" onclick=\"vignscroll('".$divid."','".$b."')\"></span>";
	}
  $template->parts["scrollbtns_".$divid].="<span onclick=\"vignscroll('".$divid."','+1')\"><i class=\"fas fa-chevron-right\"></i></span></div><div class=\"clear\"></div></div>";
}
function show_specs($table, $id, $field_id='artid', $ret=null) {
    global $DB_site, $template, $items, $caract_princip, $article, $caracts,$chkdescription;
  
    include_once('class/articles/'.$table.'.php');
    $funct = $table.'_fields';
    $fieldsets = $funct($article['typearticleid']);
    $keys = array_keys($fieldsets);
    $correspondance = array("article_cuisiniere" => "Cuisinière", "article_table_cuisson"=>"Plaque de cuisson", "article_four"=>"Four","article_piano"=>"Piano de cuisson","article_congel_dependant"=>"Congélateur", "article_frigo_dependant"=>"Réfrigérateur","article_froid"=>"Combiné froid","article_foyers"=>"Foyer" );
    if(!is_null($ret)){
      $num = $ret+1;
    }
    $template->parts['techlist'] .= 
    "<div class=\"col-xs-12\">
        <p class=\"techtitleplus\">".$correspondance[$table]." ".$num."</p>
     </div>
     <div class=\"col-md-3 col-sm-6 col-xs-12\">";
     $test='';
    //Génération des caractéristiques
    if (!is_null($fieldsets[$keys[0]][0])){
        $fieldsets = array(array($fieldsets));
    }
    //Boucle sur les groupements de caractéristiques (ex : array(0 => 'froid', 1 => 'frigo', 2 => 'congel'))
    foreach ($fieldsets as $fieldset){
        //Boucle sur chaque type de caractériques
        $z=0;
        foreach ($fieldset as $title => $fields) {    
            //Requête de sélection des différentes caractéristiques constituant le produit
            $sql = "SELECT ".implode(',', array_keys($fields));
            $sql .= " FROM ".$table;
            $sql .= " WHERE ".$field_id." = ".$id;
            $item = $DB_site->query_first($sql);
            $titre = $template->translate("/article/champs/$title","F");
            $y=0;
            //on check si on affiche le bloc, si il y a au moins 1 caractéristique
            $affiche= false;
            foreach ($fields as $field => $props){
              if (!empty($item[$field])){
                    $affiche=true;                                        
                } 
            }
            if($affiche){
              if($z%4 == 0){
                $template->parts['techlist'] .= "</div><div class=\"col-xs-12\"><div class=\"col-md-3 col-sm-6 col-xs-12\"><p class=\"techtitle\">$titre</p>";
              }else{
                $template->parts['techlist'] .= "<div class=\"col-md-3 col-sm-6 col-xs-12\"><p class=\"techtitle\">$titre</p>";
              }
            }
              
            //Boucle sur chaque caractéristique
            foreach($fields as $field => $props){
                //Si la caractéristique n'est pas renseignée pour ce produit, on passe à la caractéristique suivante
                if (empty($item[$field])){
                    continue;                                        
                }

                /* Assignation des valeurs de champs caractéristique
                * $tr (CHAMP_NUMERIC / CHAMP_TEXT / CHAMP_TRAD / CHAMP_MULTI) : indique le type de champs
                * $label : intitulé du champs
                * $suffix : chaine de caratères à rajouter à la fin de la caractéristique (ex : cm², L, etc...)
                * $req : ?
                * $info : si il faut ou non afficher la caractéristique
                */
                
                list($tr, $label, $suffix, $req, $info) = $props;
               // var_dump($tr, $label, $suffix, $req, $info);
                $infoicon = "";
                
                if($y%2 == 0) {
                  $class  ='fond_gris' ;
                }else{
                  $class ='' ;
                }
                switch($tr){
                    case CHAMP_NUMERIC:
                    
                    case CHAMP_TEXT:
                        if($field == "poids") {
                            $item[$field] = $item[$field] / 1000;
                            $suffix = "Kg";
                        }
                        $value = $item[$field];
                        $value_princip = $value;
                        if($suffix != '') $suffix =" ($suffix)";
                         $template->parts['techlist'] .=
                         "<div>
                            <div class=\"row $class\">
                              <div class=\"col-xs-7\"><b>".$label.$suffix." :</b></div>
                              <div class=\"col-xs-5\" style=\"text-align:right;\">".$value."</div>
                            </div>
                          </div>";
                        break;
                                   
                    case CHAMP_TRAD:
                        if($field == "couleur_fab") continue;
                        $value = $template->translate($item[$field]);
                        $value_princip = $value;
                        if($suffix != '') $suffix =" ($suffix)";
                         $template->parts['techlist'] .=
                         "<div>
                            <div class=\"row $class\">
                              <div class=\"col-xs-7\"><b>".$label.$suffix." :</b></div>
                              <div class=\"col-xs-5\" style=\"text-align:right;\">".$value."</div>
                            </div>
                          </div>";


                        break;
                                   
                    case CHAMP_MULTI:
                        $value="";
                        $value_princip="";
                        $exvalues=explode(",",$item[$field]);
                        $sortfunct=$table."_order_values";
                        if(function_exists($sortfunct)){
                            $exvalues=$sortfunct($field,$exvalues);
                        }
                        if(!empty($exvalues)){
                            foreach($exvalues as $exvalue){
                                $value_princip.=$template->translate($exvalue,"F").", ";
                            }
                            $value_princip=substr($value_princip,0,-2);
                            $template->parts['techlist'] .=
                                "<div>
                                    <div class=\"row $class\">
                                      <div class=\"col-xs-7\"><b>".$label." :</b></div>
                                      <div class=\"col-xs-5\" style=\"text-align:right;\">".$value_princip."</div>
                                    </div>
                                </div>";
                        }
                        break;
                }
                $y++;
            }
            if($affiche){
              $template->parts['techlist'] .="</div>";
              $z++;
            }      
        }
    }
    $template->parts['techlist'] .="</div>";
}
function show_specs_foyers($table, $id, $field_id='artid', $ret=null) {
  global $DB_site, $template, $items, $caract_princip, $article, $caracts,$chkdescription;
  include_once('class/articles/'.$table.'.php');
  $funct = $table.'_fields';
  $fieldsets = $funct($article['typearticleid']);
  $keys = array_keys($fieldsets);
  $correspondance = array("article_foyers"=>"Foyer" );
  if(!is_null($ret)){
    $num = $ret+1;
  }
  if(intval($num)%5==0){
    $template->parts['techlist'] .= "</div><div class=\"col-xs-12\"><div class=\"col-md-3 col-sm-6 col-xs-12\"><p class=\"techtitleplus\">".$correspondance[$table]." ".$num."</p>";
  }else{
    $template->parts['techlist'] .= "<div class=\"col-md-3 col-sm-6 col-xs-12\"><p class=\"techtitleplus\">".$correspondance[$table]." ".$num."</p>";
  }
   $test='';
  //Génération des caractéristiques
  if (!is_null($fieldsets[$keys[0]][0])){
      $fieldsets = array(array($fieldsets));
  }
  //Boucle sur les groupements de caractéristiques (ex : array(0 => 'froid', 1 => 'frigo', 2 => 'congel'))
  foreach ($fieldsets as $fieldset){
    //Boucle sur chaque type de caractériques
    foreach ($fieldset as $title => $fields) {    
      //Requête de sélection des différentes caractéristiques constituant le produit
        $sql = "SELECT ".implode(',', array_keys($fields));
        $sql .= " FROM ".$table;
        $sql .= " WHERE ".$field_id." = ".$id;
        $item = $DB_site->query_first($sql);
        $titre = $template->translate("/article/champs/$title","F");
        $y=0;
        //on check si on affiche le bloc, si il y a au moins 1 caractéristique
        $affiche= false;
        foreach ($fields as $field => $props){
          if (!empty($item[$field])) $affiche=true;
        }
        $template->parts['techlist'] .=    "<div class=\"col-xs-12\">";
        //Boucle sur chaque caractéristique
        foreach($fields as $field => $props){
          //Si la caractéristique n'est pas renseignée pour ce produit, on passe à la caractéristique suivante
          if (empty($item[$field])) continue;                                    
          list($tr, $label, $suffix, $req, $info) = $props;
          if($y%2 == 0) {
            $class  ='fond_gris' ;
          }else{
            $class ='' ;
          }
          switch($tr){
            case CHAMP_NUMERIC:
            case CHAMP_TEXT:
              $value = $item[$field];
              $value_princip = $value;
              if($suffix != '') $suffix =" ($suffix)";
              $template->parts['techlist'] .=
                         "<div class=\"row $class\">
                            <div class=\"col-md-6 col-xs-7\"><b style=\"white-space:nowrap;\">".$label.$suffix." :</b></div>
                              <div class=\"col-md-6 col-xs-5 \" style=\"text-align:right;\">".$value."</div>
                          </div>";
            break;
            
            case CHAMP_TRAD:
              $value = $template->translate($item[$field]);
              $value_princip = $value;
              if($suffix != '') $suffix =" ($suffix)";
              $template->parts['techlist'] .=                         "
                            <div class=\"row $class\">
                              <div class=\"col-md-6 col-xs-7 \"><b style=\"white-space:nowrap;\">".$label.$suffix." :</b></div>
                              <div class=\"col-md-6 col-xs-5 \" style=\"text-align:right;\">".$value."</div>
                            </div> ";
            break;
                                   
            case CHAMP_MULTI:
              $value="";
              $value_princip="";
              $exvalues=explode(",",$item[$field]);
              $sortfunct=$table."_order_values";
              if(function_exists($sortfunct)){
                  $exvalues=$sortfunct($field,$exvalues);
              }
              if(!empty($exvalues)){
                foreach($exvalues as $exvalue){
                  $value_princip.=$template->translate($exvalue,"F").", ";
                }
                $value_princip=substr($value_princip,0,-2);
                $template->parts['techlist'] .=
                        "<div class=\"row $class\">
                              <div class=\"col-xs-7 \"><b style=\"white-space:nowrap;\">".$label." :</b></div>
                              <div class=\"col-xs-5 \" style=\"text-align:right;\">".$value_princip."</div>
                            </div>";
              }
            break;
          }
          $y++;
        }
        $template->parts['techlist'] .="</div></div>";
      }
    }
}
//retourne les id de lignes correspondantes ou false si pas de type dépendant
function checkit($value_id,$table_nom,$type_value='artid'){
  global $DB_site;
  $array = array();
  $result = $DB_site->query("SELECT id FROM $table_nom WHERE $type_value=$value_id");
  if($DB_site->num_rows($result) >0){
    $array[] = true;
    $array[] = $DB_site->num_rows($result);
    $tt = array();
    while($row = $DB_site->fetch_array($result)){
      $tt[] = $row[id];
    }
    $array[] = $tt;
    return $array;
  }else{
    return false;
  }
}
?>
