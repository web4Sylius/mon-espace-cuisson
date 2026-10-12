<?php
require "admin_global.php";

$sql_stat = "SELECT * FROM rgpd";
$valueau=$valuedu="";
if(isset($_POST['action']) && $_POST['action'] == "search"){
	if(isset($_POST['du']) && isset($_POST['au'])){
		$du = new DateTime($_POST['du'].' 00:01:00');
		$au = new DateTime($_POST['au'].' 23:59:00');

		$timestampdu = $du->getTimestamp();
		$timestampau = $au->getTimestamp();
		$valuedu = $du->format('Y-m-d');
		$valueau = $au->format('Y-m-d');

		$sql_stat = "SELECT * FROM rgpd WHERE datec >= '$timestampdu' AND datec <= '$timestampau'";
	}
}

include "./header.php";
include "./menu.php";

$choix =array(0 => 0, 1=>0, 2=>0);
$req = $DB_site2->query($sql_stat);
while($row = $DB_site2->fetch_array($req)){

	switch($row['choix']){
		case '0':
			$choix[0]++;
		break;

		case '1':
			$choix[1]++;
		break;
		
		case '2':
			$choix[2]++;
		break;
	}


}

$nbclique = array_sum($choix);

?>
<h3>Statistiques cliques RGPD</h3>
<form method="post" action="#" name="search">
	<input type="hidden" name="action" value="search">
	<p>Choix de la date : <b>Début</b> <input type="date" id="du" name="du" value="<?php echo $valuedu ?>">à 00h01   <b>Fin</b> <input type="date" id="au" name="au" value="<?php echo $valueau ?>">à 23h59 <input type="submit" value="Valider"></p>
</form>

<?php
$cnt=1;
$table="<table class=\"tab_visit\">";
$td = "<tr><td style=\"text-align:center;\">Acceptation</td><td style=\"text-align:center;\">Refus</td><td style=\"text-align:center;\">Paramètrage</td><td style=\"text-align:center;\">Total</td></tr>";
$ligne ="<tr><td style=\"text-align:center;\">$choix[0]</td><td style=\"text-align:center;\">$choix[1]</td><td style=\"text-align:center;\">$choix[2]</td><td style=\"text-align:center;\">$nbclique</td></tr>";
$table.=$td;
$table.=$ligne;
$table.="</table>";
echo $table;


include "./footer.php";