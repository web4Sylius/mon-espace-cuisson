<?php

include_once("host_config_admin.php");

require_once($rootpath."class/template.class.php");
require_once($rootpath."class/facture.class.php");
require_once($rootpath."admin/mpfields.php");
require_once($rootpath."admin/export/xlexport.php");
require_once($rootpath."admin/ftp.php");
require_once($rootpath."class/fonctions.php");

if(isset($_POST['rechercher']) && $_POST['du'] != '' && $_POST['au'] != '') {
    if($_POST['du']==""){
        $date_du=date("d/m/Y", mktime(date("H"), date("i"), date("s"), date("n"), date("j"), date("Y")-2));
    } else {
        $date_du=$_POST['du'];
    }
    if($_POST['au']==""){
        $date_au=date("d/m/Y", mktime(date("H"), date("i"), date("s"), date("n"), date("j")+1));
    } else {
        $date_au=$_POST['au'];
    }

    $dt = DateTime::createFromFormat('d/m/Y', $date_au);
    $dateau = strtotime($dt->format('Y-m-d'));
    $dd = DateTime::createFromFormat('d/m/Y', $date_du);
    $datedu = strtotime($dd->format('Y-m-d'));

    $datedu_name = $dd->format('d-m-Y');
    $dateau_name = $dt->format('d-m-Y');

    $DB_site = clone $DB_site2;
    
    $mcav_dossier = 'admin/export/sav_liste/';

//Préparation du fichier csv pour le GEM
    $csv =  new xlexport("csv");
    $csv->name            = "sav_".$datedu_name."_".$dateau_name;
    $csv->csvsep          = ";";
    $csv->striptags       = false;
    $filename_source      = $mcav_dossier.$csv->name.'.csv';

//Listing des entêtes du fichier CSV
    $head = array(
    "Numero commande", //Obligatoire
    "Date commande", //Obligatoire
    "Nom", //Obligatoire
    "Prenom", //Obligatoire
    "Societe", //Facultatif
    "Montant HT", //Obligatoire
);
    $headkeys = array_flip($head);
    $csv->addrow(array("Liste du ".$datedu_name." au ".$dateau_name));
    //Insertion des entêtes dans le fichier, avec séparateur ; et sans les " entre les termes
    $csv->addrow($head, false);

    $requete=$DB_site->query("SELECT DISTINCT * FROM facture f INNER JOIN utilisateur u ON f.userid = u.userid INNER JOIN lignefacture lf ON lf.factureid = f.provenanceid WHERE f.provenance = 'sav' AND f.heurefacture BETWEEN ".$datedu." and ".$dateau." GROUP BY f.facturid");

    while($infos=$DB_site->fetch_array($requete)){
        $facture = new facture($DB_site,$infos['facturid']);
        $csv->addrow(array($infos['facturid'], $infos['datefacture'], $facture->utilisateur->nom, $facture->utilisateur->prenom, $facture->utilisateur->raisonsociale, $facture->totalht));
    }

    $csv->export();
}
else {
   
include $rootpath."admin/header.php";
include $rootpath."admin/menu.php";

echo "<h1>Générer la liste des SAV</h1>

<p>Permet de générer un fichier excel concernant les informations des commandes SAV (à partir du 2ème rang).
Veuillez choisir les dates :</p>
<form action=\"script_sav_liste.php?action=rechercher\" method=\"post\">
Entre le <input type=\"text\" id=\"du\" name=\"du\" value=\"".$date_du."\" onclick=\"calendrier_show(this.id,this.value)\" size=\"8\" readonly>
    et le <input type=\"text\" id=\"au\" name=\"au\" value=\"".$date_au."\" onclick=\"calendrier_show(this.id,this.value)\" size=\"8\" readonly>
    <input type=\"submit\" name=\"rechercher\" value=\"Télécharger\">
    </form>";

}