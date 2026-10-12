<?php

require "admin_global.php";
include "./header.php";
include "./menu.php";
include "./marge.php";
include "../class/facture.class.php";
include_once("../class/DAOgarantie.class.php");
include_once("../class/garantie.class.php");
include_once("../class/DAOlignefacturegarantie.class.php");
include_once("../class/stock.class.php");
include_once("../class/frais_port.php");
include_once("../class/fournisseur.class.php");
include_once("./adv/adv.class.php");
include_once("./fonctions.php");



ini_set('display_errors',1);
ini_set("max_execution_time",180);
ini_set("memory_limit","256M");


$superusers = array("philippe", "cc", "benoit","ariel","alexis","jessica","jbandre");

$year=isset($_GET['year'])?intval($_GET['year']):date("Y");

echo "<style>
.ca { color:black; border-collapse:collapse; border:solid thin black; }
.ca td { border:solid thin black; }
.ca_nb { text-align:right; white-space:nowrap; }
.ca_title { background-color:yellow; text-align:center; }
.ca_head { font-weight:bold; text-align:center; }
</style>
<script language=\"javascript\">
 jQuery(function($) {
		$('.showpopup').mouseenter(function(){
    $(this).children('div').css('visibility', 'visible');
     $(this).children('div').css('background-color', '#AAFFAA');
    $(this).css('background-color', '#AAFFAA');
		}).mouseleave(function(){
    $(this).children('div').css('visibility', 'hidden');
     $(this).children('div').css('background-color', 'white');
    $(this).css('background-color', 'white');
		});
		});
</script>
<h1>Stats objectif commerciaux</h1>";



echo "<p align=\"center\">
<form name=\"src\">"; 

$is_superuser = in_array($user_info['username'], $superusers);

if($is_superuser){
	echo "Utilisateur : <select name=\"username\" onchange=\"document.forms.src.submit();\">";
	$liste_commerciaux=array('username' => 'direct', 'jours' => 'francs');
	$liste_commerciaux[]=array('username' => 'direct', 'jours' => 'francs');
	$selected="selected";
	$selected_direct="";
	$username="";
	if($_GET[username]==""){
		$username="";
	}
	elseif($_GET[username]=="direct"){
		$username="direct";
		$selected_direct="selected";
		$selected="";
	}
	elseif($_GET[username]!="direct"){
		$selected="";
	}
	echo "<option value=\"\" ".$selected.">Choisir</option>";
	echo "<option value=\"direct\" ".$selected_direct.">".UCfirst("direct")."</option>";
	$commercials=$DB_site2->query(" SELECT username FROM parametresgeneraux WHERE commissions ='1' ORDER BY username ASC ");
	while($commercial=$DB_site2->fetch_array($commercials)){
		$liste_commerciaux[]=array('username' => $commercial[username], 'jours' => 'ouvres');
		if($_GET[username]==$commercial[username]){
			$selected="selected";
			$username=$commercial['username'];
		} else {
			$selected="";
		}
		echo "<option value=\"".$commercial[username]."\" ".$selected.">".UCfirst($commercial[username])."</option>";
		$i++;
	}
	echo "</select>";
} else {
	$username=$user_info['username'];
}


$optyears="";
for($y=2010;$y<=date("Y");$y++)
    $optyears.="<option value=\"".$y."\"".($y==$year?" selected":"").">".$y."</option>";

echo " Année <select name=\"year\" onchange=\"document.forms.src.submit();\">".$optyears."</select>
</form>
</p>";

if($username=="") {
}
else {

$list=ca($year,null,$username);
$end=null;
if($year==date("Y")) $end=mktime(date("G"),0,0,date("n"),date("j"),$year-1);
$list1=ca($year-1,$end,$username);

// print_r($list);


$objectif_commercial=array();
$objectifs_sql=$DB_site2->query(" SELECT * FROM objectif_commercial WHERE username='".$username."' AND annee='".$year."' ORDER BY mois ASC ");
while($objectif=$DB_site2->fetch_array($objectifs_sql)){
	$objectif_commercial[$year][$objectif[mois]]=$objectif[objectif_ca];
}

echo "<table class=\"ca\">";
echo "<tr><td colspan=\"20\" class=\"ca_title\">Commandes ".UCfirst($username)."</td></tr>
<tr>
	<td class=\"ca_head\">Mois</td>
	<td class=\"ca_head\">Objectif mensuel HT</td>
	<td class=\"ca_head\">Nbre jours travaillés</td>
	<td class=\"ca_head\">Objectif journalier (*)</td>
	<td class=\"ca_head\">CA HT (validé)</td>
	<td class=\"ca_head\">Cdes en attente de règlement</td>
	<td class=\"ca_head\">Total Cdes enregistrées</td>
	<td class=\"ca_head\">% objectif du mois</td>
	<td class=\"ca_head\">Av/ret / objectif &euro;HT</td>
	<td class=\"ca_head\">Av/ret / objectif %</td>
	<td class=\"ca_head\">Av/ret / N-1 &euro;HT</td>
	<td class=\"ca_head\">% N-1</td>
	<td class=\"ca_head\">Nbre cdes</td>
	<td class=\"ca_head\">% N-1</td>
	<td class=\"ca_head\">Panier moyen</td>
	<td class=\"ca_head\">% N-1</td>
	<td class=\"ca_head\">Marge prev. HT</td>
	<td class=\"ca_head\">Marge prev. %</td>
	<td class=\"ca_head\">Remise accordée HT</td>
	<td class=\"ca_head\">Remise %</td>
</tr>";

$nb_jour_total=0;
$anencours=0;
$objtotal=0;
for($mois=1;$mois<=12;$mois++){
    $total=$list['ca'][$mois];
    $encours=$pcencours="";
    
	if($username=="direct"){
		$nb_jour=date("t", mktime(0,0,0,$mois,1,$year));
	} else{
		$nb_jour=getJours($year."-".$mois."-"."01",$year."-".$mois."-".date("t", mktime(0,0,0,$mois, 1, $year)),$username);
	}
	$nb_jour_total+=$nb_jour;
	
	if($mois==date("n") && $year==date("Y")){
		$jour_en_cours=getJours($year."-".$mois."-"."01",$year."-".$mois."-".date("j"),$username);
		$objencours=$jour_en_cours*$objectif_commercial[$year][$mois];
		$encours=$total-$objencours;
		$pcencours=$objencours>0?100*($encours/$objencours):0;
		$anencours+=$objencours;
		$totalencours += $encours;
		if($mois<=date('n')) {
			$objmois +=$objencours;
			$encoursmois += $encours;
		}
	}else{
		$encours=$total-($objectif_commercial[$year][$mois]*$nb_jour);
		$pcencours=$objectif_commercial[$year][$mois]>0?100*($encours/($objectif_commercial[$year][$mois]*$nb_jour)):0;
		$anencours+=$objectif_commercial[$year][$mois]*$nb_jour;
		$totalencours += $encours;
		if($mois<=date('n')) {
			$objmois+=$objectif_commercial[$year][$mois]*$nb_jour;
			$encoursmois += $encours;
		}
	}

    $nm1="";
    if($total>0){
		if($list1['ca'][$mois]>0){
			$nm1=100*($total-$list1['ca'][$mois])/$list1['ca'][$mois];
		} else {
			$nm1=0;
		}
    }

    $pcnb1="";
    if($list['nb'][$mois]>0){
		if($list1['nb'][$mois]>0){
			$pcnb1=100*($list['nb'][$mois]-$list1['nb'][$mois])/$list1['nb'][$mois];
		} else {
			$pcnb1=0;
		}
    }
    
    $panier=$panier1=$pcpanier1=0;
    if($list['nb'][$mois]>0){
		$panier=$total/$list['nb'][$mois];
		if($list1['nb'][$mois]>0){
			$panier1=$list1['ca'][$mois]/$list1['nb'][$mois];
			$pcpanier1=100*($panier-$panier1)/$panier1;
		} else {
			$panier1=0;
			$pcpanier1=0;
		}
    }
	
	if($total>0){
		$remise_pct=100*$list['remise'][$mois]/$total;
		$marge_pct=100*$list['marge'][$mois]/$total;
    } else {
		$remise_pct=0;
		$marge_pct=0;
	}
	
	$objtotal+=$objectif_commercial[$year][$mois]*$nb_jour;

    echo "<tr>
	  <td align=\"center\"><a href=\"facture.php?commercial_attribution=".$username."&fromdate=".mktime(0,0,0,$mois,1,$year)."&todate=".mktime(23,59,59,$mois,cal_days_in_month(CAL_GREGORIAN,$mois,$year),$year)."&lst_trietat[]=1&lst_trietat[]=5&lst_trietat[]=7&lst_trietat[]=8\" target=\"_blank\">".$mois."/".$year."</a></td>
      <td class=\"ca_nb\">".number_format($objectif_commercial[$year][$mois]*$nb_jour,0,',',' ')." &euro;</td>
	  <td class=\"ca_nb\">".$nb_jour."</td>
	  <td class=\"ca_nb\">".number_format($objectif_commercial[$year][$mois],0,',',' ')." &euro;</td>

      ".tdvalue($total,"&euro;",null,number_format($objectif_commercial[$year][$mois]*$nb_jour,0,',',' '), "showpopup")."
      <div class=\"popup\"><table>".$list['factid'][$mois]."</table></div></td>
	  <td class=\"ca_nb showpopup\">".number_format($list['attente'][$mois],0,',',' ')." &euro;
	  <div class=\"popup\"><table>".$list['factid_attente'][$mois]."</table></div></td>
	  <td class=\"ca_nb\">".number_format($total+$list['attente'][$mois],0,',',' ')." &euro;</td>
      ".tdvalue($objectif_commercial[$year][$mois]?100*$total/($mois==date("n")&&$year==date("Y")?$objencours:($objectif_commercial[$year][$mois]*$nb_jour)):0,"%",false,100)
      .($mois<=date('n')||$year!=date("Y")?tdvalue($encours,"&euro;",true):"<td class=\"ca_nb\"></td>")  
      .($mois<=date('n')||$year!=date("Y")?tdvalue($pcencours,"%",true):"<td class=\"ca_nb\"></td>")
      .tdvalue($total-($list1['ca'][$mois]),"&euro;",true)
      .tdvalue($nm1,"%",true)
      ."<td class=\"ca_nb\">".$list['nb'][$mois]."</td>"
      .tdvalue($pcnb1,"%",true)
      ."<td class=\"ca_nb\">".number_format($panier,0,',',' ')." &euro;</td>"
      .tdvalue($pcpanier1,"%",true)
	  ."<td class=\"ca_nb\">".number_format($list['marge'][$mois],0,',',' ')." &euro;</td>
	  <td class=\"ca_nb\">".number_format($marge_pct,1,',',' ')." %</td>
	  <td class=\"ca_nb\">".number_format($list['remise'][$mois],0,',',' ')." &euro;</td>
	  <td class=\"ca_nb\">".number_format($remise_pct,1,',',' ')." %</td>
    </tr>";
}



$total=array_sum($list['ca']);
$total1=array_sum($list1['ca']);
$nb=array_sum($list['nb']);
$nb1=array_sum($list1['nb']);
$remise=array_sum($list['remise']);
$marge=array_sum($list['marge']);
$attente=array_sum($list['attente']);
$panier=0;
if($nb>0){
	$panier=$total/$nb;
} else {
	$panier=0;
}
if($nb1>0){
	$panier1=$total1/$nb1;
	$nb_pct=100*($nb-$nb1)/$nb1;
} else {
	$panier1=0;
	$nb_pct=0;
}
if($total1>0){
	$total_pct=100*($total-$total1)/$total1;
} else {
	$total_pct=0;
}
if($panier1>0){
	$panier_pct=100*($panier-$panier1)/$panier1;
} else {
	$panier_pct=0;
}

echo "<tr style=\"font-weight:bold;\">
    <td align=\"right\">Total</td>
    <td class=\"ca_nb\">".number_format($objtotal,0,',',' ')." &euro;</td>
	<td class=\"ca_nb\">".$nb_jour_total."</td>
	<td class=\"ca_nb\">".@number_format($objtotal/$nb_jour_total,0,',',' ')." &euro;</td>"
    .tdvalue($total,"&euro;",false,$anencours)
	."<td class=\"ca_nb\">".number_format($attente,0,',',' ')." &euro;</td>
	<td class=\"ca_nb\">".number_format($attente+$total,0,',',' ')." &euro;</td>"
    .($mois==12||$year!=date("Y")?tdvalue($anencours>0?100*$total/$anencours:0,"%",false,100):tdvalue($anencours>0?100*$total/$objmois:0,"%",false,100))
    .($mois==12||$year!=date("Y")?tdvalue($total-$anencours,"&euro;",true):tdvalue($encoursmois,"&euro;",true))
    .($mois==12||$year!=date("Y")?tdvalue($anencours>0?100*($total-$anencours)/$anencours:0,"%",true):tdvalue($anencours>0?100*$encoursmois/$objmois:0,"%",true))
    .tdvalue($total-$total1,"&euro;",true)
    .tdvalue($total_pct,"%",true)
    ."<td class=\"ca_nb\">".$nb."</td>"
    .tdvalue($nb_pct,"%",true)
    ."<td class=\"ca_nb\">".number_format($panier,0,',',' ')." &euro;</td>"
    .tdvalue($panier_pct,"%",true)
	."<td class=\"ca_nb\">".number_format($marge,0,',',' ')." &euro;</td>
	<td class=\"ca_nb\">".@number_format(100*$marge/$total,1,',',' ')." %</td>
	<td class=\"ca_nb\">".number_format($remise,0,',',' ')." &euro;</td>
	<td class=\"ca_nb\">".@number_format(100*$remise/$total,1,',',' ')." %</td>
</tr>";

echo "</table><br>(*) = uniquement sur les jours travaillés.<br>Tous les chiffres concernent le CA enregistré validé (sauf ''Cdes en attente de règlement'' et ''Total Cdes enregistrées'').<br>Pour l'annÃée en cours, les calculs et les totaux se basent sur les jours passés et en cours.";
}

include "./footer.php";


function ca($annee,$to=null,$username=""){
    global $DB_site2;
    
    if(is_null($to)) $to = mktime(23,59,59,12,31,$annee);
	$ca = $nb = $remise = $marge = $attente = $factid = array();
	$totalachat=$totalvente=0;
    $sql = "SELECT f.facturid, f.heurefacture, f.marge, f.provenance,
			SUM((lf.prix * lf.qte) / (1 + (lf.tva / 100))) as 'prixHT',
			f.prixport / (1 + f.tvaport / 100) as 'portHT', u.nom, u.raisonsociale,
			CASE WHEN (SELECT code_caviste_id FROM code_caviste WHERE code = f.cadeauid) IS NULL THEN (SUM((lf.prix * lf.qte) / (1 + (lf.tva / 100))) + (f.prixport / (1 + f.tvaport / 100))) * (f.montantcadeau / (SUM(lf.prix * lf.qte) + f.prixport)) ELSE 0 END as remiseHT";
    $sql .= " FROM facture f";
    $sql .= " INNER JOIN lignefacture lf ON (lf.factureid = f.facturid)";
    $sql .= " INNER JOIN utilisateur u ON (f.userid = u.userid)";
    $sql .= " WHERE f.heurefacture BETWEEN ".mktime(0,0,0,1,1,$annee)." AND ".$to;
    $sql .= " AND f.valide IN (1,5,7,8)";
    $sql .= " AND f.provenanceid = ''";
    $sql .= " AND f.commercial_attribution ='".$username."' ";
    $sql .= " GROUP BY f.facturid";

    $req = $DB_site2->query($sql);
    while($facture = $DB_site2->fetch_array($req)){
        $month = date("n",$facture['heurefacture']);
		// echo "<br><br>mois de ".$month."<br>";
		$totalvente = $facture['prixHT'] + $facture['portHT'] - $facture['remiseHT'];
		$totalachat=0;
        $ca[$month] += $totalvente;
		$remise[$month] += $facture['remiseHT'];
        $nb[$month]++;
		// echo "Cde NÂ° ".$facture[facturid]." : ".$totalvente."<br>";
		$totalachat=calcul_prix_achat_facture($facture[facturid]);
		$marge[$month]+=$totalvente-$totalachat;
		//affiche la marge en % dans les lignes popup
    	if($facture['marge']!='' && $facture['marge'] != 0.00 && number_format($totalvente,0,',',' ') != 0) {
    		$margepc_td = "<td title=\"marge\" ".($facture['marge']<=15 ? 'style=\'background-color:#e65858b3;\'' : '').">".$facture['marge']."%</td>";
    	}
    	else {
    		$margepc_td = "<td title=\"marge\"></td>";
    	}
		// echo "cumulÃ© de CA : ".$ca[$month]."<br>";
		$factid[$month] .= "<tr class=\"ligne_popup\">
        <td><a href=\"facture.php?action=editer&facturid=".$facture['facturid']."\" target=\"_blank\">".$facture['facturid']."</a></td>
        <td>".$facture['nom'].($facture['raisonsociale']?' - '.$facture['raisonsociale']:'')."</td>
        <td>".date("d/m/Y",$facture['heurefacture'])."</td>
        <td align=\"right\">".number_format($totalvente,0,',',' ')."</td>
        ".$margepc_td."
      </tr>";
    }
	// ici uniquement les commandes en attente de rÃ¨glement
	$sql = "SELECT f.facturid, f.heurefacture, f.provenance,
			SUM((lf.prix * lf.qte) / (1 + (lf.tva / 100))) as 'prixHT',
			f.prixport / (1 + f.tvaport / 100) as 'portHT', f.nom,
			CASE WHEN (SELECT code_caviste_id FROM code_caviste WHERE code = f.cadeauid) IS NULL THEN (SUM((lf.prix * lf.qte) / (1 + (lf.tva / 100))) + (f.prixport / (1 + f.tvaport / 100))) * (f.montantcadeau / (SUM(lf.prix * lf.qte) + f.prixport)) ELSE 0 END as remiseHT";
    $sql .= " FROM facture f";
    $sql .= " INNER JOIN lignefacture lf ON (lf.factureid = f.facturid)";
    $sql .= " WHERE f.heurefacture BETWEEN ".mktime(0,0,0,1,1,$annee)." AND ".$to;
    $sql .= " AND f.valide = 0";
    $sql .= " AND f.moyenid in (2,4,5,14,17,18,99)";
    $sql .= " AND f.provenanceid = ''";
    $sql .= " AND f.commercial_attribution ='".$username."' ";
    $sql .= " GROUP BY f.facturid";
    $req = $DB_site2->query($sql);
    while($facture = $DB_site2->fetch_array($req)){
        $month = date("n",$facture['heurefacture']);
		$totalattente = $facture['prixHT'] + $facture['portHT'] - $facture['remiseHT'];
        $attente[$month] += $totalattente;
        $factid_attente[$month] .= "<tr>
        <td><a href=\"facture.php?action=editer&facturid=".$facture['facturid']."\" target=\"_blank\">".$facture['facturid']."</a></td>
        <td>".$facture['nom']."</td>
        <td>".date("d/m/Y",$facture['heurefacture'])."</td>
        <td align=\"right\">".number_format($totalattente,0,',',' ')."</td>
      </tr>";
    }
	
    return array(
        "ca" => $ca,
        "nb" => $nb,
		"remise" => $remise,
		"marge" => $marge,
		"attente" => $attente,
		"factid" => $factid,
		"factid_attente" => $factid_attente
    );
}


function calcul_prix_achat_facture($facturid){
	global $DB_site2;
	$totalachat=0;
	
	$sql = "SELECT f.facturid, SUM(lf.paht * lf.qte) as 'prixht' FROM facture f";
    $sql .= " INNER JOIN lignefacture lf ON (lf.factureid = f.facturid)";
    $sql .= " WHERE f.facturid = ".$facturid;
	$requete = $DB_site2->query($sql);
	while($ligne = $DB_site2->fetch_array($requete)){
		$totalachat += $ligne['prixht'];
	}
	
	
	$sql = "SELECT ef.id AS efid, ef.facturid, ef.tarif, ef.type, efl.ligne_facture_id, efl.paht, efl.ecopart, efl.qte, efl.facture, efl.date";
	$sql .= " FROM envoi_fournisseur ef";
	$sql .= " INNER JOIN envoi_fournisseur_ligne efl ON (efl.efid = ef.id)";
	$sql .= " WHERE ef.destinataire <> '0'";
	$sql .= " AND ef.facturid = ".$facturid;
	$requete = $DB_site2->query($sql);
	while($ligne = $DB_site2->fetch_array($requete)){
		// l'achat des produits
// 		$totalachat += $ligne['qte'] * ($ligne['paht'] + $ligne['ecopart']);
		// echo "ligne 1 : + ".$ligne['qte']." * (".$ligne['paht']." + ".$ligne['ecopart'].") => ".$totalachat."<br>";
		
		// le "mazetcout" => le truc incomprÃ©hensible ....
		$adv=new adv($DB_site2);
		$adv->load_from_efid($ligne['efid']);
		if(isset($adv->fields['mazetcout']) && !isset($trsptfourn['mazetcout'.$adv->id])){
			$totalachat += floatval($adv->fields['mazetcout']);
			// echo "ligne mazetcout : + ".$adv->fields['mazetcout']."<br>";
		}
	}
	
	// les autres fournisseurs (installateurs ?)
	$sql = "SELECT *";
	$sql .= " FROM facture_fournisseur_autre";
	$sql .= " WHERE facturid = ".$facturid;
	$requete = $DB_site2->query($sql);
	while($ligne = $DB_site2->fetch_array($requete)){
		$totalachat += $ligne['prix'];
		// echo "ligne 2 : + ".$ligne['prix']." <br>";
	}
	
	// les avoirs
	$sql = "SELECT ffid, facturid, date, utilisateur, destinataire, article, paht, ecopart, qte, date_recu";
	$sql .= " FROM avoir_ligne";
	$sql .= " WHERE facturid = ".$facturid;
	$requete = $DB_site2->query($sql);
	while($ligne=$DB_site2->fetch_array($requete)){
		if($ligne['date_recu'] == 0){
			$totalachat -= ($ligne['paht']+$ligne['ecopart']);
			// echo "ligne 3 : - ( ".$ligne['paht']." + ".$ligne['ecopart']." ) <br>";
		}
	}
	
	// les retours
	$sql = "SELECT s.artid, a.artcode, s.date, s.utilisateur, a.paht, a.ecopart, COUNT(*) AS qte";
	$sql.= " FROM stock_item s";
	$sql.= " INNER JOIN article a USING(artid)";
	$sql.= " WHERE origin ='retour:".$facturid."'";
	$sql.= " GROUP BY date";
	$requete = $DB_site2->query($sql);
	while($retour = $DB_site2->fetch_array($requete)){
		$artids=array($retour['artid']);
		$si = $DB_site2->query("SELECT artid, artids FROM stock_mini WHERE artid=".$retour['artid']);
		if($DB_site2->num_rows($si) != 0){
			$a = $DB_site2->fetch_array($si);
			$artids = array_merge($artids,explode(",",$a['artids']));
		}
		$artids=array_filter($artids);
		$sql_lf = "SELECT lignefactureid";
		$sql_lf.= " FROM lignefacture";
		$sql_lf.= " WHERE factureid =".$facturid;
		$sql_lf.= " AND artid IN (".implode(",",$artids).")";
		$lignefacture = $DB_site2->query_first($sql_lf);
		if($lignefacture!=""){
            $sql = "SELECT efl.id, ROUND((efl.paht + efl.ecopart),2) AS montantht";
                    $sql.= " FROM envoi_fournisseur_ligne efl";
                    $sql.= " INNER JOIN envoi_fournisseur ef ON (ef.id = efl.efid)";
                    $sql.= " WHERE efl.ligne_facture_id = ".$lignefacture['lignefactureid'];
                    $sql.= " AND (efl.type = 'stock' OR efl.type = 'fourn')";
                    $sql.= " AND ef.destinataire <> '0'";
                    $sql.= " AND ef.facturid = ".$facturid;
            $ht = $DB_site2->query_first($sql);
            $totalachat -= ($retour['qte']*$ht['montantht']);
		}
		// echo "ligne 4 : - ( ".$retour['qte']." * ".$ht['montantht']." ) <br>";
	}
	// echo "ligne total : = ".$totalachat."<br>";
	return $totalachat;
}



function tdvalue($value,$sym="",$sign=null,$climit=0,$class=""){

	if($value=="")
		if($class=="") {
			return "<td></td>";
		}
		else {
			return "<td>";
		}
		if($sym=="&euro;"){
			$value=number_format($value,0,',',' ');
		} else {
			$value=number_format($value,1,',',' ');
		}
		if($class=="") {
			return "<td class=\"ca_nb\" style=\"color:".($value>$climit?"green":"red").";\">".($sign&&$value>0?"+":"").$value." ".$sym."</td>";
		}
		else {
			return "<td class=\"ca_nb showpopup\" style=\"color:".(intval(str_replace(" ", "", $value))>intval(str_replace(" ", "", $climit))?"green":"red").";\">".($sign&&$value>0?"+":"").$value." ".$sym;
		}
	}


?>
