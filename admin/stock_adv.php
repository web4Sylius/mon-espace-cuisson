<?php

include_once("adv/adv.class.php");
include_once("../class/offre_fournisseur.php");
include_once("../class/facture.class.php");

if(isset($_POST['actionadv'])){
    include("stock_adv2.php");

}

print "<a name=\"adv\"></a>
<style>
.tab_on a { background-color:white; border:solid thin black; text-align:center; text-decoration:none; padding:10px; }
.tab_off { padding-top:10px; }
.tab_off:hover { padding-top:0px; }
.tab_off a { background-color:#e4e4e4; border:solid thin #cccccc; text-align:center; text-decoration:none; padding:10px; }
.advgroup { background-color:#f4f4f4; margin-bottom:10px; font-size:12px; text-align:left; }
.advgroup p { margin:6px; color:white; font-weight:bold; font-size:14px; background-color:#AA0000; }
.advgroup table { font-size:12px; border-collapse:collapse; width:100%; }
.advgroup td { border-top:solid thin #808080; padding-top:5px; padding-bottom:5px; }
#stepfourn2,#stepfourn3,#stepfourn4,#stepfourn5,#stepfourn6,#stepfourn7 { display:none; }
.caracts td { height:20px; border:none; }
.roinput { border:solid thin black; background:none; }
#fournbtn { height:20px; background:white; text-align:center; }
#steptrspt2,#steptrspt3,#steptrspt4,#steptrspt5,#steptrspt6,#steptrspt6,#steptrspt7,#steptrspt8,#steptrspt9,#steptrspt10,#steptrspt11,#steptrspt12 { display:none; }
#stepsls2,#stepsls3,#stepsls4,#stepsls5,#stepsls6,#stepsls6,#stepsls7,#stepsls8,#stepsls9,#stepsls10,#stepsls11 { display:none; }
</style>";

$listartid=array();
$adv=new adv($DB_site2);

if(isset($_GET['deladv'])){
    $deladv=new adv($DB_site2,$_GET['deladv']);
    $deladv->delete();
}

if(isset($_GET['deladvcmd'])){
    include_once("adv/envoi_fournisseur.php");
    $ef=new envoi_fournisseur($DB_site2,$_GET['deladvcmd']);
    $ef->delete();
    $adv->cancel_efid($_GET['deladvcmd']);
}

/// tâche 6882 : permet de valider l'enlèvement des items, pour ne plus qu'ils apparaissent dans "suivi des exp" ///
if(isset($_POST['valid_enlev'])){
    $cmd=new stock_cmd($DB_site2,$_POST['modifcmdid']);
    $artids=explode(",",$_POST['itemsartid']);
    $qte=explode(",",$_POST['itemsartqte']);
    $nb=0;
    $suivitxt="";
    foreach($artids as $artid) {
        $items=$cmd->getitems($artid,0);
        $i=0;
        foreach($items as $item) {
            if($i==$qte[$nb]) break;
            if($item->enlevement_fait=='1'){
                $item->enlevement_fait='0';
                $item->update();
                $coche="Les produits suivants ont été décochés pour l'enlèvement :";
            }
            else {
                $item->enlevement_fait='1';
                $item->update();
                $coche="Les produits suivants ont été cochés pour l'enlèvement :";
            }
            $i++;
        }
        $suivitxt.=" - artid: ".$artid. " x".$qte[$nb];
        $nb++;
    }
    $suivi=new stock_suivi($DB_site2);
    $suivi->stock_cmd_id=$_POST['modifcmdid'];
    $suivi->commercial=$user_info['username'];
    $suivi->suivi=$coche.$suivitxt;
    $suivi->create();
}
/// fin ///

if(isset($_GET['ar']) && isset($_POST['ar_valide'])){
    $ar=new adv_ar($DB_site2);
    $ar->advid=$_GET['ar'];
    $d=date_create_from_format("d/m/Y",$_POST['ar_date']);
    $ar->date_ar=$d->getTimestamp();
    $d=date_create_from_format("d/m/Y",$_POST['ar_date_recu']);
    $ar->date=$d->getTimestamp();
    $ar->utilisateur=$user_info['username'];
    $ar->numero=$_POST['ar_num'];
    $ar->valide=$_POST['ar_valide'];
    $ar->save();
    
    $adv=new adv($DB_site2,$ar->advid);
    
    $updindate=0;
    $updprice=false;
    $suivi="";
    $lfids=explode(",",$_POST['arlfids']);
    $postkeys=array_keys($_POST);
    foreach($lfids as $lfid){
    $ar->save_field("artcode_".$lfid,$_POST['artcode_'.$lfid]);
    $ar->save_field("qte_".$lfid,$_POST['qte_'.$lfid]);
    $ar->save_field("date_".$lfid,$_POST['date_'.$lfid]);
    $ar->save_field("paht_".$lfid,$_POST['paht_'.$lfid]);
    $ar->save_field("info_".$lfid,$_POST['info_'.$lfid]);
    $kopts=preg_grep("/^option_[0-9]+_".$lfid."$/",$postkeys);
    foreach($kopts as $kopt){
        $ar->save_field($kopt,$_POST[$kopt]);
    }
    if($_POST['arlivr_'.$lfid]!=""){
        $d=date_create_from_format("d/m/Y",$_POST['arlivr_'.$lfid]);
        $updindate=$d->getTimestamp();
    }
    if($_POST['newpaht_'.$lfid]!="") $updprice=true;
    }

    if($updindate>0 || $updprice){
    $stock_cmd=new stock_cmd($DB_site2,$adv->facturid);
    foreach($stock_cmd->items() as $item){
        if($updindate>0){
        $suivi.="<br>Date expédition ".$item->ref." : ".date("d/m/Y",$item->date_enlevement)." => ".date("d/m/Y",$updindate);
        $item->date_enlevement=$updindate;
        }
        if($_POST['newpaht_'.$item->artid]!=""){
        $suivi.="<br>Prix achat ".$item->ref." : ".$item->paht." => ".$_POST['newpaht_'.$item->artid];
        $item->paht=$_POST['newpaht_'.$item->artid];
        $adv->save_field('price1_'.$item->artid,$item->paht);
        }
        $item->update();
    }

    $DB_site2->query("INSERT INTO suivi_stock SET stock_cmd_id=".$stock_cmd->id.", date=".time().", commercial='".$user_info['username']."', suivi='".mysqli_real_escape_string($DB_site2->link_id,$suivi)."'");
    }
    unset($suivi);

}


$fmarques=array();
foreach($stock_cmd->items as $item) $fmarques[]=$item->marqueid;
$fmarques=array_unique($fmarques);

$fourn=array();
$list=$DB_site2->query("SELECT * FROM fournisseur WHERE `type`='fournadv' ORDER BY societe");
while($advf=$DB_site2->fetch_array($list)){
    if(advmarque(explode(',',$advf['marques']),$fmarques)){
        if($advf['groupes']!=""){
            $groups=explode(",",$advf['groupes']);

            foreach($groups as $group)
                $fourn[$group][]=$advf['mail'];
        }else
            $fourn[$advf['societe']][]=$advf['mail'];
    }
}
    

if(isset($_GET['fourn'])){
    $mails=array();
    $req=$DB_site2->query("SELECT *,fc.devise FROM fournisseur LEFT OUTER JOIN fournisseur_condition fc USING(societe) WHERE `type`='fournadv' AND societe='".mysqli_real_escape_string($DB_site2->link_id,$_GET['fourn'])."'");
    while($f=$DB_site2->fetch_array($req)){
        $mails[]=$f['mail'];
        $devise=$f['devise'];
    }
    $adv=new adv($DB_site2,$_GET['advid']);
    $adv->destinataire=$_GET['fourn'];
    $adv->save_field("fournisseur",implode(";",$mails));
    $adv->devise=$devise;
    $adv->update();
    $adv->update_step(2);

    $stock_cmd->items(-1);
    $stock_cmd->groupitems();
    
    foreach($stock_cmd->items as $item){
        $af=$DB_site2->query_first("SELECT af.prix,af.trspt,af.principal,af.delai,af.reappro FROM article_fournisseur af WHERE fournisseur='".mysqli_real_escape_string($DB_site2->link_id,$adv->destinataire)."' AND artid=".$item->artid);
        if(is_array($af)){
            $adv->save_field("price1_".$item->artid,$af['prix']+$af['trspt']);
        }
    }
    
    $adv->del_field("offre");
    $adv->del_field("listeprix_%");
    if(isset($_GET['offre']) || isset($_GET['offres'])){
        if(isset($_GET['offres']))
            $offreids=explode(",",$_GET['offres']);
        else
            $offreids=array($_GET['offre']);
        
        if(isset($_GET['offre']) && $_GET['offre']==0){
            $adv->delete(true,true,false);
        }else{
            $artids=array();
            $listeprix=array();
            foreach($stock_cmd->items as $item){
                $artids[$item->artid]=$item->qte;
            }
            
            $listesprix[$item->artid]=array();
            foreach($offreids as $offreid){
                $offre=new offre_fournisseur($DB_site2,$offreid);

                $pahtart=$offre->paht_articles($artids);
                foreach($stock_cmd->items as $item){
                    
                    foreach($pahtart[$item->artid] as $paht=>$qte){
                        $listesprix[$item->artid][$paht]=$qte;
                    }
                }
            }

            foreach($stock_cmd->items as $item){
                $remainqte=$item->qte;
                $total=0;
                while($remainqte>0){
                    $pahtmin=min(array_keys($listesprix[$item->artid]));
                    $qtemax=$listesprix[$item->artid][$pahtmin];
                    $subqte=($remainqte>$qtemax?$qtemax:$remainqte);
                    $remainqte-=$subqte;
                    $listesprix[$item->artid][$pahtmin]-=$subqte;
                    
                    if($listesprix[$item->artid][$pahtmin]==0) unset($listesprix[$item->artid][$pahtmin]);
            //      var_dump($item->artid);var_dump($pahtmin);var_dump($listesprix[$item->artid]);
                    $adv->save_field("listeprix_".$item->artid."[".$pahtmin."]",$subqte);
                    $total+=$pahtmin*$subqte;
                }
                $adv->save_field("price1_".$item->artid,$total/$item->qte);
            }
        }
    }
}

if(isset($_GET['advid'])){
    if($_GET['advid']==0){
        $adv->type=$_GET['type'];
        $adv->facturid=$stock_cmd->id;
        $adv->create();
        $_GET['advid']=$adv->id;
    }else
        $adv=new adv($DB_site2,$_GET['advid']);

    $adv->locked=isset($_GET['annule']);
    $adv->modif=isset($_GET['modif']);
}

$infoadv=$DB_site2->query_first("SELECT suivi FROM suivi_stock WHERE moyen='advfourn' AND stock_cmd_id=".$stock_cmd->id." ORDER BY date DESC LIMIT 1");


$stock_cmd->items(-1);
$stock_cmd->groupitems();
if(count($stock_cmd->items)>0){
    print "<form method=\"post\">
    <input type=\"hidden\" name=\"attente\" value=\"".$stock_cmd->id."\">
    <table id=\"fourn_recap\" width=\"100%\">
    <tr>
        <td style=\"color:white;background-color:#AAA;font-weight:bold;\" colspan=\"8\"><img width=\"20\" align=\"absmiddle\" src=\"images/cave.png\">Fournisseur produit</td>
    </tr>
    <tr>
        <td colspan=\"8\" style=\"background-color:#AAA;\">
            <span class=\"infobtn\" onclick=\"this.parentNode.querySelector('.infocmdpopup').style.display='block';\">Info ADV <img src=\"images/editer.gif\"></span>
            <span class=\"infocmdtxt\">".$infoadv['suivi']."</span>
            <div class=\"infocmdpopup\">
                <textarea id=\"fournadvcomment\">".$infoadv['suivi']."</textarea>
                <img src=\"images/icon-x.svg\" title=\"Annuler\" onclick=\"this.parentNode.style.display='none';\"> <img src=\"images/coche_verte.svg\" onclick=\"updadvcomment('fourn')\" title=\"Mettre à jour\">
            </div>
        </td>
    </tr>";

    $advcmd=$adv->listadv($stock_cmd->id,"fourn");
    if($advcmd==array())
      print "<tr style=\"background-color:#FF2222;\"><td rowspan=\"".(1+count($stock_cmd->items))."\">Non commandé<br><a href=\"stock.php?action=cmd&stock_cmd_id=".$stock_cmd->id."&type=fourn&advid=0\">Passer commande</a></td></tr>";
    else
      print "<tr style=\"background-color:#FFAA22;\"><td rowspan=\"".(1+count($stock_cmd->items))."\">Commande enregistrée<br>".$advcmd[0]->destinataire."<br>".date("d/m/Y",$advcmd[0]->date_creation)."<br>".$advcmd[0]->utisateur."<br><a href=\"stock.php?action=cmd&stock_cmd_id=".$stock_cmd->id."&type=fourn&advid=".$advcmd[0]->id."#adv\">Modifier</a></td></tr>";
    
    foreach($stock_cmd->items as $item) print "<tr style=\"background-color:#FF9999;\"><td><img src=\"".$item->img."\" width=\"50\"> ".$item->qte." ".$item->artcode."</td><td></td><td></td></tr>";

    print "</table>
    </form>";
}


// RECAP_FOURNISSEUR

$tr=array();
$stock_cmd->items(0);
if(count($stock_cmd->items)>0){
    print "<form method=\"post\">
    <input type=\"hidden\" name=\"attente\" value=\"".$stock_cmd->id."\">
    <table id=\"fourn_recap\" width=\"100%\">
    <tr>
        <td style=\"color:white;background-color:#AAA;font-weight:bold;\" colspan=\"8\"><img width=\"20\" align=\"absmiddle\" src=\"images/cave.png\">Fournisseur produit</td>
    </tr>
    <tr style=\"background-color:#AAA;\">
            <td style=\"background-color:#AAA;\">
            <span class=\"infobtn\" onclick=\"this.parentNode.querySelector('.infocmdpopup').style.display='block';\">Info ADV <img src=\"images/editer.gif\"></span>
            <span class=\"infocmdtxt\">".$infoadv['suivi']."</span>
            <div class=\"infocmdpopup\">
                <textarea id=\"fournadvcomment\">".$infoadv['suivi']."</textarea>
                <img src=\"images/icon-x.svg\" title=\"Annuler\" onclick=\"this.parentNode.style.display='none';\"> <img src=\"images/coche_verte.svg\" onclick=\"updadvcomment('fourn')\" title=\"Mettre à jour\">
            </div>
        </td>";

        $body_relance="";
    //// tâche 6875 : permet de changer toutes les dates en même temps (date enlèvement ou date livraison)
    foreach($stock_cmd->items as $item){
        $item_liste[]=$item->id;
        $datelivr=$item->date_livraison;
        $dateenl=$item->date_enlevement;
        $date_enlev_liste[]=$item->date_enlevement;
        $date_livr_liste[]=$item->date_livraison;
        $date_livr_origin=$item->date_livraison_origin;
        $date_enlev_origin=$item->date_enlevement_origin;
    }

    $arr_unique=($date_enlev_liste!='' ? array_unique($date_enlev_liste) : ''); 
    $arr_unique2=($date_livr_liste!='' ? array_unique($date_livr_liste) : '');
    $item_liste=implode(',',$item_liste);
    $date_enlev_liste=implode(',',$date_enlev_liste);
    $date_livr_liste=implode(',',$date_livr_liste);

/// relance tâche #7039
    $conditions=$DB_site2->query_first("SELECT * FROM fournisseur_condition WHERE societe='".mysqli_real_escape_string($DB_site2->link_id,$stock_cmd->destinataire)."'");
    $sujet_relance="Suivi de commande stock #CDE".$stock_cmd->id."#";
    if($conditions['ar']==1){
        if(!isset($adv) || !is_object($adv)) $adv=new adv($DB_site2);
        $advlist=$adv->listadv($stock_cmd->id,"fourn");
        $advcmd=end($advlist);
        $listar=$advcmd->list_ar();
        if(!is_null($listar)){
            foreach($listar as $ar){
                $sujet_relance.=" - AR N°".$ar->numero." du ".date("d/m/Y",$ar->date_ar);
            }
        }
    }
    $body_relance="Bonjour,<br>Pouvez-vous nous faire un point sur cette commande :<br><br><b>Détail de la commande</b><table border='2' cellpadding='5' cellspacing='1' width='100%'><thead><tr><td style='font-weight:bold'>Qte:</td><td style='font-weight:bold'>Références:</td><td align='left' style='font-weight:bold'>&nbsp;Informations:</td><td>Date de départ</td><td>Date de livraison</td><td>Nouvelle date prévue</td></tr></thead><tbody>";
    $tr=array();
    foreach($stock_cmd->items as $item){
        if(is_a($tr[$item->date_livraison][$item->artid],"stock_item"))
            $tr[$item->date_livraison][$item->artid]->qte++;
        else
            $tr[$item->date_livraison][$item->artid]=clone $item;
    }
    ksort($tr);
    foreach($tr as $datelivraison=>$items){
        foreach($items as $item){
            if($item->state==0){
                $body_relance.="<tr><td>".$item->qte."<td>".addslashes($item->marque)."<br>".addslashes($item->ref)."</td><td>".preg_replace('/\n|\r/',' ',preg_replace('/"/',"'",$item->libelle))."</td><td>".date('d-m-Y',$item->date_enlevement)."</td><td>".date('d-m-Y',$item->date_livraison)."</td><td></td></tr>";
            } 
        }
    }
    $body_relance.="</tbody></table><br>Pouvez-vous nous confirmer que cette commande a bien été expédiée/sera expédiée à date ? <b>En cas de report, nous indiquer une nouvelle date ci-dessus.</b><br><br>Nous vous remercions par avance de votre réponse, <b>sous 24h maximum</b>, par retour de ce mail.<br><br>Référence <b>#CDE".$stock_cmd->id."#</b> : à rappeler dans toute correspondance dans l’objet du mail.<br>";
/// fin relance tâche #7039

    print"
    <td></td>";
    if(count($stock_cmd->items) > 0) {
        print "
        <td style=\"background-color:#AAA;text-align:left\" onmouseover=\"document.getElementById('updateenlev_fourn_all').style.visibility='visible';\" onmouseout=\"document.getElementById('updateenlev_fourn_all').style.visibility='hidden';\">
        <div id=\"updateenlev_fourn_all\" style=\"position:absolute;visibility:hidden;cursor:pointer;background-color:white;\" onclick=\"setupdateadvdate_enlev_all('".$item_liste."','".$date_enlev_liste."');\">
        <img src=\"images/editer.gif\" title=\"modifier\">
        (tout modifier)
        </div>
        Date enlèv.<br>".(count($arr_unique)<2 && $dateenl>0?($date_enlev_origin>0 && $date_enlev_origin!=$dateenl ?date("d/m/Y",$date_enlev_origin)."<br>=>".date("d/m/Y",$dateenl):date("d/m/Y",$dateenl)):"")."
        </td>
        <td onmouseover=\"document.getElementById('updatelivr_fourn_all').style.visibility='visible';\" onmouseout=\"document.getElementById('updatelivr_fourn_all').style.visibility='hidden';\">
        <div id=\"updatelivr_fourn_all\" style=\"position:absolute;visibility:hidden;cursor:pointer;background-color:white;\" onclick=\"setupdateadvdate_livr_all('".$item_liste."','".$date_livr_liste."');\">
        <img src=\"images/editer.gif\" title=\"modifier\">
        (tout modifier)
        </div>
        Date livr. maxi<br>".(count($arr_unique2)<2 && $datelivr>0?($date_livr_origin>0 && $date_livr_origin!=$datelivr ?date("d/m/Y",$date_livr_origin)."<br>=>".date("d/m/Y",$datelivr):date("d/m/Y",$datelivr)):"")."
        </td>";
    }
    else {
        print "
        <td></td>
        <td></td>";
    }
    print "
    <td></td>
    <td></td>
    <td></td>
    <td><div style=\"margin-bottom:4px;\">".($stock_cmd->date_relance!=='0'?'Relance faite le<br>'.date("d/m/Y",$stock_cmd->date_relance).'<br>':'')."<span class=\"btn_relance\" onclick=\"mail_auto('adv@mon-espace-cuisson.com','','adv@mon-espace-cuisson.com','".$sujet_relance."','".addslashes($body_relance)."',undefined,undefined,false,null,".$stock_cmd->id.",true);mail_suivi('suivi_stock','".$stock_cmd->id."');add_exp_list('contact','contact@mon-espace-cuisson.com','base');\">Relancer</span></div></td>
    </tr>";
    //// fin tâche 6875
    $tr=array();
    foreach($stock_cmd->items as $item){
        if(is_a($tr[$item->date_livraison][$item->artid],"stock_item"))
            $tr[$item->date_livraison][$item->artid]->qte++;
        else
            $tr[$item->date_livraison][$item->artid]=clone $item;
    }

    ksort($tr);

    foreach($tr as $datelivraison=>$items){
        $itemsartid=array();
        $itemsartqte=array();
        $check="";
        foreach($items as $item){
            $itemsartid[]=$item->artid;
            $itemsartqte[]=$item->qte;
            if($item->enlevement_fait=='1'){
                $check="checked";
            }
        }

        print "<tr style=\"background-color:#00dd00;\">
            <td rowspan=\"".(1+count($items))."\">".$stock_cmd->destinataire."
                <br>".date("d/m/Y H:i",$stock_cmd->date)."
                <br>".$stock_cmd->utilisateur."
                <br>En attente de livraison<br>
                <input type=\"checkbox\" id=\"valid_enlev\" onclick=\"enlevement_ok('".implode(",",$itemsartid)."','".implode(",",$itemsartqte)."')\" ".$check.">
                <label for=\"valid_enlev\">Enlèvement</label><br>
                <br><a href=\"javascript:showStock('cancel2')\" style=\"color:red;\">Annuler la commande</a>
            </td>
            <td>Article</td>
            <td>Date enlèvement</td>
            <td>Date livraison</td>
            <td>Transporteur</td>
            <td>Entrée dans stock</td>
            <td>Annuler/ajouter produit</td>
            <td></td>
        </tr>";

        foreach($items as $item){
            $key=$datelivraison.$item->artid;
            $key2=$item->date_enlevement.$item->artid;
            $qteopt="";
            $qteopt_ajout="";
            for($i=1;$i<=$item->qte;$i++) $qteopt.="<option value=\"$i\"".($item->qte==$i?" selected":"").">$i</option>";
            for($i=1;$i<=20;$i++) $qteopt_ajout.="<option value=\"$i\">$i</option>";

            $validform="<input type=\"checkbox\" name=\"chk_".$datelivraison."_".$item->artid."\" value=\"1\" onclick=\"document.getElementById('detail_".$key."').style.display=(this.checked?'inline':'none');\"> <span id=\"detail_".$key."\" style=\"display:none\">qté : <select name=\"qte_".$datelivraison."_".$item->artid."\">".$qteopt."</select></span>";

            print "<tr style=\"background-color:#99dd99;\">
            <td><img src=\"".$item->img."\" width=\"50\" align=\"left\"> ".$item->qte." ".$item->artcode."<br><i>".$item->ref."</i></td>
            <td onmouseover=\"document.getElementById('edit_".$key2."').style.display='block'\" onmouseout=\"document.getElementById('edit_".$key2."').style.display='none'\"><span id=\"dateenlev_".$key2."\"><img src=\"images/editer.gif\" id=\"edit_".$key2."\" onclick=\"editenlevement('".$key2."')\" style=\"display:none;position:absolute;\">".($item->date_enlevement>0?($item->date_enlevement_origin>0 && $item->date_enlevement_origin!=$item->date_enlevement ?date("d/m/Y",$item->date_enlevement_origin)."<br>=>".date("d/m/Y",$item->date_enlevement):date("d/m/Y",$item->date_enlevement)):"")."</span><span id=\"modifout_".$key2."\" style=\"display:none;\">Qte: <select onchange=\"setupdateadvdate_enlev(".$item->artid.",this.value,".$item->date_enlevement.");\">$qteopt</select> <input type=\"text\" onclick=\"setupdateadvdate_enlev(".$item->artid.",".$item->qte.",".$item->date_enlevement.");\" value=\"".date("d/m/Y",$item->date_enlevement)."\" size=\"8\" readonly> </span></td>

            <td onmouseover=\"document.getElementById('edit_".$key."').style.display='block'\" onmouseout=\"document.getElementById('edit_".$key."').style.display='none'\"><span id=\"datelivr_".$key."\"><img src=\"images/editer.gif\" id=\"edit_".$key."\" onclick=\"editlivraison('".$key."')\" style=\"display:none;position:absolute;\">".($item->date_livraison>0?($item->date_livraison_origin>0 && $item->date_livraison_origin!=$item->date_livraison ?date("d/m/Y",$item->date_livraison_origin)."<br>=>".date("d/m/Y",$item->date_livraison):date("d/m/Y",$item->date_livraison)):"")."</span><span id=\"modifout_".$key."\" style=\"display:none;\">Qte: <select onchange=\"setupdateadvdate(".$item->artid.",this.value,".$datelivraison.");\">$qteopt</select> <input type=\"text\" onclick=\"setupdateadvdate(".$item->artid.",".$item->qte.",".$datelivraison.");\" value=\"".date("d/m/Y",$datelivraison)."\" size=\"8\" readonly> </span></td>
            <td>".$item->trspt."</td>
            <td>$validform</td>
            <td><a onclick='\$jQuery(\"#qte_suppr_".$datelivraison."_".$item->artid."\").show();' style='cursor:pointer;' >Supprimer un produit</a><select onchange=\"supprimer_article_cmdStock(this,$item->artid);\" name=\"qte_suppr_".$datelivraison."_".$item->artid."\" id=\"qte_suppr_".$datelivraison."_".$item->artid."\" style=\"display:none;\"><option selected></option>".str_replace("selected", "", $qteopt)."</select><br>
            <a onclick='\$jQuery(\"#qte_ajout_".$datelivraison."_".$item->artid."\").show();' style='cursor:pointer;' >Ajouter un produit</a><select onchange=\"ajouter_article_cmdStock(this,$item->artid);\" name=\"qte_ajout_".$datelivraison."_".$item->artid."\" id=\"qte_ajout_".$datelivraison."_".$item->artid."\" style=\"display:none;\"><option selected></option>".str_replace("selected", "", $qteopt_ajout)."</select></td>
            <td>
                <div>
                    <span class=\"infobtn\" onclick=\"this.parentNode.querySelector('.infocmdpopup').style.display='block';\">Info compta <img src=\"images/editer.gif\"></span>
                    <div class=\"infocmdtxt\">".$item->comment."</div>
                    <div class=\"infocmdpopup\">
                        <textarea id=\"comment$item->id\">".$item->comment."</textarea>
                        <img src=\"images/icon-x.svg\" title=\"Annuler\" onclick=\"this.parentNode.style.display='none';\"> <img src=\"images/coche_verte.svg\" onclick=\"updcomptacomment(document.getElementById('comment$item->id').value,".$item->artid.");\" title=\"Mettre à jour\">
                    </div>
                </div>
            </td>
            </tr>";
        }
    }

    print "<tr style=\"background-color:#00dd00;\"><td colspan=\"5\"></td><td><input type=\"submit\" value=\"Valider\"></td></tr>
    </table>
    </form>";

    $conditions=$DB_site2->query_first("SELECT * FROM fournisseur_condition WHERE societe='".mysqli_real_escape_string($DB_site2->link_id,$stock_cmd->destinataire)."'");
    if($conditions['ar']==1){
        $arcolors=array(0=>"#F99",1=>"#99dd99");
        if(!isset($adv) || !is_object($adv)) $adv=new adv($DB_site2);
        $advlist=$adv->listadv($stock_cmd->id,"fourn");
        $advcmd=end($advlist);
        $curadv=$advcmd;
        $stock_cmd->groupitems();

        print "<table width=\"800\">";
        $listar=$advcmd->list_ar();
        foreach($listar as $ar){
            print "<tr>
            <td rowspan=\"".(count($stock_cmd->items)+1)."\" style=\"background:".($ar->valide==1?"#0D0":"#F22")."\">
            <b>AR</b> n°".($ar->numero==""?"?":$ar->numero)."
            <br>Du ".date("d/m/Y",$ar->date_ar)."
            <br>Reçu ".date("d/m/Y",$ar->date)."
            </td>
            </tr>";

            foreach($stock_cmd->items as $item){
                $fields=$ar->line_fields($item->artid);

                $optvalue=1;
                $options="";
                if(isset($fields["options"])){
                    foreach($fields["options"] as $caractvalid=>$value){
                        $caract=$DB_site2->query_first("SELECT libelleFR FROM caracteristiquevaleur WHERE caractvalid=".$caractvalid);
                        $options.="<div style=\"color:".($value==1?"green":"red").";\">".$caract[0]."</div>";
                        if($value==0) $optvalue=0;
                    }
                }

                print "<tr>
                <td style=\"background:".$arcolors[$fields["qte"]].";\">Qté : ".$item->qte."</td>
                <td style=\"background:".$arcolors[$fields["artcode"] && $optvalue].";color:".($fields["artcode"]==0?"red":"").";\">".$item->artcode." <i>".$item->ref."</i>".$options."</td>
                <td colspan=\"3\" style=\"background:".$arcolors[$fields["date"]].";\">".date("d/m/Y",$item->date_enlevement)."</td>
                <td style=\"background:".$arcolors[$fields["paht"]].";\">".($item->paht+$item->ecopartht)."</td>
                <td style=\"background:".($ar->valide==1?"#0D0":"#F22")."\">".$fields["info"]."</td>
                </tr>";
            }
        }
        print "</table>";

        if(count($listar)==0 || end($listar)->valide==0){
            $newar=isset($_GET['ar']) && $_GET['ar']==$advcmd->id;

            if($newar){
                print "<form name=\"arform\" method=\"post\">
                <table width=\"800\">
                <tr>
                <td rowspan=\"".(count($stock_cmd->items)+1)."\" id=\"new_ar\" style=\"background:#CCC;\">AR n°".$adv->text_input("ar_num","",array("size"=>8))."
                <input type=\"hidden\" name=\"ar_valide\" value=\"0\">
                <input type=\"hidden\" name=\"arlfids\" value=\"".implode(",",array_map(function($o){return $o->artid;},$stock_cmd->items))."\">
                <br>du ".$advcmd->cal_input("ar_date","ar_color()")."
                <br>reçu&nbsp;".$advcmd->cal_input("ar_date_recu","ar_color()")."
                <input type=\"button\" id=\"valid_ar\" value=\"Valider\" style=\"display:none;\" onclick=\"send_ar()\">
                </td>
                </tr>";
                foreach($stock_cmd->items as $item){
                    $lfid=$item->artid;
                    $options="";
                    if(is_array($adv->fields['caract_'.$lfid])){
                        $options="";
                        foreach($adv->fields['caract_'.$lfid] as $caractvalid=>$value){
                            $caract=$DB_site2->query_first("SELECT libelleFR FROM caracteristiquevaleur WHERE caractvalid=".$caractvalid);
                            $options.="<div id=\"li_".$caractvalid."_".$lfid."\">".$adv->coche("option_".$caractvalid."_".$lfid,"verif_ar(".$lfid.",'artcode')")." ".$caract[0]."</div>";
                        }
                    }

                    print "<tr>
                    <td id=\"td".$lfid."qte\" style=\"background:#CCC;\">
                    <div style=\"height:32px;\">Qté : ".$item->qte."</div>
                    ".$advcmd->coche("qte_".$lfid,"verif_ar(".$lfid.",'qte')")."
                    </td>
                    <td id=\"td".$lfid."artcode\" style=\"background:#CCC;\">
                    <div style=\"height:32px;\">".$item->artcode." <i>".$item->ref."</i>".$advcmd->coche("artcode_".$lfid,"verif_ar(".$lfid.",'artcode')")."</div>
                    ".$options."
                    </td>
                    <td colspan=\"3\" id=\"td".$lfid."date\" style=\"background:#CCC;\">
                    <div id=\"olddate_".$lfid."\" style=\"height:32px;\">".date("d/m/Y",$item->date_enlevement)." <img src=\"images/editer.gif\" onclick=\"change_ar_date(".$lfid.",'".$item->date_enlevement."')\" style=\"cursor:pointer;\"></div>
                    <div id=\"newdate_".$lfid."\" style=\"height:32px;display:none;\">".$adv->cal_input("arlivr_".$lfid)."</div>
                    ".$advcmd->coche("date_".$lfid,"verif_ar(".$lfid.",'date')")."
                    </td>
                    <td id=\"td".$lfid."paht\" style=\"background:#CCC;\">
                    <div id=\"oldprice_".$lfid."\" style=\"height:32px;\">".$advcmd->fields["price1_".$lfid]." + ".$advcmd->fields["ecopart_".$lfid]." <img src=\"images/editer.gif\" style=\"cursor:pointer;\" onclick=\"change_ar_price(".$lfid.",'".$advcmd->fields["price1_".$lfid]."')\"></div>
                    <div id=\"newprice_".$lfid."\" style=\"height:32px;display:none;\">PUHT sans ecopart. : <input type=\"text\" name=\"newpaht_".$lfid."\" size=\"4\"></div>
                    ".$advcmd->coche("paht_".$lfid,"verif_ar(".$lfid.",'paht')")."
                    </td>
                    <td style=\"background:#CCC;\">".$advcmd->textarea("info_".$lfid,"",array("style"=>"height:100%;"))."</td>
                    </tr>";
                }
                print "</table>
                </form>";
            }else{
                print "<table width=\"800\">
                <tr>
                <td style=\"background:#CCC;\">AR</td>
                <td colspan=\"7\" style=\"background:#CCC;\"><img src=\"images/plus.gif\">&nbsp;<a href=\"stock.php?action=cmd&stock_cmd_id=".$stock_cmd->id."&ar=".$advcmd->id."#adv\">Ajouter</a></td>
                </tr>
                </table>";
            }
        }
    }  
}

$stock_cmd->items(array(1,2,3));
$stock_cmd->groupitems();
if(count($stock_cmd->items)>0){
    print "<table width=\"100%\">
    <tr>
        <td style=\"color:white;background-color:#AAA;font-weight:bold;\" colspan=\"8\"><img width=\"20\" align=\"absmiddle\" src=\"images/cave.png\">Fournisseur produit</td>
    </tr>
    <tr>
        <td colspan=\"8\" style=\"background-color:#AAA;\">
            <span class=\"infobtn\" onclick=\"this.parentNode.querySelector('.infocmdpopup').style.display='block';\">Info ADV <img src=\"images/editer.gif\"></span>
            <span class=\"infocmdtxt\">".$infoadv['suivi']."</span>
            <div class=\"infocmdpopup\">
                <textarea id=\"fournadvcomment\">".$infoadv['suivi']."</textarea>
                <img src=\"images/icon-x.svg\" title=\"Annuler\" onclick=\"this.parentNode.style.display='none';\"> <img src=\"images/coche_verte.svg\" onclick=\"updadvcomment('fourn')\" title=\"Mettre à jour\">
            </div>
        </td>
    </tr>
    <tr style=\"background-color:#00dd00;\">
        <td rowspan=\"".(1+count($stock_cmd->items))."\">".$stock_cmd->destinataire."<br>".date("d/m/Y H:i",$stock_cmd->date)."<br>".$stock_cmd->utilisateur."<br>Livré</td>
        <td>Article</td>
        <td>Date enlèvement</td>
        <td>Date livraison</td>
        <td></td>
        <td>Entrée dans stock</td>
        <td></td>
    </tr>";
    foreach($stock_cmd->items as $item) print "<tr style=\"background-color:#99dd99;\">
        <td><img src=\"".$item->img."\" width=\"50\" align=\"left\"> ".$item->qte." ".$item->artcode."<br><i>".$item->ref."</i></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td rowspan=\"$rowspan\">
            <div>
                <span class=\"infobtn\" onclick=\"this.parentNode.querySelector('.infocmdpopup').style.display='block';\">Info compta <img src=\"images/editer.gif\"></span>
                <div class=\"infocmdtxt\">".$item->comment."</div>
                <div class=\"infocmdpopup\">
                    <textarea id=\"comment$item->id\">".$item->comment."</textarea>
                    <img src=\"images/icon-x.svg\" title=\"Annuler\" onclick=\"this.parentNode.style.display='none';\"> <img src=\"images/coche_verte.svg\" onclick=\"updcomptacomment(document.getElementById('comment$item->id').value,".$item->artid.");\" title=\"Mettre à jour\">
                </div>
            </div>
        </td>
        </tr>";
    print "</table>";
}

print "<form method=\"post\" action=\"stock.php?action=cmd&stock_cmd_id=".$stock_cmd->id."\" name=\"modiflivr\">
<input type=\"hidden\" name=\"modifcmdid\" value=\"".$stock_cmd->id."\">
<input type=\"hidden\" name=\"artid\">
<input type=\"hidden\" name=\"qte\">
<input type=\"hidden\" name=\"olddate\">
<input type=\"hidden\" name=\"livrdate\">
<input type=\"hidden\" name=\"updateall\">
</form>

<form method=\"post\" action=\"stock.php?action=cmd&stock_cmd_id=".$stock_cmd->id."\" name=\"modifenlev\">
<input type=\"hidden\" name=\"modifcmdid\" value=\"".$stock_cmd->id."\">
<input type=\"hidden\" name=\"artid\">
<input type=\"hidden\" name=\"qte\">
<input type=\"hidden\" name=\"olddate\">
<input type=\"hidden\" name=\"enlevdate\">
<input type=\"hidden\" name=\"updateall\">
</form>

<form method=\"post\" action=\"#\" name=\"valid_enlev\">
<input type=\"hidden\" name=\"modifcmdid\" value=\"".$stock_cmd->id."\">
<input type=\"hidden\" name=\"itemsartid\">
<input type=\"hidden\" name=\"itemsartqte\">
<input type=\"hidden\" name=\"valid_enlev\" value=\"ok\">
</form>";

if(isset($_GET['type']) && $_GET['type']=="fourn"){

    $opt=array(""=>"Choisir ...");
    $requete=$DB_site2->query("SELECT * FROM fournisseur WHERE type='trspt' GROUP BY societe");
    while($t=$DB_site2->fetch_array($requete))
        $opt[$t['societe']]=$t['societe'];

    $requete=$DB_site2->query("SELECT * FROM fournisseur WHERE type='trspt' ORDER BY societe,ville");
    $optlieutrspt=array(""=>"Choisir...");
    while($lieutrspt=$DB_site2->fetch_array($requete)){
        $optlieutrspt[$lieutrspt[id]]="$lieutrspt[societe] $lieutrspt[ville] (".substr($lieutrspt['cp'],0,2).")";
    }
    
    $stock_cmd->items(-1);
    $stock_cmd->groupitems();
    


    print "<h3><img src=\"images/cave.png\" width=\"20\" align=\"absmiddle\"> Fournisseur de caves + accessoires</h3>
    <form name=\"form1\" action=\"stock.php?action=cmd&stock_cmd_id=".$stock_cmd->id."&advid=".$adv->id."\" method=\"post\" enctype=\"multipart/form-data\">
    <input type=\"hidden\" name=\"actionadv\" value=\"advfournisseur\">
    <input type=\"hidden\" name=\"fournisseurname\">";

    $artids=array();
    foreach($stock_cmd->items as $item){
        $artids[$item->artid]=$item->qte;
    }
    $offre=new offre_fournisseur($DB_site2);
    $offres=$offre->search_artids($artids,"fournisseur");

    foreach($fourn as $group=>$groupmails){
    
        foreach($stock_cmd->items as $item){
            $af=$DB_site2->query_first("SELECT af.prix,af.ecopartht,af.trspt,af.principal,af.delai,af.reappro FROM article_fournisseur af WHERE fournisseur='".mysqli_real_escape_string($DB_site2->link_id,$group)."' AND artid=".$item->artid);
            if(is_array($af)){
                $fournlist[] = $group;
                $prix=array();
                $prix['artid']=$item->artid;
                $prix['artcode']=$item->artcode;
                $prix['artcode_eurocave']=$item->ref;
                $prix['qte']=$item->qte;
                $prix['prix']=$af['prix'];
                $prix['ecopartht']=$af['ecopartht'];
                //$prix['trspt']=$af['trspt'];
                $prix['principal']=$af['principal'];
                $prix['delai']=$af['delai'];
                
                if(isset($offres[$group])){
                    foreach($offres[$group] as $offre){
                        $pahtart=$offre->paht_articles($artids);
                        
                        foreach($pahtart[$item->artid] as $paht=>$qte){
                            if($offre->articles[$item->artid]['offre']!=""){
                                $prix['listprix'][$paht]=$qte>$item->qte?$item->qte:$qte;
                                if($paht!=$item->paht) $prix['oldprice']=$item->paht/$item->qte;
                            }
                        }
                    }
                }
                $pahtlist[$item->artid][$group] = $prix;

                if($prix['prix']>0){
                    if(isset($prix['listprix'])){
                        $totalart=0;
                        foreach($prix['listprix'] as $pahtu=>$qte) $totalart+=($pahtu)*$qte;
                        $fourntotal[$group][] = $totalart;
                    }else
                        $fourntotal[$group][] = $prix['qte']*($prix['prix']);
                }
            }
        }
    }
    
    $fournlist = array_unique($fournlist);
    foreach($fournlist as $k=>$fournname){
        foreach($pahtlist as $fournprix){
            if(!isset($fournprix[$fournname])){
                unset($fournlist[$k]);
            }
        }
    }
    
    asort($fournlist);
    
    print <<<HTML
    <div id="stepfourn1" class="advgroup">
     <p>1. Fournisseur</p>
     <table>
          <tr>
               <td colspan="2" align="center"></td>
    
HTML;

    foreach($fournlist as $fournname){
        $style="";
        if($fournname==$adv->destinataire) $style="background-color:#00aa00; color: white;";
        $iconoffre="";
        $link="&fourn=".rawurlencode($fournname);
        if(isset($offres[$fournname])){
            if(count($offres[$fournname])==1){
                $link.="&offre=".$offres[$fournname][0]->id;
            }else{
                $link.="&offres=".implode(",",array_map(function($a){return $a->id;},$offres[$fournname]));
            }
            $iconoffre="Offre spéciale";
        }
        $radiobtn="<input type=\"radio\" name=\"fournisseur\" value=\"".implode(";",$fourn[$fournname])."\" ".($fournname==$adv->destinataire?"checked":"").">";
        print <<<HTML
            <td style="$style; font-weight:bold; cursor:pointer;" align="center" onclick="document.location='stock.php?action=cmd&amp;stock_cmd_id={$stock_cmd->id}&amp;type=fourn&amp;advid={$_GET[advid]}$link#adv'">
                $radiobtn
                $fournname
                $iconoffre
            </td>
HTML;
    }
    
    print "</tr>";
    
    $listartid=array();
    foreach($pahtlist as $lignefactureid => $list){
        $listartid[]=$lignefactureid;
        $fkey = key($list);
        $img  = "<img src=\"../configurations/www.ma-cave-a-vin.fr/images/produits/".$pahtlist[$lignefactureid][$fkey]['artid'].".jpg\" width=\"50\">";

        $price=$adv->fields['price1_'.$lignefactureid];
        print <<<HTML
        <tr>
            <td style="border-top:solid 1px #888; padding: 10px;">
            <input type="hidden" name="qte_$lignefactureid" value="{$pahtlist[$lignefactureid][$fkey]['qte']}">
            <input type="hidden" name="price1_$lignefactureid" value="$price">
            <input type="hidden" name="ecopart_$lignefactureid" value="{$pahtlist[$lignefactureid][$fkey]['ecopartht']}">
            <strong>{$pahtlist[$lignefactureid][$fkey]['qte']}x {$pahtlist[$lignefactureid][$fkey]['artcode']}</strong>
            <br>
            {$pahtlist[$lignefactureid][$fkey]['artcode_eurocave']}
            </td>
            <td style="text-align: center; padding: 10px;">$img</td>
HTML;

        foreach($fournlist as $fournname){
            $info = '';

            if($pahtlist[$lignefactureid][$fournname]['prix'] > 0){
                $info.="<strong>Prix achat HT</strong> : ";
                if(!isset($pahtlist[$lignefactureid][$fournname]['listprix'])){
                    $info .= number_format($pahtlist[$lignefactureid][$fournname]['prix'],2,',',' ').'&nbsp;'.$devises->symbole($pahtlist[$lignefactureid][$fournname]['devise']);
                }else{
                    $info.="<table>";
                    foreach($pahtlist[$lignefactureid][$fournname]['listprix'] as $paht=>$qte)
                        $info.="<tr>
                        <td>".$qte."x</td>
                        <td>".number_format($paht,2,',','').'&nbsp;'.$devises->symbole($pahtlist[$lignefactureid][$fournname]['devise'])."</td>
                        <td style=\"text-decoration:line-through;\">".($pahtlist[$lignefactureid][$fournname]['oldprice']!=$paht?number_format($pahtlist[$lignefactureid][$fournname]['oldprice'],2,',','').'&nbsp;'.$devises->symbole($pahtlist[$lignefactureid][$fournname]['devise']):"")."</td>
                        </tr>";
                        
                    $info.="</table>";
                }
            }
            
            if($pahtlist[$lignefactureid][$fournname]['trspt'] > 0){
                $info .=  ' + '.number_format($pahtlist[$lignefactureid][$fournname]['trspt'],2,',',' ').' '.$devises->symbole($pahtlist[$lignefactureid][$fournname]['devise']).' soit '.number_format($pahtlist[$lignefactureid][$fournname]['prix']+$pahtlist[$lignefactureid][$fournname]['trspt'],2,',',' ').' '.$devises->symbole($pahtlist[$lignefactureid][$fournname]['devise']);
            }

            if($pahtlist[$lignefactureid][$fournname]['delai'] > 0){
                $info .=  '<br><strong>Délai livr. fournisseur</strong> : '.$pahtlist[$lignefactureid][$fournname]['delai'].'j';
            }
        
            $background="";
            print <<<HTML
            <td align="right" style="border:solid 1px #888; padding: 10px;$background">$info</td>
HTML;
        }

    }
    
    print "<tr>
               <td colspan=\"2\"></td>";
               
    foreach($fournlist as $fournname){
        $total="";
        $style="";
        if(count($fourntotal[$fournname]) == count($pahtlist)){
            $total=number_format(array_sum($fourntotal[$fournname]),2,',',' ').$devises->symbole($fournisseur_devise[$fournname]);
        }
        if($fournname==$adv->destinataire) $style="background-color:#00aa00; color: white;";
        print <<<HTML
            <td align="right" style="$style; font-size: 14px; font-weight: bold;padding:5px 10px; <?php if(count($fourntotal[$fournname]) == count($pahtlist)){ ?>font-weight: bold;<?php } ?>">$total</td>
HTML;
    }
    
    print "</tr>";
    print "
    <tr><td>".$adv->checkbox("uncolis","adv_save(%name)",1)."</td><td colspan=\"4\">Attention : mettre tous les articles dans 1 seul colis</td></tr>
    <tr><td>".$adv->checkbox("palette","adv_save(%name)",1)."</td><td colspan=\"5\">Attention : Article(s) à mettre sur palette + cerclage pour le transport</td></tr>
    <tr><td>".$adv->checkbox("suremballage","adv_save(%name)",1)."</td><td colspan=\"4\">Attention :  Prévoir un suremballage du produit + palette + cerclage pour le transport</td></tr>
    </table>
    </div>";
    
    print "<div id=\"stepfourn2\" class=\"advgroup\"".(isset($adv->steps[2])?" style=\"display:block;\"":"").">
    <p>2. Pièces jointes</p>
    <table>
    <tr><td>".$adv->checkbox("nopj","showstep('fourn3');adv_save(%name,3)","0")."</td><td>Aucune</td></tr>
    <tr><td>".$adv->checkbox("blpj","showstep('fourn3');adv_save(%name,3)","1")."</td><td>Bon de livraison</td></tr>
    <!--<tr><td>".$adv->checkbox("glspj","showstep('fourn3');adv_save(%name,3)","1")."</td><td>Bon GLS ou FEDEX Express</td><td>".$adv->sendfile("bongls")."</td></tr>-->
    <tr><td>".$adv->checkbox("trsptpja4","showstep('fourn3');adv_save(%name,3)","1")."</td><td>Fragile + Note importante A4</td></tr>
    <tr><td>".$adv->checkbox("trsptpja5","showstep('fourn3');adv_save(%name,3)","1")."</td><td>Fragile + Note importante A5</td><td></td></tr>
    <!--<tr><td><input type=\"checkbox\" name=\"identpj\" value=\"1\" onclick=\"('fourn4');\"></td><td colspan=\"2\">Texte à inscrire sur colis : Commande N° $facturid De Ma cave à vin - ACILYON pour $rs_facture[nom] $rs_facture[prenom] $rs_facture[codepostal] $rs_facture[ville] $pays_fact[libelle]</td></tr>-->
    <!--<tr><td>".$adv->checkbox("otherpj","showstep('fourn3');adv_save(%name,3)","1")."</td><td>Autre : <input type=\"text\" name=\"otherpjname\" size=\"60\"></td><td><input type=\"file\" name=\"otherpjfile\"></td></tr>-->
    </table>
    </div>";

    if(!$adv->locked && $adv->fields["typecmd"]=="delete") $adv->save_field("typecmd","new");
    if($adv->locked) $adv->save_field("typecmd","delete");
    if($adv->modif) $adv->save_field("typecmd","modif");

    print "<div id=\"stepfourn3\" class=\"advgroup\"".(isset($adv->steps[3])?" style=\"display:block;\"":"").">
    <p>3. Objet du mail</p>
    <span id=\"newcmd\"".($adv->modif || $adv->locked?" style=\"display:none;\"":"").">".$adv->radio_inputs("typecmd",array("Nouvelle commande"=>"new"),"showstep('fourn4');adv_save('typecmd',4);")."</span>
    <span id=\"delcmd\"".($adv->modif || !$adv->locked?" style=\"display:none;\"":"").">".$adv->radio("typecmd",array("Annuler commande"=>"delete"),"showstep('fourn6');adv_save('typecmd',6);")."</span>
    <span id=\"modifcmd\"".(!$adv->modif?" style=\"display:none;\"":"").">".$adv->radio("typecmd",array("Modifier commande"=>"modif"),"showstep('fourn6');adv_save('typecmd',6);")."</span>
    <!--<input type=\"radio\" name=\"mailsubject\" value=\"cmd\" onclick=\"document.getElementsByName('intro')[0].checked=true;document.getElementsByName('endtxt2')[0].checked=true;\" checked> Commande n°$facturid
    <br><input type=\"radio\" name=\"mailsubject\" value=\"annule\" onclick=\"document.getElementsByName('intro')[1].checked=true;document.getElementsByName('endtxt2')[1].checked=true;\"> Annulation de la commande n°$facturid-->
    </div>";

    $sql="SELECT * FROM fournisseur WHERE `type`='fourn' ORDER BY societe,ville";
    $requete=$DB_site2->query($sql);
    $optenlev=array(""=>"Choisir...");
    while($lieu=$DB_site2->fetch_array($requete)){
    $optenlev[$lieu['id']]=$lieu['societe']." ".$lieu['ville'];
    }
    
    $step52style=isset($adv->steps[4])?"":"display:none;";
    
    print "<div id=\"stepfourn4\" class=\"advgroup\"".(isset($adv->steps[4])?" style=\"display:block;\"":"").">
    <p>4. Mode de transport</p>
    <table>
    <tr>
      <td>Lieu d'enlèvement : ".$adv->select("selectenlev",$optenlev,"adv_save(this.name);\$jQuery('#stepfourn4 table tr').show();")."</td></tr>
    </tr>
    <tr style=\"$step52style\">
      <td valign=\"top\">".$adv->radio_inputs("trsptmode",array("Enlevement à notre charge en vos locaux"=>"parenlevement"),"sumprod();selectfournlivr(this.value);adv_save('trsptmode',6);")."
    <div id=\"parenlevement\" ".($adv->fields['trsptmode']!="parenlevement"?"style=\"display:none;\"":"").">
      par le transporteur ".$adv->select("selecttrspt",$opt,"showstep('fourn5');showstep('fourn6');adv_save(this.name,'6,7')")."
    </div>
      </td>
      <td id=\"tdparenlevement\" style=\"border-left:solid thin black;".($adv->fields['trsptmode']!="parenlevement"?"display:none;":"")."\">
    Date départ maximum
    <br>".$adv->cal_input("parenlevindate","adv_calsave")."
      </td>
    </tr>
    <tr style=\"$step52style\">
      <td valign=\"top\">
    ".$adv->radio("trsptmode",array("Livraison par vos soins par votre transporteur"=>"parvossoins"),"sumprod();selectfournlivr(this.value);adv_save('trsptmode','6,7');")."
    <div id=\"parvossoins\" ".($adv->fields['trsptmode']!="parvossoins"?"style=\"display:none;\"":"").">
      <br>".$adv->checkbox("chkinfotrspt","showstep('fourn5');showstep('fourn6');adv_save(%name)",1)." Info transporteur : ".$adv->text_input("infofournothertxt","adv_autosave(this.name)",array("size"=>60))."
    </div>
      </td>
      <td id=\"tdparvossoins\" style=\"border-left:solid thin black;".($adv->fields['trsptmode']!="parvossoins"?"display:none;":"")."\">
    Date départ maximum
    <br>".$adv->cal_input("parvossoinsindate","adv_calsave")."
    <br><br>".$adv->radio_inputs("typedatelivrfourn",array("Date livraison au plus tard"=>"max"),"adv_save('typedatelivrfourn');showstep('fourn5');showstep('fourn6');")."
    <br>".$adv->radio("typedatelivrfourn",array("livraison impérative"=>"imperative"),"adv_save('typedatelivrfourn');showstep('fourn5');showstep('fourn6');")."
    <br>le ".$adv->cal_input("parvossoinsoutdate","adv_calsave")."
    <br><br>Cout du transport HT ".$adv->nb_input("mazetcout","adv_autosave(this.name)",array("onblur"=>"sumprod()"))."
      </td>
    </tr>
    </table>
    </div>";

    print "<div id=\"stepfourn5\" class=\"advgroup\"".(isset($adv->steps[5])?" style=\"display:block;\"":"").">
    <p>5. Prix</p>
    <table>
    <tr><td>Prix des produits</td><td id=\"prixprodtotal\"></td></tr>
    <tr><td>Eco participation</td><td id=\"prixecoparttotal\"></td></tr>
    <tr><td>Transport</td><td id=\"prixtrspttotal\"></td></tr>
    <tr><td>Garantie fournisseur</td><td id=\"prixgartotal\"></td></tr>
    <tr><td>Montant total commande HT :</td><td id=\"prixfourntotal\"></td></tr>
    </table>
    <br>Informations supplémentaires :<br><textarea cols=\"80\" onkeyup=\"adv_save('infocomplement')\" name=\"infocomplement\"></textarea>
    </div>";

    print "<div id=\"stepfourn6\"".(isset($adv->steps[6])?" style=\"display:block;\"":"").">
      <input type=\"button\" value=\"Aperçu\" class=\"preview\" onclick=\"valid1(true);\">
      <div style=\"text-align:left;".($adv->locked?"display:none;":"")."\">
    <!--<br><input type=\"radio\" name=\"envoiauto\" onclick=\"envoidate_save();\" ".($adv->envoi>0?"checked":"")."> Envoyer la commande automatiquement <span id=\"envoi1\" style=\"".($adv->envoi==0?"display:none;":"")."\">le <input type=\"text\" id=\"envoidate\" value=\"".$envoidate."\" size=\"10\" onclick=\"calendrier_show(this.id,this.value,undefined,envoidate_save);\" readonly> <span id=\"henvoi\">".$henvoi."</span> <input type=\"button\" value=\"Valider\" class=\"clock\" onclick=\"document.location='facture.php?action=editer&facturid=$facturid#adv'\"></span>
    <br><br><input type=\"radio\" name=\"envoiauto\" onclick=\"envoidate_save();\" ".($adv->envoi==0?"checked":"")."> Envoyer la commande immédiatement <span id=\"envoi0\" style=\"".($adv->envoi>0?"display:none;":"")."\">-->
    <input type=\"button\" onclick=\"valid1()\" value=\"Envoi immédiat\" /></span>
      </div>
      <div style=\"".($adv->locked?"":"display:none;").";margin-top:10px;\"><input type=\"button\" onclick=\"valid1()\" value=\"Annuler la commande\" style=\"color:red;font-weight:bold;\" /></div>
    </div>";

    print "</form>";
}

$infoadv=$DB_site2->query_first("SELECT suivi FROM suivi_stock WHERE moyen='advtrspt' ORDER BY date DESC LIMIT 1");

print "<br><table id=\"trspt_recap\" width=\"100%\">
    <tr>
        <td colspan=\"8\" style=\"color:white;background-color:#AAA; font-weight:bold;\"><img src=\"images/livraison.png\" width=\"25\" align=\"absmiddle\"> Transporteur</td>
    </tr>
    <tr>
        <td colspan=\"8\" style=\"background-color:#AAA;\">
            <span class=\"infobtn\" onclick=\"this.parentNode.querySelector('.infocmdpopup').style.display='block';\">Info ADV <img src=\"images/editer.gif\"></span>
            <span class=\"infocmdtxt\">".$infoadv['suivi']."</span>
            <div class=\"infocmdpopup\">
                <textarea id=\"trsptadvcomment\">".$infoadv['suivi']."</textarea>
                <img src=\"images/icon-x.svg\" title=\"Annuler\" onclick=\"this.parentNode.style.display='none';\"> <img src=\"images/coche_verte.svg\" onclick=\"updadvcomment('trspt')\" title=\"Mettre à jour\">
            </div>
        </td>
    </tr>";


// RECAP_TRANSPORTEUR
$tr=array();
$stock_cmd->items(0);
if(count($stock_cmd->items)>0){
    foreach($stock_cmd->items as $item){
        if(is_a($tr[$item->date_livraison][$item->trspt][$item->artid],"stock_item"))
            $tr[$item->date_livraison][$item->trspt][$item->artid]->qte++;
        else
            $tr[$item->date_livraison][$item->trspt][$item->artid]=$item;
    }

    $advcmd=$adv->listadv($stock_cmd->id,"trspt");
    
    ksort($tr);
    if(isset($tr[0][""])){
        
        foreach($advcmd as $cmd){
            print "<tr style=\"background-color:#FFAA22;\"><td rowspan=\"".(1+count($cmd->lignes))."\">Commande enregistrée<br>".$cmd->destinataire."<br>".date("d/m/Y",$cmd->date_creation)."<br>".$cmd->utisateur."<br><a href=\"stock.php?action=cmd&stock_cmd_id=".$stock_cmd->id."&type=trspt&advid=".$cmd->id."#adv\">Modifier</a></td></tr>";

            foreach($cmd->lignes as $artid=>$qte){
                $item=new stock_item($DB_site2);
                $item->loadartid($artid);
                print "<tr style=\"background-color:#FFCC99;\"><td>".$qte." ".$item->artcode."<br><i>".$item->ref."</i></td><td></td><td></td></tr>";
            }
        }

        print "<tr style=\"background-color:#FF2222;\"><td rowspan=\"".(1+count($tr[0][""]))."\">Non commandé<br><a href=\"stock.php?action=cmd&stock_cmd_id=".$stock_cmd->id."&type=trspt&advid=0#adv\">Passer commande</a></td></tr>";

        foreach($tr[0][""] as $item){
            print "<tr style=\"background-color:#FF9999;\"><td>".$item->qte." ".$item->artcode."<br><i>".$item->ref."</i></td><td></td><td></td></tr>";
        }
    }
        
    foreach($tr as $datelivraison=>$listtrspt){
        if($datelivraison>0){
            foreach($listtrspt as $trspt=>$items){
                if($trspt=="") $trspt="Transporteur fournisseur";
                
                $station_chargeur="";
                if(preg_match("/(schenker|heppner)/i",$trspt,$m)){
                                    foreach($advcmd as $cmd){
                                        if($cmd->destinataire==$trspt)
                                            $station_chargeur .= "<br><a href=\"station_chargeur.php?advid=".$cmd->id."&transporteur=".strtolower($m[0])."\" target=\"_blank\"><img src=\"images/icon/pdf.png\" style=\"width: 20px; cursor: pointer; vertical-align: -7px; margin-top: 3px;\"> Export station chargeur</a>";
                                    }
                }

                print "<tr style=\"background-color:#00dd00;\">
                <td rowspan=\"".(1+count($items))."\">".$trspt."<br>En attente de livraison".$station_chargeur."</td>
                <td>Article</td><td>Date enlèvement</td><td>Date livraison</td><td></td></tr>";
                
                foreach($items as $item){
                    $key=$datelivraison.$item->artid.uniqid();
                    $key2=$item->date_enlevement.$item->artid.uniqid();
                    print "<tr style=\"background-color:#99dd99;\">
                    <td>".$item->qte." ".$item->artcode."<br><i>".$item->ref."</i></td>
                     <td onmouseover=\"document.getElementById('edit_".$key2."').style.display='block'\" onmouseout=\"document.getElementById('edit_".$key2."').style.display='none'\"><span id=\"dateenlev_".$key2."\"><img src=\"images/editer.gif\" id=\"edit_".$key2."\" onclick=\"editenlevement('".$key2."')\" style=\"display:none;position:absolute;\">".($item->date_enlevement>0?date("d/m/Y",$item->date_enlevement):"")."</span><span id=\"modifout_".$key2."\" style=\"display:none;\">Qte: <select onchange=\"setupdateadvdate_enlev(".$item->artid.",this.value,".$item->date_enlevement.");\">$qteopt</select> <input type=\"text\" onclick=\"setupdateadvdate_enlev(".$item->artid.",".$item->qte.",".$item->date_enlevement.");\" value=\"".date("d/m/Y",$item->date_enlevement)."\" size=\"8\" readonly> </span></td>

                    
                    
                    <td onmouseover=\"document.getElementById('edit_".$key."').style.display='block'\" onmouseout=\"document.getElementById('edit_".$key."').style.display='none'\"><span id=\"datelivr_".$key."\"><img src=\"images/editer.gif\" id=\"edit_".$key."\" onclick=\"editlivraison('".$key."')\" style=\"display:none;position:absolute;\">".($item->date_livraison>0?date("d/m/Y",$item->date_livraison):"")."</span><span id=\"modifout_".$key."\" style=\"display:none;\">Qte: <select onchange=\"setupdateadvdate(".$item->artid.",this.value,".$datelivraison.");\">$qteopt</select> <input type=\"text\" onclick=\"setupdateadvdate(".$item->artid.",".$item->qte.",".$datelivraison.");\" value=\"".date("d/m/Y",$datelivraison)."\" size=\"8\" readonly> </span></td>
                    <td>
                       <div>
                            <span class=\"infobtn\" onclick=\"this.parentNode.querySelector('.infocmdpopup').style.display='block';\">Info compta <img src=\"images/editer.gif\"></span>
                            <div class=\"infocmdtxt\">".$item->comment_trspt."</div>
                            <div class=\"infocmdpopup\">
                                <textarea id=\"commenttrspt$item->id\">".$item->comment_trspt."</textarea>
                                <img src=\"images/icon-x.svg\" title=\"Annuler\" onclick=\"this.parentNode.style.display='none';\"> <img src=\"images/coche_verte.svg\" onclick=\"updcomptacomment(document.getElementById('commenttrspt$item->id').value,".$item->artid.",'trspt');\" title=\"Mettre à jour\">
                            </div>
                        </div>
                    </td>
                    </tr>";
                }
            }
        }
    }
}

$tr=array();
$stock_cmd->items(array(1,2,3));
$stock_cmd->groupitems();
if(count($stock_cmd->items)>0){
    foreach($stock_cmd->items as $item){
    if(is_a($tr[$item->date_livraison][$item->trspt][$item->artid],"stock_item"))
        $tr[$item->date_livraison][$item->trspt][$item->artid]->qte++;
    else
        $tr[$item->date_livraison][$item->trspt][$item->artid]=$item;
    }
    print "<tr style=\"background-color:#888;\"><td colspan=\"5\"></td></tr>";
    foreach($tr as $datelivraison=>$listtrspt){
    if($datelivraison>0){
        foreach($listtrspt as $trspt=>$items){
        if($trspt=="") $trspt="Transporteur fournisseur";
        print "<tr style=\"background-color:#00dd00;\"><td rowspan=\"".(1+count($items))."\">".$trspt."<br>Livré</td><td>Article</td><td>Date enlèvement</td><td>Date livraison</td><td></td></tr>";
        foreach($items as $item){
            
            print "<tr style=\"background-color:#99dd99;\">
              <td>".$item->qte." ".$item->artcode."<br><i>".$item->ref."</i></td>
              <td>".($item->date_enlevement>0?date("d/m/Y",$item->date_enlevement):"")."</td>
              <td>".($item->date_livraison>0?date("d/m/Y",$item->date_livraison):"")."</td>
              <td>
                <div>
                    <span class=\"infobtn\" onclick=\"this.parentNode.querySelector('.infocmdpopup').style.display='block';\">Info compta <img src=\"images/editer.gif\"></span>
                    <div class=\"infocmdtxt\">".$item->comment_trspt."</div>
                    <div class=\"infocmdpopup\">
                        <textarea id=\"commenttrspt$item->id\">".$item->comment_trspt."</textarea>
                        <img src=\"images/icon-x.svg\" title=\"Annuler\" onclick=\"this.parentNode.style.display='none';\"> <img src=\"images/coche_verte.svg\" onclick=\"updcomptacomment(document.getElementById('commenttrspt$item->id').value,".$item->artid.",'trspt');\" title=\"Mettre à jour\">
                    </div>
                </div>
              </td>
            </tr>";
        }
        }
    }
    }
}

print "</table>";

if(isset($_GET['type']) && $_GET['type']=="trspt"){
    include("stock_adv_trspt.php");
}

echo "<script language=\"javascript\">
var autosaveid;
function adv_save(name,step){
    if(document.getElementById(name+'-ok')){
      document.getElementById(name+'-ok').innerHTML='<img src=\"/configurations/www.ma-cave-a-vin.fr/images/templates/ajax_wait.gif\" width=\"16\" align=\"absmiddle\">';
      document.getElementById(name+'-ok').style.display='inline';
    }
    var post=new Array();
    post['action']='update';
    post['advid']=".intval($adv->id).";
    if (name instanceof Array){
    post['name']=new Array();
    post['value']=new Array();
    for(i=0;i<name.length;i++){
if(document.getElementsByName(name[i]).length==0) alert(name[i]);
      post['name['+i+']']=name[i];
      post['value['+i+']']=document.getElementsByName(name[i])[0].value;
    }
    }else{
    post['name']=name;
    post['value']=document.getElementsByName(name)[0].value;
    }
    if(step!=undefined) post['step']=step;
    ajaxrequest('adv/adv_ajax.php',updcb,post);
}

function adv_calsave(){
    adv_save(returninput);
}

function adv_autosave(name){
    clearTimeout(autosaveid);
    autosaveid=setTimeout(\"adv_save('\"+name+\"')\",1000);
}

function fournisseur_save(name){
    document.getElementsByName('fournisseurname')[0].value=name;
    var post=new Array();
    post['action']='fournisseur';
    post['advid']=".intval($adv->id).";
    post['destinataire']=name;
    ajaxrequest('adv/adv_ajax.php',updcb,post);
}

function updligne(ligneid,qte,step){
    var post=new Array();
    post['advid']=".intval($adv->id).";
    post['action']='ligne';
    post['ligneid']=ligneid;
    post['qte']=qte;
    if(step!=undefined) post['step']=step;
    ajaxrequest('adv/adv_ajax.php',updcb,post);
}

function updcb(httpRequest){
    var xml=httpRequest.responseXML.documentElement;

    if(xml.getElementsByTagName('field')[0]){
    var field=xml.getElementsByTagName('field')[0];
    var name=field.getAttribute('name');
    if(document.getElementById(name+'-ok')){
      document.getElementById(name+'-ok').style.display='none';
    }
    }

    if(xml.getElementsByTagName('fournisseur')[0]){
    var f=xml.getElementsByTagName('fournisseur')[0];
    var heure=f.getAttribute('heure');
    if(document.getElementById('henvoi')) document.getElementById('henvoi').innerHTML='à '+heure+'h';
    }
}

function showstep(step){
  document.getElementById('step'+step).style.display='block';
  var m=step.match('^([a-z]+)([0-9]{1,2})$');
  if(m==null) return;
}

function selectfournlivr(type){
     document.getElementById('parenlevement').style.display='none';
     document.getElementById('parvossoins').style.display='none';
     document.getElementById(type).style.display='block';
     document.getElementById('tdparenlevement').style.display='none';
     document.getElementById('tdparvossoins').style.display='none';
     document.getElementById('td'+type).style.display='table-cell';
}

function sumprod(){
         var total=0;
         var ecopart=0;
         var gartotal=0;
     var artids=new Array('".implode("','",$listartid)."');

         for(i=0;i<artids.length;i++){
          var artid=artids[i];
          total+=parseFloat(document.getElementsByName('price1_'+artid)[0].value)*parseInt(document.getElementsByName('qte_'+artid)[0].value);
          /*if(document.getElementsByName('gar_'+artid)[0].value==1) gartotal+=parseFloat(document.getElementsByName('caract_'+artid+'[]')[i2].checked+' '+document.getElementsByName('qte_'+pid)[0].value*document.getElementsByName('garval_'+pid)[0].value);

          for(i2=0;i2<document.getElementsByName('caract_'+artid+'[]').length;i2++){
          caractvalid=document.getElementsByName('caract_'+artid+'[]')[i2].value;
          if(document.getElementsByName('caract_'+pid+'[]')[i2].checked) total+=parseFloat(document.getElementsByName('caractval_'+artid+'['+caractvalid+']')[0].value);
          }*/
          ecopart+=parseFloat(document.getElementsByName('ecopart_'+artid)[0].value)*parseInt(document.getElementsByName('qte_'+artid)[0].value);
         }

         fourn=0;
         if(document.getElementsByName('trsptmode')[0].value=='parenlevement') fourn=0;
         if(document.getElementsByName('trsptmode')[0].value=='parvossoins') fourn=parseFloat(document.getElementsByName('mazetcout')[0].value);       
     if(isNaN(fourn)) fourn=0;

     total=Math.round(total*100)/100;
         document.getElementById('prixprodtotal').innerHTML=total+' €';
         document.getElementById('prixecoparttotal').innerHTML=ecopart+' €';
         document.getElementById('prixtrspttotal').innerHTML=fourn+' €';
         document.getElementById('prixgartotal').innerHTML=gartotal+' €';
         document.getElementById('prixfourntotal').innerHTML=Math.round(100*(total+ecopart+fourn+gartotal))/100+' €';
}

function valid1(showpreview){
       error=\"\";
       fourn=document.getElementsByName('fournisseur')[0].value;
       
       if(fourn=='') error=\"Il faut sélectionner un fournisseur\";


       trspt=false;
    if(document.getElementsByName('trsptmode')[0].value!='') trspt=true;
    if(fourn=='0') trspt=true;  
    if(document.getElementsByName('typecmd')[0].value=='delete') trspt=true;
       if(trspt==false) error=\"Il faut sélectionner un mode de transport\";

       if(error==\"\"){
           if(showpreview==true){
               preview(form1);
           }else{
               document.forms.form1.action='stock.php?action=cmd&stock_cmd_id=".$stock_cmd->id."&advid=".$adv->id."#adv';
        document.forms.form1.target = \"_self\";
               document.forms.form1.submit();
           }
       }else
           alert(error);
}

function preview(){
      var advid='".$adv->id."';
      if(advid=='') return;
   window.open(\"stock_adv2.php?preview=1&stock_cmd_id=".$stock_cmd->id."&advid=".$adv->id."&actionadv=advfournisseur\", \"popup\", \"height=640,width=640,menubar='no',location='no',status='no'\");
}

var editkey=undefined;
function editlivraison(key){
    
    if(editkey!=undefined){
    document.getElementById('datelivr_'+editkey).style.display='inline';
    document.getElementById('modifout_'+editkey).style.display='none';
    }
    editkey=key;
    document.getElementById('datelivr_'+key).style.display='none';
    document.getElementById('modifout_'+key).style.display='inline';
}

var editkey=undefined;
function editenlevement(key){
    if(editkey!=undefined){
    document.getElementById('dateenlev_'+editkey).style.display='inline';
    document.getElementById('modifout_'+editkey).style.display='none';
    }
    editkey=key;
    document.getElementById('dateenlev_'+key).style.display='none';
    document.getElementById('modifout_'+key).style.display='inline';
}

function setupdateadvdate(artid,qte,olddate){
    document.getElementsByName('artid')[0].value=artid;
    document.getElementsByName('qte')[0].value=qte;
    document.getElementsByName('olddate')[0].value=olddate;
    calendrier_show(undefined,'',false,updadvdate);
}
function updadvdate(){
    document.getElementsByName('livrdate')[0].value=cal_timestamp;
    document.forms.modiflivr.submit();
}

function setupdateadvdate_enlev(artid,qte,olddate){
    document.getElementsByName('artid')[1].value=artid;
    document.getElementsByName('qte')[1].value=qte;
    document.getElementsByName('olddate')[1].value=olddate;
    calendrier_show(undefined,'',false,updadvdate_enlev);
}
function updadvdate_enlev(){
    document.getElementsByName('enlevdate')[0].value=cal_timestamp;
    document.forms.modifenlev.submit();
}

function setupdateadvdate_livr_all(artids,old_dates){
    document.getElementsByName('artid')[0].value=artids;
    document.getElementsByName('olddate')[0].value=old_dates;
    document.getElementsByName('updateall')[0].value='1';
    calendrier_show(undefined,'',false,updadvdate);
}

function setupdateadvdate_enlev_all(artids,old_dates){
    document.getElementsByName('artid')[1].value=artids;
    document.getElementsByName('olddate')[1].value=old_dates;
    document.getElementsByName('updateall')[1].value='1';
    calendrier_show(undefined,'',false,updadvdate_enlev);
}

function enlevement_ok(artids,qte){
    document.getElementsByName('itemsartid')[0].value=artids;
    document.getElementsByName('itemsartqte')[0].value=qte;
    document.forms.valid_enlev.submit();
}

function cout_trspt(){
    var post=new Array();
    post['advid']='".$adv->id."';
    post['action']='trspt_infos';
    ajaxrequest('adv/adv_ajax.php',function(httpRequest){
        var xml=httpRequest.responseXML.documentElement;
        document.getElementsByName('trsptcoutval')[0].value=parseFloat(xml.getElementsByTagName('tarif')[0].firstChild.nodeValue);
    },post);
}

function totalassurance(){
         totalarticles=0;
         poidstotal=0;
     var volumetotal=0;
         for(i=0;i<document.getElementsByName('produits2[]').length;i++){
             if(document.getElementsByName('produits2[]')[i].checked){
                 ligneid=document.getElementsByName('produits2[]')[i].value;
                 totalarticles+=document.getElementsByName('qte2_'+ligneid)[0].value*(parseFloat(document.getElementsByName('price_'+ligneid)[0].value)+parseFloat(document.getElementsByName('ecopart2_'+ligneid)[0].value));
                 poidstotal+=document.getElementsByName('qte2_'+ligneid)[0].value*parseFloat(document.getElementsByName('poids_'+ligneid)[0].value);
         volumetotal+=document.getElementsByName('qte2_'+ligneid)[0].value*parseFloat(document.getElementsByName('volume_'+ligneid)[0].value);
             }
         }

         document.getElementById('poidstotal').innerHTML=Math.round(poidstotal);
     document.getElementById('valeurht').innerHTML=Math.round(totalarticles*100)/100+'&euro;';
     document.getElementsByName('valeurdeclaree')[0].value=Math.round(totalarticles*100)/100;
     document.getElementById('coutpoids').innerHTML=poidstotal+'kg';
     document.getElementById('volumetotal').innerHTML=Math.round(volumetotal*100)/100+'m<sup>3</sup>';
     document.getElementById('poidsvolume100').innerHTML=Math.round(volumetotal*10000)/100;
     document.getElementById('poidsvolume250').innerHTML=Math.round(volumetotal*25000)/100;
     document.getElementById('poidsvolume300').innerHTML=Math.round(volumetotal*30000)/100;
     adv_save('valeurdeclaree');
}

function tarifs(type,fields){
    totalassurance();
    var opts=new trsptprice(type,poidstotal,totalarticles,'$livraison[cp]');
    opts.fields=fields;
    document.getElementById('priceopts').innerHTML=opts.generate();
    document.getElementById('totaltrspt').innerHTML='0 €';
    
}

function trspttarif(){
      total=0;

      if(document.getElementsByName('trsptcout')[0].value==1) total+=parseFloat(document.getElementsByName('trsptcoutval')[0].value);

      if(document.getElementsByName('priceoptnb').length>0){
    var optnb=document.getElementsByName('priceoptnb')[0].value;
    for(i=0;i<optnb;i++) if(document.getElementsByName('chkopt['+i+']')[0].checked) total+=parseFloat(document.getElementsByName('priceopt['+i+']')[0].value);
      }

      document.getElementById('totaltrspt').innerHTML=total+' €';

}

function nbonly(event,decimal,input) {
    // Compatibilité IE / Firefox
    if(!event && window.event) {
        event=window.event;
    }

    if(decimal==undefined) decimal=false;

    // IE
    if(event.keyCode>0 && (event.keyCode < 48 || event.keyCode > 57) && event.keyCode!=8 && event.keyCode!=9) {
        if(!decimal || event.keyCode!=46 || input.value.indexOf('.')>0) {
            event.returnValue = false;
            event.cancelBubble = true;
        }
    }

    // DOM
    if(event.which!=0 && (event.which < 48 || event.which > 57) && event.which!=8) {
        if(!decimal || event.which!=46 || input.value.indexOf('.')>0){
                event.preventDefault();
                event.stopPropagation();
        }
    }
}

function valid2(showpreview){ 
    prod=false;
    error=\"\";
        for(i=0;i<document.getElementsByName('produits2[]').length;i++){
             if(document.getElementsByName('produits2[]')[i].checked) prod=true;
        }
        if(prod==false) error=\"Il faut sélectionner un produit\";

        if(error==\"\"){
      if(showpreview==true){
                preview();
         }else{
                document.forms.form2.action='stock.php?action=cmd&stock_cmd_id=".$stock_cmd->id."&advid=".$adv->id."#adv';
        document.forms.form2.target = \"_self\";
                document.forms.form2.submit();
         }
    }else
        alert(error);
}

function verif_ar(lfid,td){
    var value=parseInt(document.getElementsByName(td+'_'+lfid)[0].value);
    if(td=='artcode'){
    var opt=ar_options(lfid);
    value=value && opt;
    }
    var tdid='td'+lfid+td;
    var colors=new Array();
    colors[0]='#F99';
    colors[1]='#99dd99';
    document.getElementById(tdid).style.backgroundColor=colors[value];
    ar_color();
}

function ar_options(lfid){
    var ret=1;
    var inp=getElementsByRegExpName('option_([0-9]+)_'+lfid);
    if(inp.length==0) return 1;
    for(oi=0;oi<inp.length;oi++){
    if(inp[oi].value==0) ret=0;
    var optname=inp[oi].name.split('_');
    document.getElementById('li_'+optname[1]+'_'+optname[2]).style.color=(inp[oi].value==0?'red':'green');
    }
    return ret;
}

function ar_color(){
    var lfids=document.getElementsByName('arlfids')[0].value.split(',');
    var fields=new Array('qte','artcode','date','paht');
    var ok=0;
    var unk=0;
    for(l=0;l<lfids.length;l++){
    for(i=0;i<fields.length;i++){
        var value=document.getElementsByName(fields[i]+'_'+lfids[l])[0].value;
        if(value=='') unk++;
        value=Number(value);
        if(value==1 && fields[i]=='artcode'){
        var opt=ar_options(lfids[l]);
        value=value && opt;
        
        }
        if(value==1) ok++;
    }
    }
    if(ok==fields.length*lfids.length){
    document.getElementsByName('ar_valide')[0].value=1;
    document.getElementById('new_ar').style.backgroundColor='#0D0';
    }else{
      if(unk==0)
    document.getElementById('new_ar').style.backgroundColor='#F22';
      document.getElementsByName('ar_valide')[0].value=0;
    }
      
    if(ok==fields.length*lfids.length || unk==0){
    if(document.getElementsByName('ar_date')[0].value!='' && document.getElementsByName('ar_date_recu')[0].value!=''){
        document.getElementById('valid_ar').style.display='block';
    }
    }
//     console.debug(ok);console.debug(unk);
}

function send_ar(){
    if(document.getElementsByName('ar_valide')[0].value==0) {
    ajaxrequest('stock_adv2.php?advid=".$_GET['ar']."&preview=1&actionadv=advfournisseur&output=xml',ar_error_mail);
    }else
    document.forms.arform.submit();
}

function ar_error_mail(httpRequest){
    var ar_num=document.getElementsByName('ar_num')[0].value;
    var ar_date=document.getElementsByName('ar_date')[0].value;
    
    var fields=new Array();
    var xml=httpRequest.responseXML.documentElement;

    var msg=\"<p style='color:red;'>Bonjour,</p><p style='color:red;'>Nous avons constaté une ou plusieurs erreurs/anomalies sur votre accusé de réception \"+ar_num+\" du \"+ar_date+\". Vous trouverez ci-dessous le détail sur la commande initiale.</p>\";
    msg+=\"<p style='color:red;'>Nous vous remercions de bien vouloir nous retourner un AR conforme.</p>\";

    mailbody=xml.getElementsByTagName('body')[0].firstChild.nodeValue;
    var firstmail=new DOMParser().parseFromString(mailbody,'text/html');
    var destinataire=xml.getElementsByTagName('to')[0].firstChild.nodeValue;
    
    
    var lfids=document.getElementsByName('arlfids')[0].value.split(',');
    for(l=0;l<lfids.length;l++){
          var lfid=lfids[l];
          
          if(document.getElementsByName('qte_'+lfid)[0].value==0){
          firstmail.getElementById('qte_'+lfid).style.color='red';
          firstmail.getElementById('qte_'+lfid).style.fontWeight='bold';
          }
          if(document.getElementsByName('artcode_'+lfid)[0].value==0){
          firstmail.getElementById('artcode_'+lfid).style.color='red';
          firstmail.getElementById('artcode_'+lfid).style.fontWeight='bold';
          }
          var options=getElementsByRegExpName('option_([0-9]+)_'+lfid);
          if(options.length>0){
          for(oi=0;oi<options.length;oi++){
              if(options[oi].value==0){
              var optname=options[oi].name.split('_');
              var span=firstmail.getElementById('opt_'+lfid+'_'+optname[1]);
              if(span){
                  span.style.color='red';
                  span.style.fontWeight='bold';
              }
              }
          }
          }
          if(document.getElementsByName('date_'+lfid)[0].value==0 && firstmail.getElementById('indate')){
          firstmail.getElementById('indate').style.color='red';
          firstmail.getElementById('indate').style.fontWeight='bold';
          }
          if(document.getElementsByName('paht_'+lfid)[0].value==0){
          firstmail.getElementById('paht_'+lfid).style.color='red';
          firstmail.getElementById('paht_'+lfid).style.fontWeight='bold';
          }
          var infos=document.getElementsByName('info_'+lfid)[0].value;
          if(infos!=''){
          var p=firstmail.createElement('p');
          p.style.color='red';
          p.innerHTML=infos;
          firstmail.getElementById('lf_'+lfid).appendChild(p);
          }
    }
    
    msg+=\"<blockquote style='border-left:4px solid #CCC;padding-left: 8px;'>\"+firstmail.body.innerHTML+\"</blockquote>\";
    msg+=\"<p>Merci par avance de votre réponse, en répondant directement sur ce mail.</p>\";
    mail_auto(undefined,destinataire,undefined,\"AR \"+ar_num+\" commande ".$curadv->facturid."\",msg);
    mail_suivi('suivi_stock','".$curadv->facturid."');
    sendform=document.forms.arform;
}

function change_ar_date(lfid,olddate){
    document.getElementById('olddate_'+lfid).style.display='none';
    document.getElementById('newdate_'+lfid).style.display='block';
    var d=new Date();
    d.setTime(olddate*1000);
    document.getElementById('arlivr_'+lfid).value=('0'+d.getDate()).slice(-2)+'/'+('0'+(d.getMonth()+1)).slice(-2)+'/'+d.getFullYear();
}

function change_ar_price(lfid,oldprice){
    document.getElementById('oldprice_'+lfid).style.display='none';
    document.getElementById('newprice_'+lfid).style.display='block';
    document.getElementsByName('newpaht_'+lfid)[0].value=parseFloat(oldprice);
}

function getElementsByRegExpName(p_regexp, p_element, p_tagName) {
    p_element = p_element === undefined ? document : p_element;
    p_tagName = p_tagName === undefined ? '*' : p_tagName;
    var v_return = [];
    var v_inc = 0;
    for(var v_i = 0, v_il = p_element.getElementsByTagName(p_tagName).length; v_i < v_il; v_i++) {
        if(p_element.getElementsByTagName(p_tagName).item(v_i).name && p_element.getElementsByTagName(p_tagName).item(v_i).name.match(p_regexp)) {
            v_return[v_inc] = p_element.getElementsByTagName(p_tagName).item(v_i);
            v_inc++;
        }
    }
    return v_return;
}
".$js_popup_pj."
</script>
<script language=\"javascript\" src=\"../javascripts/prototype.js\"></script>
<script language=\"javascript\" src=\"trsptprice.js\"></script>";

function advmarque($advmids,$lignemids){
    foreach($advmids as $mid)
    if(in_array($mid,$lignemids)) return true;

    return false;
}

function fournkey($txt){
    $search=array("é","è","ê","à","ç"," ",",",";",":","'");
    $replace=array("e","e","e","a","c");
    return strtolower(str_replace($search,$replace,$txt));
}

?>
