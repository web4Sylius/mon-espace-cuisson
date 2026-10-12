<?php
require_once($_SERVER['DOCUMENT_ROOT']."/admin/admin_global.php");

require_once(SITE_PATH."/class/facture.class.php");
require_once(SITE_PATH."/class/DAOgarantie.class.php");
require_once(SITE_PATH."/class/garantie.class.php");
require_once(SITE_PATH."/class/DAOfournisseur.class.php");
require_once(SITE_PATH."/class/DAOlignefacturegarantie.class.php");
require_once(SITE_PATH."/class/stock.class.php");
require_once(SITE_PATH."/class/frais_port.php");
require_once(SITE_PATH."/class/installateur.class.php");
require_once(SITE_PATH."/class/formulaire.class.php");
require_once(BACK_PATH."/dissociation.php");
require_once(SITE_PATH."/class/email_client.php");
require_once(BACK_PATH."/sav.class.php");
require_once(BACK_PATH."/export/xlexport.php");
require_once(SITE_PATH."/class/fournisseur.class.php");
require_once(SITE_PATH."/class/fonctions.php");

require_once(BACK_PATH."/marge.php");

$superusers = array("philippe", "cc", "benoit","ariel","jessica","jbandre");

if(!isset($_GET['userid']) || empty($_GET['userid'])) exit;

if(!isset($_GET['prems'])){
	$options = $selected ="";
	$select = "<tr><td colspan=\"7\" class=\"td_factures_year\" >Pour voir les factures à une date antérieur choisissez l'année : </td><td colspan=\"2\" class=\"td_factures_year\"><select onchange=\"loadbyyear(this.value)\" id=\"suivi_annee\"><option></option>";
	$factures=$DB_site2->query("SELECT DATE_FORMAT(datefacture, '%Y') as annees FROM facture WHERE userid=".$_GET['userid']." and supprimee='0' GROUP BY annees ORDER BY datefacture DESC");

	while($annees = $DB_site2->fetch_array($factures)){

		if(isset($_GET['datemax'])){
			if($annees['annees'] == date("Y",$_GET['datemax'])){
				$selected = "selected"; 
			}else{
				$selected = ""; 
			} 
		}

		if($annees['annees'] != date("Y") && $annees['annees'] != (date("Y")-1) && !isset($_GET['datemax'])){

			$options .= "<option value=\"".mktime(0,0,0,1,1,$annees['annees'])."\" $selected>".$annees['annees']."</option>";

		}elseif(isset($_GET['datemax'])){
			$options .= "<option value=\"".mktime(0,0,0,1,1,$annees['annees'])."\" $selected>".$annees['annees']."</option>";
		}
	}
	$select .= $options;
	$select .="</select></td><td colspan=\"2\" class=\"td_factures_year\"></td></tr>";
	echo $select;
}

echo "<tr>
		<td class=\"td_head_factures\"><b>N° Fac</b></td>
		<td class=\"td_head_factures\"><b>N° Cde</b></td>
		<td class=\"td_head_factures\"><b>Date</b></td>
		<td class=\"td_head_factures\"><b>Montant TTC</b></td>
		<td class=\"td_head_factures\"><b>Marge HT</b></td>
		<td class=\"td_head_factures\"><b>Moyen paiement</b></td>
		<td class=\"td_head_factures\"><b>Etat</b></td>
		<td class=\"td_head_factures\"><b>Cial attribué</b></td>
                <td class=\"td_head_factures\"><b>Avis client</b></td>
		<td class=\"td_head_factures\"><b>ACTIONS</b></td>              
	</tr>";

if($_GET['prems']==1){
	$limit=" LIMIT 10 ";
	$annee="";
}elseif(isset($_GET['datemax'])){
	$limit="";
	$datemax = $_GET['datemax'];
	$datemin = mktime(0,0,0,12,31,date("Y",$datemax));
	$annee = "AND heurefacture > '$datemax' AND heurefacture < '$datemin'";
} else {
	$limit="";
	$datemax = mktime(0,0,0,1,1,(date("Y")-1)); //debut année en cours
	$annee="AND heurefacture > '$datemax'";
}

$factures=$DB_site2->query("SELECT facturid FROM facture WHERE userid=".$_GET['userid']." and supprimee='0' $annee ORDER BY heurefacture DESC ".$limit); // LIMIT 10
while ($f=$DB_site2->fetch_array($factures)){
		$facture=new facture($DB_site2,$f['facturid']);
	$remboursement = 0 ;
	$rowalt = "td_factures_".getrowbg();
	$articles="";

	foreach($facture->lignes as $ligne){
	$margetotal=new marge($DB_site2,$facture->facturid);
	$margett = $margetotal->margetotal($DB_site2,$facture->facturid);

		$argument="";

		$argreq=$DB_site2->query("SELECT artcode,argument FROM article WHERE argument<>'' AND (artid IN (SELECT artid FROM article_lot WHERE lotid='".$ligne->artid."') OR artid='".$ligne->artid."')");
		if($DB_site2->num_rows($argreq)>0){
			while($arg=$DB_site2->fetch_array($argreq)) $argument.=" - ".$arg['artcode']." : ".$arg['argument'];
		}

		if($argument!="") $argument="<img src=\"images/lockit.jpg\" width=\"20\" title=\"".$argument."\" align=\"absmiddle\">";
		$fbart="";

		if($ligne->artid>0){
				$nbfile=$DB_site2->query_first("SELECT COUNT(*) FROM article_files WHERE artid=".$ligne->artid);
				if($nbfile[0]>0) $fbart="<img src=\"images/icon/pdf.png\" width=\"30\" onclick=\"fb.show('article_files.php?artid=$ligne->artid&parent=0')\" align=\"absmiddle\">";
		}
	  
			$ref=$ligne->artcode;
			if($ligne->artid>0) $ref="<a href=\"clientnavig.php?userid=".$_GET['userid']."&goto=art,".$ligne->artid.",-.html\" target=\"_blank\">".$ref."</a>";
			if($ligne->ref!="") $ref.=" (".$ligne->ref.")";
			
		$articles.="$ligne->qte $ref $argument $fbart<br>";
	}

	if ($remboursement > 0){
		$remboursement = "<br><font color=\"#E5840F\">-".number_format($remboursement, 2, ',', ' ')." €</font>" ;
	}else{
		$remboursement = "" ;
	}

	$devis=$DB_site2->query_first("SELECT * FROM envoi_devis WHERE facturid=".$facture->facturid." ORDER BY date DESC");
	$logodevis="";
	if($devis['date']!="") $logodevis=" <img src=\"images/devis.png\" height=\"24\" align=\"absmiddle\"><br>".date("d/m/Y à H:i",$devis['date'])." par ".($devis['utilisateur']==""?"le client":$devis['utilisateur']);

	$marge=new marge($DB_site2,$facture->facturid);
	
		$savinfo=$DB_site2->query_first("SELECT COUNT(*) FROM sav WHERE facturid=".$facture->facturid." ");
		if($savinfo[0]>0){
				$sav=$DB_site2->query_first("SELECT id FROM sav WHERE facturid=".$facture->facturid." ");
				$iconeSAV=" <a href=\"facture.php?action=editer&facturid=".$facture->facturid."&savid=".$sav['id']."#sav\"><img src=\"images/sav.png\" height=\"20\"></a>";
		} else {
				$iconeSAV="";
		}
	
	
	echo "<tr>";
	if ($facture->numerofacture > 556500){
		echo "<td class=\"$rowalt\" align=\"center\">$facture->numerofacture</td>";
		$lientelecharger = "<br><br><a href=\"http://$host/facture2.php?passession=oui&facturid=$facture->facturid&passe=$facture->passefacture&langue=FR\" target=\"_blank\">Facture</a>" ;
	}else{
		if ($facture->valide == 1){
			echo "<td class=\"$rowalt\" align=\"center\"> -- </td>" ;
			$lientelecharger = "<br><br><a href=\"http://$host/facture2.php?passession=oui&facturid=$facture->facturid&passe=$facture->passefacture&cde=1&langue=FR\" target=\"_blank\">Commande</a>" ;
		}else {
			echo "<td class=\"$rowalt\" align=\"center\"> -- </td>" ;
			$lientelecharger = "<br><br>" ;
		}
	}
	$margetotal=new marge($DB_site2,$facture->facturid);
	$margett = $margetotal->margetotal($DB_site2,$facture->facturid);
	echo "<td class=\"$rowalt\">".$facture->facturid.$iconeSAV."</td>";
	echo "<td class=\"$rowalt\" align=\"center\">".date("d/m/Y H:i",$facture->datecommande)."</td>";
	echo "<td class=\"$rowalt\" align=\"right\" nowrap>".number_format($facture->totalttc,2,",","")." &euro; $remboursement</td>";
	if ($facture->valide != 0){
		echo "<td class=\"$rowalt\" align=\"right\" nowrap>".$margett['margett']." &euro;<br>".$margett['margett_pc']." %</td>";
	}
	else {
		echo "<td class=\"$rowalt\" align=\"right\" nowrap>".number_format($marge->marge,2,',','')." &euro;<br>".number_format($marge->pcmarge,2,',','')."%</td>";
	}
	$passepar=($facture->commercial==""?"le client":$facture->commercial);
	$moyenpaiement=$facture->getMoyen($facture->moyenid);
	if($facture->provenance=="mp") $moyenpaiement=$marketplaces[$facture->provenanceid];
	echo "<td class=\"$rowalt\" rowspan=\"2\" align=\"center\">$moyenpaiement<br>Passée par $passepar</td>";

	switch($facture->valide){
			case 0: // non traité
				$couleurlien="#000000";
			break;
			case 1: // Validée
			case 7:
				$couleurlien="#0000FF";
			break;
			case 2: // refus commercant
				$couleurlien="#FF0000";
			break;
			case 3: // refus carte bleue
				$couleurlien="#FF0000";
			break;
			case 4: // abandon internaute
				$couleurlien="#FF0000";
			break;
			case 5: // expédié
			case 8:
				$couleurlien="#00FF00";
			break;
		}

	echo "<td class=\"$rowalt\" rowspan=\"2\" align=\"center\" nowrap width=\"10%\"><font color=\"$couleurlien\">$facture->validetxt</font>$logodevis</td>
			<td class=\"$rowalt\" rowspan=\"2\" align=\"center\" nowrap width=\"10%\"><div  align=\"center\">";
	
	if($facture->provenance == 'mp'){
		echo "Marketplace";
	} elseif($facture->provenance == 'sav' || $facture->provenance == 'avoir'){
		echo "SAV";
	}else{
		if($facture->commercial_attribution==""){
			echo "Non attribué";
		} else {
			echo UCfirst($facture->commercial_attribution);
		}
			}
			
			$davis=new DateTime("2 weeks ago");
			$davis->setTime(0,0,0);
			$avis="";
			if(in_array($facture->valide,array(5,8)) && $davis->gettimestamp()>=$facture->dateexpedition){
				$avis=$infoavis[$facture->avisclient];
			}
	echo "</div></td>
	<td class=\"$rowalt\" rowspan=\"2\">".$avis."</td>
	<td class=\"$rowalt\" rowspan=\"2\" align=\"center\" width=\"30%\"><div  align=\"center\">
	<a href=\"facture.php?action=editer&facturid=$facture->facturid\" target=\"_self\">Editer</a>";
	
	if ($facture->numerofacture > 556500){
			echo "<br><br><a href=\"http://$host/facture2.php?passession=oui&facturid=".$facture->facturid."&passe=".$facture->passefacture."&langue=FR\" target=\"_blank\">Facture</a>" ;
	}else{
			if ($facture->valide == 1) 
					echo "<br><br><a href=\"http://$host/facture2.php?passession=oui&facturid=$facture->facturid&passe=$facture->passefacture&cde=1&langue=FR\" target=\"_blank\">Commande</a>" ;
			elseif ($facture->valide==0 && $devis['date']=="" && !in_array($facture->moyenid,array(2,4,5,99,17,14)))
					echo "<br><br><a href=\"utilisateur.php?action=cacher&facturid=".$facture->facturid."&userid=".$_GET['userid']."\" target=\"_self\">Supprimer</a>" ;
			else
					echo "<br><br>" ;
	}


	$projets_exist=$DB_site2->query("SELECT * FROM projet WHERE userid = ".$_GET['userid']." AND etatid IN (2, 3, 4, 5, 6, 7, 8)");
	if($facture->valide==0) {
		echo "<br><a href=\"javascript:popdevis($facture->facturid)\">Envoyer le devis</a><br>";
		//// lie ou délie un devis à un projet :
		$projet=$DB_site2->query_first("SELECT projetid,libelle,facturid FROM projet WHERE devis LIKE '%\"".$facture->facturid."\"%' AND userid = ".$_GET['userid']);
		$devis=$DB_site2->query_first("SELECT * FROM envoi_devis WHERE facturid=".$facture->facturid." ORDER BY date DESC");
		if(isset($projet['projetid']) && $devis['date']!="" && $DB_site2->num_rows($projets_exist)>0) {
			echo "<a href=\"javascript:poprmdevis($facture->facturid,".$projet['projetid'].",'".addslashes($projet['libelle'])."')\">Délier du projet</a>";
		}
		elseif(empty($projet['projetid']) && $devis['date']!="" && $DB_site2->num_rows($projets_exist)>0) {
				echo "<a href=\"javascript:popadddevis($facture->facturid)\">Lier à un projet</a>";
		}
		
	}

	if(check_commande_valide($DB_site2, $facture->facturid) == true && $DB_site2->num_rows($projets_exist)>0){
		//// lie ou délie une commande à un projet
		$projet=$DB_site2->query_first("SELECT projetid,libelle,facturid FROM projet WHERE facturid=". $facture->facturid." AND userid = ".$_GET['userid']);
		if(isset($projet['projetid'])) {
			echo "<br><a href=\"javascript:poprmcmd($facture->facturid,".$projet['projetid'].",'".addslashes($projet['libelle'])."')\">Délier du projet</a>";
		}
		else {
			echo "<br><a href=\"javascript:popaddcmd($facture->facturid)\">Lier à un projet</a>";
		}
	}
			echo "</div>
		</td>
		</tr>";

	echo "<tr><td colspan=\"5\" class=\"$rowalt\" >$articles</td></tr>";

	
}



?>
