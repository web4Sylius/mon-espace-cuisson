<?php

setcookie("transpo");
require ("global.php");


$title="title".$template->langue;
$libelle="libelle".$template->langue;
$host=$template->parts["host"];
//ici on fais afficher les balise hreflang pour fr et en
foreach($template->sites() as $site_code=>$site){
    foreach($site['hosts'] as $url=>$deflangue){
    if($site['actif'][$url])
        $template->parts["alternate"].="<link rel=\"alternate\" hreflang=\"".$site['locales'][$deflangue]."\" href=\"https://www.".$url."/cde1.php\">\n";
    }
}

if($_SERVER['HTTP_REFERER']=="" || strpos($_SERVER['HTTP_REFERER'],"cde1.php")!=false) {
    $_SERVER['HTTP_REFERER']="/";

}
$template->parts["returnurl"]=$_SERVER['HTTP_REFERER'];

$sql = "SELECT count(*)";
$sql .= " FROM lignepanier";
$sql .= " WHERE panierid = '".$panier['panierid']."'";
$sql .= " AND panierid != 0";
$lignepaniercount=$DB_site->query_first($sql);

$template->parts["taxtxt"]=$template->translate(($sitepro?"/site/ht":"/site/ttc"),"U");

$lstartid=array();

$criteocat=array();
$requete=$DB_site->query("SELECT * FROM display_categorie WHERE display='criteo'");
while($dc=$DB_site->fetch_array($requete)){
    $criteocat[]=$dc['catid'];
}

$remarketing=array("libelle"=>array(),"price"=>array(),"category"=>array());

$ral=new ral($DB_site);
$ral->langue=$template->langue;
$ral->select_function="panier_ral_select";

if($template->pro)
    $template->parts['autocolor'] = "#0393c2";
else
    $template->parts['autocolor'] = "#ac8b5a";

$nxtckitems = array();
//Si il y a 1 ou plus d'articles dans le panier
if ($lignepaniercount[0] != 0) {
    $TemplateCde1BitDynamique = "" ;
    $criteoitems=array();
    $criteonb=0;
    $i=0;
    $prixtva = 0;
    $prixtotalpanier = 0;
    $totalht=0;
    $ajd = date('Y-m-d') ;
    
    
    // pays pour définir le taux de TVA
    if ($_COOKIE['userid'] != ""){
    $pays_user=$DB_site->query_first("select paysid,tvaintracom from utilisateur where userid = '".$_COOKIE['userid']."'");
    $paysid = $pays_user['paysid'] ;
    }elseif ($user_info['userid'] != ""){
    $pays_user=$DB_site->query_first("select paysid,tvaintracom from utilisateur where userid = '".$user_info['userid']."'");
    $paysid = $pays_user['paysid'] ;
    }else{
    $paysid = "57" ;
    }
    $tvafrance=$DB_site->query_first("select 0 AS taux0,TVAtauxnormal AS taux1, TVAtauxreduit AS taux2 from pays where paysid = '57'");
    $montauxtva=$DB_site->query_first("select 0 AS taux0,TVAtauxnormal AS taux1,TVAtauxreduit AS taux1,cee,tvaintracom from pays where paysid = '$paysid'");
    $packs=new Pack($DB_site);
    
    $lignepaniertmps=$DB_site->query("SELECT l.qte, a.artid, a.artcode, a.delailivraison, a.ecopart, a.prix, a.tva, a.tauxchoisi, a.libelleFR, a.libelleEN,
    a.fraisgestionid,a.garantie_incluse, l.lignepanierid, image, a.date_appro, a.catid FROM lignepanier l INNER JOIN article a on (a.artid=l.artid) 
    WHERE panierid = '$panier[panierid]' ORDER BY l.lignepanierid ASC");
    
    $n = 1;
    $nb_lignes_panier = $DB_site->num_rows($lignepaniertmps);
    $default = false;
    $txt ='';
    $nvinscons = 0;
    /**************************************************************************************************************************************//////////////////
    //on boucle sur chaque ligne du panier*******************************************************************////////////////////////////////////////////////
    while ($lignepaniertmp=$DB_site->fetch_array($lignepaniertmps)){
        $lstartid[]=$lignepaniertmp['artid'];
        $table_article = table_typearticle($DB_site,$lignepaniertmp[artid]);
        $libmarque = $DB_site->query_first("SELECT * from article_marque am, marque m where am.marqueid = m.marqueid and am.artid ='$lignepaniertmp[artid]'");
        $article = $DB_site->query_first("SELECT a.libelle".$template->langue.", a.active, spec.* FROM article a INNER JOIN $table_article AS spec USING(artid) WHERE a.artid='".$lignepaniertmp['artid']."'");
        $article['marque']=$libmarque['libelle'];

        //on vérifie si le produit est bien actif
        if(intval($article['active']) == 1 ){

            $art_rangements=$DB_site->query("SELECT * FROM article a INNER JOIN article_rangement ac ON(a.artid = ac.artid_rangement) INNER JOIN article_site a_s ON(a.artid=a_s.artid) WHERE ac.artid = '".$lignepaniertmp['artid']."' AND a_s.site_code='".$template->site_code()."' AND a_s.active = '1' AND commandable='1' GROUP BY a.artid ORDER BY a.artcode");

            //$art_vins=$DB_site->query("SELECT * FROM article a INNER JOIN vins_associes va ON(a.artid = va.artid_vin) INNER JOIN article_site a_s ON(a.artid=a_s.artid) WHERE va.artid = '".$lignepaniertmp['artid']."' AND a_s.site_code='".$template->site_code()."' AND a_s.active = '1' AND commandable='1' GROUP BY a.artid ORDER BY a.artcode");

            if($n == $nb_lignes_panier)
                $bas_tab = 'true';
            else
                $bas_tab = 'false';

            if($template->langue == 'FR'){
                    $lib = 'accessoires conseill&eacutes';
                    $style = '';
                }elseif($template->langue){
                    $lib = 'Recommended accessories';
                    $style = "style=\"font-size:10px;\"";
                }
                else{
                    $lib = 'accessoires conseill&eacutes';
                    $style = '';
                }

            $nb_acces_ligne_panier = $DB_site->num_rows($art_rangements);
            $id_ligne = $lignepaniertmp['lignepanierid'];
            if($nb_acces_ligne_panier > 0){
                if($txt == ''){ 
                    $default = true;
                    $txt = $id_ligne;
                }else{
                    $default = false;
                }

                $accesPanier = "<span id=\"panier_accessoires_".$id_ligne."\" class=\"panier_accessoires hidden-xs\" ".$style." onclick=\"afficheAccessoiresPanier('".$id_ligne."',".$bas_tab.")\"><span id=\"chev_left_".$id_ligne."\" class=\"fa fa-chevron-down\" style=\"margin-right:2px;\"></span>".$lib."<span id=\"chev_right_".$id_ligne."\" class=\"fa fa-chevron-down\" style=\"margin-left:2px;\"></span></span>";
                $default++ ;
            }
            else
                $accesPanier ='';


            $td1="";
            $isPack = false;
            $artidPack = array();
            if($packs->isPack($lignepaniertmp['artid'])){
                $isPack = true;
                $prices=$packs->prices($lignepaniertmp['artid'],$template->tva_ref(),false);
                foreach($packs->getProduits($lignepaniertmp['artid']) as $art){
                    $artidPack[] = $art['artid'];
                    // on recupere l'url de l'article pour le lien sur le libelle ou l'image
                    $myArt = $DB_site->query_first("SELECT a.libelle".$template->langue.", m.libelle as marque FROM article a INNER JOIN article_marque am ON a.artid=am.artid INNER JOIN marque m ON am.marqueid=m.marqueid WHERE a.artid='".$art['artid']."'");
                    $urlArt=$template->mkurl('article',$art['artid'],$myArt);
                    $art_rangements_pack = $DB_site->query("SELECT * FROM article a INNER JOIN article_rangement ac ON(a.artid = ac.artid_rangement) INNER JOIN article_site a_s ON(a.artid=a_s.artid) WHERE ac.artid = '".$art['artid']."' AND a_s.site_code='".$template->site_code()."' AND a_s.active = '1' AND commandable='1' GROUP BY a.artid ORDER BY a.artcode");

                    $nb_acces_ligne_panier_pack = $DB_site->num_rows($art_rangements_pack);
                    if($nb_acces_ligne_panier_pack > 0){
                        if($txt == ''){ 
                            $default = true;
                            $txt = $id_ligne;
                        }else{
                            $default = false;
                        }
                        $accesPanierPack = "<span id=\"panier_accessoires_".$id_ligne."\" class=\"panier_accessoires accesPack hidden-xs\" ".$style." onclick=\"afficheAccessoiresPanier('".$id_ligne."',".$bas_tab.")\"><span id=\"chev_left_".$id_ligne."\" class=\"fa fa-chevron-down\" style=\"margin-right:2px;\"></span>".$lib."<span id=\"chev_right_".$id_ligne."\" class=\"fa fa-chevron-down\" style=\"margin-left:2px;\"></span></span>";
                        $default++ ;
                    }
                    else
                        $accesPanierPack ="";

                    $td1 .= "<div class=\"packart\" >
                    <a href=\"".$urlArt."\"><img src=\"".article_image($art['artid'],47,84)."\"></a>
                    <div class=\"libelle\" ><a href=\"".$urlArt."\" style=\"color:black;\">".$art[$libelle]."</a></div>
                    $accesPanierPack
                    <div class=\"clear\"></div>
                    </div>";
                }
                    $td1.="<span class=\"ref spec\">Ref : $lignepaniertmp[artcode]</span>";
            }else{
                // on recupere l'url de l'article pour le lien sur le libelle ou l'image
                $myArt = $DB_site->query_first("SELECT a.libelle".$template->langue.", m.libelle as marque FROM article a INNER JOIN article_marque am ON a.artid=am.artid INNER JOIN marque m ON am.marqueid=m.marqueid WHERE a.artid='".$lignepaniertmp['artid']."'");
                $urlArt = $template->mkurl('article',$lignepaniertmp['artid'],$myArt);
                $prices=article_prices($lignepaniertmp['artid'],$template->tva_ref(),$template->site_code());
                $td1="<a href=\"".$urlArt."\"><img src=\"".article_image($lignepaniertmp['artid'],47,84)."\"></a>
                <div class=\"libelle\" style=\"margin:0 0 6px 6px;\">
                 <a href=\"".$urlArt."\" style=\"color:black;\">$lignepaniertmp[$libelle]</a>
                  <span class=\"marque\">$libmarque[libelle]</span>
                  <span class=\"ref\" style=\"margin-bottom:10px;\">Ref : $lignepaniertmp[artcode]</span>
                  $accesPanier
                </div>";
            }
        
            $lignepaniertmp['prix']=$prices['prix'];

            $nxtckitems[] = $lignepaniertmp['artid'];
        
            if(in_array($lignepaniertmp['catid'],$criteocat)){
                $criteoitems[]=array("id"=>strval($lignepaniertmp['artid']),"price"=>(float)number_format($lignepaniertmp['prix'],2,'.',''),"quantity"=>(int)$lignepaniertmp['qte']);
            }
            
            $texteecopart="";
            if ($lignepaniertmp['ecopart']!=0){
                $texteecopart=$template->translate("/commande/dont_ecopart","F")." : $lignepaniertmp[ecopart] &euro;<br>";
            }       

            // mofidication du prix en f° des caractéristiques
            $sql = "SELECT c.".$libelle." as libelle_caract, cv.".$libelle." as libelle_caractval, cv.ref, lpc.caractvalid";
            $sql .= " FROM lignepaniercaracteristique lpc";
            $sql .= " INNER JOIN caracteristiquevaleur cv ON (lpc.caractvalid = cv.caractvalid)";
            $sql .= " INNER JOIN caracteristique as c ON (cv.caractid = c.caractid)";
            $sql .= " WHERE lignepanierid = '".$lignepaniertmp['lignepanierid']."'";
            $rq_lignepaniercaracteristique = $DB_site->query($sql);
            $modif_px=0;
            while ($rs_lignepaniercaracteristique = $DB_site->fetch_array($rq_lignepaniercaracteristique)){   

                $liste_ral=$DB_site->query_first("SELECT caractvalid FROM caractval_ral WHERE caractvalid = '".$rs_lignepaniercaracteristique['caractvalid']."'");
                ///si l'option est une couleur RAL :
                if(!is_null($liste_ral)){
                    $caracteid = $DB_site->query_first("SELECT caractid FROM `caracteristiquevaleur` WHERE `caractvalid` = '".$liste_ral['caractvalid']."'");
                    $caracvals = $DB_site->query("SELECT caractvalid FROM `caracteristiquevaleur` WHERE `subcaractid` = ".$caracteid['caractid']);
                    $caracvals_liste="";
                    foreach($caracvals as $caracv){
                        $caracvals_liste[]=$caracv['caractvalid'];
                    }
                    $caracvals=implode("','",$caracvals_liste);
                    $caracval = $DB_site->query_first("SELECT caractvalid FROM `article_caractval` WHERE `artid` = '".$lignepaniertmp['artid']."' AND `caractvalid` IN ('".$caracvals."')");
                    $sql = "SELECT ac.differenceprix, ac.achat, cv.prix_fixe, cv.pvttc, cv.pvht, cv.paht, c.libelleFR as libelleral";
                    $sql .= " FROM article_caractval ac INNER JOIN caracteristiquevaleur cv ON cv.caractvalid = ac.caractvalid INNER JOIN caracteristique c ON cv.caractid = c.caractid";
                    $sql .= " WHERE ac.caractvalid = '".$caracval['caractvalid']."'";
                    $sql .= " AND ac.artid = '".$lignepaniertmp['artid']."'";
                    $caract_diff_px = $DB_site->query_first($sql);
                    $rs_lignepaniercaracteristique['caractvalid']=$caracval['caractvalid'];
                }
                else{
                  $sql = "SELECT ac.differenceprix, ac.achat, cv.prix_fixe, cv.pvttc, cv.pvht, cv.paht, c.libelleFR as libelleral";
                  $sql .= " FROM article_caractval ac INNER JOIN caracteristiquevaleur cv ON cv.caractvalid = ac.caractvalid INNER JOIN caracteristique c ON cv.caractid = c.caractid";
                  $sql .= " WHERE ac.caractvalid = '".$rs_lignepaniercaracteristique['caractvalid']."'";
                  $sql .= " AND ac.artid = '".$lignepaniertmp['artid']."'";

                  $caract_diff_px = $DB_site->query_first($sql);
              }
              //Si lot, on additionne les prix
              $lot_prix=array();
              $caractvaltotal=array();
              $req_lot=$DB_site->query("SELECT artid,qte FROM article_lot WHERE lotid=".$lignepaniertmp['artid']);
              while($art_lot=$DB_site->fetch_array($req_lot)){
                  $lot_prix[]=array($art_lot['artid'],$art_lot['qte']);
              }
              if($lot_prix!=array()){
                foreach($lot_prix as $articleid => $art_val){
                    $art=$DB_site->query_first("SELECT differenceprixht, differenceprix, cv.prix_fixe, cv.pvht, cv.pvttc, cv.paht FROM article_caractval INNER JOIN caracteristiquevaleur as cv USING(caractvalid)WHERE caractvalid=".$rs_lignepaniercaracteristique['caractvalid']." AND artid=".intval($art_val[0]));
                    if($art!=""){
                        if($art['prix_fixe'] == '1'){
                            $caractvaltotal["prixlot"]=$caractvaltotal["prixlot"] + $art['pvttc']*$art_val[1];
                        }
                        else{
                            $diffprix = unserialize($art["differenceprix"]);
                            $caractvaltotal["prixlot"]=$caractvaltotal["prixlot"] + $diffprix['base']*$art_val[1];
                        }
                    }
                }
            }
            if(isset($caractvaltotal["prixlot"])){
                $caract_prix=round($caractvaltotal["prixlot"],4);
            }
            else{
                if($caract_diff_px['prix_fixe'] == '1'){
                    $caract_prix=caract_prices($caract_diff_px['pvttc'],$template->tva_ref());
                }
                else{
                    $caract_prix=caract_prices($caract_diff_px['differenceprix'],$template->tva_ref());
                }
            }

            $modif_px+=$caract_prix;
        }

            $lignepaniertmp['prix'] += $modif_px ;          
                
            $prixligne = $lignepaniertmp['prix'] * $lignepaniertmp['qte'];
            $totalht+=$prixligne/(1 + $tvafrance['taux'.$lignepaniertmp['tauxchoisi']]/100);
          
            $prixtva+=$prixligne*(1-(100/(100+$tvafrance['taux'.$lignepaniertmp['tauxchoisi']])));
            if($sitepro || $montauxtva['taux'.$lignepaniertmp['tauxchoisi']]==0 || ($pays_user['tvaintracom']!="" && $montauxtva['tvaintracom']==1))
            $prixligne = $prixligne / (1 + $tvafrance['taux'.$lignepaniertmp['tauxchoisi']]/100);
            
            $prixtotalpanier += $prixligne ;
            $template->parts["prixligne"] = $template->price_format($prixligne);
            // frais de gestion
            $fraisgestionid[] = $lignepaniertmp['fraisgestionid'];
            // caractéristiques
            $TemplateCde1BitCaracteristiquesBit = "" ;
            $TemplateCde1BitGarantieBit="";
            unset ($tab_lignepaniercaracteristique) ;
            $tab_lignepaniercaracteristique = array () ;
            $caractvalname_select="";
            
            $template->parts["options"]="";
      
            $i = 0;
            $j = 0;
            $rq_lignepaniercaracteristique=$DB_site->query("SELECT c.*, lpc.caractvalid FROM lignepaniercaracteristique lpc INNER JOIN caracteristiquevaleur cv ON cv.caractvalid = lpc.caractvalid INNER JOIN caracteristique c ON c.caractid = cv.caractid 
			WHERE lpc.lignepanierid = '$lignepaniertmp[lignepanierid]' ORDER BY position" );
            while ($rs_lignepaniercaracteristique=$DB_site->fetch_array($rq_lignepaniercaracteristique))
               $tab_lignepaniercaracteristique[] = $rs_lignepaniercaracteristique['caractvalid'] ;
                
            $rq_caracteristiques=$DB_site->query("SELECT c.caractid, c.$libelle FROM caracteristique as c 
            INNER JOIN caracteristiquevaleur cv USING(caractid)
            INNER JOIN article_caractval ac USING(caractvalid) WHERE ac.artid='$lignepaniertmp[artid]' AND (select count(subcaractid) from caracteristiquevaleur where subcaractid=cv.caractid)=0 GROUP BY c.caractid ORDER BY c.position") ;
            while ($rs_caracteristiques=$DB_site->fetch_array($rq_caracteristiques)){ // ttes caracteristiques de l'article
                $test[] =
                $valeurs_caracteristique_zonedeliste = "" ;
                $radio_caract="";
                $rq_caracteristique_valeurs=$DB_site->query("SELECT cv.caractvalid, cv.$libelle,ac.differenceprix, cv.prix_fixe, cv.pvttc FROM caracteristiquevaleur as cv
                INNER JOIN article_caractval as ac ON (cv.caractvalid=ac.caractvalid)
                WHERE cv.caractid='$rs_caracteristiques[caractid]' AND artid='$lignepaniertmp[artid]'
                ORDER BY cv.caractvalid ASC ");
                $nb_val_caract=$DB_site->num_rows($rq_caracteristique_valeurs) ;
                while ($rs_caracteristique_valeurs=$DB_site->fetch_array($rq_caracteristique_valeurs)){ // valeurs
                    $optvalue=$rs_caracteristique_valeurs['caractvalid'];
                    $ral_caract=$ral->is_ral_option($rs_caracteristique_valeurs['caractvalid']);
            
                    if($ral_caract==false){
                        $optlibelle=$rs_caracteristique_valeurs[$libelle];
                        $optaction="document.formu.submit();";
                        $ral_panel="";
                    }else{
                        $j = $i+1;
                        $template->parts["ral_panel"].="<div id=\"ral_panel_".$rs_caracteristique_valeurs['caractvalid']."\" class=\"ral_panel\">".$ral->generate()."</div>";
                        $ral_caractval=$DB_site->query_first("SELECT libelle".$template->langue.",caractvalid FROM caracteristiquevaleur cv INNER JOIN lignepaniercaracteristique USING(caractvalid) WHERE cv.caractid='".$ral->caractid."' AND lignepanierid='$lignepaniertmp[lignepanierid]' ORDER BY lignepaniercaracteristiqueid ASC LIMIT ".$i.",".$j);
                        if($ral_caractval==""){
                            $optlibelle=$rs_caracteristique_valeurs[$libelle];
                        }else{
                            $caract_ral=$DB_site->query_first("select cr.caractvalid from caractval_ral cr INNER JOIN caracteristiquevaleur cv ON cv.caractvalid = cr.caractvalid WHERE cr.caractvalid =".$ral_caractval['caractvalid']);
                            $optlibelle=$ral_caractval[0];
                            $optvalue=$ral_caractval['caractvalid'];
                            $optaction="ral_show('ral_panel_".$ral_caractval['caractvalid']."',true,this);set_ral_vars({'caractid':$rs_caracteristiques[caractid],'lignepanierid':$lignepaniertmp[lignepanierid]});";
                            $i++;
                        }
                    }

                    ///si prix fixe ou prix variable
                    if(!is_null($caract_ral)){
                        $caract_id = $DB_site->query_first("SELECT caractid FROM `caracteristiquevaleur` WHERE `caractvalid` =".$caract_ral['caractvalid']);

                        $prix_caract_ral=$DB_site->query_first("select cv.pvttc, cv.prix_fixe, ac.differenceprix from caracteristiquevaleur cv INNER JOIN article_caractval ac ON ac.caractvalid = cv.caractvalid WHERE cv.subcaractid = ".$caract_id['caractid']);
                        if($prix_caract_ral['prix_fixe'] == '1'){
                            $caract_prix=caract_prices($prix_caract_ral['pvttc'],$template->tva_ref(),$sitepro);
                        }
                        else{
                            $caract_prix=caract_prices($prix_caract_ral['differenceprix'],$template->tva_ref(),$sitepro);
                        }

                    }
                    else{
                        if($rs_caracteristique_valeurs['prix_fixe'] == '1'){
                            $caract_prix=caract_prices($rs_caracteristique_valeurs['pvttc'],$template->tva_ref(),$sitepro);
                        }
                        else{
                            $caract_prix=caract_prices($rs_caracteristique_valeurs['differenceprix'],$template->tva_ref(),$sitepro);
                        }
                    }
                    $prixtxt=($caract_prix==0?$template->translate("/commande/offert","F"):$template->price_format($caract_prix));

                    if (in_array($optvalue, $tab_lignepaniercaracteristique)){
                        $valeurs_caracteristique_zonedeliste .= "<option value=\"$optvalue\" onchange=\"$optaction\" selected>$optlibelle</option>" ;
                        $valeurs_cde2_caracteristique = $rs_caracteristique_valeurs[$libelle] ;
                        $radio_caract.="<div><input type=\"radio\" name=\"lst_caract[$lignepaniertmp[lignepanierid]][$rs_caracteristiques[caractid]]\" value=\"$optvalue\" onclick=\"$optaction\" checked> $optlibelle <b>".$prixtxt."</b></div>";
                        $caractvalname_select=$optlibelle." <b>".$prixtxt."</b>";
                    }else{
                        $valeurs_caracteristique_zonedeliste .= "<option value=\"$optvalue\" onchange=\"$optaction\">$optlibelle</option>" ;
                        $radio_caract.="<div><input type=\"radio\" name=\"lst_caract[$lignepaniertmp[lignepanierid]][$rs_caracteristiques[caractid]]\" onclick=\"$optaction\" value=\"$optvalue\"> $optlibelle <b>".$prixtxt."</b></div>";
                    }
                    $mavaleurselectionnee = "$rs_caracteristique_valeurs[$libelle]" ;   
                }
                    
         
                $caracteristique[$libelle] = $rs_caracteristiques[$libelle] ;
                //if($rs_caracteristiques['caractid']!=1)
                    $TemplateCde1BitCaracteristiquesBit .= "<p><b>$caracteristique[$libelle]</b> <select name=\"lst_caract[$lignepaniertmp[lignepanierid]][$rs_caracteristiques[caractid]]\" onchange=\"document.formu.submit();\">$valeurs_caracteristique_zonedeliste</select></p>$ral_panel";
                /*else
                    $TemplateCde1BitGarantieBit .= "<p><b>$caracteristique[$libelle]</b></p>".$radio_caract;*/
            
                $template->parts["options"].="<div class=\"opt_ligne\">"
                          . "<div class=\"libelle_option\">"
                              . "<p class=\"intitule\">".$caractvalname_select."</p>"
                              . "<p id=\"txt_options\" data-open=\"".$voir."\" data-close=\"".$masquer."\"><i class=\"fa fa-chevron-down fa-lg\"></i></p>"
                              . "<div class=\"clear\"></div>"
                          . "</div>"
                          . "<div class=\"centre_options\">".$radio_caract."</div>"
                        ." </div>";
            }
            
                // if($TemplateCde1BitGarantieBit=="") $TemplateCde1BitGarantieBit=$textegarantie;


            $optqte="";
            $limit = $lignepaniertmp['qte'] <= 50 ? 50 : $lignepaniertmp['qte'];
            for($i=1;$i<=$limit;$i++)
                $optqte.="<option value=\"".$i."\"".($lignepaniertmp['qte']==$i?" selected":"").">".$i."</option>";
            
            // affichages
            $libellerewrite = url_rewrite($lignepaniertmp[$libelle]) ;
            $lienlibelle="<a href=\"art,$lignepaniertmp[artid],$libellerewrite.htm\" title=\"$lignepaniertmp[$libelle]\" target=\"_blank\">".$lignepaniertmp[$libelle]." <i>".$libmarque['libelle']." ".$article['gamme'.$template->langue]." ".$lignepaniertmp['artcode']."</i></a>";
            if(substr($lignepaniertmp['artcode'],0,7)=="ACI-KDO") $lienlibelle=$lignepaniertmp[$libelle];
            
            $template->parts["lignepanierid"]=$lignepaniertmp['lignepanierid'];
            $template->parts["td1"]=$td1;
            $template->parts["ligneqte"]=$lignepaniertmp['qte'];
            $prixht = $lignepaniertmp['prix']/(1 + $tvafrance['taux'.$lignepaniertmp['tauxchoisi']]/100);
            if($sitepro) $template->parts["lignepu"]=$template->price_format($prixht);
            else $template->parts["lignepu"]=$template->price_format($lignepaniertmp['prix']);

            
            
            if(!$template->mobile){
                $template->parts['detailsArt']= $td1;
                $template->parts['optionsArt']= $TemplateCde1BitCaracteristiquesBit." ".$TemplateCde1BitGarantieBit;
                $template->parts['price']= $template->parts["lignepu"]." <sup>".$template->parts["taxtxt"]."</sup>";
                $template->parts['qte']="<select id=\"qte_$lignepaniertmp[lignepanierid]\" name=\"qte[$lignepaniertmp[lignepanierid]]\" onchange=\"document.forms.formu.submit();\">$optqte</select>";
                $template->parts['price2']=$template->parts["prixligne"]." <sup>".$template->parts["taxtxt"]."</sup>";
                $template->parts['delpanier']="<a href=\"cde1.php?action=delpanier&lignepanierid=$lignepaniertmp[lignepanierid]\"><img src=\"images/templates/cross.gif\"></a>";
   

                //******************** DEBUT ACCESSOIRES CONSEILLES SANS PACK***************************************//
                $i=0;
                if($nb_acces_ligne_panier > 0){
                    $template->parts['artcons']='';
                    while ($art_rangement=$DB_site->fetch_array($art_rangements)){
                        //$template->parts['class_sup'] = $i == 0 ? 'premier_acc vignette_acc' : 'vignette_acc';
                        $template->parts['class_test'] ='l_'.$lignepaniertmp['lignepanierid'];
                        vignette($art_rangement['artid'],'categorie');
                        $i++;
                        if($i==1) {
                            $class = 'premier_acc vignette_acc';
                        }else{
                            $class = 'vignette_acc';
                        }
                        vignette($art_rangement['artid'],'accessoires_panier',$class);
                    }
                        $template->parts["nb_accessoires"]=$i;
                        $template->parts["ligne_panier"] = $lignepaniertmp['lignepanierid'];
                        vignscroll("accessoires",$i,$lignepaniertmp['lignepanierid']);
                        //$template->evaltemplate("OngletArtCons","ongletartcons");
                }
                // ****************FIN ACCESSOIRES CONSEILLES SANS PACK ***********************************************//


                //*************** DEBUT ACCESSOIRES CONSEILLES AVEC PACK***********************************************//
                $i=0;
                if($isPack){
                    $template->parts['artcons']='';
                    foreach($artidPack as $art){
                        $art_rangements_pack = $DB_site->query("SELECT * FROM article a INNER JOIN article_rangement ac ON(a.artid = ac.artid_rangement) INNER JOIN article_site a_s ON(a.artid=a_s.artid) WHERE ac.artid = '".$art."' AND a_s.site_code='".$template->site_code()."' AND a_s.active = '1' AND commandable='1' GROUP BY a.artid ORDER BY a.artcode");
                    
                        while ($art_rangements=$DB_site->fetch_array($art_rangements_pack)){
                            //$template->parts['class_sup'] = $i == 0 ? 'premier_acc vignette_acc' : 'vignette_acc';
                            $template->parts['class_test'] ='l_'.$lignepaniertmp['lignepanierid'];
                            vignette($art_rangements['artid'],'categorie');
                            $i++;
                            if($i==1) {
                                $class = 'premier_acc vignette_acc';
                            }else{
                                $class = 'vignette_acc';
                            }
                            vignette($art_rangements['artid'],'accessoires_panier',$class);
                        }
                            $template->parts["nb_accessoires"]=$i;
                            $template->parts["ligne_panier"] = $lignepaniertmp['lignepanierid'];
                            vignscroll("accessoires",$i,$lignepaniertmp['lignepanierid']);
                            //$template->evaltemplate("OngletArtCons","ongletartcons");
                    }
                }
                // **********************FIN ACCESSOIRES CONSEILLES AVEC PACK  ***********************************************//

                $template->evaltemplate("LignesPanier", "lignes");
            }else
                $template->evaltemplate("LignePanier","lignes");
        
            $remarketing['libelle'][]=utf8_encode(html_entity_decode($lignepaniertmp[$libelle],ENT_COMPAT,"ISO-8859-15"));
            $remarketing['price'][]=$lignepaniertmp['prix'];
            $lignecat=$DB_site->query_first("SELECT c.libelle".$template->langue." AS libelle FROM categorie c INNER JOIN article USING(catid) WHERE artid=".$lignepaniertmp['artid']);
            $remarketing['category'][]=utf8_encode(html_entity_decode($lignecat['libelle'],ENT_COMPAT,"ISO-8859-15"));
        
            $n++;
        }else{
            //si le produit dans le panier n'est plus actif on le supprime du panier
            $sql = "DELETE FROM lignepanier WHERE lignepanierid ='".$lignepaniertmp['lignepanierid']."'";
            $DB_site->query($sql);

            $sql = "DELETE FROM lignepaniercaracteristique WHERE lignepanierid ='".$lignepaniertmp['lignepanierid']."'";
            $DB_site->query($sql);

            $sql = "SELECT COUNT(*) FROM lignepanier WHERE panierid = '".$panier['panierid']."' AND panierid != 0";
            $lignepaniercount = $DB_site->query_first($sql);
            //si il n'y a plus d'article dans le panier on supprime le panier
            if($lignepaniercount[0] == 0){
                    $sql = "DELETE FROM panier WHERE panierid = '" . $panier['panierid'] . "'";
                    $DB_site->query($sql);
                    $template->redirect("/",302);        
                }

        }

    }/******FIN DE LA BOUCLE SUR LES LIGNES DU PANIER*************************************************************************************************/
    /*************************************************************************************************************************************************/

   

    if($default){
        $template->parts["affichage_first"] = "<input type=\"hidden\" id=\"affichage_first\" value=\"$txt\"> ";
        $template->parts["borderTab"] = "display:none;";
    }

    if ($_COOKIE['userid'] != ""){
        $pays_user=$DB_site->query_first("select paysid from utilisateur where userid = '$_COOKIE[userid]'");
        $paysid = $pays_user['paysid'] ;
    }else
        {
        $paysid = "57" ;
    }

    $template->parts["prixtotalpanier"] = $template->price_format($prixtotalpanier);
    $template->parts["prixtva"] = $template->price_format($prixtva);
    $template->parts["prixht"]=$template->price_format($totalht);

        

  $template->evaltemplate("PanierPlein","pagecommande");

}else 
   $template->evaltemplate("PanierVide","pagecommande");



if(count($criteoitems)>0){
    $jsoncriteoitems=json_encode($criteoitems);
    $template->parts["criteo"]=<<<JS
    <script type="text/javascript" src="//static.criteo.net/js/ld/ld.js" async="true"></script>
    <script type="text/javascript">
    window.criteo_q = window.criteo_q || [];
    window.criteo_q.push(
    { event: "setAccount", account: 2995 },
    { event: "setEmail", email: ["$user_info[mail]"] },
    { event: "setSiteType", type: "d" },
    { event: "viewBasket", item: $jsoncriteoitems}
    );
    </script>

JS;
}

$template->parts["criteo"].="<script src=\"http://nxtck.com/act.php?tag=36948\"></script>";

if(ESPACE == 'prod'){
    //Mise en place des tags Nextperformance
    $cpt   = 0;
    $nxtck = '';
    foreach($nxtckitems as $l_item){
        if($cpt > 0) $nxtck .= ',';
        $nxtck .= $l_item;
        $cpt++;
    }
    $prixtotalpanier2 = str_replace('.',',' , $prixtotalpanier); //il faut remplacer le point par une virgule pour nextperformance...........
    $template->parts["nextperformance"] = "<script type=\"text/javascript\" src=\"https://nxtck.com/act.php?tag=43957&pid=".$nxtck."&f1=".$prixtotalpanier2."\" async></script>";
}
$template->parts["remarketing"]=google_remarketing($lstartid,"cart",$prixtotalpanier,$remarketing['category'],$remarketing['libelle'],$remarketing['price']);


$template->parts["etape"]="<img src=\"images/templates/commande1.jpg\" alt=\"Panier\">";

//BarreCookie
$template->parts["stylePanier"]="style=\"bottom:0;\"";

$template->evaltemplate("RappelInst","rappelinst");
$template->evaltemplate("MiniContact","minicontact");
$articles_vus=$maincat->articles_vus($s,6);
$c=0;
if(count($articles_vus)>0){
    foreach($articles_vus as $artid){
    $c++;
    if($c>1) $TemplateAV.="<div class=\"sep\"></div>";
    $TemplateAV.=article_vu($artid);
    }
    $template->parts["artvus"]=$TemplateAV;
    $template->evaltemplate("ArticlesVusBar","articlesvus");
}
$template->parts["nbartvus"]=count($articles_vus);
$template->evaltemplate("ArticleBar","articlebar");
if(isset($_GET['back_call'])){
    $template->parts['facturid']=mysqli_real_escape_string($DB_site->link_id,$_GET['facturid']);
    //$template->evaltemplate("DevisBackCallStep1","devis_back_step1");
    //$template->evaltemplate("DevisBackCallStep2","devis_back_step2");
    //$template->evaltemplate("DevisBackOverlay","devis_back");
    $template->parts['modalclass'] = "";
    $template->parts['stylecss']="style=\"display:block;z-index:0;\"";
    $template->evaltemplate("DevisBackCallStep1","modal");
    $template->evaltemplate("DevisBackCallStep2","modal");
    $template->evaltemplate("Overlay","modaltype");

}elseif(isset($_GET['back_mail'])){
    $template->parts['facturid']=mysqli_real_escape_string($DB_site->link_id,$_GET['facturid']);
    $template->parts['modalclass'] = "";
    $template->parts['stylecss']="style=\"display:block;z-index:0;\"";
    $template->evaltemplate("DevisBackMailStep1","modal");
    $template->evaltemplate("DevisBackMailStep2","modal");
    $template->evaltemplate("Overlay","modaltype");
}

$template->evaltemplate("PanierPage","main");

//Paramétrage des META référencement de la page
if($sitepro)
{   
    $title_trad = '/index/meta_title_pro';
    $title_desc = '/index/meta_description_pro';
}
else
{
    $title_trad = '/index/meta_title';
    $title_desc = '/index/meta_description';
}

$template->pageparams($host, $template->translate($title_trad), $template->translate($title_desc));

$template->out();



function vignscroll($divid,$nbelem,$ligne_panier,$classvins=""){
    global $template;

    $template->parts["scrollbtns_".$divid]="";
    if($nbelem<=3) {
        $template->parts["scrollbtns_".$divid]="<span id=\"text_vignscroll_".$ligne_panier."\" class=\"text_vignscroll\"></span>";
        return false;
    }
    $template->parts["scrollbtns_".$divid]="<div id=\"scrollbtnsparent_".$ligne_panier."\" class=\"scrollbtnsparent ".$classvins."\" style=\"display:none;margin-bottom:10px;\">
      <div class=\"scrollbtns hidden-xs\" id=\"scroll_".$divid."_".$ligne_panier."_btns\">
      <span  onclick=\"vignscroll('".$divid."_".$ligne_panier."','-1')\"><i class=\"fas fa-chevron-left\"></i></span>";
      for($b=0;$b<$nbelem;$b++)
            $template->parts["scrollbtns_".$divid].="<span class=\"btn".($b<=2?" selected":"")."\" onclick=\"vignscroll('".$divid."_".$ligne_panier."','".$b."')\"></span>";
            $template->parts["scrollbtns_".$divid].="<span onclick=\"vignscroll('".$divid."_".$ligne_panier."','+1')\"><i class=\"fas fa-chevron-right\"></i></span>
      </div>
      <span id=\"text_vignscroll_".$ligne_panier."\" class=\"text_vignscroll\"></span>
      <div class=\"clear\"></div>
    </div>";
}


?>
