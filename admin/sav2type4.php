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
<input type=\"hidden\" name=\"sav_facturid\" value=\"".$facturid."\">
<input type=\"hidden\" name=\"sav_civlivraison\" value=\"".$rs_facture['civilite']."\">
<input type=\"hidden\" name=\"sav_nomlivraison\" value=\"".$rs_facture['nom']."\">
<input type=\"hidden\" name=\"sav_prenomlivraison\" value=\"".$rs_facture['prenom']."\">
<input type=\"hidden\" name=\"sav_raisonsocialelivraison\" value=\"".$rs_facture['raisonsociale']."\">
<input type=\"hidden\" name=\"sav_adresselivraison\" value=\"".$rs_facture['adresse']."\">
<input type=\"hidden\" name=\"sav_cplivraison\" value=\"".$rs_facture['codepostal']."\">
<input type=\"hidden\" name=\"sav_villelivraison\" value=\"".$rs_facture['ville']."\">
<input type=\"hidden\" name=\"sav_payslivraison\" value=\"".$rq_pays[libelle]."\">
<p>Réclamation : manque produit<span class=\"modifstep\">".$sav->steps[1]['utilisateur']." ".($sav->steps[1]['date']>0?date("d/m/Y",$sav->steps[1]['date']):"")."</span></p>
<b>Produits concernés</b>";

$lignes=$DB_site2->query("SELECT * FROM lignefacture WHERE factureid=$rs_facture[facturid]");
  while($ligne=$DB_site2->fetch_array($lignes)){
    $article=$DB_site2->query_first("SELECT artcode_eurocave,garantie_incluse,m.libelle AS marque FROM article a INNER JOIN article_marque am USING(artid) INNER JOIN marque m USING(marqueid) WHERE artid=".$ligne['artid']);
    $optqte="";
    for($i=0;$i<=$ligne['qte'];$i++) $optqte.="<option value=\"$i\"".($sav->lignes[$ligne['lignefactureid']]==$i?" selected":"").">$i</option>";
    print "<br><select name=\"savligne[$ligne[lignefactureid]]\" onchange=\"document.forms.et1.submit();\">$optqte</select> / $ligne[qte] $ligne[libelle] $ligne[artcode] - $article[artcode_eurocave] $article[marque]
    <table id=\"detail$ligne[lignefactureid]\" style=\"border:solid thin black;".($sav->lignes[$ligne['lignefactureid']]>0?"":"display:none;")."\">
    <tr><td colspan=\"4\">Rappel commande passée :</td></tr>";
    $requete=$DB_site2->query("SELECT efl.paht,ef.destinataire,efl.type,ef.assur_comp FROM envoi_fournisseur_ligne efl INNER JOIN envoi_fournisseur ef ON(ef.id=efl.efid) WHERE efl.ligne_facture_id=".$ligne['lignefactureid']);
    while($efl=$DB_site2->fetch_array($requete)){
	switch($efl['type']){
	    case "fourn";
	    if($efl['destinataire']!="0") print "<tr><td>".$ligne['qte']." ".$ligne['artcode']." - ".$article['artcode_eurocave']."</td><td>".$efl['destinataire']."</td><td align=\"right\">".$efl['paht']." &euro; HT</td><td></td></tr>";
	    break;
	    case "trspt":
	    if($efl['destinataire']!="0") print "<tr><td>1 transport</td><td>".$efl['destinataire']."</td><td align=\"right\">".$efl['paht']." &euro; HT</td><td>".($efl['assur_comp']=='1'?"assurance complémentaire":"")."</td></tr>";
	    break;
	    case "sls":
	    if($efl['destinataire']!="0") print "<tr><td>1 installateur</td><td>".$efl['destinataire']."</td><td align=\"right\">".$efl['paht']." &euro; HT</td><td>".($efl['assur_comp']=='1'?"assurance complémentaire":"")."</td></tr>";
	    break;
	}
    }
//     if($article['garantie_incluse']=="") $article['garantie_incluse']="aucune";
//     print "<tr><td colspan=\"2\">Garantie constructeur : ".$article['garantie_incluse']."</td>";
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

print "</form>
</div>";

$photos="<br>";
foreach($sav->files as $fid=>$f){
    if($f['ext']=="jpg" && $f['utilisateur']==""){
	if($f['size']<1048576)
	    $photos.=" <a href=\"sav/".$fid.".".$f['ext']."\" target=\"_blank\"><img src=\"sav/".$fid.".".$f['ext']."\" width=\"120\" title=\"".$f['name']."\"></a>";
	else
	    $photos.=" <a href=\"sav/".$fid.".".$f['ext']."\" target=\"_blank\" class=\"photosav\">".$f['name']."</a>";
    }
}

print "<div>
<form method=\"post\" action=\"facture.php?action=editer&facturid=$facturid&savid=".$sav->id."#sav\" enctype=\"multipart/form-data\">
<input type=\"hidden\" name=\"savid\" value=\"".$sav->id."\">
<input type=\"hidden\" name=\"step\" value=\"2\">
<table class=\"etape\">
<tr><td colspan=\"2\" class=\"etapetd\">Etape 1 : Informations client</td><td class=\"etapesep\">&nbsp;</td><td colspan=\"2\" class=\"etapetd\">Etape 2 : Validation des informations client</td></tr>
<tr><td colspan=\"2\">Saisie par ".$sav->utilisateur." le ".date("d/m/Y à H:i",$sav->date_creation)."</td><td class=\"etapesep\"></td><td></td></tr>
<tr><td>Date livraison</td><td class=\"client\">".$sav->fields['sav_datelivr']."</td><td class=\"etapesep\"></td><td>Date livraison validée sur le site du transporteur</td><td nowrap>".$sav->verif('sav_datelivr',"cal")."</td></tr>
<tr><td>N° du bon de transport</td><td class=\"client\">".$sav->fields['sav_nbon']."</td><td class=\"etapesep\"></td><td>Numéro du bon de transport validé sur le site du transporteur</td><td nowrap>".$sav->verif('sav_nbon')."</td></tr>
<tr><td>Nom du transporteur</td><td class=\"client\">".$sav->fields['sav_nomtrspt']."</td><td class=\"etapesep\"></td><td></td><td nowrap>".$sav->verif('sav_nomtrspt')."</td></tr>

<tr><td>Avez-vous conservé la marchandise ?</td><td class=\"client\">".$sav->ouinon('sav_marchandise')."</td><td class=\"etapesep\"></td><td></td><td nowrap>".$sav->verif('sav_marchandise',"ouinon")."</td></tr>
<tr><td>Avez-vous conservé l'emballage ?</td><td class=\"client\">".$sav->ouinon('sav_emballage')."</td><td class=\"etapesep\"></td><td></td><td nowrap>".$sav->verif('sav_emballage',"ouinon")."</td></tr>
<tr><td>Réserves sur le bon de transport récupéré par le livreur ?</td><td class=\"client\">".$sav->ouinon('sav_reserve')."</td><td class=\"etapesep\"></td><td>Réserves émises par le client confirmées par le transporteur ou par le bon de transport</td><td nowrap>".$sav->verif('sav_reserve',"ouinon")."</td></tr>
<tr><td colspan=\"2\">Si oui, lesquelles :<br><span class=\"client\">".$sav->fields['sav_reserves']."</span></td><td class=\"etapesep\"></td><td colspan=\"2\">".$sav->textarea("sav_reserves-verif","sav_autosave('sav_reserves-verif')",array("cols"=>40,"rows"=>3))." ".$sav->wait("sav_reserves-verif")."</td></tr>
<tr><td colspan=\"2\">Détail précis de votre demande :<br><span class=\"client\">".$sav->fields['sav_demande']."</span></td><td class=\"etapesep\"></td><td></td></tr>
<tr><td colspan=\"2\">Photos envoyées par le client :$photos</td><td class=\"etapesep\"></td><td colspan=\"2\"><br><a href=\"javascript:mail_enreg(undefined,'$rs_facture[mail]',undefined,50,'$user[langue]',undefined,'&tracking_sav=SAV".$facturid."-".$sav->id."');mail_suivi('suivi_commande',$facturid);mail_services('$user[langue]','$user[site_code]');\">Mail de demande de photos pour le dossierAAA</a>";

//print "<br><a href=\"javascript:mail_enreg(undefined,'$rs_facture[mail]',undefined,27,'$user[langue]');mail_suivi('suivi_commande',$facturid);mail_services('$user[langue]','$user[site_code]');\">Mail de demande de lettre/mail réclamation client (manque produit)</a>";

print "<br><br>".$sav->filebox_button()."</td></tr>
</table>";
if($sav->maxstep<2) print "<div style=\"text-align:center;padding:10px;\"><input type=\"submit\" value=\"Passer à l'étape suivante\"></div>";
print "</form>
</div>";

if($sav->maxstep>=2){
  print "<div class=\"advgroup\">
  <form method=\"post\">
  <input type=\"hidden\" name=\"savid\" value=\"$savid\">
  <input type=\"hidden\" name=\"step\" value=\"3\">
  <p>Etape 3 : Analyse du litige par le Service Après Vente<span class=\"modifstep\">".$sav->steps[3]['utilisateur']." ".($sav->steps[3]['date']>0?date("d/m/Y",$sav->steps[3]['date']):"")."</span></p>
  <table>
  <tr><td>Qui est responsable ? <span id=\"sav_responsable-ok\"></span></td><td>".$sav->radio_inputs("sav_responsable",array("Aucun"=>0),"sav_save('sav_responsable')")."</td><td>".$sav->radio("sav_responsable",array("Fournisseur"=>1),"sav_save('sav_responsable')")."</td><td>".$sav->radio("sav_responsable",array("Transporteur"=>2),"sav_save('sav_responsable')")."</td><td>".$sav->radio("sav_responsable",array("Installateur"=>3),"sav_save('sav_responsable')")."</td><td>".$sav->radio("sav_responsable",array("Acilyon"=>4),"sav_save('sav_responsable')")."</td><td>".$sav->radio("sav_responsable",array("Client"=>5),"sav_save('sav_responsable')")."</td></tr>
  <tr><td>Demande hors délai (7jours date de livraison) ".$sav->wait("sav_remplace")."</td><td>".$sav->radio_inputs("sav_remplace",array("Oui"=>1),"sav_save('sav_remplace')")."</td><td>".$sav->radio("sav_remplace",array("Non"=>1),"sav_save('sav_remplace')")."</td><td></td><td></td><td></td><td></td></tr>
  <tr><td>Qui doit payer le remplacement du produit ? <span id=\"sav_remplacement-ok\"></span></td><td>".$sav->radio_inputs("sav_remplacement",array("Aucun"=>0),"sav_save('sav_remplacement')")."</td><td>".$sav->radio("sav_remplacement",array("Fournisseur"=>1),"sav_save('sav_remplacement')")."</td><td>".$sav->radio("sav_remplacement",array("Transporteur"=>2),"sav_save('sav_remplacement')")."</td><td>".$sav->radio("sav_remplacement",array("Installateur"=>3),"sav_save('sav_remplacement')")."</td><td>".$sav->radio("sav_remplacement",array("Acilyon"=>4),"sav_save('sav_remplacement')")."</td><td>".$sav->radio("sav_remplacement",array("Client"=>5),"sav_save('sav_remplacement')")."</td></tr>
  <tr><td>Qui prend en charge les frais de transport liés au remplacement / renvoi du produit ? <span id=\"sav_fraisport-ok\"></span></td><td>".$sav->radio_inputs("sav_fraisport",array("Aucun"=>0),"sav_save('sav_fraisport')")."</td><td>".$sav->radio("sav_fraisport",array("Fournisseur"=>1),"sav_save('sav_fraisport')")."</td><td>".$sav->radio("sav_fraisport",array("Transporteur"=>2),"sav_save('sav_fraisport')")."</td><td>".$sav->radio("sav_fraisport",array("Installateur"=>3),"sav_save('sav_fraisport')")."</td><td>".$sav->radio("sav_fraisport",array("Acilyon"=>4),"sav_save('sav_fraisport)")."</td><td>".$sav->radio("sav_fraisport",array("Client"=>5),"sav_save('sav_fraisport')")."</td></tr>
  <tr><td colspan=\"6\">Conclusion :<br>".$sav->textarea("sav_conclusion","sav_autosave('sav_conclusion')",array("cols"=>70,"rows"=>4))." <span id=\"sav_conclusion-ok\"></span></td></tr>
  </table>
  <div style=\"text-align:center;\"><a href=\"javascript:mail_enreg(undefined,'$rs_facture[mail]',undefined,51,'$user[langue]',undefined,'&tracking_sav=SAV".$facturid."-".$sav->id."');mail_suivi('suivi_commande',$facturid);mail_services('$user[langue]','$user[site_code]');\" style=\"color:red;font-size:14px;\">Mail confirmation ouverture réclamation client manque produit</a></div>";
  if($sav->maxstep==2) print "<div style=\"text-align:center;padding:10px;\"><input type=\"submit\" value=\"Passer à l'étape suivante\"></div>";
  print "</form>
  </div>";
}

if($sav->maxstep>=3){
  print "<div class=\"advgroup\">
  <form method=\"post\">
  <input type=\"hidden\" name=\"savid\" value=\"".$sav->id."\">
  <input type=\"hidden\" name=\"step\" value=\"4\">
  <p>Etape 4 : Prise en charge du litige<span class=\"modifstep\">".$sav->steps[4]['utilisateur']." ".($sav->steps[4]['date']>0?date("d/m/Y",$sav->steps[4]['date']):"")."</span></p>
  <div style=\"width:49%;float:left;border-right:solid thin black;test-align:left;\">
    <b>Dossier client :</b>
    <br><br><a href=\"javascript:mail_enreg(undefined,'$rs_facture[mail]',undefined,46,'$user[langue]',undefined,'&tracking_sav=SAV".$facturid."-".$sav->id."');mail_suivi('suivi_commande',$facturid);mail_services('$user[langue]','$user[site_code]');\">Mail confirmation d'expédition des produits manquants</a>
    <br><br><a href=\"javascript:mail_enreg(undefined,'$rs_facture[mail]',undefined,47,'$user[langue]', undefined, '&tracking_sav=SAV".$facturid."-".$sav->id."');mail_suivi('suivi_commande',$facturid);mail_services('$user[langue]','$user[site_code]');\">Refus expédition des produits manquants</a>
  </div>
  <div style=\"width:50%;float:left;margin-left:2px;text-align:left;\">
    <b>Dossier fournisseur :</b><br>
    <br><a href=\"javascript:mail_enreg(undefined,undefined,undefined,48,'$user[langue]',undefined,'&tracking_sav=SAV".$facturid."-".$sav->id."');mail_suivi('suivi_commande',$facturid);mail_services('$user[langue]','$user[site_code]');\">Mail réclamation fournisseur</a>
    <br><a href=\"javascript:mail_enreg(undefined,undefined,undefined,49,'$user[langue]',undefined,'&tracking_sav=SAV".$facturid."-".$sav->id."');mail_suivi('suivi_commande',$facturid);mail_services('$user[langue]','$user[site_code]');\">Relance réclamation fournisseur</a>
    <br><br>Prise en charge du litige : ".$sav->radio_inputs("sav_litigeaccept",array("Oui"=>1,"Non"=>0),"sav_save('sav_litigeaccept')")." ".$sav->wait("sav_litigeaccept")."
    <br>Commentaires :".$sav->wait("sav_motifrefus")."<br>".$sav->textarea("sav_motifrefus","sav_autosave('sav_motifrefus')",array("cols"=>40,"rows"=>3))."
    <br><br>dossier litige n°".$sav->text_input('sav_ndossier',"sav_autosave('sav_ndossier')").$sav->wait("sav_ndossier")."
  </div>
  <div style=\"clear:both;\"></div>";

  if($sav->maxstep==3) print "<div style=\"text-align:center;padding:10px;\"><input type=\"submit\" value=\"Passer à l'étape suivante\"></div>";
  print "</form>
  </div>";

}

foreach($sav->lignes as $ligneid=>$qte){
   $table="<table><tr><td>Article</td><td>Fournisseur</td><td>Prix</td><td>Demande<br>avoir</td><td>Info compta</td></tr>";
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
     
      $table.="<tr><td>".$efl['artcode']."</td><td>".$efl['destinataire']."</td><td>".round($efl['qte']*$efl['paht'],2)."</td><td>$icon</td><td>".$info."</td></tr>";
      
   }
   $table.="</table>";
}

if($sav->maxstep>=4){
  print "<div class=\"advgroup\">
  <script language=\"javascript\">
  window.onfocus=function(){
      refreshpj();
  }
  </script>
  <form method=\"post\">
  <input type=\"hidden\" name=\"savid\" value=\"".$sav->id."\">
  <input type=\"hidden\" name=\"step\" value=\"5\">
  <p>Etape 5 : Montage du dossier litige<span class=\"modifstep\">".$sav->steps[5]['utilisateur']." ".($sav->steps[5]['date']>0?date("d/m/Y",$sav->steps[5]['date']):"")."</span></p>
  <div style=\"width:49%;float:left;border-right:solid thin black;test-align:left;\">
    ".$sav->checkbox("sav_pasouverture","sav_save('sav_pasouverture')")." Pas d'ouverture de dossier litige
    <br><br><a href=\"facture.php?action=creer&createfrom=".$facturid."\" target=\"_blank\">Création nouvelle commande</a> &nbsp; <a href=\"facture.php?action=creer&createfromuser=".$facturid."\" target=\"_blank\">Création avoir</a>
    <br><b>Les pièces du dossier :</b> <img src=\"images/refresh.svg\" height=\"24\" align=\"absmiddle\" onclick=\"refreshpj()\"> ".$sav->filebox_button()."
    <div style=\"margin:0px;padding:0px;\" id=\"et6tmppj\">";
   
    $url="http://".$_SERVER['SERVER_NAME']."/facture2.php?facturid=$facturid&passe=$rs_facture[passefacture]&nopub=1";
    $name="Facture $facturid.pdf";
    print "<br><input type=\"checkbox\" name=\"checkboxfile\" value=\"$facturid\"><input type=\"hidden\" id=\"filename$facturid\" value=\"".$name."\"><input type=\"hidden\" id=\"fileurl$facturid\" value=\"".$url."\"> <a href=\"".$url."\" target=\"_blank\">".$name."</a>";
    
    
    $requete=$DB_site2->query("SELECT * FROM facture WHERE userid<>".intval($rs_facture['userid'])." AND provenance='stat/antispy.phpsav' AND provenanceid=".$facturid);
    while($fsav=$DB_site2->fetch_array($requete)){
	  $url="http://".$_SERVER['SERVER_NAME']."/facture2.php?facturid=$fsav[facturid]&passe=$fsav[passefacture]&nopub=1";
	  $name="Facture SAV $fsav[facturid].pdf";
	  print "<br><input type=\"checkbox\" name=\"checkboxfile\" value=\"$fsav[facturid]\"><input type=\"hidden\" id=\"filename$fsav[facturid]\" value=\"".$name."\"><input type=\"hidden\" id=\"fileurl$fsav[facturid]\" value=\"".$url."\"> <a href=\"".$url."\" target=\"_blank\">".$name."</a>";
    }

    print $sav->checkbox_files();
    print "</div>
    <div style=\"margin:5px;\"><a href=\"javascript:void(0);\" onclick=\"maildossier(36);\">Dossier à envoyer par mail</a></div>
    <div style=\"margin:10px;font-style:italic;\">MEMO : le renvoi des produits doit être effectué à la charge du fournisseur, ou facturé ultérieurement. S'il faut ramasser le mauvais produit expédier pour le rapatrier chez le fabricant, alors il faut aussi se faire rembourser le transport.</div>
    <div>Si un nouveau produit a été réexpédié à nos frais, il ne faut pas oublier de faire une demande d'avoir sur le(s) produit(s) concerné(s) dans le module ci-dessus Récapitulatif commande.</div>
    <br>Dossier complet envoyé le ".$sav->cal_input('sav_envoidossier',"sav_calsave")." par ".$sav->checkbox('sav_envoimail',"sav_save('sav_envoimail')")." mail &nbsp; ".$sav->checkbox('sav_envoiposte',"sav_save('sav_envoiposte')")." La Poste ".$sav->wait("sav_envoimail").$sav->wait("sav_envoiposte")."
  </div>
  <div style=\"width:50%;float:left;margin-left:2px;text-align:left;\">
    $table
  </div>
  <div style=\"clear:both;\"></div>";
  if($sav->maxstep==4) print "<div style=\"text-align:center;padding:10px;\"><input type=\"submit\" value=\"Passer à l'étape suivante\"></div>";
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
  <div>Analyse financière du dossier litige :</div>
  <div style=\"width:49%;float:left;border-right:solid thin black;\">
    <b>Du coté du client</b><br>
    <br>".$savfacture['user']."
    <br>Confirmation client que le produit manquant a été reçu<br>le : ".$sav->cal_input('sav_daterepare',"sav_calsave")." <span id=\"sav_daterepare-ok\"></span>
    <br>Confirmation qu'un nouveau produit a été renvoyé au client<br>le : ".$sav->cal_input("sav_produitrenvoye","sav_calsave")."
  </div>
  <div style=\"width:50%;float:left;margin-left:2px;\">
    <b>Du coté fournisseur</b><br>
    <br>".$savfacture['fourn']."
  </div>
  <div style=\"clear:both;\"></div>
  <br><br><font style=\"color:#990000;font-weight:bold;\">Avant de clôturer un dossier, s'assurer que :</font>
	<table>
	<tr><td>Le(s) produit(s) ne nous ont pas été facturé(s) plusieurs fois</td><td>".$sav->checkbox("sav_verif1","sav_save('sav_verif1');sumverif(3)")."</td><td><span id=\"sav_verif1-ok\"></span><span id=\"sav_verif1-date\">".$sav->modiffield('sav_verif1')."</span></td></tr>
	<tr><td>Les frais liés à la réexpédition de la marchandise ont été remboursés ou fait l'objet d'un avoir</td><td>".$sav->checkbox("sav_verif2","sav_save('sav_verif2');sumverif(3)")."</td><td><span id=\"sav_verif2-ok\"></span><span id=\"sav_verif2-date\">".$sav->modiffield('sav_verif2')."</span></td></tr>
	</table>";

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