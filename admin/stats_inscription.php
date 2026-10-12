<?php

ini_set("display_errors","1");
error_reporting(E_ALL);

require "admin_global.php";
include "./header.php";
include "./menu.php";


echo "<h1>Statistiques d'inscription</h1>";

/*
''
'administrateur'
'configurateur'
'devisrapide'
'mobile'
'mp'
'site'
*/

setlocale(LC_TIME, 'fr', 'fr_FR', 'fr_FR.ISO8859-1');

$jour = date("d");
$mois = date("m");
$etat_inscription_site="'','site','mobile'";
$annee = date("Y")-1; // necessaire pour le goto

// $annee = date("Y");

// OUI, c'est du très très moche de faire un GOTO, mais j'ai trouvé que c'etait encore plus moche de faire autre chose .....
debuttableau:


$datedebutmois=$annee."-".$mois."-01";
$datejour=$annee."-".$mois."-".$jour;

$datedebutmois_1=date("Y-m-d",mktime(0,0,0,$mois-1,1,$annee));
$datejour_1=date("Y-m-d",mktime(0,0,0,$mois-1,$jour,$annee));

$datedebutannee=$annee."-01-01";

$datedebutannee_1=date("Y-m-d",mktime(0,0,0,1,1,$annee-1));
$datejourannee_1=date("Y-m-d",mktime(0,0,0,$mois,$jour,$annee-1));

$nb_mois_site			=compte_inscription($datedebutmois, $datejour , $etat_inscription_site);
$nb_mois_configurateur	=compte_inscription($datedebutmois, $datejour , "'configurateur'");
$nb_mois_devisrapide	=compte_inscription($datedebutmois, $datejour , "'devisrapide'");
$nb_mois_administrateur	=compte_inscription($datedebutmois, $datejour , "'administrateur'");
$nb_mois_mp				=compte_inscription($datedebutmois, $datejour , "'mp'");
$nb_mois_total			=$nb_mois_site+$nb_mois_configurateur+$nb_mois_devisrapide+$nb_mois_administrateur+$nb_mois_mp;

if($nb_mois_total>0){
	$pct_mois_site			=round($nb_mois_site*100/$nb_mois_total);
	$pct_mois_configurateur	=round($nb_mois_configurateur*100/$nb_mois_total);
	$pct_mois_devisrapide	=round($nb_mois_devisrapide*100/$nb_mois_total);
	$pct_mois_administrateur=round($nb_mois_administrateur*100/$nb_mois_total);
	$pct_mois_mp			=round($nb_mois_mp*100/$nb_mois_total);
} else {
	$pct_mois_site=$pct_mois_configurateur=$pct_mois_devisrapide=$pct_mois_administrateur=$pct_mois_mp=0;
}

$nb_mois_site_1				=compte_inscription($datedebutmois_1, $datejour_1 , $etat_inscription_site);
$nb_mois_configurateur_1	=compte_inscription($datedebutmois_1, $datejour_1 , "'configurateur'");
$nb_mois_devisrapide_1		=compte_inscription($datedebutmois_1, $datejour_1 , "'devisrapide'");
$nb_mois_administrateur_1	=compte_inscription($datedebutmois_1, $datejour_1 , "'administrateur'");
$nb_mois_mp_1				=compte_inscription($datedebutmois_1, $datejour_1 , "'mp'");
$nb_mois_total_1			=$nb_mois_site_1+$nb_mois_configurateur_1+$nb_mois_devisrapide_1+$nb_mois_administrateur_1+$nb_mois_mp_1;

if($nb_mois_total_1>0){
	$pct_mois_site_1			=round($nb_mois_site_1*100/$nb_mois_total_1);
	$pct_mois_configurateur_1	=round($nb_mois_configurateur_1*100/$nb_mois_total_1);
	$pct_mois_devisrapide_1		=round($nb_mois_devisrapide_1*100/$nb_mois_total_1);
	$pct_mois_administrateur_1	=round($nb_mois_administrateur_1*100/$nb_mois_total_1);
	$pct_mois_mp_1				=round($nb_mois_mp_1*100/$nb_mois_total_1);
} else {
	$pct_mois_site_1=$pct_mois_configurateur_1=$pct_mois_devisrapide_1=$pct_mois_administrateur_1=$pct_mois_mp_1=0;
}

$nb_annee_site				=compte_inscription($datedebutannee, $datejour , $etat_inscription_site);
$nb_annee_configurateur		=compte_inscription($datedebutannee, $datejour , "'configurateur'");
$nb_annee_devisrapide		=compte_inscription($datedebutannee, $datejour , "'devisrapide'");
$nb_annee_administrateur	=compte_inscription($datedebutannee, $datejour , "'administrateur'");
$nb_annee_mp				=compte_inscription($datedebutannee, $datejour , "'mp'");
$nb_annee_total				=$nb_annee_site+$nb_annee_configurateur+$nb_annee_devisrapide+$nb_annee_administrateur+$nb_annee_mp;

if($nb_annee_total>0){
	$pct_annee_site				=round($nb_annee_site*100/$nb_annee_total);
	$pct_annee_configurateur	=round($nb_annee_configurateur*100/$nb_annee_total);
	$pct_annee_devisrapide		=round($nb_annee_devisrapide*100/$nb_annee_total);
	$pct_annee_administrateur	=round($nb_annee_administrateur*100/$nb_annee_total);
	$pct_annee_mp				=round($nb_annee_mp*100/$nb_annee_total);
} else {
	$pct_annee_site=$pct_annee_configurateur=$pct_annee_devisrapide=$pct_annee_administrateur=$pct_annee_mp=0;
}

$nb_annee_site_1			=compte_inscription($datedebutannee_1, $datejourannee_1 , $etat_inscription_site);
$nb_annee_configurateur_1	=compte_inscription($datedebutannee_1, $datejourannee_1 , "'configurateur'");
$nb_annee_devisrapide_1		=compte_inscription($datedebutannee_1, $datejourannee_1 , "'devisrapide'");
$nb_annee_administrateur_1	=compte_inscription($datedebutannee_1, $datejourannee_1 , "'administrateur'");
$nb_annee_mp_1				=compte_inscription($datedebutannee_1, $datejourannee_1 , "'mp'");
$nb_annee_total_1			=$nb_annee_site_1+$nb_annee_configurateur_1+$nb_annee_devisrapide_1+$nb_annee_administrateur_1+$nb_annee_mp_1;

if($nb_annee_total_1>0){
	$pct_annee_site_1			=round($nb_annee_site_1*100/$nb_annee_total_1);
	$pct_annee_configurateur_1	=round($nb_annee_configurateur_1*100/$nb_annee_total_1);
	$pct_annee_devisrapide_1	=round($nb_annee_devisrapide_1*100/$nb_annee_total_1);
	$pct_annee_administrateur_1	=round($nb_annee_administrateur_1*100/$nb_annee_total_1);
	$pct_annee_mp_1				=round($nb_annee_mp_1*100/$nb_annee_total_1);
} else {
	$pct_annee_site_1=$pct_annee_configurateur_1=$pct_annee_devisrapide_1=$pct_annee_administrateur_1=$pct_annee_mp_1=0;
}







$causes=array(
	2=>"projet abandonné",
	7=>"Client injoignable",
	8=>"Double compte",
	10=>"achat ailleurs",
	11=>"Faux numéro/email",
	12=>"Petit dossier"
	);



echo "<h1>".$annee."</h1>
<table class=\"ica_tab\" id=\"ica_tab\">
	<tr>
        <td><b>Inscriptions</b></td>
        <td>Qté / mois</td>
        <td>% du mois</td>
        <td>Qté / M-1</td>
        <td>% M-1</td>
        <td>Qté année</td>
        <td>% année</td>
		<td>Qté année N-1</td>
		<td>% année N-1</td>
		<td>Qté différence N/N-1</td>
		<td>% différence N/N-1</td>
    </tr>
	<tr>
		<td>Ouverture compte sur site</td>
        <td>".$nb_mois_site."</td>
        <td>".$pct_mois_site." %</td>
        <td>".$nb_mois_site_1."</td>
        <td>".$pct_mois_site_1." %</td>
        <td>".$nb_annee_site."</td>
        <td>".$pct_annee_site." %</td>
		<td>".$nb_annee_site_1."</td>
        <td>".$pct_annee_site_1." %</td>
		".tdvalue($nb_annee_site-$nb_annee_site_1,"",true)."
		".tdvalue(difference($nb_annee_site, $nb_annee_site_1),"%",true)."
    </tr>
	<tr>
		<td>Ouverture compte sur configurateur</td>
        <td>".$nb_mois_configurateur."</td>
        <td>".$pct_mois_configurateur." %</td>
        <td>".$nb_mois_configurateur_1."</td>
        <td>".$pct_mois_configurateur_1." %</td>
        <td>".$nb_annee_configurateur."</td>
        <td>".$pct_annee_configurateur." %</td>
		<td>".$nb_annee_configurateur_1."</td>
        <td>".$pct_annee_configurateur_1." %</td>
		".tdvalue($nb_annee_configurateur-$nb_annee_configurateur_1,"",true)."
		".tdvalue(difference($nb_annee_configurateur, $nb_annee_configurateur_1),"%",true)."
    </tr>
	<tr>
		<td>Ouverture compte sur devis rapide</td>
        <td>".$nb_mois_devisrapide."</td>
        <td>".$pct_mois_devisrapide." %</td>
        <td>".$nb_mois_devisrapide_1."</td>
        <td>".$pct_mois_devisrapide_1." %</td>
        <td>".$nb_annee_devisrapide."</td>
        <td>".$pct_annee_devisrapide." %</td>
		<td>".$nb_annee_devisrapide_1."</td>
        <td>".$pct_annee_devisrapide_1." %</td>
		".tdvalue($nb_annee_devisrapide-$nb_annee_devisrapide_1,"",true)."
		".tdvalue(difference($nb_annee_devisrapide, $nb_annee_devisrapide_1),"%",true)."
    </tr>
	<tr>
		<td>Ouverture compte sur administrateur</td>
        <td>".$nb_mois_administrateur."</td>
        <td>".$pct_mois_administrateur." %</td>
        <td>".$nb_mois_administrateur_1."</td>
        <td>".$pct_mois_administrateur_1." %</td>
        <td>".$nb_annee_administrateur."</td>
        <td>".$pct_annee_administrateur." %</td>
		<td>".$nb_annee_administrateur_1."</td>
        <td>".$pct_annee_administrateur_1." %</td>
		".tdvalue($nb_annee_administrateur-$nb_annee_administrateur_1,"",true)."
		".tdvalue(difference($nb_annee_administrateur, $nb_annee_administrateur_1),"%",true)."
    </tr>
	<tr>
		<td>Ouverture compte sur mp</td>
        <td>".$nb_mois_mp."</td>
        <td>".$pct_mois_mp." %</td>
        <td>".$nb_mois_mp_1."</td>
        <td>".$pct_mois_mp_1." %</td>
        <td>".$nb_annee_mp."</td>
        <td>".$pct_annee_mp." %</td>
		<td>".$nb_annee_mp_1."</td>
        <td>".$pct_annee_mp_1." %</td>
		".tdvalue($nb_annee_mp-$nb_annee_mp_1,"",true)."
		".tdvalue(difference($nb_annee_mp, $nb_annee_mp_1),"%",true)."
    </tr>
	<tr>
		<td><b>Total inscription</b></td>
        <td>".$nb_mois_total."</td>
        <td>100 %</td>
        <td>".$nb_mois_total_1."</td>
        <td>100 %</td>
        <td>".$nb_annee_total."</td>
        <td>100 %</td>
		<td>".$nb_annee_total_1."</td>
        <td>100 %</td>
		".tdvalue($nb_annee_total-$nb_annee_total_1,"",true)."
		".tdvalue(difference($nb_annee_total, $nb_annee_total_1),"%",true)."
    </tr>
</table>
<br>
<div class=\"clear\"></div>
<br>

";


echo "
<table class=\"ica_tab\" id=\"ica_tab\">
	<tr>
        <td><b>Analyse clients perdus</b></td>
        <td>Qté / mois</td>
        <td>% du mois</td>
        <td>Qté / M-1</td>
        <td>% M-1</td>
        <td>Qté année</td>
        <td>% année</td>
		<td>Qté année N-1</td>
		<td>% année N-1</td>
		<td>Qté différence N/N-1</td>
		<td>% différence N/N-1</td>
    </tr>";
	
////////////////////	
// $causes
////////////////////
$cause_perte_valeur=array();
$cause_perte_total=array("qte_mois" => 0, "qte_mois_1" => 0, "qte_annee" => 0, "qte_annee_1" => 0);
foreach ($causes as $key => $value){
	$cause_perte_valeur[$key]['qte_mois']=perte_inscription($datedebutmois, $datejour , $key);
	$cause_perte_total['qte_mois']+=$cause_perte_valeur[$key]['qte_mois'];
	
	
	$cause_perte_valeur[$key]['qte_mois_1']=perte_inscription($datedebutmois_1, $datejour_1 , $key);
	$cause_perte_total['qte_mois_1']+=$cause_perte_valeur[$key]['qte_mois_1'];
	
	
	$cause_perte_valeur[$key]['qte_annee']=perte_inscription($datedebutannee, $datejour , $key);
	$cause_perte_total['qte_annee']+=$cause_perte_valeur[$key]['qte_annee'];
	
	
	$cause_perte_valeur[$key]['qte_annee_1']=perte_inscription($datedebutannee_1, $datejourannee_1 , $key);
	$cause_perte_total['qte_annee_1']+=$cause_perte_valeur[$key]['qte_annee_1'];
}
reset($causes);
foreach ($causes as $key => $value){
	
	if($cause_perte_total['qte_mois']>0){
		$pct_mois =round($cause_perte_valeur[$key]['qte_mois']*100/$cause_perte_total['qte_mois']);
	} else {
		$pct_mois =0;
	}
	if($cause_perte_total['qte_mois_1']>0){
		$pct_mois_1 =round($cause_perte_valeur[$key]['qte_mois_1']*100/$cause_perte_total['qte_mois_1']);
	} else {
		$pct_mois_1 =0;
	}
	if($cause_perte_total['qte_annee']>0){
		$pct_annee =round($cause_perte_valeur[$key]['qte_annee']*100/$cause_perte_total['qte_annee']);
	} else {
		$pct_annee =0;
	}
	if($cause_perte_total['qte_annee_1']>0){
		$pct_annee_1 =round($cause_perte_valeur[$key]['qte_annee_1']*100/$cause_perte_total['qte_annee_1']);
	} else {
		$pct_annee_1 =0;
	}
	
	echo "<tr>
		<td>".$value."</td>
        <td>".$cause_perte_valeur[$key]['qte_mois']."</td>
        <td>".$pct_mois." %</td>
        <td>".$cause_perte_valeur[$key]['qte_mois_1']."</td>
        <td>".$pct_mois_1." %</td>
        <td>".$cause_perte_valeur[$key]['qte_annee']."</td>
        <td>".$pct_annee." %</td>
		<td>".$cause_perte_valeur[$key]['qte_annee_1']."</td>
        <td>".$pct_annee_1." %</td>
		".tdvalue($cause_perte_valeur[$key]['qte_annee']-$cause_perte_valeur[$key]['qte_annee_1'],"",true)."
		".tdvalue(difference($cause_perte_valeur[$key]['qte_annee'], $cause_perte_valeur[$key]['qte_annee_1']),"%",true)."
    </tr>";
}
echo "<tr>
		<td><b>Total perdu</b></td>
        <td>".$cause_perte_total['qte_mois']."</td>
        <td>100 %</td>
        <td>".$cause_perte_total['qte_mois_1']."</td>
        <td>100 %</td>
        <td>".$cause_perte_total['qte_annee']."</td>
        <td>100 %</td>
		<td>".$cause_perte_total['qte_annee_1']."</td>
        <td>100 %</td>
		".tdvalue($cause_perte_total['qte_annee']-$cause_perte_total['qte_annee_1'],"",true)."
		".tdvalue(difference($cause_perte_total['qte_annee'], $cause_perte_total['qte_annee_1']),"%",true)."
    </tr>
</table>
<br>
<div class=\"clear\"></div>
<br>";


echo "
<table class=\"ica_tab\" id=\"ica_tab\">
	<tr>
        <td><b>Analyse Inscriptions / clients perdus</b></td>
        <td>Qté / mois</td>
        <td>Qté / M-1</td>
        <td>Qté année</td>
		<td>Qté année N-1</td>
		<td>% différence N/N-1</td>
    </tr>
	<tr>
		<td><b>inscription</b></td>
        <td>".$nb_mois_total."</td>
        <td>".$nb_mois_total_1."</td>
        <td>".$nb_annee_total."</td>
		<td>".$nb_annee_total_1."</td>
		".tdvalue(difference($nb_annee_total, $nb_annee_total_1),"%",true)."
    </tr>
	<tr>
		<td>Clients perdus</td>
        <td>".$cause_perte_total['qte_mois']."</td>
        <td>".$cause_perte_total['qte_mois_1']."</td>
        <td>".$cause_perte_total['qte_annee']."</td>
		<td>".$cause_perte_total['qte_annee_1']."</td>
		".tdvalue(difference($cause_perte_total['qte_annee'], $cause_perte_total['qte_annee_1']),"%",true)."
    </tr>
	<tr>
		<td>% perdus</td>
        <td>".round($cause_perte_total['qte_mois']/$nb_mois_total*100)." %</td>
        <td>".round($cause_perte_total['qte_mois_1']/$nb_mois_total_1*100)." %</td>
        <td>".round($cause_perte_total['qte_annee']/$nb_annee_total*100)." %</td>
		<td>".round($cause_perte_total['qte_annee_1']/$nb_annee_total_1*100)." %</td>
		<td>&nbsp;</td>
    </tr>
</table>";


	
$annee++;
if($annee<=date("Y")){
	goto debuttableau;
}
	

echo "<h1>Historique sur 90 jours</h1>
<table class=\"ica_tab\">
	<tr>
		<td>jour</td>
		<td>Nb inscriptions</td>
		<td>histogramme</td>
	</tr>";
for ($i = 0; $i <= 90; $i++) {
	$date_debut=date("Y-m-d", mktime(0,0,0,$mois,$jour-$i));
	$date_fin=date("Y-m-d", mktime(0,0,0,$mois,$jour-$i+1));
	
	$nb_jour_i=compte_inscription($date_debut, $date_fin , $etat_inscription_site);
	
	echo "<tr>
		<td>".$date_debut."</td>
		<td>".$nb_jour_i."</td>
		<td><div style=\"background-color:blue; width:".($nb_jour_i*10)."px;\">&nbsp;</div></td>
	</tr>";
}

	
	
function compte_inscription($debut,$fin,$etat_inscription_site){
	global $DB_site2;
	$sql="SELECT COUNT(*) FROM utilisateur
		WHERE provenance IN(".$etat_inscription_site.")
		AND cast(dateinscription as date) BETWEEN '".$debut."' AND '".$fin."' ";
	$res=$DB_site2->query_first($sql);
	return $res[0];
}

function perte_inscription($debut,$fin,$causeperte){
	global $DB_site2;
	$sql="SELECT COUNT(*) FROM utilisateur
		INNER JOIN causeperte ON (utilisateur.userid=causeperte.userid)
		WHERE cast(dateinscription as date) BETWEEN '".$debut."' AND '".$fin."'
		AND causeperte.cause='".$causeperte."' ";
	$res=$DB_site2->query_first($sql);
	return $res[0];
}


function difference($valeur1, $valeur2){
	if($valeur2>0){
		$retour=(($valeur1*100)/$valeur2)-100;
	} else {
		$retour=0;
	}
	return $retour;
}


/* FUNCTION */
function tdvalue($value, $sym="", $sign=null, $climit=0){
    return "<td style=\"color:".($value>$climit?"green":"red").";\">".($sign&&$value>0?"+":"").number_format($value,0,',',' ').$sym."</td>";
}


echo "<br>";

include "./footer.php";
?>
