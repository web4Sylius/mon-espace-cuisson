<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/admin/admin_global.php");

if(!isset($_GET['installateur_id']) || !is_numeric($_GET['installateur_id']))
{
	header('Location: /admin/rubriques/port_installateur/');
	exit;
} 
$fournisseur_id = $_GET['installateur_id'];

require_once(SITE_PATH . "/class/fournisseur.class.php");

//Infos sur l'installateur en cours
$oFourn = new fournisseur($DB_site2,'',$fournisseur_id);
$oListe = new liste($DB_site2, $_GET);

//Requête de suppression d'enregitrement
if(isset($_POST['del_id']) && is_array($_POST['del_id']))
{
    foreach($_POST['del_id'] as $id)
    {
        $sql = "DELETE FROM zone_type WHERE prix_id IN (SELECT prix_id FROM prix_installateur WHERE zone_id = " . $id . ")";
        $oListe->delete($sql);

        $sql = "DELETE FROM prix_installateur WHERE zone_id = " . $id;
        $oListe->delete($sql);

        $sql = "DELETE FROM zone_pays WHERE zone_id = " . $id;
        $oListe->delete($sql);

        $sql = "DELETE FROM zone_departement WHERE zone_id = " . $id;
        $oListe->delete($sql);

        $sql = "DELETE FROM zone WHERE zone_id = " . $id;
        $oListe->delete($sql);
    }
}

//Paramétrage du fil d'ariane (respecter l'ordre dans l'arbo)
$aFilAriane = array(
	array(
		'titre' => 'Installateurs',
		'lien'  => 'port_installateur/'
	),
	array(
		'titre' => 'Zones "' . $oFourn->fournisseur_nom . '"'
	)
);

$oListe->setFilAriane($aFilAriane);
$oListe->setTitre('Zones "' . $oFourn->fournisseur_nom . '"');
$oListe->creation = true;


$oListe->suppression = true;
$oListe->edition     = true;

//Paramétrage des entêtes de colonnes
$aEntetes = array(
    'zone_libelle' => 'Zone',
    'departements' => 'Département(s)',
    'pays'         => 'Pays',
    'prix'         => 'Tranches de prix'
);

$sql = "SELECT zone_id, 
zone_libelle, 
CONCAT('<a href=\"/admin/rubriques/port_installateur/zones/departements/index.php?installateur_id=" . $oFourn->fournisseur_id . "&zone=',z.zone_id,'\">',(SELECT COUNT(*) FROM zone_departement zd WHERE zd.zone_id = z.zone_id),'</a>'), 
CONCAT('<a href=\"/admin/rubriques/port_installateur/zones/pays/index.php?installateur_id=" . $oFourn->fournisseur_id . "&zone=',z.zone_id,'\">',(SELECT COUNT(*) FROM zone_pays zp WHERE zp.zone_id = z.zone_id),'</a>'),
CONCAT('<a href=\"/admin/rubriques/port_installateur/zones/prix/index.php?installateur_id=" . $oFourn->fournisseur_id . "&zone=',z.zone_id,'\">',(SELECT COUNT(*) FROM prix_installateur pi WHERE pi.zone_id = z.zone_id),'</a>') as prix,
(SELECT COUNT(*) FROM zone_departement zd WHERE zd.zone_id = z.zone_id) AS departements,
(SELECT COUNT(*) FROM zone_pays zp WHERE zp.zone_id = z.zone_id) AS pays,
(SELECT COUNT(*) FROM prix_installateur pi WHERE pi.zone_id = z.zone_id) AS prix";
$sql .= " FROM zone z";
$sql .= " WHERE z.fc_id = " . $oFourn->fournisseur_id;

$oListe->setEntetes($aEntetes);
$oListe->setContentSQL($sql);

include_once($oListe->header);
include_once($oListe->menu);
$oListe->showContent();
?>
<link href="/admin/css/style.css" type="text/css" rel="stylesheet">
<?php
include_once($oListe->footer);
