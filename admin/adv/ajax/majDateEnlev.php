<?php
require_once($_SERVER['DOCUMENT_ROOT']."/admin/admin_global.php");
require_once(BACK_PATH."/adv/adv.class.php");

$fournisseur_nom = $_GET['name'];
$commande_id     = $_GET['facturid'];
$adv_id          = $_GET['advid'];

$adv = new adv($DB_site2, $adv_id);
$adv->locked = isset($_GET['annule']);

//PARAMS "Date enlèvement fournisseur"
$aLibDateEnlev = array("Date enlèvement chez fournisseur" => "fournisseur");
$aFuncDateEnlev = array(
  "loadStep('trspt9', '/admin/adv/ajax/majAdrLivr.php?name=".$fournisseur_nom."&facturid=".$commande_id."&advid=".$adv_id."')",
  "adv_save('trsptenl',8)"
);

//PARAMS "Date livraison en vos locaux par le transporteur du fournisseur"
$aLibDateLivrLocaux = array("Date livraison en vos locaux par le transporteur du fournisseur" => "eberhardt");
$aFuncDateLivrLocaux = array(
  "loadStep('trspt9', '/admin/adv/ajax/majAdrLivr.php?name=".$fournisseur_nom."&facturid=".$commande_id."&advid=".$adv_id."')",
  "adv_save('trsptenl',8)"
);
?>
<p style="padding:5px; margin:0;">8. Date enlèvement</p>
<b>Date livraison commande : <?php echo $datelivrtxt; ?></b>
<table>
	<tr>
		<td><?php echo $adv->radio_inputs("trsptenl", $aLibDateEnlev, implode(';',$aFuncDateEnlev)); ?></td>
		<td rowspan="2" style="border-left:solid thin black;">Date<br><?php echo $adv->cal_input("trsptenlfourndate","adv_calsave"); ?></td>
	</tr>
	<tr>
		<td><?php echo $adv->radio("trsptenl", $aLibDateLivrLocaux, implode(';',$aFuncDateLivrLocaux)); ?></td>
	</tr>
</table>