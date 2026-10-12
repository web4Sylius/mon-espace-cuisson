<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/admin/admin_global.php");
require_once(SITE_PATH . "/class/fournisseur.class.php");

if(!isset($_GET['installateur_id']) || !is_numeric($_GET['installateur_id']))
{
	header('Location: /admin/rubriques/port_installateur/');
	exit;
} 
if(!isset($_GET['zone']) || $_GET['zone'] == '')
{
	header('Location: /admin/rubriques/port_installateur/zones/?installateur_id=' . $_GET['installateur_id']);
	exit;
} 

$fournisseur_id = $_GET['installateur_id'];
$zone_id        = $_GET['zone'];

//Infos sur l'installateur en cours
$oFourn = new fournisseur($DB_site2,'',$fournisseur_id);
$oForm = new form($DB_site2, $_POST, $_GET);


//Détails zone
$sql = "SELECT zone_libelle FROM zone WHERE zone_id = " . $zone_id;
$zone_libelle = $DB_site2->query_first($sql)['zone_libelle'];
	

//Paramétrage du fil d'ariane (respecter l'ordre dans l'arbo)
$aFilAriane = array(
	array(
		'titre' => 'Installateurs',
		'lien'  => 'port_installateur/'
	),
	array(
		'titre' => 'Zones "' . $oFourn->fournisseur_nom . '"',
		'lien'  => 'port_installateur/zones/?installateur_id=' . $oFourn->fournisseur_id
	),
	array(
		'titre' => 'Tranche(s) de prix zone ' . $zone_libelle,
		'lien'  => 'port_installateur/zones/prix/?installateur_id=' . $oFourn->fournisseur_id . '&zone=' . $zone_id
	),
	array(
		'titre' => 'Association zone/tranche de prix'
	)	
);

//Définition des champs éditables
$sqlSelect = "SELECT livr_type_id, livr_type_nom";
$sqlSelect .= " FROM livraison_type";
$sqlSelect .= " WHERE fc_id = " . $oFourn->fournisseur_id;
$sqlSelect .= " ORDER BY livr_type_nom";

//Définition des champs éditables
$aChamps = array(
	array('libelle' => 'Poids min (kg)', 'type' => 'text', 'name' => 'prix_poidsmin', 'req' => true),
	array('libelle' => 'Poids max (kg)', 'type' => 'text', 'name' => 'prix_poidsmax', 'req' => true),
	array('libelle' => 'Prix de vente (TTC)', 'type' => 'text', 'name' => 'prix_pvttc'),
	array('libelle' => 'Prix d\'achat (HT)', 'type' => 'text', 'name' => 'prix_paht', 'req' => true),
	array('libelle' => 'Type de livraison', 'type' => 'select', 'name' => 'typelivr', 'sql' => $sqlSelect)
);

$oForm->setFilAriane($aFilAriane);
$oForm->setTitre('Association d\'une tranche poids/prix à la zone ' . $zone_libelle);
$oForm->setChamps($aChamps);

if(isset($_GET['action']) && $_GET['action'] == 'edit')
{
	$sql = "SELECT ROUND(pi.prix_poidsmin/1000) as prix_poidsmin, ROUND(pi.prix_poidsmax/1000) as prix_poidsmax, pi.prix_paht, pi.prix_pvttc, zt.livr_type_id as typelivr";
	$sql .= " FROM prix_installateur pi";
	$sql .= " INNER JOIN zone_type zt ON (zt.prix_id = pi.prix_id)";
	$sql .= " WHERE pi.prix_id = " . $id;
	$oForm->setValues($sql);
}

//Traitement du formulaire
if($_POST != array())
{ 
	if($oForm->isValid())
	{
        if(isset($_POST['edit_id']))
        {
            $sql = "UPDATE prix_installateur SET";
            $sql .= " prix_poidsmin = " . $_POST['prix_poidsmin'] * 1000;
            $sql .= ", prix_poidsmax = " . $_POST['prix_poidsmax'] * 1000;
            $sql .= ", prix_paht = " . $_POST['prix_paht'];
            $sql .= ", prix_pvttc = " . $_POST['prix_pvttc'];
            $sql .= " WHERE prix_id = " . $_POST['edit_id'];
            $DB_site2->query($sql);

            $sql = "UPDATE zone_type SET";
            $sql .= " livr_type_id = " . $_POST['typelivr'];
            $sql .= " WHERE prix_id = " . $_POST['edit_id'];
            $DB_site2->query($sql);
        }
        else
        {
            $sql = "INSERT INTO prix_installateur (";
            $sql .= "prix_poidsmin";
            $sql .= ", prix_poidsmax";
            $sql .= ", prix_paht";
            $sql .= ", prix_pvttc";
            $sql .= ", zone_id";
            $sql .= ") VALUES (";
            $sql .= $_POST['prix_poidsmin'] * 1000;
            $sql .= ", " . $_POST['prix_poidsmax'] * 1000;
            $sql .= ", '" . $_POST['prix_paht'] . "'";
            $sql .= ", '" . $_POST['prix_pvttc'] . "'";
            $sql .= ", " . $zone_id;
            $sql .= ")";
            $DB_site2->query($sql);
            $create_id = $DB_site2->insert_id();

            $sql = "INSERT INTO zone_type (";
            $sql .= "livr_type_id";	
            $sql .= ", prix_id";	
            $sql .= ") VALUES (";
            $sql .= $_POST['typelivr'];
            $sql .= ", " . $create_id;
            $sql .= ")";
            $DB_site2->query($sql);
        }

		header('Location: index.php?installateur_id=' . $oFourn->fournisseur_id . '&zone=' . $zone_id . '&ajout=1');
		exit;
	}
}

include_once($oForm->header);
include_once($oForm->menu);
$oForm->showContent();
?>
<link href="/admin/css/style.css" type="text/css" rel="stylesheet">
<?php
include_once($oForm->footer);
