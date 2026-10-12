<?php
require($_SERVER['DOCUMENT_ROOT'] . "/admin/admin_global.php");
require($rootpath . "admin/export/xlexport.php");

if(!empty($_POST)){
	if(isset($_POST['stat_to_export'])){
		//Préparation du fichier csv pour le GEM
		$csv = new xlexport("csv");
		$csv->name            = "export_".date("Y-m-d-H-i-s");
		$csv->csvsep          = ";";
		$csv->striptags       = false;
		//Formatage des date au format EN pour utilisation en SQL
		$date_debut = implode('-',array_reverse(explode('/',$_POST['date_debut'])));
		$date_fin   = implode('-',array_reverse(explode('/',$_POST['date_fin'])));
		if($_POST['stat_to_export'] == 'cmd_provenance'){
			$sql = "SELECT DATE_FORMAT(f.datefacture,'%d/%m/%Y') as 'Date commande', f.facturid as 'Num facture',
					ROUND((SUM((lf.prix * lf.qte) / (1 + (lf.tva / 100))) + f.prixport / (1 + f.tvaport / 100)) - CASE WHEN (SELECT code_caviste_id FROM code_caviste WHERE code = f.cadeauid) IS NULL THEN (SUM((lf.prix * lf.qte) / (1 + (lf.tva / 100))) + (f.prixport / (1 + f.tvaport / 100))) * (f.montantcadeau / (SUM(lf.prix * lf.qte) + f.prixport)) ELSE 0 END,2) as 'Montant HT',
					CASE f.provenance WHEN 'sav' THEN 'SAV' WHEN 'mp' THEN 'marketplace' WHEN '' THEN f.commercial WHEN 'mobile' THEN f.commercial END as 'Origine',
					u.raisonsociale as 'Societe', u.nom as 'Nom', p.libelle as 'Pays'";
			$sql .= " FROM facture f";
			$sql .= " INNER JOIN lignefacture lf ON (lf.factureid = f.facturid)";
			$sql .= " INNER JOIN utilisateur u ON (u.userid = f.userid)";
			$sql .= " INNER JOIN pays p ON (p.paysid = u.paysid)";
			$sql .= " WHERE datefacture BETWEEN '" . $date_debut . "' AND '" . $date_fin . "'";
			$sql .= " AND f.valide IN (1,5,7,8)";
			$sql .= " GROUP BY f.facturid";
			$sql .= " ORDER BY f.facturid DESC";
			$req = $DB_site2->query($sql);
		} elseif($_POST['stat_to_export'] == 'devis_etranger'){
			$sql = "SELECT f.facturid AS 'Num commande', u.raisonsociale as 'Societe', u.nom as 'Nom',
					u.prenom as 'Prenom', DATE_FORMAT(FROM_UNIXTIME(ed.date),'%d/%m/%Y') as 'Date commande'";
			$sql .= " FROM facture f";
			$sql .= " INNER JOIN envoi_devis ed USING (facturid)";
			$sql .= " INNER JOIN utilisateur u USING (userid)";
			$sql .= " WHERE f.datefacture BETWEEN '" . $date_debut . "' AND '" . $date_fin . "'";
			$sql .= " AND (f.paysid = 81 OR f.paysid = 153 OR f.paysid = 172)";
		}elseif ($_POST['stat_to_export'] == 'ventes_mp'){
			$sql = "SELECT lf.artcode as 'Ref', mp.market_place_nom as 'MarketPlace', COUNT(*) as Qte";
			$sql .= " FROM facture f";
			$sql .= " INNER JOIN lignefacture lf ON (lf.factureid = f.facturid)";
			$sql .= " INNER JOIN market_place mp ON (mp.market_place_site_id = f.provenanceid)";
			$sql .= " WHERE f.valide IN (1,5,7,8)";
			$sql .= " AND f.provenance = 'mp'";
			$sql .= " AND f.datefacture BETWEEN '" . $date_debut . "' AND '" . $date_fin . "'";
			$sql .= " GROUP BY f.provenanceid, lf.artcode";
			$sql .= " ORDER BY lf.artcode";
			//echo $sql;
			//exit;
		}else{
			echo "erreur de parametres";
			exit;
		}
		$req = $DB_site2->query($sql);
		$aExport  = array();
		$aEntetes = array();
		while($row = $DB_site2->fetch_assoc($req)){
			if(array_key_exists ( 'Montant HT' , $row )){
				if($row['Montant HT']==""){ // la commande fait 0 euro (SAV)
					$row['Montant HT']=0;
				}
			}
			if(array_key_exists ( 'Ref' , $row )){
				if($row['Ref']=="produit" || $row['Ref']=="compensation"){ // les facilité de paiement de cdiscount ou les compensation amazon
					continue;
				}
			}
			$aExport[] = $row; //Stockage des résultats dans un tableau
			if($aEntetes == array()){
				foreach($row as $key => $value){
					$aEntetes[] = $key; //Stockage des entêtes de colonne
				}
			}
		}
		if(count($aExport) > 0){
			if($aEntetes != array())
				$csv->addrow($aEntetes); //Insertion des entêtes s'il y en a
			foreach($aExport as $ligne){
				$csv->addrow($ligne); //Insertion de la ligne de résultat
			}
			$csv->export();
		} else {
			include_once(BACK_PATH."/header.php");
			include_once(BACK_PATH."/menu.php");
			echo "aucun résultat à la requête demandée.<br><a href=\"stats_export.php\">Retour</a>";
			include_once(BACK_PATH."/footer.php");
		}
	}
	exit;
}

include_once(BACK_PATH."/header.php");
include_once(BACK_PATH."/menu.php");
?>
<h1>Export statistiques</h1>

<form method="post">
	<label for="stat_export">Statistiques à exporter :</label> 
	<select id="stat_export" name="stat_to_export" onchange="loadNext(this.value);">
		<option value="">-- Sélectionner --</option>
		<option value="cmd_provenance">Commandes HT sur période donnée</option>
		<option value="devis_etranger">Devis pour clients étrangers (UK,ES,IE)</option>
		<option value="ventes_mp">Produits vendus sur market places</option>
	</select>
	<div id="dates_filter" style="display: none;">
		Du <input type="text" name="date_debut" id="date_debut" value="" onclick="calendrier_show('date_debut',this.value);">(inclus)
		au <input type="text" name="date_fin" id="date_fin" value="" onclick="calendrier_show('date_fin',this.value);">(inclus) |
	</div>
	<input type="submit" value="Exporter">
</form>

<script type="text/javascript">
	function loadNext(form){
		if(form == 'cmd_provenance' || form == 'devis_etranger' || form == 'ventes_mp'){
			$jQuery('#dates_filter').css('display','inline-block');
		}
	}
</script>
<?php
include_once(BACK_PATH."/footer.php");
?>