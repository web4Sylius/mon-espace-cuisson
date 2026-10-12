<?php

$rootpath = '/var/www/html/mec/';
$valider_commande          = false;
$valider_paiement_commande = false;
$validersp_commande        = false;
$expedier_commande         = false;
$expediersp_commande       = false;
$annuler_commande          = false;
$supprimer_commande        = false;
$avoir_commande            = false;
$facture_commande          = false;
$confirmation_commande     = false;
$affiche_sav               = true;
$allowclose                = array("cc","philippe","ariel",'jessica');

$aResponsable = array(
 0 => "Aucun",
 1 => "Fournisseur",
 2 => "Transporteur",
 3 => "Installateur",
 4 => "Acilyon",
 5 => "Client"
);

$sql = "SELECT *";
$sql .= " FROM etatfacture";
$sql .= " WHERE valudeid = '".$rs_facture['valide']."'";
$etatfacture = $DB_site2->query_first($sql);
switch($rs_facture['valide']){
     case 0: //Non traitée
     $couleurlien = "#000000";
     break;
     case 1: //Validée
     case 7: //Validéen attente de paiement
     $couleurlien = "#0000FF";
     break;
     case 2: //Refus commerçant
     $couleurlien = "#FF0000";
     break;
     case 3: //Refusée
     $couleurlien = "#FF0000";
     break;
     case 4: //Abandon internaute
     $couleurlien = "#FF0000";
     break;
     case 5: //Expédiée
     case 8: //Expédiée en attente de paiement
     $couleurlien = "#00AA00";
     break;
     case 6: //Remboursée
     $couleurlien = "#FF0000";
     break;
   }

   $sql = "SELECT COUNT(*)";
   $sql .= " FROM envoi_devis";
   $sql .= " WHERE facturid = ".$facturid;
   $envoidevis = $DB_site2->query_first($sql);

//Intitulé pour commande "non aboutie"
   if(($rs_facture['moyenid'] == 1 && $rs_facture['valide'] == 0)){

     $etatfacture[$libelle] = "Non aboutie";

     $supprimer_commande = true;

   }

//Intitulé pour commande "expediée" ou "expédiée en attente de paiement"
if($rs_facture['valide'] == 5 || $rs_facture['valide'] == 8){ // expédiée

 $avoir_commande = true;
     /*list($annee, $mois, $jour) = explode("-", $rs_facture['dateexpedition']);
     //$etat_facture .= "<br>Date d'expédition : <b>$jour/$mois/$annee</b>";
     $dateexpedition = "Date expédition : $jour/$mois/$annee" ;
     $actions = "<input type=\"button\" value=\"Créer un avoir\" onclick=\"window.open('facture.php?action=creer&createfromuser=".$facturid."');\">";*/
     
     $confirmation_commande = true;
     $facture_commande = true;

   } else {

     //Intitulé pour commande analysée par FIANET
     if(in_array("110", $modules) && $rs_facture['moyenid'] == 1){

      /************************************TRAITEMENT FIA NET**************************************/
      $etat_facture .= "<br><a href=\"".$kwixo_url_connect."\" target=\"_blank\">Cliquez ici pour consulter l'analyse FIANET</a>";
      /************************************TRAITEMENT FIA NET**************************************/

     //Intitulé pour commande passée par Kwixo
    } elseif ($rs_facture['moyenid'] == 8 || $rs_facture['moyenid'] == 9){

      $etat_facture .= ""
      . "<br>"
      . "<form name=\"rnpbo\" target=\"_blank\" style=\"display:none;\" method=\"post\" action=\"".$kwixo_url_connect."\">"
      . "<input type=\"hidden\" name=\"login\" value=\"".$kwixo_login."\">"
      . "<input type=\"hidden\" name=\"PWD\" value=\"".$kwixo_passwd."\">"
      . "</form>"
      . "<a onclick=\"document.rnpbo.submit();\">Cliquez ici pour consulter l'analyse R&P</a>";

    }

  }

//Actions pour commande "validée"
if($rs_facture['valide'] == 1){ // Commande validée ou non traité

 $expedier_commande     = true;
 $annuler_commande      = true;
 $confirmation_commande = true;
 
//Actions pour commande par CB non finalisée
} elseif ($rs_facture['valide'] == 0 && $rs_facture['moyenid'] == 1){ //Commande par CB non finalisée

     //Si aucun devis réalisé, on autorise la suppression de la commande
 if($envoidevis[0] == 0){

  $supprimer_commande = true;

}

//Actions pour commande autre que CB, en cours
} elseif($rs_facture['valide'] == 0 && $rs_facture['moyenid'] > 1){

 $valider_commande      = true;
 $annuler_commande      = true;
 $validersp_commande    = true;
 $confirmation_commande = true;

//Actions pour commande "validée en attente de paiement"
} elseif ($rs_facture['valide'] == 7){

 $valider_commande      = true;
 $annuler_commande      = true;
 $expediersp_commande   = true;
 $confirmation_commande = true;

//Actions pour commande "expédiée en attente de paiement"
} elseif($rs_facture['valide'] == 8){

 $valider_paiement_commande = true;
 $avoir_commande            = true;
 $confirmation_commande     = true;

} elseif($rs_facture['valide'] == 0 && $envoidevis[0] == 0){ //Commande non finalisée, sans devis

 $supprimer_commande = true;

}

if($_COOKIE['admin_username'] == 'virginie'){
 $valider_commande = false;
 $valider_paiement_commande = false;
 $validersp_commande = false;
 $expedier_commande = false;
 $expediersp_commande = false;
 $annuler_commande = false;
 $supprimer_commande = false;
 $avoir_commande = false;
 $facture_commande = false;
 $affiche_sav = false;
}

//Accord client pour expédier
if($_POST['action'] == 'exp_accord'){
  if(isset($_POST['accord']) && $_POST['accord'] !== ''){
    $DB_site2->query("UPDATE facture SET exp_accord_client = 1 WHERE facturid='".$facturid."'");
  }
  else{
    $DB_site2->query("UPDATE facture SET exp_accord_client = 0 WHERE facturid='".$facturid."'");
  }
  if(isset($_POST['date_accord'])){
    $DB_site2->query("UPDATE facture SET date_exp_accord_client = ".$_POST['date_accord']." WHERE facturid='".$facturid."'");
  }
  echo "<script language='javascript'>
        document.location='facture.php?action=editer&facturid=".$facturid."';</script>";
}

//Récupération du nombre d'articles remboursés
$sql = "SELECT COUNT(*)";
$sql .= " FROM lignefacture";
$sql .= " WHERE factureid = '".$rs_facture['facturid']."'";
$sql .= " AND remboursement = '1'";
$rembs = $DB_site2->query_first($sql);
if($rembs[0] > 0){
  $txt_remb = "<br><br>&nbsp; <font color=\"#E5840F\">".$rembs[0]." article(s) remboursé(s)</font>" ;
}

//Mise en forme de la date de commande
list ($aaaa, $mm, $jj) = explode("-", $rs_facture['datefacture']);

//Mise en forme de la date d'expédition
$timestampExp = 0;
if($rs_facture['dateexpedition'] != "" && $rs_facture['dateexpedition'] != '0000-00-00'){
 $tabTemp = explode('-', $rs_facture['dateexpedition']);
 $timestampExp = mktime(0,0,0,$tabTemp[1],$tabTemp[2],$tabTemp[0]);
}

//Récupération du mode de livraison
switch($rs_facture['modelivraison']){
 case 0:
 $modelivraison = "ECO (Simple)";
 break;
 case 1:
 $modelivraison = "CONFORT (Installateurs)";
 break;
 case 4:
 $modelivraison = "CONFORT & MISE EN SERVICE (Installateur + Montage)";
 break;
 case 7:
 $modelivraison = "CONFORT & MISE EN SERVICE (Installateur + Montage)";
 break;
 case 5:
 $modelivraison="Spécialisée : ".$rs_facture['txtlivr'];
 break;
}

$incoterms="";
if($rs_facture['incoterms']!="") $incoterms.="<br>Incoterms ".$rs_facture['incoterms'];

//Récupération de l'origine de la commande
switch($rs_facture['provenance']){
 case 'mp':
 $provenance              = $marketplaces[$rs_facture['provenanceid']]." n°".(strlen($rs_facture['mp_facturid']) > 20 ? implode('<br>',str_split($rs_facture['mp_facturid'], 20)) : $rs_facture['mp_facturid']);
 $moyenpaiement[$libelle] = $marketplaces[$rs_facture['provenanceid']];
 break;
 case 'sav':
          //Recherche du détail sur la commande de provenance
          //Si aucun résultat, c'est que c'est une commande de stock => lien différent
 $sql = "SELECT facturid";
 $sql .= " FROM facture";
 $sql .= " WHERE facturid = ".$rs_facture['provenanceid'];
 $aFacture = $DB_site2->query_first($sql);
 if(!$aFacture){
   $provenance = "SAV commande de stock <a href=\"stock.php?action=cmd&stock_cmd_id=".$rs_facture['provenanceid']."\" target=\"_blank\" style=\"margin:0; padding:0;\">".$rs_facture['provenanceid']."</a>";
 } else {
   $provenance = "SAV commande <a href=\"facture.php?action=editer&facturid=".$rs_facture['provenanceid']."\" target=\"_blank\" style=\"margin:0; padding:0;\">".$rs_facture['provenanceid']."</a>";
 }

 
 break;
 case 'avoir':
 $provenance = "Avoir commande <a href=\"facture.php?action=editer&facturid=".$rs_facture['provenanceid']."\" target=\"_blank\" style=\"margin:0; padding:0;\">".$rs_facture['provenanceid']."</a>";
 break;
 default:
 $provenance = "Site ".$template->urlcode($rs_facture['site_code'],$user['langue'])." <img src=\"images/drapeaux/".$user['langue'].".gif\" width=\"20\">";
 break;
}
if($rs_facture['commercial'] != "") $provenance .= " (".ucfirst($rs_facture['commercial']).")";

/*$sql = "SELECT *";
$sql .= " FROM sav";
$sql .= " WHERE facturid = ".$facturid;
$listesav = $DB_site2->query($sql);*/

$sql = "SELECT s.id as sav_id, sf.value as type_litige, s.date as sav_date, s.fin, s.demande_cloture, s.lignes as liste_produits, s.sav_etat, (SELECT value FROM sav_fields WHERE savid = s.id AND name = 'sav_responsable') as sav_responsable, (SELECT value FROM sav_fields WHERE savid = s.id AND name = 'sav_prischarge') as sav_priseencharge, (SELECT value FROM sav_fields WHERE savid = s.id AND name = 'sav_prischargesav') as sav_encharge, s.sav_priorite";
$sql .= " FROM sav_fields sf";
$sql .= " INNER JOIN sav s ON (s.id = sf.savid)";
$sql .= " WHERE s.facturid = ".$facturid;
$sql .= " AND sf.name = 'sav_typelitige'";
$req = $DB_site2->query($sql);
while($ligne = $DB_site2->fetch_array($req)){
 $aSAV[$ligne['sav_id']] = $ligne;
}

//Construction de l'arborescence des SAV
$parent     = getparentsav($facturid);
$links      = savlink($parent);
$savlinks   = savlinkids($parent,$links);
$linkcolors = array("#AAAAAA","#00FFFF","#FFFF00","#00FF00","#FF8888","#FFAA00","#AA00FF","#AA7700","#FFAADD");
$linkcolor  = array();
$i = 0;
foreach($savlinks as $linkid){
 
 if($i >= count($linkcolors)) $i = 0;
 $linkcolor[$linkid] = $linkcolors[$i];
 $i++;
}

if($links!=false) $savtree = drawchild($parent,$links);

$p = strrev(md5($rs_facture['facturid']."devis"));
//Récupération des informations de navigation sur le client de la commande
include_once($rootpath."/class/ccstat.class.php");

$stat = new ccstat();

$visites = $stat->get_visit($rs_facture['userid'], $rs_facture['heurefacture'],'stat',true,$DB_site2);

$affiche_infos = false;
if($visites != false){
 
 $visite  = $visites[0];
 $paysip  = $stat->ip2country($visite->ip);
 $nbpages = 0;
 
 if(count($visite->pages) > 0){
  
  $times = array_keys($visite->pages);
  $d     = date("H:i:s",max($times)-min($times));
  $enter = $visite->pages[min($times)];
  $nbpages = count($visite->pages);
}

$affiche_infos = true;

}

$template->parts["modalclass"] = "modal-devis";
$template->evaltemplate("ModalDevis","modal");
$template->evaltemplate("Overlay","modaltype");
//Récupération de l'encours actuel. S'il est supérieur à l'encours limite, on affiche un message d'erreur
$objFacture = new facture($DB_site2, $rs_facture['userid']);
$url_devis = rewrite_url("",$user['langue'],"/devis.php?devisid=".$rs_facture['facturid']."&p=".$p,$template,$_SERVER['HTTP_HOST']);
//Lien vers devis
$url_devisdetails = rewrite_url("",$user['langue'],"/devis.php?devisid=".$rs_facture['facturid']."&p=".$p."&opt=1",$template,$_SERVER['HTTP_HOST']);
$url_devisdetails = rewrite_url("",$user['langue'],"/devis.php?devisid=".$rs_facture['facturid']."&p=".$p."&opt=1",$template,$_SERVER['HTTP_HOST']);
$url_devisenvoi = "javascript:popdevis(".$rs_facture['facturid'].")";

$lienDevis = "<a href='javascript:void(0)' onclick=\"typemodal('".$url_devis."', '".$url_devisdetails."','".$url_devisenvoi."');\"><img src=\"".BACK_HOST."/images/icon/loupe_detail.png\">Devis</a>";



//Lien vers facture pro forma
$lienFacProForma = "<a href=\"../facture3.php?facturid=".$rs_facture['facturid']."&passe=".$rs_facture['passefacture']."&proforma=1\" target=\"_blank\"><img src=\"".BACK_HOST."/images/icon/loupe_detail.png\">Facture pro forma</a>";

//Lien vers facture
$lienfacture = "";
$lienregenerer = "";
//15550

if (!empty($rs_facture['numerofacture']) && intval($rs_facture['numerofacture']) > $numero_min_facture) {
    $lienfacture = "<a href=\"../facture2.php?facturid=" . $rs_facture['facturid'] . "&passe=" . $rs_facture['passefacture'] . "&nopub=1\" target=\"_blank\">
 <img src=\"" . BACK_HOST . "/images/icon/loupe_detail.png\">Facture (<span style=\"font-size: 10px;\">n°" . $rs_facture['numerofacture'] . "</span>)
 </a>";
    $is_superuser = in_array($user_info['username'], $superusers);
    if ($is_superuser) {
        $lienregenerer .= "<a href=\"facture.php?action=regenererfacture&facturid=" . $rs_facture['facturid'] . "\" target=\"_blank\">regenerer la facture PDF</a<br>";
    }
}

$lienmodif="";
if($rs_facture['valide']!=5 && $rs_facture['valide']!=8 && (in_array($user_info['username'], $superusers) || $user_info['username'] == 'stephane') ){
  $lienmodif="<a href=\"facture.php?action=creer&facturid=".$rs_facture['facturid']."\" target=\"_blank\"><img src=\"".BACK_HOST."/images/icon/edit.png\">Modifier</a>";
}

//Lien vers contrat SFG
$contratsfg = "";
if(file_exists("sfg_pdf/".$rs_facture['facturid'].".pdf")){
 $contratsfg = "<a href=\"sfg_pdf/".$rs_facture['facturid'].".pdf\" target=\"_blank\"><img src=\"".BACK_HOST."/images/icon/loupe_detail.png\">Contrat SFG</a>";
}

//Découpage de l'état
$tab_exlude_etat = array('Non aboutie', 'Non traitée', 'Refus commerçant');
if(strpos($etatfacture[$libelle], ' ') !== false && !in_array($etatfacture[$libelle], $tab_exlude_etat)){
 $pos_espace1 = strpos($etatfacture[$libelle], ' ');
 $etat_principal = substr($etatfacture[$libelle], 0, $pos_espace1);
 $etat_secondaire = substr($etatfacture[$libelle], $pos_espace1);
 $intitule_etat = ""
 . "<span style=\"font-size: 16px;\">".mb_strtoupper($etat_principal, 'UTF-8')."</span>"
 . "<br>"
 . "<span style=\"font-size: 12px;\">".$etat_secondaire."</span>";
} else {
 $intitule_etat = "<span style=\"font-size: 16px;\">".mb_strtoupper($etatfacture[$libelle], 'UTF-8')."</span>";
}

$davis=new DateTime("2 weeks ago");
$davis->setTime(0,0,0);
$dateexp=new DateTime($rs_facture['dateexpedition']);
$htmlavis="";
if(in_array($rs_facture['valide'],array(5,8)) /*&& $davis>=$dateexp*/ && $rs_facture['provenance']==""){
  switch($rs_facture['avis']){
    case 0:
    $info="<div class=\"selectavis\">
    <span class=\"avis0\" onclick=\"selectavis(this.parentNode)\">A traiter</span>
    <div>
    <span class=\"avis2\" onclick=\"envoiavis(2)\">Pas d'envoi</span>
    <span class=\"avis1\" onclick=\"envoiavis(1)\">Envoyer</span>
    </div>
    </div>";
    break;
    
    case 1:
    $info="<span class=\"avis1\">Envoyé</span>";
    break;
    
    case 2:
    $info="<div class=\"selectavis\">
    <span class=\"avis2\" onclick=\"selectavis(this.parentNode)\">Pas d'envoi</span>
    <div>
    <span class=\"avis0\" onclick=\"envoiavis(0)\">A traiter</span>
    <span class=\"avis1\" onclick=\"envoiavis(1)\">Envoyer</span>
    </div>
    </div>";
    break;
  }

  $htmlavis="<tr>
  <td class=\"champs\">Avis client</td>
  </tr>
  <tr>
  <td class=\"valeur\">".$info."</td>
  </tr>";
}

?>
<div class="commande_detail size_normal" id="facture_details" style="">
 <div class="absolute_titre">
  <h2 class="customized">Commande n°<span><?php echo $rs_facture['facturid']; ?></span> <?php if($affiche_infos){ ?><a href="javascript:void(0);" onmouseover="show_element('infos_statistiques');" onmouseout="show_element('infos_statistiques');"><img src="<?php echo SITE_URL; ?>/configurations/<?php echo $host; ?>/images/templates/icone_info.gif"></a><?php } ?></h2>
  <?php if($visite){ ?>
    <div id="infos_statistiques">
     <strong>Entrée sur le site :</strong> <?php echo date("d/m/Y H:i",$visite->timestamp); ?>
     <br>
     <strong>Provenance :</strong> <?php echo $visite->provenance; ?>
     <br>
     <strong>Navigateur :</strong> <?php echo $visite->browser; ?>
     <br>
     <strong>OS :</strong> <?php echo $visite->os; ?>
     <br>
     <strong>FAI :</strong> <?php echo $visite->fai['fai']." ".$visite->ip; ?>
     <br>
     <strong>Pays :</strong> <?php echo ($paysip != "" ? "<img src=\"images/drapeaux/".strtoupper($paysip).".gif\" width=\"15\" style=\"vertical-align: middle;\">" : "?"); ?>
     <br>
     <strong>Pages vues :</strong> <?php echo $nbpages; ?>
     <br>
     <strong>Page d'entrée :</strong> <?php echo $enter; ?>
     <br>
     <strong>Temps passé :</strong> <?php echo $d; ?>
   </div>
 <?php } ?>
 <span class="txt_13">Passée le <strong><?php echo $rs_facture['heurefacture'] > 0 ? date("d/m/Y à H:i", $rs_facture['heurefacture']) : $rs_facture['datefacture']; ?></strong></span>
 <?php if(($rs_facture['valide'] == 5 || $rs_facture['valide'] == 8)){ ?>
  <br>
  Expédiée le <strong><?php echo date("d/m/Y", $timestampExp); ?></strong>
<?php } ?>
<br>
<input type="hidden" id="accroche_div" name="input_inutile">
</div>
<div class="infos_cmd">
  <table>
   <tr>
    <td class="champs">N° commande client</td>
  </tr>
  <tr>
    <td class="valeur">
     <span id="clientcde"><?php echo $rs_facture['clientcde']; ?> <img src="images/editer.gif" onclick="document.getElementById('clientcde').style.display='none'; document.forms.clientcdeform.style.display='inline';" style="cursor: pointer;"></span>
     <form id="clientcdeform" name="clientcdeform" method="post">
      <input type="text" name="cdeclient" value="<?php echo $rs_facture['clientcde']; ?>">
      <input type="submit" value="OK">
    </form>
  </td>
</tr>
<tr>
  <td class="champs">Règlement</td>
</tr>
<tr>
  <td class="valeur">
   <?php echo $moyenpaiement[$libelle]; ?>
   <?php if($moyenpaiement[$libelle] == 'Sofinco'){ ?>
    <img src="/configurations/<?php echo $host; ?>/images/templates/icone_info.gif" style="width: 20px; vertical-align: -3px; cursor: pointer;" onmouseover="show_element('infos_reglement')" onmouseout="show_element('infos_reglement')">
    <div id="infos_reglement">
     <strong>N° dossier</strong> : <?php echo $rs_facture['num_dossier']; ?>
   </div>
 <?php } ?>
</td>
</tr>
<tr>
  <td class="champs">Livraison</td>
</tr>
<tr>
  <td class="valeur"><?php echo $modelivraison.$incoterms; ?></td>
</tr>
<tr>
  <td class="champs">Origine</td>
</tr>
<tr>
  <td class="valeur">
   <?php echo $provenance; ?>
   <?php if($rs_facture['provenance'] == 'mp'){ ?>
    <img src="images/editer.gif" style="cursor: pointer;" onclick="document.forms.formnumcommande.style.display='inline';"></span><br>
    <form id="formnumcommande" name="formnumcommande" method="post" style="display:none">
     <input type="text" name="numcommande" placeholder="N° commande marketplace" value="<?php echo $rs_facture['mp_facturid']; ?>">
     <input type="submit" value="OK">
   </form>
 <?php } ?>
</td>
</tr>
<?php echo $htmlavis; ?>
</table>
</div>

<?php if($supprimer_commande){ ?>
 <a href="javascript:void(0)" onclick="if(confirm('Supprimer la commande n°<?php echo $rs_facture['facturid']; ?> ?')){ location.href = '?action=cacher&facturid=<?php echo $rs_facture['facturid']; ?>&lst_trietat='; }" class="delete_cmd" title="Supprimer"><img src="<?php echo BACK_HOST; ?>/images/icon/erreur3.png" alt="Supprimer"></a>
<?php } 
$sql = "SELECT *";
$sql .= " FROM envoi_fournisseur";
$sql .= " WHERE facturid = ".$facturid." AND destinataire!='0'";
$ann = $DB_site2->query_first($sql);

?>

<div class="commande_actions" style="position: absolute;left: 391px;top: 65px;">
  <p style="background-color: <?php echo $couleurlien; ?>;"><?php echo $intitule_etat; ?></p>
  <div class="liste_actions">
   <?php if($valider_commande || $validersp_commande || $valider_paiement_commande || $expedier_commande || $expediersp_commande || $annuler_commande){ ?>
     <form action="facture.php" method="post" name="action_cmd" onchange="validationFacture()">
      <input type="hidden" name="facturid" value="<?php echo $facturid; ?>">
      <select id="action_option" name="action" >
       <option value="">--- Changer le statut ---</option>
       <?php if($valider_commande){ ?><option style="background-color: blue;" value="valider">Valider le règlement</option><?php } ?>
       <?php if($validersp_commande){ ?><option style="background-color: blue;" value="validersp">Valider sans paiement</option><?php } ?>
       <?php if($valider_paiement_commande){ ?><option style="background-color: blue;" value="paiement">Valider le règlement</option><?php } ?>
       <?php if($expedier_commande){ ?><option style="background-color: green;" value="expedier">Expédier</option><?php } ?>
       <?php if($expediersp_commande){ ?><option style="background-color: green;" value="expediersp">Expédier sans paiement</option><?php } ?>
       <?php if($annuler_commande){ ?><option style="background-color: red;" value="refuser">Annuler</option><?php }?>
     </select>
     <input type="hidden" id="typedefacture" value="<?php echo $ann['type'];?>">
   </form>
 <?php }?>
</div>
</div>

<div class="lien_fichiers">
  <?php echo $lienDevis; ?>
  <?php echo $lienFacProForma; ?>
  <div style="clear: both;"></div>
  <?php echo $lienfacture . '--'; ?>
  <?php echo $lienregenerer; ?>
  <?php echo $contratsfg; ?>
  <?php echo $lienmodif; ?>a
  <?php if($avoir_commande){ ?>
   <a href="facture.php?action=creer&createfromuser=<?php echo $facturid; ?>" target="_blank" class="bouton" style="margin: 30px 0 0;"><img src="<?php echo BACK_HOST; ?>/images/icon/pieces.png">Créer un avoir</a>
 <?php } ?>
 <?php
 if($facture_commande){
   ?>
   <form action="facture.php" method="post" id="facture_cmd" name="facture_cmd">
    <input type="hidden" name="action" value="mailfacture">
    <input type="hidden" name="facturid" value="<?php echo $facturid; ?>">
    <a href="javascript:void(0)" class="bouton" onclick="if(mailfacture()) document.forms.facture_cmd.submit();"><img src="<?php echo BACK_HOST; ?>/images/reply_all.png">Renvoyer facture</a>
  </form>
  <?php
}
?>
</div>

<div class="commande_actions" style="position: absolute;top: 105px;right: 155px;">
 <?php if($confirmation_commande){ ?>
   <form action="facture.php" method="post" id="confirmation_cmd" name="confirmation_cmd">
     <input type="hidden" name="action" value="mailconfirmation">
     <input type="hidden" name="facturid" value="<?php echo $facturid; ?>">
     <a href="javascript:void(0)" class="actions confirmer" onclick="if(mailconfirm()) document.forms.confirmation_cmd.submit();">Renvoyer l'email<br>de confirmation</a>
   </form>
 <?php } ?>	
</div>
<div class="clear"></div>
<!-- Origine attribuee -->
<div class="origine_attribuee">
  <?php
  echo "Origine attribuée : ";
  if($rs_facture['provenance'] == 'mp'){
    echo "Marketplace";
  } elseif($rs_facture['provenance'] == 'sav' || $rs_facture['provenance'] == 'avoir'){
    echo "SAV";
  }else{
    $is_superuser = in_array($user_info['username'], $superusers);
    $is_supercom = in_array($user_info['username'], $supercom);
    if($is_superuser || $is_supercom){
      if($rs_facture['commercial_attribution']==""){
        ?>
        <form name="attribution" id="attribution" method="post">
          <input type="hidden" name="action" value="attribution">
          <input type="hidden" name="facturid" value="<?php echo $facturid; ?>">
          <select name="commercial_attribution">
            <option value="">-- Sélectionner --</option>
            <option value="direct">Direct</option>
            <?php
            $commercials=$DB_site2->query(" SELECT username FROM parametresgeneraux WHERE commissions ='1' ORDER BY username ASC ");
            while($commercial=$DB_site2->fetch_array($commercials)){
              echo "<option value=\"".$commercial['username']."\">".UCfirst($commercial['username'])."</option>";
            }
            ?>
          </select><br>
          <input type="submit" value="valider">
        </form>
        <?php
      } else {
        echo UCfirst($rs_facture['commercial_attribution']);
        ?>
        <form name="attribution" id="attribution" method="post">
          <input type="hidden" name="action" value="attribution">
          <input type="hidden" name="facturid" value="<?php echo $facturid; ?>">
          <input type="hidden" name="commercial_attribution" value="">
          <input type="submit" value="vider">
        </form>
        <?php
      }
    } else {
      if($rs_facture['commercial_attribution']==""){
        echo "Non attribué";
      } else {
        echo UCfirst($rs_facture['commercial_attribution']);
      }
    }
  }
  ?>
</div>

  <?php // Lier la commande avec un projet. Ne s'affiche que pour les commandes avec l'état validé && pour les personnes avec permission
  if(check_projets_exist($DB_site2, $rs_facture['userid']) == true && check_commande_valide($DB_site2, $rs_facture['facturid']) == true) {

    $usersLierProjet = array("philippe","ariel","benoit", "jessica", "alexis","jbandre");
    $is_superuserProjet = in_array($user_info['username'], $usersLierProjet);
    if($is_superuserProjet) {
      ?>
      <div class="projets_attribues" style="position: absolute;top: 146px; left: 205px;">
       <?php
       $projet=$DB_site2->query_first("SELECT projetid, facturid, commercial FROM projet WHERE facturid = ".$rs_facture['facturid']);
       if($projet['facturid'] != '') {
        echo "Commande liée avec projet <a href='projet.php?projetid=" . $projet['projetid']."'>". $projet['projetid']."</a><br/>- ".$projet['commercial']."<br/>
        <form method='post'>
        <input type='hidden' value='delier-projet' name='action'/>
        <input type='hidden' value=".$projet['facturid']." name='facturid'/>
        <input type='hidden' value=".$projet['projetid']." name='projet-id'/>
        <button value='0' name='delier-ok'>Délier la commande</button>
        </form><br/>";
      }
      else {
        ?>
        <form method="post">
         <input type='hidden' value='lier-projet' name='action'/>
         <input type='hidden' value="<?php echo $rs_facture['facturid'];?>" name='facturid'/>
         <select name="liste_projets" style="width: 110px">
          <option value="">-- Projets --</option>";
          <?php
          function tri($a, $b){ // tri par date desc
            if ($a->date == $b->date) {
              return 0;
            }
            return ($a->date > $b->date) ? -1 : 1;

          }
          $fac=new facture($DB_site2,$_GET['facturid']);
          $client=$fac->utilisateur;
          $projets=new projets();
          $clprojets=$client->projets($projets->encours);
          $projetsclos=$client->projets($projets->clos);
          if(count($clprojets)>0) {
            usort($clprojets,"tri");
            echo "<option value=''>En cours :</option>";
          }
          foreach($clprojets as $projet){
            if($projet->facturid==0){
              $style="";
            }
            else {
              $style="background-color:#a7e1b9;";
            }
          echo "<option style=\"".$style."\" value=\"".$projet->projetid."\">".($projet->facturid==0?'':'CDE'.$projet->facturid.' - ')." PJT ".$projet->projetid." du ".$projet->date->format("d/m/Y")." - ".substr(UCfirst($projet->libelle), 0, 120)."... - ".$projet->commercial."</option>";
          }
          if(count($projetsclos)>0) {
            usort($projetsclos,"tri");
            echo "<option value=''>Clos :</option>";
          }
          foreach($projetsclos as $projet){
            if($projet->facturid==0){
              $style="";
            }
            else {
              $style="background-color:#a7e1b9;";
            }
            echo "<option style=\"".$style."\" value=\"".$projet->projetid."\">".($projet->facturid==0?'':'CDE'.$projet->facturid.' - ')." PJT ".$projet->projetid." du ".$projet->date->format("d/m/Y")." - ".substr(UCfirst($projet->libelle), 0, 60)."</option>";
          }
          ?>
        </select><br/>
        <input type="submit" name="lier_cmd" value="Lier la commande au projet"/>
      </form>
    <?php }     
    ?>
  </div>
<?php }
}
?>
<div class="exp_accord">
  <form>
  <div title="si coché, empêche l'expédition sans accord du client">Expédition/accord client : <input type="checkbox" name="accord_client" value="accord" onclick="exp_accord_client(this)" <?php echo ($rs_facture['exp_accord_client']==1?'checked':''); ?>></div>
    Accord client le : <?php echo ($rs_facture['date_exp_accord_client'] > 0 ? date("d/m/Y",$rs_facture['date_exp_accord_client']) : '') ." <img src=\"images/editer.gif\" align=\"absmiddle\" title=\"Modifier\" onclick=\"calendrier_show(undefined, this.value,false,exp_accord_client);\">";
   ?>
</form>
</div>
<div>
 <?php if($rs_facture['datelivraison'] > 0){ ?>
   <p class="date_livraison_new">Livraison <?php echo $rs_facture['changelivraison'] == "" ? "annoncée" : ''; ?> le <strong><?php echo date("d/m/Y",$rs_facture['datelivraison']); ?> </strong><?php echo $rs_facture['changelivraison'] != "" ? '(modifiée par '.$rs_facture['changelivraison'].')' : ''; ?> <img src="images/editer.gif" align="absmiddle" title="Modifier" onclick="calendrier_show(undefined, '<?php echo ($rs_facture['datelivraison'] > 0 ? date("d/m/Y",$rs_facture['datelivraison']) : date("d/m/Y")); ?>', false, changedatelivr, 'accroche_div');"></p>
 <?php } else { ?>
   <p class="date_livraison_new">Aucune date de livraison annoncée <img src="images/editer.gif" align="absmiddle" title="Modifier" onclick="calendrier_show(undefined, '<?php echo ($rs_facture['datelivraison'] > 0 ? date("d/m/Y",$rs_facture['datelivraison']) : date("d/m/Y")); ?>', false, changedatelivr, 'accroche_div');"></p>
   <?php 
 }

 /*****fonction pour lier une commande a une autre commande en SAV manuellement*****/
 $usersLierSav = array("philippe","qualite","adv","ariel","cc","benoit", "jessica","jbandre");
 $is_superuser = in_array($user_info['username'], $usersLierSav);
 $laFacture = $DB_site2->query_first("SELECT provenance, provenanceid FROM facture WHERE facturid='$facturid'");
 if(isset($_GET['liersav'])){
  switch ($_GET['liersav']) {
    case '0':
    $msg = "La liaison a bien été effectuée";
    // si la commande comporte des garanties SFG, envoyer un mail :
    $garantie = $DB_site2->query("SELECT lfg.lignefactureid FROM lignefacturegarantie lfg INNER JOIN lignefacture lf ON lf.lignefactureid = lfg.lignefactureid INNER JOIN facture f ON lf.factureid = f.facturid WHERE f.facturid ='".$facturid."' AND lfg.fournisseur = 'SFG'");
    if($DB_site2->num_rows($garantie) > 0) {
        //Envoi du mail
        $mail =new email();
        $mail->expediteur   = "contact@mon-espace-cuisson.com";
        $mail->expname      = "contact@mon-espace-cuisson.com";
        if(ESPACE == 'prod') {
        $mail->destinataire = "contact@mmon-espace-cuisson.com";
        }
        else {
        $mail->destinataire = "benoit@mon-espace-cuisson.com";
        }
        $mail->copymode     = "bcc";
        $mail->to           = array("adv@mon-espace-cuisson.com","benoit@mon-espace-cuisson.com");
        $mail->subject      = "Attention Contrat SFG sur #CDE".$_GET['facturid']."# a controler";
        $mail->body         = "attention commande SAV N° ".$_GET['facturid']." comporte des contrats SFG, a controler";
        $mail->sendmail();
    }
// fin du mail pour SFG
    break;
    case '1':
    $msg = "Cette commande est déjà liée à une autre cmd par SAV" ;
    break;  
    case '2':
    $msg = "Cette facture est un avoir sur une autre facture. Elle ne peut être lié a une autre";
    break;
    case '3':
    $msg = "Cette facture est déjà liée à une marketplace";
    break;
    case '5':
    $msg = "La facture saisie n'existe pas";
    break;
  }
  echo "<script>alert('".$msg."');</script>";
  echo "<script>window.location.href = '/admin/facture.php?action=editer&facturid=".$facturid."';</script>";
}
if($is_superuser && $laFacture['provenance'] == ''){
  ?>
  <span><b>Cmde <?php echo $facturid; ?></b> à lier au rang 2 de la cmde </span>
  <form id="lierSav" name="lierSav" method="post" style="display: inline;">
   <input type="hidden" name="action" value="liersav">
   <input type="hidden" name="facturid" value="<?php echo $facturid; ?>">
   <input type="text" name="commandeAlier" value="" onkeyup="liersavsrc(this.value)" style="width: 70px;">
   <button id="btnliersav" value="0" onclick="liersav(this.value)">OK</button>
 </form>
 <div id="liersavclient"></div>
 <?php
 /***** FIN fonction pour lier une commande a une autre commande en SAV manuellement*****/
}
        //// fonction pour délier une commande ////
if(isset($_POST['ok-delier'])){
  $DB_site2->query("UPDATE facture SET provenanceid = '', provenance = '' WHERE facturid='".$facturid."'");
  echo "<script>alert(\"La commande a bien été délié avec la commande ".$laFacture['provenanceid']."\");</script>";
  echo "<script>window.location.href = '/admin/facture.php?action=editer&facturid=".$facturid."';</script>";
}
if($is_superuser && $laFacture['provenance'] != ''){
  ?>
  <div style="text-align: center">
   <span>Cette commande est liée à la commande <?php echo $laFacture['provenanceid']?> : </span>
   <form id="delierSav" name="delierSav" method="post">
    <button id="btndeliersav" value="0" name="ok-delier">Délier la commande</button>
  </form>
</div>
<?php 
} ?>
</div>
</div>

<div class="commande_detail size_normal" id="facture_details_2" style="">
	<table class="sav_detail">
   <?php
   if(!empty($aSAV)){
    echo "<div style=\"width: 680px; padding-left:10px;\">
    <div style=\"width: 680px; float:left;\">";
    $cpt = 1;
    $nb_sav=count($aSAV);
    foreach($aSAV as $sav_id => $sav_infos){
     $sql_typelitige = $DB_site2->query_first("SELECT * FROM sav_typelitige WHERE typelitigeid='".$sav_infos['type_litige']."' ");
     $sav_type=$sql_typelitige[libelle];
     switch($sav_infos['type_litige']){
      case 0:
      $sav_responsable = $sav_infos['sav_responsable'];
      break;
      case 1:
      $sav_responsable = $sav_infos['sav_priseencharge'] == 2 || $sav_infos['sav_priseencharge'] == 3 ? 'SFG' : $sav_infos['sav_encharge'];
      break;
      case 2:
      $sav_responsable = $aResponsable[$sav_infos['sav_responsable']];
      break;
      case 3:
      $sav_responsable = "";
      break;
      case 4:
      $sav_responsable = $aResponsable[$sav_infos['sav_responsable']];
      break;
      case 6:
      $sav_responsable = $sav_infos['sav_responsable'];
      break;
      case 7:
      $sav_responsable = $sav_infos['sav_responsable'];
      break;
    }

    $sav_reference = '';
    $aExplode      = explode(',', $sav_infos['liste_produits']);
    foreach($aExplode as $le_pdt){
      $aPdt = explode('-',$le_pdt);
      if($aPdt[0] == 1){
       $sql = "SELECT lf.artcode, a.artcode_eurocave";
       $sql .= " FROM lignefacture lf";
       $sql .= " LEFT JOIN article a ON (a.artid = lf.artid)";
       $sql .= " WHERE lf.lignefactureid = ".$aPdt[1];
       $aProduit = $DB_site2->query_first($sql);
       $sav_reference .= ($sav_reference != '' ? '<br>' : '').$aProduit['artcode'].(strlen($aProduit['artcode_eurocave']) <= 20 ? ' ('.$aProduit['artcode_eurocave'].')' : '');
     }
   }

   if($sav_infos['fin'] != '0'){
    $bgcolor = '#00AA00';
  } else if($sav_infos['demande_cloture'] > 0){
    $bgcolor = '#ff9600';
  } else {
    $bgcolor = '#ad0000';
  }
  $allow_del_sav = array("cc","philippe","ariel","benoit","jessica","jbandre");
  
  $sql_typelitige = $DB_site2->query_first("SELECT * FROM sav_typelitige WHERE typelitigeid='".$sav_infos['type_litige']."' ");
  $sav_type=$sql_typelitige['libelle'];
  
  $displaybloc="none";
  if($sav_open!=""){
    if($sav_open==$sav_infos['sav_id']){
     $displaybloc="block";
   }
 } else {
  if($cpt==$nb_sav){
   $displaybloc="block";
 }
}
?>
<tr>
 <td class="intitule" style="background-color: <?php echo $bgcolor; ?>;">
   <span onclick="jQuery('#sav_<?php echo $sav_infos['sav_id']; ?>').toggle();" style="color: white;font-weight: 600;">SAV N° #SAV<?php echo $rs_facture['facturid'];?>-<?php echo $sav_infos['sav_id']; ?># du <?php echo date("d/m/Y",$sav_infos['sav_date']); ?></span> 
   <?php if(in_array($_COOKIE['admin_username'], $allow_del_sav)){ ?>
     <img src="images/icon/erreur_14.png" onclick="del_sav(<?php echo $sav_infos['sav_id']; ?>, <?php echo $facturid; ?>)">
   <?php } ?>
 </td>
</tr>
<tr>
  <td>
    <?php if(($sav_infos['demande_cloture'] == 0 || !isset($sav_infos['demande_cloture'])) && in_array($user_info['username'],array('adv','philippe','ariel','jessica')) && $sav_infos['fin'] == 0){  ?>
    <form method="post" name="cloture_rapide" style="text-align: center;" action="facture.php?action=editer&facturid=<?php echo $facturid; ?>&savid=<?php echo $sav_infos['sav_id']; ?>">
      <input type="hidden" name="step" value="cloture_rapide">
      <input type="hidden" name="action_sav" value="precloture">
      <input type="hidden" name="savid" value="<?php echo $sav_infos['sav_id']; ?>">
      <input type="hidden" name="date_fin" value="<?php echo ($sav_infos['fin']>0?$sav_infos['fin']:0); ?>">
      <input type="button" class="cloture_demande" onclick="cloture_demande_rapide();" value="Demander la clôture" name="precloture">
    </form>
    <?php } ?>
  <?php if(($sav_infos['fin'] == 0 || !isset($sav_infos['fin'])) && in_array($user_info['username'],$allowclose) && $sav_infos['demande_cloture'] != 0){ ?>
    <form method="post" name="cloture_rapide" style="text-align: center;" action="facture.php?action=editer&facturid=<?php echo $facturid; ?>&savid=<?php echo $sav_infos['sav_id']; ?>">
      <input type="hidden" name="step" value="cloture_rapide">
      <input type="hidden" name="action_sav" value="cloture" id="action_cloture">
      <input type="hidden" name="savid" value="<?php echo $sav_infos['sav_id']; ?>">
      <input type="hidden" name="date_fin" value="<?php echo ($sav_infos['fin']>0?$sav_infos['fin']:0); ?>">
      <input type="button" class="cloture_demande" onclick="if(confirm('Clôturer le litige ?')){ document.forms['cloture_rapide'].submit();}" value="Clôturer" name="cloture">
      <input type="button" class="cloture_demande" style="background-color:red;" onclick="if(confirm('Annuler la demande de clôture ?')){ document.getElementById('action_cloture').value = 'cloture_annule';document.forms['cloture_rapide'].submit();}" value="Annuler" name="annule">
    </form>
      
  <?php } if($sav_infos['fin'] != 0) { ?>
  <p style="color:red;text-align: center;">Clôturé le <?php echo date("d/m/Y H:i",$sav_infos['fin']); ?></p>
  <?php } ?>
  </td>
</tr>
<tr>
 <td class="contenu" style="display: <?php echo $displaybloc; ?>;" id="sav_<?php echo $sav_infos['sav_id']; ?>">
   <table>
    <tr>
     <td width="500" style="padding-left:10px;">
       <?php
       $max_menu = $DB_site2->query_first("SELECT MAX(m.ordre) FROM sav_menu AS m
        INNER JOIN sav_etape AS e USING(menuid)
        INNER JOIN sav_resume AS r USING(etapeid)
        WHERE r.savid='".$sav_infos['sav_id']."' ");
       if($max_menu[0]==""){
        $max_menu[0]=0;
      }
      $menus = $DB_site2->query("SELECT * FROM sav_menu WHERE typelitigeid='".$sav_infos['type_litige']."' ORDER BY ordre ASC");
      $i=0;
      while ($menu = $DB_site2->fetch_array($menus)) {
        $i++;
        echo "<strong>".$i."/ ".$menu[libelle]." : </strong>\n";
        $etapes = $DB_site2->query("SELECT r.resumeid, r.dateline, r.datecreation, e.libelle FROM sav_resume AS r
         INNER JOIN sav_etape AS e USING(etapeid)
         WHERE r.savid='".$sav_infos['sav_id']."'
         AND e.menuid='".$menu['menuid']."'
         ORDER BY r.dateline ASC");
        while ($etape = $DB_site2->fetch_array($etapes)) {
         echo "<br>- ".$etape['libelle']." : ".date("d/m/Y",$etape['dateline']);
         if($etape['datecreation']>time()-(48*60*60)){
          echo "<form name=\"sav_resume_del_".$etape['resumeid']."\" id=\"sav_resume_del_".$etape['resumeid']."\" method=\"post\" style=\"display:inline;\">
          <input type=\"hidden\" name=\"action\" value=\"sav_resume_del\">
          <input type=\"hidden\" name=\"resumeid\" value=\"".$etape['resumeid']."\">
          <input type=\"hidden\" name=\"facturid\" value=\"".$facturid."\">
          </form><a href=\"javascript:document.getElementById('sav_resume_del_".$etape['resumeid']."').submit();\"><img src=\"images/icon/erreur_14.png\"></a>";
        }
      }
				// if($menu['ordre']==$max_menu[0] || $menu['ordre']==$max_menu[0]+1){
      
				//} else {
      if($menu['ordre']>$max_menu[0]+1){
        echo " => en attente d'une étape précédente.<br>";
      } else {
						// echo "<br>";
        echo "	&nbsp; <a href=\"javascript:void(0);\" onclick=\"jQuery('#sav_".$sav_infos['sav_id']."_".$menu['menuid']."').show();\">+</a>
        <form name=\"sav_resume_add\" method=\"post\" id=\"sav_".$sav_infos['sav_id']."_".$menu['menuid']."\" style=\"display:none;\">
        <input type=\"hidden\" name=\"action\" value=\"sav_resume_add\">
        <input type=\"hidden\" name=\"facturid\" value=\"".$facturid."\">
        <input type=\"hidden\" name=\"savid\" value=\"".$sav_infos['sav_id']."\">
        <select name=\"etapeid\">
        <option value=\"\">-- Nouvelle étape --</option>";
        
        $liste_etapes=$DB_site2->query(" SELECT * FROM sav_etape WHERE menuid='".$menu['menuid']."' ORDER BY ordre ASC ");
        while($liste_etape=$DB_site2->fetch_array($liste_etapes)){
         echo "<option value=\"".$liste_etape['etapeid']."\">".$liste_etape['libelle']."</option>";
       }
       echo "		</select>
       <input name=\"dateline\" type=\"date\" value=\"".date("Y-m-d")."\">
       <input type=\"submit\" value=\"ajouter\">
       </form><br>";
     }
				//}
   }

   $details_sav = $DB_site2->query_first("SELECT * FROM sav_fields WHERE savid='".$sav_infos['sav_id']."' AND name='sav_detail1-verif' ");
			$nbserie_sav = $DB_site2->query_first("SELECT * FROM sav_fields WHERE savid='".$sav_infos['sav_id']."' AND name='sav_nbserie_verif' "); // sav_nbserie_verif
			$ndossier_sav = $DB_site2->query_first("SELECT * FROM sav_fields WHERE savid='".$sav_infos['sav_id']."' AND name='sav_ndossier' "); // sav_ndossier
			
			?>
   </td>
   <td width="480" style="padding-right:10px;">
    <div>
     
     <div class="titre" style="float:left; width:250px; text-align:right; font-size:12px;"><strong><?php echo $sav_type; ?></strong></div>
     <img style="float:left; width=20px; margin-left:20px; margin-top:2px; position:relative;" onclick="location.href='facture.php?action=editer&facturid=<?php echo $facturid; ?>&savid=<?php echo $sav_infos['sav_id']; ?>#sav'" src="<?php echo BACK_URL; ?>/images/icon/edit.png">
     <div class="clear"></div>
     <strong>Détail précis</strong> : <?php echo $details_sav[value] != '' ? $details_sav[value] : 'N/A'; ?><br>
     <p><strong>Responsable</strong> : <?php echo $sav_responsable != '' ? $sav_responsable : 'N/A'; ?></p>
     <p><strong>Référence(s)</strong> : <?php echo $sav_reference != '' ? $sav_reference : 'N/A'; ?></p>
     <p><strong>N° série</strong> : <?php echo $nbserie_sav[value] != '' ? $nbserie_sav[value] : 'N/A'; ?></p>
     <p><strong>N° dossier SAV</strong> : <?php echo $ndossier_sav[value] != '' ? $ndossier_sav[value] : 'N/A'; ?></p>
     <p class="note">
      <textarea id="statut_sav<?php echo $sav_infos['sav_id']; ?>" placeholder="Etat actuel du SAV" style="width:400px; height:60px;"><?php echo $sav_infos['sav_etat']; ?></textarea>
      <input type="button" value="Enreg." style="margin-top:15px;" onclick="save_etat(<?php echo $sav_infos['sav_id']; ?>)">
    </p>
    <div class="clear"></div>
    <p class="sav_priorite"><strong>SAV prioritaire :</strong> <input type="radio" name="sav_prioritaire<?php echo $sav_infos['sav_id']; ?>" value="1" <?php echo $sav_infos['sav_priorite'] == 1 ? 'checked' : ''; ?> onclick="setSavPrio(<?php echo $sav_id; ?>, this.value);" style="vertical-align: -1px;"> Oui <input type="radio" name="sav_prioritaire<?php echo $sav_infos['sav_id']; ?>" value="0" <?php echo $sav_infos['sav_priorite'] == 0 ? 'checked' : ''; ?> onclick="setSavPrio(<?php echo $sav_id; ?>, this.value);" style="vertical-align: -1px;"> Non</p>
    <p class="message" id="maj_txt<?php echo $sav_infos['sav_id']; ?>">Informations mises à jour</p>
    <div class="clear"></div>
  </div>
</td>
</tr>
</table>
</td>
</tr>
<?php 
$cpt++;
}
}
echo "</table>";

?>
<div style="text-align:center;">
 <?php
 echo $savtree;
 ?>
</div>
<div class="clear"></div>
<div class="lien_actions" >
  <?php if($affiche_sav){ ?>
    <a href="javascript:void(0)" onclick="show_element('newsav')" class="bouton"><img src="<?php echo BACK_HOST; ?>/images/icon/sav_picto2.png">Nouveau SAV</a>
    <form name="newsav" id="newsav" method="get" style="display:none;" action="#sav">
     <input type="hidden" name="action" value="editer">
     <input type="hidden" name="facturid" value="<?php echo $facturid; ?>">
     <input type="hidden" name="savid" value="0">
     <select name="typelitige" onchange="check_select_value(this);">
      <option value="">-- Sélectionner le type de litige --</option>
      <?php
      $sql_typelitiges = $DB_site2->query("SELECT * FROM sav_typelitige ORDER BY ordre ASC ");
      while($sql_typelitige = $DB_site2->fetch_array($sql_typelitiges)){
        echo "<option value=\"".$sql_typelitige['typelitigeid']."\">".$sql_typelitige['libelle']."</option>";
      }
      ?>
    </select>
  </form>
  <?php 
}
?>
</div><div class="clear"></div>
</div>
<div style="clear:both;"></div>
