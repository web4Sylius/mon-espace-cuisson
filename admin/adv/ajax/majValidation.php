<?php
require_once($_SERVER['DOCUMENT_ROOT']."/admin/admin_global.php");
require_once(BACK_PATH."/adv/adv.class.php");

$fournisseur_nom = $_GET['name'];
$commande_id     = $_GET['facturid'];
$adv_id          = $_GET['advid'];

$adv = new adv($DB_site2, $adv_id);
$adv->locked = isset($_GET['annule']);

$envoidate = null;
if($adv->envoi>0) $envoidate = date("d/m/Y",$adv->envoi);
$henvoi = "";
if($adv->destinataire != ""){
	$sql = "SELECT *";
	$sql .= " FROM adv_heure";
	$sql .= " WHERE fournisseur = '".$adv->destinataire."'";
    $h = $DB_site2->query_first($sql);
    $henvoi = "à ".intval($h['heure'])."h";
}
?>
<div id="verif_envoi">
	<p>Des erreurs ont étés détéctés dans les informations transmises !</p>
	<a onclick="document.getElementById('verif_envoi').style.display='none';">ok</a>
</div>

<input type="button" value="Aperçu" class="preview" onclick="valid2(true);" style="border: 1px solid grey; display: inline-block; height: 40px; cursor: pointer;">
<div style="text-align:left; <?php echo ($adv->locked ? 'display:none;' : 'display:inline-block;'); ?>" style="display: inline-block; border: 1px solid grey; height: 40px;">
	<input type="button" onclick="verif_edi()" value="Envoi immédiat" style="height: 40px; background-color: #CCCCCC; border: 1px solid grey; cursor: pointer;">
</div>
<!--
<div style="text-align:left; <?php echo ($adv->locked ? 'display:none;' : ''); ?>">
	<br>
	<input type="radio" name="envoiauto" onclick="envoidate_save();" <?php echo ($adv->envoi > 0 ? "checked" : ""); ?>> Envoyer la commande automatiquement <span id="envoi1" style="<?php echo ($adv->envoi == 0 ? "display:none;" : ""); ?>"> le <input type="text" id="envoidate" value="<?php echo $envoidate; ?>" size="10" onclick="calendrier_show(this.id,this.value,undefined,envoidate_save);" readonly> <span id="henvoi"><?php echo $henvoi; ?></span> <input type="button" value="Envoi programmé" onclick="document.location='facture.php?action=editer&facturid=<?php echo $facturid; ?>#adv'"></span>
	<br><br>
	<input type="radio" name="envoiauto" onclick="envoidate_save();" ".($adv->envoi==0?"checked":"")."> Envoyer la commande immédiatement <span id="envoi0" style="".($adv->envoi>0?"display:none;":"").""><input type="button" onclick="valid2()" value="Envoi immédiat"></span>
</div>
-->
<div style="<?php echo ($adv->locked ? "" : "display:none;"); ?>; margin-top:10px;"><input type="button" onclick="valid2()" value="Annuler la commande" style="color:red; font-weight:bold;" /></div>