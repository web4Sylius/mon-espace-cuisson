<?php

ini_set("display_errors","1");
error_reporting(E_ALL);

require "admin_global.php";
include "./header.php";
include "./menu.php";


echo "<h1>Statistiques Origine des ventes</h1>";


$provenance_type="'','mp','mobile','sav','avoir'";
$provenance_mp="'mp'";

setlocale(LC_TIME, 'fr', 'fr_FR', 'fr_FR.ISO8859-1');

$jour = date("d");
$mois = date("m");

$etat = "1,5,7,8";
// $etatpaye = "1,5";
// $etatnonpaye = "7,8";
$etatnonvalide = "0";
$etatfacture = "5,8";
$moyenattente = "2,4,5,14,17,18,99";
$annee = date("Y")-1;


// OUI, c'est du très très moche de faire un GOTO, mais j'ai trouvé que c'etait encore plus moche de faire autre chose .....
debuttableau:



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


$cde_mois_total=auto_cde("mois", $jour, $mois, $annee, "all");
$cde_annee_total=auto_cde("annee", $jour, $mois, $annee, "all");
$cde_annee_1_total=auto_cde("a-1", $jour, $mois, $annee_1, "all");

$typesfacture=array(
	"commercial" => "service commercial",
	"non_attribue" => "non attribuées",
	"direct" => "directe site",
	"marketplace" => "Market place",
	"sav" => "de SAV");


echo "<h1>".$annee."</h1>
<table class=\"ica_tab\" id=\"ica_tab\">
	<tr>
        <td></td>
        <td colspan=\"4\">mois en cours</td>
        <td colspan=\"4\">année en cours (en cumulé)</td>
        <td colspan=\"4\">comparatif en cumul / année N-1</td>
    </tr>
	<tr>
		<td>Stats ventes</td>
        <td>Qté cde</td>
        <td>% qté</td>
        <td>CA HT</td>
        <td>% CA HT</td>
		<td>Qté cde</td>
        <td>% qté</td>
        <td>CA HT</td>
        <td>% CA HT</td>
		<td>Qté cde</td>
        <td>% qté /N-1</td>
        <td>CA HT</td>
        <td>% CA HT / N-1</td>
    </tr>";




foreach ($typesfacture as $key => $value){
	$cde_mois=auto_cde("mois", $jour, $mois, $annee, $key);
	$cde_annee=auto_cde("annee", $jour, $mois, $annee, $key);
	$cde_annee_1=auto_cde("a-1", $jour, $mois, $annee_1, $key);
	echo "<tr>
			<td>Commandes ".$value."</td>
			<td>".$cde_mois[nb]."</td>
			".tdvalue($cde_mois[nb]*100/$cde_mois_total[nb],"%",0)."
			<td>".number_format($cde_mois[ca], 0, ',', ' ')." &euro;</td>
			".tdvalue($cde_mois[ca]*100/$cde_mois_total[ca],"%",0)."
			
			<td>".$cde_annee[nb]."</td>
			".tdvalue($cde_annee[nb]*100/$cde_annee_total[nb],"%",0)."
			<td>".number_format($cde_annee[ca], 0, ',', ' ')." &euro;</td>
			".tdvalue($cde_annee[ca]*100/$cde_annee_total[ca],"%",0)."
			
			<td>".$cde_annee_1[nb]."</td>
			".tdvalue($cde_annee_1[nb]*100/$cde_annee_1_total[nb],"%",0)."
			<td>".number_format($cde_annee_1[ca], 0, ',', ' ')." &euro;</td>";
	if($cde_annee_1[ca]>0){
		echo tdvalue(($cde_annee[ca]*100/$cde_annee_1[ca])-100,"%",1);
	} else {
		echo "<td>--</td>";
	}
	echo "</tr>";
}
echo "	<tr>
		<td align=\"right\"><b>Total</b></td>
        <td>".$cde_mois_total[nb]."</td>
		<td>100%</td>
		<td>".number_format($cde_mois_total[ca], 0, ',', ' ')." &euro;</td>
		<td>100%</td>
		<td>".$cde_annee_total[nb]."</td>
		<td>100%</td>
		<td>".number_format($cde_annee_total[ca], 0, ',', ' ')." &euro;</td>
		<td>100%</td>
		<td>".$cde_annee_1_total[nb]."</td>
		<td>100%</td>
		<td>".number_format($cde_annee_1_total[ca], 0, ',', ' ')." &euro;</td>
		".tdvalue(($cde_annee_total[ca]*100/$cde_annee_1_total[ca])-100,"%",1)."
	</tr>";

	
	
echo "</table>";

$annee++;
if($annee<=date("Y")){
	goto debuttableau;
}

echo "<br>Les CA sont les ventes enregistrés validés.";





/* FUNCTION */
function tdvalue($value, $sym="", $sign=null, $climit=0){
    return "<td style=\"color:".($value>$climit?"green":"red").";\">".($sign&&$value>0?"+":"").number_format($value,0,',',' ')." ".$sym."</td>";
}

/* * * * * * * * *
	ENREGISTRE
* * * * * * * * */

/*
function objectif_prorata($objectif, $mois, $jour){
    $retour=0;
	$t=date("t", mktime(0,0,0,$mois));
	$retour=$objectif/$t*$jour;
    return $retour;
}
*/


function auto_cde($periode, $j, $m, $a, $type){
    global $DB_site2;
  
    $ca = 0;
    if($m<10)
		$m = "0".$m;

    switch ($periode) {
        case 'annee':
            $and =" AND cast(f.datefacture as date) BETWEEN '".$a."-01-01' AND '".$a."-12-31' ";
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
	switch ($type) {
		/*
        case 'telephone':
            $and .=" AND f.commercial<>'' AND f.provenance NOT IN('mp','sav') ";
            break;
        case 'suivi_commercial':
            $and .=" AND f.commercial='' AND f.provenance IN('','mobile') AND (SELECT COUNT(*) FROM suivi_client WHERE suivi_client.userid=f.userid AND suivi_client.type='client' AND CAST(FROM_UNIXTIME(suivi_client.date) as date) BETWEEN DATE_SUB(f.datefacture, INTERVAL 1 MONTH) AND f.datefacture )>0 ";
            break;
        case 'direct':
            $and .=" AND f.commercial='' AND f.provenance IN('','mobile') AND (SELECT COUNT(*) FROM suivi_client WHERE suivi_client.userid=f.userid AND suivi_client.type='client' AND CAST(FROM_UNIXTIME(suivi_client.date) as date) BETWEEN DATE_SUB(f.datefacture, INTERVAL 1 MONTH) AND f.datefacture )=0 ";
            break;
		*/
		case 'commercial' :
			$and .= " AND f.commercial_attribution NOT IN ('direct','') ";
			break;
		case 'direct' :
			$and .= " AND f.commercial_attribution ='direct' ";
			break;
		case 'non_attribue' :
			$and .= " AND f.commercial_attribution ='' AND f.provenance NOT IN ('mp','sav','avoir') ";
			break;
			
			
        case 'marketplace':
			$and .=" AND f.provenance='mp' ";
			break;
		case 'sav':
			$and .=" AND f.provenance IN('sav','avoir') ";
			break;
		case 'all':
			$and .="  ";
			/*
			$and .="  AND NOT ( (f.commercial<>'' AND f.provenance NOT IN('mp','sav')) 
							OR (AND f.commercial='' AND f.provenance IN('','mobile') AND (SELECT COUNT(*) FROM suivi_client WHERE suivi_client.userid=f.userid AND suivi_client.type='client' AND suivi_client.date > DATE_SUB(f.datefacture, INTERVAL -1 MONTH))>0)
							OR (AND f.commercial='' AND f.provenance IN('','mobile') AND (SELECT COUNT(*) FROM suivi_client WHERE suivi_client.userid=f.userid AND suivi_client.type='client' AND suivi_client.date > DATE_SUB(f.datefacture, INTERVAL -1 MONTH) )=0)
							OR (f.provenance='mp')
							OR (f.provenance='sav')
						)";
			*/
			break;
    }
	$nombre=0;
    $sql = "SELECT f.facturid, SUM((lf.prix * lf.qte) / (1 + (lf.tva / 100))) as 'prixHT', f.prixport / (1 + f.tvaport / 100) as 'portHT',
		CASE WHEN (SELECT code_caviste_id FROM code_caviste WHERE code = f.cadeauid) IS NULL THEN (SUM((lf.prix * lf.qte) / (1 + (lf.tva / 100))) + (f.prixport / (1 + f.tvaport / 100))) * (f.montantcadeau / (SUM(lf.prix * lf.qte) + f.prixport)) ELSE 0 END as remiseHT 
            FROM facture f
			INNER JOIN lignefacture lf ON (lf.factureid = f.facturid)
            WHERE f.valide IN (1,5,7,8) 
            ".$and." GROUP BY f.facturid";
	if($type=="all"){
		 // echo $sql;
	}
    $req = $DB_site2->query($sql);
    while($row = $DB_site2->fetch_array($req)){
		$totalfacture=$row['prixHT'] + $row['portHT'] - $row['remiseHT'];
        $ca += $totalfacture;
		$nombre++;
    }
	$retour=array('nb' => $nombre, 'ca' => $ca );
    return $retour;
}








echo "<br>";

include "./footer.php";
?>
