<?php
//Vérification du statut des articles
function verif_articles($artid = null){
     global $DB_site2;
     global $langues;
     global $rootpath;
     global $tabCatExclusionFiltres;

     $return = array();

     $sql = "SELECT *";
     $sql .= " FROM article";
     $sql .= " WHERE catid NOT IN (".implode(',',$tabCatExclusionFiltres).",-1)"; //Ne prend pas en compte les pièces détachées ILVE, car trop de produits
     if(!is_null($artid)) $sql.=" AND artid = ".$artid;
     $rq = $DB_site2->query($sql);
     
     $sql = "SELECT garantieid, marques, types_produits FROM garantie";
     $req = $DB_site2->query($sql);
     while($row = $DB_site2->fetch_array($req)){
          
          $tabGarantie[] = $row;
          
     }
     
     //Boucle sur tous les articles
     while($article = $DB_site2->fetch_array($rq)){
          
          //Vérification de l'attribution à une marque
          $sql = "SELECT marqueid, libelle";
          $sql .= " FROM marque";
          $sql .= " INNER JOIN article_marque USING(marqueid)";
          $sql .= " WHERE artid = ".$article['artid'];
          $marque = $DB_site2->query_first($sql);
          if(empty($marque['libelle'])) $return[$article['artid']]['Références > Marque'] = "Sans marque";
          
          //Vérification de l'attribution d'une référence interne
          if($article['artcode'] == "") $return[$article['artid']]['Références > Ref Acilyon']="Référence vide";
          
          //Vérification de l'attribution d'une référence fabricant
          if($article['artcode_eurocave']=="") $return[$article['artid']]['Références > Ref constructeur']="Référence vide";
          
          //Vérification de la présence d'un libellé produit pour chaque langue du site
          foreach($langues as $langue)
               if($article['libelle'.$langue]=="") $return[$article['artid']]['Références > Libellé '.$langue] = "libellé vide";
          
          //Vérification de l'attribution d'un type article
          if($article['typearticleid']==0) $return[$article['artid']]['Références > Type article'] = "non défini";
          
          //Vérification de l'attribution des prix
          if($article['prix']==0) $return[$article['artid']]['Prix > Prix vente']="Prix à 0";
          if($article['prixconseilleht'] == 0) $return[$article['artid']]['Prix > PPI']="Prix à 0";

          //if($article['delailivraison']==0) $return[$article['artid']]['Délai et visibilité > Délai livraison']="délai à 0 jour";
          
          //Vérification de l'attribution des coloris
          if($article['coloris']=="") $return[$article['artid']]['Coloris fabricant']="Pas de coloris";
          if($article['coloris_categorie']=="") $return[$article['artid']]['Coloris catégorie']="Pas de coloris";

	  //Si l'article n'est pas un accessoire, on vérifie l'attribution du niveau de finition et des dimensions/poids
          if($article['typearticleid'] != 20){
               
               //Vérification finition
               $sql = "SELECT COUNT(*)";
               $sql .= " FROM articlerecherche_finition";
               $sql .= " WHERE artid = ".$article['artid'];
               $finition = $DB_site2->query_first($sql);
               if($finition[0]==0) $return[$article['artid']]['Finition']="Pas de finition sélectionnée";
               
               //Vérification dimensions/poids
               $dim = array(
                   "hauteur",
                   "largeur",
                   "profondeur",
                   "poid"
               );
               
               foreach($dim as $var){
                    if($article[$var]=="") $return[$article['artid']][$var]=$var." vide";
                    if($article[$var]==0) $return[$article['artid']][$var]=$var." à 0";
               }
          }
         
          if($article['emballage_poids1'] + $article['emballage_poids2'] + $article['emballage_poids3']==0) $return[$article['artid']]['Poids brut']="Poids brut à 0";
          
          //Vérification des garanties
          foreach($tabGarantie as $la_garantie){
               
               $garantiePossible = false;
               
               $tabMarque = explode(',', $la_garantie['marques']);
               $tabType   = explode(',', $la_garantie['types_produits']);
               
               $marque_valide = false;
               foreach($tabMarque as $la_marque){
                    if($la_marque == $marque['marqueid']){
                         $marque_valide = true;
                         break;
                    }
               }
               
               $type_valide = false;
               foreach($tabType as $le_type){
                    if($le_type == $article['typearticleid']){
                         $type_valide = true;
                         break;
                    }
               }
               
               if($marque_valide && $type_valide){
                    $garantiePossible = true;
                    break;
               }
               
          }
          
          if($garantiePossible){
               
               $erreurGar = true;
               
               $sql = "SELECT id";
               $sql .= " FROM article_garantie_liste agl";
               $sql .= " INNER JOIN garantie g ON (g.garantieid = agl.garantieid)";
               $sql .= " WHERE agl.artid = ".$article['artid']; 
               $req = $DB_site2->query($sql);
               
               if($DB_site2->num_rows($req) > 0) $erreurGar = false;
               
          }
          
          if($garantiePossible && $erreurGar){
               $return[$article['artid']]['Garantie']="Pas de garantie";
          }
          
          //Commenté car mal optimisé => gros temps de chargement
          
          /*include_once(SITE_PATH.'/class/DAOarticle.class.php');
          include_once(SITE_PATH.'/class/garantie.class.php');
          include_once(SITE_PATH.'/class/DAOgarantie.class.php');

          $lesGarantiesPos = DAOgarantie::loadGarantiesByTypeMarque($DB_site2,'FR',DAOarticle::typeArticleByIDart($DB_site2,'FR',$article['artid'])["typearticleid"],DAOarticle::marqueArticleByIDart($DB_site2,'FR',$article['artid'])["marqueid"]);
          $lesGaranties    = DAOgarantie::checkGarantiesArt($DB_site2,'FR',$article['artid']);
	 
          if(count($lesGarantiesPos)>0 && !is_array($lesGaranties)) $return[$article['artid']]['Garantie']="Pas de garantie";*/

          //$infoFournValide=$DB_site2->query_first("SELECT MIN(delai), MIN(prix) FROM article_fournisseur WHERE artid=".$article['artid']." AND principal=1;");
          if($article['delailivraison']==0 || $article['paht']==0) $return[$article['artid']]['Prix > Fournisseur principal']="délai et/ou prix égal 0";

          if($article['typearticleid']!=0){
               
               $sql = "SELECT *";
               $sql .= " FROM typearticle";
               $sql .= " WHERE typearticleid = ".$article['typearticleid'];
               $typearticle = $DB_site2->query_first($sql);
               
               if($typearticle['onglets'] != ""){
                    
                    $tabs = explode(",",$typearticle['onglets']);
                    
                    foreach($tabs as $tab){
                        
                         if($tab != 'pack'){
                              
                              if(file_exists($rootpath."class/articles/article_".$tab.".php")) include_once($rootpath."class/articles/article_".$tab.".php");
                              $functionfields = "article_".$tab."_fields";
                              $article_fields = $functionfields($article['typearticleid']);
                              $keys = array_keys($article_fields);
                              
                              if (!is_null($article_fields[$keys[0]][0])) $article_fields = array(array($article_fields));
                              
                              if($tab == "cuisiniere") $tab="piano";
                              
                              $sql = "SELECT *";
                              $sql .= " FROM article_".$tab;
                              $sql .= " WHERE artid = ".$article['artid'];
                              $r = $DB_site2->query($sql);
                              
                              while($tabdata=$DB_site2->fetch_array($r)){
                                   
                                   foreach($article_fields as $type => $groups) {
                                        
                                        foreach($groups as $group => $fields) {
                                                  
                                             foreach($fields as $field => $prop) {
                                                  
                                                  list($tr, $label, $suffix, $req,$info) = $prop;
                                                  if($req && empty($tabdata[$field])) $return[$article['artid']][$tab." > ".$group." > ".$field]="Champ vide";
                                                  
                                             }
                                        }
                                   }
                                   
                                   if($tab=="table_cuisson"){
                                        
                                        $foyer_fields = article_foyers_fields($article['typearticleid']);
                                        $foyerid = 1;
                                        
                                        $sql = "SELECT *";
                                        $sql .= " FROM article_foyers";
                                        $sql .= " WHERE table_id = ".$tabdata['id'];
                                        $foyers = $DB_site2->query($sql);
                                        
                                        while($foyer = $DB_site2->fetch_array($foyers)){
                                             foreach($foyer_fields as $field=> $prop) {
                                                 list($tr, $label, $suffix, $req,$info) = $prop;
                                                 if($req && empty($foyer[$field])) $return[$article['artid']][$tab." > Foyer $foyerid > ".$field]="Champ vide";
                                             }
                                             $foyerid++;
                                        }
                                        
                                   }
                              }
                         }
                    }
               }
          }
     }
     
     return $return;
}

function verif_article_format($verifs){
    global $DB_site2;

    if($verifs==array()) return "";

    $return="";
    foreach($verifs as $artid=>$verif){
	$article=$DB_site2->query_first("SELECT artcode FROM article WHERE artid=".$artid);
	$return.="<table class=\"error_table\">
	<thead>
	<tr><td colspan=\"2\">Erreurs ".$article['artcode']."</td></tr>
	</thead>";
	foreach($verif as $field=>$msg)
	    $return.="<tr><td>$field</td><td>$msg</td></tr>";
	$return.="</table>";
    }
    
    return $return;
}

?>