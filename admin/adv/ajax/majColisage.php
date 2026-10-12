<?php
require_once($_SERVER['DOCUMENT_ROOT']."/admin/admin_global.php");
require_once(BACK_PATH."/adv/adv.class.php");

$fournisseur_nom = $_GET['name'];
$commande_id     = $_GET['facturid'];
$adv_id          = $_GET['advid'];

$adv = new adv($DB_site2, $adv_id);
$adv->locked = isset($_GET['annule']);

//Définition de la limite de poids livrable par ce transporteur
switch($fournisseur_nom){
	case 'GLS':
		$limite_poids = 30;
	break;
	case 'TNT National':
		$limite_poids = 30;
	break;
	default:
		$limite_poids = 0;
	break;
}

//PARAMS "colisage simple"
$aLibColisSimple = array('Colisage simple (1 seul colis)' => 0);
$aFuncColisSimple = array(
	"adv_save(%name,6)",
	"show_colis(this.value)",
	"colis_prepare(this.value)",
	"colis_add('colis','',this.value)"
);

$aLibColisMulti = array('Colisage multiple' => 1);
$aFuncColisMulti = array(
	"adv_save('pcolis',6)",
	"show_colis(this.value)",
	"colis_prepare(this.value)"
);
?>
<p style="padding:5px; margin:0;">6. Colisage</p>
<div style="padding: 5px;">
    <?php echo $adv->radio_inputs("pcolis", $aLibColisSimple, implode(';',$aFuncColisSimple)); ?>
    <?php echo $adv->radio("pcolis", $aLibColisMulti, implode(';',$aFuncColisMulti)); ?>
    <div id="colis_simple">
    	<div id="colis_simple_dim"></div>
    	<center><input type="button" value="Valider" onclick="if(checkPoidsColis(<?php echo $limite_poids; ?>,'<?php echo $fournisseur_nom; ?>')){ loadStep('trspt7', '/admin/adv/ajax/majLieuEnlev.php?name=<?php echo $fournisseur_nom; ?>&facturid=<?php echo $commande_id; ?>&advid=<?php echo $adv_id; ?>'); }" style="width: 100px;"></center>
    </div>
    <div id="colis_conf">
        <input type="hidden" name="nb_palette" id="nb_palette" value="0">
        <div id="colis_list">
            <a href="javascript:void(0)" onclick="colis_add('colis')"><img src="images/icon/edit_add.png"> Ajouter un colis</a>
            <!-- Commenté pour éviter de commander des palettes consignées, ce que nous ne faisons jamais -->
            <!-- <a href="javascript:void(0)" onclick="colis_add('palette')"><img src="images/icon/edit_add.png"> Ajouter une palette</a> -->
        </div>
        <div id="art_list"></div>
        <div class="clear"></div>
        <center><input type="button" value="Valider" onclick="if(checkPoidsColis(<?php echo $limite_poids; ?>,'<?php echo $fournisseur_nom; ?>')){ loadStep('trspt7', '/admin/adv/ajax/majLieuEnlev.php?name=<?php echo $fournisseur_nom; ?>&facturid=<?php echo $commande_id; ?>&advid=<?php echo $adv_id; ?>'); }" style="width: 100px;"></center>
    </div>
</div>
