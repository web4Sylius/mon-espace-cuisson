<?php
require_once($_SERVER['DOCUMENT_ROOT']."/admin/admin_global.php");
ini_set("display_errors",1);
include_once(BACK_PATH."/header.php");
include_once(BACK_PATH."/menu.php");
?>
<h1>Commandes à attribuer à un commercial :</h1>
<?php
if(isset($_GET['ordre_tri']) && $_GET['ordre_tri']!=""){
	$ordre_tri=$_GET['ordre_tri'];
} else {
	$ordre_tri="DESC";
}




$factures=$DB_site2->query("SELECT f.facturid, passefacture, datefacture,
							heurefacture, datelivraison, u.mail, f.prixgestion,
							f.prixport, ef.$libelle, f.valide, mp.$libelle as moyenpaiement,
							f.nom, u.nom AS unom,f.commercial,f.devise,
							cadeauid, f.moyenid, f.prix_contre_remboursement, f.numerofacture,
							f.codenumerofacture, f.advdate1, f.advdate2, f.advdate3, f.provenance,
							f.provenanceid, f.mp_facturid, f.userid, u.vip, u.codepostal, u.ville,
							u.paysid FROM facture f
							INNER JOIN utilisateur u ON (f.userid = u.userid)
							INNER JOIN etatfacture ef ON (f.valide = ef.valudeid)
							INNER JOIN moyenpaiement mp ON (f.moyenid = mp.moyenid)
							WHERE datefacture>='2017-01-01'
							AND f.provenance IN ('', 'mobile')
							AND (
								 f.valide IN ('1','5','6','7','8')
								 OR 
								 ( f.valide = '0' AND f.moyenid IN (2,4,5,14,17,18,99)  )
							)
							AND commercial_attribution=''
							ORDER BY datefacture ".$ordre_tri.", f.facturid DESC ");
$nb = $DB_site2->num_rows($factures) ;
$i=0;
if ($nb > 0){ // il y a des cdes
	if (!isset($nb) || ($nb<=0))
		echo "<font class=\"cost\">Aucune facture ne correspond à votre recherche</font><br><br>" ;
	else
		echo "$nb facture(s) correspond(ent) à votre recherche : <br><br>";
	
	if(isset($_GET['ordre_tri']) && $_GET['ordre_tri']!=""){
		if($_GET['ordre_tri']=="DESC"){
			$autre_ordre="ASC";
		} else {
			$autre_ordre="DESC";
		}
	} else {
		$autre_ordre="ASC";
	}
	echo "<a href=\"commande_attribution.php?ordre_tri=".$autre_ordre."\">CHANGER L'ORDRE DE TRI</a><br><br>";
	
	
// 	echo "<a href=\"commande_attribution.php?attribuer=auto\">ATTRIBUTION AUTOMATIQUE</a><br><br>";
	
	
	
	if(isset($_GET['attribuer']) && $_GET['attribuer']=="auto"){
		// ici on fait une attribution auto.
		echo "ces commandes ont été attribuées automatiquement à 'direct' :<br>";
		while ($facture=$DB_site2->fetch_array($factures)) {
			$sqlSub1 = "SELECT COUNT(*)";
			$sqlSub1 .= " FROM suivi_commande ";
			$sqlSub1 .= " WHERE facturid = '".$facture[facturid]."'";
			$sqlSub1 .= " AND hide ='0'";
			$sqlSub1 .= " AND commercial NOT IN ('', 'client', 'sav', 'adv') ";
			$suivi_commande=$DB_site2->query_first($sqlSub1);
			
			$sqlSub2 = "SELECT COUNT(*)";
			$sqlSub2 .= " FROM suivi_client ";
			$sqlSub2 .= " WHERE userid = ".$facture[userid];
			$sqlSub2 .= " AND commercial NOT IN ('', 'client') ";
			$suivi_client=$DB_site2->query_first($sqlSub2);
			
			if($suivi_commande[0]==0 && $suivi_client[0]==0){
				echo "- $facture[facturid] <br>";
				$sql="UPDATE facture SET commercial_attribution='direct' WHERE facturid='".$facture[facturid]."' ";
				$DB_site2->query($sql);
			} else {
			//	echo "+ $facture[facturid] <br>";
			//	echo "sql : $sqlSub1 --- $sqlSub2 <br><br>";
			}
			
		}
	} else {
	
	
		echo "<a href=\"facture.php\">Retour à la liste des factures</a>
			<br><br>
			<table class=\"table_factures\">
				<tr>
				   <td class=\"td_head_factures\"><b>N° Fac</b></td>
				   <td class=\"td_head_factures\"><b>N° Cde</b></td>
				   <td class=\"td_head_factures\"><b>Date</b></td>
				   <td class=\"td_head_factures\"><b>Montant TTC</b></td>
				   <td class=\"td_head_factures\" width=\"30\"><b>Moyen paiement</b></td>
				   <td class=\"td_head_factures\"><b>ACTIONS</b></td>
			  </tr>";

		while ($facture=$DB_site2->fetch_array($factures)) {
			$remboursement = 0 ;
			$rowalt = "td_factures_".getrowbg();
			list ($annee, $mois, $jour) = explode("-", $facture[datefacture]);
			$lignefactures=$DB_site2->query("SELECT * FROM lignefacture WHERE factureid = '$facture[facturid]'");
			$total_facture = 0 ;
			while ($lignefacture=$DB_site2->fetch_array($lignefactures)){
				$total_facture += ($lignefacture[prix] * $lignefacture[qte]) ;
				if ($lignefacture[remboursement]){
					$remboursement += $lignefacture[prix] * $lignefacture[qte] ;
				}
			}
			$total_facture=$DB_site2->query_first("SELECT SUM(prix*qte) FROM lignefacture WHERE factureid='$facture[facturid]'");
			// remise sur tout le catalogue
			$fact=$DB_site2->query_first("SELECT pctremise FROM facture WHERE facturid = '$facture[facturid]'");
			if ($fact[0] != 0){
				$total_facture[0] = $total_facture[0] * (1 - $fact[0] / 100) ;
			}

			// Bon de réduction 
			$prixcadeau = 0;
			if (in_array( "13", $modules ) && $rs_facture[cadeauid] > 0 ){
			  $cadeau=$DB_site2->query_first("select cadeauid, montantcadeau from facture where facturid = '$facture[facturid]'") ;
			  if ($cadeau[cadeauid] != ""){
				  $prixcadeau = $cadeau[montantcadeau] ;
			  }
			}
			if(is_string($rs_facture[cadeauid]) && $rs_facture[cadeauid]!="")
			  {}

			// remise
			if (in_array ("3", $modules)){
			  $prixremise = 0 ;
			  $remise_facture = $DB_site2 -> query_first ("SELECT r.remiseid, r.typeremise, r.valeur, r.pctremise, f.montantremise 
														   FROM facture f inner join remise r on (f.remiseid = r.remiseid)
														   WHERE f.facturid = '$facture[facturid]'" );
			  if ($remise_facture[remiseid] != ""){
				  $prixremise = $remise_facture[montantremise] ;
			  } // fin du if remise
			} // fin des remises

			// contre remboursement
			if ($rs_facture[moyenid] == '3'){
			  $prix_contre_remb = $facture[prix_contre_remboursement] ;
			} else {
			  $prix_contre_remb = 0 ;
			}
			$total_facture = number_format($total_facture[0] + $facture[prixgestion] + $prix_contre_remb + $facture[prixport] - $prixcadeau -$prixremise, 2, ',', ' ')." ".$devises->symbole($facture['devise']);
			list($annee, $mois, $jour) = explode( "-", $facture[datefacture] );

			if ($remboursement > 0){
			  $remboursement = "<br><font color=\"#E5840F\">-".number_format($remboursement, 2, ',', ' ')." €</font>" ;
			} else {
			  $remboursement = "" ;
			}

			echo "<tr Height=\"25\">";
			
			if ($facture[numerofacture] > 556500){
			  echo "<td class=\"$rowalt\" align=\"center\">$facture[codenumerofacture]$facture[numerofacture]</td>";
			  $lientelecharger = "<br><br><a href=\"https://$host/facture2.php?passession=oui&facturid=$facture[facturid]&passe=$facture[passefacture]&langue=FR\" target=\"_blank\">Facture</a>" ;
			} else {
			  if ($facture[valide] == 1) {
				  echo "<td class=\"$rowalt\" align=\"center\"> -- </td>" ;
				  $lientelecharger = "<br><br><a href=\"https://$host/facture2.php?passession=oui&facturid=$facture[facturid]&passe=$facture[passefacture]&cde=1&langue=FR\" target=\"_blank\">Commande</a>" ;
				  if (in_array("124", $modules)){
					  $lientelecharger .= " <input type=\"checkbox\" name=\"chk_etiquette[$rs_facture[facturid]]\" />" ;
				  }
			 } else {
				  echo "<td class=\"$rowalt\" align=\"center\"> -- </td>" ;
				  $lientelecharger = "<br><br>" ;
				}
			}
			
			$vip=usericon($facture['vip'],20);

			$sav=$DB_site2->query_first("SELECT sav.id,value AS typelitige FROM sav
										INNER JOIN sav_fields ON ( sav.id = sav_fields.savid )
										WHERE name = 'sav_typelitige' AND facturid=".$facture['facturid']." AND fin=0 ORDER BY sav.date DESC");

			if($sav['id']!="")
				$vip.="<a href=\"facture.php?action=editer&facturid=".$facture['facturid']."&savid=".$sav['id']."#sav\"><img src=\"images/".($sav['typelitige']!=5?"sav":"fraude").".png\" height=\"20\"></a>";

			if($facture['paysid']==57)
				$ville=substr($facture['codepostal'],0,2)." ".$facture['ville'];
			else
				$ville=$facture['ville'];

			echo "<td class=\"$rowalt\">$facture[facturid]<br>$facture[nom]<br><a href=\"utilisateur.php?action=editer&userid=$facture[userid]\" style=\"color:#404040;\">$facture[unom]</a>$vip $ville</td>";
			echo "<td class=\"$rowalt\" align=\"center\">".date("d/m/Y H:i",$facture['heurefacture'])."</td>";
			echo "<td class=\"$rowalt\" align=\"right\" nowrap>$total_facture $remboursement</td>";

			if ($facture[valide] == 0) { // non traité
				$couleurlien="#000000";
			}
			if ($facture[valide] == 1 || $facture[valide]==7) { // Validée
				$couleurlien="#0000FF";
			}
			if ($facture[valide] == 2) { // refus commercant
				$couleurlien="#FF0000";
			}
			if ($facture[valide] == 3) { // refus carte bleue
				$couleurlien="#FF0000";
			}
			if ($facture[valide] == 4) { // abandon internaute
				$couleurlien="#FF0000";
			}
			if ($facture[valide] == 5 || $facture[valide]==8) { // expédié
				$couleurlien="#00FF00";
			}

			$suivienvoi="";
			$advdate1=$advdate2=$advdate3="";
			$efl=array();
			$rappeltxt="";
			$devis=$DB_site2->query_first("SELECT * FROM envoi_devis WHERE facturid=$facture[facturid] ORDER BY date DESC");

			if($devis['date']!="") 
				$rappeltxt.="<img src=\"images/devis.png\" width=\"24\"><br>".date("d/m/Y à H:i",$devis['date'])." par ".($devis['utilisateur']!=""?$devis['utilisateur']:$rq_rappel['commercial']);

			$passepar="Passé par ".($facture['commercial']==""?$rq_rappel['commercial']:$facture['commercial']);
			
			
			/*
			// Ceci est fait pour attribuer les commandes en automatique pour les plus simples : les 'direct'
			$suivi_client=$DB_site2->query_first("SELECT COUNT(*) FROM suivi_client WHERE userid='".$facture[userid]."' AND commercial!='' ");
			if($suivi_client[0]==0){
				$DB_site2->query("UPDATE facture SET commercial_attribution='direct' WHERE facturid = '".$facture[facturid]."' ");
			} else {
				echo "cette facture n'est pas en direct : ".$facture[facturid]." <br><br>";
			}	
			*/
			
			
			echo "<td class=\"$rowalt\" align=\"center\" nowrap width=\"10%\">$facture[moyenpaiement]<br><font color=\"$couleurlien\">$facture[$libelle]</font><br>$passepar</td>
				  <td rowspan=\"2\" class=\"$rowalt\" align=\"center\" width=\"30%\"><div  align=\"center\">
					<a href=\"facture.php?action=editer&facturid=$facture[facturid]&oldaction=rechercher&ref=$ref&nom=$nom&mail=$mail&date=$date&lst_trietat=$lst_trietat\" target=\"_blank\">Editer</a>";
			if ($facture[numerofacture] > 556500){
			   echo "<br><br><a href=\"https://$host/facture2.php?passession=oui&facturid=$facture[facturid]&passe=$facture[passefacture]&langue=FR\" target=\"_blank\">Facture</a>" ;
			} else {
			  if ($facture[valide] == 1) {
				  echo "<br><br><a href=\"https://$host/facture2.php?passession=oui&facturid=$facture[facturid]&passe=$facture[passefacture]&cde=1&langue=FR\" target=\"_blank\">Commande</a>" ;
			  }
			}
			$lignefactures=$DB_site2->query("SELECT lf.*,a.artcode_eurocave FROM lignefacture lf
											LEFT JOIN article a ON(a.artid=lf.artid)
											WHERE lf.factureid = '$facture[facturid]' ");
			$articles="";
			while ($lignefacture=$DB_site2->fetch_array($lignefactures)){
				if($lignefacture['prix']>0)
					$articles.=$lignefacture['qte']." <a href=\"clientnavig.php?userid=$facture[userid]&goto=art,".$lignefacture['artid'].",-.html\" target=\"_blank\">".$lignefacture['artcode']."</a> (".$lignefacture['artcode_eurocave'].")<br>";
			}
			echo "<tr><td colspan=\"5\" class=\"$rowalt\">$articles</td></tr>";
			$i++;
			if($i>100){
				 break;
			}
		}
	}
	echo "</table>
	<br><br>
	<a href=\"facture.php\">Retour à la liste des commandes</a>" ;
} else {
	echo "<font class=\"cost\" style=\"font-size: 20px;\" > ! Aucune commande n'est sans commercial attribué !</font>" ;
	$action = "" ;
}
    

include(BACK_PATH."/footer.php");


?>
