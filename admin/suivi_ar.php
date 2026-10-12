<?php
require "admin_global.php";

include "./header.php";
include "./menu.php";
include "adv/adv.class.php";
include "adv/envoi_fournisseur.php";
include "../class/stock.class.php";

if(!isset($_GET['fournisseur'])) $_GET['fournisseur']="";

$adv=new adv($DB_site2);

$no_ar_list=$adv->no_ar();
$tr=array();
$today=mktime(0,0,0);
$fournisseurs=array();

foreach($no_ar_list as $adv){
    $dateadv=date("d/m/Y",$adv->date_creation);
    $info="";
    $list_ar=$adv->list_ar();
    if(count($list_ar)==0){
	$td_ar="Aucun AR";
	$td_color="#CCC";
	$start_date=$adv->date_creation;
	switch($adv->origin){
	    case "facture":
		    $link="facture.php?action=editer&facturid=$adv->facturid#adv";
		    $ef=new envoi_fournisseur($DB_site2,$adv->efid);
		    $tarif=number_format($ef->tarif,2,',','')." ".$devises->symbole($ef->devise);
	    break;
	    
	    case "stock":
		    $link="stock.php?action=cmd&stock_cmd_id=$adv->facturid#adv";
		    $stock_cmd=new stock_cmd($DB_site2,$adv->facturid);
		    $items=$stock_cmd->items();
		    $stock_cmd->groupitems();
		    $tarif=0;
		    foreach($stock_cmd->items as $item)
			$tarif+=$item->paht;
			
		    $conditions=$DB_site2->query_first("SELECT devise FROM fournisseur_condition WHERE societe='".mysqli_real_escape_string($DB_site2->link_id,$stock_cmd->destinataire)."'");
		    $tarif=number_format($tarif,2,',','')." ".$devises->symbole($conditions['devise']);
	    break;
	}
	
    }else{
	$last_ar=end($list_ar);
	$td_ar="AR ".$last_ar->numero." du ".date("d/m/Y",$last_ar->date_ar);
	$td_color="red";
	$start_date=$last_ar->date;
    
	$destinataire=$adv->fields['fournisseur'];
	
	if($adv->origin=="facture"){
	    $ef=new envoi_fournisseur($DB_site2,$adv->efid);
	    $tarif=number_format($ef->tarif,2,',','')." ".$devises->symbole($ef->devise);
	    $link="facture.php?action=editer&facturid=$adv->facturid#adv";
	    
	    $info="<img src=\"images/icon/viewmag_pt.png\" onmouseover=\"document.getElementById('info_".$adv->id."').style.visibility='visible';\" onmouseout=\"document.getElementById('info_".$adv->id."').style.visibility='hidden';\"><div id=\"info_".$adv->id."\" class=\"popup\">Erreur :";
	    foreach($adv->lignes as $lfid=>$qte){
		$fields=$last_ar->line_fields($lfid);
		$ref=$adv->fields['ref_'.$lfid];
		if($fields['paht']==0) $info.="<br>Prix ".$ref;
		if($fields['date']==0) $info.="<br>Date ".$ref;
		if($fields['artcode']==0) $info.="<br>Réf ".$ref;
		if($fields['qte']==0) $info.="<br>Qté ".$ref;
		if(isset($fields['options'])){
		    foreach($fields['options'] as $caractvalid=>$value){
			if($value==0){
			    $caract=$DB_site2->query_first("SELECT libelleFR FROM caracteristiquevaleur WHERE caractvalid=".$caractvalid);
			    $info.="<br>Option ".$caract[0]." ".$ref;
			}
		    }
		}
	    }
	    $info.="</div>";
	    
	}elseif($adv->origin=="stock"){
	    $tarif=0;
	    $stock_cmd=new stock_cmd($DB_site2,$adv->facturid);
	    $items=$stock_cmd->items();
	    $stock_cmd->groupitems();
	    
	    $info="<img src=\"images/icon/viewmag_pt.png\" onmouseover=\"document.getElementById('info_".$adv->id."').style.visibility='visible';\" onmouseout=\"document.getElementById('info_".$adv->id."').style.visibility='hidden';\"><div id=\"info_".$adv->id."\" class=\"popup\">Erreur :";
	    foreach($stock_cmd->items as $item){
		$tarif+=$item->paht;
		
		$fields=$last_ar->line_fields($item->artid);
		$ref=$item->ref;
		if($fields['paht']==0) $info.="<br>Prix ".$ref;
		if($fields['date']==0) $info.="<br>Date ".$ref;
		if($fields['artcode']==0) $info.="<br>Réf ".$ref;
		if($fields['qte']==0) $info.="<br>Qté ".$ref;
		if(isset($fields['options'])){
		    foreach($fields['options'] as $caractvalid=>$value){
			if($value==0){
			    $caract=$DB_site2->query_first("SELECT libelleFR FROM caracteristiquevaleur WHERE caractvalid=".$caractvalid);
			    $info.="<br>Option ".$caract[0]." ".$ref;
			}
		    }
		}
	    }
	    $info.="</div>";
	    $link="stock.php?action=cmd&stock_cmd_id=$adv->facturid#adv";
	    
	    $conditions=$DB_site2->query_first("SELECT devise FROM fournisseur_condition WHERE societe='".mysqli_real_escape_string($DB_site2->link_id,$stock_cmd->destinataire)."'");
	    $tarif=number_format($tarif,2,',','')." ".$devises->symbole($conditions['devises']);
	}
    
    }
    $fournisseurs[]=$adv->destinataire;
    $nbj=intval(($today-$start_date)/86400);
    
    if($_GET['fournisseur']=="" || $_GET['fournisseur']==$adv->destinataire){
		$tr[$nbj][]=<<<HTML
		<tr>
		    <td id="dateadv_$adv->id">$dateadv</td>
		    <td><a href="$link" id="facturid_$adv->id" target="_blank">$adv->facturid</a></td>
		    <td>$adv->destinataire <input type="hidden" id="dest_$adv->id" value="$destinataire"></td>
		    <td align="right" id="total_$adv->id">$tarif</td>
		    <td style="background:$td_color">
			$td_ar
			$info
		    </td>
		    <td align="right">$nbj</td>
		    <td><a href="javascript:relance($adv->id)">Relance</a></td>
		</tr>
HTML;
    }
}

$fournisseurs=array_unique($fournisseurs);
asort($fournisseurs);
$optfourn="<option value=\"\">Tous</option>";
foreach($fournisseurs as $f)
    $optfourn.="<option value=\"".$f."\"".($_GET['fournisseur']==$f?" selected":"").">".$f."</option>";
    
krsort($tr);
$table=implode("",array_map(function($arr){ return implode("",$arr); },$tr));

print <<<HTML
<h1>Suivi AR fournisseurs</h1>
<p>Fournisseur : <select onchange="document.location='suivi_ar.php?fournisseur='+this.value">$optfourn</select></p>

<table class="table_alternate_color">
<tr class="entete">
  <td>Date commande fournisseur</td>
  <td>Commande</td>
  <td>Fournisseur</td>
  <td>Total HT</td>
  <td>Etat AR</td>
  <td>Jours attente</td>
</tr>
$table
</table>

HTML;

print <<<JAVASCRIPT
<script language="javascript">
    function relance(advid){
	var facturid=document.getElementById('facturid_'+advid).innerHTML;
    
	var msg="<p>Bonjour,</p>";
	msg+="<p>Nous n'avons toujours pas reçu l'accusé de réception de commande de la commande N°"+facturid+" du "+document.getElementById('dateadv_'+advid).innerHTML+" pour un montant total de "+document.getElementById('total_'+advid).innerHTML+" HT.</p>";
	msg+="<p>Merci de votre envoi.</p>";
	mail_auto(undefined,document.getElementById('dest_'+advid).value,undefined,"AR commande "+facturid,msg);
	mail_suivi("suivi_commande",facturid);
    }
</script>
JAVASCRIPT;

?>