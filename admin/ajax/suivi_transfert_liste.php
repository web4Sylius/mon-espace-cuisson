<?php
require_once($_SERVER['DOCUMENT_ROOT']."/admin/admin_global.php");

if(!isset($_GET['type']) || empty($_GET['type'])) exit;
if(!isset($_GET['userid']) && !isset($_GET['facturid'])) exit;
if(!isset($_GET['suiviid']) || empty($_GET['suiviid'])) exit;
if(!isset($_GET['type_suivi']) || empty($_GET['type_suivi'])) exit;
$userid = mysqli_real_escape_string($DB_site2->link_id,$_GET['userid']);

$aEtat = array(
	1 => array(
	 	'statut'  => 'VALIDEE',
	 	'couleur' => 'blue'
	),
	5 => array(
	 	'statut'  => 'EXPEDIEE',
	 	'couleur' => 'green'
	),
	7 => array(
	 	'statut'  => 'VALIDEE (SP)',
	 	'couleur' => 'blue'
	),
	8 => array(
	 	'statut'  => 'EXPEDIEE (SP)',
	 	'couleur' => 'green'
	),
);

$aCivilite = array(
	0 => 'Mr',
	1 => 'Mme',
	2 => 'Mlle'
);

$contenu = false;

$selectContent = "<div style=\"position: absolute; right: -2px; top: -19px; background-color: #363E42; color: white; font-weight: bold; padding: 2px 5px; cursor: pointer;\" onclick=\"hideAssoc(".$_GET['suiviid'].");\"><img src=\"".BACK_URL."/images/icon/erreur.png\" style=\"width: 15px; vertical-align: -3px;\"> Fermer</div>";
if($_GET['type_suivi'] != 'suiviint'){
	switch($_GET['type']){

		case 'cmd':

			//Récupération des commandes déjà associées, pour ne pas proposer à nouveau l'association
			$sql = "SELECT f.*, DATE_FORMAT(f.datefacture, '%d/%m/%Y') as date_commande, f.civilite as livraison_civilite, f.nom as livraison_nom, f.prenom as livraison_prenom, u.civilite as facturation_civilite, u.nom as facturation_nom, u.prenom as facturation_prenom";
			$sql .= " FROM facture f";
			$sql .= " INNER JOIN utilisateur u ON (u.userid = f.userid)";
			$sql .= " WHERE 1";
			if(isset($_GET['userid']))
	                    $sql .= " AND f.userid = ".$_GET['userid'];
	                if(isset($_GET['facturid']))
	                    $sql.=" AND f.facturid=".$_GET['facturid'];
			$sql .= " AND (f.valide IN (1,5,7,8) OR (f.valide=0 AND f.moyenid IN (2,4,5,14,99)))";
			$sql .= " AND f.facturid NOT IN (
							SELECT copie_id 
							FROM suivi_duplication 
							WHERE suivi_copie_type = 'cmd' 
							AND suivi_origine_id = ".$_GET['suiviid']." 
							AND suivi_origine_type = 'client'
					)";
			$req = $DB_site2->query($sql);

			$cpt = 1;
			while($row = $DB_site2->fetch_array($req)){
				$contenu = true;
				//Récupération des produits de la commande
				$sqlPdt = "SELECT *";
				$sqlPdt .= " FROM lignefacture";
				$sqlPdt .= " WHERE factureid = ".$row['facturid'];
				$reqPdt = $DB_site2->query($sqlPdt);
				if($DB_site2->num_rows($reqPdt)){
					$selectContentPdt = "<div style=\"background-color: #c0c0c0;\">";
					$nb=0;
					while($ligne = $DB_site2->fetch_array($reqPdt)){
						if($nb>0) $selectContentPdt .= "<br>";
						$selectContentPdt .= $ligne['qte'].' x '.$ligne['artcode'];
						$nb++;
					}
					$selectContentPdt .= "</div>";
				}

				$selectContent .= ""
				. "<div class=\"resultat_recherche\" ".($cpt == $DB_site2->num_rows($req) ? 'style="border-bottom: 1px solid;"' : '')." onclick=\"lierMail(".intval($_GET['userid']).", ".$row['facturid'].", ".$_GET['suiviid'].", '".$_GET['type_suivi']."', 'commande','')\">"
					. "<div class=\"round_content\" style=\"background-color: #363E42;  color: white;\">"
						. "<p>"
							. "<span style=\"font-weight: bold; font-size: 12px;\">Commande n°".$row['facturid']."</span> <span style=\"font-size: 10px;\">du ".$row['date_commande']."</span>"
						. "</p>"
					. "</div>"
					. "<div class=\"round_content\" style=\"background-color: ".$aEtat[$row['valide']]['couleur'].";  color: white; padding: 2px 5px;\">"
						. "<span style=\"font-weight: bold; font-size: 10px; color: white;\">".$aEtat[$row['valide']]['statut']."</span>"
					. "</div>"
					. "<div class=\"facturation\">"
						. "<p>"
							. "<span style=\"font-weight: bold; font-size: 12px;\">Facturation</span>"
							. "<br>"
							. "<span style=\"font-size: 10px;\">".$aCivilite[$row['facturation_civilite']]." ".$row['facturation_prenom']." ".$row['facturation_nom']."</span>"
						. "</p>"
					. "</div>"
					. "<div class=\"livraison\">"
						. "<p>"
							. "<span style=\"font-weight: bold; font-size: 12px;\">Livraison</span>"
							. "<br>"
							. "<span style=\"font-size: 10px;\">".$aCivilite[$row['livraison_civilite']]." ".$row['livraison_prenom']." ".$row['livraison_nom']."</span>"
						. "</p>"
					. "</div>"
					. $selectContentPdt
				. "</div>";

				$cpt++;
			}

			break;
		case 'fourn':
			break;

		case 'sav':

			$selectContent = "<option value=\"\"></option>";
			$sql = "SELECT sav.id FROM sav INNER JOIN facture ON sav.facturid=facture.facturid WHERE facture.userid ='$userid'";
			$savs = $DB_site2->query($sql);
			while ($sav = $DB_site2->fetch_array($savs)) {
				$contenu = true;
				$selectContent .="<option value=\"".$sav['id']."\">$sav[id]</option>";
			}
			
			break;		
	}
}else{
	switch($_GET['type']){
		case 'sav':
			$selectContent = "<option value=\"\"></option>";
			$sql = "SELECT id FROM sav WHERE facturid ='$userid'";
			$savs = $DB_site2->query($sql);
			while ($sav = $DB_site2->fetch_array($savs)) {
				$contenu = true;
				$selectContent .="<option value=\"".$sav['id']."\">$sav[id]</option>";
			}
		break;
	}
}

if($contenu){
	?>
	<script type="text/javascript">
		var $jQuery = jQuery.noConflict();

		$jQuery(document).ready(function() {
			$jQuery('.resultat_recherche').each(function(){

				var Elem = $jQuery(this);

				Elem.mouseenter(function(){
					Elem.css('border','2px solid red');
				});

				Elem.mouseleave(function(){
					Elem.css('border','1px solid');
				});
			});
		});
	</script>
	<?php
	echo $selectContent;	
} else {
	echo "vide";
}