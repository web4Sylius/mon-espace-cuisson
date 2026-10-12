<?php
header('Content-Type: text/html; charset=iso-8859-1');

require_once($_SERVER['DOCUMENT_ROOT']."/global.php");

$facture_id = intval($_GET['facture_id']);

//Récupération de la liste des types d'articles existants
$sql = "SELECT typearticleid";
$sql .= " FROM typearticle";
$req = $DB_site->query($sql);
while($row = $DB_site->fetch_array($req)){
     $aFullTypeArticle[] = $row['typearticleid'];
}

//Récupération des articles de la commande
$aTypeArticle = array();
$sql = "SELECT DISTINCT(a.typearticleid)";
$sql .= " FROM lignefacture lf";
$sql .= " INNER JOIN article a ON (a.artid = lf.artid)";
$sql .= " WHERE lf.factureid = ".$facture_id;
$req = $DB_site->query($sql);
while($row = $DB_site->fetch_array($req)){
     $aTypeArticle[] = $row['typearticleid'];
}

$aForfait      = array();
$aInstallation = array();

//Récupération des forfaits correspondants (valable uniquement avec plusieurs articles)
if(count($aTypeArticle) > 1){
     $sql = "SELECT *";
     $sql .= " FROM installation i";
     $sql .= " WHERE i.installation_type_id = 1";
     $sql .= " AND (";
     $nb = 0;
     foreach($aTypeArticle as $typearticleid){
          if($nb > 0) $sql .= " OR ";
          $sql .= "installation_typearticle LIKE '%|".$typearticleid."|%'";
          $nb++;
     }
     $sql .= ")";
     foreach($aFullTypeArticle as $le_type_exlclu){
          if(!in_array($le_type_exlclu, $aTypeArticle)){
               $sql .= " AND installation_typearticle NOT LIKE '%|".$le_type_exlclu."|%'";
          }
     }
     $req = $DB_site->query($sql);
     while($row = $DB_site->fetch_array($req)){
          $aForfait[] = $row;
     }
}

//Récupérations des installations correspondantes
foreach($aTypeArticle as $typearticleid){
     $sql = "SELECT *";
     $sql .= " FROM installation i";
     $sql .= " WHERE i.installation_type_id = 2";
     $sql .= " AND installation_typearticle = '|".$typearticleid."|'";
     $req = $DB_site->query($sql);
     while($row = $DB_site->fetch_array($req)){
          $aInstallation[] = $row;
     }
}
?>

<p class="titre_options"> En option : Installation et mise en service</p>
<p class="desc_options">En complément de la livraison &laquo; ECO &raquo; ou &laquo; CONFORT &raquo;, Mon espace Cuisson vous propose, en option, la mise en service de vos appareils par une équipe de spécialistes.<br>Attention : Le(s) technicien(s) interviendra/interviendront sur RDV, quelques jours après la livraison conforme de tous les appareils effectuée par le transporteur.</p>
<p class="option_titre_forfait"><input type="radio" name="choix_installation" value="1" onclick="toggle_installation('affiche');"> Je souhaite souscrire à une offre d'installation et de mise en service d'un ou plusieurs de mes appareils</p>
<div id="list_installations" style="float: left; display: none;">
<?php 
if(count($aForfait) > 0){ 
?>
     <p class="option_titre_forfait">Optez pour un forfait :</p>
<?php 
     foreach($aForfait as $le_forfait){ 

          //Concaténation des types d'articles pour affichage dans le titre
          $aTemp = explode('|', $le_forfait['installation_typearticle']);
          
          $aTypeTemp = array();
          foreach($aTemp as $ligne){

               if(is_numeric($ligne)) $aTypeTemp[] = $ligne;
          }

          $chaine_intitule = '';
          foreach($aTypeTemp as $typearticleid){

               $sql = "SELECT libelle";
               $sql .= " FROM typearticle";
               $sql .= " WHERE typearticleid = ".$typearticleid;
               $libelle_type = $DB_site->query_first($sql);

               $chaine_intitule .= $chaine_intitule == '' ? $libelle_type['libelle'] : ' + '.$libelle_type['libelle'];
          }

          //Calcul du prix de vente TTC
          $prix_ttc = $le_forfait['installation_pv_ht'] * (1 + ($le_forfait['installation_tva_taux'] / 100));

?>
          <div class="bloc_option">
               <p class="intitule"><input onclick="uncheck('unchecker'); maj_total_options(this, '<?php echo $prix_ttc; ?>');" type="checkbox" name="installation[]" value="<?php echo $le_forfait['installation_id']; ?>"> Forfait : Installation et mise en service <span class="vert">de votre &laquo;</span> <?php echo $chaine_intitule; ?> <span class="vert">&raquo; :</span> <span class="prix"><?php echo $prix_ttc > 0 ? number_format($prix_ttc,2,',',' ').' &euro;' : 'Offert'; ?></span></p>
               <p class="description"><?php echo $le_forfait['installation_description']; ?></p>
          </div>
<?php 
     }
}

if(count($aInstallation) > 0){ 

     foreach($aInstallation as $l_installation){ 
          
          //Concaténation des types d'articles pour affichage dans le titre
          $aTemp = explode('|', $l_installation['installation_typearticle']);
          
          $aTypeTemp = array();
          foreach($aTemp as $ligne){

               if(is_numeric($ligne)) $typearticleid = $ligne;
          }         
          
          $sql = "SELECT libelle";
          $sql .= " FROM typearticle";
          $sql .= " WHERE typearticleid = ".$typearticleid;
          $libelle_type = $DB_site->query_first($sql);

          //Calcul du prix de vente TTC
          $prix_ttc = $l_installation['installation_pv_ht'] * (1 + ($l_installation['installation_tva_taux'] / 100));

?>
          <div class="bloc_option">
               <p class="intitule"><input onclick="uncheck('unchecker'); maj_total_options(this, '<?php echo $prix_ttc; ?>');" type="checkbox" name="installation[]" value="<?php echo $l_installation['installation_id']; ?>"> Installation et mise en service <span class="vert">de votre &laquo;</span> <?php echo $libelle_type['libelle']; ?> <span class="vert">&raquo; :</span> <span class="prix"><?php echo $prix_ttc > 0 ? number_format($prix_ttc,2,',',' ').' &euro;' : 'Offert'; ?></span></p>
               <p class="description"><?php echo $l_installation['installation_description']; ?></p>
          </div>
<?php
     }
}
?>
     <p class="total_options">Total &laquo; Installation et mise en service &raquo; : <span id="total_options"><?php echo number_format(0,2,',',' '); ?> &euro;</span><input type="hidden" id="total_options_hidden" name="total_options_hidden" value="0"></p>
     <div class="alerte_installation">
          <p class="notabene_options"><strong>Sont exclus de toutes nos prestations :</strong></p>
          <ul>
               <li>Tous travaux d'électricité, plomberie, gaz, maçonnerie, menuiserie, etc... soumis à responsabilité décennale.</li>
               <li>Raccords et accessoires.</li>
               <li><u>Attention</u> : l'arrivée électrique et de gaz, les conduits de cheminées, les meubles de cuisine doivent être installés et adaptés et/ou opérationnels à proximité avant l'arrivée du technicien.</li>
          </ul>
     </div>     
</div>
<p class="option_titre_forfait"><input type="radio" id="unchecker" name="choix_installation" value="0" onclick="toggle_installation('masque'); uncheck_options(this); maj_total_options(this,0,0);"> Je souhaite seulement la livraison et ne suis pas intéressé par l'installation et la mise en service</p>
<a href="javascript:void(0);" onclick="verif_checkboxes_options();" class="etapesuivante_new">Etape suivante</a>