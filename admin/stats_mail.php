<?php

require "admin_global.php";
include "./header.php";
include "./menu.php";

if(!isset($year)) $year = date("Y");

$month = array("","Janvier","Février","Mars","Avril","Mai","Juin","Juillet","Aout","Septembre","Octobre","Novembre","Décembre");

?>

<style>
.tab_conf { border-collapse: collapse; border: solid thin black; text-align: center; margin-top: 10px; width: 30%;}
.tab_conf tr:first-child { font-weight: bold; background-color: #395870; line-height: 25px; }
.tab_conf tr:first-child td { color: white; }
.tab_conf td:first-child { font-weight: bold; }
.tab_conf tr:nth-child(2n) { background-color: #f5f5f5; }
.tab_conf tr:last-child { background-color: #b9ccdb; font-weight: bold; }
</style>

<?php 

print "<h1>Stats configurateur</h1>
<br>Ceci concerne toutes les inscriptions de clients par le formulaire du configurateur.<br>
Ceci est un détail de la page de <a href=\"stats_inscription.php\">statistiques d'inscription</a><br>";

for($y=2010; $y<=date("Y"); $y++) 
	$optyear .= "<option value=\"".$y."\"".($y == $year ? " selected" : "").">".$y."</option>";

print "<p align=\"center\">Année : <select name=\"year\" onchange=\"document.location='stats_mail.php?year='+this.value\">".$optyear."</select>";

print "<table class=\"tab_conf\">
	<tr>
		<td>Tableau ".$year."</td>
		<td>Nb. Total</td>
		<td>Nb. Invalide *</td>
	</tr>";

$count = $total = 0;
for($mois=1; $mois<13; $mois++){
	$value = 0;
	$d = cal_days_in_month(CAL_GREGORIAN, $mois, $year);
	$m = $mois;
	if($m < 10) $m = "0".$m;

	$req = $DB_site2->query("SELECT * FROM utilisateur WHERE provenance = 'configurateur' AND (dateinscription > '".$year."-".$m."-01 00:00:00' AND dateinscription <= '".$year."-".$m."-".$d." 23:59:59') ");
	while($user = $DB_site2->fetch_array($req)){
		if($user['mail'] != "")
			if(domain_exists($user['mail'])){
				//
			}else{
				$value += 1;
			}
	}
	$total += $value;
	$count += $DB_site2->num_rows($req);

	print "<tr>
		<td>".$month[$mois]."</td>
		<td>".$DB_site2->num_rows($req)."</td>
		<td ".($value > 0 ? "style='color:red'":'').">".$value."</td>
	</tr>";
}

print "<tr>
		<td>Total</td>
		<td>".$count."</td>
		<td>".$total."</td>
	</tr>
</table>";

print "<br><em style=\"font-size: 10px;\">* A titre indicatif, puisqu'on ne teste que le <br>nom de domaine de l'adresse mail</em></p>";

function domain_exists($email, $record = 'MX'){
	list($user,$domain) = explode('@',$email);
	return checkdnsrr($domain,$record);
}
