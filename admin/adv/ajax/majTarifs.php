<?php
require_once($_SERVER['DOCUMENT_ROOT']."/admin/admin_global.php");
require_once(BACK_PATH."/adv/adv.class.php");
require_once(BACK_PATH."/facture.class.php");
require_once(SITE_PATH."/class/tnt.class.php");

$fournisseur_nom = $_GET['name'];
$commande_id     = $_GET['facturid'];
$adv_id          = $_GET['advid'];

$facture = new facture($DB_site2, $commande_id);
$adv     = new adv($DB_site2, $adv_id);
$adv->locked = isset($_GET['annule']);

$coutTrspt  = 0;
$erreur_edi = '';
if($adv->destinataire == 'TNT National'){
	include_once(SITE_PATH."/configurations/".$host."/transporteurs/tnt/tnt.php");
	$tnt = new tnt($DB_site2, $adv,"STDA4",$username,$pass);

	if(ESPACE != 'prod') $tnt->test_mode = true; //Permet d'éviter de faire la demande de ramassage à TNT

	$coutTrspt = $tnt->getCout();

	if($tnt->getError() !== '') $erreur_edi = $tnt->getError();
}

//Mise en place de la liste déroulante des moyens de paiement
$sql = "SELECT moyenid, libelleFR";
$sql .= " FROM moyenpaiement";
$sql .= " WHERE moyenid NOT IN (0,3)";
$sql .= " ORDER BY libelleFR";
$requete = $DB_site2->query($sql);
$opt_moyen[0] = "-- Sélectionner --";
while($le_moyen = $DB_site2->fetch_array($requete)){
    $opt_moyen[$le_moyen['moyenid']] = $le_moyen['libelleFR'];
}

//PARAMS "Cout transport"
$aFuncCoutTrspt = array(
     "trspttarif()",
     "adv_save(%name)",
     "adv_save('trsptcoutval')"
);

//PARAMS "Contre remboursement"
$aFuncCtrRemb = array();
if($fournisseur_nom != 'TNT National' && $fournisseur_nom != 'GLS'){
	$aFuncCtrRemb = array(
		"trspttarif()",
		"adv_save(%name)"
	);
}

//PARAMS "Moyen de paiement"
$aFuncMoyPay = array(
     "adv_save(this.name)"
);

//PARAMS "Montant du règlement"
$aFuncMontReg = array(
     "adv_save(this.name)"
);

//PARAMS "Tarif du service"
$aFuncTarifServ = array(
     "adv_save(this.name)"
);
?>
<p style="padding:5px; margin:0;">12. Tarifs</p>
<table>
	<tr>
		<td width="20"></td>
		<td>Valeur HT</td>
		<td>Poids</td>
		<td>Volume</td>
		<td>Poids volume 100kg</td>
		<td>Poids volume 250kg</td>
		<td>Poids volume 300kg</td>
	</tr>
	<tr>
		<td></td>
		<td id="valeurht"></td>
		<td id="coutpoids"></td>
		<td id="volumetotal"></td>
		<td id="poidsvolume100"></td>
		<td id="poidsvolume250"></td>
		<td id="poidsvolume300"></td>
	</tr>
	<tr>
		<td><?php echo $adv->checkbox("trsptcout", implode(';', $aFuncCoutTrspt), 1); ?></td>
		<td colspan="5">
		Cout transport 
		<div style="float:right;">
		CP <span id="cplivr"><?php echo $facture->codepostal." - ".$facture->pays; ?></span>
		</div>
		</td>
		<td><?php echo $adv->nb_input("trsptcoutval","adv_save(this.name)","",$coutTrspt); ?><span id="trsptcout_devise"><?php echo $devises->symbole($adv->devise); ?></span></td>
	</tr>
	<?php if(count($aFuncCtrRemb) > 0){ ?>
	<tr>
		<td><?php echo $adv->checkbox("trspt_ctr_remb", implode(';', $aFuncCtrRemb), 1); ?></td>
		<td>Contre remboursement</td>
		<td colspan="2">Moyen de paiement <?php echo $adv->select("ctr_remb_moyen", $opt_moyen, implode(';', $aFuncMoyPay)); ?></td>
		<td colspan="2">Montant du règlement <?php echo $adv->nb_input("ctr_remb_montant", implode(';', $aFuncMontReg)); ?><span id="ctr_remb_montant_devise"><?php echo $devises->symbole($adv->devise); ?></span></td>
		<td>Tarif du service <?php echo $adv->nb_input("ctr_remb_coutval", implode(';', $aFuncTarifServ)); ?><span id="ctr_remb_coutval_devise"><?php echo $devises->symbole($adv->devise); ?></span></td>
	</tr>
	<?php } ?>
</table>
<div id="priceopts"></div>
<div style="width:100%; text-align:right; font-weight:bold;">Total : <span id="totaltrspt"></span></div>
<input type="hidden" name="valeurdeclaree" value="0"> 
<?php if($erreur_edi != ''){ ?>
<div style="width: 100%; text-align: center;">
	<p style="padding: 0; margin: 0; color: red; font-weight: bold; font-size: 12px;"><?php echo $erreur_edi; ?></p>
</div>
<?php } ?>         
<br>Informations supplémentaires :<br><textarea cols="80" onblur="adv_save('infocomplement')" name="infocomplement"></textarea>