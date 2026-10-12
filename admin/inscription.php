<?php

ini_set("display_errors","1");
error_reporting(E_ALL);

require "admin_global.php";
include "./header.php";
include "./menu.php";

if($_GET['conseils']!=""){
	$sqlconseil=" AND conseils = '".$_GET['conseils']."' ";
	if($_GET['conseils']=="oui"){
		$conseiloui="selected";
	}else{
		$conseiloui="";
	}
	if($_GET['conseils']=="non"){
		$conseilnon="selected";
	}else{
		$conseilnon="";
	}
} else {
	$sqlconseil="";
}
if($_GET['allowemail']!=""){
	$sqlallowemail=" AND allowemail = '".$_GET['allowemail']."' ";
	if($_GET['allowemail']=="1"){
		$allowemailoui="selected";
	}else{
		$allowemailoui="";
	}
	if($_GET['allowemail']=="0"){
		$allowemailnon="selected";
	}else{
		$allowemailnon="";
	}
} else {
	$sqlallowemail="";
}

if($_GET['dateinscription']!=""){
	$dateinscription=$_GET['dateinscription'];
} else {
	$dateinscription=date("Y-m-d");
}

echo "<h1>inscription</h1>
<div id=\"formurecherche\" class=\"commande_detail\">
	<div style=\"padding: 10px;\">
		<form name=\"srcform\" action=\"inscription.php\" method=\"get\" style=\"display:inline;\">
			<input type=\"hidden\" name=\"action\" value=\"search\">
			<table>
				<tr>
					<td>
						 &nbsp; Newsletter :
						<select name=\"allowemail\" style=\"width: 75px;\">
							<option value=\"\">Tous</option>
							<option value=\"1\" ".$allowemailoui.">Oui</option>
							<option value=\"0\" ".$allowemailnon.">Non</option>
						</select> &nbsp; 
					</td>
					<td>
						 &nbsp; Conseil :
						<select name=\"conseils\" style=\"width: 75px;\">
							<option value=\"\">Tous</option>
							<option value=\"oui\" ".$conseiloui.">Oui</option>
							<option value=\"non\" ".$conseilnon.">Non</option>
						</select> &nbsp; 
					</td>
					<td>
						 &nbsp; Inscrit depuis :
						<input type=\"date\" name=\"dateinscription\" value=\"".$dateinscription."\"> &nbsp; 
					</td>
					<td>
						<input type=\"submit\" value=\"Go\">
					</td>
				</tr>
			</table>
		</form>
	</div>
</div>
<div class=\"clear\"></div>
Ceci liste contient uniquement les utilisateurs qui se sont inscrits sur le site (ne contient pas les inscrits de MP et les inscrit par un commercial)<br>
<div class=\"commande_detail\">
<table class=\"table_modules_boutiques no_border liste_clients\">
	<tr>
		<td class=\"entete\" align=\"center\">
			<b>Inscription</b>
		</td>
		<td class=\"entete\" align=\"center\">
			<b>N°</b>
		</td>
		<td class=\"entete\" align=\"center\">
			<b>Société</b>
		</td>
		<td class=\"entete\" align=\"center\">
			<b>Nom</b>
		</td>
		<td class=\"entete\" align=\"center\">
			<b>Conseils</b>
		</td>
		<td class=\"entete\" align=\"center\">
			<b>Newsletter</b>
		</td>
		<td class=\"entete\" align=\"center\">
			<b>Fiche</b>
		</td>
	</tr>";

if($_GET['action']=="search"){
	
	$sql="SELECT * FROM utilisateur
		WHERE 1
		".$sqlconseil."
		".$sqlallowemail."
		AND cast(dateinscription as date) >= '".$_GET['dateinscription']."'
		AND provenance = 'site'
		ORDER BY userid DESC ";
	// echo $sql;
	$cnt = 1;
	$inscrits=$DB_site2->query($sql);
	while($inscrit=$DB_site2->fetch_array($inscrits)){
		list($date_inscription, $horaire_inscription) = explode(' ', $inscrit['dateinscription']);
		list($annee, $mois, $jour) = explode( "-", $date_inscription );
		list($heure, $minute, $seconde) = explode( ":", $horaire_inscription );
		
		$class_td = $cnt%2 == 0 ? 'td_alt2' : 'td_alt1';
		
		if($inscrit['allowemail']==0){
			$allowemail="non";
		} else {
			$allowemail="oui";
		}
		
		// echo "=> ".$inscrit['mail']." <br>";
		?>
		<tr>
			<td class="<?php echo $class_td; ?>" align="center"><?php echo $jour; ?>/<?php echo $mois; ?>/<?php echo $annee; ?><br><?php echo $heure; ?>h<?php echo $minute; ?></td>
			<td class="<?php echo $class_td; ?>" align="center">
				<?php echo $inscrit[userid]; ?>
			</td>
			<td class="<?php echo $class_td; ?>" align="center">
				<?php echo $inscrit['raisonsociale']; ?>
			</td>
			<td class="<?php echo $class_td; ?>" align="center">
				<?php echo $inscrit['nom'].' '.$inscrit['prenom']; ?>
			</td>
			<td class="<?php echo $class_td; ?>" align="center">
				<?php echo $inscrit['conseils']; ?>
			</td>
			<td class="<?php echo $class_td; ?>" align="center">
				<?php echo $allowemail; ?>
			</td>
			<td class="<?php echo $class_td; ?>" align="center">
				<a href="utilisateur.php?action=editer&userid=<?php echo $inscrit[userid]; ?>"><img src="images/icon/edit.png"></a>
			</td>
		</tr>
		<?php
		$cnt++;
	}
}

echo "</table>
</div>";


include "./footer.php";
?>