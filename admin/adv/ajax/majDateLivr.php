<?php
require_once($_SERVER['DOCUMENT_ROOT']."/admin/admin_global.php");
require_once(BACK_PATH."/adv/adv.class.php");

$fournisseur_nom = $_GET['name'];
$commande_id     = $_GET['facturid'];
$adv_id          = $_GET['advid'];

$adv = new adv($DB_site2, $adv_id);
$adv->locked = isset($_GET['annule']);

$sql = "SELECT datelivraison";
$sql .= " FROM facture";
$sql .= " WHERE facturid = ".$commande_id;
$dateLivraison = $DB_site2->query_first($sql);
$datelivrtxt = ($dateLivraison['datelivraison'] > 0 ? date("d/m/Y",$dateLivraison['datelivraison']) : "?");

//PARAMS "Date livraison"
$aLibDateLivr = array(
	"maximum" => "trsptdateother",
	"date précise" => "trsptdateauto"
);
$aFuncDateLivr = array(
	"adv_save('trsptdatemax','11')",
	"loadStep('trspt11', '/admin/adv/ajax/majPJ.php?name=".$fournisseur_nom."&facturid=".$commande_id."&advid=".$adv_id."')"
);
?>
<p style="padding:5px; margin:0;">10. Date livraison</p>
<b>Date livraison commande : <?php echo $datelivrtxt; ?></b>
<br>Date livraison <?php echo $adv->radio_inputs("trsptdatemax", $aLibDateLivr, implode(';', $aFuncDateLivr))." ".$adv->cal_input("trsptotherdate","adv_calsave"); ?>

<!-- Désactiver lors de la refonte générale de fin 2016 (cf. doc Philippe/Noelia) -->
<!--<br><?php echo $adv->checkbox("livraisonplustot","adv_save(%name)",1); ?>Voir la possibilité de livrer plus tôt
<br><?php echo $adv->checkbox("infotrspt","adv_save(%name)","other"); ?> Info pour transporteur : <?php echo $adv->text_input("othertrsptinfo","adv_autosave(this.name)",array("size"=>60)); ?>-->