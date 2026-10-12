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
<p>Réclamation : rétractation annulation<span class=\"modifstep\">".$sav->steps[1]['utilisateur']." ".($sav->steps[1]['date']>0?date("d/m/Y",$sav->steps[1]['date']):"")."</span></p>
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


?>
<div>
    <form method="post" action="facture.php?action=editer&facturid=<?php echo $facturid; ?>&savid=<?php echo $sav->id; ?>#sav" enctype="multipart/form-data">
        <input type="hidden" name="savid" value="<?php echo $sav->id; ?>">
        <input type="hidden" name="step" value="2">
        <table class="etape">
            <tr>
                <td colspan="2" class="etapetd">Etape 1 : Informations client</td>
                <td class="etapesep">&nbsp;</td>
                <td colspan="2" class="etapetd">Etape 2 : Validation des informations client</td>
            </tr>
            <tr>
                <td colspan="2">Saisie par <?php echo $sav->utilisateur; ?> le <?php echo date("d/m/Y à H:i",$sav->date_creation); ?></td>
                <td class="etapesep"></td>
                <td></td>
            </tr>
            <tr>
                <td>Date livraison</td>
                <td class="client"><?php echo $sav->fields['sav_datelivr']; ?></td>
                <td class="etapesep"></td>
                <td>Date livraison validée sur le site du transporteur</td>
                <td nowrap><?php echo $sav->verif('sav_datelivr',"cal"); ?></td>
            </tr>
            <tr>
                <td>N° du bon de transport</td>
                <td class="client"><?php echo $sav->fields['sav_nbon']; ?></td>
                <td class="etapesep"></td>
                <td>Numéro du bon de transport validé sur le site du transporteur</td>
                <td nowrap><?php echo $sav->verif('sav_nbon'); ?></td>
            </tr>
            <tr>
                <td>Nom du transporteur</td>
                <td class="client"><?php echo $sav->fields['sav_nomtrspt']; ?></td>
                <td class="etapesep"></td>
                <td></td>
                <td nowrap><?php echo $sav->verif('sav_nomtrspt'); ?></td>
            </tr>
            <tr>
                <td>Avez-vous conservé la marchandise ?</td>
                <td class="client"><?php echo $sav->ouinon('sav_marchandise'); ?></td>
                <td class="etapesep"></td>
                <td></td>
                <td nowrap><?php echo $sav->verif('sav_marchandise',"ouinon"); ?></td>
            </tr>
            <tr>
                <td>Avez-vous conservé l'emballage ?</td>
                <td class="client"><?php echo $sav->ouinon('sav_emballage'); ?></td>
                <td class="etapesep"></td>
                <td></td>
                <td nowrap><?php echo $sav->verif('sav_emballage',"ouinon"); ?></td>
            </tr>
            <tr>
                <td>Réserves sur le bon de transport récupéré par le livreur ?</td>
                <td class="client"><?php echo $sav->ouinon('sav_reserve'); ?></td>
                <td class="etapesep"></td>
                <td>Réserves émises par le client confirmées par le transporteur ou par le bon de transport</td>
                <td nowrap><?php echo $sav->verif('sav_reserve',"ouinon"); ?></td>
            </tr>
            <tr>
                <td colspan="2">Si oui, lesquelles :<br><span class="client"><?php echo $sav->fields['sav_reserves']; ?></span></td>
                <td class="etapesep"></td>
                <td colspan="2"><?php echo $sav->textarea("sav_reserves_verif","sav_autosave('sav_reserves_verif')",array("cols"=>50,"rows"=>3)); ?> <?php echo $sav->wait("sav_reserves_verif"); ?></td>
            </tr>
            <tr>
                <td colspan="2">Détail précis de votre demande :<br><span class="client"><?php echo $sav->fields['sav_demande']; ?></span></td>
                <td class="etapesep"></td>
                <td><?php echo $sav->filebox_button(); ?></td>
            </tr>
            <tr>
                <td colspan="2">&nbsp;</td>
                <td class="etapesep"></td>
                <td colspan="2" style="padding: 5px;"><a href="javascript:void(0);" onclick="javascript:mail_enreg(undefined,'<?php echo $rs_facture['mail']; ?>',undefined,162,'<?php echo $user['langue']; ?>', undefined, '<?php echo "&tracking_sav=SAV".$facturid."-".$sav->id; ?>');mail_suivi('suivi_commande',<?php echo $facturid; ?>);mail_services('<?php echo $user['langue']; ?>','<?php echo $user['site_code']; ?>');">Envoyer formulaire et conditions rétractation</a></td>
            </tr>
        </table>
<?php

if($sav->maxstep<2) print "<div style=\"text-align:center;padding:10px;\"><input type=\"submit\" value=\"Passer à l'étape suivante\"></div>";
print "</form>
</div>";

if($sav->maxstep>=2){
  $datelimite="?";
  if($sav->fields['sav_datelivr_verif']!=""){
      $sp=explode("/",$sav->fields['sav_datelivr_verif']);
      $d=mktime(0,0,0,$sp[1],$sp[0],$sp[2])+(14*86400);
      $datelimite=date("d/m/Y",$d);
  }
  print "<div class=\"advgroup\">
  <form method=\"post\">
  <input type=\"hidden\" name=\"savid\" value=\"".$sav->id."\">
  <input type=\"hidden\" name=\"step\" value=\"3\">
  <p>Etape 3 : Conclusion<span class=\"modifstep\">".$sav->steps[3]['utilisateur']." ".($sav->steps[3]['date']>0?date("d/m/Y",$sav->steps[3]['date']):"")."</span></p>
  date limite d'envoi de la lettre = $datelimite<br><br>
  <table>
  <tr><td>Lettre recommandée envoyée dans les 14 jours suivant la livraison ?".$sav->wait("sav_recu7j")."</td><td>".$sav->radio_inputs("sav_recu7j",array("Oui"=>1),"sav_save('sav_recu7j')")."</td><td>".$sav->radio("sav_recu7j",array("Non"=>0),"sav_save('sav_recu7j')")."</td></tr>
  <tr><td>Droit de rétractation ne peut pas s'exercer à cause personnalisation produit".$sav->wait("sav_retracperso")."</td><td>".$sav->radio_inputs("sav_retracperso",array("Oui"=>1),"sav_save('sav_retracperso')")."</td><td>".$sav->radio("sav_retracperso",array("Non"=>0),"sav_save('sav_retracperso')")."</td></tr>
  <tr><td>Droit de rétractation accordé ?".$sav->wait("sav_retracaccord")."</td><td>".$sav->radio_inputs("sav_retracaccord",array("Oui"=>1),"sav_save('sav_retracaccord')")."</td><td>".$sav->radio("sav_retracaccord",array("Non"=>0),"sav_save('sav_retracaccord')")." &nbsp; Motif : ".$sav->text_input("sav_motifretract","sav_autosave('sav_motifretract')").$sav->wait("sav_motifretract")."</td></tr>
  </table>
  <a href=\"javascript:mail_enreg(undefined,'$rs_facture[mail]',undefined,28,'$user[langue]', undefined, '&tracking_sav=SAV".$facturid."-".$sav->id."');mail_suivi('suivi_commande',$facturid);mail_services('$user[langue]','$user[site_code]');\">Confirmation acception retour produit</a>
  <br><a href=\"javascript:mail_enreg(undefined,'$rs_facture[mail]',undefined,29,'$user[langue]',undefined,'&tracking_sav=SAV".$facturid."-".$sav->id."');mail_suivi('suivi_commande',$facturid);mail_services('$user[langue]','$user[site_code]');\">Confirmation refus droit de rétractation client</a>";
  if($sav->maxstep<3) print "<div style=\"text-align:center;padding:10px;\"><input type=\"submit\" value=\"Passer à l'étape suivante\"></div>";
print "</form>
</div>";

}

if($sav->maxstep>=3){
  print "<div class=\"advgroup\">
  <form method=\"post\">
  <input type=\"hidden\" name=\"savid\" value=\"".$sav->id."\">
  <input type=\"hidden\" name=\"step\" value=\"4\">
  <p>Etape 4 : Réception et validation de la marchandise<span class=\"modifstep\">".$sav->steps[4]['utilisateur']." ".($sav->steps[4]['date']>0?date("d/m/Y",$sav->steps[4]['date']):"")."</span></p>
  <table>
  <tr><td>Expédiée par le client le : ".$sav->cal_input("sav_retourmarchandise","sav_calsave")." ".$sav->wait("sav_retourmarchandise")." Reçu le ".$sav->cal_input("sav_retourmarchandiserecu","sav_calsave")." ".$sav->wait("sav_retourmarchandiserecu")."</td></tr>
  <tr><td style=\"color:#990000;font-weight:bold;\">Vérification de la marchandise</td></tr>
  <tr><td>Emballage en parfait état de re-commercialisation (carton, film de protection, polystyrène, ...) ".$sav->wait("sav_retouremballage")."</td><td>".$sav->radio_inputs("sav_retouremballage",array("Oui"=>1),"sav_save('sav_retouremballage')")."</td><td>".$sav->radio("sav_retouremballage",array("Non"=>0),"sav_save('sav_retouremballage')")."</td></tr>
  <tr><td>Produit en parfait état ".$sav->wait("sav_retouretat")."</td><td>".$sav->radio_inputs("sav_retouretat",array("Oui"=>1),"sav_save('sav_retouretat')")."</td><td>".$sav->radio("sav_retouretat",array("Non"=>0),"sav_save('sav_retouretat')")."</td></tr>
  <tr><td>Tous les accessoires sont présents (notice, clayettes, filtres à charbon, supports clayettes, cordons, ... ".$sav->wait("sav_retouraccessoires")."</td><td>".$sav->radio_inputs("sav_retouraccessoires",array("Oui"=>1),"sav_save('sav_retouraccessoires')")."</td><td>".$sav->radio("sav_retouraccessoires",array("Non"=>0),"sav_save('sav_retouraccessoires')")."</td></tr>
  <tr><td>Le produit est tout à fait recommercialisable ? La rétractation est accordée ".$sav->wait("sav_retourcommerc")."</td><td>".$sav->radio_inputs("sav_retourcommerc",array("Oui"=>1),"sav_save('sav_retourcommerc')")."</td><td>".$sav->radio("sav_retourcommerc",array("Non"=>0),"sav_save('sav_retourcommerc')")."</td></tr>
  </table>
  ".$sav->filebox_button()."
  <br><br><a href=\"javascript:mail_enreg(undefined,'$rs_facture[mail]',undefined,30,'$user[langue]',undefined,'&tracking_sav=SAV".$facturid."-".$sav->id."');mail_suivi('suivi_commande',$facturid);mail_services('$user[langue]','$user[site_code]');\">Confirmation réception marchandise bon état - remboursement accepté</a>
  <br><a href=\"javascript:mail_enreg(undefined,'$rs_facture[mail]',undefined,31,'$user[langue]',undefined,'&tracking_sav=SAV".$facturid."-".$sav->id."');mail_suivi('suivi_commande',$facturid);mail_services('$user[langue]','$user[site_code]');\">Confirmation réception marchandise mauvais état - remboursement refusé</a>
  <br><br><a href=\"facture.php?action=creer&createfrom=".$facturid."\" target=\"_blank\">Création nouvelle commande</a> &nbsp; <a href=\"facture.php?action=creer&createfromuser=".$facturid."\" target=\"_blank\">Création avoir</a>";

  ?>
  <br><br>
  <a href="javascript:void(0);" onclick="javascript:mail_enreg(undefined,'<?php echo $rs_facture['mail']; ?>',undefined,81,'<?php echo $user['langue']; ?>', undefined, '<?php echo "&tracking_sav=SAV".$facturid."-".$sav->id; ?>');mail_suivi('suivi_commande',<?php echo $facturid; ?>);mail_services('<?php echo $user['langue']; ?>','<?php echo $user['site_code']; ?>');">Envoyer mail pour envoi chèque cadeau suite rétractation</a>
  <br>
  <?php

  if($sav->maxstep<4) print "<div style=\"text-align:center;padding:10px;\"><input type=\"submit\" value=\"Passer à l'étape suivante\"></div>";
print "</form>
</div>";
}

/*if($sav->maxstep>=4){
print "<div class=\"advgroup\">
  <script language=\"javascript\">
  window.onfocus=function(){
      refreshpj();
  }
  </script>
  <form method=\"post\">
  <input type=\"hidden\" name=\"savid\" value=\"".$sav->id."\">
  <input type=\"hidden\" name=\"step\" value=\"5\">
  <p>Etape 5 : Montage du dossier (facultatif)<span class=\"modifstep\">".$sav->steps[5]['utilisateur']." ".($sav->steps[5]['date']>0?date("d/m/Y",$sav->steps[5]['date']):"")."</span></p>
  <div style=\"margin-top:10px;\">
  Dossier litige n°".$sav->text_input('sav_ndossier',"sav_autosave('sav_ndossier')")." ".$sav->wait("sav_ndossier")."
  </div>
    <br><b>Pièces à joindre au dossier :</b>
  <div>
    ".$sav->checkbox('sav_nbseriemp',"sav_save('sav_nbseriemp')")." Numéro de série mauvais produit (si possible) ".$sav->wait("sav_nbseriemp")."
    <br>".$sav->checkbox('sav_factureclient',"sav_save('sav_factureclient')")." <a href=\"../facture2.php?facturid=$facturid&passe=$rs_facture[passefacture]\">Facture client Ma Cave à Vin</a> ".$sav->wait("sav_factureclient")."
    <br>".$sav->checkbox('sav_lettreclient',"sav_save('sav_lettreclient')")." Lettre/mail explicatif du problème (par le client ou Acilyon) ".$sav->wait("sav_lettreclient")."
    <br>".$sav->checkbox('sav_lettreclient2',"sav_save('sav_lettreclient2')")." Lettre client ".$sav->wait("sav_lettreclient2")."
    <br>".$sav->checkbox('sav_demandeavoir',"sav_save('sav_demandeavoir')")." Facultatif : demander d'avoir (si produit remplacé facturé) ".$sav->wait("sav_demandeavoir")."
    <br>
    <br><a href=\"facture.php?action=creer&createfrom=".$facturid."\" target=\"_blank\">Création nouvelle commande</a> &nbsp; <a href=\"facture.php?action=creer&createfromuser=".$facturid."\" target=\"_blank\">Création avoir</a>
    <br><br><b>Les pièces du dossier :</b> <img src=\"images/refresh.svg\" height=\"24\" align=\"absmiddle\" onclick=\"refreshpj()\"> ".$sav->filebox_button()."
    <div style=\"margin:0px;padding:0px;\" id=\"et6tmppj\">";

    $url="http://".$_SERVER['SERVER_NAME']."/facture2.php?facturid=$facturid&passe=$rs_facture[passefacture]&nopub=1";
    $name="Facture $facturid.pdf";
    print "<br><input type=\"checkbox\" name=\"checkboxfile\" value=\"$facturid\"><input type=\"hidden\" id=\"filename$facturid\" value=\"".$name."\"><input type=\"hidden\" id=\"fileurl$facturid\" value=\"".$url."\"> <a href=\"".$url."\" target=\"_blank\">".$name."</a>";
   
   $requete=$DB_site2->query("SELECT * FROM facture WHERE userid<>".intval($rs_facture['userid'])." AND provenance='sav' AND provenanceid=".$facturid);
   while($fsav=$DB_site2->fetch_array($requete)){
	$url="http://".$_SERVER['SERVER_NAME']."/facture2.php?facturid=$fsav[facturid]&passe=$fsav[passefacture]&nopub=1";
	$name="Facture SAV $fsav[facturid].pdf";
	print "<br><input type=\"checkbox\" name=\"checkboxfile\" value=\"$fsav[facturid]\"><input type=\"hidden\" id=\"filename$fsav[facturid]\" value=\"".$name."\"><input type=\"hidden\" id=\"fileurl$fsav[facturid]\" value=\"".$url."\"> <a href=\"".$url."\" target=\"_blank\">".$name."</a>";
   }

   print $sav->checkbox_files();
   print "</div>
  <div style=\"margin:5px;\"><a href=\"javascript:void(0);\" onclick=\"maildossier(37);\">Dossier à envoyer par mail</a></div>
  <div style=\"margin:10px;font-style:italic;\"> MEMO : dans le cas d'un droit de rétractation, le client doit envoyer une lettre par recommandé sous 7 jours à compter de la date de commande. De plus, c'est à lui d'organiser le rapatriement de la marchandise et le produit doit être envoyé dans son emballage d'origine et en parfait état. Si retour chez fabricant, bien leur demander de vérifier le colis dès réception</div>
  <div>
    Dossier complet envoyé le ".$sav->cal_input('sav_envoidossier',"sav_calsave")." par ".$sav->checkbox('sav_envoimail',"sav_save('sav_envoimail')")." mail &nbsp; ".$sav->checkbox('sav_envoiposte',"sav_save('sav_envoiposte')")." La Poste ".$sav->wait("sav_envoimail").$sav->wait("sav_envoiposte")."
  </div>";
  if($sav->maxstep<5) print "<div style=\"text-align:center;padding:10px;\"><input type=\"submit\" value=\"Passer à l'étape suivante\"></div>";
  print "</form>
  </div>";
}*/

if($sav->maxstep>=4){
  print "<div class=\"advgroup\" style=\"padding: 5px;\">
  <form name=\"et5form\" method=\"post\" id=\"et5form\">
  <input type=\"hidden\" name=\"savid\" value=\"".$sav->id."\">
  <input type=\"hidden\" name=\"step\" value=\"6\">
  <input type=\"hidden\" name=\"date_fin\" value=\"".($sav->date_fin>0?$sav->date_fin:0)."\">
  <p>Etape 5 : Cloture du dossier litige<span class=\"modifstep\">".$sav->steps[6]['utilisateur']." ".($sav->steps[6]['date']>0?date("d/m/Y",$sav->steps[6]['date']):"")."</span></p>
  <font style=\"font-size:12px;color:red;\">ATTENTION : Si la commande a été expédiée, ne pas oublier de créer l'avoir correspondant au montant du remboursement (généralement hors frais de port si facturés)</font><br>";
  $r=$DB_site2->query("SELECT f.facturid,u.raisonsociale,f.valide FROM facture f INNER JOIN utilisateur u ON (u.userid=f.userid) WHERE f.provenance='sav' AND f.provenanceid=$facturid");
  while($jfacture=$DB_site2->fetch_array($r)){
      print "<br>- <a href=\"facture.php?action=editer&facturid=".$jfacture['facturid']."\" target=\"_blank\">Commande n°".$jfacture['facturid']." ".$jfacture['raisonsociale']."</a> ".$status[$jfacture['valide']];
  }
  print "<br><br>Client remboursé le ".$sav->cal_input("sav_daterembourse","sav_calsave")." ".$sav->wait("sav_daterembourse")." montant : ".$sav->nb_input("sav_montantrembourse","sav_autosave('sav_montantrembourse')")." ".$sav->wait("sav_montantrembourse");
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
