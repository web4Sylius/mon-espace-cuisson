<?php
include 'global.php';
//exports bdd article

//article_bbq

$sql = "SELECT `id`, `artid`, `type_bbq`, `utilisation`, `systeme_allumage`, `affichage`, `consommation_gaz`, `consommation_elec`, `thermostat_reglable`, `type_bouton_reglage`, `surface_cuisson` as surface_de_cuisson, `matiere_gille`, `plancha`, `fonctionalites`, `nb_plan_travail`, `dimensions_plan_travail` FROM article_bbq ";

// article congele
$sql= "SELECT `id`, `artid`, `position_congel`, `volume_congel`, `type_froid_congel`, `degivrage_congel`, `anti_givre` as nofrost, `congelation_rapide`, `compartiment_fraicheur_congel` FROM `monespacecuisson`.`article_congel`";

//article four ==> Tout prendre

//article frigo
$sql = "SELECT `id`, `artid`, `position_frigo`, `volume_frigo`, `type_froid_frigo`, `degivrage_auto`, `refrigeration_rapide`, `clayettes`, `compartiment_fraicheur` as compartiment, `balconnet`, `version_porte` FROM `monespacecuisson`.`article_frigo`";

//article froid
$sql = "SELECT `id`, `artid`, `type_pose`, `type_refrigerateur`, `volume_total`, `type_froid`, `alarme`, `ecran_affichage`, `fonction_vacances`, `autonomie`, `niveau_sonore`, `porte_reversible`, `classe_climatique`, `bar`, `eclairage_bar`, `compartiments_independants`, `technologie_specifique`, `nofrost` FROM `monespacecuisson`.`article_froid`";

//article hotte
$sql = "SELECT `id`, `artid`, `type_hotte`, `finition_hotte`, `utilisation`, `forme`, `type_aspiration`, `nb_vitesses`, `niveau_sonore`, `niveau_sonore_mini`, `emplacement_cmd`, `type_cmd_hotte`, `type_cmd_hotte_categorie`, `fonction_booster_hottes`, `touche_marche_arret`, `telecommande`, `mode_aspiration`, `fonction_arret_differe`, `debit_air_max`, `debit_air_booster`, `puissance_aspiration`, `pression_mini`, `pression_maxi`, `puissance_mini`, `puissance_maxi`, `niv_sonore_recyclage`, `niv_sonore_evacuation`, `niv_sonore_booster`, `filtre_graisse`, `filtre_charbon`, `voyant_saturation_filtres`, `hauteur_hotte`, `diametre_sortie_air`, `installation_hotte` FROM `monespacecuisson`.`article_hotte`";

//plancha
$sql = "SELECT `id`, `artid`, `energie`, `type_plancha`, `matiere_plaque`, `consommation_gaz`, `consommation_elec`, `resistances_amovibles`, `thermostat_reglable`, `plage_temperature`, `type_bouton_reglage`, `emplacement_bouton_reglage`, `mode_diffusion_chaleur`, `surface_cuisson` as surface_de_cuisson, `securite_thermocouple`, `nb_plan_travail` FROM `monespacecuisson`.`article_plancha`";

$sql = "SELECT `id`, `artid`, `emplacement`, `ecran_table`, `nature_surface`, `cat_cmd`, `type_cmd`, `nombre_pos_cuisson`, `coloris_plaque`, `minuteur`, `interrupteur`, `verouillage_cmd`, `type_table`, `type_pose_table`, `memo_programme`, `support_wok`, `maintien_chaud`, `bruleur_wok`, `bruleur_rapide`, `bruleur_semi_rapide`, `bruleur_auxiliere`, `injecteur`, `nb_foyers`, `type_foyers_1`, `nb_foyers_1`, `type_foyers_2`, `nb_foyers_2`, `type_foyer_categorie`, `type_allumage`, `type_grille`, `type_couronnes`, `bords`, `equipement_table`, `securite`, `sonde_temp`, `turbine`, `securite_enfants`, `securite_arret_auto`, `temoin_chaleur_res`, `securite_anti_surchauffe`, `securite_anti_deborde`, `detect_casserole`, `classe_energie_table`, `puissance_table`, `techno_specifique_table`, `thermostat_table`, `plancha`, `tapanaki`, `chauffe_plat`, `fonction_pause`, `fonction_autre`, `fonction_autre2` FROM `monespacecuisson`.`article_table_cuisson`";

//script pour rappatrier des données specifiques

//champs dimensions
/*
$sql = "SELECT artid,typearticleid FROM article WHERE typearticleid = 1";
$req = $DB_site->query($sql);
while($result = $DB_site->fetch_array($req)){
	$artmec = $DB_site->query_first("SELECT * FROM article_mec WHERE artid = ".$result[artid]);
	$tablespec = $DB_site->query_first("SELECT bdd_table FROM typearticle WHERE typearticleid = ".$result[typearticleid]);
	$champs = "hauteur = '".$artmec['hauteur']."', largeur='".$artmec['largeur']."', profondeur='".$artmec['profondeur']."', poids = '".$artmec['poid']."',  matiere = '".$artmec['matiere']."', couleur = '".$artmec['coloris']."'";
	$puissance = ", puissance_totale='".$artmec['puissance_totale']."', intensite_electrique='".$artmec['intensite_electrique']."', tension_electrique='".$artmec['tension_electrique']."', frequence='".$artmec['frequence']."'";
	$sql_update = "UPDATE ".$tablespec[0]." SET ".$champs.$puissance." WHERE artid=".$result[artid].";" ;
	$DB_site->query($sql_update);
}
*/
/*
$sql = "SELECT artid,typearticleid FROM article WHERE typearticleid = 25";
$req = $DB_site->query($sql);
while($result = $DB_site->fetch_array($req)){
	//var_dump($result[artid]);
	$artmec = $DB_site->query_first("SELECT * FROM article_mec WHERE artid = ".$result[artid]);
	$tablespec = $DB_site->query_first("SELECT bdd_table FROM typearticle WHERE typearticleid = ".$result[typearticleid]);
	$champs = "hauteur = '".$artmec['hauteur']."', largeur='".$artmec['largeur']."', profondeur='".$artmec['profondeur']."', poids = '".$artmec['poid']."', matiere = '".$artmec['matiere']."', couleur = '".$artmec['coloris']."'";
	$sql_update = "UPDATE ".$tablespec[0]." SET ".$champs." WHERE artid=".$result[artid].";" ;
	$DB_site->query($sql_update);
	echo $sql_update."<br>";
}
*/
$sql = "SELECT artid,typearticleid FROM article WHERE typearticleid = 26";
$req = $DB_site->query($sql);
while($result = $DB_site->fetch_array($req)){
	//var_dump($result[artid]);
	$artmec = $DB_site->query_first("SELECT * FROM article_mec WHERE artid = ".$result[artid]);
	$tablespec = $DB_site->query_first("SELECT bdd_table FROM typearticle WHERE typearticleid = ".$result[typearticleid]);
	$champs = "hauteur = '".$artmec['hauteur']."', largeur='".$artmec['largeur']."', profondeur='".$artmec['profondeur']."', poids = '".$artmec['poid']."', matiere = '".$artmec['matiere']."', couleur = '".$artmec['coloris']."'";
	$puissance = ", puissance_totale='".$artmec['puissance_totale']."', intensite_electrique='".$artmec['intensite_electrique']."', tension_electrique='".$artmec['tension_electrique']."', frequence='".$artmec['frequence']."'";
	$sql_update = "UPDATE ".$tablespec[0]." SET ".$champs.$puissance." WHERE artid=".$result[artid].";" ;
	$DB_site->query($sql_update);
	echo $sql_update."<br>";
}