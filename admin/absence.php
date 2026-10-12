<?php

ini_set("display_errors","1");
error_reporting(E_ALL);

require "admin_global.php";
include "./header.php";
include "./menu.php";


if($_GET['action']=="delete"){
	$DB_site2->query("DELETE FROM absence WHERE absenceid='".$_GET['absenceid']."' ");
	echo "<br><b>absence supprimée.</b>";
}
if($_POST['action']=="add"){
	$erreur=0;
	if(!isset($_POST['datedebut']) || $_POST['datedebut']==""){
		echo "date de début invalide.";
		$erreur=1;
	}
	if(!isset($_POST['datefin']) || $_POST['datefin']==""){
		echo "date de fin invalide.";
		$erreur=1;
	}
	if(!isset($_POST['username']) || $_POST['username']==""){
		echo "utilisateur invalide.";
		$erreur=1;
	}
	if(!isset($_POST['absence_raisonid']) || $_POST['absence_raisonid']==""){
		echo "raison invalide.";
		$erreur=1;
	}
	$explode_debut = explode("/", $_POST['datedebut']);
	$datedebut_us=$explode_debut[2].'-'.$explode_debut[1].'-'.$explode_debut[0];
	
	$explode_fin = explode("/", $_POST['datefin']);
	$datefin_us=$explode_fin[2].'-'.$explode_fin[1].'-'.$explode_fin[0];
	
	
	if($erreur==0){
		$DB_site2->query("INSERT INTO absence (username, datedebut, datefin, absence_raisonid)
						VALUES ('".$_POST['username']."', '".$datedebut_us."', '".$datefin_us."', '".$_POST[absence_raisonid]."') ");
		echo "<br><b>absence ajoutée avec succès.</b>";
	} else {
		echo "<br><b>absence non ajoutée.</b>";
	}
}

if($_POST['action']=="domodify"){
	$erreur=0;
	if(!isset($_POST['datedebut']) || $_POST['datedebut']==""){
		echo "date de début invalide.";
		$erreur=1;
	}
	if(!isset($_POST['datefin']) || $_POST['datefin']==""){
		echo "date de fin invalide.";
		$erreur=1;
	}
	if(!isset($_POST['username']) || $_POST['username']==""){
		echo "utilisateur invalide.";
		$erreur=1;
	}
	if(!isset($_POST['absence_raisonid']) || $_POST['absence_raisonid']==""){
		echo "raison invalide.";
		$erreur=1;
	}
	$explode_debut = explode("/", $_POST['datedebut']);
	$datedebut_us=$explode_debut[2].'-'.$explode_debut[1].'-'.$explode_debut[0];
	
	$explode_fin = explode("/", $_POST['datefin']);
	$datefin_us=$explode_fin[2].'-'.$explode_fin[1].'-'.$explode_fin[0];
	
	
	if($erreur==0){
		$DB_site2->query("UPDATE absence SET username='".$_POST['username']."',
							datedebut='".$datedebut_us."',
							datefin='".$datefin_us."',
							absence_raisonid='".$_POST[absence_raisonid]."'
							WHERE absenceid='".$_POST['absenceid']."' ");
		echo "<br><b>absence modifiée avec succès.</b>";
	} else {
		echo "<br><b>absence non modifiée.</b>";
	}
}


if($_GET['action']=="modify"){
	$absence=$DB_site2->query_first("SELECT * FROM absence WHERE absenceid='".$_GET['absenceid']."' ");
	
	$selected="";
	$selectutilisateur="<select name=\"username\"><option value=\"\">-- Sélectionner --</option>";
	$commercials=$DB_site2->query(" SELECT username FROM parametresgeneraux WHERE commissions ='1' ORDER BY username ASC ");
	while($commercial=$DB_site2->fetch_array($commercials)){
		if($absence[username]==$commercial[username]){
			$selected="selected";
		} else {
			$selected="";
		}
		$selectutilisateur.= "<option value=\"".$commercial[username]."\" ".$selected.">".UCfirst($commercial[username])."</option>";
	}
	$selectutilisateur.="</select>";
	
	$selectraison="<select name=\"absence_raisonid\"><option value=\"\">-- Sélectionner --</option>";
	$raisons=$DB_site2->query(" SELECT * FROM absence_raison ORDER BY libelle ASC ");
	while($raison=$DB_site2->fetch_array($raisons)){
		if($absence[absence_raisonid]==$raison[absence_raisonid]){
			$selected="selected";
		} else {
			$selected="";
		}
		$selectraison.= "<option value=\"".$raison[absence_raisonid]."\" ".$selected.">".UCfirst($raison[libelle])."</option>";
	}
	$selectraison.="</select>";

	$explodedebut = explode("-", $absence[datedebut]);
	$datedebut_fr=$explodedebut[2].'/'.$explodedebut[1].'/'.$explodedebut[0];
	
	$explodefin = explode("-", $absence[datefin]);
	$datefin_fr=$explodefin[2].'/'.$explodefin[1].'/'.$explodefin[0];
	
	echo "<h1>modifier une absence</h1>
	<center>
		<form method=\"post\" action=\"absence.php\">
			<input type=\"hidden\" name=\"action\" value=\"domodify\">
			<input type=\"hidden\" name=\"absenceid\" value=\"".$_GET['absenceid']."\">
			<table align=\"center\" width=\"50%\">
				<tr>
					<td align=\"center\">
						<b>Utilisateur :</b>
						$selectutilisateur
					</td>
					<td align=\"center\">
						<b>Debut (inclus) :</b> <input type=\"text\" name=\"datedebut\" id=\"datedebut\" size=\"10\" value=\"".$datedebut_fr."\" onclick=\"calendrier_show('datedebut',this.value);\" readonly>
					</td>
					<td align=\"center\">
						<b>Fin (inclus) :</b> <input type=\"text\" name=\"datefin\" id=\"datefin\" size=\"10\" value=\"".$datefin_fr."\" onclick=\"calendrier_show('datefin',this.value);\" readonly>
					</td>
					<td align=\"center\">
						<b>Raison :</b>
						$selectraison
					</td>
					<td>
						<input type=\"submit\" class=\"skinned\" value=\"Envoyer\">
					</td>
				</tr>
			</table>
		</form>
	</center>";
} else {
	$selectutilisateur="<select name=\"username\"><option value=\"\">-- Sélectionner --</option>";
	$commercials=$DB_site2->query(" SELECT username FROM parametresgeneraux WHERE commissions ='1' ORDER BY username ASC ");
	while($commercial=$DB_site2->fetch_array($commercials)){
		$selectutilisateur.= "<option value=\"".$commercial[username]."\">".UCfirst($commercial[username])."</option>";
	}
	$selectutilisateur.="</select>";

	$selectraison="<select name=\"absence_raisonid\"><option value=\"\">-- Sélectionner --</option>";
	$raisons=$DB_site2->query(" SELECT * FROM absence_raison ORDER BY libelle ASC ");
	while($raison=$DB_site2->fetch_array($raisons)){
		if($raison['libelle']=='maladie' || $raison['libelle']=='injustifié') {
			$selectraison.= "<option value=\"".$raison[absence_raisonid]."\">".UCfirst($raison['libelle'])." (pas comptabilisé)</option>";
		}
		else {
			$selectraison.= "<option value=\"".$raison[absence_raisonid]."\">".UCfirst($raison['libelle'])."</option>";
		}
	}
	$selectraison.="</select>";
	

	echo "<h1>Ajouter une absence</h1>
		<center>
			<form method=\"post\" action=\"absence.php\">
				<input type=\"hidden\" name=\"action\" value=\"add\">
				<table align=\"center\" width=\"60%\">
					<tr>
						<td align=\"center\">
							<b>Utilisateur :</b>
							$selectutilisateur
						</td>
						<td align=\"center\">
							<b>Debut (inclus) :</b> <input type=\"text\" name=\"datedebut\" id=\"datedebut\" size=\"10\" value=\"\" onclick=\"calendrier_show('datedebut',this.value);\" readonly>
						</td>
						<td align=\"center\">
							<b>Fin (inclus) :</b> <input type=\"text\" name=\"datefin\" id=\"datefin\" size=\"10\" value=\"\" onclick=\"calendrier_show('datefin',this.value);\" readonly>
						</td>
						<td align=\"center\">
							<b>Raison :</b>
							$selectraison
						</td>
						<td>
							<input type=\"submit\" class=\"skinned\" value=\"Envoyer\">
						</td>
					</tr>
				</table>
			</form>
		</center>";


	$absences=$DB_site2->query("SELECT * FROM absence ORDER BY datedebut DESC ");


	echo "<h1>Liste des absences</h1>
		<center>
			<table align=\"center\" width=\"60%\">
				<tr>
					<td align=\"center\">
						Utilisateur
					</td>
					<td align=\"center\">
						Début (inclus)
					</td>
					<td align=\"center\">
						Fin (inclus)
					</td>
					<td align=\"center\">
						Raison
					</td>
					<td align=\"center\">
						Jours ouvrés
					</td>
					<td align=\"center\">
						Jours comptabilisés
					</td>
					<td align=\"center\">
						Action
					</td>
				</tr>";
	$rowalt=1;
	while($absence=$DB_site2->fetch_array($absences)){
		$explodedebut = explode("-", $absence[datedebut]);
		$datedebut_fr=$explodedebut[2].'/'.$explodedebut[1].'/'.$explodedebut[0];
		
		$explodefin = explode("-", $absence[datefin]);
		$datefin_fr=$explodefin[2].'/'.$explodefin[1].'/'.$explodefin[0];
		
		$raison=$DB_site2->query_first("SELECT * FROM absence_raison WHERE absence_raisonid='".$absence[absence_raisonid]."' ");
		
		$nb_jour_ouvres=getJours($absence['datedebut'], $absence[datefin]);
		$rowalt=$rowalt%2;

		if($raison['libelle']=="maladie" || $raison['libelle']=="injustifié"){
			$nb_jour_comptabilises=0;
		}
		else{
			$nb_jour_comptabilises=$nb_jour_ouvres;
		}
		
		echo "<tr class=\"td_alt".$rowalt."\">
				<td align=\"center\">".UCfirst($absence[username])."</td>
				<td align=\"center\">".$datedebut_fr."</td>
				<td align=\"center\">".$datefin_fr."</td>
				<td align=\"center\">".$raison[libelle]."</td>
				<td align=\"center\">".$nb_jour_ouvres."</td>
				<td align=\"center\">".$nb_jour_comptabilises."</td>
				<td align=\"center\">
					<a href=\"absence.php?action=delete&absenceid=".$absence[absenceid]."\">supprimer</a><br>
					<a href=\"absence.php?action=modify&absenceid=".$absence[absenceid]."\">modifier</a>
				</td>
			</tr>";
		$rowalt++;
	}
				
	echo "	</table>
		</center>";
}	

function getJours($datedeb,$datefin,$utilisateur=""){ // format "Y-m-d"
	global $DB_site2;
	$jour_ferie_array=array();
	$jour_feries=$DB_site2->query("SELECT * FROM jour_ferie ORDER BY dateline ASC ");
	while($jour_ferie=$DB_site2->fetch_array($jour_feries)){
		$jour_ferie_array[]=$jour_ferie[dateline];
	}
    $nb_jours=0;
    $dated=explode('-',$datedeb);
    $datef=explode('-',$datefin);
    $timestampcurr=mktime(0,0,0,$dated[1],$dated[2],$dated[0]);
    $timestampf=mktime(0,0,0,$datef[1],$datef[2],$datef[0]);
    while($timestampcurr<=$timestampf){
		$datecurr=date('Y-m-d',$timestampcurr);
		if(!in_array($datecurr,$jour_ferie_array)){ // si c'est pas un jour férié
			if((date('w',$timestampcurr)!=0)&&(date('w',$timestampcurr)!=6)){ // si c'est pas un samedi ou un dimanche
				$nb_jours++;
			}
		}
		$timestampcurr=mktime(0,0,0,date('m',$timestampcurr),(date('d',$timestampcurr)+1) ,date('Y',$timestampcurr));
    }
	return $nb_jours;
}

echo "<br>";

include "./footer.php";

?>