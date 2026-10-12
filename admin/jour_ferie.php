<?php

ini_set("display_errors","1");
error_reporting(E_ALL);

require "admin_global.php";
include "./header.php";
include "./menu.php";


if($_GET['action']=="delete"){
	$DB_site2->query("DELETE FROM jour_ferie WHERE jour_ferieid='".$_GET['jour_ferieid']."' ");
}
if($_POST['action']=="add"){
	$explode = explode("/", $_POST['dateline']);
	$dateline_us=$explode[2].'-'.$explode[1].'-'.$explode[0];
	$DB_site2->query("INSERT IGNORE INTO jour_ferie (dateline) VALUES ('".$dateline_us."') ");
}




echo "<h1>Ajouter un jour férié</h1>
	<center>
		<form method=\"post\" action=\"jour_ferie.php\">
			<input type=\"hidden\" name=\"action\" value=\"add\">
			<table align=\"center\" width=\"50%\">
				<tr>
					<td align=\"center\">
						<b>Jour :</b> <input type=\"text\" name=\"dateline\" id=\"dateline\" size=\"10\" value=\"\" onclick=\"calendrier_show('dateline',this.value);\" readonly>
					</td>
					<td>
						<input type=\"submit\" class=\"skinned\" value=\"Envoyer\">
					</td>
				</tr>
			</table>
		</form>
	</center>";


$jour_feries=$DB_site2->query("SELECT * FROM jour_ferie ORDER BY dateline ASC ");


echo "<h1>Liste des jours fériés</h1>
	<center>
		<table align=\"center\" width=\"50%\">
			<tr>
				<td align=\"center\">
					Jour
				</td>
				<td>
					Action
				</td>
			</tr>";
while($jour_ferie=$DB_site2->fetch_array($jour_feries)){
	$explode = explode("-", $jour_ferie[dateline]);
	$dateline_fr=$explode[2].'/'.$explode[1].'/'.$explode[0];
	echo "<tr>
			<td align=\"center\">".$dateline_fr."</td>
			<td><a href=\"jour_ferie.php?action=delete&jour_ferieid=".$jour_ferie[jour_ferieid]."\">supprimer</a></td>
		</tr>";
}
			
echo "	</table>
	</center>";
	
	
	

echo "<br>";

include "./footer.php";
?>


