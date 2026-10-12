<?php

// print $sav->filebox();

// verification qui est l'utilisateur admin connecté
$authModifType = ($_COOKIE['admin_username']=="tanguy" || $_COOKIE['admin_username']=="philippe" || $_COOKIE['admin_username']=="sylvain" || $_COOKIE['admin_username']=="qualite");

// permet de changer le type de litige, pas de refresh pour le moment car la les pages de modification sav2type0.php et sav2type6.php sont identiques pour conserver les données
if ($authModifType){
  print "-<p><b>Réclamation :</b> avarie<span class=\"modifstep\">".$sav->steps[1]['utilisateur']." ".($sav->steps[1]['date']>0?date("d/m/Y",$sav->steps[1]['date']):"")."</span></p>";

  print  "<form method=\"post\">
          <input type=\"hidden\" name=\"savid\" value=\"".$sav->id."\">
          <table>
            <tr>
              <td>Choix/Changement de litige :  ".$sav->wait("sav_typelitige")."</td><td>".$sav->radio_inputs("sav_typelitige",array("Avarie transport"=>0),"sav_save('sav_typelitige')")."</td>
              <td colspan=\"2\">".$sav->radio("sav_typelitige",array("Avarie occulte"=>6),"sav_save('sav_typelitige')")."</td>
            </tr>
          </table>";
  print "</form>";
} else
  print "-<p><b>Réclamation avarie transport :</b><span class=\"modifstep\">".$sav->steps[1]['utilisateur']." ".($sav->steps[1]['date']>0?date("d/m/Y",$sav->steps[1]['date']):"")."</span></p>";

print "<a name=\"sav\"></a>
<div class=\"advgroup\">
<form name=\"et1\" action=\"facture.php?action=editer&facturid=$facturid&savid=".$sav->id."#sav\" method=\"post\">
<input type=\"hidden\" name=\"savid\" value=\"".$sav->id."\">
<input type=\"hidden\" name=\"step\" value=\"1\">
<input type=\"hidden\" name=\"sav_nom\" value=\"$user[nom]\">
<input type=\"hidden\" name=\"sav_prenom\" value=\"$user[prenom]\">
<input type=\"hidden\" name=\"sav_civ\" value=\"".$user['civilite']."\">
<input type=\"hidden\" name=\"sav_facturid\" value=\"".$facturid."\">";

print "<b>Produits concernés</b>";

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
	print "<td colspan=\"2\">Garantie sérénité : ".$caract['libelle_caractval']."</td>";

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

$fichiers="<br>";
foreach($sav->files as $fid=>$f){
    if($f['utilisateur']!=""){
	if(($f['ext']=="jpg" || $f['ext']=="png") && $f['size']<1048576)
	    $fichiers.=" <a href=\"sav/".$fid.".".$f['ext']."\" target=\"_blank\"><img src=\"sav/".$fid.".".$f['ext']."\" width=\"120\" title=\"".$f['name']."\"></a>";
	else
	    $fichiers.=" <a href=\"sav/".$fid.".".$f['ext']."\" target=\"_blank\" class=\"photosav\">".$f['name']."</a>";
    }
}

print "<div>
<form action=\"facture.php?action=editer&facturid=$facturid&savid=".$sav->id."#sav\" method=\"post\" enctype=\"multipart/form-data\">
<input type=\"hidden\" name=\"savid\" value=\"".$sav->id."\">
<input type=\"hidden\" name=\"step\" value=\"2\">
<table class=\"etape\">
<tr><td colspan=\"2\" class=\"etapetd\">Etape 1 : Informations client</td><td class=\"etapesep\">&nbsp;</td><td colspan=\"2\" class=\"etapetd\">Etape 2 : Validation des informations client</td></tr>
<tr><td colspan=\"2\">Saisie par ".$sav->utilisateur." le ".date("d/m/Y à H:i",$sav->date_creation)."</td><td class=\"etapesep\"></td><td></td></tr>
<tr><td>Date livraison</td><td class=\"client\">".$sav->fields['sav_datelivr']."</td><td class=\"etapesep\"></td><td>Date livraison validée sur le site du transporteur</td><td nowrap>".$sav->verif('sav_datelivr',"cal")."</td></tr>
<tr><td>N° du bon de transport</td><td class=\"client\">".$sav->fields['sav_nbon']."</td><td class=\"etapesep\"></td><td>Numéro du bon de transport validé sur le site du transporteur</td><td nowrap>".$sav->verif('sav_nbon')."</td></tr>
<tr><td>Nom du transporteur</td><td class=\"client\">".$sav->fields['sav_nomtrspt']."</td><td class=\"etapesep\"></td><td></td><td nowrap>".$sav->verif('sav_nomtrspt')."</td></tr>
<tr><td>N° de série de l'appareil</td><td class=\"client\">".$sav->fields['sav_nbserie']."</td><td class=\"etapesep\"></td><td>Facultatif</td><td nowrap>".$sav->verif('sav_nbserie')."</td></tr>
<tr><td>Avez-vous conservé la marchandise ?</td><td class=\"client\">".$sav->ouinon('sav_marchandise')."</td><td class=\"etapesep\"></td><td></td><td nowrap>".$sav->verif('sav_marchandise',"ouinon")."</td></tr>
<tr><td>Avez-vous conservé l'emballage ?</td><td class=\"client\">".$sav->ouinon('sav_emballage')."</td><td class=\"etapesep\"></td><td></td><td nowrap>".$sav->verif('sav_emballage',"ouinon")."</td></tr>
<tr><td>Réserves sur le bon de transport récupéré par le livreur ?</td><td class=\"client\">".$sav->ouinon('sav_reserve')."</td><td class=\"etapesep\"></td><td>Réserves émises par le client confirmées par le transporteur ou par le bon de transport</td><td nowrap>".$sav->verif('sav_reserve',"ouinon")."</td></tr>
<tr><td colspan=\"2\">Si oui, lesquelles :<br><span class=\"client\">".$sav->fields['sav_reserves']."</span></td><td class=\"etapesep\"></td><td colspan=\"2\">".$sav->textarea("sav_reserves_verif","sav_autosave('sav_reserves_verif')",array("cols"=>50,"rows"=>3))." ".$sav->wait("sav_reserves_verif")."</td></tr>
<tr><td colspan=\"2\">Détail précis de votre demande :<br><span class=\"client\">".$sav->fields['sav_demande']."</span></td><td class=\"etapesep\"></td><td></td></tr>
<tr><td colspan=\"2\">Photos envoyées par le client :$photos</td><td class=\"etapesep\"></td><td colspan=\"2\"><a href=\"javascript:mail_enreg(undefined,'$rs_facture[mail]',undefined,15,'$user[langue]','$user[site_code]','&tracking_sav=SAV".$facturid."-".$sav->id."');mail_suivi('suivi_commande',$facturid);mail_services('$user[langue]','$user[site_code]');\">Mail de demande de photos pour le dossier</a><br><br>".$sav->filebox_button()."</td></tr>
</table>";
if($sav->maxstep<2) print "<div style=\"text-align:center;padding:10px;\"><input type=\"submit\" value=\"Passer à l'étape suivante\"></div>";
print "</form>
</div>";

if($sav->maxstep>=2){
  print "<div class=\"advgroup\">
  <form method=\"post\">
  <input type=\"hidden\" name=\"savid\" value=\"".$sav->id."\">
  <input type=\"hidden\" name=\"step\" value=\"3\">
  <p>Etape 3 : Analyse des responsabilités par le SAV<span class=\"modifstep\">".$sav->steps[3]['utilisateur']." ".($sav->steps[3]['date']>0?date("d/m/Y",$sav->steps[3]['date']):"")."</span></p>
  <table>
  <tr><td>Type d'avarie ".$sav->wait("sav_typeavarie")."</td><td>".$sav->radio_inputs("sav_typeavarie",array("Transporteur"=>1),"sav_save('sav_typeavarie')")."</td><td>".$sav->radio("sav_typeavarie",array("Installateur"=>2),"sav_save('sav_typeavarie')")."</td><td colspan=\"2\">".$sav->radio("sav_typeavarie",array(""=>3),"sav_save('sav_typeavarie')")." Avarie occulte (responsabilité du fabricant)<br>Avarie occulte = produit réceptionné abîmé avec des dommages ne pouvant pas être causés pendant le transport.<br>Photos impératives</td><td >".$sav->radio("sav_typeavarie",array(""=>4),"sav_save('sav_typeavarie')")." Analyse en cours</td></tr>
  <tr><td>Qui est responsable ? ".$sav->wait("sav_responsable")."</td><td colspan=\"3\">".$sav->select("sav_responsable",$fournisseurs,"sav_save('sav_responsable')")."</td><td></td><td>".$sav->radio("sav_responsable",array("Client"=>4),"sav_save('sav_responsable')")."</td><td>".$sav->radio("sav_responsable",array("Acilyon"=>5),"sav_save('sav_responsable')")."</td></tr>
  <tr><td>Résolution à l'amiable ? ".$sav->wait("sav_amiable")."</td><td>".$sav->radio_inputs("sav_amiable",array("Non"=>0),"sav_save('sav_amiable')")."</td><td>".$sav->radio("sav_amiable",array("Oui"=>1),"sav_save('sav_amiable')")."</td><td>Montant : ".$sav->nb_input('sav_montantamiable',"sav_autosave('sav_montantamiable')")." ".$sav->wait("sav_montantamiable")."</td><td colspan=\"2\"></td></tr>
  <tr><td>Qui doit payer le remplacement du produit / remise commerciale ".$sav->wait("sav_remplacement")."</td><td nowrap>".$sav->radio_inputs("sav_remplacement",array("Transporteur"=>1),"sav_save('sav_remplacement')")."</td><td nowrap>".$sav->radio("sav_remplacement",array("Installateur"=>2),"sav_save('sav_remplacement')")."</td><td>".$sav->radio("sav_remplacement",array("Fournisseur"=>3),"sav_save('sav_remplacement')")."</td><td>".$sav->radio("sav_remplacement",array("Client"=>4),"sav_save('sav_remplacement')")."</td><td>".$sav->radio("sav_remplacement",array("Acilyon"=>5),"sav_save('sav_remplacement')")."</td></tr>
  <tr><td>Qui doit payer les frais de transport liés au remplacement du produit ? ".$sav->wait("sav_fraisport")."</td><td nowrap>".$sav->radio_inputs("sav_fraisport",array("Transporteur"=>1),"sav_save('sav_fraisport')")."</td><td nowrap>".$sav->radio("sav_fraisport",array("Installateur"=>2),"sav_save('sav_fraisport')")."</td><td>".$sav->radio("sav_fraisport",array("Fournisseur"=>3),"sav_save('sav_fraisport')")."</td><td>".$sav->radio("sav_fraisport",array("Client"=>4),"sav_save('sav_fraisport)")."</td><td>".$sav->radio("sav_fraisport",array("Acilyon"=>5),"sav_save('sav_fraisport')")."</td></tr>
  <tr><td>Commentaire :</td><td colspan=\"5\">".$sav->textarea("sav_conclusion","sav_autosave('sav_conclusion')",array("cols"=>70,"rows"=>4))." <span id=\"sav_conclusion-ok\"></span></td></tr>
  </table>
  <div style=\"text-align:center;\"><a href=\"javascript:mail_enreg(undefined,'$rs_facture[mail]',undefined,14,'$user[langue]',undefined,'&tracking_sav=SAV".$facturid."-".$sav->id."');mail_suivi('suivi_commande',$facturid);mail_services('$user[langue]','$user[site_code]');\" style=\"color:red;font-size:14px;\">Mail confirmation ouverture réclamation client</a></div>";
  if($sav->maxstep==2) print "<div style=\"text-align:center;padding:10px;\"><input type=\"submit\" value=\"Passer à l'étape suivante\"></div>";
  print "</form>
  </div>";
}

if($sav->maxstep>=3){

    ?>
    <div class="advgroup">
        <form method="post" enctype="multipart/form-data">
        <input type="hidden" name="savid" value="<?php echo $sav->id; ?>">
        <input type="hidden" name="step" value="4">
        <p>Etape 4 : Ouverture du dossier litige<span class="modifstep"><?php echo $sav->steps[4]['utilisateur']." ".($sav->steps[4]['date'] > 0 ? date("d/m/Y",$sav->steps[4]['date']) : ""); ?></span></p>

        <div style="width:49%;float:left;border-right:solid thin black;">
            <b>Dossier litige client :</b>
            <br><br>
            Si le client n'a pas émit de réserves à la livraison, il faut lui demander un courrier écrit sous 3 jours :
            <br><br>
            <a href="javascript:void(0);" onclick="javascript:mail_enreg(undefined, '<?php echo $rs_facture['mail']; ?>', undefined, 16, '<?php echo $user['langue']; ?>', undefined, '<?php echo "&tracking_sav=SAV".$facturid."-".$sav->id; ?>'); mail_suivi('suivi_commande',<?php echo $facturid; ?>);mail_services('<?php echo $user['langue']; ?>','<?php echo $user['site_code']; ?>');">Mail de demande de lettre recommandée sous 3 jours</a>
            <!--<br>
            <a href="javascript:mail_enreg(undefined,'<?php echo $rs_facture['mail']; ?>',undefined,17,'<?php echo $user['langue']; ?>');mail_suivi('suivi_commande',<?php echo $facturid; ?>);mail_services('<?php echo $user['langue']; ?>','<?php echo $user['site_code']; ?>');">Mail de demande de lettre/mail réclamation client</a>-->
            <br><br>
            <b>Lettre client envoyée</b>
            <div>
                <?php echo $sav->checkbox("sav_lettrem3","sav_save('sav_lettrem3')"); ?> Lettre réclamation client envoyée sous 3 jours
                <br>
                <?php echo $sav->checkbox("sav_lettrehd","sav_save('sav_lettrehd')"); ?> Lettre réclamation client envoyée hors délai
                <br>
                <!--<?php echo $sav->checkbox("sav_reclamacilyon","sav_save('sav_reclamacilyon')"); ?> Lettre réclamation client envoyée par Acilyon
                <br>(Lorsque le client a refusé la cave)-->
            </div>
            
            <hr>

            <br><br>
            <a href="javascript:mail_enreg(undefined,'<?php echo $rs_facture['mail']; ?>',undefined,18,'<?php echo $user['langue']; ?>', undefined, '<?php echo "&tracking_sav=SAV".$facturid."-".$sav->id; ?>');mail_suivi('suivi_commande',<?php echo $facturid; ?>);mail_services('<?php echo $user['langue']; ?>','<?php echo $user['site_code']; ?>');">Générer mail confirmation prise en charge litige au client - Remplacement produit</a>
            <br>
            <a href="javascript:mail_enreg(undefined,'<?php echo $rs_facture['mail']; ?>',undefined,38,'<?php echo $user['langue']; ?>', undefined, '<?php echo "&tracking_sav=SAV".$facturid."-".$sav->id; ?>');mail_suivi('suivi_commande',<?php echo $facturid; ?>);mail_services('<?php echo $user['langue']; ?>','<?php echo $user['site_code']; ?>');">Générer mail confirmation prise en charge litige au client - Remise commerciale</a>
            
            <hr>

            <br>
            Le client n'a pas émit de réserve à la livraison et n'a pas envoyé de lettre recommandée : aucune suite à donner à son dossier
            <br><br>
            <a href="javascript:mail_enreg(undefined,'<?php echo $rs_facture['mail']; ?>',undefined,39,'<?php echo $user['langue']; ?>', undefined, '<?php echo "&tracking_sav=SAV".$facturid."-".$sav->id; ?>');mail_suivi('suivi_commande',<?php echo $facturid; ?>);mail_services('<?php echo $user['langue']; ?>','<?php echo $user['site_code']; ?>');">Générer mail refus de prise en charge litige</a>
            <br><br>
            <center><?php echo $sav->filebox_button(); ?></center>
        </div>

        <div style="width:50%;float:left;margin-left:2px;">
            <b>Dossier litige fournisseur :</b>
            <br><br>
            <a href="javascript:mail_enreg(undefined,undefined,undefined,32,'<?php echo $user['langue']; ?>', undefined, '<?php echo "&tracking_sav=SAV".$facturid."-".$sav->id; ?>');mail_suivi('suivi_commande',<?php echo $facturid; ?>);mail_services('<?php echo $user['langue']; ?>','<?php echo $user['site_code']; ?>');">Générer mail réclamation transporteur</a>
            <br>
            <a href="javascript:mail_enreg(undefined,undefined,undefined,40,'<?php echo $user['langue']; ?>',undefined,'<?php echo "&tracking_sav=SAV".$facturid."-".$sav->id; ?>');mail_suivi('suivi_commande',<?php echo $facturid; ?>);mail_services('<?php echo $user['langue']; ?>','<?php echo $user['site_code']; ?>');">Relance mail réclamation transporteur</a>
            
            <hr>
            
            <br>
            <font style="color:red;font-weight:bold;">Avarie occulte : Il est impératif de joindre au mail des photos illustrant le problème.</font>
            <br><br>
            <a href="javascript:mail_enreg(undefined,undefined,undefined,41,'<?php echo $user['langue']; ?>',undefined, '<?php echo "&tracking_sav=SAV".$facturid."-".$sav->id; ?>');mail_suivi('suivi_commande',<?php echo $facturid; ?>);mail_services('<?php echo $user['langue']; ?>','<?php echo $user['site_code']; ?>');">Générer mail réclamation avarie occulte fournisseur</a>
            <br>
            <a href="javascript:mail_enreg(undefined,undefined,undefined,42,'<?php echo $user['langue']; ?>', undefined, '<?php echo "&tracking_sav=SAV".$facturid."-".$sav->id; ?>');mail_suivi('suivi_commande',<?php echo $facturid; ?>);mail_services('<?php echo $user['langue']; ?>','<?php echo $user['site_code']; ?>');">Relance mail réclamation avarie occulte fournisseur</a>
            
            <hr>

            <br><br>
            Litige accepté par fournisseur :
            <br>
            <?php echo $sav->radio_inputs("sav_litigeaccept",array("Oui"=>1,"Non"=>0),"sav_save('sav_litigeaccept')"); ?> <?php echo $sav->wait("sav_litigeaccept"); ?>
            <br>
            Commentaires :<?php echo $sav->wait("sav_motifrefus"); ?><br><?php echo $sav->textarea("sav_motifrefus","sav_autosave('sav_motifrefus')",array("cols"=>40,"rows"=>3)); ?>
            <br><br>
            Dossier litige n°<?php echo $sav->text_input('sav_ndossier',"sav_autosave('sav_ndossier')"); ?> <?php echo $sav->wait("sav_ndossier"); ?> <?php echo $sav->cal_input('sav_dateouvre',"sav_calsave"); ?> <?php echo $sav->wait("sav_dateouvre"); ?>

            <br><br>
            
            <a href="javascript:mail_enreg(undefined,undefined,undefined,159,'<?php echo $user['langue']; ?>', undefined, '<?php echo "&tracking_sav=SAV".$facturid."-".$sav->id; ?>');mail_suivi('suivi_commande',<?php echo $facturid; ?>);mail_services('<?php echo $user['langue']; ?>','<?php echo $user['site_code']; ?>');">Générer mail attestation irréparabilité</a>

        </div>
        <div style="clear:both;"></div>
    <?php

  if($sav->maxstep==3) print "<div style=\"text-align:center;padding:10px;\"><input type=\"submit\" value=\"Passer à l'étape suivante\"></div>";
  "</form>
  </div>";
}

$savresponsable=$DB_site2->query_first("SELECT * FROM fournisseur WHERE societe='".addslashes($sav->fields['sav_responsable'])."' AND type='savclient'");
if($savresponsable['id']=="")
  $adresseresponsable="Non configuré";
else
  $adresseresponsable=$savresponsable['societe']."<br>".$savresponsable['adresse1']."<br>".$savresponsable['cp']." ".$savresponsable['ville'];

$nofacture=true;
foreach($sav->lignes as $ligneid=>$qte){
   $table="<table><tr><td>Article</td><td>Fournisseur</td><td>Prix</td><td>Demande<br>avoir</td><td>Info compta</td></tr>";
   $sql="";
   if($sav->fields['sav_responsable']!="") $sql=" AND ef.destinataire='".mysqli_real_escape_string($DB_site2->link_id,$sav->fields['sav_responsable'])."'";
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
  <form method=\"post\" enctype=\"multipart/form-data\">
  <input type=\"hidden\" name=\"savid\" value=\"".$sav->id."\">
  <input type=\"hidden\" name=\"step\" value=\"5\">
  <p>Etape 5 : Montage du dossier litige<span class=\"modifstep\">".$sav->steps[5]['utilisateur']." ".($sav->steps[5]['date']>0?date("d/m/Y",$sav->steps[5]['date']):"")."</span></p>
  <div style=\"width:49%;float:left;border-right:solid thin black;\">
      ".$sav->checkbox("sav_pasouverture","sav_save('sav_pasouverture')")." Pas d'ouverture de dossier litige
      <br><br><b>Pièces à joindre au dossier :</b>
	<br>".$sav->checkbox("sav_factureacilyon","sav_save('sav_factureacilyon')")." Facture Acilyon <span id=\"sav_factureacilyon-ok\"></span>
	<br>".$sav->checkbox('sav_facturefourn',"sav_save('sav_facturefourn')")." Copie facture fournisseur <span id=\"sav_facturefourn-ok\"></span>
	<br>".$sav->checkbox('sav_factureclient',"sav_save('sav_factureclient')")." <a href=\"../facture2.php?facturid=$facturid&passe=$rs_facture[passefacture]\">Facture client Ma Cave à Vin</a> <span id=\"sav_factureclient-ok\"></span>
	<br>".$sav->checkbox('sav_lettreclient',"sav_save('sav_lettreclient')")." Lettre client <span id=\"sav_lettreclient-ok\"></span>
	<br>".$sav->checkbox('sav_attestation',"sav_save('sav_attestation')")." Facultatif : attestation sur l'honneur (en cas perte colis) <span id=\"sav_attestation-ok\"></span>
	<br>".$sav->checkbox('sav_advalorem',"sav_save('sav_advalorem')")." Facultatif : copie valeur AD VALOREM <span id=\"sav_advalorem-ok\"></span>
	<br>
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
      <br><a href=\"javascript:maildossier(33);\">Dossier à envoyer par mail</a>
      <br><br>Dossier à envoyer par la Poste à l'adresse facturation :<br>$adresseresponsable
      <br>Dossier complet envoyé le ".$sav->cal_input("sav_envoidossier","sav_calsave")."
      <br>par ".$sav->checkbox('sav_envoimail',"sav_save('sav_envoimail');sav_save('sav_envoidossier')")." mail &nbsp; ".$sav->checkbox('sav_envoiposte',"sav_save('sav_envoiposte');sav_save('sav_envoidossier')")." La Poste";
      if(file_exists("sfg_pdf/".$facturid.".pdf")) print "<br><br><center><a href=\"javascript:annulesfg($facturid)\" style=\"color:red;font-weight:bold;\">Annuler le contrat SFG</a></center>";
  print "</div>
  <div style=\"width:50%;float:left;margin-left:2px;\">
      Dans le cas d'un remplacement du produit ou d'une remise commerciale, il faut créer une nouvelle commande et facturer le transporteur / installateur / fournisseur du produit à remplacer ou de la remise commerciale.
      <br><a href=\"facture.php?action=creer&createfrom=".$facturid."\" target=\"_blank\">Création nouvelle commande</a>
      <br><br>Si remise commerciale, il faut également créer l'avoir correspondant au nom du client du montant de la remise commerciale convenue
      <br><a href=\"facture.php?action=creer&createfromuser=".$facturid."\" target=\"_blank\">Création avoir</a>
      <br>
      <div style=\"margin-top:10px;margin-bottom:10px;\">
      <font color=\"#DD6666\">ATTENTION NE PAS OUBLIER DE faire la demande d'avoir transport dans le tableau ci dessous si facture transport non enregistrée</font>
	$table
      <br><font color=\"#DD6666\">Ou d'ajouter cet avoir à la liste des avoirs en attente si la facture a déjà été enregistrée (module demande d'avoir)</font>

      </div>
      <font color=\"#DD6666\">Si avarie occulte, il faut demander un avoir sur le produit au fabricant</font>
  </div>
  <div style=\"clear:both;\"></div>
      <div style=\"margin:10px;font-style:italic;\">MEMO : Il est impératif de s'assurer que le numéro de litige du transporteur est bien inscrit dans la facture ACILYON (facturation transporteur) pour que le litige soit étudié</div>
      ";
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
  <form method=\"post\" name=\"et5form\" id=\"et5form\">
  <input type=\"hidden\" name=\"savid\" value=\"".$sav->id."\">
  <input type=\"hidden\" name=\"step\" value=\"6\">
  <input type=\"hidden\" name=\"date_fin\" value=\"".($sav->date_fin>0?$sav->date_fin:0)."\">
  <p>Etape 6 : Cloture du dossier litige<span class=\"modifstep\">".$sav->steps[6]['utilisateur']." ".($sav->steps[6]['date']>0?date("d/m/Y",$sav->steps[6]['date']):"")."</span></p>
  <a href=\"javascript:mail_enreg(undefined,undefined,undefined,52,'$user[langue]');mail_suivi('suivi_commande',$facturid);mail_services('$user[langue]','$user[site_code]');\">Générer Mail confirmation remboursement client</a><br>&nbsp;
  <div>Analyse financière du dossier litige :</div>
  <div style=\"width:49%;float:left;border-right:solid thin black;\">
    <b>Du coté du client</b>
    <br>Nouveau produit reçu le ".$sav->cal_input("sav_produitrecu","sav_calsave")."
    <br>".$savfacture['user']."
    <br><br><b>Avant de cloturer le dossier litige, s'assurer que :</b>
    <br>1 - Si remise commerciale, un avoir client du montant de la remise a été créé ".$sav->checkbox("sav_verif1","sav_save('sav_verif1')")." ".$sav->wait("sav_verif1")." <span id=\"sav_verif1-date\">".$sav->modiffield('sav_verif1')."</span>
    <br><br>2 - Si remise commerciale, le client a bien été remboursé ".$sav->checkbox("sav_verif2","sav_save('sav_verif2')")." ".$sav->wait("sav_verif2")." <span id=\"sav_verif2-date\">".$sav->modiffield('sav_verif2')."</span>
  </div>
  <div style=\"width:50%;float:left;margin-left:2px;\">
    <b>Du coté fournisseur</b>
    <br>".$savfacture['fourn']."
    <br>1 - Si remplacement produit, une nouvelle commande a été créée et facturée au fournisseur ".$sav->checkbox("sav_verif3","sav_save('sav_verif3')")." ".$sav->wait("sav_verif3")." <span id=\"sav_verif3-date\">".$sav->modiffield('sav_verif3')."</span>
    <br><br>2 - Si remplacement produit, un avoir sur les frais de port a été demandé ".$sav->checkbox("sav_verif4","sav_save('sav_verif4')")." ".$sav->wait("sav_verif4")." <span id=\"sav_verif4-date\">".$sav->modiffield('sav_verif4')."</span>
    <br><br>3 - Si remise commerciale, la remise commerciale a été facturée au fournisseur ".$sav->checkbox("sav_verif5","sav_save('sav_verif5')")." ".$sav->wait("sav_verif5")." <span id=\"sav_verif5-date\">".$sav->modiffield('sav_verif5')."</span>
    <br><br>4 - Si avarie occulte, un avoir du produit et des frais de transport a été demandé au fabricant (s'il n'a pas été facturé) ".$sav->checkbox("sav_verif6","sav_save('sav_verif6')")." ".$sav->wait("sav_verif6")." <span id=\"sav_verif6-date\">".$sav->modiffield('sav_verif6')."</span>
  </div>
  <div style=\"clear:both;\"></div>";
      

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
