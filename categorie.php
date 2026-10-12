<?php
include_once($_SERVER['DOCUMENT_ROOT'].'/global.php');

//Redirection sur la home si aucune catégorie ou url spécifiée
if((!isset($_GET['catid']) || $_GET['catid']=="") && (!isset($_GET['url']) || $_GET['url']=="")) header("location:/");

$gtime           = microtime(true);
$datedujour      = date("Y-m-d");
$metatitle       = array();

// recuperation du nombre d'article affiches par page
//$perpage_default = 20;
$perpage_default = 50;

$perpage         = isset($_GET['perpage']) ? intval($_GET['perpage']) : $perpage_default;

$categorie = new categorie($DB_site, 0, $template->langue);

//GESTION REDIRECTIONS
if(isset($_GET['catid']))
{
  //on récupère les données de la catégorie
  $categorie->load($_GET['catid']);
  //on récupère l'url de la catégorie lors dans le cas d'un tri par prix croissant
  $urlCat = $template->mkurl("categorie", $categorie->catid,$categorie);
  
  //on génère a nouveau le lien canonique en cas de tri
  $template->parts['canonical'] = "https://".$template->host.$urlCat;

  //création des balises hreflang en cas de tri.
  foreach($template->sites() as $site_code=>$site){
       foreach($site['hosts'] as $url=>$deflangue){    
           if($site['actif'][$url]){
               $categ=new categorie($DB_site,$categorie->catid,$deflangue);
               $url2 = $template->mkurl('categorie',$categorie->catid,$categ);
               $template->parts["alternate"].="<link rel=\"alternate\" hreflang=\"".$site['locales'][$deflangue]."\" href=\"https://www.".$url.$url2."\">\n";
           }
       }
   }
  //$redirect_cat = array(0,99);
  if($categorie->loaded==false || $categorie->catid==0){
    header("Location: /");
    exit;
  }

  if($_GET['sensorder']=="ventes") unset($_GET['sensorder']);
  $_GET['page']=ceil(1+$_GET['start']/$perpage);
  $url=$categorie->mkurl($_GET);
  if(strpos($url['url'],"categorie.php")===false) $template->redirect($url['url'],301);

  if(preg_match("/(\/[^\/]+-cat,[0-9]+.htm)/",$_SERVER['REQUEST_URI'],$m)){
      $template->redirect($url['url'],301);
  }
  
  $template->parts["metarobots"]="noindex";
  $categorie->page=ceil($_GET['start']/$perpage)+1;
}

if(isset($_GET['url'])) {
  $categorie->load_from_url($_GET['url']);
  $urlCat = $template->mkurl("categorie", $categorie->catid,$categorie);
  $template->parts['canonical'] = "https://".$template->host.$urlCat;
  $get_url = "/".mysqli_real_escape_string($DB_site->link_id, $_GET['url']);
  //Paramétrage des balises hreflang fr et en
   foreach($template->sites() as $site_code=>$site){
       foreach($site['hosts'] as $url=>$deflangue){    
           if($site['actif'][$url]){
               $categ=new categorie($DB_site,$categorie->catid,$deflangue);
               $url2 = $template->mkurl('categorie',$categorie->catid,$categ);
               if(count($categorie->marqueids) == 1 ){
                $m = new marque($DB_site,$categorie->marqueids[0]);
                $m_libelle = str_replace(" ", "-", strtolower($m->libelle));
                $url2 = str_replace(".htm", "/".$m_libelle."-m.htm", $url2);
               }
               $template->parts["alternate"].="<link rel=\"alternate\" hreflang=\"".$site['locales'][$deflangue]."\" href=\"https://www.".$url.$url2."\">\n";
           }
       }
   }

  if(isset($_GET['start']) || isset($_GET['sensorder'])){
      $subcatids=count($categorie->subcatids)>0?implode(",",$categorie->subcatids):null;
      $page=ceil(1+intval($_GET['start'])/$perpage);
      $start=isset($_GET['start'])?intval($_GET['start']):null;
      $sensorder=$_GET['sensorder']!="ventes"?$_GET['sensorder']:null;
      $url=$categorie->mkurl(array("catid"=>$categorie->catid,"subcatids"=>$subcatids,"start"=>$start,"page"=>$page,"sensorder"=>$sensorder));
      $template->redirect($url['url'],301);
  }

  unset($_GET['url']);
 
  $_GET['catid']=$categorie->catid;
  if($categorie->subcatids!=array()) $_GET['subcatids']=implode(",",$categorie->subcatids);
  if($categorie->marqueids!=array()) $_GET['marqueids']=implode(",",$categorie->marqueids);

  $_GET['start']=($categorie->page-1)*$perpage;
}

if($categorie->loaded==false || $categorie->catid==0){
    header("Location: /");
    exit;
}

$origincat=clone $categorie;
//Chargement du détail de la catégorie parente (si existente et si seulement filtre sur la categorie...)
if($categorie->menu_appartenance==0 && $categorie->parentid>0)
{
    $pcat=new categorie($DB_site,$categorie->parentid,$template->langue);
 
    while($pcat->parentid!=0 && $pcat->menu_appartenance==0)
    {
       $pcat=new categorie($DB_site,$pcat->parentid,$template->langue);
    }
    $categorie=new categorie($DB_site,$pcat->catid,$template->langue);
    $_GET['subcatids']=$_GET['catid'];
    $_GET['catid']=$categorie->catid;
}

$temp    = "title$langue";
$start   = 0;

//Paramétrage de la liste de choix de l'ordre de tri
$aResTri = array(
    'ventes',
    'ASC',
    'DESC',
);


if(!isset($_GET['sensorder'])) $_GET['sensorder'] = $categorie->defaultorder;
foreach($aResTri as $type_tri){
    $urlTri = $categorie->mkurl($_GET, array("sensorder" => $type_tri));
    $template->parts["select_tri"] .= "<option value=\"".$urlTri['url']."\" ".($type_tri == $_GET['sensorder'] ? 'selected' : '').">".$template->translate("/categorie/tri/".$type_tri,"F")."</option>";
    $template->parts["link_tri"].="<a href=\"".$urlTri['url']."\"><i class=\"fa fa".($type_tri == $_GET['sensorder'] ? '-check' : '')."-square-o\"></i>".$template->translate("/categorie/tri/".$type_tri,"F")."</a>";
}


//Application des filtres avant recherche des produits

// les new
$categorie->setparam("type_accessoire",$_GET['type_accessoire']);
$categorie->setparam("type_pieces",$_GET['type_pieces']);
$categorie->setparam("gamme",$_GET['gamme']);
$categorie->setparam("colorisids",$_GET['colorisids']);
$categorie->setparam("largeurids",$_GET['largeurids']);
$categorie->setparam("prixids",$_GET['prixids']);
$categorie->setparam("marqueids",$_GET['marqueids']);
$categorie->setparam("hauteurids",$_GET['hauteurids']);
$categorie->setparam("profondeurids",$_GET['profondeurids']);
$categorie->setparam("finitionids",$_GET['finitionids']);
$categorie->setparam("type_fourids",$_GET['type_fourids']);
$categorie->setparam("type_plaqueids",$_GET['type_plaqueids']);
$categorie->setparam("type_poseids",$_GET['type_poseids']);
$categorie->setparam("type_hotteids",$_GET['type_hotteids']);
$categorie->setparam("type_aspirationids",$_GET['type_aspirationids']);
$categorie->setparam("type_bbqids",$_GET['type_bbqids']);
$categorie->setparam("type_accessoireids",$_GET['type_accessoireids']);
$categorie->setparam("nb_foyersids",$_GET['nb_foyersids']);
$categorie->setparam("nb_foursids",$_GET['nb_foursids']);
$categorie->setparam("capaciteids",$_GET['capaciteids']);
$categorie->setparam("capaciteidskg",$_GET['capaciteidskg']);
$categorie->setparam("couvertsids",$_GET['couvertsids']);
$categorie->setparam("origineids",$_GET['origineids']);
//$categorie->setparam("typearticleids",$_GET['typearticleids']);

$categorie->setparam("pro",($sitepro ? 2 : 1));
$categorie->setparam("site_code",$template->site_code());

// redirection vers nouvelles pages promo
if($_GET['bonsplans']=="promo"){
    $categorie->promo_load();
    $template->redirect($categorie->promo_url,301);
}

if($_GET['bonsplans']=="idkdos"){
    $categorie->idkdos_load();
    $template->redirect($categorie->idkdos_url,301);
}

//Mise en place du tri
switch($_GET['sensorder']){
    case "ASC":
        $order = "prix ASC";
        break;

    case "DESC":
        $order = "prix DESC";
        break;

    case "appro":
        $order = "date_appro,prixht ASC";
        break;

    case "ventes":
        $order = "ventes DESC, stock DESC";
        if($template->pro) $order = "ventespro DESC, stock DESC";
        unset($_GET['sensorder']);
        break;

    default:
       $order = "prix ASC";
}
    
//nice($categorie);
$remarketing = array(
    "libelle"  => array(),
    "price"    => array(),
    "category" => array()
);


//Mise en place fil d'ariane
if(isset($_GET['subcatids']) && $_GET['subcatids']!="") $categorie->setsubcatids(explode(",",$_GET['subcatids']));
$asubcatids=$categorie->subcatids;
$menus=$categorie->getMenus();
$fil_cats=array();
$bcategorie=$categorie;

if($categorie->menu_appartenance>0){
    $menu=$menus[$categorie->menu_appartenance];
    $fil_cats[]=$categorie;
    
    foreach($categorie->subcategories() as $p=>$subcategorie){
    if(in_array($subcategorie[0]->catid,$asubcatids) && count($asubcatids)==1){
        $fil_cats[]=$subcategorie[0];
        $footer2[]=$subcategorie[0]->libelle;
        break;
    }
    }
}else{
    foreach($categorie->subcategories() as $p=>$subcategorie){
        if(in_array($subcategorie[0]->catid,$asubcatids)){
            if($subcategorie[0]->menu_appartenance>0){
            $menu=$menus[$subcategorie[0]->menu_appartenance];
            $fil_cats[]=$subcategorie[0];
            $bcategorie=$subcategorie[0];
            break;
            }
            $footer2[]=$subcategorie[0]->libelle;
        }
        
        if(count($subcategorie)>1){
            foreach($subcategorie as $p2=>$subcategorie2){
	      if($p2>0){
		  if(in_array($subcategorie2[0]->catid,$asubcatids)){
		  $menu=$menus[$subcategorie[0]->menu_appartenance];
		  $fil_cats[]=$subcategorie[0];
		  $fil_cats[]=$subcategorie2[0];
		  $footer2[]=$subcategorie2[0]->libelle;
		  }
	      }
            }
        }

        if(is_null($menu) && $categorie->parentid==0)
        {
            $menu['libelle'] = $categorie->libelle;
            $menu['url']     = substr($categorie->url,1);
            if(substr($menu['url'],-4) == '.htm') $menu['url'] = substr($menu['url'],0,-4);
        }
    }
}

$pos=2;
$template->parts["fil_ariane"] = <<<HTML
    &gt; <span property="itemListElement" typeof="ListItem"><a property="item" typeof="WebPage" href="/$menu[url].htm"><span property="name">$menu[libelle]</span></a>
    <meta property="position" content="$pos"></span>
HTML;
$pos++;
$tab = count($fil_cats);
$n = 0;
foreach($fil_cats as $fil_cat){
   $n++;
  $url=$fil_cat->url();
  $libelle=$fil_cat->libelle;
  if($n == $tab){
    //pour le dernier lien du breadcrumb on ne met pas l'attribut typeof="v:Breadcrumb" sinon cela cause des erreurs d'explorations google
    $template->parts["fil_ariane"].=<<<HTML
    &gt; <span property="itemListElement" typeof="ListItem"><a property="item" typeof="WebPage" href="$url[url]"><span property="name">$libelle</span></a>
    <meta property="position" content="$pos"></span>
HTML;
  }else{
    $template->parts["fil_ariane"].=<<<HTML
    &gt; <span property="itemListElement" typeof="ListItem"><a property="item" typeof="WebPage" href="$url[url]"><span property="name">$libelle</span></a>
    <meta property="position" content="$pos"></span>
HTML;
  }
 $pos++;
}

$filtres=$bcategorie->filtres;
$subcategories = $bcategorie->subcategories();
$getsubcatids=explode(",",$_GET['subcatids']);

if(isset($_GET['subcatids']) && $_GET['subcatids']!="") $categorie->setsubcatids(explode(",",$_GET['subcatids']));

$template->parts["categorie_visuel"] = "<img id=\"categorie_visuel\" class=\"hidden-xs\" src=\"".$categorie->image(230,134)."\" alt=\"".$categorie->libelle."\">";

//////////////////////////////////////////////// TSI SI# 2186 /////////////////////////////////////
////////////// Permet de remonter le texte de la sous sous catégorie qd elle existe ///////////////
// Le code remplace la sous catégorie par sa parente quand c'est possible
// Mais la catégorie est encore disponible. Donc on recupere le libelle et
// le commentaire de la sous cat et on le met dans l'affichage.
// recuperation de la sous categorie
if (isset($categorie->subcatids) && (count($categorie->subcatids)==1) && !isset($categorie2->description)) {
    $pcat=new categorie($DB_site,$categorie->subcatids[0],$template->langue);
    $categorie2=$pcat;
} 
elseif(isset($categorie->subcatids) && (count($categorie->subcatids) > 1) && $origincat->parentid == $categorie->catid && !isset($categorie2->description))
{ 
  $categorie2=$origincat;
  
}else{
  $categorie2=$categorie;
}

str_replace(' ','',$categorie2->description);

// Permet de conserver le detail de la sous catégorie quand 1 catégorie et 1 marque selectionnée...
if ( isset($categorie2->description) && !($categorie2->description==""))
    {
        $bcategorie->description=$categorie2->description;
        $bcategorie->description2=$categorie2->description2;
        $bcategorie->libelle=$categorie2->libelle;

        // #SI2267 Gestion optipmisation contenu des marques
        if (count($categorie->marqueids) == 1)
        {     
            $laMarque = $categorie->getlibellemarque($categorie->marqueids[0]);
            $bcategorie->libelle=$categorie2->libelle." ".$laMarque["libelle"];
            $bcategorie->description = "<p style=\"display:inline;\">".ucfirst(strtolower($laMarque['libelle']))." - </p>".$bcategorie->description;
        }
    }
if($sitepro)
  $template->parts["description"]  = $bcategorie->description2;
else
  $template->parts["description"] = $bcategorie->description;

$template->parts["catlibelle"]   = $bcategorie->libelle;
// affichage de taille variable dans l'entete de resultat recherche, pour garder liseret le + long possible
$tailledutexte = (strlen($bcategorie->libelle)+12)*10;
$template->parts["widthlibelle"] = "style=\"width:".$tailledutexte."px;\"";

//////////////////////////////////////////////// TSI SI# 2186 /////////////////////////////////////
//var_dump($categorie);
//Récupération des produits

$articles     = $categorie->articles($order);

$articlecount = count($articles);

$criteoitems  = array();
if($articlecount == 0){
  $redirect = $categorie->url;
  if(intval($categorie->menu_appartenance) === 0){ //redirection des categories invisibles
    $redirect = "/";
  }elseif($get_url == $categorie->url) { //si la categorie n'as pas d'articles, on redireigne sur categorie mere
    $redirect = "/".$menu['url'].".htm";
  }
  header("Status: 302 Moved Temporarily", false, 302);
  header("Location:".$redirect);
  exit();
}

$template->parts["articlecount"] = $articlecount;

//Paramétrage de la liste de choix du nombre de resultats, et selection si choix
$aResParPage = array(50,100,999);
foreach($aResParPage as $nb_result){
    if($nb_result>$articlecount && $nb_result<999) continue;
    $urlParPage = $categorie->mkurl($_GET, array("perpage" => $nb_result == $perpage_default ? null : $nb_result, "start" => 0));
    $template->parts["par_page"] .= "<option value=\"".$urlParPage['url']."\" ".($nb_result == $perpage ? 'selected' : '').">".($nb_result == 999 ? $template->translate("/categorie/filtres/tous","F") : $nb_result)."</option>";
}
$template->evaltemplate("TriNbResultats","tri_nb_resultats"); 

//Paramétrage de la pagination
if(isset($_GET['start'])) $start = intval($_GET['start']);
$template->parts["pagesnav"] = "";
$nbpages = ceil($articlecount / $perpage);
if($nbpages > 1){
    $page_courante = $start / $perpage;
    $page_courante++;

    //Bouton précédent
    if($page_courante > 1){
        $url = $categorie->mkurl($_GET, array("page" => $page_courante - 1, "start" => $start - $perpage));

        $template->parts["urlprecedent"] = $url['url'];
        $template->parts["relprev"]      = isset($url['rel']) ? "rel=\"".$url['rel']."\"" : "";

        $template->evaltemplate("NavigationPrecedent","precedent");
    }else
    $template->evaltemplate("NavigationPrecedentOff","precedent");

    //Boutons de page
    $nb_prec = 3;
    $nb_suiv = 3;
    
    // gestion de la tranche d'affichage en cours
    $debut_boucle = $page_courante > $nb_prec ? $page_courante - $nb_prec : 1;
    $fin_boucle = $page_courante + $nb_suiv < $nbpages ? $page_courante + $nb_suiv : $nbpages;
    for($i = $debut_boucle; $i <= $fin_boucle; $i++){
        $url = $categorie->mkurl($_GET, array("page" => ($i), "start" => ($perpage * ($i - 1))));
        $template->parts["pagesnav"] .= "<li><a href= \"".$url['url']."\" ".(isset($url['rel']) ? " rel=\"".$url['rel']."\"" : "")." class=\"".($start == (($i - 1) * $perpage) ? "selected" : "")."\">".($i)."</a></li>";
    }
    $template->parts["pagesnavmobile"]=$page_courante."/".$nbpages;

    //Bouton suivant
    if($page_courante < $nbpages){
        $url = $categorie->mkurl($_GET, array("page" => $page_courante + 1, "start" => $start + $perpage));
        
        $template->parts["urlsuivant"] = $url['url'];
        $template->parts["relnext"]    = isset($url['rel'])?"rel=\"".$url['rel']."\"":"";
        
        $template->evaltemplate("NavigationSuivant","suivant");
    }else
    $template->evaltemplate("NavigationSuivantOff","suivant");
}else{
    $template->evaltemplate("NavigationSuivantOff","suivant");
    $template->evaltemplate("NavigationPrecedentOff","precedent");
    $template->parts["pagesnavmobile"]="1/1";
}


$aPremiersFiltres      = array(1,5,9,13);
$affiche_liste_filtres = false;
$nb_filtre             = 0;
$display_criteres      = false;
$footer2           = array();
$categorie->typearticleids = $categorie->get_typearticleids();

$template->parts["les_filtres"] = '';
 //////////////////////////////////////////////////////////
//var_dump($filtres);

//var_dump($categorie);

if($filtres != ''){
    $aFiltres = explode('|', $filtres);

    foreach($aFiltres as $le_filtre){

        $sFiltreTag       = '';
        $sFiltreValues    = '';
        $sFiltreIntitule  = '';
        $sListeValues     = '';
        $aFiltresElements = array();

        $sTraduction1 = '';
        $sTraduction2 = '';
        $sTraduction3 = '';

        $template->parts["li_dimensions"] = '';
        $template->parts["supp_filtre"]="";

        switch($le_filtre){

            case 'categorie':

                $sFiltreTag       = 'categorie';
                $sFiltreValues    = 'subcatids';
                $sFiltreValuesMin = '';
                $sFiltreValuesMax = '';
                $aFiltresElements = $subcategories;
                
                $asubcatids = $bcategorie->subcatids;

                $aFiltreValues = array(
                    'subcatids' => '[?url_values]'
                );

                $aFiltreValuesSuppr = array(
                    'subcatids' => $bcategorie->parentid==0||$bcategorie->menu_appartenance>0?null:$bcategorie->catid
                );

                $iTypeSelect = 3;

                break;

            case 'gamme':

                $sFiltreTag       = 'gamme';
                $sFiltreValues    = 'finitionids';
                $sFiltreValuesMin = '';
                $sFiltreValuesMax = '';
                $aFiltresElements = $categorie->getfinition();
                
                $aFiltreValues = array(
                    'finitionids' => '[?url_values]'
                );

                $aFiltreValuesSuppr = array(
                    'finitionids' => null
                );
                
                $iTypeSelect = 1;
                
                foreach($aFiltresElements as $id=>$param){
                    if($param->checked) $footer2[]=$param->libelle;
                }

                break;

            case 'marque':

                $sFiltreTag       = 'marque';
                $sFiltreValues    = 'marqueids';
                $sFiltreValuesMin = '';
                $sFiltreValuesMax = '';
                $aFiltresElements = $categorie->getmarques();
                
                $aFiltreValues = array(
                    'marqueids' => '[?url_values]'
                );

                $aFiltreValuesSuppr = array(
                    'marqueids' => null
                );

                foreach($aFiltresElements as $id=>$param){
                    if($param->checked){
                    $footer2[]=$param->libelle;
                    $metatitle['marque'][]=$param->libelle;
                    }
                }
                $iTypeSelect = 1;

                break;

            case 'rangement':

                $sFiltreTag       = 'rangement';
                $sFiltreValues    = 'moderangementids';
                $sFiltreValuesMin = '';
                $sFiltreValuesMax = '';
                $aFiltresElements = $categorie->getmoderangement($articles);
                
                $aFiltreValues = array(
                    'moderangementids' => '[?url_values]'
                );

                $aFiltreValuesSuppr = array(
                    'moderangementids' => null
                );

                $iTypeSelect = 1;
                
                foreach($aFiltresElements as $id=>$param){
                    if($param->checked) $footer2[]=$param->libelle;
                }

                break;

            case 'typeporte':

                $sFiltreTag       = 'typeporte';
                $sFiltreValues    = 'typeporteids';
                $sFiltreValuesMin = '';
                $sFiltreValuesMax = '';
                $aFiltresElements = $categorie->gettypeporte($articles);
                
                $aFiltreValues = array(
                    'typeporteids' => '[?url_values]'
                );

                $aFiltreValuesSuppr = array(
                    'typeporteids' => null
                );

                $iTypeSelect = 1;
                
                foreach($aFiltresElements as $id=>$param){
                    if($param->checked) $footer2[]=$param->libelle;
                }

                break;

            case 'type_four':

                $sFiltreTag       = 'type_four';
                $sFiltreValues    = 'type_fourids';
                $sFiltreValuesMin = '';
                $sFiltreValuesMax = '';
                $aFiltresElements = $categorie->gettype_four();
                $aFiltreValues = array(
                    'type_fourids' => '[?url_values]'
                );

                $aFiltreValuesSuppr = array(
                    'type_fourids' => null
                );
                $iTypeSelect = 5;
                foreach($aFiltresElements as $id=>$param){
                    if($param->checked) $footer2[]= $param->libelle;
                }

                break;

            case 'nb_foyers':

                $sFiltreTag       = 'nb_foyers';
                $sFiltreValues    = 'nb_foyersids';
                $sFiltreValuesMin = '';
                $sFiltreValuesMax = '';
                $aFiltresElements = $categorie->getnb_foyers();
                $aFiltreValues = array(
                    'nb_foyersids' => '[?url_values]'
                );

                $aFiltreValuesSuppr = array(
                    'nb_foyersids' => null
                );
                $iTypeSelect = 5;
                foreach($aFiltresElements as $id=>$param){
                    if($param->checked) $footer2[]= $param->libelle;
                }

                break;

            case 'nb_fours':

                $sFiltreTag       = 'nb_fours';
                $sFiltreValues    = 'nb_foursids';
                $sFiltreValuesMin = '';
                $sFiltreValuesMax = '';
                $aFiltresElements = $categorie->getnb_fours();
                $aFiltreValues = array(
                    'nb_foursids' => '[?url_values]'
                );

                $aFiltreValuesSuppr = array(
                    'nb_foursids' => null
                );
                $iTypeSelect = 5;
                foreach($aFiltresElements as $id=>$param){
                    if($param->checked) $footer2[]= $param->libelle;
                }

                break;    

            case 'type_plaque':

                $sFiltreTag       = 'type_plaque';
                $sFiltreValues    = 'type_plaqueids';
                $sFiltreValuesMin = '';
                $sFiltreValuesMax = '';
                $aFiltresElements = $categorie->gettype_plaque();
                $aFiltreValues = array(
                    'type_plaqueids' => '[?url_values]'
                );

                $aFiltreValuesSuppr = array(
                    'type_plaqueids' => null
                );
                $iTypeSelect = 5;
                foreach($aFiltresElements as $id=>$param){
                    if($param->checked) $footer2[]= $param->libelle;
                }

                break;    
            case 'type_bbq':

                $sFiltreTag       = 'type_bbq';
                $sFiltreValues    = 'type_bbqids';
                $sFiltreValuesMin = '';
                $sFiltreValuesMax = '';
                $aFiltresElements = $categorie->gettype_bbq();
                $aFiltreValues = array(
                    'type_bbqids' => '[?url_values]'
                );

                $aFiltreValuesSuppr = array(
                    'type_bbqids' => null
                );
                $iTypeSelect = 5;
                foreach($aFiltresElements as $id=>$param){
                    if($param->checked) $footer2[]= $param->libelle;
                }

                break;     

            case 'type_hotte':

                $sFiltreTag       = 'type_hotte';
                $sFiltreValues    = 'type_hotteids';
                $sFiltreValuesMin = '';
                $sFiltreValuesMax = '';
                $aFiltresElements = $categorie->gettype_hotte();
                $aFiltreValues = array(
                    'type_hotteids' => '[?url_values]'
                );

                $aFiltreValuesSuppr = array(
                    'type_hotteids' => null
                );
                $iTypeSelect = 5;
                foreach($aFiltresElements as $id=>$param){
                    if($param->checked) $footer2[]= $param->libelle;
                }

                break;     
            case 'type_aspiration':

                $sFiltreTag       = 'type_aspiration';
                $sFiltreValues    = 'type_aspirationids';
                $sFiltreValuesMin = '';
                $sFiltreValuesMax = '';
                $aFiltresElements = $categorie->gettype_aspiration();
                $aFiltreValues = array(
                    'type_aspirationids' => '[?url_values]'
                );

                $aFiltreValuesSuppr = array(
                    'type_aspirationids' => null
                );
                $iTypeSelect = 5;
                foreach($aFiltresElements as $id=>$param){
                    if($param->checked) $footer2[]= $param->libelle;
                }

                break;
                    
            case 'type_pose':

                $sFiltreTag       = 'type_pose';
                $sFiltreValues    = 'type_poseids';
                $sFiltreValuesMin = '';
                $sFiltreValuesMax = '';
                $aFiltresElements = $categorie->gettype_pose();
                $aFiltreValues = array(
                    'type_poseids' => '[?url_values]'
                );

                $aFiltreValuesSuppr = array(
                    'type_poseids' => null
                );
                $iTypeSelect = 5;
                foreach($aFiltresElements as $id=>$param){
                    if($param->checked) $footer2[]= $param->libelle;
                }

                break;   

            case 'type_accessoire':

                $sFiltreTag       = 'type_accessoire';
                $sFiltreValues    = 'type_accessoireids';
                $sFiltreValuesMin = '';
                $sFiltreValuesMax = '';
                $aFiltresElements = $categorie->gettype_accessoire();
                $aFiltreValues = array(
                    'type_accessoireids' => '[?url_values]'
                );

                $aFiltreValuesSuppr = array(
                    'type_accessoireids' => null
                );
                $iTypeSelect = 5;
                foreach($aFiltresElements as $id=>$param){
                    if($param->checked) $footer2[]= $param->libelle;
                }

                break;       

            case 'couleur':

                $sFiltreTag       = 'coloris';
                $sFiltreValues    = 'colorisids';
                $sFiltreValuesMin = '';
                $sFiltreValuesMax = '';
                $aFiltresElements = $categorie->get_couleur();
                
                $aFiltreValues = array(
                    'colorisids' => '[?url_values]'
                );

                $aFiltreValuesSuppr = array(
                    'colorisids' => null
                );

                $iTypeSelect = 5;
                
                foreach($aFiltresElements as $id=>$param){
                    if($param->checked) $footer2[]=$param->libelle;
                }

                break;

            case 'installation':

                $sFiltreTag       = 'installation';
                $sFiltreValues    = 'emplacementids';
                $sFiltreValuesMin = '';
                $sFiltreValuesMax = '';
                $aFiltresElements = $categorie->getemplacement($articles);
                
                $aFiltreValues = array(
                    'emplacementids' => '[?url_values]'
                );

                $aFiltreValuesSuppr = array(
                    'emplacementids' => null
                );

                $iTypeSelect = 1;

                break;

            case 'origine':

                $sFiltreTag       = 'origine';
                $sFiltreValues    = 'origineids';
                $sFiltreValuesMin = '';
                $sFiltreValuesMax = '';
                $aFiltresElements = $categorie->getorigine($articles);
                
                $aFiltreValues = array(
                    'origineids' => '[?url_values]'
                );

                $aFiltreValuesSuppr = array(
                    'origineids' => null
                );

                $iTypeSelect = 1;

                break;

            case "capacite_lavage":   
                $sFiltreTag       = 'capacitekg';
                $sFiltreValues    = 'capaciteidskg';
                $sFiltreValuesMin = '';
                $sFiltreValuesMax = '';
                $sListeValues     = 'capaciteskg';
                $aFiltresElements = $categorie->getcapacitekg();
                
                $aFiltreValues = array(
                    'capaciteidskg' => '[?url_values]'
                );

                $aFiltreValuesSuppr = array(
                    'capaciteidskg' => null
                );

                $iTypeSelect = 7;
                
                break; 

            case "capacitecouverts": 
                $sFiltreTag       = 'couverts';
                $sFiltreValues    = 'couvertsids';
                $sFiltreValuesMin = '';
                $sFiltreValuesMax = '';
                $sListeValues     = 'couverts';
                $aFiltresElements = $categorie->getcapacitecouverts();
                
                $aFiltreValues = array(
                    'couvertsids' => '[?url_values]'
                );

                $aFiltreValuesSuppr = array(
                    'couvertsids' => null
                );

                $iTypeSelect = 8;
                
                break; 
            case 'volume_cavite':

                $sFiltreTag       = 'capacite';
                $sFiltreValues    = 'capaciteids';
                $sFiltreValuesMin = '';
                $sFiltreValuesMax = '';
                $sListeValues     = 'capacites';
                $aFiltresElements = $categorie->getcapacite();
                
                $aFiltreValues = array(
                    'capaciteids' => '[?url_values]'
                );

                $aFiltreValuesSuppr = array(
                    'capaciteids' => null
                );

                $iTypeSelect = 6;
                
                break;

            case 'prix':

                $sFiltreTag       = 'prix';
                $sFiltreValues    = 'prixids';
                $sFiltreValuesMin = '';
                $sFiltreValuesMax = '';
                $sListeValues     = 'listeprix';
                $aFiltresElements = $categorie->getprix();
                
                $aFiltreValues = array(
                    'prixids' => '[?url_values]'
                );

                $aFiltreValuesSuppr = array(
                    'prixids' => null
                );

                $iTypeSelect = 4;

                $sTraduction1 = $template->translate("/categorie/entre_euro","F");
                $sTraduction2 = $template->translate("/categorie/inferieur_euro","F");
                $sTraduction3 = $template->translate("/categorie/superieur_euro");

                foreach($aFiltresElements as $id=>$param){
                    if($param->checked){
                    $txt=sprintf($template->translate("/categorie/entre_euro","F"),$template->price_format($param->from,0),$template->price_format($param->to,0));
                    if($id==0) $txt=sprintf($template->translate("/categorie/inferieur_euro","F"),$template->price_format($param->to,0));
                    if($id==(count($categorie->listeprix)-2)) $txt=sprintf($template->translate("/categorie/superieur_euro"),$template->price_format($param->from,0));
                    $footer2[]=$txt;
                    }
                  }
                break;
                
            case 'largeur':
            case 'hauteur':
            case 'profondeur':
                $aDimensions = $categorie->getdimids();
                $iTypeSelect = 2;

                $sTraduction1 = $template->translate("/categorie/entre_mm","F");
                $sTraduction2 = $template->translate("/categorie/inferieur_mm","F");
                $sTraduction3 = $template->translate("/categorie/superieur_mm","F");

                if($le_filtre == 'largeur'){
                    $sFiltreTag       = 'largeur';
                    $sFiltreValues    = 'largeurids';
                    $sFiltreValuesMin = 'largeurmin';
                    $sFiltreValuesMax = 'largeurmax';
                    $sListeValues     = 'largeurs';
                    $aFiltresElements = $aDimensions['largeur'];

                    $aFiltreValues = array(
                        'largeurids' => '[?url_values]',
                        'larg_min'   => '[?dim_min]',
                        'larg_max'   => '[?dim_max]'
                    );

                    $aFiltreValuesSuppr = array(
                        'largeurids' => null,
                        'larg_min'   => null,
                        'larg_max'   => null
                    );

                    $input_min    = "larg_min";
                    $input_max    = "larg_max";
                    $tpl_min_name = isset($categorie->largeurmin) && $categorie->largeurmin !== '' ? $categorie->largeurmin : '';
                    $tpl_max_name = isset($categorie->largeurmax) && $categorie->largeurmax !== '' ? $categorie->largeurmax : '';
                } else if($le_filtre == 'hauteur'){
                    $sFiltreTag       = 'hauteur';
                    $sFiltreValues    = 'hauteurids';
                    $sFiltreValuesMin = 'hauteurmin';
                    $sFiltreValuesMax = 'hauteurmax';
                    $sListeValues     = 'hauteurs';
                    $aFiltresElements = $aDimensions['hauteur'];

                    $aFiltreValues = array(
                        'hauteurids' => '[?url_values]',
                        'haut_min'   => '[?dim_min]',
                        'haut_max'   => '[?dim_max]'
                    );

                    $aFiltreValuesSuppr = array(
                        'hauteurids' => null,
                        'haut_min'   => null,
                        'haut_max'   => null
                    );

                    $input_min    = "haut_min";
                    $input_max    = "haut_max";
                    $tpl_min_name = isset($categorie->hauteurmin) && $categorie->hauteurmin !== '' ? $categorie->hauteurmin : '';
                    $tpl_max_name = isset($categorie->hauteurmax) && $categorie->hauteurmax !== '' ? $categorie->hauteurmax : '';
                } else if($le_filtre == 'profondeur'){
                    $sFiltreTag       = 'profondeur';
                    $sFiltreValues    = 'profondeurids';
                    $sFiltreValuesMin = 'profondeurmin';
                    $sFiltreValuesMax = 'profondeurmax';
                    $sListeValues     = 'profondeurs';
                    $aFiltresElements = $aDimensions['profondeur'];

                    $aFiltreValues = array(
                        'profondeurids' => '[?url_values]',
                        'prof_min'      => '[?dim_min]',
                        'prof_max'      => '[?dim_max]'
                    );

                    $aFiltreValuesSuppr = array(
                        'profondeurids' => null,
                        'prof_min'      => null,
                        'prof_max'      => null
                    );

                    $input_min    = "prof_min";
                    $input_max    = "prof_max";
                    $tpl_min_name = isset($categorie->profondeurmin) && $categorie->profondeurmin !== '' ? $categorie->profondeurmin : '';
                    $tpl_max_name = isset($categorie->profondeurmax) && $categorie->profondeurmax !== '' ? $categorie->profondeurmax : '';
                }
                // permet d'avoir les valeurs maximum, pour les slider range
                $val_min=array();
                foreach($aFiltresElements as $element_id => $param){
                    $val_min[]=$param->from;
                    $val_max=$param->to;
                }
              
                $url=$categorie->mkurl($_GET,array("start"=>0,"larg_min"=>null,"larg_max"=>null,"haut_min"=>null,"haut_max"=>null,"prof_min"=>null,"prof_max"=>null));
                $template->parts["li_dimensions"] = ""
                . "<li class=\"filtre_dimensions\">
                 <input type = \"hidden\" value=\"".min($val_min)."\" class = \"min_".$le_filtre."\" />
                   <input type = \"hidden\" value=\"".$val_max."\" class = \"max_".$le_filtre."\" />
                <div class=\"dimension_flex\">
                <input type = \"text\"  id=\"".$input_min."\" value=\"".$tpl_min_name."\" onkeypress=\"nbonly(event,false,this)\" class = \"dim_min\" />
            <input type = \"text\"  id=\"".$input_max."\" value=\"".$tpl_max_name."\" class = \"dim_max\" onkeypress=\"nbonly(event,false,this)\" />
              </div>
             
      </p>
      <div class = \"slider-3-".$sFiltreValues."\"></div>
      <span class=\"ok\" onclick=\"dimvalues('".$url['url']."','".$sFiltreValues."')\">OK</span>
                </li>";

                break;
        }


        //Génération du filtre
        if(count($aFiltresElements) > 0){
                    
            $nb_filtre++;
            $premier_filtre = false;
            if(in_array($nb_filtre, $aPremiersFiltres)) $premier_filtre = true;

           // $aUrl[$sFiltreTag] = $categorie->mkurl($_GET,$aFiltreValues,true);
            $template->parts["premier"]          = $premier_filtre ? 'premier' : '';
            //$template->parts["selectstyle"]	 = $sFiltreTag=="categorie"?"dark":"";
            $template->parts["filtre_shortname"] = $sFiltreTag;
            $filtre_nom = "";
            if($template->translate("/categorie/filtres/hauteur")  === $sFiltreTag || $template->translate("/categorie/filtres/largeur")  === $sFiltreTag || $template->translate("/categorie/filtres/profondeur")  === $sFiltreTag) {
            $filtre_nom = $template->translate("/categorie/filtres/".$sFiltreTag,"F") . " : ";
          }
            $template->parts["all_choices"]      = ''; 
            $template->parts["liste_choices"]    = '';
            $template->parts["filtre_url"]       = $aUrl[$sFiltreTag]['url'];
            $i = 1;
            
            foreach($aFiltresElements as $element_id => $param){

                switch($iTypeSelect){

                    case 1:

                        $count         = 1; //Hack pour le cas 3
                        $sLibelle      = $param->libelle;
                        $sId           = $param->id;
                        $sChecked      = $param->checked ? "checked" : "";
                        $sQte          = $param->qte;
                        $afficheSsMenu = false;
                       
                        break;

                    case 2:

                        $count    = 1; //Hack pour le cas 3
                        $sLibelle = sprintf($sTraduction1, $param->from, $param->to);
                        if($element_id == 0) $sLibelle = sprintf($sTraduction2, $param->to);
                        if($element_id == (count($categorie->$sListeValues)-2)) $sLibelle = sprintf($sTraduction3, $param->from);
                        $sId           = $param->id;
                        $sChecked      = $param->checked ? "checked" : "";
                        $sQte          = $param->qte;
                        $afficheSsMenu = false;
                        
                        break;

                    case 3:
                        $count         = $categorie->articlecount($param[0]->subcatids);
                        $sLibelle      = $param[0]->libelle;
                        $sId           = $param[0]->catid;
                        $sChecked      = in_array($param[0]->catid, $getsubcatids) ? "checked" : "";
                        $sQte          = $count;
                        $afficheSsMenu = true;

                        break;

                    case 4:

                        $count    = 1; //Hack pour le cas 3
                        $sLibelle = sprintf($template->translate("/categorie/entre_euro","F"),$template->price_format($param->from,0),$template->price_format($param->to,0));
                        if($element_id == 0) $sLibelle = sprintf($template->translate("/categorie/inferieur_euro","F"),$template->price_format($param->to,0));
                        if($element_id == (count($categorie->$sListeValues)-2)) $sLibelle = sprintf($template->translate("/categorie/superieur_euro"),$template->price_format($param->from,0));
                        $sId           = $param->id;
                        $sChecked      = $param->checked ? "checked" : "";
                        $sQte          = $param->qte;
                        $afficheSsMenu = false;
                        
                        break;

                    case 5:

                        $count         = 1;
                        $sLibelle      = $template->translate($param->libelle);
                        $sId           = $param->id;
                        $sChecked      = $param->checked ? "checked" : "";
                        $sQte          = $param->qte;
                        $afficheSsMenu = false;
                       
                        break;    

                    case 6:

                        $count         = 1;
                        $sLibelle      = $param->libelle." L";
                        $sId           = $param->id;
                        $sChecked      = $param->checked ? "checked" : "";
                        $sQte          = $param->qte;
                        $afficheSsMenu = false;
                       
                        break;

                    case 7:

                        $count         = 1;
                        $sLibelle      = $param->libelle." Kg";
                        $sId           = $param->id;
                        $sChecked      = $param->checked ? "checked" : "";
                        $sQte          = $param->qte;
                        $afficheSsMenu = false;
                       
                        break; 

                    case 8:

                        $count         = 1;
                        $sLibelle      = $param->libelle." Couverts";
                        $sId           = $param->id;
                        $sChecked      = $param->checked ? "checked" : "";
                        $sQte          = $param->qte;
                        $afficheSsMenu = false;
                       
                        break;    
                }

                $url=$categorie->mkurl($_GET,array($sFiltreValues=>$sId,"start"=>0),true);
                
                if($count > 0){
                    $check  ="<i class=\"far fa-square\" style=\"margin-right: 10px;\"></i>";
                    if($sChecked != ''){ 
                      $template->parts["all_choices"] .= ($template->parts["all_choices"] != '' ? ', ' : '').$sLibelle;
                                            if($sitepro) {
                        $template->parts["style_check"] = "style_check_pro";
                      }
                      else {
                      $template->parts["style_check"] = "";
                    }
                      $check = "<i class=\"fas fa-check\" style=\"margin-right: 10px;\"></i>";
                    }
                    
                    $template->parts["liste_choices"] .= "<li class=\"choix-".$sId."\"><a class=\"choice ".$sChecked."\" href=\"".$url['url']."\" rel=\"".$url['rel']."\" onclick=\"filter_link(this,'".$sFiltreValues."')\">".$check.$sLibelle.($sQte>0?" ($sQte)":"")."</a></li>";
                    $i++;

                    if($afficheSsMenu && count($param) > 1){
                        
                        foreach($param as $p2 => $subcategorie2){

                            if($p2 > 0) $count2 = $categorie->articlecount($subcategorie2[0]->subcatids);
                            if($p2 > 0 && $count2 > 0){
                                $url=$categorie->mkurl($_GET,array($sFiltreValues=>$subcategorie2[0]->catid,"start"=>0),true);
                                $check  ="<i class=\"far fa-square\"></i>";
                                $sschecked=in_array($subcategorie2[0]->catid,$getsubcatids);
                                if($sschecked){
                                    $template->parts["all_choices"] .= ($template->parts["all_choices"] != '' ? ', ' : '').$subcategorie2[0]->libelle;
                                    $check = "<i class=\"fas fa-check\"></i>";
                                }
                                $template->parts["liste_choices"] .= "<li class=\"choix-".$subcategorie2[0]->catid."\"><a class=\"choice".($sschecked ? " checked" : "")." sscat\" href=\"".$url['url']."\" rel=\"".$url['rel']."\">".$check.$subcategorie2[0]->libelle." (".$count2.")</a></li>";
                                $i++;
                               
                            }
                        }
                    }
                }

            }


            $nbcheck = 0;
            if(($iTypeSelect != 3 && $categorie->$sFiltreValues != array()) || ($iTypeSelect == 3 && isset($_GET['subcatids'])) || (isset($categorie->$sFiltreValuesMin) && $categorie->$sFiltreValuesMin != '') || (isset($categorie->$sFiltreValuesMax) && $categorie->$sFiltreValuesMax != '')){
                $display_criteres = true;
                $template->parts["filter_open"]  = 'opened';
                $affiche_liste_filtres = true;

                $aURLSuppr =  $categorie->mkurl($_GET,$aFiltreValuesSuppr,false);

                $template->parts["filtre_suppr_lien"] = $aURLSuppr['url'];
                $template->parts["supp_filtre"]="<a href=\"".$aURLSuppr['url']."\" onclick=\"filter_link(this,'".$sFiltreValues."')\" rel=\"".$aURLSuppr['rel']."\" class=\"fa fa-times-circle fa-lg\"></a>";
                $suppr_desc = '';
 
                if(!empty($categorie->$sFiltreValues)){
                    $i=1;
                  
                    foreach($categorie->$sFiltreValues as $filtre_id){
                        $sLibelleSuppr="";
                        switch($iTypeSelect){

                            case 5:
                                foreach($aFiltresElements as $obj){
                                    if($obj->id == $filtre_id) $sLibelleSuppr = $template->translate($obj->id);                         
                                }
                            break;

                            case 1:

                                $sLibelleSuppr = $aFiltresElements[$filtre_id]->libelle;
                                
                            break;
                            case 6:

                                $sLibelleSuppr = $aFiltresElements[$filtre_id]->libelle." L";
                                
                            break;
                            case 7:

                                $sLibelleSuppr = $aFiltresElements[$filtre_id]->libelle." Kg";
                                
                            break;
                            case 8:

                                $sLibelleSuppr = $aFiltresElements[$filtre_id]->libelle." Couverts";
                                
                            break;

                            case 2:

                                $sLibelleSuppr = sprintf($sTraduction1, $aFiltresElements[$filtre_id]->from, $aFiltresElements[$filtre_id]->to);
                                if($filtre_id == 0) $sLibelleSuppr = sprintf($sTraduction2, $aFiltresElements[$filtre_id]->to);
                                if($filtre_id == (count($categorie->$sListeValues)-2)) $sLibelleSuppr = sprintf($sTraduction3, $aFiltresElements[$filtre_id]->from);

                                break;

                            case 3:

                                foreach($aFiltresElements as $element_id=>$param){
                                    if($param[0]->catid==$filtre_id)
                                    $sLibelleSuppr=$param[0]->libelle;
                                    else{
                                    foreach($param as $p2 => $subcategorie2)
                                        if($p2>0 && $subcategorie2[0]->catid==$filtre_id) $sLibelleSuppr=$subcategorie2[0]->libelle;
                                    }
                                }
                               
                                break;

                            case 4:

                                $sLibelleSuppr = sprintf($sTraduction1, $template->price_format($aFiltresElements[$filtre_id]->from,0), $template->price_format($aFiltresElements[$filtre_id]->to,0));
                                if($filtre_id == 0) $sLibelleSuppr = sprintf($sTraduction2, $template->price_format($aFiltresElements[$filtre_id]->to,0));
                                if($filtre_id == (count($categorie->$sListeValues)-2)) $sLibelleSuppr = sprintf($sTraduction3, $template->price_format($aFiltresElements[$filtre_id]->from,0));

                                break;                                
                        }
                        $nbcheck++;
                        $url=$categorie->mkurl($_GET,array($sFiltreValues=>$filtre_id,"start"=>0), true);

                        if($sitepro) {
                          $colorpro = "libellesuppr_pro";
                        }
                        else {
                          $colorpro = "";
                        }
                          // remplacer le premier "||"" par "&&" pour ne plus avoir les libellessup des filtres catégories
                        if($sLibelleSuppr != "" || ($aFiltresElements[$filtre_id]->to != "" || $aFiltresElements[$filtre_id]->from != "" || $aFiltresElements[$filtre_id]->libelle != "")) $suppr_desc .= ($suppr_desc != '' ? ' ' : '')."<div class='libellesuppr ".$colorpro." libellesup".$sFiltreValues."'>".$filtre_nom.$sLibelleSuppr." <a href=\"".$url['url']."\" rel=\"".$url['rel']."\" onclick=\"filter_link(this,'".$sFiltreValues."')\"><img src='/images/templates/croix.png' alt='delete' /></a></div>";
                        $i++;
                    }
                }
            
                if(isset($categorie->$sFiltreValuesMin)){
                    $sLibelleSuppr = sprintf($sTraduction1, $categorie->$sFiltreValuesMin, $categorie->$sFiltreValuesMax);
                    $suppr_desc .= ($suppr_desc != '' ? ' ' : '')."<div class='libellesuppr ".$colorpro." libellesup".$sFiltreValues."''>".$filtre_nom.$sLibelleSuppr." <a href=\"".$aURLSuppr['url']."\" onclick=\"filter_link(this,'".$sFiltreValues."')\" rel=\"".$url['rel']."\" ><img src='/images/templates/croix.png' alt='delete' /></a></div>";
                }
    
                $template->parts["filtre_suppr_desc"] = $suppr_desc;
                $template->evaltemplate("FiltreSuppression","filtre_suppression");

            }
            //$url_cat=$categorie->mkurl($_GET,array("catid"=>$_GET['catid']));
			$template->parts["reinitialiser_tout"] = $categorie->mkurl(array("catid"=>$categorie->catid))['url'];
			// réinitialiser
			if($nbcheck == 0) {
			  $urlsupp = "#";
			} else {
			  $urlsupp = $aURLSuppr['url'];
			  $onclick = "onclick=\"filter_link(this,'".$sFiltreValues."')\"";
			}
			if($sitepro) {
			  $colorpro = "_pro";
			}

			// affichage différent pour les filtres avec un slider range 
			if($sFiltreValues == "hauteurids" || $sFiltreValues == "profondeurids" || $sFiltreValues == "largeurids") {
				$template->parts["reinitialiser"] = "<div class='flex-reini'><div>". $template->translate("/categorie/filtres/".$sFiltreTag,"F")." ". $template->translate("/filtres/selectionnee") ."</div><div class='reinitialise".$colorpro."'><a href=\"".$aURLSuppr['url']."\"  rel=\"".$aURLSuppr['rel']."\">".$template->translate('/filtres/reinitialiser','F')."</a></div></div>";
			} else { // affichage pour les filtres normaux
				$template->parts["reinitialiser"] = "<div class='flex-reini'><div><span class='selectionne'>".$nbcheck." </span>". $template->translate("/filtres/selectionne")."</div><div class='reinitialise".$colorpro."'><a href=\"".$urlsupp."\" rel=\"".$aURLSuppr['rel']."\">".$template->translate('/filtres/reinitialiser','F')."</a></div></div>";
			}
			$template->parts["all_choices"] = $template->translate("/categorie/filtres/".$sFiltreTag,"F");
            $template->parts["nom_filtre"]="filtre_".$sFiltreValues;
            $template->evaltemplate("FiltreSelect","les_filtres");
        }
    }
}

// Gestion des cases a cocher dans les critères
$bp=$categorie->getbonsplans();

if($bp['deselection']>0){
    $url=$categorie->mkurl($_GET,array("bonsplans"=>"selection","start"=>0),true);
    $selected=(!in_array("selection",$categorie->bonsplans)?"selected":"");
    $template->parts["bpselection"]="<a href=\"".$url['url']."\" class=\"bp selection ".$selected."\">".$template->translate("/categorie/selection","F")." (".$bp['selection'].")</a>";
}

if($bp['promo']>0){
    $categorie->promo_load();
    $template->parts["bppromo"]="<a href=\"".$categorie->promo_url."\" class=\"bp promo hidden-xs\">".$template->translate("/menu/promos","F")." (".$bp['promo'].")</a>";
}

if($bp['idkdos']>0){
    $categorie->idkdos_load();
    $template->parts["bpidkdos"]="<a href=\"".$categorie->idkdos_url."\" class=\"bp idkdos\">".$template->translate("/menu/idees_cadeaux","F")." (".$bp['idkdos'].")</a>";
}

if(count($categorie->bonsplans)>0 || $bp['deselection']>0){
    $url=$categorie->mkurl($_GET,array("bonsplans"=>"selection","start"=>0),false);
    $selected=(in_array("selection",$categorie->bonsplans)?"selected":"");
    $template->parts["bptout"].="<a href=\"".$url['url']."\" class=\"bp tout ".$selected."\">".$template->translate("/categorie/afficher_articles","F")." (".$bp['tout'].")</a>";
}



$template->parts["selection"]="<div class=\"filtres_select\"><a href=\"".$categorie->mkurl(array("catid"=>$categorie->catid))['url']."\" class=\"checked\"><i class=\"fas fa-check\"></i> ".$template->translate("/categorie/filtres/tous_produits","F")." (".$articlecount.")</a></div>";
        
if(!$template->pro){
    $gammelink=array();
    foreach(array("access","premium") as $gamme){
        $gammecat=new categorie($DB_site,$categorie->catid,$template->langue);
        $gammecat->subcategories();
        $gammecat->setparam("pro",($sitepro?2:1));
        $gammecat->setparam("site_code",$template->site_code());
        $gammecat->setparam("pagegamme",$gamme);
        $gammecat->gamme_load();
        $gammeurl=$template->mkurl("gamme",0,$gammecat);

        $nbarticle=count($gammecat->articles("prix",false));
        if($nbarticle>0){
            $gammecat->catid=0;
            $gammecat->gamme_load();
            $gammelink[$gammecat->pagegamme[0]]="<div class=\"filtres_select\"><a href=\"".$gammeurl."\">".$gammecat->gamme_libelle." (".$nbarticle.")</a></div>";
        }
    }
    krsort($gammelink);
    $template->parts["selection"].=implode("",$gammelink);
}
$promocat=new categorie($DB_site,$categorie->catid,$template->langue);
$promocat->subcategories();
$promocat->setparam("pro",($sitepro?2:1));
$promocat->setparam("site_code",$template->site_code());
$promocat->setparam("bonsplans",array("promo"));
$nbarticle=count($promocat->articles("prix",false));
if($nbarticle>0){
    $promocat->promo_load();
    $template->parts["selection"].="<div class=\"filtres_select\" ><a href=\"".$template->mkurl("promo",0,$promocat)."\">".$template->translate("/admin/index/promotions","F")." (".$nbarticle.")</a></div>";
}

$template->evaltemplate("FiltreSelection","filtre_selection");

// Affichage "VOS CRITERES" des criteres selectionnés avec bouton de suppression
if($affiche_liste_filtres){
    $template->evaltemplate("FiltreListe","filtre_liste");
}


if(!$display_criteres){
    $template->parts["filter_open"]  = '';
    $template->parts["les_filtres"]  = str_replace('%display_criteres%','none',$template->parts["les_filtres"]);
} else {
    $template->parts["les_filtres"]  = str_replace('%display_criteres%','block',$template->parts["les_filtres"]);
}


// Affichage des filtres de recherche
if($nb_filtre > 0)
    $template->evaltemplate("CategorieFiltres","liste_filtres");

// Affichage du bouton permettant de montrer/cacher le filtre
// if($nb_filtre > 4)
//     $template->evaltemplate("CategorieOpenFiltre","open_filtres");

// Affichage liste des articles sélectionés par les critères

if ($articlecount > 0 && $articles != false){
    
    $orderselected[str_replace(" ","",$order)] = "selected";

    $articles2 = array_slice($articles,$start,$perpage);

    $cpt = 0;
    $nxtckitems = array();
    foreach($articles2 as $artid)
    {
        $template->parts["class_sup"] = '';
        $noncommandable = "";
        
        $sql = "SELECT commandable, libelle".$template->langue." AS libelle";
        $sql .= " FROM article";
        $sql .= " WHERE artid = ".intval($artid);
        $article = $DB_site->query_first($sql);

        // tracking CRITEO
        if(count($criteoitems) < 3 && $article['commandable'] == '1') $criteoitems[] = $artid;

        //Tracking NextPerformance
        if(count($nxtckitems) < 3 && $article['commandable'] == '1') $nxtckitems[] = $artid;
    
        $remarketing['libelle'][]  = utf8_encode(html_entity_decode($article['libelle']));
        $remarketing['category'][] = utf8_encode($categorie->libelle);
        $remarketing['price'][]    = article_price($artid);


        vignette($artid,"categorie");


    }
}else{
    $url=$categorie->mkurl($_GET,array("haut_min"=>null,"haut_max"=>null,"larg_min"=>null,"larg_max"=>null,"prof_min"=>null,"prof_max"=>null));

    $template->parts["initurl"]=$url['url'];
    $template->evaltemplate("Categorie0Art","articles");

    $template->parts["hidefiltration"]="hidden";
}

if(ESPACE == 'prod' && count($nxtckitems) > 0){
    //Mise en place des tags Nextperformance
    $cpt   = 0;
    $nxtck = '';
    foreach($nxtckitems as $l_item){
        if($cpt > 0) $nxtck .= ',';
        $nxtck .= $l_item;
        $cpt++;
    }
    $template->parts["nextperformance"] = "<script type=\"text/javascript\" src=\"https://nxtck.com/act.php?tag=43959&pid=".$nxtck."\" async></script>";
}
  
// gestion du tracking CRITEO, insertion du script JS pour les categories definies
$asubcatids   = $categorie->subcatids;
$uniqsubcat   = count($asubcatids) == 1;
$selectsubcat = null;

$sql = "SELECT *";
$sql .= " FROM display_categorie";
$sql .= " WHERE display = 'criteo'";
$sql .= " AND catid = ".$categorie->catid;
$incriteo = $DB_site->query_first($sql);                
$jsoncriteitems = json_encode($criteoitems);

// tracking CRITEO
// <<< HEREDOC Here Document permet d'entrer la procedure JavaScript ecrite entre les balise JS directement dans la variable
if($incriteo['catid']!="") 
{
    $template->parts["criteo"]=<<<JS
    <script type="text/javascript" src="//static.criteo.net/js/ld/ld.js" async="true"></script>
    <script type="text/javascript">
    window.criteo_q = window.criteo_q || [];
    window.criteo_q.push(
    { event: "setAccount", account: 2995 },
    { event: "setEmail", email: ["$user_info[mail]"] },
    { event: "setSiteType", type: "d" },
    { event: "viewList", item: $jsoncriteitems }
    );
    </script>
JS;
}

// Affichage sous recherche, barre "amenagement, conseil et promotion"
$encarts=encarts($template->site_code(),$template->langue,"categorie",$categorie->catid);
$i=1;
foreach($encarts as $encart){
    $template->parts['marginright0'] = '';
    $template->parts['boots'] = 'encarts_width';
    if($i==3) $template->parts['boots'] .= " hidden-xs hidden-sm";
    if($i==4){ 
      $template->parts['marginright0'] = 'margin-right: 0;';
      $template->parts['boots'] .= " hidden-xs hidden-sm hidden-md encarts_disp";
    }
    $template->evaltemplate($encart,"encarts");
    $i++;
}

// Permet de positionner correctement la barre d'avertissement sur les cookies en bas de page
include("barrefixe.php");

//Paramétrage des balisees META dans le footer
if(isset($metatitle['cat'])){
    $metatxt = implode(", ",array_unique($metatitle['cat']));
}elseif(isset($metatitle['capacite'])){
    $metatxt = $origincat->ref_title." ".strtolower(implode(" ",$metatitle['capacite']));
}else{
    $metatxt = $origincat->ref_title;
}
if(isset($metatitle['marque'])) $metatxt .= " ".implode(" ",$metatitle['marque']);
$template->parts["footer2"]=$metatxt;


// Suivi google 
$template->parts["remarketing"] = google_remarketing($articles2,"category",'',$remarketing['category'],$remarketing['libelle'],$remarketing['price']);

$template->parts["onload"]="scroll_search();";

//TSI Affiche libelle et marque dans le footer... utile ?
//$template->parts["h2footer"] = "<h2>".$categorie->libelle." ".implode(" ",$footer2)."</h2>";
if(isset($laMarque) && $laMarque !='')
  $marque = ucfirst(strtolower($laMarque[0]))." - ";
else
  $marque = '';

$metadesc = sprintf($template->translate("/categorie/meta_description"),$categorie->libelle);

$template->pageparams($host,$categorie->libelle . " - " . $template->translate("/site/nom"),$metadesc);

$template->evaltemplate("Footer1","footer1");

//pour site part et pro sauf pro en

if(($template->langue == 'EN' && $sitepro) || $dedie !== false || $sitetest){
  $template->parts["follow"]='<meta NAME="ROBOTS" CONTENT="NOINDEX, NOFOLLOW">';
}else{
  $template->parts["follow"]='<meta NAME="ROBOTS" CONTENT="INDEX, FOLLOW, ALL">';
}

// Preparation du corps de la page 
$template->evaltemplate("Categorie","main");
// $template->savecache("main");
// $template->getcache("main");

// Affichage final de la page formate
$template->out();

function mkurl($param,$value,$multi,$start=0){
    $get=$_GET;
    $remove=false;
    $spv=array();
    if($multi){
    if(isset($get[$param])){
        $spv=explode(",",$get[$param]);
        if(in_array($value,$spv)){
        $remove=true;
        $spv2=array();
        foreach($spv as $v) if($v!=$value) $spv2[]=$v;
        $spv=$spv2;
        }
    }
    }else{
    $remove=($get[$param]==$value);
    }

    if(!$remove) $spv[]=$value;
    if($spv[0]==="" && $spv!=array()) array_shift($spv);
    $get[$param]=implode(",",$spv);
    if($param!="start") $get['start']=$start;
    
    $oldurl=oldparam($get);
    if($oldurl!=false) return $oldurl;

    return "/categorie.php?".htmlentities(http_build_query($get));
}

function oldparam($get){
    global $DB_site;
    global $template;
    global $categorie;

    $okparams=array("catid","subcatids","start","sensorder");
    foreach($get as $param=>$value) if($value!="" && !in_array($param,$okparams)) return false;
    if(isset($get['subcatids']) && substr_count($get['subcatids'],",")>0) return false;

    $catid=$get['catid'];
    if(isset($get['subcatids']) && $get['subcatids']!="") $catid=intval($get['subcatids']);
    $cat=$DB_site->query_first("SELECT libelle".$template->langue." FROM categorie WHERE catid=".intval($catid));

    if(!isset($get['start']) || $get['start']==0){
    if($catid!=$categorie->catid)
      return "/".url_rewrite($cat[0])."-cat,".$catid.".htm";
    else
      return $categorie->url;
    }else{
    if($catid!=$categorie->catid)
      return "/tri,".$catid.",".url_rewrite($cat[0]).",".$get['start'].",".$get['sensorder'].".htm";
    else
      return $categorie->url."?start=".$get['start']."&sensorder=".$get['sensorder'];
    }

}

?>
