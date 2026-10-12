<?php
// --------------------------------------------------------------------------------- //
// --------------------------------------------------------------------------------- //
// --------------------------------------------------------------------------------- //
// fonction qui affiche le tableau pour FOURNISSEUR PRODUIT                          //
// --------------------------------------------------------------------------------- //
// --------------------------------------------------------------------------------- //
// --------------------------------------------------------------------------------- //
$rootpath = '/var/www/html/mec/';
function fourn_recap($facturid){
	global $DB_site2,$host;
	global $adv;
	global $devises;

    $f=new facture($DB_site2,$facturid);
	$trsptclient=$f->transportsClient();
	
    $infoadv=$DB_site2->query_first("SELECT suivi FROM suivi_commande WHERE type='advfourn' AND facturid=".$facturid." ORDER BY date DESC LIMIT 1");
	
	$return="<table id=\"fourn_recap\" width=\"100%\"".(!isset($_GET['type'])||$_GET['type']=="fourn"?"":" style=\"display:none;\"").">
	<tr>
            <td colspan=\"8\" style=\"color:white;background-color:#AAA;font-weight:bold;\">
                <img src=\"images/cave.png\" width=\"20\" align=\"absmiddle\"> Fournisseur produit
                <a href=\"javascript:mail_trspt('fourn');\">Envoi email livraison directe fournisseur</a>
            </td>
        </tr>
        <tr style=\"background-color:#AAA;\">
            <td style=\"background-color:#AAA;\">
                <span class=\"infobtn\" onclick=\"this.parentNode.querySelector('.infocmdpopup').style.display='block';\">Info ADV <img src=\"images/editer.gif\"></span>
                <span class=\"infocmdtxt\">$infoadv[suivi]</span>
                <div class=\"infocmdpopup\">
                    <textarea id=\"fournadvcomment\">$infoadv[suivi]</textarea>
                    <img src=\"images/icon-x.svg\" title=\"Annuler\" onclick=\"this.parentNode.style.display='none';\"> <img src=\"images/coche_verte.svg\" onclick=\"updadvcomment('fourn')\" title=\"Mettre à jour\">
                </div>
            </td>";

	$total=0;

	$rArt=$DB_site2->query("SELECT * FROM article");
	while($aArt = $DB_site2->fetch_array($rArt)){
		$a[$aArt['artid']] = $aArt['artcode_eurocave'];
    }
    
    //// tâche 6875 : permet de changer toutes les dates en même temps (date enlèvement ou date livraison)
	$r3=$DB_site2->query("SELECT * FROM envoi_fournisseur_ligne efl INNER JOIN lignefacture lf ON (lf.lignefactureid=efl.ligne_facture_id) WHERE efl.type='fourn' AND facture!='STOCK' AND factureid = ".$facturid);
	$fourn_liste=array();
	while($fourn=$DB_site2->fetch_array($r3)){
		$fourn_liste[]=$fourn['id'];
		$date_enlev_in=$fourn['indate'];
		$date_enlev_origin=$fourn['origin_in'];
		$date_livr_out=$fourn['outdate'];
		$date_livr_origin=$fourn['origin_out'];
		$date_enlev[]=$date_enlev_in;
		$date_livr[]=$date_livr_out;
	}
	$arr_unique=($date_enlev!='' ? array_unique($date_enlev) : ''); 
	$arr_unique2=($date_livr!='' ? array_unique($date_livr) : '');

	$fourn_liste=implode(',',$fourn_liste);

	$liste=$adv->listadv($facturid,"fourn");
	$advlfids=array();
	$sql="";
	foreach($liste as $advprod){
		if($advprod->efid==0){
			$tdorangef=true;
		}
	}

	$return.="
	<td></td>
	<td></td>";
	if($DB_site2->num_rows($r3) > 0) {
		$return.="
		<td style=\"background-color:#AAA;text-align:left\" onmouseover=\"document.getElementById('updateenlev_all').style.visibility='visible';\" onmouseout=\"document.getElementById('updateenlev_all').style.visibility='hidden';\">
		<div id=\"updateenlev_all\" style=\"position:absolute;visibility:hidden;cursor:pointer;background-color:white;\" onclick=\"calendrier_show(undefined,'',false,updadvdateall);upddatetype='in';efl='".$fourn_liste."';\">
		<img src=\"images/editer.gif\" title=\"modifier\">
		(tout modifier)
		</div>
		Date enlèv.<br>".(count($arr_unique)<2 && $date_enlev_in>0 && $date_enlev_origin!=$date_enlev_in?date("d/m/Y",$date_enlev_origin)."<br>=>":"").(count($arr_unique)<2 && $date_enlev_in>0?date("d/m/Y",$date_enlev_in):"-")."
		</td>
		<td onmouseover=\"document.getElementById('updatelivr_all').style.visibility='visible';\" onmouseout=\"document.getElementById('updatelivr_all').style.visibility='hidden';\">
		<div id=\"updatelivr_all\" style=\"position:absolute;visibility:hidden;cursor:pointer;background-color:white;\" onclick=\"calendrier_show(undefined,'',false,updadvdateall);upddatetype='out';efl='".$fourn_liste."';\" >
		<img src=\"images/editer.gif\" title=\"modifier\">
		(tout modifier)
		</div>
		Date livr. maxi<br>".(count($arr_unique2)<2 && $date_livr_out>0 && $date_livr_origin!=$date_livr_out?date("d/m/Y",$date_livr_origin)."<br>=>":"").(count($arr_unique2)<2 && $date_livr_out>0?date("d/m/Y",$date_livr_out):"-")."
		</td>
		<td></td>
		<td></td>
		<td></td>";
		}
		elseif($tdorangef==false) {
			//// tache 6929 - pour ajouter/modifier date pour tous les produits
			if($advlfids!=array()) $sql=" AND lignefactureid NOT IN (".implode(",",$advlfids).")";
			$req=$DB_site2->query("SELECT * FROM lignefacture lf WHERE factureid=$facturid AND (SELECT COUNT(ligne_facture_id) FROM envoi_fournisseur_ligne WHERE ligne_facture_id=lf.lignefactureid AND type IN ('fourn','stock'))=0".$sql);
			$lignfac=array();
			while($nocmd=$DB_site2->fetch_array($req)){
				$lignfac[]=$nocmd['lignefactureid'];
			}
			$lignfac=implode(',',$lignfac);

			$return.="
			<td colspan=\"5\" style=\"white-space: nowrap;\">
			<table>
			<tr>
			<td>Date cde</td>
			<td>Date enlèv.</td>
			<td>Date livr.</td>
			<td>Info</td>
			</tr>
			<tr>
			<td><input type=\"text\" name=\"datefourn\" id=\"datefourn\" value=\"\" onclick=\"cmddateall(this.id,'fourncmd','$lignfac','date')\" size=\"10\" ></td>
			<td><input type=\"text\" id=\"dateenlfourn\" size=\"10\" onclick=\"cmddateall(this.id,'fourncmd','$lignfac','enlevement')\" value=\"\"></td>
			<td><input type=\"text\" id=\"datelivrfourn\" size=\"10\" onclick=\"cmddateall(this.id,'fourncmd','$lignfac','livraison')\" value=\"\"></td>
			<td><input type=\"text\" id=\"destfourn\" size=\"35\" value=\"\" onclick=\"if(this.value=='fournisseur')this.value='';document.getElementById('okfourn').style.display='inline';\"><a href=\"javascript:cmdtxtall('fourncmd','$lignfac',document.getElementById('destfourn').value)\" id=\"okfourn\" value=\"OK\" style=\"display:none;\">Mettre à jour</a>
			<span id=\"acmd_fourn_all\" style=\"background-color:white;width:max-content;\">
			(tout modifier)
			</span></td>
			</tr>
			</table>
			</td>";
        //// tache 6929
		}
		else {
			$return.="<td colspan=\"4\"></td>";
		}
		$return.="
		
		</tr>";
    //// fin tâche 6875

	$r=$DB_site2->query("SELECT * FROM envoi_fournisseur WHERE type='fourn' AND facturid=$facturid");
	while($envoi=$DB_site2->fetch_array($r)){
		$r2=$DB_site2->query("SELECT * FROM envoi_fournisseur_ligne efl INNER JOIN lignefacture lf ON (lf.lignefactureid=efl.ligne_facture_id) WHERE efid=".$envoi['id']." AND efl.type='fourn' AND facture!='STOCK'");
		$livrcmd="";
		$nolivr=false;
		$cancel="";
		$email_livraison="";
		if($DB_site2->num_rows($r2)>0){
			$lf1=$DB_site2->fetch_array($r2);
			$rowspan=$DB_site2->num_rows($r2)+1;
			if($envoi['destinataire']!="0"){
				if($lf1['indate']!=0){
					if($envoi['enlevement']==0){
						$nolivr=true;
						$livrcmd="<input type=\"text\" id=\"datelivrcmd$envoi[id]\" size=\"10\" value=\"Date enlèv.\" onclick=\"efid=$envoi[id];this.value='';calendrier_show(this.id,this.value,false,updenlevcmd);\">";
						$deladv=new adv($DB_site2);
						$result=$deladv->load_from_efid($envoi['id']);
						if($result) $cancel="<br><a href=\"facture.php?facturid=$facturid&action=editer&type=fourn&advid=".$deladv->id."&annule=1#adv\" style=\"color:red;\">Annuler la commande</a><br><a href=\"facture.php?facturid=$facturid&action=editer&type=fourn&advid=".$deladv->id."&modif=1#adv\">Modifier la commande</a>";
					}else{
						$livrcmd="Enlevée le ".date("d/m/Y",$envoi['enlevement']);
						if($envoi['livraison']<1){
							$livrcmd .="<img src=\"images/supprimer.gif\" title=\"supprimer\" onclick=\"upddatetype='enlevement';ef='$envoi[id]';cal_timestamp=0;updadvdate2();\">";
						}
						$livrcmd .="<br>";
					}
				}

				if($lf1['outdate']!=0 && !$nolivr){
					if($envoi['livraison']==0)
						$livrcmd.="<input type=\"text\" id=\"datelivrcmd$envoi[id]\" size=\"10\" value=\"Date livraison\" onclick=\"efid=$envoi[id];this.value='';calendrier_show(this.id,this.value,false,updlivrcmd);\">";
					elseif($envoi['livraison']==-1)
						$livrcmd.="<span style=\"color:black;white-space:nowrap;font-weight:bold;font-size:11px;\">Livraison annulée</span>";
					else
						$livrcmd.="Livrée le ".date("d/m/Y",$envoi['livraison']);
				}
			}

			$lieuenlev=$lieulivr="";
			if($envoi['destinataire']=="0"){
				$envoi['destinataire']="Pas de commande";
				$cancel="<a href=\"javascript:deladvcmd($envoi[id])\" style=\"color:red;\">Annuler la commande</a>";
			}else{
				if($envoi['enlev']>0){
					$fourn=$DB_site2->query_first("SELECT id,societe,ville FROM fournisseur WHERE id=".$envoi['enlev']);
					if($fourn['id']!="") $lieuenlev="Stock ".$fourn['societe']." ".$fourn['ville'];
				}

				if($envoi['livr']>0){
					$fourn=$DB_site2->query_first("SELECT id,societe,ville FROM fournisseur WHERE id=".$envoi['livr']);
					if($fourn['id']!="") $lieulivr=$fourn['societe']." ".$fourn['ville'];
				}
				if(isset($trsptclient[$envoi['id']])){
                                    $email_livraison="<br><br><input type=\"button\" onclick=\"mail_trspt('".$trsptclient[$envoi['id']]."',".$envoi['id'].");\" value=\"Mail expé.\">";
                                }
			}


			$body_relance="";
			$a_r="";
			if(($envoi['enlevement']==0 || $envoi['livraison']==0) && $envoi['destinataire']!=="Pas de commande"){
				$ligne_article_envoi=$DB_site2->query("SELECT a.artcode_eurocave,lf.libelle AS lib,m.libelle AS marque, efl.qte, efl.paht, efl.ecopart, efl.outdate, efl.indate FROM article a INNER JOIN article_marque am ON(am.artid=a.artid) INNER JOIN marque m ON(m.marqueid=am.marqueid) INNER JOIN lignefacture lf ON lf.artid = a.artid INNER JOIN envoi_fournisseur_ligne efl ON efl.ligne_facture_id = lf.lignefactureid WHERE efl.efid=".$envoi['id']." AND efl.type='fourn' AND facture!='STOCK'");
				$adv->load_from_efid($envoi['id']);
				$listar=$adv->list_ar();
				$sujet_relance="Suivi de commande #CDE".$facturid."#";
				if(!is_null($listar)){
            		foreach($listar as $ar){
						$sujet_relance.= " - AR N°".$ar->numero." du ".date("d/m/Y",$ar->date_ar);
					}
				}
				$body_relance="Bonjour,<br><br>Pouvez-vous nous faire un point sur cette commande :<br><br><b>Détail de la commande</b><table border='2' cellpadding='5' cellspacing='1' width='100%'><thead><tr><td style='font-weight:bold'>Qte:</td><td style='font-weight:bold'>Références:</td><td align='left' style='font-weight:bold'>&nbsp;Informations:</td><td>Date de départ</td><td>Date de livraison</td><td>Nouvelle date de livr. prévue</td></tr></thead><tbody>";
				foreach($ligne_article_envoi as $ligne){
					$body_relance.="<tr><td>".$ligne['qte']."</td><td>".addslashes($ligne['marque'])."<br>".addslashes($ligne['artcode_eurocave'])."</td><td>".preg_replace('/\n|\r/',' ',preg_replace('/"/',"'",$ligne['lib']))."</td><td>".($envoi['enlevement']!=='0'?date("d/m/Y",$envoi['enlevement']):date('d-m-Y',$ligne['indate']))."</td><td>".date('d-m-Y',$ligne['outdate'])."</td><td></td></tr>";
				}
				$body_relance.="</tbody></table><br>Pouvez-vous nous confirmer que cette commande a bien été expédiée/sera expédiée à date ? <b>En cas de report, nous indiquer une nouvelle date ci-dessus.</b><br><br>Nous vous remercions par avance de votre réponse, <b>sous 24h maximum</b>, par retour de ce mail.<br><br>Référence <b>#CDE".$facturid."#</b> : à rappeler dans toute correspondance dans l’objet du mail.<br>";
			}



			// Modif TSI: #2565 Ajout d'un lien directe sur le detail du fournisseur choisit
			$return.="<tr style=\"background-color:#00dd00;\">
				<td rowspan=\"".($rowspan)."\"><b><a href=\"fournisseurs_liste.php?action=editer&fournisseur=".$envoi['destinataire']."\" target=\"_Blank\">".$envoi['destinataire'].
				"&nbsp;<img src=\"./images/visible.png\" style=\"width:15px;background-color:#FFFFFF;\" >".
				"</a></b>".$lieuenlev.$email_livraison."<br>".date("d/m/Y H:i",$envoi['date'])."<br>".$lf1['expediteur']."<br>$livrcmd $cancel</td>
				<td colspan=\"2\"></td>
				<td>$lieuenlev</td>
				<td>$lieulivr</td>
				<td rowspan=\"$rowspan\" onmouseover=\"document.getElementById('trspt$envoi[id]').style.visibility='visible';\" onmouseout=\"document.getElementById('trspt$envoi[id]').style.visibility='hidden';\"><div id=\"trspt$envoi[id]\" style=\"position:absolute;visibility:hidden;cursor:pointer;\"><img src=\"images/editer.gif\" onclick=\"document.getElementById('selecttrspt$envoi[id]').style.display='list-item';document.getElementById('tdtrspt$envoi[id]').style.display='none';document.getElementById('trspt$envoi[id]').style.display='none';\"></div>Transport<br><select id=\"selecttrspt$envoi[id]\" style=\"display:none;\" onchange=\"document.location='facture.php?action=editer&facturid=$facturid&newtrspt='+this.value+'&efid=$envoi[id]#adv'\">$opt</select><div id=\"tdtrspt$envoi[id]\">$envoi[trspt]</div></td>
				<td rowspan=\"$rowspan\">Tarif<br>".$envoi['tarif']."&nbsp;".$devises->symbole($envoi['devise'])."</td>
				<td rowspan=\"$rowspan\">
				<div style=\"margin-bottom:5px;\">".($envoi['date_relance']!=='0'?'Relance faite le<br>'.date("d/m/Y",$envoi['date_relance']).'<br>':'')."<span class=\"btn_relance\" onclick=\"mail_auto('adv@mon-espace-cuisson.com','','adv@mon-espace-cuisson.com','".$sujet_relance."','".addslashes($body_relance)."',undefined,undefined,false,null,".$envoi['id'].");mail_suivi('suivi_fournisseur',".$facturid.");add_exp_list('contact','contact@mon-espace-cuisson.com','base');\">Relancer</span></div>
                    <div>
                        <span class=\"infobtn\" onclick=\"this.parentNode.querySelector('.infocmdpopup').style.display='block';\">Info compta <img src=\"images/editer.gif\"></span>
                        <div class=\"infocmdtxt\">$envoi[comment]</div>
                        <div class=\"infocmdpopup\">
                            <textarea id=\"comment$envoi[id]\">$envoi[comment]</textarea>
                            <img src=\"images/icon-x.svg\" title=\"Annuler\" onclick=\"this.parentNode.style.display='none';\"> <img src=\"images/coche_verte.svg\" onclick=\"updcomptacomment($envoi[id]);\" title=\"Mettre à jour\">
                        </div>
                    </div>
                </td>
			</tr>";
			$total+=$envoi['tarif'];

			$lignes=array();
			mysqli_data_seek($r2,0);
			while($lf=$DB_site2->fetch_array($r2)){
				$color1=($lf['indate']>=$today && $lf['indate']<$tomorrow?" style=\"color:red;\"":"");
				$color2=($lf['outdate']>=$today && $lf['outdate']<$tomorrow?" style=\"color:red;\"":"");
				$imperative=($lf['imperatif']==1?"imperative":"maxi");
				$img="";
				$path="../configurations/".$host."/images/produits/br/".$lf['artid'].".jpg";

				if(file_exists($path)) $img="<img src=\"$path\" style=\"width:30px;\" onmouseover=\"this.style.position='absolute';this.style.width='200px';\" onmouseout=\"this.style.position='relative';this.style.width='30px';\">";

				$return.="<tr style=\"background-color:#99dd99;\">
					<td>$img</td>
					<td title=\"$lf[libelle]\" style=\"max-width: 141px;\">".$lf['qte']." ".($lf['artcode']==''?$lf['libelle']:$lf['artcode'])."<br>".$artref[$lf['lignefactureid']]."</td>
					<td$color1 onmouseover=\"document.getElementById('fournindate$lf[id]').style.visibility='visible';\" onmouseout=\"document.getElementById('fournindate$lf[id]').style.visibility='hidden';\"><div id=\"fournindate$lf[id]\" style=\"position:absolute;visibility:hidden;cursor:pointer;\"><img src=\"images/editer.gif\" onclick=\"calendrier_show(undefined,'',false,updadvdate);upddatetype='in';efl='$lf[id]';\" title=\"modifier\"><img src=\"images/supprimer.gif\" title=\"supprimer\" onclick=\"upddatetype='in';efl='$lf[id]';cal_timestamp=0;updadvdate();\"></div>Date enlèv.<br>".($lf['indate']>0 && $lf['origin_in']!=$lf['indate']?date("d/m/Y",$lf['origin_in'])."<br>=>":"").($lf['indate']>0?date("d/m/Y",$lf['indate']):"-")."</td>
					<td$color2 onmouseover=\"document.getElementById('fournoutdate$lf[id]').style.visibility='visible';\" onmouseout=\"document.getElementById('fournoutdate$lf[id]').style.visibility='hidden';\"><div id=\"fournoutdate$lf[id]\" style=\"position:absolute;visibility:hidden;cursor:pointer;\"><img src=\"images/editer.gif\" onclick=\"calendrier_show(undefined,'',false,updadvdate);upddatetype='out';efl='$lf[id]';\"><img src=\"images/supprimer.gif\" title=\"supprimer\" onclick=\"upddatetype='out';efl='$lf[id]';cal_timestamp=0;updadvdate();\"></div>Date livr. $imperative<br>".($lf['outdate']>0 && $lf['origin_out']!=$lf['outdate']?date("d/m/Y",$lf['origin_out'])."<br>=>":"").($lf['outdate']>0?date("d/m/Y",$lf['outdate']):"-")."</td>
				</tr>";

				$lignes[$lf['lignefactureid']]=$lf;
			}

			$conditions=$DB_site2->query_first("SELECT * FROM fournisseur_condition WHERE societe='".mysqli_real_escape_string($DB_site2->link_id,$envoi['destinataire'])."'");
			if($conditions['ar']==1){
				$adv=new adv($DB_site2);
				$adv->load_from_efid($envoi['id']);
				$ef=new envoi_fournisseur($DB_site2,$envoi['id']);
				$ef->loadlignes();
				$newar=isset($_GET['ar']) && $_GET['ar']==$adv->id;

				$arcolors=array(0=>"#F99",1=>"#99dd99");
				$listar=$adv->list_ar();
				foreach($listar as $ar){
					$return.="<tr>
						<td rowspan=\"".(count($lignes)+2)."\" style=\"background:".($ar->valide==1?"#0D0":"#F22")."\">
							<b>AR</b> n°".($ar->numero==""?"?":$ar->numero)."
							<br>Du ".date("d/m/Y",$ar->date_ar)."
							<br>Reçu ".date("d/m/Y",$ar->date)."
							<br><a href=\"javascript:void(0)\" onclick=\"arEdit(".$adv->id.",".$envoi['id'].",'".implode("-",array_keys($lignes))."',".$ar->id.")\">Modifier</a>
						</td>
					</tr>";

					foreach($ef->lignes as $efl){
						$lfid=$efl->lignefactureid;
						$fields=$ar->line_fields($lfid);
						$optvalue=1;
						$options="";
						if(isset($fields["options"])){
							foreach($fields["options"] as $caractvalid=>$value){
								$caract=$DB_site2->query_first("SELECT libelleFR FROM caracteristiquevaleur WHERE caractvalid=".$caractvalid);
								$options.="<div style=\"color:".($value==1?"green":"red").";\">".$caract[0]."</div>";
								if($value==0) $optvalue=0;
							}
						}

						$datecolspan=3;
						$tdarlivr="";
						if(isset($fields["livr"])){
							$tdarlivr="<td colspan=\"2\" style=\"background:".$arcolors[floor(($fields["livr"]+$fields['trspt'])/2)].";\">
							".date("d/m/Y",$efl->outdate)."
							<br>Prix : ".$adv->fields['mazetcout']."
							</td>";
							$datecolspan=1;
						}

						$return.="<tr>
							<td style=\"background:".$arcolors[$fields["qte"]].";white-space: nowrap;\">Qté : ".$efl->qte."</td>
							<td style=\"background:".$arcolors[$fields["artcode"] && $optvalue].";color:".($fields["artcode"]==0?"red":"").";\">".$ligne['artcode']." <i>".$adv->fields["ref_".$lfid]."</i>".$options."</td>
							<td colspan=\"".$datecolspan."\" style=\"background:".$arcolors[$ar->fields["date_".$lfid]].";\">".date("d/m/Y",$efl->indate)."</td>
							$tdarlivr
							<td style=\"background:".$arcolors[$fields["paht"]].";\">".($efl->paht+$efl->ecopart)."</td>
							<td style=\"background:".($ar->valide==1?"#0D0":"#F22")."\">".$fields["info"]."</td>
						</tr>";
					}
					if(isset($adv->fields['mazetcout']))
                                            $return.="<tr>
                                                <td colspan=\"2\">Transport</td>
                                                <td colspan=\"3\"></td>
                                                <td>".$adv->fields['mazetcout']."</td>
                                                <td style=\"background:".($ar->valide==1?"#0D0":"#F22")."\"></td>
                                            </tr>";
				}

				if(count($listar)==0 || end($listar)->valide==0){
					$return.="<tr>";

					if($newar){
						$return.="<td colspan=\"8\">
						<form name=\"arform\" method=\"post\">
							<table width=\"100%\">
								<tr>
									<td id=\"new_ar\" style=\"background:#aaaaaa;\" colspan=\"6\">
										<b>AR n°</b> ".$adv->text_input("ar_num","",array("size"=>10))." &nbsp;&nbsp;&nbsp;
										<b>Du</b> ".$adv->cal_input("ar_date","ar_color()")." &nbsp;&nbsp;&nbsp;
										<b>Reçu le</b> ".$adv->cal_input("ar_date_recu","ar_color()")."
										<input type=\"hidden\" name=\"ar_valide\" value=\"0\">
										<input type=\"hidden\" name=\"arlfids\" value=\"".implode("-",array_keys($lignes))."\">
										<input type=\"button\" id=\"valid_ar\" value=\"Valider\" style=\"display:none;\" onclick=\"send_ar()\">
									</td>
								</tr>
								<tr style=\"background:#CCC;font-weight:bold;\" align=\"center\">
									<td>Quantité</td>
									<td align=\"left\">Référence</td>
									<td>P.U HT</td>
									<td>Enlèvement</td>
									<td>Livraison</td>
									<td>Infos</td>
								</tr>";

						foreach($lignes as $lfid=>$ligne){
							$options="";
							if(is_array($adv->fields['caract_'.$lfid])){
								$options="";
								$optionsprix="";
								foreach($adv->fields['caract_'.$lfid] as $caractvalid=>$value){
									$liste_ral=$DB_site2->query_first("SELECT caractvalid FROM caractval_ral WHERE caractvalid = '".$caractvalid."'");
                					///si l'option est une couleur RAL, on recupère le bon prix d'achat:
									if(!is_null($liste_ral)){
										$caracteid = $DB_site2->query_first("SELECT caractid FROM `caracteristiquevaleur` WHERE `caractvalid` = '".$liste_ral['caractvalid']."'");
										$caracvals = $DB_site2->query("SELECT caractvalid FROM `caracteristiquevaleur` WHERE `subcaractid` = ".$caracteid['caractid']);
										$caracvals_liste="";
										foreach($caracvals as $caracv){
											$caracvals_liste[]=$caracv['caractvalid'];
										}
										$caracvals=implode("','",$caracvals_liste);
										$caracval = $DB_site2->query_first("SELECT caractvalid FROM `article_caractval` WHERE `artid` = '".$ligne['artid']."' AND `caractvalid` IN ('".$caracvals."')");
							

										$sql = "SELECT (SELECT cv.libelleFR FROM caracteristiquevaleur cv INNER JOIN article_caractval ac USING(caractvalid) WHERE cv.caractvalid=".$caractvalid." AND ac.artid=".$ligne['artid']."), ac.achat, cv.paht, cv.prix_fixe FROM caracteristiquevaleur cv INNER JOIN article_caractval ac USING(caractvalid) WHERE cv.caractvalid=".$caracval['caractvalid']." AND ac.artid=".$ligne['artid'];
									}
									else{
										$sql = "SELECT cv.libelleFR, ac.achat, cv.paht, cv.prix_fixe FROM caracteristiquevaleur cv INNER JOIN article_caractval ac USING(caractvalid) WHERE cv.caractvalid=".$caractvalid." AND ac.artid=".$ligne['artid'];
									}

									$caract=$DB_site2->query_first($sql);
									if(strlen($caract[0])>25) $caract[0]=substr($caract[0],0,25)."...";
									///si prix fixe
									if($caract['prix_fixe'] == '1'){
										$caract[1]=$caract['paht'];
									}

									$options.="<div id=\"li_".$caractvalid."_".$lfid."\" style=\"width:260px;margin:0 auto;\">".$adv->coche("option_".$caractvalid."_".$lfid,"verif_ar(".$lfid.",'artcode')")." ".$caract[0]." : <strong>".$caract[1]."</strong></div>";
									$optionsprix+=$adv->fields["caractval_".$lfid."[".$caractvalid."]"];
								}
							}


							$tdarlivr="";
							if($ligne['outdate']>0){
								$tdarlivr="<td id=\"td".$lfid."livr\" style=\"background:#CCC;\">
										<div class=\"editvalue\" onclick=\"change_ar_date(".$lfid.",'".$ligne['outdate']."','livr')\"><div id=\"oldlivr_".$lfid."\" style=\"height:32px;\">".date("d/m/Y",$ligne['outdate'])."</div></div>
										<div id=\"newlivr_".$lfid."\" style=\"height:32px;display:none;\">".$adv->cal_input("arlivr_".$lfid)."</div>
										<br>".$adv->coche("livr_".$lfid,"verif_ar(".$lfid.",'livr')")."
								</td>";
							}

							$ref=$adv->fields["ref_".$lfid];
							if(strlen($ref)>30) $ref=substr($ref,0,30)."...";
							$return.="<tr align=\"center\">
								<td id=\"td".$lfid."qte\" style=\"background:#CCC;\">
									<div style=\"height:32px;\">".$ligne['qte']."</div>
									".$adv->coche("qte_".$lfid,"verif_ar(".$lfid.",'qte')")."
								</td>
								<td id=\"td".$lfid."artcode\" style=\"background:#CCC;text-align:left;\">
									<div style=\"height:32px;max-width:260px;margin:0 auto;\">".$ligne['artcode']."<br><i>".$ref."</i></div>
									".$options."
									<br><div style=\"max-width:260px;margin:0 auto;\">".$adv->coche("artcode_".$lfid,"verif_ar(".$lfid.",'artcode')")."</div>
								</td>
								<td id=\"td".$lfid."paht\" style=\"background:#CCC;\">
									<div class=\"editvalue\" onclick=\"change_ar_price(".$lfid.",'".$adv->fields["price1_".$lfid]."','price')\">
										<div id=\"oldprice_".$lfid."\" style=\"height:32px;\">".($adv->fields["price1_".$lfid] + $optionsprix)."
											<br>+".$adv->fields["ecopart_".$lfid]."&euro; Ecopart</div></div>
									<div id=\"newprice_".$lfid."\" style=\"height:32px;display:none;\"><input type=\"text\" name=\"newpriceval_".$lfid."\" size=\"7\">
										<br>+".$adv->fields["ecopart_".$lfid]."&euro; Ecopart</div>
									<br>".$adv->coche("paht_".$lfid,"verif_ar(".$lfid.",'paht')")."
								</td>
								<td id=\"td".$lfid."date\" style=\"background:#CCC;\">
									<div class=\"editvalue\" onclick=\"change_ar_date(".$lfid.",'".$ligne['indate']."','date')\"><div id=\"olddate_".$lfid."\" style=\"height:32px;\">".date("d/m/Y",$ligne['indate'])."</div></div>
									<div id=\"newdate_".$lfid."\" style=\"height:32px;display:none;\">".$adv->cal_input("ardate_".$lfid)."</div>
									<br>".$adv->coche("date_".$lfid,"verif_ar(".$lfid.",'date')")."
								</td>
								".$tdarlivr."
								<td style=\"background:#CCC;\">".$adv->textarea("info_".$lfid,"",array("style"=>"height:100%;"))."</td>
							</tr>
							<tr>
								<td id=\"td".$lfid."trspt\" style=\"background:#CCC;\" colspan=\"6\">
									<br><b>Coût Transport :</b> <input type=\"text\" name=\"newtrsptval_".$lfid."\" size=\"8\" value=\"".$adv->fields['mazetcout']."\"> HT
										&nbsp; ".$adv->coche("trspt_".$lfid,"verif_ar(".$lfid.",'trspt')")."
								</td>
							</tr>";
						}
						$return.="</table>
							</form>
						</td>";
					}else{
						$return.="<td style=\"background:#CCC;\">AR</td>
						<td colspan=\"7\" style=\"background:#CCC;\"><img src=\"images/plus.gif\">&nbsp;<a href=\"facture.php?action=editer&facturid=".$facturid."&ar=".$adv->id."#adv\">Ajouter</a></td>";
						
					}
					$return.="</tr>";
				}
			}
		}
		$return.="<tr><td colspan=\"8\"><div id=\"tabAR".$adv->id."\"></div></td></tr>";
		$return.="<tr><td>&nbsp;</td></tr>";

	}

	$liste=$adv->listadv($facturid,"fourn");
	$advlfids=array();
	$sql="";
	foreach($liste as $advprod){

		if($advprod->efid==0){
			$txtenvoi="Pas d'envoi programmé";
			if($advprod->envoi>0) $txtenvoi="Envoi le ".date("d/m",$advprod->envoi)." à ".$advprod->envoi_heure."h";
			$return.="<tr style=\"background-color:#FFAA22;\"><td rowspan=\"".(count($advprod->lignes)+1)."\" style=\"color:black;\" nowrap>Commande enregistrée<br>"
			."<a href=\"fournisseurs_liste.php?action=editer&fournisseur=".$advprod->destinataire."\" target=\"_Blank\">".$advprod->destinataire."&nbsp;<img src=\"./images/visible.png\" style=\"width:15px;background-color:#FFFFFF;\" ></a>"
			."<br>".date("d/m/Y H:i",$advprod->date_creation)."<br>".$advprod->utilisateur." <br><a href=\"facture.php?action=editer&facturid=$facturid&advid=".$advprod->id."&type=fourn#adv\">Modifier</a> <a href=\"javascript:deladv(".$advprod->id.",".$advprod->envoi.");\" style=\"color:red;\">Supprimer</a><br>$txtenvoi</td><td colspan=\"7\"></td></tr>";
			foreach($advprod->lignes as $lignefactureid=>$qte){
				$advlfids[]=$lignefactureid;
				$lignefacture=$DB_site2->query_first("SELECT * FROM lignefacture WHERE lignefactureid=".$lignefactureid);
				$img="";
				$path="../configurations/".$host."/images/produits/br/".$lignefacture['artid'].".jpg";
				if(file_exists($path)) $img="<img src=\"$path\" style=\"width:30px;\" onmouseover=\"this.style.position='absolute';this.style.width='200px';\" onmouseout=\"this.style.position='relative';this.style.width='30px';\">";
				$return.="<tr style=\"background-color:#FFCC99;\">
					<td>$img</td>
					<td title=\"$lignefacture[libelle]\" style=\"max-width: 141px;\">".$lignefacture['qte']." ".($lignefacture['artcode']==''?$lignefacture['libelle']:$lignefacture['artcode'])."<br>".$artref[$lignefactureid]."</td>
					<td>Date enlèv.<br>".($advprod->fields['trsptmode']=="parenlevement"?$advprod->fields['parenlevindate']:"").($advprod->fields['trsptmode']=="parvossoins"?$advprod->fields['parvossoinsindate']:"")."</td>
					<td>Date livr.<br>".($advprod->fields['trsptmode']=="parvossoins"?$advprod->fields['parvossoinsoutdate']:"")."</td>
					<td>".$advprod->fields['selecttrspt']."</td>
					<td></td>
					<td></td>
				</tr>";
			}
		}
	}
	if($advlfids!=array()) $sql=" AND lignefactureid NOT IN (".implode(",",$advlfids).")";


	$req=$DB_site2->query("SELECT * FROM lignefacture lf WHERE factureid=$facturid AND (SELECT COUNT(ligne_facture_id) FROM envoi_fournisseur_ligne WHERE ligne_facture_id=lf.lignefactureid AND type IN ('fourn','stock'))=0".$sql);
	$lignesfacid=array();
	if($DB_site2->num_rows($req)>0){
		while($lignefact=$DB_site2->fetch_array($req)){
			$lignesfacid[]=$lignefact['lignefactureid'];
		}
		$lignesfacid=implode(",",$lignesfacid);
		$return.="<tr style=\"background-color:#FF2222;\">
		<td rowspan=\"".($DB_site2->num_rows($req)+1)."\" style=\"color:black;\">
                Articles non commandés
                <br><a href=\"facture.php?facturid=$facturid&action=editer&type=fourn&advid=0#adv\" style=\"display:block;color:white;margin-left:3px;\">Commander</a>
                <br><a href=\"javascript:nocmd('fourn','".$lignesfacid."')\" style=\"display:block;color:white;margin-left:3px;\">Pas de Cde</a>
            </td>
            <td colspan=\"7\"></td>
        </tr>";
    }
	
	foreach($req as $nocmd){
		$cmddate=$enlevdate=$livrdate="";
		$cmdf="fournisseur";
		$rc=$DB_site2->query("SELECT * FROM calendrier WHERE type='fourncmd' AND objid=".$nocmd['lignefactureid']);
		$cal=$DB_site2->fetch_array($rc);
		if($cal!=false){
			$cmdf=$cal['txt'];
			if($cal['date']>0) $cmddate=date("d/m/Y",$cal['date']);
			if($cal['enlevement']>0) $enlevdate=date("d/m/Y",$cal['enlevement']);
			if($cal['livraison']>0) $livrdate=date("d/m/Y",$cal['livraison']);
		}
		$img="";
		$path="../configurations/".$host."/images/produits/br/".$nocmd['artid'].".jpg";
		if(file_exists($path)) $img="<img src=\"$path\" style=\"width:30px;\" onmouseover=\"this.style.position='absolute';this.style.width='200px';\" onmouseout=\"this.style.position='relative';this.style.width='30px';\">";
		$return.="<tr style=\"background-color:#FF9999;\">
			<td>$img</td>
			<td title=\"$nocmd[libelle]\" style=\"max-width: 141px;\">".$nocmd['qte']." ".($nocmd['artcode']==''?$nocmd['libelle']:$nocmd['artcode'])."<br>".$artref[$nocmd['lignefactureid']]."</td>
			<td colspan=\"5\" style=\"white-space: nowrap;\">
                            <table>
                            <tr>
                                <td>Date cde</td>
                                <td>Date enlèv.</td>
                                <td>Date livr.</td>
                                <td>Info</td>
                            </tr>
                            <tr>
                                <td><input type=\"text\" name=\"cmddate$nocmd[lignefactureid]\" id=\"cmddate$nocmd[lignefactureid]\" value=\"$cmddate\" onclick=\"cmddate(this.id,'fourncmd',$nocmd[lignefactureid],'date')\" size=\"10\"></td>
                                <td><input type=\"text\" id=\"enl$nocmd[lignefactureid]\" size=\"10\" onclick=\"cmddate(this.id,'fourncmd',$nocmd[lignefactureid],'enlevement')\" value=\"$enlevdate\"></td>
                                <td><input type=\"text\" id=\"liv$nocmd[lignefactureid]\" size=\"10\" onclick=\"cmddate(this.id,'fourncmd',$nocmd[lignefactureid],'livraison')\" value=\"$livrdate\"></td>
                                <td><input type=\"text\" id=\"dcmdf$nocmd[lignefactureid]\" size=\"35\" value=\"$cmdf\" onclick=\"if(this.value=='fournisseur')this.value='';document.getElementById('okdcmdf$nocmd[lignefactureid]').style.display='inline';\"><a href=\"javascript:cmdtxt('fourncmd',$nocmd[lignefactureid],document.getElementById('dcmdf$nocmd[lignefactureid]').value)\" id=\"okdcmdf$nocmd[lignefactureid]\" value=\"OK\" style=\"display:none;\">Mettre à jour</a></td>
                            </tr>
                            </table>
                        </td>
                        </tr>";
	}

	$return.="</table>";

	return $return;

}

// --------------------------------------------------------------------------------- //
// --------------------------------------------------------------------------------- //
// --------------------------------------------------------------------------------- //
// fonction qui affiche le tableau pour STOCK PRODUIT                                //
// --------------------------------------------------------------------------------- //
// --------------------------------------------------------------------------------- //
// --------------------------------------------------------------------------------- //

function stock_recap($facturid){
	global $DB_site2,$host;
	global $adv;
	global $devises;
	
	$stock=new stock($DB_site2);
	
	$infoadv=$DB_site2->query_first("SELECT suivi FROM suivi_commande WHERE type='advstock' AND facturid=".$facturid." ORDER BY date DESC LIMIT 1");

	// TSI: Est ce normal que cette table partage le mm id que celle du dessus ?
	$return="<br><table id=\"fourn_recap\" width=\"100%\"".(!isset($_GET['type'])||$_GET['type']=="stock"?"":" style=\"display:none;\"").">
	<tr>
            <td colspan=\"8\" style=\"color:white;background-color:#AAA;font-weight:bold;\">
                <img src=\"images/cave.png\" width=\"20\" align=\"absmiddle\"> Stock produit
            </td>
        </tr>
        <tr style=\"background-color:#AAA;\">
            <td style=\"background-color:#AAA;\">
                <span class=\"infobtn\" onclick=\"this.parentNode.querySelector('.infocmdpopup').style.display='block';\">Info ADV <img src=\"images/editer.gif\"></span>
                <span class=\"infocmdtxt\">$infoadv[suivi]</span>
                <div class=\"infocmdpopup\">
                    <textarea id=\"stockadvcomment\">$infoadv[suivi]</textarea>
                    <img src=\"images/icon-x.svg\" title=\"Annuler\" onclick=\"this.parentNode.style.display='none';\"> <img src=\"images/coche_verte.svg\" onclick=\"updadvcomment('stock')\" title=\"Mettre à jour\">
                </div>
            </td>";
            
    //// tache 6929 - pour ajouter/modifier date pour tous les produits
    $r3=$DB_site2->query("SELECT * FROM envoi_fournisseur_ligne efl INNER JOIN lignefacture lf ON (lf.lignefactureid=efl.ligne_facture_id) WHERE efl.type='stock' AND factureid = ".$facturid);
    $stocknb=array();
    $req=$DB_site2->query("SELECT * FROM lignefacture lf WHERE factureid=$facturid");
    while($lignefacture=$DB_site2->fetch_array($req)){
    	$stockartid = $stock->similaire($lignefacture['artid']);
    	$stockitems = $stock->stockbyartid($stockartid);
    	foreach($stockitems as $stockitem){
    		if($stockitem->state == 1 || ($stockitem->state == 2 && $stockitem->dest=="facture ".$facturid)){
    			$stocknb[$lignefacture['artid']]++;
    		}
    	}
    }
    if(count($stocknb)>0){
    	$stockartids=array_keys($stocknb);
	}
    $liste=$adv->listadv($facturid,"stock");
	$advlfids=array();
	$sql="";
	foreach($liste as $advprod){
		if($advprod->efid==0){
			$tdorangest=true;
		}
	}

    if($DB_site2->num_rows($r3) == 0 && !empty($stockartids) && $tdorangest==false) {
			if($advlfids!=array()) $sql=" AND lignefactureid NOT IN (".implode(",",$advlfids).")";
			$req=$DB_site2->query("SELECT * FROM lignefacture lf WHERE factureid=$facturid AND artid IN (".implode(",",$stockartids).") AND (SELECT COUNT(ligne_facture_id) FROM envoi_fournisseur_ligne WHERE ligne_facture_id=lf.lignefactureid AND type='stock')=0".$sql);
			$lignfac=array();
			while($nocmd=$DB_site2->fetch_array($req)){
				$lignfac[]=$nocmd['lignefactureid'];
			}
			$lignfac=implode(',',$lignfac);

			$return.="
			<td></td>
			<td></td>
			<td colspan=\"5\" style=\"white-space: nowrap;\">
			<table>
			<tr><td>Date cde</td><td>Date enlèv.</td><td>Date livr.</td><td>Info</td></tr>
			<tr>
			<td><input type=\"text\" name=\"datestock\" id=\"datestock\" value=\"\" onclick=\"cmddateall(this.id,'stockcmd','$lignfac','date')\" size=\"10\"></td>
			<td><input type=\"text\" id=\"dateenlstock\" size=\"10\" onclick=\"cmddateall(this.id,'stockcmd','$lignfac','enlevement')\" value=\"\"></td>
			<td><input type=\"text\" id=\"datelivrstock\" size=\"10\" onclick=\"cmddateall(this.id,'stockcmd','$lignfac','livraison')\" value=\"\"></td>
			<td><input type=\"text\" id=\"deststock\" size=\"35\" value=\"\" onclick=\"if(this.value=='fournisseur')this.value='';document.getElementById('okstock').style.display='inline';\"><a href=\"javascript:cmdtxtall('stockcmd','$lignfac',document.getElementById('deststock').value)\" id=\"okstock\" value=\"OK\" style=\"display:none;\">Mettre à jour</a>
			<span id=\"acmd_stock_all\" style=\"background-color:white;width:max-content;\">
			(tout modifier)
			</span></td>
			</tr>
			</table>
			</td>
			</tr>";
        //// fin tache 6929
		} else {
			$return.="
		<td colspan=\"7\"></td>
		</tr>";
		}

	$total=0;

	$r=$DB_site2->query("SELECT * FROM envoi_fournisseur WHERE type='stock' AND facturid=$facturid");
	while($envoi=$DB_site2->fetch_array($r)){
		$r2=$DB_site2->query("SELECT * FROM envoi_fournisseur_ligne efl INNER JOIN lignefacture lf ON (lf.lignefactureid=efl.ligne_facture_id) WHERE efid=".$envoi['id']." AND efl.type='stock'");
		$livrcmd="";
		$nolivr=false;
		if($DB_site2->num_rows($r2)>0){
			$lf1=$DB_site2->fetch_array($r2);
			$rowspan=$DB_site2->num_rows($r2)+1;
			if($envoi['destinataire']!="0"){
				if($lf1['indate']!=0){
					if($envoi['enlevement']==0){
						$nolivr=true;
						$livrcmd="<input type=\"text\" id=\"datelivrcmd$envoi[id]\" size=\"10\" value=\"Date enlèv.\" onclick=\"efid=$envoi[id];this.value='';calendrier_show(this.id,this.value,false,updenlevcmd);\">";
						$deladv=new adv($DB_site2);
						$result=$deladv->load_from_efid($envoi['id']);
						if($result) $cancel="<br><a href=\"facture.php?facturid=$facturid&action=editer&type=stock&advid=".$deladv->id."&annule=1#adv\" style=\"color:red;\">Annuler la commande</a><br><a href=\"facture.php?facturid=$facturid&action=editer&type=stock&advid=".$deladv->id."&modif=1#adv\">Modifier la commande</a>";
					}else{
						$livrcmd="Enlevée le ".date("d/m/Y",$envoi['enlevement']);
						if($envoi['livraison']<1){
							$livrcmd.="<img src=\"images/supprimer.gif\" title=\"supprimer\" onclick=\"upddatetype='enlevement';ef='$envoi[id]';cal_timestamp=0;updadvdate2();\">";
						}
						$livrcmd .="<br>";
					}
					

				}
				if($lf1['outdate']!=0 && !$nolivr){
					if($envoi['livraison']==0)
						$livrcmd.="<input type=\"text\" id=\"datelivrcmd$envoi[id]\" size=\"10\" value=\"Date livraison\" onclick=\"efid=$envoi[id];this.value='';calendrier_show(this.id,this.value,false,updlivrcmd);\">";
					elseif($envoi['livraison']==-1)
						$livrcmd.="<span style=\"color:black;white-space:nowrap;font-weight:bold;font-size:11px;\">Livraison annulée</span>";
					else
						$livrcmd.="Livrée le ".date("d/m/Y",$envoi['livraison']);
				}

			}

			$lieuenlev="";
			if($envoi['destinataire']=="0"){
				$envoi['destinataire']="Pas de commande";
				$cancel="<a href=\"javascript:deladvcmd($envoi[id])\" style=\"color:red;\">Annuler la commande</a>";
			}else{
				if($envoi['enlev']>0){
					$fourn=$DB_site2->query_first("SELECT id,societe,ville FROM fournisseur WHERE id=".$envoi['enlev']);
					if($fourn['id']!="") $lieuenlev="<br>Stock ".$fourn['societe']." ".$fourn['ville'];
				}
				if($envoi['livr']>0){
					$fourn=$DB_site2->query_first("SELECT id,societe,ville FROM fournisseur WHERE id=".$envoi['livr']);
					if($fourn['id']!="") $lieulivr=$fourn['societe']." ".$fourn['ville'];
				}
			}

			/// relance tâche #7039
			$body_relance="";
			$a_r="";
			if($envoi['enlevement']==0 && $envoi['destinataire']!=="Pas de commande"){
				$ligne_article_envoi=$DB_site2->query("SELECT a.artcode_eurocave,lf.libelle AS lib,m.libelle AS marque, efl.qte, efl.paht, efl.ecopart, efl.outdate, efl.indate FROM article a INNER JOIN article_marque am ON(am.artid=a.artid) INNER JOIN marque m ON(m.marqueid=am.marqueid) INNER JOIN lignefacture lf ON lf.artid = a.artid INNER JOIN envoi_fournisseur_ligne efl ON efl.ligne_facture_id = lf.lignefactureid WHERE efl.efid=".$envoi['id']." AND efl.type='stock'");
				$adv->load_from_efid($envoi['id']);
				$listar=$adv->list_ar();
				$sujet_relance="Suivi de commande #CDE".$facturid."#";
				if(!is_null($listar)){
					foreach($listar as $ar){
						$sujet_relance.= " - AR N°".$ar->numero." du ".date("d/m/Y",$ar->date_ar);
					}
				}
				$body_relance="Bonjour,<br><br>Pouvez-vous nous faire un point sur cette sortie de stock :<br><br><b>Détail de la commande</b><table border='2' cellpadding='5' cellspacing='1' width='100%'><thead><tr><td style='font-weight:bold'>Qte:</td><td style='font-weight:bold'>Références:</td><td align='left' style='font-weight:bold'>&nbsp;Informations:</td><td>Date de départ</td><td>Nouvelle date de départ prévue</td></tr></thead><tbody>";
				foreach($ligne_article_envoi as $ligne){
					$body_relance.="<tr><td>".$ligne['qte']."</td><td>".addslashes($ligne['marque'])."<br>".addslashes($ligne['artcode_eurocave'])."</td><td>".preg_replace('/\n|\r/',' ',preg_replace('/"/',"'",$ligne['lib']))."</td><td>".($envoi['enlevement']!=='0'?date("d/m/Y",$envoi['enlevement']):date('d-m-Y',$ligne['indate']))."</td><td></td></tr>";
				}
				$societe_enlev=$DB_site2->query_first("SELECT societe,ville FROM fournisseur WHERE id=".$envoi['enlev']);
				$body_relance.="</tbody></table><br>Pouvez-vous nous confirmer que cette commande a bien été expédiée/sera expédiée à date par ".$societe_enlev['societe']." " .$societe_enlev['ville']." ? <b>En cas de report, nous indiquer une nouvelle date ci-dessus.</b><br><br>Nous vous remercions par avance de votre réponse, <b>sous 24h maximum</b>, par retour de ce mail.<br><br>Référence <b>#CDE".$facturid."#</b> : à rappeler dans toute correspondance dans l’objet du mail.<br>";
			}



			$return.="<tr style=\"background-color:#00dd00;\">
			<td rowspan=\"".($rowspan)."\">".$envoi['destinataire'].$lieuenlev."<br>".date("d/m/Y H:i",$envoi['date'])."<br>".$lf1['expediteur']."<br>$livrcmd $cancel</td>
			<td colspan=\"2\"></td>
			<td>$lieuenlev</td>
			<td>$lieulivr</td>
			<td rowspan=\"$rowspan\" onmouseover=\"document.getElementById('trspt$envoi[id]').style.visibility='visible';\" onmouseout=\"document.getElementById('trspt$envoi[id]').style.visibility='hidden';\"><div id=\"trspt$envoi[id]\" style=\"position:absolute;visibility:hidden;cursor:pointer;\"><img src=\"images/editer.gif\" onclick=\"document.getElementById('selecttrspt$envoi[id]').style.display='list-item';document.getElementById('tdtrspt$envoi[id]').style.display='none';document.getElementById('trspt$envoi[id]').style.display='none';\"></div>Transport<br><select id=\"selecttrspt$envoi[id]\" style=\"display:none;\" onchange=\"document.location='facture.php?action=editer&facturid=$facturid&newtrspt='+this.value+'&efid=$envoi[id]#adv'\">$opt</select><div id=\"tdtrspt$envoi[id]\">$envoi[trspt]</div></td>
			<td rowspan=\"$rowspan\">Tarif<br>".$envoi['tarif']."&nbsp;".$devises->symbole($envoi['devise'])."</td>
			<td rowspan=\"$rowspan\">
                <div>
                <div style=\"margin-bottom:5px;\">".($envoi['date_relance']!=='0'?'Relance faite le<br>'.date("d/m/Y",$envoi['date_relance']).'<br>':'')."<span class=\"btn_relance\" onclick=\"mail_auto('adv@mon-espace-cuisson.com','','adv@mon-espace-cuisson.com','$sujet_relance','".addslashes($body_relance)."',undefined,undefined,false,null,".$envoi['id'].");mail_suivi('suivi_fournisseur',".$facturid.");add_exp_list('contact','contact@mon-espace-cuisson.com','base');\">Relancer</span></div>
                    <span class=\"infobtn\" onclick=\"this.parentNode.querySelector('.infocmdpopup').style.display='block';\">Info compta <img src=\"images/editer.gif\"></span>
                    <div class=\"infocmdtxt\">$envoi[comment]</div>
                    <div class=\"infocmdpopup\">
                        <textarea id=\"comment$envoi[id]\">$envoi[comment]</textarea>
                        <img src=\"images/icon-x.svg\" title=\"Annuler\" onclick=\"this.parentNode.style.display='none';\"> <img src=\"images/coche_verte.svg\" onclick=\"updcomptacomment($envoi[id]);\" title=\"Mettre à jour\">
                    </div>
                </div>
            </td>
			</tr>";
			$total+=$envoi['tarif'];

			mysqli_data_seek($r2,0);
			while($lf=$DB_site2->fetch_array($r2)){
				$color1=($lf['indate']>=$today && $lf['indate']<$tomorrow?" style=\"color:red;\"":"");
				$color2=($lf['outdate']>=$today && $lf['outdate']<$tomorrow?" style=\"color:red;\"":"");
				$imperative=($lf['imperatif']==1?"imperative":"maxi");
				$img="";
				$path="../configurations/".$host."/images/produits/br/".$lf['artid'].".jpg";
				if(file_exists($path)) $img="<img src=\"$path\" style=\"width:30px;\" onmouseover=\"this.style.position='absolute';this.style.width='200px';\" onmouseout=\"this.style.position='relative';this.style.width='30px';\">";
				$return.="<tr style=\"background-color:#99dd99;\">
				<td>$img</td>
				<td title=\"$lf[libelle]\" style=\"max-width: 141px;\">".$lf['qte']." ".($lf['artcode']==''?$lf['libelle']:$lf['artcode'])."<br>".$artref[$lf['lignefactureid']]."</td>
				<td$color1 onmouseover=\"document.getElementById('fournindate$lf[id]').style.visibility='visible';\" onmouseout=\"document.getElementById('fournindate$lf[id]').style.visibility='hidden';\"><div id=\"fournindate$lf[id]\" style=\"position:absolute;visibility:hidden;cursor:pointer;\"><img src=\"images/editer.gif\" onclick=\"calendrier_show(undefined,'',false,updadvdate);upddatetype='in';efl='$lf[id]';\" title=\"modifier\"><img src=\"images/supprimer.gif\" title=\"supprimer\" onclick=\"upddatetype='in';efl='$lf[id]';cal_timestamp=0;updadvdate();\"></div>Date enlèv.<br>".($lf['indate']>0 && $lf['origin_in']!=$lf['indate']?date("d/m/Y",$lf['origin_in'])."<br>=>":"").($lf['indate']>0?date("d/m/Y",$lf['indate']):"-")."</td>
				<td$color2 onmouseover=\"document.getElementById('fournoutdate$lf[id]').style.visibility='visible';\" onmouseout=\"document.getElementById('fournoutdate$lf[id]').style.visibility='hidden';\"><div id=\"fournoutdate$lf[id]\" style=\"position:absolute;visibility:hidden;cursor:pointer;\"><img src=\"images/editer.gif\" onclick=\"calendrier_show(undefined,'',false,updadvdate);upddatetype='out';efl='$lf[id]';\"><img src=\"images/supprimer.gif\" title=\"supprimer\" onclick=\"upddatetype='out';efl='$lf[id]';cal_timestamp=0;updadvdate();\"></div>Date livr. $imperative<br>".($lf['outdate']>0 && $lf['origin_out']!=$lf['outdate']?date("d/m/Y",$lf['origin_out'])."<br>=>":"").($lf['outdate']>0?date("d/m/Y",$lf['outdate']):"-")."</td>
				</tr>";
			}

		}

	}

	$liste=$adv->listadv($facturid,"stock");
	$advlfids=array();
	$sql="";
	foreach($liste as $advprod){

		if($advprod->efid==0){
			$txtenvoi="Pas d'envoi programmé";
			if($advprod->envoi>0) $txtenvoi="Envoi le ".date("d/m",$advprod->envoi)." à ".$advprod->envoi_heure."h";
			// modif TSI:
			$return.="<tr style=\"background-color:#FFAA22;\"><td rowspan=\"".(count($advprod->lignes)+1)."\" style=\"color:black;\" nowrap>Commande enregistrée<br>"
			."<a href=\"fournisseurs_liste.php?action=editer&fournisseur=".$advprod->destinataire."\" target=\"_Blank\">".$advprod->destinataire."&nbsp;<img src=\"./images/visible.png\" style=\"width:15px;background-color:#FFFFFF;\" ></a>"
			."<br>".date("d/m/Y H:i",$advprod->date_creation)."<br>".$advprod->utilisateur." <br><a href=\"facture.php?action=editer&facturid=$facturid&advid=".$advprod->id."&type=stock#adv\">Modifier</a> <a href=\"javascript:deladv(".$advprod->id.",".$advprod->envoi.");\" style=\"color:red;\">Supprimer</a><br>$txtenvoi</td><td colspan=\"7\"></td></tr>";
			foreach($advprod->lignes as $lignefactureid=>$qte){
				$advlfids[]=$lignefactureid;
				$lignefacture=$DB_site2->query_first("SELECT * FROM lignefacture WHERE lignefactureid=".$lignefactureid);
				$img="";
				$path="../configurations/".$host."/images/produits/br/".$lignefacture['artid'].".jpg";
				if(file_exists($path)) $img="<img src=\"$path\" style=\"width:30px;\" onmouseover=\"this.style.position='absolute';this.style.width='200px';\" onmouseout=\"this.style.position='relative';this.style.width='30px';\">";
				$return.="<tr style=\"background-color:#FFCC99;\">
				<td>$img</td>
				<td title=\"$lignefacture[libelle]\" style=\"max-width: 141px;\">".$lignefacture['qte']." ".($lignefacture['artcode']==''?$lignefacture['libelle']:$lignefacture['artcode'])."<br>".$artref[$lignefactureid]."</td>
				<td>Date enlèv.<br>".($advprod->fields['trsptmode']=="parenlevement"?$advprod->fields['parenlevindate']:"").($advprod->fields['trsptmode']=="parvossoins"?$advprod->fields['parvossoinsindate']:"")."</td>
				<td>Date livr.<br>".($advprod->fields['trsptmode']=="parvossoins"?$advprod->fields['parvossoinsoutdate']:"")."</td>
				<td>".$advprod->fields['selecttrspt']."</td>
				<td></td>
				<td></td>
				</tr>";
			}
		}
	}

	if($advlfids!=array()) $sql=" AND lignefactureid NOT IN (".implode(",",$advlfids).")";

	$stocknb=$stocknbreserve=array();
	$lieustock0=array();
	$req=$DB_site2->query("SELECT * FROM lignefacture lf WHERE factureid=$facturid");
	while($lignefacture=$DB_site2->fetch_array($req)){
		$stockartid = $stock->similaire($lignefacture['artid']);
		$stockitems = $stock->stockbyartid($stockartid);

		foreach($stockitems as $stockitem){
			if($stockitem->state == 1 || ($stockitem->state == 2 && $stockitem->dest=="facture ".$facturid)){
				$stocknb[$lignefacture['artid']]++;
				if($stockitem->state == 2 ) $stocknbreserve[$lignefacture['artid']]++;
			}
			if($stockitem->state==0 && ($stockitem->dest=="" || $stockitem->dest=="facture ".$facturid)){
				if(!isset($lieustock0[$stockitem->artid][$stockitem->lieu]))
					$lieustock0[$stockitem->artid][$stockitem->lieu]=$stockitem;
				else
					$lieustock0[$stockitem->artid][$stockitem->lieu]->qte++;
			}
		}
	}

	if(count($stocknb)>0){
		$stockartids=array_keys($stocknb);

		$req=$DB_site2->query("SELECT * FROM lignefacture lf WHERE factureid=$facturid AND artid IN (".implode(",",$stockartids).") AND (SELECT COUNT(ligne_facture_id) FROM envoi_fournisseur_ligne WHERE ligne_facture_id=lf.lignefactureid AND type='stock')=0".$sql);
		$stockartids1nb=$DB_site2->num_rows($req);
		if($stockartids1nb>0) $return.="<tr style=\"background-color:#FF2222;\"><td rowspan=\"".($stockartids1nb+1)."\" style=\"color:black;\">Articles non commandés<br><a href=\"facture.php?facturid=$facturid&action=editer&type=stock&advid=0#adv\">Commander</a></td><td colspan=\"7\"></td></tr>";

		while($nocmd=$DB_site2->fetch_array($req)){

			$cmddate=$enlevdate=$livrdate="";
			$cmdf="stock";
			$rc=$DB_site2->query("SELECT * FROM calendrier WHERE type='stockcmd' AND objid=".$nocmd['lignefactureid']);
			$cal=$DB_site2->fetch_array($rc);
			if($cal!=false){
				$cmdf=$cal['txt'];
				if($cal['date']>0) $cmddate=date("d/m/Y",$cal['date']);
				if($cal['enlevement']>0) $enlevdate=date("d/m/Y",$cal['enlevement']);
				if($cal['livraison']>0) $livrdate=date("d/m/Y",$cal['livraison']);
			}
			$img="";
			$path="../configurations/".$host."/images/produits/br/".$nocmd['artid'].".jpg";
			if(file_exists($path)) $img="<img src=\"$path\" style=\"width:30px;\" onmouseover=\"this.style.position='absolute';this.style.width='200px';\" onmouseout=\"this.style.position='relative';this.style.width='30px';\">";

			$reserve=($stocknbreserve[$nocmd['artid']]>0?"<p style=\"color:#6d6c6c;font-style:italic;\">Réservé</p>":"");

			$return.="<tr class=\"".$nocmd['artcode']."\" style=\"background-color:#FF9999;\">
			<td>$img</td>
			<td title=\"$nocmd[libelle]\">".$nocmd['qte']." ".$nocmd['artcode']."<br>".$artref[$nocmd['lignefactureid']].$reserve."</td>
			<td colspan=\"5\" style=\"white-space: nowrap;\">
			<table>
			<tr><td>Date cde</td><td>Date enlèv.</td><td>Date livr.</td><td>Info</td></tr>
			<tr>
			<td><input type=\"text\" name=\"cmddate$nocmd[lignefactureid]\" id=\"cmddate$nocmd[lignefactureid]\" value=\"$cmddate\" onclick=\"cmddate(this.id,'stockcmd',$nocmd[lignefactureid],'date')\" size=\"10\"></td>
			<td><input type=\"text\" id=\"enl$nocmd[lignefactureid]\" size=\"10\" onclick=\"cmddate(this.id,'stockcmd',$nocmd[lignefactureid],'enlevement')\" value=\"$enlevdate\"></td>
			<td><input type=\"text\" id=\"liv$nocmd[lignefactureid]\" size=\"10\" onclick=\"cmddate(this.id,'stockcmd',$nocmd[lignefactureid],'livraison')\" value=\"$livrdate\"></td>
			<td><input type=\"text\" id=\"dcmdf$nocmd[lignefactureid]\" size=\"35\" value=\"$cmdf\" onclick=\"if(this.value=='fournisseur')this.value='';document.getElementById('okdcmdf$nocmd[lignefactureid]').style.display='inline';\"><a href=\"javascript:cmdtxt('stockcmd',$nocmd[lignefactureid],document.getElementById('dcmdf$nocmd[lignefactureid]').value)\" id=\"okdcmdf$nocmd[lignefactureid]\" value=\"OK\" style=\"display:none;\">Mettre à jour</a></td>
			</tr>
			</table>
			</td>
			</tr>";
		}
	}

	if(count($lieustock0)>0){
		$stock0artids=array_keys($lieustock0);
		$sql="SELECT * FROM lignefacture lf WHERE factureid=$facturid ";
		if(!is_null($stockartids)) $sql.="AND artid NOT IN (".implode(",",$stockartids).") ";
		$sql.="AND artid IN (".implode(",",$stock0artids).") ";
		$sql.="AND artid!=0 AND (SELECT COUNT(ligne_facture_id) FROM envoi_fournisseur_ligne WHERE ligne_facture_id=lf.lignefactureid AND type='stock')=0";

		$req=$DB_site2->query($sql);
		$stockartids0nb=$DB_site2->num_rows($req);
		if($stockartids0nb>0) $return.="<tr style=\"background:#AAA;\"><td rowspan=\"".($stockartids0nb+1)."\">Articles en attente de livraison</td></tr>";
		while($nocmd=$DB_site2->fetch_array($req)){
			$img="";
			$path="../configurations/".$host."/images/produits/br/".$nocmd['artid'].".jpg";
			if(file_exists($path)) $img="<img src=\"$path\" style=\"width:30px;\" onmouseover=\"this.style.position='absolute';this.style.width='200px';\" onmouseout=\"this.style.position='relative';this.style.width='30px';\">";

			$return.="<tr style=\"background-color:#CCC;\">
			<td>$img</td>
			<td title=\"$nocmd[libelle]\">".$nocmd['qte']." ".$nocmd['artcode']."<br>".$artref[$nocmd['lignefactureid']].$reserve."</td>
			<td colspan=\"5\" style=\"white-space: nowrap;\">";

			foreach($lieustock0[$nocmd['artid']] as $fournid=>$item){
				$fourn=$DB_site2->query_first("SELECT societe,ville FROM fournisseur WHERE id=".$fournid);
				$return.="Voir tableau stock <a href=\"#detail_stock\">ci-dessous</a>";
				//$return.=$fourn['societe']." ".$fourn['ville']." ".date("d/m/Y",$item->date_livraison)." : ".$item->qte."<br>";
			}
			$return.="</td></tr>";
		}
	}

	if($stockartids0nb+$stockartids1nb==0)
		$return.="<tr style=\"background-color:#CCC;\"><td colspan=\"5\">Aucun produit en stock</td></tr>";

	$return.="</table>";

	return $return;

}


// --------------------------------------------------------------------------------- //
// --------------------------------------------------------------------------------- //
// --------------------------------------------------------------------------------- //
// fonction qui affiche le tableau pour TRANSPORTEUR                                 //
// --------------------------------------------------------------------------------- //
// --------------------------------------------------------------------------------- //
// --------------------------------------------------------------------------------- //

function trspt_recap($facturid){
	global $DB_site2,$host;
	global $adv;
	global $devises;

	$f=new facture($DB_site2,$facturid);
	$trsptclient=$f->transportsClient();
	
	$infoadv=$DB_site2->query_first("SELECT suivi FROM suivi_commande FORCE INDEX (facturid) WHERE type='advtrspt' AND facturid=".$facturid." ORDER BY date DESC LIMIT 1");
	
      //transporteur
	$return="<br><table id=\"trspt_recap\" width=\"100%\">
	<tr>0
            <td colspan=\"8\" style=\"color:white;background-color:#AAA; font-weight:bold;\">
                <img src=\"images/livraison.png\" width=\"25\" align=\"absmiddle\"> Transporteur
                <a href=\"javascript:mail_trspt('multi')\">Envoi email transport multiple</a>
            </td>
        </tr>
        <tr style=\"background-color:#AAA;\">
            <td style=\"background-color:#AAA;\">
                <span class=\"infobtn\" onclick=\"this.parentNode.querySelector('.infocmdpopup').style.display='block';\">Info ADV <img src=\"images/editer.gif\"></span>
                <span class=\"infocmdtxt\">$infoadv[suivi]</span>
                <div class=\"infocmdpopup\">
                    <textarea id=\"trsptadvcomment\">$infoadv[suivi]</textarea>
                    <img src=\"images/icon-x.svg\" title=\"Annuler\" onclick=\"this.parentNode.style.display='none';\"> <img src=\"images/coche_verte.svg\" onclick=\"updadvcomment('trspt')\" title=\"Mettre à jour\">
                </div>
            </td>";

    //// tâche 6875 : permet de changer toutes les dates en même temps (date enlèvement ou date livraison)
	$r3=$DB_site2->query("SELECT * FROM envoi_fournisseur_ligne efl INNER JOIN lignefacture lf ON (lf.lignefactureid=efl.ligne_facture_id) WHERE efl.type='trspt' AND facture!='STOCK' AND factureid = ".$facturid);
	$fourn_liste=array();
	while($fourn=$DB_site2->fetch_array($r3)){
		$fourn_liste[]=$fourn['id'];
		$date_enlev_in=$fourn['indate'];
		$date_enlev_origin=$fourn['origin_in'];
		$date_livr_out=$fourn['outdate'];
		$date_livr_origin=$fourn['origin_out'];
		$date_enlev[]=$date_enlev_in;
		$date_livr[]=$date_livr_out;
	}
	$arr_unique=($date_enlev!='' ? array_unique($date_enlev) : ''); 
	$arr_unique2=($date_livr!='' ? array_unique($date_livr) : '');

	$fourn_liste=implode(',',$fourn_liste);

	$liste=$adv->listadv($facturid,"trspt");
	$advlfids=array();
	$sql="";
	foreach($liste as $advprod){
		if($advprod->efid==0){
			$tdoranget=true;
		}
	}

	$return.="
	<td></td>
	<td></td>";
	if($DB_site2->num_rows($r3) > 0) {
		$return.="
		<td style=\"background-color:#AAA;text-align:left\" onmouseover=\"document.getElementById('updateenlev_trspt_all').style.visibility='visible';\" onmouseout=\"document.getElementById('updateenlev_trspt_all').style.visibility='hidden';\">
		<div id=\"updateenlev_trspt_all\" style=\"position:absolute;visibility:hidden;cursor:pointer;background-color:white;\" onclick=\"calendrier_show(undefined,'',false,updadvdateall);upddatetype='in';efl='".$fourn_liste."';\">
		<img src=\"images/editer.gif\" title=\"modifier\">
		(tout modifier)
		</div>
		Date enlèv.<br>".(count($arr_unique)<2 && $date_enlev_in>0 && $date_enlev_origin!=$date_enlev_in?date("d/m/Y",$date_enlev_origin)."<br>=>":"").(count($arr_unique)<2 && $date_enlev_in>0?date("d/m/Y",$date_enlev_in):"-")."
		</td>
		<td onmouseover=\"document.getElementById('updatelivr_trspt_all').style.visibility='visible';\" onmouseout=\"document.getElementById('updatelivr_trspt_all').style.visibility='hidden';\">
		<div id=\"updatelivr_trspt_all\" style=\"position:absolute;visibility:hidden;cursor:pointer;background-color:white;\" onclick=\"calendrier_show(undefined,'',false,updadvdateall);upddatetype='out';efl='".$fourn_liste."';\">
		<img src=\"images/editer.gif\" title=\"modifier\">
		(tout modifier)
		</div>
		Date livr. maxi<br>".(count($arr_unique2)<2 && $date_livr_out>0 && $date_livr_origin!=$date_livr_out?date("d/m/Y",$date_livr_origin)."<br>=>":"").(count($arr_unique2)<2 && $date_livr_out>0?date("d/m/Y",$date_livr_out):"-")."
		</td>
		<td></td>
		<td></td>";
		}
		elseif($tdoranget==false) {
			//// tache 6929 - pour ajouter/modifier date pour tous les produits
			if($advlfids!=array()) $sql=" AND lignefactureid NOT IN (".implode(",",$advlfids).")";
			$req=$DB_site2->query("SELECT * FROM lignefacture lf WHERE factureid=$facturid AND (SELECT COUNT(ligne_facture_id) FROM envoi_fournisseur_ligne WHERE ligne_facture_id=lf.lignefactureid AND type='trsptcmd')=0".$sql);
			$lignfac=array();
			while($nocmd=$DB_site2->fetch_array($req)){
				$lignfac[]=$nocmd['lignefactureid'];
			}
			$lignfac=implode(',',$lignfac);

			$return.="
			<td style=\"background-color:#AAA;text-align:left\" colspan=\"3\">
			A commander le <input type=\"text\" name=\"datetrspt\" id=\"datetrspt\" value=\"\" onclick=\"cmddateall(this.id,'trsptcmd','$lignfac','date')\" size=\"10\"> à <input type=\"text\" id=\"desttrspt\" value=\"\" onclick=\"if(this.value=='installateur')this.value='';document.getElementById('oktrspt').style.display='inline';\" size=\"35\"><input type=\"button\" id=\"oktrspt\" style=\"display:none;\" value=\"OK\" onclick=\"cmdtxtall('trsptcmd','$lignfac',document.getElementById('desttrspt').value);\">
			<span id=\"acmd_trspt_all\" style=\"background-color:white;width:max-content;\">
			(tout modifier)
			</span>
			</td>";
		//// fin tache 6929
		}
		else {
			$return.="
		<td></td>";
		}
		$return.="
		<td></td>
		</tr>";
    //// fin tâche 6875


	$compat=true;
	$livrcmd="";
	$stocklivr=array();
	$r=$DB_site2->query("SELECT * FROM envoi_fournisseur WHERE type='trspt' AND facturid=$facturid");
	$nombrelivraison=$DB_site2->num_rows($r);
	while($envoi=$DB_site2->fetch_array($r)){
		$r2=$DB_site2->query("SELECT * FROM envoi_fournisseur_ligne efl INNER JOIN lignefacture lf ON (lf.lignefactureid=efl.ligne_facture_id) WHERE efid=".$envoi['id']." AND efl.type='trspt'");
		$nolivr=false;
		$livrcmd="";
		$tracking="";
		$email_livraison="";
		$station_chargeur="";
		$produitsBonLivraison="";

		if($DB_site2->num_rows($r2)>0){
			$lf1=$DB_site2->fetch_array($r2);
			$rowspan=$DB_site2->num_rows($r2)+1;
			
			
			
			if($nombrelivraison>1){
				$produitssql=$DB_site2->query("SELECT efl.id, lf.qte, efl.ligne_facture_id FROM envoi_fournisseur_ligne efl INNER JOIN lignefacture lf ON (lf.lignefactureid=efl.ligne_facture_id) WHERE efid=".$envoi['id']." AND efl.type='trspt'");
				while($produitsres=$DB_site2->fetch_array($produitssql)){
					$produitsBonLivraison.="&produits[]=".$produitsres['qte']."-".$produitsres['ligne_facture_id'];
				}
			}
			
			$depart=$arrivee="";
			if($envoi['destinataire']!="0"){
				$deladv=new adv($DB_site2);
				$result=$deladv->load_from_efid($envoi['id']);

				if($lf1['indate']!=0){
					if($envoi['enlevement']==0){
						$nolivr=true;
						$livrcmd="<input type=\"text\" id=\"datelivrcmd$envoi[id]\" size=\"10\" value=\"Date enlèv.\" onclick=\"efid=$envoi[id];this.value='';calendrier_show(this.id,this.value,false,updenlevcmd);\">";

						if($result) $cancel="<br><a href=\"facture.php?facturid=$facturid&action=editer&type=trspt&advid=".$deladv->id."&annule=1#adv\" style=\"color:red;\">Annuler la commande</a><br><a href=\"facture.php?facturid=$facturid&action=editer&type=trspt&advid=".$deladv->id."&modif=1#adv\">Modifier la commande</a>";
					}else {
						$livrcmd="Enlevée le ".date("d/m/Y",$envoi['enlevement']);
						if($envoi['livraison']<1){
							$livrcmd .= "<img src=\"images/supprimer.gif\" title=\"supprimer\" onclick=\"	upddatetype='enlevement';ef='$envoi[id]';cal_timestamp=0;updadvdate2();\">";
						}
						$livrcmd .="<br>";
					}

				}
				if(!$nolivr){
					if($envoi['livraison']==0)
						$livrcmd.="<input type=\"text\" id=\"datelivrcmd$envoi[id]\" size=\"10\" value=\"Date livraison\" onclick=\"efid=$envoi[id];this.value='';calendrier_show(this.id,this.value,false,updlivrcmd);\"><br><a href=\"javascript:efid=$envoi[id];cal_timestamp=-1;updlivrcmd();\" style=\"color:black;\">Livraison annulée</a>";
					elseif($envoi['livraison']==-1)
						$livrcmd.="<span style=\"color:black;white-space:nowrap;font-weight:bold;font-size:11px;\">Livraison annulée</span>";
					else
						$livrcmd.="Livrée le ".date("d/m/Y",$envoi['livraison']);
				}

				if($envoi['enlev']!="0"){
					$fdepart=$DB_site2->query_first("SELECT societe,ville FROM fournisseur WHERE id=".$envoi['enlev']);
					if(!is_null($fdepart))
						$depart=$fdepart['societe']." ".$fdepart['ville'];
					else
						$depart="?";
				}

				if($envoi['livr']=="0")
					$arrivee="=&gt; Client";
				else{
					$lieuarriv=$DB_site2->query_first("SELECT societe,ville,type FROM fournisseur WHERE id=".$envoi['livr']);
					$arrivee="=&gt; ".$lieuarriv['societe']." ".$lieuarriv['ville'];
					if($lieuarriv['type']=="fourn"){
						$stockadv=new adv($DB_site2);
						$stockadv->load_from_lignefactureid($lf1['lignefactureid'],"stock");
						if($stockadv!=false && $stockadv->fields['trsptmode']=="parenlevement") $stocklivr[]=$envoi['id'];
					}
				}

				// if($envoi['destinataire']=="GLS"){
				// 	$tracking="<div id=\"tracklink".$envoi['id']."\" ".($envoi['trackid']==""?"style=\"display:none;\"":"")."><a href=\"http://www.gls-group.eu/276-I-PORTAL-WEB/content/GLS/FR01/FR/5004.htm?txtRefNo=".$envoi['trackid']."&txtAction=71000\" target=\"_blank\">".$envoi['trackid']."</a> <img src=\"images/editer.gif\" onclick=\"edit_tracking(".$envoi['id'].")\"></div>
				// 	<div id=\"trackedit".$envoi['id']."\" ".($envoi['trackid']!=""?"style=\"display:none;\"":"")."><input type=\"text\" id=\"trackid".$envoi['id']."\" value=\"".($envoi['trackid']!=""?$envoi['trackid']:"Tracking")."\" size=\"10\" onclick=\"if(this.value=='Tracking') this.value=''\"><a href=\"javascript:void(0);\" onclick=\"document.location='facture.php?action=editer&facturid=$facturid&trackid='+document.getElementById('trackid".$envoi['id']."').value+'&efid=".$envoi['id']."';\">OK</a></div>";
				// }

				switch ($envoi['destinataire']) {
					case 'GLS':
						$tracking="
							<div id=\"tracklink".$envoi['id']."\" ".($envoi['trackid'] == "" ? "style=\"display:none;\"" : "").">
								<a href=\"http://www.gls-group.eu/276-I-PORTAL-WEB/content/GLS/FR01/FR/5004.htm?txtRefNo=".$envoi['trackid']."&txtAction=71000\" target=\"_blank\">".$envoi['trackid']."</a> 
								<img src=\"images/editer.gif\" onclick=\"edit_tracking(".$envoi['id'].")\">
							</div>
							<div id=\"trackedit".$envoi['id']."\" ".($envoi['trackid'] != "" ? "style=\"display:none;\"" : "").">
								<input type=\"text\" id=\"trackid".$envoi['id']."\" value=\"".($envoi['trackid'] != "" ? $envoi['trackid'] : "Tracking")."\" size=\"10\" onclick=\"if(this.value=='Tracking') this.value=''\">
								<a href=\"javascript:void(0);\" onclick=\"document.location='facture.php?action=editer&facturid=$facturid&trackid='+document.getElementById('trackid".$envoi['id']."').value+'&efid=".$envoi['id']."';\">OK</a>
							</div>";
						break;

					case 'SCHENKER Mulhouse':
					case 'DB Schenker France':
					case 'DB Schenker National':
					case 'DB Schenker Lyon':
					case 'DB Schenker International':
					case 'DB Schenker Chambery':
						if(is_numeric($envoi['trackid'])) $trackurl = "https://eschenker.dbschenker.com/nges-portal/public/fr-FR_FR/#!/tracking/customer-search?refType=aam&refNumber=".$envoi['trackid']."&transportMode=LAND"; // -> Trackid récépissé
						else $trackurl = "https://eschenker.dbschenker.com/nges-portal/public/fr-FR_FR/#!/tracking/schenker-search?refNumber=".$envoi['trackid']; // -> Trackid STT

						$tracking="
							<div id=\"tracklink".$envoi['id']."\" ".($envoi['trackid'] == "" ? "style=\"display:none;\"" : "").">
								<a href=\"".$trackurl."\" target=\"_blank\">".$envoi['trackid']."</a> 
								<img src=\"images/editer.gif\" onclick=\"edit_tracking(".$envoi['id'].")\">
							</div>
							<div id=\"trackedit".$envoi['id']."\" ".($envoi['trackid'] != "" ? "style=\"display:none;\"" : "").">
								<input type=\"text\" id=\"trackid".$envoi['id']."\" value=\"".($envoi['trackid'] != "" ? $envoi['trackid'] : "")."\" size=\"10\" placeholder=\"n° récépissé OU STT\" onclick=\"if(this.value=='Tracking') this.value=''\">
								<a href=\"javascript:void(0);\" onclick=\"document.location='facture.php?action=editer&facturid=$facturid&trackid='+document.getElementById('trackid".$envoi['id']."').value+'&efid=".$envoi['id']."';\">OK</a>
							</div>";

						$station_chargeur = ""
						. "<br>"
						. "<a href=\"station_chargeur.php?advid=".$deladv->id."&transporteur=schenkernat\" target=\"_blank\"><img src=\"images/icon/pdf.png\" style=\"width: 20px; cursor: pointer; vertical-align: -7px; margin-top: 3px;\"> Export station chargeur</a>";
						break;

					case 'TNT International':
						$tracking="
							<div id=\"tracklink".$envoi['id']."\" ".($envoi['trackid'] == "" ? "style=\"display:none;\"" : "").">
								<a href=\"http://www.tnt.fr/public/suivi_colis/recherche/visubontransport.do?bonTransport=".$envoi['trackid']."&radiochoixrecherche=BT&radiochoixtypeexpedition=INT\" target=\"_blank\">".$facturid."</a> 
								<img src=\"images/editer.gif\" onclick=\"edit_tracking(".$envoi['id'].")\">
							</div>
							<div id=\"trackedit".$envoi['id']."\" ".($envoi['trackid'] != "" ? "style=\"display:none;\"" : "").">
								<input type=\"text\" id=\"trackid".$envoi['id']."\" value=\"".($envoi['trackid'] != "" ? $envoi['trackid'] : "")."\" size=\"10\" onclick=\"if(this.value=='Tracking') this.value=''\">
								<a href=\"javascript:void(0);\" onclick=\"document.location='facture.php?action=editer&facturid=$facturid&trackid='+document.getElementById('trackid".$envoi['id']."').value+'&efid=".$envoi['id']."';\">OK</a>
							</div>";
						break;

					case 'FEDEX Express':
						$tracking="
							<div id=\"tracklink".$envoi['id']."\" ".($envoi['trackid'] == "" ? "style=\"display:none;\"" : "").">
								<a href=\"http://www.tnt.fr/public/suivi_colis/recherche/visubontransport.do?bonTransport=".$envoi['trackid']."&radiochoixrecherche=BT&radiochoixtypeexpedition=NAT\" target=\"_blank\">".$envoi['trackid']."</a> 
								<img src=\"images/editer.gif\" onclick=\"edit_tracking(".$envoi['id'].")\">
							</div>
							<div id=\"trackedit".$envoi['id']."\" ".($envoi['trackid'] != "" ? "style=\"display:none;\"" : "").">
								<input type=\"text\" id=\"trackid".$envoi['id']."\" value=\"".($envoi['trackid'] != "" ? $envoi['trackid'] : "Bon transport")."\" size=\"10\" onclick=\"if(this.value=='Tracking') this.value=''\">
								<a href=\"javascript:void(0);\" onclick=\"document.location='facture.php?action=editer&facturid=$facturid&trackid='+document.getElementById('trackid".$envoi['id']."').value+'&efid=".$envoi['id']."';\">OK</a>
							</div>";
						break;
					case 'HEPPNER LYON':	
					case 'HEPPNER Le Mans':
						
						$trackurl = "http://portail.heppner.fr/webster/DetailPositionDirectAction.do?idTiers=31638&idAlias=864&reference=".$facturid."&lg=fr";
						$tracking = ""
						."<div id=\"trackedit".$envoi['id']."\" ".($envoi['trackid'] != "" ? "style=\"display:none;\"" : "").">
                                                        <input type=\"text\" id=\"trackid".$envoi['id']."\" value=\"".($envoi['trackid'] != "" ? $envoi['trackid'] : "Tracking")."\" size=\"10\" onclick=\"if(this.value=='Tracking') this.value=''\">
                                                        <a href=\"javascript:void(0);\" onclick=\"document.location='facture.php?action=editer&facturid=$facturid&trackid='+document.getElementById('trackid".$envoi['id']."').value+'&efid=".$envoi['id']."';\">OK</a>
                                                </div>"
						. "<div id=\"tracklink".$envoi['id']."\" style=\"display: block;\">"
							. "<a href=\"".$trackurl."\" target=\"_blank\">".$facturid." (tracking)</a>"
							."<img src=\"images/editer.gif\" onclick=\"edit_tracking(".$envoi['id'].")\">"
						. "</div>";
					
						$station_chargeur = ""
						. "<br>"
						. "<a href=\"station_chargeur.php?advid=".$deladv->id."&transporteur=heppner\" target=\"_blank\"><img src=\"images/icon/pdf.png\" style=\"width: 20px; cursor: pointer; vertical-align: -7px; margin-top: 3px;\"> Export station chargeur</a>";
						break;
				}
				if(isset($trsptclient[$envoi['id']])){
                                    $email_livraison="<br><br><input type=\"button\" onclick=\"mail_trspt('".$trsptclient[$envoi['id']]."',".$envoi['id'].");\" value=\"Mail expé.\">";
                                }

			}else{
				$envoi['destinataire']="Pas de commande";
				$cancel="<a href=\"javascript:deladvcmd($envoi[id])\" style=\"color:red;\">Annuler la commande</a>";
			}

			$sql = "SELECT passefacture";
			$sql .= " FROM facture";
			$sql .= " WHERE facturid = ".$facturid;
			$passeFacture = $DB_site2->query_first($sql)[0];
			

			$body_relance="";
			$livrtype="client";
			$text_livr="";
			if(($envoi['enlevement']==0 || $envoi['livraison']==0) && $envoi['destinataire']!=="Pas de commande"){
				$ligne_article_envoi=$DB_site2->query("SELECT a.artcode_eurocave,lf.libelle AS lib,m.libelle AS marque, efl.qte, efl.paht, efl.ecopart, efl.outdate, efl.indate FROM article a INNER JOIN article_marque am ON(am.artid=a.artid) INNER JOIN marque m ON(m.marqueid=am.marqueid) INNER JOIN lignefacture lf ON lf.artid = a.artid INNER JOIN envoi_fournisseur_ligne efl ON efl.ligne_facture_id = lf.lignefactureid WHERE efl.efid=".$envoi['id']." AND efl.type='trspt' AND facture!='STOCK'");
				$sujet_relance="Suivi de livraison #CDE".$facturid."#";
				if($envoi['trackid']!==''){
					$sujet_relance.= " - Récépissé N°".$envoi['trackid'];
				}
				$adv->load_from_efid($envoi['id']);
				if(substr($adv->fields['trsptlivr'],0,3)=="sls"){
					$livrid=substr($adv->fields['trsptlivr'],3);
					$livr=$DB_site2->query_first("SELECT * FROM fournisseur WHERE id='$livrid'");
					$livrtype="sls";
					$rdv_tel=$livr['telephone'];
				}

				if(substr($adv->fields['trsptlivr'],0,5)=="stock"){
					$livrid=substr($adv->fields['trsptlivr'],5);
					$livr=$DB_site2->query_first("SELECT * FROM fournisseur WHERE id='$livrid'");
					$livrtype="stock";
					$rdv_tel=$livr['telephone'];
				}

				if(substr($adv->fields['trsptlivr'],0,5)=="fourn"){
					$livrid=substr($adv->fields['trsptlivr'],5);
					$livr=$DB_site2->query_first("SELECT * FROM fournisseur WHERE id='$livrid'");
					$livrtype="fourn";
					$rdv_tel=$livr['telephone'];
				}
				$adv_facture=$DB_site2->query_first("SELECT * FROM facture f, pays p WHERE f.paysid=p.paysid AND f.facturid=".$facturid);
				$rdv_tel=$adv_facture[telephone];
				switch($livrtype){
					case "sls":
					case "stock":
					case "fourn":
					$text_livr="<br>$livr[societe] $livr[ville]<br>$livr[adresse1]".($livr[adresse2]!=""?"<br>$livr[adresse2]":"")."<br>$livr[cp] $livr[ville]<br>tél: $livr[telephone] fax: $livr[fax]<br>$livr[mail]<br>$livr[horaires]";
					break;

					case "client":
					$text_livr="<br>$adv_facture[raisonsociale]<br>$adv_facture[nom] $adv_facture[prenom]<br>$adv_facture[adresse]<br>$adv_facture[codepostal] $adv_facture[ville]<br>$adv_facture[libelle]<br>$adv_facture[telephone]";
					$livrid=0;
					break;
				}
				$body_relance="Bonjour,<br><br>Pouvez-vous nous faire un point sur cette livraison :<br><br><b>Détail de la commande</b><table border='2' cellpadding='5' cellspacing='1' width='100%'><thead><tr><td style='font-weight:bold'>Qte:</td><td style='font-weight:bold'>Références:</td><td align='left' style='font-weight:bold'>&nbsp;Informations:</td><td>Date d'enlèvement</td><td>Date de livraison</td><td>Nouvelle date de livr. prévue</td></tr></thead><tbody>";
				foreach($ligne_article_envoi as $ligne){
					$body_relance.="<tr><td>".$ligne['qte']."</td><td>".addslashes($ligne['marque'])."<br>".addslashes($ligne['artcode_eurocave'])."</td><td>".preg_replace('/\n|\r/',' ',preg_replace('/"/',"'",$ligne['lib']))."</td><td>".($envoi['enlevement']!=='0'?date("d/m/Y",$envoi['enlevement']):date('d-m-Y',$ligne['indate']))."</td><td>".date('d-m-Y',$ligne['outdate'])."</td><td></td></tr>";
				}
				$body_relance.="</tbody></table><br><b>Adresse de livraison :</b>".$text_livr."<br><br>Pouvez-vous nous confirmer que cette commande a bien été livrée/sera livrée à date convenue ? <b>En cas de report, nous indiquer une nouvelle date ci-dessus.</b><br><br>Nous vous remercions par avance de votre réponse, <b>sous 24h maximum</b>, par retour de ce mail.<br><br>Référence <b>#CDE".$facturid."#</b> : à rappeler dans toute correspondance dans l’objet du mail.<br>";
			}

			
			
			// modif TSI: #2565 Ajout d'un lien directe sur le detail du fournisseur choisit
			$return .= ""
			. "<tr style=\"background-color:#00dd00;\">"
				. "<td rowspan=\"".$rowspan."\" style=\"position: relative;\"><a href=\"fournisseurs_liste.php?action=editer&fournisseur=".$envoi['destinataire']."\" target=\"_Blank\">"
					. $envoi['destinataire']
					."&nbsp;<img src=\"./images/visible.png\" style=\"width:15px;background-color:#FFFFFF;\" >"
					. "</a><br>"
					. $tracking
					. "<a href=\"/facture2.php?facturid=".$facturid."&passe=".$passeFacture."&cde=1".$produitsBonLivraison."\" target=\"_blank\"><img src=\"images/icon/pdf.png\" style=\"width: 20px; cursor: pointer; vertical-align: -7px; margin-top: 3px;\"> Bon de livraison</a>"
					. $station_chargeur
					. $email_livraison
					. "<br><br>"
					. date("d/m/Y H:i",$envoi['date'])
					. "<br>"
					. $lf1['expediteur']
					. "<br>"
					. $livrcmd . " " . $cancel
				. "</td>"
				. "<td colspan=\"2\"></td>"
				. "<td>".$depart."</td>"
				. "<td>".$arrivee."</td>"
				. "<td rowspan=\"".$rowspan."\">Transport<br>".$envoi['trspt']."</td>"
				. "<td rowspan=\"".$rowspan."\">Tarif<br>".$envoi['tarif']."&nbsp;".$devises->symbole($envoi['devise'])."</td>"
				. "<td rowspan=\"".$rowspan."\">
				<div style=\"margin-bottom:5px;\">".($envoi['date_relance']!=='0'?'Relance faite le<br>'.date("d/m/Y",$envoi['date_relance']).'<br>':'')."<span class=\"btn_relance\" onclick=\"mail_auto('adv@mon-espace-cuisson.com','','adv@mon-espace-cuisson.com','$sujet_relance','".addslashes($body_relance)."',undefined,undefined,false,null,".$envoi['id'].");mail_suivi('suivi_fournisseur',".$facturid.");add_exp_list('contact','contact@mon-espace-cuisson.com','base');\">Relancer</span></div>
                        <div>
                            <span class=\"infobtn\" onclick=\"this.parentNode.querySelector('.infocmdpopup').style.display='block';\">Info compta <img src=\"images/editer.gif\"></span>
                            <div class=\"infocmdtxt\">$envoi[comment]</div>
                            <div class=\"infocmdpopup\">
                                <textarea id=\"comment$envoi[id]\">$envoi[comment]</textarea>
                                <img src=\"images/icon-x.svg\" title=\"Annuler\" onclick=\"this.parentNode.style.display='none';\"> <img src=\"images/coche_verte.svg\" onclick=\"updcomptacomment($envoi[id]);\" title=\"Mettre à jour\">
                            </div>
                        </div>
                    </td>"
			. "</tr>";

			$total += $envoi['tarif'];

			mysqli_data_seek($r2,0);
			while($lf=$DB_site2->fetch_array($r2)){
				$color1=($lf['indate']>=$today && $lf['indate']<$tomorrow?" style=\"color:red;\"":"");
				$color2=($lf['outdate']>=$today && $lf['outdate']<$tomorrow?" style=\"color:red;\"":"");
				$imperative=($lf['imperatif']==1?"imperative":"maxi");
				$img="";
				$path="../configurations/".$host."/images/produits/br/".$lf['artid'].".jpg";
				if(file_exists($path)) $img="<img src=\"$path\" style=\"width:30px;\" onmouseover=\"this.style.position='absolute';this.style.width='200px';\" onmouseout=\"this.style.position='relative';this.style.width='30px';\">";
				$return.="<tr style=\"background-color:#99dd99;\">
				<td>$img</td>
				<td title=\"$lf[libelle]\" style=\"max-width: 141px;\">".$lf['qte']." ".($lf['artcode']==''?$lf['libelle']:$lf['artcode'])."</td>
				<td$color1 onmouseover=\"document.getElementById('trsptindate$lf[id]').style.visibility='visible';\" onmouseout=\"document.getElementById('trsptindate$lf[id]').style.visibility='hidden';\">
				<div id=\"trsptindate$lf[id]\" style=\"position:absolute;visibility:hidden;cursor:pointer;\">
				<img src=\"images/editer.gif\" onclick=\"calendrier_show(undefined,'',false,updadvdate);upddatetype='in';efl='$lf[id]';\" title=\"modifier\">
				<img src=\"images/supprimer.gif\" title=\"supprimer\" onclick=\"upddatetype='in';efl='$lf[id]';cal_timestamp=0;updadvdate();\">
				</div>
				Date enlèv.<br>".($lf['indate']>0 && $lf['origin_in']!=$lf['indate']?date("d/m/Y",$lf['origin_in'])."<br>=>":"").($lf['indate']>0?date("d/m/Y",$lf['indate']):"-")."
				</td>
				<td$color2 onmouseover=\"document.getElementById('trsptoutdate$lf[id]').style.visibility='visible';\" onmouseout=\"document.getElementById('trsptoutdate$lf[id]').style.visibility='hidden';\">
				<div id=\"trsptoutdate$lf[id]\" style=\"position:absolute;visibility:hidden;cursor:pointer;\">
				<img src=\"images/editer.gif\" onclick=\"calendrier_show(undefined,'',false,updadvdate);upddatetype='out';efl='$lf[id]';\">
				<img src=\"images/supprimer.gif\" title=\"supprimer\" onclick=\"upddatetype='out';efl='$lf[id]';cal_timestamp=0;updadvdate();\">
				</div>
				Date livr. $imperative<br>".($lf['outdate']>0 && $lf['origin_out']!=$lf['outdate']?date("d/m/Y",$lf['origin_out'])."<br>=>":"").($lf['outdate']>0?date("d/m/Y",$lf['outdate']):"-")."
				</td>
				</tr>";
			}
			$compat=false;
		}
	}

	$liste=$adv->listadv($facturid,"trspt");
	$advlfids=array();
	$sql=$sqltrsptsup="";
	foreach($liste as $advprod){

		if($advprod->efid==0){
			$txtenvoi="Pas d'envoi programmé";
			if($advprod->envoi>0) $txtenvoi="Envoi le ".date("d/m",$advprod->envoi)." à ".$advprod->envoi_heure."h";
			// modif TSI: #2565 Ajout d'un lien directe sur le detail du fournisseur choisit
			$return.="<tr style=\"background-color:#FFAA22;\"><td rowspan=\"".(count($advprod->lignes)+1)."\" style=\"color:black;\" nowrap>Commande enregistrée<br>"
			."<a href=\"fournisseurs_liste.php?action=editer&fournisseur=".$advprod->destinataire."\" target=\"_Blank\">".$advprod->destinataire
			."&nbsp;<img src=\"./images/visible.png\" style=\"width:15px;background-color:#FFFFFF;\" >"
			."</a><br>".date("d/m/Y H:i",$advprod->date_creation)."<br>".$advprod->utilisateur." <br><a href=\"facture.php?action=editer&facturid=$facturid&advid=".$advprod->id."&type=trspt#adv\">Modifier</a> <a href=\"javascript:deladv(".$advprod->id.",".$advprod->envoi.");\" style=\"color:red;\">Supprimer</a><br>$txtenvoi</td><td colspan=\"7\"></td></tr>";
			$dest="";
			if($advprod->fields['trsptlivr']=="client")
				$dest="Chez le client";
			elseif(substr($advprod->fields['trsptlivr'],0,3)=="sls"){
				$sls=$DB_site2->query_first("SELECT societe,ville FROM fournisseur WHERE id=".intval(substr($advprod->fields['trsptlivr'],3)));
				$dest=$sls['societe']." ".$sls['ville'];
			}
			foreach($advprod->lignes as $lignefactureid=>$qte){
				$advlfids[]=$lignefactureid;

				$lignefacture=$DB_site2->query_first("SELECT * FROM lignefacture WHERE lignefactureid=".$lignefactureid);
				$img="";
				$path="../configurations/".$host."/images/produits/br/".$lignefacture['artid'].".jpg";
				if(file_exists($path)) $img="<img src=\"$path\" style=\"width:30px;\" onmouseover=\"this.style.position='absolute';this.style.width='200px';\" onmouseout=\"this.style.position='relative';this.style.width='30px';\">";
				$return.="<tr style=\"background-color:#FFCC99;\">
				<td>$img</td>
				<td title=\"$lignefacture[libelle]\" style=\"max-width: 141px;\">".$lignefacture['qte']." ".($lignefacture['artcode']==''?$lignefacture['libelle']:$lignefacture['artcode'])."</td>
				<td>Date enlèv.<br>".($advprod->fields['trsptenl']=="fournisseur"?$advprod->fields['trsptenlfourndate']:"").($advprod->fields['trsptmode']=="parvossoins"?$advprod->fields['parvossoinsindate']:"")."</td>
				<td>Date livr.<br>".($advprod->fields['trsptotherdate'])." (".$advprod->fields['trspttype'].")</td>
				<td>".$dest."</td>
				<td></td>
				<td></td>
				</tr>";
			}
		}
	}

	if($advlfids!=array()) $sql=" AND lignefactureid NOT IN (".implode(",",$advlfids).")";
	if($stocklivr!=array()) $sqltrsptsup=" AND efid NOT IN (".implode(",",$stocklivr).")";
	
	$req=$DB_site2->query("SELECT * FROM lignefacture lf WHERE factureid=$facturid AND (SELECT COUNT(ligne_facture_id) FROM envoi_fournisseur_ligne WHERE ligne_facture_id=lf.lignefactureid AND type='trspt'".$sqltrsptsup.")=0".$sql);
	$lignesfacid=array();
	if($DB_site2->num_rows($req)>0){
		while($lignefact=$DB_site2->fetch_array($req)){
			$lignesfacid[]=$lignefact['lignefactureid'];
		}
		$lignesfacid=implode(",",$lignesfacid);
		$sql = "SELECT mail, telephone";
		$sql .= " FROM facture";
		$sql .= " WHERE facturid = ".$facturid;
		$facture = $DB_site2->query_first($sql);
		if($facture['mail']!="" && $facture['telephone']!=""){
			$liencommande="<a href=\"facture.php?facturid=$facturid&action=editer&type=trspt&advid=0#adv\" style=\"display:block;color:white;margin-left:3px;\">Commander</a>";
		} else {
			$liencommande="<a href=\"javascript:void(0);\" onclick=\"alert('les coordonnées de livraison sont incomplètes : veuillez remplir le mail et le telephone de livraison');\" style=\"display:block;color:white;margin-left:3px;\">Commander</a>";
		}
		$return.="<tr style=\"background-color:#FF2222;\">
                    <td rowspan=\"".($DB_site2->num_rows($req)+1)."\" style=\"color:black;\">
                        Articles non commandés
                        <br>".$liencommande."
                        <br><a href=\"javascript:nocmd('trspt','".$lignesfacid."')\" style=\"display:block;color:white;margin-left:3px;\">Pas de Cde</a>
                    </td>
                    <td colspan=\"7\"></td>
                </tr>";
	}
	foreach($req as $nocmd){
		$cmddate="";
		$cmdf="transporteur";
		$rc=$DB_site2->query("SELECT * FROM calendrier WHERE type='trsptcmd' AND objid=".$nocmd['lignefactureid']);
		$cal=$DB_site2->fetch_array($rc);
		if($cal!=false){
			if($cal['date']>0) $cmddate=$cal['date']!=0?date("d/m/Y",$cal['date']):"";
			$cmdf=$cal['txt'];
		}
		$img="";
		$path="../configurations/".$host."/images/produits/br/".$nocmd['artid'].".jpg";
		if(file_exists($path)) $img="<img src=\"$path\" style=\"width:30px;\" onmouseover=\"this.style.position='absolute';this.style.width='200px';\" onmouseout=\"this.style.position='relative';this.style.width='30px';\">";
		$return.="<tr style=\"background-color:#FF9999;\">
		<td>$img</td>
		<td title=\"$nocmd[libelle]\" style=\"max-width: 141px;\">".$nocmd['qte']." ".($nocmd['artcode']==''?$nocmd['libelle']:$nocmd['artcode'])."</td>
		<td colspan=\"5\" nowrap>A commander le <input type=\"text\" name=\"cmddate2$nocmd[lignefactureid]\" id=\"cmddate2$nocmd[lignefactureid]\" value=\"$cmddate\" onclick=\"cmddate(this.id,'trsptcmd',$nocmd[lignefactureid],'date')\" size=\"10\"> à <input type=\"text\" id=\"dcmdf2$nocmd[lignefactureid]\" size=\"35\" value=\"$cmdf\" onclick=\"if(this.value=='transporteur')this.value='';document.getElementById('okdcmdf2$nocmd[lignefactureid]').style.display='inline'\"><input type=\"button\" id=\"okdcmdf2$nocmd[lignefactureid]\" value=\"OK\" style=\"display:none;\" onclick=\"cmdtxt('trsptcmd',$nocmd[lignefactureid],document.getElementById('dcmdf2$nocmd[lignefactureid]').value);\"></td>
		</tr>";
		$compat=false;
	}

    //compatibilité anciennes commandes
	if($compat){
		$ef=$DB_site2->fetch_array($DB_site2->query("SELECT ROUND(SUM(tarif),2) FROM envoi_fournisseur WHERE type='trspt' AND facturid=$facturid"));
		$total+=$ef[0];
		$req=$DB_site2->query("SELECT * FROM lignefacture lf INNER JOIN envoi_fournisseur_ligne efl ON (lf.lignefactureid=efl.ligne_facture_id) WHERE lf.factureid=$facturid AND efl.type='trspt' AND efl.efid=0");
		if($DB_site2->num_rows($req)>0) $return.="<tr style=\"background-color:#AAAAAA;\"><td rowspan=\"".($DB_site2->num_rows($req)+1)."\">Articles commandés</td><td colspan=\"4\"></td><td rowspan=\"".($DB_site2->num_rows($req)+1)."\">$ef[0]</td></tr>";
		while($oldcmd=$DB_site2->fetch_array($req)){
			$return.="<tr><td title=\"$oldcmd[libelle]\">".$oldcmd['artcode']."</td><td>$oldcmd[expediteur] ".date("d/m/Y",$oldcmd['date'])."</td><td></td><td></td></tr>";
		}
	}

	$return.="</table>";
	return $return;
}

// --------------------------------------------------------------------------------- //
// --------------------------------------------------------------------------------- //
// --------------------------------------------------------------------------------- //
// fonction qui affiche le tableau pour INSTALLATEUR                                 //
// --------------------------------------------------------------------------------- //
// --------------------------------------------------------------------------------- //
// --------------------------------------------------------------------------------- //

function sls_recap($facturid){
	global $DB_site2,$host;
	global $adv;
	global $devises;
	
	$f=new facture($DB_site2,$facturid);
	$trsptclient=$f->transportsClient();
	
    $infoadv=$DB_site2->query_first("SELECT suivi FROM suivi_commande WHERE type='advsls' AND facturid=".$facturid." ORDER BY date DESC LIMIT 1");
	
      //installateur
	$return="<br><table id=\"sls_recap\" width=\"100%\">
	<tr>
        <td colspan=\"8\" style=\"color:white;background-color:#AAAAAA;font-weight:bold;\"><img src=\"images/carton.png\" width=\"25\" align=\"absmiddle\"> Installateur</td>
	</tr>
	 <tr style=\"background-color:#AAA;\">
            <td style=\"background-color:#AAA;\">
            <span class=\"infobtn\" onclick=\"this.parentNode.querySelector('.infocmdpopup').style.display='block';\">Info ADV <img src=\"images/editer.gif\"></span>
            <span class=\"infocmdtxt\"> $infoadv[suivi] </span>
            <div class=\"infocmdpopup\">
                <textarea id=\"slsadvcomment\">$infoadv[suivi]</textarea>
                <img src=\"images/icon-x.svg\" title=\"Annuler\" onclick=\"this.parentNode.style.display='none';\"> <img src=\"images/coche_verte.svg\" onclick=\"updadvcomment('sls')\" title=\"Mettre à jour\">
            </div>
        </td>";

	//// tâche 6875 : permet de changer toutes les dates en même temps (date enlèvement ou date livraison)
	$r3=$DB_site2->query("SELECT * FROM envoi_fournisseur_ligne efl INNER JOIN lignefacture lf ON (lf.lignefactureid=efl.ligne_facture_id) WHERE efl.type='sls' AND facture!='STOCK' AND factureid = ".$facturid);
	$fourn_liste=array();
	while($fourn=$DB_site2->fetch_array($r3)){
		$fourn_liste[]=$fourn['id'];
		$date_enlev_in=$fourn['indate'];
		$date_enlev_origin=$fourn['origin_in'];
		$date_livr_out=$fourn['outdate'];
		$date_livr_origin=$fourn['origin_out'];
		$date_enlev[]=$date_enlev_in;
		$date_livr[]=$date_livr_out;
	}

	$arr_unique=($date_enlev!='' ? array_unique($date_enlev) : ''); 
	$arr_unique2=($date_livr!='' ? array_unique($date_livr) : '');

	$fourn_liste=implode(',',$fourn_liste);

	$liste=$adv->listadv($facturid,"sls");
	$advlfids=array();
	$sql="";
	foreach($liste as $advprod){
		if($advprod->efid==0){
			$tdorangesls=true;
		}
	}

	$return.="
	<td></td>
	<td></td>";
	if($DB_site2->num_rows($r3) > 0) {
		$return.="
		<td style=\"background-color:#AAA;text-align:left\" onmouseover=\"document.getElementById('updateenlev_sls_all').style.visibility='visible';\" onmouseout=\"document.getElementById('updateenlev_sls_all').style.visibility='hidden';\">
		<div id=\"updateenlev_sls_all\" style=\"position:absolute;visibility:hidden;cursor:pointer;background-color:white;\" onclick=\"calendrier_show(undefined,'',false,updadvdateall);upddatetype='in';efl='".$fourn_liste."';\">
		<img src=\"images/editer.gif\" title=\"modifier\">
		(tout modifier)
		</div>
		Date réception<br>".(count($arr_unique)<2 && $date_enlev_in>0 && $date_enlev_origin!=$date_enlev_in?date("d/m/Y",$date_enlev_origin)."<br>=>":"").(count($arr_unique)<2 && $date_enlev_in>0?date("d/m/Y",$date_enlev_in):"-")."
		</td>
		<td onmouseover=\"document.getElementById('updatelivr_sls_all').style.visibility='visible';\" onmouseout=\"document.getElementById('updatelivr_sls_all').style.visibility='hidden';\">
		<div id=\"updatelivr_sls_all\" style=\"position:absolute;visibility:hidden;cursor:pointer;background-color:white;\"  onclick=\"calendrier_show(undefined,'',false,updadvdateall);upddatetype='out';efl='".$fourn_liste."';\">
		<img src=\"images/editer.gif\" title=\"modifier\">
		(tout modifier)
		</div>
		Date livr. maxi<br>".(count($arr_unique2)<2 && $date_livr_out>0 && $date_livr_origin!=$date_livr_out?date("d/m/Y",$date_livr_origin)."<br>=>":"").(count($arr_unique2)<2 && $date_livr_out>0?date("d/m/Y",$date_livr_out):"-")."
		</td>
		<td></td>
		<td></td>";
	}
	elseif($tdorangesls==false) {
		//// tache 6929 - pour ajouter/modifier date pour tous les produits
		if($advlfids!=array()) $sql=" AND lignefactureid NOT IN (".implode(",",$advlfids).")";
		$req=$DB_site2->query("SELECT * FROM lignefacture lf WHERE factureid=$facturid AND (SELECT COUNT(ligne_facture_id) FROM envoi_fournisseur_ligne WHERE ligne_facture_id=lf.lignefactureid AND type='sls')=0".$sql);
		$lignfac=array();
		while($nocmd=$DB_site2->fetch_array($req)){
			$lignfac[]=$nocmd['lignefactureid'];
		}
		$lignfac=implode(',',$lignfac);
		
		$return.="
		<td style=\"background-color:#AAA;text-align:left\" colspan=\"3\">
		A commander le <input type=\"text\" name=\"datesls\" id=\"datesls\" value=\"\" onclick=\"cmddateall(this.id,'slscmd','$lignfac','date')\" size=\"10\"> à <input type=\"text\" id=\"destsls\" value=\"\" onclick=\"if(this.value=='installateur')this.value='';document.getElementById('oksls').style.display='inline';\" size=\"35\"><input type=\"button\" id=\"oksls\" style=\"display:none;\" value=\"OK\" onclick=\"cmdtxtall('slscmd','$lignfac',document.getElementById('destsls').value);\">
		<span id=\"acmd_sls_all\" style=\"background-color:white;width:max-content;\">
			(tout modifier)
			</span>
		</td>";
		//// fin tache 6929
	}
	else {
		$return.="<td></td>";
	}
	$return.="
	<td></td>
	</tr>";
    //// fin tâche 6875

	$compat=true;
	$livrcmd="";
	$sql = "SELECT *";
	$sql .= " FROM envoi_fournisseur";
	$sql .= " WHERE type = 'sls'";
	$sql .= " AND facturid = " . $facturid;
	$r = $DB_site2->query($sql);

	while($envoi = $DB_site2->fetch_array($r))
	{
		//$nolivr = false;
		
		$tracking="";
		$email_livraison="";

		$sql = "SELECT *";
		$sql .= " FROM envoi_fournisseur_ligne efl";
		$sql .= " INNER JOIN lignefacture lf ON (lf.lignefactureid = efl.ligne_facture_id)";
		$sql .= " WHERE efid = " . $envoi['id'];
		$sql .= " AND efl.type = 'sls'";
		$r2 = $DB_site2->query($sql);

		if($DB_site2->num_rows($r2) > 0)
		{
			$lf1     = $DB_site2->fetch_array($r2);
			$rowspan = $DB_site2->num_rows($r2)+1;
			$livrcmd = "";
			
			if(!empty($envoi['destinataire']))
			{
                if($envoi['livraison'] != -1){
                    if($lf1['indate']!=0){
                        
                        $deladv = new adv($DB_site2);
                        $result = $deladv->load_from_efid($envoi['id']);
                            
                        if($envoi['enlevement']==0){
                            $nolivr=true;
                            $livrcmd="<input type=\"text\" id=\"datelivrcmd$envoi[id]\" size=\"10\" value=\"Date réception\" onclick=\"efid=$envoi[id];this.value='';calendrier_show(this.id,this.value,false,updenlevcmd);\">";
                            $cancel = "<br><a href=\"facture.php?facturid=" . $facturid . "&action=editer&type=sls&advid=" . $deladv->id . "&annule=1#adv\" style=\"color:red;\">Annuler la commande</a><br><a href=\"facture.php?facturid=" . $facturid . "&action=editer&type=sls&advid=" . $deladv->id . "&modif=1#adv\">Modifier la commande</a>";
                        }else{
                            $livrcmd="Enlevée le ".date("d/m/Y",$envoi['enlevement']);
                            if($envoi['livraison']<1){
                            	$livrcmd .= "<img src=\"images/supprimer.gif\" title=\"supprimer\" onclick=\"upddatetype='enlevement';ef='$envoi[id]';cal_timestamp=0;updadvdate2();\">";
                            }
                            $livrcmd .="<br>";
                                    
                            if($envoi['livraison'] == 0){
                                $livrcmd .= "<input type=\"text\" id=\"datelivrcmd" . $envoi['id'] . "\" size=\"10\" value=\"Date livraison\" onclick=\"efid=" . $envoi['id'] . ";this.value='';calendrier_show(this.id,this.value,false,updlivrcmd);\"><br><a href=\"javascript:efid=" . $envoi['id'] . ";cal_timestamp=-1;updlivrcmd();\" style=\"color:black;\">Livraison annulée</a>";
                            }else{
                                $livrcmd .= "Livrée le ".date("d/m/Y",$envoi['livraison']);
                            }
                        }
                    }
                }else{
                    $livrcmd .= "<span style=\"color:black;white-space:nowrap;font-weight:bold;font-size:11px;\">Livraison annulée</span>";
                }
			}

			if(empty($envoi['destinataire']))
			{
				$envoi['destinataire'] = "Pas de commande";
				$cancel = "<a href=\"javascript:deladvcmd(" . $envoi['id'] . ")\" style=\"color:red;\">Annuler la commande</a>";
			}
			else
			{
				if($envoi['enlev'] != "0")
				{
					$sql = "SELECT societe,ville";
					$sql .= " FROM fournisseur";
					$sql .= " WHERE id = " . $envoi['enlev'];
					$fdepart = $DB_site2->query_first($sql);

					if(!is_null($fdepart))
						$depart = $fdepart['societe'] . " " . $fdepart['ville'];
					else
						$depart = "?";
				}
				
				switch ($envoi['destinataire']) {
                                    case "Girard Agediss":
                                    $tracking="
                                            <div id=\"tracklink".$envoi['id']."\" ".($envoi['trackid'] == "" ? "style=\"display:none;\"" : "").">
                                                    <a href=\"http://webtracking.girard-agediss.com/tracking-acylion-".$envoi['trackid'].".html\" target=\"_blank\">".$envoi['trackid']."</a> 
                                                    <img src=\"images/editer.gif\" onclick=\"edit_tracking(".$envoi['id'].")\">
                                            </div>
                                            <div id=\"trackedit".$envoi['id']."\" ".($envoi['trackid'] != "" ? "style=\"display:none;\"" : "").">
                                                    <input type=\"text\" id=\"trackid".$envoi['id']."\" value=\"".($envoi['trackid'] != "" ? $envoi['trackid'] : "Tracking")."\" size=\"10\" onclick=\"if(this.value=='Tracking') this.value=''\">
                                                    <a href=\"javascript:void(0);\" onclick=\"document.location='facture.php?action=editer&facturid=$facturid&trackid='+document.getElementById('trackid".$envoi['id']."').value+'&efid=".$envoi['id']."';\">OK</a>
                                            </div>";
                                    break;

                                    case "AGEDISS": //rajout suite a tache 5113
                                    $tracking="
                                            <div id=\"tracklink".$envoi['id']."\" ".($envoi['trackid'] == "" ? "style=\"display:none;\"" : "").">
                                                    <a href=\"http://webtracking.girard-agediss.com/tracking-acylion-".$envoi['trackid'].".html\" target=\"_blank\">".$envoi['trackid']."</a> 
                                                    <img src=\"images/editer.gif\" onclick=\"edit_tracking(".$envoi['id'].")\">
                                            </div>
                                            <div id=\"trackedit".$envoi['id']."\" ".($envoi['trackid'] != "" ? "style=\"display:none;\"" : "").">
                                                    <input type=\"text\" id=\"trackid".$envoi['id']."\" value=\"".($envoi['trackid'] != "" ? $envoi['trackid'] : "Tracking")."\" size=\"10\" onclick=\"if(this.value=='Tracking') this.value=''\">
                                                    <a href=\"javascript:void(0);\" onclick=\"document.location='facture.php?action=editer&facturid=$facturid&trackid='+document.getElementById('trackid".$envoi['id']."').value+'&efid=".$envoi['id']."';\">OK</a>
                                            </div>";
                                    break;

                                    case "GLOBE EXPRESS 2 DIS":
                                    $tracking="
                                            <div id=\"tracklink".$envoi['id']."\" ".($envoi['trackid'] == "" ? "style=\"display:none;\"" : "").">
                                                    <a href=\"https://dispatchweb.fr/Tracky/Home/".$envoi['trackid']."\" target=\"_blank\">".$envoi['trackid']."</a> 
                                                    <img src=\"images/editer.gif\" onclick=\"edit_tracking(".$envoi['id'].")\">
                                            </div>
                                            <div id=\"trackedit".$envoi['id']."\" ".($envoi['trackid'] != "" ? "style=\"display:none;\"" : "").">
                                                    <input type=\"text\" id=\"trackid".$envoi['id']."\" value=\"".($envoi['trackid'] != "" ? $envoi['trackid'] : "Tracking")."\" size=\"10\" onclick=\"if(this.value=='Tracking') this.value=''\">
                                                    <a href=\"javascript:void(0);\" onclick=\"document.location='facture.php?action=editer&facturid=$facturid&trackid='+document.getElementById('trackid".$envoi['id']."').value+'&efid=".$envoi['id']."';\">OK</a>
                                            </div>";
                                    break;
				}
				if(isset($trsptclient[$envoi['id']])){
                                    $email_livraison="<br><br><input type=\"button\" onclick=\"mail_trspt('".$trsptclient[$envoi['id']]."',".$envoi['id'].")\" value=\"Mail expé.\">";
                                }
			}
	
			/// relance tâche #7039
			$body_relance="";
			$modelivraison="";
			if(($envoi['enlevement']==0 || $envoi['livraison']==0) && $envoi['destinataire']!=="Pas de commande"){
				$ligne_article_envoi=$DB_site2->query("SELECT a.artcode_eurocave,lf.libelle AS lib,m.libelle AS marque, efl.qte, efl.paht, efl.ecopart, efl.outdate, efl.indate FROM article a INNER JOIN article_marque am ON(am.artid=a.artid) INNER JOIN marque m ON(m.marqueid=am.marqueid) INNER JOIN lignefacture lf ON lf.artid = a.artid INNER JOIN envoi_fournisseur_ligne efl ON efl.ligne_facture_id = lf.lignefactureid WHERE efl.efid=".$envoi['id']." AND efl.type='sls' AND facture!='STOCK'");

				$adv_facture=$DB_site2->query_first("SELECT * FROM facture f, pays p WHERE f.paysid=p.paysid AND f.facturid=".$facturid);
				$text_livr=($adv_facture['raisonsociale']!==''?$adv_facture[raisonsociale].'<br>':'')."$adv_facture[nom] $adv_facture[prenom]<br>$adv_facture[adresse]<br>$adv_facture[codepostal] $adv_facture[ville]<br>$adv_facture[libelle]<br>$adv_facture[telephone]<br>";

				switch($f->modelivraison){
					case 1:
					$modelivraison = "- Confort";
					break;
					case 4:
					$modelivraison = "- Premium avec montage";
					break;
				}
				$sujet_relance="Suivi de livraison #CDE".$facturid."#";

				$ids_livr=$DB_site2->query("SELECT livr,id,type FROM envoi_fournisseur WHERE facturid='".$facturid."' ORDER BY type ASC");
				foreach($ids_livr as $id_livr){
					if($id_livr['type']=='fourn' && $id_livr['livr']!=='0' && $id_livr['livr']!=='-1'){
						$envoi_id_livr=$id_livr['livr'];
					}
					if($id_livr['type']=='stock' && $id_livr['livr']!=='0' && $id_livr['livr']!=='-1'){
						$envoi_id_livr=$id_livr['livr'];
					}
					if($id_livr['type']=='trspt' && $id_livr['livr']!=='0' && $id_livr['livr']!=='-1'){
						$envoi_id_livr=$id_livr['livr'];
					}
				}
				if(isset($envoi_id_livr)){
					$lieuarriv=$DB_site2->query_first("SELECT societe,ville,type FROM fournisseur WHERE id=".$envoi_id_livr."");
					$agence=$lieuarriv['societe']." ".$lieuarriv['ville'];
				}
				$body_relance="Bonjour,<br><br>Pouvez-vous nous faire un point sur cette commande :<br><br><b>Agence</b><br>".$agence."<br><br>".($modelivraison!==''?'<b>Type de livraison :</b><br><b>'.$modelivraison.'</b><br>':'')."<br><b>Détail de la commande</b><table border='2' cellpadding='5' cellspacing='1' width='100%'><thead><tr><td style='font-weight:bold'>Qte:</td><td style='font-weight:bold'>Références:</td><td align='left' style='font-weight:bold'>&nbsp;Informations:</td><td>Date réception produits à votre agence</td><td>Date de livraison</td><td>Nouvelle date de livr. prévue</td></tr></thead><tbody>";
				foreach($ligne_article_envoi as $ligne){
					$text_date_in=date("d/m/Y",$ligne['indate']);
					$text_date_out=date("d/m/Y",$ligne['outdate']);
					switch($adv->fields['maxslslivr']){
						case "precis":
						$text_date_in=$template->translate("/admin/adv_install/livrer_exclusivement","F").date("d/m/Y",$ligne['indate']);
						$text_date_out=$template->translate("/admin/adv_install/livrer_exclusivement","F").date("d/m/Y",$ligne['outdate']);
						break;
						case "max":
						$text_date_in="<b>".$template->translate("/admin/adv_install/date_installation_maxi","F")."</b>".date("d/m/Y",$ligne['indate']);
						$text_date_out="<b>".$template->translate("/admin/adv_install/date_installation_maxi","F")."</b>".date("d/m/Y",$ligne['outdate']);
						break;
					}
					$body_relance.="<tr><td>".$ligne['qte']."</td><td>".addslashes($ligne['marque'])."<br>".addslashes($ligne['artcode_eurocave'])."</td><td>".preg_replace('/\n|\r/',' ',preg_replace('/"/',"'",$ligne['lib']))."</td><td>".($envoi['enlevement']!=='0'?date("d/m/Y",$envoi['enlevement']):$text_date_in)."</td><td>".$text_date_out."</td><td></td></tr>"; 
				}
				$body_relance.="</tbody></table><br><b>Adresse de livraison</b><br>".$text_livr."<br><br>Pouvez-vous nous confirmer que cette commande a bien été livrée/sera livrée a la date convenue ? <b>En cas de report, nous indiquer une nouvelle date ci-dessus.</b><br><br>Nous vous remercions par avance de votre réponse, <b>sous 24h maximum</b>, par retour de ce mail.<br><br>Référence <b>#CDE".$facturid."#</b> : à rappeler dans toute correspondance dans l’objet du mail.<br>";
			}
			/// fin relance tâche #7039

			// modif TSI : #2565 Ajout d'un lien directe sur le detail du fournisseur choisit
			$return .= ""
			. "<tr style=\"background-color:#00dd00;\">"
				. "<td rowspan=\"" . $rowspan . "\">"
					. "<a href=\"fournisseurs_liste.php?action=editer&fournisseur=" . $envoi['destinataire'] . "\" target=\"_Blank\">" . $envoi['destinataire'] . "&nbsp;<img src=\"./images/visible.png\" style=\"width:15px;background-color:#FFFFFF;\" ></a>"
					. $tracking
					. $email_livraison
					. "<br>".date("d/m/Y H:i",$envoi['date'])
					. "<br>"
					. $lf1['expediteur']
					. "<br>"
					. $livrcmd . " " . $cancel 
				. "</td>"
				. "<td colspan=\"2\"></td>"
				. "<td>" . $depart . "</td>"
				. "<td>=&gt; Client</td>"
				. "<td rowspan=\"" . $rowspan . "\">Transport<br>" . $envoi['trspt'] . "</td>"
				. "<td rowspan=\"" . $rowspan . "\">Tarif<br>" . $envoi['tarif'] . "&nbsp;" . $devises->symbole($envoi['devise']) . "</td>"
				. "<td rowspan=\"" . $rowspan . "\">
				<div style=\"margin-bottom:5px;\">".($envoi['date_relance']!=='0'?'Relance faite le<br>'.date("d/m/Y",$envoi['date_relance']).'<br>':'')."<span class=\"btn_relance\" onclick=\"mail_auto('adv@mon-espace-cuisson.com','','adv@mon-espace-cuisson.com','".$sujet_relance."','".addslashes($body_relance)."',undefined,undefined,false,null,".$envoi['id'].");mail_suivi('suivi_fournisseur',".$facturid.");add_exp_list('contact','contact@mon-espace-cuisson.com','base');\">Relancer</span></div>
                            <span class=\"infobtn\" onclick=\"this.parentNode.querySelector('.infocmdpopup').style.display='block';\">Info compta <img src=\"images/editer.gif\"></span>
                            <div class=\"infocmdtxt\">$envoi[comment]</div>
                            <div class=\"infocmdpopup\">
                                <textarea id=\"comment$envoi[id]\">$envoi[comment]</textarea>
                                <img src=\"images/icon-x.svg\" title=\"Annuler\" onclick=\"this.parentNode.style.display='none';\"> <img src=\"images/coche_verte.svg\" onclick=\"updcomptacomment($envoi[id]);\" title=\"Mettre à jour\">
                            </div>
                        </div>
                    </td>"
			. "</tr>";

			$total += $envoi['tarif'];

			mysqli_data_seek($r2,0);
			while($lf = $DB_site2->fetch_array($r2)){
				$color1=($lf['indate']>=$today && $lf['indate']<$tomorrow?" style=\"color:red;\"":"");
				$color2=($lf['outdate']>=$today && $lf['outdate']<$tomorrow?" style=\"color:red;\"":"");
				$imperative=($lf['imperatif']==1?"imperative":"maxi");
				$img="";
				$path="../configurations/".$host."/images/produits/br/".$lf['artid'].".jpg";
				if(file_exists($path)) $img="<img src=\"$path\" style=\"width:30px;\" onmouseover=\"this.style.position='absolute';this.style.width='200px';\" onmouseout=\"this.style.position='relative';this.style.width='30px';\">";
				$return.="<tr style=\"background-color:#99dd99;\">
				<td>$img</td>
				<td title=\"$lf[libelle]\" style=\"max-width: 141px;\">".$lf['qte']." ".($lf['artcode']==''?$lf['libelle']:$lf['artcode'])."</td>
				<td$color1 onmouseover=\"document.getElementById('slsindate$lf[id]').style.visibility='visible';\" onmouseout=\"document.getElementById('slsindate$lf[id]').style.visibility='hidden';\"><div id=\"slsindate$lf[id]\" style=\"position:absolute;visibility:hidden;cursor:pointer;\"><img src=\"images/editer.gif\" onclick=\"calendrier_show(undefined,'',false,updadvdate);upddatetype='in';efl='$lf[id]';\" title=\"modifier\"><img src=\"images/supprimer.gif\" title=\"supprimer\" onclick=\"upddatetype='in';efl='$lf[id]';cal_timestamp=0;updadvdate();\"></div>Date réception<br>".($lf['indate']>0 && $lf['origin_in']!=$lf['indate']?date("d/m/Y",$lf['origin_in'])."<br>=>":"").($lf['indate']>0?date("d/m/Y",$lf['indate']):"-")."</td>
				<td$color2 onmouseover=\"document.getElementById('slsoutdate$lf[id]').style.visibility='visible';\" onmouseout=\"document.getElementById('slsoutdate$lf[id]').style.visibility='hidden';\"><div id=\"slsoutdate$lf[id]\" style=\"position:absolute;visibility:hidden;cursor:pointer;\"><img src=\"images/editer.gif\" onclick=\"calendrier_show(undefined,'',false,updadvdate);upddatetype='out';efl='$lf[id]';\"><img src=\"images/supprimer.gif\" title=\"supprimer\" onclick=\"upddatetype='out';efl='$lf[id]';cal_timestamp=0;updadvdate();\"></div>Date livr. $imperative<br>".($lf['outdate']>0 && $lf['origin_out']!=$lf['outdate']?date("d/m/Y",$lf['origin_out'])."<br>=>":"").($lf['outdate']>0?date("d/m/Y",$lf['outdate']):"-")."</td>
				</tr>";
			}
			$compat=false;
		}
	}

	$liste=$adv->listadv($facturid,"sls");
	$advlfids=array();
	$sql="";
	foreach($liste as $advprod){

		if($advprod->efid==0){
			$txtenvoi="Pas d'envoi programmé";
			if($advprod->envoi>0) $txtenvoi="Envoi le ".date("d/m",$advprod->envoi)." à ".$advprod->envoi_heure."h";
			// TSI Modif :
			$affAdvprod="<a href=\"fournisseurs_liste.php?action=editer&fournisseur=".$advprod->destinataire."\" target=\"_Blank\">".$advprod->destinataire."&nbsp;<img src=\"./images/visible.png\" style=\"width:15px;background-color:#FFFFFF;\" ></a>";
			$return.="<tr style=\"background-color:#FFAA22;\"><td rowspan=\"".(count($advprod->lignes)+1)."\" style=\"color:black;\" nowrap>Commande enregistrée<br>".($advprod->destinataire=="0"?"Pas de commande":$affAdvprod)
			."<br>".date("d/m/Y H:i",$advprod->date_creation)."<br>".$advprod->utilisateur." <br><a href=\"facture.php?action=editer&facturid=$facturid&advid=".$advprod->id."&type=sls#adv\">Modifier</a> <a href=\"javascript:deladv(".$advprod->id.",".$advprod->envoi.");\" style=\"color:red;\">Supprimer</a><br>$txtenvoi</td><td colspan=\"7\"></td></tr>";
			foreach($advprod->lignes as $lignefactureid=>$qte){
				$advlfids[]=$lignefactureid;
				$lignefacture=$DB_site2->query_first("SELECT * FROM lignefacture WHERE lignefactureid=".$lignefactureid);
				$img="";
				$path="../configurations/".$host."/images/produits/br/".$lignefacture['artid'].".jpg";
				if(file_exists($path)) $img="<img src=\"$path\" style=\"width:30px;\" onmouseover=\"this.style.position='absolute';this.style.width='200px';\" onmouseout=\"this.style.position='relative';this.style.width='30px';\">";
				$return.="<tr style=\"background-color:#FFCC99;\">
				<td>$img</td>
				<td title=\"$lignefacture[libelle]\" style=\"max-width: 141px;\">".$lignefacture['qte']." ".($lignefacture['artcode']==''?$lignefacture['libelle']:$lignefacture['artcode'])."</td>
				<td>Date réception<br>".$advprod->fields['slslivrdate']."</td>
				<td>Date livr.<br>".($advprod->fields['slsmaxdate'])."</td>
				<td></td>
				<td></td>
				<td></td>
				</tr>";
			}
		}
	}
	if($advlfids!=array()) $sql=" AND lignefactureid NOT IN (".implode(",",$advlfids).")";

	$req=$DB_site2->query("SELECT * FROM lignefacture lf WHERE factureid=$facturid AND (SELECT COUNT(ligne_facture_id) FROM envoi_fournisseur_ligne WHERE ligne_facture_id=lf.lignefactureid AND type='sls')=0".$sql);
	$lignesfacid=array();
	if($DB_site2->num_rows($req)>0){
		while($lignefact=$DB_site2->fetch_array($req)){
			$lignesfacid[]=$lignefact['lignefactureid'];
		}
		$lignesfacid=implode(",",$lignesfacid);
		$return.="<tr style=\"background-color:#FF2222;\">
            <td rowspan=\"".($DB_site2->num_rows($req)+1)."\" style=\"color:black;\">
                Articles non commandés
                <br><a href=\"facture.php?facturid=$facturid&action=editer&type=sls&advid=0#adv\" style=\"display:block;color:white;margin-left:3px;\">Commander</a>
                <br><a href=\"javascript:nocmd('sls','".$lignesfacid."')\" style=\"display:block;color:white;margin-left:3px;\">Pas de Cde</a>
            </td>
            <td colspan=\"7\"></td>
        </tr>";
    }
	foreach($req as $nocmd){
		$cmddate="";
		$cmdf="installateur";
		$rc=$DB_site2->query("SELECT * FROM calendrier WHERE type='slscmd' AND objid=".$nocmd['lignefactureid']);
		$cal=$DB_site2->fetch_array($rc);
		if($cal!=false){
			if($cal['date']>0) $cmddate=date("d/m/Y",$cal['date']);
			$cmdf=$cal['txt'];
		}
		$img="";
		$path="../configurations/".$host."/images/produits/br/".$nocmd['artid'].".jpg";
		if(file_exists($path)) $img="<img src=\"$path\" style=\"width:30px;\" onmouseover=\"this.style.position='absolute';this.style.width='200px';\" onmouseout=\"this.style.position='relative';this.style.width='30px';\">";
		$return.="<tr style=\"background-color:#FF9999;\">
		<td>$img</td>
		<td title=\"$nocmd[libelle]\" style=\"max-width: 141px;\">".$nocmd['qte']." ".($nocmd['artcode']==''?$nocmd['libelle']:$nocmd['artcode'])."</td>
		<td colspan=\"5\" nowrap>A commander le <input type=\"text\" name=\"cmddate3$nocmd[lignefactureid]\" id=\"cmddate3$nocmd[lignefactureid]\" value=\"$cmddate\" onclick=\"cmddate(this.id,'slscmd',$nocmd[lignefactureid],'date')\" size=\"10\"> à <input type=\"text\" id=\"dcmdf3$nocmd[lignefactureid]\" value=\"$cmdf\" onclick=\"if(this.value=='installateur')this.value='';document.getElementById('okdcmdf3$nocmd[lignefactureid]').style.display='inline';\" size=\"35\"><input type=\"button\" id=\"okdcmdf3$nocmd[lignefactureid]\" style=\"display:none;\" value=\"OK\" onclick=\"cmdtxt('slscmd',$nocmd[lignefactureid],document.getElementById('dcmdf3$nocmd[lignefactureid]').value);\"></td>
		</tr>";
		$compat=false;
	}

		//compatibilité anciennes commandes
	if($compat){
		$ef=$DB_site2->fetch_array($DB_site2->query("SELECT ROUND(SUM(tarif),2) FROM envoi_fournisseur WHERE type='sls' AND facturid=$facturid"));
		$total+=$ef[0];
		$req=$DB_site2->query("SELECT * FROM lignefacture lf INNER JOIN envoi_fournisseur_ligne efl ON (lf.lignefactureid=efl.ligne_facture_id) WHERE lf.factureid=$facturid AND efl.type='sls' AND efl.efid=0");
		if($DB_site2->num_rows($req)>0) $return.="<tr style=\"background-color:#AAAAAA;\"><td rowspan=\"".($DB_site2->num_rows($req)+1)."\">Articles commandés</td><td colspan=\"4\"></td><td rowspan=\"".($DB_site2->num_rows($req)+1)."\">$ef[0]</td></tr>";
		while($oldcmd=$DB_site2->fetch_array($req)){
			$return.="<tr><td title=\"$oldcmd[libelle]\">".$oldcmd['artcode']."</td><td>$oldcmd[expediteur] ".date("d/m/Y",$oldcmd['date'])."</td><td></td><td></td></tr>";
		}
	}

	$return.="</table>";
	return $return;
}

// --------------------------------------------------------------------------------- //
// --------------------------------------------------------------------------------- //
// --------------------------------------------------------------------------------- //
// fonction qui affiche le tableau pour MISE EN SERVICE                              //
// --------------------------------------------------------------------------------- //
// --------------------------------------------------------------------------------- //
// --------------------------------------------------------------------------------- //

function miseenservice_recap($facturid){
     global $DB_site2;
     global $adv;
     global $artref;
    
     //Mise en service
     $return = ""
     . "<br>"
     . "<table class=\"cmd_adv\" id=\"service_recap\" width=\"100%\">"
          . "<tr>"
               . "<td colspan=\"8\" style=\"color:white;background-color:#AAAAAA;font-weight:bold;\"><img src=\"images/icon/sav_picto.png\" width=\"20\" align=\"absmiddle\"> Mise en service</td>"
          . "</tr>";

     $compat  = true;
     $livrcmd = "";
    
     //Récupération des commandes de mise en service
     $sql = "SELECT *";
     $sql .= " FROM envoi_fournisseur";
     $sql .= " WHERE type = 'service'";
     $sql .= " AND facturid = ".$facturid;
     $r = $DB_site2->query($sql);
    
     //TODO : coder cette partie concernant l'affichage des mises en service déjà passées
     while($envoi = $DB_site2->fetch_array($r)){
      
          $sql = "SELECT *";
          $sql .= " FROM envoi_fournisseur_ligne efl";
          $sql .= " INNER JOIN lignefacture lf ON (lf.lignefactureid = efl.ligne_facture_id)";
          $sql .= " WHERE efid = ".$envoi['id'];
          $sql .= " AND efl.type = 'service'";
          $r2 = $DB_site2->query($sql);
          
          $nolivr = false;
          
          if($DB_site2->num_rows($r2) > 0){
               
               $lf1     = $DB_site2->fetch_array($r2);
               $rowspan = $DB_site2->num_rows($r2)+1;
               $livrcmd = "";

          if($envoi['destinataire'] == "0"){
               
               $envoi['destinataire'] = "Pas de commande";
               $cancel = "<a href=\"javascript:deladvcmd(".$envoi['id'].")\" style=\"color:red;\">Annuler la commande</a>";
          }
          
          $return .= ""
          . "<tr style=\"background-color:#00dd00;\">"
               . "<td rowspan=\"".$rowspan."\">".$envoi['destinataire']
                    . "<br>".date("d/m/Y H:i",$envoi['date'])
                    . "<br>".$lf1['expediteur']
                    . "<br>".$livrcmd." ".$cancel
               . "</td>"
               . "<td colspan=\"4\"></td>"
               //. "<td rowspan=\"".$rowspan."\">Transport<br>".$envoi['trspt']."</td>"
               . "<td rowspan=\"".$rowspan."\">Tarif<br>".$envoi['tarif']."</td>"
               . "<td rowspan=\"".$rowspan."\" align=\"center\">"
                    . "Info compta"
                    . "<br>"
                    . "<textarea id=\"comment$envoi[id]\" style=\"background:none;\" cols=\"20\" onclick=\"this.style.background='white';document.getElementById('updcom$envoi[id]').style.display='block';\">$envoi[comment]</textarea><a href=\"javascript:updadvcomment($envoi[id]);\" id=\"updcom".$envoi['id']."\" style=\"display:none;color:black;\">Mettre à jour</a>"
               . "</td>"
          . "</tr>";
                  
          $total += $envoi['tarif'];

          mysqli_data_seek($r2,0);
	
          while($lf = $DB_site2->fetch_array($r2)){
               
               $color1     = ($lf['indate'] >= $today && $lf['indate'] < $tomorrow ? " style=\"color:red;\"" : "");
               $color2     = ($lf['outdate'] >= $today && $lf['outdate'] < $tomorrow ? " style=\"color:red;\"" : "");
               $imperative = ($lf['imperatif'] == 1 ? "imperative" : "maxi");
               $img        = "";
               $path       = "../configurations/www.mon-espace-cuisson.com/images/produits/br/".$lf['artid'].".jpg";
               
               if(file_exists($path)) $img="<img src=\"$path\" style=\"width:30px;\" onmouseover=\"this.style.position='absolute';this.style.width='200px';\" onmouseout=\"this.style.position='relative';this.style.width='30px';\">";
               
               $return .= ""
               . "<tr style=\"background-color:#99dd99;\">"
                    . "<td>".$img."</td>"
                    . "<td title=\"".$lf['libelle']."\">".$lf['qte']." ".$lf['artcode']."</td>"
               . "</tr>";
	}
	$compat=false;
      }
    }
    
     // Récupération des actions du service après vente, effectuées sur la mise en service
     $liste = $adv->listadv($facturid, "service");
    
     $advlfids = array();
     $sql      = "";
     foreach($liste as $advprod){

          if($advprod->efid == 0){
             
               $txtenvoi = "Pas d'envoi programmé";
               
               if($advprod->envoi>0) $txtenvoi = "Envoi le ".date("d/m",$advprod->envoi)." à ".$advprod->envoi_heure."h";
               
               $return .= ""
               . "<tr style=\"background-color:#FFAA22;\">"
                    . "<td rowspan=\"".(count($advprod->lignes)+1)."\" style=\"color:black;\" nowrap>"
                         . "Commande enregistrée"
                         . "<br>"
                         . ($advprod->destinataire=="0" ? "Pas de commande":$advprod->destinataire)
                         . "<br>"
                         . date("d/m/Y H:i",$advprod->date_creation)
                         . "<br>"
                         . $advprod->utilisateur
                         . "<br>"
                         . "<a href=\"facture.php?action=editer&facturid=".$facturid."&advid=".$advprod->id."&type=miseenservice#adv\">Modifier</a> <a href=\"javascript:deladv(".$advprod->id.",".$advprod->envoi.");\" style=\"color:red;\">Supprimer</a>"
                         . "<br>"
                         . $txtenvoi
                    . "</td>"
                    . "<td colspan=\"7\"></td>"
               . "</tr>";
               
               foreach($advprod->lignes as $lignefactureid => $qte){
                    
                    $advlfids[] = $lignefactureid;
                    
                    $sql = "SELECT *";
                    $sql .= " FROM lignefacture";
                    $sql .= " WHERE lignefactureid = ".$lignefactureid;
                    $lignefacture = $DB_site2->query_first($sql);
                    
                    $img  = "";
                    $path = "../configurations/www.mon-espace-cuisson.com/images/produits/br/".$lignefacture['artid'].".jpg";
                    
                    if(file_exists($path)) $img = "<img src=\"".$path."\" style=\"width:30px;\" onmouseover=\"this.style.position='absolute';this.style.width='200px';\" onmouseout=\"this.style.position='relative';this.style.width='30px';\">";
                    
                    $return .= ""
                    . "<tr style=\"background-color:#FFCC99;\">"
                         . "<td>".$img."</td>"
                         . "<td title=\"".$lignefacture['libelle']."\">".$lignefacture['qte']." ".$lignefacture['artcode']."</td>"
                         . "<td>"
                              . "Date enlèv."
                              . "<br>"
                              . $advprod->fields['slslivrdate']
                         . "</td>"
                         . "<td>"
                              . "Date livr."
                              . "<br>"
                              .($advprod->fields['slsmaxdate'])
                         . "</td>"
                         . "<td></td>"
                         . "<td></td>"
                         . "<td></td>"
                    . "</tr>";
               }
          }
     }
     
    if($advlfids != array()) $sql = " AND lignefactureid NOT IN (".implode(",",$advlfids).")";

     //Récupération des articles dont la commande n'a pas été passée
     $sqlFinal = "SELECT lf.*, a.artcode_eurocave, a.typearticleid";
     $sqlFinal .= " FROM lignefacture lf";
     $sqlFinal .= " INNER JOIN article a USING (artid)";
     $sqlFinal .= " WHERE factureid = ".$facturid;
     $sqlFinal .= " AND (";
     $sqlFinal .= "      SELECT COUNT(ligne_facture_id)";
     $sqlFinal .= "      FROM envoi_fournisseur_ligne";
     $sqlFinal .= "      WHERE ligne_facture_id = lf.lignefactureid";
     $sqlFinal .= "      AND type = 'service'";
     $sqlFinal .= ") = 0";
     $sqlFinal .= " AND lf.artid <> 0 ".$sql;
     $req = $DB_site2->query($sqlFinal);
     
     //Si aucune commande passée, on affiche l'encart des commandes non passées (rouge)
     if($DB_site2->num_rows($req) > 0){

          //Boucle sur les articles non commandés
          $cpt = 0;
          while($nocmd = $DB_site2->fetch_array($req)){
               
               //On regarde si une demande d'installation a été passé pour cet article, si non, on ne l'affiche pas
               $sql = "SELECT lignefactureinstallation_id ";
               $sql .= " FROM lignefactureinstallation";
               $sql .= " WHERE factureid = ".$nocmd['factureid'];
               $sql .= " AND lignefactureinstallation_typearticles LIKE '%|".$nocmd['typearticleid']."|%'";
               $tabInstallation = $DB_site2->query_first($sql);
               
               if(!empty($tabInstallation)){
                    if($cpt == 0){
                         $return .= ""
                         . "<tr style=\"background-color:#FF2222;\">"
                              . "<td class=\"adv_cmd\" rowspan=\"".($DB_site2->num_rows($req)+1)."\">"
                                   . "Articles non commandés"
                                   . "<br>"
                                   . "<a class=\"actions commander\" href=\"facture.php?facturid=".$facturid."&action=editer&type=miseenservice&advid=0#adv\">Commander</a>"
                                   ."<br><a href=\"javascript:nocmd('service','".$nocmd['lignefactureid']."')\" style=\"display:block;color:white;margin-left:3px;\">Pas de Cde</a>"
                              . "</td>"
                              . "<td class=\"article_nocmd_titre\"></td>"
                              . "<td class=\"article_nocmd_titre\"></td>"
                              . "<td class=\"article_nocmd_titre\"></td>";
                    }

                    $cmddate = "";
                    $cmdf    = "service";

                    // Récupération de la date préalablement choisie
                    $sql = "SELECT *";
                    $sql .= " FROM calendrier";
                    $sql .= " WHERE type = 'servicecmd'";
                    $sql .= " AND objid = ".$nocmd['lignefactureid'];
                    $rc = $DB_site2->query($sql);

                    $cal = $DB_site2->fetch_array($rc);

                    if($cal != false){
                         if($cal['date'] > 0) $cmddate = date("d/m/Y",$cal['date']);
                         $cmdf = $cal['txt'];
                    }

                    $img  = "";
                    $path = "../configurations/".$host."/images/produits/br/".$nocmd['artid'].".jpg";
                    $img  = "<img src=\"../images/templates/photo_non_dispo.jpg\" style=\"width:30px;\">";
                    if(file_exists($path)) $img = "<img src=\"".$path."\" style=\"width:30px;\" onmouseover=\"this.style.position='absolute';this.style.width='200px';\" onmouseout=\"this.style.position='relative';this.style.width='30px';\">";

                    $return .= ""
                    . "<tr style=\"background-color:#FF9999;\">"
                         . "<td class=\"article_nocmd\">".$img."</td>"
                         . "<td class=\"article_nocmd\" style=\"font-weight:bold;\" title=\"".$nocmd['libelle']."\">".$nocmd['qte']." x ".$nocmd['artcode']."<br>(".$nocmd['artcode_eurocave'].")</td>"
                         . "<td class=\"article_nocmd\" colspan=\"5\" nowrap>A commander le <input type=\"text\" name=\"cmddate4".$nocmd['lignefactureid']."\" id=\"cmddate4".$nocmd['lignefactureid']."\" value=\"".$cmddate."\" onclick=\"cmddate(this.id,'servicecmd',".$nocmd['lignefactureid'].",'date')\" size=\"10\"> à <input type=\"text\" id=\"dcmdf4".$nocmd['lignefactureid']."\" value=\"".$cmdf."\" onclick=\"if(this.value == 'service') this.value='';document.getElementById('okdcmdf4".$nocmd['lignefactureid']."').style.display='inline';\" size=\"35\"><input type=\"button\" id=\"okdcmdf4".$nocmd['lignefactureid']."\" style=\"display:none;\" value=\"OK\" onclick=\"cmdtxt('servicecmd',".$nocmd['lignefactureid'].",document.getElementById('dcmdf3".$nocmd['lignefactureid']."').value);\"></td>"
                    . "</tr>";

                    $compat = false;
                    $cpt++;
               }
          }
     
     }

    //compatibilité anciennes commandes
    if($compat){
      $ef=$DB_site2->fetch_array($DB_site2->query("SELECT ROUND(SUM(tarif),2) FROM envoi_fournisseur WHERE type='sls' AND facturid=$facturid"));
      $total+=$ef[0];
      $req=$DB_site2->query("SELECT * FROM lignefacture lf INNER JOIN envoi_fournisseur_ligne efl ON (lf.lignefactureid=efl.ligne_facture_id) WHERE lf.factureid=$facturid AND efl.type='sls' AND efl.efid=0");
      if($DB_site2->num_rows($req)>0) $return.="<tr style=\"background-color:#AAAAAA;\"><td rowspan=\"".($DB_site2->num_rows($req)+1)."\">Articles commandés</td><td colspan=\"4\"></td><td rowspan=\"".($DB_site2->num_rows($req)+1)."\">$ef[0]</td></tr>";
      while($oldcmd=$DB_site2->fetch_array($req)){
		$return.="<tr><td title=\"$oldcmd[libelle]\">".$oldcmd['artcode']."</td><td>$oldcmd[expediteur] ".date("d/m/Y",$oldcmd['date'])."</td><td></td><td></td></tr>";
      }
    }

    $return.="</table>";
    return $return;
}

// --------------------------------------------------------------------------------- //
// --------------------------------------------------------------------------------- //
// --------------------------------------------------------------------------------- //
// fonction qui affiche le tableau pour SFG (encore utilisé ?)                       //
// --------------------------------------------------------------------------------- //
// --------------------------------------------------------------------------------- //
// --------------------------------------------------------------------------------- //


function sfg_recap($facturid){
	global $DB_site2,$host;
	global $rs_facture;
	global $devises;

	$bgcolor="#FF2222";
	$bgcolor2="#FF9999";
	$dateexp=DateTime::createFromFormat("Y-m-d",$rs_facture['datefacture']);
	if(($rs_facture['valide']==5 || $rs_facture['valide']==8) && time()>$dateexp->getTimestamp()+86400){ $bgcolor2="#99dd99"; $bgcolor="#00dd00"; }

	$produits=$DB_site2->query("SELECT *,lc.paht as caract_value FROM lignefacturecaracteristique lc, lignefacture l WHERE l.factureid=".$facturid." AND (lc.caractvalid =10 OR lc.caractvalid =11) AND lc.lignefactureid = l.lignefactureid");
	if($DB_site2->num_rows($produits)>0){
		$tr="";
		$return="<br><table id=\"sls_recap\" width=\"100%\">
		<tr><td colspan=\"8\" style=\"color:white;background-color:#AAAAAA;font-weight:bold;\"><img src=\"images/sfg.png\" width=\"25\" align=\"absmiddle\"> Garantie</td></tr>";

		while($produit=$DB_site2->fetch_array($produits)){
			$total+=$produit['caract_value']*$produit['qte'];
			$img="";
			$path="../configurations/".$host."/images/produits/br/".$produit['artid'].".jpg";
			if(file_exists($path)) $img="<img src=\"$path\" style=\"width:30px;\" onmouseover=\"this.style.position='absolute';this.style.width='200px';\" onmouseout=\"this.style.position='relative';this.style.width='30px';\">";
			$tr.="<tr style=\"background-color:$bgcolor2;\">
			<td>$img</td>
			<td title=\"$produit[libelle]\">".$produit['qte']." ".$produit['artcode']."</td>
			<td width=\"200\">".$produit['libelle_caractval']."</td>
			</tr>";
		}
		$contrat=$DB_site2->query_first("SELECT receive,annule FROM sfg WHERE facturid=".$facturid);
		$date="";
		$contratpdf="";
		if($contrat['receive']>0){
			$date=date("d/m/Y",$contrat['receive']);
			if($contrat['annule']>0) $date.="<br>Annulé le ".date("d/m/Y",$contrat['annule']);
			$contratpdf="<a href=\"sfg_pdf/$facturid.pdf\">Contrat</a>";
			if(!file_exists("sfg_pdf/$facturid.pdf")) $contratpdf="Contrat introuvable";
		}
		$rowspan=$DB_site2->num_rows($produits)+1;
		$return.="<tr style=\"background-color:$bgcolor;\">
		<td rowspan=\"$rowspan\">SFG<br>$date<br>$contratpdf</td>
		<td colspan=\"4\"></td>
		<td rowspan=\"$rowspan\"></td>
		<td rowspan=\"$rowspan\">Tarif<br>".number_format($total,2,',','')."&nbsp;".$devises->symbole($envoi['devise'])."</td>
		<td rowspan=\"$rowspan\" align=\"center\"></td>
		</tr>".$tr;

		$return.="</table>";
	}

	return $return;
}

// --------------------------------------------------------------------------------- //
// --------------------------------------------------------------------------------- //
// --------------------------------------------------------------------------------- //
// fonction qui affiche le tableau pour GARANTIE                                     //
// --------------------------------------------------------------------------------- //
// --------------------------------------------------------------------------------- //
// --------------------------------------------------------------------------------- //

function garantie_recap($facturid){
	$rootpath = '/var/www/html/mec/';
	global $DB_site2,$host;
	global $adv;
	global $artref;
	global $user;
	global $user_info;
	global $devises;

	include_once "../class/garantie.class.php";
	require_once($rootpath.'/class/DAOgarantie.class.php');
	include_once $rootpath ."/class/DAOarticle.class.php";
	include_once "$rootpath./class/DAOpays.class.php";
	include_once "$rootpath./class/DAOlignefacturegarantie.class.php";
	include_once "$rootpath./class/DAOfournisseur.class.php";
	include_once "$rootpath./class/DAOtype.class.php";
	include_once "$rootpath./class/lignefacturegarantie.class.php";
	include "adv/mail_garantie.php";
	
    $infoadv=$DB_site2->query_first("SELECT suivi FROM suivi_commande WHERE type='advgar' AND facturid=".$facturid." ORDER BY date DESC LIMIT 1");
	
	if(isset($_POST['lignefactureid'])){
		$lfinfos=array();
		if(is_array($_POST['lignefactureid'])){
			foreach($_POST['lignefactureid'] as $lfid){
				if($_POST['chkgar1_'.$lfid]=="on")
					$lfinfos[$lfid]=array("oldgarantieid"=>$_POST['oldgarantieid1_'.$lfid],"newgarantieid"=>$_POST['newgarantieid1_'.$lfid]);
				if($_POST['chkgar2_'.$lfid]=="on")
					$lfinfos[$lfid]=array("oldgarantieid"=>$_POST['oldgarantieid2_'.$lfid],"newgarantieid"=>$_POST['newgarantieid2_'.$lfid]);
			}
		} else {
			if($_POST['chkgar1_'.$_POST['lignefactureid']]=="on")
				$lfinfos[$_POST['lignefactureid']]=array("oldgarantieid"=>$_POST['oldgarantieid1_'.$_POST['lignefactureid']],"newgarantieid"=>$_POST['newgarantieid1_'.$_POST['lignefactureid']]);
			if($_POST['chkgar2_'.$_POST['lignefactureid']]=="on")
				$lfinfos[$_POST['lignefactureid']]=array("oldgarantieid"=>$_POST['oldgarantieid2_'.$_POST['lignefactureid']],"newgarantieid"=>$_POST['newgarantieid2_'.$_POST['lignefactureid']]);
		}
		if($lfinfos!=array())
			mail_garantie($facturid,$lfinfos);
	}

	if(isset($_GET['deladvgar'])){
		$advdel=new adv($DB_site2,$_GET['deladvgar']);
		$advdel->delete();
	}

/*
function saveInTableFields($name,$value) {
	global $DB_site2;
	global $user_info;
	global $adv;
	    $DB_site2->query("INSERT INTO adv_fields SET advid=".$adv->id.", name='".$name."', value='".mysqli_real_escape_string($DB_site2->link_id,$value)."', utilisateur='".$user_info['username']."', date=".time());
    }

    function deleteInTableFields($name) {
	global $DB_site2;
	global $user_info;
	    $DB_site2->query("DELETE FROM adv_fields WHERE name='".$name."';");
    }

    function nameInTableFields($name,$value=1) {
	global $DB_site2;
	global $user_info;
	if ($value===0) {
	    return $DB_site2->query("SELECT * FROM adv_fields WHERE name='".$name."';");
	} else {
	    $r=$DB_site2->query_first("SELECT * FROM adv_fields WHERE name='".$name."';");
	    return $r["value"];
	}
}
*/

	$facture=$DB_site2->query_first("SELECT paysid,site_code,langue FROM facture WHERE facturid=".$facturid);
	if($facture['site_code']=="") $facture['site_code']="base";

    /*if (isset($_POST['Valider']) && count($_POST['numLigneCmd'])>0) {
	    $erreur="Problème de garantie sur le(s) produit(s) ";
	    $templateUser=new template($user['langue'],$DB_site2);
	    foreach ($_POST['numLigneCmd'] as $ligneCmd) {
		    if (isset($_POST['garsup1_'.$ligneCmd.''])) {
			    $idgarsup1=$_POST['garsup1_'.$ligneCmd.''];
			    if (isset($_POST['garsup2_'.$ligneCmd.''])) {
				    $idgarsup2=$_POST['garsup2_'.$ligneCmd.''];
			    } else {
				    $idgarsup2='null';
			    }
			    $garantieSup1=new garantie($DB_site2,'FR',$idgarsup1);
			    $garantieSup2=new garantie($DB_site2,'FR',$idgarsup2);
			    $defautidgarsup1=$_POST['defautidgarsup1_'.$ligneCmd.''];
			    $defautidgarsup2=$_POST['defautidgarsup2_'.$ligneCmd.''];
			    $defautGarantieSup1=new garantie($DB_site2,'FR',$defautidgarsup1);
			    $defautGarantieSup2=new garantie($DB_site2,'FR',$defautidgarsup2);
			    $prixgarsup1=$_POST['prixgarsup1_'.$ligneCmd.''];
			    if (isset($_POST['prixgarsup2_'.$ligneCmd.''])) {
				    $prixgarsup2=$_POST['prixgarsup2_'.$ligneCmd.''];
			    } else {
				    $prixgarsup2="";
			    }
			    $IDgarClientDuree=nameInTableFields("IDgarClientDuree_".$ligneCmd);
			    $IDgarClientPrix=nameInTableFields("IDgarClientPrix_".$ligneCmd);
			    $IDgarClientType=nameInTableFields("IDgarClientType_".$ligneCmd);
			    $IDgarClient=nameInTableFields("IDgarClient_".$ligneCmd);
			    $IDgarFourn=nameInTableFields("IDgarFourn_".$ligneCmd);
			    $garantieFourn=new garantie($DB_site2,'FR',$IDgarFourn);
			    $numArt=$_POST['numArt_'.$ligneCmd.''];
			    $IDArt=$_POST['IDArt_'.$ligneCmd.''];
			    $qteArt=$_POST['qteArt_'.$ligneCmd.''];
				    //On ne sélectionne plus la date de début de garanteie à ce stade
				    //C'est lorsque la commande passe en status "expédiée"
				    
			    //$cmdDateGarantie=$_POST['cmddateGarantie_'.$ligneCmd.''];
// 				if ($cmdDateGarantie!="" && $cmdDateGarantie!="Pas de garantie") {
// 					$sp=preg_split('/[- :\/h]/',$cmdDateGarantie);
// 					$cmdDateGarantie=mktime(0,0,0,$sp[1],$sp[0],$sp[2]);
// 				}

			    $DB_site2->query("DELETE FROM adv WHERE facturid='".$facturid."' AND type='garantie' AND lignes='".$qteArt."-".$ligneCmd."';");

			    $duree=0;
			    $prix=0;
			    $add=true;
			    if ($garantieFourn->garantieid==$IDgarClient) {
				    if ($garantieSup1->garantieid!="" || $garantieSup2->garantieid!="") {
					    $add=false;
					    $erreur.=$numArt." / ";
				    }
			    } else {
				    if ($garantieSup1->garantieid=="" && $garantieSup2->garantieid=="") {
					    if ($garantieFourn->duree!=$IDgarClientDuree || $garantieFourn->type_garantie!=$IDgarClientType) {
						    $add=false;
						    $erreur.=$numArt." / ";
					    }
				    } elseif ($garantieSup1->garantieid!="" && $garantieSup2->garantieid=="") {
					    if ($garantieSup1->type_garantie==$IDgarClientType) {
						    $duree+=$garantieSup1->duree;
						    $prix+=$prixgarsup1;
						    if ($garantieFourn->type_garantie==$garantieSup1->type_garantie && $garantieSup1->cumul==1) {
							    $duree+=$garantieFourn->duree;
							    $prix+=$garantieFourn->prix;
						    }
					    }
					    if ($IDgarClientDuree!=$duree || $IDgarClientPrix!=$prix) {
						    $add=false;
						    $erreur.=$numArt." / ";
					    }
				    } elseif ($garantieSup2->garantieid!="" && $garantieSup1->garantieid=="") {
					    $erreur.=$numArt." / ";
				    } else {
					    if ($garantieSup1->type_garantie==$IDgarClientType && $garantieSup2->type_garantie==$IDgarClientType) {
						    $duree+=$garantieSup1->duree;
						    $duree+=$garantieSup2->duree;
						    $prix+=$prixgarsup1;
						    $prix+=$prixgarsup2;
						    if ($garantieFourn->type_garantie==$garantieSup1->type_garantie) {
							    $duree+=$garantieFourn->duree;
							    $prix+=$garantieFourn->prix;
						    }
					    }
					    if ($IDgarClientDuree!=$duree || $IDgarClientPrix!=$prix) {
						    $add=false;
						    $erreur.=$numArt." / ";
					    }
				    }
			    }
			    if ($add) {
				    if ($garantieSup1->garantieid==$defautGarantieSup1->garantieid && $garantieSup2->garantieid==$defautGarantieSup2->garantieid) {
					    //echo "Aucun changement<br/><br/>";
					    if ($defautGarantieSup1->garantieid!="") $DB_site2->query("UPDATE lignefacturegarantie SET differenceprix='".$prixgarsup1."' WHERE lignefactureid=".$ligneCmd." AND garantieid=".$defautGarantieSup1->garantieid.";");
					    if ($defautGarantieSup2->garantieid!="") $DB_site2->query("UPDATE lignefacturegarantie SET differenceprix='".$prixgarsup2."' WHERE lignefactureid=".$ligneCmd." AND garantieid=".$defautGarantieSup2->garantieid.";");	
					    if ($defautGarantieSup1->garantieid!="") {
						    $advg1=new adv($DB_site2);
						    $advg1->add_lignefacture($qteArt,$ligneCmd);
						    $advg1->facturid=$facturid;
						    $advg1->type="garantie";
						    $advg1->destinataire=$defautGarantieSup1->fournisseur;
						    $advg1->save_field("insertDate_".$ligneCmd,time());
// 							$DB_site2->query("INSERT INTO adv SET facturid='".$facturid."', date='', utilisateur='".$user_info['username']."', lignes='".$qteArt.'-'.$ligneCmd."', type='garantie', destinataire='".$defautGarantieSup1->fournisseur."', devise='".$defautGarantieSup1->deviseFournisseur()."'");
						    
						    if ($defautGarantieSup2->garantieid!="") {
// 								$DB_site2->query("INSERT INTO adv SET facturid='".$facturid."', date='', utilisateur='".$user_info['username']."', lignes='".$qteArt.'-'.$ligneCmd."', type='garantie', destinataire='".$defautGarantieSup2->fournisseur."', devise='".$defautGarantieSup2->deviseFournisseur()."'");
							    $advg2=new adv($DB_site2);
							    $advg2->add_lignefacture($qteArt,$ligneCmd);
							    $advg2->facturid=$facturid;
							    $advg2->type="garantie";
							    $advg2->destinataire=$defautGarantieSup2->fournisseur;
							    $advg2->save_field("insertDate_".$ligneCmd,time());
						    }
// 							$DB_site2->query("INSERT INTO envoi_fournisseur_ligne SET date=".time().", expediteur='".$user_info['username']."', type='gar', lignefactureid=".$ligneCmd.", paht=".$prixgarsup1.", devise='EUR', qte=".$qteArt);
// 							if ($defautGarantieSup2->garantieid!="") {
// 							    $DB_site2->query("INSERT INTO envoi_fournisseur_ligne SET date=".time().", expediteur='".$user_info['username']."', type='gar', lignefactureid=".$ligneCmd.", paht=".$prixgarsup2.", devise='".$defautGarantieSup2->deviseFournisseur()."', qte=".$qteArt);
// 							}
					    }
				    } else {
					    //echo "Le changement c'est maintenant<br/><br/>";
					    $DB_site2->query("DELETE FROM lignefacturegarantie WHERE lignefactureid=".$ligneCmd.";");
					    DAOlignefacturegarantie::insertDistinctPrix($DB_site2,$garantieFourn->garantieid,$IDArt,$garantieFourn->prix,$ligneCmd,$templateUser->translate("/article/garantie_type/garantie",'F'),$IDgarClientDuree,$garantieFourn->fournisseur,DAOfournisseur::loadCodeFournisseurByName($DB_site2,$garantieFourn->fournisseur),$user['paysid'],$garantieFourn->duree,$garantieFourn->type_garantie);
					    //if ($defautGarantieSup1->garantieid!="") $DB_site2->query("DELETE FROM lignefacturegarantie WHERE lignefactureid=".$ligneCmd." AND garantieid=".$defautGarantieSup1->garantieid.";");
					    //if ($defautGarantieSup2->garantieid!="") $DB_site2->query("DELETE FROM lignefacturegarantie WHERE lignefactureid=".$ligneCmd." AND garantieid=".$defautGarantieSup2->garantieid.";");
					    if ($garantieSup1->garantieid!="") {
						    $typeGarantie=$templateUser->translate("/article/garantie_type/garantie",'F');
						    if ($garantieSup1->cumul==1) {
							$typeGarantie=$templateUser->translate("/article/garantie_type/extension_garantie",'F')." +";
						    }
						    DAOlignefacturegarantie::insertDistinctPrix($DB_site2,$garantieSup1->garantieid,$IDArt,$prixgarsup1,$ligneCmd,$typeGarantie,$IDgarClientDuree,$garantieSup1->fournisseur,DAOfournisseur::loadCodeFournisseurByName($DB_site2,$garantieSup1->fournisseur),$user['paysid'],$garantieSup1->duree,$garantieSup1->type_garantie);
						    if ($garantieSup2->garantieid!="") {
							    $typeGarantie=$templateUser->translate("/article/garantie_type/garantie",'F');
							    if ($garantieSup2->cumul==1) {
								$typeGarantie=$templateUser->translate("/article/garantie_type/extension_garantie",'F')." +";
							    }
							    DAOlignefacturegarantie::insertDistinctPrix($DB_site2,$garantieSup2->garantieid,$IDArt,$prixgarsup2,$ligneCmd,$typeGarantie,$IDgarClientDuree,$garantieSup2->fournisseur,DAOfournisseur::loadCodeFournisseurByName($DB_site2,$garantieSup2->fournisseur),$user['paysid'],$garantieSup2->duree,$garantieSup2->type_garantie);
						    }
					    }
					    if ($defautGarantieSup1->garantieid!="") {
// 							$DB_site2->query("INSERT INTO adv SET facturid='".$facturid."', date='', utilisateur='".$user_info['username']."', lignes='".$qteArt.'-'.$ligneCmd."', type='garantie', destinataire='".$garantieSup1->fournisseur."', devise='".$garantieSup1->deviseFournisseur()."'");
// 							deleteInTableFields("insertDate_".$ligneCmd);
// 							saveInTableFields("insertDate_".$ligneCmd,time());
						    $advg1=new adv($DB_site2);
						    $advg1->add_lignefacture($qteArt,$ligneCmd);
						    $advg1->facturid=$facturid;
						    $advg1->type="garantie";
						    $advg1->destinataire=$defautGarantieSup1->fournisseur;
						    $advg1->save_field("insertDate_".$ligneCmd,time());
						    if ($defautGarantieSup2->garantieid!="") {
							    $advg2=new adv($DB_site2);
							    $advg2->add_lignefacture($qteArt,$ligneCmd);
							    $advg2->facturid=$facturid;
							    $advg2->type="garantie";
							    $advg2->destinataire=$defautGarantieSup2->fournisseur;
							    $advg2->save_field("insertDate_".$ligneCmd,time());
// 								$DB_site2->query("INSERT INTO adv SET facturid='".$facturid."', date='', utilisateur='".$user_info['username']."', lignes='".$qteArt.'-'.$ligneCmd."', type='garantie', destinataire='".$garantieSup2->fournisseur."', devise='".$garantieSup2->deviseFournisseur()."'");
// 								deleteInTableFields("insertDate_".$ligneCmd);
// 								saveInTableFields("insertDate_".$ligneCmd,time());
						    }
					    }
				    }
			    } else {
				    echo "Produit en erreur<br/><br/>";
			    }
		    }
	    }
	}*/

    /*if ($_GET['delete']==1) {
	    $DB_site2->query("DELETE FROM adv_fields WHERE advid=0;");
	}*/

    //garantie
	$req=$DB_site2->query("SELECT DISTINCT(artid),lf.* FROM lignefacture lf INNER JOIN lignefacturegarantie lfg ON lf.lignefactureid=lfg.lignefactureid WHERE lf.factureid=$facturid;");
	if($DB_site2->num_rows($req)>0) {

		$return="<br/>
		<form action=\"#adv\" method=\"post\">
		<table id=\"garantie_recap\" width=\"100%\">
		<tr>
            <td colspan=\"8\" style=\"color:white;background-color:#AAA; font-weight:bold;\">Garantie - ".DAOpays::getPaysById($DB_site2,$user['paysid'])."</td>
        </tr>
        <tr>
            <td colspan=\"8\" style=\"background-color:#AAA;\">
                <span class=\"infobtn\" onclick=\"this.parentNode.querySelector('.infocmdpopup').style.display='block';\">Info ADV <img src=\"images/editer.gif\"></span>
                <span class=\"infocmdtxt\"> $infoadv[suivi] </span>
                <div class=\"infocmdpopup\">
                    <textarea id=\"garadvcomment\">$infoadv[suivi]</textarea>
                    <img src=\"images/icon-x.svg\" title=\"Annuler\" onclick=\"this.parentNode.style.display='none';\"> <img src=\"images/coche_verte.svg\" onclick=\"updadvcomment('gar')\" title=\"Mettre à jour\">
                </div>
            </td>
        </tr>";
		if (isset($erreur) && $erreur!="Problème de garantie sur le(s) produit(s) ") {
			$return.="<tr><td colspan=\"8\" style=\"color:white;background-color:red; font-weight:bold;height:150px;text-align:center;\">".substr($erreur, 0, -3)."</td></tr>";
		}
		$return.="
		<tr style=\"background-color:grey;\">
		<td colspan=\"2\"></td>
		<td style=\"color:white;width:170px;\">Garantie fournisseur</td>
		<td style=\"color:white;width:170px;\">Garantie complémentaire 1</td>
		<td style=\"color:white;width:170px;\">Garantie complémentaire 2</td>
		<td style=\"color:white;width:90px;\">Garantie achetée par le client</td>";
		//<td style=\"color:white;width:50px;\">Date début garantie</td>
		$return.="</tr>";
	}
	
	$jscomp=$jslfid=array();
	
	while($nocmd=$DB_site2->fetch_array($req)){

		$sql = "SELECT *";
		$sql .= " FROM lignefacturegarantiecmd";
		$sql .= " WHERE lignefactureid = ".$nocmd['lignefactureid'];
		$lignefacturegarantiecmd = $DB_site2->query_first($sql);

          // mémorisation garantie client
		if($lignefacturegarantiecmd['lignefactureid'] == ""){  

			$sql = "SELECT lfg.*, SUM(differenceprix) AS differenceprixTotale, MAX(lfg.duree) as dureeMax, MAX(lfg.type) as typeMax, g.garantie_client_id";
			$sql .= " FROM lignefacturegarantie lfg";
			$sql .= " INNER JOIN garantie g ON (g.garantieid = lfg.garantieid)";
			$sql .= " WHERE lfg.lignefactureid = ".$nocmd['lignefactureid'];
			$lfgcmd = $DB_site2->query_first($sql);

			$lignefacturegarantiecmd['lignefactureid']     = $nocmd['lignefactureid'];
			$lignefacturegarantiecmd['duree']              = intval($lfgcmd['dureeMax']);
			$lignefacturegarantiecmd['prix']               = floatval($lfgcmd['differenceprixTotale']);
			$lignefacturegarantiecmd['type']               = intval($lfgcmd['typeMax']);
			$lignefacturegarantiecmd['garantie_client_id'] = intval($lfgcmd['garantie_client_id']);

			$sql = "INSERT INTO lignefacturegarantiecmd SET";
			$sql .= " lignefactureid      = ".$lignefacturegarantiecmd['lignefactureid'];
			$sql .= ", duree              = ".$lignefacturegarantiecmd['duree'];
			$sql .= ", prix               = ".$lignefacturegarantiecmd['prix'];
			$sql .= ", type               = ".$lignefacturegarantiecmd['type'];
			$sql .= ", garantie_client_id = ".$lignefacturegarantiecmd['garantie_client_id'];
			$DB_site2->query($sql);
		}

	/*$return.="<input type=\"hidden\" name=\"numLigneCmd[]\" value=\"".$nocmd['lignefactureid']."\"/>";
	$return.="<input type=\"hidden\" name=\"numArt_".$nocmd['lignefactureid']."\" value=\"".$nocmd['artcode']."\"/>";
	$return.="<input type=\"hidden\" name=\"IDArt_".$nocmd['lignefactureid']."\" value=\"".$nocmd['artid']."\"/>";
	$return.="<input type=\"hidden\" name=\"qteArt_".$nocmd['lignefactureid']."\" value=\"".$nocmd['qte']."\"/>";
*/
	$img="";
	$path="../configurations/".$host."/images/produits/br/".$nocmd['artid'].".jpg";
	if(file_exists($path)) $img="<img src=\"$path\" style=\"width:30px;\" onmouseover=\"this.style.position='absolute';this.style.width='200px';\" onmouseout=\"this.style.position='relative';this.style.width='30px';\">";

		//inserer données de la garantie client duree/pays dans fields mais une seule fois
		/*$IDgarFourn="IDgarFourn_".$nocmd['lignefactureid'];
		if ($DB_site2->num_rows(nameInTableFields($IDgarFourn,0))==0) {
			$IDgarantieFournisseur=$DB_site2->query_first("SELECT g.* FROM ((envoi_fournisseur ef INNER JOIN envoi_fournisseur_ligne efl ON ef.id=efl.efid AND ef.type=\"fourn\") INNER JOIN lignefacture lf ON lf.lignefactureid=efl.ligne_facture_id AND lf.lignefactureid=".$nocmd['lignefactureid'].") INNER JOIN garantie g ON g.fournisseur=ef.destinataire AND g.utilisation_garantie=0;");
			if ($IDgarantieFournisseur['garantieid']!="") {
				saveInTableFields($IDgarFourn,$IDgarantieFournisseur['garantieid']);
			}
		}
		$libGarantieFourn="";
		if (nameInTableFields($IDgarFourn)!="") {
			$garantieFournisseur=new garantie($DB_site2,'FR',nameInTableFields($IDgarFourn));
			$libGarantieFourn=$garantieFournisseur->duree." an(s) ".$garantieFournisseur->getTypeGarantie();
		}
		
		$IDgarClient="IDgarClient_".$nocmd['lignefactureid'];
		$IDgarClientDuree="IDgarClientDuree_".$nocmd['lignefactureid'];
		$IDgarClientPrix="IDgarClientPrix_".$nocmd['lignefactureid'];
		$IDgarClientType="IDgarClientType_".$nocmd['lignefactureid'];
		if ($DB_site2->num_rows(nameInTableFields($IDgarClient,0))==0) {
			$choixGarantieClient=$DB_site2->query_first("SELECT SUM(differenceprix) AS differenceprixTotale, MAX(lfg.duree) as dureeMax, MAX(lfg.type) as typeMax, lfg.* FROM ((envoi_fournisseur ef INNER JOIN envoi_fournisseur_ligne efl ON ef.id=efl.efid AND ef.type=\"fourn\") INNER JOIN lignefacture lf ON lf.lignefactureid=efl.ligne_facture_id AND lf.lignefactureid=".$nocmd['lignefactureid'].") INNER JOIN lignefacturegarantie lfg ON lfg.lignefactureid=lf.lignefactureid GROUP BY `garantieid` ORDER BY typeMax DESC, dureeMax DESC;");
			if ($choixGarantieClient['garantieid']!="") {
				saveInTableFields($IDgarClient,$choixGarantieClient['garantieid']);
				saveInTableFields($IDgarClientDuree,$choixGarantieClient['dureeMax']);
				saveInTableFields($IDgarClientPrix,$choixGarantieClient['differenceprixTotale']);
				saveInTableFields($IDgarClientType,$choixGarantieClient['type']);
			}
		}
		$libGarantieClient="";
		if (nameInTableFields($IDgarClient)!="") {
			//$garantieClient=new garantie($DB_site2,'FR',nameInTableFields($IDgarClient));
			$IDgarClientValue=nameInTableFields($IDgarClient);
			$IDgarClientDureeValue=nameInTableFields($IDgarClientDuree);
			$IDgarClientPrixValue=nameInTableFields($IDgarClientPrix);
			$IDgarClientTypeValue=nameInTableFields($IDgarClientType);
			$libGarantieClient=$IDgarClientDureeValue." an(s) ".DAOtype::getTypeById($DB_site2,$IDgarClientTypeValue)." - ".$IDgarClientPrixValue."¤";
		}

		$bkColor="FF7777";//rouge
		$optgarsup1="";
		$optgarsup2="";
	if ($IDgarClientValue!="" && $garantieFournisseur->garantieid!="") {
			$optgarsup1="<option value=\"null\">Pas de garantie</option>";
			$optgarsup2="<option value=\"null\"></option>";
			$fournPrinc=true;
			$paysSFG = array(57,181,17,111,99);
			$onlySFG="";
			if (DAOarticle::getFournisseurPrincByProduct($DB_site2,'FR',$nocmd['artid'])==$garantieFournisseur->fournisseur) {
				$typearticle=DAOarticle::typeArticleByIDart($DB_site2,'FR',$nocmd['artid']);
				$marque=DAOarticle::marqueArticleByIDart($DB_site2,'FR',$nocmd['artid']);
				$garantiesPossibles=DAOgarantie::loadGarantiesByTypeMarque($DB_site2,'FR',$typearticle['typearticleid'],$marque['marqueid']);
			} else {
				$typearticle=DAOarticle::typeArticleByIDart($DB_site2,'FR',$nocmd['artid']);
				$garantiesPossibles=DAOgarantie::loadGarantiesByType($DB_site2,'FR',$typearticle['typearticleid']);
				$fournPrinc=false;
				if (in_array($user['paysid'],$paysSFG)) {
					$onlySFG="SFG";
				}
			}
			
			if ($fournPrinc) {
				$bkColor="FFCC99";//orange
			}

			$idgarsup1="null";
			$idgarsup2="null";
			$prices=array();
			$allGarantieCompByLigneFac=$DB_site2->query("SELECT lfg.* FROM lignefacturegarantie lfg INNER JOIN garantie g ON g.garantieid=lfg.garantieid WHERE lfg.lignefactureid=".$nocmd['lignefactureid']." AND utilisation_garantie=1 ORDER BY cumul DESC, lfg.duree DESC;");
			$i=0;
			while ($garantieCompByLigneFac=$DB_site2->fetch_array($allGarantieCompByLigneFac)) {
				$i++;
				${"idgarsup".$i} = $garantieCompByLigneFac['garantieid'];
				$prices[$garantieCompByLigneFac['garantieid']]=$garantieCompByLigneFac['differenceprix'];
			}

			foreach ($garantiesPossibles as $laGarantie) {
				$selected1="";
				$selected2="";
				if ($laGarantie->garantieid==$idgarsup1) {
					$selected1 = "selected";
				}
				if ($laGarantie->garantieid==$idgarsup2) {
					$selected2 = "selected";
				}
				$p="";
				if ($laGarantie->cumul==1) {
					$p="+";
				}
				$validAdd=false;
				if ($laGarantie->type_garantie>=$IDgarClientTypeValue) {
					if (empty($onlySFG)) {
						if (in_array($user['paysid'], $laGarantie->pays) && $laGarantie->utilisation_garantie==1) {
							if ($fournPrinc) {
								$validAdd=true;
							} elseif ($laGarantie->fournisseur==$garantieFournisseur->fournisseur) {
								$validAdd=true;
							}
						}
					} else {
						if ($laGarantie->fournisseur==$onlySFG && ($laGarantie->utilisation_garantie==1 || $laGarantie->fournisseur==$garantieFournisseur->fournisseur)) {
							$validAdd=true;
						}
					}
				}

				if ($validAdd) {
					//$prices[$laGarantie->garantieid]=$laGarantie->prix;
					$optgarsup1.="<option value=\"".$laGarantie->garantieid."\" $selected1>".$laGarantie->fournisseur." ".$p.$laGarantie->duree." an(s) ".$laGarantie->getTypeGarantie()." - PAHT:".$laGarantie->paht.$devises->symbole($laGarantie->deviseFournisseur())."</option>";
					$return.="<input type=\"hidden\" id=\"pricehidden1_".$laGarantie->garantieid."\" value=\"".$laGarantie->prix."\"/>";
					$optgarsup2.="<option value=\"".$laGarantie->garantieid."\" $selected2>".$laGarantie->fournisseur." ".$p.$laGarantie->duree." an(s) ".$laGarantie->getTypeGarantie()." - PAHT:".$laGarantie->paht.$devises->symbole($laGarantie->deviseFournisseur())."</option>";
					$return.="<input type=\"hidden\" id=\"pricehidden2_".$laGarantie->garantieid."\" value=\"".$laGarantie->prix."\"/>";
				}
			}

			$dateCmdGar="";
			$disabledgarsup1="";
			$disabledgarsup2="";
			$disableddate="";
			$linkGarantie="";
			$garantieDejaCmd=$DB_site2->query_first("SELECT * FROM adv WHERE lignes='".$nocmd['qte']."-".$nocmd['lignefactureid']."' AND type='garantie';");
		if ($garantieDejaCmd>0) {
			$bkColor="00DD00";//vert
				$dateCmdGar=date("d/m/Y",$garantieDejaCmd['date']);
				$today=date("d/m/Y",time());
				$dateSaisieDate=date("d/m/Y",nameInTableFields("insertDate_".$nocmd['lignefactureid']));
				if ($today>$dateSaisieDate && $today>$dateCmdGar) {
					$disabledgarsup1="disabled";
					$disabledgarsup2="disabled";
					$disableddate="disabled";
				}

				$garSFGValid=$DB_site2->query_first("SELECT * FROM sfg WHERE facturid = (SELECT DISTINCT(facturid) FROM envoi_fournisseur ef, envoi_fournisseur_ligne efl WHERE ef.id=efl.efid AND efl.ligne_facture_id=".$nocmd['lignefactureid']." AND efl.type='garantie');");
			if ($garSFGValid>0) {
				$linkGarantie='&#10003; SFG';
			}
		}
		}

		if ($idgarsup1=="null") {
			$disabledgarsup2="disabled";
			$dateCmdGar="Pas de garantie";
			$bkColor="00DD00";//vert
		}*/
		
		// CC //
		
		
		$garFournColor="#FF9999";
		$allGarantieFournByLigneFac=$DB_site2->query("SELECT lfg.*, g.garantie_client_id FROM lignefacturegarantie lfg INNER JOIN garantie g ON g.garantieid=lfg.garantieid WHERE lfg.lignefactureid=".$nocmd['lignefactureid']." AND utilisation_garantie=0 ORDER BY cumul DESC, lfg.duree DESC;");
		$tdGarFourn="";
		$fourncmd=false;
		while($GarantieFournByLigneFac=$DB_site2->fetch_array($allGarantieFournByLigneFac)){
			$eflgarFourn=$DB_site2->query_first("SELECT efl.* FROM envoi_fournisseur_ligne efl INNER JOIN envoi_fournisseur ef ON(ef.id=efl.efid) WHERE ligne_facture_id=".$nocmd['lignefactureid']." AND (ef.type='fourn' OR ef.type='stock')");
			if($eflgarFourn['id']!=""){

				if($GarantieFournByLigneFac['paht'] > 0){

					//Recherche l'existence de la garantie dans la table envoi_fournisseur. 
					$sql = "SELECT ef.id";
					$sql .= " FROM envoi_fournisseur ef";
					$sql .= " INNER JOIN envoi_fournisseur_ligne efl ON (efl.efid = ef.id)";
					$sql .= " WHERE efl.ligne_facture_id = ".$nocmd['lignefactureid'];
					$sql .= " AND ef.type = 'garantie'";
					$efl_id = $DB_site2->query_first($sql);

					//Si la garantie fournisseur n'a pas été ajouté dans la table, on l'ajoute
					if(!$efl_id){

						//Si c'est une garantie fournisseur payante, on l'ajoute dans la table "envoi_fournisseur" pour que Cécile puisse la retrouver lors de la saisie des factures
						$efl1  = new envoi_fournisseur_ligne($DB_site2);
						$efl1->type           = "garantie";
						$efl1->qte            = $nocmd['qte'];
						$efl1->paht           = $GarantieFournByLigneFac['paht'];
						$efl1->lignefactureid = $nocmd['lignefactureid'];
						$listefl[] = $efl1;

						$ef1 = new envoi_fournisseur($DB_site2);
						$ef1->facturid     = $nocmd['factureid'];
						$ef1->destinataire = $GarantieFournByLigneFac['fournisseur'];
						$ef1->tarif        = $GarantieFournByLigneFac['paht'] * $nocmd['qte'];
						$ef1->type         = 'garantie';

						$ef1->create();
					    $ef1->create_lines($listefl);
					}
				}

				$garFournColor = "#99dd99";
				$fourncmd      = true;
			}

			$tdGarFourn.=formatGarantieName($GarantieFournByLigneFac['duree'],$GarantieFournByLigneFac['cumul'],$GarantieFournByLigneFac['type'],$GarantieFournByLigneFac['fournisseur'], $GarantieFournByLigneFac['garantie_client_id']);
			$jscomp["garfourn[".$nocmd['lignefactureid']."]"]["duree"]=$GarantieFournByLigneFac['duree'];
			$jscomp["garfourn[".$nocmd['lignefactureid']."]"]["type"]=$GarantieFournByLigneFac['type'];
		}
		$nofourncmd=$DB_site2->num_rows($allGarantieFournByLigneFac)==0;
		
		$cmdstock=false;
		if(!$nofourncmd && !$fourncmd){
            $stock=new stock($DB_site2);
            $cmdstock=count($stock->search($nocmd['artid'],null,"facture ".$facturid,array(2,3)))>0;
        }
		
// 		$garantiesComp=DAOgarantie::loadGarantiesPayArtByPays($DB_site2,$facture['site_code'],$facture['langue'],$facture['paysid'],$nocmd['artid']);
		$typearticle=DAOarticle::typeArticleByIDart($DB_site2,'FR',$nocmd['artid']);
		$marque=DAOarticle::marqueArticleByIDart($DB_site2,'FR',$nocmd['artid']);
		$garantiesComp=DAOgarantie::loadGarantiesByTypeMarque($DB_site2,$facture['site_code'],'FR',$typearticle['typearticleid'],$marque['marqueid']);
		foreach($garantiesComp as $garantieComp){
			$jscomp["garcomp[".$garantieComp->garantieid."]"]['duree']=$garantieComp->duree;
			$jscomp["garcomp[".$garantieComp->garantieid."]"]['type']=$garantieComp->type_garantie;
			$jscomp["garcomp[".$garantieComp->garantieid."]"]['cumul']=$garantieComp->cumul;
		}

		for($i=1;$i<=2;$i++){
			${"garCompColor".$i}="";
			$allGarantieCompByLigneFac=$DB_site2->query_first("SELECT lfg.*, g.cumul, g.garantie_client_id FROM lignefacturegarantie lfg INNER JOIN garantie g ON g.garantieid=lfg.garantieid WHERE lfg.lignefactureid=".$nocmd['lignefactureid']." AND utilisation_garantie=1 ORDER BY cumul DESC, lfg.duree DESC LIMIT ".($i-1).",".$i);

			$eflgarFourn=$DB_site2->query_first("SELECT efl.* FROM envoi_fournisseur_ligne efl INNER JOIN envoi_fournisseur ef ON(ef.id=efl.efid) WHERE ligne_facture_id=".$nocmd['lignefactureid']." AND destinataire='".$GarantieCompByLigneFac['fournisseur']."' AND destinataire<>''");
			if($eflgarFourn['id']==""){
				$advgar=new adv($DB_site2);
				$result=$advgar->load_from_lignefactureid($nocmd['lignefactureid'],"garantie");
				if($result && $advgar->destinataire==$allGarantieCompByLigneFac['fournisseur']){
					${"tdGarComp".$i}=formatGarantieName($allGarantieCompByLigneFac['dureeReelle'],$allGarantieCompByLigneFac['cumul'],$allGarantieCompByLigneFac['type'],$allGarantieCompByLigneFac['fournisseur'],$allGarantieCompByLigneFac['garantie_client_id']);
					${"tdGarComp".$i}.="<br><a href=\"javascript:warningDelGarantie('".$advgar->id."');\" style=\"color:red;\">Annuler</a>
					<input type=\"hidden\" id=\"garantieidComp".$i."_".$nocmd['lignefactureid']."\" value=\"".$allGarantieCompByLigneFac['garantieid']."\">";
					${"garCompColor".$i}="#99dd99";
					${"compcmd".$i}=true;
				}else{
					${"tdGarComp".$i}="
					<input type=\"hidden\" name=\"oldgarantieid".$i."_".$nocmd['lignefactureid']."\" value=\"".$allGarantieCompByLigneFac['garantieid']."\">
					<input type=\"checkbox\" name=\"chkgar".$i."_".$nocmd['lignefactureid']."\" onclick=\"checkfourn();\" style=\"".($fourncmd || $nofourncmd || $cmdstock?"":"display:none")."\">
					<select id=\"garantieidComp".$i."_".$nocmd['lignefactureid']."\" name=\"newgarantieid".$i."_".$nocmd['lignefactureid']."\" onchange=\"compareGaranties(".$nocmd['lignefactureid'].");checkfourn();\">";
					if($allGarantieCompByLigneFac['id']=="") ${"tdGarComp".$i}.="<option value=\"\">Aucune garantie</option>";
					foreach($garantiesComp as $garantieComp){
						if(in_array($user['paysid'],$garantieComp->pays) && $garantieComp->utilisation_garantie==1){
							$selected="";
							if($garantieComp->garantieid==$allGarantieCompByLigneFac['garantieid']){
								$selected="selected";
								${"compcmd".$i}=false;
							}
							${"tdGarComp".$i}.="<option value=\"".$garantieComp->garantieid."\"".$selected.">".formatGarantieName($garantieComp->duree,$garantieComp->cumul,$garantieComp->type_garantie,$garantieComp->fournisseur,$garantieComp->destinataire_garantie)."</option>";
						}
					}
					${"tdGarComp".$i}.="</select>";
					${"garCompColor".$i}="#FF9999";

				}
			}else{
				${"tdGarComp".$i}="<input type=\"hidden\" id=\"garantieidComp".$i."_".$nocmd['lignefactureid']."\" value=\"".$allGarantieCompByLigneFac['garantieid']."\">"
				.formatGarantieName($allGarantieCompByLigneFac['duree'],$allGarantieCompByLigneFac['cumul'],$allGarantieCompByLigneFac['type'],$allGarantieCompByLigneFac['fournisseur'],$allGarantieCompByLigneFac['garantie_client_id']);
				${"garCompColor".$i}="#99dd99";
				if($allGarantieCompByLigneFac['garantieid']!="") ${"compcmd".$i}=true;
			}
		}
		
		$garclientcolor="#FF9999";
		if($fourncmd)
			if(isset($compcmd1)){
				if($compcmd1){
					if(isset($compcmd2) && $compcmd2)
						$garclientcolor="#99dd99";
					else{
						$garclientcolor="#99dd99";
						$garCompColor2="#99dd99";
					}
				}
			}else{
				$garclientcolor="#99dd99";
				$garCompColor1="#99dd99";
				$garCompColor2="#99dd99";
			}


			$lfinputs="<input type=\"hidden\" name=\"lignefactureid[]\" value=\"".$nocmd['lignefactureid']."\">";

			$jscomp["garclient[".$nocmd['lignefactureid']."]"]['duree']=$lignefacturegarantiecmd['duree'];
			$jscomp["garclient[".$nocmd['lignefactureid']."]"]['type']=$lignefacturegarantiecmd['type'];
			$tdGarClient=formatGarantieName($lignefacturegarantiecmd['duree'],2,$lignefacturegarantiecmd['type'],"",$lignefacturegarantiecmd['garantie_client_id'])." - ".($nocmd['qte']*$lignefacturegarantiecmd['prix'])." ¤ <img id=\"garCheck_".$nocmd['lignefactureid']."\" width=\"20\" align=\"absmiddle\">";

			$jslfid[]=$nocmd['lignefactureid'];

		//pour que le calendrier soit affiché sous le input, fichier calendrier.js -> function getPosition modifier valeur de topValue
			$return.="<tr>
			<td style=\"background-color:".$garclientcolor."\">$img</td>
			<td style=\"background-color:".$garclientcolor."\" title=\"$nocmd[libelle]\">".$nocmd['qte']." ".$nocmd['artcode'].$lfinputs."</td>";

			$return.="<td style=\"background-color:".$garFournColor."\">".$tdGarFourn."</td>
			<td style=\"background-color:".$garCompColor1."\">".$tdGarComp1."</td>
			<td style=\"background-color:".$garCompColor2."\">".$tdGarComp2."</td>
			<td style=\"background-color:".$garclientcolor."\">".$tdGarClient."</td>";
					/*if (!empty($libGarantieFourn)) {
						$return.="<td>$libGarantieFourn</td>
							<td>
								<select style=\"width:120px;\" name=\"garsup1_".$nocmd['lignefactureid']."\" id=\"garsup1_".$nocmd['lignefactureid']."\" $disabledgarsup1 onchange=\"updatePrix(this,'prixgarsup1_".$nocmd['lignefactureid']."','pricehidden1_');activeLbx2(this,'garsup2_".$nocmd['lignefactureid']."','prixgarsup2_".$nocmd['lignefactureid']."');\">$optgarsup1</select>
								<input type=\"text\" style=\"width:30px;\" name=\"prixgarsup1_".$nocmd['lignefactureid']."\" id=\"prixgarsup1_".$nocmd['lignefactureid']."\" value=\"".$prices[$idgarsup1]."\" $disabledgarsup1/>¤
								<input type=\"hidden\" name=\"defautidgarsup1_".$nocmd['lignefactureid']."\" value=\"".$idgarsup1."\"/>
							</td>
							<td>
								<select style=\"width:120px;\" name=\"garsup2_".$nocmd['lignefactureid']."\" id=\"garsup2_".$nocmd['lignefactureid']."\" $disabledgarsup2 onchange=\"updatePrix(this,'prixgarsup2_".$nocmd['lignefactureid']."','pricehidden2_');\">$optgarsup2</select>
								<input type=\"text\" style=\"width:30px;\" name=\"prixgarsup2_".$nocmd['lignefactureid']."\" id=\"prixgarsup2_".$nocmd['lignefactureid']."\" value=\"".$prices[$idgarsup2]."\" $disabledgarsup2/>¤
								<input type=\"hidden\" name=\"defautidgarsup2_".$nocmd['lignefactureid']."\" value=\"".$idgarsup2."\"/>
							</td>
							<td>$libGarantieClient</td>";
								
							    //<td><input type=\"text\" name=\"cmddateGarantie_$nocmd[lignefactureid]\" id=\"cmddateGarantie_$nocmd[lignefactureid]\" value=\"$dateCmdGar\" onclick=\"cmddateSimple(this.id);\" $disableddate size=\"10\"></td>
								$return.="<td style=\"width:50px;\">$linkGarantie</td>";
					} else {
						$return.="<td colspan=\"5\">Produit à commander</td>";
					}*/
					$return.="</tr>";
				} 

				if($DB_site2->num_rows($req)>0)
					$return.="<tr>
				<td colspan=\"3\"></td>
				<td><input id=\"sendgar\" style=\"display:none;\" type=\"submit\" value=\"Commander la garantie\"></td>
				</tr>
				</table>
				</form>";

				foreach($jscomp as $var=>$values){
					$jscompdata.=$var."=new Array();\n";
					foreach($values as $v=>$val)
						$jscompdata.=$var."['".$v."']='".$val."';\n";
				}

				if(count($jslfid)>0)
                                    $jslfidstr="var lfids=new Array('".implode("','",$jslfid)."');\n";
                                else
                                    $jslfidstr="var lfids=new Array();\n";

				$return.=<<<JS
				<script type="text/javascript">
				function updatePrix(obj,inputPriceOld,inputPriceNew)
				{
					var newPrice = '';
					if (obj.value!="null") {
						newPrice = document.getElementById(inputPriceNew+obj.value).value;
					};
					document.getElementById(inputPriceOld).value = newPrice;
				}
				function activeLbx2(obj,lbx2,inputPrice2)
				{
					if (obj.value!="null") {
						document.getElementById(lbx2).disabled=false;
						document.getElementById(inputPrice2).disabled=false;
					} else {
						document.getElementById(lbx2).disabled=true;
						document.getElementById(inputPrice2).disabled=true;
						document.getElementById(lbx2).value="null";
						document.getElementById(inputPrice2).value="";
					};
				}
				function warningDelGarantie(advid){
					if(confirm("Etes vous sur de vouloir annuler l'envoi de cette garantie ?"))
						document.location='facture.php?action=editer&facturid=$facturid&deladvgar='+advid+'#adv';
				}
				function compareGaranties(lignefactureid){
					var garfourn=new Array();
					var garcomp=new Array();
					var garclient=new Array();
					var garCheckImg=new Array();
					garCheckImg[true]="images/coche_verte.svg";
					garCheckImg[false]="images/icon-x.svg";
					$jscompdata

					var garComp1=document.getElementById('garantieidComp1_'+lignefactureid).value;
					var garComp2=document.getElementById('garantieidComp2_'+lignefactureid).value;

					var maxtype=(garfourn[lignefactureid]!=undefined ? parseInt(garfourn[lignefactureid]['type']) : 0);
					if(garComp1!='')
						if(garcomp[garComp1]['cumul']=='2')
							maxtype=parseInt(garcomp[garComp1]['type']);

						if(garComp2!='')
							if(garcomp[garComp2]['cumul']=='2')
								maxtype=parseInt(garcomp[garComp2]['type']);

							var typeOK=maxtype==garclient[lignefactureid]['type'];

							var maxduree=(garfourn[lignefactureid]!=undefined ? parseInt(garfourn[lignefactureid]['duree']) :0);
							if(garComp1!='')
								if(garcomp[garComp1]['cumul']=='1')
									maxduree+=parseInt(garcomp[garComp1]['duree']);
								else
									maxduree=parseInt(garcomp[garComp1]['duree']);

								if(garComp2!='')
									if(garcomp[garComp2]['cumul']=='1')
										maxduree+=parseInt(garcomp[garComp2]['duree']);
									else
										maxduree=parseInt(garcomp[garComp2]['duree']);

									var dureeOK=maxduree>=garclient[lignefactureid]['duree'];

									document.getElementById('garCheck_'+lignefactureid).src=garCheckImg[dureeOK && typeOK];
								}

								function checkfourn(){
									var garfournlist=new Array();
									for(i=0;i<lfids.length;i++){
										if(document.getElementsByName('chkgar1_'+lfids[i])[0] && document.getElementsByName('chkgar1_'+lfids[i])[0].checked) garfournlist.push(document.getElementById('garantieidComp1_'+lfids[i]).value);
										if(document.getElementsByName('chkgar2_'+lfids[i])[0] && document.getElementsByName('chkgar2_'+lfids[i])[0].checked) garfournlist.push(document.getElementById('garantieidComp2_'+lfids[i]).value);
									}

									garfournlist = garfournlist.filter(function (e, i, arr) {
										return arr.lastIndexOf(e) === i;
									});

document.getElementById('sendgar').style.display=(garfournlist.length==1 && garfournlist[0]!=''?"inline":"none");
}

$jslfidstr
for(i=0;i<lfids.length;i++)
	compareGaranties(lfids[i]);

</script>
JS;

	return $return;
}


?>
