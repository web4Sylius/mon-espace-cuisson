<?php

ini_set("display_errors","1");
error_reporting(E_ALL);

require "admin_global.php";
include "./header.php";
include "./menu.php";

/*
=> dans le config :
$objectif_enregistre[2017] =array(0,160000,170000,150000,160000,160000,170000,190000,130000,160000,155000,235000,260000);
$objectif_facture[2017]    =array(0,177000,178500,158500,160000,151500,161500,207000,147000,143000,146500,218000,243000);
$objectif_mp[2017]         =array(0,16000,12000,16000,20000,20000,23000,21000,14000,22000,18000,22000,44000);
$objectif_sav[2017]        =array(0,0,0,0,0,0,0,0,0,0,0,0,0);

$facture[valide] == 0 => non traité
$facture[valide] == 1 => validé
$facture[valide] == 2 => refusé
$facture[valide] == 4 => abandon
$facture[valide] == 5 => expédié
$facture[valide] == 6 => remboursé
$facture[valide] == 7 => validé en attente de paiement
$facture[valide] == 8 => expédié en attente de paiement
*/

$objectif_total=array();
foreach ($objectif_enregistre as $annee => $array) {
	$objectif_total[$annee]=array();
	foreach ($array as $mois => $value) {
		$objectif_total[$annee][$mois]=$value+$objectif_mp[$annee][$mois];
	}
}
$objectif_facture_total=array();
foreach ($objectif_facture as $annee => $array) {
	$objectif_facture_total[$annee]=array();
	foreach ($array as $mois => $value) {
		$objectif_facture_total[$annee][$mois]=$value+$objectif_mp[$annee][$mois];
	}
}
$objectif_mp_total=array();
foreach ($objectif_mp as $annee => $array) {
	$objectif_mp_total[$annee]=array();
	foreach ($array as $mois => $value) {
		$objectif_mp_total[$annee][$mois]=$value;
	}
}


echo "<h1>Statistiques Chiffre d'Affaires sur Objectifs</h1>";


$provenance_type="'','mp','mobile','sav','avoir'";
$provenance_mp="'mp'";

setlocale(LC_TIME, 'fr', 'fr_FR', 'fr_FR.ISO8859-1');

$jour = date("d");
$mois = date("n");
$annee = date("Y");
$etat = "1,5,7,8";
// $etatpaye = "1,5";
// $etatnonpaye = "7,8";
$etatnonvalide = "0";
$etatfacture = "5,8";
$moyenattente = "2,4,5,14,17,18,99";
$annee_1 = $annee - 1;
$mois_m1 = $mois - 1 < 1 ? 12 : $mois - 1;
if($mois - 1 < 1){
	$annee_m1 = $annee - 1;
} else {
	$annee_m1 = $annee;
}
$t = cal_days_in_month(CAL_GREGORIAN, $mois, $annee);
$t_n1 = cal_days_in_month(CAL_GREGORIAN, $mois, $annee-1);
$t_m1 = cal_days_in_month(CAL_GREGORIAN, $mois_m1, $annee_m1);

$objectif_total_annee=0;
for ($i = 1; $i < date("n"); $i++) {
	$objectif_total_annee+=$objectif_total[$annee][$i];
}
$objectif_total_annee+=objectif_prorata($objectif_total[$annee][$mois], date("n"), date("j"));
$objectif_total_total=0;
for ($i = 1; $i <= 12; $i++) {
	$objectif_total_total+=$objectif_total[$annee][$i];
}

$objectif_total_annee_1=0;
for ($i = 1; $i < date("n"); $i++) {
	$objectif_total_annee_1+=$objectif_total[$annee_1][$i];
}
$objectif_total_annee_1+=objectif_prorata($objectif_total[$annee_1][$mois], date("n"), date("j"));
$objectif_total_total_1=0;
for ($i = 1; $i <= 12; $i++) {
	$objectif_total_total_1+=$objectif_total[$annee_1][$i];
}


$objectif_facture_total_annee=0;
for ($i = 1; $i < date("n"); $i++) {
	$objectif_facture_total_annee+=$objectif_facture_total[$annee][$i];
}
$objectif_facture_total_annee+=objectif_prorata($objectif_facture_total[$annee][$mois], date("n"), date("j"));
$objectif_facture_total_total=0;
for ($i = 1; $i <= 12; $i++) {
	$objectif_facture_total_total+=$objectif_facture_total[$annee][$i];
}

$objectif_facture_total_annee_1=0;
for ($i = 1; $i < date("n"); $i++) {
	$objectif_facture_total_annee_1+=$objectif_facture_total[$annee_1][$i];
}
$objectif_facture_total_annee_1+=objectif_prorata($objectif_facture_total[$annee_1][$mois], date("n"), date("j"));
$objectif_facture_total_total_1=0;
for ($i = 1; $i <= 12; $i++) {
	$objectif_facture_total_total_1+=$objectif_facture_total[$annee_1][$i];
}


$objectif_mp_total_annee=0;
for ($i = 1; $i < date("n"); $i++) {
	$objectif_mp_total_annee+=$objectif_mp_total[$annee][$i];
}
$objectif_mp_total_annee+=objectif_prorata($objectif_mp_total[$annee][$mois], date("n"), date("j"));
$objectif_mp_total_total=0;
for ($i = 1; $i <= 12; $i++) {
	$objectif_mp_total_total+=$objectif_mp_total[$annee][$i];
}

$objectif_mp_total_annee_1=0;
for ($i = 1; $i < date("n"); $i++) {
	$objectif_mp_total_annee_1+=$objectif_mp_total[$annee_1][$i];
}
$objectif_mp_total_annee_1+=objectif_prorata($objectif_mp_total[$annee_1][$mois], date("n"), date("j"));
$objectif_mp_total_total_1=0;
for ($i = 1; $i <= 12; $i++) {
	$objectif_mp_total_total_1+=$objectif_mp_total[$annee_1][$i];
}


echo "(*) = comprend aussi les commandes de l'année précédente<br>
<table class=\"ica_tab\" id=\"ica_tab\">
    <thead>
        <tr>
            <td class=\"ica_thead\" colspan=\"9\">CA Enregistré</td>
        </tr>
    </thead>
	<tr>
        <td colspan=\"2\"></td>
        <td>Année en cours</td>
        <td>&nbsp;</td>
        <td>Mois en cours</td>
        <td>&nbsp;</td>
        <td>Avant hier</td>
        <td>Hier</td>
		<td>Ce jour</td>
    </tr>
	<tr>
		<td rowspan=\"3\">CA</td>
        <td>CA HT enregistré validé au jour</td>
        <td>".number_format(auto_ca("annee", $jour, $mois, $annee, $provenance_type,$etat),0,',',' ')." &euro;</td>
        <td>&nbsp;</td>
        <td>".number_format(auto_ca("mois", $jour, $mois, $annee, $provenance_type,$etat),0,',',' ')." &euro;</td>
        <td>&nbsp;</td>
        <td>".number_format(auto_ca("avanthier", $jour, $mois, $annee, $provenance_type,$etat),0,',',' ')." &euro;</td>
        <td>".number_format(auto_ca("hier", $jour, $mois, $annee, $provenance_type,$etat),0,',',' ')." &euro;</td>
		<td>".number_format(auto_ca("jour", $jour, $mois, $annee, $provenance_type,$etat),0,',',' ')." &euro;</td>
    </tr>
	<tr>
        <td>CA HT enregistré non validé au jour</td>
        <td>".number_format(auto_ca("annee", $jour, $mois, $annee, $provenance_type,$etatnonvalide, "datefacture", $moyenattente),0,',',' ')." &euro; (*)</td>
        <td>&nbsp;</td>
        <td>".number_format(auto_ca("mois", $jour, $mois, $annee, $provenance_type,$etatnonvalide, "datefacture", $moyenattente),0,',',' ')." &euro;</td>
        <td>&nbsp;</td>
        <td>".number_format(auto_ca("avanthier", $jour, $mois, $annee, $provenance_type,$etatnonvalide, "datefacture", $moyenattente),0,',',' ')." &euro;</td>
        <td>".number_format(auto_ca("hier", $jour, $mois, $annee, $provenance_type,$etatnonvalide, "datefacture", $moyenattente),0,',',' ')." &euro;</td>
		<td>".number_format(auto_ca("jour", $jour, $mois, $annee, $provenance_type,$etatnonvalide, "datefacture", $moyenattente),0,',',' ')." &euro;</td>
    </tr>
	<tr>
        <td>CA HT enregistré (dont non validé) au jour</td>
        <td><b>".number_format(auto_ca("annee", $jour, $mois, $annee, $provenance_type,$etat)+auto_ca("annee", $jour, $mois, $annee, $provenance_type,$etatnonvalide, "datefacture", $moyenattente),0,',',' ')." &euro;</b></td>
        <td>&nbsp;</td>
        <td><b>".number_format(auto_ca("mois", $jour, $mois, $annee, $provenance_type,$etat)+auto_ca("mois", $jour, $mois, $annee, $provenance_type,$etatnonvalide, "datefacture", $moyenattente),0,',',' ')." &euro;</b></td>
        <td>&nbsp;</td>
        <td><b>".number_format(auto_ca("avanthier", $jour, $mois, $annee, $provenance_type,$etat)+auto_ca("avanthier", $jour, $mois, $annee, $provenance_type,$etatnonvalide, "datefacture", $moyenattente),0,',',' ')." &euro;</b></td>
        <td><b>".number_format(auto_ca("hier", $jour, $mois, $annee, $provenance_type,$etat)+auto_ca("hier", $jour, $mois, $annee, $provenance_type,$etatnonvalide, "datefacture", $moyenattente),0,',',' ')." &euro;</b></td>
		<td><b>".number_format(auto_ca("jour", $jour, $mois, $annee, $provenance_type,$etat)+auto_ca("jour", $jour, $mois, $annee, $provenance_type,$etatnonvalide, "datefacture", $moyenattente),0,',',' ')." &euro;</b></td>
    </tr>
	<tr>
        <td colspan=\"9\">&nbsp;</td>
	</tr>
	<tr>
		<td rowspan=\"6\">Objectif</td>
        <td>Objectif HT au jour</td>
        <td>".number_format($objectif_total_annee,0,',',' ')." &euro;</td>
        <td>&nbsp;</td>
        <td>".number_format(objectif_prorata($objectif_total[$annee][$mois], date("n"), date("j")),0,',',' ')." &euro;</td>
        <td>&nbsp;</td>
        <td>".number_format(objectif_prorata($objectif_total[$annee][$mois], date("n"), 1),0,',',' ')." &euro;</td>
        <td>".number_format(objectif_prorata($objectif_total[$annee][$mois], date("n"), 1),0,',',' ')." &euro;</td>
		<td>".number_format(objectif_prorata($objectif_total[$annee][$mois], date("n"), 1),0,',',' ')." &euro;</td>
    </tr>
	<tr>
        <td>Av/Ret Objectif &euro; (dont non validé)</td>
        ".tdvalue(auto_ca("annee", $jour, $mois, $annee, $provenance_type,$etat)+auto_ca("annee", $jour, $mois, $annee, $provenance_type,$etatnonvalide, "datefacture", $moyenattente)-$objectif_total_annee,"€",true)."
        <td>&nbsp;</td>
        ".tdvalue(auto_ca("mois", $jour, $mois, $annee, $provenance_type,$etat)+auto_ca("mois", $jour, $mois, $annee, $provenance_type,$etatnonvalide, "datefacture", $moyenattente)-objectif_prorata($objectif_total[$annee][$mois], date("n"), date("j")),"€",true)."
        <td>&nbsp;</td>
        ".tdvalue(auto_ca("avanthier", $jour, $mois, $annee, $provenance_type,$etat)+auto_ca("avanthier", $jour, $mois, $annee, $provenance_type,$etatnonvalide, "datefacture", $moyenattente)-objectif_prorata($objectif_total[$annee][$mois], date("n"), 1),"€",true)."
        ".tdvalue(auto_ca("hier", $jour, $mois, $annee, $provenance_type,$etat)+auto_ca("hier", $jour, $mois, $annee, $provenance_type,$etatnonvalide, "datefacture", $moyenattente)-objectif_prorata($objectif_total[$annee][$mois], date("n"), 1),"€",true)."
		".tdvalue(auto_ca("jour", $jour, $mois, $annee, $provenance_type,$etat)+auto_ca("jour", $jour, $mois, $annee, $provenance_type,$etatnonvalide, "datefacture", $moyenattente)-objectif_prorata($objectif_total[$annee][$mois], date("n"), 1),"€",true)."
    </tr>
	<tr>
        <td>Av/Ret Objectif % (dont non validé)</td>
        ".tdvalue(
			(
				auto_ca("annee", $jour, $mois, $annee, $provenance_type,$etat)
				+auto_ca("annee", $jour, $mois, $annee, $provenance_type,$etatnonvalide, "datefacture", $moyenattente)
				-$objectif_total_annee
			)
			*100/$objectif_total_annee,"%",true)."
        <td>&nbsp;</td>
        ".tdvalue(
			(
				auto_ca("mois", $jour, $mois, $annee, $provenance_type,$etat)
				+auto_ca("mois", $jour, $mois, $annee, $provenance_type,$etatnonvalide, "datefacture", $moyenattente)
				-objectif_prorata( $objectif_total[$annee][$mois], date("n"), date("j") )
			)*100/objectif_prorata(	$objectif_total[$annee][$mois], date("n"), date("j") )
			,"%",true)."
        <td>&nbsp;</td>
        ".tdvalue(
			(
				auto_ca("avanthier", $jour, $mois, $annee, $provenance_type,$etat)
				+auto_ca("avanthier", $jour, $mois, $annee, $provenance_type,$etatnonvalide, "datefacture", $moyenattente)
				-objectif_prorata($objectif_total[$annee][$mois], date("n"), 1 )
			)*100/objectif_prorata(	$objectif_total[$annee][$mois], date("n"), 1 )
			,"%",true)."
        ".tdvalue(
			(
				auto_ca("hier", $jour, $mois, $annee, $provenance_type,$etat)
				+auto_ca("hier", $jour, $mois, $annee, $provenance_type,$etatnonvalide, "datefacture", $moyenattente)
				-objectif_prorata( $objectif_total[$annee][$mois], date("n"), 1 )
			)*100/objectif_prorata( $objectif_total[$annee][$mois], date("n"), 1 )
			,"%",true)."
		".tdvalue(
			(
				auto_ca("jour", $jour, $mois, $annee, $provenance_type,$etat)
				+auto_ca("jour", $jour, $mois, $annee, $provenance_type,$etatnonvalide, "datefacture", $moyenattente)
				-objectif_prorata( $objectif_total[$annee][$mois], date("n"), 1	)
			)*100/objectif_prorata(	$objectif_total[$annee][$mois], date("n"), 1 )
			,"%",true)."
    </tr>
	<tr>
        <td colspan=\"8\">&nbsp;</td>
	</tr>
	<tr>
        <td>Objectif total de la période en cours</td>
        <td>".number_format($objectif_total_total,0,',',' ')." &euro;</td>
        <td>&nbsp;</td>
        <td>".number_format($objectif_total[$annee][$mois],0,',',' ')." &euro;</td>
        <td colspan=\"4\">&nbsp;</td>
    </tr>
	<tr>
        <td>Reste à faire CA / objectif</td>
        <td>".number_format($objectif_total_total-auto_ca("annee", $jour, $mois, $annee, $provenance_type,$etat)-auto_ca("annee", $jour, $mois, $annee, $provenance_type,$etatnonvalide, "datefacture", $moyenattente),0,',',' ')." &euro;</td>
        <td>&nbsp;</td>
        <td>".number_format($objectif_total[$annee][$mois]-auto_ca("mois", $jour, $mois, $annee, $provenance_type,$etat)-auto_ca("mois", $jour, $mois, $annee, $provenance_type,$etatnonvalide, "datefacture", $moyenattente),0,',',' ')." &euro;</td>
        <td colspan=\"4\">&nbsp;</td>
    </tr>
	<tr>
        <td colspan=\"9\">&nbsp;</td>
	</tr>
	<tr>
		<td rowspan=\"3\">N-1</td>
        <td>CA HT enregistré validé année N-1 au jour</td>
        <td>".number_format(auto_ca("annee", $jour, $mois, $annee_1, $provenance_type,$etat),0,',',' ')." &euro;</td>
        <td>&nbsp;</td>
        <td>".number_format(auto_ca("m-1", $jour, $mois, $annee_1, $provenance_type,$etat),0,',',' ')." &euro;</td>
        <td>&nbsp;</td>
        <td>".number_format(auto_ca("avanthier", $jour, $mois, $annee_1, $provenance_type,$etat),0,',',' ')." &euro;</td>
        <td>".number_format(auto_ca("hier", $jour, $mois, $annee_1, $provenance_type,$etat),0,',',' ')." &euro;</td>
		<td>".number_format(auto_ca("jour", $jour, $mois, $annee_1, $provenance_type,$etat),0,',',' ')." &euro;</td>
    </tr>
	<tr>
        <td>Av/Ret CA N-1 &euro; (validé)</td>
        ".tdvalue(auto_ca("annee", $jour, $mois, $annee, $provenance_type,$etat)-auto_ca("annee", $jour, $mois, $annee_1, $provenance_type,$etat),"€",true)."
        <td>&nbsp;</td>
        ".tdvalue(auto_ca("mois", $jour, $mois, $annee, $provenance_type,$etat)-auto_ca("m-1", $jour, $mois, $annee_1, $provenance_type,$etat),"€",true)."
        <td>&nbsp;</td>
        ".tdvalue(auto_ca("avanthier", $jour, $mois, $annee, $provenance_type,$etat)-auto_ca("avanthier", $jour, $mois, $annee_1, $provenance_type,$etat),"€",true)."
        ".tdvalue(auto_ca("hier", $jour, $mois, $annee, $provenance_type,$etat)-auto_ca("hier", $jour, $mois, $annee_1, $provenance_type,$etat),"€",true)."
		".tdvalue(auto_ca("jour", $jour, $mois, $annee, $provenance_type,$etat)-auto_ca("jour", $jour, $mois, $annee_1, $provenance_type,$etat),"€",true)."
    </tr>
	<tr>
        <td>Av/Ret CA N-1 % (validé)</td>";
	if(auto_ca("annee", $jour, $mois, $annee_1, $provenance_type,$etat)>0){
		echo tdvalue(
			(auto_ca("annee", $jour, $mois, $annee, $provenance_type,$etat)-auto_ca("annee", $jour, $mois, $annee_1, $provenance_type,$etat))*100/auto_ca("annee", $jour, $mois, $annee_1, $provenance_type,$etat),"%",true);
	} else {
		echo "<td>--</td>";
	}
	echo "<td>&nbsp;</td>";
	
	if(auto_ca("m-1", $jour, $mois, $annee_1, $provenance_type,$etat)>0){
		echo tdvalue(
			(auto_ca("mois", $jour, $mois, $annee, $provenance_type,$etat)-auto_ca("m-1", $jour, $mois, $annee_1, $provenance_type,$etat))*100/auto_ca("m-1", $jour, $mois, $annee_1, $provenance_type,$etat),"%",true);
	} else {
		echo "<td>--</td>";
	}
	echo "<td>&nbsp;</td>";
	
	if(auto_ca("avanthier", $jour, $mois, $annee_1, $provenance_type,$etat)>0){
		echo tdvalue(
			(auto_ca("avanthier", $jour, $mois, $annee, $provenance_type,$etat)-auto_ca("avanthier", $jour, $mois, $annee_1, $provenance_type,$etat))*100/auto_ca("avanthier", $jour, $mois, $annee_1, $provenance_type,$etat),"%",true);
	} else {
		echo "<td>--</td>";
	}
	if(auto_ca("hier", $jour, $mois, $annee_1, $provenance_type,$etat)>0){
		echo tdvalue(
			(auto_ca("hier", $jour, $mois, $annee, $provenance_type,$etat)-auto_ca("hier", $jour, $mois, $annee_1, $provenance_type,$etat))*100/auto_ca("hier", $jour, $mois, $annee_1, $provenance_type,$etat),"%",true);
	} else {
		echo "<td>--</td>";
	}
	if(auto_ca("jour", $jour, $mois, $annee_1, $provenance_type,$etat)>0){
		echo tdvalue(
			(auto_ca("jour", $jour, $mois, $annee, $provenance_type,$etat)-auto_ca("jour", $jour, $mois, $annee_1, $provenance_type,$etat))*100/auto_ca("jour", $jour, $mois, $annee_1, $provenance_type,$etat),"%",true);
	} else {
		echo "<td>--</td>";
	}
	echo "
    </tr>
</table>
<br>
<div class=\"clear\"></div>
<br>
<table class=\"ica_tab\" id=\"ica_tab\">
    <thead>
        <tr>
            <td class=\"ica_thead\" colspan=\"9\">CA Facturé</td>
        </tr>
    </thead>
	<tr>
        <td colspan=\"2\"></td>
        <td>Année en cours</td>
        <td>&nbsp;</td>
        <td>Mois en cours</td>
        <td>&nbsp;</td>
        <td>Avant hier</td>
        <td>Hier</td>
		<td>Ce jour</td>
    </tr>
	<tr>
		<td>CA</td>
        <td>CA HT facturé au jour</td>
        <td><b>".number_format(auto_ca("annee", $jour, $mois, $annee, $provenance_type,$etatfacture, "dateexpedition"),0,',',' ')." &euro;</b></td>
        <td>&nbsp;</td>
        <td><b>".number_format(auto_ca("mois", $jour, $mois, $annee, $provenance_type,$etatfacture, "dateexpedition"),0,',',' ')." &euro;</b></td>
        <td>&nbsp;</td>
        <td><b>".number_format(auto_ca("avanthier", $jour, $mois, $annee, $provenance_type,$etatfacture, "dateexpedition"),0,',',' ')." &euro;</b></td>
        <td><b>".number_format(auto_ca("hier", $jour, $mois, $annee, $provenance_type,$etatfacture, "dateexpedition"),0,',',' ')." &euro;</b></td>
		<td><b>".number_format(auto_ca("jour", $jour, $mois, $annee, $provenance_type,$etatfacture, "dateexpedition"),0,',',' ')." &euro;</b></td>
    </tr>
	<tr>
        <td colspan=\"9\">&nbsp;</td>
	</tr>
	<tr>
		<td rowspan=\"6\">Objectif</td>
        <td>Objectif HT au jour</td>
        <td>".number_format($objectif_facture_total_annee,0,',',' ')." &euro;</td>
        <td>&nbsp;</td>
        <td>".number_format(objectif_prorata($objectif_facture_total[$annee][$mois], date("n"), date("j")),0,',',' ')." &euro;</td>
        <td>&nbsp;</td>
        <td>".number_format(objectif_prorata($objectif_facture_total[$annee][$mois], date("n"), 1),0,',',' ')." &euro;</td>
        <td>".number_format(objectif_prorata($objectif_facture_total[$annee][$mois], date("n"), 1),0,',',' ')." &euro;</td>
		<td>".number_format(objectif_prorata($objectif_facture_total[$annee][$mois], date("n"), 1),0,',',' ')." &euro;</td>
    </tr>
	<tr>
        <td>Av/Ret Objectif &euro;</td>
        ".tdvalue(auto_ca("annee", $jour, $mois, $annee, $provenance_type,$etatfacture, "dateexpedition")-$objectif_facture_total_annee,"€",true)."
        <td>&nbsp;</td>
        ".tdvalue(auto_ca("mois", $jour, $mois, $annee, $provenance_type,$etatfacture, "dateexpedition")-objectif_prorata($objectif_facture_total[$annee][$mois], date("n"), date("j")),"€",true)."
        <td>&nbsp;</td>
        ".tdvalue(auto_ca("avanthier", $jour, $mois, $annee, $provenance_type,$etatfacture, "dateexpedition")-objectif_prorata($objectif_facture_total[$annee][$mois], date("n"), 1),"€",true)."
        ".tdvalue(auto_ca("hier", $jour, $mois, $annee, $provenance_type,$etatfacture, "dateexpedition")-objectif_prorata($objectif_facture_total[$annee][$mois], date("n"), 1),"€",true)."
		".tdvalue(auto_ca("jour", $jour, $mois, $annee, $provenance_type,$etatfacture, "dateexpedition")-objectif_prorata($objectif_facture_total[$annee][$mois], date("n"), 1),"€",true)."
    </tr>
	<tr>
        <td>Av/Ret Objectif %</td>
        ".tdvalue(
			(auto_ca(
				"annee", $jour, $mois, $annee, $provenance_type,$etatfacture, "dateexpedition")
				-$objectif_facture_total_annee
			)
			*100/$objectif_facture_total_annee,"%",true)."
        <td>&nbsp;</td>
        ".tdvalue(
			(auto_ca
				("mois", $jour, $mois, $annee, $provenance_type,$etatfacture, "dateexpedition")
				-objectif_prorata(
					$objectif_facture_total[$annee][$mois], date("n"), date("j")
				)
			)*100/objectif_prorata(
					$objectif_facture_total[$annee][$mois], date("n"), date("j")
				)
			
			,"%",true)."
        <td>&nbsp;</td>
        ".tdvalue(
			(auto_ca
				("avanthier", $jour, $mois, $annee, $provenance_type,$etatfacture, "dateexpedition")
				-objectif_prorata(
					$objectif_facture_total[$annee][$mois], date("n"), 1
				)
			)*100/objectif_prorata(
					$objectif_facture_total[$annee][$mois], date("n"), 1
				)
			
			,"%",true)."
        ".tdvalue(
			(auto_ca
				("hier", $jour, $mois, $annee, $provenance_type,$etatfacture, "dateexpedition")
				-objectif_prorata(
					$objectif_facture_total[$annee][$mois], date("n"), 1
				)
			)*100/objectif_prorata(
					$objectif_facture_total[$annee][$mois], date("n"), 1
				)
			,"%",true)."
		".tdvalue(
			(auto_ca
				("jour", $jour, $mois, $annee, $provenance_type,$etatfacture, "dateexpedition")
				-objectif_prorata(
					$objectif_facture_total[$annee][$mois], date("n"), 1
				)
			)*100/objectif_prorata(
					$objectif_facture_total[$annee][$mois], date("n"), 1
				)
			,"%",true)."
    </tr>
	<tr>
        <td colspan=\"8\">&nbsp;</td>
	</tr>
	<tr>
        <td>Objectif total de la période en cours</td>
        <td>".number_format($objectif_facture_total_total,0,',',' ')." &euro;</td>
        <td>&nbsp;</td>
        <td>".number_format($objectif_facture_total[$annee][$mois],0,',',' ')." &euro;</td>
        <td colspan=\"4\">&nbsp;</td>
    </tr>
	<tr>
        <td>Reste à faire CA / objectif</td>
        <td>".number_format($objectif_facture_total_total-auto_ca("annee", $jour, $mois, $annee, $provenance_type,$etatfacture, "dateexpedition"),0,',',' ')." &euro;</td>
        <td>&nbsp;</td>
        <td>".number_format($objectif_facture_total[$annee][$mois]-auto_ca("mois", $jour, $mois, $annee, $provenance_type,$etatfacture, "dateexpedition"),0,',',' ')." &euro;</td>
        <td colspan=\"4\">&nbsp;</td>
    </tr>
	<tr>
        <td colspan=\"9\">&nbsp;</td>
	</tr>
	<tr>
		<td rowspan=\"3\">N-1</td>
        <td>CA HT année N-1 au jour</td>
        <td>".number_format(auto_ca("annee", $jour, $mois, $annee_1, $provenance_type,$etatfacture, "dateexpedition"),0,',',' ')." &euro;</td>
        <td>&nbsp;</td>
        <td>".number_format(auto_ca("m-1", $jour, $mois, $annee_1, $provenance_type,$etatfacture, "dateexpedition"),0,',',' ')." &euro;</td>
        <td>&nbsp;</td>
        <td>".number_format(auto_ca("avanthier", $jour, $mois, $annee_1, $provenance_type,$etatfacture, "dateexpedition"),0,',',' ')." &euro;</td>
        <td>".number_format(auto_ca("hier", $jour, $mois, $annee_1, $provenance_type,$etatfacture, "dateexpedition"),0,',',' ')." &euro;</td>
		<td>".number_format(auto_ca("jour", $jour, $mois, $annee_1, $provenance_type,$etatfacture, "dateexpedition"),0,',',' ')." &euro;</td>
    </tr>
	<tr>
        <td>Av/Ret CA N-1 &euro;</td>
        ".tdvalue(auto_ca("annee", $jour, $mois, $annee, $provenance_type,$etatfacture, "dateexpedition")-auto_ca("annee", $jour, $mois, $annee_1, $provenance_type,$etatfacture, "dateexpedition"),"€",true)."
        <td>&nbsp;</td>
        ".tdvalue(auto_ca("mois", $jour, $mois, $annee, $provenance_type,$etatfacture, "dateexpedition")-auto_ca("m-1", $jour, $mois, $annee_1, $provenance_type,$etatfacture, "dateexpedition"),"€",true)."
        <td>&nbsp;</td>
        ".tdvalue(auto_ca("avanthier", $jour, $mois, $annee, $provenance_type,$etatfacture, "dateexpedition")-auto_ca("avanthier", $jour, $mois, $annee_1, $provenance_type,$etatfacture, "dateexpedition"),"€",true)."
        ".tdvalue(auto_ca("hier", $jour, $mois, $annee, $provenance_type,$etatfacture, "dateexpedition")-auto_ca("hier", $jour, $mois, $annee_1, $provenance_type,$etatfacture, "dateexpedition"),"€",true)."
		".tdvalue(auto_ca("jour", $jour, $mois, $annee, $provenance_type,$etatfacture, "dateexpedition")-auto_ca("jour", $jour, $mois, $annee_1, $provenance_type,$etatfacture, "dateexpedition"),"€",true)."
    </tr>
	<tr>
        <td>Av/Ret CA N-1 %</td>";
	if(auto_ca("annee", $jour, $mois, $annee_1, $provenance_type,$etatfacture, "dateexpedition")>0){
		echo tdvalue(
			(auto_ca("annee", $jour, $mois, $annee, $provenance_type,$etatfacture, "dateexpedition")-auto_ca("annee", $jour, $mois, $annee_1, $provenance_type,$etatfacture, "dateexpedition"))*100/auto_ca("annee", $jour, $mois, $annee_1, $provenance_type,$etatfacture, "dateexpedition"),"%",true);
	} else {
		echo "<td>--</td>";
	}
	echo "<td>&nbsp;</td>";
	
	if(auto_ca("m-1", $jour, $mois, $annee_1, $provenance_type,$etatfacture, "dateexpedition")>0){
		echo tdvalue(
			(auto_ca("mois", $jour, $mois, $annee, $provenance_type,$etatfacture, "dateexpedition")-auto_ca("m-1", $jour, $mois, $annee_1, $provenance_type,$etatfacture, "dateexpedition"))*100/auto_ca("m-1", $jour, $mois, $annee_1, $provenance_type,$etatfacture, "dateexpedition"),"%",true);
	} else {
		echo "<td>--</td>";
	}
	echo "<td>&nbsp;</td>";
	
	if(auto_ca("avanthier", $jour, $mois, $annee_1, $provenance_type,$etatfacture, "dateexpedition")>0){
		echo tdvalue(
			(auto_ca("avanthier", $jour, $mois, $annee, $provenance_type,$etatfacture, "dateexpedition")-auto_ca("avanthier", $jour, $mois, $annee_1, $provenance_type,$etatfacture, "dateexpedition"))*100/auto_ca("avanthier", $jour, $mois, $annee_1, $provenance_type,$etatfacture, "dateexpedition"),"%",true);
	} else {
		echo "<td>--</td>";
	}
	if(auto_ca("hier", $jour, $mois, $annee_1, $provenance_type,$etatfacture, "dateexpedition")>0){
		echo tdvalue(
			(auto_ca("hier", $jour, $mois, $annee, $provenance_type,$etatfacture, "dateexpedition")-auto_ca("hier", $jour, $mois, $annee_1, $provenance_type,$etatfacture, "dateexpedition"))*100/auto_ca("hier", $jour, $mois, $annee_1, $provenance_type,$etatfacture, "dateexpedition"),"%",true);
	} else {
		echo "<td>--</td>";
	}
	if(auto_ca("jour", $jour, $mois, $annee_1, $provenance_type,$etatfacture, "dateexpedition")>0){
		echo tdvalue(
			(auto_ca("jour", $jour, $mois, $annee, $provenance_type,$etatfacture, "dateexpedition")-auto_ca("jour", $jour, $mois, $annee_1, $provenance_type,$etatfacture, "dateexpedition"))*100/auto_ca("jour", $jour, $mois, $annee_1, $provenance_type,$etatfacture, "dateexpedition"),"%",true);
	} else {
		echo "<td>--</td>";
	}
	echo "
    </tr>
</table>
<br>
<div class=\"clear\"></div>
<br>
<table class=\"ica_tab\" id=\"ica_tab\">
    <thead>
        <tr>
            <td class=\"ica_thead\" colspan=\"9\">Dont CA Facturé marketplaces</td>
        </tr>
    </thead>
	<tr>
        <td colspan=\"2\"></td>
        <td>Année en cours</td>
        <td>&nbsp;</td>
        <td>Mois en cours</td>
        <td>&nbsp;</td>
        <td>Avant hier</td>
        <td>Hier</td>
		<td>Ce jour</td>
    </tr>
	<tr>
		<td>CA</td>
        <td>CA HT facturé au jour</td>
        <td><b>".number_format(auto_ca("annee", $jour, $mois, $annee, $provenance_mp,$etatfacture, "dateexpedition"),0,',',' ')." &euro;</b></td>
        <td>&nbsp;</td>
        <td><b>".number_format(auto_ca("mois", $jour, $mois, $annee, $provenance_mp,$etatfacture, "dateexpedition"),0,',',' ')." &euro;</b></td>
        <td>&nbsp;</td>
        <td><b>".number_format(auto_ca("avanthier", $jour, $mois, $annee, $provenance_mp,$etatfacture, "dateexpedition"),0,',',' ')." &euro;</b></td>
        <td><b>".number_format(auto_ca("hier", $jour, $mois, $annee, $provenance_mp,$etatfacture, "dateexpedition"),0,',',' ')." &euro;</b></td>
		<td><b>".number_format(auto_ca("jour", $jour, $mois, $annee, $provenance_mp,$etatfacture, "dateexpedition"),0,',',' ')." &euro;</b></td>
    </tr>
	<tr>
        <td colspan=\"9\">&nbsp;</td>
	</tr>
	<tr>
		<td rowspan=\"6\">Objectif</td>
        <td>Objectif HT au jour</td>
        <td>".number_format($objectif_mp_total_annee,0,',',' ')." &euro;</td>
        <td>&nbsp;</td>
        <td>".number_format(objectif_prorata($objectif_mp_total[$annee][$mois], date("n"), date("j")),0,',',' ')." &euro;</td>
        <td>&nbsp;</td>
        <td>".number_format(objectif_prorata($objectif_mp_total[$annee][$mois], date("n"), 1),0,',',' ')." &euro;</td>
        <td>".number_format(objectif_prorata($objectif_mp_total[$annee][$mois], date("n"), 1),0,',',' ')." &euro;</td>
		<td>".number_format(objectif_prorata($objectif_mp_total[$annee][$mois], date("n"), 1),0,',',' ')." &euro;</td>
    </tr>
	<tr>
        <td>Av/Ret Objectif &euro;</td>
        ".tdvalue(auto_ca("annee", $jour, $mois, $annee, $provenance_mp,$etatfacture, "dateexpedition")-$objectif_mp_total_annee,"€",true)."
        <td>&nbsp;</td>
        ".tdvalue(auto_ca("mois", $jour, $mois, $annee, $provenance_mp,$etatfacture, "dateexpedition")-objectif_prorata($objectif_mp_total[$annee][$mois], date("n"), date("j")),"€",true)."
        <td>&nbsp;</td>
        ".tdvalue(auto_ca("avanthier", $jour, $mois, $annee, $provenance_mp,$etatfacture, "dateexpedition")-objectif_prorata($objectif_mp_total[$annee][$mois], date("n"), 1),"€",true)."
        ".tdvalue(auto_ca("hier", $jour, $mois, $annee, $provenance_mp,$etatfacture, "dateexpedition")-objectif_prorata($objectif_mp_total[$annee][$mois], date("n"), 1),"€",true)."
		".tdvalue(auto_ca("jour", $jour, $mois, $annee, $provenance_mp,$etatfacture, "dateexpedition")-objectif_prorata($objectif_mp_total[$annee][$mois], date("n"), 1),"€",true)."
    </tr>
	<tr>
        <td>Av/Ret Objectif %</td>
        ".tdvalue(
			(auto_ca(
				"annee", $jour, $mois, $annee, $provenance_mp,$etatfacture, "dateexpedition")
				-$objectif_mp_total_annee
			)
			*100/$objectif_mp_total_annee,"%",true)."
        <td>&nbsp;</td>
        ".tdvalue(
			(auto_ca
				("mois", $jour, $mois, $annee, $provenance_mp,$etatfacture, "dateexpedition")
				-objectif_prorata(
					$objectif_mp_total[$annee][$mois], date("n"), date("j")
				)
			)*100/objectif_prorata(
					$objectif_mp_total[$annee][$mois], date("n"), date("j")
				)
			
			,"%",true)."
        <td>&nbsp;</td>
        ".tdvalue(
			(auto_ca
				("avanthier", $jour, $mois, $annee, $provenance_mp,$etatfacture, "dateexpedition")
				-objectif_prorata(
					$objectif_mp_total[$annee][$mois], date("n"), 1
				)
			)*100/objectif_prorata(
					$objectif_mp_total[$annee][$mois], date("n"), 1
				)
			
			,"%",true)."
        ".tdvalue(
			(auto_ca
				("hier", $jour, $mois, $annee, $provenance_mp,$etatfacture, "dateexpedition")
				-objectif_prorata(
					$objectif_mp_total[$annee][$mois], date("n"), 1
				)
			)*100/objectif_prorata(
					$objectif_mp_total[$annee][$mois], date("n"), 1
				)
			,"%",true)."
		".tdvalue(
			(auto_ca
				("jour", $jour, $mois, $annee, $provenance_mp,$etatfacture, "dateexpedition")
				-objectif_prorata(
					$objectif_mp_total[$annee][$mois], date("n"), 1
				)
			)*100/objectif_prorata(
					$objectif_mp_total[$annee][$mois], date("n"), 1
				)
			,"%",true)."
    </tr>
	<tr>
        <td colspan=\"8\">&nbsp;</td>
	</tr>
	<tr>
        <td>Objectif total de la période en cours</td>
        <td>".number_format($objectif_mp_total_total,0,',',' ')." &euro;</td>
        <td>&nbsp;</td>
        <td>".number_format($objectif_mp_total[$annee][$mois],0,',',' ')." &euro;</td>
        <td colspan=\"4\">&nbsp;</td>
    </tr>
	<tr>
        <td>Reste à faire CA / objectif</td>
        <td>".number_format($objectif_mp_total_total-auto_ca("annee", $jour, $mois, $annee, $provenance_mp,$etatfacture, "dateexpedition"),0,',',' ')." &euro;</td>
        <td>&nbsp;</td>
        <td>".number_format($objectif_mp_total[$annee][$mois]-auto_ca("mois", $jour, $mois, $annee, $provenance_mp,$etatfacture, "dateexpedition"),0,',',' ')." &euro;</td>
        <td colspan=\"4\">&nbsp;</td>
    </tr>
	<tr>
        <td colspan=\"9\">&nbsp;</td>
	</tr>
	<tr>
		<td rowspan=\"3\">N-1</td>
        <td>CA HT année N-1 au jour</td>
        <td>".number_format(auto_ca("annee", $jour, $mois, $annee_1, $provenance_mp,$etatfacture, "dateexpedition"),0,',',' ')." &euro;</td>
        <td>&nbsp;</td>
        <td>".number_format(auto_ca("m-1", $jour, $mois, $annee_1, $provenance_mp,$etatfacture, "dateexpedition"),0,',',' ')." &euro;</td>
        <td>&nbsp;</td>
        <td>".number_format(auto_ca("avanthier", $jour, $mois, $annee_1, $provenance_mp,$etatfacture, "dateexpedition"),0,',',' ')." &euro;</td>
        <td>".number_format(auto_ca("hier", $jour, $mois, $annee_1, $provenance_mp,$etatfacture, "dateexpedition"),0,',',' ')." &euro;</td>
		<td>".number_format(auto_ca("jour", $jour, $mois, $annee_1, $provenance_mp,$etatfacture, "dateexpedition"),0,',',' ')." &euro;</td>
    </tr>
	<tr>
        <td>Av/Ret CA N-1 &euro;</td>
        ".tdvalue(auto_ca("annee", $jour, $mois, $annee, $provenance_mp,$etatfacture, "dateexpedition")-auto_ca("annee", $jour, $mois, $annee_1, $provenance_mp,$etatfacture, "dateexpedition"),"€",true)."
        <td>&nbsp;</td>
        ".tdvalue(auto_ca("mois", $jour, $mois, $annee, $provenance_mp,$etatfacture, "dateexpedition")-auto_ca("m-1", $jour, $mois, $annee_1, $provenance_mp,$etatfacture, "dateexpedition"),"€",true)."
        <td>&nbsp;</td>
        ".tdvalue(auto_ca("avanthier", $jour, $mois, $annee, $provenance_mp,$etatfacture, "dateexpedition")-auto_ca("avanthier", $jour, $mois, $annee_1, $provenance_mp,$etatfacture, "dateexpedition"),"€",true)."
        ".tdvalue(auto_ca("hier", $jour, $mois, $annee, $provenance_mp,$etatfacture, "dateexpedition")-auto_ca("hier", $jour, $mois, $annee_1, $provenance_mp,$etatfacture, "dateexpedition"),"€",true)."
		".tdvalue(auto_ca("jour", $jour, $mois, $annee, $provenance_mp,$etatfacture, "dateexpedition")-auto_ca("jour", $jour, $mois, $annee_1, $provenance_mp,$etatfacture, "dateexpedition"),"€",true)."
    </tr>
	<tr>
        <td>Av/Ret CA N-1 %</td>";
	if(auto_ca("annee", $jour, $mois, $annee_1, $provenance_mp,$etatfacture, "dateexpedition")>0){
		echo tdvalue(
			(auto_ca("annee", $jour, $mois, $annee, $provenance_mp,$etatfacture, "dateexpedition")-auto_ca("annee", $jour, $mois, $annee_1, $provenance_mp,$etatfacture, "dateexpedition"))*100/auto_ca("annee", $jour, $mois, $annee_1, $provenance_mp,$etatfacture, "dateexpedition"),"%",true);
	} else {
		echo "<td>--</td>";
	}
	echo "<td>&nbsp;</td>";
	
	if(auto_ca("m-1", $jour, $mois, $annee_1, $provenance_mp,$etatfacture, "dateexpedition")>0){
		echo tdvalue(
			(auto_ca("mois", $jour, $mois, $annee, $provenance_mp,$etatfacture, "dateexpedition")-auto_ca("m-1", $jour, $mois, $annee_1, $provenance_mp,$etatfacture, "dateexpedition"))*100/auto_ca("m-1", $jour, $mois, $annee_1, $provenance_mp,$etatfacture, "dateexpedition"),"%",true);
	} else {
		echo "<td>--</td>";
	}
	echo "<td>&nbsp;</td>";
	
	if(auto_ca("avanthier", $jour, $mois, $annee_1, $provenance_mp,$etatfacture, "dateexpedition")>0){
		echo tdvalue(
			(auto_ca("avanthier", $jour, $mois, $annee, $provenance_mp,$etatfacture, "dateexpedition")-auto_ca("avanthier", $jour, $mois, $annee_1, $provenance_mp,$etatfacture, "dateexpedition"))*100/auto_ca("avanthier", $jour, $mois, $annee_1, $provenance_mp,$etatfacture, "dateexpedition"),"%",true);
	} else {
		echo "<td>--</td>";
	}
	if(auto_ca("hier", $jour, $mois, $annee_1, $provenance_mp,$etatfacture, "dateexpedition")>0){
		echo tdvalue(
			(auto_ca("hier", $jour, $mois, $annee, $provenance_mp,$etatfacture, "dateexpedition")-auto_ca("hier", $jour, $mois, $annee_1, $provenance_mp,$etatfacture, "dateexpedition"))*100/auto_ca("hier", $jour, $mois, $annee_1, $provenance_mp,$etatfacture, "dateexpedition"),"%",true);
	} else {
		echo "<td>--</td>";
	}
	if(auto_ca("jour", $jour, $mois, $annee_1, $provenance_mp,$etatfacture, "dateexpedition")>0){
		echo tdvalue(
			(auto_ca("jour", $jour, $mois, $annee, $provenance_mp,$etatfacture, "dateexpedition")-auto_ca("jour", $jour, $mois, $annee_1, $provenance_mp,$etatfacture, "dateexpedition"))*100/auto_ca("jour", $jour, $mois, $annee_1, $provenance_mp,$etatfacture, "dateexpedition"),"%",true);
	} else {
		echo "<td>--</td>";
	}
	echo "
    </tr>
</table>
";





/* FUNCTION */
function tdvalue($value, $sym="", $sign=null, $climit=0){
    return "<td style=\"color:".($value>$climit?"green":"red").";\">".($sign&&$value>0?"+":"").number_format($value,0,',',' ')." ".$sym."</td>";
}

/* * * * * * * * *
	ENREGISTRE
* * * * * * * * */

function objectif_prorata($objectif, $mois, $jour){
    $retour=0;
	$t=date("t", mktime(0,0,0,$mois));
	$retour=$objectif/$t*$jour;
    return $retour;
}


function auto_ca($return, $j, $m, $a, $prov, $etat="1,5,7,8", $champdate="datefacture", $moyenpaiement=""){
    global $DB_site2;
  
    $ca = 0;
    if($m<10)
		$m = "0".$m;
	
	if($champdate=="dateexpedition"){
		$etat="5,8";
	}
	
    switch ($return) {
        case 'annee':
			if($etat==0){
				$adeb=$a-1;
			} else {
				$adeb=$a;
			}
			$and =" AND cast(f.".$champdate." as date) BETWEEN '".$adeb."-01-01' AND '".$a."-".$m."-".$j."' ";
            break;
        case 'mois':
            $and =" AND cast(f.".$champdate." as date) BETWEEN '".$a."-".$m."-01' AND '".$a."-".$m."-31' ";
            break;
        case 'jour':
            $and =" AND cast(f.".$champdate." as date) = '".$a."-".$m."-".$j."' ";
            break;
        case 'hier':
            // $and =" AND cast(f.".$champdate." as date) = '".date('Y-m-d',strtotime("-1 day",time()))."' ";
			$and =" AND cast(f.".$champdate." as date) = '".date('Y-m-d', mktime(0, 0, 0, $m, $j-1, $a) )."' ";
			break;
		case 'avanthier':
            // $and =" AND cast(f.".$champdate." as date) = '".date('Y-m-d',strtotime("-2 day",time()))."' ";
			$and =" AND cast(f.".$champdate." as date) = '".date('Y-m-d', mktime(0, 0, 0, $m, $j-2, $a) )."' ";
			break;
        case 'a-1':
            $and =" AND cast(f.".$champdate." as date) BETWEEN '".$a."-01-01' AND '".$a."-".$m."-".$j."' ";
            break;
		case 'm-1':
            $and =" AND cast(f.".$champdate." as date) BETWEEN '".$a."-".$m."-01' AND '".$a."-".$m."-".$j."' ";
            break;
    }
	if($moyenpaiement!=""){
		$and.=" AND moyenid IN (".$moyenpaiement.") ";
	}

    $sql = "SELECT SUM((lf.prix * lf.qte) / (1 + (lf.tva / 100))) as 'prixHT', f.prixport / (1 + f.tvaport / 100) as 'portHT',
		CASE WHEN (SELECT code_caviste_id FROM code_caviste WHERE code = f.cadeauid) IS NULL THEN (SUM((lf.prix * lf.qte) / (1 + (lf.tva / 100))) + (f.prixport / (1 + f.tvaport / 100))) * (f.montantcadeau / (SUM(lf.prix * lf.qte) + f.prixport)) ELSE 0 END as remiseHT 
            FROM facture f
			INNER JOIN lignefacture lf ON (lf.factureid = f.facturid)
            WHERE f.valide IN (".$etat.") AND f.provenance IN (".$prov.")
            ".$and." GROUP BY f.facturid";
    $req = $DB_site2->query($sql);
    while($row = $DB_site2->fetch_array($req)){
        $ca += $row['prixHT'] + $row['portHT'] - $row['remiseHT'];
    }
    return $ca;
}





echo "<br>";

include "./footer.php";
?>
