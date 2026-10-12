<?php
require_once($_SERVER['DOCUMENT_ROOT']."/admin/admin_global.php");
require_once(BACK_PATH."/adv/adv.class.php");
require_once(SITE_PATH."/class/stock.class.php");

$fournisseur_nom = $_GET['name'];
$commande_id     = $_GET['facturid'];
$adv_id          = $_GET['advid'];

$adv = new adv($DB_site2, $adv_id);
$adv->locked = isset($_GET['annule']);

$aFunctions = array(
	"adv_save(%name,'12,13')",
	"loadStep('trspt12', '/admin/adv/ajax/majTarifs.php?name=".$fournisseur_nom."&facturid=".$commande_id."&advid=".$adv_id."', 'tarifs(\'".$fournisseur_nom."\'); auto_cout();')",
	"loadStep('trspt13', '/admin/adv/ajax/majValidation.php?name=".$fournisseur_nom."&facturid=".$commande_id."&advid=".$adv_id."')"
);
?>
<p style="padding:5px; margin:0;">11. Pièces jointes</p>
<?php echo $adv->checkbox("notrsptpj",implode(';',$aFunctions),"0"); ?> Aucune
<br>
<?php echo $adv->checkbox("bonstrsptpj",implode(';',$aFunctions),1); ?> Bon de livraison
<!--<br>
<?php echo $adv->checkbox("bonstrsptslspj",implode(';',$aFunctions),1); ?> Bon de livraison adresse installateur <span id="bonslsname"></span>-->
<br>
<?php echo $adv->checkbox("facturetrsptpj",implode(';',$aFunctions),1); ?> Facture (pour commande export uniquement)
<br>
<?php echo $adv->checkbox("othertrsptpj",implode(';',$aFunctions),1); ?> Autre : <input type="file" name="othertrsptpjfile">