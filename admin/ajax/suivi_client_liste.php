<?php
require_once($_SERVER['DOCUMENT_ROOT']."/admin/admin_global.php");

$contenu = false;

if(!isset($_GET['txt']) || empty($_GET['txt'])) exit;
if(!isset($_GET['userid']) || empty($_GET['userid'])) exit;
if(!isset($_GET['type_suivi']) || empty($_GET['type_suivi'])) exit;

$selectContent = "<div style=\"position: absolute; right: -2px; top: -19px; background-color: #363E42; color: white; font-weight: bold; padding: 2px 5px; cursor: pointer;\" onclick=\"hideAssoc(".$_GET['suiviid'].");\"><img src=\"".BACK_URL."/images/icon/erreur.png\" style=\"width: 15px; vertical-align: -3px;\"> Fermer</div>";

$recherche = $_GET['txt'];

//Récupération des commandes déjà associées, pour ne pas proposer à nouveau l'association
$sql = "SELECT u.userid, u.raisonsociale, u.prenom, u.nom, DATE_FORMAT(u.dateinscription, '%d/%m/%Y') as date_inscription";
$sql .= " FROM utilisateur u";
$sql .= " WHERE 1 ";
if(is_numeric($recherche)){
	$sql .= "AND u.userid = ".$recherche;
} else {
	$sql .= " AND (u.nom LIKE '%".$recherche."%'";
	$sql .= " OR u.prenom LIKE '%".$recherche."%'";
	$sql .= " OR u.raisonsociale LIKE '%".$recherche."%')";
}
$sql .= " ORDER BY userid DESC ";
$req = $DB_site2->query($sql);

$cpt = 1;
while($row = $DB_site2->fetch_array($req)){
	$contenu = true;
	$selectContent .= ""
	. "<div class=\"resultat_recherche\" ".($cpt == $DB_site2->num_rows($req) ? 'style="border-bottom: 1px solid;"' : '')." onclick=\"assocSuivi(".$_GET['userid'].", ".$row['userid'].", ".$_GET['suiviid'].", '".$_GET['type_suivi']."', 'client')\">"
		. "<div class=\"round_content\">"
			. "<p>"
				. "<span style=\"font-weight: bold; font-size: 12px;\">Client n°".$row['userid']."</span> <span style=\"font-size: 10px;\">inscrit le ".$row['date_inscription']."</span>"
				. "<br>"
				. ($row['raisonsociale'] != '' ? $row['raisonsociale'].'<br>' : '')
				. $row['prenom']." ".$row['nom']
			. "</p>"
		. "</div>"
	. "</div>";

	$cpt++;
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