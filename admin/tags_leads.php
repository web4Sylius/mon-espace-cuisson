<?php
/* Tâche 6656, pour Pauline marketing */
include("admin_global.php");


include "./header.php";
include "./menu.php";

$DB_site = $DB_site2;

if($_POST['action'] == 'tags_leads') {
	$DB_site->query('UPDATE leads_utm SET utm_source = "'.mysqli_real_escape_string($DB_site->link_id,htmlentities($_POST['utm_source'])).'", utm_medium = "'.mysqli_real_escape_string($DB_site->link_id,htmlentities($_POST['utm_medium'])).'", utm_campagne = "'.mysqli_real_escape_string($DB_site->link_id,htmlentities($_POST['utm_campagne'])).'" WHERE id ='.$_POST['utm_id']);
}

if($_POST['action'] == 'add_tag') {
		$sql = "INSERT INTO leads_utm SET";
		$sql .= " utm_source 			 = '".mysqli_real_escape_string($DB_site->link_id,htmlentities($_POST['utm_source']))."'";
		$sql .= ", utm_medium 			 = '".mysqli_real_escape_string($DB_site->link_id,htmlentities($_POST['utm_medium']))."'";
		$sql .= ", utm_campagne 		 = '".mysqli_real_escape_string($DB_site->link_id,htmlentities($_POST['utm_campagne']))."'";
		$DB_site->query($sql);
}


$table = "<table class=\"tags\">
<tr>
<th>Nom :</th>
<th>Moyen :</th>
<th>Campagne :</th>
<th style=\"text-align:center;\"><img src=\"images/icon/edit_add_22.png\" title=\"ajouter un tag\" onclick=\"add_tag()\"></th>
</tr>";

$liste=$DB_site->query("SELECT * FROM leads_utm");
foreach($liste as $tag) {
	$table .= "<tr id=\"tagid_".$tag['id']."\">
	<td id=\"utm1\">".$tag['utm_source']."</td>
	<td id=\"utm2\">".$tag['utm_medium']."</td>
	<td id=\"utm3\">".$tag['utm_campagne']."</td>
	<td id=\"utm4\"><img src=\"images/icon/edit.png\" title=\"modifier un tag\" onclick=\"edit_tag(".$tag['id'].")\"></td>
	</tr>";
}

$table .= "</table></div>";
$popup = "<div class=\"flex_lead\"><div>
<b>Administration des tags pour la réception de leads sur la page formulaire-contact.php</b>
<form method=\"post\" action=\"tags_leads.php\" class=\"form_add_tag\">
<input type=\"hidden\" name=\"action\" value=\"add_tag\">
<label>Utm Source: </label>
<input type=\"text\" name=\"utm_source\" placeholder=\"Manageo, Linkedin etc...\">
<label>Utm Moyen: </label>
<input type=\"text\" name=\"utm_medium\" placeholder=\"lien, etc...\">
<label>Utm Campagne: </label>
<input type=\"text\" name=\"utm_campagne\" placeholder=\"campagne_Adcom, etc...\">
<br><input type=\"submit\" class=\"valid_tag\" value=\"Ajouter un tag\">
</form>";

$contacts=$DB_site->query("SELECT COUNT(cl.utm_source) as 'nb', cl.utm_source, lu.utm_campagne, lu.utm_medium FROM leads_utm lu INNER JOIN contact_leads cl ON cl.utm_source = lu.id GROUP BY lu.utm_campagne");
foreach($contacts as $stat) {
	$stat1 .= "<tr><td>".$stat['utm_campagne']."</td><td>".$stat['nb']."</td></tr>";
}
$contacts2=$DB_site->query("SELECT COUNT(cl.utm_source) as 'nb', lu.utm_campagne, lu.utm_source, lu.utm_medium FROM leads_utm lu INNER JOIN contact_leads cl ON cl.utm_source = lu.id GROUP BY lu.utm_source");
foreach($contacts2 as $stat) {
	$stat2 .= "<tr><td>".$stat['utm_source']."</td><td>".$stat['nb']."</td></tr>";
}
$contacts3=$DB_site->query("SELECT COUNT(cl.utm_source) as 'nb', lu.utm_campagne, lu.utm_source, lu.utm_medium FROM leads_utm lu INNER JOIN contact_leads cl ON cl.utm_source = lu.id GROUP BY lu.utm_medium");
foreach($contacts3 as $stat) {
	$stat3 .= "<tr><td>".$stat['utm_medium']."</td><td>".$stat['nb']."</td></tr>";
}

$stat = "<div style=\"text-align: center;\"><b>Statistiques des leads, nombre de contacts</b><br>
<table class=\"tags\" style=\"width:80%;\">
<tr>
<th>Campagne :</th>
<th>Total :</th>
</tr>".
$stat1
."
<tr>
<th>Source :</th>
<th>Total :</th>
</tr>".$stat2."
<tr>
<th>Moyen :</th>
<th>Total :</th>
</tr>".$stat3."
</table></div></div>";

echo $popup . $table . $stat;


include "./footer.php";

?>