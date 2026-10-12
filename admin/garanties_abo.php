<?php
require "admin_global.php";
include "./header.php";
include "./menu.php";
include "../class/garantie.class.php";
include "../class/DAOgarantie.class.php";
include "../class/DAOtype.class.php";
$garantieid = 642;
$lesgaranties = DAOgarantie::loadAllGarantiesAbo($DB_site2,null,'FR',1);
$maGarantie = new garantie($DB_site2,null,'FR',$garantieid);

//Checkboxes des types d'articles
$sql = "SELECT typearticleid, libelle";
$sql .= " FROM typearticle";
$sql .= " ORDER BY libelle";
$r = $DB_site2->query($sql);
$cptType = 0;
while($types = $DB_site2->fetch_array($r)){
     
     $aCheckTypeArticle[] = $types;
}
$nbTypesArticles     = count($aCheckTypeArticle);
$nbTypesArticlesCols = ceil($nbTypesArticles/3);

?>
<script type="text/javascript">
	     function addTranche(){
          
          var tranche_mini = document.getElementById('prix_mini_tranche').value;
          var tranche_maxi = document.getElementById('prix_maxi_tranche').value;
          var paht = document.getElementById('paht_tranche[0]').value;
          var prix_base = document.getElementsByName('prix_tranche[0][base]')[0].value;
          var prix_uk = document.getElementsByName('prix_tranche[0][uk]')[0].value;
          var ppi_base = document.getElementsByName('ppi_tranche[0][base]')[0].value;
          var ppi_uk = document.getElementsByName('ppi_tranche[0][uk]')[0].value;
          var garantie_id = document.getElementById('garantieid').value;
          
          ajaxrequest('ajax/tarifs_tranche.php?action=add&garantie_id='+garantie_id+'&mini='+tranche_mini+'&maxi='+tranche_maxi+'&paht='+paht+'&prix[base]='+prix_base+'&prix[uk]='+prix_uk+'&ppi[base]='+ppi_base+'&ppi[uk]='+ppi_uk, update_tarifs);  
     }
     
     function deleteTranche(tranche_id){
          
          if(confirm('Supprimer cette tranche de prix ?')){
               
               var garantie_id = document.getElementById('garantieid').value;
               
               ajaxrequest('ajax/tarifs_tranche.php?action=delete&tranche_id='+tranche_id+'&garantie_id='+garantie_id, update_tarifs);  
          }
          
          return false;
     }
     function update_tarifs(httprequest){
          
          document.getElementById('tarifs').innerHTML = httprequest.responseText;
     }
</script>
<h1>Garanties Platine Home</h1>
<h2>Paramétrage de la garantie Platine Home</h2>
<br>

<p>Les types de produits :</p>
<!-- DEBUT ENCART TYPES ARTICLES -->
<div id="types_articles" class="contenus" style="display: block;">
<input type="hidden" name="garantieid" id="garantieid" value="<?php echo $maGarantie->garantieid; ?>"/>
  <table class="tbl_checkbox">
       <tr>
            <td>
                 <?php
                 $compteur = 0;
                 $nbMax = $nbTypesArticlesCols;
                 for($i=$compteur;$i<$nbMax;$i++){

                      $checked = "";
                      $gras    = "";

                      if(in_array($aCheckTypeArticle[$i]['typearticleid'], $maGarantie->types_produits)){

                           $checked = "checked";
                           $gras    = "style=\"font-weight:bold;\"";
                      }

                      $compteur++;

                      echo "<input type=\"checkbox\" name=\"types_produits[]\" id=\"types_produits_".$compteur."\" value=\"".$aCheckTypeArticle[$i]['typearticleid']."\" ".$checked." /><span ".$gras.">".$aCheckTypeArticle[$i]['libelle']."</span><br/>";
                 }

                 ?>
            </td>
            <td>
                 <?php
                 $nbMax = $nbTypesArticlesCols + $compteur;
                 for($i=$compteur;$i<$nbMax;$i++){

                      $checked = "";
                      $gras    = "";

                      if(in_array($aCheckTypeArticle[$i]['typearticleid'], $maGarantie->types_produits)){

                           $checked = "checked";
                           $gras    = "style=\"font-weight:bold;\"";
                      }

                      $compteur++;

                      echo "<input type=\"checkbox\" name=\"types_produits[]\" id=\"types_produits_".$compteur."\" value=\"".$aCheckTypeArticle[$i]['typearticleid']."\" ".$checked." /><span ".$gras.">".$aCheckTypeArticle[$i]['libelle']."</span><br/>";
                 }

                 ?>
            </td>
            <td>
                 <?php
                 $nbMax = $nbTypesArticlesCols + $compteur > count($aCheckTypeArticle) ? count($aCheckTypeArticle) : $nbTypesArticlesCols + $compteur;
                 for($i=$compteur;$i<$nbMax;$i++){

                      $checked = "";
                      $gras    = "";

                      if(in_array($aCheckTypeArticle[$i]['typearticleid'], $maGarantie->types_produits)){

                           $checked = "checked";
                           $gras    = "style=\"font-weight:bold;\"";
                      }

                      $compteur++;

                      echo "<input type=\"checkbox\" name=\"types_produits[]\" id=\"types_produits_".$compteur."\" value=\"".$aCheckTypeArticle[$i]['typearticleid']."\" ".$checked." /><span ".$gras.">".$aCheckTypeArticle[$i]['libelle']."</span><br/>";
                 }

                 ?>
            </td>
       </tr>
  </table>
</div>
<!-- FIN ENCART TYPES ARTICLES -->

<p>Les tranches de prix :</p>
<!-- DEBUT ENCART TARIFS -->
<div id="tarifs" class="contenus" style="display: block;">
	<?php include('ajax/tarifs_tranche.php'); ?>
</div>
<!-- FIN ENCART TARIFS -->











<?php
include "./footer.php";
?>