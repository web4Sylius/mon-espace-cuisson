<?php
require "admin_global.php";

include("fonctions.php");
include "./header.php";
include "./menu.php";

$req=$DB_site2->query("SELECT DISTINCT artcode,artid,emballage_poids,emballage_largeur,emballage_hauteur,emballage_profondeur FROM article INNER JOIN article_lot USING(artid) WHERE (emballage_hauteur = 0 OR emballage_largeur = 0 OR emballage_profondeur=0 OR emballage_poids = 0) AND active='1' AND commandable='1'");
$contenu ="<center><h3>Veuillez renseigner les dimensions brutes de ces articles pour le colisage</h3>";
$contenu .= "<table>";
while($row = $DB_site2->fetch_array($req)){
	$contenu .= "<tr><td>".$row['artcode']."</td><td><a href=\"ajout_produit.php?artid=".$row['artid']."&prec=modif\">Modifier</a></td></tr>";
}

$contenu .= "</table><br><a href=\"/admin\">retour</a></center><br></center>";

echo $contenu;














include "./footer.php";

?>