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

//Insertion dans les articles vus si l'utilisateur n'est pas un admin
if(!isset($_COOKIE['admin_username']) && !in_array($_SERVER['REMOTE_ADDR'], $aAdminIPS)){

    $sql = "SELECT COUNT(*)";
    $sql .= " FROM article_vu";
    $sql .= " WHERE artid = ".$artid;
    $sql .= " AND s = '".$s."'";
    $artvus = $DB_site->query_first($sql);
    if($artvus[0] == 0){

        $sql = "INSERT INTO article_vu SET";
        $sql .= " artid   = ".$artid;
        $sql .= ", nb     = 1";
        $sql .= ", s      = '".$s."'";
        $sql .= ", userid = ".intval($user_info['userid']);
        $sql .= ", date   = ".time();
        $DB_site->query($sql);

    } else {
        
        $sql = "UPDATE article_vu SET";
        $sql .= " nb = nb + 1";
        $sql .= " WHERE artid = ".$artid;
        $sql .= " AND s = '".$s."'";
        $DB_site->query($sql);
    }
}

//Sélection détail de l'article
$sql = "SELECT *";
$sql .= " FROM article";
$sql .= " WHERE artid = '".$artid."'";
$article = $DB_site->query_first($sql);

//Consommation annuelle en KW
$article['conso_euro'] = round($article['conso_kw'] * $prixkw * 365);

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

$template->parts["logomarquedetail"] = "<img src=\"".$marque->logo(150,30)."\" alt=\"".$marq['type'.$template->langue]."\">" ;

//Mise en place fil d'ariane
if($art_actif[0]!=3){
    $template->parts["fil_ariane"].=<<<HTML
	    &gt; <span typeof="v:Breadcrumb">$menu[libelle]</span>
HTML;

    $fil_cats=array_reverse($fil_cats);
    foreach($fil_cats as $fil_cat){
	    $url=$fil_cat->url();
	    $template->parts["fil_ariane"].=<<<HTML
	    &gt; <span typeof="v:Breadcrumb"><a href="$url[url]" rel="v:url" property="v:title">$fil_cat->libelle</a></span>
HTML;
    }
}else{
    $marque->archive_load();
    $template->parts["fil_ariane"].="&gt; <span typeof=\"v:Breadcrumb\"><a href=\"/".$template->translate("/archives/url")."\" rel=\"v:url\" property=\"v:title\">".$template->translate("/archives/fil")."</a></span>
    &gt; <span typeof=\"v:Breadcrumb\"><a href=\"/".$marque->archive_url."\">".$marque->libelle."</a></span>";
}

$fillibelle=$article[$libelle]." ".ucfirst(strtolower($marq['libelle']));
$template->parts["fil_ariane"].=<<<HTML
	&gt; <span typeof="v:Breadcrumb">$fillibelle</span>
HTML;

$tauxtvaref = $template->tva_ref();
$txtva = (1 + $tauxtvaref[$article['tauxchoisi']] / 100);

/*$fillibelle=html_entity_decode($article[$libelle],ENT_COMPAT,"ISO-8859-15");
if(strlen($fillibelle)>100) $fillibelle=substr($fillibelle,0,100)."&hellip;";
if($art_actif[0]!=3)
    $template->parts["fil"]="<span typeof=\"v:Breadcrumb\"><a href=\"/\" rel=\"v:url\" property=\"v:title\">".$template->translate("/site/nom")."</a></span> &gt; <span typeof=\"v:Breadcrumb\"><a href=\"".$categorie->url()['url']."\" rel=\"v:url\" property=\"v:title\">".$categorie->libelle."</a></span> &gt; ".$fillibelle." ".ucfirst(strtolower($marq['libelle']));
else{
    $marque->archive_load();
    $template->parts["fil"]="<span typeof=\"v:Breadcrumb\"><a href=\"/\" rel=\"v:url\" property=\"v:title\">".$template->translate("/site/nom")."</a></span> &gt; <span typeof=\"v:Breadcrumb\"><a href=\"/".$template->translate("/archives/url")."\" rel=\"v:url\" property=\"v:title\">".$template->translate("/archives/fil")."</a></span> &gt; <span typeof=\"v:Breadcrumb\"><a href=\"/".$marque->archive_url."\">".$marque->libelle."</a></span> &gt; ".$fillibelle." ".ucfirst(strtolower($marq['libelle']));
}*/
    

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


if (isset($user_info['paysid'])) {
  $idPays=$user_info['paysid'];
} else {
  $idPays=$template->pays_ref();
}

$tabGaranties=DAOgarantie::loadMaxGarantieArtByPays($DB_site,$template->site_code(),'FR',$idPays,$article['artid']);
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
      <tr>
        <td><strong>$nomGarantie&nbsp;:</strong></td>
        <td>$libGarantie $typeGarantie</td>
      </tr>
HTML;

  $template->evaltemplate("InfoGarantieBtn","infogarantie");
}
  
//************************//
// DEBUT CARACTERISTIQUES //
//************************//
$oArticle = new produit($DB_site, $template->langue, $template->site_code(), $template->tva_ref(), $artid);

$tabCaracteristiques = $oArticle->getCaracteristiquesPrincipales($artid);
$caract_num = 1;

$ralcolor=new ral($DB_site, $artid);
$ralcolor->langue=$template->langue;
$ralcolor->select_function="article_ral_select";
//nice($tabCaracteristiques);
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
                          . "<p id=\"txt_options\" data-open=\"".$voir."\" data-close=\"".$masquer."\">".($caract_num <= 4 && !$reconstruction ? $masquer : $voir)." <i class=\"fa fa-chevron-".($caract_num <= 4 && !$reconstruction ? 'up' : 'down')." fa-lg\"></i></p>"
                      . "</div>"
                      . "<div class=\"centre_options\" ".($caract_num <= 4 && !$reconstruction ? 'style=\"display:block;\"' : '').">";

              $template->parts['liste_options_preview'] .= ""
                   . "<div class=\"optpreview\">"
                        . "<div class=\"opttitle\">".$la_caracteristique['libelle'.$template->langue]." :</div>"
                        . "<div class=\"optimg\" id=\"previewopt".$la_caracteristique['caractid']."\"><span style=\"color:#df4126;\">".$template->translate("/article/options/definir","F")."</span></div>"
                        . "<div class=\"clear\"></div>"
                   . "</div>";
          }

          //Récupération des différentes options de la caractéristique mère
          $tabOptions = array();
          if(!$ralcolor->is_ral_carac($la_caracteristique['caractid'])){
              $tabOptions = $oArticle->getOptions($artid, $la_caracteristique['caractid']); 
          }

          $taille_reduite = false;
          if(count($tabOptions) > 10){
               $taille_reduite = true;
          }

          if(!empty($tabOptions)){
              foreach($tabOptions as $l_option){ 
                   $affiche_visuel = false;
                   $affiche_zoom = false;
                   $preview_action = ''; 

                   $aDiffPrix = unserialize($l_option['differenceprix'.($sitepro ? 'ht' : '')]);
                   $l_option['differenceprix'] = $aDiffPrix[$template->site_code()];

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

                   //DEBUT ENCART OPTION
                   $template->parts['article_options_new'] .= ""
                        . "<a name=\"choix_options".$caract_num."\"></a>"
                        . "<input type=\"hidden\" name=\"incomp_opt_".$l_option['caractvalid']."\" class=\"input_hidden_option\" id=\"incomp_opt".$l_option['caractvalid']."\" value=\"\">"
                        . "<div class=\"encart_option\" ".($taille_reduite ? 'style=\"width: 50px\"' : '')." id=\"option".$l_option['caractvalid']."\" onclick=\"".($ralcolor->is_ral_option($l_option['caractvalid']) ? "ral_show('ral_panel_".$l_option['caractvalid']."',true,this);" : "checkOption(this, ".$article['artid'].", ".$l_option['caractid'].", ".$l_option['caractvalid'].", ".$caract_num."); ".$preview_action)."\">";

                   if(!$taille_reduite){
                        $template->parts['article_options_new'] .= "<div class=\"title\">".$l_option['libelle'.$template->langue]."</div>";
                   }

                   if($affiche_visuel){ 
                        $template->parts['article_options_new'] .= ""
                             . "<img src=\"/images/caracteristiquevaleur/".$l_option['caractvalid'].".jpg\" class=\"visuel_option\" alt=\"".$l_option['description'.$template->langue]."\">";
                   }

                   $template->parts['article_options_new'] .= "<div class=\"check\">
			      <input type=\"radio\" name=\"lst_".$la_caracteristique['caractid']."\" id=\"lst".($ralcolor->is_ral_option($l_option['caractvalid']) ? '_ral' : '')."_".$la_caracteristique['caractid']."_".$l_option['caractvalid']."\" value=\"".$l_option['caractvalid']."\" style=\"vertical-align: -2px; margin: 0 5px 0 0;\">"
                             . "<span class=\"prix\">".($l_option['differenceprix'] != 0 ? $template->price_format($l_option['differenceprix']) : $template->translate("/commande/offert","F"))."</span>
                             </div>";

                   
                    $template->parts['article_options_new'] .= "</div>";
		    
		    //DEBUT POPUP RAL
                   if($ralcolor->is_ral_option($l_option['caractvalid'])){
                      $template->parts['article_options_new'] .= "<div class=\"ral_panel\" id=\"ral_panel_".$l_option['caractvalid']."\">".$ralcolor->generate($la_caracteristique['caractid'])."</div>";
                   }
                   //FIN POPUP RAL

                   //FIN ENCART OPTION

                   //DEBUT POPUP ZOOM (Titre + Descriptif + Photo)
                   if($affiche_zoom || !empty($l_option['desc_caract_val'])){

                        $template->parts['article_options_new'] .= ""
                             . "<div class=\"visuel_zoom\">"
                                  . "<p id=\"info_disabled_".$l_option['caractvalid']."\" class=\"info_disabled\">Incompatible avec l'option<br><span id=\"lst_opt_dis_".$l_option['caractvalid']."\"></span></p>"
                                  . "<span class=\"titre\">".$l_option['description'.$template->langue]."</span><br>";

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

if($hasoption && $article['commandable']==1){
    $template->evaltemplate("ArticleOptionPreviewNew","optionspreviewnew");
    $template->evaltemplate("ArticleOptionNew","optionnew");

    //$template->evaltemplate("ArticleOption","options");
} 

//BLOC INSTALLATEUR
if($article['montage']==1) $template->evaltemplate("ArticleInstallateur","article_installateur");

if($article['commandable']==0 || $article['commandable']==2 || $art_actif[0]==3 || $article['date_appro']>time()){
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
    $template->parts["stock_libelle"]   = $template->translate("/article/stock/reappro_le")." ".date("d/m/Y",$d['date_appro']);
    //$template->parts["datelivraison"] = date("d/m/Y",$jourlivraison);
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
    
    $article['acclist']="<tr>
                           <td width=\"40%\" align=\"left\"><b>".$template->translate("/article/champs/accessoires_inclus","F")."&nbsp;: </b></td>
                           <td align=\"left\">".substr($acclist,4)."</td>
                       </tr>";
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
  /*$template->parts["criteo"]="<script type=\"text/javascript\" src=\"http://static.criteo.net/criteo_ld.js\"></script>
  <script type=\"text/javascript\">
  document.write('<div id=\\\\\"cto_se_7713654_ac\\\\\" style=\\\\\"display:none\\\\\">');
  document.write('<div class=\\\\\"ctoWidgetServer\\\\\">http:\/\/macaveavinfr.widget.criteo.com\/pmo\/<\/div>');
  document.write('<div class=\\\\\"ctoDataType\\\\\">sendEvent<\/div>');
  document.write('<div class=\\\\\"ctoParams\\\\\">wi=7713654&pt1=2&i=$artid<\/div>');
  document.write('<\/div>');
  </script>";*/
  
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

$article[finition]="";
if($finition[1]!=""){
    $template->parts["finition"]="<li><b>".$template->translate("/article/champs/niveau_gamme","F")." : </b>$finition[1]</li>";
    $article[finition]="<tr class=\"%class%\">
                           <td width=\"40%\" align=\"left\"><b>".$template->translate("/article/champs/niveau_gamme","F")." : </b></td>
                           <td align=\"left\">".$finition[1]."</td>
                       </tr>";
}

if ($article[nbr_temperature] != ""){
    $template->parts["nbtemperature"]="<li><b>".$template->translate("/article/champs/nombre_temperature","F")."&nbsp;: </b>".$template->translate($article[nbr_temperature])."</li>";
    $article[nbr_temperature] = "<tr class=\"%class%\">
                                    <td width=\"40%\" align=\"left\"><b>".$template->translate("/article/champs/nombre_temperature","F")."&nbsp;: </b></td>
                                    <td align=\"left\">".$template->translate($article[nbr_temperature])."</td>
                                </tr>";

   $nbcrit++;
}

if($article[plage_temperature]!=""){
    $template->parts["plagereglage"]="<li><b>".$template->translate("/article/champs/plage_reglage","F")." : </b>".$template->translate($article[plage_temperature])."</li>";
    $article[plage_reglage] = "<tr class=\"%class%\">
                                    <td width=\"40%\" align=\"left\"><b>".$template->translate("/article/champs/plage_reglage","F")." : </b></td>
                                    <td align=\"left\">".$template->translate($article[plage_temperature],"F")."</td>
                                </tr>";
}


if ($article[capacite_bouteilles] != ""){
    $template->parts["capacity"]="<li><b>".$template->translate("/article/champs/capacite_modele","F")." : </b>$article[capacite_bouteilles] ".$template->translate($article['capacite_unite'])."</li>";
    $article[capacite_bouteilles]="<tr class=\"%class%\">
                                    <td width=\"40%\" align=\"left\"><b>".$template->translate("/article/champs/capacite_modele","F")." : </b></td>
                                    <td align=\"left\">$article[capacite_bouteilles] ".$template->translate($article['capacite_unite'])."</td>
                                </tr>";
    $nbcrit++;
}

if($article[capacite_mini]!="" && $article[capacite_maxi]!=""){
    $template->parts["capaciteminimaxi"]="<li><b>".$template->translate("/article/champs/capacite_mini_maxi","F")." : </b>$article[capacite_mini] / $article[capacite_maxi] ".$template->translate($article['capacite_unite'])."</li>";
    $article['capaciteminimaxi']="<tr class=\"%class%\">
                                    <td width=\"40%\" align=\"left\"><b>".$template->translate("/article/champs/capacite_mini_maxi","F")." : </b></td>
                                    <td align=\"left\">$article[capacite_mini] / $article[capacite_maxi] ".$template->translate($article['capacite_unite'])."</td>
                                </tr>";
}

if ($article[capacite_l] != 0){
    $template->parts["capacity"]="<li><b>".$template->translate("/article/champs/capacite_modele","F")." : </b>$article[capacite_l] L</li>";
    $article[capacite_totale] = "<tr class=\"%class%\">
                                <td class=\"techcaract\">".$template->translate("/article/champs/capacite_modele","F")." : </td>
                                <td>$article[capacite_l] L</td>
                            </tr>";
    $nbcrit++;
}

if ($article[version_porte] != ""){
    $template->parts["version_porte"]="<li><b>".$template->translate("/article/champs/version_porte","F")." : </b>".$template->translate($article[version_porte])."</li>";
    $article[version_porte] = "<tr class=\"%class%\">
                                <td class=\"techcaract\">".$template->translate("/article/champs/version_porte","F")." : </td>
                                <td>".$template->translate($article[version_porte])."</td>
                            </tr>";
    $nbcrit++;
}

if ($article[coloris] != ""){
    $template->parts["coloris"]="<li><b>".$template->translate("/article/champs/coloris_modele","F")." : </b>".$template->translate($article[coloris])."</li>";
    $article[coloris] = "<tr class=\"%class%\">
                            <td class=\"techcaract\">".$template->translate("/article/champs/coloris_modele","F")." : </td>
                            <td>".$template->translate($article[coloris])."</td>
                        </tr>";
    $nbcrit++;
}

if ($article[coloris_porte] != ""){
    $template->parts["coloris_porte"]="<li><b>".$template->translate("/article/champs/coloris_cadre","F")." : </b>".$template->translate($article[coloris_porte])."</li>";
    $article[coloris_porte] = "<tr class=\"%class%\">
                                <td class=\"techcaract\">".$template->translate("/article/champs/coloris_cadre","F")." : </td>
                                <td>".$template->translate($article[coloris_porte])."</td>
                            </tr>";
    $nbcrit++;
}

if ($article[matiere] != ""){
    $article[matiere] = "<tr class=\"%class%\">
                                <td class=\"techcaract\">".$template->translate("/article/champs/matiere","F")." : </td>
                                <td>".$template->translate($article[matiere])."</td>
                            </tr>";
    $nbcrit++;
}

/*
if ($article[rangements_txt] != ""){
    $template->parts["rangements"]="<li><b>".$template->translate("/article/champs/accessoires_inclus","F")." : </b>$article[rangements_txt]</li>";
    $article[rangements_txt] = "<tr>
                                    <td class=\"techcaract\">".$template->translate("/article/champs/accessoires_inclus","F")." : </td>
                                    <td>$article[rangements_txt]</td>
                                </tr>";
    $nbcrit++;
}*/

/*if ($article[garantie_incluse] != ""){
    $template->parts["garantie_incluse"]="<li><b>".$template->translate("/article/champs/garantie_constructeur","F")." : </b>$article[garantie_incluse]</li>";
    $article[garantie_incluse] = "<tr>
                                    <td class=\"techcaract\">".$template->translate("/article/champs/garantie_constructeur","F")." : </td>
                                    <td>$article[garantie_incluse]</td>
                                </tr>";
    $nbcrit++;
}*/

if ($article[temperature_cave] != ""){
    $template->parts["temperature_cave"]="<li><b>".$template->translate("/article/champs/temperature_installation","F")." : </b>".$template->translate($article[temperature_cave])."</li>";
    $article[temperature_cave] = "<tr class=\"%class%\">
                                     <td class=\"techcaract\">".$template->translate("/article/champs/temperature_installation","F")." : </td>
                                    <td>".$template->translate($article[temperature_cave])."</td>
                                </tr>";
    $nbcrit++;
}

if ($article[fonction_hiver] != ""){
    $template->parts["hiver"]="<li><b>".$template->translate("/article/champs/fonction_hiver","F")." : </b>".$template->translate($article[fonction_hiver])."</li>";
    $article[fonction_hiver] = "<tr class=\"%class%\">
                                <td class=\"techcaract\">".$template->translate("/article/champs/fonction_hiver","F")." : </td>
                                <td>".$template->translate($article[fonction_hiver])."</td>
                            </tr>";
    $nbcrit++;
}

if ($article[regulation] != ""){
    $article[regulation] = "<tr class=\"%class%\">
                                <td class=\"techcaract\">".$template->translate("/article/champs/regulation","F")." : </td>
                                <td>".$template->translate($article[regulation])."</td>
                            </tr>";
    $nbcrit++;
}

if ($article[position_regulateur] != ""){
    $article[position_regulateur] = "<tr class=\"%class%\">
                                <td class=\"techcaract\">".$template->translate("/article/champs/position_regulateur","F")." : </td>
                                <td>".$template->translate($article[position_regulateur])."</td>
                            </tr>";
    $nbcrit++;
}

if ($article[temperature] != ""){
    $article[temperature] = "<tr class=\"%class%\">
                                <td class=\"techcaract\">".$template->translate("/article/champs/temperature","F")." : </td>
                                <td>".$template->translate($article[temperature])."</td>
                            </tr>";
    $nbcrit++;
}

/*if ($article[boutons_reglage] != ""){
    $article[boutons_reglage] = "<tr>
                                    <td class=\"techcaract\">".$template->translate("/article/champs/commande_chaud_froid","F")." : </td>
                                    <td>".$template->translate($article[boutons_reglage])."</td>
                                </tr>";
    $nbcrit++;
}*/

if ($article[reglage] != ""){
    $article[reglage] = "<tr class=\"%class%\">
                            <td class=\"techcaract\">".$template->translate("/article/champs/reglage","F")." : </td>
                            <td>".$template->translate($article[reglage])."</td>
                        </tr>";
    $nbcrit++;
}

if ($article[affichage] != ""){
    $article[affichage] = "<tr class=\"%class%\">
                            <td class=\"techcaract\">".$template->translate("/article/champs/affichage","F")." : </td>
                            <td>".$template->translate($article[affichage])."</td>
                        </tr>";
    $nbcrit++;
}

if ($article[hygrometrie] != ""){
    $article[hygrometrie] = "<tr class=\"%class%\">
                                <td class=\"techcaract\">".$template->translate("/article/champs/hygrometrie","F")." : </td>
                                <td>".$template->translate($article[hygrometrie])."</td>
                            </tr>";
    $nbcrit++;
}

if ($article[hygrometre] != ""){
    $article[hygrometre] = "<tr class=\"%class%\">
                                <td class=\"techcaract\">".$template->translate("/article/champs/hygrometre","F")." : </td>
                                <td>".$template->translate($article[hygrometre])."</td>
                            </tr>";
    $nbcrit++;
}

if ($article[pieds_reglables] != ""){
    $article[pieds_reglables] = "<tr class=\"%class%\">
                                    <td class=\"techcaract\">".$template->translate("/article/champs/pied_sur_verin","F")." : </td>
                                    <td>".$template->translate($article[pieds_reglables])."</td>
                                </tr>";
    $nbcrit++;
}

if ($article[serrure] != ""){
    $article[serrure] = "<tr class=\"%class%\">
                            <td class=\"techcaract\">".$template->translate("/article/champs/serrure","F")." : </td>
                            <td>".$template->translate($article[serrure])."</td>
                        </tr>";
    $nbcrit++;
}

if ($article[poignee] != ""){
    $article[poignee] = "<tr class=\"%class%\">
                            <td class=\"techcaract\">".$template->translate("/article/champs/poignee","F")." : </td>
                            <td>".$template->translate($article[poignee])."</td>
                        </tr>";
    $nbcrit++;
}

if ($article[modularite_interieure] != ""){
    $article[modularite_interieure] = "<tr class=\"%class%\">
                                        <td class=\"techcaract\">".$template->translate("/article/champs/modularite_interieur","F")." : </td>
                                        <td>".$template->translate($article[modularite_interieure])."</td>
                                    </tr>";
    $nbcrit++;
}

if ($article[roulettes] != ""){
    $article[roulettes] = "<tr class=\"%class%\">
                            <td class=\"techcaract\">".$template->translate("/article/champs/roulettes","F")." : </td>
                            <td>".$template->translate($article[roulettes])."</td>
                        </tr>";
    $nbcrit++;
}

if ($article[alarme] != ""){
    $article[alarme] = "<tr class=\"%class%\">
                            <td class=\"techcaract\">".$template->translate("/article/champs/alarme","F")." : </td>
                            <td>".$template->translate($article[alarme],"F")."</td>
                        </tr>";
    $nbcrit++;
}

if ($article[eclairage] != ""){
    $article[eclairage] = "<tr class=\"%class%\">
                            <td class=\"techcaract\">".$template->translate("/article/champs/eclairage","F")." : </td>
                            <td>".$template->translate($article[eclairage])."</td>
                        </tr>";
    $nbcrit++;
}

if ($article[interrupteur] != ""){
    $article[interrupteur] = "<tr class=\"%class%\">
                                <td class=\"techcaract\">".$template->translate("/article/champs/interrupteur_marche_arret","F")." : </td>
                                <td>".$template->translate($article[interrupteur])."</td>
                            </tr>";
    $nbcrit++;
}

if ($article[bouton_test] != ""){
    $article[bouton_test] = "<tr class=\"%class%\">
                                <td class=\"techcaract\">".$template->translate("/article/champs/bouton_test","F")." : </td>
                                <td>".$template->translate($article[bouton_test])."</td>
                            </tr>";
    $nbcrit++;
}

if ($article[vide_poche] != ""){
    $article[vide_poche] = "<tr class=\"%class%\">
                                <td class=\"techcaract\">".$template->translate("/article/champs/vide_poche","F")." : </td>
                                <td>".$template->translate($article[vide_poche])."</td>
                            </tr>";
    $nbcrit++;
}

if ($article[clayette_fil] != ""){
    $article[clayette_fil] = "<tr class=\"%class%\">
                                <td class=\"techcaract\">".$template->translate("/article/champs/clayette_fil","F")." : </td>
                                <td>".$template->translate($article[clayette_fil])."</td>
                            </tr>";
    $nbcrit++;
}

/*if ($article[panier_transport] != ""){
    $article[panier_transport] = "<tr>
                                    <td class=\"techcaract\">".$template->translate("/article/champs/panier_transport","F")." : </td>
                                    <td$article[panier_transport]</td>
                                </tr>";
    $nbcrit++;
}*/

/*if ($article[vinokit] != ""){
    $article[vinokit] = "<tr>
                            <td class=\"techcaract\">Vinokit : </td>
                            <td>$article[vinokit]</td>
                        </tr>";
    $nbcrit++;
}*/

/*if ($article[tablette] != ""){
    $article[tablette] = "<tr>
                            <td class=\"techcaract\">Tablette sous clayette centrale : </td>
                            <td>$article[tablette]</td>
                        </tr>";
    $nbcrit++;
}*/

/*if ($article[clayette_hetre] != ""){
    $article[clayette_hetre] = "<tr>
                                <td class=\"techcaract\">Clayette en hêtre en demi profondeur : </td>
                                <td>$article[clayette_hetre]</td>
                            </tr>";
    $nbcrit++;
}*/

/*if ($article[tapis_basse] != ""){
    $article[tapis_basse] = "<tr>
                                <td class=\"techcaract\">Tapis en partie basse : </td>
                                <td>$article[tapis_basse]</td>
                            </tr>";
    $nbcrit++;
}*/

if ($article[production_froid] != ""){
    $article[production_froid] = "<tr class=\"%class%\">
                                    <td class=\"techcaract\">".$template->translate("/article/champs/production_froid","F")." : </td>
                                    <td>".$template->translate($article[production_froid])."</td>
                                </tr>";
    $nbcrit++;
}

if ($article[production_chaud] != ""){
    $article[production_chaud] = "<tr class=\"%class%\">
                                    <td class=\"techcaract\">".$template->translate("/article/champs/production_chaud","F")." : </td>
                                    <td>".$template->translate($article[production_chaud])."</td>
                                </tr>";
    $nbcrit++;
}

if ($article[declenchement_chaud] != ""){
    $article[declenchement_chaud] = "<tr class=\"%class%\">
                                        <td class=\"techcaract\">".$template->translate("/article/champs/declenchement_chaud","F")."&nbsp;: </td>
                                        <td>".$template->translate($article[declenchement_chaud])."</td>
                                    </tr>";
    $nbcrit++;
}



if ($article[fonction_hiver_techno] != ""){
    $fonction_hiver_techno = "<tr class=\"%class%\">
                                        <td class=\"techcaract\">".$template->translate("/article/champs/fonction_hiver","F")." : </td>
                                        <td>".$template->translate($article[fonction_hiver_techno])."</td>
                                    </tr>";

    $article[fonction_hiver_techno] = "<tr class=\"%class%\">
                                        <td class=\"techcaract\">".$template->translate("/article/champs/fonction_hiver","F")." : </td>
                                        <td>".$template->translate($article[fonction_hiver_techno])."</td>
                                    </tr>";
    $nbcrit++;
}

if ($article[parois_laterales] != ""){
    $article[parois_laterales] = "<tr class=\"%class%\">
                                    <td class=\"techcaract\">".$template->translate("/article/champs/nature_parois_laterales","F")."&nbsp;: </td>
                                    <td>".$template->translate($article[parois_laterales])."</td>
                                </tr>";
    $nbcrit++;
}

if ($article[parois_fond] != ""){
    $article[parois_fond] = "<tr class=\"%class%\">
                                <td class=\"techcaract\">".$template->translate("/article/champs/nature_paroi_fond","F")."&nbsp;: </td>
                                <td>".$template->translate($article[parois_fond])."</td>
                            </tr>";
    $nbcrit++;
}

if ($article[revetement_exterieur] != ""){
    $article[revetement_exterieur] = "<tr class=\"%class%\">
                                        <td class=\"techcaract\">".$template->translate("/article/champs/nature_revetement_exterieur","F")." : </td>
                                        <td>".$template->translate($article[revetement_exterieur])."</td>
                                    </tr>";
    $nbcrit++;
}

if ($article[isolation] != "")
    $article[isolation] = "<tr class=\"%class%\">
                                <td class=\"techcaract\">".$template->translate("/article/champs/nature_isolation","F")." : </td>
                                <td>".$template->translate($article[isolation])."</td>
                            </tr>";
if ($article[gaz] != "")
    $article[gaz] = "<tr class=\"%class%\">
                                <td class=\"techcaract\">".$template->translate("/article/champs/type_gaz","F")." : </td>
                                <td>".$template->translate($article[gaz])."</td>
                            </tr>";
                            
if ($article[puissance] != 0)
    $article[puissance] = "<tr class=\"%class%\">
                                <td class=\"techcaract\">".$template->translate("/article/champs/puissance_absorbee","F")." : </td>
                                <td>".$article[puissance]." W</td>
                            </tr>";
else
    $article[puissance]="";

if ($article[puissance_electrique] != "")
    $article[puissance_electrique] = "<tr class=\"%class%\">
                                        <td class=\"techcaract\">".$template->translate("/article/champs/puissance_electrique","F")." : </td>
                                        <td>$article[puissance_electrique]</td>
                                    </tr>";

if ($article[intensite_electrique] != "")
    $article[intensite_electrique] = "<tr class=\"%class%\">
                                        <td class=\"techcaract\">".$template->translate("/article/champs/intensite_electrique","F")." : </td>
                                        <td>$article[intensite_electrique]</td>
                                    </tr>";
                                    
if ($article[tension_electrique] != "")
    $article[tension_electrique] = "<tr class=\"%class%\">
                                        <td class=\"techcaract\">".$template->translate("/article/champs/tension_electrique","F")." : </td>
                                        <td>".$template->translate($article[tension_electrique],"F")."</td>
                                    </tr>";
                                    
$article[consommation_h]="";
$article[consommation_an]="";
if ($article['conso_kw']!="" && $article[conso_kw]!="0"){
    $article[consommation_h] = "<tr class=\"%class%\">
                                    <td class=\"techcaract\">".$template->translate("/article/champs/consommation_24h","F")." : </td>
                                    <td>".round($article[conso_kw],1)."</td>
                                </tr>";

    $article[consommation_an] = "<tr class=\"%class%\">
                                    <td class=\"techcaract\">".$template->translate("/article/champs/consommation_an","F")." : </td>
                                    <td>".round(floatval($article['conso_kw'])*365)."</td>
                                </tr>";
}

if ($article[niveau_bruit] != "")
    $article[niveau_bruit] = "<tr class=\"%class%\">
                                <td class=\"techcaract\">".$template->translate("/article/champs/niveau_bruit","F")." : </td>
                                <td>$article[niveau_bruit]</td>
                            </tr>";

$v=floatval($article[emballage_hauteur])*floatval($article[emballage_largeur])*floatval($article[emballage_profondeur]);
if ($article[emballage_hauteur] != "" && $article[emballage_largeur] != "" && $article[emballage_profondeur] != "")
    $article[emballage] = "<tr class=\"%class%\">
        <td class=\"techcaract\">".$template->translate("/article/champs/dimensions_emballe","F")." : </td>
        <td>".sprintf($template->translate("/article/champs/dimensions_format"),$article[emballage_hauteur],$article[emballage_largeur],$article[emballage_profondeur])."</td>
                           </tr>
                           <tr class=\"%class2%\">
        <td class=\"techcaract\">".$template->translate("/article/champs/poids_brut","F")." : </td>
        <td>$article[emballage_poids] kg</td>
          </tr>";


if($v>0) $volume="<tr class=\"%class%\">
         <td class=\"techcaract\">".$template->translate("/article/champs/volume","F")." : </td>
                     <td>".round($v/pow(10,9),2)." m<sup>3</sup></td>
      </tr>";
      
 if($article['hauteur_encastrement']<>0 && $article['largeur_encastrement']<>0 && $article['profondeur_encastrement']<>0)
    $article['dimensions_encastrement']="<tr class=\"%class%\">
            <td class=\"techcaract\">".$template->translate("/article/champs/dimensions_encastrement","F")."&nbsp;:</td>
            <td>".$article['hauteur_encastrement']." * ".$article['largeur_encastrement']." * ".$article['profondeur_encastrement']." mm</td>
          </tr>";
          
 if($article['hauteur_groupe']<>0 && $article['largeur_groupe']<>0 && $article['profondeur_groupe']<>0)
    $article['dimensions_groupe']="<tr class=\"%class%\">
            <td class=\"techcaract\">".$template->translate("/article/champs/dimensions_groupe","F")."&nbsp;:</td>
            <td>".$article['hauteur_groupe']." * ".$article['largeur_groupe']." * ".$article['profondeur_groupe']." mm / ".round($article['poids_net_groupe']/1000,1)." kg</td>
          </tr>";
          
 if($article['hauteur_evaporateur']<>0 && $article['largeur_evaporateur']<>0 && $article['profondeur_evaporateur']<>0)
    $article['dimensions_evaporateur']="<tr class=\"%class%\">
            <td class=\"techcaract\">".$template->translate("/article/champs/dimensions_evaporateur","F")."&nbsp;:</td>
            <td>".$article['hauteur_evaporateur']." * ".$article['largeur_evaporateur']." * ".$article['profondeur_evaporateur']." mm / ".round($article['poids_net_evaporateur']/1000,1)." kg</td>
          </tr>";
          

 if($article['hauteur_regulateur']<>0 && $article['largeur_regulateur']<>0 && $article['profondeur_regulateur']<>0)
    $article['dimensions_regulateur']="<tr class=\"%class%\">
            <td class=\"techcaract\">".$template->translate("/article/champs/dimensions_regulateur","F")."&nbsp;:</td>
            <td>".$article['hauteur_regulateur']." * ".$article['largeur_regulateur']." * ".$article['profondeur_regulateur']." mm / ".round($article['poids_net_regulateur']/1000,1)." kg</td>
          </tr>";
          
if($article['distance_plafond']<>0)
    $article['distance_plafond']="<tr class=\"%class%\">
            <td class=\"techcaract\">".$template->translate("/article/champs/hauteur_minimum_encastrement","F")." :</td>
            <td>".$article['distance_plafond']." mm</td>
        </tr>";
else
    $article['distance_plafond']="";
    
    
if($article['epaisseur_mur_min']<>0 && $article['epaisseur_mur_max']<>0)
    $article['epaisseur_mur']="<tr class=\"%class%\">
            <td class=\"techcaract\">".$template->translate("/article/champs/epaisseur_mur","F")." :</td>
            <td>".$article['epaisseur_mur_min']." / ".$article['epaisseur_mur_max']." mm</td>
        </tr>";
    
if($article['infos_encastrement']!="")
    $article['infos_encastrement']="<tr class=\"%class%\">
            <td class=\"techcaract\">".$template->translate("/article/champs/infos_encastrement","F")." :</td>
            <td>".$template->translate($article['infos_encastrement'],"F")."</td>
        </tr>";

        
if ($article[thermometre] != "")
    $article[thermometre] = "<tr class=\"%class%\">
                                <td class=\"techcaract\">".$template->translate("/article/champs/thermometre","F")." : </td>
                                <td>".$template->translate($article[thermometre])."</td>
                            </tr>";

if ($article[filtre_charbon] != "")
    $article[filtre_charbon] = "<tr class=\"%class%\">
                                <td class=\"techcaract\">".$template->translate("/article/champs/filtre_charbon","F")." : </td>
                                <td>".$template->translate($article[filtre_charbon])."</td>
                            </tr>";

if ($article[absence_vibration] != "")
    $article[absence_vibration] = "<tr class=\"%class%\">
                                        <td class=\"techcaract\">".$template->translate("/article/champs/absence_vibration","F")." : </td>
                                        <td>".$template->translate($article[absence_vibration],"F")."</td>
                                    </tr>";

if ($article[dimensions] != ""){
    $template->parts["dimensions"]="<li><b>".$template->translate("/article/champs/dimensions","F")." : </b>".sprintf($template->translate("/article/champs/dimensions_format"),$article[hauteur],$article[largeur],$article[profondeur])."</li>";
    $article[dimensions] = "<tr class=\"%class%\">
                                <td class=\"techcaract\">".$template->translate("/article/champs/dim_produit","F")." : </td>
                                <td>".sprintf($template->translate("/article/champs/dimensions_format"),$article[hauteur],$article[largeur],$article[profondeur])."</td>
                            </tr>
                            <tr class=\"%class2%\">
        <td class=\"techcaract\">".$template->translate("/article/champs/poids_net","F")." :</td>
        <td>".$article['poid']." $poidsunit</td>
          </tr>";
}

if ($article[volume_interieur] != 0)
    $article[volume_interieur] = "<tr class=\"%class%\">
                                        <td class=\"techcaract\">".$template->translate("/article/champs/volume_interieur","F")." : </td>
                                        <td>".$template->translate($article[volume_interieur])." cm<sup>3</sup></td>
                                    </tr>";
else
    $article[volume_interieur]="";
                                    
if ($article[conso_kw] != "" && $article[conso_euro]!=""){
    $template->parts["conso_kw"]="<li><b>".$template->translate("/article/champs/consommation","F")." : </b>".round($article[conso_kw],1)." kW/24h ".$template->translate("/article/champs/soit_env")." $article[conso_euro] &euro; ".$template->translate("/site/ttc").$template->translate("/article/champs/par_an")."</li>";
    $article[conso_kw] = "<tr class=\"%class%\">
                            <td class=\"techcaract\">".$template->translate("/article/champs/consommation","F")." : </td>
                            <td>".round($article[conso_kw],1)." kW/24h ".$template->translate("/article/champs/soit_env")." $article[conso_euro] &euro; ".$template->translate("/site/ttc").$template->translate("/article/champs/par_an")."</td>
                        </tr>";
}else
    $article[conso_kw]="";

//Caractéristique "label energétique"
if($article['energy_label'] != ""){
    
    $label_energetique = str_replace('+','plus',strtolower($article['energy_label']));

    //$template->parts["energy_label"] = "<p class=\"telechargement\"><i class=\"fa fa-chevron-right\"></i><a href=\"javascript:void(0);\" onclick=\"tb_show('Etiquette-énergie','/fiche_energetique.php?artid=".$artid."',false)\">Télécharger l'étiquette-énergie</a></p>";
    $template->parts["energy_label"] = "<p class=\"telechargement\"><i class=\"fa fa-chevron-right\"></i><a href=\"/images/mail/fiche_energetique.php?artid=".$artid."\" target=\"_blank\">".$template->translate("/article/champs/telecharger_etiquette","F")."</a></p>";

    if(substr($article['energy_label'],0,1)=="A" && file_exists("images/templates/energie_".$label_energetique.".jpg")){
        $iconenergie = "<img class=\"picto\" alt=\"".$template->translate("/article/champs/classe_energetique","F")." ".$label_energetique."\" src=\"/images/templates/energie_".$label_energetique.".jpg\">";
    }

    /*<li><b>".$template->translate("/article/champs/classe_energetique","F")." : </b>".$article['energy_label']." (<a href=\"javascript:void(0);\" onclick=\"tb_show('Etiquette-énergie','/fiche_energetique.php?artid=".$article['artid']."',false);\">Etiquette-énergie</a>)</li>";*/
    
    $article['energy_label']="<tr class=\"%class%\">
            <td class=\"techcaract\">".$template->translate("/article/champs/classe_energetique","F")." : </td>
            <td>".$article['energy_label']." (<a href=\"/images/mail/fiche_energetique.php?artid=".$article['artid']."\">".$template->translate("/article/champs/telecharger_etiquette","F")."</a>)</td>
    </tr>";
}

//Fiche technique (A mettre en place plus tard quand elle existera)
//$template->parts["fiche_technique"] = "<p class=\"telechargement\"><i class=\"fa fa-chevron-right\"></i><a href=\"\">Télécharger la fiche technique</a></p>";
//
if($article['classe_clima']!=""){
    $template->parts["classe_clima"]="<li><b>".$template->translate("/article/champs/classe_climatique","F")." : </b>".$article['classe_clima']."</li>";
    $article['classe_clima']="<tr class=\"%class%\">
            <td class=\"techcaract\">".$template->translate("/article/champs/classe_climatique","F")." : </td>
            <td>".$article['classe_clima']."</td>
            </tr>";
}

if($article['systeme_humidification']!=""){
    $template->parts["systeme_humidification"]="<li><b>".$template->translate("/article/champs/systeme_humidification","F")." : </b>".$article['systeme_humidification']."</li>";
    $article['systeme_humidification']="<tr class=\"%class%\">
            <td class=\"techcaract\">".$template->translate("/article/champs/systeme_humidification","F")." : </td>
            <td>".$template->translate($article['systeme_humidification'])."</td>
            </tr>";
}

if($article['regulation_humidification']!=""){
    $template->parts["regulation_humidification"]="<li><b>".$template->translate("/article/champs/regulation_humidification","F")." : </b>".$article['regulation_humidification']."</li>";
    $article['regulation_humidification']="<tr class=\"%class%\">
            <td class=\"techcaract\">".$template->translate("/article/champs/regulation_humidification","F")." : </td>
            <td>".$template->translate($article['regulation_humidification'])."</td>
            </tr>";
}

if($article['etiquette_reperage']!=""){
    $template->parts["etiquette_reperage"]="<li><b>".$template->translate("/article/champs/etiquette_reperage","F")." : </b>".$article['etiquette_reperage']."</li>";
    $article['etiquette_reperage']="<tr class=\"%class%\">
            <td class=\"techcaract\">".$template->translate("/article/champs/etiquette_reperage","F")." : </td>
            <td>".$template->translate($article['etiquette_reperage'])."</td>
            </tr>";
}


if($article['type_climatiseur']!="")
    $article['type_climatiseur']="<tr class=\"%class%\">
            <td class=\"techcaract\">".$template->translate("/article/champs/type_climatiseur","F")." : </td>
            <td>".$template->translate($article['type_climatiseur'])."</td>
            </tr>";

            
if($article['filtre_poussiere']!="")
    $article['filtre_poussiere']="<tr class=\"%class%\">
            <td class=\"techcaract\">".$template->translate("/article/champs/filtre_poussiere","F")." : </td>
            <td>".$template->translate($article['filtre_poussiere'])."</td>
            </tr>";
            
if($article['distance_unites']<>0)
    $article['distance_unites']="<tr class=\"%class%\">
            <td class=\"techcaract\">".$template->translate("/article/champs/distance_unites","F")." : </td>
            <td>".$template->translate($article['distance_unites'])." m</td>
            </tr>";
else
   $article['distance_unites']="";
   
if($article['longueur_gaine']<>0)
    $article['longueur_gaine']="<tr class=\"%class%\">
            <td class=\"techcaract\">".$template->translate("/article/champs/longueur_gaine","F")." : </td>
            <td>".$template->translate($article['longueur_gaine'])." m</td>
            </tr>";
else
   $article['longueur_gaine']="";
   
if($article['puissance_frigo']<>0)
    $article['puissance_frigo']="<tr class=\"%class%\">
            <td class=\"techcaract\">".$template->translate("/article/champs/puissance_frigo","F")." : </td>
            <td>".$article['puissance_frigo']." W</td>
            </tr>";
else
   $article['puissance_frigo']="";
   
   
if($article['niveau_sonore_1m']<>0)
    $article['niveau_sonore_1m']="<tr class=\"%class%\">
            <td class=\"techcaract\">".$template->translate("/article/champs/niveau_sonore_1m","F")." : </td>
            <td>".$article['niveau_sonore_1m']." dB</td>
            </tr>";
else
   $article['niveau_sonore_1m']="";
   
  
if($article['niveau_sonore_3m']<>0)
    $article['niveau_sonore_3m']="<tr class=\"%class%\">
            <td class=\"techcaract\">".$template->translate("/article/champs/niveau_sonore_3m","F")." : </td>
            <td>".$article['niveau_sonore_3m']." dB</td>
            </tr>";
else
   $article['niveau_sonore_3m']="";
   
   
if($article['niveau_sonore_5m']<>0)
    $article['niveau_sonore_5m']="<tr class=\"%class%\">
            <td class=\"techcaract\">".$template->translate("/article/champs/niveau_sonore_5m","F")." : </td>
            <td>".$article['niveau_sonore_5m']." dB</td>
            </tr>";
else
   $article['niveau_sonore_5m']="";
   
if($article['kit_sonore']!="")
    $article['kit_sonore']="<tr class=\"%class%\">
            <td class=\"techcaract\">".$template->translate("/article/champs/kit_bas_niveau_sonore","F")." : </td>
            <td>".$template->translate($article['kit_sonore'],"F")."</td>
            </tr>";

if($article['debit_air_ext']<>0)
    $article['debit_air_ext']="<tr class=\"%class%\">
            <td class=\"techcaract\">".$template->translate("/article/champs/debit_air_ext","F")." : </td>
            <td>".$article['debit_air_ext']." m<sup>3</sup>/h</td>
            </tr>";
else
   $article['debit_air_ext']="";
   
   
if($article['coloris_groupe']!="")
    $article['coloris_groupe']="<tr class=\"%class%\">
            <td class=\"techcaract\">".$template->translate("/article/champs/coloris_groupe","F")." : </td>
            <td>".$template->translate($article['coloris_groupe'],"F")."</td>
            </tr>";
         
if($article['nb_ventilateur']<>0)
    $article['nb_ventilateur']="<tr class=\"%class%\">
            <td class=\"techcaract\">".$template->translate("/article/champs/nb_ventilateur","F")." : </td>
            <td>".$article['nb_ventilateur']."</td>
            </tr>";
else
   $article['nb_ventilateur']="";
   
   
if($article['debit_air_int']<>0)
    $article['debit_air_int']="<tr class=\"%class%\">
            <td class=\"techcaract\">".$template->translate("/article/champs/debit_air_int","F")." : </td>
            <td>".$article['debit_air_int']." m<sup>3</sup>/h</td>
            </tr>";
else
   $article['debit_air_int']="";
   
   
if($article['puissance_chauffage']<>0)
    $article['puissance_chauffage']="<tr class=\"%class%\">
            <td class=\"techcaract\">".$template->translate("/article/champs/puissance_chauffage","F")." : </td>
            <td>".$article['puissance_chauffage']." W</td>
            </tr>";
else
   $article['puissance_chauffage']="";
   
   
if($article['kit_humidificateur']<>"")
    $article['kit_humidificateur']="<tr class=\"%class%\">
            <td class=\"techcaract\">".$template->translate("/article/champs/kit_humidificateur","F")." : </td>
            <td>".$template->translate($article['kit_humidificateur'],"F")."</td>
            </tr>";
             
if($article['evacuation_eau']<>"")
    $article['evacuation_eau']="<tr class=\"%class%\">
            <td class=\"techcaract\">".$template->translate("/article/champs/evacuation_eau","F")." : </td>
            <td>".$template->translate($article['evacuation_eau'],"F")."</td>
            </tr>";
            
if($article['coloris_evaporateur']<>"")
    $article['coloris_evaporateur']="<tr class=\"%class%\">
            <td class=\"techcaract\">".$template->translate("/article/champs/coloris_evaporateur","F")." : </td>
            <td>".$template->translate($article['coloris_evaporateur'],"F")."</td>
            </tr>";

//**********************************//
// DEBUT CARACTERISTIQUES GENERALES //
//**********************************//
$aArticles = array(
    $article['capacite_bouteilles'],
    $article['capaciteminimaxi'],
    $article['finition'],
    $article['version_porte'],
    $article['type_climatiseur'],
    $article['filtre_poussiere'],
    $article['distance_unites'],
    $article['longueur_gaine'],
    $article['coloris'],
    $article['coloris_porte'],
    $article['matiere'],
    $article['temperature_cave'],
    $article['energy_label'],
    $article['classe_clima'],
    $garantie_incluse
);

$template->parts["general"] = ""
. "<p class=\"techtitle\">".$template->translate("/article/champs/caracteristiques_generales","F")." :</p>"
. "<div class=\"clear\"></div>"
. "<table>"; 

$class = 'fond_gris';
foreach($aArticles as $la_carac){
    if($la_carac != ''){
        $class = $class == '' ? 'fond_gris' : '';
        $template->parts["general"] .= str_replace('%class%', $class, $la_carac);
    }
}

$template->parts["general"] .= "</table>";
//********************************//
// FIN CARACTERISTIQUES GENERALES //
//********************************//

//******************//
// DEBUT REGULATION //
//******************//
if($article['regulation']!="" || $article['temperature']!="" || $article['thermometre']!="" || $article['boutons_reglage']!="" || $article['reglage']!="" || $article['affichage']!=""){

    $aArticles = array(
        $article['nbr_temperature'],
        $article['plage_reglage'],
        $article['regulation'],
        $article['temperature'],
        $article['thermometre'],
        $article['reglage'],
        $article['affichage'],
        $article['position_regulateur']
    );

    $template->parts["regulation"] = ""
    . "<p class=\"techtitle\">".ucfirst($template->translate("/article/champs/regulation","F"))." :</p>"
    . "<div class=\"clear\"></div>"
    . "<table>"; 

    $class = 'fond_gris';
    foreach($aArticles as $la_carac){
        if($la_carac != ''){
            $class = $class == '' ? 'fond_gris' : '';
            $template->parts["regulation"] .= str_replace('%class%', $class, $la_carac);
        }
    }

    $template->parts["regulation"] .= "</table>";
}
//****************//
// FIN REGULATION //
//****************//

//*******************//
// DEBUT EQUIPEMENTS //
//*******************//
if($article['hygrometrie']!="" || $article['hygrometre']!="" || $article['pieds_reglables']!="" || $article['serrure']!="" || $article['poignee']!="" || $article['modularite_interieure']!="" || $article['roulettes']!="" || $article['eclairage']!="" || $article['interrupteur']!="" || $article['bouton_test']!="" || $article['vide_poche']!="" || $article['clayette_fil']!="" || $article['panier_transport']!="" || $article['vinokit']!="" || $article['tablette']!="" || $article['clayette_hetre']!="" || $article['tapis_basse']!=""){

    $aArticles = array(
        $article['hygrometrie'],
        $article['hygrometre'],
        $article['filtre_charbon'],
        $article['pieds_reglables'],
        $article['serrure'],
        $article['poignee'],
        $article['modularite_interieure'],
        $article['roulettes'],
        $article['alarme'],
        $article['eclairage'],
        $article['interrupteur'],
        $article['bouton_test'],
        $article['vide_poche'],
        $article['clayette_fil'],
        $article['panier_transport'],
        $article['vinokit'],
        $article['tablette'],
        $article['clayette_hetre'],
        $article['tapis_basse'],
        $article['etiquette_reperage'],
        $article['systeme_humidification'],
        $article['regulation_humidification'],
        $article['acclist']
    );

    $template->parts["equipement"] = ""
    . "<p class=\"techtitle\">".ucfirst($template->translate("/article/champs/equipements","F"))." :</p>"
    . "<div class=\"clear\"></div>"
    . "<table>"; 

    $class = 'fond_gris';
    foreach($aArticles as $la_carac){
        if($la_carac != ''){
            $class = $class == '' ? 'fond_gris' : '';
            $template->parts["equipement"] .= str_replace('%class%', $class, $la_carac);
        }
    }

    $template->parts["equipement"] .= "</table>";
}
//*****************//
// FIN EQUIPEMENTS //
//*****************//

//*********************//
// DEBUT TECHONOLOGIES //
//*********************//
if($article['production_froid']!="" || $article['production_chaud']!="" || $article['declenchement_chaud']!="" || $article['fonction_hiver_techno']!="" || $article['parois_laterales']!="" || $article['parois_fond']!="" || $article['revetement_exterieur']!="" || $article['isolation']!="" || $article['systeme_humidification']!=""){

    $aArticles = array(
        $article['production_froid'],
        $article['production_chaud'],
        $article['declenchement_chaud'],
        $article['fonction_hiver_techno'],
        $article['parois_laterales'],
        $article['parois_fond'],
        $article['revetement_exterieur'],
        $article['absence_vibration'],
        $article['isolation'],
        $article['gaz']
    );

    $template->parts["technologie"] = ""
    . "<p class=\"techtitle\">".ucfirst($template->translate("/article/champs/technologies","F"))." :</p>"
    . "<div class=\"clear\"></div>"
    . "<table>"; 

    $class = 'fond_gris';
    foreach($aArticles as $la_carac){
        if($la_carac != ''){
            $class = $class == '' ? 'fond_gris' : '';
            $template->parts["technologie"] .= str_replace('%class%', $class, $la_carac);
        }
    }

    $template->parts["technologie"] .= "</table>";
}
//*******************//
// FIN TECHONOLOGIES //
//*******************//

//********************//
// DEBUT PERFORMANCES //
//********************//
if($article['puissance_electrique']!="" || $article['intensite_electrique']!="" || $article['consommation_h']!="" || $article['consommation_an']!="" || $article['niveau_bruit']!=""){

    $aArticles = array(
        $article['puissance_electrique'],
        $article['intensite_electrique'],
        $article['tension_electrique'],
        $article['consommation_h'],
        $article['consommation_an'],
        $article['niveau_bruit'],
        $article['conso_kw']
    );

    $template->parts["performances"] = ""
    . "<p class=\"techtitle\">".ucfirst($template->translate("/article/champs/performances","F"))." :</p>"
    . "<div class=\"clear\"></div>"
    . "<table>"; 

    $class = 'fond_gris';
    foreach($aArticles as $la_carac){
        if($la_carac != ''){
            $class = $class == '' ? 'fond_gris' : '';
            $template->parts["performances"] .= str_replace('%class%', $class, $la_carac);
        }
    }

    $template->parts["performances"] .= "</table>";
}
//******************//
// FIN PERFORMANCES //
//******************//

$imgfabrication="<img src=\"/images/templates/fabrication_".$article[paysfabrication].".jpg\" alt=\"".$template->translate("/article/origines/".$article[paysfabrication],"F")."\" align=\"absmiddle\"> ".$template->translate("/article/origines/".$article[paysfabrication],"F");
$origine="<tr><td><b>".$template->translate("/article/champs/origine_fabrication","F")." :</b></td><td>".$imgfabrication."</td></tr>";

//********************//
// DEBUT DIMENSIONS //
//********************//
$aArticles = array(
    $article['volume_interieur'],
    $article['dimensions'],
    $article['dimensions_groupe'],
    $article['dimensions_evaporateur'],
    $article['dimensions_regulateur'],
    $article['dimensions_encastrement'],
    $article['distance_plafond'],
    $article['epaisseur_mur'],
    $article['infos_encastrement'],
    $article['emballage'],
    $volume,
    $origine
);

$template->parts["techdimensions"] = ""
. "<p class=\"techtitle\">".ucfirst($template->translate("/article/champs/dimensions","F"))." :</p>"
. "<div class=\"clear\"></div>"
. "<table>"; 

$class = 'fond_gris';
foreach($aArticles as $la_carac){
    if($la_carac != ''){
        $class = $class == '' ? 'fond_gris' : '';
        $template->parts["techdimensions"] .= str_replace('%class%', $class, $la_carac);
    }
}

$template->parts["techdimensions"] .= "</table>";
//****************//
// FIN DIMENSIONS //
//****************//

if($article['puissance_frigo'].$article[niveau_sonore_1m].$article[niveau_sonore_3m].$article[niveau_sonore_5m].$article[kit_sonore].$article[debit_air_ext].$article[coloris_groupe].$article[puissance]!="")
    $template->parts["unite_exterieure"]="<div class=\"hsep\"></div>
    <div class=\"techtitle\">".$template->translate("/article/champs/groupe_condensation","F")." :</div>
    <table>
    $article[puissance_frigo]
    $article[puissance]
    $article[niveau_sonore_1m]
    $article[niveau_sonore_3m]
    $article[niveau_sonore_5m]
    $article[kit_sonore]
    $article[debit_air_ext]
    $article[coloris_groupe]
    $article[gaz]
    </table>";
    
    
if($article[nb_ventilateur].$article[debit_air_int].$article[puissance_chauffage].$article[kit_humidificateur].$article[evacuation_eau].$article[coloris_evaporateur]!="")
    $template->parts["evaporateur"]="<div class=\"hsep\"></div>
    <div class=\"techtitle\">".$template->translate("/article/champs/evaporateur","F")." :</div>
    <table>
    $article[nb_ventilateur]
    $article[debit_air_int]
    $article[puissance_chauffage]
    $article[kit_humidificateur]
    $article[evacuation_eau]
    $article[coloris_evaporateur]
    </table>";


$i=0;
$art_rangements=$DB_site->query("SELECT * FROM article a INNER JOIN article_rangement ac ON(a.artid = ac.artid_rangement) INNER JOIN article_site a_s ON(a.artid=a_s.artid) WHERE ac.artid = '$artid' AND a_s.site_code='".$template->site_code()."' AND a_s.active = '1' AND commandable='1' GROUP BY a.artid ORDER BY a.artcode");
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

// articles liés (inverse rangements)
$sep="";
$i=0;
$artcodelist=array();
$refs=array();
$art_rangements=$DB_site->query("SELECT a.artcode,a.artcode_eurocave,a.active,a.artid,a.catid,a.libelleFR,a.libelleEN,a.libelleSP,m.marqueid,(SELECT COUNT(id) FROM article_lot WHERE lotid=a.artid) AS lot,m.libelle AS marque FROM article_rangement ac, article a,article_marque am,marque m WHERE a.artid = ac.artid AND a.artid=am.artid AND m.marqueid=am.marqueid AND ac.artid_rangement= '$artid'");
if ($DB_site->num_rows($art_rangements) > 0){
    while ($art_rangement=$DB_site->fetch_array($art_rangements)){
	if($art_rangement['active']==1)
	  $artcodelist[]="<div class=\"artcompat\">
	    <a href=\"".$template->mkurl("article",$art_rangement['artid'],$art_rangement)."\">
	      <img src=\"images/".$art_rangement['artid']."_50_50.jpg\" alt=\"".$art_rangement['libelle'.$template->langue]."\">
	      <br>".$art_rangement['artcode'].
	    "</a>
	  </div>";
	$active=intval($art_rangement['active']);
	if($active==0) $active=1;
	if(intval($art_rangement['lot'])==0 && $art_rangement['catid']!=212) $refs[$art_rangement['marqueid']][$active][]=$art_rangement['artcode_eurocave'];
    }
    
    if(count($artcodelist)>0) $template->parts["compatlist"]="<p class=\"compattitle\">".$template->translate("/article/champs/compatible_modele_suivants","F")."</p>"
  .implode("",$artcodelist);
    
    foreach($refs as $marqueid=>$reflist) {

      $marque=$DB_site->query_first("SELECT libelle FROM marque WHERE marqueid=".intval($marqueid));
      if(count($reflist[1])>0){
	  asort($reflist[1]);
	  $template->parts["compatrefs"].="<p class=\"compattitle\">".$template->translate("/article/champs/compatible_avec","F")." ".$marque['libelle']."</p>
	  <div class=\"reflist\">".implode(" - ",$reflist[1])."</div>";
      }
      if(count($reflist[3])>0){
	  asort($reflist[3]);
	  $template->parts["compatrefs"].="<p class=\"compattitle\">".$template->translate("/article/champs/compatible_ancien","F")." ".$marque['libelle']."</p>
	  <div class=\"reflist\">".implode(" - ",$reflist[3])."</div>";
      }
    }
    
    $template->evaltemplate("OngletCompat","ongletcompatible");
}

//******************//
// DEBUT MEME GAMME //
//******************//
$sep="";
$i=0;
$arts_sim=$DB_site->query("SELECT arts.artid FROM article_similaire arts INNER JOIN article a USING(artid) INNER JOIN article_site a_s ON(a.artid=a_s.artid) WHERE gid=(select gid from article_similaire where artid = '$artid') AND arts.artid<>'$artid' AND a_s.site_code='".$template->site_code()."' AND a_s.active='1' GROUP BY a.artid ORDER BY a.artcode");
if($DB_site->num_rows($arts_sim) > 0){
    while ($art_sim=$DB_site->fetch_array($arts_sim)){
        $template->parts['class_sup'] = $i == 0 ? 'premier_acc vignette_acc' : 'vignette_acc';
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

//***********************//
// DEBUT SELECTION MVIAL //
//***********************//
$i=0;
$sep="";
$template->parts["artselect"]="";
if($finition['finitionid']!="" && $art_actif[0]==1){
    $selection=$DB_site->query("SELECT a.artid FROM article a INNER JOIN articlerecherche_finition af USING(artid) INNER JOIN article_site a_s ON(a.artid=a_s.artid) WHERE a.catid IN (183,184,185) AND a.artid<>$artid AND af.finitionid=".$finition['finitionid']." AND a_s.site_code='".$template->site_code()."' AND a_s.active='1' GROUP BY a.artid LIMIT 8");
    if($DB_site->num_rows($selection)>0){
	while($vin=$DB_site->fetch_array($selection)){
	    $template->parts['class_sup'] = $i == 0 ? 'premier_acc vignette_acc' : 'vignette_acc';
	      vignette($vin['artid'],'selection');
	      $i++;
	}

	$template->evaltemplate("OngletSelection","ongletselection");
	$template->parts["nb_selection"]=$i;
	vignscroll("selection",$i);
    }
}
//*********************//
// FIN SELECTION MVIAL //
//*********************//

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

//Gestion affichage des packs
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
    //       $pack_prices=article_prices($packid,$template->tva_ref(),$template->site_code(),$sitepro);
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
            vignette($art_pack['artid'],'pack',$i==1?"first":"");
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
    $strikeout="<div class=\"strikeout\"><strike>".$template->price_format($pack_prices['prixconseille'])." <small>".$template->translate($taxtxt,"U")."</small></strike></div>";
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
	//$cercleReduc="<img class=\"cercleReduc\" src=\"../images/templates/".$template->langue."/cercle-pack.png\"/>";
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
	//$cercleReduc="<img class=\"cercleReduc\" src=\"../images/templates/".$template->langue."/cercle-pack.png\"/>";
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


if($template->parts["artcons"]!="") $template->evaltemplate("ArticleConseille","artconseilles");
if($template->parts["artsim"]!="") $template->evaltemplate("ArticleSimilaire","artsimilaires");
if($template->parts["artselect"]!="") $template->evaltemplate("ArticleSelection","artselection");
if($template->parts["compatlist"]!="" || $template->parts["compatrefs"]!="") $template->evaltemplate("ArticleCompatible","compatible");
if($template->parts["listartinpack"]!="") $template->evaltemplate("ArticlePack","artpack");
$template->evaltemplate("OngletTechnique","onglettechnique");
$template->evaltemplate("ArticleTechnique","fichetechnique");

// image
/*if ($article[image]!="") {

    $redim="";
    $imgurl="configurations/".$template->parts["host"]."/images/produits/article/$article[artid].$article[image]";
    if(file_exists($imgurl)){
  $size=getimagesize($imgurl);
  if($size[0]>280){
      $h=intval(280*$size[1]/$size[0]);
      $w=280;
      $redim="width=\"$w\" height=\"$h\"";
      if($h>370) $redim="height=\"370\"";
  }else
    if($size[1]>370) $redim="height=\"380\"";

  //$articleimage="<a href=\"#\" onclick=\"Javascript: agrandir($artid,$article_largeur,$article_hauteur);\"><img id=\"big\" src=\"/configurations/".$template->parts["host"]."/images/produits/article/$article[artid].$article[image]\" alt=\"$article[$libelle]\"></a>";
//  $articleimage="<a href=\"photos.php?artid=$article[artid]\" title=\"$article[legende]\" class=\"thickbox\" rel=\"gallerie-".url_rewrite($article[$libelle])."\"><img id=\"big\" src=\"/configurations/".$template->parts["host"]."/images/produits/article/$article[artid].$article[image]\" alt=\"$article[$libelle]\" $redim></a>";
//  $articleimage="<img id=\"big\" src=\"images/$article[artid]_280_370.jpg\" alt=\"$article[$libelle]\">";
  $articleimage="<img id=\"big\" src=\"$imgurl\" alt=\"$article[$libelle]\" $redim>";
    }else
  $articleimage="<img id=\"big\" src=\"images/templates/photo_non_dispo.jpg\" alt=\"$article[$libelle]\" width=\"185\">";
}*/

$imgart=article_image($artid,280,420,$kdopid);
$template->parts["imgartid"] = "artid=".$artid;

//Visuel de l'article
$template->parts["imgart"]="<img id=\"artpic\" class=\"visuel\" src=\"".$imgart."\" itemprop=\"image\" alt=\"".$article[$libelle]."\" onclick=\"tb_show('','photo.php?artid=".$article['artid']."',false);\">";
$template->parts["imgart_mobile"]="<img src=\"".$imgart."\" itemprop=\"image\" alt=\"".$article[$libelle]."\" onclick=\"\">";


$template->parts["strikeout"]="<div class=\"estrikeout\"></div>";
$template->parts["pc"]="";


$fraisport=$DB_site->query_first("SELECT gratuit FROM fraisport WHERE paysid=57 LIMIT 1");
$totallivr=$fraisport[0];



//Gestion prix articles
$template->parts["devise_code"] = $template->devise();
$article_prices = article_prices($article[artid],$template->tva_ref(),$template->site_code(),$sitepro);

$template->parts["pcprice"]   = round($article_prices['remise'])."%";
$template->parts["pricediff"] = $template->price_format($article_prices['prixconseille']-$article_prices['prix']);
$template->parts["soprice"]   = $template->price_format($article_prices['prixconseille']);

//Formatage des prix
$template->parts["pricetax"] = $template->translate($sitepro?"/site/ht":"/site/ttc","U");
$template->parts["price"]    = $template->price_format($article_prices['prix'],2,true,true);
$template->parts["prixd3"]   = $template->price_format($article_prices['prix']/3);

$promo=$DB_site->query_first("SELECT COUNT(*) FROM promo WHERE artid=".$article[artid]);
$secondchoix=$DB_site->query_first("SELECT COUNT(*) FROM secondchoix WHERE artid=".$article[artid]);
if (round($article_prices['prix']) != round($article_prices['prixconseille'])){
    if($promo[0]>0 || $secondchoix[0]>0){
        $template->parts["pc"]        = "<p class=\"reduc\">-".round($article_prices['remise'])."%</p>";
        $template->parts["strikeout"] = "<p class=\"prix_barre\">".$template->price_format($article_prices['prixconseille'])."</p>";
        $promotitle=round($article_prices['remise'])."% ".$template->translate("/article/reduction")." : ";
    }

    if($article_prices['remise'] >= 5) $template->evaltemplate("ArticleEconomie","economie");
    $template->evaltemplate("ArticlePrixBarre","prixbarre");
}

if($article_prices['vf']) {
    $vfarticle=$DB_site->query_first("SELECT * FROM vente_flash_article vfa INNER JOIN vente_flash vf ON (vf.id=vfa.vfid) WHERE (".time()." BETWEEN vf.du AND vf.au) AND vfa.artcode='$article[artcode]'");
    $template->parts["js"]="<script language=\"javascript\">
    var d=new Date(".date("Y",$vfarticle['au']).",".(date("m",$vfarticle['au'])-1).",".date("d",$vfarticle['au']).",".date("H",$vfarticle['au']).",".date("i",$vfarticle['au']).",0);
    var dn=new Date(".date("Y").",".(date("m")-1).",".date("d").",".date("H").",".date("i").",0);
    var dstr={days:\"".$template->translate("/article/champs/jours")."\",hours:\"".$template->translate("/article/champs/heures")."\",minutes:\"".$template->translate("/article/champs/minutes")."\",seconds:\"".$template->translate("/article/champs/secondes")."\"};
    upddecompte();
    </script>" ;
    
    $template->parts["pc"]="<p class=\"reduc\">".round(-$article_prices['remise'])."%</p>";
    $template->parts["strikeout"]="<p class=\"prix_barre\">".$template->price_format($article_prices['prixconseille'])." </p>";
    $promotitle=round($article_prices['remise'])."% ".$template->translate("/article/reduction")." : ";

    $template->evaltemplate("VFCompteur","venteflash");
    $template->parts["imgart"]="<img src=\"/images/templates/".$template->langue."/venteflash.png\" class=\"vfimg\" onclick=\"tb_show('','photo.php?artid=".$article['artid']."',false);\">".$template->parts["imgart"];
}

if($article['ecopart'] > 0){
    $template->parts["ecopart"] = ($sitepro ? number_format($article['ecopartht'],2,',',' ').' €' : number_format($article['ecopart'],2,',',' ').' €');
    $template->evaltemplate("ArticleEcopart","ecoparttxt");
}

//Livraison offerte
if($article_prices['prix'] >= $totallivr ){
    $template->evaltemplate("ArticleLivraisonOfferte","article_livraison_offerte");
    //$iconlivr = "<div class=\"iconlivraison\"><img src=\"images/templates/".$template->langue."/livraison.png\"></div>";
}

//Génération du picto garantie
if(!empty($garantie)){
    if(file_exists("images/templates/".$template->langue."/garantie_".$dureeGarantie.".jpg")){
        $icongarantie = "<img class=\"picto\" src=\"/images/templates/".$template->langue."/garantie_".$dureeGarantie.".jpg\">";
    } 
}

//Affichage des icones
//if($iconlivr) $template->parts["icons"] .= $iconlivr;
if($icongarantie) $template->parts["icons"] .= $icongarantie;
if($iconenergie) $template->parts["icons"] .= $iconenergie;

// pays de fabrication
if(file_exists("images/templates/origine-gd-".$article['paysfabrication'].".jpg")){
    $template->parts["icons"] .= "<img class=\"picto\" src=\"/images/templates/origine-gd-".$article['paysfabrication'].".jpg\" alt=\"".$template->translate("/article/origines/".$article['paysfabrication'],"F")."\" align=\"absmiddle\">";
}

if($template->site_code()=="base"){
    $sr=new showroom($DB_site,$template->langue);
    $sr_icon="images/templates/showroom.jpg";
    if($sr->in_showroom($article['artid']) && file_exists($sr_icon)) $template->parts["icons"] .="<a href=\"/showroom-cave-a-vin-lyon.html\" target=\"_blank\"><img class=\"picto\" src=\"/$sr_icon\"></a>";
}

$requete=$DB_site->query("SELECT iconid FROM article_icon ai INNER JOIN icons ON(icons.id=ai.iconid) WHERE active=1 AND ai.artid=".$article['artid']);
while($icon=$DB_site->fetch_array($requete)){
    $path="images/icons/".$template->langue."/".$icon['iconid'].".png";
    if(file_exists($path)) $template->parts["icons"].="<img src=\"".$path."\" class=\"picto\">";
}

$configurateur=new configurateur($DB_site);
$configurateur->loadcookie();
if($configurateur->in_artids($artid)){
    $template->parts["config_url"]="/configurateur.php?id=".$configurateur->configurateurid;
    $template->evaltemplate("ConfigRetour","retour_config");
}

if($article['video']!=""){
    $template->parts["videoiframe"]=$article['video'];
    $template->evaltemplate("ArticleVideo","artvideo");
    $template->evaltemplate("OngletVideo","ongletvideo");
}

$template->parts["prices"] ="";
$template->parts["artid"]=$article['artid'];

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
$sql .= " ORDER BY position";
$article_images = $DB_site->query($sql);
$nb_articles = $DB_site->num_rows($article_images);
$sep = "";
if($nb_articles > 0){
        while ($article_image = $DB_site->fetch_array($article_images)){
            $photonb++;
            $src = "images/".$article['artid']."_$article_image[articlephotoid]_60_60.jpg";
            
            if ($photonb < 6) $TemplatePhotosArticleBit .= ""
            . "<div class=\"smallimg\" onclick=\"tb_show('','photo.php?artid=".$article['artid']."&imgid=".$article_image['articlephotoid']."',false)\" onmouseover=\"change_artpic('/images/".$article['artid']."_".$article_image[articlephotoid]."_280_420.jpg');\" onmouseout=\"change_artpic('".$imgart."')\">"
                . "<img src=\"".$src."\" alt=\"".$article[$libelle]."\">"
            . "</div>";
            
            if($article_image['ambiance'] == 1) $images_ambiance[] = "<img src=\"images/".$article['artid']."_".$article_image['articlephotoid']."_630_350.jpg\" width=\"630\" height=\"350\" alt=\"".$article[$libelle]."\">";
            
            $template->parts["imgcomp_mobile"].="<img src=\"images/".$article['artid']."_".$article_image['articlephotoid']."_280_420.jpg\">";
        }

    $template->parts["simg"] = $TemplatePhotosArticleBit;
}

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
          $libelle=$art_pack['libelle'.$template->langue];
          
          $template->parts["kdopacklibelle"]=$libelle." ".$marque['libelle'];
          $template->parts["kdopackref"]=$art_pack['artcode'];
          $template->parts["kdopacklogo"]="<img src=\"/marques/".$marque['marqueid']."_100_20.jpg\" alt=\"".$marque['type']."\">";
          $template->parts["kdopackimg"]="images/".$art_pack['artid']."_100_100.jpg";
          $template->parts["kdopacklink"]="art,".$art_pack['artid'].",".url_rewrite($libelle).".htm";
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

        if(!$sitepro && $article_prices['prix'] > 150 && $article_prices['prix'] <= 25000 && (is_null($user_info['raisonsociale']) || $user_info['raisonsociale'] == '') && $oArticle->categorie[count($oArticle->categorie)-1]['categorie_id'] != 182){ 

               $mensualites = $article_prices['prix'] <= 2000 ? 4 : 10;
               
               $template->parts['nbfois']       = $mensualites;
               $template->parts['prix_finance'] = number_format($article_prices['prix'] / $mensualites,2,',',' ');
               
               //Récupération des informations concernant le plan de financement
               $objXmlCredit = @simplexml_load_file($sofinco_url_pt.'/sofgate.asp?p0=WSACCROCHE&q6='.$sofinco_site_id.'&p4='.$article_prices['prix'].'&d1='.$mensualites);

               if($objXmlCredit->C_RETOUR == '00'){ 

                  //Formatage TAEG
                  $taeg = strip_tags($objXmlCredit->SIMU->LIB_TX_TEG_AN);
                  $pos_temp = strrpos($taeg, ':');
                  $taeg     = substr($taeg, $pos_temp+1);
                  $taeg     = trim($taeg);

                  //Formatage montant total dû
                  $montant_total = strip_tags($objXmlCredit->SIMU->LIB_MONTANT_TOTAL_SANS_ASSURANCE);
                  $pos_temp = strrpos($montant_total, ':');
                  $montant_total     = substr($montant_total, $pos_temp+1);
                  $montant_total     = trim($montant_total);

                  //Formatage des mentions pédagogiques
                  $mention_init  = strip_tags($objXmlCredit->ML->LIB_MENTION_SANITAIRE);
                  $tab_temp      = explode('.',$mention_init);
                  $mention = '';
                  foreach($tab_temp as $la_mention){
                      if(!empty($la_mention)){
                           $mention .= $la_mention.'. ';
                      }
                  }

                  $template->parts["sofinco_site_id"]     = $sofinco_site_id;
                  $template->parts["total_finance_brut"]  = $article_prices['prix'];
                  $template->parts["total_financement"]   = number_format($article_prices['prix'],2,',',' ');
                  $template->parts["taeg"]                = $taeg;
                  $template->parts["total_du"]            = $montant_total;
                  $template->parts["url_finance"]         = "/financement_sofinco.php?montant=".$article_prices['prix']."&mensualites=".$mensualites;
                  $template->parts["url_simulation"]      = $sofinco_url_pt."/sofgate.asp?q6=".$sofinco_site_id."&p0=PRODUIT&p4=".$article_prices['prix'];
                  $template->parts["mention_pedagogique"] = $mention;

                  $template->evaltemplate("PaiementSofinco","paiement_sofinco");
               }
          }
    }
    
    if($sitepro){
	  $template->evaltemplate("ArticleLocation","paiement_sofinco");
	  $template->parts["paiement_sofinco"]=$template->short_links($template->parts["paiement_sofinco"]);
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

    $template->parts["fblike"] = "<iframe src=\"http://www.facebook.com/plugins/like.php?href=".urlencode("http://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'])."&layout=button_count&show_faces=false&width=450&action=like&colorscheme=light\" scrolling=\"no\" frameborder=\"0\" allowTransparency=\"true\" style=\"border:none; overflow:hidden; width:100px; height:20px\"></iframe>
    <iframe allowtransparency=\"true\" frameborder=\"0\" scrolling=\"no\" src=\"https://platform.twitter.com/widgets/tweet_button.html\" style=\"width:100px; height:20px; border:none;\"></iframe>
    <g:plusone size=\"medium\"></g:plusone>";
    
    $pname=utf8_encode(html_entity_decode($article['libelle'.$template->langue],ENT_COMPAT,"ISO-8859-15"));    
    $template->parts["remarketing"]=google_remarketing($article['artid'],"product",$article['prix'],utf8_encode($categorie->libelle),$pname,$article['prix']);
}


if($art_actif[0]==3){
    $template->parts["icons"]="";
    $template->parts["simg"]="";
    $template->evaltemplate("ArticleContact","contact_article");
    $template->evaltemplate("ArticleArchive","ajoutpanier");
    //$template->parts["imgart"]="<img src=\"/images/templates/".$template->langue."/epuise.png\" class=\"bande\">".$template->parts["imgart"];
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
$template->parts["gamme"]=$article['gamme'.$template->langue];
$template->parts["artcode"]=$article["artcode"];
$template->parts["description_txt"] = $article["description".$template->langue];
$template->evaltemplate("ArticleDescription","description");
if($art_actif[0]==3){
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
    
    $template->parts["js"].="<script type=\"text/javascript\" src=\"javascripts/sliderman.js\"></script>
    <script type=\"text/javascript\">

    effectsDemo2 = 'rain,stairs,fade';
    var demoSlider_2 = Sliderman.slider({container: 'ambianceimgs', width: 725, height: 400, effects: effectsDemo2,
    display: { autoplay: 5000,
    loading: {background: '#000000', opacity: 0.5, image: 'images/templates/wait2.gif'},
    $navigationslider
    } });

    </script>";

    $template->evaltemplate("ArticleAmbiance","ambiance");
}

$template->parts["comparateur"]="";
if($comparateur->compare_type($article['typearticleid']) && $art_actif[0]==1){
      if($comparateur->in_articles($artid)){
          $classComp="btn_comparer_on";
          $txtComp=$template->translate("/comparateur/ajoute_au_comparateur","F");
      }else{
          $classComp="btn_comparer_off";
          $txtComp=$template->translate("/comparateur/comparer","F");
      }

      $template->parts["comparateur"] = ""
          . "<a id=\"btn_comparaison".$artid."\" class=\"".$classComp." btn_comparer\" style=\"float: right;\" onclick=\"toggleCompare(".$artid.");\" href=\"javascript:void(0);\">"
              . "<span id=\"span_comparaison".$artid."\">".$txtComp."</span>"
          . "</a>";
}

include("barrefixe.php");

foreach($template->sites() as $site_code=>$site){
    $article_site=$DB_site->query_first("SELECT active FROM article_site WHERE site_code='".$site_code."' AND artid=".$article['artid']);
    if($article_site['active']==1 || $article_site['active']==3){
	foreach($site['hosts'] as $url=>$deflangue){
	    if($site['actif'][$url]){
		$alternate=new template($deflangue,$DB_site);
		$alternate->langue=$deflangue;
		$template->parts["alternate"].="<link rel=\"alternate\" hreflang=\"".$site['locales'][$deflangue]."\" href=\"https://www.".$url.$alternate->mkurl("article",$article['artid'],$article)."\">\n";
	    }
	}
    }
}

$metatitle=$promotitle.$template->parts["libelle"]." ".ucwords($marq['libelle']).", ".$article['artcode']." - ".$template->translate("/site/nom");
$metadescription=$template->parts["libelle"].", ".$article['artcode'].". ".$template->translate("/site/meta_description");
if($art_actif[0]==3){
    $metatitle=$template->translate("/archives/meta_article","F")." ".$template->parts["libelle"]." ".ucwords($marq['libelle'])." - ".$template->translate("/site/nom");
    $metadescription=$metatitle." ".$template->translate("/site/meta_description");
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
    if($nbelem<=4) return false;
    $template->parts["scrollbtns_".$divid]="<div class=\"scrollbtnsparent\">
      <div class=\"scrollbtns\" id=\"scroll_".$divid."_btns\">
      <span class=\"toleft\" onclick=\"vignscroll('".$divid."','-1')\"></span>";
      for($b=0;$b<$nbelem;$b++)
	  $template->parts["scrollbtns_".$divid].="<span class=\"btn".($b<=3?" selected":"")."\" onclick=\"vignscroll('".$divid."','".$b."')\"></span>";
      $template->parts["scrollbtns_".$divid].="<span class=\"toright\" onclick=\"vignscroll('".$divid."','+1')\"></span>
      </div>
      <div class=\"clear\"></div>
    </div>";
}

?>