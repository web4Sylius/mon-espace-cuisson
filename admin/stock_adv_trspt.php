<?php


print "<div id=\"tabtrspt\" style=\"".($_GET['type']!="trspt"?"visibility:hidden; position:absolute;":"")."\">
<h3><img src=\"images/livraison.png\" width=\"25\" align=\"absmiddle\"> Fournisseur de TRANSPORT</h3>
<form name=\"form2\" action=\"stock.php?action=cmd&stock_cmd_id=".$stock_cmd->id."&advid=".$adv->id."\" method=\"post\" enctype=\"multipart/form-data\">
<input type=\"hidden\" name=\"actionadv\" value=\"advfournisseur\">
<input type=\"hidden\" name=\"trsptname\">
<input type=\"hidden\" name=\"fournisseurname\">";

$marqueids=array();
print "<div id=\"steptrspt1\" class=\"advgroup\">
<p>1. Produits</p>
<table>
<tr><td colspan=\"2\"></td><td>qte</td><td>Détail</td><td>Poids brut en kg</td></tr>";
$stock_cmd->items(0);
$stock_cmd->groupitems();
foreach($stock_cmd->items as $item){
    if($item->trspt=="" && $item->date_livraison==0){
      if($article['marqueid']!=null) $marqueids[]=$article['marqueid'];

      $img="<img src=\"".$item->img."\" style=\"width:30px;\" onmouseover=\"this.style.position='absolute';this.style.width='200px';\" onmouseout=\"this.style.position='relative';this.style.width='30px';\">";
      $checkbox="<input type=\"hidden\" name=\"\" value=\"\"><input type=\"checkbox\" name=\"produits2[]\" value=\"".$item->artid."\" onclick=\"updligne(".$item->artid.",".$item->qte.",2);totalassurance();showstep('trspt2');\" ".($adv->lignes[$item->artid]>0?"checked":"").">";

      print "<tr>
	<td style=\"$style\"><input type=\"hidden\" name=\"price_".$item->artid."\" value=\"".($item->paht/$item->qte)."\"><input type=\"hidden\" name=\"ecopart2_".$item->artid."\" value=\"".$item->ecopartht."\"><input type=\"hidden\" name=\"volume_".$item->artid."\" value=\"".$item->volume_brut."\">$checkbox</td>
	<td>$img</td>
	<td><input type=\"hidden\" name=\"qte2_".$item->artid."\" value=\"".$item->qte."\">".$item->qte." ".$item->artcode."</td>
	<td>".$item->libelle."</td>
	<td><input type=\"hidden\" name=\"poids_".$item->artid."\" value=\"".$item->poids_brut."\">".$item->poids_brut."<input type=\"hidden\" name=\"volume_$lignefacture[lignefactureid]\" value=\"$article[volume]\"></td>
      </tr>";
   }
}
$marqueids=array_unique($marqueids);
print "<tr><td colspan=\"3\"></td><td>Poids total</td><td id=\"poidstotal\"></td></tr>
<tr><td>".$adv->checkbox("uncolis","adv_save(%name)",1)."</td><td colspan=\"3\">Attention : un seul colis pour tous les articles</td></tr>
<tr><td>".$adv->checkbox("hayon","adv_save(%name)",1,1)."</td><td colspan=\"3\">Camion avec hayon indispensable</td></tr>
</table>
</div>";

print "<div id=\"steptrspt2\" class=\"advgroup\"".(isset($adv->steps[2])?" style=\"display:block;\"":"").">
<p>2. Type de livraison</p>
<table ".($adv->modif?"style=\"display:none;\"":"").">
<tr>
  <td>".$adv->radio_inputs("typetrspt",array(""=>0),"fournisseur_save(0);document.getElementsByName('trsptname')[0].value='0';showstep('trspt12');adv_save('typetrspt',12)")."</td>
  <td>Pas de transport</td>
  <td>".$adv->radio("typetrspt",array(""=>"webmaster@mon-espace-cuisson.com"),"document.getElementsByName('trsptname')[0].value='Acilyon test';showstep('trspt3');fournisseur_save('Acilyon test');adv_save('typetrspt',3)")."</td>
  <td>Acilyon test</td>
</tr>";


$fourn=array();
$list=$DB_site2->query("SELECT * FROM fournisseur WHERE `type`='trsptadv' ORDER BY societe");
while($advf=$DB_site2->fetch_array($list)){
    if($advf['groupes']!=""){
      $groups=explode(",",$advf['groupes']);
      foreach($groups as $group)
	$fourn[$group][]=$advf['mail'];
    }else
      $fourn[$advf['societe']][]=$advf['mail'];
}


foreach($fourn as $group=>$mails){
	    $i++;
	    $c++;
	    $mails=array_unique($mails);
	    $mailcsv=implode(";",$mails);
	    print "<td>".$adv->radio("typetrspt",array(""=>$mailcsv),"document.getElementsByName('trsptname')[0].value='$group'; tarifs('$group');showstep('trspt3');fournisseur_save('$group');adv_save('typetrspt',3)")."</td><td title=\"$mailcsv\">".ucfirst($group)."</td>";
            if($c==2){
                 $c=0;
                 print "</tr><tr>";
            }
}

print "</table>";
if($adv->modif) print $adv->destinataire." (".$adv->fields['typetrspt'].")";
print "</div>";

print "<div id=\"steptrspt3\" class=\"advgroup\"".(isset($adv->steps[3])?" style=\"display:block;\"":"").">
<p>3. Pièces jointes</p>
".$adv->checkbox("notrsptpj","showstep('trspt4');adv_save(%name,4)","0")." Aucune
<br>".$adv->checkbox("othertrsptpj","showstep('trspt4');adv_save(%name,4)",1)." Autre : <input type=\"file\" name=\"othertrsptpjfile\">
</div>";

if(!$adv->locked && $adv->fields["trsptobj"]=="annule") $adv->save_field("typecmd","cmd");
if($adv->locked) $adv->save_field("trsptobj","annule");
if($adv->modif) $adv->save_field("trsptobj","modif");
print "<div id=\"steptrspt4\" class=\"advgroup\"".(isset($adv->steps[4])?" style=\"display:block;\"":"").">
<p>4. Objet du mail</p>
<div".($adv->modif || $adv->locked?" style=\"display:none;\"":"").">".$adv->radio_inputs("trsptobj",array("Commande n°".$adv->facturid=>"cmd"),"showstep('trspt5');showstep('trspt6');adv_save('trsptobj',6)")."</div>
<div".($adv->modif || !$adv->locked?" style=\"display:none;\"":"").">".$adv->radio("trsptobj",array("Annulation de la commande n°".$adv->facturid=>"annule"),"showstep('trspt12');adv_save('trsptobj',5)")."</div>
<div id=\"modifcmd\"".(!$adv->modif?" style=\"display:none;\"":"").">".$adv->radio("trsptobj",array("Modifier commande"=>"modif"),"showstep('trspt5');adv_save('trsptobj',5);")."</div>";
if($rs_facture['provenance']=="sav") print "<br>".$adv->radio("trsptobj",array(""=>"avarie"),"showstep('trspt5');adv_save('trsptobj',5)")." Commande n°$facturid - Suite avarie commande n°".$rs_facture['provenanceid']." (pour non paiement des frais de port = avarie transporteur)<input type=\"hidden\" name=\"provenanceid\" value=\"".$rs_facture['provenanceid']."\">";
print "<br>".$adv->checkbox("envoiedi","adv_save(%name)",1)." Envoi par EDI
</div>";

/*
print "<div class=\"advgroup\">
<p>4. Texte d'intro</p>
<input type=\"radio\" name=\"trsptintro\" value=\"bonjour\" checked> Bonjour, Veuillez trouver ci-joint une nouvelle commande de transport n°$facturid
<br><input type=\"radio\" name=\"trsptintro\" value=\"annule\"> Merci d'annuler le transport sur la commande n°$facturid et nous le confirmer par retour de ce mail.
</div>";
*/

$enlev=$DB_site2->query_first("SELECT * FROM fournisseur INNER JOIN pays USING(paysid) WHERE id=".$stock_cmd->enlevement);
print "<div id=\"steptrspt5\" class=\"advgroup\"".(isset($adv->steps[5])?" style=\"display:block;\"":"").">
<p>5. Lieu d'enlèvement</p>"
.$enlev['societe'].
"<br>".$enlev['nom']." ".$enlev['prenom'].
"<br>".$enlev['adresse'].
($enlev['adresse2']!=""?"<br>".$enlev['adresse2']:"").
"<br>".$enlev['cp']." ".$enlev['ville'].
"<br>".$enlev['libelle'].
"</div>";

print "<div id=\"steptrspt6\" class=\"advgroup\"".(isset($adv->steps[6])?" style=\"display:block;\"":"").">
<p>6. Identification à mettre sur le colis</p>
".$adv->checkbox("trsptbl","showstep('trspt7');adv_save(%name,7)",1)." BL fournit ci-joint
<br>".$adv->checkbox("trsptfacture","showstep('trspt7');adv_save(%name,7)",1)." Facture ci-jointe
<br>".$adv->checkbox("trsptfragile","showstep('trspt7');adv_save(%name,7)",1)." Etiquette Fragile ci-jointe
<!--<br><input type=\"checkbox\" name=\"trspttext\" value=\"1\"> Texte sur le colis : commande n°$facturid de Ma Cave à Vin - Acilyon pour $rs_facture[nom] $rs_facture[prenom] $rs_facture[codepostal] $rs_facture[ville]-->
<br>".$adv->checkbox("trsptimportant","showstep('trspt7');adv_save(%name,7)",1)." Important : enlever tous les documents de livraison du transporteur du fournisseur
<br>".$adv->checkbox("trspttextother","showstep('trspt7');adv_save(%name,7)",1)." Autre : ".$adv->text_input("trsptothertext","adv_autosave(this.name)",array("size"=>60))."
</div>";

print "<div id=\"steptrspt7\" class=\"advgroup\"".(isset($adv->steps[7])?" style=\"display:block;\"":"").">
<p>7. Date enlèvement</p>
Date enlèvement chez le fournisseur : ".$adv->cal_input("trsptenlfourndate","adv_calsave")."
<br><a href=\"javascript:showstep('trspt8');\">Suivant &gt;&gt;</a>
</div>";

print "<div id=\"steptrspt8\" class=\"advgroup\"".(isset($adv->steps[8])?" style=\"display:block;\"":"").">
<p>8. Type de transport</p>
<table>
<tr>
  <td>".$adv->radio_inputs("trspttype",array("Avec RDV"=>"rdv"),"showstep('trspt9');adv_save('trspttype',9)")."</td>
  <td>".$adv->radio("trspttype",array("Express - Gold"=>"gold"),"showstep('trspt9');adv_save('trspttype',9)")."</td>
  <td>".$adv->radio("trspttype",array("Sans RDV"=>"nordv"),"showstep('trspt9');adv_save('trspttype',9)")."</td>
</tr>
</table>
</div>";

$livr=$DB_site2->query_first("SELECT * FROM fournisseur INNER JOIN pays USING(paysid) WHERE id=".$stock_cmd->livraison);
print "<div id=\"steptrspt9\" class=\"advgroup\"".(isset($adv->steps[9])?" style=\"display:block;\"":"").">
<p>9. Adresse de livraison</p>".
$livr['societe'].
"<br>".$livr['nom']." ".$livr['prenom'].
"<br>".$livr['adresse'].
($livr['adresse2']!=""?"<br>".$livr['adresse2']:"").
"<br>".$livr['cp']." ".$livr['ville'].
"<br>".$livr['libelle'].
"<br><a href=\"javascript:showstep('trspt10');\">Suivant &gt;&gt;</a>
</div>";

print "<div id=\"steptrspt10\" class=\"advgroup\"".(isset($adv->steps[10])?" style=\"display:block;\"":"").">
<p>10. Date livraison</p>
<br>Date livraison ".$adv->radio_inputs("trsptdatemax",array("maximum"=>"trsptdateother","date précise"=>"trsptdateauto"),"adv_save('trsptdatemax','11,12');cout_trspt();showstep('trspt11');showstep('trspt12');")." ".$adv->cal_input("trsptotherdate","adv_calsave")."
<br>".$adv->checkbox("livraisonplustot","adv_save(%name)",1)."Voir la possibilité de livrer plus tôt
<br>".$adv->checkbox("infotrspt","adv_save(%name)","other")." Info pour transporteur : ".$adv->text_input("othertrsptinfo","adv_autosave(this.name)",array("size"=>60))."
</div>";


print "<div id=\"steptrspt11\" class=\"advgroup\"".(isset($adv->steps[11])?" style=\"display:block;\"":"").">
<p>11. Tarifs</p>
<table>
<tr><td width=\"20\"></td><td>Valeur HT</td><td>Poids</td><td>Volume</td><td>Poids volume 100kg</td><td>Poids volume 250kg</td><td>Poids volume 300kg</td></tr>
<tr><td></td><td id=\"valeurht\"></td><td id=\"coutpoids\"></td><td id=\"volumetotal\"></td><td id=\"poidsvolume100\"></td><td id=\"poidsvolume250\"></td><td id=\"poidsvolume300\"></td></tr>
<tr><td>".$adv->checkbox("trsptcout","trspttarif();adv_save(%name);adv_save('trsptcoutval')",1)."</td><td colspan=\"6\">Cout transport <div style=\"float:right;\">CP <span id=\"cplivr\">".$livr['cp']." - ".$livr['libelle']."</span></div></td><td>".$adv->nb_input("trsptcoutval","adv_save(this.name)")."&euro;</td></tr>
</table>
<div id=\"priceopts\"></div>
<div style=\"width:100%;text-align:right;font-weight:bold;\">Total : <span id=\"totaltrspt\"></span></div>
<input type=\"hidden\" name=\"valeurdeclaree\" value=\"0\">
<br>Informations supplémentaires :<br><textarea cols=\"80\" onkeyup=\"adv_save('infocomplement')\" name=\"infocomplement\"></textarea>
</div>";

$envoidate=null;
if($adv->envoi>0) $envoidate=date("d/m/Y",$adv->envoi);
$henvoi="";
if($adv->destinataire!=""){
    $h=$DB_site2->query_first("SELECT * FROM adv_heure WHERE fournisseur='".$adv->destinataire."'");
    $henvoi="à ".intval($h['heure'])."h";
}
print "<div id=\"steptrspt12\"".(isset($adv->steps[12])?" style=\"display:block;\"":"")." class=\"advgroup\">
  <input type=\"button\" value=\"Aperçu\" class=\"preview\" onclick=\"valid2(true);\">
  <div style=\"text-align:left;".($adv->locked?"display:none;":"")."\">
      <!--<br><input type=\"radio\" name=\"envoiauto\" onclick=\"envoidate_save();\" ".($adv->envoi>0?"checked":"")."> Envoyer la commande automatiquement <span id=\"envoi1\" style=\"".($adv->envoi==0?"display:none;":"")."\">le <input type=\"text\" id=\"envoidate\" value=\"".$envoidate."\" size=\"10\" onclick=\"calendrier_show(this.id,this.value,undefined,envoidate_save);\" readonly> <span id=\"henvoi\">".$henvoi."</span> <input type=\"button\" value=\"Envoi programmé\" onclick=\"document.location='facture.php?action=editer&facturid=$facturid#adv'\"></span>
      <br><br><input type=\"radio\" name=\"envoiauto\" onclick=\"envoidate_save();\" ".($adv->envoi==0?"checked":"")."> Envoyer la commande immédiatement <span id=\"envoi0\" style=\"".($adv->envoi>0?"display:none;":"")."\">-->
      <input type=\"button\" onclick=\"valid2()\" value=\"Envoi immédiat\"></span>
  </div>
  <div style=\"".($adv->locked?"":"display:none;").";margin-top:10px;\"><input type=\"button\" onclick=\"valid2()\" value=\"Annuler la commande\" style=\"color:red;font-weight:bold;\" /></div>
</div>";

print "</div>
<input type=\"hidden\" name=\"advid\" value=\"".$adv->id."\">
</form>";


?>
