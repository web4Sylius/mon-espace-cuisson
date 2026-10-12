<?php
/**
 * Ce fichier sert à récupérer les types de transports possible pour un nom de fournisseur passé en paramètres
 * Le résultat est une liste d'input radio
 */
require_once($_SERVER['DOCUMENT_ROOT']."/admin/admin_global.php");
require_once(BACK_PATH."/adv/adv.class.php");
require_once(SITE_PATH."/class/fournisseur.class.php");

$poids       = isset($_GET['poids']) && is_numeric($_GET['poids']) ? $_GET['poids'] : 0;
$departement = isset($_GET['departement']) && is_numeric($_GET['departement']) ? $_GET['departement'] : 0;
$pays        = isset($_GET['pays']) && is_numeric($_GET['pays']) ? $_GET['pays'] : 0;
$recup_cave  = isset($_GET['recup_cave']) && $_GET['recup_cave'] == 'recup_cave' ? 'recup_cave' : '';

$oInstallateur = new fournisseur($DB_site2, $_GET['installateur']);
$oAdv = new adv($DB_site2);

$params=array('poids' => $poids, 'pays' => $pays, 'recup_cave' => $recup_cave);
if(in_array($pays,array(57,181))) $params['departement']=$departement;
$aListeTypeLivraison = $oInstallateur->getListeTypeLivraison($params);

$return = '';
if(!empty($aListeTypeLivraison))
{
	$cpt = 0;
	foreach($aListeTypeLivraison as $le_type_livraison)
	{
		$input_values = array($le_type_livraison['livr_type_nom'] => $le_type_livraison['livr_type_id']);
		$input_actions = "showstep('sls10'); adv_save('typetrspt',10)";
		$input_actions .= "; tarif_transport(" . $oInstallateur->fournisseur_id . ")";

		$function = 'radio_inputs';
		if($cpt > 0) $function = 'radio';

		$return .= $oAdv->$function("typetrspt", $input_values, $input_actions);
		
		$cpt++;
	}
}
else
{
	$input_values = array("Aucun" => "0");
	$input_actions = "showstep('sls10'); adv_save('typetrspt',10);";

	$return = $oAdv->radio_inputs("typetrspt", $input_values, $input_actions);
}

echo $return;
