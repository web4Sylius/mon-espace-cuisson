<?php

include_once('./admin_global.php');
include_once(SITE_PATH."/class/facture.class.php");
include("export/xlexport.php");

if(!isset($_POST['search']) || $_POST['datedebut'] == "" || $_POST['datefin'] == ""){
	include ("./header.php");
	include ("./menu.php");	
	?>
	<h1>Taxe Ecologique</h1>

	<form action="#" method="post" target="_blank">
		<input type="hidden" name="search" value="1">
		<p>Date début : <input type="text" id="datedebut" name="datedebut" onclick="calendrier_show(this.id,this.value)" autocomplete="off"> Date fin : <input type="text" id="datefin" name="datefin" onclick="calendrier_show(this.id,this.value)" autocomplete="off"> &nbsp;&nbsp;&nbsp;&nbsp; <input type="submit" value="Valider"></p>

	</form>
<?php
}

$marqueids = array(35,36,24,42,55);
$data=array();

$cols = array("Date facture","Numero facture","sct client facturation","nom client facturation","pays facturation
	","sct client livraison","nom client livraison","pays livraison","Marque","Libelle","Type article","ref Fabriquant","ref MEC","poids net unit","ecopart HT unit","qte","gaz");
$data[]=$cols;
if(isset($_POST['search'])){

	if(isset($_POST['datedebut']) && $_POST['datedebut'] != "" && isset($_POST['datefin']) && $_POST['datefin'] != ""){
		//gestion de la date
		$datedebut = DateTime::createFromFormat('d/m/Y', mysqli_real_escape_string($DB_site2->link_id, $_POST['datedebut']))->format('Y-m-d');
		$datefin = DateTime::createFromFormat('d/m/Y', mysqli_real_escape_string($DB_site2->link_id, $_POST['datefin']))->format('Y-m-d');

		$sql = "SELECT f.facturid FROM facture AS f";
		$sql.= " WHERE f.valide IN(5,8) AND f.dateexpedition BETWEEN '".$datedebut."' AND '".$datefin."'";
		$sql.= " ORDER BY f.dateexpedition ASC ";
		$result = $DB_site2->query($sql);

		while($facture = $DB_site2->fetch_array($result)){
			$fact = new facture($DB_site2,$facture['facturid']);
			$datefacture = date("d/m/Y",$fact->dateexpedition); //date expédition
			$nomclientlivr = $fact->nom." ".$fact->prenom;
			$nomclientfact = $fact->utilisateur->nom." ".$fact->utilisateur->prenom;
		
			foreach ($fact->lignes as $ligne) {
				if(in_array($ligne->marqueid,$marqueids)){
					$row=array($datefacture,$fact->numerofacture,utf8_encode($fact->utilisateur->raisonsociale),$nomclientfact,$fact->utilisateur->pays,utf8_encode($fact->raisonsociale),$nomclientlivr,$fact->pays,$ligne->marque,$ligne->libelle,$ligne->libelle_typearticle(),utf8_encode($ligne->ref),$ligne->artcode,$ligne->emballage_poids,$ligne->ecopartht,$ligne->qte,$ligne->gaz($template,$ligne->gaz));
					$data[]=$row;
				}
			}
		}
		export_data_to_csv($data,'export_test');
	}else{
		print "<p>Veuillez choisir une Date de début et une Date de fin !!</p>";
	}

}

function export_data_to_csv($data,$filename='export',$delimiter = ';',$enclosure = '"') {
	// Dit au navigateur que les données retournées sont un fichier qui s'appelle $filename.csv
	header("Content-disposition: attachment; filename=$filename.csv");
	// Dit au navigateur que les données retournées sont un fichier csv.
	header("Content-Type: text/csv");
	$fp = fopen("php://output", 'w');
	fputs($fp, $bom =( chr(0xEF) . chr(0xBB) . chr(0xBF) ));
	//fputcsv($fp,array_keys($data[0]),$delimiter,$enclosure);
	foreach ($data as $fields) {
		fputcsv($fp, $fields,$delimiter,$enclosure);
	}

	fclose($fp);
	die();
}

?>