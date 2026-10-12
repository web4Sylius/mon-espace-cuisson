<?php 
$ip_allow = array('127.0.0.1','213.44.169.37','80.12.82.212','146.59.188.71','134.214.83.4','51.77.27.253');
 // lecture de l'ip en cours
 $ip = isset($_SERVER['REMOTE_ADDR']) ? trim($_SERVER['REMOTE_ADDR']) : '';

 if ( array_search($ip, $ip_allow) === FALSE ){
 	header('Location: https://www.mon-espace-cuisson.com/');
 	exit;
 }

include 'global.php';

$etatfacture = array(0 => "Non traitée", 1 => "Validée", 2 => "Annnulée", 3 => "Refusée", 4 => "Abandon", 5 => "Expédié", 6 => "Remboursé", 7 => "Validée en attente de paiement", 8 => "Expédiée en attente de paiement");
$typelitige = array(0 => "Avarie de transport", 1 => "Panne", 2 => "Erreur de livraison", 3 => "Rétractation", 4 => "Produit manquant", 5 => "Fraude", 6 => "Avarie occulte", 7 => "Colis perdu");

$contactid = mysqli_real_escape_string($DB_site->link_id,$_GET['contact']);
$annuaire = mysqli_real_escape_string($DB_site->link_id,$_GET['annuaire']);
$extension = mysqli_real_escape_string($DB_site->link_id,$_GET['extension']);

$table = "<table>";
$tr="";

if($contactid != ""){

	if($annuaire == "clients mec"){
		//recup tous les projet en cours du client date - num+lien - commercial - budget - delai
		$liste_etatid="2,3,4,7,8";

		$telephones = "SELECT telephone,portable FROM utilisateur WHERE userid=".$contactid;
		$req_telephones = $DB_site->query_first($telephones);

		$sql = "SELECT p.projetid, p.commercial,p.libelle, p.dateline, p.budget, p.delai_cmd, p.echeance_livraison FROM projet AS p INNER JOIN utilisateur u ON u.userid = p.userid WHERE (p.userid = '$contactid' ".($req_telephones['telephone']!==''?" OR u.telephone = '".$req_telephones['telephone']."' OR u.portable = '".$req_telephones['telephone']."'":'').($req_telephones['portable']!==''?" OR u.portable = '".$req_telephones['portable']."' OR u.telephone = '".$req_telephones['portable']."'":'').") AND p.etatid IN ($liste_etatid) GROUP BY p.projetid ORDER BY p.projetid DESC";

		$req = $DB_site->query($sql);
		if($DB_site->num_rows($req)>0){
			while($row = $DB_site->fetch_array($req)){
				$datepjt = date("d/m/Y", $row['dateline']);
				if($row['delai_cmd'] == 0)
					$delai = "sans";
				else{
					$delaijours = intval($row['delai_cmd'])*60*60*24;
					$delai  = intval($row['echeance_livraison'] - $delaijours);
					$delai = date("d/m/Y", $delai);
				}

				$tr .= "<tr><td>$datepjt</td><td>- <a href=\"https://$host/admin/projet.php?projetid=".$row['projetid']."\">PJT".$row['projetid']."</a></td><td>- ".$row['commercial']." - ".$row['budget']."€ - Délai : $delai</td></tr>";
			}
		}else{

			//projet relations
			$sql = "SELECT p.projetid, p.commercial,p.libelle, p.dateline, p.budget, p.delai_cmd, p.echeance_livraison FROM projet AS p INNER JOIN projet_relation AS pr USING(projetid) INNER JOIN utilisateur u ON u.userid = pr.userid WHERE (pr.userid = '$contactid' ".($req_telephones['telephone']!==''?" OR u.telephone = '".$req_telephones['telephone']."' OR u.portable = '".$req_telephones['telephone']."'":'').($req_telephones['portable']!==''?" OR u.portable = '".$req_telephones['portable']."' OR u.telephone = '".$req_telephones['portable']."'":'').") AND p.etatid IN ($liste_etatid) ORDER BY p.projetid DESC";
			$req = $DB_site->query($sql);
			if($DB_site->num_rows($req)>0){
				while($row = $DB_site->fetch_array($req)){
					$datepjt = date("d/m/Y", $row['dateline']);
					if($row['delai_cmd'] == 0)
						$delai = "sans";
					else{
						$delaijours = intval($row['delai_cmd'])*60*60*24;
						$delai  = intval($row['echeance_livraison'] - $delaijours);
						$delai = date("d/m/Y", $delai);
					}
					$tr .= "<tr><td>$datepjt</td><td>- <a href=\"https://$host/admin/projet.php?projetid=".$row['projetid']."\">PJT".$row['projetid']."</a></td><td>- ".$row['commercial']." - ".$row['budget']."€ - Délai : $delai</td></tr>";
				}
			}
			else{ // si aucun projet, on prend le dernier projet clos perdu
				$sql_clos = "SELECT p.projetid, p.commercial,p.libelle, p.dateline, p.budget, p.delai_cmd, p.echeance_livraison FROM projet AS p INNER JOIN utilisateur u ON u.userid = p.userid WHERE (p.userid = '$contactid' ".($req_telephones['telephone']!==''?" OR u.telephone = '".$req_telephones['telephone']."' OR u.portable = '".$req_telephones['telephone']."'":'').($req_telephones['portable']!==''?" OR u.portable = '".$req_telephones['portable']."' OR u.telephone = '".$req_telephones['portable']."'":'').") AND p.etatid IN (6) ORDER BY p.projetid DESC LIMIT 1";
				$req_clos = $DB_site->query_first($sql_clos);
				if($DB_site->num_rows($req_clos)>0 && !is_null($req_clos)){
					$datepjt = date("d/m/Y", $req_clos['dateline']);
					if($req_clos['delai_cmd'] == 0)
						$delai = "sans";
					else{
						$delaijours = intval($req_clos['delai_cmd'])*60*60*24;
						$delai  = intval($req_clos['echeance_livraison'] - $delaijours);
						$delai = date("d/m/Y", $delai);
					}
					$tr .= "<tr><td>$datepjt</td><td>- <a href=\"https://$host/admin/projet.php?projetid=".$req_clos['projetid']."\">PJT".$req_clos['projetid']." (perdu)</a></td><td>- ".$req_clos['commercial']." - ".$req_clos['budget']."€ - Délai : $delai</td></tr>";
				}
				else{ // si aucun projet, on prend le dernier projet clos perdu des relations
					$sql_clos = "SELECT p.projetid, p.commercial,p.libelle, p.dateline, p.budget, p.delai_cmd, p.echeance_livraison FROM projet AS p INNER JOIN projet_relation AS pr USING(projetid) INNER JOIN utilisateur u ON u.userid = pr.userid WHERE (pr.userid = '$contactid' ".($req_telephones['telephone']!==''?" OR u.telephone = '".$req_telephones['telephone']."' OR u.portable = '".$req_telephones['telephone']."'":'').($req_telephones['portable']!==''?" OR u.portable = '".$req_telephones['portable']."' OR u.telephone = '".$req_telephones['portable']."'":'').") AND p.etatid IN (6) ORDER BY p.projetid DESC";

					$req_clos = $DB_site->query($sql_clos);
					if($DB_site->num_rows($req_clos)>0 && !is_null($req_clos)){
						$datepjt = date("d/m/Y", $req_clos['dateline']);
						if($req_clos['delai_cmd'] == 0)
							$delai = "sans";
						else{
							$delaijours = intval($req_clos['delai_cmd'])*60*60*24;
							$delai  = intval($req_clos['echeance_livraison'] - $delaijours);
							$delai = date("d/m/Y", $delai);
						}
						$tr .= "<tr><td>$datepjt</td><td>- <a href=\"https://$host/admin/projet.php?projetid=".$req_clos['projetid']."\">PJT".$req_clos['projetid']." (perdu)</a></td><td>- ".$req_clos['commercial']." - ".$req_clos['budget']."€ - Délai : $delai</td></tr>";
					}
				}
			}
		}
		

		//dernière commande date - num+lien - commercial - montant commande - etat
		$liste_etatid="1,5,7,8"; 
		$liste_moyenid="2,5,14,17,99"; 
		$sql = "SELECT facturid, heurefacture, f.commercial, totalttc, valide FROM facture f INNER JOIN utilisateur u ON u.userid = f.userid WHERE (f.userid = '$contactid' ".($req_telephones['telephone']!==''?" OR u.telephone = '".$req_telephones['telephone']."' OR u.portable = '".$req_telephones['telephone']."'":'').($req_telephones['portable']!==''?" OR u.portable = '".$req_telephones['portable']."' OR u.telephone = '".$req_telephones['portable']."'":'').") AND f.valide IN ($liste_etatid)";
		$sql2 = "SELECT facturid, heurefacture, f.commercial, totalttc, valide FROM facture f INNER JOIN utilisateur u ON u.userid = f.userid WHERE (f.userid = '$contactid' ".($req_telephones['telephone']!==''?" OR u.telephone = '".$req_telephones['telephone']."' OR u.portable = '".$req_telephones['telephone']."'":'').($req_telephones['portable']!==''?" OR u.portable = '".$req_telephones['portable']."' OR u.telephone = '".$req_telephones['portable']."'":'').") AND f.valide =0 AND f.moyenid IN($liste_moyenid) ORDER BY facturid DESC LIMIT 1";
		$sql = $sql." UNION ".$sql2;
		$req = $DB_site->query($sql);
	
               if($DB_site->num_rows($req)>0){
               	while($cmd = $DB_site->fetch_array($req)){
               		if($cmd['facturid'] != ""){
               			$datecmd = date("d/m/Y", $cmd['heurefacture']);
               			$tr .= "<tr><td>$datecmd</td><td>- <a href=\"https://$host/admin/facture.php?action=editer&facturid=".$cmd['facturid']."\">CDE".$cmd['facturid']."</a></td><td>- ".$cmd['commercial']." - ".$cmd['totalttc']."€ - ".$etatfacture[intval($cmd['valide'])]."</td></tr>";
               		}
               	}
               }
		

		//dernier SAV date - num+lien - montant - typesav
		$sql = "SELECT sav.date, sav.id, sav.utilisateur, f.facturid, sf.value FROM sav INNER JOIN facture f USING(facturid) INNER JOIN sav_fields sf ON sav.id=sf.savid INNER JOIN utilisateur u ON u.userid = f.userid WHERE (f.userid = '$contactid' ".($req_telephones['telephone']!==''?" OR u.telephone = '".$req_telephones['telephone']."' OR u.portable = '".$req_telephones['telephone']."'":'').($req_telephones['portable']!==''?" OR u.portable = '".$req_telephones['portable']."' OR u.telephone = '".$req_telephones['portable']."'":'').") AND sav.fin = 0 AND sf.name='sav_typelitige' ORDER BY sav.id DESC";
	
		$req = $DB_site->query($sql);
		if($DB_site->num_rows($req)>0){
			while ($row = $DB_site->fetch_array($req)) {
				$datesav = date("d/m/Y", $row['date']);
				$tr .= "<tr><td>$datesav</td><td>- <a href=\"https://$host/admin/facture.php?action=editer&facturid=".$row['facturid']."\">SAV".$row['facturid']."-".$row['id']."</a></td><td>- ".$typelitige[$row['value']]."</td></tr>";
			}
		}
	}elseif($annuaire == "assoc mec"){
		//le contactid correspond au userid du client principal du projet
		$telephones = "SELECT telephone,portable FROM utilisateur WHERE userid=".$contactid;
		$req_telephones = $DB_site->query_first($telephones);

		$liste_etatid="2,3,4,7,8";
		$sql = "SELECT p.projetid, p.commercial,p.libelle, p.dateline, p.budget, p.delai_cmd, p.echeance_livraison FROM projet AS p INNER JOIN utilisateur u ON u.userid = p.userid WHERE (p.userid = '$contactid' ".($req_telephones['telephone']!==''?" OR u.telephone = '".$req_telephones['telephone']."' OR u.portable = '".$req_telephones['telephone']."'":'').($req_telephones['portable']!==''?" OR u.portable = '".$req_telephones['portable']."' OR u.telephone = '".$req_telephones['portable']."'":'').") AND p.etatid IN ($liste_etatid) ORDER BY p.projetid DESC";

		$req = $DB_site->query($sql);
		if($DB_site->num_rows($req)>0){
			while($row = $DB_site->fetch_array($req)){
				$datepjt = date("d/m/Y", $row['dateline']);
				if($row['delai_cmd'] == 0)
					$delai = "sans";
				else{
					$delaijours = intval($row['delai_cmd'])*60*60*24;
					$delai  = intval($row['echeance_livraison'] - $delaijours);
					$delai = date("d/m/Y", $delai);
				}

				$tr .= "<tr><td>$datepjt</td><td>- <a href=\"https://$host/admin/projet.php?projetid=".$row['projetid']."\">PJT".$row['projetid']."</a></td><td>- ".$row['commercial']." - ".$row['budget']."€ - Délai : $delai</td></tr>";
			}
		}
		else{ // si aucun projet, on prend le dernier projet clos perdu
			$telephones = "SELECT telephone,portable FROM utilisateur WHERE userid=".$contactid;
			$req_telephones = $DB_site->query_first($telephones);

			$sql_clos = "SELECT p.projetid, p.commercial,p.libelle, p.dateline, p.budget, p.delai_cmd, p.echeance_livraison FROM projet AS p INNER JOIN utilisateur u ON u.userid = p.userid WHERE (p.userid = '$contactid' ".($req_telephones['telephone']!==''?" OR u.telephone = '".$req_telephones['telephone']."' OR u.portable = '".$req_telephones['telephone']."'":'').($req_telephones['portable']!==''?" OR u.portable = '".$req_telephones['portable']."' OR u.telephone = '".$req_telephones['portable']."'":'').") AND p.etatid IN (6) ORDER BY p.projetid DESC LIMIT 1";

			$req_clos = $DB_site->query_first($sql_clos);
			if(!is_null($req_clos)){
				$datepjt = date("d/m/Y", $req_clos['dateline']);
				if($req_clos['delai_cmd'] == 0)
					$delai = "sans";
				else{
					$delaijours = intval($req_clos['delai_cmd'])*60*60*24;
					$delai  = intval($req_clos['echeance_livraison'] - $delaijours);
					$delai = date("d/m/Y", $delai);
				}
				$tr .= "<tr><td>$datepjt</td><td>- <a href=\"https://$host/admin/projet.php?projetid=".$req_clos['projetid']."\">PJT".$req_clos['projetid']." (perdu)</a></td><td>- ".$req_clos['commercial']." - ".$req_clos['budget']."€ - Délai : $delai</td></tr>";
			}
		}
	} 

}


$table .= $tr."</table>";
				

echo $table;	
