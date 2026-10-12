<?php 
require "admin_global.php";

//archiver un visiteur automatiquement si le mail est déjà en bdd :
$liste_mail_existant = $DB_site2->query("SELECT v.visiteurid FROM visiteur v INNER JOIN utilisateur u ON u.mail = v.mail AND archive != 'oui' ORDER BY v.visiteurid DESC");
if($DB_site2->num_rows($liste_mail_existant) > 0) {
	while($mail_exist = $DB_site2->fetch_array($liste_mail_existant)){
		$DB_site2->query("UPDATE visiteur SET archive = 'oui' WHERE visiteurid='".$mail_exist['visiteurid']."'");
	}
}
//archiver un visiteur manuellement
if(isset($_POST['archive']) && $_POST['archive'] == "oui"){
	$visiteurid = mysqli_real_escape_string($DB_site2->link_id,$_POST['ligne']);
	$DB_site2->query("UPDATE visiteur SET archive = 'oui' WHERE visiteurid='$visiteurid'");
}
//colore les visiteurs en orange s'ils sont les contacts d'un client
$total_orange=0;
$total_vert=0;
$arr=array();
$arr_user=array();
$array_orange="";
$array_user="";

$liste_contacts_existant = $DB_site2->query("SELECT v.visiteurid, ua.userid FROM visiteur v INNER JOIN utilisateur_assoc ua ON ua.utilisateur_assoc_email = v.mail WHERE v.mail != '' ORDER BY v.visiteurid DESC");
if($DB_site2->num_rows($liste_contacts_existant) > 0) {
	while($mail_exist = $DB_site2->fetch_array($liste_contacts_existant)){
				$arr[]=$mail_exist['visiteurid'];
				$arr_user[]=$mail_exist['userid'];
	}
	$array_orange=implode(",",$arr);
	$array_user=implode(",",$arr_user);
}

$arr=array();
$array_vert="";
$archives = "archive != 'oui'";
$valueau=$valuedu="";

if(isset($_POST['action']) && $_POST['action'] == "search"){
	if(isset($_POST['archives']) && $_POST['archives'] == "oui"){
		$archives = "archive = 'oui'";
		$checked = "checked";
		$form_visiteur = 'display: none;';
	}
	else {
		$archives = "archive != 'oui'";
		$checked = "";
		$form_visiteur = 'display: block;';
	}

	if(isset($_POST['du']) && isset($_POST['au'])){
		$du = new DateTime($_POST['du']);
		$au = new DateTime($_POST['au']);

		$timestampdu = $du->getTimestamp();
		$timestampau = $au->getTimestamp();
		$valuedu = $du->format('Y-m-d');
		$valueau = $au->format('Y-m-d');

		$sql_liste = "SELECT v.* FROM visiteur v WHERE ".$archives." AND v.dateinscription >= $timestampdu AND v.dateinscription <= $timestampau ORDER BY v.visiteurid DESC";

		//compte le résultat des mails enregistrés
		$sql_liste_vert = "SELECT v.*, u.mail as cmpt_exist FROM visiteur v INNER JOIN utilisateur u ON u.mail = v.mail WHERE ".$archives." AND v.dateinscription >= $timestampdu AND v.dateinscription <= $timestampau AND u.mail != '' ORDER BY v.visiteurid DESC";
		$liste_vert = $DB_site2->query($sql_liste_vert);
		//compte le résultat des contacts associés
		$sql_liste_orange = "SELECT v.*, ua.userid as user_princi FROM visiteur v INNER JOIN utilisateur_assoc ua ON ua.utilisateur_assoc_email = v.mail WHERE ".$archives." AND v.dateinscription >= $timestampdu AND v.dateinscription <= $timestampau AND v.mail != '' GROUP BY v.visiteurid ORDER BY v.visiteurid DESC";
		$liste_orange = $DB_site2->query($sql_liste_orange);
		$total_orange=($DB_site2->num_rows($liste_orange)>0?$DB_site2->num_rows($liste_orange):0);
		$total_vert=($DB_site2->num_rows($liste_vert)>0?$DB_site2->num_rows($liste_vert):0);

		/// colori les mails en vert s'ils existent dans la bdd (le visiteur a pu s'inscrire ultérieurement avec la même adresse)
		$mails_existant = $DB_site2->query("SELECT v.visiteurid FROM visiteur v INNER JOIN utilisateur u ON u.mail = v.mail AND archive = 'oui' GROUP BY v.visiteurid ORDER BY v.visiteurid DESC");
		if($DB_site2->num_rows($mails_existant) > 0) {
			while($mail_exist = $DB_site2->fetch_array($mails_existant)){
				$arr[]=$mail_exist['visiteurid'];
			}
			$array_vert=implode(",",$arr);
		}
		/// fin du colori
	}
}
else {
	$sql_liste = "SELECT * FROM visiteur v WHERE ".$archives." ORDER BY v.visiteurid DESC";
	$sql_liste_orange = "SELECT v.*, ua.userid as user_princi FROM visiteur v INNER JOIN utilisateur_assoc ua ON ua.utilisateur_assoc_email = v.mail WHERE ".$archives." ORDER BY v.visiteurid DESC";
	$liste_orange = $DB_site2->query($sql_liste_orange);
	$total_orange=($DB_site2->num_rows($liste_orange)>0?$DB_site2->num_rows($liste_orange):0);
}

include "./header.php";
include "./menu.php";

?>

<style type="text/css">
	.main{width: 100%;}
</style>



<h3>Liste des Visiteurs non inscrits</h3>
<form method="post" action="#" name="search">
	<input type="hidden" name="action" value="search">
	<p>Choix de la date : Début <input type="date" id="du" name="du" value="<?php echo $valuedu ?>" required>   Fin <input type="date" id="au" name="au" value="<?php echo $valueau ?>" required>
	<input type="checkbox" name="archives" value="oui" <?php echo $checked ?>/> Voir les visiteurs archivés <input type="submit" value="Valider"></p>
</form>
<?php
$cnt=1;
$table="<table class=\"tab_visit\">";
$td = "<tr><td>ACTION</td><td>Date</td><td>Mail</td><td>Civilité</td><td>Société</td><td>Nom</td><td>Prénom</td><td>Adresse</td><td>Ville</td><td>code postal</td><td>Pays</td><td>Téléphone / Portable</td><td>Connu</td><td>news SMS</td><td>news mail</td><td>Provenance</td><td>Système d'exploitation</td><td>Navigateur</td><td>Version navigateur</td></tr>";
$liste = $DB_site2->query($sql_liste);


$td.="<tr>".$DB_site2->num_rows($liste)." résultats, dont ".$total_orange." <span style=\"color:rgb(193,127,4);\">contact(s) associé(s)</span>, dont ".$total_vert." <span style=\"color:rgb(0, 128, 0);\">compte(s) créé(s)</span></tr>";

while($visiteur = $DB_site2->fetch_array($liste)){
	
	$pays = $DB_site2->query_first("SELECT libelle FROM pays WHERE paysid ='$visiteur[paysid]'");
	$ligne.= "<tr>";
	$class_td = $cnt%2 == 0 ? 'td_alt2' : 'td_alt1';
	$ligne.= "<td class=\"$class_td\">
	<div style=\"".$form_visiteur."\">
	<form method=\"post\" action=\"#\"><input type=\"hidden\" name=\"archive\" value=\"oui\"><input type=\"hidden\" name=\"ligne\" value=\"$visiteur[visiteurid]\"><input type=\"submit\" value=\"archiver\"></form>
	<button onclick=\"mail_auto('".$template->translate('/site/mail_expediteur')."','$visiteur[mail]',undefined,undefined,undefined,undefined,undefined,false,'visiteur');mail_services('FR','base','commercial');add_exp_list('contact','".$template->translate('/site/mail_expediteur')."','base','FR')\">Envoyer email</button>
	</div>
	</td>";
	$ligne.= "<td class=\"$class_td\">".gmdate("Y-m-d H:m:s",($visiteur[dateinscription]+7200))."</td>";
	$ligne.= "<td class=\"$class_td\"><span id=\"mail_exist_color_".$visiteur[visiteurid]."\">$visiteur[mail]</span></td>";
	$ligne.= "<td class=\"$class_td\">".($visiteur[civilite] == 0?"Mr":"Mme")."</td>";
	$ligne.= "<td class=\"$class_td\">$visiteur[raisonsociale]</td>";
	$ligne.= "<td class=\"$class_td\">$visiteur[nom] ".($visiteur[nom]!=''?"<a href=\"/admin/utilisateur.php?src=".$visiteur[nom]."\" target=\"_blank\" style=\"text-decoration: none\">🔍</a>":'')."</td>";
	$ligne.= "<td class=\"$class_td\">$visiteur[prenom]</td>";
	$ligne.= "<td class=\"$class_td\">$visiteur[adresse]</td>";
	$ligne.= "<td class=\"$class_td\">$visiteur[ville]</td>";
	$ligne.= "<td class=\"$class_td\">$visiteur[codepostal]</td>";
	//$ligne.= "<td class=\"$class_td\">".($visiteur[adresse]=='' && $visiteur[ville]=='' && $visiteur[codepostal]==''?"":"$visiteur[adresse]<br/> $visiteur[ville], $visiteur[codepostal]")."</td>";
	$ligne.= "<td class=\"$class_td\">$pays[0]</td>";
	$ligne.= "<td class=\"$class_td\">$visiteur[telephone] / $visiteur[portable]</td>";
	$ligne.= "<td class=\"$class_td\">$visiteur[connu]</td>";
	$ligne.= "<td class=\"$class_td\">".($visiteur[news_sms] == 1?"oui":"non")."</td>";
	$ligne.= "<td class=\"$class_td\">".($visiteur[news_mail] == 1?"oui":"non")."</td>";
	$ligne.= "<td class=\"$class_td\">$visiteur[provenance]</td>";
	$ligne.= "<td class=\"$class_td\">$visiteur[device]</td>";
	$ligne.= "<td class=\"$class_td\">$visiteur[browser]</td>";
	$ligne.= "<td class=\"$class_td\">$visiteur[browser_version]</td>";
	

	$ligne.= "</tr>";
	$cnt++;
	
}

$table.=$td;
$table.=$ligne;
$table.="</table>";

echo $table;
echo "<script type=\"text/javascript\">mail_existe_color('".$array_orange."','#c17f04','".$array_user."'); mail_existe_color('".$array_vert."','green'); </script>";
include "./footer.php";