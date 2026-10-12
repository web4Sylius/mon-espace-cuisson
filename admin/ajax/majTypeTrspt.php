<?php
require_once($_SERVER['DOCUMENT_ROOT']."/admin/admin_global.php");
require_once(BACK_PATH."/adv/adv.class.php");

$fournisseur_nom = $_GET['name'];
$commande_id     = $_GET['facturid'];
$adv_id          = $_GET['advid'];

$adv = new adv($DB_site2, $adv_id);
$adv->locked = isset($_GET['annule']);

switch($_GET['name']){
	case 'Acilyon (TEST)':
		$aOptions = array(
			'standard'    => 'SYSTEM<br><i>Livraison standard</i>',
			'top13'       => 'Top13<br><i>Livraison en 24/48h (selon département), garantie avant 13h</i>',
			'top18'       => 'Top18<br><i>Livraison en 24/48h (selon département), garantie avant 18h</i>',
			/*'pallet'      => 'PALLET',
			'express'     => 'EXPRESS',*/
			'direct'      => 'DIRECT<br><i>Affrètement, transport direct</i>',
			'standard'    => 'STANDARD<br><i>sans rendez-vous</i>',
			'starprio'    => 'STAR PRIORITY<br><i>Garantie de délai (en fonction de la zone 24/48h)<br>dans la journée jusqu\'à 18h, sans RDV</i>',
			'starprio13h' => 'STAR PRIORITY AVANT 13h<br><i>Garantie de délai (en fonction de la zone 24/48h)<br>avant 13h, sans RDV</i>',
			'stardate'    => 'STAR DATE<br><i>Garantie de "date" dans la journée jusqu\'à 18h, sans RDV</i>',
			'stardate13h' => 'STAR DATE AVANT 13h<br><i>Garantie de "date" avant 13h, sans RDV</i>',
			'rdvpro'      => 'RENDEZ-VOUS<br><i>Livraison chez un professionnel, avec rendez-vous</i>',
			'rdvpart'     => 'PARTICULIER<br><i>Livraison chez un particulier, avec rendez-vous</i>',
			'rdv'         => 'Adresse client avec RDV (particulier)',
			'nordv'       => 'Adresse client sans RDV (professionnel)',
			'ondemand'    => 'On demand'
		);

		$aFunctions = array(
		    "loadStep('trspt5', '/admin/adv/ajax/majProduits.php?name=".$fournisseur_nom."&facturid=".$commande_id."&advid=".$adv_id."')",
            "adv_save('trspttype',4)"
		);
	break;
	case 'DB Schenker National':
	case 'DB Schenker Lyon':
		$aOptions = array(
			'standard' => 'SYSTEM<br><i>Livraison standard</i>',
			'top13'    => 'Top13<br><i>Livraison en 24/48h (selon département), garantie avant 13h</i>',
			'top18'    => 'Top18<br><i>Livraison en 24/48h (selon département), garantie avant 18h</i>',
			/*'pallet'   => 'PALLET',
			'express'  => 'EXPRESS',*/
			'direct'   => 'DIRECT<br><i>Affrètement, transport direct</i>'
		);

		$aFunctions = array(
		    "loadStep('trspt5', '/admin/adv/ajax/majProduits.php?name=".$fournisseur_nom."&facturid=".$commande_id."&advid=".$adv_id."')",
            "adv_save('trspttype',4)"
		);
	break;
	case 'HEPPNER LYON':
	case 'HEPPNER Le Mans':
		$aOptions = array(
			'standard'    => 'STANDARD<br><i>Sans rendez-vous</i>',
			'starprio'    => 'STAR PRIORITY<br><i>Garantie de délai (en fonction de la zone 24/48h)<br>dans la journée jusqu\'à 18h, sans RDV</i>',
			'starprio13h' => 'STAR PRIORITY AVANT 13h<br><i>Garantie de délai (en fonction de la zone 24/48h)<br>avant 13h, sans RDV</i>',
			'stardate'    => 'STAR DATE<br><i>Garantie de "date" dans la journée jusqu\'à 18h, sans RDV</i>',
			'stardate13h' => 'STAR DATE AVANT 13h<br><i>Garantie de "date" avant 13h, sans RDV</i>',
			'rdvpro'      => 'RENDEZ-VOUS<br><i>Livraison chez un professionnel, avec rendez-vous</i>',
			'rdvpart'     => 'PARTICULIER<br><i>Livraison chez un particulier, avec rendez-vous</i>'
		);

		$aFunctions = array(
		    "loadStep('trspt5', '/admin/adv/ajax/majProduits.php?name=".$fournisseur_nom."&facturid=".$commande_id."&advid=".$adv_id."')",
            "adv_save('trspttype',4)"
		);
	break;
	case 'GLS':
		$aOptions = array(
			'classique'   => 'CLASSIQUE'
		);

		$aFunctions = array(
		    "loadStep('trspt5', '/admin/adv/ajax/majProduits.php?name=".$fournisseur_nom."&facturid=".$commande_id."&advid=".$adv_id."')",
            "adv_save('trspttype',4)"
		);
	break;
	case 'FEDEX Express':
		$aOptions = array(
			'tnt_pro'       => 'J<br><i>Professionnel</i>',
			'tnt_part'      => 'JZ<br><i>Particulier</i>',
			'tnt_relais'    => 'JD<br><i>Relais colis</i>'
		);

		$aFunctions = array(
		    "showDetailsTNT(this.value, '".$fournisseur_nom."', '".$commande_id."', '".$adv_id."')",
            "adv_save('trspttype',4)"
		);
	break;	
	default:
		$aOptions = array(
			'rdv'    => 'Adresse client avec RDV (particulier)',
			'nordv'    => 'Adresse client sans RDV (professionnel)',
			'ondemand' => 'On demand'
		);

		$aFunctions = array(
		    "loadStep('trspt5', '/admin/adv/ajax/majProduits.php?name=".$fournisseur_nom."&facturid=".$commande_id."&advid=".$adv_id."')",
            "adv_save('trspttype',4)"
		);
	break;
}

$js_reload = '';
if(isset($adv->steps[45]) && $fournisseur_nom == 'FEDEX Express'){
	$js_reload = "showDetailsTNT('".(isset($adv->fields['trspttype']) ? $adv->fields['trspttype'] : '')."', '".$fournisseur_nom."', '".$commande_id."', '".$adv_id."','".(isset($adv->fields['trspttypedetails']) ? $adv->fields['trspttypedetails'] : '')."','".($adv->locked ? '1' : '')."');";	
	$display_step_bis = "style=\"display:table;\"";
} 

?>
<script type="text/javascript">
	$jQuery( document ).ready(function() {
		<?php echo $js_reload; ?>
	});
</script>
<p style="padding:5px; margin:0;">4. Type de transport</p>
<table border="0" cellpadding="0" cellspacing="0" width="100%">
	<?php
	$i=1;
	foreach($aOptions as $val => $option){
		if($i == 1 || $i%4 == 0) echo "<tr>";

		//PARAMS
		$aLibelle  = array($option => $val);
        echo "<td>".$adv->radio_inputs("trspttype", $aLibelle, implode(';', $aFunctions))."</td>"; 

		if($i == count($aOptions) || $i%3 == 0) echo "</tr>";

		$i++;
	}
	?>
</table>
<table id="steptrsptdetails" border="0" cellpadding="0" cellspacing="0" width="100%" <?php echo $display_step_bis; ?>>
	<tr>
	<!-- Rempli uniquement dans le cas de TNT -->
	</tr>
</table>