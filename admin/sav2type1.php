<?php

// print $sav->filebox();

print "<a name=\"sav\"></a>
<div class=\"advgroup\">
	<form name=\"et1\" action=\"facture.php?action=editer&facturid=$facturid&savid=".$sav->id."#sav\" method=\"post\">
		<input type=\"hidden\" name=\"savid\" value=\"".$sav->id."\">
		<input type=\"hidden\" name=\"step\" value=\"1\">
		<input type=\"hidden\" name=\"sav_nom\" value=\"$user[nom]\">
		<input type=\"hidden\" name=\"sav_prenom\" value=\"$user[prenom]\">
		<input type=\"hidden\" name=\"sav_civ\" value=\"".$user['civilite']."\">
		<input type=\"hidden\" name=\"sav_codepostal\" value=\"$user[codepostal]\">
		<input type=\"hidden\" name=\"sav_facturid\" value=\"".$facturid."\">
		<p>Réclamation : panne produit<span class=\"modifstep\">".$sav->steps[1]['utilisateur']." ".($sav->steps[1]['date']>0?date("d/m/Y",$sav->steps[1]['date']):"")."</span></p>
		<b>Produits concernés</b>";

	$refs=array();
	$lignes=$DB_site2->query("SELECT * FROM lignefacture WHERE factureid=$rs_facture[facturid]");
	while($ligne=$DB_site2->fetch_array($lignes)){
		$article=$DB_site2->query_first("SELECT artcode_eurocave,garantie_incluse,m.libelle AS marque FROM article a INNER JOIN article_marque am USING(artid) INNER JOIN marque m USING(marqueid) WHERE artid=".$ligne['artid']);
		$optqte="";
		for($i=0;$i<=$ligne['qte'];$i++) $optqte.="<option value=\"$i\"".($sav->lignes[$ligne['lignefactureid']]==$i?" selected":"").">$i</option>";
		if($sav->lignes[$ligne['lignefactureid']]>0) $refs[]=$ligne[artcode]."/".$article[artcode_eurocave];
		
		print "<br>
			<select name=\"savligne[$ligne[lignefactureid]]\" onchange=\"document.forms.et1.submit();\">$optqte</select> / $ligne[qte] $ligne[libelle] $ligne[artcode] - $article[artcode_eurocave] $article[marque]
		<table id=\"detail$ligne[lignefactureid]\" style=\"border:solid thin black;".($sav->lignes[$ligne['lignefactureid']]>0?"":"display:none;")."\">
			<tr>
				<td colspan=\"4\">Rappel commande passée :</td>
			</tr>";
		
		$requete=$DB_site2->query("SELECT efl.paht,ef.destinataire,efl.type,ef.assur_comp FROM envoi_fournisseur_ligne efl INNER JOIN envoi_fournisseur ef ON(ef.id=efl.efid) WHERE efl.ligne_facture_id=".$ligne['lignefactureid']);
		while($efl=$DB_site2->fetch_array($requete)){
			switch($efl['type']){
				case "fourn";
					if($efl['destinataire']!="0") 
						print "<tr>
							<td>".$ligne['qte']." ".$ligne['artcode']." - ".$article['artcode_eurocave']."</td>
							<td>".$efl['destinataire']."</td>
							<td align=\"right\">".$efl['paht']." &euro; HT</td>
							<td></td>
						</tr>";
					break;
				case "trspt":
					if($efl['destinataire']!="0") 
						print "<tr>
							<td>1 transport</td>
							<td>".$efl['destinataire']."</td>
							<td align=\"right\">".$efl['paht']." &euro; HT</td>
							<td>".($efl['assur_comp']=='1'?"assurance complémentaire":"")."</td>
						</tr>";
					break;
				case "sls":
					if($efl['destinataire']!="0") 
						print "<tr>
							<td>1 installateur</td>
							<td>".$efl['destinataire']."</td>
							<td align=\"right\">".$efl['paht']." &euro; HT</td>
							<td>".($efl['assur_comp']=='1'?"assurance complémentaire":"")."</td>
						</tr>";
					break;
			}
		}
		//if($article['garantie_incluse']=="") $article['garantie_incluse']="aucune";
		//print "<tr><td colspan=\"2\">Garantie constructeur : ".$article['garantie_incluse']."</td>";
		$caract=$DB_site2->query_first("SELECT lfc.libelle_caract,lfc.libelle_caractval,cv.libelleFR FROM lignefacturecaracteristique lfc INNER JOIN caracteristiquevaleur cv ON (cv.caractvalid=lfc.caractvalid) WHERE cv.caractid=22 AND lfc.lignefactureid=".$ligne['lignefactureid']);
		if($caract[0]=="")
			print "<tr><td colspan=\"2\">Garantie constructeur : <a href=\"../facture2.php?facturid=$facturid&passe=".$rs_facture['passefacture']."&nopub=1\">voir facture</a></td>";
		else
			print "<tr><td colspan=\"2\">Garantie constructeur : ".$caract['libelle_caractval']."</td>";
		
		$caract=$DB_site2->query_first("SELECT lfc.libelle_caract,lfc.libelle_caractval,cv.libelleFR FROM lignefacturecaracteristique lfc INNER JOIN caracteristiquevaleur cv ON (cv.caractvalid=lfc.caractvalid) WHERE cv.caractid=3 AND lfc.lignefactureid=".$ligne['lignefactureid']);
		if($caract[0]==""){
			print "<td colspan=\"2\">Garantie sérénité : aucune</td>";
		}else
			print "<td colspan=\"2\">Garantie sérénité : ".$caract['libelleFR']."</td>";
		print "</tr></table>";
	}

print "<input type=\"hidden\" name=\"sav_refs\" value=\"".implode(" - ",$refs)."\">
	</form>
</div>";

print "<div>
	<form action=\"facture.php?action=editer&facturid=$facturid&savid=".$sav->id."#sav\" method=\"post\" enctype=\"multipart/form-data\">
		<input type=\"hidden\" name=\"savid\" value=\"".$sav->id."\">
		<input type=\"hidden\" name=\"step\" value=\"2\">
		<table class=\"etape\">
			<tr>
				<td colspan=\"2\" class=\"etapetd\">Etape 1 : Informations client</td>
				<td class=\"etapesep\">&nbsp;</td>
				<td colspan=\"2\" class=\"etapetd\">Etape 2 : Validation des informations client</td>
			</tr>
			<tr>
				<td colspan=\"2\">Saisie par ".$sav->utilisateur." le ".date("d/m/Y à H:i",$sav->date_creation)."</td>
				<td class=\"etapesep\"></td>
				<td></td>
			</tr>
			<tr>
				<td>Date livraison</td>
				<td class=\"client\">".$sav->fields['sav_datelivr']."</td>
				<td class=\"etapesep\"></td>
				<td>Date livraison validée sur le site du transporteur</td>
				<td nowrap>".$sav->verif('sav_datelivr',"cal")."</td>
			</tr>
			<tr>
				<td>Date expédition de la commande</td>
				<td class=\"client\">".$rs_facture[dateexpedition]."</td>
				<td class=\"etapesep\"></td>
				<td></td>
				<td nowrap></td>
			</tr>
			<tr>
				<td>Date constatation panne</td>
				<td class=\"client\">".$sav->fields['sav_datelitige']."</td>
				<td class=\"etapesep\"></td>
				<td></td>
				<td nowrap></td>
			</tr>
			<tr>
				<td>N° de série de l'appareil</td>
				<td class=\"client\">".$sav->fields['sav_nbserie']."</td>
				<td class=\"etapesep\"></td>
				<td></td>
				<td nowrap>".$sav->verif('sav_nbserie')."</td>
			</tr>
			<tr>
				<td>Avez-vous conservé l'emballage ?</td>
				<td class=\"client\">".$sav->ouinon('sav_emballage')."</td>
				<td class=\"etapesep\"></td>
				<td></td>
				<td nowrap>".$sav->verif('sav_emballage',"ouinon")."</td>
			</tr>
			<tr>
				<td>Pays dans lequel se situe le produit</td>
				<td class=\"client\">".$sav->fields['sav_paysproduit']."</td>
				<td class=\"etapesep\"></td>
				<td></td>
				<td nowrap>".$sav->verif('sav_paysproduit')."</td>
			</tr>
			<tr>
				<td colspan=\"2\">Détail précis de votre demande :<br><span class=\"client\">".$sav->fields['sav_detail1']."</span></td>
				<td class=\"etapesep\"></td>
				<td colspan=\"2\">".$sav->textarea("sav_detail1-verif","sav_autosave('sav_detail1-verif')",array("cols"=>40,"rows"=>3))." ".$sav->wait("sav_detail1-verif")."</td>
			</tr>
			<tr>
				<td colspan=\"2\">Autres informations importantes :<br><span class=\"client\">".$sav->fields['sav_detail2']."</span></td>
				<td class=\"etapesep\"></td>
				<td colspan=\"2\">
	<a href=\"javascript:mail_enreg(undefined,'$rs_facture[mail]',undefined,19,'$user[langue]','base','&tracking_sav=SAV".$facturid."-".$sav->id."');mail_suivi('suivi_commande',$facturid);mail_services('$user[langue]','$user[site_code]');\">Mail demande d'explications et/ou photos pour le dossier</a><br>
					<a href=\"javascript:mail_enreg(undefined,'$rs_facture[mail]',undefined,108,'$user[langue]','base','&tracking_sav=SAV".$facturid."-".$sav->id."');mail_suivi('suivi_commande',$facturid);mail_services('$user[langue]','$user[site_code]');\">Refus de prise en charge sous garantie</a><br>
					<br>".$sav->filebox_button()."
				</td>
			</tr>
		</table>";
	if($sav->maxstep<2) print "<div style=\"text-align:center;padding:10px;\">
									<input type=\"submit\"value=\"Passer à l'étape suivante\">
								</div>";
	print "</form>
</div>";

$selectsav="<select name=\"sav_prischargesav\" onchange=\"sav_save('sav_prischargesav');document.getElementsByName('sav_prischargetel')[0].value=savtel[this.value];\"><option></option>";
foreach($savclient as $fournisseur=>$tel) $selectsav.="<option value=\"$fournisseur\"".($sav->fields['sav_prischargesav']==$fournisseur?" selected":"").">$fournisseur</option>";
$selectsav.="</select>";

if($sav->fields['sav_prischarge']==1) $telsav=$savclient[$sav->fields['sav_prischargesav']];
if(in_array($sav->fields['sav_prischarge'],array(2,3))) $telsav=$savclient['SFG'];

if($sav->maxstep>=2){
	print "<div class=\"advgroup\">
		<form method=\"post\">
			<input type=\"hidden\" name=\"savid\" value=\"".$sav->id."\">
			<input type=\"hidden\" name=\"step\" value=\"3\">
			<p>Etape 3 : Analyse de la réclamation par le SAV<span class=\"modifstep\">".$sav->steps[3]['utilisateur']." ".($sav->steps[3]['date']>0?date("d/m/Y",$sav->steps[3]['date']):"")."</span></p>
			<table style=\"border:solid thin #aaa;\">
				<tr>
					<td>Il s'agit : ".$sav->wait("sav_garantie")."</td>
					<td>".$sav->radio_inputs("sav_garantie",array(""=>1),"sav_save('sav_garantie')")."</td>
					<td>Sous garantie fabricant</td>
					<td>".$sav->radio("sav_garantie",array(""=>2),"sav_save('sav_garantie')")."</td>
					<td>Sous garantie SFG / UK Warranty</td>
					<td>".$sav->radio("sav_garantie",array(""=>3),"sav_save('sav_garantie')")."</td>
					<td>Hors garantie</td>
					<td>".$sav->radio("sav_garantie",array(""=>4),"sav_save('sav_garantie')")."</td>
					<td>D'une panne à la mise en service (7 jours à partir de la livraison)</td>
				</tr>
				<tr>
					<td>Le client doit être pris en charge par : ".$sav->wait("sav_prischarge").$sav->wait("sav_prischargesav")."</td>
					<td>".$sav->radio_inputs("sav_prischarge",array(""=>1),"sav_save('sav_prischarge')")."</td>
					<td>Le SAV du fabricant<br>$selectsav</td>
					<td>".$sav->radio("sav_prischarge",array(""=>2),"sav_save('sav_prischarge');document.getElementsByName('sav_prischargetel')[0].value=savtel['SFG'];")."</td>
					<td>SFG / UK Warranty (sous garantie)</td>
					<td>".$sav->radio("sav_prischarge",array(""=>3),"sav_save('sav_prischarge');document.getElementsByName('sav_prischargetel')[0].value=savtel['SFG'];")."</td>
					<td>SFG / UK Warranty (hors garantie)</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
				</tr>
			</table>
			Expiration de la garantie constructeur ".$sav->cal_input("sav_fingarfourn","sav_calsave")." ".$sav->wait("sav_fingarfourn")."
			<br>Garantie sérénité SFG départ ".$sav->cal_input("sav_debutgarsfg","sav_calsave")." ".$sav->wait("sav_debutgarsfg")." - Date de fin ".$sav->cal_input("sav_fingarsfg","sav_calsave")." ".$sav->wait("sav_fingarsfg");

  if($sav->maxstep<3) print "<div style=\"text-align:center;padding:10px;\">
								<input type=\"submit\"value=\"Passer à l'étape suivante\"></div>";
  print "</form>
  </div>";
}

if($sav->maxstep>=3){
	print "<div class=\"advgroup\">
		<form method=\"post\">
			<input type=\"hidden\" name=\"savid\" value=\"".$sav->id."\">
			<input type=\"hidden\" name=\"step\" value=\"4\">
			<p>Etape 4 : Suivi du dossier de panne<span class=\"modifstep\">".$sav->steps[4]['utilisateur']." ".($sav->steps[4]['date']>0?date("d/m/Y",$sav->steps[4]['date']):"")."</span></p>";
	if(file_exists("sfg_pdf/$facturid.pdf")) print "<div><a href=\"sfg_pdf/$facturid.pdf\">Télécharger le contrat de garantie SFG</a><br>&nbsp;</div>";

	print "<div style=\"width:49%;float:left;border-right:solid thin black;test-align:left;\">
		<b>Dossier client :</b>
		<br><br>";
    switch($sav->fields['sav_garantie']){
        case 1:
            ?>
            <b>N° de téléphone à donner au client</b> <input type="text" name="sav_prischargetel" size="10" value="<?php echo $telsav; ?>" readonly>
            <br>
            <a href="javascript:mail_enreg(undefined,'<?php echo $rs_facture['mail']; ?>',undefined,20,'<?php echo $user['langue']; ?>','base','<?php echo "&tracking_sav=SAV".$facturid."-".$sav->id ?>'); mail_suivi('suivi_commande',<?php echo $facturid; ?>); mail_services('<?php echo $user['langue']; ?>','<?php echo $user['site_code']; ?>');">Envoyer confirmation ouverture réclamation panne sous garantie fabricant</a>
            <?php
            break;
		case 2:
			$contratsfg="";
			if(file_exists("sfg_pdf/".$facturid.".pdf")) $contratsfg="addserverpj('Contrat SFG $facturid.pdf','sfg_pdf/".$facturid.".pdf');";
			print "<b>N° de téléphone à donner au client</b> <input type=\"text\" name=\"sav_prischargetel\" size=\"10\" value=\"".$telsav."\" readonly>
			<br><a href=\"javascript:mail_enreg(undefined,'$rs_facture[mail]',undefined,21,'$user[langue]','base','&tracking_sav=SAV".$facturid."-".$sav->id."');mail_suivi('suivi_commande',$facturid);mail_services('$user[langue]','$user[site_code]');$contratsfg\">Envoyer confirmation ouverture réclamation panne sous garantie sérénité SFG</a>";
			break;
		case 3:
			print "<b>N° de téléphone à donner au client</b> <input type=\"text\" name=\"sav_prischargetel\" size=\"10\" value=\"".$telsav."\" readonly>
			<br><a href=\"javascript:mail_enreg(undefined,'$rs_facture[mail]',undefined,22,'$user[langue]','base','&tracking_sav=SAV".$facturid."-".$sav->id."');mail_suivi('suivi_commande',$facturid);mail_services('$user[langue]','$user[site_code]');\">Mail client panne hors garantie</a>";
			break;
		case 4:
			print "<b>N° de téléphone à donner au client</b> <input type=\"text\" name=\"sav_prischargetel\" size=\"10\" value=\"".$telsav."\" readonly>
			<br>Si besoin : <a href=\"javascript:mail_enreg(undefined,'$rs_facture[mail]',undefined,34,'$user[langue]','base','&tracking_sav=SAV".$facturid."-".$sav->id."');mail_suivi('suivi_commande',$facturid);mail_services('$user[langue]','$user[site_code]');\">Envoi mail procédure client (panne déballage)</a>
			<br><br>Lettre réclamation client envoyée sous 7 jours (panne déballage) : ".$sav->radio_inputs('sav_lettrereclam7j',array("Non"=>1,"Oui"=>2),"sav_save('sav_lettrereclam7j')")." ".$sav->wait("sav_lettrereclam7j")."
			<br><br><a href=\"javascript:mail_enreg(undefined,'$rs_facture[mail]',undefined,45,'$user[langue]','base','&tracking_sav=SAV".$facturid."-".$sav->id."');addserverpj('Facture $facturid.pdf','factures/$facturid.pdf');mail_suivi('suivi_commande',$facturid);mail_services('$user[langue]','$user[site_code]');\">Envoi confirmation prise en charge panne déballage</a>";
			break;
    }
	?>
    <br><br>
    Date d'envoi : <?php echo $sav->cal_input("sav_envoimailgarantie","sav_calsave"); ?>
    <br><br>
    <a href="javascript:mail_enreg(undefined,'',undefined,160,'<?php echo $user['langue']; ?>',undefined,'<?php echo "&tracking_sav=SAV".$facturid."-".$sav->id; ?>'); mail_suivi('suivi_commande',<?php echo $facturid; ?>); mail_services('<?php echo $user['langue']; ?>','<?php echo $user['site_code']; ?>');">Envoyer demande d'intervention au SAV fabricant ou SFG / UK Warranty</a>
	
    <?php
    print "<div style=\"margin-top:10px;text-align:left;height:200px;overflow:auto;border:solid thin #CCCCCC;\">";
    $i=-1;
    foreach($savclient as $fournisseur=>$tel){
		$i++;
		print "<br>$fournisseur : ".autofrtel($tel);
    }
    print "</div>";

	print "<br><a href=\"javascript:mail_enreg(undefined,'$rs_facture[mail]',undefined,53,'$user[langue]',undefined,'&tracking_sav=SAV".$facturid."-".$sav->id."');mail_suivi('suivi_commande',$facturid);mail_services('$user[langue]','$user[site_code]');\">Demande d'envoi plaque signalétique</a>";
	if(file_exists("sfg_pdf/".$facturid.".pdf")) print "<br><a href=\"javascript:mail_enreg(undefined,'$rs_facture[mail]',undefined,54,'$user[langue]', undefined, '&tracking_sav=SAV".$facturid."-".$sav->id."');addserverpj('Contrat n°$facturid.pdf','sfg_pdf/$facturid.pdf');mail_suivi('suivi_commande',$facturid);mail_services('$user[langue]','$user[site_code]');\">Envoi contrat SFG</a> &nbsp; <a href=\"sfg_pdf/$facturid.pdf\">Télécharger le contrat</a>";
	print "<br><br><a href=\"javascript:mail_enreg(undefined,'$rs_facture[mail]',undefined,78,'$user[langue]', undefined,'&tracking_sav=SAV".$facturid."-".$sav->id."');mail_suivi('suivi_commande',$facturid);mail_services('$user[langue]','$user[site_code]');\">Suivi de votre dossier suite ouverture SAV</a>
	<br><a href=\"javascript:mail_enreg(undefined,'$rs_facture[mail]',undefined,79,'$user[langue]',undefined,'&tracking_sav=SAV".$facturid."-".$sav->id."');mail_suivi('suivi_commande',$facturid);mail_services('$user[langue]','$user[site_code]');\">Mail au client suite prise de rdv avec un technicien</a>";
	print "</div>
	<div style=\"width:50%;float:left;margin-left:2px;text-align:left;\">
		<b>Dossier fournisseur :</b><br>";
		if($sav->fields['sav_garantie']==4){
			print "<br><font style=\"color:#AA3366;font-weight:bold;\">Ne pas oublier de joindre le bon de transport par mail si disponible</font>
			<br><br><a href=\"javascript:mail_enreg(undefined,undefined,undefined,44,'$user[langue]',undefined,'&tracking_sav=SAV".$facturid."-".$sav->id."');mail_suivi('suivi_commande',$facturid);mail_services('$user[langue]','$user[site_code]');\">Générer mail panne déballage au fabricant</a>";
		}
	print "<hr><br><br><center><a href=\"javascript:mail_enreg(undefined,undefined,undefined,23,'$user[langue]', undefined, '&tracking_sav=SAV".$facturid."-".$sav->id."');mail_suivi('suivi_commande',$facturid);mail_services('$user[langue]','$user[site_code]');\">Mail relance point SAV</a></center>
		<hr><br>Panne prise en charge par fabricant / SFG / UK Warranty :
		<br>".$sav->radio_inputs("sav_litigeaccept",array("Oui"=>1,"Non"=>0),"sav_save('sav_litigeaccept')")." ".$sav->wait("sav_litigeaccept")."
		<br>Commentaire :".$sav->wait("sav_motifrefus")."<br>".$sav->textarea("sav_motifrefus","sav_autosave('sav_motifrefus')",array("cols"=>40,"rows"=>3))."
		<br><br>n°dossier SAV fabricant ".$sav->text_input('sav_ndossier',"sav_autosave('sav_ndossier')").$sav->wait("sav_ndossier")."
   </div>
	<div style=\"clear:both;\"></div>";
		/*print "<br><b>Lettre client envoyée au fabricant</b>
		<table>
			<tr><td>".$sav->checkbox('sav_lettrereclam',"sav_save('sav_lettrereclam')")." ".$sav->wait("sav_lettrereclam")." Lettre réclamation client envoyée</td>
				<td>".$sav->checkbox('sav_lettrereclamacilyon',"sav_save('sav_lettrereclamacilyon')")." ".$sav->wait("sav_lettrereclamacilyon")." Lettre réclamation client envoyée par Acilyon</td></tr>
			<tr><td>".$sav->checkbox('sav_lettrereclamhd',"sav_save('sav_lettrereclamhd')")." ".$sav->wait("sav_lettrereclamhd")." Lettre réclamation client hors délai (panne déballage)</td>
				<td></td></tr>
			<tr><td>".$sav->checkbox('sav_lettrereclam0',"sav_save('sav_lettrereclam0')")." ".$sav->wait("sav_lettrereclam0")." Pas de lettre de réclamation à envoyer</td></tr>
		</table>
		".$sav->filebox_button();*/
		if($sav->maxstep<4) print "<div style=\"text-align:center;padding:10px;\"><input type=\"submit\"value=\"Passer à l'étape suivante\"></div>";
		print "</form>
	</div>";
}

if($sav->maxstep>=4){
	/*print "<div class=\"advgroup\">
	<form method=\"post\">
		<input type=\"hidden\" name=\"savid\" value=\"$savid\">
		<input type=\"hidden\" name=\"step\" value=\"5\">
		<p>Etape 5 : Montage du dossier litige</p>
		<b>Analyse préliminaire :</b>
		<div style=\"margin-top:8px;margin-bottom:48px;font-size:14px;line-height:22px;\">
			".$sav->checkbox('sav_pbtemp',"sav_save('sav_pbtemp')")."<b>La cave est mal régulée (problème de température)</b>".$sav->wait("sav_pbtemp")."
			<div style=\"margin-left:20px;\">".$sav->checkbox('sav_mauvutil',"sav_save('sav_mauvutil')")." - Solution possible : Mauvaise utilisation ou connaisse du produit, demander au client de voir la notice d'utilisaiton ".$sav->wait("sav_mauvutil")."</div>
			<div style=\"margin-left:20px;\">".$sav->checkbox('sav_patienter',"sav_save('sav_patienter')")." - Si appel client au bout de 2 ou 3 jours, lui demander de patienter car la régulation peut prendre plus de temps. Lui demander de rappeler d'ici plusieurs jours. ".$sav->wait("sav_patienter")."</div>
			<br>".$sav->checkbox('sav_bruyant',"sav_save('sav_bruyant')")." <b>Produit très bruyant</b> : la plupart temps, c'est normal (à voir selon le modèle, si modèle bruyant). ".$sav->wait("sav_bruyant")."
		</div>
		<div style=\"margin:8px;\">Sinon, faire l'analyse suivante :</div>
		<b>Détermination action et responsabilité</b>
		<table>
			<tr><td>".$sav->checkbox('sav_chk220',"sav_save('sav_chk220')")." Remplacement produit (+ récupération ancien)</td><td>".$sav->select('sav_fourn220',$fournisseurs,"sav_save('sav_fourn220')")." ".$sav->wait("sav_fourn220")."</td></tr>
			<tr><td>".$sav->checkbox('sav_chk221',"sav_save('sav_chk221')")." Echange produit (+ récupération ancien)</td><td>".$sav->select('sav_fourn221',$fournisseurs,"sav_save('sav_fourn221')")." ".$sav->wait("sav_fourn221")."</td></tr>
			<tr><td>".$sav->checkbox('sav_chk222',"sav_save('sav_chk222')")." Récupération produit suite annulation</td><td>".$sav->select('sav_fourn222',$fournisseurs,"sav_save('sav_fourn222')")." ".$sav->wait("sav_fourn222")."</td></tr>
			<tr><td>".$sav->checkbox('sav_chk223',"sav_save('sav_chk223')")." Geste commercial<br>montant et prise en charge : ".$sav->text_input('sav_geste223',"sav_autosave('sav_geste223')")."</td><td>".$sav->select('sav_fourn223',$fournisseurs,"sav_save('sav_fourn223')")." ".$sav->wait("sav_fourn223")."</td></tr>
			<tr><td>".$sav->checkbox('sav_chk224',"sav_save('sav_chk224')")." Geste commercial<br>montant et prise en charge : ".$sav->text_input('sav_geste224',"sav_autosave('sav_geste224')")."</td><td>".$sav->select('sav_fourn224',$fournisseurs,"sav_save('sav_fourn224')")." ".$sav->wait("sav_fourn224")."</td></tr>
			<tr><td>".$sav->checkbox('sav_chk225',"sav_save('sav_chk225')")." Remboursement<br>raison : ".$sav->text_input('sav_raison225',"sav_autosave('sav_raison225')")."</td></tr>
			<tr><td>".$sav->checkbox('sav_chk226',"sav_save('sav_chk226')")." Aucune suite, dossier à solder<br>raison : ".$sav->text_input('sav_raison225',"sav_autosave('sav_raison225')")."</td></tr>
			<tr><td>".$sav->checkbox('sav_chk227',"sav_save('sav_chk227')")." Réparation chez client</td><td>".$sav->select('sav_fourn227',$fournisseurs,"sav_save('sav_fourn227')")." ".$sav->wait("sav_fourn227")."</td></tr>
			<tr><td>".$sav->checkbox('sav_chk228',"sav_save('sav_chk228')")." Réparation chez constructeur</td><td>".$sav->select('sav_fourn228',$fournisseurs,"sav_save('sav_fourn228')")." ".$sav->wait("sav_fourn228")."</td></tr>
			<tr><td>".$sav->checkbox('sav_chk229',"sav_save('sav_chk229')")." Autre ".$sav->text_input('sav_autre22',"sav_autosave('sav_autre22')")."</td></tr>
			<tr><td>".$sav->checkbox('sav_chk2210',"sav_save('sav_chk2210')")." Prise en charge panne par SAV fabricant - client renvoyé vers SAV fabricant, Voir étape 7 directement</td><td>".$sav->select('sav_fourn2210',$fournisseurs,"sav_save('sav_fourn2210')")." ".$sav->wait("sav_fourn2210")."</td></tr>
			<tr><td>".$sav->checkbox('sav_chk2211',"sav_save('sav_chk2211')")." Prise en charge panne par SFG - client renvoyé vers SFG</td></tr>
		</table>
		<div style=\"margin-top:10px;\">
			<b>N° de téléphone à donner au client :</b>
		</div>
		<div style=\"float:left;width:50%;\">";
		$i=-1;
		foreach($savclient as $fournisseur=>$tel){
			$i++;
			if($i==round(count($savclient)/2)) print "</div><div style=\"float:left;width:50%;\">";
			print "<br>$fournisseur : ".autofrtel($tel);
		}
		$total=0;
		for($i=0;$i<=7;$i++) $total+=floatval($sav->fields['sav_val22'.$i]);
		print "</div>
		<div style=\"clear:both\"></div>
		<br><b>Analyse du coût prévisionnel du litige</b>
		<table>
			<tr><td>Prix achat cave ".$sav->wait("sav_val220")."</td><td>".$sav->nb_input("sav_val220","sav_autosave('sav_val220');calcsum22()")."</td></tr>
			<tr><td>Réparation ".$sav->wait("sav_val221")."</td><td>".$sav->nb_input("sav_val221","sav_autosave('sav_val221');calcsum22()")."</td></tr>
			<tr><td>Transport aller ".$sav->wait("sav_val222")."</td><td>".$sav->nb_input("sav_val222","sav_autosave('sav_val222');calcsum22()")."</td></tr>
			<tr><td>Transport retour ".$sav->wait("sav_val223")."</td><td>".$sav->nb_input("sav_val223","sav_autosave('sav_val223');calcsum22()")."</td></tr>
			<tr><td>Geste commercial ".$sav->wait("sav_val224")."</td><td>".$sav->nb_input("sav_val224","sav_autosave('sav_val224');calcsum22()")."</td></tr>
			<tr><td>Geste commercial ".$sav->wait("sav_val225")."</td><td>".$sav->nb_input("sav_val225","sav_autosave('sav_val225');calcsum22()")."</td></tr>
			<tr><td>Remboursement ".$sav->wait("sav_val226")."</td><td>".$sav->nb_input("sav_val226","sav_autosave('sav_val226');calcsum22()")."</td></tr>
			<tr><td>Autre : ".$sav->text_input('sav_autreval22',"sav_autosave('sav_autreval22');calcsum22()")." ".$sav->wait("sav_val227")."</td><td>".$sav->nb_input("sav_val227","sav_autosave('sav_val227');calcsum22()")."</td></tr>
			<tr><td>Total HT : </td><td id=\"resultsum22\">".round($total,2)." &euro;</td></tr>
		</table>
		Relance point SAV fabricant
		<div style=\"margin-top:10px;\">
			Commentaires INTERNES :<br>".$sav->textarea('sav_com2',"sav_autosave('sav_com2')",array("cols"=>70,"rows"=>4))." ".$sav->wait("sav_com2");
		if($sav->maxstep<5) print "<div style=\"text-align:center;padding:10px;\"><input type=\"submit\"value=\"Passer à l'étape suivante\"></div>";
	print "</form>
	</div>";*/

foreach($sav->lignes as $ligneid=>$qte){
	$table="<table>
		<tr>
			<td>Article</td>
			<td>Fournisseur</td>
			<td>Prix</td>
			<td>Demande<br>avoir</td>
			<td>Info compta</td>
		</tr>";
	$sql="";
	if($sav->fields['sav_prischarge']!="") $sql=" AND ef.destinataire='".$sav->fields['sav_prischarge']."'";
	$r=$DB_site2->query("SELECT efl.qte,efl.facture,ef.destinataire,efl.paht,ef.comment,efl.id,lf.artcode,efl.efid FROM envoi_fournisseur_ligne efl INNER JOIN envoi_fournisseur ef ON(ef.id=efl.efid) INNER JOIN lignefacture lf ON(efl.ligne_facture_id=lf.lignefactureid) WHERE ef.destinataire<>'0' AND ligne_facture_id=".intval($ligneid).$sql);
	while($efl=$DB_site2->fetch_array($r)){
		$icon="";
		$info="<textarea name=\"comment".$efl['efid']."\" cols=\"20\" rows=\"2\">".$efl['comment']."</textarea><br><a href=\"javascript:updeflcomment($efl[efid],'".$sav->fields['sav_nbon_verif']."','".$sav->fields['sav_ndossier']."')\">Ajouter l'info</a>";
		if($efl['facture']!=""){
			$avoir=$DB_site2->query_first("SELECT id FROM avoir_ligne WHERE eflid=".$efl['id']);
			if($avoir['id']!="") $icon="<img src=\"images/coche_verte.svg\" style=\"width: 20px;\">";
			$info="<a href=\"avoir_fournisseur.php?action=ajouter&ffid=".$efl['facture']."\" target=\"_blank\">".$efl['facture']."</a>";
		}
		$table.="<tr>
			<td>".$efl['artcode']."</td>
			<td>".$efl['destinataire']."</td>
			<td>".round($efl['qte']*$efl['paht'],2)."</td>
			<td>$icon</td>
			<td>".$info."</td>
		</tr>";
	}
   $table.="</table>";
}

print "<div class=\"advgroup\">
	<script language=\"javascript\">
		window.onfocus=function(){
			refreshpj();
		}
	</script>
	<form method=\"post\">
		<input type=\"hidden\" name=\"savid\" value=\"".$sav->id."\">
		<input type=\"hidden\" name=\"step\" value=\"5\">
		<p>Etape 5 : En cas de panne irréparable<span class=\"modifstep\">".$sav->steps[5]['utilisateur']." ".($sav->steps[5]['date']>0?date("d/m/Y",$sav->steps[5]['date']):"")."</span></p>
		<div style=\"width:49%;float:left;border-right:solid thin black;test-align:left;\">
			<br><b>Pièces à joindre au dossier :</b>
			<br>".$sav->checkbox('sav_nbserie',"sav_save('sav_nbserie')")." Numéro de série ".$sav->wait("sav_nbserie")."
			<br>".$sav->checkbox('sav_facturefourn',"sav_save('sav_facturefourn')")." Copie facture fournisseur ".$sav->wait("sav_facturefourn")."
			<br>".$sav->checkbox('sav_factureclient',"sav_save('sav_factureclient')")." <a href=\"../facture2.php?facturid=$facturid&passe=$rs_facture[passefacture]\">Facture client Ma Cave à Vin</a> ".$sav->wait("sav_factureclient")."
			<br>".$sav->checkbox('sav_photos',"sav_save('sav_photos')")." Facultatif : photos ".$sav->wait("sav_photos")."
			<br>".$sav->checkbox('sav_lettreexpl',"sav_save('sav_lettreexpl')")." Lettre explicative du problème (par le client ou Acilyon) ".$sav->wait("sav_lettreexpl")."
			<br>
			<br><b>Les pièces du dossier :</b> <img src=\"images/refresh.svg\" height=\"24\" align=\"absmiddle\" onclick=\"refreshpj()\"> ".$sav->filebox_button()."
			<div style=\"margin:0px;padding:0px;\" id=\"et6tmppj\">";
			$url="http://".$_SERVER['SERVER_NAME']."/facture2.php?facturid=$facturid&passe=$rs_facture[passefacture]&nopub=1";
			$name="Facture $facturid.pdf";
			print "<br><input type=\"checkbox\" name=\"checkboxfile\" value=\"$facturid\"><input type=\"hidden\" id=\"filename$facturid\" value=\"".$name."\"><input type=\"hidden\" id=\"fileurl$facturid\" value=\"".$url."\"> <a href=\"".$url."\" target=\"_blank\">".$name."</a>";
      
			/*$requete=$DB_site2->query("SELECT * FROM facture WHERE userid<>".intval($rs_facture['userid'])." AND provenance='sav' AND provenanceid=".$facturid);
			while($fsav=$DB_site2->fetch_array($requete)){
				$url="http://".$_SERVER['SERVER_NAME']."/facture2.php?facturid=$fsav[facturid]&passe=$fsav[passefacture]&nopub=1";
				$name="Facture SAV $fsav[facturid].pdf";
				print "<br><input type=\"checkbox\" name=\"checkboxfile\" value=\"$fsav[facturid]\"><input type=\"hidden\" id=\"filename$fsav[facturid]\" value=\"".$name."\"><input type=\"hidden\" id=\"fileurl$fsav[facturid]\" value=\"".$url."\"> <a href=\"".$url."\" target=\"_blank\">".$name."</a>";
			}*/

			$pjfiles=$DB_site2->query("SELECT * FROM sav_files WHERE savid=$savid AND state=1 ORDER BY date");
			while($pj=$DB_site2->fetch_array($pjfiles)){
				$js.="serverpj[$pj[id]]=new Array();
				serverpj[$pj[id]]['url']=\"sav/$pj[id].$pj[ext]\";
				serverpj[$pj[id]]['name']=\"$pj[name]\";\n";
				print "<br><input type=\"checkbox\" name=\"et6pj\" value=\"$pj[id]\" ".($pj['size']>10785760?"style=\"visibility:hidden\"":"")."><a href=\"sav/$pj[id].$pj[ext]\" target=\"_blank\">$pj[name]</a> ".round($pj['size']/1024)." ko <img src=\"images/supprimer.gif\" title=\"Supprimer\" onclick=\"delpj($pj[id])\">";
			}
			print $sav->checkbox_files();
			print "</div>
		<br><a href=\"javascript:void(0)\" onclick=\"maildossier(35)\">AR à envoyer par mail</a>
		<br><a href=\"javascript:void(0)\" onclick=\"maildossier(80)\">Mail au client pour envoi chèque cadeau - produit à remplacer, n'est plus fabriqué</a>
		<br><a href=\"javascript:void(0)\" onclick=\"maildossier(81)\">Mail au client pour envoi chèque cadeau - produit à remplacer, vétusté applicable</a>";
		if(file_exists("sfg_pdf/".$facturid.".pdf")) print "<br><br><center><a href=\"javascript:annulesfg($facturid)\" style=\"color:red;font-weight:bold;\">Annuler le contrat SFG</a></center>";
      ?>
	  
		</div>
		<div style="width:50%;float:left;margin-left:2px;text-align:left;">
			<a href="facture.php?action=creer&createfrom=<?php echo $facturid; ?>" target="_blank">Création nouvelle commande</a>
			<br>
			<a href="facture.php?action=creer&createfromuser=<?php echo $facturid; ?>" target="_blank">Création avoir</a>
			<?php if($sav->fields['sav_garantie'] == 2){ ?>
			<br><br>
			<a href="javascript:mail_enreg(undefined,'<?php echo $rs_facture['mail']; ?>',undefined,161,'<?php echo $user['langue']; ?>',undefined,'<?php echo "&tracking_sav=SAV".$facturid."-".$sav->id; ?>'); mail_suivi('suivi_commande',<?php echo $facturid; ?>); mail_services('<?php echo $user['langue']; ?>','<?php echo $user['site_code']; ?>');">Envoyer mail au client pour envoi chèque cadeau suite courrier valorisation SFG / UK Warranty</a>
			<?php } ?>
			<br>
			&nbsp;
			<?php echo $table; ?>
		</div>
		<div style="clear:both;"></div>
		<div style="margin:10px;font-style:italic;">Panne sans garantie PMOD, le client doit prendre à sa charge le déplacement des techniciens nécessaires à la réparation de la cave. Le fabricant ne prend à sa charge que le remplacement des pièces.</div>
		
		<?php
		if($sav->maxstep<5) print "<div style=\"text-align:center;padding:10px;\"><input type=\"submit\"value=\"Passer à l'étape suivante\"></div>";
	print "</form>
	</div>";
}

$f=new facture($DB_site2);
$factures=$f->search("provenance='sav' AND provenanceid=".$facturid);
foreach($factures as $facturid=>$facture){
	if($facture->userid==$rs_facture['userid'])
		$savfacture["user"].="<a href=\"facture.php?action=editer&facturid=$facturid\" target=\"_blank\">Commande n°".$facturid." ".$facture->totalttc." &euro; : ".$facture->validetxt."</a><br>";
	else
		$savfacture["fourn"].="<a href=\"facture.php?action=editer&facturid=$facturid\" target=\"_blank\">Commande ".$facture->utilisateur->raisonsociale." n°".$facturid." ".$facture->totalttc." &euro; : ".$facture->validetxt."</a><br>";
}

if($sav->maxstep>=5){
	print "<div class=\"advgroup\" style=\"padding: 5px;\">
		<form name=\"et5form\" method=\"post\" id=\"et5form\">
			<input type=\"hidden\" name=\"savid\" value=\"".$sav->id."\">
			<input type=\"hidden\" name=\"step\" value=\"6\">
			<input type=\"hidden\" name=\"date_fin\" value=\"".($sav->date_fin>0?$sav->date_fin:0)."\">
			<p>Etape 6 : Cloture du dossier litige<span class=\"modifstep\">".$sav->steps[6]['utilisateur']." ".($sav->steps[6]['date']>0?date("d/m/Y",$sav->steps[6]['date']):"")."</span></p>
			<div>Analyse financière du dossier litige :<br>&nbsp;</div>
			<div style=\"width:49%;float:left;\">
				<b>Du coté du client</b><br>
				<br>".$savfacture['user']."
				<br>Confirmation client que le produit a été réparé : 
				<br>le ".$sav->cal_input('sav_daterepareclient',"sav_calsave")." <span id=\"sav_daterepareclient-ok\"></span>
				<br><br>OU
				<br><br>Confirmation client que le nouveau produit a été échangé :
				<br>le ".$sav->cal_input('sav_daterembourse',"sav_calsave")." <span id=\"sav_daterembourse-ok\"></span>
			</div>
			<div style=\"width:50%;float:left;margin-left:2px;border-left:solid thin black;\">
				<b>Du coté fournisseur</b><br>
				<br>".$savfacture['fourn']."
				<br><br>Confirmation fournisseur que le produit a été réparé :
				<br>le ".$sav->cal_input('sav_daterepare',"sav_calsave")." <span id=\"sav_daterepare-ok\"></span>
				<br><br>OU
				<br><br>Confirmation que le produit irréparable a été retourné au fabricant :
				<br>le ".$sav->cal_input('sav_dateretourne',"sav_calsave")." <span id=\"sav_dateretourne-ok\"></span>
			</div>
			<div style=\"clear:both;\"></div>";
	print "<div style=\"margin-top:10px;\">
				<br><br><font style=\"color:#990000;font-weight:bold;\">Avant de clôturer un dossier, s'assurer que :</font>
				<table>
					<tr>
						<td>Aucun frais ne nous ont été facturés concernant la réparation des produits</td>
						<td nowrap>".$sav->radio_inputs("sav_verif1",array("Non"=>1,"Oui"=>2),"sav_save('sav_verif1');")."</td>
						<td>".$sav->wait("sav_verif1").$sav->textarea("sav_verif1txt","sav_autosave('sav_verif1txt')",array("cols"=>30,"rows"=>2))."</td>
						<td><span id=\"sav_verif1-date\">".$sav->modiffield('sav_verif1')."</span></td>
					</tr>
					<tr>
						<td>Les garanties ne nous ont pas été facturées plusieurs fois : si un produit a été remplacé, la garantie (SFG / UK Warranty ou constructeur) ne doit pas nous être facturées plusieurs fois</td>
						<td nowrap>".$sav->radio_inputs("sav_verif2",array("Non"=>1,"Oui"=>2),"sav_save('sav_verif2');")."</td>
						<td>".$sav->wait("sav_verif2").$sav->textarea("sav_verif2txt","sav_autosave('sav_verif2txt')",array("cols"=>30,"rows"=>2))."</td>
						<td><span id=\"sav_verif2-date\">".$sav->modiffield('sav_verif2')."</span></td>
					</tr>
					<tr>
						<td>En cas de remplacement du produit par une autre référence, s'assurer que l'on a obtenu un avoir sur la garantie SFG / UK Warranty ou constructeur du produit commandé initialement</td>
						<td nowrap>".$sav->radio_inputs("sav_verif3",array("Non"=>1,"Oui"=>2),"sav_save('sav_verif3');")."</td>
						<td>".$sav->wait("sav_verif3").$sav->textarea("sav_verif3txt","sav_autosave('sav_verif3txt')",array("cols"=>30,"rows"=>2))."</td>
						<td><span id=\"sav_verif3-date\">".$sav->modiffield('sav_verif3')."</span></td>
					</tr>
					<tr>
						<td>Aucun frais de transport ne nous a été facturé, ou les avoirs correspondants ont été enregistrés</td>
						<td nowrap>".$sav->radio_inputs("sav_verif4",array("Non"=>1,"Oui"=>2),"sav_save('sav_verif4');")."</td>
						<td>".$sav->wait("sav_verif4").$sav->textarea("sav_verif4txt","sav_autosave('sav_verif4txt')",array("cols"=>30,"rows"=>2))."</td>
						<td><span id=\"sav_verif4-date\">".$sav->modiffield('sav_verif4')."</span></td>
					</tr>
					<tr>
						<td>En cas de remplacement produit, s'assurer que les avoirs sur les produits commandés initialement ont bien été enregistrés</td>
						<td nowrap>".$sav->radio_inputs("sav_verif5",array("Non"=>1,"Oui"=>2),"sav_save('sav_verif5');")."</td>
						<td>".$sav->wait("sav_verif5").$sav->textarea("sav_verif5txt","sav_autosave('sav_verif5txt')",array("cols"=>30,"rows"=>2))."</td>
						<td><span id=\"sav_verif5-date\">".$sav->modiffield('sav_verif5')."</span></td>
					</tr>
				</table>
			</div>";


	print "<div id=\"ffourn2\"><img src=\"images/plus.jpg\" align=\"absmiddle\" onclick=\"document.getElementById('ffourn2').style.display='none';document.getElementById('ffourn').style.display='block';\"> Récapitulatif commandes</div>
	<div id=\"ffourn\" style=\"display:none;\"><img src=\"images/moins.jpg\" align=\"absmiddle\" onclick=\"document.getElementById('ffourn2').style.display='block';document.getElementById('ffourn').style.display='none';\"> Récapitulatif commandes".$recapcommandes."</div>";
	?>
    <?php
            //Si le SAV n'a pas été clôturé,
            //On affiche l'encart de mise à jour des statuts
            if($sav->date_fin == 0)
            { 
                echo ""
                . "<div class=\"cloture_sav\">" 
                    . "<input type=\"hidden\" name=\"action_sav\" id=\"action_cloture\" value=\"\">";

                    //Si la demande de clôture n'a pas été passé,
                    //On affiche les options permettant de le faire
                    if($sav->date_demande_cloture == 0 || !isset($sav->date_demande_cloture))
                    { 
                        echo ""
                        . "<p class=\"input\" style=\"color: red;\"><input type=\"radio\" value=\"1\" name=\"precloture\"> <u><strong>Attention</strong>, ce litige à entrainé des problèmes de marge et doit être analysé.</u></p>"
                        . "<p  class=\"input\" style=\"color: green;\"><input type=\"radio\" value=\"2\" name=\"precloture\"> Ce litige ne comporte aucune anomalie.</p>"
                        . "<p><input type=\"button\" class=\"cloture_demande\" onclick=\"cloture_demande();\" value=\"Demander la clotûre\" name=\"precloture\"></p>";
                    } 
                    //Si la demande de clôture a été faite,
                    //On masque l'option précédemment choisi
                    //Et on donne la possibilité de sélectionner l'autre
                    //ET de mettre à jour le statut
                    else
                    {
                        if($sav->sav_analyse == 1)
                        {
                            echo "<p class=\"input\" style=\"color: green;\"><input type=\"radio\" value=\"2\" name=\"precloture\"> Le litige ne comporte plus d'anomalie de rentabilité et peut être clôturé.</p>";
                        }
                        else
                        {
                            echo "<p class=\"input\" style=\"color: red;\"><input type=\"radio\" value=\"1\" name=\"precloture\"> Le litige comporte une anomalie de rentabilité et et doit être analysé avant clôture.</p>";
                        }

                        echo ""
                        . "<p>"
                            . "<input type=\"button\" class=\"sav_update\" onclick=\"if(confirm('Valider la mise à jour ?')){ document.getElementById('action_cloture').value = 'precloture';  document.forms['et5form'].submit(); }\" value=\"Mettre à jour le statut\" name=\"precloture\">"
                            . "<input type=\"button\" class=\"sav_unclose\" onclick=\"if(confirm('Annuler la demande de clôture ?')){ document.getElementById('action_cloture').value = 'cloture_annule';  document.forms['et5form'].submit(); }\" value=\"Annuler la demande de clotûre\" name=\"rouvrir\">"
                        . "</p>";
                    }

                echo "</div>";
            } 

            echo "<div class=\"sav_statuts_resume\">";

            if((isset($sav->date_demande_cloture) && $sav->date_demande_cloture != 0) || $sav->date_fin != 0)
            {
                echo "<p>";

                if(isset($sav->date_demande_cloture) && $sav->date_demande_cloture != 0) echo "<span>Demande de clôture le " . $sav->date_demande_cloture . "</span>";
                if($sav->date_fin !== 0) echo "<span>Clôturé le " . date("d/m/Y H:i",$sav->date_fin) . "</span>";

                echo "</p>";
            }

            //Si le SAV n'est pas clôturé
            //Et que l'utilisateur a les droits pour le faire
            //Affichage du bouton "Clôturer"
            if(($sav->date_fin == 0 || !isset($sav->date_fin)) && in_array($user_info['username'],$allowclose)) echo "<input type=\"button\" class=\"finaliser_sav\" onclick=\"if(confirm('Clôturer le litige ?')){ document.getElementById('action_cloture').value = 'cloture';  document.forms['et5form'].submit(); }\" value=\"CLOTURER\" name=\"cloture\">";

            ?>
            </div>
        </form>
    </div>
<?php
}
