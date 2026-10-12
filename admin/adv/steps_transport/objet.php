<?php
if(!$adv->locked && $adv->fields["trsptobj"]=="annule") $adv->save_field("typecmd","cmd");
if($adv->locked) $adv->save_field("trsptobj","annule");
if($adv->modif) $adv->save_field("trsptobj","modif");

//Selon l'état de la commande de transport, on affiche ou non les options creation/édition/annulation
$display_creation   = '';
$display_annulation = '';
$display_edition    = '';
if($adv->modif || $adv->locked) $display_creation = 'display:none;';
if($adv->modif || !$adv->locked) $display_annulation = 'display:none;';
if(!$adv->modif) $display_edition = 'display:none;';

//PARAMS options CREATION
$aLibCreation = array("Commande n°".$facturid => "cmd");
$aFuncCreation = array(
	"showstep('trspt2')",
	"adv_save('trsptobj',1)"
);

//PARAMS options ANNULATION
$aLibAnnulation = array("Annulation de la commande n°".$facturid => "annule");
$aFuncAnnulation = array(
	"showstep('trspt12')", //On saute toutes les étapes pour finaliser immédiatement l'annulation
	"adv_save('trsptobj',1)"
);

//PARAMS options EDITION
$aLibEdition = array("Modifier commande" => "modif");
$aFuncEdition = array(
	"showstep('trspt2')",
	"adv_save('trsptobj',1)"
);

//Si la commande est un SAV, on donne la possibilité de créer une commande de transport suite à avarie de transport sur commande principale
$affiche_avarie = false;
if($rs_facture['provenance'] == "sav"){
	$affiche_avarie = true;

	//PARAMS options AVARIE
	$aLibAvarie = array("" => "avarie");
	$aFuncAvarie = array(
		"showstep('trspt2')",
		"adv_save('trsptobj',1)"
	);
}
?>

<div id="steptrspt1" class="advgroup">
	<p style="padding:5px; margin:0;">1. Objet du mail</p>
	
	<div style="padding: 5px; <?php echo $display_creation; ?>"><?php echo $adv->radio_inputs("trsptobj", $aLibCreation, implode(';',$aFuncCreation)); ?></div>
	<div style="padding: 5px; <?php echo $display_annulation; ?>"><?php echo $adv->radio("trsptobj", $aLibAnnulation, implode(';',$aFuncAnnulation)); ?></div>
	<div style="padding: 5px; <?php echo $display_edition; ?>" id="modifcmd"><?php echo $adv->radio("trsptobj", $aLibEdition, implode(';',$aFuncEdition)); ?></div>
	
	<?php if($affiche_avarie){ ?>
		<br>
		<?php echo $adv->radio("trsptobj", $aLibAvarie, implode(';',$aFuncAvarie)); ?> Commande n°<?php echo $facturid; ?> - Suite avarie commande n°<?php echo $rs_facture['provenanceid']; ?> (pour non paiement des frais de port = avarie transporteur)
		<input type="hidden" name="provenanceid" value="<?php echo $rs_facture['provenanceid']; ?>">
	<?php } ?>

</div>