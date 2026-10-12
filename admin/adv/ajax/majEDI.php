<?php

require_once($_SERVER['DOCUMENT_ROOT']."/admin/admin_global.php");
require_once(BACK_PATH."/adv/adv.class.php");


$fournisseur_nom = $_GET['name'];
$commande_id     = $_GET['facturid'];
$adv_id          = $_GET['advid'];

$adv = new adv($DB_site2, $adv_id);
$adv->locked = isset($_GET['annule']);

switch($fournisseur_nom){
	case 'Acilyon (TEST)':
		$edi   = true;
		$noedi = true;
	break;
	case 'DB Schenker France':
	case 'DB Schenker National':
	case 'DB Schenker Lyon':
		$edi   = true;
		$noedi = true;
	break;
	case 'HEPPNER Le Mans':
	case 'HEPPNER LYON':
		$edi   = true;
		$noedi = true;
	break;
	case 'GLS':
		$edi   = true;
		$noedi = true;
	break;
	default:
		$edi   = true;
		$noedi = true;
	break;
}

if($noedi){
	//PARAMS "sans EDI"
	$aLibNoEDI  = array("Pas d'envoi EDI" => 0);
	$aFuncNoEDI = array(
		"adv_save('envoiedi',3)",
		"loadStep('trspt4', '/admin/adv/ajax/majTypeTrspt.php?name=".$fournisseur_nom."&facturid=".$commande_id."&advid=".$adv_id."')"
	);
	$aOptNoEDI  = array("class" => "edibtn btn0");
}

if($edi){
	//PARAMS "avec EDI"
	$aLibEDI  = array("Envoi vers EDI" => 1);
	$aFuncEDI = array(
		"adv_save('envoiedi',3)",
		"loadStep('trspt4', '/admin/adv/ajax/majTypeTrspt.php?name=".$fournisseur_nom."&facturid=".$commande_id."&advid=".$adv_id."')"
	);
	$aOptEDI  = array("class" => "edibtn btn1");
}
?>
<p style="padding:5px; margin:0;">3. EDI</p>
<div style="margin:10px;">
	<?php
	if($noedi){
		echo $adv->radio_inputs("envoiedi", $aLibNoEDI, implode(';',$aFuncNoEDI), $aOptNoEDI);
	}
	if($edi){
		if(!$noedi){ //Si l'option "sans EDI" n'est pas affiché, il faut créer l'input hidden obligatoirement avec radio_inputs() et non radio()
			echo $adv->radio_inputs("envoiedi", $aLibEDI, implode(';',$aFuncEDI), $aOptEDI);
		} else {
			echo $adv->radio("envoiedi", $aLibEDI, implode(';',$aFuncEDI), $aOptEDI);
		}		
	}
	?>
</div>