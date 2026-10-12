<?php

require "admin_global.php";
include "./header.php";
include "./menu.php";
include "../class/fonctions.php";
include "../class/stock.class.php";

if($user_info['gestion_stock']=="") exit;

$stock=new stock($DB_site2);
$lieux=$stock->lieux();

$lieu=null;
if(isset($_GET['lieu'])) $lieu=$_GET['lieu'];
if(intval($lieu)==0) $lieu=null;
$selectlieu="";
    $selectlieu="Lieu de stockage : <select onchange=\"document.location='stock_ajust.php?lieu='+this.value;\"><option value=\"\"></option>";
    foreach($lieux as $lieuid => $libelle){
        if (preg_match('/'.$lieuid.'/', $user_info['gestion_stock']) == '1') {
            $selectlieu.="<option value=\"".$lieuid."\"".($lieuid==$_GET['lieu']?" selected":"").">".$libelle."</option>";
        }
    }
    $selectlieu.="</select>";

if(isset($_GET['action']) && $_GET['action']=="envoi"){
    $totaldiff=0;
    $list=$stock->stockbyqte(array(1,2),$lieu);
    $list+=$stock->stock0(array(1,2),$lieu,true);
    $r2=$stock->stockbyqte(2,$lieu);
    $r0=$stock->stockbyqte(0,$lieu);

    $now=time();
    $body="<p>Comptage du stock ".$lieux[$lieu]." par ".$user_info['username']." le ".date("d/m/Y à H:i")."</p>
    <table style=\"border:solid 1px black\">
    <thead style=\"background-color:#CCC; text-align:center; font-weight:bold;\">
    <tr>
        <td colspan=\"2\">Produit</td>
        <td>Qte stock physique</td>
        <td>Qté comptée</td>
        <td>Décompte stock<br>(valeur dans suivi)</td>
        <td colspan=\"2\">Différence</td>
        <td>Commentaire</td>
    </tr>
    </thead>";
    
    foreach($_POST['ajust'] as $artid=>$qte){
     $a=new stock_ajust($DB_site2);
     $a->date=$now;
     $a->utilisateur=$user_info['username'];
     $a->lieu=$lieu;
     $a->artid=$artid;
     $a->qte=$list[$artid]->qte-$r0[$item->artid]->qte;
     $a->qte_compte=$qte;
     $a->commentaire=$_POST['com'][$artid];
     $a->create();

     /// tache 7035 : pour qté décompté (valeur qui se retrouve dans stock->suivi (stock.php))
      $stockajust=new stock_ajust($DB_site2);
      $firstajust=$stockajust->searchfirst($artid);
      $trqte = 0;
      foreach($firstajust as $lieuid=>$ajust){
        if($lieuid==$_GET['lieu']){
            $trqte=$ajust->qte;
        }
      }
      $to = time();
      $from  = $to-5*365*86400;
      $from=$firstajust[$_GET['lieu']]->date;
      $suivi = $stock->suivi($artid,$_GET['lieu'],$from,$to);
      $qte_arr=array();

      foreach($suivi['in'] as $s)
                    $qte_arr[$s->artid][$s->date] += $s->qte;
                            
            foreach($suivi['facture'] as $s)
                    $qte_arr[$s->artid][$s->date_expedition] -= $s->qte;
                    
            foreach($suivi['perdu'] as $s)
                    $qte_arr[$s->artid][$s->date_sortie] -= $s->qte;
                    
            foreach($suivi['suivi'] as $date=>$s)
                    $qte_arr[$s->artid][$date] -= $s->qte;

            foreach ($suivi['maj'] as $date=>$s) {
                    $qte_arr[$s[artid]][$date] = (signe($s[qte]) * $qte_arr[$s[artid]][$date]) +  $s[qte];
                } 

      if(!is_null($qte_arr[$s->artid])){
            foreach($qte_arr[$s->artid] as $qtets => $qteartid){
                $trqte+=$qteartid;
            }
        }
        /// fin tache 7035

     $paht=$DB_site2->query_first("SELECT paht FROM article WHERE artid = '".$artid."'");
     $diff=$qte-($list[$artid]->qte-$r0[$item->artid]->qte);
     $totaldiff+=$diff*$paht['paht'];
     $body.="<tr".($diff<>0?" style=\"background-color:#FFAAAA;\"":"").">
            <td>".$list[$artid]->artcode." (".$list[$artid]->ref.")</td>
            <td>".$list[$artid]->libelle."</td>
            <td>".($list[$artid]->qte-$r0[$item->artid]->qte)."</td>
            <td>".$qte."</td>
            <td>".$trqte."</td>
            <td>".($diff>0?"+":"").$diff."</td>
            <td>".($diff!=0?$diff*$paht['paht']."&euro;":"")."</td>
            <td>".$_POST['com'][$artid]."</td>
        </tr>\n";
    }
    
    $body.="<tr>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td colspan=\"2\" align=\"right\"><b>".number_format($totaldiff,2,',','')."&euro;</b></td>
    </tr>
    </table>";

    $mail=new email();
    $mail->destinataire=$user_info['mail_contactFR'];
    $mail->to=array("adv@mon-espace-cuisson.com","pr@mon-espace-cuisson.com","webmaster@mon-espace-cuisson.com");
 //$mail->destinataire="webmaster4@mon-espace-cuisson.com";
    $mail->expediteur=$user_info['mail_contactFR'];
    $mail->expname=$user_info['nom_contact'];
    $mail->subject="Comptage du stock ".$lieux[$lieu];
    $mail->body=$body;
    $mail->sendmail();

    $msg="<p>Message envoyé</p>";
    $_GET['action']="ajust";
    $_GET['date']=$now;
}

print "<h1>Stock</h1>";

print "<style>
.physique { background-color:#FFAAAA; text-align:center; }
.ajustinput { background-color:#FFFFAA; text-align:center; }
.center { text-align:center; }
#stock_table { border-collapse:collapse; border:solid 1px #AAA; }
#stock_table td { border-top:solid 1px #CCC; padding:2px; }
</style>";
if(!is_null($lieu)){
    $listajust=$stock->ajust_date($lieu);
    if($listajust!=array()){
    krsort($listajust);
    $tblajust="<div>
    <h3>Liste des relevés</h3>
    <table>";
    foreach($listajust as $date=>$utilisateur){
        $tblajust.="<tr><td>".date("d/m/Y H:i",$date)."</td><td>".$utilisateur."</td><td><a href=\"stock_ajust.php?action=ajust&date=".$date."&lieu=".$lieu."\">Voir</a></td></tr>";
    }
    $tblajust.="</table></div>";
    }
}

if(!is_null($lieu) && !isset($_GET['action'])){
  
    $r1=$stock->stockbyqte(array(0,1,2),$lieu);
    $r=array_merge($r1,$stock->stock0(array(0,1,2),$lieu,true));
    ksort($r);
    $r2=$stock->stockbyqte(2,$lieu);
    $r0=$stock->stockbyqte(0,$lieu);

    $totalligne=0;
    $totalqte=0;
    $total=0;

    $tr=array();
    foreach($r as $item){
      $totalligne=$r1[$item->artid]->paht-$r0[$item->artid]->paht;
      $totalqte+=$item->qte-$r0[$item->artid]->qte;

      $total+=$totalligne;
      $nclist=$nclist0="";
      $noncommandenb=0;
      $noncommande=$item->cmd_non_reserve();
      foreach($noncommande as $noncmdid){
        $noncmdqte=$DB_site2->query_first("SELECT SUM(qte) FROM lignefacture WHERE factureid=".$noncmdid." AND (artid=".$item->artid.($item->artids!=""?" OR artid IN (".$item->artids.")":"").")");
        $noncommandenb+=$noncmdqte[0];
        $nclist.="<br>".$noncmdid;
      }

      /// tache 7035 : pour qté décompté (valeur qui se retrouve dans stock->suivi (stock.php))
      $stockajust=new stock_ajust($DB_site2);
      $firstajust=$stockajust->searchfirst($item->artid);
      $trqte = 0;
      foreach($firstajust as $lieuid=>$ajust){
        if($lieuid==$_GET['lieu']){
            $trqte=$ajust->qte;
        }
      }
      $to = time();
      $from  = $to-5*365*86400;
      $from=$firstajust[$_GET['lieu']]->date;
      $suivi = $stock->suivi($item->artid,$_GET['lieu'],$from,$to);
      $qte_arr=array();
      foreach($suivi['in'] as $s){
        $qte_arr[$s->artid][$s->date] += $s->qte;
      }             
    foreach($suivi['facture'] as $s){
        $qte_arr[$s->artid][$s->date_expedition] -= $s->qte;
    }  
    foreach($suivi['perdu'] as $s){
        $qte_arr[$s->artid][$s->date_sortie] -= $s->qte;
    }
    foreach($suivi['suivi'] as $date=>$s){
        $qte_arr[$s->artid][$date] -= $s->qte;
    }
    foreach ($suivi['maj'] as $date=>$s) {
        $qte_arr[$s[artid]][$date] = (signe($s[qte]) * $qte_arr[$s[artid]][$date]) +  $s[qte];
    } 

        if(!is_null($qte_arr[$s->artid])){
            foreach($qte_arr[$s->artid] as $qtets => $qteartid){
                $trqte+=$qteartid;
            }
        }
        
        /// fin tache 7035

      $tdqtecde=$tddatecde="";
      $cmds=$item->commandes($lieu);
      foreach($cmds as $cmd){
      $tdqtecde.="<br>".$cmd->qte;
      $tddatecde.="<br>".($cmd->date_livraison>0?date("d/m/Y",$cmd->date_livraison):"?");
      }
      $flist="";
      if(intval($r2[$item->artid]->qte)>0){
      $cmdlist=$stock->getdest($item->artid,$lieu,2);  
      foreach($cmdlist as $facturid) $flist.="<br>".$facturid;
      }
      $qtevirtuel=$item->qte+$r0[$item->artid]->qte-$noncommandenb-$noncommande0nb;
      $defcom=$defqte="";
      if(isset($_POST['ajust'][$item->artid])){
      $defqte=$_POST['ajust'][$item->artid];
      $defcom=$_POST['com'][$item->artid];
      }

      $tr[$item->marque][$item->artcode]="<tr>
    <td><img src=\"".$item->img."\" width=\"50\"></td>
    <td>".$item->artcode."<br><i>".implode("<br>",$item->similaire)."</i><br>".$item->ref."</td>
    <td>".$item->libelle." ".$item->marque."</td>
    <td class=\"physique\">".($item->qte-$r0[$item->artid]->qte)."</td>
    <td class=\"center\">".intval($r2[$item->artid]->qte).$flist."</td>
    <td class=\"center\">".($item->qte-$noncommandenb-$r2[$item->artid]->qte-$r0[$item->artid]->qte)."</td>
    <td class=\"center\"><b>".intval($r0[$item->artid]->qte)."</b>".$tdqtecde."</td>
    <td>&nbsp;$tddatecde</td>
    <td id=\"td_".$item->artid."\" class=\"ajustinput\"><input type=\"text\" name=\"ajust[".$item->artid."]\" size=\"3\" onkeypress=\"nbonly(event,false,this)\" value=\"".$defqte."\"></td>
    <td class=\"center\">".$trqte."</td>
    <td><textarea name=\"com[".$item->artid."]\" cols=\"40\" rows=\"3\">".$defcom."</textarea></td>
      </tr>";
    }
    ksort($tr);


    $tbl.="<div class=\"clear\"></div>
    <p>Attention : contrôler la quantité du stock par rapport à la quantité du stock &quot;physique&quot;. Les autres quantités sont à titre indicatif</p>
    <table id=\"stock_table\">";
    foreach($tr as $marque=>$artcodes){
    ksort($artcodes);
    $tbl.="<tr>
      <td name=\"#tr$marque\" colspan=\"21\" style=\"text-align:center;background-color:#CCCCCC;\">".$marque."</td></tr>
      <tr style=\"font-weight:bold;text-align:center;\">
      <td></td>
      <td>Ref acilyon<br>constructeur</td>
      <td width=\"300\">Libellé</td>
      <td class=\"physique\">Qté stock physique</td>
      <td>Qté réservé</td>
      <td>Qté réel instantané</td>
      <td>Qté en réappro</td>
      <td>Date réappro</td>
      <td class=\"ajustinput\">Quantité comptée</td>
      <td title=\"valeur dans suivi\">Décompte stock</td>
      <td>Commentaire</td>
    </tr>".implode("",$artcodes);
    }
    $tbl.="<tr><td>Total</td><td></td><td></td><td class=\"center\">$totalqte</td><td align=\"right\">".number_format($total,2,',','')."</td><td></td><td></td><td></td><td></td><td class=\"center\"><input type=\"button\" value=\"Envoyer\" onclick=\"verifinput()\"></td></tr>
    </table>";
}

if($_GET['action']=="preview"){
    $totaldiff=0;
    $list=$stock->stockbyqte(1,$lieu);
    $list+=$stock->stock0("1,2",$lieu);
    $r2=$stock->stockbyqte(2,$lieu);

    $tbl="<style>
    .tbl { border:solid 1px black; border-collapse:collapse; }
    .tbl thead { background-color:#CCC; text-align:center; font-weight:bold; }
    .tbl td { border-top:solid 1px #888; border-left:solid 1px #CCC; padding:2px; }
    </style>";
    $tbl.="<p>Comptage du stock ".$lieux[$lieu]." par ".$user_info['username']." le ".date("d/m/Y à H:i")."</p>
    <table class=\"tbl\">
    <thead>
    <tr><td colspan=\"2\">Produit</td><td width=\"70\">Qte stock physique</td><td width=\"70\">Qté comptée</td><td title=\"valeur dans suivi\">Décompte<br>stock</td><td colspan=\"2\">Différence</td><td width=\"250\">Commentaire</td></tr>
    </thead>";

    /// Utiliser le paht de l'article et pas celui du stock_item #6927
    foreach($_POST['ajust'] as $artid=>$qte){
        /// tache 7035 : pour qté décompté (valeur qui se retrouve dans stock->suivi (stock.php))
      $stockajust=new stock_ajust($DB_site2);
      $firstajust=$stockajust->searchfirst($artid);
      $trqte = 0;
      foreach($firstajust as $lieuid=>$ajust){
        if($lieuid==$_GET['lieu']){
            $trqte=$ajust->qte;
        }
      }
      $to = time();
      $from  = $to-5*365*86400;
      $from=$firstajust[$_GET['lieu']]->date;
      $suivi = $stock->suivi($artid,$_GET['lieu'],$from,$to);
      $qte_arr=array();

      foreach($suivi['in'] as $s)
        $qte_arr[$s->artid][$s->date] += $s->qte;
    
    foreach($suivi['facture'] as $s)
        $qte_arr[$s->artid][$s->date_expedition] -= $s->qte;
    
    foreach($suivi['perdu'] as $s)
        $qte_arr[$s->artid][$s->date_sortie] -= $s->qte;
    
    foreach($suivi['suivi'] as $date=>$s)
        $qte_arr[$s->artid][$date] -= $s->qte;

    foreach ($suivi['maj'] as $date=>$s) {
        $qte_arr[$s[artid]][$date] = (signe($s[qte]) * $qte_arr[$s[artid]][$date]) +  $s[qte];
    } 

      if(!is_null($qte_arr[$s->artid])){
            foreach($qte_arr[$s->artid] as $qtets => $qteartid){
                $trqte+=$qteartid;
            }
        }
        /// fin tache 7035


     $paht=$DB_site2->query_first("SELECT paht FROM article WHERE artid = '".$artid."'");
     $form.="<input type=\"hidden\" name=\"ajust[$artid]\" value=\"$qte\"><input type=\"hidden\" name=\"com[$artid]\" value=\"".$_POST['com'][$artid]."\">";
     $diff=$qte-($list[$artid]->qte+$r2[$artid]->qte);
     $totaldiff+=$diff*$paht['paht'];
     $tbl.="<tr".($diff<>0?" style=\"background-color:#FFAAAA;\"":"")."><td>".$list[$artid]->artcode."</td><td>".$list[$artid]->libelle."</td><td align=\"center\">".($list[$artid]->qte+$r2[$artid]->qte)."</td><td align=\"center\">".$qte."</td><td>".$trqte."</td><td>".($diff>0?"+":"").$diff."</td><td>".($diff!=0?$diff*$paht['paht']."&euro;":"")."</td><td>".strip_tags($_POST['com'][$artid])."</td></tr>";
    }
    $tbl.="<tr><td></td><td></td><td></td><td></td><td colspan=\"2\" align=\"right\"><b>".number_format($totaldiff,2,',','')."&euro;</b></td></tr>
    </table>
    <p align=\"center\"><input type=\"button\" value=\"Retour\" onclick=\"retour()\"> <input type=\"button\" value=\"Envoyer\" onclick=\"envoi()\"></p>".$form;
}

if($_GET['action']=="ajust"){

    $tbl="<div class=\"clear\"></div>
    <table id=\"stock_table\">
    <tr><td></td>
    <td>Ref Acilyon</td>
    <td width=\"300\">Libellé</td>
    <td class=\"center\">Qté stock physique</td>
    <td>Quantité comptée</td>
    <td title=\"valeur dans suivi\">Décompte stock</td>
    <td colspan=\"2\">Différence</td>
    <td>Commentaire</td></tr>";
    $totaldiff=0;
    $list=$stock->ajust_list($lieu,$_GET['date']);
    foreach($list as $ajust){
    $item=$ajust->item();
    $paht=$DB_site2->query_first("SELECT paht FROM article WHERE artid = '".$item->artid."'");
    $diff=$ajust->qte_compte-$ajust->qte;
    $qte_compte=$ajust->qte_compte;
    $totaldiff+=$diff*$paht['paht'];

    /// tache 7035 : pour qté décompté (valeur qui se retrouve dans stock->suivi (stock.php))
      $stockajust=new stock_ajust($DB_site2);
      $firstajust=$stockajust->searchfirst($item->artid);
      $trqte = 0;
      foreach($firstajust as $lieuid=>$ajust2){
        if($lieuid==$_GET['lieu']){
            $trqte=$ajust2->qte;
        }
      }
      $to = time();
      $from  = $to-5*365*86400;
      $from=$firstajust[$_GET['lieu']]->date;
      $suivi = $stock->suivi($item->artid,$_GET['lieu'],$from,$to);
      $qte_arr=array();
      foreach($suivi['in'] as $s)
        $qte_arr[$s->artid][$s->date] += $s->qte;

    foreach($suivi['facture'] as $s)
        $qte_arr[$s->artid][$s->date_expedition] -= $s->qte;

    foreach($suivi['perdu'] as $s)
        $qte_arr[$s->artid][$s->date_sortie] -= $s->qte;

    foreach($suivi['suivi'] as $date=>$s)
        $qte_arr[$s->artid][$date] -= $s->qte;

    foreach ($suivi['maj'] as $date=>$s) {
        $qte_arr[$s[artid]][$date] = (signe($s[qte]) * $qte_arr[$s[artid]][$date]) +  $s[qte];
    } 

      if(!is_null($qte_arr[$s->artid])){
            foreach($qte_arr[$s->artid] as $qtets => $qteartid){
                $trqte+=$qteartid;
            }
        }
        /// fin tache 7035
    $tbl.="<tr".($diff!=0?" style=\"background-color:#FFAAAA;\"":"")."><td><img src=\"".$item->img."\" width=\"50\"></td>
    <td>".$item->artcode."<br><i>".implode("<br>",$item->similaire)."</i><br>".$item->ref."</td>
    <td>".$item->libelle." ".$item->marque."</td>
    <td class=\"center\">".$ajust->qte."</td>
    <td class=\"center\">".$qte_compte."</td>
    <td class=\"center\">".$trqte."</td>
    <td class=\"center\">".($diff>0?"+":"").$diff."</td>
    <td>&nbsp;".($diff!=0?number_format($diff*$paht['paht'],2,',','')."&euro;":"")."</td>
    <td>".$ajust->commentaire."</td>
    </tr>";
    }
    $tbl.="<tr><td colspan=\"6\"></td><td><b>".number_format($totaldiff,2,',','')."&euro;</b></td></tr>
    </table>";
}

print "<h3>Etat du stock</h3>
<form name=\"ajustform\" method=\"post\">
<!--<input type=\"hidden\" name=\"action\" value=\"1\">-->".
$msg.$selectlieu.$tbl.$tblajust;
print "</form>";


print "<script language=\"javascript\">
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
    

function verifinput(){
    var tbl=document.getElementById('stock_table');
    var inputs=tbl.getElementsByTagName('input');
    var stop=false;
    for(i=0;i<inputs.length;i++){
    if(inputs[i].name.substr(0,5)=='ajust'){
        var id=inputs[i].name.substr(6,inputs[i].name.length-7);
        document.getElementById('td_'+id).style.backgroundColor='';
        if(inputs[i].value==''){
        stop=true;
        document.getElementById('td_'+id).style.backgroundColor='red';
        }
    }
    }
    if(stop)
      alert(\"Certains articles n'ont pas la quantitÃ© en stock renseignÃ©e, merci de les remplir pour valider l'envoi\");
    else{
      document.forms.ajustform.action='stock_ajust.php?action=preview&lieu=$lieu';
      document.forms.ajustform.submit();    
    }
}

function retour(){
    document.forms.ajustform.action='stock_ajust.php?lieu=$lieu';
    document.forms.ajustform.submit();
}

function envoi(){
    document.forms.ajustform.action='stock_ajust.php?action=envoi&lieu=$lieu';
    document.forms.ajustform.submit();
}
</script>";

include "footer.php";

?>
