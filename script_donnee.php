<?php 
include('global.php');

//facture, pas besoin de script
//idem lignefacture
// lignefacturecaracteristique excepté "artid"
//idem lignefacturediss
//idem lignefacturegarantie
//utilisateur_assoc pas besoin de script
//idem statistique_utilisateur
//idem statistique_produit_utilisateur
//idem facture fournisseur


//utilisateur

$DB_site->query("TRUNCATE TABLE utilisateur");
$sql = "SELECT * FROM utilisateur_mec";
$result = $DB_site->query($sql);
while($row = $DB_site->fetch_array($result)){
	$req = "INSERT INTO `utilisateur` SET `userid`='$row[userid]', `mail`='".mysqli_real_escape_string($DB_site->link_id,$row[mail])."', `password`='".mysqli_real_escape_string($DB_site->link_id,$row[password])."', `civilite`='$row[civilite]', `nom`='".mysqli_real_escape_string($DB_site->link_id,$row[nom])."', `prenom`='".mysqli_real_escape_string($DB_site->link_id,$row[prenom])."', `codepostal`='$row[codepostal]', `adresse`='".mysqli_real_escape_string($DB_site->link_id,$row[adresse])." ".mysqli_real_escape_string($DB_site->link_id,$row[adresse2])."', `ville`='".mysqli_real_escape_string($DB_site->link_id,$row[ville])."', `telephone`='".mysqli_real_escape_string($DB_site->link_id,$row[telephone])."', `raisonsociale`='".mysqli_real_escape_string($DB_site->link_id,$row[raisonsociale])."', `dateinscription`='$row[dateinscription]', `paysid`='$row[paysid]', `tvaintracom`='$row[tvaintracom]', `siret`='$row[siret]', `profession`='".mysqli_real_escape_string($DB_site->link_id,$row[profession])."', `site`='".mysqli_real_escape_string($DB_site->link_id,$row[site])."', `site_code`='base', `langue`='FR', `allowemail`='0', `allowsms`='0', `groupeid`='$row[groupeid]', `pctremise`='$row[pctremise]', `optionnel`='$row[optionnel]', `portable`='$row[portable]', `nom_livraison`='".mysqli_real_escape_string($DB_site->link_id,$row[nom_livraison])."', `prenom_livraison`='".mysqli_real_escape_string($DB_site->link_id,$row[prenom_livraison])."', `codepostal_livraison`='$row[codepostal_livraison]', `adresse_livraison`='".mysqli_real_escape_string($DB_site->link_id,$row[adresse_livraison])."', `ville_livraison`='".mysqli_real_escape_string($DB_site->link_id,$row[ville_livraison])."', `paysid_livraison`='$row[paysid_livraison]', `raisonsociale_livraison`='".mysqli_real_escape_string($DB_site->link_id,$row[raisonsociale_livraison])."', `infotranspo`='".mysqli_real_escape_string($DB_site->link_id,$row[infotranspo])."', `telephone_livraison`='".mysqli_real_escape_string($DB_site->link_id,$row[telephone_livraison])."', `contacthour`='$row[contacthour]', `communication`='$row[communication]', `infos`='".mysqli_real_escape_string($DB_site->link_id,$row[infos])."', `connu`='$row[connu]', `vip`='$row[vip]', `commercial`='$row[commercial]', `besoin`='".mysqli_real_escape_string($DB_site->link_id,$row[besoin])."', `code411`='$row[code411]', `provenance`='', `utilisateur_type_suivi_id`='1', `utilisateur_admin`='$row[utilisateur_admin]',`pourcentage_remise`='$row[pourcentage_remise]', `conditions_reglement`='$row[condition_reglement_id]',`conseils`='non';";
	echo $req."<br>";

	$DB_site->query($req);
}