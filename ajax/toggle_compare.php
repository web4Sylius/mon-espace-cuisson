<?php
// header('Content-Type: text/html; charset=utf8');
require_once($_SERVER['DOCUMENT_ROOT']."/global.php");

$artid = intval($_GET['artid']);

if(isset($artid) && $artid > 0){
  // $artid=intval($_GET['artid']);

  //Variable utile uniquement dans le cas d'une suppression de produit dans le comparateur
  $categorie_return_id = 0;

  $comparateur=new comparateur($DB_site);

  $ajout = true;
  $return_ajout = 0;
  $btn_article_compare="";
  //Si aucun produit dans le comparateur, pas besoin de faire la boucle de comparaison
  $article_comparateur=$comparateur->select_article($artid);
    if($article_comparateur!=false){
    	$categorie_return_id = $article_comparateur->typearticleid == 10 ? 1 : $article_comparateur->typearticleid;
        $return_ajout=2;
        $ajout=false;
        $className = 'btn_comparer_off';
        $check = 'far fa-square';
        $btn_article_compare = $template->translate("/comparateur/comparer","F");
        $comparateur->remove_article($article_comparateur->artid);
    }

    if($comparateur->count_typearticle($artid)>=4){
        $className = 'btn_comparer_off';
        $check = 'far fa-square';
        $btn_article_compare = $template->translate("/comparateur/comparer","F");
        $return_ajout=3;
        $ajout=false;
        
    }

//Produit n'est pas dans le comparateur : on l'ajoute
    if($ajout && $artid != 0){
        $added_article=$comparateur->add_article($artid,$user_info['userid']);
    	$categorie_id = $added_article->typearticleid == 10 ? 1 : $added_article->typearticleid;
        
        $className = 'btn_comparer_on';
        $check = 'fas fa-check';
        $return_ajout = 1;
        $btn_article_compare = $template->translate("/comparateur/ajoute_au_comparateur","F");

        $categorie_return_id = $categorie_id;   
    }
}
//Construction du contenu de la div du footer
$nbPdts=count($comparateur->articles());
if($nbPdts>0){
    $return = '<ul>';
    
    $articles_types=$comparateur->articles(true);
    $cnt=1;
    foreach($articles_types as $typearticleid=>$articles){
	$classLI = 'orange';
	if($cnt%2 == 0){
	      $classLI = '';
	}
    
	$return .=  "<li>"
				  . "<a href=\"javascript:void(0);\" onclick=\"tb_show('".$template->translate("/comparateur/comparer_les_articles","F")."','/comparateur.php?width=945&typearticleid=".$typearticleid."',false);\">".$template->translate("/article/typearticle/".$typearticleid,"F")." </a>"
				  . "<br /><div class=\"bloc-comparateur\">";
	foreach($articles as $date=>$article){
	    $return.="<div class=\"visuel_comparateur\" onclick=\"tb_show('".$template->translate("/comparateur/comparer_les_articles","F")."','/comparateur.php?width=945&typearticleid=".$article->typearticleid."',false);\">"
					    . "<img src=\"/images/".$article->artid."_100_100.jpg\">"
				      . " </div>";
	}
	$cnt++;
	$return.="</div><div class=\"btn_compare\" onclick=\"tb_show('".$template->translate("/comparateur/comparer_les_articles","F")."','/comparateur.php?width=945&typearticleid=".$article->typearticleid."',false);\">".$template->translate('/barrefixe/comparer','F')."</div><div class=\"btn_compare\" onclick=\"vider_comparateur(1);\">".$template->translate('/barrefixe/vider','F')."</div><div class=\"clear\"></div></li>";
    }
    
    $return .= '</ul>';
    
    $nbPdtsCategorie=count($articles_types[$categorie_return_id]);
    

/* if(isset($_SESSION['comparateur']) && count($_SESSION['comparateur']) > 0){
     //Construction de la liste
     $return = '<ul>';
     $cnt = 1;
     foreach($_SESSION['comparateur'] as $categorie_id => $tabArticle){
          $classLI = 'orange';
          if($cnt%2 == 0){
               $classLI = '';
          }
          
          $categorie_nom="cave";
          
          
          //R?cup?ration de la liste des visuels
          $liste_visuels = "";

          if($categorie_id == $detailsArticle['typearticleid'] || !$ajout || $artid == 0){
               foreach($tabArticle as $article_id){
                    $liste_visuels .= "<div class=\"visuel_comparateur\" onclick=\"tb_show('Comparer<br>les articles','/comparateur.php?typearticleid=".$categorie_id."',false);\">"
                                        . "<img src=\"/images/".$article_id."_100_100.jpg\">"
                                   . " </div>";
               }
          
          
               $return .=  "<li class=\"".$classLI."\">"
                              . "<a href=\"javascript:void(0);\" onclick=\"tb_show('Comparer<br>les articles','/comparateur.php?typearticleid=".$categorie_id."',false);\">".$categorie_nom." (".count($_SESSION['comparateur'][$categorie_id]).")</a>"
                              . "<br />"
                              . $liste_visuels
                         . "</li>";
               $cnt++;
          }
          
          $nbPdts += count($_SESSION['comparateur'][$categorie_id]);
     }
     $return .= '<ul>';*/
     
     //Cr?ation de l'intitul? du bouton comparateur
     
     $txtPopup=$template->translate("/comparateur/selectionnez_type_article","F");
}else {
     //Cr?ation de l'intitul? du bouton comparateur
     $txtPopup = '<center>'.$template->translate("/comparateur/aucun_article_dans_comparateur","F").'</center>';
}

$txtComparateur = $nbPdts;

// $nbPdtsCategorie = count($_SESSION['comparateur'][$categorie_return_id]);
        
echo $artid.'|||'.$className.'|||'.$return.'|||'.$txtComparateur.'|||'.$categorie_return_id.'|||'.$nbPdtsCategorie.'|||'.$nbPdts.'|||'.$return_ajout.'|||'.$btn_article_compare.'|||'.$txtPopup.'|||'.$check;