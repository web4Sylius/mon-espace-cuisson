<?php

// require "admin_global.php";
// include "./header.php";
// include "./menu.php";
include(BACK_PATH."/statistiques/functions_markdetailsite.php");

setlocale(LC_TIME, 'fr', 'fr_FR', 'fr_FR.ISO8859-1');

$jour = date("d");
$mois = date("m");
$annee = date("Y");
$etat = "1,5,7,8";
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

$ca_annee = $ca_annee_n1 = $ca_mois = $ca_mois_n1 = $ca_jour = $ca_hier = $caf_annee = $caf_annee_n1 = $caf_mois = $caf_mois_n1 = $caf_jour = $caf_hier = $obj_annee = $obj_mois = $obj_mois_m = $obj_jour = $obj_hier = $objf_annee = $objf_mois = $objf_mois_m = $objf_jour = $objf_hier = $avret_ht_annee = $avret_pc_annee = $avret_annee_n1 = $avret_ht_mois = $avret_pc_mois = $avret_mois_n1 = $avret_ht_jour = $avret_pc_jour = $avret_ht_hier = $avret_pc_hier = $avretf_ht_annee = $avretf_pc_annee = $avretf_annee_n1 = $avretf_ht_mois = $avretf_pc_mois = $avretf_mois_n1 = $avretf_ht_jour = $avretf_pc_jour = $avretf_ht_hier = $avretf_pc_hier = 0;

/* CALCUL TABLEAU */
$ca_annee       =   auto_ca("annee", $jour, $mois, $annee, "'','mp','mobile'");
$ca_annee_n1    =   auto_ca("a-1", $jour, $mois, $annee-1, "'','mp','mobile'");
$ca_mois        =   auto_ca("mois", $jour, $mois, $annee, "''");
$ca_mois_n1     =   auto_ca("mois", $jour, $mois, $annee-1, "''");
$ca_jour        =   auto_ca("jour", $jour, $mois, $annee, "''");
$ca_hier        =   auto_ca("hier", $jour, $mois, $annee, "''");

$caf_annee      =   auto_caf("annee", $jour, $mois, $annee, "'','mp','mobile'");
$caf_annee_n1   =   auto_caf("a-1", $jour, $mois, $annee-1, "'','mp','mobile'");
$caf_mois       =   auto_caf("mois", $jour, $mois, $annee, "''");
$caf_mois_n1    =   auto_caf("mois", $jour, $mois, $annee-1, "''");
$caf_jour       =   auto_caf("jour", $jour, $mois, $annee, "''");
$caf_hier       =   auto_caf("hier", $jour, $mois, $annee, "''");

$obj_annee      =   objectif_ca($annee, 1) + objectif_ca($annee, 2) + objectif_ca($annee, 3) + objectif_ca($annee, 4) + objectif_ca($annee, 5);
$obj_mois       =   objectif_ca($annee, 1, $mois) + objectif_ca($annee, 2, $mois) + objectif_ca($annee, 3, $mois) + objectif_ca($annee, 4, $mois);
$obj_mois_m     =   objectif_ca($annee, 1, $mois, true);
$obj_jour       =   $obj_mois / $t;
$obj_hier       =   $obj_mois / $t;

$objf_annee     =   objectif_caf($annee, 1) + objectif_caf($annee, 2) + objectif_caf($annee, 3) + objectif_caf($annee, 4) + objectif_caf($annee, 5);
$objf_mois      =   objectif_caf($annee, 1, $mois) + objectif_caf($annee, 2, $mois) + objectif_caf($annee, 3, $mois) + objectif_caf($annee, 5, $mois);
$objf_mois_m    =   objectif_caf($annee, 1, $mois, true);
$objf_jour      =   $objf_mois / $t;
$objf_hier      =   $objf_mois / $t;

$avret_ht_annee =   av_ret("annee", $mois, $annee, $t);
$avret_pc_annee =   av_ret_prcent("annee", $mois, $annee, $t);
$avret_annee_n1 =   av_ret_prcent_n1("annee", $mois, $annee-1, $t_n1);
$avret_ht_mois  =   av_ret("mois", $mois, $annee, $t);
$avret_pc_mois  =   av_ret_prcent("mois", $mois, $annee, $t);
$avret_mois_n1  =   av_ret_prcent_n1("mois", $mois-1, $annee, $t_n1);
$avret_ht_jour  =   av_ret("jour", $mois, $annee, $t);
$avret_pc_jour  =   av_ret_prcent("jour", $mois, $annee, $t);
$avret_ht_hier  =   av_ret("hier", $mois, $annee, $t);
$avret_pc_hier  =   av_ret_prcent("hier", $mois, $annee, $t);

$avretf_ht_annee =   av_retf("annee", $mois, $annee, $t);
$avretf_pc_annee =   av_ret_prcentf("annee", $mois, $annee, $t);
$avretf_annee_n1 =   av_ret_prcent_n1f("annee", $mois, $annee-1, $t_n1);
$avretf_ht_mois  =   av_retf("mois", $mois, $annee, $t);
$avretf_pc_mois  =   av_ret_prcentf("mois", $mois, $annee, $t);
$avretf_mois_n1  =   av_ret_prcent_n1f("mois", $mois-1, $annee, $t_n1);
$avretf_ht_jour  =   av_retf("jour", $mois, $annee, $t);
$avretf_pc_jour  =   av_ret_prcentf("jour", $mois, $annee, $t);
$avretf_ht_hier  =   av_retf("hier", $mois, $annee, $t);
$avretf_pc_hier  =   av_ret_prcentf("hier", $mois, $annee, $t);

print "
<table class=\"ica_tab\" id=\"ica_tab\" style=\"display:none;\">
    <thead>
        <tr>
            <td class=\"ica_thead\" colspan=\"11\">CA Enregistré</td>
        </tr>
    </thead>
    <tr>
        <td class=\"ica_title\" colspan=\"2\">CA Année ".$annee."</td>
        <td class=\"ica_sep\" colspan=\"1\" rowspan=\"8\"></td>
        <td class=\"ica_title\" colspan=\"2\">CA Mois ".ucfirst(strftime("%B"))."</td>
        <td class=\"ica_sep\" colspan=\"1\" rowspan=\"8\"></td>
        <td class=\"ica_title\" colspan=\"2\">CA Jour</td>
        <td class=\"ica_sep\" colspan=\"1\" rowspan=\"8\"></td>
        <td class=\"ica_title\" colspan=\"2\">CA Hier</td>
    </tr>
    <tr>
        <td colspan=\"2\"></td>
        <td class=\"ica_head\">CA Objectif Mois</td>
        <td>".number_format($obj_mois_m,0,',',' ')." &euro;</td>
        <td colspan=\"2\"></td>
        <td colspan=\"2\"></td>
    </tr>
    <tr>
        <td class=\"ica_head\">CA HT</td>
        <td>".number_format($ca_annee,0,',',' ')." &euro;</td>
        <td class=\"ica_head\">CA HT</td>
        <td>".number_format($ca_mois,0,',',' ')." &euro;</td>
        <td class=\"ica_head\">CA HT</td>
        <td>".number_format($ca_jour,0,',',' ')." &euro;</td>
        <td class=\"ica_head\">CA HT</td>
        <td>".number_format($ca_hier,0,',',' ')." &euro;</td>
    </tr>
    <tr>
        <td class=\"ica_head\">CA Objectif HT</td>
        <td>".number_format($obj_annee,0,',',' ')." &euro;</td>
        <td class=\"ica_head\">CA Objectif HT</td>
        <td>".number_format($obj_mois,0,',',' ')." &euro;</td>
        <td class=\"ica_head\">CA Objectif HT</td>
        <td>".number_format($obj_jour,0,',',' ')." &euro;</td>
        <td class=\"ica_head\">CA Objectif HT</td>
        <td>".number_format($obj_hier,0,',',' ')." &euro;</td>
    </tr>
    <tr>
        <td class=\"ica_head\" rowspan=\"2\">Av/Ret Objectif</td>
        ".tdvalue($avret_ht_annee,"&euro;",true)."
        <td class=\"ica_head\" rowspan=\"2\">Av/Ret Objectif</td>
        ".tdvalue($avret_ht_mois,"&euro;",true)."
        <td class=\"ica_head\" rowspan=\"2\">Av/Ret Objectif</td>
        ".tdvalue($avret_ht_jour,"&euro;",true)."
        <td class=\"ica_head\" rowspan=\"2\">Av/Ret Objectif</td>
        ".tdvalue($avret_ht_hier,"&euro;",true)."
    </tr>
    <tr>
        ".tdvalue($avret_pc_annee,"%",true)."
        ".tdvalue($avret_pc_mois,"%",true)."
        ".tdvalue($avret_pc_jour,"%",true)."
        ".tdvalue($avret_pc_hier,"%",true)."
    </tr>
    <tr>
        <td class=\"ica_head\" rowspan=\"2\">CA HT n-1</td>
        <td>".number_format($ca_annee_n1,0,',',' ')." &euro;</td>
        <td class=\"ica_head\" rowspan=\"2\">CA HT n-1</td>
        <td>".number_format($ca_mois_n1,0,',',' ')." &euro;</td>
        <td rowspan=\"2\" colspan=\"2\"></td>
        <td rowspan=\"2\" colspan=\"2\"></td>
    </tr>
    <tr>
        ".tdvalue($avret_annee_n1,"%",true)."
        ".tdvalue($avret_mois_n1,"%",true)."
    </tr>
</table>
<br>
<div class=\"clear\"></div>
<br>
<table class=\"ica_tab\" id=\"ica_tabf\" style=\"display:none;\">
    <thead>
        <tr>
            <td class=\"ica_thead\" colspan=\"11\">CA Facturé</td>
        </tr>
    </thead>
    <tr>
        <td class=\"ica_title\" colspan=\"2\">CA Année ".$annee."</td>
        <td class=\"ica_sep\" colspan=\"1\" rowspan=\"8\"></td>
        <td class=\"ica_title\" colspan=\"2\">CA Mois ".ucfirst(strftime("%B"))."</td>
        <td class=\"ica_sep\" colspan=\"1\" rowspan=\"8\"></td>
        <td class=\"ica_title\" colspan=\"2\">CA Jour</td>
        <td class=\"ica_sep\" colspan=\"1\" rowspan=\"8\"></td>
        <td class=\"ica_title\" colspan=\"2\">CA Hier</td>
    </tr>
    <tr>
        <td colspan=\"2\"></td>
        <td class=\"ica_head\">CA Objectif Mois</td>
        <td>".number_format($objf_mois_m,0,',',' ')." &euro;</td>
        <td colspan=\"2\"></td>
        <td colspan=\"2\"></td>
    </tr>
    <tr>
        <td class=\"ica_head\">CA HT</td>
        <td>".number_format($caf_annee,0,',',' ')." &euro;</td>
        <td class=\"ica_head\">CA HT</td>
        <td>".number_format($caf_mois,0,',',' ')." &euro;</td>
        <td class=\"ica_head\">CA HT</td>
        <td>".number_format($caf_jour,0,',',' ')." &euro;</td>
        <td class=\"ica_head\">CA HT</td>
        <td>".number_format($caf_hier,0,',',' ')." &euro;</td>
    </tr>
    <tr>
        <td class=\"ica_head\">CA Objectif HT</td>
        <td>".number_format($objf_annee,0,',',' ')." &euro;</td>
        <td class=\"ica_head\">CA Objectif HT</td>
        <td>".number_format($objf_mois,0,',',' ')." &euro;</td>
        <td class=\"ica_head\">CA Objectif HT</td>
        <td>".number_format($objf_jour,0,',',' ')." &euro;</td>
        <td class=\"ica_head\">CA Objectif HT</td>
        <td>".number_format($objf_hier,0,',',' ')." &euro;</td>
    </tr>
    <tr>
        <td class=\"ica_head\" rowspan=\"2\">Av/Ret Objectif</td>
        ".tdvalue($avretf_ht_annee,"&euro;",true)."
        <td class=\"ica_head\" rowspan=\"2\">Av/Ret Objectif</td>
        ".tdvalue($avretf_ht_mois,"&euro;",true)."
        <td class=\"ica_head\" rowspan=\"2\">Av/Ret Objectif</td>
        ".tdvalue($avretf_ht_jour,"&euro;",true)."
        <td class=\"ica_head\" rowspan=\"2\">Av/Ret Objectif</td>
        ".tdvalue($avretf_ht_hier,"&euro;",true)."
    </tr>
    <tr>
        ".tdvalue($avretf_pc_annee,"%",true)."
        ".tdvalue($avretf_pc_mois,"%",true)."
        ".tdvalue($avretf_pc_jour,"%",true)."
        ".tdvalue($avretf_pc_hier,"%",true)."
    </tr>
    <tr>
        <td class=\"ica_head\" rowspan=\"2\">CA HT n-1</td>
        <td>".number_format($caf_annee_n1,0,',',' ')." &euro;</td>
        <td class=\"ica_head\" rowspan=\"2\">CA HT n-1</td>
        <td>".number_format($caf_mois_n1,0,',',' ')." &euro;</td>
        <td rowspan=\"2\" colspan=\"2\"></td>
        <td rowspan=\"2\" colspan=\"2\"></td>
    </tr>
    <tr>
        ".tdvalue($avretf_annee_n1,"%",true)."
        ".tdvalue($avretf_mois_n1,"%",true)."
    </tr>
</table>
";



/* FUNCTION */
function td_value($value, $sym="", $sign=null, $climit=0){
    return "<td style=\"color:".($value>$climit?"green":"red").";\">".($sign&&$value>0?"+":"").number_format($value,2,',',' ').$sym."</td>";
}

/* * * * * * * * *
	ENREGISTRE
* * * * * * * * */
function objectif_ca($annee, $site_id, $mois=null, $now=false){
    global $DB_site2;

    if($now==true){
        $j = cal_days_in_month(CAL_GREGORIAN, $mois, $annee);
        $obj = objectif_ca($annee, 1, $mois) + objectif_ca($annee, 2, $mois) + objectif_ca($annee, 3, $mois) + objectif_ca($annee, 4, $mois) + objectif_ca($annee, 5, $mois);
        $montant = ($obj / $j) * date("d");
    }elseif($mois==null){
        $sql = "SELECT obj_montant FROM `objectifs_enregistres` WHERE obj_annee='".$annee."' AND site_id='".$site_id."' GROUP BY obj_mois";
        $req = $DB_site2->query($sql);
        while($row = $DB_site2->fetch_array($req)){
            $montant += $row['obj_montant'];
        }
    }else{
        $sql = "SELECT obj_montant FROM `objectifs_enregistres` WHERE obj_annee='".$annee."' AND obj_mois='".$mois."' AND site_id='".$site_id."' GROUP BY obj_mois";
        $req = $DB_site2->query_first($sql);
        $montant = $req['obj_montant'];
    }

    return $montant;
}

function auto_ca($return, $j, $m, $a, $prov){
    global $DB_site2;
  
    $ca = 0;
    if($m<10) $m = "0".$m;

    switch ($return) {
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
            $and =" AND cast(f.datefacture as date) = '".date('Y-m-d',strtotime("-1 day",time()))."' ";
			break;
        case 'a-1':
            $and =" AND cast(f.datefacture as date) BETWEEN '".$a."-01-01' AND '".$a."-".$m."-".$j."' ";
            break;
		case 'm-1':
            $and =" AND cast(f.datefacture as date) BETWEEN '".$a."-".$m."-01' AND '".$a."-".$m."-".$j."' ";
            break;
    }

    $sql = "SELECT SUM((lf.prix * lf.qte) / (1 + (lf.tva / 100))) as 'prixHT', MONTH(datefacture), f.prixport / (1 + f.tvaport / 100) as 'portHT', CASE WHEN (SELECT code_caviste_id FROM code_caviste WHERE code = f.cadeauid) IS NULL THEN (SUM((lf.prix * lf.qte) / (1 + (lf.tva / 100))) + (f.prixport / (1 + f.tvaport / 100))) * (f.montantcadeau / (SUM(lf.prix * lf.qte) + f.prixport)) ELSE 0 END as remiseHT 
            FROM facture f INNER JOIN lignefacture lf ON (lf.factureid = f.facturid)
            WHERE f.valide IN (1,5,7,8) AND f.provenance IN (".$prov.")
            ".$and." GROUP BY factureid";
    $req = $DB_site2->query($sql);
    while($row = $DB_site2->fetch_array($req)){
        $ca += $row['prixHT'] + $row['portHT'] - $row['remiseHT'];
    }

    return $ca;
}

function av_ret($type, $mois, $annee, $t){
    global $obj_annee;
    global $obj_mois;
    global $obj_mois_m;

    $return = 0;
    switch ($type) {
        case 'annee':
            for($i=1; $i < $mois; $i++){
                $obj = objectif_ca($annee, 1, $i) + objectif_ca($annee, 2, $i) + objectif_ca($annee, 3, $i) + objectif_ca($annee, 4, $i) + objectif_ca($annee, 5, $i);
                $return += auto_ca("m-1", date("d"), $i, $annee, "'','mp','mobile'") - $obj;
            }
            $return += auto_ca("m-1", date("d"), $mois, $annee, "'','mp','mobile'") - $obj_mois_m;
            break;

        case 'mois':
            $return = auto_ca("m-1", date("d"), $mois, $annee, "''") - $obj_mois;
            break;

        case 'jour':
            $j = date("d");
            $return = auto_ca("jour", $j, $mois, $annee, "''") - ($obj_mois / $t);
            break;

        case 'hier':
            $return = auto_ca("hier", 0, $mois, $annee, "''") - ($obj_mois / $t);
            break;
    }

    return $return;
}

function av_ret_prcent($type, $mois, $annee, $t){
    global $obj_annee;
    global $obj_mois;
    global $obj_mois_m;

    $return = 0;
    switch ($type) {
        case 'annee':
            for($i=1; $i < $mois; $i++){
                $obj_global = objectif_ca($annee, 1, $i) + objectif_ca($annee, 2, $i) + objectif_ca($annee, 3, $i) + objectif_ca($annee, 4, $i) + objectif_ca($annee, 5, $i);
                $obj += objectif_ca($annee, 1, $i) + objectif_ca($annee, 2, $i) + objectif_ca($annee, 3, $i) + objectif_ca($annee, 4, $i) + objectif_ca($annee, 5, $i);
                $avret += auto_ca("m-1", date("d"), $i, $annee, "'','mp','mobile'") - $obj_global;
            }
            $avret += auto_ca("m-1", date("d"), $mois, $annee, "'','mp','mobile'") - $obj_mois_m;
            $value = $obj_mois / $t;
            $value = $value * date("d");
            $obj = $obj + $value;
            $return = ($avret*100) / $obj;
            break;

        case 'mois':
            $avret = auto_ca("m-1", date("d"), $mois, $annee, "''") - $obj_mois;
            $return = ($avret*100) / $obj_mois;
            break;

        case 'jour':
            $j = date("d");
            $avret = auto_ca("jour", $j, $mois, $annee, "''") - ($obj_mois / $t);
            $return = ($avret*100) / ($obj_mois / $t);
            break;

        case 'hier':
            $avret = auto_ca("hier", 0, $mois, $annee, "''") - ($obj_mois / $t);
            $return = ($avret*100) / ($obj_mois / $t);
            break;
    }

    return $return;
}

function av_ret_prcent_n1($type, $mois, $annee, $t){
    global $ca_annee;
    global $ca_annee_n1;
    global $ca_mois;
    global $ca_mois_n1;

    if($type == 'annee'){
        $return = ($ca_annee * 100) / $ca_annee_n1 - 100;
    }elseif ($type == 'mois'){
        $return = ($ca_mois * 100) / $ca_mois_n1 - 100;
    }else
        $return = 0;
    return $return;
}


/* * * * * * * * *
	FACTURE
* * * * * * * * */
function objectif_caf($annee, $site_id, $mois=null, $now=false){
    global $DB_site2;

    if($now==true){
        $j = cal_days_in_month(CAL_GREGORIAN, $mois, $annee);
        $obj = objectif_caf($annee, 1, $mois) + objectif_caf($annee, 2, $mois) + objectif_caf($annee, 3, $mois) + objectif_caf($annee, 4, $mois) + objectif_caf($annee, 5, $mois);
        $montant = ($obj / $j) * date("d");
    }elseif($mois==null){
        $sql = "SELECT obj_montant FROM `objectifs_factures` WHERE obj_annee='".$annee."' AND site_id='".$site_id."' GROUP BY obj_mois";
        $req = $DB_site2->query($sql);
        while($row = $DB_site2->fetch_array($req)){
            $montant += $row['obj_montant'];
        }
    }else{
        $sql = "SELECT obj_montant FROM `objectifs_factures` WHERE obj_annee='".$annee."' AND obj_mois='".$mois."' AND site_id='".$site_id."' GROUP BY obj_mois";
        $req = $DB_site2->query_first($sql);
        $montant = $req['obj_montant'];
    }

    return $montant;
}

function auto_caf($return, $j, $m, $a, $prov){
    global $DB_site2;
  
    $ca = 0;
    if($m<10) $m = "0".$m;

    switch ($return) {
        case 'annee':
            $and =" AND cast(f.dateexpedition as date) BETWEEN '".$a."-01-01' AND '".$a."-12-31' ";
            break;
        case 'mois':
            $and =" AND cast(f.dateexpedition as date) BETWEEN '".$a."-".$m."-01' AND '".$a."-".$m."-31' ";
            break;
        case 'jour':
            $and =" AND cast(f.dateexpedition as date) = '".$a."-".$m."-".$j."' ";
            break;
        case 'hier':
            $and =" AND cast(f.dateexpedition as date) = '".date('Y-m-d',strtotime("-1 day",time()))."' ";
			break;
        case 'a-1':
            $and =" AND cast(f.dateexpedition as date) BETWEEN '".$a."-01-01' AND '".$a."-".$m."-".$j."' ";
            break;
		case 'm-1':
            $and =" AND cast(f.dateexpedition as date) BETWEEN '".$a."-".$m."-01' AND '".$a."-".$m."-".$j."' ";
            break;
    }

    $sql = "SELECT SUM((lf.prix * lf.qte) / (1 + (lf.tva / 100))) as 'prixHT', f.prixport / (1 + f.tvaport / 100) as 'portHT', CASE WHEN (SELECT code_caviste_id FROM code_caviste WHERE code = f.cadeauid) IS NULL THEN (SUM((lf.prix * lf.qte) / (1 + (lf.tva / 100))) + (f.prixport / (1 + f.tvaport / 100))) * (f.montantcadeau / (SUM(lf.prix * lf.qte) + f.prixport)) ELSE 0 END as remiseHT 
            FROM facture f INNER JOIN lignefacture lf ON (lf.factureid = f.facturid)
            WHERE f.valide IN (5,8) AND f.provenance IN (".$prov.")
            ".$and." GROUP BY factureid";
    $req = $DB_site2->query($sql);
    while($row = $DB_site2->fetch_array($req)){
        $ca += $row['prixHT'] + $row['portHT'] - $row['remiseHT'];
    }

    return $ca;
}

function av_retf($type, $mois, $annee, $t){
    global $objf_annee;
    global $objf_mois;
    global $objf_mois_m;

    $return = 0;
    switch ($type) {
        case 'annee':
            for($i=1; $i < $mois; $i++){
                $obj = objectif_caf($annee, 1, $i) + objectif_caf($annee, 2, $i) + objectif_caf($annee, 3, $i) + objectif_caf($annee, 4, $i) + objectif_caf($annee, 5, $i);
                $return += auto_caf("m-1", date("d"), $i, $annee, "'','mp','mobile'") - $obj;
            }
            $return += auto_caf("m-1", date("d"), $mois, $annee, "'','mp','mobile'") - $objf_mois_m;
            break;

        case 'mois':
            $return = auto_caf("m-1", date("d"), $mois, $annee, "''") - $objf_mois;
            break;

        case 'jour':
            $j = date("d");
            $return = auto_caf("jour", $j, $mois, $annee, "''") - ($objf_mois / $t);
            break;

        case 'hier':
            $return = auto_caf("hier", 0, $mois, $annee, "''") - ($objf_mois / $t);
            break;
    }

    return $return;
}

function av_ret_prcentf($type, $mois, $annee, $t){
    global $objf_annee;
    global $objf_mois;
    global $objf_mois_m;

    $return = 0;
    switch ($type) {
        case 'annee':
            for($i=1; $i < $mois; $i++){
                $obj_global = objectif_caf($annee, 1, $i) + objectif_caf($annee, 2, $i) + objectif_caf($annee, 3, $i) + objectif_caf($annee, 4, $i) + objectif_caf($annee, 5, $i);
                $obj += objectif_caf($annee, 1, $i) + objectif_caf($annee, 2, $i) + objectif_caf($annee, 3, $i) + objectif_caf($annee, 4, $i) + objectif_caf($annee, 5, $i);
                $avret += auto_caf("m-1", date("d"), $i, $annee, "'','mp','mobile'") - $obj_global;
            }
            $avret += auto_caf("m-1", date("d"), $mois, $annee, "'','mp','mobile'") - $objf_mois_m;
            $value = $objf_mois / $t;
            $value = $value * date("d");
            $obj = $obj + $value;
            $return = ($avret*100) / $obj;
            break;

        case 'mois':
            $avret = auto_caf("mois", 0, $mois, $annee, "''") - $objf_mois;
            $return = ($avret*100) / $objf_mois;
            break;

        case 'jour':
            $j = date("d");
            $avret = auto_caf("jour", $j, $mois, $annee, "''") - ($objf_mois / $t);
            $return = ($avret*100) / ($objf_mois / $t);
            break;

        case 'hier':
            $avret = auto_caf("hier", 0, $mois, $annee, "''") - ($objf_mois / $t);
            $return = ($avret*100) / ($objf_mois / $t);
            break;
    }

    return $return;
}

function av_ret_prcent_n1f($type, $mois, $annee, $t){
    global $caf_annee;
    global $caf_annee_n1;
    global $caf_mois;
    global $caf_mois_n1;
	
    if($type == 'annee'){
        $return = $caf_annee_n1 > 0 ? ($caf_annee * 100) / $caf_annee_n1 - 100 : 0;
    }elseif ($type == 'mois'){
        $return = $caf_mois_n1 > 0 ? ($caf_mois * 100) / $caf_mois_n1 - 100 : 0;
    }else
        $return = 0;
    return $return;
}