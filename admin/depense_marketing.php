<?php

require "admin_global.php";
include "./header.php";
include "./menu.php";

echo "
<style>
	.ca { color:black; border-collapse:collapse; border:solid thin black; }
	.ca td { border:solid thin black; }
	.ca_nb { text-align:right; white-space:nowrap; }
	.ca_title { background-color:yellow; }
	.ca_head { font-weight:bold; text-align:center; }
</style>";

echo "<h1>Dépenses marketing</h1>";


if(isset($action) && ($action=="doadd" || $action=="domodify") ){
	$error=0;
	// on enregistre en BdD
	if(!isset($annee) || $annee=="" || $annee!=intval($annee) ){
		echo "année incorrecte";
		$error=1;
	}
	if(!isset($mois) || $mois=="" || $mois!=intval($mois) ){
		echo "mois incorrecte";
		$error=1;
	}
	if(!isset($categorieid) || $categorieid=="" || $categorieid!=intval($categorieid) ){
		echo "categorie incorrecte";
		$error=1;
	}
	if(!isset($montant) || $montant=="" || $montant!=floatval($montant) ){
		echo "montant incorrect";
		$error=1;
	}
	if(!isset($langue_dep) || $langue_dep=="" ){
		echo "langue incorrecte";
		$error=1;
	}
	if($error==1){
		$action=substr($action, 2);
	} else {
		$sql=" REPLACE INTO depense_marketing (categorieid, annee, mois, langue, montant, description) VALUES (".$categorieid.", ".$annee.", ".$mois.", '".$langue_dep."', ".$montant.", '".addslashes($depense_description)."') ";
		$DB_site2->query($sql);
		$action="";
	}
}

if(isset($action) && ($action=="add" || $action=="modify") ){
	$categorie_select="<select name=\"categorieid\">";
	$sql="	SELECT categorieid, libelle, type FROM depense_marketing_categorie ORDER by ordre ASC";
	$categories = $DB_site2->query($sql);
	while($categorie = $DB_site2->fetch_array($categories)){
		if($categorieid==$categorie['categorieid']){
			$selected="selected";
		} else {
			$selected="";
		}
		$categorie_select.="<option value=\"".$categorie['categorieid']."\" ".$selected.">".$categorie['libelle']." (".$categorie['type'].")</option>";
	}
	$categorie_select.="</select>";
	
	if($_GET['langue_dep']=="FR"){
		$langue_dep="FR";
	} else {
		$langue_dep="EN";
	}

	$sql = "	SELECT * FROM depense_marketing WHERE annee='".$annee."' AND mois='".$mois."' AND categorieid='".$categorieid."' AND langue='".$langue_dep."' ";
	$depense = $DB_site2->query_first($sql);
	
	if($langue_dep=="FR"){
		$depensefr="selected";
	} else {
		$depenseen="selected";
	}
	
	echo "<form action=\"depense_marketing.php\" method=\"get\">
			<input type=\"hidden\" name=\"action\" value=\"do".$action."\">
			<table>
				<tr>
					<td align=\"right\">Année :</td>
					<td><input type=\"text\" style=\"text-align:right;\" name=\"annee\" value=\"".$annee."\"></td>
				</tr>
				<tr>
					<td align=\"right\">Mois :</td>
					<td><input type=\"text\" style=\"text-align:right;\" name=\"mois\" value=\"".$mois."\"></td>
				</tr>
				<tr>
					<td align=\"right\">Catégorie :</td>
					<td>".$categorie_select."</td>
				</tr>
				<tr>
					<td align=\"right\">Site :</td>
					<td>
						<select name=\"langue_dep\">
							<option value=\"FR\" ".$depensefr.">FR</option>
							<option value=\"EN\" ".$depenseen.">EN</option>
						</select>
					</td>
				</tr>
				<tr>
					<td align=\"right\">Montant HT :</td>
					<td><input type=\"text\" style=\"text-align:right;\" name=\"montant\" value=\"".$montant."\"></td>
				</tr>
				<tr>
					<td align=\"right\">Description :</td>
					<td><textarea name=\"depense_description\" cols=\"40\" rows=\"5\">".$depense['description']."</textarea></td>
				</tr>
				<tr>
					<td colspan=\"2\" align=\"center\"><input type=\"submit\" value=\"enregistrer\"></td>
				</tr>
			</table>
			<center><a href=\"depense_marketing.php?year=".$annee."\">Retour</a></center>
		</form>";
}


if(!isset($action) || $action==""){ // on affiche le tableau des dépenses
	
	if(isset($_GET['year'])){
		$year=$_GET['year'];
	}else{
		$year=date("Y");
		// $year=2016;
	}

	echo "<p align=\"center\">Année : <select name=\"year\" onchange=\"document.location='depense_marketing.php?year='+this.value\">";

	for($y=2016;$y<=date("Y");$y++){
		echo "<option value=\"$y\"".($y==$year?" selected":"").">$y</option>";
	}
	echo "</select></p>";
	
	$langues=array("FR","EN");
	foreach ($langues as $key => $langue_dep) {
	
		$colonne=array();

		$sql="	SELECT DISTINCT(depense_marketing.categorieid), libelle, type FROM depense_marketing
				INNER JOIN depense_marketing_categorie ON (depense_marketing.categorieid=depense_marketing_categorie.categorieid)
				WHERE annee='".$year."' AND langue='".$langue_dep."'
				ORDER by ordre ASC";
		$categories = $DB_site2->query($sql);
		while($categorie = $DB_site2->fetch_array($categories)){
			$colonne[]=array("categorieid" => $categorie['categorieid'], "libelle" => $categorie['libelle'], "type" => $categorie['type'] );
		}

		$nbcolonnes=count($colonne);

		if($nbcolonnes==0){
			echo "aucune dépense enregistrée pour cette année.<br><br><a href=\"depense_marketing.php?action=add&annee=".$year."\">Ajouter une dépense</a>";
			break;
		}

		$nbcolonnestotal=$nbcolonnes+7;
		echo "<table class=\"ca\">
		<tr><td colspan=\"".$nbcolonnestotal."\" class=\"ca_title\" align=\"center\">Dépenses marketing ".$langue_dep."</td></tr>
		<tr>
		  <td class=\"ca_head\">Mois</td>";

		foreach ($colonne as $key => $value) {
			echo "<td class=\"ca_head\">".$value['libelle']."<br>(".$value['type'].")</td>";
		}

		echo "<td class=\"ca_head\">Total<br>online</td>
			<td class=\"ca_head\">Total<br>offline</td>
			<td class=\"ca_head\">Total<br>dépenses</td>
			<td class=\"ca_head\">Total<br>recettes</td>
			<td class=\"ca_head\">Ratio<br>online</td>
			<td class=\"ca_head\">Ratio<br>online & offline</td>
		</tr>";

		$total_total_ca=0;
		$total_total_depense_online=$total_total_depense_offline=0;
		for ($i = 1; $i <= 12; $i++) { // on fait défiller les mois
			echo "<tr>
					<td align=\"center\"><b>".$i."/".$year."</b></td>";
			$total_depense_online=$total_depense_offline=0;
			foreach ($colonne as $key => $value) {
				$sql=" SELECT montant, description FROM depense_marketing WHERE annee='".$year."' AND mois='".$i."' AND categorieid='".$value['categorieid']."' AND langue='".$langue_dep."' ";
				$depense = $DB_site2->query_first($sql);
				if($depense['montant']>0){
					echo "<td align=\"right\" nowrap><span title=\"".$depense['description']."\">".number_format($depense['montant'], 2, ",", " ")." €</span> <a href=\"depense_marketing.php?action=modify&categorieid=".$value['categorieid']."&annee=".$year."&mois=".$i."&langue_dep=".$langue_dep."&montant=".$depense['montant']."\">M</a></td>";
				} else {
					echo "<td align=\"right\" nowrap>0,00 € <a href=\"depense_marketing.php?action=add&categorieid=".$value['categorieid']."&annee=".$year."&mois=".$i."&langue_dep=".$langue_dep."\">A</a></td>";
				}
				if($value['type']=="online"){
					$total_depense_online+=$depense['montant'];
				} else {
					$total_depense_offline+=$depense['montant'];
				}
			}
			$total_ca=ca($year, $i, $langue_dep);
			$total_total_ca+=$total_ca;
			$total_total_depense_online+=$total_depense_online;
			$total_total_depense_offline+=$total_depense_offline;
			if($total_ca>0){
				$ratio_online=number_format( ($total_depense_online/$total_ca) * 100, 2, ",", "");
				$ratio_online_offline=number_format( (($total_depense_online+$total_depense_offline)/$total_ca) *100 , 2, ",", "");
				$couleur_ratio=couleur_ratio($ratio_online);
			} else {
				$ratio_online="--";
				$ratio_online_offline="--";
				$couleur_ratio="#ffffff";
			}
			$total_depense=$total_depense_online+$total_depense_offline;
			echo "<td align=\"right\" nowrap>".number_format($total_depense_online, 2, ",", " ")." €</td>
				<td align=\"right\" nowrap>".number_format($total_depense_offline, 2, ",", " ")." €</td>
				<td align=\"right\" nowrap>".number_format($total_depense, 2, ",", " ")." €</td>
				<td align=\"right\" nowrap>".number_format($total_ca, 2, ",", " ")." €</td>
				<td align=\"right\" nowrap style=\"background-color:".$couleur_ratio.";\"><b>".$ratio_online." %</b></td>
				<td align=\"right\" nowrap><b>".$ratio_online_offline." %</b></td>
			</tr>";
		}

		echo "<tr>
		  <td align=\"center\" class=\"ca_title\"><b>Total</b></td>";

		foreach ($colonne as $key => $value) {
			$sql="	SELECT SUM(montant) as montant FROM depense_marketing WHERE annee='".$year."' AND categorieid='".$value['categorieid']."' AND langue='".$langue_dep."' ";
			$depense = $DB_site2->query_first($sql);
			echo "<td align=\"right\" nowrap class=\"ca_title\"><b>".number_format($depense['montant'], 2, ",", " ")." €</b></td>";
		}

		if($total_total_ca>0){
			$total_ratio_online=number_format( ($total_total_depense_online/$total_total_ca) * 100, 2, ",", "");
			$total_ratio_online_offline=number_format( (($total_total_depense_online+$total_total_depense_offline)/$total_total_ca) *100 , 2, ",", "");
		} else {
			$total_ratio_online="--";
			$total_ratio_online_offline=="--";
		}
		$total_total_depense=$total_total_depense_online+$total_total_depense_offline;
		$couleur_ratio=couleur_ratio($total_ratio_online);
		echo "<td align=\"right\" nowrap class=\"ca_title\"><b>".number_format($total_total_depense_online, 2, ",", " ")." €</b></td>
			<td align=\"right\" nowrap class=\"ca_title\"><b>".number_format($total_total_depense_offline, 2, ",", " ")." €</b></td>
			<td align=\"right\" nowrap class=\"ca_title\"><b>".number_format($total_total_depense, 2, ",", " ")." €</b></td>
			<td align=\"right\" nowrap class=\"ca_title\"><b>".number_format($total_total_ca, 2, ",", " ")." €</b></td>
			<td align=\"right\" nowrap class=\"ca_title\" style=\"background-color:".$couleur_ratio.";\"><b>".$total_ratio_online." %</b></td>
			<td align=\"right\" nowrap class=\"ca_title\"><b>".$total_ratio_online_offline." %</b></td>
		</tr>";

		echo "</table><br><br>";
		unset($colonne);
	}
	echo "<br>Tous les montants sont exprimés HT.<br>Les recettes représentent les commandes enregistrées sur le site.<br><br><a href=\"depense_marketing.php?action=add&annee=".$year."\">Ajouter une dépense</a>";
}
include "./footer.php";


function ca($annee,$mois,$langue="FR"){
    global $DB_site2;
    $sql  = "SELECT f.facturid, f.heurefacture, f.provenance, SUM((lf.prix * lf.qte) / (1 + (lf.tva / 100))) as 'prixHT', f.prixport / (1 + f.tvaport / 100) as 'portHT', CASE WHEN (SELECT code_caviste_id FROM code_caviste WHERE code = f.cadeauid) IS NULL THEN (SUM((lf.prix * lf.qte) / (1 + (lf.tva / 100))) + (f.prixport / (1 + f.tvaport / 100))) * (f.montantcadeau / (SUM(lf.prix * lf.qte) + f.prixport)) ELSE 0 END as remiseHT";
    $sql .= " FROM facture f";
    $sql .= " INNER JOIN lignefacture lf ON (lf.factureid = f.facturid)";
    $sql .= " WHERE f.heurefacture BETWEEN ".mktime(0,0,0,$mois,1,$annee)." AND ".mktime(0,0,0,$mois+1,1,$annee);
    $sql .= " AND ( (f.valide IN (1,5,7,8) ) OR (f.valide = 0 AND f.moyenid in (2,4,5,14,17,18,99)) )  ";
	$sql .= " AND f.provenance IN ('', 'mobile')";
	$sql .= " AND f.langue = '".$langue."'";
    $sql .= " GROUP BY f.facturid";
	// echo $sql."<br><br><br>";
    $req = $DB_site2->query($sql);
    while($facture = $DB_site2->fetch_array($req)){
        $ca += $facture['prixHT'] + $facture['portHT'] - $facture['remiseHT'];
    }
    return $ca;
}

function couleur_ratio($ratio){
	if($ratio<8){
		$couleur="#07a004";
	}
	if($ratio>=8 && $ratio<10){
		$couleur="#39ff35";
	}
	if($ratio>=10 && $ratio<12){
		$couleur="#fc7e2a";
	}
	if($ratio>=12){
		$couleur="#ff0000";
	}
	return $couleur;
}

?>