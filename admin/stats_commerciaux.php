<?php

ini_set("display_errors","1");
error_reporting(E_ALL);

require "admin_global.php";
include "./header.php";
include "./menu.php";


/*
$provenance_type="'','mp','mobile','sav','avoir'";
$provenance_mp="'mp'";
*/

echo "<script language=\"javascript\">
 jQuery(function($) {
		$('.showpopup').mouseenter(function(){
    $(this).children('div').css('visibility', 'visible');
    console.log($(this).children('div'));
     $(this).children('div').css('background-color', '#AAFFAA');
    $(this).css('background-color', '#AAFFAA');
		}).mouseleave(function(){
    $(this).children('div').css('visibility', 'hidden');
     $(this).children('div').css('background-color', 'white');
    $(this).css('background-color', 'white');
		});
		});
</script>";

setlocale(LC_TIME, 'fr', 'fr_FR', 'fr_FR.ISO8859-1');

$jour = date("d");
$mois = date("n");

if($_GET['annee'] && $_GET['annee'] != date("Y") && preg_match('/[0-9]/', $_GET['annee']) && strlen($_GET['annee']) == 4) {
	$annee = $_GET['annee'];
	$annee_libelle = "année " . $_GET['annee'] . " (en cumulé)";
	$mois_libelle = strftime("%B");
}
else {
$annee_libelle = "année en cours (en cumulé)";
$annee = date("Y");
$mois_libelle = "mois en cours";
}

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


$cde_mois_total=auto_cde("mois", $jour, $mois, $annee, "tous");
$cde_annee_total=auto_cde("annee", $jour, $mois, $annee, "tous");
$cde_annee_1_total=auto_cde("annee", $jour, $mois, $annee_1, "tous");

$cde_mois_both=auto_cde("mois", $jour, $mois, $annee, "tous");
$cde_annee_both=auto_cde("annee", $jour, $mois, $annee, "tous");
$cde_annee_1_both=auto_cde("annee", $jour, $mois, $annee_1, "tous");

$commerciaux=array("direct");
$commercials=$DB_site2->query("SELECT username FROM parametresgeneraux WHERE commissions ='1' ORDER BY username ASC ");
while($commercial=$DB_site2->fetch_array($commercials)){
	$commerciaux[]=$commercial[username];
}


echo "<h1>Statistiques commerciaux (ventes enregistrées validées)</h1>";
echo "

Choisir une autre année : <form method='get'><select name='annee'>";
for($i = 2015; $i < 2026; $i++) {
	if($_GET['annee'] == $i) {
		echo "<option value='".$i."'' selected>".$i."</option>";
	}
	else if(date("Y") == $i && !$_GET['annee']) {
		echo "<option value='".$i."'' selected>".$i."</option>";
	}
	else {
		echo "<option value='".$i."''>".$i."</option>";
	}
}
echo "<input type='submit' value='valider'></form>

<table class=\"ica_tab\" id=\"ica_tab\">
	<tr>
        <td></td>
        <td colspan=\"4\">".$mois_libelle."</td>
        <td colspan=\"4\">".$annee_libelle."</td>
        <td colspan=\"4\">comparatif en cumul / année N-1</td>
    </tr>
	<tr>
		<td></td>
		<td nowrap>qté cde</td>
        <td nowrap>% qté</td>
        <td nowrap>CA HT</td>
        <td nowrap>% CA HT</td>
		<td nowrap>Qté cde</td>
        <td nowrap>% qté</td>
        <td nowrap>CA HT</td>
        <td nowrap>% CA HT</td>
		<td nowrap>Qté cde</td>
        <td nowrap>% qté /N-1</td>
        <td nowrap>CA HT</td>
        <td nowrap>% CA HT / N-1</td>
    </tr>";
if($cde_mois_total[ca]>0){
	echo "<tr>
			<td><b>Total cdes site</b></td>
			<td>".$cde_mois_both[nb]."</td>
			<td>".number_format($cde_mois_both[nb]*100/$cde_mois_total[nb], 1, ',', ' ') ." %</td>
			<td nowrap>".number_format($cde_mois_both[ca], 0, ',', ' ')." &euro;</td>
			<td>".number_format($cde_mois_both[ca]*100/$cde_mois_total[ca], 1, ',', ' ') ." %</td>
			<td>".$cde_annee_both[nb]."</td>
			<td>".number_format($cde_annee_both[nb]*100/$cde_annee_total[nb], 1, ',', ' ') ." %</td>
			<td nowrap>".number_format($cde_annee_both[ca], 0, ',', ' ')." &euro;</td>
			<td>".number_format($cde_annee_both[ca]*100/$cde_annee_total[ca], 1, ',', ' ') ." %</td>
			<td>".$cde_annee_1_both[nb]."</td>
			".tdvalue(($cde_annee_both[nb]*100/$cde_annee_1_both[nb])-100,"%",1)."
			<td nowrap>".number_format($cde_annee_1_both[ca], 0, ',', ' ')." &euro;</td>
			".tdvalue(($cde_annee_both[ca]*100/$cde_annee_1_both[ca])-100,"%",1)."
		</tr>";
} else {
	echo "<tr><td colspan=\"13\">Pas assez de commandes pour faire des statistiques</td></tr>";
}

foreach ($commerciaux as $key => $value){
	
	$cde_mois=auto_cde("mois", $jour, $mois, $annee, $value);
	$cde_annee=auto_cde("annee", $jour, $mois, $annee, $value);
	$cde_annee_1=auto_cde("annee", $jour, $mois, $annee_1, $value);
	
	if($cde_mois_total[ca]>0){
		echo "	<tr>
				<td><b>".UCfirst($value)."</b></td>
				<td>".$cde_mois[nb]."</td>
				<td>".number_format($cde_mois[nb]*100/$cde_mois_total[nb], 1, ',', ' ') ." %</td>
				<td class=\"showpopup\">".number_format($cde_mois[ca], 0, ',', ' ')." &euro;
				<div class=\"popup\"><table>".$cde_mois[idfac]."</table></div></td>
				<td>".number_format($cde_mois[ca]*100/$cde_mois_total[ca], 1, ',', ' ') ." %</td>
				<td>".$cde_annee[nb]."</td>
				<td>".number_format($cde_annee[nb]*100/$cde_annee_total[nb], 1, ',', ' ') ." %</td>
				<td>".number_format($cde_annee[ca], 0, ',', ' ')." &euro;</td>
				<td>".number_format($cde_annee[ca]*100/$cde_annee_total[ca], 1, ',', ' ') ." %</td>
				<td>".$cde_annee_1[nb]."</td>";
		if($cde_annee_1[ca]>0){
			echo tdvalue(($cde_annee[nb]*100/$cde_annee_1[nb])-100,"%",1)."
				<td>".number_format($cde_annee_1[ca], 0, ',', ' ')." &euro;</td>
				".tdvalue(($cde_annee[ca]*100/$cde_annee_1[ca])-100,"%",1);
		} else {
			echo "<td>--</td>";
			echo "<td>0 &euro;</td>";
			echo "<td>--</td>";
		}
		echo "	</tr>";
	} else {
		echo "<tr><td colspan=\"13\">Pas assez de commandes pour faire des statistiques pour ".UCfirst($value).".</td></tr>";
	}
}

$cde_mois=auto_cde("mois", $jour, $mois, $annee, "");
$cde_annee=auto_cde("annee", $jour, $mois, $annee, "");
$cde_annee_1=auto_cde("annee", $jour, $mois, $annee_1, "");
if($cde_mois_total[ca]>0){
	echo "	<tr>
			<td><b>non attribués</b></td>
			<td>".$cde_mois[nb]."</td>
			<td>".number_format($cde_mois[nb]*100/$cde_mois_total[nb], 1, ',', ' ') ." %</td>
			<td>".number_format($cde_mois[ca], 0, ',', ' ')." &euro;</td>
			<td>".number_format($cde_mois[ca]*100/$cde_mois_total[ca], 1, ',', ' ') ." %</td>
			<td>".$cde_annee[nb]."</td>
			<td>".number_format($cde_annee[nb]*100/$cde_annee_total[nb], 1, ',', ' ') ." %</td>
			<td>".number_format($cde_annee[ca], 0, ',', ' ')." &euro;</td>
			<td>".number_format($cde_annee[ca]*100/$cde_annee_total[ca], 1, ',', ' ') ." %</td>
			<td>".$cde_annee_1[nb]."</td>";
	if($cde_annee_1[ca]>0){
		echo tdvalue(($cde_annee[nb]*100/$cde_annee_1[nb])-100,"%",1)."
			<td>".number_format($cde_annee_1[ca], 0, ',', ' ')." &euro;</td>
			".tdvalue(($cde_annee[ca]*100/$cde_annee_1[ca])-100,"%",1);
	} else {
		echo "<td>--</td>";
		echo "<td>0 &euro;</td>";
		echo "<td>--</td>";
	}
	echo "	</tr>";
} else {
	echo "<tr><td colspan=\"13\">Pas assez de commandes pour faire des statistiques pour non attribué.</td></tr>";
}
echo "</table><br>";









echo "<h1>Statistiques commerciaux (commandes en attente de validation)</h1>";
echo "<table class=\"ica_tab\" id=\"ica_tab\">
	<tr>
        <td></td>
        <td colspan=\"4\">mois en cours</td>
        <td colspan=\"4\">année en cours (en cumulé)</td>
    </tr>
	<tr>
		<td></td>
		<td nowrap>qté cde</td>
        <td nowrap>% qté</td>
        <td nowrap>CA HT</td>
        <td nowrap>% CA HT</td>
		<td nowrap>Qté cde</td>
        <td nowrap>% qté</td>
        <td nowrap>CA HT</td>
        <td nowrap>% CA HT</td>
    </tr>";
$javascript_hover="";
foreach ($commerciaux as $key => $value){
	$cde_mois=auto_cde("mois", $jour, $mois, $annee, $value, "0");
	$cde_annee=auto_cde("annee", $jour, $mois, $annee, $value, "0");
	$cde_annee_1=auto_cde("annee", $jour, $mois, $annee_1, $value, "0");
	if($cde_mois_total[ca]>0){
		echo "	<tr>
				<td><b>".UCfirst($value)."</b></td>
				<td>".$cde_mois[nb]."</td>
				<td>".number_format($cde_mois[nb]*100/$cde_mois_total[nb], 1, ',', ' ') ." %</td>
				<td><a id=\"comm_non_valide_mois_".$value."\">".number_format($cde_mois[ca], 0, ',', ' ')." &euro;</a>
					<div id=\"div_non_valide_mois_".$value."\" style=\"display:none;\">".$cde_mois[cdes]."</div>
				</td>
				<td>".number_format($cde_mois[ca]*100/$cde_mois_total[ca], 1, ',', ' ') ." %</td>
				<td>".$cde_annee[nb]."</td>
				<td>".number_format($cde_annee[nb]*100/$cde_annee_total[nb], 1, ',', ' ') ." %</td>
				<td><a id=\"comm_non_valide_annee_".$value."\">".number_format($cde_annee[ca], 0, ',', ' ')." &euro;</a>
					<div id=\"div_non_valide_annee_".$value."\" style=\"display:none;\">".$cde_annee[cdes]."</div>
				</td>
				<td>".number_format($cde_annee[ca]*100/$cde_annee_total[ca], 1, ',', ' ') ." %</td>
			</tr>";
		$javascript_hover.="
			\$jQuery(\"#comm_non_valide_annee_".$value."\").click(function() {
			  \$jQuery(\"#div_non_valide_annee_".$value."\").toggle();
			});";
		$javascript_hover.="
			\$jQuery(\"#comm_non_valide_mois_".$value."\").click(function() {
			  \$jQuery(\"#div_non_valide_mois_".$value."\").toggle();
			});";
	} else {
		echo "<tr><td colspan=\"9\">Pas assez de commandes pour faire des statistiques pour ".UCfirst($value).".</td></tr>";
	}
}
$cde_mois=auto_cde("mois", $jour, $mois, $annee, "", "0");
$cde_annee=auto_cde("annee", $jour, $mois, $annee, "", "0");
$cde_annee_1=auto_cde("annee", $jour, $mois, $annee_1, "", "0");
if($cde_mois_total[ca]>0){
	echo "	<tr>
			<td><b>non attribués</b></td>
			<td>".$cde_mois[nb]."</td>
			<td>".number_format($cde_mois[nb]*100/$cde_mois_total[nb], 1, ',', ' ') ." %</td>
			<td><a id=\"comm_non_att_non_valide_mois_".$value."\">".number_format($cde_mois[ca], 0, ',', ' ')." &euro;</a>
				<div id=\"div_non_att_non_valide_mois_".$value."\" style=\"display:none;\">".$cde_mois[cdes]."</div>
			</td>
			<td>".number_format($cde_mois[ca]*100/$cde_mois_total[ca], 1, ',', ' ') ." %</td>
			<td>".$cde_annee[nb]."</td>
			<td>".number_format($cde_annee[nb]*100/$cde_annee_total[nb], 1, ',', ' ') ." %</td>
			<td><a id=\"comm_non_att_non_valide_annee_".$value."\">".number_format($cde_annee[ca], 0, ',', ' ')." &euro;</a>
				<div id=\"div_non_att_non_valide_annee_".$value."\" style=\"display:none;\">".$cde_annee[cdes]."</div>
			</td>
			<td>".number_format($cde_annee[ca]*100/$cde_annee_total[ca], 1, ',', ' ') ." %</td>
		</tr>";
	$javascript_hover.="
		\$jQuery(\"#comm_non_att_non_valide_annee_".$value."\").click(function() {
		  \$jQuery(\"#div_non_att_non_valide_annee_".$value."\").toggle();
		});";
	$javascript_hover.="
		\$jQuery(\"#comm_non_att_non_valide_mois_".$value."\").click(function() {
		  \$jQuery(\"#div_non_att_non_valide_mois_".$value."\").toggle();
		});";
} else {
	echo "<tr><td colspan=\"9\">Pas assez de commandes pour faire des statistiques pour non attribué.</td></tr>";
}
echo "</table><br>";

echo "<script>
	\$jQuery( document ).ready(function() {
		".$javascript_hover."
	});
	</script>";






/* FUNCTION */
function tdvalue($value, $sym="", $sign=null, $climit=0){
	if($sym=="%"){
		$number=number_format($value,1,',',' ');
	} else {
		$number=number_format($value,0,',',' ');
	}
    return "<td style=\"color:".($value>$climit?"green":"red").";\">".($sign&&$value>0?"+":"").$number." ".$sym."</td>";
}

function auto_cde($periode, $j, $m, $a, $commercial="", $etatfacture="1,5,7,8", $log=""){
    global $DB_site2;
  
    $ca = 0;
    if($m<10)
		$m = "0".$m;
	//$j=$j+1;
	
    switch ($periode) {
        case 'annee':
            $and =" AND cast(f.datefacture as date) BETWEEN '".$a."-01-01' AND '".$a."-".$m."-".$j."' ";
            break;
        case 'mois':
            $and =" AND cast(f.datefacture as date) BETWEEN '".$a."-".$m."-01' AND '".$a."-".$m."-31' ";
            break;
        case 'jour':
            $and =" AND cast(f.datefacture as date) = '".$a."-".$m."-".$j."' ";
            break;
        case 'hier':
			$and =" AND cast(f.datefacture as date) = '".date('Y-m-d', mktime(0, 0, 0, $m, $j-1, $a) )."' ";
			break;
		case 'avanthier':
			$and =" AND cast(f.datefacture as date) = '".date('Y-m-d', mktime(0, 0, 0, $m, $j-2, $a) )."' ";
			break;
        case 'a-1':
            $and =" AND cast(f.datefacture as date) BETWEEN '".$a."-01-01' AND '".$a."-".$m."-".$j."' ";
            break;
		case 'm-1':
            $and =" AND cast(f.datefacture as date) BETWEEN '".$a."-".$m."-01' AND '".$a."-".$m."-".$j."' ";
            break;
    }
	if($commercial!=""){
		if($commercial!="tous"){
			$andcommercialf = " AND f.commercial_attribution IN('".$commercial."') ";
		} else {
			$andcommercialf="";
		}
	} else {
		$andcommercialf = " AND f.commercial_attribution IN('') ";
	}
	if($etatfacture=="0"){
		$moyenattente = "2,4,5,14,17,18,99";
		$and.=" AND moyenid IN (".$moyenattente.") ";
	}
	$nombre=0;
    $sql = "SELECT f.facturid, f.nom, f.heurefacture, SUM((lf.prix * lf.qte) / (1 + (lf.tva / 100))) as 'prixHT', f.prixport / (1 + f.tvaport / 100) as 'portHT',
			CASE WHEN (SELECT code_caviste_id FROM code_caviste WHERE code = f.cadeauid) IS NULL THEN (SUM((lf.prix * lf.qte) / (1 + (lf.tva / 100))) + (f.prixport / (1 + f.tvaport / 100))) * (f.montantcadeau / (SUM(lf.prix * lf.qte) + f.prixport)) ELSE 0 END as remiseHT 
            FROM facture f
			INNER JOIN lignefacture AS lf ON (lf.factureid = f.facturid)
            WHERE f.valide IN (".$etatfacture.") 
            ".$and." ".$andcommercialf."
			AND f.provenance IN('','mobile')
			GROUP BY f.facturid";
	if($log!=""){
		echo $sql;
	}
	$cdes="";
    $req = $DB_site2->query($sql);
    while($row = $DB_site2->fetch_array($req)){
		$totalfacture=$row['prixHT'] + $row['portHT'] - $row['remiseHT'];
        $ca += $totalfacture;
        $factid .= "<tr>
        <td><a href=\"facture.php?action=editer&facturid=".$row['facturid']."\" target=\"_blank\">".$row['facturid']."</a></td>
        <td>".$row['nom']."</td>
        <td>".date("d/m/Y",$row['heurefacture'])."</td>
        <td align=\"right\">".number_format($totalfacture,2,',','')."</td>
      </tr>";
		//if($type=="all"){
			// echo "facture N° ".$row[facturid]." : ".$totalfacture."<br>";
		//}
		$cdes.="F N° <a href=\"facture.php?action=editer&facturid=".$row[facturid]."\" target=\"_blank\">".$row[facturid]."</a> : ".number_format($totalfacture, 2, ',', ' ')." &euro;<br>";
		$nombre++;
    }
	
	if($etatfacture=="0"){
		$retour=array('nb' => $nombre, 'ca' => $ca , 'cdes' => $cdes, 'idfac' => $factid);
	} else {
		$retour=array('nb' => $nombre, 'ca' => $ca, 'idfac' => $factid);
	}
    return $retour;
}


echo "<br>";

include "./footer.php";
?>
