<?php

print "<a name=\"sav\"></a>
<div class=\"advgroup\">
<form name=\"et1\" action=\"stock.php?action=cmd&stock_cmd_id=".$stock_cmd->id."&savid=".$sav->id."#sav\" method=\"post\">
<input type=\"hidden\" name=\"savid\" value=\"".$sav->id."\">
<input type=\"hidden\" name=\"step\" value=\"1\">

<p>Réclamation : manque produit<span class=\"modifstep\">".$sav->steps[1]['utilisateur']." ".($sav->steps[1]['date']>0?date("d/m/Y",$sav->steps[1]['date']):"")."</span></p>
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
if($sav->maxstep<=1) print "<div style=\"text-align:center;padding:10px;\"><input type=\"submit\" value=\"Passer à l'étape suivante\"></div>";
print "</form>
</div>";

if($sav->maxstep>=1){
  print "<div class=\"advgroup\">
  <form method=\"post\">
  <input type=\"hidden\" name=\"savid\" value=\"$savid\">
  <input type=\"hidden\" name=\"step\" value=\"2\">
  <p>Etape 2 : Analyse du litige par le Service Après Vente<span class=\"modifstep\">".$sav->steps[3]['utilisateur']." ".($sav->steps[3]['date']>0?date("d/m/Y",$sav->steps[3]['date']):"")."</span></p>
  <table>
  <tr><td>Qui est responsable ? </td><td>".$sav->radio_inputs("sav_responsable",array("Aucun"=>0),"sav_save('sav_responsable')")."</td><td>".$sav->radio("sav_responsable",array("Fournisseur"=>1),"sav_save('sav_responsable')")."</td><td>".$sav->radio("sav_responsable",array("Transporteur"=>2),"sav_save('sav_responsable')")."</td><td>".$sav->radio("sav_responsable",array("Acilyon"=>4),"sav_save('sav_responsable')")."</td></tr>
  <tr><td>Demande hors délai (7jours date de livraison) </td><td>".$sav->radio_inputs("sav_remplace",array("Oui"=>1),"sav_save('sav_remplace')")."</td><td>".$sav->radio("sav_remplace",array("Non"=>1),"sav_save('sav_remplace')")."</td><td></td><td></td><td></td><td></td></tr>
  <tr><td>Qui doit payer le remplacement du produit ? </td><td>".$sav->radio_inputs("sav_remplacement",array("Aucun"=>0),"sav_save('sav_remplacement')")."</td><td>".$sav->radio("sav_remplacement",array("Fournisseur"=>1),"sav_save('sav_remplacement')")."</td><td>".$sav->radio("sav_remplacement",array("Transporteur"=>2),"sav_save('sav_remplacement')")."</td><td>".$sav->radio("sav_remplacement",array("Acilyon"=>4),"sav_save('sav_remplacement')")."</td></tr>
  <tr><td>Qui prend en charge les frais de transport liés au remplacement / renvoi du produit ? <span id=\"sav_fraisport-ok\"></span></td><td>".$sav->radio_inputs("sav_fraisport",array("Aucun"=>0),"sav_save('sav_fraisport')")."</td><td>".$sav->radio("sav_fraisport",array("Fournisseur"=>1),"sav_save('sav_fraisport')")."</td><td>".$sav->radio("sav_fraisport",array("Transporteur"=>2),"sav_save('sav_fraisport')")."</td><td>".$sav->radio("sav_fraisport",array("Acilyon"=>4),"sav_save('sav_fraisport)")."</td></tr>
  <tr><td colspan=\"6\">Conclusion :<br>".$sav->textarea("sav_conclusion","sav_autosave('sav_conclusion')",array("cols"=>70,"rows"=>4))." <span id=\"sav_conclusion-ok\"></span></td></tr>
  </table>";
  
  if($sav->maxstep==1) print "<div style=\"text-align:center;padding:10px;\"><input type=\"submit\" value=\"Passer à l'étape suivante\"></div>";
  print "</form>
  </div>";
}

if($sav->maxstep>=2){
  print "<div class=\"advgroup\">
  <form method=\"post\">
  <input type=\"hidden\" name=\"savid\" value=\"".$sav->id."\">
  <input type=\"hidden\" name=\"step\" value=\"3\">
  <p>Etape 3 : Prise en charge du litige<span class=\"modifstep\">".$sav->steps[4]['utilisateur']." ".($sav->steps[4]['date']>0?date("d/m/Y",$sav->steps[4]['date']):"")."</span></p>

  <div>
    <b>Dossier fournisseur :</b><br>
    <br><a href=\"javascript:mail_enreg(undefined,undefined,undefined,48,'FR');mail_suivi('suivi_stock',".$stock_cmd->id.");mail_services('FR','base');\">Mail réclamation fournisseur</a>
    <br><a href=\"javascript:mail_enreg(undefined,undefined,undefined,49,'FR');mail_suivi('suivi_stock',".$stock_cmd->id.");mail_services('FR','base');\">Relance réclamation fournisseur</a>
    <br><br>Prise en charge du litige : ".$sav->radio_inputs("sav_litigeaccept",array("Oui"=>1,"Non"=>0),"sav_save('sav_litigeaccept')")."
    <br>Commentaires :".$sav->wait("sav_motifrefus")."<br>".$sav->textarea("sav_motifrefus","sav_autosave('sav_motifrefus')",array("cols"=>40,"rows"=>3))."
    <br><br>dossier litige n°".$sav->text_input('sav_ndossier',"sav_autosave('sav_ndossier')")."
  </div>
  <div style=\"clear:both;\"></div>";

  if($sav->maxstep==2) print "<div style=\"text-align:center;padding:10px;\"><input type=\"submit\" value=\"Passer à l'étape suivante\"></div>";
  print "</form>
  </div>";

}

$stock_cmd->items();
$stock_cmd->groupitems();

foreach($sav->lignes as $ligneid=>$qte){
   $table="<table><tr><td>Article</td><td>Fournisseur</td><td>Prix</td><td>Demande<br>avoir</td><td>Info compta</td></tr>";
   $sql="";
   
   if($qte>0){
	$icon="";
	$avoir=$DB_site2->query_first("SELECT id FROM avoir_ligne WHERE stock_cmd_id=".$stock_cmd->id." AND article='".mysqli_real_escape_string($DB_site2->link_id,$stock_cmd->items[$ligneid]->ref)."'");
	if($avoir['id']!="") $icon="<img src=\"images/coche_verte.svg\">";
	$info="<a href=\"avoir_fournisseur.php?action=ajouter&ffid=".$stock_cmd->items[$ligneid]->facture."\" target=\"_blank\">".$stock_cmd->items[$ligneid]->facture."</a>";
      
	$table.="<tr><td>".$stock_cmd->items[$ligneid]->artcode."</td><td>".$stock_cmd->destinataire."</td><td>".round($stock_cmd->items[$ligneid]->paht,2)."</td><td>$icon</td><td>".$info."</td></tr>";
   }
   $table.="</table>";
}

if($sav->maxstep>=3){
  print "<div class=\"advgroup\">
  <script language=\"javascript\">
  window.onfocus=function(){
      refreshpj();
  }
  </script>
  <form method=\"post\">
  <input type=\"hidden\" name=\"savid\" value=\"".$sav->id."\">
  <input type=\"hidden\" name=\"step\" value=\"4\">
  <p>Etape 4 : Montage du dossier litige<span class=\"modifstep\">".$sav->steps[5]['utilisateur']." ".($sav->steps[5]['date']>0?date("d/m/Y",$sav->steps[5]['date']):"")."</span></p>
  <div style=\"width:49%;float:left;border-right:solid thin black;test-align:left;\">
    ".$sav->checkbox("sav_pasouverture","sav_save('sav_pasouverture')")." Pas d'ouverture de dossier litige
    <br><br><a href=\"stock.php\" target=\"_blank\">Création nouvelle commande</a>
    <br><b>Les pièces du dossier :</b> <img src=\"images/refresh.svg\" height=\"24\" align=\"absmiddle\" onclick=\"refreshpj()\"> <span class=\"fileboxbutton\" onclick=\"fb.show('fournisseur_files.php?savid=".$sav->id."');\"><img src=\"images/icon/jpg.png\"> Voir les fichiers joints (".count($stock_cmd->files).")</span>
    <div style=\"margin:0px;padding:0px;\" id=\"et6tmppj\">";

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
  if($sav->maxstep==3) print "<div style=\"text-align:center;padding:10px;\"><input type=\"submit\" value=\"Passer à l'étape suivante\"></div>";
  print "</form>
  </div>";
}

if($sav->maxstep>=4){
?>
    <div class="advgroup" style="padding: 5px;">
        <form name="et5form" method="post" id="et5form">
            <input type="hidden" name="savid" value="<?php echo $sav->id; ?>">
            <input type="hidden" name="step" value="5">
            <input type="hidden" name="date_fin" value="<?php echo ($sav->date_fin > 0 ? $sav->date_fin : 0); ?>">
            
            <p>Etape 5 : Cloture du dossier litige<span class="modifstep"><?php echo $sav->steps[6]['utilisateur'] . " " . ($sav->steps[6]['date'] > 0 ? date("d/m/Y",$sav->steps[6]['date']) : ""); ?></span></p>
            <div>Analyse financière du dossier litige :</div>
            <div>
                <b>Du coté fournisseur</b><br>
                <br>Confirmation que le produit manquant a été envoyé à Acilyon <?php echo $sav->cal_input("sav_produitrenvoye","sav_calsave"); ?>
            </div>
            <br><br>
            <font style="color:#990000;font-weight:bold;">Avant de clôturer un dossier, s'assurer que :</font>
            <table>
                <tr>
                    <td>Le(s) produit(s) ne nous ont pas été facturé(s) plusieurs fois</td>
                    <td><?php echo $sav->checkbox("sav_verif1","sav_save('sav_verif1'); sumverif(3)"); ?></td>
                    <td><span id="sav_verif1-ok"></span><span id="sav_verif1-date"><?php echo $sav->modiffield('sav_verif1'); ?></span></td>
                </tr>
                <tr>
                    <td>Les frais liés à la réexpédition de la marchandise ont été remboursés ou fait l'objet d'un avoir</td>
                    <td><?php echo $sav->checkbox("sav_verif2","sav_save('sav_verif2'); sumverif(3)"); ?></td>
                    <td><span id="sav_verif2-ok"></span><span id="sav_verif2-date"><?php echo $sav->modiffield('sav_verif2'); ?></span></td>
                </tr>
            </table>
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