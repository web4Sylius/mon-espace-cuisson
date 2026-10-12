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
$oForm = new form($DB_site2, $_POST);


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
		'titre' => 'Pays zone ' . $zone_libelle,
		'lien'  => 'port_installateur/zones/pays/?installateur_id=' . $oFourn->fournisseur_id . '&zone=' . $zone_id
	),
	array(
		'titre' => 'Association zone/pays'
	)	
);

//Définition des champs éditables
$sqlSelect = "SELECT paysid, libelle";
$sqlSelect .= " FROM pays";
$sqlSelect .= " ORDER BY libelle";


$aChamps = array(
    array('libelle' => 'Pays', 'type' => 'select', 'name' => 'pays', 'sql' => $sqlSelect, 'req' => true, 'multiple' => true)
);
	
	
$oForm->setFilAriane($aFilAriane);
$oForm->setTitre('Association d\'un pays à la zone ' . $zone_libelle);
$oForm->setChamps($aChamps);

//Traitement du formulaire
if($_POST != array())
{ 
	if($oForm->isValid())
    {
        foreach($_POST['pays'] as $le_pays)
        {
            $sql = "INSERT INTO zone_pays (";
            $sql .= "zone_id";
            $sql .= ", paysid";
            $sql .= ") VALUES (";
            $sql .= $zone_id;
            $sql .= "," . $le_pays;
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
