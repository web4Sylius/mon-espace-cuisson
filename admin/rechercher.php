<?php
require_once($_SERVER['DOCUMENT_ROOT']."/admin/admin_global.php");
require_once(BACK_PATH."/fonctions.php");
require_once(BACK_PATH."/articles/verif.php");
require_once(SITE_PATH."/class/stock.class.php");

include_once(BACK_PATH."/header.php");
include_once(BACK_PATH."/menu.php");
echo "<h1>Catalogue</h1>" ;

$stock = new stock($DB_site2);

if($catid == "" || !isset($catid)){
     $parentid = "0";
} else {
     $parentid = $catid; 
}

$i = 0;
$tabcategorie = array();
$requete      = "";
$title        = "";

if(isset($_POST['recherche'])) $recherche = str_replace(" ","%",$_POST['recherche']);
if(isset($_GET['recherche'])) $recherche = str_replace(" ","%",$_GET['recherche']);
if(!isset($_POST['active']) && !isset($_GET['active'])) $active = array(0, 1, 2);
if(!isset($_POST['commandable']) && !isset($_GET['commandable'])) $commandable = array(0, 1, 2);

?>
<script language="javascript">
function rechercher(){
     if (document.formu.recherche.value!=''){
          if (document.formu.recherche.value.length<50){
               document.formu.submit();
          } else {
               alert("Pas plus de 50 caractères s'il vous plaît.");
          }
     } else {
          alert("Vous essayer d'effectuer une recherche vide !");
     }
}
</script>

<h3>
<?php if(isset($recherche) && !empty($recherche)){ ?>
Recherche : "<?php echo stripslashes($recherche); ?>"
<?php } ?>
</h3>

<?php
include_once(BACK_PATH.'/includes/catalogue_recherche.php');

//Traitement de la recherche
if(isset($recherche) && $recherche!=""){ //Input texte de recherche
    
     if(preg_match("/[0-9]{13}/",$recherche)){ //Recherche sur code barre
          
          $requete .= " AND a.artid IN (";
          $requete .= "    SELECT artid";
          $requete .= "    FROM code_barre ";
          $requete .= "    WHERE codebarre = '".$recherche."'";
          $requete .= ")";
          
     } else { //Recherche sur libellé ou réf. interne ou réf. fabricant
          
          $requete.=" AND (";
          $requete .= "   a.".$libelle." LIKE '%".$recherche."%'";
          $requete .= "   OR";
          $requete .= "   a.artcode LIKE '%".$recherche."%'";
          $requete .= "   OR";
          $requete .= "   a.artcode_eurocave LIKE '%".$recherche."%'";
          $requete .= "   OR";
          $requete .= "   a.refs_fabricant LIKE '%".$recherche."%'";
          $requete .= "   OR";
          $requete .= "   a.gammeFR LIKE '%".$recherche."%'";
          $requete .= ")";
          
     }
     
}

if(isset($_POST['active']) || isset($_GET['active'])){ //Si au moins un statut est coché
     
     $active = isset($_POST['active']) ? $_POST['active'] : $_GET['active'];

     $requete .= " AND a.active IN ('".implode("','",$active)."')";
     
     if(in_array(0,$active) && !in_array(1,$active)) $title .= " inactifs temporairement";
     if(in_array(2,$active) && !in_array(1,$active)) $title .= " supprimés";
     
}

if(isset($_POST['commandable']) || isset($_GET['commandable'])){ //Filtre sur produits commandables
     
     $commandable = isset($_POST['commandable']) ? $_POST['commandable'] : $_GET['commandable'];
     
     $requete.=" AND a.commandable IN('".implode("','",$commandable)."')";
     
     if(in_array(0,$commandable)) $title .= " vendus en magasin";
     if(in_array(2,$commandable)) $title .= " non disponibles";
    
}

if(isset($_POST['reappro']) || isset($_GET['reappro'])){ //Filtre sur produits en reappro
     
    $requete .= " AND a.artid IN (";
    $requete .= "        SELECT artid";
    $requete .= "        FROM article_fournisseur";
    $requete .= "        WHERE reappro > ".time();
    $requete .= "        GROUP BY artid";
    $requete .= ")";
    
}

if(isset($_POST['cdc']) || isset($_GET['cdc'])){ //Filtre sur produits "coups de coeur"
     
     $requete .= " AND a.artid = (";
     $requete .= "    SELECT artid";
     $requete .= "    FROM coupdecoeur";
     $requete .= "    WHERE artid = a.artid";
     $requete .= ")";
     
}

if(isset($_POST['promo']) || isset($_GET['promo'])){ //Filtre sur produits en promo
    
     $requete .= " AND a.artid = (";
     $requete .= "    SELECT artid";
     $requete .= "    FROM promo";
     $requete .= "    WHERE artid = a.artid";
     $requete .= ")";
     
}

if(isset($_POST['idkdos']) || isset($_GET['idkdos'])){ //Filtre sur idées cadeau
     
     $requete .= " AND a.artid = (";
     $requete .= "    SELECT artid";
     $requete .= "    FROM idkdos";
     $requete .= "    WHERE artid=a.artid";
     $requete .= ")";
    
}

if(isset($_POST['deselection']) || isset($_GET['deselection'])){ //Filtre sur "Hors sélection"
     
     $requete .= " AND a.artid = (";
     $requete .= "    SELECT artid";
     $requete .= "    FROM article_deselection";
     $requete .= "    WHERE artid = a.artid";
     $requete .= ")";
    
}

if(isset($_POST['erreurs']) || isset($_GET['erreurs'])){ //Filtre sur produits en erreur
     
     $verif  = verif_articles();
     $artids = array_keys($verif);
     
     $requete .= " AND a.artid IN (".implode(",",$artids).")";
    
}

if((isset($_POST['type']) && !empty($_POST['type'])) || (isset($_GET['type']) && !empty($_GET['type']))){ //Filtre sur types d'articles
     
     $type_id = isset($_POST['type']) ? $_POST['type'] : $_GET['type'];
     
     $requete .= " AND a.typearticleid = ".$type_id;
    
}

if((isset($_POST['categorie']) && !empty($_POST['categorie'])) || (isset($_GET['categorie']) && !empty($_GET['categorie']))){ //Filtre sur catégorie
     
     $categorie_id = isset($_POST['categorie']) ? $_POST['categorie'] : $_GET['categorie'];
     
     $requete .= " AND c.catid = ".$categorie_id;
    
}

if((isset($_POST['marque']) && !empty($_POST['marque'])) || (isset($_GET['marque']) && !empty($_GET['marque']))){ //Filtre sur marues
     
     $marque_id = isset($_POST['marque']) ? $_POST['marque'] : $_GET['marque'];
     
     $requete .= " AND am.marqueid = ".$marque_id;
    
}

if((isset($_POST['fournisseur']) && !empty($_POST['fournisseur'])) || (isset($_GET['fournisseur']) && !empty($_GET['fournisseur']))){ //Filtre sur marues
     
     $fournisseur_id = isset($_POST['fournisseur']) ? $_POST['fournisseur'] : $_GET['fournisseur'];
     
     $requete .= " AND a.artid = (";
     $requete .= "     SELECT af.artid";
     $requete .= "     FROM fournisseur_condition fc";
     $requete .= "     INNER JOIN article_fournisseur af ON (af.fournisseur LIKE fc.societe)";
     $requete .= "     WHERE af.artid = a.artid";
     $requete .= "     AND fc.id = ".$fournisseur_id;
     $requete .= ")";
    
}

//Calcul du nombre de résultats
$sql = "SELECT count(a.artid) as nb_resultats";
$sql .= " FROM article a";
$sql .= " LEFT JOIN categorie c ON (c.catid = a.catid)";
$sql .= " LEFT JOIN article_marque am ON (am.artid = a.artid)";
$sql .= " LEFT JOIN marque m ON (m.marqueid = am.marqueid)";
$sql .= " WHERE 1 ";
$sql .= $requete;
$nb_resultats = $DB_site2->query_first($sql)['nb_resultats'];

if ($nb_resultats > 0){ //S'il y a des résultats, on affiche la liste
     
     //Mise en place de la pagination
     if(!isset($_GET['page']) || empty($_GET['page'])) $_GET['page'] = 1;
     
     $nb_article_page = 500;
     $nb_page = ceil($nb_resultats/ $nb_article_page);
     $debut   = ($_GET['page'] - 1) * $nb_article_page;
     
     //Reconstruction de l'url
     $url = '';
     foreach($_GET as $param => $value){
          
          if($param != 'page'){
               if(substr($url,-1) != '?' && substr($url,-1) != '&'){
                    if(empty($url)){
                         $url .= '?';
                    } else {
                         $url .= '&';
                    }
               }
          
               if(is_array($value)){
                    foreach($value as $sub_value){
                         $url .= $param.'[]='.$sub_value;
                    }
               } else {
                    $url .= $param.'='.$value;
               }
          }
     }
     
     foreach($_POST as $param => $value){
         
          if($param != 'page'){
                if(substr($url,-1) != '?' && substr($url,-1) != '&'){
                    if(empty($url)){
                         $url .= '?';
                    } else {
                         $url .= '&';
                    }
               }
          
               if(is_array($value)){
                    foreach($value as $sub_value){
                         $url .= $param.'[]='.$sub_value;
                    }
               } else {
                    $url .= $param.'='.$value;
               }
          }
     }
     
     //Requête de sélection des informations produits
     $sql = "SELECT a.*, a.catid, c.".$libelle." as catlibelle, m.libelle as marque_nom, (SELECT COUNT(*) FROM article_pack WHERE packid = a.artid) as pack";
     $sql .= " FROM article a";
     $sql .= " LEFT JOIN categorie c ON (c.catid = a.catid)";
     $sql .= " LEFT JOIN article_marque am ON (am.artid = a.artid)";
     $sql .= " LEFT JOIN marque m ON (m.marqueid = am.marqueid)";
     $sql .= " WHERE 1 ";
     $sql .= $requete;
     $sql .= " ORDER BY a.artcode";
     $sql .= " LIMIT ".$debut.", ".$nb_article_page;
     $articles = $DB_site2->query($sql);
     
     ?>
     
     <div class="commande_detail">
          <table class="table_modules_boutiques no_border">
               <tr>
                    <td colspan="7" style="text-align: left;"><strong><?php echo $nb_resultats; ?> résultat(s)</strong></td>
               </tr>
               <tr>
                    <td colspan="7" style="text-align: center;">
                         <?php
                         if($nb_page > 1){

                              $min = $_GET['page'] - 5;
                              $max = $_GET['page'] + 5;

                              if($min < 1) $min = 1;
                              if($max > $nb_page) $max = $nb_page;
                         ?>
                                   <p class="pagination">
                                        <?php if($_GET['page'] > 1){ ?>
                                        <a class="fleche" href="<?php echo BACK_HOST; ?>/rechercher.php<?php echo $url.(!empty($url) ? '&' : '?'); ?>page=1"><<</a>
                                        <a class="fleche" href="<?php echo BACK_HOST; ?>/rechercher.php<?php echo $url.(!empty($url) ? '&' : '?'); ?>page=<?php echo ($_GET['page']-1); ?>"><</a>
                                        <?php } ?>

                                        <?php for($i=$min;$i<=$max;$i++){ ?>
                                             <a href="<?php echo BACK_HOST; ?>/rechercher.php<?php echo $url.(!empty($url) ? '&' : '?'); ?>page=<?php echo $i; ?>" class="<?php echo ($_GET['page'] == $i ? 'on' : 'off'); ?>"><?php echo $i; ?></a>
                                        <?php } ?>

                                        <?php if($_GET['page'] < $nb_page){ ?>
                                        <a class="fleche" href="<?php echo BACK_HOST; ?>/rechercher.php<?php echo $url.(!empty($url) ? '&' : '?')."page=".($_GET['page']+1); ?>">></a>
                                        <a class="fleche" href="<?php echo BACK_HOST; ?>/rechercher.php<?php echo $url.(!empty($url) ? '&' : '?')."page=".$nb_page; ?>">>></a>
                                        <?php } ?>
                                   </p>
                         <?php } ?>
                    </td>
               </tr>
               <tr>
                    <td>
                         <table class="table_commande_color catalogue" style="margin: 10px 0 0 0;">
                              <tr>
                                   <td class="entete"></td>
                                   <td class="entete">Marque</td>
                                   <td class="entete">Libellé</td>
                                   <td class="entete">Redir.</td>
                                   <td class="entete" colspan="5"></td>
                                   <td class="entete">Qte vendue</td>
                                   <td class="entete">CA HT</td>
                                   <td class="entete">Qte stock</td>
                              </tr>
                              <?php
                              $actives = array(
                                   0 => "<font color=\"red\">Invisible</font>",
                                   1 => "<font color=\"green\">Visible</font>",
                                   2 => "<font color=\"red\">Supprimé</font>"
                               );
                              
                              while($article = $DB_site2->fetch_array($articles)){
                                   
                                   $reappro  = "";
                                   $redirect = "";
                                   $rowalt   = "td_promos_".getrowbg();
                                   
                                   if($article['artcode'] != ""){
                                        $ref_article = "<strong><i>Réf. ".$article['artcode']." (".$article['artcode_eurocave'].")</i></strong> :<br>";
                                   } else{
                                        $ref_article = "";
                                   }
	    
		  
                                   $sql = "SELECT *";
                                   $sql .= " FROM article_fournisseur";
                                   $sql .= " WHERE artid = ".$article['artid'];
                                   $fournlist = $DB_site2->query($sql);
                                   
                                   while($fourn = $DB_site2->fetch_array($fournlist)){
                                        
                                        if($fourn['reappro']>time()) $reappro .= "<p style=\"color:blue;margin:0;text-align:right;\">Réappro le ".date("d/m/Y",$fourn['reappro'])." (".$fourn['fournisseur'].")</p>";
                                        
                                   }
                                   
                                   if($article['redirect'] != ""){ //Vérification présence "Redirection"
                                        
                                        $sql = "SELECT artid, artcode, active";
                                        $sql .= " FROM article";
                                        $sql .= " WHERE artid = ".$article['redirect'];
                                        $redirect_art = $DB_site2->query_first($sql);
                                        
                                        $redirect = "<a href=\"ajout_produit.php?artid=".$redirect_art['artid']."&prec=modif\"><img src=\"/configurations/".$host."/images/produits/br/".$redirect_art['artid'].".jpg\" height=\"80\"><br>".$redirect_art['artcode']."</a><br>".$actives[$redirect_art['active']];
                                   }
                                   
                                   
                                   //Récupération état "Promotion"
                                   $sql = "SELECT *";
                                   $sql .= " FROM promo";
                                   $sql .= " WHERE artid = '".$article['artid']."'";
                                   $rs_promotion = $DB_site2->query_first($sql);
                                   
                                   //Récupération état "Second choix"
                                   $sql = "SELECT *";
                                   $sql .= " FROM secondchoix";
                                   $sql .= " WHERE artid = ".$article['artid'];
                                   $secondchoix = $DB_site2->query_first($sql);
                                   
                                   //Quantité vendue
                                   $sql = "SELECT SUM(prix/(1+tva/100)) AS prix, SUM(qte) AS qte";
                                   $sql .= " FROM lignefacture lf";
                                   $sql .= " INNER JOIN facture ON (facturid = factureid)";
                                   $sql .= " WHERE artid = ".$article['artid'];
                                   $sql .= " AND valide IN (1, 5, 7, 8)";
                                   $sql .= " GROUP BY artid";
                                   $ventes = $DB_site2->query_first($sql);
                                   
                                   //Chiffre d'affaire HT
                                   $sql = "SELECT SUM(prix/(1+tva/100)) AS prix, SUM(qte) AS qte";
                                   $sql .= " FROM lignefacturediss lf";
                                   $sql .= " INNER JOIN facture ON (facturid = factureid)";
                                   $sql .= " WHERE artid = ".$article['artid'];
                                   $sql .= " AND valide IN (1, 5, 7, 8)";
                                   $sql .= " GROUP BY artid";
                                   $venteslot = $DB_site2->query_first($sql);
                                   
                                   //Quantité en stock
                                   $item = $stock->in_stock($article['artid']);
                              ?>
                              <tr>
                                   <td class="<?php echo $rowalt; ?>" style="text-align: center;">
                                   		<?php $img_path = SITE_PATH."/configurations/$host/images/produits/br/".$article['artid'].".jpg"; ?>
                                        <?php if($article['catid'] == 117 && !file_exists($img_path)){ ?>
                                             <img src="/images/templates/ilve_defaut.jpg" style="max-width: 60px; max-height: 60px;">
                                        <?php } else { ?>
                                             <img src="/configurations/<?php echo $host; ?>/images/produits/br/<?php echo $article['artid']; ?>.jpg" style="max-width: 60px; max-height: 60px;">
                                        <?php } ?>
                                   </td>
                                   <td class="<?php echo $rowalt; ?>" style="text-align: center;"><?php echo $article['marque_nom']; ?></td>
                                   <td class="<?php echo $rowalt; ?>" width="50%"><?php echo $ref_article." ".$article[$libelle]." ".$actives[$article['active']]." ".$reappro; ?></td>
                                   <td class="<?php echo $rowalt; ?>" style="<?php echo ($article['active']==1 ? "opacity:0.6" : ""); ?>"><?php echo $redirect; ?></td>
                                   <td valign="middle" align="center" class="<?php echo $rowalt; ?>">
                                   <?php
                                   
                                   foreach($langues as $langue) echo "<a href=\"".$langue_url[$langue]."/art,$article[artid],".url_rewrite($article['libelle'.$langue]).".htm\" target=\"_blank\">$langue</a><br>";
                                   ?>
                                   </td>
                                   <td valign="middle" align="center" class="<?php echo $rowalt; ?>">
                                        <a href="ajout_<?php echo ($article['pack'] == 0 ? "produit" : "pack"); ?>.php?artid=<?php echo $article['artid']; ?>&prec=modif" onMouseOver="poplink('Editer (modifier)');" onmouseout="killlink()"><img src="images/editer.gif"></a>
                                   </td>
                                   <td valign="middle" align="center" class="<?php echo $rowalt; ?>">
                                        <a href="deplacer_produit.php?artid=<?php echo $article['artid']; ?>" onMouseOver="poplink('Déplacer');" onmouseout="killlink()"><img src="images/deplacer.gif"></a>
                                   </td>
                                   <td valign="middle" align="center" class="<?php echo $rowalt; ?>">
                                        <a href="copier_produit.php?artid=<?php echo $article['artid']; ?>" onMouseOver="poplink('Copier');" onmouseout="killlink()"><img src="images/copier.gif"></a>
                                   </td>
                                   <?php
                                   
                                   if( $rs_promotion['promoid'] != "" ){
                                   ?>
                                        <td valign="middle" align="center" class="<?php echo $rowalt; ?>">
                                             <a onMouseOver="poplink('Enlever des promo');" onmouseout="killlink()" href="catalogue.php?action=promo_non&catid=<?php echo $catid; ?>&artid=<?php echo $article['artid']; ?>" target="_self">
                                             <img src="images/promotion_vert.gif"></a>
                                        </td>
                                   <?php
                                   } else {
                                   ?>
                                        <td valign="middle" align="center" class="<?php echo $rowalt; ?>">
                                             <a onMouseOver="poplink('Ajouter une promotion');" onmouseout="killlink()" href="catalogue.php?action=promo_oui&catid=<?php echo $catid; ?>&artid=<?php echo $article['artid']; ?>" target="_self">
                                             <img src="images/promotion.gif"></a>
                                        </td>
                                   <?php
                                   }

                                   
                                   ?>
                                   <td class="<?php echo $rowalt; ?>" align="center"><?php echo intval($ventes['qte'] + $venteslot['qte']); ?></td>
                                   <td class="<?php echo $rowalt; ?>" align="center"><?php echo number_format($ventes['prix'] + $venteslot['prix'],0,',',''); ?>&euro;</td>
                                   <td class="<?php echo $rowalt; ?>" align="center"><?php echo intval($item->qte); ?></td>
                              </tr>
                         <?php } ?>
                         </table>
                    </td>
               </tr>
               <tr>
                    <td colspan="7" style="text-align: center; padding: 10px 0 0 0;">
                         <?php
                         if($nb_page > 1){

                              $min = $_GET['page'] - 5;
                              $max = $_GET['page'] + 5;

                              if($min < 1) $min = 1;
                              if($max > $nb_page) $max = $nb_page;
                         ?>
                                   <p class="pagination">
                                        <?php if($_GET['page'] > 1){ ?>
                                        <a class="fleche" href="<?php echo BACK_HOST; ?>/rechercher.php<?php echo $url.(!empty($url) ? '&' : '?'); ?>page=1"><<</a>
                                        <a class="fleche" href="<?php echo BACK_HOST; ?>/rechercher.php<?php echo $url.(!empty($url) ? '&' : '?'); ?>page=<?php echo ($_GET['page']-1); ?>"><</a>
                                        <?php } ?>

                                        <?php for($i=$min;$i<=$max;$i++){ ?>
                                             <a href="<?php echo BACK_HOST; ?>/rechercher.php<?php echo $url.(!empty($url) ? '&' : '?'); ?>page=<?php echo $i; ?>" class="<?php echo ($_GET['page'] == $i ? 'on' : 'off'); ?>"><?php echo $i; ?></a>
                                             <?php if($i%30 == 0) echo '<br>'; ?>
                                        <?php } ?>

                                        <?php if($_GET['page'] < $nb_page){ ?>
                                        <a class="fleche" href="<?php echo BACK_HOST; ?>/rechercher.php<?php echo $url.(!empty($url) ? '&' : '?')."page=".($_GET['page']+1); ?>">></a>
                                        <a class="fleche" href="<?php echo BACK_HOST; ?>/rechercher.php<?php echo $url.(!empty($url) ? '&' : '?')."page=".$nb_page; ?>">>></a>
                                        <?php } ?>
                                   </p>
                         <?php } ?>
                    </td>
               </tr>
          </table>
     </div>
		
<?php     
} else {
?>
<div class="commande_detail">
     <table class="table_modules_boutiques no_border">
          <tr>
               <td colspan="7" style="text-align: center;"><strong>Aucun produit ne correspond à ces critères de recherche</strong></td>
          </tr>
     </table>
</div>
<?php
}


include "./footer.php";
?>