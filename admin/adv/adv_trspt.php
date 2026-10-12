<?php

//Si modification, chargement en ajax de chaque étape déjà saisie
$js_reload = '';
if(isset($adv->steps[3])) $js_reload .= "loadStep('trspt3', '/admin/adv/ajax/majEDI.php?name=".$adv->fields['trsptname']."&facturid=".$facturid."&advid=".$adv->id.($adv->locked ? "&annule=1" : "")."');";
if(isset($adv->steps[4])) $js_reload .= "loadStep('trspt4', '/admin/adv/ajax/majTypeTrspt.php?name=".$adv->fields['trsptname']."&facturid=".$facturid."&advid=".$adv->id.($adv->locked ? "&annule=1" : "")."');";
if(isset($adv->steps[5])) $js_reload .= "loadStep('trspt5', '/admin/adv/ajax/majProduits.php?name=".$adv->fields['trsptname']."&facturid=".$facturid."&advid=".$adv->id.($adv->locked ? "&annule=1" : "")."');";
if(isset($adv->steps[6])) $js_reload .= "loadStep('trspt6', '/admin/adv/ajax/majColisage.php?name=".$adv->fields['trsptname']."&facturid=".$facturid."&advid=".$adv->id.($adv->locked ? "&annule=1" : "")."',\"colis_prepare(document.getElementsByName('pcolis')[0].value)\");";
if(isset($adv->steps[7])) $js_reload .= "loadStep('trspt7', '/admin/adv/ajax/majLieuEnlev.php?name=".$adv->fields['trsptname']."&facturid=".$facturid."&advid=".$adv->id.($adv->locked ? "&annule=1" : "")."');";
if(isset($adv->steps[8])) $js_reload .= "loadStep('trspt8', '/admin/adv/ajax/majDateEnlev.php?name=".$adv->fields['trsptname']."&facturid=".$facturid."&advid=".$adv->id.($adv->locked ? "&annule=1" : "")."');";
if(isset($adv->steps[9])) $js_reload .= "loadStep('trspt9', '/admin/adv/ajax/majAdrLivr.php?name=".$adv->fields['trsptname']."&facturid=".$facturid."&advid=".$adv->id.($adv->locked ? "&annule=1" : "")."');";
if(isset($adv->steps[10])) $js_reload .= "loadStep('trspt10', '/admin/adv/ajax/majDateLivr.php?name=".$adv->fields['trsptname']."&facturid=".$facturid."&advid=".$adv->id.($adv->locked ? "&annule=1" : "")."');";
if(isset($adv->steps[11])) $js_reload .= "loadStep('trspt11', '/admin/adv/ajax/majPJ.php?name=".$adv->fields['trsptname']."&facturid=".$facturid."&advid=".$adv->id.($adv->locked ? "&annule=1" : "")."');";
if(isset($adv->steps[12])) $js_reload .= "loadStep('trspt12', '/admin/adv/ajax/majTarifs.php?name=".$adv->fields['trsptname']."&facturid=".$facturid."&advid=".$adv->id.($adv->locked ? "&annule=1" : "")."');";
if(isset($adv->steps[13])) $js_reload .= "loadStep('trspt13', '/admin/adv/ajax/majValidation.php?name=".$adv->fields['trsptname']."&facturid=".$facturid."&advid=".$adv->id.($adv->locked ? "&annule=1" : "")."', 'totalassurance();');";

if($js_reload != ''){
?>
<script type="text/javascript">
    $jQuery( document ).ready(function() {
        <?php echo $js_reload; ?>
    });
</script>
<?php
}
?>
<div id="tabtrspt" style="<?php echo $_GET['type'] != "trspt" ? "visibility:hidden; position:absolute;" : ""; ?>">
<h3><img src="images/livraison.png" width="25" align="absmiddle"> Fournisseur de TRANSPORT</h3>
<form name="form2" action="facture.php?action=editer&facturid=<?php echo $facturid; ?>" method="post" enctype="multipart/form-data" style="position: relative;">
<div class="loader-adv"><img src="<?php echo BACK_URL; ?>/images/loader.gif" class="loading"></div>
<input type="hidden" name="actionadv" value="advfournisseur">
<input type="hidden" name="trsptname">
<input type="hidden" name="fournisseurname">
<?php
$marqueids=array();

if(isset($_GET['annule']) || isset($_GET['modif'])){
    //$adv->save_field("envoiedi","0");
}

//1. Objet du mail 
include(BACK_PATH.'/adv/steps_transport/objet.php');

//2. Transporteur
include(BACK_PATH.'/adv/steps_transport/transporteur.php');

//3. Envoi EDI ou non
include(BACK_PATH.'/adv/steps_transport/edi.php');

//4. Type de transport
include(BACK_PATH.'/adv/steps_transport/type_transport.php');

//5. Produits
include(BACK_PATH.'/adv/steps_transport/produits.php');

//6. Colisage
include(BACK_PATH.'/adv/steps_transport/colisage.php');

//7. Lieu d'enlèvement
include(BACK_PATH.'/adv/steps_transport/lieu_enlevement.php');

//8. Date d'enlèvement
include(BACK_PATH.'/adv/steps_transport/date_enlevement.php');

//9. Adresse de livraison
include(BACK_PATH.'/adv/steps_transport/adresse_livraison.php');

//10. Date de livraison
include(BACK_PATH.'/adv/steps_transport/date_livraison.php');

//11. Pièces jointes
include(BACK_PATH.'/adv/steps_transport/pieces_jointes.php');

//12. Tarifs
include(BACK_PATH.'/adv/steps_transport/tarifs.php');

//13. Validation
include(BACK_PATH.'/adv/steps_transport/validation.php');

//Input servant à stocker certaines valeurs avant que l'étape "Tarifs" soit affiché
?>
<input type="hidden" id="cplivr_temp" value="">
<input type="hidden" id="bonslsname_temp" value="">
<input type="hidden" id="bonstrsptslspj_temp" name="bonstrsptslspj_temp" value="">
<input type="hidden" id="bonstrsptpj_temp" name="bonstrsptpj_temp" value="">

<!--<div id="steptrspt11" class="advgroup" <?php echo (isset($adv->steps[11]) ? "style=\"display:block;\"" : ""); ?>>
    <p>11. Manutention</p>
    Unités de manutention : <?php echo $adv->radio_inputs("nbmanutention",array(1=>1,2=>2,3=>3,"autre : "=>4),"showstep('trspt12');showstep('trspt13');adv_save('nbmanutention',11)"); ?><?php echo $adv->text_input("nbmanutentionautre","adv_autosave(this.name)",array("size"=>3)); ?> (dont <?php echo $adv->text_input("nbmanutentionconsigne","adv_autosave(this.name)",array("size"=>3)); ?> consigné <?php echo $adv->select("manutentionconsignetype",array(''=>'-- Sélectionner --','PAL'=>'Palette(s)'),"adv_save(this.name)"); ?>) 
    <br>
    Colis : <?php echo $adv->radio_inputs("nbcolis",array(1=>1,2=>2,3=>3,"autre : "=>4),"adv_save('nbcolis','12,13');showstep('trspt12');showstep('trspt13');"); ?><?php echo $adv->text_input("nbcolisautre","adv_autosave(this.name)",array("size"=>5)); ?>
    <br>
    <span style="color: red; font-size: 11px;">Le nombre d'UM (Unités de Manutention) donne le nombre d'étiquettes. Il peut être égal au nombre de colis (tous les colis sont étiquettés) ou inférieur (dans le cas d'une palette filmée par exemple).</span>
</div>-->

<!--
print "<div class=\"advgroup\">
<p>11. Information transporteur</p>
<input type=\"radio\" name=\"infotrspt\" value=\"0\"> Aucune
<br><input type=\"radio\" name=\"infotrspt\" value=\"rdv\"> Prendre RDV avec le client pour livraison
<br><input type=\"radio\" name=\"infotrspt\" value=\"other\"> Autre : <input type=\"text\" name=\"othertrsptinfo\" size=\"60\">
</div>";
-->


<?php



/*print "<div id=\"steptrspt11\" class=\"advgroup\"".(isset($adv->steps[11])?" style=\"display:block;\"":"").">
<p>11. Tarifs</p>
<table>
<tr>
  <td width=\"20\"></td>
  <td>Valeur HT</td>
  <td>Poids</td>
  <td>Volume</td>
  <td>Poids volume 100kg</td>
  <td>Poids volume 250kg</td>
  <td>Poids volume 300kg</td>
</tr>
<tr>
  <td></td>
  <td id=\"valeurht\"></td>
  <td id=\"coutpoids\"></td>
  <td id=\"volumetotal\"></td>
  <td id=\"poidsvolume100\"></td>
  <td id=\"poidsvolume250\"></td>
  <td id=\"poidsvolume300\"></td>
</tr>
<tr>
  <td>".$adv->checkbox("trsptcout","trspttarif();adv_save(%name);",1)."</td>
  <td colspan=\"6\">Cout transport <div style=\"float:right;\">CP <span id=\"cplivr\">$rs_facture[codepostal] - $pays_fact[libelle]</span></div></td>
  <td>".$adv->nb_input("trsptcoutval","adv_save(this.name)")."<span id=\"trsptcout_devise\">".$devises->symbole($adv->devise)."</span></td>
</tr>
</table>
<div id=\"priceopts\"></div>
<div style=\"width:100%;text-align:right;font-weight:bold;\">Total : <span id=\"totaltrspt\"></span></div>
<input type=\"hidden\" name=\"valeurdeclaree\" value=\"0\">

<!--<table>
<tr><td><input type=\"checkbox\" name=\"trsptcout\" value=\"1\" onclick=\"trspttarif()\"></td><td colspan=\"2\">Cout transport <div style=\"float:right;\"><span id=\"coutpoids\"></span> CP <span id=\"cplivr\">$rs_facture[codepostal] - $pays_fact[libelle]</span></div></td><td><input type=\"text\" name=\"trsptcoutval\" value=\"\" size=\"5\"> €</td></tr>
<tr id=\"trecotax\"><td><input type=\"checkbox\" name=\"trsptecotax\" value=\"1\" onclick=\"trspttarif()\"></td><td>Ecotax</td><td></td><td>1 €</td></tr>
<tr id=\"trexterieur\" style=\"visibility:hidden;\"><td><input type=\"checkbox\" name=\"trsptenlext\" value=\"12.7\" onclick=\"trspttarif()\"></td><td>Enlèvement extérieur</td><td></td><td>12,70 €</td></tr>
<tr id=\"trparis\"><td><input type=\"checkbox\" name=\"trsptparis\" value=\"6.40\" onclick=\"trspttarif()\"></td><td>Grande métropole Paris</td><td></td><td id=\"prixparis\">6.40 €</td></tr>
<tr id=\"trlyon\"><td><input type=\"checkbox\" name=\"trsptlyon\" value=\"4.9\" onclick=\"trspttarif()\"></td><td>Grande métropole Lyon</td><td></td><td id=\"prixlyon\">4,90 €</td></tr>
<tr id=\"trmarseille\" style=\"visibility:hidden; height:0px;\"><td><input type=\"checkbox\" name=\"trsptmarseille\" value=\"4.9\" onclick=\"trspttarif()\"></td><td>Grande métropole Marseille</td><td></td><td id=\"prixmarseille\">4,90 €</td></tr>
<tr id=\"alloincorse\"><td><input type=\"checkbox\" name=\"trsptcorse\" onclick=\"trspttarif()\" value=\"12\"></td><td>Corse</td><td></td><td>12,00 &euro;</td></tr>
<tr id=\"alloinsaison\" style=\"visibility:hidden;\"><td><input type=\"checkbox\" name=\"trsptsaison\" onclick=\"trspttarif()\" value=\"10\"></td><td>Taxe saisonnière (dep. 06,11,13,20,30,34,66,83,84,98 du 01/06 au 31/08)</td><td></td><td>10%</td></tr>
<tr id=\"alloinville\" style=\"visibility:hidden;\"><td><input type=\"checkbox\" name=\"trsptalloinville\" onclick=\"trspttarif()\" value=\"5.4\"></td><td>Centre ville (Paris, Lyon, Marseille, Bordeaux)</td><td></td><td>5,40 &euro;</td></tr>
<tr id=\"tretage\" style=\"visibility:hidden;\"><td><input type=\"checkbox\" name=\"trsptetage\" value=\"25\" onclick=\"trspttarif()\"></td><td>Livraison étage simple</td><td></td><td>25 €</td></tr>
<tr id=\"trpart\"><td><input type=\"checkbox\" name=\"trsptclientpart\" value=\"7\" onclick=\"trspttarif()\"></td><td>Client particulier pour RDV</td><td></td><td id=\"prixparticulier\">7,00 €</td></tr>
<tr id=\"trpro\"><td><input type=\"checkbox\" name=\"trsptclientpro\" value=\"0\" onclick=\"trspttarif()\"></td><td>Client professionnel sans RDV</td><td></td><td>0 €</td></tr>
<tr>
    <td><input type=\"checkbox\" name=\"trsptassurcomp\" value=\"1\" onclick=\"trspttarif()\"></td>
    <td id=\"txtassurance\">Assurance complémentaire 0,6% valeur</td>
    <td></td>
    <td><input type=\"radio\" name=\"baseassur\" onclick=\"document.getElementsByName('trsptassurcompval')[0].value=valtrsptassurance+Math.round(document.getElementsByName('valeurdeclaree')[0].value*pctrsptassurance)/100;\" checked>PA :<input type=\"text\" name=\"valeurdeclaree\" value=\"0\" size=\"5\" class=\"roinput\" readonly>
	<br><input type=\"radio\" name=\"baseassur\" onclick=\"document.getElementsByName('trsptassurcompval')[0].value=valtrsptassurance+Math.round(document.getElementsByName('valeurpv')[0].value*pctrsptassurance)/100;\">PV :<input type=\"text\" name=\"valeurpv\" value=\"0\" size=\"5\" class=\"roinput\" readonly>
	<br>Remb. :<input type=\"text\" id=\"valeurrembourse\" value=\"0\" class=\"roinput\" size=\"5\" readonly>
	<br><input type=\"text\" name=\"trsptassurcompval\" value=\"\" size=\"5\"> €
    </td>
</tr>
<tr><td><input type=\"checkbox\" name=\"trsptassurcompint\" value=\"1\" onclick=\"trspttarif()\"></td><td>Assurance complémentaire export 8.6&euro;/kg</td><td></td><td>Valeur :<input type=\"text\" name=\"valeurdeclaree2\" value=\"0\" size=\"5\" class=\"roinput\" readonly><br>Remb. :<input type=\"text\" id=\"valeurrembourse2\" value=\"0\" class=\"roinput\" size=\"5\" readonly><br><input type=\"text\" name=\"trsptassurcompintval\" value=\"\" size=\"5\"> €</td></tr>
<tr><td><input type=\"checkbox\" name=\"trsptcoutother\" value=\"1\" onclick=\"trspttarif()\"></td><td>Autre : <input type=\"text\" name=\"trsptcoutothername\" size=\"80\"></td><td></td><td><input type=\"text\" name=\"trsptcoutotherval\" value=\"\" size=\"5\"> €</td></tr>
<tr><td></td><td>Total HT (hors taxe gas oil)</td><td></td><td id=\"totaltrspt\"></td></tr>
</table>-->
<br>Informations supplémentaires :<br><textarea cols=\"80\" onkeyup=\"adv_save('infocomplement')\" name=\"infocomplement\"></textarea>
</div>";*/



print "</div>
<input type=\"hidden\" name=\"advid\" value=\"".$adv->id."\">
</form>";

/*
print "<div class=\"advgroup\">
<p>13. Texte de fin</p>
<input type=\"checkbox\" name=\"trsptfinautre\" value=\"1\"> Autre :<br><textarea name=\"otherendtxttrspt\" cols=\"60\" rows=\"3\" onkeyup=\"document.getElementsByName('trsptfinautre')[0].checked=(this.value!='')\"></textarea>
<br><input type=\"radio\" name=\"trsptfin\" value=\"remercie\" checked> Nous vous remercions de votre envoi, par mail ou par fax, de votre confirmation de commande.<br>Cordialement<br>$user_info[nom_contact] - Acilyon - www.ma-cave-a-vin.fr<br>Tél : + 33.4.26.03.05.43 - Fax : + 33.4.26.03.05.49
<br><input type=\"radio\" name=\"trsptfin\" value=\"annule\"> Cordialement<br>$user_info[nom_contact] - Acilyon - www.ma-cave-a-vin.fr<br>Tél : + 33.4.26.03.05.43 - Fax : + 33.4.26.03.05.49
</div>";
*/

?>
