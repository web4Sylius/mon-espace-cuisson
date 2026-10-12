<?php

ini_set("display_errors",1);

require "admin_global.php";
include "./header.php";
include "./menu.php";
include "../class/stock.class.php";

if(isset($_GET['action']) && $_GET['action']=="envoyer"){

    unset($_GET['action']);
    
    if(isset($_POST['efl'])){
        foreach($_POST['efl'] as $lid){
            $facture=$DB_site2->query_first("SELECT f.facturid,f.nom,f.codepostal,p.diminutif FROM facture f INNER JOIN pays p USING(paysid) WHERE facturid=".intval($_POST['assocfacturid_'.$lid]));
            $ligne=$DB_site2->query_first("SELECT * FROM envoi_fournisseur_ligne efl LEFT JOIN lignefacture lf ON(lf.lignefactureid=efl.ligne_facture_id) WHERE efl.id=".$lid." AND lf.factureid=".intval($_POST['assocfacturid_'.$lid]));
            $totalligne=($_POST['paht_'.$lid]-$_POST['prix_'.$lid]) * $_POST['qte_'.$lid];

            if($facture['facturid']=="" || $ligne['id']==""){
                $ligne['libelle']=$_POST['article_'.$lid];
                $ligne['qte']=1;
                $ligne['type']=$_POST['type_'.$lid];
                $ref="";
            }else
                $ref=$_POST['article_'.$lid];
        // var_dump("INSERT INTO avoir_ligne SET ffid='".$_POST['ffid']."', facturid=".intval($_POST['assocfacturid_'.$lid]).", eflid=".$lid.", date=".time().", utilisateur='".$user_info['username']."', destinataire='".$_POST['destinataire']."', article='".$_POST['article_'.$lid]."', paht=".floatval($totalligne).", ecopart=".floatval($_POST['ecopart_'.$lid]).", qte=".intval($_POST['qte_'.$lid]).", description='".$_POST['text_'.$lid ]."'");
            $table.="<tr><td>".$_POST['ffid']."</td><td>".$_POST['assocfacturid_'.$lid]."</td><td>".$facture['nom']." (".$facture['diminutif'].$facture['codepostal'].")</td><td>".$ligne['artcode']."</td><td>".$ref."</td><td>".$ligne['libelle']."</td><td>".number_format($_POST['paht_'.$lid]*$_POST['qte_'.$lid],2,',','')." &euro;</td><td>".number_format($_POST['prix_'.$lid]*$_POST['qte_'.$lid],2,',','')." &euro;</td><td>".number_format($totalligne,2,',','')." &euro;</td><td>".nl2br(stripslashes($_POST['text_'.$lid ]))."</td><td></td></tr>";
            
            $avoirid=intval($_POST['avoirid_'.$lid]);
            if($avoirid==0)
                $sql="INSERT INTO avoir_ligne SET ffid='".$_POST['ffid']."', facturid=".intval($_POST['assocfacturid_'.$lid]).", eflid=".$lid.", date=".time().", utilisateur='".$user_info['username']."', type='".$ligne['type']."', destinataire='".mysqli_real_escape_string($DB_site2->link_id,$_POST['destinataire'])."', article='".mysqli_real_escape_string($DB_site2->link_id,$_POST['article_'.$lid])."', paht=".floatval($totalligne).", ecopart=".floatval($_POST['ecopart_'.$lid]).", qte=".intval($_POST['qte_'.$lid]).", description='".mysqli_real_escape_string($DB_site2->link_id,$_POST['text_'.$lid])."'";
            else
                $sql="UPDATE avoir_ligne SET date=".time().", utilisateur='".$user_info['username']."', paht=".floatval($totalligne).", description='".mysqli_real_escape_string($DB_site2->link_id,$_POST['text_'.$lid])."' WHERE id=".$avoirid;
            $DB_site2->query($sql);
        }
    }
    if(isset($_POST['si'])){
        foreach($_POST['si'] as $lid){
            $totalligne=($_POST['paht_'.$lid]-$_POST['prix_'.$lid]) * $_POST['qte_'.$lid];

            $ref=$_POST['article_'.$lid];
            
            $avoirid=intval($_POST['avoirid_'.$lid]);
            if($avoirid==0)
                $sql="INSERT INTO avoir_ligne SET ffid='".$_POST['ffid']."', stock_cmd_id='".$_POST['stockcmd_'.$lid]."', date=".time().", utilisateur='".$user_info['username']."', type='".$_POST['type_'.$lid]."', destinataire='".mysqli_real_escape_string($DB_site2->link_id,$_POST['destinataire'])."', article='".mysqli_real_escape_string($DB_site2->link_id,$_POST['article_'.$lid])."', paht=".floatval($totalligne).", ecopart=".floatval($_POST['ecopart_'.$lid]).", qte=".intval($_POST['qte_'.$lid]).", description='".mysqli_real_escape_string($DB_site2->link_id,$_POST['text_'.$lid ])."'";
            else
                $sql="UPDATE avoir_ligne SET date=".time().", utilisateur='".$user_info['username']."', paht=".floatval($totalligne).", description='".mysqli_real_escape_string($DB_site2->link_id,$_POST['text_'.$lid])."' WHERE id=".$avoirid;;
            $DB_site2->query($sql);
        }
    }
}


if(isset($_POST['delavoirids'])){
    $DB_site2->query("DELETE FROM avoir_ligne".$_POST['delavoirtype']." WHERE id IN (".$_POST['delavoirids'].")");
}
if(isset($_POST['relanceids'])){
    $DB_site2->query("UPDATE avoir_ligne".$_POST['avoirtype']." SET utilisateur2='".$user_info['username']."', relance=".time().", relancenb=relancenb+1 WHERE id IN (".str_replace("|",",",$_POST['relanceids']).")");
}

if(isset($_POST['infotxt'])){
    if(in_array($_POST['infotype'],array("a","f"))) {
        $infofield=array("a"=>"info_acilyon","f"=>"info_fournisseur");
        $sql="UPDATE avoir_ligne".$_POST['infoavoirtype']." SET ".$infofield[$_POST['infotype']]."='".mysqli_real_escape_string($DB_site2->link_id,$_POST['infotxt'])."' WHERE id=".intval($_POST['avoirid']);
        $DB_site2->query($sql);
    }
}

if(!isset($_GET['action'])) $_GET['action']="";

if(isset($_POST['scrolltop'])) $scrolltop="window.addEventListener(\"load\", scrolltop, false);";

print "<LINK href=\"cal.css\" type=\"text/css\" rel=\"stylesheet\">
<style>
.llist thead { background-color:white; font-weight:bold; text-align:center;}
.llist td { background-color:#efefef; }
.infotext { border:none; width:600px; height:50px; }
.infotext:hover { border:solid 1px #CCC; }
.btn_info { display:none; }
</style>";

print "<script language=\"javascript\">
function calcavoir(){
    var totalavoir=0;
    for(i=0;i<document.getElementsByName('efl[]').length;i++){
	chk=document.getElementsByName('efl[]')[i];
	var lid=chk.value;
	if(chk.checked){
	    var totalligne=(document.getElementById('paht_'+lid).value-document.getElementById('prix_'+lid).value)*document.getElementById('qte_'+lid).value;
	    document.getElementById('totalligne_'+lid).innerHTML=Math.round(totalligne*100)/100;
	    totalavoir+=totalligne;
	    document.getElementById('submit').disabled=false;
	    document.getElementById('trtxt_'+lid).style.display='table-row';
	}else{
	    document.getElementById('totalligne_'+lid).innerHTML='';
	    document.getElementById('trtxt_'+lid).style.display='none';
	}
    }
    for(i=0;i<document.getElementsByName('si[]').length;i++){
	chk=document.getElementsByName('si[]')[i];
	var lid=chk.value;
	if(chk.checked){
	    var totalligne=(document.getElementById('paht_'+lid).value-document.getElementById('prix_'+lid).value)*document.getElementById('qte_'+lid).value;
	    document.getElementById('totalligne_'+lid).innerHTML=Math.round(totalligne*100)/100;
	    totalavoir+=totalligne;
	    document.getElementById('submit').disabled=false;
	    document.getElementById('trtxt_'+lid).style.display='table-row';
	}else{
	    document.getElementById('totalligne_'+lid).innerHTML='';
	    document.getElementById('trtxt_'+lid).style.display='none';
	}
    }
    document.getElementById('totalavoir').innerHTML=Math.round(totalavoir*100)/100;
}

function envoi(){
    if(!document.getElementsByName('group')){
		alert('Il faut configurer le contact pour les avoirs de ce fournisseur');
		return;
    }
    html=\"<p>Bonjour</p><p>Après vérification de votre facture, il s'avère que nous contestons le montant des éléments suivants : </p>\";
    html+=\"<table style='margin-top:10px;border:solid thin black;font-size:12px;' border='1' cellspacing='0'><tr><td>N° facture</td><td>Commande Acilyon</td><td>Client</td><td>Ref. Acilyon</td><td>Ref.</td><td>Dénomination</td><td>Prix facturé &euro; HT</td><td>Prix commandé &euro;HT</td><td>Avoir demandé</td><td>Détail de la demande</td><td>Votre commentaire</td></tr>\";
    var totallignes=0;
    for(i=0;i<document.getElementsByName('efl[]').length;i++){
		if(document.getElementsByName('efl[]')[i].checked){
			var lid=document.getElementsByName('efl[]')[i].value;
			totalligne=document.getElementById('qte_'+lid).value*(document.getElementById('paht_'+lid).value-document.getElementById('prix_'+lid).value);
			totallignes+=totalligne;
			
			if(document.getElementById('assocfacturid_'+lid).value==''){
			  qte=1;
			  ref='';
			  libelle=document.getElementById('article_'+lid).value;
			}else{
			  ref=document.getElementById('article_'+lid).value;
			  qte=document.getElementById('qte_'+lid).value;
			  libelle=document.getElementById('libelle_'+lid).value;
			}

			html+=\"<tr><td>\"+document.getElementsByName('ffid')[0].value+\"</td><td>\"+document.getElementById('assocfacturid_'+lid).value+\"</td><td>\"+document.getElementById('client_'+lid).value+\"</td><td>\"+document.getElementById('artcode_'+lid).value+\"</td><td>\"+ref+\"</td><td>\"+libelle+\"</td><td>\"+(Math.round(100*document.getElementById('paht_'+lid).value*qte)/100)+\" &euro;</td><td>\"+(Math.round(100*document.getElementById('prix_'+lid).value*qte)/100)+\" &euro;</td><td>\"+(Math.round(totalligne*100)/100)+\" &euro;</td><td>\"+document.getElementById('text_'+lid).value+\"</td><td></td></tr>\";
		}
    }
    for(i=0;i<document.getElementsByName('si[]').length;i++){
		if(document.getElementsByName('si[]')[i].checked){
			var lid=document.getElementsByName('si[]')[i].value;
			totalligne=document.getElementById('qte_'+lid).value*(document.getElementById('paht_'+lid).value-document.getElementById('prix_'+lid).value);
			totallignes+=totalligne;
			
			ref=document.getElementById('article_'+lid).value;
			qte=document.getElementById('qte_'+lid).value;
			libelle=document.getElementById('libelle_'+lid).value;

			html+=\"<tr><td>\"+document.getElementsByName('ffid')[0].value+\"</td><td>Stock n°\"+document.getElementById('stockcmd_'+lid).value+\"</td><td></td><td>\"+document.getElementById('artcode_'+lid).value+\"</td><td>\"+ref+\"</td><td>\"+libelle+\"</td><td>\"+(Math.round(100*document.getElementById('paht_'+lid).value*qte)/100)+\" &euro;</td><td>\"+(Math.round(100*document.getElementById('prix_'+lid).value*qte)/100)+\" &euro;</td><td>\"+(Math.round(totalligne*100)/100)+\" &euro;</td><td>\"+document.getElementById('text_'+lid).value+\"</td><td></td></tr>\";
		}
    }
    html+=\"<tr><td colspan='8' align='right'>Total avoir : </td><td><b>\"+(Math.round(totallignes*100)/100)+\" &euro;</b></td></tr></table>\";
    html+=\"<p>Sans réception de l'avoir ou retour de votre part sous 30 jours, une note de débit sera automatiquement émise et déduite du paiement de la facture.</p>\";

    mail_auto(undefined,document.getElementsByName('group')[0].value,\"$user_info[mail_contact]\",\"Demande d'avoirs\",html);
    sendform=document.forms.avoir;
}

function relance(avoirids,mails,type){
    if(avoirids=='') return;
    avoirid=avoirids.split('|');
    document.getElementsByName('relanceids')[0].value=avoirids;
    document.getElementsByName('avoirtype')[0].value=type==undefined?'':type;
    var okmail=false;
    var totalavoir=0;
    var html=\"<p>Bonjour,</p><p>Suite à notre précédente demande, nous n'avons toujours pas reçu les avoirs suivants :</p>\";
    html+=\"<table style='font-size:12px; margin-top:10px;' cellspacing='0' border='1'><tr><td>N° facture</td><td>Commande Acilyon</td><td>Client</td><td>Ref.</td><td>Dénomination</td><td>Prix facturé &euro; HT</td><td>Prix commandé  &euro;HT</td><td>Valeur avoir &euro; HT</td><td>Détail de la demande</td><td>Info Acilyon</td><td>Info fournisseur</td><td>Votre commentaire</td></tr>\";
    for(i=0;i<avoirid.length;i++){
		if(document.getElementsByName('relance_'+avoirid[i])[0].checked){
	    	var client=document.getElementsByName('client_'+avoirid[i])[0].value;
	    	var facturid=document.getElementsByName('facturid_'+avoirid[i])[0].value;
	    	var prixfacture='';
	    	var prixcommande='';
	    	if(document.getElementsByName('stockcmd_'+avoirid[i])[0].value!=0){
	      		client='';
	      		facturid='Stock n°'+document.getElementsByName('stockcmd_'+avoirid[i])[0].value;
	      		prixfacture=document.getElementsByName('paht_'+avoirid[i])[0].value+' &euro;';
	      		prixcommande=(Math.round((document.getElementsByName('paht_'+avoirid[i])[0].value-document.getElementsByName('prix_'+avoirid[i])[0].value)*100)/100)+' &euro;';

	    	}else{
	      		prixfacture=document.getElementsByName('paht_'+avoirid[i])[0].value+' &euro;';
	      		prixcommande=(Math.round((document.getElementsByName('paht_'+avoirid[i])[0].value-document.getElementsByName('prix_'+avoirid[i])[0].value)*100)/100)+' &euro;';
    		}
	    	html+=\"<tr><td>\"+document.getElementsByName('ffid_'+avoirid[i])[0].value+\"</td><td>\"+facturid+\"</td><td>\"+client+\"</td><td>\"+document.getElementsByName('article_'+avoirid[i])[0].value+\"</td><td>\"+document.getElementsByName('libelle_'+avoirid[i])[0].value+\"</td><td align='right' nowrap>\"+prixfacture+\"</td><td align='right' nowrap>\"+prixcommande+\"</td><td align='right' nowrap>\"+document.getElementsByName('prix_'+avoirid[i])[0].value+\" &euro;</td><td>\"+document.getElementsByName('description_'+avoirid[i])[0].value+\"</td><td>\"+document.getElementById('info_a_'+avoirid[i]).value+\"</td><td>\"+document.getElementById('info_f_'+avoirid[i]).value+\"</td><td></td></tr>\";
	    	totalavoir+=parseFloat(document.getElementsByName('prix_'+avoirid[i])[0].value);
	    	okmail=true;
		}
    }
    html+=\"</table>\";
    html+=\"<p>Montant total des avoirs demandés : <b>\"+Math.round(100*totalavoir)/100+\" &euro; HT</b></p>\";
    html+=\"<p>Nous vous remercions de nous tenir informés,</p>\";
    
    if(okmail){
      mail_auto(undefined,mails,'$user_info[mail_contact]','Relance demande avoirs',html);
      sendform=document.forms.relanceform;
    }else
      alert('Aucun avoir est sélectionné');
}

function annule(avoirids,mails,type){
    if(avoirids=='') return;
    document.getElementsByName('delavoirids')[0].value='';
    document.getElementsByName('delavoirtype')[0].value=type==undefined?'':type;
    avoirid=avoirids.split('|');
    var okmail=false;
    var html=\"<p>Bonjour,</p><p>Nous annulons les demandes d'avoir suivantes :</p>\";
    html+=\"<table style='margin-top:10px;' cellspacing='0' border='1'><tr><td>Facture</td><td>Commande Acilyon</td><td>Client</td><td>Ref.</td><td>Dénomination</td><td>Valeur avoir HT</td><td>Raison</td></tr>\";
    for(i=0;i<avoirid.length;i++){
	if(document.getElementsByName('relance_'+avoirid[i])[0].checked){
	    document.getElementsByName('delavoirids')[0].value+=','+avoirid[i];
	    var client=document.getElementsByName('client_'+avoirid[i])[0].value;
	    var facturid=document.getElementsByName('facturid_'+avoirid[i])[0].value;
	    if(document.getElementsByName('stockcmd_'+avoirid[i])[0].value!=0){
	      client='';
	      facturid='Stock n°'+document.getElementsByName('stockcmd_'+avoirid[i])[0].value;
	    }
	    html+=\"<tr><td>\"+document.getElementsByName('ffid_'+avoirid[i])[0].value+\"</td><td>\"+facturid+\"</td><td>\"+client+\"</td><td>\"+document.getElementsByName('article_'+avoirid[i])[0].value+\"</td><td>\"+document.getElementsByName('libelle_'+avoirid[i])[0].value+\"</td><td align='right'>\"+document.getElementsByName('prix_'+avoirid[i])[0].value+\" &euro;</td><td>\"+document.getElementsByName('description_'+avoirid[i])[0].value+\"</td></tr>\";
	    okmail=true;
	}
    }
    html+=\"</table>\";
    html+=\"<p>Comme précisé lors de la première demande, nous vous remercions de nous faire parvenir les avoirs correspondants sous 72 heures. A défaut de remarque ou de réception de l'avoir dans ce délai, une note de débit sera systématiquement éditée et vous sera envoyée,</p>\";

    document.getElementsByName('delavoirids')[0].value=document.getElementsByName('delavoirids')[0].value.substr(1);
    
    if(okmail){
      mail_auto(undefined,mails,'$user_info[mail_contact]','Annulation demande avoirs',html);
      sendform=document.forms.annulation;
    }else
      alert('Aucun avoir est sélectionné');
}

function configligne(lid){
    if(document.getElementById('article_'+lid)) document.getElementById('article_'+lid).name='article_'+lid;
    if(document.getElementById('avoirid_'+lid)) document.getElementById('avoirid_'+lid).name='avoirid_'+lid;
    if(document.getElementById('qte_'+lid)) document.getElementById('qte_'+lid).name='qte_'+lid;
    if(document.getElementById('prix_'+lid)) document.getElementById('prix_'+lid).name='prix_'+lid;
    if(document.getElementById('paht_'+lid)) document.getElementById('paht_'+lid).name='paht_'+lid;
    if(document.getElementById('ecopart_'+lid)) document.getElementById('ecopart_'+lid).name='ecopart_'+lid;
    if(document.getElementById('assocfacturid_'+lid)) document.getElementById('assocfacturid_'+lid).name='assocfacturid_'+lid;
    if(document.getElementById('stockcmd_'+lid)) document.getElementById('stockcmd_'+lid).name='stockcmd_'+lid;
    if(document.getElementById('libelle_'+lid)) document.getElementById('libelle_'+lid).name='libelle_'+lid;
    if(document.getElementById('artcode_'+lid)) document.getElementById('artcode_'+lid).name='artcode_'+lid;
    if(document.getElementById('client_'+lid)) document.getElementById('client_'+lid).name='client_'+lid;
    if(document.getElementById('text_'+lid)) document.getElementById('text_'+lid).name='text_'+lid;
    if(document.getElementById('type_'+lid)) document.getElementById('type_'+lid).name='type_'+lid;
}

function checkall(state,chklist) {
    for(i=0;i<chklist.length;i++)
	document.getElementsByName('relance_'+chklist[i])[0].checked=state;
}

function active_info(type,id) {
    document.getElementById('info_'+type+'_'+id).style.border='solid 1px black';
    document.getElementById('btn_'+type+'_'+id).style.display='block';
}

function update_info(type,id,avoirtype) {
    document.getElementsByName('infoavoirtype')[0].value=avoirtype==undefined?'':avoirtype;
    document.getElementsByName('infotype')[0].value=type;
    document.getElementsByName('avoirid')[0].value=id;
    document.getElementsByName('infotxt')[0].value=document.getElementById('info_'+type+'_'+id).value;
    document.getElementsByName('scrolltop')[0].value=document.body.scrollTop;
    document.forms.infoform.submit();
}

function scrolltop(){
  document.body.scrollTop=".intval($_POST['scrolltop']).";
}

$scrolltop
</script>";

echo "<h1>Demande d'avoir</h1>";

if($_GET['action']==""){
    

      $requete=$DB_site2->query("SELECT * FROM avoir_ligne WHERE date_recu=0 ORDER BY destinataire,date");
      $destfourn="";
      $totalavoir=0;
      $total=0;
      $tr="";
      
      
      while($avoir=$DB_site2->fetch_array($requete)){

            $ncmd="";
            if($avoir['facturid']!=0){
                $idorigin=$avoir['facturid'];
                $ncmd=" cmd <a href=\"facture.php?facturid=".$avoir['facturid']."&action=editer\" target=\"_blank\">n°".$avoir['facturid']."</a>";
            }elseif($avoir['stock_cmd_id']!=0){
                $idorigin=$avoir['stock_cmd_id'];
                $ncmd=" cmd stock <a href=\"stock.php?action=cmd&stock_cmd_id=".$avoir['stock_cmd_id']."\" target=\"_blank\">n°".$avoir['stock_cmd_id']."</a>";
            }

            if($destfourn!=$avoir['destinataire'] || $rownb==$i){
                if($totalavoir!=0){
                    $mailist=array();
                    $r=$DB_site2->query("SELECT * FROM fournisseur WHERE societe='".mysqli_real_escape_string($DB_site2->link_id,$destfourn)."' AND type='avoir'");
                    while($dest=$DB_site2->fetch_array($r))
                        if($dest['mail']!="") $mailist[]=$dest['mail'];

                    $tr.="<tr><td colspan=\"3\"><input type=\"checkbox\" onclick=\"checkall(this.checked,new Array(".implode(",",$agroup)."))\">Tout cocher <input type=\"button\" value=\"Relancer\" onclick=\"relance('".implode('|',$agroup)."','".implode(';',$mailist)."')\"> <input type=\"button\" value=\"Annuler\" onclick=\"annule('".implode('|',$agroup)."','".implode(';',$mailist)."')\"></td><td colspan=\"2\" align=\"right\" height=\"30\">Total $dest :</td><td style=\"font-weight:bold;text-align:right;\">".number_format($totalavoir,2,',','')."</td></tr>";
                }
                $tr.="<tr><td colspan=\"6\" style=\"text-align:center;background-color:#cccccc;\"><a name=\"".$avoir['destinataire']."\"></a><b>".$avoir['destinataire']."</b></td></tr>";
                $totalavoir=0;
                $agroup=array();
                $destlist[]=$avoir['destinataire'];
            }
	        $efl = array();
            if($avoir['eflid']>0){
                $efl=$DB_site2->query_first("SELECT efl.* FROM envoi_fournisseur_ligne efl INNER JOIN lignefacture lf ON(lf.lignefactureid=efl.ligne_facture_id) WHERE efl.id=".$avoir['eflid']." AND lf.factureid=".intval($idorigin));
                if($efl['id']=="") $efl=$DB_site2->query_first("SELECT 1 AS qte,prix AS paht,description AS libelle FROM facture_fournisseur_autre WHERE id=".$avoir['eflid']);
                if($efl['id']=="") $efl['libelle']=$avoir['article'];
            }elseif($avoir['stock_cmd_id']>0){
            	//on recupere le paht et la qte pour une cmd de stock
            	$sql="SELECT COUNT(si.paht) as qte, si.paht FROM stock_item si INNER JOIN stock_cmd sc ON si.stock_cmd_id=sc.id INNER JOIN avoir_ligne al ON sc.id=al.stock_cmd_id WHERE sc.id='".$avoir['stock_cmd_id']."' AND si.artid=(SELECT artid FROM article WHERE artcode_eurocave='".$avoir['article']."' LIMIT 1)";
            	$paht_stck = $DB_site2->query_first($sql);
            	$efl['qte']=intval($paht_stck['qte']);
                $efl['paht']=$paht_stck['paht'];
            }
            $facture=$DB_site2->query_first("SELECT f.nom,f.codepostal,p.diminutif, f.datefacture FROM facture f INNER JOIN pays p USING(paysid) WHERE facturid=".intval($idorigin));
            
            // Modif #SI2243: Ajout de la date de la commande dans la relance
            $factureDate=$DB_site2->query_first("SELECT date_facture as date FROM facture_fournisseur f WHERE numero='".mysqli_real_escape_string($DB_site2->link_id,$avoir['ffid'])."'");
            $ladate=date("d-m-Y",$factureDate['date']);
            
            $relance="";
            if($avoir['relance']!=0) $relance="<br><span style=\"color:blue\">Relance n°".$avoir['relancenb']." le ".date("d/m/Y",$avoir['relance'])." par ".$avoir['utilisateur2']."</span>";
    
            $tr.="<tr><td><input type=\"checkbox\" name=\"relance_".$avoir['id']."\" value=\"".$avoir['id']."\">
                            <input type=\"hidden\" name=\"article_".$avoir['id']."\" value=\"".$avoir['article']."\">
                            <input type=\"hidden\" name=\"paht_".$avoir['id']."\" value=\"".round(($efl['qte']*($efl['paht']+$efl['ecopart'])),2)."\">
                            <input type=\"hidden\" name=\"prix_".$avoir['id']."\" value=\"".($avoir['paht'])."\">
                            <input type=\"hidden\" name=\"ffid_".$avoir['id']."\" value=\"".$avoir['ffid']." ".$ladate."\">
                            <input type=\"hidden\" name=\"libelle_".$avoir['id']."\" value=\"".$efl['libelle']."\">
                            <input type=\"hidden\" name=\"description_".$avoir['id']."\" value=\"".$avoir['description']."\">
                            <input type=\"hidden\" name=\"facturid_".$avoir['id']."\" value=\"".$avoir['facturid']."\">
                            <input type=\"hidden\" name=\"stockcmd_".$avoir['id']."\" value=\"".$avoir['stock_cmd_id']."\">
                            <input type=\"hidden\" name=\"client_".$avoir['id']."\" value=\"".($facture!=""?$facture['nom']." (".$facture['diminutif'].$facture['codepostal'].")":"")."\">
                        </td>
                        <td><a href=\"fournisseur.php?action=editer&facturid=".$avoir['ffid']."\">".$avoir['ffid']." ".$ladate."</a></td>
                        <td>".date("d/m/Y H:i",$avoir['date'])."<br>".$avoir['utilisateur']."$relance</td><td>".$avoir['article'].$ncmd."</td>
                        <td align=\"right\">".number_format($avoir['paht'],2,',','')."</td></tr>
                        <tr><td colspan=\"5\">".$avoir['description']."&nbsp;</td></tr>
                        <tr><td>Infos Acilyon :</td><td colspan=\"4\"><textarea id=\"info_a_".$avoir['id']."\" class=\"infotext\" onclick=\"active_info('a',".$avoir['id'].")\">".$avoir['info_acilyon']."</textarea><input type=\"button\" id=\"btn_a_".$avoir['id']."\" value=\"OK\" class=\"btn_info\" onclick=\"update_info('a',".$avoir['id'].")\"></td></tr>
                        <tr><td>Infos fournisseur :</td><td colspan=\"4\" style=\"border-bottom:solid thin black;\"><textarea id=\"info_f_".$avoir['id']."\" class=\"infotext\" onclick=\"active_info('f',".$avoir['id'].")\">".$avoir['info_fournisseur']."</textarea><input type=\"button\" id=\"btn_f_".$avoir['id']."\" value=\"OK\" class=\"btn_info\" onclick=\"update_info('f',".$avoir['id'].")\"></td></tr>";
            $destfourn=stripslashes($avoir['destinataire']);
            $totalavoir+=$avoir['paht'];
            $total+=$avoir['paht'];
            $agroup[]=$avoir['id'];
      }
      if($totalavoir>0){
            $mailist=array();
            $r=$DB_site2->query("SELECT * FROM fournisseur WHERE societe='".$destfourn."' AND type='avoir'");
            while($dest=$DB_site2->fetch_array($r))
                if($dest['mail']!="") $mailist[]=$dest['mail'];

            $tr.="<tr><td colspan=\"3\"><input type=\"checkbox\" onclick=\"checkall(this.checked,new Array(".implode(",",$agroup)."))\">Tout cocher <input type=\"button\" value=\"Relancer\" onclick=\"relance('".implode('|',$agroup)."','".implode(';',$mailist)."')\"> <input type=\"button\" value=\"Annuler\" onclick=\"annule('".implode('|',$agroup)."','webmaster@mon-espace-cuisson.com')\"></td><td colspan=\"2\" align=\"right\">Total $dest :</td><td style=\"font-weight:bold;text-align:right;\">".number_format($totalavoir,2,',','')."</td></tr>";
      }

      $totalavoir=0;
      $destlist2=array();
      $requete=$DB_site2->query("SELECT alf.*,fff.id AS ffflineid FROM avoir_ligne_fg alf INNER JOIN facture_fournisseur_frais fff ON(alf.ffid=fff.numero) WHERE alf.date_recu=0 ORDER BY alf.destinataire,alf.date");
      while($avoir=$DB_site2->fetch_array($requete)){
            if(!in_array($avoir['destinataire'],$destlist2)){
                if($totalavoir!=0){
                    $tr.="<tr><td colspan=\"3\"><input type=\"checkbox\" onclick=\"checkall(this.checked,new Array('".implode("','",$agroup)."'))\">Tout cocher <input type=\"button\" value=\"Relancer\" onclick=\"relance('".implode('|',$agroup)."','','_fg')\"></td><td colspan=\"2\" align=\"right\" height=\"30\">Total :</td><td style=\"font-weight:bold;text-align:right;\">".number_format($totalavoir,2,',','')."</td></tr>";
                }
                
                $tr.="<tr><td colspan=\"6\" style=\"text-align:center;background-color:#cccccc;\"><a name=\"".$avoir['destinataire']."\"></a><b>".$avoir['destinataire']."</b></td></tr>";
                $totalavoir=0;
                $agroup=array();
                $destlist[]=$avoir['destinataire'];
                $destlist2[]=$avoir['destinataire'];
            }
            
            $relance="";
            if($avoir['relance']!=0) $relance="<br><span style=\"color:blue\">Relance n°".$avoir['relancenb']." le ".date("d/m/Y",$avoir['relance'])." par ".$avoir['utilisateur2']."</span>";
            
            $ff=$DB_site2->query_first("SELECT * FROM facture_fournisseur_frais WHERE numero='".mysqli_real_escape_string($DB_site2->link_id,$avoir['ffid'])."'");
            $ladate=date("d-m-Y",$ff['date_facture']);
          
            $lid=$avoir['id'];
            $tr.="<tr>
                    <td><input type=\"checkbox\" name=\"relance_".$lid."\" value=\"".$avoir['id']."\">
                        <input type=\"hidden\" name=\"article_".$lid."\" value=\"\">
                        <input type=\"hidden\" name=\"paht_".$lid."\" value=\"".round($ff['montant_ht'],2)."\">
                        <input type=\"hidden\" name=\"prix_".$lid."\" value=\"".($avoir['prix'])."\">
                        <input type=\"hidden\" name=\"ffid_".$lid."\" value=\"".$avoir['ffid']." ".$ladate."\">
                        <input type=\"hidden\" name=\"libelle_".$lid."\" value=\"\">
                        <input type=\"hidden\" name=\"description_".$lid."\" value=\"".$avoir['description']."\">
                        <input type=\"hidden\" name=\"facturid_".$lid."\" value=\"\">
                        <input type=\"hidden\" name=\"stockcmd_".$lid."\" value=\"\">
                        <input type=\"hidden\" name=\"client_".$lid."\" value=\"\">
                    </td>
                    <td><a href=\"fournisseur_frais.php?action=editer&id=".$avoir['ffflineid']."\">".$avoir['ffid']."<br>".$ladate."</a></td>
                    <td>".date("d/m/Y H:i",$avoir['date'])."<br>".$avoir['utilisateur'].$relance."</td><td></td>
                    <td align=\"right\">".number_format($avoir['prix'],2,',','')."</td>
                </tr>
                <tr>
                    <td colspan=\"5\">".$avoir['description']."&nbsp;</td>
                </tr>
                <tr>
                    <td>Infos Acilyon :</td>
                    <td colspan=\"4\"><textarea id=\"info_a_".$lid."\" class=\"infotext\" onclick=\"active_info('a','".$lid."')\">".$avoir['info_acilyon']."</textarea><input type=\"button\" id=\"btn_a_".$lid."\" value=\"OK\" class=\"btn_info\" onclick=\"update_info('a','".$lid."','_fg')\"></td>
                </tr>
                <tr>
                    <td>Infos fournisseur :</td>
                    <td colspan=\"4\" style=\"border-bottom:solid thin black;\"><textarea id=\"info_f_".$lid."\" class=\"infotext\" onclick=\"active_info('f','".$lid."')\">".$avoir['info_fournisseur']."</textarea><input type=\"button\" id=\"btn_f_".$lid."\" value=\"OK\" class=\"btn_info\" onclick=\"update_info('f','".$lid."','_fg')\"></td>
                </tr>";
            
            $totalavoir+=$avoir['prix'];
            $total+=$avoir['prix'];
            $agroup[]=$lid;
      }
      
      if($totalavoir!=0){
            $tr.="<tr><td colspan=\"3\"><input type=\"checkbox\" onclick=\"checkall(this.checked,new Array('".implode("','",$agroup)."'))\">Tout cocher <input type=\"button\" value=\"Relancer\" onclick=\"relance('".implode('|',$agroup)."','','_fg')\"> <input type=\"button\" value=\"Annuler\" onclick=\"annule('".implode('|',$agroup)."','','_fg')\"></td><td colspan=\"2\" align=\"right\" height=\"30\">Total :</td><td style=\"font-weight:bold;text-align:right;\">".number_format($totalavoir,2,',','')."</td></tr>";
        }
      
      print "<p><b>Avoirs envoyés en attente</b></p>
      <form name=\"annulation\" method=\"post\">
      <input type=\"hidden\" name=\"delavoirtype\">
      <input type=\"hidden\" name=\"delavoirids\">
      </form>
      <form name=\"relanceform\" method=\"post\">
      <input type=\"hidden\" name=\"avoirtype\">
      <input type=\"hidden\" name=\"relanceids\">
      </form>
      <form name=\"infoform\" method=\"post\">
      <input type=\"hidden\" name=\"infoavoirtype\">
      <input type=\"hidden\" name=\"scrolltop\" value=\"0\">
      <input type=\"hidden\" name=\"avoirid\" value=\"0\">
      <input type=\"hidden\" name=\"infotype\" value=\"\">
      <input type=\"hidden\" name=\"infotxt\" value=\"\">
      </form>";
      foreach($destlist as $dest) print "<a href=\"#".$dest."\">".$dest."</a> &nbsp;";
      print "<table width=\"700\">
      <thead style=\"font-weight:bold;\">
      <tr><td></td><td>Facture</td><td>Demande</td><td>Article</td><td>Valeur HT</td><td></td></tr>
      </thead>";
      print $tr;
      print "</table>
      <div style=\"font-weight:bold;text-align:center;width:400px;height:40px;line-height:40px;border:solid thin black;margin-top:10px;\">Total avoirs en cours : ".number_format($total,2,',','')." &euro;</div>";
      
      print "<div class=\"remonte\" onclick=\"document.body.scrollTop=0;\"></div>";
      
      
}

if($_GET['action']=="ajouter"){
    $ff=$DB_site2->query_first("SELECT * FROM facture_fournisseur WHERE numero='".$_GET['ffid']."'");
    $group=array();
    $societe=stripslashes($ff['fournisseur']);
    $list=$DB_site2->query("SELECT * FROM fournisseur WHERE `type`='avoir' AND societe='".mysqli_real_escape_string($DB_site2->link_id,$societe)."'");
    while($advf=$DB_site2->fetch_array($list)){
        if($advf['groupes']!=""){
            $csv=explode(",",$advf['groupes']);
            foreach($csv as $g)
                $group[$g][]=$advf['mail'];
        }else
            $group[$advf['societe']][]=$advf['mail'];
    }
    $nbgroup=count($group);

    $selgroup="";
    if($group!=array() && $nbgroup>1){
        $selgroup="<p>Sélectionnez le destinataire : <select name=\"group\">";
        foreach($group as $g=>$mails)
            $selgroup.="<option value=\"".implode(";",$mails)."\">$g</option>";
        $selgroup.="</select></p>";
    }elseif($nbgroup>0)
        $selgroup="<input type=\"hidden\" name=\"group\" value=\"".implode(";",array_shift($group))."\">";

    if($nbgroup==0) $selgroup="<p><font color=\"red\"><strong>Attention, aucun contact n'est créé pour les demandes d'avoir chez ce fournisseur.<br>Vous ne pouvez donc pas créer de demande d'avoir</strong></font></p>";

    $disablesend="disabled";

    print "<div>Demande d'avoir sur facture n°".$ff['numero']." ".$ff['fournisseur']."</div>";
    print "<br><form name=\"avoir\" action=\"avoir_fournisseur.php?action=envoyer\" method=\"post\">
    <input type=\"hidden\" name=\"ffid\" value=\"".$ff['numero']."\">
    <input type=\"hidden\" name=\"destinataire\" value=\"".$ff['fournisseur']."\">
    $selgroup
    <table class=\"llist\">
    <thead>
    <tr><td></td><td>Article</td><td>PU HT facturé<br><small>avec eco part.</small></td><td>PU HT réel<br><small>avec eco part.</small></td><td>Quantité</td><td>Valeur avoir</td></tr>
    </thead>";
    $lignes=$DB_site2->query("SELECT lf.artcode,lf.factureid,efl.id,lf.libelle,efl.qte,efl.ecopart,efl.paht, ef.comment FROM envoi_fournisseur_ligne efl INNER JOIN lignefacture lf ON(lf.lignefactureid=efl.ligne_facture_id) INNER JOIN envoi_fournisseur ef ON(ef.id=efl.efid)  WHERE efl.facture='".$_GET['ffid']."'");
    while($ligne=$DB_site2->fetch_array($lignes)){
        $article=$DB_site2->query_first("SELECT artcode_eurocave FROM article WHERE artcode='".$ligne['artcode']."'");
        $avoir=$DB_site2->query_first("SELECT * FROM avoir_ligne WHERE ffid='".$_GET['ffid']."' AND eflid=".$ligne['id']);
        $facture=$DB_site2->query_first("SELECT * FROM facture INNER JOIN pays USING(paysid) WHERE facturid=".$ligne['factureid']);
        $envoi="";
        $infos_compta="";
        $chkbox="";
        if($ligne['comment'] != '')
            $infos_compta="Infos compta : ";

        if($avoir['date']!="" && $article[0]!=""){
            if($avoir['date']==0){
                $envoi="<div style=\"color:red;\">Avoir de ".number_format($avoir['paht']*$avoir['qte'],2,',','')." &euro; à envoyer</div>";
        // 		    $chkbox="checked";
            }else{
                $envoi="<div style=\"color:".($avoir['date_recu']==0?"red":"green").";\">Avoir de ".number_format($avoir['paht']*$avoir['qte'],2,',','')." &euro; envoyé le ".date("d/m/Y",$avoir['date'])." par ".$avoir['utilisateur'].($avoir['date_recu']>0?" reçu le ".date("d/m/Y",$avoir['date_recu']):"")."</div>";
                $chkbox="disabled";
            }
        }
        print "<tr>
                    <td><input type=\"checkbox\" id=\"ef_".$ligne['id']."\" name=\"efl[]\" value=\"".$ligne['id']."\" onclick=\"configligne(this.value);calcavoir();\" ".$chkbox."><input type=\"hidden\" id=\"avoirid_".$ligne['id']."\" value=\"".$avoir['id']."\"><input type=\"hidden\" id=\"artcode_".$ligne['id']."\" value=\"".$ligne['artcode']."\"><input type=\"hidden\" id=\"libelle_".$ligne['id']."\" value=\"".$ligne['libelle']."\"><input type=\"hidden\" id=\"client_".$ligne['id']."\" value=\"".$facture['nom']." (".$facture['diminutif'].$facture['codepostal'].")\"></td>
                    <td>".$ligne['factureid']." ".$ligne['artcode']." <input type=\"text\" id=\"article_".$ligne['id']."\" value=\"".$article[0]."\"><br>".$ligne['libelle'].$envoi."<br><span style=\"color:red;\">".$infos_compta."<br>".$ligne['comment']."</span></td>
                    <td><input type=\"hidden\" id=\"assocfacturid_".$ligne['id']."\" value=\"".$ligne['factureid']."\"><input type=\"hidden\" id=\"paht_".$ligne['id']."\" value=\"".($ligne['paht']+$ligne['ecopart'])."\"><input type=\"hidden\" id=\"ecopart_".$ligne['id']."\" value=\"".$ligne['ecopart']."\">".number_format($ligne['paht']+$ligne['ecopart'],2,',','')."</td>
                    <td><input type=\"text\" id=\"prix_".$ligne['id']."\" value=\"0\" size=\"4\" onblur=\"calcavoir();\"></td>
                    <td align=\"center\"><input type=\"text\" id=\"qte_".$ligne['id']."\" value=\"".($avoir['qte']!=""?$avoir['qte']:$ligne['qte'])."\" size=\"1\"></td>
                    <td id=\"totalligne_".$ligne['id']."\"></td>
                </tr>
                <tr id=\"trtxt_".$ligne['id']."\" style=\"display:none;\">
                    <td colspan=\"6\"><textarea id=\"text_".$ligne['id']."\" rows=\"3\" cols=\"60\">".trim($ligne['comment']." ".$avoir['description'])."</textarea></td>
                </tr>";
    }
    
    $lignes=$DB_site2->query("SELECT * FROM facture_fournisseur_autre WHERE facture='".mysqli_real_escape_string($DB_site2->link_id,$_GET['ffid'])."'");
    while($ligne=$DB_site2->fetch_array($lignes)){
        $avoir=$DB_site2->query_first("SELECT * FROM avoir_ligne WHERE ffid='".mysqli_real_escape_string($DB_site2->link_id,$_GET['ffid'])."' AND article='".mysqli_real_escape_string($DB_site2->link_id,$ligne['description'])." ".$ligne['facturid']."'");
        $facture=$DB_site2->query_first("SELECT * FROM facture INNER JOIN pays USING(paysid) WHERE facturid=".intval($ligne['facturid']));
        $envoi="";
        $chkbox="";
        $travoirdesc="display:none;";
        if($avoir['date']!="" && $avoir['article']!=""){
            if($avoir['date']==0){
                $envoi="<div style=\"color:red;\">Avoir de ".number_format($avoir['paht']*$avoir['qte'],2,',','')." &euro; à envoyer</div>";
        // 		    $chkbox="checked";
        // 		    $travoirdesc="";
        // 		    $disablesend="";
            }else{
                $envoi="<div style=\"color:".($avoir['date_recu']==0?"red":"green").";\">Avoir de ".number_format($avoir['paht']*$avoir['qte'],2,',','')." &euro; envoyé le ".date("d/m/Y",$avoir['date'])." par ".$avoir['utilisateur'].($avoir['date_recu']>0?" reçu le ".date("d/m/Y",$avoir['date_recu']):"")."</div>";
                $chkbox="disabled";
            }
        }
        
        print "<tr>
                <td><input type=\"checkbox\" id=\"ef_".$ligne['id']."\" name=\"efl[]\" value=\"".$ligne['id']."\" onclick=\"configligne(this.value);calcavoir();\" ".$chkbox."><input type=\"hidden\" id=\"avoirid_".$ligne['id']."\" value=\"".$avoir['id']."\"><input type=\"hidden\" id=\"artcode_".$ligne['id']."\" value=\"".$ligne['artcode']."\"><input type=\"hidden\" id=\"libelle_".$ligne['id']."\" value=\"".$ligne['libelle']."\"><input type=\"hidden\" id=\"client_".$ligne['id']."\" value=\"".$facture['nom']." (".$facture['diminutif'].$facture['codepostal'].")\"><input type=\"hidden\" id=\"type_".$ligne['id']."\" value=\"".$ligne['type']."\"></td>
                <td>".$ligne['facturid']." <input type=\"text\"id=\"article_".$ligne['id']."\" value=\"".$ligne['description']." ".$ligne['facturid']."\" size=\"40\"><br>".$ligne['libelle'].$envoi."</td>
                <td><input type=\"hidden\" id=\"assocfacturid_".$ligne['id']."\" value=\"".$ligne['facturid']."\"><input type=\"hidden\" id=\"paht_".$ligne['id']."\" value=\"".$ligne['prix']."\"><input type=\"hidden\" id=\"ecopart_".$ligne['id']."\" value=\"0\">".number_format($ligne['prix'],2,',','')."</td>
                <td><input type=\"text\" id=\"prix_".$ligne['id']."\" value=\"0\" size=\"4\" onblur=\"calcavoir();\"></td>
                <td align=\"center\"><input type=\"text\" id=\"qte_".$ligne['id']."\" value=\"1\" size=\"1\"></td>
                <td id=\"totalligne_".$ligne['id']."\"></td>
            </tr>
            <tr id=\"trtxt_".$ligne['id']."\" style=\"".$travoirdesc."\">
                <td colspan=\"6\"><textarea id=\"text_".$ligne['id']."\" rows=\"3\" cols=\"60\">".$avoir['description']."</textarea></td>
            </tr>";
    }
    
    $stock=new stock($DB_site2);
    foreach(array("fourn","trspt") as $type){
        $cmdlist=$stock->getfacture($_GET['ffid'],$type);
        foreach($cmdlist as $cmdid=>$items){
            foreach($items as $item){
                $infos_compta = $DB_site2->query_first("SELECT comment FROM stock_item where stock_cmd_id='".$item->stock_cmd_id."' AND artid='".$item->artid."' ");
                if($infos_compta != '')
                    $infos_compta = "Infos compta : ".$infos_compta[0];
        
                $lid=uniqid();
                $avoir=$DB_site2->query_first("SELECT * FROM avoir_ligne WHERE ffid='".mysqli_real_escape_string($DB_site2->link_id,$_GET['ffid'])."' AND article='".$item->ref."'");
                $envoi="";
                $chkbox="";
                if($avoir['date']!="" && $avoir['article']!=""){
                    if($avoir['date']==0){
                        $envoi="<div style=\"color:red;\">Avoir de ".number_format($avoir['paht']*$avoir['qte'],2,',','')." &euro; à envoyer</div>";
        // 				$chkbox="checked";
                    }else{
                        $envoi="<div style=\"color:".($avoir['date_recu']==0?"red":"green").";\">Avoir de ".number_format($avoir['paht']*$avoir['qte'],2,',','')." &euro; envoyé le ".date("d/m/Y",$avoir['date'])." par ".$avoir['utilisateur'].($avoir['date_recu']>0?" reçu le ".date("d/m/Y",$avoir['date_recu']):"")."</div>";
                        $chkbox="disabled";
                    }
                }

                    
                print "<tr>
                            <td><input type=\"checkbox\" id=\"si_".$item->artid."\" name=\"si[]\" value=\"".$lid."\" onclick=\"configligne('$lid');calcavoir();\" ".$chkbox."><input type=\"hidden\" id=\"avoirid_".$lid."\" value=\"".$avoir['id']."\"><input type=\"hidden\" id=\"artcode_".$lid."\" value=\"".$item->artcode."\"><input type=\"hidden\" id=\"libelle_".$lid."\" value=\"".$item->libelle."\"><input type=\"hidden\" id=\"stockcmd_".$lid."\" value=\"".$item->stock_cmd_id."\"><input type=\"hidden\" id=\"type_".$lid."\" value=\"".$type."\"></td>
                            <td>".$item->stock_cmd_id." <input type=\"text\"id=\"article_".$lid."\" value=\"".$item->ref."\" size=\"40\"><br>".$item->libelle.$envoi."</td>
                            <td><input type=\"hidden\" id=\"paht_".$lid."\" value=\"".round(($item->paht+$item->ecopartht)/$item->qte,2)."\"><input type=\"hidden\" id=\"ecopart_".$lid."\" value=\"".($item->ecopartht/$item->qte)."\">".number_format(($item->paht+$item->ecopartht)/$item->qte,2,',','')."</td>
                            <td><input type=\"text\" id=\"prix_".$lid."\" size=\"4\" value=\"0\" onblur=\"calcavoir();\"></td>
                            <td align=\"center\"><input type=\"text\" id=\"qte_".$lid."\" value=\"".($avoir['qte']!=""?$avoir['qte']:$item->qte)."\" size=\"1\"></td>
                            <td id=\"totalligne_".$lid."\"></td>
                        </tr>
                        <tr id=\"trtxt_".$lid."\" style=\"display:none;\">
                            <td colspan=\"6\"><textarea id=\"text_".$lid."\" rows=\"3\" cols=\"60\">".trim($infos_compta." ".$avoir['description'])."</textarea></td>
                        </tr>";
            }
        }
    }
    
    print "<tr>
            <td colspan=\"5\" style=\"font-weight:bold;text-align:right;\"><input type=\"button\" value=\"Recalculer\" onclick=\"calcavoir();\"> Total avoir : </td>
            <td id=\"totalavoir\">0,00</td>
        </tr>
    </table>
    </form>";

    print "<div><input type=\"button\" value=\"Annuler\" onclick=\"document.location='avoir_fournisseur.php';\"> &nbsp; <input type=\"button\" id=\"submit\" onclick=\"envoi();\" value=\"Envoyer la demande d'avoir\" ".$disablesend."></div>";
}

?>
