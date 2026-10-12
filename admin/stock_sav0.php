<?php

print "<a name=\"sav\"></a>
<div class=\"advgroup\">
<form name=\"et1\" action=\"stock.php?action=cmd&stock_cmd_id=".$stock_cmd->id."&savid=".$sav->id."#sav\" method=\"post\">
<input type=\"hidden\" name=\"savid\" value=\"".$sav->id."\">
<input type=\"hidden\" name=\"step\" value=\"1\">
<input type=\"hidden\" name=\"sav_nom\" value=\"$user[nom]\">
<input type=\"hidden\" name=\"sav_prenom\" value=\"$user[prenom]\">
<input type=\"hidden\" name=\"sav_civ\" value=\"".$user['civilite']."\">
<input type=\"hidden\" name=\"sav_facturid\" value=\"".$facturid."\">
<p>Réclamation : avarie de transport<span class=\"modifstep\">".$sav->steps[1]['utilisateur']." ".($sav->steps[1]['date']>0?date("d/m/Y",$sav->steps[1]['date']):"")."</span></p>
<b>Produits concernés</b>";

$stock_cmd->items();
$stock_cmd->groupitems();
foreach($stock_cmd->items as $item){
    $optqte="";
    for($i=0;$i<=$item->qte;$i++) $optqte.="<option value=\"$i\"".($sav->lignes[$item->artid]==$i?" selected":"").">$i</option>";
    print "<br><select name=\"savligne[".$item->artid."]\" onchange=\"document.forms.et1.submit();\">$optqte</select> / ".$item->qte." ".$item->libelle." ".$item->artcode." - ".$item->ref." ".$item->marque;
}

print "</form>
</div>";

print "<div>
<form action=\"stock.php?action=cmd&stock_cmd_id=".$stock_cmd->id."&savid=".$sav->id."#sav\" method=\"post\" enctype=\"multipart/form-data\">
<input type=\"hidden\" name=\"savid\" value=\"".$sav->id."\">
<input type=\"hidden\" name=\"step\" value=\"1\">
<table class=\"etape\">
<tr><td colspan=\"2\" class=\"etapetd\">Etape 1 : Informations sur la livraison</td></tr>
<tr><td>Date livraison validée sur le site du transporteur</td><td nowrap>".$sav->cal_input('sav_datelivr',"sav_calsave")."</td></tr>
<tr><td>Numéro du bon de transport validé sur le site du transporteur</td><td nowrap>".$sav->text_input('sav_nbon',"sav_autosave('sav_nbon')")."</td></tr>
<tr><td>Nom du transporteur</td><td nowrap>".$sav->text_input('sav_nomtrspt',"sav_autosave('sav_nomtrspt')")."</td></tr>
<tr><td>N° de série de l'appareil</td><td nowrap>".$sav->text_input('sav_nbserie',"sav_autosave('sav_nbserie')")."</td></tr>
<tr><td>Avez-vous conservé la marchandise ?</td><td nowrap>".$sav->radio_inputs('sav_marchandise',array("Oui"=>1,"Non"=>0),"sav_save('sav_marchandise')")."</td></tr>
<tr><td>Avez-vous conservé l'emballage ?</td><td nowrap>".$sav->radio_inputs('sav_emballage',array("Oui"=>1,"Non"=>0),"sav_save('sav_emballage')")."</td></tr>
<tr><td>Réserves émises par le client confirmées par le transporteur ou par le bon de transport</td><td nowrap>".$sav->radio_inputs('sav_reserve',array("Oui"=>1,"Non"=>0),"sav_save('sav_reserve')")."</td></tr>
<tr><td>Si oui, lesquelles :</td><td>".$sav->textarea("sav_reserves_verif","sav_autosave('sav_reserves_verif')",array("cols"=>50,"rows"=>3))."</td></tr>
<tr><td><span class=\"fileboxbutton\" onclick=\"fb.show('fournisseur_files.php?savid=".$sav->id."');\"><img src=\"images/icon/jpg.png\"> Voir les fichiers joints (".count($stock_cmd->files).")</span></td></tr>
</table>";
if($sav->maxstep<1) print "<div style=\"text-align:center;padding:10px;\"><input type=\"submit\" value=\"Passer à l'étape suivante\"></div>";
print "</form>
</div>";

if($sav->maxstep>=1){
  print "<div class=\"advgroup\">
  <form method=\"post\">
  <input type=\"hidden\" name=\"savid\" value=\"".$sav->id."\">
  <input type=\"hidden\" name=\"step\" value=\"2\">
  <p>Etape 2 : Analyse des responsabilités par le SAV<span class=\"modifstep\">".$sav->steps[3]['utilisateur']." ".($sav->steps[3]['date']>0?date("d/m/Y",$sav->steps[3]['date']):"")."</span></p>
  <table>
  <tr><td>Type d'avarie</td><td>".$sav->radio_inputs("sav_typeavarie",array("Transporteur"=>1),"sav_save('sav_typeavarie')")."</td><td colspan=\"2\">".$sav->radio("sav_typeavarie",array(""=>3),"sav_save('sav_typeavarie')")." Avarie occulte (responsabilité du fabricant)<br>Avarie occulte = produit réceptionné abîmé avec des dommages ne pouvant pas être causés pendant le transport.<br>Photos impératives</td></tr>
  <tr><td>Qui est responsable ? </td><td>".$sav->radio("sav_responsable",array("Acilyon"=>5),"sav_save('sav_responsable')")."</td><td></td><td></td></tr>
  <tr><td>Résolution à l'amiable ? </td><td>".$sav->radio_inputs("sav_amiable",array("Non"=>0),"sav_save('sav_amiable')")."</td><td>".$sav->radio("sav_amiable",array("Oui"=>1),"sav_save('sav_amiable')")."</td><td>Montant : ".$sav->nb_input('sav_montantamiable',"sav_autosave('sav_montantamiable')")." </td></tr>
  <tr><td>Qui doit payer le remplacement du produit / remise commerciale </td><td nowrap>".$sav->radio_inputs("sav_remplacement",array("Transporteur"=>1),"sav_save('sav_remplacement')")."</td><td>".$sav->radio("sav_remplacement",array("Fournisseur"=>3),"sav_save('sav_remplacement')")."</td><td>".$sav->radio("sav_remplacement",array("Acilyon"=>5),"sav_save('sav_remplacement')")."</td></tr>
  <tr><td>Qui doit payer les frais de transport liés au remplacement du produit ? </td><td nowrap>".$sav->radio_inputs("sav_fraisport",array("Transporteur"=>1),"sav_save('sav_fraisport')")."</td><td>".$sav->radio("sav_fraisport",array("Fournisseur"=>3),"sav_save('sav_fraisport')")."</td><td>".$sav->radio("sav_fraisport",array("Acilyon"=>5),"sav_save('sav_fraisport')")."</td></tr>
  <tr><td>Commentaire :</td><td colspan=\"5\">".$sav->textarea("sav_conclusion","sav_autosave('sav_conclusion')",array("cols"=>70,"rows"=>4))."</td></tr>
  </table>";
  
  if($sav->maxstep==1) print "<div style=\"text-align:center;padding:10px;\"><input type=\"submit\" value=\"Passer à l'étape suivante\"></div>";
  print "</form>
  </div>";
}

if($sav->maxstep>=2){
  print "<div class=\"advgroup\">
  <form method=\"post\" enctype=\"multipart/form-data\">
  <input type=\"hidden\" name=\"savid\" value=\"".$sav->id."\">
  <input type=\"hidden\" name=\"step\" value=\"3\">
  <p>Etape 3 : Ouverture du dossier litige<span class=\"modifstep\">".$sav->steps[4]['utilisateur']." ".($sav->steps[4]['date']>0?date("d/m/Y",$sav->steps[4]['date']):"")."</span></p>

  <div>
    <b>Dossier litige fournisseur :</b>
    <br><br><a href=\"javascript:mail_enreg(undefined,undefined,undefined,32,'FR');mail_suivi('suivi_stock',".$stock_cmd->id.");mail_services('FR','base');\">Générer mail réclamation transporteur</a>
    <br><a href=\"javascript:mail_enreg(undefined,undefined,undefined,40,'FR');mail_suivi('suivi_stock',".$stock_cmd->id.");mail_services('FR','base');\">Relance mail réclamation transporteur</a>
    <hr>
    <br><font style=\"color:red;font-weight:bold;\">Avarie occulte : Il est impératif de joindre au mail des photos illustrant le problème.</font>
    <br><br><a href=\"javascript:mail_enreg(undefined,undefined,undefined,41,'FR');mail_suivi('suivi_stock',".$stock_cmd->id.");mail_services('FR','base');\">Générer mail réclamation avarie occulte fournisseur</a>
    <br><a href=\"javascript:mail_enreg(undefined,undefined,undefined,42,'FR');mail_suivi('suivi_stock',".$stock_cmd->id.");mail_services('FR','base');\">Relance mail réclamation avarie occulte fournisseur</a>
    <hr>
    <br><br>Litige accepté par fournisseur :
    <br>".$sav->radio_inputs("sav_litigeaccept",array("Oui"=>1,"Non"=>0),"sav_save('sav_litigeaccept')")." 
    <br>Commentaires :<br>".$sav->textarea("sav_motifrefus","sav_autosave('sav_motifrefus')",array("cols"=>40,"rows"=>3))."
    <br><br>Dossier litige n°".$sav->text_input('sav_ndossier',"sav_autosave('sav_ndossier')")." ".$sav->cal_input('sav_dateouvre',"sav_calsave")." 
  </div>
  <div style=\"clear:both;\"></div>";

  if($sav->maxstep==2) print "<div style=\"text-align:center;padding:10px;\"><input type=\"submit\" value=\"Passer à l'étape suivante\"></div>";
  "</form>
  </div>";
}

$savresponsable=$DB_site2->query_first("SELECT * FROM fournisseur WHERE societe='".addslashes($sav->fields['sav_responsable'])."' AND type='savclient'");
if($savresponsable['id']=="")
  $adresseresponsable="Non configuré";
else
  $adresseresponsable=$savresponsable['societe']."<br>".$savresponsable['adresse1']."<br>".$savresponsable['cp']." ".$savresponsable['ville'];
/*
foreach($sav->lignes as $ligneid=>$qte){
   $table="<table><tr><td>Article</td><td>Fournisseur</td><td>Prix</td><td>Demande<br>avoir</td><td>Info compta</td></tr>";
   $sql="";
   
   while($efl=$DB_site2->fetch_array($r)){
      $icon="";
      $info="<textarea name=\"comment".$efl['efid']."\" cols=\"20\" rows=\"2\">".$efl['comment']."</textarea><br><a href=\"javascript:updeflcomment($efl[efid],'".$sav->fields['sav_nbon_verif']."','".$sav->fields['sav_ndossier']."')\">Ajouter l'info</a>";
      if($efl['facture']!=""){
	$avoir=$DB_site2->query_first("SELECT id FROM avoir_ligne WHERE eflid=".$efl['id']);
	if($avoir['id']!="") $icon="<img src=\"images/coche_verte.svg\">";
	$info="<a href=\"avoir_fournisseur.php?action=ajouter&ffid=".$efl['facture']."\" target=\"_blank\">".$efl['facture']."</a>";
      }
     
      $table.="<tr><td>".$efl['artcode']."</td><td>".$efl['destinataire']."</td><td>".round($efl['qte']*$efl['paht'],2)."</td><td>$icon</td><td>".$info."</td></tr>";
      
   }
   $table.="</table>";
}*/
  
if($sav->maxstep>=3){
  print "<div class=\"advgroup\">
  <script language=\"javascript\">
  window.onfocus=function(){
      refreshpj();
  }
  </script>
  <form method=\"post\" enctype=\"multipart/form-data\">
  <input type=\"hidden\" name=\"savid\" value=\"".$sav->id."\">
  <input type=\"hidden\" name=\"step\" value=\"4\">
  <p>Etape 4 : Montage du dossier litige<span class=\"modifstep\">".$sav->steps[5]['utilisateur']." ".($sav->steps[5]['date']>0?date("d/m/Y",$sav->steps[5]['date']):"")."</span></p>
  ".$sav->checkbox("sav_pasouverture","sav_save('sav_pasouverture')")." Pas d'ouverture de dossier litige
  <br><br><b>Les pièces du dossier :</b> <img src=\"images/refresh.svg\" height=\"24\" align=\"absmiddle\" onclick=\"refreshpj()\"> <span class=\"fileboxbutton\" onclick=\"fb.show('fournisseur_files.php?savid=".$sav->id."');\"><img src=\"images/icon/jpg.png\"> Voir les fichiers joints (".count($stock_cmd->files).")</span>
  <div style=\"margin:0px;padding:0px;\" id=\"et6tmppj\">";

  print $sav->checkbox_files();
  print "</div>
  <br><a href=\"javascript:maildossier(33);\">Dossier à envoyer par mail</a>
  <br><br>Dossier à envoyer par la Poste à l'adresse facturation :<br>$adresseresponsable
  <br>Dossier complet envoyé le <input type=\"text\" name=\"sav_envoidossier\" id=\"sav_envoidossier\" value=\"".$sav->fields['sav_envoidossier']."\" onclick=\"calendrier_show(this.id,this.value)\">
  <br>par ".$sav->checkbox('sav_envoimail',"sav_save('sav_envoimail');sav_save('sav_envoidossier')")." mail &nbsp; ".$sav->checkbox('sav_envoiposte',"sav_save('sav_envoiposte');sav_save('sav_envoidossier')")." La Poste";
  
  print "<br><br>Dans le cas d'un remplacement du produit ou d'une remise commerciale, il faut créer une nouvelle commande et facturer le transporteur / fournisseur du produit à remplacer ou de la remise commerciale.
  <br><a href=\"facture.php?action=creer&createfrom=".$stock_cmd->id."\" target=\"_blank\">Création nouvelle commande</a>

  <div style=\"margin-top:10px;margin-bottom:10px;\">
      <font color=\"#DD6666\">ATTENTION NE PAS OUBLIER DE faire la demande d'avoir transport dans le tableau ci dessous si facture transport non enregistrée</font>
	$table
      <br><font color=\"#DD6666\">Ou d'ajouter cet avoir à la liste des avoirs en attente si la facture a déjà été enregistrée (module demande d'avoir)</font>

  </div>
  <font color=\"#DD6666\">Si avarie occulte, il faut demander un avoir sur le produit au fabricant</font>

  <div style=\"clear:both;\"></div>
  <div style=\"margin:10px;font-style:italic;\">MEMO : Il est impératif de s'assurer que le numéro de litige du transporteur est bien inscrit dans la facture ACILYON (facturation transporteur) pour que le litige soit étudié</div>";
  
  if($sav->maxstep==3) print "<div style=\"text-align:center;padding:10px;\"><input type=\"submit\" value=\"Passer à l'étape suivante\"></div>";
  print "</form>
  </div>";
}

if($sav->maxstep>=4){
?>
    <div class="advgroup" style="padding: 5px;">
        <form method="post" name="et5form" id="et5form">
            <input type="hidden" name="savid" value="<?php echo $sav->id; ?>">
            <input type="hidden" name="step" value="5">
            <input type="hidden" name="date_fin" value="<?php echo ($sav->date_fin > 0 ? $sav->date_fin : 0); ?>">
            <p>Etape 5 : Cloture du dossier litige <span class="modifstep"><?php echo $sav->steps[6]['utilisateur'] . " " . ($sav->steps[6]['date'] > 0 ? date("d/m/Y",$sav->steps[6]['date']) : ""); ?></span></p>
            <div>Analyse financière du dossier litige :</div>
            <div>
                <b>Du coté fournisseur</b>
                <br>
                <?php echo $savfacture['fourn']; ?>
                <br>
                1 - Si remplacement produit, une nouvelle commande a été créée et facturée au fournisseur <?php echo $sav->checkbox("sav_verif3","sav_save('sav_verif3')"); ?>
                <br><br>
                2 - Si remplacement produit, un avoir sur les frais de port a été demandé <?php echo $sav->checkbox("sav_verif4","sav_save('sav_verif4')"); ?>
                <br><br>
                3 - Si remise commerciale, la remise commerciale a été facturée au fournisseur <?php echo $sav->checkbox("sav_verif5","sav_save('sav_verif5')"); ?>
                <br><br>
                4 - Si avarie occulte, un avoir du produit et des frais de transport a été demandé au fabricant (s'il n'a pas été facturé) <?php echo $sav->checkbox("sav_verif6","sav_save('sav_verif6')"); ?>
            </div>
            <div id="ffourn2"><img src="images/plus.jpg" align="absmiddle" onclick="document.getElementById('ffourn2').style.display='none'; document.getElementById('ffourn').style.display='block';"> Récapitulatif commandes</div>
            <div id="ffourn" style="display:none;"><img src="images/moins.jpg" align="absmiddle" onclick="document.getElementById('ffourn2').style.display='block'; document.getElementById('ffourn').style.display='none';"> Récapitulatif commandes <?php echo $recapcommandes; ?></div>

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