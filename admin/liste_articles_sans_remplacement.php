<?php 
require "admin_global.php";

include("fonctions.php");
include "./header.php";
include "./menu.php";

$req = $DB_site2->query("SELECT DISTINCT a.artcode as artcode,a.artid as artid, a.libelleFR as libelle FROM article a INNER JOIN typearticle USING(typearticleid) WHERE a.typearticleid IN (1,11,5) AND a.active=\"3\" AND a.artid NOT IN (select artid FROM article_remplacement) ORDER BY a.catid");


$contenu ="<center><h3>Veuillez renseigner les articles de remplacement pour ces articles</h3>";
$contenu .= "<table>";
while($row = $DB_site2->fetch_array($req)){
	$contenu .= "<tr><td>".$row['artcode']."</td><td><a href=\"ajout_produit.php?artid=".$row['artid']."&prec=modif\">Modifier</a></td></tr>";
}

$contenu .= "</table><br><a href=\"/admin\">retour</a></center><br>";

echo $contenu;


include "./footer.php";

?>