<?php
/*
if($_SERVER['REMOTE_ADDR']!="88.184.216.175"){
    print "page en cours de réparation";
    exit;

}*/
ini_set("display_errors",1);


require "admin_global.php";
include "./header.php";
include "./menu.php";
include "./fonctions.php";
include("facture.class.php");

include("script_adv.php");
// var_dump($mail);exit;
$today=mktime(0,0,0,date("m"),date("d"),date("Y"));

if(!isset($ts)) $ts=$today;
$tomorrow=$ts+86400;
if(date("w",$tomorrow)==6) $tomorrow+=172800;
if(date("w",$tomorrow)==0) $tomorrow+=86400;

// preparation panneaux de recherche facture/commande, ouverture dans une nouvelle fenetre
$srcform="<div id=\"formurecherche\" class=\"table_factures\">
                <form method=\"post\" action=\"facture.php\" target=\"_Blank\">
                <input type=\"hidden\" name=\"action\" value=\"rechercher\">
		            &nbsp;&nbsp;&nbsp;&nbsp;<b>Rechercher Commande :</b> 
                <input type=\"text\" placeholder=\"n° cde / n° fact / société / nom  / email\" name=\"ref\" size=\"80\" name=\"ref\" value=\"\">&nbsp;&nbsp;
                <input type=\"checkbox\" name=\"suppr\" ".(isset($suppr)?"checked":"").">Supprimées&nbsp;&nbsp; 
                <input type=\"submit\" value=\"Lancer\">
                </form>
          </div>";
          
// affichage panneaux de recherche
$txt.=$srcform;

if(!isset($_GET['nocmd'])){
    //******************************** Suivi des Expeditions / Livraison *********************************************
    if(isset($_GET['updefldate']) && isset($_GET['eflid'])){
        $envoi=$DB_site2->query_first("SELECT efl.indate,efl.outdate,efl.type,lf.artcode,ef.destinataire,lf.factureid FROM envoi_fournisseur_ligne efl INNER JOIN lignefacture lf ON (lf.lignefactureid=efl.ligne_facture_id) INNER JOIN envoi_fournisseur ef ON (ef.id=efl.efid) WHERE efl.id=".$_GET['eflid']);
        $DB_site2->query("UPDATE envoi_fournisseur_ligne SET ".$_GET['modifdate']."date=".$_GET['updefldate']." WHERE id=".$_GET['eflid']);
        $suivitxt="Changement date ".($_GET['modifdate']=="in"?"enlèvement":"livraison")." commande ".$envoi['destinataire']." ".$envoi['artcode']." : ".date("d/m/Y",$envoi[$_GET['modifdate']."date"])." remplacé par ".date("d/m/Y",$_GET['updefldate']);
        $DB_site2->query("INSERT INTO suivi_commande SET facturid=".$envoi['factureid'].", date=".time().", commercial='".$user_info[username]."', suivi='".mysqli_real_escape_string($DB_site2->link_id,$suivitxt)."'");
        $scrolltop="window.addEventListener(\"load\", scrolltop, false);";
    }
    if(isset($_GET['updefldate']) && isset($_GET['efid'])){
        $envois=$DB_site2->query("SELECT efl.indate,efl.outdate,efl.type,lf.artcode,ef.destinataire,lf.factureid FROM envoi_fournisseur_ligne efl INNER JOIN lignefacture lf ON (lf.lignefactureid=efl.ligne_facture_id) INNER JOIN envoi_fournisseur ef ON (ef.id=efl.efid) WHERE efl.efid=".$_GET['efid']);
        $DB_site2->query("UPDATE envoi_fournisseur_ligne SET ".$_GET['modifdate']."date=".$_GET['updefldate']." WHERE efid=".$_GET['efid']);
        $envoi=$DB_site2->fetch_array($envois);
        $suivitxt="Changement date ".($_GET['modifdate']=="in"?"enlèvement":"livraison")." commande ".$envoi['destinataire'];
        $suivitxt.="<br> ".$envoi['artcode']." : ".date("d/m/Y",$envoi[$_GET['modifdate']."date"])." remplacé par ".date("d/m/Y",$_GET['updefldate']);
        while($envoi2=$DB_site2->fetch_array($envois)) $suivitxt.="<br> ".$envoi2['artcode']." : ".date("d/m/Y",$envoi2[$_GET['modifdate']."date"])." remplacé par ".date("d/m/Y",$_GET['updefldate']);
        $DB_site2->query("INSERT INTO suivi_commande SET facturid=".$envoi['factureid'].", date=".time().", commercial='".$user_info[username]."', suivi='".mysqli_real_escape_string($DB_site2->link_id,$suivitxt)."'");
        $scrolltop="window.addEventListener(\"load\", scrolltop, false);";
    }
    if(isset($_GET['validadvdate']) && isset($_GET['efid'])){
        $typedate=($_GET['modifdate']=="in"?"enlevement":"livraison");
        $envoi=$DB_site2->query("UPDATE envoi_fournisseur SET `$typedate`=".intval($_GET['validadvdate'])." WHERE id=".$_GET['efid']);
        $scrolltop="window.addEventListener(\"load\", scrolltop, false);";
    }
    if(isset($_GET['stock_cmd_id']) && isset($_GET['modifdate'])){
        $stock_cmd=new stock_cmd($DB_site2,$_GET['stock_cmd_id']);
        switch($_GET['modifdate']){
        	  case "in":
        	     $typedate="date_enlevement";
        	  break;
        	  
        	  case "out":
        	     $typedate="date_livraison";
        	  break;
        }
        $items=$stock_cmd->getitems($_GET['artid'],0,$_GET['olddate'],$typedate);
        foreach($items as $item){
        	  $item->$typedate=intval($_GET['upddate']);
        	  $item->update();
        }
    }	
  
    if(!isset($_GET['tdate'])) $_GET['tdate']="indate";
    if($_GET['tdate']=="indate"){
            $optdest="";
            /// affiche les transporteurs (et installateurs) actifs
            $req=$DB_site2->query("SELECT * FROM fournisseur LEFT OUTER JOIN fournisseur_condition fc USING(societe) WHERE (type = 'trsptadv' OR type = 'sls') AND fc.activite = '1' AND (fc.types LIKE '%transporteur%' OR fc.types LIKE '%installateur%') GROUP BY societe ORDER BY societe");
            while($trspt=$DB_site2->fetch_array($req)){
                $optdest.="<option value=\"".$trspt['societe']."\">".$trspt['societe']."</option>";
            }
            
            $optexp="";
            $req=$DB_site2->query("SELECT * FROM `fournisseur` WHERE `type` LIKE 'fourn' ORDER BY societe");
            while($lieu=$DB_site2->fetch_array($req)){
                $optexp.="<option value=\"".$lieu['id']."\">".$lieu['societe']." ".$lieu['ville']."</option>";
            }
            
            $txttitre="Enlèvement ";
            $printexp="<form action=\"bordereau_exp.php\" target=\"_blank\">
                        <p>Transporteur : <select name=\"destinataire\">$optdest</select>&nbsp;Date : <input type=\"text\" id=\"dateexp\" name=\"date\" value=\"".date("d/m/Y")."\" onclick=\"calendrier_show(this.id,this.value)\" size=\"8\" readonly></p>
                        <p>Lieu de départ : <select name=\"enlev\">$optexp</select></p>
                        <input type=\"submit\" value=\"Imprimer les expéditions\">
                        </form>";
    }
    if($_GET['date']=="outdate") $txttitre="Livraison ";
    if(!isset($_GET['order'])) $_GET['order']=$_GET['facturid'];
    
    
    // Checkboxes de selection Fournisseurs/Transporteurs/Installateurs et Delais 
    $txt.="<br><div id=\"formurecherche\" class=\"table_factures\">"; 
    $txt.="<table style=\"width:100%;\" border=\"0\">
                <tr>
                    <td>
                        <p style=\"background-color:#f7fadf;\"><input type=\"checkbox\" name=\"filtertype\" value=\"fourn\" onclick=\"filtertables()\" checked> Fournisseurs</p>
                        <p style=\"background-color:#EEFAFF;\"><input type=\"checkbox\" name=\"filtertype\" value=\"trspt\" onclick=\"filtertables()\" checked> Transporteurs</p>
                        ".($_GET['tdate']=="outdate"?"<p style=\"background-color:#ffdbdb;\"><input type=\"checkbox\" name=\"filtertype\" value=\"sls\" onclick=\"filtertables()\" checked> Installateur</p>":"")."
                    </td>
                    <td>
                        <p><input type=\"checkbox\" name=\"filterdate\" value=\"$ts\" onclick=\"filtertables()\" checked> Aujourd'hui</p>
                        <p><input type=\"checkbox\" name=\"filterdate\" value=\"late\" onclick=\"filtertables()\" checked> Retard</p>
                        <p><input type=\"checkbox\" name=\"filterdate\" value=\"imp\" onclick=\"filtertables()\" checked> Impératif</p>
                        <p><input type=\"checkbox\" name=\"filterdate\" value=\"$tomorrow\" onclick=\"filtertables()\"> 2 jours</p>
                        <p><input type=\"checkbox\" name=\"filterdate\" value=\"a_venir\" onclick=\"filtertables()\"> A venir</p>
                    </td>
                    <td style=\"width:50%;\">
                        $printexp
                    </td>
                </tr>
           </table>";
    $txt.="</div>";

    // Gestion de l'affichage des tableaux de resultats  
    if($_GET['tdate']=="outdate"){
        $txt.=suivi_fourn($ts,"outdate",$_GET['order'],"imp");
        $txt.=suivi_trspt($ts,"outdate",$_GET['order'],"imp");
        $txt.=suivi_sls($ts,"outdate",$_GET['order'],"imp");
    }
  
    $txt.=suivi_fourn($ts,$_GET['tdate'],$_GET['order'],"<");
    $txt.=suivi_trspt($ts,$_GET['tdate'],$_GET['order'],"<");
    if($_GET['tdate']=="outdate") $txt.=suivi_sls($ts,$_GET['tdate'],$_GET['order'],"<");
  
    $txt.=suivi_fourn($ts,$_GET['tdate'],$_GET['order']);
    $txt.=suivi_trspt($ts,$_GET['tdate'],$_GET['order']);
    if($_GET['tdate']=="outdate") $txt.=suivi_sls($ts,$_GET['tdate'],$_GET['order']);
  
    $txt.=suivi_fourn($tomorrow,$_GET['tdate'],$_GET['order'],null,2);
    $txt.=suivi_trspt($tomorrow,$_GET['tdate'],$_GET['order'],null,2);
    if($_GET['tdate']=="outdate") $txt.=suivi_sls($tomorrow,$_GET['tdate'],$_GET['order'],null,2);

    $txt.=suivi_fourn($tomorrow,$_GET['tdate'],$_GET['order'],null,2,'a_venir');
    $txt.=suivi_trspt($tomorrow,$_GET['tdate'],$_GET['order'],null,2,'a_venir');
    if($_GET['tdate']=="outdate") $txt.=suivi_sls($tomorrow,$_GET['tdate'],$_GET['order'],null,2,'a_venir');

  //   $txt.=suivi_fourn($ts+86400,$_GET['tdate'],$_GET['order'],">");
  //   $txt.=suivi_trspt($ts+86400,$_GET['tdate'],$_GET['order'],">");
  //   $txt.=suivi_sls($ts+86400,$_GET['tdate'],$_GET['order'],">");

}else{
    //******************************** Suivi commandes a traiter *********************************************
    if(isset($_GET['updadvdate'])){
        if(intval($_GET['calid'])==0)
          $DB_site2->query("INSERT INTO calendrier SET ".$_GET['modifdate']."='".$_GET['updadvdate']."', objid='".$_GET['objid']."', type='".$_GET['type']."', expediteur='".$user_info['username']."'");
        else
          $DB_site2->query("UPDATE calendrier SET ".$_GET['modifdate']."='".$_GET['updadvdate']."' WHERE id=".$_GET['calid']);
          $scrolltop="window.addEventListener(\"load\", scrolltop, false);";
    }
    if(isset($_POST['objid'])){
        $tsdate=$tsreappro=$tsenlevement=0;
        if($_POST['date']!=""){
          	$sp=preg_split('/[- :\/h]/',$_POST['date']);
          	$tsdate=mktime($sp[3],$sp[4],0,$sp[1],$sp[0],$sp[2]);
        }
        if($_POST['reappro']!=""){
          	$sp=preg_split('/[- :\/h]/',$_POST['reappro']);
          	$tsreappro=mktime($sp[3],$sp[4],0,$sp[1],$sp[0],$sp[2]);
        }
        if($_POST['enlevement']!=""){
          	$sp=preg_split('/[- :\/h]/',$_POST['enlevement']);
          	$tsenlevement=mktime($sp[3],$sp[4],0,$sp[1],$sp[0],$sp[2]);
        }
        foreach($_POST['dispo'] as $lignefactureid=>$txtbis){
          	if(preg_match("/([0-9]+)_([0-9]+)/",$lignefactureid,$m)){
          	    $DB_site2->query("INSERT INTO mail_outbox SET date=".time().", utilisateur='".$user_info['username']."', `to`='".$_POST['mail_dest']."', subject='Demande de disponibilité', body='<table>".mysqli_real_escape_string($DB_site2->link_id,$txtbis)."</table>', stock_cmd_id=".$m[1]);
          	}else{
          	    $facture=$DB_site2->query_first("SELECT f.facturid FROM facture f INNER JOIN lignefacture lf ON(lf.factureid=f.facturid) WHERE lf.lignefactureid=".$lignefactureid);
          	    $DB_site2->query("INSERT INTO mail_outbox SET date=".time().", utilisateur='".$user_info['username']."', `to`='".$_POST['mail_dest']."', subject='Demande de disponibilité', body='<table>".mysqli_real_escape_string($DB_site2->link_id,$txtbis)."</table>', facturid=".$facture['facturid']);
          	}
          	$cal=$DB_site2->query_first("SELECT objid FROM calendrier WHERE objid='".$lignefactureid."'");
          	if($cal['objid']==$lignefactureid)
          	    $DB_site2->query("UPDATE calendrier SET dispo=1 WHERE objid='".$lignefactureid."'");
          	else
          	    $DB_site2->query("INSERT INTO calendrier SET objid='".$lignefactureid."', type='".$_POST['type']."', date=".$tsdate.", reappro=".$tsreappro.", enlevement=".$tsenlevement.", txt='".$_POST['txt']."', expediteur='".$user_info['username']."', dispo=1");
        }
        $scrolltop="window.addEventListener(\"load\", scrolltop, false);";
    }
    
    $fournlink=array();
    $jscontact="var advfourn=new Array();\n";
    $req=$DB_site2->query("SELECT * FROM fournisseur WHERE `type`='fournadv'");
    while($advf=$DB_site2->fetch_array($req)){
      $mails=array();
      if($advf['groupes']!=""){
  	$groups=explode(",",$advf['groupes']);
  
  	foreach($groups as $group){
  	  $fournlink[$group][]=$advf['mail'];
  	}
      }else{
  	  $fournlink[$advf['societe']][]=$advf['mail'];
      }
      
    }
    foreach($fournlink as $societe=>$mails)
        $jscontact.="advfourn['".addslashes($societe)."']=\"".implode(";",$mails)."\";\n";
  
    //************************ Double emplois avec les critères checkbox ******************************
    /*if(isset($_GET['ts']))
        $txtFilterLink="<br>Commandes à passer aujourd'hui - <a href=\"suivi_adv.php?nocmd=1\">Afficher toutes les commandes à passer</a>";
    else
        $txtFilterLink="<br><a href=\"suivi_adv.php?nocmd=1&ts=".mktime(0,0,0,date("m"),date("d"),date("Y"))."\">Afficher les commandes à passer aujourd'hui</a> - Toutes les commandes";
    */
    
    $txt.="<div id=\"addcaldiv\">
    <form name=\"addcalform\" action=\"suivi_adv.php?nocmd=1\" method=\"post\">
    <input type=\"hidden\" name=\"objid\" value=\"0\">
    <input type=\"hidden\" name=\"type\">
    <table>
    <tr><td>Date expédition :</td><td><input type=\"text\" id=\"date\" name=\"date\" onclick=\"calendrier_show('date','');\" readonly> <img src=\"images/supprimer.gif\" onclick=\"document.getElementById('date').value='';\"></td></tr>
    <tr><td>Date réappro :</td><td><input type=\"text\" id=\"reappro\" name=\"reappro\" onclick=\"calendrier_show('reappro','');\" readonly> <img src=\"images/supprimer.gif\" onclick=\"document.getElementById('reappro').value='';\"></td></tr>
    <tr><td>Date enlèvement souhaitée :</td><td><input type=\"text\" id=\"enlevement\" name=\"enlevement\" onclick=\"calendrier_show('enlevement','');\" readonly> <img src=\"images/supprimer.gif\" onclick=\"document.getElementById('enlevement').value='';\"></td></tr>
    <tr><td>Message :</td><td><textarea name=\"txt\"></textarea></td></tr>
    <tr><td colspan=\"2\" align=\"center\"><input type=\"button\" value=\"Annuler\" onclick=\"document.getElementById('addcaldiv').style.visibility='hidden';\"> <input type=\"submit\" value=\"OK\"></td></tr>
    </table>
    </form>
    </div>";
  
    $txt.="<br><div id=\"formurecherche\" class=\"table_factures\">";
    //$txt.="<table>
    //<tr><td rowspan=\"4\">Afficher :</td>
    $txt.="<table width=\"60%\" align=\"\" border=\"0\">
              <tr><td rowspan=\"4\" align=\"right\"><b>Choix Affichage :&nbsp;&nbsp;&nbsp;</b></td>
              <td style=\"background-color:#f7fadf;\"><input type=\"checkbox\" name=\"filtertype\" value=\"fourn\" onclick=\"filternc()\" checked> Fournisseurs</td><td><input type=\"checkbox\" name=\"filterdate\" value=\"unk\" onclick=\"filternc()\" checked> Sans date</td></tr>
              <tr><td style=\"background-color:#EEFAFF;\"><input type=\"checkbox\" name=\"filtertype\" value=\"trspt\" onclick=\"filternc()\" checked> Transporteurs</td><td><input type=\"checkbox\" name=\"filterdate\" value=\"today\" onclick=\"filternc()\" checked> Aujourd'hui</td></tr>
              <tr><td style=\"background-color:#ffdbdb;\"><input type=\"checkbox\" name=\"filtertype\" value=\"sls\" onclick=\"filternc()\" checked> Installateur</td><td><input type=\"checkbox\" name=\"filterdate\" value=\"late\" onclick=\"filternc()\" checked> Retard</td></tr>
              <tr><td></td><td><input type=\"checkbox\" name=\"filterdate\" value=\"futur\" onclick=\"filternc()\"> Futur</td></tr>
              <tr><td></td><td><input type=\"checkbox\" onclick=\"filteradv(this.checked)\"> Commandes programmées</td></tr>
          </table>";
    $txt.="</div>";
    
    //$txt.= $txtFilterLink;
    
    $txt.=nocmd($_GET['ts'],$_GET['order']);
}


//************************************************************************************************
// Préparation de l'affichage du contenu de la page
$txtBody = $txt;
$txt="";

$textEntete = "des Livraison";
if(isset($_GET['nocmd']))
  $textEntete = "des Commandes à Traiter";
if(isset($_GET['tdate']) && ($_GET['tdate']=="indate") )
  $textEntete = "des Expeditions";
  
//************************************************************************************************
// Affichage du contenu de la page

echo "<h1>Adv Suivi ".$textEntete."</h1>

<link href=\"cal.css\" type=\"text/css\" rel=\"stylesheet\">

<script language=\"javascript\" src=\"calendrier.js\"></script>

<script language=\"javascript\">
  
  //****************************************************************************
  function scrolltop(){
    document.body.scrollTop=".intval($_GET['scrolltop']).";
  }
  $scrolltop
  $jscontact
  $js_popup_pj
  
  //****************************************************************************
  var modifdate='';
  var calid=0;
  var type='';
  var objid=0;
  var dispolist=new Array();
  var dispofourn=new Array();
  initcalendrier(false);
  
  function chgdate(){
      document.location='suivi_adv.php?order=$order&ts='+cal_timestamp;
  }

  //****************************************************************************
  function filtertables(){
    var comb=[];
    var alltbl=[];
    for(i=0;i<document.getElementsByName('filtertype').length;i++){
      for(i2=0;i2<document.getElementsByName('filterdate').length;i2++){
        if(document.getElementsByName('filtertype')[i].checked && document.getElementsByName('filterdate')[i2].checked){
          comb.push('tbl'+document.getElementsByName('filtertype')[i].value+document.getElementsByName('filterdate')[i2].value);
        }
        alltbl.push('tbl'+document.getElementsByName('filtertype')[i].value+document.getElementsByName('filterdate')[i2].value);
      } 
    }

    for(i=0;i<alltbl.length;i++){
      if(document.getElementById(alltbl[i])) document.getElementById(alltbl[i]).style.display='none';
    }

    for(i=0;i<comb.length;i++){
      if(document.getElementById(comb[i]))
        document.getElementById(comb[i]).style.display='block';
    }
  }

  //****************************************************************************
  function filternc(){
    for(i=0;i<document.getElementsByName('filtertype').length;i++){
      var chk=document.getElementsByName('filtertype')[i];
	    if(document.getElementById('tbl'+chk.value)) document.getElementById('tbl'+chk.value).style.display=(chk.checked?'':'none');
    }

    for(i=0;i<document.getElementsByName('filterdate').length;i++){
      var chk=document.getElementsByName('filterdate')[i];
      for(i2=0;i2<document.getElementsByName(chk.value+'[]').length;i2++){
        document.getElementsByName(chk.value+'[]')[i2].style.display=(chk.checked?'table-row':'none');
      }
    }
  }

  //****************************************************************************
  function updadvdate(){
      if(modifdate=='') return;
      document.location='suivi_adv.php?nocmd=1&order=".$_GET['order']."&ts=".$_GET['ts']."&updadvdate='+cal_timestamp+'&modifdate='+modifdate+'&calid='+calid+'&objid='+objid+'&type='+type+'&scrolltop='+document.body.scrollTop;
  }
  
  //****************************************************************************
  function addcaldate(objid,type){
      document.getElementsByName('objid')[0].value=objid;
      document.getElementsByName('type')[0].value=type;
      var d=new Date();
      document.getElementById('date').value=document.getElementById('reappro').value='';
      document.forms.addcalform.action='suivi_adv.php?nocmd=1&scrolltop='+document.body.scrollTop;
      document.getElementById('addcaldiv').style.left=document.getElementById('add'+type+objid).offsetLeft+'px';
      document.getElementById('addcaldiv').style.top=document.getElementById('add'+type+objid).offsetTop+'px';
      document.getElementById('addcaldiv').style.visibility='visible';
  }
  
  //****************************************************************************
  function todispo(state,facturid,lignefactureid,artcode,qte,prix,enlevement,livraison,reappro,libelle,artfourn){
      var enlevementtxt='';
      if(enlevement>0){
      	  var d=new Date();
      	  d.setTime(enlevement*1000);
      	  enlevementtxt=d.getDate()+'/'+(d.getMonth()+1)+'/'+d.getFullYear();
      }
      var livraisontxt='';
      if(livraison>0){
      	  var d=new Date();
      	  d.setTime(livraison*1000);
      	  livraisontxt=d.getDate()+'/'+(d.getMonth()+1)+'/'+d.getFullYear();
      }
      if(state){
      	 dispolist[lignefactureid]=\"<tr><td>#CDE\"+facturid+\"#</td><td>\"+qte+\"</td><td>\"+artcode+\"</td><td>\"+libelle+\"</td><td>\"+prix+\"</td><td>\"+reappro+\"</td><td>\"+enlevementtxt+\"</td><td>\"+livraisontxt+\"</td><td></td></tr>\";
      	 dispofourn[artcode]=artfourn;
      }else{
      	 dispolist[lignefactureid]=undefined;
      	 delete dispofourn[artcode];
      }
  }
  
  //****************************************************************************
  function maildispo(){
      /*if(dispolist.length==0){
      	  alert('Il faut cocher les articles pour lesquels il faut demander la disponibilité');
      	  return;
      }*/
      
      var to=undefined;
      
      var activefourn=new Array();
      var nbart=Object.keys(dispofourn).length;
      if(nbart>0)
      {
    	  for(l in dispofourn)
        {
    	      if(dispofourn.hasOwnProperty(l))
            {
        		  for(f in dispofourn[l])
              {
        		      if(dispofourn[l].hasOwnProperty(f)){
        			        if(activefourn[dispofourn[l][f]]==undefined)
        			           activefourn[dispofourn[l][f]]=1;
        			        else
        			           activefourn[dispofourn[l][f]]++;
    		          }
    		      }
	          }
	      }
	  
    	  var selectfourn=new Array();
        
    	  for(l in activefourn){
    	      if(activefourn.hasOwnProperty(l) && activefourn[l]==nbart)
    		      selectfourn.push(l);
    	  }
        
	      if(selectfourn.length==0){
      	    var close=document.createElement('a');
      	    close.onclick=function(){document.getElementById('dispodest').style.display='none';
        }
        
  	    close.innerHTML='Fermer';
  	    close.href=\"javascript:void(0);\";
  	    document.getElementById('dispodest').innerHTML='Aucun fournisseur en commun n\'a été trouvé<br>';
  	    document.getElementById('dispodest').appendChild(close);
  	    document.getElementById('dispodest').style.display='block';
  	    return;
      }
    
  	  if(selectfourn.length==1)
      {
  	     to=advfourn[selectfourn[0]];
  	  }else{
    	   document.getElementById('dispodest').innerHTML='Plusieurs fournisseurs sont disponibles :<br>';
    	   var br=document.createElement('br');
    	   for(i=0;i<selectfourn.length;i++)
         {
    	      var a=document.createElement('a');
    	      a.href=\"javascript:void(0);\";
    	      a.setAttribute('onclick',\"document.getElementsByName('mail_dest')[0].value='\"+advfourn[selectfourn[i]]+\"';document.getElementById('dispodest').style.display='none';\");
    	      a.innerHTML=selectfourn[i];
    	      document.getElementById('dispodest').appendChild(br);
    	      document.getElementById('dispodest').appendChild(a);
    	   }
  	     document.getElementById('dispodest').style.display='block';
  	  }
    }
  
    var msg=\"<p>Bonjour,</p><p>Pourriez vous nous communiquer la disponibilité pour la(les) référence(s) suivantes, nous confirmer prix d'achat et nous confirmer/indiquer la(les) date(s) d'enlèvement(s) :</p><table border=1 cellspacing=0><tr><td>Commande</td><td>Qté</td><td>Réf. fabricant</td><td>Désignation</td><td>Prix Unit &euro;HT</td><td>Date réappro</td><td>Date enlèvement souhaitée par nos soins</td><td>Date livraison souhaitée par vos soins</td><td>Infos fournisseur</td></tr>\";
      
    var inputs=document.forms.addcalform.getElementsByTagName('input');
    for(i=0;i<inputs.length;i++) if(inputs[i].name.substring(0,5)=='dispo') document.forms.addcalform.removeChild(inputs[i]);

    for(l in dispolist){
    	  if(dispolist[l]!=undefined){
    	    msg+=dispolist[l];
    	    var inp=document.createElement('input');
    	    inp.type='hidden';
    	    inp.name='dispo['+l+']';
    	    inp.value=dispolist[l];
    	    document.forms.addcalform.appendChild(inp);
    	  }
    }
    
    document.getElementById('date').value='';
    document.getElementById('reappro').value='';
    document.getElementById('enlevement').value='';
    document.getElementsByName('txt')[0].value='';
    document.getElementsByName('type')[0].value='fourncmd';

    msg+=\"</table><p>Merci par avance de votre réponse, en répondant directement sur ce mail.</p>\";
    mail_auto(undefined,to,undefined,\"Demande de disponibilité\",msg);
    sendform=document.forms.addcalform;
  }

  //****************************************************************************
  var eflid=false;
  var efid=false;
  function updefldate(){
      if(eflid==false) return;
      document.location='suivi_adv.php?eflid='+eflid+'&updefldate='+cal_timestamp+'&modifdate='+modifdate+'&tdate=".$_GET['tdate']."&scrolltop='+document.body.scrollTop;
  }
  
  //****************************************************************************
  function updefdate(){
      if(efid==false) return;
      document.location='suivi_adv.php?efid='+efid+'&updefldate='+cal_timestamp+'&modifdate='+modifdate+'&tdate=".$_GET['tdate']."&scrolltop='+document.body.scrollTop;
  }
  
  //****************************************************************************
  function validadvdate(){
      if(efid==false) return;
      document.location='suivi_adv.php?efid='+efid+'&validadvdate='+cal_timestamp+'&modifdate='+modifdate+'&tdate=".$_GET['tdate']."&scrolltop='+document.body.scrollTop;
  }
  
  //****************************************************************************
  function toggletool(sender,state){
      var div=sender.getElementsByTagName('div')[0];
      if(div) div.style.visibility=state;
//       document.getElementById(id).style.visibility=state;
  }
  
  //****************************************************************************
  function filteradv(state){
      var listtd=document.getElementsByName('progcmd');
      if(state){
	  for(i=0;i<listtd.length;i++){
	    if(listtd[i].innerHTML!='')
	      listtd[i].parentNode.style.display='table-row';
	    else
	      listtd[i].parentNode.style.display='none';
	  }
      }else
	  for(i=0;i<listtd.length;i++) listtd[i].parentNode.style.display='table-row';
  }
  
  //****************************************************************************
  var stock_cmd_id;
  var artid;
  var olddate;
  function modifstockdate(it_stock_cmd_id,it_artid,it_olddate,typedate){
      stock_cmd_id=it_stock_cmd_id;
      artid=it_artid;
      olddate=it_olddate;
      modifdate=typedate;
      calendrier_show(undefined,'',false,updstockdate);
  }
  
  //****************************************************************************
  function updstockdate(){
      document.location='suivi_adv.php?stock_cmd_id='+stock_cmd_id+'&artid='+artid+'&olddate='+olddate+'&upddate='+cal_timestamp+'&modifdate='+modifdate+'&scrolltop='+document.body.scrollTop;
  }

  //****************************************************************************
  function filter_futur(){
    if(document.getElementById('addcaldiv')) filternc();
    if(document.getElementById('formurecherche')) filtertables();
  }

  window.onload = filter_futur;
</script>

<style>
  .tbl { border-collapse:collapse; margin-top:10px; border:solid thin #808080; width:100%; }
  .tbl td { border-left:solid thin #808080; border-right:solid thin #808080; padding:4px; }
  .tbltitle { border:solid thin #808080; text-align:center;height:30px;font-weight:bold; }
  .tbltitlebis {border:solid thin #808080; text-align:center;height:30px;font-weight:bold;font-size: 18px;}
  #addcaldiv { position:absolute; visibility:hidden; background-color:white; border:solid thin black; }
  #addcaldiv input { width:80px; }
  #addcaldiv textarea { width:200px; }
  #dispodest { position:absolute; display:none; border:solid 1px black; background:white; padding:4px; text-align:center; }
</style>
";


// Affichage de la page, critères de recherches et tableaux de resultats
print $txtBody;

include "./footer.php";
?>
